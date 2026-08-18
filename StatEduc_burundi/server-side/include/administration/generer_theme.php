<?php
set_time_limit(0);
ini_set("memory_limit", "256M");

require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame.class.php';
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';

// ══════════════════════════════════════════════════════════════════════════════
// FIX DÉFINITIF GENTHEME — Session 7
// ══════════════════════════════════════════════════════════════════════════════
//
// CAUSES IDENTIFIÉES DES ZÉRO THÈMES GÉNÉRÉS :
//
// 1. BUG LANGUE (frame.class.php ~L10545) :
//    La requête DICO_TYPE_THEME utilise $_SESSION['langue'] directement
//    (et NON la variable de boucle $langue).
//    → Si la session vaut 'eng' et que DICO_TYPE_THEME n'a que 'fr',
//      la requête renvoie vide → $type_frame='' → switch default →
//      "Attention! Dico" → aucun fichier généré.
//    FIX : forcer $_SESSION['langue']='fr' AVANT new frame().
//
// 2. BUG CLÉ id_systemes (ADODB_ASSOC_CASE_UPPER) :
//    ADODB retourne les clés en MAJUSCULES. La clé construite doit être
//    exactement 'CODE_TYPE_SECTEUR_ENS'. Si $GLOBALS['PARAM']['CODE'] != 'CODE'
//    ou $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'] != 'TYPE_SECTEUR_ENS',
//    le tableau $id_systemes reste vide → aucune boucle systeme → zéro génération.
//    FIX : double tentative avec clé exacte ET clé construite + fallback.
//
// 3. BUG LIBELLE TRIM (switch exact-match) :
//    trim($aresult[0]['LIBELLE']) déjà fait dans frame.class.php.
//    Mais si LIBELLE contient des caractères non-ASCII (\r, \xc2\xa0 = NBSP),
//    trim() ne les supprime pas.
//    FIX : pré-patch de $_SESSION['langue'] garantit que la requête retourne
//    un résultat ; preg_replace appliqué via output buffering intercepte
//    "Attention! Dico" pour diagnostic.
//
// 4. BUG TABLEAU VIDE → new frame() avec arrays vides ne génère rien.
//    FIX : validation explicite avant instanciation + message d'erreur lisible.
//
// ══════════════════════════════════════════════════════════════════════════════

// ── 1. Forcer langue 'fr' pour toute la génération ───────────────────────────
$_langue_session_original = isset($_SESSION['langue']) ? $_SESSION['langue'] : 'fr';
$_SESSION['langue'] = 'fr';

// ── 2. Connexions ─────────────────────────────────────────────────────────────
$db       = $GLOBALS['conn'];       // Base principale (SQL Server BURUNDI)
$db_dico  = $GLOBALS['conn_dico'];  // Base dictionnaire (SQL Server)

// ── 3. Tableau $langues ───────────────────────────────────────────────────────
$langues     = array();
$id_themes   = array();
$id_systemes = array();
$errors      = array();
$info        = array();

if (isset($_GET['langue_regen'])) {
    $langues[] = trim($_GET['langue_regen']);
} else {
    $requete    = "SELECT CODE_LANGUE, LIBELLE_LANGUE FROM DICO_LANGUE;";
    $all_langues = $db_dico->GetAll($requete);
    if (is_array($all_langues)) {
        foreach ($all_langues as $row) {
            // Clé UPPER (ADODB_ASSOC_CASE_UPPER)
            $code = isset($row['CODE_LANGUE']) ? trim($row['CODE_LANGUE'])
                  : (isset($row['code_langue']) ? trim($row['code_langue']) : '');
            if ($code !== '') $langues[] = $code;
        }
    }
    if (empty($langues)) {
        // Fallback : forcer 'fr' si DICO_LANGUE est vide ou inaccessible
        $langues[] = 'fr';
        $errors[]  = 'DICO_LANGUE vide ou inaccessible → langue "fr" forcée en fallback.';
    }
}
$info[] = 'Langues : ' . implode(', ', $langues);

