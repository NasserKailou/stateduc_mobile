<?php
		lit_libelles_page(__FILE__);
?>
<script type="text/Javascript">
		function toggle_theme() {
						var id_theme     = form_theme.themes_dispo.options[form_theme.themes_dispo.selectedIndex].value;
						var radio     = form_theme.choix_affich;
						var val_affich;
						for (var i=0; i<radio.length;i++) {
							 if (radio[i].checked) {
								val_affich=radio[i].value;
								break;
							 }
						}
						//(val_affich==1) ? (val_affich='all_themes') : (val_affich='active_themes');
						if(val_affich==3){
							val_affich='active_themes_rattach';
						}else if(val_affich==1){
							val_affich='all_themes';
						}else{
							val_affich='active_themes'
						}
						location.href   = '?val=gestheme&theme_en_cours='+id_theme+'&val_affich='+val_affich;
						//location.href   = '?val=gestheme&theme_en_cours='+id_theme;
		}
		function toggle_filtre_secteur_T() {
						var id_theme     = form_theme.filtre_secteur_T[form_theme.filtre_secteur_T.selectedIndex].value;
						location.href   = '?val=gestheme&filtre_secteur_T_en_cours='+id_theme;
		}
		function toggle_filtre_secteur_T_S() {
                    //alert('theme=' + form_theme.filtre_secteur_T_S.value);
						var id_secteur     = form_theme.filtre_secteur_T_S[form_theme.filtre_secteur_T_S.selectedIndex].value;
						location.href   = '?val=gestheme&filtre_secteur_T_S_en_cours='+id_secteur;
                        
		}
		function clear_filtre_appart_T_S() {
           //document.form_theme.choix_affich[2].checked=true;
		  	setTimeout( function() {
					$( "#choix_affich_3" ).click();
				}
				,500);
		}
		function reload(choix) {
                 var id_secteur     = form_theme.filtre_secteur_T_S[form_theme.filtre_secteur_T_S.selectedIndex].value;
				 var id_appart     = "";
				 if(form_theme.filtre_appart_theme){
				 	id_appart     = form_theme.filtre_appart_theme[form_theme.filtre_appart_theme.selectedIndex].value;
				 }
				 //if(id_appart!="")
				 	location.href   = '?val=gestheme&filtre_secteur_T_S_en_cours='+id_secteur+'&filtre_appart_theme_en_cours='+id_appart+'&choix_affich='+choix;
				// else
				// 	location.href   = '?val=gestheme&filtre_secteur_T_S_en_cours='+id_secteur+'&choix_affich='+choix;
		}
		
		function AlertSupThm(Elem_Sup,cas){
				var Mess_Sup = '';
				var Cas_Thm_Sup = '';
				if(cas == 'theme'){
						Mess_Sup = "<?php echo recherche_libelle_page('SupThm'); ?>";
						Cas_Thm_Sup = 'SupThm';
				}
				else if(cas == 'ss_theme'){
						Mess_Sup = "<?php echo recherche_libelle_page('SupSsThm'); ?>";
						Cas_Thm_Sup = 'SupSsThm';
				}
				if(confirm( Mess_Sup + '"' + Elem_Sup + '"')){ 
						
						var radio     = form_theme.choix_affich;
						var val_affich;
						for (var i=0; i<radio.length;i++) {
							 if (radio[i].checked) {
								val_affich=radio[i].value;
							 }
						}
						//(val_affich==1) ? (val_affich='all_themes') : (val_affich='active_themes');
						if(val_affich==3){
							val_affich='active_themes_rattach';
						}else if(val_affich==1){
							val_affich='all_themes';
						}else{
							val_affich='active_themes'
						}
						document.location.href="administration.php?val=gestheme&supp="+Cas_Thm_Sup+'&val_affich='+val_affich;
				}
		}
		function Assoc_Prop_Type_Thm (type_thm) {
				var CLASSE = '' ;
				var ACTION_THEME = '' ;
				switch(type_thm){
						case '1':{
								CLASSE 				= 'matrice' ;
								ACTION_THEME 	= 'instance_matrice.php' ;
								break;
						}
						case '7':{
								CLASSE 				= 'mat_grille' ;
								ACTION_THEME 	= 'instance_mat_grille.php' ;
								break;
						}
						case '8':{
								CLASSE 				= 'menu' ;
								ACTION_THEME 	= '' ;

								break;
						}
						case '2':
						case '3':
						case '4':
						case '5':
						case '6':{
								CLASSE = 'grille' ;
								ACTION_THEME = 'instance_grille.php' ;
								break;
						}
				}
				document.form_theme.action_theme.value 	= ACTION_THEME ;
				document.form_theme.classe.value 				= CLASSE ;
		}
</script>

