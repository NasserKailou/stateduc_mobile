<?php
lit_libelles_page('/export_grille.php');

function formulaire_concat_zone_html($element,$langue,$id_systeme,$annee=''){
	//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
	${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
	//Fin ajout Hebie
	$html ='';
	$suffix_ann = '';
	if($annee<>'') $suffix_ann = "_ANN_".$annee;
	static $deja_aff_sys = array(); // permet de ne pas repeter un objet de type clé à l'affichage
	// c'est le cas de CODE_REGROUPEMENT(type:systeme_valeur_multiple)ds ENV_SOCIO et ds ECONOMIE_ENV_SOCIO
	if($element['TYPE_OBJET']=='booleen'){

			$html.= $GLOBALS['libelle_oui'].'&nbsp;';
			$html.= "\$".$element['CHAMP_PERE']."_0_1".$suffix_ann;
			
			$html.= "\t\t&nbsp;&nbsp;&nbsp;&nbsp;";
			$html.= $GLOBALS['libelle_non'].'&nbsp;';
			$html.= "\$".$element['CHAMP_PERE']."_0_0".$suffix_ann;
			
	}
	elseif($element['TYPE_OBJET']=='checkbox'){
			//////////////////////
			
			$html.= "\$".$element['CHAMP_PERE']."_0".$suffix_ann;
			//////////////////////
	}
	elseif($element['TYPE_OBJET']=='text'){
			
			$html.= "\$".$element['CHAMP_PERE']."_0".$suffix_ann;
			
			//////////////////////
	}
	elseif($element['TYPE_OBJET']=='label'){
			//////////////////////
			$html.= recherche_libelle_zone($element['ID_ZONE'],$langue);						
			//////////////////////
	}
	elseif($element['TYPE_OBJET']=='combo'){
			//////////////////////
				
			$dynamic_content_object = false ;
			if( (trim($element['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($element['CHAMP_INTERFACE']) <> '') && (trim($element['TABLE_INTERFACE']) <> '')){
				//if( ereg('\$code_annee', $element['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $element['REQUETE_CHAMP_INTERFACE']) ){
					$dynamic_content_object = true ;
				//}
			}
			if($dynamic_content_object == true){
				$html 	.= '<!--DynContentObj_'.$element['ID_ZONE'].'_'.$element['CHAMP_PERE'].'_0'.$suffix_ann.'-->'."\n"; 
			}else{
				$result_nomenc 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']); 
				foreach($result_nomenc as $element_result_nomenc){
						//Un valeur du combo pour chaque valeur de la nomenclature 
						$html 	.= "\$".$element['CHAMP_PERE']."_0_".$element_result_nomenc[get_champ_extract($element['CHAMP_PERE'])].$suffix_ann; 
				}
			}
			//////////////////////
	}
	elseif( $element['TYPE_OBJET'] == 'systeme_valeur_unique' and !isset($deja_aff_sys[$element['CHAMP_PERE']]) ){
			/////////// caracteristiques systeme déjà 
			$deja_aff_sys[$element['CHAMP_PERE']] = 1;
			///////////////// 
			if( $element['BOUTON_INTERFACE']=='popup' ){
					if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
						$html .= "\$".$element['CHAMP_INTERFACE']."_0".$suffix_ann;
					}
					else{
							$html .= "\$".$element['CHAMP_PERE']."_0".$suffix_ann;
					}
			}
			else{
						$html.= "\$".$element['CHAMP_PERE']."_0".$suffix_ann;
			}
	}
	return $html ;
}

function get_cell_matrice($ligne ,$code_dims, $element, $annee='', $langue, $id_systeme){
	//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
	${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
	//Fin ajout Hebie
	$htm 		= '';
	$suffix_ann = '';
	if($annee<>'') $suffix_ann = "_ANN_".$annee;
	if($element['TYPE_OBJET']=='booleen'){

			$htm.= $GLOBALS['libelle_oui'];
			$htm.= "\$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."_1".$suffix_ann;
			$htm.= "";
			$htm.= $GLOBALS['libelle_non'];
			$htm.= "\$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."_0".$suffix_ann;
			
	}
	elseif($element['TYPE_OBJET']=='checkbox'){
			
			$htm.= "\$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims.$suffix_ann;
			
	}
	elseif($element['TYPE_OBJET']=='text'){

			$htm.= "\$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims.$suffix_ann;
	}
	elseif($element['TYPE_OBJET']=='combo'){
				
			$result_nomenc 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']); 
			foreach($result_nomenc as $element_result_nomenc){
					//Un valeur du combo pour chaque valeur de la nomenclature 
					$htm 	.= "\$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."_".$element_result_nomenc[get_champ_extract($element['CHAMP_PERE'])].$suffix_ann;
					// Lecture des libellés de la table de nomenclature
			}

	}elseif($element['TYPE_OBJET']=='liste_radio'){
			//////////////////////
			$result_nomenc 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']); 
			
			$htm		.= "\t\t<table width='100%' valign='top'>\n";
			
			foreach($result_nomenc as $element_result_nomenc){
				$htm		.= "\t\t\t<tr>\n";
				$htm		.= "\t\t\t\t<td nowrap='nowrap' align='center'>".$element_result_nomenc['LIBELLE']."</td>\n";
				$htm		.= "\t\t\t\t<td align='center'><b>\$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."_".$element_result_nomenc[get_champ_extract($element['CHAMP_PERE'])].$suffix_ann."</b></td>\n"; 
				$htm		.= "\t\t\t</tr>\n";
			}
			 
			$htm		.= "\t\t</table>\n";

	}else{
		$htm 		.= "\t\t<b> --- </b>\n";
	}
	return $htm ;
}

function recherche_libelle($code,$langue,$table){
	// permet de récupérer le libellé dans la table de traduction
	// en fonction de la langue et de la table  aussi
	
			// Positionnement de la connexion 
			// Modif pour externalisation de DICO
			if ( preg_match('/'.preg_quote('^'.$GLOBALS['PARAM']['TYPE'].'_.*$', '/').'/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
				$conn                 =   $GLOBALS['conn'];
			} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
				$conn                 =   $GLOBALS['conn_dico']; 
			} 			
			$requete 	= "SELECT LIBELLE
							FROM DICO_TRADUCTION 
							WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
							AND NOM_TABLE='".$table."'";
	
	// Traitement Erreur Cas : GetAll / GetRow
	try {
			$all_res	= $conn->GetAll($requete);
			if(!is_array($all_res)){                    
					throw new Exception('ERR_SQL');  
			} 
			return($all_res[0]['LIBELLE']);									
	}
	catch(Exception $e){
			$erreur = new erreur_manager($e,$requete);
	}
	// Fin Traitement Erreur Cas : GetAll / GetRow
}

function get_chaines_loc($id_systeme){
	
	$tab_chaines_loc = array();
	$all_chaines_loc = array();
	$requete        = 'SELECT CODE_TYPE_CHAINE FROM DICO_CHAINE_LOCALISATION
										 WHERE CODE_TYPE_SYSTEME_ENSEIGNEMENT='.$id_systeme.' 
										 ORDER BY ORDRE_TYPE_CHAINE';
											
	// Traitement Erreur Cas : GetAll / GetRow
	try {
			$all_chaines_loc  = $GLOBALS['conn_dico']->GetAll($requete);
			if(!is_array($all_chaines_loc)){                    
					throw new Exception('ERR_SQL');  
			} 
	}
	catch(Exception $e){
			$erreur = new erreur_manager($e,$requete);
	}
	// Fin Traitement Erreur Cas : GetAll / GetRow

	if( count($all_chaines_loc) == 0 ){
			$tab_chaines_loc[] = array( 'numCh' => 2, 'nbRegs' => 4 );
	}else{
			foreach( $all_chaines_loc as $i => $tab ){
					$req_niv  	=  'SELECT    COUNT(*) 
													FROM      '.$GLOBALS['PARAM']['HIERARCHIE'].'
													WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$tab['CODE_TYPE_CHAINE'];
					
					//echo $req_niv;
					
					// Traitement Erreur Cas : Execute / GetOne
					try {            
							$niv_chaine = 	$this->conn->getOne($req_niv);
							if($niv_chaine ===false){                
									 throw new Exception('ERR_SQL');   
							}
							$tab_chaines_loc[] = array( 'numCh' => $tab['CODE_TYPE_CHAINE'], 'nbRegs' => $niv_chaine );								 
					}
					catch (Exception $e) {
							 $erreur = new erreur_manager($e,$req_niv);
					}        
					// Fin Traitement Erreur Cas : Execute / GetOne
			}
	}
	return $tab_chaines_loc;
}
/*
function requete_nomenclature_bool($champ_pere, $langue){
	$req_bool       = 'SELECT DICO_TRADUCTION.LIBELLE, DICO_BOOLEEN.CODE_DICO_BOOLEEN AS '.$champ_pere.'
						FROM DICO_BOOLEEN ,  DICO_TRADUCTION
						WHERE DICO_BOOLEEN.CODE_DICO_BOOLEEN = DICO_TRADUCTION.CODE_NOMENCLATURE 
						AND DICO_TRADUCTION.NOM_TABLE=\'DICO_BOOLEEN\' 
						AND DICO_TRADUCTION.CODE_LANGUE=\''.$langue.'\'
						ORDER BY DICO_BOOLEEN.ORDRE_DICO_BOOLEEN';  
	try {
			$all_res	= $GLOBALS['conn_dico']->GetAll($req_bool);
			if(!is_array($all_res)){                    
					throw new Exception('ERR_SQL');  
			} 
			return $all_res;									
	}
	catch(Exception $e){
			$erreur = new erreur_manager($e,$req_bool);
	}
			// Fin Traitement Erreur Cas : GetAll / GetRow
}
*/
function recherche_libelle_zone($id_zone,$langue){
	//Recherche du LIBELLE dans la table TRADUCTION
	$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
						WHERE CODE_NOMENCLATURE=".$id_zone." 
						AND NOM_TABLE='DICO_ZONE'
						AND CODE_LANGUE='".$langue."';";
	// Traitement Erreur Cas : GetAll / GetRow
	try {
			$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
			if(!is_array($aresult)){                    
					throw new Exception('ERR_SQL');  
			} 
			return $aresult[0]['LIBELLE'];									
	}
	catch(Exception $e){
			$erreur = new erreur_manager($e,$requete);
	}
	// Fin Traitement Erreur Cas : GetAll / GetRow
}

function get_dims_zone_matricielle($type_zone, $id_tabm, $dico){
	foreach($dico as $element){
		if( ($element['TYPE_OBJET'] == $type_zone) && ($element['ID_TABLE_MERE_THEME'] == $id_tabm) ){
			return($element);
		}
	}
}

function get_tabms_matricielles($dico){
	$tab_tabms_mat = array();
	foreach($dico as $element){
		if( ($element['TYPE_OBJET'] == 'dimension_ligne') || ($element['TYPE_OBJET'] == 'dimension_colonne') ){
			if(!in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tab_tabms_mat)){
				$tab_tabms_mat[] = $element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME'];
			}
		}
	}
	return($tab_tabms_mat);
}

function generer_table_grille($id_theme, $langue ,$id_systeme){
	//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
	${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
	//Fin ajout Hebie
	
	$requete	="		SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
						FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
						WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
						AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
						AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
						AND			DICO_ZONE.ID_THEME = ".$id_theme.
						" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
						" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
						" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
							AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
							AND  	DICO_ZONE.ID_ZONE IN (".implode(', ',$_SESSION['list_chps_export']).") 
							ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
	//echo $requete;
	try {
		$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
		if(!is_array($aresult)){                    
			throw new Exception('ERR_SQL');  
		} 
		$dico	= $aresult;									
	}
	catch(Exception $e){
			$erreur = new erreur_manager($e,$requete);
	}
									
	// Les Locaux par exemple
	$NB_TOTAL_COL 	= 0;
	$NB_LIGNE_ECRAN	= $_SESSION['nbre_total_enr'];//$dico[0]['NB_LIGNES_FRAME'];//$dico[0]['NB_LIGNES_FRAME']; //Nombre de lignes à afficher
	
	$tab_elem_grp 				= array();
	
	$nom_grp_deja_aff 			= array();
	$html_fonc_TotMat_in_Gril 	= '';
	$affiche_totaux_mat_in_Gril	= array();
	$colspan_Total_2Dim			= array();
	$tabms_matricielles 		= get_tabms_matricielles($dico);//A revoir
	$req_lib        = "SELECT LIBELLE
						FROM DICO_TRADUCTION 
						WHERE CODE_NOMENCLATURE=".$id_theme.$id_systeme." AND CODE_LANGUE='".$langue."'
						AND NOM_TABLE='DICO_THEME_LIB_LONG';";
	$res_lib		= $GLOBALS['conn_dico']->GetAll($req_lib);
	$libelle_long   = $res_lib[0]['LIBELLE'];									
	$html 			= "<TABLE class='table-questionnaire' border='1'>"."\n\t";
	$html           .= '<CAPTION><B>' . $libelle_long . '</B></CAPTION><TR>'."\n\t\t";//A revoir

	// Calcul du nombre de lignes de libellés
	$nb_niv_1             = 1;
	$nb_niv_2             = 1;
	foreach($dico as $i_elem => $element){
		if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
			if($nb_niv_1 < 3){
				$nb_niv_1	= 3;
				$nb_niv_2   = 2;
			}
			if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
				if( trim($element['NOM_GROUPE'] == '') ){
					$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
					$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
				}else{
					$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
					$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
				}
				$dico[$i_elem]['OBJET_MATRICIEL']	= true;
				$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
				
				if( $element['AFFICHE_TOTAL'] && ($element['TYPE_OBJET'] == 'text') && ($element['TYPE_ZONE_BASE'] == 'int')){
					$aff_total_vertic = true ;
				}
			}
		}elseif ($element['TYPE_OBJET'] == 'liste_radio' or ($element['TYPE_OBJET'] == 'booleen') 
			or ($element['TYPE_OBJET']=='liste_checkbox') or ($element['TYPE_OBJET']=='text_valeur_multiple') ){
			// S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
			if($nb_niv_1 < 2) $nb_niv_1	= 2;
			//break;
		}
	}
	
	if( is_array($tabms_matricielles) && count($tabms_matricielles) ){
		$mat_dim_colonne 		=	array();
		$mat_liste_mes 			= 	array();
		$tab_libelles_mesures 	= 	array();
		$tab_types_mesures 		= 	array();
		$attrib_obj_mesures 	= 	array();
		$elem_obj_mesures 		= 	array();
		$mat_dim_colonne		= 	array();
		$mat_codes_ligne		= 	array();
		$mat_codes_colonne		= 	array();
		$colspan_cell_tot		= 	array();
		
		$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
							WHERE CODE_NOMENCLATURE=1 
							AND NOM_TABLE='DICO_BOOLEEN'
							AND CODE_LANGUE='".$langue."';";
		try {
				$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
				if(!is_array($aresult)){                    
						throw new Exception('ERR_SQL');  
				} 
				$GLOBALS['libelle_oui'] = $aresult[0]['LIBELLE'];									
		}
		catch(Exception $e){
				$erreur = new erreur_manager($e,$requete);
		}
			
		$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
							WHERE CODE_NOMENCLATURE=0 
							AND NOM_TABLE='DICO_BOOLEEN'
							AND CODE_LANGUE='".$langue."';";
		try {
				$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
				if(!is_array($aresult)){                    
						throw new Exception('ERR_SQL');  
				} 
				$GLOBALS['libelle_non'] = $aresult[0]['LIBELLE'];									
		}
		catch(Exception $e){
				$erreur = new erreur_manager($e,$requete);
		}
	}
	
	$plus_last_tr_tot 	= 0 ;
	$last_tr_tot = '';

	// Affichage les entetes
	foreach($dico as $i_elem => $element){
		if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){ // si type dimension on ignore
			// Affichage de la colonne vide entre chaque éléments de la grille
			// Pour chacun des éléments du tableau
			// imprimer l'éventuel libellé d'entête
			// et prévoir les colspan pour cadrer avec la seconde ligne de libellés (horizontaux)
			if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
				//récupération des
				
				if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
					$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
					$grp_mat = $element['NOM_GROUPE'];
					//$html	.=	"\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1+$plus_last_tr_tot)."></TD>\n";
					
					$mat_dim_colonne[$grp_mat] 	=	get_dims_zone_matricielle('dimension_ligne', $element['ID_TABLE_MERE_THEME'], $dico);//A revoir
					if(!(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat]))){
						$mat_dim_colonne[$grp_mat]	=	get_dims_zone_matricielle('dimension_colonne', $element['ID_TABLE_MERE_THEME'], $dico);//A revoir
					}
					$libelle_mat = recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue);//A revoir
					
					if(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat])){
						$mat_nomenc_colonne	= requete_nomenclature($mat_dim_colonne[$grp_mat]['TABLE_FILLE'], $mat_dim_colonne[$grp_mat]['CHAMP_FILS'], $mat_dim_colonne[$grp_mat]['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne[$grp_mat]['SQL_REQ']) ;
						foreach($mat_nomenc_colonne as $mat_i => $mat_val){
							$mat_codes_colonne[$grp_mat][] = array('code' => $mat_val[get_champ_extract($mat_dim_colonne[$grp_mat]['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
						}
					}
					if( count($mat_codes_ligne[$grp_mat]) || count($mat_codes_colonne[$grp_mat]) ){
						$mat_liste_mes[$grp_mat] 			= array();
						$tab_libelles_mesures[$grp_mat] 	= array();
						$tab_lib_mes_tot[$grp_mat] 			= array();
						$tab_types_mesures[$grp_mat] 		= array();
						$attrib_obj_mesures[$grp_mat] 		= array();
						$elem_obj_mesures[$grp_mat] 		= array();
						$nb_cell_cols_tot = 0 ;
						
						foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
							if(recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
								$tab_libelles_mesures[$grp_mat][$mat_i]	= 	recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
							}
							$mat_liste_mes[$grp_mat][$mat_i]		= $mat_val['CHAMP_PERE'];
							$tab_types_mesures[$grp_mat][$mat_i] 	= $mat_val['TYPE_OBJET'];
							$attrib_obj_mesures[$grp_mat][$mat_i] 	= $mat_val['ATTRIB_OBJET'];
							
							$elem_obj_mesures[$grp_mat][$mat_i] 	= $mat_val ;
							
							if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
								$affiche_totaux_mat_in_Gril[$grp_mat][$mat_i] 	= true;
								$tab_lib_mes_tot[$grp_mat][$mat_i]			=	$tab_libelles_mesures[$grp_mat][$mat_i] ;
							}
						}
						$nb_mesures[$grp_mat] =  count($mat_liste_mes[$grp_mat]);
						$colspan_cell_tot[$grp_mat] = 0 ;
						
						if( count($affiche_totaux_mat_in_Gril[$grp_mat]) ){
							$colspan_cell_tot[$grp_mat] = '' ;
							$nb_cell_cols_tot = 1 ;
							if( count($affiche_totaux_mat_in_Gril[$grp_mat]) > 1 ){
								$colspan_cell_tot[$grp_mat] = ' colspan='.( count($affiche_totaux_mat_in_Gril[$grp_mat]) + 1);
								$nb_cell_cols_tot =  count($affiche_totaux_mat_in_Gril[$grp_mat]) + 1 ;
							}
						}
						
						$colspan_entete_mat = $nb_cell_cols_tot + ( $nb_mesures[$grp_mat] * count($mat_codes_colonne[$grp_mat]) ) ;
						
					}			
					// libelle
					
					$html 	.= 	"\t\t<TD align='center'";
					$html 	.=	" COLSPAN=" . $colspan_entete_mat ;
					$html 	.= 	" ><b>";
					$html   .= 	$libelle_mat ;
					$html   .= 	"</b></TD>\n";
				}
				//die('ici');
			}elseif ( ($element['TYPE_OBJET'] == 'liste_radio') or ($element['TYPE_OBJET'] == 'booleen') 
					or ($element['TYPE_OBJET']=='liste_checkbox') or ($element['TYPE_OBJET']=='text_valeur_multiple')){
					
					//$html .="\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1+$plus_last_tr_tot)."></TD>\n";
					$html 			        .= "\t\t<TD align='center' ";
				
				// Lecture des libellés de la table de nomenclature
				if ( $element['TYPE_OBJET'] == 'booleen' ) {               
					$result_nomenc 		= requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
				}elseif ( $element['TYPE_OBJET'] == 'text_valeur_multiple' ) { 
					$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
					$result_nomenc 		= requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
				}else{
					$result_nomenc 		= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
				}
				
				$html 	.= " COLSPAN=" . count($result_nomenc);
				$html 	.= " ><b>";
				$html   .= recherche_libelle_zone($element['ID_ZONE'],$langue);
				$html   .= "</b></TD>\n";
			}elseif($element['TYPE_OBJET'] == 'text' or $element['TYPE_OBJET'] == 'systeme_text' or $element['TYPE_OBJET'] == 'combo' or $element['TYPE_OBJET'] == 'systeme_combo' or $element['TYPE_OBJET'] == 'label' or $element['TYPE_OBJET'] == 'checkbox') {              

				//$html .="\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1+$plus_last_tr_tot)."></TD>\n";
				$html 		.= "\t\t<TD align='center'";
				$html       .= " ROWSPAN=".$nb_niv_1;
				$html 		.= " ><b>";
				//A revoir, libellés verticaux
				$html   .=   recherche_libelle_zone($element['ID_ZONE'],$langue);
				$html               .= 	"</b></TD>\n";
			}
		}
	}
	$html .= "\t</TR>\n";
	
	$nom_grp_deja_aff 		= array();
	if($nb_niv_1 > 1){
		$html .= "\t<TR>\n";
		$niv_3_entete = false ;
		foreach($dico as $i_elem => $element){
			//Pour chacun des éléments du tableau (de type liste_radio)
			//imprimer les libellé des nomenclatures (verticaux)
			$cpt++;
			if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
				if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
					$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
					$grp_mat = $element['NOM_GROUPE'];
					if( $nb_mesures[$grp_mat] > 1 ){
						if(count($mat_codes_colonne[$grp_mat])){
							foreach ($mat_codes_colonne[$grp_mat] as $col => $elem_col){
								$html 		.="\t\t"."<TD align='center' COLSPAN=". $nb_mesures[$grp_mat] ."><b>".$elem_col['libelle']."</b></TD>\n";
							}
						}
						$niv_3_entete = true ;
						$rowspan_tot = '';
					}else{
						if(count($mat_codes_colonne[$grp_mat])){
							foreach ($mat_codes_colonne[$grp_mat] as $col => $elem_col){
								$libelle_afficher   =   $elem_col['libelle'];//A revoir, libellés verticaux
								$html 		.=	"\t\t"."<TD ROWSPAN=".$nb_niv_2." nowrap  align='center'><b>".$libelle_afficher."</b></TD>\n";
							}
						}
						$rowspan_tot = ' ROWSPAN='.$nb_niv_2;
					}
				}
			}elseif( ($element['TYPE_OBJET'] == 'liste_radio') or ($element['TYPE_OBJET'] == 'booleen') or ($element['TYPE_OBJET']=='liste_checkbox') or ($element['TYPE_OBJET']=='text_valeur_multiple')){
				// Lecture des libellés de la table de nomenclature	
				if($element['TYPE_OBJET']=='booleen'){
					$result_nomenc 	= requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
				}elseif ( $element['TYPE_OBJET'] == 'text_valeur_multiple' ) { 
					$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
					$result_nomenc 		= requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
				}else{
					$result_nomenc 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
				}
				//print_r($result_nomenc);
				//print '<BR><BR>';

				foreach($result_nomenc as $element_result_nomenc){		
					//Pour chaque libellé de la nomenclature
					$libelle_afficher   =   $element_result_nomenc['LIBELLE'];
					$html 		.="\t\t"."<TD ROWSPAN=".$nb_niv_2." nowrap  align='center'><b>".$libelle_afficher."</b></TD>\n";
				}
			}
		}
		$html .= "\t</TR>\n";
	}
	// Fin affichage des entetes
	
	$nom_grp_deja_aff 		= array();
	if( isset($niv_3_entete) && ($niv_3_entete == true)){

		$html .= "\t<TR>\n";
		foreach($dico as $i_elem => $element){
			if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
				if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
					$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
					$grp_mat = $element['NOM_GROUPE'];
					if( $nb_mesures[$grp_mat] > 1 ){
						foreach($mat_codes_colonne[$grp_mat] as $col => $elem_col){
							foreach($mat_liste_mes[$grp_mat] as $i_mes => $mes){
								if(!$element['AFFICH_VERTIC_MES'])
									$html		.="\t\t<TD align='center'  nowrap='nowrap'><b>".$tab_libelles_mesures[$grp_mat][$i_mes]."</b></TD>\n";
								else
									$html		.="\t\t<TD align='center'  nowrap='nowrap'></TD>\n";
							}
						}
					}
				}
			}
		}
		$html .= "\t</TR>\n";
	}elseif($nb_niv_1 > 2){
		$html .= "\t<TR></TR>\n";
	}
	
	// Affichage des zones de saisie
	// Autant de fois que lignes à affcher (nbre défini dans DICO_THEME.NB_LIGNES)
	for ($j = 0; $j < $NB_LIGNE_ECRAN; $j++){

		$nom_grp_deja_aff 		= array();
		$html .= "\t<TR>\n";

		// Pour chaque Ligne
		$i_TD = 0;
		$i_TD++;
		foreach($dico as $element){
			//$i_TD++;
			// Pour chacun des éléments du tableau
			// Lecture des libellés de la table de nomenclature
			// Modif Alassane
			if (strlen($element['CHAMP_PERE'])>30){
				if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql'){
					$taille_max_car =   strlen($nom_champ);
				}else if ($GLOBALS['conn']->databaseType == 'postgres9') {
					$taille_max_car=strlen($element['CHAMP_PERE']);
				}else{
					$taille_max_car =   31;
				}
			}else{
				 $taille_max_car = strlen($element['CHAMP_PERE']);
			}
			// Fin Modif Alassane
		   
		   // Ajout d'une condition de test Alassane
			if ( trim($element['TABLE_FILLE'])<>'' and trim($element['CHAMP_FILS'])<>'' ) {
				$result_nomenc 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
			}
			elseif (trim($element['TYPE_OBJET']) == 'booleen') {
				$result_nomenc 	= requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
			}
			if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
				if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
					$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
					$grp_mat = $element['NOM_GROUPE'];
					foreach($mat_codes_colonne[$grp_mat] as $col => $elem_col){
						foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){					
						  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
							$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
							$html		.= $get_cell_matrice($j , $elem_col['code'], $elem_obj_mesures[$grp_mat][$i_mes], '', $langue, $id_systeme);
							$html		.= "</b></TD>\n";
							$i_TD++ ;
						}
					}
				}
			}
			else{
				switch ($element['TYPE_OBJET']){
					case 'liste_radio':
					case 'booleen':
						foreach($result_nomenc as $element_result_nomenc){
							//Un bouton radio pour chaque valeur de la nomenclature
							$html       .= "\t\t<TD align='center'><b>\$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[get_champ_extract($element['CHAMP_PERE'])]."</b></TD>\n";
							$i_TD++;
							$html.= "";
						}
						break;
					case 'liste_checkbox':
						foreach($result_nomenc as $element_result_nomenc){
							//Un bouton radio pour chaque valeur de la nomenclature
							$html       .= "\t\t<TD align='center'><b>\$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[get_champ_extract($element['CHAMP_PERE'])]."</b></TD>\n";
							$i_TD++;
							$html.= "";
						}
						break;
					case 'text_valeur_multiple':{
						$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
						$result_nomenc 		= requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
						foreach($result_nomenc as $element_result_nomenc){
							//Un bouton radio pour chaque valeur de la nomenclature
							$html       .= "\t\t<TD align='center'><b>\$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[get_champ_extract($zone_ref['CHAMP_FILS'])]."</b></TD>\n";
							$i_TD++;
							$html.= "";
						}
						break;
					}
					case 'combo':
					case 'systeme_combo':
													
						$html		.= "\t\t<TD align='center' nowrap='nowrap'><b>\n";
						////
						$dynamic_content_object = false ;
						if( (trim($element['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($element['CHAMP_INTERFACE']) <> '') && (trim($element['TABLE_INTERFACE']) <> '')){
							//if( ereg('\$code_annee', $element['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $element['REQUETE_CHAMP_INTERFACE']) ){
								$dynamic_content_object = true ;
							//}
						}
						if($dynamic_content_object == true){
							$html 	.= '<!--DynContentObj_'.$element['ID_ZONE'].'_'.$element['CHAMP_PERE'].'_'.$j.'-->'."\n"; 
						}else{
							foreach($result_nomenc as $element_result_nomenc){
								//Un valeur du combo pour chaque valeur de la nomenclature 
								$html 	.= "\$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[get_champ_extract($element['CHAMP_PERE'])]; 
								$html.= "";
							}
						}
						$html 		.= "</b></TD>\n";
						$i_TD++;
						break;
				case 'text':
				case 'systeme_text':
					$html 	        .= "\t\t<TD align='center'>\$".$element['CHAMP_PERE']."_".$j."</TD>\n";
					$i_TD++;
					break;
				case 'checkbox':
					$html 	        .= "\t\t<TD align='center'><b>\$".$element['CHAMP_PERE']."_".$j."</b></TD>\n";
					$i_TD++;
					break;
				case 'label':
					$html 	        .= "\t\t<TD><b>";
					$html 	        .= "\$".$element['CHAMP_PERE']."_".$j; 
					$html 	        .= "</b></TD>\n";
					$i_TD++;
					break;
				}// Fin switch
			} // fin else
		}
		$html .= "\t</TR>\n";
		// Fin de parcours du Dico pour affichage des zones de saisie			
	}
	$html		.= "</TABLE>";
	return $html;
}

