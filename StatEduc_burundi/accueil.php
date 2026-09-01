<?php 
require_once 'common.php';

//Pour affichage direct du menu de consultation des cubes serveur et locaux pour les cubes users
if(isset($_SESSION['groupe']) && $_SESSION['groupe']==0) $GLOBALS['afficher_menu_cubes'] = true;// A revoir si necessaire
//Fin Pour affichage du menu de consultation des cubes serveur et locaux

require_once $GLOBALS['SISED_PATH_INC'] . 'header.php';

$photo = 'photo';
if(isset($_SESSION['code_etab']) && $_SESSION['code_etab']<>''){
	require_once $GLOBALS['SISED_PATH_INC'] . 'accueil/info_etablissement.php';
}

require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php';

?>