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
            oCMenu.menuPlacement=0  // permet de contr�ler (ou bloquer) la position � gauche pour �viter
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
                                                    
            // Param�tres de cm_makeLevel(width, height, regClass, overClass, borderX, borderY, borderClass, rows, align, offsetX, offsetY, arrow, arrowWidth, arrowHeight, roundBorder)
            oCMenu.level[0]=new cm_makeLevel(90,21,"clT","clTover",1,1,"clB",0,"bottom",0,0,0,0,0);
            oCMenu.level[2]=new cm_makeLevel(110,22,"clS2","clS2over");
            oCMenu.level[3]=new cm_makeLevel(140,22);oCMenu.fromLeft=175;
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('1010','0','Accueil','accueil.php','','80');
oCMenu.makeMenu('1060','0','Choisir un Secteur','saisie_donnees.php?val=choix_sys_saisie','','130');
oCMenu.makeMenu('1020','0','Choix Etablissement','saisie_donnees.php?val=choix_etablissement','','130');
oCMenu.makeMenu('1030','0','Questionnaires','','','130');
oCMenu.makeMenu('1050','0','Ajout Etablissement','saisie_donnees.php?val=new_etab&theme=101','','155');
oCMenu.makeMenu('1040','0','Quitter','?val=logout','','42');
oCMenu.makeMenu('3205','1030','Identification & Localisation','questionnaire.php?theme=3205&type_ent_stat=1','','200');
oCMenu.makeMenu('3305','1030','Données Générales','questionnaire.php?theme=3305&type_ent_stat=1','','200');
oCMenu.makeMenu('7305','1030','Classes & Mobiliers','questionnaire.php?theme=7305&type_ent_stat=1','','200');
oCMenu.makeMenu('3505','1030','Equipement','questionnaire.php?theme=3505&type_ent_stat=1','','200');
oCMenu.makeMenu('6605','1030','Elèves par Nationalité','questionnaire.php?theme=6605&type_ent_stat=1','','200');
oCMenu.makeMenu('3605','1030','Elèves par age','questionnaire.php?theme=3605&type_ent_stat=1','','200');
oCMenu.makeMenu('7405','1030','Résultats au examens','questionnaire.php?theme=7405&type_ent_stat=1','','200');
oCMenu.makeMenu('7505','1030','Elèves Internes','questionnaire.php?theme=7505&type_ent_stat=1','','200');
oCMenu.makeMenu('3705','1030','Infos sur le personnel(1)','questionnaire.php?theme=3705&type_ent_stat=1','','200');
oCMenu.makeMenu('3805','1030','Infos sur le personnel(2)','questionnaire.php?theme=3805&type_ent_stat=1','','200');

//Leave this line - it constructs the menu
            oCMenu.construct();