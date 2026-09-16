<?php
/**
 * common_session_guard.php — Guard session_start() contre le double-lock
 *
 * USAGE: Remplacer chaque `session_start()` dans common.php par ce patron.
 *
 * TAGS: AK-FIX-SESSION
 *
 * PROBLÈME RÉSOLU (BUG-SESSION-001):
 *   `session_start()` sans guard dans common.php re-verrouille le fichier
 *   de session quand questionnaire_ws.php appelle session_write_close()
 *   avant le cURL interne. Sous Apache avec MPM worker ou prefork, cela
 *   cause un deadlock : le processus cURL attend le processus parent
 *   qui détient le verrou → timeout infini → cURL error 28.
 *
 * RÈGLE ABSOLUE:
 *   - Tout `session_start()` dans common.php DOIT être précédé de ce guard.
 *   - questionnaire_ws.php DOIT appeler `session_write_close()` AVANT
 *     le require_once de common.php (ou avant tout cURL interne).
 */

// ─────────────────────────────────────────────────────────────────────────────
// PATTERN 1 — Guard simple (à utiliser dans common.php)
// ─────────────────────────────────────────────────────────────────────────────

// AVANT (bug):
// session_start();

// APRÈS (AK-FIX-SESSION):
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 3600);
    ini_set('session.cookie_lifetime', 0);
    session_start();
}

// ─────────────────────────────────────────────────────────────────────────────
// PATTERN 2 — Dans questionnaire_ws.php (avant require_once common.php)
//             Libérer le verrou de session AVANT le cURL interne
// ─────────────────────────────────────────────────────────────────────────────

/**
 * À placer en TÊTE de questionnaire_ws.php, AVANT tout require_once:
 *
 * // AK-FIX-SESSION: libérer le verrou de session avant le cURL interne
 * if (session_status() === PHP_SESSION_ACTIVE) {
 *     session_write_close();
 * }
 * require_once 'common.php';
 */

// ─────────────────────────────────────────────────────────────────────────────
// PATTERN 3 — Dans data_save.php (avant le cURL)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * À placer juste AVANT la construction du cURL dans data_save.php:
 *
 * // AK-FIX-SESSION: écrire et fermer la session avant cURL interne
 * // Sinon le processus Apache enfant (questionnaire_ws.php) sera bloqué
 * // en attendant le verrou de session détenu par le processus parent.
 * if (session_status() === PHP_SESSION_ACTIVE) {
 *     session_write_close();
 * }
 *
 * $curl = new Curl();
 * $curl->setOpt(CURLOPT_URL, SISED_AURL_INTERNAL . '/questionnaire_ws.php/...');
 * // ...
 */

// ─────────────────────────────────────────────────────────────────────────────
// PATTERN 4 — Reprise de session après cURL (si nécessaire)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Si vous devez accéder à $_SESSION après le cURL:
 *
 * // Reprendre la session après le cURL
 * if (session_status() === PHP_SESSION_NONE) {
 *     session_start();
 * }
 * $_SESSION['last_save'] = time();
 * session_write_close(); // refermer proprement
 */

// ─────────────────────────────────────────────────────────────────────────────
// CHECKLIST d'application
// ─────────────────────────────────────────────────────────────────────────────
/*
Fichiers à modifier:

1. common.php (ligne ~94 et ~592 si dupliqué):
   CHERCHER:  session_start();
   REMPLACER: if (session_status() === PHP_SESSION_NONE) { session_start(); }

2. questionnaire_ws.php (toute en tête, avant require_once):
   AJOUTER:
   if (session_status() === PHP_SESSION_ACTIVE) { session_write_close(); }

3. data_save.php (avant construction cURL):
   AJOUTER:
   if (session_status() === PHP_SESSION_ACTIVE) { session_write_close(); }

Vérification:
   grep -n "session_start()" common.php questionnaire_ws.php data_save.php
   # Toutes les occurrences doivent être précédées du guard
*/
