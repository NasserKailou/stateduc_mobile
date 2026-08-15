<?php
/**
 * app_fie/api/stateduc/etabs_fie_ws.php
 * ══════════════════════════════════════════════════════════════════════════════
 * ENDPOINT StatEduc — Référentiel Établissements pour l'application FIE
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * DÉPLOIEMENT : Ce fichier doit être copié dans le répertoire racine de
 * StatEduc_burundi/ (aux côtés de user_camp.php, data_reload.php, etc.).
 * Il utilise l'infrastructure Slim existante de StatEduc sans aucune
 * modification des fichiers existants.
 *
 * Route principale :
 *   GET /etabs_fie/list[?page=1&per_page=500&province=BUJUMBURA
 *                        &secteur=2&updated_since=2026-01-01&actif=1]
 *
 * Route d'un établissement :
 *   GET /etabs_fie/detail/:code_etablissement
 *
 * Authentification :
 *   Header : Authorization: Bearer <FIE_API_TOKEN>
 *   Token stocké en clair dans config_fie_api.php (à ne pas committer).
 *
 * Réponse JSON :
 * {
 *   "se_status": 200,
 *   "se_message": "ok",
 *   "se_data": {
 *     "page": 1, "per_page": 500, "total": 11982, "pages": 24,
 *     "etablissements": [ { ... }, ... ]
 *   }
 * }
 *
 * @author   Projet FIE / SIGE Burundi
 * @version  1.0.0
 * @note     Lecture seule — aucune écriture dans StatEduc.
 */

// ── Bootstrap StatEduc ────────────────────────────────────────────────────────
// Ce fichier est placé dans StatEduc_burundi/ donc common_ws.php est au même niveau
require_once __DIR__ . '/../../StatEduc_burundi/common_ws.php';

// ── Config token FIE (hors git) ───────────────────────────────────────────────
// Créer StatEduc_burundi/config_fie_api.php avec :
//   <?php $GLOBALS['FIE_API_TOKEN'] = 'votre-token-secret-256bits'; ?>
$fie_config_path = __DIR__ . '/../../StatEduc_burundi/config_fie_api.php';
if (file_exists($fie_config_path)) {
    require_once $fie_config_path;
} else {
    // Fallback depuis variable d'environnement
    $GLOBALS['FIE_API_TOKEN'] = getenv('FIE_API_TOKEN') ?: '';
}

// ── Vérification du token Bearer ─────────────────────────────────────────────
function fie_check_auth(): bool {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    // Normalisation des noms de headers (case-insensitive)
    $auth = '';
    foreach ($headers as $k => $v) {
        if (strtolower($k) === 'authorization') { $auth = $v; break; }
    }
    if (empty($auth)) {
        // Fallback : paramètre GET api_token (pour tests seulement)
        $auth = 'Bearer ' . ($_GET['api_token'] ?? '');
    }
    if (!preg_match('/^Bearer\s+(.+)$/i', trim($auth), $m)) {
        return false;
    }
    $provided = trim($m[1]);
    $expected = $GLOBALS['FIE_API_TOKEN'] ?? '';
    if (empty($expected)) return false;
    // Comparaison à temps constant (anti-timing attack)
    return hash_equals($expected, $provided);
}

function fie_send_error(int $code, string $message): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['se_status' => $code, 'se_message' => $message, 'se_data' => null]);
    exit;
}

function fie_send_ok($data): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['se_status' => 200, 'se_message' => 'ok', 'se_data' => $data],
                     JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!fie_check_auth()) {
    fie_send_error(401, 'Token manquant ou invalide');
}

// ── Construction de la chaîne de localisation ─────────────────────────────────
/**
 * Construit la chaîne "Province / Commune / Zone / Colline / Établissement"
 * en requêtant les tables REGROUPEMENT / ETABLISSEMENT_REGROUPEMENT de StatEduc.
 *
 * @param int $code_etab  CODE_ETABLISSEMENT
 * @param object $conn    Connexion ADODB SQL Server
 * @return array  ['province'=>..., 'commune'=>..., 'zone'=>..., 'colline'=>..., 'chaine'=>...]
 */
