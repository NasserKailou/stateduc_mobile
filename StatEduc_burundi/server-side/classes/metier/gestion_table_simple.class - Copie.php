<?php /** 
    * Classe : gestion_table_simple
    * <pre>
    * 	Cette classe  permet de gérer le contenu de toute Table  
	*	de la Base de données via une interface web. Les enregistrements
	*	existants sont listés dans un Combo et Tout enregistrement sélectionné
	*	peut etre sujet à des opérations de MAJ à l'aide des boutons associés.
	*	Pour instancier cette Classe :
	*		1 : un fichier d'instanciation d'une Table 
	*		2 : Un Fichier Template pour la présentation des objets  
	*			d'interface associés aux champs de la Table
	*	Il est à précicer donc  que la Présentation est assez autonome des aspects de traitement
    * </pre>
    * @access public
*/	

class gestion_table_simple{

			
		/**
		* Attribut : $conn
		* <pre>
		* 	Connexion à la Base
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn;
			
		/**
		* Attribut : $langue
		* <pre>
		* 	langue choisie
		* </pre>
		* @var 
		* @access public
		*/   
		public $langue;
			
		/**
		* Attribut : $nom_page_class
		* <pre>
		* 	Fichier contenant la Classe :
		*   	Pour récupérer les libellés de Bouton de la Page
		* </pre>
		* @var 
		* @access public
		*/   
		public $nom_page_class;
			
		/**
		* Attribut :  $i_enr
		* <pre>
		* 	Numero de l'enregistrement courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $i_enr						= 0;
			
		/**
		* Attribut : $total_enr
		* <pre>
		* 	Nombre Total d'enregistrements extraits de la Table
		* </pre>
		* @var 
		* @access public
		*/   
		public $total_enr					= 0;
			
		/**
		* Attribut : $nom_champ_combo
		* <pre>
		* 	Champ de la Table permettant d'avoir les libellés
		*	affichés ds le Combo de choix d'enregistrement
		* </pre>
		* @access public
		*/   
		public $nom_champ_combo;
			
		/**
		* Attribut : $frame
		* <pre>
		* 	Fichier contenant le Template HTML
		* </pre>
		* @var 
		* @access public
		*/   
		public $frame;
			
		/**
		* Attribut : $code_libelle_trad
		* <pre>
		* 	S'il est renseigné, le contenu est un champ de la table dont
		*	les valeurs(Codes) ont des correspondances dans la Table de Traduction
		* </pre>
		* @var 
		* @access public
		*/   
		public $code_libelle_trad;
		
			
		/**
		* Attribut : $btn_new
		* <pre>
		* 	Booléen pour l'affichage ou Non du bouton "Nouveau"
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_new						= false;
			
		/**
		* Attribut : $btn_add	
		* <pre>
		* 	Booléen pour l'affichage ou Non du bouton "Ajouter"
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_add						= false;
			
		/**
		* Attribut : $btn_upd	
		* <pre>
		* 	Booléen pour l'affichage ou Non du bouton "Modifier"
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_upd						= false;
			
		/**
		* Attribut : $btn_del
		* <pre>
		* 	Booléen pour l'affichage ou Non du bouton "Supprimer"
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_del						= false;
			
		/**
		* Attribut : $btn_first
		* <pre>
		* 	Booléen pour l'affichage ou Non du bouton "<<" : Premier Enr
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_first					= false;
			
		/**
		* Attribut : $btn_last
		* <pre>
		* 	Booléen pour l'affichage ou Non du bouton ">>" : Dernier Enr
		* </pre>
		* @access public
		*/  
		public $btn_last					= false;
			
		/**
		* Attribut : $btn_prev
		* <pre>
		* 	Booléen pour l'affichage ou Non du bouton "<" : Enr Précédent
		* </pre>
		* @access public
		*/   
		public $btn_prev					= false;
			
		/**
		* Attribut : $btn_next
		* <pre>
		* 	Booléen pour l'affichage ou Non du bouton ">" : Enr Suivant
		* </pre>
		* @access public
		*/   
		public $btn_next					= false;
			
		/**
		* Attribut : $is_list
		* <pre>
		* 	Booléen pour l'affichage ou Non des tags "<br/>"
		* </pre>
		* @access public
		*/   
		public $is_list						= false;
		
		/**
		* Attribut : $bool_concat_combo
		* <pre>
		* 	Booléen pour Concaténer des libellés au niveau du Combo des enregistrements 
		* </pre>
		* @var 
		* @access public
		*/   
		public $bool_concat_combo			= false;
		
			
		/**
		* Attribut : $table
		* <pre>
		* 	Table choisie
		* </pre>
		* @access public
		*/   
		public $table;
			
