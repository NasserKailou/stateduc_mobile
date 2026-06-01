<?php
/**
 * data_reload.php — Rechargement des données d'un formulaire depuis le serveur
 *
 * Ce fichier permet de récupérer les données déjà saisies et sauvegardées en
 * base pour un thème/formulaire et un établissement donnés. Utilisé pour :
 *   - Pré-remplir un formulaire déjà partiellement saisi
 *   - Synchroniser les données serveur vers l'app mobile (après offline)
 *   - Exporter les données multi-établissements en ZIP (route school_data)
 *
 * ─── Routes exposées ──────────────────────────────────────────────────────────
 *
 *  GET /theme_data/{login}/{id_sector}/{id_theme}/{id_camp}/{id_etab}/{id_filter}
 *      → Retourne les données sauvegardées pour un thème+établissement.
 *        Appel interne vers questionnaire_reload_ws.php via curl.
 *        Réponse : contenu HTML brut du formulaire pré-rempli (pas de JSON).
 *        L'app Flutter parse ce HTML pour extraire les valeurs des champs.
 *
 *  POST /school_data/{user}/{id_sector}/{id_camp}/{id_year}/{id_period}
 *      → Export ZIP multi-établissements (synchronisation tablette \u2192 serveur).
 *        Corps POST : liste des ID établissements (ID_SCHOOLS=1,2,3,...)
 *        Retourne un fichier ZIP contenant les XML de données par établissement.
 *
 *  GET /revisions_list/{user_login}
 *      → Liste des révisions disponibles (mises à jour de référentiel).
 *
 *  GET /revision_files/{user_login}/{id_rev}
 *      → Liste des fichiers d'une révision donnée.
 *
 *  GET /revision_zip/{user_login}/{rev_num}
 *      → Téléchargement du ZIP d'une révision.
 *
 * ─── Notes importantes ────────────────────────────────────────────────────────
 *  - La route /theme_data utilise $_SESSION['annee'] directement (pas de paramètre
 *    id_annee dans l'URL). Pour l'app mobile, la session est déjà initialisée
 *    lors du premier appel avec le token de connexion.
 *  - Le paramètre id_filter peut contenir un ":" pour indiquer une combinaison
 *    filtre + numéro de formulaire (format "filtre:num_form").
 *  - L'ancienne route GET /theme_data (commentée) est conservée pour référence.
 *
 * @author    Projet StatEduc MEN — développement mobile AK / sessions 11-15
 * @version   session-15 (commentaires français complets)
 * @requires  Slim v2, php-curl-class, ADODB_XML
 */

require_once 'common_ws.php';

// Chargement des bibliothèques (XML et cURL)
require_once $GLOBALS['SISED_PATH_LIB'] . 'adodb_xml/class.ADODB_XML.php';
require $GLOBALS['SISED_PATH_LIB'] . 'Curl/Curl.php';

use \Curl\Curl;

// ─── Instance cURL pour les appels internes vers questionnaire_reload_ws.php ──
$curl = new Curl();
// En-têtes HTTP imitant un navigateur (nécessaire pour questionnaire_reload_ws.php)
$curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
$curl->setHeader('Accept', '*/*');
$curl->setHeader('Accept-Encoding', 'gzip,deflate');
$curl->setHeader('Accept-Language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');

// ─── Constantes de réponse JSON ───────────────────────────────────────────────
$lib_status  = $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data    = $GLOBALS['PARAM_WS']['LIB_DATA'];
$status_ok   = $GLOBALS['PARAM_WS']['STATUS_OK'];
$status_ko   = $GLOBALS['PARAM_WS']['STATUS_KO'];

$app = new \Slim\Slim();


