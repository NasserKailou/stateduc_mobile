<script language="JavaScript" type="text/javascript">
	function recharger(id_fonc, id_rub) {
		location.href   = 'annuaire.php?val=pop_fonc_rub&id_fonc='+id_fonc+'&id_rub='+id_rub;
	}
</script>
<?php $requete                = ' SELECT DICO_FONCTIONNALITE.LIBELLE_FONCTIONNALITE
								FROM DICO_FONCTIONNALITE
								WHERE DICO_FONCTIONNALITE.ID_FONCTIONNALITE ='.$_GET['id_fonc'];
	//print $requete;
	$fonc_name = $GLOBALS['conn_dico']->GetOne($requete);

	
	$this->btn_new = false;
	
	
	if( isset($val['ID_RUBRIQUE']) && (trim($val['ID_RUBRIQUE'])<> '' ) ){
		$GLOBALS['id_rub'] = $val['ID_RUBRIQUE'] ;
	}
	
?>

<table align="center" border="1">
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_fonc'); ?></td>
      <td width="60%"><INPUT style="width : 30;" readonly="1" type="text" size="3" name="ID_FONCTIONNALITE" value="<?php echo $val['ID_FONCTIONNALITE']; ?>">
        <b>&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp;<?php echo $fonc_name; ?></b></td>
    </tr>    
	<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_rub'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_RUBRIQUE" onChange="recharger(ID_FONCTIONNALITE.value, ID_RUBRIQUE.value);">
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
        <td colspan="2">&nbsp;</td>
 	</tr>
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('nom_assoc'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="NOM_FONC_RUBRIQUE" value="<?php echo $val['NOM_FONC_RUBRIQUE']; ?>"></td>
 	</tr>    
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('ordre'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="ORDRE" value="<?php echo $val['ORDRE']; ?>"></td>
 	</tr>    
</table>
<br>
