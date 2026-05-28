<?php function manage_magic_quotes(){
		ini_set("magic_quotes_runtime", 0);
		# On n'exécute la boucle que si nécessaire
		if(get_magic_quotes_gpc() == 1){
				
				# Définition de la fonction récursive.
				function remove_magic_quotes(&$array)
				{
					 foreach($array as $key => $val){
				
							 # Si c'est un array, recurssion de la fonction, sinon suppression des slashes
							 if(is_array($val)){
									 remove_magic_quotes($array[$key]);
							 } else if(is_string($val)){
									 $array[$key] = stripslashes($val);
							 }
					 }
				}
				
				# Appel de la fonction pour chaque variables.
				# Notes, vous pouvez enlevez celle d'on vous ne vous servez pas.
				# Personnellement, j'enlève $_REQUEST et $_FILES
				
				remove_magic_quotes($_POST);
				remove_magic_quotes($_GET);
				remove_magic_quotes($_REQUEST);
				remove_magic_quotes($_SERVER);
				remove_magic_quotes($_FILES);
				remove_magic_quotes($_COOKIE);
		}
}
/*******************************/
function create_combo($tableau, $nom_combo, $nom_code, $selected_element, $fonction, $disabled='')
 {
 //TODO: en double avec arbre->create_combo
  // Créé la chaine de texte qui va constituer la liste des options dans le combo   
    
    $chaine_combo='';
	if(trim($fonction) <> ''){
		$onchange = ' onchange="'.$fonction.';"' ;
	}
		$chaine_combo .= '	<SELECT name="'.$nom_combo.'" id="'.$nom_combo.'" '.$onchange.$disabled.'>';  
	  if(is_array($tableau)){
				foreach ($tableau as $row)
				 {
										
				$chaine_combo      .="\n<option value='".$row[$nom_code]."'";
										if ($row[$nom_code] == $selected_element){
												$chaine_combo  .=" selected";
										}
										$chaine_combo      .=">".$row[$nom_combo]."</option>";              
										
				 }
		}
	
    $chaine_combo .= "\n</SELECT>";
    

  return $chaine_combo;
 }

