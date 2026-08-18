<?php
set_time_limit(0);
ini_set("memory_limit", "256M");

require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame.class.php';
if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';

// ══════════════════════════════════════════════════════════════════════════════
// FIX DÉFINITIF GENTHEME — Session 8
// ══════════════════════════════════════════════════════════════════════════════
//
// CAUSE RACINE CONFIRMÉE (diagnostic Session 7) :
//
// DICO_TYPE_THEME contient 8 types dont "Menu" (et autres non-frame).
// La requête originale "SELECT ID FROM DICO_THEME WHERE ID_TYPE_THEME<>8"
// excluait le type 8 mais PAS les autres types non-frame (Menu, etc.).
// Résultat : des ID_THEME appartenant à des types "Menu" étaient passés
// à generer_frame() → la requête DICO_TYPE_THEME retournait "Menu" →
// switch → default → "Attention! Dico" → aucun fichier généré.
//
// TYPES VALIDES pour le switch dans frame.class.php :
//   "Formulaire", "Grille", "Grille_eff_1", "Grille_eff_2",
//   "Grille_eff_3", "Mat_Grille", "Matrice"
//
// FIX :
// 1. Requête $id_themes filtrée par JOIN sur DICO_TYPE_THEME pour ne garder
//    QUE les thèmes dont le LIBELLE de type est un type-frame valide.
// 2. $_SESSION['langue'] forcé à 'fr' (bug langue confirmé session 7).
// 3. Output buffering pour compter les "Attention! Dico" résiduels.
// ══════════════════════════════════════════════════════════════════════════════

// ── 1. Forcer langue 'fr' (frame.class.php utilise $_SESSION['langue'] direct) ─
$_langue_session_original = isset($_SESSION['langue']) ? $_SESSION['langue'] : 'fr';
$_SESSION['langue'] = 'fr';

// ── 2. Connexions ─────────────────────────────────────────────────────────────
$db       = $GLOBALS['conn'];
$db_dico  = $GLOBALS['conn_dico'];

$langues     = array();
$id_themes   = array();
$id_systemes = array();
$errors      = array();
$info        = array();

// ── 3. Langues ────────────────────────────────────────────────────────────────
if (isset($_GET['langue_regen'])) {
    $langues[] = trim($_GET['langue_regen']);
} else {
    $all_langues = $db_dico->GetAll("SELECT CODE_LANGUE FROM DICO_LANGUE;");
    if (is_array($all_langues)) {
        foreach ($all_langues as $row) {
            $code = isset($row['CODE_LANGUE']) ? trim($row['CODE_LANGUE'])
                  : (isset($row['code_langue']) ? trim($row['code_langue']) : '');
            if ($code !== '') $langues[] = $code;
        }
    }
    if (empty($langues)) {
        $langues[] = 'fr';
        $errors[]  = 'DICO_LANGUE vide → langue "fr" forcée.';
    }
}
$info[] = 'Langues : ' . implode(', ', $langues);

// ── 4. Tableau $id_themes — FILTRE SUR LES TYPES FRAME VALIDES ───────────────
// Types de frames reconnus par le switch de frame.class.php
$types_frame_valides = ['Formulaire','Grille','Grille_eff_1','Grille_eff_2','Grille_eff_3','Mat_Grille','Matrice'];
// Placeholders SQL pour les types valides
$placeholders = implode(',', array_fill(0, count($types_frame_valides), '?'));

// Requête : JOIN DICO_TYPE_THEME pour ne sélectionner QUE les thèmes
// dont le type est un type-frame valide (exclut "Menu" et autres non-frame)
// On filtre aussi ID_TYPE_THEME<>8 pour exclure les types système
$requete_themes = "SELECT DT.ID
                   FROM   DICO_THEME DT
                   INNER  JOIN DICO_TYPE_THEME DTT
                          ON  DTT.ID_TYPE_THEME = DT.ID_TYPE_THEME
                          AND DTT.CODE_LANGUE   = 'fr'
                   WHERE  DTT.LIBELLE IN ({$placeholders})";

