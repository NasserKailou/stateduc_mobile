<?php

	$GLOBALS['PARAM_SYS']                                           =   array();

		
	$GLOBALS['PARAM_SYS']['CLES_SIMPLES']	                        =	array();
	$GLOBALS['PARAM_SYS']['CLES_SIMPLES'][]							= 	$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
	$GLOBALS['PARAM_SYS']['CLES_SIMPLES'][]							= 	$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
	$GLOBALS['PARAM_SYS']['CLES_SIMPLES'][]							= 	$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
	$GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']	           			=	$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
	$GLOBALS['PARAM_SYS']['ZONES_LISTE']	           				=	array('systeme','combo','liste_checkbox','systeme_liste_radio','systeme_valeur_multiple','systeme_combo','dimension_ligne','dimension_colonne','liste_radio');
	$GLOBALS['PARAM_SYS']['COMP_CODE_TYPE']		           			=	array('systeme','combo','liste_checkbox','systeme_liste_radio','systeme_valeur_multiple','systeme_combo','dimension_ligne','dimension_colonne','liste_radio');	
	$GLOBALS['PARAM_SYS']['COMP_TYPE_NUMERIQUE']     			    =	array('liste_radio','booleen','checkbox','combo','liste_checkbox','radio','systeme','systeme_liste_radio','systeme_valeur_multiple','systeme_valeur_unique','loc_etab','systeme_combo','dimension_ligne','dimension_colonne',);
	$GLOBALS['PARAM_SYS']['COMP_TABLE_FILLE']	           			=	array('combo','liste_checkbox','systeme_liste_radio','systeme_valeur_multiple','systeme_combo','dimension_ligne','dimension_colonne','liste_radio');	
	$GLOBALS['PARAM_SYS']['COMP_CHAMP_LIE']	           				=	array('combo','systeme_liste_radio','systeme_combo','liste_radio');
	
	$GLOBALS['PARAM_SYS']['SHOW_VIEWS_IN_MOTHER_TABLES_LIST']       =	true;
	/*$GLOBALS['PARAM']['DICO_TABLE_SHARED']	           				=	array('DICO_TRADUCTION','DICO_AGGREGATION_LEVEL','DICO_AGGREGATION_REPORT','DICO_CHAINE_LOCALISATION','DICO_CRITERE_FILTRE','DICO_CRITERE_FILTRE_REPORT','DICO_DIMENSION_REPORT','DICO_DIMENSION','DICO_DIMENSION_IMBRIQUEE','DICO_ZONE','DICO_ZONE_JOINTURE','DICO_ZONE_SYSTEME','DICO_FIXE_REGROUPEMENT','DICO_MESURE','DICO_MESURE_REPORT','DICO_PLAGE_CODES','DICO_PLAGE_CODES_ASSOC','DICO_REGLE_THEME','DICO_REGLE_THEME_ASSOC','DICO_REPORT','DICO_REPORT_PARAM','DICO_REPORT_SYSTEME','DICO_RPT_CHAMP','DICO_RPT_CRITERE','DICO_RPT_JOINTURES','DICO_RPT_TABLE_MERE','DICO_TABLE_CLES','DICO_TABLE_MERE_THEME','DICO_THEME','DICO_THEME_SYSTEME','DICO_TRADUCTION','DICO_ZONE','DICO_ZONE_JOINTURE','DICO_ZONE_SYSTEME');*/
	$GLOBALS['PARAM']['DICO_TABLE_SHARED']	           			=	array('DICO_TRADUCTION','DICO_TYPE_COMPOSANT','DICO_TYPE_DONNEES','DICO_TYPE_FONCTIONNALITE','DICO_TYPE_OBJET','DICO_TYPE_PRESENTATION','DICO_TYPE_REPORT','DICO_TYPE_RUBRIQUE','DICO_TYPE_THEME','DICO_ZONE','DICO_ZONE_JOINTURE','DICO_ZONE_SYSTEME');
?>