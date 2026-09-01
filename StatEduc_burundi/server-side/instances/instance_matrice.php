<?php 
ini_set("memory_limit", "64M");
// Fichiers d'includes'

include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'controle.inc.php';

if(count($_POST) == 0) {
// Modif bass
} elseif(!isset($GLOBALS['dont_submit'])) {

    require_once $GLOBALS['SISED_PATH_CLS'] .  'metier/controle_theme.class.php';

    // On récupère les paramètres globaux et l'objet passé en session

    $curobj_matrice 		= $_SESSION['curobj_instance'];
   
    $taille_ligne 					= count($curobj_matrice->ligne);
    $taille_colonne 				= count($curobj_matrice->colonne);
    //echo"<br>taille_ligne=$taille_ligne||taille_colonne=$taille_colonne <br>";

    // ici fonction VERIF du $_POST
    $curobj_matrice->verif($_POST);
   
    // On transforme les différentes valeurs de post en une matrice    
    
    $matrice_manuel	= post_to_matrice($curobj_matrice->mesures,$taille_ligne,$taille_colonne,$_POST,$curobj_matrice->type_matrice);
    
    // Formattage des données à l'image de la base de données
    $curobj_matrice->prepare_donnees_bdd($curobj_matrice->type_matrice,$matrice_manuel,$taille_ligne,$taille_colonne);

  
    // Comparaison matricielle
    $curobj_matrice->comparer_matrice($curobj_matrice->valeurs,$curobj_matrice->matrice_donnees_post,$curobj_matrice->nb_cles);
    
    //$matr_code[0]	= array('CODE_ETABLISSEMENT',$curobj_matrice->code_etablissement);
    //$matr_code[1]	= array('CODE_ANNEE',$curobj_matrice->code_annee);
    
    // maj des données dans la base de données
    //echo 'passage du post<pre>';
   // print_r($curobj_matrice->matrice_donnees_bdd);
    $curobj_matrice->maj_db($curobj_matrice->nomtableliee,$curobj_matrice->val_cle,$curobj_matrice->matrice_donnees_bdd,$curobj_matrice->dimensions,$curobj_matrice->mesures);
    
    //$curobj_matrice->conn->debug=false;
    $controle_theme	=	new controle_theme($curobj_matrice->id_theme, $_SESSION['langue'], $curobj_matrice->code_etablissement, $curobj_matrice->code_annee, $curobj_matrice->code_filtre,true);
    
    unset($_SESSION['curobj_instance']);

}
    echo '<script language="javascript" src="'.$GLOBALS['SISED_URL_JSC'] . 'js.js"></script>' . "\n";
    $id_theme = $theme_manager->id;
    $code_etablissement = $_SESSION['code_etab'];
    $code_annee = $_SESSION['annee'];
	$code_filtre = $_SESSION['filtre'];
    $id_systeme	= $_SESSION['secteur'];    

    $curobj_matrice	=	new matrice($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
    
    $curobj_matrice->get_ligne($curobj_matrice->sqlligne);
    $curobj_matrice->get_colonne($curobj_matrice->sqlcolonne);
    
    $curobj_matrice->get_matrice();
    
    $curobj_matrice->prepare_donnees_template();
    
    $curobj_matrice->remplit_formulaire($curobj_matrice->template);
    //echo $curobj_matrice->remplit_formulaire($curobj_matrice->template,$type_matrice,$nbligne,$nbcolonne,$curobj_matrice->mesures);
    
    $_SESSION['curobj_instance'] = $curobj_matrice;
?>
