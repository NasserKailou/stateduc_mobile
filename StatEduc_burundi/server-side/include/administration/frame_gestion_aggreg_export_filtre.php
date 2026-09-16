<script language="javascript" type="text/javascript">
 function change_table(){
 	sel = document.getElementById('TABLE_FILTRE');
	table = sel.options[sel.selectedIndex].value;
	document.getElementById('TABLE_FILTRE_2').value = table;
 }
</script>
<?php lit_libelles_page('/frame_gestion_aggreg_export_filtre.php');

if(isset($_POST['TABLE_FILTRE']) && $_POST['TABLE_FILTRE']<>''){

	$_SESSION['FILTRE']['FILTRE_2']	=	true;
	$_SESSION['FILTRE']['TYPE_FILTRE_2']	=	$_POST['TABLE_FILTRE'];
	$_SESSION['FILTRE']['CODE_TYPE_FILTRE_2']	=	$GLOBALS['PARAM']['CODE'].'_'.$_POST['TABLE_FILTRE'];
	$_SESSION['FILTRE']['CODE_TYPE_FILTRE_2_ALIAS']	=	$GLOBALS['PARAM']['CODE'].'_'.$_POST['TABLE_FILTRE_2'];
	$_SESSION['FILTRE']['LIBELLE_TYPE_FILTRE_2']	=	$GLOBALS['PARAM']['LIBELLE'].'_'.$_POST['TABLE_FILTRE'];
	$_SESSION['FILTRE']['ORDRE_TYPE_FILTRE_2']	=	$GLOBALS['PARAM']['ORDRE'].'_'.$_POST['TABLE_FILTRE'];
	
	echo "<script type='text/Javascript' type='javascript'>
			parent.document.location.href='administration.php?val=aggregated_export&id_systeme=".$_GET['id_systeme']."&id_chaine=".$_GET['id_chaine']."&type_reg=".$_GET['type_reg']."'; 
			fermer();
		  </script>\n";	
}

?>
<br/>
<br/>
<br/>
<form name="form1" method="post" action="">
<table align="center">
	<tr>
		<td nowrap="nowrap"><?php echo recherche_libelle_page('tab_filtre'); ?></td>
		<td>
		<select name="TABLE_FILTRE" id="TABLE_FILTRE" style='width:240px' onchange="change_table()">
		<?php $col_main_tab = array();
			$nomenc_main_tab = array();
			$col_main_tab = $GLOBALS['conn']->MetaColumnNames($GLOBALS['PARAM']['ETABLISSEMENT']);
			foreach($col_main_tab as $col){
				if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$col) &&
					($col<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
					$nomenc_main_tab[] = substr($col,strlen($GLOBALS['PARAM']['CODE'])+1);
				}
			}
			echo "<option value=''></option>";
			foreach ($nomenc_main_tab as $tab){
				echo "<option value='".$tab."'";
				if (strtoupper($val['TABLE_FILTRE']) == strtoupper($tab)){
					echo " selected";
				}
				echo ">".$tab."</option>";
			}
		?>																				
		</select >
		</td>
	</tr>
	<tr>
		<td nowrap="nowrap"><?php echo recherche_libelle_page('tab_filtre2'); ?></td>
		<td><input name="TABLE_FILTRE_2" id="TABLE_FILTRE_2" style='width:240px' value="" /></td>
	</tr>
</table>
<br>
<table border='1' align="center">
	<tr>
		<td align="center">
		<INPUT type="submit" name='add_filtre' value='<?php echo recherche_libelle_page('ajouter'); ?>' style="width:100px" />
		</td>
	</tr>
</table>
</form>
