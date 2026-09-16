<?php //on lance le theme_manager et on inclue la classe dynamiquement avant d'inclure common.php (pour utilisation en session)

$photo = 'photo-light';

if (isset($_GET['val'])) {
	if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
    if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
    switch ($_GET['val']) {

        case 'type_rpt' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_type_rpt.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'type_fonc' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_type_fonc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'type_rubr' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_type_rubr.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'type_comp' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_type_comp.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'gest_rpt' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_rpt.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'gest_comp' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_comp.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'gest_fonc' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_fonc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'gest_rubr' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_rubr.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'gest_agg' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_aggregations.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'gest_agg_ch' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_prof.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'gest_ref' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_ref.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'viewer' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/liste_rpt_treeview.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_rpt_sys' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_rpt_syst.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_fonc_sys' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_fonc_syst.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_rub_sys' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_rub_syst.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_comp_sys' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_comp_syst.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_fonc_niv_rub' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_fonc_niv_rub.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_rub_niv' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_rub_niv.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_fonc_rub' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_fonc_rub.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_comp_fonc' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_comp_fonc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_link_rub' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/gestion_link_rub.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'pop_rpt_view' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'annuaire/pop_rpt_view.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
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
