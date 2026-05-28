<?php //on lance le theme_manager et on inclue la classe dynamiquement avant d'inclure common.php (pour utilisation en session)
	
	$photo = 'photo-light';
	if (isset($_GET['val'])) {
		if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
		if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
    	switch ($_GET['val']) {
	
			case 'act_rpt_crit' :
				$GLOBALS['afficher_menu_rpt'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/activer_rpt_critere.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'criteres' :
				$GLOBALS['afficher_menu_rpt'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_criteres.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'list_rpt' :
				$GLOBALS['afficher_menu_rpt'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/liste_user_report.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'gest_rpt' :
				$GLOBALS['afficher_menu_rpt'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_user_report.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'gest_mes' :
				$GLOBALS['afficher_menu_rpt'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_mesures.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'gest_dim' :
				$GLOBALS['afficher_menu_rpt'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_dimensions.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'gest_agg' :
				$GLOBALS['afficher_menu_rpt'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_aggregations.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'supp_pdf' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/supp_pdf.php';
			break;
			
			case 'test_supp_pdf' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/test_supp_pdf.php';
			break;
			
            case 'pop_rpt_tabm' :
                $GLOBALS['cacher_interface'] = true;
                require_once 'common.php';
                require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
                require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_rpt_tabm.php';
                require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
            break;
		case 'pop_rpt_chp' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_rpt_champs.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'pop_rpt_jnt' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_rpt_jnt.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;        
        
		case 'pop_valeur_critere' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_rpt_val_crit.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;					
			case 'OpenPopupRptSyst' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_report_syst.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'OpenPopupRptCrit' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_report_crit.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'OpenPopupRptMes' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_report_mes.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'OpenPopupRptDim' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_report_dim.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'OpenPopupRptAgg' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_report_agg.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
			
			case 'OpenPopupRptQry' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_report_qry.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
           
		    case 'pop_import_rpt_table' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_import_rpt_table.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;
		
			case 'OpenPopupExportXls' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/instance_export.php';
			break;
			
            case 'OpenPopupExportHtml' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/instance_html.php';
			break;
			
			case 'videForXls' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				//require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/test_export.php';
			break;
            
        case 'popup_rpt_crit' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_rpt_critere.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

        case 'test_rpt' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/test_rpt.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;

			case 'crit_reg' :
				$GLOBALS['cacher_interface'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/criteres_regs.php';
			break;
            
			case 'instance_etat' :
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/instance_etat_externe.php';
			break;	
            
            case 'charger_chps' :
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/charger_champs_table.php';
			break;
            
			default:
				$GLOBALS['afficher_menu_rpt'] = true;
				require_once 'common.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
				//require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/activer_rpt_critere.php';
				require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
			break;	
		}
	}else{
		$GLOBALS['afficher_menu_rpt'] = true;
		require_once 'common.php';
		require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
		//require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/activer_rpt_critere.php';
		require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';

	}
?>
