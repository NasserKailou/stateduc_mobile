<?php
/** 
    * Classe Menu : Genère un menu dynamique personnalisé 
	* avec les paramètres de latable DICO_MENU.
    * Elle gére les différents types Menus : 
	* Menu Principal, Menu Saisie, Menu Report
    * @access public
*/	

class Menu {
	
	/**
	 * Attribut : db
	 * Connexion à la Base
	 * @var array
	 * @access public
	 */
    public $db;
	
	/**
	 * Attribut : dico_menu_principal
	 * Récupère les éléments concernent le Menu Principal
	 * @var array
	 * @access public
	 */
    public $dico_menu_principal;
	
	/**
	 * Attribut : dico_menu_saisie
	 * Récupère les éléments concernent le Menu Saisie
	 * @var array
	 * @access public
	 */
    public $dico_menu_saisie;
	
	/**
	 * Attribut : dico_menu_report
	 * Récupère les éléments concernent le Menu Reporting
	 * @var array
	 * @access public
	 */
	public $dico_menu_report;
	
	/**
	 * Attribut : dico_menu_cubes
	 * Récupère les éléments concernent le Menu Reporting Cubes
	 * @var array
	 * @access public
	 */
	public $dico_menu_cubes;
	
	/**
	 * Attribut : header
	 * Partie JS Précédent l'appel des items de Menu
	 * @var string
	 * @access public
	 */
    public $header;
	
	/**
	 * Attribut : footer
	 * Partie JS suivant l'appel des items de Menu
	 * @var string
	 * @access public
	 */
    public $footer;
    
	/**
	 * METHODE :  __construct()
 	 * Constructeur de la classe :
	 * @access public
	 */ 
    function __construct(){ 
    	$this->db 	= $GLOBALS['conn'];
        $this->theme_manager 	= $GLOBALS['theme_manager'];
    }


	/**
	* METHODE :  init_head_foot() :
	* 
	* Prépare les codes JS nécessaires au début et à la suite 
	* de la constitution des items de Menu. 
	* @access public
	* 
	*/
        function init_head_foot() {
         $this->header = '//Menu object creation
            oCMenu=new makeCM("oCMenu") //Making the menu object. Argument: menuname
            oCMenu.frames = 0
            //Menu properties   
            oCMenu.pxBetween =0
            oCMenu.fromTop=6
            oCMenu.rows=1 
            oCMenu.offlineRoot="" 
            oCMenu.onlineRoot="" 
            oCMenu.resizeCheck=1
            oCMenu.wait=300 
            oCMenu.fillImg="image/cm_fill.gif"
            oCMenu.zIndex=400
            oCMenu.menuPlacement=0  // permet de contrôler (ou bloquer) la position à gauche pour éviter
                                    // dans notre cas, que le menu ne se superpose pas au logo
            
            //Background bar properties
            oCMenu.useBar=0         // 1 affiche la barre de menu, 0 la cache
            oCMenu.barWidth="100%"
            oCMenu.barHeight=50     // modifie la hauteur de la barre de menu
            oCMenu.barClass="clBar" // classe css pour la barre
            oCMenu.barX=0 
            oCMenu.barY=50
            oCMenu.barBorderX=0
            oCMenu.barBorderY=0
            oCMenu.barBorderClass=""
                                                    
            // Paramètres de cm_makeLevel(width, height, regClass, overClass, borderX, borderY, borderClass, rows, align, offsetX, offsetY, arrow, arrowWidth, arrowHeight, roundBorder)
            oCMenu.level[0]=new cm_makeLevel(90,21,"clT","clTover",1,1,"clB",0,"bottom",0,0,0,0,0);
            oCMenu.level[2]=new cm_makeLevel(110,22,"clS2","clS2over");
            oCMenu.level[3]=new cm_makeLevel(140,22);';
					
            // contient le pied de page des fichiers menu'
            $this->footer = '//Leave this line - it constructs the menu
            oCMenu.construct();';
        } // Fin rempli_head_foot





//---------------------------------------------------------------------// 
    	/**
         * Trie les elements pere d'un tableau (dico) selon l'ordre de
         * précedence
         * 
         * @param array $dico
         * @return array $dico
         */   
/*
	 function tri_pere(&$dico) {

        $cpt = 0;
		while  ( $cpt < count($dico) )
    	{    		
    		// trier les peres
    		if ( !$dico[$cpt][PERE] )// element ayant pas de pere
    		{    		
    			$cpt2 = 0;
    			while ( ($cpt2 < count($dico)) )
    			{	
    				$entrer = false; 
	    			if ( $dico[$cpt2][PRECEDENT]=== $dico[$cpt][ID] )
	    			{
	    				$entrer = true;
	    				break;
	    			}
    				$cpt2++;	
    			}    			
    			if ( ($cpt > $cpt2) && $entrer )
    			{	//permuter les valeurs
    				$this->permuter($dico[$cpt], $dico[$cpt2]);

    				$cpt = 0; // on recommence au début du dico    				
    			}
    			else $cpt++;
    		}
    		else $cpt++;    		
    	}
    	
    	// remonte les père en haut du tableau
    	$cpt=0;
    	while  ( $cpt < count($dico) )
    	{
    	$cpt2 = 0;
			while ( ($cpt2 < count($dico)) )
			{	
				//remonter les pere en haut du tableau
				if ( $dico[$cpt2][PERE] ) //si c'est un fils
					if ($cpt > $cpt2) // si il en bas 
						$this->permuter($dico[$cpt], $dico[$cpt2]);
				$cpt2++;
			}
			$cpt++;
    	}
    	return $dico;
  	}
*/

//---------------------------------------------------------------------//
    	/**
         * Trie les elements fils d'un tableau (dico) selon l'ordre de
         * précedence
         * 
         * @param array $dico
         * @return array $dico
         */
/*
  	function tri_fils(&$dico) {

    	$cpt = 0;
    	while  ( $cpt < count($dico) )
    	{
    		if ( $dico[$cpt][PERE] )
    		{ // element ayant un pere
    			$cpt2 = 0;    			
    			while ( ($cpt2 < count($dico)) )
    			{ 	  
    				$entrer = false;  	
    					
		    			if ( ( $dico[$cpt][ID] === $dico[$cpt2][PRECEDENT] ) || ( $dico[$cpt][ID] === $dico[$cpt2][PERE] ))
		    			{
		    				$entrer = true;
		    				break;
		    			}
	    				$cpt2++;	
    			}    			
    			if ( ($cpt > $cpt2) && $entrer )
    			{     				   				
    				$this->permuter($dico[$cpt], $dico[$cpt2]);

    				$cpt = 0; // on recommence au début du dico    				
    			}
    			else $cpt++;
    		}
    		else $cpt++;  
    	}// fin while
    	return $dico;
  	}
  	
*/
//---------------------------------------------------------------------// 	
 
