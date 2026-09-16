<?php include $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     
$GLOBALS['conn'] = $GLOBALS['conn_dico'] ;
if (count($_POST)==0) {
       
       if (isset($_GET['id_groupe'])){
       		$id_groupe  =$_GET['id_groupe'];
       }else{
            //$id_groupe   =1; 
			$id_groupe   = $_SESSION['groupe'];
       }  
       
       $lib_nom_table='ADMIN_DROITS';
       $type_traitement='droit';  

       $user = new user($id_groupe, $lib_nom_table,$type_traitement,$_SESSION['langue'],$conn); 
         
       $user->champ_id      ='ID_MENU';
       $user->champ_lib     ='LIBELLE';
       $user->champ_ordre   ='TYPE';
       $user->champ_systeme = 'CODE_GROUPE';      
       
       
       // Configuration de la barre de navigation
       configurer_barre_nav($user->nb_lignes); 
       
       $html .= '<br /><br /><br />';
       $html .= "<span class=''>";
       $html .= '<table width="50%" class="center-table">';
       
       // gestion des entêtes des menus
       $html .= '<caption>';
       $html .=  $user->recherche_libelle_page('IdEntete',$_SESSION['langue'],'droit');
       $html .= '</caption>';
       // Fin de la gestion des entêtes
       
       $html .= '<tr>';
       $html .= '<td align="center">';
       $html .= '</td>';
       $html .= '<td align="center">';  
      
       $user->get_donnees();
       
       $html .= $user->entete_template; 
       $html .='<br><br>';
      $html .= $user->remplir_template($user->template);
      $html .= $user->fin_template; 
      $html .= afficher_barre_nav(true,false, array('val', 'type', 'id_groupe', 'gestiondroit'));
      $html .= '<br />';       
     
      //passage de l'objet en session
      $_SESSION['instance_nomenc']  =   $user;   
    
      $html .= '</td>';
      $html .= '</tr>';
      $html .= '</table>'; 
      $html.='</span><br />';
      echo $html;        
      
} else {

    // Il s'agit du traitement des donnees du POST
    
    if (isset($_SESSION['instance_nomenc'] )){
        $user   =   $_SESSION['instance_nomenc'];        
    }      
   
    // Récupération de la valeur du post
    $user->get_post_template($_POST);
    
    // compraison
    $user->comparer($user->matrice_donnees_template,$user->donnees_post);   
    
    // maj dans la base de données     
   $user->maj_bdd($user->matrice_donnees_bdd);
   unset($_SESSION['instance_nomenc']);
   
   /////////// Modif Alassane
        
       if (isset($_GET['id_groupe'])){
        $id_groupe  =$_GET['id_groupe'];
       }else{
            $id_groupe   =1; 
       }  
       
       $lib_nom_table='ADMIN_DROITS';
       $type_traitement='droit';  

       //$user = new user($_GET['id_groupe'], $lib_nom_table,$type_traitement,$_SESSION['langue'],$conn); 
       $user = new user($id_groupe, $lib_nom_table,$type_traitement,$_SESSION['langue'],$conn); 
         
       $user->champ_id      ='ID_MENU';
       $user->champ_lib     ='LIBELLE';
       $user->champ_ordre   ='TYPE';
       $user->champ_systeme = 'CODE_GROUPE';      
       
       
       // Configuration de la barre de navigation
       configurer_barre_nav($user->nb_lignes); 
       
       $html .='<br /> <br />';
       $html     .="<span class=''>";
       $html .= '<table width="50%" class="center-table">';
       
        // gestion des entêtes des menus
       $html .= '<caption>';
       $html .=  $user->recherche_libelle_page('IdEntete',$_SESSION['langue'],'droit');
       $html .= '</caption>';
       // Fin de la gestion des entêtes
       
       $html .= '<tr>';
       $html .= '<td align="center">';
       $html .= '</td>';
       $html .= '<td align="center">';  
    
       $user->get_donnees();
       $html .='<br><br>';
       $html .= $user->entete_template; 
      
      $html .= $user->remplir_template($user->template);
      $html .= $user->fin_template; 
      $html .= afficher_barre_nav(true,false, array('val', 'type', 'gestiondroit','id_groupe'));
      $html .= '<br />';       
     
      //passage de l'objet en session
      $_SESSION['instance_nomenc']  =   $user;   
    
      $html .= '</td>';
      $html .= '</tr>';
      $html .= '</table>'; 
      $html.='</span><br />';
      echo $html;
   /////////// Fin Modif Alassane
    
}
   
    
?>
