<?php
/**
 * data_save_curl.php — cURL interne robuste vers questionnaire_ws.php
 *
 * USAGE: Copier/adapter dans StatEduc_burundi/data_save.php
 *
 * TAGS: AK-FIX-PORT, AK-FIX-TIMEOUT, AK-FIX-SESSION, AK-YEAR-MULTI
 *
 * PROBLÈMES RÉSOLUS:
 *   - BUG-PORT-001:    URL interne incorrecte (port 80 au lieu de 8083) → 404
 *   - BUG-CURL28-001:  Timeout 120s insuffisant → cURL error 28
 *   - BUG-SESSION-001: Deadlock session → cURL bloqué → error 28
 */

// ─────────────────────────────────────────────────────────────────────────────
// DÉPENDANCES
// ─────────────────────────────────────────────────────────────────────────────
require_once 'config_app.php';  // définit SISED_AURL_INTERNAL, SISED_CURL_TIMEOUT
require_once 'common.php';      // session, connexion DB

// ─────────────────────────────────────────────────────────────────────────────
// LIBÉRATION SESSION — OBLIGATOIRE avant tout cURL interne (AK-FIX-SESSION)
// ─────────────────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// ─────────────────────────────────────────────────────────────────────────────
// RÉCEPTION DES DONNÉES FLUTTER
// ─────────────────────────────────────────────────────────────────────────────
$raw    = file_get_contents('php://input');
$data   = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['se_data' => 'Payload JSON invalide']);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// VALIDATION DES CHAMPS OBLIGATOIRES
// ─────────────────────────────────────────────────────────────────────────────
$login = trim($data['login'] ?? '');
if (empty($login)) {
    http_response_code(422);
    echo json_encode(['se_data' => 'Champ login manquant']);
    exit;
}

// AK-YEAR-MULTI: résoudre l'année à utiliser
$annee_code = trim($data['annee_code'] ?? '');
if (empty($annee_code)) {
    // Fallback : année active en base
    $rs_annee = $GLOBALS['conn']->Execute(
        "SELECT code FROM annees WHERE active = 1"
    );
    if ($rs_annee && !$rs_annee->EOF) {
        $annee_code = $rs_annee->fields['CODE'];
    }
}
if (empty($annee_code)) {
    http_response_code(422);
    echo json_encode(['se_data' => 'Année introuvable — configurer une année active']);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// CONSTRUCTION DE L'URL INTERNE
// ─────────────────────────────────────────────────────────────────────────────
// SISED_AURL_INTERNAL = "http://127.0.0.1:{port_détecté}" (AK-FIX-PORT)
$encoded_login  = urlencode($login);
$encoded_annee  = urlencode($annee_code);
$url_interne    = SISED_AURL_INTERNAL
                . '/questionnaire_ws.php/save'
                . '/' . $encoded_login
                . '/annee/' . $encoded_annee;

// ─────────────────────────────────────────────────────────────────────────────
// EXÉCUTION DU cURL INTERNE ROBUSTE
// ─────────────────────────────────────────────────────────────────────────────
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url_interne,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $raw,          // renvoi du payload brut
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => SISED_CURL_TIMEOUT,      // 300s (AK-FIX-TIMEOUT)
    CURLOPT_CONNECTTIMEOUT => SISED_CURL_CONN_TIMEOUT, // 10s
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => false,  // connexion locale, pas besoin de vérif SSL
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response   = curl_exec($ch);
$http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
curl_close($ch);

// ─────────────────────────────────────────────────────────────────────────────
// TRAITEMENT DE LA RÉPONSE
// ─────────────────────────────────────────────────────────────────────────────
if ($curl_errno !== 0) {
    // cURL a échoué (réseau, timeout, etc.)
    error_log("[data_save] cURL error $curl_errno: $curl_error | URL: $url_interne");

    http_response_code(502);
    echo json_encode([
        'se_data' => "$curl_errno : $curl_error",
        'se_url'  => $url_interne,  // aide au diagnostic
    ]);
    exit;
}

if ($http_code !== 200) {
    // questionnaire_ws.php a répondu avec un code d'erreur HTTP
    error_log("[data_save] HTTP $http_code depuis questionnaire_ws | URL: $url_interne");

    http_response_code($http_code);
    echo json_encode([
        'se_data' => "$http_code : HTTP/$http_code",
        'se_url'  => $url_interne,
    ]);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// SUCCÈS — relayer la réponse de questionnaire_ws.php
// ─────────────────────────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
echo $response;
