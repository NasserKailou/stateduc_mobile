<?php 
include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/aggregated_db_structure.class.php'; 	
?> 

<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>

<?php $GestZone = new aggregated_db_structure() ;
		set_time_limit(0);
		ini_set("memory_limit", "64M");
		$GestZone->init();			
?>

