<?php
/**
 * StatEduc_burundi/api/fie/etabs_fie_ws.php
 * ══════════════════════════════════════════════════════════════════════════════
 * ENDPOINT StatEduc → FIE : Référentiel Établissements
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Appelé par app_fie/api/stateduc/StatEducApiClient.php lors d'une sync.
 * Ce fichier est dans StatEduc_burundi/api/fie/ et utilise la connexion
 * SQL Server de StatEduc via $GLOBALS['conn'] (ADODB).
 *
 * URL d'accès (exemple XAMPP) :
 *   http://localhost:8085/stateduc_burundi/api/fie/etabs_fie_ws.php
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
 *     "page": 1, "per_page": 500, "total": 11982, "pages": 24,
 *     "etablissements": [ {...}, ... ]
 *   }
 * }
 *
 * Configuration du token :
 *   Créer StatEduc_burundi/api/fie/token.php avec :
 *     <?php return 'votre-token-secret'; ?>
 *   OU définir la variable d'environnement STATEDUC_FIE_TOKEN.
 *   Si aucun token n'est configuré, l'accès est OUVERT (développement local).
 *
 * @author   Projet FIE / SIGE Burundi
 * @version  1.1.0
 */

// ── Désactiver tout affichage d'erreurs — répondre TOUJOURS en JSON ───────────
// Si PHP affiche une erreur HTML, l'appel curl dans StatEducApiClient échouera
// avec "réponse n'est pas du JSON".
ini_set('display_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

// Bufferiser la sortie — si un warning est affiché avant les headers, on peut
// le capturer et le mettre dans la réponse JSON d'erreur plutôt qu'en HTML.
ob_start();

// ── Helpers de réponse (définis avant tout require pour éviter les fatals) ───

function fie_etabs_send_error(int $code, string $message): void
{
    $buffered = ob_get_clean();
    if ($buffered) $message .= ' | PHP output: ' . substr(strip_tags($buffered), 0, 300);
    ob_start(); // restart buffer
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode([
        'se_status'  => $code,
        'se_message' => $message,
        'se_data'    => null,
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

function fie_etabs_send_ok($data): void
{
    ob_get_clean(); // Jeter tout output parasite
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
// Ce fichier est dans api/fie/ → StatEduc_burundi/ est 2 niveaux plus haut
$stateduc_root = dirname(__DIR__, 2); // = .../stateduc_burundi/

try {
    require_once $stateduc_root . '/config_app.php';
    require_once $stateduc_root . '/params.php';
    require_once $stateduc_root . '/params_sys.php';
    require_once $stateduc_root . '/constants.php';
    require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
    if (!defined('ADODB_ASSOC_CASE')) {
        define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);
    }
    require_once $stateduc_root . '/connexion.php';
} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur bootstrap StatEduc : ' . $e->getMessage());
}

// Vérifier que la connexion DB est disponible
if (empty($GLOBALS['conn'])) {
    fie_etabs_send_error(500, 'Connexion base de données StatEduc non disponible.');
}

// ── Lecture du token ──────────────────────────────────────────────────────────
$expected_token = '';

// 1) Fichier token.php (recommandé)
$token_file = __DIR__ . '/token.php';
if (file_exists($token_file)) {
    $expected_token = (string)(require $token_file);
}
// 2) Variable d'environnement
if ($expected_token === '') {
    $expected_token = (string)(getenv('STATEDUC_FIE_TOKEN') ?: '');
}
// 3) Sans token configuré → accès ouvert en dev local
//    (mettre en place le token dès que le serveur est accessible sur le réseau)
$auth_required = ($expected_token !== '');

// ── Vérification Bearer token ─────────────────────────────────────────────────
if ($auth_required) {
    $auth_header = '';
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            if (strtolower($k) === 'authorization') { $auth_header = $v; break; }
        }
    }
    // Fallback : header Apache via $_SERVER
    if (!$auth_header) {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    }
    // Fallback GET (tests uniquement)
    if (!$auth_header) {
        $auth_header = 'Bearer ' . ($_GET['api_token'] ?? '');
    }

    if (!preg_match('/^Bearer\s+(.+)$/i', trim($auth_header), $m)
        || !hash_equals($expected_token, trim($m[1]))) {
        fie_etabs_send_error(401, 'Token manquant ou invalide. Configurez STATEDUC_FIE_TOKEN ou api/fie/token.php.');
    }
}

// ── Paramètres de requête ─────────────────────────────────────────────────────
$page        = max(1, (int)($_GET['page']     ?? 1));
$per_page    = min(1000, max(1, (int)($_GET['per_page'] ?? 500)));
$province_f  = isset($_GET['province'])      ? trim($_GET['province'])      : null;
$secteur_f   = isset($_GET['secteur'])       ? (int)$_GET['secteur']        : null;
$updated_f   = isset($_GET['updated_since']) ? trim($_GET['updated_since']) : null;
$actif_f     = isset($_GET['actif'])         ? (int)$_GET['actif']          : null;
$code_etab_f = isset($_GET['code_etablissement']) ? (int)$_GET['code_etablissement'] : null;
$offset      = ($page - 1) * $per_page;

// ── Construction WHERE ────────────────────────────────────────────────────────
$where_parts = ['1=1'];

if ($province_f !== null) {
    $prov_esc = addslashes($province_f);
    $where_parts[] = "E.CODE_ETABLISSEMENT IN (
        SELECT ER2.CODE_ETABLISSEMENT
        FROM ETABLISSEMENT_REGROUPEMENT ER2
        JOIN REGROUPEMENT      R2  ON R2.CODE_REGROUPEMENT = ER2.CODE_REGROUPEMENT
        JOIN TYPE_REGROUPEMENT TR2 ON TR2.CODE_TYPE_REGROUPEMENT = R2.CODE_TYPE_REGROUPEMENT
        JOIN HIERARCHIE        H2  ON H2.CODE_TYPE_REGROUPEMENT = TR2.CODE_TYPE_REGROUPEMENT
        WHERE H2.NIVEAU_HIERARCHIE = 4
          AND UPPER(R2.LIBELLE_REGROUPEMENT) LIKE UPPER('%$prov_esc%')
    )";
}
if ($secteur_f !== null) {
    $where_parts[] = "E.CODE_TYPE_SECTEUR_ENS = " . (int)$secteur_f;
}
if ($actif_f !== null) {
    $where_parts[] = $actif_f
        ? "E.CODE_TYPE_ETAT_FONCT = 1"
        : "(E.CODE_TYPE_ETAT_FONCT IS NULL OR E.CODE_TYPE_ETAT_FONCT != 1)";
}
if ($updated_f && preg_match('/^\d{4}-\d{2}-\d{2}/', $updated_f)) {
    $date_esc = addslashes($updated_f);
    // Proxy par ANNEE_CREATION si pas de colonne DATE_MAJ
    $where_parts[] = "(E.ANNEE_CREATION >= YEAR('$date_esc') OR 1=1)";
}
if ($code_etab_f !== null) {
    $where_parts[] = "E.CODE_ETABLISSEMENT = " . (int)$code_etab_f;
    $per_page = 1;
    $offset   = 0;
}

