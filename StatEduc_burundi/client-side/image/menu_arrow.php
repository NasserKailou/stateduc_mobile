<?php
// fix(session32/menu_arrow): supprime blocage 30s causé par session_start() concurrente.
//
// CAUSE RACINE :
//   administration.php détient un verrou exclusif sur le fichier de session PHP
//   (/tmp/sess_XXXX). Quand le navigateur charge menu_arrow.php en parallèle,
//   session_start() tente d'acquérir le même verrou → deadlock 30 s → fatal error.
//
// CORRECTION :
//   'read_and_close' => true  : lit $_SESSION puis relâche le verrou immédiatement,
//   sans bloquer ni attendre la fin du script parent.
//   Ce script n'écrit rien en session → read_and_close est suffisant et correct.
//
// FALLBACK : si aucun fichier GIF n'existe du tout, renvoie 204 No Content
//   plutôt qu'une réponse vide sans header, ce qui évite les erreurs réseau.

session_start(['read_and_close' => true]);

$name = 'defaut';

if (isset($_SESSION['style'])) {
    $candidate = preg_replace('`\.css$`', '', $_SESSION['style']);
    if (file_exists(__DIR__ . '/menu_arrow-' . $candidate . '.gif')) {
        $name = $candidate;
    }
}

$filepath = __DIR__ . '/menu_arrow-' . $name . '.gif';

if (file_exists($filepath)) {
    // Envoi immédiat du GIF — pas de base de données, pas de traitement lourd
    header('Content-Type: image/gif');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: public, max-age=3600');
    readfile($filepath);
} else {
    // Fallback propre : 204 No Content (aucun body, pas d'erreur réseau côté navigateur)
    header('HTTP/1.1 204 No Content');
}
exit;
?>
