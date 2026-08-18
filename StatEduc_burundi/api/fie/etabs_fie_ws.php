<?php
/**
 * StatEduc_burundi/api/fie/etabs_fie_ws.php
 * ══════════════════════════════════════════════════════════════════════════════
 * ENDPOINT StatEduc → FIE : Référentiel Établissements via ATLAS_COLLINE
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Appelé par app_fie/api/stateduc/StatEducApiClient.php lors d'une sync.
 * Utilise la VUE SQL Server ATLAS_COLLINE qui contient directement les 14
 * colonnes du fichier FICHIER_ETAB.xlsx :
 *   CODE_PROVINCE, PROVINCE, CODE_COMMUNE, COMMUNE, CODE_COLLINE, COLLINE,
 *   CODE_TYPE_SECTEUR_ENS, SECTEUR_ENS, CODE_TYPE_STATUT_ORG, STATUT,
 *   NOM_ETAB, CODE_ETABLISSEMENT, CODE_TYPE_MILIEU, MILIEU
 *
 * URL d'accès (exemple XAMPP) :
 *   http://localhost:8085/StatEduc_burundi/api/fie/etabs_fie_ws.php
 *
 * Authentification :
 *   Header  : Authorization: Bearer <STATEDUC_FIE_TOKEN>
 *   Fallback: GET ?api_token=<token>   (tests uniquement)
 *
 * Réponse JSON :
 * {
 *   "se_status": 200,
 *   "se_message": "ok",
 *   "se_data": {
 *     "page": 1, "per_page": 500, "total": 11498, "pages": 23,
 *     "etablissements": [ {
 *       "code_etablissement": 10011,
 *       "nom_etablissement":  "GIHOSHA (E.P GIHOSHA)",
 *       "code_province": 117,  "province": "BUJUMBURA Mairie",
 *       "code_commune":  11716,"commune":  "NTAHANGWA",
 *       "code_colline":  1170501, "colline": "Gihosha",
 *       "code_type_secteur_ens": 1, "secteur_ens": "Préscolaire",
 *       "code_type_statut_org":  3, "statut_org": "école maternelle publique",
 *       "code_type_milieu":      1, "milieu": "urbain"
 *     }, ... ]
 *   }
 * }
 *
 * @author   Projet FIE / SIGE Burundi — Session 6 ATLAS_COLLINE rewrite
 * @version  2.0.0 — ATLAS_COLLINE view (simplifié, toutes les 14 colonnes directes)
 */

