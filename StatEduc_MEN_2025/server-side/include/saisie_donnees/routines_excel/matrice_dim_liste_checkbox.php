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
