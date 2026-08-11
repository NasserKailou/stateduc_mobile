<script language="JavaScript" type="text/javascript">
	function recharger(tabm) {
		location.href   = 'synthese.php?val=pop_rpt_chp&id=<?php echo $_GET['id']?>&tabm='+tabm+'&id_rpt';
	}
</script>
<?php $requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['id'];
	//print $requete;
	$theme_name 			= $GLOBALS['conn_dico']->GetOne($requete);
	
	$requete                = ' SELECT DICO_RPT_TABLE_MERE.ID_RPT_TABLE_MERE, 
								DICO_RPT_TABLE_MERE.NOM_TABLE_MERE, DICO_RPT_TABLE_MERE.NOM_ALIAS
								FROM DICO_RPT_TABLE_MERE
								WHERE DICO_RPT_TABLE_MERE.ID = '.$_GET['id'].' ;';
	//print $requete;
	$all_tabm 				= $GLOBALS['conn_dico']->GetAll($requete);
	
	
	$requete                = ' SELECT DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP, DICO_OLAP_TYPE_CHAMP.NOM_TYPE_CHAMP
								FROM DICO_OLAP_TYPE_CHAMP;';
	//print $requete;
	$all_type_chp			= $GLOBALS['conn_dico']->GetAll($requete);
	
	
	$requete                = ' SELECT DICO_OLAP_MES_FONCTION.FONCTION FROM DICO_OLAP_MES_FONCTION;';
	//print $requete;
	$all_fonc				= $GLOBALS['conn_dico']->GetAll($requete);
	
	
	//$requete                = ' SELECT DICO_OLAP_MES_FORMAT.FORMAT FROM DICO_OLAP_MES_FORMAT;';
	//print $requete;
	//$all_format				= $GLOBALS['conn_dico']->GetAll($requete);
	$all_format = array(  
							array ('CODE' => 'int', 'LIBELLE' => recherche_libelle_page('int') ),
							array ('CODE' => 'double', 'LIBELLE' => recherche_libelle_page('double') ),
							array ('CODE' => 'text', 'LIBELLE' => recherche_libelle_page('text') )
						 );

	$requete                = ' SELECT DICO_DIMENSION_REPORT.ID_DIMENSION, DICO_DIMENSION_REPORT.DIM_LIBELLE_ENTETE AS LIBELLE_DIMENSION
								FROM DICO_DIMENSION_REPORT
								WHERE DICO_DIMENSION_REPORT.ID = '.$_GET['id'].';';
	//print $requete;
	$all_dim				= $GLOBALS['conn_dico']->GetAll($requete);
	
	



?>
<table align="center" border="1" width="450">
	<caption style="text-align:center;"><B><?php echo 'Report : '.$theme_name;?></B></caption>
    <INPUT type="hidden" name="ID" value="<?php echo $val['ID']; ?>">  
	<tr> 
		<td colspan="2" width="30%"><?php echo recherche_libelle_page('id_chp'); ?><br> 
      <INPUT style="width : 88%;" readonly="1" type="text" size="3" name="ID_CHAMP" value="<?php echo $val['ID_CHAMP']; ?>"></td>
      <td colspan="2" width="40%"><?php echo recherche_libelle_page('tabm'); ?><br>
          <select style="width : 100%;" name="ID_RPT_TABLE_MERE"  onChange="recharger(this.value);">
            <option value=''></option>
            <?php foreach ($all_tabm as $i => $tabm){
					echo "<option value='".$tabm['ID_RPT_TABLE_MERE']."'";
					if ($tabm['ID_RPT_TABLE_MERE'] == $GLOBALS['tabm']){
						echo " selected";
					}
					echo ">".$tabm['NOM_TABLE_MERE'].' ('.$tabm['NOM_ALIAS'].') '."</option>";
				}
				?>
          </select></td>
        <td colspan="2" width="30%"><?php echo recherche_libelle_page('ord_rpt'); ?><br> 
            <INPUT style="width : 88%;" type="text" size="3" name="ORDRE_RPT" value="<?php echo $val['ORDRE_RPT']; ?>"></td>
    </tr>
	<tr> 
      <td colspan="3" width="50%"><?php echo recherche_libelle_page('nom_chp'); ?> 
            <br> 
			
			<select name="NOM_CHAMP" id="NOM_CHAMP" style='width:220px'>";
			<?php $requete  = ' SELECT DICO_RPT_TABLE_MERE.NOM_TABLE_MERE
								FROM DICO_RPT_TABLE_MERE
								WHERE DICO_RPT_TABLE_MERE.ID_RPT_TABLE_MERE = '.$GLOBALS['tabm'];
				$nom_tabm = $GLOBALS['conn_dico']->GetOne($requete);
				$ColTabBD = $GLOBALS['conn']->MetaColumnNames($nom_tabm);
				echo "<option value=''></option>";
				foreach ($ColTabBD as $col){
					echo "<option value='".$col."'";
					if ($val['NOM_CHAMP'] == $col){
						echo " selected";
					}
					echo ">".$col."</option>";
				}	
			?>
			</select >
			<!--<INPUT style="width : 100%;" type="text" size="20" name="NOM_CHAMP" value="<?php echo $val['NOM_CHAMP']; ?>">-->
			</td>
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('typ_chp'); ?> 
            <br>
            <select style="width : 100%;" name="ID_TYPE_CHAMP" onchange="display_alias_field(this.value);">
              <option value=''></option>
              <?php foreach ($all_type_chp as $i => $type_chp){
					echo "<option value='".$type_chp['ID_TYPE_CHAMP']."'";
					if ($type_chp['ID_TYPE_CHAMP'] == $val['ID_TYPE_CHAMP']){
						echo " selected";
					}
					echo ">".$type_chp['NOM_TYPE_CHAMP']."</option>";
				}
				?>
            </select>
	  </td>
    </tr>
