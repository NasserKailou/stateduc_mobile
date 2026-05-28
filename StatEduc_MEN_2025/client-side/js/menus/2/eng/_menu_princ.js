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
            oCMenu.menuPlacement=0  // permet de contrÙler (ou bloquer) la position ‡ gauche pour Èviter
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
                                                    
            // ParamËtres de cm_makeLevel(width, height, regClass, overClass, borderX, borderY, borderClass, rows, align, offsetX, offsetY, arrow, arrowWidth, arrowHeight, roundBorder)
            oCMenu.level[0]=new cm_makeLevel(90,21,"clT","clTover",1,1,"clB",0,"bottom",0,0,0,0,0);
            oCMenu.level[2]=new cm_makeLevel(110,22,"clS2","clS2over");
            oCMenu.level[3]=new cm_makeLevel(140,22);
            oCMenu.fromLeft=199.5
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('2080','0','(#)Accueil','accueil.php','','60');
oCMenu.makeMenu('2010','0','(#)Saisie donn√©es','','','100');
oCMenu.makeMenu('20810','2010','(#)Choisir un Secteur','saisie_donnees.php?val=choix_sys_princ','','145');
oCMenu.makeMenu('20140','2010','(#)Choix Etablissement','saisie_donnees.php?val=choix_etablissement','','145');
oCMenu.makeMenu('20660','2010','(#)Ajout Etablissement','saisie_donnees.php?val=new_etab&theme=101','','145');
oCMenu.makeMenu('2020','0','(#)Contr√¥le / Validation','','','135');
oCMenu.makeMenu('30350','2020','(#)Controle de Coh√©rences','administration.php?val=controle','','140');
oCMenu.makeMenu('2040','0','(#)Admin / Config','','','110');
oCMenu.makeMenu('2090','2040','(#)Gestion Utilisateurs','','','150');
oCMenu.makeMenu('20640','2090','(#)Gestion Utilisateurs','administration.php?val=gestionuser','','130');
oCMenu.makeMenu('20160','2040','(#)Gestion Habillages','','','150');
oCMenu.makeMenu('20300','20160','(#)Defaut','?style=defaut.css','','100');
oCMenu.makeMenu('20310','20160','(#)Brun','?style=brun.css','','100');
oCMenu.makeMenu('20320','20160','(#)Afrique','javascript:set_style(\"afrique.css\");','','100');
oCMenu.makeMenu('20330','20160','(#)Contraste','?style=contraste.css','','100');
oCMenu.makeMenu('20340','20160','(#)Gris','javascript:set_style(\'gris.css\');','','100');
oCMenu.makeMenu('20250','2040','(#)Gestion Param√®tres','','','150');
oCMenu.makeMenu('20680','20250','(#)Param√®tres Config','administration.php?val=param','','140');
oCMenu.makeMenu('2030','0','(#)Tableau Synth√®se','synthese.php','','120');
oCMenu.makeMenu('2060','0','(#)Aide','','','50');
oCMenu.makeMenu('2070','0','(#)Quitter','accueil.php?val=logout','','50');

//Leave this line - it constructs the menu
            oCMenu.construct();