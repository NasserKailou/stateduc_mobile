<?php lit_libelles_page('/import.php');
	if( count($_POST) > 0 ){
			$_SESSION['config_import'] = $_POST;
	}else{
		$import_all = 1;
	}
	$checked = '';
	if (count($_SESSION['config_import'] ) > 0){
		if (isset($_SESSION['config_import']['rep_source'])){
			$dossier_source = $_SESSION['config_import']['rep_source'] ;
		}
		if (isset($_SESSION['config_import']['log_file'])){
			$log_file = $_SESSION['config_import']['log_file'] ;
		}
	}
?><br><br>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<form method="post" action="" name="form_import">
<INPUT name="action_import" value="1" type="hidden">
    
  <table width="496" border="0" align="center" cellpadding="3" cellspacing="0" class="table-login">
    <caption>CONFIGURATION IMPORTATION</caption>
      <tr> 
      <TD width="109" height="34"  ><?php echo recherche_libelle_page('RepSource'); ?></TD>
            <TD  >	<div style="position :absolute ;">
		<input type="file" name="DOSSIER_SOURCE" style="filter :alpha(opacity=0)" size="50" 
		onchange="document.form_import.rep_source.value=document.form_import.DOSSIER_SOURCE.value">
		</div>
		<input size="50" type="text" name="rep_source" <?php echo 'value="'.$dossier_source.'" ';?> >
		<INPUT type="button" value="...">

			</TD>
        </tr>
        <tr> 
      <TD height="33" ><?php echo recherche_libelle_page('Dossier_lo'); ?></TD>
      <TD ><!-- <div style="position :absolute ;">
		<input type="file" name="DEST_LOG" value= "<?php //echo $dossier_dest_log; ?>" style="filter :alpha(opacity=0)" size="50" 
		onchange="document.form_import.rep_dest_log.value=document.form_import.DEST_LOG.value">
		</div> -->
		<input size="50" type="text" name="log_file" <?php echo 'value="'.$log_file.'" ';?> >
		<!-- <INPUT type="button" value="..."> --></TD>
        </tr>
    <tr> 
      <td align="center" colspan="2"> <INPUT name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="21" height="22" border="0"  value="Envoyer" class="envoyer"></td>
        </tr>
    </table>

</form>
<?php if( count($_POST) > 0 ){
			$det_pp = 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=no,status=no,resizable=no,width=320, height=50, left=400, top=200';
			ouvrir_popup('administration.php?val=aggregated_action_import','popup',$det_pp);		
	}
?>

