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
			$this->create_log_file($cheminFichierExcel);   
			foreach ($this->donnees_post_excel as $tab) {
				$logData = "";
				$logData .= $tab[0].";".$tab[1].";".$tab[2].";".$tab[3].";".$tab[4].";".$this->get_group_name_by_code($tab[6]);
				if (empty($tab[4]) || empty($tab[5]) || empty($tab[6])) {
					array_push($tab, '<span class="error">'.$this->recherche_libelle_page('UserMandatoryDetails',$_SESSION['langue'],'user').'</span>'); 
					$logData .= ";".$this->recherche_libelle_page('UserMandatoryDetails',$_SESSION['langue'],'user');
				} else {
					$sql =  'SELECT EXISTS ( SELECT '.$this->champ_id.' FROM '.$this->nom_table.' WHERE '.$this->champ_lib.' like '.$this->conn->qstr($tab[4]).' );';
					if ($this->conn->GetOne($sql)) {
						array_push($tab, '<span class="error">'.$this->recherche_libelle_page('LoginExist',$_SESSION['langue'],'user').'</span>'); 
						$logData .= ";".$this->recherche_libelle_page('LoginExist',$_SESSION['langue'],'user');
					} else {
						$sql =  'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->champ_name_user.','.$this->champ_email_user.','.$this->champ_tel_user.','.$this->champ_lib.','.$this->champ_ordre.','.$this->champ_systeme.','.$this->champ_user_parent.')'.
						' VALUES('.$tab[0].','.$this->conn->qstr($tab[1]).','.$this->conn->qstr($tab[2]).','.$this->conn->qstr($tab[3]).','.$this->conn->qstr($tab[4]).','.$this->conn->qstr($tab[5]).','.$this->conn->qstr($tab[6]).','.$_SESSION['code_user'].')';
						
						if ($this->conn->Execute($sql)===false) {
							array_push($tab, '<span class="error">'.$this->recherche_libelle_page('ERR_SQL',$_SESSION['langue'],'user').'</span>'); 
							$logData .= ";".$this->recherche_libelle_page('ERR_SQL',$_SESSION['langue'],'user');
						} else {
							array_push($tab, '<span class="success">OK</span>'); 	
							$logData .= ";OK";
						}							
					}
				}
				$tab[6] = $this->get_group_name_by_code($tab[6]);
				$result[] = $tab;
				$this->record_log_file($logData);
			}
			$this->close_log_file();
		}
		return $result;
	}   
	
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

}
?>
