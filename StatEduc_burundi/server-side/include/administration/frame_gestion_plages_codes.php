<script type='text/javascript'>
	var xhr = null; 

	function getXhr(){
		if(window.XMLHttpRequest) // Firefox et autres
		   xhr = new XMLHttpRequest(); 
		else if(window.ActiveXObject){ // Internet Explorer 
		   try {
					xhr = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
				}
		}
		else { // XMLHttpRequest non supporté par le navigateur 
		   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
		   xhr = false; 
		} 
	}
	
	/**
	* Méthode qui sera appelée sur le click du bouton
	*/
	function load_champs(){
		getXhr();
		// On défini ce qu'on va faire quand on aura la réponse
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				leselect = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('NOM_CHAMP').innerHTML = leselect;
				jQuery('#NOM_CHAMP select').attr('style', 'width:280px;');
				jQuery('#NOM_CHAMP select').uniform();
			}
		}
		sel = document.getElementById('NOM_TABLE');
		table = sel.options[sel.selectedIndex].value;
		url="administration.php?val=charger_chps&table="+table+"&chp_fils=<?php echo $val['NOM_CHAMP']; ?>&plage_codes=1";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);		
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
	}
</script>
<br>
<table align="center">
    <tr> 
        <td width="100px"><?php echo recherche_libelle_page('type_regroup'); ?></td>
        <td>
		<select name="CODE_TYPE_REGROUP" id="CODE_TYPE_REGROUP" style="width: 280px">
		<?php 
			$req_type_regroup = "SELECT * FROM ".$GLOBALS['PARAM']['TYPE_REGROUPEMENT'];
			$res_type_regroup	=	$GLOBALS['conn']->GetAll($req_type_regroup);
			echo "<option value='0'></option>";
			foreach ($res_type_regroup as $type_regroup){
				echo "<option value='".$type_regroup[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."'";
				if ($val['CODE_TYPE_REGROUP'] == $type_regroup[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]){
					echo " selected";
				}
				echo ">".$type_regroup[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."</option>";
			}
		?>																				
		</select >
		</td>
    </tr>
	<?php 
	//if($val['NOM_CHAMP']==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
		$req_nb_chp_rattach_assoc = "SELECT COUNT(*) FROM DICO_PLAGE_CODES_ASSOC WHERE CODE_TYPE_REGROUP = ".$val['CODE_TYPE_REGROUP']." AND NOM_CHAMP = '".$val['NOM_CHAMP']."'";
		$nb_chp_rattach_assoc = $GLOBALS['conn_dico']->GetOne($req_nb_chp_rattach_assoc);
	?>
	<tr> 
        <!-- <td><?php echo recherche_libelle_page('RattachAdd'); ?></td> -->
        <td colspan="2" align="center"><?php echo '<input name="button" type="button" onclick="OpenPopupPlageCodesAssoc(CODE_TYPE_REGROUP.value, NOM_CHAMP.value)" value="'.recherche_libelle_page('CodesRattachAdd').' ('.$nb_chp_rattach_assoc.')" style="width:380px"/>';?></td>
	</tr>
	<?php 
	//}
	?>
    <tr> 
        <td><?php echo recherche_libelle_page('nom_table'); ?></td>
        <td>
		<select name="NOM_TABLE" id="NOM_TABLE" onchange="load_champs()" style="width: 280px">
		<?php $TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
			$tables = array();
			$deb_nomenc = $GLOBALS['PARAM']['TYPE'].'_';
			$deb_fact_tab = 'FACT_TABLE_';
			$fin_nomenc_system = '_'.$GLOBALS['PARAM']['SYSTEME'];
			foreach($TabBD as $tab)
			{
				if(!preg_match("/^($deb_fact_tab)/",strtoupper($tab)) && !preg_match("/^($deb_nomenc)/",strtoupper($tab)) && !preg_match("/($fin_nomenc_system)$/",strtoupper($tab)) && !preg_match("/^(DICO_)/",strtoupper($tab)) && !preg_match("/^(ADMIN_DROITS)/",strtoupper($tab)) && !preg_match("/^(ADMIN_GROUPES)/",strtoupper($tab)) && !preg_match("/^(ADMIN_USERS)/",strtoupper($tab)) && !preg_match("/^(PARAM_DEFAUT)/",strtoupper($tab)) && !preg_match("/^(SYSTEME)/",strtoupper($tab)))
				{
					$tables[] = $tab;
				}
			}
			$TabBD = $tables;
			sort($TabBD);
			echo "<option value=''></option>";
			foreach ($TabBD as $tab){
				echo "<option value='".$tab."'";
				if (strtoupper($val['NOM_TABLE']) == strtoupper($tab)){
					echo " selected";
				}
				echo ">".$tab."</option>";
			}
		?>																				
		</select >
		</td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('nom_champ'); ?></td>
        <td>
		<div id='NOM_CHAMP' style='display:inline'>														
		<select name="NOM_CHAMP" id="NOM_CHAMP">
			<option value=''></option>
		</select>
		</div>
		</td>
    </tr>
	<tr>
	  <td width="100px"><?php echo recherche_libelle_page('nom_champ_entete'); ?></td>
	  <td><input type="text" name="DESCRIP_CHAMP_PLAGE" id="DESCRIP_CHAMP_PLAGE" value="<?php echo $val['DESCRIP_CHAMP_PLAGE']; ?>" style="width: 300px"/></td>
	</tr>
    <tr> 
        <td><?php echo recherche_libelle_page('val_min'); ?></td>
        <td><INPUT type="text" size="20" name="VAL_MIN" value="<?php echo $val['VAL_MIN']; ?>" maxlength="50"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('val_max'); ?></td>
        <td><INPUT type="text" size="20" name="VAL_MAX" value="<?php echo $val['VAL_MAX']; ?>" maxlength="50"></td>
    </tr>
</table>
<br>
<?php $_SESSION['descr_champ_plage'] = $val['DESCRIP_CHAMP_PLAGE'];?>
<script type='text/javascript'>
	load_champs()
</script>
