<?php 
//Verifier s'il ya des lignes de donnees pr la grille dans la base
function exist_bdd_grille($ligne,$tab){
	$clause_where = "";
	$i = 0;
	foreach($ligne as $key=>$val){
		($tab['type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
		if($i==0){
			$clause_where .= " WHERE $key = $val_key ";
		}else{
			$clause_where .= " AND $key = $val_key ";
		}
		$i++;
	}
	$req_verif = "SELECT * FROM ".$tab['name'].$clause_where;
	//echo '<BR> ---req_verif--- <BR>'.$req_verif.'<BR>';
	$res_verif = $GLOBALS['conn']->GetAll($req_verif);
	$action = 'I';
	if(is_array($res_verif) && count($res_verif)>0){
		$action = 'U';
	}
	return $action;
}
///Suppression donnees table grille avant eventuelle reinsertion
function suppr_bdd_grille($ligne,$tab){
	$clause_where = "";
	$i = 0;
	foreach($ligne as $key=>$val){
		($tab['type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
		if($i==0){
			$clause_where .= " WHERE $key = $val_key ";
		}else{
			$clause_where .= " AND $key = $val_key ";
		}
		$i++;
	}
	$res_exist_ens_etab = array();
	if($tab['name']==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
		$req_exist_ens_etab = "SELECT ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].$clause_where.";";
		$res_exist_ens_etab = $GLOBALS['conn']->GetAll($req_exist_ens_etab);
	}
	$res_exist_pers_etab = array();
	if($tab['name']==$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']){
		$req_exist_pers_etab = "SELECT ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT'].$clause_where.";";
		$res_exist_pers_etab = $GLOBALS['conn']->GetAll($req_exist_pers_etab);
	}	
	$req_delete = "DELETE FROM ".$tab['name'].$clause_where;
	//echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
	if ($GLOBALS['conn']->Execute($req_delete) === false){
		$GLOBALS['theme_data_MAJ_ok'] 	= false;
		$error_report = "$req_delete<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
		$error_report .= "<tr>\n";
		$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
		$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$tab['name']." : ".$tab['sheet'];
		$error_report .= "</td>\n";
		$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
		$error_report .= $req_delete;
		$error_report .= "</td>\n";
		$error_report .= "</tr>\n";
		$error_report .= "</table>\n";
		print $error_report; 
	}elseif($tab['name']==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'] || $tab['name']==$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']){//On supprime les enseignants dans la table enseignant qui ne sont rattachés à aucun autre etablissement ou année
		if(count($res_exist_ens_etab)>0){	
			foreach($res_exist_ens_etab as $val_id_enseign){
				$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$val_id_enseign[$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']];
				$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
				if($NB_ENS_ETAB == 0){
					$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$val_id_enseign[$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']];
					if ($GLOBALS['conn']->Execute($req_delete) === false){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "$req_delete<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
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
		if(count($res_exist_pers_etab)>0){	
			foreach($res_exist_pers_etab as $val_id_pers){
				$req_exist_pers_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN'].") AS NB_PERS_ETAB FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." = ".$val_id_pers[$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']];
				$NB_PERS_ETAB = $GLOBALS['conn']->GetOne($req_exist_pers_etab);
				if($NB_PERS_ETAB == 0){
					$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." = ".$val_id_pers[$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']];
					if ($GLOBALS['conn']->Execute($req_delete) === false){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "$req_delete<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Err_Suppr_Modif')." ".$GLOBALS['PARAM']['PERSONNEL_ADMIN'];
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
//Verifier si la ligne est deja dans la base
function exist_dans_bdd($ligne,$tab){
	$clause_where = "";
	$i = 0;
	foreach($ligne as $key=>$val){
		if(in_array($key,$tab['obligatory_fields'])){
			($tab['type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
			if($i==0){
				$clause_where .= " WHERE $key = $val_key ";
			}else{
				$clause_where .= " AND $key = $val_key ";
			}
			$i++;
		}
	}
	$req_verif = "SELECT * FROM ".$tab['name'].$clause_where;
	//echo '<BR> ---req_verif--- <BR>'.$req_verif.'<BR>';
	$res_verif = $GLOBALS['conn']->GetAll($req_verif);
	$action = 'I';
	if(is_array($res_verif) && count($res_verif)>0){
		$action = 'U';
	}
	return $action;
}

function get_plage_codes(){
	$_SESSION['tab_plage'] = array();
	if (in_array('DICO_PLAGE_CODES',array_map('strtoupper',$GLOBALS['conn_dico']->MetaTables('TABLES')))){
		$ColTab	=	$GLOBALS['conn_dico']->MetaColumnNames('DICO_PLAGE_CODES');  
		$cles=array_keys($ColTab); 
		if(count($cles)==4){
			$requete 	= 'SELECT * FROM DICO_PLAGE_CODES';
			$all_res	= $GLOBALS['conn_dico']->GetAll($requete);
			if(is_array($all_res) && count($all_res)>0){
				foreach ($all_res as $i => $plg){
						$_SESSION['tab_plage'][$plg['NOM_CHAMP']] = array( 'val_min' => $plg['VAL_MIN'] , 'val_max' => $plg['VAL_MAX'] );
				}
			}
		}elseif(count($cles)>4){
			//Ajout hebie pour gestion plage automatique
			$requete        = 'SELECT CODE_TYPE_CHAINE FROM DICO_CHAINE_LOCALISATION
								 WHERE CODE_TYPE_SYSTEME_ENSEIGNEMENT='.$_SESSION['secteur'].' 
								 ORDER BY ORDRE_TYPE_CHAINE';
			$tab_chaines  = $GLOBALS['conn_dico']->GetAll($requete);
			$req_type_regroup_plage = "SELECT DISTINCT CODE_TYPE_REGROUP FROM DICO_PLAGE_CODES";
			$res_type_regroup_plage = $GLOBALS['conn_dico']->GetAll($req_type_regroup_plage);
			$tab_type_regroup_plage = array();
			$tab_non_type_regroup_plage = array();
			if(is_array($res_type_regroup_plage)){
				foreach($res_type_regroup_plage as $type_regroup_plage){
					($type_regroup_plage['CODE_TYPE_REGROUP']<>0)? ($tab_type_regroup_plage[] = $type_regroup_plage['CODE_TYPE_REGROUP']):($tab_non_type_regroup_plage[] = $type_regroup_plage['CODE_TYPE_REGROUP']);
				}
			}
			if(count($tab_type_regroup_plage)>0){
				foreach($_SESSION['rattachmts'] as $code_chaine => $chaine_loc){
					$niv_chaine = count($chaine_loc);
					if(isset($chaine_loc[$niv_chaine-1]) && $chaine_loc[$niv_chaine-1]<>''){
						$req_type_regroup = "SELECT ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']." FROM ".$GLOBALS['PARAM']['REGROUPEMENT'].
											" WHERE ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']." = ".$chaine_loc[$niv_chaine-1];
						$type_regroup = $GLOBALS['conn']->GetOne($req_type_regroup);
						if(in_array($type_regroup,$tab_type_regroup_plage)){
							$req_plage 	= 'SELECT * FROM DICO_PLAGE_CODES WHERE CODE_TYPE_REGROUP = '.$type_regroup;
							$res_plage	= $GLOBALS['conn_dico']->GetAll($req_plage);
							if(is_array($res_plage)){
								foreach ($res_plage as $i => $plg){
									$req_plage_code_add 	= "SELECT NOM_CHAMP_ASSOC FROM DICO_PLAGE_CODES_ASSOC WHERE CODE_TYPE_REGROUP = ".$type_regroup." AND NOM_CHAMP = '".$plg['NOM_CHAMP']."' ORDER BY ORDRE_CHAMP_ASSOC";
									$res_plage_code_add	= $GLOBALS['conn_dico']->GetAll($req_plage_code_add);
									$champs_rattach_add = "";
									if(is_array($res_plage_code_add)){
										foreach ($res_plage_code_add as $j => $plg_chp_add){
											if($plg_chp_add['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
												$champs_rattach_add .= $_SESSION['code_etab'];
											}elseif($plg_chp_add['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){
												$champs_rattach_add .= $_SESSION['secteur'];
											}elseif(isset($_SESSION['plage_codes_assoc'][$plg_chp_add['NOM_CHAMP_ASSOC']]) && $_SESSION['plage_codes_assoc'][$plg_chp_add['NOM_CHAMP_ASSOC']]<>''){
												$champs_rattach_add .= $_SESSION['plage_codes_assoc'][$plg_chp_add['NOM_CHAMP_ASSOC']];
											}
										}
									}
									$_SESSION['tab_plage'][$plg['NOM_CHAMP']] = array( 'val_min' => $chaine_loc[$niv_chaine-1].$champs_rattach_add.$plg['VAL_MIN'] , 'val_max' => $chaine_loc[$niv_chaine-1].$champs_rattach_add.$plg['VAL_MAX'] );
								}
							}
							break;
						}
					}
				}
			}
			//Fin Ajout hebie pour gestion plage automatique
		}
	}
}

function get_cle_max($champ,$table,$tab_keys,$ligne){
	$clause_where = "";
	if($table <> $GLOBALS['PARAM']['ENSEIGNANT']){
		$i = 0;
		$tab_keys_minus_incr_field = array_diff($tab_keys,array($champ));
		foreach($tab_keys_minus_incr_field as $val){
			if($i==0){
				$clause_where .= " WHERE $val = ".$ligne[$val]." ";
			}else{
				$clause_where .= " AND $val = ".$ligne[$val]." ";
			}
			$i++;
		}
	}
	$max_return = 0;
	//$sql = ' SELECT  MAX('.$champ.') as MAX_INSERT FROM  '.$table.$clause_where;
	//Ajout Hebie déc 2023 pour prise en compte plage de code inteligente
	get_plage_codes();
	$sql = '';
	$sql_clauses_plage = '';
	if(isset($_SESSION['tab_plage'][$champ])){
		if($clause_where<>''){
			$sql_clauses_plage .= ' AND '.$champ.' >= '.$_SESSION['tab_plage'][$champ]['val_min'].' AND '.$champ.' <= '.$_SESSION['tab_plage'][$champ]['val_max'];
		}else{
			$sql_clauses_plage .= ' WHERE '.$champ.' >= '.$_SESSION['tab_plage'][$champ]['val_min'].' AND '.$champ.' <= '.$_SESSION['tab_plage'][$champ]['val_max'];
		}
	}
	$sql	.= 	'SELECT  MAX('.$champ.') as MAX_INSERT FROM  '.$table;
	$sql 	.=	$clause_where ;
	$sql	= 	str_replace( $table . ' AND ' , $table . ' WHERE ' ,  $sql );
	$sql .= $sql_clauses_plage;
	//Fin Ajout Hebie déc 2023 pour prise en compte plage de code inteligente
	//echo '<br>'.$sql.'<br>';
	// Gestion des erreurs lors de l'exécution de la requête SQL
	if (($rs =  $GLOBALS['conn']->Execute($sql))===false) {   
		$GLOBALS['theme_data_MAJ_ok'] 	= false;
		print ' <br> Invalid query :<br>'.$sql.'<br>'; 
	}
	if (!$rs->EOF) {                  
		$max_return = $rs->fields['MAX_INSERT'];                  
	}
	return($max_return);
}

function suppr_bdd($ligne,$tab){
	$action = exist_dans_bdd($ligne,$tab);
	if($action == 'U' && $tab['name']<>$GLOBALS['PARAM']['ETABLISSEMENT'] && $tab['name']<>$GLOBALS['PARAM']['DONNEES_ETABLISSEMENT']){
		$crit_sql = '#';
		$tab_fields = array();
		$tab_values = array();
		foreach( $tab['keys_fields'] as $i_key => $key_col ){
			(trim($tab['type_fields'][$key_col])=='int') ? ( $val_key = $ligne[$key_col] ) : ($val_key = $GLOBALS['conn']->qstr($ligne[$key_col])) ;
			$crit_sql .=  ' AND ' . $key_col . ' = ' . $val_key;
			$tab_fields[] = $key_col;
			$tab_values[] = $val_key;
		}                   
		$crit_sql	= 	str_replace( '#' . ' AND ' , ' WHERE ' ,  $crit_sql );
				
		$sql = ' DELETE FROM '.$tab['name'];
		$sql	.= 	$crit_sql ;
		//echo '<BR> ---DELETE de suppr_bdd--- <BR>'.$sql.'<BR>';
		if ($GLOBALS['conn']->Execute($sql) === false){
			$GLOBALS['theme_data_MAJ_ok'] 	= false;
			$error_report = "$sql<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
			$error_report .= "<tr>\n";
			$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
			$error_report .= recherche_libelle_page('Err_Suppr_Modif_Lig')." ".$tab['name']." : ".$tab['sheet'];
			$error_report .= "</td>\n";
			foreach($tab_fields as $field){
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
	}
}

function maj_bdd($ligne,$tab,$num_lig=""){
	if($num_lig=="") $action = exist_dans_bdd($ligne,$tab);
	else $action = 'I';
	switch($action){
		case 'I' :
					$sql_champs = '';
					$sql_vals = '';
					$sql = '';
					if((isset($tab['incr_field'])) && ($tab['incr_field']<>'')){
						if(!isset($ligne[$tab['incr_field']])){
							if(strtoupper($tab['name']) == strtoupper($tab['main_table_mere'])){
								$incr_value = get_cle_max($tab['incr_field'],$tab['name'],$tab['keys_fields'],$ligne) + 1;
								$ligne[$tab['incr_field']] = $incr_value;
								$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$num_lig] = $incr_value;
								$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'] = $tab['keys_fields'];
								$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'] = $tab['type_fields'];
							}else{
								$ligne[$tab['incr_field']] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$num_lig];
							}
						}elseif(isset($ligne[$tab['incr_field']]) && $ligne[$tab['incr_field']]<>""){
							$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$num_lig] = $ligne[$tab['incr_field']];
						}
					}elseif((isset($tab['incr_fields'])) && count($tab['incr_fields'])==1){
						if(!isset($ligne[$tab['incr_fields'][0]])){
							if(strtoupper($tab['name']) == strtoupper($tab['main_table_mere'])){
								$incr_value = get_cle_max($tab['incr_fields'][0],$tab['name'],$tab['keys_fields'],$ligne) + 1;
								$ligne[$tab['incr_fields'][0]] = $incr_value;
								$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$num_lig] = $incr_value;
								$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields'] = $tab['keys_fields'];
								$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_type_fields'] = $tab['type_fields'];
							}else{
								$ligne[$tab['incr_fields'][0]] = $_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$num_lig];
							}
						}elseif(isset($ligne[$tab['incr_fields'][0]]) && $ligne[$tab['incr_fields'][0]]<>""){
							$_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$num_lig] = $ligne[$tab['incr_fields'][0]];
						}
					}elseif((isset($tab['incr_fields'])) && count($tab['incr_fields'])>1){
						foreach($tab['incr_fields'] as $incr_field){
							$ligne[$incr_field] = $_SESSION[$tab['id_theme']][$tab['main_table_mere']][$num_lig][$incr_field];
						}
					}
					if($tab['name'] == $GLOBALS['PARAM']['ETABLISSEMENT']){
						$ligne[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] = $_SESSION['secteur'];
					}
					$tab_fields = array();
					$tab_values = array();
					foreach($ligne as $chp=>$val){
						if( $val <> ''){
							$_SESSION[$tab['id_theme']][$tab['name']][$num_lig][$chp] = $val;
							$sql_champs .= ", $chp";
							if (trim($tab['type_fields'][$chp])=='int'){
								$sql_vals 	.= ", $val";
							}else{
								$sql_vals 	.= ", ".$GLOBALS['conn']->qstr($val);
							}
							$tab_fields[] = $chp;
							$tab_values[] = $val;
						}
					}
					$sql.= 'INSERT INTO '.$tab['name'].' ('.$sql_champs.') VALUES ('.$sql_vals.')';
					$sql = str_replace('(,','(',$sql);	
					//if ($tab['name'] == 'DONNEES_GENERALES') echo '<BR> ---INSERT maj_bdd--- <BR>'.$sql.'<BR>';
					if ($GLOBALS['conn']->Execute($sql) === false) {
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "$sql<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						if($num_lig<>"")
							$error_report .= recherche_libelle_page('Invalid_Donnee_Lig')." $num_lig : ".$tab['name']." : ".$tab['sheet'];
						else
							$error_report .= recherche_libelle_page('Invalid_Donnee_Lig')." : ".$tab['name']." : ".$tab['sheet'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab['obligatory_fields']))
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
						
						if((isset($tab['incr_field'])) && ($tab['incr_field']<>'') && (strtoupper($tab['name']) == strtoupper($tab['main_table_mere']))){
							unset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['incr_values'][$num_lig]);
						}
						//Suppression dans la table mere principale en cas d'echec d'insertion dans une table secondaire
						if(isset($tab['main_table_mere']) && isset($_SESSION['incr_keys'][$tab['id_theme']][$tab['main_table_mere']]['main_table_keys_fields']) && $tab['name'] <> $tab['main_table_mere']){
							$clause_where = "";
							$i = 0;
							foreach($ligne as $key=>$val){
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
									//if ($tab['name'] == 'DONNEES_GENERALES') echo '<BR> ---req_delete--- <BR>'.$req_delete.'<BR>';
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										$error_report = "$req_delete<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
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
					break;
		case 'U' : 
					if((isset($tab['incr_field'])) && ($tab['incr_field']<>'')){
						$clause_where = "";
						$i = 0;
						foreach($ligne as $key=>$val){
							if(in_array($key,$tab['obligatory_fields'])){
								($tab['type_fields'][$key]=='int') ? ( $val_key = $val ) : ($val_key = $GLOBALS['conn']->qstr($val)) ;
								if($i==0){
									$clause_where .= " WHERE $key = $val_key ";
								}else{
									$clause_where .= " AND $key = $val_key ";
								}
								$i++;
							}
						}
						$req_incr_field = "SELECT ".$tab['incr_field']." FROM ".$tab['name']." $clause_where";
						//if ($tab['name'] == 'DONNEES_GENERALES') echo '<BR> ---req_verif--- <BR>'.$req_incr_field.'<BR>';
						$ligne[$tab['incr_field']] = $GLOBALS['conn']->GetOne($req_incr_field);
					}
					
					$crit_sql = '#';
					$tab_fields = array();
					$tab_values = array();
					foreach( $tab['keys_fields'] as $i_key => $key_col ){
						(trim($tab['type_fields'][$key_col])=='int') ? ( $val_key = $ligne[$key_col] ) : ($val_key = $GLOBALS['conn']->qstr($ligne[$key_col])) ;
						$crit_sql .=  ' AND ' . $key_col . ' = ' . $val_key ;
						$tab_fields[] = $key_col;
						$tab_values[] = $val_key;
					}                   
					$crit_sql	= 	str_replace( '#' . ' AND ' , ' WHERE ' ,  $crit_sql );
							
					$sql = ' UPDATE '.$tab['name'].' SET ';
					if(isset($tab['data_entry_fields']) && is_array($tab['data_entry_fields'])){
						foreach( $tab['data_entry_fields'] as $i => $chp_row ){
							if(isset($ligne[$chp_row]) && trim($ligne[$chp_row]) <> ''){
								(trim($tab['type_fields'][$chp_row])=='int') ? ( $val_chp = $ligne[$chp_row] ) : ($val_chp = $GLOBALS['conn']->qstr($ligne[$chp_row])) ;
								if($tab['name'] <> $GLOBALS['PARAM']['ETABLISSEMENT'] || $chp_row <> $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
									$sql .=  ' , ' . $chp_row . ' = ' . $val_chp ;
									$tab_fields[] = $chp_row;
									$tab_values[] = $val_chp;
								}
							}else{
								/*if(preg_match('/'.preg_quote('^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$', '/').'/', $chp_row)){
									 $sql .=  ' , ' . $chp_row . ' = 255' ;
								}else{
									if(isset($tab['field_table_ref'])) $chp_table_ref = $tab['field_table_ref'][$i];
									else $chp_table_ref = '';
									if( (trim($chp_table_ref) <> '') && (preg_match('/'.preg_quote('^'.$GLOBALS['PARAM']['TYPE'].'_.*$', '/').'/', strtoupper($chp_table_ref))) ){
										$sql .=  ' , ' . $chp_row . ' = 255' ;
									}else{*/
										if($chp_row <> $GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']){
											(trim($tab['type_fields'][$chp_row])=='int') ? ( $val_chp = 'Null' ) : ($val_chp = '\'\'') ;
											$sql .=  ' , ' . $chp_row . ' = ' . $val_chp ;
										}
									/*}
								}*/
								$tab_fields[] = $chp_row;
								$tab_values[] = $val_chp;
							}
						}
					}
					$sql	= 	str_replace( ' SET ' . ' , ' , ' SET ' ,  $sql ); 
					$sql	.= 	$crit_sql ;
					//if ($tab['name'] == 'DONNEES_GENERALES') echo '<BR> ---UPDATE maj_bdd--- <BR>'.$sql.'<BR>';
					if ($GLOBALS['conn']->Execute($sql) === false){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						$error_report = "$sql<br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
						$error_report .= "<tr>\n";
						$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
						$error_report .= recherche_libelle_page('Invalid_Donnee_Lig')." ".$tab['name']." : ".$tab['sheet'];
						$error_report .= "</td>\n";
						foreach($tab_fields as $field){
							if(in_array($field,$tab['obligatory_fields']))
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
						//print ' <br> Invalid data in this row :<br> --- '.$sql.' --- <br>'; 
					}
					break;
	}
}

function valid_xls_import($fichier){// Todo: Verifier l'integrité du fichier
	return true;
}

lit_libelles_page("/questionnaire.php");
if( isset($_POST) && count($_POST) > 0 ){
	set_time_limit(0);
	ini_set("memory_limit", "64M");
	if(isset($_SESSION['xls_files'])) unset($_SESSION['xls_files']);
	//$fichier = str_replace('\\\\','\\',$_POST['chemin_fichier']);

	if($_FILES['chemin']) {	
		$rep = "server-side/import_export/"; 	
		$fichier_upload = $_FILES['chemin']; // simplication du tableau $_FILES
		if(!empty($fichier_upload["name"]))
		{
			//nom du fichier choisi:
			$nomFichier    = $fichier_upload["name"];//$_FILES["fichier1"]["name"] ;
			//nom temporaire sur le serveur:
			$nomTemporaire = $fichier_upload["tmp_name"] ;
			//type du fichier choisi:
			$typeFichier   = $fichier_upload["type"] ;
			//poids en octets du fichier choisit:
			$poidsFichier  = $fichier_upload["size"];
			//code de l'erreur si jamais il y en a une:
			$codeErreur    = $fichier_upload["error"];
			
			if(preg_match("/(\.xls)/",$nomFichier)) //Extraction nom de fichier
			{
				//chemin qui mène au dossier qui va contenir les fichiers upload:
				$chemin = $rep ;
				if (!is_dir($chemin))
				{
					mkdir($chemin, 0777);
				}
				$saveFile=$chemin.$nomFichier;
				
				if(copy($nomTemporaire, $saveFile))
				{
					//				
				}
				else
					echo "<center> $nomFichier : Upload not OK </center>";
			}
			else
				echo "<center> $nomFichier : ".recherche_libelle_page('TypeFichIncorrect')." </center>";		
		}		
	}
	
	$_POST['chemin_fichier'] = $saveFile;
	$fichier = $_POST['chemin_fichier'];

	if (file_exists($fichier)){
		$nom_fichier = $fichier;
		if(isset($GLOBALS['PARAM']['DO_EXCEL_IMPORT_BATCH']) && $GLOBALS['PARAM']['DO_EXCEL_IMPORT_BATCH']
			&& !(isset($_SESSION['code_etab']) && $_SESSION['code_etab']<>0)){
			$dossier_xsl = dirname($fichier);
			if (is_dir($dossier_xsl)){
				$files = new DirectoryIterator($dossier_xsl);  
				foreach($files as $file ) {  
					if ( !$file->isDot() ) {  
						if (substr($file->getFilename(),-3) == 'xls' || substr($file->getFilename(),-3) == 'xlsx'){
							$file_path = $file->getPathName();
							if(valid_xls_import($file_path)){
								$_SESSION['xls_files'][] = $file_path;
							}
						}
						
					}  
				}  
			}
		}else{
			$_SESSION['xls_files'][] = $fichier;
		}
	}else{
		$nom_fichier = '';
		print "<script type='text/Javascript'>\n";
		print "alert('File not found');\n";
		//print "fermer();\n";
		print "</script>\n";
		//die(' : File not found ... <br>');
	}
	if($nom_fichier<>''){
		require_once 'reader.php';
		
		// ExcelFile($filename, $encoding);
		$data = new Spreadsheet_Excel_Reader();
		// Set output Encoding.
		$data->setOutputEncoding('CP1251');
		
		$data->read($nom_fichier);
		
		error_reporting(E_ALL ^ E_NOTICE);
		
		require_once ('excel_db_structure_'. $_SESSION['secteur'].'.php');
	}
	
}
elseif( isset($_GET['excel_file']) && $_GET['excel_file']<>'' ){
	if(isset($_SESSION['incr_keys'])) unset($_SESSION['incr_keys']);
	set_time_limit(0);
	ini_set("memory_limit", "128M");
	//$fichier = str_replace('\\\\','\\',urldecode($_GET['excel_file']));
	debut_popup_progress();//For progress popup starting
	
	/////Dico reading
	include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
	if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
	if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
	if(!isset($_SESSION['code_etab']) || $_SESSION['code_etab']==''){
		$_SESSION['code_etab'] = 0;
	}
	$req_feuil_etab	="	SELECT NOM_PAGE FROM  DICO_EXCEL_TABLE
						WHERE NOM_TABLE = '".$GLOBALS['PARAM']['ETABLISSEMENT']."' 
						AND ID_SYSTEME = ".$_SESSION['secteur']."
						ORDER BY ORDRE_TABLE";
	$res_feuil_etab = $GLOBALS['conn_dico']->GetAll($req_feuil_etab);
	
	$error_report = "<br/><br/><br/><b>---".recherche_libelle_page('Debut_Log')."---</b>\n";
	print $error_report;
	
	//echo "<pre>";
	//print_r($_SESSION['xls_files']);
	foreach($_SESSION['xls_files'] as $xls_file){
	
		require_once 'reader.php';
		// ExcelFile($filename, $encoding);
		$data = new Spreadsheet_Excel_Reader();
		// Set output Encoding.
		$data->setOutputEncoding('CP1251');
		//$fichier = urldecode($_GET['excel_file']);
		//$nom_fichier = $fichier;
		$nom_fichier = $xls_file;
	
		$data->read($nom_fichier);
		error_reporting(E_ALL ^ E_NOTICE);
		if(file_exists($xls_file)) unlink($xls_file);
		//Affichage nom du fichier Excel
		$error_report = "<br/><br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
		$error_report .= "<tr>\n";
		$error_report .= "<td rowspan='2' style='color:#0000FF; border-color:#000000'>\n";
		$error_report .=  "<b>".recherche_libelle_page('Emplacement_Fichier')." : ".basename($nom_fichier)."</b>";
		$error_report .= "</td>\n";
		$error_report .= "</tr>\n";
		$error_report .= "</table>\n";
		print $error_report;
		
		//if(isset($_GET['new_code_etab']) && $_GET['new_code_etab']=='new_etab'){
		if(!(isset($_SESSION['code_etab']) && $_SESSION['code_etab']<>0)){	
			$row_excel_code_etab = $_GET['row_excel_code_etab'];//row
			$col_excel_code_etab = $_GET['col_excel_code_etab'];//column
			if($row_excel_code_etab>0 && $col_excel_code_etab>0){
				for($sheet=0;$sheet<count($data->sheets);$sheet++){
					if(strtoupper($data->boundsheets[$sheet]['name'])==strtoupper($res_feuil_etab[0]['NOM_PAGE'])){
						$school_code = $data->sheets[$sheet]['cells'][$row_excel_code_etab][$col_excel_code_etab];
						break;
					}
				}
			}else{
				//Requete pour gestion incrementale du code_etablissement
				$req_max_code = 'SELECT MAX('.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].') as MAX_INSERT  FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'];
				$school_code	= $GLOBALS['conn']->GetOne($req_max_code) + 1;
			}
			
			if(isset($school_code) && ($school_code==0 || $school_code=="")){
				$GLOBALS['theme_data_MAJ_ok'] = false;
				$error_report = "<br/><br/><table border='1' style='background-color:#FFFFFF; border-color:#000000'>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td rowspan='2' style='color:#000000; border-color:#000000'>\n";
				$error_report .= recherche_libelle_page('Invalid_Code_Etab')." ".$nom_fichier;
				$error_report .= "</td>\n";
				$error_report .= "<td style='color:#FF0000; border-color:#000000'>\n";
				$error_report .= "<b>".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."</b>";
				$error_report .= "</td>\n";
				$error_report .= "</tr>\n";
				$error_report .= "<tr>\n";
				$error_report .= "<td align='center'>\n";
				$error_report .= $school_code;
				$error_report .= "</td>\n";
				$error_report .= "</tr>\n";
				$error_report .= "</table>\n";
				print $error_report;
				//exit();
				continue;
			}else{
				$code_etablissement	= $school_code;
			}
			
		//}elseif(!(isset($_GET['new_code_etab']) && $_GET['new_code_etab']=='new_etab') && (isset($_SESSION['code_etab']) && $_SESSION['code_etab']<>"")){
		}elseif(isset($_SESSION['code_etab']) && $_SESSION['code_etab']<>0){
			$code_etablissement = $_SESSION['code_etab'];
		}
		
		$_SESSION['code_etab_excel'] = $code_etablissement;
		$code_annee = $_SESSION['annee'];
		$code_filtre = $_SESSION['filtre'];
		$id_systeme	= $_SESSION['secteur'];
		$themes_saisie = '';
		if(isset($GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE']) && count($GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE'])>0){
			$cpt = 0;
			foreach($GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE'] as $id_th){
				if($cpt == 0) $themes_saisie .= '('.$id_th;
				else $themes_saisie .= ','.$id_th;
				$cpt++;
			}
			$themes_saisie .= ')';
		}
		if($themes_saisie<>'')
		$requete  = "SELECT DICO_THEME.* 
					 FROM DICO_THEME, DICO_THEME_SYSTEME 
					 WHERE DICO_THEME.ID = DICO_THEME_SYSTEME.ID AND DICO_THEME_SYSTEME.ID_THEME_SYSTEME NOT IN $themes_saisie AND DICO_THEME_SYSTEME.ID_SYSTEME = $id_systeme AND DICO_THEME.ID_TYPE_THEME NOT IN (1,5,6,7,8)".";";
		else
		$requete  = "SELECT DICO_THEME.* 
					 FROM DICO_THEME, DICO_THEME_SYSTEME 
					 WHERE DICO_THEME.ID = DICO_THEME_SYSTEME.ID AND DICO_THEME_SYSTEME.ID_SYSTEME = $id_systeme AND DICO_THEME.ID_TYPE_THEME NOT IN (1,5,6,7,8)".";";
		$result_themes	= $GLOBALS['conn_dico']->GetAll($requete);
		$tables = array();
		foreach($result_themes as $theme){
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
			//Modif HEBIE 09 06 2019: Mise en commentaire pour prendre en compte l'import excel de la grille colonne sans dimension colonne
			//if($_SESSION['curobj_theme']->type_theme <> 4 || $_SESSION['curobj_theme']->type_gril_eff_fix_col){
				foreach($_SESSION['curobj_theme']->tableau_type_zone_base as $nom_tab => $tab_type_zone){
					$tables[$nom_tab.'_'.$theme['ID']]['name'] = $nom_tab;
					$tables[$nom_tab.'_'.$theme['ID']]['id_theme'] = $theme['ID'];
					$tables[$nom_tab.'_'.$theme['ID']]['type_theme'] = $_SESSION['curobj_theme']->type_theme;
					foreach($tab_type_zone as $nom_chp => $type_chp){
						$tables[$nom_tab.'_'.$theme['ID']]['type_fields'][$nom_chp] = $type_chp;
					}
				}
				
				foreach($_SESSION['curobj_theme']->tableau_zone_saisie as $nom_tab => $tab){
					foreach($tab as $chp){
							if((isset($chp['type'])) && ($chp['type']=='c' || $chp['type']=='li_ch' || $chp['type']=='loc_etab'
								 || $chp['type']=='csu' || $chp['type']=='cmm' || $chp['type']=='cmu' 
								 || $chp['type']=='sco' || $chp['type']=='sys_txt' || $chp['type']=='dim_lig'  
								 || $chp['type']=='dim_col') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
								$tables[$nom_tab.'_'.$theme['ID']]['keys_fields'][] = $chp['champ'];
							}
							if((isset($chp['type'])) && ($chp['type']<>'c') && ($chp['type']<>'l') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
								$tables[$nom_tab.'_'.$theme['ID']]['entry_fields'][] = $chp['champ'];
								$tables[$nom_tab.'_'.$theme['ID']]['id_zone_nom_champ'][$chp['id_zone']] = $chp['champ'];
								$tables[$nom_tab.'_'.$theme['ID']]['id_zones'][] = $chp['id_zone'];
							}
							//if((isset($chp['type'])) && ($chp['type']<>'c') && ($chp['type']<>'l') && ($chp['type']<>'dim_lig') && ($chp['type']<>'dim_col') && ($chp['type']<>'loc_etab') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
							if((isset($chp['type'])) && ($chp['type']<>'c' || ($chp['type']=='c' && $nom_tab==$GLOBALS['PARAM']['ETABLISSEMENT'] && $chp['champ']==$GLOBALS['PARAM']['CODE_ETABLISSEMENT'])) 
								&& ($chp['type']<>'l') && ($chp['type']<>'dim_lig') && ($chp['type']<>'dim_col') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
							//if((isset($chp['type'])) && ($chp['type']<>'c') && ($chp['type']<>'l') && ($chp['type']<>'dim_lig') && ($chp['type']<>'dim_col') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
								
								$tables[$nom_tab.'_'.$theme['ID']]['data_entry_fields'][] = $chp['champ'];
								if(($chp['type']<>'tvm') && ($chp['type']<>'b') && ($chp['ss_type']<>'b'))
									$tables[$nom_tab.'_'.$theme['ID']]['field_table_ref'][] = $chp['table_ref'];
								else
									$tables[$nom_tab.'_'.$theme['ID']]['field_table_ref'][] = '';
							}
							if(($chp['type']=='dim_col') || ($chp['type']=='dim_lig')){
								$tables[$nom_tab.'_'.$theme['ID']]['obligatory_fields'][] = $chp['champ'];
								if(($chp['type']=='dim_lig')){
									$tables[$nom_tab.'_'.$theme['ID']]['chp_pere_tab_ref_row_dim'] = $chp['champ'];
									$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_row_dim'] = $chp['table_ref'];
									$tables[$nom_tab.'_'.$theme['ID']]['id_zone_row_dim'] = $chp['id_zone'];
								}
								if(($chp['type']=='dim_col')){
									$tables[$nom_tab.'_'.$theme['ID']]['chp_pere_tab_ref_col_dim'] = $chp['champ'];
									$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $chp['table_ref'];
									$tables[$nom_tab.'_'.$theme['ID']]['id_zone_col_dim'] = $chp['id_zone'];
								}
							}
							if(($chp['type']=='tvm')){
								$tables[$nom_tab.'_'.$theme['ID']]['entry_fields'][] = $GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'];
								$tables[$nom_tab.'_'.$theme['ID']]['obligatory_fields'][] = $GLOBALS['PARAM']['CODE'].'_'.$chp['table_ref'];
								$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $chp['table_ref'];
								$tables[$nom_tab.'_'.$theme['ID']]['id_zone_col_dim'] = $chp['id_zone'];
							}
							if(($chp['type']=='li_ch')){
								$tables[$nom_tab.'_'.$theme['ID']]['obligatory_fields'][] = $chp['champ'];
								$tables[$nom_tab.'_'.$theme['ID']]['chp_pere_tab_ref_col_dim'] = $chp['champ'];
								$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $chp['table_ref'];
								$tables[$nom_tab.'_'.$theme['ID']]['id_zone_col_dim'] = $chp['id_zone'];
							}
					}
				}
						
				foreach($_SESSION['curobj_theme']->code_nomenclature as $nom_tab => $tab){
					foreach($tab as $zone_tab => $records){
						$records_minus_val_indet = array_diff($records, array('255'));
						if(strtoupper($zone_tab) == strtoupper($tables[$nom_tab.'_'.$theme['ID']]['id_zone_row_dim'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_row_dim'])){
							$tables[$nom_tab.'_'.$theme['ID']]['records_tab_ref_row_dim'] = $records_minus_val_indet;
						}
						if(strtoupper($zone_tab) == strtoupper($tables[$nom_tab.'_'.$theme['ID']]['id_zone_col_dim'].'_'.$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'])){
							$tables[$nom_tab.'_'.$theme['ID']]['records_tab_ref_col_dim'] = $records_minus_val_indet;
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
									if(!in_array($GLOBALS['PARAM']['CODE'].'_'.$tables[$tab.'_'.$theme['ID']]['tab_ref_col_dim'],$tables[$nom_tab.'_'.$theme['ID']]['entry_fields']))
										$tables[$nom_tab.'_'.$theme['ID']]['entry_fields'][] = $GLOBALS['PARAM']['CODE'].'_'.$tables[$tab.'_'.$theme['ID']]['tab_ref_col_dim'];
								}
							}
						}
					}
				}
				
				foreach($_SESSION['curobj_theme']->val_cle as $nom_tab => $tab){
					$tables[$nom_tab.'_'.$theme['ID']]['nb_val_cle'] = count($tab);
					if(is_array($tab))
					foreach($tab as $nom_chp => $val_chp){
						$tables[$nom_tab.'_'.$theme['ID']]['entry_fields'][] = $nom_chp;
						$tables[$nom_tab.'_'.$theme['ID']]['obligatory_fields'][] = $nom_chp;
						$tables[$nom_tab.'_'.$theme['ID']]['val_cle'][$nom_chp] = $val_chp;
					}
				}
		
				foreach($_SESSION['curobj_theme']->nomtableliee as $nom_tab){
					if(!isset($_SESSION['curobj_theme']->main_table_mere) || $tables[$nom_tab.'_'.$theme['ID']]['name'] == $_SESSION['curobj_theme']->main_table_mere){
						if(is_array($tables[$nom_tab.'_'.$theme['ID']]['keys_fields'])){
							foreach($tables[$nom_tab.'_'.$theme['ID']]['keys_fields'] as $key){
								if(!in_array($key,$tables[$nom_tab.'_'.$theme['ID']]['entry_fields'])){
									$tables[$nom_tab.'_'.$theme['ID']]['incr_field'] = $key; break;
								}
							}
							if(!isset($tables[$nom_tab.'_'.$theme['ID']]['incr_field']))
							foreach($tables[$nom_tab.'_'.$theme['ID']]['keys_fields'] as $key){
								if(is_array($tables[$nom_tab.'_'.$theme['ID']]['data_entry_fields']) && in_array($key,$tables[$nom_tab.'_'.$theme['ID']]['data_entry_fields'])){
									$tables[$nom_tab.'_'.$theme['ID']]['incr_field'] = $key; break;
								}
							}
						}
						if(!isset($tables[$nom_tab.'_'.$theme['ID']]['incr_field'])){
							$tables[$nom_tab.'_'.$theme['ID']]['incr_field'] = '';
						}
					}else{
						if(is_array($tables[$nom_tab.'_'.$theme['ID']]['keys_fields'])){
							foreach($tables[$nom_tab.'_'.$theme['ID']]['keys_fields'] as $key){
								if(!in_array($key,$tables[$nom_tab.'_'.$theme['ID']]['entry_fields'])){
									$tables[$nom_tab.'_'.$theme['ID']]['incr_fields'][] = $key;
								}
							}
							if(!isset($tables[$nom_tab.'_'.$theme['ID']]['incr_fields']))
							foreach($tables[$nom_tab.'_'.$theme['ID']]['keys_fields'] as $key){
								if(is_array($tables[$nom_tab.'_'.$theme['ID']]['data_entry_fields']) && in_array($key,$tables[$nom_tab.'_'.$theme['ID']]['data_entry_fields'])){
									$tables[$nom_tab.'_'.$theme['ID']]['incr_fields'][] = $key;
								}
							}
						}
						if(!isset($tables[$nom_tab.'_'.$theme['ID']]['incr_fields'])){
							$tables[$nom_tab.'_'.$theme['ID']]['incr_fields'] = array();
						}
					}
				}
				
				if($_SESSION['curobj_theme']->type_theme == 3){//cas de formumaire : obligatory_fields
					foreach($_SESSION['curobj_theme']->nomtableliee as $nom_tab){
						foreach($_SESSION['curobj_theme']->tab_regles_zone as $id_zone => $controles){
							foreach($controles as $controle){
								if($controle['controle_obligation']==1){
									if(in_array($id_zone,$tables[$nom_tab.'_'.$theme['ID']]['id_zones'])){
										$tables[$nom_tab.'_'.$theme['ID']]['obligatory_fields_form'][] = $tables[$nom_tab.'_'.$theme['ID']]['id_zone_nom_champ'][$id_zone];
										$tables[$nom_tab.'_'.$theme['ID']]['exist_obligatory_ctrl'] = true;
									}
								}
							}
						}
					}
				}
				
				if($_SESSION['curobj_theme']->type_theme == 2 || $_SESSION['curobj_theme']->type_theme == 4){//cas de grille : obligatory_fields permet d'identifier de facon unique la ligne ou la colonne
					foreach($_SESSION['curobj_theme']->nomtableliee as $nom_tab){
						foreach($_SESSION['curobj_theme']->tab_regles_zone as $id_zone => $controles){
							foreach($controles as $controle){
								if($controle['controle_obligation']==1){
									if(in_array($id_zone,$tables[$nom_tab.'_'.$theme['ID']]['id_zones'])){
										$tables[$nom_tab.'_'.$theme['ID']]['obligatory_fields'][] = $tables[$nom_tab.'_'.$theme['ID']]['id_zone_nom_champ'][$id_zone];
										$tables[$nom_tab.'_'.$theme['ID']]['exist_obligatory_ctrl'] = true;
									}
								}
							}
						}
						$tables[$nom_tab.'_'.$theme['ID']]['main_table_mere'] = $_SESSION['curobj_theme']->main_table_mere;
						if($tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim']<>""){
							foreach($tables[$nom_tab.'_'.$theme['ID']]['data_entry_fields'] as $chps){
								if(!in_array($chps,$tables[$nom_tab.'_'.$theme['ID']]['obligatory_fields']))
										$tables[$nom_tab.'_'.$theme['ID']]['obligatory_fields'][] = $chps;
								
							}
						}
					}
				}
			}
		}
		//echo "<br>tables<pre>";
		//print_r($tables);
		
		//////End Dico reading
		
		// ExcelFile($filename, $encoding);
		/*$data = new Spreadsheet_Excel_Reader();
		
		// Set output Encoding.
		$data->setOutputEncoding('CP1251');
		
		$data->read($nom_fichier);
		
		error_reporting(E_ALL ^ E_NOTICE);*/

		require('excel_db_structure_'. $_SESSION['secteur'].'.php');
	//}
	//For closing importation processing statement or displaying errors report
	if(!isset($GLOBALS['theme_data_MAJ_ok']) || (isset($GLOBALS['theme_data_MAJ_ok']) && $GLOBALS['theme_data_MAJ_ok'] == true)){//For progress popup closing
		print "<script type='text/Javascript'>\n";
		print "window.opener.location.href='questionnaire.php?theme=".$_SESSION['theme']."&code_etab=".$_SESSION['code_etab_excel']."';\n";
		print "</script>\n";
		fin_popup_progress();//For progress popup closing
	}elseif(!isset($GLOBALS['theme_data_MAJ_ok']) || (isset($GLOBALS['theme_data_MAJ_ok']) && $GLOBALS['theme_data_MAJ_ok'] == true)){//For progress popup closing
		print "<script type='text/Javascript'>\n";
		print "window.opener.location.href='questionnaire.php?theme=".$_SESSION['theme']."&code_etab=".$_SESSION['code_etab_excel']."';\n";
		print "</script>\n";
		fin_popup_progress();//For progress popup closing
	}else{//display errors report if there is
		print("\n<script type='text/Javascript'>\n");
		print("\t <!--  \n");
		print("\t window.resizeTo(800,400); \n"); 
		print("\t window.moveTo(100,50); \n"); 
		print("\t var ch_eval = 'document.getElementById(\"progress\").style.visibility=\'hidden\';';\n");
		print("\t eval(ch_eval);\n");
		print("\t --> \n");
		print("\t </script> \n");
		print "<script type='text/Javascript'>\n";
		print "window.opener.location.href='questionnaire.php?theme=".$_SESSION['theme']."&code_etab=".$_SESSION['code_etab_excel']."';\n";
		print "</script>\n";
	}
	//End: For closing importation processing statement or displaying errors report
}
?>
<br/><br/>
<?php
if($GLOBALS['theme_data_MAJ_ok'] == true){
?>
<br/>
<form name="form_load_quest" method="post" enctype="multipart/form-data">
<table width="450" border="0" align="center">
    <caption><?php echo recherche_libelle_page('EntImpExcel')?></caption>
	<tr> 
		<TD height="34"  ><?php echo recherche_libelle_page('ExcelFileLoc')?></TD>
		<TD>
			<input type="file" name="chemin" size="50" onchange="document.form_load_quest.chemin_fichier.value=document.form_load_quest.chemin.value"/>
			<input type="hidden" name="chemin_fichier" value=""/>
			<input type="hidden" name="new_code_etab" value="<?php echo $_GET['val_code_etab']; ?>"/>
			<input type="hidden" name="id_theme_syst" value="<?php echo $_SESSION['id_theme_systeme']; ?>"/>
		</TD>
	</tr>
	<tr> 
    	<td align="center" colspan="2"> <INPUT type="submit" name="valider" value="<?php echo recherche_libelle_page('BtnImpExcel')?>"/></td>
    </tr>
</table>
</form>
<?php
}else{
	$error_report = "<b>---".recherche_libelle_page('Fin_Log')."---</b><br/><br/>\n";
	print $error_report;
}
?>