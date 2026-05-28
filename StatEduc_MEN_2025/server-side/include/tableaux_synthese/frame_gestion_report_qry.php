<?php $requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['id_rpt'].' ORDER BY  DICO_REPORT.ORDRE_EXECUTION';
	//print $requete;
	$nom_rpt = $GLOBALS['conn_dico']->GetOne($requete);

	$all_type_req = array(  
							array ('CODE' => '0', 'LIBELLE' => recherche_libelle_page('donnees') ),
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('admin') )
						 );
?>
<table align="center" border="1" width="550">
    <tr> 
        <td colspan="2"   align="center"><?php echo recherche_libelle_page('nom_rpt'); ?> 
            : <b><?php echo $nom_rpt; ?></b></td>
    </tr>
    <tr> 
        <td width="12%"><?php echo recherche_libelle_page('id_rpt'); ?></td>
      <td width="88%"><INPUT style="width : 95%;" readonly="1" type="text" size="3" name="ID" value="<?php echo $val['ID']; ?>"></td>
    </tr>
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('id_qry'); ?></td>
      <td width="88%"><INPUT style="width : 95%;" readonly="1" type="text" size="3" name="ID_QUERY" value="<?php echo $val['ID_QUERY']; ?>"></td>
    </tr>
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('nom_qry'); ?></td>
      <td width="88%"><INPUT style="width : 95%;" type="text" size="3" name="NOM_QUERY" value="<?php echo $val['NOM_QUERY']; ?>"></td>
    </tr>	
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('type_req'); ?></td>
        <td width="88%">
		<select style="width : 95%;" name="TYPE_QUERY">
                <option value=''></option>
                <?php foreach ($all_type_req as $i => $type_req){
					echo "<option value='".$type_req['CODE']."'";
					if ($type_req['CODE'] == $val['TYPE_QUERY']){
						echo " selected";
					}
					echo ">".$type_req['LIBELLE']."</option>";
				}
				?>
            </select>	
	  </td>
    </tr>
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('ord_exec'); ?></td>
      <td width="88%"><INPUT style="width : 95%;" type="text" size="20" name="ORDRE_EXECUTION" value="<?php echo $val['ORDRE_EXECUTION']; ?>"></td>
    </tr>
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('comm'); ?></td>
        <td width="88%">
		<INPUT style="width : 95%;" type="text" size="20" name="COMMENTAIRE" value="<?php echo $val['COMMENTAIRE']; ?>">		
	  </td>
    </tr>    
	    
    <tr> 
        <td width="12%"><?php echo recherche_libelle_page('query'); ?></td>
        <td width="88%">
		<textarea name="SQL_QUERY" style="width : 95%;" rows="8"><?php echo $val['SQL_QUERY']; ?></textarea>
	  </td>
    </tr>
    
</table>
<br>