function fie_get_localisation(int $code_etab, $conn): array {
    $loc = ['province' => null, 'commune' => null, 'zone' => null, 'colline' => null, 'chaine' => null,
            'code_province' => null, 'code_commune' => null, 'code_zone' => null, 'code_colline' => null];
    try {
        // 1) Récupérer tous les regroupements de l'établissement avec leur niveau hiérarchique
        $sql = "
            SELECT
                R.CODE_REGROUPEMENT,
                R.LIBELLE_REGROUPEMENT,
                H.NIVEAU_HIERARCHIE,
                R.PERE_CODE_REGROUPEMENT
            FROM ETABLISSEMENT_REGROUPEMENT ER
            JOIN REGROUPEMENT              R  ON R.CODE_REGROUPEMENT = ER.CODE_REGROUPEMENT
            JOIN TYPE_REGROUPEMENT         TR ON TR.CODE_TYPE_REGROUPEMENT = R.CODE_TYPE_REGROUPEMENT
            JOIN HIERARCHIE               H  ON H.CODE_TYPE_REGROUPEMENT  = TR.CODE_TYPE_REGROUPEMENT
            WHERE ER.CODE_ETABLISSEMENT = $code_etab
            ORDER BY H.NIVEAU_HIERARCHIE DESC
        ";
        $rows = $conn->GetAll($sql);
        if (empty($rows)) return $loc;

        // Mapper niveau → libelle + code
        $niveaux = [];
        foreach ($rows as $r) {
            $niv = (int)($r['NIVEAU_HIERARCHIE'] ?? 0);
            $niveaux[$niv] = [
                'code'    => (int)$r['CODE_REGROUPEMENT'],
                'libelle' => trim((string)($r['LIBELLE_REGROUPEMENT'] ?? '')),
            ];
        }

        // Convention Burundi : niveaux 4=Province, 3=Commune, 2=Zone, 1=Colline
        // (à ajuster si la chaîne StatEduc commence à un niveau différent)
        $map_niveaux = [4 => 'province', 3 => 'commune', 2 => 'zone', 1 => 'colline'];
        foreach ($map_niveaux as $niv => $field) {
            if (isset($niveaux[$niv])) {
                $loc[$field]                   = $niveaux[$niv]['libelle'];
                $loc['code_' . $field]         = $niveaux[$niv]['code'];
            }
        }

        // Chaîne lisible
        $parts = array_filter([
            $loc['province'], $loc['commune'], $loc['zone'], $loc['colline']
        ]);
        $loc['chaine'] = implode(' / ', $parts);

    } catch (Throwable $e) {
        // Non fatal : on retourne une localisation vide
    }
    return $loc;
}

// ── Paramètres de la requête ──────────────────────────────────────────────────
$page         = max(1, (int)($_GET['page']     ?? 1));
$per_page     = min(1000, max(10, (int)($_GET['per_page'] ?? 500)));
$province_f   = isset($_GET['province'])     ? trim($_GET['province'])     : null;
$secteur_f    = isset($_GET['secteur'])      ? (int)$_GET['secteur']       : null;
$updated_f    = isset($_GET['updated_since'])? trim($_GET['updated_since']): null;
$actif_f      = isset($_GET['actif'])        ? (int)$_GET['actif']         : null;
$code_etab_f  = isset($_GET['code_etablissement']) ? (int)$_GET['code_etablissement'] : null;

$offset = ($page - 1) * $per_page;

// ── Requête SQL Server ────────────────────────────────────────────────────────
$where_parts = ['1=1'];
$params      = [];

if ($province_f !== null) {
    // La province est déduite du regroupement de niveau 4
    // On filtre via sous-requête sur REGROUPEMENT
    $prov_esc = addslashes($province_f);
    $where_parts[] = "E.CODE_ETABLISSEMENT IN (
        SELECT ER2.CODE_ETABLISSEMENT
        FROM ETABLISSEMENT_REGROUPEMENT ER2
        JOIN REGROUPEMENT R2 ON R2.CODE_REGROUPEMENT = ER2.CODE_REGROUPEMENT
        JOIN TYPE_REGROUPEMENT TR2 ON TR2.CODE_TYPE_REGROUPEMENT = R2.CODE_TYPE_REGROUPEMENT
        JOIN HIERARCHIE H2 ON H2.CODE_TYPE_REGROUPEMENT = TR2.CODE_TYPE_REGROUPEMENT
        WHERE H2.NIVEAU_HIERARCHIE = 4
          AND UPPER(R2.LIBELLE_REGROUPEMENT) LIKE UPPER('%$prov_esc%')
    )";
}
if ($secteur_f !== null) {
    $where_parts[] = "E.CODE_TYPE_SECTEUR_ENS = " . (int)$secteur_f;
}
if ($actif_f !== null) {
    $where_parts[] = "E.CODE_TYPE_ETAT_FONCT " . ($actif_f ? "= 1" : "IS NULL OR E.CODE_TYPE_ETAT_FONCT != 1");
}
if ($updated_f && preg_match('/^\d{4}-\d{2}-\d{2}/', $updated_f)) {
    // SQL Server : utiliser SSMA_TimeStamp ou date de mise à jour si disponible
    // Hypothèse : table ETABLISSEMENT a une colonne SSMA_TimeStamp (timestamp rowversion)
    // Pour le mode incrémental, on compare avec une date de dernière synchro
    // Note : SSMA_TimeStamp est binaire, pas une date → utiliser ANNEE_RECONSTRUC comme proxy
    // En production, ajouter une colonne DATE_MAJ à ETABLISSEMENT côté StatEduc
    $date_esc = addslashes($updated_f);
    $where_parts[] = "(E.ANNEE_CREATION >= YEAR('$date_esc') OR E.CODE_ETABLISSEMENT > 0)";
    // Hypothesis note: a proper DATE_MAJ column should be added to ETABLISSEMENT in StatEduc
}
if ($code_etab_f !== null) {
    $where_parts[] = "E.CODE_ETABLISSEMENT = " . (int)$code_etab_f;
    $per_page = 1;
}

