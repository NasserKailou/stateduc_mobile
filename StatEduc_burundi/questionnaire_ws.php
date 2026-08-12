<?php
// SESSION 61 FIX DEFINITIF -- anti-deadlock reel
// ROOT CAUSE IDENTIFIEE (log kailou.log, ligne DIAG) :
//   Avant (session 34) : @session_start(['read_and_close' => true]) ligne 3
//   -> session ouverte en lecture seule et immediatement fermee.
//   Toutes les ecritures $_SESSION du bootstrap (login, code_user, secteur,
//   type_ent_stat...) etaient perdues (en memoire seulement, non persistees).
//   Puis require 'common.php' -> common.php:94 session_start() SANS read_and_close
//   et SANS guard session_status() -> essaie d'acquerir un verrou exclusif
//   -> BLOQUE INDEFINIMENT -> timeout 120s -> erreur cURL 28 -> KOSAVE sur TOUS les formulaires.
//
// FIX : ouvrir la session normalement (mode ecriture) pour que les ecritures
//   bootstrap (lignes ci-dessous) soient persistees sur disque, puis appeler
//   session_write_close() juste AVANT require 'common.php'.
//   common.php:94 session_start() s'executera sans conflit de verrou.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
//On positionne la filtre, l'ann�e et le secteur (si necessaire) choisis en session
// Note SESSION 59 : read_and_close (ligne 3) rend $_SESSION en lecture seule ici.
// Toute écriture dans $_SESSION est ignorée silencieusement.
// Le fix KOSAVE est donc dans instance_grille.php (préfère $_GET['filtre'] sur $_SESSION['filtre']).
if(isset($_GET['filtre']) && $_GET['filtre']<>'') $_SESSION['filtre']=$_GET['filtre'];
if(isset($_GET['annee']) && $_GET['annee']<>'') $_SESSION['annee']=$_GET['annee'];
if(isset($_GET['secteur']) && $_GET['secteur']<>'') $_SESSION['secteur']=$_GET['secteur'];
if(isset($_GET['sector']) && $_GET['sector']<>'') $_SESSION['sector']=$_GET['sector'];

// --- Mobile/curl session bootstrap -------------------------------------------
// When called via internal curl from data_save.php (app mobile), there is no
// browser session. data_save.php passes login and langue as GET params so the
// arbre class can write data under the correct user account and retrieve labels.
if(isset($_GET['login']) && $_GET['login']<>'')   $_SESSION['login']=$_GET['login'];
if(isset($_GET['langue']) && $_GET['langue']<>'') $_SESSION['langue']=$_GET['langue'];
// Defaults so common.php / CSS / libelle lookups never crash on empty values
if(!isset($_SESSION['langue'])  || $_SESSION['langue']=='')  $_SESSION['langue']='fr';
if(!isset($_SESSION['style'])   || $_SESSION['style']=='')   $_SESSION['style']='stateduc.css';
if(!isset($_SESSION['valide'])  )                            $_SESSION['valide']=true;
// Resolve real CODE_USER from ADMIN_USERS using login so arbre writes under the
// correct account and common.php loads DICO_FIXE_REGROUPEMENT for the right user.
// data_save.php access check queries ADMIN_USERS.CODE_USER - must match here.
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
if(!isset($_SESSION['groupe']))    $_SESSION['groupe']=1;  // groupe 1 = admin (bypasses most restrictions)
// -----------------------------------------------------------------------------

//on lance le theme_manager et on inclue la classe dynamiquement avant d'inclure common.php (pour utilisation en session)
$GLOBALS['lancer_theme_manager'] 		= true;
$GLOBALS['lancer_theme_manager_classe'] = true;
$GLOBALS['theme_data_MAJ_ok'] 			= true;

$_SESSION['secteur'] = $_GET['sector'];
$_SESSION['sector'] = $_GET['sector'];
$_SESSION['code_etab'] = $_GET['code_etab'];
$GLOBALS['ne_pas_verifier_session'] = true;
// Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
// Session 27 : fix bootstrap type_ent_stat + charger_theme APPARTENANCE pour themes mobiles
// Sans $_SESSION['type_ent_stat'], set_tab_session('chaines') charge la mauvaise chaine
// -> $_SESSION['chaine'] vide -> requetes arbre echouent -> infos_etab vide -> form non affiche.
// charger_theme sans APPARTENANCE charge tous themes secteur -> collision themes classique/mobile.
// Fix : injecter type_ent_stat en session avant common.php, puis charger_theme(APPARTENANCE, sector).
if(isset($_GET['type_ent_stat']) && $_GET['type_ent_stat']<>'') {
    $_SESSION['type_ent_stat'] = $_GET['type_ent_stat'];
}
// SESSION 61 : persiste toutes les ecritures bootstrap (login, code_user, secteur,
// type_ent_stat...) sur disque et libere le verrou de session.
// Sans ceci, common.php:94 session_start() bloquait indefiniment (deadlock)
// -> timeout 120s -> erreur 28 -> KOSAVE generalise sur tous les formulaires.
session_write_close();
require_once 'common.php';
// Recharger avec APPARTENANCE = campagne (type_ent_stat) pour isoler les themes mobiles
$_appart_mob = (isset($_GET['type_ent_stat']) && $_GET['type_ent_stat']<>'') ? $_GET['type_ent_stat'] : '';
$theme_manager->charger_theme($_appart_mob, $_GET['sector']);
$theme_manager->set_theme_courant();
$theme_manager->set_classe();
unset($_SESSION['reg_parents']);
if(isset($_GET['tmis'])) require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre4.class.php';
else require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';

//Ajout HEBIE pour la generation de theme pendant la saisie
if(isset($GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE']) && count($GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE'])>0){
	if(in_array($_GET['theme'],$GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE'])){
		$langues	=	array();
		$id_themes	 =	array();
		$id_systemes	=	array();	
		set_time_limit(0);
		array_push($langues, $_SESSION['langue']);
		$long_syst_id=strlen(''.$_SESSION['secteur']);
		$long_theme_syst_id=strlen(''.$_GET['theme']);
		$long_theme_id=$long_theme_syst_id-$long_syst_id;
		$str_theme_id=substr($_GET['theme'],0,$long_theme_id);
		array_push($id_themes, $str_theme_id);
		array_push($id_systemes, $_SESSION['secteur']);
		
		if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']){
			require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';
			$form	=	new frame_mobile( $id_themes, $langues, $id_systemes, $_SESSION['annee'], $_SESSION['code_etab'] );
		}
	}
}					
//fin generation de theme pendant la saisie
//echo "THEME : ".$theme_manager->id;

//////////////////////// traitement switch_theme
		require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/switch_theme.class.php';		
		$switch_theme  = new switch_theme($theme_manager->id);
		$switch_theme->init();
//////////////////////// fin traitement switch_theme

lit_libelles_page(__FILE__);

function load_theme($id_theme,$code_etab="",$type_ent_stat=""){
	print '<script type="text/Javascript">'."\n";
	if($type_ent_stat<>"")
		print '    document.location.href=\'?theme='.$id_theme.'&code_etab='.$code_etab.'&type_ent_stat='.$type_ent_stat.'\';'."\n";
	else
		print '    document.location.href=\'?theme='.$id_theme.'\';'."\n";
	print '</script>'."\n";
}

