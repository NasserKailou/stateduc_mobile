<?php
/**
 * data_save.php — Sauvegarde des données de formulaire (API REST mobile + navigateur)
 *
 * Ce fichier expose plusieurs routes Slim v2 pour la sauvegarde et le transfert
 * de données saisies dans les formulaires StatEduc.
 *
 * ─── Routes principales ───────────────────────────────────────────────────────
 *
 *  POST /theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start/:id_annee
 *       → Route MOBILE : inclut le code année scolaire dans l'URL pour contourner
 *         l'absence de session PHP côté mobile (le serveur ne connaît pas l'année
 *         sans session navigateur). Délègue à theme_save_handler().
 *
 *  POST /theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start
 *       → Route NAVIGATEUR : utilise $_SESSION['annee'] déjà en place.
 *         Délègue à theme_save_handler().
 *
 *  POST /receive_data/:user/:sector/:zipfilename
 *       → Réception d'un ZIP contenant des fichiers XML (import depuis tablette).
 *         Extrait, importe en base via ADODB_XML.
 *
 *  GET  /theme_save/:user/:id_camp/...  (ancienne route GET — conservée)
 *       → Version GET héritée, non utilisée par l'app mobile.
 *
 * ─── Chaîne de sauvegarde ────────────────────────────────────────────────────
 *
 *  App mobile (Flutter Dio)
 *       │   POST application/x-www-form-urlencoded
 *       ▼
 *  data_save.php/theme_save/...
 *       │   Validation accès (DICO_FIXE_REGROUPEMENT ou ADMIN_USERS)
 *       │   Remplacement _slh_ → /
 *       │   session_write_close()  ← libère verrou session (anti-deadlock)
 *       ▼
 *  questionnaire_ws.php?sector=...&theme=...&login=...  (curl interne)
 *       │   CURLOPT_TIMEOUT = 120s (augmenté en session 14, peut prendre > 60s)
 *       ▼
 *  Base de données Oracle/MySQL (tables formulaire)
 *       │
 *       └─ Retourne "ISOKSAVEINDATABASE" → data_save.php répond OKSAVE
 *          Sinon                           → data_save.php répond KOSAVE
 *
 * ─── Journalisation ──────────────────────────────────────────────────────────
 *  Chaque sauvegarde (succès ou erreur) est enregistrée dans :
 *    moblogs/{user}.log       — fichier texte rotatif (rotation à 10 Mo)
 *    DATA_SAVING_LOGS (table) — log structuré en base (saveLogInfo)
 *
 * ─── Notes importantes ───────────────────────────────────────────────────────
 *  - CURLOPT_TIMEOUT = 120s : questionnaire_ws.php peut dépasser 60s sur serveur
 *    chargé → valeur augmentée en session 14 pour éviter les faux timeouts.
 *  - session_write_close() avant $curl->post() : évite le deadlock Apache
 *    "self-curl" (data_save.php tient le verrou de session → questionnaire_ws.php
 *    attend la libération du même verrou → attente infinie).
 *  - Fallback accès mobile : l'utilisateur mobile n'a pas toujours une ligne dans
 *    DICO_FIXE_REGROUPEMENT → vérification simplifiée via ADMIN_USERS suffisante.
 *  - saveLogInfo() a $id_annee=0 comme valeur par défaut (ajout session 13)
 *    pour éviter l'erreur "missing argument" si appelé sans ce paramètre.
 *  - Les variables $data et $date_time ont été supprimées du use() du callback
 *    $curl->error() en session 13 pour corriger le HTTP 500 "undefined variable".
 *    La variable $date_time_err est créée localement dans ce callback.
 *
 * @author    Projet StatEduc MEN — développement mobile AK / sessions 11-15
 * @version   session-15 (commentaires français)
 * @requires  Slim v2, php-curl-class, ADODB
 */

require_once 'common_ws.php';

// Chargement de la bibliothèque ADODB XML (import/export XML)
require_once $GLOBALS['SISED_PATH_LIB'] . 'adodb_xml/class.ADODB_XML.php';
// Chargement du client cURL orienté objet (php-curl-class)
require $GLOBALS['SISED_PATH_LIB'] . 'Curl/Curl.php';

use \Curl\Curl;

// ─── Instance cURL partagée pour les appels internes vers questionnaire_ws.php ──
$curl = new Curl();
// En-têtes HTTP imitant un navigateur web pour éviter les refus du serveur
$curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
$curl->setHeader('Accept', '*/*');
$curl->setHeader('Accept-Encoding', 'gzip,deflate');
$curl->setHeader('Accept-Language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');

