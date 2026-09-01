<?php $requete	='	SELECT  DICO_TYPE_COMPOSANT.*
					FROM    DICO_TYPE_COMPOSANT';
	//echo $requete ;			
	$all_ID_TYPE_COMPOSANT = $GLOBALS['conn_dico']->GetAll($requete);

?>
<br />
<table align="center" border="1">
<tr> 
        <td width="40%" nowrap="nowrap"><?php echo recherche_libelle_page('id_comp'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" readonly="1" type="text" name="ID_COMPOSANT" value="<?php echo $val['ID_COMPOSANT']; ?>"></td>
    </tr>    
<tr> 
        <td width="40%" nowrap="nowrap"><?php echo recherche_libelle_page('lib_comp'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" name="LIBELLE_COMPOSANT" value="<?php echo $val['LIBELLE_COMPOSANT']; ?>"></td>
    </tr>
<tr> 
        <td width="40%" nowrap="nowrap"><?php echo recherche_libelle_page('id_typ_comp'); ?></td>
        <td width="60%"><select style="width : 250;" name="ID_TYPE_COMPOSANT">
							<option value=''></option>
							<?php foreach ($all_ID_TYPE_COMPOSANT as $i => $tab){
												echo "<option value='".trim($tab['ID_TYPE_COMPOSANT'])."'";
												if (trim($tab['ID_TYPE_COMPOSANT']) == trim($val['ID_TYPE_COMPOSANT'])){
														echo " selected";
												}
												echo ">".$tab['LIBELLE_TYPE_COMPOSANT']."</option>";
										}
								
								?>
					</select></td>
    </tr></table>
<br>
