<?php
/**
 * StatEduc_burundi/api/fie/etabs_fie_ws.php
 * ══════════════════════════════════════════════════════════════════════════════
 * ENDPOINT StatEduc → FIE : Référentiel Établissements
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Appelé par app_fie/api/stateduc/StatEducApiClient.php lors d'une sync.
 * Ce fichier est dans StatEduc_burundi/api/fie/ et utilise la connexion
 * SQL Server de StatEduc via $GLOBALS['conn'] (ADODB) — même bootstrap que
 * saisie_donnees.php?val=choix_etablissement.
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
 * Localisation : même chaîne que saisie_donnees.php?val=choix_etablissement —
 *   ETABLISSEMENT_REGROUPEMENT → REGROUPEMENT → TYPE_REGROUPEMENT → HIERARCHIE
 *   La table HIERARCHIE contient NIVEAU_HIERARCHIE : 1=bas (colline), N=haut (province).
 *
 * @author   Projet FIE / SIGE Burundi
 * @version  1.2.0  — fix bootstrap : connexion.inc.php au lieu de connexion.php
 */

// ── Désactiver tout affichage d'erreurs — répondre TOUJOURS en JSON ───────────
ini_set('display_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

// Bufferiser la sortie — capturer tout warning PHP avant les headers
ob_start();

// ── Helpers de réponse (définis avant tout require pour éviter les fatals) ───

function fie_etabs_send_error(int $code, string $message): void
{
    $buffered = ob_get_clean();
    if ($buffered) $message .= ' | PHP output: ' . substr(strip_tags($buffered), 0, 300);
    // On vide et repart proprement
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
// Ce fichier est dans api/fie/ → StatEduc_burundi/ est 2 niveaux plus haut.
// On reproduit EXACTEMENT la séquence de common.php (sans la partie session/UI)
// qui est la même que saisie_donnees.php?val=choix_etablissement.
$stateduc_root = dirname(__DIR__, 2); // = .../stateduc_burundi/

try {
    require_once $stateduc_root . '/config_app.php';   // $GLOBALS['SISED_PATH'], SISED_PATH_CLS, etc.
    require_once $stateduc_root . '/params.php';        // $GLOBALS['PARAM'][...]
    require_once $stateduc_root . '/params_sys.php';    // $GLOBALS['PARAM_SYS'][...]
    require_once $stateduc_root . '/constants.php';

    // ADOdb + constante ADODB_ASSOC_CASE (même ordre que common.php lignes 8-9)
    require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
    if (!defined('ADODB_ASSOC_CASE')) {
        define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);
    }
    $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;

} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur bootstrap StatEduc (config/params) : ' . $e->getMessage());
}

// ── Connexion DB via connexion.class.php — même logique que connexion.inc.php ─
// On n'utilise PAS connexion.inc.php directement car il appelle connexion::init()
// qui fait header('Location: no_conn.php')+exit() en cas d'échec — ce qui casserait
// notre réponse JSON. On reproduit uniquement la partie connexion ADOdb.
//
// Équivalent de :
//   require_once connexion.inc.php  →  new connexion() → $connexion->init(false)
//   puis : $GLOBALS['conn'] est peuplé si $connexion->ok === true
// ─────────────────────────────────────────────────────────────────────────────
try {
    require_once $GLOBALS['SISED_PATH_CLS'] . 'connexion.class.php';

    // Lire les sources de connexion depuis connexion.php (le fichier credentials)
    $connexion_obj = new connexion();
    $curcnx = $connexion_obj->sources[0] ?? null;

    if (empty($curcnx)) {
        fie_etabs_send_error(500, 'Aucune source de connexion trouvée dans connexion.php.');
    }

    // Déterminer le type ADOdb (mssql→mssqlnative, mysql→mysqli)
    $conn_type = $curcnx['type'];
    if ($conn_type === 'mssql')  $conn_type = 'mssqlnative';
    if ($conn_type === 'mysql')  $conn_type = 'mysqli';

    // Créer et ouvrir la connexion ADOdb
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
            'Connexion base de données StatEduc impossible (serveur=' . $curcnx['serveur'] .
            ', base=' . $curcnx['base'] . ').'
        );
    }

    // Charset UTF-8 pour SQL Server
    if ($curcnx['type'] === 'mssql') {
        $conn->setConnectionParameter('CharacterSet', 'UTF-8');
    } elseif ($curcnx['type'] === 'mysql') {
        $conn->setCharset('utf8');
    }

    $GLOBALS['conn'] = $conn;

} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur connexion DB StatEduc : ' . $e->getMessage());
}

