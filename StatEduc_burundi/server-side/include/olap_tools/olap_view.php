<?php lit_libelles_page('/olap_view.php');
	include_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/olap_view.class.php'; 
    include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/olap.set_obj_serveur.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php if(isset($_GET['valView']) && $_GET['valView']=="cub_locaux")
    {
        $requete="UPDATE DICO_OLAP_AUTRE_CONNEXION SET DEFAUT = NULL WHERE SGBD = 'OlapServer'"; 
    }
    elseif(isset($_GET['valView']) && $_GET['valView']=="cub_serveur")
    {
        $requete="UPDATE DICO_OLAP_AUTRE_CONNEXION SET DEFAUT = 1 WHERE SGBD = 'OlapServer'";
    }
    if ($GLOBALS['conn_dico']->Execute($requete) === false){
        echo '<br>'.$requete.'<br>';
    }
    
    // Identification du type sgbd par défaut
    $requete='SELECT SGBD, DB FROM DICO_OLAP_AUTRE_CONNEXION WHERE DEFAUT=1';    
    $result = $GLOBALS['conn_dico']->GetAll($requete);
    if (is_array($result) && count($result)){
        
        $type_sgbd =$result[0]['SGBD']; 
        
        if (isset($result[0]['DB']) && $result[0]['DB']<>'')
            $default_db = $result[0]['DB'];
        else
            $default_db = '';
    }
    else{
        $type_sgbd ='OlapFile'; 
    }
   
   // Recupération de la liste des cubes olap en fonction de la base de données par défaut ou non
    $Serveur_olap = new set_obj_serveur($default_db);
	$Serveur_olap->set_olap_base();
    
	$olap_view = new olap_view($type_sgbd,$default_db) ;
	
	// utilisation de la connexion en cours comme source OLAP
	$olap_view->use_current_cnx_for_OLAP = true ;
	
	// base(s) OLAP à utiliser
    if ($type_sgbd =='OlapFile') 
	    $olap_view->une_seule_base_OLAP 	 = true; // Cube olap
    else
        $olap_view->une_seule_base_OLAP 	 = false; // Server Olap
    if ($type_sgbd <>'OlapFile') 
	        $olap_view->tab_cubes_base 			 = $Serveur_olap->liste_cubes ; // listes des cubes des bases OLAP utilisées
        
	//echo '<pre>';
    //print_r($Serveur_olap->liste_cubes);
    $olap_view->run() ;
?>

