<?php lit_libelles_page('/choix_secteur.php');
?>
<script type="text/Javascript">

	function chg_session_sys(sys) {
		location.href= '?val=<?php echo $_GET['val'];?>&secteur='+sys;
	}
	
</script>
<br><br><br>
<form action="">
<table align="center" width="40%">
	<caption><?php echo recherche_libelle_page('ges_cur_sys');?></caption>
<tr>
<td>
<br>
<TABLE align="center" width="100%">
                    <TR> 
                        <td nowrap align="right"> <?php echo recherche_libelle_page('curr_sect');?> : </td>
                        <td> 
						<span style="font-weight:bold;">
						<?php echo get_libelle_from_array($_SESSION['tab_secteur'], $_SESSION['secteur'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE');?>
                        </span></td>
                    </TR>
                    <TR> 
                        <td nowrap align="right"> 
							<?php echo recherche_libelle_page('chg_sect');?>  :
                         </td>
                        <td> 
                            <?php $nom_combo_systeme      = 'LIBELLE';
			$nom_code_systeme       =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
	
			if ($_GET['secteur']) {
	
				$code_secteur        = $_GET['secteur'];            
				$_SESSION['secteur'] = $_GET['secteur'];
	
			} else if ($_SESSION['secteur']) {
	
				$code_secteur        = $_SESSION['secteur'];
				$_GET['secteur']     = $_SESSION['secteur'];
	
			}
			
			$fonction = 'chg_session_sys('.$nom_combo_systeme .'.value)';
			//echo create_combo($_SESSION['tab_secteur'], $nom_combo_systeme, $nom_code_systeme, $code_secteur,  $fonction);
			echo create_combo($_SESSION['tab_secteur'], $nom_combo_systeme, $nom_code_systeme, $code_secteur,  '');
		?>
                        </td>
                    </TR>
                </TABLE>
<br>
</td>
</tr>
<tr><td colspan="2" align="center">
<INPUT type="button" style="width:100%" value=" OK ..." onClick="<?php echo 'chg_session_sys('.$nom_combo_systeme .'.value)';?>">
</td></tr>
</table>
</form>
