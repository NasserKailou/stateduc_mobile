<?php /** 
    * Classe Grille Générique
    * <pre>
    * Cette Classe est destinée aux traitements des données de tous les types
	* de thémes prévus sur les questionnaires. 
    * Elle se charge de la récupération des données en Base pour les disposer 
	* convenablement sur l'interface de saisie ainsi du traitement inverse.
	* Les opérations de contréle des données  sont également confiées é cette Classe
    * </pre>
    * @access public
    * @author Alassane, Touré, Yacine
    * @version 1.1
*/	
class grille {
	
	public $tab_donnees_tpl;
	
	/**
	 * Attribut : code_etablissement
	 * <pre>
	 * Unité de Collecte
	 * </pre>
	 * @var numeric
	 * @access public
	 */   
	public $code_etablissement;	
	
	/**
	 * Attribut : code_annee
	 * <pre>
	 * Année de Colecte 
	 * </pre>
	 * @var numeric
	 * @access public
	 */
	
	public $code_annee;
	
	/**
	 * Attribut : code_filtre
	 * <pre>
	 * On se positionne sur une filtre
	 * </pre>
	 * @var numeric
	 * @access public
	 */
	public $code_filtre;
	
	/**
	 * Attribut : id_systeme
	 * <pre>
	 * Secteur d'Enseignement de l'établissemenr
	 * </pre>
	 * @var numeric
	 * @access public
	 */	
	public $id_systeme;
	
	 
	 /**
	 * Attribut : nomtableliee
	 * <pre>
	 * Contient la liste des tables utilisées par le théme en cours
	 * Toutes les tables devrant etre mises é jour seront listées dans cet attribut
	 * </pre>
	 * @var array
	 * @access public
	 */
	public $nomtableliee	=	array();
	
	 
	 /**
	 * Attribut : sql_data
	 * <pre>
	 * Les requétes d'extraction de données par table mére 
	 * </pre>
	 * @var array
	 * @access public
	 */
	public $sql_data		=	array();
	
	
	/**
	 *
	 * Attribut : tableau_zone_saisie
	 * Liste des zones activées par table mére
	 * @var array
	 * @access public
	 */
	 public $tableau_zone_saisie 			= 	array();
     
    /**
	 * Attribut : tableau_type_zone_base
	 * <pre>
	 * type de chaque zone(Champ) dans la Base
	 * </pre>
	 * @var array
	 * @access public
	 */ 
     public $tableau_type_zone_base 	= 	array();
	 
	/**
	 * Attribut : tab_joins_in_main_tab
	 * <pre>
	 * Jointures entre Table mére principale et Table(s) Secondaire(s)
	 * </pre>
	 * @var array
	 * @access public
	 */   
	 public $tab_joins_in_main_tab	= 	array();
	 	 
	 
	 /**
	 * Attribut : code_nomenclature
	 * <pre>
	 *  Codes de Nomenclatures des zones liées aux tables de Nomenclatures
	 * </pre>
	 * @var array
	 * @access public
	 */
	 public $code_nomenclature 	= 	array();
	
	 
	 /**
	 * Attribut : champs
	 * <pre>
	 * Champs utlisés dans chq table mére pour les aspects de traitements
	 * </pre>		
	 * @var array
	 * @access public
	 */
	 public $champs				=	array();
	 
	 
	 /**
	 * Attribut : nb_cles
	 * <pre>
	 * Contient le nombre de champs clés définies dans chq table mére
	 * </pre>
	 * @var array
	 * @access public
	 */
	 public $nb_cles	=	array();
	 
	 
	 /**
	 * Attribut : nb_lignes
	 * <pre>
	 * Nombre de lignes de la grille (0 = Formulaire, >0 = Grille)
	 * </pre>
	 * @var numeric
	 * @access public
	 */	
	 public $nb_lignes;
	 
	
	 /**
	 * Attribut : classe
	 * <pre>
	 * Contient le nom de la classe metier (grille)
	 * </pre>
	 * @var string 
	 * @access public
	 */	
	 public $classe;
	
	
	 /**
	 * Attribut : type_frame
	 * <pre>
	 * Contient le type de théme
	 * </pre>
	 * @var string 
	 * @access public
	 */	
	 public $type_frame;
	 
	 
	 /** 
	 * Attribut : val_cle
	 * <pre>
	 * Contient les valeurs connues de certains champs clés  comme
	 * $code_etablissement, $code_annee, ...
	 * </pre>
	 * @var array
	 * @access public
	 */
	 public $val_cle	=	array();
	 
	 
	 /** 
	 * Attribut : template
	 * <pre>
	 * Contient la structure HTML du théme pour l'affichage
	 * </pre> 
	 * @var string
	 * @access public
	 */	
	 public $template;
	
	
	/**
	 * Attribut : matrice_donnees
	 * <pre>
	 * Dans cette variable sont rangées les données extraites de la base 
	 * devant etre afficher dans la grille en cours de navigation
	 * ici chaque ligne d'enregistrement est un n-uplet composé des champs 
	 * de chq table mére
	 * </pre> 
	 * @var array
	 * @access public
	 */
	 public $matrice_donnees		=	array();
	 
	
	/**
	 * Attribut : matrice_donnees_tpl
	 * <pre>
	 * Redisposition des données de matrice_donnees
	 * suivant la logique d'affichage imposée par le template
	 * </pre> 
	 * @var array
	 * @access public
	 */
	 public $matrice_donnees_tpl	=	array();
	
	 
	 /**
	 * Attribut : matrice_donnees_post
	 * <pre>
	 * Cette variable est configurée aprés soumission de la page HTML via POST, 
	 * sous la méme base de présentation  que $matrice_donnees
	 * </pre> 
	 * @var array
	 * @access public
	 */
	 public $matrice_donnees_post	=	array();
	  
	 
	 /**
	 * Attribut : matrice_donnees_bdd
	 * <pre>
	 * Cette variable est le résultat de la comparaison entre les données de 
	 * départ et celles du POST. Le contenu de cette variable sera utlisé pour
	 * la MAJ de la base de données
	 * </pre> 
	 * @var array
	 * @access public
	 */	
	 public $matrice_donnees_bdd	=	array();	
	
	 
	 /**
	 * Attribut : conn
	 * <pre>
	 * Dans cette variable on extrait la variable globale de connexion é la 
	 * base pour en faire une propriété de la classe
	 * </pre>
 	 * @var string
	 * @access public
	 */	
	 public $conn;

	 /**
	 * Attribut : id_theme
	 * <pre>
	 * Le théme en cours de traitement
	 * </pre>
 	 * @var integer
	 * @access public
	 */	
	 public $id_theme;
	 public $type_theme;
	 
	 /**
	 * Attribut : VARS_GLOBALS
	 * <pre>
	 * Cette variable nous permet de garder nombre d'informations utilisées 
	 * notamment pour bien gérer l'affichage des données dans la navigation
	 * page par page 
	 * </pre> 
	 * @var array
	 * @access public
	 */
	 public $VARS_GLOBALS = array();
	 
	 
	 /**
	 * Attribut : tab_regles_zone
	 * <pre>
	 * Cette variable nous permet de stocker les differentes régles et
	 * type de contréle de saisie relatifs aux zones de saisies
	 * </pre> 
	 * @var array
	 * @access public
	 */
	 public $tab_regles_zone = array();

	
	 /**
	 * Attribut : new_etab
	 * <pre>
	 * Permet de marquer le moment de création d'un établissement
	 * </pre> 
	 * @var bolean
	 * @access public
	 */
	 public $new_etab = false ;
	 
	/**
	 * Attribut : tab_plage
	 * <pre>
	 * Stocke les Valeurs Maximales en Base des Champs 
	 * dont les valeurs é prendre sont définies sur une 
	 * Plage ou intervalle (Saisie non centralisée)
	 * </pre>
	 * @var array
	 * @access public
	 */   
     public $tab_plage = array();

	/**
	 * Attribut : main_table_mere
	 * <pre>
	 * contient le nom de la table mére principale
	 * </pre>
	 * @var string
	 * @access public
	 */   
	 public $main_table_mere = '';
	 
	/**
	 * Attribut : list_tabms_matric
	 * <pre>
	 * contient les tables méres é structure matricielle
	 * </pre>
	 * @var array
	 * @access public
	 */   
	 public $list_tabms_matric = array();
	
	 
	/**
	 * Attribut : col_zone_Equi_col_data
	 * <pre>
	 * Equivalence entre les colonnes des zones dans tableau_zone_saisie
	 * et les champs de MAJ
	 * </pre>
	 * @var string
	 * @access public
	 */   
	 public $col_zone_Equi_col_data = array();
	 
	

	/**
	 * Attribut : tab_keys_zones
	 * <pre>
	 * Tableau des champs clés  de chaque de table
	 * </pre>
	 * @var array
	 * @access public
	 */   
	 public $tab_keys_zones = array();
	 
	/**
	 * Attribut : tab_not_keys_zones
	 * <pre>
	 * Tableau des champs n'étant pas de type clés  dans chaque table
	 * </pre>
	 * @var string
	 * @access public
	 */   
	 public $tab_not_keys_zones = array();
	 
	
	/**
	 * Attribut : common_tabm_fields_joined
	 * <pre>
	 * Tableau des jointures communes é toutes les tables méres 
	 * </pre>
	 * @var array
	 * @access public
	 */   
	 public $common_tabm_fields_joined = array();
	
	
	/**
	 * Attribut : are_joined_fields
	 * <pre>
	 * Tableau des jointures directes entre les champs des 
	 * tables secondaires et la table mére principale 
	 * </pre>
	 * @var array
	 * @access public
	 */   
	 public $are_joined_fields = array();
	 
	 /**
	 * Attribut : is_from_mobile
	 * <pre>
	 * Variable indiquand si la guille est utilisée depuis
	 * l'application mobile
	 * </pre>
	 * @var boolean
	 * @access public
	 */  
	 public $is_from_mobile = false;
	
