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
oCMenu.makeMenu('1020','0','Choix Etablissement','','','130');
oCMenu.makeMenu('1030','0','Questionnaires','','','130');
oCMenu.makeMenu('1050','0','Ajout Etablissement','saisie_donnees.php?val=new_etab&theme=101','','155');
oCMenu.makeMenu('1040','0','Quitter','?val=logout','','42');
oCMenu.makeMenu('13404','1030','Identification','questionnaire.php?theme=13404&type_ent_stat=1','','220');
oCMenu.makeMenu('13504','1030','Environnement','questionnaire.php?theme=13504&type_ent_stat=1','','220');
oCMenu.makeMenu('13604','1030','INFO GENERALE','questionnaire.php?theme=13604&type_ent_stat=1','','220');
oCMenu.makeMenu('13704','1030','Locaux','questionnaire.php?theme=13704&type_ent_stat=1','','220');
oCMenu.makeMenu('13804','1030','Mobilier Equipement','questionnaire.php?theme=13804&type_ent_stat=1','','220');
oCMenu.makeMenu('13904','1030','Equipement Didactique','questionnaire.php?theme=13904&type_ent_stat=1','','220');
oCMenu.makeMenu('14004','1030','Personnels','questionnaire.php?theme=14004&type_ent_stat=1','','220');
oCMenu.makeMenu('14204','1030','Répartition des apprenants inscrits en début année','#','','220');
oCMenu.makeMenu('14304','14204','Education Non Formelle debut campagne','questionnaire.php?theme=14304&type_ent_stat=1','','220');
oCMenu.makeMenu('14404','14204',' Alphabétisation  debut campagne','questionnaire.php?theme=14404&type_ent_stat=1','','220');
oCMenu.makeMenu('14504','1030','Apprenants en fin de campagne/année','#','','220');
oCMenu.makeMenu('14604','14504','Education Non Formelle Fin campagne','questionnaire.php?theme=14604&type_ent_stat=1','','220');
oCMenu.makeMenu('14704','14504','Alphabétisation Fin campagne','questionnaire.php?theme=14704&type_ent_stat=1','','220');
oCMenu.makeMenu('14904','1030','Résultats en fin de campagne/année ','questionnaire.php?theme=14904&type_ent_stat=1','','220');

//Leave this line - it constructs the menu
            oCMenu.construct();