// Sécurité : vérification finale
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
$page        = max(1, (int)($_GET['page']     ?? 1));
$per_page    = min(1000, max(1, (int)($_GET['per_page'] ?? 500)));
$province_f  = isset($_GET['province'])      ? trim($_GET['province'])      : null;
$secteur_f   = isset($_GET['secteur'])       ? (int)$_GET['secteur']        : null;
$updated_f   = isset($_GET['updated_since']) ? trim($_GET['updated_since']) : null;
$actif_f     = isset($_GET['actif'])         ? (int)$_GET['actif']          : null;
$code_etab_f = isset($_GET['code_etablissement']) ? (int)$_GET['code_etablissement'] : null;
$offset      = ($page - 1) * $per_page;

// ── Construction WHERE ────────────────────────────────────────────────────────
// Les noms de tables/champs suivent les PARAM définis dans params.php :
//   ETABLISSEMENT.CODE_ETABLISSEMENT, NOM_ETABLISSEMENT,
//   CODE_TYPE_SECTEUR_ENS (TYPE_SYSTEME_ENSEIGNEMENT = 'TYPE_SECTEUR_ENS')
// ─────────────────────────────────────────────────────────────────────────────
$where_parts = ['1=1'];

if ($province_f !== null) {
    // Filtrer par province = regroupement de plus haut niveau dans la chaîne
    // Même logique que la clause WHERE de etablissement.php :
    //   E_R.CODE_REGROUPEMENT IN (regroupements de la province demandée)
    $prov_esc = addslashes($province_f);
    $where_parts[] = "E.CODE_ETABLISSEMENT IN (
        SELECT ER2.CODE_ETABLISSEMENT
        FROM ETABLISSEMENT_REGROUPEMENT ER2
        JOIN REGROUPEMENT      R2  ON R2.CODE_REGROUPEMENT      = ER2.CODE_REGROUPEMENT
        JOIN TYPE_REGROUPEMENT TR2 ON TR2.CODE_TYPE_REGROUPEMENT = R2.CODE_TYPE_REGROUPEMENT
        JOIN HIERARCHIE        H2  ON H2.CODE_TYPE_REGROUPEMENT  = TR2.CODE_TYPE_REGROUPEMENT
        WHERE H2.NIVEAU_HIERARCHIE = (
            SELECT MAX(H3.NIVEAU_HIERARCHIE)
            FROM HIERARCHIE H3
        )
          AND UPPER(R2.LIBELLE_REGROUPEMENT) LIKE UPPER('%$prov_esc%')
    )";
}
if ($secteur_f !== null) {
    $where_parts[] = 'E.CODE_TYPE_SECTEUR_ENS = ' . (int)$secteur_f;
}
if ($actif_f !== null) {
    $where_parts[] = $actif_f
        ? 'E.CODE_TYPE_ETAT_FONCT = 1'
        : '(E.CODE_TYPE_ETAT_FONCT IS NULL OR E.CODE_TYPE_ETAT_FONCT != 1)';
}
if ($updated_f && preg_match('/^\d{4}-\d{2}-\d{2}/', $updated_f)) {
    $date_esc = addslashes($updated_f);
    $where_parts[] = "E.ANNEE_CREATION >= YEAR('$date_esc')";
}
if ($code_etab_f !== null) {
    $where_parts[] = 'E.CODE_ETABLISSEMENT = ' . (int)$code_etab_f;
    $per_page = 1;
    $offset   = 0;
}

$where_sql = implode(' AND ', $where_parts);

// ── COUNT total ───────────────────────────────────────────────────────────────
try {
    $count_sql = "SELECT COUNT(*) AS TOTAL FROM ETABLISSEMENT E WHERE $where_sql";
    $total     = (int)($GLOBALS['conn']->GetOne($count_sql) ?? 0);
} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur COUNT établissements : ' . $e->getMessage());
}
$pages = ($per_page > 0) ? (int)ceil($total / $per_page) : 0;

// ── Liste paginée (SQL Server 2012+ : OFFSET / FETCH NEXT) ───────────────────
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
    ORDER BY E.NOM_ETABLISSEMENT ASC, E.CODE_ETABLISSEMENT ASC
    OFFSET $offset ROWS
    FETCH NEXT $per_page ROWS ONLY
";

try {
    $rows = $GLOBALS['conn']->GetAll($list_sql);
    if ($rows === false) {
        fie_etabs_send_error(500, 'Erreur lecture établissements (GetAll returned false).');
    }
} catch (Throwable $e) {
    fie_etabs_send_error(500, 'Erreur lecture établissements : ' . $e->getMessage());
}

