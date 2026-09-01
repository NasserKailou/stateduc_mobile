<?php
	/*header('Content-Type: application/word');
	header('content-disposition: inline;filename=controle_batch_data.doc');*/
    header("Content-Type: application/vnd.ms-word; name=controle_batch_data.doc");
	header("Content-Disposition: attachment; filename=controle_batch_data.doc");
	echo $_SESSION['table_controle'];
?>
