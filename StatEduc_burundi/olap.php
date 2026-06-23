<?php //on lance le theme_manager et on inclue la classe dynamiquement avant d'inclure common.php (pour utilisation en session)

$photo = 'photo-light';

if (isset($_GET['val'])) {
	if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
    if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
    switch ($_GET['val']) {

        case 'view' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/olap_view.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'cub_server' :
			$GLOBALS['afficher_menu_cubes'] = true;
			require_once 'common.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
		break;
        case 'pop_OLAP2' :
			$GLOBALS['afficher_menu_cubes'] = true;
			require_once 'common.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/instance_olap.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'pop_olap_menu' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/instance_olap.php';
        break;
        case 'pop_OLAP' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/instance_olap.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'pop_olap_doc' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/pop_olap_doc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_olap_img' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/pop_olap_img.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
		case 'gest_olap_inst' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_inst.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        
		case 'gest_dim' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_dim.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_dimensions.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        
		case 'gest_mes' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_mes.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_mesures.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

		
		case 'other_cnx' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_other_cnx.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

		case 'type_field' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_type_ch.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		
		case 'fonc' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_fonc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		
		case 'format' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_format.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

		case 'sgbd' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_sgbd.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

		
		case 'pop_olap_mes' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_mes.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		
		case 'pop_olap_chp' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_champs.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
        case 'popup_olap_crit' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_critere.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		
		case 'pop_olap_qry' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_qry.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		
		case 'pop_olap_tabm' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_tabm.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_olap_import_cube' :
            //$GLOBALS['cacher_interface'] = true;
			require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_import_cube.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'pop_olap_del_cube' :
            //$GLOBALS['cacher_interface'] = true;
			require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_del_cube.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_olap_import_cube_doc' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/popup_olap_import_cube_doc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
        case 'pop_olap_import_cube_img' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/popup_olap_import_cube_img.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
        case 'pop_olap_import_un_cube' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/popup_olap_import_un_cube.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
		case 'pop_olap_dim' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_dim.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'pop_import_dim' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_import_dim.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'pop_valeur_critere' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_val_crit.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
		case 'pop_olap_jnt' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_jnt.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		
		case 'pop_olap_gen' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/generer_cube.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
		case 'pop_olap_gen_menu' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/generer_cube.php';
        break;
		
		
		case 'olap_gen_all' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/generer_cube.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';            
        break;
        
		case 'pop_refresh_cubes_menu' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/raffraichir_cubes_serveur.php';
        break;
			
		case 'refresh_cubes_server' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/raffraichir_cubes_serveur.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';            
        break;
        case 'refresh_cubes_server2' :
			$GLOBALS['afficher_menu_cubes'] = true;
			require_once 'common.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/raffraichir_cubes_serveur.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        		
        case 'gen_batch' :            
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/gestion_olap_gen_batch.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'del_fact_tab' :            
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'olap_tools/suppr_tables_faits.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
        default:
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'administration/info_param.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
    }

} else {

    require_once 'common.php';
    require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
    //require_once $GLOBALS['SISED_PATH_INC'] . 'administration/info_param.php';
    require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';

}

?>
