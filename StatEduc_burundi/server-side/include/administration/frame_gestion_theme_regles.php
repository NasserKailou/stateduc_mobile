<br>
<table>
	<tr>
		<td><?php echo recherche_libelle_page('idth'); ?></td>
		<td><INPUT type="text" size="10" name="ID_THEME" value="<?php echo $val['ID_THEME']; ?>" readonly="1"></td>
	</tr>

	<tr>
		<td><?php echo recherche_libelle_page('idregth'); ?></td>
		<td><INPUT type="text" size="10" name="ID_REGLE_THEME" value="<?php echo $val['ID_REGLE_THEME']; ?>">
		&nbsp;&nbsp;<?php if(recherche_libelle_page('ordregth') <> '') echo recherche_libelle_page('ordregth'); else echo "Theme rule Order"; ?>
		&nbsp;&nbsp;<INPUT type="text" size="5" name="ORDRE_REGLE_THEME" value="<?php echo $val['ORDRE_REGLE_THEME']; ?>"></td>
	</tr>
	
	<tr>
		<td valign="top"><?php echo recherche_libelle_page('libregth'); ?></td>
		<td>
			<textarea name="LIBELLE_TRAD" cols="40" rows="2"><?php echo $val['LIBELLE_TRAD']; ?></textarea>
		</td>
	</tr>
	
	<tr>
		<td valign="top"><?php echo recherche_libelle_page('sqlregth'); ?></td>
		<td><textarea name="SQL_REGLE_THEME" cols="80" rows="8"><?php echo $val['SQL_REGLE_THEME']; ?></textarea></td>
	</tr>
	<?php if($this->btn_add <> true){ ?>
			<tr> 
					<td nowrap><?php echo recherche_libelle_page('regthasc'); ?></td>
					<td><input type='button' name='' value='...'
							onClick="OpenPopupRegThmAsc(<?php echo $val['ID_REGLE_THEME'];?>);"></td>
			</tr>
	<?php } ?>

	
</table>
<br>
