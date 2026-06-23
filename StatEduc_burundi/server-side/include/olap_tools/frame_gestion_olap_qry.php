<?php $requete                = ' SELECT DICO_OLAP.THEME_NAME
								FROM DICO_OLAP
								WHERE DICO_OLAP.ID_OLAP ='.$_GET['id_olap'];
	//print $requete;
	$theme_name = $GLOBALS['conn_dico']->GetOne($requete);

	$all_type_req = array(  
							array ('CODE' => '0', 'LIBELLE' => recherche_libelle_page('create_qry') ),
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('drop_qry') )
						 );
?>
<table align="center" border="1" width="550">
	<caption style="text-align:center;"><B><?php echo 'Cube : '.$theme_name;?></B></caption>
    <INPUT type="hidden" name="ID_OLAP" value="<?php echo $val['ID_OLAP']; ?>">
    <tr> 
        <td width="20%"><?php echo recherche_libelle_page('id_qry'); ?></td>
      <td width="80%"><INPUT style="width : 100%;" readonly="1" type="text" size="3" name="ID_QUERY_OLAP" value="<?php echo $val['ID_QUERY_OLAP']; ?>"></td>
    </tr>
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('nom_qry'); ?></td>
      <td width="88%"><INPUT style="width : 100%;" type="text" size="3" name="NOM_QUERY_OLAP" value="<?php echo $val['NOM_QUERY_OLAP']; ?>"></td>
    </tr>	
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('type_req'); ?></td>
        <td width="88%">
		<select style="width : 100%;" name="TYPE_QUERY_OLAP">
                <?php foreach ($all_type_req as $i => $type_req){
					echo "<option value='".$type_req['CODE']."'";
					if ($type_req['CODE'] == $val['TYPE_QUERY_OLAP']){
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
      <td width="88%"><INPUT style="width : 100%;" type="text" size="20" name="ORDRE_EXECUTION_OLAP" value="<?php echo $val['ORDRE_EXECUTION_OLAP']; ?>"></td>
    </tr>
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('comm'); ?></td>
        <td width="88%">
		<INPUT style="width : 100%;" type="text" size="20" name="COMMENTAIRE_OLAP" value="<?php echo $val['COMMENTAIRE_OLAP']; ?>">		
	  </td>
    </tr>    
	    
    <tr> 
        <td width="12%"><?php echo recherche_libelle_page('query'); ?></td>
        <td width="88%">
		<textarea name="SQL_QUERY_OLAP" style="width : 100%;" rows="8"><?php echo $val['SQL_QUERY_OLAP']; ?></textarea>
	  </td>
    </tr>
    
</table>
<br>