function hide_ID($id){
	print '<script type="text/Javascript">'."\n";
    print '    document.getElementById(\''.$id.'\').style.visibility = \'hidden\';'."\n";
    print '</script>'."\n";
}
function deactivate_ID($id){
	print '<script type="text/Javascript">'."\n";
	print 'if(eval("document.getElementById(\''.$id.'\')")){'."\n";
	print '	eval ("document.getElementById(\''.$id.'\')").disabled = true;'."\n";
	print '}'."\n";
    print '</script>'."\n";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
<TITLE>StatEduc 2 - <?php echo recherche_libelle_page('TypeAppli'); ?></TITLE>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS'] . $_SESSION['style']; ?>">
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS'] ."questionnaire.css"; ?>">
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>themes/jquery-ui/base/jquery.ui.all.css"/>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>themes/uniform/default/css/uniform.default.css"/>
<!--LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>themes/uniform/aristo/css/uniform.aristo.css"/-->
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>base.css"/>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/jquery-1.9.1.js"></script>
<!--script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/jquery-migrate-1.2.1.js"></script-->
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>uniform/jquery.uniform.js"></script>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/ui/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/jquery.blockUI.js"></script>
<script type="text/javascript" src="<?php if(isset($GLOBALS['PARAM']['UNIFORM_STYLE_ACTIVATED']) && $GLOBALS['PARAM']['UNIFORM_STYLE_ACTIVATED']==false) echo $GLOBALS['SISED_URL_JSC'].'stateduc_without_uniform_style.js'; else echo $GLOBALS['SISED_URL_JSC'].'stateduc.js' ?>"></script>
<script type="text/Javascript" language="javascript">
	var show_imput_form = false;
	var xhr = null; 
		
	function getXhr(){
		if(window.XMLHttpRequest){ // Firefox et autres
		   xhr = new XMLHttpRequest(); 
		}
		else if(window.ActiveXObject){ // Internet Explorer 
		   try {
					xhr = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
				}
		}
		else { // XMLHttpRequest non support� par le navigateur 
		   alert("<?php echo recherche_libelle_page('no_jajax');?>"); 
		   xhr = false; 
		} 
	}
	
	function run_imputation(cible, annee, check_action){
		getXhr();
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout re�u et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				run_imput_txt = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				//document.getElementById('imput_progress').innerHTML = run_imput_txt;
				if(cible != 'sup_teach') document.location.href='questionnaire.php?theme=<?php echo $_GET['theme']; if(isset($_GET['tmis'])) echo "&tmis=".$_GET['tmis'] ; ?>';
				else document.location.href='questionnaire.php?theme=<?php echo $_SESSION['theme_pere']; if(isset($_SESSION['tmis'])) echo "&tmis=".$_SESSION['tmis']; ?>';
			}
		}

		//url="administration.php?val=imputation";
		url="administration.php?val=imputation&cible_imput="+cible+"&annee_imput="+annee+"&action_imput="+check_action+"&theme=<?php echo $_GET['theme']; ?>";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
		//setTimeout("load_champs()",1000);	
	}
	
	function run_traitement_grille(list_chps, type){
		getXhr();
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout re�u et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				run_sort_txt = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				//document.getElementById('imput_progress').innerHTML = run_sort_txt;
				document.location.href='questionnaire.php?theme=<?php echo $_GET['theme']; if(isset($_GET['tmis'])) echo "&tmis=".$_GET['tmis'] ; if(isset($_GET['id_teacher'])) echo "&id_teacher=".$_GET['id_teacher'] ; if(isset($_GET['ligne'])) echo "&ligne=".$_GET['ligne'] ; ?>';
				
			}
		}
		if(type=='sort') url="administration.php?val=trier_grille&theme=<?php echo $_GET['theme']; ?>&sort=1"+list_chps;
		else if(type=='filter') url="administration.php?val=filtrer_grille&theme=<?php echo $_GET['theme']; ?>&filter=1"+list_chps;
		else if(type=='export_gril') url="administration.php?val=exporter_grille&theme=<?php echo $_GET['theme']; ?>&export_gril=1&list_chps="+list_chps;
		else if(type=='export_form_hist') url="administration.php?val=exporter_grille&theme=<?php echo $_GET['theme']; ?>&export_form_hist=1&list_chps="+list_chps;
		else if(type=='export_form_curr') url="administration.php?val=exporter_grille&theme=<?php echo $_GET['theme']; ?>&export_form_curr=1"+list_chps;
		else if(type=='cancelsort') url="administration.php?val=trier_grille&theme=<?php echo $_GET['theme']; ?>&cancelsort=1";
		else if(type=='cancelfilter') url="administration.php?val=filtrer_grille&theme=<?php echo $_GET['theme']; ?>&cancelfilter=1";
		
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
		//setTimeout("load_champs()",1000);	
	}
	//Desactiver des champs li�s � un reponse
	function desactiver_zone(){
		var i,j,k,bool,nom_zone,liste_zones,tab_chp_pere,val_chp_pere
		var args=desactiver_zone.arguments;
		var chp_pere=args[0];
		if(chp_pere != 'undefined' && chp_pere != ''){
			tab_chp_pere = chp_pere.split('_');
			val_chp_pere = tab_chp_pere[tab_chp_pere.length-1];
			if(val_chp_pere==1){
				bool = false;
			}else{
				bool = true;
			}
		}else{
			bool = true;
		}
		getXhr();
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout re�u et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				var codes_chps = xhr.responseText;
				//alert(codes_chps);
				var tab_codes_chps = codes_chps.split('#');
				for (i=0; i<(tab_codes_chps.length); i++){
					var tab_codes_chp = tab_codes_chps[i].split(';');
					nom_zone = args[i+1];
					for(var j = 0; j<(tab_codes_chp.length); j++){
						var val_code = "";
						if(tab_codes_chp[j]!="") val_code = "_"+tab_codes_chp[j];
						if(eval ('document.getElementById("'+nom_zone+'_0'+val_code+'")')){
							eval ('document.getElementById("'+nom_zone+'_0'+val_code+'").disabled = '+bool+';');
						}
					}
				}
			}
		}
		k = 0
		liste_zones = "";
		for (i=1; i<(args.length); i++){
			nom_zone = args[i];
			if(k==0) liste_zones += nom_zone;
			else liste_zones += ":"+nom_zone;
			k++;
		}
		url="saisie_donnees.php?val=desact_zone&bool="+bool+"&liste_zones="+liste_zones+"&theme=<?php echo $_GET['theme']; ?>";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
	}
	
	//Desactiver des themes li�s � un reponse
	function desactiver_theme(){	
		var i,j,bool,obj,tab_chp_pere,val_chp_pere
		var args=desactiver_theme.arguments;
		var chp_pere=args[0];
		if(chp_pere != 'undefined' && chp_pere != ''){
			tab_chp_pere = chp_pere.split('_');
			val_chp_pere = tab_chp_pere[tab_chp_pere.length-1];
			//alert(val_chp_pere);
			if(val_chp_pere==1){
				bool = "false";
			}else{
				bool = "true";
			}
		}else{
			bool = "true";
		}
	  	getXhr();
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout re�u et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				var xhr_txt = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
			}
		}
		var j = 0;
		var liste_themes = "";
		for (i=1; i<(args.length); i++){
			var theme_id = args[i];
			if(j==0) liste_themes += theme_id;
			else liste_themes += ":"+theme_id;
			j++;
		}
		url="saisie_donnees.php?val=desact_theme&bool="+bool+"&liste_themes="+liste_themes;
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
	}	
	//Teacher checking from database
	function teacher_validation(field_name,teach_number,alerter){
		if(!isset(alerter)){
			alerter = true;//for TMIS alerter will be false
		}
		getXhr();
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout re�u et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				var valid_teach_txt = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				if(valid_teach_txt != "" && valid_teach_txt != "no_action"){
					var row_valid_teach = valid_teach_txt.split('#');
					if(row_valid_teach[0] == "existing_teacher"){
						var msg_alert = "<?php echo recherche_libelle_page('Teach_Exist'); ?> \""+trim(row_valid_teach[1])+"\" <?php echo recherche_libelle_page('Teach_Sect'); ?> \""+trim(row_valid_teach[2])+"\" <?php echo recherche_libelle_page('Teach_Year'); ?> \n<?php echo recherche_libelle_page('Teach_Capt_Confirm'); ?>";
						if(alerter == true){
							if(confirm(msg_alert)){
								teacher_get_data(field_name,teach_number);//A revoir : faire en sorte que l'identifiant cl� primaire soit pris en compte pr eviter la cr�ation d'un nouvel enregistrement en cas d'identifiant incremental
							}
						}else{
							alert("<?php echo recherche_libelle_page('Teach_Exist'); ?> \""+trim(row_valid_teach[1])+"\" <?php echo recherche_libelle_page('Teach_Sect'); ?> \""+trim(row_valid_teach[2])+"\" <?php echo recherche_libelle_page('Teach_Year'); ?>");
							eval ("document.getElementById('"+field_name+"').value = \"\"");
							<?php
							/*if(in_array($_GET['theme'],$GLOBALS['PARAM']['TRANSFERT_ENSEIGNANT_THEMES']) ||
								in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) ||
								in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])){
								echo "OpenPopupRechEns('ENS',".$_GET['theme'].", teach_number);\n";
							}elseif(in_array($_GET['theme'],$GLOBALS['PARAM']['TRANSFERT_PERS_ADMIN_THEMES'])){
								echo "OpenPopupRechEns('NON_ENS',".$_GET['theme'].", teach_number);\n";
							}elseif(in_array($_GET['theme'],$GLOBALS['PARAM']['TRANSFERT_PERS_SERVICE_THEMES'])){
								echo "OpenPopupRechEns('SERV',".$_GET['theme'].", teach_number);\n";
							}*/
							?>
						}
					}else if(row_valid_teach[0] == "not_exist_teacher_year"){
						if(alerter == true){
							teacher_get_data(field_name,teach_number);//A revoir : faire en sorte que l'identifiant cl� primaire soit pris en compte pr eviter la cr�ation d'un nouvel enregistrement en cas d'identifiant incremental
						}else{
							var msg_alert = "<?php echo recherche_libelle_page('Teach_Exist2'); ?> \n<?php echo recherche_libelle_page('Teach_Capt_Confirm2'); ?>";
							if(confirm(msg_alert)){
								<?php
								if(in_array($_GET['theme'],$GLOBALS['PARAM']['TRANSFERT_ENSEIGNANT_THEMES']) ||
									in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) ||
									in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])){
									echo "OpenPopupRechEns('ENS',".$_GET['theme'].", teach_number);\n";
								}elseif(in_array($_GET['theme'],$GLOBALS['PARAM']['TRANSFERT_PERS_ADMIN_THEMES'])){
									echo "OpenPopupRechEns('NON_ENS',".$_GET['theme'].", teach_number);\n";
								}elseif(in_array($_GET['theme'],$GLOBALS['PARAM']['TRANSFERT_PERS_SERVICE_THEMES'])){
									echo "OpenPopupRechEns('SERV',".$_GET['theme'].", teach_number);\n";
								}
								?>
							}
						}
					}
				}else if(valid_teach_txt != "no_action" && alerter==true){
					alert("<?php echo recherche_libelle_page('Teach_Not_Found'); ?>");
				}
			}
		}

		//url="administration.php?val=imputation";
		url="saisie_donnees.php?val=valid_teach&teach_number="+teach_number+"&action=teach_checking&theme=<?php echo $_GET['theme']; ?>";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
		//setTimeout("load_champs()",1000);	
	}
	
	//Teacher validation from database
	function teacher_get_data(field_name,teach_number){
		getXhr();
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout re�u et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				var valid_teach_txt = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				if(valid_teach_txt != ""){
					var row_valid_teach = valid_teach_txt.split('#');
					var elts_line = field_name.split('_');
					num_line = elts_line[elts_line.length-1];
					var tab_valid_fields = new Array();
					var tab_valid_fields_exist = new Array();
					<?php
					if(isset($GLOBALS['PARAM']['TEACHER_VALIDATION']) && $GLOBALS['PARAM']['TEACHER_VALIDATION']==true
						&& is_array($GLOBALS['PARAM']['TEACH_VALID_FIELDS']) && count($GLOBALS['PARAM']['TEACH_VALID_FIELDS'])>0
						&& isset($GLOBALS['PARAM']['TEACH_VALID_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACH_VALID_THEMES'])){
						$long_syst_id=strlen(''.$_SESSION['secteur']);
						$long_theme_syst_id=strlen(''.$_GET['theme']);
						$long_theme_id=$long_theme_syst_id-$long_syst_id;
						$str_theme_id=substr($_GET['theme'],0,$long_theme_id);
					
						$requete =" SELECT	DICO_ZONE_SYSTEME.ID_ZONE, DICO_TRADUCTION.LIBELLE, DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE, DICO_ZONE_SYSTEME.TYPE_OBJET, DICO_ZONE.TABLE_MERE, DICO_ZONE_SYSTEME.ACTIVER, DICO_ZONE.TYPE_ZONE_BASE,
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
						//echo $requete ;			
						$list_col_valid_teach = $GLOBALS['conn_dico']->GetAll($requete); 
						//echo'<pre> list colonnes :<br>';
						//print_r($list_col_valid_teach);
						$teach_valid_fields = array();
						foreach($GLOBALS['PARAM']['TEACH_VALID_FIELDS'] as $col_valid_teach){
							echo "tab_valid_fields_exist['".$col_valid_teach."'] = 0;\n";
							if(is_array($list_col_valid_teach)){
								foreach($list_col_valid_teach as $col){
									if($col['CHAMP_PERE'] == $col_valid_teach){
										echo "tab_valid_fields['".$col['CHAMP_PERE']."'] = new Array();\n";
										echo "tab_valid_fields['".$col['CHAMP_PERE']."']['TYPE_ZONE_BASE'] = '".$col['TYPE_ZONE_BASE']."';\n";
										echo "tab_valid_fields['".$col['CHAMP_PERE']."']['TYPE_OBJET'] = '".$col['TYPE_OBJET']."';\n";
										echo "tab_valid_fields_exist['".$col_valid_teach."'] = 1;\n";
										break;
									}
								}
							}
						}
					
						$k = 0;
						foreach($GLOBALS['PARAM']['TEACH_VALID_FIELDS'] as $col_valid_teach){
							echo "if((tab_valid_fields_exist['$col_valid_teach']==1) && (tab_valid_fields['$col_valid_teach']['TYPE_OBJET']=='text' || tab_valid_fields['$col_valid_teach']['TYPE_OBJET']=='systeme_text')){\n";
								echo "if(document.getElementById('".$col_valid_teach."_'+num_line)){\n";
									echo "document.getElementById('".$col_valid_teach."_'+num_line).value = row_valid_teach[".$k."];\n";
								echo "}\n";
								echo "if(document.getElementById('".$col_valid_teach."_0_'+num_line)){\n";
									echo "document.getElementById('".$col_valid_teach."_0_'+num_line).value = row_valid_teach[".$k."];\n";
								echo "}\n";
							echo "}\n";
							echo "else if((tab_valid_fields_exist['$col_valid_teach']==1) && (tab_valid_fields['$col_valid_teach']['TYPE_OBJET']=='liste_radio' || tab_valid_fields['$col_valid_teach']['TYPE_OBJET']=='systeme_liste_radio' || tab_valid_fields['$col_valid_teach']['TYPE_OBJET']=='booleen')){\n"; 
								echo "if(document.getElementById('".$col_valid_teach."_'+num_line+'_'+row_valid_teach[".$k."])){\n";
									echo "document.getElementById('".$col_valid_teach."_'+num_line+'_'+row_valid_teach[".$k."]).checked = true;\n";
								echo "}\n";
							echo "}\n";
							/*echo "else if(tab_valid_fields['$col_valid_teach']['TYPE_OBJET']=='combo' || tab_valid_fields['$col_valid_teach']['TYPE_OBJET']=='systeme_combo'){\n";
								echo "if(document.getElementById('".$col_valid_teach."_'+num_line)){\n";
									echo "alert('".$col_valid_teach."_'+num_line+'_'+row_valid_teach[".$k."]);\n";
									echo "document.getElementById('".$col_valid_teach."_'+num_line).value = row_valid_teach[".$k."];\n";
								echo "}\n";
							echo "}\n";*/
							echo "else if((tab_valid_fields_exist['$col_valid_teach']==1) && (tab_valid_fields['$col_valid_teach']['TYPE_OBJET']=='checkbox')){\n";
								echo "if(document.getElementById('".$col_valid_teach."_'+num_line)){\n";
									echo "if(row_valid_teach[".$k."]==0 || row_valid_teach[".$k."]=='')\n";
									echo "document.getElementById('".$col_valid_teach."_'+num_line).checked = false;\n";
									echo "else if(row_valid_teach[".$k."]==1)\n";
									echo "document.getElementById('".$col_valid_teach."_'+num_line).checked = true;\n";
								echo "}\n";
							echo "}\n";
							$k++;
						}
					}
					?>
				}
			}
		}

		//url="administration.php?val=imputation";
		url="saisie_donnees.php?val=valid_teach&teach_number="+teach_number+"&action=teach_get_data&theme=<?php echo $_GET['theme']; ?>";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
		//setTimeout("load_champs()",1000);	
	}
	//End Teacher validation from payroll
	
	function export_teacher_records(){
		//alert('ici');
		var	popup	=	window.open('administration.php?val=action_export_teach','popup_teach_rec_export','toolbar=no,location=no,directories=no,menubar=no,scrollbars=no,status=no,resizable=no,width=320, height=35, left=400, top=200');
	}
	function import_teacher_records(type_theme, code_etab,teach_rec_file,log_file){
		//alert('ici');
		var	popup	=	window.open('administration.php?val=action_import_teach&type_theme='+type_theme+'&code_etab='+code_etab+'&teach_rec_file='+teach_rec_file+'&log_file='+log_file,'popup_teach_rec_import','toolbar=no,location=no,directories=no,menubar=no,scrollbars=no,status=no,resizable=no,width=320, height=35, left=400, top=200');
	}
	function centre(page,largeur,hauteur,options){
		var top=(screen.height-hauteur)/2;
		var left=(screen.width-largeur)/2;
		window.open(page,"","top="+top+",left="+left+",width="+largeur+",height="+hauteur+","+options);
	}
	function AlertSupEtab(code_etab){
		if(confirm("<?php echo recherche_libelle_page('AlertSupEt'); ?>")){ 
			document.location.href="saisie_donnees.php?val=choix_etablissement&sup_etab="+code_etab;
		}
	}
	function Set_Element(elem, form, val){
		eval('document.'+form+'.'+elem+'.value = '+val+' ;');
	}
	function changer_action(){
		document.forms['form1'].action = "questionnaire.php?<?php echo $gets; if(isset($_SESSION['debut'])) echo '&deb_save='.$_SESSION['debut']; ?>" ;
		//alert(document.forms['form1'].action);
	}
	function toggle_filtre(filtre){
    <?php 
      parse_str($gets, $output);
      unset($output['filtre']);
      $result = http_build_query($output);
    ?>
		document.location.href="?<?php echo $result; ?>&filtre="+filtre;
	}
	function toggle_annee(annee){   
    <?php 
      parse_str($gets, $output);
      unset($output['annee']);
      $result = http_build_query($output);
    ?>  
		document.location.href="?<?php echo $result; ?>&annee="+annee;
	}
	<?php
	$funct_toggle_combo_chaine = "\n";
	$funct_toggle_combo_chaine .= 'function toggle_combo_chaine(numCh) {' . "\n";
	$funct_toggle_combo_chaine .= "\n";
	$gets = '';
	$i_count_gets = 0 ;
	foreach($_GET as $i_get=>$g) {
		if(preg_match('`^theme`', $i_get) || preg_match('`^theme_pere`', $i_get) || preg_match('`^tmis`', $i_get) || preg_match('`^secteur`', $i_get)) {
			if($i_count_gets==0) $gets .= $i_get . '=' . $g;
			else $gets .= '&'.$i_get . '=' . $g;
			$i_count_gets++ ;
		}
	}
	$funct_toggle_combo_chaine .= "\t".'document.location.href = \'?'.$gets.'&id_chaine_tmis=\'+numCh;'."\n";
	$funct_toggle_combo_chaine .= "\n";
	$funct_toggle_combo_chaine .= '}' . "\n";
	print $funct_toggle_combo_chaine;
	?>
	function ouvrir_pop_imp_excel(){
		var	popup	=	window.open('saisie_donnees.php?val=PopupImportExcel','PopupImportExcel', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=500, height=175, left=250, top=50')
		popup.document.close();
		popup.focus();
	}
	function AlertImput(cible, annee){
		if(annee != "" ){
			//alert('check_action='+check_action+' cible='+cible+' annee='+annee);
			var check_action = "" ;
			var tab_lib_ann	 = new Array();
			
			<?php $requete = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
				$list_ann = $GLOBALS['conn']->GetAll($requete); 
								
				foreach ($list_ann as $i_ann => $ann){
					if($ann[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] == $_SESSION['annee']){
						$ord_curr_ann = $ann[$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] ;
					}
					print('tab_lib_ann['.$ann[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']].']='."'".$ann[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]."';");
				}
			?>
			if(document.getElementById('id_imputer').checked == true){check_action='imputer'}
			else if(document.getElementById('id_lever_impute').checked == true){check_action='lever_impute'}
			else if(cible == 'sup_teach'){check_action='sup_teach'}
			
			if(check_action != "" ){
				if(cible == 'all_thm'){var msg_cible = " <?php echo recherche_libelle_page('thisschool');?> ";}
				else if(cible == 'cur_thm'){var msg_cible = " <?php echo recherche_libelle_page('thistheme');?> ";}
				else if(cible == 'tmis_thm'){var msg_cible = " <?php echo recherche_libelle_page('tmistheme');?> ";}
				
				if(check_action == 'imputer'){var msg_alert = "<?php echo recherche_libelle_page('estimfor');?> "+msg_cible+" <?php echo recherche_libelle_page('whithyear');?> ("+ tab_lib_ann[annee] +") ?";}
				else if(check_action == 'lever_impute'){var msg_alert = "<?php echo recherche_libelle_page('cancelestim');?> " + msg_cible +' ?';}
				else if(check_action == 'sup_teach'){var msg_alert = "<?php echo recherche_libelle_page('AlertSupTeach');?> ";}
				
				if(confirm(msg_alert)){
					if(check_action != 'sup_teach'){
						//MM_displayLayers('imput_running','','block');
						MM_showHideLayers('form_imput','','hide','imput_running','','show');
					}
					run_imputation(cible, annee, check_action);
				}
			}else{alert("<?php echo recherche_libelle_page('tickaction');?>");}
		}else{
			alert("<?php echo recherche_libelle_page('yearoblig');?>");
		}
	}
	
	function sort_grille(nb_chp_gril, action){
			//alert(nb_chp_gril);
			var tab_list_chps_ord	 = "";
			var chp_ord = "";
			var ord = ""
			
			if(action=='sort'){
				for(var i=0; i<nb_chp_gril; i++){
					if(document.getElementById('chp_sort_'+i).value != ''){
						chp_ord = document.getElementById('chp_sort_'+i).value;
						if(document.getElementById('ord_asc_'+i).checked == true){ord='ASC';}
						else if(document.getElementById('ord_desc_'+i).checked == true){ord='DESC';}
						//tab_list_chps_ord[chp_ord] = ord;
						tab_list_chps_ord += "&"+chp_ord+"="+ord;
					}
				}
				
				if(tab_list_chps_ord != ""){
					//alert(tab_list_chps_ord);
					//MM_showHideLayers('form_sort','','hide','sort_running','','show');
					run_traitement_grille(tab_list_chps_ord, 'sort');
				}else{alert("<?php echo recherche_libelle_page('selectfield');?>");}
			}else if(action=='cancel'){
				run_traitement_grille(tab_list_chps_ord, 'cancelsort');
			}
	}	
	function trim(string) 
	{ 
		//alert('trim:'+string);
		if(typeof(string)!='undefined') return string.replace(/(^\s*)|(\s*$)/g,''); 
		//if(typeof(string)!='undefined') return string.replace(/^\s+/, '').replace(/\s+$/, '');
	} 
	function filter_grille(action){
			//alert(nb_chp_gril);
			var tab_list_chps_filter	 = "";
			var chp_filter = "";
			var val_filter = "";
			var operators = new Array("equalint","equalint_cb","equaltext","different","different_cb","contain","notcontain","start","end","superior","superior_cb","inferior","inferior_cb","between");
			var operators_int = new Array("equalint","equalint_cb","different","different_cb","superior","superior_cb","inferior","inferior_cb");
			if(action=='filter'){
				var select_op = false;
				if(document.getElementById('chp_filter').value != ''){
					chp_filter = document.getElementById('chp_filter').value;
					tab_list_chps_filter += "&champ="+chp_filter;	
					for(var i=0; i<operators.length; i++){
						if(document.getElementById(operators[i]).value != ''){
							val_filter = document.getElementById(operators[i]).value;
							tab_list_chps_filter += "&"+operators[i]+"="+val_filter;
							select_op = true;
						}
					}
				}else{alert("<?php echo recherche_libelle_page('selectfield');?>");}
				if(tab_list_chps_filter != "" && select_op){
					//alert(tab_list_chps_filter);
					//MM_showHideLayers('form_filter','','hide','filter_running','','show');
					var int_op_valide = true;
					var betw_op_valide = true;
					for(var i=0; i<operators_int.length; i++){
						if(document.getElementById(operators_int[i]).value != ''){
							val_filter = document.getElementById(operators_int[i]).value;
							reg=/^(0|([1-9][0-9]*))$/;
							if (!reg.test(val_filter)){
								int_op_valide = false;
							}
						}
					}
					if(document.getElementById('between').value != ''){
						val_filter = document.getElementById('between').value;
						var tab_val_betw = val_filter.split('-');
						var val1 = trim(tab_val_betw[0]);
						var val2 = trim(tab_val_betw[1]);
						reg=/^(0|([1-9][0-9]*))$/;
						if(tab_val_betw.length != 2 || !reg.test(val1) || !reg.test(val2)){
							betw_op_valide = false;
						}
					}
					if(int_op_valide && betw_op_valide) run_traitement_grille(tab_list_chps_filter, 'filter');
					if(!int_op_valide) alert("<?php echo recherche_libelle_page('int_required');?>");
					if(!betw_op_valide) alert("<?php echo recherche_libelle_page('betw_op_required');?>");
				}else{alert("<?php echo recherche_libelle_page('selectcriteria');?>");}
			}else if(action=='cancel'){
				run_traitement_grille(tab_list_chps_filter, 'cancelfilter');
			}
	}
	function isset(varname){
		//alert('isset:'+varname);
		return(typeof(varname)!='undefined');
	}
	function filter_criteria(){
		<?php
		if(isset($GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])
			|| isset($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])){
			$long_syst_id=strlen(''.$_SESSION['secteur']);
			$long_theme_syst_id=strlen(''.$_GET['theme']);
			$long_theme_id=$long_theme_syst_id-$long_syst_id;
			$str_theme_id=substr($_GET['theme'],0,$long_theme_id);
		
			$requete =" SELECT	DICO_ZONE_SYSTEME.ID_ZONE, DICO_TRADUCTION.LIBELLE, DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE, DICO_ZONE_SYSTEME.TYPE_OBJET, DICO_ZONE.TABLE_MERE, DICO_ZONE_SYSTEME.ACTIVER, DICO_ZONE.TYPE_ZONE_BASE,
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
			//echo $requete ;			
			$list_col = $GLOBALS['conn_dico']->GetAll($requete); 
			//echo'<pre> list colonnes :<br>';
			//print_r($list_col);
		}
		echo "var tab_val_nomenc = new Array();\n";
		if(is_array($list_col) && count($list_col)>0){
			foreach($list_col as $col){
				if($col['TABLE_FILLE']<>'' && $col['SQL_REQ']<>''){
					$list_val_nomenc = requete_nomenclature($col['TABLE_FILLE'], $col['CHAMP_FILS'], $col['CHAMP_PERE'], $_SESSION['langue'], $_SESSION['secteur'], $col['SQL_REQ']);
					echo "tab_val_nomenc['".$col['CHAMP_PERE']."'] = new Array();\n";
					//echo "alert('".$col['CHAMP_PERE']."');\n";
					echo " var i = 0;\n";
					foreach($list_val_nomenc as $val_nomenc){
						echo "tab_val_nomenc['".$col['CHAMP_PERE']."'][i] = new Array();\n";
						echo "tab_val_nomenc['".$col['CHAMP_PERE']."'][i]['valeur'] = ".$val_nomenc[get_champ_extract($col['CHAMP_PERE'])].";\n";
						echo "tab_val_nomenc['".$col['CHAMP_PERE']."'][i]['libelle'] = \"".$val_nomenc['LIBELLE']."\";\n";
						echo "i++;\n";
					}
				}
			}
		}
		?>
		var operators = new Array("equalint","equaltext","different","contain","notcontain","start","end","superior","inferior","between");
		val = document.getElementById('chp_filter').value;
		if(val != ''){
			var tab_chp = val.split('|');
			var chp = tab_chp[1].split('-');
			var champ = chp[1];
			var type_chp = tab_chp[0];
			var operators_int = new Array("equalint","different","superior","inferior","between");
			var operators_int_cb = new Array("equalint_cb","different_cb","superior_cb","inferior_cb");
			var operators_text = new Array("equaltext","contain","notcontain","start","end");
			
			if(type_chp=='int'){
				//A cacher
				for(var i=0; i<operators_text.length; i++){
					document.getElementById(operators_text[i]).value = '';
				}
				//tr_equal_combo
				if(isset(tab_val_nomenc[champ])){
					for(var i=0; i<operators_int.length; i++){
						if(operators_int[i] != 'between')	document.getElementById(operators_int[i]).value='';
						if(operators_int[i] != 'between')	document.getElementById('tr_'+operators_int[i]).style.display='none';
					}
					for(var j=0; j<operators_int_cb.length; j++){
						document.getElementById('tr_'+operators_int_cb[j]).style.display='block';
						var combo_nomenc = document.getElementById(operators_int_cb[j]);
						combo_nomenc.options.length=0;
						var nul_val = new Option( '---', '' );
						combo_nomenc.options[0] = nul_val;
						for(var i=1; i<=tab_val_nomenc[champ].length; i++){
							var temp = new Option( tab_val_nomenc[champ][i-1]['libelle'], tab_val_nomenc[champ][i-1]['valeur'] );
							combo_nomenc.options[i] = temp;
						}
					}	
				}else{
					for(var i=0; i<operators_int_cb.length; i++){
						document.getElementById('tr_'+operators_int_cb[i]).style.display='none';
						var combo_nomenc = document.getElementById(operators_int_cb[i]);
						combo_nomenc.options.length=0;
					}
					for(var i=0; i<operators_int.length; i++){
						if(operators_int[i] != 'between')	document.getElementById('tr_'+operators_int[i]).style.display='block';
					}
				}
				//
				document.getElementById('tb_text').style.visibility='hidden';
				//A montrer
				document.getElementById('tb_int').style.visibility='visible';
				//document.getElementById('tb_int').style.overflow='visible';
			}else if(type_chp=='text'){
				//A cacher
				for(var i=0; i<operators_int.length; i++){
					document.getElementById(operators_int[i]).value = '';
				}
				document.getElementById('tb_int').style.visibility='hidden';
				//A Montrer
				document.getElementById('tb_text').style.visibility='visible';
				//document.getElementById('tb_text').style.overflow='visible';
			}
		}
					
	}
	function export_grille(nb_chps,typ_thm){
		var list_chp_pere = "";
		var j = 0;
		for(var i=0; i<nb_chps; i++){
			if(document.getElementById('ord_export_'+i).checked == true){
				if(j==0) list_chp_pere += document.getElementById('chp_export_'+i).value;
				else list_chp_pere += ":"+document.getElementById('chp_export_'+i).value;
				//list_chp_pere += "&"+chp_ord+"="+ord;
				j++;
			}
		}
		//alert(list_chp_pere);	
		if(typ_thm=='gril') run_traitement_grille(list_chp_pere, 'export_gril');
		else if(typ_thm=='form_hist') run_traitement_grille(list_chp_pere, 'export_form_hist');
	}
	function export_form_curr(){
		var tab_list_chps_filter	 = "";
		run_traitement_grille(tab_list_chps_filter, 'export_form_curr');
	}
	function export_form_hist(){
		var tab_list_chps_filter	 = "";
		run_traitement_grille(tab_list_chps_filter, 'export_form_hist');
	}
	function MM_findObj(n, d) { //v4.01
	  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
		d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
	  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	  if(!x && d.getElementById) x=d.getElementById(n); return x;
	}
	
	function MM_showHideLayers() { //v6.0
	  var i,p,v,obj0,obj,args=MM_showHideLayers.arguments;
	  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) {
	  	v=args[i+2];
	  	obj0 = obj;
		if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
		if(v=='visible') {obj.display='block'; obj.position='absolute';}
		else {obj.display='none'; obj.position='relative';}
		//Pr cacher ou afficher le bouton export a l'ouverture de la div imputation
		/*if(obj0.id=='form_imput' && v=='visible'){
			document.getElementById('show_form_export').style.position='relative';
			document.getElementById('hide_form_export').style.position='relative';
		}else if(obj0.id=='form_imput' && v=='hidden'){
			document.getElementById('show_form_export').style.position='absolute';
			document.getElementById('hide_form_export').style.position='absolute';
		}*/
		//Pr cacher ou afficher les groupes de criteres associer au filtre
		if(obj0.id=='form_filter' && v=='visible'){
			document.getElementById('tb_text').style.position='absolute';
			document.getElementById('tb_int').style.position='absolute';
			var val = document.getElementById('chp_filter').value;
			var tab_chp = val.split('|');
			var type_chp = tab_chp[0];
			if(type_chp=='text') document.getElementById('tb_text').style.visibility='visible';
			else document.getElementById('tb_int').style.visibility='visible';
		}else if(obj0.id=='form_filter' && v=='hidden'){
			document.getElementById('tb_text').style.position='relative';
			document.getElementById('tb_int').style.position='relative';
			document.getElementById('tb_text').style.visibility='hidden';
			document.getElementById('tb_int').style.visibility='hidden';
		}
		obj.visibility=v;
		}
	}
	
	function MM_callJS(jsStr) { //v2.0
	  return eval(jsStr)
	}
