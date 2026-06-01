<?php
/**
 * controle_theme_batch.class.php ? Moteur de contrôle de cohérence des données de collecte
 *
 * Ce fichier implémente la classe `controle_theme` qui effectue les contrôles de cohérence
 * inter-règles pour un thème de collecte statistique (StatEduc MEN).
 *
 * =========================================================================
 * PRINCIPE DES CONTRÔLES DE COHÉRENCE
 * =========================================================================
 *
 * Un contrôle de cohérence met en relation DEUX règles SQL (R1 et R2) via un opérateur
 * de comparaison (critère OP) stocké dans DICO_REGLE_THEME_ASSOC :
 *
 *   R1 OP R2  ?  si la condition est VRAIE : données cohérentes (OK)
 *                si la condition est FAUSSE : violation détectée (KO)
 *
 * Exemple : "Nb élèves filles <= Nb élèves total"
 *   R1 = SELECT SUM(NB_FILLES) FROM ...  ? valeur numérique
 *   R2 = SELECT SUM(NB_TOTAL)  FROM ...  ? valeur numérique
 *   OP = '<='
 *   ? eval("if(V1 <= V2) $OK=true; else $OK=false;")
 *
 * =========================================================================
 * DEUX MODES D'UTILISATION
 * =========================================================================
 *
 * MODE HTML (alert = true)  ? usage navigateur web classique :
 *   - Génère des alertes JavaScript (navigateurs modernes) ou VBScript (IE)
 *   - Affiche message OK ou KO directement dans le navigateur après soumission
 *   - Utilisé dans le workflow web standard de StatEduc
 *
 * MODE BATCH/API (alert = false)  ? usage depuis data_controle.php (API mobile) :
 *   - N'affiche aucune alerte HTML
 *   - Remplit $tab_regles_theme_assoc_not_ok[] avec les règles en violation
 *   - data_controle.php lit ce tableau et retourne les erreurs en JSON
 *   - Utilisé par l'application Flutter pour les contrôles serveur
 *
 * =========================================================================
 * FLUX D'EXÉCUTION
 * =========================================================================
 *
 *   __construct($ctrl_id, $langue, $code_etablissement, $code_annee, $code_filtre, $alert)
 *       ?
 *       ??? get_regles()
 *       ?     ?? Charge R1 depuis DICO_REGLE_THEME (WHERE ID_REGLE_THEME = regle1)
 *       ?     ?? Interpole les variables PHP dans le SQL via eval()
 *       ?     ?    ex: eval('$sql = "$sql_regle_theme";')
 *       ?     ?    ? $code_etablissement, $code_annee, $code_filtre remplacés
 *       ?     ?? Charge R2 depuis DICO_REGLE_THEME (WHERE ID_REGLE_THEME = regle2)
 *       ?     ?? Remplit $tab_regles_theme[] et $tab_regles_theme_assoc[]
 *       ?
 *       ??? controle_regles_theme()
 *             ?? Pour chaque R1 dans $tab_regles_theme :
 *             ?   ?? valeur_sql_regle(sql_R1) ? V1
 *             ?       ?? Pour chaque R2 dans $tab_regles_theme_assoc[R1] :
 *             ?           ?? valeur_sql_regle(sql_R2) ? V2
 *             ?           ?? eval("if(V1 OP V2) $OK=true; else $OK=false;")
 *             ?           ?? Si OK=true  ? alerter(id_regle, id_regle_assoc, 'regle_OK')
 *             ?           ?? Si OK=false ? $tab_regles_theme_assoc_not_ok[R1][R2] = infos
 *             ?                          ? alerter(id_regle, id_regle_assoc, 'regle_pas_OK')
 *
 * =========================================================================
 * STRUCTURE DE $tab_regles_theme_assoc_not_ok
 * =========================================================================
 *
 * Tableau indexé par [id_regle_R1][id_regle_R2] contenant pour chaque violation :
 *   - 'id_assoc'        : ID de l'association DICO_REGLE_THEME_ASSOC
 *   - 'id_regle_assoc'  : ID de la règle R2
 *   - 'critere_assoc'   : opérateur utilisé (<=, >=, =, <, >, <>)
 *   - 'id_theme_assoc'  : ID du thème de R2
 *   - 'id_theme'        : ID du thème de R1
 *   - 'sql_assoc'       : SQL interpolé de R2
 *   - 'msg_assoc'       : libellé du message d'erreur (DICO_REGLE_THEME_ASSOC)
 *   - 'nom_regle_1'     : libellé traduit de R1 (DICO_REGLE_THEME)
 *   - 'nom_regle_2'     : libellé traduit de R2 (DICO_REGLE_THEME)
 *   - 'val_champ1'      : valeur calculée de V1 (pour affichage)
 *   - 'val_champ2'      : valeur calculée de V2 (pour affichage)
 *
 * =========================================================================
 * TABLES BASE DE DONNÉES UTILISÉES (DICO)
 * =========================================================================
 *
 *   DICO_REGLE_THEME         : définit les règles SQL (R1, R2) par thème
 *   DICO_REGLE_THEME_ASSOC   : associe R1 et R2 avec l'opérateur (CRITERE)
 *                              et le flag ACTIVER_CTRL (1=actif)
 *   DICO_MESSAGE             : messages traduits pour les alertes (IDs 101-107)
 *   DICO_TRADUCTION          : libellés traduits de toutes les nomenclatures
 *
 * =========================================================================
 * DÉPENDANCES
 * =========================================================================
 *
 *   $GLOBALS['conn']       : connexion AdoDB à la base de données de collecte
 *   $GLOBALS['conn_dico']  : connexion AdoDB à la base DICO (règles, libellés)
 *   $GLOBALS['PARAM']      : paramètres globaux (CODE_ETABLISSEMENT, TYPE_ANNEE, etc.)
 *   erreur_manager         : classe de gestion des erreurs SQL
 *   recherche_libelle_nomenclature_ctrl() : fonction globale de traduction nomenclature
 *
 * @author    Équipe StatEduc MEN
 * @version   2025
 * @encoding  ISO-8859-15 / Windows CRLF (fichier legacy ? ne pas convertir)
 */
