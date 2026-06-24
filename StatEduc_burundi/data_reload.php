<?php

/**
 * data_reload.php
 *
 * Web Service REST - Rechargement et pre-remplissage des donnees pour l'app mobile.
 * Route : GET /data_reload/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
 * Renvoie les valeurs deja saisies afin de pre-remplir le formulaire a la reprise.
 * Corrige l'encodage ISO-8859-1 vers UTF-8 avant serialisation JSON.
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 * @sessions 4-19
 * @modifie Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
 *          Toutes les modifications et nouveautes sont documentees
 *          directement dans le code avec des commentaires en francais.
 */
require_once 'common_ws.php';

require_once $GLOBALS['SISED_PATH_LIB'] . 'adodb_xml/class.ADODB_XML.php';
require $GLOBALS['SISED_PATH_LIB'] . 'Curl/Curl.php';

use \Curl\Curl;
$curl = new Curl();
$curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
$curl->setHeader('Accept', '*/*');
$curl->setHeader('Accept-Encoding', 'gzip,deflate');
$curl->setHeader('Accept-Language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');

$lib_status =  $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data = $GLOBALS['PARAM_WS']['LIB_DATA'];

$status_ok = $GLOBALS['PARAM_WS']['STATUS_OK'];
$status_ko = $GLOBALS['PARAM_WS']['STATUS_KO'];

$app = new \Slim\Slim();

//$app->add(new \HttpAuth());

 // renvoie les thèmes pour une campagne
/*$app->get('/theme_data/:user/:id_sector/:id_teme/:id_camp/:id_etab/:id_filter', function ($user, $id_sector, $id_teme, $id_camp, $id_etab, $id_filter) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl) {
	$msg_ko = $GLOBALS['PARAM_WS']['KO'];
  
	$id_year = $_SESSION['annee'];
	$requete = "SELECT DISTINCT ID_CAMPAGNE
				FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU
				WHERE AU.NOM_USER LIKE '".$user."' 
			AND DFR.ID_USER=AU.CODE_USER 
				AND ID_ANNEE=".$id_year."   
				AND ID_PERIODE=".$id_filter." 
			AND ID_CAMPAGNE=".$id_camp.";";
	$camps = $GLOBALS['conn_dico']->GetAll($requete); 
  	if (count($camps) == 0 || $camps[0] == '') {
		$rps = array($lib_status=>$status_ko, $lib_message=>$msg_ko, $lib_data=>"The user '".$user."' can't access this Survey");
		echo json_encode($rps);
		return;
	}
  
	$curl->success(function($instance) {
		
		$dom = new DOMDocument;
		$dom->loadHTML($instance->response);
		$forms = $dom->getElementsByTagName('form');
		foreach ($forms as $form) {
			if (strpos($form->getAttribute('action'), 'questionnaire.php?theme_frame=') !== FALSE) {
				//$rps = array($GLOBALS['PARAM_WS']['LIB_STATUS']=>$GLOBALS['PARAM_WS']['STATUS_OK'],$GLOBALS['PARAM_WS']['LIB_MESSAGE']=>$GLOBALS['PARAM_WS']['OK'],$GLOBALS['PARAM_WS']['LIB_DATA']=>$dom->saveXML($form));
				//echo json_encode($rps);
				echo $dom->saveXML($form);
				break;
			}
		}
		//echo $instance->response;
	});
	$curl->error(function($instance) {
		$rps = array($GLOBALS['PARAM_WS']['LIB_STATUS']=>$GLOBALS['PARAM_WS']['STATUS_OK'],$GLOBALS['PARAM_WS']['LIB_MESSAGE']=>$GLOBALS['PARAM_WS']['KO'],$GLOBALS['PARAM_WS']['LIB_DATA']=>$instance->error_code." : ".$instance->error_message);		
		echo json_encode($rps);
	});
	$curl->complete(function($instance) {
		//echo 'call completed' . "<br/>";
	});
	
	$urlBase = $GLOBALS['SISED_AURL'].'questionnaire_ws.php?sector='.$id_sector.'&theme='.$id_teme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp;
	if ($id_filter != null) {
		$urlBase .= '&filtre='.$id_filter;
	}
	//echo $urlBase;
	$curl->get($urlBase);	
	
});*/

 // renvoie les thèmes pour une campagne
