<?php /** 
     * Classe obj serveur
     * <pre>
     * Classe permettant de 
     * -> recuperer les bases et les cubes d'un serveur
     * </pre>
     * @access public
     * @author Hebié
     * @version 1.1
    */
    class obj_serveur{   
        /**
         * Attribut : xdbtype
         * <pre>
         *  Contient le type de gestionnaire de base de données
         * </pre>
         * @var array
         * @access public
	    */	
        public $xdbtype;
        
        /**
         * Attribut : xdbProvider
         * <pre>
         *  Contient la chaîne de connexion correspondant au provider
         * </pre>
         * @var array
         * @access public
	    */	
        public $xdbprovider;
        
         /**
         * Attribut : xServerName
         * <pre>
         *  Contient le nom du serveur
         * </pre>
         * @var array
         * @access public
	    */	
        public $xserverName;
        
        /**
         * Attribut : xdbSource
         * <pre>
         *  Contient le chemin de la source de données ( chemin du fichier ou le nom de la base MSQL, MySQL, ou Oracle)
         * le chemin du fichier est valable pour les BDD Access ou les fichiers Cubes OLAP
         * </pre>
         * @var array
         * @access public
	    */	
       
       public $xdbsource;
        
        /**
         * Attribut : xdbCatalogue
         * <pre>
         *  Contient le catalogue des Cubes sur le serveur OLAP ( la base de données OLAP)
         * </pre>
         * @var array
         * @access public
	    */	
        public $xdbcatalogue;
        
         /**
         * Attribut : xdbdatamember
         * <pre>
         *  Contient le cube courant
         * </pre>
         * @var array
         * @access public
	    */	
        public $xdbdatamember;
        
         /**
         * Attribut : xdbUser
         * <pre>
         *  Contient le nom de l'utilisateur de la base de données
         * </pre>
         * @var array
         * @access public
	    */	
        public $xdbuser;
        
         /**
         * Attribut : xdbPwd
         * <pre>
         *  Contient le mot de passe de l'utilisateur de la base de données
         * </pre>
         * @var array
         * @access public
	    */	
        public $xdbpwd;
                         
         /**
         * Attribut : vbScript
         * <pre>
         *  Contient le script "vbScript" necessaire à l'établissement de la connexion au pivottable
         * </pre>
         * @var array
         * @access public
	    */	
        public $vbscript;
        
        public function __construct($serveur){
            $this->xserverName=$serveur;
            $this->set_html();
            
        }
        
        public function get_bases(){        
        //
        }
        public function get_cubes($base){         
        //
        }

        public function set_vbscript() {                    
            
            $script ="";
            $script.='<SCRIPT LANGUAGE="VBSCRIPT">';
            $script.="\n";
            $script.='sub Connection() 
                Dim objConn
                Dim objRS
                Dim objCatalog
                Dim objCubeDef
                Dim listeBases
                Dim listeCubesBase
                Dim indBase
                Dim indCubeBase
                Set objConn = CreateObject("ADODB.Connection")
                Set objCatalog = CreateObject("ADOMD.Catalog")
                Set objCubeDef = CreateObject("ADOMD.CubeDef")';
                
                $script.="\n";
                $script.='objConn.Open "Data Source='.$this->xserverName.';Provider=MSOLAP"';
                $script.="\n";     
                $script.='set objRS = objConn.OpenSchema(adSchemaCatalogs)';
                $script.="\n";  
                
                $script.='indBase=0';
                $script.="\n";  
                
                $script.='Do Until objRS.EOF';
                
                    $script.="\n";
                    $script.='listeBases(indBase) = objRS.Fields("Catalog_Name")';
                    $script.="\n";
                    $script.='indBase = indBase + 1';
                    $script.="\n";                     
                    $script.='objConn.DefaultDatabase = objRS.Fields("Catalog_Name")';
                    $script.="\n";
                    $script.='set objCatalog.ActiveConnection = objConn';
                    $script.="\n";
                    
                    $script.='indCubeBase = 0';
                    $script.="\n"; 
                    
                    $script.='For Each objCubeDef In objCatalog.CubeDefs';
                        
                        $script.="\n";
                        $script.='listeCubesBase(indBase)(indCubeBase) = objCubeDef.name';
                        $script.="\n";  
                        $script.='indCubeBase = indCubeBase + 1';
                    
                    $script.="\n";                     
                    $script.='Next objCubeDef';
                    $script.="\n";                    
                
                $script.='objRS.MoveNext';
                $script.="\n";
                $script.='Loop';                
                $script.="\n";
                
                $script.='objRS.Close';
                $script.="\n";
                $script.='objConn.Close';
                $script.="\n";
                $script.='end sub'; 
                $script.="\n";
                $script.="</SCRIPT>";

                $this->vbscript=$script;
        }
        
        public function set_html(){
             
             // Construction du script vbscript pour l'établissement de la connexion au pivottable    
             $this->set_vbscript();
        }
    }
    
    $test_script=new obj_serveur('SERVEUR');
    echo $test_script->vbscript;
        
?>
