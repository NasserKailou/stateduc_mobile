<?php class user {

    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $code_groupe;
    	
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
		public $defaut_langue;
    	
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
		public $donnees_post_excel = array();
    	
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
		public $champ_id ;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_lib ;
    	
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
		public $champ_name_user;
		
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_email_user;  
		 
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_tel_user; 
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_systeme ;
		public $champ_user_parent;   
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $type_traitement;
    
    
    	
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
		public $list_groupes = array();
    
    // utiliser dans l'entete du template pour la traduction
    	
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
		public $lib_name_long;
		
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_email;
		
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_tel;
		
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
		* Attribut : 
		*  pointeur du fichier de log
		* @var mixed
		* @access public
		*/   
		public $fp ;
		
		/**
		* Attribut : 
        * chemin d'acc�s du fichier de log
		* @var string
		* @access public
		*/   
		public $chemin_log ;
        
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function __construct($id_groupe, $lib_nom_table,$type_traitement,$langue,$conn){
            $this->code_groupe          =   $id_groupe;
            $this->nom_table            =   $lib_nom_table;
            $this->type_traitement      =   $type_traitement;            
            $this->defaut_langue        =   $langue;
            $this->conn                 =   $conn; 
            
             
            if ($this->type_traitement=='user'){            
                $this->template             =   file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'user.html');  
            }else{
                $this->template             =   file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'droit.html'); 
                $this->nb_lignes            =40;                
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
		public function __wakeup(){
        	if($GLOBALS['placer_conn_dico'])
				$this->conn =   $GLOBALS['conn_dico'];
			else
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
        if ($this->type_traitement=='user') {
			$req_recherche = empty($this->donnees_post['filtre_recherche']['NOM_LONG_USER_R'])?'':' ('.$this->champ_name_user.' LIKE '.$this->conn->qstr('%'.$this->donnees_post['filtre_recherche']['NOM_LONG_USER_R'].'%').') AND ';
			$req_recherche .= empty($this->donnees_post['filtre_recherche']['EMAIL_USER_R'])?'':'('.$this->champ_email_user.' LIKE '.$this->conn->qstr('%'.$this->donnees_post['filtre_recherche']['EMAIL_USER_R'].'%').') AND ';
			$req_recherche .= empty($this->donnees_post['filtre_recherche']['TEL_USER_R'])?'':'('.$this->champ_tel_user.' LIKE '.$this->conn->qstr('%'.$this->donnees_post['filtre_recherche']['TEL_USER_R'].'%').') AND ';
			$req_recherche .= empty($this->donnees_post['filtre_recherche']['LIBELLE_R'])?'':'('.$this->champ_lib.' LIKE '.$this->conn->qstr('%'.$this->donnees_post['filtre_recherche']['LIBELLE_R'].'%').') AND '; 
			
			//echo $req_recherche ;
			if($_SESSION['groupe'] == 1){
				$requete ='SELECT '.$this->champ_id.','.$this->champ_name_user.','.$this->champ_email_user.','.$this->champ_tel_user.','.$this->champ_lib.','.$this->champ_ordre.' FROM '.$this->nom_table. ' WHERE '.$req_recherche.
						$this->champ_systeme.'='.$this->code_groupe. ' ORDER BY '.$this->champ_id;
			}elseif($_SESSION['groupe'] <> $_SESSION['id_groupe']){
				$requete ='SELECT '.$this->champ_id.','.$this->champ_name_user.','.$this->champ_email_user.','.$this->champ_tel_user.','.$this->champ_lib.','.$this->champ_ordre.' FROM '.$this->nom_table. ' WHERE '.$req_recherche.
						$this->champ_systeme.'='.$this->code_groupe.' AND '.$this->champ_user_parent.'='.$_SESSION['code_user']. ' ORDER BY '.$this->champ_id;
			}else{
				$requete ='SELECT '.$this->champ_id.','.$this->champ_name_user.','.$this->champ_email_user.','.$this->champ_tel_user.','.$this->champ_lib.','.$this->champ_ordre.' FROM '.$this->nom_table. ' WHERE '.$req_recherche.
						$this->champ_systeme.'='.$this->code_groupe.' AND '.$this->champ_id.'='.$_SESSION['code_user'].' ORDER BY '.$this->champ_id;
			}
        }elseif ($this->type_traitement=='droit') {
        
            $requete ='SELECT DISTINCT    A.ID_MENU AS '.$this->champ_id.', B.LIBELLE AS '.$this->champ_lib.', '.'\''.'checked'.'\''.' AS '.$this->champ_ordre.
                    ' FROM         ADMIN_DROITS AS A, DICO_TRADUCTION AS B'.
                    ' WHERE  A.ID_MENU = B.CODE_NOMENCLATURE '.
                    ' AND     (A.CODE_GROUPE ='.$this->code_groupe.' ) AND (B.CODE_LANGUE =\''.$this->defaut_langue.'\') AND (B.NOM_TABLE ='.'\''.'DICO_MENU'.'\''.')';                    
                    
            
            $requeteunion =' UNION  SELECT DISTINCT A.ID AS '.$this->champ_id.', B.LIBELLE AS '.$this->champ_lib.', '.'\''.'\''.' AS '.$this->champ_ordre.
                        ' FROM DICO_MENU AS A, DICO_TRADUCTION AS B  WHERE A.ID = B.CODE_NOMENCLATURE '. 
                        ' AND (B.CODE_LANGUE =\''.$this->defaut_langue.'\')'.
                        ' AND (B.NOM_TABLE ='.'\''.'DICO_MENU'.'\''.')'.
                        ' AND A.ID NOT IN (SELECT DISTINCT ID_MENU FROM ADMIN_DROITS WHERE CODE_GROUPE='.$this->code_groupe.' )';
                        
            $requete .=$requeteunion;
        }
		//echo '<br/>'.$requete;
        // Gestion des erreurs lors de l'ex�cution des requ�tes SQL    
        try{
            $tab_donnees    =   array(); 
            $valeurs = $GLOBALS['conn_dico']->GetAll($requete);
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
            if ($this->type_traitement=='user'){
				$donnees_transformees[0] = $ligne_donnes[$this->champ_id ];
				$donnees_transformees[1] = $ligne_donnes[$this->champ_name_user ];
				$donnees_transformees[2] = $ligne_donnes[$this->champ_email_user ];
				$donnees_transformees[3] = $ligne_donnes[$this->champ_tel_user ];
				$donnees_transformees[4] = $ligne_donnes[$this->champ_lib ];
                $donnees_transformees[5] = $ligne_donnes[$this->champ_ordre ];
            }else{
				$donnees_transformees[0] = $ligne_donnes[$this->champ_id ];
				$donnees_transformees[1] = $ligne_donnes[$this->champ_lib ];
                if (isset($ligne_donnes[$this->champ_ordre ]) && $ligne_donnes[$this->champ_ordre ]=='checked'){
                    $donnees_transformees[2] = $ligne_donnes[$this->champ_ordre ];                    
                }else{                    
                    $donnees_transformees[2] = '\''.'\'';
                }
            }
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
            
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                // cas des champs id                   
                if (isset($this->matrice_donnees[$ligne][$this->champ_id ])){
                  //  echo 'pass� la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_id ];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'CODE_'.$ligne} = ''.$val_champ_base.'';                                      

                }else{
                    ${'CODE_'.$ligne} = '';	
                }
				
				// cas des champs nom user                   
                if (isset($this->matrice_donnees[$ligne][$this->champ_name_user ])){
                  //  echo 'pass� la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_name_user ];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'NOM_LONG_USER_'.$ligne} = ''.$val_champ_base.'';                                      

                }else{
                    ${'NOM_LONG_USER_'.$ligne} = '';	
                }
				
				// cas des champs email user                   
                if (isset($this->matrice_donnees[$ligne][$this->champ_email_user ])){
                  //  echo 'pass� la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_email_user ];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'EMAIL_USER_'.$ligne} = ''.$val_champ_base.'';                                      

                }else{
                    ${'EMAIL_USER_'.$ligne} = '';	
                }
				
				// cas des champs tel user                   
                if (isset($this->matrice_donnees[$ligne][$this->champ_tel_user ])){
                  //  echo 'pass� la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_tel_user ];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'TEL_USER_'.$ligne} = ''.$val_champ_base.'';                                      

                }else{
                    ${'TEL_USER_'.$ligne} = '';	
                }
                
                // cas des champs libell�
                if (isset($this->matrice_donnees[$ligne][$this->champ_lib])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_lib];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'LIBELLE_'.$ligne} = ''.$val_champ_base.'';
                }else{
                    ${'LIBELLE_'.$ligne} = '';	
                }
                
                // cas des champs ordre
                if ($this->type_traitement=='user'){
                    if (isset($this->matrice_donnees[$ligne][$this->champ_ordre])){
                        $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_ordre];
                        //$val_champ_base = addslashes($val_champ_base);                    
                        ${'ORDRE_'.$ligne} = ''.$val_champ_base.'';                    
                    }else{
                        ${'ORDRE_'.$ligne} = '';	
                    }
                }else{
                     // traitement du cas des cas � cocher pour la gestion des de l'attribution des droits
                    if (isset($this->matrice_donnees[$ligne][$this->champ_ordre])){
                        $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_ordre];
                        //$val_champ_base = addslashes($val_champ_base);                    
                        ${'CHECKED_'.$ligne} = ''.$val_champ_base.'';                    
                    }else{
                        ${'CHECKED_'.$ligne} = '';	
                    }
                }
				if($_SESSION['groupe'] <> 1 && $_SESSION['groupe'] == $_SESSION['id_groupe']){
					$readonly = ' readonly';
					$disabled = ' disabled';
				}else{
					$readonly = '';
					$disabled = '';
				}
				// cas des bouton user privilege management                   
                if (isset($GLOBALS['PARAM']['USER_PRIVILEGE_MANAGEMENT']) && $GLOBALS['PARAM']['USER_PRIVILEGE_MANAGEMENT'] && isset($this->matrice_donnees[$ligne][$this->champ_id ])){
                  //  echo 'pass� la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_id ];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'USER_PRIVILEGE_'.$ligne} = '<input name="button" type="button" onclick="OpenPopupUserPriv(CODE_'.$ligne.'.value, LIBELLE_'.$ligne.'.value, id_groupe.value)" value="'.$this->recherche_libelle_page('UserPriv',$_SESSION['langue'],'user').'" />';                                      

                }else{
                    ${'USER_PRIVILEGE_'.$ligne} = '';	
                }
            }
            // entete du template
            ${'id_name'}= $this->id_name;
            ${'lib_name'}=$this->lib_name;
            ${'lib_name_long'}=$this->lib_name_long;
            ${'lib_email'}=$this->lib_email;
            ${'lib_tel'}=$this->lib_tel;
            ${'lib_entete'}=$this->lib_entete;
            if ($this->type_traitement=='user'){
                ${'lib_ordre'}=$this->lib_ordre;
			
			    ${'NOM_LONG_USER_R'}=$this->donnees_post['filtre_recherche']['NOM_LONG_USER_R'];
				${'EMAIL_USER_R'}=$this->donnees_post['filtre_recherche']['EMAIL_USER_R'];
				${'TEL_USER_R'}=$this->donnees_post['filtre_recherche']['TEL_USER_R'];
				${'LIBELLE_R'}=$this->donnees_post['filtre_recherche']['LIBELLE_R'];
				${'boutton_filtrer'}=$this->recherche_libelle_page('Filtrer',$_SESSION['langue'],'user');
            }
        }       
       
      // return eval("echo \"$template\";");
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
			// permet de r�cup�rer le libell� dans la table de traduction
			// en fonction de la langue et de la table  aussi
			$requete 	= 'SELECT LIBELLE
									FROM DICO_LIBELLE_PAGE 
									WHERE CODE_LIBELLE='.'\''.$code.'\''.' And CODE_LANGUE='.'\''.$langue.'\''
									.'AND NOM_PAGE='.'\''.$table.'\'';
			
			// Gestion des erreurs lors de l'ex�cution de la requ�te SQL
			
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
			
			if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
				$conn                 =   $GLOBALS['conn'];
			} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
				$conn                 =   $GLOBALS['conn_dico']; 
			}
			// permet de r�cup�rer le libell� dans la table de traduction
			// en fonction de la langue et de la table  aussi
			$requete 	= 'SELECT LIBELLE
									FROM DICO_TRADUCTION 
									WHERE CODE_NOMENCLATURE='.$code.' And CODE_LANGUE='.'\''.$langue.'\''
									.'AND NOM_TABLE='.'\''.$table.'\'';
			
			// Gestion des erreurs lors de l'ex�cution de la requ�te SQL
			
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
            //echo "<pre>"; print_r($matr);
            $max_cle_incr = $this->get_cle_max();              
            $this->donnees_post = array();
			
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                $champ_id       = 'CODE_'.$ligne;
				$champ_lib_name_long = 'NOM_LONG_USER_'.$ligne;
            	$champ_lib_email = 'EMAIL_USER_'.$ligne;
            	$champ_lib_tel = 'TEL_USER_'.$ligne;
                $champ_lib      = 'LIBELLE_'.$ligne;
                $champ_ordre    = 'ORDRE_'.$ligne;
                $delete         = 'DELETE_'.$ligne;               
                
                if (isset($matr[$champ_id]) && $matr[$champ_id]<>''){                   
                    if($this->type_traitement=='user'){
                        if (!isset($matr[$delete])) {
                            $donnees_ligne  =   array();
                            $donnees_ligne [] = $matr[$champ_id];
							
                            if (isset($matr[$champ_lib_name_long]) && $matr[$champ_lib_name_long]<>'' ){
                                $donnees_ligne [] = $matr[$champ_lib_name_long];
                            }else{
                                $donnees_ligne []='';
                            }
							
                            if (isset($matr[$champ_lib_email]) && $matr[$champ_lib_email]<>'' ){
                                $donnees_ligne [] = $matr[$champ_lib_email];
                            }else{
                                $donnees_ligne []='';
                            }
							
                            if (isset($matr[$champ_lib_tel]) && $matr[$champ_lib_tel]<>'' ){
                                $donnees_ligne [] = $matr[$champ_lib_tel];
                            }else{
                                $donnees_ligne []='';
                            }
							
                            if (isset($matr[$champ_lib]) && $matr[$champ_lib]<>'' ){
                                $donnees_ligne [] = $matr[$champ_lib];
                            }else{
                                $donnees_ligne []='';
                            }
                            if (isset($matr[$champ_ordre]) && $matr[$champ_ordre]<>'' ){
                                $donnees_ligne [] = $matr[$champ_ordre];
                            }else{
                                $donnees_ligne []='\'\'';
                            }                                        
                            
                            
                            $this->donnees_post[]= $donnees_ligne;
                        }
                    }else{
                        // traitement de la r�cup�ration des droits associ� au groupe
                        if (isset($matr[$delete])) {
                            // cas ou la valeur est associ�
                            $donnees_ligne  =   array();
                            $donnees_ligne [] = $matr[$champ_id];
                            if (isset($matr[$champ_lib]) && $matr[$champ_lib]<>'' ){
                                $donnees_ligne [] = $matr[$champ_lib];
                            }else{
                                $donnees_ligne []='\'\'';
                            }
                            
                            if ($matr[$delete]<>'' && $matr[$delete]==1){
                                $donnees_ligne [] = 'checked';
                            }else{
                                $donnees_ligne [] ='\'\'';                                
                            }                            
                            $this->donnees_post[] = $donnees_ligne;
                        }else {
                            // cas ou la valeur n'est pas associ�
                            $donnees_ligne  =   array();
                            $donnees_ligne [] = $matr[$champ_id];
                            if (isset($matr[$champ_lib]) && $matr[$champ_lib]<>'' ){
                                $donnees_ligne [] = $matr[$champ_lib];
                            }else{
                                $donnees_ligne []='\'\'';
                            }
                            
                            $donnees_ligne []='\'\'';                              
                            
                            $this->donnees_post[]= $donnees_ligne;
                            
                        }

                    }
                    
                    
                    
                }else{
                    // traitement des nouvelles creations de donn�es dans ce cas il faut n�cessairement saisir les libell�s
                    if (isset($matr[$champ_lib]) && $matr[$champ_lib]<>''){                      
                        // il faut trouver la valeur max dans la table de nomenclature
                        $max_cle_incr++;
                        $donnees_ligne  =   array();
                        $donnees_ligne [] = $max_cle_incr;
                        if($this->type_traitement=='user'){
                            if (isset($matr[$champ_lib_name_long]) && $matr[$champ_lib_name_long]<>'' ){
                                $donnees_ligne [] = $matr[$champ_lib_name_long];
                            }else{
                                $donnees_ligne []='';
                            }
    					
                            if (isset($matr[$champ_lib_email]) && $matr[$champ_lib_email]<>'' ){
                                $donnees_ligne [] = $matr[$champ_lib_email];
                            }else{
                                $donnees_ligne []='';
                            }
    					
                            if (isset($matr[$champ_lib_tel]) && $matr[$champ_lib_tel]<>'' ){
                                $donnees_ligne [] = $matr[$champ_lib_tel];
                            }else{
                                $donnees_ligne []='';
                            }
                        }
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
        //echo "<pre>"; print_r($this->donnees_post);
    }
    
	/**
	* METHODE : 
	* <pre>
	* 
	* </pre>
	* @access public
	* 
	*/
	public function get_post_filtre($matr) {              
		if (is_array($matr)){   
			$this->donnees_post = array();
			$donnees_filtre = array();
			$donnees_filtre['NOM_LONG_USER_R'] = $matr['NOM_LONG_USER_R'];
			$donnees_filtre['EMAIL_USER_R'] = $matr['EMAIL_USER_R'];
			$donnees_filtre['TEL_USER_R'] = $matr['TEL_USER_R'];
			$donnees_filtre['LIBELLE_R'] = $matr['LIBELLE_R'];
			$this->donnees_post['filtre_recherche'] = $donnees_filtre;
		}
	}
	
	public function get_excel_data($matr, $nbrow){      
              
        if (is_array($matr)){              
            //echo "<pre>"; print_r($matr);
            $max_cle_incr = $this->get_cle_max();              
            
            for($i=1;$i<$nbrow;$i++){
				$champ_lib_name_long = 0;
            	$champ_lib_email = 1;
            	$champ_lib_tel = 2;
                $champ_login      = 3;
                $champ_pwd    = 4; 
                $champ_group       = 5;              
                
                // traitement des nouvelles creations de donn�es dans ce cas il faut n�cessairement saisir les libell�s
				//if (isset($matr[$i][$champ_login]) && $matr[$i][$champ_login]<>'' && isset($matr[$i][$champ_group]) && $matr[$i][$champ_group]<>''){                      
					// il faut trouver la valeur max dans la table de nomenclature
					$max_cle_incr++;
					$donnees_ligne  =   array();
					$donnees_ligne [] = $max_cle_incr;
					if (isset($matr[$i][$champ_lib_name_long]) && $matr[$i][$champ_lib_name_long]<>'' ){
						$donnees_ligne [] = $matr[$i][$champ_lib_name_long];
					}else{
						$donnees_ligne []='';
					}
				
					if (isset($matr[$i][$champ_lib_email]) && $matr[$i][$champ_lib_email]<>'' ){
						$donnees_ligne [] = $matr[$i][$champ_lib_email];
					}else{
						$donnees_ligne []='';
					}
				
					if (isset($matr[$i][$champ_lib_tel]) && $matr[$i][$champ_lib_tel]<>'' ){
						$donnees_ligne [] = $matr[$i][$champ_lib_tel];
					}else{
						$donnees_ligne []='';
					}
					if (isset($matr[$i][$champ_login]) && $matr[$i][$champ_login]<>'' ){
						$donnees_ligne [] = $matr[$i][$champ_login];
					}else{
						$donnees_ligne []='';
					}
					//$donnees_ligne [] = $matr[$champ_ordre]; 
					if (isset($matr[$i][$champ_pwd]) && $matr[$i][$champ_pwd]<>'' ){
						// session 35 : migration md5 -> bcrypt (import Excel)
						$donnees_ligne [] = password_hash($matr[$i][$champ_pwd], PASSWORD_BCRYPT);
					}else{
						$donnees_ligne []='';
					}   
					if (isset($matr[$i][$champ_group]) && $matr[$i][$champ_group]<>'' ){
						$donnees_ligne [] = $matr[$i][$champ_group];
					}else{
						$donnees_ligne []='';
					}
					// Colonnes 6-11 optionnelles — liaison école+campagne (session 68+69)
					$champ_code_etab  = 6;  // code_etab   ex. "101012071"  -> $tab[7]
					$champ_id_camp    = 7;  // id_camp     ex. "12"         -> $tab[8]
					$champ_id_systeme = 8;  // id_systeme  ex. "1"          -> $tab[9]
					$champ_id_annee   = 9;  // id_annee    ex. "2024"       -> $tab[10]
					$champ_id_chaine  = 10; // id_chaine   ex. "1"          -> $tab[11]
					$champ_id_periode = 11; // id_periode  ex. "1"          -> $tab[12]
					$donnees_ligne [] = (isset($matr[$i][$champ_code_etab])  && $matr[$i][$champ_code_etab]<>'')  ? trim($matr[$i][$champ_code_etab])  : '';
					$donnees_ligne [] = (isset($matr[$i][$champ_id_camp])    && $matr[$i][$champ_id_camp]<>'')    ? trim($matr[$i][$champ_id_camp])    : '';
					$donnees_ligne [] = (isset($matr[$i][$champ_id_systeme]) && $matr[$i][$champ_id_systeme]<>'') ? trim($matr[$i][$champ_id_systeme]) : '';
					$donnees_ligne [] = (isset($matr[$i][$champ_id_annee])   && $matr[$i][$champ_id_annee]<>'')   ? trim($matr[$i][$champ_id_annee])   : '';
					$donnees_ligne [] = (isset($matr[$i][$champ_id_chaine])  && $matr[$i][$champ_id_chaine]<>'')  ? trim($matr[$i][$champ_id_chaine])  : '';
					$donnees_ligne [] = (isset($matr[$i][$champ_id_periode]) && $matr[$i][$champ_id_periode]<>'') ? trim($matr[$i][$champ_id_periode]) : '';
					$this->donnees_post_excel[]= $donnees_ligne;                     
					
				//}
                
            }
        }
         //echo "<pre>"; print_r($this->donnees_post_excel);
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
        $sql = 'SELECT  MAX('.$this->champ_id.') as MAX_INSERT FROM  '.$this->nom_table;        
                   
        try{
            if (($rs =  $this->conn->Execute($sql))===false) {
                throw new Exception('ERR_SQL');
            }            
            if (!$rs->EOF) {                  
                $max_return = $rs->fields['MAX_INSERT'];                  
            }
        }
        catch (Exception $e){
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
        $entete     .=   "function recharge(id_groupe) {";
        if ($this->type_traitement=='user') {       
		    $entete	    .=   "location.href   = '?val=gestionuser&id_groupe='+id_groupe";
        }else {
            $entete	    .=   "location.href   = '?val=gestiondroit&id_groupe='+id_groupe";
        }		
        $entete     .=   " }";
        $entete     .=  " function Alert_Supp(checkbox){ ";
        $entete     .=  "var chaine_eval ='document.form1.'+checkbox+'.checked == true';";       
        $entete     .=  " if (eval(chaine_eval)){ " ;
        $mess_alert		 	= $this->recherche_libelle(110,$_SESSION['langue'],'DICO_MESSAGE');
        $mess_alert			= addslashes($mess_alert);
        $entete     .=  "   alert ('$mess_alert');";
        $entete     .=  "}  }\n";
		$entete     .=  "$(function() {
							var initValues = new Array();							
							$('form[name=\"form1\"] input[name$=\"_R\"]').each(function() {
								initValues.push($(this).val());
							});
							$('a').click(function() {	
								$('form[name=\"form1\"] input[name$=\"_R\"]').each(function(index, value) {
									$(this).val(initValues[index]);
								});							
								var form = $('form[name=\"form1\"]');
								$(form).attr('action', $(this).attr('href'));								
								$(form).submit();
								return false;
							});
						 });\n";
        $entete     .=  " </script>";

        $entete     .= "<br />";       
        $entete     .="<form name ='form1' action='".$_SERVER['REQUEST_URI']."' method='post'>";        
        $entete     .="<span class=''>";
                     
        $entete     .= '<div ><table><tr><td>'.$this->recherche_libelle_page('DescGroup',$_SESSION['langue'],'user').'</td><td> ';
        $entete     .="<select name='id_groupe' onchange= 'recharge(id_groupe.value)'>";
            
        $this->init_liste_groupe();        
        foreach ($this->list_groupes as $groupe) {
            $entete .= '<option value='.'\''.$groupe[0].'\'';
                
            if ($groupe[0]==$this->code_groupe) {
            $entete .=   ' selected ';   
            }
                
            $entete .= '>'.$groupe[1].'</option>';            
        }
        $entete     .='</select></td></tr>';            
            
        $entete     .= '</table></div></span><br />'; 
                
        $this->entete_template = $entete;
        $this->fin_template="<br /></div><div><INPUT TYPE='SUBMIT' VALUE='".$this->recherche_libelle_page('Valider',$_SESSION['langue'],'nomenclature')."'></div></span></Form>";
        //$this->fin_template='</Form>';        
     
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function init_liste_groupe() {
		
		if(!isset($_GET['app']) || $_GET['app'] <> 'mob') $req_ord    = 'SELECT ORDRE_GROUPE FROM ADMIN_GROUPES WHERE CODE_GROUPE ='.$_SESSION['groupe'];
		else $req_ord    = 'SELECT ORDRE_GROUPE FROM ADMIN_GROUPES WHERE CODE_GROUPE = -1';
		
		try{            
            if(($ord_grp     =  $GLOBALS['conn_dico']->GetOne($req_ord))===false){
            	throw new Exception('ERR_SQL');
            }                            
        }
        catch (Exception $e){
            $erreur = new erreur_manager($e, $req_ord);
        }
		if( !$ord_grp ){
			$ord_grp = 0 ;
		}
		
        $sql    = 'SELECT * FROM ADMIN_GROUPES WHERE ORDRE_GROUPE >='.$ord_grp;
        //echo $sql ;
        try{            
            if(($rs     =  $GLOBALS['conn_dico']->Execute($sql))===false){
                    throw new Exception('ERR_SQL');
            }                            
            
            if (!$rs->EOF){
                $rs->MoveFirst();
                while (!$rs->EOF){
                    $groupe = array();
                    $groupe [] = $rs->fields['CODE_GROUPE'];
                    $groupe [] = $rs->fields['LIBELLE_GROUPE'];
                                    
                    $this->list_groupes[] = $groupe;
                    $rs->MoveNext();
    
                }
            }
        }
        catch (Exception $e){
            $erreur = new erreur_manager($e,$sql);
        }        
        
    
    }   
	
	
	function get_group_name_by_code($grp_code) {
		foreach ($this->list_groupes as $groupe) {
            if ($groupe[0]==$grp_code) {
				return $groupe[1];   
            }     
        }
		return $grp_code;
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
				// cette variable contient la cl� identifiant de l'�l�ment matriciell
				if (array_key_exists('filtre_recherche', $elt)) continue;
				$cle	=	associer_identifiant($elt,$indice_cle);
				$action	=	existe_element_grille_action($cle,$elt,$indice_cle,$matr1);		
				//echo '<br />'.$action. '<br />';
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
				// cette variable contient la cl� identifiant de l'�l�ment matriciell
				
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
								// session 35 : migration md5 -> bcrypt (creation/modification utilisateur)
								if ($this->type_traitement=='user'){
										// Hacher le mot de passe en bcrypt seulement s'il est non vide
										if (!empty($tab[5])) {
											$tab[5] = password_hash($tab[5], PASSWORD_BCRYPT);
										}
								} 
								// fin session 35
                switch ($action){
                    case 'I':                        
                            $insert_ok=true;
							$sql =  'SELECT * FROM ADMIN_USERS WHERE NOM_USER='.$this->conn->qstr($tab[3]);
							$rs = $this->conn->GetAll($sql);
							if(count($rs)>0){
									print "<script language='javascript' type='text/javascript'>\n";
									print "alert('".$this->recherche_libelle_page('ExistUser',$_SESSION['langue'],'user')." : ".$tab[1]."');\n";
									print "</script>\n";
									$insert_ok=false;
							}
							if($insert_ok){
								if($_SESSION['groupe']==1){
									$sql =  'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->champ_name_user.','.$this->champ_email_user.','.$this->champ_tel_user.','.$this->champ_lib.','.$this->champ_ordre.','.$this->champ_systeme.')'.
											' VALUES('.$tab[0].','.$this->conn->qstr($tab[1]).','.$this->conn->qstr($tab[2]).','.$this->conn->qstr($tab[3]).','.$this->conn->qstr($tab[4]).','.$this->conn->qstr($tab[5]).','.$this->code_groupe.')';
								}else{
									$sql =  'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->champ_name_user.','.$this->champ_email_user.','.$this->champ_tel_user.','.$this->champ_lib.','.$this->champ_ordre.','.$this->champ_systeme.','.$this->champ_user_parent.')'.
											' VALUES('.$tab[0].','.$this->conn->qstr($tab[1]).','.$this->conn->qstr($tab[2]).','.$this->conn->qstr($tab[3]).','.$this->conn->qstr($tab[4]).','.$this->conn->qstr($tab[5]).','.$this->code_groupe.','.$_SESSION['code_user'].')';
								}	//echo  $sql; return;  
								try{
									if ($this->conn->Execute($sql)===false)
										throw new Exception('ERR_SQL');
								}
								catch (Exception $e){
									$erreur = new erreur_manager($e,$sql);
								}
							}                         
                            break;
                        
                    case 'U':
                        if ($this->type_traitement=='user'){
							//On fait d'abord la mise � jour
							$sql =  'UPDATE '.$this->nom_table.' SET '.$this->champ_name_user.'='.$this->conn->qstr($tab[1]).','.
									$this->champ_email_user.'='.$this->conn->qstr($tab[2]).','.
									$this->champ_tel_user.'='.$this->conn->qstr($tab[3]).','.
									$this->champ_lib.'='.$this->conn->qstr($tab[4]).','.
									$this->champ_ordre.'='.$this->conn->qstr($tab[5]).' WHERE '.
									$this->champ_id.'='.$tab[0].' AND '.$this->champ_systeme.'='.$this->code_groupe; 
							try{
								if ($this->conn->Execute($sql)==false)
								 throw new Exception('ERR_SQL');
							}                                  
							catch (Exception $e){
								$erreur = new erreur_manager($e,$sql);
							}
							//Fin MAJ
							//Apres on verifie si la mise � jour � cr�� un doublon sur le user name
							$sql =  'SELECT * FROM ADMIN_USERS WHERE NOM_USER='.$this->conn->qstr($tab[3]);
							$rs = $this->conn->GetAll($sql);
							if(count($rs)>1){
								print "<script language='javascript' type='text/javascript'>\n";
								print "alert('".$this->recherche_libelle_page('ExistUser',$_SESSION['langue'],'user')." : ".$tab[1]."');\n";
								print "</script>\n";
								$sql =  'UPDATE '.$this->nom_table.' SET '.$this->champ_name_user.'='.$this->conn->qstr($GLOBALS['ancien_param_user'][0][1]).','.
										$this->champ_email_user.'='.$this->conn->qstr($GLOBALS['ancien_param_user'][0][2]).','.
										$this->champ_tel_user.'='.$this->conn->qstr($GLOBALS['ancien_param_user'][0][3]).','.
										$this->champ_lib.'=\''.$GLOBALS['ancien_param_user'][0][4].'\','.
										$this->champ_ordre.'=\''.$GLOBALS['ancien_param_user'][0][5].'\' WHERE '.
										$this->champ_id.'='.$tab[0].' AND '.$this->champ_systeme.'='.$this->code_groupe; 
								try{
									if ($this->conn->Execute($sql)==false)
									 throw new Exception('ERR_SQL');
								}                                  
								catch (Exception $e){
									$erreur = new erreur_manager($e,$sql);
								}
								$update_ok=false;
								break 2;
							}
							//Fin verif
                        }else{
                            if ($tab[2]=='checked') {
                                // il s'agit d'ajout de droit associ�
                                $sql =  'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->champ_systeme.')'.
                                        ' VALUES('.$tab[0].','.$this->code_groupe.')';
                                       
                                try{
                                        if ($this->conn->Execute($sql)==false)
                                        throw new Exception('ERR_SQL');
                                   }        
                                   catch (Exception $e){
                                        $erreur = new erreur_manager($e,$sql);
                                   }   
                            }else{
                                // Il s'agit d'une suppression de droit associ�
                                $sql =  'DELETE FROM '.$this->nom_table.' WHERE '.
                                $this->champ_id.'='.$tab[0].' AND '.$this->champ_systeme.'='.$this->code_groupe;
                                 try{
                                        if ($this->conn->Execute($sql)==false)
                                            throw new Exception('ERR_SQL');
                                   }        
                                   catch (Exception $e){
                                        $erreur = new erreur_manager($e,$sql);
                                   }   
                            
                            }
                        
                        }
                        break;
                        
                        
                    case 'D' :
                        $sql =  'DELETE FROM '.$this->nom_table.' WHERE '.
                                $this->champ_id.'='.$tab[0].' AND '.$this->champ_systeme.'='.$this->code_groupe;                              
                        try{
                                if ($this->conn->Execute($sql)==false)
                                     throw new Exception('ERR_SQL');
								else{
									$del_fix_regroup = "DELETE FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER = ".$tab[0];
									$rs_del_fix_reg = $this->conn->Execute($del_fix_regroup);
								}
									
                           }       
                           catch (Exception $e){
                                $erreur = new erreur_manager($e,$sql);
                           }                                   
                        break;
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
	public function maj_bdd_excel($cheminFichierExcel){   
		$result = array();
		if (is_array($this->donnees_post_excel)){      
			// ── LOG LEGACY (import_export/) ─────────────────────────────────────────
			$this->create_log_file($cheminFichierExcel);
			// ── LOG RICHE MOBLOGS ────────────────────────────────────────────────────
			$this->create_mob_log($cheminFichierExcel);
			// ── NASSER LOG : chargement du logger si pas encore inclus ────────────────
			if (!class_exists('NasserLog')) {
				$nasser_logger_path = realpath(dirname(__FILE__).'/../../../..').DIRECTORY_SEPARATOR.'moblogs'.DIRECTORY_SEPARATOR.'nasser_logger.php';
				if (file_exists($nasser_logger_path)) { require_once $nasser_logger_path; }
			}
			// ── NASSER LOG : contexte import ─────────────────────────────────────────
			if (class_exists('NasserLog')) {
				NasserLog::etape('MAJ_BDD_EXCEL_ENTREE', 'N/A',
					'Fichier="'.basename($cheminFichierExcel).'" | Nb lignes données='.count($this->donnees_post_excel), '');
				NasserLog::params_hierarchie();
			}
			// ── FIN NASSER LOG ────────────────────────────────────────────────────────

			$num_ligne = 0; // compteur de ligne Excel (1-based pour le log)
			foreach ($this->donnees_post_excel as $tab) {
				$num_ligne++;
				$login_courant = isset($tab[4]) ? $tab[4] : 'N/A';

				$logData = "";
				$logData .= $tab[0].";".$tab[1].";".$tab[2].";".$tab[3].";".$tab[4].";".$this->get_group_name_by_code($tab[6]);

				// ── NASSER LOG : séparateur de ligne ─────────────────────────────────
				if (class_exists('NasserLog')) {
					NasserLog::ligne($num_ligne, $login_courant,
						isset($tab[0]) ? $tab[0] : 'N/A',
						isset($tab[7]) ? $tab[7] : 'N/A'
					);
				}
				// ── FIN NASSER LOG ────────────────────────────────────────────────────

				// ── MOBLOGS : début traitement ligne ─────────────────────────────────
				$this->write_mob_log($num_ligne, 'DEBUT_LIGNE',
					$login_courant,
					'CODE_USER='.$tab[0].' | NOM='.$tab[1].' | EMAIL='.$tab[2]
					.' | CODE_GROUPE='.$tab[6].' | CODE_ETAB='.(isset($tab[7]) ? $tab[7] : '')
				);

				if (empty($tab[4]) || empty($tab[5]) || empty($tab[6])) {
					$msg_manquant = $this->recherche_libelle_page('UserMandatoryDetails',$_SESSION['langue'],'user');
					array_push($tab, '<span class="error">'.$msg_manquant.'</span>'); 
					$logData .= ";".$msg_manquant;
					// ── MOBLOGS : champs obligatoires manquants ───────────────────────
					$this->write_mob_log($num_ligne, 'VALIDATION_ECHEC',
						$login_courant,
						'CHAMPS OBLIGATOIRES MANQUANTS — login='.(empty($tab[4])?'[vide]':$tab[4])
						.' | pass='.(empty($tab[5])?'[vide]':'OK').' | groupe='.(empty($tab[6])?'[vide]':$tab[6])
					);
				} else {
					$sql =  'SELECT EXISTS ( SELECT '.$this->champ_id.' FROM '.$this->nom_table.' WHERE '.$this->champ_lib.' like '.$this->conn->qstr($tab[4]).' );';
					if ($this->conn->GetOne($sql)) {
						$msg_exist = $this->recherche_libelle_page('LoginExist',$_SESSION['langue'],'user');
						array_push($tab, '<span class="error">'.$msg_exist.'</span>'); 
						$logData .= ";".$msg_exist;
						// ── MOBLOGS : login déjà existant ────────────────────────────
						$this->write_mob_log($num_ligne, 'LOGIN_DOUBLON',
							$login_courant,
							'LOGIN DEJA EXISTANT dans ADMIN_USERS — skip total'
						);
					} else {
						$sql =  'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->champ_name_user.','.$this->champ_email_user.','.$this->champ_tel_user.','.$this->champ_lib.','.$this->champ_ordre.','.$this->champ_systeme.','.$this->champ_user_parent.')'.
						' VALUES('.$tab[0].','.$this->conn->qstr($tab[1]).','.$this->conn->qstr($tab[2]).','.$this->conn->qstr($tab[3]).','.$this->conn->qstr($tab[4]).','.$this->conn->qstr($tab[5]).','.$this->conn->qstr($tab[6]).','.$_SESSION['code_user'].')';
						
						// AK-PHP-02 : transaction atomique INSERT ADMIN_USERS + DICO_FIXE_REGROUPEMENT
						// BeginTrans() avant le 1er INSERT pour que les deux soient atomiques.
						// Si l'un des deux echoue -> RollbackTrans() annule les deux.
						$this->conn->BeginTrans();

						// ── NASSER LOG : transaction + SQL INSERT ADMIN_USERS ─────────
						if (class_exists('NasserLog')) {
							NasserLog::transaction('BEGIN', $login_courant, 'BeginTrans() avant INSERT ADMIN_USERS');
							NasserLog::sql('ADMIN_USERS_INSERT', $sql);
						}
						// ── FIN NASSER LOG ────────────────────────────────────────────
						// ── MOBLOGS : ouverture transaction ───────────────────────────
						$this->write_mob_log($num_ligne, 'TRANSACTION_BEGIN',
							$login_courant, 'BeginTrans() ouvert'
						);
						// ── MOBLOGS : SQL ADMIN_USERS ─────────────────────────────────
						$this->write_mob_log($num_ligne, 'ADMIN_USERS_INSERT',
							$login_courant,
							'SQL: ' . $sql
						);

						if ($this->conn->Execute($sql)===false) {
							$db_err_au = method_exists($this->conn, 'ErrorMsg') ? $this->conn->ErrorMsg() : 'N/A';
							$this->conn->RollbackTrans();
							// ── NASSER LOG ────────────────────────────────────────────
							if (class_exists('NasserLog')) {
								NasserLog::err('ADMIN_USERS_INSERT', 'ECHEC + RollbackTrans | DB: ' . $db_err_au);
							}
							// ── FIN NASSER LOG ────────────────────────────────────────
							// ── MOBLOGS : INSERT ADMIN_USERS échoué ──────────────────
							$this->write_mob_log($num_ligne, 'ADMIN_USERS_ERREUR',
								$login_courant,
								'ECHEC INSERT ADMIN_USERS — RollbackTrans() | Erreur DB: ' . substr($db_err_au, 0, 200)
							);
							array_push($tab, '<span class="error">'.$this->recherche_libelle_page('ERR_SQL',$_SESSION['langue'],'user').'</span>'); 
							$logData .= ";".$this->recherche_libelle_page('ERR_SQL',$_SESSION['langue'],'user');
						} else {
							// ── MOBLOGS : INSERT ADMIN_USERS réussi ──────────────────
							$this->write_mob_log($num_ligne, 'ADMIN_USERS_OK',
								$login_courant,
								'INSERT ADMIN_USERS réussi — CODE_USER=' . $tab[0]
							);

							// Liaison école+campagne : INSERT DICO_FIXE_REGROUPEMENT
							// Colonnes Excel G-L → $tab[7..12] :
							//   $tab[7]=CODE_ETAB, $tab[8]=ID_CAMP, $tab[9]=ID_SYSTEME,
							//   $tab[10]=ID_ANNEE, $tab[11]=ID_CHAINE, $tab[12]=ID_PERIODE
								$regroup_warning = '';
								$trans_committed = false;  // AK-PHP-02 : indique si CommitTrans/RollbackTrans deja appele
								if (!empty($tab[7]) && !empty($tab[8]) && !empty($tab[9])) {

									$raw_code_etab   = trim($tab[7]);
									$code_etab_q     = $this->conn->qstr($raw_code_etab);
									$id_camp         = intval($tab[8]);
									$id_systeme      = intval($tab[9]);
									$id_annee        = intval($tab[10]);
									$id_chaine       = intval($tab[11]);
									$id_periode      = (!empty($tab[12])) ? intval($tab[12]) : 0;

									// Valeurs validées sur données réelles (DICO_FIX_REGROUPEMENT)
									$id_status       = 2; // valeur réelle : 2
									$id_type_regroup = 0; // valeur réelle : 0 (établissement)

								// ── NASSER LOG : paramètres DICO ────────────────
								if (class_exists('NasserLog')) {
									NasserLog::etape('DICO_PARAMS', $login_courant,
										'CODE_ETAB='.$raw_code_etab
										.' | CAMP='.$id_camp.' | SYS='.$id_systeme
										.' | ANNEE='.$id_annee.' | CHAINE='.$id_chaine
										.' | PERIODE='.$id_periode
										.' | ID_STATUS='.$id_status.' | ID_TYPE_REGROUP='.$id_type_regroup, '');
								}
								// ── FIN NASSER LOG ────────────────────────────────────
								// ── MOBLOGS : paramètres DICO ────────────────────
								$this->write_mob_log($num_ligne, 'DICO_PARAMS',
									$login_courant,
									'CODE_ETAB='.$raw_code_etab
									.' | CAMP='.$id_camp.' | SYS='.$id_systeme
									.' | ANNEE='.$id_annee.' | CHAINE='.$id_chaine
									.' | PERIODE='.$id_periode
								);

									// ----------------------------------------------------------------
									// Récupérer USER_PRIV, ID_REGROUP_PARENTS, ID_TYPE_REGROUP_PARENTS
									// depuis un enregistrement existant de la même campagne/chaîne.
									// D'abord on cherche exactement ce code école, sinon n'importe quel
									// enregistrement de la même campagne+chaîne (modèle générique).
									// ----------------------------------------------------------------
									$sql_tpl = 'SELECT TOP 1 USER_PRIV, ID_REGROUP_PARENTS,'
										.' ID_TYPE_REGROUP_PARENTS'
										.' FROM DICO_FIXE_REGROUPEMENT'
										.' WHERE ID_CAMPAGNE='.$id_camp
										.' AND ID_CHAINE='.$id_chaine
										.' AND ID_ANNEE='.$id_annee
										.' AND ID_SYSTEME='.$id_systeme
										.' AND ID_REGROUP='.$code_etab_q;
								// ── NASSER LOG : SQL template DICO (école exacte) ───
								if (class_exists('NasserLog')) {
									NasserLog::sql('DICO_TEMPLATE_ECOLE_EXACTE', $sql_tpl);
								}
								// ── FIN NASSER LOG ────────────────────────────────────
								// ── MOBLOGS : recherche template DICO par école ──
								$this->write_mob_log($num_ligne, 'DICO_TEMPLATE_LOOKUP',
									$login_courant,
									'SQL (école exacte): ' . $sql_tpl
								);
								$tpl = $this->conn->GetRow($sql_tpl);
								// ── NASSER LOG : résultat template école ─────────────
								if (class_exists('NasserLog')) {
									NasserLog::sql('DICO_TEMPLATE_ECOLE_EXACTE_RESULT', '(GetRow)', $tpl ? [$tpl] : []);
								}
								// ── FIN NASSER LOG ────────────────────────────────────

								// Si pas de résultat pour ce code école précis → modèle générique
								if (empty($tpl)) {
									$sql_tpl2 = 'SELECT TOP 1 USER_PRIV, ID_REGROUP_PARENTS,'
										.' ID_TYPE_REGROUP_PARENTS'
										.' FROM DICO_FIXE_REGROUPEMENT'
										.' WHERE ID_CAMPAGNE='.$id_camp
										.' AND ID_CHAINE='.$id_chaine
										.' AND ID_ANNEE='.$id_annee
										.' AND ID_SYSTEME='.$id_systeme;
									// ── NASSER LOG : fallback template générique ──────
									if (class_exists('NasserLog')) {
										NasserLog::note('Aucun template pour CODE_ETAB='.$raw_code_etab.' → fallback générique');
										NasserLog::sql('DICO_TEMPLATE_GENERIQUE', $sql_tpl2);
									}
									// ── FIN NASSER LOG ────────────────────────────────
									// ── MOBLOGS : fallback template générique ────
									$this->write_mob_log($num_ligne, 'DICO_TEMPLATE_FALLBACK',
										$login_courant,
										'Pas de template école — SQL générique: ' . $sql_tpl2
									);
									$tpl = $this->conn->GetRow($sql_tpl2);
									// ── NASSER LOG : résultat fallback générique ─────
									if (class_exists('NasserLog')) {
										NasserLog::sql('DICO_TEMPLATE_GENERIQUE_RESULT', '(GetRow)', $tpl ? [$tpl] : []);
									}
									// ── FIN NASSER LOG ────────────────────────────────
								}

								// fix BUG-REGROUP-001 :
								// Du template on ne prend QUE USER_PRIV (valeur générique invariante).
								// ID_REGROUP_PARENTS et ID_TYPE_REGROUP_PARENTS dépendent du CODE_ETAB réel
								// et ne peuvent PAS être copiés depuis un enregistrement d'un autre établissement.
								// Ces deux champs seront TOUJOURS calculés via le lookup ETABLISSEMENT_REGROUPEMENT.
								$user_priv              = isset($tpl['USER_PRIV'])
									? $tpl['USER_PRIV'] : '';
								// Valeurs du template conservées en fallback ultime seulement
								$id_regroup_parents_tpl = isset($tpl['ID_REGROUP_PARENTS'])
									? $tpl['ID_REGROUP_PARENTS'] : '';
								$id_type_regroup_par_tpl= isset($tpl['ID_TYPE_REGROUP_PARENTS'])
									? $tpl['ID_TYPE_REGROUP_PARENTS'] : '';
								// Initialisation : vide — sera rempli par le lookup ETABLISSEMENT_REGROUPEMENT
								$id_regroup_parents     = '';
								$id_type_regroup_par    = '';

								// ── NASSER LOG : valeurs issues du template ───────────
								if (class_exists('NasserLog')) {
									NasserLog::valeur('USER_PRIV (template)',          $user_priv,            'TEMPLATE_DICO');
									NasserLog::valeur('ID_REGROUP_PARENTS (template — ignoré)', $id_regroup_parents_tpl, 'TEMPLATE_DICO — NON UTILISÉ (dépend CODE_ETAB)');
									NasserLog::valeur('ID_TYPE_REGROUP_PARENTS (tpl — ignoré)', $id_type_regroup_par_tpl, 'TEMPLATE_DICO — NON UTILISÉ');
									NasserLog::note('fix BUG-REGROUP-001 : ID_REGROUP_PARENTS sera calculé via ETABLISSEMENT_REGROUPEMENT pour CODE_ETAB='.$raw_code_etab);
								}
								// ── FIN NASSER LOG ────────────────────────────────────
								// ── MOBLOGS : résultat template ───────────────────
								$this->write_mob_log($num_ligne, 'DICO_TEMPLATE_RESULT',
									$login_courant,
									'USER_PRIV='.$user_priv
									.' | ID_REGROUP_PARENTS=[ignoré — lookup ER forcé]'
									.' | ID_TYPE_REGROUP_PARENTS=[ignoré — lookup ER forcé]'
								);

									// ── fix AK-PHP-01 v2 (BUG-REGROUP-001 round 2) : ──────────────────────────
								// Logique LIAISONS inspirée de arbre5::getparentsid() et build_chaine().
								// Le JOIN direct ER→REGROUPEMENT→HIERARCHIE était bogué (retournait VIDE).
								// Algorithme correct en 3 étapes :
								//   1. ETABLISSEMENT_REGROUPEMENT  → CODE_REGROUPEMENT de l'école
								//   2. HIERARCHIE (build_chaine)   → liste des CODE_TYPE_REGROUPEMENT de la chaîne
								//                                     (triée DESC = du plus haut au plus bas)
								//   3. LIAISONS (PERE_CODE_REGROUPEMENT) → remonter niveau par niveau
								if (!empty($raw_code_etab)) {

									// AK-CONN-GEO (BUG-REGROUP-001 r4) : utiliser la connexion SQL Server BURUNDI
									// (base principale) pour les tables géographiques ETABLISSEMENT_REGROUPEMENT,
									// REGROUPEMENT, HIERARCHIE, LIAISONS — toutes dans [BURUNDI].[dbo].
									// Quand administration.php passe placer_conn_dico=true, common.php écrase
									// $GLOBALS['conn'] avec conn_dico (Access/.mdb) et sauvegarde l'original dans
									// $GLOBALS['conn_original']. Sans cette sauvegarde, $this->conn (= conn_dico)
									// ne peut pas voir ETABLISSEMENT_REGROUPEMENT → ETAPE1 retourne VIDE.
									$conn_geo = (isset($GLOBALS['conn_original']) && $GLOBALS['conn_original'] !== false)
										? $GLOBALS['conn_original']
										: $this->conn;

									if (class_exists('NasserLog')) {
										$conn_geo_type = ($conn_geo === $this->conn) ? 'this->conn (fallback)' : 'conn_original (SQL Server BURUNDI)';
										NasserLog::note('AK-PHP-01 v2 — début lookup LIAISONS pour CODE_ETAB='.$raw_code_etab.' chaine='.$id_chaine.' | conn_geo='.$conn_geo_type);
									}

									// ─── ÉTAPE 1 : CODE_REGROUPEMENT direct de l'école ────────────────────────
									$sql_etab_reg =
										'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS code_reg'
										.' FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']
										.' WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$code_etab_q;

									if (class_exists('NasserLog')) { NasserLog::sql('AK2_ETAPE1_ETAB_REG', $sql_etab_reg); }

									$code_reg_ecole = $conn_geo->GetOne($sql_etab_reg);

									if (class_exists('NasserLog')) {
										NasserLog::valeur('ETAPE1 code_reg_ecole', $code_reg_ecole,
											empty($code_reg_ecole) ? '◄◄ VIDE — CODE_ETAB absent de ETABLISSEMENT_REGROUPEMENT' : 'OK');
									}

									// ─── DIAGNOSTIC ÉTAPE 1 VIDE : trouver ce qui existe en base pour cet étab ──
									if (empty($code_reg_ecole) && class_exists('NasserLog')) {
										// Chercher si le CODE_ETABLISSEMENT existe dans la table (sans filtre)
										$sql_diag_etab =
											'SELECT TOP 5 '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']
											.', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']
											.' FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']
											.' WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' LIKE '
											.$conn_geo->qstr('%'.substr(trim($raw_code_etab),0,3).'%');
										NasserLog::sql('AK2_DIAG_ER_SAMPLE', $sql_diag_etab);
										$diag_rows = $conn_geo->GetAll($sql_diag_etab);
										NasserLog::note('DIAG ER : '.count($diag_rows).' lignes ETABLISSEMENT_REGROUPEMENT avec CODE_ETAB LIKE %'.substr(trim($raw_code_etab),0,3).'%');
										if (!empty($diag_rows)) {
											foreach ($diag_rows as $dr) {
												NasserLog::note('  DIAG ER ligne: '.json_encode($dr));
											}
										}
										// Chercher aussi le compte total de la table pour vérifier qu'elle n'est pas vide
										$sql_diag_count = 'SELECT COUNT(*) FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'];
										NasserLog::sql('AK2_DIAG_ER_COUNT', $sql_diag_count);
										$total_er = $conn_geo->GetOne($sql_diag_count);
										NasserLog::note('DIAG ER : total lignes dans ETABLISSEMENT_REGROUPEMENT = '.$total_er);
										// Chercher dans REGROUPEMENT si le code 61555 existe (peut être un code_regroupement direct)
										$sql_diag_reg =
											'SELECT TOP 3 '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']
											.', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']
											.', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']
											.' FROM '.$GLOBALS['PARAM']['REGROUPEMENT']
											.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = '.(int)$raw_code_etab;
										NasserLog::sql('AK2_DIAG_REG_DIRECT', $sql_diag_reg);
										$diag_reg = $conn_geo->GetAll($sql_diag_reg);
										NasserLog::note('DIAG REGROUPEMENT direct CODE='.$raw_code_etab.' : '.count($diag_reg).' ligne(s)');
										if (!empty($diag_reg)) {
											foreach ($diag_reg as $dr) { NasserLog::note('  DIAG REG: '.json_encode($dr)); }
										}
									}
									// ─── FIN DIAGNOSTIC ────────────────────────────────────────────────────────

									if (!empty($code_reg_ecole)) {

										// ─── ÉTAPE 2 : types de la chaîne (build_chaine-like, DESC = haut→bas) ────
										// HIERARCHIE avec CODE_TYPE_CHAINE_LOC=$id_chaine, ordonné NIVEAU_HIERARCHIE DESC
										// → index 0 = plus haut niveau (province/région), dernier = niveau feuille (école)
										$sql_chaine =
											'SELECT DISTINCT T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS code_type_reg'
											.', T_C.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' AS niveau'
											.' FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' AS T_C'
											.' WHERE T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.(int)$id_chaine
											.' ORDER BY T_C.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC';

										if (class_exists('NasserLog')) { NasserLog::sql('AK2_ETAPE2_BUILD_CHAINE', $sql_chaine); }

										$chaine_types = $conn_geo->GetAll($sql_chaine);

										if (class_exists('NasserLog')) {
											NasserLog::valeur('ETAPE2 chaine_types count', count($chaine_types),
												empty($chaine_types) ? '◄◄ AUCUN TYPE trouvé pour chaine='.$id_chaine : 'OK');
											if (!empty($chaine_types)) {
												NasserLog::note('ETAPE2 types (DESC): '.implode(',', array_column($chaine_types, 'code_type_reg')));
											}
										}

										if (!empty($chaine_types)) {

											// ─── ÉTAPE 3 : navigation LIAISONS du niveau feuille vers la racine ──────
											// On détermine d'abord quel est le CODE_TYPE_REGROUPEMENT de l'école
											// en le cherchant dans REGROUPEMENT.
											$sql_type_ecole =
												'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS code_type_reg'
												.' FROM '.$GLOBALS['PARAM']['REGROUPEMENT']
												.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = '.(int)$code_reg_ecole;

											if (class_exists('NasserLog')) { NasserLog::sql('AK2_ETAPE3A_TYPE_ECOLE', $sql_type_ecole); }

											$code_type_reg_ecole = $conn_geo->GetOne($sql_type_ecole);

											if (class_exists('NasserLog')) {
												NasserLog::valeur('ETAPE3A code_type_reg_ecole', $code_type_reg_ecole,
													empty($code_type_reg_ecole) ? '◄◄ VIDE' : 'OK');
											}

											// Trouver l'index de l'école dans la chaîne (= niveau feuille)
											// La chaîne est triée DESC donc le dernier élément = niveau feuille
											// On cherche la position du type de l'école dans le tableau
											$idx_ecole = -1;
											foreach ($chaine_types as $idx => $ct) {
												if ((int)$ct['code_type_reg'] === (int)$code_type_reg_ecole) {
													$idx_ecole = $idx;
													break;
												}
											}

											if (class_exists('NasserLog')) {
												NasserLog::note('ETAPE3B idx_ecole dans chaine='.$idx_ecole
													.' | nb_niveaux='.count($chaine_types)
													.' (0=haut, '.(count($chaine_types)-1).'=feuille)');
											}

											// Remonter via LIAISONS depuis l'école jusqu'au sommet
											// Résultat : tableau de [code_reg, code_type_reg] dans l'ordre croissant
											// (feuille → racine comme fix_regroup.php : ETABLISSEMENT, colline, commune, province)
											$all_levels        = array(); // [code_reg, code_type_reg] de l'école incluse
											$cur_code_reg      = (int)$code_reg_ecole;
											$cur_code_type_reg = (int)$code_type_reg_ecole;

											// Ajouter l'école elle-même en premier
											$all_levels[] = array(
												'code_reg'      => $cur_code_reg,
												'code_type_reg' => $cur_code_type_reg,
											);

											// Remonter vers la racine en suivant LIAISONS (PERE_CODE_REGROUPEMENT)
											// On s'arrête quand il n'y a plus de parent ou qu'on a dépassé le sommet
											$max_iterations = count($chaine_types) + 2; // garde-fou boucle infinie
											$iterations     = 0;
											while ($iterations < $max_iterations) {
												$iterations++;

												// getparentsid-like : trouver le PERE via LIAISONS
												// PERE.CODE_REGROUPEMENT = L.PERE_CODE_REGROUPEMENT
												// L.CODE_REGROUPEMENT    = FILS.CODE_REGROUPEMENT
												// FILS.CODE_REGROUPEMENT = $cur_code_reg
												// FILS.CODE_TYPE_REGROUPEMENT = $cur_code_type_reg
												// PERE.CODE_TYPE_REGROUPEMENT IN (types de la chaîne)
												$sql_parent =
													'SELECT PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS code_reg'
													.', PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS code_type_reg'
													.' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE'
													.', '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS'
													.', '.$GLOBALS['PARAM']['LIAISONS'].' AS L'
													.', '.$GLOBALS['PARAM']['HIERARCHIE'].' AS H'
													.' WHERE PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']
													.'     = L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT']
													.' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']
													.'   = FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']
													.' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = '.$cur_code_reg
													.' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$cur_code_type_reg
													.' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']
													.'   = H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']
													.' AND H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.(int)$id_chaine;

												if (class_exists('NasserLog')) {
													NasserLog::sql('AK2_ETAPE3C_PARENT_iter'.$iterations, $sql_parent);
												}

												$parent_row = $conn_geo->GetRow($sql_parent);

												if (empty($parent_row) || empty($parent_row['code_reg'])) {
													// Plus de parent : on est au sommet de la hiérarchie
													if (class_exists('NasserLog')) {
														NasserLog::note('ETAPE3C iter='.$iterations.' — aucun parent LIAISONS → sommet atteint');
													}
													break;
												}

												$cur_code_reg      = (int)$parent_row['code_reg'];
												$cur_code_type_reg = (int)$parent_row['code_type_reg'];
												$all_levels[]      = array(
													'code_reg'      => $cur_code_reg,
													'code_type_reg' => $cur_code_type_reg,
												);

												if (class_exists('NasserLog')) {
													NasserLog::note('ETAPE3C iter='.$iterations.' → parent trouvé: code_reg='.$cur_code_reg.' code_type_reg='.$cur_code_type_reg);
												}
											}

											// all_levels est dans l'ordre [école, parent1, parent2, …, racine]
											// ID_REGROUP_PARENTS = tous les codes sauf l'école (= index 0)
											// comme fix_regroup.php : les parents sans l'établissement lui-même
											if (count($all_levels) > 1) {
												$parent_entries = array_slice($all_levels, 1); // exclure l'école (index 0)
											} else {
												// Un seul niveau (école = racine), on l'inclut quand même
												$parent_entries = $all_levels;
											}

											// Reconstruire les listes en ordre inverse (racine → feuille sans école)
											// pour correspondre au format attendu : province,commune,colline,…
											$parent_entries_asc = array_reverse($parent_entries);

											$parent_codes      = array();
											$parent_type_codes = array();
											foreach ($parent_entries_asc as $entry) {
												$parent_codes[]      = $entry['code_reg'];
												$parent_type_codes[] = $entry['code_type_reg'];
											}

											$id_regroup_parents  = implode(',', $parent_codes);
											$id_type_regroup_par = implode(',', $parent_type_codes);

											if (class_exists('NasserLog')) {
												NasserLog::note('ETAPE3 RÉSULTAT : all_levels=['.implode(',', array_column($all_levels,'code_reg')).']');
												NasserLog::note('ETAPE3 parent_entries_asc=['
													.implode(',', array_map(function($e){return 'R='.$e['code_reg'].'/T='.$e['code_type_reg'];}, $parent_entries_asc)).']');
												NasserLog::valeur('ID_REGROUP_PARENTS (LIAISONS)', $id_regroup_parents,
													empty($id_regroup_parents) ? '◄◄ VIDE après remontée LIAISONS' : 'LIAISONS — OK');
												NasserLog::valeur('ID_TYPE_REGROUP_PARENTS (LIAISONS)', $id_type_regroup_par, 'LIAISONS');
											}

											$this->write_mob_log($num_ligne, 'AK2_HIER_LIAISONS_OK', $login_courant,
												count($all_levels).' niveaux total | '
												.count($parent_entries_asc).' parents'
												.' | ID_REGROUP_PARENTS='.$id_regroup_parents
												.' | ID_TYPE_REGROUP_PARENTS='.$id_type_regroup_par
											);

										} else {
											// Chaîne vide : aucun type trouvé pour $id_chaine → fallback template
											if (!empty($id_regroup_parents_tpl)) {
												$id_regroup_parents  = $id_regroup_parents_tpl;
												$id_type_regroup_par = $id_type_regroup_par_tpl;
											}
											if (class_exists('NasserLog')) {
												NasserLog::err('AK2_CHAINE_VIDE',
													'build_chaine retourne VIDE pour chaine='.$id_chaine
													.' | Fallback template='.$id_regroup_parents);
											}
											$this->write_mob_log($num_ligne, 'AK2_CHAINE_VIDE', $login_courant,
												'HIERARCHIE sans résultat pour chaine='.$id_chaine.' — fallback template: '.$id_regroup_parents);
										}

									} else {
										// Aucune ligne dans ETABLISSEMENT_REGROUPEMENT pour ce CODE_ETAB → fallback
										if (!empty($id_regroup_parents_tpl)) {
											$id_regroup_parents  = $id_regroup_parents_tpl;
											$id_type_regroup_par = $id_type_regroup_par_tpl;
										}
										if (class_exists('NasserLog')) {
											NasserLog::err('AK2_ETAB_REG_VIDE',
												'ETABLISSEMENT_REGROUPEMENT retourne VIDE pour CODE_ETAB='.$raw_code_etab
												.' | Fallback template='.$id_regroup_parents);
										}
										$this->write_mob_log($num_ligne, 'AK2_ETAB_REG_VIDE', $login_courant,
											'Aucun CODE_REGROUPEMENT pour CODE_ETAB='.$raw_code_etab.' — fallback template: '.$id_regroup_parents);
									}
								}
								// ── fin fix AK-PHP-01 v2 (BUG-REGROUP-001 round 2) ──────────────────────────

								// ── NASSER LOG : valeurs FINALES avant INSERT DICO ───────
								if (class_exists('NasserLog')) {
									NasserLog::note('VALEURS FINALES à insérer dans DICO_FIXE_REGROUPEMENT :');
									NasserLog::valeur('ID_USER',                  $tab[0],              'Excel col A');
									NasserLog::valeur('ID_REGROUP (CODE_ETAB)',   $raw_code_etab,       'Excel col G');
									NasserLog::valeur('ID_REGROUP_PARENTS',       $id_regroup_parents,  empty($id_regroup_parents)?'◄◄ VIDE/MANQUANT':'ETAB_REGROUPEMENT');
									NasserLog::valeur('ID_TYPE_REGROUP_PARENTS',  $id_type_regroup_par, empty($id_type_regroup_par)?'◄◄ VIDE/MANQUANT':'ETAB_REGROUPEMENT');
									NasserLog::valeur('USER_PRIV',                $user_priv,           '');
									NasserLog::valeur('ID_CAMPAGNE',              $id_camp,             '');
									NasserLog::valeur('ID_SYSTEME',               $id_systeme,          '');
									NasserLog::valeur('ID_CHAINE',                $id_chaine,           '');
									NasserLog::valeur('ID_ANNEE',                 $id_annee,            '');
									NasserLog::valeur('ID_PERIODE',               $id_periode,          '');
								}
								// ── FIN NASSER LOG ────────────────────────────────────────

								$user_priv_q         = $this->conn->qstr($user_priv);
								$regroup_parents_q   = $this->conn->qstr($id_regroup_parents);
								$type_regroup_par_q  = $this->conn->qstr($id_type_regroup_par);

									// Vérifier doublon PK avant INSERT
									$sql_chk = 'SELECT COUNT(*) FROM DICO_FIXE_REGROUPEMENT'
										.' WHERE ID_USER='.$tab[0]
										.' AND ID_CAMPAGNE='.$id_camp
										.' AND ID_SYSTEME='.$id_systeme
										.' AND ID_CHAINE='.$id_chaine
										.' AND ID_ANNEE='.$id_annee
										.' AND ID_PERIODE='.$id_periode
										.' AND ID_TYPE_REGROUP='.$id_type_regroup
										.' AND ID_REGROUP='.$code_etab_q;
									$exists = intval($this->conn->GetOne($sql_chk));

									// ── NASSER LOG : SQL vérif doublon ───────────────────────
									if (class_exists('NasserLog')) {
										NasserLog::sql('DICO_DOUBLON_CHECK', $sql_chk, [$exists]);
										NasserLog::valeur('DOUBLON_EXISTS', $exists, $exists > 0 ? 'OUI — skip INSERT DICO' : 'NON — INSERT DICO à faire');
									}
									// ── FIN NASSER LOG ───────────────────────────────────────

									// ── MOBLOGS : résumé valeurs finales avant INSERT ─
									$this->write_mob_log($num_ligne, 'DICO_VALEURS_FINALES',
										$login_courant,
										'ID_REGROUP_PARENTS='.(empty($id_regroup_parents)?'[vide]':$id_regroup_parents)
										.' | ID_TYPE_REGROUP_PARENTS='.(empty($id_type_regroup_par)?'[vide]':$id_type_regroup_par)
										.' | USER_PRIV='.$user_priv
										.' | DOUBLON_CHECK='.$exists
									);

									if ($exists > 0) {
										// AK-PHP-02 : doublon DICO ignoré, mais INSERT ADMIN_USERS OK -> CommitTrans
										$this->conn->CommitTrans();
										$trans_committed = true;
										$regroup_warning = ' [École déjà liée — doublon ignoré]';
										// ── NASSER LOG : doublon DICO → CommitTrans ────────────
									if (class_exists('NasserLog')) {
										NasserLog::transaction('COMMIT', $login_courant,
											'DOUBLON DICO ignoré — ADMIN_USERS seul validé | CODE_ETAB='.$raw_code_etab.' CAMP='.$id_camp.' ANNEE='.$id_annee);
										NasserLog::note('DICO_DOUBLON_SKIP : enregistrement déjà présent dans DICO_FIXE_REGROUPEMENT — aucun INSERT DICO');
									}
									// ── FIN NASSER LOG ───────────────────────────────────────
									// ── MOBLOGS : doublon DICO ────────────────────
										$this->write_mob_log($num_ligne, 'DICO_DOUBLON_SKIP',
											$login_courant,
											'DOUBLON DICO_FIXE_REGROUPEMENT — CommitTrans() (ADMIN_USERS validé, DICO ignoré)'
											.' | CODE_ETAB='.$raw_code_etab.' | CAMP='.$id_camp.' | ANNEE='.$id_annee
										);
									} else {
										// INSERT avec les colonnes RÉELLES de DICO_FIXE_REGROUPEMENT
										$sql_regroup =
											'INSERT INTO DICO_FIXE_REGROUPEMENT'
											.' (USER_PRIV, ID_CAMPAGNE, ID_STATUS, ID_USER,'
											.'  ID_SYSTEME, ID_CHAINE, ID_ANNEE, ID_PERIODE,'
											.'  ID_TYPE_REGROUP, ID_REGROUP,'
											.'  ID_REGROUP_PARENTS, ID_TYPE_REGROUP_PARENTS)'
											.' VALUES ('
											.$user_priv_q.', '
											.$id_camp.', '
											.$id_status.', '
											.$tab[0].', '
											.$id_systeme.', '
											.$id_chaine.', '
											.$id_annee.', '
											.$id_periode.', '
											.$id_type_regroup.', '
											.$code_etab_q.', '
											.$regroup_parents_q.', '
											.$type_regroup_par_q.')';

										// ── NASSER LOG : SQL complet INSERT DICO ────────────────
										if (class_exists('NasserLog')) {
											NasserLog::sql('DICO_INSERT_SQL', $sql_regroup);
										}
										// ── FIN NASSER LOG ───────────────────────────────────────
										// ── MOBLOGS : SQL INSERT DICO ────────────────
										$this->write_mob_log($num_ligne, 'DICO_INSERT_SQL',
											$login_courant,
											'SQL: ' . $sql_regroup
										);

										if ($this->conn->Execute($sql_regroup) === false) {
										// AK-PHP-02 : INSERT DICO echoue -> RollbackTrans annule aussi INSERT ADMIN_USERS
										$this->conn->RollbackTrans();
										$trans_committed = true;  // RollbackTrans = transaction resolue
											$db_err = method_exists($this->conn, 'ErrorMsg')
												? $this->conn->ErrorMsg() : '';
											$regroup_warning = ' [ERREUR: école non liée + utilisateur annulé: '
												. htmlspecialchars(substr($db_err, 0, 150)) . ']';
											// ── NASSER LOG : RollbackTrans + erreur DICO ────────────
											if (class_exists('NasserLog')) {
												NasserLog::transaction('ROLLBACK', $login_courant,
													'ECHEC INSERT DICO_FIXE_REGROUPEMENT — ADMIN_USERS annulé aussi (atomique)');
												NasserLog::err('DICO_INSERT_ERREUR',
													'Execute() retourne false | DB Erreur: '.substr($db_err, 0, 300)
													.' | SQL: '.$sql_regroup);
											}
											// ── FIN NASSER LOG ───────────────────────────────────────
											// ── MOBLOGS : DICO INSERT échoué ─────────
											$this->write_mob_log($num_ligne, 'DICO_INSERT_ERREUR',
												$login_courant,
												'ECHEC INSERT DICO_FIXE_REGROUPEMENT — RollbackTrans() annule aussi ADMIN_USERS'
												.' | DB Erreur: ' . substr($db_err, 0, 200)
											);
										} else {
											// AK-PHP-02 : les deux INSERTs réussis -> CommitTrans valide la transaction
											$this->conn->CommitTrans();
											$trans_committed = true;
											$regroup_warning = ' [École liée OK]';
											// ── NASSER LOG : CommitTrans succès complet ─────────────
											if (class_exists('NasserLog')) {
												NasserLog::transaction('COMMIT', $login_courant,
													'INSERT DICO_FIXE_REGROUPEMENT réussi — transaction validée (ADMIN_USERS + DICO)');
												NasserLog::note('DICO_INSERT_OK :'
													.' ID_USER='.$tab[0]
													.' | CODE_ETAB='.$raw_code_etab
													.' | ID_REGROUP_PARENTS='.(empty($id_regroup_parents)?'◄◄ VIDE':$id_regroup_parents)
													.' | ID_TYPE_REGROUP_PARENTS='.(empty($id_type_regroup_par)?'◄◄ VIDE':$id_type_regroup_par)
													.' | USER_PRIV='.$user_priv
												);
											}
											// ── FIN NASSER LOG ───────────────────────────────────────
											// ── MOBLOGS : succès complet ──────────────
											$this->write_mob_log($num_ligne, 'DICO_INSERT_OK',
												$login_courant,
												'INSERT DICO_FIXE_REGROUPEMENT réussi — CommitTrans() validé'
												.' | ID_USER='.$tab[0].' | CODE_ETAB='.$raw_code_etab
												.' | ID_REGROUP_PARENTS='.$id_regroup_parents
												.' | ID_TYPE_REGROUP_PARENTS='.$id_type_regroup_par
											);
										}
									}
								} else {
									// ── NASSER LOG : champs DICO manquants ───────────────────
									if (class_exists('NasserLog')) {
										NasserLog::note('DICO_SKIP_CHAMPS_VIDES : tab[7..9] absents ou vides — aucun INSERT DICO_FIXE_REGROUPEMENT');
										NasserLog::valeur('tab[7] CODE_ETAB',  isset($tab[7]) ? $tab[7] : 'non défini', 'Excel col H');
										NasserLog::valeur('tab[8] ID_CAMP',    isset($tab[8]) ? $tab[8] : 'non défini', 'Excel col I');
										NasserLog::valeur('tab[9] ID_SYSTEME', isset($tab[9]) ? $tab[9] : 'non défini', 'Excel col J');
										NasserLog::err('DICO_SKIP_CHAMPS_VIDES',
											'Liaison DICO ignorée — colonnes Excel insuffisantes pour ligne '.$num_ligne.' login='.$login_courant);
									}
									// ── FIN NASSER LOG ───────────────────────────────────────
									// ── MOBLOGS : colonnes DICO absentes → pas d'INSERT DICO ─
									$this->write_mob_log($num_ligne, 'DICO_SKIP_CHAMPS_VIDES',
										$login_courant,
										'CODE_ETAB / ID_CAMP / ID_SYSTEME manquants — pas de liaison DICO'
									);
								}
							// AK-PHP-02 : CommitTrans si la transaction n'a pas encore ete resolue
							// (cas: tab[7..9] vides -> pas de INSERT DICO -> transaction toujours ouverte)
							if (!$trans_committed) {
								$this->conn->CommitTrans();
								// ── NASSER LOG : CommitTrans sécurité ────────────────────────
								if (class_exists('NasserLog')) {
									NasserLog::transaction('COMMIT', $login_courant,
										'CommitTrans() de SÉCURITÉ — transaction encore ouverte (DICO non traité) — seul ADMIN_USERS validé');
									NasserLog::note('TRANSACTION_COMMIT_SECURITE : cas anormal — vérifier pourquoi DICO n\'a pas été traité pour login='.$login_courant);
								}
								// ── FIN NASSER LOG ───────────────────────────────────────────
								// ── MOBLOGS : CommitTrans de sécurité ────────────
								$this->write_mob_log($num_ligne, 'TRANSACTION_COMMIT_SECURITE',
									$login_courant,
									'CommitTrans() de sécurité (DICO non traité) — ADMIN_USERS seul validé'
								);
							}
							array_push($tab, '<span class="success">OK'.$regroup_warning.'</span>'); 	
							$logData .= ";OK".$regroup_warning;
						}							
					}
				}
				$tab[6] = $this->get_group_name_by_code($tab[6]);
				$result[] = $tab;
				$this->record_log_file($logData);
				// ── NASSER LOG : fin de traitement de la ligne ───────────────────────
				if (class_exists('NasserLog')) {
					NasserLog::note('═══ FIN LIGNE '.$num_ligne.' — login='.$login_courant.' ═══');
				}
				// ── FIN NASSER LOG ────────────────────────────────────────────────────
				// ── MOBLOGS : fin ligne ───────────────────────────────────────────────
				$this->write_mob_log($num_ligne, 'FIN_LIGNE',
					$login_courant,
					'--- fin traitement ligne '.$num_ligne.' ---'
				);
			}
			$this->close_log_file();
			// ── NASSER LOG : fin import complet ──────────────────────────────────────
			if (class_exists('NasserLog')) {
				NasserLog::note('');
				NasserLog::note('╔══════════════════════════════════════════════════════════════════════════════════╗');
				NasserLog::note('║           FIN IMPORT EXCEL — maj_bdd_excel() terminé                           ║');
				NasserLog::note('╚══════════════════════════════════════════════════════════════════════════════════╝');
				NasserLog::note('Total lignes traitées : '.count($result).' | Fichier: '.basename($cheminFichierExcel));
			}
			// ── FIN NASSER LOG ────────────────────────────────────────────────────────
			// ── MOBLOGS : fermeture ───────────────────────────────────────────────────
			$this->close_mob_log();
		}
		return $result;
	}   
	

	// ════════════════════════════════════════════════════════════════════════════════
	// fix AK-PHP-02 : Migration des utilisateurs mobiles vers une nouvelle annee
	// ═══════════════════════════════════════════════════════════════════════════════
	/**
	 * Duplique les entrees DICO_FIXE_REGROUPEMENT vers une nouvelle annee de collecte.
	 * Ne cree pas de doublon si l'entree existe deja pour la nouvelle annee.
	 * Enrichit ID_REGROUP_PARENTS / ID_TYPE_REGROUP_PARENTS depuis ETABLISSEMENT_REGROUPEMENT
	 * quand ces colonnes sont vides (meme logique AK-PHP-01).
	 *
	 * @param int $old_annee   Annee source
	 * @param int $new_annee   Nouvelle annee de collecte
	 * @param int $new_camp    Nouveau ID_CAMPAGNE
	 * @param int $new_periode Nouveau ID_PERIODE (0 = conserver l'ancien)
	 * @param int $id_groupe   Filtre groupe utilisateur (0 = tous)
	 * @return array           ['migrated'=>N, 'skipped'=>N, 'errors'=>[...]]
	 */
	public function migrer_utilisateurs_annee($old_annee, $new_annee, $new_camp, $new_periode = 0, $id_groupe = 0) {
		$migrated = 0;
		$skipped  = 0;
		$errors   = array();

		$sql_src = 'SELECT DFR.*, AU.CODE_GROUPE'
			.' FROM DICO_FIXE_REGROUPEMENT DFR'
			.' INNER JOIN ADMIN_USERS AU ON AU.CODE_USER = DFR.ID_USER'
			.' WHERE DFR.ID_ANNEE = '.(int)$old_annee;
		if ((int)$id_groupe > 0) {
			$sql_src .= ' AND AU.CODE_GROUPE = '.(int)$id_groupe;
		}
		$rows = $this->conn->GetAll($sql_src);
		if (empty($rows) || !is_array($rows)) {
			$errors[] = 'Aucun utilisateur trouve pour l\'annee '.$old_annee;
			return array('migrated' => 0, 'skipped' => 0, 'errors' => $errors);
		}

		foreach ($rows as $row) {
			$id_user        = intval($row['ID_USER']);
			$id_systeme     = intval($row['ID_SYSTEME']);
			$id_chaine      = intval($row['ID_CHAINE']);
			$id_camp_new    = (int)$new_camp;
			$id_periode_new = ((int)$new_periode > 0) ? (int)$new_periode : intval($row['ID_PERIODE']);
			$id_type_rg     = intval($row['ID_TYPE_REGROUP']);
			$id_regroup     = $row['ID_REGROUP'];
			$id_regroup_q   = $this->conn->qstr($id_regroup);
			$user_priv      = $row['USER_PRIV'];
			$id_status      = intval($row['ID_STATUS']);

			// Verifier doublon
			$sql_chk = 'SELECT COUNT(*) FROM DICO_FIXE_REGROUPEMENT'
				.' WHERE ID_USER='.$id_user
				.' AND ID_CAMPAGNE='.$id_camp_new
				.' AND ID_SYSTEME='.$id_systeme
				.' AND ID_CHAINE='.$id_chaine
				.' AND ID_ANNEE='.(int)$new_annee
				.' AND ID_PERIODE='.$id_periode_new
				.' AND ID_TYPE_REGROUP='.$id_type_rg
				.' AND ID_REGROUP='.$id_regroup_q;
			if (intval($this->conn->GetOne($sql_chk)) > 0) {
				$skipped++;
				continue;
			}

			// Enrichir ID_REGROUP_PARENTS / ID_TYPE_REGROUP_PARENTS si vides
			$id_regroup_parents  = $row['ID_REGROUP_PARENTS'];
			$id_type_regroup_par = $row['ID_TYPE_REGROUP_PARENTS'];
			if ((empty($id_regroup_parents) || empty($id_type_regroup_par)) && !empty($id_regroup) && $id_type_rg == 0) {
				$sql_hier =
					'SELECT R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS code_reg'
					.', R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS code_type_reg'
					.', H.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' AS niveau'
					.' FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS ER'
					.' INNER JOIN '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS R'
					.'   ON R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']
					.'    = ER.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']
					.' INNER JOIN '.$GLOBALS['PARAM']['HIERARCHIE'].' AS H'
					.'   ON H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']
					.'    = R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']
					.'  AND H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$id_chaine
					.' WHERE ER.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$id_regroup_q
					.' ORDER BY H.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' ASC';
				$hier_rows = $this->conn->GetAll($sql_hier);
				if (!empty($hier_rows) && is_array($hier_rows)) {
					$codes_reg = array(); $codes_type_reg = array();
					foreach ($hier_rows as $hrow) {
						$codes_reg[]      = $hrow['code_reg'];
						$codes_type_reg[] = $hrow['code_type_reg'];
					}
					$parent_codes      = (count($codes_reg) > 1) ? array_slice($codes_reg, 1)      : $codes_reg;
					$parent_type_codes = (count($codes_reg) > 1) ? array_slice($codes_type_reg, 1) : $codes_type_reg;
					$id_regroup_parents  = implode(',', $parent_codes);
					$id_type_regroup_par = implode(',', $parent_type_codes);
				}
			}

			$sql_ins = 'INSERT INTO DICO_FIXE_REGROUPEMENT'
				.' (USER_PRIV, ID_CAMPAGNE, ID_STATUS, ID_USER,'
				.'  ID_SYSTEME, ID_CHAINE, ID_ANNEE, ID_PERIODE,'
				.'  ID_TYPE_REGROUP, ID_REGROUP,'
				.'  ID_REGROUP_PARENTS, ID_TYPE_REGROUP_PARENTS)'
				.' VALUES ('
				.$this->conn->qstr($user_priv).', '
				.$id_camp_new.', '
				.$id_status.', '
				.$id_user.', '
				.$id_systeme.', '
				.$id_chaine.', '
				.(int)$new_annee.', '
				.$id_periode_new.', '
				.$id_type_rg.', '
				.$id_regroup_q.', '
				.$this->conn->qstr($id_regroup_parents).', '
				.$this->conn->qstr($id_type_regroup_par).')' ;

			if ($this->conn->Execute($sql_ins) === false) {
				$db_err = method_exists($this->conn, 'ErrorMsg') ? $this->conn->ErrorMsg() : '';
				$errors[] = 'User '.$id_user.' / '.$id_regroup.': '.substr($db_err, 0, 120);
			} else {
				$migrated++;
			}
		}
		return array('migrated' => $migrated, 'skipped' => $skipped, 'errors' => $errors);
	}
	// -- fin fix AK-PHP-02 --------------------------------------------------------

	/**
		* Cr�ation d'un fichier de log
		* @access public
		* 
		*/
		function create_log_file($cheminFichierExcel){
			$this->chemin_log = dirname($cheminFichierExcel).'/_log_'.basename($cheminFichierExcel,".xlsx").'.log';
			$ficlog = $this->chemin_log;
			if (file_exists( $this->chemin_log)){
				unlink($this->chemin_log);
			}
			$this->fp = fopen ("$ficlog","a");
		}
    
		/**
		* Ecriture d'une requ�te qui n'a pas pu �tre ex�cut�e dans le fichier de log
		* @access public
		*  @param string sql requ�te sql non 
		*/
		function record_log_file($logdata){
	        $date=date("D M j G:i:s T Y");
	        $chaine="$date;$logdata\n";	       
			$ligne = fputs($this->fp,$chaine);  
	    }
    
		/**
		* Fermeture du fichier de log
		* @access public
		*/
		function close_log_file(){
	        fclose($this->fp);    
	    }

	// ════════════════════════════════════════════════════════════════════════════════
	// MOBLOGS — Système de log riche pour l'import Excel des utilisateurs
	// Répertoire cible : StatEduc_burundi/moblogs/
	// Format : TIMESTAMP | LIGNE | ETAPE | LOGIN | SQL_ou_valeur | RESULTAT
	// ════════════════════════════════════════════════════════════════════════════════

	/**
	 * Ouvre le fichier de log riche dans StatEduc_burundi/moblogs/.
	 * Le nom du fichier est basé sur le timestamp d'ouverture :
	 *   import_YYYYMMDD_HHMMSS_<basename_excel>.log
	 *
	 * @param  string $cheminFichierExcel  Chemin complet du fichier Excel importé
	 * @return void
	 */
	public function create_mob_log($cheminFichierExcel) {
		// Remonter à la racine StatEduc_burundi/ depuis server-side/import_export/
		// __FILE__ = .../server-side/classes/metier/user.class.php
		$base_app = realpath(dirname(__FILE__) . '/../../../..');
		$mob_dir  = $base_app . DIRECTORY_SEPARATOR . 'moblogs';

		// Créer le répertoire si absent (robustesse déploiement)
		if (!is_dir($mob_dir)) {
			@mkdir($mob_dir, 0755, true);
		}

		$ts       = date('Ymd_His');
		$basename = basename($cheminFichierExcel, '.xlsx');
		$this->mob_log_path = $mob_dir . DIRECTORY_SEPARATOR . 'import_' . $ts . '_' . $basename . '.log';
		$this->mob_fp = @fopen($this->mob_log_path, 'a');

		if ($this->mob_fp) {
			$sep = str_repeat('=', 100);
			$header  = $sep . "\n";
			$header .= "MOBLOGS — Import Excel Utilisateurs StatEduc Burundi\n";
			$header .= "Fichier   : " . basename($cheminFichierExcel) . "\n";
			$header .= "Date/heure: " . date('Y-m-d H:i:s') . "\n";
			$header .= "Initiateur: " . (isset($_SESSION['code_user']) ? $_SESSION['code_user'] : 'N/A') . "\n";
			$header .= $sep . "\n";
			$header .= sprintf("%-23s | %-4s | %-30s | %-20s | %-s\n",
				'TIMESTAMP', 'LGN', 'ETAPE', 'LOGIN', 'DETAIL');
			$header .= str_repeat('-', 100) . "\n";
			fputs($this->mob_fp, $header);
		}
	}

	/**
	 * Écrit une ligne de log structurée dans le fichier moblogs.
	 * Format tabulaire : TIMESTAMP | LIGNE | ETAPE | LOGIN | DETAIL
	 *
	 * @param  int    $ligne   Numéro de ligne Excel (1-based)
	 * @param  string $etape   Étape : ADMIN_USERS_INSERT | HIER_LOOKUP | DICO_INSERT |
	 *                                 DOUBLON_SKIP | TRANSACTION | VALIDATION | ERROR
	 * @param  string $login   Login de l'utilisateur traité ($tab[4])
	 * @param  string $detail  Détail libre : SQL, valeur, message d'erreur…
	 * @return void
	 */
	public function write_mob_log($ligne, $etape, $login, $detail) {
		if (empty($this->mob_fp)) { return; }
		$ts = date('Y-m-d H:i:s');
		$line = sprintf("%-23s | %-4s | %-30s | %-20s | %s\n",
			$ts,
			str_pad((string)$ligne, 4, ' ', STR_PAD_LEFT),
			substr($etape,  0, 30),
			substr($login,  0, 20),
			$detail
		);
		fputs($this->mob_fp, $line);
	}

	/**
	 * Ferme proprement le fichier moblogs et affiche le chemin dans error_log.
	 * @return void
	 */
	public function close_mob_log() {
		if (empty($this->mob_fp)) { return; }
		$footer  = str_repeat('-', 100) . "\n";
		$footer .= "FIN LOG — " . date('Y-m-d H:i:s') . "\n";
		$footer .= str_repeat('=', 100) . "\n";
		fputs($this->mob_fp, $footer);
		fclose($this->mob_fp);
		$this->mob_fp = null;
		error_log('[moblogs] Log fermé : ' . (isset($this->mob_log_path) ? $this->mob_log_path : 'N/A'));
	}
	// ════════════════════════════════════════════════════════════════════════════════
	// FIN MOBLOGS
	// ════════════════════════════════════════════════════════════════════════════════

}
?>
