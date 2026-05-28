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
            oCMenu.level[3]=new cm_makeLevel(140,22);oCMenu.fromLeft=175;
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('1010','0','Accueil','accueil.php','','80');
oCMenu.makeMenu('1020','0','Choix Etablissement','saisie_donnees.php?val=choix_etablissement','','130');
oCMenu.makeMenu('1030','0','Questionnaires','questionnaire.php','','130');
oCMenu.makeMenu('1040','0','Quitter','?val=logout','','42');
oCMenu.makeMenu('1903','1030','Identification et Localisation','questionnaire.php?theme=1903','','220');
oCMenu.makeMenu('2003','1030','Donn√©es G√©n√©rales','questionnaire.php?theme=2003','','220');
oCMenu.makeMenu('2103','1030','Locaux','questionnaire.php?theme=2103','','220');
oCMenu.makeMenu('2203','1030','Personnel Administratif','questionnaire.php?theme=2203','','220');
oCMenu.makeMenu('2303','1030','Personnel Enseignant','questionnaire.php?theme=2303','','220');
oCMenu.makeMenu('2403','1030','Effectifs des El√®ves','questionnaire.php?theme=2403','','220');
oCMenu.makeMenu('2503','1030','Mobiliers et Equipements','questionnaire.php?theme=2503','','220');
oCMenu.makeMenu('2603','1030','Manuels Eleves & Nvx Inscrits 6√®mes','questionnaire.php?theme=2603','','220');
oCMenu.makeMenu('2803','1030','Origine Scolaire Nvx Admis','questionnaire.php?theme=2803','','220');
oCMenu.makeMenu('4103','1030','Resultats BEPC & Tab. R√©cap','questionnaire.php?theme=4103','','220');

//Leave this line - it constructs the menu
            oCMenu.construct();