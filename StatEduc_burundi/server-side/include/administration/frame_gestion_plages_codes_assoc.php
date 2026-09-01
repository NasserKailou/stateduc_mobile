<table align="center">
	<tr> 
        <td><?php echo recherche_libelle_page('descr_champ_plage'); ?></td>
        <td><b><?php echo $_SESSION['descr_champ_plage']; ?></b></td>
    </tr>
	<tr>
		<input type="hidden" name="NOM_CHAMP" id="NOM_CHAMP" value="<?php echo $val['NOM_CHAMP']; ?>" />
		<input type="hidden" name="CODE_TYPE_REGROUP" id="CODE_TYPE_REGROUP" value="<?php echo $val['CODE_TYPE_REGROUP']; ?>" />
		<td nowrap="nowrap"><?php echo recherche_libelle_page('nom_champ_rattach'); ?></td>
		<td>
		<select name="NOM_CHAMP_ASSOC" id="NOM_CHAMP_ASSOC">
		<?php $col_main_tab = array();
			$nomenc_main_tab = array();
			$col_main_tab = $GLOBALS['conn']->MetaColumnNames($GLOBALS['PARAM']['ETABLISSEMENT']);
			echo "<option value=''></option>";
			if($_GET['nom_champ'] <> $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
				echo "<option value='".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."'";
				if ($val['NOM_CHAMP_ASSOC'] == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
					echo " selected";
				}
				echo ">".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."</option>\n";
			}
			foreach($col_main_tab as $col){
				if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$col)){
					echo "<option value='".$col."'";
					if ($val['NOM_CHAMP_ASSOC'] == $col){
						echo " selected";
					}
					echo ">".$col."</option>\n";
				}
			}
		?>																				
		</select >
		</td>
	</tr>
	<tr> 
        <td><?php echo recherche_libelle_page('ordre_champ_rattach'); ?></td>
        <td><INPUT type="text" size="5" name="ORDRE_CHAMP_ASSOC" value="<?php echo $val['ORDRE_CHAMP_ASSOC']; ?>"/></td>
    </tr>
</table>
<script type="text/javascript">
	jQuery('#NOM_CHAMP_ASSOC').uniform();
</script>