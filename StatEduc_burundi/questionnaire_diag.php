<?php
// DIAG SESSION 28 - test direct DB
// Appel: questionnaire_diag.php?theme=9002&code_etab=61967&type_ent_stat=2

$GLOBALS['ne_pas_verifier_session'] = true;
require_once 'common.php';

header('Content-Type: text/plain; charset=utf-8');

$id_theme_sys = isset($_GET['theme']) ? intval($_GET['theme']) : 9002;
$type_ent_stat = isset($_GET['type_ent_stat']) ? intval($_GET['type_ent_stat']) : 2;
$code_etab = isset($_GET['code_etab']) ? intval($_GET['code_etab']) : 61967;

// Calculer id_theme et id_systeme depuis theme_manager
$tm_id = $theme_manager->id;
$secteur = $_SESSION['secteur'];

echo "=== DIAG SESSION 28 ===\n";
echo "theme_GET=$id_theme_sys\n";
echo "type_ent_stat=$type_ent_stat\n";
echo "theme_manager->id=$tm_id\n";
echo "secteur=$secteur\n";
echo "SESSION[type_ent_stat]=" . ($_SESSION['type_ent_stat'] ?? 'UNDEF') . "\n";
echo "\n";

// Test 1: Requete FRAME SANS filtre APPARTENANCE
$sql1 = "SELECT A.ID, B.FRAME, B.NB_LIGNES_FRAME, B.APPARTENANCE, B.ID_SYSTEME
          FROM DICO_THEME A, DICO_THEME_SYSTEME B
          WHERE A.ID=B.ID
          AND A.ID=$tm_id
          AND B.ID_SYSTEME=$secteur";
echo "=== TEST 1: FRAME sans filtre APPARTENANCE ===\n";
echo "SQL: $sql1\n";
$rs1 = $GLOBALS['conn_dico']->GetAll($sql1);
if ($rs1 === false || !is_array($rs1)) {
    echo "RESULT: ERREUR SQL ou FALSE\n";
} else {
    echo "NB ROWS: " . count($rs1) . "\n";
    foreach ($rs1 as $row) {
        echo "  -> APPARTENANCE={$row['APPARTENANCE']} FRAME={$row['FRAME']} NB_LIGNES={$row['NB_LIGNES_FRAME']}\n";
    }
}
echo "\n";

// Test 2: Requete FRAME AVEC filtre APPARTENANCE=type_ent_stat
$sql2 = "SELECT A.ID, B.FRAME, B.NB_LIGNES_FRAME, B.APPARTENANCE, B.ID_SYSTEME
          FROM DICO_THEME A, DICO_THEME_SYSTEME B
          WHERE A.ID=B.ID
          AND A.ID=$tm_id
          AND B.ID_SYSTEME=$secteur
          AND B.APPARTENANCE=$type_ent_stat";
echo "=== TEST 2: FRAME AVEC filtre APPARTENANCE=$type_ent_stat ===\n";
echo "SQL: $sql2\n";
$rs2 = $GLOBALS['conn_dico']->GetAll($sql2);
if ($rs2 === false || !is_array($rs2)) {
    echo "RESULT: ERREUR SQL ou FALSE\n";
} else {
    echo "NB ROWS: " . count($rs2) . "\n";
    foreach ($rs2 as $row) {
        echo "  -> APPARTENANCE={$row['APPARTENANCE']} FRAME={$row['FRAME']} NB_LIGNES={$row['NB_LIGNES_FRAME']}\n";
    }
}
echo "\n";

// Test 3: sqltableliee - TABLE_MERE
$sql3 = "SELECT DISTINCT DICO_ZONE.TABLE_MERE, DICO_ZONE.PRIORITE
          FROM DICO_ZONE, DICO_ZONE_SYSTEME
          WHERE DICO_ZONE.ID_THEME=$tm_id
          AND DICO_ZONE.TABLE_MERE IS NOT NULL
          AND DICO_ZONE_SYSTEME.ID_ZONE=DICO_ZONE.ID_ZONE
          AND DICO_ZONE_SYSTEME.ACTIVER=1
          AND DICO_ZONE_SYSTEME.ID_SYSTEME=$secteur
          ORDER BY DICO_ZONE.PRIORITE";
echo "=== TEST 3: TABLE_MERE (sqltableliee) ===\n";
echo "SQL: $sql3\n";
$rs3 = $GLOBALS['conn_dico']->GetAll($sql3);
if ($rs3 === false || !is_array($rs3)) {
    echo "RESULT: ERREUR ou FALSE\n";
} else {
    echo "NB ROWS: " . count($rs3) . "\n";
    foreach ($rs3 as $row) {
        echo "  -> TABLE_MERE={$row['TABLE_MERE']} PRIORITE={$row['PRIORITE']}\n";
    }
}
echo "\n";

// Test 4: DICO_THEME_SYSTEME - toutes les entrees pour ID=tm_id
$sql4 = "SELECT * FROM DICO_THEME_SYSTEME WHERE ID=" . $tm_id;
echo "=== TEST 4: DICO_THEME_SYSTEME WHERE ID=$tm_id ===\n";
echo "SQL: $sql4\n";
$rs4 = $GLOBALS['conn_dico']->GetAll($sql4);
if ($rs4 === false || !is_array($rs4)) {
    echo "RESULT: ERREUR ou FALSE\n";
} else {
    echo "NB ROWS: " . count($rs4) . "\n";
    foreach ($rs4 as $row) {
        echo "  -> " . json_encode($row) . "\n";
    }
}
echo "\n";

// Test 5: Verif OPcache
echo "=== TEST 5: OPcache status ===\n";
if (function_exists('opcache_get_status')) {
    $oc = opcache_get_status(false);
    echo "opcache enabled: " . ($oc['opcache_enabled'] ? 'YES' : 'NO') . "\n";
    echo "opcache_statistics: " . json_encode($oc['opcache_statistics'] ?? []) . "\n";
} else {
    echo "opcache_get_status: NOT AVAILABLE\n";
}
echo "\n";

// Test 6: Verif que notre code modifie est bien charge
echo "=== TEST 6: Verification version grille.class.php chargee ===\n";
$grille_file = $GLOBALS['SISED_PATH_CLS'] . 'metier/grille.class.php';
echo "Path: $grille_file\n";
echo "File exists: " . (file_exists($grille_file) ? 'YES' : 'NO') . "\n";
if (file_exists($grille_file)) {
    $gf = file_get_contents($grille_file);
    echo "Contains DIAG_S28_TOP: " . (strpos($gf, 'DIAG_S28_TOP') !== false ? 'YES' : 'NO') . "\n";
    echo "File size: " . strlen($gf) . " bytes\n";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($grille_file)) . "\n";
}
echo "\n";

echo "=== FIN DIAG ===\n";
?>
