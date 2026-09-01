<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php lit_libelles_page('/excel_fields_row_col.php');
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
	foreach($_SESSION['curobj_theme']->tableau_zone_saisie as $nom_tab => $tab){
		foreach($tab as $chp){
			//if((isset($chp['type'])) && ($chp['type']<>'c') && ($chp['type']<>'l') && ($chp['type']<>'dim_lig') && ($chp['type']<>'dim_col') && ($chp['type']<>'loc_etab') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
			if((isset($chp['type'])) && ($chp['type']<>'c' || ($chp['type']=='c' && $nom_tab==$GLOBALS['PARAM']['ETABLISSEMENT'] && $chp['champ']==$GLOBALS['PARAM']['CODE_ETABLISSEMENT'])) 
				&& ($chp['type']<>'l') && ($chp['type']<>'dim_lig') && ($chp['type']<>'dim_col') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
			
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
					if(preg_match('/WHERE/',$chp['sql'])){
						if(preg_match('/SELECT /'.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']))
							$chp['sql'] = str_replace('WHERE','WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 AND ',$chp['sql']);
						else
							$chp['sql'] = str_replace('WHERE','WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 AND ',$chp['sql']);
					}else{
						$chp['sql'] = str_replace('ORDER BY',' WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 ORDER BY',$chp['sql']);
					}
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
					if(preg_match('/WHERE/',$chp['sql'])){
						if(preg_match('/SELECT /'.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']))
							$chp['sql'] = str_replace('WHERE','WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 AND ',$chp['sql']);
						else
							$chp['sql'] = str_replace('WHERE','WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 AND ',$chp['sql']);
					}else{
						$chp['sql'] = str_replace('ORDER BY',' WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 ORDER BY',$chp['sql']);
					}
					$tables[$nom_tab.'_'.$theme['ID']]['sql_col_dim'] = $chp['sql'];
				}
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
					if(preg_match('/WHERE/',$chp['sql'])){
						if(preg_match('/SELECT /'.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']))
							$chp['sql'] = str_replace('WHERE','WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 AND ',$chp['sql']);
						else
							$chp['sql'] = str_replace('WHERE','WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 AND ',$chp['sql']);
					}else{
						$chp['sql'] = str_replace('ORDER BY',' WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 ORDER BY',$chp['sql']);
					}
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
					if(preg_match('/WHERE/',$chp['sql'])){
						if(preg_match('/SELECT /'.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']))
							$chp['sql'] = str_replace('WHERE','WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 AND ',$chp['sql']);
						else
							$chp['sql'] = str_replace('WHERE','WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 AND ',$chp['sql']);
					}else{
						$chp['sql'] = str_replace('ORDER BY',' WHERE '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'].'<>255 ORDER BY',$chp['sql']);
					}
					$tables[$nom_tab.'_'.$theme['ID']]['sql_col_dim'] = $chp['sql'];
			}
		}
	}

	foreach($_SESSION['curobj_theme']->code_nomenclature as $nom_tab => $tab){
		foreach($tab as $zone_tab => $records){
			$records_minus_val_indet = array_diff($records, array('255'));
			if(strtoupper($zone_tab) == strtoupper($tables[$nom_tab.'_'.$theme['ID']]['id_zone_row_dim'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_row_dim'])){
				$tables[$nom_tab.'_'.$theme['ID']]['records_tab_ref_row_dim'] = $records_minus_val_indet;
				$result = $GLOBALS['conn']->GetAll($tables[$nom_tab.'_'.$theme['ID']]['sql_row_dim']);
				$res		=		array();
				if(is_array($result))
				foreach( $result as $i => $rs) {
					$res[] = $rs[get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_row_dim'])];
				}
				foreach($res as $tab){
					$tables[$nom_tab.'_'.$theme['ID']]['name_records_tab_ref_row_dim'][] = $tab;
				} 
			}
			if(strtoupper($zone_tab) == strtoupper($tables[$nom_tab.'_'.$theme['ID']]['id_zone_col_dim'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'])){
				$tables[$nom_tab.'_'.$theme['ID']]['records_tab_ref_col_dim'] = $records_minus_val_indet;
				$result = $GLOBALS['conn']->GetAll($tables[$nom_tab.'_'.$theme['ID']]['sql_col_dim']);
				$res		=		array();
				if(is_array($result))
				foreach( $result as $i => $rs) {
					$res[] = $rs[get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'])];
				}
				foreach($res as $tab){
					$tables[$nom_tab.'_'.$theme['ID']]['name_records_tab_ref_col_dim'][] = $tab;
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
	//echo "<br>tables<pre>";
	//print_r($tables);
	if(count($_POST)){
		if(isset($_POST['nb_chps']) && !isset($_POST['nb_val_dim_row']) && !isset($_POST['nb_val_dim_col'])){
			$val_row = "";	
			$premier_elt=true;	
			for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
				if( isset($_POST['ROW_'.$i]) ){
						if(!$premier_elt) $val_row .=',';
						$premier_elt=false;
						$val_row .= $_POST['ROW_'.$i] ;	
				}
			}
			$val_col = "";	
			$premier_elt=true;	
			for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
				if( isset($_POST['COL_'.$i]) ){
						if(!$premier_elt) $val_col .=',';
						$premier_elt=false;
						$val_col .= $_POST['COL_'.$i] ;	
				}
			}
			if( $val_row<>'' && $val_col<>''){			
				echo '<script type="text/Javascript">
						var iframe = window.parent.document.getElementById(\'dialog_content\');
						var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
						var inputLignes = innerDoc.getElementById(\'NUM_LIGNES\');
						var inputCols = innerDoc.getElementById(\'NUM_COLONNES\');						
						inputLignes.value=\''.$val_row.'\' ;
						inputCols.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_row==''){			
				echo '<script type="text/Javascript">
						var iframe = window.parent.document.getElementById(\'dialog_content\');
						var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
						var inputCols = innerDoc.getElementById(\'NUM_COLONNES\');	
						inputCols.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_col==''){			
				echo '<script type="text/Javascript">
						var iframe = window.parent.document.getElementById(\'dialog_content\');
						var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
						var inputLignes = innerDoc.getElementById(\'NUM_LIGNES\');
						inputLignes.value=\''.$val_row.'\' ;
						fermer();
					  </script>';
			}
		}
		if(isset($_POST['nb_val_dim_row']) && !isset($_POST['nb_val_dim_col'])){
			$val_row = "";	
			$premier_elt=true;
			for( $j = 0 ; $j < $_POST['nb_val_dim_row'] ; $j++){
				for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
					if( isset($_POST['ROW_'.$j.'_'.$i]) ){
							if(!$premier_elt) $val_row .=',';
							$premier_elt=false;
							$val_row .= $_POST['ROW_'.$j.'_'.$i] ;	
					}
				}
			}
			$val_col = "";	
			$premier_elt=true;
			for( $j = 0 ; $j < $_POST['nb_val_dim_row'] ; $j++){
				for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
					if( isset($_POST['COL_'.$j.'_'.$i]) ){
							if(!$premier_elt) $val_col .=',';
							$premier_elt=false;
							$val_col .= $_POST['COL_'.$j.'_'.$i] ;	
					}
				}
			}
			if( $val_row<>'' && $val_col<>''){			
				echo '<script type="text/Javascript">
						var iframe = window.parent.document.getElementById(\'dialog_content\');
						var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
						var inputLignes = innerDoc.getElementById(\'NUM_LIGNES\');
						var inputCols = innerDoc.getElementById(\'NUM_COLONNES\');						
						inputLignes.value=\''.$val_row.'\' ;
						inputCols.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_row==''){			
				echo '<script type="text/Javascript">
						var iframe = window.parent.document.getElementById(\'dialog_content\');
						var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
						var inputCols = innerDoc.getElementById(\'NUM_COLONNES\');	
						inputCols.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_col==''){			
				echo '<script type="text/Javascript">
						var iframe = window.parent.document.getElementById(\'dialog_content\');
						var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
						var inputLignes = innerDoc.getElementById(\'NUM_LIGNES\');
						inputLignes.value=\''.$val_row.'\' ;
						fermer();
					  </script>';
			}
		}
		if(!isset($_POST['nb_val_dim_row']) && isset($_POST['nb_val_dim_col'])){
			$val_row = "";	
			$premier_elt=true;
			for( $j = 0 ; $j < $_POST['nb_val_dim_col'] ; $j++){
				for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
					if( isset($_POST['ROW_'.$j.'_'.$i]) ){
							if(!$premier_elt) $val_row .=',';
							$premier_elt=false;
							$val_row .= $_POST['ROW_'.$j.'_'.$i] ;	
					}
				}
			}
			$val_col = "";	
			$premier_elt=true;
			for( $j = 0 ; $j < $_POST['nb_val_dim_col'] ; $j++){
				for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
					if( isset($_POST['COL_'.$j.'_'.$i]) ){
							if(!$premier_elt) $val_col .=',';
							$premier_elt=false;
							$val_col .= $_POST['COL_'.$j.'_'.$i] ;	
					}
				}
			}
			if( $val_row<>'' && $val_col<>''){			
				echo '<script type="text/Javascript">
						var iframe = window.parent.document.getElementById(\'dialog_content\');
						var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
						var inputLignes = innerDoc.getElementById(\'NUM_LIGNES\');
						var inputCols = innerDoc.getElementById(\'NUM_COLONNES\');						
						inputLignes.value=\''.$val_row.'\' ;
						inputCols.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_row==''){			
				echo '<script type="text/Javascript">
						var iframe = window.parent.document.getElementById(\'dialog_content\');
						var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
						var inputCols = innerDoc.getElementById(\'NUM_COLONNES\');	
						inputCols.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_col==''){			
				echo '<script type="text/Javascript">
						var iframe = window.parent.document.getElementById(\'dialog_content\');
						var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
						var inputLignes = innerDoc.getElementById(\'NUM_LIGNES\');
						inputLignes.value=\''.$val_row.'\' ;
						fermer();
					  </script>';
			}
		}
	}
	if(isset($_GET['lignes']) && $_GET['lignes']<>'') $lignes = explode(',',$_GET['lignes']);
	if(isset($_GET['colonnes']) && $_GET['colonnes']<>'') $colonnes = explode(',',$_GET['colonnes']);
	
?>
<br /><br /><br />
<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
 <table  border="1" align="center" width="500">
    <tr> 
        <td width="100%" align="center"><?php echo recherche_libelle_page('choix_table').' : <b>'.$_GET['nom_table']; ?></b></td>
    </tr>
	<tr><td >&nbsp;</td></tr>
	
	<?php if((($_SESSION['type_theme_excel']<>2 && $_SESSION['type_theme_excel']<>4) || ($_SESSION['curobj_theme']->type_gril_eff_fix_col)) && isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['data_entry_fields']) && !isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']) && !isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim'])){
			$entry_fields = $tables[$_GET['nom_table'].'_'.$theme['ID']]['data_entry_fields'];
	?>
	<tr><td >
        <table align="center">
				<tr> 
					<td align='center' ><?php echo "".recherche_libelle_page('nom_chp');?></td>
					<td align='center' ><?php echo "".recherche_libelle_page('form_row')."";?></td>
 					<td align='center' ><?php echo "".recherche_libelle_page('form_col')."";?></td>
				</tr>
				<?php $k = 0;
				foreach ($entry_fields as $i => $chp){
				?>
					<tr> 
						<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$i;?>" value="<?php echo $chp;?>" readonly="1"/></td>
						<td align="center" ><input style="width:75px" type="text" name="<?php echo'ROW_'.$i;?>" value="<?php if(isset($lignes[$k])) echo $lignes[$k];?>" /></td>
						<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'COL_'.$i;?>" value="<?php if(isset($colonnes[$k])) echo $colonnes[$k];?>" /></td>
					</tr>
				<?php $k++; 
				}
				?>
					
				<tr><td colspan=3 align='center'>&nbsp;</td></tr>
		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
	<?php }?>
	<?php if(($_SESSION['type_theme_excel']==2 || ($_SESSION['type_theme_excel']==4 && !$_SESSION['curobj_theme']->type_gril_eff_fix_col)) && isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['data_entry_fields']) && !isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']) && !isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim'])){
			$entry_fields = $tables[$_GET['nom_table'].'_'.$theme['ID']]['data_entry_fields'];
	?>
	<tr><td >
        <table align="center" >
			<?php if($_SESSION['type_theme_excel']==2){ ?>
				<tr> 
					<td align='center' ><?php echo "".recherche_libelle_page('nom_chp');?></td>
 					<td align='center' ><?php echo "".recherche_libelle_page('form_col')."";?></td>
				</tr>
				<?php $k = 0;
				foreach ($entry_fields as $i => $chp){
				?>
					<tr> 
						<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$i;?>" value="<?php echo $chp;?>" readonly="1"/></td>
						<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'COL_'.$i;?>" value="<?php if(isset($colonnes[$k])) echo $colonnes[$k];?>" /></td>
					</tr>
				<?php $k++; 
				}
				?>
				<tr><td colspan=3 align='center'>&nbsp;</td></tr>
			<?php }else{ ?>
				<tr> 
					<td align='center' ><?php echo "".recherche_libelle_page('nom_chp');?></td>
 					<td align='center' ><?php echo "".recherche_libelle_page('form_row')."";?></td>
				</tr>
				<?php $k = 0;
				foreach ($entry_fields as $i => $chp){
				?>
					<tr> 
						<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$i;?>" value="<?php echo $chp;?>" readonly="1"/></td>
						<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'ROW_'.$i;?>" value="<?php if(isset($lignes[$k])) echo $lignes[$k];?>" /></td>
					</tr>
				<?php $k++; 
				}
				?>
				<tr><td colspan=3 align='center'>&nbsp;</td></tr>
			<?php } ?>
		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
	<?php }?>
	<?php if(isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']) && !isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim'])){
			$entry_fields = $tables[$_GET['nom_table'].'_'.$theme['ID']]['data_entry_fields'];
	?>
	<tr><td >
		<div style="position:absolute; ; width: 100px; right: 195px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('nom_chp');?></td>
			</tr>
		</table>
		</div>
		<div style="position:absolute; ; width: 100px; right: 86px;">
		<table style="border:0">
			<tr> 
				<td align='right'><?php echo "".recherche_libelle_page('form_row')."";?></td>
			</tr>
		</table>
		</div>
		<div style="position:absolute; ; width: 100px; right: 8px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('form_col')."";?></td>
			</tr>
		</table>
		</div>
		<br/>
		<br/>
		<table border="1" align="center">
				
				<?php $k = 0;
				foreach ($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php foreach ($entry_fields as $i => $chp){
					?>
						<table  align="center">
							
							<tr> 
							<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$j.'_'.$i;?>" value="<?php echo $chp;?>" readonly="1"/></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'ROW_'.$j.'_'.$i;?>" value="<?php if(isset($lignes[$k])) echo $lignes[$k];?>" /></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'COL_'.$j.'_'.$i;?>" value="<?php if(isset($colonnes[$k])) echo $colonnes[$k];?>" /></td>
							</tr>
						</table>
					<?php $k++; 
					}
					?>
					</td>
					</tr>
				<?php }
				?>
			
		</table>
  </table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
			<input type="hidden" name="nb_val_dim_row" value="<?php echo count($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']); ?>" />
	<?php }?>
	<?php if((($_SESSION['type_theme_excel']<>2 && $_SESSION['type_theme_excel']<>4) || ($_SESSION['curobj_theme']->type_gril_eff_fix_col)) && !isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']) && isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim'])){
			$entry_fields = $tables[$_GET['nom_table'].'_'.$theme['ID']]['data_entry_fields'];
			
	?>
	<tr><td >
		
		<div style="position:absolute; ; width: 100px; right: 195px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('nom_chp');?></td>
			</tr>
		</table>
		</div>
		<div style="position:absolute; ; width: 100px; right: 86px;">
		<table style="border:0">
			<tr> 
				<td align='right'><?php echo "".recherche_libelle_page('form_row')."";?></td>
			</tr>
		</table>
		</div>
		<div style="position:absolute; ; width: 100px; right: 8px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('form_col')."";?></td>
			</tr>
		</table>
		</div>
		<br/>
		<br/>
		<table border="1" align="center">
				
				<?php $k=0;
				foreach ($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php foreach ($entry_fields as $i => $chp){
					?>
						<table align="center">
							
							<tr> 
							<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$j.'_'.$i;?>" value="<?php echo $chp;?>" readonly="1" /></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'ROW_'.$j.'_'.$i;?>" value="<?php if(isset($lignes[$k])) echo $lignes[$k];?>" /></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'COL_'.$j.'_'.$i;?>" value="<?php if(isset($colonnes[$k])) echo $colonnes[$k];?>" /></td>
							</tr>
						</table>
					<?php $k++;
					}
					?>
					</td>
					</tr>
				<?php }
				?>
			
	  </table>
		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
			<input type="hidden" name="nb_val_dim_col" value="<?php echo count($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim']); ?>" />
	<?php }?>
	<?php if(($_SESSION['type_theme_excel']==2 || ($_SESSION['type_theme_excel']==4 && !$_SESSION['curobj_theme']->type_gril_eff_fix_col)) && !isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']) && isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim'])){
			$entry_fields = $tables[$_GET['nom_table'].'_'.$theme['ID']]['data_entry_fields'];
			
	?>
	<tr><td >
		
		<div style="position:absolute; ; width: 100px; right: 150px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('nom_chp');?></td>
			</tr>
		</table>
		</div>
		<?php if($_SESSION['type_theme_excel']==2){ ?>
		<div style="position:absolute; ; width: 100px; right: 48px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('form_col')."";?></td>
			</tr>
		</table>
		</div>
		<br/>
		<br/>
		<table border="1" align="center">
				
				<?php $k=0;
				foreach ($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php foreach ($entry_fields as $i => $chp){
					?>
						<table align="center">
							
							<tr> 
							<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$j.'_'.$i;?>" value="<?php echo $chp;?>" readonly="1"/></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'COL_'.$j.'_'.$i;?>" value="<?php if(isset($colonnes[$k])) echo $colonnes[$k];?>" /></td>
							</tr>
						</table>
					<?php $k++;
					}
					?>
					</td>
					</tr>
				<?php }
				?>
	  </table>
	  </table>
	</td>
	</tr>
		<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
		<input type="hidden" name="nb_val_dim_col" value="<?php echo count($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim']); ?>" />
	  <?php }else{ ?>
	  <div style="position:absolute; ; width: 100px; right: 48px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('form_row')."";?></td>
			</tr>
		</table>
		</div>
		<br/>
		<br/>
		<table border="1" align="center">
				
				<?php $k=0;
				foreach ($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php foreach ($entry_fields as $i => $chp){
					?>
						<table align="center">
							
							<tr> 
							<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$j.'_'.$i;?>" value="<?php echo $chp;?>" readonly="1"/></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'ROW_'.$j.'_'.$i;?>" value="<?php if(isset($lignes[$k])) echo $lignes[$k];?>" /></td>
							</tr>
						</table>
					<?php $k++;
					}
					?>
					</td>
					</tr>
				<?php }	?>
			
	  </table>	
	  </table>
	</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
			<input type="hidden" name="nb_val_dim_row" value="<?php echo count($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']); ?>" />
	  <?php } ?>
	<?php }?>
	<?php if(isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']) && isset($_GET['dim']) && $_GET['dim']=='dim_row'){
			$entry_fields = $tables[$_GET['nom_table'].'_'.$theme['ID']]['data_entry_fields'];
			
	?>
	<tr><td >
		
		<div style="position:absolute; ; width: 100px; right: 150px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('nom_chp');?></td>
			</tr>
		</table>
		</div>
		<div style="position:absolute; ; width: 100px; right: 42px;">
		<table style="border:0">
			<tr> 
				<td align='right'><?php echo "".recherche_libelle_page('form_row')."";?></td>
			</tr>
		</table>
		</div>
		<br/>
		<br/>
		<table border="1" align="center">
				
				<?php $k=0;
				foreach ($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php foreach ($entry_fields as $i => $chp){
					?>
						<table align="center">
							
							<tr> 
							<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$j.'_'.$i;?>" value="<?php echo $chp;?>" readonly="1" /></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'ROW_'.$j.'_'.$i;?>" value="<?php if(isset($lignes[$k])) echo $lignes[$k];?>" /></td>
							</tr>
						</table>
					<?php $k++; 
					}
					?>
					</td>
					</tr>
				<?php }
				?>
			
	  </table>
		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
			<input type="hidden" name="nb_val_dim_row" value="<?php echo count($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_row_dim']); ?>" />
	<?php }?>
	<?php if(isset($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim']) && isset($_GET['dim']) && $_GET['dim']=='dim_col'){
			$entry_fields = $tables[$_GET['nom_table'].'_'.$theme['ID']]['data_entry_fields'];
			
	?>
	<tr><td >
		
		<div style="position:absolute; ; width: 100px; right: 150px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('nom_chp');?></td>
			</tr>
		</table>
		</div>
		<div style="position:absolute; ; width: 100px; right: 48px;">
		<table style="border:0">
			<tr> 
				<td align='right' ><?php echo "".recherche_libelle_page('form_col')."";?></td>
			</tr>
		</table>
		</div>
		<br/>
		<br/>
		<table border="1" align="center">
				
				<?php $k=0;
				foreach ($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php foreach ($entry_fields as $i => $chp){
					?>
						<table align="center">
							
							<tr> 
							<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$j.'_'.$i;?>" value="<?php echo $chp;?>" readonly="1"/></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'COL_'.$j.'_'.$i;?>" value="<?php if(isset($colonnes[$k])) echo $colonnes[$k];?>" /></td>
							</tr>
						</table>
					<?php $k++;
					}
					?>
					</td>
					</tr>
				<?php }
				?>
			
	  </table>
		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
			<input type="hidden" name="nb_val_dim_col" value="<?php echo count($tables[$_GET['nom_table'].'_'.$theme['ID']]['name_records_tab_ref_col_dim']); ?>" />
	<?php }?>
</table>
<br/>
<table align="center" border="1" width="50%" >
	<tr> 
		<td align='center' nowrap="nowrap">
			<INPUT   style="width:50%;"  type='submit' name='Input' <?php echo 'value="'.recherche_libelle_page('submit').'"';?>>&nbsp;&nbsp;&nbsp;
			<INPUT   style="width:45%;"  type="button" <?php echo 'value="'.recherche_libelle_page('fermer').'"';?> onClick="javascript:fermer();">
		</td>
	</tr>
</table>
</FORM>