// ── Désactiver tout affichage d'erreurs — répondre TOUJOURS en JSON ───────────
ini_set('display_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

// Bufferiser la sortie — capturer tout warning PHP avant les headers
ob_start();

// ── Helpers de réponse ────────────────────────────────────────────────────────

function fie_etabs_send_error(int $code, string $message): void
{
    $buffered = ob_get_clean();
    if ($buffered) $message .= ' | PHP output: ' . substr(strip_tags($buffered), 0, 300);
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode([
        'se_status'  => $code,
        'se_message' => $message,
        'se_data'    => null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function fie_etabs_send_ok($data): void
{
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode([
        'se_status'  => 200,
        'se_message' => 'ok',
        'se_data'    => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ── Bootstrap StatEduc ────────────────────────────────────────────────────────
$stateduc_root = dirname(__DIR__, 2); // = .../StatEduc_burundi/

try {
    require_once $stateduc_root . '/config_app.php';
    require_once $stateduc_root . '/params.php';
    require_once $stateduc_root . '/params_sys.php';
    require_once $stateduc_root . '/constants.php';

    require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
    if (!defined('ADODB_ASSOC_CASE')) {
        define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);
    }
    $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;

} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur bootstrap StatEduc : ' . $e->getMessage());
}

// ── Connexion DB (SQL Server via ADOdb) ───────────────────────────────────────
try {
    require_once $GLOBALS['SISED_PATH_CLS'] . 'connexion.class.php';

    $connexion_obj = new connexion();
    $curcnx = $connexion_obj->sources[0] ?? null;

    if (empty($curcnx)) {
        fie_etabs_send_error(500, 'Aucune source de connexion trouvée.');
    }

    $conn_type = $curcnx['type'];
    if ($conn_type === 'mssql')  $conn_type = 'mssqlnative';
    if ($conn_type === 'mysql')  $conn_type = 'mysqli';

    $conn = ADONewConnection($conn_type);

    if ($curcnx['type'] === 'access') {
        $connected = @$conn->Connect(
            'Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $curcnx['serveur'] .
            ';Uid=' . $curcnx['utilisateur'] . ';Pwd=' . $curcnx['mdp'] . ';'
        );
    } else {
        $connected = @$conn->Connect(
            $curcnx['serveur'],
            $curcnx['utilisateur'],
            $curcnx['mdp'],
            $curcnx['base']
        );
    }

    if (!$connected || !$conn->IsConnected()) {
        fie_etabs_send_error(500,
            'Connexion StatEduc impossible (serveur=' . $curcnx['serveur'] .
            ', base=' . $curcnx['base'] . ').'
        );
    }

    if ($curcnx['type'] === 'mssql') {
        $conn->setConnectionParameter('CharacterSet', 'UTF-8');
    } elseif ($curcnx['type'] === 'mysql') {
        $conn->setCharset('utf8');
    }

    $GLOBALS['conn'] = $conn;

} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur connexion DB StatEduc : ' . $e->getMessage());
}

if (empty($GLOBALS['conn'])) {
    fie_etabs_send_error(500, 'Connexion DB non disponible.');
}

// ── Lecture du token ──────────────────────────────────────────────────────────
$expected_token = '';
$token_file = __DIR__ . '/token.php';
if (file_exists($token_file)) {
    $expected_token = (string)(require $token_file);
}
if ($expected_token === '') {
    $expected_token = (string)(getenv('STATEDUC_FIE_TOKEN') ?: '');
}
$auth_required = ($expected_token !== '');

// ── Vérification Bearer token ─────────────────────────────────────────────────
if ($auth_required) {
    $auth_header = '';
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            if (strtolower($k) === 'authorization') { $auth_header = $v; break; }
        }
    }
    if (!$auth_header) $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$auth_header) $auth_header = 'Bearer ' . ($_GET['api_token'] ?? '');

    if (!preg_match('/^Bearer\s+(.+)$/i', trim($auth_header), $m)
        || !hash_equals($expected_token, trim($m[1]))) {
        fie_etabs_send_error(401, 'Token manquant ou invalide.');
    }
}

// ── Paramètres de requête ─────────────────────────────────────────────────────
$page         = max(1, (int)($_GET['page']     ?? 1));
$per_page     = min(1000, max(1, (int)($_GET['per_page'] ?? 500)));
$secteur_f    = isset($_GET['secteur'])            ? (int)$_GET['secteur']            : null;
$province_f   = isset($_GET['code_province'])      ? (int)$_GET['code_province']      : null;
$code_etab_f  = isset($_GET['code_etablissement']) ? (int)$_GET['code_etablissement'] : null;
$offset       = ($page - 1) * $per_page;

// ── Construction WHERE pour ATLAS_COLLINE ─────────────────────────────────────
// La vue ATLAS_COLLINE expose directement les 14 colonnes FICHIER_ETAB.xlsx
// Filtres disponibles : CODE_TYPE_SECTEUR_ENS, CODE_PROVINCE, CODE_ETABLISSEMENT
// ─────────────────────────────────────────────────────────────────────────────
$where_parts = ['1=1'];

if ($secteur_f !== null) {
    $where_parts[] = 'CODE_TYPE_SECTEUR_ENS = ' . (int)$secteur_f;
}
if ($province_f !== null) {
    $where_parts[] = 'CODE_PROVINCE = ' . (int)$province_f;
}
if ($code_etab_f !== null) {
    $where_parts[] = 'CODE_ETABLISSEMENT = ' . (int)$code_etab_f;
    $per_page = 1;
    $offset   = 0;
}

$where_sql = implode(' AND ', $where_parts);

// ── COUNT total ───────────────────────────────────────────────────────────────
try {
    $count_sql = "SELECT COUNT(*) AS TOTAL FROM ATLAS_COLLINE WHERE $where_sql";
    $total     = (int)($GLOBALS['conn']->GetOne($count_sql) ?? 0);
} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur COUNT ATLAS_COLLINE : ' . $e->getMessage());
}
$pages = ($per_page > 0) ? (int)ceil($total / $per_page) : 0;

