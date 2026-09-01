<?php

	$GLOBALS['PARAM']                                           =   array();

	$GLOBALS['PARAM']['TYPE']	                                =	'TYPE';
	$GLOBALS['PARAM']['CODE']	                                =	'CODE';
	$GLOBALS['PARAM']['LIBELLE']	                            =	'LIBELLE';
	$GLOBALS['PARAM']['ORDRE']	                                =	'ORDRE';
	$GLOBALS['PARAM']['SYSTEME']	                            =	'SYSTEME';
	$GLOBALS['PARAM']['CODE_ADMIN']	                           	=	'CADMIN';   /*******************nouveau champ ajoute*******************/
    
	$GLOBALS['PARAM']['REGROUPEMENT']	                        =	'REGROUPEMENT';
	$GLOBALS['PARAM']['ORDRE_TRIE_REGROUPEMENT']	            =	'LIBELLE_REGROUPEMENT';
	$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT']	                =	'PERE_CODE_REGROUPEMENT';
	$GLOBALS['PARAM']['LIAISONS']	                            =	'LIAISONS';

	$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']	            =	'TYPE_CHAINE_REGROUP';
	$GLOBALS['PARAM']['HIERARCHIE']	                            =	'HIERARCHIE';
	$GLOBALS['PARAM']['NIVEAU_CHAINE']                          =	'NIVEAU_CHAINE';
    
	$GLOBALS['PARAM']['TYPE_REGROUPEMENT']	                    =	'TYPE_REGROUPEMENT';
	$GLOBALS['PARAM']['TYPE_RATTACHEMENT']						=	'';//utiliser pour rattacher le themme   une nomenclature : definition de plusieurs instances de theme_manager et/ou definition d'une hierarchie entre les entit s collect es
    $GLOBALS['PARAM']['RATTACHEMENT_FILTRE_PLUSIEURS']     		=	array();//Permet de gerer la liason 1,1 ou plusieurs   plusieurs entre un type d'entit  collect e et le filtre associ 
	$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC']  		=	array();//Permet d'associer une table de donn es   la selection du filtre pour un type d'entit  de collecte
	$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_SELECT']  		=	array();//Permet de selection l'occurence du filtre   afficher   partir d'une table de donn e d'un type d'entit  de collecte
	$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']	    =	array();//Permet d'associer une table de donn es   la selection des occurences d'entit s de collecte d'un type donn 
	$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']	            =	'';
	
	$GLOBALS['PARAM']['ETABLISSEMENT']	                        =	'ETABLISSEMENT';
	$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	                    =	'CODE_ETABLISSEMENT';
    $GLOBALS['PARAM']['NOM_ETABLISSEMENT']	                    =	'NOM_ETABLISSEMENT';
    $GLOBALS['PARAM']['CODE_ADMINISTRATIF']	                    =	'';
	$GLOBALS['PARAM']['NOM_CHEF_ETABLISSEMENT']             	=	'NOM_CHEF_ETABLISSEMENT';
    $GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']	            =	'TYPE_SECTEUR';
	$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT']	            =	'NOM_ETABLISSEMENT';//Ajout pr gestion affichage des etablissements par ordre alpha ou code admin
	$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']	            =	'ETABLISSEMENT_REGROUPEMENT';
	$GLOBALS['PARAM']['DONNEES_ETABLISSEMENT']		            = 	'DONNEES_GENERALES';
	
	$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']	            =	'TYPE_SOUS_SECTEUR';
    
	$GLOBALS['PARAM']['TYPE_ANNEE']	                            =	'TYPE_ANNEE';
    
	$GLOBALS['PARAM']['ENSEIGNANT']	                            =	'';
	$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']	                =	'';
	$GLOBALS['PARAM']['NUMERO_ORDRE_ENSEIGNANT']	            =	'';
	$GLOBALS['PARAM']['NOM_ENSEIGNANT']	                        =	'';
	$GLOBALS['PARAM']['PRENOMS_ENSEIGNANT']	                    =	'';
	$GLOBALS['PARAM']['MATRICULE_ENSEIGNANT']	                =	'';
	$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']	            =	'';
	
	$GLOBALS['PARAM']['PERSONNEL_ADMIN']	                    =	'';
	$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']	        =	'';
	$GLOBALS['PARAM']['NUMERO_ORDRE_PERSONNEL_ADMIN']	        =	'';
	$GLOBALS['PARAM']['NOM_PERSONNEL_ADMIN']	                =	'';
	$GLOBALS['PARAM']['PRENOMS_PERSONNEL_ADMIN']	            =	'';
	$GLOBALS['PARAM']['MATRICULE_PERSONNEL_ADMIN']	            =	'';
	$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']	        =	'';
	
	$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']	            =	'';//Integer type field to be added in STAFF_SCHOOL table for TMIS
	$GLOBALS['PARAM']['ETAB_PREC_ENSEIG_TRANSFERT']	            =	'';//Text type field to be added in STAFF_SCHOOL table for TMIS
	$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT']				=	'';//Text type field to be added in STAFF_SCHOOL table for TMIS
    
	$GLOBALS['PARAM']['TEACHER_VALIDATION']						= 	false;
	$GLOBALS['PARAM']['TEACH_VALID_TABLE'] 						= 	'';
	$GLOBALS['PARAM']['TEACH_VALID_FIELDS'] 					= 	array();
	$GLOBALS['PARAM']['TEACH_VALID_CRITERIA']					= 	array('FIELD'=>'','TYPE'=>'');
	$GLOBALS['PARAM']['TEACH_VALID_THEMES']		        		=	array();
    
	$GLOBALS['PARAM']['LOCAL']	                                =	'';
	$GLOBALS['PARAM']['NUMERO_LOCAL']	                        =	'';
	
    $GLOBALS['PARAM']['POPULATION']	                            =	'POPULATION';
	$GLOBALS['PARAM']['CHAMPS_POPULATION']				        =	array();
	$GLOBALS['PARAM']['CHAMPS_POPULATION'][]			        =	array('NOM_CHAMP' => 'POP_G' , 'LABEL_CHAMP' => 'G');
	$GLOBALS['PARAM']['CHAMPS_POPULATION'][]			        =	array('NOM_CHAMP' => 'POP_F' , 'LABEL_CHAMP' => 'F');
	$GLOBALS['PARAM']['TYPE_AGE_POPULATION']	                =	'TYPE_AGE_POPULATION';
    
	$GLOBALS['PARAM']['NB']                                     =   'NO';
	$GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES']	            = 	'ESTIMATION';
	$GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_AND_INFO']		        =	true;
	$GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_THEMES']		        =	array();//Si le tableau est vide ca l'affichera pour tous les themes
    $GLOBALS['PARAM']['DO_ESTIMATION_BATCH']	                = 	true;
    $GLOBALS['PARAM']['HIDE_THEME_NAVIG']		                =	true;
	$GLOBALS['PARAM']['HIDE_DELETE_SCHOOL']		                =	true;
    $GLOBALS['PARAM']['CONCAT_CODE_ADMIN']		                =	true;
    $GLOBALS['PARAM']['ALERT_CTRL_THM_OK']		                =	false;
	$GLOBALS['PARAM']['BTN_TRANSFERT_ENSEIGNANT']	            =	false;
	$GLOBALS['PARAM']['TRANSFERT_ENSEIGNANT_THEMES']		    =	array();
    $GLOBALS['PARAM']['BTN_TRANSFERT_PERS_ADMIN']	            =	false;
	$GLOBALS['PARAM']['TRANSFERT_PERS_ADMIN_THEMES']		    =	array();
    $GLOBALS['PARAM']['BTN_TRANSFERT_PERS_SERVICE']	            =	false;
	$GLOBALS['PARAM']['TRANSFERT_PERS_SERVICE_THEMES']		    =	array();
    $GLOBALS['PARAM']['LOG_BY_SCHOOL_USER']	                    =	'';
    $GLOBALS['PARAM']['LOG_BY_SCHOOL_PASS']	                    =	'';
	$GLOBALS['PARAM']['TEACHERS_MANAGEMENT']	                =	false;
	$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']	            	=	array();//This ligne need to be commented if there is no TMIS to be done
	$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']	        		=	array();//This ligne need to be commented if there is no TMIS to be done
	$GLOBALS['PARAM']['HIDE_TEACHER_NAVIG']						=	false;
	$GLOBALS['PARAM']['BTN_IMPORT_EXCEL']                       =   false;
    $GLOBALS['PARAM']['DO_EXCEL_IMPORT_BATCH']	                = 	false;
    $GLOBALS['PARAM']['TYPE_EXPORT_QUICK_RPT']					=	'html';
	$GLOBALS['PARAM']['USER_PRIVILEGE_MANAGEMENT']				=	true;
    $GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE']          =   array();//This ligne need to be commented if there is no screen to be generated during data entry
	$GLOBALS['PARAM']['DATA_ENTRY_BY_USER']	                    =	true;//Pour gerer la trace sur une table de donn es
	$GLOBALS['PARAM']['DATA_ENTRY_TABLE']                       =	'';//Table de donn es utilis e pour tracer la saisie
	$GLOBALS['PARAM']['DATA_ENTRY_USER']                        =	'';//Nom du champ utilis  pour stocker le nom utilisateur
	$GLOBALS['PARAM']['DATA_ENTRY_DATE']                        =	'';//Nom du champ utilis  pour stocker la date
	$GLOBALS['PARAM']['DATA_ENTRY_TRACE']	                    =	true;//To trace all data handling in the system//Overall trace

	$GLOBALS['PARAM']['FILTRE']			                        =	false;
	$GLOBALS['PARAM']['TYPE_FILTRE']	                        =	'';
	$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC']  					=	'';//Permet d'associer une table de donn es   la selection du filtre
	$GLOBALS['PARAM']['FILTRE_CHAMP_ASSOC']  					=	'';//Permet definir le champs de la table de donn es   lier au filtre
	$GLOBALS['PARAM']['TYPE_PERIODICITE']	                    =	'';
	$GLOBALS['PARAM']['TYPE_PERIODE']	                        =	'';
	
	$GLOBALS['PARAM']['CLES_SIMPLES']	                        =	array();
	$GLOBALS['PARAM']['CLES_SIMPLES'][]							= 	$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
	$GLOBALS['PARAM']['CLES_SIMPLES'][]							= 	$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
	$GLOBALS['PARAM']['CLES_SIMPLES'][]							= 	$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
	$GLOBALS['PARAM']['CODE_TYPE_SYSTEME']	           			=	$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
	$GLOBALS['PARAM']['ZONES_LISTE']	           				=	array('systeme','combo','liste_checkbox','systeme_liste_radio','systeme_valeur_multiple','systeme_combo','dimension_ligne','dimension_colonne','liste_radio');
	$GLOBALS['PARAM']['COMP_CODE_TYPE']		           			=	array('systeme','combo','liste_checkbox','systeme_liste_radio','systeme_valeur_multiple','systeme_combo','dimension_ligne','dimension_colonne','liste_radio');	
	$GLOBALS['PARAM']['COMP_TYPE_NUMERIQUE']     			    =	array('liste_radio','booleen','checkbox','combo','liste_checkbox','radio','systeme','systeme_liste_radio','systeme_valeur_multiple','systeme_valeur_unique','loc_etab','systeme_combo','dimension_ligne','dimension_colonne',);
	$GLOBALS['PARAM']['COMP_TABLE_FILLE']	           			=	array('combo','liste_checkbox','systeme_liste_radio','systeme_valeur_multiple','systeme_combo','dimension_ligne','dimension_colonne','liste_radio');	
	$GLOBALS['PARAM']['COMP_CHAMP_LIE']	           				=	array('combo','systeme_liste_radio','systeme_combo','liste_radio');
	
    $GLOBALS['PARAM']['THEMES_DEBUT_ANNEE']                     =	array();
    $GLOBALS['PARAM']['THEMES_TRIMESTRIEL']                     =	array();
	$GLOBALS['PARAM']['THEMES_MENSUEL']                         =	array();
	$GLOBALS['PARAM']['THEMES_SEMESTRIEL']                      =	array();
	$GLOBALS['PARAM']['THEMES_FIN_ANNEE']                       =	array();
	$GLOBALS['PARAM']['CODES_DEBUT_ANNEE']                      =	array();
    $GLOBALS['PARAM']['CODES_TRIMESTRIEL']                      =	array();
	$GLOBALS['PARAM']['CODES_MENSUEL']                          =	array();
	$GLOBALS['PARAM']['CODES_SEMESTRIEL']                       =	array();
	$GLOBALS['PARAM']['CODES_FIN_ANNEE']                        =	array();

	$GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB']               =	array();//Permet d'appliquer un style sur la cartouche infos_etab en haut   gauche
	$GLOBALS['PARAM']['MOBILE_THEME_CONFIG']			        =	false;

	/*$GLOBALS['PARAM']                                           =	array();

	$GLOBALS['PARAM']['TYPE']	                                =	'TYPE';
	$GLOBALS['PARAM']['CODE']	                                =	'CODE';
	$GLOBALS['PARAM']['LIBELLE']	                            =	'LIBELLE';
	$GLOBALS['PARAM']['ORDRE']	                              	=	'ORDRE';
	$GLOBALS['PARAM']['SYSTEME']	                            =	'SYSTEME';
	$GLOBALS['PARAM']['CODE_ADMIN']	                           	=	'CADMIN';

	$GLOBALS['PARAM']['REGROUPEMENT']	                        =	'REGROUPEMENT';
	$GLOBALS['PARAM']['ORDRE_TRIE_REGROUPEMENT']	            =	'LIBELLE_REGROUPEMENT';
	$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT']	              	=	'PERE_CODE_REGROUPEMENT';
	$GLOBALS['PARAM']['LIAISONS']	                            =	'LIAISONS';
	$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']				=	'TYPE_CHAINE_REGROUP';
	$GLOBALS['PARAM']['HIERARCHIE']								=	'HIERARCHIE';
	$GLOBALS['PARAM']['NIVEAU_CHAINE']							=	'NIVEAU_CHAINE';
    
	$GLOBALS['PARAM']['TYPE_REGROUPEMENT']	                  	=	'TYPE_REGROUPEMENT';
	$GLOBALS['PARAM']['TYPE_RATTACHEMENT']						=	'';//utiliser pour rattacher le themme   une nomenclature : definition de plusieurs instances de theme_manager et/ou definition d'une hierarchie entre les entit s collect es
    $GLOBALS['PARAM']['RATTACHEMENT_FILTRE_PLUSIEURS']     		=	array();//Permet de gerer la liason 1,1 ou plusieurs   plusieurs entre un type d'entit  collect e et le filtre associ 
	$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC']  		=	array();//Permet d'associer une table de donn es   la selection du filtre pour un type d'entit  de collecte
	$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_SELECT']  		=	array();//Permet de selection l'occurence du filtre   afficher   partir d'une table de donn e d'un type d'entit  de collecte
	$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']	    =	array();//Permet d'associer une table de donn es   la selection des occurences d'entit s de collecte d'un type donn 
	$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']	            =	'';
	
	$GLOBALS['PARAM']['ETABLISSEMENT']	                        =	'ETABLISSEMENT';
	$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	                    =	'CODE_ETABLISSEMENT';
    $GLOBALS['PARAM']['NOM_ETABLISSEMENT']	                    =	'NOM_ETABLISSEMENT';
    $GLOBALS['PARAM']['CODE_ADMINISTRATIF']	                    =	'';
	$GLOBALS['PARAM']['NOM_CHEF_ETABLISSEMENT']             	=	'NOM_CHEF_ETABLISSEMENT';
    $GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']	            =	'TYPE_SECTEUR';
	$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT']	            =	'NOM_ETABLISSEMENT';//Ajout pr gestion affichage des etablissements par ordre alpha ou code admin
	$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']	            =	'ETABLISSEMENT_REGROUPEMENT';
	$GLOBALS['PARAM']['DONNEES_ETABLISSEMENT']		            = 	'DONNEES_GENERALES';
	
	$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']	            =	'TYPE_SOUS_SECTEUR';
    
	$GLOBALS['PARAM']['TYPE_ANNEE']	                            =	'';
    
	$GLOBALS['PARAM']['ENSEIGNANT']	                            =	'';
	$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']	                =	'';
	$GLOBALS['PARAM']['NUMERO_ORDRE_ENSEIGNANT']	            =	'';
	$GLOBALS['PARAM']['NOM_ENSEIGNANT']	                        =	'';
	$GLOBALS['PARAM']['PRENOMS_ENSEIGNANT']	                    =	'';
	$GLOBALS['PARAM']['MATRICULE_ENSEIGNANT']	                =	'';
	$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']	            =	'';
	
	$GLOBALS['PARAM']['PERSONNEL_ADMIN']	                    =	'';
	$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']	        =	'';
	$GLOBALS['PARAM']['NUMERO_ORDRE_PERSONNEL_ADMIN']	        =	'';
	$GLOBALS['PARAM']['NOM_PERSONNEL_ADMIN']	                =	'';
	$GLOBALS['PARAM']['PRENOMS_PERSONNEL_ADMIN']	            =	'';
	$GLOBALS['PARAM']['MATRICULE_PERSONNEL_ADMIN']	            =	'';
	$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']	        =	'';
	
	$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']	            =	'';//Integer type field to be added in STAFF_SCHOOL table for TMIS
	$GLOBALS['PARAM']['ETAB_PREC_ENSEIG_TRANSFERT']	            =	'';//Text type field to be added in STAFF_SCHOOL table for TMIS
	$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT']				=	'';//Text type field to be added in STAFF_SCHOOL table for TMIS
    
	$GLOBALS['PARAM']['TEACHER_VALIDATION']						= 	false;//To activate teacher validation
	$GLOBALS['PARAM']['TEACH_VALID_TABLE'] 						= 	'';//Teachers data table
	$GLOBALS['PARAM']['TEACH_VALID_FIELDS'] 					= 	array();//Fields to be copied/selected from table
	$GLOBALS['PARAM']['TEACH_VALID_CRITERIA']					= 	array('FIELD'=>'','TYPE'=>'');//Field to be used as criteria and its type
	$GLOBALS['PARAM']['TEACH_VALID_THEMES']		        		=	array();//Themes id (screens form) on which teachers validation should be applied
    
	$GLOBALS['PARAM']['LOCAL']	                                =	'';
	$GLOBALS['PARAM']['NUMERO_LOCAL']	                        =	'';
	
    $GLOBALS['PARAM']['POPULATION']	                            =	'POPULATION';//Population table
	$GLOBALS['PARAM']['CHAMPS_POPULATION']						=	array();
	$GLOBALS['PARAM']['CHAMPS_POPULATION'][]					=	array('NOM_CHAMP' => 'POP_G' , 'LABEL_CHAMP' => 'G');
	$GLOBALS['PARAM']['CHAMPS_POPULATION'][]					=	array('NOM_CHAMP' => 'POP_F' , 'LABEL_CHAMP' => 'F');
	$GLOBALS['PARAM']['TYPE_AGE_POPULATION']	                =	'TYPE_TRANCHE_AGE';
	//$GLOBALS['PARAM']['CHAMPS_POPULATION'][]			        =	array();
	//$GLOBALS['PARAM']['CHAMPS_POPULATION'][]			        =	array();
    
	$GLOBALS['PARAM']['NB']                                     =   'NB';
	$GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES']	            = 	'ESTIMATION';
	$GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_AND_INFO']		        =	true;
	$GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_THEMES']		        =	array();//Si le tableau est vide ca l'affichera pour tous les themes
    $GLOBALS['PARAM']['DO_ESTIMATION_BATCH']	                = 	true;
    $GLOBALS['PARAM']['HIDE_THEME_NAVIG']		                =	true;
	$GLOBALS['PARAM']['HIDE_DELETE_SCHOOL']		                =	true;
    $GLOBALS['PARAM']['CONCAT_CODE_ADMIN']		                =	true;
    $GLOBALS['PARAM']['ALERT_CTRL_THM_OK']		                =	false;
	$GLOBALS['PARAM']['BTN_TRANSFERT_ENSEIGNANT']	            =	false;
	$GLOBALS['PARAM']['TRANSFERT_ENSEIGNANT_THEMES']		    =	array();
    $GLOBALS['PARAM']['BTN_TRANSFERT_PERS_ADMIN']	            =	false;
	$GLOBALS['PARAM']['TRANSFERT_PERS_ADMIN_THEMES']		    =	array();
    $GLOBALS['PARAM']['BTN_TRANSFERT_PERS_SERVICE']	            =	false;
	$GLOBALS['PARAM']['TRANSFERT_PERS_SERVICE_THEMES']		    =	array();
    $GLOBALS['PARAM']['LOG_BY_SCHOOL_USER']	                    =	'';
    $GLOBALS['PARAM']['LOG_BY_SCHOOL_PASS']	                    =	'';
	$GLOBALS['PARAM']['TEACHERS_MANAGEMENT']	                =	false;
	$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']	            	=	array();//This ligne need to be commented if there is no TMIS to be done
	$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']	        		=	array();//This ligne need to be commented if there is no TMIS to be done
	$GLOBALS['PARAM']['HIDE_TEACHER_NAVIG']						=	false;
	$GLOBALS['PARAM']['BTN_IMPORT_EXCEL']                       = 	false;
	$GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP']			    =	1;
	$GLOBALS['PARAM']['IMPORT_GRILLE_COLONNE_STEP']			    =	2;
	$GLOBALS['PARAM']['IMPORT_GRILLE_LIMIT_VIDE']			    =	5;
	$GLOBALS['PARAM']['DO_EXCEL_IMPORT_BATCH']	                = 	false;
    $GLOBALS['PARAM']['TYPE_EXPORT_QUICK_RPT']					=	'html';
	$GLOBALS['PARAM']['USER_PRIVILEGE_MANAGEMENT']				=	false;
    $GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE']          =   array();//This ligne need to be commented if there is no screen to be generated during data entry
	$GLOBALS['PARAM']['DATA_ENTRY_BY_USER']	                    =	false;//Pour gerer la trace sur une table de donn es
	$GLOBALS['PARAM']['DATA_ENTRY_TABLE']                       =	'';//Table de donn es utilis e pour tracer la saisie
	$GLOBALS['PARAM']['DATA_ENTRY_USER']                        =	'';//Nom du champ utilis  pour stocker le nom utilisateur
	$GLOBALS['PARAM']['DATA_ENTRY_DATE']                        =	'';//Nom du champ utilis  pour stocker la date
	$GLOBALS['PARAM']['DATA_ENTRY_TRACE']	                    =	false;//To trace all data handling in the system//Overall trace
	
	$GLOBALS['PARAM']['FILTRE']			                        =	false;
	$GLOBALS['PARAM']['TYPE_FILTRE']	                        =	'';
	$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC']  					=	'';//Permet d'associer une table de donn es   la selection du filtre
	$GLOBALS['PARAM']['FILTRE_CHAMP_ASSOC']  					=	'';//Permet definir le champs de la table de donn es   lier au filtre
	$GLOBALS['PARAM']['TYPE_PERIODICITE']	                    =	'';
	$GLOBALS['PARAM']['TYPE_PERIODE']	                        =	'';//Table pour mobile
	
	//Ajout pour mobile: 16/05/2017
	$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']	            =	'';//Table pour mobile
	$GLOBALS['PARAM']['RATTACHEMENT_STATUT']	                =	'';//Table pour mobile
	$GLOBALS['PARAM']['DATE_DEBUT_STATUT']	                    =	'';
	$GLOBALS['PARAM']['LIBELLE_STATUT']	                    	=	'';
	
	//$GLOBALS['PARAM']['TYPE_DONNEE']	            			=	'TYPE_DONNEE';//Table pour mobile
	$GLOBALS['PARAM']['TYPE_VALIDATION']	                    =	'';//Table pour mobile
	$GLOBALS['PARAM']['VALIDATION_THEME']	                    =	'';//Table mobile
	$GLOBALS['PARAM']['VALIDATION_ID_THEME']	                =	'';
	$GLOBALS['PARAM']['VALIDATION_COMMENTAIRES']	            =	'';
	//Fin ajout pour mobile
	
	$GLOBALS['PARAM']['TABLES_WITHOUT_LOG_IMPORT']              =	array('ETABLISSEMENT','ETABLISSEMENT_REGROUPEMENT');
	$GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB']               =	array();
	$GLOBALS['PARAM']['MOBILE_THEME_CONFIG']			        =	false;
	$GLOBALS['PARAM']['UNIFORM_STYLE_ACTIVATED']               	=	true;
	$GLOBALS['PARAM']['MAIL_FROM']			        			=	"StatEduc2 <stateduc2@mail.mg>";
	$GLOBALS['PARAM']['MAIL_HOST']			        			=	"";
	$GLOBALS['PARAM']['MAIL_PORT']			        			=	"";
	$GLOBALS['PARAM']['MAIL_USERNAME']			        		=	"";
	$GLOBALS['PARAM']['MAIL_PASSWORD']			        		=	"";
	$GLOBALS['PARAM']['SMS_GATEWAY_URL']			        	=	"http://192.168.100.4:8766/";
	$GLOBALS['PARAM']['SMS_GATEWAY_KEY']			        	=	"918273";
	
	*/
?>