<?php
	$_SESSION['Nouveau'] = recherche_libelle_page('nouveau');
	if($_SESSION['Nouveau']=='' && $_SESSION['langue']=='fr') $_SESSION['Nouveau'] = 'Nouveau'; else $_SESSION['Nouveau'] = 'New';

		function get_i_theme($id_theme){
				$i_th = 0;
				if(is_array($_SESSION['tab_theme'])){
						foreach ($_SESSION['tab_theme'] as $k => $v){
								if( trim($id_theme) == trim($v['ID']) ){
										$i_th = $k ;
										break;
								}
						}
				}
				return($i_th);
		}
		
		function SuppCascadeZone($id_zone){ 
				/// Suppression dans DICO_ZONE
				$requete 	= ' DELETE    FROM DICO_ZONE WHERE  ID_ZONE = '.$id_zone;
				if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';

				
				/// Suppression dans DICO_ZONE_SYSTEME
				$requete 	= ' DELETE    FROM DICO_ZONE_SYSTEME  WHERE  ID_ZONE = '.$id_zone;
				if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';

				
				/// Suppression dans DICO_REGLE_ZONE_ASSOC
				$requete 	= ' DELETE    FROM DICO_REGLE_ZONE_ASSOC  WHERE  ID_ZONE = '.$id_zone;
				if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';


				/// Suppression dans DICO_ZONE_JOINTURE
				$requete 	= ' DELETE    FROM DICO_ZONE_JOINTURE  WHERE  ID_THEME = '.$_SESSION['tab_theme'][$_SESSION['i']]['ID'].'
											AND N_JOINTURE IN
											(SELECT DISTINCT N_JOINTURE FROM DICO_ZONE_JOINTURE 
											 WHERE ID_ZONE = '.$id_zone.' AND ID_THEME = '.$_SESSION['tab_theme'][$_SESSION['i']]['ID'].')';
				if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';
				
				/// Suppression dans DICO_DIMENSION_ZONE CAS : MATRICE
				$requete 	= ' DELETE    FROM   DICO_DIMENSION_ZONE 
											 WHERE ID_ZONE = '.$id_zone;
				if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';

				
				/// Suppression dans DICO_INDEXES CAS : MATRICE
				$requete 	= ' DELETE    FROM   DICO_INDEXES 
											 WHERE ID_ZONE = '.$id_zone;
				if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR : '.$requete.'<br>';

				
				/// Suppression dans DICO_TRADUCTION
				$requete 	= 'DELETE    FROM   DICO_TRADUCTION 
										 WHERE CODE_NOMENCLATURE = '.$id_zone.
										' AND NOM_TABLE = \'DICO_ZONE\''; 
				if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR : '.$requete.'<br>';
		}

    function supprime_theme_systeme($num_theme_courant){
        // Supprime le thème_systeme courant
       
			  // Suppression des zones systeme du theme systeme
        $requete                = "DELETE FROM DICO_ZONE_SYSTEME WHERE  ID_SYSTEME = ".$_SESSION['filtre_secteur_T_S']."
									AND  ID_ZONE IN ( SELECT  DICO_ZONE.ID_ZONE FROM   DICO_ZONE, DICO_THEME_SYSTEME 
														WHERE DICO_ZONE.ID_THEME = DICO_THEME_SYSTEME.ID
														AND DICO_THEME_SYSTEME.ID_THEME_SYSTEME = ".$num_theme_courant." )";
				$GLOBALS['conn_dico']->Execute($requete);
				//  
			 
			  // Suppression du libelle_long
        $requete                = "DELETE FROM DICO_TRADUCTION
                                    WHERE CODE_NOMENCLATURE =".$num_theme_courant."
                                    and NOM_TABLE='DICO_THEME_LIB_LONG';";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        
        // Suppression du libelle_menu
        $requete                = "DELETE FROM DICO_TRADUCTION
                                    WHERE CODE_NOMENCLATURE =".$num_theme_courant."
                                    and NOM_TABLE='DICO_THEME_LIB_MENU';";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        
         //Ajout HEBIE Suppression de la config des tables dans DICO_EXCEL_TABLE
        $requete  = "DELETE FROM DICO_EXCEL_TABLE WHERE  ID_SYSTEME = ".$_SESSION['filtre_secteur_T_S']."
						AND  ID_THEME IN ( SELECT ID FROM DICO_THEME_SYSTEME WHERE ID_THEME_SYSTEME = $num_theme_courant);";
		$GLOBALS['conn_dico']->Execute($requete);
		//Todo: faire ceci pour les autres tables DICO_EXCEL_*
				
        // Suppression dans la table DICO_THEME
        $requete                = "DELETE FROM DICO_THEME_SYSTEME
                                    WHERE ID_THEME_SYSTEME =".$num_theme_courant.";";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        
    }

    function supprime_theme(){
        // Supprime le thème courant
        // Supprime également les THEME_SYSTEME associés ainsi que le libellé dans DICO_TRADUCTION
        // TODO: et la suppression en cascade?
        
				/// sup des regles themes assoc
        $requete      = "DELETE FROM DICO_REGLE_THEME_ASSOC
							WHERE ID_REGLE_THEME IN (SELECT ID_REGLE_THEME FROM  DICO_REGLE_THEME 
							WHERE  ID_THEME = ".$_SESSION['tab_theme'][$_SESSION['i']][ID].")";
        $GLOBALS['conn_dico']->Execute($requete);
				
        $requete      = "DELETE FROM DICO_REGLE_THEME_ASSOC
							WHERE ID_REGLE_THEME_ASSOC IN (SELECT ID_REGLE_THEME FROM  DICO_REGLE_THEME 
							WHERE  ID_THEME = ".$_SESSION['tab_theme'][$_SESSION['i']][ID].")";
        $GLOBALS['conn_dico']->Execute($requete);
				/// fin sup des regles themes assoc
				
				/// sup des regles themes 
        $requete      = "DELETE FROM  DICO_REGLE_THEME WHERE  ID_THEME = ".$_SESSION['tab_theme'][$_SESSION['i']][ID].")";
        $GLOBALS['conn_dico']->Execute($requete);
				/// fin sup des regles themes
				
				
				/// sup des table mere theme
        $requete      = "DELETE FROM  DICO_TABLE_MERE_THEME WHERE  ID_THEME = ".$_SESSION['tab_theme'][$_SESSION['i']][ID]."";
        $GLOBALS['conn_dico']->Execute($requete);
				//echo '<br>'.$requete.'<br>';
				/// fin sup des table mere theme

				/////////// Récupération des zones du thème à supprimer
				$requete         = "SELECT  ID_ZONE FROM  DICO_ZONE														
									WHERE     ID_THEME = ".$_SESSION['tab_theme'][$_SESSION['i']][ID].";";
        $all_zones_thm_supp    = $GLOBALS['conn_dico']->GetAll($requete);
        if (count($all_zones_thm_supp) > 0){
            // TODO: voir alert qui retourne valeur
            foreach ($all_zones_thm_supp as $zone){
                SuppCascadeZone($zone['ID_ZONE']);
            }
        }
				///////////// Fin Récupération des zones du thème à supprimer

        // Il faut vérifier s'il existe des fils dans DICO_THEME_SYSTEME
        $requete                = "SELECT ID, ID_THEME_SYSTEME
                                    FROM DICO_THEME_SYSTEME
                                    WHERE ID = ".$_SESSION['tab_theme'][$_SESSION['i']][ID].";";
       
        //print $requete;
        $theme_systeme_a_sup    = $GLOBALS['conn_dico']->GetAll($requete);
        if (count($theme_systeme_a_sup) > 0){
            // TODO: voir alert qui retourne valeur
            foreach ($theme_systeme_a_sup as $row){
                supprime_theme_systeme($row[ID_THEME_SYSTEME]);
            }
        }
        
        // Suppression du libellé du thème (nom_interne) dans la table DICO_TRADUCTION
        $requete                = "DELETE FROM DICO_TRADUCTION
                                    WHERE CODE_NOMENCLATURE =".$_SESSION['tab_theme'][$_SESSION['i']][ID]."
                                    AND NOM_TABLE='DICO_THEME';";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        
		 //Ajout HEBIE Suppression de la config des tables dans DICO_EXCEL_TABLE
        $requete  = "DELETE FROM DICO_EXCEL_TABLE WHERE  ID_THEME = ".$_SESSION['tab_theme'][$_SESSION['i']][ID].";";
		$GLOBALS['conn_dico']->Execute($requete);
		//Todo: faire ceci pour les autres tables DICO_EXCEL_*
        
        // Suppression dans la table DICO_THEME
        $requete                = "DELETE FROM DICO_THEME
                                    WHERE ID =".$_SESSION['tab_theme'][$_SESSION['i']][ID].";";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        
    }

    function recherche_nouvel_id($tab_table, $nom_id){
        // Recherche d'une nouvelle valeur de l'ID disponible dans une table 
        // dont les éléments sont passés dans un tableau en paramètre
        // Fonction utilisée pour l'ajout d'enregistrements        
        $nouvel_id              = 0;
        foreach ($tab_table as $ligne){
            if ($nouvel_id < $ligne[$nom_id]){
                $nouvel_id      = $ligne[$nom_id];
            }
        }
        $nouvel_id      += 10;
        return $nouvel_id;
        
    }
	
	function recherche_nouvel_ord($tab_table, $chp_ord){
        // Recherche d'une nouvelle valeur de l'ID disponible dans une table 
        // dont les éléments sont passés dans un tableau en paramètre
        // Fonction utilisée pour l'ajout d'enregistrements        
        $nouvel_ord              = 0;
        foreach ($tab_table as $ligne){
            if ($nouvel_ord < $ligne[$chp_ord]){
                $nouvel_ord      = $ligne[$chp_ord];
            }
        }
        $nouvel_ord      += 10;
        return $nouvel_ord;
        
    }

    function recherche_nouvel_id_theme_systeme(){
        // Vérification de l'existence du nouveau theme_systeme
        $requete                = "SELECT ID_THEME_SYSTEME FROM DICO_THEME_SYSTEME
                                    WHERE ID_THEME_SYSTEME = ".$_SESSION['tab_theme'][$_SESSION['i']][ID].$_SESSION['filtre_secteur_T_S'].";";
        $result  = $GLOBALS['conn_dico']->GetAll($requete);
        if (!isset($result[0][ID_THEME_SYSTEME])){
            return $_SESSION['tab_theme'][$_SESSION['i']][ID].$_SESSION['filtre_secteur_T_S'];
        }
        else{
                return 'Existe';
            }
    }

    function ajout_theme(){
        // Création d'un nouvel enregistrement dans DICO_THEME et dans DICO_TRADUCTION
        
        // Il faut d'abord trouver une nouvelle valeur de l'ID disponible dans DICO_THEME 
        $nouvel_id              = recherche_nouvel_id($_SESSION['tab_theme'], 'ID');
		$nouvel_ord             = recherche_nouvel_ord($_SESSION['tab_theme'], 'ORDRE_THEME');
				$CLASSE = NULL ;
				$ACTION_THEME = NULL ;
				switch($_POST['type_theme']){
						case '1':{
								$CLASSE = "'matrice'" ;
								$ACTION_THEME = "'instance_matrice.php'" ;
								break;
						}
						case '7':{
								$CLASSE = "'mat_grille'" ;
								$ACTION_THEME = "'instance_mat_grille.php'" ;
								break;
						}
						case '8':{
								$CLASSE = "'menu'" ;
								$ACTION_THEME = "''" ;

								break;
						}

						case '2':
						case '3':
						case '4':
						case '5':
						case '6':{
								$CLASSE = "'grille'" ;
								$ACTION_THEME = "'instance_grille.php'" ;
								break;
						}
				}
        // Création de l'enregistrement dans DICO_THEME
        $db                     = $GLOBALS['conn_dico'];
        $requete                = "INSERT INTO DICO_THEME (ID,ORDRE_THEME,ID_TYPE_THEME,CLASSE,ACTION_THEME)
                                    VALUES (".$nouvel_id.", ".$nouvel_ord.", ".$_POST['type_theme'].", $CLASSE, $ACTION_THEME);";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        
        // Création du libellé dans DICO_TRADUCTION
        $requete                = "INSERT INTO DICO_TRADUCTION (CODE_NOMENCLATURE, NOM_TABLE, CODE_LANGUE, LIBELLE)
                                    VALUES (".$nouvel_id.",'DICO_THEME', '".$_SESSION['langue']."', '".$_SESSION['Nouveau']."');";
                                    //VALUES (".$nouvel_id.",'DICO_THEME', '".$_SESSION['langue']."', '".$_SESSION['Nouveau']."', NULL, NULL);";

        $GLOBALS['conn_dico']->Execute($requete);
				insert_traduction('DICO_TRADUCTION', $nouvel_id, 'DICO_THEME', $_SESSION['langue'], $_SESSION['Nouveau'], 1);
        
    }

    function ajout_theme_systeme(){
        // Création d'un nouvel enregistrement dans DICO_THEME_SYSTEME et dans DICO_TRADUCTION
        // Il faut d'abord trouver une nouvelle valeur de l'ID disponible dans DICO_THEME_SYSTEME.   
        $nouvel_id              = recherche_nouvel_id_theme_systeme();
        if ($nouvel_id == 'Existe'){
            $_SESSION['message']= 'Ce thème existe dèjà pour ce secteur.';
            lit_theme_systeme_filtre();            // Lecture du thème_système_filtré du theme courrant
        }else{
            // Création du libellé dans DICO_TRADUCTION
            $db                     = $GLOBALS['conn_dico'];
            $requete                = "INSERT INTO DICO_TRADUCTION
                                        VALUES (".$nouvel_id.",'DICO_THEME_LIB_LONG', '".$_SESSION['langue']."', '".$_SESSION['Nouveau']."', NULL, NULL);";
            //print $requete;
            $GLOBALS['conn_dico']->Execute($requete);
						insert_traduction('DICO_TRADUCTION', $nouvel_id, 'DICO_THEME_LIB_LONG', $_SESSION['langue'], $_SESSION['Nouveau'], 1);
						
            $requete                = "INSERT INTO DICO_TRADUCTION
                                        VALUES (".$nouvel_id.",'DICO_THEME_LIB_MENU', '".$_SESSION['langue']."', '".$_SESSION['Nouveau']."', NULL, NULL);";
            //print $requete;
            $GLOBALS['conn_dico']->Execute($requete);
						insert_traduction('DICO_TRADUCTION', $nouvel_id, 'DICO_THEME_LIB_MENU', $_SESSION['langue'], $_SESSION['Nouveau'], 1);
            
            // Création de l'enregistrement dans DICO_THEME_SYSTEME
            // le secteur de ce nouveau THEME_SYSTEME est celui par défaut 
            //$db                     = $GLOBALS['conn'];
						$NB_LIGNES_FRAME 			= 10;
						$TAILLE_LIB_VERTICAUX = 0;
						$AFF_MAT_LIB_VERTIC		= 0;
						if(isset($_POST['filtre_appart_theme'])) $APPARTENANCE = $_POST['filtre_appart_theme'];
						else $APPARTENANCE = 0;
						//echo '<br>type theme ='.$_SESSION['tab_theme'][$_SESSION['i']][ID_TYPE_THEME].'<br>';
						switch($_SESSION['tab_theme'][$_SESSION['i']][ID_TYPE_THEME]){
								case '1' :{ // matrice
										$NB_LIGNES_FRAME 		= 0;
										if($_POST['aff_mat_lib_vertic']==1){
												$AFF_MAT_LIB_VERTIC	= 1;
										}
										break;
								}
								case '2' :
								case '7' :{ // matrice
										$TAILLE_LIB_VERTICAUX = 30;
										break;
								}
								case '3' :{ // formulaire
										$NB_LIGNES_FRAME = 1;
										break;
								}
						}
            $requete                = "INSERT INTO DICO_THEME_SYSTEME
										(ID_THEME_SYSTEME,ID,ID_SYSTEME,FRAME,NB_LIGNES_FRAME,PERE,PRECEDENT,TAILLE_MENU,
											TAILLE_LIB_VERTICAUX,AFF_MAT_LIB_VERTIC,APPARTENANCE)
                                        VALUES (".$nouvel_id.", ".$_SESSION['tab_theme'][$_SESSION['i']][ID].", 
                                        ".$_SESSION['filtre_secteur_T_S'].", '".$nouvel_id.".html', $NB_LIGNES_FRAME, 0, 0, 220, 
										$TAILLE_LIB_VERTICAUX,$AFF_MAT_LIB_VERTIC,".$APPARTENANCE.");";
            //print $requete;
            $GLOBALS['conn_dico']->Execute($requete);
        }
        
    }

    function maj_theme(){
        // Met à jour les éléments du thème courant (après click sur "Enregistrer")
        
        // mise à jour du libellé dans DICO_TRADUCTION
        $db                     = $GLOBALS['conn_dico'];
        if ($_POST['nom_theme'] <> $_SESSION['tab_theme'][$_SESSION['i']][THEME_LIBELLE]){
            $requete            = "UPDATE DICO_TRADUCTION
                                    SET LIBELLE = ".$GLOBALS['conn_dico']->qstr($_POST['nom_theme'])."
                                    WHERE CODE_NOMENCLATURE =".$_SESSION['tab_theme'][$_SESSION['i']][ID]."
                                    and NOM_TABLE='DICO_THEME';";
            $GLOBALS['conn_dico']->Execute($requete);
        }
        // mise à jour des autres informations directement sur la table DICO_THEME
        /*$requete            = "UPDATE DICO_THEME
                                SET ACTION_POST = '".$_POST['action_post']."',
                                ACTION_THEME = '".$_POST['action_theme']."',
                                ID_TYPE_THEME = ".$_POST['type_theme']."
                                WHERE ID =".$_SESSION['tab_theme'][$_SESSION['i']][ID].";";*/
        $CLASSE = NULL ;
				$ACTION_THEME = NULL ;
				switch($_POST['type_theme']){
						case '1':{
								$CLASSE = "'matrice'" ;
								$ACTION_THEME = "'instance_matrice.php'" ;
								break;
						}
						case '7':{
								$CLASSE = "'mat_grille'" ;
								$ACTION_THEME = "'instance_mat_grille.php'" ;
								break;
						}
						case '8':{
								$CLASSE = "'menu'" ;
								$ACTION_THEME = "''" ;
								break;
						}
						case '2':
						case '3':
						case '4':
						case '5':
						case '6':{
								$CLASSE = "'grille'" ;
								$ACTION_THEME = "'instance_grille.php'" ;
								break;
						}
				}

				$requete            = "UPDATE DICO_THEME
                                SET ACTION_THEME = ".$ACTION_THEME.",
								CLASSE = ".$CLASSE.", ORDRE_THEME = ".$_POST['ordre_theme'].", 
                                ID_TYPE_THEME = ".$_POST['type_theme']."
                                WHERE ID =".$_SESSION['tab_theme'][$_SESSION['i']][ID].";";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
		
		// Mise à jour du statut des zones à inconnu.
		$requete 	= ' UPDATE    DICO_ZONE
						SET   ZONE_STATUT =\'\'      
						WHERE     ID_THEME = '.$_SESSION['tab_theme'][$_SESSION['i']][ID];
		
		$GLOBALS['conn_dico']->Execute($requete);				
    }

    function maj_theme_systeme_filtre(){
        // Met à jour les éléments du thème_systeme courant (après click sur "Enregistrer")
        
        // mise à jour du libellé dans DICO_TRADUCTION
        $db                     = $GLOBALS['conn_dico'];
            $requete            = "UPDATE DICO_TRADUCTION
                                    SET LIBELLE = ".$GLOBALS['conn_dico']->qstr($_POST['libelle_long'])."
                                    WHERE CODE_NOMENCLATURE =".$_SESSION['tab_theme_systeme_filtre'][ID_THEME_SYSTEME]."
                                    and CODE_LANGUE='".$_SESSION['langue']."' 
                                    and NOM_TABLE='DICO_THEME_LIB_LONG';";
            $GLOBALS['conn_dico']->Execute($requete);
            //print $requete.'<BR>';

            $requete            = "UPDATE DICO_TRADUCTION
                                    SET LIBELLE = ".$GLOBALS['conn_dico']->qstr($_POST['libelle_menu'])."
                                    WHERE CODE_NOMENCLATURE =".$_SESSION['tab_theme_systeme_filtre'][ID_THEME_SYSTEME]."
                                    and CODE_LANGUE='".$_SESSION['langue']."' 
                                    and NOM_TABLE='DICO_THEME_LIB_MENU';";
            $GLOBALS['conn_dico']->Execute($requete);
            //print $requete;
        if(!$_POST['taille_lib_verticaux']) $_POST['taille_lib_verticaux'] 	= 0;
				if(!$_POST['aff_mat_lib_vertic']) 	$_POST['aff_mat_lib_vertic'] 		= 0;
        // mise à jour des autres informations directement sur la table DICO_THEME_SYSTEME
        if(isset($_POST['filtre_appart_theme'])) $APPARTENANCE = $_POST['filtre_appart_theme'];
		else $APPARTENANCE = 0;
		$post_nb_ligne = 'NULL';
		if($_POST['nb_ligne']<>'')  $post_nb_ligne = $_POST['nb_ligne'];
		$requete            = "UPDATE DICO_THEME_SYSTEME
                                SET ID_SYSTEME = ".$_SESSION['filtre_secteur_T_S'].",
                                FRAME = '".$_POST['frame']."',
                                TAILLE_MENU = ".$_POST['taille_menu'].",
                                NB_LIGNES_FRAME = ".$post_nb_ligne.",
                                PRECEDENT = ".$_POST['precedent'].",
                                PERE = ".$_POST['theme_pere'].",
								TAILLE_LIB_VERTICAUX = ".$_POST['taille_lib_verticaux'].",
								AFF_MAT_LIB_VERTIC	 = ".$_POST['aff_mat_lib_vertic'].",
								APPARTENANCE = ".$APPARTENANCE." 
                                WHERE ID_THEME_SYSTEME =".$_SESSION['tab_theme_systeme_filtre'][ID_THEME_SYSTEME].";";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
    }
    
    function lit_secteur(){
        // Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
        // TODO: à virer de l'accueil
        /*$db                      = $GLOBALS['conn'];
        $requete                 = "SELECT T_S_E.CODE_TYPE_SYSTEME_ENSEIGNEMENT, D_TRAD.LIBELLE
                                    FROM TYPE_SYSTEME_ENSEIGNEMENT AS T_S_E,  DICO_TRADUCTION AS D_TRAD
                                    WHERE T_S_E.CODE_TYPE_SYSTEME_ENSEIGNEMENT = D_TRAD.CODE_NOMENCLATURE 
                                    And D_TRAD.NOM_TABLE='TYPE_SYSTEME_ENSEIGNEMENT' And D_TRAD.CODE_LANGUE='".$_GET['langue']."';";
        $_SESSION['tab_secteur'] = $db->GetAll($requete);*/
				set_tab_session('secteurs', $_GET['langue']);
    }
        
    function lit_theme(){
        
		if((isset($_SESSION['choix_affich']) && $_SESSION['choix_affich']=='active_themes_rattach'))
		{

			//$_SESSION['i']  = 0;
			if(isset($_GET['filtre_secteur_T_S_en_cours']) && $_GET['filtre_secteur_T_S_en_cours']!=''){
				$secteur=$_GET['filtre_secteur_T_S_en_cours'];
			}elseif(isset($_SESSION['filtre_secteur_T_S']) && $_SESSION['filtre_secteur_T_S']!='')
				$secteur=$_SESSION['filtre_secteur_T_S'];
			
			$appart = 0;
			if(isset($_GET['filtre_appart_theme_en_cours']) && $_GET['filtre_appart_theme_en_cours']!=''){
				$appart=$_GET['filtre_appart_theme_en_cours'];
			}elseif(isset($_SESSION['filtre_appart_T_S']) && $_SESSION['filtre_appart_T_S']!='')
				$appart=$_SESSION['filtre_appart_T_S'];
				
			$requete                = "SELECT D_T.ID, D_T.ORDRE_THEME, D_T.ACTION_THEME, D_T.CLASSE,
											D_TRAD.LIBELLE AS THEME_LIBELLE, D_T.ID_TYPE_THEME
											FROM DICO_THEME AS D_T, DICO_TRADUCTION AS D_TRAD, DICO_THEME_SYSTEME AS D_T_S
											WHERE D_T.ID = D_TRAD.CODE_NOMENCLATURE
											AND D_TRAD.NOM_TABLE='DICO_THEME'
											AND D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'
											AND D_T_S.ID=D_T.ID
											AND D_T_S.ID_SYSTEME=".$secteur."
											AND D_T_S.APPARTENANCE=".$appart."
											ORDER BY D_T.ORDRE_THEME;";
											//ORDER BY D_TRAD.LIBELLE;";					
		}						
		elseif((isset($_SESSION['choix_affich']) && $_SESSION['choix_affich']=='active_themes'))
		{

			//$_SESSION['i']  = 0;
			if(isset($_GET['filtre_secteur_T_S_en_cours']) && $_GET['filtre_secteur_T_S_en_cours']!=''){
				$secteur=$_GET['filtre_secteur_T_S_en_cours'];
			}elseif(isset($_SESSION['filtre_secteur_T_S']) && $_SESSION['filtre_secteur_T_S']!='')
				$secteur=$_SESSION['filtre_secteur_T_S'];
				
			$requete                = "SELECT D_T.ID, D_T.ORDRE_THEME, D_T.ACTION_THEME, D_T.CLASSE,
											D_TRAD.LIBELLE AS THEME_LIBELLE, D_T.ID_TYPE_THEME
											FROM DICO_THEME AS D_T, DICO_TRADUCTION AS D_TRAD, DICO_THEME_SYSTEME AS D_T_S
											WHERE D_T.ID = D_TRAD.CODE_NOMENCLATURE
											AND D_TRAD.NOM_TABLE='DICO_THEME'
											AND D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'
											AND D_T_S.ID=D_T.ID
											AND D_T_S.ID_SYSTEME=".$secteur."
											ORDER BY D_T.ORDRE_THEME;";
											//ORDER BY D_TRAD.LIBELLE;";					
		}						
		elseif (isset($_POST['btn_theme']) && $_POST['btn_theme'] == recherche_libelle_page('Ajout')){
			$requete                = "SELECT D_T.ID, D_T.ORDRE_THEME, D_T.ACTION_THEME, D_T.CLASSE,
											D_TRAD.LIBELLE AS THEME_LIBELLE, D_T.ID_TYPE_THEME
											FROM DICO_THEME AS D_T, DICO_TRADUCTION AS D_TRAD
											WHERE D_T.ID = D_TRAD.CODE_NOMENCLATURE
											AND D_TRAD.NOM_TABLE='DICO_THEME'
											AND D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'
											ORDER BY D_T.ORDRE_THEME;";
											//ORDER BY D_TRAD.LIBELLE;";
		}else{
			$requete                = "SELECT D_T.ID, D_T.ORDRE_THEME, D_T.ACTION_THEME, D_T.CLASSE,
											D_TRAD.LIBELLE AS THEME_LIBELLE, D_T.ID_TYPE_THEME, D_T_S.ID_SYSTEME
											FROM DICO_THEME AS D_T, DICO_TRADUCTION AS D_TRAD, DICO_THEME_SYSTEME AS D_T_S
											WHERE D_T.ID = D_TRAD.CODE_NOMENCLATURE
											AND D_TRAD.NOM_TABLE='DICO_THEME'
											AND D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'
											AND D_T_S.ID=D_T.ID
											ORDER BY D_T.ORDRE_THEME;";
											//ORDER BY D_TRAD.LIBELLE;";
		}
		//echo $requete;	
		$_SESSION['tab_theme']  = $GLOBALS['conn_dico']->GetAll($requete);
			
        // Chargement des types de thèmes
        $requete                = "SELECT ID_TYPE_THEME, LIBELLE, LIBELLE_TRAD
                                    FROM DICO_TYPE_THEME
									WHERE CODE_LANGUE = '".$_SESSION['langue']."'
									ORDER BY ORDRE_TYPE_THEME;";
        //print $requete;
        $_SESSION['tab_type_theme']  = $GLOBALS['conn_dico']->GetAll($requete);
        
        
    } //FIN lit_theme()

    function lit_theme_par_systeme(){
        // Chargement des thèmes_systeme pour selection des pères et précédents
        $requete                = "SELECT D_T_S.ID_THEME_SYSTEME, D_TRAD.LIBELLE, D_T_S.PRECEDENT 
                                    FROM DICO_THEME_SYSTEME AS D_T_S, DICO_THEME AS D_T, DICO_TRADUCTION AS D_TRAD
                                    WHERE D_T_S.ID = D_T.ID
                                    And D_T_S.ID_THEME_SYSTEME = D_TRAD.CODE_NOMENCLATURE
                                    And D_TRAD.NOM_TABLE='DICO_THEME_LIB_MENU'
                                    And D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'
                                    And D_T_S.ID_SYSTEME=".$_SESSION['filtre_secteur_T_S']."
									ORDER BY D_T.ORDRE_THEME;";

        $_SESSION['tab_theme_par_systeme']  = $GLOBALS['conn_dico']->GetAll($requete);
        
    } //FIN lit_theme_systeme()

    function lit_theme_systeme(){
        // Chargement des thèmes_systeme pour affichage select "themes_systeme_dispo"
        $requete                = "SELECT D_T_S.ID_THEME_SYSTEME, D_T_S.ID, D_T_S.FRAME, D_T_S.NB_LIGNES_FRAME, D_T_S.PERE,
                                    D_T_S.PRECEDENT, D_T_S.TAILLE_MENU, D_T_S.ID_SYSTEME, D_T_S.NB_LIGNES_FRAME, D_TRAD.LIBELLE 
                                    FROM DICO_THEME_SYSTEME AS D_T_S, DICO_TRADUCTION AS D_TRAD
                                    WHERE D_T_S.ID_THEME_SYSTEME = D_TRAD.CODE_NOMENCLATURE
                                    And D_TRAD.NOM_TABLE='DICO_THEME_LIB_MENU'
                                    And D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'
                                    And D_T_S.ID=".$_SESSION['tab_theme'][$_SESSION['i']]['ID'].";";
        $_SESSION['tab_theme_systeme']  = $GLOBALS['conn_dico']->GetAll($requete);
    } //FIN lit_theme_systeme()

    function lit_theme_systeme_filtre(){
        // Chargement des infos concernant le thème_systeme du secteur_filtre en cours à partir de la BD
        // Chargement des thèmes_systeme
        $requete                = "SELECT D_T_S.ID_THEME_SYSTEME, D_T_S.ID, D_T_S.FRAME, D_T_S.NB_LIGNES_FRAME, D_T_S.PERE,
                                    D_T_S.PRECEDENT, D_T_S.TAILLE_MENU, D_T_S.ID_SYSTEME, 
									D_T_S.TAILLE_LIB_VERTICAUX, D_T_S.AFF_MAT_LIB_VERTIC, D_T_S.APPARTENANCE, D_TRAD.LIBELLE 
                                    FROM DICO_THEME_SYSTEME AS D_T_S, DICO_TRADUCTION AS D_TRAD
                                    WHERE D_T_S.ID_THEME_SYSTEME = D_TRAD.CODE_NOMENCLATURE
                                    And D_T_S.ID_SYSTEME=".$_SESSION['filtre_secteur_T_S']."
                                    And D_TRAD.NOM_TABLE='DICO_THEME_LIB_LONG'
                                    And D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'
                                    And D_T_S.ID=".$_SESSION['tab_theme'][$_SESSION['i']]['ID'].";";
       // print $requete;
        $_SESSION['tab_theme_systeme_filtre']  = $GLOBALS['conn_dico']->GetRow($requete);
		//	print_r($_SESSION['tab_theme_systeme_filtre']);
        // Chargement du libellé menu
        $requete                = "SELECT D_TRAD.LIBELLE 
                                    FROM DICO_THEME_SYSTEME AS D_T_S, DICO_TRADUCTION AS D_TRAD
                                    WHERE D_T_S.ID_THEME_SYSTEME = D_TRAD.CODE_NOMENCLATURE
                                    And D_T_S.ID_SYSTEME=".$_SESSION['filtre_secteur_T_S']."
                                    And D_TRAD.NOM_TABLE='DICO_THEME_LIB_MENU'
                                    And D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'
                                    And D_T_S.ID=".$_SESSION['tab_theme'][$_SESSION['i']]['ID'].";";
        //print '<br><br>'.$requete;
        $_SESSION['tab_libelle_menu']  = $GLOBALS['conn_dico']->GetRow($requete);
        
    } //FIN lit_theme_systeme()
    
    
    function genere_theme_courant(){
        // Cette fonction génére le frame associé au theme_systeme courant
        // Elle fonctionne à l'image de genere_theme.php mais ici, les tableaux de langues, systems et thèmes
        // ne contiennent que les courants.
        //die('here'.$_SESSION['filtre_secteur_T_S']);
        $langues	        	=	array();
        $id_themes	        =	array();
        $id_systemes	      =	array();
        set_time_limit(0);
        ini_set("memory_limit", "128M");
        array_push($langues,     $_SESSION['langue']);
        array_push($id_themes,   $_SESSION['tab_theme'][$_SESSION['i']]['ID']);
        array_push($id_systemes, $_SESSION['filtre_secteur_T_S']);

        require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame.class.php';
        $form                   =	new frame( $id_themes, $langues, $id_systemes, '', '' );
        if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']){
			require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';
			$form_mobile            =	new frame_mobile( $id_themes, $langues, $id_systemes, '', '' );
        }
		//die();
    } //FIN genere_theme_courant()
	if(!isset($_SESSION['filtre_secteur_T_S'])) $_SESSION['filtre_secteur_T_S'] = $_SESSION['secteur'];
	if(isset($_POST['choix_affich']) && $_POST['choix_affich'] <> ''){
		//($_POST['choix_affich']==1) ? ($_SESSION['choix_affich'] = 'all_themes') : ($_SESSION['choix_affich'] = 'active_themes');
		if($_POST['choix_affich']==1)	$_SESSION['choix_affich'] = 'all_themes';
		elseif($_POST['choix_affich']==2)	$_SESSION['choix_affich'] = 'active_themes';
		elseif($_POST['choix_affich']==3)	$_SESSION['choix_affich'] = 'active_themes_rattach';
	}elseif(isset($_GET['choix_affich']) && $_GET['choix_affich'] <> ''){
		$_SESSION['choix_affich'] = $_GET['choix_affich'];
	}
	if(!isset($_SESSION['choix_affich'])){
		$_SESSION['choix_affich'] = 'active_themes';
	}
	$val_choix=$_SESSION['choix_affich'];
	$curr_camp = -1;
	if(isset($_GET['supp']) and (trim($_GET['supp']) <> '') and count($_POST)==0){
			switch($_GET['supp']){
					case'SupThm':{
							$_POST[btn_theme] 				= 'SupThm';
							break;
					}
					case'SupSsThm':{
							$_POST[btn_theme_systeme]	= 'SupSsThm';
							break;
					}
			}
	}
 	//Ajout HEBIE pour gerer l'affichage par defaut du theme en cours de saisie au niveau de la gestion des themes
	if(count($_POST) == 0 && !isset($_GET['theme_en_cours']) && !isset($_GET['theme_from_zones']) && isset($_SESSION['theme']) && $_SESSION['theme']!=''){
		$long_syst_id=strlen(''.$_SESSION['secteur']);
		$long_theme_syst_id=strlen(''.$_SESSION['theme']);
		$long_theme_id=$long_theme_syst_id-$long_syst_id;
		$str_theme_id=substr($_SESSION['theme'],0,$long_theme_id);
		lit_theme();
		$_SESSION['i'] = get_i_theme($str_theme_id);
	}
	//Fin Ajout HEBIE
	if (isset($_POST['btn_theme'])){
        $_SESSION['message']= '';
		if (isset($_GET['filtre_appart_theme_en_cours']) && $_GET['filtre_appart_theme_en_cours']<>''){
			$_SESSION['filtre_appart_T_S']     = $_GET['filtre_appart_theme_en_cours'];
			$curr_camp = $_SESSION['filtre_appart_T_S'];
		} else {
			$curr_camp = -1;
		}
        switch ($_POST['btn_theme']){
            case '>':           if ($_SESSION['i'] < count($_SESSION['tab_theme'])-1){
                                    $_SESSION['i']          += 1; 
                                    lit_theme_systeme();
                                    lit_theme_systeme_filtre();
                                }
                                break;
            case '<':           if ($_SESSION['i'] > 0){
                                    $_SESSION['i']          -= 1;
                                    lit_theme_systeme();
                                    lit_theme_systeme_filtre();
                                }
                                break;
            case '>>':          $_SESSION['i']               = count($_SESSION['tab_theme'])-1; 
                                    lit_theme_systeme();
                                    lit_theme_systeme_filtre();
                                break;
            case '<<':          $_SESSION['i']               = 0; 
                                    lit_theme_systeme();
                                    lit_theme_systeme_filtre();
                                break;
            case recherche_libelle_page('Ajout'):
								$tmp_choix_affich = $_SESSION['choix_affich'];//Ajout Hebie pour Création d'un nouvel enregistrement dans DICO_THEME_SYSTEME
								$_SESSION['choix_affich'] = 'all_themes';//Ajout Hebie pour Création d'un nouvel enregistrement dans DICO_THEME_SYSTEME
								lit_theme();
								ajout_theme(); // Création d'un nouvel enregistrement dans DICO_THEME et dans DICO_TRADUCTION
                                lit_theme(); // Lecture dans la BD pour rafraichir
								foreach($_SESSION['tab_theme'] as $k=>$row){
                                    if ($row[THEME_LIBELLE] == $_SESSION['Nouveau']){
                                        $_SESSION['i']        = $k; 
                                    }
                                }
								//Ajout Hebie pour Création d'un nouvel enregistrement dans DICO_THEME_SYSTEME
								ajout_theme_systeme();
								$_SESSION['choix_affich'] = $tmp_choix_affich;
								lit_theme();
								foreach($_SESSION['tab_theme'] as $k=>$row){
                                    if ($row[THEME_LIBELLE] == $_SESSION['Nouveau']){
                                        $_SESSION['i']        = $k; 
                                    }
                                }
								//Fin ajout HEBIE
								lit_theme_systeme();
                                lit_theme_systeme_filtre();
								lit_theme_par_systeme();// Ajout Hebie pour Création d'un nouvel enregistrement dans DICO_THEME_SYSTEME
                                break;
            case recherche_libelle_page('Enregistre'):
                                maj_theme(); // Mise à jour des modifications sur l'enregistrement courant
                                lit_theme(); // Lecture dans la BD pour rafraichir
                                foreach($_SESSION['tab_theme'] as $k=>$row){
                                    if ($row[THEME_LIBELLE] == $_POST['nom_theme']){
                                        $_SESSION['i']        = $k; 
                                    }
                                }
								maj_theme_systeme_filtre();//Ajout Hebie pour maj d'un enregistrement dans DICO_THEME_SYSTEME
								lit_theme_systeme();
                                lit_theme_systeme_filtre();
                                lit_theme_par_systeme();//Ajout Hebie pour maj d'un enregistrement dans DICO_THEME_SYSTEME
                                break;
            case 'SupThm':
                                supprime_theme_systeme($_SESSION['tab_theme_systeme_filtre'][ID_THEME_SYSTEME]);//Ajout Hebie pour suppr d'un enregistrement dans DICO_THEME_SYSTEME
                                supprime_theme(); // Suppression du THEME courant
                                $_SESSION['i']          = 0;
                                lit_theme(); // Lecture dans la BD pour rafraichir
                                lit_theme_systeme();
                                lit_theme_systeme_filtre();
								lit_theme_par_systeme();//Ajout Hebie pour suppr d'un enregistrement dans DICO_THEME_SYSTEME
                                break;
        }
		
    }
    else if (isset($_GET['theme_from_zones']) and !isset($_POST['btn_theme_systeme'])){
		$_SESSION['message']= '';
		$_SESSION['filtre_secteur_T_S'] = $_SESSION['secteur'];
		lit_theme(); 
        // Accès direct au theme_en_cours (par le select)
		if(isset($_GET['theme_from_zones'])) $_SESSION['i']          = get_i_theme($_GET['theme_from_zones']);
		$_GET['theme_en_cours'] = -99;   // Je ne le met pas à NULL pour pouvoir le tester plus haut
                                        // et ne pas relancer la requête de chargement
		lit_theme_systeme();            // Lecture des thème_systèmes du theme courrant
        lit_theme_systeme_filtre();            // Lecture du thème_système_filtré du theme courrant
        lit_theme_par_systeme();        // Lecture des thèmes_système du secteur (pour pères et précédents)
    }
    else if (!isset($_GET['theme_en_cours']) and !isset($_POST['btn_theme_systeme']) 
            and !isset($_GET['theme_systeme_en_cours']) and !isset($_GET['filtre_secteur_T_S_en_cours'])){
		
		$_SESSION['message']= '';
        $_SESSION['filtre_secteur_T_S'] = $_SESSION['secteur'];
        // TODO: Pb bizarre avec le tab_seteur
        //lit_secteur();                  // Lecture des libellés des secteurs (pour affichage à côté des thèmes_systèmes)
        lit_theme();                    // Lecture des thèmes dans la BD au démarrage
        if (!isset($_SESSION['theme']) || $_SESSION['theme'] == '')	$_SESSION['i'] = 0;
		//die('===' .$_SESSION['i'].'===' );
        lit_theme_systeme();            // Lecture des thème_systèmes dans la BD au démarrage
        lit_theme_systeme_filtre();     // Lecture du thème_système_filtré du theme courrant
        lit_theme_par_systeme();        // Lecture des thèmes_système du secteur (pour pères et précédents)
        
    }else if (isset($_GET['theme_en_cours']) and $_GET['theme_en_cours']<> -99 and !isset($_GET['theme_systeme_en_cours'])
             and !isset($_GET['filtre_secteur_T_S_en_cours'])){
        $_SESSION['message']= '';
        
		// Accès direct au theme_en_cours (par le select)
        $_SESSION['i']          = $_GET['theme_en_cours'];
        $_GET['theme_en_cours'] = -99;   // Je ne le met pas à NULL pour pouvoir le tester plus haut
                                        // et ne pas relancer la requête de chargement
        lit_theme_systeme();            // Lecture des thème_systèmes du theme courrant
		$_SESSION['filtre_secteur_T_S'] = $_SESSION['tab_theme_systeme'][0]['ID_SYSTEME'];
		if($_SESSION['tab_theme_systeme'][0]['ID_SYSTEME'] != $_SESSION['secteur']){
			$_SESSION['secteur'] =  $_SESSION['tab_theme_systeme'][0]['ID_SYSTEME'];
			unset ($_SESSION['hierarchie_regroup']); 
			unset ($_SESSION['infos_etab']);
		}
		lit_theme_systeme_filtre();            // Lecture du thème_système_filtré du theme courrant
       
    } else if (isset($_GET['theme_systeme_en_cours'])  and !isset($_GET['filtre_secteur_T_S_en_cours'])){ 
        $_SESSION['message']= '';
        // Accès au theme_systeme_en_cours
        lit_theme_systeme();            // Lecture des thème_systèmes du theme courrant
        
    } else if (isset($_GET['filtre_secteur_T_S_en_cours']) && $_GET['filtre_secteur_T_S_en_cours']<>''){ 
        $_SESSION['message']= '';
        if(isset($_GET['choix_affich']) && $_GET['choix_affich'] == 'all_themes'){
			$_SESSION['i'] = 0;
			lit_theme();
			$_GET['filtre_secteur_T_S_en_cours'] = $_SESSION['tab_theme'][0]['ID_SYSTEME'];
		}elseif(isset($_GET['choix_affich']) && $_GET['choix_affich'] == 'active_themes_rattach'){
			$_SESSION['choix_affich'] = 'active_themes_rattach';
			$val_choix = 'active_themes_rattach';
		}elseif(!isset($_GET['choix_affich']) && !isset($_POST['choix_affich'])){
			$_SESSION['choix_affich'] = 'active_themes';
			$val_choix = 'active_themes';
		}
		$_SESSION['filtre_secteur_T_S']     = $_GET['filtre_secteur_T_S_en_cours'];
		if (isset($_GET['filtre_appart_theme_en_cours'])){
			$_SESSION['filtre_appart_T_S']     = $_GET['filtre_appart_theme_en_cours'];
			$curr_camp = $_SESSION['filtre_appart_T_S'];
		} else {
			$curr_camp = -1;
		}
		if($_GET['filtre_secteur_T_S_en_cours'] != $_SESSION['secteur']){
			$_SESSION['secteur'] = $_GET['filtre_secteur_T_S_en_cours'];
			unset ($_SESSION['hierarchie_regroup']); 
			unset ($_SESSION['infos_etab']);
		}
		lit_theme();
		lit_theme_par_systeme();
        lit_theme_systeme_filtre();     // Lecture du thème_systèmes du theme courrant et secteur courant
    }
	if (isset($_POST['btn_genere'])){ 
		genere_theme_courant();
    } else {
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>

<title>Document sans titre</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form_theme" method="post" action="">
<BR><BR>
    <table class="center-table">
        <caption>
        <?php echo recherche_libelle_page('Gest_Thm'); ?>
        </caption>
        <tr> 
            <td> <table>
                    <tr> 
                        <td><?php echo recherche_libelle_page('NumTheme'); ?></td>
                        <td><?php echo $_SESSION['tab_theme'][$_SESSION['i']]['ID'] ?></td>
                    </tr>
					<tr> 
                        <td><?php echo recherche_libelle_page('OrdreTheme'); ?></td>
                        <td> <input type="text" size="5" name="ordre_theme" value="<?php echo $_SESSION['tab_theme'][$_SESSION['i']]['ORDRE_THEME'] ?>"> 
                        </td>
                    </tr>
                    <tr> 
                        <td><?php echo recherche_libelle_page('NomTheme'); ?></td>
                        <td> <input type="text" name="nom_theme" value="<?php echo $_SESSION['tab_theme'][$_SESSION['i']]['THEME_LIBELLE'] ?>"> 
                        </td>
                    </tr>
                    <tr> 
                        <td><?php echo recherche_libelle_page('TypeTheme'); ?></td>
                        <td> <select name="type_theme" onChange="Assoc_Prop_Type_Thm (this.value);">
                                <?php
			foreach ($_SESSION['tab_type_theme'] as $k => $v){
                echo "<option value='".$v['ID_TYPE_THEME']."'";
                if ($v['ID_TYPE_THEME'] == $_SESSION['tab_theme'][$_SESSION['i']]['ID_TYPE_THEME']){
                    echo " selected";
                }
                echo ">".$v['LIBELLE_TRAD']."</option>";
            }
        ?>
                            </select> <input type="hidden" name="action_theme" value="<?php echo $_SESSION['tab_theme'][$_SESSION['i']]['ACTION_THEME'] ?>"> 
                            <input type="hidden" name="classe" value="<?php echo $_SESSION['tab_theme'][$_SESSION['i']]['CLASSE'] ?>"> 
                        </td>
                    </tr>
                    <!--
	<tr> 
    <td>Action THEME:</td>
    <td>
        <input style="color :blue;" readonly="1" type="text" name="action_theme" value="<?php //echo $_SESSION['tab_theme'][$_SESSION['i']][ACTION_THEME] ?>">
    </td>
  </tr>
  <tr> 
    <td>CLASS:</td>
    <td>
        <input style="color :blue;" readonly="1" type="text" name="classe" value="<?php //cho $_SESSION['tab_theme'][$_SESSION['i']][CLASSE] ?>">
    </td>
  </tr> -->
                </table>
                &nbsp; 
                <br/> <table align="center">
                    <tr> 
                        <td><select name="themes_dispo" size="11" onChange="toggle_theme()">
                          <?php
			if(!isset($_SESSION['i']) || $_SESSION['i']=='') $_SESSION['i']= 0;
			if(is_array($_SESSION['tab_theme']))
			foreach ($_SESSION['tab_theme'] as $k => $v){
                echo "<option value='".$k."'";
                if ($v['ID'] == $_SESSION['tab_theme'][$_SESSION['i']]['ID']){
                    echo " selected";
                }
                echo ">".$v['THEME_LIBELLE']."</option>";
            }
        ?>
                        </select></td>
                        <td align="center" style="vertical-align:middle"> <br/>
							<input style="width:100%;" type="submit" name="btn_genere" <?php echo ' value="'.recherche_libelle_page('genere').'"'; ?>>
                            
                            <?php if( isset($_SESSION['tab_theme'][$_SESSION['i']]['ID'])){ ?>
							<br/>
                            <input style="width:100%;" onClick="javascript:location.href= 'administration.php?val=gestzone&id_theme_choisi=<?php echo $_SESSION['tab_theme'][$_SESSION['i']]['ID'];?>'" type="button" <?php echo ' value="'.recherche_libelle_page('gest_z_thm').'"'; ?>> 
                            <?php } ?>                        </td>
                    </tr>
                </table>
				<br/>
				<table align="center">
                    <tr> 
					<?php if((!isset($_POST['btn_theme'])) || (isset($_POST['btn_theme']) && $_POST['btn_theme'] <> recherche_libelle_page('Ajout'))){ ?>
                        <td> <input type="submit" name="btn_theme" <?php echo ' value="'.recherche_libelle_page('Ajout').'"'; ?>>                        </td>
					<?php } ?>
                        <td> <input type="submit" name="btn_theme" <?php echo ' value="'.recherche_libelle_page('Enregistre').'"'; ?>>                        </td>
                        <td><input type="button" name="btn_theme2" <?php echo ' value="'.recherche_libelle_page('Supprime').'"'; ?>
				onClick="AlertSupThm(<?php echo '\''.addslashes($_SESSION['tab_theme'][$_SESSION['i']]['THEME_LIBELLE']).'\',\'theme\'';?>)"></td>
                        <td> <input type="submit" name="btn_theme" value="<<">                        </td>
                        <td> <input type="submit" name="btn_theme" value="<">                        </td>
                        <td> <input type="submit" name="btn_theme" value=">">                        </td>
                        <td> <input type="submit" name="btn_theme" value=">>">                        </td>
                    </tr>
                </table>
				
				</td>
            <td> <table>
                    <tr> 
                        <td><?php echo recherche_libelle_page('FiltreSE'); ?></td>
                        <td> <select name="filtre_secteur_T_S" onChange="toggle_filtre_secteur_T_S()">
								<?php
									foreach ((array)$_SESSION['tab_secteur'] as $k => $v){
										echo "<option value='".$v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]."'";
										if ($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $_SESSION['filtre_secteur_T_S']){
											echo " selected";
										}
										echo ">".$v['LIBELLE']."</option>";
									}
								?>
                            </select>
						</td>
                    </tr>
					<?php if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>""){ ?>
					<tr> 
                        <td><?php echo recherche_libelle_page('FiltreRattachTheme'); ?></td>
                        <td>
							<select name="filtre_appart_theme"  onChange="clear_filtre_appart_T_S()">
								<option value=""></option>
								<?php
									$requete = "SELECT * FROM ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']." ORDER BY ".$GLOBALS['PARAM']['ORDRE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT'];
        							$rs_type_entite= $GLOBALS['conn']->GetAll($requete);
        							$nb = count($rs_type_entite);
									$i = 1;
									$trouv = false;
									if($curr_camp == -1) $curr_camp = $_SESSION['tab_theme_systeme_filtre']['APPARTENANCE'];
									foreach ($rs_type_entite as $k => $v){
										echo "<option value='".$v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."'";
										if ((($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']] == $curr_camp))){
											echo " selected";
											$trouv = true;
										}
										echo ">".$v[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."</option>";
										$i++;
									}
								?>
                            </select>
						</td>
                    </tr>
					<?php } ?>
					<tr>
					<td colspan="2">
					<table>
					<tr>
						<td>
						<input type="radio" name="choix_affich" value="1" <?php if((isset($val_choix) && $val_choix=='all_themes'))  echo " checked "; ?> onClick="reload('all_themes')">
						  <b><?php echo recherche_libelle_page('all_themes'); ?></b>
						</td>
						<td>
						<input type="radio" name="choix_affich" value="2" <?php if((isset($val_choix) && $val_choix=='active_themes') || !isset($val_choix))  echo " checked " ?> onClick="reload('active_themes')">
						<b><?php echo recherche_libelle_page('active_themes'); ?></b>
						</td>
						<?php if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>""){ ?>
						<td>
						<input type="radio" name="choix_affich" id="choix_affich_3" value="3" <?php if((isset($val_choix) && $val_choix=='active_themes_rattach'))  echo " checked " ?> onClick="reload('active_themes_rattach')">
						<b><?php echo recherche_libelle_page('active_themes_rattach');?></b>
						</td>
						<?php } ?>
					</tr>
					</table>
					</td>
					</tr>
                </table>
                &nbsp; <table>
                    <tr> 
                        <td><?php echo recherche_libelle_page('NumTheme'); ?></td>
                        <td><?php echo $_SESSION['tab_theme_systeme_filtre']['ID'] ?></td>
                    </tr>
                    <tr> 
                        <td><?php echo recherche_libelle_page('NumT_S'); ?></td>
                        <td><?php echo $_SESSION['tab_theme_systeme_filtre']['ID_THEME_SYSTEME'] ?></td>
                    </tr>
                    <?php if($_SESSION['tab_theme'][$_SESSION['i']]['ID_TYPE_THEME'] <> 8){ ?>
					<tr> 
                        <td><?php echo recherche_libelle_page('LibLong'); ?></td>
                        <td> <input type="text" size="50px" name="libelle_long" value="<?php if($_SESSION['tab_theme'][$_SESSION['i']]['THEME_LIBELLE'] <> $_SESSION['Nouveau'] && $_SESSION['tab_theme_systeme_filtre']['LIBELLE'] == $_SESSION['Nouveau']) echo $_SESSION['tab_theme'][$_SESSION['i']]['THEME_LIBELLE']; else echo $_SESSION['tab_theme_systeme_filtre']['LIBELLE'] ?>"></td>
                    </tr>
					<?php } ?>
					<?php if($_SESSION['tab_theme'][$_SESSION['i']]['ID_TYPE_THEME'] <> 8){ ?>
                    <tr> 
                        <td><?php echo recherche_libelle_page('Frame'); ?></td>
                        <td> <input type="text" size="30px" name="frame" value="<?php if($_SESSION['tab_theme'][$_SESSION['i']]['THEME_LIBELLE'] <> $_SESSION['Nouveau'] && $_SESSION['tab_theme_systeme_filtre']['FRAME'] == '') echo str_replace(' ','_',$_SESSION['tab_theme'][$_SESSION['i']]['THEME_LIBELLE']).'_'.$_SESSION['filtre_secteur_T_S'].'.html'; else echo $_SESSION['tab_theme_systeme_filtre']['FRAME'] ?>"></td>
                    </tr>
					<?php } ?>
				 	<?php if($_SESSION['tab_theme'][$_SESSION['i']]['ID_TYPE_THEME'] <> 8){ ?>
                    <tr> 
                        <td><?php if(!in_array($_SESSION['tab_theme'][$_SESSION['i']]['ID_TYPE_THEME'], array(4,5,6))) echo recherche_libelle_page('NbLigne'); else echo recherche_libelle_page('NbColonne'); ?></td>
                        <td> <input type="text" size="30px" name="nb_ligne" value="<?php if($_SESSION['tab_theme'][$_SESSION['i']]['ID_TYPE_THEME'] == 1) echo '0'; elseif($_SESSION['tab_theme'][$_SESSION['i']]['ID_TYPE_THEME'] == 3) echo '1'; elseif($_SESSION['tab_theme_systeme_filtre']['NB_LIGNES_FRAME'] > 1) echo $_SESSION['tab_theme_systeme_filtre']['NB_LIGNES_FRAME']; else echo '10' ?>"></td>
                    </tr>
					<?php } ?>
                    <tr> 
                        <td><?php echo recherche_libelle_page('LibMenu'); ?></td>
                        <td> <input type="text" size="30px" name="libelle_menu" value="<?php if($_SESSION['tab_theme'][$_SESSION['i']]['THEME_LIBELLE'] <> $_SESSION['Nouveau'] && $_SESSION['tab_libelle_menu']['LIBELLE'] == $_SESSION['Nouveau']) echo $_SESSION['tab_theme'][$_SESSION['i']]['THEME_LIBELLE']; else echo $_SESSION['tab_libelle_menu']['LIBELLE'] ?>"></td>
                    </tr>
                    <?php
		switch($_SESSION['tab_theme'][$_SESSION['i']]['ID_TYPE_THEME']){

			case '1' :{ // matrice
					?>
                    <tr> 
                        <td><?php echo recherche_libelle_page('MatLVert'); ?></td>
                        <td> <input type="checkbox" name="aff_mat_lib_vertic" value="1"
								<?php if ($_SESSION['tab_theme_systeme_filtre']['AFF_MAT_LIB_VERTIC']==1) echo ' CHECKED';?>></td>
                    </tr>
                    <?php
			}
			case '2' : // grille
			case '3' : // grille
			case '7' :{ // mat_grille
					?>
                    <tr> 
                        <td><?php echo recherche_libelle_page('TLibVert'); ?></td>
                        <td> <input type="text" name="taille_lib_verticaux" value="<?php echo $_SESSION['tab_theme_systeme_filtre']['TAILLE_LIB_VERTICAUX']; ?>"></td>
                    </tr>
                    <?php
			}
	}
	?>
                    <tr> 
                        <td><?php echo recherche_libelle_page('Pere'); ?></td>
                        <td><select name="theme_pere">
                          <?php
            echo "<option value='1030'";
            if ($v['ID_THEME_SYSTEME'] == 1030){
                echo " selected";
            }
            echo ">Questionnaire</option>";
            foreach ($_SESSION['tab_theme_par_systeme'] as $k => $v){
                if ($v['ID_THEME_SYSTEME'] <> $_SESSION['tab_theme_systeme_filtre']['ID_THEME_SYSTEME']){ // Il est inutile de mettre l'éléement courant dans le combo
                    echo "<option value='".$v['ID_THEME_SYSTEME']."'";
                    if ($v['ID_THEME_SYSTEME'] == $_SESSION['tab_theme_systeme_filtre']['PERE']){
                        echo " selected";
                    }
                    echo ">".$v['LIBELLE']."</option>";
                }
            }
        ?>
                        </select>
						<?php echo '('.$_SESSION['tab_theme_systeme_filtre']['PERE'].')' ?></td>
                    </tr>
                    <tr> 
                        <td><?php echo recherche_libelle_page('Preced'); ?></td>
                        <td> <select name="precedent">
                                <?php echo "<option value='0'";
            if ($v['ID_THEME_SYSTEME'] == 0){
                echo " selected";
            }
            echo ">Premier</option>";
            foreach ($_SESSION['tab_theme_par_systeme'] as $k => $v){
                if ($v['ID_THEME_SYSTEME'] <> $_SESSION['tab_theme_systeme_filtre']['ID_THEME_SYSTEME']){ // Il est inutile de mettre l'éléement courant dans le combo
                    echo "<option value='".$v['ID_THEME_SYSTEME']."'";
                    if ($v['ID_THEME_SYSTEME'] == $_SESSION['tab_theme_systeme_filtre']['PRECEDENT']){
                        echo " selected";
                    }
                    echo ">".$v['LIBELLE']."</option>";
                }
            }
        ?>
                            </select> <?php echo '('.$_SESSION['tab_theme_systeme_filtre']['PRECEDENT'].')' ?></td>
                    </tr>
                    <tr> 
                        <td><?php echo recherche_libelle_page('Taille'); ?></td>
                        <td> <input type="text" name="taille_menu" value="<?php echo $_SESSION['tab_theme_systeme_filtre']['TAILLE_MENU'] ?>"></td>
                    </tr>
                </table>
                <br>
                <br> </td>
        </tr>
    </table>
</form>
</body>
</html>

<?php } ?>