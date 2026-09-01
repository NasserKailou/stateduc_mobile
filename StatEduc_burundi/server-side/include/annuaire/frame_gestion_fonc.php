<?php $requete	='	SELECT  DICO_TYPE_FONCTIONNALITE.*
					FROM    DICO_TYPE_FONCTIONNALITE';
	//echo $requete ;			
	$all_ID_TYPE_FONCTIONNALITE = $GLOBALS['conn_dico']->GetAll($requete);	
	
	$requete	='	SELECT DICO_REPORT.ID, DICO_REPORT.LIBELLE_REPORT
					FROM DICO_REPORT
					WHERE DICO_REPORT.ID_TYPE_REPORT = 2;';
	//echo $requete ;			
	$all_rpts = $GLOBALS['conn_dico']->GetAll($requete);

?><br />
<table align="center" border="1">
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('id_fonc'); ?></td>
        <td width="50%"><INPUT style="width : 100%;" readonly="1" type="text" name="ID_FONCTIONNALITE" value="<?php echo $val['ID_FONCTIONNALITE']; ?>"></td>
    </tr>    
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('lib_fonc'); ?></td>
        <td width="50%"><INPUT style="width : 100%;" type="text" name="LIBELLE_FONCTIONNALITE" value="<?php echo $val['LIBELLE_FONCTIONNALITE']; ?>"></td>
    </tr>
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('id_typ_fonc'); ?></td>
        <td width="50%"><select style="width : 300;" name="ID_TYPE_FONCTIONNALITE">
							<option value=''></option>
							<?php foreach ($all_ID_TYPE_FONCTIONNALITE as $i => $tab){
												echo "<option value='".trim($tab['ID_TYPE_FONCTIONNALITE'])."'";
												if (trim($tab['ID_TYPE_FONCTIONNALITE']) == trim($val['ID_TYPE_FONCTIONNALITE'])){
														echo " selected";
												}
												echo ">".$tab['LIBELLE_TYPE_FONCTIONNALITE']."</option>";
										}
								
								?>
					</select></td>
    </tr><tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('id_rpt'); ?></td>
        <td width="50%"><select style="width : 300;" name="ID">
							<option value=''></option>
							<?php foreach ($all_rpts as $i => $tab){
												echo "<option value='".trim($tab['ID'])."'";
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
