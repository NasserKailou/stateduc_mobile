<?php

/**
 * common_ws.php
 *
 * Fichier d'amorçage et middleware commun a tous les Web Services mobiles.
 * Charge config_app.php, params.php, params_sys.php, params_ws.php.
 * Initialise les connexions base de donnees et les chemins globaux.
 * Point d'entree unique pour toute la couche WS REST de l'app mobile.
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 * @sessions 1-19
 * @modifie Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
 *          Toutes les modifications et nouveautes sont documentees
 *          directement dans le code avec des commentaires en francais.
 */
require_once 'config_app.php';
require_once 'params.php';
require_once 'params_sys.php';
require_once 'params_ws.php';
include 'constants.php';

require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
//include slim
require $GLOBALS['SISED_PATH_LIB'] . 'codeguy-Slim/Slim/Slim.php';
require $GLOBALS['SISED_PATH_LIB'] . 'codeguy-Slim/Slim/Middleware.php';
require $GLOBALS['SISED_PATH_INC'] . 'web_services/HttpAuth.php';
\Slim\Slim::registerAutoloader();
//include slim
require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
manage_magic_quotes();
set_time_limit(0);
ini_set("memory_limit", "64M");

//Variable ADODB
$ADODB_FETCH_MODE   = ADODB_FETCH_ASSOC;
$ADODB_CACHE_DIR    = $SISED_PATH . 'server-side/adodbcache/';

if (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb')) {
	//require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
	$conn_dico = ADONewConnection('access');
	$conn_dico->Connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb' . ';Uid=Admin;Pwd=\'\';');
	$GLOBALS['conn_dico'] = $conn_dico;
}elseif (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.accdb')) {
	//require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
	$conn_dico = ADONewConnection('access');
	$conn_dico->Connect('Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $GLOBALS['SISED_PATH'] . 'server-side/dico_DB.accdb' . ';Uid=Admin;Pwd=\'\';');
	$GLOBALS['conn_dico'] = $conn_dico;
}

require_once $GLOBALS['SISED_PATH_CLS'] . 'connexion.class.php';

$source = false;
$connexion = new connexion();
$connexion->init($source);

require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';

// session 37 : remplacement de read_and_close par session normale + session_write_close explicite
// Raison : read_and_close provoquait la perte silencieuse de toutes les ecritures $_SESSION
// (langue, annee, secteur, etc.) rendant les WS stateless et cassant les routes data_camp.php.
// La protection anti-deadlock XAMPP est desormais assuree par session_write_close() apres
// toutes les initialisations, limitant la duree du verrou de session au strict minimum.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$requete = "SELECT * FROM PARAM_DEFAUT;";
$params = $GLOBALS['conn_dico']->GetRow($requete);
if(!isset($_SESSION['langue']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
		$_SESSION['langue'] = trim($params['CODE_LANGUE']);
		//set_tab_session('secteurs', $_SESSION['langue']);
} else {
		if(isset($_GET['langue']) && $_GET['langue'] != $_SESSION['langue']) {
			$_SESSION['langue'] = $_GET['langue'];
		}
}
if(!isset($_SESSION['secteur']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
		$_SESSION['secteur']    =   trim($params['CODE_SECTEUR']);
		//set_tab_session('secteurs', $_SESSION['langue']);
} else {
		//Il faut limiter l'utilisateur à ses secteurs en cas de limitation d'acces activée
		if(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0){
			if(!in_array($_SESSION['secteur'],$_SESSION['fixe_secteurs'])){
				$_SESSION['secteur'] = $_SESSION['tab_secteur'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
			}
		}

}
set_tab_session('secteurs', $_SESSION['langue']);
// Les chaines de regroupement
set_tab_session('chaines', '');                
// Les années
set_tab_session('annees', ''); 
// Les filtres
set_tab_session('filtres', ''); 
// Les langues
set_tab_session('langues', ''); 
// Les périodes
if(isset($GLOBALS['PARAM']['TYPE_PERIODE']) && $GLOBALS['PARAM']['TYPE_PERIODE'] <> '')
	set_tab_session('periodes', ''); 
/**
* Paramètres
*
*/
$_SESSION['NB_NIVEAU_ARBO'] = $params['NB_NIVEAU_ARBO'];
$_SESSION['ARMOIRIES_PAYS'] = $params['ARMOIRIES_PAYS'];
$_SESSION['DRAPEAU_PAYS'] = $params['DRAPEAU_PAYS'];
$_SESSION['NOM_PAYS'] = $params['NOM_PAYS'];
// Rechargement des libellés pages
$requete   = "SELECT LIBELLE, CODE_LIBELLE
				FROM DICO_LIBELLE_PAGE 
				WHERE CODE_LANGUE='".$_SESSION['langue']."';";
$_SESSION['tab_libelles'] = $GLOBALS['conn_dico']->GetAll($requete);
//set_tab_session('secteurs', $_SESSION['langue']);
//gestion de l'année
if(!isset($_SESSION['annee']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
		$_SESSION['annee']    =   trim($params['CODE_ANNEE']);
} else {
		if(isset($_GET['annee']) && $_GET['annee'] != $_SESSION['annee']) {
				$_SESSION['annee'] = $_GET['annee'];		
		}
}
//gestion de la filtre
if(!isset($_SESSION['filtre']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
		$_SESSION['filtre']    =   trim($params['CODE_FILTRE']);
} else {
		if(isset($_GET['filtre']) && $_GET['filtre'] != $_SESSION['filtre']) {
				$_SESSION['filtre'] = $_GET['filtre'];
		}
}
//gestion du style
if(!isset($_SESSION['style'])) {
	$_SESSION['style'] = trim($params['CODE_STYLE']);
} else {
	if(isset($_GET['style']) && $_GET['style'] != $_SESSION['style']) {
		$_SESSION['style'] = $_GET['style'];
	}
}
session_write_close(); // session 37 : libere le verrou de session apres init
?>