    	/**
         * Trie les elements d'un tableau (dico) selon l'ordre de précedence
         * 
         * @param array $dico
         * @return array $dico
         */   
/*
    function tri_tab(&$dico) {

      
        //$this->affiche_dico ($dico); echo '<br>';        
      
		$this->tri_pere($dico);
		$this->tri_fils($dico);  
        
    	//$this->affiche_dico ($dico);
    	return $dico;
    }
*/
    
    
    
//---------------------------------------------------------------------//
    /**
	 * Charge les informations du dico relative aux themes des questionnaires
	 * (table: DICO_THEME)
	 * Ces infos sont triées avec tri_tab()
	 * 
	 */ 
/*
    function charger_theme($langue, $secteur){

        // Lecture des infos du theme dans les tables DICO_THEME_SYSTEME et DICO_TRADUCTION

		$requete = "SELECT D_T.ID_THEME_SYSTEME AS ID, D_T.ID AS ID_MENU, D_T.PERE, D_T.PRECEDENT, D_T.TAILLE_MENU, D_TRAD.LIBELLE
                            FROM DICO_THEME_SYSTEME AS D_T, DICO_TRADUCTION AS D_TRAD
                            WHERE D_T.ID_THEME_SYSTEME = D_TRAD.CODE_NOMENCLATURE
                            And D_TRAD.NOM_TABLE='DICO_THEME_LIB_MENU'
                            And D_T.ID_SYSTEME=".$secteur."
                            And D_TRAD.CODE_LANGUE='".$langue."';";
                print $requete;
		// récupération du résultat dans un tableau
		$t_result_theme	= $this->db->GetAll($requete);
		// tri de la table selon les precedences
		$t_result_theme = $this->tri_tab($t_result_theme);
        print '<BR><BR>';
        foreach ($t_result_theme as $k => $v){
            echo  '<B>'.$k ; 
            print ': </B>';
            print_r($v);
            print '</B><BR>';
        }

        $this->dico_theme = $t_result_theme;
    }
*/
    
    
//---------------------------------------------------------------------//    
	/**
	* METHODE :  charger_menu_saisie() :
	* 
	* Charge le Menu correspondant à l'espace Saisie 
	* en fonction de la lanague transmise en paramètre. 
	* @access public
	* 
	*/
	function charger_menu_saisie($langue){
    /**
	 * Charge les informations du dico relative au menu principal (table:
	 * DICO_MENU, type_menu=2) Ces infos sont triées avec tri_tab()
	 *
	 */ 
        $requete = "SELECT DISTINCT D_TRAD.LIBELLE, D_M.ID AS ID_THEME_SYSTEME, D_M.PERE, D_M.PRECEDENT, 
                    D_M.ACTION_MENU, D_M.TAILLE
                    FROM DICO_MENU AS D_M, DICO_TRADUCTION AS D_TRAD
                    WHERE D_M.ID = D_TRAD.CODE_NOMENCLATURE
                    And D_M.TYPE_MENU = 2
                    And D_TRAD.CODE_LANGUE='".$langue."' And D_TRAD.NOM_TABLE='DICO_MENU';";
            
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						// récupération du résultat dans un tableau
        		$t_result_menu	= $GLOBALS['conn_dico']->GetAll($requete);
				//var_dump($t_result_menu);exit;
						if(!is_array($t_result_menu)){                    
								throw new Exception('ERR_SQL');  
						} 
						// tri de la table selon les precedences
						$t_result_menu = $this->theme_manager->tri_list($t_result_menu, 0);
		
						$this->dico_menu_saisie = $t_result_menu;
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow				
    }
    
    
//---------------------------------------------------------------------//    
	/**
	* METHODE :  charger_menu_principal() :
	* 
	* Charge les éléments du Menu Principal  
	* en fonction de la lanague transmise en paramètre. 
	* @access public
	* 
	*/
	function charger_menu_principal($langue){
		/**
		 * Charge les informations du dico relative au menu principal (table:
		 * DICO_MENU, type_menu=1) Ces infos sont triées avec tri_tab()
		 *
		 */ 
        //Sélection de tous les menus (tous ceux accessibles à 'admin')
        //Cela permet de conserver les précédences en menus
        //On filtrera plus loin avec ADMIN_DROITS
        $requete = "SELECT DISTINCT D_TRAD.LIBELLE, D_TRAD.NOM_TABLE, D_TRAD.CODE_LANGUE, D_M.ID AS ID_THEME_SYSTEME, D_M.PERE, D_M.PRECEDENT, 
                    D_M.ACTION_MENU, D_M.TAILLE,
                    A_D.CODE_GROUPE
                    FROM DICO_MENU AS D_M, DICO_TRADUCTION AS D_TRAD, ADMIN_DROITS AS A_D
                    WHERE D_M.ID = D_TRAD.CODE_NOMENCLATURE
                    And D_M.TYPE_MENU = 1
                    And A_D.ID_MENU = D_M.ID And A_D.CODE_GROUPE = 1
                    And D_TRAD.CODE_LANGUE='".$langue."' And D_TRAD.NOM_TABLE='DICO_MENU';";
			//print $requete.'<br>';

			// Traitement Erreur Cas : GetAll / GetRow
			try {
					$t_result_menu	= $GLOBALS['conn_dico']->GetAll($requete);
					if(!is_array($t_result_menu)){                    
							throw new Exception('ERR_SQL');  
					} 
					// tri de la table selon les precedences
					$t_result_menu = $this->theme_manager->tri_list($t_result_menu, 0);
	
					$this->dico_menu_principal = $t_result_menu;
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}
			// Fin Traitement Erreur Cas : GetAll / GetRow
    }
	
