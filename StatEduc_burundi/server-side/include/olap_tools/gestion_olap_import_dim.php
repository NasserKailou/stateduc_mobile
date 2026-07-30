<script language="JavaScript" type="text/javascript">
	function recharger(id_olap_for_import) {
		location.href   = 'olap.php?val=pop_import_dim&id_olap=<?php echo $_GET['id_olap']?>&id_olap_for_import='+id_olap_for_import;
	}
</script>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php lit_libelles_page('/gestion_olap_import_dim.php');
	
	if(count($_POST)){
		if(isset($_POST['nb_dims'])){
			$tab_dim_import = array();
			
			//Requete utilisée pour contrôler l'importation multiple de la même dimension
			$req_dim_name = 'SELECT LIBELLE_DIMENSION FROM DICO_OLAP_DIMENSION WHERE ID_OLAP = '.$_GET['id_olap'];
			$rs_dim_name= $GLOBALS['conn_dico']->GetAll($req_dim_name);
			
			$tab_dim_name=array();
			foreach($rs_dim_name as $rs)
			{
				$tab_dim_name[]=$rs['LIBELLE_DIMENSION'];
			}
			for( $i = 0 ; $i < $_POST['nb_dims'] ; $i++){
				if( isset($_POST['IMPORT_DIM_'.$i]) && (trim($_POST['IMPORT_DIM_'.$i]) <> '') ){
					if(!in_array($_POST['DIM_'.$i],$tab_dim_name)) // Pour eviter l'importation multiple de la même dimension
						$tab_dim_import[] = $_POST['IMPORT_DIM_'.$i] ;
				}
			}
			
			if( count( $tab_dim_import ) ){
				
                require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/import_olap_dim.class.php';
                $liste_dim=$tab_dim_import;
				$tab_dim_import=null;
                $id_olap=$_GET['id_olap'];
                $import_dim=new import_dim_olap($liste_dim,$id_olap);
                $import_dim->get_donnees_dim();                
                $import_dim->maj_base_olap();
				
				echo '<script type="text/Javascript">
						parent.document.location.href=\'olap.php?val=pop_olap_dim&id_olap='.$_GET['id_olap'].'\';
					 </script>';
			}
		}
	}
	
	$requete                 = 'SELECT DICO_OLAP.ID_OLAP, DICO_OLAP.THEME_NAME
								FROM DICO_OLAP
								WHERE DICO_OLAP.ID_OLAP <> '.$_GET['id_olap'].'
								ORDER BY DICO_OLAP.THEME_NAME;';
	//echo " <br> $requete <br>";
	$all_olap = $GLOBALS['conn_dico']->GetAll($requete);
	
	( isset($_GET['id_olap_for_import']) && (trim($_GET['id_olap_for_import']) <> '') ) ? ($id_olap = $_GET['id_olap_for_import']) : ($id_olap = $all_olap[0]['ID_OLAP']) ;

	
	$requete                 = 'SELECT DICO_OLAP_DIMENSION.ID_DIMENSION, DICO_OLAP_DIMENSION.LIBELLE_DIMENSION
								FROM DICO_OLAP_DIMENSION
								WHERE DICO_OLAP_DIMENSION.ID_OLAP='.$id_olap.'
								ORDER BY DICO_OLAP_DIMENSION.ORDRE;';
	//echo " <br> $requete <br>";
	$all_dims = $GLOBALS['conn_dico']->GetAll($requete);

?>
<br /><br /><br />
<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
<table align="center" border="1" width="450">
    
    <tr> 
        <td width="45%"><?php echo recherche_libelle_page('choix_olap'); ?></td>
      <td width="55%">
	  		<select style="width : 100%;" name="ID_OLAP" onchange="recharger(this.value)">
                <?php foreach ($all_olap as $i => $olap ){
					echo "<option value='".$olap['ID_OLAP']."'";
					if ( trim($olap['ID_OLAP']) == trim($id_olap)){
						echo " selected";
					}
					echo ">".$olap['THEME_NAME']."</option>";
				}
				?>
            </select></td>
    </tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<?php if( count($all_dims) > 0 ){?>
	<tr><td colspan="2">
        <table width="100%">
				<tr> 
					<td align=center width="75%"><?php echo"".recherche_libelle_page('nom_dim')."";?></td>
					<td align=center width="25%"><?php echo"".recherche_libelle_page('import')."";?></td>
				</tr>
				<?php foreach ($all_dims as $i => $dim){
						// echo recherche_libelle($ID_ZONE,$_SESSION['langue'],'DICO_ZONE');
						( isset($_POST['IMPORT_DIM_'.$i]) && (trim($_POST['IMPORT_DIM_'.$i]) <> '') ) ? ($checked = ' CHECKED') : ($checked = '') ;
				?>
					<tr> 
							<td><INPUT style="width:100%" type='text' name="<?php echo'DIM_'.$i;?>" value="<?php echo $dim['LIBELLE_DIMENSION'];?>" readonly="1"></td>
							<td align="center"><INPUT type="checkbox" name="<?php echo'IMPORT_DIM_'.$i;?>"   value="<?php echo $dim['ID_DIMENSION'] ?>" <?php echo $checked; ?>></td>
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
			<input type="hidden" name="nb_dims" value="<?php echo count($all_dims); ?>" />
		<?php }?>		
</table>
</FORM>