$where_sql = implode(' AND ', $where_parts);

// ── COUNT total ───────────────────────────────────────────────────────────────
try {
    $count_sql = "SELECT COUNT(*) AS total FROM ETABLISSEMENT E WHERE $where_sql";
    $total     = (int)($GLOBALS['conn']->GetOne($count_sql) ?? 0);
} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur COUNT : ' . $e->getMessage());
}
$pages = ($per_page > 0) ? (int)ceil($total / $per_page) : 0;

// ── Liste paginée (SQL Server 2012+ : OFFSET/FETCH) ───────────────────────────
$list_sql = "
    SELECT
        E.CODE_ETABLISSEMENT,
        E.NOM_ETABLISSEMENT,
        E.CODE_TYPE_MILIEU,
        E.CODE_TYPE_STATUT_ORG,
        E.CODE_TYPE_SECTEUR_ENS,
        E.CODE_TYPE_FONCTION,
        E.CODE_TYPE_ETABLISSEMENT,
        E.CODE_TYPE_ETAT_FONCT,
        E.CODE_ECOLE_PAYS,
        E.CODE_ETABLISSEMENT_PARENT,
        E.TELEPHONE,
        E.ADRESSE_ELECTRONIQUE,
        E.RESPONSABLE_ECOLE,
        E.ANNEE_CREATION
    FROM ETABLISSEMENT E
    WHERE $where_sql
    ORDER BY E.CODE_ETABLISSEMENT ASC
    OFFSET $offset ROWS
    FETCH NEXT $per_page ROWS ONLY
";

try {
    $rows = $GLOBALS['conn']->GetAll($list_sql);
    if ($rows === false) {
        fie_etabs_send_error(500, 'Erreur de lecture de la base StatEduc (GetAll a retourné false).');
    }
} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur lecture établissements : ' . $e->getMessage());
}