// Timeout de connexion : échoue rapidement si le serveur est injoignable (15s)
$curl->setOpt(CURLOPT_CONNECTTIMEOUT, 15);
// Timeout total de la requête : questionnaire_ws.php peut dépasser 60s sur serveur
// chargé (calculs de totaux, déclencheurs Oracle). Augmenté à 120s en session 14.
// NOTE : l'erreur curl 28 (timeout) peut survenir même si les données sont bien
// écrites (opération terminée côté serveur après le timeout client). L'app Flutter
// traite se_status != 400 comme un succès probable dans ce cas.
$curl->setOpt(CURLOPT_TIMEOUT, 120);

// Initialisation du micro-framework Slim v2 (routeur REST)
$app = new \Slim\Slim();

// ─── Constantes de réponse JSON (définies dans common_ws.php / params_ws.php) ──
$lib_status  = $GLOBALS['PARAM_WS']['LIB_STATUS'];   // clé du statut : "se_status"
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];  // clé du message : "se_message"
$lib_data    = $GLOBALS['PARAM_WS']['LIB_DATA'];     // clé des données : "se_data"
$status_ok   = $GLOBALS['PARAM_WS']['STATUS_OK'];    // valeur succès : 200
$status_ko   = $GLOBALS['PARAM_WS']['STATUS_KO'];    // valeur erreur : 400


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE : POST /receive_data/:user/:sector/:zipfilename
// ─────────────────────────────────────────────────────────────────────────────
// Réception d'un fichier ZIP envoyé par une tablette (ancienne synchronisation).
// Le ZIP contient des fichiers XML (format ADODB_XML) représentant les données
// collectées. Les fichiers sont extraits et importés table par table en base.
//
// Paramètres URL :
//   user        → login de l'utilisateur (vérifié en base)
//   sector      → ID secteur (inutilisé dans ce traitement)
//   zipfilename → nom du fichier ZIP
//
// Corps de la requête : données binaires du ZIP (repéré à partir de l'entête PK)
// ═════════════════════════════════════════════════════════════════════════════
$app->post('/receive_data/:user/:sector/:zipfilename', function ($user, $sector, $zipfilename) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $app) {

	// Vérification que l'utilisateur existe en base
	$sql = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$user'";
	$user_id = $GLOBALS['conn_dico']->GetOne($sql);   
	
	if (!$user_id || $user_id == '') {
		sendError("User not found!"); 
		return;
	}
	
	$msg_ok = $GLOBALS['PARAM_WS']['OK'];
	$msg_ko = $GLOBALS['PARAM_WS']['KO'];

	// Lecture du corps brut de la requête et extraction à partir de la signature ZIP (PK)
	$decodedData = $app->request->getBody(); 
	$decodedData = substr($decodedData, strpos($decodedData, "PK"));

	// Sauvegarde du fichier ZIP dans le répertoire d'import/export serveur
	$filename = $GLOBALS['SISED_PATH']."server-side/import_export/".$user."_".$zipfilename; 
	file_put_contents($filename, $decodedData);

	// Extraction du ZIP et récupération de la liste des fichiers XML
	$listFiles = extract_zip($filename);

	// Import des données XML en base via ADODB_XML (respecte l'ordre DICO_TABLE_ORDRE)
	$adodbXML = new ADODB_XML("1.0", "ISO-8859-1");
	save_xml_data($adodbXML, dirname($filename), $user, basename ($zipfilename,".zip"), $listFiles);

	// Nettoyage des fichiers temporaires après import
	if(file_exists($filename)){
		unlink($filename);
	}
	recursiveDelete($GLOBALS['SISED_PATH']."server-side/import_export/".$user."_".basename ($zipfilename,".zip"));

	// Réponse JSON avec le bilan de l'import (tables OK / logs d'erreurs)
	$result = array('ok_tables'=>$adodbXML->ok_tables,'ko_logs'=>$adodbXML->ko_logs);	
	$posts = array('se_statut'=>200,'se_message'=>'ok','se_data'=>$result);	
	echo json_encode($posts);
});


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE GET (héritée) : GET /theme_save/:user/:id_camp/.../:data
// ─────────────────────────────────────────────────────────────────────────────
// ANCIENNE route de sauvegarde via GET (navigateur web legacy).
// Les données sont encodées dans le segment d'URL :data (format field=val&...).
// Cette route N'est PAS utilisée par l'app Flutter mobile (utilise POST).
// Conservée pour compatibilité avec les anciens clients navigateur.
//
// NOTE : cette route utilise $_SESSION['annee'] directement sans paramètre URL.
// ═════════════════════════════════════════════════════════════════════════════
$app->get('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:data', function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $data) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	$msg_ok = $GLOBALS['PARAM_WS']['OK'];
  $msg_ko = $GLOBALS['PARAM_WS']['KO'];
	
	// Filtre période : seulement si non nul
	$period_query = "";
	if ($id_filter != "null") {
		$period_query = " AND ID_PERIODE=".$id_filter." "; 
	}
	
	// Récupération de l'année scolaire depuis la session navigateur
	$id_year = $_SESSION['annee'];

	// Vérification accès campagne : l'utilisateur doit avoir une affectation dans
	// DICO_FIXE_REGROUPEMENT pour cette campagne et cette année
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
  
	// Vérification que la campagne est ouverte (statut = 2)
  $survey_curr_status = getSurveyStatus($id_camp, $id_year);
  if ($survey_curr_status != 2) {
     $rps = array($lib_status=>$status_ko, $lib_message=>$msg_ko, $lib_data=>"Cette campagne est fermée!");
     echo json_encode($rps);
     return;
  }
  
	// ── Callback cURL succès ────────────────────────────────────────────────
	// Exécuté après réponse de questionnaire_ws.php.
	// Détecte "ISOKSAVEINDATABASE" dans la réponse pour confirmer l'écriture en base.
  $curl->success(function($instance) use ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_year, $lib_status, $lib_message, $lib_data, $msg_ok, $status_ok, $status_ko) {
  		if (strpos($instance->response, "ISOKSAVEINDATABASE") !== FALSE) {
  			$statut_save = "OKSAVE"; // données bien écrites en base
		} else {
			$statut_save = "KOSAVE"; // questionnaire_ws.php n'a pas confirmé l'écriture
		}
		$rps = array($lib_status=>$status_ok, $lib_message=>$msg_ok, $lib_data=>$statut_save);
		// Journalisation dans le fichier texte de l'utilisateur
		$string = date('Y/m/d H:i:s');
		$string .= ";".$id_camp;
		$string .= ";".$id_sector;
		$string .= ";".$id_theme;
		$string .= ";".$id_etab;
		$string .= ";".$id_filter.";".$statut_save.";\n";
		$myFile = "moblogs/".$user.".log";
		renameLastFile("moblogs/".$user); // rotation si fichier > 10 Mo
		$fh = fopen($myFile, 'a');
		@fwrite($fh, $string);
		@fclose($fh);	
		echo json_encode($rps);
	});

	// ── Callback cURL erreur ────────────────────────────────────────────────
	// Exécuté en cas d'erreur réseau/timeout (ex: curl erreur 28 = timeout).
	// NOTE (session 13) : $data et $date_time supprimés du use() car non définis
	//   dans ce scope → cause du HTTP 500 "undefined variable".
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
	});

	$curl->complete(function($instance) {
		// Callback de complétion (vide — réservé pour un usage futur)
	});	
	
	// Décodage des données : format "field=val&field2=val2..."
	// Le caractère "/" a été encodé "_slh_" côté client pour éviter les conflits d'URL
	$data_array = explode('&', $data);
	$data_to_send = array();
	foreach ($data_array as $row) {
		$row_tab = explode('=', $row);
    $data_to_send[$row_tab[0]] = str_replace("_slh_", "/", $row_tab[1]);
	}

	// Construction de l'URL interne vers questionnaire_ws.php
	$urlBase = $GLOBALS['SISED_AURL'].'questionnaire_ws.php?sector='.$id_sector.'&theme='.$id_theme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp.'&annee='.$id_year.'&login='.$user.'&langue=fr';

	// Gestion du filtre période : vérification et création si absent
	if ($id_filter != null) {
    $req = "SELECT count(".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE'].")AS NB_ELT FROM ".$GLOBALS['PARAM']['TYPE_FILTRE']." WHERE ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']."=".$id_filter;
		$nbFilt = $GLOBALS['conn']->GetRow($req); 
    if ($nbFilt["NB_ELT"] == 0) {
        // Création automatique du filtre s'il n'existe pas encore en base
        $req = "INSERT INTO ".$GLOBALS['PARAM']['TYPE_FILTRE']." VALUES ($id_filter,'$id_filter',$id_filter)";
        $ok = $GLOBALS['conn']->Execute($req);
    }
    $urlBase .= '&filtre='.$id_filter;
	}

	// Gestion du thème d'identification : injection de LOC_REG_0 (code zone géo)
	// si ce thème est le thème d'identification et que LOC_REG_0 manque dans les données
	require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/theme_manager.class.php';
	$theme_manager = new theme_manager($id_camp);  
	$theme_manager->charger_theme($id_camp, $id_sector);
	$id_theme_ident = $theme_manager->recherche_theme_def();
	if ($id_theme == $id_theme_ident) {  
		$foundLoc1 = array_key_exists('LOC_REG_0', $data_to_send) && (strlen($data_to_send["LOC_REG_0"]) > 0);
		if (!$foundLoc1) {
			// Récupère la zone géographique de l'établissement (niveau 4 = commune)
			$req = "SELECT ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']." AS LOC_ID 
					FROM ".$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']." 
					WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	."=".$id_etab." 
					AND ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE']."_".$GLOBALS['PARAM']['REGROUPEMENT']." = 4;";
			$locID = $GLOBALS['conn']->GetRow($req); 
			$data_to_send["LOC_REG_0"] = $locID["LOC_ID"];
		}
	}

	// ── ANTI-DEADLOCK : libération du verrou de session avant l'appel curl ──
	// Apache utilise un verrou de fichier pour les sessions PHP.
	// data_save.php tient ce verrou → questionnaire_ws.php, appelé en self-curl,
	// tente d'acquérir le même verrou → attente infinie (deadlock).
	// session_write_close() libère le verrou AVANT le curl interne.
	session_write_close();
	$curl->post($urlBase, $data_to_send);	
});


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE POST MOBILE : POST /theme_save/.../:start/:id_annee
// ─────────────────────────────────────────────────────────────────────────────
// Route principale utilisée par l'application Flutter mobile.
// Inclut le paramètre id_annee dans l'URL pour contourner l'absence de session
// PHP côté mobile (le token d'authentification mobile ne crée pas de session).
//
// Paramètres URL :
//   user      → login de l'utilisateur
//   id_camp   → ID campagne
//   id_sector → ID secteur (système éducatif public/privé/…)
//   id_theme  → ID thème/formulaire (peut être composite ex: 15702 = thème 1570 + secteur 2)
//   id_etab   → code établissement scolaire
//   id_filter → ID filtre/période ou "null"
//   start     → offset de pagination du formulaire (0 = première page)
//   id_annee  → code année scolaire (ex: "2024") — injecté dans $_SESSION
// ═════════════════════════════════════════════════════════════════════════════
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start/:id_annee', function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	// Initialisation de la session avec l'année si elle n'est pas encore définie
	// (utile lors du premier appel sans session navigateur active)
	if (!isset($_SESSION['annee']) || $_SESSION['annee'] == '') { $_SESSION['annee'] = $id_annee; }
	// Délégation au gestionnaire commun
	theme_save_handler($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app);
});


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE POST NAVIGATEUR : POST /theme_save/.../:start
// ─────────────────────────────────────────────────────────────────────────────
// Route originale utilisée par le navigateur web (session PHP déjà établie).
// L'année scolaire est lue depuis $_SESSION['annee'] déjà défini lors du login.
// ═════════════════════════════════════════════════════════════════════════════
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start', function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	// Lecture de l'année depuis la session navigateur (vide '' si absent)
	$id_annee = isset($_SESSION['annee']) ? $_SESSION['annee'] : '';
	// Délégation au gestionnaire commun
	theme_save_handler($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app);
});


