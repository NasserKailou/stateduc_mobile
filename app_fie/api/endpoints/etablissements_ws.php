<?php
/**
 * app_fie/api/endpoints/etablissements_ws.php
 * GET /api/etablissements
 * Expose les établissements miroir FIE pour consommation par StatEduc.
 * Authentification : Authorization: Bearer <fie_api_token>
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/Database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// ── Authentification ────────────────────────────────────────────────────────
function getExpectedToken(): string {
    try {
        $row = Database::fetchOne("SELECT valeur FROM fie_settings WHERE cle='fie_api_token' LIMIT 1");
        $tok = trim($row['valeur'] ?? '');
        if ($tok !== '') return $tok;
    } catch (\Throwable $e) { }
    return defined('STATEDUC_API_TOKEN') ? STATEDUC_API_TOKEN : '';
}

function checkApiEnabled(): bool {
    try {
        $row = Database::fetchOne("SELECT valeur FROM fie_settings WHERE cle='fie_api_enabled' LIMIT 1");
        return ($row['valeur'] ?? '1') !== '0';
    } catch (\Throwable $e) { return true; }
}

function jsonError(int $code, string $msg): void {
    http_response_code($code);
    echo json_encode(['status' => $code, 'error' => $msg]);
    exit;
}

if (!checkApiEnabled()) { jsonError(503, 'API FIE désactivée.'); }

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
$expectedToken = getExpectedToken();
if ($expectedToken !== '') {
    if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m) || $m[1] !== $expectedToken) {
        jsonError(401, 'Token invalide ou manquant.');
    }
}

// ── Paramètres de filtrage ──────────────────────────────────────────────────
$page    = max(1, (int)($_GET['page']     ?? 1));
$perPage = min(1000, max(10, (int)($_GET['per_page'] ?? 500)));
$offset  = ($page - 1) * $perPage;
$actif   = isset($_GET['actif']) ? (int)$_GET['actif'] : 1;

$where  = 'actif = ?';
$params = [$actif];

if (!empty($_GET['province'])) { $where .= ' AND province = ?';  $params[] = $_GET['province']; }
if (!empty($_GET['secteur']))  { $where .= ' AND code_type_secteur_ens = ?'; $params[] = (int)$_GET['secteur']; }
if (!empty($_GET['updated_since'])) {
    $where  .= ' AND stateduc_updated_at >= ?';
    $params[] = $_GET['updated_since'];
}

// ── Requêtes ────────────────────────────────────────────────────────────────
$total = (int)Database::fetchScalar("SELECT COUNT(*) FROM etablissements_miroir WHERE $where", $params);
$rows  = Database::fetchAll(
    "SELECT code_etablissement, nom_etablissement, province, commune,
            zone_admin, colline, code_type_secteur_ens, code_type_milieu,
            chaine_localisation, code_ecole_pays, actif,
            synced_at, stateduc_updated_at
     FROM etablissements_miroir
     WHERE $where
     ORDER BY nom_etablissement
     LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, $offset])
);

echo json_encode([
    'status'         => 200,
    'source'         => 'app_fie',
    'page'           => $page,
    'per_page'       => $perPage,
    'total'          => $total,
    'pages'          => (int)ceil($total / $perPage),
    'etablissements' => $rows,
    'generated_at'   => date('Y-m-d H:i:s'),
], JSON_UNESCAPED_UNICODE);
