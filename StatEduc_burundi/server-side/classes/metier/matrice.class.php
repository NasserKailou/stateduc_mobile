<?php /** 
 * Classe matrice générique
 * <pre>
 * Classe permettant de 
 * -> Charger la matrice de données des manuels eleves d'un 
 *    établissement
 * -> Charger les nomenclatures associées au manuel
 * -> Vérifier l'intégrité de données renvoyées 
 * </pre>
 * @access public
 * @author Alassane
 * @version 1.4
 * @todo Changer la methode de passage de la connexion SGBD
*/

class matrice {
	/**
	 * Classe manuel_eleve générique
	 * 
	 * Classe permettant de - charger la matrice de données des manuels eleves
	 * d'un établissement. - charger les nomenclatures associées au manuel. -
	 * verifier l'intégrité de données renvoyées par un utilisateur.
	 * 
	 * @param  array() $ligne la varaiblecontient un tableau associatif des nomenclatures
	 * @param  array() $colonne  la variable  contient un tableau associatif des lignes la
	 * @param array() $valeurs  la variable contient un tableau associatif de la matrice la
	 * @param  numeric $code_etablissement variable  contient l identifiant de l établissement
	 */
	 
	// Données d'échange entre la partie métier et la partie affichage
	
	 /**
	 * Attribut : ligne
	 * <pre>
	 *  Contient les valeurs (codes) provenant de sqlligne
	 * </pre>
	 * @var array()
	 * @access public
	 */
	public $ligne	=	array();
	
	
	 /**
	 * Attribut : colonne
	 * <pre>
	 *  Contient les valeurs (codes) provenant de sqlcolonne
	 * </pre>
	 * @var array()
	 * @access public
	 */
	public $colonne	=	array();	
	
	 /**
	 * Attribut : valeurs
	 * <pre>
	 * Dans cette variable sont rangées les données extraites 
	 * de la base devant etre afficher
	 * dans la matrice
	 * </pre> 
	 * @var array ()
	 * @access public
	 */
	public $valeurs	=	array();
	
	
	/**
	 * Attribut : matrice_donnees
	 * <pre>
	 * Dans cette variable on prépare les données de $valeurs pour 
	 * s'accomoder à la logique d'affichage imposée par la matrice 
	 * </pre> 
	 * @var array ()
	 * @access public
	 */
	public $matrice_donnees	=	array();
	
	
	 /**
	 * Attribut : matrice_donnees_post
	 * <pre>
	 * Cette variable est configurée aprés soumission de la page HTML
	 * via POST, sous la même base 
	 * de présentation  que $valeurs
	 * </pre> 
	 * @var array ()
	 * @access public
	 */
	 public $matrice_donnees_post	=	array(); 
	 
	 
	 /**
	 * Attribut : matrice_donnees_bdd
	 * <pre>
	 * Cette variable est le résultat de la comparaison entre les 
	 * données de départ et celles du POST
	 * Le contenu de cette variable sera utlisé pour la MAJ de 
	 * la base de données
	 * </pre> 
	 * @var array ()
	 * @access public
	 */	
	public $matrice_donnees_bdd	=	array(); 
	
	
	/**
	 * Attribut : code_etablissement
	 * <pre>
	 * On choisit toujours de se rapporter à un établissement donné
	 * </pre>
	 * @var numeric
	 * @access public
	 */   
	public $code_etablissement;
	
	
	/**
	 * Attribut : code_annee
	 * <pre>
	 * On se positionne sur une année d'étude
	 * </pre>
	 * @var numeric
	 * @access public
	 */	
	public $code_annee;
	
	/**
	 * Attribut : code_filtre
	 * <pre>
	 * On se positionne sur une année d'étude
	 * </pre>
	 * @var numeric
	 * @access public
	 */	
	public $code_filtre;
		
	/**
	 * Attribut : code_systeme
	 * <pre>
	 * Utilisé si le systême d'enseigement est à prendre en 
	 * considération
	 * </pre>
 	 * @var numeric
	 * @access public
	 */
	public $code_systeme;
	
	
	// Paramètres provenant de dico
	/**
	 * Attribut : nomtableligne
	 * <pre>
	 * Contient le nom de la table de dimension ligne
	 * </pre>
	 * @var string
	 * @access public
	 */
	public $nomtableligne;
	
	
	/**
	 * Attribut : champcodeligne
	 * <pre>
	 * Contient le nom du champ relatif aux codes dimension ligne
	 * </pre>
	 * @var string
	 * @access public
	 */
	public $champcodeligne;
	
	
	/**
	 * Attribut : champlibelleligne
	 * <pre>
	 * Contient le nom du champ relatif aux libellés dimension ligne
	 * </pre>
	 * @var string
	 * @access public
	 */
	public $champlibelleligne;
	
	
	 /**
	 * Attribut : nomtablecolonne
	 * <pre>
	 * Contient le nom de la table de dimension colonne
	 * </pre>
	 * @var string
	 * @access public
	 */
	public $nomtablecolonne;
	
	
	 /**
	 * Attribut : champcodecolonne
	 * <pre>
	 * Contient le nom du champ relatif aux codes dimension colonne
	 * </pre>
	 * @var string
	 * @access public
	 */
	public $champcodecolonne;
	
	
	/**
	* Attribut : champlibellecolonne
	 * <pre>
	 * Contient le nom du champ relatif aux libellés dimension colonne
	 * </pre>
	 * @var string
	 * @access public
	 */
	public $champlibellecolonne;
	
	
	 /**
	 * Attribut : nomtableliee
	 * <pre>
	 * Contient la table mère, table devrant etre mise à jour
	 * </pre>
	 * @var array ()
	 * @access public
	 */
	public $nomtableliee;
	
	
	/**
	 * Attribut : mesures
	 * <pre>
	 * Contient la (les) mesure(s) (champ(s) de la table mère )
	 * </pre>
	 * @var array ()
	 * @access public
	 */
	public $mesures = array();
	
	
	 /**
	 * Attribut : classe
	 * <pre>
	 * Contient le nom de la classe (ici grille)
	 * </pre>
	 * @var string 
	 * @access public
	 */	
	public $classe;
	
	
	 /** 
	 * Attribut : template
	 * <pre>
	 * On récupère dans cette variable le contenu page HTML du 
	 * template à afficher
	 * Le chemin du template est recuperer depuis DICO
	 * </pre> 
	 * @var string
	 * @access public
	 */	
	public $template;
	
	
		/**
	 * Attribut : sqlligne
	 * <pre>
	 * Requête permettant d'extraire les codes de la dimension 
	 * ligne 
	 * </pre>
	 * @var string
	 * @access public
	 */
	public $sqlligne;
	
	
		/**
	 * Attribut : sqlcolonne
	 * <pre>
	 * Requête permettant d'extraire les codes de la dimension colonne 
	 * </pre>
	 * @var string
	 * @access public
	 */
	public $sqlcolonne;
	
	
	// La variable connexion à la base de données
	 /**
	 * Attribut : conn
	 * <pre>
	 * Dans cette variable on extrait la variable globale de 
	 * connexion à la la base pour en faire une
	 * propriété de la classe
	 * </pre>
 	 * @var adodb.connection
	 * @access public
	 */	
	public  $conn ;
	
	
	 /**
	 * Attribut : type_matrice
	 * <pre>
	 * Dans cette variable on saura s'il s'agit d'une matrice 
	 * ligne ET/OU colonne
	 * </pre>
 	 * @var string
	 * @access public
	 */	
	public $type_matrice;
    	
	 /**
	 * Attribut : type_saisie_mes_1
	 * <pre>
	 * Type de saisie de la mesure 1 : text ou checkbox
	 * </pre>
 	 * @var string
	 * @access public
	 */	
    public $type_saisie_mes_1;
    
	 /**
	 * Attribut : type_saisie_mes_2
	 * <pre>
	 * Type de saisie de la mesure 2 : text ou checkbox
	 * </pre>
 	 * @var string
	 * @access public
	 */	
    public $type_saisie_mes_2;
	 
     /**
	 * Attribut : nb_cles
	 * <pre>
	 * Contient le nombre de clés définies dans la table mère
	 * </pre>
	 * @var numeric
	 * @access public
	 */
	public $nb_cles;
	
	
	/**
	 * Attribut : dimensions
	 * <pre>
	 * Contient le (les) champ(s) dimensions(s) 
	 * ( champcodeligne ET/OU champcodecolonne )
	 * </pre>
	 * @var array()
	 * @access public
	 */
	public $dimensions;
	
	 /**
	 * Attribut : id_theme
	 * <pre>
	 * Variable contenant le thème en cours de traitement
	 * </pre>
 	 * @var numeric
	 * @access public
	 */	
	 public $id_theme;
		
		/**
	 * Attribut : tab_regles_zone
	 * <pre>
	 * Cette variable nous permet de stocker les differentes règles et
	 * type de contrôle de saisie relatifs aux zones de saisies
	 * </pre> 
	 * @var array()
	 * @access public
	 */
	 public $tab_regles_zone = array();

	
	/**
	 * METHODE : __construct(code_etablis_, code_annee, id_theme, id_systeme)
	 * <pre>
 	 * Constructeur de la classe :
	 * </pre>
	 * @access public
	 * @param numeric $code_etablissement le code de l'établissement
	 * @param numeric $code_annee le code année
	 * @param numeric $id_theme : le thème de référence ds DICO 
	 * @param numeric $id_systeme : le systeme d'enseignement couplé au thème 
	 */ 	 

