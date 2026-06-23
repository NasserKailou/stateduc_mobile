<?php lit_libelles_page('/fix_regroup.php');
	unset($_SESSION['reg_parents']);
	require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre5.class.php';
	if(isset($_GET['val']) && $_GET['val'] == 'fix_regroup'){
		unset($_SESSION['user_fixe_secteur']);
		unset($_SESSION['user_fixe_chaine']);
		unset($_SESSION['user_fixe_type_reg']);
		unset($_SESSION['user_fixe_type_regs']);
		unset($_SESSION['user_fixe_reg']);
		unset($_SESSION['user_fixe_regs']);
		unset($_SESSION['user_fixe_reg_parents']);
		unset($_SESSION['user_fixe_type_reg_parents']);
		unset($_SESSION['user_fixe_secteurs']);
	}
	if( count($_POST) > 0 ){
			if(isset($_GET['todo']) && $_GET['todo']=='fix'){
				$is_set_reg = false ;
				$is_set_sect = false ;
				$do_action_post = false;
				for( $i = 0 ; $i < $_POST['nb_regs_dispo'] ; $i++ ){
						if(isset($_POST['REGS_'.$i]) and trim($_POST['REGS_'.$i]) <> ''){
								$is_set_reg = true ;
								break;
						}
				}
				for( $i = 0 ; $i < $_POST['nb_sect_dispo'] ; $i++ ){
						if(isset($_POST['SECT_'.$i]) and trim($_POST['SECT_'.$i]) <> ''){
								$is_set_sect = true ;
								break;
						}
				}
				if(!$is_set_reg || !$is_set_sect){
					if(!$is_set_reg) $GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_regs').'"';
					elseif(!$is_set_sect) $GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_sect').'"';
				}elseif( ($is_set_reg == true) && ($is_set_sect == true) ){
						$do_action_post = true;
				}
				if( $do_action_post == true ){
					//echo '<pre>';
					//print_r($_POST);
					
					$_SESSION['cfg_exp'] = $_POST;
					
					$secteur				= $_SESSION['cfg_exp']['id_systeme']; 
					$chaine					= $_SESSION['cfg_exp']['id_chaine']; 
					$type_regroup			= $_SESSION['cfg_exp']['type_reg'];
					
					$regroup = '';
					$k = 0;
					for($i = 0 ; $i < $_SESSION['cfg_exp']['nb_regs_dispo'] ; $i++) {
						if(isset($_SESSION['cfg_exp']['REGS_'.$i]) && trim($_SESSION['cfg_exp']['REGS_'.$i]) <> '' && $k==0){
							$regroup .= $_SESSION['cfg_exp']['REGS_'.$i];
							$k++;
						}elseif(isset($_SESSION['cfg_exp']['REGS_'.$i]) && trim($_SESSION['cfg_exp']['REGS_'.$i]) <> ''){
							$regroup .= ','.$_SESSION['cfg_exp']['REGS_'.$i];
						}
					}
					
					$arbre	= new arbre($chaine);
					foreach($arbre->chaine as $i=>$c) {
							if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $type_regroup) {
									$curdepht = $i;
									break;
							}
					}
					//echo '<br><pre>';
					//print_r($arbre->chaine);
					$regroup_parents = '';
					$type_regroup_parents = '';
					$k = 0;
					for($i=0; $i<$curdepht; $i++) {
						if($k==0) $regroup_parents .= $_SESSION['cfg_exp']['combo_regroup_'.$i];
						else $regroup_parents .= ','.$_SESSION['cfg_exp']['combo_regroup_'.$i];
						if($k==0) $type_regroup_parents .= $arbre->chaine[$i][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
						else $type_regroup_parents .= ','.$arbre->chaine[$i][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
						$k++;
					}
					
					$secteurs = '';
					$k = 0;
					for($i = 0 ; $i < $_SESSION['cfg_exp']['nb_sect_dispo'] ; $i++) {
						if(isset($_SESSION['cfg_exp']['SECT_'.$i]) && trim($_SESSION['cfg_exp']['SECT_'.$i]) <> '' && $k==0){
							$secteurs .= $_SESSION['cfg_exp']['SECT_'.$i];
							$k++;
						}elseif(isset($_SESSION['cfg_exp']['SECT_'.$i]) && trim($_SESSION['cfg_exp']['SECT_'.$i]) <> ''){
							$secteurs .= ','.$_SESSION['cfg_exp']['SECT_'.$i];
						}
					}
					
					$req_fix_regroup = "SELECT * FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER = 0";
					$res_fix_regroup = $GLOBALS['conn_dico']->GetAll($req_fix_regroup);
					if(is_array($res_fix_regroup) && count($res_fix_regroup)>0){
						$upd_fix_regroup = "UPDATE DICO_FIXE_REGROUPEMENT SET ID_SYSTEME=$secteur, ID_CHAINE=$chaine, ID_TYPE_REGROUP=$type_regroup, ID_REGROUP='$regroup', ID_REGROUP_PARENTS='$regroup_parents', ID_TYPE_REGROUP_PARENTS='$type_regroup_parents', ID_SYSTEMES='$secteurs'  WHERE ID_USER = 0";
						$rs_upd_fix_reg = $GLOBALS['conn_dico']->Execute($upd_fix_regroup);
					}else{
						$ins_fix_regroup = "INSERT INTO DICO_FIXE_REGROUPEMENT (ID_USER,ID_SYSTEME, ID_CHAINE, ID_TYPE_REGROUP, ID_REGROUP, ID_REGROUP_PARENTS, ID_TYPE_REGROUP_PARENTS, ID_SYSTEMES) VALUES (0, $secteur, $chaine, $type_regroup, '$regroup', '$regroup_parents', '$type_regroup_parents', '$secteurs') ;";
						$rs_ins_fix_reg = $GLOBALS['conn_dico']->Execute($ins_fix_regroup);
					}

					unset($_SESSION['post_crit_regs']);//liberation des criteres de regroupement en session au niveau de quickreport
		
				}
			}elseif(isset($_GET['todo']) && $_GET['todo']=='clear'){
				$del_fix_regroup = "DELETE FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER = 0";
				$rs_del_fix_reg = $GLOBALS['conn_dico']->Execute($del_fix_regroup);
				unset($_SESSION['user_fixe_secteur']);
				unset($_SESSION['user_fixe_chaine']);
				unset($_SESSION['user_fixe_type_reg']);
				unset($_SESSION['user_fixe_type_regs']);
				unset($_SESSION['user_fixe_reg']);
				unset($_SESSION['user_fixe_regs']);
				unset($_SESSION['user_fixe_reg_parents']);
				unset($_SESSION['user_fixe_type_reg_parents']);
				unset($_SESSION['user_fixe_secteurs']);
				unset($_POST);
			}
	}
	$req_fix_regroup = "SELECT * FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER = 0";
	$res_fix_regroup = $GLOBALS['conn_dico']->GetAll($req_fix_regroup); 
	$_SESSION['user_fixe_regs'] = array();
	$_SESSION['user_fixe_type_regs'] = array();
	$_SESSION['user_fixe_regs0'] = array();
	$_SESSION['user_fixe_reg_parents'] = array();
	$_SESSION['user_fixe_type_reg_parents'] = array();
	$GLOBALS['fixe_user'] = false;
	
	if(is_array($res_fix_regroup) && count($res_fix_regroup)>0){
		$_SESSION['user_fixe_secteur'] = $res_fix_regroup[0]['ID_SYSTEME'];
		$_SESSION['user_fixe_chaine'] = $res_fix_regroup[0]['ID_CHAINE'];
		$_SESSION['user_fixe_type_reg'] = $res_fix_regroup[0]['ID_TYPE_REGROUP'];
		$_SESSION['user_fixe_reg'] = explode(',',$res_fix_regroup[0]['ID_REGROUP']);
		$_SESSION['user_fixe_secteurs'] = explode(',',$res_fix_regroup[0]['ID_SYSTEMES']);
		if($res_fix_regroup[0]['ID_REGROUP_PARENTS']<>'') $_SESSION['user_fixe_reg_parents'] = explode(',',$res_fix_regroup[0]['ID_REGROUP_PARENTS']);
		if($res_fix_regroup[0]['ID_TYPE_REGROUP_PARENTS']<>'') $_SESSION['user_fixe_type_reg_parents'] = explode(',',$res_fix_regroup[0]['ID_TYPE_REGROUP_PARENTS']);
		if($res_fix_regroup[0]['ID_REGROUP_PARENTS']<>'') $_SESSION['user_fixe_regs0'] = explode(',',$res_fix_regroup[0]['ID_REGROUP_PARENTS']);
		$_SESSION['user_fixe_regs0'][] = $res_fix_regroup[0]['ID_REGROUP'];
		if ($res_fix_regroup[0]['ID_TYPE_REGROUP_PARENTS']<>'') $_SESSION['user_fixe_type_regs'] = explode(',',$res_fix_regroup[0]['ID_TYPE_REGROUP_PARENTS']);
		$_SESSION['user_fixe_type_regs'][] = $res_fix_regroup[0]['ID_TYPE_REGROUP'];
		for($i=0; $i<count($_SESSION['user_fixe_type_regs']); $i++){
			$_SESSION['user_fixe_regs'][$_SESSION['user_fixe_type_regs'][$i]] = $_SESSION['user_fixe_regs0'][$i];
		}
		$GLOBALS['fixe_user'] = true;
	}
	
?>
<script type="text/Javascript">

		function reload_page(syst, ch, type_reg) {
				location.href= '?val=fix_regroup&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg;
		}

		/*function manage_check_2(var1, var2)
		{	
			var chaine_eval1 ='document.getElementById("' + var1 + '").checked = 1;';
			eval(chaine_eval1);
			var i = 0 ;
			while ( document.getElementById( var2 + '_' + i ) ){
				if((var2 + '_' + i) != var1){
					var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 0;';
					eval(chaine_eval2);
				}
				i++;
			}
		}*/
		
		function changer_action(todo){
			document.forms['Formulaire'].action = "administration.php?val=fix_regroup&todo="+todo ;
			//alert(document.forms['Formulaire'].action);
		}
		var do_submit = true;
		function do_post(form_name){
			if( do_submit == true ){
				eval('document.'+form_name+'.submit();');
			}
		}
</script>

<?php function get_systemes(){/// get_systemes()
				$conn 		= $GLOBALS['conn'];
        // Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
        // TODO: à virer de l'accueil
      	
        $requete                 = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme,
                                    D_TRAD.LIBELLE as libelle_systeme
                                    FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
                                    WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
                                    AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\'
									ORDER BY T_S_E.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
																		//echo $requete;
				try {            
						$GLOBALS['systemes'] = $conn->GetAll($requete);
						if(!is_array($GLOBALS['systemes'])){                
								 throw new Exception('ERR_SQL');   
						}
						if( isset($_GET['id_systeme']) and (trim($_GET['id_systeme'])<>'') ){
								$GLOBALS['id_systeme'] 	= $_GET['id_systeme'];
						}elseif(isset($_POST['id_systeme'])){
								$GLOBALS['id_systeme'] 	= $_POST['id_systeme'];
						}elseif(isset($_SESSION['user_fixe_secteur']) && $_SESSION['user_fixe_secteur']<>''){
								$GLOBALS['id_systeme'] 	= $_SESSION['user_fixe_secteur'];
						}elseif(isset($_SESSION['secteur']) && $_SESSION['secteur']<>''){
								$GLOBALS['id_systeme'] 	= $_SESSION['secteur'];
						}
						if(!isset($GLOBALS['id_systeme'])){
								$GLOBALS['id_systeme'] 	= $GLOBALS['systemes'][0]['id_systeme'];
						}
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$requete);
				} 
			// echo $requete .'<br>';
    } // FIN  get_systemes()

		function get_chaines_systeme(){
				
				$conn 		= $GLOBALS['conn'];
        $requete     = 'SELECT    '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' as CODE_TYPE_CHAINE,
                        '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 	as LIBELLE_TYPE_CHAINE
                        FROM      '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
                        WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_systeme'] ;
				try {            
						$GLOBALS['chaines_systeme'] = $conn->GetAll($requete);
						if(!is_array($GLOBALS['chaines_systeme'])){                
								 throw new Exception('ERR_SQL');   
						}
						if(isset($_GET['id_chaine']) and (trim($_GET['id_chaine'])<>'') ){
								$GLOBALS['id_chaine'] 	= $_GET['id_chaine'];
						}elseif(isset($_POST['id_chaine'])){
								$GLOBALS['id_chaine'] 	= $_POST['id_chaine'];
						}elseif(isset($_SESSION['user_fixe_chaine']) && $_SESSION['user_fixe_chaine']<>''){
								$GLOBALS['id_chaine'] 	= $_SESSION['user_fixe_chaine'];
						}
						if(!isset($GLOBALS['id_chaine'])){
								$GLOBALS['id_chaine'] 	= $GLOBALS['chaines_systeme'][0]['CODE_TYPE_CHAINE'];
						}
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$requete);
				} 
		}
		 
		function get_type_regs_chaine(){
				
				$arbre = new arbre($GLOBALS['id_chaine']);
				$GLOBALS['type_regs_chaine'] = $arbre->chaine ;
				if(isset($_GET['type_reg']) and (trim($_GET['type_reg'])<>'') ){
						$GLOBALS['type_reg'] 	= $_GET['type_reg'];
				}elseif(isset($_POST['type_reg'])){
						$GLOBALS['type_reg'] 	= $_POST['type_reg'];
				}elseif(isset($_SESSION['user_fixe_type_reg']) && $_SESSION['user_fixe_type_reg']<>''){
						$GLOBALS['type_reg'] 	= $_SESSION['user_fixe_type_reg'];
				}
				if(!isset($GLOBALS['type_reg'])){
						$GLOBALS['type_reg'] 	= $GLOBALS['type_regs_chaine'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
				}
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
													$html .=' alt="'.$tab[$i][$lib].'"';
													if(isset($_POST[$chp_check.'_'.$i]) and trim($_POST[$chp_check.'_'.$i]) <> ''){
														$html .= ' CHECKED';
													}elseif(isset($_SESSION['user_fixe_reg']) && in_array(trim($tab[$i][$code]),$_SESSION['user_fixe_reg']) && $GLOBALS['fixe_user']){
														$html .= ' CHECKED';
													}elseif(isset($_SESSION['user_fixe_secteurs']) && in_array(trim($tab[$i][$code]),$_SESSION['user_fixe_secteurs']) && $GLOBALS['fixe_user']){
														$html .= ' CHECKED';
													}
													else{
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
						$html .= "\n" . '</TABLE>';
				}
				return ($html);
		}
		
		get_systemes();
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
</style>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<FORM name="Formulaire"  method="post" action="">
    <div align="center">
	      <table class='espace_2' border='1' style="overflow: auto;" width="51%">
            <CAPTION>
            <B><?php echo recherche_libelle_page('EntFixRegroup');?></B>
            </CAPTION>
            <tr class="espace_2"> 
                <td>
					<BR/>
					 <TABLE class='espace_2' border='1'  width="98%">
                        <CAPTION>
                        <B><?php echo recherche_libelle_page('Localite');?></B> 
                        </CAPTION>
						<tr>
							<td colspan="2">
								<table border='1' class="espace_2" width="98%">
								<tr> 
								<td> <TABLE border='1' align="center"  width="100%">
										<tr>
											<td><?php echo recherche_libelle_page('Secteur');?> :</td>
										</tr>
										<tr> 
											<td><select name="id_systeme"	onChange="reload_page(this.value,id_chaine.value,type_reg.value); ">
													<?php if(is_array($GLOBALS['systemes']))
													foreach ($GLOBALS['systemes'] as $i => $systemes){
															echo "<option value='".$systemes['id_systeme']."'";
															if ($systemes['id_systeme'] == $GLOBALS['id_systeme']){
																	echo " selected";
															}
															echo ">".$systemes['libelle_systeme']."</option>";
													}
									?>
												</select> </td>
										</tr>
									</TABLE>
									</td>
									<td> 
									<TABLE border='1' align="center"  width="100%">
										<tr>
											<td><?php echo recherche_libelle_page('Chaine');?> :</td>
										</tr>
										<tr> 
											<td><select name="id_chaine" onChange="reload_page(id_systeme.value,this.value,type_reg.value);">
									 <?php if(is_array($GLOBALS['chaines_systeme']))
													foreach ($GLOBALS['chaines_systeme'] as $i => $chaine_systeme){
															echo "<option value='".$chaine_systeme['CODE_TYPE_CHAINE']."'";
															if ($chaine_systeme['CODE_TYPE_CHAINE'] == $GLOBALS['id_chaine']){
																	echo " selected";
															}
															echo ">".$chaine_systeme['LIBELLE_TYPE_CHAINE']."</option>";
													}
									?>
												</select> </td>
										</tr>
									</TABLE>
									</td>
									<td>
									<TABLE border='1' align="center"  width="100%">
										<tr>
											<td><?php echo recherche_libelle_page('TypeLocalite');?> :</td>
										</tr>
										<tr> 
										  <td><select name="type_reg" onchange="reload_page(id_systeme.value, id_chaine.value, this.value);">
											<?php foreach ($GLOBALS['type_regs_chaine'] as $i => $type_regs){
												echo "<option value='".$type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."'";
												if ($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']){ //Cas general
												//if ($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == 2){ //District: Cas Tanzanie
														echo " selected";
												}
												echo ">".$type_regs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."</option>";
										}
								?>
										  </select></td>
										</tr>
									</TABLE>
									</td>
								</tr>
							</table>
							</td>
						</tr>
                        <tr> 
						<?php $arbre	= new arbre($GLOBALS['id_chaine']);
								foreach($arbre->chaine as $i=>$c) {
										if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']) { //Cas General
										//if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == 2) { //Cas Tanzanie
												$curdepht = $i;
												break;
										}
								} 
								$arbre->type_access='config';
								$entete = $arbre->create_entete(0, $curdepht, true); 
								if ( isset($entete['code_regroup']) && ($entete['code_regroup'] <> '') ){
									if(!in_array($GLOBALS['type_reg'],$_SESSION['user_fixe_type_regs'])){
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
																	' AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['user_fixe_regs'][$GLOBALS['type_reg']].')'.
																	' AND C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' ='.$GLOBALS['id_chaine'].
																	' ORDER BY A.'.$GLOBALS['CHAMP_ORDRE'].';';  
									}
								}else {
									if(!in_array($GLOBALS['type_reg'],$_SESSION['user_fixe_type_regs'])){
										$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG 
											FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
											WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$GLOBALS['type_reg'].
											' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';  
									}else{
										$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG 
											FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
											WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$GLOBALS['type_reg'].
											' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['user_fixe_regs'][$GLOBALS['type_reg']].')'.
											' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';
									}          
								}
								//echo $requete;
								$tab_regs 	= $GLOBALS['conn']->GetAll($requete);
								$nb_td_regs =	$GLOBALS['nb_td'];

						if( isset($entete['code_regroup']) and trim($entete['code_regroup']) <> ''){
								$combos_regs = preg_replace('/combo_regroups/', 'Formulaire', preg_replace("/(<form name=\"combo_regroups\">)|(</form>)|(<br />)/", '', $entete['html']));
								$combos_regs = preg_replace('/<br>([[:blank:]]|[[:space:]])*</div>/', '</div>', $combos_regs);
								$combos_regs = str_replace('<select', '<select  style=\'width:100%;\'',$combos_regs); ;
								echo'<td align="center" valign="middle" width="25%"><table width="100%" height="100%"><tr><td align="center">' . $combos_regs . '</td></tr></table></td>';
								if($nb_td_regs > 1){
										$nb_td_regs = $nb_td_regs - 1 ;
								}
						}?>
						<td align="center" valign="middle"  width="75%"> 
								<?php print (tableau_check( $tab_regs, 'CODE_REG', 'LIB_REG', 'REGS', 'toutes', 'ALL_REGS', $nb_td_regs )); ?>
								<INPUT type="hidden" name="nb_regs_dispo" value="<?php echo count($tab_regs);?>">
						</td>
                        </tr>
                    </TABLE>
					<BR/>
					<TABLE class='espace_2' border='1'  width="98%">
                        <CAPTION>
                        <B><?php echo recherche_libelle_page('Secteurs');?></B> 
                        </CAPTION>
                        <tr> 
						<?php
							if(!isset($_SESSION['user_fixe_secteurs']) || count($_SESSION['user_fixe_secteurs'])==0){
								$requete = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme,
										D_TRAD.LIBELLE as libelle_systeme
										FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
										WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
										AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\'
										ORDER BY T_S_E.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
							}else{
								$requete = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme,
										D_TRAD.LIBELLE as libelle_systeme
										FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
										WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
										AND T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['user_fixe_secteurs']).')
										AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\'
										ORDER BY T_S_E.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
							}
							$tab_sect 	= $GLOBALS['conn']->GetAll($requete);;
							$nb_td_sect =	$GLOBALS['nb_td'];
						?>
						<td align="center" valign="middle"  width="75%"> 
								<?php print (tableau_check( $tab_sect, 'id_systeme', 'libelle_systeme', 'SECT', 'toutes', 'ALL_SECT', $nb_td_sect )); ?>
								<INPUT type="hidden" name="nb_sect_dispo" value="<?php echo count($tab_sect);?>">
						</td>
                        </tr>
                    </TABLE>
                    <br/>
              </td>
            </tr>
            <tr> 
                <td align="center"> 
                        <TABLE style="width:60%;"  border='1'>
                            <tr> 
                               <td align='center' nowrap="nowrap">
									<INPUT   style="width:50%;"  type='button' <?php echo 'value="'.recherche_libelle_page('save').'"';?> onclick="changer_action('fix'); do_post('Formulaire');"/>&nbsp;&nbsp;&nbsp;
									<INPUT   style="width:45%;"  type="button" <?php echo 'value="'.recherche_libelle_page('clear').'"';?>  onclick="changer_action('clear'); do_post('Formulaire');" />
								</td>
                            </tr>
                        </TABLE>
                </td>
            </tr>
      </table>
</div>

</FORM>
