<?php
/**
 * data_controle.php — Contrôle de cohérence des données (API REST mobile + navigateur)
 *
 * Ce fichier expose une route REST pour exécuter les règles de cohérence
 * d'un thème (formulaire) contre les données déjà sauvegardées en base,
 * pour un établissement scolaire donné.
 *
 * ─── Principe du contrôle de cohérence ───────────────────────────────────────
 *
 * Les règles de cohérence sont stockées dans deux tables du dictionnaire :
 *
 *   DICO_REGLE_THEME
 *     Contient une règle SQL (SQL_REGLE_THEME) produisant une valeur agrégée.
 *     Ex: SELECT SUM(NB_ELEVES_G) FROM TAB_EFFECTIFS WHERE CODE_ETAB='X' AND CODE_ANNEE=Y
 *
 *   DICO_REGLE_THEME_ASSOC
 *     Associe deux règles du même thème via un critère de comparaison (>, <, =, <=, >=).
 *     Si la comparaison échoue, c'est une violation de cohérence.
 *     Ex: SUM(effectifs_garçons) <= SUM(effectifs_total) — si faux, erreur signalée.
 *
 * Le traitement est délégué à controle_theme_batch.class.php (classe controle_theme)
 * qui charge les règles, exécute les SQL et stocke les violations dans
 * $tab_regles_theme_assoc_not_ok.
 *
 * ─── Routes exposées ──────────────────────────────────────────────────────────
 *
 *  GET /theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
 *      → Route MOBILE : inclut id_annee pour fonctionner sans session navigateur.
 *        Vérifie les droits avec fallback mobile (ADMIN_USERS si DICO_FIXE_REGROUPEMENT échoue).
 *
 *  GET /theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}
 *      → Route NAVIGATEUR (rétrocompatible) : utilise $_SESSION['annee'] existante.
 *
 * ─── Format de réponse JSON ───────────────────────────────────────────────────
 *
 *  {
 *    "se_status":  200,
 *    "se_message": "OK",
 *    "se_data": {
 *      "nb_erreurs": 2,
 *      "erreurs": [
 *        {
 *          "id_regle":       12,
 *          "id_regle_assoc": 7,
 *          "message":        "Effectif total ≠ somme tranches d'âge : 120 > 98",
 *          "regle_1":        "Effectif total",
 *          "regle_2":        "Somme tranches d'âge",
 *          "critere":        "<="
 *        },
 *        ...
 *      ]
 *    }
 *  }
 *
 * ─── Notes importantes ────────────────────────────────────────────────────────
 *  - L'ID thème peut être composite (ex: 15702 = thème 1570 + secteur 2).
 *    La fonction controle_strip_theme_id() retire le suffixe secteur avant toute requête.
 *  - La variable $_SESSION['annee'] est injectée avant l'appel au batch
 *    car les SQL des règles peuvent utiliser des variables de session via eval().
 *  - En cas d'exception dans controle_theme_batch, le traitement continue
 *    (une règle en erreur ne bloque pas les autres).
 *  - Compatibilité PHP 7.3.4 garantie (pas de syntaxe PHP 8+).
 *
 * @author    Projet StatEduc MEN — développement mobile AK / sessions 11-15
 * @version   session-15 (commentaires français complets)
 * @requires  Slim v2, controle_theme_batch.class.php
 */

require_once 'common_ws.php';

$app = new \Slim\Slim();

// ─── Constantes de réponse JSON (définies dans params_ws.php) ─────────────────
$lib_status  = $GLOBALS['PARAM_WS']['LIB_STATUS'];   // "se_status"
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];  // "se_message"
$lib_data    = $GLOBALS['PARAM_WS']['LIB_DATA'];     // "se_data"
$status_ok   = $GLOBALS['PARAM_WS']['STATUS_OK'];    // 200
$status_ko   = $GLOBALS['PARAM_WS']['STATUS_KO'];    // 400


