<script language="JavaScript" type="text/javascript">
	function OpenPopupCubDoc(val_chp){	
		OpenPopupOlapDoc(<?php echo $val['ID_OLAP']; ?>,val_chp);
	}
	
	function OpenPopupCubImg(val_chp){	
		OpenPopupOlapImg(<?php echo $val['ID_OLAP']; ?>,val_chp);
	}
	
	function OpenPopupCub(val_chp){	
		OpenPopupOLAP(<?php echo $val['ID_OLAP']; ?>, 'OlapFile')
	}
	
	function raz_sgbd_options(){
		eval('document.Formulaire.SGBD.selectedIndex=0');
		eval('document.Formulaire.DEFAUT_CONNEXION.value=""');
	}
	
	function disable_sgbd(chp){
		var strbool=false;
		var val_tr_sgbd="block";
		var ch_eval = 'document.Formulaire.'+chp+'.checked';
		if(eval(ch_eval)==true)
		{
			strbool=true;
			raz_sgbd_options();
			val_tr_sgbd="none";
		}		
		document.getElementById('SGBD').disabled = strbool;
		document.getElementById('DEFAUT_CONNEXION').disabled = strbool;
		document.getElementById('LIGNE_SGBD').style.display=val_tr_sgbd;	
	}
</script>

<?php $requete                = ' SELECT SGBD FROM DICO_OLAP_TYPE_SGBD;';
	$all_type_SGBD			= $GLOBALS['conn_dico']->GetAll($requete);	
	
?>
<?php /*if($this->total_enr == 0)
	{
		echo '<table align="center" width="500" >
				<tr>
					<td height="35">	   
				<input style="width:50%;" onClick="OpenPopupImportCube(';
		echo $this->donnees[$this->i_enr]['ID_OLAP'];
		echo ');" type="button"';
		echo ' value="'.recherche_libelle_page('BtnOlapImportCube').'">'; 
		echo '</td>
				</tr>
			  </table>';
	}*/
?>
<br>
<table align="center" border="1" width="500">
	<?php if( isset($val['THEME_NAME']) and (trim($val['THEME_NAME'])<>'') ){ ?>
				<caption style="text-align:center"><B><?php echo recherche_libelle_page('TypeCube').' '.$val['THEME_NAME']; ?></B></caption>
	<?php } ?>

    <tr> 
		<td colspan="2" width="30%"><?php echo recherche_libelle_page('id_olap'); ?><br> 
      <INPUT style="width : 50%;" readonly="1" type="text" size="3" name="ID_OLAP" value="<?php echo $val['ID_OLAP']; ?>"></td>
	  <td colspan="1" width="20%"><?php echo recherche_libelle_page('ordre_olap'); ?><br> 
      <INPUT style="width : 70%;" type="text" size="3" name="ORDRE_OLAP" value="<?php echo $val['ORDRE_OLAP']; ?>"></td>
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('thm_name'); ?><br> 
            <INPUT style="width : 100%;" type="text" size="3" name="THEME_NAME" value="<?php echo $val['THEME_NAME']; ?>"></td>
    </tr>
	<tr> 
      <td colspan="6" width="100%"><?php echo recherche_libelle_page('curr_cnx'); ?> 
            <INPUT name="USE_CURRENT_DB_CNX" type="checkbox" value="1" <?php if($val['USE_CURRENT_DB_CNX']=='0') echo ''; else echo' checked';?> onclick="disable_sgbd(this.name)"></td>
    </tr>
	<tr id="LIGNE_SGBD"> 
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('sgbd_olap'); ?> 
            <br><select style="width : 100%;" id="SGBD" name="SGBD">
                <option value=''></option>
                <?php foreach ($all_type_SGBD as $i => $type_SGBD){
					echo "<option value='".$type_SGBD['SGBD']."'";
					if ($type_SGBD['SGBD'] == $val['SGBD']){
						echo " selected";
					}
					echo ">".$type_SGBD['SGBD']."</option>";
				}
				?>
            </select></td>
		 <td colspan="3" width="50%"><?php echo recherche_libelle_page('dft_cnx'); ?><br> 
            <INPUT style="width : 100%;" type="text" size="3" id="DEFAUT_CONNEXION" name="DEFAUT_CONNEXION" value="<?php echo $val['DEFAUT_CONNEXION']; ?>"></td>
    </tr>	
	<tr>         
      <td colspan="3" width="50%"><?php echo recherche_libelle_page('doc_file'); ?><br>
        <input style="width : 90%;"  type="text" name="ASSOCIATE_DESC_FILE" value="<?php echo $val['ASSOCIATE_DESC_FILE']; ?>" ondblclick="OpenPopupCubDoc(this.value)" />
		<input type="button" value="..." onclick="OpenPopupImportCubDoc()" />
		</td>		
		<td colspan="3" width="50%"><?php echo recherche_libelle_page('img_file'); ?><br>
          <input style="width : 90%;" type="text" name="ASSOCIATE_IMAGE_FILE" value="<?php echo $val['ASSOCIATE_IMAGE_FILE']; ?>" ondblclick="OpenPopupCubImg(this.value)" />
		  <input type="button" value="..." onclick="OpenPopupImportCubImg()" />
	  </td>
    </tr>
	<tr> 
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('olap_file'); ?><br> 
            <input style="width : 90%;" type="text" name="ASSOCIATE_OLAP_FILE" value="<?php echo $val['ASSOCIATE_OLAP_FILE']; ?>"  ondblclick="OpenPopupCub(this.value)" />
			<input type="button" value="..." onclick="OpenPopupImportUnCub()" />
	  	</td>
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('sql_file'); ?><br> 
            <input style="width : 90%;" type="text" name="SQL_LINK_FILE_OLAP" value="<?php echo $val['SQL_LINK_FILE_OLAP']; ?>">
			<input type="button" value="..." onclick="OpenPopupImportCubSQL()" />
	  	</td>
     		<input type="hidden" name="TABLE_FAITS" value="<?php $val['TABLE_FAITS']='FACT_TABLE_'.$val['ID_OLAP'];echo $val['TABLE_FAITS']; ?>" readonly>
    </tr> 
    
