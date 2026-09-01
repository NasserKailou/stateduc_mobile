<?php 
class nomenclature {

    	
		/**
		* Attribut : $code_systeme
		* <pre>
		* Secteur choisi
		* </pre>
		* @var 
		* @access public
		*/   
		public $code_systeme;
    	
		/**
		* Attribut : $conn
		* <pre>
		* Variable de connexion à la base
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn;
    	
		/**
		* Attribut : $nom_table
		* <pre>
		* Table de nomenclature choisi
		* </pre>
		* @var 
		* @access public
		*/   
		public $nom_table;
    	
		/**
		* Attribut : $nom_table
		* <pre>
		* Table de nomenclature associee
		* </pre>
		* @var 
		* @access public
		*/   
		public $nom_table_assoc;
    	
		/**
		* Attribut : $bln_lie_systeme
		* <pre>
		* booléen pour Table liée ou  non au secteur
		* </pre>
		* @var 
		* @access public
		*/   
		public $bln_lie_systeme;
		
		/**
		* Attribut : $bln_lie_systeme_parent
		* <pre>
		* booléen pour Table Parent liée ou  non au secteur
		* </pre>
		* @var 
		* @access public
		*/   
		public $bln_lie_systeme_parent;
    	
		/**
		* Attribut : $defaut_langue
		* <pre>
		*  Langue courante
		* </pre>
		* @var 
		* @access public
		*/   
		public $defaut_langue;
    	
		/**
		* Attribut : $valeurs_nomenclatures
		* <pre>
		*  ...
		* </pre>
		* @var 
		* @access public
		*/   
		public $valeurs_nomenclatures   = array();
    	
		/**
		* Attribut : $donnees_bdd
		* <pre>
		*  données contenues dans la table de nomenclature
		* </pre>
		* @var 
		* @access public
		*/   
		public $donnees_bdd = array();
    	
		/**
		* Attribut : $donnees_post
		* <pre>
		* données postées
		* </pre>
		* @var 
		* @access public
		*/   
		public $donnees_post = array();
    	
		/**
		* Attribut : $matrice_donnees_template
		* <pre>
		* données en base structurées pour le template
		* </pre>
		* @var 
		* @access public
		*/   
		public $matrice_donnees_template = array();
    	
		/**
		* Attribut : $matrice_donnees_bdd
		* <pre>
		* données structurées pour la base
		* </pre>
		* @var 
		* @access public
		*/   
		public $matrice_donnees_bdd = array();
    
    	
		/**
		* Attribut : $matrice_donnees
		* <pre>
		* données de la table de nomenclature limitées 
		* par rapport aux lignes du template
		* </pre>
		* @var 
		* @access public
		*/   
		public $matrice_donnees = array();
    	
		/**
		* Attribut : $template
		* <pre>
		*  nom du template 
		* </pre>
		* @var 
		* @access public
		*/   
		public $template ;
    	
		/**
		* Attribut : $entete_template
		* <pre>
		*  partie entete du template où on choisit la table, le secteur, ...
		* </pre>
		* @var 
		* @access public
		*/   
		public $entete_template;
    	
		/**
		* Attribut : $fin_template
		* <pre>
		*  pied du template pouvant comporter la barre de navigation
		* </pre>
		* @var 
		* @access public
		*/   
		public $fin_template;
    	
		/**
		* Attribut : $nb_lignes
		* <pre>
		* nb de lignes du template
		* </pre>
		* @var 
		* @access public
		*/   
		public $nb_lignes = 8;   
    	
		/**
		* Attribut : $VARS_GLOBALS
		* <pre>
		* tableau de variables globales internes utilisées par la classe
		* </pre>
		* @var 
		* @access public
		*/   
		public $VARS_GLOBALS = array();
		
		/**
		* Attribut : $val_ind
		* <pre>
		*  booléen pour savoir si la table contient ou non une valeur indéterminée
		* </pre>
		* @var 
		* @access public
		*/   
		public $val_ind = false;
    
    	
		/**
		* Attribut : $champ_id
		* <pre>
		*  nom du champ (CODE) : CODE_TABLE
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_id;
    	
		/**
		* Attribut : $champ_lib
		* <pre>
		*  nom du LIBELLE (LIBELLE) : LIBELLE_TABLE
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_lib;
    	
		/**
		* Attribut : $champ_ordre
		* <pre>
		* nom du ORDRE (ORDRE) : ORDRE_TABLE
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_ordre; 
    	
		/**
		* Attribut : $champ_systeme
		* <pre>
		*  nom du champ se référant au secteur dans la table liée au systeme
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_systeme ;
    	
		/**
		* Attribut : $valeur_filtre 
		* <pre>
		*  valeur par défaut exclue dans les manips
		* </pre>
		* @var 
		* @access public
		*/   
		public $valeur_filtre = 255;
    	
		/**
		* Attribut : $lire_dico
		* <pre>
		*  booléen pour dire si oui ou non les libellés nomenc
		*  doivent etre lues à partir de DICO_TRADUCTION
		* </pre>
		* @var 
		* @access public
		*/   
		public $lire_dico;
    	
		/**
		* Attribut : $afficher_secteur
		* <pre>
		*  pour afficher les libellés par jointure avec la table système
		* </pre>
		* @var 
		* @access public
		*/   
		public $afficher_secteur;
    	
		/**
		* Attribut : $insertion_dico
		* <pre>
		*  Pour inserrer ou non  dans la table DICO_TRADUCTION
		* </pre>
		* @var 
		* @access public
		*/   
		public $insertion_dico;    
    
    	
		/**
		* Attribut : $list_tables
		* <pre>
		*  liste des tables nomenclatures
		* </pre>
		* @var 
		* @access public
		*/  
		 
		public $list_tables = array();
		
		
    	/**
		* Attribut : $list_tables_parents
		* <pre>
		*  liste des tables nomenclatures parents
		* </pre>
		* @var 
		* @access public
		*/   
		public $list_tables_parents = array();
    	
		/**
		* Attribut : $list_secteurs
		* <pre>
		*  liste des secteurs existants en base
		* </pre>
		* @var 
		* @access public
		*/   
		public $list_secteurs = array();
    
    	
		/**
		* Attribut : $id_name
		* <pre>
		*  code traduction de CODE
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_name;
    	
		/**
		* Attribut : $lib_name
		* <pre>
		*  code traduction de LIBELLE
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_name;
    	
		/**
		* Attribut : $lib_ordre
		* <pre>
		*  code traduction de ORDRE
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_ordre;
    	
		/**
		* Attribut : $lib_entete
		* <pre>
		*  code traduction de l'entête du Formulaire
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_entete;
        
    	public $afficher_table;
		public $list_champs_assoc	= array();
		
		/**
		* METHODE : __construct
		* <pre>
		*  Constructeur
		* </pre>
		* @access public
		* 
		*/
		public function __construct($id_systeme,$lib_nom_table,$nom_tab_assoc,$champ_assoc,$afficher_table=true,$langue,$lire_dico,$insertion_dico,$afficher_secteur,$conn){

				$this->champ_systeme        =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
				$this->code_systeme         =   $id_systeme;
				$this->nom_table            =   $lib_nom_table;            
				$this->nom_table_assoc      =   $nom_tab_assoc;
				$this->champ_assoc			=	$champ_assoc;
                if ($nom_tab_assoc<>'') $this->nom_champ_assoc		=	$GLOBALS['PARAM']['CODE'].'_'.$nom_tab_assoc;
				else $this->nom_champ_assoc ='';
				$this->afficher_table       =   $afficher_table;      
				$this->defaut_langue        =   $langue;
				$this->conn                 =   $conn;            
				$this->template             =   file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'nomenclature.html');     
				if (trim($lib_nom_table)=='DICO_MESSAGE') {               
						$this->champ_id             =   'CODE_'.$lib_nom_table;
						$this->champ_lib            =   'LIBELLE_'.$lib_nom_table;
						$this->champ_ordre          =   'ORDRE_'.$lib_nom_table; 
				}else{
						$this->champ_id             =   $GLOBALS['PARAM']['CODE'].'_'.$lib_nom_table;
						$this->champ_lib            =   $GLOBALS['PARAM']['LIBELLE'].'_'.$lib_nom_table;
						$this->champ_ordre          =   $GLOBALS['PARAM']['ORDRE'].'_'.$lib_nom_table; 
				}
				$this->lire_dico            =   $lire_dico; 
				$this->afficher_secteur     =   $afficher_secteur;
				$this->insertion_dico       =   $insertion_dico;
				
