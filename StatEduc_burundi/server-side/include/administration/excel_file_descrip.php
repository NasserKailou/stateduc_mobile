<?php 
include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/excel_file_descrip.class.php';
?> 

<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>

<?php 
	$GestZone = new excel_file_descrip() ;
	$GestZone->init();
?>

