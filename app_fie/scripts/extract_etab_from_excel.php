<?php
/**
 * app_fie/scripts/extract_etab_from_excel.php
 * ══════════════════════════════════════════════════════════════════════════════
 * Script d'extraction / bootstrap des données de référence.
 *
 * Lit FICHIER_ETAB.xlsx (format ATLAS_COLLINE, 14 colonnes) et peuple :
 *   1. ref_province      — provinces uniques (code_province PK)
 *   2. ref_commune       — communes uniques  (code_commune PK, FK code_province)
 *   3. ref_colline       — collines uniques  (code_colline PK, FK code_commune, code_province)
 *   4. etablissements_miroir — tous les établissements (upsert idempotent)
 *
 * Cascade inscriptions (formulaire capture) :
 *   Province → Commune → Colline → Établissement
 *   Chaque table a les FK nécessaires pour les select2 AJAX de new.php.
 *
 * ── Utilisation ──────────────────────────────────────────────────────────────
 *
 * CLI (depuis XAMPP/Linux) :
 *   php app_fie/scripts/extract_etab_from_excel.php [chemin_xlsx]
 *   php app_fie/scripts/extract_etab_from_excel.php C:\xamppSites\htdocs\app_fie\FICHIER_ETAB.xlsx
 *
 * Web (depuis navigateur) :
 *   http://localhost:8085/app_fie/scripts/extract_etab_from_excel.php
 *   http://localhost:8085/app_fie/scripts/extract_etab_from_excel.php?xlsx=/chemin/vers/FICHIER_ETAB.xlsx
 *
 * Le fichier FICHIER_ETAB.xlsx doit se trouver dans le répertoire racine de
 * app_fie/ (ou être spécifié via $argv[1] ou ?xlsx=).
 *
 * ── Dépendances ──────────────────────────────────────────────────────────────
 * - PHP 7.4+ avec extensions : ZipArchive, PDO (PDO_MySQL)
 * - Aucune dépendance externe (pas d'openpyxl, pas de PhpSpreadsheet)
 *
 * ── Colonnes ATLAS_COLLINE (14) ───────────────────────────────────────────────
 * CODE_PROVINCE, PROVINCE, CODE_COMMUNE, COMMUNE, CODE_COLLINE, COLLINE,
 * CODE_TYPE_SECTEUR_ENS, SECTEUR_ENS, CODE_TYPE_STATUT_ORG, STATUT,
 * NOM_ETAB, CODE_ETABLISSEMENT, CODE_TYPE_MILIEU, MILIEU
 * ══════════════════════════════════════════════════════════════════════════════
 */

declare(strict_types=1);

// ── Détection mode d'exécution ────────────────────────────────────────────────
$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    // Mode web : streaming HTML avec flush immédiat
    header('Content-Type: text/html; charset=utf-8');
    // Désactiver la compression pour le streaming
    @ini_set('zlib.output_compression', '0');
    while (ob_get_level() > 0) { @ob_end_flush(); }
    ob_implicit_flush(true);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
    echo '<title>Extraction FICHIER_ETAB.xlsx</title>';
    echo '<style>body{font-family:monospace;font-size:13px;margin:16px;}';
    echo '.ok{color:green;}.err{color:#c00;}.warn{color:#e67e00;}.info{color:#555;}</style>';
    echo '</head><body>';
    echo '<h2>🗂 Extraction FICHIER_ETAB.xlsx → MySQL</h2>';
    @ob_flush(); @flush();
}

set_time_limit(300); // 5 minutes max pour 11 497 lignes
ini_set('memory_limit', '256M');

// ── Fonctions d'affichage ─────────────────────────────────────────────────────
function out(string $msg, string $type = 'info'): void
{
    global $isCli;
    if ($isCli) {
        $prefix = ['ok' => '✔', 'err' => '✖', 'warn' => '⚠', 'info' => ' '][$type] ?? ' ';
        echo $prefix . ' ' . $msg . PHP_EOL;
    } else {
        $cls = htmlspecialchars($type);
        echo '<span class="' . $cls . '">' . htmlspecialchars($msg) . '</span><br>' . PHP_EOL;
        @ob_flush(); @flush();
    }
}

