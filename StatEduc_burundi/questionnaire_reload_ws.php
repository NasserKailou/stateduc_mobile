<?php

/**
 * questionnaire_reload_ws.php
 *
 * Endpoint interne de pre-remplissage du formulaire (rechargement des donnees existantes).
 * Appele via curl interne depuis data_reload.php pour pre-remplir le formulaire mobile.
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 * @sessions 1-24
 * @modifie Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
 *          Toutes les modifications et nouveautes sont documentees
 *          directement dans le code avec des commentaires en francais.
 */

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
//On positionne la filtre, l'annee et le secteur (si necessaire) choisis en session
if(isset($_GET['filtre']) && $_GET['filtre']<>'') $_SESSION['filtre']=$_GET['filtre'];
if(isset($_GET['annee']) && $_GET['annee']<>'') $_SESSION['annee']=$_GET['annee'];
// Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
// Session 24 : correction bug pre-remplissage formulaire mobile
// data_reload.php passe 'code_annee' mais ce fichier ne lisait que 'annee'.
// Resultat : $_SESSION['annee'] jamais initialise -> requetes DB retournaient annee vide -> aucune donnee pre-remplie.
// Fix : lire aussi 'code_annee' (priorite inferieure a 'annee' si les deux sont presents).
if(isset($_GET['code_annee']) && $_GET['code_annee']<>'' && (!isset($_SESSION['annee']) || $_SESSION['annee']=='')) {
    $_SESSION['annee'] = $_GET['code_annee'];
}
//fin correction session 24
if(isset($_GET['secteur']) && $_GET['secteur']<>'') $_SESSION['secteur']=$_GET['secteur'];
if(isset($_GET['sector']) && $_GET['sector']<>'') $_SESSION['sector']=$_GET['sector'];

// --- Bootstrap session mobile/curl (identique a questionnaire_ws.php) ------------------
// Quand appele via curl interne depuis data_reload.php, il n'existe pas de session navigateur.
// data_reload.php passe 'login' et 'langue' en GET pour que les requetes DICO_FIXE_REGROUPEMENT
// utilisent le bon utilisateur et que les libelles soient en bonne langue.
if(isset($_GET['login']) && $_GET['login']<>'')   $_SESSION['login']=$_GET['login'];
if(isset($_GET['langue']) && $_GET['langue']<>'') $_SESSION['langue']=$_GET['langue'];
// Valeurs par defaut pour eviter les erreurs fatales dans common.php / lit_libelles_page
if(!isset($_SESSION['langue'])  || $_SESSION['langue']=='')  $_SESSION['langue']='fr';
if(!isset($_SESSION['style'])   || $_SESSION['style']=='')   $_SESSION['style']='stateduc.css';
if(!isset($_SESSION['valide'])  )                            $_SESSION['valide']=true;
// Resolution du CODE_USER depuis ADMIN_USERS via le login, identique a questionnaire_ws.php
if(!isset($_SESSION['code_user']) || $_SESSION['code_user']==0) {
    if(isset($_SESSION['login']) && $_SESSION['login']<>'') {
        require_once 'config_app.php';
        require_once 'params.php';
        if(!isset($GLOBALS['conn_dico'])) {
            require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
            require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
            require_once $GLOBALS['SISED_PATH_CLS'] . 'connexion.class.php';
            $source = false;
            $_conn_bs = new connexion();
            $_conn_bs->init($source);
        }
        if(isset($GLOBALS['conn_dico'])) {
            $_sql_user = "SELECT CODE_USER, CODE_GROUPE FROM ADMIN_USERS WHERE NOM_USER='".$_SESSION['login']."'";
            $_row_user = $GLOBALS['conn_dico']->GetRow($_sql_user);
            if(is_array($_row_user) && isset($_row_user['CODE_USER']) && intval($_row_user['CODE_USER'])>0) {
                $_SESSION['code_user'] = intval($_row_user['CODE_USER']);
                if(isset($_row_user['CODE_GROUPE'])) $_SESSION['groupe'] = intval($_row_user['CODE_GROUPE']);
            }
        }
    }
}
if(!isset($_SESSION['code_user'])) $_SESSION['code_user']=0;
if(!isset($_SESSION['groupe']))    $_SESSION['groupe']=1;
// --- fin bootstrap session mobile ----------------------------------------------------------

//on lance le theme_manager et on inclue la classe dynamiquement avant d'inclure common.php (pour utilisation en session)
$GLOBALS['lancer_theme_manager'] 		= true;
$GLOBALS['lancer_theme_manager_classe'] = true;
$GLOBALS['theme_data_MAJ_ok'] 			= true;

$_SESSION['secteur'] = $_GET['sector'];
$_SESSION['sector'] = $_GET['sector'];
$_SESSION['code_etab'] = $_GET['code_etab'];
$GLOBALS['ne_pas_verifier_session'] = true;
require_once 'common.php';

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
		$_SESSION['code_regroupement']=is_array($code_regroup)?$code_regroup[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]:$code_regroup;
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

$curfile = $GLOBALS['SISED_PATH_INS'] . str_replace('grille', 'grille_reload_ws', $nom_theme); 
if(file_exists($curfile) and $nom_theme != '') {
	//$curr_post = $_POST;
	//$_POST = array();
    //require $curfile;
	//$_POST = $curr_post;
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
