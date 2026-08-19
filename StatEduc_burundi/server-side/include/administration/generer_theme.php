<?php
set_time_limit(0);
ini_set("memory_limit", "128M");
// PHP8 compat: desactiver le buffer de sortie pour voir la progression en temps reel
if (ob_get_level()) { ob_end_flush(); }
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

// ── FIX S17 : Synchroniser $_SESSION['langue'] avec la valeur réelle de DICO_TYPE_THEME ──
// frame::generer_frame() (ligne 10543) filtre DICO_TYPE_THEME par CODE_LANGUE = $_SESSION['langue'].
// Si la table ne contient que 'fr' mais que $_SESSION['langue'] = 'eng' (lu depuis DICO_LANGUE),
// aucune ligne n'est retournée → $type_frame vide → switch default → "Attention! Dico" → pas de fichier.
// Solution : interroger DICO_TYPE_THEME pour connaître le CODE_LANGUE effectivement présent.
$_lang_dico_row = $GLOBALS['conn_dico']->GetRow(
    "SELECT DISTINCT CODE_LANGUE FROM DICO_TYPE_THEME LIMIT 1"
);
if (!empty($_lang_dico_row) && !empty($_lang_dico_row['CODE_LANGUE'])) {
    // Utiliser la langue trouvée dans le dictionnaire de thèmes
    $_SESSION['langue'] = $_lang_dico_row['CODE_LANGUE'];
    // Synchroniser aussi le tableau $langues pour que frame boucle sur la bonne langue
    if (!in_array($_lang_dico_row['CODE_LANGUE'], $langues)) {
        // Ajouter la langue réelle si elle n'est pas dans la liste DICO_LANGUE
        array_unshift($langues, $_lang_dico_row['CODE_LANGUE']);
    }
} elseif (!isset($_SESSION['langue']) || $_SESSION['langue'] === '') {
    // Fallback : première langue de DICO_LANGUE ou 'fr'
    $_SESSION['langue'] = !empty($langues) ? $langues[0] : 'fr';
}
unset($_lang_dico_row);

$form           =	new frame( $id_themes, $langues, $id_systemes, '', '' );
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) $form_mobile    =	new frame_mobile( $id_themes, $langues, $id_systemes, '', '' );

// Pour chaque langue 


//------------------------------------------


/*
// Traitement du theme ENV SOCIO ECO (type formulaire)
$form->set_id_theme(20);
$form->generer_frame();

// Traitement du theme LOCAUX (type grille)
$form->set_id_theme(40);
$form->generer_frame();

// Traitement du theme 2
//$form->set_id_theme(180);
//$form->generer_frame();

// Traitement theme 3 (Matrice 2D)
$form->set_id_theme(50);
$form->generer_frame();


// Traitement theme 4 (Matrice 1 dimension /1 ou plusieurs lignes)
$form->set_id_theme(10);
$form->generer_frame();

// Traitement theme 4 (Matrice 1 dimension / 2 colonnes)
$form->set_id_theme(70);
$form->generer_frame();

// Users
$form->set_id_theme(2000);
$form->generer_frame();
// Bailleurs / Systeme
$form->set_id_theme(3000);
$form->generer_frame();


*/

//------------------------------------------

?>
