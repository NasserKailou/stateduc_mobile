<?php $all_type_mes = array(  
							array ('CODE' => 'int', 'LIBELLE' => recherche_libelle_page('int') ),
							array ('CODE' => 'double', 'LIBELLE' => recherche_libelle_page('double') ),
							array ('CODE' => 'text', 'LIBELLE' => recherche_libelle_page('text') )
						 );
?>
<br>
<table align="center" width="400">
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_mes'); ?></td>
        <td width="60%"><INPUT style="width : 90%" readonly="1" type="text" size="3" name="ID_MESURE" value="<?php echo $val['ID_MESURE']; ?>"></td>
    </tr>
	
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('lib_mes'); ?></td>
        <td width="60%"><INPUT style="width : 90%" type="text" size="50" name="LIBELLE_MESURE" value="<?php echo $val['LIBELLE_MESURE']; ?>"></td>
    </tr>
	
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('type_mes'); ?></td>
        <td width="60%">
			<select name="TYPE_MESURE">
                <option value=''></option>
                <?php foreach ($all_type_mes as $i => $type_mes){
					echo "<option value='".$type_mes['CODE']."'";
					if ($type_mes['CODE'] == $val['TYPE_MESURE']){
						echo " selected";
					}
					echo ">".$type_mes['LIBELLE']."</option>";
				}
				?>
            </select></td>
    </tr>
	
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('exp_mes'); ?></td>
        <td width="60%"><INPUT style="width : 90%" type="text" size="30" name="EXPRESSION_FORMULE" value="<?php echo $val['EXPRESSION_FORMULE']; ?>"></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('lib_ent'); ?></td>
        <td width="60%"><INPUT style="width : 90%" type="text" size="30" name="LIBELLE_MESURE_ENTETE" value="<?php echo $val['LIBELLE_MESURE_ENTETE']; ?>"></td>
    </tr>

</table>
<br>
