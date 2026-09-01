<script language="JavaScript" type="text/javascript">
	function recharger(id_ch, id_agg) {
		location.href   = 'annuaire.php?val=gest_agg_ch&id_ch='+id_ch+'&id_agg='+id_agg;
	}
</script>
<?php $this->btn_new = false;
	
	if( isset($val['CODE_TYPE_CHAINE']) && (trim($val['CODE_TYPE_CHAINE'])<> '' ) ){
		$GLOBALS['id_ch'] = $val['CODE_TYPE_CHAINE'] ;
	}
	
	
	if( isset($val['ID_AGGREGATION']) && (trim($val['ID_AGGREGATION'])<> '' ) ){
		$GLOBALS['id_agg'] = $val['ID_AGGREGATION'] ;
	}
	
?>

<table align="center" border="1" width="500">

<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_ch'); ?></b></td>
        <td width="60%"><select  style="width : 300;" name="CODE_TYPE_CHAINE" onChange="recharger(CODE_TYPE_CHAINE.value, ID_AGGREGATION.value);">
            <option value=''></option>
            <?php foreach ($GLOBALS['all_ch'] as $i => $ch){
							echo "<option value='".$ch['CODE_TYPE_CHAINE']."'";
							if ($ch['CODE_TYPE_CHAINE'] == $GLOBALS['id_ch']){
								echo " selected";
							}
							echo ">".$ch['LIBELLE']. ' / ' . $ch['LIBELLE_SYSTEME'] ."</option>";
						}
				?>
          </select></td>
 	</tr>
<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_agg'); ?></b></td>
        <td width="60%"><select  style="width : 300;" name="ID_AGGREGATION" onChange="recharger(CODE_TYPE_CHAINE.value, ID_AGGREGATION.value);">
            <option value=''></option>
            <?php foreach ($GLOBALS['all_agg'] as $i => $agg){
							echo "<option value='".$agg['ID_AGGREGATION']."'";
							if ($agg['ID_AGGREGATION'] == $GLOBALS['id_agg']){
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
        <td width="40%"><?php echo recherche_libelle_page('nom_prof'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="NOM_PROFONDEUR" value="<?php echo $val['NOM_PROFONDEUR']; ?>"></td>
 	</tr>    
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('level'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="HIERARCHY_LEVEL" value="<?php echo $val['HIERARCHY_LEVEL']; ?>"></td>
 	</tr>    
</table>
<br>
