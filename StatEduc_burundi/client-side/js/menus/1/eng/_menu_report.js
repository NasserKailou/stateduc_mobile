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
oCMenu.makeMenu('','30030','','synthese.php?val=list_rpt&id_rpt=&type_rpt=&id_syst=','','270');

//Leave this line - it constructs the menu
            oCMenu.construct();