// ── 4. Tableau $id_themes ─────────────────────────────────────────────────────
$requete   = 'SELECT ID FROM DICO_THEME WHERE ID_TYPE_THEME<>8';
$all_themes = $db_dico->GetAll($requete);
if (is_array($all_themes)) {
    foreach ($all_themes as $row) {
        $id = isset($row['ID']) ? (int)$row['ID']
            : (isset($row['id']) ? (int)$row['id'] : 0);
        if ($id > 0) $id_themes[] = $id;
    }
}
$info[] = 'Thèmes (DICO_THEME, ID_TYPE_THEME<>8) : ' . count($id_themes) . ' → [' . implode(',', $id_themes) . ']';
if (empty($id_themes)) {
    $errors[] = 'DICO_THEME vide ou inaccessible : aucun thème à générer.';
}

// ── 5. Tableau $id_systemes ───────────────────────────────────────────────────
// Construire la clé exacte attendue (ADODB_ASSOC_CASE_UPPER → tout en majuscules)
$param_code    = isset($GLOBALS['PARAM']['CODE'])                    ? $GLOBALS['PARAM']['CODE']                    : 'CODE';
$param_type    = isset($GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']) ? $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'] : 'TYPE_SECTEUR_ENS';
$col_systeme   = strtoupper($param_code . '_' . $param_type);   // Ex: "CODE_TYPE_SECTEUR_ENS"
$table_systeme = $param_type;                                     // Ex: "TYPE_SECTEUR_ENS"

$requete_sys   = "SELECT {$col_systeme} FROM {$table_systeme};";
$all_systemes  = $db->GetAll($requete_sys);

if (is_array($all_systemes) && count($all_systemes) > 0) {
    foreach ($all_systemes as $row) {
        // Tenter d'abord clé construite (déjà UPPER), puis clé lower, puis premier élément
        if (isset($row[$col_systeme])) {
            $id_systemes[] = (int)$row[$col_systeme];
        } elseif (isset($row[strtolower($col_systeme)])) {
            $id_systemes[] = (int)$row[strtolower($col_systeme)];
        } else {
            // Dernier recours : première valeur du tableau
            $id_systemes[] = (int)reset($row);
        }
    }
    $id_systemes = array_unique(array_filter($id_systemes));
} else {
    // Fallback : essayer une requête simplifiée
    $requete_fallback = "SELECT * FROM {$table_systeme};";
    $all_sys2 = $db->GetAll($requete_fallback);
    if (is_array($all_sys2) && count($all_sys2) > 0) {
        foreach ($all_sys2 as $row) {
            // Chercher une colonne dont le nom contient CODE et TYPE
            foreach ($row as $key => $val) {
                if (stripos($key, 'CODE') !== false && stripos($key, 'TYPE') !== false) {
                    $id_systemes[] = (int)$val;
                    break;
                }
            }
        }
        $id_systemes = array_unique(array_filter($id_systemes));
        if (!empty($id_systemes)) {
            $errors[] = "Fallback id_systemes utilisé (SELECT * FROM {$table_systeme}).";
        }
    }
    if (empty($id_systemes)) {
        $errors[] = "Table {$table_systeme} vide ou inaccessible via requête: {$requete_sys}";
    }
}
$info[] = "Systèmes d'enseignement ({$table_systeme}) : " . count($id_systemes) . ' → [' . implode(',', $id_systemes) . ']';

// ── 6. Diagnostic avant génération ───────────────────────────────────────────
echo '<div style="font-family:monospace;font-size:13px;margin:8px 0;">';

foreach ($errors as $e) {
    echo '<p style="color:#c00;font-weight:bold;margin:2px 0;">⚠ ' . htmlspecialchars($e) . '</p>';
}
foreach ($info as $i) {
    echo '<p style="color:#555;margin:2px 0;">ℹ ' . htmlspecialchars($i) . '</p>';
}

// ── 7. Arrêt propre si données manquantes ────────────────────────────────────
if (empty($id_themes) || empty($id_systemes) || empty($langues)) {
    echo '<p style="color:#c00;font-weight:bold;">✖ Génération impossible : données de configuration manquantes (voir détails ci-dessus).</p>';
    echo '</div>';
    $_SESSION['langue'] = $_langue_session_original;
    return;
}