// ═════════════════════════════════════════════════════════════════════════════
// HELPER : controle_strip_theme_id()
// ─────────────────────────────────────────────────────────────────────────────
// Retire le suffixe secteur d'un ID thème composite.
//
// L'app Flutter construit un ID thème composite en concaténant l'ID thème
// réel avec l'ID secteur pour garantir l'unicité dans son SQLite local.
// Exemple :  id_theme=15702, id_sector=2  → retourne "1570"
//            id_theme=5601,  id_sector=1  → retourne "560"
//
// La base de données DICO_REGLE_THEME stocke l'ID thème original (1570),
// donc il faut retirer le suffixe avant toute requête SQL.
//
// Algorithme :
//   1. Convertir les deux IDs en chaînes
//   2. Si la longueur du thème > longueur du secteur (cas composite)
//      ET secteur != '0' ET secteur non vide
//   3. Extraire le préfixe en retirant les n derniers caractères (n = longueur secteur)
//   4. Vérifier que le préfixe est un entier positif valide
//   5. Retourner le préfixe ou l'ID original si pas de stripping possible
//
// @param  string|int $id_theme   ID thème (potentiellement composite)
// @param  string|int $id_sector  ID secteur
// @return string                  ID thème sans suffixe secteur
// ═════════════════════════════════════════════════════════════════════════════
function controle_strip_theme_id($id_theme, $id_sector) {
    $str_theme  = '' . $id_theme;
    $str_sector = '' . $id_sector;
    $len_theme  = strlen($str_theme);
    $len_sector = strlen($str_sector);
    // Seulement si le thème est plus long que le secteur (cas composite valide)
    if ($len_theme > $len_sector && $str_sector !== '0' && $str_sector !== '') {
        $candidate = substr($str_theme, 0, $len_theme - $len_sector);
        // Vérification que le résultat est un entier positif valide
        if (is_numeric($candidate) && (int)$candidate > 0) {
            return $candidate;
        }
    }
    return $str_theme; // pas de stripping possible → retourne l'original
}