	/**
	* METHODE :  charger_menu_report() :
	* 
	* Charge les éléments constituant le Menu du reporting
	* en fonction de la lanague transmise en paramètre. 
	* @access public
	* 
	*/
	function charger_menu_report($langue){
    /**
	 * Charge les informations du dico relative au menu principal (table:
	 * DICO_MENU, type_menu=2) Ces infos sont triées avec tri_tab()
	 *
	 */

        $requete = "SELECT DISTINCT D_TRAD.LIBELLE, D_M.ID AS ID_THEME_SYSTEME, D_M.PERE, D_M.PRECEDENT, 
                    D_M.ACTION_MENU, D_M.TAILLE
                    FROM DICO_MENU AS D_M, DICO_TRADUCTION AS D_TRAD
                    WHERE D_M.ID = D_TRAD.CODE_NOMENCLATURE
                    AND D_M.TYPE_MENU = 3
                    AND D_TRAD.CODE_LANGUE='".$langue."' AND D_TRAD.NOM_TABLE='DICO_MENU';";
               
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						// récupération du résultat dans un tableau
        		$t_result_menu	= $GLOBALS['conn_dico']->GetAll($requete); 
						if(!is_array($t_result_menu)){                    
								throw new Exception('ERR_SQL');  
						} 
						// tri de la table selon les precedences
						$t_result_menu = $this->theme_manager->tri_list($t_result_menu, 0);
		
						$this->dico_menu_report = $t_result_menu;
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow				
    }
   