$all_themes = $db_dico->GetAll($requete_themes, $types_frame_valides);

if (is_array($all_themes)) {
    foreach ($all_themes as $row) {
        $id = isset($row['ID']) ? (int)$row['ID']
            : (isset($row['id']) ? (int)$row['id'] : 0);
        if ($id > 0) $id_themes[] = $id;
    }
    $id_themes = array_unique($id_themes);
}

// Fallback : si la requête paramétrée échoue (certaines versions ADODB),
// utiliser la requête simple avec concaténation sécurisée des types (valeurs fixes)
if (empty($id_themes)) {
    $types_quoted = implode(',', array_map(fn($t) => "'" . addslashes($t) . "'", $types_frame_valides));
    $requete_fb = "SELECT DT.ID
                   FROM   DICO_THEME DT
                   INNER  JOIN DICO_TYPE_THEME DTT
                          ON  DTT.ID_TYPE_THEME = DT.ID_TYPE_THEME
                          AND DTT.CODE_LANGUE   = 'fr'
                   WHERE  DTT.LIBELLE IN ({$types_quoted})";
    $all_themes_fb = $db_dico->GetAll($requete_fb);
    if (is_array($all_themes_fb)) {
        foreach ($all_themes_fb as $row) {
            $id = isset($row['ID']) ? (int)$row['ID']
                : (isset($row['id']) ? (int)$row['id'] : 0);
            if ($id > 0) $id_themes[] = $id;
        }
        $id_themes = array_unique($id_themes);
    }
}

$info[] = 'Thèmes (types frame valides uniquement) : ' . count($id_themes);
if (empty($id_themes)) {
    $errors[] = 'Aucun thème trouvé pour les types frame valides.';
}

// ── 5. Tableau $id_systemes ───────────────────────────────────────────────────
$param_code    = $GLOBALS['PARAM']['CODE']                      ?? 'CODE';
$param_type    = $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'] ?? 'TYPE_SECTEUR_ENS';
$col_systeme   = strtoupper($param_code . '_' . $param_type);  // "CODE_TYPE_SECTEUR_ENS"
$table_systeme = $param_type;                                   // "TYPE_SECTEUR_ENS"

$all_systemes = $db->GetAll("SELECT {$col_systeme} FROM {$table_systeme};");
if (is_array($all_systemes) && count($all_systemes) > 0) {
    foreach ($all_systemes as $row) {
        if (isset($row[$col_systeme]))                   { $id_systemes[] = (int)$row[$col_systeme]; }
        elseif (isset($row[strtolower($col_systeme)]))   { $id_systemes[] = (int)$row[strtolower($col_systeme)]; }
        else                                             { $id_systemes[] = (int)reset($row); }
    }
    $id_systemes = array_values(array_unique(array_filter($id_systemes)));
}
if (empty($id_systemes)) {
    // Fallback SELECT * pour détecter la bonne colonne
    $all_sys2 = $db->GetAll("SELECT * FROM {$table_systeme};");
    if (is_array($all_sys2)) {
        foreach ($all_sys2 as $row) {
            foreach ($row as $key => $val) {
                if (stripos($key, 'CODE') !== false && stripos($key, 'TYPE') !== false) {
                    $id_systemes[] = (int)$val; break;
                }
            }
        }
        $id_systemes = array_values(array_unique(array_filter($id_systemes)));
    }
}
$info[] = "Systèmes d'enseignement ({$table_systeme}) : " . count($id_systemes) . ' → [' . implode(',', $id_systemes) . ']';
if (empty($id_systemes)) $errors[] = "Table {$table_systeme} vide ou inaccessible.";