function generer_table_formulaire($id_theme, $langue ,$id_systeme){
	$requete				="
					SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
					FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
					WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
					AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
					AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
					AND			DICO_ZONE.ID_THEME = " . $id_theme . 
					" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = " . $id_systeme .
					" AND		DICO_THEME_SYSTEME.ID_SYSTEME = " . $id_systeme .
					" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
					AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
					ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
	// Traitement Erreur Cas : GetAll / GetRow
	try {
			$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
			if(!is_array($aresult)){                    
					throw new Exception('ERR_SQL');  
			} 
			$dico	= $aresult;
	}
	catch(Exception $e){
			$erreur = new erreur_manager($e,$requete);
	}
			// Fin Traitement Erreur Cas : GetAll / GetRow
	
	$req_lib        = "SELECT LIBELLE
						FROM DICO_TRADUCTION 
						WHERE CODE_NOMENCLATURE=".$id_theme.$id_systeme." AND CODE_LANGUE='".$langue."'
						AND NOM_TABLE='DICO_THEME_LIB_LONG';";
	$res_lib		= $GLOBALS['conn_dico']->GetAll($req_lib);
	$libelle_long   = $res_lib[0]['LIBELLE'];									
	
	$html 			.= "<TABLE border='1'>\n". '<CAPTION><B>' . $libelle_long . '</B></CAPTION>'."\n";
			
	//Recherche du LIBELLE BOOLEEN ex: 1) oui
	
	$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
						WHERE CODE_NOMENCLATURE=1 
						AND NOM_TABLE='DICO_BOOLEEN'
						AND CODE_LANGUE='".$langue."';";
			// Traitement Erreur Cas : GetAll / GetRow
			try {
					$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
					if(!is_array($aresult)){                    
							throw new Exception('ERR_SQL');  
					} 
					$GLOBALS['libelle_oui'] = $aresult[0]['LIBELLE'];									
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}
			// Fin Traitement Erreur Cas : GetAll / GetRow
			
	//echo $requete.'<br>';
	
	//Recherche du LIBELLE BOOLEEN ex: 2) non
	$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
						WHERE CODE_NOMENCLATURE=0 
						AND NOM_TABLE='DICO_BOOLEEN'
						AND CODE_LANGUE='".$langue."';";
			// Traitement Erreur Cas : GetAll / GetRow
			try {
					$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
					if(!is_array($aresult)){                    
							throw new Exception('ERR_SQL');  
					} 
					$GLOBALS['libelle_non'] = $aresult[0]['LIBELLE'];									
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}
			// Fin Traitement Erreur Cas : GetAll / GetRow

	$tab_elem_grp 			= array();
	$nom_grp_deja_aff 		= array();
	$html_fonc_TotMatFrml 	= '';
	$html_fonc_TotChpsFrml	= '';

	$tabms_matricielles = get_tabms_matricielles($dico);
	
	foreach($dico as $i_elem => $element){
		// Mettre un nom de groupe par defaut s'il en existe pas pour le type matriciel
		if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
			
			if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
				if( trim($element['NOM_GROUPE']) == '' ){
					$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
					$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
				}else{
					$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
					$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
				}
				$dico[$i_elem]['OBJET_MATRICIEL']	= true;
				$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
			}
		}
		elseif( ($element['NOM_GROUPE'] <> '') and ($element['TYPE_OBJET'] <> 'liste_radio') and ($element['TYPE_OBJET'] <> 'liste_checkbox') ){
			$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
			$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
			$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
		}
	}//Fin de parcours du dico
			$pass_ligne =-1;
			
				foreach($dico as $element){
					if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){ // si type dimension on ignore
						if($element['CHAMP_PERE']	==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){
							//
						}
						elseif( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) && (!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)) ){
								
								$affiche_vertic_mes	=	$element['AFFICH_VERTIC_MES'];
								$affiche_sous_totaux	=	$element['AFFICHE_SOUS_TOTAUX'];
								$nom_grp_deja_aff[]			=	$element['NOM_GROUPE'];
								$affiche_totaux_mat_Frml 	= 	array();
								
								$mat_dim_ligne 		=	get_dims_zone_matricielle('dimension_ligne', $element['ID_TABLE_MERE_THEME'], $dico);
								$mat_dim_colonne	=	get_dims_zone_matricielle('dimension_colonne', $element['ID_TABLE_MERE_THEME'], $dico);
								
								$mat_codes_ligne	= array();
								if(is_array($mat_dim_ligne) && count($mat_dim_ligne)){
									
									//Ajout hebie pour gerer une table de donnees comme table fille (nomenclature) pour generation de theme pendant la saisie
									if ($mat_dim_ligne['TABLE_FILLE']=='') $mat_dim_ligne['TABLE_FILLE']=$mat_dim_ligne['TABLE_INTERFACE'];
									if ($mat_dim_ligne['CHAMP_FILS']=='') $mat_dim_ligne['CHAMP_FILS']=$mat_dim_ligne['CHAMP_INTERFACE'];
									if ($mat_dim_ligne['SQL_REQ']=='') $mat_dim_ligne['SQL_REQ']=$mat_dim_ligne['REQUETE_CHAMP_INTERFACE'];
									//Fin modif
									
									$mat_nomenc_ligne	= requete_nomenclature($mat_dim_ligne['TABLE_FILLE'], $mat_dim_ligne['CHAMP_FILS'], $mat_dim_ligne['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_ligne['SQL_REQ'], $code_annee, $code_etablissement) ;
									if(is_array($mat_nomenc_ligne)){
										foreach($mat_nomenc_ligne as $mat_i => $mat_val){
											$mat_codes_ligne[] = array('code' => $mat_val[get_champ_extract($mat_dim_ligne['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE']);
										}
									}
								}
								
								$mat_codes_colonne	= array();
								if(is_array($mat_dim_colonne) && count($mat_dim_colonne)){
									
									//Ajout hebie pour gerer une table de donnees comme table fille (nomenclature) pour generation de theme pendant la saisie
									if ($mat_dim_colonne['TABLE_FILLE']=='') $mat_dim_colonne['TABLE_FILLE']=$mat_dim_colonne['TABLE_INTERFACE'];
									if ($mat_dim_colonne['CHAMP_FILS']=='') $mat_dim_colonne['CHAMP_FILS']=$mat_dim_colonne['CHAMP_INTERFACE'];
									if ($mat_dim_colonne['SQL_REQ']=='') $mat_dim_colonne['SQL_REQ']=$mat_dim_colonne['REQUETE_CHAMP_INTERFACE'];
									//Fin modif
									$mat_nomenc_colonne	= requete_nomenclature($mat_dim_colonne['TABLE_FILLE'], $mat_dim_colonne['CHAMP_FILS'], $mat_dim_colonne['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne['SQL_REQ'], $code_annee, $code_etablissement) ;
									if(is_array($mat_nomenc_colonne)){
										foreach($mat_nomenc_colonne as $mat_i => $mat_val){
											$mat_codes_colonne[] = array('code' => $mat_val[get_champ_extract($mat_dim_colonne['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
										}
									}
								}
								
								$mat_liste_mes 			= array();
								$tab_libelles_mesures 	= array();
								$tab_types_mesures 		= array();
								$attrib_obj_mesures 	= array();
								$vertic_aff_mesures		= array();
								$elem_obj_mesures 		= array();
								$tab_lib_mes_tot 		= array();
								$types_mes_matr_att 	= array('booleen', 'checkbox', 'text', 'combo', 'liste_radio');
								$tab_index_mes_aff_tot	= array();
								$labels_tabl_matr		= '';
								$vertic_for_all_mes		= true;
								
								foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
									if($mat_val['TYPE_OBJET'] == 'label'){
										$labels_tabl_matr .= '<b>'.recherche_libelle_zone($mat_val['ID_ZONE'], $langue) . '</b><br>';
									}else{
										if(recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
											$tab_libelles_mesures[$mat_i]	= 	recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
										}
										$mat_liste_mes[$mat_i]		= $mat_val['CHAMP_PERE'];
										$tab_types_mesures[$mat_i] 	= $mat_val['TYPE_OBJET'];
										$attrib_obj_mesures[$mat_i] = $mat_val['ATTRIB_OBJET'];
										$vertic_aff_mesures[$mat_i] = $mat_val['TAILLE_LIB_VERTICAUX'];
										
										$elem_obj_mesures[$mat_i] 	= $mat_val ;
										if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
											$affiche_totaux_mat_Frml[$mat_i] 	= true;
											$tab_lib_mes_tot[$mat_i]	=	$tab_libelles_mesures[$mat_i] ;
											$tab_index_mes_aff_tot[] = $mat_i ;
										}
									}
								}
								$nb_mesures =  count($mat_liste_mes);
								
								$html.= "\t<TR>\n";
								$html.= "\t\t<TD colspan=4 valign='top'>&nbsp;";
								if(trim($labels_tabl_matr) <> ''){
									$html.= $labels_tabl_matr;
								}
								
								if( count($mat_codes_ligne) || count($mat_codes_colonne) ){
									//echo '<br> NOM_GROUPE = ' .$element['NOM_GROUPE'] ;
									//$affiche_totaux_mat_Frml 	= $element['AFFICHE_TOTAL'];
									
									$html.= "<TABLE border=1>";
									///////////////////////////////////////////////////////////////////////////////	
									/////Cas d'affichage horizontal des mesures //////////////////////////////////
									/////////////////////////////////////////////////////////////////////////////	
									if(!$affiche_vertic_mes	){
										if ($nb_mesures > 1) {
											if($affiche_sous_totaux)
												$colspan =' COLSPAN='.($nb_mesures+1).' ';
											else
												$colspan =' COLSPAN='.$nb_mesures.' ';
										}else{
											$colspan =' ';
										}
										if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
											$rowspan =' ROWSPAN=2 ';
										}else{
											$rowspan =' ';
										}
										$html 			.= "\n\t<TR>\n\t\t<TD $rowspan>&nbsp</TD>\n";	// Zone vide d'intersection des 2 dimensions
									
									if(count($mat_codes_colonne)){
										foreach ($mat_codes_colonne as $col => $elem_col){
											// On imprime le libellé des colonnes
											$html		.="\t\t<TD $colspan align='center'>";
											
											if( ($mat_dim_colonne['TAILLE_LIB_VERTICAUX'] > 0) && $nb_mesures == 1){
												//A revoir libellé affiché
												$libelle_afficher   =   $elem_col['libelle'];
												//$libelle_afficher	.=	'+++';
												$html               .= 	$libelle_afficher;
											}else{
												$html 		.= $elem_col['libelle'];
											}
											
											$html		.="</TD>\n";
										}
									}else{
										$html		.="\t\t<TD $colspan>";
										$html 		.= '&nbsp;';
										$html		.="</TD>\n";
									}
		
									$html 			.= "\n\t</TR>\n";
									
									if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
										$html 			.= "\n\t<TR>\n";	// Zone vide d'intersection des 2 dimensions
										if(count($mat_codes_colonne)){
											foreach ($mat_codes_colonne as $col => $elem_col){
												// On imprime le libellé des colonnes
												foreach($mat_liste_mes as $i_mes => $mes){
													$html		.="\t\t<TD  align='center'>";
													
													if( $vertic_aff_mesures[$i_mes] > 0){
														//A revoir libellé affiché
														$libelle_afficher   =   $tab_libelles_mesures[$i_mes];
														//$libelle_afficher	.=	'+++';
														$html               .= 	$libelle_afficher;
														
													}else{
														$html		.=$tab_libelles_mesures[$i_mes];
														
													}
													$html		.="</TD>\n";
												}
											}
										}else{
											foreach($mat_liste_mes as $i_mes => $mes){
												$html		.="\t\t<TD  align='center'><b>".$tab_libelles_mesures[$i_mes]."</b></TD>\n";
											}
										}
										$html 			.= "\n\t</TR>\n";
									}
									
									// traitements des lignes 
									if(count($mat_codes_ligne)){
										foreach($mat_codes_ligne as $li => $elem_li){
											$Ligne = $li;
								
											$html 		        .= "\n\t<TR>\n";

													  // On imprime l'entête de ligne
											$html 		.= "\n\t\t<TD align='center'>".$elem_li['libelle']."</TD>\n";
											if(count($mat_codes_colonne)){
												foreach($mat_codes_colonne as $col => $elem_col){
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
													   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
														$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
														
														$html		.= get_cell_matrice(0 ,$elem_li['code'].'_'.$elem_col['code'], $elem_obj_mesures[$i_mes], '', $langue, $id_systeme);
														
														$html		.= "</b></TD>\n";
													}
												}
											}
											else{ // Pas de dimension Colonne

												foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
												   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
														$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
														
														$html		.= get_cell_matrice(0 ,$elem_li['code'], $elem_obj_mesures[$i_mes], '', $langue, $id_systeme);
														
														$html		.= "</b></TD>\n";
												}
																								
											}
											$html 		        .= "\n\t</TR>\n";
										}// Fin parcours des lignes
									} // fin si exist  dimension Ligne
									else{ // pas de dimensions lignes

											$html 		        .= "\n\t<TR>\n";
											
											$rowspan_chp_tot = '';
											$br_chp_tot = '';
											
											$html 		.= "\n\t\t<TD>&nbsp;</TD>\n";
											if(count($mat_codes_colonne)){
												foreach($mat_codes_colonne as $col => $elem_col){
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
													  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
														$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
														
														$html		.= get_cell_matrice(0 , $elem_col['code'], $elem_obj_mesures[$i_mes], '', $langue, $id_systeme);
														
														$html		.= "</b></TD>\n";
													}
												}
											}
											$html 		        .= "\n\t</TR>\n";
									}

									$html.= "</TABLE>";
									///////////////////////////////////////////////////////////////////////////////	
									/////Cas d'affichage vertical des mesures ////////////////////////////////////
									/////////////////////////////////////////////////////////////////////////////	
									}else{
										if ($nb_mesures > 1) {
											if($affiche_sous_totaux){
												$rowspan =' ROWSPAN='.($nb_mesures + 2).' ';
											}else{
												$rowspan =' ROWSPAN='.($nb_mesures + 1).' ';
											}
										}else{
											$rowspan =' ';
										}
									
										if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
											$colspan =' COLSPAN=2 ';
											//$colspan =' ';
										}else{
											$colspan =' ';
										}
										
										$html 			.= "\n\t<TR>\n\t\t<TD $colspan>&nbsp</TD>\n";	// Zone vide d'intersection des 2 dimensions
										if(count($mat_codes_colonne)){
											foreach ($mat_codes_colonne as $col => $elem_col){
												// On imprime le libellé des colonnes
												$html		.="\t\t<TD align='center'>";
												$html 		.= $elem_col['libelle'];
												$html		.="</TD>\n";
											}
										}else{
											$html		.="\t\t<TD>";
											$html 		.= '&nbsp;';
											$html		.="</TD>\n";
										}
			
										$html 			.= "\n\t</TR>\n";
										
										// traitements des lignes 
										if(count($mat_codes_ligne)){
											foreach($mat_codes_ligne as $li => $elem_li){
												$Ligne = $li;
									
												$html 		        .= "\n\t<TR>\n";
	
														  // On imprime l'entête de ligne
												$html 		.= "\n\t\t<TD $rowspan valign='middle' align='center'>".$elem_li['libelle']."</TD>\n";
												$k=1;

												foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
													$html 			.= "\n\t<TR>\n";
													// On imprime le libellé des mesures
													$html		.="\t\t<TD  nowrap='nowrap' align='center' valign='middle'>".$tab_libelles_mesures[$i_mes]."</TD>\n";
																								
													if(count($mat_codes_colonne)){
														foreach($mat_codes_colonne as $col => $elem_col){
															//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
															   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";

																$html		.= get_cell_matrice(0 ,$elem_li['code'].'_'.$elem_col['code'], $elem_obj_mesures[$i_mes], '', $langue, $id_systeme);
																
																$html		.= "</b></TD>\n";
															//}
														}
													}
													else{ // Pas de dimension Colonne
		
														//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
														   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
																
																$html		.= get_cell_matrice(0 ,$elem_li['code'], $elem_obj_mesures[$i_mes], '', $langue, $id_systeme);
																
																$html		.= "</b></TD>\n";
														//}
													}
													$html 		    .= "\n\t</TR>\n";
												}
											}// Fin parcours des lignes
										} // fin si exist  dimension Ligne
										else{ // pas de dimensions lignes
	
												foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
													
													$html 			.= "\n\t<TR>\n";	
													// On imprime le libellé des mesures
													$html		.="\t\t<TD $colspan nowrap='nowrap' align='center' valign='middle'>".$tab_libelles_mesures[$i_mes]."</TD>\n";
													
													$rowspan_chp_tot = '';
													$br_chp_tot = '';
													//$html 		.= "\n\t\t<TD  CLASS='".$classe_fond."'>&nbsp;</TD>\n";
													
													if(count($mat_codes_colonne)){
														foreach($mat_codes_colonne as $col => $elem_col){
															//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
															  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
																
																$html		.= get_cell_matrice(0 , $elem_col['code'], $elem_obj_mesures[$i_mes], '', $langue, $id_systeme);
																
																$html		.= "</b></TD>\n";
															//}
														}
													}
													
													$html 		        .= "\n\t</TR>\n";
												}								
										}
										$html.= "</TABLE>";
									}
								}// fin si exist des dimensions								
								
								$html.= "</TD>\n";
								$html.= "\t</TR>\n";					
								
						}
						elseif( ($element['TYPE_OBJET']=='liste_radio' or $element['TYPE_OBJET']=='liste_checkbox' or $element['TYPE_OBJET']=='text_valeur_multiple') && !(isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
								$pass_ligne++;

								if($element['TYPE_OBJET']=='liste_radio'){
										$type_objet ='radio';
										$liste_choix 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
								}
								elseif($element['TYPE_OBJET']=='liste_checkbox'){
										$type_objet ='checkbox';
										$liste_choix 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
								}elseif($element['TYPE_OBJET']=='text_valeur_multiple'){
										$type_objet ='text';
										$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
										$liste_choix 	= requete_nomenclature($zone_ref['TABLE_FILLE'], $zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
									//echo '<pre>liste_choix <br>';
									//print_r($liste_choix );
								}
								
								//echo '<pre>liste_choix <br>';
								//print_r($liste_choix );
								$nb_choix		 	= count($liste_choix);
								$nb_tr 				= intval($nb_choix / 3) + 1;
								$reste_div 		= $nb_choix % 3;
								if( $reste_div > 0 ) $rowspan	=	$nb_tr;
								else $rowspan	=	$nb_tr - 1;
								//echo"<br>rowspan=$rowspan";
								$rowspan_tr = '';
								if($nb_tr > 1) $rowspan_tr = " rowspan='$rowspan' ";
								//else 
								$html.= "\t<TR>\n";
								$html.= "\t\t<TD $rowspan_tr valign='top'>&nbsp;";
								$html.= recherche_libelle_zone($element['ID_ZONE'],$langue);
								$html.= "&nbsp;</TD>\n";
								for( $tr=0; $tr<$rowspan; $tr++ ){
										if($tr > 0)
												$html.= "\t<TR>\n";
										for($j=0; $j<3; $j++){
												$ordre = 3 * $tr + $j ;
												if(isset($liste_choix[$ordre])){
														$html.= "\t\t<TD nowrap='nowrap'>&nbsp;<b>";
														//$html.= "<span class='td_left'>";
														$html.= $liste_choix[$ordre]['LIBELLE'];
														if($element['TYPE_OBJET']=='liste_radio'){
																$name				=	$element['CHAMP_PERE']."_0"; 
														}
														elseif($element['TYPE_OBJET']=='liste_checkbox' ){
																$name				=	$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($element['CHAMP_PERE'])];
														}elseif($element['TYPE_OBJET']=='text_valeur_multiple'){
																$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
																$name				=	$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($zone_ref['CHAMP_FILS'])];
														}
														

														$html.= "&nbsp;";
														//$html.= "VALUE=$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$element['CHAMP_PERE']].">";
														
														// Modif Alassane
														if (strlen($element['CHAMP_PERE'])>30){
															if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql'){
																$taille_max_car =   strlen($element['CHAMP_PERE']);                                                               
															}else if ($GLOBALS['conn']->databaseType == 'postgres9') {
					$taille_max_car=strlen($element['CHAMP_PERE']);
				}else{
																$taille_max_car =   31;
															}
														}else{
															 $taille_max_car = strlen($element['CHAMP_PERE']);
														}                                                        
														
														if($element['TYPE_OBJET']=='text_valeur_multiple'){
															$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
															$html.= " : \$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($zone_ref['CHAMP_FILS'])];
														}else{
															$html.= " : \$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($element['CHAMP_PERE'])];
														}
														// Fin Modif Alassane
														
														//$html.= "</span>";
														$html.= "&nbsp;</b></TD>\n";
												}
												else{
														if($j==1) $cols_rest = 2;
														else $cols_rest = 1;
														$html 		.="\t\t"."<TD COLSPAN='$cols_rest'>&nbsp;</TD>\n";
														break;
												}
										}
										$html.= "\t</TR>\n";	
								}// fin for : parcours des lignes 
						}
						elseif($element['TYPE_OBJET']=='loc_etab'){ /// LOCALISATION ETABLISSEMENT
								lit_libelles_page('/questionnaire.php');
								//print_r($this->conn);
								//echo'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
								$html.= "\t<TR>\n";
								$html.= "\t\t<TD colspan=4 valign='top'>&nbsp;";
								///  données à prendre de quelque part
								$tab_chaines = get_chaines_loc($id_systeme);
								//echo '<pre>';
								//print_r($tab_chaines);
								//$tab_chaines = array();
								//$tab_chaines[] = array( 'numCh' => 2, 'nbRegs' => 4 );
								/// 
								//die();
								$html.= "<table><tr>\n";
								// pour chaque chaine mettre dans un TD
								foreach($tab_chaines as $iLoc => $chaine){
										$requete        = 'SELECT   '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', 
																	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', 
																	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', 
																	'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
															FROM    '.$GLOBALS['PARAM']['HIERARCHIE'].' , '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
															WHERE	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
																	'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
																	AND	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$chaine['numCh'].'
															ORDER BY 		'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC';
										//echo $requete;
										//exit;
										$aresult = array();
										// Traitement Erreur Cas : GetAll / GetRow
										try {
												$aresult	= $GLOBALS['conn']->GetAll($requete);
												if(!is_array($aresult)){                    
														throw new Exception('ERR_SQL');  
												} 
																					
										}
										catch(Exception $e){
												$erreur = new erreur_manager($e,$requete);
										}
										// Fin Traitement Erreur Cas : GetAll / GetRow
										 
										//$nbTypeReg = count($aresult);
										$html.= "<td valign='top'><table>\n";
										$html.='<tr>'."\n";
										$html.='<td colspan="2" nowrap="nowrap">'.recherche_libelle($chaine['numCh'],$langue,'DICO_CHAINE_LOCALISATION').'&nbsp;&nbsp;&nbsp;</td>'."\n";
										$html.='</tr>'."\n";	
										
										$html.='<tr>'."\n";
										$html.='<td>&nbsp;</td>'."\n";
										$html.='<td nowrap="nowrap">'."\n";
									   
										$html.= "\$LOC_REG_".$iLoc;    
										$html.= "</td>\n"; 
										$html.= "</tr>\n";	
											
										foreach($aresult as $iTypeReg => $ligne_type_reg){
										$html.= "<tr>\n"; 
												$html.= "<td>".recherche_libelle($ligne_type_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']],$langue,$GLOBALS['PARAM']['TYPE_REGROUPEMENT'])."</td>\n";
												$html.= "<td>\$LIBELLE_REG_".$iLoc.'_'.$iTypeReg."</td>\n";
										$html.= "</tr>\n";												
										}
										$html.= "</table></td>\n";
								}
								$html.= "</tr></table>\n";
								$html.= "</TD>\n";
								$html.= "\t</TR>\n";
						}// FIN /// LOCALISATION ETABLISSEMENT

						elseif( ((trim($element['NOM_GROUPE']=='')) or (isset($tab_elem_grp[$element['NOM_GROUPE']]) and ( count($tab_elem_grp[$element['NOM_GROUPE']])==1 ) )) && (!isset($element['OBJET_MATRICIEL'])) ){
								// zone non groupée à afficher sur une ligne
								$pass_ligne++;
								
								$html.= "\t<TR>\n";
								
								if($element['TYPE_OBJET']=='label'){
										$html.= "\t\t<TD colspan='4'><b>&nbsp;";
										$html.= formulaire_concat_zone_html($element,$langue,$id_systeme);
										$html.= "&nbsp;</b></TD>\n";
								}
								else{
										$html.= "\t\t<TD colspan='4'>&nbsp;";
										$html.= recherche_libelle_zone($element['ID_ZONE'],$langue);
										$html.= "&nbsp;&nbsp;&nbsp;<b> : ";
										//$html.= "\t\t<TD colspan='3'>&nbsp;";
										$html.= formulaire_concat_zone_html($element,$langue,$id_systeme);
										$html.= "&nbsp;</b></TD>\n";
										//$html.= "\t\t<TD colspan='2'>&nbsp;</TD>\n";
								}
								

								
								$html.= "\t</TR>\n";
						}

						elseif( !in_array($element['NOM_GROUPE'], $nom_grp_deja_aff) ){// plusieurs zones groupées
						///

							//echo '<pre>';
							//print_r($element);
							//echo '<br>'.$element['NOM_GROUPE'];
							//die('BASS');
 
								$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
								$pass_ligne++;

								////////////////////////////////
								$liste_obj		= $tab_elem_grp[$element['NOM_GROUPE']];
								$nb_obj		 		= count($liste_obj) ;
								if($nb_obj==2){ // pour faire 2 colspan de 2
										$html.= "\t<TR>\n";
										for($ordre=0; $ordre<2; $ordre++){
														
												if($liste_obj[$ordre]['TYPE_OBJET']=='label'){
														$html.= "\t\t<TD  colspan='2'><b>  &nbsp;";
														$html.= formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme);
														$html.= "&nbsp;</b></TD>\n";
												}
												else{ 
														$html.= "\t\t<TD  colspan='2'>&nbsp;";
														$html.= recherche_libelle_zone($liste_obj[$ordre]['ID_ZONE'],$langue);
														$html.= "&nbsp;&nbsp;&nbsp;<b> : ";
														//$html.= "\t\t<TD  class='td_left'>&nbsp;";
														$html.= formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme);
														$html.= "&nbsp;</b></TD>\n";
												}
										}
										$html.= "\t</TR>\n";
								}
								else{ 
										$nb_tr 				= intval( ($nb_obj-1) / 3) + 1; // $nb_obj-1 car le premier element est pris comme maitre 
										$reste_div 		= ($nb_obj-1) % 3;
										if( $reste_div > 0 ) $rowspan	=	$nb_tr;
										else $rowspan	=	$nb_tr - 1;
										if($nb_tr > 1) $rowspan_tr = " rowspan='$rowspan' ";
										$html.= "\t<TR>\n";
														
										$html.= "\t\t<TD $rowspan_tr  valign='top'>&nbsp;";
										if($liste_obj[0]['TYPE_OBJET']<>'label'){
												$html.= "".recherche_libelle_zone($liste_obj[0]['ID_ZONE'],$langue);
												$html.= "&nbsp;&nbsp;&nbsp;";
										}
										$html.= "<b> : ".formulaire_concat_zone_html($liste_obj[0],$langue,$id_systeme);
										$html.= "&nbsp;</b></TD>\n";
		
										for( $tr=0; $tr<$rowspan; $tr++ ){
												if($tr > 0)
														$html.= "\t<TR>\n";
												for($j=0; $j<3; $j++){
														$ordre = 3 * $tr + $j + 1;
														if(isset($liste_obj[$ordre])){
																$html.= "\t\t<TD nowrap='nowrap'>";
																$html.= recherche_libelle_zone($liste_obj[$ordre]['ID_ZONE'],$langue);
																$html.= "&nbsp;&nbsp;&nbsp;<b> : ";
																$html.= formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme);
																$html.= "&nbsp;</b></TD>\n";
														}
														else{
																if($j==1) $cols_rest = 2;
																else $cols_rest = 1;
																$html 		.="\t\t"."<TD COLSPAN='$cols_rest'>&nbsp;</TD>\n";
																break;
														}
												}
										}
										$html.= "\t</TR>\n";	
								}// fin for : parcours des lignes 
								/////////////////////////////////
							}
														
						}// fin : si type dimension on ignore
		} //Fin de parcours du dico
	
	$html	.= "</TABLE>";
	return $html;
}//Fin function generer_table_formulaire