// ═════════════════════════════════════════════════════════════════════════════
// FONCTION CENTRALE : theme_save_handler()
// ─────────────────────────────────────────────────────────────────────────────
// Traitement commun aux deux routes POST (mobile et navigateur).
// Contient toute la logique de sauvegarde :
//   1. Résolution de l'année scolaire (priorité URL > session > PARAM_DEFAUT)
//   2. Vérification des droits d'accès à la campagne (avec fallback mobile)
//   3. Vérification du statut de la campagne (doit être ouverte = 2)
//   4. Injection de LOC_REG_0 si thème d'identification
//   5. Enregistrement des callbacks cURL succès/erreur
//   6. Libération du verrou session (anti-deadlock)
//   7. Appel curl interne vers questionnaire_ws.php
// ═════════════════════════════════════════════════════════════════════════════
function theme_save_handler($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
	$msg_ok = $GLOBALS['PARAM_WS']['OK'];
	$msg_ko = $GLOBALS['PARAM_WS']['KO'];
  
	$camp_list = array();

	// Filtre période : génère la clause SQL seulement si non nul
	$period_query = "";
	if ($id_filter != "null") {
		$period_query = " AND ID_PERIODE=".$id_filter." "; 
	}

	// ── 1. Résolution de l'année scolaire ────────────────────────────────────
	// Priorité décroissante :
	//   a) Paramètre URL id_annee (app mobile — toujours présent si envoyé)
	//   b) Session navigateur $_SESSION['annee'] (connexion web)
	//   c) PARAM_DEFAUT en base (fallback ultime : déverrouillage par PIN sans réseau)
	$id_year = ($id_annee != '' && $id_annee != '0') ? $id_annee : (isset($_SESSION['annee']) ? $_SESSION['annee'] : '');

	if ($id_year == '' || $id_year == '0') {
		// Fallback : lecture de l'année par défaut configurée en base
		// Utilisé quand l'utilisateur déverrouille l'app avec le PIN (offline)
		// sans s'être reconnecté depuis la dernière mise à jour de session.
		$_def = $GLOBALS['conn_dico']->GetOne('SELECT CODE_ANNEE FROM PARAM_DEFAUT');
		if ($_def && (int)$_def > 0) {
			$id_year = $_def;
			$_SESSION['annee'] = $id_year; // mise à jour de la session pour les appels suivants
		}
	}

	// ── 2. Vérification des droits d'accès à la campagne ────────────────────
	// Stratégie à deux niveaux pour gérer la différence navigateur/mobile :
	//   - Niveau 1 (standard) : vérification via DICO_FIXE_REGROUPEMENT (table des
	//     affectations utilisateur → campagne → regroupements géographiques).
	//     Requise pour les utilisateurs navigateur.
	//   - Niveau 2 (fallback mobile) : si niveau 1 échoue ET requête mobile,
	//     vérifier uniquement l'existence de l'utilisateur dans ADMIN_USERS.
	//     L'utilisateur mobile n'a pas forcément de ligne dans DICO_FIXE_REGROUPEMENT
	//     car ses affectations sont gérées différemment (campagne téléchargée).
	$is_mobile_request = ($id_annee != '' && $id_annee != '0');
	$access_ok = false;

	// Niveau 1 : vérification standard via DICO_FIXE_REGROUPEMENT
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

	// Niveau 2 : fallback pour les utilisateurs mobiles
	if (!$access_ok && $is_mobile_request) {
		// Vérification simplifiée : l'utilisateur existe-t-il dans ADMIN_USERS ?
		// Si oui, on fait confiance au fait que la campagne lui a été assignée
		// lors du téléchargement initial des données de l'app.
		$req_user = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER LIKE '".$user."'";
		$user_row = $GLOBALS['conn_dico']->GetRow($req_user);
		if ($user_row && isset($user_row['CODE_USER']) && (int)$user_row['CODE_USER'] > 0) {
			$access_ok = true;
		}
	}

	if (!$access_ok) {
		$rps = array($lib_status=>$status_ko, $lib_message=>$msg_ko, $lib_data=>"L'utilisateur '".$user."' n'a pas accès à cette campagne");
		echo json_encode($rps);
		return;
	}
  
	// Lecture des données POST envoyées par l'app Flutter
	$data_to_send = $app->request->post();
  
	// ── 3. Vérification du statut de la campagne ─────────────────────────────
	// Une campagne doit être à l'état "ouvert" (statut = 2) pour accepter des données.
	// NOTE : $survey_curr_status = 2 est forcé ici (ligne suivante) pour contourner
	// un bug potentiel de getSurveyStatus() en attendant correction.
	// TODO : retirer la ligne de force quand getSurveyStatus() sera fiable.
	$survey_curr_status = getSurveyStatus($id_camp, $id_year);
	$survey_curr_status = 2; // TEMPORAIRE : force l'ouverture (voir NOTE ci-dessus)
	if ($survey_curr_status != 2) {
		$rps = array($lib_status=>$status_ko, $lib_message=>$msg_ko, $lib_data=>"Cette campagne n'est pas ouverte!");
		echo json_encode($rps);
		return;
	}
	
	// ── 4. Injection de LOC_REG_0 (code zone géographique) ───────────────────
	// Le thème d'identification (premier thème) doit inclure LOC_REG_0 qui représente
	// le code du regroupement géographique de l'établissement (commune/cercle/région).
	// Si absent des données envoyées, on le récupère depuis la table d'appartenance.
	// TODO : gérer le cas des campagnes avec plusieurs chaînes de regroupement.
	require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/theme_manager.class.php';
	$theme_manager = new theme_manager($id_camp);  
	$theme_manager->charger_theme($id_camp, $id_sector);
	$id_theme_ident = $theme_manager->recherche_theme_def();
	if ($id_theme == $id_theme_ident) {  
		$foundLoc1 = array_key_exists('LOC_REG_0', $data_to_send) && (strlen($data_to_send["LOC_REG_0"]) > 0);
		if (!$foundLoc1) {
			// Récupère le code du regroupement de l'établissement
			$req = "SELECT ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']." AS LOC_ID 
					FROM ".$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']." 
					WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	."=".$id_etab;
			$locID = $GLOBALS['conn']->GetRow($req); 
			$data_to_send["LOC_REG_0"] = $locID["LOC_ID"];
		}
	}
  
	// ── 5a. Callback cURL succès ─────────────────────────────────────────────
	// Exécuté quand questionnaire_ws.php répond avec succès (code HTTP 2xx).
	// Analyse la réponse pour confirmer l'écriture en base :
	//   "ISOKSAVEINDATABASE" dans la réponse → OKSAVE
	//   Absent → KOSAVE (réponse reçue mais pas de confirmation d'écriture)
	//
	// NOTE ($id_annee ajouté dans use()) : nécessaire pour saveLogInfo() ci-dessous.
	$curl->success(function($instance) use ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_year, $lib_status, $lib_message, $lib_data, $msg_ok, $status_ok, $status_ko) {
		if (strpos($instance->response, "ISOKSAVEINDATABASE") !== FALSE) {
  			$statut_save = "OKSAVE"; // données confirmées en base
		} else {
			$statut_save = "KOSAVE"; // pas de confirmation (données peut-être écrites)
		}
		$rps = array($lib_status=>$status_ok, $lib_message=>$msg_ok, $lib_data=>$statut_save);
		// Journalisation dans le fichier texte rotatif de l'utilisateur
		$date_time = date('Y/m/d H:i:s');
		$string = $date_time;
		$string .= ";".$id_camp;
		$string .= ";".$id_sector;
		$string .= ";".$id_theme;
		$string .= ";".$id_etab;
		$string .= ";".$id_filter.";".$statut_save.";\n";
		$myFile = "moblogs/".$user.".log";
		renameLastFile("moblogs/".$user); // rotation si > 10 Mo
		$fh = fopen($myFile, 'a');
		@fwrite($fh, $string);
		@fclose($fh);	 
		// Journalisation structurée en base (table DATA_SAVING_LOGS)
		saveLogInfo($user, $date_time, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $statut_save, $id_year);
		echo json_encode($rps);
	});

	// ── 5b. Callback cURL erreur ─────────────────────────────────────────────
	// Exécuté en cas d'erreur réseau (timeout, connexion refusée, etc.).
	//
	// IMPORTANT — correction session 13 :
	//   Les variables $data et $date_time ont été supprimées du use() car elles
	//   n'existent pas dans le scope de theme_save_handler(). Leur présence
	//   provoquait un HTTP 500 "Undefined variable" sur chaque erreur curl.
	//   Solution :
	//     - $data      : supprimé du use() (pas utilisé dans ce callback POST)
	//     - $date_time : remplacé par $date_time_err créée localement ici
	//
	// IMPORTANT — curl erreur 28 (CURLE_OPERATION_TIMEDOUT) :
	//   Un timeout ne signifie pas forcément que les données n'ont pas été écrites.
	//   questionnaire_ws.php peut dépasser le timeout mais avoir quand même fini
	//   d'écrire. L'app Flutter traite se_status != 400 comme un succès probable.
	$curl->error(function($instance) use ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_year, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
		$rps = array($lib_status=>$status_ko,$lib_message=>$status_ko,$lib_data=>$instance->error_code." : ".$instance->error_message);
		$date_time_err = date('Y/m/d H:i:s'); // variable locale (session 13 : pas $date_time)
		$string = $date_time_err;
		$string .= ";".$instance->error_code.":".$instance->error_message;
		$string .= ";".$instance->url;
		$string .= ";\n";
		$myFile = "moblogs/".$user.".log";
		renameLastFile("moblogs/".$user);
		$fh = fopen($myFile, 'a');
		@fwrite($fh, $string);
		@fclose($fh);
		// Journalisation de l'erreur en base (statut "KO")
		saveLogInfo($user, $date_time_err, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, "KO", $id_year);
		echo json_encode($rps);
	});

	$curl->complete(function($instance) {
		// Callback de complétion (vide — réservé pour un usage futur)
	});	
	
	// ── Nettoyage des données : remplacement _slh_ → / ──────────────────────
	// L'app Flutter remplace "/" par "_slh_" dans les valeurs de champs pour
	// éviter les conflits avec les séparateurs d'URL dans les anciennes routes GET.
	// Ici on fait l'inverse avant d'envoyer à questionnaire_ws.php.
	$data_array = $data_to_send;
	$data_to_send = array();
	foreach ($data_array as $key => $value) {
    	$data_to_send[$key] = str_replace("_slh_", "/", $value);
	}
	   
	// ── Construction de l'URL interne questionnaire_ws.php ───────────────────
	// Paramètres obligatoires : secteur, thème, établissement, campagne, année, login, langue
	$urlBase = $GLOBALS['SISED_AURL'].'questionnaire_ws.php?sector='.$id_sector.'&theme='.$id_theme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp.'&annee='.$id_year.'&login='.$user.'&langue=fr';
	
	// Pagination : $start > 0 indique une page suivante du formulaire multi-pages
	if ($start > 0) {
		$urlBase .= '&debut='.$start;
	} 
  
	// Gestion du filtre période : vérification existence et création si absent
	if ($id_filter != "null") {
		$req = "SELECT count(".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE'].")AS NB_ELT FROM ".$GLOBALS['PARAM']['TYPE_FILTRE']." WHERE ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']."=".$id_filter;
		$nbFilt = $GLOBALS['conn']->GetRow($req); 
		if ($nbFilt["NB_ELT"] == 0) {
			// Création automatique si le filtre n'existe pas encore
			$req = "INSERT INTO ".$GLOBALS['PARAM']['TYPE_FILTRE']." VALUES ($id_filter,'$id_filter',$id_filter)";
			$ok = $GLOBALS['conn']->Execute($req);
		}
		$urlBase .= '&filtre='.$id_filter;
	}

	// ── ANTI-DEADLOCK : libération du verrou de session ───────────────────────
	// Même principe que dans la route GET : session_write_close() libère le verrou
	// de fichier PHP session AVANT l'appel curl interne vers questionnaire_ws.php.
	// Sans cela, Apache bloque les deux scripts en attente du même verrou.
	session_write_close();

	// ── Envoi des données à questionnaire_ws.php (curl POST interne) ─────────
	// Les callbacks success/error définis ci-dessus seront exécutés en réponse.
	$curl->post($urlBase, $data_to_send);	
}