$app->get('/theme_data/:user/:id_sector/:id_theme/:id_camp/:id_etab/:id_filter', function ($user, $id_sector, $id_theme, $id_camp, $id_etab, $id_filter) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl) {
	$msg_ko = $GLOBALS['PARAM_WS']['KO'];
	
	$id_year = $_SESSION['annee'];
	
	$period_query = "";
	if ($id_filter != "null") {
		$period_query = " AND ID_PERIODE=".$id_filter." "; 
	}
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
		
	$curl->success(function($instance) use ($id_camp) {
		echo $instance->response;
	});
	$curl->error(function($instance) {
		$rps = array($GLOBALS['PARAM_WS']['LIB_STATUS']=>$GLOBALS['PARAM_WS']['STATUS_OK'],$GLOBALS['PARAM_WS']['LIB_MESSAGE']=>$GLOBALS['PARAM_WS']['KO'],$GLOBALS['PARAM_WS']['LIB_DATA']=>$instance->error_code." : ".$instance->error_message);		
		echo json_encode($rps);
	});
	$curl->complete(function($instance) {
		//echo 'call completed' . "<br/>";
	});
	
	// Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
	// Session 24 : ajout login et langue dans l'URL pour le bootstrap session mobile
	// questionnaire_reload_ws.php a besoin de ces params quand il n'y a pas de session navigateur
	$lang_reload = isset($_SESSION['langue']) && $_SESSION['langue']<>'' ? $_SESSION['langue'] : 'fr';
	$urlBase = $GLOBALS['SISED_AURL'].'questionnaire_reload_ws.php?sector='.$id_sector.'&theme='.$id_theme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp.'&code_annee='.$id_year.'&login='.$user.'&langue='.$lang_reload;
	if ($id_filter != null) {
		if (strpos($id_filter, ':') !== false) {
			$ids = explode(':', $id_filter);
			$urlBase .= '&filtre='.preg_replace('/_/','/',$ids[0]).'&filtre_num_form='.$ids[1];
		} else {
			$urlBase .= '&filtre='.$id_filter;
		}
	}
	//echo $urlBase;
	$curl->get($urlBase);	
	
});

// Recupération de la liste des filtres
$app->post('/school_data/:user/:id_sector/:id_camp/:id_year/:id_period', function ($user, $id_sector, $id_camp, $id_year, $id_period) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	$msg_ko = $GLOBALS['PARAM_WS']['KO'];
  
 	$sql = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$user'";
	$user_id = $GLOBALS['conn_dico']->GetOne($sql);   
	
	if (!$user_id || $user_id == '') {
		sendError("User not found!"); 
		return;
	}
	
  	$data_to_send = $app->request->post();
	
	$arrSchools = $data_to_send['ID_SCHOOLS'];
	
	$idSchools = explode(',', $arrSchools);
	
	$lang = $_SESSION['langue'];
	
	// liste des thèmes
	$lstThemes = get_camp_themes($id_sector, $id_camp, $lang);
	$lstThemes = array_change_key_case_recursive($lstThemes, CASE_LOWER);
	
	$tabsAndFields = array();
	foreach($lstThemes as $i => $theme){
		$lstTabs = get_tables_meres_theme($theme['id_theme']);
		foreach($lstTabs as $i => $tabLine){
			$tab = $tabLine['NOM_TABLE_MERE'];
			if ($tabsAndFields[$tab] == NULL) {
				$tabsAndFields[$tab] = array();
			}
			$zones = get_tab_mere_zones($theme['id_theme'], $tab);//print_r($zones);
			if (is_array($zones) && count($zones) > 0) {
				foreach($zones as $i => $zone){
					if (!in_array($zone['CHAMP_PERE'], $tabsAndFields[$tab])) {
						$tabsAndFields[$tab][] = $zone['CHAMP_PERE'];
					}
				}
			}
		}
	}
	$tabsAndQueries = generateExportQueries($tabsAndFields);
	
	$rootDir = time();
	$exportDir = $GLOBALS['SISED_PATH']."server-side/import_export/".$rootDir;
	if (!file_exists($exportDir)) {
		mkdir($exportDir, 0777, true);
	}
	foreach($idSchools as $i => $idSchool) {
		if (!file_exists($exportDir."/".$idSchool)) {
			mkdir($exportDir."/".$idSchool, 0777, true);
		}
		foreach($tabsAndQueries as $tablename => $query) {
			$req = str_replace('_code_school_', $idSchool, $query); 
			$req = str_replace('_code_year_', $id_year, $req); 
			$req = str_replace('_code_camp_', $idSchool, $req); 
			$req = str_replace('_code_filtre_', $id_period, $req);
			$adodbXML = new ADODB_XML("1.0", "ISO-8859-1");
			$adodbXML->ConvertToXML2($GLOBALS['conn'], $req, $tabsAndFields[$tablename], $tablename, $exportDir."/".$idSchool."/".$tablename.".xml");		
		}
	}
	
	if (create_zip($GLOBALS['SISED_PATH']."server-side/import_export", $rootDir)) {
		$fData = file_get_contents($GLOBALS['SISED_PATH']."server-side/import_export/".$rootDir.".zip");
		$app->response->header('Content-Type', 'application/octet-stream');
		$app->response->header('Pragma', "public");
		$app->response->header('Content-disposition:', 'attachment; filename='. $rootDir.".zip");
		$app->response->header('Content-Transfer-Encoding', 'binary');
		$app->response->header("Content-Description", "File Transfer");
		$app->response->header('Content-Length', filesize($GLOBALS['SISED_PATH']."server-side/import_export/".$rootDir.".zip"));
		$app->response->setBody($fData);		
	}	
	
});