// ── Fonction localisation ─────────────────────────────────────────────────────
function fie_etabs_get_localisation(int $code_etab, $conn): array
{
    static $cache = [];
    if (isset($cache[$code_etab])) return $cache[$code_etab];

    $loc = [
        'province' => null, 'commune' => null, 'zone' => null, 'colline' => null,
        'chaine'   => null,
        'code_province' => null, 'code_commune' => null,
        'code_zone' => null,    'code_colline'  => null,
    ];

    try {
        $sql = "
            SELECT
                R.CODE_REGROUPEMENT,
                R.LIBELLE_REGROUPEMENT,
                H.NIVEAU_HIERARCHIE
            FROM ETABLISSEMENT_REGROUPEMENT ER
            JOIN REGROUPEMENT      R  ON R.CODE_REGROUPEMENT = ER.CODE_REGROUPEMENT
            JOIN TYPE_REGROUPEMENT TR ON TR.CODE_TYPE_REGROUPEMENT = R.CODE_TYPE_REGROUPEMENT
            JOIN HIERARCHIE        H  ON H.CODE_TYPE_REGROUPEMENT = TR.CODE_TYPE_REGROUPEMENT
            WHERE ER.CODE_ETABLISSEMENT = $code_etab
            ORDER BY H.NIVEAU_HIERARCHIE DESC
        ";
        $rows_loc = $conn->GetAll($sql);
        if (empty($rows_loc)) { $cache[$code_etab] = $loc; return $loc; }

        $niveaux = [];
        foreach ($rows_loc as $r) {
            $niv = (int)($r['NIVEAU_HIERARCHIE'] ?? 0);
            $niveaux[$niv] = [
                'code'    => (int)$r['CODE_REGROUPEMENT'],
                'libelle' => trim((string)($r['LIBELLE_REGROUPEMENT'] ?? '')),
            ];
        }

        // Burundi : 4=Province, 3=Commune, 2=Zone, 1=Colline
        $map = [4 => 'province', 3 => 'commune', 2 => 'zone', 1 => 'colline'];
        foreach ($map as $niv => $field) {
            if (isset($niveaux[$niv])) {
                $loc[$field]             = $niveaux[$niv]['libelle'];
                $loc['code_' . $field]   = $niveaux[$niv]['code'];
            }
        }
        $parts = array_filter([$loc['province'], $loc['commune'], $loc['zone'], $loc['colline']]);
        $loc['chaine'] = implode(' / ', $parts);

    } catch (Throwable $e) {
        // Non fatal — localisation vide
    }

    $cache[$code_etab] = $loc;
    return $loc;
}

// ── Enrichissement et construction de la réponse ──────────────────────────────
$etablissements = [];
foreach ((array)$rows as $row) {
    $code = (int)($row['CODE_ETABLISSEMENT'] ?? 0);
    if ($code === 0) continue;

    $loc = fie_etabs_get_localisation($code, $GLOBALS['conn']);
    $nom = trim((string)($row['NOM_ETABLISSEMENT'] ?? ''));
    // Encodage UTF-8 (SQL Server peut retourner Latin-1)
    $nom = mb_convert_encoding($nom, 'UTF-8', 'UTF-8,ISO-8859-1');

    $etablissements[] = [
        'code_etablissement'        => $code,
        'nom_etablissement'         => $nom,
        'localisation' => [
            'province' => ['code' => $loc['code_province'], 'libelle' => $loc['province']],
            'commune'  => ['code' => $loc['code_commune'],  'libelle' => $loc['commune']],
            'zone'     => ['code' => $loc['code_zone'],     'libelle' => $loc['zone']],
            'colline'  => ['code' => $loc['code_colline'],  'libelle' => $loc['colline']],
        ],
        'chaine_localisation'       => ($loc['chaine'] ? $loc['chaine'] . ' / ' : '') . $nom,
        'typologie' => [
            'code_type_milieu'        => isset($row['CODE_TYPE_MILIEU'])        ? (int)$row['CODE_TYPE_MILIEU']        : null,
            'code_type_statut_org'    => isset($row['CODE_TYPE_STATUT_ORG'])    ? (int)$row['CODE_TYPE_STATUT_ORG']    : null,
            'code_type_secteur_ens'   => isset($row['CODE_TYPE_SECTEUR_ENS'])   ? (int)$row['CODE_TYPE_SECTEUR_ENS']   : null,
            'code_type_fonction'      => isset($row['CODE_TYPE_FONCTION'])      ? (int)$row['CODE_TYPE_FONCTION']      : null,
            'code_type_etablissement' => isset($row['CODE_TYPE_ETABLISSEMENT']) ? (int)$row['CODE_TYPE_ETABLISSEMENT'] : null,
            'code_type_etat_fonct'    => isset($row['CODE_TYPE_ETAT_FONCT'])    ? (int)$row['CODE_TYPE_ETAT_FONCT']    : null,
        ],
        'code_ecole_pays'           => $row['CODE_ECOLE_PAYS'] ?? null,
        'code_etablissement_parent' => isset($row['CODE_ETABLISSEMENT_PARENT']) ? (int)$row['CODE_ETABLISSEMENT_PARENT'] : null,
        'telephone'                 => $row['TELEPHONE']          ?? null,
        'adresse_electronique'      => $row['ADRESSE_ELECTRONIQUE'] ?? null,
        'responsable_ecole'         => isset($row['RESPONSABLE_ECOLE'])
            ? mb_convert_encoding((string)$row['RESPONSABLE_ECOLE'], 'UTF-8', 'UTF-8,ISO-8859-1')
            : null,
        'annee_creation'            => isset($row['ANNEE_CREATION']) ? (int)$row['ANNEE_CREATION'] : null,
    ];
}

fie_etabs_send_ok([
    'page'           => $page,
    'per_page'       => $per_page,
    'total'          => $total,
    'pages'          => $pages,
    'etablissements' => $etablissements,
]);
