<?php

/**
 * data_save.php
 *
 * Web Service REST - Persistance des donnees collectees par l'app mobile.
 * Route : POST /data_save
 * Enregistre les reponses du formulaire dans la base de donnees Access.
 * Gere les validations, les doublons et les erreurs de contrainte.
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 * @sessions 4-17
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
// Timeout pour l'appel interne vers questionnaire_ws.php
// Sans timeout, le curl attend indefiniment si Apache est sature (self-curl deadlock)
$curl->setOpt(CURLOPT_CONNECTTIMEOUT, 15); // echec rapide si connexion impossible
// AK-FIX-TIMEOUT: 120s trop court sur serveurs avec memory_limit bas ou charge eleve.
// questionnaire_ws.php charge HTML+arbre+ADODB -> peut depasser 120s -> erreur 28.
// 300s = 5min = marge large pour les formulaires les plus lourds.
$curl->setOpt(CURLOPT_TIMEOUT, 300);        // max 300s (AK-FIX-TIMEOUT)
// Session 46 : CURLOPT_SSL_VERIFYPEER=false supprime (faille securite).
// Correction definitive SSL-51 : config_app.php force $_sised_local_scheme='http'
// -> SISED_AURL_INTERNAL = http://127.0.0.1:PORT/ (jamais https:// vers 127.0.0.1)
// Host header pour les appels curl internes (Session 44)
// SISED_AURL_INTERNAL = http://127.0.0.1:PORT_LOCAL/stateduc/ (bypass Fortinet/NAT)
// Le header Host = HTTP_HOST (ex: stateduc.ins.ne:9191) permet a Apache de router
// vers le bon VirtualHost meme si l'URL utilise 127.0.0.1.
// Fonctionne quel que soit le nom de domaine ou la topologie reseau.
$curl->setHeader('Host', $GLOBALS['SISED_HOST_HEADER']);

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

	$urlBase = $GLOBALS['SISED_AURL_INTERNAL'].'questionnaire_ws.php?sector='.$id_sector.'&theme='.$id_theme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp.'&annee='.$id_year.'&login='.$user.'&langue=fr';
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
	session_write_close(); // Libere le verrou de session avant l'appel curl interne
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
	// Fallback PARAM_DEFAUT : si annee absente (app non reconnectee, PIN-only unlock)
	if ($id_year == '' || $id_year == '0') {
		$_def = $GLOBALS['conn_dico']->GetOne('SELECT CODE_ANNEE FROM PARAM_DEFAUT');
		if ($_def && (int)$_def > 0) { $id_year = $_def; $_SESSION['annee'] = $id_year; }
	}

	// --- AK-YEAR-CHECK : Vérification année mobile vs serveur ---
	// La pluriannualité permet de CONSULTER les données des années précédentes,
	// mais l'ENVOI de données doit toujours cibler l'année active du serveur.
	// Si l'app mobile envoie une année différente de l'année active serveur,
	// on rejette l'envoi immédiatement avec un message explicite.
	$is_mobile_request = ($id_annee != '' && $id_annee != '0');
	if ($is_mobile_request) {
		$annee_serveur = $GLOBALS['conn_dico']->GetOne('SELECT CODE_ANNEE FROM PARAM_DEFAUT');
		if ($annee_serveur && (int)$annee_serveur > 0 && (int)$id_annee !== (int)$annee_serveur) {
			// AK-YEAR-LABEL : récupérer les libellés lisibles (ex: "2009-2010", "2026-2027")
			// depuis TYPE_ANNEE (base principale SQL Server, connexion $GLOBALS['conn']).
			// Colonnes : CODE_TYPE_ANNEE (code numérique) et LIBELLE_TYPE_ANNEE (libellé affiché).
			// Fallback sur le code numérique brut si la table est inaccessible ou le libellé vide.
			$col_code_ta    = $GLOBALS['PARAM']['CODE']    . '_' . $GLOBALS['PARAM']['TYPE_ANNEE']; // CODE_TYPE_ANNEE
			$col_libelle_ta = $GLOBALS['PARAM']['LIBELLE'] . '_' . $GLOBALS['PARAM']['TYPE_ANNEE']; // LIBELLE_TYPE_ANNEE
			$table_ta       = $GLOBALS['PARAM']['TYPE_ANNEE'];                                       // TYPE_ANNEE

			$lib_annee_mobile  = (string)(int)$id_annee;   // fallback = code brut
			$lib_annee_serveur = (string)(int)$annee_serveur; // fallback = code brut

			if (isset($GLOBALS['conn']) && $GLOBALS['conn'] !== false) {
				// Libellé de l'année mobile (envoyée par l'app)
				$sql_lib_m = 'SELECT ' . $col_libelle_ta . ' FROM ' . $table_ta
				           . ' WHERE ' . $col_code_ta . ' = ' . (int)$id_annee;
				$row_m = $GLOBALS['conn']->GetRow($sql_lib_m);
				if ($row_m !== false && is_array($row_m)) {
					$r_upper = array_change_key_case($row_m, CASE_UPPER);
					if (!empty($r_upper[$col_libelle_ta])) {
						$lib_annee_mobile = trim((string)$r_upper[$col_libelle_ta]);
					}
				}
				// Libellé de l'année active du serveur
				$sql_lib_s = 'SELECT ' . $col_libelle_ta . ' FROM ' . $table_ta
				           . ' WHERE ' . $col_code_ta . ' = ' . (int)$annee_serveur;
				$row_s = $GLOBALS['conn']->GetRow($sql_lib_s);
				if ($row_s !== false && is_array($row_s)) {
					$r_upper = array_change_key_case($row_s, CASE_UPPER);
					if (!empty($r_upper[$col_libelle_ta])) {
						$lib_annee_serveur = trim((string)$r_upper[$col_libelle_ta]);
					}
				}
			}

			$rps = array(
				$lib_status  => $status_ko,
				$lib_message => $GLOBALS['PARAM_WS']['KO'],
				$lib_data    => 'Annee incorrecte : votre application utilise l\'annee '
					. $lib_annee_mobile . ' mais le serveur est sur l\'annee ' . $lib_annee_serveur
					. '. Veuillez vous reconnecter pour mettre a jour l\'annee active.'
			);
			echo json_encode($rps);
			return;
		}
	}
	// --- FIN AK-YEAR-CHECK ---

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
		// SESSION 62b — DIAGNOSTIC KOSAVE CIBLÉ (toute la réponse scannée)
		// La réponse de questionnaire_ws.php fait >>4000 chars (HTML complet).
		// On ne peut pas la tronquer → on cherche directement les tokens dans toute la réponse.
		if ($statut_save === "KOSAVE") {
			$resp_full = $instance->response;
			$resp_flat = preg_replace('/\s+/', ' ', $resp_full);

			// MAJOK : cherche MAJ_OK=true|false dans TOUTE la réponse
			$majok_pos = strpos($resp_flat, 'MAJ_OK=');
			if ($majok_pos !== false) {
				$string .= "MAJOK[" . substr($resp_flat, $majok_pos, 30) . "]\n";
			} else {
				$string .= "MAJOK[ABSENT — MAJ_OK non trouvé dans la réponse]\n";
			}

			// SQLERR : cherche "error inserting", "error deleting", "error updating" (maj_bdd)
			// Ces patterns sont spécifiques à grille.class.php — pas dans le JS HTML
			$sql_patterns = array('error inserting', 'error deleting', 'error updating');
			$sql_found = false;
			foreach ($sql_patterns as $pat) {
				$pat_pos = stripos($resp_flat, $pat);
				if ($pat_pos !== false) {
					$sql_found = true;
					$sql_ctx = substr($resp_flat, max(0, $pat_pos - 20), 800);
					$string .= "SQLERR[" . $sql_ctx . "]\n";
					break;
				}
			}
			if (!$sql_found) {
				$string .= "SQLERR[ABSENT — aucun error insert/delete/update dans la réponse]\n";
				// DIAG_TAIL : 400 derniers chars (zone où apparaissent MAJ_OK et les erreurs)
				$string .= "DIAG_TAIL[" . substr($resp_flat, -400) . "]\n";
			}
		}
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
	$curl->error(function($instance) use ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_year, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
		$rps = array($lib_status=>$status_ko,$lib_message=>$status_ko,$lib_data=>$instance->error_code." : ".$instance->error_message);
		$date_time_err = date('Y/m/d H:i:s');
		$string = $date_time_err;
		$string .= ";".$instance->error_code.":".$instance->error_message;
		$string .= ";".$instance->url;
		$string .= ";\n";
		$myFile = "moblogs/".$user.".log";
		renameLastFile("moblogs/".$user);
		$fh = fopen($myFile, 'a');
		@fwrite($fh, $string);
		@fclose($fh);
		saveLogInfo($user, $date_time_err, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, "KO", $id_year);
		echo json_encode($rps);
	});
	$curl->complete(function($instance) {
		//echo 'call completed' . "<br/>";
	});	
	
	$data_array = $data_to_send;
	
	$data_to_send = array();
	foreach ($data_array as $key => $value) {
    	$data_to_send[$key] = str_replace("_slh_", "/", $value);
	}
	   
	$urlBase = $GLOBALS['SISED_AURL_INTERNAL'].'questionnaire_ws.php?sector='.$id_sector.'&theme='.$id_theme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp.'&annee='.$id_year.'&login='.$user.'&langue=fr';
	
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
	} else {
    // SESSION 60 FIX DÉFINITIF — KOSAVE thèmes sans filtre (10502, 10602, 10702)
    //
    // ANALYSE ROOT CAUSE COMPLÈTE (sessions 58–60) :
    //
    //   questionnaire_ws.php utilise session_start(['read_and_close'=>true]) → Session B
    //   (créée par cURL data_save→questionnaire_ws) est en LECTURE SEULE.
    //   Toute écriture dans $_SESSION (ex: $_SESSION['filtre']='') est ignorée.
    //
    //   La Session 59 a ajouté dans instance_grille.php :
    //     if (isset($_GET['filtre'])) $code_filtre = $_GET['filtre'];
    //     else                        $code_filtre = $_SESSION['filtre'];
    //
    //   Mais quand id_filter=="null", data_save.php N'AJOUTAIT PAS &filtre= à l'URL.
    //   Résultat : isset($_GET['filtre']) = false → fallback sur $_SESSION['filtre']
    //   stale de Session B (ex: '1') → WHERE CODE_TYPE_PERIODE=1 → matrice vide → KOSAVE.
    //
    // FIX SESSION 60 : ajouter EXPLICITEMENT &filtre= (chaîne vide) à l'URL cURL
    // quand il n'y a pas de filtre. Ainsi dans instance_grille.php :
    //   isset($_GET['filtre']) = TRUE et $_GET['filtre'] = '' → code_filtre = ''
    //   → get_dico() n'ajoute PAS de clause WHERE filtre → lecture complète → OKSAVE.
    //
    // GARANTIE : ce &filtre= (vide) est inoffensif pour questionnaire_ws.php car
    //   ligne 21 : if(isset($_GET['filtre']) && $_GET['filtre']<>'') $_SESSION['filtre']=...
    //   la condition $_GET['filtre']<>'' est fausse → $_SESSION['filtre'] NON modifié.
    //   Instance_grille lit $code_filtre='' → grille sans filtre → OKSAVE.
    $urlBase .= '&filtre=';
  }
  //echo "<pre>".$urlBase; print_r($data_to_send);   return;
	session_write_close(); // Libere le verrou de session avant l'appel curl interne
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
    // Endpoint de diagnostic - Session 44
    // GET http://stateduc.ins.ne:9191/stateduc/data_save.php/test/
    // Retourne les URLs internes, variables serveur et resultat du probe TCP
    header('Content-Type: application/json; charset=utf-8');
    $info = array(
        'SISED_AURL'          => $GLOBALS['SISED_AURL'],
        'SISED_AURL_INTERNAL' => $GLOBALS['SISED_AURL_INTERNAL'],
        'SISED_HOST_HEADER'   => isset($GLOBALS['SISED_HOST_HEADER']) ? $GLOBALS['SISED_HOST_HEADER'] : 'N/A',
        'HTTP_HOST'           => isset($_SERVER['HTTP_HOST'])   ? $_SERVER['HTTP_HOST']   : 'N/A',
        'SERVER_ADDR'         => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'N/A',
        'SERVER_PORT'         => isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 'N/A',
        'SERVER_NAME'         => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'N/A',
    );
    // Test TCP vers SISED_AURL_INTERNAL (doit etre 127.0.0.1:port_local)
    $p = parse_url($GLOBALS['SISED_AURL_INTERNAL']);
    $test_ip   = isset($p['host']) ? $p['host'] : '127.0.0.1';
    $test_port = isset($p['port']) ? (int)$p['port'] : 80;
    $en = 0; $es = '';
    $sock = @fsockopen($test_ip, $test_port, $en, $es, 3);
    if ($sock !== false) { fclose($sock); $info['tcp_probe_internal'] = 'OK -> '.$GLOBALS['SISED_AURL_INTERNAL']; }
    else { $info['tcp_probe_internal'] = 'FAIL: '.$en.' '.$es; }
    echo json_encode($info, JSON_PRETTY_PRINT);
});

// AK-CLEANUP: route /updateScore \u00e9tait du code debug (echo $id + print_r) \u2014 d\u00e9sactiv\u00e9e
// $app->post('/updateScore/:id', function($id) use($app) { ... });

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

function saveLogInfo($user, $date_time, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $statut, $id_annee = 0) {
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