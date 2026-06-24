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
oCMenu.makeMenu('2404','1030','Identification & Localisation','questionnaire.php?theme=2404&type_ent_stat=1','','200');
oCMenu.makeMenu('2504','1030','Informations Générales','questionnaire.php?theme=2504&type_ent_stat=1','','200');
oCMenu.makeMenu('6804','1030','Salles de classe & Mobiliers','questionnaire.php?theme=6804&type_ent_stat=1','','200');
oCMenu.makeMenu('7004','1030','Equipements','questionnaire.php?theme=7004&type_ent_stat=1','','200');
oCMenu.makeMenu('7104','1030','Effectif des élèves et Red','questionnaire.php?theme=7104&type_ent_stat=1','','200');
oCMenu.makeMenu('2804','1030','Effectif des élèves par âge','questionnaire.php?theme=2804&type_ent_stat=1','','200');
oCMenu.makeMenu('8304','1030','Résultats aux examens(1)','questionnaire.php?theme=8304&type_ent_stat=1','','200');
oCMenu.makeMenu('6904','1030','Résultats aux examens(2)','questionnaire.php?theme=6904&type_ent_stat=1','','200');
oCMenu.makeMenu('7204','1030','Elèves internes','questionnaire.php?theme=7204&type_ent_stat=1','','200');
oCMenu.makeMenu('8404','1030','TechA2 Données financières','questionnaire.php?theme=8404&type_ent_stat=1','','200');
oCMenu.makeMenu('2904','1030','Infos sur le personnel(1)','questionnaire.php?theme=2904&type_ent_stat=1','','200');
oCMenu.makeMenu('3004','1030','Infos sur le personnel(2)','questionnaire.php?theme=3004&type_ent_stat=1','','200');
oCMenu.makeMenu('7904','1030','Liste personnel','questionnaire.php?theme=7904&type_ent_stat=1','','200');

//Leave this line - it constructs the menu
            oCMenu.construct();