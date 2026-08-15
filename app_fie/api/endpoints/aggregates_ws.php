<?php
/**
 * FIE — Endpoint REST : agrégats ELEVES_AGE_NIVEAU_SEXE
 * ========================================================
 * Exposé par app_fie pour que StatEduc (SQL Server) puisse venir
 * LIRE les effectifs calculés par le FIE et les intégrer dans sa
 * propre table [dbo].[ELEVES_AGE_NIVEAU_SEXE].
 *
 * Authentification : Bearer token (hash_equals, constant-time)
 * Méthodes : GET uniquement (lecture seule)
 *
 * URL type :
 *   GET /api/agregats?code_annee=1&code_etab=10011&page=1&per_page=500
 *
 * Paramètres de filtre :
 *   code_annee   int  — filtrer par CODE_TYPE_ANNEE (obligatoire si code_etab absent)
 *   code_etab    int  — filtrer par CODE_ETABLISSEMENT
 *   pending_only 1|0  — si 1 : seulement les lignes non encore envoyées (synced=0)
 *   page         int  — pagination (défaut 1)
 *   per_page     int  — lignes par page (max 1000, défaut 500)
 *
 * Réponse JSON :
 * {
 *   "fie_status": 200,
 *   "fie_data": {
 *     "page": 1, "per_page": 500, "total": 42, "pages": 1,
 *     "agregats": [
 *       {
 *         "code_etablissement": 10011,
 *         "code_type_annee": 1,
 *         "code_type_niveau": 3,
 *         "code_type_age": 8,
 *         "code_type_section": 1,
 *         "filles_age_niveau": 12,
 *         "total_age_niveau": 25,
 *         "estimation": null,
 *         "code_type_etat_fonct": null,
 *         "numero_ordre_groupe": null,
 *         "synced_to_stateduc": 0,
 *         "updated_at": "2025-08-10T14:22:00"
 *       }
 *     ]
 *   }
 * }
 *
 * Après consommation réussie, StatEduc DOIT appeler :
 *   POST /api/agregats/mark-synced
 *   Body JSON : { "token":"...", "ids": [1,2,3] }
 * pour que le FIE mette à jour synced_to_stateduc = 1.
 */

declare(strict_types=1);

// ── Bootstrap FIE ──────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__, 2));
define('BASE_URL',  '');           // non utilisé ici (API pure)

// Empêcher tout output HTML avant les headers JSON
ob_start();
set_error_handler(function (int $errno, string $errstr) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['fie_status' => 500, 'fie_error' => "PHP Error [$errno]: $errstr"]);
    exit(1);
});

require BASE_PATH . '/config/config.php';
use App\Config\Database;
use App\Services\Logger;

$log = new Logger('api_agregats');

// ── Helpers ─────────────────────────────────────────────────────────────────
function jsonOut(int $status, array $body): void
{
    ob_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function authError(): never
{
    jsonOut(401, ['fie_status' => 401, 'fie_error' => 'Authentification requise']);
}

function methodError(): never
{
    jsonOut(405, ['fie_status' => 405, 'fie_error' => 'Méthode non autorisée']);
}

function inputError(string $msg): never
{
    jsonOut(400, ['fie_status' => 400, 'fie_error' => $msg]);
}

// ── Authentification Bearer ──────────────────────────────────────────────────
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
    $providedToken = $m[1];
} else {
    authError();
}

if (!defined('FIE_AGREGATS_API_TOKEN') || FIE_AGREGATS_API_TOKEN === '') {
    jsonOut(503, ['fie_status' => 503, 'fie_error' => 'Token API non configuré côté serveur']);
}

