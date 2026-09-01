<?php // Ce fichier permet de sécuriser l'accès aux données. 
// Il faut necessairement s'être authentifié pour le passer.
// 
// Pour sécuriser un fichier il suffira donc d'inclure en début de script ce fichier.

//session_cache_expire (60);/* Configure le délai d'expiration à 60 minutes */

if (!$_SESSION['valide'])
{
	header('Location: '.$SISED_AURL.'index.php');
	exit();
}

?>
