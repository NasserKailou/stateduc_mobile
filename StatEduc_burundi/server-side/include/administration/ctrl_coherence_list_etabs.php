<META http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<?php set_time_limit(0);
    
	include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
	include_once $GLOBALS['SISED_PATH_LIB'] . 'controle.inc.php';
	require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
	require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/controle_theme_batch.class.php';

	lit_libelles_page('/controle_theme.php');

	$tab_etabs_run = $_SESSION['ctrl_cohr']['tab_etabs_run'];
	$tab_ctrls_run = $_SESSION['ctrl_cohr']['tab_ctrls_run'];
	$batch_etabs_inc = array();
	
	//Permet de retrouver le numero de ligne d'une entrée dans un tableau
	function getNumLineCtrl($code_etab, $id_th, $val_sql){
		$id_theme = $id_th;
		if(!isset($_SESSION['id_theme_ctrl']) || $id_theme <> $_SESSION['id_theme_ctrl'] || $code_etab <> $_SESSION['id_etab_ctrl'] || $_SESSION['annee'] <> $_SESSION['id_annee_ctrl'] || $_SESSION['filtre'] <> $_SESSION['id_filtre_ctrl'] || $_SESSION['secteur'] <> $_SESSION['id_secteur_ctrl']){
			$_SESSION['id_theme_ctrl'] = $id_theme;
			$_SESSION['id_etab_ctrl'] = $code_etab;
			$_SESSION['id_annee_ctrl'] = $_SESSION['annee'];
			$_SESSION['id_filtre_ctrl'] = $_SESSION['filtre'];
			$_SESSION['id_secteur_ctrl'] = $_SESSION['secteur'];
			$code_etablissement = $code_etab;
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
			$_SESSION['gets_ctrl'] = '';
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
				if(array_search($value, $tab_array_column) != false){
					$num_lig_rech = array_search($value, $tab_array_column)+1;
					$nbre_enr_tot = count($tab_array_column);
					$nbre_lig_page = $_SESSION['curobj_theme']->nb_lignes;
					$num_lig_page = $num_lig_rech - 1;
					//echo "<br/>num_lig_rech:$num_lig_rech<br/>nbre_enr_tot:$nbre_enr_tot<br/>nbre_lig_page:$nbre_lig_page<br/>num_lig_page:$num_lig_page";
					if($nbre_enr_tot > $nbre_lig_page){
						if($num_lig_rech > $nbre_lig_page){
							$num_lig_page = ($num_lig_rech % $nbre_lig_page) - 1;
							$_SESSION['gets_ctrl'] = '&suite=true&debut='.(((int)($num_lig_rech/$nbre_lig_page))*$nbre_lig_page).'&ctrl=1&num_lig_page='.$num_lig_page.'&nbre_lig_page='.$nbre_lig_page;
						}else{
							if($num_lig_page >= 0)
								$_SESSION['gets_ctrl'] = '&ctrl=1&num_lig_page='.$num_lig_page.'&nbre_lig_page='.$nbre_lig_page;
							else
								$_SESSION['gets_ctrl'] = '&ctrl=1';
						}
					}else{
						if($num_lig_page >= 0)
							$_SESSION['gets_ctrl'] = '&ctrl=1&num_lig_page='.$num_lig_page.'&nbre_lig_page='.$nbre_lig_page;
						else
							$_SESSION['gets_ctrl'] = '&ctrl=1';
					}
				}
			}
			break;
		}
	}
	
	$tot_etabs_inc = 0 ;
	$tot_inc_found = 0 ;
	if(is_array($tab_etabs_run)){
		//$class="class='ligne-paire2'";
		$arbre = new arbre($_SESSION['ctrl_cohr']['id_chaine']);
		$requete ='SELECT COUNT(*)
					FROM '.$GLOBALS['PARAM']['HIERARCHIE'].'
					WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$_SESSION['ctrl_cohr']['id_chaine'];
		$niveau_ch = $GLOBALS['conn']->GetOne($requete)-1;
		
		$html="<div id='table_scroll' style='display:inline' class='table_scroll'>";
		//$html="<div id='table_scroll' style='display:inline'>";
		$html.="<table class = 'no_border' border='1'  width='100%'>\n";
		//$html.="<caption>".recherche_libelle_page('CaptionCtrl')."</caption>";
		//$html.="<thead class='fix_titre_table'>\n";
		$html.="<tr style='background: #CCCCCC'>\n";
		//$html.="<th align='center' nowrap><div  align='center'><u>".htmlentities(recherche_libelle_page('Etab'))."</u>#nb_ecoles_found#</div></th>\n";
		$html.="<td align='center' nowrap><div  align='center'><u>".htmlentities(recherche_libelle_page('Etab'))."</u>#nb_ecoles_found#</div></td>\n";
		$html.="<td COLSPAN='2' nowrap align='center'>####<u>".htmlentities(recherche_libelle_page('FormsComp'))."</u>#nb_incoherences#</td>\n";
		$html.="<td align='center'><u>".htmlentities(recherche_libelle_page('MsgAlert'))."</u></td>\n";
		$html.="</tr>\n";
		//$html.="</thead>\n";
		
		//$html.="<tbody class='contenu_tabl'>\n";
		foreach($tab_etabs_run as $etab){
			$r2 = 0;
			$code_etab = $etab['code_etab'];
			$code_type_ent_stat = $etab['code_type_ent_stat'];
			$themes_by_ord  = 	${'theme_manager'.$code_type_ent_stat}->list; 
			$themes_by_id	=	array();
			
			foreach( $themes_by_ord  as $ord => $theme){
				$themes_by_id[$theme['ID']] = $theme ; 
			}
			$premier_theme=$themes_by_ord[0]['ID_THEME_SYSTEME'];
			$tab_incoherences = array();
			if(count($tab_ctrls_run) == 1 && $tab_ctrls_run[0] == 'MISS_SCH'){
				$controle_theme	=	new controle_theme(0, $_SESSION['langue'], $code_etab, $_SESSION['annee'], $_SESSION['filtre'], false);
				$sql_regle = 'SELECT Count('.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].') AS CountOfCODE_SCHOOL FROM '.$GLOBALS['PARAM']['DONNEES_ETABLISSEMENT'].' WHERE (('.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].')='.$code_etab.') AND (('.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].')='.$_SESSION['annee'].')';
				$controle_theme->tab_regles_theme = array(0 => array('sql' => $sql_regle));
				$msg_assoc = (recherche_libelle_page('Missing_School')<>'')? recherche_libelle_page('Missing_School'):'Missing school';
				$School_Entered = (recherche_libelle_page('School_Entered')<>'')? recherche_libelle_page('School_Entered'):'School entered';
				$Zero = (recherche_libelle_page('Zero')<>'')? recherche_libelle_page('Zero'):'Zero';
				$controle_theme->tab_regles_theme_assoc = array(0 => array(0 => array('critere_assoc' => '>','sql_assoc' => 0,'msg_assoc' => $msg_assoc,'nom_regle_1' => $School_Entered,'nom_regle_2' => $Zero)));
				$controle_theme->controle_regles_theme();
				if(is_array($controle_theme->tab_regles_theme_assoc_not_ok) && count($controle_theme->tab_regles_theme_assoc_not_ok) > 0){	
					$r2 += count($controle_theme->tab_regles_theme_assoc_not_ok);
					$tab_incoherences[0]  = $controle_theme->tab_regles_theme_assoc_not_ok;
					$exist_inchr =  true;
				}
			}elseif(count($tab_ctrls_run) == 1 && $tab_ctrls_run[0] == 'NOT_LINKED_SCH'){
				$controle_theme	=	new controle_theme(0, $_SESSION['langue'], $code_etab, $_SESSION['annee'], $_SESSION['filtre'], false);
				$sql_regle = 'SELECT Count('.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].') AS CountOfCODE_SCHOOL FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' WHERE (('.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].')='.$code_etab.') ';
				$controle_theme->tab_regles_theme = array(0 => array('sql' => $sql_regle));
				$msg_assoc = (recherche_libelle_page('Not_Linked_School')<>'')? recherche_libelle_page('Not_Linked_School'):'Schools Not Located';
				$School_Linked = (recherche_libelle_page('School_Linked')<>'')? recherche_libelle_page('School_Linked'):'School located';
				$Zero = (recherche_libelle_page('Zero')<>'')? recherche_libelle_page('Zero'):'Zero';
				$controle_theme->tab_regles_theme_assoc = array(0 => array(0 => array('critere_assoc' => '>','sql_assoc' => 0,'msg_assoc' => $msg_assoc,'nom_regle_1' => $School_Linked,'nom_regle_2' => $Zero)));
				$controle_theme->controle_regles_theme();
				if(is_array($controle_theme->tab_regles_theme_assoc_not_ok) && count($controle_theme->tab_regles_theme_assoc_not_ok) > 0){	
					$r2 += count($controle_theme->tab_regles_theme_assoc_not_ok);
					$tab_incoherences[0]  = $controle_theme->tab_regles_theme_assoc_not_ok;
					$exist_inchr =  true;
				}
			}else{
				foreach($tab_ctrls_run as $ord_ctrl => $ctrl_id){
					//echo '<br>$alert='.false.', $id_theme='.$theme['ID'].', $langue='.$_SESSION['langue'].', $code_etablissement='.$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']].', $code_annee='.$_SESSION['annee'].', $code_filtre='.''.'';
					$controle_theme	=	new controle_theme($ctrl_id, $_SESSION['langue'], $code_etab, $_SESSION['annee'], $_SESSION['filtre'], false);
					if(is_array($controle_theme->tab_regles_theme_assoc_not_ok) && count($controle_theme->tab_regles_theme_assoc_not_ok) > 0){	
						$r2 += count($controle_theme->tab_regles_theme_assoc_not_ok);
						$tab_incoherences[$ctrl_id]  = $controle_theme->tab_regles_theme_assoc_not_ok;
						$exist_inchr =  true;
					}
				}
			}
			$r2 += 1;
			
			//die($r2);
			if($r2>1){
				
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
				
			
				$hierarchie = $arbre->getparentsid($niveau_ch, $etab['code_regroup'], $_SESSION['ctrl_cohr']['id_chaine']);
				
				$hierarchie_regroup = '';
			
				foreach($hierarchie as $i=>$h) {
			
					$hierarchie_regroup .= $h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
			
					if($i<(count($hierarchie)-1)) {
			
						$hierarchie_regroup .= '<b> / </b>';
			
					}
			
				}
			
				$html .= "<tr>\n";
				//$r2=count($controle_theme->tab_regles_theme_assoc_not_ok)+1;
				if($code_type_ent_stat <> "") $get_type_ent_stat = "&type_ent_stat=".$code_type_ent_stat;
				else $get_type_ent_stat = "";
				$rowspan_etab="'".$r2."'";
				$html .= "<td nowrap class='".$classe_fond."' id='".$classe_fond.'_'.$tot_etabs_inc."_".$td_etab++."' onClick='MiseEvidenceEtabCtrl(".$tot_etabs_inc.", #val_nb_ecoles#)' rowspan=$rowspan_etab style='vertical-align:middle; text-align:left'>\n";
				$html .= "<a href='questionnaire.php?theme=".$premier_theme."&code_etab=".$code_etab.$get_type_ent_stat."' target='theme1'><b>".(replaceAccents($etab['nom_etab']))."</b></a>&nbsp;<em>(<b>".$etab['code_admin']."</b>)</em><br/>";
				$html .= "<em>(".replaceAccents($hierarchie_regroup).")</em>";
				if($etab['nom_user']<>'') $html .= "<em>(<b>".replaceAccents(recherche_libelle_page('saisipar')).": ".replaceAccents($etab['nom_user'])."</b>)</em>";
				$html .= "</td>\n";
				$html .= "</tr>\n";
			
				foreach($tab_incoherences as $ctrl_id => $regles_theme_assoc_not_ok){	
					foreach($regles_theme_assoc_not_ok as $id_regle => $regles_assoc){
						
						if(is_array($regles_assoc) && count($regles_assoc)>0){
							$alert['ID_REGLE_THEME']=$id_regle;
							foreach($regles_assoc as $id_regle_assoc => $regle){
								$alert['ID_REGLE_THEME_ASSOC']=$id_regle_assoc;
								$alert['critere_assoc']=$regle['critere_assoc'];
								$alert['msg_alert']=replaceAccents($regle['msg_assoc']);
								$alert['sql_text']=$regle['sql_assoc'];
								
								$alert['form1']=replaceAccents($regle['nom_regle_1']);
								$alert['form2']=replaceAccents($regle['nom_regle_2']);
								
								$open_theme1=$themes_by_id[$regle['id_theme']]['ID_THEME_SYSTEME'];
								$open_theme2=$themes_by_id[$regle['id_theme_assoc']]['ID_THEME_SYSTEME'];
								
								$alert['val_champ1']=replaceAccents($regle['val_champ1']);
								$alert['val_champ2']=replaceAccents($regle['val_champ2']);
								
								getNumLineCtrl($code_etab, $regle['id_theme'], $regle['val_sql']);
							}
							$html .= "<tr>\n";
							$html .= "<td   class='".$classe_fond."' id='".$classe_fond.'_'.$tot_etabs_inc."_".$td_etab++."' onClick='MiseEvidenceEtabCtrl(".$tot_etabs_inc.", #val_nb_ecoles#)' nowrap>\n";
							//$html .= $alert['form1']."<br>".$alert['nom_champ1']." = ".$alert['val_champ1'];
							if(count($tab_ctrls_run) == 1 && $tab_ctrls_run[0] == 'MISS_SCH')
								$html .= "(".($alert['form1']).") = (<span class='bl_gr'>".$alert['val_champ1'].'</span>)';
							else
								$html .= "(<a href='questionnaire.php?theme=".$open_theme1."&code_etab=".$code_etab.$get_type_ent_stat.$_SESSION['gets_ctrl']."' target='theme1'>".($alert['form1'])."</a>) = (<span class='bl_gr'>".$alert['val_champ1'].'</span>)';
							$html .= "</td>\n";
							$html .= "<td  class='".$classe_fond."' id='".$classe_fond.'_'.$tot_etabs_inc."_".$td_etab++."' onClick='MiseEvidenceEtabCtrl(".$tot_etabs_inc.", #val_nb_ecoles#)' nowrap>\n";
							//$html .= $alert['form2']."<br>".$alert['nom_champ2']." = ".$alert['val_champ2'];
							if(preg_match("/^(0|([1-9][0-9]*))$/", $alert['sql_text']) || (count($tab_ctrls_run) == 1 && $tab_ctrls_run[0] == 'MISS_SCH'))
								$html .= "(".($alert['form2']).") = (<span class='rg_gr'>".$alert['val_champ2'].'</span>)';
							else
								$html .= "(<a href='questionnaire.php?theme=".$open_theme2."&code_etab=".$code_etab.$get_type_ent_stat.$_SESSION['gets_ctrl']."' target='theme1'>".($alert['form2'])."</a>) = (<span class='rg_gr'>".$alert['val_champ2'].'</span>)';
							$html .= "</td>\n";
							$html .= "<td width='25%' class='".$classe_fond."' id='".$classe_fond.'_'.$tot_etabs_inc."_".$td_etab++."' onClick='MiseEvidenceEtabCtrl(".$tot_etabs_inc.", #val_nb_ecoles#)' >\n";
							$html .= (substr($alert['msg_alert'], 0, 200));
							$html .= "</td>\n";
							$html .= "</tr>\n";
							
							$tot_inc_found++;
						}
					}
				}
				$tot_etabs_inc++;
				$batch_etabs_inc[] = $code_etab ;
			}
		}
		
		//$html     .='</tbody>';
		$html     .='</table>';
		$html	  .='</div>';
		
		$html_imput  = '';
		if( isset($GLOBALS['PARAM']['DO_ESTIMATION_BATCH']) and ($GLOBALS['PARAM']['DO_ESTIMATION_BATCH'] == true) and ($tot_etabs_inc > 0)  and ($_SESSION['groupe'] ==1 || $_SESSION['groupe'] ==2) ){
			$html = str_replace('####', '' ,$html);
			$_SESSION['batch_etabs_inc'] = array();
			$_SESSION['batch_etabs_inc'] = $batch_etabs_inc ;
			$html_imput = ' &nbsp; : &nbsp; ';
			$requete = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
			$list_ann = $GLOBALS['conn']->GetAll($requete);
							
			foreach ($list_ann as $i_ann => $ann){
				if($ann[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] == $_SESSION['annee']){
					$ord_curr_ann = $ann[$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] ; break ;
				}
			}
			krsort($list_ann);
			
			$html_imput .= '<div id="show_form_imput" style="position: relative; float: none"> <strong>'.replaceAccents(recherche_libelle_page('dataestim')).' </strong>  <a href="#" onClick="MM_displayLayers(\'form_imput\',\'\',\'block\'); MM_showHideLayers(\'show_form_imput\',\'\',\'hide\',\'hide_form_imput\',\'\',\'show\',\'form_imput\',\'\',\'show\',\'imput_running\',\'\',\'hide\')"><strong>>></strong></a></div>';
			$html_imput .= '<div id="hide_form_imput"> <strong>'.(recherche_libelle_page('dataestim')).' </strong>  <a href="#" onClick="MM_displayLayers(\'form_imput\',\'\',\'none\'); MM_showHideLayers(\'show_form_imput\',\'\',\'show\',\'hide_form_imput\',\'\',\'hide\',\'form_imput\',\'\',\'hide\',\'imput_running\',\'\',\'hide\')"><strong><<</strong></a></div>';
		 	
				$html_imput .='<div id="form_imput">
				<form  name="imput_formulaire">
				  <table id="tabl_imput">
					  <caption><strong> '.(recherche_libelle_page('estimtitl')).' </strong></caption>
					 <tr>
						  <td nowrap="nowrap">
							<input type="radio" name="action_imput" value="imputer" id="id_imputer"> '.(recherche_libelle_page('actionestim')).'&nbsp;</td>
						  <td nowrap="nowrap">&nbsp;'.(recherche_libelle_page('yeardata')).'
							<select name="annee_imput">';
								
								foreach ($list_ann as $i_ann => $ann){
									if($ann[$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] < $ord_curr_ann){
										$html_imput .= "<option value='".$ann[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]."'";
										$html_imput .= ">".$ann[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]."</option>";
									}
								}
						
					$html_imput .='</select></td>
					</tr>
						
						<tr>
						  <td colspan="2" nowrap="nowrap">
							<input type="radio" name="action_imput" value="lever_impute"  id="id_lever_impute">
							'.(recherche_libelle_page('cancallest')).' </td>
						</tr>
						<tr>
						  <td colspan="2" nowrap="nowrap">
							
							  <div align="right">
								<input type="hidden" name="cible_imput" value="batch_etabs">
								<input name="ok" type="button" onClick="MM_callJS(\'AlertImput(document.imput_formulaire.cible_imput.value, document.imput_formulaire.annee_imput.value)\')" value="'.(recherche_libelle_page('submit')).'">
								&nbsp;&nbsp;
								  <input name="annuler" type="button" onClick="MM_displayLayers(\'form_imput\',\'\',\'none\'); MM_showHideLayers(\'show_form_imput\',\'\',\'show\',\'hide_form_imput\',\'\',\'hide\',\'form_imput\',\'\',\'hide\')" value="'.(recherche_libelle_page('closeest')).'">
							  </div>
					 </td></table>
				  </form>
			  </div>
			  
				<div id="imput_running">
					
					<table width="100%">
					  <caption><strong> '.recherche_libelle_page('estimtitl').' </strong></caption>
					 <tr><td height="28" style="margin:0px">
						<div id="imput_progress"  align="center">
							<script language="JavaScript" type="text/javascript">
								var niv = 1;
								function barre(){
									if( (document.getElementById("pourcentage")) && (document.getElementById("progrbar")) ){
										if(niv > 400) niv = 1;
										var ch_eval = \'document.getElementById("progrbar2").style.width="\'+(niv / 4)+\'%";\';
										eval(ch_eval);
										niv++;
										setTimeout("barre()", 1);
									}
								}
							</script>
						
							<div id="pourcentage" class="aa" align="center">'.(recherche_libelle_page('isruuning')).'</div>
							<div id="progrbar" class="bb" align="center"></div>
						
							<script language="JavaScript" type="text/javascript">
								setTimeout("barre()", 1);	
							</script> 
						</div>
					 &nbsp;</td></tr>
				  </table>
				</div>
		 ';
		
		}else{
			$html = str_replace('####', '' ,$html);
		}
		
		$html_tmp = str_replace('#nb_ecoles_found#', '<span class=\'alert\'> &nbsp; : '.$tot_etabs_inc.' '.replaceAccents(recherche_libelle_page('found')).' </span>', $html);
		
		$html = str_replace('#nb_ecoles_found#', '<span class=\'alert\'> &nbsp; : '.$tot_etabs_inc.' '.replaceAccents(recherche_libelle_page('found')).' </span>' . $html_imput ,$html);
		$html = str_replace('#val_nb_ecoles#', $tot_etabs_inc ,$html);
		$html = str_replace('#nb_incoherences#', '<span class=\'alert\'> &nbsp; : '.$tot_inc_found.' '.replaceAccents(recherche_libelle_page('incoh')).' </span>',$html);
		
		$html_tmp = str_replace('#val_nb_ecoles#', $tot_etabs_inc ,$html_tmp);
		$html_tmp = str_replace('#nb_incoherences#', '<span class=\'alert\'> &nbsp; : '.$tot_inc_found.' '.replaceAccents(recherche_libelle_page('incoh')).' </span>',$html_tmp);
		$_SESSION['table_controle']=$html_tmp;
		
	}
	if( isset($exist_inchr) && ($exist_inchr == true) ){
	   $html .= "<div style='position: relative; top:275px'>
		<br />
		
		<table align='center'>
			<tr>
				<td align='center' nowrap='nowrap'>
					<INPUT  style='width:150px;'  type='button' value='".htmlentities(recherche_libelle_page('Export'))."' onClick='OpenPopupExportData();'>
				</td>
				<td align='center' nowrap='nowrap'>
					<INPUT  style='width:150px;'  type='button' value='".htmlentities(recherche_libelle_page('OpenPopup'))."' onClick='OpenPopupData();'>
				</td>
			</tr>
		</table>
		</div>";
	}
	
	echo $html;
	unset($_SESSION['ctrl_cohr']);
?>

