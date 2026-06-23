<script language="JavaScript" type="text/javascript">
	function recharger(id_mes) {
		location.href   = 'synthese.php?val=OpenPopupRptMes&id_rpt=<?php echo $_GET['id_rpt']?>&id_mes='+id_mes;
	}
</script>

<?php $requete                = ' SELECT DICO_OLAP.THEME_NAME
								FROM DICO_OLAP
								WHERE DICO_OLAP.ID_OLAP ='.$_GET['id_olap'];
	//print $requete;
	$theme_name = $GLOBALS['conn_dico']->GetOne($requete);

	$requete                = '	SELECT * FROM DICO_MESURE ORDER BY LIBELLE_MESURE';
	//print $requete;
	$all_mesures 			= $GLOBALS['conn_dico']->GetAll($requete);


?>
<table align="center" border="1" width="400">
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_olap'); ?></td>
      <td width="60%"><INPUT style="width : 30;" readonly="1" type="text" size="3" name="ID_OLAP" value="<?php echo $val['ID_OLAP']; ?>">
        <b>&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp;<?php echo $theme_name; ?></b></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_mes'); ?></td>
        <td width="60%"> <select style="width : 100%;" name="ID_MESURE">
                <option value=''></option>
                <?php foreach ($all_mesures as $i => $mes){
					echo "<option value='".$mes['ID_MESURE']."'";
					if ($mes['ID_MESURE'] == $val['ID_MESURE']){
						echo " selected";
					}
					echo ">".$mes['LIBELLE_MESURE']."</option>";
				}
				?>
            </select></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('ordre'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="ORDRE_OLAP_MESURE" value="<?php echo $val['ORDRE_OLAP_MESURE']; ?>"></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('lib_mes'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="LIBELLE_OLAP_MESURE" value="<?php echo $val['LIBELLE_OLAP_MESURE']; ?>"></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('ent_mes'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="ENTETE_OLAP_MESURE" value="<?php echo $val['ENTETE_OLAP_MESURE']; ?>"></td>
    </tr>
</table>
<br>