function generer_table_formulaire_hist($id_theme, $langue ,$id_systeme, $ann=''){
	//liste des tables annuelles
	$req= "SELECT DICO_ZONE.ID_ZONE, DICO_ZONE.CHAMP_PERE, DICO_ZONE.TABLE_MERE, DICO_ZONE.ID_TABLE_MERE_THEME
			FROM DICO_ZONE, DICO_ZONE_SYSTEME
			WHERE DICO_ZONE_SYSTEME.ID_ZONE=DICO_ZONE.ID_ZONE AND DICO_ZONE_SYSTEME.ID_SYSTEME=".$id_systeme." AND DICO_ZONE.ID_THEME=".$id_theme;
	
	$res=$GLOBALS['conn_dico']->GetAll($req);
	$tab_mere_annuel = array();
	$tab_mere_non_annuel = array();
	if(is_array($res)){
		foreach($res as $rs){
			if($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']==$rs['CHAMP_PERE']){
				if(!in_array($rs['ID_TABLE_MERE_THEME'], $tab_mere_annuel)) $tab_mere_annuel[] = $rs['ID_TABLE_MERE_THEME'];
			}
		}
	}
	$list_tab_mere_annuel = '('.implode(',',$tab_mere_annuel).')';
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Construction table html pour zones non annuelles
	$requete	="
					SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
					FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
					WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
					AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
					AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
					AND			DICO_ZONE.ID_THEME = " . $id_theme . 
					" AND		DICO_ZONE.ID_TABLE_MERE_THEME NOT IN " . $list_tab_mere_annuel . 
					" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = " . $id_systeme .
					" AND		DICO_THEME_SYSTEME.ID_SYSTEME = " . $id_systeme .
					" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
					AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
					AND  	DICO_ZONE.ID_ZONE IN (".implode(', ',$_SESSION['list_chps_export']).")  
					ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
		
	// Traitement Erreur Cas : GetAll / GetRow
	try {
			$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
			if(!is_array($aresult)){                    
					throw new Exception('ERR_SQL');  
			} 
			$dico	= $aresult;
	}
	catch(Exception $e){
			$erreur = new erreur_manager($e,$requete);
	}
			// Fin Traitement Erreur Cas : GetAll / GetRow
	
	$req_lib        = "SELECT LIBELLE
						FROM DICO_TRADUCTION 
						WHERE CODE_NOMENCLATURE=".$id_theme.$id_systeme." AND CODE_LANGUE='".$langue."'
						AND NOM_TABLE='DICO_THEME_LIB_LONG';";
	$res_lib		= $GLOBALS['conn_dico']->GetAll($req_lib);
	$libelle_long   = $res_lib[0]['LIBELLE'];									
	
	$html = "<div>\n";
	$html 			.= "<TABLE border='1'>\n". '<CAPTION><B>' . $libelle_long . '</B></CAPTION>'."\n";
			
	//Recherche du LIBELLE BOOLEEN ex: 1) oui
	
	$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
						WHERE CODE_NOMENCLATURE=1 
						AND NOM_TABLE='DICO_BOOLEEN'
						AND CODE_LANGUE='".$langue."';";
			// Traitement Erreur Cas : GetAll / GetRow
			try {
					$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
					if(!is_array($aresult)){                    
							throw new Exception('ERR_SQL');  
					} 
					$GLOBALS['libelle_oui'] = $aresult[0]['LIBELLE'];									
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}
			// Fin Traitement Erreur Cas : GetAll / GetRow
			
	//echo $requete.'<br>';
	
	//Recherche du LIBELLE BOOLEEN ex: 2) non
	$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
						WHERE CODE_NOMENCLATURE=0 
						AND NOM_TABLE='DICO_BOOLEEN'
						AND CODE_LANGUE='".$langue."';";
			// Traitement Erreur Cas : GetAll / GetRow
			try {
					$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
					if(!is_array($aresult)){                    
							throw new Exception('ERR_SQL');  
					} 
					$GLOBALS['libelle_non'] = $aresult[0]['LIBELLE'];									
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}
			// Fin Traitement Erreur Cas : GetAll / GetRow

	$tab_elem_grp 			= array();
	$nom_grp_deja_aff 		= array();
	$html_fonc_TotMatFrml 	= '';
	$html_fonc_TotChpsFrml	= '';

	$tabms_matricielles = get_tabms_matricielles($dico);
	
	foreach($dico as $i_elem => $element){
		// Mettre un nom de groupe par defaut s'il en existe pas pour le type matriciel
		if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
			
			if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
				if( trim($element['NOM_GROUPE']) == '' ){
					$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
					$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
				}else{
					$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
					$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
				}
				$dico[$i_elem]['OBJET_MATRICIEL']	= true;
				$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
			}
		}
		elseif( ($element['NOM_GROUPE'] <> '') and ($element['TYPE_OBJET'] <> 'liste_radio') and ($element['TYPE_OBJET'] <> 'liste_checkbox') ){
			$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
			$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
			$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
		}
	}//Fin de parcours du dico
			$pass_ligne =-1;
			
				foreach($dico as $element){
					if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){ // si type dimension on ignore
						if($element['CHAMP_PERE']	==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){
							//
						}
						elseif( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) && (!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)) ){
								
								$affiche_vertic_mes	=	$element['AFFICH_VERTIC_MES'];
								$affiche_sous_totaux	=	$element['AFFICHE_SOUS_TOTAUX'];
								$nom_grp_deja_aff[]			=	$element['NOM_GROUPE'];
								$affiche_totaux_mat_Frml 	= 	array();
								
								$mat_dim_ligne 		=	get_dims_zone_matricielle('dimension_ligne', $element['ID_TABLE_MERE_THEME'], $dico);
								$mat_dim_colonne	=	get_dims_zone_matricielle('dimension_colonne', $element['ID_TABLE_MERE_THEME'], $dico);
								
								$mat_codes_ligne	= array();
								if(is_array($mat_dim_ligne) && count($mat_dim_ligne)){
									
									//Ajout hebie pour gerer une table de donnees comme table fille (nomenclature) pour generation de theme pendant la saisie
									if ($mat_dim_ligne['TABLE_FILLE']=='') $mat_dim_ligne['TABLE_FILLE']=$mat_dim_ligne['TABLE_INTERFACE'];
									if ($mat_dim_ligne['CHAMP_FILS']=='') $mat_dim_ligne['CHAMP_FILS']=$mat_dim_ligne['CHAMP_INTERFACE'];
									if ($mat_dim_ligne['SQL_REQ']=='') $mat_dim_ligne['SQL_REQ']=$mat_dim_ligne['REQUETE_CHAMP_INTERFACE'];
									//Fin modif
									
									$mat_nomenc_ligne	= requete_nomenclature($mat_dim_ligne['TABLE_FILLE'], $mat_dim_ligne['CHAMP_FILS'], $mat_dim_ligne['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_ligne['SQL_REQ'], $code_annee, $code_etablissement) ;
									if(is_array($mat_nomenc_ligne)){
										foreach($mat_nomenc_ligne as $mat_i => $mat_val){
											$mat_codes_ligne[] = array('code' => $mat_val[get_champ_extract($mat_dim_ligne['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE']);
										}
									}
								}
								
								$mat_codes_colonne	= array();
								if(is_array($mat_dim_colonne) && count($mat_dim_colonne)){
									
									//Ajout hebie pour gerer une table de donnees comme table fille (nomenclature) pour generation de theme pendant la saisie
									if ($mat_dim_colonne['TABLE_FILLE']=='') $mat_dim_colonne['TABLE_FILLE']=$mat_dim_colonne['TABLE_INTERFACE'];
									if ($mat_dim_colonne['CHAMP_FILS']=='') $mat_dim_colonne['CHAMP_FILS']=$mat_dim_colonne['CHAMP_INTERFACE'];
									if ($mat_dim_colonne['SQL_REQ']=='') $mat_dim_colonne['SQL_REQ']=$mat_dim_colonne['REQUETE_CHAMP_INTERFACE'];
									//Fin modif
									$mat_nomenc_colonne	= requete_nomenclature($mat_dim_colonne['TABLE_FILLE'], $mat_dim_colonne['CHAMP_FILS'], $mat_dim_colonne['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne['SQL_REQ'], $code_annee, $code_etablissement) ;
									if(is_array($mat_nomenc_colonne)){
										foreach($mat_nomenc_colonne as $mat_i => $mat_val){
											$mat_codes_colonne[] = array('code' => $mat_val[get_champ_extract($mat_dim_colonne['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
										}
									}
								}
								
								$mat_liste_mes 			= array();
								$tab_libelles_mesures 	= array();
								$tab_types_mesures 		= array();
								$attrib_obj_mesures 	= array();
								$vertic_aff_mesures		= array();
								$elem_obj_mesures 		= array();
								$tab_lib_mes_tot 		= array();
								$types_mes_matr_att 	= array('booleen', 'checkbox', 'text', 'combo', 'liste_radio');
								$tab_index_mes_aff_tot	= array();
								$labels_tabl_matr		= '';
								$vertic_for_all_mes		= true;
								
								foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
									if($mat_val['TYPE_OBJET'] == 'label'){
										$labels_tabl_matr .= '<b>'.recherche_libelle_zone($mat_val['ID_ZONE'], $langue) . '</b><br>';
									}else{
										if(recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
											$tab_libelles_mesures[$mat_i]	= 	recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
										}
										$mat_liste_mes[$mat_i]		= $mat_val['CHAMP_PERE'];
										$tab_types_mesures[$mat_i] 	= $mat_val['TYPE_OBJET'];
										$attrib_obj_mesures[$mat_i] = $mat_val['ATTRIB_OBJET'];
										$vertic_aff_mesures[$mat_i] = $mat_val['TAILLE_LIB_VERTICAUX'];
										
										$elem_obj_mesures[$mat_i] 	= $mat_val ;
										if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
											$affiche_totaux_mat_Frml[$mat_i] 	= true;
											$tab_lib_mes_tot[$mat_i]	=	$tab_libelles_mesures[$mat_i] ;
											$tab_index_mes_aff_tot[] = $mat_i ;
										}
									}
								}
								$nb_mesures =  count($mat_liste_mes);
								
								$html.= "\t<TR>\n";
								$html.= "\t\t<TD colspan=4 valign='top'>&nbsp;";
								if(trim($labels_tabl_matr) <> ''){
									$html.= $labels_tabl_matr;
								}
								
								if( count($mat_codes_ligne) || count($mat_codes_colonne) ){
									//echo '<br> NOM_GROUPE = ' .$element['NOM_GROUPE'] ;
									//$affiche_totaux_mat_Frml 	= $element['AFFICHE_TOTAL'];
									
									$html.= "<TABLE border=1>";
									///////////////////////////////////////////////////////////////////////////////	
									/////Cas d'affichage horizontal des mesures //////////////////////////////////
									/////////////////////////////////////////////////////////////////////////////	
									if(!$affiche_vertic_mes	){
										if ($nb_mesures > 1) {
											if($affiche_sous_totaux)
												$colspan =' COLSPAN='.($nb_mesures+1).' ';
											else
												$colspan =' COLSPAN='.$nb_mesures.' ';
										}else{
											$colspan =' ';
										}
										if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
											$rowspan =' ROWSPAN=2 ';
										}else{
											$rowspan =' ';
										}
										$html 			.= "\n\t<TR>\n\t\t<TD $rowspan>&nbsp</TD>\n";	// Zone vide d'intersection des 2 dimensions
									
									if(count($mat_codes_colonne)){
										foreach ($mat_codes_colonne as $col => $elem_col){
											// On imprime le libellé des colonnes
											$html		.="\t\t<TD $colspan align='center'>";
											
											if( ($mat_dim_colonne['TAILLE_LIB_VERTICAUX'] > 0) && $nb_mesures == 1){
												//A revoir libellé affiché
												$libelle_afficher   =   $elem_col['libelle'];
												//$libelle_afficher	.=	'+++';
												$html               .= 	$libelle_afficher;
											}else{
												$html 		.= $elem_col['libelle'];
											}
											
											$html		.="</TD>\n";
										}
									}else{
										$html		.="\t\t<TD $colspan>";
										$html 		.= '&nbsp;';
										$html		.="</TD>\n";
									}
		
									$html 			.= "\n\t</TR>\n";
									
									if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
										$html 			.= "\n\t<TR>\n";	// Zone vide d'intersection des 2 dimensions
										if(count($mat_codes_colonne)){
											foreach ($mat_codes_colonne as $col => $elem_col){
												// On imprime le libellé des colonnes
												foreach($mat_liste_mes as $i_mes => $mes){
													$html		.="\t\t<TD  align='center'>";
													
													if( $vertic_aff_mesures[$i_mes] > 0){
														//A revoir libellé affiché
														$libelle_afficher   =   $tab_libelles_mesures[$i_mes];
														//$libelle_afficher	.=	'+++';
														$html               .= 	$libelle_afficher;
														
													}else{
														$html		.=$tab_libelles_mesures[$i_mes];
														
													}
													$html		.="</TD>\n";
												}
											}
										}else{
											foreach($mat_liste_mes as $i_mes => $mes){
												$html		.="\t\t<TD  align='center'><b>".$tab_libelles_mesures[$i_mes]."</b></TD>\n";
											}
										}
										$html 			.= "\n\t</TR>\n";
									}
									
									// traitements des lignes 
									if(count($mat_codes_ligne)){
										foreach($mat_codes_ligne as $li => $elem_li){
											$Ligne = $li;
								
											$html 		        .= "\n\t<TR>\n";

													  // On imprime l'entête de ligne
											$html 		.= "\n\t\t<TD align='center'>".$elem_li['libelle']."</TD>\n";
											if(count($mat_codes_colonne)){
												foreach($mat_codes_colonne as $col => $elem_col){
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
													   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
														$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
														
														$html		.= get_cell_matrice(0 ,$elem_li['code'].'_'.$elem_col['code'], $elem_obj_mesures[$i_mes], $ann, $langue, $id_systeme);
														
														$html		.= "</b></TD>\n";
													}
												}
											}
											else{ // Pas de dimension Colonne

												foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
												   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
														$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
														
														$html		.= get_cell_matrice(0 ,$elem_li['code'], $elem_obj_mesures[$i_mes], $ann, $langue, $id_systeme);
														
														$html		.= "</b></TD>\n";
												}
																								
											}
											$html 		        .= "\n\t</TR>\n";
										}// Fin parcours des lignes
									} // fin si exist  dimension Ligne
									else{ // pas de dimensions lignes

											$html 		        .= "\n\t<TR>\n";
											
											$rowspan_chp_tot = '';
											$br_chp_tot = '';
											
											$html 		.= "\n\t\t<TD>&nbsp;</TD>\n";
											if(count($mat_codes_colonne)){
												foreach($mat_codes_colonne as $col => $elem_col){
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
													  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
														$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
														
														$html		.= get_cell_matrice(0 , $elem_col['code'], $elem_obj_mesures[$i_mes], $ann, $langue, $id_systeme);
														
														$html		.= "</b></TD>\n";
													}
												}
											}
											$html 		        .= "\n\t</TR>\n";
									}

									$html.= "</TABLE>";
									///////////////////////////////////////////////////////////////////////////////	
									/////Cas d'affichage vertical des mesures ////////////////////////////////////
									/////////////////////////////////////////////////////////////////////////////	
									}else{
										if ($nb_mesures > 1) {
											if($affiche_sous_totaux){
												$rowspan =' ROWSPAN='.($nb_mesures + 2).' ';
											}else{
												$rowspan =' ROWSPAN='.($nb_mesures + 1).' ';
											}
										}else{
											$rowspan =' ';
										}
									
										if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
											$colspan =' COLSPAN=2 ';
											//$colspan =' ';
										}else{
											$colspan =' ';
										}
										
										$html 			.= "\n\t<TR>\n\t\t<TD $colspan>&nbsp</TD>\n";	// Zone vide d'intersection des 2 dimensions
										if(count($mat_codes_colonne)){
											foreach ($mat_codes_colonne as $col => $elem_col){
												// On imprime le libellé des colonnes
												$html		.="\t\t<TD align='center'><b>";
												$html 		.= $elem_col['libelle'];
												$html		.="</b></TD>\n";
											}
										}else{
											$html		.="\t\t<TD>";
											$html 		.= '&nbsp;';
											$html		.="</TD>\n";
										}
			
										$html 			.= "\n\t</TR>\n";
										
										// traitements des lignes 
										if(count($mat_codes_ligne)){
											foreach($mat_codes_ligne as $li => $elem_li){
												$Ligne = $li;
									
												$html 		        .= "\n\t<TR>\n";
	
														  // On imprime l'entête de ligne
												$html 		.= "\n\t\t<TD $rowspan valign='middle' align='center'>".$elem_li['libelle']."</TD>\n";
												$k=1;

												foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
													$html 			.= "\n\t<TR>\n";
													// On imprime le libellé des mesures
													$html		.="\t\t<TD  nowrap='nowrap' align='center' valign='middle'>".$tab_libelles_mesures[$i_mes]."</TD>\n";
																								
													if(count($mat_codes_colonne)){
														foreach($mat_codes_colonne as $col => $elem_col){
															//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
															   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";

																$html		.= get_cell_matrice(0 ,$elem_li['code'].'_'.$elem_col['code'], $elem_obj_mesures[$i_mes], $ann, $langue, $id_systeme);
																
																$html		.= "</b></TD>\n";
															//}
														}
													}
													else{ // Pas de dimension Colonne
		
														//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
														   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
																
																$html		.= get_cell_matrice(0 ,$elem_li['code'], $elem_obj_mesures[$i_mes], $ann, $langue, $id_systeme);
																
																$html		.= "</b></TD>\n";
														//}
													}
													$html 		    .= "\n\t</TR>\n";
												}
											}// Fin parcours des lignes
										} // fin si exist  dimension Ligne
										else{ // pas de dimensions lignes
	
												foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
													
													$html 			.= "\n\t<TR>\n";	
													// On imprime le libellé des mesures
													$html		.="\t\t<TD $colspan nowrap='nowrap' align='center' valign='middle'>".$tab_libelles_mesures[$i_mes]."</TD>\n";
													
													$rowspan_chp_tot = '';
													$br_chp_tot = '';
													//$html 		.= "\n\t\t<TD  CLASS='".$classe_fond."'>&nbsp;</TD>\n";
													
													if(count($mat_codes_colonne)){
														foreach($mat_codes_colonne as $col => $elem_col){
															//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
															  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
																
																$html		.= get_cell_matrice(0 , $elem_col['code'], $elem_obj_mesures[$i_mes], $ann, $langue, $id_systeme);
																
																$html		.= "</b></TD>\n";
															//}
														}
													}
													
													$html 		        .= "\n\t</TR>\n";
												}								
										}
										$html.= "</TABLE>";
									}
								}// fin si exist des dimensions								
								
								$html.= "</TD>\n";
								$html.= "\t</TR>\n";					
								
						}
						elseif( ($element['TYPE_OBJET']=='liste_radio' or $element['TYPE_OBJET']=='liste_checkbox' or $element['TYPE_OBJET']=='text_valeur_multiple') && !(isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
								$pass_ligne++;

								if($element['TYPE_OBJET']=='liste_radio'){
										$type_objet ='radio';
										$liste_choix 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
								}
								elseif($element['TYPE_OBJET']=='liste_checkbox'){
										$type_objet ='checkbox';
										$liste_choix 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
								}elseif($element['TYPE_OBJET']=='text_valeur_multiple'){
										$type_objet ='text';
										$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
										$liste_choix 	= requete_nomenclature($zone_ref['TABLE_FILLE'], $zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
									//echo '<pre>liste_choix <br>';
									//print_r($liste_choix );
								}
								
								//echo '<pre>liste_choix <br>';
								//print_r($liste_choix );
								$nb_choix		 	= count($liste_choix);
								$nb_tr 				= intval($nb_choix / 3) + 1;
								$reste_div 		= $nb_choix % 3;
								if( $reste_div > 0 ) $rowspan	=	$nb_tr;
								else $rowspan	=	$nb_tr - 1;
								//echo"<br>rowspan=$rowspan";
								$rowspan_tr = '';
								if($nb_tr > 1) $rowspan_tr = " rowspan='$rowspan' ";
								//else 
								$html.= "\t<TR>\n";
								$html.= "\t\t<TD $rowspan_tr valign='top'>&nbsp;";
								$html.= recherche_libelle_zone($element['ID_ZONE'],$langue);
								$html.= "&nbsp;</TD>\n";
								for( $tr=0; $tr<$rowspan; $tr++ ){
										if($tr > 0)
												$html.= "\t<TR>\n";
										for($j=0; $j<3; $j++){
												$ordre = 3 * $tr + $j ;
												if(isset($liste_choix[$ordre])){
														$html.= "\t\t<TD nowrap='nowrap'>&nbsp;<b>";
														//$html.= "<span class='td_left'>";
														$html.= $liste_choix[$ordre]['LIBELLE'];
														if($element['TYPE_OBJET']=='liste_radio'){
																$name				=	$element['CHAMP_PERE']."_0"; 
														}
														elseif($element['TYPE_OBJET']=='liste_checkbox' ){
																$name				=	$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($element['CHAMP_PERE'])];
														}elseif($element['TYPE_OBJET']=='text_valeur_multiple'){
																$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
																$name				=	$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($zone_ref['CHAMP_FILS'])];
														}
														

														$html.= "&nbsp;";
														//$html.= "VALUE=$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$element['CHAMP_PERE']].">";
														
														// Modif Alassane
														if (strlen($element['CHAMP_PERE'])>30){
															if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql'){
																$taille_max_car =   strlen($element['CHAMP_PERE']);                                                               
															}else if ($GLOBALS['conn']->databaseType == 'postgres9') {
																$taille_max_car=strlen($element['CHAMP_PERE']);
															}else{
																$taille_max_car =   31;
															}
														}else{
															 $taille_max_car = strlen($element['CHAMP_PERE']);
														}                                                        
														
														if($element['TYPE_OBJET']=='text_valeur_multiple'){
															$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
															$html.= " : \$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($zone_ref['CHAMP_FILS'])]."_ANN_".$ann;
														}else{
															$html.= " : \$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($element['CHAMP_PERE'])]."_ANN_".$ann;
														}
														// Fin Modif Alassane
														
														//$html.= "</span>";
														$html.= "&nbsp;</b></TD>\n";
												}
												else{
														if($j==1) $cols_rest = 2;
														else $cols_rest = 1;
														$html 		.="\t\t"."<TD COLSPAN='$cols_rest'>&nbsp;</TD>\n";
														break;
												}
										}
										$html.= "\t</TR>\n";	
								}// fin for : parcours des lignes 
						}
						elseif($element['TYPE_OBJET']=='loc_etab'){ /// LOCALISATION ETABLISSEMENT
								lit_libelles_page('/questionnaire.php');
								//print_r($this->conn);
								//echo'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
								$html.= "\t<TR>\n";
								$html.= "\t\t<TD colspan=4 valign='top'>&nbsp;";
								///  données à prendre de quelque part
								$tab_chaines = get_chaines_loc($id_systeme);
								//echo '<pre>';
								//print_r($tab_chaines);
								//$tab_chaines = array();
								//$tab_chaines[] = array( 'numCh' => 2, 'nbRegs' => 4 );
								/// 
								//die();
								$html.= "<table><tr>\n";
								// pour chaque chaine mettre dans un TD
								foreach($tab_chaines as $iLoc => $chaine){
										$requete        = 'SELECT   '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', 
																	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', 
																	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', 
																	'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
															FROM    '.$GLOBALS['PARAM']['HIERARCHIE'].' , '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
															WHERE	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
																	'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
																	AND	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$chaine['numCh'].'
															ORDER BY 		'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC';
										//echo $requete;
										//exit;
										$aresult = array();
										// Traitement Erreur Cas : GetAll / GetRow
										try {
												$aresult	= $GLOBALS['conn']->GetAll($requete);
												if(!is_array($aresult)){                    
														throw new Exception('ERR_SQL');  
												} 
																					
										}
										catch(Exception $e){
												$erreur = new erreur_manager($e,$requete);
										}
										// Fin Traitement Erreur Cas : GetAll / GetRow
										 
										//$nbTypeReg = count($aresult);
										$html.= "<td valign='top'><table>\n";
										$html.='<tr>'."\n";
										$html.='<td colspan="2" nowrap="nowrap">'.recherche_libelle($chaine['numCh'],$langue,'DICO_CHAINE_LOCALISATION').'&nbsp;&nbsp;&nbsp;</td>'."\n";
										$html.='</tr>'."\n";	
										
										$html.='<tr>'."\n";
										$html.='<td>&nbsp;</td>'."\n";
										$html.='<td nowrap="nowrap">'."\n";
									   
										$html.= "\$LOC_REG_".$iLoc;    
										$html.= "</td>\n"; 
										$html.= "</tr>\n";	
											
										foreach($aresult as $iTypeReg => $ligne_type_reg){
										$html.= "<tr>\n"; 
												$html.= "<td>".recherche_libelle($ligne_type_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']],$langue,$GLOBALS['PARAM']['TYPE_REGROUPEMENT'])."</td>\n";
												$html.= "<td>\$LIBELLE_REG_".$iLoc.'_'.$iTypeReg."</td>\n";
										$html.= "</tr>\n";												
										}
										$html.= "</table></td>\n";
								}
								$html.= "</tr></table>\n";
								$html.= "</TD>\n";
								$html.= "\t</TR>\n";
						}// FIN /// LOCALISATION ETABLISSEMENT

						elseif( ((trim($element['NOM_GROUPE']=='')) or (isset($tab_elem_grp[$element['NOM_GROUPE']]) and ( count($tab_elem_grp[$element['NOM_GROUPE']])==1 ) )) && (!isset($element['OBJET_MATRICIEL'])) ){
								// zone non groupée à afficher sur une ligne
								$pass_ligne++;
								
								$html.= "\t<TR>\n";
								
								if($element['TYPE_OBJET']=='label'){
										$html.= "\t\t<TD colspan='4'>&nbsp;<b>  ";
										$html.= formulaire_concat_zone_html($element,$langue,$id_systeme, $ann);
										$html.= "&nbsp;</b></TD>\n";
								}
								else{
										$html.= "\t\t<TD colspan='4'>&nbsp;";
										$html.= recherche_libelle_zone($element['ID_ZONE'],$langue);
										$html.= "&nbsp;&nbsp;&nbsp;<b> : ";
										//$html.= "\t\t<TD colspan='3'>&nbsp;";
										$html.= formulaire_concat_zone_html($element,$langue,$id_systeme, $ann);
										$html.= "&nbsp;</b></TD>\n";
										//$html.= "\t\t<TD colspan='2'>&nbsp;</TD>\n";
								}
								

								
								$html.= "\t</TR>\n";
						}

						elseif( !in_array($element['NOM_GROUPE'], $nom_grp_deja_aff) ){// plusieurs zones groupées
						///

							//echo '<pre>';
							//print_r($element);
							//echo '<br>'.$element['NOM_GROUPE'];
							//die('BASS');
 
								$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
								$pass_ligne++;

								////////////////////////////////
								$liste_obj		= $tab_elem_grp[$element['NOM_GROUPE']];
								$nb_obj		 		= count($liste_obj) ;
								if($nb_obj==2){ // pour faire 2 colspan de 2
										$html.= "\t<TR>\n";
										for($ordre=0; $ordre<2; $ordre++){
														
												if($liste_obj[$ordre]['TYPE_OBJET']=='label'){
														$html.= "\t\t<TD  colspan='2'>&nbsp;<b>  ";
														$html.= formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme, $ann);
														$html.= "&nbsp;</b></TD>\n";
												}
												else{ 
														$html.= "\t\t<TD  colspan='2'>&nbsp;";
														$html.= recherche_libelle_zone($liste_obj[$ordre]['ID_ZONE'],$langue);
														$html.= "&nbsp;&nbsp;&nbsp;<b> : ";
														//$html.= "\t\t<TD  class='td_left'>&nbsp;";
														$html.= formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme, $ann);
														$html.= "&nbsp;</b></TD>\n";
												}
										}
										$html.= "\t</TR>\n";
								}
								else{ 
										$nb_tr 				= intval( ($nb_obj-1) / 3) + 1; // $nb_obj-1 car le premier element est pris comme maitre 
										$reste_div 		= ($nb_obj-1) % 3;
										if( $reste_div > 0 ) $rowspan	=	$nb_tr;
										else $rowspan	=	$nb_tr - 1;
										if($nb_tr > 1) $rowspan_tr = " rowspan='$rowspan' ";
										$html.= "\t<TR>\n";
														
										$html.= "\t\t<TD $rowspan_tr  valign='top'>&nbsp;";
										if($liste_obj[0]['TYPE_OBJET']<>'label'){
												$html.= recherche_libelle_zone($liste_obj[0]['ID_ZONE'],$langue);
												$html.= "&nbsp;&nbsp;&nbsp;";
										}
										$html.= "<b> : ".formulaire_concat_zone_html($liste_obj[0],$langue,$id_systeme, $ann);
										$html.= "&nbsp;</b></TD>\n";
		
										for( $tr=0; $tr<$rowspan; $tr++ ){
												if($tr > 0)
														$html.= "\t<TR>\n";
												for($j=0; $j<3; $j++){
														$ordre = 3 * $tr + $j + 1;
														if(isset($liste_obj[$ordre])){
																$html.= "\t\t<TD nowrap='nowrap'>";
																$html.= recherche_libelle_zone($liste_obj[$ordre]['ID_ZONE'],$langue);
																$html.= "&nbsp;&nbsp;&nbsp;<b> : ";
																$html.= formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme, $ann);
																$html.= "&nbsp;</b></TD>\n";
														}
														else{
																if($j==1) $cols_rest = 2;
																else $cols_rest = 1;
																$html 		.="\t\t"."<TD COLSPAN='$cols_rest'>&nbsp;</TD>\n";
																break;
														}
												}
										}
										$html.= "\t</TR>\n";	
								}// fin for : parcours des lignes 
								/////////////////////////////////
							}
														
						}// fin : si type dimension on ignore
		} //Fin de parcours du dico
	
	$html	.= "</TABLE>";
	$html .= "</div>\n";
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Construction table html pour zones annuelles
	$requete				="
					SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
					FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
					WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
					AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
					AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
					AND			DICO_ZONE.ID_THEME = " . $id_theme . 
					" AND		DICO_ZONE.ID_TABLE_MERE_THEME IN " . $list_tab_mere_annuel . 
					" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = " . $id_systeme .
					" AND		DICO_THEME_SYSTEME.ID_SYSTEME = " . $id_systeme .
					" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
					AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
					AND  	DICO_ZONE.ID_ZONE IN (".implode(', ',$_SESSION['list_chps_export']).")  
					ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";

	// Traitement Erreur Cas : GetAll / GetRow
	try {
			$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
			if(!is_array($aresult)){                    
					throw new Exception('ERR_SQL');  
			} 
			$dico	= $aresult;
	}
	catch(Exception $e){
			$erreur = new erreur_manager($e,$requete);
	}
	// Fin Traitement Erreur Cas : GetAll / GetRow
	$html .= "<div>\n";
	$html 	.= "<TABLE border='1'>". '<CAPTION><B>' . recherche_libelle_page('teach_hist') . '</B></CAPTION>'."\n";
	
	$req_ann = "SELECT DISTINCT ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].", ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_ANNEE'].
						", ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].
				" FROM ".$GLOBALS['PARAM']['TYPE_ANNEE'].", ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].
				" WHERE ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].".".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']." AND ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."<>255".
				" ORDER BY ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];

	$res_ann = $GLOBALS['conn']->GetAll($req_ann);
	
		$tab_elem_grp 			= array();
		$nom_grp_deja_aff 		= array();

		$tabms_matricielles = get_tabms_matricielles($dico);
		
		foreach($dico as $i_elem => $element){
			// Mettre un nom de groupe par defaut s'il en existe pas pour le type matriciel
			if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
				
				if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
					if( trim($element['NOM_GROUPE']) == '' ){
						$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
						$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
					}else{
						$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
						$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
					}
					$dico[$i_elem]['OBJET_MATRICIEL']	= true;
					$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
				}
			}
		}//Fin de parcours du dico
				$pass_ligne =-1;
				
				$html .= "<TR>\n";
				$html .= "<TD>&nbsp;</TD>\n";
				foreach($res_ann as $annee){
					$html .= "<TD>".'<B>' . $annee[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] . '</B>'."</TD>\n";
				}
				$html .= "</TR>\n";
				
				foreach($dico as $element){
					
					// Mettre un nom de groupe par defaut s'il en existe pas pour le type matriciel
					if( ($element['NOM_GROUPE'] <> '') and ($element['TYPE_OBJET'] <> 'liste_radio') and ($element['TYPE_OBJET'] <> 'liste_checkbox') and ($element['TYPE_OBJET']<>'text_valeur_multiple') and !(isset($element['OBJET_MATRICIEL']) && $element['OBJET_MATRICIEL'] == true) ){
						$element['NOM_GROUPE'] 			= '';
					}	
					
					//Construction de la colonnes des libellés
						
					if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') && (!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)) ){ // si type dimension on ignore
						$html 	.="<TR>";
						if($element['CHAMP_PERE']	==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){
							//
						}
						elseif( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) && ($element['TYPE_OBJET'] == 'label') && (!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)) ){
								//$nom_grp_deja_aff[] 		= $element['NOM_GROUPE'];
								$labels_tabl_matr		= '';
		
								foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
									if($mat_val['TYPE_OBJET'] == 'label'){
										$labels_tabl_matr .= '<b>'.recherche_libelle_zone($mat_val['ID_ZONE'], $langue) . '</b><br>';
									}
								}
								
								if(trim($labels_tabl_matr) <> ''){
									$html.= "\t\t<TD valign='top'>&nbsp;";
									$html.= $labels_tabl_matr;
									$html.= "</TD>\n";
								}
								
						}
						elseif( ($element['TYPE_OBJET']=='liste_radio' or $element['TYPE_OBJET']=='liste_checkbox' or $element['TYPE_OBJET']=='text_valeur_multiple') && !(isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
								$pass_ligne++;
		
								$html.= "\t\t<TD valign='top'>&nbsp;";
								$html.= recherche_libelle_zone($element['ID_ZONE'],$langue);
								$html.= "&nbsp;</TD>\n";
						}
						elseif($element['TYPE_OBJET']=='loc_etab'){ /// LOCALISATION ETABLISSEMENT
								lit_libelles_page('/questionnaire.php');

								$html.= "\t\t<TD valign='top'>&nbsp;";
								///  données à prendre de quelque part
								$tab_chaines = get_chaines_loc($id_systeme);
								// pour chaque chaine mettre dans un TD
								foreach($tab_chaines as $iLoc => $chaine){
										$html.=recherche_libelle($chaine['numCh'],$langue,'DICO_CHAINE_LOCALISATION').'&nbsp;'."\n";
								}
								$html.= "</TD>\n";
						}// FIN /// LOCALISATION ETABLISSEMENT
						elseif( ((trim($element['NOM_GROUPE']=='')) or (isset($tab_elem_grp[$element['NOM_GROUPE']]) and ( count($tab_elem_grp[$element['NOM_GROUPE']])==1 ) )) && (!isset($element['OBJET_MATRICIEL'])) ){
								// zone non groupée à afficher sur une ligne
								$pass_ligne++;

								if($element['TYPE_OBJET']<>'label'){
										$html.= "\t\t<TD valign='top'>&nbsp;";
										$html.= recherche_libelle_zone($element['ID_ZONE'],$langue);
										$html.= "&nbsp;</TD>\n";
										//$html.= "\t\t<TD colspan='2'>&nbsp;</TD>\n";
								}
						}
						else{
								// zone inconnu
								$pass_ligne++;
								$html.= "\t\t<TD>&nbsp;</TD>\n";
						}
					}// fin : si type dimension on ignore
					
					//Fin Construction de la colonnes des libellés	
					$nom_grp_deja_aff_ann = array();
						
					if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') && (!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)) ){ // si type dimension on ignore
					
						foreach($res_ann as $i_ann => $annee){
						
							$html 	.="<TD>";
							$html 	.= "<TABLE>"."\n";
						
							if($element['CHAMP_PERE']	==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){
								//
							}
							elseif( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) && (!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff_ann[$i_ann])) ){
									
									$nom_grp_deja_aff[]			=	$element['NOM_GROUPE'];
									$nom_grp_deja_aff_ann[$i_ann][]			=	$element['NOM_GROUPE'];

									$affiche_vertic_mes	=	$element['AFFICH_VERTIC_MES'];
									
									$mat_dim_ligne 		=	get_dims_zone_matricielle('dimension_ligne', $element['ID_TABLE_MERE_THEME'], $dico);
									$mat_dim_colonne	=	get_dims_zone_matricielle('dimension_colonne', $element['ID_TABLE_MERE_THEME'], $dico);
									
									$mat_codes_ligne	= array();
									if(is_array($mat_dim_ligne) && count($mat_dim_ligne)){
										
										//Ajout hebie pour gerer une table de donnees comme table fille (nomenclature) pour generation de theme pendant la saisie
										if ($mat_dim_ligne['TABLE_FILLE']=='') $mat_dim_ligne['TABLE_FILLE']=$mat_dim_ligne['TABLE_INTERFACE'];
										if ($mat_dim_ligne['CHAMP_FILS']=='') $mat_dim_ligne['CHAMP_FILS']=$mat_dim_ligne['CHAMP_INTERFACE'];
										if ($mat_dim_ligne['SQL_REQ']=='') $mat_dim_ligne['SQL_REQ']=$mat_dim_ligne['REQUETE_CHAMP_INTERFACE'];
										//Fin modif
										
										$mat_nomenc_ligne	= requete_nomenclature($mat_dim_ligne['TABLE_FILLE'], $mat_dim_ligne['CHAMP_FILS'], $mat_dim_ligne['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_ligne['SQL_REQ'], $code_annee, $code_etablissement) ;
										if(is_array($mat_nomenc_ligne)){
											foreach($mat_nomenc_ligne as $mat_i => $mat_val){
												$mat_codes_ligne[] = array('code' => $mat_val[get_champ_extract($mat_dim_ligne['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE']);
											}
										}
									}
									
									$mat_codes_colonne	= array();
									if(is_array($mat_dim_colonne) && count($mat_dim_colonne)){
										
										//Ajout hebie pour gerer une table de donnees comme table fille (nomenclature) pour generation de theme pendant la saisie
										if ($mat_dim_colonne['TABLE_FILLE']=='') $mat_dim_colonne['TABLE_FILLE']=$mat_dim_colonne['TABLE_INTERFACE'];
										if ($mat_dim_colonne['CHAMP_FILS']=='') $mat_dim_colonne['CHAMP_FILS']=$mat_dim_colonne['CHAMP_INTERFACE'];
										if ($mat_dim_colonne['SQL_REQ']=='') $mat_dim_colonne['SQL_REQ']=$mat_dim_colonne['REQUETE_CHAMP_INTERFACE'];
										//Fin modif
										$mat_nomenc_colonne	= requete_nomenclature($mat_dim_colonne['TABLE_FILLE'], $mat_dim_colonne['CHAMP_FILS'], $mat_dim_colonne['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne['SQL_REQ'], $code_annee, $code_etablissement) ;
										if(is_array($mat_nomenc_colonne)){
											foreach($mat_nomenc_colonne as $mat_i => $mat_val){
												$mat_codes_colonne[] = array('code' => $mat_val[get_champ_extract($mat_dim_colonne['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
											}
										}
									}
									
									$mat_liste_mes 			= array();
									$tab_libelles_mesures 	= array();
									$tab_types_mesures 		= array();
									$attrib_obj_mesures 	= array();
									$vertic_aff_mesures		= array();
									$elem_obj_mesures 		= array();
									$tab_lib_mes_tot 		= array();
									$types_mes_matr_att 	= array('booleen', 'checkbox', 'text', 'combo', 'liste_radio');
									$tab_index_mes_aff_tot	= array();
									$labels_tabl_matr		= '';
									$vertic_for_all_mes		= true;
									
									foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
										if($mat_val['TYPE_OBJET'] == 'label'){
											$labels_tabl_matr .= '<b>'.recherche_libelle_zone($mat_val['ID_ZONE'], $langue) . '</b><br>';
										}else{
											if(recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
												$tab_libelles_mesures[$mat_i]	= 	recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
											}
											$mat_liste_mes[$mat_i]		= $mat_val['CHAMP_PERE'];
											$tab_types_mesures[$mat_i] 	= $mat_val['TYPE_OBJET'];
											$attrib_obj_mesures[$mat_i] = $mat_val['ATTRIB_OBJET'];
											$vertic_aff_mesures[$mat_i] = $mat_val['TAILLE_LIB_VERTICAUX'];
											
											$elem_obj_mesures[$mat_i] 	= $mat_val ;
										}
									}
									$nb_mesures =  count($mat_liste_mes);
									
									//$html.= "\t<TR class='$class_ligne'><TD colspan=4 valign='top'>&nbsp;</TD></TR>\n";
									
									$html.= "\t<TR>\n";
									$html.= "\t\t<TD colspan=4 valign='top'>";
									
									if( count($mat_codes_ligne) || count($mat_codes_colonne) ){
										//echo '<br> NOM_GROUPE = ' .$element['NOM_GROUPE'] ;

										$html.= "<TABLE border=1>";
										///////////////////////////////////////////////////////////////////////////////	
										/////Cas d'affichage horizontal des mesures //////////////////////////////////
										/////////////////////////////////////////////////////////////////////////////	
										if(!$affiche_vertic_mes	){
											if ($nb_mesures > 1) {
												$colspan =' COLSPAN='.$nb_mesures.' ';
											}else{
												$colspan =' ';
											}
											if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
												$rowspan =' ROWSPAN=2 ';
											}else{
												$rowspan =' ';
											}
											$html 			.= "\n\t<TR>\n\t\t<TD $rowspan>&nbsp</TD>\n";	// Zone vide d'intersection des 2 dimensions
										
										if(count($mat_codes_colonne)){
											foreach ($mat_codes_colonne as $col => $elem_col){
												// On imprime le libellé des colonnes
												$html		.="\t\t<TD $colspan align='center'>";
												
												if( ($mat_dim_colonne['TAILLE_LIB_VERTICAUX'] > 0) && $nb_mesures == 1){
													//A revoir affichage libellé vertical
													$libelle_afficher   =   $elem_col['libelle'];
													//$libelle_afficher	.=	'+++';
													$html               .= 	$libelle_afficher;
												}else{
													$html 		.= $elem_col['libelle'];
												}
												
												$html		.="</TD>\n";
											}
										}else{
											$html		.="\t\t<TD $colspan align='center'>";
											$html 		.= '&nbsp;';
											$html		.="</TD>\n";
										}
			
										$html 			.= "\n\t</TR>\n";
										
										if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
											$html 			.= "\n\t<TR>\n";	// Zone vide d'intersection des 2 dimensions
											if(count($mat_codes_colonne)){
												foreach ($mat_codes_colonne as $col => $elem_col){
													// On imprime le libellé des colonnes
													foreach($mat_liste_mes as $i_mes => $mes){
														$html		.="\t\t<TD  align='center'>";
														
														if( $vertic_aff_mesures[$i_mes] > 0){
															//A revoir affichage libellé vertical
															$libelle_afficher   =   $tab_libelles_mesures[$i_mes];
															//$libelle_afficher	.=	'+++';
															$html               .= 	$libelle_afficher;
															
														}else{
															$html		.=$tab_libelles_mesures[$i_mes];
															
														}
														$html		.="</TD>\n";
													}
												}
											}else{
												foreach($mat_liste_mes as $i_mes => $mes){
													$html		.="\t\t<TD  align='center'>".$tab_libelles_mesures[$i_mes]."</TD>\n";
												}
											}
											$max_vertic_tot_size = 0 ;

											$html 			.= "\n\t</TR>\n";
										}
										
										// traitements des lignes 
										if(count($mat_codes_ligne)){
											foreach($mat_codes_ligne as $li => $elem_li){
												$Ligne = $li;
																		
												$html 		        .= "\n\t<TR>\n";
	
														  // On imprime l'entête de ligne
												$html 		.= "\n\t\t<TD>".$elem_li['libelle']."</TD>\n";
												if(count($mat_codes_colonne)){
													foreach($mat_codes_colonne as $col => $elem_col){
														foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
														   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
															$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
															
															$html		.= get_cell_matrice(0 ,$elem_li['code'].'_'.$elem_col['code'], $elem_obj_mesures[$i_mes], $annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']], $langue, $id_systeme);
															
															$html		.= "</b></TD>\n";
														}
													}
												}
												else{ // Pas de dimension Colonne
	
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
													   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
															$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
															
															$html		.= get_cell_matrice(0 ,$elem_li['code'], $elem_obj_mesures[$i_mes], $annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']], $langue, $id_systeme);
															
															$html		.= "</b></TD>\n";
													}
																									
												}
												
												$html 		        .= "\n\t</TR>\n";
											}// Fin parcours des lignes
										} // fin si exist  dimension Ligne
										else{ // pas de dimensions lignes

												$html 		        .= "\n\t<TR>\n";
												
												$rowspan_chp_tot = '';
												$br_chp_tot = '';
												
												$html 		.= "\n\t\t<TD>&nbsp;</TD>\n";
												if(count($mat_codes_colonne)){
													foreach($mat_codes_colonne as $col => $elem_col){
														foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
														  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
															$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
															
															$html		.= get_cell_matrice(0 , $elem_col['code'], $elem_obj_mesures[$i_mes], $annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']], $langue, $id_systeme);
															
															$html		.= "</b></TD>\n";
														}
													}
												}
												
												$html 		        .= "\n\t</TR>\n";
																				
										}

										$html.= "</TABLE>";
										///////////////////////////////////////////////////////////////////////////////	
										/////Cas d'affichage vertical des mesures ////////////////////////////////////
										/////////////////////////////////////////////////////////////////////////////	
										}else{
											if ($nb_mesures > 1) {
												$rowspan =' ROWSPAN='.($nb_mesures + 1).' ';
											}else{
												$rowspan =' ';
											}
										
											if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
												$colspan =' COLSPAN=2 ';
												//$colspan =' ';
											}else{
												$colspan =' ';
											}
											
											$html 			.= "\n\t<TR>\n\t\t<TD $colspan>&nbsp</TD>\n";	// Zone vide d'intersection des 2 dimensions
											if(count($mat_codes_colonne)){
												foreach ($mat_codes_colonne as $col => $elem_col){
													// On imprime le libellé des colonnes
													$html		.="\t\t<TD align='center'>";
													$html 		.= $elem_col['libelle'];
													$html		.="</TD>\n";
												}
											}else{
												$html		.="\t\t<TD align='center'>";
												$html 		.= '&nbsp;';
												$html		.="</TD>\n";
											}
				
											$html 			.= "\n\t</TR>\n";
											
											// traitements des lignes 
											if(count($mat_codes_ligne)){
												foreach($mat_codes_ligne as $li => $elem_li){
													$Ligne = $li;
										
													$html 		        .= "\n\t<TR>\n";
		
															  // On imprime l'entête de ligne
													$html 		.= "\n\t\t<TD $rowspan valign='middle'>".$elem_li['libelle']."</TD>\n";
													$k=1;
													
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
														$html 			.= "\n\t<TR>\n";
														// On imprime le libellé des mesures
														$html		.="\t\t<TD  nowrap='nowrap' align='center' valign='middle' >".$tab_libelles_mesures[$i_mes]."</TD>\n";
																									
														if(count($mat_codes_colonne)){
															foreach($mat_codes_colonne as $col => $elem_col){
																//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
																   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																	$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
	
																	$html		.= get_cell_matrice(0 ,$elem_li['code'].'_'.$elem_col['code'], $elem_obj_mesures[$i_mes], $annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']], $langue, $id_systeme);
																	
																	$html		.= "</b></TD>\n";
																//}
															}
														}
														else{ // Pas de dimension Colonne
			
															//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
															   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																	$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
																	
																	$html		.= get_cell_matrice(0 ,$elem_li['code'], $elem_obj_mesures[$i_mes], $annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']], $langue, $id_systeme);
																	
																	$html		.= "</b></TD>\n";
															//}
														}
														
														$html 		    .= "\n\t</TR>\n";
													}
													
												}// Fin parcours des lignes
											} // fin si exist  dimension Ligne
											else{ // pas de dimensions lignes
		
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){

														$html 			.= "\n\t<TR>\n";	
														// On imprime le libellé des mesures
														$html		.="\t\t<TD $colspan nowrap='nowrap' align='center' valign='middle' >".$tab_libelles_mesures[$i_mes]."</TD>\n";
														
														$rowspan_chp_tot = '';
														$br_chp_tot = '';
														//$html 		.= "\n\t\t<TD  CLASS='".$classe_fond."'>&nbsp;</TD>\n";
														
														if(count($mat_codes_colonne)){
															foreach($mat_codes_colonne as $col => $elem_col){
																//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
																  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																	$html		.= "\t\t<TD nowrap='nowrap' align='center' valign='middle'><b>";
																	
																	$html		.= get_cell_matrice(0 , $elem_col['code'], $elem_obj_mesures[$i_mes], $annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']], $langue, $id_systeme);
																	
																	$html		.= "</b></TD>\n";
																//}
															}
														}
														
														$html 		        .= "\n\t</TR>\n";
													}								
											}
											
											$html.= "</TABLE>";
										}
									}// fin si exist des dimensions								
									
									$html.= "</TD>\n";
									$html.= "\t</TR>\n";				
							}
							elseif( ($element['TYPE_OBJET']=='liste_radio' or $element['TYPE_OBJET']=='liste_checkbox' or $element['TYPE_OBJET']=='text_valeur_multiple') && !(isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
									$pass_ligne++;
	
									if($element['TYPE_OBJET']=='liste_radio'){
											$type_objet ='radio';
											$liste_choix 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
									}
									elseif($element['TYPE_OBJET']=='liste_checkbox'){
											$type_objet ='checkbox';
											$liste_choix 	= requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
									}elseif($element['TYPE_OBJET']=='text_valeur_multiple'){
											$type_objet ='text';
											$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
											$liste_choix 	= requete_nomenclature($zone_ref['TABLE_FILLE'], $zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
										//echo '<pre>liste_choix <br>';
										//print_r($liste_choix );
									}
									
									//echo '<pre>liste_choix <br>';
									//print_r($liste_choix );
									$nb_choix		 	= count($liste_choix);
									$nb_tr 				= intval($nb_choix / 4) + 1;
									$reste_div 		= $nb_choix % 4;
									if( $reste_div > 0 ) $rowspan	=	$nb_tr;
									else $rowspan	=	$nb_tr - 1;
									//echo"<br>rowspan=$rowspan";
									$rowspan_tr = '';
									if($nb_tr > 1) $rowspan_tr = " rowspan='$rowspan' ";
									//else 
									$html.= "\t<TR>\n";
									/*$html.= "\t\t<TD $rowspan_tr valign='top'><b>&nbsp;";
									$html.= recherche_libelle_zone($element['ID_ZONE'],$langue);
									$html.= "&nbsp;</b></TD>\n";*/
									for( $tr=0; $tr<$rowspan; $tr++ ){
											if($tr > 0)
													$html.= "\t<TR>\n";
											for($j=0; $j<3; $j++){
													$ordre = 3 * $tr + $j ;
													if(isset($liste_choix[$ordre])){
															$html.= "\t\t<TD nowrap='nowrap'>&nbsp;<b>";
															//$html.= "<span class='td_left'>";
															$html.= $liste_choix[$ordre]['LIBELLE'];
															if($element['TYPE_OBJET']=='liste_radio'){
																	$name				=	$element['CHAMP_PERE']."_0"; 
															}
															elseif($element['TYPE_OBJET']=='liste_checkbox' ){
																	$name				=	$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($element['CHAMP_PERE'])];
															}elseif($element['TYPE_OBJET']=='text_valeur_multiple'){
																	$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
																	$name				=	$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($zone_ref['CHAMP_FILS'])];
															}
															
	
															$html.= "&nbsp;";
															//$html.= "VALUE=$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$element['CHAMP_PERE']].">";
															
															// Modif Alassane
															if (strlen($element['CHAMP_PERE'])>30){
																if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql'){
																	$taille_max_car =   strlen($element['CHAMP_PERE']);                                                               
																}else if ($GLOBALS['conn']->databaseType == 'postgres9') {
																	$taille_max_car=strlen($element['CHAMP_PERE']);
																}else{
																	$taille_max_car =   31;
																}
															}else{
																 $taille_max_car = strlen($element['CHAMP_PERE']);
															}                                                        
															
															if($element['TYPE_OBJET']=='text_valeur_multiple'){
																$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
																$html.= " : \$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($zone_ref['CHAMP_FILS'])]."_ANN_".$annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']];
															}else{
																$html.= " : \$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][get_champ_extract($element['CHAMP_PERE'])]."_ANN_".$annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']];
															}
															// Fin Modif Alassane
															
															//$html.= "</span>";
															$html.= "&nbsp;</b></TD>\n";
													}
													else{
															if($j==1) $cols_rest = 2;
															else $cols_rest = 1;
															$html 		.="\t\t"."<TD COLSPAN='$cols_rest'>&nbsp;</TD>\n";
															break;
													}
											}
											$html.= "\t</TR>\n";	
									}// fin for : parcours des lignes 
							}
							elseif($element['TYPE_OBJET']=='loc_etab'){ /// LOCALISATION ETABLISSEMENT
									lit_libelles_page('/questionnaire.php');
									//print_r($this->conn);
									//echo'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
									$html.= "\t<TR>\n";
									$html.= "\t\t<TD colspan=4 valign='top'>&nbsp;";
									///  données à prendre de quelque part
									$tab_chaines = get_chaines_loc($id_systeme);
									//echo '<pre>';
									//print_r($tab_chaines);
									//$tab_chaines = array();
									//$tab_chaines[] = array( 'numCh' => 2, 'nbRegs' => 4 );
									/// 
									//die();
									$html.= "<table><tr>\n";
									// pour chaque chaine mettre dans un TD
									foreach($tab_chaines as $iLoc => $chaine){
											$requete        = 'SELECT   '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', 
																		'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', 
																		'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', 
																		'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
																FROM    '.$GLOBALS['PARAM']['HIERARCHIE'].' , '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
																WHERE	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
																		'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
																		AND	'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$chaine['numCh'].'
																ORDER BY 		'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC';
											//echo $requete;
											//exit;
											$aresult = array();
											// Traitement Erreur Cas : GetAll / GetRow
											try {
													$aresult	= $GLOBALS['conn']->GetAll($requete);
													if(!is_array($aresult)){                    
															throw new Exception('ERR_SQL');  
													} 
																						
											}
											catch(Exception $e){
													$erreur = new erreur_manager($e,$requete);
											}
											// Fin Traitement Erreur Cas : GetAll / GetRow
											 
											//$nbTypeReg = count($aresult);
											$html.= "<td valign='top'><table>\n";
											/*$html.='<tr>'."\n";
											$html.='<td colspan="2" nowrap="nowrap"><b>'.recherche_libelle($chaine['numCh'],$langue,'DICO_CHAINE_LOCALISATION').'</b>&nbsp;&nbsp;&nbsp;</td>'."\n";
											$html.='</tr>'."\n";*/	
											
											$html.='<tr>'."\n";
											$html.='<td>&nbsp;</td>'."\n";
											$html.='<td nowrap="nowrap">'."\n";
										   
											$html.= "\$LOC_REG_".$iLoc;    
											$html.= "</td>\n"; 
											$html.= "</tr>\n";	
												
											foreach($aresult as $iTypeReg => $ligne_type_reg){
											$html.= "<tr>\n"; 
													$html.= "<td>".recherche_libelle($ligne_type_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']],$langue,$GLOBALS['PARAM']['TYPE_REGROUPEMENT'])."</td>\n";
													$html.= "<td>\$LIBELLE_REG_".$iLoc.'_'.$iTypeReg."</td>\n";
											$html.= "</tr>\n";												
											}
											$html.= "</table></td>\n";
									}
									$html.= "</tr></table>\n";
									$html.= "</TD>\n";
									$html.= "\t</TR>\n";
							}// FIN /// LOCALISATION ETABLISSEMENT
	
							elseif( ((trim($element['NOM_GROUPE']=='')) or (isset($tab_elem_grp[$element['NOM_GROUPE']]) and ( count($tab_elem_grp[$element['NOM_GROUPE']])==1 ) )) && (!isset($element['OBJET_MATRICIEL'])) ){
									// zone non groupée à afficher sur une ligne
									$pass_ligne++;
									
									if($element['TYPE_OBJET']<>'label'){
											$html.= "\t<TR>\n";
											$html.= "\t\t<TD colspan='4'>&nbsp;<b> : ";
											$html.= formulaire_concat_zone_html($element,$langue,$id_systeme,$annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]);
											$html.= "&nbsp;</b></TD>\n";
											//$html.= "\t\t<TD colspan='2'>&nbsp;</TD>\n";
											$html.= "\t</TR>\n";
									}
							}
															
							$html	.= "</TABLE>";
							$html 	.="</TD>";
						
						}//Fin foreach TYPE_ANNEE
			
			}// fin : si type dimension on ignore
							
			if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') && (!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)) ){
				$html 	.="</TR>";
			}
			
		} //Fin de parcours du dico
		
	$html 	.= "</TABLE>\n";
	$html .= "</div>\n";
	return $html;
}//Fin function generer_table_formulaire_hist
?>
