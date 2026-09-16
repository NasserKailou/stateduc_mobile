<?php class olap_view {

		/**
		* Attribut : $conn
		* <pre>
		* variable de connexion à la base courante
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn; 
		
		
		/**
		* Attribut : $conn_OLAP
		* <pre>
		* Variable de connexion à la source OLAP
		* </pre>
		* @var 
		* @access public
		*/		 
		public $conn_OLAP; 
		
		
		/**
		* Attribut : $use_current_cnx_for_OLAP
		* <pre>
		* bolléen pour savoir si la connexion courante sera aussi celle d'OLAP
		* </pre>
		* @var 
		* @access public
		*/   
		public $use_current_cnx_for_OLAP = true;  
		
		
		/**
		* Attribut : $une_seule_base_OLAP
		* <pre>
		* booléen pour savoir si une seule base OLAP est  utilisée
		* conduit à lire les cubes ds la table DICO_OLAP
		* </pre>
		* @var 
		* @access public
		*/   
		public $une_seule_base_OLAP = true;  
		
		
		/**
		* Attribut : $params_autre_cnx_OLAP
		* <pre>
		* paramètres de connexion OLAP au cas où la connexion à la source OLAP est différente de celle en sours
		* </pre>
		* @var 
		* @access public
		*/   
		public $params_autre_cnx_OLAP = array(); 
		
		
		/**
		* Attribut : $tab_listes_OLAPS
		* <pre>
		* Tableau contenant toutes les instances OLAP configurées
		* </pre>
		* @var 
		* @access public
		*/   
		public $tab_liste_OLAPS = array(); 
		
		
		/**
		* Attribut : $tab_cubes_base
		* <pre>
		* Tableau contenant les cubes des differentes bases OLAP
		* cas où l'on utilise plus d'une base OLAP
		* </pre>
		* @var 
		* @access public
		*/   
		public $tab_cubes_base = array(); 
		
		
		/**
		* Attribut : $liste_bases_OLAP
		* <pre>
		* Tableau contenant les differentes bases OLAP
		* cas où l'on utilise plus d'une base OLAP
		* </pre>
		* @var 
		* @access public
		*/   
		public $liste_bases_OLAP = array(); 
		
		
		/**
		* Attribut : $curr_base_OLAP
		* <pre>
		* var contenat le nom de la base OLAP choisie
		* cas où l'on utilise plus d'une base OLAP
		* </pre>
		* @var 
		* @access public
		*/   
		public $curr_base_OLAP; 
			
		
		/**
		* Attribut : $id_OLAP
		* <pre>
		*  id de l'instance OLAP courante 
		* </pre>
		* @var 
		* @access public
		*/  
		public $id_OLAP; 
		
			
		/**
		* Attribut : $params_OLAP
		* <pre>
		*  contient l'ensemble des élèments de définition de l'instance OLAP en cours, comme la classe, l'action, ...
		* </pre>
		* @var 
		* @access public
		*/   
		public $params_OLAP = array();
        
        /**
		* Attribut : $type_sgbd
		* <pre>
		*  contient l'informaton sur le type de sgbd
		* </pre>
		* @var 
		* @access public
		*/   
		public $type_sgbd;
        
         /**
		* Attribut : $default_db
		* <pre>
		*  
		* </pre>
		* @var 
		* @access public
		*/   
		public $default_db;
        
         /**
		* Attribut : $default_db
		* <pre>
		*  
		* </pre>
		* @var 
		* @access public
		*/   
		public $select_db;
        
        
		
	
	
		/**
		* METHODE : __construct()
		* <pre>
		*  Constructeur
		* </pre>
		* @access public
		* 
		*/
		function __construct($type_sgbd='',$default_db=''){
			$this->conn	= $GLOBALS['conn']; // connexion par defaut de l'appli
			//$this->run(); // liste des instances OLAP existantes
            $this->type_sgbd    =   $type_sgbd;
            $this->default_db   =   $default_db;
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
			$this->conn						= $GLOBALS['conn'];
			$this->une_seule_base_OLAP 		= true ;
			$this->use_current_cnx_for_OLAP = true ;
			$this->tab_cubes_base 			= array() ;
		}
		
		
		/**
		* METHODE : set_tab_liste_OLAPS()
		* <pre>
		*  mise en liste des différentes instances OLAP
		*  Remplissage de la variable $tab_liste_OLAPS 
		* </pre>
		* @access public
		* 
		*/		
		function set_tab_liste_OLAPS(){
			$this->tab_liste_OLAPS = array();
			if($this->une_seule_base_OLAP == true){
				/*$requete 	=	' SELECT DICO_OLAP.ID_OLAP AS ID, DICO_OLAP.SGBD, DICO_OLAP.USE_CURRENT_DB_CNX, 
										DICO_OLAP.CLASSE_OLAP, DICO_OLAP.ACTION_OLAP, DICO_OLAP.SQL_OLAP, DICO_OLAP.DEFAUT_CONNEXION, 
										DICO_OLAP.SQL_LINK_FILE_OLAP, DICO_OLAP.THEME_NAME AS NAME, DICO_OLAP.ASSOCIATE_DESC_FILE, 
										DICO_OLAP.ASSOCIATE_IMAGE_FILE, DICO_OLAP.ASSOCIATE_OLAP_FILE
								  FROM DICO_OLAP
								  ORDER BY DICO_OLAP.THEME_NAME;';   */ 
				/*
                $requete 	=	' SELECT DICO_OLAP.ID_OLAP AS ID, DICO_OLAP.SGBD, DICO_OLAP.USE_CURRENT_DB_CNX, 
										DICO_OLAP.CLASSE_OLAP, DICO_OLAP.ACTION_OLAP, DICO_OLAP.SQL_OLAP, DICO_OLAP.DEFAUT_CONNEXION, 
										DICO_OLAP.SQL_LINK_FILE_OLAP, DICO_OLAP.THEME_NAME AS NAME, DICO_OLAP.ASSOCIATE_DESC_FILE, 
										DICO_OLAP.ASSOCIATE_IMAGE_FILE, DICO_OLAP.ASSOCIATE_OLAP_FILE
								  FROM DICO_OLAP
								  ORDER BY DICO_OLAP.ORDRE_OLAP;';    
				// Traitement Erreur Cas : GetAll / GetRow
				try {
					$this->tab_liste_OLAPS  = $GLOBALS['conn_dico']->GetAll($requete);
					if(!is_array($this->tab_liste_OLAPS)){                    
						throw new Exception('ERR_SQL');  
					} 
				}
				catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
				}
				*/
				$dossier_cubes = $GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/cubes/';
				if (is_dir($dossier_cubes)){
					if ($dh = opendir($dossier_cubes)){
						$i = 0;
						while (($file = readdir($dh)) !== false){
						   $nom_file = $dossier_cubes.$file;
						   if(is_file($nom_file)){
						   		$f['ID'] = $i;
								$f['NAME'] = $file;
								$this->tab_liste_OLAPS[$i] = $f;
								$i++;
						   }
						}
						closedir($dh);
					}
				}
				//echo '<pre>';
				//print_r($this->tab_liste_OLAPS);
				$_SESSION['liste_local_cubes'] = $this->tab_liste_OLAPS;
			}else{
				$this->set_liste_bases_OLAP();
				$this->set_curr_base_OLAP();
				if(is_array($this->tab_cubes_base[$this->curr_base_OLAP])){
					foreach($this->tab_cubes_base[$this->curr_base_OLAP] as $i => $cub){
						$this->tab_liste_OLAPS[] = $cub;
			}	}	}
			
		}
		
		
		/**
		* METHODE : set_liste_bases_OLAP()
		* <pre>
		*  Renseigner $this->liste_bases_OLAP
		* </pre>
		* @access public
		* 
		*/		
		function set_liste_bases_OLAP(){
			$this->liste_bases_OLAP = array();
                if (is_array($this->tab_cubes_base)){
                    foreach($this->tab_cubes_base as $base => $tab_cub){
                        if(!in_array($base, $this->liste_bases_OLAP)){
                            $this->liste_bases_OLAP[] = $base ;	
                        }
                     }
                 }
         }
        
				
		
		
		/**
		* METHODE : set_id_OLAP()
		* <pre>
		*  Positionner l'instance OLAP en cours (choisie par le user sinon premiere de la liste)
		* </pre>
		* @access public
		* 
		*/		
		function set_id_OLAP(){
			if( isset($_GET['id_OLAP']) and (trim($_GET['id_OLAP']) <> '')) {
				$this->id_OLAP = $_GET['id_OLAP'];
			}else{
				if( is_array($this->tab_liste_OLAPS) and (count($this->tab_liste_OLAPS) > 0) ){
					$this->id_OLAP = $this->tab_liste_OLAPS[0]['ID'];
		}	}	}
		
		
		/**
		* METHODE : set_curr_base_OLAP()
		* <pre>
		*  Positionner la base OLAP en cours (choisie par le user sinon premiere de la liste)
		* cas de plusieurs bases OLAP utilisées
		* </pre>
		* @access public
		* 
		*/		
		function set_curr_base_OLAP(){
			if( isset($_GET['base_OLAP']) and (trim($_GET['base_OLAP']) <> '')) {
				$this->curr_base_OLAP = $_GET['base_OLAP'];
			}else{
				if( is_array($this->liste_bases_OLAP) and (count($this->liste_bases_OLAP) > 0) ){
					$this->curr_base_OLAP = $this->liste_bases_OLAP[0];
		}	}	}
		
		
		
		/**
		* METHODE : set_params_OLAP()
		* <pre>
		*  Renseigner tous les éléments descriptifs du  current OLAP : classe, action, ...
		* </pre>
		* @access public
		* 
		*/		
		function set_params_OLAP(){
			if( isset($this->id_OLAP) && trim($this->id_OLAP) <> '' ){
				foreach($this->tab_liste_OLAPS as $i => $olap){
					if( trim($this->id_OLAP) == ($olap['ID']) ){
						$this->params_OLAP = $olap ;
						break;
		}	}	}	}
				
		
		
		/**
		* METHODE : set_conn_OLAP()
		* <pre>
		*  Récupérer la connexion à la source OLAP (qui dépend de chaque id_OLAP)
		* </pre>
		* @access public
		* 
		*/		
		function set_conn_OLAP(){
			if($this->use_current_cnx_for_OLAP == true){
				$this->conn_OLAP = $this->conn ;
			}else{
				// Faire la connexion à la source externe
			}
		}
		
		
		/**
		* METHODE : presenter_OLAP()
		* <pre>
		*  gere l'Affichage l'interface de présentation OLAP
		* </pre>
		* @access public
		* 
		*/		
		function presenter_OLAP(){
			?>
				<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF.'?'.$_SERVER['QUERY_STRING']; ?>">
					<br /><br />
					<span>
						<div align="center">
							<table>
							<?php $requete='SELECT SGBD, DB FROM DICO_OLAP_AUTRE_CONNEXION WHERE DEFAUT=1';    
								$result = $GLOBALS['conn_dico']->GetAll($requete);
								$type_connexion="";
								if (is_array($result) && count($result))
								{
									$type_connexion = recherche_libelle_page('cub_server');
								}
								else
								{
									$type_connexion = recherche_libelle_page('cub_local');
								}
								
								if( isset($this->params_OLAP['NAME']) and (trim($this->params_OLAP['NAME'])<>'') ){ 
									echo '<caption><B>  '.$type_connexion.'  :  '.$this->params_OLAP['NAME'].'</B></caption>';	
								}
								if(is_array($this->liste_bases_OLAP) && count($this->liste_bases_OLAP)){	
							 ?>
                            <?php if (!(isset($this->default_db) && $this->default_db<>'')){
                                     echo'<tr>
										  <td align="center" valign="top"  style="width:250">
											<div align="center">
											<select name="base_OLAP" onchange="javascript:ChargerBaseOLAP(this.value);" style="width:250">';
												/*<?php */
													
														foreach ($this->liste_bases_OLAP as $i => $base){                                                           
															echo "<option value='".$base."'";
                                                            $this->select_db    = $base;
															if ( trim($base) == trim($this->curr_base_OLAP) ){
																	echo " selected";
															}
															echo ">".$base."</option>";
														}
												/*?>*/
											  echo'</select></div>                                      
										  </td>
										  <td  style="width:250">&nbsp;</td>
									  </tr>';
                                }
                                else{
                                    echo'<tr>
										  <td align="center" valign="top"  style="width:250">
											<div align="center">';
                                                
                                              echo $this->default_db.'</div>                                      
										  </td>
										  <td  style="width:250">&nbsp;</td>
									  </tr>';
                                }
                                ?>
                                      
							<?php } else echo '<input type="hidden" name="base_OLAP" value="one_base_used" />'; ?>
							    <?php if (!(isset($this->default_db) && $this->default_db<>'')){
								echo '<tr>
                                      <td align="center" valign="top"  style="width:250">
                                      <div align="center">
                                          <select name="combo" size="10" onchange="javascript:ChargerOLAP(this.value,base_OLAP.value);" style="width:250">';
                                }
                                else{
                                    echo'<td align="center" valign="top"  style="width:250">
                                      <div align="center">
                                          <select name="combo" size="10" onchange="javascript:ChargerOLAP(this.value,\''.$this->default_db.'\');" style="width:250">';
                                }
                                ?>
                                        <?php if( is_array($this->tab_liste_OLAPS) and (count($this->tab_liste_OLAPS) > 0) ){
														foreach ($this->tab_liste_OLAPS as $i => $olap){
															echo "<option value='".$olap['ID']."'";
															if ( trim($olap['ID']) == trim($this->id_OLAP) ){
																	echo " selected";
															}
															echo ">".$olap['NAME']."</option>";
														}
													}
												?>
                                      </select>
                                     <?php echo '</div>'; ?>                                      
									</td>
								  <td  style="width:250"><div align="center">
                                      <?php if( isset($this->params_OLAP['ASSOCIATE_IMAGE_FILE']) && file_exists( $GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/images/' . $this->params_OLAP['ASSOCIATE_IMAGE_FILE'] ) ){
													echo "<img src='".$GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/images/' . $this->params_OLAP['ASSOCIATE_IMAGE_FILE']."' width='250' height='145' border='1'>"; 
												}elseif( isset($this->id_OLAP) ){
													echo "<img src='".$GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/images/defaut.gif' ."' width='250' height='145' border='1'>"; 
												}
											?>
                                  </div></td>
							  </tr>
							  <tr>
                                  <td align="center" style="width:250px"><?php if( is_array($this->params_OLAP) && count($this->params_OLAP) && (isset($this->params_OLAP['ASSOCIATE_DESC_FILE'])) && ($this->params_OLAP['ASSOCIATE_DESC_FILE']<>'') ){
                                                //if( is_array($this->params_OLAP) && count($this->params_OLAP)  ){
													echo "<input style='width:100%;' onClick=\"OpenPopupOlapDoc(".$this->id_OLAP.",'".$this->params_OLAP['ASSOCIATE_DESC_FILE']."');\" type='button'  value='".recherche_libelle_page('doc_olap')."'>";											
												}
											?></td>
								  <td  align="center" style="width:250px"><?php if( is_array($this->params_OLAP) && count($this->params_OLAP) ){
													echo "<input style='width:100%;' onClick=\"OpenPopupOLAP(".$this->id_OLAP.",'".$this->type_sgbd ."');\" type='button'  value='".recherche_libelle_page('view_olap')."'>";											                                                   
												}
											?></td>
							  </tr>							
							 </table>
						</div>
					</span>
			</FORM>			
		<?php }

		
		/**
		* METHODE : run()
		* <pre>
		*  Execution totale
		* </pre>
		* @access public
		* 
		*/
		function run(){
			$this->set_tab_liste_OLAPS(); // liste des instances OLAP existantes
			$this->set_id_OLAP(); // identifier celle qui doit etre affichée
			$this->set_params_OLAP(); // récuperer ses parametres 
			$this->set_conn_OLAP(); // Manager la connexion à la source OLAP (id_OLAP en cours)
			$this->presenter_OLAP(); // gestion de l'affichage
		}

	}
?>
