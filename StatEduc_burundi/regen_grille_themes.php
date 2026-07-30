<?php
/**
 * regen_grille_themes.php — Session 46-c
 * Régénère TOUS les fichiers ws_mob_*.html pour les thèmes de type "Grille" (grille_ligne).
 *
 * UTILISATION (admin uniquement, depuis le navigateur) :
 *   http://[serveur]/regen_grille_themes.php?confirm=oui
 *
 * POURQUOI ce script existe :
 *   Après un git pull, les corrections PHP de generer_frame_grille() prennent
 *   effet dans le code MAIS les fichiers ws_mob_*.html sur disque restent
 *   l'ancienne version (pré-correction). Ce script force leur régénération.
 *
 * SECURITE : requiert une session admin active (même guard que gestion_theme.php).
 *
 * @author   kailounasser@gmail.com — Session 46-c
 */

set_time_limit(0);
ini_set('memory_limit', '256M');

require_once 'common.php';

// ── Sécurité : admin uniquement ──────────────────────────────────────────────
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header('HTTP/1.0 403 Forbidden');
    die('Accès refusé. Connexion administrateur requise.');
}

// ── Guard anti-accès accidentel ──────────────────────────────────────────────
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'oui') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8">
    <title>Régénération thèmes Grille</title></head><body>
    <h2>Régénération ws_mob_*.html — Thèmes Grille</h2>
    <p>Ce script régénère tous les fichiers <code>ws_mob_*.html</code> pour les thèmes
    de type <strong>Grille</strong> (grille_ligne).</p>
    <p><strong>Action requise après chaque git pull</strong> pour que les corrections
    de <code>frame_mobile.class.php</code> prennent effet.</p>
    <p><a href="?confirm=oui" style="padding:10px 20px; background:#c00; color:#fff; text-decoration:none; border-radius:4px;">
    ▶ Lancer la régénération</a></p>
    </body></html>';
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== REGENERATION ws_mob_*.html — Themes Grille ===\n";
echo "Date : " . date('Y-m-d H:i:s') . "\n\n";

// ── OPcache : vider le cache si possible ─────────────────────────────────────
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "[OPcache] Cache vidé.\n";
} else {
    echo "[OPcache] opcache_reset() non disponible — redémarrer PHP si problème persiste.\n";
}
echo "\n";

// ── Vérification MOBILE_THEME_CONFIG ─────────────────────────────────────────
if (!$GLOBALS['PARAM']['MOBILE_THEME_CONFIG']) {
    die("[ERREUR] MOBILE_THEME_CONFIG = false dans params.php. Régénération mobile désactivée.\n");
}

// ── Charger les classes ───────────────────────────────────────────────────────
require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';

// ── Récupérer les langues ─────────────────────────────────────────────────────
$req_langues = "SELECT CODE_LANGUE FROM DICO_LANGUE;";
$all_langues = $GLOBALS['conn_dico']->GetAll($req_langues);
$langues = array();
foreach ($all_langues as $l) {
    $langues[] = $l['CODE_LANGUE'];
}
echo "Langues : " . implode(', ', $langues) . "\n";

// ── SESSION 46-d : corriger NB_LIGNES_FRAME=1 bloquant pour grille_ligne ──────
// Les thèmes de type grille_ligne (ID_TYPE_THEME=3) avaient NB_LIGNES_FRAME=1
// fixé par un bug dans gestion_theme.php (INSERT + affichage form).
// On remet à 10 (valeur par défaut raisonnable) pour tous ceux qui ont encore 1.
echo "=== Correction NB_LIGNES_FRAME pour grille_ligne (ID_TYPE_THEME=3) ===\n";
$req_fix_nlf = "UPDATE DICO_THEME_SYSTEME
    SET NB_LIGNES_FRAME = 10
    WHERE NB_LIGNES_FRAME = 1
    AND ID IN (SELECT ID FROM DICO_THEME WHERE ID_TYPE_THEME = 3)";
try {
    $GLOBALS['conn_dico']->Execute($req_fix_nlf);
    $affected = $GLOBALS['conn_dico']->Affected_Rows();
    echo "[FIX NB_LIGNES_FRAME] {$affected} ligne(s) corrigée(s) : NB_LIGNES_FRAME 1→10 pour ID_TYPE_THEME=3\n";
} catch (Exception $e) {
    echo "[WARN] Correction NB_LIGNES_FRAME échouée : " . $e->getMessage() . "\n";
}
echo "\n";

// ── Récupérer les thèmes de type Grille UNIQUEMENT ───────────────────────────
// ID_TYPE_THEME pour "Grille" = type dont le LIBELLE = 'Grille' dans DICO_TYPE_THEME
// ID_TYPE_THEME=3 = grille_ligne — inclus aussi (correction session 46-d)
// On filtre directement sur les thèmes qui ont une entrée DICO_THEME_SYSTEME.FRAME définie
// et NB_LIGNES_FRAME > 0 (= vraiment des thèmes grille avec lignes)
$req_themes_grille = "
    SELECT DISTINCT DT.ID, DT.ID_TYPE_THEME, DTT.LIBELLE AS TYPE_LIBELLE
    FROM DICO_THEME DT
    INNER JOIN DICO_TYPE_THEME DTT ON DTT.ID_TYPE_THEME = DT.ID_TYPE_THEME
    AND DTT.CODE_LANGUE = '" . $_SESSION['langue'] . "'
    WHERE DTT.LIBELLE IN ('Grille', 'Grille_ligne', 'grille', 'grille_ligne')
    ORDER BY DT.ID
