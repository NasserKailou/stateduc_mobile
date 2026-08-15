<?php lit_libelles_page('/load_sql.php');
?><br><br>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<script language="javascript" type="text/javascript">
	function OpenPopSaveAccessDB() {
		var	popup	=	window.open('administration.php?val=SaveAccessDB','PopSAveAccessDB', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=no,status=no,resizable=no,width=320, height=35, left=400, top=200')
		popup.document.close();
		popup.focus();
	} 
</script>
<form method="post" action="" name="form_load_sql">
    
  <table width="500" border="0" align="center" cellpadding="3" cellspacing="0">
    <caption><?php echo recherche_libelle_page('TitLoad'); ?></caption>
      <tr> 
      <TD width="109" style="text-align:center; vertical-align:middle"><u><?php echo recherche_libelle_page('RepSQL'); ?></u></TD>
        <TD>
		<div style="position :absolute ;">
		<input type="file" id="DOSSIER_SOURCE" name="DOSSIER_SOURCE" style="filter :alpha(opacity=0); opacity:0" size="50" 
		onchange="document.form_load_sql.rep_source.value=document.form_load_sql.DOSSIER_SOURCE.value">
		</div>
		<input size="50" type="text" name="rep_source" <?php echo 'value="'.$_POST['rep_source'].'" ';?> >
		<input type="button" value="..." onclick="document.getElementById('DOSSIER_SOURCE').click();">
		</TD>
    </tr>
    <tr> 
      <td style="text-align:center; vertical-align:middle" colspan="2"><u><?php echo recherche_libelle_page('exec'); ?></u> <INPUT name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="21" height="22" border="0"  value="Envoyer" class="envoyer"></td>
    </tr>
  </table>
  <?php if($GLOBALS['conn']->databaseType=='access'){ ?>
	  <br />
	  <table width="500" border="0" align="center" cellpadding="3" cellspacing="0">
		  <tr> 
		  <TD style="text-align:center; vertical-align:middle"><div align="center"><u><?php echo recherche_libelle_page('otr_act'); ?></u></div></TD>
		<TD  >
			<INPUT  style='width:100%;'  type='button' <?php echo "value='".recherche_libelle_page('base_save')."'"; ?> onClick='OpenPopSaveAccessDB();'>
			</TD>
		</tr>
	  </table>
 <?php }
 
	if( count($_POST) > 0 ){ ?>
	 <br>
     <table width="500" border="0" align="center">
	 <tr><td align="left">
	 <?php $sql_file = str_replace('\\','/',$_POST['rep_source']);
		if (file_exists($sql_file)){
			
			$lines = file($sql_file);
			$conn_db = $GLOBALS['conn_dico'];
			$chaine_sql = '';
			$nb_ch_sql = 0;
			$nb_ch_sql_fail = 0;
			foreach($lines as $line) {
				if(preg_match('/#conn_database#/', $line) || preg_match('/#conn_base#/', $line)){
					$conn_db = $GLOBALS['conn'];
					continue;
				}
				if(preg_match('/#conn_dico#/', $line)){
					$conn_db = $GLOBALS['conn_dico'];
					continue;
				}
				if(preg_match('/#begin/', $line)){
					$chaine_sql = '';
					continue;
				}
				if(!preg_match('/#begin/', $line) && !preg_match('/#end/', $line) && !preg_match('/#conn_database#/', $line) && !preg_match('/#conn_base#/', $line) && !preg_match('/#conn_dico#/', $line) && trim($line)<>''){
					$chaine_sql .= $line;
				}
				if(preg_match('/#end/', $line)){
					$nb_ch_sql++;
					$mess = '';
					echo '<br>' . $chaine_sql .'';
					( ($conn_db->Execute($chaine_sql))===false ) ? ($ok = false) : ($ok   = true); 
					if($ok==false) $nb_ch_sql_fail++;
					($ok==true) ? ($mess = '<br><span style="color: #0000FF;"> : OK ! ... </span>') : ($mess = '<br><span style="color: #FF0000;"> : PB ! ... </span>');
					echo $mess . '<br>';
				}
			}		
		}else{
			print ' : File not Found ... <br>';
		}
		print '<br> '.$nb_ch_sql.' SQL Instruction(s) Executed ....... <span style="color: #FF0000;">'.$nb_ch_sql_fail.' Failure(s)</span><br>';
		?>
		</td></tr></table>
		<?php }
?>
</form>
