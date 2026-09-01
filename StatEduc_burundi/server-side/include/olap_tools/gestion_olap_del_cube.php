<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php lit_libelles_page('/gestion_olap_del_cube.php');
function get_cubes(){
		$dossier_cubes = $GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/cubes/';
		$tab_liste_OLAPS = array();
		if (is_dir($dossier_cubes)){
			if ($dh = opendir($dossier_cubes)){
				$i = 0;
				while (($file = readdir($dh)) !== false){
				   $nom_file = $dossier_cubes.$file;
				   if(is_file($nom_file)){
						$f['ID'] = $i;
						$f['NAME'] = $file;
						$tab_liste_OLAPS[$i] = $f;
						$i++;
				   }
				}
				closedir($dh);
			}
		}
		//echo '<pre>';
		//print_r($this->tab_liste_OLAPS);
		$_SESSION['liste_local_cubes'] = $tab_liste_OLAPS;
}

function delete_cubes(){  
	$GLOBALS['post'] 			= 1;
	if(is_array($_SESSION['liste_local_cubes']) && count($_SESSION['liste_local_cubes'])>0){
		foreach($_SESSION['liste_local_cubes'] as $i => $cube){
				if( isset( $_POST['AppDel_'.$cube['ID']] ) and  ( $_POST['AppDel_'.$cube['ID']] =='1') ){
						unlink($GLOBALS['$SISED_URL'] . 'server-side/include/olap_tools/cubes/'.$cube['NAME']);
				}
		}
	}
}

if( $_POST['post'] =='1'){ // Actions 
	delete_cubes();
	get_cubes();
}// FIN Actions 
else{
	get_cubes();
	
}
//echo '<pre>';
//print_r($_SESSION['liste_local_cubes']);
		
?>
<body>
<br/>
<br/>
<div align="center">
<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
    <div  style="text-align:center; width: 400px; height: 250px; overflow: scroll;">
       
        <table>
		<caption><?php echo recherche_libelle_page('loc_cub_managmt');?></caption>
				<tr> 
				<td align=center><?php echo "<img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' width='16' height='16'/>";?></td>
				<td align=left><b><?php echo recherche_libelle_page('loc_cub');?></b></td>
				</tr>
				<?php if(is_array($_SESSION['liste_local_cubes']) && count($_SESSION['liste_local_cubes'])>0){
					foreach($_SESSION['liste_local_cubes'] as $i => $cube){
					?>
						<tr> 
						<td align=center nowrap><INPUT type="checkbox" <?php echo'name="AppDel_'.$cube['ID'].'"';?> value="1"/>
						</td>
						<td align=left nowrap="nowrap"><span style="color: #0000FF; font-weight:bold;"><?php echo $cube['NAME'];?></span></td>
						</tr>
					<?php }
				}
				?>
	  </table>
  </div> 
   <br/>
   <div  style="text-align:center; width: 400px; height: 40px;"> 
        <table style="width: 100px">
            <tr> 
                <td align='center' nowrap> <INPUT type='hidden' id='post' name='post' value='1'> 
                    <INPUT style="width:100%" type='submit' name='Input' <?php echo 'value="'.recherche_libelle_page('Suppr').'"';?>>
                </td>
            </tr>
        </table>
  </div>
  
</FORM>
</div>
</body>
