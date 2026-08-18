<?php
set_time_limit(0);
ini_set("memory_limit", "128M");
//On a besoin de la variable de session 'langue'

require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame.class.php';
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';

// Instanciation
$db             = $GLOBALS['conn'];

$langues		=	array();
$id_themes		=	array();
$id_systemes	=	array();

//$requete   		= "SELECT CODE_LANGUE, LIBELLE_LANGUE FROM DICO_LANGUE WHERE CODE_LANGUE='fr';";

if(isset($_GET['langue_regen'])){
		$langues[]		=	$_GET['langue_regen'];
}else{
		$requete   		= "SELECT CODE_LANGUE, LIBELLE_LANGUE FROM DICO_LANGUE;";
		$all_langues 	= $GLOBALS['conn_dico']->GetAll($requete);
		foreach ($all_langues as $langue){
			$langues[]	=	$langue['CODE_LANGUE'];
		}
}

//$requete   	= "SELECT ID FROM DICO_THEME WHERE ID IN ( 40, 50, 80, 90, 100, 110, 120, 130 );";
//$requete   		= "SELECT ID FROM DICO_THEME;";

//$requete   	= "SELECT ID FROM DICO_THEME WHERE ID IN ( 150 );";

$requete   		= 'SELECT ID FROM DICO_THEME WHERE ID_TYPE_THEME<>8';
                  
$all_themes 	= $GLOBALS['conn_dico']->GetAll($requete);
foreach ($all_themes as $theme){
	$id_themes[]	=	$theme['ID'];
}

//$requete   		= "SELECT ID_SYSTEME FROM DICO_SYSTEME WHERE ID_SYSTEME=1;";

$requete   		= 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
$all_systemes = $db->GetAll($requete);
foreach ($all_systemes as $systeme){
	$id_systemes[]	=	$systeme[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
}

$form           =	new frame( $id_themes, $langues, $id_systemes, '', '' );
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) $form_mobile    =	new frame_mobile( $id_themes, $langues, $id_systemes, '', '' );

// ── Génération de tous les thèmes ────────────────────────────────────────────
// CORRECTION PHP8 : le bloc d'appel était commenté dans /* */ — restauré.
// La classe frame() reçoit tous les id_themes/langues/id_systemes dans son
// constructeur et generer_frame() boucle en interne sur les trois dimensions.
// Un seul appel suffit — pas besoin de boucle foreach externe.
//------------------------------------------

try {
	$form->generer_frame();
	echo '<p style="color:green;font-weight:bold;margin:10px 0;">&#10003; Génération des frames terminée avec succès.</p>';
} catch (Exception $e) {
	echo '<p style="color:red;">Erreur lors de la génération : '.htmlspecialchars($e->getMessage()).'</p>';
}

if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG'] && isset($form_mobile)) {
	try {
		$form_mobile->generer_frame();
		echo '<p style="color:green;">&#10003; Génération mobile terminée.</p>';
	} catch (Exception $e) {
		echo '<p style="color:orange;">[Mobile] Erreur : '.htmlspecialchars($e->getMessage()).'</p>';
	}
}

//------------------------------------------

?>
