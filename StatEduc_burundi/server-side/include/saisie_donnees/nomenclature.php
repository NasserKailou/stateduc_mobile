<?php  

include $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     

set_time_limit(0);

if (count($_POST)==0) {

       $lire_dico       = 1;
	   $list_tables_parents = array();
       if (isset($_GET['lib_nom_table'])){
        		$lib_nom_table  =$_GET['lib_nom_table'];
       }else{                        
			////////////////////////
			$list_tables = array();
			foreach (array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES')) as $nom_table){            
				if ( (preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/',strtoupper($nom_table)) && !preg_match('/_'.$GLOBALS['PARAM']['SYSTEME'].'$/',strtoupper($nom_table))) or (strtoupper($nom_table)=='DICO_MESSAGE') ){               
						$list_tables[]    =   $nom_table ;
				}
			}
			sort($list_tables );
			$lib_nom_table             =       $list_tables[0]; 
		}
		
		$ColTab	=	$GLOBALS['conn']->MetaColumnNames($lib_nom_table);  
		$cles=array_keys($ColTab);
		
		if(count($cles)>3) {
			
			foreach ($cles as $chp){            
				if ( (preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$chp)) && ($chp <> $GLOBALS['PARAM']['CODE'].'_'.$lib_nom_table) ){               
					$curr_table_assoc = substr($chp,strlen($GLOBALS['PARAM']['CODE'].'_')); 
					$list_tables_parents[]    =   $curr_table_assoc ;
					//break;
				}
			}
		}
		
		if ( isset($_GET['champ_assoc']) && ($_GET['champ_assoc'] <> '') ){ 
			$nom_table_assoc 	= $_GET['table_nomenc_assoc'];
			$champ_assoc 		= $_GET['champ_assoc']; 
		}elseif ( isset($_GET['table_nomenc_assoc']) && ($_GET['table_nomenc_assoc'] <> '') ){ 
			$nom_table_assoc 	= $_GET['table_nomenc_assoc'];
			$champ_assoc 		= ''; 
		}else{ 
			$nom_table_assoc = '';
			$champ_assoc = '';      
		}
			
	  //echo '<br> aaa = '.$lib_nom_table;
       if (isset($_GET['lire_dico'])){
            $lire_dico       =$_GET['lire_dico'];
       }else {
            $lire_dico       =1;           
       }
	   
	   if (isset($_GET['secteur']) && trim($_GET['secteur']) <> ''){
            $secteur       =$_GET['secteur'];
       	}else{
			$secteur       =$_SESSION['secteur'];		
		}
       // Echange de paramètres
       $insertion_dico      = true;
       $afficher_secteur    = true;  
	   $afficher_table    = true;     
       $nomenclature = new nomenclature($secteur, $lib_nom_table,$nom_table_assoc,$champ_assoc,$afficher_table, $_SESSION['langue'],$lire_dico,$insertion_dico,$afficher_secteur,$conn); 
       if(count($list_tables_parents)>0)   $nomenclature->list_tables_parents = $list_tables_parents;
       // Configuration de la barre de navigation
       configurer_barre_nav($nomenclature->nb_lignes);

       $html .='<br /> <br />';
       $html .="<span class=''>";
       $html .= '<table width="50%" class="center-table">';
       
       // gestion des entêtes des menus
       $html .= '<caption>';
       $html .=  $nomenclature->recherche_libelle_page('IdEntete',$_SESSION['langue'],'nomenclature');
       $html .= '</caption>';
       // Fin de la gestion des entêtes
       
       $html .= '<tr>';
       $html .= '<td align="center">';
       $html .= '</td>';
       $html .= '<td align="center">';       
    
       $nomenclature->get_donnees();       
       $html .='<br>';
       $html .= $nomenclature->entete_template;        
      
      // recherche des libellés des entêtes du template
      $nomenclature->id_name        =   $nomenclature->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'nomenclature');
      $nomenclature->lib_name       =   $nomenclature->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'nomenclature');
      $nomenclature->lib_ordre      =   $nomenclature->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'nomenclature');      
       
      $html .='<br>';
      $html .= $nomenclature->remplir_template($nomenclature->template);
      $html .= $nomenclature->fin_template; 
			// avant
			//$html .= afficher_barre_nav(true,true, array('val', 'type', 'nomenclature'));
			// fin avant 
			// bass
			//val=nomenclature&lib_nom_table=TYPE_AGE&secteur=1&lire_dico=0
      $html .= afficher_barre_nav(true,true, array('val', 'type', 'nomenclature','lib_nom_table','table_nomenc_assoc','champ_assoc','secteur','lire_dico'));
			// fin bass
      $html .= '<br />';       
     
      //passage de l'objet en session
      $_SESSION['inst_nomclat']  =   $nomenclature;  
					//echo '<pre> Pre ---------------------<br>';
					//print_r($nomenclature);
     
      $html .= '</td>';
      $html .= '</tr>';
      $html .= '</table>'; 
      $html.='</span><br />';
      echo $html;        
      
} else {

		// Il s'agit du traitement des donnees du POST
		//echo '<pre> Post ---------------------<br>';
		//print_r($_POST);
	
    if (isset($_SESSION['inst_nomclat'])){        
        $nomenclature   =   $_SESSION['inst_nomclat'];        
    } 
   
   	//////// Traitements Import from an other sector
    if ( isset($_POST['do_import_nomenc']) and trim($_POST['import_from_systeme'])<>''){
		$nomenclature->do_import_nomenc($_POST['import_from_systeme']);            
    } 
	///////
	if (isset($_POST['table_nomenc_assoc']) and (trim($_POST['table_nomenc_assoc']) <> '')){
			$nom_table_assoc       =$_POST['table_nomenc_assoc'];
	}else {
			$nom_table_assoc       ='';      
	}
	$nomenclature->nom_table_assoc=$nom_table_assoc;
	  
	if (isset($_POST['champ_assoc']) and (trim($_POST['champ_assoc']) <> '')){
			$champ_assoc       =$_POST['champ_assoc'];
	}else{
			$champ_assoc       ='';		
	}  
	$nomenclature->champ_assoc=$champ_assoc;
	if($nom_table_assoc <> '') 	$nomenclature->nom_champ_assoc		=	$GLOBALS['PARAM']['CODE'].'_'.$nom_table_assoc;   

    // Récupération de la valeur du post
    $nomenclature->get_post_template($_POST);
    
    // compraison
    $nomenclature->comparer($nomenclature->matrice_donnees_template,$nomenclature->donnees_post);
         
    // maj dans la base de donées   
    
	 $nomenclature->maj_bdd($nomenclature->matrice_donnees_bdd);
	 unset( $_SESSION['inst_nomclat'] );
	 if (isset($_GET['lib_nom_table'])){
        $lib_nom_table  =$_GET['lib_nom_table'];
     }elseif( isset($_POST['table_nomenc']) and (trim($_POST['table_nomenc']) <> '')){
        $lib_nom_table  =$_POST['table_nomenc'];
     }else{                        
		////////////////////////
		$list_tables = array();
		foreach (array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES')) as $nom_table){            
            if ( (preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/',strtoupper($nom_table)) && !preg_match('/_'.$GLOBALS['PARAM']['SYSTEME'].'$/',strtoupper($nom_table))) or (strtoupper($nom_table)=='DICO_MESSAGE') ){               
				$list_tables[]    =   $nom_table ;
			}
		}
		sort($list_tables );
		$lib_nom_table             =       $list_tables[0]; 
     }       
		 
	   $ColTab	=	$GLOBALS['conn']->MetaColumnNames($lib_nom_table);  
	   $cles=array_keys($ColTab);
		
		if(count($cles)>3) {
			
			foreach ($cles as $chp){            
				if ( (preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$chp)) && ($chp <> $GLOBALS['PARAM']['CODE'].'_'.$lib_nom_table) ){               
					$curr_table_assoc = substr($chp,strlen($GLOBALS['PARAM']['CODE'].'_')); 
					$list_tables_parents[]    =   $curr_table_assoc ;
					//break;
				}
			}
		}	 
			 //echo '<br> aaa = '.$lib_nom_table;
       if (isset($_GET['lire_dico'])){
            $lire_dico       =$_GET['lire_dico'];
       }elseif (isset($_POST['lire_dico']) and (trim($_POST['lire_dico']) <> '')){
            $lire_dico       =$_POST['lire_dico'];
       }else {
            $lire_dico       =1;           
       }
       
		if (isset($_GET['secteur']) && trim($_GET['secteur']) <> ''){
            $secteur       =$_GET['secteur'];
       	}elseif (isset($_POST['systeme']) and (trim($_POST['systeme']) <> '')){
            $secteur       =$_POST['systeme'];
       	}else{
			$secteur       =$_SESSION['secteur'];		
		}
       // Echange de paramètres
       $insertion_dico      = true;
       $afficher_secteur    = true;
	   $afficher_table    = true;
	   
	  
       $nomenclature 		= new nomenclature($secteur, $lib_nom_table,$nom_table_assoc,$champ_assoc,$afficher_table, $_SESSION['langue'], $lire_dico, $insertion_dico, $afficher_secteur,$conn); 
       if(count($list_tables_parents)>0)   $nomenclature->list_tables_parents = $list_tables_parents;
       // Configuration de la barre de navigation
       configurer_barre_nav($nomenclature->nb_lignes);

       $html .='<br /> <br />';
       $html .="<span class=''>";
       $html .= '<table width="50%" class="center-table">';
       
       // gestion des entêtes des menus
       $html .= '<caption>';
       $html .=  $nomenclature->recherche_libelle_page('IdEntete',$_SESSION['langue'],'nomenclature');
       $html .= '</caption>';
       // Fin de la gestion des entêtes
       
       $html .= '<tr>';
       $html .= '<td align="center">';
       $html .= '</td>';
       $html .= '<td align="center">';       
    
       $nomenclature->get_donnees();       
       
	   $html .='<br>';
       $html .= $nomenclature->entete_template;        
      
      // recherche des libellés des entêtes du template
      $nomenclature->id_name        =   $nomenclature->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'nomenclature');
      $nomenclature->lib_name       =   $nomenclature->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'nomenclature');
      $nomenclature->lib_ordre      =   $nomenclature->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'nomenclature');      
       
      $html .='<br>';
      $html .= $nomenclature->remplir_template($nomenclature->template);
      $html .= $nomenclature->fin_template; 
			// avant
			//$html .= afficher_barre_nav(true,true, array('val', 'type', 'nomenclature'));
			// fin avant 
			// bass
			//val=nomenclature&lib_nom_table=TYPE_AGE&secteur=1&lire_dico=0
      $html .= afficher_barre_nav(true,true, array('val', 'type', 'nomenclature','lib_nom_table','table_nomenc_assoc','champ_assoc','secteur','lire_dico'));
			// fin bass
      $html .= '<br />';       
     
      //passage de l'objet en session
      $_SESSION['inst_nomclat']  =   $nomenclature;   
    
      $html .= '</td>';
      $html .= '</tr>';
      $html .= '</table>'; 
      $html.='</span><br />';
      echo $html;        

////////////////////////////////////////////////////// fin bass 
}
?>