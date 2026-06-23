<?php if(!isset($GLOBALS['cacher_interface']) || !$GLOBALS['cacher_interface']) {
lit_libelles_page('/footer.php');

?>

</div>

<div id="<?php echo isset($photo)?$photo:''; ?>"></div>

<!-- Affichage du pied de page --> 
<?php
if(preg_match('#accueil.php#',$_SERVER['PHP_SELF']) || preg_match('#index.php#',$_SERVER['PHP_SELF'])){
?>
<div id="footer">
<div id="gauche"><a target="_blank" href='http://www.uis.unesco.org'><?php echo recherche_libelle_page('ISU'); ?></a></div>

<?php if($GLOBALS['libelles_page_found'] == true){
?>
		<div id="centre"><?php echo recherche_libelle_page('Footer') . '        :           Version  1.4';?></div>
<?php }
if (isset($_SESSION['valide']) && $_SESSION['valide']){ ?>
		<div id="droite"><a href="javascript:centre('<?php echo $SISED_AURL; ?>credits.php',500,530,'menubar=no,scrollbars=yes,statusbar=no')"><?php echo recherche_libelle_page('credits'); ?></a></div>
<?php } else{?>
	<div id="droite"><a target="_blank" href='http://www.uis.unesco.org'><?php echo recherche_libelle_page('ISU'); ?></a></div>
<?php } 
}
?>
</div>
<!-- FIN Affichage du pied de page -->
<?php }

?>

</BODY>
<?php 
if(!preg_match('#index.php#',$_SERVER['PHP_SELF'])) {
echo '<script type="text/Javascript"> $.unblockUI(); </script>';
}
?>
</HTML>
<?php if(isset($GLOBALS['msg_global'])) Alerte_Globale($GLOBALS['msg_global']); Alert_chg_cnx();Lancer_Popup();?>
