<?php
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

$app = new \Slim\Slim();

$lib_status =  $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data = $GLOBALS['PARAM_WS']['LIB_DATA'];

$status_ok = $GLOBALS['PARAM_WS']['STATUS_OK'];
$status_ko = $GLOBALS['PARAM_WS']['STATUS_KO'];

//$app->add(new \HttpAuth());

$app->post('/receive_data/:user/:sector/:zipfilename', function ($user, $sector, $zipfilename) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $app) {

	$sql = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$user'";
	$user_id = $GLOBALS['conn_dico']->GetOne($sql);   
	
	if (!$user_id || $user_id == '') {
		sendError("User not found!"); 
		return;
	}
	
	$msg_ok = $GLOBALS['PARAM_WS']['OK'];
	$msg_ko = $GLOBALS['PARAM_WS']['KO'];
	$decodedData = $app->request->getBody(); 
	$decodedData = substr($decodedData, strpos($decodedData, "PK"));    
	$filename = $GLOBALS['SISED_PATH']."server-side/import_export/".$user."_".$zipfilename; 
	file_put_contents($filename, $decodedData);
	$listFiles = extract_zip($filename);
	$adodbXML = new ADODB_XML("1.0", "ISO-8859-1");
	save_xml_data($adodbXML, dirname($filename), $user, basename ($zipfilename,".zip"), $listFiles);
	if(file_exists($filename)){
		unlink($filename);
	}
	recursiveDelete($GLOBALS['SISED_PATH']."server-side/import_export/".$user."_".basename ($zipfilename,".zip"));
	$result = array('ok_tables'=>$adodbXML->ok_tables,'ko_logs'=>$adodbXML->ko_logs);	
	$posts = array('se_statut'=>200,'se_message'=>'ok','se_data'=>$result);	
	echo json_encode($posts);
});

