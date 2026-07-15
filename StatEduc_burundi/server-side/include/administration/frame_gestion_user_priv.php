<?php
	unset($_SESSION['reg_parents']);
	unset($_SESSION['user_fixe_campagne']);
	unset($_SESSION['user_fixe_secteur']);
	unset($_SESSION['user_fixe_chaine']);
	unset($_SESSION['user_fixe_type_reg']);
	unset($_SESSION['user_fixe_type_regs']);
	unset($_SESSION['user_fixe_reg']);
	unset($_SESSION['user_fixe_regs']);
	unset($_SESSION['user_fixe_reg_parents']);
	unset($_SESSION['user_fixe_type_reg_parents']);
	unset($_SESSION['user_fixe_secteurs']);
	unset($_SESSION['user_fixe_chaines']);
	require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre5.class.php';
	if(isset($_GET['id_user']) && $_GET['id_user']<> ''){
		$_SESSION['id_user'] = $_GET['id_user'];
		if(isset($_GET['name_user']) && $_GET['name_user']<>'') $_SESSION['name_user'] = $_GET['name_user'];
		if(isset($_GET['id_groupe']) && $_GET['id_groupe']<>'') $_SESSION['id_groupe'] = $_GET['id_groupe'];
	}
	if(isset($_GET['val']) && $_GET['val']<> '' ){
		$_SESSION['val'] = $_GET['val'];
	}
	if(isset($_GET['action_new']) && $_GET['action_new']== 'New' && (!isset($_POST['action']) || $_POST['action']== '')){
		unset($val);
	}
	if(isset($_POST['action']) && $_POST['action']<> ''){
		unset($_GET);
	}
	if(!isset($val['USER_PRIV']) || $val['USER_PRIV']==''){
		$_SESSION['action_new']= 'New';
	}else{
		unset($_SESSION['action_new']);
	}
	$_SESSION['user_fixe_regs'] = array();
	$_SESSION['user_fixe_type_regs'] = array();
	$_SESSION['user_fixe_regs0'] = array();
	$_SESSION['user_fixe_reg_parents'] = array();
	$_SESSION['user_fixe_type_reg_parents'] = array();
	$GLOBALS['fixe_user'] = false;
	if(isset($val['USER_PRIV']) && $val['USER_PRIV']<>''){
		$_SESSION['user_fixe_campagne']=	$val['ID_CAMPAGNE'];
		$_SESSION['user_fixe_secteur']=	$val['ID_SYSTEME'];
		$_SESSION['user_fixe_chaine']=	$val['ID_CHAINE'];
		$_SESSION['user_fixe_type_reg']=	$val['ID_TYPE_REGROUP'];
		$_SESSION['user_fixe_reg']=	explode(',',$val['ID_REGROUP']);
		if($val['ID_REGROUP_PARENTS']<>'') $_SESSION['user_fixe_reg_parents']=	explode(',',$val['ID_REGROUP_PARENTS']);//A revoir si necessaire
		if($val['ID_TYPE_REGROUP_PARENTS']<>'') $_SESSION['user_fixe_type_reg_parents']=	explode(',',$val['ID_TYPE_REGROUP_PARENTS']);//A revoir si necessaire
		if($val['ID_REGROUP_PARENTS']<>'') $_SESSION['user_fixe_regs0']= explode(',',$val['ID_REGROUP_PARENTS']);
		$_SESSION['user_fixe_regs0'][] = $val['ID_REGROUP'];
		if ($val['ID_TYPE_REGROUP_PARENTS']<>'') $_SESSION['user_fixe_type_regs'] = explode(',',$val['ID_TYPE_REGROUP_PARENTS']);//A revoir si necessaire
		$_SESSION['user_fixe_type_regs'][] = $val['ID_TYPE_REGROUP'];//A revoir si necessaire
		for($i=0; $i<count($_SESSION['user_fixe_type_regs']); $i++){
			$_SESSION['user_fixe_regs'][$_SESSION['user_fixe_type_regs'][$i]] = $_SESSION['user_fixe_regs0'][$i];//A revoir si necessaire
		}
		$GLOBALS['fixe_user'] = true;
	}elseif($_SESSION['id_user']<>0){
		if(isset($_SESSION['fixe_regs']) && count($_SESSION['fixe_regs'])>0){
			$_SESSION['user_fixe_regs'] = $_SESSION['fixe_regs'];
			$_SESSION['user_fixe_type_regs'] = $_SESSION['fixe_type_regs'];
			if(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs']>0)) $_SESSION['user_fixe_secteurs'] = $_SESSION['fixe_secteurs'];
		}
	}
	if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT'] <> '') get_campagnes();
?>
<script type="text/Javascript">
		function reload_page(val,syst,ch,type_reg,action,camp='',status='',prd='') {
			<?php if(is_array($GLOBALS['campagnes'])){ ?>
				location.href= '?val='+val+'&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg+'&action_new='+action+'&id_campagne='+camp+'&id_status='+status+'&id_periode='+prd+'&id_user=<?php echo $_SESSION['id_user']; if(isset($_SESSION['name_user']) && $_SESSION['name_user']<>'') echo "&name_user=".$_SESSION['name_user'];?>';
			<?php }else{ ?>
				location.href= '?val='+val+'&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg+'&action_new='+action+'&id_user=<?php echo $_SESSION['id_user']; if(isset($_SESSION['name_user']) && $_SESSION['name_user']<>'') echo "&name_user=".$_SESSION['name_user'];?>';
			<?php } ?>
		}
		function init_user_priv(val) {
			//alert("ici");
			location.href= '?val='+val+'&id_user=<?php echo $_SESSION['id_user']; if(isset($_SESSION['name_user']) && $_SESSION['name_user']<>'') echo "&name_user=".$_SESSION['name_user'];?>';
		}
		function changer_action(todo){
			document.forms['Formulaire'].elements['todo'].value = todo;
		}
		var do_submit = true;
		function do_post(form_name){
			if( do_submit == true){
				eval('document.'+form_name+'.submit();');
			}
		}
</script>
<?php
		function est_ds_tableau($elem,$tab){
			if(is_array($tab)){
				foreach($tab as $elements){
					if( $elements == $elem ){
						return true;
					}	
				}	
			}	
		}

		function get_criteres_where($champ,$table,$array){
			if(count($array)){
				$crit_in = array();
				foreach($array as $i_elem => $elem){
					if(!est_ds_tableau($elem[$champ], $crit_in)){
						$crit_in[] = $elem[$champ] ;
					}
				}
				$where = ' '.$table.'.'.$champ . ' IN ( '.implode(', ',$crit_in).' ) ';
				return $where ;
			}	
		}
	    function get_periodes(){
				$conn = $GLOBALS['conn'];
				$requete     = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'].' as id_periode, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'].' as libelle_periode
						FROM '.$GLOBALS['PARAM']['TYPE_PERIODE'].'
						ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'].';';
				try {            
						  $GLOBALS['periodes'] = $conn->GetAll($requete);
						$GLOBALS['periodes'] = array_change_key_case_recursive($GLOBALS['periodes'], CASE_LOWER);
					$_SESSION['periodes'] = $GLOBALS['periodes'];
					if(!is_array($GLOBALS['periodes'])){                
										 throw new Exception('ERR_SQL');   
								}
					unset($GLOBALS['id_periode']);
								if( isset($_GET['id_periode']) and (trim($_GET['id_periode'])<>'') ){
										$GLOBALS['id_periode'] 	= $_GET['id_periode'];
								}
								if(!isset($GLOBALS['id_periode'])){
										$GLOBALS['id_periode'] 	= $GLOBALS['periodes'][0]['id_periode'];
								}
								$_SESSION['id_periode'] = $GLOBALS['id_periode'];
				}catch (Exception $e) {
						 $erreur = new erreur_manager($e,$requete);
				}    
	    }
		function get_campagnes(){
				$conn 		= $GLOBALS['conn'];
	            $requete     = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as id_campagne, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as libelle_campagne
							FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
							ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].';';
				try {            
						$GLOBALS['campagnes'] = $conn->GetAll($requete);
						$GLOBALS['campagnes'] = array_change_key_case_recursive($GLOBALS['campagnes'], CASE_LOWER);
						$_SESSION['campagnes'] = $GLOBALS['campagnes'];
						if(!is_array($GLOBALS['campagnes'])){                
								 throw new Exception('ERR_SQL');   
						}
						unset($GLOBALS['id_campagne']);
						if( isset($_GET['id_campagne']) and (trim($_GET['id_campagne'])<>'') ){
								$GLOBALS['id_campagne'] 	= $_GET['id_campagne'];
						}elseif(isset($_SESSION['user_fixe_campagne']) && $_SESSION['user_fixe_campagne']<>''){
								$GLOBALS['id_campagne'] 	= $_SESSION['user_fixe_campagne'];
						}
						if(!isset($GLOBALS['id_campagne'])){
								$GLOBALS['id_campagne'] 	= $GLOBALS['campagnes'][0]['id_campagne'];
						}
						$_SESSION['id_campagne'] = $GLOBALS['id_campagne'];
				}catch (Exception $e) {
						 $erreur = new erreur_manager($e,$requete);
				}
    	}
      
	      function get_status(){
					$conn 		= $GLOBALS['conn'];
		            $requete     = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT'].' as id_status, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT'].' as libelle_status
								FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT'].' 
								ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT'].';';
					try {            
							$GLOBALS['status'] = $conn->GetAll($requete);
							$GLOBALS['status'] = array_change_key_case_recursive($GLOBALS['status'], CASE_LOWER);
							if(!is_array($GLOBALS['status'])){                
									 throw new Exception('ERR_SQL');   
							}
							unset($GLOBALS['id_status']);
							if( isset($_GET['id_status']) and (trim($_GET['id_status'])<>'') ){
									$GLOBALS['id_status'] 	= $_GET['id_status'];
							}/*elseif(isset($_SESSION['user_fixe_status']) && $_SESSION['user_fixe_status']<>''){
									$GLOBALS['id_status'] 	= $_SESSION['user_fixe_status'];
							} */
							if(!isset($GLOBALS['id_status'])){
									$GLOBALS['id_status'] 	= $GLOBALS['status'][0]['id_status'];
							}
					}catch (Exception $e) {
							 $erreur = new erreur_manager($e,$requete);
					}
	    }
    
		function get_systemes(){
				
				$conn 		= $GLOBALS['conn'];
        		if(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0 && $_SESSION['id_user']<>0){
					$requete                 = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme, D_TRAD.LIBELLE as libelle_systeme
										FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
										WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
										AND T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['fixe_secteurs']).')
										AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\'
										ORDER BY T_S_E.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
				}else{
					$requete                 = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme, D_TRAD.LIBELLE as libelle_systeme
										FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
										WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
										AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\'
										ORDER BY T_S_E.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].';';
				}
				try {            
						$GLOBALS['systemes'] = $conn->GetAll($requete);
						$GLOBALS['systemes'] =  array_change_key_case_recursive($GLOBALS['systemes'], CASE_LOWER);
						$_SESSION['systemes'] = $GLOBALS['systemes'];
						if(!is_array($GLOBALS['systemes'])){                
								 throw new Exception('ERR_SQL');   
						}
						unset($GLOBALS['id_systeme']);
						if( isset($_GET['id_systeme']) and (trim($_GET['id_systeme'])<>'') ){
								$GLOBALS['id_systeme'] 	= $_GET['id_systeme'];
						}elseif(isset($_SESSION['user_fixe_secteur']) && $_SESSION['user_fixe_secteur']<>''){
								$GLOBALS['id_systeme'] 	= $_SESSION['user_fixe_secteur'];
						}elseif(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0 && $_SESSION['id_user']<>0){
								$GLOBALS['id_systeme'] 	= $_SESSION['fixe_secteurs'][0];
						}/*elseif(isset($_SESSION['secteur']) && $_SESSION['secteur']<>''){
								$GLOBALS['id_systeme'] 	= $_SESSION['secteur'];
						}*/
						if(!isset($GLOBALS['id_systeme'])){
								$GLOBALS['id_systeme'] 	= $GLOBALS['systemes'][0]['id_systeme'];
						}
						$_SESSION['id_systeme'] = $GLOBALS['id_systeme'];
				}catch (Exception $e) {
						 $erreur = new erreur_manager($e,$requete);
				} 
    	}

		function get_chaines_systeme(){
				
				$conn 		= $GLOBALS['conn'];
				if(!(isset($_SESSION['fixe_chaines']) && count($_SESSION['fixe_chaines'])>0 && $_SESSION['id_user']<>0)){
					$requete     = 'SELECT    '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' as CODE_TYPE_CH,
                        '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 	as LIBELLE_TYPE_CH
                        FROM      '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
                        WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_systeme'] ;
				}else{
					$requete     = 'SELECT    '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' as CODE_TYPE_CH,
                        '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 	as LIBELLE_TYPE_CH
                        FROM      '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
                        WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_systeme'].
						' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' IN ('.implode(',',$_SESSION['fixe_chaines'][$GLOBALS['id_systeme']]).');';
				}
        		

				try {            
						$GLOBALS['chaines_systeme'] = $conn->GetAll($requete);
						$_SESSION['chaines_systeme'] = $GLOBALS['chaines_systeme'];
						if(!is_array($GLOBALS['chaines_systeme'])){                
								 throw new Exception('ERR_SQL');   
						}
						unset($GLOBALS['id_chaine']);
						if(isset($_GET['id_chaine']) and (trim($_GET['id_chaine'])<>'') ){
								$GLOBALS['id_chaine'] 	= $_GET['id_chaine'];
						}elseif(isset($_SESSION['user_fixe_chaine']) && $_SESSION['user_fixe_chaine']<>''){
								$GLOBALS['id_chaine'] 	= $_SESSION['user_fixe_chaine'];
						}elseif(isset($_SESSION['fixe_chaines'][$_SESSION['id_systeme']]) && count($_SESSION['fixe_chaines'][$_SESSION['id_systeme']])>0 && $_SESSION['id_user']<>0){
								$GLOBALS['id_chaine'] 	= $_SESSION['fixe_chaines'][$_SESSION['id_systeme']][0];
						}
						if(!isset($GLOBALS['id_chaine'])){
								$GLOBALS['id_chaine'] 	= $GLOBALS['chaines_systeme'][0]['CODE_TYPE_CH'];
								
						}
						$_SESSION['id_chaine'] = $GLOBALS['id_chaine'];

				}catch (Exception $e) {
						 $erreur = new erreur_manager($e,$requete);
				} 
		}
		 
		function get_type_regs_chaine(){
				
				$arbre = new arbre($GLOBALS['id_chaine']);
				$GLOBALS['type_regs_chaine'] = $arbre->chaine ;
				$_SESSION['type_regs_chaine'] = $GLOBALS['type_regs_chaine'];
				unset($GLOBALS['type_reg']);
				if(isset($_GET['type_reg']) and (trim($_GET['type_reg'])<>'') ){
					$GLOBALS['type_reg'] 	= $_GET['type_reg'];
				}elseif(isset($_SESSION['user_fixe_type_reg']) && $_SESSION['user_fixe_type_reg']<>''){
					$GLOBALS['type_reg'] 	= $_SESSION['user_fixe_type_reg'];
				}elseif(isset($_SESSION['fixe_type_reg'][$_SESSION['id_chaine']]) && count($_SESSION['fixe_type_reg'][$_SESSION['id_chaine']])>0 && $_SESSION['id_user']<>0){
					$GLOBALS['type_reg'] = $_SESSION['fixe_type_reg'][$_SESSION['id_chaine']][0];
				}
				if(!isset($GLOBALS['type_reg'])){
					$GLOBALS['type_reg'] 	= $GLOBALS['type_regs_chaine'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
				}
				
				$_SESSION['type_reg'] = $GLOBALS['type_reg'];
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
								if(isset($_SESSION['user_fixe_reg']) && in_array(trim($tab[$i][$code]),$_SESSION['user_fixe_reg']) && $GLOBALS['fixe_user']){
									$html .= ' CHECKED';
								}elseif(in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_reg_parents'])){
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
			$html .= "\n" . '</TABLE>';
		}
		return ($html);
	}
	
  	if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT'] <> '') get_status();
  	if(isset($GLOBALS['PARAM']['TYPE_PERIODE']) && $GLOBALS['PARAM']['TYPE_PERIODE'] <> '') get_periodes();
	get_systemes();
	get_chaines_systeme();
	get_type_regs_chaine();

	$GLOBALS['nb_td'] = 4;
	$libelle_year = '';	
	if(is_array($GLOBALS['campagnes']) && is_array($GLOBALS['status']) && is_array($GLOBALS['periodes'])){
			$req_lib_year = 'SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' AS lib_year'.
		                  ' FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].
		                  ' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'];
			$libelle_year = $GLOBALS['conn']->GetOne($req_lib_year);
		}
	?>

