<?php class chaine {

    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $code_systeme;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $nom_table;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $bln_lie_systeme;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $defaut_langue;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $valeurs_nomenclatures   = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $donnees_bdd = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $donnees_post = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $matrice_donnees_template = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $matrice_donnees_bdd= array();
    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $matrice_donnees = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $template ;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $entete_template;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $fin_template;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $nb_lignes = 8;   
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $VARS_GLOBALS = array();
    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_id;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_lib;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_ordre; 
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_systeme; 
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $valeur_filtre = 255;    
    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $list_tables = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $list_secteurs = array();
    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_name;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_name;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_ordre;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_entete;
        
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function __construct($id_systeme, $lib_nom_table, $langue,$conn){
            $champ_systeme 							=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
            $this->code_systeme         =   $id_systeme;
            $this->nom_table            =   $lib_nom_table;            
            $this->defaut_langue        =   $langue;
            $this->conn                 =   $conn;            
            $this->template             =   file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'nomenclature.html');            
            $this->champ_id             =   $GLOBALS['PARAM']['CODE'].'_'.$lib_nom_table;
            $this->champ_lib            =   $GLOBALS['PARAM']['LIBELLE'].'_'.$lib_nom_table;
            $this->champ_ordre          =   $GLOBALS['PARAM']['ORDRE'].'_'.$lib_nom_table; 
            
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function __wakeup(){
        $this->conn =   $GLOBALS['conn'];
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_donnees(){   
    
        $requete    =   'SELECT '.$this->champ_id.','.$this->champ_lib.','.$this->champ_ordre.' FROM '.$this->nom_table.
                    ' WHERE '.$this->champ_systeme.'='.$this->code_systeme;
        
        $tab_donnees    =   array(); 
        try{
            $valeurs = $this->conn->GetAll($requete);
            if (!is_array($valeurs))            
                throw new Exception('ERR_SQL');
            if (count($valeurs)>0) {
                for ($i=0;$i<count($valeurs);$i++){
                    $tab_donnees []    =   $valeurs[$i];
                }
            }
        }
        catch (Exception $e) {
            $erreur = new erreur_manager($e,$requete);
        }        
        
           
        $this->VARS_GLOBALS['donnees_bdd']   =   $tab_donnees;        
        $this->limiter_affichage();
        $this->init_liste_table();
        
        
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function transformer_donnees(){
       $this->matrice_donnees_template = array();
       foreach ($this->matrice_donnees as $ligne_donnes) {
            $donnees_transformees = array();
            if (strlen($this->champ_id)>30){
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9'){
                    $donnees_transformees[0] = $ligne_donnes[$this->champ_id];
                }else{
                $donnees_transformees[0] = $ligne_donnes[substr($this->champ_id,0,31)];
                }                
            }else{            
                $donnees_transformees[0] = $ligne_donnes[$this->champ_id ];
            }
            
            if (strlen($this->champ_lib)>30){
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9'){
                    $donnees_transformees[1] = $ligne_donnes[$this->champ_lib];
                }else{
                $donnees_transformees[1] = $ligne_donnes[substr($this->champ_lib,0,31) ];
                }                
            }else{            
                $donnees_transformees[1] = $ligne_donnes[$this->champ_lib ];
            }
            
            if (strlen($this->champ_ordre)>30){
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9'){
                    $donnees_transformees[2] = $ligne_donnes[$this->champ_ordre];
                }else{
                $donnees_transformees[2] = $ligne_donnes[substr($this->champ_ordre,0,31) ];
                }                
            }else{            
                $donnees_transformees[2] = $ligne_donnes[$this->champ_ordre ];
            }
            
            /*$donnees_transformees[0] = $ligne_donnes[$this->champ_id ];
            $donnees_transformees[1] = $ligne_donnes[$this->champ_lib ];            
            $donnees_transformees[2] = $ligne_donnes[$this->champ_ordre ];*/
            $this->matrice_donnees_template[] = $donnees_transformees; 
            
        }
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function limiter_affichage(){
        if(!isset($GLOBALS['nbre_total_enr']))
                    $GLOBALS['nbre_total_enr'] = count( $this->VARS_GLOBALS['donnees_bdd'] );                    
                    
                    $mat = array_slice( $this->VARS_GLOBALS['donnees_bdd'], $GLOBALS['debut'], $GLOBALS['cfg_nbres_ppage']);               
                
                foreach( $mat as $ligne_donnees){
                    $this->matrice_donnees[] = $ligne_donnees;
                }
                $GLOBALS['nbenr'] = count($this->matrice_donnees);
                
                // transfromation des donnees
                $this->transformer_donnees();
    }    
   
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function remplir_template($template){
        if (is_array($this->matrice_donnees)){              
            
            if (strlen($this->champ_id)>30){
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9'){
                    $nom_champ_id = $this->champ_id ;
                }else{
                    $nom_champ_id = substr($this->champ_id,0,31) ;
                }                
            }else{            
                $nom_champ_id = $this->champ_id;
            }            
           
            if (strlen($this->champ_lib)>30){
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9'){                    
                    $nom_champ_lib = $this->champ_lib ;                   
                }else{
                    $nom_champ_lib= substr($this->champ_lib,0,31) ;                    
                }                
            }else{            
                $nom_champ_lib = $this->champ_lib;
            }
            
            if (strlen($this->champ_ordre)>30){
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9'){
                    $nom_champ_ordre= $this->champ_ordre ;
                }else{
                    $nom_champ_ordre = substr($this->champ_ordre,0,31) ;
                }                
            }else{            
                $nom_champ_ordre = $this->champ_ordre;
            }             
            
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                // cas des champs id                   
                if (isset($this->matrice_donnees[$ligne][$nom_champ_id ])){
                  //  echo 'passé la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$nom_champ_id ];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'CODE_'.$ligne} = ''.$val_champ_base.'';                                      

                }else{
                    ${'CODE_'.$ligne} = '';	
                }
                
                // cas des champs libellé
                if (isset($this->matrice_donnees[$ligne][$nom_champ_lib])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$nom_champ_lib];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'LIBELLE_'.$ligne} = ''.$val_champ_base.'';
                }else{
                    ${'LIBELLE_'.$ligne} = '';	
                }
                
                // cas des champs ordre
                if (isset($this->matrice_donnees[$ligne][$nom_champ_ordre])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$nom_champ_ordre];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'ORDRE_'.$ligne} = ''.$val_champ_base.'';                    
                }else{
                    ${'ORDRE_'.$ligne} = '';	
                }
                
            }
            // entete du template
            ${'id_name'}= $this->id_name;
            ${'lib_name'}=$this->lib_name;
            ${'lib_ordre'}=$this->lib_ordre;
            ${'lib_entete'}=$this->lib_entete;
        }              
      
        eval('$result = sprintf(\'%s\', "'.str_replace('"','\"',$template).'");');
        return $result;
    }
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function recherche_libelle_page($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				$requete 	= "SELECT LIBELLE
										FROM DICO_LIBELLE_PAGE 
										WHERE CODE_LIBELLE='".$code."' And CODE_LANGUE='".$langue."'
										AND NOM_PAGE='".$table."'";
				
				try {
                    $all_res	= $GLOBALS['conn_dico']->GetAll($requete);
                    if (!is_array($all_res))                    
                        throw new Exception('ERR_SQL');                     
                }
                catch(Exception $e){
                    $erreur = new erreur_manager($e,$requete);
                }                 
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function recherche_libelle($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				// Positionnement de la connexion 
				// Modif pour externalisation de DICO
				if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					$conn                 =   $GLOBALS['conn'];
				} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					$conn                 =   $GLOBALS['conn_dico']; 
				}				
				$requete 	= " SELECT LIBELLE
								FROM DICO_TRADUCTION 
								WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
								AND NOM_TABLE='".$table."'";
				
				try {
                    $all_res	= $conn->GetAll($requete);
                    if (!is_array($all_res))                    
                        throw new Exception('ERR_SQL');                     
                }
                catch(Exception $e){
                    $erreur = new erreur_manager($e,$requete);
                }
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_post_template($matr){      
              
        if (is_array($matr)){              
            
            $max_cle_incr = $this->get_cle_max();            
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                $champ_id       = 'CODE_'.$ligne;
                $champ_lib      = 'LIBELLE_'.$ligne;
                $champ_ordre    = 'ORDRE_'.$ligne;
                $delete         = 'DELETE_'.$ligne;               
                
                if (isset($matr[$champ_id]) && $matr[$champ_id]<>''){                   
                    if (!isset($matr[$delete])) {
                        $donnees_ligne  =   array();
                        $donnees_ligne [] = $matr[$champ_id];
                        if (isset($matr[$champ_lib]) && $matr[$champ_lib]<>'' ){
                            $donnees_ligne [] = $matr[$champ_lib];
                        }else{
                            $donnees_ligne []='\'\'';
                        }
                        
                        if (isset($matr[$champ_ordre]) && $matr[$champ_ordre]<>'' ){
                            $donnees_ligne [] = $matr[$champ_ordre];
                        }else{
                            $donnees_ligne []='\'\'';
                        }                                        
                        
                        
                        $this->donnees_post[]= $donnees_ligne;
                    }
                }else{
                    // traitement des nouvelles creations de données dans ce cas il faut nécessairement saisir les libellés
                    if (isset($matr[$champ_lib]) && $matr[$champ_lib]<>''){                      
                        // il faut trouver la valeur max dans la table de nomenclature
                        $max_cle_incr++;
                        $donnees_ligne  =   array();
                        $donnees_ligne [] = $max_cle_incr;
                        $donnees_ligne [] = $matr[$champ_lib];
                        //$donnees_ligne [] = $matr[$champ_ordre]; 
                        if (isset($matr[$champ_ordre]) && $matr[$champ_ordre]<>'' ){
                            $donnees_ligne [] = $matr[$champ_ordre];
                        }else{
                            $donnees_ligne []='\'\'';
                        }                        
                        $this->donnees_post[]= $donnees_ligne;                        
                        
                    }
                    
                }
                
            }
        }
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_cle_max(){
        $max_return = 0;
        $sql = 'SELECT  MAX('.$this->champ_id.') as MAX_INSERT FROM  '.$this->nom_table.
               ' WHERE '.$this->champ_id.'<>'.$this->valeur_filtre ;       
        // Gestion des erreurs lors de l'exécution de la requête SQL
        try{
            if (($rs =  $this->conn->Execute($sql))===false) {   
                throw new Exception('ERR_SQL');  
            }
            if (!$rs->EOF) {                  
                $max_return = $rs->fields['MAX_INSERT'];                  
            }
        }
        catch(Exception $e) {
            $erreur = new erreur_manager($e,$sql);
        }
        return($max_return);
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function init_liste_table(){        

        $entete     =   '<script type='.'"'.'text/javascript'.'"'.'>';	        
        $entete     .=  " function Alert_Supp(checkbox){ ";
        $entete     .=  "var chaine_eval ='document.form1.'+checkbox+'.checked == true';";       
        $entete     .=  " if (eval(chaine_eval)){ " ;
        $mess_alert		 	= $this->recherche_libelle(110,$_SESSION['langue'],'DICO_MESSAGE');
        $mess_alert			= addslashes($mess_alert);
        $entete     .=  "   alert ('$mess_alert');";
        $entete     .=  "}  }";
        $entete     .=  " </script>";
        
        //$entete     .= "<br />";        
        $entete     .="<form name ='form1' action='".$_SERVER['REQUEST_URI']."' method='post'>";        
        $entete     .="<span class=''>";
        
        $entete     .= '</span>'; 
		//$entete     .= '</span><br />'; 
        $this->entete_template = $entete;
        $this->fin_template="</div><div><br><INPUT TYPE='SUBMIT' VALUE='".$this->recherche_libelle_page('Valider',$_SESSION['langue'],'nomenclature')."'></div></span></Form>";         
     
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function comparer ($matr1,$matr2){
	
		// Cette fonction permet de faire la comparaison matricielle complexe		
		//$indice_cle	=	$this->nb_cles - 1;		
		
		$indice_cle	=	0;
				
		$result 	=	array();
		$i = 0;
		foreach ($matr2 as $elt)
		{
				// cette variable contient la clé identifiant de l'élément matriciell
				
				$cle	=	associer_identifiant($elt,$indice_cle);
								
				$action	=	existe_element_grille_action($cle,$elt,$indice_cle,$matr1);		
				//echo $action. '<br />';
				switch ($action)
				{
					case 'I':
					case 'U':				
						for ($i=0;$i<count($elt);$i++){
							$tmp[$i]	=	$elt[$i];		
						}
						$tmp[$i++]	=	$action;
						$result []	=	$tmp;
						break;
					
				}	
		}
		
		foreach ($matr1 as $elt)
		{
				// cette variable contient la clé identifiant de l'élément matriciell
				
				$cle	=	associer_identifiant($elt,$indice_cle);
				$action	=	existe_element_grille_action($cle,$elt,$indice_cle,$matr2);
				switch ($action)
				{
					case 'I':
						
						$action		=	'D'	;				
						for ($i=0;$i<count($elt);$i++){
							$tmp[$i]	=	$elt[$i];		
						}
						$tmp[$i++]	=	$action;
						$result []	=	$tmp;
						break;
				}	
		}		
		
		//return $result;
		$this->matrice_donnees_bdd	=	$result;
	}	
    
    public function get_ord_max(){
		$max_return = 0;
		$sql = ' SELECT  MAX('.$this->champ_ordre.') as MAX_INSERT FROM  '.$this->nom_table.
				 ' WHERE '.$this->champ_id.'<>'.$this->valeur_filtre ;
		// Gestion des erreurs lors de l'exécution de la requête SQL
		 //die($this->conn->host); 
		try{
			if (($rs =  $this->conn->Execute($sql))===false) {   
				throw new Exception('ERR_SQL');  
			}
			if (!$rs->EOF) {                  
				$max_return = $rs->fields['MAX_INSERT'];                  
			}
		}
		catch(Exception $e) {
			$erreur = new erreur_manager($e,$sql);
		}
		return($max_return);
	}
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function maj_bdd($matr){        
        if (is_array($matr)){                
        	foreach ($matr as $tab){
                $action = $tab[sizeof($tab)-1];
                //echo 'valeur action = ' .$action;
				if($tab[2]=="''") $tab[2] = $this->get_ord_max()+1;
                switch ($action){
                    case 'I':
                    
                        $sql    =   'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->champ_lib.','.$this->champ_ordre.','.$this->champ_systeme.')'.
                                            ' VALUES ('.$tab[0].','.$this->conn->qstr($tab[1]).','.$tab[2].','.$this->code_systeme.')'; 
                        try{
                            if ($this->conn->Execute($sql)==false)
                                throw new Exception('ERR_SQL');
                        }
                        catch (Exception $e){
                             $erreur = new erreur_manager($e,$sql);
                        }
                        break;
                    case 'U':
                        // Mise à jour des données sur les nomenclatures et dans la traduction
                        $sql = 'UPDATE '.$this->nom_table.' SET '.$this->champ_lib.'='.$this->conn->qstr($tab[1]).','.$this->champ_ordre.'='.$tab[2].
                               ' WHERE '.$this->champ_id.'='.$tab[0]. ' AND '.$this->champ_systeme.'='.$this->code_systeme ; 
                               
                        try{
                            if ($this->conn->Execute($sql)==false)
                                throw new Exception('ERR_SQL');
                        }
                        catch (Exception $e){
                             $erreur = new erreur_manager($e,$sql);
                        }                     
                        break;
                    case 'D' : 
                        
                        $sql = 'DELETE FROM '.$this->nom_table.' WHERE '.$this->champ_id.'='.$tab[0].' AND '.$this->champ_systeme.'='.$this->code_systeme;                        
                        
                        try{
                            if ($this->conn->Execute($sql)==false)
                                throw new Exception('ERR_SQL');
                        }
                        catch (Exception $e){
                             $erreur = new erreur_manager($e,$sql);
                        }  
                        break;
            }
            }
         }   
    }
    

}
?>
