<?php

/**
 * annees_ws.php
 *
 * Web Service REST - Liste des années de recensement disponibles (TYPE_ANNEE).
 *
 * Route : GET /list/:login
 *   Retourne toutes les années disponibles triées par ORDRE_TYPE_ANNEE ASC.
 *   Réponse JSON : { se_status:200, se_message:'ok',
 *                    se_data: [{ code, libelle, ordre }, ...] }
 *
 * Authentification : HTTP Basic (identique aux autres endpoints mobiles).
 * Pas de paramètre année dans l'URL — la liste est globale (indépendante
 * de la campagne ou de l'utilisateur).
 *
 * Utilisé par l'onglet « Année » de la page Paramètres de l'app Flutter
 * pour permettre au directeur d'école de choisir une année de recensement.
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 * @session AK-YEAR-01
 */

require_once 'common_ws.php';

$app = new \Slim\Slim();

$lib_status  = $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data    = $GLOBALS['PARAM_WS']['LIB_DATA'];
$status_ok   = $GLOBALS['PARAM_WS']['STATUS_OK'];
$status_ko   = $GLOBALS['PARAM_WS']['STATUS_KO'];

$app->add(new \HttpAuth());

// ─── GET /list/:login ────────────────────────────────────────────────────────
// Retourne la liste complète des années de TYPE_ANNEE triées par ORDRE ASC.
// :login est requis pour la cohérence avec les autres routes (HttpAuth l'exige)
// mais n'est pas utilisé dans la requête SQL (la liste est globale).
$app->get('/list/:login', function ($login) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {

    $col_code    = $GLOBALS['PARAM']['CODE']    . '_' . $GLOBALS['PARAM']['TYPE_ANNEE'];   // CODE_TYPE_ANNEE
    $col_libelle = $GLOBALS['PARAM']['LIBELLE'] . '_' . $GLOBALS['PARAM']['TYPE_ANNEE'];   // LIBELLE_TYPE_ANNEE
    $col_ordre   = $GLOBALS['PARAM']['ORDRE']   . '_' . $GLOBALS['PARAM']['TYPE_ANNEE'];   // ORDRE_TYPE_ANNEE
    $table       = $GLOBALS['PARAM']['TYPE_ANNEE'];                                        // TYPE_ANNEE

    // AK-YEAR-03 fix: TYPE_ANNEE est dans la base principale (conn), pas dans dico_DB (conn_dico).
    // La fonction set_tab_session('annees') dans fonctions.inc.php utilise $GLOBALS['conn'],
    // ce qui confirme que TYPE_ANNEE appartient à la base de données principale.
    if (!isset($GLOBALS['conn']) || $GLOBALS['conn'] === false) {
        error_log('[annees_ws] /list — ERREUR: conn non disponible');
        echo json_encode(array(
            $lib_status  => $status_ko,
            $lib_message => 'DB unavailable',
            $lib_data    => array(),
        ));
        return;
    }

    // SELECT * pour éviter tout problème de nom de colonne — AdoDB retournera les
    // vrais noms tels qu'ils sont dans la table (ADODB_ASSOC_CASE_UPPER → MAJUSCULES).
    // On filtre ensuite sur les clés attendues ($col_code, $col_libelle, $col_ordre).
    $requete = 'SELECT * FROM ' . $table . ' ORDER BY ' . $col_ordre . ' ASC';

    $rows = $GLOBALS['conn']->GetAll($requete);

    if ($rows === false || !is_array($rows)) {
        error_log('[annees_ws] /list — requête échouée: ' . $GLOBALS['conn']->ErrorMsg()
                  . ' col_code=' . $col_code . ' table=' . $table);
        $rows = array();
    }

    // Reconstruction du tableau de sortie avec les clés minuscules attendues par Flutter.
    // ADODB_ASSOC_CASE_UPPER (défini dans fonctions.inc.php) force les clés en MAJUSCULES,
    // ce qui correspond aux noms de colonnes $col_code/$col_libelle/$col_ordre.
    // En cas d'ambiguïté (SELECT *), on tente aussi array_change_key_case pour sécuriser.
    $annees = array();
    foreach ($rows as $r) {
        // Normaliser les clés en MAJUSCULES pour être sûr (ADODB_ASSOC_CASE_UPPER)
        $r_upper = array_change_key_case($r, CASE_UPPER);
        $code    = isset($r_upper[$col_code])    ? (int)$r_upper[$col_code]            : 0;
        $libelle = isset($r_upper[$col_libelle]) ? trim((string)$r_upper[$col_libelle]) : '';
        $ordre   = isset($r_upper[$col_ordre])   ? (int)$r_upper[$col_ordre]            : 0;
        $annees[] = array(
            'code'    => $code,
            'libelle' => $libelle,
            'ordre'   => $ordre,
        );
    }

    echo json_encode(array(
        $lib_status  => $status_ok,
        $lib_message => $GLOBALS['PARAM_WS']['OK'],
        $lib_data    => $annees,
    ));
});


// ─── GET /active/:login ──────────────────────────────────────────────────────
// AK-YEAR-MULTI-02 : Retourne l'année de collecte ACTIVE du serveur.
// Utilisé par l'app Flutter avant tout envoi ou rechargement pour vérifier
// la cohérence entre l'année active mobile et l'année active serveur.
// $_SESSION['annee'] est défini par common_ws.php via set_tab_session('annees').
// Réponse JSON : { se_status:200, se_message:'ok',
//                  se_data: { code: <int>, libelle: <string> } }
$app->get('/active/:login', function ($login) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {

    // Récupère l'année active de la session serveur
    $annee_active = isset($_SESSION['annee']) ? (int)$_SESSION['annee'] : 0;

    if ($annee_active <= 0) {
        // Pas d'année active en session — erreur explicite
        error_log('[annees_ws] /active — ERREUR: $_SESSION[annee] absent ou nul pour login=' . $login);
        echo json_encode(array(
            $lib_status  => $status_ko,
            $lib_message => 'Année active non définie sur le serveur',
            $lib_data    => array('code' => 0, 'libelle' => ''),
        ));
        return;
    }

    // Recherche du libellé de l'année active dans TYPE_ANNEE
    $col_code    = $GLOBALS['PARAM']['CODE']    . '_' . $GLOBALS['PARAM']['TYPE_ANNEE'];
    $col_libelle = $GLOBALS['PARAM']['LIBELLE'] . '_' . $GLOBALS['PARAM']['TYPE_ANNEE'];
    $table       = $GLOBALS['PARAM']['TYPE_ANNEE'];

    $libelle_annee = '';
    if (isset($GLOBALS['conn']) && $GLOBALS['conn'] !== false) {
        $sql_lib = 'SELECT ' . $col_libelle . ' FROM ' . $table
                 . ' WHERE ' . $col_code . ' = ' . $annee_active;
        $row_lib = $GLOBALS['conn']->GetRow($sql_lib);
        if ($row_lib !== false && is_array($row_lib)) {
            $r_upper       = array_change_key_case($row_lib, CASE_UPPER);
            $libelle_annee = isset($r_upper[$col_libelle])
                           ? trim((string)$r_upper[$col_libelle])
                           : '';
        }
    }

    echo json_encode(array(
        $lib_status  => $status_ok,
        $lib_message => $GLOBALS['PARAM_WS']['OK'],
        $lib_data    => array(
            'code'    => $annee_active,
            'libelle' => $libelle_annee,
        ),
    ));
});

$app->run();
