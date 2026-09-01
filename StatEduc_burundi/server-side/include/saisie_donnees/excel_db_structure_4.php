<?php 
if( isset($_POST) && count($_POST) > 0 && $_POST['id_theme_syst'] == 3704 ){
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper($data->boundsheets[$sheet]['name'])=='PAGE 1'){
			$row_excel_nom_etab = 24;//row
			$col_excel_nom_etab = 16;//column
			$row_excel_code_etab = 24;//row
			$col_excel_code_etab = 32;//column
			$req_etab = "SELECT ".$GLOBALS['PARAM']['NOM_ETABLISSEMENT']." FROM ".$GLOBALS['PARAM']['ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab'];
			$inst_name = $GLOBALS['conn']->GetOne($req_etab);
			if(trim($inst_name) <> "" && strtoupper($data->sheets[$sheet]['cells'][$row_excel_nom_etab][$col_excel_nom_etab]) <> strtoupper($inst_name) && (!isset($_SESSION['xls_files']) || count($_SESSION['xls_files'])==1)){
				print "<script type='text/Javascript'>\n";
				print "if(confirm(\"".recherche_libelle_page('Err_Diff_Etab_Nom')." ".$GLOBALS['PARAM']['ETABLISSEMENT']." ".recherche_libelle_page('Err_Diff_Etab_Nom_Suite')."\")){\n";
				print "document.location.href='saisie_donnees.php?val=PopupImportExcel&excel_file=".urlencode($_POST['chemin_fichier'])."&row_excel_code_etab=".$row_excel_code_etab."&col_excel_code_etab=".$col_excel_code_etab."';\n";
				print "}\n";
				print "</script>\n";
			}else{
				print "<script type='text/Javascript'>\n";
				if(isset($_POST['new_code_etab']) && $_POST['new_code_etab'] <> "")
					print "document.location.href='saisie_donnees.php?val=PopupImportExcel&excel_file=".urlencode($_POST['chemin_fichier'])."&new_code_etab=".$_POST['new_code_etab']."&row_excel_code_etab=".$row_excel_code_etab."&col_excel_code_etab=".$col_excel_code_etab."';\n";
				else
					print "document.location.href='saisie_donnees.php?val=PopupImportExcel&excel_file=".urlencode($_POST['chemin_fichier'])."&row_excel_code_etab=".$row_excel_code_etab."&col_excel_code_etab=".$col_excel_code_etab."';\n";
				print "</script>\n";
			}

			break;
		}
	}
}
if( isset($_GET['excel_file']) && $_GET['excel_file']<>'' ){
	$tab_themes = array();
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 1'){
			$table = 'ETABLISSEMENT_370';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(24,24,24,27,27,29,34,40,46,36,40,44,44,44,46,0,42,46,21,47,21,49,42);
			$cols_excel = array(32,16,38,16,38,46,46,10,27,16,40,10,25,46,10,0,13,42,38,27,16,16,25);
			$empty_row = true;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$k=0;
			$cpt = 0;
			$cpt_oblig = 0;
			$tab_fields = array();
			$tab_fields_oblig_vide = array();
			$tab_values = array();
			if(is_array($tab['data_entry_fields']))
			foreach ($tab['data_entry_fields'] as $data_field) {
				if($rows_excel[$k]<>0 && $cols_excel[$k]<>0){
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'')	{$cpt++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
					if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
						&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
						&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
						&& in_array($data_field,$tab['obligatory_fields_form'])){
						$cpt_oblig--;
						$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
						$tab_fields_oblig_vide[] = $data_field;
					}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
							&& !in_array($data_field,$tab['obligatory_fields_form'])){
							$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
					}
					$tab_fields[] = $data_field;
					$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
				}
				if($rows_excel[$k]==0 && $cols_excel[$k]==0){
					if(isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form'])){
						$tab_fields_oblig_vide[] = $data_field;
						$tab_fields[] = $data_field;
						$tab_values[] = '';
						if($data_field==$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] && $_GET['new_code_etab']<>'new_etab') $cpt_oblig++;
					}
				}
				$k++;
			}
			if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
			if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
				$error_report .= "</td>\n";
				foreach($tab_fields as $field){
					if(in_array($field,$tab_fields_oblig_vide))
						$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
					else
						$error_report .= "<td>\n";
						$error_report .= "$field";
						$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "<tr>\n";
				foreach($tab_values as $value){
					$error_report .= "<td align='center'>\n";
					$error_report .= "$value";
					$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report;
			}
			if(!$empty_row){
				$ligne = array();
				$tab_val_cle = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
					$tab_val_cle[] = $key_field;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$k]<>0 && $cols_excel[$k]<>0 && !in_array($data_field,$tab_val_cle)){
						$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
					}
					$k++;
				}
				$empty_table = false;
				maj_bdd($ligne,$tab);
			}else{
				$ligne = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				suppr_bdd($ligne,$tab);
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 1'){
			$table = 'ENVIR_SOCIO_ECO_380';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(52,58,61,88,90,94,56,58);
			$cols_excel = array(45,19,46,33,29,41,46,41);
			$empty_row = true;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$k=0;
			$cpt = 0;
			$cpt_oblig = 0;
			$tab_fields = array();
			$tab_fields_oblig_vide = array();
			$tab_values = array();
			if(is_array($tab['data_entry_fields']))
			foreach ($tab['data_entry_fields'] as $data_field) {
				if($rows_excel[$k]<>0 && $cols_excel[$k]<>0){
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'')	{$cpt++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
					if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
						&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
						&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
						&& in_array($data_field,$tab['obligatory_fields_form'])){
						$cpt_oblig--;
						$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
						$tab_fields_oblig_vide[] = $data_field;
					}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
							&& !in_array($data_field,$tab['obligatory_fields_form'])){
							$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
					}
					$tab_fields[] = $data_field;
					$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
				}
				if($rows_excel[$k]==0 && $cols_excel[$k]==0){
					if(isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form'])){
						$tab_fields_oblig_vide[] = $data_field;
						$tab_fields[] = $data_field;
						$tab_values[] = '';
						if($data_field==$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] && $_GET['new_code_etab']<>'new_etab') $cpt_oblig++;
					}
				}
				$k++;
			}
			if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
			if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
				$error_report .= "</td>\n";
				foreach($tab_fields as $field){
					if(in_array($field,$tab_fields_oblig_vide))
						$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
					else
						$error_report .= "<td>\n";
						$error_report .= "$field";
						$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "<tr>\n";
				foreach($tab_values as $value){
					$error_report .= "<td align='center'>\n";
					$error_report .= "$value";
					$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report;
			}
			if(!$empty_row){
				$ligne = array();
				$tab_val_cle = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
					$tab_val_cle[] = $key_field;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$k]<>0 && $cols_excel[$k]<>0 && !in_array($data_field,$tab_val_cle)){
						$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
					}
					$k++;
				}
				$empty_table = false;
				maj_bdd($ligne,$tab);
			}else{
				$ligne = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				suppr_bdd($ligne,$tab);
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 1'){
			$table = 'ENVIR_INFRAS_SANIT_380';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(65,65,67,67,69);
			$cols_excel = array(28,47,28,47,28);
			$i=0;
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
							$empty_row = false;
						}
					}
					$k++;
					$i++;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
								$ligne[$data_field] = $code_col;
							}
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}

		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 1'){
			$table = 'ENVIR_INFRAST_RELIG_380';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(71,71,73,73,74,74);
			$cols_excel = array(28,47,28,47,28,47);
			$i=0;
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
							$empty_row = false;
						}
					}
					$k++;
					$i++;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
								$ligne[$data_field] = $code_col;
							}
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}

		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 1'){
			$table = 'ENVIR_INFRAST_SPORT_CULT_380';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(75,0,75,0,77,0,77,0,78,0,78,0);
			$cols_excel = array(28,0,47,0,28,0,47,0,28,0,47,0);
			$i=0;
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
							$empty_row = false;
						}
					}
					$k++;
					$i++;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
								$ligne[$data_field] = $code_col;
							}
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}

		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 1'){
			$table = 'ENVIR_ACTIVITE_ECO_380';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(82,0,82,0,82,0,84,0,84,0,84,0,86,0,86,0,86,0);
			$cols_excel = array(12,0,26,0,44,0,12,0,26,0,44,0,12,0,26,0,44,0);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 1'){
			$table = 'ENVIR_SECT_PROJ_DEV_380';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(96,96,96,96);
			$cols_excel = array(22,28,40,45);
			$i=0;
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
							$empty_row = false;
						}
					}
					$k++;
					$i++;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
								$ligne[$data_field] = $code_col;
							}
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}

		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 2'){
			$table = 'DONNEES_GENERALES_390';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(5,6,9,10,12,12,12,13,14,14,18,18,19,19,20,20,21,23,23,23,23,24,24,25,26,28,30,30,32,35,35,36,36,31,34,37,33,42,43,43,43,44,45,45,46,48,49,46,63,29,29,23,23,28,11,11,11,13,14,25,25,26,26,27,27,28,29,51,52,52,54,62,62,50,38,47,47,22,64,64,14,24,62,9,11,53,7,47);
			$cols_excel = array(23,46,23,28,20,34,44,20,22,24,20,37,20,29,20,29,20,20,25,30,47,22,29,22,22,21,23,41,20,23,32,23,32,24,26,23,20,21,25,33,37,25,20,24,25,31,26,30,25,21,32,35,41,45,21,16,25,27,37,29,31,29,31,40,22,32,45,31,31,37,31,10,30,31,30,23,34,20,33,42,14,29,43,32,33,32,42,48);
			$empty_row = true;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$k=0;
			$cpt = 0;
			$cpt_oblig = 0;
			$tab_fields = array();
			$tab_fields_oblig_vide = array();
			$tab_values = array();
			if(is_array($tab['data_entry_fields']))
			foreach ($tab['data_entry_fields'] as $data_field) {
				if($rows_excel[$k]<>0 && $cols_excel[$k]<>0){
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'')	{$cpt++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
					if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
						&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
						&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
						&& in_array($data_field,$tab['obligatory_fields_form'])){
						$cpt_oblig--;
						$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
						$tab_fields_oblig_vide[] = $data_field;
					}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
							&& !in_array($data_field,$tab['obligatory_fields_form'])){
							$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
					}
					$tab_fields[] = $data_field;
					$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
				}
				if($rows_excel[$k]==0 && $cols_excel[$k]==0){
					if(isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form'])){
						$tab_fields_oblig_vide[] = $data_field;
						$tab_fields[] = $data_field;
						$tab_values[] = '';
						if($data_field==$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] && $_GET['new_code_etab']<>'new_etab') $cpt_oblig++;
					}
				}
				$k++;
			}
			if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
			if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
				$error_report .= "</td>\n";
				foreach($tab_fields as $field){
					if(in_array($field,$tab_fields_oblig_vide))
						$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
					else
						$error_report .= "<td>\n";
						$error_report .= "$field";
						$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "<tr>\n";
				foreach($tab_values as $value){
					$error_report .= "<td align='center'>\n";
					$error_report .= "$value";
					$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report;
			}
			if(!$empty_row){
				$ligne = array();
				$tab_val_cle = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
					$tab_val_cle[] = $key_field;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$k]<>0 && $cols_excel[$k]<>0 && !in_array($data_field,$tab_val_cle)){
						$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
					}
					$k++;
				}
				$empty_table = false;
				maj_bdd($ligne,$tab);
			}else{
				$ligne = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				suppr_bdd($ligne,$tab);
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 2'){
			$table = 'ETAB_CANTINE_SOURCE_FINANC_390';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(15,15,15,15,15,16,16,16);
			$cols_excel = array(11,22,35,41,48,20,32,44);
			$i=0;
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
							$empty_row = false;
						}
					}
					$k++;
					$i++;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
								$ligne[$data_field] = $code_col;
							}
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}

		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 2'){
			$table = 'ETAB_PARTENARIAT_390';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(37,37,37);
			$cols_excel = array(32,41,46);
			$i=0;
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
							$empty_row = false;
						}
					}
					$k++;
					$i++;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
								$ligne[$data_field] = $code_col;
							}
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}

		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 2'){
			$table = 'ETAB_ENSEIGNANT_CORPS_390';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(69,69,69,69,69,69,69,69,69,69,69,69,69,69,69,69,69,69,69,69,69,69);
			$cols_excel = array(10,12,14,16,18,20,22,23,24,26,27,29,30,31,37,39,40,41,42,43,45,46);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 2'){
			$table = 'ETAB_AUTEURS_GROSSESSE_390';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(53,53,53,53);
			$cols_excel = array(12,19,24,28);
			$i=0;
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
							$empty_row = false;
						}
					}
					$k++;
					$i++;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
								$ligne[$data_field] = $code_col;
							}
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}

		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 2'){
			$table = 'CAS_VIOLENCE_390';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(57,57,57,58,58,58,59,59,59,60,60,60,61,61,61);
			$cols_excel = array(18,29,31,18,29,31,18,29,31,18,29,31,18,29,31);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_row_dim'] as $code) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 2'){
			$table = 'SOURCE_FINANC_PROJET_ETAB_390';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(39,39,39,39,39,40,40,40);
			$cols_excel = array(6,19,33,40,46,12,24,33);
			$i=0;
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
							$empty_row = false;
						}
					}
					$k++;
					$i++;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							if((trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'q') && 
								(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')){
								$ligne[$data_field] = $code_col;
							}
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}

		}
	}
	$cpt_del = 0;
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 3'){
			$table = 'LOCAL_400';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$row_excel = 9;
			$cols_excel = array(5,16,21,23,27,30,35,38,42,47,51,54,75);
			$limit_empty_rows = $GLOBALS['PARAM']['IMPORT_GRILLE_LIMIT_VIDE'];//default limit of empty lines for stopping data integration 
			if($tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) && count($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) > 0){
				$tab_rows = array_keys($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']);
				$last_row = $tab_rows[count($tab_rows)-1];
				$limit_empty_rows = $last_row - $row_excel +1;
			}
			$empty_row = false;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields'])-$tables[$table]['nb_val_cle'];
			if($nb_oblig_data_entry_fields > 0){
				if($cpt_del == 0){
					$ligne_del = array();
					$table_del = $table;
					$tab_del = $tab;
					if($tables[$table]['name']==$GLOBALS['PARAM']['ENSEIGNANT']){
						$table_del = str_replace($GLOBALS['PARAM']['ENSEIGNANT'],$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'],$table);
						$tab_del = $tables[$table_del];
					}
					if(is_array($tables[$table_del]['val_cle']))
					foreach($tables[$table_del]['val_cle'] as $key_field => $key_val){
						$ligne_del[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne_del[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(($action = exist_bdd_grille($ligne_del,$tab_del)) == 'U') suppr_bdd_grille($ligne_del,$tab_del);
					$cpt_del++;
				}
				while(!$empty_row) {
					//cas table non matricielle
					if(!(isset($tab['records_tab_ref_col_dim']) && count($tab['records_tab_ref_col_dim'])>0)){
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'' && !preg_match("/^[\(\[\{\*]/",$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]]) && in_array($data_field,$tab['obligatory_fields']))	$cpt++;
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']))	$tab_fields_oblig_vide[] = $data_field;
									if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
										&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
										&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
										&& in_array($data_field,$tab['obligatory_fields'])){
										$cpt--;
										$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										$tab_fields_oblig_vide[] = $data_field;
									}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& !in_array($data_field,$tab['obligatory_fields'])){
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
									}
									$tab_fields[] = $data_field;
									$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if($cpt==$nb_oblig_data_entry_fields) $empty_row = false;
							if((($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)) || (($cpt == 0 || $cpt == 1) && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))){
							//if(($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)){
							
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
										$error_report .= "$field";
										$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if(isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]) && $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
								
							}
						}
						if(!$empty_row){
							$ligne = array();
							if(is_array($tables[$table]['val_cle']))
							foreach($tables[$table]['val_cle'] as $key_field => $key_val){
								$ligne[$key_field] = $key_val;
							}
							if(isset($exist_filtre) && $exist_filtre==true){
								$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
							}
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if(($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])))){
								$empty_table = false;
								maj_bdd($ligne,$tab,$row_excel);
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}
					//cas table matricielle : dimension colonne et assimulées
					else{
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ii = 0;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0){
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'')	$cpt++;
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']) && $i<count($tab['data_entry_fields']))	$tab_fields_oblig_vide[] = $data_field;
										if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>'')) 
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& in_array($data_field,$tab['obligatory_fields'])){
											$cpt--;
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
											$tab_fields_oblig_vide[] = $data_field;
										}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>''))
												&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
												&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
												&& !in_array($data_field,$tab['obligatory_fields'])){
												$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										}
										if($i<count($tab['data_entry_fields'])){
											$tab_fields[] = $data_field;
											$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										}
									}
									$i++;
									$ii++;
								}
							}
							if($cpt > 0) $empty_row = false;
							if($cpt == 0 && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
									$error_report .= "$field";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
							}
						}
						if(!$empty_row){
							$i=0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ligne = array();
								if(is_array($tables[$table]['val_cle']))
								foreach($tables[$table]['val_cle'] as $key_field => $key_val){
									$ligne[$key_field] = $key_val;
								}
								if(isset($exist_filtre) && $exist_filtre==true){
									$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
								}
								if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
								elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
								$empty_cells = true;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0 && trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''){
										$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										$empty_cells = false;
									}
									$i++;
								}
								if((!$empty_cells) && (($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))))){
									$empty_table = false;
									maj_bdd($ligne,$tab,$row_excel);
								}
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}	
				}
			}else{
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Chp_Oblig_Manquant_Gril')." ".$data->boundsheets[$sheet]['name']." ! ".recherche_libelle_page('Chp_Oblig_Manquant_Gril_Config')." : ".$tab['name'];
				$error_report .= "</td>\n";
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report; 

				//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
				if(isset($tab['main_table_mere']) && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields']) && $tab['name'] <> $tab['main_table_mere']){
					$ligne_del = array();
					$clause_where = "";
					if($tab['main_table_mere'] <> $GLOBALS['PARAM']['ENSEIGNANT']){
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne_del[$key_field] = $key_val;
						}
						$i = 0;
						foreach($ligne_del as $key=>$val){
							if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
								($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
								if($i==0){
									$clause_where .= " WHERE $key = $val_key ";
								}else{
									$clause_where .= " AND $key = $val_key ";
								}
								$i++;
							}
						}
						if($clause_where <> ""){
							$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
							$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
							if($nb_exist > 0){
								$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
								//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
								if ($GLOBALS['conn']->Execute($req_delete) === false){
									$GLOBALS['theme_data_MAJ_ok'] 	= false;
									$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
									$error_report .= "<tr>\n";
									$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
									$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
									$error_report .= "</td>\n";
									$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
									$error_report .= $req_delete;
									$error_report .= "</td>\n";
									$error_report .= "</tr>\n";
									$error_report .= "</table>\n";
									print $error_report; 
								}
							}
						}
					}else{
						foreach($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'] as $id_pers){
							$clause_where = " WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = $id_pers";
							$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].$clause_where;
							$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
							if($NB_ENS_ETAB == 0){
								$req_exist_ens = "SELECT COUNT(*) FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
								$nb_exist_ens = $GLOBALS['conn']->GetOne($req_exist_ens);
								if($nb_exist_ens > 0){
									$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
									//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
										$error_report .= "<tr>\n";
										$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
										$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$GLOBALS['PARAM']['ENSEIGNANT'];
										$error_report .= "</td>\n";
										$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
										$error_report .= $req_delete;
										$error_report .= "</td>\n";
										$error_report .= "</tr>\n";
										$error_report .= "</table>\n";
										print $error_report; 
									}
								}
							}
						}
					}	
				}
				//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}

		}
	}
	$cpt_del = 0;
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 3'){
			$table = 'LOCAL_REHABILITATION_400';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$row_excel = 9;
			$cols_excel = array(63,65,67,69,71,73);
			$limit_empty_rows = $GLOBALS['PARAM']['IMPORT_GRILLE_LIMIT_VIDE'];//default limit of empty lines for stopping data integration 
			if($tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) && count($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) > 0){
				$tab_rows = array_keys($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']);
				$last_row = $tab_rows[count($tab_rows)-1];
				$limit_empty_rows = $last_row - $row_excel +1;
			}
			$empty_row = false;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields'])-$tables[$table]['nb_val_cle'];
			if($nb_oblig_data_entry_fields > 0){
				if($cpt_del == 0){
					$ligne_del = array();
					$table_del = $table;
					$tab_del = $tab;
					if($tables[$table]['name']==$GLOBALS['PARAM']['ENSEIGNANT']){
						$table_del = str_replace($GLOBALS['PARAM']['ENSEIGNANT'],$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'],$table);
						$tab_del = $tables[$table_del];
					}
					if(is_array($tables[$table_del]['val_cle']))
					foreach($tables[$table_del]['val_cle'] as $key_field => $key_val){
						$ligne_del[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne_del[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(($action = exist_bdd_grille($ligne_del,$tab_del)) == 'U') suppr_bdd_grille($ligne_del,$tab_del);
					$cpt_del++;
				}
				while(!$empty_row) {
					//cas table non matricielle
					if(!(isset($tab['records_tab_ref_col_dim']) && count($tab['records_tab_ref_col_dim'])>0)){
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'' && !preg_match("/^[\(\[\{\*]/",$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]]) && in_array($data_field,$tab['obligatory_fields']))	$cpt++;
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']))	$tab_fields_oblig_vide[] = $data_field;
									if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
										&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
										&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
										&& in_array($data_field,$tab['obligatory_fields'])){
										$cpt--;
										$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										$tab_fields_oblig_vide[] = $data_field;
									}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& !in_array($data_field,$tab['obligatory_fields'])){
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
									}
									$tab_fields[] = $data_field;
									$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if($cpt==$nb_oblig_data_entry_fields) $empty_row = false;
							if((($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)) || (($cpt == 0 || $cpt == 1) && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))){
							//if(($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)){
							
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
										$error_report .= "$field";
										$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if(isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]) && $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
								
							}
						}
						if(!$empty_row){
							$ligne = array();
							if(is_array($tables[$table]['val_cle']))
							foreach($tables[$table]['val_cle'] as $key_field => $key_val){
								$ligne[$key_field] = $key_val;
							}
							if(isset($exist_filtre) && $exist_filtre==true){
								$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
							}
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if(($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])))){
								$empty_table = false;
								maj_bdd($ligne,$tab,$row_excel);
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}
					//cas table matricielle : dimension colonne et assimulées
					else{
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ii = 0;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0){
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'')	$cpt++;
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']) && $i<count($tab['data_entry_fields']))	$tab_fields_oblig_vide[] = $data_field;
										if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>'')) 
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& in_array($data_field,$tab['obligatory_fields'])){
											$cpt--;
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
											$tab_fields_oblig_vide[] = $data_field;
										}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>''))
												&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
												&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
												&& !in_array($data_field,$tab['obligatory_fields'])){
												$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										}
										if($i<count($tab['data_entry_fields'])){
											$tab_fields[] = $data_field;
											$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										}
									}
									$i++;
									$ii++;
								}
							}
							if($cpt > 0) $empty_row = false;
							if($cpt == 0 && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
									$error_report .= "$field";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
							}
						}
						if(!$empty_row){
							$i=0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ligne = array();
								if(is_array($tables[$table]['val_cle']))
								foreach($tables[$table]['val_cle'] as $key_field => $key_val){
									$ligne[$key_field] = $key_val;
								}
								if(isset($exist_filtre) && $exist_filtre==true){
									$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
								}
								if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
								elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
								$empty_cells = true;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0 && trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''){
										$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										$empty_cells = false;
									}
									$i++;
								}
								if((!$empty_cells) && (($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))))){
									$empty_table = false;
									maj_bdd($ligne,$tab,$row_excel);
								}
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}	
				}
			}else{
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Chp_Oblig_Manquant_Gril')." ".$data->boundsheets[$sheet]['name']." ! ".recherche_libelle_page('Chp_Oblig_Manquant_Gril_Config')." : ".$tab['name'];
				$error_report .= "</td>\n";
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report; 

				//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
				if(isset($tab['main_table_mere']) && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields']) && $tab['name'] <> $tab['main_table_mere']){
					$ligne_del = array();
					$clause_where = "";
					if($tab['main_table_mere'] <> $GLOBALS['PARAM']['ENSEIGNANT']){
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne_del[$key_field] = $key_val;
						}
						$i = 0;
						foreach($ligne_del as $key=>$val){
							if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
								($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
								if($i==0){
									$clause_where .= " WHERE $key = $val_key ";
								}else{
									$clause_where .= " AND $key = $val_key ";
								}
								$i++;
							}
						}
						if($clause_where <> ""){
							$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
							$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
							if($nb_exist > 0){
								$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
								//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
								if ($GLOBALS['conn']->Execute($req_delete) === false){
									$GLOBALS['theme_data_MAJ_ok'] 	= false;
									$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
									$error_report .= "<tr>\n";
									$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
									$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
									$error_report .= "</td>\n";
									$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
									$error_report .= $req_delete;
									$error_report .= "</td>\n";
									$error_report .= "</tr>\n";
									$error_report .= "</table>\n";
									print $error_report; 
								}
							}
						}
					}else{
						foreach($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'] as $id_pers){
							$clause_where = " WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = $id_pers";
							$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].$clause_where;
							$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
							if($NB_ENS_ETAB == 0){
								$req_exist_ens = "SELECT COUNT(*) FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
								$nb_exist_ens = $GLOBALS['conn']->GetOne($req_exist_ens);
								if($nb_exist_ens > 0){
									$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
									//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
										$error_report .= "<tr>\n";
										$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
										$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$GLOBALS['PARAM']['ENSEIGNANT'];
										$error_report .= "</td>\n";
										$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
										$error_report .= $req_delete;
										$error_report .= "</td>\n";
										$error_report .= "</tr>\n";
										$error_report .= "</table>\n";
										print $error_report; 
									}
								}
							}
						}
					}	
				}
				//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}

		}
	}
	$cpt_del = 0;
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 4'){
			$table = 'MOBILIER_LOCAL_410';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$row_excel = 7;
			$cols_excel = array(3);
			$limit_empty_rows = $GLOBALS['PARAM']['IMPORT_GRILLE_LIMIT_VIDE'];//default limit of empty lines for stopping data integration 
			if($tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) && count($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) > 0){
				$tab_rows = array_keys($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']);
				$last_row = $tab_rows[count($tab_rows)-1];
				$limit_empty_rows = $last_row - $row_excel +1;
			}
			$empty_row = false;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields'])-$tables[$table]['nb_val_cle'];
			if($nb_oblig_data_entry_fields > 0){
				if($cpt_del == 0){
					$ligne_del = array();
					$table_del = $table;
					$tab_del = $tab;
					if($tables[$table]['name']==$GLOBALS['PARAM']['ENSEIGNANT']){
						$table_del = str_replace($GLOBALS['PARAM']['ENSEIGNANT'],$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'],$table);
						$tab_del = $tables[$table_del];
					}
					if(is_array($tables[$table_del]['val_cle']))
					foreach($tables[$table_del]['val_cle'] as $key_field => $key_val){
						$ligne_del[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne_del[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(($action = exist_bdd_grille($ligne_del,$tab_del)) == 'U') suppr_bdd_grille($ligne_del,$tab_del);
					$cpt_del++;
				}
				while(!$empty_row) {
					//cas table non matricielle
					if(!(isset($tab['records_tab_ref_col_dim']) && count($tab['records_tab_ref_col_dim'])>0)){
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'' && !preg_match("/^[\(\[\{\*]/",$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]]) && in_array($data_field,$tab['obligatory_fields']))	$cpt++;
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']))	$tab_fields_oblig_vide[] = $data_field;
									if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
										&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
										&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
										&& in_array($data_field,$tab['obligatory_fields'])){
										$cpt--;
										$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										$tab_fields_oblig_vide[] = $data_field;
									}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& !in_array($data_field,$tab['obligatory_fields'])){
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
									}
									$tab_fields[] = $data_field;
									$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if($cpt==$nb_oblig_data_entry_fields) $empty_row = false;
							if((($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)) || (($cpt == 0 || $cpt == 1) && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))){
							//if(($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)){
							
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
										$error_report .= "$field";
										$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if(isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]) && $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
								
							}
						}
						if(!$empty_row){
							$ligne = array();
							if(is_array($tables[$table]['val_cle']))
							foreach($tables[$table]['val_cle'] as $key_field => $key_val){
								$ligne[$key_field] = $key_val;
							}
							if(isset($exist_filtre) && $exist_filtre==true){
								$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
							}
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if(($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])))){
								$empty_table = false;
								maj_bdd($ligne,$tab,$row_excel);
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}
					//cas table matricielle : dimension colonne et assimulées
					else{
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ii = 0;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0){
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'')	$cpt++;
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']) && $i<count($tab['data_entry_fields']))	$tab_fields_oblig_vide[] = $data_field;
										if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>'')) 
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& in_array($data_field,$tab['obligatory_fields'])){
											$cpt--;
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
											$tab_fields_oblig_vide[] = $data_field;
										}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>''))
												&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
												&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
												&& !in_array($data_field,$tab['obligatory_fields'])){
												$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										}
										if($i<count($tab['data_entry_fields'])){
											$tab_fields[] = $data_field;
											$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										}
									}
									$i++;
									$ii++;
								}
							}
							if($cpt > 0) $empty_row = false;
							if($cpt == 0 && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
									$error_report .= "$field";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
							}
						}
						if(!$empty_row){
							$i=0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ligne = array();
								if(is_array($tables[$table]['val_cle']))
								foreach($tables[$table]['val_cle'] as $key_field => $key_val){
									$ligne[$key_field] = $key_val;
								}
								if(isset($exist_filtre) && $exist_filtre==true){
									$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
								}
								if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
								elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
								$empty_cells = true;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0 && trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''){
										$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										$empty_cells = false;
									}
									$i++;
								}
								if((!$empty_cells) && (($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))))){
									$empty_table = false;
									maj_bdd($ligne,$tab,$row_excel);
								}
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}	
				}
			}else{
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Chp_Oblig_Manquant_Gril')." ".$data->boundsheets[$sheet]['name']." ! ".recherche_libelle_page('Chp_Oblig_Manquant_Gril_Config')." : ".$tab['name'];
				$error_report .= "</td>\n";
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report; 

				//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
				if(isset($tab['main_table_mere']) && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields']) && $tab['name'] <> $tab['main_table_mere']){
					$ligne_del = array();
					$clause_where = "";
					if($tab['main_table_mere'] <> $GLOBALS['PARAM']['ENSEIGNANT']){
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne_del[$key_field] = $key_val;
						}
						$i = 0;
						foreach($ligne_del as $key=>$val){
							if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
								($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
								if($i==0){
									$clause_where .= " WHERE $key = $val_key ";
								}else{
									$clause_where .= " AND $key = $val_key ";
								}
								$i++;
							}
						}
						if($clause_where <> ""){
							$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
							$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
							if($nb_exist > 0){
								$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
								//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
								if ($GLOBALS['conn']->Execute($req_delete) === false){
									$GLOBALS['theme_data_MAJ_ok'] 	= false;
									$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
									$error_report .= "<tr>\n";
									$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
									$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
									$error_report .= "</td>\n";
									$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
									$error_report .= $req_delete;
									$error_report .= "</td>\n";
									$error_report .= "</tr>\n";
									$error_report .= "</table>\n";
									print $error_report; 
								}
							}
						}
					}else{
						foreach($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'] as $id_pers){
							$clause_where = " WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = $id_pers";
							$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].$clause_where;
							$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
							if($NB_ENS_ETAB == 0){
								$req_exist_ens = "SELECT COUNT(*) FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
								$nb_exist_ens = $GLOBALS['conn']->GetOne($req_exist_ens);
								if($nb_exist_ens > 0){
									$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
									//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
										$error_report .= "<tr>\n";
										$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
										$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$GLOBALS['PARAM']['ENSEIGNANT'];
										$error_report .= "</td>\n";
										$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
										$error_report .= $req_delete;
										$error_report .= "</td>\n";
										$error_report .= "</tr>\n";
										$error_report .= "</table>\n";
										print $error_report; 
									}
								}
							}
						}
					}	
				}
				//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}

		}
	}
	$cpt_del = 0;
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 4'){
			$table = 'MOBILIER_COLLECTIF_410';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$row_excel = 7;
			$cols_excel = array(7,10,13,16,19,22);
			$limit_empty_rows = $GLOBALS['PARAM']['IMPORT_GRILLE_LIMIT_VIDE'];//default limit of empty lines for stopping data integration 
			if($tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) && count($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) > 0){
				$tab_rows = array_keys($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']);
				$last_row = $tab_rows[count($tab_rows)-1];
				$limit_empty_rows = $last_row - $row_excel +1;
			}
			$empty_row = false;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields'])-$tables[$table]['nb_val_cle'];
			if($nb_oblig_data_entry_fields > 0){
				if($cpt_del == 0){
					$ligne_del = array();
					$table_del = $table;
					$tab_del = $tab;
					if($tables[$table]['name']==$GLOBALS['PARAM']['ENSEIGNANT']){
						$table_del = str_replace($GLOBALS['PARAM']['ENSEIGNANT'],$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'],$table);
						$tab_del = $tables[$table_del];
					}
					if(is_array($tables[$table_del]['val_cle']))
					foreach($tables[$table_del]['val_cle'] as $key_field => $key_val){
						$ligne_del[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne_del[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(($action = exist_bdd_grille($ligne_del,$tab_del)) == 'U') suppr_bdd_grille($ligne_del,$tab_del);
					$cpt_del++;
				}
				while(!$empty_row) {
					//cas table non matricielle
					if(!(isset($tab['records_tab_ref_col_dim']) && count($tab['records_tab_ref_col_dim'])>0)){
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'' && !preg_match("/^[\(\[\{\*]/",$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]]) && in_array($data_field,$tab['obligatory_fields']))	$cpt++;
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']))	$tab_fields_oblig_vide[] = $data_field;
									if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
										&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
										&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
										&& in_array($data_field,$tab['obligatory_fields'])){
										$cpt--;
										$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										$tab_fields_oblig_vide[] = $data_field;
									}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& !in_array($data_field,$tab['obligatory_fields'])){
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
									}
									$tab_fields[] = $data_field;
									$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if($cpt==$nb_oblig_data_entry_fields) $empty_row = false;
							if((($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)) || (($cpt == 0 || $cpt == 1) && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))){
							//if(($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)){
							
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
										$error_report .= "$field";
										$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if(isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]) && $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
								
							}
						}
						if(!$empty_row){
							$ligne = array();
							if(is_array($tables[$table]['val_cle']))
							foreach($tables[$table]['val_cle'] as $key_field => $key_val){
								$ligne[$key_field] = $key_val;
							}
							if(isset($exist_filtre) && $exist_filtre==true){
								$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
							}
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if(($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])))){
								$empty_table = false;
								maj_bdd($ligne,$tab,$row_excel);
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}
					//cas table matricielle : dimension colonne et assimulées
					else{
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ii = 0;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0){
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'')	$cpt++;
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']) && $i<count($tab['data_entry_fields']))	$tab_fields_oblig_vide[] = $data_field;
										if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>'')) 
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& in_array($data_field,$tab['obligatory_fields'])){
											$cpt--;
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
											$tab_fields_oblig_vide[] = $data_field;
										}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>''))
												&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
												&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
												&& !in_array($data_field,$tab['obligatory_fields'])){
												$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										}
										if($i<count($tab['data_entry_fields'])){
											$tab_fields[] = $data_field;
											$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										}
									}
									$i++;
									$ii++;
								}
							}
							if($cpt > 0) $empty_row = false;
							if($cpt == 0 && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
									$error_report .= "$field";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
							}
						}
						if(!$empty_row){
							$i=0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ligne = array();
								if(is_array($tables[$table]['val_cle']))
								foreach($tables[$table]['val_cle'] as $key_field => $key_val){
									$ligne[$key_field] = $key_val;
								}
								if(isset($exist_filtre) && $exist_filtre==true){
									$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
								}
								if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
								elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
								$empty_cells = true;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0 && trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''){
										$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										$empty_cells = false;
									}
									$i++;
								}
								if((!$empty_cells) && (($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))))){
									$empty_table = false;
									maj_bdd($ligne,$tab,$row_excel);
								}
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}	
				}
			}else{
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Chp_Oblig_Manquant_Gril')." ".$data->boundsheets[$sheet]['name']." ! ".recherche_libelle_page('Chp_Oblig_Manquant_Gril_Config')." : ".$tab['name'];
				$error_report .= "</td>\n";
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report; 

				//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
				if(isset($tab['main_table_mere']) && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields']) && $tab['name'] <> $tab['main_table_mere']){
					$ligne_del = array();
					$clause_where = "";
					if($tab['main_table_mere'] <> $GLOBALS['PARAM']['ENSEIGNANT']){
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne_del[$key_field] = $key_val;
						}
						$i = 0;
						foreach($ligne_del as $key=>$val){
							if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
								($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
								if($i==0){
									$clause_where .= " WHERE $key = $val_key ";
								}else{
									$clause_where .= " AND $key = $val_key ";
								}
								$i++;
							}
						}
						if($clause_where <> ""){
							$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
							$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
							if($nb_exist > 0){
								$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
								//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
								if ($GLOBALS['conn']->Execute($req_delete) === false){
									$GLOBALS['theme_data_MAJ_ok'] 	= false;
									$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
									$error_report .= "<tr>\n";
									$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
									$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
									$error_report .= "</td>\n";
									$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
									$error_report .= $req_delete;
									$error_report .= "</td>\n";
									$error_report .= "</tr>\n";
									$error_report .= "</table>\n";
									print $error_report; 
								}
							}
						}
					}else{
						foreach($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'] as $id_pers){
							$clause_where = " WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = $id_pers";
							$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].$clause_where;
							$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
							if($NB_ENS_ETAB == 0){
								$req_exist_ens = "SELECT COUNT(*) FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
								$nb_exist_ens = $GLOBALS['conn']->GetOne($req_exist_ens);
								if($nb_exist_ens > 0){
									$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
									//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
										$error_report .= "<tr>\n";
										$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
										$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$GLOBALS['PARAM']['ENSEIGNANT'];
										$error_report .= "</td>\n";
										$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
										$error_report .= $req_delete;
										$error_report .= "</td>\n";
										$error_report .= "</tr>\n";
										$error_report .= "</table>\n";
										print $error_report; 
									}
								}
							}
						}
					}	
				}
				//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}

		}
	}
	$cpt_del = 0;
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 4'){
			$table = 'MOBILIERS_ELEVES_410';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$row_excel = 7;
			$cols_excel = array(26,29,32,35,38,41);
			$limit_empty_rows = $GLOBALS['PARAM']['IMPORT_GRILLE_LIMIT_VIDE'];//default limit of empty lines for stopping data integration 
			if($tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) && count($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) > 0){
				$tab_rows = array_keys($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']);
				$last_row = $tab_rows[count($tab_rows)-1];
				$limit_empty_rows = $last_row - $row_excel +1;
			}
			$empty_row = false;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields'])-$tables[$table]['nb_val_cle'];
			if($nb_oblig_data_entry_fields > 0){
				if($cpt_del == 0){
					$ligne_del = array();
					$table_del = $table;
					$tab_del = $tab;
					if($tables[$table]['name']==$GLOBALS['PARAM']['ENSEIGNANT']){
						$table_del = str_replace($GLOBALS['PARAM']['ENSEIGNANT'],$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'],$table);
						$tab_del = $tables[$table_del];
					}
					if(is_array($tables[$table_del]['val_cle']))
					foreach($tables[$table_del]['val_cle'] as $key_field => $key_val){
						$ligne_del[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne_del[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(($action = exist_bdd_grille($ligne_del,$tab_del)) == 'U') suppr_bdd_grille($ligne_del,$tab_del);
					$cpt_del++;
				}
				while(!$empty_row) {
					//cas table non matricielle
					if(!(isset($tab['records_tab_ref_col_dim']) && count($tab['records_tab_ref_col_dim'])>0)){
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'' && !preg_match("/^[\(\[\{\*]/",$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]]) && in_array($data_field,$tab['obligatory_fields']))	$cpt++;
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']))	$tab_fields_oblig_vide[] = $data_field;
									if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
										&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
										&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
										&& in_array($data_field,$tab['obligatory_fields'])){
										$cpt--;
										$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										$tab_fields_oblig_vide[] = $data_field;
									}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& !in_array($data_field,$tab['obligatory_fields'])){
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
									}
									$tab_fields[] = $data_field;
									$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if($cpt==$nb_oblig_data_entry_fields) $empty_row = false;
							if((($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)) || (($cpt == 0 || $cpt == 1) && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))){
							//if(($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)){
							
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
										$error_report .= "$field";
										$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if(isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]) && $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
								
							}
						}
						if(!$empty_row){
							$ligne = array();
							if(is_array($tables[$table]['val_cle']))
							foreach($tables[$table]['val_cle'] as $key_field => $key_val){
								$ligne[$key_field] = $key_val;
							}
							if(isset($exist_filtre) && $exist_filtre==true){
								$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
							}
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if(($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])))){
								$empty_table = false;
								maj_bdd($ligne,$tab,$row_excel);
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}
					//cas table matricielle : dimension colonne et assimulées
					else{
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ii = 0;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0){
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'')	$cpt++;
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']) && $i<count($tab['data_entry_fields']))	$tab_fields_oblig_vide[] = $data_field;
										if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>'')) 
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& in_array($data_field,$tab['obligatory_fields'])){
											$cpt--;
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
											$tab_fields_oblig_vide[] = $data_field;
										}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>''))
												&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
												&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
												&& !in_array($data_field,$tab['obligatory_fields'])){
												$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										}
										if($i<count($tab['data_entry_fields'])){
											$tab_fields[] = $data_field;
											$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										}
									}
									$i++;
									$ii++;
								}
							}
							if($cpt > 0) $empty_row = false;
							if($cpt == 0 && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
									$error_report .= "$field";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
							}
						}
						if(!$empty_row){
							$i=0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ligne = array();
								if(is_array($tables[$table]['val_cle']))
								foreach($tables[$table]['val_cle'] as $key_field => $key_val){
									$ligne[$key_field] = $key_val;
								}
								if(isset($exist_filtre) && $exist_filtre==true){
									$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
								}
								if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
								elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
								$empty_cells = true;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0 && trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''){
										$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										$empty_cells = false;
									}
									$i++;
								}
								if((!$empty_cells) && (($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))))){
									$empty_table = false;
									maj_bdd($ligne,$tab,$row_excel);
								}
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}	
				}
			}else{
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Chp_Oblig_Manquant_Gril')." ".$data->boundsheets[$sheet]['name']." ! ".recherche_libelle_page('Chp_Oblig_Manquant_Gril_Config')." : ".$tab['name'];
				$error_report .= "</td>\n";
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report; 

				//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
				if(isset($tab['main_table_mere']) && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields']) && $tab['name'] <> $tab['main_table_mere']){
					$ligne_del = array();
					$clause_where = "";
					if($tab['main_table_mere'] <> $GLOBALS['PARAM']['ENSEIGNANT']){
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne_del[$key_field] = $key_val;
						}
						$i = 0;
						foreach($ligne_del as $key=>$val){
							if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
								($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
								if($i==0){
									$clause_where .= " WHERE $key = $val_key ";
								}else{
									$clause_where .= " AND $key = $val_key ";
								}
								$i++;
							}
						}
						if($clause_where <> ""){
							$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
							$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
							if($nb_exist > 0){
								$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
								//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
								if ($GLOBALS['conn']->Execute($req_delete) === false){
									$GLOBALS['theme_data_MAJ_ok'] 	= false;
									$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
									$error_report .= "<tr>\n";
									$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
									$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
									$error_report .= "</td>\n";
									$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
									$error_report .= $req_delete;
									$error_report .= "</td>\n";
									$error_report .= "</tr>\n";
									$error_report .= "</table>\n";
									print $error_report; 
								}
							}
						}
					}else{
						foreach($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'] as $id_pers){
							$clause_where = " WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = $id_pers";
							$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].$clause_where;
							$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
							if($NB_ENS_ETAB == 0){
								$req_exist_ens = "SELECT COUNT(*) FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
								$nb_exist_ens = $GLOBALS['conn']->GetOne($req_exist_ens);
								if($nb_exist_ens > 0){
									$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
									//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
										$error_report .= "<tr>\n";
										$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
										$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$GLOBALS['PARAM']['ENSEIGNANT'];
										$error_report .= "</td>\n";
										$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
										$error_report .= $req_delete;
										$error_report .= "</td>\n";
										$error_report .= "</tr>\n";
										$error_report .= "</table>\n";
										print $error_report; 
									}
								}
							}
						}
					}	
				}
				//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}

		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 4'){
			$table = 'EQUIPEMENT_DIDACT_420';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(33,33,33,35,35,35,37,37,37);
			$cols_excel = array(11,23,44,11,23,44,11,23,44);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 4'){
			$table = 'OUVRAGE_PEDAG_420';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(43,45,47,49,51,53,55,57,59,61);
			$cols_excel = array(11,15,19,23,27,32,38,44);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 4'){
			$table = 'MANUEL_DOTE_ETAT_420';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(68,70,72,74,76,78,80,82,84,86);
			$cols_excel = array(11,15,19,23,27,32,38,44);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 4'){
			$table = 'MANUEL_PROPRE_ELEVE_420';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(91,93,95,97,99,101,103,105,107,109);
			$cols_excel = array(11,15,19,23,27,32,38,44);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 4'){
			$table = 'DON_FOUR_NIVEAU_420';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(115,115,115,115,115,115,115,115);
			$cols_excel = array(11,15,19,23,27,32,38,44);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 5'){
			$table = 'DONNEES_FINANC_430';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(24,26,26,24,26,24,5);
			$cols_excel = array(29,29,38,52,52,38,36);
			$empty_row = true;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$k=0;
			$cpt = 0;
			$cpt_oblig = 0;
			$tab_fields = array();
			$tab_fields_oblig_vide = array();
			$tab_values = array();
			if(is_array($tab['data_entry_fields']))
			foreach ($tab['data_entry_fields'] as $data_field) {
				if($rows_excel[$k]<>0 && $cols_excel[$k]<>0){
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'')	{$cpt++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
					if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
						&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
						&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
						&& in_array($data_field,$tab['obligatory_fields_form'])){
						$cpt_oblig--;
						$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
						$tab_fields_oblig_vide[] = $data_field;
					}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
							&& !in_array($data_field,$tab['obligatory_fields_form'])){
							$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
					}
					$tab_fields[] = $data_field;
					$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
				}
				if($rows_excel[$k]==0 && $cols_excel[$k]==0){
					if(isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form'])){
						$tab_fields_oblig_vide[] = $data_field;
						$tab_fields[] = $data_field;
						$tab_values[] = '';
						if($data_field==$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] && $_GET['new_code_etab']<>'new_etab') $cpt_oblig++;
					}
				}
				$k++;
			}
			if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
			if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
				$error_report .= "</td>\n";
				foreach($tab_fields as $field){
					if(in_array($field,$tab_fields_oblig_vide))
						$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
					else
						$error_report .= "<td>\n";
						$error_report .= "$field";
						$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "<tr>\n";
				foreach($tab_values as $value){
					$error_report .= "<td align='center'>\n";
					$error_report .= "$value";
					$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report;
			}
			if(!$empty_row){
				$ligne = array();
				$tab_val_cle = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
					$tab_val_cle[] = $key_field;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$k]<>0 && $cols_excel[$k]<>0 && !in_array($data_field,$tab_val_cle)){
						$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
					}
					$k++;
				}
				$empty_table = false;
				maj_bdd($ligne,$tab);
			}else{
				$ligne = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				suppr_bdd($ligne,$tab);
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 5'){
			$table = 'DEPENSES_430';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(36,38,40,42,44,46,48,50,52,54,56);
			$cols_excel = array(16,27,38,53,65,76,85,94);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 5'){
			$table = 'SUBVENTIONS_430';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(7,9,13,15,17,19,21);
			$cols_excel = array(29,29,29,29,29,29,29);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_row_dim'] as $code) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	$cpt_del = 0;
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 6'){
			$table = 'PERSONNEL_440';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$row_excel = 4;
			$cols_excel = array(8,6,3,4,5,11,38,44);
			$limit_empty_rows = $GLOBALS['PARAM']['IMPORT_GRILLE_LIMIT_VIDE'];//default limit of empty lines for stopping data integration 
			if($tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) && count($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) > 0){
				$tab_rows = array_keys($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']);
				$last_row = $tab_rows[count($tab_rows)-1];
				$limit_empty_rows = $last_row - $row_excel +1;
			}
			$empty_row = false;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields'])-$tables[$table]['nb_val_cle'];
			if($nb_oblig_data_entry_fields > 0){
				if($cpt_del == 0){
					$ligne_del = array();
					$table_del = $table;
					$tab_del = $tab;
					if($tables[$table]['name']==$GLOBALS['PARAM']['ENSEIGNANT']){
						$table_del = str_replace($GLOBALS['PARAM']['ENSEIGNANT'],$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'],$table);
						$tab_del = $tables[$table_del];
					}
					if(is_array($tables[$table_del]['val_cle']))
					foreach($tables[$table_del]['val_cle'] as $key_field => $key_val){
						$ligne_del[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne_del[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(($action = exist_bdd_grille($ligne_del,$tab_del)) == 'U') suppr_bdd_grille($ligne_del,$tab_del);
					$cpt_del++;
				}
				while(!$empty_row) {
					//cas table non matricielle
					if(!(isset($tab['records_tab_ref_col_dim']) && count($tab['records_tab_ref_col_dim'])>0)){
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'' && !preg_match("/^[\(\[\{\*]/",$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]]) && in_array($data_field,$tab['obligatory_fields']))	$cpt++;
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']))	$tab_fields_oblig_vide[] = $data_field;
									if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
										&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
										&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
										&& in_array($data_field,$tab['obligatory_fields'])){
										$cpt--;
										$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										$tab_fields_oblig_vide[] = $data_field;
									}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& !in_array($data_field,$tab['obligatory_fields'])){
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
									}
									$tab_fields[] = $data_field;
									$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if($cpt==$nb_oblig_data_entry_fields) $empty_row = false;
							if((($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)) || (($cpt == 0 || $cpt == 1) && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))){
							//if(($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)){
							
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
										$error_report .= "$field";
										$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if(isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]) && $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
								
							}
						}
						if(!$empty_row){
							$ligne = array();
							if(is_array($tables[$table]['val_cle']))
							foreach($tables[$table]['val_cle'] as $key_field => $key_val){
								$ligne[$key_field] = $key_val;
							}
							if(isset($exist_filtre) && $exist_filtre==true){
								$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
							}
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if(($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])))){
								$empty_table = false;
								maj_bdd($ligne,$tab,$row_excel);
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}
					//cas table matricielle : dimension colonne et assimulées
					else{
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ii = 0;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0){
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'')	$cpt++;
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']) && $i<count($tab['data_entry_fields']))	$tab_fields_oblig_vide[] = $data_field;
										if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>'')) 
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& in_array($data_field,$tab['obligatory_fields'])){
											$cpt--;
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
											$tab_fields_oblig_vide[] = $data_field;
										}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>''))
												&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
												&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
												&& !in_array($data_field,$tab['obligatory_fields'])){
												$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										}
										if($i<count($tab['data_entry_fields'])){
											$tab_fields[] = $data_field;
											$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										}
									}
									$i++;
									$ii++;
								}
							}
							if($cpt > 0) $empty_row = false;
							if($cpt == 0 && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
									$error_report .= "$field";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
							}
						}
						if(!$empty_row){
							$i=0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ligne = array();
								if(is_array($tables[$table]['val_cle']))
								foreach($tables[$table]['val_cle'] as $key_field => $key_val){
									$ligne[$key_field] = $key_val;
								}
								if(isset($exist_filtre) && $exist_filtre==true){
									$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
								}
								if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
								elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
								$empty_cells = true;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0 && trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''){
										$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										$empty_cells = false;
									}
									$i++;
								}
								if((!$empty_cells) && (($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))))){
									$empty_table = false;
									maj_bdd($ligne,$tab,$row_excel);
								}
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}	
				}
			}else{
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Chp_Oblig_Manquant_Gril')." ".$data->boundsheets[$sheet]['name']." ! ".recherche_libelle_page('Chp_Oblig_Manquant_Gril_Config')." : ".$tab['name'];
				$error_report .= "</td>\n";
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report; 

				//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
				if(isset($tab['main_table_mere']) && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields']) && $tab['name'] <> $tab['main_table_mere']){
					$ligne_del = array();
					$clause_where = "";
					if($tab['main_table_mere'] <> $GLOBALS['PARAM']['ENSEIGNANT']){
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne_del[$key_field] = $key_val;
						}
						$i = 0;
						foreach($ligne_del as $key=>$val){
							if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
								($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
								if($i==0){
									$clause_where .= " WHERE $key = $val_key ";
								}else{
									$clause_where .= " AND $key = $val_key ";
								}
								$i++;
							}
						}
						if($clause_where <> ""){
							$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
							$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
							if($nb_exist > 0){
								$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
								//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
								if ($GLOBALS['conn']->Execute($req_delete) === false){
									$GLOBALS['theme_data_MAJ_ok'] 	= false;
									$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
									$error_report .= "<tr>\n";
									$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
									$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
									$error_report .= "</td>\n";
									$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
									$error_report .= $req_delete;
									$error_report .= "</td>\n";
									$error_report .= "</tr>\n";
									$error_report .= "</table>\n";
									print $error_report; 
								}
							}
						}
					}else{
						foreach($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'] as $id_pers){
							$clause_where = " WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = $id_pers";
							$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].$clause_where;
							$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
							if($NB_ENS_ETAB == 0){
								$req_exist_ens = "SELECT COUNT(*) FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
								$nb_exist_ens = $GLOBALS['conn']->GetOne($req_exist_ens);
								if($nb_exist_ens > 0){
									$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
									//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
										$error_report .= "<tr>\n";
										$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
										$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$GLOBALS['PARAM']['ENSEIGNANT'];
										$error_report .= "</td>\n";
										$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
										$error_report .= $req_delete;
										$error_report .= "</td>\n";
										$error_report .= "</tr>\n";
										$error_report .= "</table>\n";
										print $error_report; 
									}
								}
							}
						}
					}	
				}
				//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}

		}
	}
	$cpt_del = 0;
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 6'){
			$table = 'PERSONNEL_ETAB_440';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$row_excel = 4;
			$cols_excel = array(20,24,22,28,26,14,39,2,31,33,30,35,36,37,40,42,12,16,18);
			$limit_empty_rows = $GLOBALS['PARAM']['IMPORT_GRILLE_LIMIT_VIDE'];//default limit of empty lines for stopping data integration 
			if($tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) && count($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) > 0){
				$tab_rows = array_keys($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']);
				$last_row = $tab_rows[count($tab_rows)-1];
				$limit_empty_rows = $last_row - $row_excel +1;
			}
			$empty_row = false;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields'])-$tables[$table]['nb_val_cle'];
			if($nb_oblig_data_entry_fields > 0){
				if($cpt_del == 0){
					$ligne_del = array();
					$table_del = $table;
					$tab_del = $tab;
					if($tables[$table]['name']==$GLOBALS['PARAM']['ENSEIGNANT']){
						$table_del = str_replace($GLOBALS['PARAM']['ENSEIGNANT'],$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'],$table);
						$tab_del = $tables[$table_del];
					}
					if(is_array($tables[$table_del]['val_cle']))
					foreach($tables[$table_del]['val_cle'] as $key_field => $key_val){
						$ligne_del[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne_del[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(($action = exist_bdd_grille($ligne_del,$tab_del)) == 'U') suppr_bdd_grille($ligne_del,$tab_del);
					$cpt_del++;
				}
				while(!$empty_row) {
					//cas table non matricielle
					if(!(isset($tab['records_tab_ref_col_dim']) && count($tab['records_tab_ref_col_dim'])>0)){
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'' && !preg_match("/^[\(\[\{\*]/",$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]]) && in_array($data_field,$tab['obligatory_fields']))	$cpt++;
									if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']))	$tab_fields_oblig_vide[] = $data_field;
									if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
										&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
										&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
										&& in_array($data_field,$tab['obligatory_fields'])){
										$cpt--;
										$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										$tab_fields_oblig_vide[] = $data_field;
									}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& !in_array($data_field,$tab['obligatory_fields'])){
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
									}
									$tab_fields[] = $data_field;
									$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if($cpt==$nb_oblig_data_entry_fields) $empty_row = false;
							if((($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)) || (($cpt == 0 || $cpt == 1) && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))){
							//if(($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)){
							
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
										$error_report .= "$field";
										$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if(isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]) && $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
								
							}
						}
						if(!$empty_row){
							$ligne = array();
							if(is_array($tables[$table]['val_cle']))
							foreach($tables[$table]['val_cle'] as $key_field => $key_val){
								$ligne[$key_field] = $key_val;
							}
							if(isset($exist_filtre) && $exist_filtre==true){
								$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
							}
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($cols_excel[$i]<>0){
									$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
								}
								$i++;
							}
							if(($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])))){
								$empty_table = false;
								maj_bdd($ligne,$tab,$row_excel);
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}
					//cas table matricielle : dimension colonne et assimulées
					else{
						$empty_row = true;
						$k=1;
						while(($k<=$limit_empty_rows) && ($empty_row)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ii = 0;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0){
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>'')	$cpt++;
										if(trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])=='' && in_array($data_field,$tab['obligatory_fields']) && $i<count($tab['data_entry_fields']))	$tab_fields_oblig_vide[] = $data_field;
										if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>'')) 
											&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
											&& in_array($data_field,$tab['obligatory_fields'])){
											$cpt--;
											$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
											$tab_fields_oblig_vide[] = $data_field;
										}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>''))
												&& trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''
												&& !is_numeric($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])
												&& !in_array($data_field,$tab['obligatory_fields'])){
												$data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]] = '';
										}
										if($i<count($tab['data_entry_fields'])){
											$tab_fields[] = $data_field;
											$tab_values[] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										}
									}
									$i++;
									$ii++;
								}
							}
							if($cpt > 0) $empty_row = false;
							if($cpt == 0 && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel])){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." $row_excel : ".$data->boundsheets[$sheet]['name'];
								$error_report .= "</td>\n";
								foreach($tab_fields as $field){
									if(in_array($field,$tab_fields_oblig_vide))
										$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
									else
										$error_report .= "<td>\n";
									$error_report .= "$field";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "<tr>\n";
								foreach($tab_values as $value){
									$error_report .= "<td align='center'>\n";
									$error_report .= "$value";
									$error_report .= "</td>\n";
								}
								$error_report .= "</tr>\n";
								$error_report .= "</table>\n";
								print $error_report;
								
								//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
								if(isset($tab['main_table_mere']) && $tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
									$ligne_del = array();
									if(is_array($tables[$table]['val_cle']))
									foreach($tables[$table]['val_cle'] as $key_field => $key_val){
										$ligne_del[$key_field] = $key_val;
									}
									if(isset($tab['incr_field']) && $tab['incr_field']<>""){
										if($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel];
										}
									}
									$clause_where = "";
									$i = 0;
									if(isset($ligne_del[$tab['incr_field']]) && $ligne_del[$tab['incr_field']]<>"")
									foreach($ligne_del as $key=>$val){
										if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
											($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
											if($i==0){
												$clause_where .= " WHERE $key = $val_key ";
											}else{
												$clause_where .= " AND $key = $val_key ";
											}
											$i++;
										}
									}
									if($clause_where <> ""){
										$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
										$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
										if($nb_exist > 0){
											$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
											//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
											if ($GLOBALS['conn']->Execute($req_delete) === false){
												$GLOBALS['theme_data_MAJ_ok'] 	= false;
												$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
												$error_report .= "<tr>\n";
												$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
												$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
												$error_report .= "</td>\n";
												$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
												$error_report .= $req_delete;
												$error_report .= "</td>\n";
												$error_report .= "</tr>\n";
												$error_report .= "</table>\n";
												print $error_report; 
											}
										}
									}
								}
								//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
							}
							if($tab['name'] == $tab['main_table_mere'])	$k++;
							if($empty_row){
								$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
							}
						}
						if(!$empty_row){
							$i=0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ligne = array();
								if(is_array($tables[$table]['val_cle']))
								foreach($tables[$table]['val_cle'] as $key_field => $key_val){
									$ligne[$key_field] = $key_val;
								}
								if(isset($exist_filtre) && $exist_filtre==true){
									$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
								}
								if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
								elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
								$empty_cells = true;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($cols_excel[$i]<>0 && trim($data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]])<>''){
										$ligne[$data_field] = $data->sheets[$sheet]['cells'][$row_excel][$cols_excel[$i]];
										$empty_cells = false;
									}
									$i++;
								}
								if((!$empty_cells) && (($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$row_excel]))))){
									$empty_table = false;
									maj_bdd($ligne,$tab,$row_excel);
								}
							}
							$row_excel = $row_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_LIGNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_rows--;
						}
					}	
				}
			}else{
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Chp_Oblig_Manquant_Gril')." ".$data->boundsheets[$sheet]['name']." ! ".recherche_libelle_page('Chp_Oblig_Manquant_Gril_Config')." : ".$tab['name'];
				$error_report .= "</td>\n";
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report; 

				//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
				if(isset($tab['main_table_mere']) && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields']) && $tab['name'] <> $tab['main_table_mere']){
					$ligne_del = array();
					$clause_where = "";
					if($tab['main_table_mere'] <> $GLOBALS['PARAM']['ENSEIGNANT']){
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne_del[$key_field] = $key_val;
						}
						$i = 0;
						foreach($ligne_del as $key=>$val){
							if(in_array($key,$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'])){
								($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
								if($i==0){
									$clause_where .= " WHERE $key = $val_key ";
								}else{
									$clause_where .= " AND $key = $val_key ";
								}
								$i++;
							}
						}
						if($clause_where <> ""){
							$req_exist = "SELECT COUNT(*) FROM ".$tab['main_table_mere'].$clause_where;
							$nb_exist = $GLOBALS['conn']->GetOne($req_exist);
							if($nb_exist > 0){
								$req_delete = "DELETE FROM ".$tab['main_table_mere'].$clause_where;
								//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
								if ($GLOBALS['conn']->Execute($req_delete) === false){
									$GLOBALS['theme_data_MAJ_ok'] 	= false;
									$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
									$error_report .= "<tr>\n";
									$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
									$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['main_table_mere'];
									$error_report .= "</td>\n";
									$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
									$error_report .= $req_delete;
									$error_report .= "</td>\n";
									$error_report .= "</tr>\n";
									$error_report .= "</table>\n";
									print $error_report; 
								}
							}
						}
					}else{
						foreach($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'] as $id_pers){
							$clause_where = " WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = $id_pers";
							$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].$clause_where;
							$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
							if($NB_ENS_ETAB == 0){
								$req_exist_ens = "SELECT COUNT(*) FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
								$nb_exist_ens = $GLOBALS['conn']->GetOne($req_exist_ens);
								if($nb_exist_ens > 0){
									$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT'].$clause_where;
									//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
										$error_report .= "<tr>\n";
										$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
										$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$GLOBALS['PARAM']['ENSEIGNANT'];
										$error_report .= "</td>\n";
										$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
										$error_report .= $req_delete;
										$error_report .= "</td>\n";
										$error_report .= "</tr>\n";
										$error_report .= "</table>\n";
										print $error_report; 
									}
								}
							}
						}
					}	
				}
				//Fin Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}

		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 7'){
			$table = 'GROUPE_PEDAGOGIQUE_450';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(23,23,8,6,23,23,8,6,23,23,8,6,23,23,8,6,23,23,8,6,23,23,8,6,23,23,8,6,23,23,8,6);
			$cols_excel = array(4,5,4,4,6,7,6,6,10,11,10,10,12,13,12,12,14,15,14,14,18,19,18,18,20,21,20,20,22,23,22,22);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 7'){
			$table = 'EFFECTIF_AGE_450';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(10,10,11,11,12,12,13,13,14,14,15,15,16,16,17,17,18,18,19,19,20,20,21,21);
			$cols_excel = array(4,5,6,7,10,11,12,13,14,15,18,19,20,21,22,23);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 7'){
			$table = 'EFFECTIF_PRO_PARENT_450';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(33,33,34,34,35,35,36,36,37,37,38,38,39,39,0,0,40,40);
			$cols_excel = array(4,5,6,7,10,11,12,13,14,15,18,19,20,21,22,23);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 8'){
			$table = 'AIRE_RECRUTEMENT_470';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(8,8,10,10,12,12);
			$cols_excel = array(11,14,17,20,23,26);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 8'){
			$table = 'NOUVEAU_1ERE_ANNEE_470';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(24,25,24,25,24,25,24,25,24,25,24,25,24,25,24,25,24,25,24,25,24,25,24,24);
			$cols_excel = array(7,7,11,11,13,13,16,16,18,18,21,21,24,24,27,27,30,30,33,33,36,36,39,39);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
					elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 8'){
			$table = 'EFFECTIF_ELEVE_500';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(40,40,40,40,40,40,40,40,40,41,41,41,41,41,41,41,41,41,42,42,42,42,42,42,42,42,42);
			$cols_excel = array(6,9,15,18,4,35,33,28,26,6,9,15,18,4,35,33,28,26,6,9,15,18,4,35,33,28,26);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_row_dim'] as $code) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 9'){
			$table = 'EFFECTIF_ELEVE_DEFICIENT_610';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12);
			$cols_excel = array(3,4,5,6,9,10,11,12,13,14,17,18,19,20,21,22);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 9'){
			$table = 'BENEFICIAIRES_OUTILS_BRAILLE_610';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(14,14,15,15);
			$cols_excel = array(3,4,5,6,9,10,11,12,13,14,17,18,19,20,21,22);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 9'){
			$table = 'LISTE_ETAB_PROCHE_610';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(31,31,32,32,33,33);
			$cols_excel = array(13,23,13,23,13,23);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_row_dim'] as $code) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 9'){
			$table = 'EFFECTIF_ABANDON_610';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(22,22,24,24,23,23,25,25,26,26,0,0,0,0,0,0,0,0,27,27);
			$cols_excel = array(3,4,5,6,9,10,11,12,13,14,17,18,19,20,21,22);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE 9'){
			$table = 'RESULTAT_EXAMEN_610';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(42,42,42,42,43,43,43,43,44,44,44,44,45,45,45,45,47,47,47,47);
			$cols_excel = array(4,6,7,3,10,12,13,9,16,18,19,15);
			$j = 0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_col_dim'] as $code_col) {
				$k = 0;
				foreach($tab['records_tab_ref_row_dim'] as $code) {
					$empty_row = true;
					$i = 0;
					$cpt = 0;
					$cpt_oblig = 0;
					$tab_fields = array();
					$tab_fields_oblig_vide = array();
					$tab_values = array();
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'')	{$cpt++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
							if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
							if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
								&& in_array($data_field,$tab['obligatory_fields_form'])){
								$cpt_oblig--;
								$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
								$tab_fields_oblig_vide[] = $data_field;
							}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
									&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])<>''
									&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]])
									&& !in_array($data_field,$tab['obligatory_fields_form'])){
									$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]] = '';
							}
							$tab_fields[] = $data_field;
							$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
						}
						$i++;
						$j++;
						$k++;
					}
					if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
					if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab_fields_oblig_vide))
								$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
							else
								$error_report .= "<td>\n";
								$error_report .= "$field";
								$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "<tr>\n";
						foreach($tab_values as $value){
							$error_report .= "<td align='center'>\n";
							$error_report .= "$value";
							$error_report .= "</td>\n";
						}
						$error_report .= "</tr>\n";
						$error_report .= "</table>\n";
						print $error_report;
					}
					if(!$empty_row){	
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						$j -= $i;
						$k -= $i;
						if(is_array($tab['data_entry_fields']))
						foreach ($tab['data_entry_fields'] as $data_field) {
							if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
								$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$j]];
							}
							$j++;
							$k++;
						}
						$empty_table = false;
						maj_bdd($ligne,$tab);
					}else{
						$ligne = array();
						if(is_array($tables[$table]['val_cle']))
						foreach($tables[$table]['val_cle'] as $key_field => $key_val){
							$ligne[$key_field] = $key_val;
						}
						if(isset($exist_filtre) && $exist_filtre==true){
							$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
						}
						if(isset($tab['chp_pere_tab_ref_col_dim']) && $tab['chp_pere_tab_ref_col_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_col_dim']] = $code_col;}
						elseif(isset($tab['tab_ref_col_dim']) && $tab['tab_ref_col_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] = $code_col;}
						if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
						elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
						suppr_bdd($ligne,$tab);
					}
					$j -= $i;
				}
				$j += $i;
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE10'){
			$table = 'INFRASTRUCTURES_TECHNOLOGIQUES_550';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(4,4,5,6,7,8,16,9,10,12);
			$cols_excel = array(11,14,11,11,11,11,14,11,11,12);
			$empty_row = true;
			$empty_table = true;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$k=0;
			$cpt = 0;
			$cpt_oblig = 0;
			$tab_fields = array();
			$tab_fields_oblig_vide = array();
			$tab_values = array();
			if(is_array($tab['data_entry_fields']))
			foreach ($tab['data_entry_fields'] as $data_field) {
				if($rows_excel[$k]<>0 && $cols_excel[$k]<>0){
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'')	{$cpt++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
					if(trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
					if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
						&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
						&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
						&& in_array($data_field,$tab['obligatory_fields_form'])){
						$cpt_oblig--;
						$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
						$tab_fields_oblig_vide[] = $data_field;
					}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]])
							&& !in_array($data_field,$tab['obligatory_fields_form'])){
							$data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]] = '';
					}
					$tab_fields[] = $data_field;
					$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
				}
				if($rows_excel[$k]==0 && $cols_excel[$k]==0){
					if(isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form'])){
						$tab_fields_oblig_vide[] = $data_field;
						$tab_fields[] = $data_field;
						$tab_values[] = '';
						if($data_field==$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] && $_GET['new_code_etab']<>'new_etab') $cpt_oblig++;
					}
				}
				$k++;
			}
			if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
			if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
				$error_report .= "</td>\n";
				foreach($tab_fields as $field){
					if(in_array($field,$tab_fields_oblig_vide))
						$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
					else
						$error_report .= "<td>\n";
						$error_report .= "$field";
						$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "<tr>\n";
				foreach($tab_values as $value){
					$error_report .= "<td align='center'>\n";
					$error_report .= "$value";
					$error_report .= "</td>\n";
				}
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report;
			}
			if(!$empty_row){
				$ligne = array();
				$tab_val_cle = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
					$tab_val_cle[] = $key_field;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				$k=0;
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$k]<>0 && $cols_excel[$k]<>0 && !in_array($data_field,$tab_val_cle)){
						$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$k]][$cols_excel[$k]];
					}
					$k++;
				}
				$empty_table = false;
				maj_bdd($ligne,$tab);
			}else{
				$ligne = array();
				if(is_array($tables[$table]['val_cle']))
				foreach($tables[$table]['val_cle'] as $key_field => $key_val){
					$ligne[$key_field] = $key_val;
				}
				if(isset($exist_filtre) && $exist_filtre==true){
					$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
				}
				suppr_bdd($ligne,$tab);
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}
	for($sheet=0;$sheet<count($data->sheets);$sheet++){
		if(strtoupper(substr($data->boundsheets[$sheet]['name'],0,6))=='PAGE10'){
			$table = 'MATERIEL_EQUIPEMENT_TECHNO_550';
			$exist_filtre = false;
			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['tab_ref_row_dim'])) $tab['tab_ref_row_dim'] = $tables[$table]['tab_ref_row_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_row_dim'])) $tab['chp_pere_tab_ref_row_dim'] = $tables[$table]['chp_pere_tab_ref_row_dim'];
			if(isset($tables[$table]['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = $tables[$table]['records_tab_ref_row_dim'];
			if(isset($tables[$table]['tab_ref_col_dim'])) $tab['tab_ref_col_dim'] = $tables[$table]['tab_ref_col_dim'];
			if(isset($tables[$table]['chp_pere_tab_ref_col_dim'])) $tab['chp_pere_tab_ref_col_dim'] = $tables[$table]['chp_pere_tab_ref_col_dim'];
			if(isset($tables[$table]['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = $tables[$table]['records_tab_ref_col_dim'];
			if(isset($tables[$table]['keys_fields'])){
				if($tables[$table]['type_theme']<>2 && $tables[$table]['type_theme']<>4)	$tab['obligatory_fields'] = $tables[$table]['keys_fields'];
				else $tab['obligatory_fields'] = $tables[$table]['obligatory_fields'];
			}
			if(isset($tables[$table]['data_entry_fields'])) $tab['data_entry_fields'] = $tables[$table]['data_entry_fields'];
			if(isset($tables[$table]['field_table_ref'])) $tab['field_table_ref'] = $tables[$table]['field_table_ref'];
			if(isset($tables[$table]['main_table_mere'])) $tab['main_table_mere'] = $tables[$table]['main_table_mere'];
			if(isset($tables[$table]['exist_obligatory_ctrl'])) $tab['exist_obligatory_ctrl'] = $tables[$table]['exist_obligatory_ctrl'];
			if(isset($tables[$table]['obligatory_fields_form'])) $tab['obligatory_fields_form'] = $tables[$table]['obligatory_fields_form'];
			if(isset($tables[$table]['id_theme'])){
				$tab['id_theme'] = $tables[$table]['id_theme'];
				if(!in_array($tables[$table]['id_theme'], $tab_themes)){
					$tab_themes[] = $tables[$table]['id_theme'];
					if(isset($_SESSION['incr_keys'][$tables[$table]['id_theme']])) unset($_SESSION['incr_keys'][$tables[$table]['id_theme']]);
				}
			}
			
			$rows_excel = array(20,20,20,21,21,21,22,22,22,23,23,23,24,24,24,25,25,25,26,26,26,27,27,27,28,28,28,29,29,29,30,30,30,31,31,31,32,32,32,33,33,33,34,34,34);
			$cols_excel = array(10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13,10,11,13);
			$i=0;
			$nb_oblig_data_entry_fields = count($tab['obligatory_fields_form']);
			$empty_table = true;
			foreach($tab['records_tab_ref_row_dim'] as $code) {
				$empty_row = true;
				$k=0;
				$cpt = 0;
				$cpt_oblig = 0;
				$tab_fields = array();
				$tab_fields_oblig_vide = array();
				$tab_values = array();
				if(is_array($tab['data_entry_fields']))
				foreach ($tab['data_entry_fields'] as $data_field) {
					if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'')	{$cpt++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>'' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	{$cpt_oblig++;}
						if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])=='' && isset($tab['obligatory_fields_form']) && in_array($data_field,$tab['obligatory_fields_form']))	$tab_fields_oblig_vide[] = $data_field;
						if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>'')) 
							&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
							&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
							&& in_array($data_field,$tab['obligatory_fields_form'])){
							$cpt_oblig--;
							$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
							$tab_fields_oblig_vide[] = $data_field;
						}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$k]) && $tab['field_table_ref'][$k]<>''))
								&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])<>''
								&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]])
								&& !in_array($data_field,$tab['obligatory_fields_form'])){
								$data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]] = '';
						}
						$tab_fields[] = $data_field;
						$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
					}
					$k++;
					$i++;
				}
				if($cpt_oblig == $nb_oblig_data_entry_fields && $cpt > 0)	$empty_row = false;
				if(($cpt > 0) && ($cpt_oblig < $nb_oblig_data_entry_fields)){
					$GLOBALS['theme_data_MAJ_ok'] 	= false;
					$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
					$error_report .= "<tr>\n";
					$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
					$error_report .= recherche_libelle_page('Donnee_Manquante_Lig')." : ".$data->boundsheets[$sheet]['name'];
					$error_report .= "</td>\n";
					foreach($tab_fields as $field){
						if(in_array($field,$tab_fields_oblig_vide))
							$error_report .= "<td style='color:#FF0000; font-weight: bold'>\n";
						else
							$error_report .= "<td>\n";
							$error_report .= "$field";
							$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "<tr>\n";
					foreach($tab_values as $value){
						$error_report .= "<td align='center'>\n";
						$error_report .= "$value";
						$error_report .= "</td>\n";
					}
					$error_report .= "</tr>\n";
					$error_report .= "</table>\n";
					print $error_report;
				}
				if(!$empty_row){	
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					$i -= $k;
					if(is_array($tab['data_entry_fields']))
					foreach ($tab['data_entry_fields'] as $data_field) {
						if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
							$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$cols_excel[$i]];
						}
						$i++;
					}
					$empty_table = false;
					maj_bdd($ligne,$tab);
				}else{
					$ligne = array();
					if(is_array($tables[$table]['val_cle']))
					foreach($tables[$table]['val_cle'] as $key_field => $key_val){
						$ligne[$key_field] = $key_val;
					}
					if(isset($exist_filtre) && $exist_filtre==true){
						$ligne[$champ_filtre] = $data->sheets[$sheet]['cells'][$num_lig_filtre][$num_col_filtre];
					}
					if(isset($tab['chp_pere_tab_ref_row_dim']) && $tab['chp_pere_tab_ref_row_dim']<>''){ $ligne[$tab['chp_pere_tab_ref_row_dim']] = $code;}
					elseif(isset($tab['tab_ref_row_dim']) && $tab['tab_ref_row_dim']<>''){ $ligne[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] = $code;}
					suppr_bdd($ligne,$tab);
				}
			}
			if($empty_table){
				$GLOBALS['theme_data_MAJ_ok'] 	= false;
				$error_report = "<p style='color:#0000FF; border-color:#000000'>".recherche_libelle_page('Empty_Table_Sheet')." ".$tab['sheet']." ".recherche_libelle_page('Empty_Table_Sheet_2')." ".$tab['name']."</p>\n";
				print $error_report;
			}
		}
	}

}
?>
