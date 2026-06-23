<script language="JavaScript1.2" type="text/javascript">
    function OpenPopupInstanceRpt(str_html) {
        var popup = window.open("test_export.php?data="+str_html,"popup",'toolbar=yes,location=no,directories=no,menubar=yes,scrollbars=yes,status=no,resizable=1,width=600, height=370, left=200, top=150')		
    }

    function recharger() {
		location.href   = 'synthese.php?val=OpenPopupExportXls';
	}
    
    function OpenPopupExportXls() {
		var	popup	=	window.open('synthese.php?val=videForXls','PopupExportXls', 'toolbar=yes,location=yes,directories=no,menubar=yes,scrollbars=yes,status=yes,resizable=1,width=600, height=370, left=200, top=150')
        popup.document.close();
        var chaine_eval = "popup.window.location.href   = 'synthese.php?val=OpenPopupExportXls';";
        eval(chaine_eval);
        popup.focus();
    }		
</script>
<?php // Inclusion de la classe executioncript pour la mise jour eventuelle de la structure de données
	include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/create_query.class.php'; 
    include_once $GLOBALS['SISED_PATH_CLS'].'metier/executionscript.class.php';         

    // Inclusion de la classe quickreport pour la génération des reports
    include_once $GLOBALS['SISED_PATH_CLS'].'metier/quickreport.class.php';         

    set_time_limit(0);
    $parametres = $_SESSION['params'];
    
	//Ajout Hebie pr affichage de l'ensemble des reports fils d'un report pere selectioné
	$req_rpt_Pe = ' SELECT  	DICO_REPORT_SYSTEME.ID, DICO_REPORT.ID_TYPE_REPORT, DICO_REPORT_SYSTEME.TITRE_ETAT
					FROM 		DICO_REPORT, DICO_REPORT_SYSTEME
					WHERE 		DICO_REPORT_SYSTEME.ACTIVER=1
					AND			DICO_REPORT.ID = DICO_REPORT_SYSTEME.ID
					AND 		DICO_REPORT_SYSTEME.ID_SYSTEME ='.$parametres['ID_SYSTEME'].'
					AND 		DICO_REPORT_SYSTEME.PERE = 0
					ORDER BY 	DICO_REPORT_SYSTEME.ORDRE_AFFICHAGE_MENU ;';
	$list_rpt_Pe	= $GLOBALS['conn_dico']->GetAll($req_rpt_Pe); 
	$tab_list_rpt_Pe = array();
	foreach($list_rpt_Pe as $rpt){
		$tab_list_rpt_Pe[] = $rpt['ID'];
	}
	$list_rpt_fi = array();
	if(in_array($parametres["ID_REPORT"],$tab_list_rpt_Pe)){
		$req_rpt_fi = ' SELECT  	DICO_REPORT_SYSTEME.ID, DICO_REPORT.ID_TYPE_REPORT, DICO_REPORT_SYSTEME.TITRE_ETAT
						FROM 		DICO_REPORT, DICO_REPORT_SYSTEME
						WHERE 		DICO_REPORT_SYSTEME.ACTIVER=1
						AND			DICO_REPORT.ID = DICO_REPORT_SYSTEME.ID
						AND 		DICO_REPORT_SYSTEME.PERE = '.$parametres["ID_REPORT"].$parametres['ID_SYSTEME'].'
						ORDER BY 	DICO_REPORT_SYSTEME.ORDRE_AFFICHAGE_MENU ;';
		$list_rpt_fi = $GLOBALS['conn_dico']->GetAll($req_rpt_fi);
	}
	
	$_SESSION["htmltable"] = '';
	if(count($list_rpt_fi)>0){
		$_SESSION["htmltable"] .= '<b>'.$parametres["TITRE_USER_RPT"].'</b>';
		foreach($list_rpt_fi as $rpt_fils){
			$parametres["ID_REPORT"] = $rpt_fils['ID'];
			$parametres["TITRE_USER_RPT"] = $rpt_fils['TITRE_ETAT'];
			$requete                = ' SELECT DICO_CRITERE_FILTRE_REPORT.*, DICO_CRITERE_FILTRE.* 
										FROM DICO_CRITERE_FILTRE, DICO_CRITERE_FILTRE_REPORT
										WHERE DICO_CRITERE_FILTRE.ID_CRITERE = DICO_CRITERE_FILTRE_REPORT.ID_CRITERE
										AND DICO_CRITERE_FILTRE_REPORT.ID = ' . $rpt_fils['ID'] . '
										AND DICO_CRITERE_FILTRE_REPORT.ACTIVER_CRITERE = 1 ;';
			$all_crit_rpt = $GLOBALS['conn_dico']->GetAll($requete);
			foreach( $all_crit_rpt as $i_crit => $crit ){
				$parametres["CRITERES_NOM"][$i_crit]['CHAMP_TABLE_DONNEES'] = $crit['CHAMP_TABLE_DONNEES'];
				$parametres["CRITERES_NOM"][$i_crit]['NOM_TABLE_DONNEES'] = $crit['NOM_TABLE_DONNEES'];
				$parametres["CRITERES_NOM"][$i_crit]['OPERATEUR_REQUETE'] = $crit['OPERATEUR_REQUETE'];
				$parametres["CRITERES_NOM"][$i_crit]['LIBELLE'] = $crit['LIBELLE'];
				$parametres["CRITERES_NOM"][$i_crit]['CHAMP_REF'] = $crit['CHAMP_REF'];
				$parametres["CRITERES_NOM"][$i_crit]['TABLE_REF'] = $crit['TABLE_REF'];
			}
			// Maj de la source de données avant le traitement de la production du report
			$ExecScript = new executionscript($parametres );
			$creport = new quickreport($parametres,$nom_fichier);
			$_SESSION["htmltable"] .= '<br><br>'.$creport->get_titre().'<br><br>'.$creport->get_entetehtml().'<br>'.$creport->get_html();
		}
	//Fin ajout Hebie pr affichage de l'ensemble des reports fils d'un report pere selectioné
	}else{
		// Maj de la source de données avant le traitement de la production du report
		$ExecScript = new executionscript($parametres );
		$creport = new quickreport($parametres,$nom_fichier);
		$_SESSION["htmltable"] = $creport->get_titre().'<br><br>'.$creport->get_entetehtml().'<br>'.$creport->get_html();
	}
	
	echo '<script language="JavaScript1.2" type="text/javascript">';
    //echo 'recharger()';
    echo "\nOpenPopupExportXls()\n";
    echo '</script>';
?>

