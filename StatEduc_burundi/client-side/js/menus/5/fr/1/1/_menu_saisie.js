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
            oCMenu.level[3]=new cm_makeLevel(140,22);oCMenu.fromLeft=175;
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('2901','1030','Identification','questionnaire.php?theme=2901&type_ent_stat=1','','220');
oCMenu.makeMenu('3001','1030','Infomations Générales','questionnaire.php?theme=3001&type_ent_stat=1','','220');
oCMenu.makeMenu('7501','1030','Caractéristiques des locaux','questionnaire.php?theme=7501&type_ent_stat=1','','220');
oCMenu.makeMenu('6101','1030','Récap Salles de classe','questionnaire.php?theme=6101&type_ent_stat=1','','220');
oCMenu.makeMenu('7901','1030','Mobilier et Equipement','questionnaire.php?theme=7901&type_ent_stat=1','','220');
oCMenu.makeMenu('7801','1030','Informations - Personnel','questionnaire.php?theme=7801&type_ent_stat=1','','220');
oCMenu.makeMenu('8101','1030','Récapitulatif Enseignant ','questionnaire.php?theme=8101&type_ent_stat=1','','220');
oCMenu.makeMenu('7701','1030','Effectifs GP par age','questionnaire.php?theme=7701&type_ent_stat=1','','220');
oCMenu.makeMenu('13301','1030','Handicap','questionnaire.php?theme=13301&type_ent_stat=1','','220');
oCMenu.makeMenu('15501','1030','Réfugies','questionnaire.php?theme=15501&type_ent_stat=1','','220');
oCMenu.makeMenu('13101','1030','Coordonnés des responsables locaux de la collecte de données','questionnaire.php?theme=13101&type_ent_stat=1','','220');

//Leave this line - it constructs the menu
            oCMenu.construct();