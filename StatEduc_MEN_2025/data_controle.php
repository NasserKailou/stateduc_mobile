<?php
/**
 * data_controle.php — Contrôle de cohérence des données (API REST pour app mobile)
 *
 * Route : GET /theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
 *
 * Exécute les règles de cohérence du thème (DICO_REGLE_THEME + DICO_REGLE_THEME_ASSOC)
 * contre les données déjà sauvegardées en base pour l'établissement demandé.
 *
 * Retourne JSON :
 *   {
 *     "se_status": 200,
 *     "se_message": "OK",
 *     "se_data": {
 *       "nb_erreurs": 2,
 *       "erreurs": [
 *         { "id_regle": 12, "id_regle_assoc": 7, "message": "Effectif total ≠ somme tranches d'âge : 120 > 98" },
 *         ...
 *       ]
 *     }
 *   }
 *
 * NOTES :
 *   - Utilise controle_theme_batch.class.php qui stocke les violations dans
 *     $tab_regles_theme_assoc_not_ok sans émettre de sortie HTML.
 *   - controle_theme_batch prend ctrl_id = ID_ASSOC_REG_THM (une règle d'association).
 *     Il faut donc d'abord requêter DICO_REGLE_THEME_ASSOC pour trouver tous les
 *     ID_ASSOC_REG_THM associés au thème, puis appeler le batch pour chacun.
 *   - L'ID thème peut être composite (ex: 15702 = thème 1570 + secteur 2).
 *     On strip le suffixe secteur avant la requête (même logique que data_rules.php).
 *   - Compatibilité PHP 7.3.4 garantie (pas de syntaxe PHP 8+).
 */

require_once 'common_ws.php';

$app = new \Slim\Slim();

$lib_status  = $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data    = $GLOBALS['PARAM_WS']['LIB_DATA'];
$status_ok   = $GLOBALS['PARAM_WS']['STATUS_OK'];
$status_ko   = $GLOBALS['PARAM_WS']['STATUS_KO'];

// ─────────────────────────────────────────────────────────────────────────────
// Helper : strip sector suffix from composite theme ID
// e.g. id_theme=15702, id_sector=2 → 1570
// Returns the stripped numeric string, or the original if no valid strip found.
// ─────────────────────────────────────────────────────────────────────────────
function controle_strip_theme_id($id_theme, $id_sector) {
    $str_theme  = '' . $id_theme;
    $str_sector = '' . $id_sector;
    $len_theme  = strlen($str_theme);
    $len_sector = strlen($str_sector);
    if ($len_theme > $len_sector && $str_sector !== '0' && $str_sector !== '') {
        $candidate = substr($str_theme, 0, $len_theme - $len_sector);
        if (is_numeric($candidate) && (int)$candidate > 0) {
            return $candidate;
        }
    }
    return $str_theme;
}