if (!hash_equals(FIE_AGREGATS_API_TOKEN, $providedToken)) {
    $log->warning("Tentative d'accès avec token invalide", [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
    authError();
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// ══════════════════════════════════════════════════════════════════════════════
// GET  /api/agregats  — Lecture des agrégats
// ══════════════════════════════════════════════════════════════════════════════
if ($method === 'GET') {

    $codeAnnee  = isset($_GET['code_annee'])  ? (int)$_GET['code_annee']  : null;
    $codeEtab   = isset($_GET['code_etab'])   ? (int)$_GET['code_etab']   : null;
    $pendingOnly = !empty($_GET['pending_only']);
    $page       = max(1, (int)($_GET['page']     ?? 1));
    $perPage    = min(1000, max(1, (int)($_GET['per_page'] ?? 500)));
    $offset     = ($page - 1) * $perPage;

    // Au moins un filtre obligatoire (sinon table entière)
    if ($codeAnnee === null && $codeEtab === null) {
        inputError("Paramètre 'code_annee' ou 'code_etab' requis");
    }

    // ── Construction de la requête ──────────────────────────────────────────
    $where  = [];
    $params = [];

    if ($codeAnnee !== null) {
        $where[]  = 'a.code_type_annee = ?';
        $params[] = $codeAnnee;
    }
    if ($codeEtab !== null) {
        $where[]  = 'a.code_etablissement = ?';
        $params[] = $codeEtab;
    }
    if ($pendingOnly) {
        $where[] = 'a.synced_to_stateduc = 0';
    }

    $whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $db = Database::getInstance();

    $total = (int)$db->fetchScalar(
        "SELECT COUNT(*)
         FROM agregats_eleves_age_niveau_sexe a $whereClause",
        $params
    );

    $rows = $db->fetchAll(
        "SELECT
            a.id,
            a.code_etablissement,
            a.code_type_annee,
            a.code_type_niveau,
            a.code_type_age,
            a.code_type_section,
            a.filles_age_niveau,
            a.total_age_niveau,
            a.estimation,
            a.code_type_etat_fonct,
            a.numero_ordre_groupe,
            a.synced_to_stateduc,
            a.updated_at
         FROM agregats_eleves_age_niveau_sexe a
         $whereClause
         ORDER BY a.code_etablissement, a.code_type_niveau, a.code_type_age, a.code_type_section
         LIMIT ? OFFSET ?",
        array_merge($params, [$perPage, $offset])
    );

    // Caster les valeurs numériques (PDO retourne des strings)
    $agregats = array_map(function (array $row): array {
        return [
            'id'                   => (int)$row['id'],
            'code_etablissement'   => (int)$row['code_etablissement'],
            'code_type_annee'      => (int)$row['code_type_annee'],
            'code_type_niveau'     => (int)$row['code_type_niveau'],
            'code_type_age'        => (int)$row['code_type_age'],
            'code_type_section'    => (int)$row['code_type_section'],
            'filles_age_niveau'    => $row['filles_age_niveau'] !== null
                                        ? (int)$row['filles_age_niveau']   : null,
            'total_age_niveau'     => $row['total_age_niveau']  !== null
                                        ? (int)$row['total_age_niveau']    : null,
            'estimation'           => $row['estimation']        !== null
                                        ? (int)$row['estimation']          : null,
            'code_type_etat_fonct' => $row['code_type_etat_fonct'] !== null
                                        ? (int)$row['code_type_etat_fonct'] : null,
            'numero_ordre_groupe'  => $row['numero_ordre_groupe'] !== null
                                        ? (int)$row['numero_ordre_groupe'] : null,
            'synced_to_stateduc'   => (int)$row['synced_to_stateduc'],
            'updated_at'           => $row['updated_at'],
        ];
    }, $rows);

    $log->info("GET /api/agregats", [
        'code_annee' => $codeAnnee, 'code_etab' => $codeEtab,
        'total'      => $total,     'page'      => $page,
        'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    jsonOut(200, [
        'fie_status' => 200,
        'fie_data'   => [
            'page'      => $page,
            'per_page'  => $perPage,
            'total'     => $total,
            'pages'     => max(1, (int)ceil($total / $perPage)),
            'agregats'  => $agregats,
        ],
    ]);
}

// ══════════════════════════════════════════════════════════════════════════════
// POST /api/agregats/mark-synced  — Marquer comme synchronisés
// ══════════════════════════════════════════════════════════════════════════════
if ($method === 'POST') {

    // Lire le corps JSON
    $rawBody = file_get_contents('php://input');
    $body    = json_decode($rawBody, true);

    if (!is_array($body) || empty($body['ids']) || !is_array($body['ids'])) {
        inputError("Corps JSON invalide. Attendu : {\"ids\": [1,2,3,...]}");
    }

    // Valider et assainir les IDs
    $ids = array_filter(array_map('intval', $body['ids']), fn(int $id) => $id > 0);
    $ids = array_values(array_unique($ids));

    if (empty($ids)) {
        inputError("Tableau 'ids' vide ou invalide");
    }

    if (count($ids) > 5000) {
        inputError("Trop d'IDs (max 5000 par appel)");
    }

    $db          = Database::getInstance();
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $affected = $db->query(
        "UPDATE agregats_eleves_age_niveau_sexe
         SET synced_to_stateduc = 1, synced_at = NOW()
         WHERE id IN ($placeholders) AND synced_to_stateduc = 0",
        $ids
    );

    $log->info("POST /api/agregats/mark-synced", [
        'ids_count' => count($ids),
        'affected'  => $affected,
        'ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    jsonOut(200, [
        'fie_status' => 200,
        'fie_data'   => [
            'ids_received' => count($ids),
            'rows_updated' => $affected,
        ],
    ]);
}

// ── Méthode non gérée ────────────────────────────────────────────────────────
methodError();
