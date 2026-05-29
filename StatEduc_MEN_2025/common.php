<?php
require_once 'config_app.php';
require_once 'params.php';
require_once 'params_sys.php';
include 'constants.php';

require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);
manage_magic_quotes();
//if((isset($_GET['val']) && ($_GET['val']=='controle' || $_GET['val']=='incohr_list_etabs')) || (isset($_GET['tmis']))){
	set_time_limit(0);
	ini_set("memory_limit", "64M");
//}

//Verification de l'existance du  $GLOBALS['PARAM']['dico_DB']
//mise à jour par Nasser kailounasser@gmail.com
$dico_DB = '';
if(!isset($GLOBALS['PARAM']['dico_DB']) || $GLOBALS['PARAM']['dico_DB']=='') {
	$dico_DB='dico_DB';
	} else {
			$dico_DB=$GLOBALS['PARAM']['dico_DB'];
	}

//Variable ADODB
$ADODB_FETCH_MODE   = ADODB_FETCH_ASSOC;
$ADODB_CACHE_DIR    = $SISED_PATH . 'server-side/adodbcache/';
//Liberation de la session TMIS en cas de choix d'autres options du menu principal
if(isset($_GET['val']) && isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);

//Gestion des cookies pr l'exportation vers excel. Cas Tanzanie
if(isset($_POST['nb_regs_dispo']) && count((array)$_POST['nb_regs_dispo']) > 0 && isset($_POST['nb_annees_dispo']) && count($_POST['nb_annees_dispo']) > 0 ){
	if((!isset($_COOKIE['combo_regroup_0'])) || (isset($_COOKIE['combo_regroup_0']) && $_POST['combo_regroup_0'] != $_COOKIE['combo_regroup_0'])) {
		setcookie('combo_regroup_0', $_POST['combo_regroup_0'], time() + (3600 * 720 * 12));
	}
	for( $i = 0 ; $i < $_POST['nb_regs_dispo'] ; $i++ ){
			if(isset($_POST['REGS_'.$i]) and trim($_POST['REGS_'.$i]) <> ''){
					if((!isset($_COOKIE['REGS_'.$i])) || (isset($_COOKIE['REGS_'.$i]) && $_POST['REGS_'.$i] != $_COOKIE['REGS_'.$i])) {
						setcookie('REGS_'.$i, $_POST['REGS_'.$i], time() + (3600 * 720 * 12));
					}
			}else{
				// Suppression du cookie
				setcookie('REGS_'.$i);
				// Suppression de la valeur du tableau $_COOKIE
				unset($_COOKIE['REGS_'.$i]);
			}
	}
	for( $i = 0 ; $i < $_POST['nb_annees_dispo'] ; $i++ ){
			if(isset($_POST['ANN_'.$i]) and trim($_POST['ANN_'.$i]) <> ''){
					if((!isset($_COOKIE['ANN_'.$i])) || (isset($_COOKIE['ANN_'.$i]) && $_POST['ANN_'.$i] != $_COOKIE['ANN_'.$i])) {
						setcookie('ANN_'.$i, $_POST['ANN_'.$i], time() + (3600 * 720 * 12));
					}
			}else{
				// Suppression du cookie
				setcookie('ANN_'.$i);
				// Suppression de la valeur du tableau $_COOKIE  
				unset($_COOKIE['ANN_'.$i]);
			}
	}
	if($GLOBALS['PARAM']['FILTRE']){
		for( $i = 0 ; $i < $_POST['nb_filtres_dispo'] ; $i++ ){
				if(isset($_POST['PERIOD_'.$i]) and trim($_POST['PERIOD_'.$i]) <> ''){
						if((!isset($_COOKIE['PERIOD_'.$i])) || (isset($_COOKIE['PERIOD_'.$i]) && $_POST['PERIOD_'.$i] != $_COOKIE['PERIOD_'.$i])) {
							setcookie('PERIOD_'.$i, $_POST['PERIOD_'.$i], time() + (3600 * 720 * 12));
						}
				}else{
					// Suppression du cookie
					setcookie('PERIOD_'.$i);
					// Suppression de la valeur du tableau $_COOKIE  
					unset($_COOKIE['PERIOD_'.$i]);
				}
		}
	}
}
//Fin Cas Tanzanie
if(!(isset($_POST['login']) && isset($_POST['password'])) 
	&& (!isset($_GET['val']) || (isset($_GET['val']) && $_GET['val']<>'param_conn'))
	&& (!isset($_POST['val']) || (isset($_POST['val']) && $_POST['val']<>'param_conn'))){

	require_once $GLOBALS['SISED_PATH_LIB'] . 'connexion.inc.php';
	if($connexion->ok === true){ // Si connexion � la base r�ussie 
		
		/*function __autoload($class_name) {
			require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/' .$class_name . '.class.php';
		}*/
		
		function my_autoloader($class_name) {
			require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/' .$class_name . '.class.php';
		}
		spl_autoload_register('my_autoloader');
		
		ini_set('session.gc_maxlifetime', 3600); 
		session_start();
    	if(isset($_GET['filtre'])) $_SESSION['filtre'] = $_GET['filtre'];  
		if(isset($GLOBALS['placer_conn_dico']) && $GLOBALS['placer_conn_dico']) {
			$GLOBALS['conn'] = $GLOBALS['conn_dico'] ; 
		}
		require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/' . 'erreur_manager.class.php';
		/**
		 * Param�tres
		 *
		 */
		if($GLOBALS['PARAM']['USER_PRIVILEGE_MANAGEMENT'] && $_SESSION['groupe'] <> 1 && isset($_SESSION['code_user'])){
			$requete = "SELECT * FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER = ".$_SESSION['code_user'];
			$fixe_regroup = $GLOBALS['conn_dico']->GetAll($requete);
			if(count((array)$fixe_regroup) == 0){
				unset($_SESSION['valide']);
				$message = "No privilege defined. Please contact administrator!";
			}
		}
		
		$requete = "SELECT * FROM PARAM_DEFAUT;";
		$params = $GLOBALS['conn_dico']->GetRow($requete);

		$_SESSION['NB_NIVEAU_ARBO'] = $params['NB_NIVEAU_ARBO'];
		$_SESSION['ARMOIRIES_PAYS'] = $params['ARMOIRIES_PAYS'];
		$_SESSION['DRAPEAU_PAYS'] = $params['DRAPEAU_PAYS'];
		$_SESSION['NOM_PAYS'] = $params['NOM_PAYS'];
					
		if(!isset($_SESSION['langue']) || $_SESSION['langue']=='' || !isset($_SESSION['tab_langues']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
				$requete = "SELECT CODE_LANGUE, LIBELLE_LANGUE FROM DICO_LANGUE;";
				$tab_langues = $GLOBALS['conn_dico']->GetAll($requete);
				$langue_trouve = false;
				foreach($tab_langues as $langue){
					if(trim($params['CODE_LANGUE'])==trim($langue['CODE_LANGUE'])){
						$_SESSION['langue'] = trim($params['CODE_LANGUE']);
						$langue_trouve = true;
						break;
					}
				}
				if(!$langue_trouve){
					$_SESSION['langue'] = $tab_langues[0]['CODE_LANGUE'];
				}
				set_tab_session('langues', ''); 
				set_tab_session('secteurs', $_SESSION['langue']);
				if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>"")
					set_tab_session('entite_stat', $_SESSION['langue']);
		} else {
				if(isset($_GET['langue']) && $_GET['langue'] != $_SESSION['langue']) {
					$_SESSION['langue'] = $_GET['langue'];
					// Rechargement des libell�s pages
					$requete   = "SELECT LIBELLE, CODE_LIBELLE
									FROM DICO_LIBELLE_PAGE 
									WHERE CODE_LANGUE='".$_SESSION['langue']."';";
					$_SESSION['tab_libelles'] = $GLOBALS['conn_dico']->GetAll($requete);
					set_tab_session('secteurs', $_SESSION['langue']);
					if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>"")
						set_tab_session('entite_stat', $_SESSION['langue']);
				}
		}
		if(!isset($_SESSION['secteur']) || $_SESSION['secteur']=='' || !isset($_SESSION['tab_secteur']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
				if(!(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0)){
					$requete = ' SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].
								  ' FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].
								  ' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
				}else{
					$requete = ' SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].
								  ' FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].
								  ' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['fixe_secteurs']).')'.
								  ' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
				}
				$tab_secteurs = $GLOBALS['conn']->GetAll($requete);
				$secteur_trouve = false;
				foreach((array)$tab_secteurs as $secteur){
					if(trim($params['CODE_SECTEUR'])==trim($secteur[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']])){
						$_SESSION['secteur'] = trim($params['CODE_SECTEUR']);
						$secteur_trouve = true;
						break;
					}
				}
				if(!$secteur_trouve){
					$_SESSION['secteur'] = $tab_secteurs[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
				}
				set_tab_session('secteurs', $_SESSION['langue']);
				set_tab_session('chaines', '');  
				if ($GLOBALS['PARAM']['TYPE_PERIODE'] != '') {               
					set_tab_session('periodes', ''); 
				}               
		} else {
				//Il faut limiter l'utilisateur � ses secteurs en cas de limitation d'acces activ�e
				if(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0){
					if(!in_array($_SESSION['secteur'],$_SESSION['fixe_secteurs'])){
						$_SESSION['secteur'] = $_SESSION['tab_secteur'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
						set_tab_session('secteurs', $_SESSION['langue']);
					}
				}
				if(isset($_GET['secteur']) && $_GET['secteur'] != $_SESSION['secteur']) {
						$_SESSION['secteur'] = $_GET['secteur'];
						set_tab_session('secteurs', $_SESSION['langue']);
						// on a chang� de secteur, le code secteur de l'�tablissement en session peut ne pas correspondre
						// on vide alors les infos �tab
						unset ($_SESSION['hierarchie_regroup']); 
						unset ($_SESSION['infos_etab']);	
				}
		}
		if(!isset($_SESSION['chaine']) || $_SESSION['chaine']=='' || !isset($_SESSION['tab_chaines']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
				set_tab_session('chaines', '');                
		}
    if((!isset($_SESSION['periode']) || $_SESSION['periode']=='' || !isset($_SESSION['tab_periodes']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) && ($GLOBALS['PARAM']['TYPE_PERIODE'] != '')) {               
				set_tab_session('periodes', '');                
		}
		//gestion de l'ann�e
		if(!isset($_SESSION['annee']) || $_SESSION['annee']=='' || !isset($_SESSION['tab_annees']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
			$requete = ' SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].
						  ' FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].
						  ' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].';';
			$tab_annees = $GLOBALS['conn']->GetAll($requete);
			$annee_trouve = false;
			foreach((array)$tab_annees as $annee){
				if(trim($params['CODE_ANNEE'])==trim($annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']])){
					$_SESSION['annee'] = trim($params['CODE_ANNEE']);
					$annee_trouve = true;
					break;
				}
			}
			if(!$annee_trouve){
				$_SESSION['annee'] = $tab_annees[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']];
			}
			set_tab_session('annees', ''); 
		} else {
			if(isset($_GET['annee']) && $_GET['annee'] != $_SESSION['annee']) {
				$_SESSION['annee'] = $_GET['annee'];		
			}    
			if(isset($_GET['periode']) && $_GET['periode'] != $_SESSION['periode']) {
				$_SESSION['periode'] = $_GET['periode'];		
			}
		}
		//gestion du filtre
		if($GLOBALS['PARAM']['FILTRE'] && $GLOBALS['PARAM']['TYPE_FILTRE']<>''){
			if(!isset($_SESSION['filtre']) || $_SESSION['filtre']=='' || !isset($_SESSION['tab_filtres']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
				if(isset($GLOBALS['PARAM']['FILTRE_TABLE_ASSOC']) && $GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'] <> ''){
					$requete  = 'SELECT '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].
								' FROM '.$GLOBALS['PARAM']['TYPE_FILTRE'].','.$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'].'
									WHERE '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' = '.$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'].'.'.$GLOBALS['PARAM']['FILTRE_CHAMP_ASSOC'].'
									AND '.$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$_SESSION['code_etab'].'
									AND '.$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'].';';
				}else{
					$requete = ' SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].
								  ' FROM '.$GLOBALS['PARAM']['TYPE_FILTRE'].
								  ' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].';';
				}
				$tab_filtres = $GLOBALS['conn']->GetAll($requete);
				$filtre_trouve = false;
				foreach($tab_filtres as $filtre){
					if(trim($params['CODE_FILTRE'])==trim($filtre[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']])){
						$_SESSION['filtre'] = trim($params['CODE_FILTRE']);
						$filtre_trouve = true;
						break;
					}
				}
				if(!$filtre_trouve){
					$_SESSION['filtre'] = $tab_filtres[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
				}
				set_tab_session('filtres', ''); 
			} else {
				if(isset($_GET['filtre']) && $_GET['filtre'] != $_SESSION['filtre']) {
					$_SESSION['filtre'] = $_GET['filtre'];
				}
			}
		}
		//gestion du style
		if(!isset($_SESSION['style'])) {
			foreach($_COOKIE as $i=>$c) {
				if(preg_match('`^style_'.$_SESSION['login'].'$`', $i)) {
					$_SESSION['style'] = $c;
						break;
				} else {
					$_SESSION['style'] = trim($params['CODE_STYLE']);
				}
			}
			if(isset($_SESSION['style']) && isset($_SESSION['login'])) $_COOKIE['style_' . $_SESSION['login']] = $_SESSION['style'];
		} else {
			if(isset($_GET['style']) && $_GET['style'] != $_SESSION['style']) {
				$_SESSION['style'] = $_GET['style'];
				setcookie('style_' . $_SESSION['login'], $_GET['style'], time() + (3600 * 720));
				$_COOKIE['style_' . $_SESSION['login']] = $_GET['style'];
			}
		}
		
		//gestion tab_entite stat
		if(!(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>"") && isset($_SESSION['tab_entite_stat'])) unset($_SESSION['tab_entite_stat']);
		if((!isset($_SESSION['tab_entite_stat']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])) && isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>""){
			set_tab_session('entite_stat', $_SESSION['langue']);
		}
		//gestion de la selection des elements du filtre selon le niveau de rattachement et la table associ�e 
		if(isset($GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC']) && count($GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC']) > 0){
			set_tab_session('filtres', ''); 
		}
		
		if(isset($GLOBALS['PARAM']['FILTRE_TABLE_ASSOC']) && $GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'] <> ''){
			set_tab_session('filtres', ''); 
		}
		
		/**
		 * gestion des th�mes et des types de th�mes courant pour les questionnaires
		 *
		 */
		if(isset($GLOBALS['lancer_theme_manager']) && $GLOBALS['lancer_theme_manager']) {
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/theme_manager.class.php';
				if (isset($_GET['theme_frame'])) { 
					$_SESSION['id_theme_systeme']   = $_GET['theme_frame'];
				}elseif (isset($_GET['theme'])) { //si $_GET['theme'] existe alors le theme courant est $_GET['theme']
					$_SESSION['id_theme_systeme']  = $_GET['theme'];
				}
				if(isset($_SESSION['tab_entite_stat']) && is_array($_SESSION['tab_entite_stat']) && count($_SESSION['tab_entite_stat'])>0){
					foreach ($_SESSION['tab_entite_stat'] as $ent_stat){
						${'theme_manager'.$ent_stat[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]} = new theme_manager($ent_stat[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]);
						$_SESSION['theme_manager'.$ent_stat[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] = ${'theme_manager'.$ent_stat[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]};
					}
					if(isset($_GET['type_ent_stat'])){
						$theme_manager = ${'theme_manager'.$_GET['type_ent_stat']};
						$_SESSION['theme_manager'] = $theme_manager;
						$_SESSION['type_ent_stat'] = $_GET['type_ent_stat'];
					}elseif(isset($_SESSION['type_ent_stat'])){
						$theme_manager = ${'theme_manager'.$_SESSION['type_ent_stat']};
						$_SESSION['theme_manager'] = $theme_manager;
					}else{
						$theme_manager = ${'theme_manager'.$_SESSION['tab_entite_stat'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]};
						$_SESSION['theme_manager'] = $theme_manager;
						$_SESSION['type_ent_stat'] = $_SESSION['tab_entite_stat'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']];
					}
				}else{
					$theme_manager = new theme_manager();
				}

				if(isset($GLOBALS['lancer_theme_manager_classe']) && $GLOBALS['lancer_theme_manager_classe']) {
						
						if (file_exists($GLOBALS['SISED_PATH_CLS'] . 'metier/'.$theme_manager->classe.'.class.php')) {
							require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/'.$theme_manager->classe.'.class.php'; 
						} else {
							 /*	
								echo '<div align=center>
												<br><br><br><br>'.$GLOBALS['SISED_PATH_CLS'] . 'metier/'.$theme_manager->classe.'.class.php'.' : Fichier introuvable !'.
										'<br></div>';*/
						}
				}
		
		}
		/**
		 * gestion de la classe de nomenclature
		 *
		 */
		if(isset($GLOBALS['lancer_nomenclature']) && $GLOBALS['lancer_nomenclature']) {
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/nomenclature.class.php';
		}
		/**
		 * gestion de la classe de atlas
		 *
		 */
		if(isset($GLOBALS['lancer_atlas']) && $GLOBALS['lancer_atlas']) {
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/atlas.class.php';
		}
		/**
		 * gestion de la classe de atlas_pop
		 *
		 */
		if(isset($GLOBALS['lancer_atlas_pop']) && $GLOBALS['lancer_atlas_pop']) {
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/atlas_pop.class.php';
		}
		/**
		 * gestion de la classe de nomenc_data
		 *
		 */
		if(isset($GLOBALS['lancer_nomenc_data']) && $GLOBALS['lancer_nomenc_data']) {
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/nomenc_data.class.php';
		}
		/**
		 * gestion de la classe de traduction
		 *
		 */
		if(isset($GLOBALS['lancer_traduction']) && $GLOBALS['lancer_traduction']) {
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/traduction.class.php';
		}
		/**
		 * gestion de la classe de user
		 *
		 */
		if(isset($GLOBALS['lancer_user']) && $GLOBALS['lancer_user']) {
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/user.class.php';
		}
		/**
		* gestion de la clas chaine de regroupement
		*
		*/
		if(isset($GLOBALS['lancer_chaine']) && $GLOBALS['lancer_chaine']) {
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/chaine.class.php';
		}
		if(isset($GLOBALS['lancer_chaine']) && $GLOBALS['erreur_manager'] == true){ // si pas d'erreur affich�e : pour �viter headers already sent by 
				/*echo'
					<br>
					<link href="'.$SISED_PATH_CSS.'/defaut.css" rel="stylesheet" type="text/css">
					<div align=center>
							<form method="post" action="accueil.php">
									<INPUT type="submit" name="Submit" value="Retour">
							</form>
					</div>
					';*/
				//die();
		}
		//session_start();
		if(!isset($GLOBALS['ne_pas_verifier_session']) || !$GLOBALS['ne_pas_verifier_session']) {
				require_once $GLOBALS['SISED_PATH_INC'] . 'verif_session.php';
		}
		/**
		 * nettoyage des objets pass�s en session
		 *
		 */
		if(!isset($GLOBALS['lancer_theme_manager']) || !$GLOBALS['lancer_theme_manager']) {
				if(isset($_SESSION['curobj_instance'])) {
						//unset($_SESSION['curobj_instance']);
				}
		}
		/**
		 * gestion du logout
		 *
		 */
		if(isset($_GET['val']) && $_GET['val'] == 'logout') {
				session_destroy();
				header('Location: '.$SISED_AURL.'index.php');
		}

		//Recherche d'un �tablissement
		if ( isset($_GET['code_admin']) && (trim($_GET['code_admin']) <> '') ) {
			$db 	= $GLOBALS['conn']; 
			$requete    = 'SELECT '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].','.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' 
				   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' 
				   WHERE '.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' =\''. $_GET['code_admin'].'\'
				   AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$_SESSION['secteur'].';'; 
			
			$result_etab = $db->GetRow($requete);
			//echo $requete . '<br>';
			if (is_array($result_etab) && count($result_etab)){
				$_GET['code_etab'] = $result_etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']]; 
				unset($_SESSION['id_theme_systeme']);
				print '<script type="text/Javascript">';
				print " document.location.href='questionnaire.php?code_etab=".$_GET['code_etab']."&theme=".$theme_manager->recherche_theme_def()."';";
				print '</script>';
				exit();
			} else { 
				// Le code administratif n'a pas �t� trouv� dans la BD
				$code_admin_not_found = true ;
			}
		}elseif (isset($_GET['code_etab_rech']) && (trim($_GET['code_etab_rech']) <> '')) {
			$db 	= $GLOBALS['conn']; 
			$requete    = 'SELECT '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].','.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' 
				   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' 
				   WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='. $_GET['code_etab_rech'].'
				   AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$_SESSION['secteur'].';'; 
			$result_etab = $db->GetRow($requete);
			//echo $requete . '<br>';
			if (is_array($result_etab) && count($result_etab)){
				unset($_SESSION['id_theme_systeme']);
				print '<script type="text/Javascript">';
				print " document.location.href='questionnaire.php?code_etab=".$_GET['code_etab_rech']."&theme=".$theme_manager->recherche_theme_def()."';";
				print '</script>';
				exit();
			} else { 
				// Le code administratif n'a pas �t� trouv� dans la BD
				$code_etab_not_found = true ;
			}
		}
		//Fin recherche �tablissement
		/**
		 * Gestion de l'ordre de selection des requetes de regroupement
		 */
		if(isset($GLOBALS['PARAM']['ORDRE_TRIE_REGROUPEMENT'])){
			if($GLOBALS['PARAM']['ORDRE_TRIE_REGROUPEMENT']==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']){
				$GLOBALS['CHAMP_ORDRE'] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
			}elseif($GLOBALS['PARAM']['ORDRE_TRIE_REGROUPEMENT']==$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']){
				$GLOBALS['CHAMP_ORDRE'] =$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
			}elseif($GLOBALS['PARAM']['ORDRE_TRIE_REGROUPEMENT']==$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']){
				$GLOBALS['CHAMP_ORDRE'] =$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
			}
		}
		/**
		 * Gestion des param�tres du regroupement fixer
		 *
		 */
		if(!isset($_SESSION['id_user']) || $_SESSION['id_user']==0){
			$fixe_regroup = array();
			if(isset($_SESSION['code_user'])){
				$requete = "SELECT * FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER = ".$_SESSION['code_user'];
				$fixe_regroup = $GLOBALS['conn_dico']->GetAll($requete);
			}
			if(!(is_array($fixe_regroup) && count($fixe_regroup)>0)){
				$requete = "SELECT * FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER = 0";
				$fixe_regroup = $GLOBALS['conn_dico']->GetAll($requete);
			}
			//Ajout HEBIE pr filtrer les regroupements par secteur au niveau des privil�ges utilisateurs: 05 04 2016
			$_SESSION['fixe_secteurs'] = array();
			if(is_array($fixe_regroup) && count($fixe_regroup)>0){
				foreach($fixe_regroup as $fix_reg){
					if(!in_array($fix_reg['ID_SYSTEME'],$_SESSION['fixe_secteurs'])) $_SESSION['fixe_secteurs'][] = $fix_reg['ID_SYSTEME'];
				}
			}
			$fixe_regroup_sect = array();
			if(isset($_SESSION['code_user'])){
				$requete = "SELECT * FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER = ".$_SESSION['code_user'];
				$fixe_regroup_sect = $GLOBALS['conn_dico']->GetAll($requete);
			}
			if(!(is_array($fixe_regroup_sect) && count($fixe_regroup_sect)>0) && $_SESSION['secteur'] != ''){
				$requete = "SELECT * FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER = 0";
				$fixe_regroup_sect = $GLOBALS['conn_dico']->GetAll($requete);
			}
			//Fin Ajout HEBIE pr filtrer les regroupements par secteur au niveau des privil�ges utilisateurs
			$_SESSION['fixe_reg'] = array();
			$_SESSION['fixe_regs'] = array();
			$_SESSION['fixe_type_reg'] = array();
			$_SESSION['fixe_type_reg_tmp'] = array();
			$_SESSION['fixe_type_regs'] = array();
			$_SESSION['fixe_reg_parents_code'] = array();
			$_SESSION['fixe_reg_parents'] = array();
			$_SESSION['fixe_type_reg_parents'] = array();
			$_SESSION['fixe_chaines'] = array();
			if(is_array($fixe_regroup_sect) && count($fixe_regroup_sect)>0){
				foreach($fixe_regroup_sect as $fix_reg){
					//if(!in_array($fix_reg['ID_SYSTEME'],$_SESSION['fixe_secteurs'])) $_SESSION['fixe_secteurs'][] = $fix_reg['ID_SYSTEME'];//Mis en commentaire par HEBIE pr filtrer les regroupements par secteur au niveau des privil�ges utilisateurs: 05 04 2016
					//if(!in_array($fix_reg['ID_CHAINE'],$_SESSION['fixe_chaines'])) $_SESSION['fixe_chaines'][] = $fix_reg['ID_CHAINE'];
					$_SESSION['fixe_chaines'][$fix_reg['ID_SYSTEME']][] = $fix_reg['ID_CHAINE'];
					//if(!in_array($fix_reg['ID_TYPE_REGROUP'],$_SESSION['fixe_type_reg'])) $_SESSION['fixe_type_reg'][] = $fix_reg['ID_TYPE_REGROUP'];
					$_SESSION['fixe_type_reg'][$fix_reg['ID_CHAINE']][] = $fix_reg['ID_TYPE_REGROUP'];
					$user_regs = array();
					$user_regs = explode(',',$fix_reg['ID_REGROUP']);
					foreach($user_regs as $user_reg){
						//if(!in_array($user_reg,$_SESSION['fixe_reg'])) $_SESSION['fixe_reg'][] = $user_reg;
						$_SESSION['fixe_reg'][$fix_reg['ID_TYPE_REGROUP']][] = $user_reg;
					}
					$user_type_regs_parent = array();
					if($fix_reg['ID_TYPE_REGROUP_PARENTS']<>'') $user_type_regs_parent = explode(',',$fix_reg['ID_TYPE_REGROUP_PARENTS']);
					foreach($user_type_regs_parent as $user_type_reg_parent){
						if(!in_array($user_type_reg_parent,$_SESSION['fixe_type_reg_parents'])) $_SESSION['fixe_type_reg_parents'][] = $user_type_reg_parent;
						//$_SESSION['fixe_type_reg_parents'][$fix_reg['ID_CHAINE']][] = $user_type_reg_parent;
					}
					$user_regs_parent = array();
					if($fix_reg['ID_REGROUP_PARENTS']<>'') $user_regs_parent = explode(',',$fix_reg['ID_REGROUP_PARENTS']);
					foreach($user_regs_parent as $i => $user_reg_parent){
						if(!in_array($user_reg_parent,$_SESSION['fixe_reg_parents_code'])) $_SESSION['fixe_reg_parents_code'][] = $user_reg_parent;
						if(!in_array($fix_reg['ID_TYPE_REGROUP'],$_SESSION['fixe_type_reg_tmp'])) $_SESSION['fixe_type_reg_tmp'][] = $fix_reg['ID_TYPE_REGROUP'];
						//Modif HEBIE du 14 05 2019 pour permettre la fixation de plusieurs localit�s pour un user
						//if(!in_array($user_reg_parent,(array)($_SESSION['fixe_reg_parents'][$fix_reg['ID_TYPE_REGROUP']])))
							//$_SESSION['fixe_reg_parents'][$fix_reg['ID_TYPE_REGROUP']][] = $user_reg_parent;
						if(!in_array($user_reg_parent,(array)($_SESSION['fixe_reg_parents'][$_SESSION['fixe_type_reg_parents'][$i]])))
							$_SESSION['fixe_reg_parents'][$_SESSION['fixe_type_reg_parents'][$i]][] = $user_reg_parent;
					}
					
				}
				set_tab_session('secteurs', $_SESSION['langue']);
				$_SESSION['fixe_type_regs'] = $_SESSION['fixe_type_reg_parents'];

				foreach($_SESSION['fixe_type_reg'] as $fixe_type_reg){
					foreach($fixe_type_reg as $type_reg){
						$_SESSION['fixe_type_regs'][] = $type_reg;
					}
				}

				for($i=0; $i<count($_SESSION['fixe_type_reg_parents']); $i++){
					//Modif Hebie du 24 04 2019 pour regler l'affichage de l'arbre lorqu'on fixe au dela du deuxi�me niveau de la chaine
					//$_SESSION['fixe_regs'][$_SESSION['fixe_type_reg_parents'][$i]] = implode(',',$_SESSION['fixe_reg_parents'][$_SESSION['fixe_type_reg_tmp'][$i]]);
					//Modif HEBIE du 14 05 2019 pour permettre la fixation de plusieurs localit�s pour un user
					//$_SESSION['fixe_regs'][$_SESSION['fixe_type_reg_parents'][$i]] = $_SESSION['fixe_reg_parents_code'][$i];
					$_SESSION['fixe_regs'][$_SESSION['fixe_type_reg_parents'][$i]] = implode(',',$_SESSION['fixe_reg_parents'][$_SESSION['fixe_type_reg_parents'][$i]]);
				}
				foreach($_SESSION['fixe_reg'] as $type_reg=>$fixe_reg){
					$_SESSION['fixe_regs'][$type_reg] = implode(',',$fixe_reg);
				}
				/*echo "<pre>";
				print_r($_SESSION['fixe_regs']);*/
			}
		}
		
        if(isset($theme_manager)) {
            //$theme_manager->charger_theme();
            $theme_manager->set_theme_courant();
            //$theme_manager->set_classe();
            $_SESSION['id_theme_systeme'] = $theme_manager->id_theme_systeme;
        }
        //// bass : suppression �tablissement
		if (isset($_GET['sup_etab'])){
			sup_etab($_GET['sup_etab']);
		}
		if( $GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES'] && (trim($GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES']) <> '' ) ) {
			if(isset($GLOBALS['lancer_theme_manager']) && $GLOBALS['lancer_theme_manager']) {
				set_session_tabms_imputees($_SESSION['secteur']) ;
			}
		}
	}
}elseif((isset($_POST['login']) && isset($_POST['password'])) 
		|| (isset($_GET['val']) && $_GET['val']=='param_conn')
		|| (isset($_POST['val']) && $_POST['val']=='param_conn')){ // Si user non encore valide ou connexion impossible

		session_start();
		require_once $GLOBALS['SISED_PATH_CLS'] . 'connexion.class.php';
		$connexion = new connexion();
		$curcnx = $connexion->getActive();
		
		//mise à jour par Nasser kailounasser@gmail.com
		if (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb')){
			//require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
			$conn_dico = ADONewConnection('access');
			$conn_dico->Connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb' . ';Uid=Admin;Pwd=\'\';');
			$GLOBALS['conn_dico'] = $conn_dico;
		} elseif (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.accdb')){
			//require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
			$conn_dico = ADONewConnection('access');
			$conn_dico->Connect('Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $GLOBALS['SISED_PATH'] . 'server-side/dico_DB.accdb' . ';Uid=Admin;Pwd=\'\';');
			$GLOBALS['conn_dico'] = $conn_dico;
		} elseif($curcnx['type']=='mssql') {
	      	$conn_dico = ADONewConnection('mssqlnative');
			$conn_dico->setConnectionParameter('CharacterSet','UTF-8');
	  		//$conn_dico->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], 'dico_DB');	
			  $conn_dico->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $dico_DB);  
	  		$GLOBALS['conn_dico'] = $conn_dico;
	    } elseif($curcnx['type']=='mysql') {
	      	$conn_dico = ADONewConnection('mysqli');
			//$conn_dico->setConnectionParameter(MYSQLI_SET_CHARSET_NAME,'UTF-8');
			//$conn_dico->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], 'dico_DB');	
			//$conn_dico->PConnect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $curcnx['base']);
			$conn_dico->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $dico_DB);	
			$conn_dico->setCharset('utf8'); 			
	  		$GLOBALS['conn_dico'] = $conn_dico;
	    } elseif($curcnx['type']=='postgres9') {
			ini_set("display_errors",0);error_reporting(0);
	      	
			ini_set("display_errors",1);error_reporting(1);
	      $conn_dico = ADONewConnection('postgres9');//$conn_dico->debug = true;
	  		$conn_dico->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $dico_DB);	
	  		$GLOBALS['conn_dico'] = $conn_dico;
    	}
		//fin mise à jour Nasser
		
		$requete = "SELECT * FROM PARAM_DEFAUT;";
		$params = $GLOBALS['conn_dico']->GetRow($requete);
		if(!isset($_SESSION['langue']) || $_SESSION['langue']=='' || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
				$_SESSION['langue'] = trim($params['CODE_LANGUE']);
				//set_tab_session('secteurs', $_SESSION['langue']);
		} else {
				if(isset($_GET['langue']) && $_GET['langue'] != $_SESSION['langue']) {
					$_SESSION['langue'] = $_GET['langue'];
				}
		}
		if(!isset($_SESSION['secteur']) || $_SESSION['secteur']=='' || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
				$_SESSION['secteur']    =   trim($params['CODE_SECTEUR']);
				//set_tab_session('secteurs', $_SESSION['langue']);
		} else {
				//Il faut limiter l'utilisateur � ses secteurs en cas de limitation d'acces activ�e
				if(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0){
					if(!in_array($_SESSION['secteur'],$_SESSION['fixe_secteurs'])){
						$_SESSION['secteur'] = $_SESSION['tab_secteur'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
					}
				}
		
		}
		/*
		 * V�rification de session utilisateur et authentification
		 *
		 */
		if((isset($_POST) && count($_POST)>0)) {
			if(isset($_POST['login']) && isset($_POST['password'])) {
				valide_user($_POST['login'],md5($_POST['password']));
				valide_user_LogByEtab($_POST['login'], $_POST['password']);
				if(isset($_SESSION['valide']) && $_SESSION['valide']){
					header('Location: '.$SISED_AURL.'accueil.php');
				}
			}elseif((isset($_POST['btn_connexion']))) {
				if(isset($_SESSION['valide']) && $_SESSION['valide']){
					set_tab_session('secteurs', $_SESSION['langue']);
					// Les chaines de regroupement
					set_tab_session('chaines', '');                
					// Les ann�es
					set_tab_session('annees', ''); 
					// Les filtres
					set_tab_session('filtres', ''); 
					// Les langues
					set_tab_session('langues', ''); 
					
					header('Location: '.$SISED_AURL.'accueil.php');
				}else{
					session_destroy();
					header('Location: '.$SISED_AURL.'index.php');
				}
			}elseif(isset($_POST['btn_quitter'])) {
					session_destroy();
					header('Location: '.$SISED_AURL.'index.php');
			}
		}
		if(!isset($GLOBALS['ne_pas_verifier_session']) || !$GLOBALS['ne_pas_verifier_session']) {
				require_once $GLOBALS['SISED_PATH_INC'] . 'verif_session.php';
		}
		/**
		 * Param�tres
		 *
		 */
		$_SESSION['NB_NIVEAU_ARBO'] = $params['NB_NIVEAU_ARBO'];
		$_SESSION['ARMOIRIES_PAYS'] = $params['ARMOIRIES_PAYS'];
		$_SESSION['DRAPEAU_PAYS'] = $params['DRAPEAU_PAYS'];
		$_SESSION['NOM_PAYS'] = $params['NOM_PAYS'];
		// Rechargement des libell�s pages
		$requete   = "SELECT LIBELLE, CODE_LIBELLE
						FROM DICO_LIBELLE_PAGE 
						WHERE CODE_LANGUE='".$_SESSION['langue']."';";
		$_SESSION['tab_libelles'] = $GLOBALS['conn_dico']->GetAll($requete);
		//set_tab_session('secteurs', $_SESSION['langue']);
		//gestion de l'ann�e
		if(!isset($_SESSION['annee']) || $_SESSION['annee']=='' || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
				$_SESSION['annee']    =   trim($params['CODE_ANNEE']);
		} else {
				if(isset($_GET['annee']) && $_GET['annee'] != $_SESSION['annee']) {
						$_SESSION['annee'] = $_GET['annee'];		
				}
		}
		//gestion de la filtre
		if(!isset($_SESSION['filtre']) || $_SESSION['filtre']=='' || preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
				$_SESSION['filtre']    =   trim($params['CODE_FILTRE']);
		} else {
				if(isset($_GET['filtre']) && $_GET['filtre'] != $_SESSION['filtre']) {
						$_SESSION['filtre'] = $_GET['filtre'];
				}
		}
		//gestion du style
		if(!isset($_SESSION['style'])) {
			$_SESSION['style'] = trim($params['CODE_STYLE']);
		} else {
			if(isset($_GET['style']) && $_GET['style'] != $_SESSION['style']) {
				$_SESSION['style'] = $_GET['style'];
			}
		}
}
?>