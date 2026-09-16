<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<script type="text/Javascript">
	function fermer() {
		window.close();
	}
</script>
<?php 
	set_time_limit(0);
	ini_set("memory_limit", "512M");
	include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
	include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/import.class.php';
	$chemin_zip 			=	$_SESSION['config_import']['rep_source'];
	if (isset($_SESSION['config_import']['IMPORT_ALL']))
		$nomencl = true;
	else
		$nomencl  = false; 
	if (isset($_SESSION['config_import']['log_file'])){
		$log_file = $_SESSION['config_import']['log_file'];
	}else{
		$log_file ='';
	}
	$import_instance	=	new import($chemin_zip, $nomencl,$log_file);
?>
