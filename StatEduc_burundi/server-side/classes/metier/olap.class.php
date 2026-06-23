<?php /** 
     * Classe permettant de -> gerer les tableaux croisé dynamique
     * @access public
     * @author Alassane
     * @version 1.1
    */
    class olap{   
        /**
         * Attribut : xdbtype
         *  Contient  le type de gestionnaire de base de données
         * @var array
         * @access public
	    */	
        public $xdbtype;
        
        /**
         * Attribut : xdbProvider
         *  Contient  la chaîne de connexion correspondant au provider
         * @var array
         * @access public
	    */	
        public $xdbprovider;
        
         /**
         * Attribut : xServerName
         *  Contient  le nom du serveur
         * @var array
         * @access public
	    */	
        public $xserverName;
        
        /**
         * Attribut : xdbSource
         *  Contient  le chemin de la source de données ( chemin du fichier ou
         * le nom de la base MSQL, MySQL, ou Oracle) le chemin du fichier est
         * valable pour les BDD Access ou les fichiers Cubes OLAP
         * @var array
         * @access public
	    */	
       
        public $xdbsource;
        
        /**
         * Attribut : xdbCatalogue
         *  Contient  le catalogue des Cubes sur le serveur OLAP ( la base de
         * données OLAP)
         * @var array
         * @access public
	    */	
        public $xdbcatalogue;
        
         /**
         * Attribut : xdbdatamember
         *  Contient  le cube courant
         * @var array
         * @access public
	    */	
        public $xdbdatamember;
        
         /**
         * Attribut : xdbUser
         *  Contient  le nom de l'utilisateur de la base de données
         * @var array
         * @access public
	    */	
        public $xdbuser;
        
         /**
         * Attribut : xdbPwd
         *  Contient  le mot de passe de l'utilisateur de la base de données
         * @var array
         * @access public
	    */	
        public $xdbpwd;
                         
        /**
         * Attribut : html
         *  Contient  la chaîne html correspondant
         * @var array
         * @access public
	    */	
        public $html;
        
         /**
         * Attribut : vbScript
         *  Contient  le script "vbScript" necessaire à l'établissement de la
         * connexion au pivottable
         * @var array
         * @access public
	    */	
        public $vbscript;
        
         /**
         * Attribut : source_cube
         *  Contient  le type de source (cube local ou serveur olap)
         * @var array
         * @access public
	    */	
        public $source_cube;
        
         /**
         * Attribut : nom_cube
         *  Contient  le nom du cube
         * @var string
         * @access public
	    */	
        public $nom_cube;
        
        /**
         * Attribut : id_cube  
         * Contient  l'identifiant du cube
         * @var array
         * @access public
	    */	
        public $id_cube;
        
        /**
         * Attribut : liste_bases_OLAP  
         * Contient  la liste des bases OLAP disponible sur le serveur
         * @var array
         * @access public
	    */	
        public $liste_bases_OLAP = array();
        
        /**
         * Attribut : liste_chaines
         * Contient la liste des chaines OLAP disponible sur le serveur
         * @var array
         * @access public
	    */
        public $liste_chaines = array();
        
        /**
         * Attribut : default_db
         * Contient la base OLAP par defaut sur le serveur
         * @var string
         * @access public
	    */
        public $default_db ;
        
        public function __construct($id_cube='',$nom_base='',$nom_cube='',$type_sgbd='',$nom_server=''){
            $this->conn  = $GLOBALS['conn'];
            $this->source_cube = $source_cube;
            $this->nom_cube = $nom_cube;
            $this->id_cube = $id_cube;            
           
            // Initialisation du type SGBD : OLAPFILE ou un SREVEUR OLAP
			$this->xdbtype =$type_sgbd;
		    if($nom_base<>'' && $nom_cube<>'' && $type_sgbd=='OlapServer'){
				
				// Cas d'un serveur olap                
                $sql ='SELECT * FROM DICO_OLAP_AUTRE_CONNEXION WHERE SGBD='.'\'OlapServer\'';
                $result =  $GLOBALS['conn_dico']->GetAll($sql);   
                $this->default_db=$nom_base;
                //Recupération des information sur le serveur 
                if($nom_server=='')	$this->xdbsource    =     $result[0]['SERVEUR'];
				else $this->xdbsource    =     $nom_server; 
                $this->xdbcatalogue  =    $nom_base;
                $this->xdbdatamember    = $nom_cube;
				$this->xdbquery =   '';
				$this->xdbuser  =   '';
				$this->xdbpwd   =   '';
				if (isset($result[0]['PROVIDER']) && $result[0]['PROVIDER']<>"")
					$this->xdbprovider =$result[0]['PROVIDER'];
				else
					$this->xdbprovider = 'MSOLAP';
			
			}elseif($nom_base=='' && $nom_cube=='' && $type_sgbd=='OlapServer'){
				$this->get_param($this->id_cube,'');
			}elseif($type_sgbd=='OlapFile' && $nom_cube<>''){
				// Lecture des parametètres
				$this->get_param($this->id_cube,'cas_menu');
            }elseif($type_sgbd=='OlapFile'){
				// Lecture des parametètres
				$this->get_param($this->id_cube,'cas_liste');
            }
            // Construction de la chaîne html pour l'affichage du pivottable         
            $this->set_html();
            
        }
        
        function __wakeup(){
            $this->conn  = $GLOBALS['conn'];
        }
        
        
        /**
        *   Function permet la lecture des paramètres pour l'initialisation des attributs de la classe
        */
        public function get_param($id_cube,$cas){      

            // Cas d'un cube local
            
            if ($this->xdbtype=='OlapFile'){ 
                    // Cas d'un fichier Olap physique            
                    /*$sql = 'SELECT ASSOCIATE_OLAP_FILE FROM DICO_OLAP WHERE ID_OLAP='.$id_cube;                  
                    $result =  $GLOBALS['conn_dico']->GetAll($sql);                    
                    if (is_array($result) && count($result)){
                        $this->nom_cube = $result[0]['ASSOCIATE_OLAP_FILE'];
                    }
                    else{
                        if (!(isset($this->nom_cube) && $this->nom_cube<>''))
                            $this->nom_cube='';
                    }*/
                    if($cas=='cas_liste') $this->nom_cube = $_SESSION['liste_local_cubes'][$id_cube]['NAME'];
					if  ($this->nom_cube<>'')                       
                        if (preg_match('/.cub/',$this->nom_cube))
                            $this->xdbsource    =   $GLOBALS['SISED_SHARED_PATH_LAN'].$this->nom_cube; 
                        else
                            $this->xdbsource    =   $GLOBALS['SISED_SHARED_PATH_LAN'].$this->nom_cube.'.cub';
                    $this->xdbquery =   '';
                    $this->xdbuser  =   'Guest';
                    $this->xdbpwd   =   '';
            }
            else{
                // Cas d'un serveur olap                
                //$this->xdbtype      =   'OlapServer';
                $sql ='SELECT * FROM DICO_OLAP_AUTRE_CONNEXION WHERE SGBD='.'\'OlapServer\'';
                $result =  $GLOBALS['conn_dico']->GetAll($sql);   
                if (isset($result[0]['DB']) && $result[0]['DB']<>'')
                    $this->default_db = $result[0]['DB'];
                else
                    $this->default_db='';
                
                //Recupération des information sur le serveur 
                if (is_array($result)){                
                    $this->xdbsource    =     $result[0]['SERVEUR']; 
                    if (isset($result[0]['DB']) && $result[0]['DB']<>''){
                        $this->xdbcatalogue  =    $result[0]['DB'];
                        $this->set_olap_base(); 
                        if (is_array($this->liste_bases_OLAP)){
                            foreach($this->liste_bases_OLAP as $base){    
                                foreach ($this->liste_chaines[$base] as $cube){
                                    if(in_array($this->id_cube, $cube)){                                        
                                        $this->xdbdatamember    = $cube['NAME']  ;
                                        break;
                                    }
                                }
                              
                            }
                        }
                    }
                    else {                        
                        // Recherche de la base olap par défaut                                                
                        $this->set_olap_base();                         
                        if (is_array($this->liste_bases_OLAP)){
                            foreach($this->liste_bases_OLAP as $base){    
                                foreach ($this->liste_chaines[$base] as $cube){
                                    if(in_array($this->id_cube, $cube)){
                                        $this->xdbcatalogue     =   $base;
                                        $this->xdbdatamember    = $cube['NAME']  ;
                                        break;
                                    }
                                }
                              
                            }
                        }
                      
                    }
                   
                    $this->xdbquery =   '';
                    $this->xdbuser  =   '';
                    $this->xdbpwd   =   '';
                }
            
            }
            switch ($this->xdbtype){
                    case 'OlapFile':
                        $this->xdbprovider ='msolap';
                        break;
                    case 'OlapServer':
                        if (isset($result[0]['PROVIDER']) && $result[0]['PROVIDER']<>"")
						 	$this->xdbprovider =$result[0]['PROVIDER'];
						else
							$this->xdbprovider ='MSOLAP';
                        break;
            }             
            
        }
                
        /**
        *   Function permet d'initialiser la  connexion  à la source de données à travers un script vbcript
        */
        public function set_vbscript() {                    
            $script.='sub Setup_PT() 
                 Dim adocon
                 Dim Rst
                 Dim sConnection
                 Dim strChemin
                 Dim strquery                                 
                 Set adocon = CreateObject("ADODB.Connection") ';
                
                switch ($this->xdbtype){
                    case 'OlapFile':
                         
						 /*//A activer pour acces aux cubes locaux à travers internet 
						 $script.="\n";
                         $script.='document.location.href ="'.$this->xdbsource.'"';
                         $script.="\n";
						 *///Fin à activer
						 
						 //A desactiver pour acces aux cubes locaux à travers internet
						 $script.="\n";
                         $script.='strChemin ="'.$this->xdbsource.'"';
                         $script.="\n";
                         $script.=' sConnection ='.'"DATA SOURCE="& strChemin &";PROVIDER='.$this->xdbprovider.';USER ID='.$this->xdbuser.';PASSWORD='.$this->xdbpwd.';INITIAL CATALOG=;"';   
                         $script.="\n";
						 $script.=' adocon.ConnectionString = sConnection ';
						 $script.="\n";
						 $script.=' adocon.Open ';
						 $script.="\n";
						 $script.=' pivottable1.ConnectionString = sConnection ' ;
						 $script.="\n";
						 $script.=' pivottable1.DataMember = adocon.DefaultDatabase ';  
                         $script.="\n";                         
                         //Fin à desactiver
						 
                        break; 
                    case 'OlapServer':
                         $script.="\n";
                         $script.='strChemin ="'.$this->xdbsource.'"';
                         $script.="\n";                        
                         $script.=' sConnection ='.'"DATA SOURCE="& strChemin &";PROVIDER='.$this->xdbprovider.';USER ID='.$this->xdbuser.';PASSWORD='.$this->xdbpwd.';INITIAL CATALOG='.$this->xdbcatalogue.';"';   
                         $script.="\n";
						 
						 $script.=' adocon.ConnectionString = sConnection ';
						 $script.="\n";
						 $script.=' adocon.Open ';
						 $script.="\n";
						 $script.=' pivottable1.ConnectionString = sConnection ' ;
						 $script.="\n";
						
						 $script.=' pivottable1.DataMember ="'. $this->xdbdatamember.'"';  
                         $script.="\n"; 
                        
                        break;  
                }            
                 
                $script.=' end sub ';
                $script.="\n";
                $this->vbscript=$script;
        }
        
         /**
        *   Function permet de reconstituer la chaîne html
        */
        public function set_html(){
             
             // Construction du script vbscript pour l'établissement de la connexion au pivottable    
            $this->set_vbscript();
            $html=' </br></br> <HTML><HEAD> <SCRIPT LANGUAGE="VBSCRIPT">';
            $html.="\n";
            $html.=$this->vbscript;
            $html.="</SCRIPT></HEAD> ";
            $html.="\n";          
            $html.='<BODY ONLOAD = "Setup_PT()">
              </br></br>
              <div id="Pivot" align=center >                
                <object id="Pivottable1" classid="CLSID:0002E55A-0000-0000-C000-000000000046"> 
                </object>
              </div>  
            </BODY>            
            </HTML> ';
           	/*
            $html=' </br></br> <HTML><HEAD> <SCRIPT LANGUAGE="VBSCRIPT">';
            $html.="\n";
            $html.=$this->vbscript;
            $html.="</SCRIPT></HEAD> ";
            $html.="\n";          
            $html.='<BODY ONLOAD = "Setup_PT()">
              </br></br>
              <div id="Pivot" align=center >                
                <object id="Pivottable1" classid="CLSID:0002E552-0000-0000-C000-000000000046"> 
                </object>
              </div>  
            </BODY>            
            </HTML> ';
            */
            
            $this->html=$html;            
           
        }
        
        /**
        *   Function permet de recuperer les bases olap d'un serveur
        */
        public function set_olap_base(){
        
        if($this->default_db <> '') {
            $req = 'SELECT * FROM DICO_OLAP_OBJ_SERVEUR WHERE BASE_OLAP =\''.$this->default_db.'\'';
        }else{
            $req = "SELECT * FROM DICO_OLAP_OBJ_SERVEUR";
        }       
        $res = $GLOBALS['conn_dico']->GetAll($req);
        if(count($res)>0){
            $liste_cubes = array();
            $tab_cube = array();
            if (is_array($res )){
                foreach($res as $rs) {
                    $tab_cube['ID'] = $rs['ID_CUBE'];
                    $tab_cube['NAME'] = $rs['NOM_CUBE'];
                    $liste_cubes[$rs['BASE_OLAP']][] = $tab_cube;                             
                }
            }
        }
        $this->liste_chaines =  $liste_cubes;  
                
        if (is_array($liste_cubes)){
            foreach($liste_cubes as $base => $tab_cub){
                if(!in_array($base, $this->liste_bases_OLAP)){
                    $this->liste_bases_OLAP[] = $base ;
                }
            }
        }


    }       
          
}
?>