	/**
	 * Attribut : is_incremental_field
	 * <pre>
	 * Pour savoir si une zone est incrémentale afin de parser 
	 * les valeurs attribuées dans les plages éventuellement 
	 * </pre>
	 * @var array
	 * @access public
	 */   
	 public $is_incremental_field = array();
	 public $keys_template = array();
	 public $type_gril_eff_fix_col = false;
	 /**
	 * METHODE :  __construct(code_etablissement, code_annee, id_theme, id_systeme)
	 * <pre>
 	 * Constructeur de la classe :
	 * </pre>
	 * @access public
	 * @param numeric $code_etablissement le code de l'établissement
	 * @param numeric $code_annee : année de Collecte
	 * @param numeric $id_theme : le théme courant
	 * @param numeric $id_systeme : le secteur d'enseignement choisi  
	 
	 */ 	 
	function __construct($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre=""){
		
		/**
		* On renseigne certains attributs comme :
		* $code_etablissement, $code_annee, $id_theme, $conn, ...
		*/
		
		$this->code_etablissement	= $code_etablissement;        
    	$this->code_annee 			= $code_annee;
		$this->code_filtre 		= $code_filtre;
		$this->id_systeme 			= $id_systeme;
		$this->id_theme 			= $id_theme;
		$this->conn 				= $GLOBALS['conn'];

		if( $_GET['val'] == 'new_etab' ){ 
            /**
            * Pour dire qu' on est en phase de création d'un nouvel établissement
            * on enléve le code établissement de la session 
            */
            $this->new_etab = true; 
            unset($_SESSION['code_etab']); // 
		} 
		//Recuperation du type de theme
		$req_type_theme = "SELECT ID_TYPE_THEME FROM DICO_THEME WHERE ID=".$id_theme;
		$this->type_theme	= $GLOBALS['conn_dico']->GetOne($req_type_theme);
		//Fin Recuperation du type de theme	
		// Detection type grille_eff_1 avec fix col
		if($this->type_theme==4){
			$req	=	"SELECT		DICO_ZONE.*, DICO_ZONE_SYSTEME.*
						FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME 
						WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
						AND			DICO_ZONE.ID_THEME = DICO_THEME.ID	
						AND			DICO_THEME.ID_TYPE_THEME = ".$this->type_theme.
						" AND		DICO_ZONE.ID_THEME = ".$id_theme.
						" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
						" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
						  AND		DICO_ZONE_SYSTEME.TYPE_OBJET = 'dimension_colonne' ";
							
			$dic	= $GLOBALS['conn_dico']->GetAll($req);
			if(is_array($dic) && count($dic)>0) $this->type_gril_eff_fix_col = true;
		}
		// Fin Detection type grille_eff_1 avec fix col
		
		/**
		*
		* Appel de la méthode get_dico pour renseigner certains attributs é partir de DICO :
		*/
		$this->get_dico($id_theme,$id_systeme); // recupération du DICO en fonction du théme et su secteur
		$dico	=	$GLOBALS['dico'];// Stockage des variables du DICO
		//echo '<pre>';
        //print_r($dico);
        
        // Assigner des variables de la classe avec leurs valeurs respectives de DICO
        if (isset($dico['nomtableliee'])) 			{$this->nomtableliee=$dico['nomtableliee'];}        
        if (isset($dico['tableau_zone_saisie'])) 	{$this->tableau_zone_saisie=$dico['tableau_zone_saisie'];} 
        if (isset($dico['tableau_type_zone_base'])) {$this->tableau_type_zone_base=$dico['tableau_type_zone_base'];}           
        if (isset($dico['code_nomenclature'])) 		{$this->code_nomenclature=$dico['code_nomenclature'];}     
        if (isset($dico['sql_data'])) 				{$this->sql_data=$dico['sql_data'];}     
        if (isset($dico['nb_cles'])) 		 		{$this->nb_cles=$dico['nb_cles'];} 
        if (isset($dico['val_cle']))           		{$this->val_cle=$dico['val_cle'];}
        if (isset($dico['nb_lignes'])) 			    {$this->nb_lignes=$dico['nb_lignes'];}
        if (isset($dico['classe'])) 				{$this->classe=$dico['classe'];}
		if (isset($dico['type_frame'])) 			{$this->type_frame=$dico['type_frame'];}
        if (isset($dico['template']))				{
			if (file_exists($GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template'])) {
				$this->template 	= file_get_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template']);
			}else{
				 print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template'] . ' : Inexistant !<BR>';
			}
		}
		// Préparation des jointures communes aux diff tables méres 
		$this->set_common_tabm_fields_joined();
        
		//Récupération des plages de codes réservés lors des créations ETABLISSEMENTS, ENSEIGNANTS, ...
		//$this->get_plage_codes();
	}
	
	
	/**
	* METHODE :  get_dico( $id_theme,  $id_systeme):
	* Permet de lire la configuration associée au théme et au systéme 
	* d'enseignement depuis DICO pour associer les attributs de la 
	* classe é leurs valeurs respectives
	*
	*<pre>
	*	--> Récupérer le propriétés du théme :  $id_theme
	*	--> Parcourir les tables méres du théme
	*		--> Récupérer les zones et leurs caractéristiques
	*		--> Préparer la requéte d'extraction des données
	*	--> Préparer le contenu de la variable dico[]    ( explotée par __construct() )
	*</pre>
	* @access public
	* @param numeric $id_theme : le théme en cours
	* @param numeric $id_systeme : le systeme d'enseignement en cours
	* 
	*/
	function get_dico($id_theme,$id_systeme){
		$code_etablissement     = $this->code_etablissement;        
		$code_annee             = $this->code_annee;
		$code_filtre             = $this->code_filtre;
		//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
		${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']} = $code_annee;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;
		if(isset($_SESSION['code_regroupement']) && $_SESSION['code_regroupement'] <> ''){
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']} = $_SESSION['code_regroupement'];
		}
		//Fin ajout Hebie
		$conn					= $GLOBALS['conn'];
        unset($dico); // préparation de la variable DICO
        // === SESSION 28 DIAG : entree get_dico ===
        $_diag_appart_top = isset($_SESSION['type_ent_stat']) ? $_SESSION['type_ent_stat'] : 'UNDEF';
        $_diag_top = date('Y-m-d H:i:s').';get_dico_ENTER;id_theme='.$id_theme.';id_systeme='.$id_systeme.';type_ent_stat='.$_diag_appart_top."\n";
        error_log('[DIAG_S28_TOP] '.$_diag_top);
        @file_put_contents(dirname(__FILE__).'/../../../../moblogs/diag_grille.log', $_diag_top, FILE_APPEND);
        @file_put_contents(sys_get_temp_dir().'/diag_stateduc.log', $_diag_top, FILE_APPEND);
        // === FIN SESSION 28 DIAG ===
        
        // pour récupérer le nom du FRAME, le nombre de lignes, et la CLASSE
		$sql 						=   ' SELECT A.*, B.FRAME, B.NB_LIGNES_FRAME  FROM DICO_THEME  A, DICO_THEME_SYSTEME B '.
                                        ' WHERE A.ID=B.ID '.
                                        ' AND A.ID='.$id_theme.
                                        ' AND B.ID_SYSTEME='.$id_systeme.
                                        // Session 28 : filtre APPARTENANCE pour isoler le bon FRAME classique vs mobile
                                        // Sans ce filtre, quand ID=900 existe en APPARTENANCE=1 ET APPARTENANCE=2,
                                        // la requete retourne 2 lignes et prend le mauvais FRAME (ex: infos_gen_2.html au lieu de 9002.html)
                                        (isset($_SESSION['type_ent_stat']) && $_SESSION['type_ent_stat']<>'' ? ' AND B.APPARTENANCE='.$_SESSION['type_ent_stat'] : '');
									  
		// Traitement Erreur Cas : Execute / GetOne
		try {            
				$rs							= $GLOBALS['conn_dico']->Execute($sql);
				if($rs === false){                
                    throw new Exception('ERR_SQL');   
				}
				
				$dico['nb_lignes']		=	$rs->fields['NB_LIGNES_FRAME'];
				$dico['template']		=	$rs->fields['FRAME'];
				$dico['classe']			=	$rs->fields['CLASSE'];
				$dico['type_frame']		=	$rs->fields['ID_TYPE_THEME'];
		}
		catch (Exception $e) {
			$erreur = new erreur_manager($e,$sql);
        $_diag_fe = date('Y-m-d H:i:s').';get_dico_FRAME_ERR;id_theme='.$id_theme.';id_systeme='.$id_systeme.';msg='.$e->getMessage()."\n";
        error_log('[DIAG_S28_FRAME_ERR] '.$_diag_fe);
        @file_put_contents(dirname(__FILE__).'/../../../../moblogs/diag_grille.log', $_diag_fe, FILE_APPEND);
        @file_put_contents(sys_get_temp_dir().'/diag_stateduc.log', $_diag_fe, FILE_APPEND);
		}        
		// Fin Traitement Erreur Cas : Execute / GetOne
        // === SESSION 28 DIAG : resultat FRAME ===
        $_diag_fr = date('Y-m-d H:i:s').';get_dico_FRAME_RESULT;id_theme='.$id_theme.';id_systeme='.$id_systeme.
            ';frame='.(isset($dico['template'])?$dico['template']:'NULL').
            ';nb_lignes='.(isset($dico['nb_lignes'])?$dico['nb_lignes']:'NULL').
            ';sql_frame='.str_replace("\n",' ',$sql)."\n";
        error_log('[DIAG_S28_FRAME_RES] '.$_diag_fr);
        @file_put_contents(dirname(__FILE__).'/../../../../moblogs/diag_grille.log', $_diag_fr, FILE_APPEND);
        @file_put_contents(sys_get_temp_dir().'/diag_stateduc.log', $_diag_fr, FILE_APPEND);
        // === FIN ===
		///

		// récupération des zones de saisie de la grille
		// Identification de l'ensemble des tables liées
		$sqltableliee   =   'SELECT DISTINCT DICO_ZONE.TABLE_MERE, 
                            DICO_ZONE.PRIORITE FROM DICO_ZONE, DICO_ZONE_SYSTEME 
						    WHERE DICO_ZONE.ID_THEME ='.$id_theme.
						    ' AND DICO_ZONE.TABLE_MERE IS NOT NULL 
						    AND DICO_ZONE_SYSTEME.ID_ZONE=DICO_ZONE.ID_ZONE
						    AND DICO_ZONE_SYSTEME.ACTIVER=1
						    AND DICO_ZONE_SYSTEME.ID_SYSTEME='.$id_systeme.
						    ' ORDER BY DICO_ZONE.PRIORITE';                        
                        
		//echo $sqltableliee.'<br><br>';
		try { 
						$rstableliee				= $GLOBALS['conn_dico']->Execute($sqltableliee);   
                                  
						if($rstableliee ===false){                
								 throw new Exception('ERR_SQL');   
						}
						$nb_table_liee				=   $rstableliee->RecordCount();
                        // === SESSION 28 DIAG : get_dico TABLE_MERE ===
                        $_diag_appart = isset($_SESSION['type_ent_stat']) ? $_SESSION['type_ent_stat'] : 'UNDEF';
                        $_diag_line_dico = date('Y-m-d H:i:s').';get_dico;theme='.$id_theme.';systeme='.$id_systeme.
                            ';type_ent_stat='.$_diag_appart.';frame='.(isset($dico['template'])?$dico['template']:'UNSET').
                            ';nb_table_liee='.$nb_table_liee.';sqltableliee='.str_replace("\n",' ',$sqltableliee)."\n";
                        error_log('[DIAG_S28_DICO] '.$_diag_line_dico);
                        @file_put_contents(dirname(__FILE__).'/../../../../moblogs/diag_grille.log', $_diag_line_dico, FILE_APPEND);
                        @file_put_contents(sys_get_temp_dir().'/diag_stateduc.log', $_diag_line_dico, FILE_APPEND);
                        // === FIN SESSION 28 DIAG ===
						
						$pile_jointure	        	= 	array();
						$tab_order_by	        	=	array();
						$tab_criteres	        	=	array();
						$this->tab_regles_zone  	=   array(); 
						$this->list_tabms_matric 	=   array(); 
						$this->tab_keys_zones		= 	array();
						$this->tab_not_keys_zones	= 	array();
						
						$set_main_tab = true;
                        while (!$rstableliee->EOF){ //POUR CHQ TABLE MERE
							if($set_main_tab == true){ // Récupération de la table mére principale
								$this->main_table_mere = $rstableliee->fields['TABLE_MERE'];
							}
							$set_main_tab = false ;
							$tableau_zone_saisie = array();
							$tableau_type_zone_base = array();
							$nomtableliee	    =	$rstableliee->fields['TABLE_MERE']; // TABLE MERE COURANTE
							$this->tab_keys_zones[$nomtableliee] = array();
							$this->tab_not_keys_zones[$nomtableliee] = array();
							$this->champs[$nomtableliee]	= array();
														
                            $sql_donnees	    =	'SELECT DISTINCT '; // On commence la création de la requéte pour la sélection 
                                                                        // selection des données de la TABLE MERE COURANTE
							$pile_table_liee[]	=	$nomtableliee; // on garde une pile des TABLE MERE
							$nb_cles			=	0; 
							$passe_type_e	    =	0;
							$id_zone			=	0;
							
							
							$is_matric_curr_tabm = false ;
							// Récupération des  zones et de leurs caractéristiques en fonction de la TABLE MERE COURANTE, secteur et théme		
                            $sqlzone_			=   ' SELECT DICO_ZONE.ID_ZONE, DICO_ZONE.TABLE_FILLE, DICO_ZONE.ORDRE,'. 
                                                    ' DICO_ZONE.SQL_REQ, DICO_ZONE.CHAMP_PERE, DICO_ZONE.TYPE_ZONE_BASE, DICO_ZONE.ORDRE_TRI,DICO_ZONE.ID_ZONE_REF, 
                                                      DICO_ZONE_SYSTEME.*'.
                                                    ' FROM DICO_ZONE,DICO_ZONE_SYSTEME'.			 
                                                    ' WHERE DICO_ZONE.ID_ZONE=DICO_ZONE_SYSTEME.ID_ZONE'.
                                                    ' AND DICO_ZONE_SYSTEME.ID_SYSTEME ='.$id_systeme.
                                                    ' AND DICO_ZONE_SYSTEME.ACTIVER =1'.
                                                    ' AND DICO_ZONE.ID_THEME ='.$id_theme.										
                                                    ' AND DICO_ZONE.TABLE_MERE=\''.$nomtableliee.'\'
                                                      ORDER BY DICO_ZONE.ORDRE';
							//echo "<br>/// $sqlzone_ ///<br>";
							//var_dump($rs);
							// Traitement Erreur Cas : Execute / GetOne
							try {
								$all_zones_curr_tabm	= $GLOBALS['conn_dico']->GetAll($sqlzone_);
								if(!is_array($all_zones_curr_tabm)){                    
									throw new Exception('ERR_SQL');  
								} 
								if($this->is_tabm_matricielle($all_zones_curr_tabm)){
									$this->list_tabms_matric[] = $nomtableliee ;
									$is_matric_curr_tabm = true ;
								}
							}
							catch(Exception $e){
									$erreur = new erreur_manager($e,$requete);
							}
							
							try {            
									$rs	= $GLOBALS['conn_dico']->Execute($sqlzone_);
									if($rs ===false){                
										throw new Exception('ERR_SQL');   
									}
									$cptr			=	0;
									$val_cle 		= 	array();
									$criteres		=	'';
									$cptr_pass 		= 	0; 
									$i_col_zone		= 	-1;
									$i_col_data		= 	-1;
									
									while (!$rs->EOF){ // POUR CHQ ZONE de la TABLE MERE COURANTE
                                        //echo'bass<br><pre>';
                                        //var_dump($rs);
                                        $zone_saisie = array();
										$i_col_zone++;
										
                                        // Si la ZONE COURANTE est une clé dans la TABLE MERE 
										if (
												trim($rs->fields['TYPE_OBJET'])=='systeme'	or
												trim($rs->fields['TYPE_OBJET'])=='liste_checkbox' or
												trim($rs->fields['TYPE_OBJET'])=='loc_etab' or
												trim($rs->fields['TYPE_OBJET'])=='systeme_valeur_unique' or
												trim($rs->fields['TYPE_OBJET'])=='systeme_valeur_multiple' or
												trim($rs->fields['TYPE_OBJET'])=='systeme_text' or
												trim($rs->fields['TYPE_OBJET'])=='systeme_liste_radio' or
												trim($rs->fields['TYPE_OBJET'])=='systeme_combo' or
												trim($rs->fields['TYPE_OBJET'])=='dimension_ligne' or
												trim($rs->fields['TYPE_OBJET'])=='dimension_colonne'
                                            ){
													
                                                        //echo $rs->fields['TYPE_OBJET'].'/'.$rs->fields['CHAMP_PERE'].'<br>';
                                                    
													                                                    
													if ( (trim($rs->fields['TYPE_OBJET'])=='systeme') and (trim($rs->fields['CHAMP_PERE'])==trim($GLOBALS['PARAM']['CODE_ETABLISSEMENT'])) ){
														
                                                        // s'il s'agit du CODE_ETABLISSEMENT la valeur de la clé est préparée et rangée dans  val_cle[CODE_ETABLISSEMENT] 
                                                        if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1) $val_cle[$rs->fields['CHAMP_PERE']] 	= '' ;
														else $val_cle[$rs->fields['CHAMP_PERE']] 	= $this->code_etablissement ;
                                                        // Cette valeur est prise en compte dans les critéres de la requéte SQL_REQ de Selection des données de la TABLE MERE
														if(!preg_match('/.'.trim($rs->fields['CHAMP_PERE']).'/',$criteres)){
															//echo "$nomtableliee<pre>";
															//print_r($_SESSION['liste_etab']);
															//Modif HEBIE pr transmission en critere d'une liste d'etablissements pr TMIS
															/*echo "code_etab=".$this->code_etablissement."<br>";
															echo "liste_etab=<pre>";
															print_r($_SESSION['liste_etab']);*/
															if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
																if (($nb_table_liee==1)&& ($cptr_pass==0)){
																	$tab_criteres[$nomtableliee].=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].' IN ('.implode(', ',$_SESSION['liste_etab']).')';
																}else{
																	$tab_criteres[$nomtableliee].=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].' IN ('.implode(', ',$_SESSION['liste_etab']).')';
																}
															}else{	
																if (($nb_table_liee==1)&& ($cptr_pass==0)){
																	$tab_criteres[$nomtableliee].=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_etablissement;
																}else{
																	$tab_criteres[$nomtableliee].=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_etablissement;
																}
															}
															//Fin Modif HEBIE
															
														}
														$cptr_pass++;
													}
													elseif ( (trim($rs->fields['TYPE_OBJET'])=='systeme') and  (trim($rs->fields['CHAMP_PERE'])==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']) ){
														// s'il s'agit du CODE_TYPE_ANNEE la valeur de la clé est préparée et rangée dans  val_cle[CODE_TYPE_ANNEE] 
                                                        $val_cle[$rs->fields['CHAMP_PERE']] 	= $this->code_annee;
                                                        // Cette valeur est prise en compte dans les critéres de la requéte SQL_REQ de Selection des données de la TABLE MERE														
                                                        if(!preg_match('/.'.trim($rs->fields['CHAMP_PERE']).'/',$criteres)){
															if (($nb_table_liee==1)&& ($cptr_pass==0)){
																$tab_criteres[$nomtableliee].=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_annee;
															}else{
																$tab_criteres[$nomtableliee].=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_annee;
															}		
														}
														$cptr_pass++;	
													}
												elseif ( (trim($rs->fields['TYPE_OBJET'])=='systeme') and  (trim($rs->fields['CHAMP_PERE'])==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']) ){
														// s'il s'agit du CODE_TYPE_PERIODE la valeur de la clé est préparée et rangée dans  val_cle[CODE_TYPE_PERIODE] 
                                                        $val_cle[$rs->fields['CHAMP_PERE']] 	= $this->code_filtre;
                                                        // SESSION 58 — FIX KOSAVE thèmes sans filtre (10502 DONNEES_ETABLISSEMENT, etc.)
                                                        // ROOT CAUSE : quand code_filtre='' (thème sans filtre période, app mobile),
                                                        // le SQL produit ".CHAMP_PERE=" (sans valeur) → syntaxe invalide → Execute()===false
                                                        // → theme_data_MAJ_ok=false → KOSAVE.
                                                        // FIX : ne construire la clause WHERE filtre QUE si code_filtre est non vide.
                                                        // Sans clause filtre → matrice complète lue → comparer() correct → OKSAVE.
                                                        if(trim($this->code_filtre) != '') {
                                                            if(!preg_match('/.'.trim($rs->fields['CHAMP_PERE']).'/',$criteres)){
                                                                if (($nb_table_liee==1)&& ($cptr_pass==0)){
                                                                    $tab_criteres[$nomtableliee].=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_filtre;
                                                                }else{
                                                                    $tab_criteres[$nomtableliee].=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_filtre;
                                                                }		
                                                            }
                                                        }
														$cptr_pass++;	
													}
													elseif ( (trim($rs->fields['TYPE_OBJET'])=='systeme') and  (trim($rs->fields['CHAMP_PERE'])==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']) ){
														// s'il s'agit du CODE_REGROUPEMENT la valeur de la clé est préparée et rangée dans  val_cle[CODE_REGROUPEMENT] 
                                                        if(isset($_SESSION['code_regroupement'])) 		$val_cle[$rs->fields['CHAMP_PERE']] 	= $_SESSION['code_regroupement'];
														elseif(isset($GLOBALS['code_regroupement'])) 	$val_cle[$rs->fields['CHAMP_PERE']] 	= $GLOBALS['code_regroupement'];
                                                        // Cette valeur est prise en compte dans les critéres de la requéte SQL_REQ de Selection des données de la TABLE MERE
                                                        if(!preg_match('/.'.trim($rs->fields['CHAMP_PERE']).'/',$criteres)){
															if (($nb_table_liee==1)&& ($cptr_pass==0)){
																$tab_criteres[$nomtableliee].=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$val_cle[$rs->fields['CHAMP_PERE']];
															}else{
																$tab_criteres[$nomtableliee].=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$val_cle[$rs->fields['CHAMP_PERE']];
															}		
														}
														$cptr_pass++;	
													}
						
													elseif ( isset($rs->fields['VALEUR_CONSTANTE']) and trim($rs->fields['VALEUR_CONSTANTE'])<>'' ){
														// si la clé a une valeur par défaut, celle ci est mise dans  val_cle[NOM DE LA CLE] 
														$val_cle[$rs->fields['CHAMP_PERE']] 	= $rs->fields['VALEUR_CONSTANTE'];
                                                        // Cette valeur est prise en compte dans les critéres de la requéte SQL_REQ de Selection des données de la TABLE MERE
                                                        if(!preg_match('/.'.trim($rs->fields['CHAMP_PERE']).'/',$criteres)){
															if (($nb_table_liee==1)&& ($cptr_pass==0)){
																$tab_criteres[$nomtableliee].=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$rs->fields['VALEUR_CONSTANTE'];
															}else{
																$tab_criteres[$nomtableliee].=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$rs->fields['VALEUR_CONSTANTE'];
															}		
														}
														$cptr_pass++;	
											}
											$this->tab_keys_zones[$nomtableliee][$i_col_zone] = $i_col_zone ;
											$nb_cles++; // on décompte le nombre de clés de la TABLE MERE 
										}else{
											if( $rs->fields['TYPE_OBJET'] <> 'label' ){
												$this->tab_not_keys_zones[$nomtableliee][$i_col_zone] = $i_col_zone ;
											}
										}
										
										switch ($rs->fields['TYPE_OBJET']){ // Traitement d'équivalences entre type cotés affichage  et type cotés metier
                                            case 'text': 						{$zone_saisie['type'] 	= 's';          break;}
                                            case 'liste_radio':					{$zone_saisie['type'] 	= 'm';          break;}
                                            case 'combo':						{$zone_saisie['type'] 	= 'co';         break;}
                                            case 'checkbox':					{$zone_saisie['type'] 	= 'ch';         break;}
											case 'text_valeur_multiple':		{$zone_saisie['type'] 	= 'tvm'; 		break;}
                                            case 'liste_checkbox':				{$zone_saisie['type'] 	= 'li_ch';      break;}
                                            case 'systeme':						{$zone_saisie['type'] 	= 'c';          break;}
											case 'dimension_ligne':				{$zone_saisie['type'] 	= 'dim_lig';    break;}
											case 'dimension_colonne':			{$zone_saisie['type'] 	= 'dim_col';    break;}
											//case 'zone_matricielle':			{$zone_saisie['type'] 	= 'zone_mat';    break;}
                                            case 'valeur_multiple':				{$zone_saisie['type'] 	= 'e';          break;}
                                            case 'systeme_valeur_unique':		{$zone_saisie['type'] 	= 'csu';        break;}
                                            case 'systeme_valeur_multiple':	    {$zone_saisie['type'] 	= 'cmm';        break;}
                                            case 'booleen':						{$zone_saisie['type'] 	= 'b';          break;}
                                            case 'label':						{$zone_saisie['type'] 	= 'l';          break;}
                                            case 'loc_etab':					{$zone_saisie['type'] 	= 'loc_etab';   break;}
                                            case 'systeme_liste_radio':			{$zone_saisie['type'] 	= 'cmu'; 	    break;}
											case 'systeme_combo':				{$zone_saisie['type'] 	= 'sco';	    break;}
											case 'systeme_text':				{$zone_saisie['type'] 	= 'sys_txt';	break;}
											case 'hidden_field':				{$zone_saisie['type'] 	= 'hidden_field';	break;}
										}
										
										if($is_matric_curr_tabm == true){
											switch ($zone_saisie['type']){
												case 's'	:
												case 'co'	:
												case 'b'	:
												case 'm'	:
												case 'ch'	:
												case 'hidden_field'	:	{
													$zone_saisie['ss_type'] 	= $zone_saisie['type'];
													$zone_saisie['type'] 		= 'zone_mat';
													break ;
												}
												
											}
										}	
										if ($rs->fields['ORDRE_TRI'] > 0){
                                            // s'il s'agit d'un champ de tri, le mettre dans la pile des champs de tri
											$tab_order_by[$nomtableliee][$rs->fields['ORDRE_TRI']]	=	$rs->fields['CHAMP_PERE']; 
										}	
										
                                        // Récupéraion des régles de saisies définies autour de la zone                                     
										$sql_regles =' 	SELECT  *
                                                        FROM   DICO_REGLE_ZONE, DICO_REGLE_ZONE_ASSOC 
                                                        WHERE DICO_REGLE_ZONE.ID_REGLE_ZONE = DICO_REGLE_ZONE_ASSOC.ID_REGLE_ZONE
                                                        AND    DICO_REGLE_ZONE_ASSOC.ID_ZONE ='.$rs->fields['ID_ZONE'];
										
										//echo "<br> sql_regles = $sql_regles";
										// Traitement Erreur Cas : Execute / GetOne
										try {            
												$rs_regles	=	$GLOBALS['conn_dico']->Execute($sql_regles);
												if($rs_regles === false){                
                                                     throw new Exception('ERR_SQL');   
												}
												if($rs_regles->RecordCount()>0){
                                                    //$nom_champ = $rs->fields['CHAMP_PERE'] ;
                                                    $this->tab_regles_zone[$rs->fields['ID_ZONE']] = array();
                                                    while(!$rs_regles->EOF){ // Parcours des régles 
                                                        // Récupération des Spécificités de chaque régle
                                                        $id_regle 					= $rs_regles->fields['ID_REGLE_ZONE'] ;
                                                        $type_donnees 				= $rs_regles->fields['TYPE_DONNEES'] ;
                                                        $taille_donnees 			= $rs_regles->fields['TAILLE_DONNEES'] ;
                                                        $format_donnees 			= $rs_regles->fields['FORMAT_DONNEES'] ;
                                                        $intervalle_valeurs 		= $rs_regles->fields['INTERVALLE_VALEURS'] ;
                                                        $valeur_minimale 			= $rs_regles->fields['VALEUR_MINIMALE'] ;
                                                        $valeur_maximale 			= $rs_regles->fields['VALEUR_MAXIMALE'] ;
                                                        $controle_presence 			= $rs_regles->fields['CONTROLE_PRESENCE'] ;
                                                        $controle_parution 			= $rs_regles->fields['CONTROLE_PARUTION'] ;
                                                        $controle_obligation 		= $rs_regles->fields['CONTROLE_OBLIGATION'] ;
                                                        $controle_integrite_ref     = $rs_regles->fields['CONTROLE_INTEGRITE_REF'] ;
                                                        $controle_edition 			= $rs_regles->fields['CONTROLE_EDITION'] ;
														$vals_enum		 			= $rs_regles->fields['VALEURS_ENUM'] ;
														$controle_unicite 			= $rs_regles->fields['CONTROLE_UNICITE'] ;
                                                        
                                                        if($type_donnees) $this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['type_donnees'] 	                =	$type_donnees;
                                                                
                                                        if($taille_donnees)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['taille_donnees']                 =	$taille_donnees;
                        
                                                        if($format_donnees)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['format_donnees']                 =	$format_donnees;
                        
                                                        if($intervalle_valeurs)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['intervalle_valeurs']         =	$intervalle_valeurs;
                        
                                                        if($valeur_minimale)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['valeur_minimale']            =	$valeur_minimale;
                        
                                                        if($valeur_maximale)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['valeur_maximale']            =	$valeur_maximale;
                        
                                                        if($controle_presence)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['controle_presence']          =	$controle_presence;
                        
                                                        if($controle_parution)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['controle_parution']          =	$controle_parution;
                        
                                                        if($controle_obligation)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['controle_obligation']    =	$controle_obligation;
														
														if($controle_unicite)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['controle_unicite']    =	array( 'champ' => $rs->fields['CHAMP_PERE'], 'type' => $rs->fields['TYPE_ZONE_BASE'], 'table_mere' => $nomtableliee) ;
                        
                                                        if($controle_integrite_ref)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['controle_integrite_ref'] =	$controle_integrite_ref;
                        
                                                        if($controle_edition)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['controle_edition']           =	$controle_edition;
														
														if($vals_enum)	$this->tab_regles_zone[$rs->fields['ID_ZONE']][$id_regle]['vals_enum']           =	$vals_enum;
                                                        
                                                        $rs_regles->MoveNext();
                                                    }// 
                                                    //echo"$nom_champ<pre>";
                                                    //print_r(	$tab_regles_zone[$nom_champ]);
												}

										}
										catch (Exception $e) {
                                             $erreur = new erreur_manager($e,$sql_regles);
										}        
										// Fin Traitement Erreur Cas : Execute / GetOne
										if( $zone_saisie['type'] == 'e' ){ // S'il s'agit d'un type Effectif    
											//$pass=true;	
											if ($passe_type_e == 0){ 
												// on récupére les éléments essentiels : comme le SQL_REQ, la TABLE FILLE, ...
                                                // au premier passage sur un type effectif
                                                                    
												$ordre			=	$rs->fields['ORDRE'];
												$table_fille	=	$rs->fields['TABLE_FILLE'];
												$pos_type_e		=	$id_zone;						
												$tableau_zone_saisie[$pos_type_e]['ordre'] 	= $rs->fields['ORDRE'];
												$tableau_zone_saisie[$pos_type_e]['type'] 	= $zone_saisie['type'];//'e'; 
												$tableau_zone_saisie[$pos_type_e]['id_zone'] = $rs->fields['ID_ZONE'] ;                       
																		 
												// On récupére les éléments comme SQL_REQ, TABLE_FILLE, CHAMP_FILS é partir de la ZONE REF
                                                $sql_zone_ref = ' SELECT DICO_ZONE.TABLE_FILLE, DICO_ZONE.SQL_REQ,DICO_ZONE.CHAMP_FILS , DICO_ZONE.CHAMP_PERE '.
																' FROM DICO_ZONE'.			 
																' WHERE DICO_ZONE.ID_ZONE='.$rs->fields['ID_ZONE_REF'];										
																								
												// Traitement Erreur Cas : Execute / GetOne
												try {            
														$rszone_ref	=	$GLOBALS['conn_dico']->execute($sql_zone_ref);
														if($rszone_ref ===false){                
																 throw new Exception('ERR_SQL');   
														}
														$tableau_zone_saisie[$pos_type_e]['table_ref'] 		= $rszone_ref->fields['TABLE_FILLE'];
														$tableau_zone_saisie[$pos_type_e]['champ_ref'] 		= $rszone_ref->fields['CHAMP_PERE'];
														$tableau_zone_saisie[$pos_type_e]['id_zone_ref'] 	= $rs->fields['ID_ZONE_REF'];
														if (!is_null($rszone_ref->fields['SQL_REQ']) and (trim($rszone_ref->fields['SQL_REQ'])<>'') ){
															$temp_sql	=	$rszone_ref->fields['SQL_REQ'];						
															$chaine_eval ="\$tableau_zone_saisie[$pos_type_e]['sql'] =\"$temp_sql\";";						
															eval ($chaine_eval);	
														}else{
															$tableau_zone_saisie[$pos_type_e]['sql'] 	= '';
														}	
												}
												catch (Exception $e) {
                                                    $erreur = new erreur_manager($e,$sql_zone_ref);
												}        
												// Fin Traitement Erreur Cas : Execute / GetOne												
												$id_zone++;
											}
											$passe_type_e++;
											//$tab_champ[]=$rs->fields['CHAMP_PERE'];
											$tableau_zone_saisie[$pos_type_e]['champ'][]		=	$rs->fields['CHAMP_PERE']; // On réalise un tableau des champs péres de type effectif
                                            // Modif Alassane
											if ($zone_saisie['type'] != 'l'){
                                            	$tableau_type_zone_base[$rs->fields['CHAMP_PERE']]	=   $rs->fields['TYPE_ZONE_BASE'];
                                            }
											// Fin Modif Alassane 
                                            // Rangement des propriétés requises sur la zone                                             
										}
										elseif( $zone_saisie['type'] == 'tvm' ){ // S'il s'agit d'un text valeur multiple    
											//$pass=true;	
											/*if($zone_saisie['type']=='tvm'){
												$zone_ref = get_zone_ref_of($rs->fields['ID_ZONE_REF']);
												$rs->fields['TABLE_FILLE'] 	= $zone_ref['TABLE_FILLE'] ;
												$rs->fields['CHAMP_FILS'] 	= $zone_ref['CHAMP_FILS'] ;
												$rs->fields['SQL_REQ'] 			= $zone_ref['SQL_REQ'] ;
											}  */                                            

											$ordre			=	$rs->fields['ORDRE'];
											$table_fille	=	$rs->fields['TABLE_FILLE'];
											$pos_type_e		=	$id_zone;						
											$tableau_zone_saisie[$pos_type_e]['ordre'] 	= $rs->fields['ORDRE'];
											$tableau_zone_saisie[$pos_type_e]['type'] 	= $zone_saisie['type'];//'tvm'; 
											$tableau_zone_saisie[$pos_type_e]['id_zone'] = $rs->fields['ID_ZONE'] ;                          
																	 
											// On récupére les éléments comme SQL_REQ, TABLE_FILLE, CHAMP_FILS é partir de la ZONE REF
											$sql_zone_ref = ' SELECT DICO_ZONE.TABLE_FILLE, DICO_ZONE.SQL_REQ,DICO_ZONE.CHAMP_FILS , DICO_ZONE.CHAMP_PERE '.
															' FROM DICO_ZONE'.			 
															' WHERE DICO_ZONE.ID_ZONE='.$rs->fields['ID_ZONE_REF'];										
																							
											// Traitement Erreur Cas : Execute / GetOne
											try {            
													$rszone_ref	=	$GLOBALS['conn_dico']->execute($sql_zone_ref);
													if($rszone_ref ===false){                
															 throw new Exception('ERR_SQL');   
													}
													$tableau_zone_saisie[$pos_type_e]['table_ref'] 		= $rszone_ref->fields['TABLE_FILLE'];
													$tableau_zone_saisie[$pos_type_e]['champ_ref'] 		= $rszone_ref->fields['CHAMP_PERE'];
													$tableau_zone_saisie[$pos_type_e]['id_zone_ref'] 	= $rs->fields['ID_ZONE_REF'];
													if (!is_null($rszone_ref->fields['SQL_REQ']) and (trim($rszone_ref->fields['SQL_REQ'])<>'') ){
														$temp_sql	=	$rszone_ref->fields['SQL_REQ'];						
														$chaine_eval ="\$tableau_zone_saisie[$pos_type_e]['sql'] =\"$temp_sql\";";						
														eval ($chaine_eval);	
													}else{
														$tableau_zone_saisie[$pos_type_e]['sql'] 	= '';
													}	
											}
											catch (Exception $e) {
												$erreur = new erreur_manager($e,$sql_zone_ref);
											}        
											// Fin Traitement Erreur Cas : Execute / GetOne												
											$id_zone++;
											$passe_type_e++;
											//$tab_champ[]=$rs->fields['CHAMP_PERE'];
											$tableau_zone_saisie[$pos_type_e]['champ']		    =	$rs->fields['CHAMP_PERE'];
											// Modif Alassane
											$tableau_type_zone_base[$rs->fields['CHAMP_PERE']]	= $rs->fields['TYPE_ZONE_BASE'];
											// Fin Modif Alassane 
											// Rangement des propriétés requises sur la zone                                             
										}
                                        else{	
                                            // S'il ne s'agit d'un type autre  que le type 'e' et le type 'tvm'
											//Modif HEBIE
											if($rs->fields['TABLE_FILLE']=='' && $rs->fields['TABLE_INTERFACE']<>'' && $rs->fields['CHAMP_INTERFACE']<>'' && $rs->fields['REQUETE_CHAMP_INTERFACE']<>''){
												$rs->fields['TABLE_FILLE']=$rs->fields['TABLE_INTERFACE'];
												$rs->fields['SQL_REQ']=$rs->fields['REQUETE_CHAMP_INTERFACE'];
												//
											}
											//Fin Modif HEBIE
											
											$zone_saisie['champ'] 		= $rs->fields['CHAMP_PERE'];
											$zone_saisie['table_ref']   = $rs->fields['TABLE_FILLE'];
											$zone_saisie['ordre'] 		= $rs->fields['ORDRE'];
											$zone_saisie['id_zone'] 	= $rs->fields['ID_ZONE'] ;   
												
															 
                                            // Modif Alassane
                                                $tableau_type_zone_base[$rs->fields['CHAMP_PERE']] 		= $rs->fields['TYPE_ZONE_BASE'];
                                            // Fin Modif Alassane                    
											
											if($rs->fields['BOUTON_INTERFACE']){
                                                $zone_saisie['champ_interface'] 		= $rs->fields['CHAMP_INTERFACE'];
                                                $zone_saisie['sql_interface'] 			= $rs->fields['REQUETE_CHAMP_INTERFACE'];
											}
											if( 
															( $rs->fields['BOUTON_INTERFACE']=='saisie' ) and
															( trim($rs->fields['CHAMP_INTERFACE']) <> trim($rs->fields['CHAMP_PERE']) ) and
															( $rs->fields['REQUETE_CHAMP_SAISIE'] )
												){
													$zone_saisie['sql_saisie'] 					= $rs->fields['REQUETE_CHAMP_SAISIE'];
											}
											
											// Harmonisation Bass Hebie
											//$zone_saisie['DynContentObj'] = 0 ;
											if( (trim($rs->fields['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($rs->fields['CHAMP_INTERFACE']) <> '') && (trim($rs->fields['TABLE_INTERFACE']) <> '') ){
												//if( ereg('\$code_annee', $rs->fields['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $rs->fields['REQUETE_CHAMP_INTERFACE']) ){
													$zone_saisie['DynContentObj'] 	= 1 ;
													$zone_saisie['champ_interface'] = $rs->fields['CHAMP_INTERFACE'];
													$zone_saisie['sql_interface'] 	= $rs->fields['REQUETE_CHAMP_INTERFACE'];
													$zone_saisie['table_interface'] = $rs->fields['TABLE_INTERFACE'];
													
													$zone_saisie['sql'] = $rs->fields['REQUETE_CHAMP_INTERFACE'];
													$zone_saisie['table_ref']  = $rs->fields['TABLE_INTERFACE'];
												//}
											}
														
											if (!is_null($rs->fields['SQL_REQ']) and (trim($rs->fields['SQL_REQ'])<>'') ){
												$temp_sql	=	$rs->fields['SQL_REQ'];
												//Ajout HEBIE pr gestion code_etablissement TMIS
												if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
													$temp_sql = preg_replace('/'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'[[:space:]]*=/', $GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' IN ', $temp_sql);
													$code_etablissement = '('.implode(', ',$_SESSION['liste_etab']).')';
													${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
													$chaine_eval ="\$zone_saisie['sql'] =\"$temp_sql\";";
												}else{	
													$chaine_eval ="\$zone_saisie['sql'] =\"$temp_sql\";";
												}
												//Fin Ajout HEBIE
												eval ($chaine_eval);				
											}else{
												$zone_saisie['sql'] 	= '';
											}
                                            // Rangement des propriétés requises sur la zone                                            
											$tableau_zone_saisie[]		=	$zone_saisie;
											$id_zone++;
											//$pass=false;
										}
										//echo "<br>".$tableau_zone_saisie[$id_zone - 1]['sql'];				
										// constitution des champs é extraire dans la requete, les types label sont exclus
										if($tableau_zone_saisie[$id_zone-1]['type'] <> 'l'){
											$i_col_data++;
											if ($cptr==0){
												$sql_donnees .=  $nomtableliee.'.'.$rs->fields['CHAMP_PERE']. ' AS '. $rs->fields['CHAMP_PERE'];			
											}else{
												$sql_donnees .=  ','.$nomtableliee.'.'.$rs->fields['CHAMP_PERE']. ' AS '. $rs->fields['CHAMP_PERE'];
											}
											$cptr++;
											
											$this->champs[$nomtableliee][$i_col_data]	=	$rs->fields['CHAMP_PERE'];
											$this->col_zone_Equi_col_data[$nomtableliee]['zone'][$i_col_zone] = $i_col_data ;
											$this->col_zone_Equi_col_data[$nomtableliee]['data'][$i_col_data] = $i_col_zone ;
										}		
										
										// Récupération des jointures définies autour de la zone courante
                                        $sql_join	=' SELECT  C.ID_ZONE, C.N_JOINTURE, B.CHAMP_PERE '.
													 ' FROM DICO_ZONE_JOINTURE AS A,DICO_ZONE AS B, '. 
													 ' DICO_ZONE_JOINTURE AS C '. 
													 ' WHERE A.ID_ZONE='.$rs->fields['ID_ZONE'].
													 ' AND A.N_JOINTURE=C.N_JOINTURE '. 
													 ' AND A.ID_THEME='.$id_theme.
													 ' AND C.ID_THEME='.$id_theme.
													 ' AND C.ID_ZONE<>'.$rs->fields['ID_ZONE'].
													 ' AND C.ID_ZONE=B.ID_ZONE '.
													 ' AND B.CHAMP_PERE IS NOT NULL'; 
										
										//echo "<br> sql_join = $sql_join";
										// Traitement Erreur Cas : Execute / GetOne
										try {            
												$rs_join	=	$GLOBALS['conn_dico']->Execute($sql_join);
												/*if(count($GLOBALS['conn_dico']->GetAll($sql_join))){
													echo '<pre>';
													print_r($GLOBALS['conn_dico']->GetAll($sql_join));
												}*/
												if($rs_join ===false){                
													throw new Exception('ERR_SQL');   
												}
												$temp	= array();
												while(!$rs_join->EOF){						
													// Inutile de mettre les champs suivants dans les jointures 
                                                    // sachant qu'ils font partie des critéres SQL_REQ
                                                    if( (trim($rs_join->fields['CHAMP_PERE']) <> trim($GLOBALS['PARAM']['CODE_ETABLISSEMENT'])) and
                                                        (trim($rs_join->fields['CHAMP_PERE']) <> trim($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'])) and
														(trim($rs_join->fields['CHAMP_PERE']) <> trim($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'])) and
                                                        (trim($rs_join->fields['CHAMP_PERE']) <> trim($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']))
                                                    ){
                                                        $temp	= array($rs_join->fields['N_JOINTURE'], $rs_join->fields['CHAMP_PERE'], $nomtableliee);
													    $pile_jointure[$nomtableliee]['champs_jointures'][]	=	$temp;
                                                    }
													$this->are_joined_fields[$nomtableliee][$rs->fields['ID_ZONE']][$rs_join->fields['ID_ZONE']] = true ;
													
													$rs_join->MoveNext();
												}
										}
										catch (Exception $e) {
												 $erreur = new erreur_manager($e,$sql_join);
										}        
										// Fin Traitement Erreur Cas : Execute / GetOne										
										$rs->MoveNext();			
									}																	 
							}
							catch (Exception $e) {
									 $erreur = new erreur_manager($e,$sqlzone_);
							}        
							// Fin Traitement Erreur Cas : Execute / GetOne			
							$pile_jointure[$nomtableliee]['champs_select']	=	$sql_donnees;
							
							// Rangement des zones par TABLE_MERE dans la variable dico	
							$dico['tableau_zone_saisie'][$nomtableliee]	=	$tableau_zone_saisie;
							//echo'tableau_zone_saisie<pre>';
							//print_r($dico['tableau_zone_saisie']);
                            // Rangement des types des zones par TABLE_MERE dans la variable dico	
							$dico['tableau_type_zone_base'][$nomtableliee] = $tableau_type_zone_base;
							//Rangement dans la variable dico des différentes TABLE_MERES obtenues 
                            $dico['nomtableliee'][]			=	$nomtableliee;
                            // Rangement des nombres de clés de chq TABLE_MERE dans la variable dico	
							$dico['nb_cles'][$nomtableliee]	=	$nb_cles;
							// Rangement des valeurs associées é des clés de chq TABLE_MERE dans la variable dico	
							$dico['val_cle'][$nomtableliee]	=	$val_cle;
									
							// Traitement de la clause order by	 de chaque TABLE_MERE
							
							if ($tab_order_by[$nomtableliee]){
								$sql_order_by[$nomtableliee].=' ORDER BY ';
								ksort($tab_order_by[$nomtableliee]);
								$i=0;
								foreach ($tab_order_by[$nomtableliee] as $champ_tri){
									if ($i==0){
										$sql_order_by[$nomtableliee].= ' '.$nomtableliee.'.'.$champ_tri;
									}else{
										$sql_order_by[$nomtableliee].= ' , '.$nomtableliee.'.'.$champ_tri;
									}
									$i++;
								}
							}
							//Fin du traitement de la clause order by
							
							$rstableliee->MoveNext();
							
						}// FIN POUR CHQ TABLE MERE
		}
		catch (Exception $e) {
				 $erreur = new erreur_manager($e,$sqltableliee);
		} 		

		$union_sql_main_tab = array();
		$first_tab_second = true ;
		if(is_array($pile_table_liee))
		foreach ($pile_table_liee as $table_liee){
			if( ($table_liee <> $this->main_table_mere) or (count($pile_table_liee) == 1)  ){ 
				$tab_m_joins = array( $this->main_table_mere, $table_liee );	
				//$j	=	0;
                $clause_joins = '';
                //echo" --- champs_jointures : $table_liee --- <pre>";
                //print_r($pile_jointure[$table_liee]['champs_jointures']);
				if (is_array($pile_jointure[$table_liee]['champs_jointures'])){
					foreach ($pile_jointure[$table_liee]['champs_jointures'] as $jointure){		
							
                        $nom_tableau_assciees = trouver_table_associee($jointure[0],$tab_m_joins,$table_liee,$pile_jointure);
                        if(is_array($nom_tableau_assciees) and count($nom_tableau_assciees)){
                            foreach ($nom_tableau_assciees as $nom_tableau_assciee){
                                $clause_joins.= ' AND '.$table_liee.'.'.$jointure[1].'='.$nom_tableau_assciee[1].'.'.$nom_tableau_assciee[0];
                                if( trim($nom_tableau_assciee[1]) == ($this->main_table_mere) ){
                                    $this->tab_joins_in_main_tab[$table_liee][$jointure[1]] = $nom_tableau_assciee[0];
                                }
                            }	
                        }                            
					}
				}
                // Création de requéte d'une table secondaire en jointure avec la table principale
				$sql_donnees   = $pile_jointure[$table_liee]['champs_select'];
				//$sql_donnees.=	$sql_from.' WHERE ';
                if(isset($this->tab_joins_in_main_tab[$table_liee])){
				    $sql_donnees.=	' FROM ' . $this->main_table_mere . ' , ' . $table_liee ;
                    $criteres_in_main_tab = $tab_criteres[$this->main_table_mere];
                }else{
                    $criteres_in_main_tab = '';
                    $sql_donnees.=	' FROM ' . $table_liee ;
                }

				if(!$tab_criteres[$table_liee]){
					if (preg_match ("/$table_liee\.[^=]*=([^.]*)\./", $sql_donnees, $elem)){
						$tab_criteres[$table_liee] = $tab_criteres[$elem[1]];
					}
				}
				
                if(trim($clause_joins . $tab_criteres[$table_liee] . $criteres_in_main_tab)<>'') {
                    $sql_donnees .= ' WHERE '.$clause_joins . $tab_criteres[$table_liee] . $criteres_in_main_tab ;
                    //echo " <br> *** $table_liee *** <br> $sql_donnees <br> <br> " ;
                    $sql_donnees  = preg_replace('/WHERE[[:space:]]*AND/', 'WHERE', $sql_donnees); 
                    //echo " <br>str_replace *** $table_liee *** <br> $sql_donnees <br> <br> " ;
                }
				
				// Constitution de la requéte de la table principale
                if(isset($this->tab_joins_in_main_tab[$table_liee])){
                    $union_sql_main_tab[] =  str_replace($pile_jointure[$table_liee]['champs_select'], $pile_jointure[$this->main_table_mere]['champs_select'],$sql_donnees) ;
				}
				// Ajout de la condition order by	
				$sql_donnees.=$sql_order_by[$table_liee];
				//$sql_donnees.=$sql_order_by[$this->main_table_mere];
				// Rangement de la requéte de la table mére dans dico
				$dico['sql_data'][$table_liee] =$sql_donnees;
				$first_tab_second = false;
			}  // Fin Création requéte table secondaire      
		}
        $dico['sql_data'][$this->main_table_mere] = '';
        if(count($union_sql_main_tab) == 0){
		    $dico['sql_data'][$this->main_table_mere] .= $pile_jointure[$this->main_table_mere]['champs_select'] . ' FROM ' . $this->main_table_mere ;
            if(trim($tab_criteres[$this->main_table_mere])<>'') {
                $dico['sql_data'][$this->main_table_mere] .= ' WHERE ' . $tab_criteres[$this->main_table_mere];
                $dico['sql_data'][$this->main_table_mere]  = preg_replace('/WHERE[[:space:]]*AND/', 'WHERE', $dico['sql_data'][$this->main_table_mere]); 
            }
                
        }elseif(count($union_sql_main_tab) == 1){
		    $dico['sql_data'][$this->main_table_mere] .= $union_sql_main_tab[0];	
        }elseif(count($union_sql_main_tab) > 1){
            foreach($union_sql_main_tab as $i => $sql_join_tab_sec){
                ($i > 0) ? ($union = ' UNION ') : ($union = '') ;
		        $dico['sql_data'][$this->main_table_mere] .= $union . '(' . $sql_join_tab_sec . ')';	
            }
        }
        $dico['sql_data'][$this->main_table_mere] .= $sql_order_by[$this->main_table_mere];
		//echo'<pre><br>';
		//print_r($dico['sql_data']);
		//exit;
		 
		//////////////////////////////////// FIN POUR LA TEBLE LIEE LOCAL ///////////////////////
		// Identification du template et de la classe metier é utiliser
		//if (isset($dico['template'])){ $this->template 	= file_get_contents('./template/'.$dico['template']);}
		// Instanciation dun objet local
		if (!isset($dico['classe'])){
				$dico['classe']='grille';
		}
		$GLOBALS['dico']	=	$dico;
	}	
		
	
	/**
	 * 
	 * @access public
	 */	
	function __wakeup(){
		$this->conn	= $GLOBALS['conn'] ;
	}

	/**
	* METHODE :  is_tabm_matricielle($zones_tabm):
	* Permet é partir de la lise des zones d'une table mére ($zones_tabm)
	* de savoir si la table mére contient une structure matricielle ou non
	* @access public
	* @param array $zones_tabm : liste de zones  du théme
	* 
	*/
	function is_tabm_matricielle($zones_tabm){
		foreach($zones_tabm as $element){
			if( ($element['TYPE_OBJET'] == 'dimension_ligne') || ($element['TYPE_OBJET'] == 'dimension_colonne') ){
				return (true);
			}
		}
		if($this->type_theme==4 && $this->type_gril_eff_fix_col) return (true);
		return(false);
	}
	
	/**
	* METHODE :  set_common_tabm_fields_joined() :
	* Permet de renseigner la variable : $this->common_tabm_fields_joined
	*<pre>
	*	--> Pour chq clé de la table mére principale
	*		Si s'elle a une jointure dans toutes les tables secondaires
	*			--> alors elle est clé commune
	*</pre>
	* @access public
	* 
	*/
	function set_common_tabm_fields_joined(){
		$tab_key_fields = array();
		foreach($this->nomtableliee as $nomtableliee){
			foreach($this->tableau_zone_saisie[$nomtableliee] as $col_zone => $zone_saisie){
				if( 	($zone_saisie['type']=='c') 		or ($zone_saisie['type']=='csu') or ($zone_saisie['type']=='cmm') 
					or 	($zone_saisie['type']=='cmu') 		or ($zone_saisie['type']=='sco') or ($zone_saisie['type']=='sys_txt')
					or 	($zone_saisie['type']=='loc_etab')	or ($zone_saisie['type']=='li_ch')
				){
					$tab_key_fields[$nomtableliee][]	=	$col_zone ;										
				}
			}
		}
		//echo '<pre>tab_key_fields<br>';
		//print_r($tab_key_fields);
		//echo '<pre>$this->are_joined_fields<br>';
		//print_r($this->are_joined_fields);
		
		
		if( count($this->nomtableliee) == 1 ){
			$this->common_tabm_fields_joined = $tab_key_fields ;
		}else{
			$i_common_join = -1 ;
			if(is_array($tab_key_fields[$this->main_table_mere]))
			foreach($tab_key_fields[$this->main_table_mere] as $i_main => $col_zone_main ){
				$is_key_common_join		= 	true ; 
				$tab_fields_joined_sec	=	array();
				$id_zone_main = $this->tableau_zone_saisie[$this->main_table_mere][$col_zone_main]['id_zone'];
				foreach($this->nomtableliee as $nomtableliee){
					if( $nomtableliee <> $this->main_table_mere){
						foreach($tab_key_fields[$nomtableliee] as $i_sec => $col_zone_sec){
							$join_not_found = true ;
							$id_zone_sec = $this->tableau_zone_saisie[$nomtableliee][$col_zone_sec]['id_zone'];
							//echo '<pre>'.$id_zone_main.'/'.$id_zone_sec.'<br>';
							//print_r($this->are_joined_fields[$this->main_table_mere]);
							if( isset($this->are_joined_fields[$this->main_table_mere][$id_zone_main][$id_zone_sec]) or isset($this->are_joined_fields[$this->main_table_mere][$id_zone_sec][$id_zone_main])  ){
								$join_not_found = false ; 
								$tab_fields_joined_sec[$nomtableliee] = $col_zone_sec ;
								//echo'<br>join:'.$this->tableau_zone_saisie[$nomtableliee][$col_zone_sec]['champ'].'/'.$this->tableau_zone_saisie[$this->main_table_mere][$col_zone_main]['champ'];
								break;
							}
						}
						if($join_not_found == true){
							$is_key_common_join = false ; 
							break ;
						}
					}
				}
				if($is_key_common_join == true){
					$i_common_join++ ;
					$this->common_tabm_fields_joined[$this->main_table_mere][$i_common_join] = $col_zone_main ;
					foreach($tab_fields_joined_sec as $nomtableliee => $col_zone_sec_join){
						$this->common_tabm_fields_joined[$nomtableliee][$i_common_join] = $col_zone_sec_join ;
					}
				}
			}
		}
		
		if( count($this->common_tabm_fields_joined) == 0 ){
			if( count($this->nomtableliee) == 1 ){
				//echo '<div align="center">Absence de <span style="color: #FF0000">Champs de type Clé </span> dans ce théme . Contacter l\'Administrateur ... </div>';
			}else{
				//echo '<div align="center">Absence de <span style="color: #FF0000">Jointures Commumes</span> é toutes les tables de données de ce théme . Contacter l\'Administrateur ... </div>';
			}
		}
		//echo '<pre>common_tabm_fields_joined<br>';
		 //print_r($this->common_tabm_fields_joined);
	}

	/**
	* METHODE :  get_plage_codes():
	* Permet de renseigner  la variable $this->tab_plage, contenant 
    * les intervalles de valeurs définis pour un champ donné
	*
	*<pre>
	*	--> Lecture de la table : DICO_PLAGE_CODES
	*		Mise en tableau du contenu dans la variable : $this->tab_plage
	*</pre>
	*
	* @access public
	* 
	*/
    function get_plage_codes(){
		$this->tab_plage = array();
		if (in_array('DICO_PLAGE_CODES',array_map('strtoupper',$GLOBALS['conn_dico']->MetaTables('TABLES')))){
			$ColTab	=	$GLOBALS['conn_dico']->MetaColumnNames('DICO_PLAGE_CODES');  
			$cles=array_keys($ColTab); 
			if(count($cles)==4){
				$requete 	= 'SELECT * FROM DICO_PLAGE_CODES';
				$all_res	= $GLOBALS['conn_dico']->GetAll($requete);
				if(is_array($all_res) && count($all_res)>0){
					foreach ($all_res as $i => $plg){
							$this->tab_plage[$plg['NOM_CHAMP']] = array( 'val_min' => $plg['VAL_MIN'] , 'val_max' => $plg['VAL_MAX'] );
					}
				}
			}elseif(count($cles)>4){
				//Ajout hebie pour gestion plage automatique
				$requete        = 'SELECT CODE_TYPE_CHAINE FROM DICO_CHAINE_LOCALISATION
									 WHERE CODE_TYPE_SYSTEME_ENSEIGNEMENT='.$_SESSION['secteur'].' 
									 ORDER BY ORDRE_TYPE_CHAINE';
				$tab_chaines  = $GLOBALS['conn_dico']->GetAll($requete);
				$req_type_regroup_plage = "SELECT DISTINCT CODE_TYPE_REGROUP FROM DICO_PLAGE_CODES";
				$res_type_regroup_plage = $GLOBALS['conn_dico']->GetAll($req_type_regroup_plage);
				$tab_type_regroup_plage = array();
				$tab_non_type_regroup_plage = array();
				if(is_array($res_type_regroup_plage)){
					foreach($res_type_regroup_plage as $type_regroup_plage){
						($type_regroup_plage['CODE_TYPE_REGROUP']<>0)? ($tab_type_regroup_plage[] = $type_regroup_plage['CODE_TYPE_REGROUP']):($tab_non_type_regroup_plage[] = $type_regroup_plage['CODE_TYPE_REGROUP']);
					}
				}
				if(count($tab_type_regroup_plage)>0){
					if(isset($_POST[$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].'_0']) && $_POST[$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].'_0']<>''){
						foreach($tab_chaines as $iLoc => $chaine){
							if(isset($_POST['LOC_REG_'.$iLoc]) && $_POST['LOC_REG_'.$iLoc]<>''){ 
								//on recupère tous les regroupements ($rattachements) et leurs types ($types_regroup) à partir du regroupement selectionné								
								unset($_SESSION['reg_parents']);
								$chaine2 = $this->get_chaine_reg($_POST['LOC_REG_'.$iLoc]);
								$tab_chaines2 = $this->get_chaines_loc($_SESSION['secteur']);
								$idChaineLoc = $this->get_iLoc_chaine($chaine2,$tab_chaines2);
								require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php'; 
								$arbre 	= new arbre($chaine2);
								$rattachements = $arbre->getparentsid( $tab_chaines2[$idChaineLoc]['nbRegs']-1 , $_POST['LOC_REG_'.$iLoc], $chaine2);
								$types_regroup = array();
								foreach ($rattachements as $rattachement){
									$currReg = $rattachement[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
									$req_type_regroup = "SELECT ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']." FROM ".$GLOBALS['PARAM']['REGROUPEMENT'].
													" WHERE ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']." = ".$currReg;
									$types_regroup[] = $GLOBALS['conn']->GetOne($req_type_regroup);
								}								
								$trouve = false;
								foreach ($types_regroup as $key => $type_regroup){ 
									$codeRegAdmin .= $rattachements[$key][$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
									//pour chaque type de regroupement on verifie s'il n'y a rien de défini comme plage dessus
									if(in_array($type_regroup,$tab_type_regroup_plage)){
										$req_plage 	= 'SELECT * FROM DICO_PLAGE_CODES WHERE CODE_TYPE_REGROUP = '.$type_regroup;
										$res_plage	= $GLOBALS['conn_dico']->GetAll($req_plage);
										if(is_array($res_plage)){
											foreach ($res_plage as $i => $plg){
												$req_plage_code_add 	= "SELECT NOM_CHAMP_ASSOC FROM DICO_PLAGE_CODES_ASSOC WHERE CODE_TYPE_REGROUP = ".$type_regroup." AND NOM_CHAMP = '".$plg['NOM_CHAMP']."' ORDER BY ORDRE_CHAMP_ASSOC";
												$res_plage_code_add	= $GLOBALS['conn_dico']->GetAll($req_plage_code_add);
												$champs_rattach_add = "";
												if(is_array($res_plage_code_add)){
													$utiliser_code_annee = 0;
													foreach ($res_plage_code_add as $j => $plg_chp_add){
														if($plg_chp_add['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
															$champs_rattach_add .= $this->code_etablissement;
														}elseif($plg_chp_add['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){
															if ($plg['NOM_CHAMP']==$GLOBALS['PARAM']['CODE_ADMINISTRATIF']) {
																$req_cadmin_systeme = 'SELECT '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'_Y_N FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$this->id_systeme;
																$cadmin_systeme = $GLOBALS['conn']->GetRow($req_cadmin_systeme);
																if (is_array($cadmin_systeme)) {
																	$champs_rattach_add .= $cadmin_systeme[$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
																	$utiliser_code_annee = $cadmin_systeme[$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'_Y_N'];
																}
															} else {
																$champs_rattach_add .= $this->id_systeme;
															}															
														}elseif(isset($_POST[$plg_chp_add['NOM_CHAMP_ASSOC'].'_0']) && $_POST[$plg_chp_add['NOM_CHAMP_ASSOC'].'_0']<>''){
															$tab_chp_val = explode('_', $_POST[$plg_chp_add['NOM_CHAMP_ASSOC'].'_0']);
															if($plg_chp_add['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'] && $plg['NOM_CHAMP']==$GLOBALS['PARAM']['CODE_ADMINISTRATIF']){
																$req_cadmin_statut_etab = 'SELECT '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'].' FROM '.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'].' = '.$tab_chp_val[count($tab_chp_val)-1];
																$cadmin_statut_etab = $GLOBALS['conn']->GetOne($req_cadmin_statut_etab);
																$champs_rattach_add .= $cadmin_statut_etab;															
															}else{
																$champs_rattach_add .= $tab_chp_val[count($tab_chp_val)-1];
															}
														}
													}
												}
												// on utlilse le code du regroupement dont le type est en cours de traitement ($rattachements[$key][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']])
												if ($plg['NOM_CHAMP']==$GLOBALS['PARAM']['CODE_ADMINISTRATIF']) {
													if ($utiliser_code_annee == 1) { //on rajoute l'année si elle est prevue pour ce sous secteur.
														$req_cadmin_annee = 'SELECT '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'];
														$cadmin_annee = $GLOBALS['conn']->GetOne($req_cadmin_annee);
														$champs_rattach_add .= $cadmin_annee;
													}
													$codeRegCourant = $codeRegAdmin;
												}else{
													$codeRegCourant = $rattachements[$key][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
												}												
												$this->tab_plage[$plg['NOM_CHAMP']] = array( 'val_min' => $codeRegCourant.$champs_rattach_add.$plg['VAL_MIN'] , 'val_max' => $codeRegCourant.$champs_rattach_add.$plg['VAL_MAX'], 'plage_min'  => $plg['VAL_MIN'], 'plage_max'  => $plg['VAL_MAX']);
											}
										}
										// le type de regroupement courant est présent dans la chaine. 
										$trouve = true;
									}
								}
								if ($trouve) break; //Pas besoin de chercher dans une autre chaine
							}
						}
					}else{
						foreach($_SESSION['rattachmts'] as $code_chaine => $chaine_loc){
							$niv_chaine = count($chaine_loc);
							if(isset($chaine_loc[$niv_chaine-1]) && $chaine_loc[$niv_chaine-1]<>''){
								$req_type_regroup = "SELECT ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']." FROM ".$GLOBALS['PARAM']['REGROUPEMENT'].
													" WHERE ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']." = ".$chaine_loc[$niv_chaine-1];
								$type_regroup = $GLOBALS['conn']->GetOne($req_type_regroup);
								if(in_array($type_regroup,$tab_type_regroup_plage)){
									$req_plage 	= 'SELECT * FROM DICO_PLAGE_CODES WHERE CODE_TYPE_REGROUP = '.$type_regroup;
									$res_plage	= $GLOBALS['conn_dico']->GetAll($req_plage);
									if(is_array($res_plage)){
										foreach ($res_plage as $i => $plg){
											$req_plage_code_add 	= "SELECT NOM_CHAMP_ASSOC FROM DICO_PLAGE_CODES_ASSOC WHERE CODE_TYPE_REGROUP = ".$type_regroup." AND NOM_CHAMP = '".$plg['NOM_CHAMP']."' ORDER BY ORDRE_CHAMP_ASSOC";
											$res_plage_code_add	= $GLOBALS['conn_dico']->GetAll($req_plage_code_add);
											$champs_rattach_add = "";
											if(is_array($res_plage_code_add)){
												foreach ($res_plage_code_add as $j => $plg_chp_add){
													if($plg_chp_add['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
														$champs_rattach_add .= $this->code_etablissement;
													}elseif($plg_chp_add['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){
														$champs_rattach_add .= $this->id_systeme;
													}elseif(isset($_SESSION['plage_codes_assoc'][$plg_chp_add['NOM_CHAMP_ASSOC']]) && $_SESSION['plage_codes_assoc'][$plg_chp_add['NOM_CHAMP_ASSOC']]<>''){
														$champs_rattach_add .= $_SESSION['plage_codes_assoc'][$plg_chp_add['NOM_CHAMP_ASSOC']];
													}
												}
											}
											$this->tab_plage[$plg['NOM_CHAMP']] = array( 'val_min' => $chaine_loc[$niv_chaine-1].$champs_rattach_add.$plg['VAL_MIN'] , 'val_max' => $chaine_loc[$niv_chaine-1].$champs_rattach_add.$plg['VAL_MAX'] );
										}
									}
									break;
								}
							}
						}
					}
				}
				if(count($tab_non_type_regroup_plage)>0){
					$req_plage 	= 'SELECT * FROM DICO_PLAGE_CODES WHERE CODE_TYPE_REGROUP = 0';
					$res_plage	= $GLOBALS['conn_dico']->GetAll($req_plage);
					if(is_array($res_plage)){
						foreach ($res_plage as $i => $plg){
							$req_plage_code_add 	= "SELECT NOM_CHAMP_ASSOC FROM DICO_PLAGE_CODES_ASSOC WHERE CODE_TYPE_REGROUP = 0 AND NOM_CHAMP = '".$plg['NOM_CHAMP']."' ORDER BY ORDRE_CHAMP_ASSOC";
							$res_plage_code_add	= $GLOBALS['conn_dico']->GetAll($req_plage_code_add);
							$champs_rattach_add = "";
							if(is_array($res_plage_code_add)){
								foreach ($res_plage_code_add as $j => $plg_chp_add){
									if($plg_chp_add['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
										$champs_rattach_add .= $this->code_etablissement;
									}elseif($plg_chp_add['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){
										$champs_rattach_add .= $this->id_systeme;
									}elseif(isset($_SESSION['plage_codes_assoc'][$plg_chp_add['NOM_CHAMP_ASSOC']]) && $_SESSION['plage_codes_assoc'][$plg_chp_add['NOM_CHAMP_ASSOC']]<>''){
										$champs_rattach_add .= $_SESSION['plage_codes_assoc'][$plg_chp_add['NOM_CHAMP_ASSOC']];
									}
								}
							}
							$this->tab_plage[$plg['NOM_CHAMP']] = array( 'val_min' => $champs_rattach_add.$plg['VAL_MIN'] , 'val_max' => $champs_rattach_add.$plg['VAL_MAX'] );
						}
					}
				}
				//Fin Ajout hebie pour gestion plage automatique
			}
		}
		
	}


	/**
	* METHODE :  parse_in_plage(&$code , $champ):
	* Permet de valider un code  $code dans $this->tab_plage[$champ] .
	* Procédure de contréle d'une valeur attibuée dans une Plage définie
	*
	*<pre>
	*	--> Si $code n'est pas dans le tableau des Plages de $champ
	*		-	erreur sera signalée 
	*</pre>
	*
	* @access public
	* 
	*/
	function parse_in_plage(&$code , $champ){
		if (isset($this->tab_plage[$champ])){
			if( $code > 0 ){
					if($code < $this->tab_plage[$champ]['val_min']){
							$code = $this->tab_plage[$champ]['val_min'] ;
					}elseif($code > $this->tab_plage[$champ]['val_max']){
							$code = -100 ;
					}
			}else{
					echo "<script type='text/Javascript'>\n";
					echo "$.unblockUI();\n";
					echo "</script>\n";
					die($this->tab_plage[$champ]['val_min']);
					$code = -100 ;
			}
		}
	}

	/**
	* Methode set_code_nomenclature():
	* Permet de renseigner l'attribut : $this->code_nomenclature
	* <pre>
	* 	--> Pour chq  de zone liée é une nomenclature
	*		-	Récupérer les codes de nomenclature associés 
	*			dans $this->code_nomenclature
	* </pre>
	* @access public	 
	*/ 
	function set_code_nomenclature(){

		foreach($this->nomtableliee as $nomtableliee){
			foreach($this->tableau_zone_saisie[$nomtableliee] as $col => $zone_saisie){
				if( ($zone_saisie['type']=='m') or ($zone_saisie['type']=='cmu') or ($zone_saisie['type']=='sco') 
						or ($zone_saisie['type']=='li_ch') or ($zone_saisie['type']=='co')
						or ($zone_saisie['type']=='dim_lig') or ($zone_saisie['type']=='dim_col') 
						or (($zone_saisie['type']=='zone_mat') && ($zone_saisie['ss_type'] <> 'b')) ){
					
					if (!is_null($zone_saisie['sql']) and (trim($zone_saisie['sql']) <> '') ){
					//echo $zone_saisie['sql'].'<br>';
					//echo 'long='.strlen($zone_saisie['sql']).'<br>';
					//echo '<pre>';
					//print_r($zone_saisie);
						// Traitement Erreur Cas Execute
						if( isset($zone_saisie['DynContentObj']) && ($zone_saisie['DynContentObj']== 1) ){
							if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
								$zone_saisie['sql'] = preg_replace('/'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'[[:space:]]*=/', $GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' IN ', $zone_saisie['sql']);
							}
							try { 
								$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_NUM;           
								$rs			= 	$this->conn->Execute($zone_saisie['sql']); 
								if($rs ===false){                
										 throw new Exception('ERR_SQL');   
								} 
								$code		=		array();
                        
								while (!$rs->EOF) {                        
									//$code[] = $rs->fields[$zone_saisie['champ']];
                                    //echo " valeur =" . $this->get_champ_extract($zone_saisie['champ']). " <br>";
									if (preg_match ("/^(0|([1-9][0-9]*))$/", $rs->fields[0])){
										$code[] = $rs->fields[0];
									}
									$rs->MoveNext();							
								}
								$key_nomenc = $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
								$this->code_nomenclature[$nomtableliee][$key_nomenc]	=	$code;
								$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
							}
							catch (Exception $e) {
									 $erreur = new erreur_manager($e,$zone_saisie['sql']);
							} 
							 
						}else{
							try {            
								$rs			= 	$this->conn->Execute($zone_saisie['sql']); 
								if($rs ===false){                
										 throw new Exception('ERR_SQL');   
								} 
								$code		=		array();
                        
								while (!$rs->EOF) {                        
									//$code[] = $rs->fields[$zone_saisie['champ']];
                                    //echo " valeur =" . $this->get_champ_extract($zone_saisie['champ']). " <br>";
									$code[] = $rs->fields[$this->get_champ_extract($zone_saisie['champ'])];
									$rs->MoveNext();							
								}
								$key_nomenc = $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
								$this->code_nomenclature[$nomtableliee][$key_nomenc]	=	$code;
							}
							catch (Exception $e) {
									 $erreur = new erreur_manager($e,$zone_saisie['sql']);
							}  
						}      
						// Fin Traitement Erreur Cas Execute
					}
				}
				elseif( ($zone_saisie['type']=='e') or ($zone_saisie['type']=='tvm')  ){
										
					if (!is_null($zone_saisie['sql']) and (trim($zone_saisie['sql']) <> '') ){
						// Traitement Erreur Cas Execute
						try {
						    $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_NUM;        
								$rs			= $this->conn->Execute($zone_saisie['sql']);
								if($rs ===false){                
										 throw new Exception('ERR_SQL');   
								} 
								$code	=	array();
								while (!$rs->EOF) {
									$code[] = $rs->fields[0];
									$rs->MoveNext();							
								}
								$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
								$key_nomenc = $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
								$this->code_nomenclature[$nomtableliee][$key_nomenc]	=	$code;
						}
						catch (Exception $e) {
								 $erreur = new erreur_manager($e,$zone_saisie['sql']);
						}        
						// Fin Traitement Erreur Cas Execute						
					}
				}
				elseif( ($zone_saisie['type']=='b') or ( ($zone_saisie['type']=='zone_mat') && ($zone_saisie['ss_type']=='b') ) ){
					///////////////////
					//if(!isset($zone_saisie['table_ref'])){
							//$table_ref = 'DICO_BOOLEEN';
							$this->tableau_zone_saisie[$nomtableliee][$col]['table_ref']	=	'DICO_BOOLEEN';
					//}
						
				//	if( !($sql = $zone_saisie['sql']) ){
							$sql = 'SELECT  CODE_DICO_BOOLEEN FROM  DICO_BOOLEEN ORDER BY ORDRE_DICO_BOOLEEN';
							//$champ_extract = 'CODE_DICO_BOOLEEN';
							$this->tableau_zone_saisie[$nomtableliee][$col]['sql']	=	$sql;
							//$zone_saisie['sql'] = $sql;
				//	}
					//else
							//$champ_extract = $zone_saisie['champ'];
					
					//echo"<br>$champ_extract";
					$champ_extract = 'CODE_DICO_BOOLEEN';
					
					// Traitement Erreur Cas Execute
					try {            
							$rs = $GLOBALS['conn_dico']->Execute($sql);
							if($rs === false){                
									 throw new Exception('ERR_SQL');   
							}
							$code	=	array();
							while (!$rs->EOF){
									$code[] = $rs->fields[$champ_extract];
									$rs->MoveNext();							
							}
							$key_nomenc = $zone_saisie['id_zone'] . '_DICO_BOOLEEN' ;
							$this->code_nomenclature[$nomtableliee][$key_nomenc]	=	$code;
					}
					catch (Exception $e) {
							 $erreur = new erreur_manager($e,$sql);
					}        
					// Fin Traitement Erreur Cas Execute
										
					//echo'<br><br><pre>';
					//print_r($code);	
					//////////////////////
				}
			}
		}
		//echo'code_nomenclature <pre>';
		//print_r($this->code_nomenclature);
	}
		
	
	/**
	* METHODE :  get_donnees_bdd ( )
	* Permet de récupérer les données sous forme de n-uplet basé  
	* sur l'ensemble des champs de l'attribut $this->champs
	*
	*<pre>
	*
	*	-->	Pour chq table mére 
	*		-	Récupérer les données en Base
	*		-	Limiter les données obtenues en foncion
	*			des contraintes d'affichage du théme
	*</pre>
	* @access public
	* 
	*/
	function get_donnees_bdd(){
		foreach($this->nomtableliee as $nomtableliee){
		 	if( !isset($this->VARS_GLOBALS['donnees_bdd'][$nomtableliee]) ){
                // variable qui va contenir toutes les données de la table : $nomtableliee
				$this->VARS_GLOBALS['donnees_bdd'][$nomtableliee] = array();
				if(isset($this->code_etablissement)){
						$sql = $this->sql_data[$nomtableliee];
                        //echo  $nomtableliee.'<br>'.$sql .'<br><br>';
						$tab_donnees_bdd = array();
                        //echo "<br> *** $nomtableliee *** <br>".$sql.'<br><br>';
                        // === SESSION 28 DIAG : log sql_data + nb lignes ===
                        $diag_type_ent = isset($_SESSION['type_ent_stat']) ? $_SESSION['type_ent_stat'] : 'UNDEF';
                        $diag_theme = isset($this->id_theme) ? $this->id_theme : '?';
                        $diag_syst  = isset($this->id_systeme) ? $this->id_systeme : '?';
                        $_diag_line_gdb = date('Y-m-d H:i:s').';get_donnees_bdd;theme='.$diag_theme.';systeme='.$diag_syst.
                            ';type_ent_stat='.$diag_type_ent.';table='.$nomtableliee.';sql='.str_replace("\n",' ',$sql)."\n";
                        error_log('[DIAG_S28_GDB] '.$_diag_line_gdb);
                        @file_put_contents(dirname(__FILE__).'/../../../../moblogs/diag_grille.log', $_diag_line_gdb, FILE_APPEND);
                        @file_put_contents(sys_get_temp_dir().'/diag_stateduc.log', $_diag_line_gdb, FILE_APPEND);
                        // === FIN SESSION 28 DIAG ===
						try{
								$recordSet	=	$this->conn->Execute($sql);
								
								if($recordSet===false){
										throw new Exception('ERR_SQL');
								}
								$i=0;
								while (!$recordSet->EOF){
										$ligne = $recordSet->fields;
										$j=0;
										foreach ($ligne as $j_col => $col){
                                        
                                            if (strpos($col,"00:00:00")===false){
											    $tab_donnees_bdd[$i][$j]=$col;
                                            }else{
                                                $tab_donnees_bdd[$i][$j]= adodb_date2('d/m/y',$col);                                                
                                            }
											//echo"<br>-->".$this->champs[$nomtableliee][$j]."=".$col;
											$fld = $recordSet->FetchField($j);
											$fld_name = $fld->name;
											if( trim($col) == '255' &&  trim($this->val_cle[$nomtableliee][$fld_name]) == '' ){
												//die($fld_name);
												if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$fld_name)){
													$tab_donnees_bdd[$i][$j] = '';
												}else{
													//$col_zone 	= $this->col_zone_Equi_col_data[$nomtableliee]['data'][$j_col];
													$col_zone 	= $this->col_zone_Equi_col_data[$nomtableliee]['data'][$j];//Modif HEBIE
													$table_ref 	= $this->tableau_zone_saisie[$nomtableliee][$col_zone]['table_ref'] ;
													if( (trim($table_ref) <> '') && (preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table_ref))) ){
														$tab_donnees_bdd[$i][$j] = '';
													}
												}
											}
											$j++;
										}
										$i++;			
										$recordSet->MoveNext();
								}
						}catch( Exception $e){
								$err = new erreur_manager($e, $sql);
                        // === SESSION 28 DIAG : erreur SQL ===
                        $_diag_line_err = date('Y-m-d H:i:s').';SQL_ERROR;theme='.$diag_theme.';table='.$nomtableliee.';msg='.$e->getMessage()."\n";
                        error_log('[DIAG_S28_SQLERR] '.$_diag_line_err);
                        @file_put_contents(dirname(__FILE__).'/../../../../moblogs/diag_grille.log', $_diag_line_err, FILE_APPEND);
                        @file_put_contents(sys_get_temp_dir().'/diag_stateduc.log', $_diag_line_err, FILE_APPEND);
						}
                        // === SESSION 28 DIAG : nb rows retournees ===
                        $_diag_line_rows = date('Y-m-d H:i:s').';get_donnees_bdd_result;theme='.$diag_theme.';table='.$nomtableliee.';nb_rows='.count($tab_donnees_bdd)."\n";
                        error_log('[DIAG_S28_ROWS] '.$_diag_line_rows);
                        @file_put_contents(dirname(__FILE__).'/../../../../moblogs/diag_grille.log', $_diag_line_rows, FILE_APPEND);
                        @file_put_contents(sys_get_temp_dir().'/diag_stateduc.log', $_diag_line_rows, FILE_APPEND);
                        // === FIN SESSION 28 DIAG ===
						//$this->matrice_donnees[$nomtableliee]	=	$tab_donnees_bdd;
						$this->VARS_GLOBALS['donnees_bdd'][$nomtableliee] = $tab_donnees_bdd;
					}
			}
			
			// Limitation des données par rapport é un début et au nombre de lignes dispo sur le template
            $this->limiter_affichage($nomtableliee);
		}
	
		/*echo'<div align=left>';
       echo'DONNEES BDD<pre>';
       print_r($this->VARS_GLOBALS['donnees_bdd']);
	  echo'</div>';*/
	}		
	
	
	/**
	* Méthode extraire_valeur_matrice permet l'extraction de la valeur 
	* dans les champs dont les contenus renvoyés sont sous la forme 
	* $[nom_champ]_[ligne]_[valeur]
	*
	* @access public
	* 
	*/
	function extraire_valeur_matrice($texte){
		// cette permet l'extraction de la valeur encodée dans le champ de type  matriciel 
		$return = $texte;
		if(preg_match('/_/',$texte)){
				$val = explode('_',$texte);
				$return = $val[count($val)-1];
		}
		return ($return);
	}

	/**
	* METHODE :  get_champ_extract($nom_champ):
	*
	* Retourne les 30 ou 31 premiers caractéres de $nom_champ en fonction du type de SGBD 
    * pour permttre d'extraire correctement la valeur du champ $nom_champ dans un recordset
	*
	* @access public
	* 
	*/
	function get_champ_extract($nom_champ){
        $champ_extract = $nom_champ;
        if (strlen($nom_champ)>30) {
            if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql') { 
                    $taille_max_extract=strlen($nom_champ);                
            }else if ($GLOBALS['conn']->databaseType == 'postgres9') {
					$taille_max_extract=strlen($nom_champ);
			}else{
                    $taille_max_extract=31;                
            }
            $champ_extract = substr($nom_champ, 0, $taille_max_extract); 
        }
        return($champ_extract);
	}
	
	function get_dims_zone_matricielle_2($type_zone){
		$requete	="		SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
							FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
							WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
							AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
							AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
							AND			DICO_ZONE.ID_THEME = ".$this->id_theme.
							" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$this->id_systeme.
							" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$this->id_systeme.
							" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
								AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
								ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
		try {
				$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
				if(!is_array($aresult)){                    
						throw new Exception('ERR_SQL');  
				} 
				$dico	= $aresult;									
		}
		catch(Exception $e){
				$erreur = new erreur_manager($e,$requete);
		}
		
		foreach($dico as $element){
			if( ($element['TYPE_OBJET'] == $type_zone) ){
				return($element);
			}
		}
	}
	
	function requete_nomenclature($table_nomenclature, $champ_fils, $champ_pere, $langue, $id_systeme, $sql){
		if( (!$champ_pere) or (trim($champ_pere) == '') ){
			$champ_pere =  $champ_fils ;
		}
		//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
		//Fin ajout Hebie
		$chaine_eval ="\$sql_eval =\"$sql\";";						
		eval ($chaine_eval);	
		try {            
			$all_res	= $this->conn->GetAll($sql_eval);
			if(!is_array($all_res)){              
				throw new Exception('ERR_SQL');   
			} 
			$code		=		array();
			foreach( $all_res as $i => $res) {
				$code[] = $res[$this->get_champ_extract($champ_pere)];
			}
			$req_nomenc_trad = 'SELECT DICO_TRADUCTION.LIBELLE, DICO_TRADUCTION.CODE_NOMENCLATURE AS '.$champ_pere.'
								FROM '.$table_nomenclature.', DICO_TRADUCTION
								WHERE DICO_TRADUCTION.CODE_NOMENCLATURE = '.$table_nomenclature.'.'.$champ_fils.'
								AND DICO_TRADUCTION.NOM_TABLE=\''.$table_nomenclature.'\' 
								AND DICO_TRADUCTION.CODE_LANGUE=\''.$langue.'\'
								AND DICO_TRADUCTION.CODE_NOMENCLATURE IN ('.implode(', ',$code).')
								ORDER BY '.$table_nomenclature.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$table_nomenclature;  
			try {
				$all_res	= $this->conn->GetAll($req_nomenc_trad);
				if(!is_array($all_res)){                    
					throw new Exception('ERR_SQL');  
				} 
				//echo '<br>code ='.$table_nomenclature.'<pre>';
				//print_r($all_res);
				//die;
				return $all_res;									
			}
			catch(Exception $e){
				$erreur = new erreur_manager($e,$req_nomenc_trad);
			}
		}
		catch (Exception $e) {
			$erreur = new erreur_manager($e,$sql_eval);
		}        
		// Fin Traitement Erreur Cas : GetAll / GetRow
	}
	
	
	function requete_interface($table_interface, $champ_interface, $champ_pere, $id_systeme, $sql, $code_annee='', $code_etablissement=''){
			
			//Ajout HEBIE pr gestion code_etablissement TMIS
			//echo $this->code_etablissement."<br>";
			$code_filtre = $this->code_filtre;
			//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']} = $code_annee;
			${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;			
			if(isset($_SESSION['code_regroupement']) && $_SESSION['code_regroupement'] <> ''){
				${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']} = $_SESSION['code_regroupement'];
			}
			//Fin ajout Hebie
			if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
				$sql = preg_replace('/'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'[[:space:]]*=/', $GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' IN ', $sql);
				$code_etablissement = '('.implode(', ',$_SESSION['liste_etab']).')';
				${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
				$chaine_eval ="\$sql_eval =\"$sql\";";
			}else{	
				$chaine_eval ="\$sql_eval =\"$sql\";";
			}
			//Fin Ajout HEBIE
			eval ($chaine_eval);
			try {            
				$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_NUM; 
				$all_res	= $this->conn->GetAll($sql_eval);
				if(!is_array($all_res)){              
					throw new Exception('ERR_SQL');   
				} 
				$tab_code_libelle	=	array();
				$code = array();
				$with_champ_libelle = 	false ;
				if(is_array($all_res)){
					if( is_array($all_res[0]) && (count($all_res[0]) > 1) ){
						$with_champ_libelle = true ;
					}
					foreach( $all_res as $i => $res) {
						if (preg_match ("/^(0|([1-9][0-9]*))$/", $res[0])){
							$tab_code_libelle[$i][$this->get_champ_extract($champ_pere)] = $res[0];
							$code[] = $res[0];
							if( $with_champ_libelle == true ){
								$tab_code_libelle[$i]['LIBELLE'] = $res[1] ;
							}else{
								$tab_code_libelle[$i]['LIBELLE'] = $res[0] ;
							}
						}
					}
				}
				$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
				if(count($tab_code_libelle) > 0){	
					//print_r($code);
					if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table_interface))){
						$req_nomenc_trad = 'SELECT  DICO_TRADUCTION.CODE_NOMENCLATURE AS '.$champ_pere.', DICO_TRADUCTION.LIBELLE
											FROM '.$table_interface.', DICO_TRADUCTION
											WHERE DICO_TRADUCTION.CODE_NOMENCLATURE = '.$table_interface.'.'.$GLOBALS['PARAM']['CODE'].'_'.$table_interface.'
											AND DICO_TRADUCTION.NOM_TABLE=\''.$table_interface.'\' 
											AND DICO_TRADUCTION.CODE_LANGUE=\''.$_SESSION['langue'].'\'
											AND DICO_TRADUCTION.CODE_NOMENCLATURE IN ('.implode(', ',$code).')';  
						try {
						
							$all_res	= $this->conn->GetAll($req_nomenc_trad);
							if(!is_array($all_res)){                    
								throw new Exception('ERR_SQL');  
							} 
							//echo '<br>code ='.$table_nomenclature.'<pre>';
							//print_r($all_res);
							$all_res_ord = array();
							foreach($code as $ord_code => $val){
								foreach( $all_res as $ord_res => $res ){
									if( trim($res[$this->get_champ_extract($champ_pere)]) == trim($val) ){
										$all_res_ord[] = $res ;
										break;
									}							
								}
							}
							return $all_res_ord;									
						}
						catch(Exception $e){
							$erreur = new erreur_manager($e,$req_nomenc_trad);
						}
					}else{
						return $tab_code_libelle;
					}
				}
			}
			catch (Exception $e) {
				$erreur = new erreur_manager($e,$sql_eval);
			}
		   
		 //Fin Traitement Erreur Cas : GetAll / GetRow
	}


	
	/**
	* METHODE :  get_lignes_tpl_saisies($matr) :
	*
	* Permet de savoir quelles sont les lignes saisies 
	* dans le template lors de la soumission .
	*
	* @access public
	* 
	*/
	function get_lignes_tpl_saisies($matr){
			$lignes_tpl_saisies = array();
			foreach($this->nomtableliee as $nomtableliee){
					for($ligne = 0; $ligne < $this->nb_lignes; $ligne++){
								///// on teste si la ligne est saisie
								$ligne_vide = true;
								$nb_colonnes = count($this->tableau_zone_saisie[$nomtableliee]);
								foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){
									$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
									if( $zone_saisie['type']=='e' or $zone_saisie['type']=='li_ch' or $zone_saisie['type']=='tvm' ){
										if(!is_array($nom_champ))
											$tab_nom_champ = array($nom_champ);
										else
											$tab_nom_champ = $nom_champ;
										
										$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
										
										if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]))						
										foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
											foreach($tab_nom_champ as $nom_champ){
												$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;                                
																																 
												if( isset($matr[$v]) and trim($matr[$v])<>'')
												{				
													$ligne_vide = false;
													$nb_ligne_post++;
													break;
												}
											}
											if($ligne_vide == false)
												break;
										}
										if($ligne_vide == false)
											break;
									}
									elseif( $zone_saisie['type']=='zone_mat'  ){
										if($this->type_theme==4 && $this->type_gril_eff_fix_col){
											$tab_dims = $this->get_dims_zone_matricielle($nomtableliee,'ligne');
										}else{
											$tab_dims = $this->get_dims_zone_matricielle($nomtableliee,'');
										}
										if( count($tab_dims )){
											$nom_champ		=	$zone_saisie['champ'];
											$tab_zones_dims = $tab_dims['zones_dims'];
											$tab_codes_dims = $this->do_concat_dimensions_codes($tab_dims['codes_dims']);
										}//Modif Hebié 14/05/2018 au Mali: Pour gerer le ctrl de saisie obligatoire sur la grille_col_1 fix_coll
											$mat_dim_col	=	$this->get_dims_zone_matricielle_2('dimension_colonne');
											if(is_array($mat_dim_col) && count($mat_dim_col)){
												$mat_nomenc_col	= $this->requete_nomenclature($mat_dim_col['TABLE_FILLE'], $mat_dim_col['CHAMP_FILS'], $mat_dim_col['CHAMP_PERE'], $_SESSION['langue'] ,$this->id_systeme, $mat_dim_col['SQL_REQ']) ;
											}
											if(is_array($tab_codes_dims) && count($tab_codes_dims)>0){						
												foreach($tab_codes_dims as $val_matrice){
													if($this->type_theme==4 && $this->type_gril_eff_fix_col){
														$v = $nom_champ.'_0_'.$val_matrice.'_'.$mat_nomenc_col[$ligne][$this->get_champ_extract($mat_dim_col['CHAMP_FILS'])] ;
													}else{
														$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;                                
													}
													if( isset($matr[$v]) and trim($matr[$v])<>'')
													{				
														$ligne_vide = false;
														$nb_ligne_post++;
														break;
													}
													if($ligne_vide == false)
														break;
												}
											}else{
												$v = $nom_champ.'_0_'.$mat_nomenc_col[$ligne][$this->get_champ_extract($mat_dim_col['CHAMP_FILS'])] ;
												if( isset($matr[$v]) and trim($matr[$v])<>'' ){				
													$ligne_vide = false;
													$nb_ligne_post++;
													break;
												}
											}
											if($ligne_vide == false)
												break;											
										//}//Modif Hebié 14/05/2018 au Mali: Pour gerer le ctrl de saisie obligatoire sur la grille_col_1 fix_coll
									}
									elseif($zone_saisie['type']=='loc_etab'){
												///  données é prendre de quelque part
												//$tab_chaines = array();
												//$tab_chaines[] = array( 'numCh' => 2, 'nbRegs' => 4 );
												///
												$tab_chaines = $this->get_chaines_loc($this->id_systeme);
												
												foreach($tab_chaines as $iLoc => $chaine){
														$v	=	'LOC_REG_'.$iLoc;
														if( isset($matr[$v]) and trim($matr[$v])<>''){
																$ligne_vide = false;
																//$nb_ligne_post++;
																break;
														}
												}
				

									}
									else{
										$v = $nom_champ.'_'.$ligne ;			
										if( isset($matr[$v]) and trim($matr[$v])<>'' ){				
											$ligne_vide = false;
											$nb_ligne_post++;
											break;
										}
									}
								}
								/////////////////                
                            if( ($ligne_vide) == false and (!isset($matr['DELETE_'.$ligne])) ){
                                    if(!isset($lignes_tpl_saisies[$ligne])){
                                            $lignes_tpl_saisies[$ligne] = $ligne;
                                    }
                            }
                    }
            }
			return($lignes_tpl_saisies);
	}
	
	
	/**
	* Méthode get_cle_max permet de retourner la plus grande valeur de la clé 
	* située é la position incrémentale: comme NUMERO_LOCAL, NUMERO_GP, autres
	*
	* @access public
	* 
	*/
	function get_cles_max(){
		$cle_max_return	= array();
		$cle_max_found	= array();
		$tab_return		= array();
		//echo '<pre><br>$common_tabm_fields_joined<br>';
		//print_r( $this->common_tabm_fields_joined);
		foreach( $this->common_tabm_fields_joined as $nomtableliee => $tab_join_zones ){
			$sql_clauses = '';
			foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){
				$champ_cle	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
				if( ($zone_saisie['type']=='c') && (isset($this->val_cle[$nomtableliee][$champ_cle])) ){
				//if( ($zone_saisie['type']=='c') && (isset($this->val_cle[$nomtableliee][$champ_cle])) && ($nomtableliee==$GLOBALS['PARAM']['ENSEIGNANT']) ){//Modif Hebie pour ignorer la val_cle dans la table enseignant au cas oé celle-lé existerait
					//Ajout HEBIE
					if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1 && $champ_cle == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
						//$sql_clauses .= ' AND '.$champ_cle.'='.$this->extraire_valeur_matrice($_POST[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_'.$ligne]);//A revoir si necessaire
					}else{
						$sql_clauses .= ' AND '.$champ_cle.'='.$this->val_cle[$nomtableliee][$champ_cle];
					}
					//Fin Ajout HEBIE
				}
			}

			foreach( $tab_join_zones as $i_common_join => $col_zone ){
				/// LE MAX DOIT ETRE RECUPERE DEPUIS LA BDD
				$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$col_zone]['champ'];
				$sql = '';
				$sql_clauses_plage = '';
				if(isset($this->tab_plage[$nom_champ])){
					if($sql_clauses<>'' || ($this->new_etab==true && $nom_champ == $GLOBALS['PARAM']['CODE_ETABLISSEMENT'])){
						$sql_clauses_plage .= ' AND '.$nom_champ.' >= '.$this->tab_plage[$nom_champ]['val_min'].' AND '.$nom_champ.' <= '.$this->tab_plage[$nom_champ]['val_max'];
					}else{
						$sql_clauses_plage .= ' WHERE '.$nom_champ.' >= '.$this->tab_plage[$nom_champ]['val_min'].' AND '.$nom_champ.' <= '.$this->tab_plage[$nom_champ]['val_max'];
					}
				}
				if( $this->new_etab==true && ($nom_champ == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']) ) {
					if($this->conn->databaseType=='access'){
						$sql .= 'SELECT MAX('.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].') as MAX_INSERT  FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' <> 4294967295 '.$sql_clauses_plage.' WITH OWNERACCESS OPTION';
					}else{
						$sql .= 'SELECT MAX('.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].') as MAX_INSERT  FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' <> 4294967295 '.$sql_clauses_plage;
					}
				}elseif( !isset($this->val_cle[$nomtableliee][$nom_champ]) ){
					$sql	.= 	'SELECT  MAX('.$nom_champ.') as MAX_INSERT FROM  '.$nomtableliee;
					$sql 	.=	$sql_clauses ;
					$sql	= 	str_replace( $nomtableliee . ' AND ' , $nomtableliee . ' WHERE ' ,  $sql );
					$sql .= $sql_clauses_plage;
				}
				//echo $sql.'<br>';
				// Traitement Erreur Cas Execute
				
				if(trim($sql) <> ''){
					if(!isset($cle_max_return[$i_common_join])){
						$cle_max_return[$i_common_join] = -1 ;
					}
					try {            
						$rs = $this->conn->Execute($sql);
						if($rs ===false){                
							throw new Exception('ERR_SQL');   
						}
						if ( isset($rs->fields['MAX_INSERT']) && (trim($rs->fields['MAX_INSERT']) <> '' )){
							$cle_max_found[$i_common_join] = $rs->fields['MAX_INSERT'];
						}else{
							$cle_max_found[$i_common_join] = 0;
						}
						
						if( $cle_max_found[$i_common_join]	> $cle_max_return[$i_common_join] ){
							$cle_max_return[$i_common_join] = $cle_max_found[$i_common_join] ;
						}
						//echo '$cle_max_return[$i_common_join]='.$cle_max_return[$i_common_join].'<br>';
					}
					catch (Exception $e) {
						$erreur = new erreur_manager($e,$sql);
					}
				}
			}
		}		
		foreach( $this->common_tabm_fields_joined as $nomtableliee => $tab_join_zones ){
			foreach( $tab_join_zones as $i_common_join => $col_zone ){
				if( isset($cle_max_return[$i_common_join]) ){
					$tab_return[$nomtableliee][$col_zone] = $cle_max_return[$i_common_join]  ;
				}
			}
		}	
		// Cas du code administratif	
		if(isset($this->tab_plage[$GLOBALS['PARAM']['CODE_ADMINISTRATIF']])) {
			$nom_champ = $GLOBALS['PARAM']['CODE_ADMINISTRATIF'];
			$nomtable = $GLOBALS['PARAM']['ETABLISSEMENT'];
			foreach( $this->tableau_zone_saisie[$nomtable] as $col_zone => $def_zone ) {
				if ($def_zone['champ'] == $nom_champ) {
					$sql = '';
					if ($this->conn->databaseType == "access") {
						$sql = 'SELECT  MAX(VAL('.$nom_champ.')) as MAX_INSERT FROM '.$nomtable.' WHERE '.$nom_champ.' >= \''.$this->tab_plage[$nom_champ]['val_min'].'\' AND '.$nom_champ.' <= \''.$this->tab_plage[$nom_champ]['val_max'].'\'';					
					} else {
						$sql = 'SELECT  MAX(CAST('.$nom_champ.' AS BIGINT)) as MAX_INSERT FROM '.$nomtable.' WHERE '.$nom_champ.' >= \''.$this->tab_plage[$nom_champ]['val_min'].'\' AND '.$nom_champ.' <= \''.$this->tab_plage[$nom_champ]['val_max'].'\'';					
					}				
					try {       
						$code_max_return = -1 ;
						try {
							if (is_int($this->tab_plage[$nom_champ]['val_min'])) {
								$rs = $this->conn->Execute($sql);
								if ( $rs === false ) {                
									throw new Exception('ERR_SQL');   
								}
								if ( isset($rs->fields['MAX_INSERT']) && (trim($rs->fields['MAX_INSERT']) <> '' )){
									$code_max_found = $rs->fields['MAX_INSERT'];
								} else {
									$code_max_found = 0;
								}
							} else {
								$code_max_found = 0;
							} 
						}
						catch (Exception $e) { echo "exception";
							$code_max_found = 0;
						}
						
						if( $code_max_found	> $code_max_return ){
							$code_max_return = $code_max_found ;
						}						
					}
					catch (Exception $e) {echo "exception2";
						$erreur = new erreur_manager($e,$sql);
					}
					$tab_return[$nomtable][$col_zone] = $code_max_return;
					break;
				}
			}
		}
		//echo '<pre><br>$tab_return<br>';
		//print_r( $tab_return);
		return($tab_return);
	}
	
	/**
	* METHODE :  get_chaine_reg($reg):
	* Permet de retourner la Chaine appartenant au secteur en session et
    * qui contient le type de $reg en derniére position
	*
	* @access public
	* 
	*/
	function get_chaine_reg($reg){
				
				$requete ='SELECT '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']
										.' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['HIERARCHIE'].', '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']
										.' WHERE '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' AND  '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$reg
										.' AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'=1'
										.' AND  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'];
		