class controle_theme{
		
			
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
		
		public $tab_regles_theme_assoc_not_ok = array();
		public $champ1 = array();
		public $champ2 = array();
		public $alert;
		
		
		/**
		 * Constructeur ? Initialise le moteur de contrôle et lance l'exécution complète.
		 *
		 * Initialise tous les attributs de l'instance, puis si ctrl_id != 0, déclenche
		 * automatiquement get_regles() pour charger les règles SQL, puis
		 * controle_regles_theme() pour exécuter les comparaisons.
		 *
		 * @param int    $ctrl_id              ID de l'association (DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM)
		 *                                     Passé 0 ? n'exécute pas les contrôles (instance vide)
		 * @param string $langue               Code langue pour les libellés traduits (ex: 'FR', 'EN')
		 * @param string $code_etablissement   Code de l'établissement ciblé par le contrôle
		 * @param string $code_annee           Code de l'année scolaire (ex: '2024')
		 * @param string $code_filtre          Code filtre optionnel (ex: secteur, sous-groupe)
		 * @param bool   $alert                true = mode HTML (alertes JS/VBScript)
		 *                                     false = mode batch API (remplit tab_regles_theme_assoc_not_ok)
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
				if($ctrl_id != 0){
					$this->get_regles();
					$this->controle_regles_theme();
				}
				
		}
		
		
		/**
		 * recherche_libelle ? Récupère un libellé traduit depuis DICO_TRADUCTION.
		 *
		 * Détermine la connexion à utiliser selon le type de table :
		 *   - Table de nomenclature (préfixée par PARAM.TYPE) ? base courante ($GLOBALS['conn'])
		 *   - Autre table ? base DICO externe ($GLOBALS['conn_dico'])
		 *
		 * @param  int    $code    Code de la nomenclature à traduire
		 * @param  string $langue  Code langue (ex: 'FR')
		 * @param  string $table   Nom de la table de nomenclature (ex: 'DICO_REGLE_THEME')
		 * @return string          Libellé traduit ou chaîne vide si non trouvé
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
		 * valeur_sql_regle ? Exécute un SQL et retourne le résultat sous forme de tableau 2D.
		 *
		 * Deux cas de figure :
		 *   1. Si $sql est une constante numérique pure (ex: "0", "42") :
		 *      ? retourne directement array(array($sql)) sans requête DB
		 *   2. Sinon : exécute la requête via AdoDB GetAll() et retourne le résultat
		 *      ? Si résultat vide : retourne array(array(0)) (convention "zéro")
		 *      ? Si erreur SQL   : retourne la chaîne 'erreur' (gérée par controle_regles_theme)
		 *
		 * Le résultat est toujours de la forme : array(array(valeur, ...))
		 * pour être compatible avec la logique de comparaison multi-champs/multi-lignes.
		 *
		 * @param  string $sql  Requête SQL interpolée (avec variables $code_etablissement etc.)
		 *                      ou constante numérique
		 * @return mixed        Tableau 2D de résultats, ou 'erreur' en cas d'exception SQL
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
							$val_return_sql= array ( array ( 0 ) );
						}
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$sql);
					}
					//$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
					//echo "<br> $val_return_sql=$sql <br><br>";	
					//echo "<br> <pre> ";
					//print_r($val_return_sql);
				}
				//echo $val_return_sql;
				return($val_return_sql);
		}
		
		
		/**
		 * get_regles ? Charge et interpole toutes les règles SQL associées au contrôle.
		 *
		 * Cette méthode effectue les opérations suivantes :
		 *
		 * 1. Déclare les variables PHP locales ($code_etablissement, $code_annee, $code_filtre)
		 *    ET les variables dynamiques globales (via ${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} etc.)
		 *    pour permettre à eval() de les substituer dans les SQL stockés en base.
		 *
		 * 2. Requête DICO_REGLE_THEME_ASSOC (WHERE ID_ASSOC_REG_THM = $ctrl_id AND ACTIVER_CTRL=1)
		 *    ? récupère theme1, regle1, critere, theme2, regle2
		 *
		 * 3. Charge R1 : SQL de la règle principale (regle1 du theme1)
		 *    ? eval('$sql = "$sql_regle_theme";') ? interpolation des variables PHP dans le SQL
		 *    ? stocké dans $this->tab_regles_theme[id_regle]['sql']
		 *
		 * 4. Charge R2 : SQL de la règle associée (regle2 du theme2)
		 *    ? eval('$sql = "$sql_regle_assoc";') ? même interpolation
		 *    ? stocké dans $this->tab_regles_theme_assoc[regle1][regle2][] avec :
		 *         'id_assoc', 'id_regle_assoc', 'critere_assoc', 'id_theme_assoc',
		 *         'id_theme', 'sql_assoc', 'msg_assoc', 'nom_regle_1', 'nom_regle_2'
		 *
		 * IMPORTANT : L'interpolation via eval() permet aux SQL de contenir des références
		 * aux variables PHP comme $code_etablissement ou $code_annee, rendant les requêtes
		 * dynamiques selon le contexte de l'établissement et de l'année scolaire.
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
				
				$sql_ctrl_assoc =	'SELECT DISTINCT DICO_REGLE_THEME_1.ID_THEME AS theme1, DICO_REGLE_THEME_1.ID_REGLE_THEME AS regle1,
								DICO_REGLE_THEME_ASSOC.CRITERE AS critere, 
								DICO_REGLE_THEME_2.ID_THEME AS theme2, DICO_REGLE_THEME_2.ID_REGLE_THEME AS regle2
								FROM DICO_REGLE_THEME AS DICO_REGLE_THEME_1,  DICO_REGLE_THEME AS DICO_REGLE_THEME_2, DICO_REGLE_THEME_ASSOC
								WHERE DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME 	= DICO_REGLE_THEME_1.ID_REGLE_THEME 
								AND	DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME_ASSOC = DICO_REGLE_THEME_2.ID_REGLE_THEME 
								AND	DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM ='.$this->ctrl_id.'
								AND DICO_REGLE_THEME_ASSOC.ACTIVER_CTRL = 1;';			
												
				$ctrl	= $GLOBALS['conn_dico']->GetAll($sql_ctrl_assoc); 
				$ctrl	= array_change_key_case_unicode($ctrl[0]);
				//echo "<pre>"; print_r($ctrl);
				//echo  $sql_ctrl_assoc ;
				//die();
				$this->id_theme = $ctrl['theme1'] ;
				
				$sql_regles_theme =	"	SELECT    *
										FROM      DICO_REGLE_THEME
										WHERE     ID_THEME = ".$this->id_theme."
										AND 	  ID_REGLE_THEME = ".$ctrl['regle1']."
										AND 	  SQL_REGLE_THEME IS NOT NULL";				
												
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
                            		$sql_regles_assoc =	"SELECT    *
															FROM      DICO_REGLE_THEME
															WHERE     ID_THEME = ".$ctrl['theme2']."
															AND 	  ID_REGLE_THEME = ".$ctrl['regle2']."
															AND 	  SQL_REGLE_THEME IS NOT NULL";				
																					
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
                                                        $tab['id_assoc']		= $this->ctrl_id;
                                                        $tab['id_regle_assoc']	= $ctrl['regle2'];
                                                        $tab['critere_assoc']	= $ctrl['critere'];
                                                        $tab['id_theme_assoc']	= $ctrl['theme2'];
														$tab['id_theme']		= $ctrl['theme1'];
                                                        $tab['sql_assoc']		= $sql;
														$tab['msg_assoc'] 		= $this->recherche_libelle($tab['id_assoc'], $this->langue, 'DICO_REGLE_THEME_ASSOC');
														$tab['nom_regle_1'] 	= $this->recherche_libelle($ctrl['regle1'], $this->langue, 'DICO_REGLE_THEME');
														$tab['nom_regle_2'] 	= $this->recherche_libelle($ctrl['regle2'], $this->langue, 'DICO_REGLE_THEME');
                                                            
                                                        $this->tab_regles_theme_assoc[$ctrl['regle1']][$ctrl['regle2']]	= $tab;
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
		 * controle_regles_theme ? Exécute les comparaisons R1 OP R2 et détecte les violations.
		 *
		 * Algorithme principal de contrôle de cohérence :
		 *
		 * Pour chaque règle R1 dans $tab_regles_theme :
		 *   1. V1 = valeur_sql_regle(sql_R1)
		 *   2. Si V1 = 'erreur' ? alerter('regle_sql_erreur') et passer à la suivante
		 *   3. Pour chaque règle associée R2 dans $tab_regles_theme_assoc[R1] :
		 *      a. V2 = valeur_sql_regle(sql_R2)
		 *      b. Si V2 = 'erreur' ? alerter('regle_sql_erreur')
		 *      c. CAS SPÉCIAUX (considérés OK sans comparaison) :
		 *         - V1=0 ET V2=0 (données toutes nulles)
		 *         - V2=0 avec opérateur > ou >= (ne peut violer "X > 0" si X=0)
		 *         - V1=0 avec opérateur < ou <= (symétrique)
		 *      d. COMPARAISON NORMALE :
		 *         - Construit $chaine_op = "V1 OP V2"
		 *         - eval("if($chaine_op) $OK = true; else $OK = false;")
		 *         - Si OK=true  ? alerter('regle_OK') (si ALERT_CTRL_THM_OK=true)
		 *         - Si OK=false ? remplit $tab_regles_theme_assoc_not_ok[R1][R2]
		 *                       ? alerter('regle_pas_OK')
		 *
		 * GESTION MULTI-LIGNES / MULTI-CHAMPS :
		 *   - Si nb_records_1 == nb_records_2 ET nb_champs_1 == nb_champs_2 :
		 *     comparaison ligne-à-ligne, champ-à-champ
		 *   - Sinon (cardinalités différentes) :
		 *     $OK=false immédiatement, extrait premier enregistrement pour affichage
		 *
		 * NOTE : unset des variables de session num_lig_page / nbre_lig_page
		 * au début pour éviter des effets de bord de pagination.
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
				//echo 'tab_regles_theme<pre>';
				//print_r($this->tab_regles_theme);
				//echo'<br>tab_regles_theme_assoc<pre>';
				//print_r($this->tab_regles_theme_assoc);
				if(isset($_SESSION['num_lig_page'])) unset($_SESSION['num_lig_page']);
				if(isset($_SESSION['nbre_lig_page'])) unset($_SESSION['nbre_lig_page']);
				if( is_array($this->tab_regles_theme) and count($this->tab_regles_theme) ){
						foreach( $this->tab_regles_theme as $id_regle =>$tab_regle ){
								$val_sql = $this->valeur_sql_regle($tab_regle['sql']);
								//echo '<br>'.$val_sql.'<br>';
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
														//echo '<br>'.$val_sql_assoc.'<br>';
														if(trim($val_sql_assoc)=='erreur'){
																$this->alerter($id_regle_assoc,'','regle_sql_erreur');
														}
														elseif(count($val_sql)== 0 && count($val_sql_assoc)==0){
															if( $GLOBALS['PARAM']['ALERT_CTRL_THM_OK'] == true ){
																if($this->alert){
																	$this->alerter($id_regle,$id_regle_assoc,'regle_OK');
																}
															}
														}
														elseif(((isset($val_sql_assoc[0][0]) && $val_sql_assoc[0][0] == 0) && (!preg_match ("/^(0|([1-9][0-9]*))$/", $tab_regle_assoc['sql_assoc'])) && ($tab_regle_assoc['critere_assoc'] == '>' || $tab_regle_assoc['critere_assoc'] == '>='))
																|| ((isset($val_sql[0][0]) && $val_sql[0][0] == 0) && (!preg_match ("/^(0|([1-9][0-9]*))$/", $tab_regle_assoc['sql_assoc'])) && ($tab_regle_assoc['critere_assoc'] == '<' || $tab_regle_assoc['critere_assoc'] == '<='))){
															if( $GLOBALS['PARAM']['ALERT_CTRL_THM_OK'] == true ){
																if($this->alert){
																	$this->alerter($id_regle,$id_regle_assoc,'regle_OK');
																}
															}
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
																		//if( trim($list_champs_1[$ichp]) <> trim($list_champs_2[$ichp]) ){//Modif Hebie 07 03 2014 lomé
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
																		if( $OK == false  || $OK2 == false)	break;
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
																		$OK2 = true ;
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
																			$rec_cpt++;
																		}
																		break;
																	}
																	$chain_val_sql_assoc .= ' ; ...';
																}
																if( $OK==true && $OK2 == true ){
																	//die('var ='.$GLOBALS['PARAM']['ALERT_CTRL_THM_OK']);
																	if( $GLOBALS['PARAM']['ALERT_CTRL_THM_OK'] == true ){
																		if($this->alert){
																			$this->alerter($id_regle,$id_regle_assoc,'regle_OK');
																		}
																	}
																}
																else{
																	if($nb_records_1==1 &&	$nb_champs_1==1 ){
																		
																		foreach( $val_sql[0] as $field => $value ){
																			if(is_numeric($value)){
																				$tab_regle_assoc['val_champ1'] = round($value);
																			}else{
																				$tab_regle_assoc['val_champ1'] = $value;
																			}
																		}
																		foreach( $val_sql_assoc[0] as $field => $value ){
																			if(is_numeric($value)){
																				$tab_regle_assoc['val_champ2'] = round($value);
																			}else{
																				$tab_regle_assoc['val_champ2'] = $value;
																			}
																		}
																	}else{
																		$tab_regle_assoc['val_champ1'] = $chain_val_sql ;
																		$tab_regle_assoc['val_champ2'] = $chain_val_sql_assoc ;
																	}
																	$tab_regle_assoc['val_sql'] = $val_sql ;
																	$this->tab_regles_theme_assoc_not_ok[$id_regle][$id_regle_assoc]=$tab_regle_assoc;
																	if($this->alert){ 
																		$this->alerter($id_regle,$id_regle_assoc,'regle_pas_OK');
																	}
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
		 * alerter ? Génère une alerte HTML (JS ou VBScript/IE) selon le cas de contrôle.
		 *
		 * Cette méthode n'est active QUE si $this->alert = true (mode navigateur web).
		 * En mode batch API ($alert=false), cette méthode n'est jamais appelée pour
		 * 'regle_pas_OK' (les erreurs sont stockées dans $tab_regles_theme_assoc_not_ok).
		 *
		 * Cas gérés :
		 *   'theme_pas_regle'  : le thème n'a aucune règle ? message DICO_MESSAGE[103]
		 *   'regle_pas_assoc'  : une règle R1 n'a pas de règle associée R2
		 *                        ? message DICO_MESSAGE[104] + libellé de R1
		 *   'regle_sql_erreur' : erreur d'exécution SQL pour une règle
		 *                        ? message DICO_MESSAGE[106] + libellé de la règle
		 *   'regle_OK'         : contrôle passé avec succès (affiché seulement si
		 *                        PARAM['ALERT_CTRL_THM_OK']=true)
		 *                        ? messages DICO_MESSAGE[101] et [105]
		 *   'regle_pas_OK'     : VIOLATION détectée
		 *                        ? libellé depuis DICO_REGLE_THEME_ASSOC (message métier)
		 *
		 * Détection navigateur :
		 *   - strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? Internet Explorer ? VBScript
		 *   - Sinon ? JavaScript alert()
		 *
		 * @param int|string $id_regle        ID de la règle R1 (pour libellé et recherche)
		 * @param int|string $id_regle_assoc  ID de la règle R2 associée
		 * @param string     $cas             Type d'alerte : 'theme_pas_regle', 'regle_pas_assoc',
		 *                                    'regle_sql_erreur', 'regle_OK', 'regle_pas_OK'
		 */
		public function alerter($id_regle, $id_regle_assoc, $cas){
				// Si cas=bon Alerte positive
				// Sinon Si cas=mauvais Alerte négative
				$message_alert ='';
				//echo "<br>$cas<br>";
				switch($cas){
						case 'theme_pas_regle' :{
								$mess1		  		= $this->recherche_libelle(107,$this->langue,'DICO_MESSAGE');
								$mess				= $this->recherche_libelle(103,$this->langue,'DICO_MESSAGE');
								
								if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){ // IE
										$message_alert ="Chr(13)&\"$mess\"&Chr(13)&Chr(13)";
										print("<script language=\"VBScript\" type=\"text/vbscript\"> \n");
										print("msgbox $message_alert,vbExclamation,\"$mess1\"\n");
										print("</script>\n");
								}else{ // Autre que IE
										$message_alert ='\n'. $mess .'\n\n';
										print("<script language=\"JavaScript\" type=\"text/javascript\"> \n");
										print("alert(\" $message_alert \") \n");
										print("</script>\n");
								}

						break;		
						}
						case 'regle_pas_assoc' :{
								$mess1			= $this->recherche_libelle(107,$this->langue,'DICO_MESSAGE');
								$lib_regle 	= $this->recherche_libelle($id_regle,$this->langue,'DICO_REGLE_THEME');
								$mess			 	= $this->recherche_libelle(104,$this->langue,'DICO_MESSAGE');
								//$message_alert =" $lib_regle \\n";
								//$message_alert.="\\t ( $mess )";
								if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){ // IE
										$message_alert ="Chr(13)&\"$lib_regle\"&Chr(13)&Chr(13)";
										$message_alert.="&\"   $mess!\"&Chr(13)";
											
										print("<script language=\"VBScript\" type=\"text/vbscript\"> \n");
										print("msgbox $message_alert,vbExclamation,\"$mess1\"\n");
										print("</script>\n");

								}else{ // Autre que IE
										$message_alert ='\t'. $lib_regle .'\n\n';
										$message_alert.='\t\t'. $mess .'! \n';
											
										print("<script language=\"JavaScript\" type=\"text/javascript\"> \n");
										print("alert( $message_alert )");
										print("</script>\n");
								}

						break;
						}
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
						case 'regle_OK' :{
								$lib_regle 			= $this->recherche_libelle($id_regle,$this->langue,'DICO_REGLE_THEME');
								$lib_regle_assoc 	= $this->recherche_libelle($id_regle_assoc,$this->langue,'DICO_REGLE_THEME');
								$mess1		 				= $this->recherche_libelle(101,$this->langue,'DICO_MESSAGE');
								$mess2		 				= $this->recherche_libelle(105,$this->langue,'DICO_MESSAGE');
								if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){ // IE
										$message_alert ="Chr(13)&\"$mess1\"&Chr(13)&Chr(13)";
										$message_alert.="&\"   $mess2!\"&Chr(13)&Chr(13)";
										$message_alert.="&\"      -   $lib_regle\"&Chr(13)&Chr(13)";
										$message_alert.="&\"      -   $lib_regle_assoc\"&Chr(13)";
										
										print("<script language=\"VBScript\" type=\"text/vbscript\"> \n");
										print("msgbox $message_alert,vbInformation,\"$mess1\"\n");
										print("</script>\n");

								}else{ // Autre que IE
										$message_alert =' \n ' . $mess1 .' \n \n ';
										$message_alert.=' \t '.   $mess2 .'! \n \n ';
										$message_alert.=' \t\t-\t '. $lib_regle .' \n \n ';
										$message_alert.=' \t\t-\t '. $lib_regle_assoc .' \n ';
										
										print("<script language=\"JavaScript\" type=\"text/javascript\"> \n");
										print(" alert( $message_alert )");
										print("</script>\n");

								}