/*
function recherche_libelle($code_libelle){
    foreach ($_SESSION['tab_libelles'] as $key => $row){
        foreach($row as $cell){
            if (strpos($cell, $code_libelle) !== FALSE){
                return $_SESSION['tab_libelles'][$key]['LIBELLE'];
            }
        }
    }
}
*/

	//recherche le nom d'une valeur d'une table nomenclature (utilisé pour les infos établissement)
    function recherche_libelle_nomenclature_ctrl($id_nomenclature, $table_nomenclature){
		if(!in_array($table_nomenclature, array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES')))){
			$arr_tables_nomenc = preg_grep('/'.$table_nomenclature.'/', array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES')));
			$table_nomenclature = current($arr_tables_nomenc);
		}
		$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
                            WHERE CODE_NOMENCLATURE=".$id_nomenclature." 
                            And CODE_LANGUE='".$_SESSION['langue']."'
                            And NOM_TABLE='".$table_nomenclature."';";
        $aresult	= $GLOBALS['conn']->GetAll($requete); 
       //echo "<br>".$requete;
	    //print '<BR>'.$id_zone.'-->'.count($aresult);
        return $aresult[0]['LIBELLE'];
    }

    //recherche le nom d'une valeur d'une table nomenclature (utilisé pour les infos établissement)
    function recherche_libelle_nomenclature($id_nomenclature, $table_nomenclature){
		$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
                            WHERE CODE_NOMENCLATURE=".$id_nomenclature." 
                            And CODE_LANGUE='".$_SESSION['langue']."'
                            And NOM_TABLE='".$table_nomenclature."';";
        $aresult	= $GLOBALS['conn']->GetAll($requete); 
       //echo "<br>".$requete;
	    //print '<BR>'.$id_zone.'-->'.count($aresult);
        return $aresult[0]['LIBELLE'];
    }

    function recherche_libelle_page($code_libelle){
    // Recherche le libellé à afficher à partir du tableau en session qui contient l'ensemble des libellés
        foreach ($_SESSION['tab_libelles'] as $key => $row){
            foreach($row as $cell){
                if ($cell == $code_libelle){
                    return $_SESSION['tab_libelles'][$key]['LIBELLE'];
                }
            }
        }
    }
		
		function quote(&$chaine){
				return(str_replace("'","''",str_replace("''","'",$chaine)));
    }
		

    function lit_libelles_page($page){
        // Recherche des libellés à traduire sur la page 
				$GLOBALS['libelles_page_found'] = true;
        preg_match('`.*/(.*)$`', preg_replace('`\\\`', '/', $page), $reg);
        //$db                     = $GLOBALS['conn'];
        $requete                    = "SELECT LIBELLE, CODE_LIBELLE
                                        FROM DICO_LIBELLE_PAGE 
                                        WHERE CODE_LANGUE='".$_SESSION['langue']."'
                                        And NOM_PAGE='".$reg[1]."';";

        $_SESSION['tab_libelles']   = $GLOBALS['conn_dico']->GetAll($requete);
		if(!is_array($_SESSION['tab_libelles'])){
				$GLOBALS['libelles_page_found'] = false;
		}
        //print $requete;
    }

	// Fonction de login
	
		function valide_user_LogByEtab ($login,$password){
			//Vérification du login dans la table ADMIN_USERS et chargement du groupe utilisateur
			//(pour le choix du menu)
						
			// Modfi Bass - Atelier Somone Senegal
			
			if( (isset($GLOBALS['PARAM']['LOG_BY_SCHOOL_USER']) and trim($GLOBALS['PARAM']['LOG_BY_SCHOOL_USER']) <> '') && (isset($GLOBALS['PARAM']['LOG_BY_SCHOOL_PASS']) and trim($GLOBALS['PARAM']['LOG_BY_SCHOOL_PASS']) <> '') ){
				require_once $GLOBALS['SISED_PATH_LIB'] . 'connexion.inc.php';
				require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
				define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);
			
				$requete        = ' SELECT '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].'
									WHERE '.$GLOBALS['PARAM']['LOG_BY_SCHOOL_USER'].'=\''.$login.'\' AND '.$GLOBALS['PARAM']['LOG_BY_SCHOOL_PASS'].'=\''.$password.'\'';
				$all_row_school = $GLOBALS['conn']->GetAll($requete);
				
				if ( is_array($all_row_school) and (count($all_row_school)) ){
					//die('chez moi');
					$row_school = $all_row_school[0];
					$GLOBALS['LogByEtab']				= 	true;
					$GLOBALS['LogByEtab_code_etab']		=	$row_school[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']];
					$GLOBALS['LogByEtab_code_systeme']	=	$row_school[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
					
					$requete = 'SELECT CODE_GROUPE
								FROM ADMIN_GROUPES
								WHERE CODE_GROUPE <> 0
								ORDER BY ORDRE_GROUPE DESC';
					$groupe = $GLOBALS['conn_dico']->GetAll($requete);
					
				}
			
				if ( is_array($groupe) and (count($groupe)) ){
				
					$_SESSION['groupe'] = $groupe[0]['CODE_GROUPE'];
					session_regenerate_id();
					$_SESSION['valide'] = 1;
					$_SESSION['login'] = $login;
		
					// Les sous_secteurs (systemes d'enseignement)
					set_tab_session('secteurs', $_SESSION['langue']);
					// Les chaines de regroupement
					set_tab_session('chaines', '');                
					// Les années
					set_tab_session('annees', ''); 
					// Les langues
					set_tab_session('langues', ''); 

					$_SESSION['infos_etab'] = get_infos_etab();
					print '<script type="text/Javascript">';
					print " document.location.href='questionnaire.php?code_etab=".$GLOBALS['LogByEtab_code_etab']."&code_systeme=".$GLOBALS['LogByEtab_code_systeme']."&LogByEtab=1';";
					print '</script>';
					exit();
				}
			}
	}// Fin function valide_user

	function valide_user($login,$password){
            //Vérification du login dans la table ADMIN_USERS et chargement du groupe utilisateur
            //(pour le choix du menu)
            //require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';

           // $db                 = $GLOBALS['conn'];
            $requete            = "SELECT CODE_GROUPE, CODE_USER FROM ADMIN_USERS
                                    WHERE NOM_USER = '".$login."' AND PASSWORD = '".$password."';";
            $groupe = $GLOBALS['conn_dico']->GetAll($requete);
            
            $_SESSION['groupe'] = $groupe[0]['CODE_GROUPE'];
			$_SESSION['code_user'] = $groupe[0]['CODE_USER'];
            if ( is_array($groupe) and (isset($_SESSION['groupe'])) ){
                //Implémentation de la session
                //TODO: à quoi ça sert dans la mesure où on "session_destroy()" dans le logout.php?
				session_regenerate_id();
				$_SESSION['valide'] = 1;
                //Mise en place du cookie et de la session
                $_SESSION['login'] = $login;
				/*
                if(!isset($_COOKIE['style_' . $_SESSION['login']])) {

                    setcookie('style_' . $_SESSION['login'], $_SESSION['style'], time() + (3600 * 720));

                } else {

                    $_SESSION['style'] = $_COOKIE['style_' . $_SESSION['login']];

                }
				*/

    //------------------Initialisation en SESSION
                // TODO: Le premier questionnaire devra être retrouvé dans DICO_THEME
                $_SESSION['ID_theme_defaut']    = 90;
				//require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
                //require_once $GLOBALS['SISED_PATH_LIB'] . 'connexion.inc.php';
				// Les sous_secteurs (systemes d'enseignement)
				set_tab_session('secteurs', $_SESSION['langue']);
                // Les chaines de regroupement
				set_tab_session('chaines', '');                
				// Les années
				set_tab_session('annees', ''); 
				// Les filtres
				set_tab_session('filtres', ''); 
                // Les langues
               	set_tab_session('langues', ''); 
                // Les IA
                // TODO: implémenter le combo
//*************************************************************
                // Redirection
                header('Location: '.$GLOBALS['SISED_AURL'].'accueil.php');
                exit();
            }
	}// Fin function valide_user
	
	function valide_user_ws($login, $password) {
		//Vérification du login dans la table ADMIN_USERS et chargement du groupe utilisateur
		//(pour le choix du menu)
		//require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';

	   // $db                 = $GLOBALS['conn'];
		$requete = "SELECT CODE_GROUPE, CODE_USER FROM ADMIN_USERS
								WHERE NOM_USER = '".$login."' AND PASSWORD = '".$password."';";
		$groupe = $GLOBALS['conn_dico']->GetRow($requete);
		
		$_SESSION['groupe'] = $groupe['CODE_GROUPE'];
		$_SESSION['code_user'] = $groupe['CODE_USER'];
		if (is_array($groupe) and (isset($_SESSION['groupe']))){
			return true;
		} else {
			return false;
		}
	}// Fin function valide_user_ws
	
	function infos_user_ws($login, $password) {
		//Vérification du login dans la table ADMIN_USERS et chargement du groupe utilisateur
		//(pour le choix du menu)
		//require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';

	   // $db                 = $GLOBALS['conn'];
		$requete = "SELECT CODE_GROUPE, CODE_USER, NOM_LONG_USER, EMAIL_USER FROM ADMIN_USERS
								WHERE NOM_USER = '".$login."' AND PASSWORD = '".$password."';";
		$groupe = $GLOBALS['conn_dico']->GetRow($requete);
		
		if (is_array($groupe)) {
			return $groupe;
		} else {
			return NULL;
		}
	}// Fin function infos_user_ws
	
	function set_tab_session($cas, $langue){
			
			require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
			define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);
			require_once $GLOBALS['SISED_PATH_LIB'] . 'connexion.inc.php';
			$db                 = $GLOBALS['conn'];
			switch($cas){
					case 'annees':{
							
							$requete                        = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
							//echo "<br/>$requete";
							$_SESSION['tab_annees']         = $db->GetAll($requete);
					
							break;
					}// fin case 
					
					case 'filtres':{
							if(!isset($_GET['code_etab']) && isset($GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC']) && isset($_GET['type_ent_stat']) && $GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC'][$_GET['type_ent_stat']] <> ''){
								$res_exist_filtre = array();
								$requete  = 'SELECT '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].', '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' FROM '.$GLOBALS['PARAM']['TYPE_FILTRE'].','.$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC'][$_GET['type_ent_stat']].'
												WHERE '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' = '.$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC'][$_GET['type_ent_stat']].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].'
														AND '.$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC'][$_GET['type_ent_stat']].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$_SESSION['code_etab_parent'].'
														AND '.$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_ASSOC'][$_GET['type_ent_stat']].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'];
								$res_exist_filtre = $db->GetAll($requete);
								if(count($res_exist_filtre) > 0){
									$_SESSION['filtre'] = $res_exist_filtre[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
								}
							}elseif(isset($_GET['code_etab']) && isset($GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_SELECT']) && isset($_GET['type_ent_stat']) && $GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_SELECT'][$_GET['type_ent_stat']] <> ''){
								$res_exist_filtre = array();
								$requete  = 'SELECT '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].', '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' FROM '.$GLOBALS['PARAM']['TYPE_FILTRE'].','.$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_SELECT'][$_GET['type_ent_stat']].'
												WHERE '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' = '.$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_SELECT'][$_GET['type_ent_stat']].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].'
														AND '.$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_SELECT'][$_GET['type_ent_stat']].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$_GET['code_etab'].'
														AND '.$GLOBALS['PARAM']['RATTACHEMENT_FILTRE_TABLE_SELECT'][$_GET['type_ent_stat']].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'];
								$res_exist_filtre = $db->GetAll($requete);
								if(count($res_exist_filtre) > 0){
									$_SESSION['filtre'] = $res_exist_filtre[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
								}
							}elseif(isset($GLOBALS['PARAM']['FILTRE_TABLE_ASSOC']) && $GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'] <> ''){
								$res_exist_filtre = array();
								if(isset($_GET['code_etab']) && $_GET['code_etab']<>''){
									$_SESSION['code_etab'] = $_GET['code_etab'];
								}
								$requete  = 'SELECT '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].', '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' FROM '.$GLOBALS['PARAM']['TYPE_FILTRE'].','.$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'].'
												WHERE '.$GLOBALS['PARAM']['TYPE_FILTRE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' = '.$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'].'.'.$GLOBALS['PARAM']['FILTRE_CHAMP_ASSOC'].'
														AND '.$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$_SESSION['code_etab'].'
														AND '.$GLOBALS['PARAM']['FILTRE_TABLE_ASSOC'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'];
								$res_exist_filtre = $db->GetAll($requete);
								//if(count($res_exist_filtre) > 0 && (!isset($_GET['filtre']) || $_GET['filtre'] == '') && (!isset($_SESSION['filtre']) || $_SESSION['filtre'] == '')){
								if(count($res_exist_filtre) > 0 && (!isset($_GET['filtre']) || $_GET['filtre'] == '')){
									$_SESSION['filtre'] = $res_exist_filtre[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']];
								}elseif(count($res_exist_filtre) == 0){
									$requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' FROM '.$GLOBALS['PARAM']['TYPE_FILTRE'];
									$_SESSION['filtre'] = 0;
								}
							}else{
								$requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' FROM '.$GLOBALS['PARAM']['TYPE_FILTRE'];
							}
							$_SESSION['tab_filtres']         = $db->GetAll($requete);
					
							break;
					}// fin case 
					
					case 'langues':{
							
							$requete                        = "SELECT CODE_LANGUE, LIBELLE_LANGUE
																FROM DICO_LANGUE;";
							$_SESSION['tab_langues']        = $GLOBALS['conn_dico']->GetAll($requete);
					
							break;
					}// fin case 
					
					case 'chaines':{
							
                if(!(isset($_SESSION['fixe_chaines']) && count((array)$_SESSION['fixe_chaines'][$_SESSION['secteur']])>0)){
					$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].
								' FROM '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].
								' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'];
				}else{
					$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].
								' FROM '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].
								' WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$_SESSION['secteur'].
								' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' IN ('.implode(',',$_SESSION['fixe_chaines'][$_SESSION['secteur']]).')';
                }                                    
                $_SESSION['tab_chaines']        = $db->GetAll($requete);
                $_SESSION['chaine']             = $_SESSION['tab_chaines'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']]; 
					
							break;
					}// fin case   
					
					case 'periodes':{
							
                $requete                        = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'].' FROM '.$GLOBALS['PARAM']['TYPE_PERIODE'].' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'];
                                                    
                $_SESSION['tab_periodes']        = $db->GetAll($requete);
                $_SESSION['periode']             = $_SESSION['tab_periodes'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE']]; 
					
							break;
					}// fin case 
					
					case 'secteurs':{
						if(!(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0)){
							$requete = ' SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', D_TRAD.LIBELLE'.
										  ' FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD'.
										  ' WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE '.
										  ' AND D_TRAD.NOM_TABLE='.'\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\''. 
										  ' AND D_TRAD.CODE_LANGUE='.'\''.$langue.'\'
											ORDER BY T_S_E.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
						}else{
							$requete = ' SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', D_TRAD.LIBELLE'.
										  ' FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD'.
										  ' WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE '.
										  ' AND T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['fixe_secteurs']).')'.
										  ' AND D_TRAD.NOM_TABLE='.'\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\''. 
										  ' AND D_TRAD.CODE_LANGUE='.'\''.$langue.'\'
											ORDER BY T_S_E.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
						}
							//echo $requete ;
						$_SESSION['tab_secteur'] 			= $db->GetAll($requete);
						break;
					}
					
					case 'entite_stat':{
					
						$requete  	= ' SELECT T_R_2.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].', D_TRAD.LIBELLE'.
									  ' FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' AS T_R_2,  DICO_TRADUCTION AS D_TRAD'.
									  ' WHERE T_R_2.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = D_TRAD.CODE_NOMENCLATURE '.
									  ' AND D_TRAD.NOM_TABLE='.'\''.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'\''. 
									  ' AND D_TRAD.CODE_LANGUE='.'\''.$langue.'\'
										ORDER BY T_R_2.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'];
						//echo $requete ;
						$_SESSION['tab_entite_stat'] 			= $db->GetAll($requete);
						$_SESSION['type_ent_stat'] = $_SESSION['tab_entite_stat'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']];
						break;
					}
					// fin case 
			}// fin switch
	}
	
	function get_champ_extract($nom_champ){
        $champ_extract = $nom_champ;
        if (strlen($nom_champ)>30) {
            if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql' || $GLOBALS['conn']->databaseType == 'postgres9') { 
                $taille_max_extract=strlen($nom_champ);                
            }else{
                $taille_max_extract=31;                
            }
            $champ_extract = substr($nom_champ ,0,$taille_max_extract); 
        }
        return($champ_extract);
    }
	
	function get_libelle_from_array($tab, $code, $champ_code, $champ_lib){
        if(is_array($tab)){
            foreach( $tab as $i =>$enr ){
                if( $enr[get_champ_extract($champ_code)] == $code ){
                    return($enr[get_champ_extract($champ_lib)]);
                }
            }
        }
    }
	
	function sup_etab($code_etab,$with_alert=true){
		set_time_limit(0);
		if( isset($code_etab) and (trim($code_etab) <> '') ){
			$conn = $GLOBALS['conn'];
			$GLOBALS['sup_etab_success'] = true;
			$GLOBALS['etab_enfant_exist'] = false;
			if(isset($GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']) && $GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] <> ''){
				$req_etab_enfant   	= "SELECT COUNT(".$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].") FROM ".$GLOBALS['PARAM']['ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']." = ".$code_etab;
				$nb_etab_enfant = $conn->GetOne($req_etab_enfant);
				if($nb_etab_enfant > 0){
					$GLOBALS['etab_enfant_exist'] = true;
					$GLOBALS['sup_etab_success'] = false;
				}
			}
			if($with_alert){
				$GLOBALS['cas_sup_etab'] = true;
			}
			if(!$GLOBALS['etab_enfant_exist']){	
				
				if( isset($_SESSION['hierarchie_regroup']) )	unset($_SESSION['hierarchie_regroup']);
				if( isset($infos_etab) )					unset($infos_etab);
				for($i = count($_SESSION['theme_manager']->list)-1; $i>=0; $i--){
					$id_theme =$_SESSION['theme_manager']->list[$i]['ID'];
					$requete   	= "SELECT DISTINCT PRIORITE, TABLE_MERE FROM   DICO_ZONE WHERE ID_THEME = $id_theme ORDER BY PRIORITE DESC;";
					$all_tables = $GLOBALS['conn_dico']->GetAll($requete);
					foreach ($all_tables as $table){ 
						if(is_array($conn->MetaColumns($table['TABLE_MERE']))){                           
							foreach($conn->MetaColumns($table['TABLE_MERE']) as $champ){
								if($champ->name ==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
									//echo $table['TABLE_MERE'] .'<br>';
									$sql=	'DELETE FROM '.$table['TABLE_MERE'].' WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$code_etab;
									//echo $sql.'<br>'; 
									if ($conn->Execute($sql) === false) {
										$GLOBALS['sup_etab_success'] = false;
									}
								}
							}
						}
					}
				}
			}
		}
	}
			
	function alert_sup_etab(){
			if( $GLOBALS['cas_sup_etab'] == true ){
					lit_libelles_page('/questionnaire.php');

					if($GLOBALS['sup_etab_success'] == true){
							////////// alert sup successs
							print("<script type=\"text/javascript\">\n");
							print("\t <!-- \n");
							print("alert(\"".recherche_libelle_page('SupEtOK')."\"); \n");
							print("\t //--> \n");
							print("</script>\n");
								
					}
					else{
						if($GLOBALS['etab_enfant_exist']){
							print("<script type=\"text/javascript\">\n");
							print("\t <!-- \n");
							print("alert(\"".recherche_libelle_page('SupEtabEnfant')."\"); \n");
							print("\t //--> \n");
							print("</script>\n");
						}else{
							print("<script type=\"text/javascript\">\n");
							print("\t <!-- \n");
							print("alert(\"".recherche_libelle_page('SupEtErr')."\"); \n");
							print("\t //--> \n");
							print("</script>\n");
						}
					}
			}
	}
	function Alerte_Globale($msg){
			if( trim($msg) <> '' ){
					print("<script type=\"text/javascript\">\n");
					print("\t <!-- \n");
					echo "alert(".$msg."); \n";
					print("\t //--> \n");
					print("</script>\n");
			}
	}
	
	function Lancer_Popup(){
			if(isset($_SESSION['lancer_popup_page'])){
				//die('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'.$_SESSION['lancer_popup_page']);				
					$page = $_SESSION['lancer_popup_page'];
					unset($_SESSION['lancer_popup_page']);
					print("<script type=\"text/javascript\">\n");
					print("\t <!-- \n");
					print("\t"." var	popup	=	window.open('".$page."','popregen', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=no,status=no,resizable=no,width=310, height=200, left=400, top=200'); \n");
					//print("\t"." var	popup	=	window.open('".$GLOBALS['SISED_PATH_INC'] . "administration/Config_Regeneration.php','popregen', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=400, height=300, left=270, top=50'); \n");
					//print("\t popup.document.close(); \n");
					//print("\t popup.focus(); \n");
					print("\t //--> \n");
					print("</script>\n");
			}
	}
	
	function ouvrir_popup($page_pp, $nom_pp = 'popup', $det_pp='toolbar=no,location=no,directories=no,menubar=no,scrollbars=no,status=no,resizable=no,width=310, height=200, left=400, top=200'){
			print("<script type=\"text/javascript\">\n");
			print("\t <!-- \n");
			print("\t"." var	popup	=	window.open('".$page_pp."','".$nom_pp."', '".$det_pp."'); \n");
			print("\t //--> \n");
			print("</script>\n");
	}
