<?php 
session_start();
set_time_limit(0);
////Recuperation des varibles globales dans $_GET
ini_set("memory_limit", "64M");
$gets = '';
$i=0;
if(count($_GET)>0) {
	foreach($_GET as $cle => $val) {
		if(isset($_GET[$cle]) && !preg_match('`^annee`', $cle) && !preg_match('`^id_chaine_tmis`', $cle) && !preg_match('`^secteur`', $cle)) {
			$gets .= $cle . '=' . $val;
			if($i<(count($_GET)-1)) {
				$gets .= '&';
			}
		}
		$i++;
	}
}//print_r($_POST);
//On positionne la filtre, l'année et le secteur (si necessaire) choisis en session
if(isset($_GET['filtre']) && $_GET['filtre']<>'') $_SESSION['filtre']=$_GET['filtre'];
if(isset($_GET['annee']) && $_GET['annee']<>'') $_SESSION['annee']=$_GET['annee'];
if(isset($_GET['secteur']) && $_GET['secteur']<>'') $_SESSION['secteur']=$_GET['secteur'];
if(isset($_GET['sector']) && $_GET['sector']<>'') $_SESSION['sector']=$_GET['sector'];
//on lance le theme_manager et on inclue la classe dynamiquement avant d'inclure common.php (pour utilisation en session)
$GLOBALS['lancer_theme_manager'] 		= true;
$GLOBALS['lancer_theme_manager_classe'] = true;
$GLOBALS['theme_data_MAJ_ok'] 			= true;

$_SESSION['secteur'] = $_GET['sector'];
$_SESSION['sector'] = $_GET['sector'];
$_SESSION['code_etab'] = $_GET['code_etab'];
$GLOBALS['ne_pas_verifier_session'] = true;
require_once 'common.php';

