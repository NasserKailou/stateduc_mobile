
<br>
<table width="500" border="2">
	
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('lib_reg'); ?>  <b>&nbsp;&nbsp;<?php echo $val['ID_REGLE_ZONE']; ?> &nbsp;
	    <input type="hidden" name="ID_REGLE_ZONE" value="<?php echo $val['ID_REGLE_ZONE']; ?>" />
	  </b></td>
	  <td colspan="2" nowrap="nowrap" width="65%" style="text-align:center;"><input type="text" style="width:95%"   name="LIBELLE_TRAD" value="<?php echo $val['LIBELLE_TRAD']; ?>" /></td>
	</tr>
	<tr><td colspan="4" nowrap="nowrap" style="height:5px"></td></tr>
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('type_d'); ?></td>
	  <td colspan="2" nowrap="nowrap" width="65%"><select name="TYPE_DONNEES" style="width:100px" >
        <option value=''></option>
        <?php $conn 		= $GLOBALS['conn_dico'];
				$requete	="	SELECT  TYPE_DONNEES, CODE_TYPE_DONNEES, LIBELLE_TYPE_DONNEES   
											FROM   DICO_TYPE_DONNEES WHERE TYPE_DONNEES IN ('int', 'date', 'decimal')";
				$all_type_donnees 		= $GLOBALS['conn_dico']->GetAll($requete);

					foreach ($all_type_donnees as $i => $type_donnees){
							echo "<option value='".trim($type_donnees['TYPE_DONNEES'])."'";
							if (trim($type_donnees['TYPE_DONNEES']) == trim($val['TYPE_DONNEES'])){
									echo " selected";
							}
							echo ">".get_libelle($type_donnees['CODE_TYPE_DONNEES'],'DICO_TYPE_DONNEES',$type_donnees['LIBELLE_TYPE_DONNEES'])."</option>";
					}
				?>
      </select></td>
	</tr>
	<tr><td colspan="4" nowrap="nowrap" style="height:5px"></td></tr>
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('size_d'); ?></td>
	  <td colspan="2" nowrap="nowrap" width="65%"><input type="text" style="width:100px"  name="TAILLE_DONNEES" value="<?php echo $val['TAILLE_DONNEES']; ?>" /></td>
	</tr>
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('frmt_d'); ?></td>
	  <td colspan="2" nowrap="nowrap" width="65%" style="text-align:center;"><input type="text" style="width:95%"  name="FORMAT_DONNEES" value="<?php echo $val['FORMAT_DONNEES']; ?>" /></td>
	</tr>
	<tr><td colspan="4" nowrap="nowrap" style="height:5px"></td></tr>
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('ctrl_obl'); ?></td>
	  <td colspan="2" nowrap="nowrap" width="65%"><input name="CONTROLE_OBLIGATION" type="checkbox" value="1" <?php if($val['CONTROLE_OBLIGATION']=='1') echo' checked';?> /></td>
	</tr>
	<tr><td colspan="4" nowrap="nowrap" style="height:5px"></td></tr>
	<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('ctrl_uni'); ?></td>
	  <td colspan="2" nowrap="nowrap" width="65%"><input name="CONTROLE_UNICITE" type="checkbox" value="1" <?php if($val['CONTROLE_UNICITE']=='1') echo' checked';?> /></td>
	</tr>
	<tr><td colspan="4" nowrap="nowrap" style="height:5px"></td></tr>
	<tr>
		<td colspan="4" nowrap="nowrap" style="padding:0 2px;">
		<?php echo recherche_libelle_page('int_val'); ?> <INPUT name="INTERVALLE_VALEURS" type="checkbox" value="1" <?php if($val['INTERVALLE_VALEURS']=='1') echo' checked';?>>
		&nbsp; &nbsp; &nbsp; &nbsp;
		<?php echo recherche_libelle_page('val_min'); ?> <INPUT type="text" style="width:100px"  name="VALEUR_MINIMALE" value="<?php echo $val['VALEUR_MINIMALE']; ?>">
		&nbsp; &nbsp; &nbsp; &nbsp;
		<?php echo recherche_libelle_page('val_max'); ?> <INPUT type="text" style="width:100px"  name="VALEUR_MAXIMALE" value="<?php echo $val['VALEUR_MAXIMALE']; ?>"></td>
	</tr>
<tr><td colspan="4" nowrap="nowrap" style="height:5px"></td></tr>
<tr>
	  <td colspan="2" nowrap="nowrap"><?php echo recherche_libelle_page('val_enum'); ?></td>
	  <td colspan="2" nowrap="nowrap" width="65%" style="text-align:center;"><input type="text" style="width:95%" name="VALEURS_ENUM" value="<?php echo $val['VALEURS_ENUM']; ?>" /></td>
</tr>
</table>
<br>