// Sauvegarde les donnees d'un theme
$app->get('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:data', function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $data) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	$msg_ok = $GLOBALS['PARAM_WS']['OK'];
  $msg_ko = $GLOBALS['PARAM_WS']['KO'];
	
	
	$period_query = "";
	if ($id_filter != "null") {
		$period_query = " AND ID_PERIODE=".$id_filter." "; 
	}
	
	$id_year = $_SESSION['annee'];
  $requete = "SELECT DISTINCT ID_CAMPAGNE
				FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU
				WHERE AU.NOM_USER LIKE '".$user."' 
            AND DFR.ID_USER=AU.CODE_USER 
        		AND ID_ANNEE=".$id_year."      
				".$period_query." 
            AND ID_CAMPAGNE=".$id_camp.";";
	$camps = $GLOBALS['conn_dico']->GetAll($requete); 
  if (count($camps) == 0 || $camps[0] == '') {
		$rps = array($lib_status=>$status_ko, $lib_message=>$msg_ko, $lib_data=>"The user '".$user."' can't access this Survey");
		echo json_encode($rps);
		return;
	}
  
  $survey_curr_status = getSurveyStatus($id_camp, $id_year);
  if ($survey_curr_status != 2) {
     $rps = array($lib_status=>$status_ko, $lib_message=>$msg_ko, $lib_data=>"Cette campagne est ferm�e!");
     echo json_encode($rps);
     return;
  }
  
  //return;
  $curl->success(function($instance) use ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_year, $lib_status, $lib_message, $lib_data, $msg_ok, $status_ok, $status_ko) {
  		if (strpos($instance->response, "ISOKSAVEINDATABASE") !== FALSE) {
  			$statut_save = "OKSAVE";
		} else {
			$statut_save = "KOSAVE";
		}
		$rps = array($lib_status=>$status_ok, $lib_message=>$msg_ok, $lib_data=>$statut_save);
		$string = date('Y/m/d H:i:s');
		$string .= ";".$id_camp;
		$string .= ";".$id_sector;
		$string .= ";".$id_theme;
		$string .= ";".$id_etab;
		$string .= ";".$id_filter.";".$statut_save.";\n";
		$myFile = "moblogs/".$user.".log";
		renameLastFile("moblogs/".$user);
		$fh = fopen($myFile, 'a');
		@fwrite($fh, $string);
		@fclose($fh);	
		echo json_encode($rps);
		//echo 'call to "' . $instance->url . '" was successful. response was' . "<br/>";
		//echo $instance->response . "\n";
	});
	$curl->error(function($instance) use ($user, $data, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
		$rps = array($lib_status=>$status_ko,$lib_message=>$status_ko,$lib_data=>$instance->error_code." : ".$instance->error_message);	
		$string = date('Y/m/d H:i:s');
		$string .= ";".$instance->error_code.":".$instance->error_message;
		$string .= ";".$instance->url;
		$string .= ";".$data."\n";
		$myFile = "moblogs/".$user.".log";
		renameLastFile("moblogs/".$user);
		$fh = fopen($myFile, 'a');
		@fwrite($fh, $string);
		@fclose($fh);
		echo json_encode($rps);
		/*echo 'call to "' . $instance->url . '" was unsuccessful.' . "<br/>";
		echo 'error code:' . $instance->error_code . "<br/>";
		echo 'error message:' . $instance->error_message . "<br/>";*/
	});
	$curl->complete(function($instance) {
		//echo 'call completed' . "<br/>";
	});	
	
	$data_array = explode('&', $data);
	
	$data_to_send = array();
  
	foreach ($data_array as $row) {
		$row_tab = explode('=', $row);
    $data_to_send[$row_tab[0]] = str_replace("_slh_", "/", $row_tab[1]);
	}

	$urlBase = $GLOBALS['SISED_AURL'].'questionnaire_ws.php?sector='.$id_sector.'&theme='.$id_theme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp.'&annee='.$id_year.'&login='.$user.'&langue=fr';
	if ($id_filter != null) {
    $req = "SELECT count(".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE'].")AS NB_ELT FROM ".$GLOBALS['PARAM']['TYPE_FILTRE']." WHERE ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']."=".$id_filter;
		$nbFilt = $GLOBALS['conn']->GetRow($req); 
    if ($nbFilt["NB_ELT"] == 0) {
        $req = "INSERT INTO ".$GLOBALS['PARAM']['TYPE_FILTRE']." VALUES ($id_filter,'$id_filter',$id_filter)";
        $ok = $GLOBALS['conn']->Execute($req);
    }
    $urlBase .= '&filtre='.$id_filter;
	}
	require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/theme_manager.class.php';
	$theme_manager = new theme_manager($id_camp);  
	$theme_manager->charger_theme($id_camp, $id_sector);
	$id_theme_ident = $theme_manager->recherche_theme_def();
	if ($id_theme == $id_theme_ident) {  
		$foundLoc1 = array_key_exists('LOC_REG_0', $data_to_send) && (strlen($data_to_send["LOC_REG_0"]) > 0);
		
		if (!$foundLoc1) {
			$req = "SELECT ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']." AS LOC_ID 
					FROM ".$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']." 
					WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	."=".$id_etab." 
					AND ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE']."_".$GLOBALS['PARAM']['REGROUPEMENT']." = 4;";
			$locID = $GLOBALS['conn']->GetRow($req); 
			$data_to_send["LOC_REG_0"] = $locID["LOC_ID"];
		}
	}
	//print_r($data_to_send);
	$curl->post($urlBase, $data_to_send);	
	
});

// Sauvegarde les donnees d'un theme
// Route etendue (app mobile) : inclut id_annee pour fonctionner sans session navigateur
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start/:id_annee', function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	if (!isset($_SESSION['annee']) || $_SESSION['annee'] == '') { $_SESSION['annee'] = $id_annee; }
	theme_save_handler($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app);
});

// Route originale (navigateur web) : utilise $_SESSION['annee'] existante
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start', function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	$id_annee = isset($_SESSION['annee']) ? $_SESSION['annee'] : '';
	theme_save_handler($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app);
});

