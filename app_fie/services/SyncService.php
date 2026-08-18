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
    // IMPORT EXCEL (mode dégradé / bootstrap)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Importe les établissements depuis FICHIER_ETAB.xlsx (format ATLAS_COLLINE).
     * 14 colonnes : CODE_PROVINCE, PROVINCE, CODE_COMMUNE, COMMUNE,
     *               CODE_COLLINE, COLLINE, CODE_TYPE_SECTEUR_ENS, SECTEUR_ENS,
     *               CODE_TYPE_STATUT_ORG, STATUT, NOM_ETAB,
     *               CODE_ETABLISSEMENT, CODE_TYPE_MILIEU, MILIEU
     *
     * Idempotent : si un CODE_ETABLISSEMENT existe déjà (source=api_stateduc),
     * l'Excel ne l'écrase PAS (la source API est prioritaire).
     *
     * @param string $xlsxPath  Chemin absolu vers FICHIER_ETAB.xlsx
     * @param string $triggeredBy
     * @return array  Résumé
     */
    public function importFromExcel(string $xlsxPath, string $triggeredBy = 'import'): array
    {
        if (!file_exists($xlsxPath)) {
            throw new RuntimeException("Fichier Excel introuvable : $xlsxPath");
        }

        $this->syncLogId = $this->startSyncLog('excel_import', $triggeredBy);
        $summary = ['inserted' => 0, 'updated' => 0, 'errors' => 0, 'total' => 0];
        $errorDetails = [];

        if (class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            $rows = $this->readExcelWithSpreadsheet($xlsxPath);
        } else {
            $rows = $this->readExcelPythonFallback($xlsxPath);
        }

        $summary['total'] = count($rows);

        Database::beginTransaction();
        try {
            foreach ($rows as $row) {
                $code = (int)($row['CODE_ETABLISSEMENT'] ?? 0);
                if ($code <= 0) continue;

                $etab = $this->normalizeExcelRow($row);

                try {
                    $existing = Database::fetchOne(
                        "SELECT source FROM etablissements_miroir WHERE code_etablissement = ?",
                        [$code]
                    );
                    if ($existing && $existing['source'] === 'api_stateduc') {
                        continue; // API a priorité sur Excel
                    }
                    $result = $this->upsertEtablissement($etab, 'excel_import');
                    $summary[$result]++;
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
            . "+{$summary['inserted']} insérés, {$summary['updated']} MàJ");
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
     * Fallback Python pour lire le fichier Excel (openpyxl).
     */
    private function readExcelPythonFallback(string $path): array
    {
        $jsonPath = tempnam(sys_get_temp_dir(), 'fie_etab_') . '.json';
        $escaped  = escapeshellarg($path);
        $escJson  = escapeshellarg($jsonPath);
        $cmd = "python3 -c \"
import openpyxl, json, sys
wb = openpyxl.load_workbook($escaped, read_only=True, data_only=True)
ws = wb.active
rows = list(ws.iter_rows(values_only=True))
headers = [str(h) if h is not None else '' for h in rows[0]]
result = []
for row in rows[1:]:
    if all(v is None for v in row): continue
    d = {headers[i]: (str(row[i]) if row[i] is not None else None) for i in range(min(len(headers),len(row)))}
    result.append(d)
with open($escJson, 'w', encoding='utf-8') as f:
    json.dump(result, f, ensure_ascii=False)
\" 2>&1";
        exec($cmd, $output, $rc);
        if ($rc !== 0 || !file_exists($jsonPath)) {
            throw new RuntimeException("Lecture Excel Python échouée : " . implode("\n", $output));
        }
        $rows = json_decode(file_get_contents($jsonPath), true) ?? [];
        @unlink($jsonPath);
        return $rows;
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
