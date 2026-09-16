<?php //on lance le theme_manager et on inclue la classe dynamiquement avant d'inclure common.php (pour utilisation en session)

$photo = 'photo-light';

if (isset($_GET['val'])) {
	if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
	if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
    switch ($_GET['val']) {

        case 'param' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/info_param.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'param_conn' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/info_param.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'gestheme' :
            $GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_theme.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'gentheme' :
            $GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/generer_theme.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'gestmenu' :
            $GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_menu.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'genmenu' :
            $GLOBALS['lancer_theme_manager'] = true;            
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/generer_menu.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'gestraduc' :
            $GLOBALS['lancer_traduction'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_traduction.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

        case 'gestzone' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_zone.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
        case 'charger_chps' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/charger_champs_table.php';
        break;
        
        case 'service_charger_chps' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/service_charger_champs.php';
        break;
        
        case 'gestchaineloc' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_chaine_loc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'gest_systemes' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_systemes.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'gest_langues' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_langues.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'gest_annees' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_annees.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'gest_filtres' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_filtres.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
						
        case 'gest_periodes' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_periodes.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'gest_camp' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_campagne.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'gest_lib_trad' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_libelle_trad.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
		case 'params_global' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_param_global.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

        
			case 'gest_extract' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_extraction.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

        case 'OpenPopupZoneSysteme' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_zone_systeme.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'ImpTheme' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/import_theme.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'ImpZoneSyst' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/import_zones_syst.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupInsererTableMere' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/inserer_table_mere.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupAjouterZone' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/inserer_zone.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupZoneJoin' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_zone_join.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupZoneRegle' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_zone_regle.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupZoneOrdre' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_zone_ordre.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'OpenPopupRechLibelle' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/result_rech_libelle.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupGestDim' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_dimension.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/gestion_dimensions.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupZoneIndexes' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_zone_indexes.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupGestRegZone' :
            //$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_regle_zone.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'OpenPopupGestRegTheme' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_theme_regles.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'GestRegSuivi' :
            $GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_suivi_saisie_regles.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'ViderTraces' :
            $GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/vider_traces_saisie.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'OpenPopupRegThmAsc' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_theme_regles_assoc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'regen' :
            $GLOBALS['cacher_interface'] = true;
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/Config_Regeneration.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'import' :
            $GLOBALS['lancer_theme_manager'] = true;            
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/import.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'export' :
            $GLOBALS['lancer_theme_manager'] = true;            
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/export.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'aggregated_export' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/aggregated_export.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'download_user_template' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/download_user_excel_template.php';
        break;

        case 'download_file' :            
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_LIB'] . 'telecharger.php';
        break;
		
		case 'load_sql' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/load_sql.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'SaveAccessDB' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/save_access_db.php';
        break;

		case 'CompacterBD' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/compacter_BD.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'PopSaveAccessDB' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/save_access_db.php';
        break;
		
        case 'defaut_nomenc' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/defaut_nomenc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

		
        case 'defaut_nomenc_syst' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/defaut_nomenc_syst.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'action_import' :
			$GLOBALS['cacher_interface'] = true;
            $GLOBALS['lancer_theme_manager'] = true;            
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/action_import.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'action_import_teach' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/action_import_teacher.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'action_export' :
			$GLOBALS['cacher_interface'] = true;
            $GLOBALS['lancer_theme_manager'] = true;            
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/action_export.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'action_export_teach' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/action_export_teacher.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'data_maj' :
			//$GLOBALS['cacher_interface'] = true;
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/data_update.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'count_rec' :
			//$GLOBALS['cacher_interface'] = true;
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/count_records.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'aggregated_action_export' :
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/aggregated_action_export.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;

        case 'session' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/session.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'controle' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/controle_coherence.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'controle_val' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/controle_validation.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'suivi_saisie' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/suivi_saisie.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'incohr_list_etabs' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/ctrl_coherence_list_etabs.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'validate_etab_detais' :
			$GLOBALS['lancer_theme_manager'] = true;
      		$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/ctrl_validation_etab_details.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'suivi_list_etabs' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/suivi_saisie_list_etabs.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'PopupExportHtml' :
            $GLOBALS['cacher_interface'] = true;
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/instance_popup_html.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'PopExportWord' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/instance_word.php';
        break;
		
		case 'imputation' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/run_imputation.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'trier_grille' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/run_traitement_grille.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
        case 'filtrer_grille' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
			//require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/run_traitement_grille.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'exporter_grille' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
			 //require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/run_traitement_grille.php';
            //require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'videForXls' :
			$GLOBALS['cacher_interface'] = true;
			require_once 'common.php';
			//require_once $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/test_export.php';
		break;
		case 'OpenPopupExportGrilleXls' :
			$GLOBALS['lancer_theme_manager'] = true;
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/instance_export_xls.php';
        break;          
        case 'update_version' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/gestion_updates.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'testdev' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/testdev.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        case 'gestiongroup' :
            $GLOBALS['placer_conn_dico'] = true;
			$GLOBALS['lancer_nomenclature'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_group.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
        case 'gestionuser' :
			$GLOBALS['placer_conn_dico'] = true;
            $GLOBALS['lancer_user'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_user.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		 case 'gestionuserpriv' :
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_user_priv.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
        case 'gestiondroit' :
			$GLOBALS['placer_conn_dico'] = true;
            $GLOBALS['lancer_user'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_droit.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
				
        case 'gest_plages' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_plages_codes.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'gest_plages_codes_assoc' :
            $GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_plages_codes_assoc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
        case 'excel_file_descrip' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/excel_file_descrip.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'OpenPopupExcelFieldsDef' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_excel_fields.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'OpenPopupExcelTables' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_excel_tables.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'OpenPopupExcelAggregTables' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_excel_aggreg_tables.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'OpenPopupExcelAssocQuery' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_req_assoc_aggreg_export.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'OpenPopupFieldsRowCol' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/excel_fields_row_col.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'OpenPopupAggregFixeVal' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_excel_aggreg_tab_fixe_val.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'OpenPopupFiltreVal' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_excel_tab_filtre_val.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'OpenPopupAggregFieldsRowCol' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/aggregated_excel_fields_row_col.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'aggregated_db_structure' :
			$GLOBALS['lancer_theme_manager'] = true;
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/aggregated_db_structure.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'aggregated_excel_file_desc' :
			$GLOBALS['lancer_theme_manager'] = true;
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/aggregated_excel_file_desc.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'excel_table_fille_requete' :
			$GLOBALS['lancer_theme_manager'] = true;
			$GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_excel_table_fille_requete.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'fields_aggregated_table' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/fields_aggregated_table.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		case 'frame_gestion_aggreg_export_filtre' :
			$GLOBALS['lancer_theme_manager'] = true;
            $GLOBALS['cacher_interface'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
			require_once $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_aggreg_export_filtre.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'fix_regroup' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_user_priv.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
		
		case 'upload_entites' :
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/upload_stat_entites.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
		     break;   
		
		case 'feedbacks' :
    case 'feedbacks_auto' :
    case 'feedbacks_auto_add' :
    case 'feedbacks_auto_modify' :
    case 'feedbacks_auto_firepjs' :
    case 'feedbacks_auto_error' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_collectors_feedback.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
        break;
        
    	case 'feedbacks_auto_delete' :
			$GLOBALS['lancer_theme_manager'] = true;
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_collectors_feedback.php';
        break;
    
		case 'send_feedbacks_auto' :  
			$GLOBALS['lancer_theme_manager'] = true;
			$GLOBALS['cacher_interface'] = true;
            require_once 'common_ws.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/remind_collectors.php';
        break; 
		
		default:
            require_once 'common.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'administration/info_param.php';
            require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';
    }

} else {

    require_once 'common.php';
    require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';
    require_once $GLOBALS['SISED_PATH_INC'] . 'administration/info_param.php';
    require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';

}

?>