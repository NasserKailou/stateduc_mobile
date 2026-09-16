<?php class gestion_table_simple{

			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $langue;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $nom_page_class;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $i_enr							= 0;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $total_enr					= 0;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $i_cle 						= 0;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $combo_visible			= true;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $nom_champ_combo;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $frame;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $code_libelle_trad;
		
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_new						= false;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_add						= false;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_upd						= false;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_del						= false;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_first					= false;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_last					= false;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_prev					= false;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_next					= false;
		
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $table;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $table_trad;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champs 						= array();
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $tab_champs_trad   = array();
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $sql_order_by;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $sql_criteres_filtre;
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $action; // contient le type d'action à effectuer aprés soumission
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $ok_action; // indique le resultat de l'action de MAJ ds la base
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $act_MAJ;  // indique la tenue d'une action de MAJ
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_champ_err;

			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $donnees 					= array();
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $val_champ 				= array();
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $text_alert 				= array();
		
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $taille_ecran			= '95%';
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_quit					= true;
		
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $titre_ecran			= '';
		

		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function __construct(){

				$this->conn						= $GLOBALS['conn'];
				$this->langue    			= $_SESSION['langue'];
				
				//$this->pas						= $pas;
				$this->i_enr					= 0;
				$this->btn_ann				= true;
				$this->btn_next				= false;
				$this->btn_add				= false;
				$this->btn_upd				= false;
				$this->btn_del				= false;
				$this->btn_first			= false;
				$this->btn_last				= false;
				$this->btn_prev				= false;
				$this->btn_next				= false;
				
				$this->taille_ecran		= '95%';
				$this->btn_quit				= true;
				
				$this->combo_visible	= true;
				$this->nom_page_class	= 'gestion_table_simple.class.php';
				
				$this->tab_champs_trad=	array(); 
				//$this->btn_pas_next	= false;
				//$this->btn_pas_prev	= false;
				////
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function run(){

				lit_libelles_page('/'.$this->frame);
				$this->print_JS();
				$this->get_donnees();
				$this->gererPost();
				$this->ctrl_btn_action();
				$this->set_val_champ();
				$this->affiche_template();
				$this->alerte_MAJ();
				$this->reload_if_delete();
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function init_page(){
				?>
				<script type="text/javascript">
					<!--
						init();
					-->
				</script>

		<?php //header('Location: '.$PHP_SELF);
				//exit();
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
				$this->conn	= $GLOBALS['conn'];
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_champ_extract($nom_champ){
				$champ_extract = $nom_champ;
				if (strlen($nom_champ)>30) {
						if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql') { 
								$taille_max_extract=30;                
						}else if ($this->conn->databaseType == 'postgres9') {
								$taille_max_extract=strlen($nom_champ);
						}else{
								$taille_max_extract=31;                
						}
						$champ_extract = substr($nom_champ ,0,$taille_max_extract); 
				}
				return($champ_extract);
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function recherche_libelle($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					$conn                 =   $GLOBALS['conn'];
				} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					$conn                 =   $GLOBALS['conn_dico']; 
				}
				$requete 	= "SELECT LIBELLE
										FROM DICO_TRADUCTION 
										WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
										AND NOM_TABLE='".$table."'";
				
				//echo $requete.'<br><br>';
				//print_r($this->conn);
				
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$all_res	= $conn->GetAll($requete); 
						if(!is_array($all_res)){                    
								throw new Exception('ERR_SQL');  
						} 
					return($all_res[0]['LIBELLE']);										
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
		}
		
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function recherche_libelle_bouton($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				$requete 	= "SELECT LIBELLE
										FROM DICO_LIBELLE_PAGE 
										WHERE CODE_LIBELLE='".$code."' And CODE_LANGUE='".$langue."'
										AND NOM_PAGE='".$table."'";
				//echo '<br>'.$requete ;
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$all_res	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($all_res)){                    
								throw new Exception('ERR_SQL');  
						} 
						return($all_res[0]['LIBELLE']);									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
		}
				
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_text_alert(){
				if(!($this->text_alert)){
						$this->text_alert['AvSup'] 			= $this->recherche_libelle(200,$this->langue,'DICO_MESSAGE');
						
						$this->text_alert['OkIns']			= $this->recherche_libelle(203,$this->langue,'DICO_MESSAGE');
						$this->text_alert['OkUpd'] 			= $this->recherche_libelle(204,$this->langue,'DICO_MESSAGE');
						$this->text_alert['OkSup'] 			= $this->recherche_libelle(205,$this->langue,'DICO_MESSAGE');
						
						$this->text_alert['PbIns'] 			= $this->recherche_libelle(206,$this->langue,'DICO_MESSAGE');
						$this->text_alert['PbUpd'] 			= $this->recherche_libelle(207,$this->langue,'DICO_MESSAGE');
						$this->text_alert['PbSup'] 			= $this->recherche_libelle(208,$this->langue,'DICO_MESSAGE');
				}
				//echo'<pre>';
				//print_r($this->text_alert);
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function print_JS(){
				$this->get_text_alert();
				?>
				<script type="text/javascript">
					<!--
							function load_action(i_enr,do_act){
									document.getElementById( 'action' ).value 	= do_act;
									document.getElementById( 'i_action' ).value = i_enr;
									document.Formulaire.submit();	
							}
							
							function avertir_supp(i_enr){ 
									var text_avert ="<?php echo $this->text_alert['AvSup']; ?>"; 
									if(confirm(text_avert)){ 
											load_action(i_enr,'Del'); 
									} 
							} 
							function mess_MAJ(action,res_action,lib_champ_err){ 
									//var i = 0; 
									//alert (action + '***' + res_action + '***' + lib_champ_err)
									var lib_champ = '';
									if(action=='Add') i = 0; 
									else if(action=='Upd' ) i = 1; 
									else if(action=='Del' ) i = 2; 
									if(lib_champ_err != '') lib_champ = ' : (' + lib_champ_err + ')';									
									OK00 = " <?php echo $this->text_alert['PbIns']; ?> "; 
									OK01 = " <?php echo $this->text_alert['OkIns']; ?> "; 
									OK10 = " <?php echo $this->text_alert['PbUpd']; ?> "; 
									OK11 = " <?php echo $this->text_alert['OkUpd']; ?> "; 
									OK20 = " <?php echo $this->text_alert['PbSup']; ?> "; 
									OK21 = " <?php echo $this->text_alert['OkSup']; ?> ";
 
									var OK	= Array([OK00,OK01],[OK10,OK11],[OK20,OK21]); 
									alert(OK[i][res_action] + lib_champ);
							}
							
							function init() {
									location.href   = '?<?php echo $_SERVER['QUERY_STRING']; ?>';
							}

					-->
				</script>
				<?php }
		
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_sql_criteres_filtre(){
				$sql = '';
				$criteres_filtre = array();
				$and = false ;
				foreach($this->champs as $champ){
						if( (trim($champ['filtre']) <> '') and (trim($champ['val']) <> '') ){
								$chaine_eval = "\$val = ".trim($champ['val']).";";
								eval($chaine_eval);
								if($and == false){
										$sql .= "\n".' WHERE ';
								}else{
										$sql .= "\n".' AND ';
								}
								$and = true;
								if($champ['type']=='int'){
										$sql .= $champ['nom'].'=' . trim($val);
								}
								elseif($champ['type']=='text'){
										$sql .= $champ['nom'] . '=' . $this->conn->qstr(trim($val)) ;
								}
						}
				}
				$this->sql_criteres_filtre = $sql;
		}

		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_sql_order_by(){
				$tab = array();
				$sql ='';
				foreach($this->champs as $i => $champ){
						if( (trim($champ['ordre']) <> '') ){
								$tab[$i] = $champ['ordre'];
						}
				}
				if( count($tab) > 0 ){
						asort($tab);
						$virg = false;
						foreach( $tab as $i => $val ){
								if( $virg == false ){
										$sql .= "\n".' ORDER BY ';
								}
								else{
										$sql .= ', '."\n";
								}
								$virg = true;
								$sql .= $this->champs[$i]['nom'];
						}
				}
				$this->sql_order_by = $sql;				
		}
				
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_donnees(){
				$this->donnees = array();
				$this->get_sql_criteres_filtre();
				$this->get_sql_order_by();
				//$champ_cle = $this->champ[$this->i_cle]['nom'];
				$requete 	=	'SELECT * FROM '.$this->table;
				$requete 	.= $this->sql_criteres_filtre;
				$requete 	.= $this->sql_order_by;
				//echo $requete;
				
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$this->donnees  = $this->conn->GetAll($requete);
						if(!is_array($this->donnees)){                    
								throw new Exception('ERR_SQL');  
						} 
						$this->total_enr = count($this->donnees);									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
								
				//echo '<br>nb ='.$this->total_enr;
				//echo'DONNEES<pre>';
				//print_r($this->donnees);
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function set_i_enr_of_insert(){
				if( $this->total_enr > 0){
						$this->i_enr = $this->total_enr - 1 ;
						
						foreach( $this->donnees as $i_enr => $enr ){
								$find = true ;
								foreach( $this->champs as $i => $champ ){
										if( (trim($enr[$this->get_champ_extract($champ['nom'])])) <> (trim($_POST[$champ['nom']])) ){
												$find = false ;
												break ;
										}
								}
								if( $find == true ){
										$this->i_enr = $i_enr ;
										break ;
								}
						}
				}
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function gererPost(){
				$this->ok_action 	= 1;
				if( (isset($_POST['action'])) and ($_POST['action'] <> '') ){ // actions
						
						$this->action	 = $_POST['action'];
						
						if( $this->action == 'New' ){
								//$this->Insert( $_POST['Active'], $_POST['TabMActive'] );
								$this->btn_add = true;
								unset($this->i_enr);
						}
						elseif( $this->action == 'Add'){
								$this->Insert();
								unset($this->i_enr);
						}
						elseif( $this->action == 'Upd'){
								$this->i_enr	 = $_POST['i_action'];
								$this->Update();
						}
						elseif( $this->action == 'UpdRptSQL'){
								$this->i_enr	 = $_POST['i_action'];
								
								include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/create_query.class.php';
								$req_rpt_sql = new create_query($this->donnees[$this->i_enr]['ID']);
								$sql_rpt = $req_rpt_sql->creer_query();
								//echo "<pre>";
								//print_r($this->sql);die;$this->donnees[$this->i_enr]['ID']
								$req_upd_sql = 'UPDATE DICO_REPORT SET SQL_REPORT =\''.$sql_rpt.'\' WHERE ID  = '.$this->donnees[$this->i_enr]['ID'].'';
								if($GLOBALS['conn_dico']->Execute($req_upd_sql) === false) echo '<br><font color=red> error updating Report SQL </font><br>'.$req;
								$this->get_donnees();
						}
						elseif( $this->action == 'Del'){
								$this->i_enr	 = $_POST['i_action'];
								$this->Delete();
						}
						elseif( $this->action == 'Open'){
								$this->i_enr	 = $_POST['i_action'];
								//
						}
						if( ($this->act_MAJ == 1) and ($this->ok_action == 1) ){ // s'il une action sur la BDD est effectuée
								$this->get_donnees(); // on recharge les données 
								if($this->action == 'Add'){
										$this->set_i_enr_of_insert();
								}
						}
				}// FIN actions sur 
				else{
						// debut 
				}
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function ctrl_btn_action(){
			//	echo '<br>total_enr='.$this->total_enr ;
				if( $this->total_enr == 0 ){ // pas d'enr
						$this->btn_add			= true;
						$this->btn_ann			= false;
						//unset($this->i_enr);
				}
				else{ // il y'a des enr ds la base
						if( (!isset($this->i_enr)) or (trim($this->i_enr) == '') ){
								$this->i_enr = 0; // on va au premier enr 
						}
						else{
								if($this->btn_add <> true){ // s'i ne s'agit pas d'un ajout
										$this->btn_new			= true;
										$this->btn_upd			= true;
										$this->btn_del			= true;
								}
								if( $this->i_enr > 0 ){
										$this->btn_prev			= true;
										$this->btn_first		= true;
								}
								if( $this->i_enr < ($this->total_enr - 1) ){
										$this->btn_next			= true;
										$this->btn_last			= true;
								}
						}
				}
		}
		
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function set_val_champ(){

				if( $this->action == 'New' or ($this->total_enr == 0) ){
						foreach($this->champs as $champ){
								if( isset($champ['incr']) and ($champ['incr'] == 1) and ($champ['type'] == 'int') ){
										$req_max = 'SELECT     MAX('.$champ['nom'].') AS max_val
																FROM       '.$this->table;
										
										// Traitement Erreur Cas : Execute / GetOne
										try {            
												$val_max 	=	$this->conn->getOne($req_max) ;
												if($val_max === false){                
														 throw new Exception('ERR_SQL');   
												}
												$val_incr 	=	$val_max + 1 ;
												$this->val_champ[$champ['nom']]	= $val_incr;								 
										}
										catch (Exception $e) {
												 $erreur = new erreur_manager($e,$req_max);
										}        
										// Fin Traitement Erreur Cas : Execute / GetOne
										
								}
								elseif( isset($champ['val']) and trim($champ['val']) <> '' ){
										$chaine_eval = "\$val = ".trim($champ['val']).";";
										eval($chaine_eval);
										$this->val_champ[$champ['nom']]	= $val;
								}
						}
						unset($this->i_enr);
				}
				elseif( $this->action == 'Add' or $this->action == 'Upd' ){
						foreach($this->champs as $champ){
								$this->val_champ[$champ['nom']]	= $_POST[$champ['nom']];
						}
						/*if ( ($this->ok_action == 1) and (isset($this->code_libelle_trad)) and (trim($this->code_libelle_trad) <> '') ){
								$this->val_champ['LIBELLE_TRAD']	= $_POST['LIBELLE_TRAD'];
						}*/
						if ( ($this->ok_action == 1) and (count($this->tab_champs_trad) > 0)  ){
								foreach($this->tab_champs_trad as $champ => $champs_trad){
										$this->val_champ[$champs_trad['libelle']]	= $_POST[$champs_trad['libelle']];
								}
						}
				}
				elseif( isset($this->i_enr) and trim($this->i_enr) <>'' ){
						$donnees_enr	= $this->donnees[$this->i_enr];
						//echo '<pre>';
						//print_r($donnees_enr);
						foreach($this->champs as $champ){
								$this->val_champ[$champ['nom']]	= $donnees_enr[$this->get_champ_extract($champ['nom'])];
						}
						/*
						if ( ($this->ok_action == 1) and (isset($this->code_libelle_trad)) and (trim($this->code_libelle_trad) <> '') ){
								$this->val_champ['LIBELLE_TRAD']	= $this->recherche_libelle($donnees_enr[$this->get_champ_extract(trim($this->code_libelle_trad))],$this->langue,$this->table);
						}*/
						if ( ($this->ok_action == 1) and (count($this->tab_champs_trad) > 0)  ){
								foreach($this->tab_champs_trad as $champ => $champs_trad){
										$this->val_champ[$champs_trad['libelle']]	= $this->recherche_libelle($donnees_enr[$this->get_champ_extract(trim($champ))],$this->langue,$this->table);
								}
						}

				}
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function verif_POST(){
				foreach($this->champs as $champ){
						if( (trim($_POST[$champ['nom']]) == '') and ($champ['obli'] == '1')){// si  champ obli, valeur obli
								$this->ok_action 	= 0;
								$this->lib_champ_err 	= recherche_libelle_page($champ['lib']);
								break;
						}

						if( (trim($_POST[$champ['nom']]) <> '') and ($champ['type'] == 'int')){// si type champ entier, valeur entier
								if (!preg_match ("/^(0|([1-9][0-9]*))$/", trim($_POST[$champ['nom']]))){
										$this->ok_action 	= 0;
										$this->lib_champ_err 	= recherche_libelle_page($champ['lib']);
										break;
								}
						}
				}
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function Insert(){ ///
				$this->act_MAJ = 1;
				$this->verif_POST();
				if($this->ok_action == 1){ // s'il n'y a pas deja une erreur dans les données postées
						$tab_req = array();
						foreach($this->champs as $champ){
								if( trim($_POST[$champ['nom']]) <> '') {
										$tab_req[] = array( 'champ' => $champ['nom'], 'val' => $_POST[$champ['nom']], 'type' => $champ['type']	);
								}
						}
						$sql_champs = 'INSERT INTO '.$this->table.' (';
						$sql_values = 'VALUES (';
						
						foreach($tab_req as $i => $tab){
								if($i>0){
										$sql_champs .= ', ';
										$sql_values .= ', ';
								}
								$sql_champs .= $tab['champ'];
								if($tab['type']=='int'){
										$sql_values .= trim($tab['val']);
								}
								elseif($tab['type']=='text'){
										$sql_values .=  $this->conn->qstr(trim($tab['val'])) ;
								}
						}
						$sql_champs .= ') ';
						$sql_values .= ') ';
						
						$requete = $sql_champs . $sql_values ;
								//
						if ($this->conn->Execute($requete) === false){
								$this->ok_action 	= 0;
								echo '<br>'.$requete.'<br>';
						}
						
						/*if ( ($this->ok_action == 1) and (isset($this->code_libelle_trad)) and (trim($this->code_libelle_trad) <> '') ){
								
								$requete 	= 'INSERT INTO DICO_TRADUCTION 
														 ( CODE_NOMENCLATURE, NOM_TABLE, CODE_LANGUE, LIBELLE )
														 VALUES
														 ( '.$_POST[trim($this->code_libelle_trad)].', \''. trim($this->table) .'\', \''.$this->langue.'\', \''.addslashes($_POST['LIBELLE_TRAD']).'\' )';
											//
								if ($this->conn->Execute($requete) === false){
								   	$this->ok_action 	= 0;
										echo $requete.'<br>';
								}
						}*/
						if ( ($this->ok_action == 1) and (count($this->tab_champs_trad) > 0)  ){
								foreach($this->tab_champs_trad as $champ => $champs_trad){
										$requete 	= 'INSERT INTO DICO_TRADUCTION 
																 ( CODE_NOMENCLATURE, NOM_TABLE, CODE_LANGUE, LIBELLE )
																 VALUES
																 ( '.$_POST[trim($champ)].', \''. trim($champs_trad['table']) .'\', \''.$this->langue.'\', '.$this->conn->qstr($_POST[$champs_trad['libelle']]).' )';
													//
										if ($GLOBALS['conn_dico']->Execute($requete) === false){
												$this->ok_action 	= 0;
												echo $requete.'<br>';
										}
										insert_traduction('DICO_TRADUCTION', $_POST[trim($champ)], trim($champs_trad['table']), $this->langue, $_POST[$champs_trad['libelle']], 1);
								}
						}
				}
				if($this->ok_action == 0){
						$this->btn_add = true;
				}
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function Update(){ /// Mise à jour 
				$this->act_MAJ = 1;

				$this->verif_POST();
				if($this->ok_action == 1){ // s'il n'y a pas deja une erreur dans les données postées

						$sql = 'UPDATE    '.$this->table.' SET '."\n";
						$virg = false;
						$and  = false;
						foreach($this->champs as $i => $champ){
								if($virg == true){
										$sql .= ', '."\n";
								}
								$virg = true;
								if(trim($_POST[$champ['nom']]) == ''){
										$sql .= $champ['nom'].'='."NULL";
								}else{
										if($champ['type']=='int'){
												$sql .= $champ['nom'] . '=' . trim($_POST[$champ['nom']]);
										}
										elseif($champ['type']=='text'){
												$sql .= $champ['nom'] . '=' . $this->conn->qstr(trim($_POST[$champ['nom']])) ;
										}
								}
						}
						foreach($this->champs as $i => $champ){
								if( isset($champ['cle']) and ($champ['cle'] == '1') ){
										if($and == false){
												$sql .= "\n".' WHERE ';
										}
										else{
												$sql .= "\n".' AND ';
										}
										$and = true;
										if($champ['type']=='int'){
												$sql .= $champ['nom'] . '=' . trim($_POST[$champ['nom']]);
										}
										elseif($champ['type']=='text'){
												$sql .= $champ['nom'] . '=' . $this->conn->qstr(trim($_POST[$champ['nom']]));
										}
								}
						}
						$requete = $sql ; 
						//echo $requete ;
						if ($this->conn->Execute($requete) === false){
								$this->ok_action 	= 0;
								echo '<br>'.$requete.'<br>';
						}
						/*if ( ($this->ok_action == 1) and (isset($this->code_libelle_trad)) and (trim($this->code_libelle_trad) <> '') ){
								$requete 	= 'UPDATE DICO_TRADUCTION 
														 SET LIBELLE = \''.addslashes($_POST['LIBELLE_TRAD']).'\'
														 WHERE CODE_NOMENCLATURE='.$_POST[trim($this->code_libelle_trad)].' 
														 AND  CODE_LANGUE=\''.$this->langue.'\'
														 AND NOM_TABLE=\''.trim($this->table).'\'';
											//
								if ($this->conn->Execute($requete) === false){
								   	$this->ok_action 	= 0;
										echo $requete.'<br>';
								}
						}*/
						if ( ($this->ok_action == 1) and (count($this->tab_champs_trad) > 0)  ){
								foreach($this->tab_champs_trad as $champ => $champs_trad){
										$requete 	= 'UPDATE DICO_TRADUCTION 
																 SET LIBELLE = '.$GLOBALS['conn_dico']->qstr($_POST[trim($champs_trad['libelle'])]).'
																 WHERE CODE_NOMENCLATURE='.$_POST[trim($champ)].' 
																 AND  CODE_LANGUE=\''.$this->langue.'\'
																 AND NOM_TABLE=\''.trim($champs_trad['table']).'\'';
													//
										if ($GLOBALS['conn_dico']->Execute($requete) === false){
												$this->ok_action 	= 0;
												echo $requete.'<br>';
										}
								}
						}
				}
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function Delete(){ /// Suppression zone n°i de la table mére n°iTab 
				$this->act_MAJ = 1;
				$and  = false;
				$sql = 'DELETE FROM   ' . $this->table . "\n";
				foreach($this->champs as $i => $champ){
						if( isset($champ['cle']) and ($champ['cle'] == '1') ){
								if($and == false){
										$sql .= "\n".' WHERE ';
								}
								else{
										$sql .= "\n".' AND ';
								}
								$and = true;
								if($champ['type']=='int'){
										$sql .= $champ['nom'] . '=' . trim($_POST[$champ['nom']]);
								}
								elseif($champ['type']=='text'){
										$sql .= $champ['nom'] . '=' . $this->conn->qstr(trim($_POST[$champ['nom']]));
								}
						}
				}
				$requete = $sql ; 
				if ($this->conn->Execute($requete) === false){
				  	$this->ok_action 	= 0;
						echo '<br>'.$requete.'<br>';
				}
				/*if ( ($this->ok_action == 1) and (isset($this->code_libelle_trad)) and (trim($this->code_libelle_trad) <> '') ){
						$requete 	= 'DELETE FROM DICO_TRADUCTION 
												 WHERE CODE_NOMENCLATURE='.$_POST[trim($this->code_libelle_trad)].' 
												 AND  CODE_LANGUE=\''.$this->langue.'\'
												 AND NOM_TABLE=\''.trim($this->table).'\'';
									//
						if ($this->conn->Execute($requete) === false){
								$this->ok_action 	= 0;
								echo $requete.'<br>';
						}
				}*/
				if ( ($this->ok_action == 1) and (count($this->tab_champs_trad) > 0)  ){
						foreach($this->tab_champs_trad as $champ => $champs_trad){
								
								// SUPPRESSION DE TTES LES OCURRENCES DES LANGUES
								$requete 	= 'DELETE FROM DICO_TRADUCTION 
														 WHERE CODE_NOMENCLATURE='.$_POST[trim($champ)].' 
														 AND NOM_TABLE=\''.trim($champs_trad['table']).'\'';

											//
								if ($GLOBALS['conn_dico']->Execute($requete) === false){
										$this->ok_action 	= 0;
										echo $requete.'<br>';
								}						
						}
				}
		}

		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function affiche_template(){

				//$url	=	parse_url(__FILE__ );
				//$chaine_GET = $url[query];
				//echo '<br> ='.$_SERVER['QUERY_STRING']
		?>
	
		<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF.'?'.$_SERVER['QUERY_STRING']; ?>">
				<INPUT type="hidden" id="action" name="action">
				<INPUT type="hidden" id="i_action" name="i_action">
				<span>
					<div align="center">
						<table width="<?php echo $this->taille_ecran;?>">
						<?php if( isset($this->titre_ecran) and (trim($this->titre_ecran)<>'') ){ ?>
									<caption><B><?php echo recherche_libelle_page($this->titre_ecran);?></B></caption>
						<?php } ?>
							<tr>
							<?php if($this->total_enr > 0){
											if( trim($this->nom_champ_combo) == '' and isset($this->champs[0]['nom']) ){
													$this->nom_champ_combo = $this->champs[0]['nom'];
											}
							?>
									
                <td height="168" align="center" valign="middle"> 
                    <div align="center">
											<select style="width : 400px;" name="combo" size="10" onchange="javascript:load_action(combo.value,'Open');">
											<?php foreach ($this->donnees as $i_enr => $enr){
															echo "<option value='".$i_enr."'";
															if ( trim($this->i_enr) == trim($i_enr) ){
																	echo " selected";
															}
															if( trim($this->code_libelle_trad) == trim($this->nom_champ_combo) ){
																	if(!isset($this->table_trad)){
																			$this->table_trad = $this->table;
																	}
																	echo ">".$this->recherche_libelle($enr[$this->get_champ_extract(trim($this->code_libelle_trad))],$this->langue,$this->table_trad)."</option>";
															}
															else{
																	echo ">".$enr[$this->nom_champ_combo]."</option>";
															}
													}
											?>
											</select>
             <br><br>
			<input style="width:100%;" <?php echo "onClick=\"javascript:load_action('".($this->i_enr)."','UpdRptSQL');\""; ?> type='button' <?php echo 'value="'.recherche_libelle_page('UpdRptSQL').'"'; ?>>
				 </div>							  </td>
									<?php }
									?>
									<td>
									<div align="center">
									<table>
										<tr>
											<td align="center" valign="middle">
											<?php $val = array();
													$val = $this->val_champ ;
													///////////// inclusion du frame ici
													include($this->frame);
													//////////////////////////////////
											?>											</td>
										</tr>
										<tr><td nowrap align="center">
										<?php if( $this->btn_first==true ){ ?>
												<input type='button' name='' <?php echo 'value="<<"'; ?>	
												<?php echo "onClick=\"javascript:load_action('".(0)."','Open');\""; ?>>
                                    &nbsp; 
                                    <?php }?>
                                    <?php if( $this->btn_prev==true ){ ?>
                                    <INPUT type='button' name='Input' <?php echo 'value="<"'; ?>	
												<?php echo "onClick=\"javascript:load_action(".( $this->i_enr - 1 ).",'Open');\""; ?>>
                                    &nbsp; 
                                    <?php }?>
                                    <?php if( $this->btn_new==true ){ ?>
                                    <input type='button' name='' <?php echo 'value="'.$this->recherche_libelle_bouton('nouv', $this->langue, $this->nom_page_class).'"'; ?>	
												<?php echo "onClick=\"javascript:load_action('','New');\""; ?>>&nbsp;
										<?php }?>
										<?php if( $this->btn_add==true ){ ?>
												<input type='button' name='' <?php echo 'value="'.$this->recherche_libelle_bouton('ajout', $this->langue, $this->nom_page_class).'"'; ?>	
												<?php echo "onClick=\"javascript:load_action('','Add');\""; ?>>&nbsp; 
										<?php }?>
										<?php if( $this->btn_upd==true ){ ?>
												<input type='button' name='' <?php echo 'value="'.$this->recherche_libelle_bouton('modif', $this->langue, $this->nom_page_class).'"'; ?>	
												<?php echo "onClick=\"javascript:load_action('".($this->i_enr)."','Upd');\""; ?>>&nbsp; 
										<?php }?>
										<?php if( $this->btn_del==true ){ ?>
												<input type='button' name='' <?php echo 'value="'.$this->recherche_libelle_bouton('supp', $this->langue, $this->nom_page_class).'"'; ?>	
												<?php echo "onClick=\"javascript:avertir_supp('".($this->i_enr)."');\""; ?>>&nbsp; 
										<?php }?>
										<?php if( $this->btn_ann==true ){ ?>
												<input type='button' name='' <?php echo 'value="'.$this->recherche_libelle_bouton('annul', $this->langue, $this->nom_page_class).'"'; ?>	
												<?php echo "onClick=\"init();\""; ?>>&nbsp; 
										<?php }?>
										<?php if( $this->btn_next==true ){ ?>
												<input type='button' name='' <?php echo 'value=">"'; ?>	
												<?php echo "onClick=\"javascript:load_action('".($this->i_enr + 1)."','Open');\""; ?>>&nbsp; 
										<?php }?>
										<?php if( $this->btn_last==true ){ ?>
												<input type='button' name='' <?php echo 'value=">>"'; ?>	
												<?php echo "onClick=\"javascript:load_action('".($this->total_enr - 1)."','Open');\""; ?>>
										<?php }?>
										<?php if( $this->btn_quit==true ){ ?>
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<INPUT type="button" <?php echo "value=\"".$this->recherche_libelle_bouton('quit', $this->langue, $this->nom_page_class)."\"";?> onClick="javascript:fermer();">
										<?php }?>
										</td></tr>
									</table>
									</div>									</td>
							</tr>
						</table>
					</div>
				</span>
		</FORM>
				<br>
		<div id="user_rpt_conf_box">		
			<ul class="resp-tabs-list hor_1">
				<li><?php echo recherche_libelle_page('BtnRptSys'); ?></li>
				<?php if ($val['ID_TYPE_REPORT'] != 7) { ?>
				<li><?php echo recherche_libelle_page('BtnRptCrit'); ?></li>
				<li><?php echo recherche_libelle_page('BtnRptMes'); ?></li>
				<li><?php echo recherche_libelle_page('BtnRptDim'); ?></li>
				<li><?php echo recherche_libelle_page('BtnRptAgg'); ?></li>
				<li><?php echo recherche_libelle_page('BtnRptQry'); ?></li>
				<li><?php echo recherche_libelle_page('BtnRptTable'); ?></li>
				<li><?php echo recherche_libelle_page('BtnRptChp'); ?></li>
				<li><?php echo recherche_libelle_page('BtnRptJnt'); ?></li>
				<li><?php echo recherche_libelle_page('BtnRptCritQu'); ?></li>
				<?php } ?>
			</ul>
			<div class="resp-tabs-container hor_1">
				<div>
					<div class="tab-ontainer">
						<iframe class="ui-widget-content ui-corner-all rpt_frame" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=OpenPopupRptSyst&id_rpt=<?php echo $this->donnees[$this->i_enr]['ID']; ?>&id_syst=<?php echo $_SESSION['secteur']; ?>" ></iframe>
					</div>
				</div>
				<?php if ($val['ID_TYPE_REPORT'] != 7) { ?>
				<div>
					<div class="tab-ontainer">
						<?php 
							$list_ids = get_table_field_values('conn_dico', 'DICO_CRITERE_FILTRE', 'ID_CRITERE', '');
							foreach($list_ids as $i => $id) { ?>
								<iframe class="ui-widget-content ui-corner-all rpt_frame_lst" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=OpenPopupRptCrit&id_rpt=<?php echo $this->donnees[$this->i_enr]['ID']; ?>&id_crit=<?php echo $id['ID_CRITERE']; ?>" ></iframe>
						<?php } ?>
					</div>
				</div>
				<div>
					<div class="tab-ontainer">
						<?php 
							$list_ids = get_table_field_values('conn_dico', 'DICO_MESURE', 'ID_MESURE', '');
							foreach($list_ids as $i => $id) { ?>
								<iframe class="ui-widget-content ui-corner-all rpt_frame_lst" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=OpenPopupRptMes&id_rpt=<?php echo $this->donnees[$this->i_enr]['ID']; ?>&id_mes=<?php echo $id['ID_MESURE']; ?>" ></iframe>
						<?php } ?>
					</div>
				</div>
				<div>
					<div class="tab-ontainer">
						<?php 
							$list_ids = get_table_field_values('conn_dico', 'DICO_DIMENSION', 'ID_DIMENSION', '');
							foreach($list_ids as $i => $id) { ?>
								<iframe class="ui-widget-content ui-corner-all rpt_frame_lst" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=OpenPopupRptDim&id_rpt=<?php echo $this->donnees[$this->i_enr]['ID']; ?>&id_dim=<?php echo $id['ID_DIMENSION']; ?>" ></iframe>
						<?php } ?>
					</div>
				</div>
				<div>
					<div class="tab-ontainer">
						<iframe class="ui-widget-content ui-corner-all rpt_frame" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=OpenPopupRptAgg&id_rpt=<?php echo $this->donnees[$this->i_enr]['ID']; ?>" ></iframe>
					</div>
				</div>
				<div>
					<div class="tab-ontainer">
						<iframe class="ui-widget-content ui-corner-all rpt_frame" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=OpenPopupRptQry&id_rpt=<?php echo $this->donnees[$this->i_enr]['ID']; ?>" ></iframe>
					</div>
				</div>
				<div>
					<div class="tab-ontainer">
						<iframe class="ui-widget-content ui-corner-all rpt_frame" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=pop_rpt_tabm&ID=<?php echo $this->donnees[$this->i_enr]['ID']; ?>&id_rpt" ></iframe>
					</div>
				</div>
				<div>
					<div class="tab-ontainer">
						<iframe class="ui-widget-content ui-corner-all rpt_frame" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=pop_rpt_chp&id=<?php echo $this->donnees[$this->i_enr]['ID']; ?>&id_rpt" ></iframe>
					</div>
				</div>
				<div>
					<div class="tab-ontainer">
						<iframe class="ui-widget-content ui-corner-all rpt_frame" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=pop_rpt_jnt&id=<?php echo $this->donnees[$this->i_enr]['ID']; ?>&id_rpt" ></iframe>
					</div>
				</div>
				<div>
					<div class="tab-ontainer">
						<iframe class="ui-widget-content ui-corner-all rpt_frame" id="BtnRptSys_frame" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif" src_url="synthese.php?val=popup_rpt_crit&id=<?php echo $this->donnees[$this->i_enr]['ID']; ?>&id_rpt" ></iframe>
					</div>
				</div>
				<?php } ?>
			</div>		
		  </div> 
			<script type="text/javascript">
				$(document).ready(function () {
			
					$('#user_rpt_conf_box').easyResponsiveTabs({
						type: 'default', //Types: default, vertical, accordion
						width: 'auto', //auto or any width like 600px
						fit: true, // 100% fit in a container
						closed: 'accordion', // Start closed if in accordion view
						tabidentify: 'hor_1', // The tab groups identifier
						activate: function (event) { // Callback function if tab is switched
							var $tab = $(this);
							var $info = $('#nested-tabInfo');
							var $name = $('span', $info);
				
							$name.text($tab.text());
				
							$info.show();
							var force_reload = false;
							if ($(this).text() == '<?php echo recherche_libelle_page('BtnRptChp'); ?>' ||
									$(this).text() == '<?php echo recherche_libelle_page('BtnRptJnt'); ?>' ) {
								force_reload = true;
							}
							$('.resp-tab-content-active iframe').each(function(idx) {
								if (force_reload || ($(this).attr('src') == '<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif')) {
									var pageUrl = $(this).attr('src_url');
									$(this).attr('src', pageUrl);
								}
							});
						}
					});
			 		$('.resp-tab-content-active iframe').each(function(idx) {
						if ($(this).attr('src') == '<?php echo $GLOBALS['SISED_URL_IMG']; ?>loading.gif') {
							var pageUrl = $(this).attr('src_url');
							$(this).attr('src', pageUrl);
						}
					});
				});
			</script>
<?php }
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function alerte_MAJ(){
				if( isset($this->act_MAJ)){ // si on est aprés soumission
						print("<script type=\"text/javascript\">\n");
						print("\t <!-- \n");
						print("mess_MAJ('".$this->action."','".$this->ok_action."',\"".trim($this->lib_champ_err)."\"); \n");
						print("\t //--> \n");
						print("</script>\n");
				}
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function reload_if_delete(){
				if( ($this->action == 'Del') and ($this->ok_action == 1) ){
						$this->init_page();
				}
		}
}

?>

