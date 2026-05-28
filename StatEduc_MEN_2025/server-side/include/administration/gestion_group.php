<?php 

include $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php'; 
   
if (count($_POST)==0) {       
      
       // Echange de paramètres
       $lire_dico       =false;
       $insertion_dico      =    false;
       $afficher_secteur    = false;       
       $lib_nom_table='ADMIN_GROUPES';

       $nomenclature = new nomenclature($_SESSION['secteur'], $lib_nom_table,'','',false, $_SESSION['langue'],$lire_dico,$insertion_dico,$afficher_secteur,$GLOBALS['conn']); 
         
       $nomenclature->champ_id      ='CODE_GROUPE';
       $nomenclature->champ_lib     ='LIBELLE_GROUPE';
       $nomenclature->champ_ordre   ='ORDRE_GROUPE';
       
       // Configuration de la barre de navigation
       configurer_barre_nav($nomenclature->nb_lignes); 
       $html .='<br /> <br />';
       $html     .="<span class=''>";
       $html .= '<table width="50%" class="center-table">';
       
        // gestion des entêtes des menus
       $html .= '<caption>';
       $html .=  $nomenclature->recherche_libelle_page('IdEntete',$_SESSION['langue'],'group');
       $html .= '</caption>';
       // Fin de la gestion des entêtes
       
       $html .= '<tr>';
       $html .= '<td align="center">';
       $html .= '</td>';
       $html .= '<td align="center">';       
    
       $nomenclature->get_donnees();       
          
       $html .= $nomenclature->entete_template; 
      
      // recherche des libellés des entêtes du template
      $nomenclature->id_name        =   $nomenclature->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'nomenclature');
      $nomenclature->lib_name       =   $nomenclature->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'nomenclature');
      $nomenclature->lib_ordre      =   $nomenclature->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'nomenclature'); 
      
      $html .= $nomenclature->remplir_template($nomenclature->template);
      $html .= $nomenclature->fin_template; 
      $html .= afficher_barre_nav(true,true, array('val', 'type', 'gestiongroup'));
      $html .= '<br />';       
     
      //passage de l'objet en session
      $_SESSION['instance_nomenc']  =   $nomenclature;   
    
      $html .= '</td>';
      $html .= '</tr>';
      $html .= '</table>'; 
      $html.='</span><br />';
      echo $html;        
      
} else {

    // Il s'agit du traitement des donnees du POST
    
    if (isset($_SESSION['instance_nomenc'] )){
        $nomenclature   =   $_SESSION['instance_nomenc'];        
    }      
   
    // Récupération de la valeur du post
    $nomenclature->get_post_template($_POST);
    
    // compraison
    $nomenclature->comparer($nomenclature->matrice_donnees_template,$nomenclature->donnees_post);
    
    // maj dans la base de données        
    
   $nomenclature->maj_bdd($nomenclature->matrice_donnees_bdd);
  unset($_SESSION['instance_nomenc']);
  
  ///////////////////////////////////////////: Modif Alassane
    // Echange de paramètres
       $lire_dico       =false;
       $insertion_dico      =    false;
       $afficher_secteur    = false;       
       $lib_nom_table='ADMIN_GROUPES';

       $nomenclature = new nomenclature($_SESSION['secteur'], $lib_nom_table,'','',false, $_SESSION['langue'],$lire_dico,$insertion_dico,$afficher_secteur,$GLOBALS['conn']); 
         
       $nomenclature->champ_id      ='CODE_GROUPE';
       $nomenclature->champ_lib     ='LIBELLE_GROUPE';
       $nomenclature->champ_ordre   ='ORDRE_GROUPE';
       //echo $nomenclature->champ_id;
       // Configuration de la barre de navigation
       configurer_barre_nav($nomenclature->nb_lignes); 
       $html .='<br /> <br />';
       $html     .="<span class=''>";
       $html .= '<table width="50%" class="center-table">';
       
        // gestion des entêtes des menus
       $html .= '<caption>';
       $html .=  $nomenclature->recherche_libelle_page('IdEntete',$_SESSION['langue'],'group');
       $html .= '</caption>';
       // Fin de la gestion des entêtes
       
       $html .= '<tr>';
       $html .= '<td align="center">';
       $html .= '</td>';
       $html .= '<td align="center">';       
    
       $nomenclature->get_donnees();       
          
       $html .= $nomenclature->entete_template; 
      
      // recherche des libellés des entêtes du template
      $nomenclature->id_name        =   $nomenclature->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'nomenclature');
      $nomenclature->lib_name       =   $nomenclature->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'nomenclature');
      $nomenclature->lib_ordre      =   $nomenclature->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'nomenclature'); 
      
      $html .= $nomenclature->remplir_template($nomenclature->template);
      $html .= $nomenclature->fin_template; 
      $html .= afficher_barre_nav(true,true, array('val', 'type', 'gestiongroup'));
      $html .= '<br />';       
     
      //passage de l'objet en session
      $_SESSION['instance_nomenc']  =   $nomenclature;   
    
      $html .= '</td>';
      $html .= '</tr>';
      $html .= '</table>'; 
      $html.='</span><br />';
      echo $html;   
      
  ////////////////////////////////////// Fin Modif Alassane
    
}
 
    
?>
