			$tab = array();
			$tab['sheet'] = $data->boundsheets[$sheet]['name'];
			if(isset($tables[$table]['name'])) $tab['name'] = $tables[$table]['name'];
			if(isset($tables[$table]['type_fields'])) $tab['type_fields'] = $tables[$table]['type_fields'];
			if(isset($tables[$table]['keys_fields'])) $tab['keys_fields'] = $tables[$table]['keys_fields'];
			if(isset($tables[$table]['incr_field'])) $tab['incr_field'] = $tables[$table]['incr_field'];
			if(isset($tables[$table]['incr_fields'])) $tab['incr_fields'] = $tables[$table]['incr_fields'];
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
			