<?php
/**
 * app_fie/services/SyncService.php
 * ══════════════════════════════════════════════════════════════════════════════
 * Service de synchronisation des établissements.
 *
 * Sources :
 *   1. API StatEduc (source de vérité) — mode NORMAL
 *   2. Fichier Excel infos_etab_bu.xlsx — mode DÉGRADÉ / BOOTSTRAP
 *
 * Dans les deux cas, l'upsert est idempotent par CODE_ETABLISSEMENT.
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
    // SYNC DEPUIS L'API STATEDUC
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Synchronisation complète ou incrémentale depuis l'API StatEduc.
     *
     * @param string|null $updatedSince  Date ISO 'YYYY-MM-DD' pour mode incrémental
     * @param int|null    $secteur       Filtrer par CODE_TYPE_SECTEUR_ENS
     * @param string|null $province      Filtrer par province
     * @param string|null $triggeredBy   Identité du déclencheur (login ou 'cron')
     * @return array  Résumé : ['inserted'=>N, 'updated'=>N, 'errors'=>N, 'total'=>N]
     */
    public function syncFromApi(
        ?string $updatedSince = null,
        ?int    $secteur      = null,
        ?string $province     = null,
        ?string $triggeredBy  = 'system'
    ): array {
        $summary = ['inserted' => 0, 'updated' => 0, 'errors' => 0, 'total' => 0, 'pages_done' => 0];
        $errorDetails = [];

        // Démarrage du log (non bloquant : si la table sync_log est absente, on continue)
        try {
            $this->syncLogId = $this->startSyncLog('api_stateduc', $triggeredBy);
        } catch (Throwable $eLog) {
            $this->logger->warning("sync_log indisponible (table absente ?) : " . $eLog->getMessage());
            $this->syncLogId = 0;
        }

        try {
            // Test de connectivité — ping() capte l'erreur dans lastError
            if (!$this->apiClient->ping()) {
                $pingErr = $this->apiClient->lastError ?: 'connexion refusée ou timeout';
                throw new RuntimeException('API StatEduc inaccessible : ' . $pingErr);
            }

            $page = 1;
            do {
                $params = ['page' => $page, 'per_page' => STATEDUC_SYNC_PAGE_SIZE];
                if ($updatedSince) $params['updated_since'] = $updatedSince;
                if ($secteur)      $params['secteur']       = $secteur;
                if ($province)     $params['province']      = $province;

                $data = $this->apiClient->getEtablissements($params);
                if (!$data) break;

                $totalPages = (int)($data['pages'] ?? 1);
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
            $this->logger->info("Sync API terminée : {$summary['total']} etabs, "
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
    // IMPORT EXCEL (mode dégradé / bootstrap)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Importe les établissements depuis le fichier Excel infos_etab_bu.xlsx.
     * Idempotent : si un CODE_ETABLISSEMENT existe déjà (source=api_stateduc),
     * l'Excel ne l'écrase PAS (la source API est prioritaire).
     *
     * @param string $xlsxPath  Chemin absolu vers infos_etab_bu.xlsx
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

        // Utilise PhpSpreadsheet si disponible, sinon tente la lecture native
        if (class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            $rows = $this->readExcelWithSpreadsheet($xlsxPath);
        } else {
            // Fallback : lecture par script Python en CLI
            $rows = $this->readExcelPythonFallback($xlsxPath);
        }

        $summary['total'] = count($rows);

        Database::beginTransaction();
        try {
            foreach ($rows as $row) {
                $code = (int)($row['CODE_ETABLISSEMENT'] ?? 0);
                if ($code <= 0) continue;

                // Construire le tableau normalisé
                $etab = $this->normalizeExcelRow($row);

                try {
                    // Si déjà en base avec source=api_stateduc → ne pas écraser
                    $existing = Database::fetchOne(
                        "SELECT source FROM etablissements_miroir WHERE code_etablissement = ?",
                        [$code]
                    );
                    if ($existing && $existing['source'] === 'api_stateduc') {
                        // API a priorité sur Excel
                        continue;
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
        $this->logger->info("Import Excel terminé : {$summary['total']} lignes, "
            . "+{$summary['inserted']} insérés, {$summary['updated']} MàJ");
        return $summary;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UPSERT IDEMPOTENT
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * INSERT OR UPDATE un établissement dans etablissements_miroir.
     * Retourne 'inserted' ou 'updated'.
     */
    private function upsertEtablissement(array $etab, string $source): string
    {
        $code = (int)($etab['code_etablissement'] ?? 0);
        if ($code <= 0) throw new InvalidArgumentException("code_etablissement invalide");

        // Extraire localisation (API → structure imbriquée ; Excel → à plat)
        $province = null; $commune = null; $zone = null; $colline = null;
        $cp = null; $cc = null; $cz = null; $ccl = null;
        if (isset($etab['localisation'])) {
            $loc     = $etab['localisation'];
            $province = $loc['province']['libelle'] ?? null;
            $commune  = $loc['commune']['libelle']  ?? null;
            $zone     = $loc['zone']['libelle']     ?? null;
            $colline  = $loc['colline']['libelle']  ?? null;
            $cp       = $loc['province']['code']    ?? null;
            $cc       = $loc['commune']['code']     ?? null;
            $cz       = $loc['zone']['code']        ?? null;
            $ccl      = $loc['colline']['code']     ?? null;
        } else {
            $province = $etab['province']   ?? null;
            $commune  = $etab['commune']    ?? null;
            $zone     = $etab['zone_admin'] ?? null;
            $colline  = $etab['colline']    ?? null;
        }
        $chaine = $etab['chaine_localisation'] ?? implode(' / ', array_filter([$province, $commune, $zone, $colline]));
        $typo   = $etab['typologie'] ?? [];

        $sql = "
            INSERT INTO etablissements_miroir (
                code_etablissement, nom_etablissement,
                province, commune, zone_admin, colline, chaine_localisation,
                code_province, code_commune, code_zone, code_colline,
                code_type_milieu, code_type_statut_org, code_type_secteur_ens,
                code_type_fonction, code_type_etablissement, code_type_etat_fonct,
                code_ecole_pays, code_etablissement_parent,
                telephone, adresse_electronique, responsable_ecole, annee_creation,
                source, synced_at, stateduc_updated_at, actif
            ) VALUES (
                :code, :nom,
                :province, :commune, :zone, :colline, :chaine,
                :cp, :cc, :cz, :ccl,
                :milieu, :statut, :secteur, :fonction, :type_etab, :etat_fonct,
                :code_ecole, :parent,
                :tel, :email, :resp, :annee,
                :source, NOW(), :stateduc_updated_at, 1
            )
            ON DUPLICATE KEY UPDATE
                nom_etablissement       = IF(:source2 = 'api_stateduc' OR source != 'api_stateduc', VALUES(nom_etablissement), nom_etablissement),
                province                = IF(:source3 = 'api_stateduc' OR source != 'api_stateduc', VALUES(province), province),
                commune                 = IF(:source4 = 'api_stateduc' OR source != 'api_stateduc', VALUES(commune), commune),
                zone_admin              = VALUES(zone_admin),
                colline                 = VALUES(colline),
                chaine_localisation     = VALUES(chaine_localisation),
                code_province           = COALESCE(VALUES(code_province), code_province),
                code_commune            = COALESCE(VALUES(code_commune), code_commune),
                code_zone               = COALESCE(VALUES(code_zone), code_zone),
                code_colline            = COALESCE(VALUES(code_colline), code_colline),
                code_type_milieu        = COALESCE(VALUES(code_type_milieu), code_type_milieu),
                code_type_statut_org    = COALESCE(VALUES(code_type_statut_org), code_type_statut_org),
                code_type_secteur_ens   = COALESCE(VALUES(code_type_secteur_ens), code_type_secteur_ens),
                code_type_etat_fonct    = VALUES(code_type_etat_fonct),
                code_ecole_pays         = COALESCE(VALUES(code_ecole_pays), code_ecole_pays),
                responsable_ecole       = COALESCE(VALUES(responsable_ecole), responsable_ecole),
                synced_at               = NOW(),
                source                  = IF(:source5 = 'api_stateduc', 'api_stateduc', source),
                stateduc_updated_at     = IF(:source6 = 'api_stateduc', VALUES(stateduc_updated_at), stateduc_updated_at),
                actif                   = 1
        ";

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':code'                => $code,
            ':nom'                 => $this->cleanStr($etab['nom_etablissement'] ?? ''),
            ':province'            => $this->cleanStr($province),
            ':commune'             => $this->cleanStr($commune),
            ':zone'                => $this->cleanStr($zone),
            ':colline'             => $this->cleanStr($colline),
            ':chaine'              => $this->cleanStr($chaine),
            ':cp'                  => $cp  ? (int)$cp  : null,
            ':cc'                  => $cc  ? (int)$cc  : null,
            ':cz'                  => $cz  ? (int)$cz  : null,
            ':ccl'                 => $ccl ? (int)$ccl : null,
            ':milieu'              => $typo['code_type_milieu']        ?? ($etab['code_type_milieu'] ?? null),
            ':statut'              => $typo['code_type_statut_org']    ?? ($etab['code_type_statut_org'] ?? null),
            ':secteur'             => $typo['code_type_secteur_ens']   ?? ($etab['code_type_secteur_ens'] ?? null),
            ':fonction'            => $typo['code_type_fonction']      ?? ($etab['code_type_fonction'] ?? null),
            ':type_etab'           => $typo['code_type_etablissement'] ?? ($etab['code_type_etablissement'] ?? null),
            ':etat_fonct'          => $typo['code_type_etat_fonct']    ?? ($etab['code_type_etat_fonct'] ?? null),
            ':code_ecole'          => $etab['code_ecole_pays']         ?? null,
            ':parent'              => $etab['code_etablissement_parent'] ?? null,
            ':tel'                 => $etab['telephone']               ?? null,
            ':email'               => $etab['adresse_electronique']    ?? null,
            ':resp'                => $this->cleanStr($etab['responsable_ecole'] ?? null),
            ':annee'               => $etab['annee_creation']          ?? null,
            ':source'              => $source,
            ':stateduc_updated_at' => null, // sera complété si API fournit une date
            ':source2'             => $source,
            ':source3'             => $source,
            ':source4'             => $source,
            ':source5'             => $source,
            ':source6'             => $source,
        ]);

        // rowCount() > 1 = UPDATE (MySQL INSERT...ON DUPLICATE KEY UPDATE renvoie 2 pour update)
        return $stmt->rowCount() > 1 ? 'updated' : 'inserted';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // LECTURE EXCEL
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Lit le fichier Excel via PhpSpreadsheet.
     */
    private function readExcelWithSpreadsheet(string $path): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setLoadSheetsOnly(['etab']);
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
     * Fallback Python pour lire l'Excel (openpyxl).
     */
    private function readExcelPythonFallback(string $path): array
    {
        $jsonPath = tempnam(sys_get_temp_dir(), 'fie_etab_') . '.json';
        $escaped  = escapeshellarg($path);
        $escJson  = escapeshellarg($jsonPath);
        $cmd = "python3 -c \"
import openpyxl, json, sys
wb = openpyxl.load_workbook($escaped, read_only=True, data_only=True)
ws = wb['etab'] if 'etab' in wb.sheetnames else wb.active
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
     * Normalise une ligne Excel au format attendu par upsertEtablissement.
     */
    private function normalizeExcelRow(array $row): array
    {
        $null_str = fn($v) => ($v === null || $v === 'NULL' || $v === 'None' || $v === '') ? null : (string)$v;
        $null_int = fn($v) => ($v === null || $v === 'NULL' || $v === 'None' || $v === '' || $v === '0') ? null : (int)$v;

        return [
            'code_etablissement'        => (int)($row['CODE_ETABLISSEMENT'] ?? 0),
            'nom_etablissement'         => $null_str($row['NOM_ETABLISSEMENT'] ?? null) ?? '',
            'province'                  => $null_str($row['PROVINCE']          ?? null),
            'commune'                   => $null_str($row['COMMUNE']           ?? null),
            'zone_admin'                => $null_str($row['ZONE']              ?? null),
            'colline'                   => $null_str($row['COLLINE']           ?? null),
            'code_type_milieu'          => $null_int($row['CODE_TYPE_MILIEU']        ?? null),
            'code_type_statut_org'      => $null_int($row['CODE_TYPE_STATUT_ORG']    ?? null),
            'code_type_secteur_ens'     => $null_int($row['CODE_TYPE_SECTEUR_ENS']   ?? null),
            'code_type_fonction'        => $null_int($row['CODE_TYPE_FONCTION']      ?? null),
            'code_type_etablissement'   => $null_int($row['CODE_TYPE_ETABLISSEMENT'] ?? null),
            'code_type_etat_fonct'      => $null_int($row['CODE_TYPE_ETAT_FONCT']    ?? null),
            'code_ecole_pays'           => $null_str($row['CODE_ECOLE_PAYS']   ?? null),
            'code_etablissement_parent' => $null_int($row['CODE_ETABLISSEMENT_PARENT'] ?? null),
            'telephone'                 => $null_str($row['TELEPHONE']         ?? null),
            'adresse_electronique'      => $null_str($row['ADRESSE_ELECTRONIQUE'] ?? null),
            'responsable_ecole'         => $null_str($row['RESPONSABLE_ECOLE'] ?? null),
            'annee_creation'            => $null_int($row['ANNEE_CREATION']    ?? null),
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
        if ($id <= 0) return;
        try {
            Database::query(
                "UPDATE sync_log SET inserted=?, updated=?, errors=?, last_page=? WHERE id=?",
                [$summary['inserted'], $summary['updated'], $summary['errors'],
                 $summary['pages_done'] ?? null, $id]
            );
        } catch (Throwable $e) {
            $this->logger->warning("updateSyncLog échoué : " . $e->getMessage());
        }
    }

    private function finishSyncLog(int $id, string $status, array $summary, array $errors): void
    {
        if ($id <= 0) return;
        try {
            $det = empty($errors) ? null : json_encode(array_slice($errors, 0, 100), JSON_UNESCAPED_UNICODE);
            Database::query(
                "UPDATE sync_log
                 SET status=?, ended_at=NOW(), total_records=?, inserted=?, updated=?, errors=?, details=?
                 WHERE id=?",
                [$status, $summary['total'], $summary['inserted'], $summary['updated'],
                 $summary['errors'], $det, $id]
            );
        } catch (Throwable $e) {
            $this->logger->warning("finishSyncLog échoué : " . $e->getMessage());
        }
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
