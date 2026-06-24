<?php
// DIAG SESSION 28 - test direct DB avec session valide
// APPEL DEPUIS UNE SESSION ACTIVE (apres connexion) :
// questionnaire_diag.php?theme=9002&code_etab=61967&type_ent_stat=2

// On injecte type_ent_stat dans GET avant common.php
// pour eviter l'ecrasement par set_tab_session('entite_stat')
$GLOBALS['lancer_theme_manager'] = true;
$GLOBALS['lancer_theme_manager_classe'] = false;
require_once 'common.php';

header('Content-Type: text/plain; charset=utf-8');

$type_ent_stat = isset($_GET['type_ent_stat']) ? intval($_GET['type_ent_stat']) : 2;

// theme_manager->id: apres common.php avec type_ent_stat=2 dans GET, doit etre 900
$tm_id   = $theme_manager->id;
$secteur = $_SESSION['secteur'];

echo "=== DIAG SESSION 28 ===\n";
echo "theme_GET=" . ($_GET['theme'] ?? '?') . "\n";
echo "type_ent_stat_GET=$type_ent_stat\n";
echo "SESSION[type_ent_stat]=" . ($_SESSION['type_ent_stat'] ?? 'UNDEF') . "\n";
echo "theme_manager->id=$tm_id\n";
echo "secteur=$secteur\n";
echo "SESSION[code_etab]=" . ($_SESSION['code_etab'] ?? 'UNDEF') . "\n";
echo "\n";

// Si theme_manager->id est vide, on le force manuellement
if (empty($tm_id)) {
    // theme=9002, secteur=2 -> id=900 (les 3 premiers chiffres)
    // On calcule depuis theme_manager->list
    echo "theme_manager->id vide! Tentative recherche manuelle...\n";
    if (isset($theme_manager->list) && is_array($theme_manager->list)) {
        foreach ($theme_manager->list as $t) {
            echo "  list entry: ID_THEME_SYSTEME=" . ($t['ID_THEME_SYSTEME'] ?? '?') . " ID=" . ($t['ID'] ?? '?') . " APPARTENANCE=" . ($t['APPARTENANCE'] ?? '?') . "\n";
        }
    } else {
        echo "theme_manager->list non disponible\n";
    }
    // Forcer tm_id=900 pour les tests
    $tm_id = 900;
    echo "tm_id force a 900 pour les tests\n\n";
}

// Test 1: FRAME sans filtre APPARTENANCE
$sql1 = "SELECT A.ID, B.FRAME, B.NB_LIGNES_FRAME, B.APPARTENANCE, B.ID_SYSTEME
          FROM DICO_THEME A, DICO_THEME_SYSTEME B
          WHERE A.ID=B.ID AND A.ID=$tm_id AND B.ID_SYSTEME=$secteur";
echo "=== TEST 1: FRAME sans filtre APPARTENANCE ===\n";
echo "SQL: $sql1\n";
$rs1 = $GLOBALS['conn_dico']->GetAll($sql1);
if (!is_array($rs1)) {
    echo "RESULT: ERREUR - " . $GLOBALS['conn_dico']->ErrorMsg() . "\n";
} else {
    echo "NB ROWS: " . count($rs1) . "\n";
    foreach ($rs1 as $row) {
        echo "  -> APPARTENANCE={$row['APPARTENANCE']} FRAME={$row['FRAME']} NB_LIGNES={$row['NB_LIGNES_FRAME']}\n";
    }
}
echo "\n";

// Test 2: FRAME AVEC filtre APPARTENANCE=type_ent_stat
$sql2 = "SELECT A.ID, B.FRAME, B.NB_LIGNES_FRAME, B.APPARTENANCE, B.ID_SYSTEME
          FROM DICO_THEME A, DICO_THEME_SYSTEME B
          WHERE A.ID=B.ID AND A.ID=$tm_id AND B.ID_SYSTEME=$secteur
          AND B.APPARTENANCE=$type_ent_stat";
echo "=== TEST 2: FRAME AVEC APPARTENANCE=$type_ent_stat ===\n";
echo "SQL: $sql2\n";
$rs2 = $GLOBALS['conn_dico']->GetAll($sql2);
if (!is_array($rs2)) {
    echo "RESULT: ERREUR - " . $GLOBALS['conn_dico']->ErrorMsg() . "\n";
} else {
    echo "NB ROWS: " . count($rs2) . "\n";
    foreach ($rs2 as $row) {
        echo "  -> APPARTENANCE={$row['APPARTENANCE']} FRAME={$row['FRAME']} NB_LIGNES={$row['NB_LIGNES_FRAME']}\n";
    }
}
echo "\n";