						break;								
						}
						case 'regle_pas_OK' :{
								/*
								$lib_regle 				= $this->recherche_libelle($id_regle,$this->langue,'DICO_REGLE_THEME');
								$lib_regle_assoc 	= $this->recherche_libelle($id_regle_assoc,$this->langue,'DICO_REGLE_THEME');
								$mess1		 				= $this->recherche_libelle(102,$this->langue,'DICO_MESSAGE');
								$mess2		 				= $this->recherche_libelle(105,$this->langue,'DICO_MESSAGE');
								*/
								$mess1		 		= $this->recherche_libelle(102,$this->langue,'DICO_MESSAGE');
								$id_assoc 			= $this->tab_regles_theme_assoc[$id_regle][$id_regle_assoc]['id_assoc'];
								$message_alert 		= $this->recherche_libelle($id_assoc,$this->langue,'DICO_REGLE_THEME_ASSOC');								
								
								if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){ // IE
										/*
										$message_alert ="Chr(13)&\"$mess1\"&Chr(13)&Chr(13)";
										$message_alert.="&\"   $mess2!\"&Chr(13)&Chr(13)";
										$message_alert.="&\"      -   $lib_regle\"&Chr(13)&Chr(13)";
										$message_alert.="&\"      -   $lib_regle_assoc\"&Chr(13)";
										*/
										$message_alert ="\"$message_alert\"";

										print("<script language=\"VBScript\" type=\"text/vbscript\"> \n");
										print("msgbox $message_alert,vbcritical,\"$mess1\"\n");
										print("</script>\n");

								}else{ // Autre que IE
										/*
										$message_alert =' \n '. $mess1 . ' \n \n ';
										$message_alert.=' \t '. $mess2 . ' ! \n \n ';
										$message_alert.=' \t\t-\t'.  $lib_regle .' \n \n ';
										$message_alert.=' \t\t-\t'.  $lib_regle_assoc .' \n \n ';
										*/
										print("<script language=\"JavaScript\" type=\"text/javascript\">\n");
										print("alert( \"$message_alert\" )\n");
										print("</script>\n");

								}

						break;
						}						
				}
				//echo $message_alert;
				/*
				print("<script language=\"JavaScript\" type=\"text/javascript\">\n");
				print("<!--\n");
				print("alert(\"$message_alert\");\n");
				print("//-->\n");
				print("</script>\n");
				*/
		}

}// Fin class controle_theme ? Moteur de contrôle de cohérence StatEduc MEN

?>