	/**
	* METHODE :  charger_menu_cubes() :
	* 
	* Charge les éléments constituant le Menu du reporting baser sur les cubes
	* en fonction de la lanague transmise en paramètre. 
	* @access public
	* 
	*/
	function charger_menu_cubes($langue){
    /**
	 * Charge les informations du dico relative au menu cubes (table:
	 * DICO_MENU, type_menu=3) Ces infos sont triées avec tri_tab()
	 *
	 */

        $requete = "SELECT DISTINCT D_TRAD.LIBELLE, D_M.ID AS ID_THEME_SYSTEME, D_M.PERE, D_M.PRECEDENT, 
                    D_M.ACTION_MENU, D_M.TAILLE, D_M.TYPE_OLAP_SOURCE, D_M.SERVER_OLAP, D_M.BASE_OLAP
                    FROM DICO_MENU AS D_M, DICO_TRADUCTION AS D_TRAD
                    WHERE D_M.ID = D_TRAD.CODE_NOMENCLATURE
                    AND D_M.TYPE_MENU = 4
                    AND D_TRAD.CODE_LANGUE='".$langue."' AND D_TRAD.NOM_TABLE='DICO_MENU';";
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						// récupération du résultat dans un tableau
        		$t_result_menu	= $GLOBALS['conn_dico']->GetAll($requete); 
						if(!is_array($t_result_menu)){                    
								throw new Exception('ERR_SQL');  
						} 
						// tri de la table selon les precedences
						$t_result_menu = $this->theme_manager->tri_list($t_result_menu, 0);
		
						$this->dico_menu_cubes = $t_result_menu;
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow				
    }

//---------------------------------------------------------------------//
	/**
	* METHODE :  cree_fichier_menu_principal() :
	* 
	* Crée le fichier JS associé au menu Principal en fonction 
	* d'un groupe donné et de la langue.
	* @access public
	* 
	*/
	function cree_fichier_menu_principal ($groupe, $langue){
    	/**
         * Creer les fichiers _menu_princ.js  permettant
         * d'afficher les menus structure methode makemenu du fichier menu.js:
         * (name,parent,txt,lnk,targ,w,h,img1,img2,cl,cl2,align,rows,nolink,
         * onclick,onmouseover,onmouseout)
         * 
         */
         
         
         print '<BR><H1>';
         print $groupe.'/'.$langue.'<BR></H1>';
         //lecture des droits (DICO_MENU <--> ADMIN_GROUPES) du groupe en cours
        /*$requete                = "SELECT ID_MENU
                                    FROM ADMIN_DROITS
                                    WHERE  CODE_GROUPE = '".$groupe."';";*/
                                    
        $requete                = "SELECT ID_MENU
                                    FROM ADMIN_DROITS
                                    WHERE  CODE_GROUPE = ".$groupe.";";        
        $tab_droits_menu = array();
        $tab_droits_menu	= $GLOBALS['conn_dico']->GetAll($requete);         
        
        $droits_menu            = array();
        foreach ($tab_droits_menu as $element){
            //echo 'elemeent<br> <pre>';
           //print_r($element);
            
            array_push($droits_menu, $element['ID_MENU']);
        }

         // TODO: oCMenu.fromLeft reste à calculer (comme pour menu principal)
         //       lorsque le menu saisie sera totalement dynamique
         // Initialise l'entete des fichiers menu.js avec les éléments communs
         $this->init_head_foot();


            //nom du fichier menu principal
            $file_princ = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/'.$langue.'/_menu_princ.js';
            
            print '<BR><i><b>Menu_principal:<BR></b></i>';
            print '<BR><i><b>'.$file_princ.'<BR></b></i>';

            //definir la variable $data (donnée de dico_menu à mettre dans le menu principal)
            $taille =  0;
            $taille_barre_menu =  0;
            $taille_menu_principal = count($this->dico_menu_principal);
            for ($cpt = 0; $cpt < $taille_menu_principal ; $cpt++){	
                if (in_array($this->dico_menu_principal[$cpt]['ID_THEME_SYSTEME'], $droits_menu)){
                    $nom_p 				= $this->dico_menu_principal[$cpt]['ID_THEME_SYSTEME'];
                    $pere_p 			= $this->dico_menu_principal[$cpt]['PERE'];					
                    $libelle_p 		    = $this->dico_menu_principal[$cpt]['LIBELLE'];
                    $taille             = $this->dico_menu_principal[$cpt]['TAILLE'];
                    $link_p 			= $this->dico_menu_principal[$cpt]['ACTION_MENU'];
                    $data_p 			.= "oCMenu.makeMenu('".$nom_p."','".$pere_p."','".$libelle_p."','".$link_p."','','".$taille."');\n";
                    print $this->dico_menu_principal[$cpt]['LIBELLE'];
                    // Calcul de la longueur de la barre de menu (pour la centrer)
                    if ((!$this->dico_menu_principal[$cpt]['PERE']) or ($this->dico_menu_principal[$cpt]['PERE'] == 0)){
                        $taille_barre_menu = $taille_barre_menu + $this->dico_menu_principal[$cpt]['TAILLE'];
                        print 'principal';
                    }
                }
                print '<BR>';
            }
            print 'taille_totale-->'.$taille_barre_menu.'<BR>';
            // Calcul du 'fromleft' à partir de la longueur de la barre de menu 
            $from_left = (1024 - $taille_barre_menu)/2;
            print 'from_left-->'.$from_left.'<BR>';

            // Pour le menu principal (appelé à partir de accueil.php) 
            // Le "fromLeft" est différent
            // et la flèche des sous-menus se trouve sur le répertoire "image" au même niveau
            $this->header         .= '
            oCMenu.fromLeft='.$from_left.'
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"' . $GLOBALS['SISED_URL_IMG'] . 'menu_arrow.php",10,10);';
                
            $contenu_p       = $this->header;
            $contenu_p      .= "\n";
            $contenu_p      .= $data_p;
            $contenu_p      .= "\n";
            $contenu_p      .=  $this->footer;

            // création du Répertoire
            $rep_princ = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/';
            if (!file_exists($rep_princ)){ // Création du répertoire
                if ( !mkdir($rep_princ) ){
                    exit('Le répertoire '.$rep_princ.' n\'a pu être créé.');			
                } else {
                    chmod($rep_princ, 0770);
                }
            }
            $rep_princ = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/'.$langue.'/';
            if (!file_exists($rep_princ)){ // Création du répertoire
                if ( !mkdir($rep_princ) ){
                        exit('Le répertoire '.$rep_princ.' n\'a pu être créé.');			
                } else {
                    chmod($rep_princ, 0770);
                }
            }
            // création du fichier _menu_princ.js
            if ( !file_put_contents($rep_princ.'_menu_princ.js', $contenu_p) ){
                    exit('Le fichier '.$rep_princ.'_menu_princ.js n\'a pu être créé.');			
            }
  }// Fin cree_fichier_menu_principal
  
  

