<?php
set_time_limit(0);
ini_set("memory_limit", "256M");
// PHP8 compat: desactiver le buffer de sortie pour voir la progression en temps reel
if (ob_get_level()) { ob_end_flush(); }

// ── INTERCEPTEUR D'ERREURS FATALES S17e ──────────────────────────────────────
// Les Fatal Errors PHP 8 (ex: null['key']) ne sont PAS des Exceptions → elles
// tuent le script silencieusement sans afficher quoi que ce soit après le crash.
// register_shutdown_function permet de les attraper APRÈS la mort du script.
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo '<div style="background:#fff0f0;border:2px solid red;padding:12px;margin:10px 0;font-family:monospace;">';
        echo '<strong style="color:red;">💀 FATAL ERROR INTERCEPTÉ (S17e)</strong><br>';
        echo '&nbsp;Type &nbsp;: ' . htmlspecialchars($err['type'] . ' (' . ($err['type'] === E_ERROR ? 'E_ERROR' : $err['type']) . ')') . '<br>';
        echo '&nbsp;Message : ' . htmlspecialchars($err['message']) . '<br>';
        echo '&nbsp;Fichier &nbsp;: ' . htmlspecialchars($err['file']) . '<br>';
        echo '&nbsp;Ligne &nbsp;&nbsp;: ' . htmlspecialchars($err['line']) . '<br>';
        echo '</div>';
        flush();
    }
});

// Capturer aussi les warnings/notices qui peuvent indiquer des problèmes
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Ignorer les E_NOTICE et E_DEPRECATED pour ne pas saturer l'affichage
    if (in_array($errno, [E_NOTICE, E_DEPRECATED, E_USER_DEPRECATED])) {
        return true; // supprimé silencieusement
    }
    // Afficher warnings et autres
    echo '<div style="background:#fffbe6;border:1px solid #f0ad4e;padding:6px 10px;margin:4px 0;font-size:.8rem;font-family:monospace;">';
    echo '⚠️ [PHP ' . $errno . '] ' . htmlspecialchars($errstr) . ' — ' . htmlspecialchars(basename($errfile)) . ':' . $errline;
    echo '</div>';
    flush();
    return true;
});

require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame.class.php';
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';

// Instanciation
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

// ── FIX S17c : Résolution de langue via TRIPLE intersection ──────────────────
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

// ── Diagnostic affiché ────────────────────────────────────────────────────────
echo '<div style="background:#f0f7ff;border:1px solid #1a56db;border-radius:6px;padding:10px 14px;margin:10px 0;font-size:.85rem;font-family:monospace;">';
echo '<strong style="color:#1a56db;">🔍 Diagnostic gentheme (S17e)</strong><br>';
echo '&nbsp;DICO_LANGUE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <strong>' . htmlspecialchars(implode(', ', $langues)) . '</strong><br>';
echo '&nbsp;DICO_TYPE_THEME &nbsp;&nbsp;&nbsp;&nbsp;: <strong>' . htmlspecialchars(!empty($_langs_dtt) ? implode(', ', $_langs_dtt) : '(vide)') . '</strong><br>';
echo '&nbsp;DICO_TRADUCTION &nbsp;&nbsp;&nbsp;: <strong>' . htmlspecialchars(!empty($_langs_trad) ? implode(', ', $_langs_trad) : '(vide)') . '</strong><br>';
echo '&nbsp;Intersection finale : <strong>' . htmlspecialchars(!empty($_intersection_full) ? implode(', ', $_intersection_full) : '(vide — fallback utilisé)') . '</strong><br>';
echo '&nbsp;Langue session &nbsp;&nbsp;&nbsp;&nbsp;: <strong style="color:#0a7c30;">' . htmlspecialchars($_SESSION['langue']) . '</strong><br>';
echo '&nbsp;Thèmes à générer &nbsp;: <strong>' . count($id_themes) . '</strong> | Systèmes : <strong>' . implode(', ', $id_systemes) . '</strong><br>';

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

