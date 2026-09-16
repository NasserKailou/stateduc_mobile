<?php $requete	='	SELECT  DICO_TYPE_REPORT.*
					FROM    DICO_TYPE_REPORT
					WHERE DICO_TYPE_REPORT.ID_TYPE_REPORT = 2';
	//echo $requete ;			
	$all_ID_TYPE_REPORT = $GLOBALS['conn_dico']->GetAll($requete);
	
	$requete	='	SELECT  DICO_TYPE_PRESENTATION.*
					FROM    DICO_TYPE_PRESENTATION';
	//echo $requete ;			
	$all_ID_TYPE_PRESENTATION = $GLOBALS['conn_dico']->GetAll($requete);
?>
<br>
<table align="center" border="1">
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('id_rpt'); ?></td>
        <td><INPUT style="width : 350;" readonly="1" type="text" size="3" name="ID" value="<?php echo $val['ID']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('lib_rpt'); ?></td>
        <td><INPUT style="width : 350;" type="text" size="50" name="LIBELLE_REPORT" value="<?php echo $val['LIBELLE_REPORT']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('rpt_classe'); ?></td>
        <td><INPUT style="width : 350;" type="text" size="20" name="CLASSE" value="<?php echo $val['CLASSE']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('act_rpt'); ?></td>
        <td><INPUT style="width : 350;" type="text" size="30" name="ACTION_REPORT" value="<?php echo $val['ACTION_REPORT']; ?>"></td>
    </tr>
    
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('dft_cnx'); ?></td>
        <td><INPUT style="width : 350;" type="text" size="30" name="DEFAULT_CONNEXION" value="<?php echo $val['DEFAULT_CONNEXION']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('autre_cnx'); ?></td>
        <td><INPUT style="width : 350;" type="text" size="30" name="AUTRE_CONNEXION" value="<?php echo $val['AUTRE_CONNEXION']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('sql_file'); ?></td>
        <td><INPUT style="width : 350;" type="text" size="50" name="SQL_LINK_FILE" value="<?php echo $val['SQL_LINK_FILE']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('rpt_file'); ?></td>
        <td><INPUT style="width : 350;" type="text" size="50" name="RADICAL_RPT_FILE" value="<?php echo $val['RADICAL_RPT_FILE']; ?>"></td>
    </tr>
    <tr> 
        <td nowrap><?php echo recherche_libelle_page('type_rpt'); ?></td>
        <td><select style="width : 350;" name="ID_TYPE_REPORT">
							
							<?php foreach ($all_ID_TYPE_REPORT as $i => $tab){
												echo "<option value='".trim($tab['ID_TYPE_REPORT'])."'";
												if (trim($tab['ID_TYPE_REPORT']) == trim($val['ID_TYPE_REPORT'])){
														echo " selected";
												}
												echo ">".$tab['LIBELLE_TYPE_REPORT']."</option>";
										}
								
								?>
					</select>
					
					</td>
    </tr>
	<tr> 
        <td nowrap><?php echo recherche_libelle_page('sql_rpt'); ?></td>
        <td><textarea name="SQL_REPORT" style="width : 350;" rows="3"><?php echo $val['SQL_REPORT']; ?></textarea></td>
    </tr>
</table>
<br>
