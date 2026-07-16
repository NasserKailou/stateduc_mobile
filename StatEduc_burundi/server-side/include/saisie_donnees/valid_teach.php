<?php 
if(isset($_GET['teach_number']) && isset($GLOBALS['PARAM']['TEACHER_VALIDATION']) && $GLOBALS['PARAM']['TEACHER_VALIDATION']){
	//Recherche enseignant
	if($GLOBALS['PARAM']['TEACH_VALID_CRITERIA']['TYPE']=='int'){
		$req_valid_teacher = "SELECT ".implode(', ',$GLOBALS['PARAM']['TEACH_VALID_FIELDS'])."
								FROM ".$GLOBALS['PARAM']['TEACH_VALID_TABLE']."
								WHERE ".$GLOBALS['PARAM']['TEACH_VALID_CRITERIA']['FIELD']."=".$_GET['teach_number']."";
	}else{
		$req_valid_teacher = "SELECT ".implode(', ',$GLOBALS['PARAM']['TEACH_VALID_FIELDS'])."
								FROM ".$GLOBALS['PARAM']['TEACH_VALID_TABLE']."
								WHERE ".$GLOBALS['PARAM']['TEACH_VALID_CRITERIA']['FIELD']."='".$_GET['teach_number']."'";
	}
	$res_valid_teach = $GLOBALS['conn']->GetAll($req_valid_teacher);
	
	// Enseignant inexistant
	if((is_array($res_valid_teach) && count($res_valid_teach)==0) || (!is_array($res_valid_teach))){
		echo "";
	}else{
	//Enseignant existant
		$res_search_teacher = array();
		$res_teach_school = "";
		$res_teach_sector = "";
		
		$long_syst_id=strlen(''.$_SESSION['secteur']);
		$long_theme_syst_id=strlen(''.$_GET['theme']);
		$long_theme_id=$long_theme_syst_id-$long_syst_id;
		$str_theme_id=substr($_GET['theme'],0,$long_theme_id);
		
		$req_zone= "SELECT CHAMP_PERE FROM DICO_ZONE WHERE ID_THEME=".$str_theme_id;
		$res_zone=$GLOBALS['conn_dico']->GetAll($req_zone);
		
		$suite_req_search_teacher = "";
		if(isset($GLOBALS['PARAM']['FILTRE']) && $GLOBALS['PARAM']['FILTRE'] && isset($GLOBALS['PARAM']['TYPE_FILTRE']) && $GLOBALS['PARAM']['TYPE_FILTRE'] <> '' && isset($_SESSION['filtre'])){
			$theme_period=false;
			if(is_array($res_zone)){
				foreach($res_zone as $rs){
					if($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']==$rs['CHAMP_PERE']){
						$theme_period=true;
						break;
					}
				}
			}
			if($theme_period){
				$suite_req_search_teacher = ' AND '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' = '.$_SESSION['filtre'];
			}
		}
	
		if(in_array($GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'],$_SESSION['imput_cur_tabms'])){
			if($GLOBALS['PARAM']['TEACH_VALID_CRITERIA']['TYPE']=='int'){
				$req_search_teacher = 'SELECT '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].', '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
										FROM  '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ENSEIGNANT'].' 
										WHERE '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].'	= '.$GLOBALS['PARAM']['ENSEIGNANT'].'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].'
												AND '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'].$suite_req_search_teacher.'
												AND '.$GLOBALS['PARAM']['ENSEIGNANT'].'.'.$GLOBALS['PARAM']['TEACH_VALID_CRITERIA']['FIELD'].' = '.$_GET['teach_number'];
			}else{
				$req_search_teacher = 'SELECT '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].', '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
										FROM  '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ENSEIGNANT'].' 
										WHERE '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].'	= '.$GLOBALS['PARAM']['ENSEIGNANT'].'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].'
												AND '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'].$suite_req_search_teacher.'
												AND '.$GLOBALS['PARAM']['ENSEIGNANT'].'.'.$GLOBALS['PARAM']['TEACH_VALID_CRITERIA']['FIELD'].' = \''.$_GET['teach_number'].'\'';
			}
			$res_search_teacher	= $GLOBALS['conn']->GetAll($req_search_teacher);
			
			$req_teach_school = 'SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].', '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' 
								   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].', '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' 
								   WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'
								   		AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$res_search_teacher[0][$GLOBALS['PARAM']['CODE_ETABLISSEMENT']].';';
				//echo $requete;exit;
			$res_teach_school_sect = $GLOBALS['conn']->GetAll($req_teach_school);
			$res_teach_school = $res_teach_school_sect[0][$GLOBALS['PARAM']['NOM_ETABLISSEMENT']];
			$res_teach_sector = $res_teach_school_sect[0][get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])];
		}	
	
		$req_zones_saisie =" SELECT	DICO_ZONE_SYSTEME.ID_ZONE, DICO_TRADUCTION.LIBELLE, DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE, DICO_ZONE_SYSTEME.TYPE_OBJET, DICO_ZONE.CHAMP_PERE, DICO_ZONE.TABLE_MERE, DICO_ZONE_SYSTEME.ACTIVER, DICO_ZONE.TYPE_ZONE_BASE,
					DICO_ZONE.TABLE_FILLE, DICO_ZONE.SQL_REQ, DICO_ZONE.CHAMP_FILS, DICO_ZONE.CHAMP_PERE
					FROM	DICO_ZONE_SYSTEME, DICO_ZONE, DICO_TRADUCTION
					WHERE	DICO_ZONE_SYSTEME.ID_ZONE = DICO_ZONE.ID_ZONE
					AND DICO_TRADUCTION.CODE_NOMENCLATURE=DICO_ZONE.ID_ZONE 
					AND	DICO_ZONE.ID_THEME = ".$str_theme_id."
					AND	DICO_ZONE_SYSTEME.ID_SYSTEME = ".$_SESSION['secteur']." 
					AND	DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme'
					AND	DICO_ZONE_SYSTEME.ACTIVER = 1
					AND NOM_TABLE='DICO_ZONE'
					AND CODE_LANGUE='".$_SESSION['langue']."'
					ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE
					";
	
		$list_col = $GLOBALS['conn_dico']->GetAll($req_zones_saisie);
	
		//echo'<pre> list colonnes :<br>';
		//print_r($list_col);
		
		//Enseignant existant dans autre(s) etablissement(s) dans cette meme année
		if($_GET['action']=="teach_checking" && is_array($res_search_teacher) && count($res_search_teacher)>0){
			echo "existing_teacher#$res_teach_school#$res_teach_sector";//Pour demande de confirmation avant de collecter plusieurs fois le meme enseignant dans la meme année
		}elseif($_GET['action']=="teach_checking" && is_array($res_search_teacher) && count($res_search_teacher)==0){
			echo "not_exist_teacher_year";//Pour collecter l'enseignant sans demande de confirmation
		}elseif($_GET['action']=="teach_get_data" && is_array($list_col) && is_array($GLOBALS['PARAM']['TEACH_VALID_FIELDS'])){
			//Recuperation/collecte données de l'enseignant 
			$tab_valid_fields = array(); 
			foreach($GLOBALS['PARAM']['TEACH_VALID_FIELDS'] as $col_valid_teach){
				foreach($list_col as $col){
					if($col['CHAMP_PERE'] == $col_valid_teach){
						$tab_valid_fields[$col['CHAMP_PERE']]['TYPE_ZONE_BASE'] = $col['TYPE_ZONE_BASE'];
						$tab_valid_fields[$col['CHAMP_PERE']]['TYPE_OBJET'] = $col['TYPE_OBJET'];
						break;
					}
				}
			}
		
			if(is_array($res_valid_teach)){
				$row_valid_teach = "";
				foreach($res_valid_teach as $res_teach){
					$cpt = 0;
					foreach($GLOBALS['PARAM']['TEACH_VALID_FIELDS'] as $col_valid_teach){
						if($cpt == 0){
							if($tab_valid_fields[$col_valid_teach]['TYPE_ZONE_BASE']=='int'){
								if(strtoupper($res_teach[$col_valid_teach])=='M') $res_teach[$col_valid_teach] = 1;
								elseif(strtoupper($res_teach[$col_valid_teach])=='F') $res_teach[$col_valid_teach] = 2;
								$row_valid_teach .= round($res_teach[$col_valid_teach]);
							}else	$row_valid_teach .= $res_teach[$col_valid_teach];
						}else{
							if($tab_valid_fields[$col_valid_teach]['TYPE_ZONE_BASE']=='int'){
								if(strtoupper($res_teach[$col_valid_teach])=='M') $res_teach[$col_valid_teach] = 1;
								elseif(strtoupper($res_teach[$col_valid_teach])=='F') $res_teach[$col_valid_teach] = 2;
								$row_valid_teach .= "#".round($res_teach[$col_valid_teach]);
							}else	$row_valid_teach .= "#".$res_teach[$col_valid_teach];
						}
						$cpt++;
					}
					break;
				}
				echo $row_valid_teach;
			}
		}
	}
}else{
	echo "no_action";
}
?>
