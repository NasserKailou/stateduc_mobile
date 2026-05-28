<?php lit_libelles_page('/pop_olap_img.php');
	if( file_exists( $GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/images/' . $_GET['fich_OlapImg'] ) && $_GET['fich_OlapImg'] <>'' ){
		header('Location: '.$GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/images/' . $_GET['fich_OlapImg']);
	}else{
		echo "Impossible : ".recherche_libelle_page('ResultAffich'); 
	} 
?>