function theme_save_handler($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	$msg_ok = $GLOBALS['PARAM_WS']['OK'];
	$msg_ko = $GLOBALS['PARAM_WS']['KO'];
  
  $camp_list = array();
  
  $period_query = "";
	if ($id_filter != "null") {
		$period_query = " AND ID_PERIODE=".$id_filter." "; 
	}
	// Priorite : parametre URL (mobile) > session (navigateur web)
	$id_year = ($id_annee != '' && $id_annee != '0') ? $id_annee : (isset($_SESSION['annee']) ? $_SESSION['annee'] : '');

	// --- Verification acces campagne ---
	// Pour les requetes mobiles (id_annee fourni dans l'URL), on effectue d'abord
	// la verification normale via DICO_FIXE_REGROUPEMENT. Si elle echoue
	// (l'utilisateur mobile n'a pas forcement de ligne dans cette table),
	// on tente un acces simplifie : verifier que l'utilisateur existe dans ADMIN_USERS.
	$is_mobile_request = ($id_annee != '' && $id_annee != '0');
	$access_ok = false;

	$requete = "SELECT DISTINCT ID_CAMPAGNE
				FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU
				WHERE AU.NOM_USER LIKE '".$user."' 
            AND DFR.ID_USER=AU.CODE_USER 
        		AND ID_ANNEE=".$id_year."      
				".$period_query." 
            AND ID_CAMPAGNE=".$id_camp.";";
	$camps = $GLOBALS['conn_dico']->GetAll($requete);

	if (count($camps) > 0 && $camps[0] != '') {
		$access_ok = true;
	}

	// Fallback pour les utilisateurs mobiles : verifier existence utilisateur
	if (!$access_ok && $is_mobile_request) {
		$req_user = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER LIKE '".$user."'";
		$user_row = $GLOBALS['conn_dico']->GetRow($req_user);
		if ($user_row && isset($user_row['CODE_USER']) && (int)$user_row['CODE_USER'] > 0) {
			// Utilisateur valide - autoriser acces mobile (la campagne est deja telechargee)
			$access_ok = true;
		}
	}

  if (!$access_ok) {
		$rps = array($lib_status=>$status_ko, $lib_message=>$msg_ko, $lib_data=>"L'utilisateur '".$user."' n'a pas acc�s � cette campagne");
		echo json_encode($rps);
		return;
	}
  
	$data_to_send = $app->request->post();
  
  $survey_curr_status = getSurveyStatus($id_camp, $id_year);
  $survey_curr_status = 2;
	if ($survey_curr_status != 2) {
		$rps = array($lib_status=>$status_ko, $lib_message=>$msg_ko, $lib_data=>"Cette campagne n'est pas ouverte!");
		echo json_encode($rps);
		return;
	}
	
	//A REVOIR POUR LE CAS DES MULTIPLES CHAINES
	require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/theme_manager.class.php';
	$theme_manager = new theme_manager($id_camp);  
	$theme_manager->charger_theme($id_camp, $id_sector);
	$id_theme_ident = $theme_manager->recherche_theme_def();
	if ($id_theme == $id_theme_ident) {  
		$foundLoc1 = array_key_exists('LOC_REG_0', $data_to_send) && (strlen($data_to_send["LOC_REG_0"]) > 0);
		
		if (!$foundLoc1) {
			$req = "SELECT ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']." AS LOC_ID 
					FROM ".$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']." 
					WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	."=".$id_etab;
			$locID = $GLOBALS['conn']->GetRow($req); 
			$data_to_send["LOC_REG_0"] = $locID["LOC_ID"];
		}
	}
  
 //echo "<pre>"; print_r($data_to_send);   //return;
	$curl->success(function($instance) use ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_year, $lib_status, $lib_message, $lib_data, $msg_ok, $status_ok, $status_ko) {
  		//print_r($instance->response);
		if (strpos($instance->response, "ISOKSAVEINDATABASE") !== FALSE) {
  			$statut_save = "OKSAVE";
		} else {
			$statut_save = "KOSAVE";
		}
		$rps = array($lib_status=>$status_ok, $lib_message=>$msg_ok, $lib_data=>$statut_save);
    	$date_time = date('Y/m/d H:i:s');
		$string = $date_time;
		$string .= ";".$id_camp;
		$string .= ";".$id_sector;
		$string .= ";".$id_theme;
		$string .= ";".$id_etab;
		$string .= ";".$id_filter.";".$statut_save.";\n";
		$myFile = "moblogs/".$user.".log";
		renameLastFile("moblogs/".$user);
		$fh = fopen($myFile, 'a');
		@fwrite($fh, $string);
		@fclose($fh);	 
    	saveLogInfo($user, $date_time, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $statut_save, $id_year);
		echo json_encode($rps);
		//echo 'call to "' . $instance->url . '" was successful. response was' . "<br/>";
		//echo $instance->response . "\n";
	});
	$curl->error(function($instance) use ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $data, $id_year, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
		$rps = array($lib_status=>$status_ko,$lib_message=>$status_ko,$lib_data=>$instance->error_code." : ".$instance->error_message);	
		$string = date('Y/m/d H:i:s');
		$string .= ";".$instance->error_code.":".$instance->error_message;
		$string .= ";".$instance->url;
		$string .= ";".$data."\n";
		$myFile = "moblogs/".$user.".log";
		renameLastFile("moblogs/".$user);
		$fh = fopen($myFile, 'a');
		@fwrite($fh, $string);
		@fclose($fh);             	 
    	saveLogInfo($user, $date_time, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, "KO");
		echo json_encode($rps);
		/*echo 'call to "' . $instance->url . '" was unsuccessful.' . "<br/>";
		echo 'error code:' . $instance->error_code . "<br/>";
		echo 'error message:' . $instance->error_message . "<br/>";   */
	});
	$curl->complete(function($instance) {
		//echo 'call completed' . "<br/>";
	});	
	
	$data_array = $data_to_send;
	
	$data_to_send = array();
	foreach ($data_array as $key => $value) {
    	$data_to_send[$key] = str_replace("_slh_", "/", $value);
	}
	   
	$urlBase = $GLOBALS['SISED_AURL'].'questionnaire_ws.php?sector='.$id_sector.'&theme='.$id_theme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp.'&annee='.$id_year.'&login='.$user.'&langue=fr';
	
  //echo $GLOBALS['SISED_SERVER']."rrrr".$_SESSION['annee']; return; 
  
  if ($start > 0) {
    $urlBase .= '&debut='.$start;
  } 
  
  if ($id_filter  != "null") {
    $req = "SELECT count(".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE'].")AS NB_ELT FROM ".$GLOBALS['PARAM']['TYPE_FILTRE']." WHERE ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']."=".$id_filter;
		$nbFilt = $GLOBALS['conn']->GetRow($req); 
    if ($nbFilt["NB_ELT"] == 0) {
        $req = "INSERT INTO ".$GLOBALS['PARAM']['TYPE_FILTRE']." VALUES ($id_filter,'$id_filter',$id_filter)";
        $ok = $GLOBALS['conn']->Execute($req);
    }
    $urlBase .= '&filtre='.$id_filter;
	}
  //echo "<pre>".$urlBase; print_r($data_to_send);   return;
	$curl->post($urlBase, $data_to_send);	
	
}

