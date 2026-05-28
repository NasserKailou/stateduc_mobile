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
oCMenu.makeMenu('8803','1030','Identification','questionnaire.php?theme=8803&type_ent_stat=1','','400');
oCMenu.makeMenu('8903','1030','Données générales','#','','400');
oCMenu.makeMenu('9003','8903','Données générales (1)','questionnaire.php?theme=9003&type_ent_stat=1','','400');
oCMenu.makeMenu('9103','8903','Données générales (2)','questionnaire.php?theme=9103&type_ent_stat=1','','400');
oCMenu.makeMenu('9203','1030','Locaux','#','','400');
oCMenu.makeMenu('9303','9203','Caracteristique & Etat Locaux','questionnaire.php?theme=9303&type_ent_stat=1','','400');
oCMenu.makeMenu('9403','9203','Recap Locaux par nature mur','questionnaire.php?theme=9403&type_ent_stat=1','','400');
oCMenu.makeMenu('9503','1030','Mobilier & Equipement Collectif','#','','400');
oCMenu.makeMenu('9603','9503','Mobilier et Equip 1er C','questionnaire.php?theme=9603&type_ent_stat=1','','400');
oCMenu.makeMenu('9703','9503','Mobilier et Equip 2me C','questionnaire.php?theme=9703&type_ent_stat=1','','400');
oCMenu.makeMenu('9803','1030','Mobiliers Elèves','#','','400');
oCMenu.makeMenu('9903','9803','Mobilier élèves 1er C','questionnaire.php?theme=9903&type_ent_stat=1','','400');
oCMenu.makeMenu('10003','9803','Mobilier élèves 2me C','questionnaire.php?theme=10003&type_ent_stat=1','','400');
oCMenu.makeMenu('10103','1030','Manuels élèves','questionnaire.php?theme=10103&type_ent_stat=1','','400');
oCMenu.makeMenu('10203','1030','Equip. didactiques','questionnaire.php?theme=10203&type_ent_stat=1','','400');
oCMenu.makeMenu('10303','1030','Guide Profs','questionnaire.php?theme=10303&type_ent_stat=1','','400');
oCMenu.makeMenu('10403','1030','Effectifs / Niveau,  Age, ...','#','','400');
oCMenu.makeMenu('10503','10403','Répartition Age 1C','questionnaire.php?theme=10503&type_ent_stat=1','','400');
oCMenu.makeMenu('10603','10403','Répartition Age 2C','questionnaire.php?theme=10603&type_ent_stat=1','','400');
oCMenu.makeMenu('11003','1030','Exclus  ou  Abandons','#','','400');
oCMenu.makeMenu('11103','11003','Exclus/Abandons 1C','questionnaire.php?theme=11103&type_ent_stat=1','','400');
oCMenu.makeMenu('11203','11003','Exclus/Abandons 2C','questionnaire.php?theme=11203&type_ent_stat=1','','400');
oCMenu.makeMenu('12003','1030','Aire de Recrutement','#','','400');
oCMenu.makeMenu('12103','12003','Aire de recrutement 1C','questionnaire.php?theme=12103&type_ent_stat=1','','400');
oCMenu.makeMenu('12203','12003','Aire de recrutement 2C','questionnaire.php?theme=12203&type_ent_stat=1','','400');
oCMenu.makeMenu('12303','1030','Refugie','#','','400');
oCMenu.makeMenu('12503','12303','Eleve Refugie deplaces interne','questionnaire.php?theme=12503&type_ent_stat=1','','400');
oCMenu.makeMenu('12403','12303','Eleve Refugie menage retournée','questionnaire.php?theme=12403&type_ent_stat=1','','400');
oCMenu.makeMenu('12603','12303','Eleve Refugie menage non nigerienne','questionnaire.php?theme=12603&type_ent_stat=1','','400');
oCMenu.makeMenu('12703','1030','Handicap','#','','400');
oCMenu.makeMenu('12803','12703','Effect Handicap 1c','questionnaire.php?theme=12803&type_ent_stat=1','','400');
oCMenu.makeMenu('12903','12703','Effect Handicap 2c','questionnaire.php?theme=12903&type_ent_stat=1','','400');
oCMenu.makeMenu('11303','1030','Informations sur le Personnel','#','','400');
oCMenu.makeMenu('11403','11303','Pers. Enseignant','questionnaire.php?theme=11403&type_ent_stat=1','','400');
oCMenu.makeMenu('11503','11303','Récapitulatif Enseignants','questionnaire.php?theme=11503&type_ent_stat=1','','400');
oCMenu.makeMenu('15303','11303','Personnel Enseignant vacataire','questionnaire.php?theme=15303&type_ent_stat=1','','400');
oCMenu.makeMenu('11603','11303','Pers. Non Enseignant','questionnaire.php?theme=11603&type_ent_stat=1','','400');
oCMenu.makeMenu('11703','1030','Elèves allocataires / Niveau,...','#','','400');
oCMenu.makeMenu('11803','11703','Répartition Brse 1C','questionnaire.php?theme=11803&type_ent_stat=1','','400');
oCMenu.makeMenu('11903','11703','Répartition Brse 2C','questionnaire.php?theme=11903&type_ent_stat=1','','400');
oCMenu.makeMenu('15403','1030','Donnees Financiere','questionnaire.php?theme=15403&type_ent_stat=1','','400');
oCMenu.makeMenu('13203','1030','Coordonnés des responsables locaux de la collecte de données','questionnaire.php?theme=13203&type_ent_stat=1','','400');

//Leave this line - it constructs the menu
            oCMenu.construct();