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
oCMenu.makeMenu('702','1030','Identification','questionnaire.php?theme=702','','220');
oCMenu.makeMenu('802','1030','Donn√©es G√©n√©rales','questionnaire.php?theme=802','','220');
oCMenu.makeMenu('902','1030','Locaux','questionnaire.php?theme=902','','220');
oCMenu.makeMenu('1002','1030','Personnel Enseignant et administratif','questionnaire.php?theme=1002','','220');
oCMenu.makeMenu('1102','1030','Effectifs des El√®ves','questionnaire.php?theme=1102','','220');
oCMenu.makeMenu('1202','1030','Mobiliers et Equipements','questionnaire.php?theme=1202','','220');
oCMenu.makeMenu('1302','1030','Equipement didactique & Nvx Inscrits','questionnaire.php?theme=1302','','220');
oCMenu.makeMenu('1502','1030','Aire des recrutements','questionnaire.php?theme=1502','','220');
oCMenu.makeMenu('1602','1030','Resultats Exam & Tableau Recap','questionnaire.php?theme=1602','','220');

//Leave this line - it constructs the menu
            oCMenu.construct();