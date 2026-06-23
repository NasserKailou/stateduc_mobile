<script language="javascript" type="text/javascript">
	<!-- 
	//window.resizeTo(320,35);	
	-->
</script>
<script language="javascript" type="text/javascript">
	function decocher(val)
	{	
		if(val=="loc_cube")
		{	
			if(document.getElementById('gen_cubes_locaux').checked)
				document.getElementById('gen_cubes_locaux').checked=false;	
		}
		else if(val=="tab_fait")
		{	
			if(document.getElementById('gen_tables_faits').checked)
				document.getElementById('gen_tables_faits').checked=false;
		}
		else if(val=="del_tab_fait")
		{	
			if(document.getElementById('del_tables_faits').checked)
				document.getElementById('del_tables_faits').checked=false;
		}
		else if(val=="compact_db")
		{	
			if(document.getElementById('compact_db').checked)
				document.getElementById('compact_db').checked=false;
		}
	}
	function gen_batch()
	{	
		if(document.getElementById('gen_tables_faits').checked)
		{	
			var nom_fichier = 'olap.php?val=pop_olap_gen_menu&val_gen=fact_tab';
			var	popup	=	window.open(nom_fichier,'popGenAll', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=350, height=50, left=200, top=150')
			popup.document.close();
			popup.focus();
			//location.href='olap.php?val=olap_gen_all&val_gen=fact_tab';	
			location.href='olap.php?val=gen_batch&val_gen=loc_cube';
		}
		else if(document.getElementById('gen_cubes_locaux').checked)
		{
			var nom_fichier = 'olap.php?val=pop_olap_gen_menu&val_gen=loc_cube';
			var	popup	=	window.open(nom_fichier,'popGenAll', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=350, height=50, left=200, top=150')
			popup.document.close();
			popup.focus();
			//location.href='olap.php?val=olap_gen_all&val_gen=loc_cube';
			location.href='olap.php?val=gen_batch&val_gen=del_fact_tab';
		}
		else if(document.getElementById('del_tables_faits').checked)
		{	
			var nom_fichier = 'olap.php?val=pop_olap_gen_menu&val_gen=del_fact_tab';
			var	popup	=	window.open(nom_fichier,'popGenAll', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=350, height=50, left=200, top=150')
			popup.document.close();
			popup.focus();
			//location.href='olap.php?val=olap_gen_all&val_gen=del_fact_tab';	
			location.href='olap.php?val=gen_batch&val_gen=compact_db';
		}
		else if(document.getElementById('compact_db').checked)
		{	
			var nom_fichier = 'olap.php?val=pop_olap_gen_menu&val_gen=compact_db';
			var	popup	=	window.open(nom_fichier,'popGenAll', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=350, height=50, left=200, top=150')
			popup.document.close();
			popup.focus();
			//location.href='olap.php?val=olap_gen_all&val_gen=compact_db';
			location.href='olap.php?val=gen_batch&val_gen=compact_db';
		}
	}
	
</script>
<br><br><br><br><br>
<?php lit_libelles_page('/gestion_olap_gen_batch.php');
?>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS'] . $_SESSION['style']; ?>">
<br>
<br>
<br>
<br>
<INPUT name="post_regen" value="1" type="hidden">
<table align="center" cellpadding="3" cellspacing="0" border="0" class="table-login">
	<caption><?php echo recherche_libelle_page("gen_batch")?></caption>
	<tr>	
			<TD><?php echo recherche_libelle_page("tables_faits")?> :</TD>
			<TD><INPUT type="checkbox" value="1" id="gen_tables_faits" name="gen_tables_faits" <?php if(isset($_GET['val_gen']) && $_GET['val_gen']=='fact_tab') echo 'checked';?> onClick="decocher('loc_cube'); decocher('del_tab_fait'); decocher('compact_db')"></TD> 
	</tr>               
	<tr>
			<TD><?php echo recherche_libelle_page("cubes_locaux")?> :</TD>
			<TD><INPUT type="checkbox" value="1" id="gen_cubes_locaux" name="gen_cubes_locaux" <?php if(isset($_GET['val_gen']) && $_GET['val_gen']=='loc_cube') echo 'checked';?> onClick="decocher('tab_fait'); decocher('del_tab_fait'); decocher('compact_db')"></TD> 
	</tr>  
	<tr>
			<TD><?php echo recherche_libelle_page("del_tab_faits")?> :</TD>
			<TD><INPUT type="checkbox" value="1" id="del_tables_faits" name="del_tables_faits" <?php if(isset($_GET['val_gen']) && $_GET['val_gen']=='del_fact_tab') echo 'checked';?> onClick="decocher('tab_fait'); decocher('loc_cube'); decocher('compact_db')"></TD> 
	</tr>
	<tr>
			<TD><?php echo recherche_libelle_page("compact_db")?> :</TD>
			<TD><INPUT type="checkbox" value="1" id="compact_db" name="compact_db" <?php if(isset($_GET['val_gen']) && $_GET['val_gen']=='compact_db') echo 'checked';?> onClick="decocher('tab_fait'); decocher('loc_cube'); decocher('del_tab_fait')"></TD> 
	</tr>       
	<tr align="center">
			<td colspan=2 align="center"> <INPUT name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="21" height="22" border="0"  value="Envoyer" class="envoyer" onclick="gen_batch()"></td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
<?php //if(isset($_GET['val_gen']) && $_GET['val_gen']<>'') echo "var timer = setInterval(\"gen_batch()\", 500);\n";?>
</script>