//Modif Hebie pour gerer le changement dynamique de certaines variables globales: interventions db Nigeria
if (isset($_SESSION['type_ent_stat']) && $_SESSION['type_ent_stat'] <> '') {
	if ($_SESSION['type_ent_stat'] == 1 || $_SESSION['type_ent_stat'] == 22 || $_SESSION['type_ent_stat'] == 23 || $_SESSION['type_ent_stat'] == 29) {
		$GLOBALS['PARAM']['ETABLISSEMENT']	                        =	'ENUMERATION_AREA';
		$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	                    =	'CODE_ENUMERATION_AREA';
		$GLOBALS['PARAM']['NOM_ETABLISSEMENT']	                    =	'NAME_ENUMERATION_AREA';
		$GLOBALS['PARAM']['CODE_ADMINISTRATIF']	                    =	'NUMBER_ENUMERATION_AREA';
		$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']	            =	'';
		$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT']	            =	'CODE_ENUMERATION_AREA';
		$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']	            =	'ENUMERATION_AREA_ADMIN_ENTITY';
		if ($_SESSION['type_ent_stat'] == 23 || $_SESSION['type_ent_stat'] == 28) {
		  	$GLOBALS['PARAM']['TYPE_FILTRE']	                    =	'TYPE_PERIOD';
		}
		if ($_SESSION['type_ent_stat'] == 29 && isset($_GET['id_year_tc'])) {		
			$_SESSION['CODE_TYPE_YEAR_TC'] = $_GET['id_year_tc'];
		}
	} else if ($_SESSION['type_ent_stat'] == 10 || $_SESSION['type_ent_stat'] == 17 || $_SESSION['type_ent_stat'] == 18) {
		$GLOBALS['PARAM']['ETABLISSEMENT']	                        =	'TRAINING';
		$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	                    =	'CODE_TRAINING';
		$GLOBALS['PARAM']['NOM_ETABLISSEMENT']	                    =	'TRAINING_VENUE';
		if ($_SESSION['type_ent_stat'] == 17 || $_SESSION['type_ent_stat'] == 18) {
			$GLOBALS['PARAM']['CODE_ADMINISTRATIF']	                  =	'DATE_REPORTING_COMPLETION';
		} else {
			$GLOBALS['PARAM']['CODE_ADMINISTRATIF']	                  =	'DATE_TRAINING';
		}
		$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']	            =	'';
		$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT']	              =	'CODE_TRAINING';
		$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']	            =	'TRAINING_ADMIN_ENTITY';
	} else if($_SESSION['type_ent_stat'] == 6 || $_SESSION['type_ent_stat'] == 21 || $_SESSION['type_ent_stat'] == 7 || $_SESSION['type_ent_stat'] == 13) {
		$GLOBALS['PARAM']['ETABLISSEMENT']	                        =	'COLLEGE';
		$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	                    =	'CODE_COLLEGE';
		$GLOBALS['PARAM']['NOM_ETABLISSEMENT']	                    =	'NAME_COLLEGE';
		$GLOBALS['PARAM']['CODE_ADMINISTRATIF']	                    =	'CODE_ADM_COLLEGE';
		$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']	            =	'';
		$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT']	              =	'CODE_COLLEGE';
		$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']	            =	'COLLEGE_ADMIN_ENTITY';
		if(in_array($_SESSION['type_ent_stat'], array(21,7))) {
		  $GLOBALS['PARAM']['TYPE_FILTRE']	                          =	'TYPE_PERIOD';
		}
	} else if($_SESSION['type_ent_stat'] == 8 || $_SESSION['type_ent_stat'] == 14) {
		$GLOBALS['PARAM']['ETABLISSEMENT']	                        =	'CONTACT_PERSON';
		$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	                    =	'CODE_CONTACT_PERSON';
		$GLOBALS['PARAM']['NOM_ETABLISSEMENT']	                    =	'NAME_CONTACT_PERSON';
		$GLOBALS['PARAM']['CODE_ADMINISTRATIF']	                    =	'SUBEB_CONTACT_PERSON_PHONE';
		$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']	            =	'';
		$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT']	              =	'CODE_CONTACT_PERSON';
		$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']	            =	'CONTACT_PERSON_ADMIN_ENTITY';
		if($_SESSION['type_ent_stat'] == 8) {
		  $GLOBALS['PARAM']['TYPE_FILTRE']	                          =	'TYPE_PERIOD';
		}
	} else if($_SESSION['type_ent_stat'] == 15 || $_SESSION['type_ent_stat'] == 9) {
		$GLOBALS['PARAM']['ETABLISSEMENT']	                        =	'CONTACT_PERSON';
		$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	                    =	'CODE_CONTACT_PERSON';
		$GLOBALS['PARAM']['NOM_ETABLISSEMENT']	                    =	'NAME_CONTACT_PERSON';
		$GLOBALS['PARAM']['CODE_ADMINISTRATIF']	                    =	'LGEA_CONTACT_PERSON_PHONE';
		$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']	            =	'';
		$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT']	              =	'CODE_CONTACT_PERSON';
		$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']	            =	'CONTACT_PERSON_ADMIN_ENTITY';
		if($_SESSION['type_ent_stat'] == 9) {
		  $GLOBALS['PARAM']['TYPE_FILTRE']	                          =	'TYPE_PERIOD';
		}
	} else if($_SESSION['type_ent_stat'] == 19 || $_SESSION['type_ent_stat'] == 20) {
		$GLOBALS['PARAM']['ETABLISSEMENT']	                        =	'LGA_TEACHER_PROFILE';
		$GLOBALS['PARAM']['CODE_ETABLISSEMENT']	                    =	'CODE_LGA_TEACHER_PROFILE';
		$GLOBALS['PARAM']['NOM_ETABLISSEMENT']	                    =	'NAME_LGA_TEACHER_PROFILE';
		$GLOBALS['PARAM']['CODE_ADMINISTRATIF']	                    =	'';
		$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']	            =	'';
		$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT']	              =	'CODE_LGA_TEACHER_PROFILE';
		$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']	            =	'LGA_TEACHER_PROFILE_ADMIN_ENTITY';
	} else if(($_SESSION['type_ent_stat'] == 33 || $_SESSION['type_ent_stat'] == 30 || $_SESSION['type_ent_stat'] == 5 || $_SESSION['type_ent_stat'] == 12) && $GLOBALS['PARAM']['FILTRE'] && $GLOBALS['PARAM']['TYPE_FILTRE']<>'') {
		$GLOBALS['PARAM']['TYPE_FILTRE']	                          =	'TYPE_PERIOD';
	}
}
//Fin Modif Hebie pour gerer le changement dynamique de certaines variables globales: interventions db Nigeria
$theme_manager->charger_theme("", $_GET['sector']);
$theme_manager->set_theme_courant();
$theme_manager->set_classe();
unset($_SESSION['reg_parents']);
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
					
//fin generation de theme pendant la saisie
//echo "THEME : ".$theme_manager->id;