</script>
</HEAD>
<BODY>
<script type="text/Javascript">	
	$.blockUI({ 
		css: { 
			border: 'none', 
			padding: '5px', 
			backgroundColor: '#000', 
			'-webkit-border-radius': '10px', 
			'-moz-border-radius': '10px', 
			opacity: .5, 
			color: '#fff'
		}, 
		message: "<h3><?php echo recherche_libelle_page('pageLoading'); ?></h3>"
	});
</script>
<div id="div_dialog">
	<!--p id="dialog_content" class="ui-widget-content ui-corner-all"></p-->
	<iframe id="dialog_content" class="ui-widget-content ui-corner-all" src="" ></iframe>
</div>
<div id="div_sousdialog">
	<!--p id="dialog_content" class="ui-widget-content ui-corner-all"></p-->
	<iframe id="sousdialog_content" class="ui-widget-content ui-corner-all" src="" ></iframe>
</div>
<a href="#" ID="backToTop"></a>
<div id="bandeau"></div>

<!-- Drapeau -->
<div id="drapeau"><img src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>drapeaux/<?php print trim($_SESSION['DRAPEAU_PAYS']); ?>.gif" width="44" height="29" border="0"></div>
<!-- Fin Drapeau -->

<!-- Affichage du menu  -->
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>menus/coolmenus4.js"></script>
<?php
if((!isset($_GET['tmis']) && (!isset($_SESSION['tmis']))) || (isset($_GET['code_etab']) && !isset($_GET['tmis']))){
if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
if(isset($_SESSION['liste_etablissements'])) unset($_SESSION['liste_etablissements']);
?>
<?php
if(isset($_GET['type_ent_stat'])){
	$type_ent_stat = $_GET['type_ent_stat'];
	$_SESSION['type_ent_stat'] = $type_ent_stat;
}elseif(isset($_SESSION['type_ent_stat'])){
	$type_ent_stat = $_SESSION['type_ent_stat'];
}
if(isset($type_ent_stat) && $type_ent_stat <>""){
?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC'] . 'menus/' . $_SESSION['groupe'] . '/' . $_SESSION['langue'] . '/' . $_SESSION['secteur']. '/' . $type_ent_stat;?>/_menu_saisie.js"></script>
<?php
}else{
?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC'] . 'menus/' . $_SESSION['groupe'] . '/' . $_SESSION['langue'] . '/' . $_SESSION['secteur'];?>/_menu_saisie.js"></script>
<?php
}
}else{
?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC'] . 'menus/' . $_SESSION['groupe'] . '/' . $_SESSION['langue'] . '/' ;?>/_menu_princ.js"></script>
<?php
}
?>
<!-- FIN Affichage du menu  -->

