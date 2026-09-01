<script language="JavaScript" type="text/javascript">
<?php $requete 	=	' SELECT * 
						FROM DICO_AGGREGATED_TABLE
						WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND EXPORT = 1 
						ORDER BY ORDRE';
	$tables_aggreg  = $GLOBALS['conn_dico']->GetAll($requete);
	$script_tables_aggreg = "var tables = new Array();\n";
	foreach($tables_aggreg as $table){// Pour chq Table Mère
		$script_tables_aggreg.="tables['".$table['NOM_TABLE_2']."'] = new Array();\n";
		$script_tables_aggreg.="tables['".$table['NOM_TABLE_2']."']['RECORDS_ROW'] = \"".$table['RECORDS_ROW']."\";\n";
		$script_tables_aggreg.="tables['".$table['NOM_TABLE_2']."']['RECORDS_COL'] = \"".$table['RECORDS_COL']."\";\n";
	}
	//echo $script_tables_aggreg;
	
	$req 	=	' SELECT NOM_REQUETE
						FROM DICO_EXCEL_REQUETE_ASSOC
						WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND EXPORT = 1  
						ORDER BY ORDRE';
	$tab_aggreg  = $GLOBALS['conn_dico']->GetAll($req);
	foreach($tab_aggreg as $tab){// Pour chq Table Mère
		$req_dim 	=	' SELECT * 
							FROM DICO_EXCEL_REQ_ASSOC_FIELDS
							WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND EXPORT = 1 AND NOM_REQUETE=\''.$tab['NOM_REQUETE'].'\' AND TYPE_DIM <>\'\' 
							ORDER BY ORDRE';
		$tab_aggreg_dim  = $GLOBALS['conn_dico']->GetAll($req_dim);
		//$script_tables_aggreg = "var tables = new Array();\n";
		$script_tables_aggreg.="tables['".$tab['NOM_REQUETE']."'] = new Array();\n";
		$dim_row = false;
		$dim_col = false;
		foreach($tab_aggreg_dim as $table){// Pour chq dim
			if($table['TYPE_DIM'] == 'dimension_ligne' ){
				$script_tables_aggreg.="tables['".$table['NOM_REQUETE']."']['RECORDS_ROW'] = \"".$table['RECORDS_ROW']."\";\n";
				$dim_row = true;
			}elseif($table['TYPE_DIM'] == 'dimension_colonne' ){
				$script_tables_aggreg.="tables['".$table['NOM_REQUETE']."']['RECORDS_COL'] = \"".$table['RECORDS_COL']."\";\n";
				$dim_col = true;
			}
		}
		if(!$dim_row) $script_tables_aggreg.="tables['".$tab['NOM_REQUETE']."']['RECORDS_ROW'] = \"\";\n";
		if(!$dim_col) $script_tables_aggreg.="tables['".$tab['NOM_REQUETE']."']['RECORDS_COL'] = \"\";\n";		
	}
	echo $script_tables_aggreg;
	
	$requete 	=	' SELECT * 
						FROM DICO_AGGREGATED_FIELD
						WHERE ID_SYSTEME = '.$_SESSION['secteur'].'';
	$champs_aggreg  = $GLOBALS['conn_dico']->GetAll($requete);
	$script_champs_fixe_val = "var champs_fixe_val = new Array();\n";
	foreach($champs_aggreg as $champ){
		if($champ['FIXES_VALS']<>''){
			$req 	=	' SELECT NOM_TABLE_2 
							FROM DICO_AGGREGATED_TABLE
							WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND NOM_TABLE = \''.$champ['NOM_TABLE'].'\'';
			$tables  = $GLOBALS['conn_dico']->GetAll($req);
			foreach($tables as $tab){
				$script_champs_fixe_val.="champs_fixe_val['".$tab['NOM_TABLE_2']."'] = new Array(".$champ['FIXES_VALS'].");\n";
			}
		}
	}
	echo $script_champs_fixe_val;

?>
	
	var html_deux_dim;
	var html_une_dim;
	var html_btn_fixe_val;
	
	function changer_btn_dim(){
		//alert(document.forms['Formulaire'].type_table.value);
		var nom_table = document.forms['Formulaire'].NOM_TABLE_2.value;
		if(!(tables[nom_table]['RECORDS_ROW'] != '' && tables[nom_table]['RECORDS_COL'] != '')){
			//document.getElementById('deux_dim').style.visibility = 'hidden';
		 	//document.getElementById('deux_dim').style.overflow = 'hidden';
		 	//document.getElementById('deux_dim').style.height = '1px';
			document.getElementById('deux_dim').innerHTML = '';
			document.getElementById('une_dim').innerHTML = html_une_dim;
			//document.getElementById('une_dim').style.visibility = 'visible';
			//document.getElementById('une_dim').style.overflow = 'visible';
		}else{
			//document.getElementById('une_dim').style.visibility = 'hidden';
			//document.getElementById('une_dim').style.overflow = 'hidden';
		 	//document.getElementById('une_dim').style.height = '1px';
			document.getElementById('une_dim').innerHTML = '';
			document.getElementById('deux_dim').innerHTML = html_deux_dim;
			//document.getElementById('deux_dim').style.visibility = 'visible';
			//document.getElementById('deux_dim').style.overflow = 'visible';
		}
		if (typeof(champs_fixe_val[nom_table]) == 'undefined'){
			document.getElementById('btn_fixe_val').innerHTML = '';
		}else{
			document.getElementById('btn_fixe_val').innerHTML = html_btn_fixe_val;
		}
	}
	
