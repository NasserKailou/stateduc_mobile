<script language="JavaScript" type="text/javascript">
	function recharger(tabm) {
		location.href   = 'olap.php?val=pop_olap_chp&id_olap=<?php echo $_GET['id_olap']?>&tabm='+tabm;
	}
</script>
<?php $requete                = ' SELECT DICO_OLAP.THEME_NAME
								FROM DICO_OLAP
								WHERE DICO_OLAP.ID_OLAP ='.$_GET['id_olap'];
	//print $requete;
	$theme_name 			= $GLOBALS['conn_dico']->GetOne($requete);
	
	$requete                = ' SELECT DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE, 
								DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS
								FROM DICO_OLAP_TABLE_MERE
								WHERE DICO_OLAP_TABLE_MERE.ID_OLAP = '.$_GET['id_olap'].' ;';
	//print $requete;
	$all_tabm 				= $GLOBALS['conn_dico']->GetAll($requete);
	
	
	$requete                = ' SELECT DICO_OLAP_TYPE_CHAMP.ID_TYPE_CHAMP, DICO_OLAP_TYPE_CHAMP.NOM_TYPE_CHAMP
								FROM DICO_OLAP_TYPE_CHAMP;';
	//print $requete;
	$all_type_chp			= $GLOBALS['conn_dico']->GetAll($requete);
	
	
	$requete                = ' SELECT DICO_OLAP_MES_FONCTION.FONCTION FROM DICO_OLAP_MES_FONCTION;';
	//print $requete;
	$all_fonc				= $GLOBALS['conn_dico']->GetAll($requete);
	
	
	$requete                = ' SELECT DICO_OLAP_MES_FORMAT.FORMAT FROM DICO_OLAP_MES_FORMAT;';
	//print $requete;
	$all_format				= $GLOBALS['conn_dico']->GetAll($requete);
	

	$requete                = ' SELECT DICO_OLAP_DIMENSION.ID_DIMENSION, DICO_OLAP_DIMENSION.LIBELLE_DIMENSION
								FROM DICO_OLAP_DIMENSION
								WHERE DICO_OLAP_DIMENSION.ID_OLAP = '.$_GET['id_olap'].';';
	//print $requete;
	$all_dim				= $GLOBALS['conn_dico']->GetAll($requete);
	
	



?>
<table align="center" border="1" width="450">
	<caption style="text-align:center;"><B><?php echo 'Cube : '.$theme_name;?></B></caption>
    <INPUT type="hidden" name="ID_OLAP" value="<?php echo $val['ID_OLAP']; ?>">  
	<tr> 
		<td colspan="2" width="30%"><?php echo recherche_libelle_page('id_chp'); ?><br> 
      <INPUT style="width : 100%;" readonly="1" type="text" size="3" name="ID_CHAMP" value="<?php echo $val['ID_CHAMP']; ?>"></td>
      <td colspan="2" width="40%"><?php echo recherche_libelle_page('tabm'); ?><br>
          <select style="width : 100%;" name="ID_OLAP_TABLE_MERE"  onChange="recharger(this.value);">
            <option value=''></option>
            <?php foreach ($all_tabm as $i => $tabm){
					echo "<option value='".$tabm['ID_OLAP_TABLE_MERE']."'";
					if ($tabm['ID_OLAP_TABLE_MERE'] == $GLOBALS['tabm']){
						echo " selected";
					}
					echo ">".$tabm['NOM_TABLE_MERE'].' ('.$tabm['NOM_ALIAS'].') '."</option>";
				}
				?>
          </select>
		</td>
        <td colspan="2" width="30%"><?php echo recherche_libelle_page('ord_olap'); ?><br> 
            <INPUT style="width : 100%;" type="text" size="3" name="ORDRE_OLAP" value="<?php echo $val['ORDRE_OLAP']; ?>"></td>
    </tr>
	<tr> 
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('nom_chp'); ?> 
            <br> 
			<select name="NOM_CHAMP" id="NOM_CHAMP" style='width:220px'>";
			<?php $requete  = ' SELECT DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE
								FROM DICO_OLAP_TABLE_MERE
								WHERE DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE = '.$GLOBALS['tabm'];
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
     	<td colspan="3" width="50%"><?php echo recherche_libelle_page('alias'); ?> 
            
			
			<br>
            <input style="width : 100%;" type="text" size="20" name="ALIAS" value="<?php echo $val['ALIAS']; ?>" /></td>
    </tr>
<tr> 
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('typ_chp'); ?> 
            <br>
            <select style="width : 100%;" name="ID_TYPE_CHAMP">
              <option value=''></option>
              <?php foreach ($all_type_chp as $i => $type_chp){
					echo "<option value='".$type_chp['ID_TYPE_CHAMP']."'";
					if ($type_chp['ID_TYPE_CHAMP'] == $val['ID_TYPE_CHAMP']){
						echo " selected";
					}
					echo ">".$type_chp['NOM_TYPE_CHAMP']."</option>";
				}
				?>
            </select></td>
	  
      <td colspan="3" width="50%"><?php echo recherche_libelle_page('format'); ?> 
            <br>
            <select style="width : 100%;" name="FORMAT">
              <option value=''></option>
              <?php foreach ($all_format as $i => $format){
					echo "<option value='".$format['FORMAT']."'";
					if ($format['FORMAT'] == $val['FORMAT']){
						echo " selected";
					}
					echo ">".$format['FORMAT']."</option>";
				}
				?>
            </select></td>
    </tr>	<tr> 
		<td colspan="3" width="50%"><?php echo recherche_libelle_page('all_level');?>
	  		<INPUT name="ALL_LEVEL" type="checkbox" value="1" <?php if($val['ALL_LEVEL']==0) echo ''; else echo' checked';?>>
	 	</td>
		<td width="50%" colspan="3" rowspan="4" valign="top"><?php echo recherche_libelle_page('expr'); ?><br>
			<textarea name="EXPRESSION" style="width : 100%;" rows="8"><?php echo $val['EXPRESSION']; ?></textarea>
		</td>
	</tr>
	<tr>	
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
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('ord_dim'); ?>
          <input style="width : 100%;" type="text" size="3" name="ORDRE_DIM" value="<?php echo $val['ORDRE_DIM']; ?>" />
      </td>
    </tr>
</table>
<br>