    function __construct($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre=""){
        
    	$this->code_etablissement				= $code_etablissement;        
        $this->conn								= $GLOBALS['conn'];
        $this->code_annee						= $code_annee;
		$this->code_filtre						= $code_filtre;
		$this->id_theme 						= $id_theme;
		$this->id_systeme 						= $id_systeme;
		
		$this->get_dico($id_theme, $id_systeme);
		$dico	=	$GLOBALS['dico'];

		if (isset($dico['nomtableligne'])) 			{$this->nomtableligne=$dico['nomtableligne']; }        
        if (isset($dico['champcodeligne'])) 		{$this->champcodeligne=$dico['champcodeligne'];}        
        if (isset($dico['champlibelleligne'])) 		{$this->champlibelleligne=$dico['champlibelleligne'];} 
        
        if (isset($dico['nomtablecolonne'])) 		{$this->nomtablecolonne=$dico['nomtablecolonne'];}        
        if (isset($dico['champcodecolonne'])) 		{$this->champcodecolonne=$dico['champcodecolonne'];}        
        if (isset($dico['champlibellecolonne'])) 	{$this->champlibellecolonne=$dico['champlibellecolonne'];}  
        if (isset($dico['nomtableliee'])) 			{$this->nomtableliee=$dico['nomtableliee'];}
        
        if (isset($dico['mesure1'])) 				{$this->mesures['mesure1']=$dico['mesure1'];}
        if (isset($dico['mesure2'])) 				{$this->mesures['mesure2']=$dico['mesure2'];}  
        
        if (isset($dico['type_saisie_mes_1'])) 				{$this->type_saisie_mes_1=$dico['type_saisie_mes_1'];} 
        if (isset($dico['type_saisie_mes_2'])) 				{$this->type_saisie_mes_2=$dico['type_saisie_mes_2'];}        
       
        if (isset($dico['classe'])) 			    {$this->classe=$dico['classe'];}
        //if (isset($dico['template']))				{$this->template 	= file_get_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template']);}
				if (file_exists($GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template'])) {
						$this->template 	= file_get_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template']);
				}else{
						 print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template'] . ' : Inexistant !<BR>';
				}

				if (isset($dico['sqlligne'])) 			    {$this->sqlligne=$dico['sqlligne'];}
        if (isset($dico['sqlcolonne'])) 		    {$this->sqlcolonne=$dico['sqlcolonne'];}        
        if (isset($dico['code_systeme'])) 			{$this->code_systeme=$dico['code_systeme'];} 
        if (isset($dico['code_annee'])) 			{$this->code_annee=$dico['code_annee'];}
		if (isset($dico['code_filtre'])) 			{$this->code_filtre=$dico['code_filtre'];}
        if (isset($dico['type_matrice'])) 			{$this->type_matrice=$dico['type_matrice'];}
        if (isset($dico['nb_cles'])) 				{$this->nb_cles=$dico['nb_cles'];}
				if (isset($dico['dimensions'])) 			{$this->dimensions=$dico['dimensions']; }
      }
	
	
	/**
	*METHODE :  get_dico( id_theme,  id_systeme)
	*<pre>
	*DEBUT
	*	Sql1 <-- requête de selection ds DICO_THEME  et DICO_THEME_SYSTEME en   
	* fonction de id_theme et id_systeme
	*	Sql2 <-- requête de selection de mesures et TABLE_MERE ds DICO_ZONE en      
	*	fonction de id_theme et de  id_systeme
	*	Sql3 <-- requête de selection de TYPE_DIMENSION , TABLE_REF , 
	*	CHAMP , DIM_LIBELLE , DIM_SQL       
	*	fonction de id_theme et de  id_systeme
	*	PREPARER LA VARIABLE dico[]    ( exploté par __CONSTRUCT() )
	*FIN
	*</pre>
    
    * Cette function get_dico() permet de lire la configuration du thème matriciel à partir du dictionnaire
    * @access public	
	* @param numeric $id_theme : le thème de référence ds DICO 
	* @param numeric $id_systeme : le systeme d'enseignement couplé au thème 
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
		//Fin ajout Hebie
		// communication et récuperation des paramètres de Dico
		$sql 						= ' SELECT A.*, B.FRAME, B.NB_LIGNES_FRAME  FROM DICO_THEME  A, DICO_THEME_SYSTEME B ' .
									  ' WHERE A.ID=B.ID '.
									  ' AND A.ID='.$id_theme.
									  ' AND B.ID_SYSTEME='.$id_systeme;
											  
		unset($dico);
		$conn						= $GLOBALS['conn'];
				
		// Traitement Erreur Cas : Execute / GetOne
		try {            
				$rs							= $GLOBALS['conn_dico']->Execute($sql);
				if($rs ===false){                
						 throw new Exception('ERR_SQL');   
				}
				$dico['nb_lignes']		= $rs->fields['NB_LIGNES_FRAME'];
				$dico['template']			= $rs->fields['FRAME'];
				$dico['matrice']			= $rs->fields['CLASSE'];
		}
		catch (Exception $e) {
				 $erreur = new erreur_manager($e,$sql);
		}        
		// Fin Traitement Erreur Cas : Execute / GetOne
											
		$sql_type_mat         = "SELECT     DICO_DIMENSION_ZONE.TYPE_DIMENSION
														FROM        DICO_DIMENSION_ZONE, DICO_ZONE, DICO_THEME, DICO_ZONE_SYSTEME, DICO_THEME_SYSTEME
														WHERE				DICO_DIMENSION_ZONE.ID_ZONE = DICO_ZONE.ID_ZONE 
														AND					DICO_ZONE.ID_THEME = DICO_THEME.ID 
														AND         DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE
														AND         DICO_THEME.ID = DICO_THEME_SYSTEME.ID
														AND		     	DICO_ZONE_SYSTEME.ACTIVER = 1
														AND 				DICO_THEME.ID = ".$id_theme."
														AND 				DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme."
														AND 				DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme;

		
			
		// Traitement Erreur Cas : GetAll / GetRow
		try {
				$res_type_mat  = $GLOBALS['conn_dico']->GetAll($sql_type_mat);
				if(!is_array($res_type_mat)){                    
						throw new Exception('ERR_SQL');  
				}		
				if( count($res_type_mat) == 2 ) // type 'lc'
						$dico['type_matrice'] ='lc';
				elseif( $res_type_mat[0]['TYPE_DIMENSION'] == 1 ) // type 'l'
						$dico['type_matrice'] ='l';
				elseif( $res_type_mat[0]['TYPE_DIMENSION'] == 2 ) // type 'c'
						$dico['type_matrice'] ='c';
		}
		catch(Exception $e){
				$erreur = new erreur_manager($e,$sql_type_mat);
		}
		// Fin Traitement Erreur Cas : GetAll / GetRow			
		
		//echo "<br>SQL TYPE:".$sql_type_mat."<br>";
		//$dico['type_matrice']		= $rs->fields['TYPE_MATRICE'];
		
		$dico['nb_cles']			= 0; // $rs->fields['NB_CLES']  ;
		$dico['code_systeme']	= $id_systeme;
		
						 
		$sql_zone_			=' SELECT DICO_ZONE.*'.
										 ' FROM DICO_ZONE,DICO_ZONE_SYSTEME'.			 
										 ' WHERE DICO_ZONE.ID_ZONE=DICO_ZONE_SYSTEME.ID_ZONE'.
										 ' AND DICO_ZONE_SYSTEME.ID_SYSTEME ='.$id_systeme.
										 ' AND DICO_ZONE_SYSTEME.ACTIVER =1'.
										 ' AND DICO_ZONE.ID_THEME ='.$id_theme ;	
									 									
        // Traitement Erreur Cas : Execute / GetOne
		try {            
				$rs	= $GLOBALS['conn_dico']->Execute($sql_zone_);
				if($rs === false){                
						 throw new Exception('ERR_SQL');   
				}
        if(!$rs->EOF) {

            $dico['mesure1']			= $rs->fields['MESURE1'];		
            $dico['mesure2']			= $rs->fields['MESURE2'];		
            $dico['nomtableliee']	    = $rs->fields['TABLE_MERE'];
            
            $dico['type_saisie_mes_1']	= $rs->fields['TYPE_SAISIE_MESURE1'];
            $dico['type_saisie_mes_2']	= $rs->fields['TYPE_SAISIE_MESURE2'];
    
            $sql_regles =" 	SELECT  *
                                            FROM   DICO_REGLE_ZONE, DICO_REGLE_ZONE_ASSOC 
                                            WHERE DICO_REGLE_ZONE.ID_REGLE_ZONE = DICO_REGLE_ZONE_ASSOC.ID_REGLE_ZONE
                                            AND    DICO_REGLE_ZONE_ASSOC.ID_ZONE =".$rs->fields['ID_ZONE'];
            
						// Traitement Erreur Cas : Execute / GetOne
						try {            
								$rs_regles	=	$GLOBALS['conn_dico']->Execute($sql_regles);
								if($rs_regles === false){                
										 throw new Exception('ERR_SQL');   
								}
								if($rs_regles->RecordCount()>0){
										//echo "<br> sql_regles = $sql_regles";
										$nom_champ = 'MATRICE' ;
										$this->tab_regles_zone[$nom_champ] = array();
										while(!$rs_regles->EOF){
												$id_regle 							= $rs_regles->fields['ID_REGLE_ZONE'] ;
												$type_donnees 					= $rs_regles->fields['TYPE_DONNEES'] ;
												$taille_donnees 				= $rs_regles->fields['TAILLE_DONNEES'] ;
												$format_donnees 				= $rs_regles->fields['FORMAT_DONNEES'] ;
												$intervalle_valeurs 		= $rs_regles->fields['INTERVALLE_VALEURS'] ;
												$valeur_minimale 				= $rs_regles->fields['VALEUR_MINIMALE'] ;
												$valeur_maximale 				= $rs_regles->fields['VALEUR_MAXIMALE'] ;
												$controle_presence 			= $rs_regles->fields['CONTROLE_PRESENCE'] ;
												$controle_parution 			= $rs_regles->fields['CONTROLE_PARUTION'] ;
												$controle_obligation 		= $rs_regles->fields['CONTROLE_OBLIGATION'] ;
												$controle_integrite_ref = $rs_regles->fields['CONTROLE_INTEGRITE_REF'] ;
												$controle_edition 			= $rs_regles->fields['CONTROLE_EDITION'] ;
												$vals_enum 			= $rs_regles->fields['VALEURS_ENUM'] ;
												
												if($type_donnees) $this->tab_regles_zone[$nom_champ][$id_regle]['type_donnees'] 	=	$type_donnees;
																
												if($taille_donnees)	$this->tab_regles_zone[$nom_champ][$id_regle]['taille_donnees'] =	$taille_donnees;
			
												if($format_donnees)	$this->tab_regles_zone[$nom_champ][$id_regle]['format_donnees'] =	$format_donnees;
			
												if($intervalle_valeurs)	$this->tab_regles_zone[$nom_champ][$id_regle]['intervalle_valeurs'] =	$intervalle_valeurs;
			
												if($valeur_minimale)	$this->tab_regles_zone[$nom_champ][$id_regle]['valeur_minimale'] =	$valeur_minimale;
			
												if($valeur_maximale)	$this->tab_regles_zone[$nom_champ][$id_regle]['valeur_maximale'] =	$valeur_maximale;
			
												if($controle_presence)	$this->tab_regles_zone[$nom_champ][$id_regle]['controle_presence'] =	$controle_presence;
			
												if($controle_parution)	$this->tab_regles_zone[$nom_champ][$id_regle]['controle_parution'] =	$controle_parution;
			
												if($controle_obligation)	$this->tab_regles_zone[$nom_champ][$id_regle]['controle_obligation'] =	$controle_obligation;
			
												if($controle_integrite_ref)	$this->tab_regles_zone[$nom_champ][$id_regle]['controle_integrite_ref'] =	$controle_integrite_ref;
			
												if($controle_edition)	$this->tab_regles_zone[$nom_champ][$id_regle]['controle_edition'] =	$controle_edition;
												
												if($vals_enum)	$this->tab_regles_zone[$nom_champ][$id_regle]['vals_enum']           =	$vals_enum;
												
												$rs_regles->MoveNext();
										}
										//echo"$nom_champ<pre>";
										//print_r(	$tab_regles_zone[$nom_champ]);
								}
						}
						catch (Exception $e) {
								 $erreur = new erreur_manager($e,$sql_regles);
						}        
						// Fin Traitement Erreur Cas : Execute / GetOne

            $sql_indexes ="SELECT * FROM DICO_INDEXES WHERE ID_ZONE =".$rs->fields['ID_ZONE'];
            
						// Traitement Erreur Cas : GetAll / GetRow
						try {
								$res_indexes  = $GLOBALS['conn_dico']->GetAll($sql_indexes);
								if(!is_array($res_indexes)){                    
										throw new Exception('ERR_SQL');  
								} 
								foreach($res_indexes as $champs_indexes){
										if (trim($champs_indexes['CHAMP_INDEXE'])==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
												$this->val_cle[] 	= array($GLOBALS['PARAM']['CODE_ETABLISSEMENT'],$this->code_etablissement);
										}
										elseif (trim($champs_indexes['CHAMP_INDEXE'])==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']){
												$this->val_cle[] 	= array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$this->code_annee);
										}
										elseif (trim($champs_indexes['CHAMP_INDEXE'])==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']){
												$this->val_cle[] 	= array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'],$this->code_filtre);
										}
										elseif(trim($champs_indexes['VALEUR_DEFAUT_INDEXE']) <> ''){
												$eval = '$val_defaut = '.$champs_indexes['VALEUR_DEFAUT_INDEXE'].';';
												//echo '<br>'.$champs_indexes['VALEUR_DEFAUT_INDEXE'];
												eval($eval);
												$this->val_cle[] 	= array($champs_indexes['CHAMP_INDEXE'],$val_defaut);
										}
								}
						}
						catch(Exception $e){
								$erreur = new erreur_manager($e,$sql_indexes);
						}
						// Fin Traitement Erreur Cas : GetAll / GetRow						
        }
		}catch (Exception $e) {
				 $erreur = new erreur_manager($e,$sql_zone_);
		}        
		// Fin Traitement Erreur Cas : Execute / GetOne
		//echo'<pre>';
		//print_r($this->val_cle);

		// On récupère les dimensions de la matrice.
						
		$sql 		= 	' SELECT A.*, B.TYPE_DIMENSION FROM DICO_DIMENSION A, DICO_DIMENSION_ZONE B, '.
						' DICO_ZONE C, DICO_ZONE_SYSTEME D '.
						' WHERE A.ID_DIMENSION = B.ID_DIMENSION ' .
						' AND B.ID_ZONE = C.ID_ZONE '.
						' AND D.ID_ZONE	= C.ID_ZONE '.
						' AND D.ID_SYSTEME='.$id_systeme.
						' AND D.ACTIVER=1'.				
						' AND C.ID_THEME ='.$id_theme.
						' ORDER BY B.TYPE_DIMENSION ';
	//	echo "$sql<br>";
		$dimensions	=	array();

		// Traitement Erreur Cas : Execute / GetOne
		try {            
				$rs			= $GLOBALS['conn_dico']->Execute($sql);
				if($rs === false){                
						 throw new Exception('ERR_SQL');   
				}
				while (!$rs->EOF){
		
					if ($rs->fields['TYPE_DIMENSION'] == 1){
						// Champs ligne
						$dico['nomtableligne']		= $rs->fields['TABLE_REF'];
						$dico['champcodeligne']		= $rs->fields['CHAMP'];
						$dico['champlibelleligne']	= $rs->fields['DIM_LIBELLE'];	
						$dico['sqlligne']			= $rs->fields['DIM_SQL'];
						
						$temp_sql	=	$rs->fields['DIM_SQL'];
						$chaine_eval ="\$dico['sqlligne'] =\"$temp_sql\";";						
						eval ($chaine_eval);
						//echo "<br>sqlligne=".$dico['sqlligne']."<br>";
						
					}elseif($rs->fields['TYPE_DIMENSION'] == 2){
						// Champs colonne
						$dico['nomtablecolonne']		= $rs->fields['TABLE_REF'];
						$dico['champcodecolonne']		= $rs->fields['CHAMP'];
						$dico['champlibellecolonne']	= $rs->fields['DIM_LIBELLE'];
						
						$temp_sql	=	$rs->fields['DIM_SQL'];
						$chaine_eval ="\$dico['sqlcolonne'] =\"$temp_sql\";";						
						eval ($chaine_eval);
					//	echo "<br>sqlcolonne=".$dico['sqlcolonne']."<br>";
					}
					
					$dimensions[]	=	$rs->fields['CHAMP'];
					$dico['nb_cles']	=	$dico['nb_cles']	+	1;
					$rs->MoveNext();
				}												 
		}
		catch (Exception $e) {
				 $erreur = new erreur_manager($e,$sql);
		}        
		// Fin Traitement Erreur Cas : Execute / GetOne
		
		$dico['dimensions']		=	$dimensions;
		//Manuel eleve
		if (!isset($dico['classe'])){
				$dico['classe']='matrice';
		}
		$GLOBALS['dico']	=	$dico;		
	}

	/**
	* METHODE :  get_champ_extract($nom_champ):
	* Retourne les 30 ou 31 premiers caractères de $nom_champ en fonction du type de SGBD 
    * pour extraire la valeur du champ $nom_champ dans un recordset
	*
	* @access public
    * @param String $nom_champ est la variable textuel dans laquelle l'extraction est faite.
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
			$champ_extract = substr($nom_champ ,0,$taille_max_extract); 
		}
		return($champ_extract);
	}

	// Récupération de la dimension ligne de la matrice
	/**
	*METHODE : get_ligne(sqlligne)
	*<pre>
	*DEBUT
	*	rs <-- Record_Set(sqlligne)
	*	ligne[] <-- AJOUTER( nomtableligne , rs(champcodeligne) 
	*                      , rs(champlibelleligne) )
	*FIN
	*</pre>
	 * @access public
     * @param String $var_sql contient le texte SQL LIGNE à exécuter
	 */
   function get_ligne($var_sql){
        // Initialisation de la matrice de la dimension ligne
			//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
			$id_systeme = $this->id_systeme;
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
			//Fin ajout Hebie
			
			if(!isset($var_sql)) $var_sql = $this->sqlligne;
			if(!is_null($var_sql)){
				$chaine_eval ="\$sql =\"$var_sql\";";						
				eval ($chaine_eval);
				// Traitement Erreur Cas : Execute / GetOne
				try {            
						$rs	= $this->conn->Execute($sql);
						if($rs === false){                
								 throw new Exception('ERR_SQL');   
						}
						while(!$rs->EOF){
								$elt['nomtable']	= $this->nomtableligne;        
								$elt['code']			= $rs->fields[$this->get_champ_extract($this->champcodeligne)];
								$elt['libelle']		= $rs->fields[$this->get_champ_extract($this->champlibelleligne)];           
								$this->ligne[]		= $elt;            
								$rs->MoveNext();              
						}
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$sql);
				}        
				// Fin Traitement Erreur Cas : Execute / GetOne
			}
    }
    
    
		// Récupération de la dimension colonne de la matrice
    /**
		*METHODE :  get_colonne(sqlcolonne)
		*<pre>
		*DEBUT
		*	rs <-- Record_Set(sqlcolonne)
		*	colonne [] <-- AJOUTER( nomtablecolonne , rs(champcodecolonne) 
		*                        , rs(champlibellecolonne) )
		*FIN
		*</pre>
		 * @access public
         * @param String $var_sql contient le texte SQL COLONNE à exécuter
		 */
   function get_colonne($var_sql){
        // Initialisation de la dimension colonne
			//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
			$id_systeme = $this->id_systeme;
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
			//Fin ajout Hebie
			
			if(!isset($var_sql)) $var_sql = $this->sqlcolonne;
			if(!is_null($var_sql)){
				$chaine_eval ="\$sql =\"$var_sql\";";						
				eval ($chaine_eval);
				//echo"<br>sql_get_colonne=$sql<br>";
				// Traitement Erreur Cas : Execute / GetOne
				try {            
						$rs = $this->conn->Execute($sql);
						if($rs === false){                
								 throw new Exception('ERR_SQL');   
						}
						while(!$rs->EOF){
								$elt['nomtable']	= $this->nomtablecolonne;        
								$elt['code']		= $rs->fields[$this->get_champ_extract($this->champcodecolonne)];
								$elt['libelle']		= $rs->fields[$this->get_champ_extract($this->champlibellecolonne)];        
								$this->colonne[]	= $elt;
								$rs->MoveNext();              
						} 
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$sql);
				}        
				// Fin Traitement Erreur Cas : Execute / GetOne
			}
			//echo'<pre>';
			//print_r($this->colonne);
    }
    
    /**
	* METHODE :  get_criteres_sql():
	* Cette fonction permet de lire les critères SQL stockée dans le dictionnaire
	* @access public    
	* 
	*/
    function get_criteres_sql(){
        $pass = 0;
        $criteres_sql = '';
        if(is_array($this->val_cle)){
            foreach($this->val_cle as $val_cle){
                if($pass == 0){
                    $and = ' WHERE ';
                }else{
                    $and = ' AND ';
                }
                $criteres_sql .= $and . $val_cle[0] . ' = ' .$val_cle[1];
                $pass++;
            }
        }
        return($criteres_sql);
    }
    
    /**
		*METHODE :  get_matrice()
		*<pre>
		*DEBUT
		*	sql <-- CONSTITUER_REQUETE ( type_matrice , mesures )
		*	valeurs <-- Record_Set(sql)
		*FIN
		*</pre>
        * Cette fonction permet de lire les données de la matrice à partir de la base de données
		* @access public
		*/
    function get_matrice(){    	
 		$type_matrice = $this->type_matrice;
        switch ($type_matrice)
        {
        	
        	case 'lc': 
        		
        		// matrice à deux dimensions ligne et colonne
        		  if ($this->mesures['mesure1'] and $this->mesures['mesure2']){
        		  	  $sql 	= 'SELECT '.$this->champcodeligne.','.$this->champcodecolonne.','.$this->mesures['mesure1'].','.$this->mesures['mesure2'].' FROM '.$this->nomtableliee
			          		.$this->get_criteres_sql() ." ORDER BY ".$this->champcodeligne.",".$this->champcodecolonne;			          		
        		  }elseif ($this->mesures['mesure1']){
        		  	  $sql	= 'SELECT '.$this->champcodeligne.','.$this->champcodecolonne.','.$this->mesures['mesure1'].' FROM '.$this->nomtableliee
			          		.$this->get_criteres_sql() ." ORDER BY ".$this->champcodeligne.",".$this->champcodecolonne;
        		  }elseif ($this->mesures['mesure2']){
        		  	   $sql = 'SELECT '.$this->champcodeligne.','.$this->champcodecolonne.','.$this->mesures['mesure2'].' FROM '.$this->nomtableliee
			          		. $this->get_criteres_sql() ." ORDER BY ".$this->champcodeligne.",".$this->champcodecolonne;
        		  }	        		  
        		  
		          break;           
           	case 'l':
           		// matrice ligne uniquement, une seule dimension
           		             		 
           		  if ($this->mesures['mesure1'] and $this->mesures['mesure2']){
		           		$sql =	'SELECT '.$this->champcodeligne.','.$this->mesures['mesure1'].",".$this->mesures['mesure2'].' FROM '.$this->nomtableliee
				          		. $this->get_criteres_sql() .' ORDER BY '.$this->champcodeligne;
           		  }elseif($this->mesures['mesure1']){
           		  		$sql =	'SELECT '.$this->champcodeligne.','.$this->mesures['mesure1'].' FROM '.$this->nomtableliee
				          		. $this->get_criteres_sql() .' ORDER BY '.$this->champcodeligne;
           		  }elseif ($this->mesures['mesure2']){
           		  		$sql =	'SELECT '.$this->champcodeligne.','.$this->mesures['mesure2'].' FROM '.$this->nomtableliee
				          		. $this->get_criteres_sql() .' ORDER BY '.$this->champcodeligne;
           		  }		
		         
		          break; 
        	case 'c':
        		 // matrice colonne uniquement, une seule dimension
        		   
        		  if ($this->mesures['mesure1'] and $this->mesures['mesure2'])
        		  {
		        		$sql =	'SELECT '.$this->champcodecolonne.','.$this->mesures['mesure1'].','.$this->mesures['mesure2'].' FROM '.$this->nomtableliee
				          		. $this->get_criteres_sql() .' ORDER BY '.$this->champcodecolonne;
        		  }elseif ($this->mesures['mesure1'])         		  		 
        		  {
        		  		$sql =	'SELECT '.$this->champcodecolonne.','.$this->mesures['mesure1'].' FROM '.$this->nomtableliee
				          		. $this->get_criteres_sql() .' ORDER BY '.$this->champcodecolonne;
        		  }elseif ($this->mesures['mesure2'])
        		  {
        		  		$sql =	'SELECT '.$this->champcodecolonne.','.$this->mesures['mesure2'].' FROM '.$this->nomtableliee
				          		. $this->get_criteres_sql() .' ORDER BY '.$this->champcodecolonne;
        		  }		
		          break;        	
        }
				// Traitement Erreur Cas : Execute / GetOne
				if($sql)
				try {            
						$rs	= $this->conn->Execute($sql);
						if($rs === false){                
								 throw new Exception('ERR_SQL');   
						}
						while (!$rs->EOF){
								switch ($type_matrice)
								{
										case 'lc':
											{
												if ($this->mesures['mesure1'] and $this->mesures['mesure2'])
												{
													$val[0]			= $rs->fields[$this->get_champ_extract($this->champcodeligne)];
													$val[1]			= $rs->fields[$this->get_champ_extract($this->champcodecolonne)];            
													
													if (($rs->fields[$this->get_champ_extract($this->mesures['mesure1'])])!=''){
															$val[2]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure1'])];
													}else{
															$val[2]			= '';
													}
													
													if (($rs->fields[$this->get_champ_extract($this->mesures['mesure2'])])!=''){
															$val[3]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure2'])];
													}else{
															$val[3]			= '';
													}		
													
												}elseif ($this->mesures['mesure1']){
													$val[0]			= $rs->fields[$this->get_champ_extract($this->champcodeligne)];
													$val[1]			= $rs->fields[$this->get_champ_extract($this->champcodecolonne)];              
													$val[2]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure1'])];	              				
												}elseif ($this->mesures['mesure2'])
												{
													$val[0]			= $rs->fields[$this->get_champ_extract($this->champcodeligne)];
													$val[1]			= $rs->fields[$this->get_champ_extract($this->champcodecolonne)];              
													$val[2]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure2'])];	    
												}		
												break;
											} 
										case 'l':
											{
												if ($this->mesures['mesure1'] and $this->mesures['mesure2'])
												{
													$val[0]			= $rs->fields[$this->get_champ_extract($this->champcodeligne)];	              			              
													$val[1]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure1'])];
													$val[2]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure2'])];
												}elseif ($this->mesures['mesure1'])
												{
													$val[0]			= $rs->fields[$this->get_champ_extract($this->champcodeligne)];	              			              
													$val[1]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure1'])];		              			
												}elseif ($this->mesures['mesure1'])
												{
													$val[0]			= $rs->fields[$this->get_champ_extract($this->champcodeligne)];	              			              
													$val[1]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure2'])];
												}		
												break;
											} 
										case 'c':
											{
												if ($this->mesures['mesure1'] and $this->mesures['mesure2'])
												{
													$val[0]			= $rs->fields[$this->get_champ_extract($this->champcodecolonne)];	              			              
													$val[1]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure1'])];
													$val[2]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure2'])];
												}elseif ($this->mesures['mesure1'])
												{
													$val[0]			= $rs->fields[$this->get_champ_extract($this->champcodecolonne)];	              			              
													$val[1]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure1'])];
												}elseif ($this->mesures['mesure2'])
												{
													$val[0]			= $rs->fields[$this->get_champ_extract($this->champcodecolonne)];	              			              
													$val[1]			= $rs->fields[$this->get_champ_extract($this->mesures['mesure2'])];
												}		
												break;
											}              		
								 }
								$this->valeurs[]	= $val;	
								//reset($val);
								$rs->MoveNext();              
						 }
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$sql);
				}        
				// Fin Traitement Erreur Cas : Execute / GetOne
				 
				 //echo'<pre>';
				 //print_r($this->valeurs);
    }
	
   
		/*
    function verif(){
		if (count($this->matrice_donnees_post)<1)
			exit('aucune valeur dans le post');
    }
		*/

    /**
		*METHODE : prepare_donnees_bdd 
		*<pre>
		*DEBUT
		*	SUIVANT type_matrice ( ‘l’  ET/OU  ‘c’)
		*		POUR i allant de 0 à nb_lignes FAIRE   ET/OU  
		*			POUR j allant de 0 à nb_colonnes FAIRE
		*				SI EXISTE(tab[i][j])
		*					val_i <-- ligne[i]  ET/OU
		*					val_j <-- colonne[j]
		*					val_mesures <-- valeurs de mesure aux coordonnées 
		*													i ET/OU j (tab[i][j])
		*					matrice_donnees_post <-- EMPILER(val_i,val_j,val_mesures)
		*				FIN SI
		*			FIN POUR	
		*		FIN POUR	
		*	FIN SUIVANT
		*FIN
		*</pre>
        * Cette fonction permet de formatter les données conformement à la structure de la tabl
        * @access public
        * @param numeric $type_matrice contient le type de matrice
        * l= matrice ligne uniquement
        * c= matrice colonne uniquement
        * lc= matrice ligne, colonne
        * @param array() $tab contient la matrice de données
        * @param numeric $nbligne contient le nb de ligne
        * @param numeric $nbcolonne contient le nb de colonne
		*/
		function prepare_donnees_bdd($type_matrice,$tab,$nbligne,$nbcolonne) {
    	$k = 0;
    	switch($type_matrice)
    	{
    		case 'lc':
    		{
    			for ($i=0; $i<=$nbligne;$i++)
		    	{
		    		for($j=0;$j<=$nbcolonne;$j++)
		    		{
		    				if (isset($tab[$i][$j]))
		    				{
		    					$tmp1 	= association_inverse(&$this->ligne,$i); 
								$tmp2 	= association_inverse(&$this->colonne,$j);            				
		            			if ($this->mesures['mesure1'] and $this->mesures['mesure2']){
									
									if (($tab[$i][$j][0])!='') {
										$val1=$tab[$i][$j][0];
									}else{
										$val1='';
									}
									if (($tab[$i][$j][1])!='') {
										$val2=$tab[$i][$j][1];
									}else{
										$val2='';
									}		
										
									$this->matrice_donnees_post[$k] =array($tmp1,$tmp2, $val1,$val2);
		            			}else {
		            				$this->matrice_donnees_post[$k] =array($tmp1,$tmp2, $tab[$i][$j]);		            				
		            			}	
								$k++;
		    				}
		    		}	
		    	}
		    	break;	
    		}	
    		case 'l':
    		{
    			for ($i=0; $i<=$nbligne; $i++)
		    	{		    		
    				if (isset($tab[$i]))
    				{
    					$tmp1 	= association_inverse(&$this->ligne,$i);			            				
            			
            			if ($this->mesures['mesure1'] and $this->mesures['mesure2']){	
							$this->matrice_donnees_post[$k] =array($tmp1, $tab[$i][0],$tab[$i][1]);

            			}else{
            				$this->matrice_donnees_post[$k] =array($tmp1, $tab[$i]);
            			}	
						$k++;
    				}		 	    			
		    	}
		    	break;	
    		}	
    		case 'c':
    		{
    			for ($j=0; $j<=$nbcolonne;$j++)
		    	{		    		
    				if (isset($tab[$j]))
    				{
    					$tmp1 	= association_inverse(&$this->colonne,$j);			            				
            			
            			if ($this->mesures['mesure1'] and $this->mesures['mesure2']){	
							$this->matrice_donnees_post[$k] =array($tmp1, $tab[$j][0],$tab[$j][1]);
            			}else {
            				$this->matrice_donnees_post[$k] =array($tmp1, $tab[$j]);
            			}	
						$k++;
    				}		 	    			
		    	}
		    	break;	
    		}	
    	}	
    	   	
    }
    
	
	/**
	*METHODE :  prepare_donnees_template ()
	*<pre>
	*DEBUT
	*	SUIVANT type_matrice ( ‘l’  ET/OU  ‘c’ )
	*		PARCOURIR valeurs 
	*			RECUPERER les POSITIONS  i  ET/OU  j dans ligne 
	*			ET/OU  colonne associé
	*			val_mesures <-- mesures extraites de valeurs
	*			matrice_donnees <-- EMPILER ( i , j , val_mesures )
	*		FIN PARCOURIR  
	*	FIN SUIVANT
	*FIN
	*</pre>
    * Cette fonction prepare_donnees_template() permet de transformer les données conformement à 
    * la structure du template.
	* @access public
	*/ 					
	function prepare_donnees_template(){
		$i=0;
		$type_matrice	=	$this->type_matrice;
		//echo $type_matrice;
		while($this->valeurs[$i]){
            switch($type_matrice)
            {
            		case 'lc':
            			{
            				
            				if (!is_null($this->valeurs[$i][2]))
            				{
	            				$tmp1 	= association(&$this->ligne,$this->valeurs[$i][0]); 
											$tmp2 	= association(&$this->colonne,$this->valeurs[$i][1]);
	            				
	            				if ($this->mesures['mesure1'] and $this->mesures['mesure2']){
												$this->matrice_donnees[$tmp1][$tmp2] = array($this->valeurs[$i][2],$this->valeurs[$i][3]);
	            				}elseif ($this->mesures['mesure1'] or $this->mesures['mesure2']){
	            					$this->matrice_donnees[$tmp1][$tmp2] = array($this->valeurs[$i][2]);
	            				}	
            				}
							break;
            			}	
            		case 'l':;
            			{
            				$tmp1 	= association(&$this->ligne,$this->valeurs[$i][0]); 
							
							if ($this->mesures['mesure1'] and $this->mesures['mesure2']){            
								$this->matrice_donnees[$tmp1] = array($this->valeurs[$i][1],$this->valeurs[$i][2]);
							}elseif($this->mesures['mesure1'] or $this->mesures['mesure2']){
								$this->matrice_donnees[$tmp1] = array ($this->valeurs[$i][1]);
							}		
							break;
            			}	
            		case  'c':
            		{
            				$tmp1 	= association(&$this->colonne,$this->valeurs[$i][0]);
            				
            				if ($this->mesures['mesure1'] and $this->mesures['mesure2']){
								$this->matrice_donnees[$tmp1] = array($this->valeurs[$i][1],$this->valeurs[$i][2]);
            				}elseif($this->mesures['mesure1'] or $this->mesures['mesure2']){
            					$this->matrice_donnees[$tmp1] = array($this->valeurs[$i][1]);
            				}
							break;
            			}	
            }	
            
			$i++;
		}
       
	}
	
	
	/**
	*METHODE :  remplit_formulaire (template)
	*<pre>
	*DEBUT
	*	SUIVANT type_matrice ( ‘l’  ET/OU  ‘c’)
	*		POUR i allant de 0 à nb_lignes FAIRE     ET/OU  
	*				POUR j allant de 0 à nb_colonnes FAIRE
	*					PREPARER LA/LES VARIABLE(S) TEMPLATE 
	*					champ(mesures) liée(s) à  i  ET/OU  j	
	*				FIN POUR
	*		FIN POUR		
	*	FIN SUIVANT
	*	EVALUER_ET_AFFICHER(template)
	*FIN
	*</pre>
    * Cette fonction remplit_formulaire() permet de remplir le template
    * @access public
    * @param array() $template contient le flux de caractère du template
	*/ 					
	function remplit_formulaire($template){
		//$template 		= $this->template;
        //echo '<pre>';
        //print_r($this->matrice_donnees);
		$type_matrice 	= $this->type_matrice;
		$champ 					= $this->mesures;
		$nbligne				= count($this->ligne);
		$nbcolonne			= count($this->colonne);
		switch ($type_matrice){
				case 'lc':
                {
						for ($i=0;$i<=$nbligne;$i++){								
							for ($j=0;$j<=$nbcolonne;$j++){                                
                                    if( isset($this->matrice_donnees[$i][$j]) and trim($this->matrice_donnees[$i][$j]) <>'' ){
                                            //$temp='manuel'.$i.$j;
                                            //$champ est un tableau associatif de mesures : une ou deux mesures
                                            //echo 'value = '.$this->matrice_donnees[$i][$j].'<br>';
                                            if($champ['mesure1'] and $champ['mesure2']){
                                                if(trim($this->type_saisie_mes_1) == 'checkbox') {
                                                    $temp=$champ['mesure1'].'_'.$i.'_'.$j;
                                                    if( isset($this->matrice_donnees[$i][$j][0]) and trim($this->matrice_donnees[$i][$j][0]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                                    }else{
                                                        $$temp="'1'";
                                                    }
                                                    
                                                }else{
                                                    $temp=$champ['mesure1'].'_'.$i.'_'.$j;
                                                    $$temp=$this->matrice_donnees[$i][$j][0];
                                                }
                                                if(trim($this->type_saisie_mes_2) == 'checkbox') {
                                                    $temp=$champ['mesure2'].'_'.$i.'_'.$j;
                                                    if( isset($this->matrice_donnees[$i][$j][1]) and trim($this->matrice_donnees[$i][$j][1]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                                    }else{
                                                        $$temp="'1'";
                                                    }
                                                }else{
                                                    $temp=$champ['mesure2'].'_'.$i.'_'.$j;
                                                    $$temp=$this->matrice_donnees[$i][$j][1];
                                                }
                                            }elseif ($champ['mesure1']){
                                                if(trim($this->type_saisie_mes_1) == 'checkbox') {
                                                    $temp=$champ['mesure1'].'_'.$i.'_'.$j;
                                                    if( isset($this->matrice_donnees[$i][$j][0]) and trim($this->matrice_donnees[$i][$j][0]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                                    }else{
                                                        $$temp="'1'";
                                                    }
                                                }else{
                                                    $temp=$champ['mesure1'].'_'.$i.'_'.$j;
                                                    $$temp=$this->matrice_donnees[$i][$j][0];
                                                }
                                            }elseif ($champ['mesure2']){											
                                                if(trim($this->type_saisie_mes_2) == 'checkbox') {
                                                    $temp=$champ['mesure2'].'_'.$i.'_'.$j;
                                                   if( isset($this->matrice_donnees[$i][$j][1]) and trim($this->matrice_donnees[$i][$j][1]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                                    }else{
                                                        $$temp="'1'";
                                                    }
                                                }else{
                                                    $temp=$champ['mesure2'].'_'.$i.'_'.$j;
                                                    $$temp=$this->matrice_donnees[$i][$j][1];
                                                }
                                            //}	
                                            
                                    }
                                }
                                else{
                                        //echo 'pass <br>';
                                        if(trim($this->type_saisie_mes_1) == 'checkbox') {
                                            $temp=$champ['mesure1'].'_'.$i.'_'.$j;
											$$temp='1';
                                        }
                                        if(trim($this->type_saisie_mes_2) == 'checkbox') {
                                            $temp=$champ['mesure2'].'_'.$i.'_'.$j;
											$$temp='1';
                                        }
                                }
							}
						}
						break;
					}
				case 'l':
					{   
						for ($i=0;$i<=$nbligne;$i++){						
							if( isset($this->matrice_donnees[$i])){
									if($champ['mesure1'] and $champ['mesure2']){
                                        if(trim($this->type_saisie_mes_1) == 'checkbox') {
                                            $temp=$champ['mesure1'].'_'.$i;
                                            if( isset($this->matrice_donnees[$i][$j][0]) and trim($this->matrice_donnees[$i][$j][0]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                            }else{
                                                    $$temp="'1'";
                                                    }
                                        }else{
                                            $temp=$champ['mesure1'].'_'.$i;
                                            $$temp=$this->matrice_donnees[$i][0];
                                        }
                                        if(trim($this->type_saisie_mes_2) == 'checkbox') {
                                            $temp=$champ['mesure2'].'_'.$i;
                                            if( isset($this->matrice_donnees[$i][$j][1]) and trim($this->matrice_donnees[$i][$j][1]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                                    }else{
                                                        $$temp="'1'";
                                                    }
                                        }else{
                                            $temp=$champ['mesure2'].'_'.$i;
                                            $$temp=$this->matrice_donnees[$i][1];
										}
									}elseif($champ['mesure1']){
                                        if(trim($this->type_saisie_mes_1) == 'checkbox') {
                                            
                                            $temp=$champ['mesure1'].'_'.$i;
                                            if( isset($this->matrice_donnees[$i][0]) and trim($this->matrice_donnees[$i][0]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                            }else{
                                                        $$temp="'1'";
                                                    }
                                        }else{
                                            $temp=$champ['mesure1'].'_'.$i;
                                            $$temp=$this->matrice_donnees[$i][0];
										}
									}elseif($champ['mesure2']){
                                        if(trim($this->type_saisie_mes_2) == 'checkbox') {
                                            $temp=$champ['mesure2'].'_'.$i;
                                            if( isset($this->matrice_donnees[$i][1]) and trim($this->matrice_donnees[$i][1]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                            }else{
                                                        $$temp="'1'";
                                                    }
                                        }else{
                                            $temp=$champ['mesure2'].'_'.$i;
                                            $$temp=$this->matrice_donnees[$i][0];
                                        }
									}		
							}else{
                                if(trim($this->type_saisie_mes_1) == 'checkbox') {
                                    $temp=$champ['mesure1'].'_'.$i;
                                    $$temp="'1'";
                                }
                                if(trim($this->type_saisie_mes_2) == 'checkbox') {
                                    $temp=$champ['mesure2'].'_'.$i;
                                    $$temp="'1'";
                                }
							}
						}
						break;
					}
				case 'c':
					{
						for ($j=0;$j<=$nbcolonne;$j++){						
							if( isset($this->matrice_donnees[$j])){
									if($champ['mesure1'] and $champ['mesure2']){
                                        if(trim($this->type_saisie_mes_1) == 'checkbox') {
                                            $temp=$champ['mesure1'].'_'.$j;
                                            if( isset($this->matrice_donnees[$i][$j][0]) and trim($this->matrice_donnees[$i][$j][0]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                            }else{
                                                        $$temp="'1'";
                                                    }
                                        }else{
                                            $temp=$champ['mesure1'].'_'.$j;
                                            $$temp=$this->matrice_donnees[$j][0];
                                        }
                                        if(trim($this->type_saisie_mes_2) == 'checkbox') {
                                            $temp=$champ['mesure2'].'_'.$j;
                                           if( isset($this->matrice_donnees[$i][$j][1]) and trim($this->matrice_donnees[$i][$j][1]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                            }else{
                                                        $$temp="'1'";
                                                    }
                                        }else{
                                            $temp=$champ['mesure2'].'_'.$j;
                                            $$temp=$this->matrice_donnees[$j][1];
                                        }
										
									}elseif($champ['mesure1']){
                                        if(trim($this->type_saisie_mes_1) == 'checkbox') {
                                            $temp=$champ['mesure1'].'_'.$j;
                                           if( isset($this->matrice_donnees[$j][0]) and trim($this->matrice_donnees[$j][0]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                            }else{
                                                        $$temp="'1'";
                                                    }
                                        }else{
                                            $temp=$champ['mesure1'].'_'.$j;
                                            $$temp=$this->matrice_donnees[$j][0];
                                        }
										
									}elseif ($champ['mesure2']){
										
                                        if(trim($this->type_saisie_mes_2) == 'checkbox') {
                                            $temp=$champ['mesure2'].'_'.$j;
                                           if( isset($this->matrice_donnees[$j][1]) and trim($this->matrice_donnees[$j][1]) <>'' ){
                                                        $$temp="'1' CHECKED";
                                            }else{
                                                        $$temp="'1'";
                                                    }
                                        }else{
                                            $temp=$champ['mesure2'].'_'.$j;
                                            $$temp=$this->matrice_donnees[$j][1];
                                        }	
									}		
							}else{
                                if(trim($this->type_saisie_mes_1) == 'checkbox') {
                                    $temp=$champ['mesure1'].'_'.$j;
                                    $$temp="'1'";
                                }
                                if(trim($this->type_saisie_mes_2) == 'checkbox') {
                                    $temp=$champ['mesure2'].'_'.$j;
                                    $$temp="'1'";
                                }
							}							
						}
						break;
					}
			}
		
		//echo $template;
		//return eval($template);
		return eval("echo \"".str_replace('"','\"',$template)."\";");
	}

	/**
	* METHODE :  verif()
	* Effectue la vérification de des elements envoyés 
	* suivant les régles de gestion associées
    * Cette fonction verif() permet la matrice
	* @access public
    * @param array() $matr contient la matrice
	*/
	function verif($matr){
			$code_etablissement = $this->code_etablissement;
			$code_annee     = $this->code_annee;
			$code_filtre     = $this->code_filtre;
			$type_matrice 	= $this->type_matrice;
			$champ 			= $this->mesures;
			$nbligne		= count($this->ligne);
			$nbcolonne		= count($this->colonne);
			$all_regles 	= $this->tab_regles_zone['MATRICE'];
			//echo'<pre>';
			//print_r($all_regles);
			if( isset($all_regles) and is_array($all_regles) ) $controle_matrice=true;
			switch ($type_matrice){
					case 'lc':{
							for ($i=0;$i<=$nbligne;$i++){								
								for ($j=0;$j<=$nbcolonne;$j++){
											//si champ controlable
											if($champ['mesure1'] and $champ['mesure2']){
												$temp=$champ['mesure1'].'_'.$i.'_'.$j;
												//$$temp=$this->matrice_donnees[$i][$j][0];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
												$temp=$champ['mesure2'].'_'.$i.'_'.$j;
												//$$temp=$this->matrice_donnees[$i][$j][1];											
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}																								
											}elseif ($champ['mesure1']){
												$temp=$champ['mesure1'].'_'.$i.'_'.$j;
												//$$temp=$this->matrice_donnees[$i][$j][0];											
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
											}elseif ($champ['mesure2']){											
												$temp	=	$champ['mesure2'].'_'.$i.'_'.$j;
												//$$temp=$this->matrice_donnees[$i][$j][0];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
											}	
									//}
								}
							}
							break;
						}
					case 'l':
						{
							for ($i=0;$i<=$nbligne;$i++){						
								//if( isset($this->matrice_donnees[$i])){
										if($champ['mesure1'] and $champ['mesure2']){
											$temp=$champ['mesure1'].'_'.$i;
											//$$temp=$this->matrice_donnees[$i][0];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
											
											$temp=$champ['mesure2'].'_'.$i;
											//$$temp=$this->matrice_donnees[$i][1];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
											
										}elseif($champ['mesure1']){
											$temp=$champ['mesure1'].'_'.$i;
											//$$temp=$this->matrice_donnees[$i][0];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
											
										}elseif($champ['mesure2']){
											$temp=$champ['mesure2'].'_'.$i;
											//$$temp=$this->matrice_donnees[$i][0];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												

										}		
								//}							
							}
							break;
						}
					case 'c':
						{
							for ($j=0;$j<=$nbcolonne;$j++){						
								//if( isset($this->matrice_donnees[$j])){
										if($champ['mesure1'] and $champ['mesure2']){
											$temp	= $champ['mesure1'].'_'.$j;
											//$$temp	= $this->matrice_donnees[$j][0];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
											
											$temp	= $champ['mesure2'].'_'.$j;
											//$$temp	= $this->matrice_donnees[$j][1];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
											
										}elseif($champ['mesure1']){
											$temp	= $champ['mesure1'].'_'.$j;
											//$$temp	= $this->matrice_donnees[$j][0];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
											
										}elseif ($champ['mesure2']){
											
											$temp	= $champ['mesure2'].'_'.$j;
											//$$temp	= $this->matrice_donnees[$j][0];
												if( $controle_matrice==true ){ 
														$champ_saisie = $temp;
														$concat_tab_code = array();
														$tab_err = All_Controle($matr, $all_regles, $champ_saisie, $concat_tab_code);
														if( (is_array($tab_err)) && (isset($tab_err['err'])) && ($tab_err['err'] == true) ){
															$this->reafficher_template($this->template, $matr, $tab_err['chp']);
															echo "<script type='text/Javascript'>\n";
															echo "$.unblockUI();\n";
															echo "</script>\n";
															exit();
														}
												}												
										}		
								//}							
							}
							break;
						}
				}
	}	
	
	/**
	* METHODE :  reafficher_template ( template , post )
	* Permet de réaficher le template aprés erreur
	* en concervant les données postées
    * Cette fonction reafficher_template() permet de reafficher le template    
	* @access public
    * @param String $template contient le flux du template
    * @param String $post contient le contient de la variable globale $_POST
    * @param String $champ_erreur le contrôle le champ d'erreur
    
	*/
	function reafficher_template($template, $post, $champ_erreur){
    
		$code_etablissement = $this->code_etablissement;
		$code_annee = $this->code_annee;
		$code_filtre = $this->code_filtre;
		$type_matrice 	= $this->type_matrice;
		$champ 					= $this->mesures;
		$nbligne				= count($this->ligne);
		$nbcolonne			= count($this->colonne);
		switch ($type_matrice){
				case 'lc':
                {
						for ($i=0;$i<=$nbligne;$i++){								
							for ($j=0;$j<=$nbcolonne;$j++){
								//if( isset($this->matrice_donnees[$i][$j])){
										//$temp='manuel'.$i.$j;
										//$champ est un tableau associatif de mesures : une ou deux mesures
										if($champ['mesure1'] and $champ['mesure2']){
											$temp=$champ['mesure1'].'_'.$i.'_'.$j;
											$$temp=$post[$temp];											
											if ($this->type_saisie_mes_1 =='checkbox' and trim($$temp) != ''){
													$$temp = "'1' CHECKED";
											}
																						
											$temp=$champ['mesure2'].'_'.$i.'_'.$j;
											$$temp=$post[$temp];											
											if ($this->type_saisie_mes_2 =='checkbox' and trim($$temp) != ''){
													$$temp = "'1' CHECKED";
											}
											
										}elseif ($champ['mesure1']){
											
											$temp=$champ['mesure1'].'_'.$i.'_'.$j;
											$$temp=$post[$temp];
											if ($this->type_saisie_mes_1 =='checkbox' and trim($$temp) != ''){
													$$temp = "'1' CHECKED";
											}
											
										}elseif ($champ['mesure2']){											
											$temp=$champ['mesure2'].'_'.$i.'_'.$j;
											$$temp=$post[$temp];
											if ($this->type_saisie_mes_2 =='checkbox' and trim($$temp) != ''){
													$$temp = "'1' CHECKED";
											}
											
										}	
								}
						}
						break;
					}
				case 'l':
					{
						for ($i=0;$i<=$nbligne;$i++){						
							//if( isset($this->matrice_donnees[$i])){
									if($champ['mesure1'] and $champ['mesure2']){
										$temp=$champ['mesure1'].'_'.$i;
										$$temp=$post[$temp];
                                        if ($this->type_saisie_mes_1 =='checkbox' and trim($$temp) != ''){
                                                $$temp = "'1' CHECKED";
                                        }
										
										$temp=$champ['mesure2'].'_'.$i;
										$$temp=$post[$temp];
                                        if ($this->type_saisie_mes_2 =='checkbox' and trim($$temp) != ''){
                                                $$temp = "'1' CHECKED";
                                        }
										
									}elseif($champ['mesure1']){
										$temp=$champ['mesure1'].'_'.$i;
										$$temp=$post[$temp];
                                        if ($this->type_saisie_mes_1 =='checkbox' and trim($$temp) != ''){
                                                $$temp = "'1' CHECKED";
                                        }
										
									}elseif($champ['mesure2']){
										$temp=$champ['mesure2'].'_'.$i;
										$$temp=$post[$temp];
                                        if ($this->type_saisie_mes_2 =='checkbox' and trim($$temp) != ''){
                                                $$temp = "'1' CHECKED";
                                        }
									}		
							//}							
						}
						break;
					}
				case 'c':
					{
						for ($j=0;$j<=$nbcolonne;$j++){						
							//if( isset($this->matrice_donnees[$j])){
									if($champ['mesure1'] and $champ['mesure2']){
										$temp	= $champ['mesure1'].'_'.$j;
										$$temp=$post[$temp];
                                        if ($this->type_saisie_mes_1 =='checkbox' and trim($$temp) != ''){
                                                $$temp = "'1' CHECKED";
                                        }
										
										$temp	= $champ['mesure2'].'_'.$j;
										$$temp=$post[$temp];
                                        if ($this->type_saisie_mes_2 =='checkbox' and trim($$temp) != ''){
                                                $$temp = "'1' CHECKED";
                                        }
										
									}elseif($champ['mesure1']){
										$temp	= $champ['mesure1'].'_'.$j;
										$$temp=$post[$temp];
                                        if ($this->type_saisie_mes_1 =='checkbox' and trim($$temp) != ''){
                                                $$temp = "'1' CHECKED";
                                        }
										
									}elseif ($champ['mesure2']){
										
										$temp	= $champ['mesure2'].'_'.$j;
										$$temp=$post[$temp];
                                        if ($this->type_saisie_mes_2 =='checkbox' and trim($$temp) != ''){
                                                $$temp = "'1' CHECKED";
                                        }
									}		
							//}							
						}
						break;
					}
			}
		
		eval("echo \"".str_replace('"','\"',$template)."\";");
		mettre_en_evidence_champ_erreur($champ_erreur);
	}	
	
	/**
	*METHODE :  comparer_matrice ( MATR1 , MATR2 , nb_cles )
	*<pre>
	*DEBUT
	*	POUR CHAQUE tableliee de nomtableliee
	*		POUR CHAQUE element de MATR2 FAIRE
	*			SI element N’EST PAS DANS MATR1 ALORS
	*				matrice_donnees_bdd [] <-- AJOUTER(element + INSERT)	
	*			FIN SI
	*			SINON SI element(MATR2) DIFFERENT	element(MATR1)
	*				matrice_donnees_bdd [] <--  AJOUTER(element + UPDATE)
	*			FIN SINON
	*		FIN POUR
	*		POUR CHAQUE element de MATR1 FAIRE
	*			SI element N’EST PAS DANS MATR2 ALORS
	*				matrice_donnees_bdd [] <--  AJOUTER(element + DELETE)	
	*			FIN SI
	*		FIN POUR
	*	FIN POUR
	*FIN
	*</pre>
    * Cette fonction comparer_matrice ($matr1,$matr2,$nb_cles) permet la comparaison matricielle
    * à partir de duex matrices de meme dimensions
	* @access public
    * @param array() $matr1 contient les données de la première matrice
    * @param array() $matr2 contient les données de la deuxième matrice
    * @param  numeric $nb_cles contient le nombre de clés   
    
	*/ 					
	public function comparer_matrice ($matr1,$matr2,$nb_cles){
	
		// Cette fonction permet de faire la comparaison matricielle complexe		
		//$indice_cle	=	$this->nb_cles - 1;		
		/*echo "<br>NB CLES =".$nb_cles."<br>";
		echo "MATRICE DEPART<pre>";
		print_r($matr1);
		echo "MATRICE ARRIVEE<pre>";
		print_r($matr2);
		*/
		
		$indice_cle	=	$nb_cles - 1;
				
		$result 	=	array();
		$i = 0;
		if(is_array($matr2))
		foreach ($matr2 as $elt)
		{
				// cette variable contient la clé identifiant de l'élément matriciell
				
				$cle	=	associer_identifiant($elt,$indice_cle);
								
				$action	=	existe_element_grille_action($cle,$elt,$indice_cle,$matr1);		
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
		
		foreach ($matr1 as $elt)
		{
				// cette variable contient la clé identifiant de l'élément matriciell
				
				$cle	=	associer_identifiant($elt,$indice_cle);
				$action	=	existe_element_grille_action($cle,$elt,$indice_cle,$matr2);
				switch ($action)
				{
					case 'I':
						
						$action		=	'D'	;				
						for ($i=0;$i<count($elt);$i++){
							$tmp[$i]	=	$elt[$i];		
						}
						$tmp[$i++]	=	$action;
						$result []	=	$tmp;
						break;
				}	
		}		
		
		//return $result;
		//echo'<pre>';
		//print_r($result);
		$this->matrice_donnees_bdd	=	$result;
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
		// Gestion des erreurs lors de l'exécution de la requête SQL
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
	*METHODE :  maj_db ()
	*<pre>
	*DEBUT
	*	PARCOUCIR matrice_donnees_bdd 
	*		SI ACTION = ‘U’  (cas update)
	*			EFFECTUER REQUETE D’UPDATE
	*		FIN SI
	*		SINON SI ACTION = ‘I’  (cas insert)
	*			EFFECTUER REQUETE D’INSERT
	*	  FIN SINON
	*		SINON SI ACTION = ‘D’  (cas delete)
	*			EFFECTUER REQUETE DE DELETE
	*		FIN SINON
	*	FIN PARCOURIR
	*FIN
	*</pre>
    * Cette classe permet la mise à jour des données dans la base de données
	* @access public
    * @param String $nom_table contient le nom de la table
    * @param array() $matr_code_ident contient la matrice des identifiants
    * @param array() $matr contient la matrice complète des valeurs
    * @param array() $libdim contient les noms des dimensions
    * @param array() $mesures contient les noms des mesures    
	*/ 					
    function maj_db($nom_table, $matr_code_ident, $matr,$libdim,$mesures){  
		
		$ID_TRACE = $this->get_cle_max_id_trace()+1;//Recuperation max id_trace + 1 pour obtenir le nouvel id_trace
			
			//$matr_code_ident = $this->val_cle;
			$nb_col	= sizeof($matr[0]);
			$nb_mesure = sizeof($mesures);
			$mesure1=$mesures['mesure1'];
			$mesure2=$mesures['mesure2'];
		  
        if (is_array($matr)){
/////////////////////////////////////// TRAITEMENT DES DELETE
			$id_action = 0;
        	foreach ($matr as $tab){
        	$i++;
        //		echo '<pre>';
				//		print_r($tab);   
						     
	        	if ($nb_mesure==2){
	        		if ($nb_col==5){
	        			$action = $tab[4];
	        		}else{
	        			$action =$tab[3];
	        		}
	        	}elseif ($nb_mesure==1){
	        		if ($nb_col==4){
	        			$action = $tab[3];
	        		}else{
	        			$action = $tab[2];
	        		}
	        	}
						// bass
						for($inc=0 ;$inc <=$nb_col ; $inc++){
								if(trim($tab[$inc]) == ''){
										//$tab[$inc] = "NULL";
								}
						}
						// fin bass	
	
	        	    /*
	             * Suppression dans une table
	             */
				$id_action++;
	            if ($action =='D'){
	                $sql 	= 'DELETE FROM '.$nom_table.' WHERE ';
	                $i		=	0;
	                foreach ($matr_code_ident as $ligne) {
	                    if ($i<>0){
	                        $sql .= ' AND '.$ligne[0].' = '.$ligne[1];
	                    }else{
	                        $sql .=  $ligne[0].' = '.$ligne[1];
	                    }
	                    $i++;
	                }
	                if ($nb_mesure ==2){
	        	        if ($nb_col==5){
	            	    	$sql .= ' AND '.$libdim[0].' = '.$tab[0].' AND '.$libdim[1].' = '.$tab[1];
	                	}else{
	  	                	$sql .= ' AND '.$libdim[0].' = '.$tab[0];              	
	                	}
	                }elseif ($nb_mesure==1){
	        	        if ($nb_col==4){
	            	    	$sql .= ' AND '.$libdim[0].' = '.$tab[0].' AND '.$libdim[1].' = '.$tab[1];
	                	}else{
	  	                	$sql .= ' AND '.$libdim[0].' = '.$tab[0];              	
	                	}
	                }
									
								if ($this->conn->Execute($sql) === false) {
											$GLOBALS['theme_data_MAJ_ok'] 	= false;
											print ' <br> error deleting :<br>'.$sql.'<br>';  
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
																	VALUES ($ID_TRACE,'$nom_table',$id_action,$code_user,'$login_user',$id_theme_user,'$action',".$this->conn->qstr($sql).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
								//echo "<br>$req_trace_user";
								if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
									//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
								}
								//Fin MAJ DICO_TRACE
							}
					
	            }
						        	       	         					
        	}                   

/////////////////////////////////////// FIN TRAITEMENT DELETE

//////////////////////////////////////// TRAITEMENT DES INSERT
        	$id_action = 0;
			foreach ($matr as $tab){
        	$i++;
        //		echo '<pre>';
				//		print_r($tab);   
						     
	        	if ($nb_mesure==2){
	        		if ($nb_col==5){
	        			$action = $tab[4];
	        		}else{
	        			$action =$tab[3];
	        		}
	        	}elseif ($nb_mesure==1){
	        		if ($nb_col==4){
	        			$action = $tab[3];
	        		}else{
	        			$action = $tab[2];
	        		}
	        	}
						// bass
						for($inc=0 ;$inc <=$nb_col ; $inc++){
								if(trim($tab[$inc]) == ''){
										//$tab[$inc] = "NULL";
								}
						}
						// fin bass	
	
	        	    /*
	             * Suppression dans une table
	             */
				$id_action++;		        	       	         	
	           	if ($action =='I'){
	           		$code_sql='';
	           		$code_val='';    
	                if (is_array($matr_code_ident)){
	                    $i 	= 0 ;
	                    foreach ($matr_code_ident as $ligne) {
	                        if ($i <> 0){
	                            $code_sql	.= ',';
	                            $code_val	.= ',';                    
	                        }
	                        $code_sql	.=$ligne[0];
	                        $code_val	.=$ligne[1];
	                        $i++;
	                        }	               
						}
	                if ($nb_mesure==2){
		                if ($nb_col == 5){
						    
							$sql_part_1 = 'INSERT INTO '.$nom_table.' ('.$code_sql.','. $libdim[0].','. $libdim[1] ; 
							$sql_part_2 = ' VALUES ('.$code_val.','.$tab[0].','.$tab[1]  ; 
							
							$val_mes_1 = $tab[2] ;
							$val_mes_2 = $tab[3] ;
							
						}else {
		                	
							$sql_part_1 = 'INSERT INTO '.$nom_table.' ('.$code_sql.','. $libdim[0] ; 
							$sql_part_2 = ' VALUES ('.$code_val.','.$tab[0] ; 
							
							$val_mes_1 = $tab[1] ;
							$val_mes_2 = $tab[2] ;
						}
						
						if( trim($val_mes_1) <> ''){ // si la mesure 1 n'est pas vide
							$sql_part_1 .=	' ,' . $mesure1 ;
							$sql_part_2 .=	' ,' . $val_mes_1 ;
						}
						if( trim($val_mes_2) <> ''){ // si la mesure 2 n'est pas vide
							$sql_part_1 .=	' ,' . $mesure2 ;
							$sql_part_2 .=	' ,' . $val_mes_2 ;
						}
						$sql_part_1 .=	')';
						$sql_part_2 .=	')';       
						$sql		= 	$sql_part_1 . $sql_part_2;	
	                
					}elseif ($nb_mesure==1){	                	
		                if ($nb_col == 4){               
		                	$sql	= 'INSERT INTO '.$nom_table.' ('.$code_sql.','. $libdim[0].','. $libdim[1].','. $mesure1.') VALUES ('.$code_val.','.$tab[0].','.$tab[1].','.$tab[2].')';		                	
		                }else {
							//bass
							//echo '<pre>';
							//print_r($tab[1]);   
							$sql	= 'INSERT INTO '.$nom_table.' ('.$code_sql.','. $libdim[0].','. $mesure1.') VALUES ('.$code_val.','.$tab[0].','."{$tab[1][0]}".')';
							//bass		                	
		                }               	
	                }
					if ($this->conn->Execute($sql) === false){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						 print ' <br> error inserting :<br>'.$sql.'<br>';  
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
															VALUES ($ID_TRACE,'$nom_table',$id_action,$code_user,'$login_user',$id_theme_user,'$action',".$this->conn->qstr($sql).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
						//echo "<br>$req_trace_user";
						if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
							//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
						}
						//Fin MAJ DICO_TRACE
					} 
            	}
        	}                   

//////////////////////////////////////// FIN TRATEMENT DES INSERT

//////////////////////////////////////// TRAITEMENT DES UPDATE
        	$id_action = 0;
			foreach ($matr as $tab){
        	$i++;
        //		echo '<pre>';
				//		print_r($tab);   
						     
	        	if ($nb_mesure==2){
	        		if ($nb_col==5){
	        			$action = $tab[4];
	        		}else{
	        			$action =$tab[3];
	        		}
	        	}elseif ($nb_mesure==1){
	        		if ($nb_col==4){
	        			$action = $tab[3];
	        		}else{
	        			$action = $tab[2];
	        		}
	        	}
						// bass
						for($inc=0 ;$inc <=$nb_col ; $inc++){
								if(trim($tab[$inc]) == ''){
										$tab[$inc] = "NULL";
								}
						}

                /*
                 * Mise à jour d'une table
                 */
				 $id_action++;
	             if ($action == 'U'){
	             	if($nb_mesure==2){
		             	if ($nb_col==5){
		             		
		                	$sql 	= 'UPDATE '.$nom_table.' SET '.$mesure1.' = '.$tab[2].' , '.$mesure2.' = '.$tab[3].' WHERE ';
		                	//$sql 	= "UPDATE $nom_table SET $mesures[0] = $tab[2] , $mesures[1] = $tab[3] WHERE ";
		             	}else{
		             		$sql 	= 'UPDATE '.$nom_table.' SET '.$mesure1.' = '.$tab[1].', '.$mesure2.' = '.$tab[2].' WHERE ';
		             		//$sql 	= "UPDATE $nom_table SET $mesures[0] = $tab[1],$mesures[1] = $tab[2] WHERE ";
		             	}
	             	}elseif($nb_mesure==1){
		             	if ($nb_col==4){
		                	$sql 	= 'UPDATE '.$nom_table.' SET '.$mesure1.' = '.$tab[2].' WHERE ';
		                	//$sql 	= "UPDATE $nom_table SET $mesures[0] = $tab[2] WHERE ";
		             	}else{
										//bass
		             		$sql 	= 'UPDATE '.$nom_table.' SET '.$mesure1.' = '."{$tab[1][0]}".' WHERE ';
										// fin bass
		             		//$sql 	= "UPDATE $nom_table SET $mesures[0] = $tab[1] WHERE ";
		             	}	             		
	             	}
	                $i 		= 0 ;
	                foreach ($matr_code_ident as $ligne) {
	                    if ($i <> 0){
	                        $sql .= ' AND '.$ligne[0].' = '.$ligne[1];
	                    }else{
	                        $sql .= $ligne[0].' = '.$ligne[1];
	                    }
	                    $i++;
	                }
	                if ($nb_mesure ==2){
	        	        if ($nb_col==5){
	            	    	$sql .= ' AND '.$libdim[0].' = '.$tab[0].' AND '.$libdim[1].' = '.$tab[1];
	                	}else{
	  	                	$sql .= ' AND '.$libdim[0].' = '.$tab[0];              	
	                	}
	                }elseif ($nb_mesure==1){
	        	        if ($nb_col==4){
	            	    	$sql .= ' AND '.$libdim[0].' = '.$tab[0].' AND '.$libdim[1].' = '.$tab[1];
	                	}else{
	  	                	$sql .= ' AND '.$libdim[0].' = '.$tab[0];              	
	                	}
	                }	       
					
								if ($this->conn->Execute($sql) === false) {
									$GLOBALS['theme_data_MAJ_ok'] 	= false;
									print ' <br> error updating :<br>'.$sql.'<br>';  
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
																		VALUES ($ID_TRACE,'$nom_table',$id_action,$code_user,'$login_user',$id_theme_user,'$action',".$this->conn->qstr($sql).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
									//echo "<br>$req_trace_user";
									if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
										//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
									}
									//Fin MAJ DICO_TRACE
								}
	            }
				
        	}                   

//////////////////////////////////////// FIN TRAITEMENT DES UPDATE        	

        }
    }
    
	
	function __wakeup(){
		$this->conn	= $GLOBALS['conn'];
	}
}
?>