		/**
		* Attribut : $table_trad
		* <pre>
		* 	Table de Traduction
		* </pre>
		* @access public
		*/   
		public $table_trad;
			
		/**
		* Attribut : $champs
		* <pre>
		* 	Liste des champs de la table avec leur propriété
		* </pre>
		* @var 
		* @access public
		*/   
		public $champs 						= array();
			
		/**
		* Attribut : tab_champs_trad
		* <pre>
		* 	Tableau de champs dont les contenus sont traduits
		* </pre>
		* @var array
		* @access public
		*/   
		public $tab_champs_trad   = array();
			
		/**
		* Attribut : $tab_btns_popup
		* <pre>
		* 	Contient un ensemble de boutons permettant de lancer des popups
		* </pre>
		* @var array
		* @access public
		*/   
		public $tab_btns_popup   = array();
		
		/**
		* Attribut : $tab_concat_combo
		* <pre>
		* 	les champs à concaténer pour la constitution des libellés affichés dans le combo 
		* </pre>
		* @var array
		* @access public
		*/   
		public $tab_concat_combo   = array();
			
		/**
		* Attribut : $sql_order_by
		* <pre>
		* 	Partie de la requête traitant les critères de tri pour la requête
		*	d'extraction des données de la Table
		* </pre>
		* @var string
		* @access public
		*/   
		public $sql_order_by;
			
		/**
		* Attribut : $sql_criteres_filtre
		* <pre>
		* 	Partie de la requête traitant les critères de filtre pour la requête
		*	d'extraction des données de la Table
		* </pre>
		* @var string
		* @access public
		*/   
		public $sql_criteres_filtre;
			
		/**
		* Attribut : $action
		* <pre>
		* 	Indique le Type d'action à effectuer à la Soumission
		* </pre>
		* @var 
		* @access public
		*/   
		public $action; // 
			
		/**
		* Attribut : $ok_action
		* <pre>
		* 	Décrit le resultat de l'action de MAJ ds la base OK ou Pas OK
		* </pre>
		* @var 
		* @access public
		*/   
		public $ok_action; // 
			
		/**
		* Attribut : $act_MAJ
		* <pre>
		* 	indique la tenue d'une action de MAJ
		* </pre>
		* @var 
		* @access public
		*/   
		public $act_MAJ;  // 
			
		/**
		* Attribut : $lib_champ_err
		* <pre>
		* 	Texte associé au champ où une erreur a été détectée
		* </pre>
		* @var string
		* @access public
		*/   
		public $lib_champ_err;

			
		/**
		* Attribut : $donnees 
		* <pre>
		* 	Tableau contenant les  données extraites de la Table
		* </pre>
		* @var array
		* @access public
		*/   
		public $donnees 					= array();
			
		/**
		* Attribut : $val_champ 
		* <pre>
		* 	Tableau associatif des Champs de la Table et de leurs valeurs
		* </pre>
		* @var array
		* @access public
		*/   
		public $val_champ 				= array();
			
		/**
		* Attribut : $text_alert
		* <pre>
		* 	Contient l'ensemble des messages d'alert correspondant
		*	aux différents types d'action prévus
		* </pre>
		* @var array
		* @access public
		*/   
		public $text_alert 				= array();
		
			
		/**
		* Attribut : $taille_ecran
		* <pre>
		* 	Taille (Largeur) de l'interface de getion de la Table
		* </pre>
		* @var 
		* @access public
		*/   
		public $taille_ecran			= '95%';
			
		
		/**
		* Attribut : $taille_combo
		* <pre>
		* 	Taille (Largeur) donnée au Combo des enregistrements
		* </pre>
		* @var 
		* @access public
		*/   
		public $taille_combo			= '';
			
		
		/**
		* Attribut : $hauteur_combo
		* <pre>
		* 	Nombre d'enregistremnts visibles sur le combo sans Scrooling
		* </pre>
		* @var 
		* @access public
		*/   
		public $hauteur_combo			= '10';
			

		/**
		* Attribut : $btn_quit
		* <pre>
		* 	Booléen pour l'affichage ou Non du bouton de Fermeture de la fenètre
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_quit					= true;
		
			
		/**
		* Attribut : $titre_ecran
		* <pre>
		* 	Titre donné à l'interface de Gestion de la Table de donnée choisie
		* </pre>
		* @var 
		* @access public
		*/   
		public $titre_ecran			= '';
		public $hidden_btn = array();