<DIV id="contenu">
<?php function new_infos_etab(){
    //Cette fonction rafraichit les infos descriptives de l'�tablissement quand on vient de le cr�er
    $conn = $GLOBALS['conn'];
	//Recherche le code_regroupement � partir du code_etab : Modif Hebie
	/*$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
				   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'
				   WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='. $_SESSION['code_etab'];
	//et mise en session
	$code_regroup = $conn->GetOne($requete);
	$_SESSION['code_regroupement']=$code_regroup;*/
	$requete ='SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'
			FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].', '.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['HIERARCHIE'].','.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 
			WHERE '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
			AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
			AND  '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'
			AND  '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$_SESSION['code_etab'].'
			AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$_SESSION['chaine'].'
			AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'=1
			AND  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'];
	//et mise en session
	$_SESSION['code_regroupement'] = $conn->GetOne($requete);
    //Recherche du nombre de niveaux de la chaine
    $requete ='SELECT COUNT(*)
                FROM '.$GLOBALS['PARAM']['HIERARCHIE'].'
                WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$_SESSION['chaine'];
    $niveau = $conn->GetOne($requete)-1;
    //La classe arbre permet de recomposer la hi�rarchie des regroupements
    $arbre = new arbre($_SESSION['chaine']);
    $hierarchie = $arbre->getparentsid($niveau,$_SESSION['code_regroupement'],$_SESSION['chaine']);
    //Mise en session de la string de la hi�rarchie
    $_SESSION['hierarchie_regroup'] = '';
    foreach($hierarchie as $i=>$h) {
	$_SESSION['hierarchie_regroup'] .= $h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
        if($i<(count($hierarchie)-1)) {
            $_SESSION['hierarchie_regroup'] .= '<b> / </b>';
        }
    }
    $_SESSION['hierarchie_regroup'] .= '<br>';
	$_SESSION['infos_etab'] = get_infos_etab();
	if(isset($_SESSION['infos_data_entry'])) unset($_SESSION['infos_data_entry']);
	if(isset($_SESSION['list_themes_desact'])) unset($_SESSION['list_themes_desact']);
}
function extraire_valeur_matrice($texte){
	// cette permet l'extraction de la valeur encod�e dans le champ de type  matriciel 
	$return = $texte;
	if(preg_match('/_/',$texte)){
			$val = explode('_',$texte);
			$return = $val[count($val)-1];
	}
	return ($return);
}
	// C'est un choix par introduction du code administratif
	// Il faut donc contr�ler l'existance de cet �tablissement
	if ( isset($_GET['code_admin']) && $code_admin_not_found == true) {
		print $_GET['code_admin'].' : '.recherche_libelle_page('CodAdmInex').'<BR>';
	}

    // Affichage des infos sur l'�tablissement en cours

	if(	(isset($_GET['code_etab']) && !isset($_GET['tmis'])) || (isset($_GET['ligne'])) || (isset($_GET['action']) && $_GET['action']=='add_new_teach')) {
		if((isset($_GET['code_etab']) && !isset($_GET['tmis']))){
			if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
			if(isset($_SESSION['list_themes_desact'])) unset($_SESSION['list_themes_desact']);
        	$_SESSION['code_etab'] = $_GET['code_etab'];
		}else{
			require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/teacher_manager.class.php';
			$teacher_manager = new teacher_manager(); 
			$teacher_manager->teacher_sql_data = $_SESSION['teacher_sql_data'];
			$teacher_manager->init();
			$new_teacher = false;
			if(isset($_GET['id_teacher'])){
				$_SESSION['id_teacher'] = $_GET['id_teacher'];
			}elseif((isset($_GET['action']) && $_GET['action']=='add_new_teach') && (!isset($_GET['deb_save']))){
				if(count($_POST) == 0){
					$new_teacher = true;
					$_SESSION['id_teacher'] = $teacher_manager->get_cle_max()+1;
				}
				$_GET['ligne'] = count($teacher_manager->list);
			}elseif((isset($_GET['action']) && $_GET['action']=='add_new_teach') && (isset($_GET['deb_save']))){
				//$_SESSION['id_teacher'] = $teacher_manager->get_cle_max()+1;
				$_GET['ligne'] = count($teacher_manager->list);
			}elseif(isset($_GET['ligne']) && !isset($_GET['deb_save'])){
				if(!isset($_SESSION['debut'])) $deb = 0;
				else  $deb = (int)($_SESSION['debut']/$_SESSION['nb_line']);
				if((count($teacher_manager->list)==0) || (!isset($teacher_manager->list[$_GET['ligne']+($deb*$_SESSION['nb_line'])]['ID_TEACHER'])) || ($teacher_manager->list[$_GET['ligne']+($deb*$_SESSION['nb_line'])]['ID_TEACHER']=='')){
					if(count($_POST) == 0){
						$new_teacher = true;
						$_SESSION['id_teacher'] = $teacher_manager->get_cle_max()+1;
					}
				}else{
					$_SESSION['id_teacher'] = $teacher_manager->list[$_GET['ligne']+($deb*$_SESSION['nb_line'])]['ID_TEACHER'];
				}
				//if(isset($_SESSION['debut'])) unset($_SESSION['debut']);
			}elseif(isset($_GET['ligne']) && isset($_GET['deb_save'])){
				$deb = (int)($_GET['deb_save']/$_SESSION['nb_line']);
				$_SESSION['id_teacher'] = $teacher_manager->list[$_GET['ligne']+($deb*$_SESSION['nb_line'])]['ID_TEACHER'];
				//if(isset($_SESSION['debut'])) unset($_SESSION['debut']);
			}
			$_GET['id_teacher'] = $_SESSION['id_teacher'];
			if(!$new_teacher) $_SESSION['code_etab'] = $teacher_manager->list_sch_teach[$_SESSION['id_teacher']];
			elseif(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1) {$_SESSION['code_etab'] = ''; $_SESSION['infos_etab'] = '';}
			if(isset($_POST[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_0'])){	
				$_SESSION['code_etab'] = extraire_valeur_matrice($_POST[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_0']);
			}
		}
		if($_SESSION['code_etab']<>''){
			$conn = $GLOBALS['conn'];
			$GLOBALS['code_etab'] = $_SESSION['code_etab'];
			//Recherche le code_regroupement � partir du code_etab : Modif Hebie
			/*$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
						   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'
						   WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='. $_SESSION['code_etab'];
			//et mise en session
			$code_regroup = $conn->GetOne($requete);
			$_SESSION['code_regroupement']=$code_regroup;*/
			$requete ='SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'
					FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].', '.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['HIERARCHIE'].','.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 
					WHERE '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
					AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
					AND  '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'
					AND  '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$_SESSION['code_etab'].'
					AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$_SESSION['chaine'].'
					AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'=1
					AND  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'];
			//et mise en session
			$_SESSION['code_regroupement'] = $conn->GetOne($requete);
			//Recherche du nombre de niveaux de la chaine
			$requete ='SELECT COUNT(*)
						FROM '.$GLOBALS['PARAM']['HIERARCHIE'].'
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$_SESSION['chaine'];
			$niveau = $conn->GetOne($requete)-1;
			//La classe arbre permet de recomposer la hi�rarchie des regroupements
			$arbre = new arbre($_SESSION['chaine']);
			$hierarchie = $arbre->getparentsid($niveau,$_SESSION['code_regroupement'],$_SESSION['chaine']);
			$_SESSION['hierarchie_regroup'] = '';
			foreach($hierarchie as $i=>$h) {
				$_SESSION['hierarchie_regroup'] .= $h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
				if($i<(count($hierarchie)-1)) {
					$_SESSION['hierarchie_regroup'] .= '<b> / </b>';
				}
			}
			$_SESSION['hierarchie_regroup'] .= '<br>';
			$_SESSION['infos_etab'] = get_infos_etab();
			if(isset($_SESSION['infos_data_entry'])) unset($_SESSION['infos_data_entry']);
					}else{
			$_SESSION['hierarchie_regroup'] = '';
			if($_SESSION['nom_reg_parents_tmis'])
			foreach($_SESSION['nom_reg_parents_tmis'] as $i=>$LIBELLE_REGROUPEMENT) {
				$_SESSION['hierarchie_regroup'] .= $LIBELLE_REGROUPEMENT;
				if($i<(count($_SESSION['nom_reg_parents_tmis'])-1)) {
					$_SESSION['hierarchie_regroup'] .= '<b> / </b>';
				}
			}
			
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
if(!isset($_GET['tmis'])){
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
	$disabled='';
	//if($_SESSION['groupe']<>1)
	//	$disabled='';
	if(isset($GLOBALS['PARAM']['RATTACHEMENT_FILTRE_PLUSIEURS']) && count($GLOBALS['PARAM']['RATTACHEMENT_FILTRE_PLUSIEURS'])>0){
		if(isset($_GET['type_ent_stat']) && $GLOBALS['PARAM']['RATTACHEMENT_FILTRE_PLUSIEURS'][$_GET['type_ent_stat']] == false){
			$disabled=' disabled="disabled"';
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
		if(!isset($_SESSION['infos_data_entry']) && isset($GLOBALS['PARAM']['DATA_ENTRY_BY_USER']) && $GLOBALS['PARAM']['DATA_ENTRY_BY_USER']){
			if(!isset($GLOBALS['PARAM']['FILTRE']) || !$GLOBALS['PARAM']['FILTRE']){
				$req_data_user = "SELECT ".$GLOBALS['PARAM']['DATA_ENTRY_USER'].", ".$GLOBALS['PARAM']['DATA_ENTRY_DATE'].
									" FROM ".$GLOBALS['PARAM']['DATA_ENTRY_TABLE'].
									" WHERE ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$_SESSION['annee']." AND ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab'];
			}else{
				$req_data_user = "SELECT ".$GLOBALS['PARAM']['DATA_ENTRY_USER'].", ".$GLOBALS['PARAM']['DATA_ENTRY_DATE'].
									" FROM ".$GLOBALS['PARAM']['DATA_ENTRY_TABLE'].
									" WHERE ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$_SESSION['annee']." AND ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab']." AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']."=".$_SESSION['filtre'];
			}
			$res_data_user = $GLOBALS['conn']->GetAll($req_data_user); 
			$_SESSION['infos_data_entry'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.recherche_libelle_page('DataEntryUser').' <b><u>'.$res_data_user[0][$GLOBALS['PARAM']['DATA_ENTRY_USER']].'</u></b>'.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.recherche_libelle_page('DataEntryDate').' <b><u>'.$res_data_user[0][$GLOBALS['PARAM']['DATA_ENTRY_DATE']].'</u></b>' ;
		}
		
	?>
	<!-- Rappel des infos etablissement -->
		<div id="rappel">
		<p>
		 
		<?php echo recherche_libelle_page('curr_year');?> : <b><u><?php echo get_libelle_from_array($_SESSION['tab_annees'], $_SESSION['annee'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']);?></u></b> 
		<?php	
			if($GLOBALS['PARAM']['FILTRE'] && $theme_periodique){ 
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo '<b>'.recherche_libelle_page('curr_period').'</b>'.' : '.create_combo($_SESSION['tab_filtres'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $_SESSION['filtre'], 'toggle_filtre(this.value)', $disabled); 
			}
		?>
		<?php if($new_teacher){ ?>
		<p><?php print '<b>' . $_SESSION['hierarchie_regroup'] . '</b>'; ?></p>
		<p><?php print $_SESSION['infos_etab']; ?></p>
		<?php }else{ ?>
		<a href="questionnaire.php?theme=<?php echo $_GET['theme']; if(isset($_SESSION['type_ent_stat'])) echo "&type_ent_stat=".$_SESSION['type_ent_stat'] ; if(isset($_GET['id_teacher'])) echo "&id_teacher=".$_GET['id_teacher'] ; if(isset($_GET['ligne'])) echo "&ligne=".$_GET['ligne'] ; ?>" style=" text-decoration:none ; " ><p><?php print '<b>' . $_SESSION['hierarchie_regroup'] . '</b>'; ?></p></a>
		<a href="questionnaire.php?theme=<?php echo $_GET['theme']; if(isset($_SESSION['type_ent_stat'])) echo "&type_ent_stat=".$_SESSION['type_ent_stat'] ; if(isset($_GET['id_teacher'])) echo "&id_teacher=".$_GET['id_teacher'] ; if(isset($_GET['ligne'])) echo "&ligne=".$_GET['ligne'] ; ?>" style=" text-decoration:none ; " ><p><?php print $_SESSION['infos_etab'].$_SESSION['infos_data_entry']; ?></p></a>
		<?php } ?>
		</div>
		<?php 
		if ((!$GLOBALS['PARAM']['HIDE_DELETE_SCHOOL'] or ( $GLOBALS['PARAM']['HIDE_DELETE_SCHOOL'] && ($_SESSION['groupe'] ==1 || $_SESSION['groupe'] ==2))) && (!isset($_SESSION['tmis'])) && (!isset($_GET['ligne'])) ){
			if( isset($_SESSION['code_etab']) and (trim($_SESSION['infos_etab']) <> '')  ){ ?>
				<div style="position:absolute; top: 68px; right: 80px; z-index: 2;"> 
					<a href="#" onClick="AlertSupEtab(<?php echo $_SESSION['code_etab'];?>)" >  <?php echo recherche_libelle_page('SupEtab');?> </a>
				</div>
			<?php } 
		}elseif(((isset($_SESSION['tmis'])) || (isset($_GET['ligne']))) && (!$new_teacher)){
			if( (isset($_SESSION['code_etab']) and (trim($_SESSION['infos_etab']) <> '')) && (isset($_SESSION['id_teacher']) and (trim($_SESSION['id_teacher']) <> ''))  ){ ?>
				<div style="position:absolute; top: 68px; right: 80px; z-index: 2;"> 
					<a href="#" onClick="AlertImput('sup_teach',<?php echo $_SESSION['annee'];?>)" >  <?php echo recherche_libelle_page('SupTeacher');?> </a>
				</div>
			<?php } 
		}?>
	<!-- Fin rappel des infos etablissement -->
	<?php }else{ ?>
		<div id="rappel">
		<p> <?php echo recherche_libelle_page('curr_year');?> : <b><?php echo get_libelle_from_array($_SESSION['tab_annees'], $_SESSION['annee'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']);?></b> 
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php if($GLOBALS['PARAM']['FILTRE'] && $theme_periodique) echo recherche_libelle_page('curr_period').' : '.create_combo($_SESSION['tab_filtres_2'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $_SESSION['filtre'], 'toggle_filtre(this.value)', $disabled); ?>
		<br>
		<?php echo recherche_libelle_page('curr_sect');?> : 
		<b><?php echo get_libelle_from_array($_SESSION['tab_secteur'], $_SESSION['secteur'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE');?></b>
		</p>
		</div>
	<?php } ?>

<?php 
}elseif(isset($_GET['tmis'])){
	//Affichage hierarchie TMIS	
	ini_set("memory_limit", "256M");
	$_SESSION['tmis'] = $_GET['tmis'];
	if(!isset($_SESSION['code_etab'])) $_SESSION['code_etab'] = 0;
	$tab_chaines_loc = array();
	$all_chaines_loc = array();
	$requete        = 'SELECT CODE_TYPE_CHAINE FROM DICO_CHAINE_LOCALISATION
						 WHERE CODE_TYPE_SYSTEME_ENSEIGNEMENT='.$_SESSION['secteur'].' 
						 ORDER BY ORDRE_TYPE_CHAINE';
	$all_chaines_loc  = $GLOBALS['conn_dico']->GetAll($requete); 

	if( count($all_chaines_loc) == 0 ){
			$tab_chaines_loc[] = array( 'numCh' => 2, 'nbRegs' => 4 );
	}else{
		foreach( $all_chaines_loc as $i => $tab ){
			$req_niv  	=  'SELECT    COUNT(*) 
							FROM      '.$GLOBALS['PARAM']['HIERARCHIE'].'
							WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$tab['CODE_TYPE_CHAINE'];
			$niv_chaine = 	$GLOBALS['conn']->getOne($req_niv); 
			$tab_chaines_loc[] = array( 'numCh' => $tab['CODE_TYPE_CHAINE'], 'nbRegs' => $niv_chaine );								 
		}
	}
	$tab_chaines = $tab_chaines_loc;
	$chaine_regs = array();
	if((isset($_GET['id_chaine_tmis']) and trim($_GET['id_chaine_tmis'])<>'')){
		$chaine_regs = explode(',',$_GET['id_chaine_tmis']);
		$_SESSION['numCh'] = $chaine_regs[0];
		$_SESSION['nbRegs'] = $chaine_regs[1];
		unset($_SESSION['reg_parents_tmis']);
		unset($_SESSION['tab_get_sort']);
		unset($_SESSION['tab_get_filter']);
		unset($_SESSION['list_chps_export']);
	}elseif(((!isset($_GET['id_chaine_tmis']) || trim($_GET['id_chaine_tmis'])=='') && (isset($_GET['secteur']) and trim($_GET['secteur'])<>'')) || (!isset($_SESSION['numCh']))){
		$_SESSION['numCh'] = $tab_chaines[0]['numCh'];
		$_SESSION['nbRegs'] = $tab_chaines[0]['nbRegs'];
		unset($_SESSION['reg_parents_tmis']);
		unset($_SESSION['tab_get_sort']);
		unset($_SESSION['tab_get_filter']);
		unset($_SESSION['list_chps_export']);
	}
	?>
	<div id="rappel">
		<p><?php echo recherche_libelle_page('curr_sect');?> : <b><?php echo get_libelle_from_array($_SESSION['tab_secteur'], $_SESSION['secteur'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE');?></b>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php echo '<b>'.recherche_libelle_page('sel_year').'</b>'.' : '.create_combo($_SESSION['tab_annees'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $_SESSION['annee'], 'toggle_annee(this.value)', $disabled);	?>
		</p>
	</div>
	<div align="left" id="rappel">
	<select name="id_chaine_tmis" id="id_chaine_tmis" onChange="toggle_combo_chaine(this.value);">
	<?php
	if(is_array($tab_chaines))
	foreach ($tab_chaines as $iLoc => $ch){
			echo "<option value='".$ch['numCh'].",".$ch['nbRegs']."'";
			if ($ch['numCh'] == $_SESSION['numCh']){
					echo " selected";
			}
			echo ">".get_libelle_from_array($_SESSION['tab_chaines'], $ch['numCh'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'])."</option>\n";
	}
	?>
	</select>
	<form name="combo_regroups" id="combo_regroups">
	<table><tr>
	<?php
		$_SESSION['iLoc'] = 0 ;
		$arbre = new arbre($_SESSION['numCh']);
		$arbre->init($_SESSION['nbRegs']);
		$GLOBALS['popup'] = 1 ;
		$arbre_html = $arbre->build('javascript:action_arbo(\'$code_regroup\', \'$nom_regroup\');');
		
		?>
		<td>
		<table align="center" width="350px">
		<tr>
		<td nowrap="nowrap" colspan="2">
		<B><?php echo get_libelle_from_array($_SESSION['tab_chaines'], $_SESSION['numCh'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']);?></B>
		</td>
		</tr>
			<?php echo $arbre_html; ?>
		</table>
		</td>
	</tr></table>
	</form>
	</div>
<?php
}//Fin Affichage hierarchie TMIS

//Ajout HEBIE pour l'affichage d'un lien de retour a la grille des enseignants
//echo $_GET['theme'];
if(isset($GLOBALS['PARAM']['TEACHERS_MANAGEMENT']) && $GLOBALS['PARAM']['TEACHERS_MANAGEMENT'] && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])){
	if(isset($_GET['theme_pere'])) $_SESSION['theme_pere'] = $_GET['theme_pere'];
	/*$requete        = "SELECT LIBELLE
						FROM DICO_TRADUCTION 
						WHERE CODE_NOMENCLATURE=".$_SESSION['theme_pere']." AND CODE_LANGUE='".$_SESSION['langue']."'
						AND NOM_TABLE='DICO_THEME_LIB_LONG';";
	$result		= $GLOBALS['conn_dico']->GetAll($requete); 
	$libelle_long   = $result[0]['LIBELLE'];*/									
	$libelle_long   = recherche_libelle_page('lib_long_gril');	
	$suite_get = '';
	if(isset($_GET['ligne']) && $_GET['ligne'] < $_SESSION['nb_line'] && !isset($_SESSION['debut'])){
		$_SESSION['ligne'] = $_GET['ligne'];
	}elseif(isset($_GET['ligne']) && $_GET['ligne'] < $_SESSION['nb_line'] && isset($_SESSION['debut'])){
		$_SESSION['ligne'] = $_GET['ligne'];
		$suite_get = '&suite=true&debut='.$_SESSION['debut'];
	}elseif(isset($_GET['ligne']) && $_GET['ligne'] >= $_SESSION['nb_line']){
		$_SESSION['ligne'] = (int)($_GET['ligne']-(((int)($_GET['ligne']/$_SESSION['nb_line']))*$_SESSION['nb_line']));
		$suite_get = '&suite=true&debut='.((int)($_GET['ligne']/$_SESSION['nb_line']))*$_SESSION['nb_line'];
	}
	echo "<div>";
	if(!isset($_SESSION['liste_etab'])){
		echo "<input style='text-align:center; color:#0000FF; font:bold; font-size:12px; width:200px' type='button' name='btn_ret_liste' id='btn_ret_liste' value=\"".$libelle_long."\" onclick=\"javascript:document.location.href='questionnaire.php?theme=".$_SESSION['theme_pere'].$suite_get."&ret_list=1&line=".$_SESSION['ligne']."&nb_line=".$_SESSION['nb_line']."'\"/>\n";
	}else{
		echo "<input style='text-align:center; color:#0000FF; font:bold; font-size:12px; width:200px' type='button' name='btn_ret_liste' id='btn_ret_liste' value=\"".$libelle_long."\" onclick=\"javascript:document.location.href='questionnaire.php?theme=".$_SESSION['theme_pere']."&tmis=".$_SESSION['tmis'].$suite_get."&ret_list=1&line=".$_SESSION['ligne']."&nb_line=".$_SESSION['nb_line']."'\"/>\n";
	}
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<input style='text-align:center; color:#0000FF; font:bold; font-size:12px; width:150px' type='button' name='btn_add_new_teach' id='btn_add_new_teach' value='".recherche_libelle_page('add_new_teach')."' onclick=\"javascript:document.location.href='questionnaire.php?action=add_new_teach&theme=".$_GET['theme']."'\"/>\n";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<input style='text-align:center; color:#0000FF; font:bold; font-size:12px; width:200px' type='button' name='btn_export_teach_rec' id='btn_export_teach_rec' value=\"".recherche_libelle_page('export_teach_rec')."\" onclick=\"javascript:export_teacher_records()\"/>\n";
	echo "</div>\n";
}
if(isset($GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])) echo "<br/><br/>";
//Fin Ajout HEBIE pour l'affichage d'un lien de retour a la grille des enseignants

//Bouton d'importation de donnees a partir de fichier excel
$theme_prec = $theme_manager->recherche_theme_prec();
if(!$theme_prec && $GLOBALS['PARAM']['BTN_IMPORT_EXCEL']){
	$button_import	=  '<BR/>
						<DIV align=center>
						<TABLE border="0">
							<TR>
								<TD width="100%" align="center"><input style="width:98%; text-align:center;" type="button" name="btn_import_excel" id="btn_import_excel" value="&nbsp;&nbsp;'.recherche_libelle_page('import_excel').'&nbsp;&nbsp;" onclick="javascript:ouvrir_pop_imp_excel();"/></TD>
							</TR>
						</TABLE>
						</DIV>';
	echo $button_import;
}
//Bouton recherche enseignant: Modif HEBIE
if(isset($GLOBALS['PARAM']['BTN_TRANSFERT_ENSEIGNANT']) && $GLOBALS['PARAM']['BTN_TRANSFERT_ENSEIGNANT'] && in_array($_GET['theme'],$GLOBALS['PARAM']['TRANSFERT_ENSEIGNANT_THEMES'])){
	echo '<br/><div style="text-align:left"><input style="text-align:center; font:bold;" type="button" name="" id="" value=" ... '.recherche_libelle_page('RechEns').' ... " onClick="OpenPopupRechEns(\'ENS\','.$_GET['theme'].');"/></div>'."\n";
}elseif(isset($GLOBALS['PARAM']['BTN_TRANSFERT_PERS_ADMIN']) && $GLOBALS['PARAM']['BTN_TRANSFERT_PERS_ADMIN'] && in_array($_GET['theme'],$GLOBALS['PARAM']['TRANSFERT_PERS_ADMIN_THEMES'])){
	echo '<br/><div style="text-align:left"><input style="text-align:center; font:bold;" type="button" name="" id="" value=" ... '.recherche_libelle_page('RechEns').' ... " onClick="OpenPopupRechEns(\'NON_ENS\','.$_GET['theme'].');"/></div>'."\n";
}elseif(isset($GLOBALS['PARAM']['BTN_TRANSFERT_PERS_SERVICE']) && $GLOBALS['PARAM']['BTN_TRANSFERT_PERS_SERVICE'] && in_array($_GET['theme'],$GLOBALS['PARAM']['TRANSFERT_PERS_SERVICE_THEMES'])){
	echo '<br/><div style="text-align:left"><input style="text-align:center; font:bold;" type="button" name="" id="" value=" ... '.recherche_libelle_page('RechEns').' ... " onClick="OpenPopupRechEns(\'SERV\','.$_GET['theme'].');"/></div>'."\n";
}
//Fin bouton recherche enseignant			
//Drapeau theme verrouill�
if(isset($_SESSION['list_themes_desact']) && in_array($_GET['theme'],$_SESSION['list_themes_desact'])){
	echo '<br/><div align="center" style="text-align:center; font:bold;"><span class="StyleVerrou" >'.recherche_libelle_page('infos_verrou').'</span></div>'."\n";
}
//echo "<pre>";
//print_r($_SESSION['imput_all_tabms']);
//Fin drapeau theme verrouill�
//$theme_manager->set_theme_courant();
//$theme_manager->set_classe();
$requete  = "SELECT ACTION_THEME 
			 FROM DICO_THEME 
			 WHERE ID =".$theme_manager->id.";";
	
//echo $requete."<pre>";
//print_r($_SESSION['curobj_instance']);
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
	echo "ISOKSAVEINDATABASE";
	/*if( isset($_SESSION['imput_all_tabms'][$_SESSION['secteur']]) && count($_SESSION['imput_all_tabms'][$_SESSION['secteur']])  
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
	}*/
}
//Fin gestion user link to data entry

//Ajout HEBIE pr recuperer la requete associ�e a la liste des enseignants
if(isset($GLOBALS['PARAM']['TEACHERS_MANAGEMENT']) && $GLOBALS['PARAM']['TEACHERS_MANAGEMENT'] && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])){
	$_SESSION['teacher_sql_data'] = $curobj_grille->sql_data;
	$_SESSION['nb_line'] = $curobj_grille->nb_lignes;
	if(isset($_GET['suite'])) $_SESSION['suite'] = $_GET['suite'];
	elseif(isset($_SESSION['suite'])) unset($_SESSION['suite']);
	if(isset($_GET['debut']) && $curobj_grille->nb_lignes > 0) $_SESSION['debut'] = $_GET['debut'];
	elseif(isset($_SESSION['debut'])) unset($_SESSION['debut']);
}
//Fin Ajout HEBIE

?>
<!-- Questionnaire suivant --> 
<?php if( $GLOBALS['pb_new_etab'] == true ){
    if(isset($_SESSION['code_etab'])){
        sup_etab($_SESSION['code_etab'],false);
        unset($_SESSION['code_etab']);
    }
    print '<script type="text/Javascript">';
	//if(isset($_SESSION['type_ent_stat']))
    //	print "    document.location.href='saisie_donnees.php?val=new_etab&type_ent_stat=".$_SESSION['type_ent_stat']."';\n";
    //else
	//	print "    document.location.href='saisie_donnees.php?val=new_etab';\n";
	print '</script>';
}elseif(!isset($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) || (isset($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && !in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']))){?>
		<div id="suite">
		<?php $theme_precedant					= $theme_manager->recherche_theme_prec();
				$theme_suivant 						= $theme_manager->recherche_theme_suiv();
				//Pour eviter de continuer sur le theme TMIS ne faisant pas partie des themes de saisie par defaut
				if(in_array($theme_suivant,$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])) $theme_suivant = false;
				//echo '<br>New_Etab='.$New_Etab;
				if(isset($New_Etab)){
						$plus_Get ='&code_etab='.$New_Etab;
						//Afin de reg�n�rer les infos decriptives de l'�tab cr��
						new_infos_etab();
						print '<script type="text/Javascript">';
						if(isset($_SESSION['type_ent_stat']) && (!isset($_POST['save_and_next']) || ($_POST['save_and_next']<>1)) && (!isset($_POST['save_and_prev']) || ($_POST['save_and_prev']<>1))){
							print "    document.location.href='questionnaire.php?theme=".${'theme_manager'.$_SESSION['type_ent_stat']}->id.$_SESSION['secteur']."&code_etab=".$_SESSION['code_etab']."&type_ent_stat=".$_SESSION['type_ent_stat']."';";
						}elseif(!isset($_SESSION['type_ent_stat']) && (!isset($_POST['save_and_next']) || ($_POST['save_and_next']<>1)) && (!isset($_POST['save_and_prev']) || ($_POST['save_and_prev']<>1))){
							//print "    document.location.href='questionnaire.php?theme=".$theme_manager->id.$_SESSION['secteur']."&code_etab=".$_SESSION['code_etab']."';";
						}
						print '</script>';
				}
				if(!isset($_SESSION['liste_etab'])){
					if($theme_precedant) {
						if(!$GLOBALS['PARAM']['HIDE_THEME_NAVIG']){
							$lib_long_theme_precedant = $theme_manager->get_lib_long_theme($theme_precedant,$_SESSION['langue']);
							if(isset($_SESSION['type_ent_stat']))
								echo '<a href="?theme=' . $theme_precedant.'&code_etab='.$_SESSION['code_etab'].'&type_ent_stat='.$_SESSION['type_ent_stat'].'" class="precedent" title="'.$lib_long_theme_precedant.'">' . recherche_libelle_page('ThemPrec') . '</a>' . "\n";
							else
								echo '<a href="?theme=' . $theme_precedant.'" class="precedent" title="'.$lib_long_theme_precedant.'">' . recherche_libelle_page('ThemPrec') . '</a>' . "\n";
						}
							
						if( (isset($_POST['save_and_prev']) && ($_POST['save_and_prev']==1)) &&($GLOBALS['theme_data_MAJ_ok']	== true) ){
							if(isset($_SESSION['type_ent_stat']))
								load_theme($theme_precedant,$_SESSION['code_etab'],$_SESSION['type_ent_stat']);
							else
								load_theme($theme_precedant);
						}
				}else{
					hide_ID('btn_save_and_prev');
				}
				if($theme_suivant) {
					if(!$GLOBALS['PARAM']['HIDE_THEME_NAVIG']){
						$lib_long_theme_suivant 	= $theme_manager->get_lib_long_theme($theme_suivant,$_SESSION['langue']);
						if(isset($New_Etab)){
							if(isset($_SESSION['type_ent_stat']))
								echo '<a href="?theme=' . $theme_suivant . $plus_Get .'&type_ent_stat='.$_SESSION['type_ent_stat'].'" class="suivant" title="'.$lib_long_theme_suivant.'">' . recherche_libelle_page('ThemSuiv') . '</a>' . "\n";
							else
								echo '<a href="?theme=' . $theme_suivant . $plus_Get .'" class="suivant" title="'.$lib_long_theme_suivant.'">' . recherche_libelle_page('ThemSuiv') . '</a>' . "\n";
						}else{
							if(isset($_SESSION['type_ent_stat']))
								echo '<a href="?theme=' . $theme_suivant.'&code_etab='.$_SESSION['code_etab'].'&type_ent_stat='.$_SESSION['type_ent_stat'].'" class="suivant" title="'.$lib_long_theme_suivant.'">' . recherche_libelle_page('ThemSuiv') . '</a>' . "\n";
							else
								echo '<a href="?theme=' . $theme_suivant.'" class="suivant" title="'.$lib_long_theme_suivant.'">' . recherche_libelle_page('ThemSuiv') . '</a>' . "\n";
						}
					}					
					if( (isset($_POST['save_and_next']) && ($_POST['save_and_next']==1)) &&($GLOBALS['theme_data_MAJ_ok']	== true) ){
						if(isset($_SESSION['type_ent_stat']))
							load_theme($theme_suivant,$_SESSION['code_etab'],$_SESSION['type_ent_stat']);
						else
							load_theme($theme_suivant);
					}
				}else{ // fin des th�mes
					hide_ID('btn_save_and_next');
					//if( $GLOBALS['theme_data_MAJ_ok']	== true ){
					if( isset($_SESSION['next_etab'][$_SESSION['code_etab']]) ){ // si 
						echo '<br><br> '.recherche_libelle_page('next_etab').' : <a href="questionnaire.php?'.$_SESSION['next_etab'][$_SESSION['code_etab']]['code_etab'].'" target="_top">'.$_SESSION['next_etab'][$_SESSION['code_etab']]['nom_etab'].'</a>'.$_SESSION['next_etab'][$_SESSION['code_etab']]['code_admin'] ;
					}
					//}
				}
			}else{
				hide_ID('btn_save_and_prev');
				hide_ID('btn_save_and_next');
			}
			print '<script type="text/Javascript">'."\n";
			print '    Set_Element(\'save_and_prev\', \'form1\', 0);'."\n";
			print '    Set_Element(\'save_and_next\', \'form1\', 0);'."\n";
			print '</script>'."\n";
			
			//Desactivation boutons save li�s � une reponse sur un theme
			if(isset($_SESSION['list_themes_desact']) && in_array($_GET['theme'],$_SESSION['list_themes_desact'])){
				deactivate_ID('btn_save_and_prev');
				deactivate_ID('btn_save_only');
				deactivate_ID('btn_save_and_next');
			}
			//Fin Desactivation boutons save li�s � une reponse sur un theme
		?>
		</div>
	<?php }elseif(isset($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])){?>

		<?php 	
				//Ajout HEBIE pr la gestion de la precedance des enseignants
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/teacher_manager.class.php';
				$teacher_manager = new teacher_manager(); 
				$teacher_manager->teacher_sql_data = $_SESSION['teacher_sql_data'];
				$teacher_manager->init();
				
				$teacher_precedant					= $teacher_manager->recherche_teacher_prec();
				$teacher_suivant 					= $teacher_manager->recherche_teacher_suiv();

				//echo '<br>New_Teach='.$New_Teach;
				if(isset($New_Teach)){
						$plus_Get ='&id_teacher='.$New_Teach;
						//Afin de reg�n�rer les infos decriptives de l'�tab cr��
						//new_infos_ens();
				}
				if($teacher_precedant) {
					if(!$GLOBALS['PARAM']['HIDE_TEACHER_NAVIG']){
						$lib_long_teacher_precedant = $teacher_manager->get_lib_long_teacher($teacher_precedant);
						echo '<a href="?theme=' .$_GET['theme'].'&id_teacher='.$teacher_precedant.'&ligne='.($teacher_manager->pos_teach-1).'" class="precedent" title="'.$lib_long_teacher_precedant.'"><b>' . recherche_libelle_page('TeachPrec') . '</b></a>' . "\n";
					}
				}
		
				if($teacher_suivant) {
					if(!$GLOBALS['PARAM']['HIDE_TEACHER_NAVIG']){
						$lib_long_teacher_suivant 	= $teacher_manager->get_lib_long_teacher($teacher_suivant);
						if(isset($New_Teach)){
							echo '<a href="?theme=' .$_GET['theme'].$plus_Get.'&id_teacher='.$teacher_suivant.'" class="suivant" title="'.$lib_long_teacher_suivant.'"><b>' . recherche_libelle_page('TeachSuiv') . '</b></a>' . "\n";
						}else{
							echo '<a href="?theme=' .$_GET['theme'].'&id_teacher='.$teacher_suivant.'&ligne='.($teacher_manager->pos_teach+1).'" class="suivant" title="'.$lib_long_teacher_suivant.'"><b>' . recherche_libelle_page('TeachSuiv') . '</b></a>' . "\n";
						}
					}					
				}
		?>
</div>
	<?php } 
	if($_GET['val'] == 'new_etab'){
		hide_ID('btn_save_and_prev');
	}
	
	// Gestion Affichage Donn�es Estim�es
	if( isset($_SESSION['imput_all_tabms'][$_SESSION['secteur']]) && count($_SESSION['imput_all_tabms'][$_SESSION['secteur']])  ) {
		$curr_tabms = $_SESSION['curobj_instance']->nomtableliee;
		if(!is_array($curr_tabms)){ $tmp = $curr_tabms ; $curr_tabms=array(); $curr_tabms[] = $tmp; }
		$_SESSION['imput_cur_tabms'] = $curr_tabms ;
		$curr_thm_imput = false ;
		$curr_thm_data = false ;
		$exist_chp_imput = false;
		$curr_thm_data_ann_prec = false;
		
		$liste_annee = $list_ann;
		krsort($liste_annee);
		$annee_prec = 0;
		foreach ($liste_annee as $i_ann => $ann){
			if($ann[$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] < $ord_curr_ann){
				$annee_prec = $ann[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']];
				break;
			}
		}
		
		if(is_array( $curr_tabms )){
			foreach($curr_tabms as $i => $tabm){
				if(is_array($GLOBALS['conn']->MetaColumns($tabm))){                           
					foreach($GLOBALS['conn']->MetaColumns($tabm) as $champ){
						if($champ->name == $GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES']){
							$exist_chp_imput = true;
							break;
						}
					}
				}
				if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
					$reqtmpdata = 'SELECT * FROM '.$tabm.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_SESSION['annee'].
							  ' AND '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' IN ( '.implode(', ',$_SESSION['liste_etab']).' )';
					if($exist_chp_imput && !isset($_GET['tmis']))
						$reqtmpdata .=  ' AND '.$GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES'].' > 0 ';
					
					//Annee precedente
					$reqtmpdata_ann_prec = 'SELECT * FROM '.$tabm.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$annee_prec.
							  ' AND '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' IN ( '.implode(', ',$_SESSION['liste_etab']).' )';
					if($exist_chp_imput && !isset($_GET['tmis']))
						$reqtmpdata_ann_prec .=  ' AND '.$GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES'].' > 0 ';
					
				
				}else{
					$reqtmpdata = 'SELECT * FROM '.$tabm.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_SESSION['annee'].
							  ' AND '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$_SESSION['code_etab'];
					if($exist_chp_imput && !isset($_GET['tmis']))
						$reqtmpdata .=  ' AND '.$GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES'].' > 0 ';
						
					//Annee precedente
					$reqtmpdata_ann_prec = 'SELECT * FROM '.$tabm.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$annee_prec.
							  ' AND '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$_SESSION['code_etab'];
					if($exist_chp_imput && !isset($_GET['tmis']))
						$reqtmpdata_ann_prec .=  ' AND '.$GLOBALS['PARAM']['CHAMP_ESTIMATION_DONNEES'].' > 0 ';
						
				}
				
				if( ($GLOBALS['conn']->Execute($reqtmpdata)) === false ){}else{
					$existtmpdata	= $GLOBALS['conn']->GetAll($reqtmpdata); 
					//echo $reqtmpdata ;
					if( is_array($existtmpdata) && count($existtmpdata) > 0){
						
						print '<script type="text/Javascript">';
						//print '    alert(\'Exist data\');';
						print '</script>';
						if($exist_chp_imput){
							if((isset($_GET['tmis']) && $_GET['tmis']=='teach_reconduct') || (!isset($_GET['tmis']) && !isset($_GET['ligne']) && $GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_AND_INFO'])){
							?>
								<div id="info_imput"><span id="info_imput_span" class="StyleImputData" style="width:135px"><?php echo recherche_libelle_page('tamponimp');?></span></div>
							<?php
							}
							$curr_thm_imput = true ;
						}
						$curr_thm_data = true;
						break;
					}else{
						$existtmpdata_ann_prec	= $GLOBALS['conn']->GetAll($reqtmpdata_ann_prec); 
						if( is_array($existtmpdata_ann_prec) && count($existtmpdata_ann_prec) > 0){
							$curr_thm_data_ann_prec = true;
						}
					}
				}
			}
		}
		if(!$curr_thm_data && $curr_thm_data_ann_prec && (isset($_GET['tmis']) && ($_GET['tmis']=='teach_data' || $_GET['tmis']=='teach_reconduct'))){
		?>
			<script type="text/Javascript" language="javascript">
				var msg_alert = "<?php echo recherche_libelle_page('no_data_curr_thm');?> ";
				if(confirm(msg_alert)){
					show_imput_form = true;
				}
			</script>
		<?php
		}
		
		//Drapeau donn�es tri�es
		if((isset($_GET['tmis']) && $_GET['tmis']=='teach_data') || (!isset($_GET['tmis']))){
			if(in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) || in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])){
				if(isset($_SESSION['tab_get_sort']) && count($_SESSION['tab_get_sort'])){
					echo '<div id="info_sort"><span class="StyleSortFiltData" style="width:100px">'.recherche_libelle_page('tamponsort').'</span></div>'."\n";
				}
				//Drapeau donn�es filtr�es
				if(isset($_SESSION['tab_get_filter']) && count($_SESSION['tab_get_filter'])){
					echo '<div id="info_filter"><span class="StyleSortFiltData" style="width:100px">'.recherche_libelle_page('tamponfilter').'</span></div>'."\n";
				}
			}
		}
		if( $_SESSION['groupe'] ==1 || $_SESSION['groupe'] ==2 ){
			?>
			<?php
			if((isset($_GET['tmis']) && $_GET['tmis']=='teach_reconduct') || (!isset($_GET['tmis']) && !isset($_GET['ligne']) && $GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_AND_INFO'] && (isset($GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_THEMES']) || (isset($GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_THEMES']) && count($GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_THEMES'])==0) || !isset($GLOBALS['PARAM']['SHOW_ESTIM_BUTTON_THEMES'])))){
			?>
			<div id="show_form_imput"><input style='text-align:center; color:#0000FF;  width:155px; height:20px; font:bold; font-size:12px' type='button' name='btn_imput_show' id='btn_imput_show' value="<?php echo recherche_libelle_page('dataestim');?>&nbsp;&nbsp;>>" onClick="MM_showHideLayers('show_form_imput','','hide','hide_form_imput','','show','form_imput','','show','imput_running','','hide')"/></div> 
			<div id="hide_form_imput"><input style='text-align:center; color:#0000FF;  width:155px; height:20px; font:bold; font-size:12px' type='button' name='btn_imput_hide' id='btn_imput_hide' value="<?php echo recherche_libelle_page('dataestim');?>&nbsp;&nbsp;<<" onClick="MM_showHideLayers('show_form_imput','','show','hide_form_imput','','hide','form_imput','','hide','imput_running','','hide')"/></div>
			<?php
			}
			?>
			<?php $caption = '<strong>'.recherche_libelle_page('actionestim').'';
			   if(isset($_GET['tmis'])){
			   		$cible_imput = 'tmis_thm';
					$caption .= ' '.recherche_libelle_page('tmisschool').' ';
			   }
			   elseif($theme_manager->pos_thm == 0){
					$cible_imput = 'all_thm';
					$caption .= ' '.recherche_libelle_page('currschool').' ';
				}else{
					$cible_imput = 'cur_thm';
					$caption .= ' '.recherche_libelle_page('currtheme').''; 
				}
				$caption .= '</strong>';
		 	?>
				<div id="form_imput">
				<form  name="imput_formulaire" id="imput_formulaire">
				  <table id="tabl_imput"  width="100%">
					  <caption><?php echo $caption; ?></caption>
					 <tr>
						  <td nowrap="nowrap">
							<input type="radio" name="action_imput" value="imputer" id="id_imputer">
							<?php echo recherche_libelle_page('estimate');?>&nbsp;</td>
						  <td nowrap="nowrap">&nbsp;<?php echo recherche_libelle_page('yeardata');?>
						  <?php //$requete = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
							//$list_ann = $GLOBALS['conn']->GetAll($requete); 
							//echo'<pre> list ann�e :<br>';
							//print_r($list_ann);
							//die($ord_curr_ann);
						   ?>
							<select name="annee_imput" id="annee_imput">
								<?php 
								krsort($list_ann);
								foreach ($list_ann as $i_ann => $ann){
									if($ann[$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] < $ord_curr_ann){
										echo "<option value='".$ann[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]."'";
										echo ">".$ann[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]."</option>";
									}
								}
						?>
							</select></td>
					</tr>
						
						<tr>
						  <td colspan="2" nowrap="nowrap">
							<input type="radio" name="action_imput" value="lever_impute"  id="id_lever_impute">
							<?php echo recherche_libelle_page('cancallest');?>           </td>
						</tr>
						<tr>
						  <td colspan="2" nowrap="nowrap">
							
							  <div align="right">
								<input type="hidden" name="cible_imput" id="cible_imput" value="<?php echo $cible_imput; ?>">
								<input name="ok" id="ok" type="button" onClick="MM_callJS('AlertImput(document.imput_formulaire.cible_imput.value, document.imput_formulaire.annee_imput.value)')" value="<?php echo recherche_libelle_page('submit');?>">
								&nbsp;&nbsp;
								  <input name="annuler" id="annuler" type="button" onClick="MM_showHideLayers('show_form_imput','','show','hide_form_imput','','hide','form_imput','','hide')" value="<?php echo recherche_libelle_page('closeest');?>">
							  </div>
					 	  </td>
						 </tr>
					</table>
				  </form>
			  </div>
			  
				<div id="imput_running">
					
					<table width="100%">
					  <caption><?php echo $caption; ?></caption>
					 <tr><td height="28" style="margin:0px">
						<div id="imput_progress"  align="center">
							<script language="JavaScript" type="text/javascript">
								var niv = 1;
								function barre(){
									if( (document.getElementById("pourcentage")) && (document.getElementById("progrbar")) ){
										if(niv > 400) niv = 1;
										var ch_eval = 'document.getElementById("progrbar").style.width="'+(niv / 4)+'%";';
										eval(ch_eval);
										niv++;
										setTimeout("barre()", 1);
									}
								}
							</script>
						
							<div id="pourcentage" class="aa" align="center"><?php echo recherche_libelle_page('isruuning');?></div>
							<div id="progrbar" class="bb" align="center"></div>
						
							<script language="JavaScript" type="text/javascript">
								setTimeout("barre()", 1);	
							</script> 
						</div>
					 &nbsp;</td></tr>
				  </table>
				</div>
		 <?php }
	}
	//
	//Bouton TMIS
	?>
			<!-- Espace sort -->
			<?php 
			if(isset($GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])){
				if((isset($_GET['tmis']) && $_GET['tmis']=='teach_data') || (!isset($_GET['tmis']))){
					$position = array_search($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']);
					if (array_key_exists($position, $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'][$position]<>''){
						$thm_fils = $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'][$position];
						?>
						<div id="add_new_teach"><input style='text-align:center; color:#0000FF; font:bold; font-size:12px; width:150px' type='button' name='btn_add_new_teach' id='btn_add_new_teach' value="<?php echo recherche_libelle_page('add_new_teach');?>" onClick="javascript:document.location.href='questionnaire.php?action=add_new_teach<?php echo '&theme_pere='.$_GET['theme']; echo '&theme='.$thm_fils;?>'"/></div>
					<?php
					}
					?>
					<div id="show_form_sort"><input style='text-align:center; color:#0000FF; width:100px; font:bold; font-size:12px' type='button' name='btn_sort_show' id='btn_sort_show' value="<?php echo recherche_libelle_page('datasort');?>&nbsp;&nbsp;>>" onClick="MM_showHideLayers('show_form_sort','','hide','hide_form_sort','','show','form_sort','','show','sort_action','','show','show_form_filter','','show','hide_form_filter','','hide','form_filter','','hide','filter_action','','hide','show_form_export','','show','hide_form_export','','hide','form_export','','hide','export_action','','hide')"/></div> 
					<div id="hide_form_sort"><input style='text-align:center; color:#0000FF;  width:100px; font:bold; font-size:12px' type='button' name='btn_sort_hide' id='btn_sort_hide' value="<?php echo recherche_libelle_page('datasort');?>&nbsp;&nbsp;<<" onClick="MM_showHideLayers('show_form_sort','','show','hide_form_sort','','hide','form_sort','','hide','sort_action','','hide')"/></div>
			<?php 
				}
			}
			?>
			<div id="form_sort">
				<form  name="sort_formulaire" id="sort_formulaire">
				  <table id="tabl_sort"  width="100%" border="1px">
					  <caption><?php echo '<strong>'.recherche_libelle_page('actionsort').'</strong>'; ?></caption>
					<?php
					$nb_col = count($list_col);
					for($cpt=0; $cpt<$nb_col; $cpt++){
					?>	 
					 <tr>
						 <td nowrap="nowrap" style="vertical-align:middle"><?php if($cpt==0) echo recherche_libelle_page('sortby'); else echo recherche_libelle_page('thenby');?><br/>
						  	<select name="<?php echo 'chp_sort_'.$cpt ;?>" id="<?php echo 'chp_sort_'.$cpt ;?>">
								<option value=''>---</option>
								<?php
								foreach ($list_col as $i_col => $col){
										echo "<option value='".$col['TYPE_ZONE_BASE']."|".$col['TABLE_MERE']."-".$col['CHAMP_PERE']."'";
										if($col['LIBELLE']<>'') echo ">".$col['LIBELLE']."</option>";
										else echo ">".$col['CHAMP_PERE']."</option>";
								}
								?>
							</select>
						</td>
						<td nowrap="nowrap">
							<input type="radio" name="<?php echo 'ord_sort_'.$cpt; ?>" value="ASC" id="<?php echo 'ord_asc_'.$cpt; ?>" checked="checked">
							<?php echo recherche_libelle_page('sortasc');?><br/>
							<input type="radio" name="<?php echo 'ord_sort_'.$cpt; ?>" value="DESC"  id="<?php echo 'ord_desc_'.$cpt; ?>">
							<?php echo recherche_libelle_page('sortdesc');?>
						</td>
					</tr>
					<?php
					}
					?>
				</table>
				<div id="sort_action" align="right">
					<?php if(isset($_SESSION['tab_get_sort'])){ ?>
					<input style="width:125px; color:#FF0000; font:bold" name="cancel" id="cancel" type="button" onClick="sort_grille(<?php echo count($list_col); ?>,'cancel')" value="<?php echo recherche_libelle_page('cancelsort');?>">
					&nbsp;&nbsp;
					<?php } ?>
					<input name="ok" id="ok" type="button" onClick="sort_grille(<?php echo count($list_col); ?>, 'sort')" value="<?php echo recherche_libelle_page('submit');?>">
					&nbsp;&nbsp;
					<input name="annuler" id="annuler" type="button" onClick="MM_showHideLayers('show_form_sort','','show','hide_form_sort','','hide','form_sort','','hide','sort_action','','hide')" value="<?php echo recherche_libelle_page('closeest');?>">&nbsp;&nbsp;
				</div>
			  </form>
		  	</div>
			
			<!-- Espace filter -->
			<?php 
			if(isset($GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])){
			if((isset($_GET['tmis']) && $_GET['tmis']=='teach_data') || (!isset($_GET['tmis']))){
			?>
			<div id="show_form_filter"><input style='text-align:center; color:#0000FF;  width:100px; font:bold; font-size:12px' type='button' name='btn_filter_show' id='btn_filter_show' value="<?php echo recherche_libelle_page('datafilter');?>&nbsp;&nbsp;>>" onClick="MM_showHideLayers('show_form_filter','','hide','hide_form_filter','','show','form_filter','','show','filter_action','','show','show_form_sort','','show','hide_form_sort','','hide','form_sort','','hide','sort_action','','hide','show_form_export','','show','hide_form_export','','hide','form_export','','hide','export_action','','hide')"/></div> 
			<div id="hide_form_filter"><input style='text-align:center; color:#0000FF;  width:100px; font:bold; font-size:12px' type='button' name='btn_filter_hide' id='btn_filter_hide' value="<?php echo recherche_libelle_page('datafilter');?>&nbsp;&nbsp;<<" onClick="MM_showHideLayers('show_form_filter','','show','hide_form_filter','','hide','form_filter','','hide','filter_action','','hide')"/></div>
			<?php 
			}
			} ?>	
				<div id="form_filter">
				<form  name="filter_formulaire" id="filter_formulaire">
				  <table id="tabl_filter"  width="100%" border="1px">
					  <caption><?php echo '<strong>'.recherche_libelle_page('actionfilter').'</strong>'; ?></caption>
					<?php
					//$nb_col = count($list_col);
					//for($cpt=0; $cpt<$nb_col; $cpt++){
					?>	 
					 <tr>
						 <td style="vertical-align:middle">
						 	<?php echo recherche_libelle_page('filteron'); ?>
						</td>
						 <td>
						  	<select name="chp_filter" id="chp_filter" onChange="filter_criteria()">
								<?php
								foreach ($list_col as $cpt => $col){
										echo "<option value='".$col['TYPE_ZONE_BASE']."|".$col['TABLE_MERE']."-".$col['CHAMP_PERE']."'";
										if($col['LIBELLE']<>'') echo ">".$col['LIBELLE']."</option>";
										else echo ">".$col['CHAMP_PERE']."</option>";
								}
								?>
							</select>
						</td>
						
					</tr>
				  </table>
				  <div id='tb_text'>
					<table width="100%" border="1px">
					<tr id="tr_equaltext">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('equal');?>
						</td>
						<td width="260px">
							<input type="text" name="equaltext" id="equaltext" value=""/>
						</td>
					</tr>
					<tr id="tr_contain">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('contain');?>
						</td>
						<td  width="260px">	
							<input type="text" name="contain" id="contain" value=""/>
						</td>
					</tr>
					<tr id="tr_notcontain">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('notcontain');?>
						</td>
						<td width="260px">	
							<input type="text" name="notcontain" id="notcontain" value=""/>
						</td>
					</tr>
					<tr id="tr_start">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('start');?>
						</td>
						<td width="260px">	
							<input type="text" name="start" id="start" value=""/>
						</td>
					</tr>
					<tr id="tr_end">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('endby');?>
						</td>
						<td width="260px">
							<input type="text" name="end" id="end" value=""/>
						</td>
					</tr>
					</table>
				  </div>
				  <div id='tb_int'>
					<table width="100%" border="1px">
					<tr id="tr_equalint">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('equal');?>
						</td>
						<td width="260px">
							<input type="text" name="equalint" id="equalint" value=""/>
						</td>
					</tr>
					<tr id="tr_equalint_cb" style="display:none">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('equal');?>
						</td>
						<td width="260px">
							<select name="equalint_cb" id="equalint_cb" style="width:140px">
							<option value="">---</option>
							</select>
						</td>
					</tr>
					<tr id="tr_different">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('different');?>
						</td>
						<td width="260px">	
							<input type="text" name="different" id="different" value=""/>
						</td>
					</tr>
					<tr id="tr_different_cb" style="display:none">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('different');?>
						</td>
						<td width="260px">
							<select name="different_cb" id="different_cb" style="width:140px">
							<option value="">---</option>
							</select>
						</td>
					</tr>
					<tr id="tr_superior">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('superior');?>
						</td>
						<td width="260px">
							<input type="text" name="superior" id="superior" value=""/>
						</td>
					</tr>
					<tr id="tr_superior_cb" style="display:none">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('superior');?>
						</td>
						<td width="260px">
							<select name="superior_cb" id="superior_cb" style="width:140px">
							<option value="">---</option>
							</select>
						</td>
					</tr>
					<tr id="tr_inferior">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('inferior');?>
						</td>
						<td width="260px">
							<input type="text" name="inferior" id="inferior" value=""/>
						</td>
					</tr>
					<tr id="tr_inferior_cb" style="display:none">
						<td nowrap="nowrap" width="120px">
							<?php echo recherche_libelle_page('inferior');?>
						</td>
						<td width="260px">
							<select name="inferior_cb" id="inferior_cb" style="width:140px">
							<option value="">---</option>
							</select>
						</td>
					</tr>
					<tr name="tr_between" id="tr_between">
						<td nowrap="nowrap" width="120px" colspan="2">
							<?php echo recherche_libelle_page('between');?><input type="text" name="between" id="between" value=""/>
						</td>
					</tr>
					</table>
				  </div>
					<?php
					//}
					?>	
				<div align="right" id="filter_action">
					<?php if(isset($_SESSION['tab_get_filter'])){ ?>
					<input style="width:125px; color:#FF0000; font:bold" name="cancel" id="cancel" type="button" onClick="filter_grille('cancel')" value="<?php echo recherche_libelle_page('cancelfilter');?>"/>
					&nbsp;&nbsp;
					<?php } ?>
					<input name="ok" id="ok" type="button" onClick="filter_grille('filter')" value="<?php echo recherche_libelle_page('submit');?>"/>
					&nbsp;&nbsp;
					<input name="annuler" id="annuler" type="button" onClick="MM_showHideLayers('show_form_filter','','show','hide_form_filter','','hide','form_filter','','hide','filter_action','','hide')" value="<?php echo recherche_libelle_page('closeest');?>"/>&nbsp;&nbsp;
				</div>
			  </form>
		  	</div>
			
			<!-- Espace export grille-->
			<?php 
			if(isset($GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])){
			if((isset($_GET['tmis']) && $_GET['tmis']=='teach_data') || (!isset($_GET['tmis']))){
			?>
			<div id="show_form_export"><input style='text-align:center; color:#0000FF;  width:100px; font:bold; font-size:12px' type='button' name='btn_export_show' id='btn_export_show' value="<?php echo recherche_libelle_page('dataexport');?>&nbsp;&nbsp;>>" onClick="MM_showHideLayers('show_form_export','','hide','hide_form_export','','show','form_export','','show','export_action','','show','show_form_sort','','show','hide_form_sort','','hide','form_sort','','hide','sort_action','','hide','show_form_filter','','show','hide_form_filter','','hide','form_filter','','hide','filter_action','','hide')"/></div> 
			<div id="hide_form_export"><input style='text-align:center; color:#0000FF;  width:100px; font:bold; font-size:12px' type='button' name='btn_export_hide' id='btn_export_hide' value="<?php echo recherche_libelle_page('dataexport');?>&nbsp;&nbsp;<<" onClick="MM_showHideLayers('show_form_export','','show','hide_form_export','','hide','form_export','','hide','export_action','','hide')"/></div>
			<?php 
			}
			} ?>
			<div id="form_export">
				<form  name="export_formulaire" id="export_formulaire">
				  <table id="tabl_export"  width="100%" border="1px">
					  <caption><?php echo '<strong>'.recherche_libelle_page('actionexport').'</strong>'; ?></caption>
					<?php
					$nb_col = count($list_col);
					foreach($list_col as $cpt => $col){
					?>	 
					<?php
						echo "<input type='hidden' name='chp_export_".$cpt."' id='chp_export_".$cpt."' value='".$col['TYPE_ZONE_BASE']."|".$col['TABLE_MERE']."-".$col['ID_ZONE']."' />";
					?>
					 <tr>
						<td nowrap="nowrap" style="vertical-align:middle">
						 	<?php if($col['LIBELLE']<>'') echo $col['LIBELLE']; else echo $col['CHAMP_PERE']; ?>
						</td>
						<td>
						 	<input type="checkbox" name="<?php echo 'ord_export_'.$cpt; ?>" id="<?php echo 'ord_export_'.$cpt; ?>" value="1" checked="checked" />
						</td>
					</tr>
					<?php
					}
					?>	
				</table>
				<div align="right" id="export_action">
					<input name="ok" id="ok" type="button" onClick="export_grille(<?php if(!isset($_GET['ligne']) && !isset($_GET['id_teacher'])) echo count($list_col).",'gril'"; else echo count($list_col).",'form_hist'"; ?>)" value="<?php echo recherche_libelle_page('submit');?>">
					&nbsp;&nbsp;
					<input name="annuler" id="annuler" type="button" onClick="MM_showHideLayers('show_form_export','','show','hide_form_export','','hide','form_export','','hide','export_action','','hide')" value="<?php echo recherche_libelle_page('closeest');?>">&nbsp;&nbsp;
				</div>
			  </form>
		  	</div>
			
			
			<!-- Espace export teacher records -->
			<?php
			//if(isset($_GET['ligne'])) unset($_SESSION['liste_etablissements']);
			if((isset($_GET['tmis']) && $_GET['tmis']=='teach_rec_import') || (!isset($_GET['tmis']) && (isset($_GET['ligne']) || isset($_GET['id_teacher'])))){
			?>
			<div id="show_form_import"><input style='text-align:center; color:#0000FF;  width:200px; font:bold; font-size:12px' type='button' name='btn_imput_show' id='btn_imput_show' value="<?php echo recherche_libelle_page('import_teach_rec');?>&nbsp;&nbsp;>>" onClick="MM_showHideLayers('show_form_import','','hide','hide_form_import','','show','form_import','','show','imput_running','','hide')"/></div> 
			<div id="hide_form_import"><input style='text-align:center; color:#0000FF;  width:200px; font:bold; font-size:12px' type='button' name='btn_imput_hide' id='btn_imput_hide' value="<?php echo recherche_libelle_page('import_teach_rec');?>&nbsp;&nbsp;<<" onClick="MM_showHideLayers('show_form_import','','show','hide_form_import','','hide','form_import','','hide','imput_running','','hide')"/></div>
			<?php
			}
			?>	
				<div id="form_import">
				<form  name="import_formulaire" id="import_formulaire">
				  <table id="tabl_import"  width="100%" border="2">
					  <caption><?php echo recherche_libelle_page('import_teach_rec'); ?></caption>
					<?php 
					echo '<tr><td>'.recherche_libelle_page('NomEtabHote').'</td>'."\n".'<td>';
					echo '<select name="etab" id="etab">' . "\n";
					if (isset($_SESSION['liste_etablissements']) && is_array($_SESSION['liste_etablissements'])){
						foreach ($_SESSION['liste_etablissements'] as $i_etab => $row) {
							$nom_link = $row[$GLOBALS['PARAM']['NOM_ETABLISSEMENT']] ;
							if( isset($row[$GLOBALS['PARAM']['CODE_ADMINISTRATIF']])){
								(trim($row[$GLOBALS['PARAM']['CODE_ADMINISTRATIF']]) <> '') ? ($pls_code_adm = ' &nbsp;&nbsp; <em>('.$row[$GLOBALS['PARAM']['CODE_ADMINISTRATIF']].')</em>') : ($pls_code_adm = '') ;
							}
							$sel = '';
							if(isset($_GET['code_etab']) && $_GET['code_etab']<>0){
								if($row[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] == $_GET['code_etab']) {
									$sel = ' selected';
								}
							}elseif($_SESSION['code_etab']<>''){
								if($row[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] == $_SESSION['code_etab']) {
									$sel = ' selected';
								}
							}elseif($GLOBALS['code_etab']<>''){
								if($row[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] == $GLOBALS['code_etab']) {
									$sel = ' selected';
								}
							}
							echo '<option value="' . $row[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] . '"'.$sel.'>' . $nom_link .$pls_code_adm. '</option>' . "\n";
						}
					}elseif(isset($_SESSION['code_etab']) && $_SESSION['code_etab']<>''){
						echo '<option value="' .$_SESSION['code_etab'].'">' .$_SESSION['nom_etab']. '</option>' . "\n";
					}elseif(isset($GLOBALS['code_etab']) && $GLOBALS['code_etab']<>''){
						echo '<option value="' .$GLOBALS['code_etab'].'">' .$_SESSION['nom_etab']. '</option>' . "\n";
					}
					echo '</select>' . "\n";
					echo '</td></tr>' . "\n";	
					 ?>
					 <tr>
						<td nowrap="nowrap"><?php echo recherche_libelle_page('source_teach_file'); ?>&nbsp;</td>
						<td nowrap="nowrap">
						<div style="position :absolute ;">
						<input type="file" id="DOSSIER_SOURCE" name="DOSSIER_SOURCE" style="filter :alpha(opacity=0); opacity:0" size="50" 
						onchange="document.import_formulaire.rep_source.value=document.import_formulaire.DOSSIER_SOURCE.value">
						</div>
						<input size="50" type="text" name="rep_source" id="rep_source" <?php echo 'value="" ';?> >
						<INPUT type="button" value="..." onClick="document.getElementById('DOSSIER_SOURCE').click();">
						</td>
					</tr>
					<tr>
						<td><?php echo recherche_libelle_page('dest_log_file'); ?></td>
						<td><input size="50" type="text" name="log_file" id="log_file" <?php echo 'value="" ';?> ></td>
					</tr>
					<tr>
					  <td colspan="2" nowrap="nowrap">
						 <div align="right">
							<input name="ok" id="ok" type="button" onClick="import_teacher_records(<?php if(isset($_GET['tmis'])) echo "'gril'"; else echo "'form'";?>,document.import_formulaire.etab.value,document.import_formulaire.rep_source.value,document.import_formulaire.log_file.value)" value="<?php echo recherche_libelle_page('submit');?>">
							&nbsp;&nbsp;
							<input name="annuler" id="annuler" type="button" onClick="MM_showHideLayers('show_form_import','','show','hide_form_import','','hide','form_import','','hide','import_action','','hide')" value="<?php echo recherche_libelle_page('closeest');?>">&nbsp;&nbsp;
						</div>
					  </td>
					 </tr>
					</table>
				  </form>
			  </div>
</DIV>

<div id="photo-light"></div>
<script language="JavaScript" type="text/javascript">
//
<?php 
if(isset($_GET['ret_list']) && $_GET['ret_list']<>'')	echo "MiseEvidenceLigneFrame(".$_GET['line'].",".$_GET['nb_line'].");\n";
if(isset($GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])) echo "filter_criteria();\n";
if(isset($_GET['tmis'])){
	 echo 'var list_div_btn = new Array("show_form_imput","hide_form_imput","add_new_teach","hide_form_sort","show_form_sort","hide_form_filter","show_form_filter","hide_form_export","show_form_export");'."\n";
	 echo 'var list_div_info = new Array("info_imput","info_sort","info_filter");'."\n";
	 //echo 'var list_div = new Array("info_imput","info_sort","info_filter","show_form_imput","hide_form_imput","form_imput","imput_running","aa","bb","hide_form_sort","show_form_sort","form_sort","sort_action","hide_form_filter","show_form_filter","form_filter","filter_action","hide_form_export","show_form_export","form_export","export_action");'."\n";
	 echo "for (i=0; i<(list_div_btn.length); i++){\n";
	 	echo "if(document.getElementById(list_div_btn[i])){\n";
			//echo "var val_top = document.getElementById(list_div[i]).style.top;\n";
			echo "document.getElementById(list_div_btn[i]).style.top += 240;\n";
		echo "}\n";
	 echo "}\n";
	 echo "for (i=0; i<(list_div_info.length); i++){\n";
	 	echo "if(document.getElementById(list_div_info[i])){\n";
			//echo "var val_top = document.getElementById(list_div[i]).style.top;\n";
			echo "document.getElementById(list_div_info[i]).style.top += 205;\n";
		echo "}\n";
	 echo "}\n";
	 echo "if(document.getElementById('info_imput'))\n";
	 echo "document.getElementById('info_imput').style.right = 80;\n";
	 echo "if(document.getElementById('form_imput'))\n";
	 echo "document.getElementById('form_imput').style.top = 200;\n";
	 echo "if(document.getElementById('info_imput_span'))\n";
	 echo "document.getElementById('info_imput_span').style.width = 150;\n";
	 echo "if(show_imput_form){ MM_showHideLayers('show_form_imput','','hide','hide_form_imput','','show','form_imput','','show','imput_running','','hide');\n";
	 echo "if(document.getElementById('form_imput'))\n";
	 echo "document.getElementById('form_imput').style.top = 130;}\n";
	 
}
if(((isset($_GET['action']) && $_GET['action']=='add_new_teach') && (!isset($_GET['deb_save'])) || (isset($_GET['ligne']) || isset($_GET['id_teacher']))) && (!isset($_SESSION['infos_etab']) || $_SESSION['infos_etab']=='')){
	echo 'var list_div_info = new Array("info_imput","info_sort","info_filter");'."\n";
	echo "for (i=0; i<(list_div_info.length); i++){\n";
		echo "if(document.getElementById(list_div_info[i])){\n";
			//echo "var val_top = document.getElementById(list_div[i]).style.top;\n";
			echo "document.getElementById(list_div_info[i]).style.top += 45;\n";
		echo "}\n";
	 echo "}\n";
	 echo "if(document.getElementById('show_form_import'))\n";
	 echo "document.getElementById('show_form_import').style.top = 80;\n";
	 echo "if(document.getElementById('hide_form_import'))\n";
	 echo "document.getElementById('hide_form_import').style.top = 80;\n";
	 echo "if(document.getElementById('form_import'))\n";
	 echo "document.getElementById('form_import').style.top = 105;\n";
	 
}
if(isset($_GET['ligne']) || isset($_GET['id_teacher'])){
?>
function AjusteFormExport(){
	var top_val_btn_export = document.getElementById('submit_actions').offsetTop;
	if(document.getElementById('form_export'))
	document.getElementById('form_export').style.top = top_val_btn_export;
	if(document.getElementById('export_action'))
	document.getElementById('export_action').style.top = top_val_btn_export + 342;
}
<?php
}
?>
</script>
<?php require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php'; ?>
