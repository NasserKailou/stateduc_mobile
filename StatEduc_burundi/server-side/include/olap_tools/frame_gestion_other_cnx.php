<?php $requete                = ' SELECT * FROM DICO_OLAP_TYPE_SGBD;';
	//print $requete;
	$all_sgbd				= $GLOBALS['conn_dico']->GetAll($requete);
?>
<br>
<table align="center" width="450">
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_cnx'); ?></td>
        <td width="60%"><INPUT style="width : 95%;" readonly="1" type="text" size="3" name="ID_CNX" value="<?php echo $val['ID_CNX']; ?>"></td>
    </tr>
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('sgbd'); ?></td>
        <td width="60%"><select name="SGBD" style="90%">
              <option value=''></option>
              <?php foreach ($all_sgbd as $i => $sgbd){
					echo "<option value='".$sgbd['SGBD']."'";
					if ($sgbd['SGBD'] == $val['SGBD']){
						echo " selected";
					}
					echo ">".$sgbd['SGBD']."</option>";
				}
				?>
            </select>
			</td>
    	</tr>
		<tr> 
        <td width="40%"><?php echo recherche_libelle_page('provider'); ?></td>
        <td width="60%">
			<select name="PROVIDER" style="90%">
              <option value=''></option>
              <?php foreach ($all_sgbd as $i => $sgbd){
					echo "<option value='".$sgbd['PROVIDER']."'";
					if ($sgbd['PROVIDER'] == $val['PROVIDER']){
						echo " selected";
					}
					echo ">".$sgbd['PROVIDER']."</option>";
				}
				?>
            </select>
			</td>
    	</tr>		
		<tr> 
        <td width="40%"><?php echo recherche_libelle_page('desc'); ?></td>
        <td width="60%"><INPUT style="width : 95%;" type="text" size="50" name="DESCRIPTION" value="<?php echo $val['DESCRIPTION']; ?>"></td>
    </tr>
		<tr> 
        <td width="40%"><?php echo recherche_libelle_page('serveur'); ?></td>
        <td width="60%"><INPUT style="width : 95%;" type="text" size="50" name="SERVEUR" value="<?php echo $val['SERVEUR']; ?>"></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('db'); ?></td>
        <td width="60%"><INPUT style="width : 95%;" type="text" size="50" name="DB" value="<?php echo $val['DB']; ?>"></td>
    </tr>
	
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('user'); ?></td>
        <td width="60%"><INPUT style="width : 95%;" type="text" size="30" name="NOM_USER" value="<?php echo $val['NOM_USER']; ?>"></td>
    </tr>
	
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('pwd'); ?></td>
        <td width="60%"><INPUT style="width : 95%;" type="text" size="30" name="PASSWORD" value="<?php echo $val['PASSWORD']; ?>"></td>
    </tr>
</table>
<br>
