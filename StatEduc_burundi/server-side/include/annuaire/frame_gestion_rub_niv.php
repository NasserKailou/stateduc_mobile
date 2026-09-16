<script language="JavaScript" type="text/javascript">
	function recharger(id_agg, id_syst, id_rub) {
		location.href   = 'annuaire.php?val=pop_rub_niv&id_agg='+id_agg+'&id_syst='+id_syst+'&id_rub='+id_rub;
	}
</script>
<?php $this->btn_new = false;
	
	
	if( isset($val['ID_SYSTEME']) && (trim($val['ID_SYSTEME'])<> '' ) ){
		$GLOBALS['id_syst'] = $val['ID_SYSTEME'] ;
	}
	
	if( isset($val['ID_RUBRIQUE']) && (trim($val['ID_RUBRIQUE'])<> '' ) ){
		$GLOBALS['id_rub'] = $val['ID_RUBRIQUE'] ;
	}
	
?>

<table align="center" border="1">
	<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_syst'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_SYSTEME" onChange="recharger(ID_AGGREGATION.value, ID_SYSTEME.value, ID_RUBRIQUE.value);">
            <option value=''></option>
            <?php foreach ($GLOBALS['all_systemes'] as $i => $systemes){
							echo "<option value='".$systemes['id_systeme']."'";
							if ($systemes['id_systeme'] == $GLOBALS['id_syst']){
								echo " selected";
							}
							echo ">".$systemes['libelle_systeme']."</option>";
						}
				?>
          </select></td>
 	</tr>    
	<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_rub'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_RUBRIQUE" onChange="recharger(ID_AGGREGATION.value, ID_SYSTEME.value, ID_RUBRIQUE.value);">
            <option value=''></option>
            <?php foreach ($GLOBALS['all_rub'] as $i => $rub){
							echo "<option value='".$rub['ID_RUBRIQUE']."'";
							if ($rub['ID_RUBRIQUE'] == $GLOBALS['id_rub']){
								echo " selected";
							}
							echo ">".$rub['LIBELLE_RUBRIQUE']."</option>";
						}
				?>
          </select></td>
 	</tr>    
	<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_agg'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_AGGREGATION">
            <option value=''></option>
            <?php foreach ($GLOBALS['all_agg'] as $i => $agg){
							echo "<option value='".$agg['ID_AGGREGATION']."'";
							if ($agg['ID_AGGREGATION'] == $val['ID_AGGREGATION']){
								echo " selected";
							}
							echo ">".$agg['LIBELLE_AGGREGATION']."</option>";
						}
				?>
          </select></td>
 	</tr>    
	<tr> 
        <td colspan="2">&nbsp;</td>
 	</tr>
		<tr> 
        <td width="40%"><?php echo recherche_libelle_page('nom_assoc'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="NOM_RUBRIQUE_NIVEAU" value="<?php echo $val['NOM_RUBRIQUE_NIVEAU']; ?>"></td>
 	</tr>    
 </table>
<br>
