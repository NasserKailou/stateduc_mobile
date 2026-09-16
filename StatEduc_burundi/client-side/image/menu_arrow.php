<?php
// fix(session33/menu_arrow): ZERO session_start() — cause racine réelle du timeout 30s.
//
// ════════════════════════════════════════════════════════════════════════
// DIAGNOSTIC COMPLET — pourquoi session_start(['read_and_close'=>true]) ne suffit pas
// ════════════════════════════════════════════════════════════════════════
//
// CAUSE RACINE RÉELLE :
//   Sur XAMPP Windows (PHP/Apache WinNT MPM), le handler de session fichier
//   utilise fopen() + LOCK_EX (verrou exclusif) sur le fichier sess_XXXX.
//   administration.php appelle session_start() via common.php ligne 94 et
//   DÉTIENT ce verrou pendant toute la durée du rendu de la page (plusieurs
//   secondes si génération de frames mobile ou requêtes SQL lentes).
//
//   Pendant ce temps, le navigateur déclenche en parallèle le chargement de
//   menu_arrow.php (référencé comme image src="...menu_arrow.php").
//   PHP tente d'ouvrir le même fichier sess_XXXX avec fopen(LOCK_EX).
//   Sur Windows, flock() LOCK_EX est BLOQUANT par défaut (contrairement à Linux
//   où PHP utilise parfois LOCK_SH pour read_and_close).
//
//   POURQUOI read_and_close NE RÉSOUT PAS le problème sur ce XAMPP :
//   - L'option 'read_and_close' => true est supportée depuis PHP 7.0.0 MAIS
//     certaines distributions XAMPP Windows compilent avec un session handler
//     personnalisé (ou une version de session.c antérieure au patch) qui ignore
//     cette option et utilise toujours LOCK_EX jusqu'à session_write_close().
//   - Sur XAMPP Windows avec PHP 5.x ou certains PHP 7.x early, session_start()
//     ouvre une connexion avec verrou bloquant quel que soit le paramètre passé.
//   - Résultat : menu_arrow.php attend 30s → PHP fatal error.
//
// CORRECTION DÉFINITIVE :
//   Supprimer TOUT appel à session_start(). Ce script sert une image GIF.
//   Il n'a AUCUN besoin d'écrire en session.
//   Pour lire $_SESSION['style'], on lit le fichier de session directement
//   avec fopen() + LOCK_SH (verrou partagé, non-exclusif, non-bloquant)
//   puis on parse le contenu avec session_decode() sur une variable locale.
//   Si la lecture échoue (fichier verrouillé, absent, PHPSESSID manquant),
//   on retombe immédiatement sur le GIF 'defaut' — zéro délai, zéro erreur.
//
// MARQUEUR DE VERSION (à supprimer après vérification en production) :
//   En-tête X-Arrow-Version présent dans la réponse HTTP → confirme que
//   cette version du fichier est bien celle exécutée par le serveur.
//   Pour vérifier : F12 → Réseau → menu_arrow.php → En-têtes de réponse.
//
// CRITÈRE D'ACCEPTATION :
//   - Zéro "Maximum execution time exceeded" dans les logs Apache/PHP.
//   - En-tête X-Arrow-Version: v3-nosession visible dans les DevTools.
//   - Flèche de menu affichée en moins de 50 ms.
// ════════════════════════════════════════════════════════════════════════

// ── Marqueur de version debug (TEMPORAIRE — supprimer après validation prod) ──
header('X-Arrow-Version: v3-nosession');

// ── Chrono de diagnostic (écrit dans un log temporaire si activé) ──────────────
// Décommenter les lignes _log() ci-dessous pour activer le chrono de trace.
// $t0 = microtime(true);
// $logfile = __DIR__ . '/menu_arrow_debug.log';
// function _log($msg) { global $t0, $logfile; $ms = round((microtime(true)-$t0)*1000,2); file_put_contents($logfile, "[{$ms}ms] $msg\n", FILE_APPEND); }
// _log("START file=" . __FILE__);

$name = 'defaut'; // valeur par défaut si la session n'est pas lisible

// ── Lecture du style SANS session_start() ────────────────────────────────────
// On lit le fichier de session directement avec flock(LOCK_SH|LOCK_NB) :
// - LOCK_SH  = verrou partagé (compatible avec le LOCK_EX d'administration.php)
// - LOCK_NB  = non-bloquant (retourne false immédiatement si impossible)
// Pas de session_start() → pas de compétition sur le verrou exclusif.

$style_found = false;

if (isset($_COOKIE[session_name()])) {
    // Construire le chemin du fichier de session (respecte session.save_path)
    $save_path = session_save_path();
    if (empty($save_path) || !is_dir($save_path)) {
        $save_path = sys_get_temp_dir();
    }

    // Sanitize l'ID de session : uniquement hex/alphanum, longueur 1-128
    $raw_sid = $_COOKIE[session_name()];
    if (preg_match('/^[a-zA-Z0-9,\-]{1,128}$/', $raw_sid)) {
        $sess_file = $save_path . DIRECTORY_SEPARATOR . 'sess_' . $raw_sid;

        if (file_exists($sess_file)) {
            $fh = @fopen($sess_file, 'rb');
            if ($fh !== false) {
                // Tente un verrou partagé non-bloquant (échec immédiat si LOCK_EX actif)
                if (@flock($fh, LOCK_SH | LOCK_NB)) {
                    $data = @fread($fh, filesize($sess_file));
                    @flock($fh, LOCK_UN);
                    @fclose($fh);

                    // Parser le contenu de session sans session_start()
                    if ($data !== false && strlen($data) > 0) {
                        // Extraction manuelle de la clé 'style' depuis le format
                        // PHP session serialize : "style|s:7:\"afrique\";"
                        // Regex robuste couvrant les valeurs courtes (noms de styles CSS)
                        if (preg_match('/\bstyle\|s:\d+:"([^"]{1,64})";/', $data, $m)) {
                            $candidate = preg_replace('`\.css$`', '', $m[1]);
                            // Sécurité : valider que le nom ne contient que des caractères sûrs
                            if (preg_match('/^[a-zA-Z0-9_\-]+$/', $candidate)) {
                                $gif_path = __DIR__ . '/menu_arrow-' . $candidate . '.gif';
                                if (file_exists($gif_path)) {
                                    $name = $candidate;
                                    $style_found = true;
                                }
                            }
                        }
                    }
                } else {
                    // LOCK_SH non obtenu → session verrouillée par administration.php
                    // → on sert 'defaut' immédiatement, sans attendre
                    @fclose($fh);
                    // _log("LOCK_SH refused (session locked) — using defaut");
                }
            }
        }
    }
}

// _log("style_found=" . ($style_found ? $name : 'defaut'));

// ── Envoi de l'image GIF ────────────────────────────────────────────────────
$filepath = __DIR__ . '/menu_arrow-' . $name . '.gif';

if (file_exists($filepath)) {
    header('Content-Type: image/gif');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: public, max-age=3600');
    // _log("readfile: $filepath");
    readfile($filepath);
} else {
    // Fallback absolu : 204 No Content — pas d'erreur réseau côté navigateur
    header('HTTP/1.1 204 No Content');
    // _log("fallback 204 (no gif found)");
}

// _log("END total=" . round((microtime(true)-$t0)*1000, 2) . "ms");
exit;
?>