<tr> 
      <td colspan="3" width="50%"><?php echo recherche_libelle_page('alias'); ?> 
            <br>
			<input style="width : 95%; display:none;" type="text" size="20" id="ALIAS_2" name="" value="<?php echo $val['ALIAS']; ?>" />
			<select name="" style='100%; display:none;' id="ALIAS_1">
			<?php $requete  = ' SELECT LIBELLE_DICO_MESURE
								FROM DICO_MESURE_REPORT
								WHERE ID = '.$val['ID'].' 
								ORDER BY ORDRE';
				$list_mes = $GLOBALS['conn_dico']->GetAll($requete);
				echo "<option value=''></option>";
				foreach ($list_mes as $mes){
					echo "<option value='".$mes['LIBELLE_DICO_MESURE']."'";
					if ($mes['LIBELLE_DICO_MESURE'] == $val['ALIAS']){
						echo " selected";
					}
					echo ">".$mes['LIBELLE_DICO_MESURE']."</option>";
				}	
			?>
			</select >
      </td>
      <td colspan="3" width="50%"><?php echo recherche_libelle_page('format'); ?> 
            <br>
            <select style="width : 100%;" name="FORMAT">
              <option value=''></option>
              <?php foreach ($all_format as $i => $format){
                    print_r($format);
					echo "<option value='".$format['CODE']."'";
					if ($format['CODE'] == $val['FORMAT']){
						echo " selected";
					}
					echo ">".$format['LIBELLE']."</option>";
				}
				?>
            </select></td>
    </tr>	<tr> 
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('fonc'); ?><br>
          <select style="width : 100%;" name="FONCTION">
              <option value=''></option>
              <?php foreach ($all_fonc as $i => $fonc){
					echo "<option value='".$fonc['FONCTION']."'";
					if ($fonc['FONCTION'] == $val['FONCTION']){
						echo " selected";
					}
					echo ">".$fonc['FONCTION']."</option>";
				}
				?>
            </select>      </td>
        <td width="50%" colspan="3" rowspan="3" valign="top"><?php echo recherche_libelle_page('expr'); ?><br>
		<textarea name="EXPRESSION" style="width : 95%;" rows="6"><?php echo $val['EXPRESSION']; ?></textarea></td>
    </tr>
	<tr> 
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('id_dim'); ?><br>
          <select style="width : 100%;" name="ID_DIMENSION">
            <option value=''></option>
            <?php foreach ($all_dim as $i => $dim){
					echo "<option value='".$dim['ID_DIMENSION']."'";
					if ($dim['ID_DIMENSION'] == $val['ID_DIMENSION']){
						echo " selected";
					}
					echo ">".$dim['LIBELLE_DIMENSION']."</option>";
				}
				?>
          </select></td>
    </tr>
	<tr> 
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('lib_entete'); ?>
          <input style="width : 95%;" type="text" size="5" name="ENTETE_CHAMP" value="<?php echo $val['ENTETE_CHAMP']; ?>" />
      </td>
    </tr>	<tr> 
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('ord_dim'); ?>
          <input style="width : 95%;" type="text" size="3" name="ORDRE_DIM" value="<?php echo $val['ORDRE_DIM']; ?>" />
      </td>
    </tr>
</table>
<br>
<script language="JavaScript" type="text/javascript">
	function display_alias_field(fieldType) {
		if (fieldType == 1) {
			$("#ALIAS_1").attr('name', 'ALIAS');
			$("#ALIAS_2").attr('name', '');
			$("#ALIAS_2").hide();
			$("#ALIAS_1").show();
			$("#ALIAS_1").width("100%");
			$("#ALIAS_1").uniform();
		} else {
			$("#ALIAS_1").attr('name', '');
			$("#ALIAS_2").attr('name', 'ALIAS');
			$("#ALIAS_1").hide();
			$("#ALIAS_1").width("100%");
			$("#ALIAS_1").uniform();
			$("#ALIAS_2").show();
		}
	}
	$(document).ready(function () {
		display_alias_field(<?php echo $val['ID_TYPE_CHAMP']; ?>);
	});
</script>