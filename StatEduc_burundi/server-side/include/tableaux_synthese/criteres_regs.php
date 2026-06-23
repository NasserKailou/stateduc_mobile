<?php
	lit_libelles_page('/liste_user_report.php');
	/**
	 * Méthode get_type_reg($code_reg)  
	 * Recupépère le type d'un regroupement
	 * @access private
	 * @param $code_reg code du regroupement
	 */
	function get_type_reg ($code_reg){
		$conn 		= $GLOBALS['conn'];
		$requete='  SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS TYPE_REG 
					FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
					WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.(int)$code_reg;            
		
		return($conn->GetOne($requete));
	}
	/**
	 * Méthode get_regs_having_type($type_reg)  
	 * Recupère tous les regroupements du type donné en paramètre
	 * @access private
	 * @param $type_reg code du regroupement
	 */	
	function get_regs_having_type ($type_reg){
		$conn 		= $GLOBALS['conn'];
		if(!in_array($type_reg,$_SESSION['fixe_type_regs'])){
			$requete='  SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG 
						FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$type_reg.
						' ORDER BY '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['CHAMP_ORDRE'];
		}else{
			$requete='  SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG 
						FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$type_reg.
						' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$type_reg].')'.
						' ORDER BY '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['CHAMP_ORDRE'];
		}          
			
		return($conn->GetAll($requete));
	}
	/**
	 * Méthode get_lib_reg($code_reg)  
	 * Recupère le nom du regroupement du code_reg donné en paramètre
	 * @access private
	 * @param $type_reg code du regroupement
	 */		
	function get_lib_reg($code_reg){
		$conn 		= $GLOBALS['conn'];
		$requete	= 'SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = '.(int)$code_reg;
		return($GLOBALS['conn']->GetOne($requete));
	}
		
	////Creation des varibles globales $_SESSION['post_crit_regs'] pour garder les criteres regroupements precedemment choisis
	if(isset($_GET['id_systeme']) || isset($_GET['id_chaine']) || isset($_GET['type_reg'])){
		unset($_SESSION['post_crit_regs']);
		unset($_SESSION['reg_parents']);
		unset($_SESSION['tab_rpt']);
	}
	$conserver_session = false;
	if(isset($_SESSION['post_crit_regs']) && isset($_SESSION['fixe_regs']) && count($_SESSION['fixe_regs'])>0){
		$depht = $_SESSION['post_crit_regs']['niv'];
		$k = 0;
		for($i=0; $i<$depht; $i++){
			if(in_array($_SESSION['post_crit_regs']['combo_regroup_'.$i],$_SESSION['fixe_reg_parents'][$GLOBALS['type_reg']]) || in_array($_SESSION['post_crit_regs']['combo_regroup_'.$i],$_SESSION['fixe_reg'][$GLOBALS['type_reg']])){
				$k++;
			}
		}
		if($k == $depht) $conserver_session = true;
		if(count($_SESSION['fixe_regs'])==1 && $_SESSION['fixe_regs'][$_SESSION['fixe_type_regs'][0]]==$_SESSION['post_crit_regs']['combo_regroup_0']) $conserver_session = true;
	}
	
	if(count($_POST)>0){
		$_SESSION['post_crit_regs']=$_POST;
	}elseif(($conserver_session) || (!isset($_SESSION['fixe_regs']) || count($_SESSION['fixe_regs'])==0)){
		if(count((array)$_SESSION['post_crit_regs'])>0) {
			foreach($_SESSION['post_crit_regs'] as $cle => $val) {
				if(isset($_SESSION['post_crit_regs'][$cle])) {
					$_POST[$cle] =  $val;
				}
			}
		}
	}else{
		unset($_SESSION['post_crit_regs']);
	}
	if(isset($_POST['zone'])) $GLOBALS['zone'] = $_POST['zone'];
	else $GLOBALS['zone'] = '';
	
	if( count($_POST) > 0 ){

		$tab_reg = array();
		$niv = $_POST['niv'];
		if( $niv == 0 and $_POST['all_zones'] == 1 ){ // Niveau Pays
			$tab_reg[0]['niveau'] 	= 1 ;
			$tab_reg[0]['type_reg'] =  get_type_reg ($_POST['zone']) ;
			$tab_reg[0]['code_reg'] = 'All' ;	
		}else{
			for( $i = 0 ; $i < $niv ; $i++ ){
				$tab_reg[$i]['niveau']		= $i + 1 ;
				$tab_reg[$i]['type_reg']	= get_type_reg ($_POST['combo_regroup_'.$i]) ;
				$tab_reg[$i]['code_reg']	= $_POST['combo_regroup_'.$i] ;	
			}
			if($_POST['all_zones'] <> 1){
				$tab_reg[$niv]['niveau'] 	= $niv + 1 ;
				$tab_reg[$niv]['type_reg']	= get_type_reg($_POST['zone']);
				$tab_reg[$niv]['code_reg']	= $_POST['zone'] ;	
			}else{
				$tab_reg[$niv]['niveau'] 	= $niv + 1 ;
				$tab_reg[$niv]['type_reg']	= get_type_reg ($_POST['zone']) ;
				$tab_reg[$niv]['code_reg']	= 'All' ;	
            }
		}
		$GLOBALS['zone'] = $_POST['zone'] ;
	}
	require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre_rpt.class.php';
