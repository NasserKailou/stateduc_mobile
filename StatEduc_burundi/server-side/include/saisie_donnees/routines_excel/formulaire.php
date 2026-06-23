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