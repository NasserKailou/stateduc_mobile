
<?php lit_libelles_page('/liste_user_report.php');
	
	function get_criteres_rpt($id_rpt){
        $conn                   = $GLOBALS['conn'];
        $requete                = ' SELECT DICO_CRITERE_FILTRE_REPORT.*, 
									DICO_CRITERE_FILTRE.LIBELLE_CRITERE, DICO_CRITERE_FILTRE.CHAMP_REF, DICO_CRITERE_FILTRE.TABLE_REF
									FROM DICO_CRITERE_FILTRE, DICO_CRITERE_FILTRE_REPORT
									WHERE DICO_CRITERE_FILTRE.ID_CRITERE = DICO_CRITERE_FILTRE_REPORT.ID_CRITERE
									AND DICO_CRITERE_FILTRE_REPORT.ID = ' . $id_rpt . '
									AND DICO_CRITERE_FILTRE_REPORT.ACTIVER_CRITERE = 1 ;';
        //print $requete;
        $GLOBALS['criteres_rpt'] = $GLOBALS['conn_dico']->GetAll($requete);
		$GLOBALS['nb_criteres_rpt']  = count($GLOBALS['criteres_rpt']);
    } //FIN get_criteres_rpt()

	function set_val_champ($id_rpt){
		$GLOBALS['val_champ'] = array();
		if( !isset($GLOBALS['criteres_rpt']) ){
			get_criteres_rpt($id_rpt);
		}

		if( isset($_POST['soumettre']) and (trim($_POST['soumettre']) <> '') ){
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
					$GLOBALS['val_champ']['VALEUR'][$i_crit] = $crit['VAl_CRITERE_FILTRE_REPORT'];
				}else{
					$GLOBALS['val_champ']['VALEUR'][$i_crit]	= '255';
				}
			}
		}
	}
	
	function get_val_filtre_critere($id_crit, $nom_champ, $nom_table){
		$conn                   = $GLOBALS['conn'];
		$champ_id             =   $GLOBALS['PARAM']['CODE'].'_'.$nom_table;
		$champ_lib            =   $GLOBALS['PARAM']['LIBELLE'].'_'.$nom_table;
		$champ_ordre          =   $GLOBALS['PARAM']['ORDRE'].'_'.$nom_table; 
		
		$lie_systeme  =   false;
		if ( isset($GLOBALS['id_systeme']) and (in_array(strtoupper($nom_table.'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$conn->MetaTables('TABLES')))) ){
			$lie_systeme  =   true; 
		}
		  
        if ( $lie_systeme == true ) {
            //$lie_systeme  =   true; 
			$requete    = 'SELECT '.$nom_table.'.'.$champ_id.' AS CODE, '.$nom_table.'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$nom_table.' AS LIBELLE'
						 .' FROM '.$nom_table. ','.$nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].' WHERE '.
						 $nom_table.'.'.$champ_id.'='.$nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$champ_id.
						 ' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$GLOBALS['id_systeme'].
						 ' ORDER BY '.$nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$nom_table; 
        } else {
            //$lie_systeme  =   false;
			$requete    ='SELECT '.$nom_table.'.'.$champ_id.' AS CODE, '.$nom_table.'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$nom_table.' AS LIBELLE'
						.' FROM '.$nom_table.
						' ORDER BY '.$nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$nom_table;             
		}
		//die($requete); 
		return ($conn->GetAll($requete));      
	}
	
	function manage_post(){
		if( isset($_POST['soumettre']) and (trim($_POST['soumettre']) <> '') ){
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
			echo '<br>CRITERES ET VALEURS SOUMIS : <pre>';
			print_r($GLOBALS['valeurs_filtres']);
		}
	}
	$GLOBALS['id_report'] 	= 1;
	$GLOBALS['id_systeme'] 	= $_SESSION['secteur'];
	get_criteres_rpt($GLOBALS['id_report']);
	set_val_champ($GLOBALS['id_report']);
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
</head>

<body>
<form name="form_theme" method="post" action="">
<BR><BR>
    <table align="center">
        <caption>
        PERSONNALISATION REPORT UTILISATEUR 
        </caption>
		<tr>
			<td>
				<table align="center" width="100%">
					<tr> 
						<td>Secteur Choisi : </td>
						<td><b> 2 : Primary Education</b><INPUT type="hidden" name="id_systeme" value="2"></td>
					</tr>
					<tr> 
						<td>Report Choisi : </td>
						<td><b> 1 : Repartition by type of location</b><INPUT type="hidden" name="id_report" value="1"></td>
					</tr>
				</table>
		<br>
	<?php if( $GLOBALS['nb_criteres_rpt'] ){?> 
		<table align="center" width="100%" class="rebords_1">
		<caption>
			Définition des critères et des valeurs de filtre 
        </caption>
		  <tr bgcolor="#cccccc" align="center" class="rebords_1"> 
			<td  height="22" class="rebords_1"> <div align="center"><b>Critère</b></div></td>
			<td  height="22" class="rebords_1"> <div align="center"><b>Champ</b></div></td>
			<td  height="22" class="rebords_1"> <div align="center"><b>Opérateur</b></div></td>
			<td  align="center" class="rebords_1"><div align="center"><b>Valeur</b></div></td>
		  </tr>
		  <tr><td colspan="4"><table width="100%" height="2"></table></td></tr>
		  <?php foreach ( $GLOBALS['criteres_rpt'] as $i_crit => $crit ){
			
				?>
				<tr bgcolor='#FFFFFF'> 
					<td   height="20"  align="left" class="rebords_1"> <b><?php echo $crit['LIBELLE_CRITERE']; ?></b></td>
					<td   height="20"  align="left" class="rebords_1"> <?php echo $crit['CHAMP_TABLE_DONNEES']; ?></td>
					<td   height="20"  align="left" class="rebords_1"> <INPUT  style="width : 100%;" type="text" size="1" maxlength="2" name="OP_<?php echo $i_crit; ?>" value="<?php echo $GLOBALS['val_champ']['OP'][$i_crit]; ?>"></td>
					<td  align='left' height="20" class="rebords_1">
					<?php $tab_val_filtre = get_val_filtre_critere($crit['ID_CRITERE'], $crit['CHAMP_REF'], $crit['TABLE_REF'])?>
						<select name="VALEUR_<?php echo $i_crit; ?>" style="width : 150px; height : 100%;">
							<option value='255'> </option>
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
				<tr><td colspan="4"><table width="100%" height="2"></table></td></tr>
				<?php }
		
		?>
		<tr>
			<td colspan="4" align="center">
				<table width="100%">
					<tr>
					 	<td width="100%" align="center">
							region
						</td>
					</tr>
				</table>
            </td>
		</tr>
		<tr><td colspan="4"><table width="100%" height="2"></table></td></tr>
		<tr>
			<td colspan="4" align="center">
				<INPUT style="width : 100%; height : 100%;" type="submit" value="Soumettre les critères choisis" name="soumettre">
            </td>
		</tr>
		<INPUT type="hidden" name="nb_criteres_rpt" value="<?php echo $GLOBALS['nb_criteres_rpt']; ?>">
		</table>
	<br>
	<?php }else{ ?>
				 <div style="font-weight: bold; color: #FF0000;" align="center"> Pas de Critères de filtres définis !</div>
			<?php }
			?>
				</table>
			</td>
		</tr>
    </table>
</form>
</body>
</html>

