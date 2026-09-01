<br/>
<br/>
<?php lit_libelles_page('/controle_theme.php'); 
    echo $_SESSION['table_controle'];
?>
<br/>
<table align="center">
	<tr>
		<td align='center' nowrap="nowrap">
			<INPUT  style="width:100px;"  type="button" <?php echo 'value="'.recherche_libelle_page('Close').'"';?> onClick="fermer();">
		</td>
	</tr>
</table>

