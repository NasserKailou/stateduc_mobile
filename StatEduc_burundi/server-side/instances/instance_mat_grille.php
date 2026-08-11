<?php 
ini_set("memory_limit", "64M");
// Fichiers d'includes'

include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'controle.inc.php';


if(count($_POST)==0) {
// Modif Bass
} elseif(!isset($GLOBALS['dont_submit'])) {

    require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/controle_theme.class.php';

    $curobj_matgrille 		= $_SESSION['curobj_instance'];
    
    // ici fonction VERIF du $_POST
    $curobj_matgrille->verif($_POST);
    
    // Récuperation des données de la superglobale $_POST
    $curobj_matgrille->get_post_template($_POST);
    
    // Comparaison des matrices de départ et d'arrivée'
    //$indice_cle	=	$curobj_matgrille->nb_cles - 1;
    $curobj_matgrille->comparer($curobj_matgrille->matrice_donnees,$curobj_matgrille->matrice_donnees_post);
		/*
		echo'champs<pre>';
		print_r($curobj_matgrille->champs);
    //echo'matrice_donnees<pre>';
		//print_r($curobj_matgrille->matrice_donnees);
    echo'matrice_donnees_bdd<pre>';
		print_r($curobj_matgrille->matrice_donnees_bdd);
   */
	 $curobj_matgrille->maj_bdd();
    
     $controle_theme	=	new controle_theme($curobj_matgrille->id_theme, $_SESSION['langue'], $curobj_matgrille->code_etablissement, $curobj_matgrille->code_annee, $curobj_matgrille->code_filtre,true);
    
    unset($_SESSION['curobj_instance']);

}
 		echo '<script language="javascript" src="'.$GLOBALS['SISED_URL_JSC'] . 'js.js"></script>' . "\n";
    // paramètre d'échange
    $id_theme = $theme_manager->id;
    $code_etablissement = $_SESSION['code_etab'];
    $code_annee = $_SESSION['annee'];
	$code_filtre = $_SESSION['filtre'];
    $id_systeme	= $_SESSION['secteur'];
    
    $curobj_matgrille = new mat_grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
    
    // Appel des fonctions de la classe
    // chargement des codes des nomenclatures des champs de type matrice
    $curobj_matgrille->set_code_nomenclature();
    
    // Récupération des différents champs
    $curobj_matgrille->set_champs();
    
    // Configuration de la barre de navigation
    configurer_barre_nav($curobj_matgrille->nb_lignes);
    
    $curobj_matgrille->get_donnees_bdd();
     
    // remplissage et affichage du template
    $curobj_matgrille->remplir_template($curobj_matgrille->template);
    
   // Affichage de la barre de navigation 
    if ($curobj_grille->nb_lignes==1)  { 
        $barre_nav = afficher_barre_nav(false,true,array('theme','type_ent_stat'));
    }else{
        $barre_nav = afficher_barre_nav(true,true,array('theme','type_ent_stat'));
    }

    echo $template;   

    echo $barre_nav;
    
    // Sauvegarde de l'objet en session
    $_SESSION['curobj_instance'] = $curobj_matgrille;
?>
