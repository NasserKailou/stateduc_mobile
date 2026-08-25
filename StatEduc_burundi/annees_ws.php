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

    // Vérification connexion DB
    if (!isset($GLOBALS['conn_dico']) || $GLOBALS['conn_dico'] === false) {
        error_log('[annees_ws] /list — ERREUR: conn_dico non disponible');
        echo json_encode(array(
            $lib_status  => $status_ko,
            $lib_message => 'DB unavailable',
            $lib_data    => array(),
        ));
        return;
    }

    // Pas d'alias AS : AdoDB (ADODB_ASSOC_CASE_UPPER) ignore les alias minuscules
    // et retourne toujours les clés en MAJUSCULES. On lit directement $r[$col_xxx].
    $requete = 'SELECT '
        . $col_code    . ', '
        . $col_libelle . ', '
        . $col_ordre
        . ' FROM ' . $table
        . ' ORDER BY ' . $col_ordre . ' ASC';

    error_log('[annees_ws] /list — login=' . $login . ' SQL: ' . $requete);

    $rows = $GLOBALS['conn_dico']->GetAll($requete);

    if ($rows === false || !is_array($rows)) {
        error_log('[annees_ws] /list — requête échouée ou aucune ligne');
        $rows = array();
    }

    // Normaliser : s'assurer que code/ordre sont des entiers, libelle une chaîne propre.
    //
    // IMPORTANT — AdoDB ADODB_ASSOC_CASE_UPPER (défini dans fonctions.inc.php via common_ws.php) :
    // GetAll() retourne TOUJOURS les clés de résultat en MAJUSCULES, même quand on écrit
    // des alias en minuscules dans le SELECT (ex: "AS code" → clé "CODE").
    // Solution : supprimer les alias AS et lire directement les noms de colonnes
    // originaux en MAJUSCULES ($col_code = "CODE_TYPE_ANNEE", etc.).
    // On reconstruit le tableau de sortie avec les clés minuscules attendues par Flutter.
    $annees = array();
    foreach ($rows as $r) {
        $annees[] = array(
            'code'    => (int)$r[$col_code],       // clé = "CODE_TYPE_ANNEE" (UPPER)
            'libelle' => trim((string)$r[$col_libelle]), // clé = "LIBELLE_TYPE_ANNEE" (UPPER)
            'ordre'   => (int)$r[$col_ordre],       // clé = "ORDRE_TYPE_ANNEE" (UPPER)
        );
    }

    error_log('[annees_ws] /list — ' . count($annees) . ' année(s) retournée(s)');

    echo json_encode(array(
        $lib_status  => $status_ok,
        $lib_message => $GLOBALS['PARAM_WS']['OK'],
        $lib_data    => $annees,
    ));
});

$app->run();
