<?php class excel_file_descrip{
	
			
		/**
		* Attribut : $conn
		* <pre>
		* 	Variable de connexion à la base
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn;
			
		/**
		* Attribut : $langue
		* <pre>
		* 	Langue choisie
		* </pre>
		* @var 
		* @access public
		*/   
		public $langue;
			
		/**
		* Attribut : $nomPage
		* <pre>
		* 	Nom de la page à transmettre à la fonction lit_libelles_page
		* </pre>
		* @var 
		* @access public
		*/   
		public $nomPage;
			
		/**
		* Attribut : $id_theme
		* <pre>
		* 	le thème en cours
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_theme; // 
			
		/**
		* Attribut : $id_type_theme
		* <pre>
		* 	Type du Thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_type_theme;
			
		/**
		* Attribut : $libelle_type_theme
		* <pre>
		* 	libellé du type thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $libelle_type_theme;
			
		/**
		* Attribut : $libelle_theme
		* <pre>
		* 	libellé du thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $libelle_theme;
			
		/**
		* Attribut : $lib_champ_err
		* <pre>
		* libellé associé au champ en cas d'erreur
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_champ_err;
			
		/**
		* Attribut : $order_zones
		* <pre>
		* booléen utilisé pour permettre ou non d'ordonner 
		* les zones par secteur
		* </pre>
		* @var 
		* @access public
		*/   
		public $order_zones = false;
			
		/**
		* Attribut :  $iTab
		* <pre>
		* indice tableau de la table mère  ds TabGestZones['tables_meres']
		* </pre>
		* @var 
		* @access public
		*/   
		public $iTab;  // 
			
		/**
		* Attribut : $iZone
		* <pre>
		* indice tableau de la zone  ds TabGestZones['zones'][$iTab]
		* </pre>
		* @var 
		* @access public
		*/   
		public $iZone;  // // 
		
		/**
		* Attribut : $id_Zone
		* <pre>
		* id de la zone  ds TabGestZones['zones'][$iTab] pour modification
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_zone;  // // 
		
		/**
		* Attribut : $i_tabm
		* <pre>
		* indice de la table mere  ds TabGestZones['zones'] pour modification
		* </pre>
		* @var 
		* @access public
		*/   
		public $i_tabm;	
		
		/**
		* Attribut : $nb_TabM
		* <pre>
		* le nombre total de table mères
		* </pre>
		* @var 
		* @access public
		*/   
		public $nb_TabM;  // // 
			
		/**
		* Attribut : $Action
		* <pre>
		* contient le type d'action à effectuer aprés soumission
		* </pre>
		* @var 
		* @access public
		*/   
		public $Action; // 
			
		/**
		* Attribut : $OkAction
		* <pre>
		* indique le resultat de l'action de MAJ ds la base
		* </pre>
		* @var 
		* @access public
		*/   
		public $OkAction; // 
			
		/**
		* Attribut : $ActMAJ
		* <pre>
		* indique la tenue d'une action de MAJ
		* </pre>
		* @var 
		* @access public
		*/   
		public $ActMAJ;  // 
			
		/**
		* Attribut : $champs_zone
		* <pre>
		* les noms des champs relatifs à la ZONE
		* </pre>
		* @var 
		* @access public
		*/   
		public $champs_zone 	= array(); // 
			
		/**
		* Attribut : $btn_add 
		* <pre>
		* controler l'affichage du bouton Ajouter
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_add 			= false; // 
			
		/**
		* Attribut : $TabValTabM 
		* <pre>
		* Vals tables mères pour l'affichage
		* </pre>
		* @var 
		* @access public
		*/   
		public $TabValTabM 		= array(); // 
			
		/**
		* Attribut : $TabValZone
		* <pre>
		* Vals zones pour l'affichage
		* </pre>
		* @var 
		* @access public
		*/   
		public $TabValZone		= array(); // 
			
		/**
		* Attribut : $TabGestZones
		* <pre>
		* Contient plusieurs valeurs recuperees de la base pour la manip des zones
		* </pre>
		* @var 
		* @access public
		*/   
		public $TabGestZones 	= array(); // 
		
		/**
		* Attribut : $TabBD
		* <pre>
		* Contient les tables de la base de données
		* </pre>
		* @var 
		* @access public
		*/		
		public $TabBD 	= array(); //
		
		/**
		* Attribut : $ColTabBD
		* <pre>
		* Contient les colonnes d'une table de la base de données
		* </pre>
		* @var 
		* @access public
		*/		
		public $ColTabBD 	= array(); //
		
		/**
		* METHODE : 
		* <pre>
		* Constructeur de la classe
		* </pre>
		* @access public
		* 
		*/
		function __construct(){
			$this->conn			= $GLOBALS['conn'];
			$this->langue    	= $_SESSION['langue'];
			$this->nomPage		= '/excel_file_descrip.php';
		}
		
		/**
		* METHODE : init()
		* <pre>
		* Enchaine les fonctions dans le bon ordre de traitement de l'objet instancié
		* </pre>
		* @access public
		* 
		*/
		function init(){
			$this->btn_add 	= false;

			lit_libelles_page($this->nomPage);
			$this->printJS();
			
			$this->get_themes();
			if( count($this->TabGestZones['themes'])){
				$this->gererPost();
				$this->gererAff();				
				$this->setSessions();
			}else{
				 print '<BR><BR><BR> ... No Themes  ... <BR>';
			}
		}
		
		function __wakeup(){
				$this->conn	= $GLOBALS['conn'];
		}
		
		/**
		* METHODE :  setSessions()
		* <pre>
		* Met en session les variables necessaires
		* </pre>
		* @access public
		* 
		*/
		function setSessions(){
				unset($_SESSION['GestZones']);
				
				$_SESSION['GestZones'] = $this->TabGestZones ;
				$_SESSION['GestZones']['id_theme'] = $this->id_theme ;
				$_SESSION['GestZones']['iTab'] = $this->iTab ;
				//echo '<pre>';
				//print_r($_SESSION['GestZones']);
		}
		
		/**
		* METHODE : printJS()
		* <pre>
		* Affiche les fonctions javascript
		* </pre>
		* @access public
		* 
		*/
		function printJS(){
			?>
			<script type="text/javascript" language="javascript">
			 <!-- 
			
				 function AvertirSupp(iTab,iZone,cas_elem,lib_elem){ 
					var TxtAvertSuppZone ="<?php echo $this->TabGestZones['TxtAlert']['AvSupZone']; ?>"; 
					var TxtAvertSuppTabM ="<?php echo $this->TabGestZones['TxtAlert']['AvSupTabM']; ?>"; 
					if(cas_elem=='Zone'){ 
						var TxtAvert = TxtAvertSuppZone + lib_elem ; 
						var Act   = 'SupZone'; 
					} 
					else if(cas_elem=='TabM'){ 
						var TxtAvert = TxtAvertSuppTabM + lib_elem ; 
						var Act   = 'SupTabM'; 
					} 
					if(confirm(TxtAvert)){ 
						Action(iTab,iZone,cas_elem,Act); 
					} 
				 } 
			
				function MessMAJ(action,res_action,lib_champ_err){ 
					//var i = 0; 
					//alert (action + '***' + res_action + '***' + lib_champ_err)
					var lib_champ = '';
					if(action=='AddZone' || action=='AddTabM' ) i = 0; 
					else if(action=='UpdZone' || action=='UpdTabM' ) i = 1;
					else if(action=='SupZone' || action=='SupTabM' ) i = 2									
					if(lib_champ_err != '') lib_champ = ' : (' + lib_champ_err + ')';									
					OK00 = " <?php echo $this->TabGestZones['TxtAlert']['PbIns']; ?> "; 
					OK01 = " <?php echo $this->TabGestZones['TxtAlert']['OkIns']; ?> "; 
					OK10 = " <?php echo $this->TabGestZones['TxtAlert']['PbUpd']; ?> "; 
					OK11 = " <?php echo $this->TabGestZones['TxtAlert']['OkUpd']; ?> "; 
					OK20 = " <?php echo $this->TabGestZones['TxtAlert']['PbSup']; ?> "; 
					OK21 = " <?php echo $this->TabGestZones['TxtAlert']['OkSup']; ?> ";

					var OK	= Array([OK00,OK01],[OK10,OK11],[OK20,OK21]); 
					alert(OK[i][res_action] + lib_champ);
				}
			 //--> 
			</script>
		<?php }
		
		/**
		* METHODE : get_themes()
		* <pre>
		* fonction de récupération des thèmes 
		* </pre>
		* @access public
		* 
		*/
		function get_themes(){
			$requete 	=	" SELECT    DICO_THEME.ID , 
										DICO_THEME.ID_TYPE_THEME , 
										DICO_TYPE_THEME.LIBELLE_TRAD AS LIBELLE_TYPE_THEME ,
										DICO_TRADUCTION.LIBELLE  
										FROM      DICO_THEME, DICO_TYPE_THEME, DICO_TRADUCTION, DICO_THEME_SYSTEME
										WHERE	DICO_THEME.ID_TYPE_THEME=DICO_TYPE_THEME.ID_TYPE_THEME 
										AND 	DICO_TYPE_THEME.CODE_LANGUE = '".$this->langue."'
										AND 	DICO_THEME.ID=DICO_TRADUCTION.CODE_NOMENCLATURE 		
										AND		DICO_TRADUCTION.NOM_TABLE = 'DICO_THEME' 
										AND 	DICO_TRADUCTION.CODE_LANGUE = '".$this->langue."' 
										AND 	DICO_THEME_SYSTEME.ID=DICO_THEME.ID
										AND		DICO_THEME_SYSTEME.ID_SYSTEME=".$_SESSION['secteur']."
										AND 	DICO_THEME.ID_TYPE_THEME NOT IN (1,5,6,7,8)
										ORDER BY DICO_THEME.ORDRE_THEME ";
										//ORDER BY DICO_TRADUCTION.LIBELLE ";
																					
			//echo $requete.'<br>' ;
			$this->TabGestZones['themes']  = $GLOBALS['conn_dico']->GetAll($requete);
			if(!isset($this->id_theme)){
					$this->id_theme 			= $this->TabGestZones['themes'][0]['ID'];
					$this->id_type_theme 		= $this->TabGestZones['themes'][0]['ID_TYPE_THEME'];
					$this->libelle_type_theme 	= $this->TabGestZones['themes'][0]['LIBELLE_TYPE_THEME'];
					$this->libelle_theme		= $this->TabGestZones['themes'][0]['LIBELLE'];
			}
			if(isset($_GET['id_theme_choisi'])){
					$this->id_theme 			= $_GET['id_theme_choisi'];
					foreach($this->TabGestZones['themes'] as $i => $themes){
							if( $themes['ID'] == $_GET['id_theme_choisi'] ){
									$this->id_type_theme 			= $themes['ID_TYPE_THEME'];
									$this->libelle_type_theme = $themes['LIBELLE_TYPE_THEME'];
									$this->libelle_theme 			= $themes['LIBELLE'];
									break;	
							}
					}
			}elseif(isset($_SESSION['theme']) && $_SESSION['theme']!=''){
				$long_syst_id=strlen(''.$_SESSION['secteur']);
				$long_theme_syst_id=strlen(''.$_SESSION['theme']);
				$long_theme_id=$long_theme_syst_id-$long_syst_id;
				$str_theme_id=substr($_SESSION['theme'],0,$long_theme_id);
				$this->id_theme 			= $str_theme_id;
				foreach($this->TabGestZones['themes'] as $i => $themes){
					if( $themes['ID'] == $str_theme_id ){
						$this->id_type_theme 			= $themes['ID_TYPE_THEME'];
						$this->libelle_type_theme = $themes['LIBELLE_TYPE_THEME'];
						$this->libelle_theme 			= $themes['LIBELLE'];
						break;	
					}
				}
			}
			if($this->id_type_theme == 1){
					$this->get_liste_dimensions();
			}
			else{
					$this->get_tables_meres_theme();
			}
			//$this->get_meta_type_zone();
			//$this->get_systemes();
			$this->set_champs_zone();
			//print_r($this->TabGestZones['themes']);
		} //FIN get_themes()
		
		/**
		* METHODE : get_cles()
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		function get_cles(){/// get_systemes()
			// Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
			// TODO: à virer de l'accueil
			//'.$GLOBALS['PARAM'][''].'
			$requete                 = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', D_TRAD.LIBELLE
										FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
										WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
										And D_TRAD.NOM_TABLE="'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'" And D_TRAD.CODE_LANGUE="'.$_GET['langue'].'";';
			$this->TabGestZones['systemes'] = $this->conn->GetAll($requete);
		} // FIN  get_systemes()

		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		function get_systemes(){/// get_systemes()
			// Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
			// TODO: à virer de l'accueil
			$requete                 = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', D_TRAD.LIBELLE
										FROM '.$GLOBALS['PARAM']['TYPE'].'_'.$GLOBALS['PARAM']['SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
										WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
										And D_TRAD.NOM_TABLE="'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'" And D_TRAD.CODE_LANGUE="'.$_GET['langue'].'";';
			$this->TabGestZones['systemes'] = $this->conn->GetAll($requete);
		} // FIN  get_systemes()
		
		/**
		* METHODE : get_liste_dimensions()
		* <pre>
		* fonction de récupération de ttes les dimensions
		* </pre>
		* @access public
		* 
		*/
		function get_liste_dimensions(){/// get_liste_dimensions()
	
			$requete                 = 'SELECT   *  FROM       DICO_DIMENSION';
			$this->TabGestZones['dimensions'] = $GLOBALS['conn_dico']->GetAll($requete);
		} // FIN  get_systemes()
		
		/**
		* METHODE : set_champs_zone()
		* <pre>
		* Préparation des champs de la table DICO_ZONE 
		* </pre>
		* @access public
		* 
		*/
		function set_champs_zone(){///// 
				if(!($this->champs_zone)){
						$this->champs_zone 		= array();
						if($this->id_type_theme <> 1){
								$this->champs_zone[] 	= array( 'nom' => 'ID_ZONE', 					'type' => 'int'		, 'manip' => false, 'lib'=>'', 'obli'=>'1'	);
								$this->champs_zone[] 	= array( 'nom' => 'TABLE_MERE', 			'type' => 'text'	, 'manip' => false, 'lib'=>'', 'obli'=>'1'	);
								$this->champs_zone[] 	= array( 'nom' => 'NOM_GROUPE', 			'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'TABLE_FILLE', 			'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'ORDRE', 						'type' => 'int' 	, 'manip' => true, 'lib'=>'OrdMet', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'SQL_REQ', 							'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'CHAMP_FILS', 			'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'CHAMP_PERE', 			'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'ORDRE_TRI', 				'type' => 'int'		, 'manip' => true, 'lib'=>'OrdTri', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'ID_ZONE_REF', 			'type' => 'int'		, 'manip' => true, 'lib'=>'ZoneRef', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'TYPE_ZONE_BASE', 	'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'LIBELLE', 					'type' => 'text'	, 'manip' => false, 'lib'=>'', 'obli'=>''	);
						}
						else{
								$this->champs_zone[] 	= array( 'nom' => 'ID_ZONE', 							'type' => 'int'		, 'manip' => false, 'lib'=>'', 'obli'=>'1'	);
								$this->champs_zone[] 	= array( 'nom' => 'TABLE_MERE', 					'type' => 'text'	, 'manip' => true, 'lib'=>'TabMere', 'obli'=>'1'	);
								$this->champs_zone[] 	= array( 'nom' => 'MESURE1', 							'type' => 'text'	, 'manip' => true, 'lib'=>'mes1', 'obli'=>'1'		);
								$this->champs_zone[]	= array( 'nom' => 'MESURE2', 							'type' => 'text'	, 'manip' => true, 'lib'=>'mes2', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'TYPE_SAISIE_MESURE1', 							'type' => 'text'	, 'manip' => true, 'lib'=>'mes1', 'obli'=>'1'		);
								$this->champs_zone[]	= array( 'nom' => 'TYPE_SAISIE_MESURE2', 							'type' => 'text'	, 'manip' => true, 'lib'=>'mes2', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'ID_DIMENSION1', 				'type' => 'int' 	, 'manip' => false, 'lib'=>'choixdim', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'ID_DIMENSION2', 				'type' => 'int'		, 'manip' => false, 'lib'=>'choixdim', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'NB_LIGNES_DIMENSION1', 'type' => 'int'		, 'manip' => false, 'lib'=>'nblidim', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'NB_LIGNES_DIMENSION2', 'type' => 'int'		, 'manip' => false, 'lib'=>'nblidim', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'LIBELLE_MESURE1', 			'type' => 'text'	, 'manip' => false, 'lib'=>'libmes1', 'obli'=>''	);
								$this->champs_zone[] 	= array( 'nom' => 'LIBELLE_MESURE2', 			'type' => 'text'	, 'manip' => false, 'lib'=>'libmes2', 'obli'=>''	);
						}
				}
		}
		
		/**
		* METHODE : get_tables_db()
		* <pre>
		* Récupération des tables de la base de données 
		* </pre>
		* @access public
		* 
		*/
		function get_tables_db(){
		
				$this->TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
				sort($this->TabBD);
				$tables = array();
				$deb_nomenc = $GLOBALS['PARAM']['TYPE'].'_';
				$deb_fact_tab = 'FACT_TABLE_';
				$fin_nomenc_system = '_'.$GLOBALS['PARAM']['SYSTEME'];
				foreach($this->TabBD as $tab)
				{
					if(!preg_match("/^($deb_fact_tab)/",strtoupper($tab)) && !preg_match("/^($deb_nomenc)/",strtoupper($tab)) && !preg_match("/($fin_nomenc_system)$/",strtoupper($tab)) && !preg_match("/^(DICO_)/",strtoupper($tab)) && !preg_match("/^(ADMIN_DROITS)/",strtoupper($tab)) && !preg_match("/^(ADMIN_GROUPES)/",strtoupper($tab)) && !preg_match("/^(ADMIN_USERS)/",strtoupper($tab)) && !preg_match("/^(PARAM_DEFAUT)/",strtoupper($tab)) && !preg_match("/^(SYSTEME)/",strtoupper($tab)))
					{
						$tables[] = $tab;
					}
				}
				$this->TabBD = $tables;
				
		} //FIN get_tables_db()
		
		
		/**
		* METHODE : get_col_table_db()
		* <pre>
		* Récupération des colonnes d'une table de la base de données 
		* </pre>
		* @access public
		* 
		*/
		function get_col_table_db($table){
		
				$this->ColTabBD	=	$this->conn->MetaColumnNames($table);
				
		} //FIN get_tables_db()
		
		function get_col_table_db_2($table){
		
				$this->ColTabBD	=	$this->conn->MetaColumns($table);
				
		} //FIN get_tables_db_2()
		
		/**
		* METHODE : get_tables_meres_theme()
		* <pre>
		* Récupération des tables mères du thème
		* </pre>
		* @access public
		* 
		*/
		function get_tables_meres_theme(){
				$requete 	=	' SELECT    ID_TABLE_MERE_THEME , 
											NOM_TABLE_MERE , 
											PRIORITE 
											FROM       DICO_TABLE_MERE_THEME
											WHERE      ID_THEME ='.$this->id_theme.' 
											ORDER BY 	 PRIORITE';
											//WHERE      ID_THEME ='.$this->id_theme.' AND NOM_TABLE_MERE <>\''.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'\' 
											
				$this->TabGestZones['tables_meres']  = $GLOBALS['conn_dico']->GetAll($requete);
				$this->nb_TabM = count($this->TabGestZones['tables_meres']);
				
		} //FIN get_tables_meres_theme()
		
		
		/**
		* METHODE : exist_tables_meres_theme()
		* <pre>
		* permet de déterminer l'existence ou non de tables mères pour le thème
		* </pre>
		* @access public
		* 
		*/
		function exist_tables_meres_theme(){
			$exist_TabM = false ;
			$requete 	= ' SELECT  * FROM  DICO_ZONE WHERE  ID_THEME = ' . $this->id_theme;
			$all_test   = $GLOBALS['conn_dico']->GetAll($requete);
			if(is_array($all_test) and count($all_test)){
				$exist_TabM = true ;
			}
			return($exist_TabM);
			//$this->nb_TabM = count($this->TabGestZones['tables_meres']);
		} 	//FIN exist_tables_meres_theme()

		///// fonction de récupération des zones du theme
		
		/**
		* METHODE : get_zone_matrice()
		* <pre>
		*  Récupère les caractéristiques de la matrice
		* </pre>
		* @access public
		* 
		*/
		function get_zone_matrice(){
		
				$requete	=	"	SELECT     *
									FROM       DICO_ZONE, DICO_DIMENSION_ZONE 
									WHERE      DICO_ZONE.ID_ZONE = DICO_DIMENSION_ZONE.ID_ZONE
									AND		   DICO_ZONE.ID_THEME = " . $this->id_theme;
				//echo $requete;						
				$all_res  = $GLOBALS['conn_dico']->GetAll($requete);
				$this->TabGestZones['zone_matrice'] = array();
				if( count($all_res) > 0 ){
						$this->TabGestZones['zone_matrice'] = $all_res;
						$this->get_libelles_zone_matrice();
						$this->get_dimensions_matrice();
						$this->TabGestZones['zones'][0][0] = $all_res[0];
				}
				else{
						$this->btn_add = true; 
				}
				
				//echo'<pre>';
				//print_r($this->TabGestZones['zones']);		
		} //FIN get_zones()
		
		
		/**
		* METHODE : get_libelles_zone_matrice()
		* <pre>
		* Récupère les libellés associés à la zone matrice
		* </pre>
		* @access public
		* 
		*/
		function get_libelles_zone_matrice(){
				$id_zone			= $this->TabGestZones['zone_matrice'][0]['ID_ZONE'];
				$elem_trad		= $this->recherche_libelles_matrice($id_zone, $this->langue, 'DICO_ZONE');
				$this->TabGestZones['libelles_zone_matrice']['LIBELLE'] 		= $elem_trad['LIBELLE'];
				$this->TabGestZones['libelles_zone_matrice']['LIBELLE_MESURE1'] = $elem_trad['LIBELLE_MESURE1'];
				$this->TabGestZones['libelles_zone_matrice']['LIBELLE_MESURE2'] = $elem_trad['LIBELLE_MESURE2'];
		} //FIN get_zones()

		
		/**
		* METHODE : recherche_libelle_bouton($code, $langue, $page)
		* <pre>
		*  permet de récupérer le libellé dans la table de traduction
		*  en fonction de la langue et de la table  aussi
		* </pre>
		* @access public
		* 
		*/
		function recherche_libelle_bouton($code, $langue, $page){
				// $page = 'generer_theme.php' ou gestion_zone.php
				$requete 	= " SELECT LIBELLE
								FROM DICO_LIBELLE_PAGE 
								WHERE CODE_LIBELLE='".$code."' AND CODE_LANGUE='".$this->langue."'
								AND NOM_PAGE='".$page."'";
												
				$all_res	= $GLOBALS['conn_dico']->GetAll($requete);                 
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
		
		/**
		* METHODE : recherche_libelle($code,$langue,$table)
		* <pre>
		* permet de récupérer le libellé dans la table de traduction
		* en fonction de la langue et de la table  aussi
		* </pre>
		* @access public
		* 
		*/
		function recherche_libelle($code,$langue,$table){
				
				if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					$conn                 =   $GLOBALS['conn'];
				} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					$conn                 =   $GLOBALS['conn_dico']; 
				}
				
				$requete 	= "SELECT LIBELLE
										FROM DICO_TRADUCTION 
										WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
										AND NOM_TABLE='".$table."'";
				
				//echo $requete;
				//print_r($this->conn);
				$all_res	= $conn->GetAll($requete); 
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
		
		/**
		* METHODE : recherche_libelles_matrice($code,$langue,$table)
		* <pre>
		* permet de récupérer les libellés zone et mesure dans la table de traduction
		* en fonction de la langue et de la table  aussi
		* </pre>
		* @access public
		* 
		*/
		function recherche_libelles_matrice($code,$langue,$table){
			if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
				$conn                 =   $GLOBALS['conn'];
			} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
				$conn                 =   $GLOBALS['conn_dico']; 
			}
			$requete 	= " SELECT LIBELLE, LIBELLE_MESURE1, LIBELLE_MESURE2
							FROM DICO_TRADUCTION 
							WHERE CODE_NOMENCLATURE=".$code." AND CODE_LANGUE='".$langue."'
							AND NOM_TABLE='".$table."'";
			$all_res	= $conn->GetAll($requete); 
			return($all_res[0]);
		}

		
		/**
		* METHODE : SuppCascadeZone($id_zone)
		* <pre>
		*  fonction de suppression en cascade de la zone
		* </pre>
		* @access public
		* 
		*/
		function SuppCascadeZone($id_zone){ 
			/// Suppression dans DICO_ZONE
			$requete 	= ' DELETE    FROM DICO_ZONE WHERE  ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_ZONE_SYSTEME
			$requete 	= ' DELETE    FROM DICO_ZONE_SYSTEME  WHERE  ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_REGLE_ZONE_ASSOC
			$requete 	= ' DELETE    FROM DICO_REGLE_ZONE_ASSOC  WHERE  ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';

			/// Suppression dans DICO_ZONE_JOINTURE
			$requete 	= ' DELETE    FROM DICO_ZONE_JOINTURE  WHERE  N_JOINTURE IN
										(SELECT DISTINCT N_JOINTURE FROM DICO_ZONE_JOINTURE 
										 WHERE ID_ZONE = '.$id_zone.' AND ID_THEME = '.$this->id_theme.')';
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_DIMENSION_ZONE CAS : MATRICE
			$requete 	= ' DELETE    FROM   DICO_DIMENSION_ZONE 
										 WHERE ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_INDEXES CAS : MATRICE
			$requete 	= ' DELETE    FROM   DICO_INDEXES 
										 WHERE ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_TRADUCTION
			$requete 	= 'DELETE    FROM   DICO_TRADUCTION 
									 WHERE CODE_NOMENCLATURE = '.$id_zone.
									' AND NOM_TABLE = \'DICO_ZONE\''; 

			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
		}

		
		/**
		* METHODE : InsertTabMere($iTab)
		* <pre>
		* Création table mére sous l'indice iTab
		* </pre>
		* @access public
		* 
		*/
		function InsertTabMere($iTab){ /// 
			//$this->ActMAJ = 1;
			// get le ID_TABLE_MERE_THEME à attribuer
			// ID_THEME = $this->id_theme
			// NOM_TABLE_MERE = NOM_TABLE_MERE_$iTab
			// PRIORITE = PRIORITE_$iTab
			$this->ActMAJ = 1;
			$iTab = $this->iTab;

			if (!preg_match ("/^(0|([1-9][0-9]*))$/", trim($_POST['PRIORITE_'.$iTab]))){
					$this->OkAction = 0;
					//return 0;
					//echo 'passe 1';
			}
			if ( trim($_POST['NOM_TABLE_MERE_'.$iTab] =='') ){
					$this->OkAction = 0;
					//return 0;
					//echo 'passe 2';
			}
			// on verifie l'existence de la table mère et de la priorité 
			$req_exist = 'SELECT *		FROM   DICO_TABLE_MERE_THEME
									 WHERE ID_THEME = '.$this->id_theme.'
									 AND ( NOM_TABLE_MERE = \''.trim($_POST['NOM_TABLE_MERE_'.$iTab]).'\' 
												 OR PRIORITE = '.trim($_POST['PRIORITE_'.$iTab]).')';
			//echo $req_exist . '<br>';
			$exists 	= $GLOBALS['conn_dico']->GetAll($req_exist);
			if( is_array($exists) and (count($exists) > 0) ){
					$this->OkAction = 0;
					//echo 'passe 3';
			}
			
			if($this->OkAction == 1){ // s'il n'y a  aucun PB dans les données postées
					$req_max = 'SELECT     MAX(ID_TABLE_MERE_THEME) AS Max_Ins
											FROM       DICO_TABLE_MERE_THEME';
					$ID_TABLE_MERE_THEME 	=	$GLOBALS['conn_dico']->getOne($req_max) + 1 ;
					$requete 	= ' INSERT INTO DICO_TABLE_MERE_THEME
												(ID_TABLE_MERE_THEME, ID_THEME, NOM_TABLE_MERE, PRIORITE)
												VALUES ('.$ID_TABLE_MERE_THEME.','.$this->id_theme.',\''.
												trim($_POST['NOM_TABLE_MERE_'.$iTab]).'\','.trim($_POST['PRIORITE_'.$iTab]).')';      
							//
					if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
					if ($this->OkAction == 0) echo $requete.'<br>';
			}
		}
		
		
		/**
		* METHODE : UpdateTabMere($iTab)
		* <pre>
		* Mise à jour table mére d'indice iTab
		* </pre>
		* @access public
		* 
		*/
		function UpdateTabMere($iTab){ /// 
				$this->ActMAJ = 1;
				$iTab = $this->iTab;

				if (!preg_match ("/^(0|([1-9][0-9]*))$/", trim($_POST['PRIORITE_'.$iTab]))){
						$this->OkAction = 0;
						//return 0;
				}
				if ( trim($_POST['NOM_TABLE_MERE_'.$iTab]) ==''){
						$this->OkAction = 0;
						//return 0;
				}
				if($this->OkAction == 1){ // s'il n'y a pas deja une erreur dans les données postées
						$requete 	= ' UPDATE    DICO_TABLE_MERE_THEME
													SET      	PRIORITE ='.trim($_POST['PRIORITE_'.$iTab]).',
																		NOM_TABLE_MERE =\''.trim($_POST['NOM_TABLE_MERE_'.$iTab]).'\'       
													WHERE     ID_THEME = '.$this->id_theme.'
													AND 			ID_TABLE_MERE_THEME = '.$this->TabGestZones['tables_meres'][$iTab]['ID_TABLE_MERE_THEME'];
								//
						if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
						if ($this->OkAction == 0) echo $requete.'<br>';
				}
				if($this->OkAction == 1){
						$requete 	= ' UPDATE    DICO_ZONE
													SET      	PRIORITE ='.$_POST['PRIORITE_'.$iTab].',
																		TABLE_MERE =\''.$_POST['NOM_TABLE_MERE_'.$iTab].'\'       
													WHERE     ID_THEME = '.$this->id_theme.'
													AND 			ID_TABLE_MERE_THEME = '.$this->TabGestZones['tables_meres'][$iTab]['ID_TABLE_MERE_THEME'];
								//
						if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
						if ($this->OkAction == 0) echo $requete.'<br>';
				}
 		}
		
		/**
		* METHODE : DeleteTabMere($iTab)
		* <pre>
		* Suppression table mére d'indice iTab
		* </pre>
		* @access public
		* 
		*/
		function DeleteTabMere($iTab){ /// 
				$this->ActMAJ = 1;
				$iTab = $this->iTab;
				$id_tab_mere_supp = $this->TabGestZones['tables_meres'][$iTab]['ID_TABLE_MERE_THEME'];
				
				$req_zones_supp 	= 'SELECT ID_ZONE FROM DICO_ZONE 
														 WHERE ID_THEME = '.$this->id_theme.'
														 AND ID_TABLE_MERE_THEME = '.$id_tab_mere_supp;
				$tab_zones_supp   =	$GLOBALS['conn_dico']->GetAll($req_zones_supp);
				$requete 	= ' DELETE    FROM DICO_TABLE_MERE_THEME
											WHERE     ID_THEME = '.$this->id_theme.'
											AND 			ID_TABLE_MERE_THEME = '.$this->TabGestZones['tables_meres'][$iTab]['ID_TABLE_MERE_THEME'];
						//
				if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
				if ($this->OkAction == 0) echo $requete.'<br>';
				
				if(is_array($tab_zones_supp)){
						foreach($tab_zones_supp as $zone){
								$this->SuppCascadeZone($zone['ID_ZONE']);
						}
				}				
		}
		
		/**
		* METHODE : DeleteZone( $iZone, $iTab)
		* <pre>
		* Suppression zone n°iZone de la table mére n°iTab 
		* </pre>
		* @access public
		* 
		*/
		function DeleteZone( $iZone, $iTab){ /// 
			$this->ActMAJ = 1;
			if($this->id_type_theme == 1){
				$id_zone_supp	= $this->TabGestZones['zone_matrice'][0]['ID_ZONE'];
			}
			else{
				$iTab 	= $this->iTab;
				$iZone 	= $this->iZone;
				$id_zone_supp =	$this->TabGestZones['zones'][$iTab][$iZone]['ID_ZONE'];
			}
			$this->SuppCascadeZone($id_zone_supp);
		}
		
		
		/**
		* METHODE : setValTabM()
		* <pre>
		* Gére les valeurs d'affichage pour les tables mères
		* </pre>
		* @access public
		* 
		*/
		function setValTabM(){
			$this->TabValTabM = array();
			if( ($_POST['ActionTabM']=='AddTabM') or ($_POST['ActionTabM']=='UpdTabM') ){
				//$mat_val	= $_POST;
				/*
				for( $iTab=0 ; $iTab <= $this->nb_TabM ; $iTab++ ){
					$this->TabValTabM['PRIORITE_'.$iTab] 			 	= $_POST['PRIORITE_'.$iTab];
					$this->TabValTabM['NOM_TABLE_MERE_'.$iTab]  = $_POST['NOM_TABLE_MERE_'.$iTab];
				}
				*/
				if(is_array($this->TabGestZones['tables_meres']))
				foreach($this->TabGestZones['tables_meres'] as $iTab => $table_mere){
					$this->TabValTabM['PRIORITE_'.$iTab] 			 = $table_mere['PRIORITE'];
					$this->TabValTabM['NOM_TABLE_MERE_'.$iTab]  = $table_mere['NOM_TABLE_MERE'];
				}
					
			}
			else{
				//$mat_val	= $this->TabGestZones['tables_meres'];
				if(is_array($this->TabGestZones['tables_meres']))
				foreach($this->TabGestZones['tables_meres'] as $iTab => $table_mere){
					$this->TabValTabM['PRIORITE_'.$iTab] 			 = $table_mere['PRIORITE'];
					$this->TabValTabM['NOM_TABLE_MERE_'.$iTab]  = $table_mere['NOM_TABLE_MERE'];
				}
				$this->TabValTabM['PRIORITE_'.($iTab + 1)]				= '';
				$this->TabValTabM['NOM_TABLE_MERE_'.($iTab + 1)]	= '';
			}
		}
		
		function gererPost(){
			//echo '<pre>';
			//print_r($_POST);
			if (isset($_POST['btn_genere'])){ 
				
				set_time_limit(0);
				ini_set("memory_limit", "64M");
				
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/excel_db_structure.class.php';
			 
				$excel_db_struct = new excel_db_structure();
				
				echo "<script language=\"JavaScript\" type=\"text/javascript\">
					alert('".recherche_libelle_page('excel_file_manag_gen_ok')."')
					</script>";
				
    		}
		}
		
		/**
		* METHODE : gererAff()
		* <pre>
		* fonction de gestion de l'affichage du Formulaire
		* </pre>
		* @access public
		* 
		*/
		function gererAff(){
		?>
				<FORM name="Formulaire"  method="post" action="administration.php?val=excel_file_descrip">
				<br>
				<DIV>
				<table class="center-table">
				<caption><?php echo recherche_libelle_page('GestExcel');?></caption>
				<tr>
					<td>
					<div name="DivChoixThemes" id="DivChoixThemes"> 
						<table>
						<tr> 
							<td nowrap> <?php echo recherche_libelle_page('sector'); ?></td>
							<td nowrap><b>
							<?php
							foreach ($_SESSION['tab_secteur'] as $k => $v){
								if ($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $_SESSION['secteur']){
									echo $v['LIBELLE'];
								}
							} 
							?></b></td>
						</tr>
						<tr> 
								<td nowrap> <?php echo recherche_libelle_page('ChoixThm'); ?></td>
						  <td><select name="id_theme" onchange="ChangerThemeExcel(id_theme.value);">
							<?php foreach ($this->TabGestZones['themes'] as $i => $themes){
									echo "<option value='".$themes['ID']."'";
									if ($themes['ID'] == $this->id_theme){
											echo " selected";
									}
									echo ">".$themes['LIBELLE']."</option>";
							}
					?>
						  </select></td>
						</tr>
										<?php if( isset($this->id_theme)){ ?>
										 <?php } ?>	
						<tr> 
								<td nowrap> <?php echo recherche_libelle_page('NumThm'); ?></td>
								<td nowrap><?php echo $this->id_theme; ?></td>
						</tr>
						<tr> 
								<td nowrap> <?php echo recherche_libelle_page('libtypth'); ?></td>
								<td nowrap><?php echo $this->libelle_type_theme; ?></td>
						</tr>
				</table>
				<br/>
				<table style="width:100%;">
				<tr> 
								<td>
								<input style="width:100%;" type="submit" name="btn_genere" <?php echo ' value="'.recherche_libelle_page('genere').'"'; ?>>
								</td>
								
				  </tr>
				</table>
							</div>
						</td>
						<?php if($this->id_type_theme <> 1){
						?>
						<td  align="right" valign="middle" rowspan="4">
								<SPAN class=""> 
									
									<div name="DivGestTablesMeres0" id="DivGestTablesMeres0">
											<table>
													<tr> 
															<td align="center"><b><?php echo recherche_libelle_page('TabMere'); ?></b></td>
															<td colspan="2" align="center"><b><?php echo recherche_libelle_page('Actions'); ?></b></td>
													</tr>
													<?php $this->setValTabM(); // elements tab mere à afficher sur l'ecran
															if(is_array($this->TabGestZones['tables_meres']))
															foreach($this->TabGestZones['tables_meres'] as $iTab => $table_mere){
																echo "
																	<tr id='TableMere".$iTab."' onClick=\"MiseEvidenceTablesMeres('".$iTab."')\"> 
																			<td>
																				<input name='NOM_TABLE_MERE_".$iTab."' id='NOM_TABLE_MERE_".$iTab."' style='width:200px' value='".$table_mere['NOM_TABLE_MERE']."' readonly='1' />";
																echo "</td>
																			<td><INPUT type='button' name='' value='".recherche_libelle_page('col_row_desc')."' 
																			onclick=\"javascript:MiseEvidenceTablesMeres('".$iTab."'); javascript:OpenPopupExcelTables('".$table_mere['NOM_TABLE_MERE']."','".$this->id_theme."','".$this->id_type_theme."','".$_SESSION['secteur']."');\" /></td>
																	</tr>";		
															}
															
															?>
													</table>
												</div>
										</SPAN>
								</td>
								<?php }
								?>
						</tr>
						
						<!--
						<tr>
							<td align="center">
								<input type='button' name='' <?php //echo"value='".recherche_libelle_page(GestRegZ)."'";?>
											 onClick="OpenPopupGestRegZone();">
							</td>
						</tr>
						-->
						<?php if($this->exist_tables_meres_theme() == false){ ?>
						<?php }?>

						
				</table>

				<INPUT type="hidden" id="ActionZone" name="ActionZone">
				<INPUT type="hidden" id="ActionTabM" name="ActionTabM">
				<INPUT type="hidden" id="ZoneActive" name="ZoneActive">
				<INPUT type="hidden" id="TabMActive" name="TabMActive">
			</DIV>
			</FORM>
			<?php }
		
}	// fin class 
?>

