<?php include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';

		class switch_theme {
				
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
		public $curr_inst;
					
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_theme;
					
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $bool_data_changed			=	false;
					
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
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function __construct($id_theme){
						$this->conn 	      			= $GLOBALS['conn'];
						$this->id_theme						= $id_theme;        
						$this->bool_data_changed 	= false;
						$this->langue    					= $_SESSION['langue'];
						lit_libelles_page('/questionnaire.php');
				}
				
				
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function init(){
						if( isset($_POST['switch_theme_id']) and trim($_POST['switch_theme_id']) <> '' ){ // on a cliqué sur theme_prec ou theme_suiv
								$requete  = "SELECT ACTION_THEME 
														 FROM DICO_THEME 
														 WHERE ID =".$this->id_theme.";";
								
								// Traitement Erreur Cas : GetAll / GetRow
								try {
										$result_curr	= $GLOBALS['conn_dico']->GetRow($requete);
										if(!is_array($result_curr)){                    
												throw new Exception('ERR_SQL');  
										} 
										$curr_inst		= $result_curr['ACTION_THEME'];									
								}
								catch(Exception $e){
										$erreur = new erreur_manager($e,$requete);
								}
								// Fin Traitement Erreur Cas : GetAll / GetRow

								$_SESSION['post_switch_theme'] = $_POST;

								$this->run($curr_inst);
						}
						if( isset($_SESSION['post_switch_theme']) ){
								if( $_GET['submit_switch_theme']==0  ){
										$GLOBALS['dont_submit'] = 1;
								}
								elseif( $_GET['submit_switch_theme']==1 ){ // il faut une MAJ 
										$_POST = $_SESSION['post_switch_theme'];
								}
								unset($_SESSION['post_switch_theme']);
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
		public function check_change_data($curr_inst){
						//$res_comp = array();
						//echo'<br>$this->curr_inst='.$this->curr_inst.'<br>';
						$curr_obj 		= $_SESSION['curobj_instance'];
						$post 				= $_SESSION['post_switch_theme'];

						switch($curr_inst){
								case 'instance_grille.php' :
								case 'instance_mat_grille.php' :{
										$curr_obj->get_post_template($post);
										$curr_obj->comparer($curr_obj->matrice_donnees, $curr_obj->matrice_donnees_post);
												//$res_comp = $this->curr_object->matrice_donnees_bdd);
										if(count($curr_obj->matrice_donnees_bdd) > 0){
												//echo '<br>res comp :<br><pre>';
												//print_r($this->curr_object->matrice_donnees_bdd);
												foreach( $curr_obj->matrice_donnees_bdd as $arr_tabliee ){
														if(count($arr_tabliee)){
																$this->bool_data_changed = true;
																break;
														}
												}
										}
										break;
								}
								case 'instance_matrice.php' :{
								    //var_dump($this->curr_object);
										$taille_ligne 					= count($curr_obj->ligne);
										$taille_colonne 				= count($curr_obj->colonne);
										$matrice_post						= post_to_matrice($curr_obj->mesures,$taille_ligne,$taille_colonne,$post,$curr_obj->type_matrice);
										$curr_obj->comparer_matrice($curr_obj->valeurs,$matrice_post,$curr_obj->nb_cles);
										//$res_comp = $this->curr_object->matrice_donnees_bdd);
										/*echo '<br>valeurs :<br><pre>';
										print_r($this->curr_object->valeurs);
										echo '<br>matrice_donnees_post :<br><pre>';
										print_r($matrice_post);*/
										//die();
										
										if(count($curr_obj->matrice_donnees_bdd) > 0){
												// '<br>res comp :<br><pre>';
												//print_r($this->curr_object->matrice_donnees_bdd);
												$this->bool_data_changed = true;
										}
										break;
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
		public function run($curr_inst){
				$post = $_SESSION['post_switch_theme'];
				$this->check_change_data($curr_inst);
				if( $this->bool_data_changed == true ){
						$this->alert_save_data();
						echo "<script type='text/Javascript'>\n";
						echo "$.unblockUI();\n";
						echo "</script>\n";
						exit();
				}else{
						print '<script type=text/javascript>';
								print 'document.location.href="questionnaire.php?theme='.$post['switch_theme_id'].'"' ;
						print '</script>' ;
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
		public function alert_save_data(){
			//$text_alert = $this->recherche_libelle(300,$this->langue,'DICO_MESSAGE');
			//$curr_obj 		 = $_SESSION['curobj_instance'];
			$post 	= $_SESSION['post_switch_theme'];
			print '<script type=text/javascript>';
			//print '<!--' ; 
			print 'if(confirm("'.recherche_libelle_page(AlertThm).'")){ ' ;
					print 'document.location.href="questionnaire.php?submit_switch_theme=1"' ;
			print '}else{';
					print 'document.location.href="questionnaire.php?submit_switch_theme=0&theme='.$post['switch_theme_id'].'"' ;
			print '}' ;
			//	print '-->' ;
			print '</script>' ;
			//exit;
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
		}	
?>
