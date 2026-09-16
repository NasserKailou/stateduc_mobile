<?php /** 
 * Classe mat_grille générique
 * <pre>
 * Classe à l'image de grille
 * Mais des zones de saisie de type matrice peuvent en plus 
 * avoir des propriétés systeme 
 * </pre>
 * @access public
 * @author Alassane, Touré, Yacine
 * @version 1.1
*/	
class mat_grille {
	
	
	/**
	 * Attribut : code_etablissement
	 * <pre>
	 * On choisit toujours de se rapporter à un établissement 
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
	 * Attribut : nomtableliee
	 * <pre>
	 * Contient l'ensemble des tables liées
	 * Toutes les tables devrant etre mises à jour seront listées 
	 * dans cet attribut Les propriétés de la classe 
	 * s'articuleront autour de ces tables liées
	 * </pre>
	 * @var array
	 * @access public
	 */
	public $nomtableliee	=	array();
	
	
	 /**
	 * Attribut : sql_data
	 * <pre>
	 * Requête permettant d'extraire les données à afficher pour 
	 * une table liée 
	 * </pre>
	 * @var array
	 * @access public
	 */
	public $sql_data		=	array();
	
	
	/**
	 *
	 * Attribut : tableau_zone_saisie
	 * <pre>
	 * Définition de l'ensemble des zones de saisie
	 *  Tableau associatif comprenant:
	 *	 -> type			: c, s, csu, cmm, cmu, ...
	 *	 -> champ			: Champ à manipuler ds la base 
	 *	 -> table_ref	: Nom de la table de nomenclature  d'où extraire les 
	                    codes des occurences utilisés à la présentation 
	 *	 -> ordre			: Position de la zone de saisie par rapport aux autres 
	 *	 -> sql				: La requête pour extraire les valeurs des codes
	 * </pre>
	 * @var array
	 * @access public
	 */
	 public $tableau_zone_saisie 	= 	array();
     
    /**
	 * Attribut : tableau_type_zone_base
	 * <pre>
	 * Variable permettant de stocker le type de données dans la base d'un champ(ZONE) 
     * d'une TABLE_MERE, utilisée lors de la constitution des requètes SQL en fonction 
     * du type de SGBD pour placer des quotes ou non au niveau des clauses
	 * </pre>
	 * @var array
	 * @access public
	 */ 
     public $tableau_type_zone_base 	= 	array();
	 
	 
	 /**
	 * Attribut : code_nomenclature
	 * <pre>
	 *  Contient les valeurs (codes) provenant d'une table de nomencalature 
	 *  définie lors de la création d'une zone de saisie de type matrice
	 *  ou booléén ou type e (type lié aux effectifs)
	 * </pre>
	 * @var array
	 * @access public
	 */
	 public $code_nomenclature 	= 	array();
	 
	 
	 /**
	 * Attribut : champs
	 * <pre>
	 * Contient les differents noms des champs issus des differentes 
	 * zones de saisies Ces champs serviront plus tard aux requêtes 
	 * de mise à jour dans la base de données
	 * </pre>		
	 * @var array
	 * @access public
	 */
	 public $champs				=	array();
	 
	 
	 /**
	 * Attribut : nb_cles
	 * <pre>
	 * Contient le nombre de clés définies dans la table liée
	 * </pre>
	 * @var array
	 * @access public
	 */
	 public $nb_cles	=	array();
	 
	 
	 /**
	 * Attribut : nb_lignes
	 * <pre>
	 * Nombre de lignes de la grille
	 * </pre>
	 * @var numeric
	 * @access public
	 */	
	 public $nb_lignes;
	 
	 
	 /**
	 * Attribut : classe
	 * <pre>
	 * Contient le nom de la classe (ici mat_grille)
	 * </pre>
	 * @var string 
	 * @access public
	 */	
	 public $classe;
	 
	 
	 /** 
	 * Attribut : val_cle
	 * <pre>
	 * Ce tableau contient les valeurs des clés définies dans les zones 
	 * de type systeme Ces valeurs sont utilisées pour filtrer des 
	 * données issues de la base C est le cas pour le moment de 
	 * code_etablissement et code_annee
	 * </pre>
	 * @var array
	 * @access public
	 */
	 public $val_cle	=	array();
	 
	 
	 /** 
	 * Attribut : template
	 * <pre>
	 * On récupère dans cette variable le contenu page HTML du 
	 * template à afficher Le chemin du template est recuperer 
	 * depuis DICO
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
	 * Ici chaque ligne d'enregistrement est un n-uplet composé des 
	 * champs issus des differentes zones
	 * </pre> 
	 * @var array
	 * @access public
	 */
	 public $matrice_donnees		=	array();
	 
	 
	/**
	 * Attribut : matrice_donnees_tpl
	 * <pre>
	 * Dans cette variable on prépare les données de $matrice_donnees pour 
	 * s'accomoder à la logique d'affichage imposée par le template 
	 * </pre> 
	 * @var array
	 * @access public
	 */
	 public $matrice_donnees_tpl	=	array();
	 
	 
	 /**
	 * Attribut : matrice_donnees_post
	 * <pre>
	 * Cette variable est configurée aprés soumission de la page HTML via 
	 * POST, sous la même base de présentation  que $matrice_donnees
	 * </pre> 
	 * @var array
	 * @access public
	 */
	 public $matrice_donnees_post	=	array();
	 
	 
	 /**
	 * Attribut : matrice_donnees_bdd
	 * <pre>
	 * Cette variable est le résultat de la comparaison entre les données
	 * de départ et celles du POST Le contenu de cette variable sera utlisé
	 * pour la MAJ de la base de données
	 * </pre> 
	 * @var array
	 * @access public
	 */	
	 public $matrice_donnees_bdd	=	array();	
	 
	 
	 /**
	 * Attribut : conn
	 * <pre>
	 * Dans cette variable on extrait la variable globale de connexion à
	 * la la base pour en faire une propriété de la classe
	 * </pre>
 	 * @var string
	 * @access public
	 */	
	 public $conn;
	
	
	 /**
	 * Attribut : VARS_GLOBALS
	 * <pre>
	 * Cette variable nous permet de garder nombre d'informations 
	 * utilisées notamment pour bien gérer l'affichage des données 
	 * dans la navigation page par page 
	 * </pre> 
	 * @var array
	 * @access public
	 */
	 public $VARS_GLOBALS = array();
	 
	 /**
	 * Attribut : id_theme
	 * <pre>
	 * Variable contenant le thème en cours de traitement
	 * </pre>
 	 * @var integer
	 * @access public
	 */	
	 public $id_theme;
	 public $type_theme;