// ── Préchargement de la chaîne de localisation (même logique que établissement.php) ──
//
// Même jointure que dans etablissement.php (val=liste_etablissement) :
//   ETABLISSEMENT_REGROUPEMENT (ER) → REGROUPEMENT (R) → TYPE_REGROUPEMENT (TR) → HIERARCHIE (H)
// La table HIERARCHIE contient NIVEAU_HIERARCHIE :
//   niveau le PLUS HAUT = province, niveau le PLUS BAS (1) = colline/sous-commune.
// Le nombre de niveaux dépend de la chaîne de localisation du pays (ex. Burundi : 4 niveaux).
//
// On charge la localisation en une seule requête pour tous les établissements de la page
// (évite N requêtes individuelles).
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Charge les niveaux max/min de la chaîne de localisation.
 * Retourne ['max' => N, 'min' => 1] (ex. Burundi : max=4 → Province)
 */
function fie_etabs_get_niveaux_chaine($conn): array
{
    static $niveaux = null;
    if ($niveaux !== null) return $niveaux;

    try {
        $sql = 'SELECT MIN(NIVEAU_HIERARCHIE) AS niv_min, MAX(NIVEAU_HIERARCHIE) AS niv_max
                FROM HIERARCHIE';
        $row = $conn->GetRow($sql);
        $niveaux = [
            'min' => isset($row['NIV_MIN']) ? (int)$row['NIV_MIN'] : 1,
            'max' => isset($row['NIV_MAX']) ? (int)$row['NIV_MAX'] : 4,
        ];
    } catch (Throwable $e) {
        $niveaux = ['min' => 1, 'max' => 4];
    }
    return $niveaux;
}

/**
 * Charge la localisation complète de plusieurs établissements en une seule requête.
 * Retourne un tableau indexé par CODE_ETABLISSEMENT :
 *   [CODE_ETABLISSEMENT => [niv_max => [...], niv_max-1 => [...], ..., niv_min => [...]]]
 */
function fie_etabs_load_localisation_batch(array $codes_etab, $conn): array
{
    if (empty($codes_etab)) return [];

    $result = [];
    $in_list = implode(',', array_map('intval', $codes_etab));

    try {
        // Même jointure que établissement.php val=liste_etablissement :
        //   ETABLISSEMENT_REGROUPEMENT ER
        //   JOIN REGROUPEMENT R       ON R.CODE_REGROUPEMENT      = ER.CODE_REGROUPEMENT
        //   JOIN TYPE_REGROUPEMENT TR ON TR.CODE_TYPE_REGROUPEMENT = R.CODE_TYPE_REGROUPEMENT
        //   JOIN HIERARCHIE H         ON H.CODE_TYPE_REGROUPEMENT  = TR.CODE_TYPE_REGROUPEMENT
        $sql = "
            SELECT
                ER.CODE_ETABLISSEMENT,
                R.CODE_REGROUPEMENT,
                R.LIBELLE_REGROUPEMENT,
                H.NIVEAU_HIERARCHIE
            FROM ETABLISSEMENT_REGROUPEMENT ER
            JOIN REGROUPEMENT      R  ON R.CODE_REGROUPEMENT       = ER.CODE_REGROUPEMENT
            JOIN TYPE_REGROUPEMENT TR ON TR.CODE_TYPE_REGROUPEMENT  = R.CODE_TYPE_REGROUPEMENT
            JOIN HIERARCHIE        H  ON H.CODE_TYPE_REGROUPEMENT   = TR.CODE_TYPE_REGROUPEMENT
            WHERE ER.CODE_ETABLISSEMENT IN ($in_list)
            ORDER BY ER.CODE_ETABLISSEMENT ASC, H.NIVEAU_HIERARCHIE DESC
        ";
        $rows = $conn->GetAll($sql);

        if (!is_array($rows)) return [];

        foreach ($rows as $row) {
            $code = (int)$row['CODE_ETABLISSEMENT'];
            $niv  = (int)$row['NIVEAU_HIERARCHIE'];
            // Garder uniquement le premier enregistrement par niveau (si plusieurs chaines)
            if (!isset($result[$code][$niv])) {
                $result[$code][$niv] = [
                    'code'    => (int)$row['CODE_REGROUPEMENT'],
                    'libelle' => trim((string)$row['LIBELLE_REGROUPEMENT']),
                ];
            }
        }
    } catch (Throwable $e) {
        // Non fatal — localisation vide pour tous
    }

    return $result;
}

/**
 * Construit le tableau de localisation structuré à partir des niveaux.
 * Mappe niveau max → province, max-1 → commune, max-2 → zone, max-3 → colline (ou sous-entités).
 * Compatible avec tous les pays (Burundi = 4 niveaux, autres = 3 niveaux, etc.)
 */
