<?php
/**
 * config_app_template.php — Détection de port Apache + URL interne cURL
 *
 * USAGE: Copier dans StatEduc_burundi/config_app.php (section SISED_AURL_INTERNAL)
 *
 * TAGS: AK-FIX-PORT
 *
 * PROBLÈME RÉSOLU:
 *   Un whitelist positif ($ports_http_only) excluait les ports hors liste (ex: 8083).
 *   Remplacé par une liste d'exclusion SSL → tout port non-SSL est tenté.
 *
 * HISTORIQUE BUGS:
 *   - BUG-PORT-001: whitelist [80,8080,8000,8888] → port 8083 → cURL → 127.0.0.1:80 → 404
 */

// ─────────────────────────────────────────────────────────────────────────────
// CONSTANTES DE BASE — adapter selon l'environnement
// ─────────────────────────────────────────────────────────────────────────────
define('SISED_APP_ROOT',  dirname(__FILE__));
define('SISED_APP_NAME',  'StatEduc');

// ─────────────────────────────────────────────────────────────────────────────
// DÉTECTION DU PORT LOCAL APACHE  (AK-FIX-PORT)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Retourne le port HTTP local sur lequel Apache écoute réellement.
 *
 * Stratégie (ordre de priorité) :
 *  1. $_SERVER['SERVER_PORT'] si non-SSL et socket 127.0.0.1:port ouvert
 *  2. Scan des ports candidats courants (80, 8080, 8000, 8083, 8888)
 *  3. Fallback : 80
 *
 * IMPORTANT: On utilise une LISTE D'EXCLUSION (SSL seulement) et non une
 * liste positive, pour ne pas manquer les ports non standard (ex: 8083).
 *
 * @return int
 */
function _sised_local_port(): int {
    // Ports SSL — jamais utilisables pour cURL interne en HTTP
    $ssl_ports = [443, 8443];

    // 1. Essayer le port déclaré par Apache
    $p = (int)($_SERVER['SERVER_PORT'] ?? 0);
    if ($p > 0 && !in_array($p, $ssl_ports, true)) {
        $s = @fsockopen('127.0.0.1', $p, $errno, $errstr, 2);
        if ($s !== false) {
            fclose($s);
            return $p;
        }
    }

    // 2. Scan des ports candidats courants
    // Ajouter ici tout port non-standard utilisé dans votre environnement
    $candidates = [80, 8080, 8000, 8083, 8888, 8008, 8090];
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $ssl_ports, true)) continue;
        $s = @fsockopen('127.0.0.1', $candidate, $errno, $errstr, 2);
        if ($s !== false) {
            fclose($s);
            return $candidate;
        }
    }

    // 3. Fallback ultime
    return 80;
}

// ─────────────────────────────────────────────────────────────────────────────
// URL INTERNE — utilisée par data_save.php pour contourner le NAT/Fortinet
// ─────────────────────────────────────────────────────────────────────────────
$_sised_port_local = _sised_local_port();
define('SISED_PORT_LOCAL',    $_sised_port_local);
define('SISED_AURL_INTERNAL', 'http://127.0.0.1:' . $_sised_port_local);

// ─────────────────────────────────────────────────────────────────────────────
// CONFIGURATION BASE DE DONNÉES (Access via ADODB)
// ─────────────────────────────────────────────────────────────────────────────
define('SISED_DB_PATH',   SISED_APP_ROOT . '/database/stateduc.mdb');
define('SISED_DB_DRIVER', 'access');   // ou 'odbc', 'ado' selon le serveur

// ─────────────────────────────────────────────────────────────────────────────
// CONFIGURATION ADODB — clés en majuscules (évite BUG-ADODB-001)
// ─────────────────────────────────────────────────────────────────────────────
if (!defined('ADODB_ASSOC_CASE')) {
    define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);
}

// ─────────────────────────────────────────────────────────────────────────────
// PARAMÈTRES cURL INTERNES — cohérents avec data_save.php
// ─────────────────────────────────────────────────────────────────────────────
define('SISED_CURL_TIMEOUT',     300);  // 300s = 5 min (AK-FIX-TIMEOUT)
define('SISED_CURL_CONN_TIMEOUT', 10);  // 10s pour l'établissement de connexion

// ─────────────────────────────────────────────────────────────────────────────
// PARAMÈTRES MÉMOIRE PHP
// ─────────────────────────────────────────────────────────────────────────────
// Note: ini_set() ici s'applique à config_app.php.
// Pour questionnaire_ws.php, re-setter en tête de fichier (AK-FIX-MEM).
ini_set('memory_limit', '256M');

// ─────────────────────────────────────────────────────────────────────────────
// DEBUG (désactiver en production)
// ─────────────────────────────────────────────────────────────────────────────
define('SISED_DEBUG', false);

if (SISED_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    error_log('[config_app] PORT LOCAL DÉTECTÉ: ' . SISED_PORT_LOCAL);
    error_log('[config_app] AURL_INTERNAL: '      . SISED_AURL_INTERNAL);
}
