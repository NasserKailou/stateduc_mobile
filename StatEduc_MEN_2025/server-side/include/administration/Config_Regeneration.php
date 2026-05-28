<script language="JavaScript" type="text/javascript">
		<!--
				function logout_parent(){
						parent.location  = '<?php echo$GLOBALS['SISED_AURL'];?>index.php?val=logout';
				}
		-->
</script>
<body onBlur="self.focus()">

<?php if(isset($_POST['post_regen']) or isset($_GET['langue_regen'])){ ?>
				<script type="text/javascript">
						<!-- 
						window.resizeTo(320,35); 
						-->
				</script>
				<br><br><br><br><br>
				<?php include $GLOBALS['SISED_PATH_INC'] . 'administration/progressbar.php'; 
				
				if( ($_POST['reg_menus'] == '1') or (isset($_GET['langue_regen'])) ){
						require_once $GLOBALS['SISED_PATH_INC'] . 'administration/generer_menu.php';
				}
				
				if( ($_POST['reg_templates'] == '1') or (isset($_GET['langue_regen'])) ){
						require_once $GLOBALS['SISED_PATH_INC'] . 'administration/generer_theme.php';
				}
 
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				//print("\t parent.location  = '".$GLOBALS['SISED_AURL']. "index.php?val=logout'; \n");
				if (!isset($_GET['langue_regen']) ){
						print("\t logout_parent();\n");
				}
				print("\t fermer();\n");
				print("\t //--> \n");
				print("</script>\n");
				echo "<script type='text/Javascript'>\n";
				echo "$.unblockUI();\n";
				echo "</script>\n";
				
				exit();	
		}
	
		//lit_libelles_page(__FILE__);
?>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS'] . $_SESSION['style']; ?>">
<br>
<br>
<br>
<br>
<form method="post" action="administration.php?val=regen" name="form1">
<INPUT name="post_regen" value="1" type="hidden">
<table align="center" cellpadding="3" cellspacing="0" border="0" class="table-login">
		<caption>REGENERATION</caption>
		<tr>
				<TD>
						MENUS :
				</TD>
				<TD>
						<INPUT type="checkbox" value="1" name="reg_menus" >
				</TD> 
		</tr>               
		<tr>
				<TD>
						TEMPLATES :
				</TD>
				<TD>
						<INPUT type="checkbox" value="1" name="reg_templates" >
				</TD> 
		</tr>               
		<tr align="center">
				    <td colspan=2 align="center"> <INPUT name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="21" height="22" border="0"  value="Envoyer" class="envoyer"></td>
		</tr>

</table>
</form>
</body>
