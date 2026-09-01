<?php set_time_limit(0);
	lit_libelles_page('/liste_user_report.php');
	if(isset($_GET['secteur']) && $_GET['secteur']<>'') $_SESSION['secteur']=$_GET['secteur'];
	function get_criteres_rpt($id_rpt){
        $conn                   = $GLOBALS['conn_dico'];
        $requete                = ' SELECT DICO_CRITERE_FILTRE_REPORT.*, 
									DICO_CRITERE_FILTRE.LIBELLE_CRITERE, DICO_CRITERE_FILTRE.CHAMP_REF, DICO_CRITERE_FILTRE.TABLE_REF
									FROM DICO_CRITERE_FILTRE, DICO_CRITERE_FILTRE_REPORT
									WHERE DICO_CRITERE_FILTRE.ID_CRITERE = DICO_CRITERE_FILTRE_REPORT.ID_CRITERE
									AND DICO_CRITERE_FILTRE_REPORT.ID = ' . $id_rpt . '
									AND DICO_CRITERE_FILTRE_REPORT.ACTIVER_CRITERE = 1 ;';
        //echo $requete;
        $GLOBALS['criteres_rpt'] = $GLOBALS['conn_dico']->GetAll($requete);
		$GLOBALS['nb_criteres_rpt']  = count($GLOBALS['criteres_rpt']);
		/*echo '<pre>';
		print_r($GLOBALS['criteres_rpt']);*/
    } //FIN get_criteres_rpt()
	$GLOBALS['all_op_crit'] = 	array(  
									array ('CODE' => '1', 'VAL' => '=',  'LIBELLE' => recherche_libelle_page('egale') ),
									array ('CODE' => '2', 'VAL' => '<>', 'LIBELLE' => recherche_libelle_page('diff') ),
									array ('CODE' => '3', 'VAL' => '<',	 'LIBELLE' => recherche_libelle_page('inf') ),
									array ('CODE' => '4', 'VAL' => '>',  'LIBELLE' => recherche_libelle_page('sup') ),
									array ('CODE' => '5', 'VAL' => '<=', 'LIBELLE' => recherche_libelle_page('inf_eg') ),
									array ('CODE' => '6', 'VAL' => '>=', 'LIBELLE' => recherche_libelle_page('sup_eg') )
								 );
								 
	$all_type_export = 	array(  
							array ('CODE' => 'html',  'LIBELLE' => recherche_libelle_page('html') ),
							array ('CODE' => 'xls',  'LIBELLE' => recherche_libelle_page('xls') ),
							array ('CODE' => 'pdf',  'LIBELLE' => recherche_libelle_page('pdf') ),
						 );

	function set_val_champ($id_rpt){
		$GLOBALS['val_champ'] = array();
		if( !isset($GLOBALS['criteres_rpt']) ){
			get_criteres_rpt($id_rpt);
		}

		if( (isset($_POST['soumettre']) and (trim($_POST['soumettre']) <> '')) or (isset($_POST['excel']) and (trim($_POST['excel']) <> '')) ){
			
			$GLOBALS['val_champ']['TITRE_USER_RPT'] = $_POST['TITRE_USER_RPT'];
			
			if( !isset($_POST['SHOW_CRITERES_RPT']) ){
				$_POST['SHOW_CRITERES_RPT'] = 0 ;
			}
			if( !isset($_POST['WITH_SCHOOL']) ){
				$_POST['WITH_SCHOOL'] = 0 ;
			}
			
			$GLOBALS['val_champ']['SHOW_CRITERES_RPT']	= $_POST['SHOW_CRITERES_RPT'];
			$GLOBALS['val_champ']['WITH_SCHOOL']		= $_POST['WITH_SCHOOL'];
			$GLOBALS['val_champ']['TYPE_EXPORT_RPT'] 	= $_POST['TYPE_EXPORT_RPT'];
			
			foreach( $GLOBALS['criteres_rpt'] as $i_crit => $crit ){
				$GLOBALS['val_champ']['OP'][$i_crit] 		= $_POST['OP_'.$i_crit];
				$GLOBALS['val_champ']['VALEUR'][$i_crit]	= $_POST['VALEUR_'.$i_crit];
				
				if( $_POST['OP_'.$i_crit] <> $_SESSION['filtre_critere'][$crit['ID_CRITERE']]['OP'] ){
					$_SESSION['filtre_critere'][$crit['ID_CRITERE']]['OP'] 	= $_POST['OP_'.$i_crit];
				}
				if( $_POST['VALEUR_'.$i_crit] <> $_SESSION['filtre_critere'][$crit['ID_CRITERE']]['VAL'] ){
					$_SESSION['filtre_critere'][$crit['ID_CRITERE']]['VALEUR']	= $_POST['VALEUR_'.$i_crit];
				}
			}
		}
		
		elseif(!count($_POST) or (isset($_POST['defaut']) and (trim($_POST['defaut']) <> '')) ){
			get_criteres_rpt($id_rpt);
			$GLOBALS['val_champ']['TITRE_USER_RPT'] 	= $GLOBALS['nom_rpt'] ;
			$GLOBALS['val_champ']['SHOW_CRITERES_RPT']	= 1 ;
			$GLOBALS['val_champ']['WITH_SCHOOL']		= 0 ;
			if(isset($GLOBALS['PARAM']['TYPE_EXPORT_QUICK_RPT']) && $GLOBALS['PARAM']['TYPE_EXPORT_QUICK_RPT']<>'')
				$GLOBALS['val_champ']['TYPE_EXPORT_RPT'] 	= $GLOBALS['PARAM']['TYPE_EXPORT_QUICK_RPT'];
			else
				$GLOBALS['val_champ']['TYPE_EXPORT_RPT'] 	= 'xls';
			
			foreach( $GLOBALS['criteres_rpt'] as $i_crit => $crit ){
			
				if( isset($_SESSION['filtre_critere'][$crit['ID_CRITERE']]['OP']) ){
					$GLOBALS['val_champ']['OP'][$i_crit] = $_SESSION['filtre_critere'][$crit['ID_CRITERE']]['OP'];
				}elseif( isset($crit['OP_CRITERE_FILTRE_REPORT']) and (trim($crit['OP_CRITERE_FILTRE_REPORT']) <> '') ){
					$GLOBALS['val_champ']['OP'][$i_crit] = $crit['OP_CRITERE_FILTRE_REPORT'];
				}else{
					$GLOBALS['val_champ']['OP'][$i_crit] 		= '<>';
				}
				
				if( isset($_SESSION['filtre_critere'][$crit['ID_CRITERE']]['VALEUR']) ){
					$GLOBALS['val_champ']['VALEUR'][$i_crit] = $_SESSION['filtre_critere'][$crit['ID_CRITERE']]['VALEUR'];
				}elseif( isset($crit['VAL_CRITERE_FILTRE_REPORT']) and (trim($crit['VAL_CRITERE_FILTRE_REPORT']) <> '') ){
					$GLOBALS['val_champ']['VALEUR'][$i_crit] = $crit['VAL_CRITERE_FILTRE_REPORT'];
				}else{
					$GLOBALS['val_champ']['VALEUR'][$i_crit]	= '255';
				}
			}
		}
	}
	
	function get_val_filtre_critere($id_crit, $nom_champ, $nom_table){
		
		if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
			$conn                 =   $GLOBALS['conn'];
		} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
			$conn                 =   $GLOBALS['conn_dico']; 
		}
		$conn                 =	  $GLOBALS['conn'];
		$champ_id             =   $GLOBALS['PARAM']['CODE'].'_'.$nom_table;
		$champ_lib            =   $GLOBALS['PARAM']['LIBELLE'].'_'.$nom_table;
		$champ_ordre          =   $GLOBALS['PARAM']['ORDRE'].'_'.$nom_table; 
		
		$lie_systeme  =   false;
		if ( isset($GLOBALS['id_syst']) and (in_array(strtoupper($nom_table.'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$conn->MetaTables('TABLES')))) ){
			$lie_systeme  =   true; 
		}
		  
        if ( $lie_systeme == true ) {
            //$lie_systeme  =   true; 
			$requete    = ' SELECT NOM.'.$champ_id.' AS CODE, D_TRAD.LIBELLE
							FROM '.$nom_table.' AS NOM,  DICO_TRADUCTION AS D_TRAD, '.$nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].' AS NOM_SYST
							WHERE NOM.'.$champ_id.' = D_TRAD.CODE_NOMENCLATURE 
							AND NOM.'.$champ_id.' = NOM_SYST.'.$champ_id.' 
							AND D_TRAD.NOM_TABLE=\''.$nom_table.'\' 
							AND NOM.'.$champ_id.'<> 255'.'
							AND NOM_SYST.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$GLOBALS['id_syst'].'
							AND D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\'
							ORDER BY NOM.'.$GLOBALS['PARAM']['ORDRE'].'_'.$nom_table.';';  
        } else {
            //$lie_systeme  =   false;
			$requete    ='  SELECT NOM.'.$champ_id.' AS CODE, D_TRAD.LIBELLE
							FROM '.$nom_table.' AS NOM,  DICO_TRADUCTION AS D_TRAD
							WHERE NOM.'.$champ_id.' = D_TRAD.CODE_NOMENCLATURE 
							AND D_TRAD.NOM_TABLE=\''.$nom_table.'\'
							AND NOM.'.$champ_id.'<> 255'.' 
							AND D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\'
							ORDER BY NOM.'.$GLOBALS['PARAM']['ORDRE'].'_'.$nom_table.';';             
		}
		//die($requete); 
		return ($conn->GetAll($requete));      
	}
	
	function manage_post(){
		if( (isset($_POST['soumettre']) and (trim($_POST['soumettre']) <> '')) or (isset($_POST['excel']) and (trim($_POST['excel']) <> ''))){
			$GLOBALS['valeurs_filtres'] = array();
			foreach( $GLOBALS['criteres_rpt'] as $i_crit => $crit ){
				$GLOBALS['valeurs_filtres'][$i_crit] = array(
					'CHAMP_TABLE_DONNEES' 		=> $crit['CHAMP_TABLE_DONNEES'],
					'NOM_TABLE_DONNEES' 		=> $crit['NOM_TABLE_DONNEES'],
					'OPERATEUR_REQUETE' 		=> $crit['OPERATEUR_REQUETE'],
					'OP_CRITERE_FILTRE_REPORT' 	=> $_POST['OP_'.$i_crit],
					'VAL_CRITERE_FILTRE_REPORT' => $_POST['VALEUR_'.$i_crit]
				);
			}
			//echo '<br>CRITERES ET VALEURS SOUMIS : <pre>';
			//print_r($GLOBALS['valeurs_filtres']);
		}
	}
    function manage_post_rpt(){
		$res_rpt = array();
		if( isset($_POST['soumettre']) and (trim($_POST['soumettre']) <> '') ){	
			$id_rpt					= $_POST['id_rpt'];
			$id_syst 				= $_POST['id_syst'];
			
			$conn                   = $GLOBALS['conn_dico'];
			$requete                = ' SELECT DICO_CRITERE_FILTRE_REPORT.*, DICO_CRITERE_FILTRE.* 
										FROM DICO_CRITERE_FILTRE, DICO_CRITERE_FILTRE_REPORT
										WHERE DICO_CRITERE_FILTRE.ID_CRITERE = DICO_CRITERE_FILTRE_REPORT.ID_CRITERE
										AND DICO_CRITERE_FILTRE_REPORT.ID = ' . $id_rpt . '
										AND DICO_CRITERE_FILTRE_REPORT.ACTIVER_CRITERE = 1 ;';
			$all_crit_rpt = $GLOBALS['conn_dico']->GetAll($requete);
			
			$all_op_crit = array(  1 => '=', 2 => '<>', 3 => '<', 4 => '>', 5 => '<=', 6 => '>=');
			
			$tab_crit_rpt = array();
			foreach( $all_crit_rpt as $i_crit => $crit ){
                if (trim($_POST['VALEUR_'.$i_crit])!=-1){
                    $tab_crit_rpt[$i_crit] = array(
                        'CHAMP_TABLE_DONNEES' 		=> $crit['CHAMP_TABLE_DONNEES'],
                        'NOM_TABLE_DONNEES' 		=> $crit['NOM_TABLE_DONNEES'],
                        'OPERATEUR_REQUETE' 		=> $crit['OPERATEUR_REQUETE'],
                        'LIBELLE' 					=> $crit['LIBELLE'],
                        'CHAMP_REF' 				=> $crit['CHAMP_REF'],
                        'TABLE_REF' 				=> $crit['TABLE_REF'],
                        'OP_CRITERE_FILTRE_REPORT' 	=> $all_op_crit[$_POST['OP_'.$i_crit]],
                        'VAL_CRITERE_FILTRE_REPORT' => $_POST['VALEUR_'.$i_crit]
                    );
                }
			}
			$res_rpt['ID_REPORT']			= $id_rpt;
			$res_rpt['ID_SYSTEME']			= $id_syst;
			$res_rpt['ID_CHAINE']			= $_SESSION['tab_rpt']['id_chaine'] ;
			$res_rpt['TYPE_REG']			= $_SESSION['tab_rpt']['type_reg'] ;
			$_GET['type_rpt']				='';
			$res_rpt['CRITERES_NOM']		= $tab_crit_rpt ;

			$res_rpt['CRITERES_REG']		= $_SESSION['tab_rpt']['tab_regs'] ;
			$res_rpt['TITRE_USER_RPT']		= $_POST['TITRE_USER_RPT'] ;
			$res_rpt['SHOW_CRITERES_RPT']	= $_POST['SHOW_CRITERES_RPT'] ;
			$res_rpt['WITH_SCHOOL']		= $_POST['WITH_SCHOOL'] ;
			$res_rpt['TYPE_EXPORT_RPT']		= $_POST['TYPE_EXPORT_RPT'] ;
		}
		return($res_rpt);		
    }
	//$GLOBALS['id_rpt'] 	= $_POST['id_rpt'];
	//$GLOBALS['id_rpt'] 	= $_GET['id_rpt'];
	//$_GET['id_rpt'] = 1;
	$GLOBALS['id_rpt'] 	= $_GET['id_rpt'];
	$GLOBALS['id_syst'] = $_GET['id_syst'];
	//echo '<pre>';
	//print_r($GLOBALS['post_crit_regs']);
	$requete    = 'SELECT DICO_REPORT_SYSTEME.TITRE_ETAT
                FROM DICO_REPORT_SYSTEME
                WHERE (DICO_REPORT_SYSTEME.ID='.$GLOBALS['id_rpt'].' AND DICO_REPORT_SYSTEME.ID_SYSTEME='.$_GET['id_syst'].')';
	$GLOBALS['nom_rpt'] = $GLOBALS['conn_dico']->GetOne($requete);
	
	$req_type_rpt = 'SELECT DICO_REPORT.ID_TYPE_REPORT
						FROM DICO_REPORT
						WHERE DICO_REPORT.ID ='.$GLOBALS['id_rpt'];
	$_SESSION['type_rpt'] = $GLOBALS['conn_dico']->GetOne($req_type_rpt);
	
	get_criteres_rpt($GLOBALS['id_rpt']);
	set_val_champ($GLOBALS['id_rpt']);
	manage_post();
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Document sans titre</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.rebords_1 {
	border: 1px solid;
	margin-right: 2px;
	margin-left: 2px;
}
-->
</style>
<script type="text/javascript">
	function reload_page(syst) {
		location.href= '?secteur='+syst;
	}
</script>
</head>

<body>
<form name="form_theme" method="post" action="">
<BR/>
    <table align="center" width="600">
        <caption>
        <?php echo recherche_libelle_page('titre'); echo $GLOBALS['val_champ']['TYPE_EXPORT_RPT']; ?>
</caption>
		<tr>
			<td>
				<table align="center" width="100%">
					<tr> 
						<td width="30%"><?php echo recherche_libelle_page('secteur'); ?></td>
						<td colspan="3"><b><?php echo get_libelle_from_array($_SESSION['tab_secteur'], $GLOBALS['id_syst'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE');?></b>
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
						<INPUT type="hidden" name="id_syst" value="<?php echo $GLOBALS['id_syst']; ?>"></td>
					</tr>
					<tr> 
						<td><?php echo recherche_libelle_page('report'); ?></td>
						<td colspan="3"><b><?php echo $GLOBALS['nom_rpt']; ?></b> 
                        <INPUT type="hidden" name="id_rpt" value="<?php echo $GLOBALS['id_rpt']; ?>"></td>
					</tr>
					<tr>
                      <td><?php echo recherche_libelle_page('us_rpt_name'); ?></td>
					  <td colspan="3"><input style="width : 98%;" type="text" name="TITRE_USER_RPT" value="<?php echo $GLOBALS['val_champ']['TITRE_USER_RPT']; ?>"></td>
				  </tr>
					<tr>
                      <td nowrap="nowrap"><?php echo recherche_libelle_page('bool_crit'); ?>
                      <input type="checkbox" name="SHOW_CRITERES_RPT" value="1" <?php if($GLOBALS['val_champ']['SHOW_CRITERES_RPT']=='1') echo' checked';?>>&nbsp;&nbsp;</td>
					  <td width="30%" colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('bool_sch'); ?>
                      <input type="checkbox" name="WITH_SCHOOL" value="1" <?php if($GLOBALS['val_champ']['WITH_SCHOOL']==1) echo' checked';?>>&nbsp;&nbsp;</td>
					  <td nowrap="nowrap"><?php echo recherche_libelle_page('type_exp'); ?>&nbsp;<select style="width : 150;" name="TYPE_EXPORT_RPT">
							<!--<option value=''></option>-->
							<?php foreach ($all_type_export as $i_exp => $nom_exp){
											echo "<option value='".trim($nom_exp['CODE'])."'";
											if (trim($nom_exp['CODE']) == trim($GLOBALS['val_champ']['TYPE_EXPORT_RPT'])){
													echo " selected";
											}
											echo ">".$nom_exp['LIBELLE']."</option>";
									}
							
							?>
						</select></td>
				  </tr>				
				 </table>
		<br>
	<?php if( $GLOBALS['nb_criteres_rpt'] ){?> 
		<table align="center" width="100%">
		<caption>
                    <?php echo recherche_libelle_page('def_crit'); ?>
</caption>
		  <tr bgcolor="#cccccc" align="center" class="rebords_1"> 
			<td width="35%"  height="22" class="rebords_1"> <div align="center"><b><?php echo recherche_libelle_page('crit'); ?></b></div></td>
			<td width="25%"  height="22" class="rebords_1"> <div align="center"><b><?php echo recherche_libelle_page('op'); ?></b></div></td>
			<td width="40%"  align="center" class="rebords_1"><div align="center"><b><?php echo recherche_libelle_page('val'); ?></b></div></td>
		  </tr>
		  <tr><td colspan="4"><table width="100%" height="2"></table></td></tr>
		  <?php foreach ( $GLOBALS['criteres_rpt'] as $i_crit => $crit ){
				if( trim($crit['CHAMP_REF']) == trim($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']) ){
				?>	
					<input type="hidden" name="OP_<?php echo $i_crit; ?>" value="1">
					<input type="hidden" name="VALEUR_<?php echo $i_crit; ?>" value="<?php echo $_SESSION['secteur']; ?>">
				<?php }elseif( trim($crit['CHAMP_REF']) == trim($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']) ){
				?>	
					<input type="hidden" name="OP_<?php echo $i_crit; ?>" value="1">
					<input type="hidden" name="VALEUR_<?php echo $i_crit; ?>" value="<?php echo $_SESSION['annee']; ?>">
				<?php }
                else{
 				    $tab_val_filtre = get_val_filtre_critere($crit['ID_CRITERE'], $crit['CHAMP_REF'], $crit['TABLE_REF']);
                    // Ajout du All Ajout Alassane
                     $All_filtre            =array();
                     $All_filtre['CODE']    = 0;
                     $All_filtre['LIBELLE'] =  recherche_libelle_page('all');                
                     $tab_val_filtre[]      = $All_filtre;                 
                     // Fin ajout du All Fin Ajout Alassane
				?>
				<tr bgcolor='#FFFFFF'> 
					<td   height="20"  align="left" class="rebords_1">&nbsp;&nbsp;<b><?php echo $crit['LIBELLE_CRITERE']; ?></b></td>
					    <td   height="20"  align="left" class="rebords_1"> 
						<select style="width : 100%;" name="OP_<?php echo $i_crit; ?>">
							<!--<option value=''></option>-->
							<?php foreach ($all_op_crit as $i => $op_crit){
											echo "<option value='".trim($op_crit['CODE'])."'";
											if (trim($op_crit['CODE']) == trim($GLOBALS['val_champ']['OP'][$i_crit])){
													echo " selected";
											}
											echo ">".$op_crit['LIBELLE']."</option>";
									}
							
							?>
						</select></td>
						<td  align='left' height="20" class="rebords_1">
						<select name="VALEUR_<?php echo $i_crit; ?>" style="width : 100%; height : 100%;">
							<!--<option value='255'> </option>-->
							<?php if( is_array($tab_val_filtre)){
								foreach ($tab_val_filtre as $i => $val_filtre){
									echo "<option value='".$val_filtre['CODE']."'";
									if ( trim($val_filtre['CODE']) == trim($GLOBALS['val_champ']['VALEUR'][$i_crit]) ){
										echo " selected";
									}
									echo ">".$val_filtre['LIBELLE']."</option>";
								}
							}
							?>
						</select>
					</td>
				</tr>
				<?php }
				?>
				<tr><td colspan="4"><table width="100%" height="2"></table></td></tr>
				<?php }
		
		?>
		<tr>
			<td colspan="4" align="center">
			<iframe src="synthese.php?val=crit_reg&id_syst=<?php echo $GLOBALS['id_syst']."&type_rpt=".$_GET['type_rpt']; ?>" width="100%" name="crit_reg" height="230"></iframe>
			</td>
		</tr>
		<tr><td colspan="4"><table width="100%" height="2"></table></td></tr>
		<tr>
			<td colspan="4" align="center">
				<INPUT style="width : 100%" type="submit" value="<?php echo recherche_libelle_page('submit'); ?>" name="soumettre">
            </td>
		</tr>
		<INPUT type="hidden" name="nb_criteres_rpt" value="<?php echo $GLOBALS['nb_criteres_rpt']; ?>">
		</table>
	
	<?php }else{ ?><br>
				 <div style="font-weight: bold; color: #FF0000;" align="center"> <?php echo recherche_libelle_page('no_crit'); ?></div>
			<?php }
			?>
		  </table>
			</td>
		</tr>
    </table>
</form>
</body>
</html>
<?php if(isset($_POST['TYPE_EXPORT_RPT'])){
    /*echo '<pre>';                
    print_r($_SESSION['tab_libelles'] );*/
	ini_set("memory_limit", "1024M");
	switch($_POST['TYPE_EXPORT_RPT']){
		case 'html':{
            $_SESSION['params']=manage_post_rpt();
            include $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/export_html.php';
			break;
		}
		case 'pdf':{
            $_SESSION['params']=manage_post_rpt();
            include $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/instance_rpt.php';
			break;
		}
		case 'xls':{
			$_POST['excel'] = 1;
            $_SESSION['params']=manage_post_rpt();
			include $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/export_xls.php';
			break;
		}
		default:{
			include $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/instance_html.php';
			break;
		}
	}
}
?>

