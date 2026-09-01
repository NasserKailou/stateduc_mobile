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
oCMenu.makeMenu('4007','1030','Localisation du Centre','questionnaire.php?theme=4007&type_ent_stat=1','','200');
oCMenu.makeMenu('4107','1030','Informations Générales','questionnaire.php?theme=4107&type_ent_stat=1','','200');
oCMenu.makeMenu('4207','1030','Locaux','questionnaire.php?theme=4207&type_ent_stat=1','','200');
oCMenu.makeMenu('4307','1030','Salles & Equip. par Niveau','questionnaire.php?theme=4307&type_ent_stat=1','','200');
oCMenu.makeMenu('4407','1030','Equipements TIC','questionnaire.php?theme=4407&type_ent_stat=1','','200');
oCMenu.makeMenu('4507','1030','Equipement TECH','questionnaire.php?theme=4507&type_ent_stat=1','','200');
oCMenu.makeMenu('8607','1030','Manuels par filière','questionnaire.php?theme=8607&type_ent_stat=1','','200');
oCMenu.makeMenu('4607','1030','Apprenants par Niveau & Filière & Nationalité','questionnaire.php?theme=4607&type_ent_stat=1','','200');
oCMenu.makeMenu('4707','1030','Apprenants par niveau & Age','questionnaire.php?theme=4707&type_ent_stat=1','','200');
oCMenu.makeMenu('4807','1030','Examen - cycle normal','questionnaire.php?theme=4807&type_ent_stat=1','','200');
oCMenu.makeMenu('4907','1030','Examen - cycle modulaire','questionnaire.php?theme=4907&type_ent_stat=1','','200');
oCMenu.makeMenu('5007','1030','Insertion des lauréats  ','questionnaire.php?theme=5007&type_ent_stat=1','','200');
oCMenu.makeMenu('5107','1030','Formateurs par qualif & nat','questionnaire.php?theme=5107&type_ent_stat=1','','200');
oCMenu.makeMenu('5207','1030','Accompagnateurs sociaux','questionnaire.php?theme=5207&type_ent_stat=1','','200');
oCMenu.makeMenu('5307','1030','Pers. adm. & accomp. & maint.','questionnaire.php?theme=5307&type_ent_stat=1','','200');
oCMenu.makeMenu('8707','1030','Liste nominative du personnel','questionnaire.php?theme=8707&type_ent_stat=1','','200');
oCMenu.makeMenu('8807','1030','Données Protection Urgence Financières','questionnaire.php?theme=8807&type_ent_stat=1','','200');

//Leave this line - it constructs the menu
            oCMenu.construct();