//echo'<br>-----------------------------------<br>';
//----------------------------------------------------------------
	function Alert_chg_cnx(){
			if(isset($GLOBALS['change_cnx']) && $GLOBALS['change_cnx'] == true){
					
					print("<script type=\"text/javascript\">\n");
					print("\t <!-- \n");
	
					print("\t"." var	popup	=	window.open('". "administration.php?val=regen','popregen', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=no,status=no,resizable=no,width=310, height=200, left=400, top=200'); \n");
					//print("\t"." var	popup	=	window.open('".$GLOBALS['SISED_PATH_INC'] . "administration/Config_Regeneration.php','popregen', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=400, height=300, left=270, top=50'); \n");
					print("\t popup.document.close(); \n");
					print("\t popup.focus(); \n");
						
					print("\t //--> \n");
					print("</script>\n");
			}
	}
	
	function get_libelle_trad($code,$table){
			if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					$conn                 =   $GLOBALS['conn'];
				} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					$conn                 =   $GLOBALS['conn_dico']; 
				}
			// permet de récupérer le libellé dans la table de traduction
			// en fonction de la langue et de la table  aussi
			$requete 	= "SELECT LIBELLE
									FROM DICO_TRADUCTION 
									WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$_SESSION['langue']."'
									AND NOM_TABLE='".$table."'";
			
			$all_res	= $conn->GetAll($requete); 
			//$this->libelle_theme   = $all_res[0]['LIBELLE'];
			return($all_res[0]['LIBELLE']);
	}
	
	function get_libelle($code,$table,$libelle){
			$lib = get_libelle_trad($code,$table);
			if( trim($lib) <> '')
					return $lib;
			else 
					return $libelle;
	}
	
	function insert_traduction($cas, $code, $table, $langue, $libelle, $init=0){
			$ok 		= false ;
			$action = false;
			if( trim($code) <> '' && trim($langue) <> '' && trim($table) <> '' ){
					// Positionnement de la connexion 
				    // Modif pour externalisation de DICO
				    if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					 	$conn                 =   $GLOBALS['conn'];
				    } else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					 	$conn                 =   $GLOBALS['conn_dico']; 
				    } 
		   
					switch($cas){
							case 'DICO_TRADUCTION' :{
									$code_val			=  $code . '';
									$code_base		= 'CODE_NOMENCLATURE'.'';
									$table_base		= 'NOM_TABLE';
									$action 			= true ;
							break;
							}
							case 'DICO_LIBELLE_PAGE' :{
									$code_val			= "'$code'";
									$code_base		= 'CODE_LIBELLE';
									$table_base		= 'NOM_PAGE';
									$action 			= true ;
							break;
							}
					}// fin switch($table){
					if( $action == true ){
							
							$ok	= true;
							$req_test 	= 'SELECT * FROM '.$cas.' 
														 WHERE '.$code_base.'='.$code_val.' 
														 AND  CODE_LANGUE=\''.$langue.'\'
														 AND '.$table_base.'=\''.trim($table).'\'';
										//
							$all_test = $conn->GetAll($req_test);
							if (is_array($all_test) and count($all_test)){
										$req_upd	= 'UPDATE '.$cas.' 
																 SET LIBELLE = '.$conn->qstr($libelle).'
																 WHERE '.$code_base.'='.$code_val.' 
																 AND  CODE_LANGUE=\''.$langue.'\'
																 AND '.$table_base.'=\''.trim($table).'\'';
									if ($conn->Execute($req_upd) === false){
											$ok	= false;
											echo '<br>'.$req_upd.'<br>';
									}
							}else{
									$req_ins 	= 'INSERT INTO '.$cas.'
															 ( '.$code_base.', '.$table_base.', CODE_LANGUE, LIBELLE )
															 VALUES
															 ( '.$code_val.', \''. trim($table) .'\', \''.$langue.'\', '.$conn->qstr($libelle).')';
									if ($conn->Execute($req_ins) === false){
											$ok	= false;
											echo '<br>'.$req_ins.'<br>';
									}
							}
							if($init == 1){
									set_tab_session('langues', ''); 
									if(is_array($_SESSION['tab_langues'])){
											foreach($_SESSION['tab_langues'] as $tab_langue){
													if( $tab_langue['CODE_LANGUE'] <> $langue ){
															insert_traduction($cas, $code, $table, $tab_langue['CODE_LANGUE'], '(#)'.$libelle, 0);
													}
											}
									}
							}
					}// fin if( $action == true ){
			}// fin if( trim($code) <> '' && trim($langue) <> ''){
	}
	
	function sup_libelles_langue($langue){
			if(trim($langue) <> ''){
			
					$conn 		= $GLOBALS['conn'];
					
					$req_del 	= 'DELETE FROM DICO_TRADUCTION WHERE CODE_LANGUE=\''.$langue.'\'';
					if ($GLOBALS['conn']->Execute($req_del) === false){}
					if ($GLOBALS['conn_dico']->Execute($req_del) === false){}
					
					$req_del 	= 'DELETE FROM DICO_LIBELLE_PAGE WHERE CODE_LANGUE=\''.$langue.'\'';
					if ($GLOBALS['conn_dico']->Execute($req_del) === false){}

					vider_dossier($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue); 
					 
					$req_grp		= "SELECT CODE_GROUPE FROM ADMIN_GROUPES ;";
					$groupes		= $GLOBALS['conn_dico']->GetAll($req_grp);
					if(is_array($groupes)){
							foreach ($groupes as $groupe){// Pour chaque groupe
									//echo '<br>'.$GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe['CODE_GROUPE'].'/'.$langue.'<br>';
									vider_dossier($GLOBALS['SISED_PATH_JSC'] . 'menus/'.$groupe['CODE_GROUPE'].'/'.$langue);
							}
					}
					//die('****************** ici *****************************************');
 			}
	}
	
	function init_libelles_langue($langue){
			if(trim($langue) <> ''){
			   
					$conn 		= $GLOBALS['conn'];
			
					$req_init 	= "INSERT INTO DICO_TRADUCTION (CODE_NOMENCLATURE,NOM_TABLE,CODE_LANGUE,LIBELLE,LIBELLE_MESURE1,LIBELLE_MESURE2)
												 SELECT CODE_NOMENCLATURE,NOM_TABLE,'".$langue."','(#)'+LIBELLE,LIBELLE_MESURE1,LIBELLE_MESURE2 FROM
												 DICO_TRADUCTION WHERE CODE_LANGUE='".$_SESSION['langue']."'";
								//
					//echo '<br>'.$req_init.'<br>';
					if ($GLOBALS['conn']->Execute($req_init) === false){
							echo '<br>'.$req_init.'<br>';
					}
					if ($GLOBALS['conn_dico']->Execute($req_init) === false){
							echo '<br>'.$req_init.'<br>';
					}
					
					$req_init 	= "INSERT INTO DICO_LIBELLE_PAGE (CODE_LIBELLE,NOM_PAGE,CODE_LANGUE,LIBELLE)
												 SELECT CODE_LIBELLE,NOM_PAGE,'".$langue."','(#)'+LIBELLE FROM
												 DICO_LIBELLE_PAGE WHERE CODE_LANGUE='".$_SESSION['langue']."'";
								//
					if ($GLOBALS['conn_dico']->Execute($req_init) === false){
							echo '<br>'.$req_init.'<br>';
					}
			}
	}
	
	function vider_dossier($dir) {
		 if($objs = glob($dir."/*")){
				 foreach($objs as $obj) {
						 is_dir($obj)? vider_dossier($obj) : unlink($obj);
				 }
		 }
		 rmdir($dir);
	}
	
	function vider_contenu_dossier($dir, $sup_rep=false) {
			if($objs = glob($dir."/*")){
					foreach($objs as $obj) {
						 is_dir($obj)? vider_dossier($obj, true) : unlink($obj);
					}
			}
			if($sup_rep == true){
					rmdir($dir);
			}
	}
	
	function get_champ_extraction($nom_champ){
			$conn 		= $GLOBALS['conn'];
			$champ_extract = $nom_champ;
			if (strlen($nom_champ)>30) {
					if ($conn->databaseType == 'mssqlnative' || $conn->databaseType == 'mssql' || $conn->databaseType == 'postgres9' || $GLOBALS['conn']->databaseType == 'mysql' || $GLOBALS['conn']->databaseType == 'access') { 
							$taille_max_extract=strlen($nom_champ);                
					}else{
							$taille_max_extract=31;                
					}
					$champ_extract = substr($nom_champ ,0,$taille_max_extract); 
			}
			return($champ_extract);
	}
	
	function debut_popup_progress(){
			print("\t <script type='text/javascript'> \n");
			print("\t <!--  \n");
			print("\t window.resizeTo(380,130); \n"); 
			print("\t --> \n");
			print("\t </script> \n");
			print("\t <br>\n");
			include $GLOBALS['SISED_PATH_INC'] . 'administration/progressbar.php';
			flush();
	}
	
	function fin_popup_progress(){
			print("<script type=\"text/javascript\">\n");
			print("\t <!-- \n");
			print("\t fermer();\n");
			print("\t //--> \n");
			print("</script>\n");
	}
	
	function get_zone_ref_of($id_zone){
		$sql_zone_ref = ' SELECT DICO_ZONE.TABLE_FILLE, DICO_ZONE.SQL_REQ, DICO_ZONE.CHAMP_FILS, DICO_ZONE.CHAMP_PERE '.
						' FROM DICO_ZONE'.			 
						' WHERE DICO_ZONE.ID_ZONE='.$id_zone;
					
		$res	= $GLOBALS['conn_dico']->GetAll($sql_zone_ref); 
		return($res[0]);
	}
	
	function exist_champ_in_table($nom_champ, $table){
		$conn 	= $GLOBALS['conn'];
		$champs	= $conn->MetaColumns($table);
		foreach( $champs as $i => $champ ){
			if(trim($champ->name) == trim($nom_champ)){
				return true;
				break;
	}	}	}
		
	function exist_table($nom_table){
		$conn 	= $GLOBALS['conn'];
		$all_tables = array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
		foreach( $all_tables as $i => $table){
			if(strtoupper(trim($nom_table)) == strtoupper(trim($table))){
				return true;
				break;
	}	}	}
	
	function get_type_champ_in_table($nom_champ, $nom_table){ 
		if( exist_table($nom_table) and exist_champ_in_table($nom_champ, $nom_table) ){
		
			$conn 	= $GLOBALS['conn'];
			
			$meta_types_integer 	= array('L', 'N', 'I', 'R');
			$meta_types_date		= array('D', 'T');
			
			$req = 'select '.$nom_champ.' from '.$nom_table;
			$recordSet 	= $conn->Execute($req);
			
			$all_champs_table = $conn->MetaColumns($nom_table);
			foreach( $all_champs_table as $champ ){
				if( trim($champ->name) == trim($nom_champ) ){
					$meta_type 	= $recordSet->MetaType($champ->type);
					//echo "<br>$nom_champ, $table, ".$type_champ.", $meta_type<br>";
					if(in_array($meta_type , $meta_types_integer)){
						return 'integer';
					}elseif(in_array($meta_type, $meta_types_date)){
						return 'date';
					}else{
						return 'string';
	}	}	}	}	}
	
	function get_infos_etab($varNomEtab='NomEtab', $varCodeEtab='CodeEtab', $varCodeAdmin='CodeAdmin'){
		$conn = $GLOBALS['conn'];
		$show_code_admin	= false ;
		$show_statut 		= false ;
		$show_chef_etab		= false ;
		
		if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
			$show_code_admin	= true ;
		}
		
		if( exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
			if( exist_table($GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']) ){
				$show_statut	= true ;
		}	}
		
		if( exist_champ_in_table($GLOBALS['PARAM']['NOM_CHEF_ETABLISSEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
			$show_chef_etab		= true ;
		}
		
		$champs_extract_in_etab = array($GLOBALS['PARAM']['CODE_ETABLISSEMENT'].', '.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']);
		
		($show_code_admin == true)	? ( $champs_extract_in_etab[] = $GLOBALS['PARAM']['CODE_ADMINISTRATIF'] ) : 0;
		($show_statut == true) 		? ( $champs_extract_in_etab[] = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'] ) : 0;
		($show_chef_etab == true)	? ( $champs_extract_in_etab[] = $GLOBALS['PARAM']['NOM_CHEF_ETABLISSEMENT'] ) : 0;
		
		$requete    = 'SELECT '.implode(', ', $champs_extract_in_etab).' 
					   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' 
					   WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='. $_SESSION['code_etab'];
		//echo $requete;exit;
		$result_etab = $conn->GetAll($requete);
		$infos_etab	= '';
		$infos_etab .= recherche_libelle_page($varNomEtab).' <b><u>'.$result_etab[0][$GLOBALS['PARAM']['NOM_ETABLISSEMENT']].'</u></b>';
		$_SESSION['nom_etab'] = $result_etab[0][$GLOBALS['PARAM']['NOM_ETABLISSEMENT']];
		$infos_etab .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.recherche_libelle_page($varCodeEtab).' <b><u>'.$_SESSION['code_etab'].'</u></b>' ;
		($show_code_admin	== true) ? ( $infos_etab .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.recherche_libelle_page($varCodeAdmin).'<b><u>'.$result_etab[0][$GLOBALS['PARAM']['CODE_ADMINISTRATIF']].'</u></b>' ) : 0;
		($show_statut		== true) ? (  $infos_etab .= '<BR>'. recherche_libelle_page('Statut').' <b><u>'.recherche_libelle_nomenclature($result_etab[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']], $GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']).'</u></b>' ) : 0;
		($show_chef_etab	== true) ? ($infos_etab .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . recherche_libelle_page('NomChefEta').' <b><u>'.$result_etab[0][$GLOBALS['PARAM']['NOM_CHEF_ETABLISSEMENT']].'</u></b>') : 0;
		$infos_etab .= '<br>'.recherche_libelle_page('SsSecteur').' <b><u>'.recherche_libelle_nomenclature($result_etab[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']], $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']).'</u></b>'; 
		//$infos_etab .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.recherche_libelle_page('curr_year').': <b><u>'.get_libelle_from_array($_SESSION['tab_annees'], $_SESSION['annee'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'])  .'</u></b>';
		return ($infos_etab);
	}
	
	function get_libelle_page($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				$conn 	= $GLOBALS['conn'];
				$requete 	= "SELECT LIBELLE
										FROM DICO_LIBELLE_PAGE 
										WHERE CODE_LIBELLE='".$code."' And CODE_LANGUE='".$langue."'
										AND NOM_PAGE='".$table."'";
				// Gestion des erreurs lors de l'exécution de la requête SQL
				
                try {
                    $all_res	= $GLOBALS['conn_dico']->GetAll($requete);
                    if (!is_array($all_res))                    
                        throw new Exception('ERR_SQL');                     
                }
                catch(Exception $e){
                    $erreur = new erreur_manager($e,$requete);
                }
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
    }
	
	function supp_config_of_secteur ($id_syst){
		
		$db            = $GLOBALS['conn'];
		
		// Suppression dans element DICO_CHAINE_LOCALISATION ds DICO_TRADUCTION
        $requete                = '	DELETE FROM DICO_TRADUCTION WHERE NOM_TABLE = \'DICO_CHAINE_LOCALISATION\'
									AND CODE_NOMENCLATURE 
									IN (SELECT DICO_TRADUCTION.CODE_NOMENCLATURE
										FROM DICO_CHAINE_LOCALISATION,DICO_TRADUCTION
										WHERE  DICO_CHAINE_LOCALISATION.CODE_TYPE_CHAINE = DICO_TRADUCTION.CODE_NOMENCLATURE
										AND DICO_CHAINE_LOCALISATION.CODE_TYPE_SYSTEME_ENSEIGNEMENT ='.$id_syst.'
										AND DICO_TRADUCTION.NOM_TABLE = \'DICO_CHAINE_LOCALISATION\';)';
										
		if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';        $db->Execute($requete);
		
		// Suppression dans element DICO_THEME_SYSTEME ds DICO_TRADUCTION
        $requete                = '	DELETE FROM DICO_TRADUCTION WHERE 
									(NOM_TABLE = \'DICO_THEME_LIB_LONG\' OR NOM_TABLE = \'DICO_THEME_LIB_MENU\')
									AND CODE_NOMENCLATURE 
									IN (SELECT DICO_TRADUCTION.CODE_NOMENCLATURE
										FROM DICO_THEME_SYSTEME, DICO_TRADUCTION
										WHERE  DICO_THEME_SYSTEME.ID_THEME_SYSTEME = DICO_TRADUCTION.CODE_NOMENCLATURE
										AND DICO_THEME_SYSTEME.ID_SYSTEME ='.$id_syst.'
										AND (NOM_TABLE = \'DICO_THEME_LIB_LONG\' OR NOM_TABLE = \'DICO_THEME_LIB_MENU\');)';
										
		if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';        $GLOBALS['conn_dico']->Execute($requete);
		
		// Suppression dans la table DICO_CHAINE_LOCALISATION
        $requete                = "DELETE FROM DICO_CHAINE_LOCALISATION WHERE CODE_TYPE_SYSTEME_ENSEIGNEMENT =".$id_syst.";";
		if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';        $GLOBALS['conn_dico']->Execute($requete);
      
	    // Suppression dans la table DICO_REPORT_SYSTEME
        $requete                = "DELETE FROM DICO_REPORT_SYSTEME WHERE ID_SYSTEME =".$id_syst.";";
		if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';        $GLOBALS['conn_dico']->Execute($requete);
	  
	    // Suppression dans la table DICO_THEME_SYSTEME
        $requete                = "DELETE FROM DICO_THEME_SYSTEME WHERE ID_SYSTEME =".$id_syst.";";
		if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';        $GLOBALS['conn_dico']->Execute($requete);
		
	    // Suppression dans la table DICO_ZONE_SYSTEME
        $requete                = "DELETE FROM DICO_ZONE_SYSTEME WHERE ID_SYSTEME =".$id_syst.";";
		if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';        $GLOBALS['conn_dico']->Execute($requete);
	    
		// Suppression dans la table PARAM_DEFAUT
        $requete                = "UPDATE PARAM_DEFAUT SET CODE_SECTEUR = NULL WHERE CODE_SECTEUR =".$id_syst.";";
		if ($GLOBALS['conn_dico']->Execute($requete) === false) echo '<br>ERROR: '.$requete.'<br>';        $GLOBALS['conn_dico']->Execute($requete);
		
	}
	
	function get_record($val_champ, $champ_extract, $tab_recordset){
		if( is_array($tab_recordset) && (trim($champ_extract) <> '') && (trim($val_champ) <> '') ){
			foreach($tab_recordset as $i => $row){
				if( trim ($val_champ) == trim ($row[get_champ_extract($champ_extract)]) ){
					return($row);
				}
			}
		}
	}
	
	function tri_recordset($tab_recordset, $champ_extract, $tab_ord){
		$tab_tri = array();
		if( is_array($tab_recordset) && is_array($tab_ord) && (trim($champ_extract) <> '') ){
			foreach($tab_ord as $i => $val){
				if( is_array($row = get_record($val, $champ_extract, $tab_recordset)) ){
					$tab_tri[] = $row ;
				}
			}
		}
		return($tab_tri);
	}
	function _est_ds_tableau($elem,$tab){
		if(is_array($tab)){
			foreach($tab as $elements){
				if( $elements == $elem ){
					return true;
				}	
			}	
		}	
	}

	function get_dico_tables_by_sector($secteur){
 
		$req = 'SELECT DICO_THEME_SYSTEME.ID_THEME_SYSTEME, DICO_THEME.ID_TYPE_THEME, 
				DICO_THEME.ID, DICO_THEME_SYSTEME.PERE, DICO_THEME_SYSTEME.PRECEDENT
				FROM DICO_THEME_SYSTEME , DICO_THEME 
				WHERE DICO_THEME.ID = DICO_THEME_SYSTEME.ID
				AND  DICO_THEME_SYSTEME.ID_SYSTEME='.$secteur ;

		$res	= $GLOBALS['conn_dico']->GetAll($req); 
		if(is_array($res) && count($res)>0){
			$list_thms			=   $GLOBALS['theme_manager']->tri_list($res); 
			$temp_list_tabms	= 	array();
			$list_tabms_ord 	= 	array();
			//print_r($list_thms);
			if( is_array($list_thms) && count($list_thms) ){
				foreach($list_thms as $i_thm => $thm){
					
					if ($thm['ID_TYPE_THEME'] <> 1){
						$reqtbms = "SELECT * FROM DICO_TABLE_MERE_THEME WHERE ID_THEME=" . $thm['ID'] . " ORDER BY PRIORITE";
					}else{
						$reqtbms = "SELECT ID_THEME,ID_TABLE_MERE_THEME ,TABLE_MERE AS NOM_TABLE_MERE,PRIORITE FROM DICO_ZONE WHERE ID_THEME=" . $thm['ID'] . " ORDER BY PRIORITE";
					}
					
					$currtabs = $GLOBALS['conn_dico']->GetAll($reqtbms); 
					if( is_array($currtabs) && count($currtabs) ){
						foreach($currtabs as $i_tabm => $tabm){
							$temp_list_tabms[] = $tabm['NOM_TABLE_MERE'] ;
						}
					}
				}
			}
			
			foreach($temp_list_tabms as $i_tabm => $tabm){
				if(!_est_ds_tableau($tabm, $list_tabms_ord)){
					$list_tabms_ord[] = $tabm ;
				}
			}
			return($list_tabms_ord);
		}
	}
	
	function set_session_tabms_imputees($secteur){
		if( !isset($_SESSION['imput_all_tabms']) ){
			$_SESSION['imput_all_tabms'] = array();
		}
		if( !count((array)$_SESSION['imput_all_tabms'][$secteur]) ){
			$_SESSION['imput_all_tabms'][$secteur] = get_dico_tables_by_sector($secteur);
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
			$all_res	= $GLOBALS['conn']->GetAll($sql_eval);
			if(!is_array($all_res)){              
				throw new Exception('ERR_SQL');   
			} 
			$code		=		array();
			foreach( $all_res as $i => $res) {
				$code[] = $res[get_champ_extract($champ_pere)];
			}
			$req_nomenc_trad = 'SELECT DICO_TRADUCTION.LIBELLE, DICO_TRADUCTION.CODE_NOMENCLATURE AS '.$champ_pere.'
								FROM '.$table_nomenclature.', DICO_TRADUCTION
								WHERE DICO_TRADUCTION.CODE_NOMENCLATURE = '.$table_nomenclature.'.'.$champ_fils.'
								AND DICO_TRADUCTION.NOM_TABLE=\''.$table_nomenclature.'\' 
								AND DICO_TRADUCTION.CODE_LANGUE=\''.$langue.'\'
								AND DICO_TRADUCTION.CODE_NOMENCLATURE IN ('.implode(', ',$code).')
								ORDER BY '.$table_nomenclature.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$table_nomenclature;  
			try {
				$all_res	= $GLOBALS['conn']->GetAll($req_nomenc_trad);
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
	function requete_nomenclature_bool($champ_pere, $langue){
		$req_bool       = 'SELECT DICO_TRADUCTION.LIBELLE, DICO_BOOLEEN.CODE_DICO_BOOLEEN AS '.$champ_pere.'
							FROM DICO_BOOLEEN ,  DICO_TRADUCTION
							WHERE DICO_BOOLEEN.CODE_DICO_BOOLEEN = DICO_TRADUCTION.CODE_NOMENCLATURE 
							AND DICO_TRADUCTION.NOM_TABLE=\'DICO_BOOLEEN\' 
							AND DICO_TRADUCTION.CODE_LANGUE=\''.$langue.'\'
							ORDER BY DICO_BOOLEEN.ORDRE_DICO_BOOLEEN';  
		try {
				$all_res	= $GLOBALS['conn_dico']->GetAll($req_bool);
				if(!is_array($all_res)){                    
						throw new Exception('ERR_SQL');  
				} 
				return $all_res;									
		}
		catch(Exception $e){
				$erreur = new erreur_manager($e,$req_bool);
		}
				// Fin Traitement Erreur Cas : GetAll / GetRow
    }
	
	function get_table_field_values($base, $table, $field, $where){
		$sql_table_field_values = ' SELECT '.$field.' FROM '.$table.$where;
					
		$res = $GLOBALS[$base]->GetAll($sql_table_field_values); 
		return($res);
	}
	
	//recherche du id_trace max ds la table dico_trace
	function get_cle_max_id_trace(){
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
	
	//converti les caractères spéciaux de word
	function convertWordSpecialChr($txt){ 

	   //gestion des guillemets divers 
	
	   $txt = preg_replace("/&#8217;/","'",$txt); 
	
	   $txt = preg_replace("/&#8216;/","'",$txt); 
	
	   //gestion des ... 
	
	   $txt = preg_replace("/&#8230;/","...",$txt); 
	
	   //gestion du oe 
	
	   $txt = preg_replace("/&#339;/","oe",$txt); 
	
	   return $txt; 
	
	}

  function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
  }
  function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
  }
	/**
	 * This file is part of the array_column library
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 *
	 * @copyright Copyright (c) Ben Ramsey (http://benramsey.com)
	 * @license http://opensource.org/licenses/MIT MIT
	 */
	if (!function_exists('array_column')) {
		/**
		 * Returns the values from a single column of the input array, identified by
		 * the $columnKey.
		 *
		 * Optionally, you may provide an $indexKey to index the values in the returned
		 * array by the values from the $indexKey column in the input array.
		 *
		 * @param array $input A multi-dimensional array (record set) from which to pull
		 *                     a column of values.
		 * @param mixed $columnKey The column of values to return. This value may be the
		 *                         integer key of the column you wish to retrieve, or it
		 *                         may be the string key name for an associative array.
		 * @param mixed $indexKey (Optional.) The column to use as the index/keys for
		 *                        the returned array. This value may be the integer key
		 *                        of the column, or it may be the string key name.
		 * @return array
		 */
		function array_column($input = null, $columnKey = null, $indexKey = null)
		{
			// Using func_get_args() in order to check for proper number of
			// parameters and trigger errors exactly as the built-in array_column()
			// does in PHP 5.5.
			$argc = func_num_args();
			$params = func_get_args();
			if ($argc < 2) {
				trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
				return null;
			}
			if (!is_array($params[0])) {
				trigger_error(
					'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
					E_USER_WARNING
				);
				return null;
			}
			if (!is_int($params[1])
				&& !is_float($params[1])
				&& !is_string($params[1])
				&& $params[1] !== null
				&& !(is_object($params[1]) && method_exists($params[1], '__toString'))
			) {
				trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
				return false;
			}
			if (isset($params[2])
				&& !is_int($params[2])
				&& !is_float($params[2])
				&& !is_string($params[2])
				&& !(is_object($params[2]) && method_exists($params[2], '__toString'))
			) {
				trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
				return false;
			}
			$paramsInput = $params[0];
			$paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
			$paramsIndexKey = null;
			if (isset($params[2])) {
				if (is_float($params[2]) || is_int($params[2])) {
					$paramsIndexKey = (int) $params[2];
				} else {
					$paramsIndexKey = (string) $params[2];
				}
			}
			$resultArray = array();
			foreach ($paramsInput as $row) {
				$key = $value = null;
				$keySet = $valueSet = false;
				if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
					$keySet = true;
					$key = (string) $row[$paramsIndexKey];
				}
				if ($paramsColumnKey === null) {
					$valueSet = true;
					$value = $row;
				} elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
					$valueSet = true;
					$value = $row[$paramsColumnKey];
				}
				if ($valueSet) {
					if ($keySet) {
						$resultArray[$key] = $value;
					} else {
						$resultArray[] = $value;
					}
				}
			}
			return $resultArray;
		}
	}
	function generateExportQueries($tabsAndFields) {
		$tabsAndQueries = array();
		if(is_array($tabsAndFields)) {
			foreach($tabsAndFields as $tab => $fields) { 
				$data_query = "SELECT ";
				$tabsAndQueries[$tab] = array();
				$nbFields = count($fields);
				for($i = 0; $i < $nbFields; $i++) {
					$data_query .= $fields[$i];
					if (($i + 1) < $nbFields) {
						$data_query .= ", ";
					}
				}
				$data_query .= " FROM ".$tab;
				$addAnd = false;
				if (in_array($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $fields)) {
					$data_query .= " WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=_code_school_ ";
					$addAnd = true;
				}
				if (in_array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $fields)) {
					if ($addAnd) {
						$data_query .= "AND ";
					}
					$data_query .= $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=_code_year_ ";
					$addAnd = true;
				}
				if (in_array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'], $fields)) {
					if ($addAnd) {
						$data_query .= "AND ";
					}
					$data_query .= $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']."=_code_camp_ ";
					$addAnd = true;
				}
				if (in_array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $fields)) {
					if ($addAnd) {
						$data_query .= "AND ";
					}
					$data_query .= $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']."=_code_filtre_ ";
				}
				$tabsAndQueries[$tab] = $data_query;
			}
		}
		return $tabsAndQueries;
	}
	
	
	function get_tab_mere_zones($idTheme, $tabMere) {
			
			$requete	=	"	SELECT  DICO_ZONE.CHAMP_PERE
								FROM    DICO_ZONE
								WHERE   DICO_ZONE.ID_THEME = ". $idTheme . "
								AND 	DICO_ZONE.TABLE_MERE = '".$tabMere."'
								AND     DICO_ZONE.CHAMP_PERE <> ''";
			//echo $requete;						
			$champs  = $GLOBALS['conn_dico']->GetAll($requete);
			return $champs;		
	} 	
	
	function get_camp_themes($id_sys, $id_camp, $code_lang) {
	   $requete = "SELECT DICO_THEME_SYSTEME.ID_THEME_SYSTEME AS id, DICO_THEME_SYSTEME.ID AS id_theme, DICO_TRADUCTION.LIBELLE AS title, DICO_THEME_SYSTEME.APPARTENANCE AS idcamp, DICO_THEME_SYSTEME.ID_SYSTEME AS idsys, DICO_THEME_SYSTEME.PRECEDENT AS pre
					FROM DICO_TRADUCTION INNER JOIN DICO_THEME_SYSTEME ON DICO_TRADUCTION.CODE_NOMENCLATURE = DICO_THEME_SYSTEME.ID_THEME_SYSTEME
					WHERE (((DICO_THEME_SYSTEME.APPARTENANCE)=".$id_camp.") AND ((DICO_THEME_SYSTEME.ID_SYSTEME)=".$id_sys.") AND ((DICO_TRADUCTION.NOM_TABLE)='DICO_THEME_LIB_MENU') AND ((DICO_TRADUCTION.CODE_LANGUE)='".$code_lang."')) AND (DICO_THEME_SYSTEME.FRAME <> '');";
	   
		 $list_theme = $GLOBALS['conn_dico']->GetAll($requete);
	   
	   return  $list_theme;
	}
	
	/**
	* METHODE : get_tables_meres_theme()
	* <pre>
	* Récupération des tables mères du thème
	* </pre>
	* @access public
	* 
	*/
	function get_tables_meres_theme($idTheme){
			$requete 	=	' SELECT    ID_TABLE_MERE_THEME , 
										NOM_TABLE_MERE , 
										PRIORITE 
										FROM       DICO_TABLE_MERE_THEME
										WHERE      ID_THEME ='.$idTheme.'
										ORDER BY 	 PRIORITE';
	
			$tabMere  = $GLOBALS['conn_dico']->GetAll($requete);
			return $tabMere;
			
	} //FIN get_tables_meres_theme()
			
	function ordonner_theme_appart($secteur){
		$tab_theme_ordre = array();
		$critere_appart = "";
		$req_appart = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'<>255';
		$rs_appart= $GLOBALS['conn']->GetAll($req_appart); 
		foreach ($rs_appart as $appart){
			$critere_appart = ' AND  D_T_S.APPARTENANCE='.$appart[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']];
			
			$requete = "SELECT D_T_S.ID_THEME_SYSTEME, D_T.ID_TYPE_THEME, D_T_S.ID, D_T_S.PERE, D_T_S.PRECEDENT
					FROM DICO_THEME_SYSTEME AS D_T_S, DICO_THEME AS D_T
					WHERE D_T.ID = D_T_S.ID
					AND  D_T_S.ID_SYSTEME=".$secteur.$critere_appart;
	
			$result_theme	= $GLOBALS['conn_dico']->GetAll($requete);
			
			// tri de la table selon les precedences
			$result_theme   =   $GLOBALS['theme_manager']->tri_list($result_theme); 
			
			foreach($result_theme as $thm){
				$tab_theme_ordre[] = $thm;
			}			
		}
		return $tab_theme_ordre;
	}
	
	function ordonner_table($tab_theme_ordre) {
		$GLOBALS['conn_dico']->Execute("DELETE FROM  DICO_TABLE_ORDRE");
		$cpt = 3;
		foreach ($tab_theme_ordre as $thm_ord) {
			if ($thm_ord['ID_TYPE_THEME'] <> 1) {
				$strRequete = "SELECT * FROM DICO_TABLE_MERE_THEME WHERE ID_THEME=" . $thm_ord['ID'] . " ORDER BY PRIORITE";
			}else{
				$strRequete = "SELECT ID_THEME,ID_TABLE_MERE_THEME ,TABLE_MERE AS NOM_TABLE_MERE,PRIORITE FROM DICO_ZONE WHERE ID_THEME=" . $thm_ord['ID'] . " ORDER BY PRIORITE";
			}
			$rsTable= $GLOBALS['conn_dico']->Execute($strRequete);
			if ($rsTable->RecordCount()>0){
				while (!$rsTable->EOF){
					//if (trim($rsTable->fields['NOM_TABLE_MERE']) <> $GLOBALS['PARAM']['ENSEIGNANT']){
						$strRequete = "SELECT * FROM DICO_TABLE_ORDRE WHERE NOM_TABLE='" . trim($rsTable->fields['NOM_TABLE_MERE']) . "'";
						$rsTrouve=$GLOBALS['conn_dico']->Execute($strRequete);
						if ($rsTrouve->RecordCount()==0){
							if ($thm_ord['ID_TYPE_THEME'] <> 1){
								$res=$GLOBALS['conn_dico']->Execute("INSERT INTO DICO_TABLE_ORDRE (ID_TABLE_ORDRE,NOM_TABLE,ORDRE) VALUES (" . $rsTable->fields['ID_TABLE_MERE_THEME'] . ",'" . $rsTable->fields['NOM_TABLE_MERE'] . "'," . $cpt . ")");
							}else{
								$res=$GLOBALS['conn_dico']->Execute ("INSERT INTO DICO_TABLE_ORDRE (ID_TABLE_ORDRE,NOM_TABLE,ORDRE) VALUES (" . ($cpt * 100) . ",'" . $rsTable->fields['NOM_TABLE_MERE'] . "'," . $cpt . ")");
							}
						}
						$rsTrouve->Close();
				   //}
					$rsTable->MoveNext();
				   $cpt = $cpt + 1;
				}
			}
		}
	}
	
	/**
	* METHODE : create_zip()
	* <pre>
	* fonction de création du fichier ZIP aprés export
	* </pre>
	* @access public
	* 
	*/
	function create_zip($exportParentDir, $dirName) {
		return create_zip_full($exportParentDir, $dirName, false);
	}
	
	function create_zip_full($exportParentDir, $dirName, $printError=false) {
		include_once($GLOBALS['SISED_PATH_LIB'].'pclzip.lib.php');
		
		$fich_compl_zip = $exportParentDir . "/" .  $dirName .".zip" ;
		if(file_exists($fich_compl_zip)){
			unlink($fich_compl_zip);
		}
		$zip = new PclZip($fich_compl_zip);
		$list_zip = array();
		$list_zip[] = $exportParentDir . "/" .  $dirName;
		$res = $zip->create( $list_zip , PCLZIP_OPT_REMOVE_PATH, $exportParentDir);
		if ($res == 0) {
			if ($printError) {
				print("Error : ".$zip->errorInfo(true));
			}
			return false;
		}
		return true;
	}
	
	// Get primary keys from dico 
	function MetaPrimaryKeys($view)
	{
		$viewOk = strtoupper($view);
		$sql = "SELECT CLES FROM DICO_TABLE_CLES WHERE NOM_TABLE='".$viewOk."'";
		$lstKeys = $GLOBALS['conn_dico']->GetCol($sql);
		if ($lstKeys) {
			$tabKeys = explode(",", $lstKeys[0]);
			return $tabKeys;
		}
		return array();
	}
	
	function encodeURIComponent($str) {
		$revert = array("%21"=>"!", "%2A"=>"*", "%27"=>"'", "%28"=>"(", "%29"=>")");
		return strstr(urlencode($str), $revert);
	}	
	
	function replaceAccents($str) {
		$search = explode(",",
	"ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ");
		$replace = explode(",",
	"c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE");
		return str_replace($search, $replace, $str);
	}

	function htmlEncode($str) {
	
		//$entities = array("&amp;", "&quot;", "&apos;", "&lt;", "&gt;", "&nbsp;", "&iexcl;", "&cent;", "&pound;", "&curren;", "&yen;", "&brvbar;", "&sect;", "&uml;", "&copy;", "&ordf;", "&laquo;", "&not;", "&shy;", "&reg;", "&macr;", "&deg;", "&plusmn;", "&sup2;", "&sup3;", "&acute;", "&micro;", "&para;", "&middot;", "&cedil;", "&sup1;", "&ordm;", "&raquo;", "&frac14;", "&frac12;", "&frac34;", "&iquest;", "&times;", "&divide;", "&Agrave;", "&Aacute;", "&Acirc;", "&Atilde;", "&Auml;", "&Aring;", "&AElig;", "&Ccedil;", "&Egrave;", "&Eacute;", "&Ecirc;", "&Euml;", "&Igrave;", "&Iacute;", "&Icirc;", "&Iuml;", "&ETH;", "&Ntilde;", "&Ograve;", "&Oacute;", "&Ocirc;", "&Otilde;", "&Ouml;", "&Oslash;", "&Ugrave;", "&Uacute;", "&Ucirc;", "&Uuml;", "&Yacute;", "&THORN;", "&szlig;", "&agrave;", "&aacute;", "&acirc;", "&atilde;", "&auml;", "&aring;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&igrave;", "&iacute;", "&icirc;", "&iuml;", "&eth;", "&ntilde;", "&ograve;", "&oacute;", "&ocirc;", "&otilde;", "&ouml;", "&oslash;", "&ugrave;", "&uacute;", "&ucirc;", "&uuml;", "&yacute;", "&thorn;", "&yuml;");
		$entities = array("&lt;", "&gt;", "&amp;", "&quot;", "&apos;", "&nbsp;", "&iexcl;", "&cent;", "&pound;", "&curren;", "&yen;", "&brvbar;", "&sect;", "&uml;", "&copy;", "&ordf;", "&laquo;", "&not;", "&shy;", "&reg;", "&macr;", "&deg;", "&plusmn;", "&sup2;", "&sup3;", "&acute;", "&micro;", "&para;", "&middot;", "&cedil;", "&sup1;", "&ordm;", "&raquo;", "&frac14;", "&frac12;", "&frac34;", "&iquest;", "&times;", "&divide;", "&Agrave;", "&Aacute;", "&Acirc;", "&Atilde;", "&Auml;", "&Aring;", "&AElig;", "&Ccedil;", "&Egrave;", "&Eacute;", "&Ecirc;", "&Euml;", "&Igrave;", "&Iacute;", "&Icirc;", "&Iuml;", "&ETH;", "&Ntilde;", "&Ograve;", "&Oacute;", "&Ocirc;", "&Otilde;", "&Ouml;", "&Oslash;", "&Ugrave;", "&Uacute;", "&Ucirc;", "&Uuml;", "&Yacute;", "&THORN;", "&szlig;", "&agrave;", "&aacute;", "&acirc;", "&atilde;", "&auml;", "&aring;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&igrave;", "&iacute;", "&icirc;", "&iuml;", "&eth;", "&ntilde;", "&ograve;", "&oacute;", "&ocirc;", "&otilde;", "&ouml;", "&oslash;", "&ugrave;", "&uacute;", "&ucirc;", "&uuml;", "&yacute;", "&thorn;", "&yuml;");

		//$UCchar = array("&", "\"", "'", "<", ">", "", "¡", "¢", "£", "¤", "¥", "¦", "§", "¨", "©", "ª", "«", "¬", "", "®", "¯", "°", "±", "²", "³", "´", "µ", "¶", "·", "¸", "¹", "º", "»", "¼", "½", "¾", "¿", "×", "÷", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï", "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "Þ", "ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
		$UCchar = array("<", ">", "&", "\"", "'", "", "¡", "¢", "£", "¤", "¥", "¦", "§", "¨", "©", "ª", "«", "¬", "", "®", "¯", "°", "±", "²", "³", "´", "µ", "¶", "·", "¸", "¹", "º", "»", "¼", "½", "¾", "¿", "×", "÷", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï", "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "Þ", "ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
	  	// get input text
	  	$tempString = $str;

		// convert all HTML entities into Unicodes
		for ($i=0; $i<=count($entities); $i++) {
			$myVar = $entities[$i];
			$tempString = str_replace($myVar,$UCchar[$i],$tempString);
		}
	  
		// then convert all Unicodes into HTML entities
		for ($i=0; $i<=count($UCchar); $i++) {
			$myVar = $UCchar[$i];
			$tempString = str_replace($myVar,$entities[$i],$tempString);
		}

		// set output textarea
		return $tempString;
	}

	function htmlDecode($str) {
		//$entities = array("&amp;", "&quot;", "&apos;", "&lt;", "&gt;", "&nbsp;", "&iexcl;", "&cent;", "&pound;", "&curren;", "&yen;", "&brvbar;", "&sect;", "&uml;", "&copy;", "&ordf;", "&laquo;", "&not;", "&shy;", "&reg;", "&macr;", "&deg;", "&plusmn;", "&sup2;", "&sup3;", "&acute;", "&micro;", "&para;", "&middot;", "&cedil;", "&sup1;", "&ordm;", "&raquo;", "&frac14;", "&frac12;", "&frac34;", "&iquest;", "&times;", "&divide;", "&Agrave;", "&Aacute;", "&Acirc;", "&Atilde;", "&Auml;", "&Aring;", "&AElig;", "&Ccedil;", "&Egrave;", "&Eacute;", "&Ecirc;", "&Euml;", "&Igrave;", "&Iacute;", "&Icirc;", "&Iuml;", "&ETH;", "&Ntilde;", "&Ograve;", "&Oacute;", "&Ocirc;", "&Otilde;", "&Ouml;", "&Oslash;", "&Ugrave;", "&Uacute;", "&Ucirc;", "&Uuml;", "&Yacute;", "&THORN;", "&szlig;", "&agrave;", "&aacute;", "&acirc;", "&atilde;", "&auml;", "&aring;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&igrave;", "&iacute;", "&icirc;", "&iuml;", "&eth;", "&ntilde;", "&ograve;", "&oacute;", "&ocirc;", "&otilde;", "&ouml;", "&oslash;", "&ugrave;", "&uacute;", "&ucirc;", "&uuml;", "&yacute;", "&thorn;", "&yuml;");
		$entities = array("&lt;", "&gt;", "&amp;", "&quot;", "&apos;", "&nbsp;", "&iexcl;", "&cent;", "&pound;", "&curren;", "&yen;", "&brvbar;", "&sect;", "&uml;", "&copy;", "&ordf;", "&laquo;", "&not;", "&shy;", "&reg;", "&macr;", "&deg;", "&plusmn;", "&sup2;", "&sup3;", "&acute;", "&micro;", "&para;", "&middot;", "&cedil;", "&sup1;", "&ordm;", "&raquo;", "&frac14;", "&frac12;", "&frac34;", "&iquest;", "&times;", "&divide;", "&Agrave;", "&Aacute;", "&Acirc;", "&Atilde;", "&Auml;", "&Aring;", "&AElig;", "&Ccedil;", "&Egrave;", "&Eacute;", "&Ecirc;", "&Euml;", "&Igrave;", "&Iacute;", "&Icirc;", "&Iuml;", "&ETH;", "&Ntilde;", "&Ograve;", "&Oacute;", "&Ocirc;", "&Otilde;", "&Ouml;", "&Oslash;", "&Ugrave;", "&Uacute;", "&Ucirc;", "&Uuml;", "&Yacute;", "&THORN;", "&szlig;", "&agrave;", "&aacute;", "&acirc;", "&atilde;", "&auml;", "&aring;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&igrave;", "&iacute;", "&icirc;", "&iuml;", "&eth;", "&ntilde;", "&ograve;", "&oacute;", "&ocirc;", "&otilde;", "&ouml;", "&oslash;", "&ugrave;", "&uacute;", "&ucirc;", "&uuml;", "&yacute;", "&thorn;", "&yuml;");

		//$UCchar = array("&", "\"", "'", "<", ">", "", "¡", "¢", "£", "¤", "¥", "¦", "§", "¨", "©", "ª", "«", "¬", "", "®", "¯", "°", "±", "²", "³", "´", "µ", "¶", "·", "¸", "¹", "º", "»", "¼", "½", "¾", "¿", "×", "÷", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï", "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "Þ", "ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
		$UCchar = array("<", ">", "&", "\"", "'", "", "¡", "¢", "£", "¤", "¥", "¦", "§", "¨", "©", "ª", "«", "¬", "", "®", "¯", "°", "±", "²", "³", "´", "µ", "¶", "·", "¸", "¹", "º", "»", "¼", "½", "¾", "¿", "×", "÷", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï", "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "Þ", "ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
	  	// get input text
		$tempString = $str;
		
		// convert all Unicodes into HTML entities
		for ($i=0; $i<=count($entities); $i++) {
			$myVar = $entities[$i];
			$tempString = str_replace($myVar,$UCchar[$i],$tempString);
		}
		
		// set output textarea
		return $tempString;
	}	
	
	function xmlEncode($str) {
		$entities = array("&lt;", "&gt;", "&amp; ");
		$UCchar = array("<", ">", "& ");
	  	// get input text
	  	$tempString = $str;

		// convert all HTML entities into Unicodes
		for ($i=0; $i<=count($entities); $i++) {
			$myVar = $entities[$i];
			$tempString = str_replace($myVar,$UCchar[$i],$tempString);
		}
	  
		// then convert all Unicodes into HTML entities
		for ($i=0; $i<=count($UCchar); $i++) {
			$myVar = $UCchar[$i];
			$tempString = str_replace($myVar,$entities[$i],$tempString);
		}

		// set output textarea
		return $tempString;
	}

	function xmlDecode($str) {
		$entities = array("&lt;", "&gt;", "&amp; ");
		$UCchar = array("<", ">", "& ");
	  	// get input text
		$tempString = $str;
		
		// convert all Unicodes into HTML entities
		for ($i=0; $i<=count($entities); $i++) {
			$myVar = $entities[$i];
			$tempString = str_replace($myVar,$UCchar[$i],$tempString);
		}
		
		// set output textarea
		return $tempString;
	}

function array_change_key_case_unicode($arr, $c = CASE_LOWER) { 
	if (empty($arr)) return $arr;
	$c = ($c == CASE_LOWER) ? MB_CASE_LOWER : MB_CASE_UPPER;
	foreach ($arr as $k => $v) {
		$ret[mb_convert_case($k, $c, "UTF-8")] = mb_check_encoding($v, 'UTF-8')?utf8_decode($v):$v; 
	}
	return $ret;
}		

function array_change_key_case_recursive($arr)
{
    return array_map(function($item){
        if(is_array($item))
            $item = array_change_key_case_recursive($item);
        return $item;
    },array_change_key_case($arr));
}

function recursiveDelete($str){
	if(is_file($str)){
		return @unlink($str);
	}elseif(is_dir($str)){
		$scan = glob(rtrim($str,'/').'/*');
		foreach($scan as $index=>$path){
			recursiveDelete($path);
		}
		return @rmdir($str);
	}
}
?>