	 /**
	 * Attribut : tab_regles_zone
	 * <pre>
	 * Cette variable nous permet de stocker les differentes règles et
	 * type de contrôle de saisie relatifs aux zones de saisies
	 * </pre> 
	 * @var array
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
	//function __construct($code_etablissement,$code_annee){
		
        $this->code_etablissement	= $code_etablissement;        
        $this->conn 				= $GLOBALS['conn'];
        $this->code_annee 			= $code_annee;
		$this->code_filtre 		= $code_filtre;
		$this->id_theme 			= $id_theme;
		$this->id_systeme 			= $id_systeme;
		
		//Recuperation du type de theme
		$req_type_theme = "SELECT ID_TYPE_THEME FROM DICO_THEME WHERE ID=".$id_theme;
		$this->type_theme	= $GLOBALS['conn_dico']->GetOne($req_type_theme);
		//Fin Recuperation du type de theme	
		
		$this->get_dico($id_theme,$id_systeme);
		$dico	=	$GLOBALS['dico'];
							
		//if (isset($dico['code_annee'])) 			{$this->code_annee=$dico['code_annee']; }        
        if (isset($dico['nomtableliee'])) 			{$this->nomtableliee=$dico['nomtableliee'];}        
        if (isset($dico['tableau_zone_saisie'])) 	{$this->tableau_zone_saisie=$dico['tableau_zone_saisie'];} 
        if (isset($dico['tableau_type_zone_base'])) {$this->tableau_type_zone_base=$dico['tableau_type_zone_base'];}                   
        if (isset($dico['code_nomenclature'])) 		{$this->code_nomenclature=$dico['code_nomenclature'];}     
        if (isset($dico['sql_data'])) 				{$this->sql_data=$dico['sql_data'];}     
        
        //if (isset($dico['champs'])) 				{$this->champs=$dico['champs'];}               
        if (isset($dico['nb_cles'])) 		 		{$this->nb_cles=$dico['nb_cles'];} 
		if (isset($dico['val_cle'])) 		 		{$this->val_cle=$dico['val_cle'];}
        if (isset($dico['nb_lignes'])) 				{$this->nb_lignes=$dico['nb_lignes'];}
        if (isset($dico['classe'])) 				{$this->classe=$dico['classe'];}
        //if (isset($dico['template']))				{$this->template 	= file_get_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template']);}
        if (file_exists($GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template'])) {
                $this->template 	= file_get_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template']);
        }else{
                 print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$_SESSION['langue'].'/'.$dico['template'] . ' : Inexistant !<BR>';
        }
      
      // print($this->template);
	}
	
	
	/**
	* METHODE :  get_dico( id_theme,  id_systeme):
	* Permet de lire la configuration associée au thème et au système 
	* d'enseignement depuis DICO pour associer les attributs de la 
	* classe à leurs valeurs respectives
	*
	*<pre>
	*DEBUT
	*	sql <-- requête de selection ds DICO_THEME  et DICO_THEME_SYSTEME en 
	*         fonction de id_theme et id_systeme
	*	sql <-- requête de selection des différentes TABLE_MERE  ds DICO_ZONE 
	*         en fonction de id_theme
	*	POUR CHAQUE TABLE_MERE
	*		Sql3 <-- requête de sélection ds DICO_ZONE en jointure avec 
	*            DICO_ZONE_SYSTEME  suivant les critères id_systeme, 
	*            id_theme, TABLE_MERE
	*		RECUPERERATION DES ZONES PAR TABLE_MERE 
	*		PREPARER LA REQUETE D’EXTRACTION DES DONNEES PAR TABLE_MERE
	*		PREPARER LA VARIABLE dico[]    ( exploté par __CONSTRUCT() )
	*	FIN POUR
	*FIN
	*</pre>
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
		
		unset($dico);
		$sql 						= ' SELECT A.*, B.FRAME, B.NB_LIGNES_FRAME  FROM DICO_THEME  A, DICO_THEME_SYSTEME B '.
                                      ' WHERE A.ID=B.ID '.
                                      ' AND A.ID='.$id_theme.
                                      ' AND B.ID_SYSTEME='.$id_systeme;
									  
		$conn						= $GLOBALS['conn'];
    // Traitement Erreur Cas : Execute / GetOne
		try {            
				$rs							= $GLOBALS['conn_dico']->Execute($sql);
				if($rs ===false){                
						 throw new Exception('ERR_SQL');   
				}
				$dico['nb_lignes']		=	$rs->fields['NB_LIGNES_FRAME'];
				$dico['template']		=	$rs->fields['FRAME'];
				$dico['classe']			=	$rs->fields['CLASSE'];
		}
		catch (Exception $e) {
				 $erreur = new erreur_manager($e,$sql);
		}        
    // Fin Traitement Erreur Cas : Execute / GetOne			
		// récupération des zones de saisie de la grille

		// Identification de l'ensemble des tables liées
		$sqltableliee='  SELECT DISTINCT DICO_ZONE.TABLE_MERE, 
				DICO_ZONE.PRIORITE FROM DICO_ZONE, DICO_ZONE_SYSTEME 
				WHERE DICO_ZONE.ID_THEME ='.$id_theme.
				' AND DICO_ZONE.TABLE_MERE IS NOT NULL 
				AND DICO_ZONE_SYSTEME.ID_ZONE=DICO_ZONE.ID_ZONE
				AND DICO_ZONE_SYSTEME.ACTIVER=1
				AND DICO_ZONE_SYSTEME.ID_SYSTEME='.$id_systeme.
				' ORDER BY DICO_ZONE.PRIORITE';

		// Traitement Erreur Cas : Execute / GetOne
		try {            
				$rstableliee =	$GLOBALS['conn_dico']->Execute($sqltableliee);
				if($rstableliee ===false){                
						 throw new Exception('ERR_SQL');   
				}
				$nb_table_liee		=	$rstableliee->RecordCount();
		
				$pile_jointure		=	array();
				$tab_order_by		=	array();
				$this->tab_regles_zone = array(); 
				$_SESSION['tableau_zone_saisie'] = array();
				while (!$rstableliee->EOF){ // Pour chq TABLE_MERE
					$tableau_zone_saisie = array();
                    $tableau_type_zone_base = array();
								
					$sql_donnees	=	'SELECT DISTINCT '; // Début de constitution de la requète
					$nomtableliee	=	$rstableliee->fields['TABLE_MERE'];
					$pile_table_liee[]	=	$nomtableliee;
					$nb_cles		=	0; 
					$id_zone		=	0;
					// Requète de récupération des zones de la table mère
					$sql_zone_						=' SELECT DICO_ZONE.ID_ZONE, DICO_ZONE.TABLE_FILLE, DICO_ZONE.ORDRE,'. 
													 ' DICO_ZONE.[SQL_REQ], DICO_ZONE.CHAMP_PERE, DICO_ZONE.TYPE_ZONE_BASE, DICO_ZONE.ORDRE_TRI,DICO_ZONE.ID_ZONE_REF,
														 DICO_ZONE_SYSTEME.*'.
													 ' FROM DICO_ZONE,DICO_ZONE_SYSTEME'.			 
													 ' WHERE DICO_ZONE.ID_ZONE=DICO_ZONE_SYSTEME.ID_ZONE'.
													 ' AND DICO_ZONE_SYSTEME.ID_SYSTEME ='.$id_systeme.
													 ' AND DICO_ZONE_SYSTEME.ACTIVER =1'.
													 ' AND DICO_ZONE.ID_THEME ='.$id_theme.										
													 ' AND DICO_ZONE.TABLE_MERE=\''.$nomtableliee.'\'
														ORDER BY DICO_ZONE.ORDRE';
					// Traitement Erreur Cas : Execute / GetOne
					try {            
							$rs	=	$GLOBALS['conn_dico']->Execute($sql_zone_);
							if($rs === false){                
                                 throw new Exception('ERR_SQL');   
							}
							$cptr			=	0;
							$val_cle 		=   array();
							$criteres		=   '';
							$cptr_pass = 0; 
							while (!$rs->EOF){ // parcours des zones
								$zone_saisie = array();
								if  (	trim($rs->fields['TYPE_OBJET'])=='systeme' or
										trim($rs->fields['TYPE_OBJET'])=='systeme_valeur_unique' or
										trim($rs->fields['TYPE_OBJET'])=='systeme_liste_radio' or
										trim($rs->fields['TYPE_OBJET'])=='systeme_combo' or
										trim($rs->fields['TYPE_OBJET'])=='systeme_text' or
										trim($rs->fields['TYPE_OBJET'])=='systeme_valeur_multiple'
									){ // Detection et Décompte des types clé
											if (trim($rs->fields['CHAMP_PERE'])==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
												$val_cle[$rs->fields['CHAMP_PERE']] 	= $this->code_etablissement;
												if(!preg_match('/.'.trim($rs->fields['CHAMP_PERE']).'/',$criteres)){
													if (($nb_table_liee>=1)&& ($cptr_pass==0)){
														$criteres.=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_etablissement;
													}else{
														$criteres.=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_etablissement;
													}
												}
												$cptr_pass++;
											}
											elseif (trim($rs->fields['CHAMP_PERE'])==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']){
												$val_cle[$rs->fields['CHAMP_PERE']] 	= $this->code_annee;
												if(!preg_match('/.'.trim($rs->fields['CHAMP_PERE']).'/',$criteres)){
													if (($nb_table_liee>=1)&& ($cptr_pass==0)){
														$criteres.=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_annee;
													}else{
														$criteres.=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_annee;
													}		
												}
												$cptr_pass++;	
											}
											elseif (trim($rs->fields['CHAMP_PERE'])==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']){
												$val_cle[$rs->fields['CHAMP_PERE']] 	= $this->code_filtre;
												if(!preg_match('/.'.trim($rs->fields['CHAMP_PERE']).'/',$criteres)){
													if (($nb_table_liee>=1)&& ($cptr_pass==0)){
														$criteres.=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_filtre;
													}else{
														$criteres.=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$this->code_filtre;
													}		
												}
												$cptr_pass++;	
											}
											elseif ( isset($rs->fields['VALEUR_CONSTANTE']) and $rs->fields['VALEUR_CONSTANTE']<>'' ){
												
												$val_cle[$rs->fields['CHAMP_PERE']] 	= $rs->fields['VALEUR_CONSTANTE'];
												if(!preg_match('/.'.trim($rs->fields['CHAMP_PERE']).'/',$criteres)){
													
													if (($nb_table_liee>=1)&& ($cptr_pass==0)){
														//die('here1');
														$criteres.=$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$rs->fields['VALEUR_CONSTANTE'];
													}else{
														//die('here2');
														$criteres.=' AND '.$nomtableliee.'.'.$rs->fields['CHAMP_PERE'].'='.$rs->fields['VALEUR_CONSTANTE'];
													}		
												}
												$cptr_pass++;	
											}
						
											$nb_cles++;
								}
								
								switch ($rs->fields['TYPE_OBJET']){
									case 'text': 						{$zone_saisie['type'] 	= 's'; 		break;}
									case 'liste_radio':				    {$zone_saisie['type'] 	= 'm'; 		break;}
									case 'combo':						{$zone_saisie['type'] 	= 'co';     break;}
									case 'systeme':						{$zone_saisie['type'] 	= 'c'; 		break;}
									case 'checkbox':					{$zone_saisie['type'] 	= 'ch';     break;}
									case 'booleen':						{$zone_saisie['type'] 	= 'b'; 		break;}
									case 'systeme_valeur_unique':		{$zone_saisie['type'] 	= 'csu'; 	break;}
									case 'systeme_liste_radio':			{$zone_saisie['type'] 	= 'cmu'; 	break;}
									case 'systeme_combo':				{$zone_saisie['type'] 	= 'sco'; 	break;}
									case 'systeme_text':				{$zone_saisie['type'] 	= 'sys_txt'; 	break;}
									case 'systeme_valeur_multiple':	    {$zone_saisie['type'] 	= 'cmm'; 	break;}
									case 'text_valeur_multiple':		{$zone_saisie['type'] 	= 'tvm'; 	break;}
									case 'label':						{$zone_saisie['type'] 	= 'l'; 		break;}
									
								}
                                if ($rs->fields['ORDRE_TRI']>0){
                                    $tab_order_by[$nomtableliee][$rs->fields['ORDRE_TRI']]	=	$rs->fields['CHAMP_PERE'];
                                }
								
								//Modif HEBIE
								if(trim($rs->fields['TABLE_FILLE'])=='' && trim($rs->fields['TABLE_INTERFACE'])<>'' && trim($rs->fields['CHAMP_INTERFACE'])<>'' && trim($rs->fields['REQUETE_CHAMP_INTERFACE'])<>''){
									$zone_saisie['DynContentObj'] 	= 1 ;
									$zone_saisie['champ_interface'] = $rs->fields['CHAMP_INTERFACE'];
									$zone_saisie['sql_interface'] 	= $rs->fields['REQUETE_CHAMP_INTERFACE'];
									$zone_saisie['table_interface'] = $rs->fields['TABLE_INTERFACE'];
									$zone_saisie['id_zone'] = $rs->fields['ID_ZONE'];
								}
								//Fin Modif HEBIE
								$zone_saisie['champ'] 			= $rs->fields['CHAMP_PERE'];
								$zone_saisie['table_ref'] 	    = $rs->fields['TABLE_FILLE'];
								$zone_saisie['ordre'] 			= $rs->fields['ORDRE'];
                                
                                // Modif Alassane
                                $tableau_type_zone_base[$rs->fields['CHAMP_PERE']] 		= $rs->fields['TYPE_ZONE_BASE'];
                                // Fin Modif Alassane 
                
								
                                if( trim($rs->fields['BOUTON_INTERFACE']) <> '' ){
                                        
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
				
								if (!is_null($rs->fields['SQL_REQ']) and (trim($rs->fields['SQL_REQ']) <> '') ){
									
									$temp_sql	=	$rs->fields['SQL_REQ'];						
									$chaine_eval ="\$zone_saisie['sql'] =\"$temp_sql\";";						
									eval ($chaine_eval);
								}
								else{
									$zone_saisie['sql'] 	= '';
								}							
								$tableau_zone_saisie[]		=	$zone_saisie;                
							
								// récupération des règles associées à la zone
                                $sql_regles =" 	SELECT  *
                                                FROM   DICO_REGLE_ZONE, DICO_REGLE_ZONE_ASSOC 
                                                WHERE DICO_REGLE_ZONE.ID_REGLE_ZONE = DICO_REGLE_ZONE_ASSOC.ID_REGLE_ZONE
                                                AND    DICO_REGLE_ZONE_ASSOC.ID_ZONE =".$rs->fields['ID_ZONE'];
								
								////////////////////////////////////////////////////////////////////////////////////////////////////////////////
								////////////////////////////////////////////////////////////////////////////////////////////////////////////////
								// Traitement Erreur Cas : Execute / GetOne
								try {            
										$rs_regles	=	$GLOBALS['conn_dico']->Execute($sql_regles);
										if($rs_regles === false){                
												 throw new Exception('ERR_SQL');   
										}
										if($rs_regles->RecordCount()>0){ // Cas de régles associées
										 //echo "<br> sql_regles = $sql_regles";
												$nom_champ = $rs->fields['CHAMP_PERE'] ;
												$this->tab_regles_zone[$nom_champ] = array();
												while(!$rs_regles->EOF){ // Récupération des caractéristiques des Règles
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
								//$pass=false;
													
								// suite de la constitution de la requete
								if($zone_saisie['type'] <> 'l'){ // sont exclure les types label 
									if ($cptr==0){
										$sql_donnees.=  $nomtableliee.'.'.$rs->fields['CHAMP_PERE']. ' AS '. $rs->fields['CHAMP_PERE'];			
									}else{
										$sql_donnees.=  ','.$nomtableliee.'.'.$rs->fields['CHAMP_PERE']. ' AS '. $rs->fields['CHAMP_PERE'];
									}
									$cptr++;
								}		
								
								/*							 
								$sql_join	=' SELECT  C.N_JOINTURE,B.CHAMP_PERE '.
											 ' FROM DICO_ZONE_JOINTURE AS A,DICO_ZONE AS B, '. 
											 ' DICO_ZONE_JOINTURE AS C '. 
											 ' WHERE A.ID_ZONE='.$rs->fields['ID_ZONE'].
											 ' AND A.N_JOINTURE=C.N_JOINTURE '. 
											 ' AND A.ID_THEME='.$id_theme.
											 ' AND C.ID_THEME='.$id_theme.
											 ' AND C.ID_ZONE<>'.$rs->fields['ID_ZONE'].
											 ' AND C.ID_ZONE=B.ID_ZONE '.
											 ' AND B.CHAMP_PERE IS NOT NULL'; 
											 
																					 
								$rs_join	=	$conn->Execute($sql_join);
								
								$temp	= array();
								while(!$rs_join->EOF){	
									
									$temp	= array($rs_join->fields['N_JOINTURE'],$rs_join->fields['CHAMP_PERE'],$nomtableliee);
									$rs_join->MoveNext();
								}
								if ($temp){
									$pile_jointure[$nomtableliee]['champs_jointures'][]	=	$temp;
								}		
								*/
								$id_zone++;
								$rs->MoveNext();			
							}
					}
					catch (Exception $e) {
							 $erreur = new erreur_manager($e,$sql_zone_);
					}        
					// Fin Traitement Erreur Cas : Execute / GetOne									
					$pile_jointure[$nomtableliee]['champs_select']	=	$sql_donnees;
					
                    // Rangement des zones par TABLE_MERE dans la variable dico	
					$dico['tableau_zone_saisie'][$nomtableliee]	=	$tableau_zone_saisie;
                    
                    // Rangement des types des zones par TABLE_MERE dans la variable dico	
                    $dico['tableau_type_zone_base'][$nomtableliee]	=	$tableau_type_zone_base;
                    
                    //Rangement dans la variable dico des différentes TABLE_MERES obtenues
					$dico['nomtableliee'][]			=	$nomtableliee;
                    
                    // Rangement des nombres de clés de chq TABLE_MERE dans la variable dico	
					$dico['nb_cles'][$nomtableliee]	=	$nb_cles;
					
                    // Rangement des valeurs associées à des clés de chq TABLE_MERE dans la variable dico
					$dico['val_cle'][$nomtableliee]	=	$val_cle;
							
					// Traitement de la clause order by	 
					
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
		// Fin Traitement Erreur Cas : Execute / GetOne
		
		//$dico['code_annee']	 =	$code_annee;
		
		$sql_from=' FROM ';
		$i	=	0;
		if(is_array($pile_table_liee))
		foreach ($pile_table_liee as $table_liee){
			if ($i==0){
					$sql_from.= $table_liee;
			}else{		
				$sql_from.= ','.$table_liee;
			}
			$i++;	
		}
			//echo 'clause from : '.$sql_from;
	  if(is_array($pile_table_liee))
		foreach ($pile_table_liee as $table_liee){
			
			$sql_donnees=	$pile_jointure[$table_liee]['champs_select'];
			$sql_donnees.=	$sql_from.' WHERE ';	
			$j	=	0;
			
			$sql_donnees.=$criteres;
			
						
			// Ajout de la condition order by	
			$sql_donnees.=$sql_order_by[$table_liee];
			$dico['sql_data'][$table_liee] =$sql_donnees;	
		}	
		
		
		//if (isset($dico['template'])){ $this->template 	= file_get_contents('./template/'.$dico['template']);}
		
		// Instanciation dun objet local
		if (!isset($dico['classe'])){
				$dico['classe']='mat_grille';
		}
		$GLOBALS['dico']	=	$dico;		
	}	
		
	/**
	 * 
	 * @access public
	 */		
	
	function __wakeup(){
		$this->conn	= $GLOBALS['conn'];
	}
	
	
	/**
	* Methode set_code_nomenclature():
	* Permet de renseigner l'attribut  "code_nomenclature"
	*<pre>
	*DEBUT
	*	POUR CHAQUE tableliee de nomtableliee
	*		POUR CHAQUE tableau_zone_saisie de tableliee
	*			SI tableau_zone_saisie(TYPE)=’cmu’ ou ‘cmm’ et EXISTE (SQL) ALORS
	*				rs <-- Record_Set(SQL)
	*				table_ref <-- tableau_zone_saisie[table_ref]
	*				champ <-- tableau_zone_saisie[champ]
	*				code_nomenclature[tableliee][table_ref] <-- rs(champ)
	*			FIN SI
	*		FIN POUR
	*	FIN POUR
	*FIN
	*</pre>
	*/
	function set_code_nomenclature(){
		foreach($this->nomtableliee as $nomtableliee){
			foreach($this->tableau_zone_saisie[$nomtableliee] as $zone_saisie){
				if( ($zone_saisie['type']=='cmu') or ($zone_saisie['type']=='sco') or ($zone_saisie['type']=='cmm') or 
						($zone_saisie['type']=='co') or ($zone_saisie['type']=='m') or ($zone_saisie['type']=='b')){
					if (!is_null($zone_saisie['sql']) and (trim($zone_saisie['sql']) <> '') ){
							//echo '<br>'.$zone_saisie['sql'].'<br>';
							// Traitement Erreur Cas : Execute / GetOne
							try {            
									$rs		= 	$this->conn->Execute($zone_saisie['sql']);
									if($rs ===false){                
											 throw new Exception('ERR_SQL');   
									}
									$code	=	array();
									while (!$rs->EOF) {
										//$code[] = $rs->fields[];
										$code[] = $rs->fields[$this->get_champ_extract($zone_saisie['champ'])];
										$rs->MoveNext();							
									}
									$this->code_nomenclature[$nomtableliee][$zone_saisie['table_ref']]	=	$code;
							}
							catch (Exception $e) {
									 $erreur = new erreur_manager($e,$zone_saisie['sql']);
							}        
							// Fin Traitement Erreur Cas : Execute / GetOne								
					}
				}
			}
			//echo'VALS CODE <pre>';
			//print_r($this->code_nomenclature[$nomtableliee]);
		}	
	}	
	
	
	/**
	*METHODE :  set_champs ( ) :
	<pre>
	* Permet de récupérer les champs (attribut : $champs) dans l'ordre
	* la variable $champs sera renseignée à partir tableau_zone_saisie['champ']
	*</pre>
	*<pre>
	*DEBUT
	*	POUR CHAQUE tableliee de nomtableliee
	*		POUR CHAQUE tableau_zone_saisie de tableliee
	*			champ <-- tableau_zone_saisie[champ]
	*			champs[tableliee] <-- AJOUTER(champ)
	*		FIN POUR
	*	FIN POUR
	*FIN
	*</pre>
	*/
	function set_champs(){
		
		foreach($this->nomtableliee as $nomtableliee){
			foreach ($this->tableau_zone_saisie[$nomtableliee] as $elt){
				if($elt['type']<>'l'){ // les champs de maj doivent etre différents du type label
					if(is_array($elt['champ'])){
						foreach($elt['champ'] as $champ)
							$this->champs[$nomtableliee][]	=	$champ;
					}
					else{
						$this->champs[$nomtableliee][]	=	$elt['champ'];
					}
				}
			}
		}
			//echo'CHAMPS<pre>';
			//print_r($this->champs);
	
	}
	
	
	/**
	* METHODE :  get_donnees_bdd ( )
	* Permet de récupérer les données sous forme de n-uplet basé  
	* sur l'ensemble des champs de l'attribut $champs
	*
	*<pre>
	*DEBUT
	*	POUR CHAQUE tableliee de nomtableliee
	*		SI PAS VARS_GLOBALS['donnees_bdd'][tableliee] ALORS
	*			rs <-- Record_Set(sql_data[tableliee])
	*			VARS_GLOBALS['donnees_bdd'][tableliee] <-- rs(ALL)
	*			faire_correspondances(tableliee)
	*			limiter_affichage(tableliee)
	*		FIN SI
	*	FIN POUR
	*FIN
	*</pre>
	*/
	function get_donnees_bdd(){
		// préparation de la requete
		//$nomtable, $tab_zone, $code_etablissement
		foreach($this->nomtableliee as $nomtableliee){
		 	if(!isset($this->VARS_GLOBALS[$nomtableliee]['donnees_bdd'])){
				$sql = $this->sql_data[$nomtableliee];
				$tab_donnees_bdd = array();
			  
			  	//echo '<br>'.$sql .'<br>';
				// Traitement Erreur Cas : Execute / GetOne
				try {            
						$recordSet	=	$this->conn->Execute($sql);
						if($recordSet ===false){                
								 throw new Exception('ERR_SQL');   
						}
						$i=0;
						while (!$recordSet->EOF){
							$ligne = $recordSet->fields;
							$j=0;
							foreach ($ligne as $col){
								$tab_donnees_bdd[$i][$j]=$col;
								$fld = $recordSet->FetchField($j);
								$fld_name = $fld->name;
								if( trim($col) == '255' && (preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$fld_name)) ){
									$tab_donnees_bdd[$i][$j] = '';
								}
								$j++;
							}
							$i++;			
							$recordSet->MoveNext();
						}
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$sql);
				}        
				// Fin Traitement Erreur Cas : Execute / GetOne				
				//$this->matrice_donnees[$nomtableliee]	=	$tab_donnees_bdd;
				$this->VARS_GLOBALS[$nomtableliee]['donnees_bdd'] = $tab_donnees_bdd;
			}
			//echo'DONNEES BDD<pre>';
			//print_r($this->VARS_GLOBALS[$nomtableliee]['donnees_bdd']);
            
			// Limitation des données par rapport à un début et au nombre de lignes dispo sur le template
			$this->faire_correspondances($nomtableliee);
			$this->limiter_affichage($nomtableliee);
			
		}
	}		
	
	
	/**
	* Méthode extraire_valeur_matrice permet l'extraction de la valeur encodée dans les champs dont les valeurs
	* renvoyées sont sous la forme $nom_champ_ligne_valeur
	*
	* @access public
	* 
	*/

	function extraire_valeur_matrice($texte){
		// cette permet l'extraction de la valeur encodée dans le champ de type  matriciel 
		$val = explode('_',$texte);
		return ($val[count($val)-1]);
	}


	/**
	* Méthode est_ds_tableau($elem,$tab)
    * Permet de vérifier l'existenc de l'élément $elem dans le tableau $tab
	*
	* @access public
	* 
	*/
    function est_ds_tableau($elem,$tab){
		if(is_array($tab))
			foreach($tab as $elements){
				if( $elements == $elem ){
					return true;
				}
			}
	}
	
	/**
	* METHODE :  get_position_type_csu($nomtableliee):
	* Retourne la position du champ de type 'csu': dans la TABLE_MERE : $nomtableliee
    * On ne doit en avoir plus d'un
	*
	* @access public
	* 
	*/
	function get_position_type_csu($nomtableliee){
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			if( $zone_saisie['type']=='csu' ){
				return $pos;
			}
		}
	}

	/**
	* METHODE :  get_champ_extract($nom_champ):
	* Retourne les 30 ou 31 premiers caractères de $nom_champ en fonction du type de SGBD 
    * pour extraire la valeur du champ $nom_champ dans un recordset
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
            $champ_extract = substr($nom_champ ,0,$taille_max_extract); 
        }
        return($champ_extract);
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
	* METHODE :  get_lignes_tpl_saisies($matr):
	* Permet de selectionner les différentes lignes du template qui contiennent 
    * des données relatives à la variable $matr =>$_POST
	*
	* @access public
	* 
	*/
	function get_lignes_tpl_saisies($matr){
			$lignes_tpl_saisies = array();
			foreach($this->nomtableliee as $nomtableliee){
					for($ligne=0; $ligne < $this->nb_lignes; $ligne++){
								///// on teste si la ligne est saisie
							$ligne_vide = true;
							$nb_colonnes = count($this->tableau_zone_saisie[$nomtableliee]);
							foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								///////////////
								if( $zone_saisie['type']=='tvm' ){ // champ structure matricielle
									if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
									
									$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm']; // clé associée à la structure matricielle
									$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$pos_cmm]['table_ref'];
									
									foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
                                        // Parcours des codes nomenc
										$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;			
										if( isset($matr[$v]) and $matr[$v]<>''){ // Test de la saisie ou non de la ligne				
											$ligne_vide = false;
											$nb_ligne_post++;
											break;
										}
									}
									if($ligne_vide == false)
										break;
								}
                                // Type de choix unique
								elseif( $zone_saisie['type']=='cmu' or $zone_saisie['type']=='sco' 
												or $zone_saisie['type']=='csu' or $zone_saisie['type']=='co' or $zone_saisie['type']=='m' 
												or $zone_saisie['type']=='s' or $zone_saisie['type']=='sys_txt' or $zone_saisie['type']=='b') {
									//echo"<br>type_zone=".$zone_saisie['type']."";
									$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
									if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
									//$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'];
									$v = $nom_champ.'_'.$ligne ;			
									if( isset($matr[$v]) and $matr[$v]<>''){	// // Test de la saisie ou non de la ligne			
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
	*METHODE :  faire_correspondances ( tableliee )
	*<pre>
	*DEBUT
	*	POUR CHAQUE tableliee de nomtableliee
	*		POUR CHAQUE ligne_donnees de VARS_GLOBALS['donnees_bdd']
	*			VARS_GLOBALS[tableliee][ligne_code] <-- Combinaison_Unique_Clés(ligne_donnees)
	*			(cette variable permettra d’associer chacun de ces enregistrements à une ligne du template)
	*		FIN POUR
	*	FIN POUR
	*FIN
	*</pre>
	*/
	function faire_correspondances($nomtableliee){
		////////////////// position du champ clé à valeur multiple // ex: code_niveau
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			if( $zone_saisie['type']=='cmm' ){
				$this->VARS_GLOBALS[$nomtableliee]['pos_cmm'] = $pos;
				break;
			}
		}
		////////////////// position du champ clé à valeur unique de type texte// ex: code_etablissement
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			if( $zone_saisie['type']=='csu' ){
				$this->VARS_GLOBALS[$nomtableliee]['pos_csu'] = $pos;
				break;
			}
		}
		////////////////// positions des champs clés à valeur unique // ex: code_regroupement, code_distance
		foreach($this->tableau_zone_saisie[$nomtableliee] as $pos => $zone_saisie){
			if( $zone_saisie['type']=='cmu' or $zone_saisie['type']=='sco' or $zone_saisie['type']=='csu' or $zone_saisie['type']=='sys_txt' ){
				$this->VARS_GLOBALS[$nomtableliee]['pos_cmu'][$pos] = $this->tableau_zone_saisie[$nomtableliee][$pos]['champ'];
			}
		}
		////////////////////// les differentes valeurs prises par chaque clé de type cmu
		//$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm'];
		if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'])){
			foreach( $this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos => $nom_champ ){
				$mat = array();
				foreach($this->VARS_GLOBALS[$nomtableliee]['donnees_bdd'] as $ligne_donnees){
					if( isset($ligne_donnees[$pos]) ){
						if( !$this->est_ds_tableau($ligne_donnees[$pos],$mat) ){
							$mat[] = $ligne_donnees[$pos];
						}
					}
				}
				//if(count($mat))
					//sort ($mat,SORT_NUMERIC);
				$this->VARS_GLOBALS[$nomtableliee]['val_cmu'][$pos] = $mat;
				//echo'<pre>';
				//print_r($this->VARS_GLOBALS[$nomtableliee]['val_cmu'][$pos]);
			}
		}
		//////////////////////// faire correspondre les lignes avec les combinaisons de clés ccu
		//$fin_parcours_imbrique = '';
		if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'])){
			foreach($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos => $value){
				$tab_pos_cmu[] = $pos;
				//$fin_parcours_imbrique.=' } ';
			}
		}
		
		/////////////////////////// FAIRE LES EQUIVALENCES ENTRE LIGNES ET LES CODES DES DLES
		$mat = array();
		foreach( $this->VARS_GLOBALS[$nomtableliee]['donnees_bdd'] as $ligne_donnees){
			$assoc_cmu_courante = array();
			if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'])){
				foreach( $this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos =>$nom_champ ){
					$assoc_cmu_courante[] = $ligne_donnees[$pos];
				}
			}
			if(!$this->est_ds_tableau($assoc_cmu_courante,$mat)){
				$mat[] = $assoc_cmu_courante;	
			}
		}
		$this->VARS_GLOBALS[$nomtableliee]['ligne_code'] = $mat;
		//////////////////////////////////////////////////////////////////////////////	
	}
		
	
	/**
	* METHODE :  limiter_affichage ( tableliee )
	* Permet à partir du jeu d'enregistrement global, de se positionner 
	* sur les données concernant la page en cours
	* Ces données sont rangées dans la variable  : $matrice_donnees
	*<pre>
	*DEBUT
	*	matrice_donnees[tableliee] = array
	*	limite_enr <-- LIMITER(VARS_GLOBALS , debut, nb_lignes)
	*	( N’avoir que les codes uniques des enregistrements pour la 
	*   page en cours )
	*	POUR CHAQUE ligne ligne_donnees de VARS_GLOBALS['donnees_bdd']
	*		SI Combinaison_Unique_Clés(ligne_donnees) EST DANS limite_enr
	*			RANGER ligne_donnees dans matrice_donnees[tableliee]
	*		FIN SI
	*	FIN POUR
	*FIN
	*</pre>
	*/	
	function limiter_affichage($nomtableliee){
		//if(!isset($this->VARS_GLOBALS[$nomtableliee]['ligne_code']))
		//$this->VARS_GLOBALS[$nomtableliee]['ligne_code'] = $this->faire_liaison_ligne_code($nomtableliee) ;	
		if(!isset($GLOBALS['nbre_total_enr']))
			$GLOBALS['nbre_total_enr'] = count( $this->VARS_GLOBALS[$nomtableliee]['ligne_code'] );
		
		$ligne_code_limit = array_slice( $this->VARS_GLOBALS[$nomtableliee]['ligne_code'], $GLOBALS['debut'], $GLOBALS['cfg_nbres_ppage']);
		foreach($ligne_code_limit as $val_code)
			$this->VARS_GLOBALS[$nomtableliee]['ligne_code_limit'][] = $val_code;
		//echo'**************<pre>';
		
		//$indice_cle = $this->nb_cles[$nomtableliee] - 1;
		if(is_array($this->VARS_GLOBALS[$nomtableliee]['donnees_bdd']))
		foreach( $this->VARS_GLOBALS[$nomtableliee]['donnees_bdd'] as $ligne_donnees){
			$assoc_cmu_courante = array();
			if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu']))
			foreach( $this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos =>$nom_champ ){
				$assoc_cmu_courante[]=$ligne_donnees[$pos];
			}
			if($this->est_ds_tableau($assoc_cmu_courante,$this->VARS_GLOBALS[$nomtableliee]['ligne_code_limit'])){
				$this->matrice_donnees[$nomtableliee][] = $ligne_donnees;	
			}
		}
		$GLOBALS['nbenr'] = count($this->matrice_donnees[$nomtableliee]);
			//echo'matrice_donnees<pre>';
			//print_r($this->matrice_donnees[$nomtableliee]);

	}
	
	
	/**
	* METHODE :  preparer_donnees_tpl ( tableliee )
	* Permet de mettre les données $matrice_donnees
	* dans une position telle que chaque ligne d'enregistrement 
	* liée à un identificateur de ligne (ici clé incrémentale)
	* soit bien positionné pour le template Ces données sont 
	* rangées dans la variable de la classe : $matrice_donnees_tpl
	*<pre>
	*DEBUT
	*	matrice_donnees_tpl[tableliee] <-- array
	*	POUR CHAQUE ligne  ligne_donnees de matrice_donnees [tableliee]
	*		SI Combinaison_Unique_Clés(ligne_donnees) change de valeur ALORS
	*			ligne++ (données doivent être sur une ligne du template)
	*		FIN SI
	*		matrice_donnees_tpl[tableliee][ligne] <-- EMPILER (ligne_donnees)
	*	FIN POUR
	*FIN
	*</pre>
	*/
	function preparer_donnees_tpl($nomtableliee){
		$mat = array();
		$tab_assoc_cmu = array();
		$ligne = -1;
		$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm']; // récuperer la position de la clé val multi
		//$indice_cle = $this->nb_cles[$nomtableliee] - 1;
			if(is_array($this->matrice_donnees[$nomtableliee])){
				if($pos_cmm){ // si clé val multi existe
						foreach($this->matrice_donnees[$nomtableliee] as $ligne_donnees){
							
							$tab_val_cmu = array();
							
							$tab_assoc_cmu_courante = array();
							if(is_array($this->VARS_GLOBALS[$nomtableliee]['pos_cmu'])){
								foreach( $this->VARS_GLOBALS[$nomtableliee]['pos_cmu'] as $pos_c =>$nom_champ ){
									$tab_assoc_cmu_courante[] = $ligne_donnees[$pos_c];
								}
							}
							if(!$this->est_ds_tableau($tab_assoc_cmu_courante,$tab_assoc_cmu)){
								
								$tab_assoc_cmu[] = $tab_assoc_cmu_courante;	
								$ligne++ ;
								$ligne_courante = $ligne ;
								for( $pos=0; $pos <= $pos_cmm; $pos++ ){
									$mat[$ligne_courante][$pos] = $ligne_donnees[$pos];
								}
								$mat[$ligne_courante][$pos_cmm] = array();
							}else{ // recherche de la ligne associée à la combinaison des clés courantes
								foreach($tab_assoc_cmu as $curr_ligne => $combined_keys){
									if( !count(array_diff_assoc($combined_keys, $tab_assoc_cmu_courante)) ){
										$ligne_courante = $curr_ligne ;
										break;
									}
								}
							}
							$cle_cmm = $ligne_donnees[$pos_cmm];
							//echo"cle_cmm=$cle_cmm-----------------<br>";
							
							//echo'<pre>';
							//print_r($mat[$ligne][$pos_cmm]);
							//echo'<br>********************<br>';
							
							for( $pos=$pos_cmm+1; $pos < count($ligne_donnees); $pos++ ){
								$mat[$ligne_courante][$pos_cmm][$cle_cmm][$pos] = $ligne_donnees[$pos];
							}
						}
				}
				else{ // pas de clé val multi
                        $mat = $this->matrice_donnees[$nomtableliee];
				}
				$this->matrice_donnees_tpl[$nomtableliee] = $mat ;	
			}
				//	echo'matrice_donnees_tpl<pre>';
				//	print_r($this->matrice_donnees_tpl);
	}
	
	
	/**
	* METHODE :  remplir_template ( template )
	* Permet de données des valeurs aux variables du template
	* et d'afficher la page HTML .
	*<pre>
	*DEBUT
	*	POUR CHAQUE tableliee de nomtableliee
	*		preparer_donnees_tpl(tableliee)
	*		POUR ligne allant de 0 à nb_lignes FAIRE
	*			POUR chaque zone de colonne col de la tableliee
	*				SELON zone(TYPE)  ( le type de la zone )
	*					nom_champ <-- zone(CHAMP)
	*					PREPARER LA VARIABLE TEMPLATE ( nom_champ, ligne, zone(TYPE)  )
	*				FIN SELON
	*			FIN POUR
	*		FIN POUR
	*	FIN POUR
	*	EVALUER_ET_AFFICHER(template)
	*FIN
	*</pre>
	*/
	function remplir_template($template){
		$code_etablissement = $this->code_etablissement;
		$code_annee = $this->code_annee;
		$code_filtre = $this->code_filtre;
		//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
		${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']} = $code_annee;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;
		//Fin ajout Hebie
		foreach($this->nomtableliee as $nomtableliee){ // pour chq TABLE_MERE
		$result_interf = array() ; 
		$this->preparer_donnees_tpl($nomtableliee);
		//echo'<pre>';
		//print_r($this->matrice_donnees_tpl[$nomtableliee]);
			//if(is_array($this->matrice_donnees_tpl[$nomtableliee])){			
				for($ligne=0;$ligne<$this->nb_lignes;$ligne++){ // pour chq ligne du Template
					foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){ // Parcours des zones
						if( $zone_saisie['type']=='tvm' ){ // Structure matricielle : val multi
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							
							$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm']; // clé val multi
							$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$pos_cmm]['table_ref'];
							if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice])){
								foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
                                    //  parcours des codes clé val multi
									if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$pos_cmm][$val_matrice][$colonne])){
										$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$pos_cmm][$val_matrice][$colonne];
										//$val_champ_base = addslashes($val_champ_base);
										${$nom_champ.'_'.$ligne.'_'.$val_matrice} = ''.$val_champ_base.'';
										//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
									}
									else{
										//${$nom_champ.'_'.$ligne} = '\'\'';
										${$nom_champ.'_'.$ligne.'_'.$val_matrice} = '';	
									}
								}
							}
						}
						elseif( $zone_saisie['type']=='l'){ // cas d'un LABEL
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							//echo"<br>$nom_champ<br>";
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							//$sql	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql'];
							
							$sql_temp		=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql'];
							$sql_temp		= str_replace('$ligne',"$ligne",$sql_temp);
							
							//echo"<br>$sql_temp<br>";
							$chaine_eval = "\$sql=\"$sql_temp\";";
							eval($chaine_eval);
							$sql_label = $sql;

							if (preg_match ("/#([^#]*)#/", $sql, $elem)){
								$v = $elem[1].'_'.$ligne;
								$sql_label = preg_replace("/#([^#]*)#/",$$v,$sql);
							}
							//$chaine_eval = "\$sql_label=\"$sql\";";
							//eval($chaine_eval);
							//echo"<br>$sql_label<br>";
							${$nom_champ.'_'.$ligne} = '';
							if (!is_null($sql_label) and (trim($sql_label) <> '') ){	
								// Traitement Erreur Cas : Execute / GetOne
								try {            
										$rslabel	= $this->conn->execute($sql_label);
										if($rslabel === false){                
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
						elseif( $zone_saisie['type']=='csu' ){ // Clé dont la valeur est saisie comme un type TEXT
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							
							$pos_csu = $this->VARS_GLOBALS[$nomtableliee]['pos_csu'];
							$nom_table_ref = $this->tableau_zone_saisie[$nomtableliee][$pos_csu]['table_ref'];
							
							if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$pos_csu])){
								$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$pos_csu];
								//$val_champ_base = addslashes($val_champ_base);
								${$nom_champ.'_'.$ligne} = ''.$val_champ_base.'';
								//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
							}
							else{
								${$nom_champ.'_'.$ligne} = '';	
							}
						}
						elseif( $zone_saisie['type']=='s' or $zone_saisie['type']=='sys_txt' ){ // champ TEXT
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								if(is_array($nom_champ))
									$nom_champ = $nom_champ[0];
								if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne])){
									$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne];
									//$val_champ_base = addslashes($val_champ_base);
									${$nom_champ.'_'.$ligne} = ''.$val_champ_base.'';
									//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
								}
								else{
									//${$nom_champ.'_'.$ligne} = '\'\'';
									${$nom_champ.'_'.$ligne} = '';	
								}

						}
						elseif( $zone_saisie['type']=='ch' ){ // CHECKBOX
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							//echo"<br>chp chk:$nom_champ=".$this->matrice_donnees[$nomtableliee][$ligne][$colonne]."";
							if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]) and $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]=='1'){
								//$val_champ_base = $this->matrice_donnees[$nomtableliee][$ligne][$colonne];
								//$val_champ_base = addslashes($val_champ_base);
								${$nom_champ.'_'.$ligne} = "'1' CHECKED";
								//${$nom_champ.'_'.$ligne} = '\''.$val_champ_base.'\'';
							}
							else{
								//${$nom_champ.'_'.$ligne} = '\'\'';
								${$nom_champ.'_'.$ligne} = "'1'";	
							}
						}
                        // Choix unique
						elseif( $zone_saisie['type']=='cmu' or $zone_saisie['type']=='sco' or $zone_saisie['type']=='co' 
										or $zone_saisie['type']=='m' or $zone_saisie['type']=='b' ) {
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							if(is_array($nom_champ))
								$nom_champ = $nom_champ[0];
							$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'];
							
							if( isset($zone_saisie['DynContentObj']) && ($zone_saisie['DynContentObj']== 1) ){
								//echo "<pre>";
								//print_r($zone_saisie);
								
								$html_ContentObj = '' ;
								//if(!count($result_interf[$zone_saisie['id_zone']])){
									$result_interf = $this->requete_interface($zone_saisie['table_interface'], $zone_saisie['champ_interface'], $zone_saisie['champ'], $this->id_systeme, $zone_saisie['sql_interface'], $code_annee, $code_etablissement);
								//}
								$result_interface	=	$result_interf;
								if( is_array($result_interface) ){
									foreach($result_interface as $element_interf){
										$html_ContentObj .= "\t\t\t<OPTION VALUE=$".$zone_saisie['champ']."_".$ligne."_".$element_interf[$this->get_champ_extract($zone_saisie['champ'])].">"; 
										$html_ContentObj .= $element_interf['LIBELLE']."</OPTION>\n"; 
									}
								}
								$template = str_replace('<!--DynContentObj_'.$zone_saisie['id_zone'].'_'.$zone_saisie['champ'].'_'.$ligne.'-->', $html_ContentObj, $template); 
							}
							
							if(isset($this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne])){ // s'il y'a valeur en Base
								$val_champ_base = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne];
								//echo"*$val_champ_base*";
								if(isset($this->code_nomenclature[$nomtableliee][$nom_matrice])){
									if(is_array($this->code_nomenclature[$nomtableliee][$nom_matrice])){
										foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
											// Parcours des codes nomenclatures
											if($val_matrice == $val_champ_base){ //  Si Correspondance entre code Template et code Base
												// Sélectionner la case correspondante										
												if($zone_saisie['type']=='co' or $zone_saisie['type']=='sco' ){
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
						//// traitement d'un champ lié à une interface de saisie
						if(isset($zone_saisie['champ_interface']) and ($zone_saisie['champ_interface']) ){
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ_interface'];
								
								if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
								
								$v = $nom_champ.'_'.$ligne ;		
								if(!isset($$v)){			
									$sql_temp	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql_interface'];
									//$sql_temp		=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['sql'];
									$sql_temp		= str_replace('$ligne',"$ligne",$sql_temp);
									//echo $sql_temp;
																	
									$chaine_eval = "\$sql=\"$sql_temp\";";
									//echo"<br>$chaine_eval<br>";
									eval($chaine_eval);
									$sql_label = $sql;
									
									if (preg_match ("/#([^#]*)#/", $sql, $elem)){
											$v = $elem[1].'_'.$ligne;
											$sql_label 	= preg_replace("/#([^#]*)#/",$$v,$sql);
									}
	
									//echo"<br>$ligne = $sql_label<br>";
									if (!is_null($sql_label) and (trim($sql_label) <> '') ){	
											// Traitement Erreur Cas : Execute / GetOne
											try {            
													$rslabel	= $this->conn->Execute($sql_label);
													if($rslabel ===false){                
															 //throw new Exception('ERR_SQL');   
													}
													$$v 	= 	$rslabel->fields[$this->get_champ_extract($nom_champ)];
													//echo"<br>$nom_champ = ".$$v."<br>";								 
											}
											catch (Exception $e) {
													 $erreur = new erreur_manager($e,$sql_label);
											}        
											// Fin Traitement Erreur Cas : Execute / GetOne										
									}
								}
						}
						///////////////////////////////
					}
				}
			//}
		}
		//$template = addslashes($template);
		return eval("echo \"".str_replace('"','\"',$template)."\";");
        /*$code = '$result = sprintf("%s", "'.$template.'");';
        eval($code);
        return $result;*/
	}	
	
    /**
	* METHODE :  verif()
	* Effectue la vérification de des elements envoyés 
	* suivant les régles de gestion associées
	* @access public
	*/
	function verif($matr){
			$code_etablissement = $this->code_etablissement;
			$code_annee = $this->code_annee;
			$code_filtre = $this->code_filtre;
			$lignes_tpl_saisies = $this->get_lignes_tpl_saisies($matr);
			foreach($this->nomtableliee as $nomtableliee){
					foreach( $lignes_tpl_saisies as $key => $ligne ){
							foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){
									if( $zone_saisie['type']=='tvm' ){
											$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
											if(is_array($nom_champ))
												$nom_champ = $nom_champ[0];
											
											$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm'];
											$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$pos_cmm]['table_ref'];
									/////////////////
											/*$tab_nom_champ =	array();
											$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
											if(!is_array($nom_champ))
												$tab_nom_champ[] = $nom_champ;
											else
												$tab_nom_champ	 = $nom_champ;
											$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'];*/	
											//foreach($tab_nom_champ as $nom_champ){
											$all_regles = $this->tab_regles_zone[$nom_champ];
											//echo"$nom_champ<pre>";
											//print_r($all_regles);
											if( isset($all_regles) and is_array($all_regles) ){ //si champ controlable
													foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
															$champ_saisie = $nom_champ.'_'.$ligne.'_'.$val_matrice;
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
									else{
											$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
											$all_regles = $this->tab_regles_zone[$nom_champ];
											if( isset($all_regles) and is_array($all_regles) ){ //si champ controlable
													$champ_saisie = $nom_champ.'_'.$ligne;
													$erreur = All_Controle($matr, $all_regles, $champ_saisie);
													if($erreur){
															$this->reafficher_template($this->template, $matr, $champ_saisie);
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
	* @access public
	*/
	function reafficher_template($template, $post, $champ_erreur){
		$code_etablissement = $this->code_etablissement;
		$code_annee = $this->code_annee;
		$code_filtre = $this->code_filtre;
		//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
		${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']} = $code_annee;
		${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']} = $code_filtre;
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
								if (!is_null($sql_label) and (trim($sql_label) <> '') ){	
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

						elseif( $zone_saisie['type']=='s' or $zone_saisie['type']=='sys_txt' or $zone_saisie['type']=='csu' ){
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

						elseif( $zone_saisie['type']=='cmu' or $zone_saisie['type']=='sco' or $zone_saisie['type']=='co' 
										or $zone_saisie['type']=='m' or $zone_saisie['type']=='b' ){
							//
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								$v	=	$nom_champ.'_'.$ligne ;
								if(is_array($nom_champ))
										$nom_champ = $nom_champ[0];
								$v	=	$nom_champ.'_'.$ligne ;
								$val_post = $this->extraire_valeur_matrice($post[$v]);
								$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'];

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
						elseif($zone_saisie['type']=='tvm'){
								//echo'<pre>';
								//print_r($this->matrice_donnees_tpl[$nomtableliee][$ligne]);
								/*$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								if(!is_array($nom_champ))
										$tab_nom_champ[] = $nom_champ;
								else
										$tab_nom_champ = $nom_champ;
								$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'];*/
								$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
								if(is_array($nom_champ))
									$nom_champ = $nom_champ[0];
								
								$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm'];
								$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$pos_cmm]['table_ref'];
																				
								if( isset($this->code_nomenclature[$nomtableliee][$nom_matrice]) and is_array($this->code_nomenclature[$nomtableliee][$nom_matrice]) ){
										foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
												//foreach($tab_nom_champ as $ord=>$nom_champ){
														$v	=	$nom_champ.'_'.$ligne.'_'.$val_matrice;
														${$nom_champ.'_'.$ligne.'_'.$val_matrice} = $post[$v];
												//}
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
								elseif(!isset($$v) and $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne]){
										${$nom_champ.'_'.$ligne} = $this->matrice_donnees_tpl[$nomtableliee][$ligne][$colonne];
								}
						}
						//// traitement d'un champ lié à une interface de saisie
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
		mettre_en_evidence_champ_erreur($champ_erreur);
		// Affichage de la barre de navigation
		afficher_barre_nav(true,true,array('theme','type_ent_stat'));
		
		//eval ("\$eval_template =addslashes($template);");
		//return eval("print ($eval_template);");
	}	

	
	/**
	* METHODE :  get_post_template ( POST ) 
	* Permet de configurer la variable $matrice_donnees_post 
	* aprés soumission de la page HTML via POST
	* et d'afficher la page HTML 
	*<pre>
	*DEBUT
	*	POUR CHAQUE tableliee de nomtableliee
	*		POUR ligne allant de 0 à nb_lignes FAIRE
	*			SI LIGNE_POST NON VIDE ET NON DELETE LIGNE_POST
	*				nom_champ <-- zone[CHAMP]
	*				SI EST POSTE LA VARIABLE (nom_champ , ligne) ALORS
	*					RANGER LA VALEUR DANS matrice_donnees_post
	*				FIN SI
	*			FIN SI
	*		FIN POUR
	*	FIN POUR
	*FIN
	*</pre>
	*/
	function get_post_template($matr){
		//global $val_cle;
		//echo'_POST<pre>';
		//print_r($_POST);
		//echo'tableau_zone_saisie<pre>';
	  //print_r($this->tableau_zone_saisie);

		foreach($this->nomtableliee as $nomtableliee){	
			$pos_cle_incr	=	$this->nb_cles[$nomtableliee] - 1;
			$matrice = array();
			//$max_cle_incr = $this->get_cle_max($nomtableliee) ;
			
			$nb_ligne_post = 0;
			$ligne_mat = -1;
			for($ligne=0; $ligne < $this->nb_lignes; $ligne++){
				///// on teste si la ligne est saisie
				$ligne_vide = true;
				$nb_colonnes = count($this->tableau_zone_saisie[$nomtableliee]);
				foreach($this->tableau_zone_saisie[$nomtableliee] as $colonne => $zone_saisie){
					$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
					///////////////
					if( $zone_saisie['type']=='tvm' ){
						if(is_array($nom_champ))
							$nom_champ = $nom_champ[0];
						
						$pos_cmm = $this->VARS_GLOBALS[$nomtableliee]['pos_cmm'];
						$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$pos_cmm]['table_ref'];
						
						foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
							$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;			
							if( isset($matr[$v]) and $matr[$v]<>''){				
								$ligne_vide = false;
								$nb_ligne_post++;
								break;
							}
						}
						if($ligne_vide == false)
							break;
					}
					elseif( $zone_saisie['type']=='cmu' or $zone_saisie['type']=='sco' 
									or $zone_saisie['type']=='csu' or $zone_saisie['type']=='co' or $zone_saisie['type']=='s'
									or $zone_saisie['type']=='m' or $zone_saisie['type']=='b' or $zone_saisie['type']=='sys_txt') {
						//echo"<br>type_zone=".$zone_saisie['type']."";
						$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
						if(is_array($nom_champ))
							$nom_champ = $nom_champ[0];
						//$nom_matrice = $this->tableau_zone_saisie[$nomtableliee][$colonne]['table_ref'];
						$v = $nom_champ.'_'.$ligne ;			
						if( isset($matr[$v]) and $matr[$v]<>''){				
							$ligne_vide = false;
							$nb_ligne_post++;
							break;
						}
					}
				}
	
				/////////////////
				if( ($ligne_vide) == false and (!isset($matr['DELETE_'.$ligne])) ){			
					$ligne_mat++;
					for($colonne=0; $colonne < count($this->tableau_zone_saisie[$nomtableliee]); $colonne++){
						if($this->tableau_zone_saisie[$nomtableliee][$colonne]['type'] == 'c'){
							$nom_cle = $this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							$matr_1[$ligne_mat][$colonne] = $this->val_cle[$nomtableliee][$nom_cle];
						}
						$type_zone = $this->tableau_zone_saisie[$nomtableliee][$colonne]['type'];
						//echo"<br>type_zone=".$type_zone."";
						if( $type_zone == 'cmu' or $type_zone == 'sco' or $type_zone == 'csu'  or $type_zone=='co' 
								or $type_zone=='s' or $type_zone=='m' or $type_zone=='b' or $type_zone=='sys_txt' ){
							//$nom_cle = $this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
							//$matr_1[$ligne_mat][$colonne] = $this->val_cle[$nomtableliee][$nom_cle];
							$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];				
							$v = $nom_champ.'_'.$ligne ;				
							if( isset($matr[$v]) and $matr[$v]<>''){					
								$val_champ = $matr[$v];
								if($type_zone == 'cmu' or $type_zone == 'sco' or $type_zone=='co' or $type_zone=='m' or $type_zone=='b')
									$val_champ = $this->extraire_valeur_matrice($matr[$v]);						
								$matr_1[$ligne_mat][$colonne] = $val_champ ;
							}
							else
								$matr_1[$ligne_mat][$colonne] = '' ;
						}
					}
					$pos_cmm 			= $this->VARS_GLOBALS[$nomtableliee]['pos_cmm'];
					$nom_matrice 	= $this->tableau_zone_saisie[$nomtableliee][$pos_cmm]['table_ref'];
					/////////////////////////////////
					if($pos_cmm){
							foreach($this->code_nomenclature[$nomtableliee][$nom_matrice] as $val_matrice){
								$matr_1[$ligne_mat][$pos_cmm] = $val_matrice ; 
								$mat = array();
								$mat = $matr_1[$ligne_mat] ;
								$mat_plus = array();
								$champs_saisis = false ;
								
								$nb_colonnes = count($this->tableau_zone_saisie[$nomtableliee]);
								for($colonne = $this->nb_cles[$nomtableliee]; $colonne < $nb_colonnes; $colonne++){
									if( $this->tableau_zone_saisie[$nomtableliee][$colonne]['type']=='tvm' ){
										$nom_champ	=	$this->tableau_zone_saisie[$nomtableliee][$colonne]['champ'];
										if(is_array($nom_champ))
											$nom_champ = $nom_champ[0];
		
										$v = $nom_champ.'_'.$ligne.'_'.$val_matrice ;			
										if( isset($matr[$v]) and $matr[$v] <> '' ){				
											$champs_saisis = true ;
											$val_champ = $matr[$v];
											$mat_plus[] = $val_champ ;
										}
										else{
											$mat_plus[] = '' ;
										}
									}
								}
								if($champs_saisis==true){
									foreach($mat_plus as $val_mesure)
										$mat[] = $val_mesure;
									ksort($mat);
									$matrice[] = $mat;
									$champs_saisis = false ;
								}
							}
					}
					else{
							$matrice = $matr_1;
					}
    		}
			}
			$this->matrice_donnees_post[$nomtableliee]	=	$matrice;
			//echo'matrice_donnees_post<pre>';
			//print_r($this->matrice_donnees_post[$nomtableliee]);
		}
	}
	
	
	/**
	* METHODE :  comparer ( MATR1 , MATR2 )
	* Permet d'effectuer la comparaison entre $matrice_donnees_post 
	* et $matrice_donnees_post 
	* Le resultat est dans matrice_donnees_bdd et sera directement 
	* utlisé pour la MAJ de la base de données.
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
	*/
	function comparer ($matr1,$matr2){
		/*
		echo 'matr1<pre>';
		print_r($matr1);
		echo '<br><br>matr2<pre>';
		print_r($matr2);
		*/
		// Cette fonction permet de faire la comparaison matricielle complexe		
		foreach($this->nomtableliee as $nomtableliee){
			$indice_cle	=	$this->nb_cles[$nomtableliee] - 1;
					
			$result 	=	array();
			$i = 0;
			if(is_array($matr2[$nomtableliee]))
				foreach ($matr2[$nomtableliee] as $elt)
				{
						// cette variable contient la clé identifiant de l'élément matriciell
						$cle	=	associer_identifiant($elt,$indice_cle);
						$action	=	existe_element_grille_action($cle,$elt,$indice_cle,$matr1[$nomtableliee]);		
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
						
						$cle	=	associer_identifiant($elt,$indice_cle);
						$action	=	existe_element_grille_action($cle,$elt,$indice_cle,$matr2[$nomtableliee]);
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
			$this->matrice_donnees_bdd[$nomtableliee]	=	$result;
		}		
	}
	
	
	function ordre_insert_tables()
	{
		// cette fonction permettra de mettre les tables liées ds un bon ordre d'insertion
		// ex : insertion ds GROUPE_PEDAGOGIQUE avant EFFECTIF_GP_AGE
		// pour le moment nous considérons que les tables liées sont créées lors de l'instanciation
		// dans le bon ordre d'insert
		return ($this->nomtableliee);
	}
	
	
	function ordre_delete_tables()
	{
		// cette fonction permettra de mettre les tables liées ds un le bon ordre de suppression
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
	* METHODE :  maj_bdd ()
	* Utilise $matrice_donnees_bdd pour insérer, supprimer, 
	* mettre à jour des données de la base 
	*<pre>
	*DEBUT
	*	(ON EFFECTUE D’ABORD LES UPDATE CAR NE DEMANDANT AUCUN 
	*  ORDRE SUR LES TABLES LIEES)
	*		POUR CHAQUE tableliee de nomtableliee
	*			PARCOUCIR matrice_donnees_bdd [tableliee]
	*				SI ACTION = ‘U’  (cas update)
	*					EFFECTUER REQUETE D’UPDATE
	*				FIN SI
	*			FIN PARCOURIR
	*		FIN POUR
	*	(ON EFFECTUE  LES INSERT APRES AVOIR ORDONNE LES TABLES 
	*  SUIVANT LE  BON ORDRE D’INSERTION)
	*		ordre_insert <-- ordre_insert_tables()
	*		POUR CHAQUE tableliee de ordre_insert
	*			PARCOUCIR matrice_donnees_bdd [tableliee]
	*				SI ACTION = ‘I’  (cas insert)
	*					EFFECTUER REQUETE D’INSERT
	*				FIN SI
	*			FIN PARCOURIR
	*		FIN POUR
	*	(ON EFFECTUE LES DELETE DANS L’ORDRE INVERSE DE L’INSERTION 
	*  DS LES TABLES )
	*		ordre_delete <-- ordre_delete_tables()
	*		POUR CHAQUE tableliee de ordre_delete
	*			PARCOUCIR matrice_donnees_bdd [tableliee]
	*				SI ACTION = ‘D’  (cas delete)
	*					EFFECTUER REQUETE DE DELETE
	*				FIN SI
	*			FIN PARCOURIR
	*		FIN POUR
	*FIN
	*</pre>
	*/
	function maj_bdd(){
		
		$ID_TRACE = $this->get_cle_max_id_trace()+1;//Recuperation max id_trace + 1 pour obtenir le nouvel id_trace
		
		//////////  TRAITEMENT DES DELETE
		$ordre_delete = $this->ordre_delete_tables();
		//echo'ordre_delete<pre>';
		//print_r($ordre_delete);
		foreach($ordre_delete as $nomtableliee){
			 $matr = $this->matrice_donnees_bdd[$nomtableliee];
			 $tab_champ = $this->champs[$nomtableliee];		
			 $nbcle	=	$this->nb_cles[$nomtableliee];
			 $nomtable = $nomtableliee;
			
			 if (is_array($matr)){
				$nb_col =count($matr[0]);
				$id_action = 0;
				foreach ($matr as $tab){
					$action = $tab[$nb_col-1];
					$id_action++;
					/*
					 * Suppression dans une table
					 */
					if ($action =='D'){
						$sql = "DELETE FROM $nomtable WHERE";
						//$sql .= " $tab_champ[0] = $tab[0]";        	
						for ($i=0 ; $i < $nbcle ; $i++){
							if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[$i]])=='int'){
								$sql 	.= "AND  $tab_champ[$i] = $tab[$i] ";
							}else{
								$sql 	.= "AND  $tab_champ[$i] = ".$this->conn->qstr($tab[$i])." ";
							}
						}
						$sql = str_replace('WHEREAND', 'WHERE', $sql);
						//echo '<BR> ---DELETE---<BR>'.$sql;
						if ($this->conn->Execute($sql) === false){
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
		/////////   FIN DE TRAITEMENT DES DELETE
		
		//////////  TRAITEMENT DES INSERT
		$ordre_insert = $this->ordre_insert_tables();
		//echo'ordre_insert<pre>';
		//print_r($ordre_insert);
		foreach($ordre_insert as $nomtableliee){
			 $matr = $this->matrice_donnees_bdd[$nomtableliee];
			 $tab_champ = $this->champs[$nomtableliee];		
			 $nbcle	=	$this->nb_cles[$nomtableliee];
			 $nomtable = $nomtableliee;
			
			 if (is_array($matr)){
				$nb_col =count($matr[0]);
				$id_action = 0;
				foreach ($matr as $tab){
				/*
				* Ajout dans une table
				*/
					//foreach ($tab as $elt){}
					$action = $tab[$nb_col-1];
					$id_action++;
					if ($action =='I'){
						$sql_champs='';
						$sql_vals='';
						$sql = '';
						
						foreach($tab as $col=>$elt){
							if (($elt or $elt=='0' ) and $elt !='I'){
								$sql_champs .= ", $tab_champ[$col]";
                                if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[$col]])=='int'){
								    $sql_vals 	.= ", $elt";
                                }else{
                                    $sql_vals 	.= ", ".$this->conn->qstr($elt);
                                }
							}
						}
						$sql.= 'INSERT INTO '.$nomtable.' ('.$sql_champs.') VALUES ('.$sql_vals.')';
						$sql = str_replace('(,','(',$sql);	
						//echo '<BR> ---INSERT--- <BR>'.$sql.'<BR>'; 
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
		/////////   FIN DE TRAITEMENT DES INSERT
	
		//////////  TRAITEMENT DES UPDATE
		foreach($this->nomtableliee as $nomtableliee){
			 $matr = $this->matrice_donnees_bdd[$nomtableliee];
			 $tab_champ = $this->champs[$nomtableliee];		
			 $nbcle	=	$this->nb_cles[$nomtableliee];
			 $nomtable = $nomtableliee;
			 
			 //echo '<br>$nbcle='.$nbcle;
			 //echo'<br>$this->tableau_type_zone_base[$nomtableliee]<pre>';
			 //print_r($this->tableau_type_zone_base[$nomtableliee]);

			  //echo"<br>NB CHAMPS =".count($tab_champ)."<br>";
			 if (is_array($matr)){
				$nb_col =count($matr[0]);
				$id_action = 0;
				foreach ($matr as $tab){
					$action = $tab[$nb_col-1];
					$id_action++;
					/*
					 * Mise à jour d'une table
					 */
					 if ($action == 'U'){
							$sql="UPDATE $nomtable SET ";			
							$pass = 0 ;
							for ($i=$nbcle ; $i < $nb_col-1; $i++){
															$virg = ',';
									if($pass==0){
											$virg = '';
									}
		
									if($tab[$i] or $tab[$i]=='0'){
											if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[$i]])=='int'){
													$sql .= " $virg  $tab_champ[$i] = $tab[$i]";
											}else{
													$sql .= " $virg  $tab_champ[$i] = ".$this->conn->qstr($tab[$i]);
											}
									}
									else{
										if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'.*$/',$tab_champ[$i])){
												$sql .= " $virg  $tab_champ[$i] = 255";
										}
										else{
												$sql .= " $virg  $tab_champ[$i] = NULL";
										}
									}
									$pass++;
							}
						
              if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[0]])=='int'){
						    	$sql .= " WHERE $tab_champ[0] = $tab[0]";
							}else{
									$sql .= " WHERE $tab_champ[0] = ".$this->conn->qstr($tab[0]);
							}                        
						for ($i=1 ; $i < $nbcle ; $i++){
								if (trim($this->tableau_type_zone_base[$nomtableliee][$tab_champ[$i]])=='int'){
										$sql .= " AND  $tab_champ[$i] = $tab[$i]"; 
								}else{
										$sql .= " AND  $tab_champ[$i] = ".$this->conn->qstr($tab[$i]); 
								}
						}
						//echo '<BR> ---UPDATE--- <BR>'.$sql.'<BR>';            	
						if ($this->conn->Execute($sql) === false){
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
		/////////   FIN DE TRAITEMENT DES UPDATE
	}
}
?>
