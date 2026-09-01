<script language="JavaScript" type="text/javascript">
	function recharger(id_fonc, id_syst) {
		location.href   = 'annuaire.php?val=pop_fonc_niv_rub&id_fonc='+id_fonc+'&id_syst='+id_syst;
	}
</script>
<?php $requete                = ' SELECT DICO_FONCTIONNALITE.LIBELLE_FONCTIONNALITE
								FROM DICO_FONCTIONNALITE
								WHERE DICO_FONCTIONNALITE.ID_FONCTIONNALITE ='.$_GET['id_fonc'];
	//print $requete;
	$fonc_name = $GLOBALS['conn_dico']->GetOne($requete);
	
	$this->btn_new = false;
	
	
	if( isset($val['ID_SYSTEME']) && (trim($val['ID_SYSTEME'])<> '' ) ){
		$GLOBALS['id_syst'] = $val['ID_SYSTEME'] ;
	}
	
	
?>

<table align="center" border="1">
<tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_fonc'); ?></td>
      <td width="60%"><INPUT style="width : 30;" readonly="1" type="text" size="3" name="ID_FONCTIONNALITE" value="<?php echo $val['ID_FONCTIONNALITE']; ?>">
        <b>&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp;<?php echo $fonc_name; ?></b></td>
    </tr>	<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_syst'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_SYSTEME" onChange="recharger(ID_FONCTIONNALITE.value, ID_SYSTEME.value);">
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
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="NOM_ASSOC_FONC_NIV" value="<?php echo $val['NOM_ASSOC_FONC_NIV']; ?>"></td>
 	</tr>    
 </table>
<br>
