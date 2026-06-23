<script language="JavaScript1.2" type="text/javascript">
    function OpenPopupInstanceRpt(nom_fic) {
        var	popup	=	window.open(nom_fic,'popUserRpt', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=600, height=370, left=200, top=150')
        popup.document.close();
        popup.focus();
		}

</script>
<?php // Inclusion de la classe quickreport pour la génération des reports
    include_once $GLOBALS['SISED_PATH_CLS'].'metier/quickreport.class.php';  
	include_once $GLOBALS['SISED_PATH_CLS'] .'metier/create_query.class.php'; 
    include_once $GLOBALS['SISED_PATH_CLS'].'metier/executionscript.class.php';         


    set_time_limit(0);
    $parametres = $_SESSION['params'];
    // Maj de la source de données avant le traitement de la production du report
    $ExecScript = new executionscript($parametres );
	//Recupération de l'id de l'état
    $id_report  = $parametres['ID_REPORT'];
    //Nom du fichier pdf qui sera créé
    $nom_fichier ='server-side/include/tableaux_synthese/pdf/'.$id_report.session_id();
    //Instanciation de la classe quickreport
    $creport = new quickreport($parametres,$nom_fichier);
    //Création du fichier pdf généré
    $creport->preview_report();
    //Visualisation du fichier pdf généré
    echo '<script language="JavaScript1.2" type="text/javascript">';
    echo 'OpenPopupInstanceRpt(\''.$nom_fichier.'.pdf'.'\')';
    echo '</script>';
?>