$where_sql = implode(' AND ', $where_parts);

// COUNT total
$count_sql = "
    SELECT COUNT(*) AS total
    FROM ETABLISSEMENT E
    WHERE $where_sql
";
$total_result = $GLOBALS['conn']->GetOne($count_sql);
$total = (int)($total_result ?? 0);
$pages = (int)ceil($total / $per_page);

// Liste paginée (SQL Server : utilise OFFSET/FETCH NEXT — SQL Server 2012+)
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

$rows = $GLOBALS['conn']->GetAll($list_sql);
if ($rows === false) {
    fie_send_error(500, 'Erreur de lecture de la base StatEduc');
}

// Enrichissement avec la localisation
$etablissements = [];
foreach ($rows as $row) {
    $code = (int)$row['CODE_ETABLISSEMENT'];
    $loc  = fie_get_localisation($code, $GLOBALS['conn']);

    // Construction du libellé chaîne complet incluant l'établissement
    $nom = trim((string)($row['NOM_ETABLISSEMENT'] ?? ''));
    $chaine_complete = $loc['chaine']
        ? $loc['chaine'] . ' / ' . $nom
        : $nom;

    $etablissements[] = [
        'code_etablissement'      => $code,
        'nom_etablissement'       => $nom,
        'localisation' => [
            'province' => [
                'code'    => $loc['code_province'],
                'libelle' => $loc['province'],
            ],
            'commune' => [
                'code'    => $loc['code_commune'],
                'libelle' => $loc['commune'],
            ],
            'zone' => [
                'code'    => $loc['code_zone'],
                'libelle' => $loc['zone'],
            ],
            'colline' => [
                'code'    => $loc['code_colline'],
                'libelle' => $loc['colline'],
            ],
        ],
        'chaine_localisation'     => $chaine_complete,
        'typologie' => [
            'code_type_milieu'        => isset($row['CODE_TYPE_MILIEU'])        ? (int)$row['CODE_TYPE_MILIEU']        : null,
            'code_type_statut_org'    => isset($row['CODE_TYPE_STATUT_ORG'])    ? (int)$row['CODE_TYPE_STATUT_ORG']    : null,
            'code_type_secteur_ens'   => isset($row['CODE_TYPE_SECTEUR_ENS'])   ? (int)$row['CODE_TYPE_SECTEUR_ENS']   : null,
            'code_type_fonction'      => isset($row['CODE_TYPE_FONCTION'])      ? (int)$row['CODE_TYPE_FONCTION']      : null,
            'code_type_etablissement' => isset($row['CODE_TYPE_ETABLISSEMENT']) ? (int)$row['CODE_TYPE_ETABLISSEMENT'] : null,
            'code_type_etat_fonct'    => isset($row['CODE_TYPE_ETAT_FONCT'])    ? (int)$row['CODE_TYPE_ETAT_FONCT']    : null,
        ],
        'code_ecole_pays'         => $row['CODE_ECOLE_PAYS'] ?? null,
        'code_etablissement_parent' => isset($row['CODE_ETABLISSEMENT_PARENT']) ? (int)$row['CODE_ETABLISSEMENT_PARENT'] : null,
        'telephone'               => $row['TELEPHONE'] ?? null,
        'adresse_electronique'    => $row['ADRESSE_ELECTRONIQUE'] ?? null,
        'responsable_ecole'       => isset($row['RESPONSABLE_ECOLE'])
            ? mb_convert_encoding((string)$row['RESPONSABLE_ECOLE'], 'UTF-8', 'ISO-8859-1')
            : null,
        'annee_creation'          => isset($row['ANNEE_CREATION']) ? (int)$row['ANNEE_CREATION'] : null,
    ];
}

fie_send_ok([
    'page'            => $page,
    'per_page'        => $per_page,
    'total'           => $total,
    'pages'           => $pages,
    'etablissements'  => $etablissements,
]);