<tr> 
        <td colspan="6" nowrap><?php echo recherche_libelle_page('sql_olap'); ?><br>
		<textarea name="SQL_OLAP" style="width : 100%;" rows="4"><?php echo $val['SQL_OLAP']; ?></textarea>		</td>
</tr>    </table>
<br>
<script language="javascript" type="text/javascript">
	disable_sgbd(document.Formulaire.USE_CURRENT_DB_CNX.name);
</script>
<?php if( $this->action == 'Del')
	{
		$req='DELETE FROM DICO_OLAP_DIMENSION WHERE ID_OLAP = '.$_GET['id_olap'].' ;';
		$GLOBALS['conn_dico']->Execute($req);
		
		$req='DELETE FROM DICO_OLAP_CHAMP WHERE ID_OLAP = '.$_GET['id_olap'].' ;';
		$GLOBALS['conn_dico']->Execute($req);
		
		$req='DELETE FROM DICO_OLAP_TABLE_MERE WHERE ID_OLAP = '.$_GET['id_olap'].' ;';
		$GLOBALS['conn_dico']->Execute($req);
		
		$req='DELETE FROM DICO_OLAP_JOINTURES WHERE ID_OLAP = '.$_GET['id_olap'].' ;';
		$GLOBALS['conn_dico']->Execute($req);
		
		$req='DELETE FROM DICO_OLAP_CRITERE WHERE ID_OLAP = '.$_GET['id_olap'].' ;';
		$GLOBALS['conn_dico']->Execute($req);            
		
        $req='DELETE FROM DICO_OLAP_QUERY WHERE ID_OLAP = '.$_GET['id_olap'].' ;';
		$GLOBALS['conn_dico']->Execute($req);      
        
		$nom_theme = $_POST['ASSOCIATE_OLAP_FILE'];
		$nom_fichier="";
		if(preg_match("/(\.cub)/i", $nom_theme))
			$nom_fichier=$nom_theme;			
		else
		$nom_fichier=$nom_theme.'.cub'	;		
		if(is_file($GLOBALS['SISED_PATH_INC'].'olap_tools/cubes/'.$nom_fichier))
			unlink($GLOBALS['SISED_PATH_INC'].'olap_tools/cubes/'.$nom_fichier);
			
		$nom_doc = $_POST['ASSOCIATE_DESC_FILE'];
		$nom_fichier="";
		$nom_fichier=$nom_doc;			
		if(is_file($GLOBALS['SISED_PATH_INC'].'olap_tools/docs/'.$nom_fichier))
			unlink($GLOBALS['SISED_PATH_INC'].'olap_tools/docs/'.$nom_fichier);
		
		$nom_img = $_POST['ASSOCIATE_IMAGE_FILE'];
		$nom_fichier="";
		$nom_fichier=$nom_img;			
		if(is_file($GLOBALS['SISED_PATH_INC'].'olap_tools/images/'.$nom_fichier))
			unlink($GLOBALS['SISED_PATH_INC'].'olap_tools/images/'.$nom_fichier);	
		
	}
?>
