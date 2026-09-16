<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/quickreport.class.php';         
		set_time_limit(0);
         //$creport = new quickreport(3,  $_SESSION['secteur']);
         //print_r($_GET);
         $creport = new quickreport($_GET["id_rpt"],  $_GET["id_sys"]);
         $creport->preview_report();

?>

