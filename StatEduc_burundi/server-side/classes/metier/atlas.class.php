<?php /** 
 * Classe Atlas
 * <pre>
 * Classe permettant la
 * -> gestion de la definition de l'Atlas d'un pays spécifique à partir du modèle générique de l'UIS
 *    Dans cette modélisation toutes les occurrences des divisions administratives sont stockées 
 * -> dans une seule table. la relation père fils est matérialisée par une rélation réflexive.
 * </pre>
 * @access public
 * @author Alassane
 * @version 1.4
*/
class atlas {

    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $code_systeme;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var adodb.connection
		* @access public
		*/   
		public $conn;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $id_chaine;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $id_type_regroupement;   
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $id_regroupement;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $id_type_fils;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var array()
		* @access public
		*/   
		public $donnees_bdd = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var array()
		* @access public
		*/   
		public $donnees_post = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var array()
		* @access public
		*/   
		public $matrice_donnees_template = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var array()
		* @access public
		*/   
		public $matrice_donnees_bdd= array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var array()
		* @access public
		*/   
		public $matrice_donnees = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $template ;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $entete_template;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $fin_template;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $nb_lignes = 8;   
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var array()
		* @access public
		*/   
		public $VARS_GLOBALS = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $chaine;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $champ_id;		// 			= ;//$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $champ_code_admin;	//			=	;//''.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'';
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $champ_lib;	//			=	;//''.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'';
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $champ_type;	//		=	;//''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''; 
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $champ_ordre;	//		=	;//''.$GLOBALS['PARAM']['ORDRE_TYPE_REGROUPEMENT'].''; 
		
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $champ_pere;	// 		=;//	''.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].'';
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $valeur_filtre = 4294967295;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $id_name;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var numeric
		* @access public
		*/   
		public $code_admin;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $lib_name;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $lib_ordre;
		
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $lib_ord;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var string
		* @access public
		*/   
		public $lib_entete;
		
		
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var array()
		* @access public
		*/   
		public $vars_modif_atlas 	= array();
		
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var boolean
		* @access public
		*/   
		public $modif_atlas = false;
        
        /**
        * Constructeur de la classe : 
             * Initialisation des paramètres du constrcuteur est fait en renseignant les informations suivantes :
             * le système  d'enseignement ou le sous secteur éducation, l'identificateur de la chaine de la regroupement
             * l'identificateur du type de regroupement par défaut, l'identificateur du prémier regroupement à par défaut
             * et le pointeur sur la connexion courante à la base de données             
        * @access public
        * @param numeric $id_systeme contient l'identificateur du sous secteur courant
        * @param numeric $id_chaine contient l'identificateur de la chaine de regroupement courante
        * @param numeric $id_type_regroupement contient l'identificateur du type de regroupement courant
        * @param numeric $id_regroupement contient l'identificateur du regroupement par défaut
        * @param numeric $conn contient l'identificateur du sous secteur courant        
        */
		public function __construct($id_systeme, $id_chaine, $id_type_regroupement,$id_regroupement,$conn){
            $this->code_systeme         =   $id_systeme;
            $this->id_chaine            =   $id_chaine;  
            $this->id_type_regroupement =   $id_type_regroupement;            
            $this->id_regroupement      =   $id_regroupement;
            //echo '<br>/////code reg='.$id_regroupement;
            $this->conn                 =   $conn; 
            //$this->arbre                =   $GLOBALS['arbre'];     
            $this->chaine               =   $this->build_chaine($this->id_chaine);            
            $this->template             =   file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'atlas.html'); 
						         
