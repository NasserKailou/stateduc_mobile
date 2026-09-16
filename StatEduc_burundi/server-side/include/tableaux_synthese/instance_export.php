<?php 
	//header("Content-Type: application/vnd.ms-excel");
	header("Content-Type: application/vnd.ms-excel; name=synthese.xls");
	header("Content-Disposition: attachment; filename=synthese.xls");
	echo $_SESSION["htmltable"];
?>
