<?php
/**
 * app_fie/public/index.php
 * Front Controller — Point d'entrée unique de l'application FIE.
 *
 * Apache/XAMPP : placer un fichier .htaccess dans public/ avec :
 *   RewriteEngine On
 *   RewriteCond %{REQUEST_FILENAME} !-f
 *   RewriteCond %{REQUEST_FILENAME} !-d
 *   RewriteRule ^ index.php [L]
 *
 * CORRECTIONS PHASE 1 :
 *   - define('BASE_PATH') avant require config.php (évite redéfinition conflit)
 *   - define('BASE_URL') : calcul correct en sous-dossier XAMPP (ex: /app_fie/public → /app_fie)
 *   - SecurityHelper::startSession() appelé ici (unique appel)
 *   - Suppression du use App\... (pas de namespaces dans ce projet)
 */

declare(strict_types=1);

// ── 1. Chemins absolus ───────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));  // /chemin/vers/app_fie

// Calcule BASE_URL (ex: http://localhost/app_fie ou https://fie.bi)
// SCRIPT_NAME = /app_fie/public/index.php → dirname = /app_fie/public → dirname = /app_fie
$_base_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_base_host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Remonte d'un niveau pour sortir de /public
$_base_dir    = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php'));
$_base_dir    = rtrim($_base_dir === DIRECTORY_SEPARATOR ? '' : $_base_dir, '/');
define('BASE_URL', $_base_scheme . '://' . $_base_host . $_base_dir);
unset($_base_scheme, $_base_host, $_base_dir);

// ── 2. Bootstrap ─────────────────────────────────────────────────────────────
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/Database.php';
require BASE_PATH . '/services/SecurityHelper.php';
require BASE_PATH . '/config/Router.php';

// ── 3. Sécurité HTTP headers ─────────────────────────────────────────────────
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // CDN Bootstrap + Font Awesome autorisés dans la CSP
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
        . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
        . "img-src 'self' data: https:; "
        . "font-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; "
        . "connect-src 'self';"
    );
}

// ── 4. Session sécurisée (unique appel) ──────────────────────────────────────
SecurityHelper::startSession();

// ── 5. URI normalisée ────────────────────────────────────────────────────────
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Retirer le préfixe du sous-répertoire (/app_fie/public → /app_fie)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
    $uri = substr($uri, strlen($scriptDir));
}

// Retirer la query string
if (($qpos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $qpos);
}

$uri = '/' . ltrim(urldecode($uri), '/');

// ── 6. Dispatch ──────────────────────────────────────────────────────────────
$router = new Router();
$router->dispatch($method, $uri);
