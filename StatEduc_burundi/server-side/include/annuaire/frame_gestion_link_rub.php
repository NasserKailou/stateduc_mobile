<script language="JavaScript" type="text/javascript">
	function recharger(id_rub, id_ss_rub) {
		location.href   = 'annuaire.php?val=pop_link_rub&id_rub='+id_rub+'&id_ss_rub='+id_ss_rub;
	}
</script>
<?php $this->btn_new = false;
	
	if( isset($val['ID_RUBRIQUE']) && (trim($val['ID_RUBRIQUE'])<> '' ) ){
		$GLOBALS['id_rub'] = $val['ID_RUBRIQUE'] ;
	}
	
	
	if( isset($val['ID_SOUS_RUBRIQUE']) && (trim($val['ID_SOUS_RUBRIQUE'])<> '' ) ){
		$GLOBALS['id_ss_rub'] = $val['ID_SOUS_RUBRIQUE'] ;
	}
	
?>

<table align="center" border="1">

<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_rub'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_RUBRIQUE" onChange="recharger(ID_RUBRIQUE.value, ID_SOUS_RUBRIQUE.value);">
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
        <td width="40%"><b><?php echo recherche_libelle_page('id_ss_rub'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_SOUS_RUBRIQUE" onChange="recharger(ID_RUBRIQUE.value, ID_SOUS_RUBRIQUE.value);">
            <option value=''></option>
            <?php foreach ($GLOBALS['all_ss_rub'] as $i => $ss_rub){
							echo "<option value='".$ss_rub['ID_RUBRIQUE']."'";
							if ($ss_rub['ID_RUBRIQUE'] == $GLOBALS['id_ss_rub']){
								echo " selected";
							}
							echo ">".$ss_rub['LIBELLE_RUBRIQUE']."</option>";
						}
				?>
          </select></td>
 	</tr>	    
	    
	<tr> 
        <td colspan="2">&nbsp;</td>
 	</tr>
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('nom_link'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="NOM_LINK_RUBRIQUE" value="<?php echo $val['NOM_LINK_RUBRIQUE']; ?>"></td>
 	</tr>    
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('ordre'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="ORDRE_SS_RUB" value="<?php echo $val['ORDRE_SS_RUB']; ?>"></td>
 	</tr>    
</table>
<br>
