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
            oCMenu.level[3]=new cm_makeLevel(140,22);oCMenu.fromLeft=206;
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"client-side/image/menu_arrow.php",10,10);
oCMenu.makeMenu('30010','0','(#)Accueil','accueil.php','','80');
oCMenu.makeMenu('30050','0','(#)Choisir un Secteur','saisie_donnees.php?val=choix_sys_rpt','','130');
oCMenu.makeMenu('30040','0','(#)Gestion des Etats','synthese.php','','180');
oCMenu.makeMenu('20850','30040','(#)Gestion des Etats','synthese.php?val=gest_rpt','','160');
oCMenu.makeMenu('20860','30040','(#)Gesion des Mesures','synthese.php?val=gest_mes','','160');
oCMenu.makeMenu('30060','30040','(#)Gestion des Critères','synthese.php?val=criteres','','160');
oCMenu.makeMenu('20870','30040','(#)Gestion des Dimensions','synthese.php?val=gest_dim','','160');
oCMenu.makeMenu('20880','30040','(#)Gestion des Aggrégations','synthese.php?val=gest_agg','','160');
oCMenu.makeMenu('30030','0','(#)Liste des Etats','','','180');
oCMenu.makeMenu('30020','0','(#)Quitter','?val=logout','','42');
oCMenu.makeMenu('93','30030','Personnel enseignant par nationalité  et diplôme/certificat','synthese.php?val=list_rpt&id_rpt=9&type_rpt=&id_syst=3','','270');
oCMenu.makeMenu('23','30030','Nombre Etablissements','synthese.php?val=list_rpt&id_rpt=2&type_rpt=&id_syst=3','','270');
oCMenu.makeMenu('33','30030','Effectifs des élèves','#','','270');
oCMenu.makeMenu('13','33','Effectifs élèves par niveau et âge','synthese.php?val=list_rpt&id_rpt=1&type_rpt=1&id_syst=3','','270');
oCMenu.makeMenu('43','33','Effectifs élèves par niveau et nationalité','synthese.php?val=list_rpt&id_rpt=4&type_rpt=1&id_syst=3','','270');
oCMenu.makeMenu('53','30030','Salles de classe','synthese.php?val=list_rpt&id_rpt=5&type_rpt=&id_syst=3','','270');
oCMenu.makeMenu('73','30030','Personnel','#','','270');
oCMenu.makeMenu('83','73','Personnel enseignant par niveau et diplôme/certificat','synthese.php?val=list_rpt&id_rpt=8&type_rpt=1&id_syst=3','','270');

//Leave this line - it constructs the menu
            oCMenu.construct();