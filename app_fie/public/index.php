<?php
/**
 * FIE — Front Controller (point d'entrée unique)
 * Toutes les requêtes HTTP sont acheminées ici via la règle Apache/Nginx :
 *   RewriteEngine On
 *   RewriteCond %{REQUEST_FILENAME} !-f
 *   RewriteCond %{REQUEST_FILENAME} !-d
 *   RewriteRule ^ index.php [L]
 *
 * Ce fichier :
 *   1. Définit BASE_PATH et charge config.php
 *   2. Démarre la session sécurisée (via SecurityHelper)
 *   3. Instancie le Router et dispatche la requête
 */

declare(strict_types=1);

// ── 1. Chemins absolus ───────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));    // /chemin/vers/app_fie
define('BASE_URL',  rtrim(
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    . dirname($_SERVER['SCRIPT_NAME']),   // retire /public/index.php
    '/'
));

// ── 2. Bootstrap ─────────────────────────────────────────────────────────────
require BASE_PATH . '/config/config.php';

use App\Services\SecurityHelper;
use App\Config\Router;

// ── 3. Sécurité HTTP headers ─────────────────────────────────────────────────
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // CSP basique — à durcir en production
    header("Content-Security-Policy: default-src 'self'; "
         . "style-src 'self' 'unsafe-inline'; "
         . "script-src 'self' 'unsafe-inline'; "
         . "img-src 'self' data:; "
         . "font-src 'self';");
}

// ── 4. Session sécurisée ─────────────────────────────────────────────────────
SecurityHelper::startSession();

// ── 5. URI normalisée ────────────────────────────────────────────────────────
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Retirer le préfixe éventuel du sous-répertoire
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
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
