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

// ── FIX S17b : Synchroniser $_SESSION['langue'] avec DICO_TYPE_THEME ──────────────────────────
// frame::generer_frame() ligne 10543 filtre DICO_TYPE_THEME par CODE_LANGUE = $_SESSION['langue'].
// La table DICO_TYPE_THEME peut avoir des langues différentes de DICO_LANGUE.
// On découvre toutes les langues présentes dans DICO_TYPE_THEME et on force $langues en conséquence.

// Étape 1 : Toutes les langues disponibles dans DICO_TYPE_THEME
$_langs_in_dico_type = $GLOBALS['conn_dico']->GetAll(
    "SELECT DISTINCT CODE_LANGUE FROM DICO_TYPE_THEME ORDER BY CODE_LANGUE"
);

if (!empty($_langs_in_dico_type) && is_array($_langs_in_dico_type)) {
    // Reconstruire $langues en ne gardant QUE les langues réellement présentes dans DICO_TYPE_THEME
    $langues_in_dtt = array_column($_langs_in_dico_type, 'CODE_LANGUE');

    // Si $langues (de DICO_LANGUE) a une intersection avec $langues_in_dtt → utiliser l'intersection
    $intersection = array_intersect($langues, $langues_in_dtt);
    if (!empty($intersection)) {
        // Il y a des langues communes → garder seulement celles-là
        $langues = array_values($intersection);
    } else {
        // Pas d'intersection → forcer avec ce qui existe dans DICO_TYPE_THEME
        $langues = $langues_in_dtt;
    }
    // Synchroniser la session avec la première langue valide
    $_SESSION['langue'] = $langues[0];

    // DEBUG : afficher les langues trouvées (à retirer une fois stabilisé)
    echo '<p style="color:#1a56db;font-size:.85rem;">🔍 FIX gentheme — DICO_TYPE_THEME CODE_LANGUE : <strong>'
         . htmlspecialchars(implode(', ', $langues_in_dtt))
         . '</strong> | Langue active session : <strong>'
         . htmlspecialchars($_SESSION['langue'])
         . '</strong></p>';
    flush();
    unset($langues_in_dtt, $intersection);
} else {
    // DICO_TYPE_THEME vide ou inaccessible → fallback
    if (!isset($_SESSION['langue']) || $_SESSION['langue'] === '') {
        $_SESSION['langue'] = !empty($langues) ? $langues[0] : 'fr';
    }
    echo '<p style="color:#CE1126;">⚠️ DICO_TYPE_THEME : aucune langue trouvée. Utilisation de : '
         . htmlspecialchars($_SESSION['langue']) . '</p>';
    flush();
}
unset($_langs_in_dico_type);

// Étape 2 : Vérification que DICO_TYPE_THEME contient bien des libellés reconnus
// (Formulaire, Grille, Matrice, etc.) pour la langue sélectionnée
$_check_types = $GLOBALS['conn_dico']->GetAll(
    "SELECT DISTINCT LIBELLE FROM DICO_TYPE_THEME WHERE CODE_LANGUE='" . $_SESSION['langue'] . "' ORDER BY LIBELLE"
);
if (!empty($_check_types) && is_array($_check_types)) {
    $_libelles = array_column($_check_types, 'LIBELLE');
    echo '<p style="color:#1a56db;font-size:.85rem;">🔍 Types de frames disponibles : <strong>'
         . htmlspecialchars(implode(', ', $_libelles)) . '</strong></p>';
    flush();
    unset($_libelles);
} else {
    echo '<p style="color:#CE1126;">⚠️ DICO_TYPE_THEME : aucun libellé pour CODE_LANGUE=\''
         . htmlspecialchars($_SESSION['langue']) . '\'</p>';
    flush();
}
unset($_check_types);

// Étape 3 : Instanciation frame — le constructeur appelle generer_frame() automatiquement
// (voir frame.class.php ligne 97 : $this->generer_frame($code_annee, $code_etablissement))
// Pas besoin d'appeler generer_frame() manuellement ensuite.
$form = new frame( $id_themes, $langues, $id_systemes, '', '' );
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) {
    $form_mobile = new frame_mobile( $id_themes, $langues, $id_systemes, '', '' );
}

//------------------------------------------
// NOTE : Le bloc set_id_theme/generer_frame() ci-dessous était
// une ancienne méthode thème par thème, maintenant inutile car
// le constructeur frame() appelle generer_frame() pour TOUS les thèmes.
//------------------------------------------

?>
