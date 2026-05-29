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
 *   - N'utilise PAS controle_theme.class.php (méthode alerter() émet du HTML/JS).
 *   - Utilise controle_theme_batch.class.php qui stocke les violations dans
 *     $tab_regles_theme_assoc_not_ok sans émettre de sortie.
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
// Route : GET /theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
//
// Paramètres URL :
//   user       → login de l'utilisateur (pour vérification accès campagne)
//   id_camp    → ID campagne
//   id_sector  → ID secteur (système éducatif)
//   id_theme   → ID thème / formulaire
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

        // ── 2. Vérification accès campagne ───────────────────────────────────
        $period_query = ($id_filter !== 'null' && $id_filter !== '0')
            ? ' AND ID_PERIODE=' . (int)$id_filter . ' '
            : '';

        // --- Verification acces campagne (avec fallback mobile) ---
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

        // ── 3. Charger le contrôleur de cohérence (version batch) ───────────
        // controle_theme_batch stocke les violations dans
        // $obj->tab_regles_theme_assoc_not_ok sans émettre de sortie HTML.
        require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/controle_theme_batch.class.php';

        // Récupérer la langue de session (défaut 'fr')
        $langue = isset($_SESSION['langue']) && $_SESSION['langue'] !== ''
            ? $_SESSION['langue']
            : 'fr';

        $filtre_val = ($id_filter === 'null' || $id_filter === '0') ? '' : $id_filter;

        // Le constructeur appelle automatiquement get_regles() + controle_regles_theme()
        $ctrl = new controle_theme(
            (int)$id_theme,
            $langue,
            $id_etab,
            $id_year,
            $filtre_val
        );

        // ── 4. Collecter les erreurs ─────────────────────────────────────────
        $erreurs = array();

        if (is_array($ctrl->tab_regles_theme_assoc_not_ok)
            && count($ctrl->tab_regles_theme_assoc_not_ok) > 0) {

            foreach ($ctrl->tab_regles_theme_assoc_not_ok as $id_regle => $assocs) {
                if (!is_array($assocs)) continue;
                foreach ($assocs as $id_regle_assoc => $tab) {
                    // Récupérer le libellé du message (DICO_REGLE_THEME_ASSOC)
                    $id_assoc = isset($tab['id_assoc']) ? $tab['id_assoc'] : 0;
                    $message  = '';
                    if ($id_assoc > 0) {
                        $req_msg = "SELECT LIBELLE FROM DICO_TRADUCTION
                                    WHERE CODE_NOMENCLATURE = " . (int)$id_assoc . "
                                    AND CODE_LANGUE = '" . $langue . "'
                                    AND NOM_TABLE = 'DICO_REGLE_THEME_ASSOC'";
                        $row_msg = $GLOBALS['conn_dico']->GetRow($req_msg);
                        if (isset($row_msg['LIBELLE'])) {
                            $message = $row_msg['LIBELLE'];
                        }
                    }
                    // Libellés des deux règles comparées
                    $lib1 = '';
                    $lib2 = '';
                    if (isset($tab['nom_regle_1'])) $lib1 = $tab['nom_regle_1'];
                    if (isset($tab['nom_regle_2'])) $lib2 = $tab['nom_regle_2'];

                    $erreur_item = array(
                        'id_regle'       => $id_regle,
                        'id_regle_assoc' => $id_regle_assoc,
                        'message'        => $message,
                        'regle_1'        => $lib1,
                        'regle_2'        => $lib2,
                        'critere'        => isset($tab['critere_assoc']) ? $tab['critere_assoc'] : '',
                    );
                    $erreurs[] = $erreur_item;
                }
            }
        }

        // ── 5. Réponse JSON ──────────────────────────────────────────────────
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
        // Delegate to the main handler by running the same logic inline
        // (Slim v2 doesn't support easy route delegation, so we duplicate the call)
        $msg_ok = $GLOBALS['PARAM_WS']['OK'];
        $msg_ko = $GLOBALS['PARAM_WS']['KO'];

        $id_year = $id_annee;
        if ($id_year !== '') {
            $_SESSION['annee'] = $id_year;
        }

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
                $lib_data    => "L'utilisateur '" . $user . "' n'a pas accès à cette campagne"
            );
            echo json_encode($rps);
            return;
        }

        require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/controle_theme_batch.class.php';

        $langue = isset($_SESSION['langue']) && $_SESSION['langue'] !== ''
            ? $_SESSION['langue']
            : 'fr';
        $filtre_val = ($id_filter === 'null' || $id_filter === '0') ? '' : $id_filter;

        $ctrl = new controle_theme(
            (int)$id_theme,
            $langue,
            $id_etab,
            $id_year,
            $filtre_val
        );

        $erreurs = array();
        if (is_array($ctrl->tab_regles_theme_assoc_not_ok)
            && count($ctrl->tab_regles_theme_assoc_not_ok) > 0) {
            foreach ($ctrl->tab_regles_theme_assoc_not_ok as $id_regle => $assocs) {
                if (!is_array($assocs)) continue;
                foreach ($assocs as $id_regle_assoc => $tab) {
                    $id_assoc = isset($tab['id_assoc']) ? $tab['id_assoc'] : 0;
                    $message  = '';
                    if ($id_assoc > 0) {
                        $req_msg = "SELECT LIBELLE FROM DICO_TRADUCTION
                                    WHERE CODE_NOMENCLATURE = " . (int)$id_assoc . "
                                    AND CODE_LANGUE = '" . $langue . "'
                                    AND NOM_TABLE = 'DICO_REGLE_THEME_ASSOC'";
                        $row_msg = $GLOBALS['conn_dico']->GetRow($req_msg);
                        if (isset($row_msg['LIBELLE'])) {
                            $message = $row_msg['LIBELLE'];
                        }
                    }
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