		 /**
		 * METHODE :  __construct()
		 * <pre>
		 * Constructeur de la classe :
		 * </pre>
		 * @access public
		 
		 */ 	 
		public function __construct(){

				$this->conn					= 	$GLOBALS['conn'];
				$this->langue    			= 	$_SESSION['langue'];
				
				$this->i_enr				= 	0;
				$this->btn_ann				= 	true;
				$this->btn_next				= 	false;
				$this->btn_add				= 	false;
				$this->btn_upd				= 	false;
				$this->btn_del				= 	false;
				$this->btn_first			= 	false;
				$this->btn_last				= 	false;
				$this->btn_prev				= 	false;
				$this->btn_next				= 	false;
				$this->is_list				= 	false;
				
				$this->taille_ecran			= 	'95%';
				$this->hauteur_combo		= 	'10';
				
				$this->btn_quit				= 	true;
				
				
				$this->nom_page_class		= 	'gestion_table_simple.class.php';
				
				$this->tab_champs_trad		=	array(); 
				$this->tab_btns_popup		=	array();
				$this->bool_concat_combo 	= 	false ;
				
		}
		
		/**
		* METHODE : run()
		* <pre>
		*
		* 	Préparation des libellés de l'interface
		*	Récupération des données de la table
		*	Gérer la Soumission de la Page
		*	Controler l'Affichage des Boutons de MAJ
		*	Afficher convenablement les données sur le Template
		*	Gérer les alertes
		*	
		* </pre>
		* @access public
		* 
		*/
		public function run(){

				lit_libelles_page('/'.$this->frame);
				
				if( (isset($this->tab_concat_combo)) and (is_array($this->tab_concat_combo)) and (count($this->tab_concat_combo)) ){
					$this->bool_concat_combo = true ;
				}

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
		* METHODE : init_page()
		* <pre>
		* 	Affichage par défaut
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
		
		public function __wakeup(){
			$this->conn	= $GLOBALS['conn'];
		}
		
		/**
		* METHODE : get_champ_extract($nom_champ)
		* <pre>
		*
		* Retourne les 30 ou 31 premiers caractères de $nom_champ en fonction du type de SGBD 
		* pour permttre d'extraire correctement la valeur du champ $nom_champ dans un recordset
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
						}else{
								$taille_max_extract=31;                
						}
						$champ_extract = substr($nom_champ ,0,$taille_max_extract); 
				}
				return($champ_extract);
		}
		
		/**
		* METHODE : get_libelle_concat( $val, $champ_cible, $table_cible, $champ_extract)
		* <pre>
		* 	Renvoie la valeur du champ $champ_extract 
		*	de la Table $table_cible où le champ $champ_cible = $val
		* </pre>
		* @access public
		* 
		*/
		public function get_libelle_concat( $val, $champ_cible, $table_cible, $champ_extract){
			
			$requete        = ' SELECT '.$champ_extract.'
								FROM '.$table_cible.'
								WHERE '.$champ_cible.'='.$val;
			//echo $requete . '<br>'; die();
			return($this->conn->GetOne($requete)); 
		}
		
		
		/**
		* METHODE : recherche_libelle($code,$langue,$table)
		* <pre>
		* 	Recherche du libellé traduit suivant les critéres ($code, $langue, $table)
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
										WHERE CODE_NOMENCLATURE=".$code." AND CODE_LANGUE='".$langue."'
										AND NOM_TABLE='".$table."'";
				
				//echo $requete.'<br><br>';
				//print_r($this->conn);
				
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$all_res	= $conn->GetAll($requete); 
						if(!is_array($all_res)){                    
								//throw new Exception('ERR_SQL');  
						} 
					return($all_res[0]['LIBELLE']);										
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
		}
		
    
		/**
		* METHODE : recherche_libelle_bouton($code, $langue, $table)
		*
		* <pre>
		* 	Recherche de libellé Page suivant les critéres ($code, $langue, $table)
		* </pre>
		* @access public
		* 
		*/
		public function recherche_libelle_bouton($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				$requete 	= "SELECT LIBELLE
                                FROM DICO_LIBELLE_PAGE 
                                WHERE CODE_LIBELLE='".$code."' AND CODE_LANGUE='".$langue."'
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
		* METHODE : get_text_alert()
		* <pre>
		* 	Récupération des messages d'alerte
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
		* METHODE : print_JS()
		* <pre>
		* 	Ecriture du code JS sur le template
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
							//alert(i_enr+" : "+do_act);
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
							location.href   = '?<?php echo $_SERVER[QUERY_STRING]; ?>';
						}

					-->
				</script>
				<?php }
		
		
		/**
		* METHODE : get_sql_criteres_filtre()
		* <pre>
		*	Parcours des champs $this->champs de la Table
		*		Si la proptiété filtre est activée
		*			concaténer à la syntaxe $this->sql_criteres_filtre
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
								if( trim($val) <> '' ){
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
				}
				$this->sql_criteres_filtre = $sql;
		}

		
		/**
		* METHODE : get_sql_order_by()
		* <pre>
		*	Parcours des champs $this->champs de la Table
		*		Si la proptiété order est activée
		*			Récupérer le nom du champ
		*		Ordonner la syntaxe dans le bon ordre de tri
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
		* METHODE : get_donnees()
		* <pre>
		*
		*	-->	Préparation de la requête de sélection des données 
		*		de la Table Concaténer la syntaxe des critères et de tri	
		*	--> Récupérer les données, les ranger dans $this->donnees
		*	
		* </pre>
		* @access public
		* 
		*/
		public function get_donnees(){
				$this->donnees = array();
				$this->get_sql_criteres_filtre();
				$this->get_sql_order_by();
				
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
		* METHODE : set_i_enr_of_insert()
		*
		* Permet de se positionner sur l'enregistrement nouvellement inserré
		*
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
		* METHODE : function gererPost()
		* <pre>
		* 	Récupérer l'action à faire aprés la soumission
		*		Si 'Add' => alors Insertion
		*		Si 'Upd' => alors Mise à Jour
		*		Si 'Del' => alors Suppression
		* </pre>
		* @access public
		* 
		*/
		public function gererPost(){
				$this->ok_action 	= 1;
                //echo '<pre>';
                //print_r($_POST);
				if( (isset($_POST['action'])) and ($_POST['action'] <> '') ){ // actions
						
						$this->action	 = $_POST['action'];
						
						if( $this->action == 'New' ){
								//$this->Insert( $_POST['Active'], $_POST['TabMActive'] );
								if(!in_array('btn_add',$this->hidden_btn)) $this->btn_add = true;
								unset($this->i_enr);
						}
						elseif( $this->action == 'Add'){
								$this->Insert();
								unset($this->i_enr);
                                // Modif Alassane 
                                if ($GLOBALS['SISED_OLAP_SERVER_SETUP']==true){
                                     include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/olap.get_obj_serveur.class.php'; 
                                     $sql ='SELECT * FROM DICO_OLAP_AUTRE_CONNEXION WHERE SGBD='.'\'OlapServer\'';
                                     $result =  $GLOBALS['conn_dico']->GetAll($sql);   
                                     
                                     if (isset($result[0]['SERVEUR']) && $result[0]['SERVEUR']<>'')
                                       $serveur_name = $result[0]['SERVEUR'];
                                     else
                                        $serveur_name='';
                                    $serveur_olap = new get_obj_serveur($serveur_name);
                                    echo $serveur_olap->vbscript;
                                    echo '<SCRIPT LANGUAGE="VBSCRIPT">';
                                    echo 'maj_dico_obj_serveur()';
                                    echo '</SCRIPT>';
                                    
                                }
                                // Fin Modif Alassane
						}
						elseif( $this->action == 'Upd'){
								$this->i_enr	 = $_POST['i_action'];                                   
								$this->Update();
                                // Modif Alassane 
                                if ($GLOBALS['SISED_OLAP_SERVER_SETUP']==true){
                                     include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/olap.get_obj_serveur.class.php'; 
                                     $sql ='SELECT * FROM DICO_OLAP_AUTRE_CONNEXION WHERE SGBD='.'\'OlapServer\'';
                                     $result =  $GLOBALS['conn_dico']->GetAll($sql);   
                                     
                                     if (isset($result[0]['SERVEUR']) && $result[0]['SERVEUR']<>'')
                                       $serveur_name = $result[0]['SERVEUR'];
                                     else
                                        $serveur_name='';
                                    $serveur_olap = new get_obj_serveur($serveur_name);
                                    echo $serveur_olap->vbscript;
                                    echo '<SCRIPT LANGUAGE="VBSCRIPT">';
                                    echo 'maj_dico_obj_serveur()';
                                    echo '</SCRIPT>';
                                    
                                }
                                // Fin Modif Alassane    
                                
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
								if($this->action == 'Add' || $this->action == 'Upd'){//Modif Hebie 27 01 2016
										$this->set_i_enr_of_insert();
								}
						}
				}// FIN actions sur 
				elseif(isset($_GET['action_new']) && $_GET['action_new']== 'New' && (!isset($_POST['action']) || $_POST['action']== '')){ // enregistrement encours cas user privileges
						unset($this->i_enr);
				}
		}
		
		/**
		* METHODE : ctrl_btn_action()
		*
		* <pre>
		* 
		* Permet Contrôler l'affichage des boutons d'action
		* en fonction du contexte courant de l'application
		*
		* </pre>
		* @access public
		* 
		*/
		public function ctrl_btn_action(){
			//	echo '<br>total_enr='.$this->total_enr ;
				if($this->total_enr == 0){ // pas d'enregistrement
						if(!in_array('btn_add',$this->hidden_btn)) $this->btn_add			= true;
						if(!in_array('btn_ann',$this->hidden_btn)) $this->btn_ann			= false;
						//unset($this->i_enr);
				}elseif(isset($_GET['action_new']) && $_GET['action_new']== 'New' && (!isset($_POST['action']) || $_POST['action']== '')){ // enregistrement encours cas user privileges
						if(!in_array('btn_add',$this->hidden_btn)) $this->btn_add			= true;
						if(!in_array('btn_ann',$this->hidden_btn)) $this->btn_ann			= true;
						//unset($this->i_enr);
				}
				else{ // il y'a des enr ds la base
						if( (!isset($this->i_enr)) or (trim($this->i_enr) == '') ){
								$this->i_enr = 0; // on va au premier enr 
						}
						else{
								if($this->btn_add <> true){ // s'i ne s'agit pas d'un ajout
										if(!in_array('btn_new',$this->hidden_btn)) $this->btn_new			= true;
										if(!in_array('btn_upd',$this->hidden_btn)) $this->btn_upd			= true;
										if(!in_array('btn_del',$this->hidden_btn)) $this->btn_del			= true;
								}
								if( $this->i_enr > 0 ){
										if(!in_array('btn_prev',$this->hidden_btn)) $this->btn_prev			= true;
										if(!in_array('btn_first',$this->hidden_btn)) $this->btn_first		= true;
								}
								if( $this->i_enr < ($this->total_enr - 1) ){
										if(!in_array('btn_next',$this->hidden_btn)) $this->btn_next			= true;
										if(!in_array('btn_last',$this->hidden_btn)) $this->btn_last			= true;
								}
						}
				}
		}
		
		
		/**
		* METHODE : set_val_champ()
		*	Renseigne convenablement la variable $this->val_champ
		*	selon que les données proviennent de la Base ou du POST
		* <pre>
		* 	Si Ajout ou Table Vide alors
		*		--> placer les valeurs par défaut et incémentales
		* 	Si Action de Post  alors
		*		--> placer les valeurs de l'interface à partir du POST
		* 	Si un Enregistrement selectonné alors
		*		--> placer les valeurs de l'interface à partir de la Base
		* </pre>
		* @access public
		* 
		*/
		public function set_val_champ(){

				if( $this->action == 'New' or ($this->total_enr == 0) ){
						foreach($this->champs as $champ){
								if( isset($champ['incr']) and ($champ['incr'] == 1) and ($champ['type'] == 'int') ){
										$req_max = 'SELECT  MAX('.$champ['nom'].') AS max_val FROM  '.$this->table;
										
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
				if( $this->action == 'Add' or $this->action == 'Upd' ){
						foreach($this->champs as $champ){
								$this->val_champ[$champ['nom']]	= $_POST[$champ['nom']];
						}
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

						if ( ($this->ok_action == 1) and (count($this->tab_champs_trad) > 0)  ){
								foreach($this->tab_champs_trad as $champ => $champs_trad){
										$this->val_champ[$champs_trad['libelle']]	= $this->recherche_libelle($donnees_enr[$this->get_champ_extract(trim($champ))],$this->langue,$this->table);
								}
						}
				}
		}
		
		/**
		* METHODE : verif_POST()
		*	
		* Controle des données Postées
		* <pre>
		* 	Parcours des champs de la Table
		*		Si champ de ctrl 'int' 	=> verication type Num
		*		Si champ de ctrl 'obli' => Champ à saisir Obligatoirement
		* </pre>
		*
		* @access public
		* 
		*/
		public function verif_POST(){
				foreach($this->champs as $champ){
						if( (trim($_POST[$champ['nom']]) == '') and ($champ['obli'] == '1')){// si  champ obli, valeur obli
								$this->ok_action 	= 0;
								$this->lib_champ_err 	= recherche_libelle_page($champ['lib']);
								//die('Err Champ 1: '.$champ['nom']);
								break;
						}

						if( (trim($_POST[$champ['nom']]) <> '') and ($champ['type'] == 'int')){// si type champ entier, valeur entier
								if (!preg_match('/^(0|([1-9][0-9]*))$/', trim($_POST[$champ['nom']]))){
										$this->ok_action 	= 0;
										$this->lib_champ_err 	= recherche_libelle_page($champ['lib']);
										//die('Err Champ 2: '.$champ['nom']);
										break;
								}
						}
				}
		}
		
		/**
		* METHODE : Insert()
		*	
		* Fonction d'insertion dans la Table
		* <pre>
		* 	constitution de la requête d'insertion de donnée
		*	Exécution de la requête
		*	Si erreur => $this->ok_action est à FAUX
		* </pre>
		*
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

						if ( ($this->ok_action == 1) and (count($this->tab_champs_trad) > 0)  ){
								foreach($this->tab_champs_trad as $champ => $champs_trad){
										$requete 	= 'INSERT INTO DICO_TRADUCTION 
																 ( CODE_NOMENCLATURE, NOM_TABLE, CODE_LANGUE, LIBELLE )
																 VALUES
																 ( '.$_POST[trim($champ)].', \''. trim($champs_trad['table']) .'\', \''.$this->langue.'\', '.$this->conn->qstr($_POST[$champs_trad['libelle']]).' )';
										
										if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($champs_trad['table']))){ // Table de Nomenclature : traduction dans la base courante
											$conn                 =   $GLOBALS['conn'];
										} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
											$conn                 =   $GLOBALS['conn_dico']; 
										}
										
										if ($conn->Execute($requete) === false){
												$this->ok_action 	= 0;
												echo $requete.'<br>';
										}
										insert_traduction('DICO_TRADUCTION', $_POST[trim($champ)], trim($champs_trad['table']), $this->langue, $_POST[$champs_trad['libelle']], 1);
								}
						}
				}
				if($this->ok_action == 0){
						if(!in_array('btn_add',$this->hidden_btn)) $this->btn_add = true;
				}
				
		}
		
		/**
		* METHODE : Update()
		*	
		* Fonction d'update dans la Table
		* <pre>
		* 	constitution de la requête d'update de données
		*	Exécution de la requête
		*	Si erreur => $this->ok_action est à FAUX
		* </pre>
		*
		* @access public
		* 
		*/
		public function Update(){ /// Mise à jour 
				$this->act_MAJ = 1;

				$this->verif_POST();
				$donnees_enr	= $this->donnees[$this->i_enr];
				if($this->ok_action == 1){ // s'il n'y a pas deja une erreur dans les données postées

						$sql = 'UPDATE    '.$this->table.' SET '."\n";
						$virg = false;
						$and  = false;                        
						foreach($this->champs as $i => $champ){								
                        	if( !(isset($champ['cle']) and ($champ['cle'] == '1')) ){   
								if($virg == true ){
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
												$sql .= $champ['nom'] . '=' . trim($donnees_enr[$this->get_champ_extract($champ['nom'])]);
										}
										elseif($champ['type']=='text'){
												$sql .= $champ['nom'] . '=' . $this->conn->qstr(trim($donnees_enr[$this->get_champ_extract($champ['nom'])]));
										}
								}
						}
						$requete = $sql ;
						if ($this->conn->Execute($requete) === false){
								$this->ok_action 	= 0;
								echo '<br>'.$requete.'<br>';
						}

						if ( ($this->ok_action == 1) and (count($this->tab_champs_trad) > 0)  ){
								foreach($this->tab_champs_trad as $champ => $champs_trad){
										$requete 	= 'UPDATE DICO_TRADUCTION 
																 SET LIBELLE = '.$this->conn->qstr($_POST[trim($champs_trad['libelle'])]).'
																 WHERE CODE_NOMENCLATURE='.$_POST[trim($champ)].' 
																 AND  CODE_LANGUE=\''.$this->langue.'\'
																 AND NOM_TABLE=\''.trim($champs_trad['table']).'\'';
													//
										if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($champs_trad['table']))){ // Table de Nomenclature : traduction dans la base courante
											$conn                 =   $GLOBALS['conn'];
										} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
											$conn                 =   $GLOBALS['conn_dico']; 
										}
										//echo '<br>'.$requete.'<br>';
										if ($conn->Execute($requete) === false){
												$this->ok_action 	= 0;
												echo $requete.'<br>';
										}
								}
						}
				}
		}
		
		/**
		* METHODE : Delete()
		*	
		* Fonction de suppression d'enregistrement ds la Table
		* <pre>
		* 	constitution de la requête de suppression de données
		*	Exécution de la requête
		*	Si erreur => $this->ok_action est à FAUX
		* </pre>
		*
		* @access public
		* 
		*/
		public function Delete(){ /// Suppression zone n°i de la table mére n°iTab 
				$this->act_MAJ = 1;
				$and  = false;
				$donnees_enr	= $this->donnees[$this->i_enr];
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
										$sql .= $champ['nom'] . '=' . trim($donnees_enr[$this->get_champ_extract($champ['nom'])]);
								}
								elseif($champ['type']=='text'){
										$sql .= $champ['nom'] . '=' . $this->conn->qstr($donnees_enr[$this->get_champ_extract($champ['nom'])]);
								}
						}
				}
				$requete = $sql ; 
				if ($this->conn->Execute($requete) === false){
				  	$this->ok_action 	= 0;
						echo '<br>'.$requete.'<br>';
				}

				if ( ($this->ok_action == 1) and (count($this->tab_champs_trad) > 0)  ){
						foreach($this->tab_champs_trad as $champ => $champs_trad){
								
								// SUPPRESSION DE TTES LES OCURRENCES DES LANGUES
								$requete 	= 'DELETE FROM DICO_TRADUCTION 
														 WHERE CODE_NOMENCLATURE='.$_POST[trim($champ)].' 
														 AND NOM_TABLE=\''.trim($champs_trad['table']).'\'';

								if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($champs_trad['table']))){ // Table de Nomenclature : traduction dans la base courante
									$conn                 =   $GLOBALS['conn'];
								} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
									$conn                 =   $GLOBALS['conn_dico']; 
								}
								
								if ($conn->Execute($requete) === false){
										$this->ok_action 	= 0;
										echo $requete.'<br>';
								}						
						}
				}
		}

		
		/**
		* METHODE : affiche_template()
		*	
		* Permet de gérer l'affichage du formulaire et des objets
		*
		* <pre>
		* 	Assigne les enregistrements de la Table au Combo
		*	Inclue le Template associé 
		*	Dispose les boutons d'action en bas du formulaire
		* </pre>
		*
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
				<?php if (!$this->is_list) { echo "<br/>"; } ?>
				<span>
					<div align="center">
						<table width="<?php echo $this->taille_ecran;?>">
						<?php if( isset($this->titre_ecran) and (trim($this->titre_ecran)<>'') ){ ?>
									<caption><B><?php echo recherche_libelle_page($this->titre_ecran);?></B></caption>
						<?php } ?>
							<tr>
							<?php if( $this->total_enr > 0 ){
									
									if( trim($this->nom_champ_combo) == '' and isset($this->champs[0]['nom']) ){
											$this->nom_champ_combo = $this->champs[0]['nom'];
									}
									
								(isset($this->taille_combo) and (trim($this->taille_combo)<>'')) ? ($t_combo = ' style = \'width:' . $this->taille_combo . ';\'') : ($t_combo = '') ;
									
							?>
									
                <td align="center" valign="middle"> 
                    <div align="center">
								<?php if( $this->total_enr > 1 ){ 
										// pour un seul element pas besoin d'afficher le combo
										//echo '<input type="hidden" name="combo" value="'.$this->i_enr.'" />';
								?>
										<select name="combo" size="<?php echo $this->hauteur_combo; ?>" onchange="javascript:load_action(combo.value,'Open');"<?php echo $t_combo; ?>>
										<?php foreach ($this->donnees as $i_enr => $enr){
														
														if($this->bool_concat_combo == true){
															//die('here');
															$ch_conc = ''; 
															foreach( $this->tab_concat_combo as $i_conc => $chp_conc ){
																if( trim($chp_conc['table_cible']) == trim($this->table) ){
																	$ch_conc .= ' '.$chp_conc['separatorDeb'].$enr[$this->get_champ_extract($chp_conc['champ'])].$chp_conc['separatorFin'];
																}else{
																	$ch_conc .= ' '.$chp_conc['separatorDeb'].$this->get_libelle_concat($enr[$this->get_champ_extract($chp_conc['champ'])], $chp_conc['champ_cible'], $chp_conc['table_cible'], $chp_conc['champ_extract']).$chp_conc['separatorFin'];
																}
															}
														}
														//Ajout HEBIE pour gerer l'affichage correcte des dimensions pour les anciennes config (le champ entete dimension n'existant pas en ce moment)
														if(trim($this->nom_champ_combo) == 'DIM_LIBELLE_ENTTE'){
															if(trim($enr['DIM_LIBELLE_ENTTE'])==''){
																$enr['DIM_LIBELLE_ENTTE'] = $enr['TABLE_REF'];
															}
														}
														//Fin Ajout HEBIE
														echo "<option value='".$i_enr."'";
														if ( trim($this->i_enr) == trim($i_enr) ){
																echo " selected";
														}
														if( trim($this->code_libelle_trad) == trim($this->nom_champ_combo) ){
																if(!isset($this->table_trad)){
																		$this->table_trad = $this->table;
																}
																echo ">" . $this->recherche_libelle($enr[$this->get_champ_extract(trim($this->code_libelle_trad))],$this->langue,$this->table_trad) . $ch_conc . "</option>";
														}
														else{
																echo ">" . $enr[$this->get_champ_extract($this->nom_champ_combo)] . $ch_conc . "</option>";
														}
												}
										?>
										</select>
										<?php } // fin si plus d'un enr
										if( is_array($this->tab_btns_popup) && count($this->tab_btns_popup) && ($this->btn_add <> true)){
											//$ch_btns_pop = '<br>'."\n";
											foreach($this->tab_btns_popup as $ibtn_pop => $btn_pop){
												$show_btn_pop = true ;
												if( isset($btn_pop['show_cond']) && (trim($btn_pop['show_cond']) <> '') ){
													//echo '('.$btn_pop['show_cond'].') ? ($show_btn_pop = true) : ($show_btn_pop = false) ;' ;
													eval ('('.$btn_pop['show_cond'].') ? ($show_btn_pop = true) : ($show_btn_pop = false) ;');
												}
												if($show_btn_pop == true){	
													$ch_btns_pop .= '<br>'."\n";
													$ch_btns_pop .= '<input style="width:70%;" type="button" value="'.recherche_libelle_page($btn_pop['label_code']).'" ';
													
													if( isset($btn_pop['fonc_js']) && (trim($btn_pop['fonc_js']) <> '') ){
														$ch_btns_pop .= 'onClick="'.$btn_pop['fonc_js'].'(';
														/// params fonc js
														$tab_param_js = array();
														if( is_array($btn_pop['params']) && count($btn_pop['params']) ){
															foreach($btn_pop['params'] as $iparam => $param){
																if( isset($param['code']) ){
																	if( isset($param['eval']) && $param['eval'] == 1){
																		eval('$var = '.trim($param['code']).';');
																		if( trim($var) == '' ) $var = '\'\'';
																		$tab_param_js[] = $var ;
																	}else{
																		$tab_param_js[] = $this->donnees[$this->i_enr][$this->get_champ_extract($param['code'])];
																	}
																}
															}
														}
														$ch_btns_pop .= implode(',',$tab_param_js);
														// fin fonc js et balise input
														$ch_btns_pop .= ');">'."\n";
													}
												}
											}
											print $ch_btns_pop ;
										}
										?>
										</div>
									</td>
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
											?>												
											</td>
											<?php if ($this->is_list) { ?>
												<td>
												<div style="width:53px;">
												<?php if( $this->btn_add==true ){ ?>
												<input type='button' style='width:50px;' name='' <?php echo 'value="'.$this->recherche_libelle_bouton('ajout', $this->langue, $this->nom_page_class).'"'; ?>	
												<?php echo "onClick=\"javascript:load_action('','Add');\""; ?>><br/> 
												<?php }?>
												<?php if( $this->btn_upd==true ){ ?>
														<input type='button' style='width:50px;' name='' <?php echo 'value="'.$this->recherche_libelle_bouton('modif', $this->langue, $this->nom_page_class).'"'; ?>	
														<?php echo "onClick=\"javascript:load_action('".($this->i_enr)."','Upd');\""; ?>><br/> 
												<?php }?>
												<?php if( $this->btn_del==true ){ ?>
														<input type='button' style='width:50px;' name='' <?php echo 'value="'.$this->recherche_libelle_bouton('supp', $this->langue, $this->nom_page_class).'"'; ?>	
														<?php echo "onClick=\"javascript:avertir_supp('".($this->i_enr)."');\""; ?>><br/> 
												<?php }?>
												<?php if( $this->btn_ann==true ){ ?>
														<input type='button' style='width:50px;' name='' <?php echo 'value="'.$this->recherche_libelle_bouton('annul', $this->langue, $this->nom_page_class).'"'; ?>	
														<?php if(isset($_GET['action_new']) && $_GET['action_new']== 'New') echo "onClick=\"init_user_priv('".$_SESSION['val']."');\""; else echo "onClick=\"init();\""; ?>> 
												<?php }?>
												</div>	
												</td>	
											<?php } ?>	
										</tr>
										<?php if (!$this->is_list) { ?>
											<tr>
												<td nowrap align="center">
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
														<?php if(isset($_GET['action_new']) && $_GET['action_new']== 'New') echo "onClick=\"init_user_priv('".$_SESSION['val']."');\""; else echo "onClick=\"init();\""; ?>>&nbsp; 
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
												</td>
											</tr>
										<?php } ?>	
									</table>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</span>
		</FORM>
				<?php if (!$this->is_list) { echo "<br/>"; } ?>
		    

<?php }
		
		/**
		* METHODE : alerte_MAJ()
		*	
		* Gére les alertes
		*
		* <pre>
		* 	l'alerte est lancée en fonction du type d'action de MAJ 
		*	et du résultat de cette action
		* </pre>
		*
		* @access public
		* 
		*/
		public function alerte_MAJ(){
				if( isset($this->act_MAJ) && (!$this->is_list || (trim($this->lib_champ_err)!=''))){ // si on est aprés soumission
						print("<script type=\"text/javascript\">\n");
						print("\t <!-- \n");
						print("mess_MAJ('".$this->action."','".$this->ok_action."',\"".trim($this->lib_champ_err)."\"); \n");
						print("\t //--> \n");
						print("</script>\n");
				}
		}
		
		/**
		* METHODE : reload_if_delete()
		*	
		* Aprés une action de suppression,
		* On réactualise l'affichage
		*
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