// ═════════════════════════════════════════════════════════════════════════════
// HELPER : controle_run_for_theme()
// ─────────────────────────────────────────────────────────────────────────────
// Exécute tous les contrôles de cohérence pour un thème donné et retourne
// la liste de toutes les violations détectées.
//
// Processus :
//   1. Requête DICO_REGLE_THEME_ASSOC pour trouver tous les ID_ASSOC_REG_THM
//      associés au thème (avec ACTIVER_CTRL = 1 pour ne prendre que les actifs).
//   2. Pour chaque ID_ASSOC_REG_THM :
//      a. Instancie controle_theme (controle_theme_batch.class.php)
//         → Le constructeur appelle automatiquement get_regles() et controle_regles_theme()
//      b. Parcourt tab_regles_theme_assoc_not_ok (règles en violation)
//      c. Ajoute chaque violation dans le tableau $erreurs
//   3. Retourne le tableau complet des violations (peut être vide si tout est OK)
//
// En cas d'exception sur une règle : erreur loggée, traitement continue.
//
// @param  string|int $raw_theme_id  ID thème réel (après strip du suffixe secteur)
// @param  string     $langue        Code langue pour les libellés ('fr', 'en', ...)
// @param  string|int $id_etab       Code établissement scolaire
// @param  string|int $id_year       Code année scolaire
// @param  string     $filtre_val    Code filtre/période (chaîne vide si aucun)
// @return array                     Tableau des violations [{id_regle, id_regle_assoc, message, ...}]
// ═════════════════════════════════════════════════════════════════════════════
function controle_run_for_theme($raw_theme_id, $langue, $id_etab, $id_year, $filtre_val) {
    require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/controle_theme_batch.class.php';

    // ── Recherche de tous les IDs d'association de règles pour ce thème ──────
    // DICO_REGLE_THEME_ASSOC lie deux règles (R1 du thème courant, R2 du thème associé)
    // via un ID_ASSOC_REG_THM. On cherche les associations où la règle de gauche (R1)
    // appartient au thème courant. ACTIVER_CTRL=1 filtre les règles actives uniquement.
    $sql_assoc_ids = "SELECT DISTINCT DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM
                      FROM DICO_REGLE_THEME_ASSOC
                      INNER JOIN DICO_REGLE_THEME AS DICO_REGLE_THEME_1
                          ON DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME = DICO_REGLE_THEME_1.ID_REGLE_THEME
                      WHERE DICO_REGLE_THEME_1.ID_THEME = " . (int)$raw_theme_id . "
                      AND DICO_REGLE_THEME_ASSOC.ACTIVER_CTRL = 1";

    $assoc_rows = $GLOBALS['conn_dico']->GetAll($sql_assoc_ids);

    $erreurs = array();

    // Si aucune règle d'association configurée pour ce thème → cohérence OK (0 erreurs)
    if (!is_array($assoc_rows) || count($assoc_rows) === 0) {
        return $erreurs;
    }

    // ── Exécution du batch de contrôle pour chaque règle d'association ────────
    foreach ($assoc_rows as $assoc_row) {
        // Normalisation des noms de colonnes en minuscules (compatibilité Oracle/MySQL)
        $assoc_row_lower = array_change_key_case($assoc_row, CASE_LOWER);
        $ctrl_id = isset($assoc_row_lower['id_assoc_reg_thm'])
            ? (int)$assoc_row_lower['id_assoc_reg_thm']
            : 0;
        if ($ctrl_id <= 0) continue; // ignorer les lignes sans ID valide

        // Instanciation de controle_theme :
        //   Paramètres : ctrl_id, langue, code_etablissement, code_annee, code_filtre, $alert
        //   $alert = false → mode batch sans sortie HTML (pas de popups JavaScript)
        // Le constructeur appelle automatiquement get_regles() + controle_regles_theme()
        try {
            $ctrl = new controle_theme(
                $ctrl_id,
                $langue,
                $id_etab,
                $id_year,
                $filtre_val,
                false // mode batch : pas d'affichage HTML des alertes
            );

            // Parcours des violations détectées (tab_regles_theme_assoc_not_ok)
            // Structure : [id_regle => [id_regle_assoc => tab_données_violation]]
            if (is_array($ctrl->tab_regles_theme_assoc_not_ok)
                && count($ctrl->tab_regles_theme_assoc_not_ok) > 0) {

                foreach ($ctrl->tab_regles_theme_assoc_not_ok as $id_regle => $assocs) {
                    if (!is_array($assocs)) continue;
                    foreach ($assocs as $id_regle_assoc => $tab) {
                        // Récupération du message d'erreur traduit (préparé par get_regles())
                        $message = isset($tab['msg_assoc']) ? $tab['msg_assoc'] : '';

                        // Construction de l'objet erreur à retourner dans la réponse JSON
                        $erreurs[] = array(
                            'id_regle'       => $id_regle,       // ID de la règle principale
                            'id_regle_assoc' => $id_regle_assoc, // ID de la règle associée
                            'message'        => $message,        // message d'erreur traduit
                            'regle_1'        => isset($tab['nom_regle_1']) ? $tab['nom_regle_1'] : '',  // libellé R1
                            'regle_2'        => isset($tab['nom_regle_2']) ? $tab['nom_regle_2'] : '',  // libellé R2
                            'critere'        => isset($tab['critere_assoc']) ? $tab['critere_assoc'] : '', // opérateur
                        );
                    }
                }
            }
        } catch (Exception $e) {
            // Une règle en erreur ne bloque pas les autres — log et continuation
            error_log('[data_controle] Erreur controle_theme_batch pour ctrl_id=' . $ctrl_id . ': ' . $e->getMessage());
        }
    }

    return $erreurs;
}


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE PRINCIPALE (mobile) :
// GET /theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
// ─────────────────────────────────────────────────────────────────────────────
// Paramètres URL :
//   user      → login de l'utilisateur (pour vérification accès campagne)
//   id_camp   → ID campagne
//   id_sector → ID secteur (système éducatif : public/privé/franco-arabe...)
//   id_theme  → ID thème/formulaire (peut être composite ex: 15702)
//   id_etab   → code établissement scolaire (clé dans les tables de données)
//   id_filter → ID filtre/période (ou "null" / "0" si non filtré)
//   id_annee  → code année scolaire (ex: "2024") — contourne l'absence de session mobile
//
// Flux de traitement :
//   1. Résolution de l'année scolaire (URL > session > session injectée)
//   2. Strip du suffixe secteur de l'ID thème composite
//   3. Vérification des droits (DICO_FIXE_REGROUPEMENT, avec fallback ADMIN_USERS)
//   4. Récupération de la langue de session (défaut 'fr')
//   5. Exécution des contrôles via controle_run_for_theme()
//   6. Retour JSON avec la liste des violations
// ═════════════════════════════════════════════════════════════════════════════
$app->get(
    '/theme_controle/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:id_annee',
    function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_annee)
    use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {

        $msg_ok = $GLOBALS['PARAM_WS']['OK'];
        $msg_ko = $GLOBALS['PARAM_WS']['KO'];

        // ── 1. Résolution de l'année scolaire ────────────────────────────────
        // Priorité : paramètre URL (app mobile) > session navigateur existante
        $id_year = ($id_annee !== '' && $id_annee !== '0')
            ? $id_annee
            : (isset($_SESSION['annee']) ? $_SESSION['annee'] : '');

        // Injection dans la session PHP : les SQL des règles (via eval() dans le batch)
        // peuvent référencer $_SESSION['annee'] implicitement
        if ($id_year !== '') {
            $_SESSION['annee'] = $id_year;
        }

        // ── 2. Strip secteur de l'ID thème composite ────────────────────────
        // Ex: id_theme=15702, id_sector=2 → raw_theme_id="1570"
        $raw_theme_id = controle_strip_theme_id($id_theme, $id_sector);

        // ── 3. Vérification des droits d'accès à la campagne ─────────────────
        // Stratégie à deux niveaux (même logique que data_save.php) :
        //   Niveau 1 : vérification via DICO_FIXE_REGROUPEMENT (standard)
        //   Niveau 2 : fallback via ADMIN_USERS (utilisateurs mobiles uniquement)
        $period_query = ($id_filter !== 'null' && $id_filter !== '0')
            ? ' AND ID_PERIODE=' . (int)$id_filter . ' '
            : '';

        $is_mobile_request = ($id_annee !== '' && $id_annee !== '0');
        $access_ok = false;

        // Niveau 1 : vérification standard
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

        // Niveau 2 : fallback mobile — vérification existence utilisateur
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

        // ── 4. Récupération de la langue pour les libellés traduits ──────────
        // La langue est lue depuis la session. Défaut 'fr' si absente.
        // Les libellés des règles et messages d'erreur sont traduits dans DICO_TRADUCTION.
        $langue = isset($_SESSION['langue']) && $_SESSION['langue'] !== ''
            ? $_SESSION['langue']
            : 'fr';

        // Normalisation du filtre : null/0 → chaîne vide (pas de filtre actif)
        $filtre_val = ($id_filter === 'null' || $id_filter === '0') ? '' : $id_filter;

        // ── 5. Exécution des contrôles de cohérence ──────────────────────────
        // controle_run_for_theme() :
        //   - Trouve tous les ID_ASSOC_REG_THM pour ce thème
        //   - Instancie controle_theme_batch pour chacun
        //   - Collecte les violations dans $erreurs
        $erreurs = controle_run_for_theme($raw_theme_id, $langue, $id_etab, $id_year, $filtre_val);

        // ── 6. Construction et envoi de la réponse JSON ───────────────────────
        $result = array(
            'nb_erreurs' => count($erreurs), // nombre total de violations
            'erreurs'    => $erreurs,        // détail des violations
        );

        $rps = array(
            $lib_status  => $status_ok,
            $lib_message => $msg_ok,
            $lib_data    => $result,
        );
        echo json_encode($rps);
    }
);


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE RÉTROCOMPATIBLE (navigateur web) :
// GET /theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}
// ─────────────────────────────────────────────────────────────────────────────
// Version sans id_annee, utilisée depuis le navigateur web où la session PHP
// est déjà établie avec $_SESSION['annee'] défini lors de la connexion.
// Pas de fallback mobile (l'utilisateur navigateur a forcément sa session).
// ═════════════════════════════════════════════════════════════════════════════
$app->get(
    '/theme_controle/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter',
    function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter)
    use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {

        // Lecture de l'année depuis la session navigateur (obligatoire ici)
        $id_annee = isset($_SESSION['annee']) ? $_SESSION['annee'] : '0';
        $msg_ok = $GLOBALS['PARAM_WS']['OK'];
        $msg_ko = $GLOBALS['PARAM_WS']['KO'];

        $id_year = $id_annee;
        if ($id_year !== '') {
            $_SESSION['annee'] = $id_year; // assure la cohérence de session
        }

        // Strip secteur de l'ID thème composite (même logique que la route mobile)
        $raw_theme_id = controle_strip_theme_id($id_theme, $id_sector);

        $period_query = ($id_filter !== 'null' && $id_filter !== '0')
            ? ' AND ID_PERIODE=' . (int)$id_filter . ' '
            : '';

        // Vérification accès campagne (standard, sans fallback mobile)
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

        // Exécution des contrôles et réponse JSON (même logique que la route mobile)
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

// Lancement du routeur Slim (traite la requête HTTP courante)
$app->run();
