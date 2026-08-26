<?php
/**
 * StatEduc_burundi/api/fie_config.php
 * Configuration de la connexion vers app_fie
 * Inclure ce fichier dans tous les endpoints API côté StatEduc.
 */

// URL de base de l'application FIE (sans slash final)
if (!defined('FIE_API_BASE_URL')) {
    define('FIE_API_BASE_URL',  getenv('FIE_API_URL') ?: 'http://fie.ins.bi');
}
// Token Bearer que StatEduc présente à FIE
if (!defined('FIE_API_TOKEN')) {
    define('FIE_API_TOKEN',     getenv('FIE_API_TOKEN_VALUE') ?: '');
}
// Timeout curl (secondes)
if (!defined('FIE_API_TIMEOUT')) {
    define('FIE_API_TIMEOUT',   30);
}
// Source identifier envoyé dans chaque requête
if (!defined('FIE_SOURCE_ID')) {
    define('FIE_SOURCE_ID', 'stateduc_burundi');
}
