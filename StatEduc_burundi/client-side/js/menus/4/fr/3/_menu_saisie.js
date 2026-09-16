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
            oCMenu.menuPlacement=0  // permet de contr�ler (ou bloquer) la position � gauche pour �viter
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
                                                    
            // Param�tres de cm_makeLevel(width, height, regClass, overClass, borderX, borderY, borderClass, rows, align, offsetX, offsetY, arrow, arrowWidth, arrowHeight, roundBorder)
            oCMenu.level[0]=new cm_makeLevel(90,21,"clT","clTover",1,1,"clB",0,"bottom",0,0,0,0,0);
            oCMenu.level[2]=new cm_makeLevel(110,22,"clS2","clS2over");
            oCMenu.level[3]=new cm_makeLevel(140,22);oCMenu.fromLeft=175;
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('1503','1030','Identification & Localisation','questionnaire.php?theme=1503','','200');
oCMenu.makeMenu('1603','1030','Informations Générales','questionnaire.php?theme=1603','','200');
oCMenu.makeMenu('1803','1030','Nb Salles, Mobilier & Equipement','questionnaire.php?theme=1803','','200');
oCMenu.makeMenu('1903','1030','Données Générales élèves(1)','questionnaire.php?theme=1903','','200');
oCMenu.makeMenu('2003','1030','Données Générales élèves(2)','questionnaire.php?theme=2003','','200');
oCMenu.makeMenu('8503','1030','Données Générales élèves(3)','questionnaire.php?theme=8503','','200');
oCMenu.makeMenu('8103','1030','Post-Fonda finance','questionnaire.php?theme=8103','','200');
oCMenu.makeMenu('2103','1030','Infos sur le personnel(1)','questionnaire.php?theme=2103','','200');
oCMenu.makeMenu('2203','1030','Infos sur le personnel(2)','questionnaire.php?theme=2203','','200');
oCMenu.makeMenu('7803','1030','Liste Personnel','questionnaire.php?theme=7803','','200');

//Leave this line - it constructs the menu
            oCMenu.construct();