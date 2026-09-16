<?php 
//header('Content-Type: text/html'); 
include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_zone.class.php'; 	
?> 
<style type="text/css" media="all">
.cachediv {
		 visibility: hidden;
		 overflow: hidden;
		 height: 1px;
		 margin-top: -500px;
		 position: absolute;
}
.montrediv {
		 visibility: visible;
		 overflow: visible;
}
</style>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>

<?php
	$GestZone = new gestion_zone() ;
	$GestZone->init();

	if(isset($_GET['ret']) && $_GET['ret']<>''){
		echo "<script language=\"JavaScript\">
			MiseEvidenceZones('".$_GET['itab']."','".$_GET['izone']."','".$_GET['nb_tabm']."')
			</script>";
	}

?>

