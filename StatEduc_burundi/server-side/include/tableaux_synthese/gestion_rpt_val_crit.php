<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php lit_libelles_page('/gestion_olap_val_crit.php');
	
	if(count($_POST)){
		if(isset($_POST['nb_chps'])){
			$val_chps = "";	
			$premier_elt=true;	
			for( $i = 0 ; $i < $_POST['nb_chps'] ; $i++){
				if( isset($_POST['IMPORT_CHP_'.$i]) && (trim($_POST['IMPORT_CHP_'.$i]) <> '') ){
						if(!$premier_elt) $val_chps .=',';
						$premier_elt=false;
						$val_chps .= $_POST['IMPORT_CHP_'.$i] ;	
				}
			}
			if( $val_chps<>'' ){			
				echo '<script type="text/Javascript">
						parent.document.Formulaire.VALEUR.value=\''.$val_chps.'\' ;
						fermer();
					  </script>';
			}
		}
	}
	
	
	
	
	if(isset($_GET['nom_table']) && $_GET['nom_table']<>'' && isset($_GET['nom_chp']) && $_GET['nom_chp']<>'')
	{
		$nom_table = $_GET['nom_table'];
		$nom_chp = $_GET['nom_chp'];
		
		$requete             = 'SELECT DISTINCT '.$nom_table.'.'.$nom_chp.' 
								FROM '.$nom_table.' 
								ORDER BY '.$nom_table.'.'.$nom_chp;
		$all_chps = $GLOBALS['conn']->GetAll($requete);
		
		if(isset($_GET['val_chp']) && $_GET['val_chp']<>''){
			$tab_val_chps = explode(',',$_GET['val_chp']);
		}
	} 
?>
<br /><br /><br />
<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
<table align="center" border="1" width="450">
    
    <tr> 
        <td width="100%" align="center"><?php echo recherche_libelle_page('choix_table').' : '.$nom_table; ?></td>
    </tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<?php if(isset($all_chps) && count($all_chps) > 0 ){?>
	<tr><td colspan="2">
        <table width="100%">
				<tr> 
					<td align=center width="70%"><?php echo "".recherche_libelle_page('nom_chp')." : ".$nom_chp;?></td>
					<td align=center width="30%"><?php echo "".recherche_libelle_page('select')."";?></td>
				</tr>
				<?php foreach ($all_chps as $i => $chp){
						//( isset($_POST['IMPORT_CHP_'.$i]) && (trim($_POST['IMPORT_CHP_'.$i]) <> '') ) ? ($checked = ' CHECKED') : ($checked = '') ;
						if(isset($tab_val_chps) && in_array($chp[get_champ_extract($nom_chp)],$tab_val_chps)) $checked = ' CHECKED'; else  $checked = '' ;
				?>
					<tr> 
							<td><INPUT style="width:100%" type='text' name="<?php echo'CHP_'.$i;?>" value="<?php echo $chp[get_champ_extract($nom_chp)];?>" readonly="1"></td>
							<td align="center"><INPUT type="checkbox" name="<?php echo'IMPORT_CHP_'.$i;?>"   value="<?php echo $chp[get_champ_extract($nom_chp)] ?>" <?php echo $checked; ?>></td>
					</tr>
				<?php }
				?>
					
				<tr><td colspan=2 align='center'>&nbsp;</td></tr>

			<tr> 
					<td colspan=2 align='center' nowrap="nowrap">
						<INPUT   style="width:50%;"  type='submit' name='Input' <?php echo 'value="'.recherche_libelle_page('submit').'"';?>>&nbsp;&nbsp;&nbsp;
						<INPUT   style="width:45%;"  type="button" <?php echo 'value="'.recherche_libelle_page('fermer').'"';?> onClick="javascript:fermer();">
					</td>
			</tr>
											 
					</table>
			</td></tr>
			<input type="hidden" name="nb_chps" value="<?php echo count($all_chps); ?>" />
		<?php }?>		
</table>
</FORM>