</script>
    <br/>
        <table>
			<input type="hidden" name="ID_TABLE" id="ID_TABLE" value="<?php echo $val['ID_TABLE']; ?>" />
			<INPUT type="hidden" name="ID_SYSTEME" id="ID_SYSTEME" value="<?php echo $val['ID_SYSTEME']; ?>">
			<tr>
				<td><?php echo recherche_libelle_page('OrdreForm'); ?></td>
				<td colspan="2"><INPUT type="text" size="2" name="ORDRE_FORM" value="<?php echo $val['ORDRE_FORM']; ?>"></td>
			</tr>
            <tr> 
                <td nowrap><?php echo recherche_libelle_page('NomForm');?></td>
                <td colspan="2"><INPUT type="text" size="40" name="NOM_FORM" value="<?php echo $val['NOM_FORM']; ?>"></td>
            </tr>
			<tr>
				<td><?php echo recherche_libelle_page('NomTable'); ?></td>
				<td>
				<select name="NOM_TABLE_2" ID="NOM_TABLE_2" onchange="changer_btn_dim()">
				<?php $tables = array();
					$requete 	=	' SELECT * 
										FROM DICO_AGGREGATED_TABLE
										WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND EXPORT =1 
										ORDER BY ORDRE';
					$tab  = $GLOBALS['conn_dico']->GetAll($requete);
					foreach ($tab as $itab => $tb){
							echo "<option value='".$tb['NOM_TABLE_2']."'";
							if ($tb['NOM_TABLE_2'] == $val['NOM_TABLE_2']){
									echo " selected";
							}
							echo ">".$tb['NOM_TABLE_2']."</option>\n";
					}
					$requete 	=	' SELECT * 
										FROM DICO_EXCEL_REQUETE_ASSOC
										WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND EXPORT =1 
										ORDER BY ORDRE';
					$tab  = $GLOBALS['conn_dico']->GetAll($requete);
					foreach ($tab as $itab => $tb){
							echo "<option value='".$tb['NOM_REQUETE']."'";
							if ($tb['NOM_REQUETE'] == $val['NOM_TABLE_2']){
									echo " selected";
							}
							echo ">".$tb['NOM_REQUETE']."</option>\n";
					}
				?>
				</select>
				</td>
				<td id="btn_fixe_val"><input name="button" type="button" style="height:22px" onclick="OpenPopupAggregFixeVal(ID_TABLE.value,NOM_TABLE_2.value)" value="<?php echo recherche_libelle_page('def_fixe_val');?>" /></td>
			</tr>
			<tr> 
                <td nowrap><?php echo recherche_libelle_page('NomPage');?></td>
                <td colspan="2"><INPUT type="text" size="40" name="NOM_PAGE" value="<?php echo $val['NOM_PAGE']; ?>"></td>
            </tr>
			<tr>
				<td colspan="3">
					<div name="une_dim" id="une_dim">
					<table>
						<tr> 
							<td nowrap><?php echo recherche_libelle_page('Lignes');?></td>
							<td nowrap>
							<INPUT type="text" size="40" name="NUM_LIGNES" value="<?php echo $val['NUM_LIGNES']; ?>" style="background-color:#CCCCCC" readonly="1">
							</td>
							<td nowrap rowspan="2" style="vertical-align:middle">
							<input name="button" type="button" style="height:45px"  onclick="OpenPopupAggregFieldsRowCol(NOM_TABLE_2.value,'',NUM_LIGNES.value,NUM_COLONNES.value)" value="<?php echo recherche_libelle_page('def_numbers');?>" />
							</td>
						</tr>
						<tr> 
							<td nowrap><?php echo recherche_libelle_page('Colonnes');?></td>
							<td><INPUT type="text" size="40" name="NUM_COLONNES" value="<?php echo $val['NUM_COLONNES']; ?>" style="background-color:#CCCCCC" readonly="1"></td>
						</tr>
					</table>
					</div>
				</td>
			</tr>
			<tr>
			<td colspan="3">
				<div name="deux_dim" id="deux_dim">
				<table>
					<tr> 
						<td nowrap><?php echo recherche_libelle_page('Lignes');?></td>
						<td nowrap>
						<INPUT type="text" size="40" name="NUM_LIGNES" value="<?php echo $val['NUM_LIGNES']; ?>" style="background-color:#CCCCCC" readonly="1">
						</td>
						<td nowrap style="vertical-align:middle">
						<input name="button" type="button" style="height:22px"  onclick="OpenPopupAggregFieldsRowCol(NOM_TABLE_2.value,'dim_row',NUM_LIGNES.value,NUM_COLONNES.value)" value="<?php echo recherche_libelle_page('def_row_numbers');?>" />
						</td>
					</tr>
					
					<tr> 
						<td nowrap><?php echo recherche_libelle_page('Colonnes');?></td>
						<td><INPUT type="text" size="40" name="NUM_COLONNES" value="<?php echo $val['NUM_COLONNES']; ?>" style="background-color:#CCCCCC" readonly="1"></td>
						<td nowrap style="vertical-align:middle">
						<input name="button" type="button" style="height:22px"  onclick="OpenPopupAggregFieldsRowCol(NOM_TABLE_2.value,'dim_col',NUM_LIGNES.value,NUM_COLONNES.value)" value="<?php echo recherche_libelle_page('def_col_numbers');?>" />
						</td>
					</tr>
				</table>
				</div>
			</td>
			</tr>
		</table>
	<br/>
	<script language="javascript" type="text/javascript">
		html_une_dim = document.getElementById('une_dim').innerHTML;
		html_deux_dim = document.getElementById('deux_dim').innerHTML;
		html_btn_fixe_val = document.getElementById('btn_fixe_val').innerHTML;
		changer_btn_dim();
	</script>