//---------------------------------------------------------------------//
	/**
	* METHODE :  cree_fichier_menu_saisie() :
	* 
	* Crée le fichier JS associé au menu de saisie en fonction 
	* d'un groupe donné, d'un secteur et de la langue.
	* @access public
	* 
	*/
	function cree_fichier_menu_saisie ($groupe, $langue, $secteur, $appart=""){
    	/**
         * Creer les fichiers _menu_princ.js et _menu_saisie.js, permettant
         * d'afficher les menus structure methode makemenu du fichier menu.js:
         * (name,parent,txt,lnk,targ,w,h,img1,img2,cl,cl2,align,rows,nolink,
         * onclick,onmouseover,onmouseout)
         * 
         */
         
        print '<BR><H1>';
        if($appart <> "")
		 	print $groupe.'/'.$langue.'/'.$secteur.'/'.$appart.'<BR></H1>';
		else
			print $groupe.'/'.$langue.'/'.$secteur.'<BR></H1>';
         // TODO: oCMenu.fromLeft reste à calculer (comme pour menu principal)
         //       lorsque le menu saisie sera totalement dynamique
         // Initialise l'entete des fichiers menu.js avec les éléments communs
		 $requete                = "SELECT ID_MENU
                                    FROM ADMIN_DROITS
                                    WHERE  CODE_GROUPE = ".$groupe.";";        
        $tab_droits_menu	= $GLOBALS['conn_dico']->GetAll($requete);         
        
        $droits_menu            = array();
        foreach ($tab_droits_menu as $element){
            //echo 'elemeent<br> <pre>';
           //print_r($element);
            
            array_push($droits_menu, $element['ID_MENU']);
        }
		
         $this->init_head_foot();
         $this->header .= 'oCMenu.fromLeft=175;
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"' . $GLOBALS['SISED_URL_IMG'] . 'menu_arrow.php",10,10);';
            
        print '<BR><i><b>Menu_saisie:<BR></b></i>';
        // Création du fichier menu_saisie. 
        $data = '';        
        
        foreach ($this->dico_menu_saisie as $row){
			if ( in_array($row['ID_THEME_SYSTEME'], $droits_menu) ){
            	$data  .= "oCMenu.makeMenu('".$row['ID_THEME_SYSTEME']."','".$row['PERE']."','".$row['LIBELLE']."','".$row['ACTION_MENU']."','','".$row['TAILLE']."');\n";
			}
        }
		$suite_where = '';     	
		if(isset($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && count($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])>0){
			$themes_a_exclure = $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'];
		    $suite_where = ' AND D_T_S.ID_THEME_SYSTEME NOT IN ( '.implode(', ',$themes_a_exclure).' )';
    	}
		// Modificiation Alassane
		if($appart <> "") $critere_appart = " AND  D_T_S.APPARTENANCE=$appart ";
		else $critere_appart = "";
		$suite_where .= $critere_appart;
        $requete = "SELECT D_T_S.ID_THEME_SYSTEME, D_T.ID_TYPE_THEME, D_T_S.ID, D_T_S.PERE, D_T_S.PRECEDENT, D_T_S.TAILLE_MENU, D_TRAD.LIBELLE
                FROM DICO_THEME_SYSTEME AS D_T_S, DICO_TRADUCTION AS D_TRAD, DICO_THEME AS D_T
                WHERE D_T_S.ID_THEME_SYSTEME = D_TRAD.CODE_NOMENCLATURE
                AND D_T.ID = D_T_S.ID
                AND D_TRAD.NOM_TABLE='DICO_THEME_LIB_MENU'
                AND  D_T_S.ID_SYSTEME=".$secteur.$suite_where."
                AND D_TRAD.CODE_LANGUE='".$langue."';";
       
				$result_theme	= $GLOBALS['conn_dico']->GetAll($requete); 

        // tri de la table selon les precedences
        $result_theme   =   $this->theme_manager->tri_list($result_theme); 
				//echo'<pre>';       
				//print_r($result_theme);

        $taille_max_quest = 0;
        // calcul de la taille max du libellé pour affichage pour dico_theme
        for ($cpt = 0; $cpt < count($result_theme); $cpt++)
        {
                if ( $taille_max_quest < $result_theme[$cpt]['TAILLE_MENU'] )
                        $taille_max_quest = $result_theme[$cpt]['TAILLE_MENU'];
        }
       

        //definir la variable $data (donnée de dico_menu a mettre dans le menu)
        for ($cpt = 0; $cpt < count($result_theme); $cpt++)
        {	// application de la taille max	
                $nom = $result_theme[$cpt]['ID_THEME_SYSTEME'];
                $pere = $result_theme[$cpt]['PERE'];
                $libelle = $result_theme[$cpt]['LIBELLE'];
                print $libelle.'<BR>';
                if ($result_theme[$cpt]['ID_TYPE_THEME'] == 8){//Cas des thème de type "menu"
                    $link = '#';
                } else  {
                            $link = 'questionnaire.php?theme=';
                            $link .= $result_theme[$cpt]['ID_THEME_SYSTEME'];
							if($appart <> ""){
								$link .= '&type_ent_stat=';
								$link .= $appart;
							}
                        }
                $data .= "oCMenu.makeMenu('".$nom."','".$pere."','".$libelle."','".$link."','','".$taille_max_quest."');\n";
        }
        $contenu = $this->header;
        $contenu .= "\n";
        $contenu .= $data;
        $contenu .= "\n";
        $contenu .=  $this->footer;
         //Fin mofication Alassane

        // création du Répertoire
        if($appart <> "")
			$rep_saisie = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/'.$langue.'/'.$secteur.'/'.$appart.'/';
		else
			$rep_saisie = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/'.$langue.'/'.$secteur.'/';
        print '<BR>'.$rep_saisie.'<BR>';
        
		$rep_saisie1 = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/'.$langue.'/'.$secteur.'/';
		if (!file_exists($rep_saisie1)){ // Création du répertoire
            if ( !mkdir($rep_saisie1) ){
                    exit('Le fichier '.$rep_saisie1.' n\'a pu être créé.');			
            } else {
                chmod($rep_saisie1, 0770); // pour LINUX
            }
        }
		if($appart <> ""){
			$rep_saisie2 = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/'.$langue.'/'.$secteur.'/'.$appart.'/';
			if (!file_exists($rep_saisie2)){ // Création du répertoire
				if ( !mkdir($rep_saisie2) ){
						exit('Le fichier '.$rep_saisie2.' n\'a pu être créé.');			
				} else {
					chmod($rep_saisie2, 0770); // pour LINUX
				}
			}
		}
        // création du fichier _menu_saisie.js
        if ( !file_put_contents($rep_saisie.'_menu_saisie.js', $contenu) ){
                exit('Le fichier '.$rep_saisie.'_menu_saisie.js n\'a pu être créé.');			
        }
        
  }// Fin cree_fichier_menu_saisie
