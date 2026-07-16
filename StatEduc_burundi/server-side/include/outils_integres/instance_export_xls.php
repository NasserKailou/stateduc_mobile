<?php
	/*header("Content-Type: application/vnd.ms-excel");*/
	header("Content-Type: application/vnd.ms-excel; name=grille.xls");
	header("Content-Disposition: attachment; filename=grille.xls");
    echo $_SESSION["table_html"];
?>
