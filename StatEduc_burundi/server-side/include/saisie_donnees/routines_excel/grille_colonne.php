			$limit_empty_cols = $GLOBALS['PARAM']['IMPORT_GRILLE_LIMIT_VIDE'];//default limit of empty lines for stopping data integration 
			if($tab['name'] <> $tab['main_table_mere'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) && count($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']) > 0){
				$tab_cols = array_keys($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values']);
				$last_col = $tab_cols[count($tab_cols)-1];
				$limit_empty_cols = $last_col - $col_excel +1;
			}
			$empty_col = false;
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
				while(!$empty_col) {
					//cas table non matricielle
					if(!(isset($tab['records_tab_ref_col_dim']) && count($tab['records_tab_ref_col_dim'])>0)){
						$empty_col = true;
						$k=1;
						while(($k<=$limit_empty_cols) && ($empty_col)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							if(is_array($tab['data_entry_fields']))
							foreach ($tab['data_entry_fields'] as $data_field) {
								if($rows_excel[$i]<>0){
									if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])<>'' && !preg_match("/^[\(\[\{\*]/",$data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel]) && in_array($data_field,$tab['obligatory_fields']))	$cpt++;
									if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])=='' && in_array($data_field,$tab['obligatory_fields']))	$tab_fields_oblig_vide[] = $data_field;
									if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>'')) 
										&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])<>''
										&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])
										&& in_array($data_field,$tab['obligatory_fields'])){
										$cpt--;
										$data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel] = '';
										$tab_fields_oblig_vide[] = $data_field;
									}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$i]) && $tab['field_table_ref'][$i]<>''))
											&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])
											&& !in_array($data_field,$tab['obligatory_fields'])){
											$data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel] = '';
									}
									$tab_fields[] = $data_field;
									$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel];
								}
								$i++;
							}
							if($cpt==$nb_oblig_data_entry_fields) $empty_col = false;
							if((($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)) || (($cpt == 0 || $cpt == 1) && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$col_excel]))){
							//if(($cpt > 1) && ($cpt < $nb_oblig_data_entry_fields)){
							
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Col')." $col_excel ".recherche_libelle_page('Donnee_Manquante_Num_Ligne')." ".$rows_excel[0].", ".$rows_excel[$i-1]." : ".$data->boundsheets[$sheet]['name'];
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
										if(isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$col_excel]) && $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$col_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$col_excel];
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
							if($empty_col){
								$col_excel = $col_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_COLONNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_cols--;
								
							}
						}
						if(!$empty_col){
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
								if($rows_excel[$i]<>0){
									$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel];
								}
								$i++;
							}
							if(($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$col_excel])))){
								$empty_table = false;
								maj_bdd($ligne,$tab,$col_excel);
							}
							$col_excel = $col_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_COLONNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_cols--;
						}
					}
					//cas table matricielle : dimension colonne et assimulées
					else{
						$empty_col = true;
						$k=1;
						while(($k<=$limit_empty_cols) && ($empty_col)){
							$cpt = 0;
							$tab_fields = array();
							$tab_fields_oblig_vide = array();
							$tab_values = array();
							$i = 0;
							foreach($tab['records_tab_ref_col_dim'] as $code_col) {
								$ii = 0;
								if(is_array($tab['data_entry_fields']))
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($rows_excel[$i]<>0){
										if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])<>'')	$cpt++;
										if(trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])=='' && in_array($data_field,$tab['obligatory_fields']) && $i<count($tab['data_entry_fields']))	$tab_fields_oblig_vide[] = $data_field;
										if((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>'')) 
											&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])<>''
											&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])
											&& in_array($data_field,$tab['obligatory_fields'])){
											$cpt--;
											$data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel] = '';
											$tab_fields_oblig_vide[] = $data_field;
										}elseif((preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$data_field) || (isset($tab['field_table_ref'][$ii]) && $tab['field_table_ref'][$ii]<>''))
												&& trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])<>''
												&& !is_numeric($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])
												&& !in_array($data_field,$tab['obligatory_fields'])){
												$data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel] = '';
										}
										if($i<count($tab['data_entry_fields'])){
											$tab_fields[] = $data_field;
											$tab_values[] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel];
										}
									}
									$i++;
									$ii++;
								}
							}
							if($cpt > 0) $empty_col = false;
							if($cpt == 0 && isset($tab['exist_obligatory_ctrl']) && $tab['exist_obligatory_ctrl'] && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$col_excel])){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								$error_report = "<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
								$error_report .= "<tr>\n";
								$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
								$error_report .= recherche_libelle_page('Donnee_Manquante_Col')." $col_excel ".recherche_libelle_page('Donnee_Manquante_Num_Ligne')." ".$rows_excel[0].", ".$rows_excel[$i-1]." : ".$data->boundsheets[$sheet]['name'];
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
										if($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$col_excel]<>""){
											$ligne_del[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$col_excel];
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
							if($empty_col){
								$col_excel = $col_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_COLONNE_STEP'];
								if($tab['name'] <> $tab['main_table_mere']) $limit_empty_cols--;
							}
						}
						if(!$empty_col){
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
									if($rows_excel[$i]<>0 && trim($data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel])<>''){
										$ligne[$data_field] = $data->sheets[$sheet]['cells'][$rows_excel[$i]][$col_excel];
										$empty_cells = false;
									}
									$i++;
								}
								if((!$empty_cells) && (($tab['name'] == $tab['main_table_mere']) || (($tab['name'] <> $tab['main_table_mere']) && (isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$col_excel]))))){
									$empty_table = false;
									maj_bdd($ligne,$tab,$col_excel);
								}
							}
							$col_excel = $col_excel + $GLOBALS['PARAM']['IMPORT_GRILLE_COLONNE_STEP'];
							if($tab['name'] <> $tab['main_table_mere']) $limit_empty_cols--;
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
