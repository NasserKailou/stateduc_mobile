<?php
/**
 * app_fie/config/config.php
 * Configuration centrale de l'application FIE (Fichier Informatisé des Élèves)
 * Système d'Information de Gestion de l'Éducation — Burundi
 *
 * @author   Projet FIE / SIGE Burundi
 * @version  1.1.0
 * @date     2026-08-15
 *
 * CORRECTIONS PHASE 1 :
 *   - Ajout constantes MAX_LOGIN_ATTEMPTS et LOGIN_LOCKOUT_SECONDS (manquantes → fatal dans AuthController)
 *   - BASE_PATH désormais défini ici si absent (évite fatal error si config.php chargé directement)
 *   - startSession() déplacé vers SecurityHelper::startSession() — session_start() supprimé d'ici
 *     (évite doublon avec l'appel dans public/index.php)
 *   - FIE_BASE_URL corrigé : utilise BASE_URL si défini (évite duplication de logique)
 */

// ─── Sécurité : garantir que BASE_PATH est défini ────────────────────────────
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('FIE_ROOT')) {
    define('FIE_ROOT', dirname(__DIR__));
}

// ─── Environnement ──────────────────────────────────────────────────────────
define('FIE_ENV',       getenv('FIE_ENV') ?: 'development'); // 'development' | 'production'
define('FIE_VERSION',   '1.1.0');
define('FIE_APP_NAME',  'FIE — Fichier Informatisé des Élèves');
define('FIE_APP_SHORT', 'FIE');
define('FIE_COUNTRY',   'Burundi');
define('FIE_LANG',      'fr');

// ─── Base de données MySQL (FIE local) ──────────────────────────────────────
define('DB_HOST',    getenv('FIE_DB_HOST')    ?: 'localhost');
define('DB_PORT',    (int)(getenv('FIE_DB_PORT') ?: 3306));
define('DB_NAME',    getenv('FIE_DB_NAME')    ?: 'fie_burundi');
define('DB_USER',    getenv('FIE_DB_USER')    ?: 'root');   // XAMPP default
define('DB_PASS',    getenv('FIE_DB_PASS')    ?: '');       // XAMPP default (empty)
define('DB_CHARSET', 'utf8mb4');

// ─── API StatEduc (source de vérité des établissements) ─────────────────────
define('STATEDUC_API_BASE_URL',  getenv('STATEDUC_API_URL')   ?: 'http://stateduc.ins.bi/');
define('STATEDUC_API_TOKEN',     getenv('STATEDUC_API_TOKEN') ?: 'CHANGE_ME_IN_ENV');
define('STATEDUC_API_TIMEOUT',   30);
define('STATEDUC_SYNC_PAGE_SIZE', 500);

// ─── Chemins absolus ────────────────────────────────────────────────────────
define('FIE_CONFIG_PATH',   FIE_ROOT . '/config/');
define('FIE_APP_PATH',      FIE_ROOT . '/app/');
define('FIE_MODELS_PATH',   FIE_ROOT . '/app/models/');
define('FIE_CTRL_PATH',     FIE_ROOT . '/app/controllers/');
define('FIE_VIEWS_PATH',    FIE_ROOT . '/app/views/');
define('FIE_SERVICES_PATH', FIE_ROOT . '/services/');
define('FIE_API_PATH',      FIE_ROOT . '/api/');
define('FIE_LOGS_PATH',     FIE_ROOT . '/logs/');
define('FIE_CACHE_PATH',    FIE_ROOT . '/cache/');
define('FIE_DOCS_PATH',     FIE_ROOT . '/docs/');
define('FIE_PUBLIC_PATH',   FIE_ROOT . '/public/');

// ─── URL de base ────────────────────────────────────────────────────────────
// BASE_URL est défini dans public/index.php (front controller).
// FIE_BASE_URL en est déduit (garantit cohérence).
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', rtrim($protocol . '://' . $host . dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/'));
}
if (!defined('FIE_BASE_URL')) {
    define('FIE_BASE_URL', BASE_URL . '/');
}

