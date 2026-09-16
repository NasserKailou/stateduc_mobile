<?php
header('Content-Type: application/json; charset=utf-8');
//Vérification de la validité de la session.
session_start();
if (!isset($_SESSION['valide']) || !$_SESSION['valide']) {
//	sendError('session_end');
//	return;
}
require_once '../../../common.php';

lit_libelles_page('/gestion_zone.php');

//$rootPath = $GLOBALS['SISED_PATH'];
//$connexion_dico = $GLOBALS['conn_dico'];
if (isset($_GET['ActionZone']) || isset($_GET['ActionZoneStatut']) || isset($_GET['ActionNewTabM'])) {
	$_POST = $_GET;
}
$op = ""; 
if (isset($_POST['ActionZoneStatut'])) {
	$op = $_POST['ActionZoneStatut'];
} else if (isset($_POST['ActionNewTabM'])) {
	$op = $_POST['ActionNewTabM'];
} else {
	if (isset($_POST['ActionZone'])) {
		$op = $_POST['ActionZone'];
	} else if (isset($_POST['ActionTabM'])) {
		$op = $_POST['ActionTabM'];
	}	
	include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_zone.class.php';	
	$GestZone = new gestion_zone() ;
	$GestZone->initAjax();
}

if ($op == 'AddZoneDetails') {
	$iTab = $_POST['TabMActive'];
	$iZone = $_POST['ZoneActive'];
	//session_start();	
	$GestZone->set_champs_zone_systeme();
	$GestZone->get_systemes(NULL);
	sendData('details', $GestZone->AffZoneDetails($iTab, $iZone));
} else if ($op == 'NewZone') {
	$GestZone -> gererPost();
	$chpPere = $_POST['CHAMP_PERE'];
	$prefixTab = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_';
	$tableFille = substr($chpPere, strlen($GLOBALS['PARAM']['CODE'].'_'));
	if ($chpPere != '' && 
		($_POST['TABLE_FILLE'] != '') &&
		!in_array($chpPere, $GLOBALS['PARAM_SYS']['CLES_SIMPLES']) &&
		in_array($_POST['TYPE_OBJET'], $GLOBALS['PARAM_SYS']['ZONES_LISTE']) &&
		(strpos($chpPere, $prefixTab) === 0)) {	
		//TABLE FILLE
		$tableFille	 = $_POST['TABLE_FILLE'];
		//CHAMP FILS
		$list_champs = $GLOBALS['conn']->MetaColumnNames($tableFille);	
		$chpFils = $GLOBALS['PARAM']['CODE'].'_'.$tableFille;
		$chpFils = in_array($chpFils, $list_champs)?$chpFils:'';
		$_POST['CHAMP_FILS'] = $chpFils;
		//SQL
		$val_sql = '';
		if (in_array(strtoupper($tableFille.'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES')))) {                    
			$lien_systeme  =  ','.$tableFille.'_'.$GLOBALS['PARAM']['SYSTEME']."\n".'WHERE '.
					$tableFille.'.'.$GLOBALS['PARAM']['CODE']."_".$tableFille.' = '.
					$tableFille.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE']."_".$tableFille.
					"\n AND ".$tableFille.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = $'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
		}       
		else {
			$lien_systeme  =  '';
		} 
		//$result .= '<br>lien systeme ='.$lien_systeme ;
		if (isset($tableFille) && (trim($tableFille) <> '')) {
			$alias = '';
			if(strtoupper($chpPere) != strtoupper($GLOBALS['PARAM']['CODE']."_".$tableFille)) {
				$alias = " AS ".$chpPere;
			}
			if($lien_systeme!='') {
				$prefix=$tableFille.'.';
			}	
			else {
				$prefix='';
			}	
			$val_sql = "SELECT ".$prefix.$GLOBALS['PARAM']['CODE']."_".$tableFille.$alias. "\n".
						"FROM ". $tableFille. $lien_systeme."\n".
						"ORDER BY ".$prefix.$GLOBALS['PARAM']['ORDRE']."_".$tableFille;			
		}
		$_POST['SQL_REQ'] = $val_sql;
	}
	$_POST['ActionZone'] = 'AddZone';
	sendData('insertDetails', $GestZone -> gererPost());	
} else if ($op == 'UpdZone') {
	if (is_object($GestZone)) {		
		sendList($GestZone -> gererPost());
	} else {
		sendError(utf8_encode(recherche_libelle_page('ErreurUpdZone')));
	}
} else if ($op == 'SupZone') {
	$GestZone -> gererPost();
	sendOk();
} else if ($op == 'UpdZoneStatus') {
	$idZone = $_POST['ID_ZONE'];
	$statut = $_POST['ZONE_STATUT'];
	$req = "UPDATE DICO_ZONE SET ZONE_STATUT='".$statut."' WHERE ID_ZONE=".$idZone;
	if ($GLOBALS['conn_dico']->Execute($req) === false) {
		sendError(utf8_encode(recherche_libelle_page('ErreurUpdZoneStatus')));
	} else {
		sendOk();
	}
} else if ($op == 'AddTabM') {
	sendData('insertTabM', $GestZone -> gererPost());
} else if ($op == 'SupTabM') {
	$GestZone -> gererPost();
	sendOk();
} else if ($op == 'UpdTabM') {
	$GestZone -> gererPost();
	sendOk();
} else if ($op == 'ValidNewTabMData') {
	$tableMere = $_POST['tableMere'];
	$priorite = $_POST['priorite'];
	$idTheme = $_POST['idTheme'];
	
	$_SESSION['GestZones']['type_int'] = array('-6','4','5','7','8','I','N','bigint','bit','decimal','float','float4','float8','int','int2','int4','int8','numeric','smallint','tinyint','real');
	$_SESSION['GestZones']['type_text'] = array('12','C','char','nchar','ntext','nvarchar','text','varchar');
	$_SESSION['GestZones']['type_date'] = array('11','D','T','datetime','smalldatetime');
		
	if (!preg_match ("#^(0|([1-9][0-9]*))$#", trim($priorite))) {
		sendData("insertTableM", utf8_encode(recherche_libelle_page('NewTabErrorPrior')));
		return;
	}
	if ( trim($tableMere == '') ) {
		sendData("insertTableM", utf8_encode(recherche_libelle_page('NewTabErrorTabMNull')));
		return;
	}
	// on verifie l'existence de la table mère 
	$req_exist = 'SELECT * FROM DICO_TABLE_MERE_THEME
							 WHERE ID_THEME = '.$idTheme.'
							 AND ( NOM_TABLE_MERE = \''.trim($tableMere).'\')';
	
	$exists = $GLOBALS['conn_dico']->GetAll($req_exist);
	if ( is_array($exists) and (count($exists) > 0) ) {
		sendData("insertTableM", utf8_encode(recherche_libelle_page('NewTabErrorTabMExist')));
		return;
	}
	// on verifie l'existence de la priorité 
	$req_exist = 'SELECT * FROM DICO_TABLE_MERE_THEME
							 WHERE ID_THEME = '.$idTheme.'
							 AND ( PRIORITE = '.trim($priorite).')';
	
	$exists = $GLOBALS['conn_dico']->GetAll($req_exist);
	if ( is_array($exists) and (count($exists) > 0) ) {
		sendData("insertTableM", utf8_encode(recherche_libelle_page('NewTabErrorPriorExist')));
		return;
	}
	sendData("insertTableM", "NewTabOk");
}

function sql($requete, $connexion_dico) {
	try {
		$result_array = $connexion_dico->GetAll($requete);
		return $result_array;
	}
	catch(Exception $e) {
		sendError($e->getMessage());
	}
}

function sendData($type, $data) {
	$posts = array('se_statut'=>200,'se_message'=>'ok','se_type'=>$type,'se_data'=>$data);	
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

function sendOk() {
	$posts = array('se_statut'=>200,'se_message'=>'ok','se_data'=>'ok');	
	echo json_encode($posts);
}
?>