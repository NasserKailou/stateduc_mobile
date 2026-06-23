<script language="JavaScript" type="text/javascript">
	function recharger(id_syst) {
		location.href   = 'annuaire.php?val=pop_rpt_sys&id_rpt=<?php echo $_GET['id_rpt']?>&id_syst='+id_syst;
	}
</script>
<?php //$nom_rpt = $GLOBALS['conn_dico']->GetOne($requete);
	
		
	$all_page_ori = array(  
							array ('CODE' => 'L', 'LIBELLE' => recherche_libelle_page('paysage') ),
							array ('CODE' => 'P', 'LIBELLE' => recherche_libelle_page('portrait') )
						 );
						 
	$all_ori_mes = array(  
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('colonne') ),
							array ('CODE' => '2', 'LIBELLE' => recherche_libelle_page('ligne') )
						 );
						 
	$all_align_mes = array(  
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('gauche') ),
							array ('CODE' => '2', 'LIBELLE' => recherche_libelle_page('centre') ),
							array ('CODE' => '3', 'LIBELLE' => recherche_libelle_page('droite') )
						 );
						 
	$this->btn_new = false;
	if( isset($val['ID_SYSTEME']) && (trim($val['ID_SYSTEME'])<> '' ) ){
		$GLOBALS['id_syst'] = $val['ID_SYSTEME'] ;
	}

?>

<table align="center" border="1" width="400">
    <tr> 
        <td   nowrap align="center" colspan="2"><?php echo recherche_libelle_page('nom_rpt'); ?> 
            : <b><?php echo $GLOBALS['nom_rpt']; ?></b></td>
    </tr>
    <tr> 
      <td nowrap width="50%" ><?php echo recherche_libelle_page('id_rpt'); ?><br />
        <input style="width : 100%;" readonly="1" type="text" size="3" name="ID" value="<?php echo $val['ID']; ?>" />
      <br> </td>
        <td nowrap width="50%" ><b><?php echo recherche_libelle_page('id_syst'); ?></b><br />
          <select  style="width : 100%;" name="ID_SYSTEME" onChange="recharger(this.value);">
            <option value=''></option>
            <?php foreach ($GLOBALS['all_systemes'] as $i => $systemes){
							echo "<option value='".$systemes['id_systeme']."'";
							if ($systemes['id_systeme'] == $GLOBALS['id_syst']){
								echo " selected";
							}
							echo ">".$systemes['libelle_systeme']."</option>";
						}
				?>
          </select></td>
    </tr>
<tr> 
        <td colspan="2"><?php echo recherche_libelle_page('tit_princ'); ?> 
            <br> <INPUT style="width : 100%;" type="text" size="30" name="TITRE_PRINCIPAL" value="<?php echo $val['TITRE_PRINCIPAL']; ?>"></td>
    </tr>    <tr> 
        <td ><?php echo recherche_libelle_page('tit_second'); ?> 
            <br> <INPUT style="width : 100%;" type="text" size="30" name="TITRE_SECOND" value="<?php echo $val['TITRE_SECOND']; ?>"></td>
        <td ><?php echo recherche_libelle_page('tit_tert'); ?> 
            <br> <INPUT style="width : 100%;" type="text" size="30" name="TITRE_TERTIAIRE" value="<?php echo $val['TITRE_TERTIAIRE']; ?>">			</td>
    </tr>
    <tr> 
        <td ><?php echo recherche_libelle_page('page_ori'); ?><br> 
			<select style="width : 100%;" name="PAGE_ORIENTATION">
                <option value=''></option>
                <?php foreach ($all_page_ori as $i => $page_ori){
					echo "<option value='".$page_ori['CODE']."'";
					if ($page_ori['CODE'] == $val['PAGE_ORIENTATION']){
						echo " selected";
					}
					echo ">".$page_ori['LIBELLE']."</option>";
				}
				?>
            </select>			</td>
        <td ><?php echo recherche_libelle_page('ordre_rpt'); ?> 
            <br>
            <input style="width : 100%;" type="text" size="30" name="ORDRE_REPORT" value="<?php echo $val['ORDRE_REPORT']; ?>" /></td>
    </tr>
    <tr> 
        <td ><?php echo recherche_libelle_page('activer'); ?>
          
          <br><input name="ACTIVER_REPORT" type="checkbox" value="1" <?php if($val['ACTIVER_REPORT']=='1') echo' checked';?> /></td>
        <td ><?php echo recherche_libelle_page('num_page'); ?> 
            <br> <INPUT style="width : 100%;" type="text" size="30" name="NUMERO_PAGE" value="<?php echo $val['NUMERO_PAGE']; ?>"></td>
    </tr>
<tr> 
        <td ><?php echo recherche_libelle_page('rpt_file'); ?> 
            <br> <INPUT style="width : 100%;" type="text" size="30" name="RPT_FILE_NAME" value="<?php echo $val['RPT_FILE_NAME']; ?>"></td>
        <td ><?php echo recherche_libelle_page('temp_table'); ?> 
            <br> <INPUT style="width : 100%;" type="text" size="30" name="TEMP_TABLE_NAME" value="<?php echo $val['TEMP_TABLE_NAME']; ?>">			</td>
    </tr>    </table>
<br>