//////////////////////// traitement switch_theme
		require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/switch_theme.class.php';		
		$switch_theme  = new switch_theme($theme_manager->id);
		$switch_theme->init();
//////////////////////// fin traitement switch_theme

lit_libelles_page(__FILE__);


function extraire_valeur_matrice($texte){
	// cette permet l'extraction de la valeur encodée dans le champ de type  matriciel 
	$return = $texte;
	if(preg_match('/_/',$texte)){
			$val = explode('_',$texte);
			$return = $val[count($val)-1];
	}
	return ($return);
}

// Affichage des infos sur l'établissement en cours

if(	(isset($_GET['code_etab']) && !isset($_GET['tmis'])) || (isset($_GET['ligne'])) || (isset($_GET['action']) && $_GET['action']=='add_new_teach')) {
	if((isset($_GET['code_etab']) && !isset($_GET['tmis']))){
		if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
		if(isset($_SESSION['list_themes_desact'])) unset($_SESSION['list_themes_desact']);
		$_SESSION['code_etab'] = $_GET['code_etab'];
	}
	if ($_SESSION['code_etab']<>'') {
		$conn = $GLOBALS['conn'];
		$GLOBALS['code_etab'] = $_SESSION['code_etab'];
		//Recherche le code_regroupement à partir du code_etab
		$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
					   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'
					   WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='. $_SESSION['code_etab'].';';
		//et mise en session		
		$code_regroups = $conn->GetAll($requete); //echo "<pre>"; print_r($code_regroups);
		$arbre_niv = new arbre($_SESSION['chaine']);
		$last_niv_num = 0;
		foreach($arbre_niv->chaine as $entry) {
			if ($entry['HIERARCHY_LEVEL'] > $last_niv_num ) {
				$last_niv_num = $entry['HIERARCHY_LEVEL'];
			}
		}
		$code_regroup = 0;
		foreach($code_regroups as $code) {
			$code_regroup = $code;
			if ($arbre_niv->get_depht_regroup($code[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]) == $last_niv_num) {
				break;
			}
		}
		//if($_SESSION['code_regroupement']<>$code_regroup){
		$_SESSION['code_regroupement']=$code_regroup;
		$requete ='SELECT '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
				FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['HIERARCHIE'].','.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 
				WHERE '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
				AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
				AND  '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$_SESSION['code_regroupement'].'
				AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'=1
				AND  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'];
		//et mise en session
		$_SESSION['chaine'] = $conn->GetOne($requete);
		
		//Recherche du nombre de niveaux de la chaine
		$requete ='SELECT COUNT(*)
					FROM '.$GLOBALS['PARAM']['HIERARCHIE'].'
					WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$_SESSION['chaine'];
		$niveau = $conn->GetOne($requete)-1;
		
		//La classe arbre permet de recomposer la hiérarchie des regroupements
		$arbre = new arbre($_SESSION['chaine']);
		//}else{
		//	$arbre = new arbre($_SESSION['chaine']);
		//	$hierarchie = $arbre->getparentsid(substr($_SESSION['nom_regroupement'],1),$_SESSION['code_regroupement']);
		//}
		$_SESSION['hierarchie_regroup'] = '';
		if (count($hierarchie) > 0) {
			$_SESSION['hierarchie_regroup'] .= '-';
		}
		$_SESSION['infos_etab'] = "-";
		if(isset($_SESSION['infos_data_entry'])) unset($_SESSION['infos_data_entry']);
	}
}
if(isset($_SESSION['tab_entite_stat']) && is_array($_SESSION['tab_entite_stat']) && count($_SESSION['tab_entite_stat'])>0){
	foreach ($_SESSION['tab_entite_stat'] as $ent_stat){
		$_SESSION['theme_manager'.$ent_stat[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] = ${'theme_manager'.$ent_stat[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]};
	}
}
if(isset($_GET['theme']) && $_GET['theme']<>'') 
	$_SESSION['theme']=$_GET['theme'];
elseif(isset($_GET['theme_frame']) && $_GET['theme_frame']<>''){ 
	$_SESSION['theme']=$_GET['theme_frame'];
	$_GET['theme']=$_GET['theme_frame'];
}

//verification de la periodicite du theme courant
$long_syst_id=strlen(''.$_SESSION['secteur']);
$long_theme_syst_id=strlen(''.$_GET['theme']);
$long_theme_id=$long_theme_syst_id-$long_syst_id;
$str_theme_id=substr($_GET['theme'],0,$long_theme_id);
$requete= "SELECT CHAMP_PERE FROM DICO_ZONE WHERE ID_THEME=".$str_theme_id;
$result=$GLOBALS['conn_dico']->GetAll($requete); 
$theme_periodique=false;
if(is_array($result)){
	foreach($result as $rs){
		if($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']==$rs['CHAMP_PERE']){
			$theme_periodique=true;
			break;
		}
	}
}

if( (trim($_SESSION['hierarchie_regroup']) <> '') or (trim($_SESSION['infos_etab']) <> '') ){ 
	$_SESSION['theme_manager'] = $theme_manager;
	//Filtrage sur les filtres	
	if($GLOBALS['PARAM']['FILTRE'] && $theme_periodique){
		$filtres = array();
		$tab_codes_filtres = array();
		if(isset($GLOBALS['PARAM']['CODES_DEBUT_ANNEE']) && count($GLOBALS['PARAM']['CODES_DEBUT_ANNEE']) && in_array($_GET['theme'],$GLOBALS['PARAM']['THEMES_DEBUT_ANNEE'])){	
			foreach($_SESSION['tab_filtres'] as $period){
				if(in_array($period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']],$GLOBALS['PARAM']['CODES_DEBUT_ANNEE'])){
					$filtres[] = $period;
					$tab_codes_filtres[] = $period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
				}
			}
		}
		if(isset($GLOBALS['PARAM']['CODES_TRIMESTRIEL']) && count($GLOBALS['PARAM']['CODES_TRIMESTRIEL']) && in_array($_GET['theme'],$GLOBALS['PARAM']['THEMES_TRIMESTRIEL'])){	
			foreach($_SESSION['tab_filtres'] as $period){
				if(in_array($period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']],$GLOBALS['PARAM']['CODES_TRIMESTRIEL'])){
					$filtres[] = $period;
					$tab_codes_filtres[] = $period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
				}
			}
		}
		if(isset($GLOBALS['PARAM']['CODES_MENSUEL']) && count($GLOBALS['PARAM']['CODES_MENSUEL']) && in_array($_GET['theme'],$GLOBALS['PARAM']['THEMES_MENSUEL'])){	
			foreach($_SESSION['tab_filtres'] as $period){
				if(in_array($period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']],$GLOBALS['PARAM']['CODES_MENSUEL'])){
					$filtres[] = $period;
					$tab_codes_filtres[] = $period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
				}
			}
		}
		if(isset($GLOBALS['PARAM']['CODES_SEMESTRIEL']) && count($GLOBALS['PARAM']['CODES_SEMESTRIEL']) && in_array($_GET['theme'],$GLOBALS['PARAM']['THEMES_SEMESTRIEL'])){	
			foreach($_SESSION['tab_filtres'] as $period){
				if(in_array($period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']],$GLOBALS['PARAM']['CODES_SEMESTRIEL'])){
					$filtres[] = $period;
					$tab_codes_filtres[] = $period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
				}
			}
		}
		if(isset($GLOBALS['PARAM']['CODES_FIN_ANNEE']) && count($GLOBALS['PARAM']['CODES_FIN_ANNEE']) && in_array($_GET['theme'],$GLOBALS['PARAM']['THEMES_FIN_ANNEE'])){	
			foreach($_SESSION['tab_filtres'] as $period){
				if(in_array($period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']],$GLOBALS['PARAM']['CODES_FIN_ANNEE'])){
					$filtres[] = $period;
					$tab_codes_filtres[] = $period[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
				}
			}
		}
		//echo "<pre>Selected period:".$_SESSION['filtre'];
		//print_r($filtres);
		if(count($filtres)>0){    
			if(!in_array($_SESSION['filtre'],$tab_codes_filtres)) $_SESSION['filtre'] = $filtres[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
			$_SESSION['tab_filtres_2'] = $filtres;
		}else{
			$_SESSION['tab_filtres_2'] = $_SESSION['tab_filtres'];
		}
	}
	//Fin filtrage filtre
	
	$filter_list = array();
	if ($_SESSION['type_ent_stat'] == 12 || $_SESSION['type_ent_stat'] == 33) {
		 $requete = 'SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'_ANNUAL_Y_N AS YEAR, '.$GLOBALS['PARAM']['ETABLISSEMENT'].'_TERM1_Y_N AS TERM1, '.
				  $GLOBALS['PARAM']['ETABLISSEMENT'].'_TERM2_Y_N AS TERM2, '.$GLOBALS['PARAM']['ETABLISSEMENT'].'_TERM3_Y_N AS TERM3 '.
				  'FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].'_USAGE '.
				  'WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$_SESSION['code_etab'].' '.
				  'AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'='.$_SESSION['type_ent_stat'].' '. 
				  'AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_SESSION['annee'];  
	   $result = $conn->GetRow($requete);
	   foreach ($_SESSION['tab_filtres_2'] as $filter_entry) {
		  if ($filter_entry[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']]==1 && $result['YEAR']==1) {
			  //$filter_list[] = $filter_entry;
		  } else if ($filter_entry[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']]==2 && $result['TERM1']==1) {
			  $filter_list[] = $filter_entry;
		  } else if ($filter_entry[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']]==3 && $result['TERM2']==1) {
			  $filter_list[] = $filter_entry;
		  } else if ($filter_entry[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']]==4 && $result['TERM3']==1) {
			  $filter_list[] = $filter_entry;
		  }
	   }          
	} else if ($_SESSION['type_ent_stat'] == 5) {
	  $filter_list[] = $_SESSION['tab_filtres_2'][1];
	  $filter_list[] = $_SESSION['tab_filtres_2'][2];
	  $filter_list[] = $_SESSION['tab_filtres_2'][3];
	} else {
	  $filter_list = $_SESSION['tab_filtres_2'];
	}
}

//echo "<pre>";
//print_r($_SESSION['imput_all_tabms']);
//Fin drapeau theme verrouillé
//$theme_manager->set_theme_courant();
//$theme_manager->set_classe();
$requete  = "SELECT ACTION_THEME 
			 FROM DICO_THEME 
			 WHERE ID =".$theme_manager->id.";";
/*echo $requete."<pre>";
print_r($_SESSION['curobj_instance']);*/
$result_etab = $GLOBALS['conn_dico']->GetRow($requete); 
$nom_theme = $result_etab['ACTION_THEME'];

$curfile = $GLOBALS['SISED_PATH_INS'] . $nom_theme;
if(file_exists($curfile) and $nom_theme != '') {
	$curr_post = $_POST;
	$_POST = array();
    require $curfile;
	$_POST = $curr_post;
	require $curfile; 
} else {
    print $curfile.' Inexistant!<BR>';
}

//Gestion data entry by user
if(isset($_POST) && count($_POST)>0 && $GLOBALS['theme_data_MAJ_ok']== true){
	if( isset($_SESSION['imput_all_tabms'][$_SESSION['secteur']]) && count($_SESSION['imput_all_tabms'][$_SESSION['secteur']])  
		&& in_array($GLOBALS['PARAM']['DATA_ENTRY_TABLE'],$_SESSION['imput_all_tabms'][$_SESSION['secteur']])){
		$_SESSION['date'] = date('d/m/Y H:i:s');
		if(!isset($GLOBALS['PARAM']['FILTRE']) || !$GLOBALS['PARAM']['FILTRE']){
			$req_data_user = "UPDATE ".$GLOBALS['PARAM']['DATA_ENTRY_TABLE']." SET ".$GLOBALS['PARAM']['DATA_ENTRY_USER']."='".$_SESSION['login']."', ".$GLOBALS['PARAM']['DATA_ENTRY_DATE']."='".$_SESSION['date']."'".
								" WHERE ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$_SESSION['annee']." AND ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab'];
		}else{
			$req_data_user = "UPDATE ".$GLOBALS['PARAM']['DATA_ENTRY_TABLE']." SET ".$GLOBALS['PARAM']['DATA_ENTRY_USER']."='".$_SESSION['login']."', ".$GLOBALS['PARAM']['DATA_ENTRY_DATE']."='".$_SESSION['date']."'".
								" WHERE ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$_SESSION['annee']." AND ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab']." AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']."=".$_SESSION['filtre'];
		}
		if ($GLOBALS['conn']->Execute($req_data_user) === false){
			print '<span style="color: #FF0000;font-weight: bold;"> Erreur UPDATING : </span> "<br><em>'.$req_data_user.'</em>"<br>'; 
		}
		$_SESSION['infos_data_entry'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.recherche_libelle_page('DataEntryUser').' <b><u>'.$_SESSION['login'].'</u></b>'.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.recherche_libelle_page('DataEntryDate').' <b><u>'.$_SESSION['date'].'</u></b>' ;
	}
}
//Fin gestion user link to data entry

?>