// ─────────────────────────────────────────────────────────────────────────────
// Helper : run coherence checks for a given raw theme ID
// Queries all ID_ASSOC_REG_THM for the theme, runs controle_theme_batch for each,
// and collects all violations into $erreurs array.
// ─────────────────────────────────────────────────────────────────────────────
function controle_run_for_theme($raw_theme_id, $langue, $id_etab, $id_year, $filtre_val) {
    require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/controle_theme_batch.class.php';

    // ── Find all association rule IDs for this theme ─────────────────────────
    // DICO_REGLE_THEME_ASSOC links two rules (from two themes) via ID_ASSOC_REG_THM.
    // We look for all associations where DICO_REGLE_THEME_1 belongs to our theme.
    $sql_assoc_ids = "SELECT DISTINCT DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM
                      FROM DICO_REGLE_THEME_ASSOC
                      INNER JOIN DICO_REGLE_THEME AS DICO_REGLE_THEME_1
                          ON DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME = DICO_REGLE_THEME_1.ID_REGLE_THEME
                      WHERE DICO_REGLE_THEME_1.ID_THEME = " . (int)$raw_theme_id . "
                      AND DICO_REGLE_THEME_ASSOC.ACTIVER_CTRL = 1";

    $assoc_rows = $GLOBALS['conn_dico']->GetAll($sql_assoc_ids);

    $erreurs = array();

    if (!is_array($assoc_rows) || count($assoc_rows) === 0) {
        // No association rules found for this theme — coherence passes with 0 errors
        return $erreurs;
    }

    // ── Run controle_theme_batch for each association rule ID ─────────────────
    foreach ($assoc_rows as $assoc_row) {
        $assoc_row_lower = array_change_key_case($assoc_row, CASE_LOWER);
        $ctrl_id = isset($assoc_row_lower['id_assoc_reg_thm'])
            ? (int)$assoc_row_lower['id_assoc_reg_thm']
            : 0;
        if ($ctrl_id <= 0) continue;

        // controle_theme_batch constructor: __construct($ctrl_id, $langue, $code_etab, $code_annee, $code_filtre='', $alert)
        // $alert has no default — pass false (batch mode, no HTML output needed)
        try {
            $ctrl = new controle_theme(
                $ctrl_id,
                $langue,
                $id_etab,
                $id_year,
                $filtre_val,
                false
            );

            if (is_array($ctrl->tab_regles_theme_assoc_not_ok)
                && count($ctrl->tab_regles_theme_assoc_not_ok) > 0) {

                foreach ($ctrl->tab_regles_theme_assoc_not_ok as $id_regle => $assocs) {
                    if (!is_array($assocs)) continue;
                    foreach ($assocs as $id_regle_assoc => $tab) {
                        // Use message already fetched by the batch class if available
                        $message = isset($tab['msg_assoc']) ? $tab['msg_assoc'] : '';

                        $erreurs[] = array(
                            'id_regle'       => $id_regle,
                            'id_regle_assoc' => $id_regle_assoc,
                            'message'        => $message,
                            'regle_1'        => isset($tab['nom_regle_1']) ? $tab['nom_regle_1'] : '',
                            'regle_2'        => isset($tab['nom_regle_2']) ? $tab['nom_regle_2'] : '',
                            'critere'        => isset($tab['critere_assoc']) ? $tab['critere_assoc'] : '',
                        );
                    }
                }
            }
        } catch (Exception $e) {
            // Log but continue — one failing association rule shouldn't block the rest
            error_log('[data_controle] controle_theme_batch error for ctrl_id=' . $ctrl_id . ': ' . $e->getMessage());
        }
    }

    return $erreurs;
}

// ─────────────────────────────────────────────────────────────────────────────
// Route : GET /theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
//
// Paramètres URL :
//   user       → login de l'utilisateur (pour vérification accès campagne)
//   id_camp    → ID campagne
//   id_sector  → ID secteur (système éducatif)
//   id_theme   → ID thème / formulaire (peut être composite ex: 15702)
//   id_etab    → code établissement
//   id_filter  → ID filtre/période (ou "null")
//   id_annee   → code année scolaire (injecté dans $_SESSION pour le contrôle SQL)
// ─────────────────────────────────────────────────────────────────────────────
$app->get(
    '/theme_controle/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:id_annee',
    function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_annee)
    use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {

        $msg_ok = $GLOBALS['PARAM_WS']['OK'];
        $msg_ko = $GLOBALS['PARAM_WS']['KO'];

        // ── 1. Résoudre l'année scolaire ────────────────────────────────────
        // Priorité : paramètre URL (mobile) > session navigateur
        $id_year = ($id_annee !== '' && $id_annee !== '0')
            ? $id_annee
            : (isset($_SESSION['annee']) ? $_SESSION['annee'] : '');

        // Injecter dans session pour que les SQL des règles puissent utiliser
        // les variables de session via eval() dans controle_theme_batch
        if ($id_year !== '') {
            $_SESSION['annee'] = $id_year;
        }

        // ── 2. Strip secteur du thème composite ─────────────────────────────
        // Ex: id_theme=15702, id_sector=2 → raw_theme_id=1570
        $raw_theme_id = controle_strip_theme_id($id_theme, $id_sector);

        // ── 3. Vérification accès campagne ───────────────────────────────────
        $period_query = ($id_filter !== 'null' && $id_filter !== '0')
            ? ' AND ID_PERIODE=' . (int)$id_filter . ' '
            : '';

        // Verification acces campagne (avec fallback mobile)
        $is_mobile_request = ($id_annee !== '' && $id_annee !== '0');
        $access_ok = false;

        $req_camp = "SELECT DISTINCT ID_CAMPAGNE
                     FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU
                     WHERE AU.NOM_USER LIKE '" . $user . "'
                     AND DFR.ID_USER = AU.CODE_USER
                     AND ID_ANNEE = " . (int)$id_year . "
                     " . $period_query . "
                     AND ID_CAMPAGNE = " . (int)$id_camp . ";";

        $camps = $GLOBALS['conn_dico']->GetAll($req_camp);
        if (is_array($camps) && count($camps) > 0 && $camps[0] !== '') {
            $access_ok = true;
        }

        // Fallback pour les utilisateurs mobiles : verifier existence utilisateur
        if (!$access_ok && $is_mobile_request) {
            $req_user = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER LIKE '" . $user . "'";
            $user_row = $GLOBALS['conn_dico']->GetRow($req_user);
            if ($user_row && isset($user_row['CODE_USER']) && (int)$user_row['CODE_USER'] > 0) {
                $access_ok = true;
            }
        }

        if (!$access_ok) {
            $rps = array(
                $lib_status  => $status_ko,
                $lib_message => $msg_ko,
                $lib_data    => "L'utilisateur '" . $user . "' n'a pas acces a cette campagne"
            );
            echo json_encode($rps);
            return;
        }

        // ── 4. Récupérer la langue de session (défaut 'fr') ──────────────────
        $langue = isset($_SESSION['langue']) && $_SESSION['langue'] !== ''
            ? $_SESSION['langue']
            : 'fr';

        $filtre_val = ($id_filter === 'null' || $id_filter === '0') ? '' : $id_filter;

        // ── 5. Exécuter les contrôles de cohérence ───────────────────────────
        // Trouve tous les ID_ASSOC_REG_THM pour ce thème, puis exécute
        // controle_theme_batch pour chacun et collecte toutes les violations.
        $erreurs = controle_run_for_theme($raw_theme_id, $langue, $id_etab, $id_year, $filtre_val);

        // ── 6. Réponse JSON ──────────────────────────────────────────────────
        $result = array(
            'nb_erreurs' => count($erreurs),
            'erreurs'    => $erreurs,
        );

        $rps = array(
            $lib_status  => $status_ok,
            $lib_message => $msg_ok,
            $lib_data    => $result,
        );
        echo json_encode($rps);
    }
);

