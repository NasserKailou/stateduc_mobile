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
            oCMenu.level[3]=new cm_makeLevel(140,22);oCMenu.fromLeft=202;
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('30420','0','(#)Accueil','accueil.php','','100');
oCMenu.makeMenu('30425','0','(#)Rafraichir/Traiter Cubes Serveur','olap.php?val=refresh_cubes_server2','','200');
oCMenu.makeMenu('30430','0','(#)Liste des Cubes','','','160');
oCMenu.makeMenu('30560','30430','(#)School Identification','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Schools Identification','','130');
oCMenu.makeMenu('30570','30430','(#)Enrolment1','','','130');
oCMenu.makeMenu('30680','30570','(#)Enrolment Grade Age','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Enrolment Grade Age','','160');
oCMenu.makeMenu('31400','30570','(#)Streams','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Streams','','160');
oCMenu.makeMenu('31410','30570','(#)Shifts','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Shifts','','160');
oCMenu.makeMenu('30690','30570','(#)Non Citizen','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Enrolment_non_citizen','','160');
oCMenu.makeMenu('30700','30570','(#)New entrants in STD1','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=New entering Primary','','160');
oCMenu.makeMenu('30710','30570','(#)New entrants STD1 Pre Primary','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=New entering Primary','','160');
oCMenu.makeMenu('30720','30570','(#)Repeaters SDT1 by Age','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Repeaters G1 Age','','160');
oCMenu.makeMenu('30730','30570','(#)Repeaters by Grade','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Repeaters','','160');
oCMenu.makeMenu('30740','30570','(#)COBET Mainstreamed','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=COBET Mainstreamed','','160');
oCMenu.makeMenu('30750','30570','(#)Disabled by Grade','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Disabled','','160');
oCMenu.makeMenu('30760','30570','(#)Dropouts by Grade','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Dropouts','','160');
oCMenu.makeMenu('30770','30570','(#)Orphans by Grade','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Orphans','','160');
oCMenu.makeMenu('30780','30570','(#)Vulnerable pupils','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Vulnerable','','160');
oCMenu.makeMenu('30790','30570','(#)Vulnerable supported pupils','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Vulnerable supported','','160');
oCMenu.makeMenu('30800','30570','(#)Food programs','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Food programs','','160');
oCMenu.makeMenu('30810','30570','(#)Enrolment by distance','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Enrolment_distance','','160');
oCMenu.makeMenu('30820','30570','(#)Capacity (Pre-primary)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Enrolment_capacity','','160');
oCMenu.makeMenu('30580','30430','(#)Teaching Staff','','','130');
oCMenu.makeMenu('30830','30580','(#)Teachers available','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers by qualification','','150');
oCMenu.makeMenu('30840','30580','(#)Teachers employed  last year','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_employed','','150');
oCMenu.makeMenu('30850','30580','(#)Teachers Edu against AIDS','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_AIDS','','150');
oCMenu.makeMenu('30860','30580','(#)Teachers counseling','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_Counseling','','150');
oCMenu.makeMenu('31290','30580','(#)Teachers for disabled','','','150');
oCMenu.makeMenu('30890','31290','(#)Teachers trained for disable','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers Trained for Disabilities','','170');
oCMenu.makeMenu('30900','31290','(#)Teachers not trained for disable','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers Not Trained for Disabilities','','170');
oCMenu.makeMenu('30880','30580','(#)Teachers with disability','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_disabled','','150');
oCMenu.makeMenu('31280','30580','(#)Pre certificate','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers precetrificated','','150');
oCMenu.makeMenu('30590','30430','(#)Teachers Attrition','','','130');
oCMenu.makeMenu('30920','30590','(#)Teachers retired','','','150');
oCMenu.makeMenu('30960','30920','(#)Teachers retired (All)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_retired','','150');
oCMenu.makeMenu('30970','30920','(#)Teachers retired (Disability)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_retired_disability','','150');
oCMenu.makeMenu('31040','30920','(#)Teachers retired (Others)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_retired_others','','150');
oCMenu.makeMenu('30930','30590','(#)Teachers dead','','','150');
oCMenu.makeMenu('30980','30930','(#)Teachers dead (All)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_Dead','','150');
oCMenu.makeMenu('30990','30930','(#)Teachers dead (Disability)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_Dead_disability','','150');
oCMenu.makeMenu('31000','30930','(#)Teachers dead (Others)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_Dead_others','','150');
oCMenu.makeMenu('30940','30590','(#)Teachers terminated','','','150');
oCMenu.makeMenu('31010','30940','(#)Teachers terminated (All)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_terminated','','150');
oCMenu.makeMenu('31020','30940','(#)Teachers terminated (disablity)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_terminated_disability','','150');
oCMenu.makeMenu('31030','30940','(#)Teachers terminated (others)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_terminated_others','','150');
oCMenu.makeMenu('30950','30590','(#)Teachers Absenteism','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teachers_absenteism','','150');
oCMenu.makeMenu('30600','30430','(#)General Information','','','130');
oCMenu.makeMenu('31050','30600','(#)Services available','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Services','','130');
oCMenu.makeMenu('31060','30600','(#)Education development','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Education_development','','130');
oCMenu.makeMenu('31200','30430','(#)Buildings','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Buildings','','130');
oCMenu.makeMenu('30620','30430','(#)Furnitures','','','130');
oCMenu.makeMenu('31070','30620','(#)Furnitures (List)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Furnitures','','130');
oCMenu.makeMenu('31080','30620','(#)Desks','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Desks','','130');
oCMenu.makeMenu('30630','30430','(#)Teaching facilities','','','130');
oCMenu.makeMenu('31090','30630','(#)Text books','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Text Books','','130');
oCMenu.makeMenu('31100','30630','(#)Teacher Guides','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teacher Guides','','130');
oCMenu.makeMenu('31110','30630','(#)Facilities','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Teaching Facilities','','130');
oCMenu.makeMenu('30640','30430','(#)Special Edu Facilities','','','130');
oCMenu.makeMenu('31120','30640','(#)Special Edu Facilities (List)','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Special_educ_facilities','','160');
oCMenu.makeMenu('30870','30640','(#)Disabled Facilities','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Disabled Facilities','','160');
oCMenu.makeMenu('30650','30430','(#)Financial Information','','','130');
oCMenu.makeMenu('31380','30650','(#)Primary','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Financial infos','','130');
oCMenu.makeMenu('31390','30650','(#)Pre-primary','','','130');
oCMenu.makeMenu('31300','31390','(#)Revenues','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Income Infos','','130');
oCMenu.makeMenu('31310','31390','(#)Expenditres','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Expenditure Infos','','130');
oCMenu.makeMenu('30660','30430','(#)Sports Facilities','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Sport Facilities','','130');
oCMenu.makeMenu('31360','0','(#)COBET & ICBAE','','','160');
oCMenu.makeMenu('31140','31360','(#)Enrolment','','','130');
oCMenu.makeMenu('31160','31140','(#)Enrolment COBET','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=COBET Enrolment','','130');
oCMenu.makeMenu('31150','31140','(#)COBET to Primary','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=COBET_Primary','','130');
oCMenu.makeMenu('31170','31140','(#)COBET to Secondary','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=COBET_Secondary','','130');
oCMenu.makeMenu('31180','31140','(#)Dropout COBET','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=COBET Dropout','','130');
oCMenu.makeMenu('31190','31140','(#)Enrolment ICBAE','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Enrolment ICBAE','','130');
oCMenu.makeMenu('31130','31360','(#)Facilitators','','','130');
oCMenu.makeMenu('31210','31130','(#)COBET','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Facilitators COBET','','130');
oCMenu.makeMenu('31220','31130','(#)ICBAE','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Facilitators ICBAE','','130');
oCMenu.makeMenu('30610','31360','(#)COBET Books','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=COBET_Books','','130');
oCMenu.makeMenu('31230','31360','(#)ICBAE Classes','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Class_ICBAE','','130');
oCMenu.makeMenu('31420','31360','(#)ICBAE Buildings','olap.php?val=pop_OLAP2&type_sgbd=OlapServer&server_olap=localhost&nom_base=TANZANIA_BASIC_SCHOOL&nom_cube=Buildings ICBAE','','130');

//Leave this line - it constructs the menu
            oCMenu.construct();