function outRaw(string $html): void
{
    global $isCli;
    if (!$isCli) { echo $html; @ob_flush(); @flush(); }
}

// ── Résolution du chemin xlsx ─────────────────────────────────────────────────
// Priorité : $argv[1] (CLI) ou ?xlsx= (web) → défaut : racine de app_fie/
$scriptDir = dirname(__DIR__); // app_fie/

if ($isCli && isset($argv[1])) {
    $xlsxPath = $argv[1];
} elseif (!$isCli && !empty($_GET['xlsx'])) {
    $xlsxPath = $_GET['xlsx'];
} else {
    // Chercher dans : app_fie/ | app_fie/../ | répertoire courant
    $candidates = [
        $scriptDir . DIRECTORY_SEPARATOR . 'FICHIER_ETAB.xlsx',
        $scriptDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'FICHIER_ETAB.xlsx',
        getcwd() . DIRECTORY_SEPARATOR . 'FICHIER_ETAB.xlsx',
    ];
    $xlsxPath = '';
    foreach ($candidates as $c) {
        $real = realpath($c);
        if ($real && is_file($real)) { $xlsxPath = $real; break; }
    }
}

if (!$xlsxPath || !is_file($xlsxPath)) {
    out("Fichier FICHIER_ETAB.xlsx introuvable.", 'err');
    out("Placez le fichier dans : $scriptDir/FICHIER_ETAB.xlsx", 'info');
    out("Ou passez le chemin en argument : php extract_etab_from_excel.php /chemin/vers/FICHIER_ETAB.xlsx", 'info');
    if (!$isCli) { echo '</body></html>'; }
    exit(1);
}
out("Fichier : $xlsxPath", 'info');

// ── Chargement de la config DB ────────────────────────────────────────────────
// app_fie/config/config.php définit DB_HOST, DB_NAME, DB_USER, DB_PASS
$configPath = $scriptDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
if (!is_file($configPath)) {
    out("Impossible de trouver config/config.php : $configPath", 'err');
    exit(1);
}

// Définir les constantes minimales pour que config.php s'exécute correctement
if (!defined('APP_ROOT'))    define('APP_ROOT', $scriptDir . DIRECTORY_SEPARATOR);
if (!defined('FIE_VERSION')) define('FIE_VERSION', 'extract_script');

require_once $configPath;

// Tentative de connexion via classe Database si disponible, sinon PDO direct
$pdo = null;
$dbClass = $scriptDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Database.php';
if (is_file($dbClass)) {
    require_once $dbClass;
    // La classe Database utilise une connexion statique — on récupère le PDO interne
    // Alternativement on crée notre propre PDO pour rester indépendant
}

// Connexion PDO directe (plus sûre pour un script batch)
$dbHost = defined('DB_HOST') ? DB_HOST : (defined('FIE_DB_HOST') ? FIE_DB_HOST : 'localhost');
$dbName = defined('DB_NAME') ? DB_NAME : (defined('FIE_DB_NAME') ? FIE_DB_NAME : 'fie_burundi');
$dbUser = defined('DB_USER') ? DB_USER : (defined('FIE_DB_USER') ? FIE_DB_USER : 'root');
$dbPass = defined('DB_PASS') ? DB_PASS : (defined('FIE_DB_PASS') ? FIE_DB_PASS : '');
$dbPort = defined('DB_PORT') ? (int)DB_PORT : 3306;

// Chercher les constantes préfixées FIE_ si les non-préfixées ne sont pas définies
if (!$dbHost || $dbHost === '') $dbHost = 'localhost';

