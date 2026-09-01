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
    class get_obj_serveur{   
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
        
        /**
         * Attribut : mysql_option
         * <pre>
         *  Contient l'option mysql
         * </pre>
         * @var array
         * @access public
	    */	
        
        public $mysql_option;
        
        /**
         * Attribut : cnn_string
         * <pre>
         *  Contient la chaine de connexion à la base dico
         * </pre>
         * @var array
         * @access public
	    */	
        
        public $cnn_string;
        
        /**
         * Attribut : conn
         * <pre>
         *  Contient la connexion à la base dico
         * </pre>
         * @var array
         * @access public
	    */
        
        public $conn;
        

        
        public function __construct($serveur){
            $this->conn  = $GLOBALS['conn_dico'];
            $this->xserverName=$serveur;
            $this->set_html();
            
        }
        
        public function set_vbscript() {       
            
        $this->xdbtype=$this->conn->databaseType;
        $this->xdbsource = $this->conn->host;
        $this->xdbcatalogue = $this->conn->databaseName;
        $this->xdbuser = $this->conn->user;
        $this->xdbpwd = $this->conn->password;
        
        switch ($this->xdbtype){
                    case 'access':
                        //$this->xdbprovider ='Microsoft.Jet.OLEDB.4.0'; //pilote oledb : Erreur : pilote isam introuvable
                        $this->xdbprovider ='Driver={Microsoft Access Driver (*.mdb, *.accdb)}'; //pilote odbc
                        $pos=strpos($this->conn->host,';');                    
                        $this->xdbsource = substr($this->conn->host,$pos + 5); //pour extraire juste le chemin de la base
                        if($_SERVER['SERVER_NAME']=='localhost')
                            $this->cnn_string = $this->xdbprovider.';Dbq='.$this->xdbsource;
                        else
                        {
                            $this->xdbsource = preg_replace('/:/','',$this->xdbsource); 
                            $this->cnn_string = $this->xdbprovider.';Dbq=//'.$_SERVER['SERVER_NAME'].'/'.$this->xdbsource;
                        }
                        break;
                    case 'mssql':
                        $this->xdbprovider ='SQLOLEDB';
                        $this->cnn_string = 'Provider='.$this->xdbprovider.';Data Source='.$this->xdbsource.';Initial Catalog='.$this->xdbcatalogue.';User ID='.$this->xdbuser.';Password='.$this->xdbpwd;
                        break;
                    Case 'mysql':
                        //$this->xdbprovider ='{MySQL ODBC 3.51 Driver}';
                        $this->xdbprovider = '{mysql}';
                        //$this->mysql_option = 16386;
                        $this->cnn_string = 'Driver='.$this->xdbprovider.';database='.$this->xdbcatalogue.';server='.$this->xdbsource.';uid='.$this->xdbuser.';pwd='.$this->xdbpwd.';option=16386';                    
                        break;
                    case 'oracle':   
                        //$this->xdbprovider ='OraOLEDB.Oracle';
                        $this->xdbprovider ='msdaora';
                        $this->cnn_string = 'Driver='.$this->xdbprovider.';server='.$this->xdbsource.';database='.$this->xdbcatalogue.';User Id='.$this->xdbuser.';Password='.$this->xdbpwd;
                        break;              
        }
        
        
        $req = 'SELECT * FROM DICO_OLAP_AUTRE_CONNEXION WHERE SGBD=\'OlapServer\''; 
        $res = $this->conn->GetAll($req);    
		//echo $req.'<pre>';
        //print_r($res);
        //die();       
         
        if(is_array($res) && count($res))
        {   
            $this->xserverName=$res[0]['SERVEUR'];        
            
            $script="";
            $script.='<SCRIPT LANGUAGE="VBSCRIPT">';
            $script.="\n";
            $script.='sub maj_dico_obj_serveur() 
                Dim objConn
                Dim objRS
                Dim objCatalog
                Dim objCubeDef
                Dim indCube
                Dim strTab(100)
                Dim i
                dim nb_cubes
                dim j
                Set objConn = CreateObject("ADODB.Connection")
                Dim cnx
                Dim rs
                Set cnx = CreateObject("ADODB.Connection")';        
                    
            $script.="\n";
			if (isset($res[0]['PROVIDER']) && $res[0]['PROVIDER']<>"")
				$script.='objConn.Open "Data Source='.$this->xserverName.';Provider='.$res[0]['PROVIDER'].'"';
			else
				$script.='objConn.Open "Data Source='.$this->xserverName.';Provider=MSOLAP"';
            $script.="\n";     
            $script.='set objRS = objConn.OpenSchema(1)';
            $script.="\n"; 
            $script.='cnx.ConnectionString = "'.$this->cnn_string.'"';
            $script.="\n";
            $script.='cnx.Open';
            $script.="\n";
            $script.="cnx.Execute \"DELETE FROM DICO_OLAP_OBJ_SERVEUR\"";
            $script.="\n";
            $script.="Set rs = CreateObject(\"ADODB.Recordset\")";
            $script.="\n";
            $script.="rs.Open \"DICO_OLAP_OBJ_SERVEUR\", cnx, 2, 3";
            $script.="\n";
            $script.="i=0";
            $script.="\n";    
            $script.="do while (i < 100)";
            $script.="\n";            
            $script.="strTab(i) = \"\"";
            $script.="\n"; 
            $script.="i=i+1"; 
            $script.="\n";            
            $script.="loop";
            $script.="\n";
            
            $script.="i = 0";
            $script.="\n";
            
            $script.="Do Until objRS.EOF";
            $script.="\n";
            
                $script.="strTab(i) = objRS.Fields(\"Catalog_Name\")";
                $script.="\n";
                
                $script.="i = i + 1";
                $script.="\n";
                
                $script.="objRS.MoveNext";
                $script.="\n";
            
            $script.="Loop";
            $script.="\n";
            
            $script.="indCube = 0";
            $script.="\n";            
            $script.="i=0";
            $script.="\n";
            
            $script.="do while (i < 100)";
            $script.="\n";
            
                $script.="If strTab(i) <> \"\" Then";
                $script.="\n";
                
                $script.="objConn.DefaultDatabase = strTab(i)";
                $script.="\n";                
                $script.="Set objCatalog = CreateObject(\"ADOMD.Catalog\")";
                $script.="\n";
                $script.="Set objCatalog.ActiveConnection = objConn";
                $script.="\n";
                $script.="nb_cubes = objCatalog.CubeDefs.Count";
                $script.="\n";
                $script.="j = 0";
                $script.="\n";
                
                $script.='do while (j < nb_cubes)';
                    
                    $script.="\n";
                    $script.="rs.AddNew";
                    $script.="\n";
                    $script.="rs.Fields(\"BASE_OLAP\") = objConn.DefaultDatabase";
                    $script.="\n";
                    $script.="rs.Fields(\"ID_CUBE\") = indCube";
                    $script.="\n";
                    $script.="rs.Fields(\"NOM_CUBE\") = objCatalog.CubeDefs(j).Name";
                    $script.="\n";
                    $script.="rs.Update";
                    $script.="\n";
                    $script.='indCube = indCube + 1';
                    $script.="\n";   
                    $script.='j = j+1';
                    $script.="\n";   
                    
                $script.='loop';
                $script.="\n";
                
                $script.='Set objCatalog = Nothing';
                $script.="\n";
                
                $script.="End If";
                $script.="\n";
                
                $script.="i=i+1";
                $script.="\n"; 
                
            $script.="Loop";
            $script.="\n";
            
            $script.="rs.Close";
            $script.="\n";
            $script.='cnx.Close';
            $script.="\n";            
            $script.='objRS.Close';
            $script.="\n";
            $script.='objConn.Close';
            $script.="\n";
            $script.='end sub'; 
            $script.="\n";     
            $script.="</SCRIPT>";
            $script.="\n"; 
            $this->vbscript=$script;
        }        
        
    }
    public function set_html(){
             
         // Construction du script vbscript pour l'établissement de la connexion au pivottable    
         $this->set_vbscript();
         
    }
}
    
        
?>
