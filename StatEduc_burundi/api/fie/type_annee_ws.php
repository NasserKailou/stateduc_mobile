<?php
/**
 * StatEduc_burundi/api/fie/type_annee_ws.php
 * ══════════════════════════════════════════════════════════════════════════════
 * ENDPOINT StatEduc → FIE : Années Scolaires (TYPE_ANNEE)
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Retourne la liste des années scolaires depuis la table SQL Server :
 *   SELECT [CODE_TYPE_ANNEE], [LIBELLE_TYPE_ANNEE], [ORDRE_TYPE_ANNEE]
 *   FROM [BURUNDI].[dbo].[TYPE_ANNEE]
 *
 * Appelé par app_fie/services/SyncService.php::syncTypeAnnee()
 *
 * URL : http://localhost:8085/StatEduc_burundi/api/fie/type_annee_ws.php
 *
 * Authentification : même Bearer token que etabs_fie_ws.php
 *
 * Réponse JSON :
 * {
 *   "se_status": 200,
 *   "se_message": "ok",
 *   "se_data": {
 *     "total": 8,
 *     "annees": [
 *       {"code_type_annee": 1, "libelle": "2018-2019", "ordre": 1},
 *       {"code_type_annee": 8, "libelle": "2025-2026", "ordre": 8}
 *     ]
 *   }
 * }
 *
 * @author   Projet FIE / SIGE Burundi — Session 6
 * @version  1.0.0
 */

ini_set('display_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');
ob_start();

// ── Helpers ───────────────────────────────────────────────────────────────────

function fie_annee_send_error(int $code, string $msg): void
{
    $buf = ob_get_clean();
    if ($buf) $msg .= ' | PHP: ' . substr(strip_tags($buf), 0, 200);
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode(['se_status' => $code, 'se_message' => $msg, 'se_data' => null], JSON_UNESCAPED_UNICODE);
    exit;
}

function fie_annee_send_ok($data): void
{
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode(['se_status' => 200, 'se_message' => 'ok', 'se_data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Bootstrap StatEduc ────────────────────────────────────────────────────────
$stateduc_root = dirname(__DIR__, 2);

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
    fie_annee_send_error(500, 'Bootstrap StatEduc : ' . $e->getMessage());
}

// ── Connexion DB ──────────────────────────────────────────────────────────────
try {
    require_once $GLOBALS['SISED_PATH_CLS'] . 'connexion.class.php';
    $connexion_obj = new connexion();
    $curcnx = $connexion_obj->sources[0] ?? null;
    if (empty($curcnx)) fie_annee_send_error(500, 'Aucune source de connexion.');

    $conn_type = $curcnx['type'];
    if ($conn_type === 'mssql') $conn_type = 'mssqlnative';
    if ($conn_type === 'mysql') $conn_type = 'mysqli';

    $conn = ADONewConnection($conn_type);
    if ($curcnx['type'] === 'access') {
        $connected = @$conn->Connect(
            'Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $curcnx['serveur'] .
            ';Uid=' . $curcnx['utilisateur'] . ';Pwd=' . $curcnx['mdp'] . ';'
        );
    } else {
        $connected = @$conn->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $curcnx['base']);
    }
    if (!$connected || !$conn->IsConnected()) {
        fie_annee_send_error(500, 'Connexion StatEduc impossible.');
    }
    if ($curcnx['type'] === 'mssql') $conn->setConnectionParameter('CharacterSet', 'UTF-8');
    elseif ($curcnx['type'] === 'mysql') $conn->setCharset('utf8');

    $GLOBALS['conn'] = $conn;
} catch (Throwable $e) {
    fie_annee_send_error(500, 'Connexion DB : ' . $e->getMessage());
}

// ── Auth Bearer token (réutilise le même fichier token.php) ──────────────────
$expected_token = '';
$token_file = __DIR__ . '/token.php';
if (file_exists($token_file)) $expected_token = (string)(require $token_file);
if ($expected_token === '') $expected_token = (string)(getenv('STATEDUC_FIE_TOKEN') ?: '');
$auth_required = ($expected_token !== '');

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
        fie_annee_send_error(401, 'Token manquant ou invalide.');
    }
}

// ── Requête TYPE_ANNEE ────────────────────────────────────────────────────────
// SELECT [CODE_TYPE_ANNEE], [LIBELLE_TYPE_ANNEE], [ORDRE_TYPE_ANNEE]
// FROM [BURUNDI].[dbo].[TYPE_ANNEE]
// Note : si la connexion pointe déjà sur BURUNDI, on omet le préfixe schéma.
// ─────────────────────────────────────────────────────────────────────────────
$sql = "SELECT CODE_TYPE_ANNEE, LIBELLE_TYPE_ANNEE, ORDRE_TYPE_ANNEE
        FROM TYPE_ANNEE
        ORDER BY ORDRE_TYPE_ANNEE ASC";

try {
    $rows = $GLOBALS['conn']->GetAll($sql);
    if ($rows === false) {
        fie_annee_send_error(500, 'Erreur lecture TYPE_ANNEE.');
    }
} catch (Throwable $e) {
    // Essai avec le préfixe complet si la table n'est pas dans le schéma par défaut
    try {
        $sql2  = "SELECT CODE_TYPE_ANNEE, LIBELLE_TYPE_ANNEE, ORDRE_TYPE_ANNEE
                  FROM [dbo].[TYPE_ANNEE]
                  ORDER BY ORDRE_TYPE_ANNEE ASC";
        $rows  = $GLOBALS['conn']->GetAll($sql2);
        if ($rows === false) fie_annee_send_error(500, 'Erreur lecture dbo.TYPE_ANNEE.');
    } catch (Throwable $e2) {
        fie_annee_send_error(500, 'Erreur TYPE_ANNEE : ' . $e->getMessage() . ' / ' . $e2->getMessage());
    }
}

// ── Construction réponse ──────────────────────────────────────────────────────
$annees = [];
foreach ((array)$rows as $row) {
    $code  = (int)($row['CODE_TYPE_ANNEE']    ?? $row['code_type_annee']    ?? 0);
    $label = trim((string)($row['LIBELLE_TYPE_ANNEE'] ?? $row['libelle_type_annee'] ?? ''));
    $ordre = (int)($row['ORDRE_TYPE_ANNEE']   ?? $row['ordre_type_annee']   ?? 0);

    if ($code <= 0) continue;

    // Normalisation UTF-8 (SQL Server peut retourner Latin-1 selon driver)
    if (mb_detect_encoding($label, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) !== 'UTF-8') {
        $label = mb_convert_encoding($label, 'UTF-8', 'ISO-8859-1');
    }

    $annees[] = [
        'code_type_annee' => $code,
        'libelle'         => $label,
        'ordre'           => $ordre,
    ];
}

fie_annee_send_ok([
    'total'  => count($annees),
    'annees' => $annees,
]);