try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ]);
    out("Connexion MySQL OK — $dbName@$dbHost", 'ok');
} catch (PDOException $e) {
    out("Erreur connexion MySQL : " . $e->getMessage(), 'err');
    out("DB : $dbName@$dbHost:$dbPort user=$dbUser", 'info');
    exit(1);
}

// ═══════════════════════════════════════════════════════════════════════════════
// LECTURE XLSX (lecteur natif ZipArchive + SimpleXML)
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Convertit une lettre de colonne Excel (A, B, …, Z, AA, …) en indice 0-based.
 */
function colLetterToIndex(string $col): int
{
    $col = strtoupper($col);
    $idx = 0;
    $len = strlen($col);
    for ($i = 0; $i < $len; $i++) {
        $idx = $idx * 26 + (ord($col[$i]) - ord('A') + 1);
    }
    return $idx - 1;
}

/**
 * Lit un fichier .xlsx et retourne un tableau de lignes associatives.
 * Clés = noms de colonnes (ligne 1 de la feuille, en MAJUSCULES).
 * Valeurs = chaînes ou null.
 *
 * @throws RuntimeException
 */
function readXlsx(string $path): array
{
    $zip = new ZipArchive();
    $res = $zip->open($path);
    if ($res !== true) {
        throw new RuntimeException("Impossible d'ouvrir le xlsx (ZipArchive code $res) : $path");
    }

    // ── sharedStrings ────────────────────────────────────────────────────────
    $sharedStrings = [];
    $ssRaw = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssRaw !== false && $ssRaw !== '') {
        $prev = libxml_use_internal_errors(true);
        $ss   = simplexml_load_string($ssRaw);
        libxml_use_internal_errors($prev);
        if ($ss !== false) {
            foreach ($ss->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } else {
                    $txt = '';
                    foreach ($si->r as $r) {
                        if (isset($r->t)) $txt .= (string)$r->t;
                    }
                    $sharedStrings[] = $txt;
                }
            }
        }
    }

    // ── Premier nom de feuille via workbook.xml ───────────────────────────────
    $sheetFile = 'xl/worksheets/sheet1.xml';
    $wbRaw = $zip->getFromName('xl/workbook.xml');
    if ($wbRaw !== false) {
        $prev = libxml_use_internal_errors(true);
        $wb   = simplexml_load_string($wbRaw);
        libxml_use_internal_errors($prev);
        if ($wb !== false) {
            $sheets   = $wb->sheets->sheet ?? [];
            $firstRId = '';
            if (isset($sheets[0])) {
                $rels = $sheets[0]->attributes('r', true);
                if ($rels && isset($rels['id'])) {
                    $firstRId = (string)$rels['id'];
                }
            }
            if ($firstRId !== '') {
                $relsRaw = $zip->getFromName('xl/_rels/workbook.xml.rels');
                if ($relsRaw !== false) {
                    $prev   = libxml_use_internal_errors(true);
                    $rXml   = simplexml_load_string($relsRaw);
                    libxml_use_internal_errors($prev);
                    if ($rXml !== false) {
                        foreach ($rXml->Relationship as $rel) {
                            if ((string)$rel['Id'] === $firstRId) {
                                $target = (string)$rel['Target'];
                                $sheetFile = (strpos($target, '/') === 0)
                                    ? ltrim($target, '/')
                                    : 'xl/' . $target;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    // ── Feuille de données ────────────────────────────────────────────────────
    $sheetRaw = $zip->getFromName($sheetFile);
    $zip->close();

    if ($sheetRaw === false || $sheetRaw === '') {
        throw new RuntimeException("Feuille introuvable dans le xlsx ($sheetFile).");
    }

    $prev  = libxml_use_internal_errors(true);
    $sheet = simplexml_load_string($sheetRaw);
    libxml_use_internal_errors($prev);
    if ($sheet === false) {
        throw new RuntimeException("Erreur parsing XML de la feuille xlsx.");
    }

    // ── Parser les lignes ─────────────────────────────────────────────────────
    $sourceRows = $sheet->sheetData->row ?? null;
    if ($sourceRows === null || count($sourceRows) === 0) {
        $sourceRows = $sheet->xpath('//*[local-name()="row"]') ?: [];
    }

    $rawRows = [];
    foreach ($sourceRows as $rowEl) {
        $rowIdx  = (int)($rowEl['r'] ?? 0);
        $cells   = [];
        $cellEls = $rowEl->c ?? [];
        foreach ($cellEls as $c) {
            $ref  = (string)($c['r'] ?? '');
            $type = (string)($c['t'] ?? '');
            $v    = isset($c->v) ? (string)$c->v : null;
            if ($v !== null) {
                if ($type === 's') {
                    $v = $sharedStrings[(int)$v] ?? '';
                } elseif ($type === 'b') {
                    $v = ($v === '1') ? 'TRUE' : 'FALSE';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $v = (string)$c->is->t;
                }
            }
            $colLetter = preg_replace('/[0-9]/', '', $ref);
            $colIdx    = colLetterToIndex($colLetter);
            $cells[$colIdx] = $v;
        }
        if (!empty($cells)) {
            $rawRows[$rowIdx] = $cells;
        }
    }

    if (empty($rawRows)) return [];

    ksort($rawRows);
    $rowIndices = array_keys($rawRows);
    $firstIdx   = $rowIndices[0];

    // Ligne 1 = entêtes
    $headerRow = $rawRows[$firstIdx];
    ksort($headerRow);
    $headers = [];
    foreach ($headerRow as $ci => $val) {
        $headers[$ci] = ($val !== null && $val !== '') ? strtoupper(trim((string)$val)) : "COL_$ci";
    }

    // Lignes de données
    $result = [];
    foreach ($rowIndices as $ri) {
        if ($ri === $firstIdx) continue;
        $cellMap = $rawRows[$ri];
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

// ── Lecture du fichier ────────────────────────────────────────────────────────
out("Lecture de FICHIER_ETAB.xlsx...", 'info');
try {
    $rows = readXlsx($xlsxPath);
} catch (RuntimeException $e) {
    out("Erreur lecture xlsx : " . $e->getMessage(), 'err');
    exit(1);
}
$totalRows = count($rows);
out("$totalRows lignes lues.", 'ok');

if ($totalRows === 0) {
    out("Aucune donnée trouvée dans le fichier.", 'err');
    exit(1);
}

// Vérifier les colonnes attendues
$expectedCols = [
    'CODE_PROVINCE', 'PROVINCE', 'CODE_COMMUNE', 'COMMUNE',
    'CODE_COLLINE', 'COLLINE', 'CODE_TYPE_SECTEUR_ENS', 'SECTEUR_ENS',
    'CODE_TYPE_STATUT_ORG', 'STATUT', 'NOM_ETAB', 'CODE_ETABLISSEMENT',
    'CODE_TYPE_MILIEU', 'MILIEU',
];
$firstRow    = $rows[0];
$actualCols  = array_keys($firstRow);
$missingCols = array_diff($expectedCols, $actualCols);
if (!empty($missingCols)) {
    out("Colonnes manquantes : " . implode(', ', $missingCols), 'warn');
    out("Colonnes présentes : " . implode(', ', $actualCols), 'info');
} else {
    out("Colonnes ATLAS_COLLINE (14) vérifiées ✔", 'ok');
}

// ═══════════════════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════════════════
$str = function(?string $v): ?string {
    if ($v === null || $v === 'NULL' || $v === 'None' || trim($v) === '') return null;
    return trim($v);
};
$int = function($v): ?int {
    if ($v === null || $v === '' || $v === 'NULL' || $v === 'None' || $v === '0') return null;
    $i = (int)$v;
    return ($i > 0) ? $i : null;
};

// ═══════════════════════════════════════════════════════════════════════════════
// 1. COLLECTE DES RÉFÉRENTIELS GÉOGRAPHIQUES (Province / Commune / Colline)
// ═══════════════════════════════════════════════════════════════════════════════
out("", 'info');
out("─── Collecte des référentiels géographiques ───────────────────────────────", 'info');

$provinces = []; // code_province => libelle
$communes  = []; // code_commune  => [libelle, code_province]
$collines  = []; // code_colline  => [libelle, code_commune, code_province]

foreach ($rows as $row) {
    $cp  = $int($row['CODE_PROVINCE']  ?? null);
    $cc  = $int($row['CODE_COMMUNE']   ?? null);
    $ccl = $int($row['CODE_COLLINE']   ?? null);
    $lp  = $str($row['PROVINCE']  ?? null);
    $lc  = $str($row['COMMUNE']   ?? null);
    $lcl = $str($row['COLLINE']   ?? null);

    if ($cp !== null && $lp !== null && !isset($provinces[$cp])) {
        $provinces[$cp] = $lp;
    }
    if ($cc !== null && $lc !== null && !isset($communes[$cc])) {
        $communes[$cc] = ['libelle' => $lc, 'code_province' => $cp];
    }
    if ($ccl !== null && $lcl !== null && !isset($collines[$ccl])) {
        $collines[$ccl] = ['libelle' => $lcl, 'code_commune' => $cc, 'code_province' => $cp];
    }
}

out(count($provinces) . " provinces uniques", 'info');
out(count($communes)  . " communes uniques",  'info');
out(count($collines)  . " collines uniques",  'info');

// ═══════════════════════════════════════════════════════════════════════════════
// 2. UPSERT ref_province
// ═══════════════════════════════════════════════════════════════════════════════
out("", 'info');
out("─── Upsert ref_province ──────────────────────────────────────────────────", 'info');

$pdo->beginTransaction();
$stmtProv = $pdo->prepare(
    "INSERT INTO ref_province (code_province, libelle)
     VALUES (:cp, :lib)
     ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)"
);
$provOk = 0; $provErr = 0;
foreach ($provinces as $cp => $libelle) {
    try {
        $stmtProv->execute([':cp' => $cp, ':lib' => $libelle]);
        $provOk++;
    } catch (PDOException $e) {
        $provErr++;
        out("Province $cp : " . $e->getMessage(), 'err');
    }
}
$pdo->commit();
out("Provinces insérées/mises-à-jour : $provOk, erreurs : $provErr", $provErr > 0 ? 'warn' : 'ok');

// ═══════════════════════════════════════════════════════════════════════════════
// 3. UPSERT ref_commune
// ═══════════════════════════════════════════════════════════════════════════════
out("", 'info');
out("─── Upsert ref_commune ───────────────────────────────────────────────────", 'info');

$pdo->beginTransaction();
$stmtComm = $pdo->prepare(
    "INSERT INTO ref_commune (code_commune, code_province, libelle)
     VALUES (:cc, :cp, :lib)
     ON DUPLICATE KEY UPDATE libelle = VALUES(libelle), code_province = VALUES(code_province)"
);
$commOk = 0; $commErr = 0;
foreach ($communes as $cc => $data) {
    try {
        $stmtComm->execute([':cc' => $cc, ':cp' => $data['code_province'], ':lib' => $data['libelle']]);
        $commOk++;
    } catch (PDOException $e) {
        $commErr++;
        out("Commune $cc : " . $e->getMessage(), 'err');
    }
}
$pdo->commit();
out("Communes insérées/mises-à-jour : $commOk, erreurs : $commErr", $commErr > 0 ? 'warn' : 'ok');

// ═══════════════════════════════════════════════════════════════════════════════
// 4. UPSERT ref_colline
// ═══════════════════════════════════════════════════════════════════════════════
out("", 'info');
out("─── Upsert ref_colline ───────────────────────────────────────────────────", 'info');

$pdo->beginTransaction();
$stmtColl = $pdo->prepare(
    "INSERT INTO ref_colline (code_colline, code_commune, code_province, libelle)
     VALUES (:ccl, :cc, :cp, :lib)
     ON DUPLICATE KEY UPDATE libelle = VALUES(libelle),
                             code_commune  = VALUES(code_commune),
                             code_province = VALUES(code_province)"
);
$collOk = 0; $collErr = 0;
foreach ($collines as $ccl => $data) {
    try {
        $stmtColl->execute([
            ':ccl' => $ccl,
            ':cc'  => $data['code_commune'],
            ':cp'  => $data['code_province'],
            ':lib' => $data['libelle'],
        ]);
        $collOk++;
    } catch (PDOException $e) {
        $collErr++;
        out("Colline $ccl : " . $e->getMessage(), 'err');
    }
}
$pdo->commit();
out("Collines insérées/mises-à-jour : $collOk, erreurs : $collErr", $collErr > 0 ? 'warn' : 'ok');

// ═══════════════════════════════════════════════════════════════════════════════
// 5. UPSERT etablissements_miroir
// ═══════════════════════════════════════════════════════════════════════════════
out("", 'info');
out("─── Upsert etablissements_miroir ($totalRows lignes) ─────────────────────", 'info');

// Préparer la requête UPSERT
// ON DUPLICATE KEY UPDATE ne touche PAS aux lignes source=api_stateduc
// (la logique de priorité est gérée en PHP avant l'execute)
$stmtEtab = $pdo->prepare(
    "INSERT INTO etablissements_miroir
        (code_etablissement, nom_etablissement,
         province, commune, colline, chaine_localisation,
         code_province, code_commune, code_colline,
         code_type_milieu, code_type_statut_org, code_type_secteur_ens,
         secteur_ens, statut_org, milieu,
         source, synced_at, actif)
     VALUES
        (:ce, :ne, :prov, :comm, :coll, :chain,
         :cp, :cc, :ccl,
         :ctm, :ctso, :ctse,
         :se, :so, :mil,
         'excel_import', NOW(), 1)
     ON DUPLICATE KEY UPDATE
         nom_etablissement     = IF(source = 'api_stateduc', nom_etablissement,     VALUES(nom_etablissement)),
         province              = IF(source = 'api_stateduc', province,              VALUES(province)),
         commune               = IF(source = 'api_stateduc', commune,               VALUES(commune)),
         colline               = IF(source = 'api_stateduc', colline,               VALUES(colline)),
         chaine_localisation   = IF(source = 'api_stateduc', chaine_localisation,   VALUES(chaine_localisation)),
         code_province         = IF(source = 'api_stateduc', code_province,         VALUES(code_province)),
         code_commune          = IF(source = 'api_stateduc', code_commune,          VALUES(code_commune)),
         code_colline          = IF(source = 'api_stateduc', code_colline,          VALUES(code_colline)),
         code_type_milieu      = IF(source = 'api_stateduc', code_type_milieu,      VALUES(code_type_milieu)),
         code_type_statut_org  = IF(source = 'api_stateduc', code_type_statut_org,  VALUES(code_type_statut_org)),
         code_type_secteur_ens = IF(source = 'api_stateduc', code_type_secteur_ens, VALUES(code_type_secteur_ens)),
         secteur_ens           = IF(source = 'api_stateduc', secteur_ens,           VALUES(secteur_ens)),
         statut_org            = IF(source = 'api_stateduc', statut_org,            VALUES(statut_org)),
         milieu                = IF(source = 'api_stateduc', milieu,                VALUES(milieu)),
         synced_at             = IF(source = 'api_stateduc', synced_at,             NOW()),
         actif                 = IF(source = 'api_stateduc', actif,                 1)"
);

$etabOk  = 0;
$etabErr = 0;
$batchSize = 500; // Commit toutes les 500 lignes pour économiser la mémoire transactionnelle
$inTx = false;

foreach ($rows as $i => $row) {
    $code = (int)($row['CODE_ETABLISSEMENT'] ?? 0);
    if ($code <= 0) { continue; }

    $cp   = $int($row['CODE_PROVINCE']         ?? null);
    $cc   = $int($row['CODE_COMMUNE']          ?? null);
    $ccl  = $int($row['CODE_COLLINE']          ?? null);
    $ctm  = $int($row['CODE_TYPE_MILIEU']      ?? null);
    $ctso = $int($row['CODE_TYPE_STATUT_ORG']  ?? null);
    $ctse = $int($row['CODE_TYPE_SECTEUR_ENS'] ?? null);

    $prov = $str($row['PROVINCE']   ?? null);
    $comm = $str($row['COMMUNE']    ?? null);
    $coll = $str($row['COLLINE']    ?? null);
    $ne   = $str($row['NOM_ETAB']   ?? ($row['NOM_ETABLISSEMENT'] ?? null)) ?? '';
    $se   = $str($row['SECTEUR_ENS'] ?? null);
    $so   = $str($row['STATUT']     ?? ($row['STATUT_ORG'] ?? null));
    $mil  = $str($row['MILIEU']     ?? null);

    // Construire la chaîne de localisation Province / Commune / Colline / Établissement
    $chaineParts = array_filter([$prov, $comm, $coll, $ne], fn($v) => $v !== null && $v !== '');
    $chain       = implode(' / ', $chaineParts);

    // Début de transaction batch
    if (!$inTx) {
        $pdo->beginTransaction();
        $inTx = true;
    }

    try {
        $stmtEtab->execute([
            ':ce'   => $code,
            ':ne'   => $ne,
            ':prov' => $prov,
            ':comm' => $comm,
            ':coll' => $coll,
            ':chain'=> $chain ?: null,
            ':cp'   => $cp,
            ':cc'   => $cc,
            ':ccl'  => $ccl,
            ':ctm'  => $ctm,
            ':ctso' => $ctso,
            ':ctse' => $ctse,
            ':se'   => $se,
            ':so'   => $so,
            ':mil'  => $mil,
        ]);
        $etabOk++;
    } catch (PDOException $e) {
        $etabErr++;
        out("Étab $code : " . $e->getMessage(), 'err');
    }

    // Commit par batch + affichage progression
    if (($i + 1) % $batchSize === 0) {
        $pdo->commit();
        $inTx = false;
        $pct  = round(($i + 1) / $totalRows * 100);
        out("  … " . ($i + 1) . "/$totalRows ($pct%) — ok=$etabOk err=$etabErr", 'info');
    }
}

// Commit du dernier batch
if ($inTx) {
    $pdo->commit();
}

// ─── Résumé final ──────────────────────────────────────────────────────────────
out("", 'info');
out("══════════════════════════════════════════════════════════════════════════════", 'info');
out("RÉSUMÉ DE L'EXTRACTION", 'info');
out("══════════════════════════════════════════════════════════════════════════════", 'info');
out("Provinces   : $provOk upsertées, $provErr erreurs",  $provErr  > 0 ? 'warn' : 'ok');
out("Communes    : $commOk upsertées, $commErr erreurs",  $commErr  > 0 ? 'warn' : 'ok');
out("Collines    : $collOk upsertées, $collErr erreurs",  $collErr  > 0 ? 'warn' : 'ok');
out("Établiss.   : $etabOk upsertés,  $etabErr erreurs",  $etabErr  > 0 ? 'warn' : 'ok');

$totalErrors = $provErr + $commErr + $collErr + $etabErr;
if ($totalErrors === 0) {
    out("", 'info');
    out("✔ Extraction terminée sans erreur. Les cascades Province→Commune→Colline→Établissement", 'ok');
    out("  sont maintenant disponibles pour le formulaire d'inscription (new.php).", 'ok');
} else {
    out("", 'info');
    out("⚠ Extraction terminée avec $totalErrors erreur(s). Voir les messages ci-dessus.", 'warn');
}

if (!$isCli) {
    echo '</body></html>';
}
