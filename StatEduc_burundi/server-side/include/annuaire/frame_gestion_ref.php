<script language="JavaScript" type="text/javascript">
	function raz_combo_options(combo){
		eval('document.Formulaire.'+combo+'.selectedIndex=0');
	}
	
	function disable(elem, bool){
		document.getElementById(elem).disabled = bool;
	}
	
	function activer_type_ref(type){
		if(type == 'R'){
			raz_combo_options('ID_FONCTIONNALITE')
			disable('ID_FONCTIONNALITE', true)
			disable('ID_RUBRIQUE', false)
		}else if(type == 'F'){
			raz_combo_options('ID_RUBRIQUE')
			disable('ID_RUBRIQUE', true)
			disable('ID_FONCTIONNALITE', false)
		}
	}
</script>
<?php $requete	='	SELECT  DICO_RUBRIQUE.*
					FROM    DICO_RUBRIQUE
					ORDER BY DICO_RUBRIQUE.LIBELLE_RUBRIQUE';
	//echo $requete ;			
	$all_ID_RUBRIQUE = $GLOBALS['conn_dico']->GetAll($requete);	
	
	$requete	='	SELECT  DICO_FONCTIONNALITE.*
					FROM    DICO_FONCTIONNALITE
					ORDER BY DICO_FONCTIONNALITE.LIBELLE_FONCTIONNALITE';
	//echo $requete ;			
	$all_ID_FONCTIONNALITE = $GLOBALS['conn_dico']->GetAll($requete);	
	
	$all_type_ref = array(  
						array ('CODE' => 'R', 'LIBELLE' => recherche_libelle_page('Rubr') ),
						array ('CODE' => 'F', 'LIBELLE' => recherche_libelle_page('Func') )
					 );

?>
<table align="center" border="1" width="450">
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('id_ref'); ?></td>
        <td width="50%"><INPUT style="width : 100%;" readonly="1" type="text" name="ID_REFERENCE" value="<?php echo $val['ID_REFERENCE']; ?>"></td>
    </tr>    
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('nom_ref'); ?></td>
        <td width="50%"><INPUT style="width : 100%;" type="text" name="NOM_REFERENCE" value="<?php echo $val['NOM_REFERENCE']; ?>"></td>
    </tr>
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('type_ref'); ?></td>
        <td width="50%">
		<select style="width : 350;" name="TYPE_REFERENCE" onchange="activer_type_ref(this.value)">
							<option value=''></option>
							<?php foreach ($all_type_ref as $i => $type_ref){
												echo "<option value='".trim($type_ref['CODE'])."'";
												if (trim($type_ref['CODE']) == trim($val['TYPE_REFERENCE'])){
														echo " selected";
												}
												echo ">".$type_ref['LIBELLE']."</option>";
										}
								
								?>
					</select>		</td>
    </tr>
	<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('id_rubr'); ?></td>
        <td width="50%"><select style="width : 350;" id="ID_RUBRIQUE" name="ID_RUBRIQUE" onchange="raz_combo_options('ID_FONCTIONNALITE')">
							<option value=''></option>
							<?php foreach ($all_ID_RUBRIQUE as $i => $tab){
												echo "<option value='".trim($tab['ID_RUBRIQUE'])."'";
												if (trim($tab['ID_RUBRIQUE']) == trim($val['ID_RUBRIQUE'])){
														echo " selected";
												}
												echo ">".$tab['LIBELLE_RUBRIQUE']."</option>";
										}
								
								?>
					</select></td>
    </tr>
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('id_fonc'); ?></td>
        <td width="50%"><select style="width : 350;" id="ID_FONCTIONNALITE" name="ID_FONCTIONNALITE" onchange="raz_combo_options('ID_RUBRIQUE')">
							<option value=''></option>
							<?php foreach ($all_ID_FONCTIONNALITE as $i => $tab){
												echo "<option value='".trim($tab['ID_FONCTIONNALITE'])."'";
												if (trim($tab['ID_FONCTIONNALITE']) == trim($val['ID_FONCTIONNALITE'])){
														echo " selected";
												}
												echo ">".$tab['LIBELLE_FONCTIONNALITE']."</option>";
										}
								
								?>
					</select></td>
    </tr>
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('tab_name'); ?></td>
        <td width="50%"><INPUT style="width : 100%;" type="text" name="RADICAL_TABLE_NAME" value="<?php echo $val['RADICAL_TABLE_NAME']; ?>"></td>
    </tr>
<tr> 
        <td width="50%" nowrap="nowrap"><?php echo recherche_libelle_page('act_ref'); ?></td>
        <td width="50%"><input name="ACTIVER_REF" type="checkbox" value="1" <?php if($val['ACTIVER_REF']=='1') echo' checked';?> /></td>
    </tr>
</table>
<br>