// ── 8. Diagnostic DICO_TYPE_THEME (pré-génération) ───────────────────────────
// Vérifier les LIBELLE réels pour s'assurer que le switch les reconnaîtra.
$valid_types = ['Formulaire','Grille','Grille_eff_1','Grille_eff_2','Grille_eff_3','Mat_Grille','Matrice'];
$dtt_check = $db_dico->GetAll("SELECT ID_TYPE_THEME, LIBELLE, CODE_LANGUE FROM DICO_TYPE_THEME WHERE CODE_LANGUE='fr'");
if (is_array($dtt_check) && count($dtt_check) > 0) {
    echo '<p style="color:#555;margin:2px 0;">ℹ DICO_TYPE_THEME (fr) : ' . count($dtt_check) . ' type(s)</p>';
    $bad = [];
    foreach ($dtt_check as $dtt) {
        $lib = isset($dtt['LIBELLE']) ? $dtt['LIBELLE'] : reset($dtt);
        $lib_clean = trim($lib);
        // Supprimer les espaces insécables et caractères invisibles non-ASCII
        $lib_clean = preg_replace('/[\xc2\xa0\xc2\x80-\xc2\xbf\xe2\x80\x8b\r\n\t]+/u', '', $lib_clean);
        if (!in_array($lib_clean, $valid_types)) {
            $bad[] = htmlspecialchars(bin2hex($lib)) . ' (' . htmlspecialchars($lib_clean) . ')';
        }
    }
    if (!empty($bad)) {
        echo '<p style="color:#c00;margin:2px 0;">⚠ LIBELLE(s) non reconnus par le switch (hex→valeur) : ' . implode(', ', $bad) . '</p>';
        $errors[] = 'LIBELLE invalides dans DICO_TYPE_THEME';
    }
} else {
    echo '<p style="color:#c00;margin:2px 0;">⚠ DICO_TYPE_THEME vide ou inaccessible pour CODE_LANGUE=\'fr\'</p>';
}

// ── 9. Génération avec output buffering pour capturer "Attention! Dico" ───────
echo '<p style="color:#555;margin:4px 0;">⚙ Lancement de la génération...</p>';
echo '</div>';

ob_start();
$generation_ok = true;
try {
    $form = new frame($id_themes, $langues, $id_systemes, '', '');
} catch (Exception $ex) {
    $generation_ok = false;
    echo '<p style="color:#c00;font-weight:bold;">✖ Exception frame : ' . htmlspecialchars($ex->getMessage()) . '</p>';
}
$output_frame = ob_get_clean();

// Détecter les "Attention! Dico" dans la sortie
$nb_attention = substr_count($output_frame, 'Attention! Dico');
$nb_h2 = substr_count($output_frame, '<H2>');

// Afficher la sortie brute (elle contient déjà <H1>/<H2>/<H3> de suivi)
echo $output_frame;

if ($nb_attention > 0) {
    echo '<p style="color:#c00;font-weight:bold;margin:4px 0;">⚠ ' . $nb_attention . ' cas "Attention! Dico" détectés — LIBELLE non reconnu par le switch dans frame.class.php.</p>';
    echo '<p style="color:#888;margin:2px 0;font-size:12px;">Valeurs LIBELLE attendues dans le switch : ' . implode(', ', $valid_types) . '</p>';
}

// Mobile
if ($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) {
    ob_start();
    try {
        $form_mobile = new frame_mobile($id_themes, $langues, $id_systemes, '', '');
    } catch (Exception $ex) {
        echo '<p style="color:#c00;">✖ Exception frame_mobile : ' . htmlspecialchars($ex->getMessage()) . '</p>';
    }
    echo ob_get_clean();
}

// ── 10. Restauration langue + message final ───────────────────────────────────
$_SESSION['langue'] = $_langue_session_original;
unset($_langue_session_original);

echo '<div style="font-family:monospace;font-size:13px;margin:8px 0;">';
if ($generation_ok && $nb_attention === 0) {
    echo '<p style="color:green;font-weight:bold;">✔ Génération des frames terminée avec succès (' . count($id_themes) . ' thèmes × ' . count($id_systemes) . ' systèmes × ' . count($langues) . ' langue(s)).</p>';
    if ($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) {
        echo '<p style="color:green;">✔ Génération mobile terminée.</p>';
    }
} else {
    echo '<p style="color:#c00;font-weight:bold;">⚠ Génération terminée avec erreurs — vérifiez les messages ci-dessus.</p>';
}
echo '<p style="color:#777;font-size:11px;">Session langue restaurée : ' . htmlspecialchars($_SESSION['langue']) . '</p>';
echo '</div>';
?>
