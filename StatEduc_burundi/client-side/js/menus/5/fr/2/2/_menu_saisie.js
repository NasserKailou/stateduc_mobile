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
oCMenu.makeMenu('15602','1030','Identification Etablissement','questionnaire.php?theme=15602&type_ent_stat=2','','220');
oCMenu.makeMenu('15702','1030','Infos Generales - Ecole (1)','questionnaire.php?theme=15702&type_ent_stat=2','','220');
oCMenu.makeMenu('15802','1030','Infos Generales - Ecole (2)','questionnaire.php?theme=15802&type_ent_stat=2','','220');
oCMenu.makeMenu('15902','1030','Infos Generales - Ecole (3)','questionnaire.php?theme=15902&type_ent_stat=2','','220');
oCMenu.makeMenu('16002','1030','Infos Generales - Ecole (4)','questionnaire.php?theme=16002&type_ent_stat=2','','220');
oCMenu.makeMenu('16202','1030','Caracteristiques Salles de Classe','questionnaire.php?theme=16202&type_ent_stat=2','','220');
oCMenu.makeMenu('16302','1030','Recapitulatif Salless Classe','questionnaire.php?theme=16302&type_ent_stat=2','','220');
oCMenu.makeMenu('16402','1030','Mobilier et Equipement','questionnaire.php?theme=16402&type_ent_stat=2','','220');
oCMenu.makeMenu('16502','1030','Equipements Didactiques (1)','questionnaire.php?theme=16502&type_ent_stat=2','','220');
oCMenu.makeMenu('16602','1030','Equipements Didactiques (2)','questionnaire.php?theme=16602&type_ent_stat=2','','220');
oCMenu.makeMenu('16702','1030','Equipements Didactiques (3)','questionnaire.php?theme=16702&type_ent_stat=2','','220');
oCMenu.makeMenu('16902','1030','Informations Relatives aux Enseignants','questionnaire.php?theme=16902&type_ent_stat=2','','220');
oCMenu.makeMenu('17002','1030','Recapitulatif Enseignants','questionnaire.php?theme=17002&type_ent_stat=2','','220');
oCMenu.makeMenu('17202','1030','Nvx Inscrits en 1ere annee','questionnaire.php?theme=17202&type_ent_stat=2','','220');
oCMenu.makeMenu('17302','1030','Effectifs GP par age','questionnaire.php?theme=17302&type_ent_stat=2','','220');
oCMenu.makeMenu('17402','1030','Aire de Recrutement','questionnaire.php?theme=17402&type_ent_stat=2','','220');
oCMenu.makeMenu('17802','1030','Structure Pedag et Effectifs Eleves','questionnaire.php?theme=17802&type_ent_stat=2','','220');
oCMenu.makeMenu('17502','1030','Effectifs Eleves Provenant Passerelle','questionnaire.php?theme=17502&type_ent_stat=2','','220');
oCMenu.makeMenu('17902','1030','Effectifs Eleves Provenant Coranique','questionnaire.php?theme=17902&type_ent_stat=2','','220');
oCMenu.makeMenu('18002','1030','Effectifs des Eleves Refugies','questionnaire.php?theme=18002&type_ent_stat=2','','220');
oCMenu.makeMenu('17602','1030','Resultats aux Examens et Handicap','questionnaire.php?theme=17602&type_ent_stat=2','','220');
oCMenu.makeMenu('17702','1030','Donnees Financieres','questionnaire.php?theme=17702&type_ent_stat=2','','220');

//Leave this line - it constructs the menu
            oCMenu.construct();