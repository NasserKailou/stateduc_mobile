	<table border="1" align="center">
		<INPUT type="hidden" name="ID_SYSTEME" id="ID_SYSTEME" value="<?php echo $val['ID_SYSTEME']; ?>"/>
		<INPUT type="hidden" name="ID_THEME" id="ID_THEME" value="<?php echo $val['ID_THEME']; ?>"/>
		<INPUT type="hidden" name="NOM_TABLE" id="NOM_TABLE" value="<?php echo $val['NOM_TABLE']; ?>"/>
		<tr> 
			<td width="100%" align="center" nowrap="nowrap" colspan="3"><?php echo recherche_libelle_page('choix_table').' <b>'.$_SESSION['table']; ?></b></td>
		</tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr>
			<td nowrap="nowrap" align="center"><?php echo recherche_libelle_page('NomChamp'); ?></td>
			<td nowrap="nowrap" align="center"><?php echo recherche_libelle_page('NumLig');?></td>
			<td nowrap="nowrap" align="center"><?php echo recherche_libelle_page('NumCol');?></td>
			
		</tr>
		<tr> 
			<td><INPUT type="text" style="width:150px; background-color:#CCCCCC" name="NOM_CHAMP" value="<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']; ?>" readonly="1"/></td>
			<td><INPUT type="text" style="width:75px" name="NUM_LIGNE" value="<?php echo $val['NUM_LIGNE']; ?>"/></td>
			<td><INPUT type="text" style="width:75px" name="NUM_COLONNE" value="<?php echo $val['NUM_COLONNE']; ?>"/></td>
		</tr>
		<tr><td colspan="3">&nbsp;</td></tr>
	</table>
