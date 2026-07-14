<?php set_time_limit(0);
	
	if( (trim($_GET['cible_imput']) <> '') && (trim($_GET['action_imput']) <> '') ){
		
		$list_tables 	= array();
		$list_etabs	 	= array();
		$delete_before 	= false ;
		switch ($_GET['cible_imput']) {
	
			case 'all_thm' : 
			{
				$list_tables 	= 	$_SESSION['imput_all_tabms'][$_SESSION['secteur']] ;
				$list_etabs[] 	= 	$_SESSION['code_etab'];
				$delete_before 	= 	true ;
			}
			break;
			
			case 'cur_thm' : {
				$list_tables 	= 	$_SESSION['imput_cur_tabms'] ;
				$list_etabs[] 	= 	$_SESSION['code_etab'];
				$delete_before 	= 	true ;
			}
			break;
			
			case 'batch_etabs' :
			{
				$list_tables 	= 	$_SESSION['imput_all_tabms'][$_SESSION['secteur']] ;
				$list_etabs 	= 	$_SESSION['batch_etabs_inc'];
			}
			break;
			
			case 'sup_teach' :
			case 'tmis_thm' :
			{
				include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
				$code_etablissement = $_SESSION['liste_etab'][0];
				$code_annee = $_SESSION['annee'];
				$code_filtre = $_SESSION['filtre'];
				$id_systeme	= $_SESSION['secteur'];
				$tmis_imput_themes_syst = array_merge($GLOBALS['PARAM']['TEACHERS_LIST_THEMES'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']);
				$tmis_imput_themes = array();
				$tmis_imput_tabms = array();
				foreach($tmis_imput_themes_syst as $theme_syst){
					$requete  = "SELECT DICO_THEME.* 
								 FROM DICO_THEME, DICO_THEME_SYSTEME 
								 WHERE DICO_THEME.ID = DICO_THEME_SYSTEME.ID AND DICO_THEME_SYSTEME.ID_THEME_SYSTEME = $theme_syst;";
					$result_theme	= $GLOBALS['conn_dico']->GetAll($requete);
					if(!in_array($result_theme[0]['ID'],$tmis_imput_themes)){
						$tmis_imput_themes[] = $result_theme[0]['ID'];
						$curr_inst	= $result_theme[0]['ACTION_THEME'];									
						$id_theme =	$result_theme[0]['ID'];    
						if (file_exists($GLOBALS['SISED_PATH_CLS'] . 'metier/'.$result_theme[0]['CLASSE'].'.class.php')) {
							require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/'.$result_theme[0]['CLASSE'].'.class.php'; 
						}
						switch($curr_inst){
							case 'instance_grille.php' :{
									$curobj_grille		=	new grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
									$curobj_grille->set_code_nomenclature();
									$_SESSION['tmis_curobj_theme'] = $curobj_grille;
									break;
							}	
							case 'instance_mat_grille.php' :{
									$curobj_matgrille = new mat_grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
									$curobj_matgrille->set_code_nomenclature();
									$curobj_matgrille->set_champs();
									$_SESSION['tmis_curobj_theme'] = $curobj_matgrille;
									break;
							}
							case 'instance_matrice.php' :{
									$curobj_matrice	=	new matrice($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
									$_SESSION['tmis_curobj_theme'] = $curobj_matrice;
									break;
							}
						}
						foreach($_SESSION['tmis_curobj_theme']->nomtableliee as $nom_tab){
							if(!in_array($nom_tab,$tmis_imput_tabms)){
								$tmis_imput_tabms[] = $nom_tab;
							}
						}
					}
				}
				
				$list_tables 	= 	$tmis_imput_tabms ;
				$list_etabs 	= 	$_SESSION['liste_etab'];
				$delete_before 	= 	true ;
			}
			break;
		}
			
		$str_theme_id = 0;
		if(isset($_GET['theme']) && $_GET['theme']<>''){
			$long_syst_id=strlen(''.$_SESSION['secteur']);
			$long_theme_syst_id=strlen(''.$_GET['theme']);
			$long_theme_id=$long_theme_syst_id-$long_syst_id;
			$str_theme_id=substr($_GET['theme'],0,$long_theme_id);
		}
		
		$id_theme_user = $str_theme_id;
		$code_user = $_SESSION['code_user'];
		$login_user = $_SESSION['login'];
		$code_annee_user = $_SESSION['annee'];
		if(isset($_SESSION['filtre']) && $_SESSION['filtre']<>'') $code_filtre_user = $_SESSION['filtre']; else $code_filtre_user = 'NULL';
		$id_systeme_user = $_SESSION['secteur'];
		$date_saisie_user = date('d/m/Y H:i:s');
		
		if( ($_GET['action_imput'] == 'lever_impute') ){
			$l_tabms_del = $list_tables;
			krsort($l_tabms_del);
			foreach($list_etabs as $etb => $code_etab){
				$ID_TRACE = get_cle_max_id_trace()+1;//Recuperation max id_trace + 1 pour obtenir le nouvel id_trace
				foreach($l_tabms_del as $i => $tabm){
					$id_action = 1;
					$req_del = 'DELETE FROM '.$tabm.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_SESSION['annee'].
							   ' AND '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$code_etab.
							   ' AND '.$GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES'].' > 0 ';
					if($GLOBALS['conn']->Execute($req_del) === false) {}
					if((isset($GLOBALS['PARAM']['DATA_ENTRY_TRACE']) && $GLOBALS['PARAM']['DATA_ENTRY_TRACE'])){
						//MAJ DICO_TRACE
						$nomtable = $tabm;
						$code_etab_user	= $code_etab;        
						
						$req_trace_user = "INSERT INTO DICO_TRACE (ID_TRACE,NOM_TABLE,ID_ACTION,CODE_USER,NOM_USER,ID_THEME,ACTION,SQL_ACTION,CODE_SECTEUR,CODE_ETABLISSEMENT,CODE_ANNEE,CODE_FILTRE,DATE_SAISIE) 
															VALUES ($ID_TRACE,'$nomtable',$id_action,$code_user,'$login_user',$id_theme_user,'D',".$GLOBALS['conn']->qstr($req_del).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
						//echo "<br>$req_trace_user";
						if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
							//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
						}
						//Fin MAJ DICO_TRACE
					}
				}
			}
		}
		elseif( ($_GET['action_imput'] == 'sup_teach') ){
			$ID_TRACE = get_cle_max_id_trace()+1;//Recuperation max id_trace + 1 pour obtenir le nouvel id_trace
			$l_tabms_del = $list_tables;
			krsort($l_tabms_del);
			foreach($l_tabms_del as $i => $tabm){
				if($tabm <> $GLOBALS['PARAM']['ENSEIGNANT']){
					$id_action = 1;
					$req_del = 'DELETE FROM '.$tabm.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_SESSION['annee'].
							   ' AND '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$_SESSION['code_etab'].
							   ' AND '.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].'='.$_SESSION['id_teacher'];
					if($GLOBALS['conn']->Execute($req_del) === false) {}
					if((isset($GLOBALS['PARAM']['DATA_ENTRY_TRACE']) && $GLOBALS['PARAM']['DATA_ENTRY_TRACE'])){
						//MAJ DICO_TRACE
						$nomtable = $tabm;
						$code_etab_user	= $_SESSION['code_etab'];        
						
						$req_trace_user = "INSERT INTO DICO_TRACE (ID_TRACE,NOM_TABLE,ID_ACTION,CODE_USER,NOM_USER,ID_THEME,ACTION,SQL_ACTION,CODE_SECTEUR,CODE_ETABLISSEMENT,CODE_ANNEE,CODE_FILTRE,DATE_SAISIE) 
															VALUES ($ID_TRACE,'$nomtable',$id_action,$code_user,'$login_user',$id_theme_user,'D',".$GLOBALS['conn']->qstr($req_del).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
						//echo "<br>$req_trace_user";
						if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
							//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
						}
						//Fin MAJ DICO_TRACE
					}
				}else{
					$val_id_enseign = $_SESSION['id_teacher'];
					$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$val_id_enseign;
					$NB_ENS_ETAB = $GLOBALS['conn']->GetOne($req_exist_ens_etab);
					if ($NB_ENS_ETAB == 0){
						$sql = 'DELETE FROM ' . $tabm . ' WHERE ' . $GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'] . ' = ' . $val_id_enseign;  
						if ($GLOBALS['conn']->Execute($sql) === false){
							print ' <br> error deleting :<br>'.$sql.'<br>';
						}
					}
				}
			}
		}
		elseif( ($_GET['action_imput'] == 'imputer') && ($_GET['annee_imput'] <> '') ){
			$l_tabms_del = $list_tables;
			krsort($l_tabms_del);
			if($delete_before 	== 	true ){
				foreach($list_etabs as $etb => $code_etab){
					$ID_TRACE = get_cle_max_id_trace()+1;//Recuperation max id_trace + 1 pour obtenir le nouvel id_trace
					foreach($l_tabms_del as $i => $tabm){
						$id_action = 1;
						$req_del = 'DELETE FROM '.$tabm.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_SESSION['annee'].
								   ' AND '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$code_etab;
						if($GLOBALS['conn']->Execute($req_del) === false) {}
						if((isset($GLOBALS['PARAM']['DATA_ENTRY_TRACE']) && $GLOBALS['PARAM']['DATA_ENTRY_TRACE'])){
							//MAJ DICO_TRACE
							$nomtable = $tabm;
							$code_etab_user	= $code_etab;        
							
							$req_trace_user = "INSERT INTO DICO_TRACE (ID_TRACE,NOM_TABLE,ID_ACTION,CODE_USER,NOM_USER,ID_THEME,ACTION,SQL_ACTION,CODE_SECTEUR,CODE_ETABLISSEMENT,CODE_ANNEE,CODE_FILTRE,DATE_SAISIE) 
																VALUES ($ID_TRACE,'$nomtable',$id_action,$code_user,'$login_user',$id_theme_user,'D',".$GLOBALS['conn']->qstr($req_del).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
							//echo "<br>$req_trace_user";
							if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
								//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
							}
							//Fin MAJ DICO_TRACE
						}
					}
				}
			}
			
			foreach($list_tables as $i => $tabm){
				$nom_champs_tabm = array();
				$val_champs_tabm = array();
				$exist_champ_imput = false;
				$do_imput = true ;
				$exist_year_field = false ;
				$exist_etab_field = false ;
				if(is_array($conn->MetaColumns($tabm))){                           
					foreach($conn->MetaColumns($tabm) as $champ){
						
						if(isset($_GET['cible_imput']) && $_GET['cible_imput']=='tmis_thm' && $tabm==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'] 
							&& ($champ->name==$GLOBALS['PARAM']['ETAB_PREC_ENSEIG_TRANSFERT'] || $champ->name==$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT'])){
							continue;
						}
						if($champ->name == $GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES']){
							$exist_champ_imput = true;
						}
						$nom_champs_tabm[] = $champ->name ;
						if($champ->name ==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']){
							$val_champs_tabm[] = $_SESSION['annee'];
							$exist_year_field = true ;
						}elseif($champ->name == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
							$val_champs_tabm[] = '#code_etab#';
							$exist_etab_field = true ;							
						}elseif($champ->name == $GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES']){
							$val_champs_tabm[] = '1';
						}else{
							$val_champs_tabm[] = $champ->name ;
						}
					}
				}
				if( ($exist_etab_field == true) && ($exist_year_field == true)){
					if($exist_champ_imput == false){
						$req_alter = "ALTER TABLE ".$tabm." ADD ".$GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES']." integer;";
						if ($conn->Execute($req_alter) === false) {$do_imput = false ; print ' <br> error altering :<br>'.$req_alter.'<br>';  }
						$nom_champs_tabm[] = $GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES'] ;
						$val_champs_tabm[] = '1';
					}

					$ID_TRACE = get_cle_max_id_trace()+1;//Recuperation max id_trace + 1 pour obtenir le nouvel id_trace
					foreach($list_etabs as $etb => $code_etab){
						if($do_imput == true){	
							$id_action = 1;		
							$req_imput = 'INSERT INTO '.$tabm.' ( '.implode(', ',$nom_champs_tabm).' )
										  SELECT '.implode(', ',$val_champs_tabm).'
										  FROM '.$tabm.'
										  WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_GET['annee_imput'].' AND '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$code_etab.';';
							
							$req_imput_curr_etab = str_replace('#code_etab#', $code_etab, $req_imput);
							if($GLOBALS['conn']->Execute($req_imput_curr_etab) === false) {print ' <br> error estimation query :<br>'.$req_imput_curr_etab.'<br>'; }
							if((isset($GLOBALS['PARAM']['DATA_ENTRY_TRACE']) && $GLOBALS['PARAM']['DATA_ENTRY_TRACE'])){	
								//MAJ DICO_TRACE
								$nomtable = $tabm;
								$code_etab_user	= $code_etab;        
								
								$req_trace_user = "INSERT INTO DICO_TRACE (ID_TRACE,NOM_TABLE,ID_ACTION,CODE_USER,NOM_USER,ID_THEME,ACTION,SQL_ACTION,CODE_SECTEUR,CODE_ETABLISSEMENT,CODE_ANNEE,CODE_FILTRE,DATE_SAISIE) 
																	VALUES ($ID_TRACE,'$nomtable',$id_action,$code_user,'$login_user',$id_theme_user,'I',".$GLOBALS['conn']->qstr($req_imput_curr_etab).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
								//echo "<br>$req_trace_user";
								if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
									//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
								}
								//Fin MAJ DICO_TRACE
							}
						}
					}
				}
			}
		}
	}
?>


