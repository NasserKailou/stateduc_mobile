<?php
/**
 * diag_gestionuser.php — Script de diagnostic erreur 500
 * Placer dans StatEduc_burundi/ et accéder via :
 * http://localhost:8083/StatEduc_burundi/diag_gestionuser.php
 */

// Activer TOUS les messages d'erreur
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log vers un fichier
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/diag_errors.log');

echo '<pre>';
echo "=== DIAGNOSTIC gestionuser - " . date('Y-m-d H:i:s') . " ===\n\n";

// Étape 1 : config_app.php
echo "STEP 1: config_app.php... ";
try {
    require_once 'config_app.php';
    echo "OK\n";
    echo "  SISED_PATH = $SISED_PATH\n";
    echo "  SISED_PATH_CLS = " . $GLOBALS['SISED_PATH_CLS'] . "\n";
    echo "  SISED_PATH_LIB = " . $GLOBALS['SISED_PATH_LIB'] . "\n";
} catch (Throwable $e) { echo "ERREUR: " . $e->getMessage() . "\n"; die(); }

// Étape 2 : params.php
echo "\nSTEP 2: params.php... ";
try {
    require_once 'params.php';
    echo "OK\n";
} catch (Throwable $e) { echo "ERREUR: " . $e->getMessage() . "\n"; die(); }

// Étape 3 : params_sys.php
echo "\nSTEP 3: params_sys.php... ";
try {
    require_once 'params_sys.php';
    echo "OK\n";
} catch (Throwable $e) { echo "ERREUR: " . $e->getMessage() . "\n"; die(); }

// Étape 4 : connexion.inc.php
echo "\nSTEP 4: connexion.inc.php... ";
try {
    require_once $GLOBALS['SISED_PATH_LIB'] . 'connexion.inc.php';
    echo "OK\n";
    echo "  connexion->ok = " . (isset($connexion->ok) ? ($connexion->ok ? 'true' : 'false') : 'NOT SET') . "\n";
} catch (Throwable $e) { echo "ERREUR: " . $e->getMessage() . "\n"; die(); }

// Étape 5 : autoload PhpSpreadsheet
echo "\nSTEP 5: autoload.php (PhpSpreadsheet)... ";
$autoload_path = $GLOBALS['SISED_PATH_LIB'] . 'autoload.php';
echo "\n  Path: $autoload_path\n  Exists: " . (file_exists($autoload_path) ? 'YES' : 'NO') . "\n";
if (file_exists($autoload_path)) {
    try {
        require_once $autoload_path;
        echo "  Load: OK\n";
        echo "  PhpSpreadsheet class: " . (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet') ? 'EXISTS' : 'MISSING') . "\n";
    } catch (Throwable $e) { echo "  ERREUR: " . $e->getMessage() . "\n"; }
} else {
    echo "  FICHIER MANQUANT — c'est la cause principale!\n";
}

// Étape 6 : user.class.php
echo "\nSTEP 6: user.class.php... ";
$user_class_path = $GLOBALS['SISED_PATH_CLS'] . 'metier/user.class.php';
echo "\n  Path: $user_class_path\n  Exists: " . (file_exists($user_class_path) ? 'YES' : 'NO') . "\n";
if (file_exists($user_class_path)) {
    try {
        require_once $user_class_path;
        echo "  Load: OK\n";
        echo "  Class 'user' exists: " . (class_exists('user') ? 'YES' : 'NO') . "\n";
    } catch (Throwable $e) { echo "  ERREUR: " . $e->getMessage() . "\n"; }
}

// Étape 7 : gestion_user.php (juste parse, pas exécute)
echo "\nSTEP 7: gestion_user.php — check inclus requis... ";
$gestion_path = $GLOBALS['SISED_PATH_INC'] . 'administration/gestion_user.php';
echo "\n  Path: $gestion_path\n  Exists: " . (file_exists($gestion_path) ? 'YES' : 'NO') . "\n";

// Étape 8 : PHP version
echo "\nSTEP 8: Environnement PHP\n";
echo "  PHP version: " . PHP_VERSION . "\n";
echo "  OS: " . PHP_OS . "\n";
echo "  extensions: " . implode(', ', array_filter(['pdo','pdo_mysql','pdo_sqlite','zip','gd','mbstring','xml'], 'extension_loaded')) . "\n";

echo "\n=== FIN DIAGNOSTIC ===\n";
echo '</pre>';
?>