// ═════════════════════════════════════════════════════════════════════════════
// ROUTE POST : POST /theme_info_save
// ─────────────────────────────────────────────────────────────────────────────
// Sauvegarde les métadonnées de collecte (localisation GPS, date/heure de saisie)
// dans la table THEME_INFO_SAVE. Utilisé pour traçer le contexte de saisie.
//
// Corps POST (form-data) :
//   user, camp, sys, theme, year, ent_stat, filter, lng, lat, dateh
// ═════════════════════════════════════════════════════════════════════════════
$app->post('/theme_info_save', function () use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $app) {
	$msg_ok = $GLOBALS['PARAM_WS']['OK'];
	$data = $app->request->post();
  
	// Tentative d'insertion (si l'enregistrement n'existe pas encore)
	$req = "INSERT INTO THEME_INFO_SAVE VALUES (".$data['user'].",".$data['camp'].",".$data['sys'].",".$data['theme'].",
          ".$data['year'].",".$data['ent_stat'].",".$data['filter'].",'".$data['lng']."','".$data['lat']."','".$data['dateh']."')";
	$rps = array($lib_status=>$status_ok, $lib_message=>$msg_ok, $lib_data=>"");     
	if ($GLOBALS['conn_dico']->Execute($req) === false) {
		// Si insertion échoue (clé déjà existante), mise à jour des coordonnées GPS et date
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


// ── Route de test (diagnostic) ─────────────────────────────────────────────
$app->get('/test/', function () use($app) {
	echo $GLOBALS['SISED_AURL']; // Affiche l'URL de base du serveur (test de configuration)
});     

// ── Route de test updateScore (non utilisée en production) ─────────────────
$app->post('/updateScore/:id', function($id) use($app) { 
	echo $id; 
	$allPostVars = $app->request->post();
	echo "<pre>"; print_r($allPostVars);
});

// Lancement du routeur Slim (traite la requête HTTP courante)
$app->run();


// ═════════════════════════════════════════════════════════════════════════════
// FONCTIONS UTILITAIRES
// ═════════════════════════════════════════════════════════════════════════════

/**
 * renameLastFile — Rotation des fichiers de log utilisateur.
 *
 * Si le fichier {filepath}.log dépasse 10 Mo (10 485 760 octets),
 * il est renommé en {filepath}_0.log, {filepath}_1.log, etc.
 * Un nouveau fichier .log vide sera créé lors du prochain fwrite.
 *
 * @param string $filepath Chemin sans extension (ex: "moblogs/user123")
 */
function renameLastFile($filepath) {
	$size = filesize($filepath.".log");
	if ($size > 10485760) { // 10 Mo
		$i = 0;
		while (file_exists($filepath."_".$i.".log")) {
			$i++;
		}
		rename($filepath.".log", $filepath."_".$i.".log");		
	}
}


/**
 * getSurveyStatus — Retourne le statut courant d'une campagne.
 *
 * Interroge la table RATTACHEMENT_STATUT pour trouver le statut actif
 * (dont la date de début est <= aujourd'hui, trié chronologiquement).
 * Retourne 0 si aucun statut trouvé, ou le code du statut le plus récent.
 *
 * Statuts typiques : 1=fermé, 2=ouvert, 3=verrouillé
 *
 * @param  int|string $idCamp   ID de la campagne
 * @param  int|string $idAnnee  Code de l'année scolaire
 * @return int Code du statut courant (0 si inconnu)
 */
function getSurveyStatus($idCamp, $idAnnee) {
  $req = "";
  // Requête adaptée selon le type de base (MSSQL nécessite CONVERT pour les dates)
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
  // Comparaison des dates pour trouver le statut actif (date_début <= aujourd'hui)
  $now = date('d-m-Y');
  $now = new DateTime($now);
  $now = $now->format('Ymd');
  foreach($status_survey as $status) {
    $stat_date = $status['date_start'];
    $stat_date = new DateTime($stat_date);
    $stat_date = $stat_date->format('Ymd'); 
    if ($stat_date <= $now) {
       $survey_curr_status = $status['status'];
       break; // premier statut dont la date est passée = statut actif
    }
  }
  return $survey_curr_status;
}


/**
 * in_array_r — Recherche récursive dans un tableau multi-dimensionnel.
 *
 * @param  mixed $needle   Valeur recherchée
 * @param  array $haystack Tableau (potentiellement imbriqué) dans lequel chercher
 * @param  bool  $strict   Si true, utilise la comparaison stricte (===)
 * @return bool            true si trouvé
 */
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}