?>
<script type="text/Javascript">

		function reload_page(syst, ch, type_reg) {
			location.href= '?val=crit_reg&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg;
		}
		
		function ctrl_all_check(i_combo, nb_combo, reg_pere){
			var chaine_eval1 ='document.getElementById("all_zones_' + i_combo + '").checked == 1;';
			if (eval(chaine_eval1)){
				for( i = 0 ; i <= nb_combo ; i++ ){
					if(i != i_combo){
						var chaine_eval2 = 'document.getElementById("all_zones_' + i + '").checked = 0;';
						//alert(chaine_eval2);
						eval( chaine_eval2 );
					}
				}
				
				var chaine_eval3 = 'document.Formulaire.zone_pere.value = reg_pere ;'
				eval( chaine_eval3 );
			}else{
				var chaine_eval3 = 'document.Formulaire.zone_pere.value = "";'
				eval( chaine_eval3 );
			}
			document.Formulaire.submit();
		}

		function manage_check(var1, var2){
			var chaine_eval1 ='document.getElementById("' + var1 + '").checked == 1;';
			if (eval(chaine_eval1)){
				$("#" + var1).uniform('update');
				var i = 0 ;
				while ( document.getElementById( var2 + '_' + i ) ){
					var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 1;';
					//alert(chaine_eval2);
					eval( chaine_eval2 );
					$("#" + var2 + "_" + i).uniform('update');
					i++;
				}
			}else{
				var i = 0 ;
				while ( document.getElementById ( var2 + '_' + i ) ) {
					var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 0;';
					eval(chaine_eval2);
					$("#" + var2 + "_" + i).uniform('update');
					i++;
				}
			}
		}

</script>

