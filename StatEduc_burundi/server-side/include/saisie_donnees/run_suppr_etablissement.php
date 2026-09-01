<?php 
	set_time_limit(0);
	
	if( (trim($_GET['action_suppr']) <> '') ){
		
		$list_tables 	= array();
		$list_tables 	= 	get_dico_tables_by_sector($_SESSION['secteur']) ;	
		$GLOBALS['sup_etab_success'] = true;
		$GLOBALS['etab_enfant_exist'] = false;
				
		if( ($_GET['action_suppr'] == 'suppr_etab') ){
			$l_tabms_del = $list_tables;
			krsort($l_tabms_del);
			
			if( isset($_SESSION['code_etab']) and (trim($_SESSION['code_etab']) <> '') ){
				$conn = $GLOBALS['conn'];
				if(isset($GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']) && $GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] <> ''){
					$req_etab_enfant   	= "SELECT COUNT(".$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].") FROM ".$GLOBALS['PARAM']['ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']." = ".$_SESSION['code_etab'];
					$nb_etab_enfant = $conn->GetOne($req_etab_enfant);
					if($nb_etab_enfant > 0){
						$GLOBALS['etab_enfant_exist'] = true;
						$GLOBALS['sup_etab_success'] = false;
					}
				}
				if(!$GLOBALS['etab_enfant_exist']){	
					if( isset($_SESSION['hierarchie_regroup']) )	unset($_SESSION['hierarchie_regroup']);
					if( isset($infos_etab) )	unset($infos_etab);
					foreach ($l_tabms_del as $table){ 
						if($table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'] || $table==$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']){					
							$res_exist_ens_etab = array();
							if($table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
								$req_exist_ens_etab = "SELECT ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab']." GROUP BY ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].";";
								$res_exist_ens_etab = $GLOBALS['conn']->GetAll($req_exist_ens_etab);
							}
							$res_exist_pers_etab = array();
							if($table==$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']){
								$req_exist_pers_etab = "SELECT ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab']." GROUP BY ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN'].";";
								$res_exist_pers_etab = $GLOBALS['conn']->GetAll($req_exist_pers_etab);
							}
						}
						$sql=	'DELETE FROM '.$table.' WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$_SESSION['code_etab'];
						if ($conn->Execute($sql) === false) {
							$GLOBALS['sup_etab_success'] = false;
							print("<script type=\"text/javascript\">\n");
							print("\t <!-- \n");
							print("alert(\"".recherche_libelle_page('SupDataEtErr')." ".$table."\"); \n");
							print("\t //--> \n");
							print("</script>\n");
						////Ajout Hebié dec 2023
						}elseif($table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'] || $table==$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']){//On supprime les enseignants/pers admin dans la table enseignant/pers admin qui ne sont rattachés à aucun autre etablissement ou année					
							if(isset($res_exist_ens_etab) && count($res_exist_ens_etab)>0){	
								foreach($res_exist_ens_etab as $val_id_enseign){
									$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$val_id_enseign[$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']];
									$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
									if($NB_ENS_ETAB == 0){
										$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$val_id_enseign[$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']];
										if ($GLOBALS['conn']->Execute($req_delete) === false){
											$GLOBALS['theme_data_MAJ_ok'] 	= false;
											print("<script type=\"text/javascript\">\n");
											print("\t <!-- \n");
											print("alert(\"".recherche_libelle_page('SupDataEtErr')." ".$table."\"); \n");
											print("\t //--> \n");
											print("</script>\n");
										}
									}
								}
							}
							if(isset($res_exist_pers_etab) && count($res_exist_pers_etab)>0){	
								foreach($res_exist_pers_etab as $val_id_pers){
									$req_exist_pers_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN'].") AS NB_PERS_ETAB FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." = ".$val_id_pers[$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']];
									$NB_PERS_ETAB = $GLOBALS['conn']->GetOne($req_exist_pers_etab);
									if($NB_PERS_ETAB == 0){
										$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." = ".$val_id_pers[$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']];
										if ($GLOBALS['conn']->Execute($req_delete) === false){
											$GLOBALS['theme_data_MAJ_ok'] 	= false;
											print("<script type=\"text/javascript\">\n");
											print("\t <!-- \n");
											print("alert(\"".recherche_libelle_page('SupDataEtErr')." ".$table."\"); \n");
											print("\t //--> \n");
											print("</script>\n");
										}
									}
								}
							}
						}
						////Fin Ajout Hebié dec 2023
					}
				}
			}
		}
		elseif( ($_GET['action_suppr'] == 'suppr_donnees') && ($_GET['annee_suppr'] <> '') ){
			$l_tabms_del = $list_tables;
			krsort($l_tabms_del);
			
			foreach($l_tabms_del as $i => $table){
				if(exist_champ_in_table($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $table) && exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $table)){
					if($table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'] || $table==$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']){				
						$res_exist_ens_etab = array();
						if($table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
							$req_exist_ens_etab = "SELECT ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab']." AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_GET['annee_suppr'].";";
							//echo req_exist_ens_etab
							$res_exist_ens_etab = $GLOBALS['conn']->GetAll($req_exist_ens_etab);
						}
						$res_exist_pers_etab = array();
						if($table==$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']){
							$req_exist_pers_etab = "SELECT ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab']." AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_GET['annee_suppr'].";";
							$res_exist_pers_etab = $GLOBALS['conn']->GetAll($req_exist_pers_etab);
						}	
					}
					$req_del = 'DELETE FROM '.$table.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_GET['annee_suppr'].
							   ' AND '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$_SESSION['code_etab'];
					if($GLOBALS['conn']->Execute($req_del) === false) {
						$GLOBALS['sup_etab_success'] = false;
						print("<script type=\"text/javascript\">\n");
						print("\t <!-- \n");
						print("alert(\"".recherche_libelle_page('SupDataEtErr')." ".$table."\"); \n");
						print("\t //--> \n");
						print("</script>\n");
					////Ajout Hebié dec 2023
					}elseif($table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'] || $table==$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']){//On supprime les enseignants/pers admin dans la table enseignant/pers admin qui ne sont rattachés à aucun autre etablissement ou année					
						if(isset($res_exist_ens_etab) && count($res_exist_ens_etab)>0){	
							foreach($res_exist_ens_etab as $val_id_enseign){
								$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$val_id_enseign[$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']];
								$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
								if($NB_ENS_ETAB == 0){
									$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['ENSEIGNANT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$val_id_enseign[$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']];
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										print("<script type=\"text/javascript\">\n");
										print("\t <!-- \n");
										print("alert(\"".recherche_libelle_page('SupDataEtErr')." ".$table."\"); \n");
										print("\t //--> \n");
										print("</script>\n");
									}
								}
							}
						}
						if(isset($res_exist_pers_etab) && count($res_exist_pers_etab)>0){	
							foreach($res_exist_pers_etab as $val_id_pers){
								$req_exist_pers_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN'].") AS NB_PERS_ETAB FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." = ".$val_id_pers[$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']];
								$NB_PERS_ETAB = $GLOBALS['conn']->GetOne($req_exist_pers_etab);
								if($NB_PERS_ETAB == 0){
									$req_delete = "DELETE FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." = ".$val_id_pers[$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']];
									if ($GLOBALS['conn']->Execute($req_delete) === false){
										$GLOBALS['theme_data_MAJ_ok'] 	= false;
										print("<script type=\"text/javascript\">\n");
										print("\t <!-- \n");
										print("alert(\"".recherche_libelle_page('SupDataEtErr')." ".$table."\"); \n");
										print("\t //--> \n");
										print("</script>\n");
									}
								}
							}
						}
					}
					////Fin Ajout Hebié dec 2023
				}
			}
		}
		if($GLOBALS['sup_etab_success'] == true){
				////////// alert sup successs
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("alert(\"".recherche_libelle_page('SupEtOK')."\"); \n");
				print("\t //--> \n");
				print("</script>\n");
		}
		else{
			if($GLOBALS['etab_enfant_exist']){
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("alert(\"".recherche_libelle_page('SupEtabEnfant')."\"); \n");
				print("\t //--> \n");
				print("</script>\n");
			}else{
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("alert(\"".recherche_libelle_page('SupEtErr')."\"); \n");
				print("\t //--> \n");
				print("</script>\n");
			}
		}
	}
?>