				if (in_array(strtoupper($this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$this->conn->MetaTables('TABLES'))))
						 $this->bln_lie_systeme  =   true; 
				else
						 $this->bln_lie_systeme  =   false; 
				if (in_array(strtoupper($this->nom_table_assoc.'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$this->conn->MetaTables('TABLES'))))
						 $this->bln_lie_systeme_parent  =   true; 
				else
						 $this->bln_lie_systeme_parent  =   false; 
				
				
				//$this->set_val_ind();
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
			if ($this->nom_table =='ADMIN_GROUPES'){
				$this->conn =   $GLOBALS['conn_dico'];
			} else{
				$this->conn =   $GLOBALS['conn'];
			}
			
		}
    
    
		/**
		* METHODE : get_donnees()
		* <pre>
		*  Récupére les données en base
		* </pre>
		* @access public
		* 
		*/
		public function get_donnees(){
        
		
        /* bass
				if (in_array($this->nom_table.'_SYSTEME',$this->conn->MetaTables('TABLES')))
             $this->bln_lie_systeme  =   true; 
        else
             $this->bln_lie_systeme  =   false; 
        */
        if ( $this->bln_lie_systeme ) {
            //$this->bln_lie_systeme  =   true; 
            if ($this->lire_dico ==false) {           
            	if($this->champ_assoc=='' || $this->nom_champ_assoc==''){       
				    $requete    ='SELECT DISTINCT '.$this->nom_table.'.* FROM '.$this->nom_table. ','.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].' WHERE '.
                                 $this->nom_table.'.'.$this->champ_id.'='.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$this->champ_id.
                                 ' AND '.$this->champ_systeme.'='.$this->code_systeme;
								 //' ORDER BY '.$this->nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table; 
				}else{
					 $requete    ='SELECT DISTINCT '.$this->nom_table.'.* FROM '.$this->nom_table. ','.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].' WHERE '.
                                 $this->nom_table.'.'.$this->champ_id.'='.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$this->champ_id.
                                 ' AND '.$this->champ_systeme.'='.$this->code_systeme.' AND '.$this->nom_champ_assoc.'='. $this->champ_assoc;
								 //' ORDER BY '.$this->nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table;
				}
            }else{
                if($this->champ_assoc=='' || $this->nom_champ_assoc==''){
					$requete='SELECT DISTINCT '.$this->nom_table.'.'.$this->champ_id.','.'DICO_TRADUCTION.LIBELLE AS '.$this->champ_lib.','.
                             $this->nom_table.'.'.$this->champ_ordre.' FROM '.$this->nom_table. ','.
                             $this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].', DICO_TRADUCTION WHERE '.
                             $this->nom_table.'.'.$this->champ_id.'=DICO_TRADUCTION.CODE_NOMENCLATURE AND '.
                             'DICO_TRADUCTION.NOM_TABLE='.'\''.$this->nom_table.'\' AND '.
                             $this->nom_table.'.'.$this->champ_id.'='.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$this->champ_id.
                             ' AND '.$this->champ_systeme.'='.$this->code_systeme.
                             ' AND DICO_TRADUCTION.CODE_LANGUE=\''.$this->defaut_langue.'\'';
							 //' ORDER BY '.$this->nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table;                             
                }else{
					$requete='SELECT DISTINCT '.$this->nom_table.'.'.$this->champ_id.','.'DICO_TRADUCTION.LIBELLE AS '.$this->champ_lib.','.
                             $this->nom_table.'.'.$this->champ_ordre.' FROM '.$this->nom_table. ','.
                             $this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].', DICO_TRADUCTION WHERE '.
                             $this->nom_table.'.'.$this->champ_id.'=DICO_TRADUCTION.CODE_NOMENCLATURE AND '.
                             'DICO_TRADUCTION.NOM_TABLE='.'\''.$this->nom_table.'\' AND '.
                             $this->nom_table.'.'.$this->champ_id.'='.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$this->champ_id.
                             ' AND '.$this->champ_systeme.'='.$this->code_systeme.
                             ' AND DICO_TRADUCTION.CODE_LANGUE=\''.$this->defaut_langue.'\''.' AND '.$this->nom_champ_assoc.'='. $this->champ_assoc;
							 //' ORDER BY '.$this->nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table;
				}             
                             
            }            
        } else {
            //$this->bln_lie_systeme  =   false;
			
            if ($this->lire_dico ==false) { 
            	if($this->champ_assoc=='' || $this->nom_champ_assoc==''){   
			    	$requete    ='SELECT DISTINCT * FROM '.$this->nom_table;
								// ' ORDER BY '.$this->nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table;
				}else{
					$requete    ='SELECT DISTINCT * FROM '.$this->nom_table.' WHERE '.$this->nom_champ_assoc.'='. $this->champ_assoc;
								// ' ORDER BY '.$this->nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table;
				}          
            }else{
				
                if($this->champ_assoc=='' || $this->nom_champ_assoc==''){ 
					$requete='SELECT DISTINCT '.$this->nom_table.'.'.$this->champ_id.','.'DICO_TRADUCTION.LIBELLE AS '.$this->champ_lib.','.
                             $this->nom_table.'.'.$this->champ_ordre.' FROM '.$this->nom_table. ','.
                             ' DICO_TRADUCTION WHERE '.
                             $this->nom_table.'.'.$this->champ_id.'=DICO_TRADUCTION.CODE_NOMENCLATURE AND '.
                             'DICO_TRADUCTION.NOM_TABLE='.'\''.$this->nom_table.'\''.
                             ' AND DICO_TRADUCTION.CODE_LANGUE=\''.$this->defaut_langue.'\'';
							// ' ORDER BY '.$this->nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table;
							 //echo($this->nom_table);exit;
				}else{
					$requete='SELECT DISTINCT '.$this->nom_table.'.'.$this->champ_id.','.'DICO_TRADUCTION.LIBELLE AS '.$this->champ_lib.','.
                             $this->nom_table.'.'.$this->champ_ordre.' FROM '.$this->nom_table. ','.
                             ' DICO_TRADUCTION WHERE '.
                             $this->nom_table.'.'.$this->champ_id.'=DICO_TRADUCTION.CODE_NOMENCLATURE AND '.
                             'DICO_TRADUCTION.NOM_TABLE='.'\''.$this->nom_table.'\''.
                             ' AND DICO_TRADUCTION.CODE_LANGUE=\''.$this->defaut_langue.'\''.' AND '.$this->nom_champ_assoc.'='. $this->champ_assoc;
							 //' ORDER BY '.$this->nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table;
				}
            }
			
			
        }  
		
       
        $tab_donnees    =   array();
        // Gestion des erreurs avant l'exécution de la requête
        
        try {
            $valeurs = $this->conn->GetAll($requete); 
            if (!is_array($valeurs))    
                throw new Exception('ERR_SQL');                           
            if (count($valeurs)>0) {
                for ($i=0;$i<count($valeurs);$i++){
                    $tab_donnees []    =   $valeurs[$i];
                }
            }      
        } 
        catch (Exception $e){
            $erreur = new erreur_manager($e,$requete);
        }
           
        $this->VARS_GLOBALS['donnees_bdd']   =   $tab_donnees;        
        $this->limiter_affichage();
        $this->init_liste_table();
        
        
    }
		
		
		/**
		* METHODE : set_val_ind()
		* <pre>
		*  Renseigne la variable $this->val_ind
		* </pre>
		* @access public
		* 
		*/
		public function set_val_ind(){
			$this->val_ind = false ;
			$requete    ='SELECT * FROM ' .$this->nom_table. ' WHERE '. $this->champ_id . ' = 255';                             
			try {
				$res = $this->conn->GetRow($requete); 
				if (!is_array($res))    
					throw new Exception('ERR_SQL');                           
				if (count($res)>0){
					$this->val_ind=true;
				}      
			} 
			catch (Exception $e){
				$erreur = new erreur_manager($e,$requete);
			} 
		} 
		
		
		/**
		* METHODE : manage_maj_val_ind()
		* <pre>
		*  Mise en base des valeurs indétermnées
		* </pre>
		* @access public
		* 
		*/
		public function manage_maj_val_ind(){
				//if( isset($_POST['val_ind']) && ($_POST['val_ind'] == 1) && ($this->val_ind == false)){ // ajout vals indét
				if( isset($_POST['val_ind']) && ($_POST['val_ind'] == 1) and (!isset($_POST['do_import_nomenc'])) ){ // ajout vals indét
						///////////////////
						if ($this->bln_lie_systeme == true) {
								$sql    =   ' DELETE FROM ' . $this->nom_table . '_' . $GLOBALS['PARAM']['SYSTEME'] . 
														' WHERE ' . $this->champ_id . ' = 255 AND ' . $this->champ_systeme . '=' .$this->code_systeme; 
								if (($rs = $this->conn->Execute($sql))===false){echo "<br> $sql <br>";}
								
								$sql    =   'INSERT INTO '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].'('.$this->champ_id.','.$this->champ_systeme.')'.
														 ' VALUES (255,'.$this->code_systeme.')'; 
								if (($rs = $this->conn->Execute($sql))===false){echo "<br> $sql <br>";}
						}
						$sql    =   'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->champ_lib.','.$this->champ_ordre.')'.
												'VALUES (255,\'Indeterminée\',255)'; 
						if (($rs = $this->conn->Execute($sql))===false){echo "<br> $sql <br>";} 
						insert_traduction('DICO_TRADUCTION','255', $this->nom_table, $this->defaut_langue, 'Indeterminée', 1);
						///////////////////
				}elseif(!isset($_POST['val_ind']) && $this->val_ind == true){ // supp val indét
						///////////////////
						if ($this->bln_lie_systeme == true) {
								$sql    =   ' DELETE FROM ' . $this->nom_table . '_' . $GLOBALS['PARAM']['SYSTEME'] . 
														' WHERE ' . $this->champ_id . ' = 255'; 
								if (($rs = $this->conn->Execute($sql))===false){echo "<br> $sql <br>";}
						}
						$sql    =   'DELETE FROM '.$this->nom_table.' WHERE ' . $this->champ_id . ' = 255'; 
						if (($rs = $this->conn->Execute($sql))===false){echo "<br> $sql <br>";}
						
						$sql = 'DELETE FROM DICO_TRADUCTION WHERE CODE_NOMENCLATURE = 255 AND NOM_TABLE = ' .	'\'' . $this->nom_table . '\'';
						if (($rs = $this->conn->Execute($sql))===false){echo "<br> $sql <br>";}
						///////////////////
				}
		}         

    
		/**
		* METHODE : transformer_donnees()
		* <pre>
		*  Structuration des données pour le template
		* </pre>
		* @access public
		* 
		*/
		public function transformer_donnees(){
       
       $this->matrice_donnees_template = array();
        
        // Contrôle de la taille des noms des champs  
        //echo 'Type base = '.$this->conn->dabaseType.'<br>';        
        if (strlen($this->champ_id)>30) {
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9') { 
                    $taille_max_id=strlen($this->champ_id);                
                }else{
                    $taille_max_id=31;                
                }
            }else{
                $taille_max_id=strlen($this->champ_id);
            }
            
            if (strlen($this->champ_lib)>30) {
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9') { 
                    $taille_max_lib=strlen($this->champ_lib);                
                }else{
                    $taille_max_lib=31;                
                }
            }else{
                $taille_max_lib=strlen($this->champ_lib);
            }
            
            if (strlen($this->champ_ordre)>30) {
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9') { 
                    $taille_max_ordre=strlen($this->champ_ordre) ;              
                }else{
                    $taille_max_ordre=31;                
                }
            }else{
                $taille_max_ordre=strlen($this->champ_ordre);
            }
           
		    if (strlen($this->nom_champ_assoc)>30) {
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9') { 
                    $taille_max_chp_assoc=strlen($this->nom_champ_assoc) ;              
                }else{
                    $taille_max_chp_assoc=31;                
                }
            }else{
                $taille_max_chp_assoc=strlen($this->nom_champ_assoc);
            }
			
        $champ_id = substr($this->champ_id ,0,$taille_max_id); 
        $champ_lib = substr($this->champ_lib ,0,$taille_max_lib);
        $champ_ordre = substr($this->champ_ordre ,0,$taille_max_ordre);
        $nom_champ_assoc = substr($this->nom_champ_assoc ,0,$taille_max_chp_assoc);
		
        // Fin du contrôle de la taille des noms des champs
            
            
       foreach ($this->matrice_donnees as $ligne_donnes) {
            $donnees_transformees = array();   
            $donnees_transformees[] = $ligne_donnes[$champ_id ]; 
            if($nom_champ_assoc <> $GLOBALS['PARAM']['CODE'].'_') $donnees_transformees[] = $ligne_donnes[$nom_champ_assoc];
            $donnees_transformees[] = $ligne_donnes[$champ_lib];
            $donnees_transformees[] = $ligne_donnes[$champ_ordre];
            
            $this->matrice_donnees_template[] = $donnees_transformees; 
            
        }
        /*echo 'champ id = '.$champ_id.'<br>';
        echo '<pre> données inti';
        print_r($this->matrice_donnees);
        echo '<pre> données trans';
        print_r($this->matrice_donnees_template);
        echo '<pre>';*/
    }
    
    
		/**
		* METHODE : limiter_affichage()
		* <pre>
		*  limiter les données extraites / aux lignes disponibles
		* </pre>
		* @access public
		* 
		*/
		public function limiter_affichage(){
			if(!isset($GLOBALS['nbre_total_enr'])){
				$GLOBALS['nbre_total_enr'] = count( $this->VARS_GLOBALS['donnees_bdd'] ); 
			}                   
					
			$mat = array_slice( $this->VARS_GLOBALS['donnees_bdd'], $GLOBALS['debut'], $GLOBALS['cfg_nbres_ppage']);               
			
			foreach( $mat as $ligne_donnees){
				$this->matrice_donnees[] = $ligne_donnees;
			}
			$GLOBALS['nbenr'] = count($this->matrice_donnees);
			// transfromation des donnees
			$this->transformer_donnees();
		}    
   
		/**
		* METHODE : remplir_template($template)
		* <pre>
		*  Associer les valeurs extraites aux variables du template 
		* </pre>
		* @access public
		* 
		*/
		public function remplir_template($template){
    
        // Contrôle de la taille des noms des champs
        if (strlen($this->champ_id)>30) {
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9') { 
                    $taille_max_id=strlen($this->champ_id);                
                }else{
                    $taille_max_id=31;                
                }
            }else{
                $taille_max_id=strlen($this->champ_id);
            }
            
            if (strlen($this->champ_lib)>30) {
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9') { 
                    $taille_max_lib=strlen($this->champ_lib);                
                }else{
                    $taille_max_lib=31;                
                }
            }else{
                $taille_max_lib=strlen($this->champ_lib);
            }
            
            if (strlen($this->champ_ordre)>30) {
                if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9') { 
                    $taille_max_ordre=strlen($this->champ_ordre);                
                }else{
                    $taille_max_ordre=31;                
                }
            }else{
                $taille_max_ordre=strlen($this->champ_ordre);
            }
            
        $champ_id = substr($this->champ_id ,0,$taille_max_id); 
        $champ_lib = substr($this->champ_lib ,0,$taille_max_lib);
        $champ_ordre = substr($this->champ_ordre ,0,$taille_max_ordre);
        
        // Fin du Contrôle de la taille du nom des champs
            
        if (is_array($this->matrice_donnees)){          
            
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                // cas des champs id                   
                if (isset($this->matrice_donnees[$ligne][$champ_id ])){
                  //  echo 'passé la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$champ_id];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'CODE_'.$ligne} = ''.$val_champ_base.'';                                      

                }else{
                    ${'CODE_'.$ligne} = '';	
                }
                
                // cas des champs libellé
                if (isset($this->matrice_donnees[$ligne][$champ_lib])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$champ_lib];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'LIBELLE_'.$ligne} = ''.$val_champ_base.'';
                }else{
                    ${'LIBELLE_'.$ligne} = '';	
                }
                
                // cas des champs ordre
                if (isset($this->matrice_donnees[$ligne][$champ_ordre])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$champ_ordre];
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
       
      // return eval("echo \"$template\";");
        eval('$result = sprintf(\'%s\', "'.str_replace('"','\"',$template).'");');
        return $result;
    }
    
		/**
		* METHODE : recherche_libelle_page($code,$langue,$table)
		* <pre>
		*  recherche un libellé page par rapport aux $code,$langue,$table
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
				// Gestion des erreurs lors de l'exécution de la requête SQL
				
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
		* METHODE : recherche_libelle($code,$langue,$table)
		* <pre>
		*  recherche une traduction par rapport aux $code,$langue,$table
		* </pre>
		* @access public
		* 
		*/
		public function recherche_libelle($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					$conn                 =   $GLOBALS['conn'];
				} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					$conn                 =   $GLOBALS['conn_dico']; 
				}
				
				$requete 	= "SELECT LIBELLE
										FROM DICO_TRADUCTION 
										WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
										AND NOM_TABLE='".$table."'";
				
				// Gestion des erreurs lors de l'exécution de la requête SQL
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
		* METHODE : get_post_template($matr)
		* <pre>
		*  Fonction de récupération du POST
		* </pre>
		* @access public
		* 
		*/
		public function get_post_template($matr){      
              
        if (is_array($matr)){              
            
            $max_cle_incr = $this->get_cle_max();     
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                $champ_id       = 'CODE'.'_'.$ligne;
                $champ_lib      = 'LIBELLE'.'_'.$ligne;
                $champ_ordre    = 'ORDRE'.'_'.$ligne;
                $delete         = 'DELETE_'.$ligne;               
                $nom_chp_assoc  =  'champ_assoc';             
                
                if (isset($matr[$champ_id]) && $matr[$champ_id]<>''){                   
                    if (!isset($matr[$delete])) {
                        $donnees_ligne  =   array();
                        $donnees_ligne [] = $matr[$champ_id];
                        if(($this->nom_champ_assoc <> $GLOBALS['PARAM']['CODE'].'_')  && (trim($matr[$nom_chp_assoc])<>'')) $donnees_ligne [] = $matr[$nom_chp_assoc];
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
                        if(($this->nom_champ_assoc <> $GLOBALS['PARAM']['CODE'].'_') && (trim($matr[$nom_chp_assoc])<>'')) $donnees_ligne [] = $matr[$nom_chp_assoc];
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
		* METHODE : get_cle_max()
		* <pre>
		*  recherche du code max ds la table de nomenc
		* </pre>
		* @access public
		* 
		*/
		public function get_cle_max(){
			$max_return = 0;
			$sql = ' SELECT  MAX('.$this->champ_id.') as MAX_INSERT FROM  '.$this->nom_table.
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
		* METHODE :  get_champ_extract($nom_champ):
		*
		* Retourne les 30 ou 31 premiers caractères de $nom_champ en fonction du type de SGBD 
		* pour permttre d'extraire correctement la valeur du champ $nom_champ dans un recordset
		*
		* @access public
		* 
		*/
		function get_champ_extract($nom_champ){
			$champ_extract = $nom_champ;
			if (strlen($nom_champ)>30) {
				if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql' || $this->conn->databaseType == 'postgres9') { 
						$taille_max_extract=strlen($nom_champ);                
				}else{
						$taille_max_extract=31;                
				}
				$champ_extract = substr($nom_champ, 0, $taille_max_extract); 
			}
			return($champ_extract);
		}
		
    	public function init_liste_champ_assoc(){
			if($this->nom_table_assoc <> ''){
				
				
				/////////////////////////////////////////
				
				if ( $this->bln_lie_systeme_parent ) {
					if ($this->lire_dico ==false) {           
						$requete    ='SELECT DISTINCT '.$this->nom_table_assoc.'.* FROM '.$this->nom_table_assoc. ','.$this->nom_table_assoc.'_'.$GLOBALS['PARAM']['SYSTEME'].' WHERE '.
									 $this->nom_table_assoc.'.'.$GLOBALS['PARAM']['CODE'].'_'.$this->nom_table_assoc.'='.$this->nom_table_assoc.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$this->nom_table_assoc.
									 ' AND '.$this->champ_systeme.'='.$this->code_systeme.
									 ' ORDER BY '.$this->nom_table_assoc.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table_assoc; 
						
					}else{
						$requete='SELECT DISTINCT '.$this->nom_table_assoc.'.'.$GLOBALS['PARAM']['CODE'].'_'.$this->nom_table_assoc.','.'DICO_TRADUCTION.LIBELLE AS '.$GLOBALS['PARAM']['LIBELLE'].'_'.$this->nom_table_assoc.','.
								 $this->nom_table_assoc.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table_assoc.' FROM '.$this->nom_table_assoc. ','.
								 $this->nom_table_assoc.'_'.$GLOBALS['PARAM']['SYSTEME'].', DICO_TRADUCTION WHERE '.
								 $this->nom_table_assoc.'.'.$GLOBALS['PARAM']['CODE'].'_'.$this->nom_table_assoc.'=DICO_TRADUCTION.CODE_NOMENCLATURE AND '.
								 'DICO_TRADUCTION.NOM_TABLE='.'\''.$this->nom_table_assoc.'\' AND '.
								 $this->nom_table_assoc.'.'.$GLOBALS['PARAM']['CODE'].'_'.$this->nom_table_assoc.'='.$this->nom_table_assoc.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$this->nom_table_assoc.
								 ' AND '.$this->champ_systeme.'='.$this->code_systeme.
								 ' AND DICO_TRADUCTION.CODE_LANGUE=\''.$this->defaut_langue.'\''.
								 ' ORDER BY '.$this->nom_table_assoc.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table_assoc;                             
						           
					}            
				} else {
					if ($this->lire_dico ==false) { 
							$requete    ='SELECT DISTINCT * FROM '.$this->nom_table_assoc.
										 ' ORDER BY '.$this->nom_table_assoc.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table_assoc;
					}else{
						$requete='SELECT DISTINCT '.$this->nom_table_assoc.'.'.$GLOBALS['PARAM']['CODE'].'_'.$this->nom_table_assoc.','.'DICO_TRADUCTION.LIBELLE AS '.$GLOBALS['PARAM']['LIBELLE'].'_'.$this->nom_table_assoc.','.
								 $this->nom_table_assoc.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table_assoc.' FROM '.$this->nom_table_assoc. ','.
								 ' DICO_TRADUCTION WHERE '.
								 $this->nom_table_assoc.'.'.$GLOBALS['PARAM']['CODE'].'_'.$this->nom_table_assoc.'=DICO_TRADUCTION.CODE_NOMENCLATURE AND '.
								 'DICO_TRADUCTION.NOM_TABLE='.'\''.$this->nom_table_assoc.'\''.
								 ' AND DICO_TRADUCTION.CODE_LANGUE=\''.$this->defaut_langue.'\''.
								 ' ORDER BY '.$this->nom_table_assoc.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$this->nom_table_assoc;
					}
				} 
				
				///////////////////////////////////////
				
				
				try {            
					if( ($rs = $this->conn->GetAll($requete) )===false)                
						 throw new Exception('ERR_SQL');   
					foreach ($rs as $chp){            
						$champ = array();
						$champ [] = $chp[$this->get_champ_extract($GLOBALS['PARAM']['CODE'].'_'.$this->nom_table_assoc)];
						$champ [] = $chp[$this->get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$this->nom_table_assoc)];
						$this->list_champs_assoc[] = $champ;
					}
				}
				catch (Exception $e){
					 $erreur = new erreur_manager($e,$sql);
				}
			}
		}
		
		/**
		* METHODE : init_liste_table()
		* <pre>
		*  Renseigne la liste des tables de nomenc et prépare l'entête du formulaire
		* </pre>
		* @access public
		* 
		*/
		public function init_liste_table(){
        $tabl_exclues=array($GLOBALS['PARAM']['TYPE_ANNEE'], $GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'], $GLOBALS['PARAM']['TYPE_REGROUPEMENT'], $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']);
		foreach (array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES')) as $nom_table){  
            $nom_table =  strtoupper($nom_table);          
            //if (preg_match("/TYPE_/",$nom_table) && !preg_match("/_SYSTEME/",$nom_table)){  
            if ( (preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/',strtoupper($nom_table)) && !preg_match('/_'.$GLOBALS['PARAM']['SYSTEME'].'$/',strtoupper($nom_table))) or (strtoupper($nom_table)=='DICO_MESSAGE') ){               
                if( !in_array(strtoupper($nom_table), array_map('strtoupper',$tabl_exclues)) ){  
					$this->list_tables[]    =   $nom_table ;
				}
            }
            sort($this->list_tables );
        }

        $entete      =	"\n<script type=".'"'.'text/javascript'.'"'.">\n";	
        $entete     .=	"function recharge(table_nomenc,table_nomenc_assoc,champ_assoc, systeme,lire_dico,change_nomenc) {\n";
       	//$entete		.=	"if(table_nomenc.toLowerCase()=='ref_competence') table_nomenc_assoc='ref_domaine';\n";
		//$entete		.=	"if(table_nomenc.toLowerCase()=='ref_domaine') table_nomenc_assoc='ref_fonction';\n";
		//$entete		.=	"table_nomenc_assoc = tables[table_nomenc]['TABLE'];\n";
		//$entete		.=	"if(champ_assoc=='' || change_nomenc==1) champ_assoc=tables[table_nomenc]['FIELD'];\n";
		$entete		.=	"if(document.getElementById('table_nomenc_assoc') && table_nomenc_assoc!='') table_nomenc_assoc=document.getElementById('table_nomenc_assoc').value;\n";
		$entete		.=	"if(document.getElementById('champ_assoc') && champ_assoc!='') champ_assoc=document.getElementById('champ_assoc').value;\n";
		$entete	    .=	"if (table_nomenc_assoc == undefined) table_nomenc_assoc='';\n";
		$entete	    .=	"if (champ_assoc == undefined) champ_assoc='';\n";
		$entete	    .=	"if (document.getElementById('check').checked==true) lire_dico=1; else lire_dico=0;\n";
		$entete	    .=	"location.href   = '?val=nomenclature&lib_nom_table='+table_nomenc+'&table_nomenc_assoc='+table_nomenc_assoc+'&champ_assoc='+champ_assoc+'&secteur='+systeme+'&lire_dico='+lire_dico;\n";
        $entete     .=   " } \n";
        $entete     .=  " function Alert_Supp(checkbox){ \n";
        $entete     .=  " var chaine_eval ='document.form1.'+checkbox+'.checked == true';\n";       
        $entete     .=  " if (eval(chaine_eval)){ \n" ;
        $mess_alert	 =	$this->recherche_libelle(110,$_SESSION['langue'],'DICO_MESSAGE');
        $mess_alert	 =	addslashes($mess_alert);
       
        $entete     .=  "   alert ('$mess_alert');\n";
        $entete     .=  "}  }\n";
        $entete     .=  " </script>\n";

       // $entete     .= "<br />";
        //$entete     .="<form action='?val=nomenclature&lib_nom_table=".$this->nom_table."&secteur=".$this->code_systeme."&lire_dico=".$this->lire_dico." method='post'>";  
        $entete     .="<form name ='form1' action='".$_SERVER['REQUEST_URI']."' method='post'>"; 
				//$entete     .="<form name ='form1' action='?val=nomenclature&lib_nom_table=".$this->nom_table."&secteur=".$this->code_systeme."&lire_dico=".$this->lire_dico."' method='post'>"; 
				      
        $entete     .="<span class=''>";
        if ($this->afficher_secteur==true){
            $entete     .= '<div ><table ><tr><td >'.$this->recherche_libelle_page('SsSectCour',$_SESSION['langue'],'nomenclature').'</td><td colspan="3" > ';
            $entete     .="<select name='systeme' style='width:250px' onchange= \"recharge(table_nomenc.value,'','',systeme.value,lire_dico.value)\">";
            
            $this->init_liste_secteur();        
            foreach ($this->list_secteurs as $secteur) {
                $entete .= '<option value='.'\''.$secteur[0].'\'';
                
                if ($secteur[0]==$this->code_systeme) {
                $entete .=   ' selected ';   
                }
                
                $entete .= '>'.$secteur[1].'</option>';            
            }
            $entete     .='</select></td></tr>';
            
            $entete     .='<tr><td>'.$this->recherche_libelle_page('ListTable',$_SESSION['langue'],'nomenclature').'</td><td colspan="3">';            
            $entete     .="<select name='table_nomenc' style='width:250px' onchange= \"recharge(table_nomenc.value,'','',systeme.value,lire_dico.value,1)\">";
            
            foreach ($this->list_tables as $nom_table) {
                $entete .= '<option value='.'\''.$nom_table.'\'';
                if ($nom_table==$this->nom_table) {
                	$entete .=   ' selected ';   
                }
                
                $entete .= '>'.$nom_table.'</option>';            
            }
            $entete     .='</select></td></tr>';
			if( is_array($this->list_tables_parents) && count($this->list_tables_parents)){
				$entete     .='<tr><td >'.$this->recherche_libelle_page('TableLie',$_SESSION['langue'],'nomenclature').'</td><td>';            
				$entete     .="<select name='table_nomenc_assoc' id='table_nomenc_assoc' style='width:250px' onchange= \"recharge(table_nomenc.value,table_nomenc_assoc.value,'',systeme.value,lire_dico.value)\">";
				$entete 	.= '<option value="">---</option>';
				foreach ($this->list_tables_parents as $nom_table) {
					$entete .= '<option value='.'\''.$nom_table.'\'';
					if (trim($nom_table)==trim($this->nom_table_assoc)) {
						$entete .=   ' selected ';   
					}
					
					$entete .= '>'.$nom_table.'</option>';            
				}
				
				$entete     .='</select></td></tr>'; 
				
				$entete     .='<tr><td>'.$this->recherche_libelle_page('ChampLie',$_SESSION['langue'],'nomenclature').'</td><td>';            
				$this->init_liste_champ_assoc();
				//echo 'ici<pre>';
				//print_r($this->list_champs_assoc);
				$entete     .="<select name='champ_assoc' id='champ_assoc' onchange= 'recharge(table_nomenc.value,table_nomenc_assoc.value,champ_assoc.value,systeme.value,lire_dico.value)'>";
				$entete 	.= '<option value="">---</option>';
				if(count($this->list_champs_assoc)){
					
					foreach ($this->list_champs_assoc as $champ) {
						$entete .= '<option value='.'\''.$champ[0].'\'';
						if ($champ[0]==$this->champ_assoc) {
							$entete .=   ' selected ';   
						}
						
						$entete .= '>'.$champ[1].'</option>';            
					}
				}
				
				$entete     .='</select></td></tr>'; 
			}
			
		    $entete     .="<tr><td>".$this->recherche_libelle_page('LireTraduc',$_SESSION['langue'],'nomenclature')."</td>
						<td><INPUT id='check' type='checkbox' name='lire_dico' value='1' ";
						if ($this->lire_dico==1){
                $entete .=' checked ';
            }
            $entete .=  "onclick=\"recharge(table_nomenc.value,'','',systeme.value,lire_dico.value)\"></td>";
			$entete  .= '<td align="right" colspan="2" nowrap></td>';
            $entete     .= '</tr></table>'; 
        }
		// bass
			if ( $this->bln_lie_systeme and ($_GET['val'] <> 'atlas')) {
				$entete     .= '<br />
					<table  class="center-table" ><caption>'. $this->recherche_libelle_page('TitImpNom',$_SESSION['langue'],'nomenclature') . '</caption>
						<tr>
							<td>&nbsp;
								'. $this->recherche_libelle_page('Du_Sect',$_SESSION['langue'],'nomenclature') .'  
							</td>
							
							<td>&nbsp;
								<select name="import_from_systeme">';
								//if( isset($_POST['import_from_systeme']) and trim($_POST['import_from_systeme'] <> '') ){
									//$selected_import_from_systeme = $_POST['import_from_systeme'];
								//} 
								$entete .= '<option value=""></option>';     
								foreach ($this->list_secteurs as $secteur) {
									$entete .= '<option value='.'\''.$secteur[0].'\'';
									
									if ($secteur[0]==$selected_import_from_systeme) {
										$entete .=   ' selected ';   
									}
									$entete .= '>'.$secteur[1].'</option>';            
								}
			$entete     .='		</select>
							</td>
			<td style="text-align: center; vertical-align: middle;">';
			$entete     .='<INPUT name="do_import_nomenc"  type="submit" style="height: 20px" value="';
			$entete     .= $this->recherche_libelle_page('import',$_SESSION['langue'],'nomenclature') ;
			$entete     .='">&nbsp;';
			$entete     .='</td>
						</tr>
					</table>'; 
		}	
		// fin bass
		
		$entete     .= '</div></span>';       
        $this->entete_template = $entete;
        $this->fin_template="<br /></div><div><INPUT TYPE='SUBMIT' VALUE='".$this->recherche_libelle_page('Valider',$_SESSION['langue'],'nomenclature')."'></div></span></Form>";
        //$this->fin_template='</Form>';        
     
    }
    
	
		/**
		* METHODE : do_import_nomenc($from_syst)
		* <pre>
		*  Effectue l'importation des occurrences d'une table d'un systeme à un autre
		* </pre>
		* @access public
		* 
		*/
		public function do_import_nomenc($from_syst){
	
		/* $sql =	'INSERT INTO '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].
			' ('.$this->champ_id.','.$this->champ_systeme.')'.
			' SELECT '.$this->champ_id.', '.$this->code_systeme.' FROM '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].
			' WHERE '.$this->champ_systeme.' ='.$from_syst;
		//die($sql);
		*/
		$sql =	' SELECT '.$this->champ_id.' AS CODE_NOMENC FROM '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].
				' WHERE '.$this->champ_systeme.' ='.$from_syst;
		$all_rows = $this->conn->GetAll($sql);
		if( is_array($all_rows) ) {
			foreach($all_rows as $i => $row){
				$sql_test = ' SELECT * FROM '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].
				' WHERE '.$this->champ_id.' ='.$row['CODE_NOMENC'].' AND '.$this->champ_systeme.' ='.$this->code_systeme;
				$res_test = $this->conn->Execute($sql_test);
				if($res_test->EOF){
					$sql =	'INSERT INTO '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].
							' ('.$this->champ_id.','.$this->champ_systeme.')'.
							' VALUES ('.$row['CODE_NOMENC'].','.$this->code_systeme.')';
					if( ($rs = $this->conn->Execute($sql) )===false){}  		
				}
			}		
		}		
	}
    
		/**
		* METHODE : init_liste_secteur()
		* <pre>
		*  mise en liste des secteurs 
		* </pre>
		* @access public
		* 
		*/
		public function init_liste_secteur() {
			if(!(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0)){
				$sql  = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'<>255';
			}else{
				$sql  = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].
						' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['fixe_secteurs']).')';
			}
			// Gestion des erreurs lors de l'exécution de la requête SQL
			
			try {            
				if( ($rs = $this->conn->Execute($sql) )===false){                
					 throw new Exception('ERR_SQL');   
				}                 
				
				if (!$rs->EOF){
					$rs->MoveFirst();
					while (!$rs->EOF){
						$secteur = array();
											// utiliser la fonction de recuperation des 30 ou 31 caracteres de la chaine
						$secteur [] = $rs->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
						$secteur [] = $rs->fields[get_champ_extraction($GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])];
						/*
						if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql'){
							$secteur [] = $rs->fields[$GLOBALS['PARAM']['LIBELLE_TYPE_SYSTEME_ENSEIGNEM']];
						}else{
							$secteur [] = $rs->fields[$GLOBALS['PARAM']['LIBELLE_TYPE_SYSTEME_ENSEIGNEME']];
						}*/
						
						$this->list_secteurs[] = $secteur;
						$rs->MoveNext();
		
					}
				} 
			}
			catch (Exception $e) {
				 $erreur = new erreur_manager($e,$sql);
			}        
		}
        
    
		/**
		* METHODE : comparer ($matr1,$matr2)
		* <pre>
		*  Permet d'effectuer la comparaison entre les données en base 
		*  et les données POST. Le résultat est dans matrice_donnees_bdd
		*  et sera directement utlisé pour la MAJ de la base de données
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
		
		//return $result;die('ici');
		$this->matrice_donnees_bdd	=	$result;        
         
	}	
    
    	 public function get_ord_max(){
			$max_return = 0;
			if (isset($this->champ_assoc) && $this->champ_assoc<>''){
				$sql = ' SELECT  MAX('.$this->champ_ordre.') as MAX_INSERT FROM  '.$this->nom_table.
					   ' WHERE '.$this->champ_id.'<>'.$this->valeur_filtre ;
					   //' WHERE '.$this->champ_id.'<>'.$this->valeur_filtre.' AND '.$this->nom_champ_assoc.'='. $this->champ_assoc ;
			}else{
				$sql = ' SELECT  MAX('.$this->champ_ordre.') as MAX_INSERT FROM  '.$this->nom_table.
					   ' WHERE '.$this->champ_id.'<>'.$this->valeur_filtre ;
			}     
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
		* METHODE : maj_bdd($matr)
		* <pre>
		* Utilise $matrice_donnees_bdd pour insérer, supprimer, 
		* mettre à jour des données de la base 
		* </pre>
		* @access public
		* 
		*/
		public function maj_bdd($matr){  
        
        if (is_array($matr)){                    
        	$matr_ins = array();
			foreach ($matr as $tab){
				$action = $tab[sizeof($tab)-1];
				if(($action == 'I')){
					$matr_ins[] = $tab;
				}
			}
			foreach ($matr as $tab){
                $action = $tab[sizeof($tab)-1];
                //echo 'valeur action = ' .$action;
				if(count($tab)==5 && $tab[3]=="''") $tab[3] = $this->get_ord_max()+1;
				elseif(count($tab)==4 && $tab[2]=="''") $tab[2] = $this->get_ord_max()+1;  
                switch ($action){
                    case 'I':
                        if ($this->bln_lie_systeme==true) {
                            // vérification de l'existence de l'enregistrement avant insertion
                           	if($this->champ_assoc =='' || $this->nom_champ_assoc=='')
						    	$sql = ' SELECT * FROM '.$this->nom_table.' WHERE '.$this->champ_id.'='.$tab[0];
							else
								$sql = ' SELECT * FROM '.$this->nom_table.' WHERE '.$this->champ_id.'='.$tab[0].' AND '.$this->nom_champ_assoc.'='.$tab[1];
                            //echo $sql;
                            try{
                                if (($rs = $this->conn->Execute($sql))===false)                            
                                    throw new Exception('ERR_SQL');
                                if ($rs->EOF) {                                
                                    // l'élement n'existe pas dans ce cas de figure il faut l'ajouter en meme temps
                                    // dans la table de nomenclature et celle de systeme                                
                                    	if($this->champ_assoc =='' || $this->nom_champ_assoc=='')
											$sql    =   'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->champ_lib.','.$this->champ_ordre.')'.
														' VALUES ('.$tab[0].','.$this->conn->qstr($tab[1]).','.$tab[2].')';  
										else
											$sql    =   'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->nom_champ_assoc.','.$this->champ_lib.','.$this->champ_ordre.')'.
														' VALUES ('.$tab[0].','.$tab[1].','.$this->conn->qstr($tab[2]).','.$tab[3].')';  
                                       
                                       // echo $sql;                        
                                    try{
                                        
										if ($this->conn->Execute($sql) <> false) {
																				
                                            // insertion de la données dans la table systeme associé
                                            $sql    =   'INSERT INTO '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].'('.$this->champ_id.','.$this->champ_systeme.')'.
                                                    ' VALUES ('.$tab[0].','.$this->code_systeme.')';                                            
                                            
                                            try{
                                                if ($this->conn->Execute($sql)===false)
                                                    throw new Exception('ERR_SQL');
                                            }
                                            catch (Exception $e){
                                                $erreur = new erreur_manager($e,$sql);
                                            }
                                            
                                            if ($this->insertion_dico==true || $this->nom_table==$GLOBALS['PARAM']['TYPE_REGROUPEMENT']){  
                                                    
                                                // Insertion de l'élément dans la table traduction par rapport à la langue courante
                                                if($this->champ_assoc =='')
													$sql = ' INSERT INTO DICO_TRADUCTION (CODE_NOMENCLATURE,NOM_TABLE,CODE_LANGUE,LIBELLE)'.
															   ' VALUES('.$tab[0].','.'\''.$this->nom_table.'\''.','.'\''.$this->defaut_langue.'\''.','.$this->conn->qstr($tab[1]).')';                                    
                                                else
													$sql = ' INSERT INTO DICO_TRADUCTION (CODE_NOMENCLATURE,NOM_TABLE,CODE_LANGUE,LIBELLE)'.
															   ' VALUES('.$tab[0].','.'\''.$this->nom_table.'\''.','.'\''.$this->defaut_langue.'\''.','.$this->conn->qstr($tab[2]).')';                                    
                                                
												try{
                                                    if ($this->conn->Execute($sql)==false)
                                                        throw new Exception('ERR_SQL'); 
													if($this->champ_assoc =='')
														insert_traduction('DICO_TRADUCTION',$tab[0], $this->nom_table, $this->defaut_langue, $tab[1], 1);
													else
														insert_traduction('DICO_TRADUCTION',$tab[0], $this->nom_table, $this->defaut_langue, $tab[2], 1);	
                                                }
                                                catch (Exception $e) {
                                                    $erreur = new erreur_manager($e,$sql);
                                                }
                                            }
                                            
                                        }else{
																						//echo '<br>HERE<br>';
                                            throw new Exception('ERR_SQL');
                                        }
                                    }
                                    catch (Exception $e) {
                                        $erreur = new erreur_manager($e,$sql);
                                    }
                                
                                }else{
                                // insertion de la données dans la table systeme associé
                                    $sql    =   'INSERT INTO '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].' ('.$this->champ_id.','.$this->champ_systeme.')'.
                                            ' VALUES ('.$tab[0].','.$this->code_systeme.')';
                                    
                                    try{
                                        if ($this->conn->execute($sql)==false) 
                                            throw new Exception('ERR_SQL');
                                    }
                                    catch (Exception $e) {
                                         $erreur = new erreur_manager($e,$sql);
                                    }
                                }
                            }
                            catch (Exception $e){
                                $erreur = new erreur_manager($e,$sql);                                
                            }                          
                            
                            
                        }else {
                               
                                try {
                                    if($this->champ_assoc =='' || $this->nom_champ_assoc=='')
										$sql    =   'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->champ_lib.','.$this->champ_ordre.')'.
													' VALUES ('.$tab[0].','.$this->conn->qstr($tab[1]).','.$tab[2].')';
                                    else
										$sql    =   'INSERT INTO '.$this->nom_table.' ('.$this->champ_id.','.$this->nom_champ_assoc.','.$this->champ_lib.','.$this->champ_ordre.')'.
													' VALUES ('.$tab[0].','.$tab[1].','.$this->conn->qstr($tab[2]).','.$tab[3].')';
                                                     
                                   if ($this->conn->execute($sql)==true) {
                                        if ($this->insertion_dico==true || $this->nom_table==$GLOBALS['PARAM']['TYPE_REGROUPEMENT']){
                                            // Insertion de l'élément dans la table traduction par rapport à la langue courante
                                            if($this->champ_assoc =='')
												$sql = ' INSERT INTO DICO_TRADUCTION (CODE_NOMENCLATURE,NOM_TABLE,CODE_LANGUE,LIBELLE)'.
														   ' VALUES('.$tab[0].','.'\''.$this->nom_table.'\''.','.'\''.$this->defaut_langue.'\''.','.$this->conn->qstr($tab[1]).')';
                                            else
												$sql = ' INSERT INTO DICO_TRADUCTION (CODE_NOMENCLATURE,NOM_TABLE,CODE_LANGUE,LIBELLE)'.
														   ' VALUES('.$tab[0].','.'\''.$this->nom_table.'\''.','.'\''.$this->defaut_langue.'\''.','.$this->conn->qstr($tab[2]).')';
                                            
											try{
                                                if ($this->conn->execute($sql)==false)
                                                    throw new Exception('ERR_SQL');
													if($this->champ_assoc =='')
														insert_traduction('DICO_TRADUCTION', $tab[0], $this->nom_table, $this->defaut_langue, $tab[1], 1);
													else
														insert_traduction('DICO_TRADUCTION', $tab[0], $this->nom_table, $this->defaut_langue, $tab[2], 1);
                                            }
                                            catch(Exception $e){
                                                $erreur = new erreur_manager($e,$sql); 
                                            }
                                        }
                                    
                                   }else{
                                        throw new Exception('ERR_SQL');                                    
                                   }
                               }
                               catch (Exception $e){
                                    $erreur = new erreur_manager($e,$sql); 
                               }
                                            
                        }
                        //echo $sql ;
                        break;
                    case 'U':
                        try{
                            // Mise à jour des données sur les nomenclatures et dans la traduction
                            if($this->champ_assoc =='' || $this->nom_champ_assoc=='')
								$sql = 'UPDATE '.$this->nom_table.' SET '.$this->champ_lib.'='.$this->conn->qstr($tab[1]).','.$this->champ_ordre.'='.$tab[2].
									   ' WHERE '.$this->champ_id.'='.$tab[0] ;                        
							else
								$sql = 'UPDATE '.$this->nom_table.' SET '.$this->nom_champ_assoc.'='.$tab[1].','.$this->champ_lib.'='.$this->conn->qstr($tab[2]).','.$this->champ_ordre.'='.$tab[3].
									   ' WHERE '.$this->champ_id.'='.$tab[0] ;                        
									
                            try{     
                                if ($this->conn->execute($sql)===false) {
									throw new Exception('ERR_SQL');
								}
                            }
                            catch(Exception $e){
                                $erreur = new erreur_manager($e,$sql); 
                            }                            
                             
                           if ($this->insertion_dico==true || $this->nom_table==$GLOBALS['PARAM']['TYPE_REGROUPEMENT']){
                                if($this->champ_assoc =='')
									$sql = 'UPDATE DICO_TRADUCTION SET LIBELLE='.$this->conn->qstr($tab[1]).' WHERE CODE_NOMENCLATURE='.$tab[0].
										   ' AND NOM_TABLE='.'\''.$this->nom_table.'\''. 
										   ' AND CODE_LANGUE='.'\''.$this->defaut_langue.'\'';
								else
									$sql = 'UPDATE DICO_TRADUCTION SET LIBELLE='.$this->conn->qstr($tab[2]).' WHERE CODE_NOMENCLATURE='.$tab[0].
										   ' AND NOM_TABLE='.'\''.$this->nom_table.'\''. 
										   ' AND CODE_LANGUE='.'\''.$this->defaut_langue.'\'';
										   
                                try{       
                                    if ($this->conn->execute($sql)===false) 
                                        throw new Exception('ERR_SQL');
                                }
                                catch (Exception $e) {
                                    $erreur = new erreur_manager($e,$sql); 
                                }                                
                            }
                        }
                        catch (Exception $e){
                            $erreur = new erreur_manager($e,$sql); 
                        }
                        break;
                    case 'D' :    
                        
                        // Avant toute suppression il faut vérifier que l'enregistrement n'est pas associé à un autre système d'enseignement                   
                        
                        if ($this->bln_lie_systeme==true) {
                                try{
                                    $sql = 'SELECT * FROM '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].' WHERE '.$this->champ_id.'='.$tab[0].
                                           ' AND '.$this->champ_systeme.'<>'.$this->code_systeme;
                                    
                                    if (($rs = $this->conn->execute($sql))===false)
                                        throw new Exception('ERR_SQL');
                                        
                                    if ($rs->EOF){
                                        $sql = 'DELETE FROM '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].' WHERE '.$this->champ_id.'='.$tab[0].
												' AND '.$this->champ_systeme.'='.$this->code_systeme;
                                        
                                        try{
                                            if ($this->conn->execute($sql)==true){
                                                $sql = 'DELETE FROM '.$this->nom_table.' WHERE '.$this->champ_id.'='.$tab[0];
                                                
                                                try{
                                                    if ($this->conn->execute($sql)==false) 
                                                        throw new Exception('ERR_SQL');
                                                }
                                                catch (Exception $e){
                                                    $erreur = new erreur_manager($e,$sql);                                                     
                                                }
                                                // supression de l'élément dans la table de traduction
                                                if ($this->insertion_dico==true || $this->nom_table==$GLOBALS['PARAM']['TYPE_REGROUPEMENT']){
                                                    $sql = 'DELETE FROM DICO_TRADUCTION WHERE CODE_NOMENCLATURE='.$tab[0].
                                                            ' AND NOM_TABLE='.'\''.$this->nom_table.'\'';
                                                    try{
                                                        if ($this->conn->execute($sql)==false)
                                                            throw new Exception('ERR_SQL');
                                                    }
                                                    catch (Exception $e){
                                                        $erreur = new erreur_manager($e,$sql); 
                                                    }
                                                }                                        
                                                
                                            }else{
                                                throw new Exception ('ERR_SQL');
                                            }                                            
                                        }
                                        catch (Exception $e){
                                            $erreur = new erreur_manager($e,$sql); 
                                        }
                                    }else {
                                        $sql = 'DELETE FROM '.$this->nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].' WHERE '.$this->champ_id.'='.$tab[0].
                                               ' AND '.$this->champ_systeme.'='.$this->code_systeme ;
                                        try {
                                            if ($this->conn->execute($sql)==false)
                                                throw new Exception('ERR_SQL');
                                        }
                                        catch (Exception $e){
                                            $erreur = new erreur_manager($e,$sql); 
                                        }
                                    }
                                }
                                catch (Exception $e){
                                    $erreur = new erreur_manager($e,$sql); 
                                }
                        }else{
                            $sql = 'DELETE FROM '.$this->nom_table.' WHERE '.$this->champ_id.'='.$tab[0];                            
                            try{
                                if ($this->conn->Execute($sql)==false)
                                    throw new Exception('ERR_SQL');
                                // supression de l'élément dans la table de traduction
                                if ($this->insertion_dico==true || $this->nom_table==$GLOBALS['PARAM']['TYPE_REGROUPEMENT']){
                                    $sql = 'DELETE FROM DICO_TRADUCTION WHERE CODE_NOMENCLATURE='.$tab[0].
                                            ' AND NOM_TABLE='.'\''.$this->nom_table.'\'';
                                    try{
                                        if ($this->conn->execute($sql)==false)
                                            throw new Exception('ERR_SQL');
                                    }
                                    catch (Exception $e){
                                        $erreur = new erreur_manager($e,$sql); 
                                    }
                                }                                         
                            }
                            catch (Exception $e){
                                $erreur = new erreur_manager($e,$sql); 
                            }
                            
                            
                        } 
                      
					   	// Modification/Suppression des donnees dans la table fille sil yen a
						if($this->champ_assoc =='') $pos_lib = 1;
						 else $pos_lib = 2;
						 $tab_ins = array();
						 foreach ($matr_ins as $tab_tmp){
							if(trim($tab[$pos_lib]) == trim($tab_tmp[$pos_lib])){
								$tab_ins = $tab_tmp;
								break;
							}
						 }	
						 foreach (array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES')) as $nom_tab_base){ 
							if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/',strtoupper($nom_tab_base)) && strtoupper($nom_tab_base)<>strtoupper($this->nom_table)){               
								$ColTab	= $this->conn->MetaColumnNames($nom_tab_base);  
								$cles=array_keys($ColTab);
								if(count($cles)==4) {				
									$nom_champ_assoc = $cles[1];            
									if($nom_champ_assoc == $this->champ_id){
										
										$sql = 'SELECT '.$cles[0].' FROM '.$nom_tab_base.' WHERE '.$nom_champ_assoc.'='.$tab[0];
                                    	$rs_a_suppr = $this->conn->GetAll($sql);
										
										///On modifie ds la table fille lorsque seul le code change et que le libelle reste le meme
										if(count($tab_ins)>0){
											$sql = 'UPDATE '.$nom_tab_base.' SET '.$nom_champ_assoc.'='.$tab_ins[0].' WHERE '.$nom_champ_assoc.'='.$tab[0];                            
											try{
												if ($this->conn->Execute($sql)==false)
													throw new Exception('ERR_SQL');
											}
											catch (Exception $e){
												$erreur = new erreur_manager($e,$sql); 
											}
											//echo "$nom_tab_base<br>".$sql;
										}else{ 
										///On supprime ds la table fille lorsque le code et le libelle changent
											$sql = 'DELETE FROM '.$nom_tab_base.' WHERE '.$nom_champ_assoc.'='.$tab[0];                            
											try{
												if ($this->conn->Execute($sql)==false)
													throw new Exception('ERR_SQL');
												// supression de l'élément dans la table de traduction
												if ($this->insertion_dico==true){
													if(is_array($rs_a_suppr) && count($rs_a_suppr)>0){
														foreach($rs_a_suppr as $rs){
															$sql = 'DELETE FROM DICO_TRADUCTION WHERE CODE_NOMENCLATURE='.$rs[$cles[0]].
																	' AND NOM_TABLE='.'\''.$nom_tab_base.'\'';
															try{
																if ($this->conn->execute($sql)==false)
																	throw new Exception('ERR_SQL');
															}
															catch (Exception $e){
																$erreur = new erreur_manager($e,$sql); 
															}
														}
													}
												}
											}
											catch (Exception $e){
												$erreur = new erreur_manager($e,$sql); 
											}
										}
									}
								}
							}
						}
						// Fin Suppression des donnees dans la table fille sil yen a
						
                        // Ajout d'un traitement spécifique pour la suppression des droits attribués aux groupes                        
                        if ($this->nom_table =='ADMIN_GROUPES'){
                            $sql = 'DELETE FROM ADMIN_DROITS WHERE CODE_GROUPE='.$tab[0];                           
                            try {
                                if ($GLOBALS['conn_dico']->execute($sql)==false)
                                    throw new Exception('ERR_SQL');                                
                            }
                            catch (Exception $e){
                                $erreur = new erreur_manager($e,$sql);                                 
                            }
                        }                        
    
                        break;
					
            		}
            }
						//$this->manage_maj_val_ind();
         }   
    }
}
?>