				// Traitement Erreur Cas GetAll
				try {
						$result = $this->conn->Getrow($requete);
						if(!is_array($result)){                    
								throw new Exception('ERR_SQL');  
						}
						return($result[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']]);                  
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas GetAll
		}
		
	/**
	* METHODE :  get_chaines_loc($id_systeme):
	* Permet de retourner les Chaines activées dans DICO et appartenant au secteur $id_systeme
	*
	* @access public
	* 
	*/

    function get_chaines_loc($id_systeme){
	
			$tab_chaines_loc = array();
			$all_chaines_loc = array();
			$requete        = 'SELECT CODE_TYPE_CHAINE FROM DICO_CHAINE_LOCALISATION
												 WHERE CODE_TYPE_SYSTEME_ENSEIGNEMENT='.$id_systeme.' 
												 ORDER BY ORDRE_TYPE_CHAINE';
													
			
			// Traitement Erreur Cas GetAll
			try {
					$all_chaines_loc  = $GLOBALS['conn_dico']->GetAll($requete);
					if(!is_array($all_chaines_loc)){                    
							throw new Exception('ERR_SQL');  
					}                   
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}
			// Fin Traitement Erreur Cas GetAll			
			if( count($all_chaines_loc) == 0 ){
					$tab_chaines_loc[] = array( 'numCh' => 2, 'nbRegs' => 4 );
			}else{
						foreach( $all_chaines_loc as $i => $tab ){
								$req_niv  	=  'SELECT    COUNT(*) 
														 		FROM      '.$GLOBALS['PARAM']['HIERARCHIE']
														 		.' WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$tab['CODE_TYPE_CHAINE'];
								
								// Traitement Erreur Cas : Execute / GetOne
								try {            
										$niv_chaine = $this->conn->getOne($req_niv);
										if($niv_chaine === false){                
												 throw new Exception('ERR_SQL');   
										}
										$tab_chaines_loc[] = array( 'numCh' => $tab['CODE_TYPE_CHAINE'], 'nbRegs' => $niv_chaine );								 
								}
								catch (Exception $e) {
										 $erreur = new erreur_manager($e,$req_niv);
								}        
								// Fin Traitement Erreur Cas : Execute / GetOne															
						}
			}
			return $tab_chaines_loc;
	}
	
	/**
	* METHODE :  get_iLoc_chaine($code_ch, $tab_chaines):
	* Permet de retourner la position de la chaine $code_ch dans la liste des chaines activées (tab_chaines)
	*
	* @access public
	* 
	*/
	function get_iLoc_chaine($code_ch, $tab_chaines){
			if( is_array($tab_chaines)){
					foreach( $tab_chaines as $iLoc => $tab ){
							if( trim($tab['numCh']) == trim($code_ch) ){
									return($iLoc);
							}
					}
			}
	}
	
	/**
	* Méthode contient_type_e permet de savoir si l'objet grille contient
	* une zone de type "e": type effectif . Au cas échéant, la position 
	* de la zone en question est retournée
	*
	* @access public
	* 
	*/
	function contient_type_e($nomtableliee){
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			if( $zone_saisie['type']=='e' ){
				return $pos;
			}
		}
	}
	
	/**
	* METHODE :  get_dims_zone_matricielle($nomtableliee):
	* 
	* Permet de récupérer les dimensions d'une table
	* $nomtableliee é structure matricielle.
	* @access public
	* 
	*/
	function get_dims_zone_matricielle($nomtableliee,$ligne){ 
		$tab_dims = array();
		// Récupération Dimension Ligne
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			if( ($zone_saisie['type']=='dim_lig') ){
				$tab_dims['zones_dims'][] = array('pos_dim' => $pos, 'contenu_dim' => $zone_saisie);
				$nom_matrice = $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
				$tab_dims['codes_dims'][] = $this->code_nomenclature[$nomtableliee][$nom_matrice];
			}
		}
		
		$dim_col_trouvee = false;
		// Récupération Dimension Colonne
		if($ligne==''){
			foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
				if( ($zone_saisie['type']=='dim_col') ){
					$dim_col_trouvee = true;
					$tab_dims['zones_dims'][] = array('pos_dim' => $pos, 'contenu_dim' => $zone_saisie);
					$nom_matrice = $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
					$tab_dims['codes_dims'][] = $this->code_nomenclature[$nomtableliee][$nom_matrice];
				}
			}
		}
		if((($this->type_theme==4) && ($this->type_gril_eff_fix_col) && (!$dim_col_trouvee)) && ($ligne=='')){
			foreach($this->nomtableliee as $nomtableliee){
				// Récupération Dimension Colonne qui se trouve dans une autre des tables meres
				foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
					if( ($zone_saisie['type']=='dim_col') ){
						$tab_dims['zones_dims'][] = array('pos_dim' => $pos, 'contenu_dim' => $zone_saisie);
						$nom_matrice = $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
						$tab_dims['codes_dims'][] = $this->code_nomenclature[$nomtableliee][$nom_matrice];
					}
				}
			}
		}
		return($tab_dims);
	}
	
	/**
	* METHODE :  get_noms_champs_matriciels($nomtableliee):
	* 
	* Permet de récupérer les Mesures d'une table
	* $nomtableliee é structure matricielle.
	* @access public
	* 
	*/
	function get_noms_champs_matriciels($nomtableliee){
		$tab_chps_mat = array();
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			if( ($zone_saisie['type']=='zone_mat') ){
				$tab_chps_mat[$pos] = $zone_saisie['champ'];
			}
		}
		//echo '<pre>get_dims_zone_matricielle<br>';
		//print_r($tab_dims);
		return($tab_chps_mat);
	}
	
	/**
	* METHODE :  get_vals_champs_type($nomtableliee, $ligne_donnees, $type_champ) :
	* 
	* Permet d'extraire les Valeurs des champs de type $type_champ
	* é partir d'une ligne d'enregistrement $ligne_donnees
	* @access public
	* 
	*/
	function get_vals_champs_type($nomtableliee, $ligne_donnees, $type_champ){
		$vals_champs = array();
		foreach($ligne_donnees as $col_data => $val_data){
			$col_zone = $this->col_zone_Equi_col_data[$nomtableliee]['data'][$col_data];
			if( ($this->tableau_zone_saisie[$nomtableliee][$col_zone]['type'] == $type_champ) ){
				$vals_champs[$col_zone] = $val_data;
			}
		}
		//echo '<pre>get_dims_zone_matricielle<br>';
		//print_r($tab_dims);
		return($vals_champs);
	}
	
	
	/**
	* METHODE :  get_noms_champs_matriciels($nomtableliee):
	* 
	* Permet de récupérer les Mesures d'une table
	* $nomtableliee é structure matricielle.
	* @access public
	* 
	*/
	function get_vals_champs_name($nomtableliee, $ligne_donnees, $list_champs){
		$vals_champs = array();
		if( !(is_array($list_champs)) and trim($list_champs) <> ''){
			$list_champs = array( 0 => $list_champs);
		}
		if(is_array($list_champs)){
			foreach($ligne_donnees as $col_data => $val_data){
				//$col_zone = $this->col_zone_Equi_col_data[$nomtableliee]['data'][$col_data];
				if($this->est_ds_tableau($this->champs[$nomtableliee][$col_data], $list_champs)){
					$vals_champs[$col_data] = $val_data;
				}
			}
		}
		//echo '<pre>get_dims_zone_matricielle<br>';
		//print_r($tab_dims);
		return($vals_champs);
	}
	
	/**
	* METHODE :  get_concat_common_keys_val($nomtableliee, $ligne_donnees) :
	* 
	* Permet de concaténer les valeurs des champs ayant des jointures communes,
	* é partir de la ligne d'enregistrement $ligne_donnees
	* @access public
	* 
	*/
	function get_concat_common_keys_val($nomtableliee, $ligne_donnees){
		$t_key_val = array();
		if(isset($ligne_donnees) && is_array($ligne_donnees)){
			if(isset($this->common_tabm_fields_joined[$nomtableliee]) && is_array($this->common_tabm_fields_joined[$nomtableliee])){
				foreach($this->common_tabm_fields_joined[$nomtableliee] as $i_col => $col_zone){
					$col_data 		= $this->col_zone_Equi_col_data[$nomtableliee]['zone'][$col_zone];
					$t_key_val[]	= $ligne_donnees[$col_data] ;
				}
			}
		}
		//echo '<pre>get_dims_zone_matricielle<br>';
		//print_r($tab_dims);
		return(implode('_', $t_key_val));
	}
	
	/**
	* METHODE :  ligne_non_vide($nomtableliee, $ligne_donnees) :
	* 
	* Permet de verifier que ligne_donnees est pour le template en cours
	* é partir de la ligne d'enregistrement $ligne_donnees
	* @access public
	* 
	*/
	function ligne_non_vide($nomtableliee, $ligne_donnees){
		$nb_cles = 0;
		$nb_champs_non_vide = 0;
		if(isset($this->tab_keys_zones[$nomtableliee]) && is_array($this->tab_keys_zones[$nomtableliee]))
			$nb_cles = count($this->tab_keys_zones[$nomtableliee]);
		if(isset($ligne_donnees) && is_array($ligne_donnees)){
			foreach($ligne_donnees as $val){ 
				if($val <> ''){	
					$nb_champs_non_vide++;
				}
			}
		}
		if($nb_champs_non_vide > $nb_cles) return true;
		return false;
	}
	
	/**
	* METHODE :  get_tab_all_keys_val($nomtableliee, $ligne_donnees) :
	* 
	* Permet de mettre en tableau les valeurs de tous les champs de type clé
	* é partir de la ligne d'enregistrement $ligne_donnees
	* @access public
	* 
	*/
	function get_tab_all_keys_val($nomtableliee, $ligne_donnees){
		$t_key_val = array();
		if(isset($ligne_donnees) && is_array($ligne_donnees)){
			if(isset($this->tab_keys_zones[$nomtableliee]) && is_array($this->tab_keys_zones[$nomtableliee])){
				foreach($this->tab_keys_zones[$nomtableliee] as $colonne => $col_zone){ 
					$col_data 				= $this->col_zone_Equi_col_data[$nomtableliee]['zone'][$col_zone] ;
					$t_key_val[$col_data]	= $ligne_donnees[$col_data] ;
				}
			}
		}
		return($t_key_val);
	}
	
	
	/**
	* METHODE :  existe_element_grille_action($keys_vals, $elt, $matr, $nomtableliee) :
	* 
	* Permet de savoir l'action de MAJ é entreprendre sur les données $elt aprés 
	$ parcours et vérification dans le contenu du tableau $matr
	* @access public
	* 
	*/
	function existe_element_grille_action($keys_vals, $elt, $matr, $nomtableliee){
		/* cette fonction permet de vérifier l'existence d'un élément matricielle
		 * dans une autre matrice de méme dimension (n colonnes)et (p lignes)
		 */
		 $trouve	=	false;	 
		 if( is_array($matr) ){
			 foreach ($matr as $element){
				unset($curr_keys);
				$curr_keys	=	$this->get_tab_all_keys_val($nomtableliee, $element) ;	 	
						
				if (count(array_diff_assoc($keys_vals, $curr_keys))==0){
					// l'élément matricielle $elt a été trouvé
					$trouve		=	true;
					// Il faut vérifier si l'objet a subi des modification;	 		
					
					if (count(array_diff_assoc($elt, $element))==0){
						$action	=	'R';	 			
						return $action;
					}
					else {	 			
						$action	=	'U';	 			 			
						return $action;
					}	 			 			
				}
			 }
		 }
		 if ($trouve==false) {
				// il s'agit d'un élément non trouvé
				$action	=	'I';
				return $action;	
		 }		
	}
	
	/**
	* METHODE :  existe_cles_dans_tab_cles($keys_vals, $tab_keys) :
	* 
	* Permet de savoir si les cles Keys_vals sont dans le tableau des cles tab_keys
	$ parcours et vérification dans le contenu du tableau $tab_keys
	* @access public
	* 
	*/
	function existe_cles_dans_tab_cles($keys_vals, $tab_keys){
		/*echo 'keys_vals<pre>';
		print_r($keys_vals);
		echo '<br><br>tab_keys<pre>';
		print_r($tab_keys);*/
		 $trouve	=	false;	 
		 if( is_array($tab_keys) ){
			 foreach ($tab_keys as $keys){
				if (count(array_diff_assoc($keys_vals, $keys))==0){
					// l'élément matricielle $elt a été trouvé
					$trouve		=	true;
				}
			 }
		 }
		 //echo "<br>$trouve<br>";
		 return $trouve;	
	}
	/**
	* METHODE :  get_pos_field_in_common_fields_joined($colonne, $nomtableliee) :
	* 
	* Permet de récupérer l'ordre d'un champ dans le tableau des jointures communes
	* @access public
	* 
	*/
	function get_pos_field_in_common_fields_joined($colonne, $nomtableliee){
		//echo'<pre>';
		//print_r($this->common_tabm_fields_joined[$nomtableliee]);
		if( is_array($this->common_tabm_fields_joined[$nomtableliee]) ){
			foreach($this->common_tabm_fields_joined[$nomtableliee] as $i_col => $col_zone){
				if($colonne == $col_zone){
					return($i_col);
				}
			}
		}
	}
	
	
	/**
	* METHODE :  do_concat_dimensions_codes($tab_dim) :
	* 
	* Permet de d'effectuer les combinaisons de codes
	* possibles avec les codes des dimensions
	* @access public
	* 
	*/
	function do_concat_dimensions_codes($tab_dim){
		$tab_concat = array();
		$nb_dims = count($tab_dim);
		for ($i_dim = 0; $i_dim < $nb_dims; $i_dim++){
			if($i_dim == 0){
				$tab_concat = $tab_dim[$i_dim];
			}else{
				$tmp_tab = array();
				$k = 0 ;
				$nb_concat 		= count($tab_concat);
				$nb_elem_dim	= count($tab_dim[$i_dim]);
				
				for ($i = 0; $i < $nb_concat; $i++){
					for ($j = 0; $j < $nb_elem_dim; $j++){
						$tmp_tab[$k] = $tab_concat[$i] . '_' . $tab_dim[$i_dim][$j] ;
						$k++;
					}
				}
				$tab_concat = $tmp_tab ;
			}
		}
		return $tab_concat;
	}
	
	function do_concat_dimensions_codes_lig($nomtableliee){
		$tab_dims = array();
		// Récupération Dimension Colonne
		$dim_col_trouvee = false;
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			if( ($zone_saisie['type']=='dim_col') ){
				$dim_col_trouvee = true;
				$tab_dims['zones_dims'][] = array('pos_dim' => $pos, 'contenu_dim' => $zone_saisie);
				$nom_matrice = $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
				$tab_dims['codes_dims'][] = $this->code_nomenclature[$nomtableliee][$nom_matrice];
			}
		}
		if(!$dim_col_trouvee){
			foreach($this->nomtableliee as $nomtableliee){
				// Récupération Dimension Colonne qui se trouve dans une autre des tables meres
				foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
					if( ($zone_saisie['type']=='dim_col') ){
						$tab_dims['zones_dims'][] = array('pos_dim' => $pos, 'contenu_dim' => $zone_saisie);
						$nom_matrice = $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
						$tab_dims['codes_dims'][] = $this->code_nomenclature[$nomtableliee][$nom_matrice];
					}
				}
			}
		}
		$tab_concat = array();
		$tab_dim = $tab_dims['codes_dims'];
		$nb_dims = count($tab_dim);
		for ($i_dim = 0; $i_dim < $nb_dims; $i_dim++){
			if($i_dim == 0){
				$tab_concat = $tab_dim[$i_dim];
			}else{
				$tmp_tab = array();
				$k = 0 ;
				$nb_concat 		= count($tab_concat);
				$nb_elem_dim	= count($tab_dim[$i_dim]);
				
				for ($i = 0; $i < $nb_concat; $i++){
					for ($j = 0; $j < $nb_elem_dim; $j++){
						$tmp_tab[$k] = $tab_concat[$i] . '_' . $tab_dim[$i_dim][$j] ;
						$k++;
					}
				}
				$tab_concat = $tmp_tab ;
			}
		}
		//echo "aaaaaaaaaaaaa<pre>";
		//print_r($tab_concat);
		return $tab_concat;
	}
	
	function do_concat_dimensions_codes_col($nomtableliee){
		$tab_dims = array();
		// Récupération Dimension Ligne
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			if( ($zone_saisie['type']=='dim_lig') ){
				$tab_dims['zones_dims'][] = array('pos_dim' => $pos, 'contenu_dim' => $zone_saisie);
				$nom_matrice = $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
				$tab_dims['codes_dims'][] = $this->code_nomenclature[$nomtableliee][$nom_matrice];
			}
		}
		
		$tab_concat = array();
		$tab_dim = $tab_dims['codes_dims'];
		$nb_dims = count($tab_dim);
		for ($i_dim = 0; $i_dim < $nb_dims; $i_dim++){
			if($i_dim == 0){
				$tab_concat = $tab_dim[$i_dim];
			}else{
				$tmp_tab = array();
				$k = 0 ;
				$nb_concat 		= count($tab_concat);
				$nb_elem_dim	= count($tab_dim[$i_dim]);
				
				for ($i = 0; $i < $nb_concat; $i++){
					for ($j = 0; $j < $nb_elem_dim; $j++){
						$tmp_tab[$k] = $tab_concat[$i] . '_' . $tab_dim[$i_dim][$j] ;
						$k++;
					}
				}
				$tab_concat = $tmp_tab ;
			}
		}
		//echo "aaaaaaaaaaaaa<pre>";
		//print_r($tab_concat);
		return $tab_concat;
	}
    
	/**
	* METHODE :  all_type_key_c($nomtableliee):
	* Permet de savoir si tous les champs de la table mére sont des clés simples
    * Et Comme ils sont invisibles sur les frames, il faut reporter dans le POST
    * pour éviter que les enregistrements ne soient supprimés dans la base
	*
	* @access public
	* 
	*/
    function all_type_key_c($nomtableliee){
		if( count($this->tableau_zone_saisie[$nomtableliee]) > 0 ){
            foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
                if( $zone_saisie['type'] <> 'c' ){
                    return false;
                }
            }
            return true;
        }
	}
    
	
	/**
	* METHODE :  get_pos_champ_ref($nomtableliee):
	* Retourne la position du champ Ref dans une TABLE_MERE : $nomtableliee
    * On ne doit en avoir plus d'un
	*
	* @access public
	* 
	*/
     function get_pos_champ_ref($nomtableliee){
        $pos_type = $this->contient_type_e($nomtableliee) ;
         
        if(trim($pos_type) == ''){
            $pos_type = $this->contient_type_tvm($nomtableliee) ;
            //echo "<br> $nomtableliee -> $pos_type <br>";
            //echo "<br> pos_type = $pos_type <br>";
        }
        if($pos_type){
            $id_zone_ref = $this->tableau_zone_saisie[$nomtableliee][$pos_type]['id_zone_ref'];
            //echo "<br> id_zone_ref = $id_zone_ref <br>";
            //echo '<pre>';
            //print_r($this->tableau_zone_saisie[$nomtableliee][$pos_type]);
            return($this->get_pos_zone_in_tab($id_zone_ref, $nomtableliee));
        }
        //return($return);				
    }
 
	
	/**
	* METHODE :  get_pos_zone_in_tab($id_zone, $nomtableliee):
	* Retourne la position de la zone : $id_zone , dans la TABLE_MERE : $nomtableliee
    * On ne doit en avoir plus d'un
	*
	* @access public
	* 
	*/
	function get_pos_zone_in_tab($id_zone, $nomtableliee){
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone){
			if( $id_zone == $zone['id_zone'] ){
                //echo"<br> ---- pos = $pos ---- champ = $champ ---- <br>";
				return($pos);
				break;
			}
		}
	}
	
	
	/**
	* METHODE :  get_pos_champ_cle_in_tab($nom_cle, $nomtableliee):
	* Retourne la position d'un champ de type clé : $nom_cle 
	*
	* @access public
	* 
	*/
	function get_pos_champ_cle_in_tab($nom_cle, $nomtableliee){
		foreach($this->tab_keys_zones[$nomtableliee] as $pos => $pos_zone){
			$nom_champ = $this->tableau_zone_saisie[$nomtableliee][$pos_zone]['champ'] ;
			if( trim($nom_cle) == trim($nom_champ) ){
                //echo"<br> ---- pos = $pos ---- champ = $champ ---- <br>";
				return($pos);
				break;
			}
		}
	}
	
	
	/**
	* METHODE :  get_pos_champ_data_in_tab($nom_champ, $nomtableliee):
	* 
	* Retourne l'ordre d'un élément  $nom_champ dans la liste des Champs 
	*
	* @access public
	* 
	*/
	function get_pos_champ_data_in_tab($nom_champ, $nomtableliee){
		foreach($this->champs[$nomtableliee] as $pos_data => $cur_chp){
			if( trim($nom_champ) == trim($cur_chp) ){
                //echo"<br> ---- pos = $pos ---- champ = $champ ---- <br>";
				return($pos_data);
				break;
			}
		}
	}

	/**
	* METHODE :  set_val_cle_post($matr, &$matr_1, $nom_cle, $ligne_tpl, $ligne_post, $colonne, $table_mere)
	* Pour récupérer les valeurs de certaines clés é partir du POST au lieu des VAL_CLE de la Base
    * Ou bien Reseigner une clé de la table secondaire s'il n'a pas de valeur par son équivalent 
    * (ou champ jointure) dans la table principale
	*
	* @access public
	* 
	*/
    function set_val_cle_post($matr, &$matr_1, $nom_cle, $ligne_tpl, $ligne_post, $colonne, $table_mere){
    
        //echo'<pre>';
        //print_r($matr);
        $v	=	$nom_cle.'_'.$ligne_tpl;
        //echo '<br>aaa='.$matr[$v];
        if(isset($matr[$v]) and trim($matr[$v])<>''){
            $matr_1[$ligne_post][$colonne] = $this->extraire_valeur_matrice($matr[$v]); 
        }elseif(trim($nom_cle)==trim($GLOBALS['PARAM']['CODE_ETABLISSEMENT']) and $this->new_etab <> true){
			//Ajout HEBIE pour l'enregistrement sequentiel des enseignants basé sur la valeur CODE_ETAB_ENSEIG_TRANSFERT de chaque ligne é transmettre a chaque fois é $this->code_etablissement
			if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
				$matr_1[$ligne_post][$colonne] =  $this->extraire_valeur_matrice($matr[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_'.$ligne_tpl]);// Position le code Etab
			}else{								                 
				$matr_1[$ligne_post][$colonne] = $this->code_etablissement; // Position le code Etab
			}
			//Fin Ajout HEBIE
		}elseif(trim($nom_cle)==trim($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'])){
			$matr_1[$ligne_post][$colonne] = $this->code_annee; 
		}elseif(trim($nom_cle)==trim($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'])){
			$matr_1[$ligne_post][$colonne] = $this->code_filtre;  
		}else{
            if(isset($this->tab_joins_in_main_tab[$table_mere][$nom_cle])){
                $main_champ_join = $this->tab_joins_in_main_tab[$table_mere][$nom_cle];
                $v	=	$main_champ_join.'_'.$ligne_tpl;
                if(isset($matr[$v]) and trim($matr[$v])<>''){
                    $matr_1[$ligne_post][$colonne] = $this->extraire_valeur_matrice($matr[$v]); 
                }else{
					if(isset($this->VARS_GLOBALS['ligne_code_limit'][$this->main_table_mere][$ligne_tpl])){
						$pos_cle_main = $this->get_pos_champ_cle_in_tab($main_champ_join, $this->main_table_mere);
						$tab_keys_line 	= explode('_', $this->VARS_GLOBALS['ligne_code_limit'][$this->main_table_mere][$ligne_tpl]);
						$pos_in_joins 	= $this->get_pos_field_in_common_fields_joined($pos_cle_main, $this->main_table_mere) ;
						if(isset($pos_in_joins) &&(trim($pos_in_joins) <> '')){
							$matr_1[$ligne_post][$colonne] = $tab_keys_line[$pos_in_joins];
						}
					}
                }
            }
			//echo"$table_mere<br/>$nom_cle<pre>";
        	//print_r($matr_1);
        }
		
    }
 
	
	/**
	* METHODE :  contient_type_li_ch($nomtableliee):
	* Pour savoir si la table $nomtableliee représente une structure de liste checkbox
	*
	* @access public
	* 
	*/
    function contient_type_li_ch($nomtableliee){
        /*echo '<pre>';
        echo 'passage fonction '.$nomtableliee. ' <br>';
        print_r($this->tableau_zone_saisie[$nomtableliee]);
        echo '</pre>';*/
        //die();
        foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
            
            if( $zone_saisie['type']=='li_ch' ){				
               //echo 'trouvé element';
                             //echo '<br><pre>';
                             //print_r($zone_saisie);
                return $pos;
            }
        }
    }

	
	/**
	* METHODE :  contient_type_tvm($nomtableliee):
	* Pour savoir si la table $nomtableliee représente une structure de text valeur multiple
	*
	* @access public
	* 
	*/
     function contient_type_tvm($nomtableliee){
		/*echo '<pre>';
        echo 'passage fonction '.$nomtableliee. ' <br>';
        print_r($this->tableau_zone_saisie[$nomtableliee]);
        echo '</pre>';*/
        //die();
        foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			
            // echo 'zone en cours = '.$zone_saisie['type'];
            
            
            if( $zone_saisie['type']=='tvm' ){				
               //echo 'trouvé element';
							 //echo '<br><pre>';
							 //print_r($zone_saisie);
                return $pos;
	}	}	}
	
     
	/**
	* METHODE :  contient_type_zone_mat ($nomtableliee):
	*
	* Pour savoir si la table $nomtableliee contient une zone matricielle
	*
	* @access public
	* 
	*/
	 function contient_type_zone_mat($nomtableliee){
		/*echo '<pre>';
        echo 'passage fonction '.$nomtableliee. ' <br>';
        print_r($this->tableau_zone_saisie[$nomtableliee]);
        echo '</pre>';*/
        //die();
        foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			
            // echo 'zone en cours = '.$zone_saisie['type'];
            
            
            if( $zone_saisie['type']=='zone_mat' ){				
               //echo 'trouvé element';
							 //echo '<br><pre>';
							 //print_r($zone_saisie);
                return $pos;
	}	}	}

	
	/**
	* Méthode est_ds_tableau permet de savoir si l'objet grille contient une 
	* zone de type "e": type effectif Au cas échéant, la position de la zone 
	* en question est retournée
	*
	* @access public
	* @param numeric $elem : élément é rechercher 
	* @param numeric $tab  : tableau oé on recherche
	*/
	
	function est_ds_tableau($elem,$tab){
		if(is_array($tab)){
			foreach($tab as $elements){
				if( $elements == $elem ){
					return true;
	}	}	}	}
	
	
	/**
	* METHODE :  faire_liaison_ligne_code($nomtableliee)
	*
	*<pre>	
	*	--> Pacourir les lignes d'enregistrement de la table mére
	*		-	Identifier les combinaisons de valeurs des clés permettant de 
	*			faire la correspondance avec chaque ligne du template  
	*</pre>
	*
	* @access public
	*/	
	function faire_liaison_ligne_code($nomtableliee){
		$mat = array();
		// common_tabm_fields_joined
		$tabm_for_ligne_code = $nomtableliee ;
		if( isset($this->VARS_GLOBALS['donnees_bdd'][$this->main_table_mere]) && count($this->VARS_GLOBALS['donnees_bdd'][$this->main_table_mere])){
			$tabm_for_ligne_code = $this->main_table_mere ;
		}
		if(is_array($this->VARS_GLOBALS['donnees_bdd'][$tabm_for_ligne_code])){
			foreach($this->VARS_GLOBALS['donnees_bdd'][$tabm_for_ligne_code] as $ligne_donnees){
				$indice_keys = $this->get_concat_common_keys_val($tabm_for_ligne_code, $ligne_donnees) ;
				if( !$this->est_ds_tableau($indice_keys, $mat) ){
					$mat[] = $indice_keys;
				}
			}
		}
		//if(count($mat))
		//sort ($mat,SORT_NUMERIC);
		return $mat ;
	}
	
	
	/**
	* METHODE :  limiter_affichage ( $nomtableliee )
	* Permet é partir du jeu d'enregistrement global, de se positionner 
	* sur les données concernant la page en cours
	* Ces données sont rangées dans la variable   : $matrice_donnees
	*<pre>
	*	--> Parcours l'ensemble du jeu d'enregistrement de la table mére : $nomtableliee
	*		-	Si la combinaison des valeurs des clé de la 
	*			ligne de donnée en cours est contenue dans les limites d'affichage
	*				--> Ranger dans $this->matrice_donnees
	*</pre>
	* @access public
	*/	
	function limiter_affichage($nomtableliee){
		
		$this->matrice_donnees[$nomtableliee] = array();
		if(!isset($this->VARS_GLOBALS['ligne_code'][$nomtableliee])){
            // Récupérer les équivalences entre les codes et les lignes du tpl
			$this->VARS_GLOBALS['ligne_code'][$nomtableliee] = $this->faire_liaison_ligne_code($nomtableliee) ;
		}	
		
		if(!isset($GLOBALS['nbre_total_enr'])){
			$GLOBALS['nbre_total_enr'] = count( $this->VARS_GLOBALS['donnees_bdd'][$nomtableliee] );
		}
		
		$ligne_code_limit = array_slice( $this->VARS_GLOBALS['ligne_code'][$nomtableliee], $GLOBALS['debut'], $GLOBALS['cfg_nbres_ppage']);
		foreach($ligne_code_limit as $val_code){
			$this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee][] = $val_code;
		}

		if( is_array($this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee]) ){
			foreach($this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee] as $ligne => $code){
				$this->VARS_GLOBALS['inv_ligne_code_limit'][$nomtableliee][$code] = $ligne;
			}
		}			
			//echo'<br>ligne_code_limit <br>'.$nomtableliee.'<pre>';
			//print_r($this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee]);
		
		if( $this->contient_type_e($nomtableliee) or $this->contient_type_tvm($nomtableliee) or $this->est_ds_tableau($nomtableliee, $this->list_tabms_matric) ){
            //s'il s'agit d'un type 'e' ou 'tvm' : structure matricielle
			if(!isset($GLOBALS['nbre_total_enr']))
				$GLOBALS['nbre_total_enr'] = count( $this->VARS_GLOBALS['ligne_code'][$nomtableliee] );
			
			//echo'**************<pre>';
			//print_r($ligne_code_limit);
			
			foreach( $this->VARS_GLOBALS['donnees_bdd'][$nomtableliee] as $ligne_donnees){
				$cle_courante = $this->get_concat_common_keys_val($nomtableliee, $ligne_donnees) ;
				if($this->est_ds_tableau($cle_courante, $this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee])){
					$this->matrice_donnees[$nomtableliee][] = $ligne_donnees;	
				}
			}
			$GLOBALS['nbenr'] = count($this->matrice_donnees[$nomtableliee]);
			//echo"<br>1 -> deb=".$GLOBALS['debut']."nb_page=".$GLOBALS['cfg_nbres_ppage']."";
		}
		elseif( trim($this->contient_type_li_ch($nomtableliee)) <> '' ){
            //s'il s'agit d'une TABLE_MERE contenant la structure liste checkbox
			if(!isset($GLOBALS['nbre_total_enr']))
				$GLOBALS['nbre_total_enr'] = count( $this->VARS_GLOBALS['ligne_code'][$nomtableliee] );
			
			//echo'**************<pre>';
			//print_r($ligne_code_limit);
			
			foreach( $this->VARS_GLOBALS['donnees_bdd'][$nomtableliee] as $ligne_donnees){
				$cle_courante = $this->get_concat_common_keys_val($nomtableliee, $ligne_donnees) ;
				if($this->est_ds_tableau($cle_courante,$this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee])){
					$this->matrice_donnees[$nomtableliee][] = $ligne_donnees;	
				}
			}
			$GLOBALS['nbenr'] = count($this->matrice_donnees[$nomtableliee]);
			//echo"<br>1 -> deb=".$GLOBALS['debut']."nb_page=".$GLOBALS['cfg_nbres_ppage']."";
		}
		/*
		elseif( $this->contient_type_li_ch($nomtableliee) ){
				$this->matrice_donnees[$nomtableliee] = $this->VARS_GLOBALS['donnees_bdd'][$nomtableliee];
		}*/
		elseif($nomtableliee == $GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']){
				$this->matrice_donnees[$nomtableliee] = $this->VARS_GLOBALS['donnees_bdd'][$nomtableliee];
		}
		else{
			// 
			
			if(is_array($this->VARS_GLOBALS['donnees_bdd'][$nomtableliee])){				
				foreach( $this->VARS_GLOBALS['donnees_bdd'][$nomtableliee] as $ligne_donnees){
					$cle_courante = $this->get_concat_common_keys_val($nomtableliee, $ligne_donnees) ;
					//echo '<br> p='.$cle_courante ;
					if($this->est_ds_tableau($cle_courante,$this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee])){
						$this->matrice_donnees[$nomtableliee][] = $ligne_donnees;	
					}
				}
			}
			//echo"<br>debut=".$GLOBALS['debut']."nb_page=".$GLOBALS['cfg_nbres_ppage']."<br>";
			$GLOBALS['nbenr'] = count($this->matrice_donnees[$nomtableliee]);
			//echo"<br>2 -> deb=".$GLOBALS['debut']."nb_page=".$GLOBALS['cfg_nbres_ppage']."";
		}
		//echo 'MATRICE DONNEES <pre>';
		//print_r($this->matrice_donnees);
	}
	
	
	/**
	* METHODE :  preparer_donnees_tpl ( $nomtableliee )
	* Permet de mettre les données $this->matrice_donnees
	* dans une position telle que chaque ligne d'enregistrement liée é un
	* identificateur de ligne (combinaison unique de clés) soit bien positionnée 
	* pour le template. Ces données sont rangées dans la variable  : $this->matrice_donnees_tpl
	*<pre>
	*	--> Parcours des enregistrements de $this->matrice_donnees
	*		-	Associer é chaque combinaison unique des valeurs de clés
	*			une ligne du template
	*				--> Ranger dans $this->matrice_donnees_tpl
	*</pre>
	* @access public
	*/	
		
	function preparer_donnees_tpl($nomtableliee){
		$this->matrice_donnees_tpl[$nomtableliee] = array();
		if( $this->contient_type_e($nomtableliee) or $this->contient_type_tvm($nomtableliee) ){
            //s'il s'agit d'un type 'e' ou 'tvm' : structure matricielle
			$mat = array();
			$ligne = -1;
			$tab_ligne_cle = array();
			$tab_cle = array();
			if(is_array($this->matrice_donnees[$nomtableliee]))
				foreach($this->matrice_donnees[$nomtableliee] as $ligne_donnees){
                    // Pour chq ligne de données on extrait la valeur de clé é la position incrémentale : $cle_courante
					$cle_courante = $this->get_concat_common_keys_val($nomtableliee, $ligne_donnees) ;
					// Pour chq ligne de données on extrait également la valeur de clé  du champ réf : $cle_colonne
                    $pos_champ_ref = $this->get_pos_champ_ref($nomtableliee);
					$cle_colonne  = $ligne_donnees[$pos_champ_ref];
                    //echo"pos_champ_ref=$pos_champ_ref";
					if( !$this->est_ds_tableau($cle_courante,$tab_cle) ) {
                        // Si la clé courante n'a pas de ligne associée
						$ligne++; //on prépare une nouvelle ligne
						//$tab_ligne_cle[$cle_courante] = $ligne;
						$tab_ligne_cle[$cle_courante] = $this->VARS_GLOBALS['inv_ligne_code_limit'][$nomtableliee][$cle_courante];
						$tab_cle[] = $cle_courante;
					}
					if($pose_type_e = $this->contient_type_e($nomtableliee)){
						$tab_champs_e	=	$this->tableau_zone_saisie[$nomtableliee][$pose_type_e]['champ'];
						$mat[$tab_ligne_cle[$cle_courante]][$cle_colonne] = $this->get_vals_champs_name($nomtableliee, $ligne_donnees, $tab_champs_e) ;
					}elseif($pose_type_tvm = $this->contient_type_tvm($nomtableliee)){
						$tab_champs_tvm	=	$this->tableau_zone_saisie[$nomtableliee][$pose_type_tvm]['champ'];
						$mat[$tab_ligne_cle[$cle_courante]][$cle_colonne] =	$this->get_vals_champs_name($nomtableliee, $ligne_donnees, $tab_champs_tvm) ;
					}
				}
				$this->matrice_donnees_tpl[$nomtableliee] = $mat ;	
				//echo '<pre>matrice_donnees_tpl TVM<br>';
				//print_r($this->matrice_donnees_tpl[$nomtableliee]);
		}									
		elseif( $this->est_ds_tableau($nomtableliee, $this->list_tabms_matric) ){
											
			if( count($tab_dims = $this->get_dims_zone_matricielle($nomtableliee,''))){
				//s'il s'agit d'un type 'e' ou 'tvm' : structure matricielle
				$mat = array();
				$ligne = -1;
				$tab_ligne_cle = array();
				$tab_cle = array();
				if(is_array($this->matrice_donnees[$nomtableliee])){
					foreach($this->matrice_donnees[$nomtableliee] as $ligne_donnees){
						// Pour chq ligne de données on extrait la valeur de clé é la position incrémentale : $cle_courante
						$cle_courante = $this->get_concat_common_keys_val($nomtableliee, $ligne_donnees) ;
						// Pour chq ligne de données on extrait également la valeur de clé  du champ réf : $cle_colonne
						$tab_zones_dims = $tab_dims['zones_dims'];
						$tab_codes_dim_curr_line = array();
						foreach($tab_zones_dims as $i_dim => $dim){
							$tab_codes_dim_curr_line[] = $ligne_donnees[$dim['pos_dim']];
						}
						if(!isset($mat[$tab_ligne_cle[$cle_courante]][implode('_',$tab_codes_dim_curr_line)])){
							//echo"pos_champ_ref=$pos_champ_ref";
							if( !$this->est_ds_tableau($cle_courante,$tab_cle) ) {
								// Si la clé courante n'a pas de ligne associée
								$ligne++; //on prépare une nouvelle ligne
								//$tab_ligne_cle[$cle_courante] = $ligne;
								$tab_ligne_cle[$cle_courante] = $this->VARS_GLOBALS['inv_ligne_code_limit'][$nomtableliee][$cle_courante];
								$tab_cle[] = $cle_courante;
							}
							$mat[$tab_ligne_cle[$cle_courante]][implode('_',$tab_codes_dim_curr_line)] = $this->get_vals_champs_type($nomtableliee, $ligne_donnees, 'zone_mat');
						}
					}
					$this->matrice_donnees_tpl[$nomtableliee] = $mat ;	
				}
			}
			//echo '<pre> matrice_donnees_tpl - Zone Matric : '.$nomtableliee.'<br>';
			//print_r($this->matrice_donnees_tpl[$nomtableliee]);
		}

		elseif( trim($pos_li_ch = $this->contient_type_li_ch($nomtableliee)) <> ''){
			//s'il s'agit d'une TABLE_MERE contenant la structure liste checkbox
			
			$mat = array();
			$ligne = -1;
			$tab_ligne_cle = array();
			$tab_cle = array();

			if(is_array($this->matrice_donnees[$nomtableliee])){
				foreach($this->matrice_donnees[$nomtableliee] as $ligne_donnees){
					$cle_courante = $this->get_concat_common_keys_val($nomtableliee, $ligne_donnees) ;
                    //echo"pos_champ_ref=$pos_champ_ref";
					if( !$this->est_ds_tableau($cle_courante,$tab_cle) ) {
						//$cle = $cle_courante ;
						$ligne++; // Associer une ligne é chq différente clé  : position incrémentale
						//$tab_ligne_cle[$cle_courante] = $ligne;
						$tab_ligne_cle[$cle_courante] = $this->VARS_GLOBALS['inv_ligne_code_limit'][$nomtableliee][$cle_courante];
						$tab_cle[] = $cle_courante;
					}
					$mat[$tab_ligne_cle[$cle_courante]][$ligne_donnees[$pos_li_ch]] =  $ligne_donnees[$pos_li_ch];
				}
				$this->matrice_donnees_tpl[$nomtableliee] = $mat ;
			}	
		}
		else{ // Sinon matrice_donnees_tpl = matrice_donnees
			//$this->matrice_donnees_tpl[$nomtableliee] = $this->matrice_donnees[$nomtableliee] ;
			
			$mat = array();
			$ligne = -1;
			$tab_ligne_cle = array();
			$tab_cle = array();
			if(is_array($this->matrice_donnees[$nomtableliee])){
				foreach($this->matrice_donnees[$nomtableliee] as $ligne_donnees){
                    // Pour chq ligne de données on extrait la valeur de clé é la position incrémentale : $cle_courante
					$cle_courante = $this->get_concat_common_keys_val($nomtableliee, $ligne_donnees) ;
					$ligne_cle_courante 		= $this->VARS_GLOBALS['inv_ligne_code_limit'][$nomtableliee][$cle_courante];
					$mat[$ligne_cle_courante] 	= $ligne_donnees ;
				}
				$this->matrice_donnees_tpl[$nomtableliee] = $mat ;	
				//echo"$nomtableliee - tpl<br> <pre>";
				//print_r($this->matrice_donnees_tpl[$nomtableliee]);
			}
		}
	}
	
	/**
	* METHODE :  remplir_template ( template )
	* Permet de données des valeurs aux variables du template
	* et d'afficher la page HTML .
	*<pre>
	*	-->	Pour chq table mére $nomtableliee
	*		preparer_donnees_tpl($nomtableliee)
	*		Parcours  des lignes du Template
	*			Pour chaque zone de la table mére $nomtableliee
	*				Récupérer le nom du champ de la zone
	*				Associer au champ la valeur correspondante
	*	Parser le Template du théme
	*</pre>
	* @access public
	*/
	function remplir_template($template){
		$champs_donnees_tpl = array();

		$code_etablissement = $this->code_etablissement;
		$code_annee = $this->code_annee;
		$code_filtre = $this->code_filtre;
		//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
		${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']} = $code_annee;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;
		if(isset($_SESSION['code_regroupement']) && $_SESSION['code_regroupement'] <> ''){
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']} = $_SESSION['code_regroupement'];
		}
		//Fin ajout Hebie
		unset($this->keys_template);
		$result_interf = array() ; 
		//echo '<br> AVANT <br> '.$template.'<br>';
		//Ajout HEBIE pr desactiver la suppression d'un enseignant a partir de liste (grille): A voir s'il faut le faire seulement lorsqu'il ya tmis dans le $_GET
		if(isset($GLOBALS['PARAM']['TEACHERS_MANAGEMENT']) && $GLOBALS['PARAM']['TEACHERS_MANAGEMENT'] && in_array(''.$this->id_theme.$this->id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']))
			$disabled = ' disabled';
		else
			$disabled = ' ';
		//Fin ajout HEBIE pr desactiver la suppression d'un enseignant a partir de liste
		foreach($this->nomtableliee as $nomtableliee){
			$this->preparer_donnees_tpl($nomtableliee);
			
			//echo"$nomtableliee <br> <pre>";
			//print_r($this->matrice_donnees[$nomtableliee]);
			//echo"$nomtableliee - tpl<br> <pre>";
			//print_r($this->matrice_donnees_tpl[$nomtableliee]);
			if(is_array($this->matrice_donnees[$nomtableliee])){
			//echo 'ici <br>';	
				//echo"$nomtableliee - tpl<br> <pre>";
				//print_r($this->tableau_zone_saisie[$nomtableliee]); 

				for( $ligne=0; $ligne < $this->nb_lignes; $ligne++ ){  // Pour chq ligne du Template                
					$colonne_donnees = -1 ;
					foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){ // Pour chq Zone
						if($zone_saisie['type'] <> 'l') // On écarte le type LABEL dans les traitements
								$colonne_donnees++;
						//Hebie: Recuperation des codes de plage associés et mise en session
						if($nomtableliee == $GLOBALS['PARAM']['ETABLISSEMENT']){
							if (in_array('DICO_PLAGE_CODES_ASSOC',array_map('strtoupper',$GLOBALS['conn_dico']->MetaTables('TABLES')))){
								$req_plage_code_assoc 	= "SELECT NOM_CHAMP_ASSOC FROM DICO_PLAGE_CODES_ASSOC";
								$res_plage_code_assoc	= $GLOBALS['conn_dico']->GetAll($req_plage_code_assoc);
								if(is_array($res_plage_code_assoc) && count($res_plage_code_assoc)>0){
									$tab_plage_chp_assoc = array();
									foreach($res_plage_code_assoc as $chp_assoc){
										if(!in_array($chp_assoc['NOM_CHAMP_ASSOC'], $tab_plage_chp_assoc)){
											$tab_plage_chp_assoc[] = $chp_assoc['NOM_CHAMP_ASSOC'];
											if($zone_saisie['champ'] == $chp_assoc['NOM_CHAMP_ASSOC'] && isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) && $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees] <>''){
												$_SESSION['plage_codes_assoc'][$chp_assoc['NOM_CHAMP_ASSOC']] = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
											}
										}
									}
								}else{
									unset($_SESSION['plage_codes_assoc']);
								}
							}else{
								unset($_SESSION['plage_codes_assoc']);
							}
						}
						//Fin Hebie
						//Ajout Hebié : Recupération et mise en session du code etablissement parent
						if($zone_saisie['champ'] == $GLOBALS['PARAM']['NOM_ETABLISSEMENT'] && isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) && $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees] <>''){
							$chp_code_etab_parent_trouve = false;
							foreach($this->tableau_zone_saisie[$nomtableliee] as $zone_data){
								if($zone_data['champ']==$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']){
									$chp_code_etab_parent_trouve = true;
								}
							}
							if(!$chp_code_etab_parent_trouve)
								$_SESSION['code_etab_parent'] = $this->code_etablissement;
						}
						if($zone_saisie['champ'] == $GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] && isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) && $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees] <>''){
							$_SESSION['code_etab_parent'] = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
						}
						//Fin Ajout Hébie
						//Ajout Hebie pr changement du filtre en session
						//Old
						/*if($zone_saisie['champ'] == $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'] && isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) && is_int($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees])){
							$_SESSION['filtre'] = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
						}*/
						//Remodifier le 23 02 2018
						if($zone_saisie['champ'] == $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'] && isset($this->matrice_donnees[$nomtableliee][$ligne][$colonne_donnees]) && is_int($this->matrice_donnees[$nomtableliee][$ligne][$colonne_donnees])){
							$_SESSION['filtre'] = $this->matrice_donnees[$nomtableliee][$ligne][$colonne_donnees];
						}
						//Fin Ajout Hebie
						
						if( $zone_saisie['type']=='s' or $zone_saisie['type']=='csu' or $zone_saisie['type']=='sys_txt' or $zone_saisie['type']=='hidden_field' ){ // cas de Zone Texte
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees])){
								$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
								//$val_champ_base = addslashes($val_champ_base);
								${$nom_champ.'_'.$ligne} = ''.$val_champ_base.''; // Affectation variable Template par Valeur en Base
								$champs_donnees_tpl[$nom_champ.'_'.$ligne] = array($val_champ_base,"text");
								//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
							}
							else{
								//${$nom_champ.'_'.$ligne} = '\'\'';
								${$nom_champ.'_'.$ligne} = '';	
							}
						}
						elseif( $zone_saisie['type']=='ch' ){ // cas d'un Checkbox
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							//echo"<br>chp chk:$nom_champ=".$this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]."";
							if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) and ($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees] == '1') ) {
								//$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne];
								//$val_champ_base = addslashes($val_champ_base);
								${$nom_champ.'_'.$ligne} = "'1' CHECKED";
								$champs_donnees_tpl[$nom_champ.'_'.$ligne] = array('1', 'checkbox');
								//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
							}
							else if (!$this->is_from_mobile){
								//${$nom_champ.'_'.$ligne} = '\'\'';
								${$nom_champ.'_'.$ligne} = "'1'";	
								$champs_donnees_tpl[$nom_champ.'_'.$ligne] = array('0', 'checkbox');
							}
						}

						elseif( $zone_saisie['type']=='l'){ // Cas d'un LABEL
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							// Traitement de la requéte associée au LABEL
							$sql_temp	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql'];
							$sql_temp	=   str_replace('$ligne',"$ligne",$sql_temp);
							//echo"<br>$sql_temp<br>";
							$chaine_eval = "\$sql=\"$sql_temp\";";
					
							eval($chaine_eval);
							$sql_label = $sql;
							//echo"<br>***** $sql *****<br>";
							if (preg_match ("/#([^#]*)#/", $sql, $elem)){
								$v = $elem[1].'_'.$ligne;
								//echo"<br>\$$v=$v<br>";
								$sql_label = preg_replace("/#([^#]*)#/",$$v,$sql);
							}
							
							//echo"<br>$sql_label<br>";
							//${$nom_champ.'_'.$ligne} = '';
							if (trim($sql_label)<>''){	
								// Traitement Erreur Cas : Execute / GetOne
								try {            
										$rslabel	= $this->conn->execute($sql_label);
										if($rslabel ===false){                
												 //throw new Exception('ERR_SQL');   
										}
										${$nom_champ.'_'.$ligne} 	= 	$rslabel->fields[$this->get_champ_extract($nom_champ)];			
										$champs_donnees_tpl[$nom_champ.'_'.$ligne] = array($rslabel->fields[$this->get_champ_extract($nom_champ)], 'text');					 							 
								}
								catch (Exception $e) {
										 $erreur = new erreur_manager($e,$sql_label);
								}        
								// Fin Traitement Erreur Cas : Execute / GetOne								
								//else
									//${$nom_champ.'_'.$ligne} = '';
							}
							//else
								//${$nom_champ.'_'.$ligne} = '';
						}
						elseif( $zone_saisie['type']=='li_ch' ){ // Cas de liste Checkbox
							//
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
								
							$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
							//echo "$nom_matrice <br>";
							//echo"<br>NOM CHAMP=".$zone_saisie['champ']."->".$zone_saisie['type']."->".$this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]."<br>";
							//echo'<br>matrice_donnees_tpl - list checkbox <br><pre>';
							//print_r($this->matrice_donnees_tpl[$nomtableliee]);
							//$nb_lignes_ch = count($this->matrice_donnees_tpl[$nomtableliee]);
							if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
								//echo'$this->code_nomenclature[$nomtableliee][$nom_matrice] <br><pre>';
								//print_r($this->code_nomenclature[$nomtableliee][$nom_matrice] );
								foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
									// on parcours les codes des clé Ref : $val_matrice
									$checked = false ;
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice])){
										// Si $val_matrice est en Base
										$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice];
										if($val_matrice == $val_champ_base){
											// Cocher la case associée é $val_matrice
											${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' CHECKED';
											$checked = true ;
											$champs_donnees_tpl[$nom_champ.'_'.$ligne.'_'.$val_matrice] = array('1', 'checkbox');
											//echo '<br> A CHECKER <br>';
										}
									}
									if($checked == false &&  !$this->is_from_mobile){ // autrement on le coche pas
										${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\'';
										$champs_donnees_tpl[$nom_champ.'_'.$ligne.'_'.$val_matrice] = array('0', 'checkbox');
									}
								}
							}
						}
						elseif($zone_saisie['type']=='loc_etab'){ // cas du type de localisation Etab

								$tab_chaines = $this->get_chaines_loc($this->id_systeme);
								// bass on recupere les regroups rattachés é  l'etab
								if(is_array($this->matrice_donnees[$nomtableliee]) && count($this->matrice_donnees[$nomtableliee]) ){
										$_SESSION['rattachmts'] = array();
										$_SESSION['reg_loc_exist'] = array();
										foreach($this->matrice_donnees[$nomtableliee] as $iReg => $tab){
												$reg_loc 			= $tab[$colonne_donnees];
												$chaine_loc         = $this->get_chaine_reg($reg_loc);
												$iLoc 				= $this->get_iLoc_chaine($chaine_loc,$tab_chaines);
												
												${'LOC_REG_'.$iLoc} = $reg_loc;
												$champs_donnees_tpl['LOC_REG_'.$iLoc] = array($reg_loc, 'text');
												unset($_SESSION['reg_parents']);
												require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php'; 
												$chaine = $tab_chaines[$iLoc];
												$arbre 	= new arbre($chaine['numCh']);
												//arbre = new $arbre;
												$rattachements = $arbre->getparentsid( $chaine['nbRegs']-1 , ${'LOC_REG_'.$iLoc}, $chaine_loc );
												foreach( $rattachements as $i => $rattachement ){
														${'LIBELLE_REG_'.$iLoc.'_'.$i} = $rattachement[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
														$champs_donnees_tpl['LIBELLE_REG_'.$iLoc.'_'.$i] = array($rattachement[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], 'text');
														$_SESSION['rattachmts'][$chaine_loc][$i] = $rattachement[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
														
														$_SESSION['reg_loc_exist']['type'][$i] = $arbre->get_type_reg($rattachement[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]);
														$_SESSION['reg_loc_exist']['code'][$i] = $rattachement[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
												}
										}
								}
								// bass
						}
						elseif( ($zone_saisie['type']=='m') or ($zone_saisie['type']=='cmu') or ($zone_saisie['type']=='sco')
						or ($zone_saisie['type']=='b') or ($zone_saisie['type']=='co') ){
							// Cas d'une valeur é choisir é partir d'une liste
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
								
							$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
							//echo "//// $nom_matrice <br><pre>";
							//print_r($this->code_nomenclature[$nomtableliee][$nom_matrice]);
							//echo"<br>NOM CHAMP=".$zone_saisie['champ']."->".$zone_saisie['type']."->".$this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]."<br>";
														
							// Harmonisation Bass hebie
							
							if( isset($zone_saisie['DynContentObj']) && ($zone_saisie['DynContentObj']== 1) ){
								//echo "<pre>";
								//print_r($zone_saisie);
								//Gestion affichage du nom de l'etablissement dans le combo etab courant
								if($zone_saisie['champ']==$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees])){
										$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
									}
									
									// Pour une ligne du Template
									$val_school = '';
									$val_curr_school = '';
									$val_hierar_school = '';
									$col_donnees = -1 ;
									foreach($this->tableau_zone_saisie[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']] as $col => $zon_saisi){
										if($zon_saisi['type'] <> 'l') $col_donnees++;
										if($zon_saisi['champ']==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
											$val_school = $this->matrice_donnees_tpl[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']][$ligne][$col_donnees];
										}elseif($zon_saisi['champ']==$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
											$val_curr_school = $this->matrice_donnees_tpl[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']][$ligne][$col_donnees];
										}elseif($zon_saisi['champ']==$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT']){
											$val_hierar_school = $this->matrice_donnees_tpl[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']][$ligne][$col_donnees];
										}
									}
									if(!isset($_GET['tmis']) && (isset($_GET['ligne']) || isset($_GET['id_teacher']))){
										if(isset($_GET['id_teacher'])) $_SESSION['id_teacher'] =  $_GET['id_teacher'];
										if($val_school<>'' && $val_curr_school<>'' && $val_school<>$val_curr_school){
											$annee_courante = $_SESSION['annee'];
											$req_hierar_school = "SELECT ".$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT'].
																" FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].
																" WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']."=".$_SESSION['id_teacher'].
																" AND ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$val_school.
																" AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$annee_courante;
											$res_hierar_school = $this->conn->GetAll($req_hierar_school);
											$val_hierar_school = $res_hierar_school[0][$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT']];
											//echo $req_hierar_school;die;
										}
									}
									
									if(!count($result_interf[$zone_saisie['id_zone']])){
										$result_interf[$zone_saisie['id_zone']] = $this->requete_interface($zone_saisie['table_interface'], $zone_saisie['champ_interface'], $zone_saisie['champ'], $this->id_systeme, $zone_saisie['sql_interface'], $code_annee, $code_etablissement);
									}
									$result_interface	=	$result_interf[$zone_saisie['id_zone']] ;
									$html_ContentObj_transfer = '' ;
									if( is_array($result_interface) ){
										//$exist_etab = false;
										foreach($result_interface as $element_interf){
											if($element_interf[$this->get_champ_extract($zone_saisie['champ'])] == $val_champ_base){
												//$exist_etab = true;
											}
											$html_ContentObj_transfer .= "\t\t\t<OPTION VALUE=$".$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].">"; 
											$html_ContentObj_transfer .= $element_interf['LIBELLE']."</OPTION>\n"; 
										}
										
										if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) && $val_champ_base<>''
											&& $val_school<>'' && $val_curr_school<>'' && $val_school<>$val_curr_school && $val_hierar_school<>''){
												$html_ContentObj_transfer = "\t\t\t<OPTION VALUE='' SELECTED>"; 
												$html_ContentObj_transfer .= $val_hierar_school."</OPTION>\n";
										}elseif(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) && $val_champ_base<>''
											&& $val_school<>'' && $val_curr_school<>'' && $val_school<>$val_curr_school && $val_hierar_school==''){
												$html_ContentObj_transfer = "\t\t\t<OPTION VALUE='' SELECTED>"; 
												$html_ContentObj_transfer .= recherche_libelle_page('etab_transfert')."</OPTION>\n";
										}
									}
									$template = str_replace('<!--DynContentObj_'.$zone_saisie['id_zone'].'_'.$zone_saisie['champ'].'_'.$ligne.'-->', $html_ContentObj_transfer, $template); 
									
								}else{
									$html_ContentObj = '' ;
									if(!count($result_interf[$zone_saisie['id_zone']])){
										$result_interf[$zone_saisie['id_zone']] = $this->requete_interface($zone_saisie['table_interface'], $zone_saisie['champ_interface'], $zone_saisie['champ'], $this->id_systeme, $zone_saisie['sql_interface'], $code_annee, $code_etablissement);
									}
									$result_interface	=	$result_interf[$zone_saisie['id_zone']] ;
									if( is_array($result_interface) ){
										$code_etab_parent_trouve = false;
										if($zone_saisie['champ'] == $GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']){
											foreach($result_interface as $element_interf){
												if(isset($_SESSION['code_etab_parent']) && $_SESSION['code_etab_parent'] == $element_interf[$this->get_champ_extract($zone_saisie['champ'])]){
													$html_ContentObj .= "\t\t\t<OPTION VALUE=$".$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])]." selected='selected'>"; 
													$html_ContentObj .= $element_interf['LIBELLE']."</OPTION>\n";
													$code_etab_parent_trouve = true; 
													break;
												}
											}
										}
										if(!$code_etab_parent_trouve){
											foreach($result_interface as $element_interf){
												$html_ContentObj .= "\t\t\t<OPTION VALUE=$".$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].">"; 
												$html_ContentObj .= $element_interf['LIBELLE']."</OPTION>\n"; 
											}
										}
									}
									//echo  $html_ContentObj ; die();
									//if(ereg('<!--DynContentObj_'.$zone_saisie['id_zone'].'_'.$zone_saisie['champ'].'_'.$ligne.'-->',  $template)){
										$template = str_replace('<!--DynContentObj_'.$zone_saisie['id_zone'].'_'.$zone_saisie['champ'].'_'.$ligne.'-->', $html_ContentObj, $template); 
									//}
									//echo '<pre>'.$zone_saisie['sql'].'<br><br>'; print_r($this->code_nomenclature[$nomtableliee][$nom_matrice]); die();
								}
							}
							
							if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees])){
								$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
								
								//echo "<br>$nom_champ=$val_champ_base<br>";
								if(isset($this->code_nomenclature[$nomtableliee][$nom_matrice])){
									if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) and $this->code_nomenclature[$nomtableliee][$nom_matrice] ){
										foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){ 
											// pour chq valeur code nomenclature
											if($val_matrice == $val_champ_base){
												//echo "<br>$nom_champ=$val_champ_base<br>";
												// Si le code == valeur en Base	: il faut le cocher									
												//$val_champ_base = addslashes($val_champ_base);
												//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' CHECKED';
												if($zone_saisie['type']=='co' or $zone_saisie['type']=='sco'){
													${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' SELECTED';
													$champs_donnees_tpl[$nom_champ.'_'.$ligne] = array($nom_champ.'_'.$ligne.'_'.$val_matrice,'select');
												}
												else {
													${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' CHECKED';
													$champs_donnees_tpl[$nom_champ.'_'.$ligne] = array($nom_champ.'_'.$ligne.'_'.$val_matrice,'radio');
												}
											}
											else{ // Sinon le code <> valeur en Base	: ne pas cocher
												//$val_champ_base = addslashes($val_champ_base);
												${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\'';
											}
										}
									}
									else{
										//$val_champ_base = addslashes($val_champ_base);
										${$nom_champ.'_'.$ligne} = ''.$val_champ_base.'';
										$champs_donnees_tpl[$nom_champ.'_'.$ligne] = array($val_champ_base,$type_champ);
									}
								}
								else{
									//$val_champ_base = addslashes($val_champ_base);
									${$nom_champ.'_'.$ligne} = ''.$val_champ_base.'';
									$champs_donnees_tpl[$nom_champ.'_'.$ligne] = array($val_champ_base,$type_champ);
									//echo '<br>type mat :'.$nom_champ.'_'.$ligne.'='.$val_champ_base.'<br>';
								}
							}
							else{
								if(isset($this->code_nomenclature[$nomtableliee][$nom_matrice])){
									if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice])){
										foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
											${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\'';
										}
									}
								}
							}
						}
						elseif($zone_saisie['type']=='e' or $zone_saisie['type']=='tvm'){
							// type 'e' ou 'tvm' : structure matricielle
							//echo'<pre>';
							//print_r($this->matrice_donnees_tpl[$nomtableliee][$ligne]);
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(!is_array($nom_champ)){
								$tab_nom_champ = array($nom_champ);
							}
							else
								$tab_nom_champ = $nom_champ;
								
							$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
							///echo"<br>nom_matrice = $nom_matrice <br><pre>";
							//print_r($this->code_nomenclature[$nomtableliee][$nom_matrice]);                            
							if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne])){
								//echo'<pre>$this->matrice_donnees_tpl[$nomtableliee][$ligne]';
								//print_r($this->matrice_donnees_tpl[$nomtableliee][$ligne]);                            
								foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
									// Pour chq code du champ ref
									//echo "<br>val mat = $val_matrice<br>";
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice])){
										// S'il y' a une valeur en base, la récupérer dans la variable du Template
										//echo'<pre>$this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice]';
										$val_champs_mat = array();
										foreach($this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice] as $val){
											$val_champs_mat[] = $val;
										}
																				
										foreach($tab_nom_champ as $ord=>$nom_champ){
											$val_champ_base = $val_champs_mat[$ord];
											//$val_champ_base = addslashes($val_champ_base);
											//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$val_champ_base.'\'';
											//echo "$nom_champ.'_'.$ligne.'_'.$val_matrice = ''.$val_champ_base.''";
											${$nom_champ.'_'.$ligne.'_'.$val_matrice} = ''.$val_champ_base.'';
											$champs_donnees_tpl[$nom_champ.'_'.$ligne.'_'.$val_matrice] = array($val_champ_base,'text');
										}
									}
									else{
										foreach($tab_nom_champ as $ord=>$nom_champ){
											//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\'\'';
											${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '';
										}
									}
								}	
							}	
						}
						elseif($zone_saisie['type']=='zone_mat'){
							// type 'e' ou 'tvm' : structure matricielle
							//echo'<pre>';
							//print_r($this->matrice_donnees_tpl[$nomtableliee][$ligne]);
							
							if( count($tab_dims = $this->get_dims_zone_matricielle($nomtableliee,'')) ){
								
								$tab_codes_dims = $this->do_concat_dimensions_codes($tab_dims['codes_dims']);
								
								$nom_matrice 	=	$zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'];
								$nom_champ		=	$zone_saisie['champ'];
								if(is_array($tab_codes_dims)){			
									foreach($tab_codes_dims as $val_matrice){                          
										// Pour chq code du champ ref
										//echo "<br>val mat = $val_matrice<br>";
										if( isset($zone_saisie['DynContentObj']) && ($zone_saisie['DynContentObj']== 1) ){
											$html_ContentObj = '' ;
											if(!count($result_interf[$zone_saisie['id_zone']])){
												$result_interf[$zone_saisie['id_zone']] = $this->requete_interface($zone_saisie['table_interface'], $zone_saisie['champ_interface'], $zone_saisie['champ'], $this->id_systeme, $zone_saisie['sql_interface'], $code_annee, $code_etablissement);
											}
											$result_interface	=	$result_interf[$zone_saisie['id_zone']] ;
											if( is_array($result_interface) ){
												foreach($result_interface as $element_interf){
													$html_ContentObj .= "\t\t\t<OPTION VALUE=$".$zone_saisie['champ']."_".$ligne."_".$val_matrice.'_'.$element_interf[$this->get_champ_extract($zone_saisie['champ'])].">"; 
													$html_ContentObj .= $element_interf['LIBELLE']."</OPTION>\n"; 
												}
											}
											//echo  $html_ContentObj ; die();
											//if(ereg('<!--DynContentObj_'.$zone_saisie['id_zone'].'_'.$zone_saisie['champ'].'_'.$ligne.'-->',  $template)){
												$template = str_replace('<!--DynContentObj_'.$zone_saisie['id_zone'].'_'.$zone_saisie['champ'].'_'.$ligne."_".$val_matrice.'-->', $html_ContentObj, $template); 
											//}
											//echo '<pre>'.$zone_saisie['sql'].'<br><br>'; print_r($this->code_nomenclature[$nomtableliee][$nom_matrice]); die();
										}
										
										if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice][$colonne])){
											// S'il y' a une valeur en base, la récupérer dans la variable du Template
											//echo'<pre> val_matrice_'.$val_matrice.' = '.$this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice];
											$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice][$colonne];                                        
											//$val_champ_base = addslashes($val_champ_base);
											//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$val_champ_base.'\'';
											//echo '$'.$nom_champ.'_'.$ligne.'_'.$val_matrice .' = '.$val_champ_base.'<br>';
											if($zone_saisie['ss_type']=='s' or $zone_saisie['ss_type']=='hidden_field'){
												////////////////////////// ZONE MTRICIELLE DE TYPE TEXT
												${$nom_champ.'_'.$ligne.'_'.$val_matrice} = ''.$val_champ_base.'';
												$champs_donnees_tpl[$nom_champ.'_'.$ligne.'_'.$val_matrice] = array($val_champ_base,'text');
												///////////////////////////	FIN ZONE MTRICIELLE DE TYPE TEXT
											}elseif($zone_saisie['ss_type']=='ch'){
												////////////////////////// ZONE MTRICIELLE DE TYPE CHECKBOX
												//$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne];
												//$val_champ_base = addslashes($val_champ_base);
												${$nom_champ.'_'.$ligne.'_'.$val_matrice} = "'1' CHECKED";
												$champs_donnees_tpl[$nom_champ.'_'.$ligne.'_'.$val_matrice] = array('1', 'checkbox');
												//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
												///////////////////////////	FIN ZONE MTRICIELLE DE TYPE CHECKBOX
											}elseif( ($zone_saisie['ss_type']=='m') or ($zone_saisie['ss_type']=='b') or ($zone_saisie['ss_type']=='co') ){
												////////////////////////// ZONE MTRICIELLE DE TYPE COMBO, BOOLEEN, LISTE RADIO
												//echo "//// $nom_matrice <br><pre>";
												//print_r($this->code_nomenclature[$nomtableliee][$nom_matrice]);
												//echo"<br>NOM CHAMP=".$zone_saisie['champ']."->".$zone_saisie['type']."->".$this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]."<br>";
												//echo "<br>$nom_champ=$val_champ_base<br>";
												if( isset($this->code_nomenclature[$nomtableliee][$nom_matrice]) && is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
													foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_code_sel){
														// pour chq valeur code nomenclature
														if($val_code_sel == $val_champ_base){
															//echo "<br>$nom_champ=$val_champ_base<br>";
															// Si le code == valeur en Base	: il faut le cocher									
															//$val_champ_base = addslashes($val_champ_base);
															//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' CHECKED';
															if($zone_saisie['ss_type']=='co' || $zone_saisie['ss_type']=='sco'){
																${$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel.'\''.' SELECTED';
																$champs_donnees_tpl[$nom_champ.'_'.$ligne.'_'.$val_matrice] = array($nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel, 'select');
															}
															else{
																${$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel.'\''.' CHECKED';
																$champs_donnees_tpl[$nom_champ.'_'.$ligne.'_'.$val_matrice] = array($nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel, 'radio');
															}
														}
														else{ // Sinon le code <> valeur en Base	: ne pas cocher
															//$val_champ_base = addslashes($val_champ_base);
															${$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel.'\'';
														}
													}
												}
												else{
													//$val_champ_base = addslashes($val_champ_base);
													${$nom_champ.'_'.$ligne.'_'.$val_matrice} = ''.$val_champ_base.'';
													$champs_donnees_tpl[$nom_champ.'_'.$ligne.'_'.$val_matrice] =  array($val_champ_base,($zone_saisie['ss_type']=='co' || $zone_saisie['ss_type']=='sco')?'select':'radio');
												}
												///////////////////////////	FIN ZONE MTRICIELLE DE TYPE COMBO, BOOLEEN, LISTE RADIO
											}
										}
										else{
											if($zone_saisie['ss_type']=='ch'){
												${$nom_champ.'_'.$ligne.'_'.$val_matrice} = "'1'";
												$champs_donnees_tpl[$nom_champ.'_'.$ligne.'_'.$val_matrice] =  array('0','checkbox'); 
											}elseif( ($zone_saisie['ss_type']=='m') or ($zone_saisie['ss_type']=='b') or ($zone_saisie['ss_type']=='co') ){
												if( isset($this->code_nomenclature[$nomtableliee][$nom_matrice]) && is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
													foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_code_sel){
														${$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel.'\'';
													}
												}
											}
										}
									}
								}
							}	
						}
						else{ // sinon autre Type de zone non encore spécifié
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne])){ // Si une valeur en Base
								$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
								//$val_champ_base = addslashes($val_champ_base);
								$var	=	$nom_champ.'_'.$ligne;
								//echo"<br>$nomtableliee \$$var = {$$var}<br>";
								if(!isset($$var)){
									${$nom_champ.'_'.$ligne} = ''.$val_champ_base.''; // récupérer la valeur en base
									//echo"<br>$nomtableliee\$$nom_champ _ $ligne = $val_champ_base<br>";
								}
								//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
							}
							else{
								//${$nom_champ.'_'.$ligne} = '\'\'';
								${$nom_champ.'_'.$ligne} = '';	
							}
						}
						
						//Gestion de la saisie de plusieurs themes bases sur la meme table matricielle avec des colonnes differentes
						//Pour GrilEff1 fix colonne
						if(($this->type_theme==4) && ($this->type_gril_eff_fix_col) && ($zone_saisie['type']=='c' || $zone_saisie['type']=='dim_col')){
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							//Recuperation des cles des donnees bd dont l'affichage est prevu dans le template
							foreach ($this->matrice_donnees[$nomtableliee] as $element){
								$keys_data = $this->get_tab_all_keys_val($nomtableliee, $element);
								foreach($keys_data as $i_c_z => $val){
									$i_c_d = $this->col_zone_Equi_col_data[$nomtableliee]['zone'][$i_c_z];
									if($this->champs[$nomtableliee][$i_c_d]==$nom_champ && preg_match("/'".$nom_champ."_".$ligne."_".$val."'/",$template)){
										$this->keys_template[$nomtableliee][] = $keys_data;
										break;
									}
								}
							}
							//Fin Recuperation des cles des donnees bd dont l'affichage est prevu dans le template
						}
						//Fin Gestion de la saisie de plusieurs themes bases sur la meme table matricielle avec des colonnes differentes
												
						//// traitement d'un champ lié é une interface de saisie
						if(isset($zone_saisie['champ_interface']) and ($zone_saisie['champ_interface']) ){
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ_interface'];
								if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
								$sql_temp	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql_interface'];
							//$sql_temp		=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql'];
								$sql_temp	= str_replace('$ligne',"$ligne",$sql_temp);
								
								
								$chaine_eval = "\$sql=\"$sql_temp\";";
								//echo"<br>$chaine_eval<br>";
								eval($chaine_eval);
								$sql_label = $sql;
								
								if (preg_match ("/#([^#]*)#/", $sql, $elem)){
										$v = $elem[1].'_'.$ligne;
										//echo '<br>$'.$v .'= '.$$v;
										$sql_label 	= preg_replace("/#([^#]*)#/",$$v,$sql);
								}

								//echo"<br>$sql_label<br>";
								if (!is_null($sql_label) and (trim($sql_label) <> '') ){	
										//echo"<br>$sql_label<br>";
										// Traitement Erreur Cas : Execute / GetOne
										try {            
												$rslabel	= $this->conn->execute($sql_label);
												if($rslabel ===false){                
														 //throw new Exception('ERR_SQL');   
												}else{
													${$nom_champ.'_'.$ligne} 	= 	$rslabel->fields[$this->get_champ_extract($nom_champ)];	
													//echo "<br><br> $sql_label";
													//echo '<br>$'.$nom_champ.'_'.$ligne  .'= '.$rslabel->fields[$this->get_champ_extract($nom_champ)];						 
												}
										}
										catch (Exception $e) {
												 $erreur = new erreur_manager($e,$sql_label);
										}        
										// Fin Traitement Erreur Cas : Execute / GetOne										
								}
						}
						///////////////////////////////
						//// Placement du Javascript pour les interface de types saisie
						if(isset($zone_saisie['sql_saisie']) and ($zone_saisie['sql_saisie']) and !$affiche_js_saisie ){
								$affiche_js_saisie = 1;
								$js_saisie.="\t<script language='javascript' type='text/javascript'>\n";
								$js_saisie.="\t\t<!--\n";
								$js_saisie.="\t\t function SAISIE_".$zone_saisie['champ']."(nom_champ_saisie, nom_champ_assoc){\n";
								$js_saisie.="\t\t\t var tab_assoc  = new Array();\n";
								$js_saisie.="\t\t\t var tab_saisie	= new Array();\n";
								$sql_saisie = $zone_saisie['sql_saisie'];
								$chaine_eval ="\$req=\"$sql_saisie\";";
								//echo $chaine_eval;
								$code_etablissement		=	$this->code_etablissement ; 
								$code_annee						=	$this->code_annee ;
								$code_filtre						=	$this->code_filtre ;
								//$id_systeme					= $GLOBALS['id_systeme'] ;

								eval ($chaine_eval);
								//echo $req;

								// Traitement Erreur Cas : Execute / GetOne
								try {            
										$rs_saisie	= $this->conn->Execute($req);
										if($rs_saisie ===false){                
												 throw new Exception('ERR_SQL');   
										}
										$i = -1;
										while (!$rs_saisie->EOF){
												$i++;
												$val_saisie		=	$rs_saisie->fields[$this->get_champ_extract($zone_saisie['champ_interface'])];
												$val_assoc		=	$rs_saisie->fields[$this->get_champ_extract($zone_saisie['champ'])];
												$js_saisie.="\t\t\t tab_saisie[$i] = '$val_saisie';\n";
												$js_saisie.="\t\t\t tab_assoc[$i] 	= '$val_assoc';\n";
												$rs_saisie->MoveNext();
										}
										
										$js_saisie.="\t\t\t var chaine_eval_1='var valeur_saisie=document.form1.'+nom_champ_saisie+'.value';\n";
										$js_saisie.="\t\t\t eval(chaine_eval_1);\n";
												
										$js_saisie.="\t\t\t for(j=0; j<=$i; j++){\n";
										$js_saisie.="\t\t\t\t if( (valeur_saisie!='') && (tab_saisie[j]==valeur_saisie) ){\n";
										$js_saisie.="\t\t\t\t\t var chaine_eval_2='document.form1.'+nom_champ_assoc+'.value=tab_assoc[j]';\n";
										$js_saisie.="\t\t\t\t\t eval(chaine_eval_2);\n";
										$js_saisie.="\t\t\t\t }\n";
										$js_saisie.="\t\t\t }\n";
										$js_saisie.="\t\t }\n";
										$js_saisie.="\t\t -->\n";
										$js_saisie.="\t </script>\n";
										
										print($js_saisie);																		 
								}
								catch (Exception $e) {
										 $erreur = new erreur_manager($e,$req);
								}        
								// Fin Traitement Erreur Cas : Execute / GetOne
						}
						///////////////////////////////
					}
				}
			}
		}
		
	
		// Configuration de la barre de navigation
		//configurer_barre_nav($this->nb_lignes);
		//return eval("echo \"$template\";");
		//\"; system('echo 'truc' > c:\test.txt'); echo \"
		// Affichage de la barre de navigation
		//afficher_barre_nav(true,true);

		//die($template);
		
		//$result = sprintf("%s", $template);
		
		if(count($result_interf)){
			$this->template = $template ;
		}
		unset($result_interf);
		unset($result_interface);
		$this->tab_donnees_tpl = $champs_donnees_tpl;
		//echo '<br> APRES <br> '.$template.'<br>'; die();
		$code = '$result = sprintf("%s", "'.str_replace('"','\"',$template).'");';
		//die($code);
		eval($code);
		return $result;
		//eval ("\$eval_template =addslashes($template);");
		//return eval("print ($eval_template);");
	}	
	
	/**
	* METHODE :  remplir_table_html ( table_html )
	* Permet de données des valeurs aux variables du table_html
	* et de retourner le tableau HTML .
	*<pre>
	*	-->	Pour chq table mére $nomtableliee
	*		preparer_donnees_tpl($nomtableliee)
	*		Parcours  des lignes du Template
	*			Pour chaque zone de la table mére $nomtableliee
	*				Récupérer le nom du champ de la zone
	*				Associer au champ la valeur correspondante
	*	Parser le Template du théme
	*</pre>
	* @access public
	*/
	function remplir_table_html($table_html, $annee){
		//$_SESSION["table_html"] = 'labas';
		$code_etablissement = $this->code_etablissement;
		$code_annee = $this->code_annee;
		$code_filtre = $this->code_filtre;
		//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
		${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']} = $code_annee;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;
		//Fin ajout Hebie
		unset($this->keys_template);
		$result_interf = array() ; 
		$suffix_ann = '';
		if(!is_array($annee) && $annee<>'') $annee = array($annee);
		elseif(!is_array($annee) && $annee=='') $annee = array(0);
		if(is_array($annee)){
			foreach($annee as $ann){
				if($ann<>0){
					$suffix_ann = "_ANN_".$ann;
					$code_annee = $ann;
				}
				//echo '<br> AVANT <br> '.$table_html.'<br>';
				
				foreach($this->nomtableliee as $nomtableliee){
					if($ann<>0){
						$this->matrice_donnees[$nomtableliee] = $_SESSION['matrice_donnees']['ANN_'.$ann][$nomtableliee];
						$this->matrice_donnees_tpl[$nomtableliee] = $_SESSION['matrice_donnees_tpl']['ANN_'.$ann][$nomtableliee];
					}else{
						$this->preparer_donnees_tpl($nomtableliee);
					}
					
					/*echo"$nomtableliee <br> <pre>";
					print_r($this->matrice_donnees[$nomtableliee]);
					echo"$nomtableliee - tpl<br> <pre>";
					print_r($this->matrice_donnees_tpl[$nomtableliee]);*/
					
					if(is_array($this->matrice_donnees[$nomtableliee])){
					//echo 'ici <br>';	
						//echo"$nomtableliee - tpl<br> <pre>";
						//print_r($this->tableau_zone_saisie[$nomtableliee]); 
							 
						for( $ligne=0; $ligne < $this->nb_lignes; $ligne++ ){  // Pour chq ligne du Template                
							$colonne_donnees = -1 ;
							foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){ // Pour chq Zone
								if($zone_saisie['type'] <> 'l') // On écarte le type LABEL dans les traitements
										$colonne_donnees++;
										
								if( $zone_saisie['type']=='s' or $zone_saisie['type']=='csu' or $zone_saisie['type']=='sys_txt' ){ // cas de Zone Texte
									$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
									if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees])){
										$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
										//$val_champ_base = addslashes($val_champ_base);
										${$nom_champ.'_'.$ligne.$suffix_ann} = ''.$val_champ_base.''; // Affectation variable Template par Valeur en Base
										//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
									}
									else{
										//${$nom_champ.'_'.$ligne} = '\'\'';
										${$nom_champ.'_'.$ligne.$suffix_ann} = '';	
									}
								}
								elseif( $zone_saisie['type']=='ch' ){ // cas d'un Checkbox
									$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
									if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
									//echo"<br>chp chk:$nom_champ=".$this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]."";
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) and ($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees] == '1') ) {
										//$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne];
										//$val_champ_base = addslashes($val_champ_base);
										${$nom_champ.'_'.$ligne.$suffix_ann} = recherche_libelle_page('checked_box');
										//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
									}
									else{
										//${$nom_champ.'_'.$ligne} = '\'\'';
										${$nom_champ.'_'.$ligne.$suffix_ann} = "";	
									}
								}
		
								elseif( $zone_saisie['type']=='l'){ // Cas d'un LABEL
									$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
									
									if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
									// Traitement de la requéte associée au LABEL
									$sql_temp	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql'];
									$sql_temp	=   str_replace('$ligne',"$ligne",$sql_temp);
									//echo"<br>$sql_temp<br>";
									$chaine_eval = "\$sql=\"$sql_temp\";";
							
									eval($chaine_eval);
									$sql_label = $sql;
									//echo"<br>***** $sql *****<br>";
									if (preg_match ("/#([^#]*)#/", $sql, $elem)){
										$v = $elem[1].'_'.$ligne;
										//echo"<br>\$$v=$v<br>";
										$sql_label = preg_replace("/#([^#]*)#/",$$v,$sql);
									}
									
									//echo"<br>$sql_label<br>";
									//${$nom_champ.'_'.$ligne} = '';
									if (trim($sql_label)<>''){	
										// Traitement Erreur Cas : Execute / GetOne
										try {            
												$rslabel	= $this->conn->execute($sql_label);
												if($rslabel ===false){                
														 //throw new Exception('ERR_SQL');   
												}
												${$nom_champ.'_'.$ligne.$suffix_ann} 	= 	$rslabel->fields[$this->get_champ_extract($nom_champ)];								 
										}
										catch (Exception $e) {
												 $erreur = new erreur_manager($e,$sql_label);
										}        
										// Fin Traitement Erreur Cas : Execute / GetOne								
										//else
											//${$nom_champ.'_'.$ligne} = '';
									}
									//else
										//${$nom_champ.'_'.$ligne} = '';
								}
								elseif( $zone_saisie['type']=='li_ch' ){ // Cas de liste Checkbox
									//
									$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
									
									if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
										
									$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
									//echo "$nom_matrice <br>";
									//echo"<br>NOM CHAMP=".$zone_saisie['champ']."->".$zone_saisie['type']."->".$this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]."<br>";
									//echo'<br>matrice_donnees_tpl - list checkbox <br><pre>';
									//print_r($this->matrice_donnees_tpl[$nomtableliee]);
									//$nb_lignes_ch = count($this->matrice_donnees_tpl[$nomtableliee]);
									if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
										//echo'$this->code_nomenclature[$nomtableliee][$nom_matrice] <br><pre>';
										//print_r($this->code_nomenclature[$nomtableliee][$nom_matrice] );
										foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
											// on parcours les codes des clé Ref : $val_matrice
											$checked = false ;
											if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice])){
												// Si $val_matrice est en Base
												$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice];
												if($val_matrice == $val_champ_base){
													// Cocher la case associée é $val_matrice
													${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = recherche_libelle_page('checked_box');
													$checked = true ;
													//echo '<br> A CHECKER <br>';
												}
											}
											if($checked == false){ // autrement on le coche pas
												${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = '';
											}
										}
									}
								}
								elseif($zone_saisie['type']=='loc_etab'){ // cas du type de localisation Etab
		
										$tab_chaines = $this->get_chaines_loc($this->id_systeme);
										// bass on recupere les regroups rattachés é  l'etab
										if(is_array($this->matrice_donnees[$nomtableliee]) && count($this->matrice_donnees[$nomtableliee]) ){
												$_SESSION['rattachmts'] = array();
												foreach($this->matrice_donnees[$nomtableliee] as $iReg => $tab){
														$reg_loc 			= $tab[$colonne_donnees];
														$chaine_loc         = $this->get_chaine_reg($reg_loc);
														$iLoc 				= $this->get_iLoc_chaine($chaine_loc,$tab_chaines);
														
														${'LOC_REG_'.$iLoc} = $reg_loc;
														unset($_SESSION['reg_parents']);
														require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php'; 
														$chaine = $tab_chaines[$iLoc];
														$arbre 	= new arbre($chaine['numCh']);
														//arbre = new $arbre;
														$rattachements = $arbre->getparentsid( $chaine['nbRegs']-1 , ${'LOC_REG_'.$iLoc}, $chaine_loc );
														//echo '<pre>';
														//print_r($rattachements);
														foreach( $rattachements as $i => $rattachement ){
																${'LIBELLE_REG_'.$iLoc.'_'.$i} = $rattachement[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
																$_SESSION['rattachmts'][$chaine_loc][$i] = $rattachement[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
														}
												}
										}
										// bass
										
										///////////////////////
								}
								elseif( ($zone_saisie['type']=='m') or ($zone_saisie['type']=='cmu') or ($zone_saisie['type']=='sco')
								or ($zone_saisie['type']=='b') or ($zone_saisie['type']=='co') ){
									// Cas d'une valeur é choisir é partir d'une liste
									$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
									
									if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
										
									$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
									//echo "//// $nom_matrice <br><pre>";
									//print_r($this->code_nomenclature[$nomtableliee][$nom_matrice]);
									//echo"<br>NOM CHAMP=".$zone_saisie['champ']."->".$zone_saisie['type']."->".$this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]."<br>";
									
									// Harmonisation Bass hebie
									//Gestion affichage du nom de l'etablissement dans le combo etab courant
									if( isset($zone_saisie['DynContentObj']) && ($zone_saisie['DynContentObj']== 1) ){
										if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees])){
											$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
											if(!count($result_interf[$zone_saisie['id_zone']])){
												$result_interf[$zone_saisie['id_zone']] = $this->requete_interface($zone_saisie['table_interface'], $zone_saisie['champ_interface'], $zone_saisie['champ'], $this->id_systeme, $zone_saisie['sql_interface'], $code_annee, $code_etablissement);
											}
											$result_interface	=	$result_interf[$zone_saisie['id_zone']] ;
											
											if($zone_saisie['champ']==$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
												
												if(is_array($this->matrice_donnees[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']])){
													// Pour une ligne du Template                
													$val_school = '';
													$val_curr_school = '';
													$val_hierar_school = '';
													$col_donnees = -1 ;
													foreach($this->tableau_zone_saisie[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']] as $col => $zon_saisi){
														if($zon_saisi['type'] <> 'l') $col_donnees++;
														if($zon_saisi['champ']==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
															$val_school = $this->matrice_donnees_tpl[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']][$ligne][$col_donnees];
														}elseif($zon_saisi['champ']==$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
															$val_curr_school = $this->matrice_donnees_tpl[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']][$ligne][$col_donnees];
														}elseif($zon_saisi['champ']==$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT']){
															$val_hierar_school = $this->matrice_donnees_tpl[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']][$ligne][$col_donnees];
														}elseif($zon_saisi['champ']==$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']){
															$_SESSION['id_teacher'] = $this->matrice_donnees_tpl[$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']][$ligne][$col_donnees];
														}
													}
													if($val_school<>'' && $val_curr_school<>'' && $val_school<>$val_curr_school){
														($ann==0) ? ($annee_courante = $_SESSION['annee']) : ($annee_courante = $ann);
														$req_hierar_school = "SELECT ".$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT'].
																			" FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].
																			" WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']."=".$_SESSION['id_teacher'].
																			" AND ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$val_school.
																			" AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$annee_courante;
														$res_hierar_school = $this->conn->GetAll($req_hierar_school);
														$val_hierar_school = $res_hierar_school[0][$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT']];
														//echo $req_hierar_school;die;
													}
												}

												$html_ContentObj_transfer = '' ;
												if( is_array($result_interface) ){
													//$exist_etab = false;
													foreach($result_interface as $element_interf){
														if($element_interf[$this->get_champ_extract($zone_saisie['champ'])] == $val_champ_base){
															//$exist_etab = true;
															$html_ContentObj_transfer .= ${$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].$suffix_ann} = $element_interf['LIBELLE'];
														}else{
															$html_ContentObj_transfer .= ${$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].$suffix_ann} = '';
														}
													}
													if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) && $val_champ_base<>''
														&& $val_school<>'' && $val_curr_school<>'' && $val_school<>$val_curr_school && $val_hierar_school<>''){
														$html_ContentObj_transfer = ${$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].$suffix_ann} = $val_hierar_school;
													}elseif(!$exist_etab && isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees]) && $val_champ_base<>''
														&& $val_school<>'' && $val_curr_school<>'' && $val_school<>$val_curr_school && $val_hierar_school==''){
														$html_ContentObj_transfer = ${$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].$suffix_ann} = recherche_libelle_page('etab_transfert');
													}
												}else{
													$html_ContentObj_transfer .= ${$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].$suffix_ann} = '';
												}
												$table_html = str_replace('<!--DynContentObj_'.$zone_saisie['id_zone'].'_'.$zone_saisie['champ'].'_'.$ligne.$suffix_ann.'-->', $html_ContentObj_transfer, $table_html); 
												
												
												
											}else{
												$html_ContentObj = '' ;
												//print_r($result_interface); die('ICI');
												if( is_array($result_interface) ){
													foreach($result_interface as $element_interf){
														if($element_interf[$this->get_champ_extract($zone_saisie['champ'])]==$val_champ_base){
															if($zone_saisie['type']=='co' or $zone_saisie['type']=='sco'){
																
																$html_ContentObj .= ${$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].$suffix_ann} = $element_interf['LIBELLE'];
															}
															else
																$html_ContentObj .= ${$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].$suffix_ann} = recherche_libelle_page('checked_box');
														}else{
															$html_ContentObj .= ${$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].$suffix_ann} = '';
														}
													}
												}else{
													$html_ContentObj .= ${$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].$suffix_ann} = '';
												}
												//echo  $html_ContentObj ; die();
												//if(ereg('<!--DynContentObj_'.$zone_saisie['id_zone'].'_'.$zone_saisie['champ'].'_'.$ligne.'-->',  $table_html)){
													$table_html = str_replace('<!--DynContentObj_'.$zone_saisie['id_zone'].'_'.$zone_saisie['champ'].'_'.$ligne.$suffix_ann.'-->', $html_ContentObj, $table_html); 
												//}
												//echo '<pre>'.$zone_saisie['sql'].'<br><br>'; print_r($this->code_nomenclature[$nomtableliee][$nom_matrice]); die();
											}
										}
										$result_nomenc	= $this->requete_interface($zone_saisie['table_interface'], $zone_saisie['champ_interface'], $zone_saisie['champ'], $this->id_systeme, $zone_saisie['sql_interface'], $code_annee, $code_etablissement);
									}else{
										$result_nomenc	= $this->requete_nomenclature($zone_saisie['table_ref'], $GLOBALS['PARAM']['CODE'].'_'.$zone_saisie['table_ref'], $nom_champ, $_SESSION['langue'] ,$this->id_systeme, $zone_saisie['sql']) ;
									}
																	
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees])){
										$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
										
										//echo "<br>$nom_champ=$val_champ_base<br>";
										if(isset($this->code_nomenclature[$nomtableliee][$nom_matrice])){
											if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) and $this->code_nomenclature[$nomtableliee][$nom_matrice] ){
												foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){ 
													// pour chq valeur code nomenclature
													if($val_matrice == $val_champ_base){
														//echo "<br>$nom_champ=$val_champ_base<br>";
														// Si le code == valeur en Base	: il faut le cocher									
														//$val_champ_base = addslashes($val_champ_base);
														//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' CHECKED';
														if(is_array($result_nomenc))
														foreach($result_nomenc as $rs_nomenc){
															if($rs_nomenc[$nom_champ]==$val_matrice){
																$lib_nomenc = $rs_nomenc['LIBELLE'];
																break;
															}
														}
														if($zone_saisie['type']=='co' or $zone_saisie['type']=='sco'){
															${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = $lib_nomenc;
														}
														else
															${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = recherche_libelle_page('checked_box');
													}
													else{ // Sinon le code <> valeur en Base	: ne pas cocher
														//$val_champ_base = addslashes($val_champ_base);
														${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = '';
													}
												}
											}
											else{
												//$val_champ_base = addslashes($val_champ_base);
												${$nom_champ.'_'.$ligne.$suffix_ann} = ''.$val_champ_base.'';
											}
										}
										else{
											//$val_champ_base = addslashes($val_champ_base);
											${$nom_champ.'_'.$ligne.$suffix_ann} = ''.$val_champ_base.'';
											//echo '<br>type mat :'.$nom_champ.'_'.$ligne.'='.$val_champ_base.'<br>';
										}
									}
									else{
										if(isset($this->code_nomenclature[$nomtableliee][$nom_matrice])){
											if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice])){
												foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
													${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = '';
												}
											}
										}
									}
								}
								elseif($zone_saisie['type']=='e' or $zone_saisie['type']=='tvm'){
									// type 'e' ou 'tvm' : structure matricielle
									//echo'<pre>';
									//print_r($this->matrice_donnees_tpl[$nomtableliee][$ligne]);
									$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
									if(!is_array($nom_champ)){
										$tab_nom_champ = array($nom_champ);
									}
									else
										$tab_nom_champ = $nom_champ;
										
									$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
									///echo"<br>nom_matrice = $nom_matrice <br><pre>";
									//print_r($this->code_nomenclature[$nomtableliee][$nom_matrice]);                            
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne])){
										//echo'<pre>$this->matrice_donnees_tpl[$nomtableliee][$ligne]';
										//print_r($this->matrice_donnees_tpl[$nomtableliee][$ligne]);                            
										foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
											// Pour chq code du champ ref
											//echo "<br>val mat = $val_matrice<br>";
											if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice])){
												// S'il y' a une valeur en base, la récupérer dans la variable du Template
												//echo'<pre>$this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice]';
												$val_champs_mat = array();
												foreach($this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice] as $val){
													$val_champs_mat[] = $val;
												}
																						
												foreach($tab_nom_champ as $ord=>$nom_champ){
													$val_champ_base = $val_champs_mat[$ord];
													//$val_champ_base = addslashes($val_champ_base);
													//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$val_champ_base.'\'';
													//echo "$nom_champ.'_'.$ligne.'_'.$val_matrice = ''.$val_champ_base.''";
													${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = ''.$val_champ_base.'';
												}
											}
											else{
												foreach($tab_nom_champ as $ord=>$nom_champ){
													//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\'\'';
													${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = '';
												}
											}
										}	
									}	
								}
								elseif($zone_saisie['type']=='zone_mat'){
									// type 'e' ou 'tvm' : structure matricielle
									//echo'<pre>';
									//print_r($this->matrice_donnees_tpl[$nomtableliee][$ligne]);
									
									if( count($tab_dims = $this->get_dims_zone_matricielle($nomtableliee,'')) ){
										
										$tab_codes_dims = $this->do_concat_dimensions_codes($tab_dims['codes_dims']);
										
										$nom_matrice 	=	$zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'];
										$nom_champ		=	$zone_saisie['champ'];
										
										$result_nomenc	= $this->requete_nomenclature($zone_saisie['table_ref'], $GLOBALS['PARAM']['CODE'].'_'.$zone_saisie['table_ref'], $nom_champ, $_SESSION['langue'] ,$this->id_systeme, $zone_saisie['sql']) ;
									 
										if(is_array($tab_codes_dims)){						
											foreach($tab_codes_dims as $val_matrice){                          
												// Pour chq code du champ ref
												//echo "<br>val mat = $val_matrice<br>";
												if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice][$colonne])){
													// S'il y' a une valeur en base, la récupérer dans la variable du Template
													//echo'<pre> val_matrice_'.$val_matrice.' = '.$this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice];
													$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$val_matrice][$colonne];                                        
													//$val_champ_base = addslashes($val_champ_base);
													//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$val_champ_base.'\'';
													//echo '$'.$nom_champ.'_'.$ligne.'_'.$val_matrice .' = '.$val_champ_base.'<br>';
		
													if($zone_saisie['ss_type']=='s'){
														////////////////////////// ZONE MTRICIELLE DE TYPE TEXT
														${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = ''.$val_champ_base.'';
														///////////////////////////	FIN ZONE MTRICIELLE DE TYPE TEXT
													}elseif($zone_saisie['ss_type']=='ch'){
														////////////////////////// ZONE MTRICIELLE DE TYPE CHECKBOX
														//$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne];
														//$val_champ_base = addslashes($val_champ_base);
														${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = recherche_libelle_page('checked_box');
														//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
														///////////////////////////	FIN ZONE MTRICIELLE DE TYPE CHECKBOX
													}elseif( ($zone_saisie['ss_type']=='m') or ($zone_saisie['ss_type']=='b') or ($zone_saisie['ss_type']=='co') ){
														////////////////////////// ZONE MTRICIELLE DE TYPE COMBO, BOOLEEN, LISTE RADIO
														//echo "//// $nom_matrice <br><pre>";
														//print_r($this->code_nomenclature[$nomtableliee][$nom_matrice]);
														//echo"<br>NOM CHAMP=".$zone_saisie['champ']."->".$zone_saisie['type']."->".$this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]."<br>";
														//echo "<br>$nom_champ=$val_champ_base<br>";
														if( isset($this->code_nomenclature[$nomtableliee][$nom_matrice]) && is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
															foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_code_sel){
																// pour chq valeur code nomenclature
																if($val_code_sel == $val_champ_base){
																	//echo "<br>$nom_champ=$val_champ_base<br>";
																	// Si le code == valeur en Base	: il faut le cocher									
																	//$val_champ_base = addslashes($val_champ_base);
																	//${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' CHECKED';
																	foreach($result_nomenc as $rs_nomenc){
																		if($rs_nomenc[$nom_champ]==$val_code_sel){
																			$lib_nomenc = $rs_nomenc['LIBELLE'];
																			break;
																		}
																	}
																	if($zone_saisie['ss_type']=='co'){
																		${$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel.$suffix_ann} = $lib_nomenc;
																	}
																	else{
																		${$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel.$suffix_ann} = recherche_libelle_page('checked_box');
																	}
																}
																else{ // Sinon le code <> valeur en Base	: ne pas cocher
																	//$val_champ_base = addslashes($val_champ_base);
																	${$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel.$suffix_ann} = '';
																}
															}
														}
														else{
															//$val_champ_base = addslashes($val_champ_base);
															${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = ''.$val_champ_base.'';
														}
														///////////////////////////	FIN ZONE MTRICIELLE DE TYPE COMBO, BOOLEEN, LISTE RADIO
													}
												}
												else{
													if($zone_saisie['ss_type']=='ch'){
														${$nom_champ.'_'.$ligne.'_'.$val_matrice.$suffix_ann} = "";
													}elseif( ($zone_saisie['ss_type']=='m') or ($zone_saisie['ss_type']=='b') or ($zone_saisie['ss_type']=='co') ){
														if( isset($this->code_nomenclature[$nomtableliee][$nom_matrice]) && is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
															foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_code_sel){
																${$nom_champ.'_'.$ligne.'_'.$val_matrice.'_'.$val_code_sel.$suffix_ann} = '';
															}
														}
													}
												}
											}
										}
									}	
								}
								else{ // sinon autre Type de zone non encore spécifié
									$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
									if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne])){ // Si une valeur en Base
										$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne_donnees];
										//$val_champ_base = addslashes($val_champ_base);
										$var	=	$nom_champ.'_'.$ligne;
										//echo"<br>$nomtableliee \$$var = {$$var}<br>";
										if(!isset($$var)){
											${$nom_champ.'_'.$ligne.$suffix_ann} = ''.$val_champ_base.''; // récupérer la valeur en base
											//echo"<br>$nomtableliee\$$nom_champ _ $ligne = $val_champ_base<br>";
										}
										//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
									}
									else{
										//${$nom_champ.'_'.$ligne} = '\'\'';
										${$nom_champ.'_'.$ligne.$suffix_ann} = '';	
									}
								}
														
								//// traitement d'un champ lié é une interface de saisie
								if(isset($zone_saisie['champ_interface']) and ($zone_saisie['champ_interface']) ){
										$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ_interface'];
										if(is_array($nom_champ))
												$nom_champ = $nom_champ[0];
										$sql_temp	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql_interface'];
									//$sql_temp		=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql'];
										$sql_temp	= str_replace('$ligne',"$ligne",$sql_temp);
										
										
										$chaine_eval = "\$sql=\"$sql_temp\";";
										//echo"<br>$chaine_eval<br>";
										eval($chaine_eval);
										$sql_label = $sql;
										
										if (preg_match ("/#([^#]*)#/", $sql, $elem)){
												$v = $elem[1].'_'.$ligne;
												//echo '<br>$'.$v .'= '.$$v;
												$sql_label 	= preg_replace("/#([^#]*)#/",$$v,$sql);
										}
		
										//echo"<br>$sql_label<br>";
										if (!is_null($sql_label) and (trim($sql_label) <> '') ){	
												//echo"<br>$sql_label<br>";
												// Traitement Erreur Cas : Execute / GetOne
												try {            
														$rslabel	= $this->conn->execute($sql_label);
														if($rslabel ===false){                
																 //throw new Exception('ERR_SQL');   
														}else{
															${$nom_champ.'_'.$ligne.$suffix_ann} 	= 	$rslabel->fields[$this->get_champ_extract($nom_champ)];	
															//echo "<br><br> $sql_label";
															//echo '<br>$'.$nom_champ.'_'.$ligne  .'= '.$rslabel->fields[$this->get_champ_extract($nom_champ)];						 
														}
												}
												catch (Exception $e) {
														 $erreur = new erreur_manager($e,$sql_label);
												}        
												// Fin Traitement Erreur Cas : Execute / GetOne										
										}
								}
								///////////////////////////////
								//$_SESSION["table_html"] = $nom_champ.'_'.$ligne.'::::::::'.$_SESSION['nbre_total_enr'];
							}
							
						}
						
					}
					
				}
			}
		}
	
		//die($table_html);
		
		//$result = sprintf("%s", $table_html);
		
		if(count($result_interf)){
			$this->template = $table_html ;
		}
		unset($result_interf);
		unset($result_interface);
		//echo '<br> APRES <br> '.$table_html.'<br>'; die();
		$code = '$result = sprintf("%s", "'.str_replace('"','\"',$table_html).'");';
		//die($code);
		eval($code);
		
		return $result;
		//eval ("\$eval_template =addslashes($table_html);");
		//return eval("print ($eval_template);");
	}
		
	/**
	* METHODE :  verif()
	* Effectue la vérification de des elements envoyés 
	* suivant les régles de gestion associées
	*<pre>
	*	-->	Pour chq table mére $nomtableliee
	*			Pour chaque zone de la table mére $nomtableliee
	*				Récupérer les Régles de contréle associées é la zone
	*				Si des incohérences sont notées
	*					--> déclencher l'alerte
	*					--> réafficher le template
	*</pre>
	* @access public
	*/
    function verif($matr){
        $code_etablissement = $this->code_etablissement;
        $code_annee 		= $this->code_annee;
		$code_filtre 		= $this->code_filtre;
        $lignes_tpl_saisies = $this->get_lignes_tpl_saisies($matr);// Récupération des lignes du Template contenant des données
        foreach($this->nomtableliee as $nomtableliee){// Pour chq Table Mére
            foreach( $lignes_tpl_saisies as $key => $ligne ){ // pour Chq ligne contenant des données
                foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){
					$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
					$id_zone	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'];
					$all_regles = $this->tab_regles_zone[$id_zone]; // liste des régles autour de la zone
					if( isset($all_regles) and is_array($all_regles) ){ //si champ é controler
						
						$concat_tab_code = array();
						$concat_tab_code_col = array();
						$concat_tab_code_lig = array();
						
						if($zone_saisie['type']=='e' or $zone_saisie['type']=='tvm' or $zone_saisie['type']=='li_ch' or $zone_saisie['type']=='b' or $zone_saisie['type']=='m'  ){
							$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
							$concat_tab_code = $this->code_nomenclature[$nomtableliee][$nom_matrice] ;
						}elseif($zone_saisie['type']=='zone_mat'){
							if( count($tab_dims = $this->get_dims_zone_matricielle($nomtableliee,'')) ){
								$tab_codes_dims = $this->do_concat_dimensions_codes($tab_dims['codes_dims']);
								$concat_tab_code = $this->do_concat_dimensions_codes($tab_dims['codes_dims']);
								$concat_tab_code_lig = $this->do_concat_dimensions_codes_lig($nomtableliee);
								$concat_tab_code_col = $this->do_concat_dimensions_codes_col($nomtableliee);
							}
						}
						elseif( $zone_saisie['type'] == 'loc_etab' ){
							$GLOBALS['ctrl_loc_etab'] = 1 ;
							$GLOBALS['tab_chaines'] = $this->get_chaines_loc($this->id_systeme);
						}
						(!is_array($nom_champ)) ? ($tab_nom_champ = array($nom_champ)) : ($tab_nom_champ = $nom_champ) ;
						foreach( $tab_nom_champ as $i_chp => $chp_ctrl ){
							if($this->type_theme==4 && $this->type_gril_eff_fix_col){
								$champ_saisie = $chp_ctrl.'_0';
								$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code_col, $concat_tab_code_lig[$ligne], $this->type_gril_eff_fix_col);
							}else{
								$champ_saisie = $chp_ctrl.'_'.$ligne;
								$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code, $concat_tab_code_lig[$ligne], $this->type_gril_eff_fix_col);
							}
							if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
								$this->reafficher_template($this->template, $matr, $tab_err['chp']);
								echo "<script type='text/Javascript'>\n";
								echo "$.unblockUI();\n";
								echo "</script>\n";
								exit();
							}
						}
					}
                }
            }
        }
    }	
	
	/**
	* METHODE :  reafficher_template ( template , post )
	* Permet de réaficher le template aprés erreur
	* en concervant les données postées
	*	
	*<pre>
	*	-->	Pour chq table mére $nomtableliee
	*			Pour chaque zone de la table mére $nomtableliee
	*				Récupérer le Nom de champ la zone
	*				Récupérer la valeur saisie dans le POST
	*				Remettre la valeur du champ sur le Template
	*</pre>
	* @access public
	*/
	function reafficher_template($template, $post, $tab_champs_err){
		$code_etablissement = $this->code_etablissement;
		$code_annee = $this->code_annee;
		$code_filtre = $this->code_filtre;
		//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
		${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']} = $code_annee;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;
		if(isset($_SESSION['code_regroupement']) && $_SESSION['code_regroupement'] <> ''){
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']} = $_SESSION['code_regroupement'];
		}
		//Fin ajout Hebie
		
		foreach($this->nomtableliee as $nomtableliee){
		//$this->preparer_donnees_tpl($nomtableliee);
			//if(is_array($this->matrice_donnees[$nomtableliee])){			
				for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
					foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){
						if( $zone_saisie['type']=='l'){
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
								
								$sql_temp		=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql'];
								$sql_temp		= str_replace('$ligne',"$ligne",$sql_temp);
								if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
									$sql_temp = preg_replace('/'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'[[:space:]]*=/', $GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' IN ', $sql_temp);
								}
								//echo"<br>$sql_temp<br>";
								$chaine_eval = "\$sql=\"$sql_temp\";";
								eval($chaine_eval);
								$sql_label = $sql;
								//echo"<br>***** $sql *****<br>";
								if (preg_match ("/#([^#]*)#/", $sql, $elem)){
										$v = $elem[1].'_'.$ligne;
										//echo"<br>\$$v=$v<br>";
										$sql_label = preg_replace("/#([^#]*)#/",$$v,$sql);
								}								
								//echo"<br>$sql_label<br>";
								if (!is_null($sql_label) and (trim($sql_label)<>'') ){	
										// Traitement Erreur Cas : Execute / GetOne
										try {            
												$rslabel	= $this->conn->execute($sql_label);
												if($rslabel ===false){                
														 throw new Exception('ERR_SQL');   
												}
												${$nom_champ.'_'.$ligne} 	= 	$rslabel->fields[$this->get_champ_extract($nom_champ)];								 
										}
										catch (Exception $e) {
												 $erreur = new erreur_manager($e,$sql_label);
										}        
										// Fin Traitement Erreur Cas : Execute / GetOne										
								}
						}

						elseif( $zone_saisie['type']=='s' or $zone_saisie['type']=='csu' or $zone_saisie['type']=='sys_txt' or $zone_saisie['type']=='hidden_field' ){
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
									$nom_champ = $nom_champ[0];
							$v	=	$nom_champ.'_'.$ligne ;
							${$nom_champ.'_'.$ligne} = $post[$v];	
						}
						elseif( $zone_saisie['type']=='ch' ){
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
									$nom_champ = $nom_champ[0];
							$v	=	$nom_champ.'_'.$ligne ;
							if($post[$v]==1){
									${$nom_champ.'_'.$ligne} = "'1' CHECKED";
							}
							else{
									${$nom_champ.'_'.$ligne} = "'1'";	
							}
						}
						elseif( $zone_saisie['type']=='li_ch' ){
							//
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
								
							$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
							//echo "$nom_matrice <br>";
							//echo"<br>NOM CHAMP=".$zone_saisie['champ']."->".$zone_saisie['type']."->".$this->matrice_donnees[$nomtableliee][$ligne][$colonne]."<br>";
							//$nb_lignes_ch = count($this->code_nomenclature[$nomtableliee][$nom_matrice]);
							if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
								foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
									$checked = false ;
									$v	=	$nom_champ.'_'.$ligne.'_'.$val_matrice ;
									if(isset($post[$v])){
										${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' CHECKED';
										$checked = true ;
									}
									else{
										${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\'';
									}
								}
							}						
						}
						elseif($zone_saisie['type']=='loc_etab'){
								///  données é prendre de quelque part
								//$tab_chaines = array();
								//$tab_chaines[] = array( 'numCh' => 2, 'nbRegs' => 4 );
								///
								$tab_chaines = $this->get_chaines_loc($this->id_systeme);
								unset($_SESSION['reg_parents']);
								require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
								foreach($tab_chaines as $iLoc => $chaine){
										${'LOC_REG_'.$iLoc} = $post['LOC_REG_'.$iLoc];
										for( $i = 0; $i < $chaine['nbRegs']; $i++){
												${'LIBELLE_REG_'.$iLoc.'_'.$i} = $post['LIBELLE_REG_'.$iLoc.'_'.$i];
										}
										//$arbre = $GLOBALS['arbre'];
										/*$arbre = new arbre($chaine['numCh']);
										$rattachements = $arbre->getparentsid( $chaine['nbRegs']-1 , ${'LOC_REG_'.$iLoc}, $chaine );
										//echo '<pre>';
										//print_r($rattachements);
										foreach( $rattachements as $i => $rattachement ){
												${'LIBELLE_REG_'.$iLoc.'_'.$i} = $rattachement['LIBELLE_REGROUPEMENT'];
										}*/
								}
						}

						elseif( ($zone_saisie['type']=='m') or ($zone_saisie['type']=='b') or ($zone_saisie['type']=='co')  
										or ($zone_saisie['type']=='cmu') or ($zone_saisie['type']=='sco') ){
							//
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								$v	=	$nom_champ.'_'.$ligne ;
								if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
								$v	=	$nom_champ.'_'.$ligne ;
								$val_post 		= $this->extraire_valeur_matrice($post[$v]);
								$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;

								if( isset($this->code_nomenclature[$nomtableliee][$nom_matrice]) and is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
										foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
												if($val_matrice == $val_post){

														if($zone_saisie['type']=='co' or $zone_saisie['type']=='sco'){
																${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' SELECTED';
														}
														else
																${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\''.' CHECKED';
												}
												else{
														//$val_champ_base = addslashes($val_champ_base);
														${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '\''.$nom_champ.'_'.$ligne.'_'.$val_matrice.'\'';
												}
										}
								}
						}
						elseif($zone_saisie['type']=='e' or $zone_saisie['type']=='tvm'){
								//echo'<pre>';
								//print_r($this->matrice_donnees_tpl[$nomtableliee][$ligne]);
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								if(!is_array($nom_champ))
										$tab_nom_champ = array($nom_champ);
								else
										$tab_nom_champ = $nom_champ;
								$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
																					
								if( isset($this->code_nomenclature[$nomtableliee][$nom_matrice]) and is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
										foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
												foreach($tab_nom_champ as $ord=>$nom_champ){
														$v	=	$nom_champ.'_'.$ligne.'_'.$val_matrice;
														${$nom_champ.'_'.$ligne.'_'.$val_matrice} = $post[$v];
												}
										}	
								}	
						}
						elseif($zone_saisie['type']=='zone_mat'){
							if( count($tab_dims = $this->get_dims_zone_matricielle($nomtableliee,'')) ){
								//echo'<pre>';
								//print_r($this->matrice_donnees_tpl[$nomtableliee][$ligne]);
								
								$nom_champ		=	$zone_saisie['champ'];
									
								$tab_codes_dims = $this->do_concat_dimensions_codes($tab_dims['codes_dims']);
																					
								if(is_array($tab_codes_dims)){						
									foreach($tab_codes_dims as $val_matrice){
											
										$v	=	$nom_champ.'_'.$ligne.'_'.$val_matrice;
										
										if($zone_saisie['ss_type']=='s' or $zone_saisie['ss_type']=='hidden_field'){
											////////////////////////// ZONE MTRICIELLE DE TYPE TEXT
											${$v} = $post[$v];
											///////////////////////////	FIN ZONE MTRICIELLE DE TYPE TEXT
										}elseif($zone_saisie['ss_type']=='ch'){
											////////////////////////// ZONE MTRICIELLE DE TYPE CHECKBOX
											if($post[$v]==1){
													${$v} = "'1' CHECKED";
											}
											else{
													${$v} = "'1'";	
											}
											///////////////////////////	FIN ZONE MTRICIELLE DE TYPE CHECKBOX
										}elseif( ($zone_saisie['ss_type']=='m') or ($zone_saisie['ss_type']=='b') or ($zone_saisie['ss_type']=='co') ){
											////////////////////////// ZONE MTRICIELLE DE TYPE COMBO, BOOLEEN, LISTE RADIO
											$val_post 		= $this->extraire_valeur_matrice($post[$v]);
											$nom_matrice 	= $zone_saisie['id_zone'] . '_' . $zone_saisie['table_ref'] ;
											if( isset($this->code_nomenclature[$nomtableliee][$nom_matrice]) and is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
												foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_code_sel){
													if($val_code_sel == $val_post){
														if($zone_saisie['ss_type']=='co'){
															${$v.'_'.$val_code_sel} = '\''.$v.'_'.$val_code_sel.'\''.' SELECTED';
														}
														else{
															${$v.'_'.$val_code_sel} = '\''.$v.'_'.$val_code_sel.'\''.' CHECKED';
														}
													}
													else{
														${$v.'_'.$val_code_sel} = '\''.$v.'_'.$val_code_sel.'\'';
													}
												}
											}
											///////////////////////////	FIN ZONE MTRICIELLE DE TYPE COMBO, BOOLEEN, LISTE RADIO
										}
									}	
								}
							}	
						}
						else{
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
								$v	=	$nom_champ.'_'.$ligne;
								//echo"<br>$nomtableliee \$$var = {$$var}<br>";
								if( !isset($$v) and $post[$v] ){
										${$nom_champ.'_'.$ligne} = $post[$v];
										//echo"<br>$nomtableliee\$$nom_champ _ $ligne = $val_champ_base<br>";
								}
								elseif(!isset($$v) and $this->matrice_donnees[$nomtableliee][$ligne][$colonne]){
										${$nom_champ.'_'.$ligne} = $this->matrice_donnees[$nomtableliee][$ligne][$colonne];
								}
						}
						
						//Gestion de la saisie de plusieurs themes bases sur la meme table matricielle avec des colonnes differentes
						if(($this->type_theme==4) && ($this->type_gril_eff_fix_col) && ($zone_saisie['type']=='c' || $zone_saisie['type']=='dim_col')){
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							//Recuperation des cles des donnees bd dont l'affichage est prevu dans le template
							foreach ($this->matrice_donnees[$nomtableliee] as $element){
								$keys_data = $this->get_tab_all_keys_val($nomtableliee, $element);
								foreach($keys_data as $i_c_z => $val){
									$i_c_d = $this->col_zone_Equi_col_data[$nomtableliee]['zone'][$i_c_z];
									if($this->champs[$nomtableliee][$i_c_d]==$nom_champ && preg_match("/'".$nom_champ."_".$ligne."_".$val."'/",$template)){
										$this->keys_template[$nomtableliee][] = $keys_data;
										break;
									}
								}
							}
							//Fin Recuperation des cles des donnees bd dont l'affichage est prevu dans le template
						}
						//Fin Gestion de la saisie de plusieurs themes bases sur la meme table matricielle avec des colonnes differentes
							
						//// traitement d'un champ lié é une interface de saisie
						if(isset($zone_saisie['champ_interface']) and ($zone_saisie['champ_interface']) ){
								$nom_champ_interface	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ_interface'];
								if(is_array($nom_champ_interface))
										$nom_champ_interface = $nom_champ_interface[0];
								$v	=	$nom_champ_interface.'_'.$ligne;
								${$nom_champ_interface.'_'.$ligne} = $post[$v];
								
								$nom_champ_cache	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								if(is_array($nom_champ_cache))
										$nom_champ_cache = $nom_champ_cache[0];
								$v	=	$nom_champ_cache.'_'.$ligne;
								${$nom_champ_cache.'_'.$ligne} = $post[$v];
						}
						///////////////////////////////
					}
				}
			}
		//}
		// Configuration de la barre de navigation
		configurer_barre_nav($this->nb_lignes);
		eval("echo \"".str_replace('"','\"',$template)."\";");
		mettre_en_evidence_champ_erreur($tab_champs_err);
		// Affichage de la barre de navigation
		afficher_barre_nav(true,true,array('theme','type_ent_stat'));
		
		//eval ("\$eval_template =addslashes($template);");
		//return eval("print ($eval_template);");
	}	

	
	/**
	* METHODE :  get_post_template ( POST ) 
	* Permet de configurer la variable $matrice_donnees_post 
	* aprés soumission de la page HTML via POST
	* et d'afficher la page HTML .
	*<pre>
	*	-->	Pour chq table mére $nomtableliee
	*			Pour chaque zone de la table mére $nomtableliee
	*				Récupérer le Nom de champ la zone
	*				Parcourir les lignes du Template
	*					Si le champ a été saisi Alors
	*						Ranger la valeur dans $this->matrice_donnees_post
	*</pre>
	*
	* @access public
	*/
	function get_post_template($matr){
		//global $val_cle;
		//echo'TAB ZONE SAISIE <pre>';
		//print_r($this->tableau_zone_saisie);
		//echo'POST<br> <pre>';
		//print_r($matr);
		//exit;
		//Récupération des plages de codes réservés lors des créations ETABLISSEMENTS, ENSEIGNANTS, ...
		$this->get_plage_codes();
        $tab_cles_max   	=   $this->get_cles_max()  ;
		$curr_max_cle_line	= 	array();

		//echo '<pre><br>$tab_cles_max<br>';
		//print_r($tab_cles_max);
		
		foreach($this->nomtableliee as $nomtableliee){	
            //  echo '<pre>';
            //  echo $nomtableliee.'<br>';
            //  print_r($this->tableau_zone_saisie[$nomtableliee]);
            //  echo'VAL CLE<pre>';
			$matrice        =   array()  ;
            
           /*if($this->new_etab == true){
                $this->code_etablissement = $max_cle_incr ;
                $this->get_dico($this->id_theme,$this->id_systeme);
            }*/
            
			//$this->parse_in_plage($max_cle_incr , $nomtableliee);
			$nb_ligne_post  = 0; //
			$ligne_mat      = -1;
			$matr_1         = array();
			for($ligne=0; $ligne < $this->nb_lignes; $ligne++){
				// on teste si la ligne est saisie
				$ligne_vide     = true;
				$nb_colonnes    = count($this->tableau_zone_saisie[$nomtableliee]);
				foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){
					$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
                   // echo $nom_champ.'//'.$zone_saisie['type'].'<br>';
					if( $zone_saisie['type']=='e' or $zone_saisie['type']=='li_ch' or $zone_saisie['type']=='tvm'){
						if(!is_array($nom_champ))
							$tab_nom_champ = array($nom_champ);
						else
							$tab_nom_champ = $nom_champ;
							
						$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'] ;
						
						if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]))
						foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
							foreach($tab_nom_champ as $nom_champ){
								$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;                                
														 
								if( isset($matr[$v]) and trim($matr[$v])<>''){				
									$ligne_vide = false;
									$nb_ligne_post++;
									break;
								}
							}
							if($ligne_vide == false)
								break;
						}
						if($ligne_vide == false)
							break;
					}
					if( $zone_saisie['type']=='zone_mat'){
						if( count($tab_dims = $this->get_dims_zone_matricielle($nomtableliee,'')) ){
								
							$nom_champ		=	$zone_saisie['champ'];
								
							$tab_codes_dims = $this->do_concat_dimensions_codes($tab_dims['codes_dims']);
							
							if(is_array($tab_codes_dims)){						
								foreach($tab_codes_dims as $val_matrice){
									$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;                                
															 
									if( isset($matr[$v]) and trim($matr[$v])<>''){				
										$ligne_vide = false;
										$nb_ligne_post++;
										break;
									}
								if($ligne_vide == false)
									break;
								}
							}
							if($ligne_vide == false)
								break;
						}
					}
					elseif($zone_saisie['type']=='loc_etab'){
						/// données é prendre de quelque part
						/// $tab_chaines = array();
						/// $tab_chaines[] = array( 'numCh' => 2, 'nbRegs' => 4 );
						///
						$tab_chaines = $this->get_chaines_loc($this->id_systeme);
						
						foreach($tab_chaines as $iLoc => $chaine){
							$v	=	'LOC_REG_'.$iLoc;
							if( isset($matr[$v]) and trim($matr[$v])<>''){
                                $ligne_vide = false;
                                //$nb_ligne_post++;
                                break;
							}
						}
					}else{
						$v = $nom_champ.'_'.$ligne ;   
                                              
						if( isset($matr[$v]) and trim($matr[$v])<>'' ){				
							$ligne_vide = false;
							$nb_ligne_post++;
							break;
						}
					}
				} 
				/////////////////                
                
                if( ($ligne_vide) == false and (!isset($matr['DELETE_'.$ligne])) ){			
					// Sér que la ligne contient des données
                    //echo "<br> __ LIGNE NON VIDE : $ligne / Nb_ligne_post=$nb_ligne_post<br>";
                    $ligne_mat++;
					if( $this->new_etab == true){
                        // New étab, nouveau code Etab et validation de ce code par rapport aux plages attribuéesde
						$pos_ch_code_etab 	= $this->get_pos_champ_cle_in_tab($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $this->main_table_mere) ;
						$new_code_etab		= $tab_cles_max[$this->main_table_mere][$pos_ch_code_etab] + 1 ;
						$this->parse_in_plage($new_code_etab , $GLOBALS['PARAM']['CODE_ETABLISSEMENT']);	
						$this->code_etablissement = $new_code_etab ;
					}

					////s'il s'agit d'un type 'e' ou 'tvm' : structure matricielle
					if( ($pos_type_e = $this->contient_type_e($nomtableliee)) or ($pos_type_tvm = $this->contient_type_tvm($nomtableliee))){
						$pos_champ_ref = $this->get_pos_champ_ref($nomtableliee);
						if($pos_type_e){
							$pos_type = $pos_type_e;
						}else{
							$pos_type = $pos_type_tvm;
						}
						//echo '<pre>VARS_GLOBALS';
						//print_r($this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee]);
						foreach($this->tab_keys_zones[$nomtableliee] as $colonne => $col_zone){ // On parcours les clés d'abord
							
							$nom_cle 	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ']	;
							$zone_type 	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['type']	;
							$col_data	= 	$this->col_zone_Equi_col_data[$nomtableliee]['zone'][$colonne];
							
							$v = $nom_cle.'_'.$ligne ;	
									
							if($nom_cle == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){ //  Champ CODE_ETABLISSEMENT 
								
								//Ajout HEBIE pour l'enregistrement sequentiel des enseignants basé sur la valeur CODE_ETAB_ENSEIG_TRANSFERT de chaque ligne é transmettre a chaque fois é $this->code_etablissement
								if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
									if($type_zone=='m' or $type_zone=='b' or $type_zone=='co' or $type_zone=='cmu' or $type_zone=='sco'){
										$matr_1[$ligne_mat][$col_data] =  $this->extraire_valeur_matrice($matr[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_'.$ligne]);// Position le code Etab
									}else{
										$matr_1[$ligne_mat][$col_data] = $matr[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_'.$ligne]; // Position le code Etab
				                	}
								}else{								                 
									$matr_1[$ligne_mat][$col_data] = $this->code_etablissement; // Position le code Etab
								}
								//Fin Ajout HEBIE 
								
							}elseif( isset($matr[$v]) and trim($matr[$v])<>''){ // vaaleur de la clé ds le POST
								if( ($zone_type=='sco') or ($zone_type=='cmu') ){
									$matr_1[$ligne_mat][$col_data] =  $this->extraire_valeur_matrice($matr[$v]);
								}else{
									$matr_1[$ligne_mat][$col_data] =  $matr[$v];
								}
							}elseif( trim($this->val_cle[$nomtableliee][$nom_cle]) <> ''){
								$matr_1[$ligne_mat][$col_data] = $this->val_cle[$nomtableliee][$nom_cle];
							}elseif( isset($this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee][$ligne]) ){ // Pas de de nouvelle création, on récupére le code en Base
								$tab_keys_line 	= explode('_', $this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee][$ligne]);
								$pos_in_joins 	= $this->get_pos_field_in_common_fields_joined($colonne, $nomtableliee) ;
								
								if(isset($pos_in_joins) &&(trim($pos_in_joins) <> '')){
									//echo '<br>$pos_in_joins ='.$pos_in_joins ;
									$matr_1[$ligne_mat][$col_data] = $tab_keys_line[$pos_in_joins];
									//echo"tabm=$nomtableliee / chp=$v / lig=$ligne / val=".$matr_1[$ligne_mat][$col_data]."<br>";
								}
							}elseif( ($nb_ligne_post > count($this->matrice_donnees_tpl[$nomtableliee]) ) ){ // nouvelle création
								if(isset($tab_cles_max[$nomtableliee][$colonne])){
									$pos_join = $this->get_pos_field_in_common_fields_joined($colonne, $nomtableliee);
									if(!isset($curr_max_cle_line[$ligne][$pos_join])){
										$curr_max_cle_line[$ligne][$pos_join] 	= $tab_cles_max[$nomtableliee][$colonne] + 1 ;
										$tab_cles_max[$nomtableliee][$colonne]	= $tab_cles_max[$nomtableliee][$colonne] + 1;
									}
									$curr_max = $curr_max_cle_line[$ligne][$pos_join] ;
									
									$this->parse_in_plage($curr_max , $nom_cle); // Vérification de la valeur attribuée / Plage de Codes
									$matr_1[$ligne_mat][$col_data] = $curr_max ;
									if( !isset($this->is_incremental_field[$nomtableliee][$col_data]) ){
										$this->is_incremental_field[$nomtableliee][$col_data] = true ;
									}
									//echo '<br>tabm='.$nomtableliee.' / clé incr ='.$v .' / value='.$tab_cles_max[$nomtableliee][$colonne];
								}
								//
							}
							// Récupération d'une éventuelle valeur de la clé dans le POST
							$this->set_val_cle_post($matr,$matr_1,$nom_cle,$ligne,$ligne_mat,$col_data,$nomtableliee);
						}
									
						$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$pos_type]['champ'];	
						
						//echo '<pre><br>nom_champ';
						//print_r($nom_champ);			
						/////////////////////////////////
						if(!is_array($nom_champ)){
							$tab_nom_champ = array($nom_champ);
						}
						else
							$tab_nom_champ = $nom_champ;
						$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$pos_type]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$pos_type]['table_ref'] ;						
						
						// Traitement de la partie matricielle 'e' ou 'tvm', ...
						if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]))
						foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
							// Parcours des codes du champ Réf
							$matr_1[$ligne_mat][$pos_champ_ref] = $val_matrice ; 
							$mat = array();
							$mat = $matr_1[$ligne_mat];
							$mat_plus = array();
							$champs_saisis = false ;
							foreach($tab_nom_champ as $nom_champ){
								$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;
								
								$pos_chp_data = $this->get_pos_champ_data_in_tab($nom_champ, $nomtableliee)	;
										
								if( isset($matr[$v]) and trim($matr[$v])<>''){	// Récupération de la valeur saisie dans le POST 			
									$champs_saisis = true ;
									$val_champ = $matr[$v];
									$mat_plus[$pos_chp_data] = $val_champ ;
									//echo '<br>'.$nom_champ.'_'.$ligne.'_'.$val_matrice ;
								}
								else{
									$mat_plus[$pos_chp_data] = '' ;
								}
							}
							if($champs_saisis==true){
								foreach($mat_plus as $pos_chp => $val_mesure)
									$mat[$pos_chp] = $val_mesure;
								ksort($mat);
								$matrice[] = $mat;
							}
						}
						/////////////////////////////////
					}
					elseif( $this->est_ds_tableau($nomtableliee, $this->list_tabms_matric) ){
							
						if( count($tab_dims = $this->get_dims_zone_matricielle($nomtableliee,'')) ){
							$tab_zones_dims = $tab_dims['zones_dims'];
							
							foreach($this->tab_keys_zones[$nomtableliee] as $colonne => $col_zone){ // On parcours les clés d'abord
								//echo '<br>'.$nomtableliee.' / $nom_cle_'.$ligne.' = '.$nom_cle;
								$nom_cle 	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ']	;
								$zone_type 	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['type']	;
								$col_data	= 	$this->col_zone_Equi_col_data[$nomtableliee]['zone'][$colonne];
								 
								$v = $nom_cle . '_' . $ligne ;	
										
								if($nom_cle == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){ //  Champ CODE_ETABLISSEMENT                                  
									
									//Ajout HEBIE pour l'enregistrement sequentiel des enseignants basé sur la valeur CODE_ETAB_ENSEIG_TRANSFERT de chaque ligne é transmettre a chaque fois é $this->code_etablissement
									if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
										if($type_zone=='m' or $type_zone=='b' or $type_zone=='co' or $type_zone=='cmu' or $type_zone=='sco'){
											$matr_1[$ligne_mat][$col_data] =  $this->extraire_valeur_matrice($matr[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_'.$ligne]);// Position le code Etab
										}else{
											$matr_1[$ligne_mat][$col_data] = $matr[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_'.$ligne]; // Position le code Etab
										}
									}else{								                 
										$matr_1[$ligne_mat][$col_data] = $this->code_etablissement; // Position le code Etab
									}
									//Fin Ajout HEBIE 
									
								}elseif( isset($matr[$v]) and trim($matr[$v])<>''){ // vaaleur de la clé ds le POST
									if( ($zone_type=='sco') or ($zone_type=='cmu') ){
										$matr_1[$ligne_mat][$col_data] =  $this->extraire_valeur_matrice($matr[$v]);
									}else{
										$matr_1[$ligne_mat][$col_data] =  $matr[$v];
									}
								}elseif( trim($this->val_cle[$nomtableliee][$nom_cle]) <> ''){
									//echo 'val -> '.$nom_cle . '<br>';
									$matr_1[$ligne_mat][$col_data] = $this->val_cle[$nomtableliee][$nom_cle];
								}elseif( isset($this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee][$ligne]) ){ // Pas de de nouvelle création, on récupére le code en Base
									//echo"$ligne : existe  clé :  ".$this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee][$ligne]."<br>";
									$tab_keys_line 	= explode('_', $this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee][$ligne]);
									$pos_in_joins 	= $this->get_pos_field_in_common_fields_joined($colonne, $nomtableliee) ;
									if(isset($pos_in_joins) &&(trim($pos_in_joins) <> '')){
										$matr_1[$ligne_mat][$col_data] = $tab_keys_line[$pos_in_joins];
									}
								}elseif( ($nb_ligne_post > count($this->matrice_donnees_tpl[$nomtableliee]) ) ){ // nouvelle création
									if(isset($tab_cles_max[$nomtableliee][$colonne])){
										$pos_join = $this->get_pos_field_in_common_fields_joined($colonne, $nomtableliee);
										if(!isset($curr_max_cle_line[$ligne][$pos_join])){
											$curr_max_cle_line[$ligne][$pos_join] 	= $tab_cles_max[$nomtableliee][$colonne] + 1 ;
											$tab_cles_max[$nomtableliee][$colonne]	= $tab_cles_max[$nomtableliee][$colonne] + 1;
										}
										$curr_max = $curr_max_cle_line[$ligne][$pos_join] ;
										
										$this->parse_in_plage($curr_max , $nom_cle); // Vérification de la valeur attribuée / Plage de Codes
										$matr_1[$ligne_mat][$col_data] = $curr_max ;
										if( !isset($this->is_incremental_field[$nomtableliee][$col_data]) ){
											$this->is_incremental_field[$nomtableliee][$col_data] = true ;
										}
										//echo '<br>tabm='.$nomtableliee.' / clé incr ='.$v .' / value='.$tab_cles_max[$nomtableliee][$colonne];
									}								
								}
								// Récupération d'une éventuelle valeur de la clé dans le POST
								$this->set_val_cle_post($matr,$matr_1,$nom_cle,$ligne,$ligne_mat,$col_data,$nomtableliee);
							}
	
							
							$tab_champ_matr = $this->get_noms_champs_matriciels($nomtableliee);
								
							$tab_zones_dims = $tab_dims['zones_dims'];						
							$tab_codes_dims = $this->do_concat_dimensions_codes($tab_dims['codes_dims']);
							//echo '<pre>tab_champ_matr<br>';
							//print_r($tab_champ_matr);			
							if(is_array($tab_codes_dims)){						
								foreach($tab_codes_dims as $val_matrice){
									// Parcours des codes du champ Réf
									$tab_codes_dim_curr_line = explode('_',$val_matrice);
									foreach($tab_zones_dims as $i_dim => $dim){
										$matr_1[$ligne_mat][$dim['pos_dim']] = $tab_codes_dim_curr_line[$i_dim]; 
									}
									
									$mat = array();
									$mat = $matr_1[$ligne_mat];
									$mat_plus = array();
									$champs_saisis = false ;
									foreach($tab_champ_matr as $pos_chp_matr => $nom_champ){

										$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;
										$pos_chp_data = $this->get_pos_champ_data_in_tab($nom_champ, $nomtableliee)	;	
										//echo '<br>' . $v . ' = ' . $matr[$v] . '<br>';		
										if( isset($matr[$v]) and trim($matr[$v])<>''){	// Récupération de la valeur saisie dans le POST 			
											$champs_saisis = true ;
											$val_champ = $matr[$v];
											$type_zone = $this->tableau_zone_saisie[$nomtableliee][$pos_chp_matr]['ss_type'];
											if( $type_zone=='m' or $type_zone=='b' or $type_zone=='co'){
												$val_champ = $this->extraire_valeur_matrice($val_champ);						
											}
											$mat_plus[$pos_chp_data ] = $val_champ ;
											
										}
										else{
											$mat_plus[$pos_chp_data ] = '' ;
										}
									}
									if($champs_saisis==true){
										foreach($mat_plus as $pos_chp => $val_mesure)
											$mat[$pos_chp] = $val_mesure;
										ksort($mat);
										$matrice[] = $mat;
									}
								}
							}
							/////////////////////////////////
						}
						//echo '<pre>$matrice<br>';
						//print_r($matrice);
					}
					elseif($nomtableliee==$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']){ // traitement localisation etablissement
                        ///  données é prendre de quelque part
                        //$tab_chaines = array();
                        //$tab_chaines[] = array( 'numCh' => 2, 'nbRegs' => 4 );
                        ///
                        $tab_chaines = $this->get_chaines_loc($this->id_systeme);
                        
                        //$ligne_reg_sai
                        foreach($tab_chaines as $iLoc => $chaine){ // Parcours des chaines actives
                        ///////////////////////////////////
                            //if( ($nb_ligne_post > count($this->matrice_donnees[$nomtableliee]) ) ){
                                        //$nom_cle = $this->tableau_zone_saisie[$nomtableliee][$i]['champ'];
                            $v	=	'LOC_REG_'.$iLoc;
                            if( trim($matr[$v])<>'' ){ // Regroupement de l'Etab par rapport é la chaine en cours
                                $pos_code_etab = $this->get_pos_champ_cle_in_tab($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $nomtableliee);
                                // Récupération du code_etab s'il existe
								$matrice[$ligne_mat][$pos_code_etab] = $this->code_etablissement; ;  
                        //}
                                $pos_code_reg = $this->get_pos_champ_cle_in_tab($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'], $nomtableliee);
                        //if( isset($matr[$v]) and $matr[$v]<>''){
                                $matrice[$ligne_mat][$pos_code_reg] = $matr[$v];
                        //}
                                $ligne_mat++;
                            }
                        ////////////////////////////////////
                        }

					}elseif( trim($pos_type_li_ch = $this->contient_type_li_ch($nomtableliee)) <> '' ){  // Structure liste checkbox
                        //s'il s'agit d'une TABLE_MERE contenant la structure liste checkbox
						foreach($this->tab_keys_zones[$nomtableliee] as $colonne => $col_zone){ // On parcours les clés d'abord
							
							$nom_cle 	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ']	;
							$zone_type 	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['type']	;
							$col_data	= 	$this->col_zone_Equi_col_data[$nomtableliee]['zone'][$colonne];
							
							$v = $nom_cle . '_' . $ligne ;	
									
							if($nom_cle == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){ //  Champ CODE_ETABLISSEMENT                                  
								
								//Ajout HEBIE pour l'enregistrement sequentiel des enseignants basé sur la valeur CODE_ETAB_ENSEIG_TRANSFERT de chaque ligne é transmettre a chaque fois é $this->code_etablissement
								if(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){
									if($type_zone=='m' or $type_zone=='b' or $type_zone=='co' or $type_zone=='cmu' or $type_zone=='sco'){
										$matr_1[$ligne_mat][$col_data] =  $this->extraire_valeur_matrice($matr[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_'.$ligne]);// Position le code Etab
									}else{
										$matr_1[$ligne_mat][$col_data] = $matr[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_'.$ligne]; // Position le code Etab
				                	}
				                }else{								                 
									$matr_1[$ligne_mat][$col_data] = $this->code_etablissement; // Position le code Etab
								}
								//Fin Ajout HEBIE 
								
							}elseif( isset($matr[$v]) and trim($matr[$v])<>''){ // vaaleur de la clé ds le POST
								if( ($zone_type=='sco') or ($zone_type=='cmu') ){
									$matr_1[$ligne_mat][$col_data] =  $this->extraire_valeur_matrice($matr[$v]);
								}else{
									$matr_1[$ligne_mat][$col_data] =  $matr[$v];
								}
							}elseif( trim($this->val_cle[$nomtableliee][$nom_cle]) <> ''){
								$matr_1[$ligne_mat][$col_data] = $this->val_cle[$nomtableliee][$nom_cle];
							}elseif( isset($this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee][$ligne]) ){ // Pas de de nouvelle création, on récupére le code en Base
								//echo"$ligne : existe  clé :  ".$this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee][$ligne]."<br>";
								$tab_keys_line 	= explode('_', $this->VARS_GLOBALS['ligne_code_limit'][$nomtableliee][$ligne]);
								$pos_in_joins 	= $this->get_pos_field_in_common_fields_joined($colonne, $nomtableliee) ;
								if(isset($pos_in_joins) &&(trim($pos_in_joins) <> '')){
									$matr_1[$ligne_mat][$col_data] = $tab_keys_line[$pos_in_joins];
								}
							}elseif( ($nb_ligne_post > count($this->matrice_donnees_tpl[$nomtableliee]) ) ){ // nouvelle création
								if(isset($tab_cles_max[$nomtableliee][$colonne])){
									$pos_join = $this->get_pos_field_in_common_fields_joined($colonne, $nomtableliee);
									if(!isset($curr_max_cle_line[$ligne][$pos_join])){
										$curr_max_cle_line[$ligne][$pos_join] 	= $tab_cles_max[$nomtableliee][$colonne] + 1 ;
										$tab_cles_max[$nomtableliee][$colonne]	= $tab_cles_max[$nomtableliee][$colonne] + 1;
									}
									$curr_max = $curr_max_cle_line[$ligne][$pos_join] ;
									$this->parse_in_plage($curr_max , $nom_cle); // Vérification de la valeur attribuée / Plage de Codes
									$matr_1[$ligne_mat][$col_data] = $curr_max ;
									if( !isset($this->is_incremental_field[$nomtableliee][$col_data]) ){
										$this->is_incremental_field[$nomtableliee][$col_data] = true ;
									}
									//echo '<br>tabm='.$nomtableliee.' / clé incr ='.$v .' / value='.$tab_cles_max[$nomtableliee][$colonne];
								}
							}
							// Récupération d'une éventuelle valeur de la clé dans le POST
							$this->set_val_cle_post($matr,$matr_1,$nom_cle,$ligne,$ligne_mat,$col_data,$nomtableliee);
						}
								
						$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$pos_type_li_ch]['champ'];
						$tab_nom_champ = array();				
						/////////////////////////////////
						if(!is_array($nom_champ)){
                            $tab_nom_champ = array($nom_champ);
                        }
                        else
                            $tab_nom_champ = $nom_champ;

						$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$pos_type_li_ch]['id_zone'] . '_' . $this->tableau_zone_saisie[$nomtableliee][$pos_type_li_ch]['table_ref'] ;
						// Récupération des valeurs CHECK é travers le POST
                        if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]))
                        foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
							// Pour chaque clé Nomenc, on cherche si la case équivalente a été cochée
                            $mat = array();
							$mat = $matr_1[$ligne_mat];
							//$mat_plus = array();
							$champs_saisis = false ;
							$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;			
							if( isset($matr[$v]) and trim($matr[$v])<>''){ // Au cas échéant, on l'empile
								$champs_saisis = true ;
								$val_champ = $matr[$v];
								$mat[$pos_type_li_ch] = $val_matrice ;
							}
							if($champs_saisis==true){
								ksort($mat);
								$matrice[] = $mat;
							}
						}
						/////////////////////////////////////
					}
					else{   // structure non matricielle                   
						foreach($this->tab_keys_zones[$nomtableliee] as $colonne => $col_zone){ // On traite tout d'abord les clés
								
							$nom_cle 	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ']	;
							$zone_type 	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['type']		;
							$col_data	= 	$this->col_zone_Equi_col_data[$nomtableliee]['zone'][$colonne]	;
							
							$v 			= 	$nom_cle.'_'.$ligne ;	
								
							if($this->new_etab==true && $nom_cle==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){ // Création d'Etab : Champ CODE_ETABLISSEMENT                                  
								$matrice[$ligne_mat][$col_data] = $this->code_etablissement; // Position le code Etab
							}elseif( isset($matr[$v]) and trim($matr[$v]) <> ''){ // vaaleur de la clé ds le POST
								if( ($zone_type=='sco') or ($zone_type=='cmu') ){
									$matrice[$ligne_mat][$col_data] =  $this->extraire_valeur_matrice($matr[$v]);
								}else{
									$matrice[$ligne_mat][$col_data] =  $matr[$v];
								}
							}elseif( trim($this->val_cle[$nomtableliee][$nom_cle]) <> ''){
								$matrice[$ligne_mat][$col_data] = $this->val_cle[$nomtableliee][$nom_cle];
							}
							elseif( isset($this->matrice_donnees[$nomtableliee][$ligne][$col_data]) ){ // Pas de de nouvelle création, on récupére le code en Base
								$matrice[$ligne_mat][$col_data] = $this->matrice_donnees[$nomtableliee][$ligne][$col_data];
							}
							elseif( ($nb_ligne_post > count($this->matrice_donnees_tpl[$nomtableliee]) ) ){ // nouvelle création
								//echo '<br>tabm='.$nomtableliee.' / clé incr ='.$v ;
								if(isset($tab_cles_max[$nomtableliee][$colonne])){
									$pos_join = $this->get_pos_field_in_common_fields_joined($colonne, $nomtableliee);
									if(!isset($curr_max_cle_line[$ligne][$pos_join])){
										$curr_max_cle_line[$ligne][$pos_join] 	= $tab_cles_max[$nomtableliee][$colonne] + 1 ;
										$tab_cles_max[$nomtableliee][$colonne]	= $tab_cles_max[$nomtableliee][$colonne] + 1;
									}
									$curr_max = $curr_max_cle_line[$ligne][$pos_join] ;
									$this->parse_in_plage($curr_max , $nom_cle); // Vérification de la valeur attribuée / Plage de Codes
									$matrice[$ligne_mat][$col_data] = $curr_max ;
									if( !isset($this->is_incremental_field[$nomtableliee][$col_data]) ){
										$this->is_incremental_field[$nomtableliee][$col_data] = true ;
									}
									//echo '<br>tabm='.$nomtableliee.' / clé incr ='.$v .' / value='.$tab_cles_max[$nomtableliee][$colonne];
									//die( '<br>tabm='.$nomtableliee.' / clé incr ='.$v .' / value='.$tab_cles_max[$nomtableliee][$colonne]);
								}
							}
							
							$this->set_val_cle_post($matr,$matrice,$nom_cle,$ligne,$ligne_mat,$col_data,$nomtableliee);
						}
                        // Traitement des champs non clés
						foreach($this->tab_not_keys_zones[$nomtableliee] as $colonne => $col_zone){ 
							//$nom_champ = $this->get_champ_colonne_num($colonne);
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ']	;				
							$type_zone 	= 	$this->tableau_zone_saisie[$nomtableliee][$colonne]['type']		;
							$col_data	= 	$this->col_zone_Equi_col_data[$nomtableliee]['zone'][$colonne]	;
							
							$v = $nom_champ.'_'.$ligne ;				
							if( $type_zone <> 'l' ){ // exclus les LABELS
								//$colonne_donnees++;
								if($nom_champ ==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){ // Si champ secteur
										$matrice[$ligne_mat][$col_data] = $_SESSION['secteur']; // prendre la valeur en session
								}
								else{ //echo "<pre>"; print_r($this->tab_plage);// Sinon récupérer la valeur ds le POST
									if( isset($matr[$v]) and trim($matr[$v])<>''){					
										$val_champ = $matr[$v];
										if( $type_zone=='m' or $type_zone=='b' or $type_zone=='co' or $type_zone=='cmu' or $type_zone=='sco'){
											$val_champ = $this->extraire_valeur_matrice($matr[$v]);		
										}
										if ($nom_champ ==$GLOBALS['PARAM']['CODE_ADMINISTRATIF'] && $val_champ=='' && isset($this->tab_plage[$nom_champ])){	
											$max_code_admin	= $tab_cles_max[$nomtableliee][$col_data]; 
											$len_school_num_admin = strlen($this->tab_plage[$nom_champ]['plage_min']);									
											$max_school_num_admin = substr($max_code_admin, strlen($max_code_admin)-$len_school_num_admin);
											$school_num_admin = str_pad($max_school_num_admin + 1, $len_school_num_admin, '0', STR_PAD_LEFT); 
											$racince_code_admin = substr($this->tab_plage[$nom_champ]['val_min'],0,strlen($this->tab_plage[$nom_champ]['val_min'])-$len_school_num_admin);
											$new_code_admin = $racince_code_admin.$school_num_admin;
											$this->parse_in_plage($new_code_admin , $nom_champ);
											$val_champ = $new_code_admin;
										}
										$matrice[$ligne_mat][$col_data] = $val_champ ;
									}elseif ($nom_champ == $GLOBALS['PARAM']['CODE_ADMINISTRATIF'] && isset($this->tab_plage[$nom_champ])){
										$max_code_admin	= $tab_cles_max[$nomtableliee][$col_data];
										$len_school_num_admin = strlen($this->tab_plage[$nom_champ]['plage_min']);									
										$max_school_num_admin	= substr($max_code_admin, strlen($max_code_admin)-$len_school_num_admin);
										$school_num_admin	= str_pad($max_school_num_admin + 1, $len_school_num_admin, '0', STR_PAD_LEFT); 
										$racince_code_admin = substr($this->tab_plage[$nom_champ]['val_min'],0,strlen($this->tab_plage[$nom_champ]['val_min'])-$len_school_num_admin);
										$new_code_admin = $racince_code_admin.$school_num_admin;
										$this->parse_in_plage($new_code_admin , $nom_champ);
										$matrice[$ligne_mat][$col_data] = $new_code_admin;
									}
									else {	
										$matrice[$ligne_mat][$col_data] = '' ;
									}
								}
							}
						}
					}
				}
                // Sinon Si la ligne POST est vide, alors qu'en entrée(matrice_donnees) tous les champs sont tous des clés simple,
                // Il faut éviter la suppression de ces lignes en les tranmettant dans le POST
                elseif( isset($this->matrice_donnees_tpl[$nomtableliee][$ligne]) and ($this->all_type_key_c($nomtableliee) == true) ){ 
                    // pour mettre ds matrice_donnees_post les lignes de matrice_donnees qui n'ont subi aucunes
                    // mofifs afin d'éviter qu'elles soient supprimées
                    $ligne_mat++;
                    $matrice[$ligne_mat] = $this->matrice_donnees_tpl[$nomtableliee][$ligne];
                }
			}
			$this->matrice_donnees_post[$nomtableliee]	=	$matrice;
		}
       
        /*
	    echo'DONNEES BDD<pre>';
	    print_r($this->VARS_GLOBALS['donnees_bdd']);
     */
        /*echo'<br> CHAMPS <pre>';
		print_r($this->champs);
        echo'<table><tr><td>';
		echo'<br> matrice_donnees <pre>';
		print_r($this->matrice_donnees);
        echo'</td>';
        echo'<td>';
		echo'<br> matrice_donnees_post <pre>';
		print_r($this->matrice_donnees_post);
        echo'</td></tr></table>';*/
        
       // die();
	}
	
	
	/**
	* METHODE :  comparer ( MATR1 , MATR2 )
	*<pre>
	* Permet d'effectuer la comparaison entre MATR1 : $this->matrice_donnees 
	* et MATR2 : $this->matrice_donnees_post Le resultat est dans matrice_donnees_bdd
	* et sera directement utlisé pour la MAJ de la base de données
	*
	*DEBUT
	*	Pour chq table mére $nomtableliee
	*		Pour chq element de MATR2 
	*			Si element n'est pas dans MATR1 Alors
	*				Mettre element dans $this->matrice_donnees_bdd + option Insert	
	*			Sinon Si element(MATR2) <>	element(MATR1)
	*				Mettre element dans $this->matrice_donnees_bdd + option Update
	*
	*		Pour chq element de MATR1
	*			Si element n'est pas dans MATR2 
	*				Mettre element dans $this->matrice_donnees_bdd + option Delete	
	*</pre>
	* @access public
	*/
	function comparer ($matr1, $matr2, $keys_template=array()){
		// Cette fonction permet de faire la comparaison matricielle complexe
		/*echo 'matr1<pre>';
		print_r($matr1);
		echo '<br><br>matr2<pre>';
		print_r($matr2);*/
		foreach($this->nomtableliee as $nomtableliee){ // pour chaque Table mére
			$result 	=	array();
			$i = 0;

			if(is_array($matr2[$nomtableliee]))
				foreach ($matr2[$nomtableliee] as $elt){ // On parcours la marice de sortie
					// cette variable contient la clé identifiant de l'élément matriciell
					//$cle	=	associer_identifiant($elt,$tab_keys_fields);
					$keys_vals 	= $this->get_tab_all_keys_val($nomtableliee, $elt) ;
					//echo 'cle <pre>';
					//print_r($cle);
					$tmp		=	array();				
					$action		=	$this->existe_element_grille_action($keys_vals, $elt, $matr1[$nomtableliee], $nomtableliee);		
					//echo $action. '<br />';
					switch ($action)
					{
						case 'I':
						case 'U':				
						for ($i=0;$i<count($elt);$i++){
							$tmp[$i]	=	$elt[$i];		
						}	
						$tmp[$i++]	=	$action;
						$result []	=	$tmp;
						break;
					}	
				}
			if(is_array($matr1[$nomtableliee]))
				foreach ($matr1[$nomtableliee] as $elt)
				{
						// cette variable contient la clé identifiant de l'élément matriciell
						$tmp		=	array();
						//$cle	=	associer_identifiant($elt,$tab_keys_fields);
						$keys_vals 	= 	$this->get_tab_all_keys_val($nomtableliee, $elt) ;
						if($this->type_theme==4 && $this->type_gril_eff_fix_col){
							if(is_array($keys_template[$nomtableliee])){
								if($this->existe_cles_dans_tab_cles($keys_vals,$keys_template[$nomtableliee])){
									$action		=	$this->existe_element_grille_action($keys_vals, $elt, $matr2[$nomtableliee], $nomtableliee);
									switch ($action)
									{
										case 'I':
											
											$action		=	'D'	;				
											for ($i=0;$i<count($elt);$i++){
												$tmp[$i]	=	$elt[$i];		
											}
											$tmp[$i++]	=	$action;
											$result[]		=	$tmp;
											break;
									}
								}
							}
						}else{
							$action		=	$this->existe_element_grille_action($keys_vals, $elt, $matr2[$nomtableliee], $nomtableliee);
							switch ($action)
							{
								case 'I':
									
									$action		=	'D'	;				
									for ($i=0;$i<count($elt);$i++){
										$tmp[$i]	=	$elt[$i];		
									}
									$tmp[$i++]	=	$action;
									$result[]		=	$tmp;
									break;
							}
						}
				}		
			
			//return $result;
			$this->matrice_donnees_bdd[$nomtableliee]	=	$result;
			//echo"<br>matrice_donnees_bdd : $nomtableliee<br><pre>";
			//print_r($this->matrice_donnees_bdd[$nomtableliee]);
		}
		//echo'matrice_donnees_bdd <br> <pre>';
		//print_r($this->matrice_donnees_bdd);
	}
	
	/**
	* METHODE : ordre_insert_tables()
	*
	* Permet de mettre les tables mére ds le bon ordre d'insertion
	*
	* @access public
	*/
	function ordre_insert_tables(){
		// ex : insertion ds GROUPE_PEDAGOGIQUE avant EFFECTIF_GP_AGE
		// pour le moment nous considérons que les tables liées sont créées lors de l'instanciation
		// dans le bon ordre d'insert
		return ($this->nomtableliee);
	}
	
	
	/**
	* METHODE : ordre_delete_tables()
	*
	* Permet de mettre les tables mére ds le bon ordre de Suppression
	*
	* @access public
	*/
	function ordre_delete_tables(){
		// ex : suppression ds EFFECTIF_GP_AGE avant GROUPE_PEDAGOGIQUE
		// ds l'inverse de l'ordre d'insert
		$array = $this->nomtableliee;
		krsort($array);
		return ($array);
	}
	
	/**
	* METHODE : get_cle_max_trace()
	* <pre>
	*  recherche du id_trace max ds la table dico_trace
	* </pre>
	* @access public
	* 
	*/
	public function get_cle_max_id_trace(){
		$max_return = 0;
		$sql = ' SELECT  MAX(ID_TRACE) as MAX_INSERT FROM  DICO_TRACE';       
		// Gestion des erreurs lors de l'exécution de la requéte SQL_REQ
		try{
			if (($rs =  $GLOBALS['conn_dico']->Execute($sql))===false) {   
				throw new Exception('ERR_SQL');  
			}
			if (!$rs->EOF) {                  
				$max_return = $rs->fields['MAX_INSERT'];                  
			}
		}
		catch(Exception $e) {
			$erreur = new erreur_manager($e,$sql);
		}
		return($max_return);
	}
	
	/**
	* METHODE :  maj_bdd ()
	* Utilise $matrice_donnees_bdd pour insérer, supprimer ou 
	* mettre é jour des données de la base 
	*<pre>
	*		Pour chaque table mére $nomtableliee
	*			Parcourir $this->matrice_donnees_bdd
	*				Si << Update >>
	*					Faire une requéte d'Update
	*				Si << Insert >>
	*					Faire une requéte d'Insert
	*				Si << Delete >>
	*					Faire une requéte de Delete
	*
	*</pre>
	* @access public
	*/
	function maj_bdd(){
	
		$ID_TRACE = $this->get_cle_max_id_trace()+1;//Recuperation max id_trace + 1 pour obtenir le nouvel id_trace
		
		//////////  TRAITEMENT DES DELETE
		$ordre_delete = $this->ordre_delete_tables();
        //$this->conn->debug = true;
		//echo 'matrice_donnees_bdd:<pre>';
		//print_r($this->matrice_donnees_bdd);
        //exit;
		foreach($ordre_delete as $nomtableliee){ // Dans le bon ordre de suppression dans les tables méres
			 $matr = $this->matrice_donnees_bdd[$nomtableliee]; // martice de sortie
			 $tab_champ = $this->champs[$nomtableliee];	
			 $nomtable = $nomtableliee;
			 $tab_keys_fields = array();
			 foreach($this->tab_keys_zones[$nomtableliee] as $colonne => $col_zone){ // On traite tout d'abord les clés
				$tab_keys_fields[]	= $this->col_zone_Equi_col_data[$nomtableliee]['zone'][$colonne]	;
			 }
			 if (is_array($matr)){
				$nb_col =count((array)$matr[0]);
				$id_action = 0;
				foreach ($matr as $tab){
					$action = $tab[$nb_col-1]; //l'action es en derniére position
					$id_action++;
					/*
					 * Suppression dans une table
					 */
					//Verification existence ou non des données de l'enseignant (é suppr) dans la table ENSEIGNANT_ETABLISSEMENT (liens avec d'autres ecoles ou années)
					$NB_ENS_ETAB = 0;
					if($nomtable == $GLOBALS['PARAM']['ENSEIGNANT'] && $GLOBALS['PARAM']['ENSEIGNANT']<>$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'] && $GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']<>''){
						foreach( $tab_keys_fields as $i_key => $key_col ){
							if($tab_champ[$key_col] == $GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']){
								$val_id_enseign = $tab[$key_col]; break;
							}
						}
						$req_exist_ens_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].") AS NB_ENS_ETAB FROM ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$val_id_enseign;
						//echo "<br/>$req_exist_ens_etab<br/>";
						$NB_ENS_ETAB = $this->conn->GetOne($req_exist_ens_etab);
					}
					//Fin Verification existence
					//Verification existence ou non des données du pers admin (é suppr) dans la table PERS_ADMIN_ETABLISSEMENT (liens avec d'autres ecoles ou années)
					$NB_PERS_ADMIN_ETAB = 0;
					if(isset($GLOBALS['PARAM']['PERSONNEL_ADMIN']) && $nomtable == $GLOBALS['PARAM']['PERSONNEL_ADMIN'] && isset($GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']) && $GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']<>"" && $GLOBALS['PARAM']['PERSONNEL_ADMIN']<>$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']){
						foreach( $tab_keys_fields as $i_key => $key_col ){
							if($tab_champ[$key_col] == $GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']){
								$val_id_pers_admin = $tab[$key_col]; break;
							}
						}
						$req_exit_pers_admin_etab = "SELECT COUNT(".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN'].") AS NB_PERS_ADMIN_ETAB FROM ".$GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN']." = ".$val_id_pers_admin;
						//echo "<br/>$req_exist_ens_etab<br/>";
						$NB_PERS_ADMIN_ETAB = $this->conn->GetOne($req_exit_pers_admin_etab);
					}
					//Fin Verification existence
					if(isset($GLOBALS['PARAM']['PERSONNEL_ADMIN']) && $GLOBALS['PARAM']['PERSONNEL_ADMIN']<>''){
						if ($action =='D' && ($nomtable <> $GLOBALS['PARAM']['ETABLISSEMENT']) 
							&& (($nomtable <> $GLOBALS['PARAM']['ENSEIGNANT']) || ($nomtable == $GLOBALS['PARAM']['ENSEIGNANT'] && $NB_ENS_ETAB == 0)) 
							&& (($nomtable <> $GLOBALS['PARAM']['PERSONNEL_ADMIN']) || ($nomtable == $GLOBALS['PARAM']['PERSONNEL_ADMIN'] && $NB_PERS_ADMIN_ETAB == 0)) 
							&& ($nomtable <> $GLOBALS['PARAM']['DONNEES_ETABLISSEMENT'])){ // Traitement des supprssions
						
							$sql = 'DELETE FROM ' . $nomtable ;  
							foreach( $tab_keys_fields as $i_key => $key_col ){
								(trim($this->tableau_type_zone_base[$nomtable][$tab_champ[$key_col]])=='int') ? ( $val_key = $tab[$key_col] ) : ($val_key = $this->conn->qstr($tab[$key_col])) ;
								$sql .=  ' AND ' . $tab_champ[$key_col] . ' = ' . $val_key;  
							}                   
							$sql	= 	str_replace( $nomtable . ' AND ' , $nomtable . ' WHERE ' ,  $sql );     	
							
							//echo '<BR> ---DELETE--- <BR>'.$sql.'<BR>'; 
							if ($this->conn->Execute($sql) === false){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								print ' <br> error deleting :<br>'.$sql.'<br>';
							}
						}
					}else{
						if ($action =='D' && ($nomtable <> $GLOBALS['PARAM']['ETABLISSEMENT']) 
							&& (($nomtable <> $GLOBALS['PARAM']['ENSEIGNANT']) || ($nomtable == $GLOBALS['PARAM']['ENSEIGNANT'] && $NB_ENS_ETAB == 0)) 
							&& ($nomtable <> $GLOBALS['PARAM']['DONNEES_ETABLISSEMENT'])){ // Traitement des supprssions
	
							$sql = 'DELETE FROM ' . $nomtable ;  
							foreach( $tab_keys_fields as $i_key => $key_col ){
								(trim($this->tableau_type_zone_base[$nomtable][$tab_champ[$key_col]])=='int') ? ( $val_key = $tab[$key_col] ) : ($val_key = $this->conn->qstr($tab[$key_col])) ;
								$sql .=  ' AND ' . $tab_champ[$key_col] . ' = ' . $val_key;  
							}                   
							$sql	= 	str_replace( $nomtable . ' AND ' , $nomtable . ' WHERE ' ,  $sql );     	
							
							//echo '<BR> ---DELETE--- <BR>'.$sql.'<BR>'; 
							if ($this->conn->Execute($sql) === false){
								$GLOBALS['theme_data_MAJ_ok'] 	= false;
								print ' <br> error deleting :<br>'.$sql.'<br>';
							}
						}
					}
					if($action =='D' && $GLOBALS['theme_data_MAJ_ok']==true && (isset($GLOBALS['PARAM']['DATA_ENTRY_TRACE']) && $GLOBALS['PARAM']['DATA_ENTRY_TRACE'])){
						//MAJ DICO_TRACE
						$code_user = $_SESSION['code_user'];
						$login_user = $_SESSION['login'];
						$id_theme_user = $this->id_theme;
						$code_etab_user	= $this->code_etablissement;        
						$code_annee_user = $this->code_annee;
						if($this->code_filtre<>"") $code_filtre_user = $this->code_filtre; else $code_filtre_user = 'NULL';
						$id_systeme_user = $this->id_systeme;
						$date_saisie_user = date('d/m/Y H:i:s');
						
						$req_trace_user = "INSERT INTO DICO_TRACE (ID_TRACE,NOM_TABLE,ID_ACTION,CODE_USER,NOM_USER,ID_THEME,ACTION,SQL_ACTION,CODE_SECTEUR,CODE_ETABLISSEMENT,CODE_ANNEE,CODE_FILTRE,DATE_SAISIE) 
															VALUES ($ID_TRACE,'$nomtable',$id_action,$code_user,'$login_user',$id_theme_user,'$action',".$this->conn->qstr($sql).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
						//echo "<br>$req_trace_user";
						if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
							//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
						}
						//Fin MAJ DICO_TRACE
					}
					
				}                   
			}
		}
		/////////   FIN DE TRAITEMENT DES DELETE
		//////////  TRAITEMENT DES INSERT
		$ordre_insert = $this->ordre_insert_tables();
		//echo'ordre_insert<pre>';
		//print_r($ordre_insert);
		foreach($ordre_insert as $nomtableliee){
			 $matr = $this->matrice_donnees_bdd[$nomtableliee];
			 $tab_champ = $this->champs[$nomtableliee];		
			 $nomtable = $nomtableliee;		
             $tab_incr_field = array();
			 if(is_array($this->is_incremental_field[$nomtableliee]) && count($this->is_incremental_field[$nomtableliee])){
			 	$tab_incr_field = $this->is_incremental_field[$nomtableliee] ;
			 }
			 /*//Ajout HEBIE
			 $tab_keys_fields = array();
			 foreach($this->tab_keys_zones[$nomtableliee] as $colonne => $col_zone){ // On traite tout d'abord les clés
				$tab_keys_fields[]	= $this->col_zone_Equi_col_data[$nomtableliee]['zone'][$colonne]	;
			 }
			 //Fin Ajout HEBIE*/
			 if (is_array($matr)){
				$nb_col =count((array)$matr[0]);
				$id_action = 0;
				foreach ($matr as $tab){
				/*
				* Ajout dans une table
				*/
					//foreach ($tab as $elt){}
					$action = $tab[$nb_col-1];
					$id_action++;
					if ($action =='I' && count($tab)>0){
						$sql_champs='';
						$sql_vals='';
						$sql = '';
						foreach( $tab_incr_field as $col_data => $bool ){
							if( trim($tab[$col_data]) == '-100'){
								$msg = recherche_libelle_page('redef_plg') . ' "' . $nomtableliee . '" : ' . recherche_libelle_page('rejet_plg'). "\n";
								echo "<script type='text/Javascript'>\n";
								echo "$.unblockUI();\n";
								echo "</script>\n";
								die($msg);
								//continue;
							}
						}

						foreach($tab as $col=>$elt){
							if (($elt or $elt=='0' ) and $elt !='I'){
								$sql_champs .= ", $tab_champ[$col]";
								if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[$col]])=='int'){
									$sql_vals 	.= ", $elt";
								}else{
										$sql_vals 	.= ", ".$this->conn->qstr($elt)."";
								}
							}
						}
                        if(isset($sql_champs) && $sql_champs<>''){
						$GLOBALS['action_insert'] = true;
						$sql.= 'INSERT INTO '.$nomtable.' ('.$sql_champs.') VALUES ('.$sql_vals.')';
						$sql = str_replace('(,','(',$sql);	
						//echo '<BR> ---INSERT--- <BR>'.$sql.'<BR>'; 
						if ($this->conn->Execute($sql) === false) {
							$GLOBALS['theme_data_MAJ_ok'] 	= false;
                            print ' <br> error inserting :<br>'.$sql.'<br>';
                            if( ($this->new_etab == true) or ($nomtable == $GLOBALS['PARAM']['ETABLISSEMENT'])){
                                $GLOBALS['pb_new_etab'] = true;
                            }
                        }
						if($GLOBALS['theme_data_MAJ_ok']==true && (isset($GLOBALS['PARAM']['DATA_ENTRY_TRACE']) && $GLOBALS['PARAM']['DATA_ENTRY_TRACE'])){
							//MAJ DICO_TRACE
							$code_user = $_SESSION['code_user'];
							$login_user = $_SESSION['login'];
							$id_theme_user = $this->id_theme;
							$code_etab_user	= $this->code_etablissement;        
							$code_annee_user = $this->code_annee;
							if($this->code_filtre<>"") $code_filtre_user = $this->code_filtre; else $code_filtre_user = 'NULL';
							$id_systeme_user = $this->id_systeme;
							$date_saisie_user = date('d/m/Y H:i:s');
							
							$req_trace_user = "INSERT INTO DICO_TRACE (ID_TRACE,NOM_TABLE,ID_ACTION,CODE_USER,NOM_USER,ID_THEME,ACTION,SQL_ACTION,CODE_SECTEUR,CODE_ETABLISSEMENT,CODE_ANNEE,CODE_FILTRE,DATE_SAISIE) 
																VALUES ($ID_TRACE,'$nomtable',$id_action,$code_user,'$login_user',$id_theme_user,'$action',".$this->conn->qstr($sql).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
							//echo "<br>$req_trace_user";
							if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
								//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
							}
							//Fin MAJ DICO_TRACE
						}
						}
					}
				}
			}
		}
		/////////   FIN DE TRAITEMENT DES INSERT
		
		//////////  TRAITEMENT DES UPDATE
      foreach($this->nomtableliee as $nomtableliee){
			 $matr = $this->matrice_donnees_bdd[$nomtableliee];
             /*echo '<pre>';
             echo $nomtableliee;
             print_r($matr);
             echo '</pre>';*/
             
             
			 $tab_champ = $this->champs[$nomtableliee];		
			 $nomtable = $nomtableliee;
			 
			 $tab_keys_fields = array();
			 foreach($this->tab_keys_zones[$nomtableliee] as $colonne => $col_zone){ // On traite tout d'abord les clés
				$tab_keys_fields[]	= $this->col_zone_Equi_col_data[$nomtableliee]['zone'][$colonne]	;
			 }

			 $tab_not_keys_fields = array();
			 foreach($this->tab_not_keys_zones[$nomtableliee] as $colonne => $col_zone){ // On traite tout d'abord les clés
				$tab_not_keys_fields[]	= $this->col_zone_Equi_col_data[$nomtableliee]['zone'][$colonne]	;
			 }
			 if (is_array($matr)){
				$nb_col =count((array)$matr[0]);
				$id_action = 0;
				foreach ($matr as $tab){
					$action = $tab[$nb_col-1];
					$id_action++;
					/*
					 * Mise é jour d'une table
					 */
					 if ($action == 'U' || ($nomtable == $GLOBALS['PARAM']['DONNEES_ETABLISSEMENT'] && $action == 'D')){
					 	$crit_sql = '#';
						foreach( $tab_keys_fields as $i_key => $key_col ){
							(trim($this->tableau_type_zone_base[$nomtable][$tab_champ[$key_col]])=='int') ? ( $val_key = $tab[$key_col] ) : ($val_key = $this->conn->qstr($tab[$key_col])) ;
							$crit_sql .=  ' AND ' . $tab_champ[$key_col] . ' = ' . $val_key ;  
						}                   
						$crit_sql	= 	str_replace( '#' . ' AND ' , ' WHERE ' ,  $crit_sql );   
								
						$sql = ' UPDATE '.$nomtable.' SET ';
						
						foreach( $tab_not_keys_fields as $i_chp => $chp_col ){
							if($nomtable == $GLOBALS['PARAM']['DONNEES_ETABLISSEMENT'] && $action == 'D'){
								(trim($this->tableau_type_zone_base[$nomtable][$tab_champ[$chp_col]])=='int') ? ( $tab[$chp_col] = 'NULL' ) : ($tab[$chp_col] = '') ;
							}
							if( trim($tab[$chp_col]) <> ''){
								(trim($this->tableau_type_zone_base[$nomtable][$tab_champ[$chp_col]])=='int') ? ( $val_chp = $tab[$chp_col] ) : ($val_chp = $this->conn->qstr($tab[$chp_col])) ;
								$sql .=  ' , ' . $tab_champ[$chp_col] . ' = ' . $val_chp ;
							}else{ 
								if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$tab_champ[$chp_col])){
									 $sql .=  ' , ' . $tab_champ[$chp_col] . ' = 255' ;
								}else{
									$col_zone 	= $this->col_zone_Equi_col_data[$nomtableliee]['data'][$chp_col];
									$table_ref 	= $this->tableau_zone_saisie[$nomtableliee][$col_zone]['table_ref'] ;
									$type	= $this->tableau_zone_saisie[$nomtableliee][$col_zone]['type'] ;
									if( (trim($table_ref) <> '') && (preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table_ref))) && ($type <> 'e')){
										$sql .=  ' , ' . $tab_champ[$chp_col] . ' = 255' ;
									}else{
										$sql .=  ' , ' . $tab_champ[$chp_col] . ' = NULL' ;	
									}
								}
							}
						}
						$sql	= 	str_replace( ' SET ' . ' , ' , ' SET ' ,  $sql ); 
						$sql	.= 	$crit_sql ;
						//echo '<BR> ---UPDATE--- <BR>'.$sql.'<BR>';            	
						if ($this->conn->Execute($sql) === false){
							$GLOBALS['theme_data_MAJ_ok'] 	= false;
							print ' <br> error updating :<br> --- '.$sql.' --- <br>'; 
						}
						if($GLOBALS['theme_data_MAJ_ok']==true && (isset($GLOBALS['PARAM']['DATA_ENTRY_TRACE']) && $GLOBALS['PARAM']['DATA_ENTRY_TRACE'])){
							if($nomtable == $GLOBALS['PARAM']['DONNEES_ETABLISSEMENT'] && $action == 'D') $action = 'U';
							//MAJ DICO_TRACE
							$code_user = $_SESSION['code_user'];
							$login_user = $_SESSION['login'];
							$id_theme_user = $this->id_theme;
							$code_etab_user	= $this->code_etablissement;        
							$code_annee_user = $this->code_annee;
							if($this->code_filtre<>"") $code_filtre_user = $this->code_filtre; else $code_filtre_user = 'NULL';
							$id_systeme_user = $this->id_systeme;
							$date_saisie_user = date('d/m/Y H:i:s');
							
							$req_trace_user = "INSERT INTO DICO_TRACE (ID_TRACE,NOM_TABLE,ID_ACTION,CODE_USER,NOM_USER,ID_THEME,ACTION,SQL_ACTION,CODE_SECTEUR,CODE_ETABLISSEMENT,CODE_ANNEE,CODE_FILTRE,DATE_SAISIE) 
																VALUES ($ID_TRACE,'$nomtable',$id_action,$code_user,'$login_user',$id_theme_user,'$action',".$this->conn->qstr($sql).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user');";
							//echo "<br>$req_trace_user";
							if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
								//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
							}
							//Fin MAJ DICO_TRACE
						}
					}
				}                   
			}
		}
		/////////   FIN DE TRAITEMENT DES UPDATE
	}
}	
?>
