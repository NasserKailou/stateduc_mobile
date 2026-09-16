<?php function get_criteres_where($champ,$table,$array){
		if(count($array)){
			$crit_in = array();
			foreach($array as $i_elem => $elem){
				if(!est_ds_tableau($elem[$champ], $crit_in)){
					$crit_in[] = $elem[$champ] ;
				}
			}
			$where               = ' '.$table.'.'.$champ . ' IN ( '.implode(', ',$crit_in).' ) ';
			return $where ;
		}	
	}
		
	if( count($_POST) > 0 ){
		$is_set_regs 	= false ;
		$is_set_ctrls 	= false ;
		for( $i = 0 ; $i < $_POST['nb_regs_dispo'] ; $i++ ){
				if(isset($_POST['REGS_'.$i]) and trim($_POST['REGS_'.$i]) <> ''){
						$is_set_regs = true ;
						break;
				}
		}
		if(isset($_POST['MISS_SCH']) and trim($_POST['MISS_SCH']) <> ''){
				$is_set_ctrls = true ;
		}elseif(isset($_POST['NOT_LINKED_SCH']) and trim($_POST['NOT_LINKED_SCH']) <> ''){
				$is_set_ctrls = true ;
		}else{
			for( $i = 0 ; $i < $_POST['nb_ctrls_dispo'] ; $i++ ){
					if(isset($_POST['CTRLS_'.$i]) and trim($_POST['CTRLS_'.$i]) <> ''){
							$is_set_ctrls = true ;
							break;
					}
			}
		}
		if(!$is_set_regs){
			$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_regs').'"';
		}elseif(!$is_set_ctrls){
			$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_ctrls').'"';
		}elseif( ($is_set_regs == true) and ($is_set_ctrls == true) ){
			
			$do_action_post = true;
			
			$tab_etabs_run	= array();
			$tab_regs_run 	= array();
			$tab_ctrls_run	= array();
			
			for( $i = 0 ; $i < $_POST['nb_regs_dispo'] ; $i++ ){
				if(isset($_POST['REGS_'.$i]) and trim($_POST['REGS_'.$i]) <> ''){
					$tab_regs_run[]	= $_POST['REGS_'.$i] ;
				}
			}
			
			if(isset($_POST['MISS_SCH']) and trim($_POST['MISS_SCH']) <> ''){
				$tab_ctrls_run[0]	= 'MISS_SCH' ;
			}elseif(isset($_POST['NOT_LINKED_SCH']) and trim($_POST['NOT_LINKED_SCH']) <> ''){
				$tab_ctrls_run[0]	= 'NOT_LINKED_SCH' ;
			}else{
				for( $i = 0 ; $i < $_POST['nb_ctrls_dispo'] ; $i++ ){
					if(isset($_POST['CTRLS_'.$i]) and trim($_POST['CTRLS_'.$i]) <> ''){
						$tab_ctrls_run[]	= $_POST['CTRLS_'.$i] ;
					}
				}
			}
			$arbre = new arbre($_POST['id_chaine']);
			$_SESSION['chaine'] = $_POST['id_chaine'];
			
			foreach($tab_regs_run as $code_regroup){

				$depht	=	$arbre->get_depht_regroup($code_regroup);
				
				//création de la liste des coderegs enfants
				$list_code_reg = $arbre->getchildsid($depht, $code_regroup, $_SESSION['chaine']);

				// On fabrique le "where" de la requête sur ETABLISSEMENT_REGROUPEMENT
				$where = get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'], $list_code_reg);
				
				$get_code_admin = '';
				if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
					$get_code_admin	= ' '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' as code_admin, ' ;
				}
				
				$get_type_ent_stat = '';
				if( exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
					$get_type_ent_stat	= ' '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as code_type_ent_stat, ' ;
				}
				if(isset($_POST['MISS_SCH']) and trim($_POST['MISS_SCH']) <> ''){		
					$requete        = ' SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']. ' as code_etab,'. 
										$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' as nom_etab,'.$get_code_admin.$get_type_ent_stat.
										$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' as code_regroup
										FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'   
										WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
										AND '.$where.' AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].'
										ORDER BY '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'];
				}elseif(isset($_POST['NOT_LINKED_SCH']) and trim($_POST['NOT_LINKED_SCH']) <> ''){		
					$requete        = ' SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']. ' as code_etab,'.$get_code_admin.$get_type_ent_stat.
										$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' as nom_etab'.' 
										FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].'   
										WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].'
										ORDER BY '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'];
				}else{
					$requete        = ' SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']. ' as code_etab,'. 
										$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' as nom_etab,'.$get_code_admin.$get_type_ent_stat.
										$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' as code_regroup
										FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' , '.$GLOBALS['PARAM']['DONNEES_ETABLISSEMENT'].'   
										WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
										AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['DONNEES_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
										AND '.$GLOBALS['PARAM']['DONNEES_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'].'
										AND '.$where.' AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].'
										ORDER BY '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'];
				}
				//echo '<br>'.$requete.'<br>'; die();
				$liste_etab = $GLOBALS['conn']->GetAll($requete);
				//Modif Hebié 18 05 2019 pour lier les établissements saisis aux operateurs de saisie au cas où la tracabilité est activée
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
				$tab_etabs_users_actions = $GLOBALS['conn_dico']->GetAll($req_etabs_users_actions);
				$tab_etabs_users = array();
				$liste_etabs_saisis = array();
				if(is_array($tab_etabs_users_actions)){
					foreach($tab_etabs_users_actions as $etab_user_action){
						if(!in_array($etab_user_action['CODE_ETABLISSEMENT'],$liste_etabs_saisis)){
							$liste_etabs_saisis[] = $etab_user_action['CODE_ETABLISSEMENT'];
							$tab_etabs_users[$etab_user_action['CODE_ETABLISSEMENT']] = $etab_user_action['NOM_USER'];
						}
					}
				}
				
				if(is_array($liste_etab)){
					foreach( $liste_etab as $ii => $etab ){
						$etab['nom_user'] = $tab_etabs_users[$etab['CODE_ETAB']];
						$tab_etabs_run[$etab['CODE_ETAB']] = array_change_key_case_unicode($etab);
					}
				}	
			}
		}
		$_SESSION['ctrl_cohr']['tab_etabs_run'] = $tab_etabs_run;
		$_SESSION['ctrl_cohr']['tab_ctrls_run'] = $tab_ctrls_run;
		$_SESSION['ctrl_cohr']['id_chaine'] 	= $_POST['id_chaine'];
	}
