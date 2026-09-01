<?php //lit_libelles_page(__FILE__);
		if(!isset($_POST['post_regen'])){
		?>
			<html>
					<link href="../css/formulaire_senegal.css" rel="stylesheet" type="text/css">
					<form method="post" action="accueil.php">
							<INPUT type="submit" name="Submit" value="Retour">
					</form>
			</html>
		<?php }
		require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
		genere_menu();
?>