// ═════════════════════════════════════════════════════════════════════════════
// ANCIENNE ROUTE (commentée — conservée pour référence historique)
// GET /theme_data/:user/:id_sector/:id_teme/:id_camp/:id_etab/:id_filter
// ─────────────────────────────────────────────────────────────────────────────
// Ancienne version qui parsait le HTML de réponse pour extraire les formulaires.
// Remplacée par la version ci-dessous qui retourne directement la réponse brute.
// ═════════════════════════════════════════════════════════════════════════════
/*$app->get('/theme_data/:user/:id_sector/:id_teme/:id_camp/:id_etab/:id_filter', function (...) {
   ... (ancien code de parsing DOM) ...
});*/


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE PRINCIPALE : GET /theme_data/:user/:id_sector/:id_theme/:id_camp/:id_etab/:id_filter
// ─────────────────────────────────────────────────────────────────────────────
// Récupère les données déjà saisies pour un formulaire+établissement et les
// retourne sous forme de HTML pré-rempli (sortie directe de questionnaire_reload_ws.php).
//
// Paramètres URL :
//   user      → login de l'utilisateur (pour vérification accès)
//   id_sector → ID secteur (public/privé/franco-arabe...)
//   id_theme  → ID thème/formulaire (peut être composite)
//   id_camp   → ID campagne
//   id_etab   → code établissement scolaire
//   id_filter → ID filtre/période ou "null"
//              Format spécial : "filtre:num_form" pour formulaires multi-pages
//
// Flux de traitement :
//   1. Vérification accès campagne via DICO_FIXE_REGROUPEMENT + $_SESSION['annee']
//   2. Construction URL interne questionnaire_reload_ws.php
//   3. Gestion du filtre composé (":") → paramètres &filtre= et &filtre_num_form=
//   4. Appel curl GET → réponse HTML directement transmise au client
//
// IMPORTANT : utilise $_SESSION['annee'] (pas de paramètre id_annee dans l'URL).
// Pour l'app mobile, la session doit avoir été initialisée avant cet appel.
// ═════════════════════════════════════════════════════════════════════════════
$app->get('/theme_data/:user/:id_sector/:id_theme/:id_camp/:id_etab/:id_filter', function ($user, $id_sector, $id_theme, $id_camp, $id_etab, $id_filter) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl) {
	$msg_ko = $GLOBALS['PARAM_WS']['KO'];
	
	// Lecture de l'année scolaire depuis la session PHP
	$id_year = $_SESSION['annee'];
	
	// Filtre période : clause SQL optionnelle
	$period_query = "";
	if ($id_filter != "null") {
		$period_query = " AND ID_PERIODE=".$id_filter." "; 
	}

	// Vérification que l'utilisateur a accès à cette campagne (via ses affectations)
	$requete = "SELECT DISTINCT ID_CAMPAGNE
				FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU
				WHERE AU.NOM_USER LIKE '".$user."' 
			AND DFR.ID_USER=AU.CODE_USER 
				AND ID_ANNEE=".$id_year."   
				".$period_query." 
			AND ID_CAMPAGNE=".$id_camp.";"; 
	$camps = $GLOBALS['conn_dico']->GetAll($requete); 
  	if (count($camps) == 0 || $camps[0] == '') {
		$rps = array($lib_status=>$status_ko, $lib_message=>$msg_ko, $lib_data=>"L'utilisateur '".$user."' n'a pas acces a cette campagne");
		echo json_encode($rps);
		return;
	}

	// ── Callback cURL succès ─────────────────────────────────────────────────
	// Retourne directement la réponse HTML brute de questionnaire_reload_ws.php.
	// Cette réponse contient le formulaire avec les valeurs pré-remplies.
	// L'app Flutter parsera ce HTML pour en extraire les valeurs des champs.
	$curl->success(function($instance) use ($id_camp) {
		echo $instance->response; // HTML pré-rempli transmis directement
	});

	// ── Callback cURL erreur ─────────────────────────────────────────────────
	// En cas d'erreur (timeout, connexion refusée), retourne un JSON d'erreur standard.
	$curl->error(function($instance) {
		$rps = array($GLOBALS['PARAM_WS']['LIB_STATUS']=>$GLOBALS['PARAM_WS']['STATUS_OK'],$GLOBALS['PARAM_WS']['LIB_MESSAGE']=>$GLOBALS['PARAM_WS']['KO'],$GLOBALS['PARAM_WS']['LIB_DATA']=>$instance->error_code." : ".$instance->error_message);		
		echo json_encode($rps);
	});
	$curl->complete(function($instance) {
		// Callback de complétion (vide — réservé pour usage futur)
	});
	
	// Construction de l'URL vers questionnaire_reload_ws.php
	// Paramètres : sector, theme (peut être composite), établissement, campagne, année
	$urlBase = $GLOBALS['SISED_AURL'].'questionnaire_reload_ws.php?sector='.$id_sector.'&theme='.$id_theme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp.'&code_annee='.$id_year;

	// Gestion du filtre période avec support du format composé "filtre:num_form"
	// Le format "1234:2" signifie : filtre=1234 ET numéro de formulaire = 2
	// (utilisé pour les campagnes avec plusieurs formulaires par période)
	if ($id_filter != null) {
		if (strpos($id_filter, ':') !== false) {
			// Format composé : séparer filtre et numéro de formulaire
			$ids = explode(':', $id_filter);
			$urlBase .= '&filtre='.preg_replace('/_/','/',$ids[0]).'&filtre_num_form='.$ids[1];
		} else {
			// Format simple : un seul filtre
			$urlBase .= '&filtre='.$id_filter;
		}
	}
	// Appel GET vers questionnaire_reload_ws.php (retourne le HTML pré-rempli)
	$curl->get($urlBase);	
});


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE : POST /school_data/:user/:id_sector/:id_camp/:id_year/:id_period
// ─────────────────────────────────────────────────────────────────────────────
// Export ZIP multi-établissements pour synchronisation tablette.
// Génère un fichier ZIP contenant les données de plusieurs établissements
// exportées en format ADODB_XML (un fichier XML par table de données).
//
// Corps POST :
//   ID_SCHOOLS → liste des codes établissements séparés par des virgules
//
// Processus :
//   1. Vérification utilisateur dans ADMIN_USERS
//   2. Récupération de la liste des thèmes de la campagne
//   3. Pour chaque thème : extraction des tables et champs concernés
//   4. Génération des requêtes d'export par établissement et par table
//   5. Export XML via ADODB_XML (une table = un fichier XML)
//   6. Compression en ZIP et envoi au client (Content-Type: octet-stream)
// ═════════════════════════════════════════════════════════════════════════════
$app->post('/school_data/:user/:id_sector/:id_camp/:id_year/:id_period', function ($user, $id_sector, $id_camp, $id_year, $id_period) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	$msg_ko = $GLOBALS['PARAM_WS']['KO'];
  
	// Vérification existence utilisateur
 	$sql = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$user'";
	$user_id = $GLOBALS['conn_dico']->GetOne($sql);   
	
	if (!$user_id || $user_id == '') {
		sendError("User not found!"); 
		return;
	}
  	
	$data_to_send = $app->request->post();
	$arrSchools = $data_to_send['ID_SCHOOLS'];
	$idSchools = explode(',', $arrSchools); // séparation de la liste des ID établissements
	
	$lang = $_SESSION['langue'];
	
	// Récupération de la liste des thèmes (formulaires) de la campagne pour ce secteur
	$lstThemes = get_camp_themes($id_sector, $id_camp, $lang);
	$lstThemes = array_change_key_case_recursive($lstThemes, CASE_LOWER);
	
	// Construction de la liste tables → champs à exporter
	$tabsAndFields = array();
	foreach($lstThemes as $i => $theme){
		// Tables "mères" du thème (tables contenant les données du formulaire)
		$lstTabs = get_tables_meres_theme($theme['id_theme']);
		foreach($lstTabs as $i => $tabLine){
			$tab = $tabLine['NOM_TABLE_MERE'];
			if ($tabsAndFields[$tab] == NULL) {
				$tabsAndFields[$tab] = array();
			}
			// Zones de saisie (champs) de cette table pour ce thème
			$zones = get_tab_mere_zones($theme['id_theme'], $tab);
			if (is_array($zones) && count($zones) > 0) {
				foreach($zones as $i => $zone){
					if (!in_array($zone['CHAMP_PERE'], $tabsAndFields[$tab])) {
						$tabsAndFields[$tab][] = $zone['CHAMP_PERE'];
					}
				}
			}
		}
	}

	// Génération des requêtes SQL d'export (avec placeholders _code_school_, etc.)
	$tabsAndQueries = generateExportQueries($tabsAndFields);
	
	// Création du répertoire temporaire d'export (identifié par timestamp)
	$rootDir = time();
	$exportDir = $GLOBALS['SISED_PATH']."server-side/import_export/".$rootDir;
	if (!file_exists($exportDir)) {
		mkdir($exportDir, 0777, true);
	}

	// Export des données pour chaque établissement et chaque table
	foreach($idSchools as $i => $idSchool) {
		if (!file_exists($exportDir."/".$idSchool)) {
			mkdir($exportDir."/".$idSchool, 0777, true);
		}
		foreach($tabsAndQueries as $tablename => $query) {
			// Substitution des placeholders par les valeurs concrètes
			$req = str_replace('_code_school_', $idSchool, $query); 
			$req = str_replace('_code_year_', $id_year, $req); 
			$req = str_replace('_code_camp_', $idSchool, $req); 
			$req = str_replace('_code_filtre_', $id_period, $req);
			// Export en XML via ADODB_XML
			$adodbXML = new ADODB_XML("1.0", "ISO-8859-1");
			$adodbXML->ConvertToXML2($GLOBALS['conn'], $req, $tabsAndFields[$tablename], $tablename, $exportDir."/".$idSchool."/".$tablename.".xml");		
		}
	}
	
	// Compression du répertoire d'export en ZIP et envoi au client
	if (create_zip($GLOBALS['SISED_PATH']."server-side/import_export", $rootDir)) {
		$fData = file_get_contents($GLOBALS['SISED_PATH']."server-side/import_export/".$rootDir.".zip");
		// En-têtes HTTP pour téléchargement de fichier binaire
		$app->response->header('Content-Type', 'application/octet-stream');
		$app->response->header('Pragma', "public");
		$app->response->header('Content-disposition:', 'attachment; filename='. $rootDir.".zip");
		$app->response->header('Content-Transfer-Encoding', 'binary');
		$app->response->header("Content-Description", "File Transfer");
		$app->response->header('Content-Length', filesize($GLOBALS['SISED_PATH']."server-side/import_export/".$rootDir.".zip"));
		$app->response->setBody($fData);		
	}	
});


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE : GET /revisions_list/:user_login
// ─────────────────────────────────────────────────────────────────────────────
// Retourne la liste des révisions actives et validées (mises à jour de référentiel).
// Les révisions permettent de distribuer des corrections ou des mises à jour
// de données de référence (listes d'établissements, nomenclatures, etc.).
// ═════════════════════════════════════════════════════════════════════════════
$app->get('/revisions_list/:user_login', function ($user_login) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	
	// Vérification utilisateur
	$sql = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$user_login'";
	$user_id = $GLOBALS['conn_dico']->GetOne($sql);   
	
	if (!$user_id || $user_id == '') {
		sendError("User not found!"); 
		return;
	}
	// Liste des révisions activées et validées (triées par ID décroissant = les plus récentes d'abord)
	$requete = "SELECT ID_REVISION as id, NUM_REVISION as num, COMMENT_REVISION as comm ".
				"FROM REVISION ".
				"WHERE ACTIVER_REVISION=1 AND VALIDER_REVISION=1 ORDER BY ID_REVISION DESC";
	$revs_array = $GLOBALS['conn_dico']->GetAll($requete); 
	$revs_array = array_change_key_case_recursive($revs_array, CASE_LOWER);
	sendList($revs_array);
});


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE : GET /revision_files/:user_login/:id_rev
// ─────────────────────────────────────────────────────────────────────────────
// Retourne la liste des fichiers contenus dans une révision donnée.
// Utilisé par l'app mobile pour savoir quels fichiers télécharger.
// ═════════════════════════════════════════════════════════════════════════════
$app->get('/revision_files/:user_login/:id_rev', function ($user_login, $id_rev) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	// NOTE : $revId et $revType sont lus depuis POST mais non utilisés ici
	$revId = $_POST['REV_ID'];
	$revType = $_POST['TYPE_REV_FILE'];
	$requete = "SELECT NOM_REV_FILE as nom, PATH_REV_FILE as path, CODE_TYPE_REVISION as type FROM REVISION_FILES WHERE ID_REVISION=".$id_rev;
	$liste_files = $GLOBALS['conn_dico']->GetAll($requete); 
	$liste_files = array_change_key_case_recursive($liste_files, CASE_LOWER);
	sendList($liste_files);
});


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE : GET /revision_zip/:user_login/:rev_num
// ─────────────────────────────────────────────────────────────────────────────
// Téléchargement du fichier ZIP d'une révision spécifique.
// Le ZIP est identifié par son numéro de révision.
// ═════════════════════════════════════════════════════════════════════════════
$app->get('/revision_zip/:user_login/:rev_num', function ($user_login, $rev_num) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $app) {
	$filePath = $GLOBALS['SISED_PATH']."server-side/import_export/".$rev_num.".zip";
	echo $filePath;
	if (file_exists($filePath)) {
		$fData = file_get_contents($filePath);
		// En-têtes HTTP pour téléchargement de fichier binaire
		$app->response->header('Content-Type', 'application/octet-stream');
		$app->response->header('Pragma', "public");
		$app->response->header('Content-disposition:', 'attachment; filename='.$rev_num.'.zip');
		$app->response->header('Content-Transfer-Encoding', 'binary');
		$app->response->header("Content-Description", "File Transfer");
		$app->response->header('Content-Length', filesize($filePath));
		$app->response->setBody($fData);	
	} else {
		sendError("file_ko"); // Fichier ZIP introuvable
	}
});

