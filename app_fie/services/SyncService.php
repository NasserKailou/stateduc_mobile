<?php
/**
 * app_fie/services/SyncService.php
 * ══════════════════════════════════════════════════════════════════════════════
 * Service de synchronisation des établissements et années scolaires.
 *
 * Sources :
 *   1. API StatEduc — vue ATLAS_COLLINE (source de vérité) — mode NORMAL
 *   2. Fichier Excel FICHIER_ETAB.xlsx (14 colonnes ATLAS_COLLINE) — mode DÉGRADÉ
 *
 * Structure ATLAS_COLLINE / Excel (14 colonnes) :
 *   CODE_PROVINCE, PROVINCE, CODE_COMMUNE, COMMUNE, CODE_COLLINE, COLLINE,
 *   CODE_TYPE_SECTEUR_ENS, SECTEUR_ENS, CODE_TYPE_STATUT_ORG, STATUT,
 *   NOM_ETAB, CODE_ETABLISSEMENT, CODE_TYPE_MILIEU, MILIEU
 *
 * Dans les deux cas, l'upsert est idempotent par CODE_ETABLISSEMENT.
 * Chaque upsert peuple aussi ref_province, ref_commune, ref_colline pour
 * les cascades déconnectées (sans accès StatEduc).
 * ══════════════════════════════════════════════════════════════════════════════
 */

class SyncService
{
    private StatEducApiClient $apiClient;
    private Logger            $logger;
    private int               $syncLogId = 0;