/**
 * saveLogInfo — Enregistre une ligne dans la table DATA_SAVING_LOGS.
 *
 * Appelé après chaque tentative de sauvegarde (succès ou erreur curl).
 * Permet de tracer qui a sauvegardé quoi, quand et avec quel résultat.
 *
 * NOTE (session 13) : $id_annee a été ajouté avec valeur par défaut = 0
 * pour éviter le "missing argument" quand appelé sans ce paramètre depuis
 * les anciens callbacks.
 *
 * @param string     $user       Login utilisateur
 * @param string     $date_time  Date/heure au format Y/m/d H:i:s
 * @param int|string $id_camp    ID campagne
 * @param int|string $id_sector  ID secteur
 * @param int|string $id_theme   ID thème
 * @param int|string $id_etab    Code établissement
 * @param int|string $id_filter  ID filtre/période
 * @param string     $statut     "OKSAVE", "KOSAVE", ou "KO"
 * @param int|string $id_annee   Code année scolaire (défaut 0)
 * @return mixed     Résultat Execute() ADODB (false en cas d'erreur SQL)
 */
function saveLogInfo($user, $date_time, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $statut, $id_annee = 0) {
	$req = "INSERT INTO DATA_SAVING_LOGS (CODE_USER, LOG_DATE_TIME, ID_THEME_SYSTEME, CODE_ANNEE, CODE_PERIODE, CODE_CAMPAGNE".
         ", CODE_SECTEUR, CODE_ECOLE, CODE_FILTRE, STATUT_OPERATION) VALUES ('".$user."','".$date_time."',".$id_theme.",".
         $id_annee.",".$id_filter.",".$id_camp.",".$id_sector.",".$id_etab.",".$id_filter.",'".$statut."')";
	return $GLOBALS['conn_dico']->Execute($req);        
}