//---------------------------------------------------------------------//
	
	/**
	* METHODE :  get_childs_rpt() :
	* 
	* Récupère les éléments fils d'un élément donné du Menu
	* @access public
	* 
	*/
	function get_childs_rpt($id_rpt_syst){
	
		$req_rpt_fi = ' SELECT  	DICO_REPORT_SYSTEME.ID, DICO_REPORT.ID_TYPE_REPORT, DICO_REPORT_SYSTEME.TITRE_ETAT_MENU
						FROM 		DICO_REPORT, DICO_REPORT_SYSTEME
						WHERE 		DICO_REPORT_SYSTEME.ACTIVER=1
						AND			DICO_REPORT.ID = DICO_REPORT_SYSTEME.ID
						AND 		DICO_REPORT_SYSTEME.PERE = '.$id_rpt_syst.'
						ORDER BY 	DICO_REPORT_SYSTEME.ORDRE_AFFICHAGE_MENU ;';
		return($GLOBALS['conn_dico']->GetAll($req_rpt_fi)); 
	}
	
	/**
	* METHODE :  cree_fichier_menu_report() :
	* 
	* Crée le fichier JS associé au menu du Reporting  en fonction 
	* d'un groupe donné, d'un secteur et de la langue.
	* @access public
	* 
	*/
	function cree_fichier_menu_report ($groupe, $langue, $secteur, $appart=""){
    	/**
         * Creer les fichiers _menu_princ.js et _menu_saisie.js, permettant
         * d'afficher les menus structure methode makemenu du fichier menu.js:
         * (name,parent,txt,lnk,targ,w,h,img1,img2,cl,cl2,align,rows,nolink,
         * onclick,onmouseover,onmouseout)
         * 
         */
         print '<BR><H1>';
         if($appart <> "")	 print $groupe.'/'.$langue.'/'.$secteur.'/'.$appart.'<BR></H1>';
		 else print $groupe.'/'.$langue.'/'.$secteur.'<BR></H1>';
		 print '<BR><i><b>Menu_Report:<BR></b></i>';

         // TODO: oCMenu.fromLeft reste à calculer (comme pour menu principal)
         //       lorsque le menu saisie sera totalement dynamique
         // Initialise l'entete des fichiers menu.js avec les éléments communs
		  
		 $requete                = "SELECT ID_MENU
                                    FROM ADMIN_DROITS
                                    WHERE  CODE_GROUPE = ".$groupe.";";        
        $tab_droits_menu 	= array();
        $tab_droits_menu	= $GLOBALS['conn_dico']->GetAll($requete);         
        
        $droits_menu            = array();
        foreach ($tab_droits_menu as $element){
            //echo 'elemeent<br> <pre>';
           //print_r($element);
            array_push($droits_menu, $element['ID_MENU']);
        }

            $taille =  0;
            $taille_barre_menu =  0;
            $taille_menu_report = count($this->dico_menu_report);
			$data       = '';
            for ($cpt = 0; $cpt < $taille_menu_report ; $cpt++){	
                if (in_array($this->dico_menu_report[$cpt]['ID_THEME_SYSTEME'], $droits_menu)){
                    $nom_p 				= $this->dico_menu_report[$cpt]['ID_THEME_SYSTEME'];
                    $pere_p 			= $this->dico_menu_report[$cpt]['PERE'];					
                    $libelle_p 		    = $this->dico_menu_report[$cpt]['LIBELLE'];
                    $taille             = $this->dico_menu_report[$cpt]['TAILLE'];
                    $link_p 			= $this->dico_menu_report[$cpt]['ACTION_MENU'];
                    $data	 			.= "oCMenu.makeMenu('".$nom_p."','".$pere_p."','".$libelle_p."','".$link_p."','','".$taille."');\n";
                    print $this->dico_menu_report[$cpt]['LIBELLE'];
                    // Calcul de la longueur de la barre de menu (pour la centrer)
                    if ((!$this->dico_menu_report[$cpt]['PERE']) or ($this->dico_menu_report[$cpt]['PERE'] == 0)){
                        $taille_barre_menu = $taille_barre_menu + $this->dico_menu_report[$cpt]['TAILLE'];
                        //print 'principal';
                    }
                }
                print '<BR>';
            }
            print 'taille_totale-->'.$taille_barre_menu.'<BR>';
            // Calcul du 'fromleft' à partir de la longueur de la barre de menu 
            $from_left = (1024 - $taille_barre_menu)/2;
			print 'from_left Report -->'.$from_left.'<BR>';
			
		 $this->header = '' ;	

         $this->init_head_foot();
         $this->header .= 'oCMenu.fromLeft='.$from_left.';
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"' . $GLOBALS['SISED_URL_IMG'] . 'menu_arrow.php",10,10);';
            
        // Modificiation Alassane
        $req_rpt_Pe = ' SELECT  	DICO_REPORT_SYSTEME.ID, DICO_REPORT.ID_TYPE_REPORT, DICO_REPORT_SYSTEME.TITRE_ETAT_MENU
						FROM 		DICO_REPORT, DICO_REPORT_SYSTEME
						WHERE 		DICO_REPORT_SYSTEME.ACTIVER=1
						AND			DICO_REPORT.ID = DICO_REPORT_SYSTEME.ID
						AND 		DICO_REPORT_SYSTEME.ID_SYSTEME ='.$secteur.'
						AND 		DICO_REPORT_SYSTEME.PERE = 0
						ORDER BY 	DICO_REPORT_SYSTEME.ORDRE_AFFICHAGE_MENU ;';
		$list_rpt_Pe	= $GLOBALS['conn_dico']->GetAll($req_rpt_Pe); 
		// tri de la table selon les precedences
        //$result_theme   =   $this->theme_manager->tri_list($result_theme); 
				//echo'<pre>';       
				//print_r($result_theme);

        $taille_max_quest = 270;
        // calcul de la taille max du libellé pour affichage pour dico_theme
       
        //definir la variable $data (donnée de dico_menu a mettre dans le menu)
		$pere_rpts = '30030' ;

        //definir la variable $data (donnée de dico_menu a mettre dans le menu)
        for ($cpt = 0; $cpt < count((array)$list_rpt_Pe); $cpt++){	// application de la taille max	
			$nom_pere = $list_rpt_Pe[$cpt]['ID'].$secteur;
			$tab_childs_rpt = $this->get_childs_rpt($nom_pere);
			$libelle = $list_rpt_Pe[$cpt]['TITRE_ETAT_MENU'];
			print $libelle.'<BR>';
			if( is_array($tab_childs_rpt) and (count($tab_childs_rpt) <> 0) ){
				$link = '#';
				if($list_rpt_Pe[$cpt]['ID_TYPE_REPORT'] == 4 || $list_rpt_Pe[$cpt]['ID_TYPE_REPORT'] == 5)
					$link = 'synthese.php?val=list_rpt&id_rpt='.$list_rpt_Pe[$cpt]['ID'].'&type_rpt='.$list_rpt_Pe[$cpt]['ID_TYPE_REPORT'].'&id_syst='.$secteur.'';
				$data .= "oCMenu.makeMenu('".$nom_pere."','".$pere_rpts."','".$libelle."','".$link."','','".$taille_max_quest."');\n";
				foreach($tab_childs_rpt as $i_child => $child_rpt){
					$link = 'synthese.php?val=list_rpt&id_rpt='.$child_rpt['ID'].'&type_rpt='.$child_rpt['ID_TYPE_REPORT'].'&id_syst='.$secteur.'';
					$nom_child = $child_rpt['ID'].$secteur ;
					$data .= "oCMenu.makeMenu('".$nom_child."','".$nom_pere."','".$child_rpt['TITRE_ETAT_MENU']."','".$link."','','".$taille_max_quest."');\n";
				}
			}else{
				$link = 'synthese.php?val=list_rpt&id_rpt='.$list_rpt_Pe[$cpt]['ID'].'&type_rpt='.$list_rpt_Pe['ID_TYPE_REPORT'].'&id_syst='.$secteur.'';
				$data .= "oCMenu.makeMenu('".$nom_pere."','".$pere_rpts."','".$libelle."','".$link."','','".$taille_max_quest."');\n";
			}
		}
		
        $contenu  = $this->header;
        $contenu .= "\n";
        $contenu .= $data;
        $contenu .= "\n";
        $contenu .= $this->footer;
         //Fin mofication Alassane

        // création du Répertoire
        if($appart <> "") $rep_saisie = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/'.$langue.'/'.$secteur.'/'.$appart.'/';
		else $rep_saisie = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/'.$langue.'/'.$secteur.'/';
        print '<BR>'.$rep_saisie.'<BR>';
        if (!file_exists($rep_saisie)){ // Création du répertoire
            if ( !mkdir($rep_saisie) ){
				exit('Le fichier '.$rep_saisie.' n\'a pu être créé.');			
            } else {
                chmod($rep_saisie, 0770); // pour LINUX
            }
        }
        // création du fichier _menu_saisie.js
        if ( !file_put_contents($rep_saisie.'_menu_report.js', $contenu) ){
                exit('Le fichier '.$rep_saisie.'_menu_report.js n\'a pu être créé.');			
        }
        
  }// Fin cree_fichier_menu_rpt
  
  /**
	* METHODE :  cree_fichier_menu_cubes() :
	* 
	* Crée le fichier JS associé au menu du Reporting Cubes en fonction 
	* d'un groupe donné, d'un secteur et de la langue.
	* @access public
	* 
	*/
	function cree_fichier_menu_cubes ($groupe, $langue, $secteur){
    	/**
         * Creer les fichiers _menu_princ.js et _menu_saisie.js, permettant
         * d'afficher les menus structure methode makemenu du fichier menu.js:
         * (name,parent,txt,lnk,targ,w,h,img1,img2,cl,cl2,align,rows,nolink,
         * onclick,onmouseover,onmouseout)
         * 
         */
         
         print '<BR><H1>';
         print $groupe.'/'.$langue.'/'.$secteur.'<BR></H1>';
		   
		 print '<BR><i><b>Menu_Cubes:<BR></b></i>';

         // TODO: oCMenu.fromLeft reste à calculer (comme pour menu principal)
         //       lorsque le menu saisie sera totalement dynamique
         // Initialise l'entete des fichiers menu.js avec les éléments communs
		  
		 $requete                = "SELECT ID_MENU
                                    FROM ADMIN_DROITS
                                    WHERE  CODE_GROUPE = ".$groupe.";";        
        $tab_droits_menu 	= array();
        $tab_droits_menu	= $GLOBALS['conn_dico']->GetAll($requete);         
        
        $droits_menu            = array();
        foreach ($tab_droits_menu as $element){
            //echo 'elemeent<br> <pre>';
           //print_r($element);
            array_push($droits_menu, $element['ID_MENU']);
        }

            $taille =  0;
            $taille_barre_menu =  0;
            $taille_menu_report = count($this->dico_menu_cubes);
			$data       = '';
            for ($cpt = 0; $cpt < $taille_menu_report ; $cpt++){	
                if (in_array($this->dico_menu_cubes[$cpt]['ID_THEME_SYSTEME'], $droits_menu)){
                    $nom_p 				= $this->dico_menu_cubes[$cpt]['ID_THEME_SYSTEME'];
                    $pere_p 			= $this->dico_menu_cubes[$cpt]['PERE'];					
                    $libelle_p 		    = $this->dico_menu_cubes[$cpt]['LIBELLE'];
                    $taille             = $this->dico_menu_cubes[$cpt]['TAILLE'];
                    $type_olap_source = $this->dico_menu_cubes[$cpt]['TYPE_OLAP_SOURCE'];
					$server_olap = $this->dico_menu_cubes[$cpt]['SERVER_OLAP'];
					$nom_base = $this->dico_menu_cubes[$cpt]['BASE_OLAP'];
					$nom_cube = $this->dico_menu_cubes[$cpt]['ACTION_MENU'];
					if($this->dico_menu_cubes[$cpt]['ACTION_MENU']<>'' && !preg_match("/.php/",$this->dico_menu_cubes[$cpt]['ACTION_MENU']) && !preg_match("/\?/",$this->dico_menu_cubes[$cpt]['ACTION_MENU'])){
						$link_p 	= 'olap.php?val=pop_OLAP2&type_sgbd='.$type_olap_source.'&server_olap='.$server_olap.'&nom_base='.$nom_base.'&nom_cube='.$nom_cube;
                    }else{
						$link_p 	= $this->dico_menu_cubes[$cpt]['ACTION_MENU'];
					}
					$data	 			.= "oCMenu.makeMenu('".$nom_p."','".$pere_p."','".$libelle_p."','".$link_p."','','".$taille."');\n";
                    print $this->dico_menu_cubes[$cpt]['LIBELLE'];
                    // Calcul de la longueur de la barre de menu (pour la centrer)
                    if ((!$this->dico_menu_cubes[$cpt]['PERE']) or ($this->dico_menu_cubes[$cpt]['PERE'] == 0)){
                        $taille_barre_menu = $taille_barre_menu + $this->dico_menu_cubes[$cpt]['TAILLE'];
                        //print 'principal';
                    }
                }
                print '<BR>';
            }
            print 'taille_totale-->'.$taille_barre_menu.'<BR>';
            // Calcul du 'fromleft' à partir de la longueur de la barre de menu 
            $from_left = (1024 - $taille_barre_menu)/2;
			print 'from_left Report -->'.$from_left.'<BR>';
			
		 $this->header = '' ;	

         $this->init_head_foot();
         $this->header .= 'oCMenu.fromLeft='.$from_left.';
            oCMenu.level[1]=new cm_makeLevel(102,22,"clS","clSover",1,1,"clB",0,"right",0,0,"' . $GLOBALS['SISED_URL_IMG'] . 'menu_arrow.php",10,10);';
            
		//
		$contenu       = $this->header;
		$contenu      .= "\n";
		$contenu      .= $data;
		$contenu      .= "\n";
		$contenu      .=  $this->footer;

		// création du Répertoire
        $rep_saisie = $GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe.'/'.$langue.'/'.$secteur.'/';
        print '<BR>'.$rep_saisie.'<BR>';
        if (!file_exists($rep_saisie)){ // Création du répertoire
            if ( !mkdir($rep_saisie) ){
				exit('Le fichier '.$rep_saisie.' n\'a pu être créé.');			
            } else {
                chmod($rep_saisie, 0770); // pour LINUX
            }
        }
        // création du fichier _menu_saisie.js
        if ( !file_put_contents($rep_saisie.'_menu_cubes.js', $contenu) ){
                exit('Le fichier '.$rep_saisie.'_menu_cubes.js n\'a pu être créé.');			
        }
        //
  }// Fin cree_fichier_menu_cubes
 }
?>