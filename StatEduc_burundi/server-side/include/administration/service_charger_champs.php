<?php 
header('Content-type: application/json');

$result = "";
if(isset($_GET["type_theme"]) && (trim($_GET["type_theme"]) == 0)) {
	if(isset($_GET["type_chp"])) {
		$requete	= "	SELECT  TYPE_DONNEES, CODE_TYPE_DONNEES, LIBELLE_TYPE_DONNEES   
						FROM   DICO_TYPE_DONNEES WHERE CODE_TYPE_DONNEES IN (1,2,3)";
		$type_donnees_chp	= $GLOBALS['conn_dico']->GetAll($requete);
		$result = "<select name='TYPE_ZONE_BASE' id='TYPE_ZONE_BASE'>";
		$result .= '<option value=\'\'></option>';			
		foreach ($type_donnees_chp as $i => $type_donnees){
			$result .= "<option value='".trim($type_donnees['TYPE_DONNEES'])."'";
			
			$type_donnee=array();
			if (trim($type_donnees['TYPE_DONNEES'])=='int') { 
				$type_donnee = array('-6','4','5','7','8','I','N','bigint','bit','decimal','float','float4','float8','int','int2','int4','int8','numeric','smallint','tinyint','real'); 
			} 
			elseif(trim($type_donnees['TYPE_DONNEES'])=='text') {
				$type_donnee = array('12','C','char','nchar','ntext','nvarchar','text','varchar');
			} 
			elseif(trim($type_donnees['TYPE_DONNEES'])=='date') {
				$type_donnee = array('11','D','T','datetime','smalldatetime');
			} 
			if (in_array(trim($_GET['id_chp']),$type_donnee)) {
				$result .= " selected='selected'";
			}
			elseif (trim($type_donnees['TYPE_DONNEES']) == trim($_GET['type_chp']) && trim($_GET['champ']) <> '' ) {
				$result .= " selected='selected'";
			}
			$result .= ">".get_libelle($type_donnees['CODE_TYPE_DONNEES'],'DICO_TYPE_DONNEES',$type_donnees['LIBELLE_TYPE_DONNEES'])."</option>";
		}		
		$result .= "</select>";
	} elseif (isset($_GET["chp_fils"])) {
		if (isset($_GET["tabM_insert"]) && ($_GET["tabM_insert"]==1)) {	
			if (isset($_GET["table"]) && (trim($_GET["table"]) <> '')) {
				$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);	
				$chpFils = $GLOBALS['PARAM']['CODE'].'_'.$_GET["table"];
				$result = in_array($chpFils, $list_champs)?$chpFils:'';
			}
		} else {
			$result = "<select name='CHAMP_FILS' id='CHAMP_FILS'>";
			if (isset($_GET["table"]) && (trim($_GET["table"]) <> '')) {
				$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);	
				//$result .= '<option value=\'\'></option>';
				foreach ($list_champs as $i => $champ){
					$result .= "<option value='".$champ."'";
					if ($_GET['chp_fils'] == $champ){
						$result .= " selected='selected'";
					}
					elseif($champ==$GLOBALS['PARAM']['CODE'].'_'.$_GET["table"]) {
						$result .= " selected='selected'";
					}
					$result .= ">".$champ."</option>\n";
				}
			} else {
				$result .= '<option value=\'\'></option>';
			}
			$result .= "</select>";	
		}	
	}		
	elseif (isset($_GET["chp_sql"])) {
		$val_sql = '';
		$result = "<textarea name='SQL_REQ' style='width:370px'  cols='68' rows='8'>";
		if (in_array(strtoupper($_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES')))) {                    
			$lien_systeme  =  ','.$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']."\n".'WHERE '.
					$_GET['table'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].' = '.
					$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].
					"\n AND ".$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = $'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
		}       
		else {
			$lien_systeme  =  '';
		} 
		//$result .= '<br>lien systeme ='.$lien_systeme ;
		if (isset($_GET["table"]) && (trim($_GET["table"]) <> '')) {
			if(trim($_GET["chp_sql"])<>'' &&  strtoupper(trim($_GET["val_table"]))==strtoupper(trim($_GET["table"]))) {
				$val_sql = urldecode($_GET["chp_sql"]);
			}
			else {
				$alias = '';
				if(isset($_GET["chp_pere"]) && $_GET["chp_pere"] <> '') {
					if(strtoupper($_GET["chp_pere"]) != strtoupper($GLOBALS['PARAM']['CODE']."_".$_GET['table'])) {
						$alias = " AS ".$_GET["chp_pere"];
					}
				}
				if($lien_systeme!='') {
					$prefix=$_GET['table'].'.';
				}	
				else {
					$prefix='';
				}	
				$val_sql = "SELECT ".$prefix.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].$alias. "\n".
							"FROM ". $_GET['table']. $lien_systeme."\n".
							"ORDER BY ".$prefix.$GLOBALS['PARAM']['ORDRE']."_".$_GET['table'];								
			}			
			$result .= $val_sql;
		}
		$result .= "</textarea>";	
		if (isset($_GET["tabM_insert"]) && ($_GET["tabM_insert"]==1)) {	
			$result = $val_sql;
		}
	}
}
elseif(isset($_GET["type_theme"]) && (trim($_GET["type_theme"]) == 1))
{	
	if(isset($_GET["mes1"]))
	{
		$result = "<select name='MESURE1' id='MESURE1'>";
		if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
			$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
			$result .= '<option value=\'\'></option>';
			foreach ($list_champs as $i => $champ){
				$result .= "<option value='".$champ."'";
				if ($_GET['mes1'] == $champ){
					$result .= " selected";
				}
				$result .= ">".$champ."</option>\n";	
			}
		}else{
			$result .= '<option value=\'\'></option>';
		}
		$result .= "</select>";
	}
	elseif(isset($_GET["mes2"]))
	{
		$result = "<select name='MESURE2' id='MESURE2'>";
		if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
			$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
			$result .= '<option value=\'\'></option>';
			foreach ($list_champs as $i => $champ){
				$result .= "<option value='".$champ."'";
				if ($_GET['mes2'] == $champ){
					$result .= " selected";
				}
				$result .= ">".$champ."</option>\n";	
			}
		}else{
			$result .= '<option value=\'\'></option>';
		}
		$result .= "</select>";
	}
	elseif(isset($_GET["chp_dim"]))
	{
		$result = "<select name='CHAMP' id='CHAMP'>";
		if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
			$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
			foreach ($list_champs as $i => $champ){
				$result .= "<option value='".$champ."'";
				if ($_GET['chp_dim'] == $champ){
					$result .= " selected";
				}
				elseif($champ==$GLOBALS['PARAM']['CODE'].'_'.$_GET["table"])
				{
					$result .= " selected";
				}
				$result .= ">".$champ."</option>\n";	
			
			}
		}else{
			$result .= '<option value=\'\'></option>';
		}
		$result .= "</select>";
	}
	elseif(isset($_GET["lib_dim"]))
	{
		$result = "<select name='DIM_LIBELLE' id='DIM_LIBELLE'>";
		if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
			$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
			foreach ($list_champs as $i => $champ){
				$result .= "<option value='".$champ."'";
				if ($_GET['lib_dim'] == $champ){
					$result .= " selected";
				}
				elseif($champ==$GLOBALS['PARAM']['LIBELLE'].'_'.$_GET["table"])
				{
					$result .= " selected";
				}
				$result .= ">".$champ."</option>\n";						
			}
		}else{
			$result .= '<option value=\'\'></option>';
		}
		$result .= "</select>";
	}
	elseif(isset($_GET["chp_sql_dim"]))
	{
		$result = "<textarea name='DIM_SQL' cols='50' rows='10'>";
		if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
			if (in_array(strtoupper($_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'))))
					$lien_systeme  =  ','.$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']."\n".'WHERE '.
						$_GET['table'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].' = '.
						$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].
						"\n AND ".$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = $'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
					
			else
				$lien_systeme  =   ''; 
			if(trim($_GET["chp_sql_dim"])<>'' &&  strtoupper(trim($_GET["val_dim_table"]))==strtoupper(trim($_GET["table"])))
			{
				$val_sql = urldecode($_GET["chp_sql_dim"]);
			}
			else
			{
				if($lien_systeme!='')
					$prefix=$_GET['table'].'.';
				else
					$prefix='';
				$val_sql = "SELECT ".$prefix.$GLOBALS['PARAM']['CODE']."_".$_GET['table']. "\n".
							"FROM ". $_GET['table'].$lien_systeme . "\n".
							"ORDER BY ".$prefix.$GLOBALS['PARAM']['ORDRE']."_".$_GET['table'];
			}			
			$result .= $val_sql;
		}
		$result .= "</textarea>";	
	}
}
elseif(isset($_GET["plage_codes"]) && (trim($_GET["plage_codes"]) == 1))
{
	$result = "<select name='NOM_CHAMP' id='NOM_CHAMP'>";
	if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
		$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
		//$result .= '<option value=\'\'></option>';
		foreach ($list_champs as $i => $champ){
			$result .= "<option value='".$champ."'";
			if ($_GET['chp_fils'] == $champ){
				$result .= " selected";
			}
			$result .= ">".$champ."</option>\n";	
		}
	}else{
		$result .= '<option value=\'\'></option>';
	}
	$result .= "</select>";
}
sendList($result);

function sendData($type, $data) {
	$posts = array('se_statut'=>200,'se_message'=>'ok','se_type'=>$type,'se_data'=>$data);	
	$result .= json_encode($posts);
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
 