<table width="600" border="2" align="center">
	<?php if($libelle_year <> ''){?>
		<tr>
		  <td colspan="4" nowrap="nowrap"><?php echo recherche_libelle_page('annee'); ?> : <b>&nbsp;&nbsp;<?php echo $libelle_year; ?></b>
		  <input type="hidden" name="ID_ANNEE" id="ID_ANNEE" value="<?php echo $_SESSION['annee']; ?>" /></td>
		</tr>
	<?php }else{ ?>
		<input type="hidden" name="ID_ANNEE" id="ID_ANNEE" value="0" />
	<?php } ?>
	<tr>
	  <td colspan="4" nowrap="nowrap"><?php if(isset($_SESSION['name_user'])) echo recherche_libelle_page('user_name')." : "; ?><b>&nbsp;&nbsp;<?php echo $_SESSION['name_user']; ?></b>
	  <input type="hidden" name="ID_USER" id="ID_USER" value="<?php echo $val['ID_USER']; ?>" />	  </td>
	</tr>
	<tr>
	  <td colspan="4" nowrap="nowrap"><?php echo recherche_libelle_page('user_priv'); ?> : <b>&nbsp;&nbsp;<?php echo $val['USER_PRIV']; ?></b>
	  <input type="hidden" name="USER_PRIV" id="USER_PRIV" value="<?php echo $val['USER_PRIV']; ?>" />	 </td>
	</tr>
	<tr><td colspan="4" nowrap="nowrap" style="height:5px"></td></tr>
	<?php if(is_array($GLOBALS['campagnes']) && is_array($GLOBALS['status']) && is_array($GLOBALS['periodes'])){?>
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('Campagne');?> :</td>
	  <td colspan="2" nowrap="nowrap" width="65%">
	  
	  <select name="ID_CAMPAGNE"	onchange="reload_page('<?php echo $_SESSION['val'];?>','','','','New',this.value,ID_STATUS.value,ID_PERIODE.value); ">
		<?php 
			if(is_array($GLOBALS['campagnes'])){
				if($val['ID_CAMPAGNE']=='')	echo "<option value='0'></option>\n";
				foreach ($GLOBALS['campagnes'] as $i => $campagne){
					if($val['ID_CAMPAGNE']<>'' && $val['ID_CAMPAGNE']==$campagne['id_campagne']){
						echo "<option value='".$campagne['id_campagne']."' selected>".trim($campagne['libelle_campagne'])."</option>";
						break;
					}elseif(!isset($val['ID_CAMPAGNE']) || $val['ID_CAMPAGNE']==''){
						echo "<option value='".$campagne['id_campagne']."'";
						if ($campagne['id_campagne'] == $GLOBALS['id_campagne']){
								echo " selected";
						}
						echo ">".trim($campagne['libelle_campagne'])."</option>\n";
					}
				}
			}
				
			?>
      </select></td>
	</tr>
	<?php }else{ ?>
	<input type="hidden" name="ID_CAMPAGNE" id="ID_CAMPAGNE" value="0" />
	<?php } ?>
	<?php if(is_array($GLOBALS['campagnes']) && is_array($GLOBALS['status']) && is_array($GLOBALS['periodes'])){ ?>
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('statut_camp');?> :</td>
	  <td colspan="2" nowrap="nowrap" width="65%">
	  <select name="ID_STATUS"	onchange="reload_page('<?php echo $_SESSION['val'];?>','','','','New',ID_CAMPAGNE.value,this.value,ID_PERIODE.value); ">
		<?php 
			if(is_array($GLOBALS['status'])){
				if($val['ID_STATUS']=='')	echo "<option value='0'></option>\n";
				foreach ($GLOBALS['status'] as $i => $status){
					if($val['ID_STATUS']<>'' && $val['ID_STATUS']==$status['id_status']){
						echo "<option value='".$status['id_status']."' selected>".$status['libelle_status']."</option>";
						break;
					}elseif(!isset($val['ID_STATUS']) || $val['ID_STATUS']==''){
						echo "<option value='".$status['id_status']."'";
						if ($status['id_status'] == $GLOBALS['id_status']){
								echo " selected";
						}
						echo ">".$status['libelle_status']."</option>\n";
					}
				}
			}
				
			?>
      </select></td>
	</tr>
	<?php }else{ ?>
		<input type="hidden" name="ID_STATUS" id="ID_STATUS" value="0" />
	<?php } ?>
	<?php if(is_array($GLOBALS['campagnes']) && is_array($GLOBALS['status']) && is_array($GLOBALS['periodes'])){ ?>
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('Periode');?> :</td>
	  <td colspan="2" nowrap="nowrap" width="65%">
	  <select name="ID_PERIODE"	onchange="reload_page('<?php echo $_SESSION['val'];?>','','','','New',ID_CAMPAGNE.value,ID_STATUS.value,this.value); ">
		<?php 
			if(is_array($GLOBALS['periodes'])){
				if($val['ID_PERIODE']=='')	echo "<option value='0'></option>\n";
				foreach ($GLOBALS['periodes'] as $i => $periodes){
					if($val['ID_PERIODE']<>'' && $val['ID_PERIODE']==$periodes['id_periode']){
						echo "<option value='".$periodes['id_periode']."' selected>".$periodes['libelle_periode']."</option>";
						break;
					}elseif(!isset($val['ID_PERIODE']) || $val['ID_PERIODE']==''){
						echo "<option value='".$periodes['id_periode']."'";
						if ($periodes['id_periode'] == $GLOBALS['id_periode']){
								echo " selected";
						}
						echo ">".$periodes['libelle_periode']."</option>\n";
					}
				}
			}
		?>
      </select>
	  </td>
	</tr>
	<?php }else{ ?>
		<input type="hidden" name="ID_PERIODE" id="ID_PERIODE" value="0" />
	<?php } ?>
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('Secteur');?> :</td>
	  <td colspan="2" nowrap="nowrap" width="65%">
	<?php if(is_array($GLOBALS['campagnes']) && is_array($GLOBALS['status']) && is_array($GLOBALS['periodes'])){ ?>
	  <select name="ID_SYSTEME"	onchange="reload_page('<?php echo $_SESSION['val'];?>',this.value,'','','New',ID_CAMPAGNE.value,ID_STATUS.value,ID_PERIODE.value); ">
	<?php }else{ ?>
	  <select name="ID_SYSTEME"	onchange="reload_page('<?php echo $_SESSION['val']; ?>',this.value,'','','New'); ">
	<?php } ?>
	<?php 
		if(is_array($GLOBALS['systemes'])){
			if($val['ID_SYSTEME']=='')	echo "<option value=''></option>\n";
			foreach ($GLOBALS['systemes'] as $i => $systemes){
				if($val['ID_SYSTEME']<>'' && $val['ID_SYSTEME']==$systemes['id_systeme']){
					echo "<option value='".$systemes['id_systeme']."' selected>".$systemes['libelle_systeme']."</option>";
					break;
				}elseif(!isset($val['ID_SYSTEME']) || $val['ID_SYSTEME']==''){
					echo "<option value='".$systemes['id_systeme']."'";
					if ($systemes['id_systeme'] == $GLOBALS['id_systeme']){
							echo " selected";
					}
					echo ">".$systemes['libelle_systeme']."</option>\n";
				}
			}
		}
			
		?>
      </select></td>
	</tr>
	<tr>
	 	<td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('Chaine');?> :</td>
	  	<td colspan="2" nowrap="nowrap" width="65%">
		<?php if(is_array($GLOBALS['campagnes']) && is_array($GLOBALS['status']) && is_array($GLOBALS['periodes'])){ ?>
			<select name="ID_CHAINE" onchange="reload_page('<?php echo $_SESSION['val'];?>',ID_SYSTEME.value,this.value,'','New',ID_CAMPAGNE.value,ID_STATUS.value,ID_PERIODE.value);">
        <?php }else{ ?>
			<select name="ID_CHAINE" onchange="reload_page('<?php echo $_SESSION['val']; ?>',ID_SYSTEME.value,this.value,'','New');">
		<?php } ?>
	  	<?php
		if(is_array($GLOBALS['chaines_systeme'])){
			if($val['ID_CHAINE']=='')	echo "<option value=''></option>\n";
			foreach ($GLOBALS['chaines_systeme'] as $i => $chaine_systeme){
				if($val['ID_CHAINE']<>'' && $val['ID_CHAINE']==$chaine_systeme['CODE_TYPE_CH']){
					echo "<option value='".$chaine_systeme['CODE_TYPE_CH']."' selected>".$chaine_systeme['LIBELLE_TYPE_CH']."</option>";
					break;
				}elseif(!isset($val['ID_CHAINE']) || $val['ID_CHAINE']==''){
					echo "<option value='".$chaine_systeme['CODE_TYPE_CH']."'";
					if ($chaine_systeme['CODE_TYPE_CH'] == $GLOBALS['id_chaine']){
							echo " selected";
					}
					echo ">".$chaine_systeme['LIBELLE_TYPE_CH']."</option>\n";
				}
			}
		}
		?>
        </select></td>
	</tr>
	<tr>
	  	<td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('TypeLocalite');?> :</td>
	  	<td colspan="2" nowrap="nowrap" width="65%">
		<?php if(is_array($GLOBALS['campagnes']) && is_array($GLOBALS['status']) && is_array($GLOBALS['periodes'])){ ?>
	  		<select name="ID_TYPE_REGROUP" onchange="reload_page('<?php echo $_SESSION['val'];?>',ID_SYSTEME.value, ID_CHAINE.value, this.value,'New',ID_CAMPAGNE.value,ID_STATUS.value, ID_PERIODE.value);">
		<?php }else{ ?>
	  		<select name="ID_TYPE_REGROUP" onchange="reload_page('<?php echo $_SESSION['val']; ?>',ID_SYSTEME.value, ID_CHAINE.value, this.value,'New');">
		<?php } ?>
		<?php
			if(is_array($GLOBALS['type_regs_chaine'])){
				if($val['ID_TYPE_REGROUP']=='')	echo "<option value=''></option>\n";
				foreach ($GLOBALS['type_regs_chaine'] as $i => $type_regs){
					if($val['ID_TYPE_REGROUP']<>'' && $val['ID_TYPE_REGROUP']==$type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]){
						echo "<option value='".$type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."' selected>".$type_regs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."</option>";
						break;
					}elseif(!isset($val['ID_TYPE_REGROUP']) || $val['ID_TYPE_REGROUP']==''){
						echo "<option value='".$type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."'";
						if ($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']){
								echo " selected";
						}
						echo ">".$type_regs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."</option>\n";
					}
				}
				//Ajout Hebie pour choix niveau etablissement
				//if($_SESSION['id_groupe']==-1){
					echo "<option value='0'";
					if ($GLOBALS['type_reg'] == 0){
							echo " selected";
					}
					echo ">".$GLOBALS['PARAM']['ETABLISSEMENT']."</option>";
				//}
				//Fin ajout HEBIE
			}
			?>
		</select>		</td>
	</tr>
	<tr><td colspan="4" nowrap="nowrap" style="height:5px"></td></tr>
	<tr> 
		<?php 
		$arbre	= new arbre($GLOBALS['id_chaine']);
		if($GLOBALS['type_reg']<>0){
			foreach($arbre->chaine as $i=>$c) {
				if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']) { //Cas General
					$curdepht = $i;
					break;
				}
			}
		}elseif($GLOBALS['type_reg']==0){//Ajout Hebie pour affichage etablissement
			$j=0;
			foreach($arbre->chaine as $i=>$c) {
				$j=$i;
			}
			$curdepht = $j+1;
		}//Fin ajout Hebie 
		
		$arbre->type_access='config';
		$entete = $arbre->create_entete(0, $curdepht, true); 
		if ( (isset($entete['code_regroup']) && ($entete['code_regroup'] <> '')) || ($curdepht > 0) ){
			if(!in_array($GLOBALS['type_reg'],$_SESSION['user_fixe_type_regs'])){
				if($GLOBALS['type_reg']<>0){
									
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
				}elseif($GLOBALS['type_reg']==0){
					//Ajout Hebie pour affichage etablissement
					$requete       = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS CODE_REG,  
											 E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS LIB_REG
											 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R  
											 WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
											 AND E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = '.(int)$entete['code_regroup'].'  
											 AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_systeme'].' 
											 ORDER BY E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].', E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
				}         
			}else{
				if($GLOBALS['type_reg']<>0){
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
				}elseif($GLOBALS['type_reg']==0){
					//Ajout Hebie pour affichage etablissement
					$requete       = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS CODE_REG,  
											 E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS LIB_REG
											 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R  
											 WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
											 AND E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = '.(int)$entete['code_regroup'].'  
											 AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_systeme'].' 
											 ORDER BY E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].', E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
			
				}
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

		if( (isset($entete['code_regroup']) and trim($entete['code_regroup']) <> '') || ($curdepht > 0)){
			$combos_regs = preg_replace_callback("/(<form name=\"combo_regroups\">)|(</form>)|(<br />)/", '', $entete['html']);
			$combos_regs = preg_replace_callback('/<br>([[:blank:]]|[[:space:]])*</div>/', '</div>', $combos_regs);
			$combos_regs = str_replace('<select', '<select  style=\'width:100%;\'',$combos_regs);
			echo'<td align="center" valign="middle" width="25%"><table width="100%" height="100%"><tr><td align="center">' . $combos_regs . '</td></tr></table></td>';
			if($nb_td_regs > 1){
				$nb_td_regs = $nb_td_regs - 1 ;
			}
		}
		?>
		<td colspan="4" nowrap="nowrap" align="center" valign="middle"  width="75%"> 
			<?php print (tableau_check( $tab_regs, 'CODE_REG', 'LIB_REG', 'REGS', 'toutes', 'ALL_REGS', $nb_td_regs )); ?>
			<INPUT type="hidden" name="nb_regs_dispo" id="nb_regs_dispo" value="<?php echo count($tab_regs);?>">
			<INPUT type="hidden" name="ID_REGROUP" id="ID_REGROUP" value="<?php echo $val['ID_REGROUP'];?>">					
			<INPUT type="hidden" name="ID_REGROUP_PARENTS" id="ID_REGROUP_PARENTS" value="<?php echo $val['ID_REGROUP_PARENTS'];?>">
			<INPUT type="hidden" name="ID_TYPE_REGROUP_PARENTS" id="ID_TYPE_REGROUP_PARENTS" value="<?php echo $val['ID_TYPE_REGROUP_PARENTS'];?>">
		</td>
	</tr>
	<tr> 
	   <td colspan="4" nowrap="nowrap" align='center'>
			<?php
			$req_ord    = 'SELECT ORDRE_GROUPE FROM ADMIN_GROUPES WHERE CODE_GROUPE ='.$_SESSION['groupe'];
			$ord_grp     =  $GLOBALS['conn_dico']->GetOne($req_ord);
			$req_ord_user    = 'SELECT ORDRE_GROUPE FROM ADMIN_GROUPES WHERE CODE_GROUPE ='.$_SESSION['id_groupe'];
			$ord_grp_user     =  $GLOBALS['conn_dico']->GetOne($req_ord_user);
			if($ord_grp < $ord_grp_user || $_SESSION['groupe']==1){
			if(isset($_SESSION['action_new']) && $_SESSION['action_new']== 'New'){
			?>
			<INPUT   style="width:35%;"  type='button' <?php echo 'value="'.recherche_libelle_page('save').'"';?> onclick="changer_action('fix'); do_post('Formulaire');"/>&nbsp;&nbsp;&nbsp;
			<?php
			}
			?>
			<INPUT type="hidden" name="todo" id="todo" value=""/>
			<?php
			}
			?>		</td>
	</tr>