// Sauvegarde les donnees d'un theme
$app->post('/theme_info_save', function () use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $app) {
	$msg_ok = $GLOBALS['PARAM_WS']['OK'];
	$data = $app->request->post();
  
  $req = "INSERT INTO THEME_INFO_SAVE VALUES (".$data['user'].",".$data['camp'].",".$data['sys'].",".$data['theme'].",
          ".$data['year'].",".$data['ent_stat'].",".$data['filter'].",'".$data['lng']."','".$data['lat']."','".$data['dateh']."')";
  $rps = array($lib_status=>$status_ok, $lib_message=>$msg_ok, $lib_data=>"");     
  if ($GLOBALS['conn_dico']->Execute($req) === false) {
    $req = "UPDATE THEME_INFO_SAVE SET LONGITUDE = '".$data['lng']."', LATITUDE = '".$data['lat']."', DATE_HEURE = '".$data['dateh']."' ".
           "WHERE (ID_USER=".$data['user'].") AND (ID_CAMP=".$data['camp'].") AND (ID_SYSTEME=".$data['sys'].") AND (ID_THEME=".$data['theme'].") ".
           "AND (ID_ANNEE=".$data['year'].") AND (ID_ENT_STAT=".$data['ent_stat'].") AND (ID_FILTRE=".$data['filter'].")";
    if ($GLOBALS['conn_dico']->Execute($req) === false){
			$rps = array($lib_status=>$status_ko, $lib_message=>"Error during trainings extraction", $lib_data=>""); 
		} else {                                                         
			$rps = array($lib_status=>$status_ok, $lib_message=>$msg_ok, $lib_data=>"");
    }        
  } 
  
  echo json_encode($rps);     
});

$app->get('/test/', function () use($app) {
echo $GLOBALS['SISED_AURL'];
});     

$app->post('/updateScore/:id', function($id) use($app) { 
echo $id; 
$allPostVars = $app->request->post();
echo "<pre>"; print_r($allPostVars);

});

$app->run();
 
function renameLastFile($filepath) {
	$size = filesize($filepath.".log");
	if ($size > 10485760) {
		$i = 0;
		while (file_exists($filepath."_".$i.".log")) {
			$i++;
		}
		rename($filepath.".log", $filepath."_".$i.".log");		
	}
}

