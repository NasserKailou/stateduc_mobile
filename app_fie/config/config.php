<?php
/**
 * app_fie/config/config.php
 * Configuration centrale de l'application FIE (Fichier Informatisé des Élèves)
 * Système d'Information de Gestion de l'Éducation — Burundi
 *
 * @author   Projet FIE / SIGE Burundi
 * @version  1.0.0
 * @date     2026-08-11
 */

// ─── Sécurité : interdire l'accès direct ───────────────────────────────────
if (!defined('FIE_ROOT')) {
    define('FIE_ROOT', dirname(__DIR__));
}

// ─── Environnement ──────────────────────────────────────────────────────────
define('FIE_ENV', getenv('FIE_ENV') ?: 'development'); // 'development' | 'production'
define('FIE_VERSION', '1.0.0');
define('FIE_APP_NAME', 'FIE — Fichier Informatisé des Élèves');
define('FIE_APP_SHORT', 'FIE');
define('FIE_COUNTRY', 'Burundi');
define('FIE_LANG', 'fr');

// ─── Base de données MySQL (FIE local) ──────────────────────────────────────
define('DB_HOST',     getenv('FIE_DB_HOST')     ?: 'localhost');
define('DB_PORT',     (int)(getenv('FIE_DB_PORT')?: 3306));
define('DB_NAME',     getenv('FIE_DB_NAME')     ?: 'fie_burundi');
define('DB_USER',     getenv('FIE_DB_USER')     ?: 'fie_user');
define('DB_PASS',     getenv('FIE_DB_PASS')     ?: '');
define('DB_CHARSET',  'utf8mb4');

// ─── API StatEduc (source de vérité des établissements) ─────────────────────
// URL de base du service web StatEduc exposant le référentiel établissements.
// En développement : utiliser l'URL interne. En production : URL externe HTTPS.
define('STATEDUC_API_BASE_URL', getenv('STATEDUC_API_URL') ?: 'http://stateduc.ins.bi/');
define('STATEDUC_API_TOKEN',    getenv('STATEDUC_API_TOKEN') ?: 'CHANGE_ME_IN_ENV');
define('STATEDUC_API_TIMEOUT',  30);   // secondes
define('STATEDUC_SYNC_PAGE_SIZE', 500); // nb établissements par page

// ─── Chemins ────────────────────────────────────────────────────────────────
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

// ─── URL de base de l'application FIE ───────────────────────────────────────
// Ajuster selon le vhost Apache / Nginx en production.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('FIE_BASE_URL', $protocol . '://' . $host . '/app_fie/');

// ─── Sécurité sessions ──────────────────────────────────────────────────────
define('FIE_SESSION_LIFETIME', 3600);        // 1 heure
define('FIE_SESSION_NAME',     'FIE_SESS');
define('FIE_CSRF_TOKEN_NAME',  '_csrf_token');
define('FIE_BCRYPT_COST',      12);

// ─── IUE (Identifiant Unique de l'Élève) ────────────────────────────────────
// Format : BI-SSSS-AAAA-NNNNNN-CC
//   BI    = code pays ISO (2 lettres)
//   SSSS  = code sous-secteur (4 chiffres)
//   AAAA  = année de première inscription (4 chiffres)
//   NNNNNN = séquence unique par année+secteur (6 chiffres, zéro-padded)
//   CC    = chiffres de contrôle Luhn (2 chiffres)
define('IUE_COUNTRY_CODE', 'BI');
define('IUE_FORMAT_REGEX', '/^BI-\d{4}-\d{4}-\d{6}-\d{2}$/');

// ─── Charte chromatique du Burundi ──────────────────────────────────────────
define('COLOR_RED',   '#CE1126');   // Rouge du drapeau
define('COLOR_WHITE', '#FFFFFF');   // Blanc
define('COLOR_GREEN', '#1EB53A');   // Vert du drapeau

// ─── Pagination ─────────────────────────────────────────────────────────────
define('DEFAULT_PAGE_SIZE', 25);
define('MAX_PAGE_SIZE',     200);

// ─── Upload fichiers ────────────────────────────────────────────────────────
define('UPLOAD_MAX_SIZE',   5 * 1024 * 1024); // 5 Mo
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

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
    $map = [
        'Database'              => FIE_CONFIG_PATH . 'Database.php',
        'Router'                => FIE_CONFIG_PATH . 'Router.php',
        'EleveModel'            => FIE_MODELS_PATH . 'EleveModel.php',
        'InscriptionModel'      => FIE_MODELS_PATH . 'InscriptionModel.php',
        'EtablissementModel'    => FIE_MODELS_PATH . 'EtablissementModel.php',
        'IueGenerator'          => FIE_SERVICES_PATH . 'IueGenerator.php',
        'StatEducApiClient'     => FIE_API_PATH    . 'stateduc/StatEducApiClient.php',
        'SyncService'           => FIE_SERVICES_PATH . 'SyncService.php',
        'AggregateService'      => FIE_SERVICES_PATH . 'AggregateService.php',
        'SecurityHelper'        => FIE_SERVICES_PATH . 'SecurityHelper.php',
        'Logger'                => FIE_SERVICES_PATH . 'Logger.php',
    ];
    if (isset($map[$className]) && file_exists($map[$className])) {
        require_once $map[$className];
    }
});

// ─── Session sécurisée ──────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(FIE_SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => FIE_SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => !FIE_DEBUG,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    // Régénération périodique de l'ID de session (anti-fixation)
    if (!isset($_SESSION['_initiated'])) {
        session_regenerate_id(true);
        $_SESSION['_initiated'] = true;
    }
}
