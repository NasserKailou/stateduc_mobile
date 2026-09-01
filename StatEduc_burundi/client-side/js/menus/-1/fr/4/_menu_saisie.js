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
oCMenu.makeMenu('3704','1030','Identification lycée','questionnaire.php?theme=3704','','180');
oCMenu.makeMenu('3804','1030','Environnement socio lycée','questionnaire.php?theme=3804','','180');
oCMenu.makeMenu('3904','1030','Données générales lycée','questionnaire.php?theme=3904','','180');
oCMenu.makeMenu('4004','1030','locaux lycée','questionnaire.php?theme=4004','','180');
oCMenu.makeMenu('4104','1030','Mobilier lycée','questionnaire.php?theme=4104','','180');
oCMenu.makeMenu('4204','1030','Equipements lycée','questionnaire.php?theme=4204','','180');
oCMenu.makeMenu('4304','1030','Données financ lycée','questionnaire.php?theme=4304','','180');
oCMenu.makeMenu('4404','1030','Personnel lycee','questionnaire.php?theme=4404','','180');
oCMenu.makeMenu('4504','1030','Effectif par age lycee','questionnaire.php?theme=4504','','180');
oCMenu.makeMenu('4704','1030','Lycée aire recrutement','questionnaire.php?theme=4704','','180');
oCMenu.makeMenu('5004','1030','Struture pédagogique lycée','questionnaire.php?theme=5004','','180');

//Leave this line - it constructs the menu
            oCMenu.construct();