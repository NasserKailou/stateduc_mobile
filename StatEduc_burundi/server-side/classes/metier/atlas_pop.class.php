<?php /** 
 * Classe Atlas Population
 * <pre>
 * Classe permettant la
 * -> gestion de la definition de donnees population selon l'atlas d'un pays spécifique à partir du modèle générique de l'UIS
 *    Dans cette modélisation toutes les occurrences des divisions administratives sont stockées 
 * -> dans une seule table. la relation père fils est matérialisée par une rélation réflexive.
 * </pre>
 * @access public
 * @author Alassane
 * @version 1.4
*/
class atlas_pop {

    	
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

		public $tableau_zone_saisie = array();
        
		public $matrice_donnees_tpl = array();
		
		public $code_nomenclature = array();
		
		public $codes_a_afficher = array();
		
	 	public $nb_cles	=	array();
		
		public $val_cle = array();
		
		public $tableau_type_zone_base = array();
		
		public $champs = array();
		
		public $champs_pop = array();
	 
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
			$this->champ_lib			=$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
			$this->champ_type			=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']; 
			$this->champ_pere 		=	$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'];
			
			if( is_array($GLOBALS['PARAM']['CHAMPS_POPULATION']) && (count($GLOBALS['PARAM']['CHAMPS_POPULATION'])) ){
				foreach($GLOBALS['PARAM']['CHAMPS_POPULATION'] as $i_chp => $chp_pop){
					$this->champs_pop[] = $chp_pop['NOM_CHAMP'];
				}
			}else{
				$this->champs_pop[] = $GLOBALS['PARAM']['NB_POPULATION_MALE']	;
				$this->champs_pop[] = $GLOBALS['PARAM']['NB_POPULATION_FEMALE']	;
			}
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
				$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', 
														A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].',
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
				$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', 
															A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].',
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
				$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', 
						'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
						FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$this->id_type_regroupement.
						' ORDER BY '.$GLOBALS['CHAMP_ORDRE'];
			}else{
				$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', 
							'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
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
		
		/////////////Selection des donnees de population
		if(!isset($this->VARS_GLOBALS[$GLOBALS['PARAM']['POPULATION']]['donnees_bdd'])){
				if(!in_array($this->id_type_regroupement,$_SESSION['fixe_type_regs'])){
					$sql = 'SELECT '.$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].','.
						$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].','.
						$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'];
						
						foreach($this->champs_pop as $i_chp => $chp_pop){
							$sql .= ', '.$GLOBALS['PARAM']['POPULATION'].'.'.$chp_pop.' AS '.$chp_pop;
						}
						$sql .=' FROM '.$GLOBALS['PARAM']['POPULATION'].', '.$GLOBALS['PARAM']['REGROUPEMENT'].
					
						' WHERE '.$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND '.$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_SESSION['annee_pop'].
						' AND '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$this->id_type_regroupement.
						' ORDER BY '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['CHAMP_ORDRE'];
				}else{
					$sql = 'SELECT '.$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].','.
							$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].','.
							$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'].' AS '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'];
						
							foreach($this->champs_pop as $i_chp => $chp_pop){
								$sql .= ', '.$GLOBALS['PARAM']['POPULATION'].'.'.$chp_pop.' AS '.$chp_pop;
							}
							$sql .=' FROM '.$GLOBALS['PARAM']['POPULATION'].', '.$GLOBALS['PARAM']['REGROUPEMENT'].
							
							' WHERE '.$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND '.$GLOBALS['PARAM']['POPULATION'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_SESSION['annee_pop'].
							' AND '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$this->id_type_regroupement.
							' AND '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$this->id_type_regroupement].')'.
							' ORDER BY '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['CHAMP_ORDRE'];
				}
						
						
				$tab_donnees_bdd = array();
			  
			  	//echo '<br>'.$sql .'<br>';
				// Traitement Erreur Cas : Execute / GetOne
				try {            
						$recordSet	=	$this->conn->Execute($sql);
						if($recordSet ===false){                
								 throw new Exception('ERR_SQL');   
						}
						$i=0;
						while (!$recordSet->EOF){
							$ligne = $recordSet->fields;
							//echo '<pre>';
							//print_r($ligne);
							$id_ligne = $ligne[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
							$j=0;
							foreach ($ligne as $col){
								//$tab_donnees_bdd[$id_ligne][$j]=$col;
								$tab_donnees_bdd[$i][$j]=$col;
								$fld = $recordSet->FetchField($j);
								$fld_name = $fld->name;
								if( trim($col) == '255' && (preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$fld_name)) ){
									//$tab_donnees_bdd[$id_ligne][$j] = '';
									$tab_donnees_bdd[$i][$j] = '';
								}
								$j++;
							}
							$i++;			
							$recordSet->MoveNext();
						}
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$sql);
				}        
				// Fin Traitement Erreur Cas : Execute / GetOne				
				//$this->matrice_donnees['POPULATION']	=	$tab_donnees_bdd;
				$this->VARS_GLOBALS[$GLOBALS['PARAM']['POPULATION']]['donnees_bdd'] = $tab_donnees_bdd;
			}
			//echo'DONNEES BDD<pre>';
			//print_r($this->VARS_GLOBALS[$GLOBALS['PARAM']['POPULATION']]['donnees_bdd']);
            
			// Limitation des données par rapport à un début et au nombre de lignes dispo sur le template
			$this->faire_correspondances($GLOBALS['PARAM']['POPULATION']);
			$this->limiter_affichage_pop($GLOBALS['PARAM']['POPULATION']);
		///////Fin selection donnees de population
        
        
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
            $donnees_transformees[1] = $ligne_donnes[$this->champ_lib ];
            $donnees_transformees[2] = $ligne_donnes[$this->champ_type];
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
                	$this->codes_a_afficher = $mat;
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
		
		function est_ds_tableau($elem,$tab){
			if(is_array($tab))
				foreach($tab as $elements){
					if( $elements == $elem ){
						return true;
					}
				}
		}
		
		/**
		*METHODE :  faire_correspondances ( tableliee )
		*<pre>
		*DEBUT
		*	POUR CHAQUE tableliee de nomtableliee
		*		POUR CHAQUE ligne_donnees de VARS_GLOBALS['donnees_bdd']
		*			VARS_GLOBALS[tableliee][ligne_code] <-- Combinaison_Unique_Clés(ligne_donnees)
		*			(cette variable permettra d’associer chacun de ces enregistrements à une ligne du template)
		*		FIN POUR
		*	FIN POUR
		*FIN
		*</pre>
		*/
		function faire_correspondances($nomtableliee){
			$chaine_eval="\$annee = array('type' => 'c','champ' => '".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."','table_ref' =>'','ordre' => 10,'sql' => '');";
			eval($chaine_eval);
			$this->tableau_zone_saisie[$nomtableliee][]=$annee;
			
			$chaine_eval="\$regroup = array('type' => 'sys_txt','champ' => '".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."','table_ref' =>'','ordre' => 20,'sql' => '');";
			eval($chaine_eval);
			$this->tableau_zone_saisie[$nomtableliee][]=$regroup;
			
			$chaine_eval="\$age = array('type' => 'cmm','champ' => '".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']."','table_ref' =>'".$GLOBALS['PARAM']['TYPE_AGE_POPULATION']."','ordre' => 30,'sql' => 'SELECT ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']." FROM ".$GLOBALS['PARAM']['TYPE_AGE_POPULATION']." ORDER BY ".$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']."');";
			eval($chaine_eval);
			$this->tableau_zone_saisie[$nomtableliee][]=$age;
			
			$ordre = 40 ;
			foreach($this->champs_pop as $i_chp => $chp_pop){
				$chaine_eval="\$var_pop = array('type' => 'tvm','champ' => '".$chp_pop."','table_ref' =>'','ordre' => ".$ordre.",'sql' => '');";
				eval($chaine_eval);
				$this->tableau_zone_saisie[$nomtableliee][]=$var_pop;	
				$ordre += 10;
			}
			
			////////////////// position du champ clé à valeur multiple // ex: code_niveau
			foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
				if( $zone_saisie['type']=='cmm' ){
					$this->VARS_GLOBALS[$nomtableliee]['pos_cmm'] = $pos;
					break;
				}
			}
			////////////////// position du champ clé à valeur unique de type texte// ex: code_etablissement
			foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
				if( $zone_saisie['type']=='csu' ){
					$this->VARS_GLOBALS[$nomtableliee]['pos_csu'] = $pos;
					break;
				}
			}
			////////////////// positions des champs clés à valeur unique // ex: code_regroupement, code_distance
			foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
				if( $zone_saisie['type']=='cmu' or $zone_saisie['type']=='sco' or $zone_saisie['type']=='csu' or $zone_saisie['type']=='sys_txt' ){
					$this->VARS_GLOBALS[$nomtableliee]['pos_cmu'][$pos] = $this->tableau_zone_saisie[$nomtableliee][$pos]['champ'];
				}
			}
			////////////////////// les differentes valeurs prises par chaque clé de type cmu
			//$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm'];
			if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'])){
				foreach( $this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos => $nom_champ ){
					$mat = array();
					foreach($this->VARS_GLOBALS[$nomtableliee]['donnees_bdd'] as $id_ligne => $ligne_donnees){
						if( isset($ligne_donnees[$pos]) ){
							if( !$this->est_ds_tableau($ligne_donnees[$pos],$mat) ){
								//$mat[$id_ligne] = $ligne_donnees[$pos];
								$mat[] = $ligne_donnees[$pos];
							}
						}
					}
					//if(count($mat))
						//sort ($mat,SORT_NUMERIC);
					$this->VARS_GLOBALS[$nomtableliee]['val_cmu'][$pos] = $mat;
					//echo'<pre>';
					//print_r($this->VARS_GLOBALS[$nomtableliee]['val_cmu'][$pos]);
				}
			}
			//////////////////////// faire correspondre les lignes avec les combinaisons de clés ccu
			//$fin_parcours_imbrique = '';
			if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'])){
				foreach($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos => $value){
					$tab_pos_cmu[] = $pos;
					//$fin_parcours_imbrique.=' } ';
				}
			}
			
			/////////////////////////// FAIRE LES EQUIVALENCES ENTRE LIGNES ET LES CODES DES DLES
			$mat = array();
			foreach( $this->VARS_GLOBALS[$nomtableliee]['donnees_bdd'] as $id_ligne => $ligne_donnees){
				$assoc_cmu_courante = array();
				if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'])){
					foreach( $this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos =>$nom_champ ){
						$assoc_cmu_courante[] = $ligne_donnees[$pos];
					}
				}
				if(!$this->est_ds_tableau($assoc_cmu_courante,$mat)){
					//$mat[$id_ligne] = $assoc_cmu_courante;	
					$mat[] = $assoc_cmu_courante;	
				}
			}
			$this->VARS_GLOBALS[$nomtableliee]['ligne_code'] = $mat;
			//////////////////////////////////////////////////////////////////////////////	
		}    
		
		/**
		* METHODE :  limiter_affichage ( tableliee )
		* Permet à partir du jeu d'enregistrement global, de se positionner 
		* sur les données concernant la page en cours
		* Ces données sont rangées dans la variable  : $matrice_donnees
		*<pre>
		*DEBUT
		*	matrice_donnees[tableliee] = array
		*	limite_enr <-- LIMITER(VARS_GLOBALS , debut, nb_lignes)
		*	( N’avoir que les codes uniques des enregistrements pour la 
		*   page en cours )
		*	POUR CHAQUE ligne ligne_donnees de VARS_GLOBALS['donnees_bdd']
		*		SI Combinaison_Unique_Clés(ligne_donnees) EST DANS limite_enr
		*			RANGER ligne_donnees dans matrice_donnees[tableliee]
		*		FIN SI
		*	FIN POUR
		*FIN
		*</pre>
		*/
		function recherche_code_affich($ligne_code,$tab_codes){
			$tab=array();
			foreach($ligne_code as $id_ligne => $ligne){
				if(in_array($ligne[0],$tab_codes)){
					$tab[$id_ligne]=$ligne;
				}
			}
			return $tab;
		}	
		function limiter_affichage_pop($nomtableliee){
			//if(!isset($this->VARS_GLOBALS[$nomtableliee]['ligne_code']))
			//$this->VARS_GLOBALS[$nomtableliee]['ligne_code'] = $this->faire_liaison_ligne_code($nomtableliee) ;	
			if(!isset($GLOBALS['nbre_total_enr']))
				$GLOBALS['nbre_total_enr'] = count( $this->VARS_GLOBALS[$nomtableliee]['ligne_code'] );
			$tab_codes=array();
			foreach($this->codes_a_afficher as $ligne){
				$tab_codes[]=$ligne[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
			}
			//$ligne_code_limit = array_slice( $this->VARS_GLOBALS[$nomtableliee]['ligne_code'], $GLOBALS['debut'], $GLOBALS['cfg_nbres_ppage']);
			$ligne_code_limit = $this->recherche_code_affich($this->VARS_GLOBALS[$nomtableliee]['ligne_code'],$tab_codes);
			foreach($ligne_code_limit as $val_code)
				$this->VARS_GLOBALS[$nomtableliee]['ligne_code_limit'][] = $val_code;
			//echo'**************<pre>';
			//print_r($ligne_code_limit);
			
			//$indice_cle = $this->nb_cles[$nomtableliee] - 1;
			if(is_array($this->VARS_GLOBALS[$nomtableliee]['donnees_bdd']))
			foreach( $this->VARS_GLOBALS[$nomtableliee]['donnees_bdd'] as $id_ligne => $ligne_donnees){
				$assoc_cmu_courante = array();
				if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu']))
				foreach( $this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos =>$nom_champ ){
					$assoc_cmu_courante[]=$ligne_donnees[$pos];
				}
				if($this->est_ds_tableau($assoc_cmu_courante,$this->VARS_GLOBALS[$nomtableliee]['ligne_code_limit'])){
					$this->matrice_donnees[$nomtableliee][] = $ligne_donnees;	
				}
			}
			$GLOBALS['nbenr'] = count($this->matrice_donnees[$nomtableliee]);
				//echo'matrice_donnees<pre>';
				//print_r($this->matrice_donnees[$nomtableliee]);
	
		}
		
		/**
        * Cette fonction remplir_template() permet de remplir le template population à partir des données provenant de la  
        * base de données et ayant subies les transformations nécessaires pour une présentation conforme au principe 
        * du template asssocié. Le résultat est évalué et afficher à l'écran               
        * @access public 
        * @param String $template contient le flux de caractères répresentant le template
        */
	public function remplir_template($template, $entete){
		if (is_array($this->matrice_donnees)){  
            
            // entete du template
            ${'id_name'}				= $this->id_name;
            ${'lib_name'}				=	$this->lib_name;
            ${'lib_ordre'}			=	$this->lib_ordre;
            ${'lib_entete'}			=	$entete;
			
			for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                
				// cas des champs id                   
                if (isset($this->matrice_donnees[$ligne][$this->champ_id ])){
                  //  echo 'passé la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_id ];
                    //$val_champ_base = addslashes($val_champ_base);                    
					${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'_'.$ligne} = $val_champ_base;
                }else{
					${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'_'.$ligne} = '';
                }
                
				// cas des champs type
                if (isset($this->matrice_donnees[$ligne][$this->champ_type])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_type];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'ORDRE_'.$ligne} = ''.$val_champ_base.'';                    
                }else{
                    ${'ORDRE_'.$ligne} = '';	
                }
                
				// cas des champs libellé
                if (isset($this->matrice_donnees[$ligne][$this->champ_lib])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_lib];                    
                    //$val_champ_base = addslashes($val_champ_base);                    
					${'LIBELLE_'.$ligne} = $val_champ_base;
                }else{
					${'LIBELLE_'.$ligne} = '';
                }
			}
		}
		
		//////Champs donnees de population
		$this->set_code_nomenclature();
		$nomtableliee=$GLOBALS['PARAM']['POPULATION'];
		$this->preparer_donnees_tpl($nomtableliee);
		//echo'<pre>';
		//print_r($this->matrice_donnees_tpl[$nomtableliee]);
			if(is_array($this->matrice_donnees_tpl[$nomtableliee])){			
				//for($ligne=0;$ligne<$this->nb_lignes;$ligne++){ // pour chq ligne du Template
				foreach($this->matrice_donnees_tpl[$nomtableliee] as $ligne => $ligne_donnees){ // pour chq ligne du Template
					foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){ // Parcours des zones
						if( $zone_saisie['type']=='tvm' ){ // Structure matricielle : val multi
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							
							$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm']; // clé val multi
							$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$pos_cmm]['table_ref'];
							
							if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice])){
								foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
                                    //  parcours des codes clé val multi
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$pos_cmm][$val_matrice][$colonne])){
										$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$pos_cmm][$val_matrice][$colonne];
										//$val_champ_base = addslashes($val_champ_base);
										${$nom_champ.'_'.$ligne.'_'.$val_matrice} = ''.$val_champ_base.'';
										//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
									}
									else{
										//${$nom_champ.'_'.$ligne} = '\'\'';
										${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '';	
									}
								}
							}
						}
						elseif( $zone_saisie['type']=='csu' ){ // Clé dont la valeur est saisie comme un type TEXT
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							
							$pos_csu = $this->VARS_GLOBALS[$nomtableliee]['pos_csu'];
							$nom_table_ref = $this->tableau_zone_saisie[$nomtableliee][$pos_csu]['table_ref'];
							
							if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$pos_csu])){
								$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$pos_csu];
								//$val_champ_base = addslashes($val_champ_base);
								${$nom_champ.'_'.$ligne} = ''.$val_champ_base.'';
								//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
							}
							else{
								${$nom_champ.'_'.$ligne} = '';	
							}
						}
						elseif( $zone_saisie['type']=='s' or $zone_saisie['type']=='sys_txt' ){ // champ TEXT
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								if(is_array($nom_champ))
									$nom_champ = $nom_champ[0];
								if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]) && $nom_champ<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']){
									$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne];
									//$val_champ_base = addslashes($val_champ_base);
									${$nom_champ.'_'.$ligne} = ''.$val_champ_base.'';
									//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
								}
								elseif($nom_champ<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']){
									//${$nom_champ.'_'.$ligne} = '\'\'';
									${$nom_champ.'_'.$ligne} = '';	
								}
						}
					}
				}
			}
	    ////////Fin champs donnees de population   
		
        eval('$result = sprintf(\'%s\', "'.str_replace('"','\"',$template).'");');

        return $result;
    }
	
	function preparer_donnees_tpl($nomtableliee){
		$mat = array();
		$tab_assoc_cmu = array();
		$ligne = -1;
		//echo'matrice_donnees<pre>';
		//print_r($this->matrice_donnees[$nomtableliee]);
		
		$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm']; // récuperer la position de la clé val multi
		//$indice_cle = $this->nb_cles[$nomtableliee] - 1;
			if(is_array($this->matrice_donnees[$nomtableliee])){
				if($pos_cmm){ // si clé val multi existe
						foreach($this->matrice_donnees[$nomtableliee] as $id_ligne => $ligne_donnees){
							
							$tab_val_cmu = array();
							
							$tab_assoc_cmu_courante = array();
							if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'])){
								
								foreach( $this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos_c =>$nom_champ ){
									$tab_assoc_cmu_courante[] = $ligne_donnees[$pos_c];
								}
							}
							if(!$this->est_ds_tableau($tab_assoc_cmu_courante,$tab_assoc_cmu)){
								
								$tab_assoc_cmu[] = $tab_assoc_cmu_courante;	
								$ligne++ ;
								$ligne_courante = $ligne ;
								//$ligne_courante = $ligne_donnees[1] ;
								for( $pos=0; $pos <= $pos_cmm; $pos++ ){
									$mat[$ligne_courante][$pos] = $ligne_donnees[$pos];
								}
								$mat[$ligne_courante][$pos_cmm] = array();
							}else{ // recherche de la ligne associée à la combinaison des clés courantes
								foreach($tab_assoc_cmu as $curr_ligne => $combined_keys){
									if( !count(array_diff_assoc($combined_keys, $tab_assoc_cmu_courante)) ){
										$ligne_courante = $curr_ligne ;
										break;
									}
								}
							}
							$cle_cmm = $ligne_donnees[$pos_cmm];
							//echo"cle_cmm=$cle_cmm-----------------<br>";
							
							//echo'<pre>';
							//print_r($mat[$ligne][$pos_cmm]);
							//echo'<br>********************<br>';
							
							for( $pos=$pos_cmm+1; $pos < count($ligne_donnees); $pos++ ){
								$mat[$ligne_courante][$pos_cmm][$cle_cmm][$pos] = $ligne_donnees[$pos];
							}
						}
				}
				else{ // pas de clé val multi
                        $mat = $this->matrice_donnees[$nomtableliee];
				}
				$mat_indexee=array();
				foreach($mat as $ligne_donnees){
					$mat_indexee[$ligne_donnees[1]]=$ligne_donnees;	
				}
				//$this->matrice_donnees_tpl[$nomtableliee] = $mat ;	
				$this->matrice_donnees_tpl[$nomtableliee] = $mat_indexee ;	
			}
					//echo'matrice_donnees_tpl<pre>';
					//print_r($this->matrice_donnees_tpl);
		}
		
		function get_champ_extract($nom_champ){
			$champ_extract = $nom_champ;
			if (strlen($nom_champ)>30) {
				if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql') { 
					$taille_max_extract=strlen($nom_champ);                
				}else if ($GLOBALS['conn']->databaseType == 'postgres9') {
					$taille_max_extract=strlen($nom_champ);
				}else{
					$taille_max_extract=31;                
				}
				$champ_extract = substr($nom_champ ,0,$taille_max_extract); 
			}
			return($champ_extract);
		}
		
		function set_code_nomenclature(){
				$nomtableliee=$GLOBALS['PARAM']['POPULATION'];
				foreach($this->tableau_zone_saisie[$nomtableliee] as $zone_saisie){
					if( ($zone_saisie['type']=='cmu') or ($zone_saisie['type']=='sco') or ($zone_saisie['type']=='cmm') or 
							($zone_saisie['type']=='co') or ($zone_saisie['type']=='m') or ($zone_saisie['type']=='b')){
						if (!is_null($zone_saisie['sql']) and (trim($zone_saisie['sql']) <> '') ){
								//echo '<br>'.$zone_saisie['sql'].'<br>';
								// Traitement Erreur Cas : Execute / GetOne
								try {            
										$rs		= 	$this->conn->Execute($zone_saisie['sql']);
										if($rs ===false){                
												 throw new Exception('ERR_SQL');   
										}
										$code	=	array();
										while (!$rs->EOF) {
											//$code[] = $rs->fields[];
											$code[] = $rs->fields[$this->get_champ_extract($zone_saisie['champ'])];
											$rs->MoveNext();							
										}
										$this->code_nomenclature[$nomtableliee][$zone_saisie['table_ref']]	=	$code;
								}
								catch (Exception $e) {
										 $erreur = new erreur_manager($e,$zone_saisie['sql']);
								}        
								// Fin Traitement Erreur Cas : Execute / GetOne								
						}
					}
				}
				//echo'VALS CODE <pre>';
				//print_r($this->code_nomenclature[$nomtableliee]);
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
		/*
		public function get_post_template($matr){      
              
        if (is_array($matr)){              
            
            $max_cle_incr = $this->get_cle_max();   
            
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                $champ_id       = 'CODE_'.$ligne;
                $champ_lib      = 'LIBELLE_'.$ligne;
                $champ_type    	= 'ORDRE_'.$ligne;
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
                        $donnees_ligne [] = $matr[$champ_lib];
                        //$donnees_ligne [] = $matr[$champ_type]; 
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
    */
	
	/**
	* Méthode extraire_valeur_matrice permet l'extraction de la valeur encodée dans les champs dont les valeurs
	* renvoyées sont sous la forme $nom_champ_ligne_valeur
	*
	* @access public
	* 
	*/

	function extraire_valeur_matrice($texte){
		// cette permet l'extraction de la valeur encodée dans le champ de type  matriciel 
		$val = explode('_',$texte);
		return ($val[count($val)-1]);
	}
	
	function transformer_matrice_post($matr){
		$tab_id_ligne=array();
		foreach($matr as $nom_chp => $val_chp){
			if(preg_match('/'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'/',$nom_chp))
				$tab_id_ligne[]=$val_chp;
		}
		return $tab_id_ligne;
	}
	
	function get_post_template($matr){
		//global $val_cle;
		//echo'_POST<pre>';
		//print_r($_POST);
		//echo'tableau_zone_saisie<pre>';
	  	//print_r($this->tableau_zone_saisie);
		//foreach($this->nomtableliee as $nomtableliee){	
			$nomtableliee=$GLOBALS['PARAM']['POPULATION'];
			$this->nb_cles=array($nomtableliee => 3);
			$pos_cle_incr	=	$this->nb_cles[$nomtableliee] - 1;
			$this->val_cle=array($nomtableliee => array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'] => $_SESSION['annee_pop']));
			$matrice = array();
			//$max_cle_incr = $this->get_cle_max($nomtableliee) ;
			
			$nb_ligne_post = 0;
			$ligne_mat = -1;
			$ligne_code=-1;
		if(is_array($matr)){			
			//for($ligne=0;$ligne<$this->nb_lignes;$ligne++){ // pour chq ligne du Template
			$tab_id_ligne=$this->transformer_matrice_post($matr);
			foreach($tab_id_ligne as $ligne){ // pour chq ligne du Template
				///// on teste si la ligne est saisie
				$ligne_code++;
				$ligne_vide = true;
				$nb_colonnes = count($this->tableau_zone_saisie[$nomtableliee]);
				foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){
					$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
					///////////////
					if( $zone_saisie['type']=='tvm' ){
						if(is_array($nom_champ))
							$nom_champ = $nom_champ[0];
						
						$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm'];
						$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$pos_cmm]['table_ref'];
						
						foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
							$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;			
							if( isset($matr[$v]) and $matr[$v]<>''){				
								$ligne_vide = false;
								$nb_ligne_post++;
								break;
							}
						}
						if($ligne_vide == false)
							break;
					}
					elseif( $zone_saisie['type']=='cmu' or $zone_saisie['type']=='sco' 
									or $zone_saisie['type']=='csu' or $zone_saisie['type']=='co' or $zone_saisie['type']=='s'
									or $zone_saisie['type']=='m' or $zone_saisie['type']=='b' or $zone_saisie['type']=='sys_txt') {
						//echo"<br>type_zone=".$zone_saisie['type']."";
						$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
						if(is_array($nom_champ))
							$nom_champ = $nom_champ[0];
						//$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'];
						$v = $nom_champ.'_'.$ligne ;			
						if( isset($matr[$v]) and $matr[$v]<>''){				
							$ligne_vide = false;
							$nb_ligne_post++;
							break;
						}
					}
				}
	
				/////////////////
				if( ($ligne_vide) == false and (!isset($matr['DELETE_'.$ligne_code])) ){			
					$ligne_mat++;
					//$ligne_mat = $ligne;
					for($colonne=0; $colonne < count($this->tableau_zone_saisie[$nomtableliee]); $colonne++){
						if($this->tableau_zone_saisie[$nomtableliee][$colonne]['type'] == 'c'){
							$nom_cle = $this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							$matr_1[$ligne_mat][$colonne] = $this->val_cle[$nomtableliee][$nom_cle];
						}
						$type_zone = $this->tableau_zone_saisie[$nomtableliee][$colonne]['type'];
						//echo"<br>type_zone=".$type_zone."";
						if( $type_zone == 'cmu' or $type_zone == 'sco' or $type_zone == 'csu'  or $type_zone=='co' 
								or $type_zone=='s' or $type_zone=='m' or $type_zone=='b' or $type_zone=='sys_txt' ){
							//$nom_cle = $this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							//$matr_1[$ligne_mat][$colonne] = $this->val_cle[$nomtableliee][$nom_cle];
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];				
							$v = $nom_champ.'_'.$ligne_code ;				
							if( isset($matr[$v]) and $matr[$v]<>''){					
								$val_champ = $matr[$v];
								if($type_zone == 'cmu' or $type_zone == 'sco' or $type_zone=='co' or $type_zone=='m' or $type_zone=='b')
									$val_champ = $this->extraire_valeur_matrice($matr[$v]);						
								$matr_1[$ligne_mat][$colonne] = $val_champ ;
							}
							else
								$matr_1[$ligne_mat][$colonne] = '' ;
						}
					}
					$pos_cmm 			= $this->VARS_GLOBALS[$nomtableliee]['pos_cmm'];
					$nom_matrice 	= $this->tableau_zone_saisie[$nomtableliee][$pos_cmm]['table_ref'];
					/////////////////////////////////
					if($pos_cmm){
							//echo 'rentre ici';
							foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
								$matr_1[$ligne_mat][$pos_cmm] = $val_matrice ; 
								$mat = array();
								$mat = $matr_1[$ligne_mat] ;
								$mat_plus = array();
								$champs_saisis = false ;
								
								$nb_colonnes = count($this->tableau_zone_saisie[$nomtableliee]);
								for($colonne = $this->nb_cles[$nomtableliee]; $colonne < $nb_colonnes; $colonne++){
									if( $this->tableau_zone_saisie[$nomtableliee][$colonne]['type']=='tvm' ){
										$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
										if(is_array($nom_champ))
											$nom_champ = $nom_champ[0];
		
										$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;			
										if( isset($matr[$v]) and $matr[$v] <> '' ){				
											$champs_saisis = true ;
											$val_champ = $matr[$v];
											$mat_plus[] = $val_champ ;
										}
										else{
											$mat_plus[] = '' ;
										}
									}
								}
								if($champs_saisis==true){
									foreach($mat_plus as $val_mesure)
										$mat[] = $val_mesure;
									ksort($mat);
									//$matrice[$ligne] = $mat;
									$matrice[] = $mat;
									$champs_saisis = false ;
								}
							}
					}
					else{
							$matrice = $matr_1;
					}
				}
			}
		}
		$this->matrice_donnees_post[$nomtableliee]	=	$matrice;
		//echo'matrice_donnees_post<pre>';
		//print_r($this->matrice_donnees_post[$nomtableliee]);
	//}
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
        
		function comparer ($matr1,$matr2){
		// Cette fonction permet de faire la comparaison matricielle complexe		
		/*
		echo 'matr1<pre>';
		print_r($matr1);
		echo '<br><br>matr2<pre>';
		print_r($matr2);
		*/
		//foreach($this->nomtableliee as $nomtableliee){
			$nomtableliee = $GLOBALS['PARAM']['POPULATION'];
			$indice_cle	=	$this->nb_cles[$nomtableliee] - 1;
					
			$result 	=	array();
			$i = 0;
			if(is_array($matr2))
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
			if(is_array($matr1))
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
			$this->matrice_donnees_bdd[$nomtableliee]	=	$result;
			//echo '<br>matrice_donnees_bdd<pre>';
			//print_r($this->matrice_donnees_bdd[$nomtableliee]);
		//}		
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
    
    /**
	* METHODE :  maj_bdd ()
	* Utilise $matrice_donnees_bdd pour insérer, supprimer, 
	* mettre à jour des données de la base 
	*<pre>
	*DEBUT
	*	(ON EFFECTUE D’ABORD LES UPDATE CAR NE DEMANDANT AUCUN 
	*  ORDRE SUR LES TABLES LIEES)
	*		POUR CHAQUE tableliee de nomtableliee
	*			PARCOUCIR matrice_donnees_bdd [tableliee]
	*				SI ACTION = ‘U’  (cas update)
	*					EFFECTUER REQUETE D’UPDATE
	*				FIN SI
	*			FIN PARCOURIR
	*		FIN POUR
	*	(ON EFFECTUE  LES INSERT APRES AVOIR ORDONNE LES TABLES 
	*  SUIVANT LE  BON ORDRE D’INSERTION)
	*		ordre_insert <-- ordre_insert_tables()
	*		POUR CHAQUE tableliee de ordre_insert
	*			PARCOUCIR matrice_donnees_bdd [tableliee]
	*				SI ACTION = ‘I’  (cas insert)
	*					EFFECTUER REQUETE D’INSERT
	*				FIN SI
	*			FIN PARCOURIR
	*		FIN POUR
	*	(ON EFFECTUE LES DELETE DANS L’ORDRE INVERSE DE L’INSERTION 
	*  DS LES TABLES )
	*		ordre_delete <-- ordre_delete_tables()
	*		POUR CHAQUE tableliee de ordre_delete
	*			PARCOUCIR matrice_donnees_bdd [tableliee]
	*				SI ACTION = ‘D’  (cas delete)
	*					EFFECTUER REQUETE DE DELETE
	*				FIN SI
	*			FIN PARCOURIR
	*		FIN POUR
	*FIN
	*</pre>
	*/
	function maj_bdd(){

		//////////  TRAITEMENT DES DELETE
		$nomtableliee=$GLOBALS['PARAM']['POPULATION'];
		$ordre_delete = array(0 => $nomtable);
		
		$tab_push = array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'] => int,$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'] => int,$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'] => int);
		foreach($this->champs_pop as $i_chp => $chp_pop){
			$tab_push[$chp_pop] = int;
		}
		$this->tableau_type_zone_base = array($nomtableliee => $tab_push);
		
		$tab_push = array(0 =>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],1 =>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'],2 =>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']);
		$ord_push = 3;
		foreach($this->champs_pop as $i_chp => $chp_pop){
			$tab_push[$ord_push] = $chp_pop;
			$ord_push++;
		}
		$this->champs = array($nomtableliee => $tab_push);
		//foreach($ordre_delete as $nomtableliee){
			 $matr = $this->matrice_donnees_bdd[$nomtableliee];
			 $tab_champ = $this->champs[$nomtableliee];		
			 $nbcle	=	$this->nb_cles[$nomtableliee];
			 $nomtable = $nomtableliee;
			
			 if (is_array($matr)){
				$nb_col =count($matr[0]);
				foreach ($matr as $tab){
					$action = $tab[$nb_col-1];
					
					/*
					 * Suppression dans une table
					 */
					if ($action =='D'){
						$sql = "DELETE FROM $nomtable WHERE";
						//$sql .= " $tab_champ[0] = $tab[0]";        	
						for ($i=0 ; $i < $nbcle ; $i++){
							if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[$i]])=='int'){
								$sql 	.= "AND  $tab_champ[$i] = $tab[$i] ";
							}else{
								$sql 	.= "AND  $tab_champ[$i] = ".$this->conn->qstr($tab[$i])." ";
							}
						}
						$sql = str_replace('WHEREAND', 'WHERE', $sql);
						//echo '<BR> ---DELETE---<BR>'.$sql;
						if ($this->conn->Execute($sql) === false){
							$GLOBALS['theme_data_MAJ_ok'] 	= false;
							print ' <br> error deleting :<br>'.$sql.'<br>';  
						}  
					}
				}                   
			}
		//}
		/////////   FIN DE TRAITEMENT DES DELETE
		
		//////////  TRAITEMENT DES INSERT
		$ordre_insert = array(0 => $nomtableliee);
		//echo'ordre_insert<pre>';
		//print_r($ordre_insert);
		//foreach($ordre_insert as $nomtableliee){
			 $matr = $this->matrice_donnees_bdd[$nomtableliee];
			 $tab_champ = $this->champs[$nomtableliee];		
			 $nbcle	=	$this->nb_cles[$nomtableliee];
			 $nomtable = $nomtableliee;
			
			 if (is_array($matr)){
				$nb_col =count($matr[0]);
				foreach ($matr as $tab){
				/*
				* Ajout dans une table
				*/
					//foreach ($tab as $elt){}
					$action = $tab[$nb_col-1];
					if ($action =='I'){
						$sql_champs='';
						$sql_vals='';
						$sql = '';
						
						foreach($tab as $col=>$elt){
							if (($elt or $elt=='0' ) and $elt !='I'){
								$sql_champs .= ", $tab_champ[$col]";
                                if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[$col]])=='int'){
								    $sql_vals 	.= ", $elt";
                                }else{
                                    $sql_vals 	.= ", ".$this->conn->qstr($elt);
                                }
							}
						}
						$sql.= 'INSERT INTO '.$nomtable.' ('.$sql_champs.') VALUES ('.$sql_vals.')';
						$sql = str_replace('(,','(',$sql);	
						//echo '<BR> ---INSERT--- <BR>'.$sql.'<BR>'; 
						if ($this->conn->Execute($sql) === false){
							$GLOBALS['theme_data_MAJ_ok'] 	= false;
							print ' <br> error inserting :<br>'.$sql.'<br>';    
						}
					}
				}                   
			}
		//}
		/////////   FIN DE TRAITEMENT DES INSERT
	
		//////////  TRAITEMENT DES UPDATE
		//foreach($this->nomtableliee as $nomtableliee){
			 $nomtableliee=$GLOBALS['PARAM']['POPULATION'];
			 $matr = $this->matrice_donnees_bdd[$nomtableliee];
			 $tab_champ = $this->champs[$nomtableliee];		
			 $nbcle	=	$this->nb_cles[$nomtableliee];
			 $nomtable = $nomtableliee;
			 
			 //echo '<br>$nbcle='.$nbcle;
			 //echo'<br>$this->tableau_type_zone_base[$nomtableliee]<pre>';
			 //print_r($this->tableau_type_zone_base[$nomtableliee]);

			  //echo"<br>NB CHAMPS =".count($tab_champ)."<br>";
			 if (is_array($matr)){
				$nb_col =count($matr[0]);
				foreach ($matr as $tab){
					$action = $tab[$nb_col-1];
					/*
					 * Mise à jour d'une table
					 */
					 if ($action == 'U'){
							$sql="UPDATE $nomtable SET ";			
							$pass = 0 ;
							for ($i=$nbcle ; $i < $nb_col-1; $i++){
															$virg = ',';
									if($pass==0){
											$virg = '';
									}
		
									if($tab[$i] or $tab[$i]=='0'){
											if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[$i]])=='int'){
													$sql .= " $virg  $tab_champ[$i] = $tab[$i]";
											}else{
													$sql .= " $virg  $tab_champ[$i] = ".$this->conn->qstr($tab[$i]);
											}
									}
									else{
											if(preg_match('/^CODE_TYPE.*$/',$tab_champ[$i])){
													$sql .= " $virg  $tab_champ[$i] = 255";
											}
											else{
													$sql .= " $virg  $tab_champ[$i] = NULL";
											}
									}
									$pass++;
							}
						
              if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[0]])=='int'){
						    	$sql .= " WHERE $tab_champ[0] = $tab[0]";
							}else{
									$sql .= " WHERE $tab_champ[0] = ".$this->conn->qstr($tab[0]);
							}                        
						for ($i=1 ; $i < $nbcle ; $i++){
								if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[$i]])=='int'){
										$sql .= " AND  $tab_champ[$i] = $tab[$i]"; 
								}else{
										$sql .= " AND  $tab_champ[$i] = ".$this->conn->qstr($tab[$i]); 
								}
						}
						//echo '<BR> ---UPDATE--- <BR>'.$sql.'<BR>';            	
						if ($this->conn->Execute($sql) === false){
							$GLOBALS['theme_data_MAJ_ok'] 	= false;
							print ' <br> error updating :<br>'.$sql.'<br>'; 
						}   
					}
				}                   
			}
		//}
		/////////   FIN DE TRAITEMENT DES UPDATE
	}
}
?>