// ── Requête paginée ATLAS_COLLINE (toutes les 14 colonnes directement) ────────
// Plus besoin de jointures HIERARCHIE/REGROUPEMENT — la vue les encapsule.
// ─────────────────────────────────────────────────────────────────────────────
$list_sql = "
    SELECT
        CODE_PROVINCE,
        PROVINCE,
        CODE_COMMUNE,
        COMMUNE,
        CODE_COLLINE,
        COLLINE,
        CODE_TYPE_SECTEUR_ENS,
        SECTEUR_ENS,
        CODE_TYPE_STATUT_ORG,
        STATUT,
        NOM_ETAB,
        CODE_ETABLISSEMENT,
        CODE_TYPE_MILIEU,
        MILIEU
    FROM ATLAS_COLLINE
    WHERE $where_sql
    ORDER BY NOM_ETAB ASC, CODE_ETABLISSEMENT ASC
    OFFSET $offset ROWS
    FETCH NEXT $per_page ROWS ONLY
";

try {
    $rows = $GLOBALS['conn']->GetAll($list_sql);
    if ($rows === false) {
        fie_etabs_send_error(500, 'Erreur lecture ATLAS_COLLINE (GetAll returned false).');
    }
} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur lecture ATLAS_COLLINE : ' . $e->getMessage());
}

// ── Utilitaire : nettoyage UTF-8 (SQL Server mssqlnative peut varier) ─────────
function fie_atlas_clean_str(?string $v): ?string
{
    if ($v === null) return null;
    $v = trim($v);
    if ($v === '') return null;
    // Détecter et convertir si nécessaire
    if (mb_detect_encoding($v, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) !== 'UTF-8') {
        $v = mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
    }
    return $v;
}

// ── Construction de la réponse ────────────────────────────────────────────────
$etablissements = [];

foreach ((array)$rows as $row) {
    $code = (int)($row['CODE_ETABLISSEMENT'] ?? 0);
    if ($code === 0) continue;

    $nom      = fie_atlas_clean_str($row['NOM_ETAB']    ?? null) ?? '';
    $province = fie_atlas_clean_str($row['PROVINCE']    ?? null);
    $commune  = fie_atlas_clean_str($row['COMMUNE']     ?? null);
    $colline  = fie_atlas_clean_str($row['COLLINE']     ?? null);
    $secteur  = fie_atlas_clean_str($row['SECTEUR_ENS'] ?? null);
    $statut   = fie_atlas_clean_str($row['STATUT']      ?? null);
    $milieu   = fie_atlas_clean_str($row['MILIEU']      ?? null);

    $cp   = isset($row['CODE_PROVINCE'])         ? (int)$row['CODE_PROVINCE']         : null;
    $cc   = isset($row['CODE_COMMUNE'])          ? (int)$row['CODE_COMMUNE']          : null;
    $ccl  = isset($row['CODE_COLLINE'])          ? (int)$row['CODE_COLLINE']          : null;
    $csec = isset($row['CODE_TYPE_SECTEUR_ENS']) ? (int)$row['CODE_TYPE_SECTEUR_ENS'] : null;
    $csta = isset($row['CODE_TYPE_STATUT_ORG'])  ? (int)$row['CODE_TYPE_STATUT_ORG']  : null;
    $cmil = isset($row['CODE_TYPE_MILIEU'])      ? (int)$row['CODE_TYPE_MILIEU']      : null;

    // Chaîne localisation complète : Province / Commune / Colline / Nom école
    $chaine_parts = array_filter([$province, $commune, $colline, $nom]);
    $chaine_localisation = implode(' / ', $chaine_parts);

    $etablissements[] = [
        // Clé primaire
        'code_etablissement'      => $code,
        'nom_etablissement'       => $nom,

        // Localisation géographique (codes + libellés ATLAS_COLLINE)
        'code_province'           => $cp,
        'province'                => $province,
        'code_commune'            => $cc,
        'commune'                 => $commune,
        'code_colline'            => $ccl,
        'colline'                 => $colline,

        // Caractéristiques pédagogiques/administratives
        'code_type_secteur_ens'   => $csec,
        'secteur_ens'             => $secteur,
        'code_type_statut_org'    => $csta,
        'statut_org'              => $statut,
        'code_type_milieu'        => $cmil,
        'milieu'                  => $milieu,

        // Chaîne pratique pour affichage
        'chaine_localisation'     => $chaine_localisation,
    ];
}

fie_etabs_send_ok([
    'page'           => $page,
    'per_page'       => $per_page,
    'total'          => $total,
    'pages'          => $pages,
    'etablissements' => $etablissements,
]);