			$this->champ_id 			=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
			$this->champ_code_admin			=$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
			$this->champ_lib			=$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
			$this->champ_ordre			=$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']; 
			$this->champ_type			=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']; 
			$this->champ_pere 		=	$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT']; 

    }
        
		/**
         * Cette fonction __wakeup() permet de réveiller l'objet placé en session
         * @access public
        */
		public function __wakeup(){
        $this->conn     =   $GLOBALS['conn'];
        $this->arbre    =   $GLOBALS['arbre'];
    }
        
		/**
        *  Cette fonction get_donnees () permet la récupération des données de la base de données : 
             * les données proviennent des tables : REGROUPEMENT, LIAISONS, HIERARCHIE, qui stockent la
             * répresentation des structures administratives dans une logique reflexive   
             * le résultat de l'interrogation de la base de données est stocké dans un tableau 
             * @access public             
        */
		public function get_donnees(){
        //echo '<br>id_reg'.$this->id_regroupement.'<br>';
        if (isset($this->id_regroupement) && $this->id_regroupement<>''){
            if(!in_array($this->id_type_regroupement,$_SESSION['fixe_type_regs'])){
				$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', A.'.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', 
														A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].',
														A.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].',
														A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
								' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS A, '.$GLOBALS['PARAM']['LIAISONS'].' AS B, 
													'.$GLOBALS['PARAM']['HIERARCHIE'].' AS C 
								WHERE A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = B.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
													AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
													C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
									' AND B.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].' ='.$this->id_regroupement.
									' AND C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' ='.$this->id_chaine.
									' ORDER BY A.'.$GLOBALS['CHAMP_ORDRE'];
			}else{
				$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', A.'.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', 
															A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].',
															A.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].',
															A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
							  ' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS A, '.$GLOBALS['PARAM']['LIAISONS'].' AS B, 
														'.$GLOBALS['PARAM']['HIERARCHIE'].' AS C 
														WHERE A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = B.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
														AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
														C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
							  ' AND B.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].' ='.$this->id_regroupement.
							  ' AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$this->id_type_regroupement].')'.
							  ' AND C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' ='.$this->id_chaine.
							  ' ORDER BY A.'.$GLOBALS['CHAMP_ORDRE'];
			}
        }else {
            if(!in_array($this->id_type_regroupement,$_SESSION['fixe_type_regs'])){
				$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', 
						'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
						FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$this->id_type_regroupement.
					' ORDER BY '.$GLOBALS['CHAMP_ORDRE'];
			}else{
				$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
							FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
							WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$this->id_type_regroupement.
							' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$this->id_type_regroupement].')'.
							' ORDER BY '.$GLOBALS['CHAMP_ORDRE'];
			}           
        }
        $tab_donnees    =   array(); 
        // Gestion des erreurs lors de l'exécution de la requête sql
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
        catch (Exception $e){
            $erreur=new erreur_manager($e,$requete);
        }
                 
        $this->VARS_GLOBALS['donnees_bdd']   =   $tab_donnees;        
        $this->limiter_affichage();
        $this->init_liste_table();
        
       // echo "<pre>"; print_r($tab_donnees);
    }
    
    /**
        * Cette fonction transformer_donnees() permet une transformation des données de la base de données 
        * afin de permet une présentation conforme au principe du template asssocié
        * le résultat du traitement est stocké dans une variable de la classse
        * $this->matrice_donnees_template        
        * @access public            
        */
        
    public function transformer_donnees(){
       $this->matrice_donnees_template = array();
       foreach ($this->matrice_donnees as $ligne_donnes) {
            $donnees_transformees = array();
            $donnees_transformees[0] = $ligne_donnes[$this->champ_id ];
            $donnees_transformees[1] = $ligne_donnes[$this->champ_code_admin ];
            $donnees_transformees[2] = $ligne_donnes[$this->champ_lib ];
			$donnees_transformees[3] = $ligne_donnes[$this->champ_ordre ];
            $donnees_transformees[4] = $ligne_donnes[$this->champ_type];
            $this->matrice_donnees_template[] = $donnees_transformees; 
            
        }
    }
        
		/**
        * Cette fonction limiter_affichage() permet de sélection un flux de données afficher. De ce fait elle 
        * est permet de limiter le nombre d'enregistrements à afficher à l'écran.              
        * @access public            
        */
        
		public function limiter_affichage(){
        if(!isset($GLOBALS['nbre_total_enr']))
                    $GLOBALS['nbre_total_enr'] = count( $this->VARS_GLOBALS['donnees_bdd'] );                    
                    
                    $mat = array_slice( $this->VARS_GLOBALS['donnees_bdd'], $GLOBALS['debut'], $GLOBALS['cfg_nbres_ppage']);               
                
                // initialisation du type de données des fils immédiat
                if (isset($this->id_regroupement) && $this->id_regroupement<>''){  
                    //echo '<pre>';
                   // print_r($mat);
                    
                    if (is_array($mat) && count($mat)>0) {
                        
                        $this->id_type_fils =  $mat[0][$this->champ_type] ;
                        //echo 'passé a ' .'elt= '.$this->id_type_regroupement. ' fils = '. $this->id_type_fils;
                    } else{ 

                        $this->id_type_fils = $this->id_type_regroupement;                        
                    }                   
                   
                }
                
                foreach( $mat as $ligne_donnees){
                    $this->matrice_donnees[] = $ligne_donnees;
                }
                $GLOBALS['nbenr'] = count($this->matrice_donnees);
                
                // transfromation des donnees
                $this->transformer_donnees();
    }    
   
		/**
        * Cette fonction remplir_template() permet de remplir le template à partir des données provenant de la  
        * base de données et ayant subies les transformations nécessaires pour une présentation conforme au principe 
        * du template asssocié. Le résultat est évalué et afficher à l'écran               
        * @access public 
        * @param String $template contient le flux de caractères répresentant le template
        */
		public function remplir_template($template){
		if (is_array($this->matrice_donnees)){  
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                // cas des champs id                   
                if (isset($this->matrice_donnees[$ligne][$this->champ_id ])){
                  //  echo 'passé la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_id ];
                    //$val_champ_base = addslashes($val_champ_base);                    
										if($this->modif_atlas == true){
												${'CODE_'.$ligne} = '<INPUT type="text" name="CODE_'.$ligne.'" value="'.$val_champ_base.'" size="6" Class ="Defaut_Ligne"  readonly>'; 
												${'COMBO_LINK_TO_'.$ligne}	=	'<select name="LINK_TO_'.$ligne.'">'.$this->vars_modif_atlas['options_combo'].'</select>';
										}else{
												${'CODE_'.$ligne} = $val_champ_base;
										}
                                     
                }else{
                    
										if($this->modif_atlas == true){
												${'CODE_'.$ligne} = ''; 
												${'COMBO_LINK_TO_'.$ligne}	=	'';
										}else{
												${'CODE_'.$ligne} = '';
										}
                }
                
                // cas des champs code admin
                if (isset($this->matrice_donnees[$ligne][$this->champ_code_admin])){ 
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_code_admin];                    
                    //$val_champ_base = addslashes($val_champ_base);                    
									 	if($this->modif_atlas == true){
												${'CADMIN_'.$ligne} = '<INPUT type="text" name="CADMIN_'.$ligne.'" value="'.$val_champ_base.'" size="33" Class ="Defaut_Ligne"  readonly>'; 
										}else{
												${'CADMIN_'.$ligne} = $val_champ_base;
												if(in_array($this->id_type_regroupement,$_SESSION['fixe_type_regs'])){
													$readonly = ' readonly';
													$disabled = ' disabled';
												}else{
													$readonly = ' ';
													$disabled = ' ';
												}
										}
                }else{
									 	if($this->modif_atlas == true){
												${'CADMIN_'.$ligne} = ''; 
										}else{
												${'CADMIN_'.$ligne} = '';
										}
                }
                
                // cas des champs libellé
                if (isset($this->matrice_donnees[$ligne][$this->champ_lib])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_lib];                    
                    //$val_champ_base = addslashes($val_champ_base);                    
									 	if($this->modif_atlas == true){
												${'LIBELLE_'.$ligne} = '<INPUT type="text" name="LIBELLE_'.$ligne.'" value="'.$val_champ_base.'" size="33" Class ="Defaut_Ligne"  readonly>'; 
										}else{
												${'LIBELLE_'.$ligne} = $val_champ_base;
												if(in_array($this->id_type_regroupement,$_SESSION['fixe_type_regs'])){
													$readonly = ' readonly';
													$disabled = ' disabled';
												}else{
													$readonly = ' ';
													$disabled = ' ';
												}
										}
                }else{
									 	if($this->modif_atlas == true){
												${'LIBELLE_'.$ligne} = ''; 
										}else{
												${'LIBELLE_'.$ligne} = '';
										}
                }
                
				// cas des champs ordre
                if (isset($this->matrice_donnees[$ligne][$this->champ_ordre])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_ordre];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'ORD_'.$ligne} = ''.$val_champ_base.'';                    
                }else{
                    ${'ORD_'.$ligne} = '';	
                }
				
                // cas des champs type
                if (isset($this->matrice_donnees[$ligne][$this->champ_type])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_type];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'ORDRE_'.$ligne} = ''.$val_champ_base.'';                    
                }else{
                    ${'ORDRE_'.$ligne} = '';	
                }
                
            }
            
            // entete du template
            ${'id_name'}				= $this->id_name;
            ${'code_admin'}				= $this->code_admin;
            ${'lib_name'}				=	$this->lib_name;
			${'lib_ord'}			=	$this->lib_ord;
            ${'lib_ordre'}			=	$this->lib_ordre;
            ${'lib_entete'}			=	$this->lib_entete;
        }       

        eval('$result = sprintf(\'%s\', "'.str_replace('"','\"',$template).'");');

        return $result;
    }

     /**
        * Cette fonction recherche_libelle_page() permet de rechercher les items des traduction de la page qui
        * nécessite de la traduction en fonction d'une langue spécifique correspondant à un identificateur spécifique              
        * @access public
        * @param String $code contient l'identificateur de l'item de la traduction à  rechercher
        * @param String $langue contient l'identificateur de la langue courant
        * @param String $table contient le nom de la table concerné par la recherche.
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
        * Cette fonction recherche_libelle() permet de rechercher les items des traduction qui
        * nécessite de la traduction en fonction d'une langue spécifique correspondant à un identificateur spécifique              
        * @access public
        * @param String $code contient l'identificateur de l'item de la traduction à  rechercher
        * @param String $langue contient l'identificateur de la langue courant
        * @param String $table contient le nom de la table concerné par la recherche.
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
            * Cette fonction get_post_template() permet de récupérer les données du template après la soumission de
            * l'utilisateur
            * @access public
            * @param array() $matr contient le tableau de données provenant de la soumission           
        */
		public function get_post_template($matr){      
              
        if (is_array($matr)){              
            
            $max_cle_incr = $this->get_cle_max();   
            
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                $champ_id       = 'CODE_'.$ligne;
                $champ_code_admin  = 'CADMIN_'.$ligne;
                $champ_lib      = 'LIBELLE_'.$ligne;
                $champ_ordre   	= 'ORD_'.$ligne;
				$champ_type    	= 'ORDRE_'.$ligne;
                $delete         = 'DELETE_'.$ligne;               
                
                if (isset($matr[$champ_id]) && $matr[$champ_id]<>''){                   
                    if (!isset($matr[$delete])) {
                        $donnees_ligne  =   array();
                        $donnees_ligne [] = $matr[$champ_id];
                        if (isset($matr[$champ_code_admin]) && $matr[$champ_code_admin]<>'' ){
                            $donnees_ligne [] = $matr[$champ_code_admin];
                        }else{
                            $donnees_ligne []='\'\'';
                        }
						
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
                        
                        if (isset($matr[$champ_type]) && $matr[$champ_type]<>'' ){
                            $donnees_ligne [] = $matr[$champ_type];
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
                        $donnees_ligne [] = $matr[$champ_code_admin];
                        $donnees_ligne [] = $matr[$champ_lib];
                        //$donnees_ligne [] = $matr[$champ_type]; 
						if (isset($matr[$champ_ordre]) && $matr[$champ_ordre]<>'' ){
                            $donnees_ligne [] = $matr[$champ_ordre];
                        }else{
                            $donnees_ligne []='\'\'';
                        }  
                        if (isset($matr[$champ_type]) && $matr[$champ_type]<>'' ){
                            $donnees_ligne [] = $matr[$champ_type];
                        }else{
                            $donnees_ligne []='\'\'';
                        }                        
                        $this->donnees_post[]= $donnees_ligne;                        
                        
                    }
                    
                }
                
            }
        }
        //echo '<pre>';
        //print_r ($this->donnees_post);
    }
    
		
        /**
        * Cette fonction maj_bdd_modif_atlas() permet de mettre à jour les données de l'atlas
        * @access public
        * @param array() $matr contient le tableau de données provenant de la soumission           
        */
		public function maj_bdd_modif_atlas($matr){
				if(is_array($this->donnees_post)){
						foreach($this->donnees_post as $ligne => $data_post){
								if( $matr['LINK_TO_'.$ligne] <> $this->vars_modif_atlas['link_from'] ){ // il y'a une modificationde liaison 
										$sql = ' UPDATE ' . $GLOBALS['PARAM']['LIAISONS'] .
													 ' SET ' . $GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'] . ' = ' . $matr['LINK_TO_'.$ligne] .
													 ' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'   = ' . $data_post[0] .
													 ' AND ' . $GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'] . ' = ' . $this->vars_modif_atlas['link_from'];                        
										//echo "<br> $sql <br>";
										// Gestion des erreurs lors de l'exécution de la requete sql
										
										try{
												if ($this->conn->Execute($sql)==false)
														throw new Exception('ERR_SQL');
										}
										catch(Exception $e){
												$erreur = new erreur_manager($e,$sql);
		}		}		}		}		}
		
    
		/**
        * Cette fonction get_cle_max() permet de retourner la valeur maximum de la table REGROUPEMENT
        * @access public                 
        */
		public function get_cle_max(){
        $max_return = 0;
        $sql = 'SELECT  MAX('.$this->champ_id.') as MAX_INSERT FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].''.
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
        * Cette fonction init_liste_table() permet d'initialiser la liste des tables de nomenclature
        * Toute table par exemple qui commence par TYPE_ est une table de nomenclature
        * @access public                 
        */
		public function init_liste_table(){        
        
        $entete     =   '<script type='.'"'.'text/javascript'.'"'.'>';	
        $entete     .=   "function recharge(table_nomenc, systeme,lire_dico) {";
        $entete	    .= "if (document.getElementById('check').checked==true) lire_dico=1; else lire_dico=0;";
		$entete	    .=   "location.href   = '?lib_nom_table='+table_nomenc+'&id_systeme='+systeme+'&lire_dico='+lire_dico";
        $entete     .=   " } ";
        
        $entete     .=  " function Alert_Supp(checkbox){ ";
        $entete     .=  "var chaine_eval ='document.form1.'+checkbox+'.checked == true';";       
        $entete     .=  " if (eval(chaine_eval)){ " ;
        $mess_alert		 	= $this->recherche_libelle(110,$_SESSION['langue'],'DICO_MESSAGE');
        $mess_alert			= addslashes($mess_alert);
        $entete     .=  "   alert ('$mess_alert	');";
        $entete     .=  "}  }";
        $entete     .=  " </script>";

        $entete     .="<form name='form1' action='".$_SERVER['REQUEST_URI']."' method='post'>";

        $this->entete_template = $entete;
        $this->fin_template="<div><INPUT TYPE='SUBMIT' VALUE='".$this->recherche_libelle_page('Valider',$_SESSION['langue'],'regroupement')."'></div></span></Form>";  
     
    }
   
    
		/**
        * Cette fonction comparer() permet de faire une comparaison matricielle entre deux flux
        * matriciels de même dimension
        * @access public
        * @param array() $matr1 contient le tableau de données de la première matrice
        * @param array() $matr2 contient le tableau de données de la deuxième matrice
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
    
		/**
        * Cette fonction haschilds() permet de verifier si regroupement donné est un noeud terminal 
        * c'est à dire est un fils.       
        * @access public
        * @param numeric $depht contient la valeur de la profondeur de la chaine de regroupement
        * @param numeric $code_regroupement contient la valeur de l'identificaeur du regroupement
        */
		public function haschilds($depht, $code_regroupement) {

        $result = false;

        if(isset($this->chaine[$depht])) {

            $curcodetyperegroup = $this->chaine[$depht][''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''];
			if(!in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])){
				$requete =  'SELECT COUNT(FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].') AS NB 
								FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
							' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].''.
							' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
							' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
							' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.';';                   
            }else{
				$requete =  'SELECT COUNT(FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].') AS NB 
								FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
							' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].''.
							' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
							' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
							' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
							' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].');';                   
			}
            $regroupements = $this->conn->GetAll($requete);
            
            // Gestion des erreurs lors de l'exécution de la requete sql
            try{
                if (!is_array($regroupements ))
                    throw new Exception('ERR_SQL');
            }
            catch(Exception $e){
                $erreur = new erreur_manager($e,$requete);
            }

            if((int)$regroupements[0]['NB'] > 0) {
    
                $result = true;
    
            }

        }

        return $result;

    }
           
    
		/**
        * Cette fonction getchildsid() permet de lister ou retourner la liste des fils d'un regroupement donné
        * il s'agit là d'une procédure recursive
        * @access public
        * @param numeric $depht contient la valeur de la profondeur de la chaine de regroupement
        * @param numeric $code_regroupement contient la valeur de l'identificaeur du regroupement
        * @param boolean $recur contient la valeur de la variable de contrôle de la recursivité
        */
		public function getchildsid($depht, $code_regroupement, $id_chaine, $recur=false) {

        $result = array();

        $haschilds = false;

        //on boucle sur les niveaux suivants afin de savoir s'il y'a des enfants
        foreach($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'] as $i=>$ncs) {

            if($this->haschilds($ncs, $code_regroupement)) {
    
                $haschilds = true;
                break;
    
            }

        }

        if(!$haschilds) {

            $result = array(0=>array($GLOBALS['PARAM']['TYPE_REGROUPEMENT']	=>	$code_regroupement));

        } else {

            if(isset($this->chaine[$depht])) {

                $curcodetyperegroup = $this->chaine[$depht][''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''];

                $requete = 'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
								FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L , '.$GLOBALS['PARAM']['HIERARCHIE'].' AS H'.
                                   ' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].''.
                                   ' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
                                   ' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
                                   ' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
								   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].
						   		   ' AND  H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$id_chaine.';';
                try {
                        $result = $this->conn->GetAll($requete);
        
                        foreach($result as $r) {
            
                            foreach($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'] as $i=>$ncs) {
                
                                $result = array_merge($this->getchildsid($ncs, $r[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''], $id_chaine, true), $result); 
            
                            }
                
                        }
                    }
                    catch(Exception $e){
                        $erreur = new erreur_manager($e, $requete);
                    }
            }

        }

        return $result;

    }
    
		/**
        * Cette fonction build_chaine() permet de reconstituer la hiérarchie de la chaine de regroupement
        * à partir de l'identificateur de la chaine de regroupement. La hiérarchie est constitué d'un empilement
        * des types de regroupement qui la constitue.
        * @access public
        * @param numeric $code_chaine contient la valeur de l'identificateur de la chaine de regroupement        
        */
		public function build_chaine($code_chaine) {

		$chaine_fille = array();

        // Gestion des erreurs lors de l'exécution de la requête sql
        try{

                if (!is_array($chaine_fille ))
                    throw new Exception('ERR_SQL');
                    
                if(count($chaine_fille) == 0) {
        
                    $requete     = 'SELECT DISTINCT T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', T_C.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', 
																		T_R.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
                                    FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' AS T_C, '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS T_R
                                    WHERE T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'=T_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
																		AND T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.(int)$code_chaine.'
                                    ORDER BY '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC;';
                    
                    // Gestion des erreurs lors de l'exécution de la requete sql
                    try {
                            $result = $this->conn->GetAll($requete);
                            if(!is_array($result)){
                                throw new Exception('ERR_SQL');
                            }
                
                            for($i=0;$i<(count($result));$i++) {
                
                                $result[$i][''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].''] = $i;
                
                                $result[$i]['NIVEAU_CHAINE_SUIVANT'] = array();
                
                                if(isset($result[($i+1)])) {
                
                                    $result[$i]['NIVEAU_CHAINE_SUIVANT'] = array(($i+1));
                
                                }
                
                            }
                        }
                        catch(Exception $e){
                            $erreur = new erreur_manager($e,$requete);
                        }
        
                    return $result;
        
                } else {
        
                    $result = array();
                    $temp = array();
                    $chaines = array();
        
                    foreach($chaine_fille as $cf) {
        
                        $requete     = 'SELECT DISTINCT T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', T_C.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', 
																				T_R.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
                                        FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' AS T_C, '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS T_R
                                        WHERE T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'=T_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
																				AND T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.(int)$cf['CODE_CHAINE'].'
                                        ORDER BY '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC;';
                        
                        // Gestion des erreurs lors de l'exécution de la requete sql
                        try {
                                $temp = $this->conn->GetAll($requete);
                                if (!is_array($temp)) {
                                    throw new Exception('ERR_SQL');
                                }                
                                for($i=0;$i<count($temp);$i++) {
                
                                    $temp[$i][''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].''] = $i;
                
                                }
                            }
                            catch(Exception $e){
                                $erreur = new erreur_manager($e, $requete);
                            }
            
                        $result = array_merge($result, $temp);
        
                    }
        
                    uasort($result, array('arbre', 'order_by_niveau_chaine'));
        
                    $temp = $result;
                    $result = array();
                    $codes_type_reg = array();
        
                    foreach($temp as $t) {
        
                        if(!in_array($t[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''], $codes_type_reg)) {
        
                            array_push($codes_type_reg, $t[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'']);
                            array_push($result, $t);
        
                        }
        
                    }
        
                    foreach($result as $i1=>$r1) {
        
                        $result[$i1]['NIVEAU_CHAINE_SUIVANT'] = array();
        
                        foreach($result as $i2=>$r2) {
        
                            if($r2[''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].''] == ($r1[''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'']+1)) {
        
                                array_push($result[$i1]['NIVEAU_CHAINE_SUIVANT'], $i2);
        
                            }
        
                        }
        
                    }
        
                    return $result;
        
                }
        }
        catch(Exception $e){
            $erreur = new erreur_manager($e,$requete_chaine_fille);
        }

    }

    
		/**
        * Cette fonction order_by_niveau_chaine() permet d'ordonnacer deux chaines de regroupement à partir du 
        * niveau de profondeur de chaque chaine.       
        * @access public
        * @param array() $a contient les données sur la chaine de regroupement a
        * @param array() $b contient les données sur la chaine de regroupement b
        */
		public function order_by_niveau_chaine($a, $b) {

        if($a[''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].''] > $b[''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'']) {

            return 1;

        }

        if($b[''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].''] > $a[''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'']) {

            return -1;

        }

        return 0;

    }
    
    	public function get_ord_max(){
			$max_return = 0;
			if (isset($this->id_regroupement) && $this->id_regroupement<>''){
				$sql =  'SELECT MAX(FILS.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].') AS MAX_INSERT'.
						' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
						' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
						' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
						' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.$this->id_regroupement.''.
						' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$this->id_type_fils.';';   
			}else{
				$sql	= 'SELECT MAX('.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].') AS MAX_INSERT'.
							' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].
							' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$this->id_type_regroupement.';';
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
        * Cette fonction maj_bdd() permet de mettre à jour les données dans la base de données à partir
        * de la matrice de valeurs passée en entrée
        * @access public
        * @param array() $matr contient les données de la matrice à mettre dans la base de données        
        */
		public function maj_bdd($matr){        
        if (is_array($matr)){
        	foreach ($matr as $tab){
                
				//Modif HEBIE pr gerer les code commencant par 0 vu que le champ code est ouvert now
				$val_debut_code = substr($tab[0],0,1);
				if($val_debut_code == '0') $tab[0] = substr($tab[0],1);
				//Fin Modif HEBIE
				
				$action = $tab[sizeof($tab)-1];
                //echo 'valeur action = ' .$action;
				if(count($tab)==6 && $tab[3]=="''") $tab[3] = $this->get_ord_max()+1;
                switch ($action){
                    case 'I':
                            // Insertion des occurrences des la tables regroupements                        
                            //$this->conn->debug=true;                                   
                            //echo 'passé insertion de dsonnées<br>';
                            //echo 'id_regroupement = '.$this->id_regroupement.'<br>';
                          
                            if (isset($this->id_regroupement) && $this->id_regroupement<>''){
                               // echo 'alassane !!!!!<br>';
                                $sql = 'INSERT INTO '.$GLOBALS['PARAM']['REGROUPEMENT'].' ('.$this->champ_id.','.$this->champ_code_admin.','.$this->champ_lib.','.$this->champ_ordre.','.$this->champ_type.')'.
                                   ' VALUES('.$tab[0].','.$this->conn->qstr($tab[1]).','.$this->conn->qstr($tab[2]).','.$tab[3].','.$this->id_type_fils.')';
                                    //echo $sql.'<br>';
                                    
                                    if ($this->conn->Execute($sql)==true)  {
                                        // insertion de la relation pere fils
                                        $sql =   'INSERT INTO '.$GLOBALS['PARAM']['LIAISONS'].' ('.$this->champ_id.','.$this->champ_pere.
                                                ') VALUES('.$tab[0].','.$this->id_regroupement.')';                                         
                                         //if ($this->conn->Execute($sql)==false)   print('Erreur d\'insertion de la relation père fils');
                                         // Gestion des erreurs lors de l'exécution de la requete sql
                                         try{
                                            if ($this->conn->Execute($sql)==false)
                                                throw new Exception('ERR_SQL');
                                         }
                                         catch(Exception $e){
                                             $erreur = new erreur_manager($e,$sql);
                                         }
                                    } else {
                                        //print('Erreur d\'insertion du regroupement');
                                         throw new Exception('ERR_SQL');
                                    }  
                                    
                            }else{
                                $sql = 'INSERT INTO '.$GLOBALS['PARAM']['REGROUPEMENT'].' ('.$this->champ_id.','.$this->champ_code_admin.','.$this->champ_lib.','.$this->champ_ordre.','.$this->champ_type.')'.
                                   ' VALUES('.$tab[0].','.$this->conn->qstr($tab[1]).','.$this->conn->qstr($tab[2]).','.$tab[3].','.$this->id_type_regroupement.')';
                               
                                //if ($this->conn->Execute($sql)==false)   print('Erreur d\'insertion du regroupement de premier niveau');   
                                // Gestion des erreurs lors de l'exécution de la requete sql
                                try{
                                    if ($this->conn->Execute($sql)==false)
                                        throw new Exception('ERR_SQL');
                                }
                                catch(Exception $e){
                                    $erreur = new erreur_manager($e,$sql);
                                }
                            }                            
                        break;
                    case 'U':
                        
                        // Mise à jour des libellés et ordre des regroupements encours de modification
                        $sql = ' UPDATE '.$GLOBALS['PARAM']['REGROUPEMENT'].' SET '.$this->champ_code_admin.'='.$this->conn->qstr($tab[1]).','.$this->champ_lib.'='.$this->conn->qstr($tab[2]).','.$this->champ_ordre.'='.$tab[3].
                               ' WHERE '.$this->champ_id.'='.$tab[0] ;                        
                        // Gestion des erreurs lors de l'exécution de la requete sql
                        try{
                            if ($this->conn->Execute($sql)==false)
                                throw new Exception('ERR_SQL');
                        }
                        catch(Exception $e){
                            $erreur = new erreur_manager($e,$sql);
                        }
                        
                        break;
                    case 'D' :
                        
                        // Cas des suppression intégrent la suppression en cascade des occurrences des regroupements 
                        // dans la relation père fils 
                                                
                        if (isset($this->id_regroupement) && $this->id_regroupement<>''){                             
                            // recherche de la profondeur                           
                             foreach ( $this->chaine as $hierarchie) {
                                if ($hierarchie[$this->champ_type]==$this->id_type_fils){
                                    $dept = $hierarchie[''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].''];
                                }
                            }                           

                            if ($this->haschilds($dept+1,$tab[0])==true){
                                // determiner l'ensemble des fils de façon recursive
                                $regroupements  =   $this->getchildsid($dept, $tab[0], $_SESSION['chaine'], $recur=false);                               
                                
                                if(is_array($regroupements )){
                                    $clause_in ='(';
                                    $i = 0;
                                    foreach($regroupements as $id_reg){
                                   		if ($i==0 && isset($id_reg[$this->champ_id])){
                                            $clause_in .= $id_reg[$this->champ_id];
                                        	$i++;
										}elseif(isset($id_reg[$this->champ_id])){
                                            $clause_in .= ','.$id_reg[$this->champ_id];
                                        	$i++;
										}
                                    }
                                    $clause_in  .=')';
                                    $sql = 'DELETE FROM '.$GLOBALS['PARAM']['LIAISONS'].' WHERE '.$this->champ_pere.' IN '.$clause_in;
                                    
                                    // if ($this->conn->Execute($sql)==false) print('Erreur de suppression par cascade');
                                    // Gestion des erreurs lors de l'exécution de la requete sql
                                    try{
                                        if ($this->conn->Execute($sql)==false)
                                            throw new Exception('ERR_SQL');
                                    }
                                    catch(Exception $e){
                                        $erreur = new erreur_manager($e,$sql);
                                    }
                                    
                                    $sql = 'DELETE FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$this->champ_id.' IN '.$clause_in;
                                    
                                   //if ($this->conn->Execute($sql)==false) print('Erreur de suppression par cascade');
                                   // Gestion des erreurs lors de l'exécution de la requete sql
                                    try{
                                        if ($this->conn->Execute($sql)==false)
                                            throw new Exception('ERR_SQL');
                                    }
                                    catch(Exception $e){
                                        $erreur = new erreur_manager($e,$sql);
                                    }
                                   
                                    $sql     =   'DELETE FROM '.$GLOBALS['PARAM']['LIAISONS'].' WHERE '.$this->champ_id.'='.$tab[0].' AND '.
                                                 $this->champ_pere.'='.$this->id_regroupement;                                            
                                            
                                    //if ($this->conn->Execute($sql)==false) print('Erreur de suppression de la relation avec le père');
                                    // Gestion des erreurs lors de l'exécution de la requete sql
                                    try{
                                        if ($this->conn->Execute($sql)==false)
                                            throw new Exception('ERR_SQL');
                                    }
                                    catch(Exception $e){
                                        $erreur = new erreur_manager($e,$sql);
                                    }
                                            
                                    $sql    =   'DELETE FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$this->champ_id.'='.$tab[0];
                                    //echo $sql.'<br>';
                                
                                    //if ($this->conn->Execute($sql)==false) print('Erreur de suppression du regroupement');
                                    // Gestion des erreurs lors de l'exécution de la requete sql
                                    try{
                                        if ($this->conn->Execute($sql)==false)
                                            throw new Exception('ERR_SQL');
                                    }
                                    catch(Exception $e){
                                        $erreur = new erreur_manager($e,$sql);
                                    }

                                    
                                }                              
                                
                                
                            }else{
                                // il n'y a aucun fils donc on peut supprimer la relation avec le père et le fils lui meme
                                $sql     =   'DELETE FROM '.$GLOBALS['PARAM']['LIAISONS'].' WHERE '.$this->champ_id.'='.$tab[0].' AND '.
                                            $this->champ_pere.'='.$this->id_regroupement;                                            
                               
                               //if ($this->conn->Execute($sql)==false) print('Erreur de suppression de la relation avec le père');
                               // Gestion des erreurs lors de l'exécution de la requete sql
                                try{
                                    if ($this->conn->Execute($sql)==false)
                                        throw new Exception('ERR_SQL');
                                }
                                catch(Exception $e){
                                    $erreur = new erreur_manager($e,$sql);
                                }
                                            
                                $sql    =   'DELETE FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$this->champ_id.'='.$tab[0];
                                
                               //if ($this->conn->Execute($sql)==false) print('Erreur de suppression du regroupement');
                               // Gestion des erreurs lors de l'exécution de la requete sql
                                try{
                                    if ($this->conn->Execute($sql)==false)
                                        throw new Exception('ERR_SQL');
                                }
                                catch(Exception $e){
                                    $erreur = new erreur_manager($e,$sql);
                                }
                            }
                        }else{
                             
                            foreach ( $this->chaine as $hierarchie) {
                                if ($hierarchie[$this->champ_type]==$this->id_type_regroupement){
                                    $dept = $hierarchie[''.$GLOBALS['PARAM']['NIVEAU_CHAINE'].''];
                                }
                            }
                            
                            if ($this->haschilds($dept+1,$tab[0])==true){
                                
                                // determiner l'ensemble des fils de façon recursive
                                // determiner l'ensemble des fils de façon recursive
                                $regroupements  =   $this->getchildsid($dept, $tab[0], $_SESSION['chaine'], $recur=false);
                                if(is_array($regroupements )){
                                    $clause_in ='(';
                                    $i = 0;
                                    foreach($regroupements as $id_reg){
                                        if ($i==0 && isset($id_reg[$this->champ_id])){
                                            $clause_in .= $id_reg[$this->champ_id];
											$i++;
										}elseif(isset($id_reg[$this->champ_id])){
                                            $clause_in .= ','.$id_reg[$this->champ_id];
                                        	$i++;
										}
                                    }
                                    $clause_in  .=')';
                                    $sql = 'DELETE FROM '.$GLOBALS['PARAM']['LIAISONS'].' WHERE '.$this->champ_pere.' IN '.$clause_in;
                                 
                                    //if ($this->conn->Execute($sql)==false) print('Erreur de suppression par cascade');
                                    // Gestion des erreurs lors de l'exécution de la requete sql
                                    try{
                                        if ($this->conn->Execute($sql)==false)
                                            throw new Exception('ERR_SQL');
                                    }
                                    catch(Exception $e){
                                        $erreur = new erreur_manager($e,$sql);
                                    }
                                    
                                    $sql = 'DELETE FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$this->champ_id.' IN '.$clause_in;                                    
                                    //if ($this->conn->Execute($sql)==false) print('Erreur de suppression par cascade'); 
                                    // Gestion des erreurs lors de l'exécution de la requete sql
                                    try{
                                        if ($this->conn->Execute($sql)==false)
                                            throw new Exception('ERR_SQL');
                                    }
                                    catch(Exception $e){
                                        $erreur = new erreur_manager($e,$sql);
                                    }
                                    
                                            
                                    $sql    =   'DELETE FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$this->champ_id.'='.$tab[0];                                    
                                
                                    //if ($this->conn->Execute($sql)==false) print('Erreur de suppression du regroupement');
                                    // Gestion des erreurs lors de l'exécution de la requete sql
                                    try{
                                        if ($this->conn->Execute($sql)==false)
                                            throw new Exception('ERR_SQL');
                                    }
                                    catch(Exception $e){
                                        $erreur = new erreur_manager($e,$sql);
                                    }
                                    
                                }
                            }else{
                                 
                                // il n'y a aucun fils donc on peut supprimer
                                $sql    =   'DELETE FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$this->champ_id.'='.$tab[0];
                                //echo $sql ;
                                //if ($this->conn->Execute($sql)==false) print('Erreur de suppression du regroupement');
                                // Gestion des erreurs lors de l'exécution de la requete sql
                                try{
                                    if ($this->conn->Execute($sql)==false)
                                        throw new Exception('ERR_SQL');
                                }
                                catch(Exception $e){
                                    $erreur = new erreur_manager($e,$sql);
                                }
                                
                            }                            
                        }                        
                        
                        break;
            }
            }
         }   
    }
}
?>