// ─────────────────────────────────────────────────────────────────────────────
// Route rétrocompatible (navigateur web avec session active)
// GET /theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}
// ─────────────────────────────────────────────────────────────────────────────
$app->get(
    '/theme_controle/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter',
    function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter)
    use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {

        $id_annee = isset($_SESSION['annee']) ? $_SESSION['annee'] : '0';
        $msg_ok = $GLOBALS['PARAM_WS']['OK'];
        $msg_ko = $GLOBALS['PARAM_WS']['KO'];

        $id_year = $id_annee;
        if ($id_year !== '') {
            $_SESSION['annee'] = $id_year;
        }

        // Strip secteur du thème composite
        $raw_theme_id = controle_strip_theme_id($id_theme, $id_sector);

        $period_query = ($id_filter !== 'null' && $id_filter !== '0')
            ? ' AND ID_PERIODE=' . (int)$id_filter . ' '
            : '';

        $req_camp = "SELECT DISTINCT ID_CAMPAGNE
                     FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU
                     WHERE AU.NOM_USER LIKE '" . $user . "'
                     AND DFR.ID_USER = AU.CODE_USER
                     AND ID_ANNEE = " . (int)$id_year . "
                     " . $period_query . "
                     AND ID_CAMPAGNE = " . (int)$id_camp . ";";

        $camps = $GLOBALS['conn_dico']->GetAll($req_camp);
        if (!is_array($camps) || count($camps) === 0 || $camps[0] === '') {
            $rps = array(
                $lib_status  => $status_ko,
                $lib_message => $msg_ko,
                $lib_data    => "L'utilisateur '" . $user . "' n'a pas acces a cette campagne"
            );
            echo json_encode($rps);
            return;
        }

        $langue = isset($_SESSION['langue']) && $_SESSION['langue'] !== ''
            ? $_SESSION['langue']
            : 'fr';
        $filtre_val = ($id_filter === 'null' || $id_filter === '0') ? '' : $id_filter;

        $erreurs = controle_run_for_theme($raw_theme_id, $langue, $id_etab, $id_year, $filtre_val);

        $result = array('nb_erreurs' => count($erreurs), 'erreurs' => $erreurs);
        $rps = array(
            $lib_status  => $status_ok,
            $lib_message => $msg_ok,
            $lib_data    => $result,
        );
        echo json_encode($rps);
    }
);

$app->run();
