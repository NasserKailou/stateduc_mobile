<?php
header('Content-Type: application/json; charset=utf-8');
//Vérification de la validité de la session.
session_start();
if (!isset($_SESSION['valide']) || !$_SESSION['valide']) {
	sendError('session_end');
	return;
}
$GLOBALS['lancer_theme_manager'] = true;
require_once '../../../../common.php';
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
require_once $GLOBALS['SISED_PATH_LIB'] . 'adodb_xml/class.ADODB_XML.php';
lit_libelles_page('/gestion_updates.php');

class FileRev {
	public $id = "";
    public $idRev = "";
    public $nomRevFile = "";
    public $pathRevFile = "";
	public $typeRev = "";
}
	
require $GLOBALS['SISED_PATH_LIB'] . 'Curl/Curl.php';

use \Curl\Curl;
$curl = new Curl();
$curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
$curl->setHeader('Accept', '*/*');
$curl->setHeader('Accept-Encoding', 'gzip,deflate');
$curl->setHeader('Accept-Language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
	
$lib_status =  'se_status';
$lib_message = 'se_message';
$lib_data = 'se_data';

$status_ok = 200;
$status_ko = 400;
//$rootPath = $GLOBALS['SISED_PATH'];
//$connexion_dico = $GLOBALS['conn_dico'];

$db = $GLOBALS['conn'];
$db_dico = $GLOBALS['conn_dico'];
$op = $_POST['Action'];
$_SESSION['code_regrpmt'] = $_POST['REG_ID'];

if ($op == 'listeRevs') {
	$requete = "SELECT ID_REVISION as id, NUM_REVISION as num, COMMENT_REVISION as comm, ACTIVER_REVISION as act, VALIDER_REVISION as val FROM REVISION ORDER BY ID_REVISION DESC";
	$revs_array = sql($requete, $db_dico);
	$revs_array = array_change_key_case_recursive($revs_array, CASE_LOWER);
	sendList($revs_array);
} else if ($op == 'listeFiles') {
	$revId = $_POST['REV_ID'];
	$revType = $_POST['TYPE_REV_FILE'];
	$requete = "SELECT NOM_REV_FILE FROM REVISION_FILES WHERE ID_REVISION=".$revId." AND CODE_TYPE_REVISION=".$revType;
	$liste_files = $db_dico->GetCol($requete);
	sendList($liste_files);
} else if ($op == 'AddRevision') {
	$revNum = $_POST['REV_NUM'];
	$revComment = $_POST['REV_COMMENT'];

	$revMax = sql("SELECT max(ID_REVISION) AS max FROM REVISION", $db_dico);
	$revMax = array_change_key_case_recursive($revMax, CASE_LOWER);
	$newId = $revMax[0]['max'] + 1;
	$sql = "INSERT INTO REVISION (ID_REVISION, NUM_REVISION, COMMENT_REVISION) VALUES ($newId, '$revNum', '$revComment')";
	
	if (sqlExec($sql, $db_dico)) {
		sendData('newId', array("id"=>$newId));
	} else {
		sendError("Ko");
	}
	
} else if ($op == 'UpdRevision') { 
	$revId = $_POST['REV_ID'];
	$revNum = $_POST['REV_NUM'];
	$revComment = $_POST['REV_COMMENT'];
	
	$sql = "UPDATE REVISION SET NUM_REVISION='$revNum', COMMENT_REVISION='$revComment' WHERE ID_REVISION=$revId";
	
	if (sqlExec($sql, $db_dico)) {
		sendData('revId', array("id"=>$revId));
	} else {
		sendError("Ko : ".$sql);
	}
	
} else if ($op == 'AddFile') {
	$revId = $_POST['REV_ID'];
	$revNum = $_POST['REV_NUM'];
	$revFile = $_POST['NOM_REV_FILE'];
	$revType = $_POST['TYPE_REV_FILE'];
	$fileExt = $_POST['FILE_EXT'];

	$exportDir = $GLOBALS['SISED_PATH']."server-side/import_export/".$revNum;
	if (!file_exists($exportDir)) {
		mkdir($exportDir, 0777, true);
	}
	if (!file_exists($exportDir."/".$revType)) {
		mkdir($exportDir."/".$revType, 0777, true);
	}
	$exportFile = $exportDir."/".$revType."/".$revFile.$fileExt;
	if ($revType == 2 || $revType == 3) {
		$req = "SELECT * FROM ".$revFile;
		$adodbXML = new ADODB_XML("1.0", "ISO-8859-1");	
		  
		if ($revType == 2) {			
			$all_champs_table = $db_dico->MetaColumns($revFile);
			$fields = array();
			foreach( $all_champs_table as $champ ) {
				$fields[] = $champ->name;
			}
			$adodbXML->ConvertToXML($db_dico, $req, $fields, $exportFile);		
		} else {			
			$all_champs_table = $db->MetaColumns($revFile);
			$fields = array();
			foreach( $all_champs_table as $champ ) {
				$fields[] = $champ->name;
			}
			$adodbXML->ConvertToXML($db, $req, $fields, $exportFile);
		}
	} else if ($revType == 4) {
		copy($GLOBALS['SISED_PATH']."questionnaire/".$_SESSION['langue']."/".$revFile, $exportFile);
	}
	$idMax = sql("SELECT max(ID_REV_FILE) AS max FROM REVISION_FILES", $db_dico);
	$idMax = array_change_key_case_recursive($idMax, CASE_LOWER);
	$newId = $idMax[0]['max'] + 1;
	$path = addslashes($revType."/".$revFile.$fileExt);
	$sql = "INSERT INTO REVISION_FILES (ID_REV_FILE, ID_REVISION, NOM_REV_FILE, PATH_REV_FILE, CODE_TYPE_REVISION) VALUES ($newId, $revId, '$revFile',  '$path', $revType)";
	
	if (sqlExec($sql, $db_dico)) {
		sendData('newId', array("id"=>$newId));
	} else {
		sendError("Ko : ".$sql);
	}
} else if ($op == 'DelFile') {
	$revId = $_POST['REV_ID'];
	$revNum = $_POST['REV_NUM'];
	$revFile = $_POST['NOM_REV_FILE'];
	$revType = $_POST['TYPE_REV_FILE'];
	$fileExt = $_POST['FILE_EXT'];
	
	unlink($GLOBALS['SISED_PATH']."server-side/import_export/".$revNum."/".$revType."/".$revFile.$fileExt);
	
	// Supprime les représentation
	$sql = "DELETE FROM REVISION_FILES WHERE ID_REVISION=$revId AND NOM_REV_FILE='$revFile' AND CODE_TYPE_REVISION=$revType";	
	if (sqlExec($sql, $db_dico)) {
		sendOk();
	} else {
		sendError("Ko: ".$sql);
	}
} else if ($op == 'createZip') {
	$revId = $_POST['REV_ID'];
	$revNum = $_POST['REV_NUM'];
	
	if (create_zip_full($GLOBALS['SISED_PATH']."server-side/import_export", $revNum, false)) {	
		$sql = "UPDATE REVISION SET VALIDER_REVISION=1 WHERE ID_REVISION=$revId";		
		if (sqlExec($sql, $db_dico)) {
			sendOk();
		} else {
			sendError("Ko : ".$sql);
		}
	} else {
		sendList("ko_zip");
	}
} else if ($op == 'delZip') {
	$revId = $_POST['REV_ID'];
	$revNum = $_POST['REV_NUM'];
	unlink($GLOBALS['SISED_PATH']."server-side/import_export/".$revNum.".zip");	
	
	$sql = "UPDATE REVISION SET VALIDER_REVISION=0 WHERE ID_REVISION=$revId";
	
	if (sqlExec($sql, $db_dico)) {
		sendOk();
	} else {
		sendError("Ko : ".$sql);
	}
} else if ($op == 'DelRev') {
	$revId = $_POST['REV_ID'];
	$revNum = $_POST['REV_NUM'];
	
	// delete directories
	$dir = $GLOBALS['SISED_PATH']."server-side/import_export/".$revNum;
	if (is_dir($dir)) {
		$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it,
					 RecursiveIteratorIterator::CHILD_FIRST);
		foreach($files as $file) {
			if ($file->isDir()){
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}
		rmdir($dir);
	}
	// delete zip file
	unlink($GLOBALS['SISED_PATH']."server-side/import_export/".$revNum.".zip");
	
	// Supprime les fichiers de la révision et la révision
	$sql = "DELETE FROM REVISION_FILES WHERE ID_REVISION=$revId";	
	if (sqlExec($sql, $db_dico)) {
		$sql = "DELETE FROM REVISION WHERE ID_REVISION=$revId";	
		if (sqlExec($sql, $db_dico)) {
			sendOk();
		} else {
			sendError("Ko: ".$sql);
		}
	} else {
		sendError("Ko: ".$sql);
	}
} else if ($op == 'activateRev') {
	$revId = $_POST['REV_ID'];
	$val = $_POST['ACT_VAL'];
	
	$sql = "UPDATE REVISION SET ACTIVER_REVISION=$val WHERE ID_REVISION=$revId";
	
	if (sqlExec($sql, $db_dico)) {
		sendOk();
	} else {
		sendError("Ko : ".$sql);
	}
} else if ($op == 'sdsdsdsd') {
}

/**
* Décrompression du fichier compressé contenant les données à importer
* @access public
* @param stirng fichier_zip chemin complet du fichier à décompresser
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

function save_xml_data($adodbXML, $xmlFilesDir, $baseName, $xmlFiles) {
	$strRequete = "SELECT * FROM DICO_TABLE_ORDRE ORDER BY ORDRE";
	$rsTables=$GLOBALS['conn_dico']->Execute($strRequete);
	if ($rsTables->RecordCount()>0) {
		while (!$rsTables->EOF) {
			$currTable = $rsTables->fields['NOM_TABLE']; //echo "\n\nTABLE : ".$currTable."\n";
			foreach($xmlFiles as $i => $file) { 
				if( strpos($file['filename'], "/".$currTable.".xml") !== FALSE){ //echo "FILE : ".$file['filename']."\n";
					$adodbXML->InsertIntoDB($GLOBALS['conn'], $xmlFilesDir.'/'.$baseName."/".$file['filename'], $currTable);
				}
			}
			$rsTables->MoveNext();
		}
	}
}
		
function sql($requete, $conn) {
	try {
		$result_array = $conn->GetAll($requete);
		return $result_array;
	}
	catch(Exception $e) {
		sendError($e->getMessage());
	}
}

function sqlExec($sql, $conn) {
	try {
		$ok = $conn->Execute($sql);
		return $ok;
	}
	catch(Exception $e) {
		sendError($e->getMessage()." : ".$sql);
	}
}

function sendData($type, $data) {
	$posts = array('se_statut'=>200,'se_message'=>'ok','se_type'=>$type,'se_data'=>$data);	//echo "<pre>"; print_r($posts);
	echo json_encode($posts);
}

function sendList($liste) {
	$posts = array('se_statut'=>200,'se_message'=>'ok','se_data'=>$liste);	
	echo json_encode($posts);
}

function sendError($message) {
	$posts = array('se_statut'=>101,'se_message'=>$message,'se_data'=>NULL);	
	echo json_encode($posts);
}

function sendOk($msg="ok") {
	$posts = array('se_statut'=>200,'se_message'=>$msg,'se_data'=>'ok');	
	echo json_encode($posts);
}
?>