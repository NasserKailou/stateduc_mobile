<?php lit_libelles_page('/pop_olap_doc.php');
	if( file_exists( $GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/docs/' . $_GET['fich_OlapDoc'] ) && $_GET['fich_OlapDoc'] <>'' ){
		//echo $GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/docs/' . $_GET['fich_OlapDoc']; 
		header('Location: '.$GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/docs/' . $_GET['fich_OlapDoc']);
	}else{
		echo "Impossible : ".recherche_libelle_page('ResultAffich'); 
	} 
?>


