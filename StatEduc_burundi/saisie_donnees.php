<?php //$GLOBALS['lancer_theme_manager'] = true;

$photo = 'photo-light';
if (isset($_GET['val'])) {
	if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
    if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
    switch ($_GET['val']) {

        case 'atlas':
            $GLOBALS['lancer_atlas'] = true; 
            $GLOBALS['lancer_nomenclature'] = true;  
            $GLOBALS['lancer_chaine'] = true;             
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/atlas.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'pop':
            $GLOBALS['lancer_atlas_pop'] = true; 
            $GLOBALS['lancer_nomenclature'] = true;  
            $GLOBALS['lancer_chaine'] = true;             
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/population.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'nomenc_data':
            $GLOBALS['lancer_nomenclature'] = true;  
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/nomenc_data.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'scoring_bench_teacher_feedb':
            $GLOBALS['lancer_atlas_pop'] = true; 
            $GLOBALS['lancer_nomenclature'] = true;  
            $GLOBALS['lancer_chaine'] = true;             
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/scoring_bench_teacher_feedb.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'modif_atlas':
            $GLOBALS['lancer_atlas'] = true; 
            $GLOBALS['lancer_nomenclature'] = true;  
            $GLOBALS['lancer_chaine'] = true;             
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/modif_atlas.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'nomenclature':           
            $GLOBALS['lancer_nomenclature'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/nomenclature.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'choix_sys_princ':           
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/choix_secteur.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'choix_sys_saisie':           
            $GLOBALS['afficher_menu_saisie'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/choix_secteur.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'choix_sys_rpt':           
            $GLOBALS['afficher_menu_rpt'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/choix_secteur.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'choix_etablissement':
            $GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';                    
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/etablissement.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'liste_etablissement':  
            $GLOBALS['cacher_interface'] = true;
            $GLOBALS['lancer_theme_manager'] = true; 
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/etablissement.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'suppr_etablissement':
            $GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';                    
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/run_suppr_etablissement.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'questionnaire': 
        case 'new_etab':         
            $GLOBALS['lancer_theme_manager'] = true;        
            $GLOBALS['lancer_theme_manager_classe'] = true;
            $GLOBALS['afficher_menu_saisie'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/questionnaire.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'popup':
            $GLOBALS['lancer_theme_manager'] = true;           
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/popup.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupChoixReg':
            $GLOBALS['lancer_theme_manager'] = true;           
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/recherche_reg.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'OpenPopupChoixEtab':
            $GLOBALS['lancer_theme_manager'] = true;           
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/recherche_etab.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'rech_ens' :
            $GLOBALS['cacher_interface'] = true;
            $GLOBALS['lancer_theme_manager'] = true; 
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/rech_ens.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'test' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/test_rech_ens.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'PopupImportExcel':
			$GLOBALS['lancer_theme_manager'] = true;
			$GLOBALS['lancer_theme_manager_classe'] = true;
			$GLOBALS['theme_data_MAJ_ok'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/import_excel.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'valid_teach' :
            $GLOBALS['lancer_theme_manager'] = true;        
            $GLOBALS['lancer_theme_manager_classe'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/valid_teach.php';
        break;
		
		case 'desact_zone' :
            $GLOBALS['lancer_theme_manager'] = true;        
            $GLOBALS['lancer_theme_manager_classe'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/desact_zone_theme.php';
        break;
		
		case 'desact_theme' :
            $GLOBALS['lancer_theme_manager'] = true;        
            $GLOBALS['lancer_theme_manager_classe'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/desact_zone_theme.php';
        break;
		
		default:
            $GLOBALS['lancer_theme_manager'] = true;  
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/etablissement.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';

    }

} else {

    require_once 'common.php';
    require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
    require_once $GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/etablissement.php';
    require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';

}

?>
