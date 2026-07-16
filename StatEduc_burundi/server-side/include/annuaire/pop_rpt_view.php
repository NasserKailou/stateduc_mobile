<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/report_user.class.php'; 
	//echo'<pre>';
	//print_r($GLOBALS['conn_dico']);
	$req_fonc_rpt	= ' SELECT DICO_REPORT_PARAM.RPT_FILE_NAME, DICO_REPORT_PARAM.TEMP_TABLE_NAME
						FROM DICO_REPORT_PARAM 
						WHERE DICO_REPORT_PARAM.ID = '.$_GET['id_rpt'].'
						AND DICO_REPORT_PARAM.ID_SYSTEME ='.$_GET['id_syst'].';';
	//echo '<br>'.$req_ss_menu;
	$rpt_params = $GLOBALS['conn_dico']->GetRow($req_fonc_rpt);
	
	if( is_array($rpt_params) && (count($rpt_params) > 0) ){
		$GLOBALS['conn_temp']	=	$GLOBALS['conn_dico'];
		$rpt_view = new report_user($rpt_params['RPT_FILE_NAME'], $rpt_params['TEMP_TABLE_NAME'], $GLOBALS['conn_temp']);
		$rpt_view->exprot_rpt_pdf();
		$rpt_view->preview_report();
	}else{
		echo ' <br> No Configuration For this Report <br>';
	}

?>