?>
<script type="text/Javascript">

		function reload_page(syst, ch, type_reg) {
			var appart_theme     = "";
			var id_filtre     = "";
			if(Formulaire.filtre_appart_theme){
				appart_theme     = Formulaire.filtre_appart_theme[Formulaire.filtre_appart_theme.selectedIndex].value;
			}
			if(Formulaire.id_filtre){
				id_filtre     = Formulaire.id_filtre[Formulaire.id_filtre.selectedIndex].value;
			}
			if(id_filtre!=""){
				if(appart_theme!=""){
					location.href= '?val=controle&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg+'&appart_theme='+appart_theme+'&filtre='+id_filtre;
				}else{
					location.href= '?val=controle&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg+'&filtre='+id_filtre;
				}
			}else{
				if(appart_theme!=""){
					location.href= '?val=controle&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg+'&appart_theme='+appart_theme;
				}else{
					location.href= '?val=controle&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg;
				}
			}
		}

		function manage_check(var1, var2){
				var chaine_eval1 ='document.getElementById("' + var1 + '").checked == 1;';
				if(var1 != 'MISS_SCH' && var1 != 'NOT_LINKED_SCH'){
					if (eval(chaine_eval1)){
						$("#" + var1).uniform('update');
							var i = 0 ;
							while ( document.getElementById( var2 + '_' + i ) ){
									var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 1;';
									//alert(chaine_eval2);
									eval(chaine_eval2);
									$("#" + var2 + "_" + i).uniform('update');
									i++;
							}
					}else{
							var i = 0 ;
							while ( document.getElementById( var2 + '_' + i ) ){
									var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 0;';
									eval(chaine_eval2);
									$("#" + var2 + "_" + i).uniform('update');
									i++;
							}
					}
					document.getElementById("MISS_SCH").checked = 0;
					$("#MISS_SCH").uniform('update');
					document.getElementById("NOT_LINKED_SCH").checked = 0;
					$("#NOT_LINKED_SCH").uniform('update');
				}else if(var1 == 'MISS_SCH' && document.getElementById("MISS_SCH").checked == 1){
					var i = 0 ;
					while ( document.getElementById( var2 + '_' + i ) ){
							var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 0;';
							eval(chaine_eval2);
							$("#" + var2 + "_" + i).uniform('update');
							i++;
					}
					document.getElementById("ALL_CTRLS").checked = 0;
					$("#ALL_CTRLS").uniform('update');
					document.getElementById("NOT_LINKED_SCH").checked = 0;
					$("#NOT_LINKED_SCH").uniform('update');
					i = 0 ;
					while ( document.getElementById( var2 + '_' + i ) ){
							var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").disabled = 1;';
							eval(chaine_eval2);
							$("#" + var2 + "_" + i).uniform('update');
							i++;
					}
					document.getElementById("ALL_CTRLS").disabled = 1;
					$("#ALL_CTRLS").uniform('update');
					document.getElementById("NOT_LINKED_SCH").disabled = 1;
					$("#NOT_LINKED_SCH").uniform('update');
				}else if(var1 == 'MISS_SCH' && document.getElementById("MISS_SCH").checked == 0){
					i = 0 ;
					while ( document.getElementById( var2 + '_' + i ) ){
							var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").disabled = 0;';
							eval(chaine_eval2);
							$("#" + var2 + "_" + i).uniform('update');
							i++;
					}
					document.getElementById("ALL_CTRLS").disabled = 0;
					$("#ALL_CTRLS").uniform('update');
					document.getElementById("NOT_LINKED_SCH").disabled = 0;
					$("#NOT_LINKED_SCH").uniform('update');
				}else if(var1 == 'NOT_LINKED_SCH' && document.getElementById("NOT_LINKED_SCH").checked == 1){
					var i = 0 ;
					while ( document.getElementById( var2 + '_' + i ) ){
							var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 0;';
							eval(chaine_eval2);
							$("#" + var2 + "_" + i).uniform('update');
							i++;
					}
					document.getElementById("ALL_CTRLS").checked = 0;
					$("#ALL_CTRLS").uniform('update');
					document.getElementById("MISS_SCH").checked = 0;
					$("#MISS_SCH").uniform('update');
					i = 0 ;
					while ( document.getElementById( var2 + '_' + i ) ){
							var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").disabled = 1;';
							eval(chaine_eval2);
							$("#" + var2 + "_" + i).uniform('update');
							i++;
					}
					document.getElementById("ALL_CTRLS").disabled = 1;
					$("#ALL_CTRLS").uniform('update');
					document.getElementById("MISS_SCH").disabled = 1;
					$("#MISS_SCH").uniform('update');
				}else if(var1 == 'NOT_LINKED_SCH' && document.getElementById("NOT_LINKED_SCH").checked == 0){
					i = 0 ;
					while ( document.getElementById( var2 + '_' + i ) ){
							var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").disabled = 0;';
							eval(chaine_eval2);
							$("#" + var2 + "_" + i).uniform('update');
							i++;
					}
					document.getElementById("ALL_CTRLS").disabled = 0;
					$("#ALL_CTRLS").uniform('update');
					document.getElementById("MISS_SCH").disabled = 0;
					$("#MISS_SCH").uniform('update');
				}
		}

