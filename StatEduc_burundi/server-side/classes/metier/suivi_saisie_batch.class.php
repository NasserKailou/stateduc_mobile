<?php class suivi_saisie{
		
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $langue; // la langue de traduction choisie
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn; // la variable de connexion à la base
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $code_etablissement; // le code de l’établissement choisi
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $code_annee; // le code année
		
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $code_filtre; // le code filtre	
		
		// contient la liste des règles de contrôle
		// avec le sql associé à la règle
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $tab_regles_suivi 				= array();
		
		public $tab_regles_suivi_saisie = array();
		public $champ1 = array();
		public $champ2 = array();
		public $alert;
		
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function __construct($ctrl_id, $langue, $code_etablissement, $code_annee, $code_filtre='', $alert){ 
				$this->conn 	   					= $GLOBALS['conn'];
				$this->ctrl_id   					= $ctrl_id;
				$this->langue   					= $langue;
				$this->code_etablissement  			= $code_etablissement;
				$this->code_annee  					= $code_annee;
				$this->code_filtre  				= $code_filtre;
				//$this->libelle_theme  			= $this->recherche_libelle($this->id_theme,$this->langue,'');
				$this->alert		  				= $alert;
				$this->get_regles();
				$this->controle_suivi_saisie();
				
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
				
				// Gestion des erreurs lors de l'exécution de la requête SQL
				
                try {
                    $all_res	= $conn->GetAll($requete);
                    if (!is_array($all_res))                    
                        throw new Exception('ERR_SQL');                     
                }
                catch(Exception $e){
                    $erreur = new erreur_manager($e,$requete);
                }
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
		
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function valeur_sql_regle($sql){
				//permet de retourner la valeur du SQL
				$val_return_sql ='erreur';
				if (preg_match ("/^(0|([1-9][0-9]*))$/", $sql)){
					$val_return_sql = $sql;
				}elseif($sql!=''){
				// Gestion des erreurs lors de l'exécution de la requête sql
					try {
							if (($res = $this->conn->Execute($sql))===false) {
									throw new Exception('ERR_SQL');
							}
							// Gestion des erreurs lors de l'exécution de la requête sql
							try {
									if(($val_return_sql = $this->conn->GetOne($sql))===false){
										//throw new Exception('ERR_SQL');
										$val_return_sql=0;
									}elseif(!$val_return_sql){
										$val_return_sql=0;
										//var_dump($val_return_sql);
										//die('here');
									}
							}
							catch(Exception $e) {
									$erreur = new erreur_manager($e,$sql);
							}
							//echo "<br> $val_return_sql=$sql <br><br>";	
					}
					catch(Exception $e){
							$erreur = new Exception($e, $sql);
					}
				}elseif($sql==''){
					$val_return_sql = 0;
				}
				//echo $val_return_sql;
				return($val_return_sql);
		}
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_regles(){
				// permet de récupérer toutes les règles de contrôle associées
				// au thème ainsi les règles en association 
				// A ranger dans les attributs $tab_regles_suivi
				// $chaine_eval ="\$tableau_zone_saisie[$pos_type_e]['sql'] =\"$temp_sql\";";						
				// eval ($chaine_eval);
				$code_etablissement = $this->code_etablissement;
				$code_annee = $this->code_annee;
				$code_filtre = $this->code_filtre;
				//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
				${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
				${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']} = $code_annee;
				${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;
				//Fin ajout Hebie
				
				$sql_ctrl =	'SELECT DICO_REGLE_SUIVI.*
								FROM DICO_REGLE_SUIVI, DICO_REGLE_SUIVI_SYSTEME
								WHERE DICO_REGLE_SUIVI.ID_REGLE_SUIVI = DICO_REGLE_SUIVI_SYSTEME.ID_REGLE_SUIVI
								AND DICO_REGLE_SUIVI_SYSTEME.ID_SYSTEME = '.$_SESSION['secteur'].'
								ORDER BY DICO_REGLE_SUIVI.ORDRE_REGLE_SUIVI;';				
												
				$all_regles_suivi	= $GLOBALS['conn_dico']->GetAll($sql_ctrl); 

				//echo"<pre>all_regles_suivi";
				//print_r($all_regles_suivi);
				// Gestion des Erreurs lors de l'exécution de la requête sql
                try {
                    if (!is_array($all_regles_suivi)) {
                        throw new Exception ('ERR_SQL');
                    }
                    if( is_array($all_regles_suivi) and count($all_regles_suivi) ){ // s'il y'a des règles suivi
                            foreach( $all_regles_suivi as $regle_suivi ){
                                    //$id_regle_suivi 	= $regle_theme['ID_REGLE_SUIVI'];
                                    $sql_regle_suivi	= $regle_suivi['SQL_REGLE_SUIVI'];
									if (!is_null($sql_regle_suivi) and (trim($sql_regle_suivi)<>'') ){
										$temp_sql	=	$sql_regle_suivi;
										if(isset($_SESSION['suivi_saisie']['liste_etabs_user']) && count($_SESSION['suivi_saisie']['liste_etabs_user'])>0){
											$temp_sql = preg_replace('/'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'[[:space:]]*=/', $GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' IN ', $temp_sql);
											$temp_sql = preg_replace('/'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'\)[[:space:]]*=/', $GLOBALS['PARAM']['CODE_ETABLISSEMENT'].') IN ', $temp_sql);
											$temp_sql = preg_replace('/GROUP(.)*BY(.)*HAVING/', 'WHERE', $temp_sql);
											$temp_sql = preg_replace('/GROUP(.)*BY(.)*/', '', $temp_sql);
											$code_etablissement = '('.implode(', ',$_SESSION['suivi_saisie']['liste_etabs_user']).')';
											${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = '('.implode(', ',$_SESSION['suivi_saisie']['liste_etabs_user']).')';
											$chaine_eval ="\$sql =\"$temp_sql\";";
											eval ($chaine_eval);
										}elseif(isset($_SESSION['suivi_saisie']['liste_etabs_user']) && count($_SESSION['suivi_saisie']['liste_etabs_user'])==0){	
											$sql = '';
										}else{	
											$chaine_eval ="\$sql =\"$temp_sql\";";
											eval ($chaine_eval);
										}
									}else{
										$sql = '';
									}
									$this->tab_regles_suivi[$regle_suivi['ID_REGLE_SUIVI']]['sql']	=	$sql;
                            }
                    }// fin s'il y'a des règles de suivi
                }
                catch(Exception $e){
                    $erreur = new erreur_manager($e,$all_regles_suivi);
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
		public function controle_suivi_saisie(){
			// Pour chaque règle de suivi R1
			// On récupère VAL_1 = valeur_sql_regle(R1)
			//echo 'tab_regles_suivi<pre>';
			//print_r($this->tab_regles_suivi);
			if( is_array($this->tab_regles_suivi) and count($this->tab_regles_suivi) ){
				foreach( $this->tab_regles_suivi as $id_regle =>$tab_regle ){
					//echo '<br>'.$tab_regle['sql'].'<br>';
					$val_sql = $this->valeur_sql_regle($tab_regle['sql']);
					//echo '<br>'.val_sql.'<br>';
					if(trim($val_sql)=='erreur'){
						//die('here');
						$this->alerter($id_regle,'','regle_sql_erreur');
					}
					$tab_regle_suivi['val_regle'] = $val_sql ;
					$this->tab_regles_suivi_saisie[$id_regle]=$tab_regle_suivi;
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
		public function alerter($id_regle, $id_regle_assoc, $cas){
			// Si cas=bon Alerte positive
			// Sinon Si cas=mauvais Alerte négative
			$message_alert ='';
			//echo "<br>$cas<br>";
			switch($cas){
					
				case 'regle_sql_erreur' :{
					$mess1		  		= $this->recherche_libelle(107,$this->langue,'DICO_MESSAGE');
					$lib_regle 			= $this->recherche_libelle($id_regle,$this->langue,'DICO_REGLE_THEME');
					$mess			 			= $this->recherche_libelle(106,$this->langue,'DICO_MESSAGE');
					//$message_alert =" $lib_regle \\n";
					//$message_alert.="\\t ( $mess )";
					
					if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){ // IE
							$message_alert ="Chr(13)&\"$lib_regle\"&Chr(13)&Chr(13)";
							$message_alert.="&\"   $mess!\"&Chr(13)";
								
							print("<script language=\"VBScript\" type=\"text/vbscript\"> \n");
							print("msgbox $message_alert,vbExclamation,\"$mess1\"\n");
							print("</script>\n");
					}else{ // Autre que IE
							$message_alert ='\n '. $lib_regle .'\n\n';
							$message_alert.='\t'. $mess .' !\n';
								
							print("<script language=\"JavaScript\" type=\"text/javascript\"> \n");
							print(" alert( $message_alert )");
							print("</script>\n");
					}

					break;
				}
			}
		}

}//Fin class suivi_saisie

?>