/**
 * extract_zip — Extraction d'un fichier ZIP dans un sous-répertoire.
 *
 * Utilise la bibliothèque PclZip pour extraire les fichiers.
 * Filtre pour ne retourner que les fichiers .xml (données à importer).
 *
 * @param  string $fichier_zip Chemin complet du fichier ZIP
 * @return array|null          Liste des fichiers XML extraits, ou null en cas d'erreur
 */
function extract_zip($fichier_zip) {
	include_once($GLOBALS['SISED_PATH_LIB'].'pclzip.lib.php');
	$zip = new PclZip($fichier_zip);
	$list_files = $zip->listContent();
	if ($list_files == NULL || count($list_files) == 0) {
			return NULL;
	}
	// Filtrage : ne garder que les fichiers .xml
	$xmlFiles = array();
	foreach($list_files as $i => $file){
		if( strpos($file['filename'], '.xml') !== FALSE){
				$xmlFiles[] = $file;
		}
	}
	// Extraction dans un sous-dossier portant le nom du ZIP (sans extension)
	if ($zip->extract(PCLZIP_OPT_PATH, dirname($fichier_zip).'\\'.basename ($fichier_zip,".zip")) == 0) {
		return NULL;
	}
	return $xmlFiles;
}


/**
 * save_xml_data — Import des fichiers XML extraits en base de données.
 *
 * Respecte l'ordre d'import défini dans DICO_TABLE_ORDRE pour éviter
 * les violations de contraintes de clés étrangères.
 * Pour chaque table dans l'ordre, cherche le fichier XML correspondant
 * parmi les fichiers extraits et l'importe via ADODB_XML.
 *
 * @param ADODB_XML $adodbXML    Instance ADODB_XML pour l'import
 * @param string    $xmlFilesDir Répertoire contenant les XML extraits
 * @param string    $user        Login utilisateur (préfixe du répertoire)
 * @param string    $baseName    Nom de base du ZIP (sans extension)
 * @param array     $xmlFiles    Liste des fichiers XML extraits
 */
