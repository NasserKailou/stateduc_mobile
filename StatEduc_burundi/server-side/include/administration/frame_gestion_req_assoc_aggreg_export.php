<table>
	<input type="hidden" name="ID_REQUETE" id="ID_REQUETE" value="<?php echo $val['ID_REQUETE']; ?>" />
	<INPUT type="hidden" name="ID_SYSTEME" id="ID_SYSTEME" value="<?php echo $val['ID_SYSTEME']; ?>">
	<tr> 
        <td><?php echo recherche_libelle_page('nom_req'); ?></td>
        <td><INPUT size="30" type="text" name="NOM_REQUETE" value="<?php echo $val['NOM_REQUETE']; ?>"></td>
    </tr>
	<tr>
		<td valign="top"><?php echo recherche_libelle_page('sql_req'); ?></td>
		<td>
		<textarea name="SQL_REQUETE" cols="40" rows="10"><?php echo $val['SQL_REQUETE']; ?></textarea>
		</td>
	</tr>
	<tr> 
        <td><?php echo recherche_libelle_page('ordre_req'); ?></td>
        <td><INPUT size="2" type="text" name="ORDRE" value="<?php echo $val['ORDRE']; ?>"></td>
    </tr>
</table>
