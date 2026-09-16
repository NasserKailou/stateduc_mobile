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
oCMenu.makeMenu('1010','0','Home','accueil.php','','80');
oCMenu.makeMenu('1060','0','Select a Sector','saisie_donnees.php?val=choix_sys_saisie','','130');
oCMenu.makeMenu('1020','0','Select a School','saisie_donnees.php?val=choix_etablissement','','130');
oCMenu.makeMenu('1030','0','Questionnaires','questionnaire.php','','130');
oCMenu.makeMenu('1040','0','Quit','?val=logout','','42');
oCMenu.makeMenu('101','1030','School Identification','questionnaire.php?theme=101&type_ent_stat=1','','180');
oCMenu.makeMenu('2101','1030','Enrolment','#','','180');
oCMenu.makeMenu('7401','1030','Teaching Staff','#','','180');
oCMenu.makeMenu('15501','7401','Teachers For Pre-primary','questionnaire.php?theme=15501&type_ent_stat=1','','180');
oCMenu.makeMenu('15401','7401','Teachers by Age','questionnaire.php?theme=15401&type_ent_stat=1','','180');
oCMenu.makeMenu('9001','7401','Teachers Available','questionnaire.php?theme=9001&type_ent_stat=1','','180');
oCMenu.makeMenu('11101','7401','Teachers Pre Certificate','questionnaire.php?theme=11101&type_ent_stat=1','','180');
oCMenu.makeMenu('7601','7401','Teachers Special Education','questionnaire.php?theme=7601&type_ent_stat=1','','180');
oCMenu.makeMenu('8001','7401','Teachers Absenteeism','questionnaire.php?theme=8001&type_ent_stat=1','','180');
oCMenu.makeMenu('8101','1030','General Information','#','','180');
oCMenu.makeMenu('8801','8101','Services Available','questionnaire.php?theme=8801&type_ent_stat=1','','180');
oCMenu.makeMenu('8401','1030','Buildings','questionnaire.php?theme=8401&type_ent_stat=1','','180');
oCMenu.makeMenu('13401','1030','Furniture','questionnaire.php?theme=13401&type_ent_stat=1','','180');
oCMenu.makeMenu('11301','1030','Sports Facilities','questionnaire.php?theme=11301&type_ent_stat=1','','180');
oCMenu.makeMenu('11501','1030','Financial Statement','questionnaire.php?theme=11501&type_ent_stat=1','','180');
oCMenu.makeMenu('2501','1030','Enrolment by Grade ','questionnaire.php?theme=2501&type_ent_stat=1','','180');

//Leave this line - it constructs the menu
            oCMenu.construct();