// Test 3: TABLE_MERE
$sql3 = "SELECT DISTINCT DICO_ZONE.TABLE_MERE, DICO_ZONE.PRIORITE
          FROM DICO_ZONE, DICO_ZONE_SYSTEME
          WHERE DICO_ZONE.ID_THEME=$tm_id
          AND DICO_ZONE.TABLE_MERE IS NOT NULL
          AND DICO_ZONE_SYSTEME.ID_ZONE=DICO_ZONE.ID_ZONE
          AND DICO_ZONE_SYSTEME.ACTIVER=1
          AND DICO_ZONE_SYSTEME.ID_SYSTEME=$secteur
          ORDER BY DICO_ZONE.PRIORITE";
echo "=== TEST 3: TABLE_MERE ===\n";
echo "SQL: $sql3\n";
$rs3 = $GLOBALS['conn_dico']->GetAll($sql3);
if (!is_array($rs3)) {
    echo "RESULT: ERREUR - " . $GLOBALS['conn_dico']->ErrorMsg() . "\n";
} else {
    echo "NB ROWS: " . count($rs3) . "\n";
    foreach ($rs3 as $row) {
        echo "  -> TABLE_MERE={$row['TABLE_MERE']} PRIORITE={$row['PRIORITE']}\n";
    }
}
echo "\n";

// Test 4: DICO_THEME_SYSTEME toutes entrees pour ID=tm_id (TOUS secteurs, TOUTES APPARTENANCE)
$sql4 = "SELECT ID, ID_SYSTEME, APPARTENANCE, FRAME, NB_LIGNES_FRAME FROM DICO_THEME_SYSTEME WHERE ID=$tm_id ORDER BY ID_SYSTEME, APPARTENANCE";
echo "=== TEST 4: DICO_THEME_SYSTEME toutes entrees ID=$tm_id ===\n";
echo "SQL: $sql4\n";
$rs4 = $GLOBALS['conn_dico']->GetAll($sql4);
if (!is_array($rs4)) {
    echo "RESULT: ERREUR - " . $GLOBALS['conn_dico']->ErrorMsg() . "\n";
} else {
    echo "NB ROWS: " . count($rs4) . "\n";
    foreach ($rs4 as $row) {
        echo "  -> ID_SYSTEME={$row['ID_SYSTEME']} APPARTENANCE={$row['APPARTENANCE']} FRAME={$row['FRAME']} NB_LIGNES={$row['NB_LIGNES_FRAME']}\n";
    }
}
echo "\n";

// Test 5: Verifier les donnees en base pour code_etab + annee
$code_etab = $_SESSION['code_etab'] ?? 61967;
$annee = $_SESSION['annee'] ?? '';
echo "=== TEST 5: Recherche TABLE_MERE et donnees etab ===\n";
echo "code_etab=$code_etab  annee=$annee\n";
// Lister les tables liees (Test 3 result) et pour chacune compter les lignes
if (is_array($rs3) && count($rs3) > 0) {
    foreach ($rs3 as $row) {
        $tbl = $row['TABLE_MERE'];
        // Trouver la cle etablissement - essai avec CODE_ETABLISSEMENT param
        $cle_etab = $GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
        $cle_annee = $GLOBALS['PARAM']['CODE'] . '_' . $GLOBALS['PARAM']['TYPE_ANNEE'];
        $sql_count = "SELECT COUNT(*) AS NB FROM $tbl WHERE $cle_etab=$code_etab";
        if ($annee != '') $sql_count .= " AND $cle_annee=$annee";
        echo "  Comptage $tbl: $sql_count\n";
        $res_count = $GLOBALS['conn']->GetRow($sql_count);
        if ($res_count === false) {
            echo "    -> ERREUR: " . $GLOBALS['conn']->ErrorMsg() . "\n";
        } else {
            echo "    -> NB LIGNES = " . ($res_count['NB'] ?? $res_count[0] ?? '?') . "\n";
        }
    }
} else {
    echo "  Pas de TABLE_MERE trouvee - impossible de compter\n";
}
echo "\n";

// Test 6: OPcache et version fichier
echo "=== TEST 6: OPcache + version grille.class.php ===\n";
if (function_exists('opcache_get_status')) {
    $oc = opcache_get_status(false);
    echo "opcache enabled: " . ($oc['opcache_enabled'] ? 'YES' : 'NO') . "\n";
} else {
    echo "opcache_get_status: NOT AVAILABLE (pas d'OPcache ou desactive)\n";
}
$grille_file = $GLOBALS['SISED_PATH_CLS'] . 'metier/grille.class.php';
echo "grille.class.php path: $grille_file\n";
if (file_exists($grille_file)) {
    $gf = file_get_contents($grille_file);
    echo "Contains DIAG_S28_TOP: " . (strpos($gf, 'DIAG_S28_TOP') !== false ? 'YES' : 'NO') . "\n";
    echo "File size: " . strlen($gf) . " bytes\n";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($grille_file)) . "\n";
}
echo "\n";

