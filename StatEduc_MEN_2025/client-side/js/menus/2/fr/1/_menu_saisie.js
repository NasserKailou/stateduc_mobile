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
oCMenu.makeMenu('1010','0','Accueil','accueil.php','','80');
oCMenu.makeMenu('1060','0','Choisir un Secteur','saisie_donnees.php?val=choix_sys_saisie','','130');
oCMenu.makeMenu('1020','0','Choix Etablissement','saisie_donnees.php?val=choix_etablissement','','130');
oCMenu.makeMenu('1030','0','Questionnaires','','','130');
oCMenu.makeMenu('1050','0','Ajout Etablissement','saisie_donnees.php?val=new_etab&theme=101','','155');
oCMenu.makeMenu('1040','0','Quitter','?val=logout','','42');
oCMenu.makeMenu('101','1030','Identification de l&#8217;établissement','questionnaire.php?theme=101','','220');
oCMenu.makeMenu('2101','1030','Informations generales','questionnaire.php?theme=2101','','220');
oCMenu.makeMenu('2501','1030','Offres de formation','questionnaire.php?theme=2501','','220');
oCMenu.makeMenu('2201','1030','Effectif apprenants  Niveau','questionnaire.php?theme=2201','','220');
oCMenu.makeMenu('2601','1030','Effectif Appr Courte Duree','questionnaire.php?theme=2601','','220');
oCMenu.makeMenu('2301','1030','Nouveau 1ere Annee','questionnaire.php?theme=2301','','220');
oCMenu.makeMenu('201','1030','Effectifs des élèves','questionnaire.php?theme=201','','220');
oCMenu.makeMenu('401','1030','Resultats scolaires ','questionnaire.php?theme=401','','220');
oCMenu.makeMenu('2401','1030','Effectif hadicap','questionnaire.php?theme=2401','','220');
oCMenu.makeMenu('601','1030','Personnel enseignant','questionnaire.php?theme=601','','220');
oCMenu.makeMenu('701','1030','Personnel administratif','questionnaire.php?theme=701','','220');
oCMenu.makeMenu('1201','1030','Equipement ','questionnaire.php?theme=1201','','220');
oCMenu.makeMenu('801','1030','Infrast Immobilières','questionnaire.php?theme=801','','220');
oCMenu.makeMenu('1001','1030','Equipements / Mobiliers  administratifs','questionnaire.php?theme=1001','','220');
oCMenu.makeMenu('1101','1030','Equipements / Mobiliers pédagogiques','questionnaire.php?theme=1101','','220');
oCMenu.makeMenu('1901','1030','ATELIERS (Salles spécialisées)','questionnaire.php?theme=1901','','220');
oCMenu.makeMenu('1401','1030','Equipement de la bibliothèque','questionnaire.php?theme=1401','','220');
oCMenu.makeMenu('1501','1030','Frais scolaire/Cotisations parallèles','questionnaire.php?theme=1501','','220');
oCMenu.makeMenu('1701','1030','Dépenses d&#8217;investissement','questionnaire.php?theme=1701','','220');
oCMenu.makeMenu('1601','1030','Dépenses de fonctionnement','questionnaire.php?theme=1601','','220');

//Leave this line - it constructs the menu
            oCMenu.construct();