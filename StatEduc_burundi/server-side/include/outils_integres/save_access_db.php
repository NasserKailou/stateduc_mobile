<?php include_once($GLOBALS['SISED_PATH_LIB'].'pclzip.lib.php');
	
	set_time_limit(0);
	
	debut_popup_progress();
	
	$chemin_output = $GLOBALS['SISED_PATH'].'server-side/db/';
	
	$ch_extr 	= explode('Dbq=', $GLOBALS['conn']->host);
	$ch_extr 	= explode(';', $ch_extr[1]);
	$db_path 	= strtolower($ch_extr[0]);
	
	$ch_extr 	= explode('/', $db_path);
	if(strpos($ch_extr[(count($ch_extr)-1)], ".mdb") !== true)
		$db_name 	= str_replace('.mdb', '', $ch_extr[(count($ch_extr)-1)]);
	elseif(strpos($ch_extr[(count($ch_extr)-1)], ".accdb") !== true)
		$db_name 	= str_replace('.accdb', '', $ch_extr[(count($ch_extr)-1)]);
	
	$zip_db_name = $db_name . '.zip' ;
	
	if(strpos($db_path, $db_name . '.mdb') !== true)
		$path_remove = str_replace($db_name . '.mdb', '', $db_path);
	elseif(strpos($db_path, $db_name . '.accdb') !== true)
		$path_remove = str_replace($db_name . '.accdb', '', $db_path);
	
	$zip_db_path = $chemin_output . $zip_db_name ;
	
	if(file_exists($zip_db_path)){
		unlink($zip_db_path);
	}
	
	$zip 		= new PclZip($zip_db_path);
	
	$list_zip	= array();
	$list_zip[] = $db_path;
	//$list_zip[] = $GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb';
	//die($db_path.'/'.$chemin_output);
	$res = $zip->create( $list_zip , PCLZIP_OPT_REMOVE_PATH, $path_remove);
	if ($res == 0) {
		print("Error : ".$zip->errorInfo(true));
	}
	
	ouvrir_popup($GLOBALS['SISED_AURL'].'server-side/db/'.$zip_db_name, 'down');            
	
	fin_popup_progress();
?>

