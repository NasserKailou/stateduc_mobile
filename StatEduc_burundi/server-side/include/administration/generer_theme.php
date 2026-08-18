<?php
set_time_limit(0);
ini_set("memory_limit", "128M");

require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame.class.php';
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';

// ── CORRECTION BUG LANGUE ────────────────────────────────────────────────────
// generer_frame() dans frame.class.php interroge DICO_TYPE_THEME avec
// AND DICO_TYPE_THEME.CODE_LANGUE = '$_SESSION[langue]'
// Si la session utilisateur vaut 'eng' et que DICO_TYPE_THEME n'a que des
// entrées 'fr', la requête renvoie vide → $type_frame='' → switch default →
// "Attention! Dico" → aucun fichier écrit.
//
// Solution : forcer temporairement $_SESSION['langue'] = 'fr' (langue de
// référence du dictionnaire) pendant toute la génération, puis restaurer
// la valeur d'origine. La génération des FRAMES est une opération système :
// les fichiers template sont identiques quelle que soit la session courante.
// ────────────────────────────────────────────────────────────────────────────
$_langue_session_original = isset($_SESSION['langue']) ? $_SESSION['langue'] : 'fr';
$_SESSION['langue'] = 'fr';  // Force 'fr' pour la génération des frames

// ── Construction des tableaux de paramètres ──────────────────────────────────
$db             = $GLOBALS['conn'];

$langues		=	array();
$id_themes		=	array();
$id_systemes	=	array();

if(isset($_GET['langue_regen'])){
		$langues[]		=	$_GET['langue_regen'];
}else{
		$requete   		= "SELECT CODE_LANGUE, LIBELLE_LANGUE FROM DICO_LANGUE;";
		$all_langues 	= $GLOBALS['conn_dico']->GetAll($requete);
		foreach ($all_langues as $langue){
			$langues[]	=	$langue['CODE_LANGUE'];
		}
}

$requete   		= 'SELECT ID FROM DICO_THEME WHERE ID_TYPE_THEME<>8';
                  
$all_themes 	= $GLOBALS['conn_dico']->GetAll($requete);
foreach ($all_themes as $theme){
	$id_themes[]	=	$theme['ID'];
}

$requete   		= 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
$all_systemes = $db->GetAll($requete);
foreach ($all_systemes as $systeme){
	$id_systemes[]	=	$systeme[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
}

// ── Instanciation : le constructeur appelle generer_frame() automatiquement ──
// NOTE : new frame() appelle $this->generer_frame() dans son __construct().
// Il ne faut PAS appeler $form->generer_frame() à nouveau après — ce serait
// une double génération inutile. Le résultat est déjà produit par le constructeur.
$form           =	new frame( $id_themes, $langues, $id_systemes, '', '' );
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) $form_mobile    =	new frame_mobile( $id_themes, $langues, $id_systemes, '', '' );

// ── Restauration de la langue de session ─────────────────────────────────────
$_SESSION['langue'] = $_langue_session_original;
unset($_langue_session_original);

// ── Confirmation visuelle ─────────────────────────────────────────────────────
// La génération s'est faite dans le constructeur ci-dessus.
// On affiche juste un message de succès.
echo '<p style="color:green;font-weight:bold;margin:10px 0;">&#10003; Génération des frames terminée avec succès.</p>';
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG'] && isset($form_mobile)) {
	echo '<p style="color:green;">&#10003; Génération mobile terminée.</p>';
}

?>
