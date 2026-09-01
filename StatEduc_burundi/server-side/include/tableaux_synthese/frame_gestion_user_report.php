<?php $requete	='	SELECT  DICO_TYPE_REPORT.*
					FROM    DICO_TYPE_REPORT';
	//echo $requete ;			
	$all_ID_TYPE_REPORT = $GLOBALS['conn_dico']->GetAll($requete);
	
	$requete	='	SELECT  DICO_TYPE_PRESENTATION.*
					FROM    DICO_TYPE_PRESENTATION';
	//echo $requete ;			
	$all_ID_TYPE_PRESENTATION = $GLOBALS['conn_dico']->GetAll($requete);
?>
<table align="center" border="1">
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('id_rpt'); ?></td>
        <td><INPUT style="width : 95%;" readonly="1" type="text" size="3" name="ID" value="<?php echo $val['ID']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('lib_rpt'); ?></td>
        <td><INPUT style="width : 95%;" type="text" size="50" name="LIBELLE_REPORT" value="<?php echo $val['LIBELLE_REPORT']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('rpt_classe'); ?></td>
        <td><INPUT style="width : 95%;" type="text" size="20" name="CLASSE" value="<?php if(isset($val['CLASSE']) && $val['CLASSE']<>"") echo $val['CLASSE']; else echo "QuickReport"; ?>"></td>
    </tr>
    <!--
	<tr> 
        <td nowrap><?php echo recherche_libelle_page('act_rpt'); ?></td>
        <td><INPUT style="width : 100%;" type="text" size="30" name="ACTION_REPORT" value="<?php echo $val['ACTION_REPORT']; ?>"></td>
    </tr>
    
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('dft_cnx'); ?></td>
        <td><INPUT style="width : 100%;" type="text" size="30" name="DEFAULT_CONNEXION" value="<?php echo $val['DEFAULT_CONNEXION']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('autre_cnx'); ?></td>
        <td><INPUT style="width : 100%;" type="text" size="30" name="AUTRE_CONNEXION" value="<?php echo $val['AUTRE_CONNEXION']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('sql_file'); ?></td>
        <td><INPUT style="width : 100%;" type="text" size="50" name="SQL_LINK_FILE" value="<?php echo $val['SQL_LINK_FILE']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('rpt_file'); ?></td>
        <td><INPUT style="width : 100%;" type="text" size="50" name="RPT_ASSOCIATE_FILE" value="<?php echo $val['RPT_ASSOCIATE_FILE']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('type_pres'); ?></td>
        <td><select style="width : 100%;" name="ID_TYPE_PRESENTATION">
							<option value=''></option>
							<?php foreach ($all_ID_TYPE_PRESENTATION as $i => $tab){
												echo "<option value='".trim($tab['ID_TYPE_PRESENTATION'])."'";
												if (trim($tab['ID_TYPE_PRESENTATION']) == trim($val['ID_TYPE_PRESENTATION'])){
														echo " selected";
												}
												echo ">".$tab['LIBELLE_TYPE_PRESENTATION']."</option>";
										}
								
								?>
					</select></td>
    </tr>
	-->
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('type_rpt'); ?></td>
        <td><select style="width : 95%;" name="ID_TYPE_REPORT">
							<option value=''></option>
							<?php foreach ($all_ID_TYPE_REPORT as $i => $tab){
												echo "<option value='".trim($tab['ID_TYPE_REPORT'])."'";
												if (trim($tab['ID_TYPE_REPORT']) == trim($val['ID_TYPE_REPORT'])){
														echo " selected";
												}
												echo ">".$tab['LIBELLE_TYPE_REPORT']."</option>";
										}
								
								?>
					</select></td>
    </tr>
	<tr> 
        <td nowrap><?php echo recherche_libelle_page('sql_rpt'); ?></td>
        <td><textarea name="SQL_REPORT" style="width:400px;" rows="5"><?php echo $val['SQL_REPORT']; ?></textarea></td>
    </tr>
</table>
<br>
