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
            oCMenu.level[3]=new cm_makeLevel(140,22);oCMenu.fromLeft=206;
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('30010','0','Home','accueil.php','','80');
oCMenu.makeMenu('30050','0','Select a Sector','saisie_donnees.php?val=choix_sys_rpt','','130');
oCMenu.makeMenu('30040','0','Reports Management','synthese.php','','180');
oCMenu.makeMenu('20850','30040','Reports Management','synthese.php?val=gest_rpt','','160');
oCMenu.makeMenu('20860','30040','Measures Management','synthese.php?val=gest_mes','','160');
oCMenu.makeMenu('30060','30040','Criteria Management','synthese.php?val=criteres','','160');
oCMenu.makeMenu('20870','30040','Dimensions Management','synthese.php?val=gest_dim','','160');
oCMenu.makeMenu('20880','30040','Aggregations Management','synthese.php?val=gest_agg','','160');
oCMenu.makeMenu('30030','0','Reports List','','','180');
oCMenu.makeMenu('30020','0','Quit','?val=logout','','42');
oCMenu.makeMenu('322','30030','At a glance','synthese.php?val=list_rpt&id_rpt=32&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('302','322','At a glance report I','synthese.php?val=list_rpt&id_rpt=30&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('312','322','At a glance report II','synthese.php?val=list_rpt&id_rpt=31&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('12','30030','School Supervision Reports','synthese.php?val=list_rpt&id_rpt=1&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('22','12','SSR: Management and Administration','synthese.php?val=list_rpt&id_rpt=2&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('162','12','SSR: Teaching and Learning Environment','synthese.php?val=list_rpt&id_rpt=16&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('172','12','SSR: Materials and Equipment','synthese.php?val=list_rpt&id_rpt=17&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('182','12','SSR: Headteachers Feedback','synthese.php?val=list_rpt&id_rpt=18&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('192','12','SSR: School Discipline','synthese.php?val=list_rpt&id_rpt=19&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('202','12','SSR: Pupil Expulsion by reason','synthese.php?val=list_rpt&id_rpt=20&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('42','12','SSR: Teacher Attendance Report','synthese.php?val=list_rpt&id_rpt=4&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('242','12','SSR: Revenue by Source','synthese.php?val=list_rpt&id_rpt=24&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('252','12','SSR: Expenditure by Source','synthese.php?val=list_rpt&id_rpt=25&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('32','12','SSR: Contribution in Kind','synthese.php?val=list_rpt&id_rpt=3&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('212','12','SSR: School Finance Capitation Grant','synthese.php?val=list_rpt&id_rpt=21&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('52','12','SSR: School Finance Report Breakdown Expenditure','synthese.php?val=list_rpt&id_rpt=5&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('332','30030','School Inspection Reports','synthese.php?val=list_rpt&id_rpt=33&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('262','332','SIR : Quality of teaching - learning','synthese.php?val=list_rpt&id_rpt=26&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('292','332','SIR: Benchmarks: Quality of learning is observed','synthese.php?val=list_rpt&id_rpt=29&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('272','332','SIR: Teachers Feedback','synthese.php?val=list_rpt&id_rpt=27&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('282','332','SIR: Continuous Assessment','synthese.php?val=list_rpt&id_rpt=28&type_rpt=5&id_syst=2','','270');
oCMenu.makeMenu('62','30030','District Inspection Reports','synthese.php?val=list_rpt&id_rpt=6&type_rpt=4&id_syst=2','','270');
oCMenu.makeMenu('82','62','DIR : Quality of teaching - learning','synthese.php?val=list_rpt&id_rpt=8&type_rpt=4&id_syst=2','','270');
oCMenu.makeMenu('92','62','DIR : Teachers feedback','synthese.php?val=list_rpt&id_rpt=9&type_rpt=4&id_syst=2','','270');
oCMenu.makeMenu('102','62','DIR: Key Benchmarks: Quality of teaching - learning','synthese.php?val=list_rpt&id_rpt=10&type_rpt=4&id_syst=2','','270');
oCMenu.makeMenu('112','62','DIR: Key Benchmarks: Teachers feedback','synthese.php?val=list_rpt&id_rpt=11&type_rpt=4&id_syst=2','','270');
oCMenu.makeMenu('222','30030','DSR: School Finance Capitation Grant','synthese.php?val=list_rpt&id_rpt=22&type_rpt=&id_syst=2','','270');

//Leave this line - it constructs the menu
            oCMenu.construct();