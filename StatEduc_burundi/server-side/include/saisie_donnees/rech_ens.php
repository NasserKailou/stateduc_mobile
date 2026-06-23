<?php
	lit_libelles_page('/rech_ens.php');
	include_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';
	$GLOBALS['pers'] = array();
    $GLOBALS['lancer_theme_manager'] 		= false;
    $GLOBALS['lancer_theme_manager_classe'] = false;
	switch(trim($_GET['type_pers'])){
		case 'NON_ENS' :{
			$GLOBALS['pers']['tbl_P'] 		= $GLOBALS['PARAM']['PERSONNEL_ADMIN'];
			$GLOBALS['pers']['tbl_P_ETA'] 	= $GLOBALS['PARAM']['PERSONNEL_ADMIN_ETABLISSEMENT'];
			$GLOBALS['pers']['id_P'] 		= $GLOBALS['PARAM']['IDENTIFIANT_PERSONNEL_ADMIN'];
			$GLOBALS['pers']['nom_P'] 		= $GLOBALS['PARAM']['NOM_PERSONNEL_ADMIN'];
			$GLOBALS['pers']['pre_P'] 		= $GLOBALS['PARAM']['PRENOMS_PERSONNEL_ADMIN'];
			$GLOBALS['pers']['mat_P'] 		= $GLOBALS['PARAM']['MATRICULE_PERSONNEL_ADMIN'];
			break;
		}
		case 'SERV' :{
			$GLOBALS['pers']['tbl_P'] 		= 'PERSONNEL_SERVICE';
			$GLOBALS['pers']['tbl_P_ETA'] 	= 'PERSONNEL_SERVICE_ETABLI';
			$GLOBALS['pers']['id_P'] 		= 'IDENTIFIANT_PERSONNEL';
			$GLOBALS['pers']['nom_P'] 		= 'PRENOMS_NOM_PERSONNEL';
			$GLOBALS['pers']['pre_P'] 		= 'PRENOMS_NOM_PERSONNEL';
			$GLOBALS['pers']['mat_P'] 		= 'MATRICULE';
			break;
		}
		default:{
			$GLOBALS['pers']['tbl_P'] 		= $GLOBALS['PARAM']['ENSEIGNANT'];
			$GLOBALS['pers']['tbl_P_ETA'] 	= $GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'];
			$GLOBALS['pers']['id_P'] 		= $GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'];
			$GLOBALS['pers']['nom_P'] 		= $GLOBALS['PARAM']['NOM_ENSEIGNANT'];
			$GLOBALS['pers']['pre_P'] 		= $GLOBALS['PARAM']['PRENOMS_ENSEIGNANT'];
			$GLOBALS['pers']['mat_P'] 		= $GLOBALS['PARAM']['MATRICULE_ENSEIGNANT'];
		}
	}
	
	function get_nom_ecole($ID_P){
        $sql      = 'SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS NOM_ECOLE, '.
							$GLOBALS['PARAM']['TYPE_ANNEE'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' AS ANNEE, '.
							$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS SECTEUR 
					 FROM         '.$GLOBALS['pers']['tbl_P'].' , '.$GLOBALS['pers']['tbl_P_ETA'].' , '.$GLOBALS['PARAM']['ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['TYPE_ANNEE'].' , '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' 
					 WHERE  '.$GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['id_P'].' = '.$GLOBALS['pers']['tbl_P_ETA'].'.'.$GLOBALS['pers']['id_P'].' 
					 AND '.$GLOBALS['pers']['tbl_P_ETA'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
					 AND '.$GLOBALS['pers']['tbl_P_ETA'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$GLOBALS['PARAM']['TYPE_ANNEE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'
					 AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'
					 AND     '.$GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['id_P'].' = '.$ID_P.'
					 ORDER BY '.$GLOBALS['pers']['tbl_P_ETA'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' DESC';
        return ($GLOBALS['conn']->GetAll($sql));
	}
	if( isset($_POST['new_recherche']) ){
		unset($_SESSION['All_ENS_FOUND']);
	}
	if( isset($_POST['transferer']) ){
		$liste_chps = " (";
		$liste_vals = " (";
		$k = 0;
		foreach($_SESSION['curobj_instance']->val_cle[$GLOBALS['pers']['tbl_P_ETA']] as $nom_chp=>$val_chp){
			if($k == 0){
				$liste_chps .= "$nom_chp";
				$liste_vals .= "$val_chp";
			}else{
				$liste_chps .= ", $nom_chp";
				$liste_vals .= ", $val_chp";
			}
			$k++;
		}
		$liste_chps .= ", ".$GLOBALS['pers']['id_P'].")";
				
		$ID_TRACE = get_cle_max_id_trace()+1;//Recuperation max id_trace + 1 pour obtenir le nouvel id_trace
		$id_action = 0;
		
		for( $i = 0 ; $i < $_POST['NB_ENS'] ; $i++ ){
			if(isset($_POST['CHECK_'.$i]) and trim($_POST['CHECK_'.$i]) <> ''){
				// effectuer le transfert
				$CODE_ENS_TRANSFERT = $_POST['CHECK_'.$i] ;
				$tmp_liste_vals = $liste_vals.", $CODE_ENS_TRANSFERT)";
				$id_action++;
				$sql = 'INSERT INTO '.$GLOBALS['pers']['tbl_P_ETA'].$liste_chps.' VALUES '.$tmp_liste_vals;
				$insert_ok = true;
				if($res = $GLOBALS['conn']->Execute($sql)===false){
					echo "Error inserting: <br/> $sql <br/>";
					$insert_ok = false;
				}
				if($insert_ok && (isset($GLOBALS['PARAM']['DATA_ENTRY_TRACE']) && $GLOBALS['PARAM']['DATA_ENTRY_TRACE'])){
					//MAJ DICO_TRACE
					$nomtable = $GLOBALS['pers']['tbl_P_ETA'];
					$code_user = $_SESSION['code_user'];
					$login_user = $_SESSION['login'];
					$id_theme_user = $str_theme_id;
					$code_etab_user	= $_SESSION['code_etab'];        
					$code_annee_user = $_SESSION['annee'];
					if(isset($_SESSION['filtre']) && $_SESSION['filtre']<>'') $code_filtre_user = $_SESSION['filtre']; else $code_filtre_user = 'NULL';
					$id_systeme_user = $_SESSION['secteur'];
					$date_saisie_user = date('d/m/Y H:i:s');
					
					$req_trace_user = "INSERT INTO DICO_TRACE (ID_TRACE,NOM_TABLE,ID_ACTION,CODE_USER,NOM_USER,ID_THEME,ACTION,SQL_ACTION,CODE_SECTEUR,CODE_ETABLISSEMENT,CODE_ANNEE,CODE_FILTRE,DATE_SAISIE) 
														VALUES ($ID_TRACE,'$nomtable',$id_action,$code_user,'$login_user',$id_theme_user,'I',".$GLOBALS['conn']->qstr($sql).",$id_systeme_user,$code_etab_user,$code_annee_user,$code_filtre_user,'$date_saisie_user')";
					//echo "<br>$req_trace_user";
					if ($GLOBALS['conn_dico']->Execute($req_trace_user) === false){
						//print ' <br> error inserting in trace table :<br>'.$req_trace_user.'<br>';   
					}
					//Fin MAJ DICO_TRACE
				}
				$_SESSION['id_teacher'] = $CODE_ENS_TRANSFERT;
			}
		}
		// réactualiser la page ouvrante
		?>
		<script type="text/Javascript">
			//parent.document.location.reload();
            parent.document.location.href='questionnaire.php?theme=<?php echo $_GET['theme'];?>';
			fermer();
		</script>
		<?php unset($_SESSION['All_ENS_FOUND']);
	}
	$all_ENS = array();
	$NB_ENS  = 0;
	if(isset($_GET['teach_number']) && $_GET['teach_number']<>'' && $_GET['teach_number']<>'undefined' && count($_POST)==0){
		$_POST['rechercher'] = recherche_libelle_page('Rechercher');
		$_POST['MATRICULE'] = $_GET['teach_number'];
		echo "<br/>".$_POST['rechercher'];
	}
	if(isset($_POST['rechercher'])){
		// rechercher les enseignants
		$criteres = '';
		unset($_SESSION['All_ENS_FOUND']);
		if( isset($_POST['NOM']) and (trim($_POST['NOM']) <> '') ){
			$criteres .= ' AND '.$GLOBALS['pers']['nom_P'].' LIKE \''.$_POST['NOM'].'%\'';
		}
		if( isset($_POST['PRENOM']) and (trim($_POST['PRENOM']) <> '') ){
			$criteres .= ' AND '.$GLOBALS['pers']['pre_P'].' LIKE \''.$_POST['PRENOM'].'%\'';
		}
		if( isset($_POST['MATRICULE']) and (trim($_POST['MATRICULE']) <> '') ){
			$criteres .= ' AND '.$GLOBALS['pers']['mat_P'].' LIKE \''.$_POST['MATRICULE'].'%\'';
		}
		if( trim ($criteres) <> '' ){
			$suite_select = $GLOBALS['pers']['id_P'].' AS IDENTIFIANT';
			if(isset($GLOBALS['pers']['nom_P']) && $GLOBALS['pers']['nom_P']<>'') $suite_select .= ', '.$GLOBALS['pers']['nom_P'].' AS NOM';
			if(isset($GLOBALS['pers']['pre_P']) && $GLOBALS['pers']['pre_P']<>'') $suite_select .= ', '.$GLOBALS['pers']['pre_P'].' AS PRENOM';
			if(isset($GLOBALS['pers']['mat_P']) && $GLOBALS['pers']['mat_P']<>'') $suite_select .= ', '.$GLOBALS['pers']['mat_P'].' AS MATRICULE';
			$sql_ENS = 'SELECT '.$suite_select.'
						FROM '.$GLOBALS['pers']['tbl_P'].'
						WHERE '.$GLOBALS['pers']['id_P'].' > 0 ';
			$sql_ENS .= $criteres ;
			
			if(isset($GLOBALS['pers']['nom_P']) && $GLOBALS['pers']['nom_P']<>'') $suite_order = $GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['nom_P'];
			if(isset($GLOBALS['pers']['pre_P']) && $GLOBALS['pers']['pre_P']<>'') $suite_select .= ', '.$GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['pre_P'];
			if(isset($GLOBALS['pers']['mat_P']) && $GLOBALS['pers']['mat_P']<>'') $suite_select .= ', '.$GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['mat_P'];
			
			if(trim($GLOBALS['pers']['nom_P']) <> trim($GLOBALS['pers']['pre_P']) && trim($GLOBALS['pers']['nom_P'])<>'' && trim($GLOBALS['pers']['pre_P'])<>''){
				$sql_ENS .= ' ORDER BY '.$GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['nom_P'].', '.$GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['pre_P'].', '.$GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['mat_P'].'';
			}else{
				$sql_ENS .= ' ORDER BY '.$GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['nom_P'].', '.$GLOBALS['pers']['tbl_P'].'.'.$GLOBALS['pers']['mat_P'].'';			
			}
			$_SESSION['All_ENS_FOUND'] = $GLOBALS['conn']->GetAll($sql_ENS);
			//echo "$sql_ENS";
		}
	}
	
	if( isset($_SESSION['All_ENS_FOUND']) and (is_array($_SESSION['All_ENS_FOUND'])) and count($_SESSION['All_ENS_FOUND']) > 0){
		configurer_barre_nav(10);
		$NB_ENS  = count($_SESSION['All_ENS_FOUND']);
		$GLOBALS['nbre_total_enr'] = $NB_ENS;
		//die( $GLOBALS['debut'] .'/'.$GLOBALS['cfg_nbres_ppage']);
		if( $NB_ENS > 10){
			$list_ENS = array_slice( $_SESSION['All_ENS_FOUND'], $GLOBALS['debut'], $GLOBALS['cfg_nbres_ppage']);
		}else{
			$list_ENS = $_SESSION['All_ENS_FOUND'];
		}
		$GLOBALS['nbenr'] = count($list_ENS);
	}
//die('$NB_ENS ='.$NB_ENS);
 if( $NB_ENS > 0 and (!isset($_POST['new_recherche']))){?>
		<style type="text/css">
<!--
.Style2 {color: #000000}
.Style3 {color: #0000FF}
-->
        </style>
		 
	<br><br><br>
	<div align="center"><span class="Style3"><?php echo $NB_ENS; ?> </span> <span class="Style2"><?php echo recherche_libelle_page('Personnels'); ?> </span><span class="Style2"> <?php echo recherche_libelle_page('Trouves'); ?> !</span></div>
	<br>
	<form name="form2" method="post" action="">
		<table width="90%" align="center" bgcolor="#000000" cellspacing="1" cellpading="3" border="0">
		  <tr bgcolor="#cccccc" > 
		    <td  height="22"> <div align="left" class="gras11"><?php echo recherche_libelle_page('IDENTIFIANT'); ?></div></td>
			<?php if(isset($GLOBALS['pers']['mat_P']) && $GLOBALS['pers']['mat_P']<>''){ ?><td  height="22"> <div align="left" class="gras11"><?php echo recherche_libelle_page('MATRICULE'); ?></div></td><?php } ?>
			<?php if(isset($GLOBALS['pers']['nom_P']) && $GLOBALS['pers']['nom_P']<>''){ ?><td  height="22"> <div align="left" class="gras11"><?php echo recherche_libelle_page('NOM'); ?></div></td><?php } ?>
			<?php if(isset($GLOBALS['pers']['pre_P']) && $GLOBALS['pers']['pre_P']<>''){ ?><td  height="22"> <div align="left" class="gras11"><?php echo recherche_libelle_page('PRENOMS'); ?></div></td><?php } ?>
			<td  height="22"> <div align="left" class="gras11"><?php echo recherche_libelle_page('ETABLISSEMENT'); ?></div></td>
			<td  height="22"> <div align="left" class="gras11"><?php echo recherche_libelle_page('ANNEE'); ?></div></td>
			<td  height="22"> <div align="left" class="gras11"><?php echo recherche_libelle_page('SECTEUR'); ?></div></td>
			<td  align="center"><div align="center" class="gras11"><?php echo recherche_libelle_page('CHOISIR'); ?></div></td>
		  </tr>
		  <?php $i_ENS = 0;
			foreach ( $list_ENS as $i_ => $ENS ){
				$tab_etab_an_sect = array();
				$tab_etab_an_sect = get_nom_ecole($ENS['IDENTIFIANT']);
				?>
				  <tr bgcolor='#FFFFFF'> 
					<td   height="20"> <?php echo $ENS['IDENTIFIANT']; ?></td>
					<?php if(isset($GLOBALS['pers']['mat_P']) && $GLOBALS['pers']['mat_P']<>''){ ?><td   height="20"> <?php echo $ENS['MATRICULE']; ?></td><?php } ?>
					<?php if(isset($GLOBALS['pers']['nom_P']) && $GLOBALS['pers']['nom_P']<>''){ ?><td   height="20"> <?php echo $ENS['NOM']; ?></td><?php } ?>
					<?php if(isset($GLOBALS['pers']['pre_P']) && $GLOBALS['pers']['pre_P']<>''){ ?><td   height="20"> <?php echo $ENS['PRENOM']; ?></td><?php } ?>
					<td   height="20"> <?php echo $tab_etab_an_sect[0]['NOM_ECOLE']; ?></td>
					<td   height="20"> <?php echo $tab_etab_an_sect[0]['ANNEE']; ?></td>
					<td   height="20"> <?php echo $tab_etab_an_sect[0]['SECTEUR']; ?></td>
					<td  align='center' height="20"><INPUT type="checkbox" name="CHECK_<?php echo $i_ENS; ?>" value='<?php echo $ENS['IDENTIFIANT']; ?>' <?php if(isset($_GET['teach_number']) && $_GET['teach_number']<>'' && $i_ENS == 0) echo ' checked="checked"'; ?>></td>
				  </tr>
				<?php $i_ENS ++;
			}
		
		?>
		<tr bgcolor="#cccccc" >
			<td colspan="5" align="center">
			
			<INPUT style="width : 50%" type="submit" value=" <?php echo recherche_libelle_page('Transferer'); ?> ... " name="transferer">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type='submit' name='new_recherche' value=' ... <?php echo recherche_libelle_page('NlleRech'); ?> ... '>
			</td>
		</tr>
		<tr bgcolor="#ffffff" >
			<td colspan="5" align="center">
			<?php echo afficher_barre_nav(true,false,array('val'));
			?>
			</td>
		</tr>

		<INPUT type="hidden" name="NB_ENS" value="<?php echo count($list_ENS); ?>">
		</table>
	</form>
	<br>
<?php }else{ ?>
				<br><br><br><br>
			<?php if(isset($_POST['rechercher']) and $NB_ENS == 0){
				?>
				 <div style="font-weight: bold; color: #FF0000;" align="center"> <?php echo recherche_libelle_page('EnsNonTrouve'); ?> !</div>
				<?php }
			?>
		<form name="form1" method="post" action="">
			<table align="center" bordercolor="#000000" cellspacing="1" cellpading="3" border="0">
				<caption><?php echo recherche_libelle_page('RechEns'); ?></caption>
				<?php
				if(isset($GLOBALS['pers']['nom_P']) && $GLOBALS['pers']['nom_P']<>''){
				?>
				<tr>
					<td><?php echo recherche_libelle_page('NOM'); ?> : </td>
					<td><INPUT type="text" size="30" name="NOM" value="<?php echo $_POST['NOM']; ?>"></td>
				</tr>
				<?php
				}
				?>
				<?php
				if(isset($GLOBALS['pers']['pre_P']) && $GLOBALS['pers']['pre_P']<>''){
				?>
				<tr>
					<td><?php echo recherche_libelle_page('PRENOMS'); ?> : </td>
					<td><INPUT type="text" size="50" name="PRENOM" value="<?php echo $_POST['PRENOM']; ?>"></td>
				</tr>
				<?php
				}
				?>
				<?php
				if(isset($GLOBALS['pers']['mat_P']) && $GLOBALS['pers']['mat_P']<>''){
				?>
				<tr>
					<td><?php echo recherche_libelle_page('MATRICULE'); ?> : </td>
					<td><INPUT type="text" size="10" name="MATRICULE" value="<?php echo $_POST['MATRICULE']; ?>"></td>
				</tr>
				<?php
				}
				?>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2"><INPUT style="width : 100%" type="submit" value=" <?php echo recherche_libelle_page('Rechercher'); ?> ..." name="rechercher"></td>
				</tr>
			</table>
		</form>
<?php } ?>
