<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php lit_libelles_page('/excel_fields_row_col.php');
	
	if(isset($_GET['nom_table'])){
		$_SESSION['table'] = $_GET['nom_table'];
	}

	$tables = array();
	$requete 	=	' SELECT * 
						FROM DICO_AGGREGATED_TABLE
						WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND NOM_TABLE_2 =\''.$_SESSION['table'].'\'';
	$tab  = $GLOBALS['conn_dico']->GetAll($requete);
	
	if(is_array($tab) && count($tab)>0){
		$requete 	=	' SELECT * 
							FROM DICO_AGGREGATED_FIELD
							WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_TABLE =\''.$tab[0]['NOM_TABLE'].'\' AND EXPORT = 1 
							ORDER BY ORDRE';
		$fields  = $GLOBALS['conn_dico']->GetAll($requete);
		
		if(is_array($fields))
		foreach($fields as $chp){
			if(!preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$chp['NOM_CHAMP_2']))
				$tables[$_SESSION['table']]['data_entry_fields'][] = $chp['NOM_CHAMP_2'];
		}
		
		if($tab[0]['SQL_ROW']<>''){
			$sql_row['sql'] = $tab[0]['SQL_ROW'];
			if(preg_match('/WHERE/',$sql_row['sql'])){
				if(preg_match('/SELECT /'.$tab[0]['TABLE_REF_ROW'].'.'.$GLOBALS['PARAM']['LIBELLE'],$sql_row['sql']))
					$sql_row['sql'] = str_replace('WHERE','WHERE '.$tab[0]['TABLE_REF_ROW'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$tab[0]['TABLE_REF_ROW'].'<>255 AND ',$sql_row['sql']);
				else
					$sql_row['sql'] = str_replace('WHERE','WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$tab[0]['TABLE_REF_ROW'].'<>255 AND ',$sql_row['sql']);
			}else{
				$sql_row['sql'] = str_replace('ORDER BY',' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$tab[0]['TABLE_REF_ROW'].'<>255 ORDER BY',$sql_row['sql']);
			}
			$tables[$_SESSION['table']]['sql_row_dim'] = $sql_row['sql'];
			$result = $GLOBALS['conn']->GetAll($tables[$_SESSION['table']]['sql_row_dim']);
			$res		=		array();
			if(is_array($result))
			foreach( $result as $i => $rs) {
				$res[] = $rs[get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$tab[0]['TABLE_REF_ROW'])];
			}
			if(is_array($res))
			foreach($res as $tb){
				$tables[$_SESSION['table']]['name_records_tab_ref_row_dim'][] = $tb;
			} 
		}
		if($tab[0]['SQL_COL']<>''){
			$sql_col['sql'] = $tab[0]['SQL_COL'];
			if(preg_match('/WHERE/',$sql_col['sql'])){
				if(preg_match('/SELECT /'.$tab[0]['TABLE_REF_COL'].'.'.$GLOBALS['PARAM']['LIBELLE'],$sql_col['sql']))
					$sql_col['sql'] = str_replace('WHERE','WHERE '.$tab[0]['TABLE_REF_COL'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$tab[0]['TABLE_REF_COL'].'<>255 AND ',$sql_col['sql']);
				else
					$sql_col['sql'] = str_replace('WHERE','WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$tab[0]['TABLE_REF_COL'].'<>255 AND ',$sql_col['sql']);
			}else{
				$sql_col['sql'] = str_replace('ORDER BY',' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$tab[0]['TABLE_REF_COL'].'<>255 ORDER BY',$sql_col['sql']);
			}
			$tables[$_SESSION['table']]['sql_col_dim'] = $sql_col['sql'];
			$result = $GLOBALS['conn']->GetAll($tables[$_SESSION['table']]['sql_col_dim']);
			$res		=		array();
			if(is_array($result))
			foreach( $result as $i => $rs) {
				$res[] = $rs[get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$tab[0]['TABLE_REF_COL'])];
			}
			if(is_array($res))
			foreach($res as $tb){
				$tables[$_SESSION['table']]['name_records_tab_ref_col_dim'][] = $tb;
			}
		
		}
	
	}else{
			
		$requete 	=	' SELECT * 
							FROM DICO_EXCEL_REQ_ASSOC_FIELDS
							WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_REQUETE =\''.$_SESSION['table'].'\' AND EXPORT = 1 
							ORDER BY ORDRE';
		$fields  = $GLOBALS['conn_dico']->GetAll($requete);
		
		if(is_array($fields))
		foreach($fields as $chp){
			if($chp['TYPE_DIM'] == '')
				$tables[$_SESSION['table']]['data_entry_fields'][] = $chp['NOM_CHAMP'];
			
			if($chp['TYPE_DIM']=='dimension_ligne'){
				$tables[$_SESSION['table']]['name_records_tab_ref_row_dim'] = explode("#",$chp['RECORDS_ROW']);
			}
			if($chp['TYPE_DIM']=='dimension_colonne'){
				$tables[$_SESSION['table']]['name_records_tab_ref_col_dim'] = explode("#",$chp['RECORDS_COL']);
			}
		}
	}
	//echo '<pre>';
	//print_r($tables[$_SESSION['table']]);
	
	if(count($_POST)){
		if(isset($_POST['nb_chps']) && !isset($_POST['nb_val_dim_row']) && !isset($_POST['nb_val_dim_col'])){
			$val_row = "";	
			$premier_elt=true;	
			for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
				if( isset($_POST['ROW_'.$i])){
						if(!$premier_elt) $val_row .=',';
						$premier_elt=false;
						$val_row .= $_POST['ROW_'.$i] ;	
				}
			}
			$val_col = "";	
			$premier_elt=true;	
			for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
				if( isset($_POST['COL_'.$i])){
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
					if( isset($_POST['ROW_'.$j.'_'.$i])){
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
					if( isset($_POST['COL_'.$j.'_'.$i])){
							if(!$premier_elt) $val_col .=',';
							$premier_elt=false;
							$val_col .= $_POST['COL_'.$j.'_'.$i] ;	
					}
				}
			}
			if( $val_row<>'' && $val_col<>''){			
				echo '<script type="text/Javascript">
						parent.document.Formulaire.NUM_LIGNES.value=\''.$val_row.'\' ;
						parent.document.Formulaire.NUM_COLONNES.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_row==''){			
				echo '<script type="text/Javascript">
						parent.document.Formulaire.NUM_COLONNES.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_col==''){			
				echo '<script type="text/Javascript">
						parent.document.Formulaire.NUM_LIGNES.value=\''.$val_row.'\' ;
						fermer();
					  </script>';
			}
		}
		if(!isset($_POST['nb_val_dim_row']) && isset($_POST['nb_val_dim_col'])){
			$val_row = "";	
			$premier_elt=true;
			for( $j = 0 ; $j < $_POST['nb_val_dim_col'] ; $j++){
				for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
					if( isset($_POST['ROW_'.$j.'_'.$i])){
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
					if( isset($_POST['COL_'.$j.'_'.$i])){
							if(!$premier_elt) $val_col .=',';
							$premier_elt=false;
							$val_col .= $_POST['COL_'.$j.'_'.$i] ;	
					}
				}
			}
			if( $val_row<>'' && $val_col<>''){			
				echo '<script type="text/Javascript">
						parent.document.Formulaire.NUM_LIGNES.value=\''.$val_row.'\' ;
						parent.document.Formulaire.NUM_COLONNES.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_row==''){			
				echo '<script type="text/Javascript">
						parent.document.Formulaire.NUM_COLONNES.value=\''.$val_col.'\' ;
						fermer();
					  </script>';
			}elseif( $val_col==''){			
				echo '<script type="text/Javascript">
						parent.document.Formulaire.NUM_LIGNES.value=\''.$val_row.'\' ;
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
        <td width="100%" align="center">
		<?php if(is_array($tab) && count($tab)>0) echo recherche_libelle_page('choix_table').' : <b>'.$tab[0]['NOM_TABLE_2'];
			  else echo recherche_libelle_page('choix_requete').' : <b>'.$_SESSION['table'];
		?>
		</b></td>
    </tr>
	<tr><td >&nbsp;</td></tr>
	
	<?php if(isset($tables[$_SESSION['table']]['data_entry_fields']) && !isset($tables[$_SESSION['table']]['name_records_tab_ref_row_dim']) && !isset($tables[$_SESSION['table']]['name_records_tab_ref_col_dim'])){
			$entry_fields = $tables[$_SESSION['table']]['data_entry_fields'];
	?>
	<tr><td >
        <table align="center">
				<tr> 
					<td align='center' ><?php echo "".recherche_libelle_page('nom_chp');?></td>
					<td align='center' ><?php echo "".recherche_libelle_page('form_row')."";?></td>
 					<td align='center' ><?php echo "".recherche_libelle_page('form_col')."";?></td>
				</tr>
				<?php $k = 0;
				if(is_array($entry_fields)){
				foreach ($entry_fields as $i => $chp){
				?>
					<tr> 
						<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$i;?>" value="<?php echo $chp;?>" readonly="1"/></td>
						<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'ROW_'.$i;?>" value="<?php if(isset($lignes[$k])) echo $lignes[$k];?>" /></td>
						<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'COL_'.$i;?>" value="<?php if(isset($colonnes[$k])) echo $colonnes[$k];?>" /></td>
					</tr>
				<?php $k++; 
				}
				}
				?>
					
				<tr><td colspan=3 align='center'>&nbsp;</td></tr>

		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
	<?php }?>
	<?php if(isset($tables[$_SESSION['table']]['name_records_tab_ref_row_dim']) && !isset($tables[$_SESSION['table']]['name_records_tab_ref_col_dim'])){
			$entry_fields = $tables[$_SESSION['table']]['data_entry_fields'];
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
				if(is_array($tables[$_SESSION['table']]['name_records_tab_ref_row_dim'])){
				foreach ($tables[$_SESSION['table']]['name_records_tab_ref_row_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php if(is_array($entry_fields)){
					foreach ($entry_fields as $i => $chp){
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
					}
					?>
					</td>
					</tr>
				<?php }
				}
				?>
			
		</table>
  </table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
			<input type="hidden" name="nb_val_dim_row" value="<?php echo count($tables[$_SESSION['table']]['name_records_tab_ref_row_dim']); ?>" />
	<?php }?>
	<?php if(!isset($tables[$_SESSION['table']]['name_records_tab_ref_row_dim']) && isset($tables[$_SESSION['table']]['name_records_tab_ref_col_dim'])){
			$entry_fields = $tables[$_SESSION['table']]['data_entry_fields'];
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
				if(is_array($tables[$_SESSION['table']]['name_records_tab_ref_col_dim'])){
				foreach ($tables[$_SESSION['table']]['name_records_tab_ref_col_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php if(is_array($entry_fields)){
					foreach ($entry_fields as $i => $chp){
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
					}
					?>
					</td>
					</tr>
				<?php }
				}
				?>
			
	  </table>
		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
			<input type="hidden" name="nb_val_dim_col" value="<?php echo count($tables[$_SESSION['table']]['name_records_tab_ref_col_dim']); ?>" />
	<?php }?>
	<?php if(isset($tables[$_SESSION['table']]['name_records_tab_ref_row_dim']) && isset($_GET['dim']) && $_GET['dim']=='dim_row'){
			$entry_fields = $tables[$_SESSION['table']]['data_entry_fields'];
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
				if(is_array($tables[$_SESSION['table']]['name_records_tab_ref_row_dim'])){
				foreach ($tables[$_SESSION['table']]['name_records_tab_ref_row_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php if(is_array($entry_fields)){
					foreach ($entry_fields as $i => $chp){
					?>
						<table align="center">
							
							<tr> 
							<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$j.'_'.$i;?>" value="<?php echo $chp;?>" readonly="1" /></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'ROW_'.$j.'_'.$i;?>" value="<?php if(isset($lignes[$k])) echo $lignes[$k];?>" /></td>
							</tr>
						</table>
					<?php $k++; 
					}
					}
					?>
					</td>
					</tr>
				<?php }
				}
				?>
			
	  </table>
		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
			<input type="hidden" name="nb_val_dim_row" value="<?php echo count($tables[$_SESSION['table']]['name_records_tab_ref_row_dim']); ?>" />
	<?php }?>
	<?php if(isset($tables[$_SESSION['table']]['name_records_tab_ref_col_dim']) && isset($_GET['dim']) && $_GET['dim']=='dim_col'){
			$entry_fields = $tables[$_SESSION['table']]['data_entry_fields'];
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
				if(is_array($tables[$_SESSION['table']]['name_records_tab_ref_col_dim'])){
				foreach ($tables[$_SESSION['table']]['name_records_tab_ref_col_dim'] as $j => $rec){
				?>
					<tr> 
					<td align='center' style="vertical-align:middle; width:150px" ><?php echo "".$rec;?></td>
					<td>
					<?php if(is_array($entry_fields)){
					foreach ($entry_fields as $i => $chp){
					?>
						<table align="center">
							
							<tr> 
							<td align="center" ><INPUT style="width:150px; background-color:#CCCCCC" type='text' name="<?php echo'CHP_'.$j.'_'.$i;?>" value="<?php echo $chp;?>" readonly="1"/></td>
							<td align="center" ><INPUT style="width:75px" type="text" name="<?php echo'COL_'.$j.'_'.$i;?>" value="<?php if(isset($colonnes[$k])) echo $colonnes[$k];?>" /></td>
							</tr>
						</table>
					<?php $k++;
					}
					}
					?>
					</td>
					</tr>
				<?php }
				}
				?>
			
	  </table>
		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
			<input type="hidden" name="nb_val_dim_col" value="<?php echo count($tables[$_SESSION['table']]['name_records_tab_ref_col_dim']); ?>" />
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

