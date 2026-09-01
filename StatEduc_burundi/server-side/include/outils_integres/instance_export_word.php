<?php 
    /*header('Content-Type: application/word');
	header('content-disposition: attachement;filename=diagnostic.doc');*/
    header("Content-Type: application/vnd.ms-word; name=diagnostic.doc");
	header("Content-Disposition: attachment; filename=diagnostic.doc");
	echo $_SESSION["table_html"];
?>
