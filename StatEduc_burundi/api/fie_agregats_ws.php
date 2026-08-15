<?php
/**
 * StatEduc_burundi/api/fie_agregats_ws.php
 * Reçoit les agrégats élèves depuis app_fie (POST)
 * OU expose les agrégats locaux pour que FIE les tire (GET)
 * URL : /StatEduc_burundi/api/fie_agregats_ws.php
 */
require_once dirname(__DIR__) . '/api/fie_config.php';
require_once dirname(__DIR__) . '/common_ws.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// ── Authentification Bearer ──────────────────────────────────────────────────
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (FIE_API_TOKEN !== '') {
    if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m) || $m[1] !== FIE_API_TOKEN) {
        http_response_code(401);
        echo json_encode(['status' => 401, 'error' => 'Token invalide ou manquant.']);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ── POST : Recevoir des agrégats depuis FIE ──────────────────────────────────
if ($method === 'POST') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data) || empty($data['agregats'])) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'error' => 'Données manquantes ou JSON invalide.']);
        exit;
    }

    $agregats = $data['agregats'];
    $annee    = $data['annee']    ?? date('Y') . '-' . (date('Y') + 1);
    $pushedAt = $data['pushed_at'] ?? date('Y-m-d H:i:s');

    // Insertion dans une table de staging (créer la table si nécessaire)
    // La table fie_agregats_staging doit être créée via la migration SQL fournie
    $inserted = 0;
    $errors   = 0;
    foreach ($agregats as $row) {
        try {
            $sql = "INSERT INTO fie_agregats_staging
                        (annee_scolaire, code_etablissement, niveau, age, sexe, effectif, source_pushed_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE effectif = VALUES(effectif), source_pushed_at = VALUES(source_pushed_at)";
            $GLOBALS['conn']->Execute($sql, [
                $annee,
                $row['code_etablissement'] ?? null,
                $row['niveau']             ?? null,
                $row['age']                ?? null,
                $row['sexe']               ?? null,
                (int)($row['effectif']     ?? 0),
                $pushedAt,
            ]);
            $inserted++;
        } catch (\Throwable $e) {
            $errors++;
        }
    }

    echo json_encode([
        'status'   => 200,
        'inserted' => $inserted,
        'errors'   => $errors,
        'message'  => "$inserted agrégats reçus et enregistrés.",
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── GET : Exposer les agrégats locaux StatEduc pour FIE ──────────────────────
$annee   = $_GET['annee']   ?? '';
$page    = max(1, (int)($_GET['page']     ?? 1));
$perPage = min(5000, max(50, (int)($_GET['per_page'] ?? 1000)));
$offset  = ($page - 1) * $perPage;

$where  = '1=1';
$params = [];
if ($annee !== '') { $where .= ' AND annee_scolaire = ?'; $params[] = $annee; }

try {
    $rs = $GLOBALS['conn']->SelectLimit(
        "SELECT annee_scolaire, code_etablissement, niveau, age, sexe, SUM(effectif) AS effectif
         FROM fie_agregats_staging
         WHERE $where
         GROUP BY annee_scolaire, code_etablissement, niveau, age, sexe
         ORDER BY annee_scolaire, code_etablissement",
        $perPage, $offset
    );
    $rows = [];
    while (!$rs->EOF) { $rows[] = $rs->fields; $rs->MoveNext(); }

    echo json_encode([
        'status'       => 200,
        'source'       => FIE_SOURCE_ID,
        'page'         => $page,
        'per_page'     => $perPage,
        'agregats'     => $rows,
        'generated_at' => date('Y-m-d H:i:s'),
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