<?php 
	/**
	 * Méthode get_systemes()  
	 * Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
	 * @access private
	 */
		function get_systemes(){
			if(!(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0)){
				$requete                 = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme,
                                    D_TRAD.LIBELLE as libelle_systeme
                                    FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
                                    WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
                                    AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
			}else{
				$requete                 = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme,
                                    D_TRAD.LIBELLE as libelle_systeme
                                    FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
                                    WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
									AND T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['fixe_secteurs']).')'.'
							  		AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
			}

			$conn 		= $GLOBALS['conn'];
				try {            
						$GLOBALS['systemes'] = $conn->GetAll($requete);
						if(!is_array($GLOBALS['systemes'])){                
								 throw new Exception('ERR_SQL');   
						}
						if( isset($_GET['id_syst']) and (trim($_GET['id_syst'])<>'') ){
							$GLOBALS['id_syst'] 	= $_GET['id_syst'];
						}elseif(isset($_POST['id_syst'])){
							$GLOBALS['id_syst'] 	= $_POST['id_syst'];
						}elseif(isset($_SESSION['secteur'])){
							$GLOBALS['id_syst'] 	= $_SESSION['secteur'];
						}
						if(!isset($GLOBALS['id_syst'])){
							$GLOBALS['id_syst'] 	= $GLOBALS['systemes'][0]['id_syst'];
						}
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$requete);
				} 
    }
	/**
	 * Méthode get_chaines_systeme()  
	 * Chargement des codes et libellés des chaines de regroupement
	 * @access private
	 */
		function get_chaines_systeme(){
			
			
			 	if(!(isset($_SESSION['fixe_chaines']) && count((array)$_SESSION['fixe_chaines'][$GLOBALS['id_syst']])>0)){
					$requete     = 'SELECT    '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' as CODE_TYPE_CH,
									'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 	as LIBELLE_TYPE_CH
									FROM      '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
									WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_syst'] ;
				}else{
					$requete     = 'SELECT    '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' as CODE_TYPE_CH,
									'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 	as LIBELLE_TYPE_CH
									FROM      '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
									WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_syst'].
									' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' IN ('.implode(',',$_SESSION['fixe_chaines'][$GLOBALS['id_syst']]).')';
                }                                    

				$conn 		= $GLOBALS['conn'];
				try {            
						$GLOBALS['chaines_systeme'] = $conn->GetAll($requete);
						if(!is_array($GLOBALS['chaines_systeme'])){                
							throw new Exception('ERR_SQL');   
						}
						if(isset($_GET['id_chaine']) and (trim($_GET['id_chaine'])<>'') ){
							$GLOBALS['id_chaine'] 	= $_GET['id_chaine'];
						}elseif(isset($_POST['id_chaine'])){
							$GLOBALS['id_chaine'] 	= $_POST['id_chaine'];
						}elseif($_SESSION['tab_rpt']['id_chaine']){
							$GLOBALS['id_chaine'] 	= $_SESSION['tab_rpt']['id_chaine'] ;
						}
						if(!isset($GLOBALS['id_chaine'])){
							$GLOBALS['id_chaine'] 	= $GLOBALS['chaines_systeme'][0]['CODE_TYPE_CH'];
						}
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$requete);
				} 
		}
		/**
		 * Méthode get_chaines_systeme()  
		 * Chargement des types de regroupements liés à chaque chaine de
		 * regroupement
		 * @access private
		 */			 
		function get_type_regs_chaine(){
				
				$arbre = new arbre($GLOBALS['id_chaine']);
				$GLOBALS['type_regs_chaine'] = $arbre->chaine ;
				if(isset($_GET['type_reg']) and (trim($_GET['type_reg'])<>'') ){
						$GLOBALS['type_reg'] 	= $_GET['type_reg'];
				}elseif(isset($_POST['type_reg'])){
						$GLOBALS['type_reg'] 	= $_POST['type_reg'];
				}elseif($_SESSION['tab_rpt']['type_reg']){
					$GLOBALS['type_reg'] 	= $_SESSION['tab_rpt']['type_reg'] ;
				}elseif(isset($_SESSION['fixe_type_reg'][$GLOBALS['id_chaine']]) && count($_SESSION['fixe_type_reg'][$GLOBALS['id_chaine']])>0){
						$GLOBALS['type_reg'] 	= $_SESSION['fixe_type_reg'][$GLOBALS['id_chaine']][0];
				}
				if(!isset($GLOBALS['type_reg'])){
						$GLOBALS['type_reg'] 	= $GLOBALS['type_regs_chaine'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
				}
				
				//$GLOBALS['first_type_reg_ch'] = $GLOBALS['type_regs_chaine'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] ;
		} 

		function tableau_check( $tab=array(), $code, $lib, $chp_check, $lib_all_check, $chp_all_check, $nb_td=1 ){
				$html 	= '';
				if(is_array($tab)){
						$html .= "\n" . '<TABLE border="1"  width="100%">';
						$i_tr = 0 ;
						while(isset($tab[$i_tr])){
								$html .= "\n\t" . '<tr>';
								$i_td = 1;
								for( $i = $i_tr ; $i < $i_tr + $nb_td ; $i++ ){

									 if(isset($tab[$i])){
												$html .= "\n\t\t" . '<td nowrap align="right">'.$tab[$i][$lib].' <INPUT type="checkbox" id="'.$chp_check.'_'.$i.'" name="'.$chp_check.'_'.$i.'" value="'.$tab[$i][$code].'"';
												if(isset($_POST[$chp_check.'_'.$i]) and trim($_POST[$chp_check.'_'.$i]) <> ''){
													$html .= ' CHECKED';
												}elseif(isset($_SESSION['fixe_reg'][$GLOBALS['type_reg']]) && in_array(trim($tab[$i][$code]),$_SESSION['fixe_reg'][$GLOBALS['type_reg']])){
													$html .= ' CHECKED';
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
								if($_POST[$chp_all_check]==1) $checked = ' CHECKED';
								else $checked = '';
								$html .= "\n" . '<tr><td nowrap colspan="' . $nb_td . '" align="center">' . recherche_libelle_page($lib_all_check) . ' <INPUT type="checkbox" id="' . $chp_all_check . '" name="' . $chp_all_check . '" value="1" onclick="manage_check(\''.$chp_all_check.'\', \''.$chp_check.'\');"'.$checked.'></td></tr>';
						}
						$html .= "\n" . '</TABLE>';
				}
				return ($html);
		}
		
		/**
		 * Méthode get_curdepht($type_reg, $arbre)  
		 * Recupère la profondeur d'un type de regroupement dans la chaine
		 * @access private
		 * @param $type_reg code du type de regroupement dont on veut recupérer
		 * le niveau
		 * @param $arbre objet dans lequel la composition de la chaine de
		 * regroupement en cours est stockée
		 */		
		function get_curdepht ($type_reg, $arbre){
			if($type_reg<>'etab'){
				foreach( $arbre->chaine as $i => $c ) {
					if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $type_reg) {
						return($i);
					}
				}
			}//Ajout Hebie pour affichage etablissement IMIS
			elseif($type_reg=='etab'){
				$j=0;
				foreach( $arbre->chaine as $i => $c ) {
					$j=$i;
				}
				return($j+1);
			}
			//Ajout Hebie pour affichage etablissement IMIS
		} 
		/**
		 * Méthode get_all_zones($type_reg_filtre, $zone_pere, $arbre)  
		 * Recupère les regroupements dont le père est précisé par la varaible
		 * $zone_pere
		 * @access private
		 * @param $type_reg_filtre code du type de regroupement des fils de
		 * zones père
		 * @param $zone_pere code du regroupement dont on veut retrouver les
		 * sous-regroupements
		 * @param $arbre objet dans lequel la composition de la chaine de
		 * regroupement en cours est stockée, la connexion courante et
		 */	
		function get_all_zones($type_reg_filtre, $zone_pere, $arbre){
			//$arbre	= new arbre($GLOBALS['id_chaine']);
			$all_zones_type_reg = array();
			$all_zones			= array();
			if( isset($zone_pere) && trim($zone_pere) <> '' ){
				if(trim($zone_pere) == 'pays'){
					$all_type_reg 	= $arbre->chaine ;
					$first_type_reg = $all_type_reg[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
					$curdepht 		= get_curdepht($first_type_reg, $arbre) ;
					$all_regs 		= get_regs_having_type ($first_type_reg) ;
					if( is_array($all_regs) ){
						foreach($all_regs as $i => $reg){
							$all_zones = array_merge($arbre->getchildsid($curdepht, $reg['CODE_REG'], $GLOBALS['id_chaine']), $all_zones);
						}
					}
				}else{
					$type_reg_pere 	= get_type_reg($zone_pere) ;
					$curdepht 		= get_curdepht($type_reg_pere, $arbre) ;
					$all_zones 		= $arbre->getchildsid($curdepht, $zone_pere, $GLOBALS['id_chaine']) ;
				}
				if( is_array($all_zones) ){
					foreach( $all_zones as $i => $reg){
						if( 
							(get_type_reg($reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]) == $type_reg_filtre) && 
							(!in_array($reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']],$all_zones_type_reg))
						  ){
							$all_zones_type_reg[] = $reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
						}
					}
				}
			}
			return($all_zones_type_reg);
		}

		get_systemes();
		get_chaines_systeme();
		get_type_regs_chaine();

?>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>themes/jquery-ui/base/jquery.ui.all.css"/>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>themes/uniform/default/css/uniform.default.css"/>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS'] . $_SESSION['style']; ?>">
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
		.espace_1_ {
	margin-top: 5px;
	margin-right: 5px;
	margin-bottom: 5px;
	margin-left: 5px;
		}
		
		
		.details {
	text-align: right;
	overflow: auto;
		}
	BODY {	
		background-image: none;
	}
	input.uniform-input, select.uniform-multiselect, textarea.uniform {
		color:#000000;
	}
	
	.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
		color:#000000;
	}
	div.selector select {
		background-color:#FFFFFF;
	}
	div.selector.disabled span, div.selector span, div.selector.disabled.active span {
		color:#000000;
		text-align:left;
	}
	div.selector, .ui-widget, input.uniform-input, select.uniform-multiselect, textarea.uniformdetails_zone_table {
		font-size: 11px;
	}
</style>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/jquery-1.9.1.js" charset="UTF-8"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>uniform/jquery.uniform.js"></script>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/ui/jquery-ui.js" charset="UTF-8"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/jquery.blockUI.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>stateduc.js"></script>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<FORM name="Formulaire"  method="post" action="">
    <div align="center">
	      <table class='espace_1_' border='1' style="width : 95%; overflow: auto;" align="center">
            <CAPTION>
            <B><?php echo recherche_libelle_page('ConfExt');?></B>
            </CAPTION>
            <tr class="espace_2"> 
                <td width="30%"><TABLE border='1' align="center"  width="100%">
												<tr> 
													<td><B><?php echo recherche_libelle_page('Secteur');?></B> <br>
													<select style="width : 100%" name="id_systeme"	onChange="reload_page(this.value,'',''); ">
															<?php
															if(is_array($GLOBALS['systemes']))
															foreach ($GLOBALS['systemes'] as $i => $systemes){
																if($systemes['ID_SYSTEME'] == $GLOBALS['id_syst']){
																	echo "<option value='".$systemes['ID_SYSTEME']."'";
																	if ($systemes['ID_SYSTEME'] == $GLOBALS['id_syst']){
																			echo " selected";
																	}
																	echo ">".$systemes['LIBELLE_SYSTEME']."</option>";
																}
															}
											?>
														</select> </td>
												</tr>
											</TABLE><br>
									<TABLE border='1' align="center"  width="100%">
												<tr> 
													<td>
													<B><?php echo recherche_libelle_page('Chaine');?></B> <br>
													<select style="width : 100%"  name="id_chaine" onChange="reload_page(id_systeme.value,this.value,'');">
                                                <?php
												if(is_array($GLOBALS['chaines_systeme']))
												foreach ($GLOBALS['chaines_systeme'] as $i => $chaine_systeme){
														echo "<option value='".$chaine_systeme['CODE_TYPE_CH']."'";
														if ($chaine_systeme['CODE_TYPE_CH'] == $GLOBALS['id_chaine']){
																echo " selected";
														}
														echo ">".$chaine_systeme['LIBELLE_TYPE_CH']."</option>";
												}
												?>
                                            </select> </td>
												</tr>
											</TABLE><br>
											<TABLE border='1' align="center"  width="100%">
												<tr> 
													<td>
													<B><?php echo recherche_libelle_page('TypeLoc'); ?></B> <br>
													<select style="width : 100%" name="type_reg" onChange="reload_page(id_systeme.value, id_chaine.value, this.value);">
														<?php
															foreach ($GLOBALS['type_regs_chaine'] as $i => $type_regs){
																if(!in_array($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']],$_SESSION['fixe_type_reg_parents'])){	
																	echo "<option value='".$type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."'";
																	if ($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']){
																			echo " selected";
																			$GLOBALS['last_lib_type_reg'] = $type_regs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] ;
																	}
																	echo ">".$type_regs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."</option>\n";
																}
															}
															//Ajout Hebie pour choix etablissement
															if($_SESSION['type_rpt']==5 || $_SESSION['type_rpt']==6){
																echo "<option value='etab'";
																if ($GLOBALS['type_reg'] == 'etab'){
																		echo " selected";
																		$GLOBALS['last_lib_type_reg'] = $GLOBALS['PARAM']['ETABLISSEMENT'];
																}
																echo ">".$GLOBALS['PARAM']['ETABLISSEMENT']."</option>";
															}
															//Fin ajout HEBIE
														?>
													</select> </td>
												</tr>
											</TABLE>
                </td>
				<td width="67%" align="center">
				<?php
						$arbre	= new arbre($GLOBALS['id_chaine']);
						
/*						foreach($arbre->chaine as $i=>$c) {
							if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']) {
								$curdepht = $i;
								break;
							}
						} 
*/						
						$curdepht = get_curdepht ($GLOBALS['type_reg'], $arbre) ;
						
						$arbre->type_access='config';
						$entete = $arbre->create_entete(0, $curdepht, true); 
						
						//echo '<br>'.$curdepht.'<br>';
				
						if ( isset($entete['code_regroup']) && ($entete['code_regroup'] <> '') ){
							/*echo "fixe_type_regs<pre>";
							print_r($_SESSION['fixe_type_regs']);
							echo "<br/>type_reg:".$GLOBALS['type_reg']."<pre>";
							print_r($_SESSION['fixe_regs']);*/
							
							if(!in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs'])){
								if($GLOBALS['type_reg']<>'etab'){
									$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, 
												A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG '.
												' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS A, '.$GLOBALS['PARAM']['LIAISONS'].' AS B, 
												'.$GLOBALS['PARAM']['HIERARCHIE'].' AS C 
												WHERE A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = B.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
												AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
												C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
												' AND B.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].' = '.(int)$entete['code_regroup'].
												' AND C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' ='.$GLOBALS['id_chaine'].
												' ORDER BY A.'.$GLOBALS['CHAMP_ORDRE'];
									$last_lib_reg_pere 	= get_lib_reg($entete['code_regroup']) ;
									$last_code_reg_pere = $entete['code_regroup'];
									//echo $requete;
								}elseif($GLOBALS['type_reg']=='etab'){
									//Ajout Hebie pour affichage etablissement
									$requete       = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS CODE_REG,  
															 E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS LIB_REG
															 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R  
															 WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
															 AND E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = '.(int)$entete['code_regroup'].'  
															 AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_syst'].' 
															 ORDER BY E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].', E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
							
									$GLOBALS['liste_etabs'] = $GLOBALS['conn']->GetAll($requete);
									if(isset($_GET['code_etab_choisi']) && trim($_GET['code_etab_choisi']) <> ''){
										$GLOBALS['code_etab_choisi'] = $_GET['code_etab_choisi'] ;
									}else{
										$GLOBALS['code_etab_choisi'] = $GLOBALS['liste_etabs'][0]['CODE_REG'] ;
									}
									
									if(is_array($GLOBALS['liste_etabs']))
									foreach($GLOBALS['liste_etabs'] as $i_etab => $etab){
										if($GLOBALS['code_etab_choisi'] == $etab['CODE_REG']){	
											$GLOBALS['nom_etab_choisi'] = $etab['LIB_REG'] ;
											break;
										}
									}
								}
								
							}else{
								if($GLOBALS['type_reg']<>'etab'){
									$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, 
													A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG '.
													' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS A, '.$GLOBALS['PARAM']['LIAISONS'].' AS B, 
													'.$GLOBALS['PARAM']['HIERARCHIE'].' AS C 
													WHERE A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = B.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
													AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
													C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
													' AND B.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].' = '.(int)$entete['code_regroup'].
													' AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$GLOBALS['type_reg']].')'.
													' AND C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' ='.$GLOBALS['id_chaine'].
													' ORDER BY A.'.$GLOBALS['CHAMP_ORDRE'];
									$last_lib_reg_pere 	= get_lib_reg($entete['code_regroup']) ;
									$last_code_reg_pere = $entete['code_regroup'];
									
								}elseif($GLOBALS['type_reg']=='etab'){
									//Ajout Hebie pour affichage etablissement
									$requete       = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS CODE_REG,  
															 E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS LIB_REG
															 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R  
															 WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
															 AND E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = '.(int)$entete['code_regroup'].'  
															 AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_syst'].' 
															 ORDER BY E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].', E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
							
									$GLOBALS['liste_etabs'] = $GLOBALS['conn']->GetAll($requete);
									if(isset($_GET['code_etab_choisi']) && trim($_GET['code_etab_choisi']) <> ''){
										$GLOBALS['code_etab_choisi'] = $_GET['code_etab_choisi'] ;
									}else{
										$GLOBALS['code_etab_choisi'] = $GLOBALS['liste_etabs'][0]['CODE_REG'] ;
									}
									
									if(is_array($GLOBALS['liste_etabs']))
									foreach($GLOBALS['liste_etabs'] as $i_etab => $etab){
										if($GLOBALS['code_etab_choisi'] == $etab['CODE_REG']){	
											$GLOBALS['nom_etab_choisi'] = $etab['LIB_REG'] ;
											break;
										}
									}
								}
							}
							
						}else {
							if(!in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs'])){
								$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG 
								FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
								WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$GLOBALS['type_reg'].
								' ORDER BY '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['CHAMP_ORDRE'];
							}else{
								$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG 
								FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
								WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$GLOBALS['type_reg'].
								' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$GLOBALS['type_reg']].')'.
								' ORDER BY '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['CHAMP_ORDRE'];
							}	
							$last_lib_reg_pere = $_SESSION['NOM_PAYS'] ;  
							$last_code_reg_pere = 'pays';       
						}
						
						$tab_last_regs 	= $GLOBALS['conn']->GetAll($requete);
						/*if((!isset($GLOBALS['zone']) || $GLOBALS['zone']=='') && (isset($_SESSION['tab_rpt']['zone']) && $_SESSION['tab_rpt']['zone']<>'')){
							$GLOBALS['zone'] = $_SESSION['tab_rpt']['zone'] ;
						}else*/
						if((!isset($GLOBALS['zone']) || $GLOBALS['zone']=='')){
							$GLOBALS['zone'] = $tab_last_regs[0]['CODE_REG'] ;
						}
						//$nb_td_regs = $GLOBALS['nb_td'];
						for( $i = 0 ; $i < $curdepht; $i++ ){
							$tab_reg[$i]['niveau']		= $i + 1 ;
							$tab_reg[$i]['type_reg']	= get_type_reg ($GLOBALS['tab_reg_combo'][$i]['code']) ;
							$tab_reg[$i]['code_reg']	= $GLOBALS['tab_reg_combo'][$i]['code'] ;	
						}
						
						if($GLOBALS['type_reg']<>'etab'){
							$tab_reg[$curdepht]['niveau'] 	= $curdepht + 1 ;
							$tab_reg[$curdepht]['type_reg']	= get_type_reg($GLOBALS['zone']);
							$tab_reg[$curdepht]['code_reg']	= $GLOBALS['zone'] ;	
						}elseif($GLOBALS['type_reg']=='etab'){
							$tab_reg[$curdepht]['niveau'] 	= $curdepht + 1 ;
							$tab_reg[$curdepht]['type_reg']	= 'etab';
							$tab_reg[$curdepht]['code_reg']	= $GLOBALS['zone'] ;	
						}

						echo $entete['html'];
						?>
				<?php
				if($GLOBALS['type_reg']<>'etab'){
					$width = 'width : 100%;';
					$br = '<br/>';
				}else{
					$width = 'width : 50%;';
					$br = '<br/>';
				}
				?>
				<table width="100%" align="left">
				<tr>
                 <td nowrap="nowrap" width="45%">
				  &nbsp;<?php echo $GLOBALS['last_lib_type_reg'].' :'.$br ;?>
				  <select style=" <?php echo $width; ?> " name="zone" onChange="document.Formulaire.submit();">
						<?php
						$deja_sel = false;
						foreach ($tab_last_regs as $i => $reg){
							echo "<option value='".trim($reg['CODE_REG'])."'";
							if (trim($reg['CODE_REG']) == trim($GLOBALS['zone']) && !$deja_sel){
								echo " selected";
								$deja_sel = true;
							}/*elseif (in_array(trim($reg['CODE_REG']),$_SESSION['fixe_reg'][$GLOBALS['type_reg']]) && !$deja_sel){
								echo " selected";
								$deja_sel = true;
							}*/
							echo ">".$reg['LIB_REG']."</option>";
						}
						?>
                   </select>
				 </td>           
				 <?php
				 if($GLOBALS['type_reg']<>'etab'){
				 ?>
				 <td align="right" width="55%"> <br />
					  <?php echo 'All &nbsp; ' . $GLOBALS['last_lib_type_reg'] .' (s) &nbsp;in &nbsp; <span style="color:#0000FF">' . $last_lib_reg_pere . '</span>';?>
				 	<INPUT type="checkbox" id="all_zones_<?php echo $curdepht ;?>" name="all_zones_<?php echo $curdepht ;?>" value="1" 
					<?php
						if($_POST['all_zones_'.$curdepht] == '1' && !in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs'])) echo ' CHECKED' ;
						if(in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs'])) echo ' DISABLED';
					?>
					onClick="ctrl_all_check(<?php echo $curdepht ;?>,<?php echo $curdepht ;?>, <?php echo '\''. $last_code_reg_pere .'\'' ;?>);">
				  </td>
				 <?php
				 }
				 ?>
                 </tr>
				</table>			
				</td>
            </tr>
        </table>
</div>
<INPUT type="hidden" name="niv" value="<?php echo $curdepht ;?>">
<INPUT type="hidden" name="zone_pere" value="<?php echo $_POST['zone_pere'] ;?>">
</FORM>
<?php
	$_SESSION['tab_rpt'] 					= array();
	$_SESSION['tab_rpt']['id_chaine'] 		= $GLOBALS['id_chaine'] ;
	$_SESSION['tab_rpt']['type_reg'] 		= $GLOBALS['type_reg'] ;
	//$_SESSION['tab_rpt']['zone'] 			= $GLOBALS['zone'];
	$_SESSION['tab_rpt']['zone_pere']		= $_POST['zone_pere'] ;
    $_SESSION['tab_rpt']['type_zone_pere']  = get_type_reg($_POST['zone_pere']);
	$_SESSION['tab_rpt']['tab_regs'] 		= $tab_reg ;
	$_SESSION['tab_rpt']['all_zones'] 	= get_all_zones($GLOBALS['type_reg'], $_POST['zone_pere'], $arbre);

	//Ajout Hebie Gestion affichage des niveaux de regroupement IMIS
	if(($_SESSION['type_rpt']==5 || $_SESSION['type_rpt']==6) && $GLOBALS['type_reg']<>'etab'){
		echo "<script type='text/Javascript'>\n";
		echo "reload_page(document.Formulaire.id_systeme.value, document.Formulaire.id_chaine.value, 'etab')\n";
		echo "</script>\n";
	}elseif(($_SESSION['type_rpt']==4 && $GLOBALS['type_reg']=='etab') || ($_GET['type_rpt']==4)){
		echo "<script type='text/Javascript'>\n";
		echo "reload_page(document.Formulaire.id_systeme.value, document.Formulaire.id_chaine.value, ".$GLOBALS['type_regs_chaine'][1][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].")\n";
		echo "</script>\n";
	}
	//Fin ajout
?>