$app->get('/revisions_list/:user_login', function ($user_login) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	
	$sql = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$user_login'";
	$user_id = $GLOBALS['conn_dico']->GetOne($sql);   
	
	if (!$user_id || $user_id == '') {
		sendError("User not found!"); 
		return;
	}
	$requete = "SELECT ID_REVISION as id, NUM_REVISION as num, COMMENT_REVISION as comm ".
				"FROM REVISION ".
				"WHERE ACTIVER_REVISION=1 AND VALIDER_REVISION=1 ORDER BY ID_REVISION DESC";
	$revs_array = $GLOBALS['conn_dico']->GetAll($requete); 
	$revs_array =  array_change_key_case_recursive($revs_array, CASE_LOWER);
	sendList($revs_array);
});

$app->get('/revision_files/:user_login/:id_rev', function ($user_login, $id_rev) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$revId = $_POST['REV_ID'];
	$revType = $_POST['TYPE_REV_FILE'];
	$requete = "SELECT NOM_REV_FILE as nom, PATH_REV_FILE as path, CODE_TYPE_REVISION as type FROM REVISION_FILES WHERE ID_REVISION=".$id_rev;
	$liste_files = $GLOBALS['conn_dico']->GetAll($requete); 
	$liste_files =  array_change_key_case_recursive($liste_files, CASE_LOWER);
	
	sendList($liste_files);
});

$app->get('/revision_zip/:user_login/:rev_num', function ($user_login, $rev_num) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $app) {
	$filePath = $GLOBALS['SISED_PATH']."server-side/import_export/".$rev_num.".zip";
	echo $filePath;
	if (file_exists($filePath)) {
		$fData = file_get_contents($filePath);
		$app->response->header('Content-Type', 'application/octet-stream');
		$app->response->header('Pragma', "public");
		$app->response->header('Content-disposition:', 'attachment; filename='.$rev_num.'.zip');
		$app->response->header('Content-Transfer-Encoding', 'binary');
		$app->response->header("Content-Description", "File Transfer");
		$app->response->header('Content-Length', filesize($filePath));
		$app->response->setBody($fData);	
	} else {
		sendError("file_ko");
	}
});

$app->run();
 
function str_starts_with($haystack, $needle)
{
    return substr_compare($haystack, $needle, 0, strlen($needle)) 
           === 0;
}
function str_ends_with($haystack, $needle)
{
    return substr_compare($haystack, $needle, -strlen($needle)) 
           === 0;
}

function sendList($liste) {
	$posts = array('se_statut'=>200,'se_message'=>'ok','se_data'=>$liste);	
	echo json_encode($posts);
}

function sendError($message) {
	$posts = array('se_statut'=>101,'se_message'=>$message,'se_data'=>NULL);	
	echo json_encode($posts);
}
?>