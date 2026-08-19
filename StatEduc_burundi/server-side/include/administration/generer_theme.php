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

// ── FIX S17c : Résolution de langue via TRIPLE intersection ──────────────────
// Problème : frame::generer_frame() (ligne ~10515) filtre DICO_TRADUCTION par
// CODE_LANGUE = $langue et NOM_TABLE='DICO_THEME_LIB_LONG'.
// Si DICO_TRADUCTION n'a que 'fr' mais DICO_LANGUE renvoie 'eng',
// aucun thème ne produit de résultat → count($aresult)=0 → génération sautée.
//
// Solution : intersecter DICO_LANGUE ∩ DICO_TYPE_THEME ∩ DICO_TRADUCTION.
// La langue retenue DOIT exister dans les 3 tables pour que la génération fonctionne.

// Étape 1 : Langues dans DICO_TYPE_THEME
$_langs_dtt = array();
$_res = $GLOBALS['conn_dico']->GetAll("SELECT DISTINCT CODE_LANGUE FROM DICO_TYPE_THEME ORDER BY CODE_LANGUE");
if (!empty($_res) && is_array($_res)) {
    $_langs_dtt = array_column($_res, 'CODE_LANGUE');
}

// Étape 2 : Langues dans DICO_TRADUCTION pour NOM_TABLE='DICO_THEME_LIB_LONG'
$_langs_trad = array();
$_res2 = $GLOBALS['conn_dico']->GetAll(
    "SELECT DISTINCT CODE_LANGUE FROM DICO_TRADUCTION WHERE NOM_TABLE='DICO_THEME_LIB_LONG' ORDER BY CODE_LANGUE"
);
if (!empty($_res2) && is_array($_res2)) {
    $_langs_trad = array_column($_res2, 'CODE_LANGUE');
}

// Étape 3 : Intersection DICO_LANGUE ∩ DICO_TYPE_THEME ∩ DICO_TRADUCTION
$_intersection_full = $langues;
if (!empty($_langs_dtt)) {
    $_intersection_full = array_values(array_intersect($_intersection_full, $_langs_dtt));
}
if (!empty($_langs_trad)) {
    $_intersection_full = array_values(array_intersect($_intersection_full, $_langs_trad));
}

// Fallback progressif si l'intersection complète est vide
if (!empty($_intersection_full)) {
    $langues = $_intersection_full;
} elseif (!empty($_langs_trad)) {
    // DICO_TRADUCTION est la contrainte bloquante : utiliser ses langues
    $langues = $_langs_trad;
} elseif (!empty($_langs_dtt)) {
    $langues = $_langs_dtt;
}
// sinon on garde $langues tel quel (DICO_LANGUE)

// Forcer la session avec la première langue valide
$_SESSION['langue'] = !empty($langues) ? $langues[0] : 'fr';

// ── Diagnostic affiché pour validation ───────────────────────────────────────
echo '<div style="background:#f0f7ff;border:1px solid #1a56db;border-radius:6px;padding:10px 14px;margin:10px 0;font-size:.85rem;font-family:monospace;">';
echo '<strong style="color:#1a56db;">🔍 Diagnostic gentheme (S17c)</strong><br>';
echo '&nbsp;DICO_LANGUE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <strong>' . htmlspecialchars(implode(', ', $langues)) . '</strong><br>';
echo '&nbsp;DICO_TYPE_THEME &nbsp;&nbsp;&nbsp;&nbsp;: <strong>' . htmlspecialchars(!empty($_langs_dtt) ? implode(', ', $_langs_dtt) : '(vide)') . '</strong><br>';
echo '&nbsp;DICO_TRADUCTION &nbsp;&nbsp;&nbsp;: <strong>' . htmlspecialchars(!empty($_langs_trad) ? implode(', ', $_langs_trad) : '(vide)') . '</strong><br>';
echo '&nbsp;Intersection finale : <strong>' . htmlspecialchars(!empty($_intersection_full) ? implode(', ', $_intersection_full) : '(vide — fallback utilisé)') . '</strong><br>';
echo '&nbsp;Langue session &nbsp;&nbsp;&nbsp;&nbsp;: <strong style="color:#0a7c30;">' . htmlspecialchars($_SESSION['langue']) . '</strong><br>';
echo '&nbsp;Thèmes à générer &nbsp;: <strong>' . count($id_themes) . '</strong> | Systèmes : <strong>' . implode(', ', $id_systemes) . '</strong><br>';

// Vérification répertoire questionnaire
foreach ($langues as $_l) {
    $_rep = $GLOBALS['SISED_PATH'] . 'questionnaire/' . $_l . '/';
    if (!file_exists($_rep)) {
        @mkdir($_rep, 0770, true);
        echo '&nbsp;📁 Dossier créé : ' . htmlspecialchars($_rep) . '<br>';
    } else {
        $_writable = is_writable($_rep) ? '✅ accessible en écriture' : '❌ NON accessible en écriture';
        echo '&nbsp;📁 ' . htmlspecialchars($_rep) . ' → ' . $_writable . '<br>';
    }
}
echo '</div>';
flush();
unset($_langs_dtt, $_langs_trad, $_intersection_full, $_res, $_res2, $_rep, $_writable, $_l);

// ── Vérification des types disponibles ───────────────────────────────────────
$_check_types = $GLOBALS['conn_dico']->GetAll(
    "SELECT DISTINCT LIBELLE FROM DICO_TYPE_THEME WHERE CODE_LANGUE='" . $_SESSION['langue'] . "' ORDER BY LIBELLE"
);
if (!empty($_check_types) && is_array($_check_types)) {
    $_libelles = array_column($_check_types, 'LIBELLE');
    echo '<p style="color:#1a56db;font-size:.85rem;">🔍 Types de frames disponibles pour <strong>'
         . htmlspecialchars($_SESSION['langue']) . '</strong> : '
         . htmlspecialchars(implode(', ', $_libelles)) . '</p>';
    flush();
    unset($_libelles);
} else {
    echo '<p style="color:#CE1126;">⚠️ DICO_TYPE_THEME : aucun libellé pour CODE_LANGUE=\''
         . htmlspecialchars($_SESSION['langue']) . '\'</p>';
    flush();
}
unset($_check_types);

// ── Instanciation frame — le constructeur appelle generer_frame() automatiquement ──
// (voir frame.class.php ligne 97 : $this->generer_frame($code_annee, $code_etablissement))
$form = new frame( $id_themes, $langues, $id_systemes, '', '' );
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) {
    $form_mobile = new frame_mobile( $id_themes, $langues, $id_systemes, '', '' );
}

?>
