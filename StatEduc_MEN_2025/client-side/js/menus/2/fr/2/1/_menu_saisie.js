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
oCMenu.makeMenu('1020','0','Choix Etablissement','','','130');
oCMenu.makeMenu('31750','1020','Classique','saisie_donnees.php?val=choix_etablissement&type_ent_stat=1','','130');
oCMenu.makeMenu('31760','1020','Mobile','saisie_donnees.php?val=choix_etablissement&type_ent_stat=2','','130');
oCMenu.makeMenu('1030','0','Questionnaires','','','130');
oCMenu.makeMenu('1050','0','Ajout Etablissement','saisie_donnees.php?val=new_etab&theme=101','','155');
oCMenu.makeMenu('1040','0','Quitter','?val=logout','','42');
oCMenu.makeMenu('102','1030','Identification Etablissement','questionnaire.php?theme=102&type_ent_stat=1','','220');
oCMenu.makeMenu('502','1030','Infos Generales - ecole','questionnaire.php?theme=502&type_ent_stat=1','','220');
oCMenu.makeMenu('8302','1030','Locaux','#','','220');
oCMenu.makeMenu('6702','8302','Caractéristiques et Etat','questionnaire.php?theme=6702&type_ent_stat=1','','220');
oCMenu.makeMenu('6502','8302','Récap Salles de classe','questionnaire.php?theme=6502&type_ent_stat=1','','220');
oCMenu.makeMenu('6602','1030','Mobilier et Equipement','questionnaire.php?theme=6602&type_ent_stat=1','','220');
oCMenu.makeMenu('7002','1030','Equipements Didactiques','questionnaire.php?theme=7002&type_ent_stat=1','','220');
oCMenu.makeMenu('8402','1030','Personnel','#','','220');
oCMenu.makeMenu('6802','8402','Liste nominative','questionnaire.php?theme=6802&type_ent_stat=1','','220');
oCMenu.makeMenu('7102','8402',' Récap. Enseignant ','questionnaire.php?theme=7102&type_ent_stat=1','','220');
oCMenu.makeMenu('8502','1030','Effectif des élèves','#','','220');
oCMenu.makeMenu('7202','8502','Nvx Inscrits en 1ère Année','questionnaire.php?theme=7202&type_ent_stat=1','','220');
oCMenu.makeMenu('6902','8502','Effectifs GP par age','questionnaire.php?theme=6902&type_ent_stat=1','','220');
oCMenu.makeMenu('8202','8502','Aire de Recrutement','questionnaire.php?theme=8202&type_ent_stat=1','','220');
oCMenu.makeMenu('13002','8502','X. Structure Pédagogique et Effectifs Eleves','questionnaire.php?theme=13002&type_ent_stat=1','','220');
oCMenu.makeMenu('15202','8502','REFUGIES','questionnaire.php?theme=15202&type_ent_stat=1','','220');
oCMenu.makeMenu('8602','8502','Provenant Passerelle','questionnaire.php?theme=8602&type_ent_stat=1','','220');
oCMenu.makeMenu('15102','8502','Provenant coranique','questionnaire.php?theme=15102&type_ent_stat=1','','220');
oCMenu.makeMenu('7302','8502','Result. Exam & Effect Handicap','questionnaire.php?theme=7302&type_ent_stat=1','','220');

//Leave this line - it constructs the menu
            oCMenu.construct();