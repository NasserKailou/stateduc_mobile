<?php


require_once 'common_ws.php';

$app = new \Slim\Slim();

$lib_status =  $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data = $GLOBALS['PARAM_WS']['LIB_DATA'];

$status_ok = $GLOBALS['PARAM_WS']['STATUS_OK'];

$app->add(new \HttpAuth());

// test si l'utisateur est connu dans la base
$app->get('/user/:login/:password', function ($login, $password) use ($lib_status, $lib_message, $lib_data, $status_ok) {
	
	$status = $GLOBALS['PARAM_WS']['LOGIN_OK'];	
	//recup des infos de l'utilisateur
	$user_info = infos_user_ws($login, md5($password));
	
	$requete = "SELECT PARAM_DEFAUT.CODE_ANNEE FROM PARAM_DEFAUT";
   
	$curr_year = $GLOBALS['conn_dico']->GetOne($requete); 
	
	$requete = "SELECT ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_ANNEE']."
						FROM ".$GLOBALS['PARAM']['TYPE_ANNEE']."
						WHERE ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_ANNEE']."=".$curr_year;

	$curr_year_lib = trim($GLOBALS['conn']->GetOne($requete)); 
  
	if (!is_array($user_info) || $user_info['CODE_USER'] == null) {
		$status = $GLOBALS['PARAM_WS']['LOGIN_KO'];	
	}
	
	$user_ident = array("id"=>$user_info['CODE_USER'], "group"=>$user_info['CODE_GROUPE'], "login"=>$login, "firstname"=>$user_info['NOM_LONG_USER'], "lastname"=>$user_info['EMAIL_USER'], "codeyear"=>$curr_year, "libyear"=>$curr_year_lib);
	
	if ($GLOBALS['PARAM']['FILTRE'] == true) {
	
		$requete = "SELECT ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']." AS CODE_TYPE_PERIOD, ".$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']." AS NAME_TYPE_PERIOD, 
							".$GLOBALS['PARAM']['ORDRE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']." AS ORDER_TYPE_PERIOD
							FROM ".$GLOBALS['PARAM']['TYPE_FILTRE']."
							WHERE ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']." <> 255;";
	
		$filters = $GLOBALS['conn']->GetAll($requete); 	
		
		$user_ident["filter"] = $GLOBALS['PARAM']['TYPE_FILTRE'];
		$user_ident["filters"] = $filters; 
	}
	
	
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$user_ident);
	echo json_encode($rps);//print_r($rps);
});

// test si l'utisateur est connu dans la base
$app->get('/leave/:login/:password', function ($login, $password) use ($lib_status, $lib_message, $lib_data, $status_ok) {	
	$status = $GLOBALS['PARAM_WS']['LOGIN_OK'];
	$user_ident = array();
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$user_ident);
	echo json_encode($rps);
});

// permet de changer le mot de passe de l'utilisateur
$app->get('/user_new_pwd/:login/:newpwd', function ($login, $newpwd) use ($lib_status, $lib_message, $lib_data, $status_ok) {
	$status = $GLOBALS['PARAM_WS']['LOGIN_OK'];	     
  $requete = "UPDATE ADMIN_USERS SET PASSWORD='".md5($newpwd)."' WHERE NOM_USER='".$login."'";
  $ok = $GLOBALS['conn_dico']->Execute($requete);
  $data = "";
  if (!$ok) {
      $status = $GLOBALS['PARAM_WS']['LOGIN_KO'];	  
      $data = $requete;  
  }
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$data);
	echo json_encode($rps);
});

// permet de tester si l'utilisateur est d j  connect 
$app->get('/user_test_login/', function () use ($lib_status, $lib_message, $lib_data, $status_ok) {
	$status = $GLOBALS['PARAM_WS']['LOGIN_OK'];	
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>"");
	echo json_encode($rps);
});

$app->run();
 
?>
