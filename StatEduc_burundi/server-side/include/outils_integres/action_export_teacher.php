<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php
		set_time_limit(0);
		ini_set("memory_limit", "64M");
		//require_once 'common.php';
		include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/export_teacher.class.php'; 
				
		$export	= new export() ;
		
		$chemin_output = $SISED_PATH.'server-side/import_export/';
		$export->chemin_output	=	$chemin_output ;
		
		$requete = "SELECT ".$GLOBALS['PARAM']['PRENOMS_ENSEIGNANT'].", ".$GLOBALS['PARAM']['NOM_ENSEIGNANT'].
					" FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].
					" WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$_SESSION['id_teacher'];
		$result = $GLOBALS['conn']->GetAll($requete);
		
		$_SESSION['teacher_name'] = $result[0][$GLOBALS['PARAM']['PRENOMS_ENSEIGNANT']]."_".$result[0][$GLOBALS['PARAM']['NOM_ENSEIGNANT']];
		$export->fichier_zip	=	$_SESSION['teacher_name'] ;

		$export->export_xml();
		
?>
