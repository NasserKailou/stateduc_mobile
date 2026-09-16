//Menu object creation
            oCMenu=new makeCM("oCMenu") //Making the menu object. Argument: menuname
            oCMenu.frames = 0
            //Menu properties   
            oCMenu.pxBetween =0
            oCMenu.fromTop=6
            oCMenu.rows=1 
            oCMenu.offlineRoot="" 
            oCMenu.onlineRoot="" 
            oCMenu.resizeCheck=1
            oCMenu.wait=300 
            oCMenu.fillImg="image/cm_fill.gif"
            oCMenu.zIndex=400
            oCMenu.menuPlacement=0  // permet de contrôler (ou bloquer) la position à gauche pour éviter
                                    // dans notre cas, que le menu ne se superpose pas au logo
            
            //Background bar properties
            oCMenu.useBar=0         // 1 affiche la barre de menu, 0 la cache
            oCMenu.barWidth="100%"
            oCMenu.barHeight=50     // modifie la hauteur de la barre de menu
            oCMenu.barClass="clBar" // classe css pour la barre
            oCMenu.barX=0 
            oCMenu.barY=50
            oCMenu.barBorderX=0
            oCMenu.barBorderY=0
            oCMenu.barBorderClass=""
                                                    
            // Paramètres de cm_makeLevel(width, height, regClass, overClass, borderX, borderY, borderClass, rows, align, offsetX, offsetY, arrow, arrowWidth, arrowHeight, roundBorder)
            oCMenu.level[0]=new cm_makeLevel(90,21,"clT","clTover",1,1,"clB",0,"bottom",0,0,0,0,0);
            oCMenu.level[2]=new cm_makeLevel(110,22,"clS2","clS2over");
            oCMenu.level[3]=new cm_makeLevel(140,22);
            oCMenu.fromLeft=144.5
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('2080','0','(#)Accueil','accueil.php','','60');
oCMenu.makeMenu('2010','0','(#)Saisie données','','','100');
oCMenu.makeMenu('20120','2010','(#)Atlas','saisie_donnees.php?val=atlas&type=typeregs','','145');
oCMenu.makeMenu('20780','20120','(#)Saisie Données - Atlas','saisie_donnees.php?val=atlas&type=typeregs','','130');
oCMenu.makeMenu('20770','20120','(#)Modification - Liaisons','saisie_donnees.php?val=modif_atlas&type=regs','','130');
oCMenu.makeMenu('20130','2010','(#)Nomenclatures','saisie_donnees.php?val=nomenclature','','145');
oCMenu.makeMenu('30340','2010','(#)Données Population','saisie_donnees.php?val=pop&type=regs','','145');
oCMenu.makeMenu('20810','2010','Choisir un Secteur','saisie_donnees.php?val=choix_sys_princ','','145');
oCMenu.makeMenu('20140','2010','(#)Choix Etablissement','saisie_donnees.php?val=choix_etablissement','','145');
oCMenu.makeMenu('20660','2010','(#)Ajout Etablissement','saisie_donnees.php?val=new_etab&theme=101','','145');
oCMenu.makeMenu('2020','0','(#)Contrôle / Validation','','','135');
oCMenu.makeMenu('30350','2020','(#)Controle Cohérences','administration.php?val=controle','','140');
oCMenu.makeMenu('30605','2020','(#)Suivi Saisie Données','','','140');
oCMenu.makeMenu('30625','30605','(#)Suivi Operateurs Saisie','administration.php?val=suivi_saisie','','145');
oCMenu.makeMenu('30615','30605','(#)Critères Evaluation Operateur','administration.php?val=GestRegSuivi','','145');
oCMenu.makeMenu('30635','30605','(#)Vider Traces par Année','administration.php?val=ViderTraces','','145');
oCMenu.makeMenu('2050','0','Outils Intégrés','','','110');
oCMenu.makeMenu('30400','2050','(#)Exportation','','','160');
oCMenu.makeMenu('20750','30400','(#)Données Etablissements','administration.php?val=export','','145');
oCMenu.makeMenu('30390','30400','(#)Données Aggrégées','administration.php?val=aggregated_export','','145');
oCMenu.makeMenu('20100','2050','(#)Importation Données','administration.php?val=import','','160');
oCMenu.makeMenu('20110','2050','(#)Cubes OLAP','olap.php?val=view&id_OLAP=1&base_OLAP=ZANZIBAR','','160');
oCMenu.makeMenu('30410','20110','(#)Cubes organisés en menu','olap.php?val=cub_server','','165');
oCMenu.makeMenu('30310','20110','(#)Cubes sur Serveur en vrac','olap.php?val=view&valView=cub_serveur','','165');
oCMenu.makeMenu('30550','20110','(#)Rafraichir/Traiter Cubes Serveur','olap.php?val=refresh_cubes_server','','165');
oCMenu.makeMenu('30300','20110','(#)Cubes Locaux','olap.php?val=view&valView=cub_locaux','','165');
oCMenu.makeMenu('20820','2050','(#)Charge un fichier SQL','administration.php?val=load_sql','','160');
oCMenu.makeMenu('20790','2050','(#)Initialiser Nomenclatures','administration.php?val=defaut_nomenc','','160');
oCMenu.makeMenu('20825','2050','(#)Retraitement de Codes','administration.php?val=data_maj','','160');
oCMenu.makeMenu('2040','0','Admin / Config','','','110');
oCMenu.makeMenu('2090','2040','(#)Gestion Utilisateurs','','','150');
oCMenu.makeMenu('20630','2090','(#)Gestion Groupes','administration.php?val=gestiongroup','','130');
oCMenu.makeMenu('20640','2090','(#)Gestion Utilisateurs','administration.php?val=gestionuser','','130');
oCMenu.makeMenu('20650','2090','(#)Gestion Droits','administration.php?val=gestiondroit','','130');
oCMenu.makeMenu('20160','2040','(#)Gestion Habillages','','','150');
oCMenu.makeMenu('20300','20160','(#)Defaut','?style=defaut.css','','100');
oCMenu.makeMenu('20310','20160','(#)Brun','?style=brun.css','','100');
oCMenu.makeMenu('20320','20160','(#)Afrique','javascript:set_style(\"afrique.css\");','','100');
oCMenu.makeMenu('20330','20160','(#)Contraste','?style=contraste.css','','100');
oCMenu.makeMenu('20340','20160','(#)Gris','javascript:set_style(\'gris.css\');','','100');
oCMenu.makeMenu('20500','2040','(#)Gestion Dictionnaire','','','150');
oCMenu.makeMenu('20600','20500','(#)Gestion Thèmes','administration.php?val=gestheme','','140');
oCMenu.makeMenu('20400','20500','(#)Gestion Menus','administration.php?val=gestmenu','','140');
oCMenu.makeMenu('20610','20500','(#)Gestion Traductions','administration.php?val=gestraduc','','140');
oCMenu.makeMenu('20620','20500','(#)Gestion Zones','administration.php?val=gestzone','','140');
oCMenu.makeMenu('20730','20500','(#)Gestion Règles Zones','administration.php?val=OpenPopupGestRegZone','','140');
oCMenu.makeMenu('20720','20500','(#)Gestion Libellés Pages','administration.php?val=gest_lib_trad&nom_page=accueil.php','','140');
oCMenu.makeMenu('30360','20500','(#)Gestion Importation Excel','administration.php?val=excel_file_descrip','','140');
oCMenu.makeMenu('20240','2040','(#)Menus & Templates','','','150');
oCMenu.makeMenu('20200','20240','(#)Génération Menus','administration.php?val=genmenu','','120');
oCMenu.makeMenu('20210','20240','(#)Génération Thèmes','administration.php?val=gentheme','','120');
oCMenu.makeMenu('20250','2040','(#)Gestion Paramètres','','','150');
oCMenu.makeMenu('20680','20250','(#)Paramètres Config','administration.php?val=param','','140');
oCMenu.makeMenu('20690','20250','(#)Gestion Secteurs','administration.php?val=gest_systemes','','140');
oCMenu.makeMenu('20700','20250','(#)Gestion Langues','administration.php?val=gest_langues','','140');
oCMenu.makeMenu('20710','20250','(#)Gestion Années','administration.php?val=gest_annees','','140');
oCMenu.makeMenu('30330','20250','Gestion Filtres','administration.php?val=gest_periodes','','140');
oCMenu.makeMenu('20830','2040','(#)Gestion Cubes OLAP','','','150');
oCMenu.makeMenu('30120','20830','(#)OLAP - Connexion Serveur ','olap.php?val=other_cnx','','170');
oCMenu.makeMenu('30530','20830','(#)OLAP - Importer Cubes Locaux','olap.php?val=pop_olap_import_cube','','170');
oCMenu.makeMenu('30540','20830','(#)OLAP - Supprimer Cubes Locaux','olap.php?val=pop_olap_del_cube','','170');
oCMenu.makeMenu('20670','2040','(#)Gestion des chaînes Loc','administration.php?val=gestchaineloc','','150');
oCMenu.makeMenu('20740','2040','(#)Edition Params GLOBALS','administration.php?val=params_global','','150');
oCMenu.makeMenu('20760','2040','(#)Attribution Plage - Codes','administration.php?val=gest_plages','','150');
oCMenu.makeMenu('30555','2040','(#)Fixer une Localité','administration.php?val=fix_regroup&id_user=0','','150');
oCMenu.makeMenu('2030','0','(#)Tableau Synthèse','synthese.php','','120');
oCMenu.makeMenu('2060','0','(#)Help','','','50');
oCMenu.makeMenu('2070','0','(#)Quitter','accueil.php?val=logout','','50');

//Leave this line - it constructs the menu
            oCMenu.construct();