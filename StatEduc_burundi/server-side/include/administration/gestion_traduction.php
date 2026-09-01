<?php include $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     

//////////// EN CAS DE SOUMISSION
if (count($_POST) > 0){       

		if (isset($_SESSION['instance_traduc'] )){
				$traduction   =   $_SESSION['instance_traduc'];        
		}      
		// Récupération de la valeur du post
		$traduction->get_post_template($_POST);  
				
		// compraison
		$traduction->comparer($traduction->matrice_donnees_template,$traduction->donnees_post); 
			 
		// maj dans la base de donées       
		$traduction->maj_bdd($traduction->matrice_donnees_bdd);
		unset($_SESSION['instance_traduc']);

}

////////////////// REAFFICHAGE DE LA PAGE
if (isset($_GET['type_traduction'])){
		$type_traduction  =$_GET['type_traduction'];
}else{
		$type_traduction  =1; 
} 

if (isset($_GET['langue_depart'])){
		$langue_depart  =$_GET['langue_depart'];
}else{
		// Gestion de la traduction en fonction qu'il y a une seule langue       
        if (isset($_SESSION['langue']))  {
            $langue_depart =$_SESSION['langue'];
        } else{
            /* TODO INITIALISER AVEC LA PREMIERE LANGUE DE LA BDD */
            $langue_depart ='fr';
        }        
        
} 

if (isset($_GET['langue_arrive'])){
		$langue_arrive   =$_GET['langue_arrive'];
}else{
		
        // Gestion de la traduction en fonction qu'il y a une seule langue       
        if (isset($_SESSION['langue']))  {
            $langue_arrive =$_SESSION['langue'];
        } else{
            /* TODO INITIALISER AVEC LA PREMIERE LANGUE DE LA BDD */
            $langue_arrive ='eng';
        }               
}            

if (isset($_GET['lib_nom_table']) and (trim($_GET['lib_nom_table']) <> '')){
		$lib_nom_table  =$_GET['lib_nom_table'];
}else{
		        
                // Gestion des erreurs lors de l'exécution de la requête sql
        try{ 
			 // recherche du premier élément de la liste pour un chargement automatique
			if ($type_traduction  ==1){
				$sql ='SELECT DISTINCT NOM_TABLE FROM DICO_TRADUCTION  ORDER BY NOM_TABLE';
				$list_traduction =  array_merge ($GLOBALS['conn']->GetAll($sql), $GLOBALS['conn_dico']->GetAll($sql)); 
				if (!is_array($list_traduction)) 
					throw new Exception('ERR_SQL');              
			}
			else{
				$sql ='SELECT DISTINCT NOM_PAGE FROM DICO_LIBELLE_PAGE  ORDER BY NOM_PAGE';  
				$list_traduction =   $GLOBALS['conn_dico']->GetAll($sql); 
				if (!is_array($list_traduction)) 
					throw new Exception('ERR_SQL');           
			}
			     
        }
        catch (Exception $e){
            $erreur = new erreur_manager($e,$requete);
        }
        if ($type_traduction  ==1){
            $lib_nom_table   =$list_traduction[0]['NOM_TABLE'];
        }
        else{
            $lib_nom_table   =$list_traduction[0]['NOM_PAGE'];
        }
}       
if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($lib_nom_table))){ // Table de Nomenclature : traduction dans la base courante
	$conn                 =   $GLOBALS['conn'];
} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
	$conn                 =   $GLOBALS['conn_dico']; 
}
$traduction = new traduction($langue_depart, $langue_arrive , $lib_nom_table,$type_traduction, $conn); 

if($traduction->fix_donnees_non_traduites()) {

		// Configuration de la barre de navigation
		configurer_barre_nav($traduction->nb_lignes);
		$html .='<br /> <br />';
		$html     .="<span class=''>";
		$html .= '<table width="50%" class="center-table">';
        
         // gestion des entêtes des menus
        $html .= '<caption>';
        $html .=  $traduction->recherche_libelle_page('IdEntete',$_SESSION['langue'],'traduction');
        $html .= '</caption>';
       // Fin de la gestion des entêtes
       
		$html .= '<tr>';
		$html .= '<td align="center">';
		$html .= '</td>';
		$html .= '<td align="center">';  

		$traduction->get_donnees();
		
		// recherche des libellés des entêtes du template
		$traduction->id_name        =   $traduction->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'traduction');
		/*$traduction->lib_name       =   $traduction->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'traduction');
		$traduction->lib_ordre      =   $traduction->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'traduction'); */
        
        $traduction->lib_name       =   $traduction->recherche_langue($langue_depart);
		$traduction->lib_ordre      =   $traduction->recherche_langue($langue_arrive); 
		
		$html .= $traduction->entete_template; 

		$html .= $traduction->remplir_template($traduction->template);
		$html .= $traduction->fin_template; 
		$html .= afficher_barre_nav(true,false, array('val', 'lib_nom_table', 'langue_depart', 'langue_arrive', 'type_traduction'));
		$html .= '<br />';       
 
		//passage de l'objet en session
		$_SESSION['instance_traduc']  =   $traduction;   

		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>'; 
		$html.='</span><br />';
		echo $html;     


} else {

		echo 'Erreur de cohérence...';

}
    
?>