";
$themes_grille = $GLOBALS['conn_dico']->GetAll($req_themes_grille);

if (!is_array($themes_grille) || count($themes_grille) === 0) {
    // Fallback : essayer avec ID_TYPE_THEME connu (2 = grille_colonne, 3 = grille_ligne)
    echo "[WARN] Requête type 'Grille' → 0 résultats. Tentative fallback ID_TYPE_THEME IN (2,3)...\n";
    $req_themes_grille = "
        SELECT DISTINCT DT.ID, DT.ID_TYPE_THEME
        FROM DICO_THEME DT
        INNER JOIN DICO_THEME_SYSTEME DTS ON DTS.ID = DT.ID
        WHERE DT.ID_TYPE_THEME IN (2, 3)
        AND DTS.NB_LIGNES_FRAME >= 1
        ORDER BY DT.ID
    ";
    $themes_grille = $GLOBALS['conn_dico']->GetAll($req_themes_grille);
}

if (!is_array($themes_grille) || count($themes_grille) === 0) {
    die("[ERREUR] Aucun thème Grille trouvé en base.\n");
}

$ids_themes = array();
foreach ($themes_grille as $t) {
    $ids_themes[] = $t['ID'];
}
echo "Thèmes Grille trouvés (" . count($ids_themes) . ") : " . implode(', ', $ids_themes) . "\n";

// ── Récupérer les systèmes ────────────────────────────────────────────────────
$req_systemes = 'SELECT ' . $GLOBALS['PARAM']['CODE'] . '_' . $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']
              . ' AS ID_SYS FROM ' . $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'] . ';';
$all_systemes = $GLOBALS['conn']->GetAll($req_systemes);
$id_systemes  = array();
foreach ($all_systemes as $s) {
    $id_systemes[] = $s['ID_SYS'];
}
echo "Systèmes : " . implode(', ', $id_systemes) . "\n\n";

// ── Vérifier les valeurs NB_LIGNES_FRAME AVANT régénération ──────────────────
echo "=== Vérification NB_LIGNES_FRAME en base ===\n";
$req_check = "SELECT DTS.ID, DTS.ID_SYSTEME, DTS.NB_LIGNES_FRAME, DTS.FRAME
              FROM DICO_THEME_SYSTEME DTS
              WHERE DTS.ID IN (" . implode(',', $ids_themes) . ")
              ORDER BY DTS.ID, DTS.ID_SYSTEME";
$check_rows = $GLOBALS['conn_dico']->GetAll($req_check);
foreach ($check_rows as $row) {
    $flag = ((int)$row['NB_LIGNES_FRAME'] > 1) ? 'OK' : 'ATTENTION NB_LIGNES_FRAME<=1';
    echo "  ID={$row['ID']} ID_SYS={$row['ID_SYSTEME']} NB_LIGNES_FRAME={$row['NB_LIGNES_FRAME']} FRAME={$row['FRAME']} → $flag\n";
}
echo "\n";

// ── Régénération ─────────────────────────────────────────────────────────────
echo "=== Lancement régénération frame_mobile ===\n";
$t_start = microtime(true);

try {
    $form_mobile = new frame_mobile($ids_themes, $langues, $id_systemes, '', '');
    $elapsed = round(microtime(true) - $t_start, 2);
    echo "\n=== SUCCÈS — Régénération terminée en {$elapsed}s ===\n";
} catch (Exception $e) {
    echo "\n=== ERREUR pendant régénération : " . $e->getMessage() . " ===\n";
}

// ── Vérifier les fichiers générés ─────────────────────────────────────────────
echo "\n=== Vérification fichiers ws_mob_*.html après régénération ===\n";
foreach ($langues as $lang) {
    $dir = $GLOBALS['SISED_PATH'] . 'questionnaire/' . $lang . '/';
    foreach ($check_rows as $row) {
        if (empty($row['FRAME'])) continue;
        $filepath = $dir . 'ws_mob_' . $row['FRAME'];
        if (file_exists($filepath)) {
            $content  = file_get_contents($filepath);
            $nb_lines = substr_count($content, "class='data_line'");
            $mtime    = date('Y-m-d H:i:s', filemtime($filepath));
            echo "  [{$lang}] ws_mob_{$row['FRAME']} → {$nb_lines} data_line(s) | modifié: {$mtime}\n";
        } else {
            echo "  [{$lang}] ws_mob_{$row['FRAME']} → FICHIER ABSENT !\n";
        }
    }
}

echo "\n=== FIN ===\n";
?>