// ─── Sécurité sessions ──────────────────────────────────────────────────────
define('FIE_SESSION_LIFETIME',  3600);        // 1 heure
define('FIE_SESSION_NAME',      'FIE_SESS');
define('FIE_CSRF_TOKEN_NAME',   '_csrf_token');
define('FIE_BCRYPT_COST',       12);

// ─── Authentification brute-force ───────────────────────────────────────────
// CORRECTION : ces constantes étaient absentes → fatal error dans AuthController
define('MAX_LOGIN_ATTEMPTS',    5);           // Nombre max de tentatives avant blocage
define('LOGIN_LOCKOUT_SECONDS', 900);         // Durée de blocage : 15 minutes

// ─── IUE (Identifiant Unique de l'Élève) ────────────────────────────────────
// Format : BI-SSSS-AAAA-NNNNNN-CC
define('IUE_COUNTRY_CODE', 'BI');
define('IUE_FORMAT_REGEX', '/^BI-\d{4}-\d{4}-\d{6}-\d{2}$/');

// ─── Charte chromatique du Burundi ──────────────────────────────────────────
define('COLOR_RED',   '#CE1126');
define('COLOR_WHITE', '#FFFFFF');
define('COLOR_GREEN', '#1EB53A');

// ─── Pagination ─────────────────────────────────────────────────────────────
define('DEFAULT_PAGE_SIZE', 25);
define('MAX_PAGE_SIZE',     200);

// ─── Upload fichiers ────────────────────────────────────────────────────────
define('UPLOAD_MAX_SIZE',       5 * 1024 * 1024); // 5 Mo
define('UPLOAD_ALLOWED_TYPES',  ['image/jpeg', 'image/png', 'image/gif']);

// ─── Modes de débogage ──────────────────────────────────────────────────────
define('FIE_DEBUG', FIE_ENV === 'development');
if (FIE_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');
ini_set('error_log', FIE_LOGS_PATH . 'php_errors.log');

// ─── Autoloader minimal ─────────────────────────────────────────────────────
spl_autoload_register(function (string $className): void {
    // Supporte namespaces App\... et classes simples
    $bare = ltrim(strrchr($className, '\\') ?: $className, '\\');
    if ($bare === '') $bare = $className;

    $map = [
        'Database'              => FIE_CONFIG_PATH   . 'Database.php',
        'Router'                => FIE_CONFIG_PATH   . 'Router.php',
        'EleveModel'            => FIE_MODELS_PATH   . 'EleveModel.php',
        'InscriptionModel'      => FIE_MODELS_PATH   . 'InscriptionModel.php',
        'EtablissementModel'    => FIE_MODELS_PATH   . 'EtablissementModel.php',
        'IueGenerator'          => FIE_SERVICES_PATH . 'IueGenerator.php',
        'StatEducApiClient'     => FIE_API_PATH      . 'stateduc/StatEducApiClient.php',
        'SyncService'           => FIE_SERVICES_PATH . 'SyncService.php',
        'AggregateService'      => FIE_SERVICES_PATH . 'AggregateService.php',
        'SecurityHelper'        => FIE_SERVICES_PATH . 'SecurityHelper.php',
        'Logger'                => FIE_SERVICES_PATH . 'Logger.php',
        // Controllers
        'AuthController'        => FIE_CTRL_PATH . 'AuthController.php',
        'InscriptionController' => FIE_CTRL_PATH . 'InscriptionController.php',
        'AdminController'       => FIE_CTRL_PATH . 'AdminController.php',
        'DashboardController'   => FIE_CTRL_PATH . 'DashboardController.php',
        'PublicController'      => FIE_CTRL_PATH . 'PublicController.php',
        'MouvementController'   => FIE_CTRL_PATH . 'MouvementController.php',
        'ExamenController'      => FIE_CTRL_PATH . 'ExamenController.php',
        'AggregatesApiController'   => FIE_CTRL_PATH . 'AggregatesApiController.php',
        'ParametresController'      => FIE_CTRL_PATH . 'ParametresController.php',
        'EtablissementsApiController' => FIE_CTRL_PATH . 'EtablissementsApiController.php',
        'StatEducClient'            => FIE_SERVICES_PATH . 'StatEducClient.php',
    ];

    if (isset($map[$bare]) && file_exists($map[$bare])) {
        require_once $map[$bare];
    }
});