// ── TEST DIRECT D'UN THÈME AVANT L'INSTANCIATION COMPLÈTE ────────────────────
// Tester le premier thème/systeme pour voir exactement ce qui se passe
echo '<div style="background:#f0fff0;border:1px solid #28a745;padding:8px 12px;margin:6px 0;font-size:.8rem;font-family:monospace;">';
echo '<strong>🧪 Test direct premier thème</strong><br>';
if (!empty($id_themes) && !empty($id_systemes) && !empty($langues)) {
    $_t0 = $id_themes[0];
    $_s0 = $id_systemes[0];
    $_l0 = $langues[0];

    // Test requête DICO_TRADUCTION
    $_rq_trad = "SELECT LIBELLE FROM DICO_TRADUCTION WHERE CODE_NOMENCLATURE=" . $_t0 . $_s0 . " AND CODE_LANGUE='" . $_l0 . "' AND NOM_TABLE='DICO_THEME_LIB_LONG'";
    $_ar = $GLOBALS['conn_dico']->GetAll($_rq_trad);
    echo '&nbsp;DICO_TRADUCTION (id_theme=' . $_t0 . ', id_systeme=' . $_s0 . ', lang=' . $_l0 . ') → ';
    echo is_array($_ar) ? ('count=' . count($_ar) . ' | libelle=' . htmlspecialchars($_ar[0]['LIBELLE'] ?? '(vide)')) : '❌ ERREUR SQL';
    echo '<br>';

    // Test requête DICO_TYPE_THEME
    $_rq_type = "SELECT DICO_TYPE_THEME.LIBELLE FROM DICO_TYPE_THEME, DICO_THEME WHERE DICO_TYPE_THEME.ID_TYPE_THEME=DICO_THEME.ID_TYPE_THEME AND DICO_TYPE_THEME.CODE_LANGUE='" . $_l0 . "' AND DICO_THEME.ID=" . $_t0;
    $_ar2 = $GLOBALS['conn_dico']->GetAll($_rq_type);
    echo '&nbsp;DICO_TYPE_THEME (id_theme=' . $_t0 . ', lang=' . $_l0 . ') → ';
    echo is_array($_ar2) ? ('count=' . count($_ar2) . ' | type_frame=' . htmlspecialchars($_ar2[0]['LIBELLE'] ?? '(vide)')) : '❌ ERREUR SQL';
    echo '<br>';

    // Vérifier PHP version
    echo '&nbsp;PHP version : ' . PHP_VERSION . '<br>';
    echo '&nbsp;Premier id_theme : ' . $_t0 . ' | Premier id_systeme : ' . $_s0 . '<br>';
}
echo '</div>';
flush();
unset($_t0, $_s0, $_l0, $_rq_trad, $_rq_type, $_ar, $_ar2);

// ── INSTANCIATION avec un seul thème pour test rapide ────────────────────────
// Si on est en mode test (pas de paramètre lang_regen), limiter à 1 thème/1 systeme
// pour isoler le problème. Désactiver en prod en retirant le if ci-dessous.
$_test_mode = !isset($_GET['full']) && !isset($_GET['langue_regen']);

if ($_test_mode && count($id_themes) > 1) {
    echo '<div style="background:#fff3cd;border:1px solid #ffc107;padding:8px 12px;margin:6px 0;font-size:.8rem;">';
    echo '⚡ <strong>MODE TEST RAPIDE</strong> — 1 thème / 1 système pour diagnostic.<br>';
    echo 'URL pour génération complète : <code>administration.php?val=gentheme&full=1</code><br>';
    echo '</div>';
    flush();
    // Limiter à 3 premiers thèmes × premier système
    $id_themes   = array_slice($id_themes,   0, 3);
    $id_systemes = array_slice($id_systemes, 0, 1);
}

echo '<p style="color:#666;font-size:.8rem;">⏳ Instanciation frame en cours...</p>';
flush();

// ── Instanciation frame — le constructeur appelle generer_frame() automatiquement ──
$form = new frame( $id_themes, $langues, $id_systemes, '', '' );

echo '<p style="color:#28a745;font-weight:bold;">✅ Génération frame terminée.</p>';
flush();

if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) {
    echo '<p style="color:#666;font-size:.8rem;">⏳ Instanciation frame_mobile en cours...</p>';
    flush();
    $form_mobile = new frame_mobile( $id_themes, $langues, $id_systemes, '', '' );
    echo '<p style="color:#28a745;font-weight:bold;">✅ Génération frame_mobile terminée.</p>';
    flush();
}

?>