</script>

<?php 
	if(isset($_GET['id_systeme']) && $_GET['id_systeme'] <> ''){
		$_SESSION['secteur'] = $_GET['id_systeme'];
	}
	$GLOBALS['id_systeme'] 	= $_SESSION['secteur'];
	
	if(isset($_GET['filtre']) && $_GET['filtre'] <> ''){
		$_SESSION['filtre'] = $_GET['filtre'];
	}

	if(isset($_GET['appart_theme']) && $_GET['appart_theme'] <> '')
		$_SESSION['appart_theme'] = $_GET['appart_theme'];
	elseif(isset($_SESSION['type_ent_stat']) && $_SESSION['type_ent_stat']<>'')
		$_SESSION['appart_theme'] = $_SESSION['type_ent_stat'];
		
	function get_chaines_systeme(){
			
		$conn 		= $GLOBALS['conn'];
		if(!(isset($_SESSION['fixe_chaines']) && count((array)$_SESSION['fixe_chaines'][$GLOBALS['id_systeme']])>0)){
			$requete     = 'SELECT    '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' as CODE_TYPE_CH,
						'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 	as LIBELLE_TYPE_CH
						FROM      '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
						WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_systeme'].'
						ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'] ;
		}else{
			$requete     = 'SELECT    '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' as CODE_TYPE_CH,
						'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 	as LIBELLE_TYPE_CH
						FROM      '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
						WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_systeme'].'
						AND 	'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' IN ('.implode(',',$_SESSION['fixe_chaines'][$GLOBALS['id_systeme']]).')'.'
						ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'] ;
		}		
		
			try {            
					$GLOBALS['chaines_systeme'] = $conn->GetAll($requete);
					if(!is_array($GLOBALS['chaines_systeme'])){                
							 throw new Exception('ERR_SQL');   
					}
					if(isset($_GET['id_chaine']) and (trim($_GET['id_chaine'])<>'') ){
							$GLOBALS['id_chaine'] 	= $_GET['id_chaine'];
					}elseif(isset($_POST['id_chaine'])){
							$GLOBALS['id_chaine'] 	= $_POST['id_chaine'];
					}
					if(!isset($GLOBALS['id_chaine'])){
							$GLOBALS['id_chaine'] 	= $GLOBALS['chaines_systeme'][0]['CODE_TYPE_CH'];
					}
			}
			catch (Exception $e) {
					 $erreur = new erreur_manager($e,$requete);
			} 
	}
		 
		function get_type_regs_chaine(){
				
				$arb = new arbre($GLOBALS['id_chaine']);
				$GLOBALS['type_regs_chaine'] = $arb->chaine ;

				if(isset($_GET['type_reg']) and (trim($_GET['type_reg'])<>'') ){
						$GLOBALS['type_reg'] 	= $_GET['type_reg'];
				}elseif(isset($_POST['type_reg'])){
						$GLOBALS['type_reg'] 	= $_POST['type_reg'];
				}elseif(isset($_SESSION['fixe_type_reg'][$GLOBALS['id_chaine']]) && count($_SESSION['fixe_type_reg'][$GLOBALS['id_chaine']])>0){
						$GLOBALS['type_reg'] 	= $_SESSION['fixe_type_reg'][$GLOBALS['id_chaine']][0];
				}
				if(!isset($GLOBALS['type_reg'])){
						$GLOBALS['type_reg'] 	= $GLOBALS['type_regs_chaine'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
				}
				unset($arb);
		} 
		
		function tableau_check( $tab=array(), $code, $lib, $chp_check, $lib_all_check, $chp_all_check, $nb_td=1, $nowrap='' ){
				$html 	= '';
				if( trim($nowrap) <> 'nowrap' ) {
					$nowrap = '' ;
				} 
				if(is_array($tab)){
						$html .= "\n" . '<TABLE class = \'no_border\' border="1"  width="100%">';
						$i_tr = 0 ;
						while(isset($tab[$i_tr])){
								$html .= "\n\t" . '<tr>';
								$i_td = 1;
								for( $i = $i_tr ; $i < $i_tr + $nb_td ; $i++ ){
									 //substr($tab[$i][$lib], 0, 200)
									 if(isset($tab[$i])){
												$html .= "\n\t\t" . '<td align="right" '.$nowrap.'>'.substr($tab[$i][$lib], 0, 200).' <INPUT type="checkbox" id="'.$chp_check.'_'.$i.'" name="'.$chp_check.'_'.$i.'" value="'.$tab[$i][$code].'"';
												if(isset($_POST[$chp_check.'_'.$i]) and trim($_POST[$chp_check.'_'.$i]) <> ''){
													$html .= ' CHECKED';
												}elseif(isset($_SESSION['fixe_reg'][$GLOBALS['type_reg']]) && in_array(trim($tab[$i][$code]),$_SESSION['fixe_reg'][$GLOBALS['type_reg']]) && !count($_POST)){
													$html .= ' CHECKED';
												}elseif(in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs']) && $chp_check=='REGS' && !in_array(trim($tab[$i][$code]),(array)($_SESSION['fixe_reg'][$GLOBALS['type_reg']]))){
													$html .= ' DISABLED';
												}else{
													$html .= '' ;
												}
												$html .= '></td>';
												$i_td++;
										}else{
												$html .= "\n\t\t" . '<td colspan="'. ( $nb_td - $i_td +1) .'"></td>';										
												$i_tr = $i ;
												break;
										}
								}
								$html .= "\n\t" . '</tr>';
								$i_tr += $nb_td ;
						}
						if( count($tab) > 1 ){
								if($_POST[$chp_all_check]==1) $checked = 'CHECKED';
								else $checked = '';
								//$html .= "\n" . '<tr><td nowrap colspan="' . $nb_td . '" align="center">' . recherche_libelle_page($lib_all_check) . ' <INPUT type="checkbox" id="' . $chp_all_check . '" name="' . $chp_all_check . '" value="1" onclick="manage_check(\''.$chp_all_check.'\', \''.$chp_check.'\');"'.$checked.'></td></tr>';
							$GLOBALS[$chp_all_check] =	'<INPUT type="checkbox" id="' . $chp_all_check . '" name="' . $chp_all_check . '" value="1" onclick="manage_check(\''.$chp_all_check.'\', \''.$chp_check.'\');"'.$checked.'>' ;
						}
						$html .= "\n" . '</TABLE>';
				}
				return ($html);
		}
		
		get_chaines_systeme();
		get_type_regs_chaine();

		$GLOBALS['nb_td'] = 4;
?>
<style type="text/css" media="all">
		.cachediv {
				 visibility: hidden;
				 overflow: hidden;
				 height: 1px;
				 margin-top: -100%px;
				 position: absolute;
		}
		.montrediv {
				 visibility: visible;
				 overflow: visible;
		}
		.espace_2 {
			margin: 4px ;
		}
		
		.details {
	text-align: right;
	overflow: auto;
		}
.no_border {
	border-top-style: none;
	border-right-style: none;
	border-bottom-style: none;
	border-left-style: none;
}
</style>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>

<FORM name="Formulaire"  method="post" action="">

<div>
<?php echo recherche_libelle_page('curr_year');?> : <b><?php echo get_libelle_from_array($_SESSION['tab_annees'], $_SESSION['annee'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']);?></b> 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
if(isset($GLOBALS['PARAM']['FILTRE']) && $GLOBALS['PARAM']['FILTRE']){
echo recherche_libelle_page('curr_period').' : <b>'.get_libelle_from_array($_SESSION['tab_filtres'], $_SESSION['filtre'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']).'</b>';
//echo create_combo($_SESSION['tab_filtres'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $_SESSION['filtre'], 'toggle_filtre()');
?>
<select name="id_filtre"  onChange="reload_page(<?php echo $GLOBALS['id_systeme'];?>, id_chaine.value, type_reg.value)">
<?php
	$requete = "SELECT * FROM ".$GLOBALS['PARAM']['TYPE_FILTRE']." ORDER BY ".$GLOBALS['PARAM']['ORDRE']."_".$GLOBALS['PARAM']['TYPE_FILTRE'];
	$rs_type_filtre= $GLOBALS['conn']->GetAll($requete);
	if(is_array($rs_type_filtre))
	foreach ($rs_type_filtre as $k => $v){
		echo "<option value='".$v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']]."'";
		if ($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']] == $_SESSION['filtre']){
			echo " selected";
		}
		echo ">".$v[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']]."</option>";
	}
?>
</select>
<?php
}
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo recherche_libelle_page('curr_sect');?> : <b><?php echo get_libelle_from_array($_SESSION['tab_secteur'], $_SESSION['secteur'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE');?></b>

<select name="id_systeme"	onChange="reload_page(this.value,'',''); ">
<?php 
if(is_array($_SESSION['tab_secteur']))
foreach ($_SESSION['tab_secteur'] as $i => $systeme){
	echo "<option value='".$systeme[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]."'";
	if ($systeme[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $_SESSION['secteur']){
			echo " selected";
	}
	echo ">".$systeme['LIBELLE']."</option>";
}
?>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>''){
echo recherche_libelle_page('appart_theme');
?> : 
<select name="filtre_appart_theme"  onChange="reload_page(<?php echo $GLOBALS['id_systeme'];?>, id_chaine.value, type_reg.value)">
<?php
	$requete = "SELECT * FROM ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']." ORDER BY ".$GLOBALS['PARAM']['ORDRE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT'];
	$rs_type_entite= $GLOBALS['conn']->GetAll($requete);
	if(is_array($rs_type_entite))
	foreach ($rs_type_entite as $k => $v){
		echo "<option value='".$v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."'";
		if ($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']] == $_SESSION['appart_theme']){
			echo " selected";
		}
		echo ">".$v[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."</option>";
	}
?>
</select>
<?php
}
?>
</div>

<div align="center">
    <table border='1' style="overflow: auto;" width="100%">
      <caption>
      <b><?php echo recherche_libelle_page('ConfExt');?></b>
      </caption>
      <tr>
        <td rowspan="2" width="45%"><table border='1' align="center"  width="100%">
            <caption>
            <b><?php echo recherche_libelle_page('tit_atlas');?></b>
            </caption>
          <tr>
              <td width="21%" class="border_table" style="padding:3px">
				  <?php 
					foreach ((array)$_SESSION['tab_secteur'] as $k => $v){
						if ($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $_SESSION['secteur']){
							echo "<b>".$v['LIBELLE']."</b><br/>";
						}
					} 
					echo "<u>".recherche_libelle_page('chaine')."</u><br/>";
				  ?>
				  <select style="width:100px" name="id_chaine" onchange="reload_page(<?php echo $GLOBALS['id_systeme'];?>,this.value,'');">
                    <?php if(is_array($GLOBALS['chaines_systeme']))
												foreach ($GLOBALS['chaines_systeme'] as $i => $chaine_systeme){
														echo "<option value='".$chaine_systeme['CODE_TYPE_CH']."'";
														if ($chaine_systeme['CODE_TYPE_CH'] == $GLOBALS['id_chaine']){
																echo " selected";
														}
														echo ">".$chaine_systeme['LIBELLE_TYPE_CH']."</option>";
												}
								?>
                  </select>
                  <hr width="75%"/>
                <u><?php echo recherche_libelle_page('type_reg');?></u> <br />
                  <select style="width:100px" name="type_reg" onchange="reload_page(<?php echo $GLOBALS['id_systeme'];?>, id_chaine.value, this.value);">
                    <?php foreach ($GLOBALS['type_regs_chaine'] as $i => $type_regs){
											echo "<option value='".$type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."'";
											if ($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']){
													echo " selected";
											}
											echo ">".$type_regs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."</option>";
									}
							?>
                  </select>
			</td>
            <td width="79%" ><table  style="border:none" cellspacing="0"  cellpadding="0"  width="100%">
                  <tr>
                    <?php $arb	= new arbre($GLOBALS['id_chaine']);
						foreach($arb->chaine as $i=>$c) {
								if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']) {
										$curdepht = $i;
										break;
								}
						} 
						$arb->type_access='config';
						$entete = $arb->create_entete(0, $curdepht, true); 

						if ( isset($entete['code_regroup']) && ($entete['code_regroup'] <> '') ){
							if(!in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs'])){	
								$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, 
												A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG '.
												' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS A, '.$GLOBALS['PARAM']['LIAISONS'].' AS B, 
												'.$GLOBALS['PARAM']['HIERARCHIE'].' AS C 
												WHERE A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = B.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
												AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
												C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
												' AND B.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].' ='.(int)$entete['code_regroup'].
												' AND C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' ='.$GLOBALS['id_chaine'].
												' ORDER BY A.'.$GLOBALS['CHAMP_ORDRE'].';';
							}else{	
								$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, 
												A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG '.
												' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS A, '.$GLOBALS['PARAM']['LIAISONS'].' AS B, 
												'.$GLOBALS['PARAM']['HIERARCHIE'].' AS C 
												WHERE A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = B.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
												AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
												C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
												' AND B.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].' ='.(int)$entete['code_regroup'].
												' AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$GLOBALS['type_reg']].')'.
												' AND C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' ='.$GLOBALS['id_chaine'].
												' ORDER BY A.'.$GLOBALS['CHAMP_ORDRE'].';';
							}		         
						}else {
							if(!in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs'])){	
								$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG 
								FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
								WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$GLOBALS['type_reg'].
								' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';     
							}else{	
								$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG 
								FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
								WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$GLOBALS['type_reg'].
								' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$GLOBALS['type_reg']].')'.
								' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';
							}
						}
						
						$tab_regs 	= $GLOBALS['conn']->GetAll($requete);
						//$nb_td_regs =	$GLOBALS['nb_td'];

				if( isset($entete['code_regroup']) and trim($entete['code_regroup']) <> ''){
						$combos_regs = preg_replace_callback("/(<form name=\"combo_regroups\">)|(</form>)|(<br />)/", '', $entete['html']);
						$combos_regs = preg_replace_callback('/<br>([[:blank:]]|[[:space:]])*</div>/', '</div>', $combos_regs);
						$combos_regs = str_replace('<select', '<select  style=\'width:100px;\'',$combos_regs); 
						?>
                    <td align="center" valign="middle" width="30%"><div style="overflow:visible; width:100%; ">
                        <table width="100%">
                          <tr>
                            <td align="center"><?php echo $combos_regs ;?> </td>
                          </tr>
                        </table>
                    </div></td>
				<?php if($nb_td_regs > 1)
					{
							$nb_td_regs = $nb_td_regs - 1 ;
					}
					}?>
                    <td align="center" valign="middle"  width="100%"><div style="height:140px; max-height:140px; overflow:auto" class="border_table" >
                        <?php print (tableau_check( $tab_regs, 'CODE_REG', 'LIB_REG', 'REGS', 'toutes', 'ALL_REGS', 2 , 'nowrap')); ?>
                        <input type="hidden" name="nb_regs_dispo" value="<?php echo count($tab_regs);?>" />
                    </div></td>
                  </tr>
                </table>
                <div class="class_table" style="vertical-align:bottom; height:24px; text-align:center"> &nbsp;<span class="class_table" style="vertical-align:bottom; height:24px; text-align:center"><u><?php echo recherche_libelle_page('all_regs'); ?></u> :<?php echo $GLOBALS['ALL_REGS'];?></span></div>
			</td>
          </tr>
        </table></td>
        <td width="55%"><table border='1' align="center"  width="100%">
            <caption>
            <b><?php echo recherche_libelle_page('ListRegles');?>  </b>
            </caption>
          <tr>
              <td><div style="height:200px; max-height:200px; overflow:auto" class="border_table" >
                  <?php 
				  	if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>''){	
						$requete='  SELECT DISTINCT DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM AS ID_ASSOC, 
									DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME AS ID_REG1, 
									DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME_ASSOC AS ID_REG2, 
									DICO_TRADUCTION.LIBELLE AS MSG_ALERT,
									DICO_THEME.ORDRE_THEME,
									DICO_REGLE_THEME.ORDRE_REGLE_THEME
									FROM DICO_THEME,DICO_THEME_SYSTEME,DICO_REGLE_THEME_ASSOC, DICO_REGLE_THEME, DICO_TRADUCTION
									WHERE   DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME = DICO_REGLE_THEME.ID_REGLE_THEME
									AND DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM = DICO_TRADUCTION.CODE_NOMENCLATURE
									AND DICO_THEME_SYSTEME.ID = DICO_REGLE_THEME.ID_THEME
									AND DICO_THEME.ID = DICO_THEME_SYSTEME.ID
									AND DICO_TRADUCTION.CODE_LANGUE =\''.$_SESSION['langue'].'\'
									AND DICO_TRADUCTION.NOM_TABLE=\'DICO_REGLE_THEME_ASSOC\'
									AND DICO_THEME_SYSTEME.ID_SYSTEME='.$_SESSION['secteur'].'
									AND DICO_THEME_SYSTEME.APPARTENANCE='.$_SESSION['appart_theme'].'
									AND DICO_REGLE_THEME_ASSOC.ACTIVER_CTRL = 1
									ORDER BY DICO_THEME.ORDRE_THEME, DICO_REGLE_THEME.ORDRE_REGLE_THEME;'; 
									
						}else{
							$requete='  SELECT DISTINCT DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM AS ID_ASSOC, 
									DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME AS ID_REG1, 
									DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME_ASSOC AS ID_REG2, 
									DICO_TRADUCTION.LIBELLE AS MSG_ALERT,
									DICO_THEME.ORDRE_THEME,
									DICO_REGLE_THEME.ORDRE_REGLE_THEME
									FROM DICO_THEME,DICO_THEME_SYSTEME,DICO_REGLE_THEME_ASSOC, DICO_REGLE_THEME, DICO_TRADUCTION
									WHERE   DICO_REGLE_THEME_ASSOC.ID_REGLE_THEME = DICO_REGLE_THEME.ID_REGLE_THEME
									AND DICO_REGLE_THEME_ASSOC.ID_ASSOC_REG_THM = DICO_TRADUCTION.CODE_NOMENCLATURE
									AND DICO_THEME_SYSTEME.ID = DICO_REGLE_THEME.ID_THEME
									AND DICO_THEME.ID = DICO_THEME_SYSTEME.ID
									AND DICO_TRADUCTION.CODE_LANGUE =\''.$_SESSION['langue'].'\'
									AND DICO_TRADUCTION.NOM_TABLE=\'DICO_REGLE_THEME_ASSOC\'
									AND DICO_THEME_SYSTEME.ID_SYSTEME='.$_SESSION['secteur'].'
									AND DICO_REGLE_THEME_ASSOC.ACTIVER_CTRL = 1
									ORDER BY DICO_THEME.ORDRE_THEME, DICO_REGLE_THEME.ORDRE_REGLE_THEME;'; 
						}
						$tab_ctrls 	= $GLOBALS['conn_dico']->GetAll($requete);
						//print_r($tab_ctrls);
						//echo($requete);
						//print (tableau_check( $tab_ctrls, 'id_assoc', 'msg_alert', 'CTRLS', 'toutes', 'ALL_CTRLS', 1, '' ));
						print (tableau_check( $tab_ctrls, 'ID_ASSOC', 'MSG_ALERT', 'CTRLS', 'toutes', 'ALL_CTRLS', 1, '' ));
							  
					?>
					<input type="hidden" name="nb_ctrls_dispo" value="<?php echo count($tab_ctrls);?>" />
              </div></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><div style="position:relative; vertical-align:baseline; height:22px" >
            <div class="class_table" style="position: absolute; text-align:left; overflow:auto"> &nbsp;&nbsp;<u>
			<?php 
				$not_linked_sch = recherche_libelle_page('not_linked_sch');
				if($not_linked_sch==''){
					if($_SESSION['langue']=='fr') $not_linked_sch = 'Etablissements sans rattachement'; else $not_linked_sch = 'Schools not located';
				}
				echo '<b>'.$not_linked_sch.'</b>';
			?>
			</u> :<input type="checkbox" name="NOT_LINKED_SCH" id="NOT_LINKED_SCH" value="1" onclick="manage_check('NOT_LINKED_SCH', 'CTRLS');" <?php if(isset($_POST['NOT_LINKED_SCH']) and trim($_POST['NOT_LINKED_SCH']) <> '') echo "checked";?>/></div>
          	<div class="class_table" style="position: absolute; left:230px; text-align:left; overflow:auto"> &nbsp;&nbsp;<u>
			<?php 
				$MissingSchools = recherche_libelle_page('miss_sch');
				if($MissingSchools==''){
					if($_SESSION['langue']=='fr') $MissingSchools = 'Etablissements Manquants'; else $MissingSchools = 'Missing Schools';
				}
				echo '<b>'.$MissingSchools.'</b>';
			?>
			</u> :<input type="checkbox" name="MISS_SCH" id="MISS_SCH" value="1" onclick="manage_check('MISS_SCH', 'CTRLS');" <?php if(isset($_POST['MISS_SCH']) and trim($_POST['MISS_SCH']) <> '') echo "checked";?>/></div>
          	<div class="class_table" style="position: absolute; left:430px; text-align:left; overflow:auto"> &nbsp;&nbsp;<u><?php echo recherche_libelle_page('all_ctrl');?></u> :<?php echo $GLOBALS['ALL_CTRLS'];?></div>
          	<table border='1' align="right">
              <tr>
                <td><input name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="19px" height="17px" border="0"  value="Envoyer" class="envoyer" /></td>
                <td class="like_caption"><?php echo recherche_libelle_page('exec');?></td>
              </tr>
            </table>
        </div></td>
      </tr>
    </table>
</div>
</FORM>
