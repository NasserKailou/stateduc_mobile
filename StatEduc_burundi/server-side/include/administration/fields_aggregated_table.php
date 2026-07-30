<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<script language="javascript" type="text/javascript">
	function changer_action(nom_table,nom_table2){
		if(nom_table2 != '')
			document.forms['Formulaire'].action = "administration.php?val=fields_aggregated_table&nom_table="+nom_table+"&nom_table2="+nom_table2 ;
		else
			document.forms['Formulaire'].action = "administration.php?val=fields_aggregated_table&nom_requete="+nom_table;
		//alert(document.forms['Formulaire'].action);
	}
	var do_submit = true;
	function do_post(form_name){
		if( do_submit == true ){
			eval('document.'+form_name+'.submit();');
		}
	}
</script>
<?php lit_libelles_page('/fields_aggregated_table.php');
	

	if(count($_POST)){
		if(isset($_GET['nom_table'])){
			$requete 	=	' SELECT * 
								FROM DICO_AGGREGATED_FIELD
								WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_TABLE =\''.$_GET['nom_table'].'\'
								ORDER BY ORDRE';
			$entry_fields  = $GLOBALS['conn_dico']->GetAll($requete);
			if(is_array($entry_fields)){
				foreach($entry_fields as $ichp => $champ){
						$nom_champ2 = $_POST['NOM_CHAMP_2_'.$ichp];
						if(isset($_POST['EXPORT_'.$ichp])) $export = $_POST['EXPORT_'.$ichp];
						else $export = 0;
						$requete='UPDATE DICO_AGGREGATED_FIELD SET NOM_CHAMP_2=\''.$nom_champ2.'\', EXPORT='.$export.' WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_TABLE=\''.$_GET['nom_table'].'\' AND NOM_CHAMP=\''.$champ['NOM_CHAMP'].'\'';
						if ($GLOBALS['conn_dico']->Execute($requete) === false){
							$GLOBALS['theme_data_MAJ_ok'] 	= false;
							print ' <br> error updating :<br> --- '.$requete.' --- <br>'; 
						}
				}
			}
		}elseif(isset($_GET['nom_requete'])){
			$requete 	=	' SELECT * 
								FROM DICO_EXCEL_REQ_ASSOC_FIELDS
								WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_REQUETE =\''.$_GET['nom_requete'].'\'
								ORDER BY ORDRE';
			$entry_fields  = $GLOBALS['conn_dico']->GetAll($requete);
			if(is_array($entry_fields)){
				foreach($entry_fields as $ichp => $champ){
						$nom_champ = $_POST['NOM_CHAMP_'.$ichp];
						if(isset($_POST['EXPORT_'.$ichp])) $export = $_POST['EXPORT_'.$ichp];
						else $export = 0;
						$requete='UPDATE DICO_EXCEL_REQ_ASSOC_FIELDS SET NOM_CHAMP=\''.$nom_champ.'\', EXPORT='.$export.' WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_REQUETE=\''.$_GET['nom_requete'].'\' AND NOM_CHAMP=\''.$champ['NOM_CHAMP'].'\'';
						if ($GLOBALS['conn_dico']->Execute($requete) === false){
							$GLOBALS['theme_data_MAJ_ok'] 	= false;
							print ' <br> error updating :<br> --- '.$requete.' --- <br>'; 
						}
				}
			}
		}
	}
	
?>
<br /><br /><br />
<FORM name="Formulaire"  method="post" action="" >
 <table  border="1" align="center" width="400">
    <tr> 
	    <td width="100%" align="center"><?php if(isset($_GET['nom_table'])) echo recherche_libelle_page('choix_table').' : <b>'.$_GET['nom_table2']; elseif(isset($_GET['nom_requete'])) echo recherche_libelle_page('choix_requete').' : <b>'.$_GET['nom_requete']; ?></b></td>
    </tr>
    <?php if(isset($_GET['nom_table'])){
		$requete 	=	' SELECT * 
							FROM DICO_AGGREGATED_FIELD
							WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_TABLE =\''.$_GET['nom_table'].'\'
							ORDER BY ORDRE';
	}elseif(isset($_GET['nom_requete'])){
		$requete 	=	' SELECT * 
							FROM DICO_EXCEL_REQ_ASSOC_FIELDS
							WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_REQUETE =\''.$_GET['nom_requete'].'\'
							ORDER BY ORDRE';
	}
	$entry_fields  = $GLOBALS['conn_dico']->GetAll($requete);
	?>
	<tr><td >
        <table align="center" border="1">
				
				<?php if(isset($_GET['nom_table'])){
					?>
					<tr> 
						<td align='center' ><b><?php echo "".recherche_libelle_page('export');?></b></td>
						<td align='center' ><b><?php echo "".recherche_libelle_page('nom_chp');?></b></td>
					</tr>
					<?php foreach ($entry_fields as $i => $chp){
						if($chp['EXPORT']==1) $checked = ' CHECKED';
						else $checked = '';
					?>
						<tr>
							<td align="center" ><INPUT style="width:75px" type="checkbox" name="<?php echo'EXPORT_'.$i;?>" value="1" <?php echo $checked; ?>/></td>
							<td align="center" ><input style="width:200px" type='text' name="<?php echo'NOM_CHAMP_2_'.$i;?>" value="<?php echo $chp['NOM_CHAMP_2'];?>" /></td>
						</tr>
					<?php }
				}else{
					?>
					<tr> 
						<td align='center' ><b><?php echo "".recherche_libelle_page('export');?></b></td>
						<td align='center' ><b><?php echo "".recherche_libelle_page('nom_chp');?></b></td>
						<td align='center' ><b><?php echo "".recherche_libelle_page('nom_req');?></b></td>
					</tr>
					<?php foreach ($entry_fields as $i => $chp){
						if($chp['EXPORT']==1) $checked = ' CHECKED';
						else $checked = '';
					?>
						
						<tr>
							<td align="center" ><input style="width:75px" type="checkbox" name="<?php echo'EXPORT_'.$i;?>" value="1" <?php echo $checked; ?>/></td>
							<td align="center" ><INPUT style="width:200px" type='text' name="<?php echo'NOM_CHAMP_'.$i;?>" value="<?php echo $chp['NOM_CHAMP'];?>" /></td>
							<td><INPUT type='button' name='' value='<?php echo recherche_libelle_page('child_table');?>' onclick="javascript:OpenPopupChildTable(<?php echo "'".$chp['NOM_REQUETE']."','".$chp['NOM_CHAMP']."'";?>);" style='width:150px'/></td>
						</tr>
				<?php }
				}
				?>
					
				<tr><td colspan=3 align='center'>&nbsp;</td></tr>
		</table>
		</td>
	</tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($entry_fields); ?>" />
</table>
<br/>
<table align="center" border="1" width="50%" >
	<tr> 
		<td align='center' nowrap="nowrap">
			<INPUT   style="width:50%;"  type='button' name='Input' <?php echo 'value="'.recherche_libelle_page('save').'"';?> onclick="changer_action('<?php if(isset($_GET['nom_table'])) echo $_GET['nom_table']."','".$_GET['nom_table2']."'"; elseif(isset($_GET['nom_requete'])) echo $_GET['nom_requete']."',''";?>); do_post('Formulaire');"/>&nbsp;&nbsp;&nbsp;
			<INPUT   style="width:45%;"  type="button" <?php echo 'value="'.recherche_libelle_page('fermer').'"';?> onClick="javascript:fermer();">
		</td>
	</tr>
</table>
</FORM>