</table>
<?php
	if(isset($_POST['todo']) && $_POST['todo']=='fix'){
		$is_set_reg = false ;
		$do_action_post = false;
		for( $i = 0 ; $i < $_POST['nb_regs_dispo'] ; $i++ ){
			if(isset($_POST['REGS_'.$i]) and trim($_POST['REGS_'.$i]) <> ''){
					$is_set_reg = true ;
					break;
			}
		}
		if(!$is_set_reg){
			$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_regs').'"';
		}elseif( ($is_set_reg == true) ){
			$do_action_post = true;
		}
		if( $do_action_post == true ){
			$_SESSION['cfg_exp'] = $_POST;
			$campagne				= $_SESSION['cfg_exp']['ID_CAMPAGNE']; 
			$secteur				= $_SESSION['cfg_exp']['ID_SYSTEME']; 
			$chaine					= $_SESSION['cfg_exp']['ID_CHAINE']; 
			$type_regroup			= $_SESSION['cfg_exp']['ID_TYPE_REGROUP'];
			$tab_regs_priv 	= array();
			$regroup = '';
			$k = 0;
			for($i = 0 ; $i < $_SESSION['cfg_exp']['nb_regs_dispo'] ; $i++) {
				if(isset($_SESSION['cfg_exp']['REGS_'.$i]) && trim($_SESSION['cfg_exp']['REGS_'.$i]) <> '' && $k==0){
					$regroup .= $_SESSION['cfg_exp']['REGS_'.$i];
					$k++;
				}elseif(isset($_SESSION['cfg_exp']['REGS_'.$i]) && trim($_SESSION['cfg_exp']['REGS_'.$i]) <> ''){
					$regroup .= ','.$_SESSION['cfg_exp']['REGS_'.$i];
				}
				if(isset($_SESSION['cfg_exp']['REGS_'.$i]) && trim($_SESSION['cfg_exp']['REGS_'.$i]) <> ''){
					$tab_regs_priv[]	= $_SESSION['cfg_exp']['REGS_'.$i];
				}
			}
			
			$arbre	= new arbre($chaine);
			foreach($arbre->chaine as $i=>$c) {
				if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $type_regroup) {
						$curdepht = $i;
						break;
				}
			}
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
			
			$user_priv = '';
			foreach ($GLOBALS['campagnes'] as $i => $campagnes){
				if($campagnes['id_campagne'] == $_SESSION['id_campagne']){
					$user_priv .= $campagnes['libelle_campagne'];
					break;
				}
			}
			foreach ($_SESSION['systemes'] as $i => $systemes){
				if($systemes['id_systeme'] == $_SESSION['id_systeme']){
					if($user_priv=='') $user_priv .= $systemes['libelle_systeme'];
					else $user_priv .= ' - '.$systemes['libelle_systeme'];
					break;
				}
			}
			foreach ($_SESSION['chaines_systeme'] as $i => $chaine_systeme){
				if($chaine_systeme['CODE_TYPE_CH'] == $_SESSION['id_chaine']){
					$user_priv .= ' - '.$chaine_systeme['LIBELLE_TYPE_CH'];
					break;
				}
			}
			if($GLOBALS['type_reg'] <> 0){
				foreach ($_SESSION['type_regs_chaine'] as $i => $type_regs){
					if ($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $_SESSION['type_reg']){
						$user_priv .= ' - '.$type_regs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
						break;
					}
				}
			}else{
				$user_priv .= ' - '.$GLOBALS['PARAM']['ETABLISSEMENT'];
			}
			
			//Ajout Hebie
			$req_niv_ch ='SELECT COUNT(*)
						FROM '.$GLOBALS['PARAM']['HIERARCHIE'].'
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$chaine;
			$niveau_ch = $GLOBALS['conn']->GetOne($req_niv_ch)-1;
			$cpt_regroup = 0;
			foreach($tab_regs_priv as $code_regroup){
				$depht	=	$arbre->get_depht_regroup($code_regroup);
				//création de la liste des coderegs enfants
				$list_code_reg = $arbre->getchildsid($depht, $code_regroup, $chaine);
				// On fabrique le "where" de la requête sur ETABLISSEMENT_REGROUPEMENT
				//$where = get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'], $list_code_reg);
				foreach($list_code_reg as $code_reg_etab){
					$hierarchie = $arbre->getparentsid($niveau_ch, $code_reg_etab, $chaine);
					$hierarchie_regroup = '';
					foreach($hierarchie as $i=>$h) {
						$hierarchie_regroup .= $h[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']].' AS '.$h[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
						$hierarchie_regroup .= ', '.$h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']].' AS '.$h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
						if($i<(count($hierarchie)-1)) {
							$hierarchie_regroup .= ', ';
						}
					}
				
					
					$requete        = ' SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']. ' as code_etab,'. 
										$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' as nom_etab,'.
										$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' as code_regroup
										FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'   
										WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
										AND '.$where.' AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$secteur.'
										ORDER BY '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'];
	
					//echo '<br>'.$requete.'<br>';
					$liste_etab = $GLOBALS['conn']->GetAll($requete);
					if(is_array($liste_etab)){
						if($cpt_regroup == 0){
							$req_etabs_priv = $requete;
						}else{
							$req_etabs_priv .= ' UNION '.$requete;
						}
						$cpt_regroup++;
					}
				}
			}
			$_SESSION['req_etabs_priv'] = $req_etabs_priv;
			
			//Fin ajout Hebie
			
			unset($_SESSION['action_new']);
			echo "<script type='text/Javascript'>\n";
			echo "document.forms['Formulaire'].elements['ID_USER'].value = ".$_SESSION['id_user'].";\n";
			echo "document.forms['Formulaire'].elements['USER_PRIV'].value = '$user_priv';\n";  
			echo "document.forms['Formulaire'].elements['ID_ANNEE'].value = ".$_SESSION['annee'].";\n";
			echo "document.forms['Formulaire'].elements['ID_REGROUP'].value = '$regroup';\n";
			echo "document.forms['Formulaire'].elements['ID_REGROUP_PARENTS'].value = '$regroup_parents';\n";
			echo "document.forms['Formulaire'].elements['ID_TYPE_REGROUP_PARENTS'].value = '$type_regroup_parents';\n";
			echo "document.getElementById( 'action' ).value 	= 'Add';\n";
			echo "document.getElementById( 'i_action' ).value = '';\n";
			echo "document.forms['Formulaire'].elements['todo'].value = '';\n";
			echo "document.forms['Formulaire'].submit();\n";
			//echo "location.href= '?val=gestionuserpriv&id_systeme=$secteur&id_chaine=$chaine&type_reg=$type_regroup&id_user=".$_SESSION['id_user']."&name_user=".$_SESSION['name_user']."';\n";
			echo "</script>\n";
		}
	}
	if(isset($_POST['action']) && $_POST['action']== 'Del'){
		unset($_SESSION['action_new']);
		echo "<script type='text/Javascript'>\n";
		echo "init_user_priv('".$_SESSION['val']."');\n";
		echo "</script>\n";
	}
	if(isset($_POST['action']) && $_POST['action']== 'New'){
		echo "<script type='text/Javascript'>\n";
		if(is_array($GLOBALS['campagnes']) && is_array($GLOBALS['status']) && is_array($GLOBALS['periodes']))
			echo "reload_page('".$_SESSION['val']."',".$GLOBALS['id_systeme'].",".$GLOBALS['id_chaine'].",".$GLOBALS['type_reg'].",'New',".$GLOBALS['id_campagne'].",".$GLOBALS['id_status'].",".$GLOBALS['id_periode'].");\n";
		else
			echo "reload_page('".$_SESSION['val']."',".$GLOBALS['id_systeme'].",".$GLOBALS['id_chaine'].",".$GLOBALS['type_reg'].",'New');\n";
		echo "</script>\n";
	}
	
?>
<br/>

