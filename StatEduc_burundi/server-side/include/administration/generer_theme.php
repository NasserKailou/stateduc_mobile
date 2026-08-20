<?php
set_time_limit(0);
ini_set("memory_limit", "256M");
// Désactiver le buffer de sortie pour voir la progression en temps réel
if (ob_get_level()) { ob_end_flush(); }

// ── INTERCEPTEUR D'ERREURS FATALES ───────────────────────────────────────────
// Les Fatal Errors PHP 8 ne sont pas des Exceptions → elles tuent le script
// silencieusement. register_shutdown_function les capture après la mort du script.
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo '<div style="background:#fff0f0;border:2px solid red;padding:12px;margin:10px 0;font-family:monospace;">';
        echo '<strong style="color:red;">💀 ERREUR FATALE</strong><br>';
        echo '&nbsp;Message : ' . htmlspecialchars($err['message']) . '<br>';
        echo '&nbsp;Fichier  : ' . htmlspecialchars($err['file']) . ' : ' . htmlspecialchars($err['line']) . '<br>';
        echo '</div>';
        flush();
    }
});

// Capturer les warnings PHP (E_WARNING) — ignorer E_NOTICE/E_DEPRECATED
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (in_array($errno, [E_NOTICE, E_DEPRECATED, E_USER_DEPRECATED])) {
        return true;
    }
    echo '<div style="background:#fffbe6;border:1px solid #f0ad4e;padding:4px 10px;margin:2px 0;font-size:.8rem;font-family:monospace;">';
    echo '⚠️ ' . htmlspecialchars($errstr) . ' — ' . htmlspecialchars(basename($errfile)) . ':' . $errline;
    echo '</div>';
    flush();
    return true;
});

require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame.class.php';
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';

// ── Construction des listes de paramètres ────────────────────────────────────
$db             = $GLOBALS['conn'];
$langues        = array();
$id_themes      = array();
$id_systemes    = array();

if(isset($_GET['langue_regen'])){
    $langues[] = $_GET['langue_regen'];
}else{
    $requete     = "SELECT CODE_LANGUE, LIBELLE_LANGUE FROM DICO_LANGUE;";
    $all_langues = $GLOBALS['conn_dico']->GetAll($requete);
    foreach ($all_langues as $langue){
        $langues[] = $langue['CODE_LANGUE'];
    }
}

$requete    = 'SELECT ID FROM DICO_THEME WHERE ID_TYPE_THEME<>8';
$all_themes = $GLOBALS['conn_dico']->GetAll($requete);
foreach ($all_themes as $theme){
    $id_themes[] = $theme['ID'];
}

$requete      = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
$all_systemes = $db->GetAll($requete);
foreach ($all_systemes as $systeme){
    $id_systemes[] = $systeme[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
}

// ── Résolution de langue via TRIPLE intersection ──────────────────────────────
$_langs_dtt = array();
$_res = $GLOBALS['conn_dico']->GetAll("SELECT DISTINCT CODE_LANGUE FROM DICO_TYPE_THEME ORDER BY CODE_LANGUE");
if (!empty($_res) && is_array($_res)) {
    $_langs_dtt = array_column($_res, 'CODE_LANGUE');
}

$_langs_trad = array();
$_res2 = $GLOBALS['conn_dico']->GetAll(
    "SELECT DISTINCT CODE_LANGUE FROM DICO_TRADUCTION WHERE NOM_TABLE='DICO_THEME_LIB_LONG' ORDER BY CODE_LANGUE"
);
if (!empty($_res2) && is_array($_res2)) {
    $_langs_trad = array_column($_res2, 'CODE_LANGUE');
}

$_intersection_full = $langues;
if (!empty($_langs_dtt)) {
    $_intersection_full = array_values(array_intersect($_intersection_full, $_langs_dtt));
}
if (!empty($_langs_trad)) {
    $_intersection_full = array_values(array_intersect($_intersection_full, $_langs_trad));
}

if (!empty($_intersection_full)) {
    $langues = $_intersection_full;
} elseif (!empty($_langs_trad)) {
    $langues = $_langs_trad;
} elseif (!empty($_langs_dtt)) {
    $langues = $_langs_dtt;
}

$_SESSION['langue'] = !empty($langues) ? $langues[0] : 'fr';

// Créer les répertoires de sortie si nécessaire
foreach ($langues as $_l) {
    $_rep = $GLOBALS['SISED_PATH'] . 'questionnaire/' . $_l . '/';
    if (!file_exists($_rep)) {
        @mkdir($_rep, 0770, true);
    }
}
unset($_langs_dtt, $_langs_trad, $_intersection_full, $_res, $_res2, $_rep, $_l);

echo '<p style="color:#666;font-size:.85rem;">⏳ Génération en cours : ' . count($id_themes) . ' thème(s) × ' . count($id_systemes) . ' système(s) × ' . implode(', ', $langues) . '...</p>';
flush();

// ── Génération frame ─────────────────────────────────────────────────────────
$form = new frame( $id_themes, $langues, $id_systemes, '', '' );

echo '<p style="color:#28a745;font-weight:bold;">✅ Génération frame terminée.</p>';
flush();

if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) {
    $form_mobile = new frame_mobile( $id_themes, $langues, $id_systemes, '', '' );
    echo '<p style="color:#28a745;font-weight:bold;">✅ Génération frame_mobile terminée.</p>';
    flush();
}
?>