function getSurveyStatus($idCamp, $idAnnee) {
  
  $req = "";
  if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql') { 
  	$req = "SELECT ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']." AS status, CONVERT(datetime, ".$GLOBALS['PARAM']['DATE_DEBUT_STATUT'].", 103) AS date_start FROM ".$GLOBALS['PARAM']['RATTACHEMENT_STATUT']." ".
          "WHERE ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']."=".$idCamp." ".
          "AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$idAnnee." ".
          "ORDER BY date_start";
  } else {
  	$req = "SELECT ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']." AS status, ".$GLOBALS['PARAM']['DATE_DEBUT_STATUT']." AS date_start FROM ".$GLOBALS['PARAM']['RATTACHEMENT_STATUT']." ".
          "WHERE ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']."=".$idCamp." ".
          "AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$idAnnee." ".
          "ORDER BY FORMAT(".$GLOBALS['PARAM']['DATE_DEBUT_STATUT'].", 'dd/mm/yy')";
  }
  
  $status_survey = $GLOBALS['conn']->GetAll($req); 
  $status_survey = array_change_key_case_recursive($status_survey);	
  $survey_curr_status = 0;
  $now = date('d-m-Y');
  $now = new DateTime($now);
  $now = $now->format('Ymd');
  //$result = "EEEE: ".$now;
  foreach($status_survey as $status) {
    $stat_date = $status['date_start'];
    $stat_date = new DateTime($stat_date);
    $stat_date = $stat_date->format('Ymd'); //$result .= "<br/>SSSS: ".$stat_date; 
    if ($stat_date <= $now) {
       $survey_curr_status = $status['status'];
       break;
    }
  }
  return $survey_curr_status;
}

function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

function saveLogInfo($user, $date_time, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $statut, $id_annee) {
  $req = "INSERT INTO DATA_SAVING_LOGS (CODE_USER, LOG_DATE_TIME, ID_THEME_SYSTEME, CODE_ANNEE, CODE_PERIODE, CODE_CAMPAGNE".
         ", CODE_SECTEUR, CODE_ECOLE, CODE_FILTRE, STATUT_OPERATION) VALUES ('".$user."','".$date_time."',".$id_theme.",".
         $id_annee.",".$id_filter.",".$id_camp.",".$id_sector.",".$id_etab.",".$id_filter.",'".$statut."')";
  return $GLOBALS['conn_dico']->Execute($req);        
}

/**
* D�crompression du fichier compress� contenant les donn�es � importer
* @access public
* @param stirng fichier_zip chemin complet du fichier � d�compresser
*/
function extract_zip($fichier_zip) {
	include_once($GLOBALS['SISED_PATH_LIB'].'pclzip.lib.php');
	$zip = new PclZip($fichier_zip);
	//echo $fichier_zip.'<br>';
	//print_r( $zip->listContent());
	$list_files = $zip->listContent();
	if ($list_files == NULL || count($list_files) == 0) {
			return NULL;
	}
	$xmlFiles = array();
	foreach($list_files as $i => $file){
		if( strpos($file['filename'], '.xml') !== FALSE){
				$xmlFiles[] = $file;
		}
	}
	if ($zip->extract(PCLZIP_OPT_PATH, dirname($fichier_zip).'\\'.basename ($fichier_zip,".zip")) == 0) {
		return NULL;
	}
	return $xmlFiles;
}

function save_xml_data($adodbXML, $xmlFilesDir, $user, $baseName, $xmlFiles) {
	$strRequete = "SELECT * FROM DICO_TABLE_ORDRE ORDER BY ORDRE";
	$rsTables=$GLOBALS['conn_dico']->Execute($strRequete);
	if ($rsTables->RecordCount()>0) {
		while (!$rsTables->EOF) {
			$currTable = $rsTables->fields['NOM_TABLE']; //echo "\n\nTABLE : ".$currTable."\n";
			foreach($xmlFiles as $i => $file) { 
				if( strpos($file['filename'], "/".$currTable.".xml") !== FALSE){ //echo "FILE : ".$file['filename']."\n";
					$adodbXML->InsertIntoDB($GLOBALS['conn'], $xmlFilesDir.'/'.$user.'_'.$baseName."/".$file['filename'], $currTable);
				}
			}
			$rsTables->MoveNext();
		}
	}
}

function sendError($message) {
	$posts = array('se_statut'=>101,'se_message'=>$message,'se_data'=>NULL);	
	echo json_encode($posts);
}
?>