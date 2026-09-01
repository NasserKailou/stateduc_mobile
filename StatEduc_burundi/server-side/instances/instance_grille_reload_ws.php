<?php 
ini_set("memory_limit", "256M");
// Fichiers d'includes'
include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'controle.inc.php';
    
    // paramètre d'échange
    $id_theme			=	$theme_manager->id;    
	$code_etablissement = $_SESSION['code_etab'];
    $code_annee = $_SESSION['annee'];
	if (isset($_GET['code_annee'])) {
		$code_annee = $_GET['code_annee'];
	}
	$code_filtre = $_SESSION['filtre'];
    $id_systeme	= $_SESSION['secteur'];

    if( $_GET['val'] == 'new_etab'){
				unset($code_etablissement);
    }

	// Instanciation de la classe
    $curobj_grille		=	new grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
	$curobj_grille->is_from_mobile = true;
	//echo "<pre>";
	//print_r($curobj_grille);
	
	// Appel des fonctions de la classe
    // chargement des codes des nomenclatures des champs de type matrice
    $curobj_grille->set_code_nomenclature();

    // Récupération des différents champs
    // $curobj_grille->set_champs();

    // Configuration de la barre de navigation
	
	//echo '<pre>';
	//print_r($_SESSION['tab_get_filter']);
	// récupération des données de la base de données
    //echo '<pre>';
	//print_r($curobj_grille->sql_data);
	//echo '<pre>';
	//print_r($curobj_grille->keys_template);
	$curobj_grille->get_donnees_bdd();
	
	// remplissage et affichage du template
    $template = $curobj_grille->remplir_template($curobj_grille->template);
	
	//echo '<pre>';
	echo json_encode($curobj_grille->tab_donnees_tpl);
    //echo $template;   

	$_SESSION['nbre_total_enr'] = $GLOBALS['nbre_total_enr'];
    	
//unset($_SESSION['tab_html_export_hist']);
//echo "<pre>";
//print_r($_SESSION["table_html"]);
?>
