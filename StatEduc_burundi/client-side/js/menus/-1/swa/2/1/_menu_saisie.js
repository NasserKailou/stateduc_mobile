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
oCMenu.makeMenu('102','1030','(#)District Identification','questionnaire.php?theme=102&type_ent_stat=1','','250');
oCMenu.makeMenu('2102','1030','(#)Enrolment','#','','250');
oCMenu.makeMenu('13002','2102','(#)Nouveau','questionnaire.php?theme=13002&type_ent_stat=1','','250');
oCMenu.makeMenu('15702','2102','(#)Nouveau','questionnaire.php?theme=15702&type_ent_stat=1','','250');
oCMenu.makeMenu('7202','2102','(#)Enrol NonCitizens','questionnaire.php?theme=7202&type_ent_stat=1','','250');
oCMenu.makeMenu('9902','2102','(#)Entrants Std I','questionnaire.php?theme=9902&type_ent_stat=1','','250');
oCMenu.makeMenu('10002','2102','(#)Entrants Std I - PreSchool','questionnaire.php?theme=10002&type_ent_stat=1','','250');
oCMenu.makeMenu('6802','2102','(#)Enrol Repeaters Grade 1','questionnaire.php?theme=6802&type_ent_stat=1','','250');
oCMenu.makeMenu('6902','2102','(#)Enrol Repeaters Grade','questionnaire.php?theme=6902&type_ent_stat=1','','250');
oCMenu.makeMenu('15102','2102','(#)Nouveau','questionnaire.php?theme=15102&type_ent_stat=1','','250');
oCMenu.makeMenu('7002','2102','(#)Enrol Disabled','questionnaire.php?theme=7002&type_ent_stat=1','','250');
oCMenu.makeMenu('6702','2102','(#)Enrolment Dropouts','questionnaire.php?theme=6702&type_ent_stat=1','','250');
oCMenu.makeMenu('7102','2102','(#)Nouveau','questionnaire.php?theme=7102&type_ent_stat=1','','250');
oCMenu.makeMenu('15802','2102','(#)Nouveau','questionnaire.php?theme=15802&type_ent_stat=1','','250');
oCMenu.makeMenu('9402','2102','(#)Lunch Information','questionnaire.php?theme=9402&type_ent_stat=1','','250');
oCMenu.makeMenu('10102','2102','(#)Enrolment Vulnerables','questionnaire.php?theme=10102&type_ent_stat=1','','250');
oCMenu.makeMenu('10202','2102','(#)Vulnerables Support','questionnaire.php?theme=10202&type_ent_stat=1','','250');
oCMenu.makeMenu('10302','2102','(#)Pupils Distance','questionnaire.php?theme=10302&type_ent_stat=1','','250');
oCMenu.makeMenu('15902','2102','(#)Nouveau','questionnaire.php?theme=15902&type_ent_stat=1','','250');
oCMenu.makeMenu('16002','2102','(#)Nouveau','questionnaire.php?theme=16002&type_ent_stat=1','','250');
oCMenu.makeMenu('16102','2102','(#)Nouveau','questionnaire.php?theme=16102&type_ent_stat=1','','250');
oCMenu.makeMenu('7402','1030','(#)Teaching Staff','#','','250');
oCMenu.makeMenu('9002','7402','(#)Teachers Available','questionnaire.php?theme=9002&type_ent_stat=1','','250');
oCMenu.makeMenu('15402','7402','(#)Nouveau','questionnaire.php?theme=15402&type_ent_stat=1','','250');
oCMenu.makeMenu('7502','7402','(#)Teachers Employed','questionnaire.php?theme=7502&type_ent_stat=1','','250');
oCMenu.makeMenu('10402','7402','(#)Teachers for AIDS','questionnaire.php?theme=10402&type_ent_stat=1','','250');
oCMenu.makeMenu('16202','7402','(#)Nouveau','questionnaire.php?theme=16202&type_ent_stat=1','','250');
oCMenu.makeMenu('10502','7402','(#)Teachers Counseling','questionnaire.php?theme=10502&type_ent_stat=1','','250');
oCMenu.makeMenu('13102','7402','(#)Nouveau','questionnaire.php?theme=13102&type_ent_stat=1','','250');
oCMenu.makeMenu('10602','7402','(#)Teachers Disability','questionnaire.php?theme=10602&type_ent_stat=1','','250');
oCMenu.makeMenu('1102','1030','(#)Teachers Attrition','#','','250');
oCMenu.makeMenu('7702','1102','(#)Teachers Retired','questionnaire.php?theme=7702&type_ent_stat=1','','250');
oCMenu.makeMenu('7802','1102','(#)Teachers Dead','questionnaire.php?theme=7802&type_ent_stat=1','','250');
oCMenu.makeMenu('7902','1102','(#)Teachers Terminated','questionnaire.php?theme=7902&type_ent_stat=1','','250');
oCMenu.makeMenu('8002','1102','(#)Teachers Absent','questionnaire.php?theme=8002&type_ent_stat=1','','250');
oCMenu.makeMenu('16302','1030','(#)Nouveau','questionnaire.php?theme=16302&type_ent_stat=1','','250');
oCMenu.makeMenu('8102','1030','(#)General Information','#','','250');
oCMenu.makeMenu('8802','8102','(#)Services','questionnaire.php?theme=8802&type_ent_stat=1','','250');
oCMenu.makeMenu('8402','1030','(#)Buildings','questionnaire.php?theme=8402&type_ent_stat=1','','250');
oCMenu.makeMenu('16402','1030','(#)Nouveau','questionnaire.php?theme=16402&type_ent_stat=1','','250');
oCMenu.makeMenu('8502','1030','(#)Furniture','questionnaire.php?theme=8502&type_ent_stat=1','','250');
oCMenu.makeMenu('16502','1030','(#)Nouveau','questionnaire.php?theme=16502&type_ent_stat=1','','250');
oCMenu.makeMenu('9602','1030','(#)Teaching and Learning Facilities','#','','250');
oCMenu.makeMenu('8602','9602','(#)Teaching books','questionnaire.php?theme=8602&type_ent_stat=1','','250');
oCMenu.makeMenu('16702','9602','(#)Nouveau','questionnaire.php?theme=16702&type_ent_stat=1','','250');
oCMenu.makeMenu('10702','9602','(#)Nouveau','questionnaire.php?theme=10702&type_ent_stat=1','','250');
oCMenu.makeMenu('10802','1030','(#)Special Edu Facilities','questionnaire.php?theme=10802&type_ent_stat=1','','250');
oCMenu.makeMenu('8702','1030','(#)Financial Information','questionnaire.php?theme=8702&type_ent_stat=1','','250');
oCMenu.makeMenu('15602','1030','(#)Nouveau','questionnaire.php?theme=15602&type_ent_stat=1','','250');

//Leave this line - it constructs the menu
            oCMenu.construct();