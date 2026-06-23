<?php /** 
     * Classe olap_create_cube générique
     * Classe permettant de creer des cubes locaux
     * @access public
     * @authors Hébié et alasssane
     * @version 1.1
    */
    class olap_create_cube{   
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
        public $xservername;
        
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
         * Attribut : xdbquery
         *  Contient  la requete sql
         * @var array
         * @access public
	    */	
        public $xdbquery;
        
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
        public $html=array();
        
         /**
         * Attribut : vbScript
         *  Contient  le script "vbScript" necessaire à l'établissement de la
         * connexion au pivottable
         * @var array
         * @access public
	    */	
        public $vbscript=array();
        
         /**
         * Attribut : liste_cubes
         *  Contient  la liste des id cubes à générer
         * @var array
         * @access public
	    */	
        public $liste_cubes=array();
        
         /**
         * Attribut : liste_file_cubes
         *  Contient  la liste des nom de fichier de cubes à générer
         * @var array
         * @access public
	    */	
        public $liste_file_cubes=array();
        
         /**
         * Attribut : strDataSource
         * le  chemin complet du fichier cube
         * @var array
         * @access public
	    */	
        public $strDataSource;
              
        public function __construct($id_cubes){
            $this->conn  = $GLOBALS['conn'];
            $this->liste_cubes = $id_cubes;
            
            // Appel de la génération de cube         
            $this->generer_cube();            
        }
        
        function __wakeup(){
            $this->conn  = $GLOBALS['conn'];
        }       
        
		/**
		* Prepare a partir du dico et affecte a la variable html la chaine
		* contenant le script 'vbscript' pour la creation des cubes locaux
		* @access public
		*/
        public function set_vbscript() { 
        
			foreach ($this->liste_cubes as $id_cube){        
				
				$req_tab_fait = "SELECT * FROM ".$_SESSION['tab_cubes_faits'][$id_cube];
				$res_tab_fait = $GLOBALS['conn']->GetAll($req_tab_fait);

				if(is_array($res_tab_fait) && count($res_tab_fait)>0){
					
					//requête pour la construction de la clause CREATECUBE INSERTINTO                
				   
					$req_create_un ='SELECT DICO_OLAP_CHAMP.NOM_CHAMP AS CHAMP, DICO_OLAP_CHAMP.FONCTION AS FONCTION, DICO_OLAP_CHAMP.FORMAT AS FORMAT, 
						DICO_OLAP_CHAMP.EXPRESSION AS EXPRESSION, DICO_OLAP_CHAMP.ALIAS AS ALIAS_CHAMP, DICO_OLAP_CHAMP.ORDRE_DIM AS ORDRE_DIM, DICO_OLAP_CHAMP.ALL_LEVEL AS ALL_LEVEL,
						DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP AS TYPE_CHAMP, DICO_OLAP_DIMENSION.LIBELLE_DIMENSION AS DIMENSION,
						DICO_OLAP.USE_CURRENT_DB_CNX AS BOOL_CURRENT_CNX, DICO_OLAP.ASSOCIATE_OLAP_FILE AS OLAP_FILE, DICO_OLAP.THEME_NAME AS NOM_CUBE, DICO_OLAP.TABLE_FAITS AS FAITS,
						DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE, 
						DICO_OLAP_CHAMP.ORDRE_OLAP AS ORDRE_OLAP
					FROM DICO_OLAP, DICO_OLAP_CHAMP, DICO_OLAP_TYPE_CHAMP, DICO_OLAP_DIMENSION, DICO_OLAP_TABLE_MERE
					WHERE (((DICO_OLAP.ID_OLAP)='.$id_cube.') 
						AND ((DICO_OLAP_CHAMP.ID_OLAP)=DICO_OLAP.ID_OLAP) 
						AND ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP) 
						AND ((DICO_OLAP_CHAMP.ID_DIMENSION)=DICO_OLAP_DIMENSION.ID_DIMENSION) 
						AND ((DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE)=DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE))
					ORDER BY DICO_OLAP_DIMENSION.ORDRE,DICO_OLAP_CHAMP.ORDRE_DIM';
					
					$req_create_deux = 'SELECT DICO_OLAP_CHAMP.NOM_CHAMP AS CHAMP, DICO_OLAP_CHAMP.FONCTION AS FONCTION, 
						DICO_OLAP_CHAMP.FORMAT AS FORMAT, DICO_OLAP_CHAMP.EXPRESSION AS EXPRESSION, DICO_OLAP_CHAMP.ALIAS AS ALIAS_CHAMP,
						DICO_OLAP_CHAMP.ORDRE_DIM AS ORDRE_DIM, DICO_OLAP_CHAMP.ALL_LEVEL AS ALL_LEVEL, DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP AS TYPE_CHAMP, Null AS DIMENSION,
						DICO_OLAP.USE_CURRENT_DB_CNX AS BOOL_CURRENT_CNX, DICO_OLAP.ASSOCIATE_OLAP_FILE AS OLAP_FILE, DICO_OLAP.THEME_NAME AS NOM_CUBE, DICO_OLAP.TABLE_FAITS AS FAITS,
						DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE, 
						DICO_OLAP_CHAMP.ORDRE_OLAP AS ORDRE_OLAP
					FROM DICO_OLAP, DICO_OLAP_CHAMP, DICO_OLAP_TYPE_CHAMP, DICO_OLAP_TABLE_MERE
					WHERE (((DICO_OLAP.ID_OLAP)='.$id_cube.') And ((DICO_OLAP_CHAMP.ID_OLAP)=DICO_OLAP.ID_OLAP) 
						And ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP) 
						And ((DICO_OLAP_CHAMP.ID_DIMENSION) Is Null) 
						And ((DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE)=DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE))
					ORDER BY DICO_OLAP_CHAMP.ORDRE_OLAP';        
					
					//$req_create=$req_create_un.' UNION '. $req_create_deux;
					
					$req_mes ='SELECT Count(DICO_OLAP_CHAMP.ID_CHAMP) AS NB_MESURE FROM DICO_OLAP_CHAMP                            
								WHERE (((DICO_OLAP_CHAMP.ID_OLAP)='.$id_cube.') AND ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=1))
								GROUP BY DICO_OLAP_CHAMP.ID_OLAP, DICO_OLAP_CHAMP.ID_TYPE_CHAMP';
					$req_mes_calcul ='SELECT Count(DICO_OLAP_CHAMP.ID_CHAMP) AS NB_MESURE FROM DICO_OLAP_CHAMP
										WHERE (((DICO_OLAP_CHAMP.ID_OLAP)='.$id_cube.') AND ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=1)
										AND ((DICO_OLAP_CHAMP.EXPRESSION) Is Not Null))
										GROUP BY DICO_OLAP_CHAMP.ID_OLAP, DICO_OLAP_CHAMP.ID_TYPE_CHAMP';
					
					//requête pour la clause from
					$req_from='SELECT DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE 
					FROM DICO_OLAP, DICO_OLAP_TABLE_MERE
					WHERE DICO_OLAP.ID_OLAP='.$id_cube.' 
					AND DICO_OLAP.ID_OLAP=DICO_OLAP_TABLE_MERE.ID_OLAP
					AND DICO_OLAP_TABLE_MERE.IS_FICTIF Is Null';
							
					//requête pour la clause where (jointure)
					$req_where='
					SELECT TM1.NOM_TABLE_MERE AS TABLE1, TM1.NOM_ALIAS AS ALIAS_TABLE1, CH1.NOM_CHAMP AS CHAMP1, TM2.NOM_TABLE_MERE AS TABLE2, TM2.NOM_ALIAS AS ALIAS_TABLE2, CH2.NOM_CHAMP AS CHAMP2 
					FROM DICO_OLAP, DICO_OLAP_JOINTURES, DICO_OLAP_TABLE_MERE TM1, DICO_OLAP_TABLE_MERE TM2, DICO_OLAP_CHAMP CH1, DICO_OLAP_CHAMP CH2
					WHERE DICO_OLAP.ID_OLAP='.$id_cube.' 
							AND DICO_OLAP.ID_OLAP=TM1.ID_OLAP
							AND DICO_OLAP.ID_OLAP=TM2.ID_OLAP
							AND TM1.ID_OLAP_TABLE_MERE=DICO_OLAP_JOINTURES.ID_OLAP_TABLE_MERE_1
							AND TM2.ID_OLAP_TABLE_MERE=DICO_OLAP_JOINTURES.ID_OLAP_TABLE_MERE_2
							AND CH1.ID_CHAMP=DICO_OLAP_JOINTURES.ID_CHAMP_1
							AND CH2.ID_CHAMP=DICO_OLAP_JOINTURES.ID_CHAMP_2
					';
					
					// Tri du tableau selon le num ordre olap
					$rs_create_un = $GLOBALS['conn_dico']->GetAll($req_create_un);
					
					$rs_create_deux = $GLOBALS['conn_dico']->GetAll($req_create_deux);               
					$rs_create_deux = $this->tri_ordre_olap($rs_create_deux);
					
					$rs_create = array_merge($rs_create_un,$rs_create_deux);
					
					// Récuperation des critères
					$req_critere='SELECT ID_OLAP,NOM_TABLE, ALIAS_TABLE, NOM_CHAMP, OPERATEUR, VALEUR,TYPE_CHAMP
					FROM DICO_OLAP_CRITERE
					WHERE (((DICO_OLAP_CRITERE.ID_OLAP)='.$id_cube.'))';
					
					$rs_criteres  = $GLOBALS['conn_dico']->GetAll($req_critere);
	
					$rs_nb_mes = $GLOBALS['conn_dico']->GetAll($req_mes);
					if (count($rs_nb_mes )>0)
						$nb_mes = $rs_nb_mes[0]['NB_MESURE'];
					else
						 $nb_mes = 0;
						 
					 $rs_mes_calcul = $GLOBALS['conn_dico']->GetAll($req_mes_calcul);
					 if (count($rs_mes_calcul)>0)
							$nb_mes_cal = $rs_mes_calcul[0]['NB_MESURE'];
					 else
							 $nb_mes_cal = 0;
					
					$nb_mes_sans_cal = $nb_mes - $nb_mes_cal; // nombre de mesures y compris les mesures calculées
	
					$rs_from	= $GLOBALS['conn_dico']->GetAll($req_from);
					$rs_where	= $GLOBALS['conn_dico']->GetAll($req_where);
			
					$strProvider = 'PROVIDER=MSOLAP';
					
					if (preg_match('/.cub/',$rs_create[0]['OLAP_FILE'])){
						$strDataSource = $GLOBALS['SISED_SHARED_PATH_LAN'].$rs_create[0]['OLAP_FILE'];
						$this->liste_file_cubes[]= $rs_create[0]['OLAP_FILE'];
					}
					else{
						$strDataSource = $GLOBALS['SISED_SHARED_PATH_LAN'].$rs_create[0]['OLAP_FILE'].'.cub';
						$this->liste_file_cubes[]= $rs_create[0]['OLAP_FILE'].'.cub';
					}
					
					$this->strDataSource    =   $strDataSource;
					if(trim($rs_create[0]['NOM_CUBE'])<>'')
						$nom_cube   =   $rs_create[0]['NOM_CUBE'];
					else
						$nom_cube   =   'MyCube';
					
					//Le nom d'un cube olap est limité à 24 char 
					if(strlen($nom_cube)>24)
						$nom_cube = substr($nom_cube,0,24);
					
					if($rs_create[0]['BOOL_CURRENT_CNX'])
					{   
						$this->xdbtype=$GLOBALS['conn']->databaseType;
						$this->xdbsource = $GLOBALS['conn']->host;
						$this->xdbcatalogue = $GLOBALS['conn']->databaseName;
						$this->xdbuser = $GLOBALS['conn']->user;
						$this->xdbpwd = $GLOBALS['conn']->password;
						
						$delimit_gche="[";
						$delimit_drte="]";                    
						
						switch ($this->xdbtype){
									case 'access':
										//$this->xdbprovider ='Microsoft.Jet.OLEDB.4.0'; //Connexion OLEDB : ça ne ne marche pas :erreur: pilote isam introuvable
										$this->xdbprovider = "Driver={Microsoft Access Driver (*.mdb, *.accdb)}"; //Connexion ODBC
										$pos=strpos($this->xdbsource,';');                    
										$this->xdbsource = substr($GLOBALS['conn']->host,$pos + 5); //pour extraire juste le chemin de la base
										$db_name = '';
										if(preg_match("//([a-zA-Z0-9_-]+.mdb);/",$this->xdbsource,$reg) || preg_match("//([a-zA-Z0-9_-]+.accdb);/",$this->xdbsource,$reg))
										{
											$db_name=$reg[1];
										}
										if($_SERVER['SERVER_NAME']=='localhost')
											$strSourceDSN = $this->xdbprovider.';Dbq='.$this->xdbsource;
										else
										{
											$this->xdbsource = preg_replace('/:/','',$this->xdbsource); 
											$strSourceDSN = $this->xdbprovider.';Dbq=//'.$_SERVER['SERVER_NAME'].'/db/'.$db_name;
										}
										break;
									case 'mssql':
										$this->xdbprovider ='SQLOLEDB';
										//$strSourceDSN = 'Provider='.$this->xdbprovider.';Data Source='.$this->xdbsource.';Initial Catalog='.$this->xdbcatalogue.';User ID='.$this->xdbuser.';Password='.$this->xdbpwd;
										$strSourceDSN = 'Provider='.$this->xdbprovider.';Data Source='.$_SERVER['SERVER_NAME'].';Initial Catalog='.$this->xdbcatalogue.';User ID='.$this->xdbuser.';Password='.$this->xdbpwd;
										break;
									case 'mysqli':                                    
										$this->xdbprovider ='{MySQL ODBC 3.51 Driver}';                                    
										$strSourceDSN = 'Driver='.$this->xdbprovider.';database='.$this->xdbcatalogue.';server='.$_SERVER['SERVER_NAME'].';uid='.$this->xdbuser.';pwd='.$this->xdbpwd.';option=16386';                    
										$delimit_gche="`";
										$delimit_drte="`";  
										break;
									case 'oracle':   
										//$this->xdbprovider ='OraOLEDB.Oracle';
										$this->xdbprovider ='msdaora';
										$strSourceDSN = 'Driver='.$this->xdbprovider.';server='.$_SERVER['SERVER_NAME'].';database='.$this->xdbcatalogue.';User Id='.$this->xdbuser.';Password='.$this->xdbpwd;
										break;              
						}
						
					}
					
					if(is_array($rs_create) && count($rs_create) && is_array($rs_nb_mes) && count($rs_nb_mes) && is_array($rs_from) && count($rs_from))
					{                
						$strCreateCube = '';
						$strInsertInto = '';
						
						$script ="";
						
						$script.="\n";
						$script.='<SCRIPT LANGUAGE="VBSCRIPT">';
						$script.="\n";
						$script.='sub Create_cube'.$id_cube.'() 
									Dim cnCube 
									Dim s 
									Dim strProvider 
									Dim strDataSource 
									Dim strSourceDSN
									Dim strSourceDSNSuffix 
									Dim strCreateCube
									Dim strInsertInto
									strProvider = "'.$strProvider.'"                   
									strDataSource = "DATA SOURCE='.$strDataSource.'"
									Set cnCube = CreateObject("ADODB.Connection")';
									$script.="\n";       
						$script.='strSourceDSN = "SOURCE_DSN=""'.$strSourceDSN.'"";"';
						$script.="\n";
						$script.='strCreateCube = "CREATECUBE=CREATE CUBE ['.$nom_cube.']("';
						$script.="\n";
									$nb_chps=count($rs_create);
									$cpte=0;
									if ($nb_chps)
									{                        
										foreach($rs_create as $rs)
										{   
											if($rs['TYPE_CHAMP']==2) // Cas des dimensions
											{   
												if($rs['ORDRE_DIM']<>'' && ($rs['ORDRE_DIM']==1 or $rs['ORDRE_DIM']==0))
												{
													$script.='strCreateCube = strCreateCube & "DIMENSION ['.$rs['DIMENSION'].'],"';
													if($rs['ALL_LEVEL']==true){
														$script.="\n";
														$script.='strCreateCube = strCreateCube & "LEVEL [All '.$rs['DIMENSION'].']  TYPE ALL,"';
													}
													$script.="\n";
													$deb_dim=false;
												}
												if(trim($rs['ALIAS_CHAMP'])<>"")
													$nom_champs=$rs['ALIAS_CHAMP'];
												else
													$nom_champs = $rs['CHAMP'];                                       
												$script.='strCreateCube = strCreateCube & "LEVEL ['.$nom_champs.'] OPTIONS(SORTBYKEY),"';//OPTIONS(SORTBYKEY): On trie par clé
												$script.="\n";
											}
																			   
										}
										foreach($rs_create as $rs)
										{
											
											if($rs['TYPE_CHAMP']==1)// Cas des mesures
											{
												$cpte++;
												if(trim($rs['ALIAS_CHAMP'])<>"")
													$nom_champs=$rs['ALIAS_CHAMP'];
												else
													$nom_champs = $rs['CHAMP'];  
												if (!(isset($rs['EXPRESSION']) && trim($rs['EXPRESSION'])<>'')){
													// Cas des mesures simples
													$script.='strCreateCube = strCreateCube & "MEASURE ['.$nom_champs.'] "';
													$script.="\n";
													if($rs['FONCTION']<>'') 
													{   
														$script.='strCreateCube = strCreateCube & "Function '.$rs['FONCTION'];                                
													}  
													if($rs['FONCTION']=='') 
													{   
														$script.='strCreateCube = strCreateCube & "Function Sum';                                
													}                                              
													if($rs['FORMAT']<>'') 
													{
														$script.=' Format \''.$rs['FORMAT'].'\'';                                                
													} 
												}else{
													// Cas des mesures calculées
													$script.='strCreateCube = strCreateCube & "COMMAND (CREATE MEMBER CURRENTCUBE.MEASURES.['.$nom_champs.'] "';                                            
													$script.="\n";
													$script.='strCreateCube = strCreateCube & " AS \''.$rs['EXPRESSION'].'\')';                                            
												}                                       
												if($cpte < $nb_mes) $script.=',"';
												else $script.=') "';
												$script.="\n";
											}
										}                                    
									}
									
						$script.='strInsertInto = strInsertInto & " INSERTINTO=INSERT INTO ['.$nom_cube.']("';
						$script.="\n";
									$nb_chps=count($rs_create);
									$cpte=0;
									if ($nb_chps)
									{                        
										foreach($rs_create as $rs)
										{                                                       
											if($rs['TYPE_CHAMP']==2)// Cas des dimensions
											{
												if(trim($rs['ALIAS_CHAMP'])<>"")
													$nom_champs=$rs['ALIAS_CHAMP'];
												else
													$nom_champs = $rs['CHAMP'];                                          
												$script.='strInsertInto = strInsertInto & "['.$rs['DIMENSION'].'].['.$nom_champs.'].NAME, ';
												$script.='['.$rs['DIMENSION'].'].['.$nom_champs.'].KEY,"';
												$script.="\n";
											}
											
										}
										
										foreach($rs_create as $rs)
										{                                    
											if($rs['TYPE_CHAMP']==1 && (!(isset($rs['EXPRESSION']) && trim($rs['EXPRESSION'])<>'')))// Cas des mesures
											{
												$cpte++;                                                                              
													if(trim($rs['ALIAS_CHAMP'])<>"")
														$nom_champs=$rs['ALIAS_CHAMP'];
													else
														$nom_champs = $rs['CHAMP'];  
													$script.='strInsertInto = strInsertInto & "Measures.['.$nom_champs.']';
												
												if($cpte < $nb_mes_sans_cal) $script.=',"'; 
												else $script.=') "'; 
												$script.="\n";
											}                      
											
										}
											
									}
									
						if ( $rs_create[0]['FAITS']<>'')
							$nom_fact_table =  $rs_create[0]['FAITS'];
						else
							$nom_fact_table ='FACT_TABLE';
						
						$script.='strInsertInto = strInsertInto & " SELECT "';
						$script.="\n";
						
						$sql_nb_chps = 'SELECT * FROM '.$nom_fact_table;                                                     
						
						$rs_nb_chps = $GLOBALS['conn']->GetRow($sql_nb_chps);                    
						$nb_champ_total = count($rs_nb_chps );
						for ($k=1; $k<=$nb_champ_total;$k++){
							$nom_champs = 'Col'.$k;                                    
							if ($k<$nb_champ_total)
								$script.='strInsertInto = strInsertInto & "'.$delimit_gche.$nom_fact_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.',"';
							else
								$script.='strInsertInto = strInsertInto & "'.$delimit_gche.$nom_fact_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.'"';
							$script.="\n";
						}
						
						$script.='strInsertInto = strInsertInto & " From "';
						$script.="\n";
						$script.='strInsertInto = strInsertInto & "'.$delimit_gche.$nom_fact_table.$delimit_drte.'"';
						$script.="\n";
						
						$script.='s = strProvider & ";" & strDataSource & ";" & strSourceDSN & ";" & strCreateCube & ";" & strInsertInto & ";"';
						$script.="\n";
									
						$script.='cnCube.Open s';
						$script.="\n";
						$script.='end sub'; 
						$script.="\n";
						$script.="</SCRIPT>";
						$script.="\n";
						$this->vbscript[$id_cube]=$script;                
					} 
				} 
			}            
            $this->html=$this->vbscript;            
        }
        
		/**
		* Trie les dimensions selon leur ordre defini dans la config du cube
		* rs_create = rs_create_1 + rs_create_2
		* @access public
		* @param array rs_create est un tableau contenant les dimensions d'un
		* cube
		*/
        public function tri_ordre_olap($rs_create) {
            $rs_create_tri = array();
            $result = array(); 
            
            $rs_create_tri  = $rs_create;
            while (count($rs_create_tri)>0){
                $min=-1;
                $elt_min = array();
                
                if (is_array($rs_create_tri)){
                    foreach ($rs_create_tri as $elt){
                        if ($min == -1){
                            $min = $elt['ORDRE_OLAP'];  
                            $elt_min =$elt;
                        }
                        else{
                            if ($min > $elt['ORDRE_OLAP']){
                                $min = $elt['ORDRE_OLAP'];
                                $elt_min =$elt;
                            }                                
                        }
                    }
                }
                
                $result[]= $elt_min;
                
                $temp= array();
                if (is_array($rs_create_tri)){
                    foreach ($rs_create_tri as $elt){
                        if ($elt['ORDRE_OLAP']<>$min)
                        $temp[] = $elt;
                    }
                }
                $rs_create_tri = $temp;
            }
            return $result;
        }
        
        /**
		* Fonction de suppression batch des tables temporaires de tous les cubes
		* à générer
		* @access public
		*/
        public function delete_table_temp(){
                    
            foreach ($this->liste_cubes as $id_cube){    
                $req_create_tables_temp = 'SELECT * FROM DICO_OLAP_QUERY WHERE ID_OLAP = '.$id_cube.' AND TYPE_QUERY_OLAP = 1 ORDER BY ORDRE_EXECUTION_OLAP' ; 
                $res_create_tables_temp = $GLOBALS['conn_dico']->GetAll($req_create_tables_temp);

                foreach($res_create_tables_temp as $rs)
                {
                    if(($result  = $GLOBALS['conn']->Execute($rs['SQL_QUERY_OLAP']))===false) {echo "<br>".$rs['SQL_QUERY_OLAP']."<br>";}
                }    
            }
        }
        
        /**
		* Fonction de creation batch des tables temporaires de tous les cubes à
		* générer
		* @access public
		*/
        public function creer_table_temp() {
            foreach ($this->liste_cubes as $id_cube){    
                $req_create_tables_temp = 'SELECT * FROM DICO_OLAP_QUERY WHERE ID_OLAP = '.$id_cube.' AND TYPE_QUERY_OLAP = 0 ORDER BY ORDRE_EXECUTION_OLAP' ; 
                $res_create_tables_temp = $GLOBALS['conn_dico']->GetAll($req_create_tables_temp);

                foreach($res_create_tables_temp as $rs)
                {
                    if(($result  = $GLOBALS['conn']->Execute($rs['SQL_QUERY_OLAP']))===false) {echo "<br>".$rs['SQL_QUERY_OLAP']."<br>";}
                }    
            }
        }
        
        
        /**
        * Fonction de suppression batch des cubes existants
		* @access public
		*/
        public function delete_cubes(){
            foreach ($this->liste_cubes as $id_cube){
                $req="SELECT ASSOCIATE_OLAP_FILE FROM DICO_OLAP WHERE ID_OLAP = ".$id_cube;
                $res = $GLOBALS['conn_dico']->GetAll($req);
                $nom_theme = $res[0]['ASSOCIATE_OLAP_FILE'];
                $nom_fichier="";
                if(preg_match("/(\.cub)/",$nom_theme))
                    $nom_fichier=$nom_theme;			
                else
                    $nom_fichier=$nom_theme.'.cub'	;      
                if(is_file($GLOBALS['SISED_PATH_INC'].'olap_tools/cubes/'.$nom_fichier)) 
                    @unlink($GLOBALS['SISED_PATH_INC'].'olap_tools/cubes/'.$nom_fichier);
            }
        }
        
		/**
        * Fonction de suppression batch des tables de faits de tous les cubes à
        * générer
		* @access public
		*/
        public function delete_table_faits(){
            foreach ($this->liste_cubes as $id_cube){ 
                $sql = ' SELECT TABLE_FAITS AS FACT FROM DICO_OLAP WHERE DICO_OLAP.ID_OLAP='.$id_cube;                
                $nom_fact_table = $GLOBALS['conn_dico']->GetAll($sql);
                
                if ($nom_fact_table[0]['FACT']<>'')
                    $sql_delete = 'DROP TABLE '.$nom_fact_table[0]['FACT'];
                else
                    $sql_delete = 'DROP TABLE FACT_TABLE';                
                //if(($result = $this->conn->Execute($sql_delete ))===false){echo  '<br>'.$sql_delete .'<br>';}
                $result = $GLOBALS['conn']->Execute($sql_delete );
                
            }
        }
        
        /**
        * Fonction de creation batch des tables de faits de tous les cubes à
        * générer
		* @access public
		*/
        public function creer_table_faits() {            
            
            switch ($this->xdbtype){
                 Case 'mysqli':                                    
                            $delimit_gche="`";
                            $delimit_drte="`"; 
                            break;
                default:
                        $delimit_gche="[";
                        $delimit_drte="]";   
                        break;                        
            }           
            foreach ($this->liste_cubes as $id_cube){    
                 $req_create_un ='SELECT DICO_OLAP_CHAMP.NOM_CHAMP AS CHAMP, DICO_OLAP_CHAMP.FONCTION AS FONCTION, DICO_OLAP_CHAMP.FORMAT AS FORMAT, 
                        DICO_OLAP_CHAMP.EXPRESSION AS EXPRESSION, DICO_OLAP_CHAMP.ALIAS AS ALIAS_CHAMP, DICO_OLAP_CHAMP.ORDRE_DIM AS ORDRE_DIM,
                        DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP AS TYPE_CHAMP, DICO_OLAP_CHAMP.ID_DIMENSION AS ID_DIM, DICO_OLAP_DIMENSION.LIBELLE_DIMENSION AS DIMENSION,
                        DICO_OLAP.USE_CURRENT_DB_CNX AS BOOL_CURRENT_CNX, DICO_OLAP.ASSOCIATE_OLAP_FILE AS OLAP_FILE, DICO_OLAP.THEME_NAME AS NOM_CUBE, DICO_OLAP.TABLE_FAITS AS FAITS,
                        DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE AS ID_TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE, 
                        DICO_OLAP_CHAMP.ORDRE_OLAP AS ORDRE_OLAP
                    FROM DICO_OLAP, DICO_OLAP_CHAMP, DICO_OLAP_TYPE_CHAMP, DICO_OLAP_DIMENSION, DICO_OLAP_TABLE_MERE
                    WHERE (((DICO_OLAP.ID_OLAP)='.$id_cube.') 
                        AND ((DICO_OLAP_CHAMP.ID_OLAP)=DICO_OLAP.ID_OLAP) 
                        AND ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP) 
                        AND ((DICO_OLAP_CHAMP.ID_DIMENSION)=DICO_OLAP_DIMENSION.ID_DIMENSION) 
                        AND ((DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE)=DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE))
                    ORDER BY DICO_OLAP_DIMENSION.ORDRE,DICO_OLAP_CHAMP.ORDRE_DIM';
                    
                    $req_create_deux = 'SELECT DICO_OLAP_CHAMP.NOM_CHAMP AS CHAMP, DICO_OLAP_CHAMP.FONCTION AS FONCTION, 
                        DICO_OLAP_CHAMP.FORMAT AS FORMAT, DICO_OLAP_CHAMP.EXPRESSION AS EXPRESSION, DICO_OLAP_CHAMP.ALIAS AS ALIAS_CHAMP,
                        DICO_OLAP_CHAMP.ORDRE_DIM AS ORDRE_DIM, DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP AS TYPE_CHAMP, Null AS ID_DIM, Null AS DIMENSION,
                        DICO_OLAP.USE_CURRENT_DB_CNX AS BOOL_CURRENT_CNX, DICO_OLAP.ASSOCIATE_OLAP_FILE AS OLAP_FILE, DICO_OLAP.THEME_NAME AS NOM_CUBE,  DICO_OLAP.TABLE_FAITS AS FAITS,
                        DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE AS ID_TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE, 
                        DICO_OLAP_CHAMP.ORDRE_OLAP AS ORDRE_OLAP
                    FROM DICO_OLAP, DICO_OLAP_CHAMP, DICO_OLAP_TYPE_CHAMP, DICO_OLAP_TABLE_MERE
                    WHERE (((DICO_OLAP.ID_OLAP)='.$id_cube.') And ((DICO_OLAP_CHAMP.ID_OLAP)=DICO_OLAP.ID_OLAP) 
                        And ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP) 
                        And ((DICO_OLAP_CHAMP.ID_DIMENSION) Is Null) 
                        And ((DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE)=DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE))
                    ORDER BY DICO_OLAP_CHAMP.ORDRE_OLAP';  
                     
                     $req_from='SELECT DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE 
                FROM DICO_OLAP, DICO_OLAP_TABLE_MERE
                WHERE DICO_OLAP.ID_OLAP='.$id_cube.' 
                AND DICO_OLAP.ID_OLAP=DICO_OLAP_TABLE_MERE.ID_OLAP
                AND DICO_OLAP_TABLE_MERE.IS_FICTIF Is Null';
                        
                //requête pour la clause where (jointure)
                $req_where='
                SELECT TM1.NOM_TABLE_MERE AS TABLE1, TM1.NOM_ALIAS AS ALIAS_TABLE1, CH1.NOM_CHAMP AS CHAMP1, TM2.NOM_TABLE_MERE AS TABLE2, TM2.NOM_ALIAS AS ALIAS_TABLE2, CH2.NOM_CHAMP AS CHAMP2 
                FROM DICO_OLAP, DICO_OLAP_JOINTURES, DICO_OLAP_TABLE_MERE TM1, DICO_OLAP_TABLE_MERE TM2, DICO_OLAP_CHAMP CH1, DICO_OLAP_CHAMP CH2
                WHERE DICO_OLAP.ID_OLAP='.$id_cube.' 
                        AND DICO_OLAP.ID_OLAP=TM1.ID_OLAP
                        AND DICO_OLAP.ID_OLAP=TM2.ID_OLAP
                        AND TM1.ID_OLAP_TABLE_MERE=DICO_OLAP_JOINTURES.ID_OLAP_TABLE_MERE_1
                        AND TM2.ID_OLAP_TABLE_MERE=DICO_OLAP_JOINTURES.ID_OLAP_TABLE_MERE_2
                        AND CH1.ID_CHAMP=DICO_OLAP_JOINTURES.ID_CHAMP_1
                        AND CH2.ID_CHAMP=DICO_OLAP_JOINTURES.ID_CHAMP_2
                ';
    
                    $rs_create_un = $GLOBALS['conn_dico']->GetAll($req_create_un);
                    
                    $rs_create_deux = $GLOBALS['conn_dico']->GetAll($req_create_deux);               
                    $rs_create_deux = $this->tri_ordre_olap($rs_create_deux);                    
                    
                    $rs_create = array_merge($rs_create_un,$rs_create_deux);
                    
                     // Récuperation des critères autres que les jointures
                    $req_critere='SELECT ID_OLAP,NOM_TABLE, ALIAS_TABLE, NOM_CHAMP,OPERATEUR, VALEUR,TYPE_CHAMP
                    FROM DICO_OLAP_CRITERE
                    WHERE (((DICO_OLAP_CRITERE.ID_OLAP)='.$id_cube.'))';
                    $rs_criteres  = $GLOBALS['conn_dico']->GetAll($req_critere);
                    
                    $req_mes ='SELECT Count(DICO_OLAP_CHAMP.ID_CHAMP) AS NB_MESURE FROM DICO_OLAP_CHAMP                            
                            WHERE (((DICO_OLAP_CHAMP.ID_OLAP)='.$id_cube.') AND ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=1))
                            GROUP BY DICO_OLAP_CHAMP.ID_OLAP, DICO_OLAP_CHAMP.ID_TYPE_CHAMP';
                            
                    $req_mes_calcul ='SELECT Count(DICO_OLAP_CHAMP.ID_CHAMP) AS NB_MESURE FROM DICO_OLAP_CHAMP
                                    WHERE (((DICO_OLAP_CHAMP.ID_OLAP)='.$id_cube.') AND ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=1)
                                    AND ((DICO_OLAP_CHAMP.EXPRESSION) Is Not Null))
                                    GROUP BY DICO_OLAP_CHAMP.ID_OLAP, DICO_OLAP_CHAMP.ID_TYPE_CHAMP';                    
                        
                    $rs_nb_mes = $GLOBALS['conn_dico']->GetAll($req_mes);
                    
                    $rs_from	= $GLOBALS['conn_dico']->GetAll($req_from);
                    $rs_where	= $GLOBALS['conn_dico']->GetAll($req_where);
                
                    if (count($rs_nb_mes )>0)
                        $nb_mes = $rs_nb_mes[0]['NB_MESURE'];
                    else
                         $nb_mes = 0;
                         
                     $rs_mes_calcul = $GLOBALS['conn_dico']->GetAll($req_mes_calcul);
                     if (count($rs_mes_calcul)>0)
                            $nb_mes_cal = $rs_mes_calcul[0]['NB_MESURE'];
                     else
                             $nb_mes_cal = 0;
                    
                    $nb_mes_sans_cal = $nb_mes - $nb_mes_cal; // nombre de mesures sans les mesures calculées
                    
                    if (isset($rs_create[0]['FAITS']) && $rs_create[0]['FAITS']<>'')
                        $nom_table_fait = $rs_create[0]['FAITS'];
                    else
                        $nom_table_fait ='FACT_TABLE';
                    
                    
                    if(is_array($rs_create) && count($rs_create) && is_array($rs_nb_mes) && count($rs_nb_mes) && is_array($rs_from) && count($rs_from))
                    {
                        if($this->xdbtype == 'mysqli')
                            $script='CREATE TABLE '.$nom_table_fait ;//Clause valable dans la cas de mysql 
                        else 
                            $script='';                        
                        
                        $script.=' SELECT ';
                        
                                    $nb_chps=count($rs_create);
                                    $cpte=0;
                                    $k=0;
                                    if ($nb_chps)
                                    {                        
                                        foreach($rs_create as $rs)
                                        { 
                                            if($rs['TYPE_CHAMP']==2) // cas des dimensions
                                            {  
                                                $k++;                                    
                                                if(trim($rs['ALIAS_TABLE_MERE'])<>""){
                                                    $nom_table=$rs['ALIAS_TABLE_MERE'];}
                                                else{
                                                    $nom_table = $rs['TABLE_MERE'];  }                                         
                                                
                                                $nom_champs = $rs['CHAMP'];                                    
                                                $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.' AS Col'.$k.',';                                                                           
                                                
                                                // gestion ordre des données de la dimension
                                                $id_dim = $rs['ID_DIM'];
                                                $id_tabm = $rs['ID_TABLE_MERE'];                                                 
                                                $req_champ_ordre ='SELECT DICO_OLAP_CHAMP.NOM_CHAMP AS CHAMP, DICO_OLAP_CHAMP.ALIAS AS ALIAS_CHAMP, DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE
                                                                    FROM DICO_OLAP_CHAMP, DICO_OLAP_TABLE_MERE
                                                                    WHERE (((DICO_OLAP_CHAMP.ID_OLAP)='.$id_cube.') 
                                                                        AND ((DICO_OLAP_CHAMP.ID_DIMENSION)='.$id_dim.') 
                                                                        AND ((DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE)='.$id_tabm.')
                                                                        AND ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=4)                                                                        
                                                                        AND ((DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE)=DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE))';
                                                
                                                $req_champ_ordre_2 ='SELECT DICO_OLAP_CHAMP.NOM_CHAMP AS CHAMP, DICO_OLAP_CHAMP.ALIAS AS ALIAS_CHAMP, DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE
                                                                    FROM DICO_OLAP_CHAMP, DICO_OLAP_TABLE_MERE
                                                                    WHERE (((DICO_OLAP_CHAMP.ID_OLAP)='.$id_cube.') 
                                                                        AND ((DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE)='.$id_tabm.')
                                                                        AND ((DICO_OLAP_CHAMP.ID_TYPE_CHAMP)=4)                                                                        
                                                                        AND ((DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE)=DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE))';
                                                
                                                
                                                $res_champ_ordre=$GLOBALS['conn_dico']->GetAll($req_champ_ordre);
                                                $res_champ_ordre_2=$GLOBALS['conn_dico']->GetAll($req_champ_ordre_2);
                                                
                                                if(count($res_champ_ordre)) //Si un ordre est defini, on utilise ordre des données
                                                {  
                                                    $k++;                                    
                                                    if(trim($res_champ_ordre[0]['ALIAS_TABLE_MERE'])<>""){
                                                        $nom_table=$res_champ_ordre[0]['ALIAS_TABLE_MERE'];}
                                                    else{
                                                        $nom_table = $res_champ_ordre[0]['TABLE_MERE'];  }                                         
                                                    
                                                    $nom_champs = $res_champ_ordre[0]['CHAMP'];                                    
                                                    $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.' AS Col'.$k.',';                                                                           
                                                }
                                                elseif(count($res_champ_ordre_2)) //Si un ordre est defini, on utilise ordre des données
                                                {  
                                                    $k++;                                    
                                                    if(trim($res_champ_ordre_2[0]['ALIAS_TABLE_MERE'])<>""){
                                                        $nom_table=$res_champ_ordre_2[0]['ALIAS_TABLE_MERE'];}
                                                    else{
                                                        $nom_table = $res_champ_ordre_2[0]['TABLE_MERE'];  }                                         
                                                    
                                                    $nom_champs = $res_champ_ordre_2[0]['CHAMP'];                                    
                                                    $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.' AS Col'.$k.',';                                                                           
                                                }
                                                else //Si aucun ordre n'est defini : le nom de la dimension est utilisé comme ordre
                                                {  
                                                    $k++;                                    
                                                    if(trim($rs['ALIAS_TABLE_MERE'])<>""){
                                                        $nom_table=$rs['ALIAS_TABLE_MERE'];}
                                                    else{
                                                        $nom_table = $rs['TABLE_MERE'];  }                                         
                                                    
                                                    $nom_champs = $rs['CHAMP'];                                    
                                                    $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.' AS Col'.$k.',';                                                                           
                                                }
                                                
                                            }
                                            
                                        }
                                        foreach($rs_create as $rs)
                                        { 
                                            
                                            //if($rs['TYPE_CHAMP']==1) // cas des mesures
                                            if($rs['TYPE_CHAMP']==1 && (!(isset($rs['EXPRESSION']) && trim($rs['EXPRESSION'])<>''))   )// Cas des mesures
                                            {
                                                $cpte++;
                                                $k++;
                                                if(trim($rs['ALIAS_TABLE_MERE'])<>"")
                                                    $nom_table=$rs['ALIAS_TABLE_MERE'];
                                                else
                                                    $nom_table = $rs['TABLE_MERE'];                                           
                                                
                                                $nom_champs = $rs['CHAMP'];                                    
                                                $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.' AS Col'.$k; 
                                                if($cpte < $nb_mes_sans_cal) $script.=','; 
                                                else $script.='';                                         
                                            }                                        
                                        }
                                    }
                                    
                        if($this->xdbtype <> 'mysqli')
                            $script.=' INTO '.$nom_table_fait ;//Valable pour les SGBD autres que mysql
                        
                        $script.=' From ';                
                                    $nb_chps=count($rs_from);
                                    $cpte=0;
                                    if ($nb_chps)
                                    {                        
                                        foreach($rs_from as $rs)
                                        { 
                                            $cpte++;
                                            if(trim($rs['ALIAS_TABLE_MERE'])<>"")
                                                $nom_alias_table=' AS '.$delimit_gche.$rs['ALIAS_TABLE_MERE'].$delimit_drte;
                                            else
                                                $nom_alias_table='';                                    
                                            $script.=$delimit_gche.$rs['TABLE_MERE'].$delimit_drte.$nom_alias_table;
                                            if($cpte < $nb_chps) $script.=','; 
                                            else $script.='';                                                               
                                        }
                                    }
                                    
                        $script.=' Where ';            
                                    $nb_chps=count($rs_where);
                                    $cpte=0;
                                    if ($nb_chps)
                                    {  
                                        foreach($rs_where as $rs)
                                        { 
                                            $cpte++; 
                                            if(trim($rs['ALIAS_TABLE1'])<>"")
                                                $nom_table1=$rs['ALIAS_TABLE1'];
                                            else
                                                $nom_table1 = $rs['TABLE1'];     
                                            if(trim($rs['ALIAS_TABLE2'])<>"")
                                                $nom_table2=$rs['ALIAS_TABLE2'];
                                            else
                                                $nom_table2 = $rs['TABLE2'];     
                                            $script.=$delimit_gche.$nom_table1.$delimit_drte.'.'.$delimit_gche.$rs['CHAMP1'].$delimit_drte.' = '.$delimit_gche.$nom_table2.$delimit_drte.'.'.$delimit_gche.$rs['CHAMP2'].$delimit_drte.' ';                                      
                                            if($cpte < $nb_chps) $script.='And '; 
                                            else $script.='';                                                               
                                        }
                                    }
                                    
                        // Début d'insertion des critères
                        
                        if (count($rs_criteres )>0){
                            foreach ($rs_criteres as $critere){
                                $script.=' AND ';
                                if (trim($critere['ALIAS_TABLE'])<>'')                        
                                     $script.= $delimit_gche.trim($critere['ALIAS_TABLE']).$delimit_drte.'.';
                                else
                                    $script.= $delimit_gche.trim($critere['NOM_TABLE']).$delimit_drte.'.';
                                
                                $script.= $delimit_gche.trim($critere['NOM_CHAMP']).$delimit_drte;
                                if (trim($critere['OPERATEUR'])<>'')                        
                                     $script.= ' '.trim($critere['OPERATEUR']).' ';
                                else
                                    $script.= ' = ';
                                
                                if(trim($critere['OPERATEUR'])<>'IN' && trim($critere['OPERATEUR'])<>'NOT IN')
                                {
                                    if (trim($critere['TYPE_CHAMP'])=='int')                        
                                         $script.= trim($critere['VALEUR']).'';
                                    else
                                        $script.= '\''.trim($critere['VALEUR']).'\'';                           
                                }
                                else
                                {
                                    $script.= '('.trim($critere['VALEUR']).')';
                                }
                            }
                         }
                        if( ($result  = $GLOBALS['conn']->Execute($script))===false){echo "<br> $script <br>";}                 
                    }   
            }            
        }
        
		/**
        * Fonction appelant la fonction d'initialisation du script de creation
        * du cube
		* @access public
		*/       
        public function generer_cube()
        {            
             // Appel de la construction du script de génération de cube    
            $this->set_vbscript();                
        }        
    }
?>