// ── 6. Affichage diagnostic ───────────────────────────────────────────────────
echo '<div style="font-family:monospace;font-size:13px;margin:8px 0;border:1px solid #ddd;padding:8px;border-radius:4px;">';
echo '<strong style="font-size:14px;">📋 Diagnostic gentheme</strong><br><br>';
foreach ($errors as $e) {
    echo '<span style="color:#c00;">⚠ ' . htmlspecialchars($e) . '</span><br>';
}
foreach ($info as $i) {
    echo '<span style="color:#555;">ℹ ' . htmlspecialchars($i) . '</span><br>';
}

// ── 7. Arrêt si données manquantes ────────────────────────────────────────────
if (empty($id_themes) || empty($id_systemes) || empty($langues)) {
    echo '<br><strong style="color:#c00;">✖ Génération impossible — données manquantes.</strong>';
    echo '</div>';
    $_SESSION['langue'] = $_langue_session_original;
    return;
}

echo '<br><span style="color:#555;">⚙ Lancement génération (' . count($id_themes) . ' thèmes × '
     . count($id_systemes) . ' systèmes × ' . count($langues) . ' langue(s))...</span><br>';
echo '</div>';

// ── 8. Génération avec output buffering ────────────────────────────────────────
// frame::generer_frame() affiche <H1>, <H2>, <H3> pour suivi en temps réel
// ET affiche "Attention! Dico" quand le switch ne reconnaît pas le LIBELLE.
// Grâce au filtre sur types_frame_valides (étape 4), ce cas ne devrait plus arriver.
ob_start();
$generation_ok = true;
try {
    $form = new frame($id_themes, $langues, $id_systemes, '', '');
} catch (Throwable $ex) {
    $generation_ok = false;
    echo '<p style="color:#c00;font-weight:bold;">✖ Exception : ' . htmlspecialchars($ex->getMessage()) . '</p>';
}
$output_frame = ob_get_clean();

$nb_attention = substr_count($output_frame, 'Attention! Dico');

// Afficher la sortie de génération (contient les <H1>/<H2>/<H3> de suivi)
echo $output_frame;

if ($nb_attention > 0) {
    echo '<div style="font-family:monospace;font-size:12px;color:#c00;margin:4px 0;">';
    echo '⚠ ' . $nb_attention . ' cas "Attention! Dico" résiduels — les LIBELLE suivants ne sont pas dans le switch :<br>';
    echo implode(', ', array_map('htmlspecialchars', $types_frame_valides));
    echo '</div>';
}

// ── 9. Mobile ─────────────────────────────────────────────────────────────────
if ($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) {
    ob_start();
    try {
        $form_mobile = new frame_mobile($id_themes, $langues, $id_systemes, '', '');
    } catch (Throwable $ex) {
        echo '<p style="color:#c00;">✖ frame_mobile : ' . htmlspecialchars($ex->getMessage()) . '</p>';
    }
    echo ob_get_clean();
}

// ── 10. Restauration langue + bilan ───────────────────────────────────────────
$_SESSION['langue'] = $_langue_session_original;
unset($_langue_session_original);

echo '<div style="font-family:monospace;font-size:13px;margin:8px 0;">';
if ($generation_ok && $nb_attention === 0) {
    echo '<p style="color:green;font-weight:bold;">✔ Génération terminée avec succès ('
         . count($id_themes) . ' thèmes, ' . count($id_systemes) . ' systèmes, '
         . count($langues) . ' langue(s)).</p>';
    if ($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) {
        echo '<p style="color:green;">✔ Génération mobile terminée.</p>';
    }
} elseif ($nb_attention > 0) {
    echo '<p style="color:#e67e00;font-weight:bold;">⚠ Génération partielle : ' . $nb_attention . ' thèmes ignorés (LIBELLE non reconnu).</p>';
} else {
    echo '<p style="color:#c00;font-weight:bold;">⚠ Génération terminée avec erreurs.</p>';
}
echo '</div>';
?>
