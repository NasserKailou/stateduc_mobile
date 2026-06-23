<script language="JavaScript" type="text/javascript">
	function recharger(id_syst) {
		location.href   = 'annuaire.php?val=pop_fonc_sys&id_fonc=<?php echo $_GET['id_fonc']?>&id_syst='+id_syst;
	}
</script>
<?php $requete                = ' SELECT DICO_FONCTIONNALITE.LIBELLE_FONCTIONNALITE
								FROM DICO_FONCTIONNALITE
								WHERE DICO_FONCTIONNALITE.ID_FONCTIONNALITE ='.$_GET['id_fonc'];
	//print $requete;
	$fonc_name = $GLOBALS['conn_dico']->GetOne($requete);
	
		
	$all_page_ori = array(  
							array ('CODE' => 'L', 'LIBELLE' => recherche_libelle_page('paysage') ),
							array ('CODE' => 'P', 'LIBELLE' => recherche_libelle_page('portrait') )
						 );
						 
	$all_ori_mes = array(  
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('colonne') ),
							array ('CODE' => '2', 'LIBELLE' => recherche_libelle_page('ligne') )
						 );
						 
	$all_align_mes = array(  
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('gauche') ),
							array ('CODE' => '2', 'LIBELLE' => recherche_libelle_page('centre') ),
							array ('CODE' => '3', 'LIBELLE' => recherche_libelle_page('droite') )
						 );
						 
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
    </tr>
	<tr> 
        <td width="40%"><b><?php echo recherche_libelle_page('id_syst'); ?></b></td>
        <td width="60%"><select  style="width : 250;" name="ID_SYSTEME" onChange="recharger(this.value);">
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
        <td width="40%"><?php echo recherche_libelle_page('fonc_sys'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="NOM_FONCTIONNALITE_SYSTEME" value="<?php echo $val['NOM_FONCTIONNALITE_SYSTEME']; ?>"></td>
 	</tr>    
  
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('activer'); ?></td>
        <td width="60%"><input name="ACTIVER" type="checkbox" value="1" <?php if($val['ACTIVER']=='1') echo' checked';?> /></td>
 	</tr>    
  
 </table>
<br>
