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
oCMenu.makeMenu('1010','0','Accueil','accueil.php','','80');
oCMenu.makeMenu('1060','0','Choisir un Secteur','saisie_donnees.php?val=choix_sys_saisie','','130');
oCMenu.makeMenu('1020','0','Choix Etablissement','saisie_donnees.php?val=choix_etablissement','','130');
oCMenu.makeMenu('1030','0','Questionnaires','questionnaire.php','','130');
oCMenu.makeMenu('1040','0','Quitter','?val=logout','','42');
oCMenu.makeMenu('2603','1030','identificalion collège','questionnaire.php?theme=2603','','180');
oCMenu.makeMenu('2703','1030','Environnement socio','questionnaire.php?theme=2703','','180');
oCMenu.makeMenu('2803','1030','Donnéées générales CEG','questionnaire.php?theme=2803','','180');
oCMenu.makeMenu('2903','1030','locaux collège','questionnaire.php?theme=2903','','180');
oCMenu.makeMenu('3003','1030','mobiliers college','questionnaire.php?theme=3003','','180');
oCMenu.makeMenu('3103','1030','Equipements college','questionnaire.php?theme=3103','','180');
oCMenu.makeMenu('3203','1030','Donnees financ college','questionnaire.php?theme=3203','','180');
oCMenu.makeMenu('3303','1030','personnel college','questionnaire.php?theme=3303','','180');
oCMenu.makeMenu('3403','1030','Effectif par age college','questionnaire.php?theme=3403','','180');
oCMenu.makeMenu('3503','1030','Aire recrutement collège','questionnaire.php?theme=3503','','180');
oCMenu.makeMenu('3603','1030','Structure pédagogique collège','questionnaire.php?theme=3603','','180');

//Leave this line - it constructs the menu
            oCMenu.construct();