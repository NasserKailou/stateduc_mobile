<?php /** 
		* Classe qui permet l'accés au contenu des thémes configurés
		* Elle permet la récupération des thèmes existants et de leur ordonnancement,
		* Mais s'occupe également du charger la classe Métier selon le thème en cours de traitement,
		* @access public
	*/	
	class theme_manager {
    
		
		/**
		 * Attribut : id
		 * 	Correspond au ID du thème en cours
		 * @var numeric
		 * @access public
		 */   
		public $id;
    	
		
		/**
		 * Attribut : pos_thm
		 * 	Position Thème courant
		 */  
		 public $pos_thm;
    	
		/**
		 * Attribut : conn
		 * 	Connexion à la Base
		 * @var array
		 * @access public
		 */     
		public $conn;
    	
		/**
		 * Attribut : list
		 * 	Stocke la liste des thèmes existants dans la Config
		 * @var array
		 * @access public
		 */   
		public $list;
    	
		/**
		* Attribut : id_theme_systeme
		* 	Correspond au thème configuré pour le système
		* @var numeric
		* @access public
		*/   
		public $id_theme_systeme;
    	
		/**
		* Attribut : classe
		* 	le nom de la classe Métier utilisée par le thème
		* @var string
		* @access public
		*/   
		public $classe;

		/**
		 * METHODE :  __construct()
		 * 	Constructeur de la classe :
		 * @access public
		 */ 
		public function __construct($appart="") {
	
			$this->conn = $GLOBALS['conn'];
		
			$this->charger_theme($appart);
		
			$this->set_theme_courant();
			$this->set_classe();
	
		}

		/**
		 * METHODE :  recherche_theme_prec()
		 * 	Permet de rechercher à partir de la liste des thèmes
		 *	celui qui précède le thème en cours
		 * @access public
		 */ 
		public function recherche_theme_prec() {

        // Retourne le thème précédent le theme_courant à partir du tableau des thèmes triés
        foreach ($this->list as $k => $theme) {

        if ($this->id_theme_systeme == $theme['ID_THEME_SYSTEME']) {

                if(isset($this->list[$k-1])) {

                    if($this->list[$k-1]['ID_TYPE_THEME'] != 8) {

                        return $this->list[$k-1]['ID_THEME_SYSTEME'];

                    } else {

                        if(isset($this->list[$k-2])) {

                            return $this->list[$k-2]['ID_THEME_SYSTEME'];

                        } else {

                            return false;

                        }

                    }

                } else {

                    return false;

                }

            }

        }

    }

		/**
		 * METHODE :  recherche_theme_suiv()
		 * 	Permet de rechercher à partir de la liste des thèmes
		 *	celui qui succéde au thème en cours
		 * @access public
		 */ 
		public function recherche_theme_suiv() {

        // Retourne le thème suivant le theme_courant à partir du tableau des thèmes triés
        foreach ($this->list as $k => $theme) {

            if ($this->id_theme_systeme == $theme['ID_THEME_SYSTEME']) {

                if(isset($this->list[$k+1])) {

                    if($this->list[$k+1]['ID_TYPE_THEME'] != 8) {

                        return $this->list[$k+1]['ID_THEME_SYSTEME'];

                    } else {

                        if(isset($this->list[$k+2])) {

                            return $this->list[$k+2]['ID_THEME_SYSTEME'];

                        } else {

                            return false;

                        }

                    }

                } else {

                    return false;

                }

            }

        }

    }

    	/**
		 * METHODE :  charger_theme()
		 * 	Permet charger les thèmes configurés
		 * @access public
		 */
		public function charger_theme($appart="", $sect="") {
			if ($sect!="") {
				$secteur = $sect;
			}
			if (!isset($langue) and (isset($_SESSION['langue'])) ){
				$langue = $_SESSION['langue'];
			}
			if (!isset($secteur) and (isset($_SESSION['secteur']))){
				$secteur = $_SESSION['secteur'];
			}
			// Lecture des infos du theme dans la table DICO_THEME du dico
			// Ajout Alassane
			//echo 'pasée alasco <br>';
			if (!isset($langue) || !isset($secteur)) {
					$requete = "SELECT * FROM PARAM_DEFAUT;";
					
					// Traitement Erreur Cas : GetAll / GetRow
					try {
							$paramdefaut = $GLOBALS['conn_dico']->GetRow($requete);
							if(!is_array($paramdefaut)){                    
									throw new Exception('ERR_SQL');  
							} 
							if (!isset($langue)) $langue    =   trim($paramdefaut['CODE_LANGUE']);
							if (!isset($secteur)) $secteur  =   trim($paramdefaut['CODE_SECTEUR']);
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
					// Fin Traitement Erreur Cas : GetAll / GetRow		
			}
			
			// Fin ajout alassane
			if($appart <> "") $critere_appart = " AND  D_T_S.APPARTENANCE=$appart ";
			else $critere_appart = "";
			$requete = "SELECT D_T_S.ID_THEME_SYSTEME, D_T.ID_TYPE_THEME, D_T_S.ID, D_T_S.PERE, D_T_S.PRECEDENT, D_T_S.TAILLE_MENU, D_TRAD.LIBELLE
						FROM DICO_THEME_SYSTEME AS D_T_S, DICO_TRADUCTION AS D_TRAD, DICO_THEME AS D_T
						WHERE D_T.ID = D_TRAD.CODE_NOMENCLATURE
						AND D_T.ID = D_T_S.ID
						AND D_TRAD.NOM_TABLE='DICO_THEME'
						AND  D_T_S.ID_SYSTEME=".$secteur.$critere_appart."
						AND D_TRAD.CODE_LANGUE='".$langue."';";
			// récupération du résultat dans un tableau       
					// Traitement Erreur Cas : GetAll / GetRow
			//echo $requete.'<br>';
			try {
							$result_theme	= $GLOBALS['conn_dico']->GetAll($requete);
							//print_r($result_theme);
							if(!is_array($result_theme)){                    
									throw new Exception('ERR_SQL');  
							}
							// tri de la table selon les precedences 
							$this->list = $this->tri_list($result_theme);	                                          
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
		}

		/**
		 * METHODE :  set_theme_courant()
		 * 	permet de placer convenablement le thème courant
		 * @access public
		 */
		public function set_theme_courant() {
    
			if ($_GET['val'] == 'new_etab') { // Lors de la création d'un nouvel étab
				$this->id_theme_systeme  = $this->recherche_theme_def();
			}elseif (isset($_GET['theme_frame'])) { //si $_GET['theme'] existe alors le theme courant est $_GET['theme']
				$this->id_theme_systeme    = $_GET['theme_frame'];
			}elseif (isset($_GET['theme'])) { //si $_GET['theme'] existe alors le theme courant est $_GET['theme']
				$this->id_theme_systeme  = $_GET['theme'];
			}else {
				$this->id_theme_systeme  = $this->recherche_theme_def();
			}
			foreach($this->list as $ord_thm => $thm) {
				if($thm['ID_THEME_SYSTEME'] == $this->id_theme_systeme) {
					$this->id = $thm['ID'];
					$this->pos_thm 	= $ord_thm;
					break;
				}
			}
		}

		/**
		 * METHODE :  set_theme_courant(id_theme_systeme)
		 * 	permet récupérer la variable ID su thème courant
		 *	à partir du thème système
		 * @access public
		 * @param numeric id_theme_systeme : le thème systéme en cours
		 */
		public function set_id_from_id_thm_sys($id_theme_systeme){
        foreach($this->list as $l) {

            if($l['ID_THEME_SYSTEME'] == $id_theme_systeme) {

                $this->id = $l['ID'];
                break;

            }
        }
    }

		/**
		 * METHODE :  set_classe()
		 * 	permet récupérer la classe Métier 
		 *	à partir du ID thème courant
		 * @access public
		 */
		public function set_classe() {
        
        $requete = 'SELECT CLASSE 
                    FROM DICO_THEME 
                    WHERE ID='.$this->id;                    
       
					
        // récupération du résultat dans un tableau
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$res	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($res)){                    
								throw new Exception('ERR_SQL');  
						} 
						$this->classe = $res[0]['CLASSE'];									
				}
				catch(Exception $e){
						//  theme manager est appelé avant session_start() s'il y'un affichage cela risque de 
						/// de provoquer une erreur comme : Cannot send session cookie - headers already sent by 
						//$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
    }

		/**
		 * METHODE :  tri_list(dico, racine=1030)
		 * 	Permet d'effectuer le tri des thèmes conformément à 
		 *	l'ordonancement défini dans la configuration
		 * @access public
		 * @param array dico : Lot des thèmes issus de la Base
		 * @param numeric racine : le Menu de départ qui par défaut = Questionnaire
		 */
		public function tri_list(&$dico, $racine=1030) {

        $noeuds = array();
        $listes = array();
		//echo '<pre>';
		//print_r($dico);
        //on recherche les noeuds et on définit le premier élément
        foreach((array)$dico as $d) {

            //on trouve l'id du premier élément
            if($d['PERE'] == $racine && $d['PRECEDENT'] == 0) {

                $first = $d;

            }

            //si PRECEDENT est égal à 0 c'est un noeud
            if($d['PRECEDENT'] == 0) {

                array_push($noeuds, $d);

            }

        }

        //on construit les listes commençant par des noeuds
        foreach($noeuds as $n) {

            //si la liste pour le noeud n'existe pas alors on la crée
            if(!isset($listes[$n['PERE']])) {

                $listes[$n['PERE']] = array();

            }

            array_push($listes[$n['PERE']], $n);
            $curel = $n;

            while($nextel = $this->tri_get_next_el($dico, $curel)) {

                array_push($listes[$n['PERE']], $nextel);
                $curel = $nextel;

            }

        }

        //on construit le dico ordonné récursivement
        $dico = $this->tri_build_ordered_dico($first, $listes);

        return $dico;

    }

		/**
		 * METHODE :  tri_build_ordered_dico(first, listes)
		 * 	Permet à partir du tableau listes, de récupérer
		 *	les thèmes fils associés au thème $first pour la constitution
		 *	du Menu << Questionannaires >>
		 * @access public
		 * @param array listes : Tableau de thèmes
		 * @param numeric first : thème ayant des fils pour le Menu
		 */
		public function tri_build_ordered_dico($first, $listes) {

        $result = array();

        if(isset($listes[$first['PERE']])) {

            //on boucle sur la liste de l'élément premier
            foreach($listes[$first['PERE']] as $l) {

                //on ajoute les éléments
                array_push($result, $l);

                //et on y rajoute à chaque fois les occurences de la liste correspondant à l'ID courant cad ses enfants
                if(isset($listes[$l['ID_THEME_SYSTEME']])) {

                    $result = array_merge($result, $this->tri_build_ordered_dico($listes[$l['ID_THEME_SYSTEME']][0], $listes));

                }

            }

        }

        return $result;

    }

		/**
		 * METHODE :  tri_get_next_el(dico, el)
		 * 	Permet à partir du tableau dico, de récupérer
		 *	le qui succéde à l'élément el 
		 * @access public
		 * @param array dico : Tableau de thèmes
		 * @param numeric el : un théme donné
		 */
		public function tri_get_next_el(&$dico, $el) {

        foreach((array)$dico as $i=>$d) {

            if($d['PRECEDENT'] == $el['ID_THEME_SYSTEME'] && $d['PERE'] == $el['PERE']) {

                //on détruit l'élément retenu afin de ne pas avoir a boucler sur des éléments déja dispatché
                unset($dico[$i]);

                return $d;

            }

        }

        return false;

    }

		/**
		 * METHODE :  recherche_theme_def()
		 * 	Retrouve le thème par défaut, le tout premier de la Liste 
		 * @access public
		 
		 */
		public function recherche_theme_def() {

        return $this->list[0]['ID_THEME_SYSTEME'];

    }
		
		/**
		 * METHODE :  get_lib_long_theme (id_theme_sys, langue)
		 * 	Retrouve le libellé long du thème système id_theme_sys
		 *	pour la langue en paramètre
		 * @access public
		 * @param array id_theme_sys : un thème systéme donné
		 * @param string langue : langue choisie
		 */
		public function get_lib_long_theme ($id_theme_sys, $langue){
				$requete        = "SELECT LIBELLE
														FROM DICO_TRADUCTION 
														WHERE CODE_NOMENCLATURE=".$id_theme_sys." AND CODE_LANGUE='".$langue."'
														AND NOM_TABLE='DICO_THEME_LIB_LONG'";
				
				// Traitement Erreur Cas : Execute / GetOne
				try {            
						$lib = $GLOBALS['conn_dico']->GetOne($requete);
						if($lib ===false){                
								 throw new Exception('ERR_SQL');   
						}
						return $lib; 								 
				}
				catch (Exception $e) {
						//  theme manager est appelé avant session_start() s'il y'un affichage cela risque de 
						/// de provoquer une erreur comme : Cannot send session cookie - headers already sent by 
						// $erreur = new erreur_manager($e,$requete);
				}        
				// Fin Traitement Erreur Cas : Execute / GetOne
		}
}

?>