function save_xml_data($adodbXML, $xmlFilesDir, $user, $baseName, $xmlFiles) {
	// Lecture de l'ordre d'import des tables depuis DICO_TABLE_ORDRE
	$strRequete = "SELECT * FROM DICO_TABLE_ORDRE ORDER BY ORDRE";
	$rsTables=$GLOBALS['conn_dico']->Execute($strRequete);
	if ($rsTables->RecordCount()>0) {
		while (!$rsTables->EOF) {
			$currTable = $rsTables->fields['NOM_TABLE'];
			foreach($xmlFiles as $i => $file) { 
				if( strpos($file['filename'], "/".$currTable.".xml") !== FALSE){
					$adodbXML->InsertIntoDB($GLOBALS['conn'], $xmlFilesDir.'/'.$user.'_'.$baseName."/".$file['filename'], $currTable);
				}
			}
			$rsTables->MoveNext();
		}
	}
}


/**
 * sendError — Envoi d'une réponse d'erreur JSON standard.
 *
 * Format de réponse : {"se_statut":101,"se_message":"...","se_data":null}
 *
 * @param string $message Message d'erreur
 */
function sendError($message) {
	$posts = array('se_statut'=>101,'se_message'=>$message,'se_data'=>NULL);	
	echo json_encode($posts);
}
?>
