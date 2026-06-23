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
oCMenu.makeMenu('1050','0','Add a School','saisie_donnees.php?val=new_etab&theme=101','','155');
oCMenu.makeMenu('1040','0','Quit','?val=logout','','42');
oCMenu.makeMenu('105','1030','Centre Identification','questionnaire.php?theme=105&type_ent_stat=1','','180');
oCMenu.makeMenu('2105','1030','Enrolment','#','','180');
oCMenu.makeMenu('11605','2105','Enrolment COBET COHORT I','questionnaire.php?theme=11605&type_ent_stat=1','','180');
oCMenu.makeMenu('11705','2105','Enrolment COBET COHORT II','questionnaire.php?theme=11705&type_ent_stat=1','','180');
oCMenu.makeMenu('11805','2105','Dropouts COBET COHORT I','questionnaire.php?theme=11805&type_ent_stat=1','','180');
oCMenu.makeMenu('11905','2105','Dropouts COBET COHORT II','questionnaire.php?theme=11905&type_ent_stat=1','','180');
oCMenu.makeMenu('16805','2105','Enrolment Disabled Cohort I','questionnaire.php?theme=16805&type_ent_stat=1','','180');
oCMenu.makeMenu('16905','2105','Enrolment Disabled Cohort II','questionnaire.php?theme=16905&type_ent_stat=1','','180');
oCMenu.makeMenu('18705','2105','Orphans Cohort I','questionnaire.php?theme=18705&type_ent_stat=1','','180');
oCMenu.makeMenu('18805','2105','Orphans Cohort II','questionnaire.php?theme=18805&type_ent_stat=1','','180');
oCMenu.makeMenu('18905','2105','Attendance Cohort I','questionnaire.php?theme=18905&type_ent_stat=1','','180');
oCMenu.makeMenu('19005','2105','Attendance Cohort II','questionnaire.php?theme=19005&type_ent_stat=1','','180');
oCMenu.makeMenu('19205','2105','Vulnerable Cohort I','questionnaire.php?theme=19205&type_ent_stat=1','','180');
oCMenu.makeMenu('19305','2105','Vulnerable Cohort II','questionnaire.php?theme=19305&type_ent_stat=1','','180');
oCMenu.makeMenu('10205','2105','Vulnerable Supported Cohort I','questionnaire.php?theme=10205&type_ent_stat=1','','180');
oCMenu.makeMenu('17005','2105','Vulnerable Supported Cohort II','questionnaire.php?theme=17005&type_ent_stat=1','','180');
oCMenu.makeMenu('9405','2105','Lunch Information','questionnaire.php?theme=9405&type_ent_stat=1','','180');
oCMenu.makeMenu('10305','2105','Enrolment Distance Cohort I','questionnaire.php?theme=10305&type_ent_stat=1','','180');
oCMenu.makeMenu('17105','2105','Enrolment Distance Cohort II','questionnaire.php?theme=17105&type_ent_stat=1','','180');
oCMenu.makeMenu('17205','2105','Peer Education Cohort I','questionnaire.php?theme=17205&type_ent_stat=1','','180');
oCMenu.makeMenu('17305','2105','Peer Education Cohort II','questionnaire.php?theme=17305&type_ent_stat=1','','180');
oCMenu.makeMenu('17405','2105','Counseling Cohort I','questionnaire.php?theme=17405&type_ent_stat=1','','180');
oCMenu.makeMenu('17505','2105','Counseling Cohort II','questionnaire.php?theme=17505&type_ent_stat=1','','180');
oCMenu.makeMenu('17605','2105','Cohort I HIV/AIDS','questionnaire.php?theme=17605&type_ent_stat=1','','180');
oCMenu.makeMenu('17705','2105','Cohort II HIV/AIDS','questionnaire.php?theme=17705&type_ent_stat=1','','180');
oCMenu.makeMenu('8505','2105','Furniture','questionnaire.php?theme=8505&type_ent_stat=1','','180');
oCMenu.makeMenu('12005','2105','Enrolment Formal Edu Primary','questionnaire.php?theme=12005&type_ent_stat=1','','180');
oCMenu.makeMenu('12105','2105','Enrolment Formal Edu Secondary','questionnaire.php?theme=12105&type_ent_stat=1','','180');
oCMenu.makeMenu('12305','1030','COBET Facilitators','questionnaire.php?theme=12305&type_ent_stat=1','','180');
oCMenu.makeMenu('10405','1030','Facilitators AIDS','questionnaire.php?theme=10405&type_ent_stat=1','','180');
oCMenu.makeMenu('10505','1030','Facilitators Counseling','questionnaire.php?theme=10505&type_ent_stat=1','','180');
oCMenu.makeMenu('12405','1030','COBET Books','questionnaire.php?theme=12405&type_ent_stat=1','','180');
oCMenu.makeMenu('17805','1030','COBET Guide','questionnaire.php?theme=17805&type_ent_stat=1','','180');
oCMenu.makeMenu('17905','1030','ICBAE Enrolment','#','','180');
oCMenu.makeMenu('12505','17905','Enrolment ICBAE ','questionnaire.php?theme=12505&type_ent_stat=1','','180');
oCMenu.makeMenu('18005','17905','ICBAE Disabled','questionnaire.php?theme=18005&type_ent_stat=1','','180');
oCMenu.makeMenu('18105','17905','Peer Education ICBAE','questionnaire.php?theme=18105&type_ent_stat=1','','180');
oCMenu.makeMenu('18205','17905','ICBAE HIV/AIDS','questionnaire.php?theme=18205&type_ent_stat=1','','180');
oCMenu.makeMenu('18305','17905','ICBAE Counseling','questionnaire.php?theme=18305&type_ent_stat=1','','180');
oCMenu.makeMenu('12605','1030','ICBAE Facilitators','questionnaire.php?theme=12605&type_ent_stat=1','','180');
oCMenu.makeMenu('12705','1030','ICBAE Classes','questionnaire.php?theme=12705&type_ent_stat=1','','180');
oCMenu.makeMenu('18405','1030','ICBAE Buildings','questionnaire.php?theme=18405&type_ent_stat=1','','180');
oCMenu.makeMenu('12905','1030','Library','questionnaire.php?theme=12905&type_ent_stat=1','','180');
oCMenu.makeMenu('15205','1030','Program Guides','questionnaire.php?theme=15205&type_ent_stat=1','','180');
oCMenu.makeMenu('18505','1030','ODL Enrolment','#','','180');
oCMenu.makeMenu('15305','18505','Enrolment Level ODL','questionnaire.php?theme=15305&type_ent_stat=1','','180');

//Leave this line - it constructs the menu
            oCMenu.construct();