    public function __construct(?StatEducApiClient $client = null)
    {
        $this->apiClient = $client ?? new StatEducApiClient();
        $this->logger    = new Logger('sync');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SYNC ÉTABLISSEMENTS DEPUIS L'API STATEDUC (ATLAS_COLLINE)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Synchronisation complète ou incrémentale depuis l'API StatEduc.
     * L'API utilise la vue ATLAS_COLLINE → toutes les 14 colonnes FICHIER_ETAB.
     *
     * @param int|null    $secteur      Filtrer par CODE_TYPE_SECTEUR_ENS
     * @param int|null    $codeProvince Filtrer par CODE_PROVINCE (entier)
     * @param string|null $triggeredBy  Identité du déclencheur
     * @return array  ['inserted'=>N, 'updated'=>N, 'errors'=>N, 'total'=>N]
     */
    public function syncFromApi(
        ?string $updatedSince = null,
        ?int    $secteur      = null,
        ?string $province     = null,   // conservé pour rétrocompat (ignoré si codeProvince fourni)
        ?string $triggeredBy  = 'system',
        ?int    $codeProvince = null
    ): array {
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $this->syncLogId = $this->startSyncLog('api_stateduc', $triggeredBy);
        $summary = ['inserted' => 0, 'updated' => 0, 'errors' => 0, 'total' => 0, 'pages_done' => 0];
        $errorDetails = [];

        try {
            if (!$this->apiClient->ping()) {
                throw new RuntimeException('API StatEduc inaccessible (ping failed)');
            }

            $page = 1;
            do {
                $params = ['page' => $page, 'per_page' => STATEDUC_SYNC_PAGE_SIZE];
                if ($secteur)      $params['secteur']       = $secteur;
                if ($codeProvince) $params['code_province'] = $codeProvince;

                $data = $this->apiClient->getEtablissements($params);
                if (!$data) break;

                $totalPages     = (int)($data['pages'] ?? 1);
                $summary['total'] = (int)($data['total'] ?? 0);

                foreach ($data['etablissements'] as $etab) {
                    try {
                        $result = $this->upsertEtablissement($etab, 'api_stateduc');
                        $summary[$result]++;
                    } catch (Throwable $e) {
                        $summary['errors']++;
                        $errorDetails[] = [
                            'code' => $etab['code_etablissement'] ?? 'unknown',
                            'err'  => $e->getMessage(),
                        ];
                        $this->logger->warning("Upsert erreur etab {$etab['code_etablissement']}: " . $e->getMessage());
                    }
                }

                $summary['pages_done'] = $page;
                $this->updateSyncLog($this->syncLogId, $summary);
                $page++;

            } while ($page <= $totalPages);

            $this->finishSyncLog($this->syncLogId, 'success', $summary, $errorDetails);
            $this->logger->info("Sync API ATLAS_COLLINE terminée : {$summary['total']} etabs, "
                . "+{$summary['inserted']} insérés, ~{$summary['updated']} MàJ, "
                . "{$summary['errors']} erreurs");

        } catch (Throwable $e) {
            $this->finishSyncLog($this->syncLogId, 'error', $summary, [['err' => $e->getMessage()]]);
            $this->logger->error("Sync API échouée : " . $e->getMessage());
            throw $e;
        }

        return $summary;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SYNC ANNÉES SCOLAIRES DEPUIS TYPE_ANNEE STATEDUC
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Synchronise ref_type_annee depuis StatEduc TYPE_ANNEE.
     * La dernière année (code_type_annee = année_debut max) est marquée actif=1.
     * Schéma réel ref_type_annee : (code_type_annee, libelle, annee_debut, annee_fin, actif)
     * PAS de colonne 'ordre' — tri par code_type_annee DESC.
     *
     * @param string|null $triggeredBy
     * @return array  ['total' => N, 'upserted' => N, 'errors' => N]
     */
    public function syncTypeAnnee(?string $triggeredBy = 'system'): array
    {
        $summary = ['total' => 0, 'upserted' => 0, 'errors' => 0];

        try {
            $data = $this->apiClient->getTypeAnnees();
            if (!$data || empty($data['annees'])) {
                throw new RuntimeException('Aucune année scolaire retournée par l\'API StatEduc.');
            }

            $annees = $data['annees'];
            $summary['total'] = count($annees);

            // Déterminer le code_type_annee max (= année la plus récente)
            $codeMax = 0;
            foreach ($annees as $a) {
                $c = (int)($a['code_type_annee'] ?? 0);
                if ($c > $codeMax) $codeMax = $c;
            }

            $pdo = Database::getInstance();

            foreach ($annees as $annee) {
                $code    = (int)($annee['code_type_annee'] ?? 0);
                $libelle = trim($annee['libelle'] ?? '');
                if ($code <= 0 || $libelle === '') continue;

                // Extraire annee_debut / annee_fin depuis le libellé (ex: "2025-2026")
                $anneeParts = explode('-', $libelle);
                $anneeDebut = isset($anneeParts[0]) ? (int)trim($anneeParts[0]) : $code;
                $anneeFinV  = isset($anneeParts[1]) ? (int)trim($anneeParts[1]) : $code + 1;

                $actif = ($code === $codeMax) ? 1 : 0;

                $stmt = $pdo->prepare("
                    INSERT INTO ref_type_annee (code_type_annee, libelle, annee_debut, annee_fin, actif)
                    VALUES (:code, :libelle, :debut, :fin, :actif)
                    ON DUPLICATE KEY UPDATE
                        libelle     = VALUES(libelle),
                        annee_debut = VALUES(annee_debut),
                        annee_fin   = VALUES(annee_fin),
                        actif       = VALUES(actif)
                ");
                $stmt->execute([
                    ':code'    => $code,
                    ':libelle' => $libelle,
                    ':debut'   => $anneeDebut,
                    ':fin'     => $anneeFinV,
                    ':actif'   => $actif,
                ]);
                $summary['upserted']++;
            }

            // Log dans sync_type_annee_log (table optionnelle — ignore si absente)
            try {
                Database::query(
                    "INSERT INTO sync_type_annee_log (triggered_by, status, total, details, synced_at)
                     VALUES (?, 'success', ?, NULL, NOW())",
                    [$triggeredBy, $summary['upserted']]
                );
            } catch (Throwable $logEx) {
                // Table sync_type_annee_log absente — non bloquant
                $this->logger->warning('sync_type_annee_log absent : ' . $logEx->getMessage());
            }

            $this->logger->info("Sync TYPE_ANNEE terminée : {$summary['upserted']} années upsertées.");

        } catch (Throwable $e) {
            try {
                Database::query(
                    "INSERT INTO sync_type_annee_log (triggered_by, status, total, details, synced_at)
                     VALUES (?, 'error', 0, ?, NOW())",
                    [$triggeredBy, $e->getMessage()]
                );
            } catch (Throwable $logEx2) { /* Table absente — non bloquant */ }
            $summary['errors']++;
            $this->logger->error("Sync TYPE_ANNEE échouée : " . $e->getMessage());
            throw $e;
        }

        return $summary;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SYNC NATIONALITÉS depuis StatEduc SQL Server
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Synchronise ref_type_nationalite depuis [BURUNDI].[dbo].[TYPE_NATIONALITE]
     * via l'API StatEduc (endpoint /api/ref/nationalites).
     *
     * @param string|null $triggeredBy  identifiant de l'appelant (pour logs)
     * @return array  ['total' => N, 'upserted' => N, 'errors' => N]
     */
    public function syncNationalites(?string $triggeredBy = 'system'): array
    {
        $summary = ['total' => 0, 'upserted' => 0, 'errors' => 0];

        try {
            // Appel API — retourne { "nationalites": [ {code_type_nationalite, libelle, ordre}, ... ] }
            $data = $this->apiClient->get('/api/ref/nationalites');
            if (!$data || empty($data['nationalites'])) {
                throw new \RuntimeException('Aucune nationalité retournée par l\'API StatEduc.');
            }

            $rows = $data['nationalites'];
            $summary['total'] = count($rows);

            $pdo = Database::getInstance();

            foreach ($rows as $row) {
                $code    = (int)($row['code_type_nationalite'] ?? 0);
                $libelle = trim($row['libelle'] ?? '');
                $ordre   = (int)($row['ordre'] ?? 0);
                if ($code <= 0 || $libelle === '') continue;

                $stmt = $pdo->prepare("
                    INSERT INTO ref_type_nationalite
                        (code_type_nationalite, libelle, ordre, synced_at)
                    VALUES (:code, :libelle, :ordre, NOW())
                    ON DUPLICATE KEY UPDATE
                        libelle    = VALUES(libelle),
                        ordre      = VALUES(ordre),
                        synced_at  = NOW()
                ");
                $stmt->execute([
                    ':code'    => $code,
                    ':libelle' => $libelle,
                    ':ordre'   => $ordre,
                ]);
                $summary['upserted']++;
            }

            $this->logger->info("Sync NATIONALITES terminée : {$summary['upserted']} lignes upsertées.");

        } catch (\Throwable $e) {
            $summary['errors']++;
            $this->logger->error("Sync NATIONALITES échouée : " . $e->getMessage());
            throw $e;
        }

        return $summary;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // IMPORT EXCEL (mode dégradé / bootstrap)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Importe les établissements depuis FICHIER_ETAB.xlsx (format ATLAS_COLLINE).
     * 14 colonnes : CODE_PROVINCE, PROVINCE, CODE_COMMUNE, COMMUNE,
     *               CODE_COLLINE, COLLINE, CODE_TYPE_SECTEUR_ENS, SECTEUR_ENS,
     *               CODE_TYPE_STATUT_ORG, STATUT, NOM_ETAB,
     *               CODE_ETABLISSEMENT, CODE_TYPE_MILIEU, MILIEU
     *
     * MODE INSERT-ONLY (session10) :
     *   Lors d'une nouvelle importation, seuls les établissements ABSENTS de la
     *   base sont insérés. Toute ligne dont le CODE_ETABLISSEMENT existe déjà
     *   (quelle que soit la source : api_stateduc, excel_import, manuel) est
     *   ignorée — on ne met jamais à jour les données existantes par Excel.
     *
     *   Raison : éviter d'écraser des corrections manuelles ou des données API
     *   à chaque ré-importation du fichier brut.
     *
     * @param string $xlsxPath   Chemin absolu vers FICHIER_ETAB.xlsx
     * @param string $triggeredBy
     * @return array  Résumé ['inserted', 'skipped', 'errors', 'total']
     */
    public function importFromExcel(string $xlsxPath, string $triggeredBy = 'import'): array
    {
        if (!file_exists($xlsxPath)) {
            throw new RuntimeException("Fichier Excel introuvable : $xlsxPath");
        }

        $this->syncLogId = $this->startSyncLog('excel_import', $triggeredBy);
        $summary = ['inserted' => 0, 'skipped' => 0, 'errors' => 0, 'total' => 0];
        $errorDetails = [];

        // Priorité : PhpSpreadsheet (si disponible) → lecteur natif ZipArchive
        // (plus de dépendance python3/openpyxl — fonctionne sur Windows/XAMPP)
        if (class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            $rows = $this->readExcelWithSpreadsheet($xlsxPath);
        } else {
            $rows = $this->readExcelNative($xlsxPath);
        }

        $summary['total'] = count($rows);

        // ── Pré-charger tous les codes existants en mémoire ───────────────────
        // Un seul SELECT au lieu d'un SELECT par ligne → 100× plus rapide
        // pour 11 497 lignes sur XAMPP/Windows.
        $existingCodes = [];
        $existingRows  = Database::fetchAll(
            "SELECT code_etablissement FROM etablissements_miroir"
        );
        foreach ($existingRows as $r) {
            $existingCodes[(int)$r['code_etablissement']] = true;
        }
        $this->logger->info("Import Excel : {$summary['total']} lignes xlsx, "
            . count($existingCodes) . " codes déjà présents en base.");

        Database::beginTransaction();
        try {
            foreach ($rows as $row) {
                $code = (int)($row['CODE_ETABLISSEMENT'] ?? 0);
                if ($code <= 0) continue;

                // ── INSERT-ONLY : ignorer toute ligne déjà en base ────────────
                if (isset($existingCodes[$code])) {
                    $summary['skipped']++;
                    continue;
                }

                $etab = $this->normalizeExcelRow($row);

                try {
                    $this->insertEtablissement($etab, 'excel_import');
                    $existingCodes[$code] = true; // marquer comme inséré
                    $summary['inserted']++;
                } catch (Throwable $e) {
                    $summary['errors']++;
                    $errorDetails[] = ['code' => $code, 'err' => $e->getMessage()];
                }
            }
            Database::commit();
        } catch (Throwable $e) {
            Database::rollback();
            $this->finishSyncLog($this->syncLogId, 'error', $summary, [['err' => $e->getMessage()]]);
            throw $e;
        }

        $this->finishSyncLog($this->syncLogId, 'success', $summary, $errorDetails);
        $this->logger->info("Import Excel ATLAS_COLLINE terminé : {$summary['total']} lignes, "
            . "+{$summary['inserted']} insérés, {$summary['skipped']} ignorés (déjà présents)");
        return $summary;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UPSERT IDEMPOTENT (établissements_miroir + ref_province/commune/colline)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * INSERT OR UPDATE un établissement dans etablissements_miroir.
     * Met aussi à jour ref_province, ref_commune, ref_colline si codes présents.
     * Retourne 'inserted' ou 'updated'.
     */
    private function upsertEtablissement(array $etab, string $source): string
    {
        $code = (int)($etab['code_etablissement'] ?? 0);
        if ($code <= 0) throw new InvalidArgumentException("code_etablissement invalide");

        // ── Extraction des champs ATLAS_COLLINE (réponse API ou Excel normalisé)
        // L'API retourne un objet plat (pas de sous-objet localisation depuis v2)
        $province  = $this->cleanStr($etab['province']         ?? null);
        $commune   = $this->cleanStr($etab['commune']          ?? null);
        $colline   = $this->cleanStr($etab['colline']          ?? null);
        $secteurEns= $this->cleanStr($etab['secteur_ens']      ?? null);
        $statutOrg = $this->cleanStr($etab['statut_org']       ?? null);
        $milieu    = $this->cleanStr($etab['milieu']           ?? null);

        $cp  = isset($etab['code_province']) ? (int)$etab['code_province'] : null;
        $cc  = isset($etab['code_commune'])  ? (int)$etab['code_commune']  : null;
        $ccl = isset($etab['code_colline'])  ? (int)$etab['code_colline']  : null;

        $csec  = isset($etab['code_type_secteur_ens']) ? (int)$etab['code_type_secteur_ens'] : null;
        $cstat = isset($etab['code_type_statut_org'])  ? (int)$etab['code_type_statut_org']  : null;
        $cmil  = isset($etab['code_type_milieu'])      ? (int)$etab['code_type_milieu']      : null;

        // Chaîne de localisation : Province / Commune / Colline / Nom
        $nom    = $this->cleanStr($etab['nom_etablissement'] ?? '') ?? '';
        $chaine = $etab['chaine_localisation']
            ?? implode(' / ', array_filter([$province, $commune, $colline, $nom]));

        // ── Upsert dans etablissements_miroir ─────────────────────────────────
        $sql = "
            INSERT INTO etablissements_miroir (
                code_etablissement, nom_etablissement,
                province, commune, colline, chaine_localisation,
                code_province, code_commune, code_colline,
                code_type_milieu, code_type_statut_org, code_type_secteur_ens,
                secteur_ens, statut_org, milieu,
                source, synced_at, actif
            ) VALUES (
                :code, :nom,
                :province, :commune, :colline, :chaine,
                :cp, :cc, :ccl,
                :cmil, :cstat, :csec,
                :secteur_ens, :statut_org, :milieu,
                :source, NOW(), 1
            )
            ON DUPLICATE KEY UPDATE
                nom_etablissement = IF(:source2 = 'api_stateduc' OR source != 'api_stateduc', VALUES(nom_etablissement), nom_etablissement),
                province          = IF(:source3 = 'api_stateduc' OR source != 'api_stateduc', VALUES(province), province),
                commune           = IF(:source4 = 'api_stateduc' OR source != 'api_stateduc', VALUES(commune), commune),
                colline           = VALUES(colline),
                chaine_localisation = VALUES(chaine_localisation),
                code_province     = COALESCE(VALUES(code_province), code_province),
                code_commune      = COALESCE(VALUES(code_commune),  code_commune),
                code_colline      = COALESCE(VALUES(code_colline),  code_colline),
                code_type_milieu  = COALESCE(VALUES(code_type_milieu), code_type_milieu),
                code_type_statut_org  = COALESCE(VALUES(code_type_statut_org), code_type_statut_org),
                code_type_secteur_ens = COALESCE(VALUES(code_type_secteur_ens), code_type_secteur_ens),
                secteur_ens       = COALESCE(VALUES(secteur_ens), secteur_ens),
                statut_org        = COALESCE(VALUES(statut_org),  statut_org),
                milieu            = COALESCE(VALUES(milieu),      milieu),
                synced_at         = NOW(),
                source            = IF(:source5 = 'api_stateduc', 'api_stateduc', source),
                actif             = 1
        ";

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':code'        => $code,
            ':nom'         => $nom,
            ':province'    => $province,
            ':commune'     => $commune,
            ':colline'     => $colline,
            ':chaine'      => $this->cleanStr($chaine),
            ':cp'          => $cp,
            ':cc'          => $cc,
            ':ccl'         => $ccl,
            ':cmil'        => $cmil,
            ':cstat'       => $cstat,
            ':csec'        => $csec,
            ':secteur_ens' => $secteurEns,
            ':statut_org'  => $statutOrg,
            ':milieu'      => $milieu,
            ':source'      => $source,
            ':source2'     => $source,
            ':source3'     => $source,
            ':source4'     => $source,
            ':source5'     => $source,
        ]);

        $wasUpdated = $stmt->rowCount() > 1;

        // ── Alimentation ref_province / ref_commune / ref_colline ─────────────
        // On upsert les libellés géographiques pour les cascades déconnectées.
        if ($cp && $province) {
            $pdo->prepare("
                INSERT INTO ref_province (code_province, libelle)
                VALUES (:cp, :lib)
                ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)
            ")->execute([':cp' => $cp, ':lib' => $province]);
        }
        if ($cc && $cp && $commune) {
            $pdo->prepare("
                INSERT INTO ref_commune (code_commune, code_province, libelle)
                VALUES (:cc, :cp, :lib)
                ON DUPLICATE KEY UPDATE libelle = VALUES(libelle), code_province = VALUES(code_province)
            ")->execute([':cc' => $cc, ':cp' => $cp, ':lib' => $commune]);
        }
        if ($ccl && $cc && $cp && $colline) {
            $pdo->prepare("
                INSERT INTO ref_colline (code_colline, code_commune, code_province, libelle)
                VALUES (:ccl, :cc, :cp, :lib)
                ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)
            ")->execute([':ccl' => $ccl, ':cc' => $cc, ':cp' => $cp, ':lib' => $colline]);
        }

        return $wasUpdated ? 'updated' : 'inserted';
    }

    /**
     * INSERT-ONLY dans etablissements_miroir (session10 — mode importation).
     *
     * Insère un établissement sans ON DUPLICATE KEY UPDATE.
     * Peuple aussi ref_province / ref_commune / ref_colline.
     * Lance une exception si le code existe déjà (la couche appelante filtre
     * en amont via le cache $existingCodes, donc ça ne devrait pas arriver).
     */
    private function insertEtablissement(array $etab, string $source): void
    {
        $code = (int)($etab['code_etablissement'] ?? 0);
        if ($code <= 0) throw new \InvalidArgumentException("code_etablissement invalide");

        $province   = $this->cleanStr($etab['province']         ?? null);
        $commune    = $this->cleanStr($etab['commune']          ?? null);
        $colline    = $this->cleanStr($etab['colline']          ?? null);
        $secteurEns = $this->cleanStr($etab['secteur_ens']      ?? null);
        $statutOrg  = $this->cleanStr($etab['statut_org']       ?? null);
        $milieu     = $this->cleanStr($etab['milieu']           ?? null);
        $nom        = $this->cleanStr($etab['nom_etablissement'] ?? '') ?? '';

        $cp   = isset($etab['code_province'])         ? (int)$etab['code_province']         : null;
        $cc   = isset($etab['code_commune'])           ? (int)$etab['code_commune']           : null;
        $ccl  = isset($etab['code_colline'])           ? (int)$etab['code_colline']           : null;
        $csec = isset($etab['code_type_secteur_ens']) ? (int)$etab['code_type_secteur_ens'] : null;
        $cstat= isset($etab['code_type_statut_org'])  ? (int)$etab['code_type_statut_org']  : null;
        $cmil = isset($etab['code_type_milieu'])      ? (int)$etab['code_type_milieu']      : null;

        $chaine = $etab['chaine_localisation']
            ?? implode(' / ', array_filter([$province, $commune, $colline, $nom]));

        $pdo = Database::getInstance();

        $pdo->prepare("
            INSERT INTO etablissements_miroir (
                code_etablissement, nom_etablissement,
                province, commune, colline, chaine_localisation,
                code_province, code_commune, code_colline,
                code_type_milieu, code_type_statut_org, code_type_secteur_ens,
                secteur_ens, statut_org, milieu,
                source, synced_at, actif
            ) VALUES (
                :code, :nom,
                :province, :commune, :colline, :chaine,
                :cp, :cc, :ccl,
                :cmil, :cstat, :csec,
                :secteur_ens, :statut_org, :milieu,
                :source, NOW(), 1
            )
        ")->execute([
            ':code'        => $code,
            ':nom'         => $nom,
            ':province'    => $province,
            ':commune'     => $commune,
            ':colline'     => $colline,
            ':chaine'      => $this->cleanStr($chaine),
            ':cp'          => $cp,
            ':cc'          => $cc,
            ':ccl'         => $ccl,
            ':cmil'        => $cmil,
            ':cstat'       => $cstat,
            ':csec'        => $csec,
            ':secteur_ens' => $secteurEns,
            ':statut_org'  => $statutOrg,
            ':milieu'      => $milieu,
            ':source'      => $source,
        ]);

        // Alimenter ref_province / ref_commune / ref_colline
        if ($cp && $province) {
            $pdo->prepare("
                INSERT INTO ref_province (code_province, libelle)
                VALUES (:cp, :lib)
                ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)
            ")->execute([':cp' => $cp, ':lib' => $province]);
        }
        if ($cc && $cp && $commune) {
            $pdo->prepare("
                INSERT INTO ref_commune (code_commune, code_province, libelle)
                VALUES (:cc, :cp, :lib)
                ON DUPLICATE KEY UPDATE libelle = VALUES(libelle), code_province = VALUES(code_province)
            ")->execute([':cc' => $cc, ':cp' => $cp, ':lib' => $commune]);
        }
        if ($ccl && $cc && $cp && $colline) {
            $pdo->prepare("
                INSERT INTO ref_colline (code_colline, code_commune, code_province, libelle)
                VALUES (:ccl, :cc, :cp, :lib)
                ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)
            ")->execute([':ccl' => $ccl, ':cc' => $cc, ':cp' => $cp, ':lib' => $colline]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LECTURE EXCEL (format ATLAS_COLLINE / FICHIER_ETAB.xlsx)
    // ══════════════════════════════════════════════════════════════════════════

    private function readExcelWithSpreadsheet(string $path): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        // FICHIER_ETAB.xlsx n'a qu'une feuille (Feuil1 ou active)
        $spreadsheet = $reader->load($path);
        $ws = $spreadsheet->getActiveSheet();
        $rows = [];
        $headers = [];
        foreach ($ws->getRowIterator() as $i => $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getCalculatedValue();
            }
            if ($i === 1) { $headers = $cells; continue; }
            if (empty(array_filter($cells))) continue;
            $rows[] = array_combine(array_slice($headers, 0, count($cells)), $cells);
        }
        return $rows;
    }

    /**
     * Lecteur natif PHP pour les fichiers .xlsx (format OOXML).
     *
     * Un fichier .xlsx est une archive ZIP contenant :
     *   xl/sharedStrings.xml  — index de toutes les chaînes partagées
     *   xl/worksheets/sheet1.xml — données de la première feuille
     *
     * Fonctionne sans openpyxl, sans python3, sans PhpSpreadsheet.
     * Compatible Windows/Linux/Mac — ZipArchive et SimpleXML sont
     * inclus dans PHP en standard depuis PHP 5.2.
     *
     * @param  string $path  Chemin absolu vers le fichier .xlsx
     * @return array         Tableau de lignes associatives [NOM_COLONNE => valeur]
     * @throws RuntimeException si le fichier ne peut pas être ouvert/parsé
     */
    private function readExcelNative(string $path): array
    {
        // ── 1. Ouvrir l'archive ZIP ───────────────────────────────────────────
        $zip = new \ZipArchive();
        $res = $zip->open($path);
        if ($res !== true) {
            throw new \RuntimeException(
                "Impossible d'ouvrir le fichier xlsx (ZipArchive code $res) : $path"
            );
        }

        // ── 2. Lire sharedStrings.xml ─────────────────────────────────────────
        // Les cellules de type "s" (string) référencent cet index (0-based).
        $sharedStrings = [];
        $ssRaw = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssRaw !== false && $ssRaw !== '') {
            // Désactiver les erreurs XML internes, on gérera manuellement
            $prevXmlErrors = libxml_use_internal_errors(true);
            $ss = simplexml_load_string($ssRaw);
            libxml_use_internal_errors($prevXmlErrors);
            if ($ss !== false) {
                foreach ($ss->si as $si) {
                    // Chaque <si> peut contenir soit <t> soit plusieurs <r><t>
                    // (rich text). On concatène tous les <t> enfants.
                    $text = '';
                    if (isset($si->t)) {
                        $text = (string)$si->t;
                    } else {
                        // Rich text : <r><t>...</t></r>
                        foreach ($si->r as $r) {
                            if (isset($r->t)) {
                                $text .= (string)$r->t;
                            }
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        // ── 3. Trouver le nom réel de la première feuille ─────────────────────
        // workbook.xml liste les feuilles, sheet1.xml n'est pas toujours le
        // bon nom (certains exports placent la feuille dans sheetN.xml).
        $sheetFile = 'xl/worksheets/sheet1.xml'; // défaut
        $wbRaw = $zip->getFromName('xl/workbook.xml');
        if ($wbRaw !== false) {
            $prevXmlErrors = libxml_use_internal_errors(true);
            $wb = simplexml_load_string($wbRaw);
            libxml_use_internal_errors($prevXmlErrors);
            if ($wb !== false) {
                // Récupérer le rId de la première feuille dans workbook.xml
                $sheets = $wb->sheets->sheet ?? [];
                $firstRId = '';
                if (isset($sheets[0])) {
                    $rels = $sheets[0]->attributes('r', true);
                    if ($rels && isset($rels['id'])) {
                        $firstRId = (string)$rels['id'];
                    }
                }
                // Résoudre le rId via workbook.xml.rels
                if ($firstRId !== '') {
                    $relsRaw = $zip->getFromName('xl/_rels/workbook.xml.rels');
                    if ($relsRaw !== false) {
                        $prevXmlErrors = libxml_use_internal_errors(true);
                        $relsXml = simplexml_load_string($relsRaw);
                        libxml_use_internal_errors($prevXmlErrors);
                        if ($relsXml !== false) {
                            foreach ($relsXml->Relationship as $rel) {
                                if ((string)$rel['Id'] === $firstRId) {
                                    $target = (string)$rel['Target'];
                                    // Target peut être "worksheets/sheet1.xml" ou absolu
                                    if (strpos($target, '/') === 0) {
                                        $sheetFile = ltrim($target, '/');
                                    } else {
                                        $sheetFile = 'xl/' . $target;
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        // ── 4. Lire la feuille ────────────────────────────────────────────────
        $sheetRaw = $zip->getFromName($sheetFile);
        $zip->close();

        if ($sheetRaw === false || $sheetRaw === '') {
            throw new \RuntimeException(
                "Feuille introuvable dans le xlsx ($sheetFile) — archive corrompue ?"
            );
        }

        $prevXmlErrors = libxml_use_internal_errors(true);
        $sheet = simplexml_load_string($sheetRaw);
        libxml_use_internal_errors($prevXmlErrors);
        if ($sheet === false) {
            throw new \RuntimeException("Impossible de parser la feuille XML du xlsx.");
        }

        // ── 5. Parser les lignes/cellules ─────────────────────────────────────
        // Namespaces OOXML — on cherche <sheetData><row><c r="A1" t="s"><v>
        $ns = $sheet->getNamespaces(true);
        // Certains xlsx n'ont pas de namespace, d'autres ont 'xmlns' comme default
        $sheetData = $sheet->sheetData ?? $sheet->children(array_values($ns)[0] ?? '')->sheetData ?? null;
        if ($sheetData === null) {
            // Essai sans namespace
            $sheetData = $sheet->sheetData;
        }

        $rawRows = [];
        $sourceRows = ($sheetData !== null) ? $sheetData->row : $sheet->xpath('//row');
        if ($sourceRows === null || count($sourceRows) === 0) {
            // Tentative xpath générique
            $sourceRows = $sheet->xpath('//*[local-name()="row"]') ?: [];
        }

        foreach ($sourceRows as $rowEl) {
            // Extraire l'index de ligne (attribut r)
            $rowIdx = (int)($rowEl['r'] ?? 0);

            $cells = [];
            $cellEls = $rowEl->c ?? $rowEl->xpath('*[local-name()="c"]') ?? [];
            foreach ($cellEls as $c) {
                // Référence de cellule ex: "A1", "B2"
                $ref  = (string)($c['r'] ?? '');
                $type = (string)($c['t'] ?? ''); // s=sharedString, n=number, b=bool, str=formula
                $v    = isset($c->v) ? (string)$c->v : null;

                // Valeur réelle
                if ($v !== null) {
                    if ($type === 's') {
                        // Index dans sharedStrings
                        $v = $sharedStrings[(int)$v] ?? '';
                    } elseif ($type === 'b') {
                        $v = ($v === '1') ? 'TRUE' : 'FALSE';
                    } elseif ($type === 'str' || $type === 'inlineStr') {
                        // Formule ou inline string — garder la valeur calculée
                        if (isset($c->is->t)) {
                            $v = (string)$c->is->t;
                        }
                        // sinon $v contient déjà la valeur calculée
                    }
                    // Type numérique ou vide : $v reste tel quel
                }

                // Extraire l'indice de colonne depuis la référence "A1" → col 0
                $colLetter = preg_replace('/[0-9]/', '', $ref);
                $colIdx = self::colLetterToIndex($colLetter);
                $cells[$colIdx] = $v;
            }

            if (!empty($cells)) {
                $rawRows[$rowIdx] = $cells;
            }
        }

        if (empty($rawRows)) {
            return [];
        }

        // ── 6. Construire le tableau associatif ───────────────────────────────
        ksort($rawRows); // s'assurer que les lignes sont dans l'ordre
        $rowIndices = array_keys($rawRows);
        $firstIdx   = $rowIndices[0];

        // Ligne 1 = entêtes
        $headerRow = $rawRows[$firstIdx];
        ksort($headerRow);
        $headers = [];
        foreach ($headerRow as $ci => $val) {
            $headers[$ci] = ($val !== null && $val !== '') ? strtoupper(trim((string)$val)) : "COL_$ci";
        }

        // Lignes suivantes = données
        $result = [];
        foreach ($rowIndices as $ri) {
            if ($ri === $firstIdx) continue;
            $cellMap = $rawRows[$ri];
            // Vérifier si la ligne est vide
            $nonNull = array_filter($cellMap, fn($v) => $v !== null && $v !== '');
            if (empty($nonNull)) continue;

            $assoc = [];
            foreach ($headers as $ci => $hdr) {
                $assoc[$hdr] = $cellMap[$ci] ?? null;
            }
            $result[] = $assoc;
        }

        return $result;
    }

    /**
     * Convertit une lettre de colonne Excel (A, B, …, Z, AA, …) en indice 0-based.
     * Ex: A→0, B→1, Z→25, AA→26
     */
    private static function colLetterToIndex(string $col): int
    {
        $col = strtoupper($col);
        $idx = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $idx - 1; // 0-based
    }

    /**
     * Normalise une ligne Excel (ATLAS_COLLINE, 14 colonnes) au format interne.
     * Colonnes Excel :
     *   CODE_PROVINCE, PROVINCE, CODE_COMMUNE, COMMUNE, CODE_COLLINE, COLLINE,
     *   CODE_TYPE_SECTEUR_ENS, SECTEUR_ENS, CODE_TYPE_STATUT_ORG, STATUT,
     *   NOM_ETAB, CODE_ETABLISSEMENT, CODE_TYPE_MILIEU, MILIEU
     */
    private function normalizeExcelRow(array $row): array
    {
        $str = fn($v) => ($v === null || $v === 'NULL' || $v === 'None' || trim((string)$v) === '') ? null : trim((string)$v);
        $int = fn($v) => ($v === null || $v === 'NULL' || $v === 'None' || $v === '' || $v === '0') ? null : (int)$v;

        return [
            // Clé primaire
            'code_etablissement'   => (int)($row['CODE_ETABLISSEMENT'] ?? 0),
            'nom_etablissement'    => $str($row['NOM_ETAB'] ?? ($row['NOM_ETABLISSEMENT'] ?? null)) ?? '',

            // Codes géographiques entiers (ATLAS_COLLINE)
            'code_province'        => $int($row['CODE_PROVINCE']  ?? null),
            'code_commune'         => $int($row['CODE_COMMUNE']   ?? null),
            'code_colline'         => $int($row['CODE_COLLINE']   ?? null),

            // Libellés géographiques
            'province'             => $str($row['PROVINCE']  ?? null),
            'commune'              => $str($row['COMMUNE']   ?? null),
            'colline'              => $str($row['COLLINE']   ?? null),

            // Codes et libellés pédagogiques/administratifs
            'code_type_secteur_ens' => $int($row['CODE_TYPE_SECTEUR_ENS'] ?? null),
            'secteur_ens'           => $str($row['SECTEUR_ENS'] ?? null),
            'code_type_statut_org'  => $int($row['CODE_TYPE_STATUT_ORG']  ?? null),
            'statut_org'            => $str($row['STATUT'] ?? ($row['STATUT_ORG'] ?? null)),
            'code_type_milieu'      => $int($row['CODE_TYPE_MILIEU']       ?? null),
            'milieu'                => $str($row['MILIEU'] ?? null),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GESTION DU JOURNAL DE SYNCHRONISATION
    // ══════════════════════════════════════════════════════════════════════════

    private function startSyncLog(string $sourceType, ?string $triggeredBy): int
    {
        Database::query(
            "INSERT INTO sync_log (source_type, started_at, status, triggered_by)
             VALUES (?, NOW(), 'running', ?)",
            [$sourceType, $triggeredBy]
        );
        return (int)Database::lastInsertId();
    }

    private function updateSyncLog(int $id, array $summary): void
    {
        Database::query(
            "UPDATE sync_log SET inserted=?, updated=?, errors=?, last_page=? WHERE id=?",
            [$summary['inserted'], $summary['updated'], $summary['errors'],
             $summary['pages_done'] ?? null, $id]
        );
    }

    private function finishSyncLog(int $id, string $status, array $summary, array $errors): void
    {
        $det = empty($errors) ? null : json_encode(array_slice($errors, 0, 100), JSON_UNESCAPED_UNICODE);
        Database::query(
            "UPDATE sync_log
             SET status=?, ended_at=NOW(), total_records=?, inserted=?, updated=?, errors=?, details=?
             WHERE id=?",
            [$status, $summary['total'], $summary['inserted'], $summary['updated'],
             $summary['errors'], $det, $id]
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UTILITAIRES
    // ══════════════════════════════════════════════════════════════════════════

    private function cleanStr(?string $s): ?string
    {
        if ($s === null || $s === 'NULL' || $s === 'None') return null;
        $s = mb_convert_encoding(trim($s), 'UTF-8', 'UTF-8,ISO-8859-1');
        return $s === '' ? null : $s;
    }
}
