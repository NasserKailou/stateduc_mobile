<script type=text/javascript>
 <!-- 

	 function AvertirSupp_D_T(table, theme, type_theme, secteur){ 
	 	//alert(table+'_'+theme+'_'+type_theme+'_'+secteur);
		var TxtAvert ="<?php echo recherche_libelle(200,$_SESSION['langue'],'DICO_MESSAGE') ?>"; 
		if(confirm(TxtAvert)){ 
			location.href   = '?val=OpenPopupExcelTables&table='+table+'&theme='+theme+'&type_theme='+type_theme+'&secteur='+secteur+'&supp_dt=1'
		} 
	 } 

	function MessMAJ(action,res_action){ 

		if(action=='AddZone') i = 0; 
		else if(action=='UpdZone') i = 1;
		else if(action=='SupZone') i = 2;								
		//if(lib_champ_err != '') lib_champ = ' : (' + lib_champ_err + ')';	
						
		OK00 = " <?php echo recherche_libelle(206,$_SESSION['langue'],'DICO_MESSAGE') ?> "; 
		OK01 = " <?php echo recherche_libelle(203,$_SESSION['langue'],'DICO_MESSAGE') ?> "; 
		OK10 = " <?php echo recherche_libelle(207,$_SESSION['langue'],'DICO_MESSAGE') ?> "; 
		OK11 = " <?php echo recherche_libelle(204,$_SESSION['langue'],'DICO_MESSAGE') ?> "; 
		OK20 = " <?php echo recherche_libelle(208,$_SESSION['langue'],'DICO_MESSAGE') ?> "; 
		OK21 = " <?php echo recherche_libelle(205,$_SESSION['langue'],'DICO_MESSAGE') ?> ";

		var OK	= Array([OK00,OK01],[OK10,OK11],[OK20,OK21]); 
		alert(OK[i][res_action]);
	}
 //--> 
</script>

