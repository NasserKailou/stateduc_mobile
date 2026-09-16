<?php 
	lit_libelles_page('/import.php');
	set_time_limit(0);
	ini_set("memory_limit", "64M");
	include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
	include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/import_teacher.class.php'; 	 
    if(isset($_GET['type_theme']) && trim($_GET['type_theme'])<>''){
    	$typ_thm =	$_GET['type_theme'];
	}else{
		$typ_thm = '';
	}
	if(isset($_GET['code_etab']) && trim($_GET['code_etab'])<>''){
    	$code_etab 	=	$_GET['code_etab'];
	}else{
		$code_etab = '';
	}
	if(isset($_GET['teach_rec_file']) && trim($_GET['teach_rec_file'])<>''){
    	$chemin_zip =	$_GET['teach_rec_file'];
	}else{
		$chemin_zip = '';
	}
    if(isset($_GET['log_file']) && trim($_GET['log_file'])<>''){
        $log_file = $_GET['log_file'];
    }else{
        $log_file ='';
    }
	if($chemin_zip<>'' && $code_etab<>''){
		$import_instance	=	new import($typ_thm, $code_etab, $chemin_zip, $log_file);
	}else{
		echo "<script language='JavaScript' type='text/javascript'>\n";
		echo "alert(\"".recherche_libelle_page('sel_school_file')."\");\n";
		echo "fermer();\n";
		echo "</script>\n";
	}
?>
