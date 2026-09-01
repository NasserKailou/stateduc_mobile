<script language="JavaScript" type="text/javascript">
	function recharger(id_fonc, id_comp) {
		location.href   = 'annuaire.php?val=pop_comp_fonc&id_fonc='+id_fonc+'&id_comp='+id_comp;
	}
</script>
<?php $this->btn_new = false;
	
	if( isset($val['ID_FONCTIONNALITE']) && (trim($val['ID_FONCTIONNALITE'])<> '' ) ){
		$GLOBALS['id_fonc'] = $val['ID_FONCTIONNALITE'] ;
	}
	
	
	if( isset($val['ID_COMPOSANT']) && (trim($val['ID_COMPOSANT'])<> '' ) ){
		$GLOBALS['id_comp'] = $val['ID_COMPOSANT'] ;
	}
	
?>

<table align="center" border="1">
<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_comp'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_COMPOSANT" onChange="recharger(ID_FONCTIONNALITE.value, ID_COMPOSANT.value);">
            <?php foreach ($GLOBALS['all_comp'] as $i => $comp){
							echo "<option value='".$comp['ID_COMPOSANT']."'";
							if ($comp['ID_COMPOSANT'] == $GLOBALS['id_comp']){
								echo " selected";
							}
							echo ">".$comp['LIBELLE_COMPOSANT']."</option>";
						}
				?>
          </select></td>
 	</tr>	<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_fonc'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_FONCTIONNALITE" onChange="recharger(ID_FONCTIONNALITE.value, ID_COMPOSANT.value);">
            <option value=''></option>
            <?php foreach ($GLOBALS['all_fonc'] as $i => $fonc){
							echo "<option value='".$fonc['ID_FONCTIONNALITE']."'";
							if ($fonc['ID_FONCTIONNALITE'] == $GLOBALS['id_fonc']){
								echo " selected";
							}
							echo ">".$fonc['LIBELLE_FONCTIONNALITE']."</option>";
						}
				?>
          </select></td>
 	</tr>    
	    
	<tr> 
        <td colspan="2">&nbsp;</td>
 	</tr>
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('nom_assoc'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="NOM_COMP_FONCTIONNALITE" value="<?php echo $val['NOM_COMP_FONCTIONNALITE']; ?>"></td>
 	</tr>    
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('ordre'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="ORDRE" value="<?php echo $val['ORDRE']; ?>"></td>
 	</tr>    
</table>
<br>
