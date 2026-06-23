<?php
/**
 * data_rules.php — Exposition des règles de cohérence pour évaluation offline
 *
 * Route : GET /theme_rules/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
 *
 * Retourne toutes les règles de cohérence d'un thème dans leur forme structurée,
 * avec les SQLs interpolés (variables PHP substituées), pour que l'app mobile
 * puisse les stocker localement et les évaluer côté client.
 *
 * Réponse JSON :
 *   {
 *     "se_status": 200,
 *     "se_message": "OK",
 *     "se_data": {
 *       "id_theme": 3,
 *       "nb_regles": 2,
 *       "regles": [
 *         {
 *           "id_regle": 5,
 *           "sql_regle": "SELECT SUM(NB_G) FROM TABLE WHERE CODE_ETAB='ECO001' AND CODE_ANNEE=2024",
 *           "associations": [
 *             {
 *               "id_assoc": 12,
 *               "id_regle_assoc": 7,
 *               "sql_assoc": "SELECT SUM(NB_F) FROM TABLE WHERE ...",
 *               "critere": "<=",
 *               "message": "Effectif garçons doit être <= effectif filles"
 *             }
 *           ]
 *         }
 *       ]
 *     }
 *   }
 *
 * NOTES :
 *   - Les SQL sont interpolés avec les valeurs concrètes (code_etab, code_annee, etc.)
 *     via eval(), comme le fait controle_theme.class.php::get_regles().
 *   - L'app mobile stocke ces règles dans la table coherence_rules (SQLite local).
 *   - L'évaluation offline s'appuie sur des requêtes SQLite contre collected_data.
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
// Route principale (mobile avec id_annee)
// GET /theme_rules/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
// ─────────────────────────────────────────────────────────────────────────────
$app->get(
    '/theme_rules/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:id_annee',
    function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_annee)
    use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {

        $msg_ok = $GLOBALS['PARAM_WS']['OK'];
        $msg_ko = $GLOBALS['PARAM_WS']['KO'];

        // ── 1. Résoudre l'année scolaire ─────────────────────────────────────
        $id_year = ($id_annee !== '' && $id_annee !== '0')
            ? $id_annee
            : (isset($_SESSION['annee']) ? $_SESSION['annee'] : '');
        if ($id_year !== '') {
            $_SESSION['annee'] = $id_year;
        }

        // ── 2. Vérification accès campagne ───────────────────────────────────
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
                $lib_data    => "Acces refuse pour l'utilisateur '" . $user . "'"
            );
            echo json_encode($rps);
            return;
        }

        // ── 3. Interpoler les variables dans les SQLs des règles ─────────────
        // Reproduit la logique de controle_theme::get_regles() sans exécuter les requêtes.
        $code_etablissement = $id_etab;
        $code_annee         = $id_year;
        $code_filtre        = ($id_filter === 'null' || $id_filter === '0') ? '' : $id_filter;

        // Injecter les variables de params pour eval()
        ${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']}                        = $code_etablissement;
        ${$GLOBALS['PARAM']['CODE'] . '_' . $GLOBALS['PARAM']['TYPE_ANNEE']}  = $code_annee;
        ${$GLOBALS['PARAM']['CODE'] . '_' . $GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;

        // ── 4. Lire toutes les règles du thème depuis DICO_REGLE_THEME ───────
        // The mobile app sends a composite theme ID (id_theme concatenated with id_sector).
        // Example: sector=2, raw_theme=1560 → composite id_theme=15602.
        // DICO_REGLE_THEME stores the raw theme ID (1560), so we must strip
        // the sector suffix — same logic as questionnaire_reload_ws.php:
        //   $str_theme_id = substr($id_theme, 0, strlen($id_theme)-strlen($id_sector))
        $str_theme_id = $id_theme;
        $len_sector   = strlen('' . $id_sector);
        $len_theme    = strlen('' . $id_theme);
        if ($len_theme > $len_sector) {
            $candidate = substr('' . $id_theme, 0, $len_theme - $len_sector);
            if (is_numeric($candidate) && (int)$candidate > 0) {
                $str_theme_id = $candidate;
            }
        }

        $sql_regles_theme = "SELECT *
                              FROM DICO_REGLE_THEME
                              WHERE ID_THEME = " . (int)$str_theme_id . "
                              AND SQL_REGLE_THEME IS NOT NULL
                              ORDER BY ORDRE_REGLE_THEME";

        $all_regles_theme = $GLOBALS['conn_dico']->GetAll($sql_regles_theme);

        if (!is_array($all_regles_theme)) {
            $rps = array(
                $lib_status  => $status_ok,
                $lib_message => $msg_ok,
                $lib_data    => array('id_theme' => (int)$id_theme, 'nb_regles' => 0, 'regles' => array())
            );
            echo json_encode($rps);
            return;
        }

        // ── 5. Langue pour les libellés ──────────────────────────────────────
        $langue = (isset($_SESSION['langue']) && $_SESSION['langue'] !== '') ? $_SESSION['langue'] : 'fr';

        // ── 6. Construire la liste structurée ────────────────────────────────
        $regles_out = array();

        foreach ($all_regles_theme as $regle_theme) {
            $id_regle         = $regle_theme['ID_REGLE_THEME'];
            $sql_regle_raw    = $regle_theme['SQL_REGLE_THEME'];

            // Interpoler les variables PHP dans le SQL (même logique que eval dans get_regles())
            $sql_regle_interp = '';
            $chaine_eval = "\$sql_regle_interp=\"$sql_regle_raw\";";
            eval($chaine_eval);

            // Libellé de la règle
            $lib_regle = '';
            $req_lib = "SELECT LIBELLE FROM DICO_TRADUCTION
                        WHERE CODE_NOMENCLATURE = " . (int)$id_regle . "
                        AND CODE_LANGUE = '" . $langue . "'
                        AND NOM_TABLE = 'DICO_REGLE_THEME'";
            $row_lib = $GLOBALS['conn_dico']->GetRow($req_lib);
            if (isset($row_lib['LIBELLE'])) {
                $lib_regle = $row_lib['LIBELLE'];
            }

            // ── 7. Lire les associations de cette règle ──────────────────────
            $sql_regles_assoc = "SELECT DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM,
                                         DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME_ASSOC,
                                         DICO_REGLE_THEME_ASSOC.CRITERE,
                                         DICO_REGLE_THEME.ID_THEME,
                                         DICO_REGLE_THEME.SQL_REGLE_THEME
                                  FROM   DICO_REGLE_THEME_ASSOC, DICO_REGLE_THEME
                                  WHERE  DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME_ASSOC = DICO_REGLE_THEME.ID_REGLE_THEME
                                  AND    DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME = " . (int)$id_regle . "
                                  AND    DICO_REGLE_THEME.SQL_REGLE_THEME IS NOT NULL
                                  AND    DICO_REGLE_THEME_ASSOC.ACTIVER_CTRL = 1";

            $all_regles_assoc = $GLOBALS['conn_dico']->GetAll($sql_regles_assoc);

            $associations_out = array();

            if (is_array($all_regles_assoc) && count($all_regles_assoc) > 0) {
                foreach ($all_regles_assoc as $regle_assoc) {
                    $id_assoc       = $regle_assoc['ID_ASSOC_REG_THM'];
                    $id_regle_assoc = $regle_assoc['ID_REGLE_THEME_ASSOC'];
                    $critere        = $regle_assoc['CRITERE'];
                    $sql_assoc_raw  = $regle_assoc['SQL_REGLE_THEME'];

                    // Interpoler les variables dans le SQL associé
                    $sql_assoc_interp = '';
                    $chaine_eval2 = "\$sql_assoc_interp=\"$sql_assoc_raw\";";
                    eval($chaine_eval2);

                    // Libellé du message d'erreur (DICO_REGLE_THEME_ASSOC)
                    $message = '';
                    $req_msg = "SELECT LIBELLE FROM DICO_TRADUCTION
                                WHERE CODE_NOMENCLATURE = " . (int)$id_assoc . "
                                AND CODE_LANGUE = '" . $langue . "'
                                AND NOM_TABLE = 'DICO_REGLE_THEME_ASSOC'";
                    $row_msg = $GLOBALS['conn_dico']->GetRow($req_msg);
                    if (isset($row_msg['LIBELLE'])) {
                        $message = $row_msg['LIBELLE'];
                    }

                    // Libellé de la règle associée
                    $lib_regle_assoc = '';
                    $req_lib2 = "SELECT LIBELLE FROM DICO_TRADUCTION
                                 WHERE CODE_NOMENCLATURE = " . (int)$id_regle_assoc . "
                                 AND CODE_LANGUE = '" . $langue . "'
                                 AND NOM_TABLE = 'DICO_REGLE_THEME'";
                    $row_lib2 = $GLOBALS['conn_dico']->GetRow($req_lib2);
                    if (isset($row_lib2['LIBELLE'])) {
                        $lib_regle_assoc = $row_lib2['LIBELLE'];
                    }

                    $associations_out[] = array(
                        'id_assoc'       => (int)$id_assoc,
                        'id_regle_assoc' => (int)$id_regle_assoc,
                        'lib_regle_assoc'=> $lib_regle_assoc,
                        'sql_assoc'      => $sql_assoc_interp,
                        'critere'        => $critere,
                        'message'        => $message,
                    );
                }
            }

            $regles_out[] = array(
                'id_regle'     => (int)$id_regle,
                'lib_regle'    => $lib_regle,
                'sql_regle'    => $sql_regle_interp,
                'associations' => $associations_out,
            );
        }

        // ── 8. Réponse ───────────────────────────────────────────────────────
        $result = array(
            'id_theme'  => (int)$id_theme,
            'nb_regles' => count($regles_out),
            'regles'    => $regles_out,
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
// Route rétrocompatible (navigateur web — session active, sans id_annee)
// GET /theme_rules/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}
// ─────────────────────────────────────────────────────────────────────────────
$app->get(
    '/theme_rules/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter',
    function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter)
    use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {

        $id_annee = isset($_SESSION['annee']) ? $_SESSION['annee'] : '0';

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
                $lib_data    => "Acces refuse pour l'utilisateur '" . $user . "'"
            );
            echo json_encode($rps);
            return;
        }

        $code_etablissement = $id_etab;
        $code_annee         = $id_year;
        $code_filtre        = ($id_filter === 'null' || $id_filter === '0') ? '' : $id_filter;

        ${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']}                        = $code_etablissement;
        ${$GLOBALS['PARAM']['CODE'] . '_' . $GLOBALS['PARAM']['TYPE_ANNEE']}  = $code_annee;
        ${$GLOBALS['PARAM']['CODE'] . '_' . $GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;

        // Strip sector suffix from composite theme ID (same logic as route 1)
        $str_theme_id2 = $id_theme;
        $len_sector2   = strlen('' . $id_sector);
        $len_theme2    = strlen('' . $id_theme);
        if ($len_theme2 > $len_sector2) {
            $candidate2 = substr('' . $id_theme, 0, $len_theme2 - $len_sector2);
            if (is_numeric($candidate2) && (int)$candidate2 > 0) {
                $str_theme_id2 = $candidate2;
            }
        }

        $sql_regles_theme = "SELECT *
                              FROM DICO_REGLE_THEME
                              WHERE ID_THEME = " . (int)$str_theme_id2 . "
                              AND SQL_REGLE_THEME IS NOT NULL
                              ORDER BY ORDRE_REGLE_THEME";

        $all_regles_theme = $GLOBALS['conn_dico']->GetAll($sql_regles_theme);

        if (!is_array($all_regles_theme)) {
            $rps = array(
                $lib_status  => $status_ok,
                $lib_message => $msg_ok,
                $lib_data    => array('id_theme' => (int)$id_theme, 'nb_regles' => 0, 'regles' => array())
            );
            echo json_encode($rps);
            return;
        }

        $langue = (isset($_SESSION['langue']) && $_SESSION['langue'] !== '') ? $_SESSION['langue'] : 'fr';
        $regles_out = array();

        foreach ($all_regles_theme as $regle_theme) {
            $id_regle      = $regle_theme['ID_REGLE_THEME'];
            $sql_regle_raw = $regle_theme['SQL_REGLE_THEME'];

            $sql_regle_interp = '';
            $chaine_eval = "\$sql_regle_interp=\"$sql_regle_raw\";";
            eval($chaine_eval);

            $lib_regle = '';
            $req_lib   = "SELECT LIBELLE FROM DICO_TRADUCTION
                          WHERE CODE_NOMENCLATURE = " . (int)$id_regle . "
                          AND CODE_LANGUE = '" . $langue . "'
                          AND NOM_TABLE = 'DICO_REGLE_THEME'";
            $row_lib   = $GLOBALS['conn_dico']->GetRow($req_lib);
            if (isset($row_lib['LIBELLE'])) {
                $lib_regle = $row_lib['LIBELLE'];
            }

            $sql_regles_assoc = "SELECT DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM,
                                          DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME_ASSOC,
                                          DICO_REGLE_THEME_ASSOC.CRITERE,
                                          DICO_REGLE_THEME.ID_THEME,
                                          DICO_REGLE_THEME.SQL_REGLE_THEME
                                   FROM   DICO_REGLE_THEME_ASSOC, DICO_REGLE_THEME
                                   WHERE  DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME_ASSOC = DICO_REGLE_THEME.ID_REGLE_THEME
                                   AND    DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME = " . (int)$id_regle . "
                                   AND    DICO_REGLE_THEME.SQL_REGLE_THEME IS NOT NULL
                                   AND    DICO_REGLE_THEME_ASSOC.ACTIVER_CTRL = 1";

            $all_regles_assoc = $GLOBALS['conn_dico']->GetAll($sql_regles_assoc);
            $associations_out = array();

            if (is_array($all_regles_assoc) && count($all_regles_assoc) > 0) {
                foreach ($all_regles_assoc as $regle_assoc) {
                    $id_assoc       = $regle_assoc['ID_ASSOC_REG_THM'];
                    $id_regle_assoc = $regle_assoc['ID_REGLE_THEME_ASSOC'];
                    $critere        = $regle_assoc['CRITERE'];
                    $sql_assoc_raw  = $regle_assoc['SQL_REGLE_THEME'];

                    $sql_assoc_interp = '';
                    $chaine_eval2 = "\$sql_assoc_interp=\"$sql_assoc_raw\";";
                    eval($chaine_eval2);

                    $message = '';
                    $req_msg = "SELECT LIBELLE FROM DICO_TRADUCTION
                                WHERE CODE_NOMENCLATURE = " . (int)$id_assoc . "
                                AND CODE_LANGUE = '" . $langue . "'
                                AND NOM_TABLE = 'DICO_REGLE_THEME_ASSOC'";
                    $row_msg = $GLOBALS['conn_dico']->GetRow($req_msg);
                    if (isset($row_msg['LIBELLE'])) {
                        $message = $row_msg['LIBELLE'];
                    }

                    $lib_regle_assoc = '';
                    $req_lib2 = "SELECT LIBELLE FROM DICO_TRADUCTION
                                 WHERE CODE_NOMENCLATURE = " . (int)$id_regle_assoc . "
                                 AND CODE_LANGUE = '" . $langue . "'
                                 AND NOM_TABLE = 'DICO_REGLE_THEME'";
                    $row_lib2 = $GLOBALS['conn_dico']->GetRow($req_lib2);
                    if (isset($row_lib2['LIBELLE'])) {
                        $lib_regle_assoc = $row_lib2['LIBELLE'];
                    }

                    $associations_out[] = array(
                        'id_assoc'       => (int)$id_assoc,
                        'id_regle_assoc' => (int)$id_regle_assoc,
                        'lib_regle_assoc'=> $lib_regle_assoc,
                        'sql_assoc'      => $sql_assoc_interp,
                        'critere'        => $critere,
                        'message'        => $message,
                    );
                }
            }

            $regles_out[] = array(
                'id_regle'     => (int)$id_regle,
                'lib_regle'    => $lib_regle,
                'sql_regle'    => $sql_regle_interp,
                'associations' => $associations_out,
            );
        }

        $result = array(
            'id_theme'  => (int)$id_theme,
            'nb_regles' => count($regles_out),
            'regles'    => $regles_out,
        );

        $rps = array(
            $lib_status  => $status_ok,
            $lib_message => $msg_ok,
            $lib_data    => $result,
        );
        echo json_encode($rps);
    }
);

$app->run();
