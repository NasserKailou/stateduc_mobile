<?php $requete	='	SELECT  DICO_TYPE_RUBRIQUE.*
					FROM    DICO_TYPE_RUBRIQUE';
	//echo $requete ;			
	$all_ID_TYPE_RUBRIQUE = $GLOBALS['conn_dico']->GetAll($requete);	
	
	$requete	='	SELECT DICO_REPORT.ID, DICO_REPORT.LIBELLE_REPORT
					FROM DICO_REPORT
					WHERE DICO_REPORT.ID_TYPE_REPORT = 2;';
	//echo $requete ;			
	$all_rpts = $GLOBALS['conn_dico']->GetAll($requete);

	// récupération des fonctionnalités n'étant pas liées à un état
	$requete            = ' SELECT ID_FONCTIONNALITE, LIBELLE_FONCTIONNALITE 
							FROM DICO_FONCTIONNALITE
							WHERE ID  Is Null
							ORDER  BY LIBELLE_FONCTIONNALITE;';
	$GLOBALS['all_fonc'] = $GLOBALS['conn_dico']->GetAll($requete);

	if( isset($val['ID_RUBRIQUE']) && (trim($val['ID_RUBRIQUE']) <> '') ){
		$requete                = ' SELECT ID_FONCTIONNALITE
									FROM DICO_FONC_RUBRIQUE
									WHERE ID_RUBRIQUE = ' . $val['ID_RUBRIQUE'];
		//print $requete;
		$val['ID_FONCTIONNALITE'] = $GLOBALS['conn_dico']->GetOne($requete);
	}
?>
<br />
<table align="center" border="1" width="450">
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('id_rubr'); ?></td>
        <td width="50%"><INPUT style="width : 100%;" readonly="1" type="text" name="ID_RUBRIQUE" value="<?php echo $val['ID_RUBRIQUE']; ?>"></td>
    </tr>    
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('lib_rubr'); ?></td>
        <td width="50%"><INPUT style="width : 100%;" type="text" name="LIBELLE_RUBRIQUE" value="<?php echo $val['LIBELLE_RUBRIQUE']; ?>"></td>
    </tr>
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('id_typ_rubr'); ?></td>
        <td width="50%"><select style="width : 350;" name="ID_TYPE_RUBRIQUE">
							<option value=''></option>
							<?php foreach ($all_ID_TYPE_RUBRIQUE as $i => $tab){
												echo "<option value='".trim($tab['ID_TYPE_RUBRIQUE'])."'";
												if (trim($tab['ID_TYPE_RUBRIQUE']) == trim($val['ID_TYPE_RUBRIQUE'])){
														echo " selected";
												}
												echo ">".$tab['LIBELLE_TYPE_RUBRIQUE']."</option>";
										}
								
								?>
					</select></td>
    </tr>
<tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_fonc'); ?>
        <input type="hidden" name="ID_FONC_BASE" value="<?php echo $val['ID_FONCTIONNALITE'] ; ?>" /></td>
        <td width="60%"><select  style="width : 350;" name="ID_FONCTIONNALITE">
          <option value=''></option>
          <?php foreach ($GLOBALS['all_fonc'] as $i => $fonc){
							echo "<option value='".$fonc['ID_FONCTIONNALITE']."'";
							if ($fonc['ID_FONCTIONNALITE'] == $val['ID_FONCTIONNALITE']){
								echo " selected";
							}
							echo ">".$fonc['LIBELLE_FONCTIONNALITE']."</option>";
						}
				?>
        </select></td>
</tr>
	<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('id_rpt'); ?></td>
        <td width="50%"><select style="width : 350;" name="ID">
							<option value=''></option>
							<?php foreach ($all_rpts as $i => $tab){
												echo "<option value='". trim($tab['ID'])."'";
												if (trim($tab['ID']) == trim($val['ID'])){
													echo " selected";
												}
												echo ">".$tab['LIBELLE_REPORT']."</option>";
										}
								
								?>
					</select></td>
    </tr>
	</table>
<br>