// Lancement du routeur Slim
$app->run();


// ═════════════════════════════════════════════════════════════════════════════
// FONCTIONS UTILITAIRES
// ═════════════════════════════════════════════════════════════════════════════

/**
 * str_starts_with — Vérifie si une chaîne commence par un préfixe donné.
 * Polyfill PHP 7.x pour la fonction native PHP 8.0.
 *
 * @param  string $haystack Chaîne à tester
 * @param  string $needle   Préfixe recherché
 * @return bool
 */
function str_starts_with($haystack, $needle)
{
    return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
}

/**
 * str_ends_with — Vérifie si une chaîne se termine par un suffixe donné.
 * Polyfill PHP 7.x pour la fonction native PHP 8.0.
 *
 * @param  string $haystack Chaîne à tester
 * @param  string $needle   Suffixe recherché
 * @return bool
 */
function str_ends_with($haystack, $needle)
{
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}

/**
 * sendList — Envoi d'une réponse JSON standard avec liste de données.
 *
 * Format : {"se_statut":200,"se_message":"ok","se_data":[...]}
 *
 * @param array $liste Tableau des données à retourner
 */
function sendList($liste) {
	$posts = array('se_statut'=>200,'se_message'=>'ok','se_data'=>$liste);	
	echo json_encode($posts);
}

/**
 * sendError — Envoi d'une réponse JSON d'erreur.
 *
 * Format : {"se_statut":101,"se_message":"...","se_data":null}
 *
 * @param string $message Message d'erreur
 */
function sendError($message) {
	$posts = array('se_statut'=>101,'se_message'=>$message,'se_data'=>NULL);	
	echo json_encode($posts);
}
?>
