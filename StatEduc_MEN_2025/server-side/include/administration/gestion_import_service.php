<?php

header('Content-Type: application/json; charset=utf-8');
session_start();
set_time_limit(0);
ini_set("memory_limit", "512M"); 
$GLOBALS['lancer_theme_manager'] = true;         
require_once '../../../common.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/import.class.php';

$rootPath = $GLOBALS['SISED_PATH'];
$connexion_dico = $GLOBALS['conn_dico'];

$op = $_POST['op'];

if ($op == 'import_data') {
	$data_file = $_POST['data_file'];
	$log_filename = $_POST['log_filename'];  
	$idx = $_POST['idx'];   
	$import_instance	=	new import($data_file, false, $idx, true);     
	sendOk($idx, $import_instance->log_data);
}

function sendError($idx, $message) {
	$posts = array('se_statut'=>101, 'idx'=>$idx, 'se_message'=>$message,'se_datas'=>NULL);	
	echo json_encode($posts);
}

function sendOk($idx, $data) {
	$posts = array('se_statut'=>200,'se_message'=>'ok', 'idx'=>$idx, 'se_datas'=>$data);	
	echo json_encode($posts);
}
?>