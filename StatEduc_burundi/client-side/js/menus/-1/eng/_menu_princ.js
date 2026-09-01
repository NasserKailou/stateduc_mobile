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
            oCMenu.level[3]=new cm_makeLevel(140,22);
            oCMenu.fromLeft=407
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('2080','0','Home','accueil.php','','60');
oCMenu.makeMenu('2010','0','Data Entry','','','100');
oCMenu.makeMenu('20140','2010','Select a School','saisie_donnees.php?val=choix_etablissement','','145');
oCMenu.makeMenu('20160','2040','Skins Management','','','150');
oCMenu.makeMenu('20300','20160','Default','?style=defaut.css','','100');
oCMenu.makeMenu('20310','20160','Brown','?style=brun.css','','100');
oCMenu.makeMenu('20320','20160','Africa','javascript:set_style(\"afrique.css\");','','100');
oCMenu.makeMenu('20330','20160','Contrast','?style=contraste.css','','100');
oCMenu.makeMenu('20340','20160','Grey','javascript:set_style(\'gris.css\');','','100');
oCMenu.makeMenu('2070','0','Quit','accueil.php?val=logout','','50');

//Leave this line - it constructs the menu
            oCMenu.construct();