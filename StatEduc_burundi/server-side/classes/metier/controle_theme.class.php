<?php class controle_theme{
		
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_theme; // le thème en cours de verif
			
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
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $libelle_theme; // le libelle long traduit du thème 
		
		// contient la liste des règles de contrôle liée au thème
		// avec le sql associé à la règle
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $tab_regles_theme 				= array();
		
		// contient la liste des règles associées aux règles du thème en cours
		// Chaque règle associé est accompagnée de son N° regle, de son sql,
		// du critère ou opération et du thème relatif		 
			
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $tab_regles_theme_assoc 	= array();
	
		
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function __construct($id_theme, $langue, $code_etablissement, $code_annee, $code_filtre=""){ 
				$this->conn 	   					= $GLOBALS['conn'];
				$this->id_theme   					= $id_theme;
				$this->langue   					= $langue;
				$this->code_etablissement  			= $code_etablissement;
				$this->code_annee  					= $code_annee;
				$this->code_filtre  				= $code_filtre;
				//$this->libelle_theme  			= $this->recherche_libelle($this->id_theme,$this->langue,'');
				if($this->id_theme<>'' && $this->code_etablissement<>''){
					$this->get_regles();
					$this->controle_regles_theme();
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
		
	public function ArrayToTable($array){
		if (is_array($array) ) {
			$echo ="\n<TABLE width=100% border=2>";
			foreach ($array as $TR) {
				$echo .="\n\t<TR>";
				if (is_array($TR)) {
					foreach ($TR as $TD) {
						$echo .="\n\t\t<TD>";
						if (is_array($TD)) {
							$echo .= ArrayToTable($TD);
						}
						else {
							$echo .= $TD;
						}
						$echo .="</TD>";
					}
				}
				else {
					$echo .= "\n\t\t<TD>".$TR."</TD>";
				}
				$echo .="\n\t</TR>";
			}
			$echo .="\n</TABLE>\n";
			return $echo;
		}else {
			return $array;
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
		public function valeur_sql_regle($sql){
			//permet de retourner la valeur du SQL
			//$GLOBALS['ADODB_FETCH_MODE'] 	= ADODB_FETCH_NUM;
			//$all_res											= $this->conn->GetAll($sql); 
			//$val_return_sql								= $all_res[0][0]
			//$GLOBALS['ADODB_FETCH_MODE'] 	= ADODB_FETCH_ASSOC;
			//
			$val_return_sql ='erreur';

			if (preg_match ("/^(0|([1-9][0-9]*))$/", $sql)){
				//$val_return_sql = $sql;
				$val_return_sql= array ( array ( $sql ) );
			}else{
			// Gestion des erreurs lors de l'exécution de la requête sql
				try {
						//$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_NUM; 
						$val_return_sql	= $this->conn->GetAll($sql);
						if (!is_array($val_return_sql)) {
							throw new Exception('ERR_SQL');
						}
						// Gestion des erreurs lors de l'exécution de la requête sql
						if( count($val_return_sql) == 0){
							$val_return_sql = array ( array ( 0 ) );
						}
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$sql);
					}
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
				// A ranger dans les attributs $tab_regles_theme et $tab_regles_theme_assoc
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
				$sql_regles_theme =	"	SELECT    *
										FROM      DICO_REGLE_THEME
										WHERE     ID_THEME = ".$this->id_theme."
										AND 	SQL_REGLE_THEME IS NOT NULL
										ORDER BY ORDRE_REGLE_THEME";				
												
				//echo"<br>sql_regles_theme=$sql_regles_theme<br>";
				$all_regles_theme	= $GLOBALS['conn_dico']->GetAll($sql_regles_theme); 
				// Gestion des Erreurs lors de l'exécution de la requête sql
                try {
                    if (!is_array($all_regles_theme)) {
                        throw new Exception ('ERR_SQL');
                    }
                    if( is_array($all_regles_theme) and count($all_regles_theme) ){ // s'il y'a des règles associées au thème
                            foreach( $all_regles_theme as $regle_theme ){
                                    //$id_regle_theme 													= $regle_theme['ID_REGLE_THEME'];
                                    $sql_regle_theme	= $regle_theme['SQL_REGLE_THEME'];
                                    $chaine_eval ="\$sql=\"$sql_regle_theme\";";					
                                    eval ($chaine_eval);
									$this->tab_regles_theme[$regle_theme['ID_REGLE_THEME']]['sql']	=	$sql;
                                    //////////////////////////////
                            $sql_regles_assoc =	"	SELECT  DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM, 
																DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME_ASSOC,
																DICO_REGLE_THEME_ASSOC.CRITERE, 
																DICO_REGLE_THEME.ID_THEME,
																DICO_REGLE_THEME.SQL_REGLE_THEME
																FROM  DICO_REGLE_THEME_ASSOC , DICO_REGLE_THEME 
																WHERE DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME_ASSOC = DICO_REGLE_THEME.ID_REGLE_THEME
																AND	DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME = ".$regle_theme['ID_REGLE_THEME']."
																AND DICO_REGLE_THEME.SQL_REGLE_THEME IS NOT NULL
																AND DICO_REGLE_THEME_ASSOC.ACTIVER_CTRL = 1";
																					
                                    //echo"<br>sql_regles_assoc=$sql_regles_assoc<br>";
                                    $all_regles_assoc	= $GLOBALS['conn_dico']->GetAll($sql_regles_assoc); 
                                    // Gestion des erreurs lors de l'exécution de la requête sql
                                    try{
                                        if(!is_array($all_regles_assoc)){
                                            throw new Exception('ERR_SQL');
                                        }
                                            
                                        if( is_array($all_regles_assoc) and count($all_regles_assoc) ){ // s'il y'a des règles associées à la règle
                                                foreach( $all_regles_assoc as $regle_assoc ){ // parcoursdes règles associées
                                                        //$regle_assoc = $all_regles_assoc[0];
                                                        $sql_regle_assoc	= $regle_assoc['SQL_REGLE_THEME'];
                                                        $chaine_eval ="\$sql=\"$sql_regle_assoc\";";						
                                                        eval ($chaine_eval);
                                                        $tab = array();
                                                        $tab['id_assoc']		= $regle_assoc['ID_ASSOC_REG_THM'];
                                                        $tab['id_regle_assoc']	= $regle_assoc['ID_REGLE_THEME_ASSOC'];
                                                        $tab['critere_assoc']	= $regle_assoc['CRITERE'];
                                                        $tab['id_theme_assoc']	= $regle_assoc['ID_THEME'];
                                                        $tab['sql_assoc']		= $sql;
                                                            
                                                        $this->tab_regles_theme_assoc[$regle_theme['ID_REGLE_THEME']][$regle_assoc['ID_REGLE_THEME_ASSOC']]	= $tab;
                                                }
                                        }// fin s'il y'a des règles associées à la règle
                                    }
                                    catch(Exception $e){
                                        $erreur = new erreur_manager($e,$sql_regles_assoc);
                                    }
                                    //////////////////////////////
                            }
                    }// fin s'il y'a des règles associées au thème
                }
                catch(Exception $e){
                    $erreur = new erreur_manager($e,$sql_regles_theme);
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
		public function controle_regles_theme(){
				// Pour chaque règle du thème R1
				// On récupère VAL_1 = valeur_sql_regle(R1)
				// Pour chaque régle associée R2
				// On récupère id_theme_assoc 
				// On récupère VAL_2 = valeur_sql_regle(R2)
				// On récupère l'opération 'OP' de R2
				// On applique la formule VAL_1 'OP' VAL_2
				// Si c'est bon on effectue Alerter(id_theme_assoc,'bon')
				// Sinon  on effectue Alerter(id_theme_assoc,'mauvais')
				
				//echo'tab_regles_theme_assoc<pre>';
				//print_r($this->tab_regles_theme_assoc);
				if(isset($_SESSION['num_lig_page'])) unset($_SESSION['num_lig_page']);
				if(isset($_SESSION['nbre_lig_page'])) unset($_SESSION['nbre_lig_page']);
				if(isset($_GET['num_lig_page'])) unset($_GET['num_lig_page']);
				if(isset($_GET['nbre_lig_page'])) unset($_GET['nbre_lig_page']);
				if( is_array($this->tab_regles_theme) and count($this->tab_regles_theme) ){
						//Ajout Hebie Mali: 17-05-2018: pour afficher les controles en ordre inverse, de sorte que le premier defini soit le dernier affiché au niveau des alert
						$this->tab_regles_theme = array_reverse($this->tab_regles_theme,true);
						foreach( $this->tab_regles_theme as $id_regle =>$tab_regle ){
								$val_sql = $this->valeur_sql_regle($tab_regle['sql']);
								//echo '<br>'.$this->ArrayToTable($val_sql).'<br>';
								//echo '<br>val_sql <pre>'.print_r($val_sql).'<br>';
								if(trim($val_sql)=='erreur'){
										//die('here');
										$this->alerter($id_regle,'','regle_sql_erreur');
								}
								else{
										if( is_array($this->tab_regles_theme_assoc[$id_regle]) and count($this->tab_regles_theme_assoc[$id_regle]) ){
												foreach( $this->tab_regles_theme_assoc[$id_regle] as $id_regle_assoc => $tab_regle_assoc ){
														//$tab_regle_assoc['critere_assoc'];
														//$tab_regle_assoc['id_theme_assoc'];
														//$tab_regle_assoc['sql_assoc']	;
														//echo"$id_regle -> $id_regle_assoc<pre>";
														//print_r($tab_regle_assoc);												
														$val_sql_assoc = $this->valeur_sql_regle($tab_regle_assoc['sql_assoc']);
														//echo '<br>sql <pre>'.$tab_regle['sql'].'<br>';
														//echo '<br>sql_assoc<pre>'.$tab_regle_assoc['sql_assoc'].'<br>';
														//echo '<br>val_sql <pre>'.print_r($val_sql).'<br>';
														//echo '<br>val_sql_assoc<pre>'.print_r($val_sql_assoc).'<br>';
														if(trim($val_sql_assoc)=='erreur'){
																$this->alerter($id_regle_assoc,'','regle_sql_erreur');
														}
														elseif(count($val_sql)== 0 && count($val_sql_assoc)==0){
															$OK = true ;
														}
														elseif(((isset($val_sql_assoc[0][0]) && $val_sql_assoc[0][0] == 0) && (!preg_match ("/^(0|([1-9][0-9]*))$/", $tab_regle_assoc['sql_assoc'])) && ($tab_regle_assoc['critere_assoc'] == '>' || $tab_regle_assoc['critere_assoc'] == '>='))
																|| ((isset($val_sql[0][0]) && $val_sql[0][0] == 0) && (!preg_match ("/^(0|([1-9][0-9]*))$/", $tab_regle_assoc['sql_assoc'])) && ($tab_regle_assoc['critere_assoc'] == '<' || $tab_regle_assoc['critere_assoc'] == '<='))){
															$OK = true ;
														}
														else{
																$op = $tab_regle_assoc['critere_assoc'];
																if( (trim($op)=='=') or (trim($op)=='==') ){
																	$op = '==' ;
																}
																$OK = true ;
																
																$nb_records_1	=	count($val_sql);
																$nb_champs_1 	=	count($val_sql[0]);
																
																$nb_records_2	=	count($val_sql_assoc);
																$nb_champs_2 	=	count($val_sql_assoc[0]);
																$chain_val_sql = '';
																$chain_val_sql_assoc = '';
																//echo "<br>($nb_records_1 == $nb_records_2) and ($nb_champs_1==$nb_champs_2)<br>";
																if( ($nb_records_1 == $nb_records_2) and ($nb_champs_1==$nb_champs_2) ){
																	$list_champs_1 = array();
																	$list_champs_2 = array();
																	$fields_to_compare = array();
																	foreach( $val_sql[0] as $field => $value ){
																		$list_champs_1[] = $field ;
																	}
																	foreach( $val_sql_assoc[0] as $field => $value ){
																		$list_champs_2[] = $field ;
																	}
																	for( $ichp=0 ; $ichp < $nb_champs_1 ; $ichp++ ){
																		//if( trim($list_champs_1[$ichp]) <> trim($list_champs_2[$ichp]) ){//Modif Hebie 07 03 2014 à lomé
																			$fields_to_compare[$ichp] = trim($list_champs_1[$ichp]) .' | '.trim($list_champs_2[$ichp]) ;
																		//}
																	}
																	if(  count($fields_to_compare) == 0){
																		$fields_to_compare[($nb_champs_1-1)] = $list_champs_1[($nb_champs_1-1)] ;
																	}
																	for( $irec=0 ; $irec < $nb_records_1 ; $irec++ ){
																		$rec_cpt = 0;
																		$OK2 = true;
																		foreach( $fields_to_compare as  $ichp => $chp){
																			$nom_chp_1 = $list_champs_1[$ichp] ;
																			$nom_chp_2 = $list_champs_2[$ichp] ;
																			if( (!$val_sql[$irec][$nom_chp_1]) or trim($val_sql[$irec][$nom_chp_1]) == '' ){
																				$val_sql[$irec][$nom_chp_1] = 0 ;
																			}
																			if( (!$val_sql_assoc[$irec][$nom_chp_2]) or trim($val_sql_assoc[$irec][$nom_chp_2]) == '' ){
																				$val_sql_assoc[$irec][$nom_chp_2] = 0 ;
																			}
																			if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$nom_chp_1)){
																				$id_nomenclature = $val_sql[$irec][$nom_chp_1];
																				$table_nomenclature = substr($nom_chp_1,strlen($GLOBALS['PARAM']['CODE'].'_'));
																				if($rec_cpt==0) $chain_val_sql = recherche_libelle_nomenclature_ctrl($id_nomenclature,$table_nomenclature); else $chain_val_sql .= ' ; '.recherche_libelle_nomenclature_ctrl($id_nomenclature,$table_nomenclature);
																			}else{
																				if(is_numeric($val_sql[$irec][$nom_chp_1])){
																					if($rec_cpt==0) $chain_val_sql = round($val_sql[$irec][$nom_chp_1]); else $chain_val_sql .= ' ; '.round($val_sql[$irec][$nom_chp_1]);
																				}else{
																					if($rec_cpt==0) $chain_val_sql = $val_sql[$irec][$nom_chp_1]; else $chain_val_sql .= ' ; '.$val_sql[$irec][$nom_chp_1];
																				}
																			}
																			if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$nom_chp_2)){
																				$id_nomenclature = $val_sql_assoc[$irec][$nom_chp_2];
																				$table_nomenclature = substr($nom_chp_2,strlen($GLOBALS['PARAM']['CODE'].'_'));
																				if($rec_cpt==0) $chain_val_sql_assoc = recherche_libelle_nomenclature_ctrl($id_nomenclature,$table_nomenclature); else $chain_val_sql_assoc .= ' ; '.recherche_libelle_nomenclature_ctrl($id_nomenclature,$table_nomenclature);
																			}else{
																				if(is_numeric($val_sql_assoc[$irec][$nom_chp_2])){
																					if($rec_cpt==0) $chain_val_sql_assoc = round($val_sql_assoc[$irec][$nom_chp_2]); else $chain_val_sql_assoc .= ' ; '.round($val_sql_assoc[$irec][$nom_chp_2]);
																				}else{
																					if($rec_cpt==0) $chain_val_sql_assoc = $val_sql_assoc[$irec][$nom_chp_2]; else $chain_val_sql_assoc .= ' ; '.$val_sql_assoc[$irec][$nom_chp_2];
																				}
																			}
																			if( (trim($op)=='==')){
																				$chaine_op = "'".$val_sql[$irec][$nom_chp_1]."' ".$op." '".$val_sql_assoc[$irec][$nom_chp_2]."'";
																			}else{
																				$chaine_op = $val_sql[$irec][$nom_chp_1]. ' ' .$op.' '.$val_sql_assoc[$irec][$nom_chp_2];
																			}
																			$rec_cpt++;
																			//echo '<br>'.$chaine_op.'<br>';
																			$chaine_eval="if($chaine_op) \$OK = true; else \$OK = false;";
																			//echo '< br>'.($chaine_eval) .'<br>';
																			eval($chaine_eval);
																			//if( $OK == false )	break;
																			if( $OK == false && $ichp < count($fields_to_compare)-1)	$OK2 = false;
																		}
																		//$chain_val_sql .= ' ) ';
																		//$chain_val_sql_assoc .= ' ) ';
																		if( $OK == false || $OK2 == false)	break;
																	}
																}else {
																	$OK = false ;
																	$list_champs_1 = array();
																	$list_champs_2 = array();
																	$fields_to_compare = array();
																	foreach( $val_sql[0] as $field => $value ){
																		$list_champs_1[] = $field ;
																	}
																	foreach( $val_sql_assoc[0] as $field => $value ){
																		$list_champs_2[] = $field ;
																	}
																	for( $ichp=0 ; $ichp < $nb_champs_1 ; $ichp++ ){
																			$fields_to_compare[$ichp] = trim($list_champs_1[$ichp]) .' | '.trim($list_champs_2[$ichp]) ;
																	}
																	if(  count($fields_to_compare) == 0){
																		$fields_to_compare[($nb_champs_1-1)] = $list_champs_1[($nb_champs_1-1)] ;
																	}
																	for( $irec=0 ; $irec < $nb_records_1 ; $irec++ ){
																		$rec_cpt = 0;
																		$OK2 = true;
																		foreach( $fields_to_compare as  $ichp => $chp){
																			$nom_chp_1 = $list_champs_1[$ichp] ;
																			$nom_chp_2 = $list_champs_2[$ichp] ;
																			if( (!$val_sql[$irec][$nom_chp_1]) or trim($val_sql[$irec][$nom_chp_1]) == '' ){
																				$val_sql[$irec][$nom_chp_1] = 0 ;
																			}
																			if( (!$val_sql_assoc[$irec][$nom_chp_2]) or trim($val_sql_assoc[$irec][$nom_chp_2]) == '' ){
																				$val_sql_assoc[$irec][$nom_chp_2] = 0 ;
																			}
																			if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$nom_chp_1)){
																				$id_nomenclature = $val_sql[$irec][$nom_chp_1];
																				$table_nomenclature = substr($nom_chp_1,strlen($GLOBALS['PARAM']['CODE'].'_'));
																				if($rec_cpt==0) $chain_val_sql = recherche_libelle_nomenclature_ctrl($id_nomenclature,$table_nomenclature); else $chain_val_sql .= ' ; '.recherche_libelle_nomenclature_ctrl($id_nomenclature,$table_nomenclature);
																			}else{
																				if(is_numeric($val_sql[$irec][$nom_chp_1])){
																					if($rec_cpt==0) $chain_val_sql = round($val_sql[$irec][$nom_chp_1]); else $chain_val_sql .= ' ; '.round($val_sql[$irec][$nom_chp_1]);
																				}else{
																					if($rec_cpt==0) $chain_val_sql = $val_sql[$irec][$nom_chp_1]; else $chain_val_sql .= ' ; '.$val_sql[$irec][$nom_chp_1];
																				}
																			}
																			if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$nom_chp_2)){
																				$id_nomenclature = $val_sql_assoc[$irec][$nom_chp_2];
																				$table_nomenclature = substr($nom_chp_2,strlen($GLOBALS['PARAM']['CODE'].'_'));
																				if($rec_cpt==0) $chain_val_sql_assoc = recherche_libelle_nomenclature_ctrl($id_nomenclature,$table_nomenclature); else $chain_val_sql_assoc .= ' ; '.recherche_libelle_nomenclature_ctrl($id_nomenclature,$table_nomenclature);
																			}else{
																				if(is_numeric($val_sql_assoc[$irec][$nom_chp_2])){
																					if($rec_cpt==0) $chain_val_sql_assoc = round($val_sql_assoc[$irec][$nom_chp_2]); else $chain_val_sql_assoc .= ' ; '.round($val_sql_assoc[$irec][$nom_chp_2]);
																				}else{
																					if($rec_cpt==0) $chain_val_sql_assoc = $val_sql_assoc[$irec][$nom_chp_2]; else $chain_val_sql_assoc .= ' ; '.$val_sql_assoc[$irec][$nom_chp_2];
																				}
																			}
																			if( (trim($op)=='==')){
																				$chaine_op = "'".$val_sql[$irec][$nom_chp_1]."' ".$op." '".$val_sql_assoc[$irec][$nom_chp_2]."'";
																			}else{
																				$chaine_op = $val_sql[$irec][$nom_chp_1]. ' ' .$op.' '.$val_sql_assoc[$irec][$nom_chp_2];
																			}
																			$rec_cpt++;
																			//echo '<br>'.$chaine_op.'<br>';
																			$chaine_eval="if($chaine_op) \$OK = true; else \$OK = false;";
																			//echo '< br>'.($chaine_eval) .'<br>';
																			eval($chaine_eval);
																			//if( $OK == false )	break;
																			if( $OK == false && $ichp < count($fields_to_compare)-1)	$OK2 = false;
																		}
																		if( $OK == false  || $OK2 == false)	break;
																	}
																	$chain_val_sql_assoc .= ' ; ...';
																}
																if( $OK==true && $OK2 == true ){
																	//die('var ='.$GLOBALS['PARAM']['ALERT_CTRL_THM_OK']);
																	if( $GLOBALS['PARAM']['ALERT_CTRL_THM_OK'] == true ){
																		$this->alerter($id_regle,$id_regle_assoc,'regle_OK');
																	}
																}
																else{
																	//Modif Hebie le 24 04 2019 pour ne pas recharger certains type de grilles
																	//Mis en commentaire par Hebié le 15 05 2019 en attendant une meilleure gestion pour la grille pour la selection ou non d'une ligne correspondante à l'erreur
																	//$this->getNumLineCtrl($val_sql);
																	//if(count($val_sql)==1) $this->getNumLineCtrl($val_sql);
																	if(!isset($_SESSION['num_lig_page']))
																		$chain_val_sql = ' ( '.$chain_val_sql.' ) ' .$op.' ( '.$chain_val_sql_assoc.' ) ';
																	else
																		$chain_val_sql = ' ( '.$chain_val_sql.' ) ';
																	$this->alerter($id_regle,$id_regle_assoc,'regle_pas_OK',$chain_val_sql);
																}
														}												
												}
										}
										else{
												//$this->alerter($id_regle,'','regle_pas_assoc');
										}
								}
						}
				}
				else{
						//$this->alerter('','','theme_pas_regle');
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
		public function alerter($id_regle, $id_regle_assoc, $cas, $chaine_op){
				// Si cas=bon Alerte positive
				// Sinon Si cas=mauvais Alerte négative
				$message_alert ='';
				//echo "<br>$cas<br>";
				switch($cas){
						case 'theme_pas_regle' :{
								$mess1		  		= $this->recherche_libelle(107,$this->langue,'DICO_MESSAGE');
								$mess				= $this->recherche_libelle(103,$this->langue,'DICO_MESSAGE');
								
								/*if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){ // IE
										$message_alert ="Chr(13)&\"$mess\"&Chr(13)&Chr(13)";
										print("<script language=\"VBScript\" type=\"text/vbscript\"> \n");
										print("msgbox \"$message_alert\",vbExclamation,\"$mess1\"\n");
										print("</script>\n");
								}else{*/ // Autre que IE
										$message_alert ='\n'. $mess .'\n\n';
										print("<script language=\"JavaScript\" type=\"text/javascript\"> \n");
										echo " alert( \"".addslashes($message_alert)."\" )";
										print("</script>\n");
								//}

						break;		
						}
						case 'regle_pas_assoc' :{
								$mess1			= $this->recherche_libelle(107,$this->langue,'DICO_MESSAGE');
								$lib_regle 	= $this->recherche_libelle($id_regle,$this->langue,'DICO_REGLE_THEME');
								$mess			 	= $this->recherche_libelle(104,$this->langue,'DICO_MESSAGE');
								//$message_alert =" $lib_regle \\n";
								//$message_alert.="\\t ( $mess )";
								/*if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){ // IE
										$message_alert ="Chr(13)&\"$lib_regle\"&Chr(13)&Chr(13)";
										$message_alert.="&\"   $mess!\"&Chr(13)";
											
										print("<script language=\"VBScript\" type=\"text/vbscript\"> \n");
										print("msgbox \"$message_alert\",vbExclamation,\"$mess1\"\n");
										print("</script>\n");

								}else{*/ // Autre que IE
										$message_alert ='\t'. $lib_regle .'\n\n';
										$message_alert.='\t\t'. $mess .'! \n';
											
										print("<script language=\"JavaScript\" type=\"text/javascript\"> \n");
										echo " alert( \"".addslashes($message_alert)."\" )";
										print("</script>\n");
								//}

						break;
						}
						case 'regle_sql_erreur' :{
								$mess1		  		= $this->recherche_libelle(107,$this->langue,'DICO_MESSAGE');
								$lib_regle 			= $this->recherche_libelle($id_regle,$this->langue,'DICO_REGLE_THEME');
								$mess			 			= $this->recherche_libelle(106,$this->langue,'DICO_MESSAGE');
								//$message_alert =" $lib_regle \\n";
								//$message_alert.="\\t ( $mess )";
								
								/*if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){ // IE
										$message_alert ="Chr(13)&\"$lib_regle\"&Chr(13)&Chr(13)";
										$message_alert.="&\"   $mess!\"&Chr(13)";
											
										print("<script language=\"VBScript\" type=\"text/vbscript\"> \n");
										print("msgbox \"$message_alert\",vbExclamation,\"$mess1\"\n");
										print("</script>\n");
								}else{*/ // Autre que IE
										$message_alert ='\n '. $lib_regle .'\n\n';
										$message_alert.='\t'. $mess .' !\n';
											
										print("<script language=\"JavaScript\" type=\"text/javascript\"> \n");
										echo " alert( \"".addslashes($message_alert)."\" )";
										print("</script>\n");
								//}

						break;
						}
						case 'regle_OK' :{
								$lib_regle 			= $this->recherche_libelle($id_regle,$this->langue,'DICO_REGLE_THEME');
								$lib_regle_assoc 	= $this->recherche_libelle($id_regle_assoc,$this->langue,'DICO_REGLE_THEME');
								$mess1		 				= $this->recherche_libelle(101,$this->langue,'DICO_MESSAGE');
								$mess2		 				= $this->recherche_libelle(105,$this->langue,'DICO_MESSAGE');
								/*if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){ // IE
										$message_alert ="Chr(13)&\"$mess1\"&Chr(13)&Chr(13)";
										$message_alert.="&\"   $mess2!\"&Chr(13)&Chr(13)";
										$message_alert.="&\"      -   $lib_regle\"&Chr(13)&Chr(13)";
										$message_alert.="&\"      -   $lib_regle_assoc\"&Chr(13)";
										
										print("<script language=\"VBScript\" type=\"text/vbscript\"> \n");
										print("msgbox \"$message_alert\",vbInformation,\"$mess1\"\n");
										print("</script>\n");

								}else{*/ // Autre que IE
										$message_alert =' \n ' . $mess1 .' \n \n ';
										$message_alert.=' \t '.   $mess2 .'! \n \n ';
										$message_alert.=' \t\t-\t '. $lib_regle .' \n \n ';
										$message_alert.=' \t\t-\t '. $lib_regle_assoc .' \n ';
										
										print("<script language=\"JavaScript\" type=\"text/javascript\"> \n");
										echo " alert( \"".addslashes($message_alert)."\" )";
										print("</script>\n");

								//}

						break;								
						}
						case 'regle_pas_OK' :{
								$mess1		 		= $this->recherche_libelle(102,$this->langue,'DICO_MESSAGE');
								$id_assoc 			= $this->tab_regles_theme_assoc[$id_regle][$id_regle_assoc]['id_assoc'];
								$message_alert 		= $this->recherche_libelle($id_assoc,$this->langue,'DICO_REGLE_THEME_ASSOC');								
								if(!isset($_SESSION['num_lig_page']) && $_SESSION['num_lig_page']>=0)
									$message_alert .= " : ! ( ".$chaine_op." )";
								else
									$message_alert .= " : ( ".$chaine_op." )";
								if(isset($_SESSION['gets_ctrl']) && $_SESSION['gets_ctrl']<>'' && isset($_SESSION['num_lig_page']) && $_SESSION['num_lig_page']>=0){
									print("<script language=\"JavaScript\" type=\"text/javascript\">\n");
									echo "afficherPopupAvertissement(\"".addslashes($message_alert)."\",\"".$_SESSION['gets_ctrl']."\",\"".$_SESSION['num_lig_page']."\")\n";
									print("</script>\n");
								}else{
									print("<script language=\"JavaScript\" type=\"text/javascript\"> \n");
									echo " alert( \"".addslashes($message_alert)."\" )";
									print("</script>\n");
								}
						break;
						}						
				}
		}
		
		//Permet de retrouver le numero de ligne d'une entrée dans un tableau
		public function getNumLineCtrl($val_sql){
			$id_theme = $this->id_theme;
			if(!isset($_SESSION['id_theme_ctrl']) || $id_theme <> $_SESSION['id_theme_ctrl'] || $_SESSION['code_etab'] <> $_SESSION['id_etab_ctrl'] || $_SESSION['annee'] <> $_SESSION['id_annee_ctrl'] || $_SESSION['filtre'] <> $_SESSION['id_filtre_ctrl'] || $_SESSION['secteur'] <> $_SESSION['id_secteur_ctrl']){
				$_SESSION['id_theme_ctrl'] = $id_theme;
				$_SESSION['id_etab_ctrl'] = $_SESSION['code_etab'];
				$_SESSION['id_annee_ctrl'] = $_SESSION['annee'];
				$_SESSION['id_filtre_ctrl'] = $_SESSION['filtre'];
				$_SESSION['id_secteur_ctrl'] = $_SESSION['secteur'];
				$code_etablissement = $_SESSION['code_etab'];
				$code_annee = $_SESSION['annee'];
				$code_filtre = $_SESSION['filtre'];
				$id_systeme	= $_SESSION['secteur'];
				
				$requete  = "SELECT DICO_THEME.* 
							 FROM DICO_THEME 
							 WHERE DICO_THEME.ID = ".$id_theme;
				$result_theme	= $GLOBALS['conn_dico']->GetAll($requete);
				$theme = $result_theme[0];
				if($theme['ID_TYPE_THEME']==2){ 
					$curr_inst	= $theme['ACTION_THEME'];									
					if (file_exists($GLOBALS['SISED_PATH_CLS'] . 'metier/'.$theme['CLASSE'].'.class.php')) {
						require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/'.$theme['CLASSE'].'.class.php'; 
					}
					switch($curr_inst){
						case 'instance_grille.php' :{
								// Instanciation de la classe
								$curobj_grille		=	new grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
								// chargement des codes des nomenclatures des champs de type matrice
								//$curobj_grille->set_code_nomenclature();
								$_SESSION['curobj_theme'] = $curobj_grille;
								break;
						}	
						case 'instance_mat_grille.php' :{
								// Instanciation de la classe
								$curobj_matgrille = new mat_grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
								// chargement des codes des nomenclatures des champs de type matrice
								//$curobj_matgrille->set_code_nomenclature();
								// Récupération des différents champs
								$curobj_matgrille->set_champs();
								// Sauvegarde de l'objet en session
								$_SESSION['curobj_theme'] = $curobj_matgrille;
								break;
						}
						case 'instance_matrice.php' :{
								// Instanciation de la classe
								$curobj_matrice	=	new matrice($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
								$_SESSION['curobj_theme'] = $curobj_matrice;
								break;
						}
					}
					foreach($_SESSION['curobj_theme']->sql_data as $tabm => $sql_data_tabm){
						$_SESSION['tabm_theme_data'] = $GLOBALS['conn']->GetAll($sql_data_tabm);
						break;
					}
				}
			}	
			foreach($val_sql[0] as $field => $value){
				$num_lig_rech = 0;
				if(is_array(array_column($_SESSION['tabm_theme_data'], $field)) && count(array_column($_SESSION['tabm_theme_data'], $field)) > 0){
					$tab_array_column = array_column($_SESSION['tabm_theme_data'], $field);
					if(is_array($tab_array_column) && count($tab_array_column)){
						$tab_array_column_tmp = array();
						foreach($tab_array_column as $colum){
							if(!in_array($colum,$tab_array_column_tmp)){
								$tab_array_column_tmp[] = $colum;
							}
						}
						$tab_array_column = $tab_array_column_tmp;
					}
					$_SESSION['num_lig_page'] = '';
					$_SESSION['gets_ctrl'] = '';
					if(array_search($value, $tab_array_column)!== false){
						$num_lig_rech = array_search($value, $tab_array_column)+1;
						$nbre_enr_tot = count($tab_array_column);
						$_SESSION['nbre_lig_page'] = $nbre_lig_page = $_SESSION['curobj_theme']->nb_lignes;
						$_SESSION['num_lig_page'] = $num_lig_rech - 1;

						if($nbre_enr_tot > $nbre_lig_page){
							if($num_lig_rech > $nbre_lig_page){
								$_SESSION['num_lig_page'] = ($num_lig_rech % $nbre_lig_page) - 1;
								$_SESSION['gets_ctrl'] = 'questionnaire.php?suite=true&theme='.$id_theme.$_SESSION['secteur'].'&debut='.(((int)($num_lig_rech/$nbre_lig_page))*$nbre_lig_page).'&ctrl=1';
							}else{
								$_SESSION['gets_ctrl'] = 'questionnaire.php?theme='.$id_theme.$_SESSION['secteur'].'&ctrl=1';
							}
						}else{
							$_SESSION['gets_ctrl'] = 'questionnaire.php?theme='.$id_theme.$_SESSION['secteur'].'&ctrl=1';
						}
					}
				}
				break;
			}
		}
}//Fin class controle_theme
?>