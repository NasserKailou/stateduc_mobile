<?php
/**
 * StatEduc_burundi/api/ping.php
 * ══════════════════════════════════════════════════════════════════════════════
 * Endpoint de vérification de connectivité — LÉGER, sans bootstrap DB.
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Utilisé par app_fie/api/stateduc/StatEducApiClient::ping() pour vérifier
 * que le serveur StatEduc est joignable SANS déclencher la connexion ADOdb
 * / SQL Server (qui prend 5–30s) ni aucune requête base de données.
 *
 * Réponse JSON :
 * {
 *   "status": "ok",
 *   "server": "StatEduc_burundi",
 *   "version": "1.0",
 *   "ts": 1234567890
 * }
 *
 * Accès :
 *   GET http://localhost:8085/StatEduc_burundi/api/ping.php
 *
 * Codes HTTP :
 *   200 — serveur disponible
 *
 * Aucune authentification requise (endpoint de health-check public).
 */

// Pas de session, pas de DB, pas d'include lourd — réponse immédiate.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-StatEduc-Ping: 1');

// Autoriser les appels cross-origin depuis app_fie (même hôte XAMPP = même origine,
// mais utile si app_fie tourne sur un port différent)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

echo json_encode([
    'status'  => 'ok',
    'server'  => 'StatEduc_burundi',
    'version' => '1.0',
    'ts'      => time(),
], JSON_UNESCAPED_UNICODE);
exit;