// Test 7: Ecriture fichier log - test droits
echo "=== TEST 7: Test droits ecriture log ===\n";
$paths_test = [
    dirname(__FILE__) . '/moblogs/diag_test.log',
    'C:/Windows/Temp/diag_stateduc_test.log',
    sys_get_temp_dir() . '/diag_stateduc_test.log',
];
foreach ($paths_test as $p) {
    $r = @file_put_contents($p, date('Y-m-d H:i:s') . " TEST\n", FILE_APPEND);
    echo "  $p -> " . ($r !== false ? "OK ($r bytes)" : "ECHEC (permission denied)") . "\n";
}
echo "sys_get_temp_dir()=" . sys_get_temp_dir() . "\n";
echo "ini error_log=" . ini_get('error_log') . "\n";
echo "\n";

// Test 8: DICO_THEME mapping - recherche ID_THEME_SYSTEME=9002
echo "=== TEST 8: DICO_THEME mapping ID_THEME_SYSTEME=9002 ===\n";
$sql8a = "SELECT ID, CODE, LIBELLE FROM DICO_THEME WHERE ID=900";
echo "SQL A: $sql8a\n";
$rs8a = $GLOBALS['conn_dico']->GetAll($sql8a);
if (!is_array($rs8a)) {
    echo "RESULT A: ERREUR - " . $GLOBALS['conn_dico']->ErrorMsg() . "\n";
} else {
    echo "NB ROWS: " . count($rs8a) . "\n";
    foreach ($rs8a as $row) {
        echo "  -> ID={$row['ID']} CODE=" . ($row['CODE'] ?? '?') . " LIBELLE=" . ($row['LIBELLE'] ?? '?') . "\n";
    }
}
// Recherche par ID_THEME_SYSTEME dans la table theme_systeme pour verifier mapping 9002->900
$sql8b = "SELECT TOP 5 ID, ID_SYSTEME, APPARTENANCE, FRAME FROM DICO_THEME_SYSTEME WHERE ID BETWEEN 895 AND 910 ORDER BY ID, ID_SYSTEME";
echo "SQL B (range 895-910): $sql8b\n";
$rs8b = $GLOBALS['conn_dico']->GetAll($sql8b);
if (!is_array($rs8b)) {
    echo "RESULT B: ERREUR - " . $GLOBALS['conn_dico']->ErrorMsg() . "\n";
} else {
    echo "NB ROWS: " . count($rs8b) . "\n";
    foreach ($rs8b as $row) {
        echo "  -> ID={$row['ID']} ID_SYSTEME={$row['ID_SYSTEME']} APPARTENANCE={$row['APPARTENANCE']} FRAME={$row['FRAME']}\n";
    }
}
echo "\n";

// Test 9: theme_manager->list - inspecter la liste des themes mobiles
echo "=== TEST 9: theme_manager->list et theme_manager2 ===\n";
echo "theme_manager class=" . get_class($theme_manager) . "\n";
echo "theme_manager->id=$tm_id\n";
// Inspecter theme_manager2 si defini
if (isset($theme_manager2)) {
    echo "theme_manager2->id=" . ($theme_manager2->id ?? 'UNDEF') . "\n";
    echo "theme_manager2->list count=" . (is_array($theme_manager2->list ?? null) ? count($theme_manager2->list) : 'N/A') . "\n";
    if (is_array($theme_manager2->list ?? null)) {
        foreach ($theme_manager2->list as $t) {
            echo "  TM2 entry: ID_THEME_SYSTEME=" . ($t['ID_THEME_SYSTEME'] ?? '?') . " ID=" . ($t['ID'] ?? '?') . " APPARTENANCE=" . ($t['APPARTENANCE'] ?? '?') . " FRAME=" . ($t['FRAME'] ?? '?') . "\n";
        }
    }
} else {
    echo "theme_manager2 NOT DEFINED\n";
}
// Inspecter theme_manager1 si defini
if (isset($theme_manager1)) {
    echo "theme_manager1->id=" . ($theme_manager1->id ?? 'UNDEF') . "\n";
} else {
    echo "theme_manager1 NOT DEFINED\n";
}
// Lister toutes variables $theme_manager* dans GLOBALS
foreach ($GLOBALS as $k => $v) {
    if (strpos($k, 'theme_manager') === 0) {
        echo "GLOBAL $k = " . (is_object($v) ? get_class($v) . " id=" . ($v->id ?? '?') : gettype($v)) . "\n";
    }
}
echo "\n";

// Test 10: session complete - dump des cles importantes
echo "=== TEST 10: SESSION dump ===\n";
$sess_keys = ['type_ent_stat','secteur','code_etab','annee','tab_entite_stat','secteur_id'];
foreach ($sess_keys as $k) {
    $val = $_SESSION[$k] ?? 'UNDEF';
    if (is_array($val)) $val = json_encode(array_slice($val, 0, 3));
    echo "  SESSION[$k]=$val\n";
}
echo "\n";

echo "=== FIN DIAG ===\n";
?>
