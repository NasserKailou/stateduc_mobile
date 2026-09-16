<?php /** 
		* Classe qui permet l'accés au contenu des enseignants enregistrés
		* Elle permet la récupération des enseignants existants et de leur ordonnancement,
		* Mais s'occupe également du charger la classe Métier selon l'enseignant en cours de traitement,
		* @access public
	*/	
	class teacher_manager {
    
		
		/**
		 * Attribut : pos_teach
		 * 	Position Enseigant courant
		 */  
		 public $pos_teach;
    	
		/**
		 * Attribut : conn
		 * 	Connexion à la Base
		 * @var array
		 * @access public
		 */     
		public $conn;
    	
		/**
		 * Attribut : list
		 * 	Stocke la liste des enseignants existants dans la base
		 * @var array
		 * @access public
		 */   
		public $list;
    	
		/**
		 * Attribut : list_sch_teach
		 * 	Stocke la liste des etablissements des enseignants existants dans la base
		 * @var array
		 * @access public
		 */   
		public $list_sch_teach;
		
		/**
		* Attribut : id_teacher
		* 	Correspond au ID de l'enseignant
		* @var numeric
		* @access public
		*/   
		public $id_teacher;
    	
		/**
		* Attribut : teachers_sql_data
		* 	le nom des requetes sql utilisée pr extraire les données enseignants
		* @var string
		* @access public
		*/   
		public $teacher_sql_data = array();

		/**
		 * METHODE :  __construct()
		 * 	Constructeur de la classe :
		 * @access public
		 */ 
		public function __construct() {
	
			$this->conn = $GLOBALS['conn'];
		
		}
		
		public function init(){
			$this->charger_teacher();
			$this->set_teacher_courant();
		}
		
		/**
		 * 
		 * @access public
		 */	
		function __wakeup(){
			$this->conn	= $GLOBALS['conn'] ;
		}
		
		/**
		* METHODE : get_cle_max()
		* <pre>
		*  recherche du code max ds la table enseignant
		* </pre>
		* @access public
		* 
		*/
		public function get_cle_max(){
			$max_return = 0;
			$sql = ' SELECT  MAX('.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].') as MAX_INSERT FROM  '.$GLOBALS['PARAM']['ENSEIGNANT'];       
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
		 * METHODE :  recherche_teacher_prec()
		 * 	Permet de rechercher à partir de la liste des enseignants
		 *	celui qui précède l'enseignant en cours
		 * @access public
		 */ 
		public function recherche_teacher_prec() {
			// Retourne le teacher précédent le teacher_courant à partir du tableau des teachers triés
			foreach ($this->list as $k => $teacher) {
				if ($this->id_teacher == $teacher['ID_TEACHER']) {
					if(isset($this->list[$k-1])) {
						return $this->list[$k-1]['ID_TEACHER'];
					} else {
						return false;
					}
				}
			}
    	}

		/**
		 * METHODE :  recherche_teacher_suiv()
		 * 	Permet de rechercher à partir de la liste des teachers
		 *	celui qui succéde au teacher en cours
		 * @access public
		 */ 
		public function recherche_teacher_suiv() {
			// Retourne le teacher suivant le teacher_courant à partir du tableau des teachers triés
			foreach ($this->list as $k => $teacher) {
				if ($this->id_teacher == $teacher['ID_TEACHER']) {
					if(isset($this->list[$k+1])) {
						return $this->list[$k+1]['ID_TEACHER'];
					} else {
						return false;
					}
				}
			}
		}

    	/**
		 * METHODE :  charger_teacher()
		 * 	Permet charger les teachers enregistrés
		 * @access public
		 */
		public function charger_teacher() {
			if (isset($_SESSION['secteur'])){
				$secteur = $_SESSION['secteur'];
			}
			// Lecture des infos du teacher dans les tables concernées de la base
			//echo 'pasée alasco <br>';
		   	if (!isset($langue) || !isset($secteur)) {
				$requete = "SELECT * FROM PARAM_DEFAUT;";
				// Traitement Erreur Cas : GetAll / GetRow
				try {
					$paramdefaut = $GLOBALS['conn_dico']->GetRow($requete);
					if(!is_array($paramdefaut)){                    
						throw new Exception('ERR_SQL');  
					} 
					if (!isset($secteur)) $secteur  =   trim($paramdefaut['CODE_SECTEUR']);
				}
				catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow		
			}
				
          	$requete = str_replace(' AS '.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'],' AS ID_TEACHER',$this->teacher_sql_data[$GLOBALS['PARAM']['ENSEIGNANT']]);     
			$requete = str_replace(' AS '.$GLOBALS['PARAM']['PRENOMS_ENSEIGNANT'],' AS FIRST_NAME',$requete);     
			$requete = str_replace(' AS '.$GLOBALS['PARAM']['NOM_ENSEIGNANT'],' AS LAST_NAME',$requete);     
	
			$requete_sch_teach = str_replace(' AS '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'],' AS CODE_SCHOOL',$this->teacher_sql_data[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']]);     
			$requete_sch_teach = str_replace(' AS '.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'],' AS ID_TEACHER',$requete_sch_teach);     
			
			// récupération du résultat dans un tableau       
			//echo $requete;
        	try {
				$result_teacher	= $GLOBALS['conn']->GetAll($requete);
				if(!is_array($result_teacher)){                    
					throw new Exception('ERR_SQL');  
				}
				$this->list = $result_teacher;	
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}
            
			try {
				$result_sch_teach	= $GLOBALS['conn']->GetAll($requete_sch_teach);
				if(!is_array($result_sch_teach)){                    
					throw new Exception('ERR_SQL');  
				}
				
				foreach($result_sch_teach as $sch_teach){
					$this->list_sch_teach[$sch_teach['ID_TEACHER']] = $sch_teach['CODE_SCHOOL'];
				}
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}  
    	}

		/**
		 * METHODE :  set_teacher_courant()
		 * 	permet de placer convenablement le teacher courant
		 * @access public
		 */
		public function set_teacher_courant() {
    
			if($_GET['val'] == 'new_teach') { // Lors de la création d'un enseignant
				$this->id_teacher  = $this->recherche_teacher_def();
			}elseif(isset($_GET['id_teacher'])) { //si $_GET['id_teacher'] existe alors le teacher courant est $_GET['id_teacher']
				$this->id_teacher    = $_GET['id_teacher'];
			}elseif(isset($_SESSION['id_teacher'])) { //si $_SESSION['id_teacher'] existe alors le teacher courant est $_SESSION['id_teacher']
				$this->id_teacher    = $_SESSION['id_teacher'];
			}else{
				$this->id_teacher  = $this->recherche_teacher_def();
			}

			foreach($this->list as $ord_teach => $teach) {
				if($teach['ID_TEACHER'] == $this->id_teacher) {
					$this->pos_teach 	= $ord_teach;
					break;
				}
			}
	    }

		/**
		 * METHODE :  recherche_teacher_def()
		 * 	Retrouve le teacher par défaut, le tout premier de la Liste 
		 * @access public
		 
		 */
		public function recherche_teacher_def() {
			return $this->list[0]['ID_TEACHER'];
		}
		
		/**
		 * METHODE :  get_lib_long_teacher (id_teacher)
		 * 	Retrouve le nom et prenom du teacher id_teacher
		 *	pour la langue en paramètre
		 * @access public
		 * @param array id_teacher : un teacher donné
		 */
		public function get_lib_long_teacher ($id_teacher){
			
			foreach($this->list as $ord_teach => $teach) {
				if($teach['ID_TEACHER'] == $id_teacher) {
					$lib = $this->list[$ord_teach]['FIRST_NAME'].' '.$this->list[$ord_teach]['LAST_NAME'];
					break;
				}
			}
			
			return $lib; 								 
		}
}

?>
