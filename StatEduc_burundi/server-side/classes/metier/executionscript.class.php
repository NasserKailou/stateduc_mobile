<?php /**
     * Classe executionscript permet d'éxecuter des requêtes (Etat basé sur plusieurs tables)
     * Ces requêtes intermédiares permettent de créer une table temporraire 
     * qui permettra de stocker les données provenant d'autres tables
	 * @package default
     * @access  public
     * @author Alassane, Touré, Yacine
     * @version 1.4	 
    */ 
class executionscript {

		/**
		* Attribut : code_systeme
		* Permet de se positionner sur le secteur courant
		* @var numeric 
		* @access public
		*/   
		public $code_systeme;
		/**
		* Attribut : id_report
		* reference du report
		* @var numeric 
		* @access public
		*/       	
		public $id_report;
        
        /**
		* Attribut : conn
		* Variable de connexion à la base de données
		* @var ADODB.connection 
		* @access public
		*/   
		public $conn;
		
        /**
		* Attribut : queries
		* Tableau contenant les requêtes à exécuter
		* @var array 
		* @access public
		*/  
        public $queries   =   array();        
        /**
		* Constructeur de la classe: chargement de la liste des requêtes,
		* exécution des requête
		* @access public
		* @param tableau param tableau contenant l'identifiant de l'état, le système d'enseignement courant et la connxion courante
		*/
		public function __construct($param){
            // Initialisation de certains attributs de la classe
            $this->id_report        =   $param["ID_REPORT"];
            $this->code_systeme     =   $param["ID_SYSTEME"];
            $this->conn             =   $GLOBALS['conn'];
            
            // Récupération de la série de requête sql associés au report
            $this->get_query();
            
            // Exection de la série des requête sql
            $this->run_query();
        }
        
		/**
		* Activation de la connexion
		* @access private
		* 
		*/
		public function __wakeup(){
        	$this->conn     =   $GLOBALS['conn'];       
        }
        
		/**
		* Construction et exécution de la requête permettant de charger la liste des requêtes intermédiaires liées à l'état
		* @access public
		* 
		*/
		public function get_query(){            
            // Requete de selection de la liste des requetes sql associés au report
            $requete    ='SELECT ID_QUERY, SQL_QUERY, ORDRE_EXECUTION,TYPE_QUERY FROM DICO_QUERY WHERE ID='.$this->id_report;
            $requete   .=' ORDER BY ORDRE_EXECUTION ';
            
            // Gestion des erreurs lors de l'exécution de la requête sql
            try{            
                $this->queries = $GLOBALS['conn_dico']->GetAll($requete);   
            }        
            catch (Exception $e){
                $erreur=new erreur_manager($e,$requete);
            }
             
        }
        
        /**
		* Exécution des requêtes en tenant compte du type de la requête
		* @access public
		* 
		*/
		public function run_query(){
            //Instanciation d'un dictionnaire
            $dict = NewDataDictionary($this->conn);

            // Exécution de la série des requétes
            
            if (is_array($this->queries)){
                foreach ($this->queries as $query){
                    // parcours de chaque requete et exécution
                    if($query['TYPE_QUERY']==1){
                        if (trim($query['SQL_QUERY'])<>''){
                            // gestion des erreurs d'exécution des requete
                            unset($quer);
                            $quer[] = $query['SQL_QUERY'];
                            if ($dict->ExecuteSQLArray($quer)<> 2)   
                                echo '<br> error in the query :'.$query['SQL_QUERY'].'<br>';                      
                            }
                    }else{
                    $sql = str_replace('$code_annee',$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_SESSION['annee'],$query['SQL_QUERY']);
                    $sql = str_replace('$code_filtre',$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].'='.$_SESSION['filtre'],$sql);
					//echo '<br>'.$sql.'<br>';
                    if (trim($sql)<>''){
                            // gestion des erreurs d'exécution des requete
                            try{
                                if($this->conn->execute($sql)==false)   
                                    print '<BR>error inserting: '.$this->conn->ErrorMsg().'<BR>';                        
                            }
                            catch (Exception $e){
                                $erreur=new erreur_manager($e,$sql);
                            }
                        }
                    }
                }
            }
        }
}
?>
