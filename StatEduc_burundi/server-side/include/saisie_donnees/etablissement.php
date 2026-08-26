<?php unset($_SESSION['reg_parents']);
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
lit_libelles_page(__FILE__);

	set_time_limit(0);
	
	//print_r($_GET);die();
	if ( isset($_GET['code_admin']) && $code_admin_not_found == true) {
		print '<span style="color: #FF0000;	font-weight: bold;">'.$_GET['code_admin'].'</span> : <span style="color: #FF0000;">'.recherche_libelle_page('CodAdmInex').'</span><BR>';
		
	}elseif ( isset($_GET['code_etab_rech']) && $code_etab_not_found == true) {
		print '<span style="color: #FF0000;	font-weight: bold;">'.$_GET['code_etab_rech'].'</span> : <span style="color: #FF0000;">'.recherche_libelle_page('CodEtaInex').'</span><BR>';
	}
	
	
	function get_all_hierarchie_etabs( $from_list_etab,  $types_hierar ){
		$types_etabs = array();
		$pos_types_hierar = array();
		
		$fils_de_type = array();
		$est_un_fils = array();
		$return = array() ;
		
		$champ_type_hierarch = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'] ;
		
		foreach( $from_list_etab as $ord_etab => $etab ){
			$types_etabs[$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']]] = $etab[$champ_type_hierarch] ;
		}
		
		foreach( $types_hierar as $i_type_fils => $type_fils ){
			$pos_types_hierar[$type_fils] = $i_type_fils ;
		}
		
		foreach( $from_list_etab as $ord_etab => $etab ){
			if( $etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']] <> '' ){
				$type_etab_fils = $etab[$champ_type_hierarch] ;
				$type_etab_pere = $types_etabs[$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']]] ;
				
				$pos_type_etab = $pos_types_hierar[$type_etab_fils] ;
				
				if( ($pos_types_hierar[$type_etab_fils] - $pos_types_hierar[$type_etab_pere]) == 1 ){
					$tabul = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ;
					$decal = $tabul ;
					if( $pos_types_hierar[$type_etab_fils] > 1 ){
						$decal = '' ;
						for( $i = 1 ; $i <= $pos_types_hierar[$type_etab_fils] ; $i++ ){
							$decal .= $tabul ;
						}
					}
					$etab['ETAB_TABUL_HIERARCHIE'] = $decal ;
					$fils_de_type[$pos_type_etab][$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']]][] = $etab ;
					$est_un_fils[$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']]] = 'oui' ; 
					
				}
			}
		}
		$return['fils_de_type'] = $fils_de_type ;
		$return['est_un_fils'] 	= $est_un_fils ;
		return($return);
	}
	
	function get_hierarchie_etabs( $from_list_etab,  $types_hierar ){
		
		$champ_type_hierarch = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'] ;
		$etab_traites 	= array();
		$new_list_etab	= array();
		
		$tab_hierar = get_all_hierarchie_etabs( $from_list_etab,  $types_hierar ) ;
		//echo'<pre>';
		//print_r($tab_hierar);
		
		for( $i_type_fils = 1 ; $i_type_fils < count($types_hierar) ; $i_type_fils++ ){
			foreach( $from_list_etab as $ord_etab => $etab ){
				if( !_est_ds_tableau($etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']], $etab_traites)){
					//$pos_type_etab = $pos_types_hierar[$etab[$champ_type_hierarch]] ;
					$curr_fils_etab = $tab_hierar['fils_de_type'][$i_type_fils][$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']]] ;
					$est_un_fils 	= $tab_hierar['est_un_fils'][$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']]] ;
					if( is_array($curr_fils_etab) && count($curr_fils_etab) ){
						//die('ICI');
						$new_list_etab[] = $etab ;
						$etab_traites[] = $etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] ;
						foreach( $curr_fils_etab as $ord_etab_fils => $etab_fils ){
							$new_list_etab[] = $etab_fils ;
							$etab_traites[] = $etab_fils[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] ;
						}
					}else if( $est_un_fils == 'oui' ){
						//continue;
					}else{
						$new_list_etab[] = $etab ;
						$etab_traites[] = $etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] ;
					}
				}
			}
		}
		return($new_list_etab);
	}
	
	switch($_GET['val']) {
	case 'liste_etablissement':{

		$db = $GLOBALS['conn'];
		
		// Recherche des regroupements sélectionnés par click dans l'arbre
		$list_code_reg = array();
		$list_nom_reg = array();
		
		$liste_etab_html       ='';
		
		if ($_GET['nom_regroup']) {
		
			$arbre = new arbre($_SESSION['chaine']);
			
			$_SESSION['code_regroupement'] = $_GET['code_regroup'];
			$_SESSION['nom_regroupement'] = $_GET['nom_regroup'];
			
			//création de la liste des coderegs enfants
			$_SESSION['list_code_reg_last'] = array();//Modif Hebie : recuperation des regroupements de dernier niveau dans la chaine selectionnée
			$list_code_reg = $arbre->getchildsid(substr($_SESSION['nom_regroupement'],1),$_SESSION['code_regroupement'],$_SESSION['chaine']);
			 // Modif Bass depuis Niger
			$crit_in = array();
			foreach($_SESSION['list_code_reg_last'] as $i_code_reg => $code_reg){
				/*if( !in_array( $code_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], $crit_in) ){
					$crit_in[] = $code_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] ;
				}*/
				//Modif Hebie : recuperation des regroupements de dernier niveau dans la chaine selectionnée
				if( !in_array( $code_reg, $crit_in) ){
					$crit_in[] = $code_reg ;
				}
			}
			
			$where               = 'E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ( '.implode(', ',$crit_in).' )';
			if(isset($_SESSION['fixe_regs'][0]) && $_SESSION['fixe_regs'][0]<> '') 
				$where               = 'E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ( '.implode(', ',$crit_in).' ) AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['ETABLISSEMENT'].' IN ( '.$_SESSION['fixe_regs'][0].' )';
			// Fin Modif Bass depuis Niger
			$with_code_admin =  '';
			if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) && ($GLOBALS['PARAM']['CONCAT_CODE_ADMIN']) ){
				$with_code_admin = ', E.'.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'] ;
			}

			$with_etab_hierarchie = '';
			$exist_rattach_etab_annee = false;
			if( ($GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] <> '') && ($GLOBALS['PARAM']['TYPE_RATTACHEMENT'] <> '') ){
				if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) 
					&& exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
					
					$with_etab_hierarchie = ' , E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' , E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] ;
					//Modif Hebie pr filtrer certain type d'entité collectée par année
					$from_rattach_etab_annee = array();
					$where_rattach_etab_annee = array();
					$req_etab_hierar    = ' SELECT '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
											FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
											ORDER BY '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'';
					$res_types_hierar = $db->GetAll($req_etab_hierar);
					if(isset($GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']) && count($GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']) > 0){
						if(is_array($res_types_hierar)){
							foreach( $res_types_hierar as $i_res => $res ){
								if(isset($GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']) && $GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] <> ''){
									$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] = ' , '.$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]];
									$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]]  = ' AND E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
									$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]]  .= ' AND '.$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'];
								}else{
									$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] = '';
									$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]]  = '';
								}
							}
							if(count($from_rattach_etab_annee)>0){
								$exist_rattach_etab_annee = true;
								$cpt_types_hierar = 0;
								foreach( $res_types_hierar as $i_res => $res ){
									if($cpt_types_hierar == 0){
										$requete = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'] . $with_code_admin . $with_etab_hierarchie . '
													FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R '.$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].' 
													WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
													AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'
													AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = '.$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']].'
													ORDER BY E.'.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
									}else{
										$requete .= ' UNION SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'] . $with_code_admin . $with_etab_hierarchie . '
													FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R '.$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].' 
													WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
													AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'
													AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = '.$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']].'
													ORDER BY E.'.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
									}
									$cpt_types_hierar++;
								}
							}
						}
					}else{
						if(is_array($res_types_hierar)){
								$cpt_types_hierar = 0;
								foreach( $res_types_hierar as $i_res => $res ){
									if($cpt_types_hierar == 0){
										$requete = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'] . $with_code_admin . $with_etab_hierarchie . '
													FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R '.' 
													WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
													AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].'
													AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = '.$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']].'
													ORDER BY E.'.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
									}else{
										$requete .= ' UNION SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'] . $with_code_admin . $with_etab_hierarchie . '
													FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R '.' 
													WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
													AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].'
													AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = '.$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']].'
													ORDER BY E.'.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
									}
									$cpt_types_hierar++;
								}
								//echo $requete;
						}
					}
					//Fin Modif Hebie
				}
			}else{
				$requete        = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'] . $with_code_admin . $with_etab_hierarchie . '
									FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R  
									WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
									AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].'
									ORDER BY E.'.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
			}
			$liste_etab = $db->GetAll($requete);
			if( trim($with_etab_hierarchie) <> '' ){
			
				$req_etab_hierar    = ' SELECT '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
										FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
										ORDER BY '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'';
			
				$res_types_hierar = $db->GetAll($req_etab_hierar);
				$types_hierar  = array();
				if( is_array($res_types_hierar) ){
					foreach( $res_types_hierar as $i_res => $res ){
						$types_hierar[] = $res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']];
					}
				}
				$liste_etab = get_hierarchie_etabs( $liste_etab,  $types_hierar ) ;
					
			}
			if(is_array($liste_etab)){ 
				$nb_etabs_reg = count($liste_etab);
			}
			//création de la liste des coderegs parents
			$list_code_reg = $arbre->getparentsid(substr($_SESSION['nom_regroupement'],1),$_SESSION['code_regroupement'],$_SESSION['chaine']);

			$parent_reg = $list_code_reg[count($list_code_reg)-1][$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
			
			$liste_etab_html .= "<B>".$nb_etabs_reg.' '.recherche_libelle_page(EtabPour).' '.$parent_reg." :<BR><BR>"."</B>";
			
			$liste_etab_html .= '<blockquote>' . "\n";
			$_SESSION['next_etab'] = array() ;
			if (is_array($liste_etab)){
				foreach ($liste_etab as $i_etab => $row) {
					$nom_link = trim($row[$GLOBALS['PARAM']['NOM_ETABLISSEMENT']]) ;
					if( trim($with_code_admin) <> '' ){
						//die($row[$GLOBALS['PARAM']['CODE_ADMINISTRATIF']]);
						(trim($row[$GLOBALS['PARAM']['CODE_ADMINISTRATIF']]) <> '') ? ($pls_code_adm = ' &nbsp;&nbsp; <em>('.$row[$GLOBALS['PARAM']['CODE_ADMINISTRATIF']].')</em>') : ($pls_code_adm = '') ;
					}
					$decal_etab = '';
					if( isset($row['ETAB_TABUL_HIERARCHIE']) and (trim($row['ETAB_TABUL_HIERARCHIE']) <> '') ){
						$decal_etab = $row['ETAB_TABUL_HIERARCHIE'] ;
					}
						
					if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $row[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']] <> "")
						$get_type_ent_stat = '&type_ent_stat='.$row[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']];
					elseif(isset($_GET['type_ent_stat']) && $_GET['type_ent_stat']<>"") $get_type_ent_stat = '&type_ent_stat='.$_GET['type_ent_stat'];
					else $get_type_ent_stat = "";
					if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $row[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']] <> "")
						$liste_etab_html .= $decal_etab . '<a href="questionnaire.php?theme='.${'theme_manager'.$row[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]}->recherche_theme_def().'&code_etab='.$row[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']].$get_type_ent_stat.'" target="_top">'.$nom_link.'</a>'.$pls_code_adm.'<br />';
					else
						$liste_etab_html .= $decal_etab . '<a href="questionnaire.php?theme='.$theme_manager->recherche_theme_def().'&code_etab='.$row[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']].$get_type_ent_stat.'" target="_top">'.$nom_link.'</a>'.$pls_code_adm.'<br />';
					if( $i_etab > 0 ){
						$code_prec_etab = $liste_etab[$i_etab - 1][$GLOBALS['PARAM']['CODE_ETABLISSEMENT']];
						//if( $code_prec_etab == $_SESSION['code_etab']){
							//$_SESSION['next_etab'][$code_prec_etab]['code_etab'] 	= 	$row[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']];
							if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $row[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']] <> "")
								$_SESSION['next_etab'][$code_prec_etab]['code_etab'] 	=	'theme='.${'theme_manager'.$row[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]}->recherche_theme_def().'&code_etab='.$row[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']].$get_type_ent_stat;
							else
								$_SESSION['next_etab'][$code_prec_etab]['code_etab'] 	=	'theme='.$theme_manager->recherche_theme_def().'&code_etab='.$row[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']].$get_type_ent_stat;
							$_SESSION['next_etab'][$code_prec_etab]['nom_etab'] 	= 	$nom_link ;
							$_SESSION['next_etab'][$code_prec_etab]['code_admin'] 	= 	$pls_code_adm ;
						//}
					}
				}
			}
			
			$liste_etab_html .= '</blockquote>' . "\n";

		} else {
		
			$liste_etab_html .= '&nbsp;' . "\n";
		
		}

?>
<style>
body {
background: none;
}
table {
border: 0px;
}
</style>
<table width="100%" height="100%">
<tr>
<td>
<?php echo $liste_etab_html;

?></td>
</tr>
</table>
<?php
	}
	break;

	default:{

		/* construction du système */

		$nom_combo_systeme     = 'LIBELLE';
		$nom_code_systeme       =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
		$nom_combo_chaine      = get_champ_extraction($GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']);

		if ($_GET['secteur']) {

			$code_secteur        = $_GET['secteur'];            
			$_SESSION['secteur'] = $_GET['secteur'];
		} else if ($_SESSION['secteur']) {

			$code_secteur        = $_SESSION['secteur'];
			$_GET['secteur']     = $_SESSION['secteur'];

		} else {

			print 'Pb avec le secteur / Issue with sector';

		}
		$fonction = "toggle_arbo('$nom_combo_systeme','$nom_combo_chaine', 'secteur')";
		$systeme_html = create_combo($_SESSION['tab_secteur'], $nom_combo_systeme, $nom_code_systeme, $code_secteur,  $fonction);
		$systeme_html = str_replace('<SELECT','<SELECT style="width:200px;"',$systeme_html);
		/* construction de la chaine */
		
		$tab_chaine_secteur =  array();        
		if(isset($_SESSION['fixe_chaines']) && count((array)$_SESSION['fixe_chaines'][$_SESSION['secteur']])>0) set_tab_session('chaines', '');
		foreach ((array)$_SESSION['tab_chaines'] as $row) {
		
			if ($row[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $_SESSION['secteur']) {
			
				array_push($tab_chaine_secteur, $row);
			
			}
		
		}
		$nom_code_chaine       =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'];
		
		if ($_GET['chaine']) {
		
			$code_chaine        = $_GET['chaine'];
			$_SESSION['chaine'] = $_GET['chaine'];
		
		} else {
			$code_chaine        = $tab_chaine_secteur[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']] ;
			$_SESSION['chaine'] = $code_chaine;
		
		}

		$fonction="toggle_arbo('$nom_combo_systeme','$nom_combo_chaine', 'chaine')";
		
		$chaine_html = create_combo($tab_chaine_secteur, $nom_combo_chaine, $nom_code_chaine, $code_chaine, $fonction);
		$chaine_html = str_replace('<SELECT','<SELECT style="width:200px;"',$chaine_html);
		/* construction de l'arbre */
		$arbre = new arbre($code_chaine);
		$arbre->init($_SESSION['NB_NIVEAU_ARBO']);
		if(isset($_GET['type_ent_stat']) && $_GET['type_ent_stat']<>"")
			$arbre_html = $arbre->build('saisie_donnees.php?val=liste_etablissement&code_regroup=$code_regroup&nom_regroup=$nom_regroup&type_ent_stat='.$_GET['type_ent_stat'], 'liste_etablissement');
		else
			$arbre_html = $arbre->build('saisie_donnees.php?val=liste_etablissement&code_regroup=$code_regroup&nom_regroup=$nom_regroup', 'liste_etablissement');
		$arbre_html = str_replace('<select','<select style="width:200px;"',$arbre_html);
    
    //Ajout Arsène : nom du survey en cours
  $req_lib_survey = 'SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' AS lib_survey'.
                    ' FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].
                    ' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = '.$_SESSION['type_ent_stat'];
  $libelle_survey = $GLOBALS['conn']->GetOne($req_lib_survey);
  
?>

<style>
ol.menu img {cursor: pointer; width: 16px; height: 16px; margin-left: -20px; margin-bottom: -2px;}
ol.menu, ol.menu ol {text-align: left; list-style-type: none;}
ol.menu li {list-style-type: none; list-style-image: none; margin-left: -20px;}
ol.menu ol {display: none;}
</style>

<script type="text/Javascript">

function questionnaire_codeAdmin() {

	var value_code_admin = code_admin.value;
	parent.location.href= 'saisie_donnees.php?val=choix_etablissement&code_admin='+value_code_admin;

}
function questionnaire_codeEtab() {

	var value_code_etab_rech 	= code_etab_rech.value;
	parent.location.href= 'saisie_donnees.php?val=choix_etablissement&code_etab_rech='+value_code_etab_rech;

}

function toggle_arbo(combo_secteur, combo_chaine, from) {
	
	//var value_secteur = LIBELLE.options[LIBELLE.selectedIndex].value;
	//var value_chaine = LIBELLE_CHAINE.options[LIBELLE_CHAINE.selectedIndex].value;
	var chaine_eval = 'var value_secteur = '+combo_secteur+'.value;';
	eval(chaine_eval );
	if( from == 'chaine'){
		var chaine_eval = 'var value_chaine = '+combo_chaine+'.value;';
		eval(chaine_eval ); 
		location.href= '?chaine='+value_chaine+'&secteur='+value_secteur;
	}else {
		location.href= '?secteur='+value_secteur;
	}

}

function expand(n) {

	var node = n;
	
	while(node.nodeName != "OL") {
	
		node = node.nextSibling;
	
	}
	
	if(node.style.display == 'block') {
	
		node.style.display = 'none';
		n.src = '<?php echo $GLOBALS['SISED_URL_IMG']; ?>arbre/plus.gif';
	
	} else {
	
		node.style.display = 'block';
		n.src = '<?php echo $GLOBALS['SISED_URL_IMG']; ?>arbre/minus.gif';
		
	}

}

</script>

<SPAN class="systeme" title="systeme" >
<SPAN title="system" ><?php echo $systeme_html; ?></SPAN>
<SPAN title="period" ><?php echo $periode_html; ?></SPAN>
</SPAN>
<SPAN class="code_admin" style=" padding-top:2px">
<div align="center">
	<div style="width:10% ; float: left;">&nbsp;</div>
	<div style="width:40% ; float: left; padding-right:2px">
		<TABLE width="100%">
			<TR>
				<?php if($_SESSION['groupe']<>1) $readonly = " readonly "; else $readonly=''; ?>
				<TD nowrap="nowrap">  <?php echo recherche_libelle_page('CodeEtab'); ?> <INPUT <?php echo $readonly; ?> name="code_etab_rech" id="code_etab_rech" type="text"> <input id="btnAction0" name="btnAction0" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>suivant.php" onclick="questionnaire_codeEtab();">  </TD>
			</TR>
		</TABLE>
	</div>
	<div style=" width:40% ; float: left; padding-left:2px">
		<TABLE width="100%">
			<TR>
				<TD nowrap="nowrap">  <?php echo recherche_libelle_page('CodeAdmin'); ?> <INPUT name="code_admin" id="code_admin" type="text"> <input id="btnAction" name="btnAction" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>suivant.php" onclick="questionnaire_codeAdmin();">  </TD>
			</TR>
		</TABLE>
	</div>
	<div style=" width:10% ; float: left; ">&nbsp;</div>
</div>
</SPAN>

<SPAN class="chaine">
<?php echo $chaine_html; ?></SPAN>

<SPAN class="arbre">
<?php echo $arbre_html; ?></SPAN>

<SPAN class="etablissement">
<iframe name="liste_etablissement" src="saisie_donnees.php?val=liste_etablissement" width="100%" height="100%" marginwidth"0" marginheight="0"></iframe>
</SPAN>

<?php 
	}
	break;
}
//// bass : suppression établissement
//require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
if (isset($_GET['sup_etab'])){
	alert_sup_etab();
}
//// fin bass

?>