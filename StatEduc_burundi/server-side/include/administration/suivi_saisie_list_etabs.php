<?php set_time_limit(0);
    
	include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
	include_once $GLOBALS['SISED_PATH_LIB'] . 'controle.inc.php';
	require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
	require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/suivi_saisie_batch.class.php';

	lit_libelles_page('/suivi_saisie.php');

	$tab_users_run = $_SESSION['suivi_saisie']['tab_users_run'];
	$tab_ctrls_run = $_SESSION['suivi_saisie']['tab_ctrls_run'];
	$val_choix_affich = $_SESSION['suivi_saisie']['val_choix_affich'];
	
	$themes_by_ord  = 	$GLOBALS['theme_manager']->list; 
	$themes_by_id	=	array();
	
	foreach( $themes_by_ord  as $ord => $theme){
		$themes_by_id[$theme['ID']] = $theme ; 
	}
	$premier_theme=$themes_by_ord[0]['ID_THEME_SYSTEME'];
	$tot_etabs_saisis = 0 ;
	$tot_users_saisis = 0 ;
	
	if(is_array($tab_users_run)){//Si existe utilisateurs
		
		$html="<div id='table_scroll' style='display:inline' class='table_scroll'>";
		$html.="<table class = 'no_border' border='1'  width='98%'>\n";
		//$html.="<caption>".recherche_libelle_page('CaptionCtrl')."</caption>";
		//$html.="<thead class='fix_titre_table'>\n";
		$html.="<tr style='background: #CCCCCC'>\n";
		$html.="<td align='center' nowrap><div  align='center'><u>".htmlentities(recherche_libelle_page('User'))."</u>#nb_users_found#</div></td>\n";
		if($val_choix_affich == "par_liste"){
			$html.="<td align='center' nowrap><div  align='center'><u>".htmlentities(recherche_libelle_page('EtabSuivi'))."</u>#nb_ecoles_found#</div></td>\n";
		}
		$tab_ctrls_id = array();
		foreach($tab_ctrls_run as $id_crit => $crit){
			$tab_ctrls_id[] = $id_crit;
			$html.="<td nowrap><div align='center'><u>".$crit."</u></div></td>\n";
		}
		$html.="</tr>\n";
		//$html.="</thead>\n";
		
		//$html.="<tbody class='contenu_tabl'>\n";
		if(isset($GLOBALS['PARAM']['FILTRE']) && $GLOBALS['PARAM']['FILTRE']==true){
			$req_etabs_users_actions	= "SELECT DICO_TRACE.CODE_ETABLISSEMENT, DICO_TRACE.CODE_USER, DICO_TRACE.NOM_USER, Count(DICO_TRACE.ACTION) AS NB_ACTIONS
											FROM DICO_TRACE
											WHERE DICO_TRACE.CODE_SECTEUR=".$_SESSION['secteur']." AND DICO_TRACE.CODE_ANNEE=".$_SESSION['annee']." AND DICO_TRACE.CODE_FILTRE=".$_SESSION['filtre']."
											GROUP BY DICO_TRACE.CODE_ETABLISSEMENT, DICO_TRACE.CODE_USER, DICO_TRACE.NOM_USER
											ORDER BY DICO_TRACE.CODE_ETABLISSEMENT, Count(DICO_TRACE.ACTION) DESC;";
		}else{
			$req_etabs_users_actions	= "SELECT DICO_TRACE.CODE_ETABLISSEMENT, DICO_TRACE.CODE_USER, DICO_TRACE.NOM_USER, Count(DICO_TRACE.ACTION) AS NB_ACTIONS
											FROM DICO_TRACE
											WHERE DICO_TRACE.CODE_SECTEUR=".$_SESSION['secteur']." AND DICO_TRACE.CODE_ANNEE=".$_SESSION['annee']."
											GROUP BY DICO_TRACE.CODE_ETABLISSEMENT, DICO_TRACE.CODE_USER, DICO_TRACE.NOM_USER
											ORDER BY DICO_TRACE.CODE_ETABLISSEMENT, Count(DICO_TRACE.ACTION) DESC;";
		}
		//echo $req_etabs_users_actions;
		$tab_etabs_users_actions = $GLOBALS['conn_dico']->GetAll($req_etabs_users_actions);
		$tab_etabs_users = array();
		$liste_etabs_saisis = array();
		foreach($tab_etabs_users_actions as $etab_user_action){
			if(is_array($_SESSION['suivi_saisie']['tab_etabs_run'])){
				if(!in_array($etab_user_action['CODE_ETABLISSEMENT'],$liste_etabs_saisis) && in_array($etab_user_action['CODE_ETABLISSEMENT'],$_SESSION['suivi_saisie']['tab_etabs_run'])){
					$liste_etabs_saisis[] = $etab_user_action['CODE_ETABLISSEMENT'];
					$tab_etabs_users[$etab_user_action['CODE_USER']][] = $etab_user_action['CODE_ETABLISSEMENT'];
				}
			}/*else{
				if(!in_array($etab_user_action['CODE_ETABLISSEMENT'],$liste_etabs_saisis)){
					$liste_etabs_saisis[] = $etab_user_action['CODE_ETABLISSEMENT'];
					$tab_etabs_users[$etab_user_action['CODE_USER']][] = $etab_user_action['CODE_ETABLISSEMENT'];
				}
			}*/
		}
		// === Session 51 : Bloc additionnel pour les agents de collecte mobile (CODE_GROUPE=4) ===
		// Les agents classiques ont leurs etablissements dans $tab_etabs_users via DICO_TRACE (ci-dessus).
		// Les agents mobiles n'ecrivent PAS dans DICO_TRACE mais dans DATA_SAVING_LOGS.
		// Ce bloc interroge DATA_SAVING_LOGS pour chaque agent mobile selectionne,
		// puis injecte leurs etablissements actifs dans $tab_etabs_users — meme structure.
		foreach($tab_users_run as $_mobile_user){
			if(!isset($_mobile_user['CODE_GROUPE']) || (int)$_mobile_user['CODE_GROUPE'] !== 4) continue;
			// Agent mobile : requete sur DATA_SAVING_LOGS
			// CODE_USER dans DATA_SAVING_LOGS = NOM_USER (login string) d'ADMIN_USERS
			// On filtre sur STATUT_OPERATION='OKSAVE' pour ne compter que les soumissions reussies
			if(isset($GLOBALS['PARAM']['FILTRE']) && $GLOBALS['PARAM']['FILTRE']==true){
				$_req_mobile_etabs = "SELECT DISTINCT DATA_SAVING_LOGS.CODE_ECOLE AS CODE_ETABLISSEMENT
									FROM DATA_SAVING_LOGS
									WHERE DATA_SAVING_LOGS.CODE_USER='".$_mobile_user['NOM_USER']."'
									AND DATA_SAVING_LOGS.CODE_SECTEUR=".$_SESSION['secteur']."
									AND DATA_SAVING_LOGS.CODE_ANNEE=".$_SESSION['annee']."
									AND DATA_SAVING_LOGS.CODE_PERIODE=".$_SESSION['filtre']."
									AND DATA_SAVING_LOGS.STATUT_OPERATION='OKSAVE';";
			}else{
				$_req_mobile_etabs = "SELECT DISTINCT DATA_SAVING_LOGS.CODE_ECOLE AS CODE_ETABLISSEMENT
									FROM DATA_SAVING_LOGS
									WHERE DATA_SAVING_LOGS.CODE_USER='".$_mobile_user['NOM_USER']."'
									AND DATA_SAVING_LOGS.CODE_SECTEUR=".$_SESSION['secteur']."
									AND DATA_SAVING_LOGS.CODE_ANNEE=".$_SESSION['annee']."
									AND DATA_SAVING_LOGS.STATUT_OPERATION='OKSAVE';";
			}
			$_mobile_etabs_actions = $GLOBALS['conn_dico']->GetAll($_req_mobile_etabs);
			if(is_array($_mobile_etabs_actions)){
				foreach($_mobile_etabs_actions as $_mobile_etab){
					$_code_etab_mob = $_mobile_etab['CODE_ETABLISSEMENT'];
					// Filtrer : conserver uniquement les etablissements du perimetre selectionne
					if(is_array($_SESSION['suivi_saisie']['tab_etabs_run']) &&
					   in_array($_code_etab_mob, $_SESSION['suivi_saisie']['tab_etabs_run'])){
						if(!isset($tab_etabs_users[$_mobile_user['CODE_USER']]))
							$tab_etabs_users[$_mobile_user['CODE_USER']] = array();
						if(!in_array($_code_etab_mob, $tab_etabs_users[$_mobile_user['CODE_USER']])){
							$tab_etabs_users[$_mobile_user['CODE_USER']][] = $_code_etab_mob;
						}
					}
				}
			}
		}
		// === Fin bloc agents mobiles ===
		foreach($tab_users_run as $user){
			
			$tab_etabs_user_run = array();
			if(isset($tab_etabs_users[$user['CODE_USER']]) && is_array($tab_etabs_users[$user['CODE_USER']])){
				$tab_etabs_user_run = $tab_etabs_users[$user['CODE_USER']];
			}
			
			if(!isset($classe_fond)) {
				$classe_fond = 'ligne-paire';
			} else {
				if($classe_fond == 'ligne-paire') {
					$classe_fond = 'ligne-impaire';
				} else {
					$classe_fond = 'ligne-paire';
				}
			}
			
			$td_etab = 0 ;
			
			$html .= "<tr>\n";
			if($val_choix_affich == "par_liste") $r2=count($tab_etabs_user_run)+1; else $r2=1;
			$rowspan_etab="'".$r2."'";
			//echo "<br>$r2";
			$html .= "<td nowrap class='".$classe_fond."' id='".$classe_fond.'_'.$tot_etabs_saisis."_".$td_etab++."' rowspan=$rowspan_etab style='vertical-align:middle; text-align:left'>\n";
			$html .= "<b>".htmlentities($user['NOM_USER'])."</b>";
			$html .= "</td>\n";
			if($val_choix_affich == "par_liste") $html .= "</tr>\n";
			
			//Affichage par nombre etablissements saisis par l'utilisateur courant
			if($val_choix_affich == "par_nbre"){
				$_SESSION['suivi_saisie']['liste_etabs_user'] = $tab_etabs_user_run;
				$tab_criteres = array();
				$suivi_saisie	=	new suivi_saisie('', $_SESSION['langue'], $tab_etabs_user_run, $_SESSION['annee'], $_SESSION['filtre'], false);
				if(is_array($suivi_saisie->tab_regles_suivi_saisie) && count($suivi_saisie->tab_regles_suivi_saisie) > 0){	
					$tab_criteres  = $suivi_saisie->tab_regles_suivi_saisie;
					$exist_ctrl =  true;
				}
				
				//$html .= "<tr>\n";
				//echo "<pre>";
				//print_r($tab_criteres);
				foreach($tab_criteres as $ctrl_id => $regle_suivi_saisie){	
					if(in_array($ctrl_id,$tab_ctrls_id)){
						$valeur = round($regle_suivi_saisie['val_regle']);
						$html .= "<td   class='".$classe_fond."' id='".$classe_fond.'_'.$tot_etabs_saisis."_".$td_etab++."' nowrap>\n";
						$html .= "<span class='bl_gr'>".$valeur.'</span>';
						$html .= "</td>\n";
					}
				}
				$html .= "</tr>\n";
			}//Fin Affichage par nombre etablissements saisis par l'utilisateur courant
			
			//Affichage par liste etablissements saisis par l'utilisateur courant
			elseif($val_choix_affich == "par_liste"){
				foreach($tab_etabs_user_run as $etab){
					//Recup code admin si existant
					$get_code_admin = '';
					if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
						$get_code_admin	= ' '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' as code_admin, ' ;
					}
					//Recherche le code_regroupement � partir du code_etab
					$requete    = 'SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' as nom_etab,'.$get_code_admin
											.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' as code_regroup 
								   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].', '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'
								   WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
										AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='. $etab.';';
					//et mise en variable
					$regroup_etab = $GLOBALS['conn']->GetAll($requete);
					$code_regroup = $regroup_etab[0]['CODE_REGROUP'];
					$nom_etab = $regroup_etab[0]['NOM_ETAB'];
					if($get_code_admin<>'') $code_admin = $regroup_etab[0]['CODE_ADMIN']; else $code_admin = '';
				
					//Recherche de la chaine � partir du code_regroupement et du secteur
					$requete ='SELECT '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
									FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['HIERARCHIE'].','.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 
									WHERE '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
									AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
									AND  '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$code_regroup.'
									AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'=1
									AND  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'];
					//et mise en variable
					$chaine_etab = $GLOBALS['conn']->GetOne($requete);
					
					//Recherche du nombre de niveaux de la chaine
					$requete ='SELECT COUNT(*)
								FROM '.$GLOBALS['PARAM']['HIERARCHIE'].'
								WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$chaine_etab;
					$niveau_ch = $GLOBALS['conn']->GetOne($requete)-1;
					
					$arbre = new arbre($chaine_etab);
					
					$code_etab = $etab;
					$tab_criteres = array();
					$suivi_saisie	=	new suivi_saisie('', $_SESSION['langue'], $code_etab, $_SESSION['annee'], $_SESSION['filtre'], false);
					if(is_array($suivi_saisie->tab_regles_suivi_saisie) && count($suivi_saisie->tab_regles_suivi_saisie) > 0){	
						$tab_criteres  = $suivi_saisie->tab_regles_suivi_saisie;
						$exist_ctrl =  true;
					}
						
					$hierarchie = $arbre->getparentsid($niveau_ch,$code_regroup,$chaine_etab);
					
					$hierarchie_regroup = '';
					foreach($hierarchie as $i=>$h) {
						$hierarchie_regroup .= $h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
						if($i<(count($hierarchie)-1)) {
							$hierarchie_regroup .= '<b> / </b>';
						}
					}
					
					$html .= "<tr>\n";
					$html .= "<td nowrap class='".$classe_fond."' id='".$classe_fond.'_'.$tot_etabs_saisis."_".$td_etab++."' style='vertical-align:middle; text-align:left'>\n";
					if(isset($_SESSION['type_ent_stat']))
						$html .= "<a href='questionnaire.php?theme=".$premier_theme."&code_etab=".$code_etab."&type_ent_stat=".$_SESSION['type_ent_stat']."' target='theme1'><b>".replaceAccents($nom_etab)."</b></a>&nbsp;<em>(<b>".$code_admin."</b>)</em><br/>";
					else
						$html .= "<a href='questionnaire.php?theme=".$premier_theme."&code_etab=".$code_etab."' target='theme1'><b>".replaceAccents($nom_etab)."</b></a>&nbsp;<em>(<b>".$code_admin."</b>)</em><br/>";
					$html .= "<em>(".replaceAccents($hierarchie_regroup).")</em>";
					$html .= "</td>\n";
					//echo "<pre>";
					//print_r($tab_criteres);
					foreach($tab_criteres as $ctrl_id => $regle_suivi_saisie){	
						if(in_array($ctrl_id,$tab_ctrls_id)){
							$valeur = round($regle_suivi_saisie['val_regle']);
							$html .= "<td   class='".$classe_fond."' id='".$classe_fond.'_'.$tot_etabs_saisis."_".$td_etab++."' nowrap>\n";
							$html .= "<span class='bl_gr'>".$valeur.'</span>';
							$html .= "</td>\n";
						}
					}
					$html .= "</tr>\n";
					$tot_etabs_saisis++;
				}
			}
			$tot_users_saisis++;
		}//fin parcours utilisateurs
			
		//$html     .='</tbody>';
		$html     .='</table>';
		$html	  .='</div>';
		
		$html = str_replace('#nb_users_found#', '<span class=\'alert\'> &nbsp; : '.$tot_users_saisis.' </span>', $html);
		$html = str_replace('#nb_ecoles_found#', '<span class=\'alert\'> &nbsp; : '.$tot_etabs_saisis.' '.htmlentities(recherche_libelle_page('found')).' </span>', $html);
		$html = str_replace('#val_nb_ecoles#', $tot_etabs_saisis ,$html);
		
		$_SESSION['table_controle']=$html;
			
		
	}//Fin Si existe utilisateurs
	
	if( isset($exist_ctrl) && ($exist_ctrl == true) ){
	   $html .= "<div style='position: relative; top:235px'>
		<br/>
		<table align='center'>
			<tr>
				<td align='center' nowrap='nowrap'>
					<INPUT  style='width:120px;'  type='button' value='".htmlentities(recherche_libelle_page('Export'))."' onClick='OpenPopupExportData();'>
				</td>
				<td align='center' nowrap='nowrap'>
					<INPUT  style='width:120px;'  type='button' value='".htmlentities(recherche_libelle_page('OpenPopup'))."' onClick='OpenPopupData();'>
				</td>
			</tr>
		</table>
		</div>";
	}
	
	echo $html;
	unset($_SESSION['suivi_saisie']);
?>