function fie_etabs_build_loc_struct(array $niveaux_etab, array $niveaux_chaine): array
{
    $niv_max = $niveaux_chaine['max'];
    // Labels génériques selon position relative par rapport au niveau max
    $labels = ['province', 'commune', 'zone', 'colline'];

    $loc = [
        'province' => null, 'commune' => null, 'zone' => null, 'colline' => null,
        'code_province' => null, 'code_commune' => null, 'code_zone' => null, 'code_colline' => null,
    ];

    // Itérer du niveau le plus haut (province) vers le plus bas (colline)
    foreach ($labels as $i => $label) {
        $niv = $niv_max - $i;
        if ($niv < 1) break;
        if (isset($niveaux_etab[$niv])) {
            $loc[$label]            = $niveaux_etab[$niv]['libelle'];
            $loc['code_' . $label]  = $niveaux_etab[$niv]['code'];
        }
    }

    return $loc;
}

// ── Chargement batch de la localisation pour tous les établissements de la page
$codes_page = [];
foreach ((array)$rows as $row) {
    $c = (int)($row['CODE_ETABLISSEMENT'] ?? 0);
    if ($c > 0) $codes_page[] = $c;
}

$loc_batch    = fie_etabs_load_localisation_batch($codes_page, $GLOBALS['conn']);
$niv_chaine   = fie_etabs_get_niveaux_chaine($GLOBALS['conn']);

// ── Construction de la réponse ────────────────────────────────────────────────
$etablissements = [];

foreach ((array)$rows as $row) {
    $code = (int)($row['CODE_ETABLISSEMENT'] ?? 0);
    if ($code === 0) continue;

    // Localisation depuis le batch (même structure que établissement.php)
    $niveaux_etab = $loc_batch[$code] ?? [];
    $loc = fie_etabs_build_loc_struct($niveaux_etab, $niv_chaine);

    // Encodage UTF-8 (SQL Server mssqlnative peut retourner UTF-16/Latin-1)
    $nom = trim((string)($row['NOM_ETABLISSEMENT'] ?? ''));
    $nom = mb_detect_encoding($nom, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) !== 'UTF-8'
        ? mb_convert_encoding($nom, 'UTF-8', 'ISO-8859-1')
        : $nom;

    // Chaîne localisation complète : Province / Commune / Zone / Colline / Nom école
    $chaine_parts = array_filter([
        $loc['province'], $loc['commune'], $loc['zone'], $loc['colline'], $nom
    ]);
    $chaine_localisation = implode(' / ', $chaine_parts);

    $etablissements[] = [
        'code_etablissement'        => $code,
        'nom_etablissement'         => $nom,
        'localisation'              => [
            'province' => ['code' => $loc['code_province'], 'libelle' => $loc['province']],
            'commune'  => ['code' => $loc['code_commune'],  'libelle' => $loc['commune']],
            'zone'     => ['code' => $loc['code_zone'],     'libelle' => $loc['zone']],
            'colline'  => ['code' => $loc['code_colline'],  'libelle' => $loc['colline']],
        ],
        'chaine_localisation'       => $chaine_localisation,
        'typologie'                 => [
            'code_type_milieu'        => isset($row['CODE_TYPE_MILIEU'])        ? (int)$row['CODE_TYPE_MILIEU']        : null,
            'code_type_statut_org'    => isset($row['CODE_TYPE_STATUT_ORG'])    ? (int)$row['CODE_TYPE_STATUT_ORG']    : null,
            'code_type_secteur_ens'   => isset($row['CODE_TYPE_SECTEUR_ENS'])   ? (int)$row['CODE_TYPE_SECTEUR_ENS']   : null,
            'code_type_fonction'      => isset($row['CODE_TYPE_FONCTION'])      ? (int)$row['CODE_TYPE_FONCTION']      : null,
            'code_type_etablissement' => isset($row['CODE_TYPE_ETABLISSEMENT']) ? (int)$row['CODE_TYPE_ETABLISSEMENT'] : null,
            'code_type_etat_fonct'    => isset($row['CODE_TYPE_ETAT_FONCT'])    ? (int)$row['CODE_TYPE_ETAT_FONCT']    : null,
        ],
        'code_ecole_pays'           => $row['CODE_ECOLE_PAYS'] ?? null,
        'code_etablissement_parent' => isset($row['CODE_ETABLISSEMENT_PARENT']) ? (int)$row['CODE_ETABLISSEMENT_PARENT'] : null,
        'telephone'                 => $row['TELEPHONE']             ?? null,
        'adresse_electronique'      => $row['ADRESSE_ELECTRONIQUE']  ?? null,
        'responsable_ecole'         => isset($row['RESPONSABLE_ECOLE'])
            ? (mb_detect_encoding((string)$row['RESPONSABLE_ECOLE'], ['UTF-8', 'ISO-8859-1'], true) !== 'UTF-8'
                ? mb_convert_encoding((string)$row['RESPONSABLE_ECOLE'], 'UTF-8', 'ISO-8859-1')
                : (string)$row['RESPONSABLE_ECOLE'])
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