<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php ///// fonction de récupération des zones du theme
		function get_donnees_table(){
				
				if(isset($_GET['table'])){
					$_SESSION['table'] = $_GET['table'];
					$_SESSION['theme_excel'] = $_GET['theme'];
					$_SESSION['type_theme_excel'] = $_GET['type_theme'];
				}
				$conn 		= $GLOBALS['conn_dico'];
				
				$requete	="	SELECT *
								FROM  DICO_EXCEL_TABLE
								WHERE NOM_TABLE = '".$_SESSION['table']."' 
								AND ID_THEME = ".$_SESSION['theme_excel']."
								AND ID_SYSTEME = ".$_SESSION['secteur'];
				//echo $requete ;			
				$all_res = $GLOBALS['conn_dico']->GetAll($requete);
				$_SESSION['donnees_table'] = array();
				$_SESSION['donnees_table'] = $all_res[0];
				
		} //FIN get_zones()
	
		function recherche_libelle($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					$conn  =   $GLOBALS['conn'];
				} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					$conn =   $GLOBALS['conn_dico']; 
				}
				$requete = "SELECT LIBELLE
							FROM DICO_TRADUCTION 
							WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
							AND NOM_TABLE='".$table."'";
				
				//echo $requete;
				//print_r($conn);
				$all_res	= $conn->GetAll($requete); 
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
		
		function set_champs_donnees_table(){///// Préparation des champs de DICO_ZONE 
				if(!($GLOBALS['champs_donnees_table'])){
					$GLOBALS['champs_donnees_table'] = array();
					$GLOBALS['champs_donnees_table'][] 	= array( 'nom_champ' => 'NOM_TABLE', 'type_champ' => 'text'	, 'manip' => false);
					$GLOBALS['champs_donnees_table'][] 	= array( 'nom_champ' => 'ID_THEME', 'type_champ' => 'int' , 'manip' => false);
					$GLOBALS['champs_donnees_table'][] 	= array( 'nom_champ' => 'ID_SYSTEME', 'type_champ' => 'int', 'manip' => false);
					$GLOBALS['champs_donnees_table'][]	= array( 'nom_champ' => 'NOM_PAGE', 'type_champ' => 'text', 'manip' => true);
					$GLOBALS['champs_donnees_table'][]	= array( 'nom_champ' => 'TYPE_THEME', 'type_champ' => 'int', 'manip' => false);
					$GLOBALS['champs_donnees_table'][] 	= array( 'nom_champ' => 'NUM_LIGNE_DEBUT', 'type_champ' => 'text', 'manip' => true);
					$GLOBALS['champs_donnees_table'][] 	= array( 'nom_champ' => 'NUM_COLONNE_DEBUT', 'type_champ' => 'text', 'manip' => true);
					$GLOBALS['champs_donnees_table'][] 	= array( 'nom_champ' => 'NUM_LIGNES', 'type_champ' => 'text', 'manip' => true);
					$GLOBALS['champs_donnees_table'][]	= array( 'nom_champ' => 'NUM_COLONNES', 'type_champ' => 'text', 'manip' => true);
				}
		}

		function MAJ_DonneesTable(){ /// Mise à jour donnees 
				$conn = $GLOBALS['conn_dico'];
				$GLOBALS['OkAction'] 	= 1;
				$GLOBALS['post'] = 1;
				
				$req_exist =   "SELECT *
								FROM  DICO_EXCEL_TABLE
								WHERE NOM_TABLE = '".$_SESSION['table']."' 
								AND ID_THEME = ".$_SESSION['theme_excel']."
								AND ID_SYSTEME = ".$_SESSION['secteur'];
				$exists 	= $GLOBALS['conn_dico']->GetAll($req_exist);

				if( is_array($exists) and (count($exists) > 0) ){
						// UPDATE
						$GLOBALS['Action']		= 'UpdZone';
				}else{
						// INSERT
						$GLOBALS['Action']		= 'AddZone';
				}

				foreach($GLOBALS['champs_donnees_table'] as $champ){
						if( (trim($_POST[$champ['nom_champ']]) <> '') and ($champ['manip'] == true) and ($champ['type_champ'] == 'int') ){
								if (!preg_match ("/^(0|([1-9][0-9]*))$/", trim($_POST[$champ['nom_champ']]))){
										$GLOBALS['OkAction'] 	= 0;
										break;
								}
						}
				}
				
				if( $GLOBALS['OkAction'] <> 0 ){

						if( is_array($exists) and (count($exists) > 0) ){
								// UPDATE
								$sql = 'UPDATE    DICO_EXCEL_TABLE SET '."\n";
								$virg = false;
								foreach($GLOBALS['champs_donnees_table'] as $i => $champ){
									if( $champ['manip'] == true ){
										if($virg == true){
												$sql .= ', '."\n";
										}
										$virg = true;
										if(trim($_POST[$champ['nom_champ']]) == ''){
												$sql .= $champ['nom_champ'].'='."NULL";
										}else{
												if($champ['type_champ']=='int'){
														$sql .= $champ['nom_champ'] . '=' . trim($_POST[$champ['nom_champ']]);
												}
												elseif($champ['type_champ']=='text'){
														$sql .= $champ['nom_champ'] . '=' . $conn->qstr(trim($_POST[$champ['nom_champ']]));
												}
										}
									}
									if($champ['nom_champ']=='TYPE_THEME'){
										$sql .= ', '."\n";
										$sql .= $champ['nom_champ'] . '=' . $_SESSION['type_theme_excel'];
									}
								}
								$sql .= "\n";
								$requete = $sql . "	WHERE NOM_TABLE = '".$_SESSION['table']."' 
													AND ID_THEME = ".$_SESSION['theme_excel']."
													AND ID_SYSTEME = ".$_SESSION['secteur'];
								}
						else{
								// INSERT
								$tab_req = array();
								$tab_req[] = array( 'champ' => 'NOM_TABLE', 'val' => $_SESSION['table']	,'type' => 'text');
								$tab_req[] = array( 'champ' => 'ID_THEME' , 'val' => $_SESSION['theme_excel']	,'type' => 'int');
								$tab_req[] = array( 'champ' => 'ID_SYSTEME'	, 'val' => $_SESSION['secteur']	,'type' => 'int');
								$tab_req[] = array( 'champ' => 'TYPE_THEME'	, 'val' => $_SESSION['type_theme_excel']	,'type' => 'int');
								foreach($GLOBALS['champs_donnees_table'] as $champ){
										if( (trim($_POST[$champ['nom_champ']]) <> '') and ($champ['manip'] == true) ){
												$tab_req[] = array( 'champ' => $champ['nom_champ'], 'val' => $_POST[$champ['nom_champ']], 'type' => $champ['type_champ']	);
										}
								}
								$sql_champs = 'INSERT INTO DICO_EXCEL_TABLE (';
								$sql_values = 'VALUES (';
								
								foreach($tab_req as $i => $tab){
										if($i>0){
												$sql_champs .= ', ';
												$sql_values .= ', ';
										}
										$sql_champs .= $tab['champ'];
										if($tab['type']=='int'){
												$sql_values .= trim($tab['val']);
										}
										elseif($tab['type']=='text'){
												$sql_values .=  $conn->qstr(trim($tab['val']));
										}
								}
								$sql_champs .= ') ';
								$sql_values .= ') ';
								
								$requete = $sql_champs . $sql_values ;
						}
				}

				if ($GLOBALS['conn_dico']->Execute($requete) === false){
						$GLOBALS['OkAction'] 	= 0;
						echo $requete.'<br>';
				}
		}

		function AlerteMAJ(){
			if($GLOBALS['post'] == 1){ // si on est aprés soumission
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("MessMAJ('".$GLOBALS['Action']."', ".$GLOBALS['OkAction']."); \n");
				if( ($GLOBALS['OkAction'] == 1) && ($GLOBALS['Action']<>'SupZone') ){
					print("fermer(); \n");
				}
				print("\t //--> \n");
				print("</script>\n");
			}
		}
		
		function Alerter($mess){
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("alert(\"$mess\"); \n");
				print("\t //--> \n");
				print("</script>\n");
		}
		
		if( $_POST['ActionZoneSyst'] == '1'){ // Actions 
				$GLOBALS['ActionPost'] = 1;
				get_donnees_table();
				set_champs_donnees_table();
				MAJ_DonneesTable();
				
				$TabValDonneesTable =	array();
				foreach($GLOBALS['champs_donnees_table'] as $i => $elem){
					if( $elem['manip']==true ){
						$TabValDonneesTable[$elem['nom_champ']] = $_POST[$elem['nom_champ']];
					}
				}
		}// FIN Actions 
		else{
				if( isset($_GET['supp_dt']) && trim($_GET['supp_dt']) <> ''){
					$GLOBALS['OkAction'] 	= 1;
					$GLOBALS['Action']		= 'SupZone';
					$GLOBALS['post'] 		= 1;
					$requete 	= " DELETE FROM DICO_EXCEL_TABLE  WHERE  NOM_TABLE = '".$_GET['table']."'
									AND ID_THEME = ".$_SESSION['theme_excel']."
									AND ID_SYSTEME = ".$_SESSION['secteur'];
					if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$GLOBALS['OkAction'] 	= 0;
					if ($GLOBALS['OkAction'] == 0) echo $requete.'<br>';
				}
				
				get_donnees_table();
				set_champs_donnees_table();
				$TabValDonneesTable = $_SESSION['donnees_table'];
				
		}

		lit_libelles_page('/gestion_excel_tables.php');
		include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
		if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
		if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
		if(!isset($_SESSION['code_etab']) || $_SESSION['code_etab']==''){
			$_SESSION['code_etab'] = 0;
		}
		$code_etablissement = $_SESSION['code_etab'];
		$code_annee = $_SESSION['annee'];
		$code_filtre = $_SESSION['filtre'];
		$id_systeme	= $_SESSION['secteur'];
		
		$requete  = "SELECT DICO_THEME.* 
					 FROM DICO_THEME 
					 WHERE DICO_THEME.ID = ".$_SESSION['theme_excel'];
		$result_theme	= $GLOBALS['conn_dico']->GetAll($requete);
		$theme = $result_theme[0]; 
		$tables = array();
		$curr_inst	= $theme['ACTION_THEME'];									
		$id_theme =	$theme['ID'];    
		if (file_exists($GLOBALS['SISED_PATH_CLS'] . 'metier/'.$theme['CLASSE'].'.class.php')) {
			require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/'.$theme['CLASSE'].'.class.php'; 
		}
		switch($curr_inst){
			case 'instance_grille.php' :{
					// Instanciation de la classe
					$curobj_grille		=	new grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
					// chargement des codes des nomenclatures des champs de type matrice
					$curobj_grille->set_code_nomenclature();
					$_SESSION['curobj_theme'] = $curobj_grille;
					break;
			}	
			case 'instance_mat_grille.php' :{
					// Instanciation de la classe
					$curobj_matgrille = new mat_grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
					// chargement des codes des nomenclatures des champs de type matrice
					$curobj_matgrille->set_code_nomenclature();
					// Récupération des différents champs
					$curobj_matgrille->set_champs();
					// Sauvegarde de l'objet en session
					$_SESSION['curobj_theme'] = $curobj_matgrille;
					break;
			}
			case 'instance_matrice.php' :{
					// Instanciation de la classe
					$curobj_matrice	=	new matrice($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
					$_SESSION['curobj_theme'] = $curobj_matrice;
					break;
			}
		}
		//echo "curobj_theme<pre>";
		//print_r($_SESSION['curobj_theme']);
		if($_SESSION['curobj_theme']->type_theme <> 4 || $_SESSION['curobj_theme']->type_gril_eff_fix_col){
			foreach($_SESSION['curobj_theme']->tableau_zone_saisie as $nom_tab => $tab){
				foreach($tab as $chp){
					if((isset($chp['type'])) && ($chp['type']<>'c') && ($chp['type']<>'l') && ($chp['type']<>'dim_lig') && ($chp['type']<>'dim_col') && ($chp['type']<>'loc_etab') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
						$tables[$nom_tab.'_'.$theme['ID']]['data_entry_fields'][] = $chp['champ'];
					}
					if(($chp['type']=='dim_col') || ($chp['type']=='dim_lig')){
						if(($chp['type']=='dim_lig')){
							$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_row_dim'] = $chp['table_ref'];
							$tables[$nom_tab.'_'.$theme['ID']]['id_zone_row_dim'] = $chp['id_zone'];
							$chp['sql'] = strtoupper($chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
							$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
							$tables[$nom_tab.'_'.$theme['ID']]['sql_row_dim'] = $chp['sql'];
						}
						if(($chp['type']=='dim_col')){
							$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $chp['table_ref'];
							$tables[$nom_tab.'_'.$theme['ID']]['id_zone_col_dim'] = $chp['id_zone'];
							$chp['sql'] = strtoupper($chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
							$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
							$tables[$nom_tab.'_'.$theme['ID']]['sql_col_dim'] = $chp['sql'];
						}
						if(($chp['type']=='tvm')){
							$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $chp['table_ref'];
							$tables[$nom_tab.'_'.$theme['ID']]['id_zone_col_dim'] = $chp['id_zone'];
							$chp['sql'] = strtoupper($chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
							$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
							$tables[$nom_tab.'_'.$theme['ID']]['sql_col_dim'] = $chp['sql'];
						}
						if(($chp['type']=='li_ch')){
							$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $chp['table_ref'];
							$tables[$nom_tab.'_'.$theme['ID']]['id_zone_col_dim'] = $chp['id_zone'];
							$chp['sql'] = strtoupper($chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
							$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
							$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
							$tables[$nom_tab.'_'.$theme['ID']]['sql_col_dim'] = $chp['sql'];
						}
					}
				}
			}
			
			foreach($_SESSION['curobj_theme']->code_nomenclature as $nom_tab => $tab){
				foreach($tab as $zone_tab => $records){
					$records_minus_val_indet = array_diff($records, array('255'));
					if(strtoupper($zone_tab) == strtoupper($tables[$nom_tab.'_'.$theme['ID']]['id_zone_row_dim'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_row_dim'])){
						$tables[$nom_tab.'_'.$theme['ID']]['records_tab_ref_row_dim'] = $records_minus_val_indet;
						$res = $GLOBALS['conn']->GetAll($tables[$nom_tab.'_'.$theme['ID']]['sql_row_dim']);
						foreach($res as $tab){
							$tables[$nom_tab.'_'.$theme['ID']]['name_records_tab_ref_row_dim'][] = $tab[$GLOBALS['PARAM']['LIBELLE'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_row_dim']];
						} 
					}
					if(strtoupper($zone_tab) == strtoupper($tables[$nom_tab.'_'.$theme['ID']]['id_zone_col_dim'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'])){
						$tables[$nom_tab.'_'.$theme['ID']]['records_tab_ref_col_dim'] = $records_minus_val_indet;
						$res = $GLOBALS['conn']->GetAll($tables[$nom_tab.'_'.$theme['ID']]['sql_col_dim']);
						foreach($res as $tab){
							$tables[$nom_tab.'_'.$theme['ID']]['name_records_tab_ref_col_dim'][] = $tab[$GLOBALS['PARAM']['LIBELLE'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim']];
						}
					}
				}
			}
			
			//cas de grille_eff : recherche dimension colonne
			if(($_SESSION['curobj_theme']->type_theme == 4)){
				$curobj_theme = $_SESSION['curobj_theme'];
				foreach($_SESSION['curobj_theme']->nomtableliee as $nom_tab){
					if(!isset($tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'])){
						foreach($curobj_theme->nomtableliee as $tab){
							if(isset($tables[$tab.'_'.$theme['ID']]['tab_ref_col_dim'])){
								$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $tables[$tab.'_'.$theme['ID']]['tab_ref_col_dim'];
								$tables[$nom_tab.'_'.$theme['ID']]['records_tab_ref_col_dim'] = $tables[$tab.'_'.$theme['ID']]['records_tab_ref_col_dim'];
								$tables[$nom_tab.'_'.$theme['ID']]['name_records_tab_ref_col_dim'] = $tables[$tab.'_'.$theme['ID']]['name_records_tab_ref_col_dim'];
							}
						}
					}
				}
			}
		}
		
?>
<body>

<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
  <span style="position:absolute; width:520px; height:150px; left: 15px; overflow: 0; top: 46px;" class="classe_table"> 

    <div  style="position:absolute; top: 16px; left: 15px;"> 
        <table><tr><td>
				<?php echo recherche_libelle_page('NomTable'); 
				?>
				<b>
				<?php echo $_SESSION['table'];
				?>
 				</b>
				</td>
				</tr></table>
	</div>
				
    <div style="position:absolute; top: 175px; width: 520px; z-index: 5"> 
        <table width="100%"><tr>
                <INPUT  type='hidden' id='ActionZoneSyst' name='ActionZoneSyst' value='1'>
				<td width="40%">  
                    <INPUT style="width:100%" type='submit' name='Input' <?php echo 'value="'.recherche_libelle_page('Enrgstr').'"';?>>                </td>
				<?php if(count($TabValDonneesTable) > 0){ ?>
				<td width="30%">  
                    <INPUT style="width:100%" type="button" name='Input' <?php echo 'value="'.recherche_libelle_page('Supprimer').'"';?>
					<?php echo "onClick=\"javascript:AvertirSupp_D_T('".$_SESSION['table']."',".$_SESSION['theme_excel'].",".$_SESSION['type_theme_excel'].",".$_SESSION['secteur'].");\""; ?>>                </td>
				<?php } ?>
				<td  width="30%">
				<INPUT  style="width:100%" type="button" <?php echo "value=\"".recherche_libelle_page('ClsWind')."\"";?> onClick="javascript:fermer();">				</td>		</tr></table>
  </div>
		<div  style="position:absolute; top: 40px; width: 93px; height: 93px; left: 15px; z-index: 3;"> 
        <table>
			<tr> 
                <td nowrap><?php echo recherche_libelle_page('NomPage');?></td>
                <td><INPUT type="text" size="15" name="NOM_PAGE" value="<?php echo $TabValDonneesTable['NOM_PAGE']; ?>"></td>
			<?php
			//verification table mère avec zone filtre
			$requete= "SELECT CHAMP_PERE FROM DICO_ZONE WHERE ID_THEME=".$_SESSION['theme_excel']." AND TABLE_MERE='".$_SESSION['table']."'";
			$result=$GLOBALS['conn_dico']->GetAll($requete);
			$tab_avec_filtre=false;
			if(is_array($result)){
				foreach($result as $rs){
					if($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']==$rs['CHAMP_PERE']){
						$tab_avec_filtre=true;
						break;
					}
				}
			}
			if($tab_avec_filtre){
			?>
				<td id="btn_filtre_val"><input name="button" type="button" style="height:22px; width:150px" onClick="OpenPopupFiltreVal()" value="<?php echo recherche_libelle_page('def_filtre_val');?>" /></td>
          	<?php }	?>
		    </tr>
			<?php if($_SESSION['type_theme_excel']==2 || ($_SESSION['type_theme_excel']==4 && !$_SESSION['curobj_theme']->type_gril_eff_fix_col)){
			?>
            <tr> 
               	<?php if($_SESSION['type_theme_excel']==2){ ?>
					<td nowrap><?php echo recherche_libelle_page('LigDeb');?></td>
					<td><INPUT type="text" size="4" name="NUM_LIGNE_DEBUT" value="<?php echo $TabValDonneesTable['NUM_LIGNE_DEBUT']; ?>"></td>
				<?php }else{ ?>	
					<td nowrap><?php echo recherche_libelle_page('ColDeb');?></td>
					<td><INPUT type="text" size="4" name="NUM_COLONNE_DEBUT" value="<?php echo $TabValDonneesTable['NUM_COLONNE_DEBUT']; ?>"></td>
				<?php } ?>		                
				
				
            </tr>
			<?php }
			?>
			<?php if(!(isset($tables[$_SESSION['table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']) && isset($tables[$_SESSION['table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim']))){
			?>
			<?php if(($_SESSION['type_theme_excel']<>2 && $_SESSION['type_theme_excel']<>4) || ($_SESSION['curobj_theme']->type_gril_eff_fix_col)){
			?>
			<tr> 
                <td nowrap><?php echo recherche_libelle_page('Lignes');?></td>
                <td nowrap>
				<INPUT type="text" size="30" id="NUM_LIGNES" name="NUM_LIGNES" value="<?php echo $TabValDonneesTable['NUM_LIGNES']; ?>" style="background-color:#CCCCCC" readonly="1">
				</td>
				<td nowrap rowspan="2" style="vertical-align:middle">
				<input name="button" type="button" style="height:45px;"  onclick="OpenPopupFieldsRowCol(<?php echo '\''.$_SESSION['table'].'\',\'\''; ?>,NUM_LIGNES.value,NUM_COLONNES.value)" value="<?php echo recherche_libelle_page('def_numbers');?>" />
				</td>
            </tr>
			<tr>
				<td nowrap><?php echo recherche_libelle_page('Colonnes');?></td>
                <td><INPUT type="text" size="30" id="NUM_COLONNES" name="NUM_COLONNES" value="<?php echo $TabValDonneesTable['NUM_COLONNES']; ?>" style="background-color:#CCCCCC" readonly="1"></td>
			</tr>
			<?php }	?>
            <tr> 
               <?php if($_SESSION['type_theme_excel']==2 || ($_SESSION['type_theme_excel']==4 && !$_SESSION['curobj_theme']->type_gril_eff_fix_col)){
				if($_SESSION['type_theme_excel']==2){ ?>
				<td nowrap><?php echo recherche_libelle_page('Colonnes');?></td>
                <td><INPUT type="text" size="30" id="NUM_COLONNES" name="NUM_COLONNES" value="<?php echo $TabValDonneesTable['NUM_COLONNES']; ?>" style="background-color:#CCCCCC" readonly="1"></td>
				<td nowrap style="vertical-align:middle">
				<input name="button" type="button" style="height:22px; width:145px"  onclick="OpenPopupFieldsRowCol(<?php echo '\''.$_SESSION['table'].'\',\'\''; ?>,'',NUM_COLONNES.value)" value="<?php echo recherche_libelle_page('def_col_numbers');?>" />
				</td>
				<?php }else{ ?>
				<td nowrap><?php echo recherche_libelle_page('Lignes');?></td>
                <td><INPUT type="text" size="30" id="NUM_LIGNES" name="NUM_LIGNES" value="<?php echo $TabValDonneesTable['NUM_LIGNES']; ?>" style="background-color:#CCCCCC" readonly="1"></td>
				<td nowrap style="vertical-align:middle">
				<input name="button" type="button" style="height:22px; width:145px"  onclick="OpenPopupFieldsRowCol(<?php echo '\''.$_SESSION['table'].'\',\'\''; ?>,NUM_LIGNES.value,'')" value="<?php echo recherche_libelle_page('def_lig_numbers');?>" />
				<?php }	?>
				<?php }
				?>
			</tr>
			<?php }else{ ?>
			
			<tr> 
                <td nowrap><?php echo recherche_libelle_page('Lignes');?></td>
                <td nowrap>
				<INPUT type="text" size="30" id="NUM_LIGNES" name="NUM_LIGNES" value="<?php echo $TabValDonneesTable['NUM_LIGNES']; ?>" style="background-color:#CCCCCC" readonly="1">
				</td>
				<td nowrap style="vertical-align:middle">
				<input name="button" type="button" style="height:22px; width:150px"  onclick="OpenPopupFieldsRowCol(<?php echo '\''.$_SESSION['table'].'\',\'dim_row\''; ?>,NUM_LIGNES.value,NUM_COLONNES.value)" value="<?php echo recherche_libelle_page('def_row_numbers');?>" />
				</td>
            </tr>
			
            <tr> 
                <td nowrap><?php echo recherche_libelle_page('Colonnes');?></td>
                <td><INPUT type="text" size="30" id="NUM_COLONNES" name="NUM_COLONNES" value="<?php echo $TabValDonneesTable['NUM_COLONNES']; ?>" style="background-color:#CCCCCC" readonly="1"></td>
           		<td nowrap style="vertical-align:middle">
				<input name="button" type="button" style="height:22px; width:150px"  onclick="OpenPopupFieldsRowCol(<?php echo '\''.$_SESSION['table'].'\',\'dim_col\''; ?>,NUM_LIGNES.value,NUM_COLONNES.value)" value="<?php echo recherche_libelle_page('def_col_numbers');?>" />
				</td>
		    </tr>
			<?php }
			?>
        </table>
				
		</div>
		
  </span>
</FORM>
</body>
<?php AlerteMAJ();
?>


