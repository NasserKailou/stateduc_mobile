<script type='text/javascript'>
	var xhr = null; 
	var xhr2 = null;
	var xhr3 = null;
	
	function getXhr(){
		if(window.XMLHttpRequest){ // Firefox et autres
		   xhr = new XMLHttpRequest(); 
		   xhr2 = new XMLHttpRequest();
		   xhr3 = new XMLHttpRequest();
		}
		else if(window.ActiveXObject){ // Internet Explorer 
		   try {
					xhr = new ActiveXObject("Msxml2.XMLHTTP");
					xhr2 = new ActiveXObject("Msxml2.XMLHTTP");
					xhr3 = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
					xhr2 = new ActiveXObject("Microsoft.XMLHTTP");
					xhr3 = new ActiveXObject("Microsoft.XMLHTTP");
				}
		}
		else { // XMLHttpRequest non supporté par le navigateur 
		   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
		   xhr = false; 
		   xhr2 = false; 
		   xhr3 = false; 
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
				document.getElementById('CHAMP_REF').innerHTML = leselect;
				jQuery('#CHAMP_REF select').attr('style', '');
				jQuery('#CHAMP_REF select').uniform();
			}
		}
		xhr2.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr2.readyState == 4 && xhr2.status == 200){
				letexte = xhr2.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('SQL_CRITERE').innerHTML = letexte;
				jQuery('#SQL_CRITERE select').attr('style', '');
				jQuery('#SQL_CRITERE select').uniform();
			}
		}
		sel = document.getElementById('TABLE_REF');
		table = sel.options[sel.selectedIndex].value;
		url="synthese.php?val=charger_chps&table="+table+"<?php if(trim($val['CHAMP_REF'])<>'') echo '&chp_ref='.$val['CHAMP_REF']; else echo '&chp_ref=\'\''; ?>";
		url2="synthese.php?val=charger_chps&val_dim_table=<?php echo $val['TABLE_REF']; ?>&table="+table+"<?php echo '&chp_sql_crit='.urlencode($val['SQL_CRITERE']); ?>";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);		
		xhr2.open("GET",url2,true);
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
		xhr2.send(null);
	}
</script>

<br>
<table align="center">
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_crit'); ?></td>
        <td width="60%"><INPUT style="width : 20%;" readonly="1" type="text" size="3" name="ID_CRITERE" value="<?php echo $val['ID_CRITERE']; ?>"></td>
    </tr>
	
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('lib_crit'); ?></td>
        <td width="60%"><INPUT style="width : 90%" type="text" size="50" name="LIBELLE_CRITERE" value="<?php echo $val['LIBELLE_CRITERE']; ?>"></td>
    </tr>
	
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('tab_crit'); ?></td>
        <td width="60%">
		<select name="TABLE_REF" id="TABLE_REF" onchange="load_champs()">
		<?php $TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
			$tables = array();
				foreach($TabBD as $tab)
				{	
					$deb_nomenc = $GLOBALS['PARAM']['TYPE'].'_';
					if(preg_match("/^($deb_nomenc)/",strtoupper($tab)))
					{
						$tables[] = $tab;
					}
				}
			if($GLOBALS['conn']->databaseType<>'access'){
				$ReqBD	=	$GLOBALS['conn']->MetaTables("VIEWS");
				foreach($ReqBD as $tab)
				{
					$tables[] = $tab;
				}
			}
			$TabBD = $tables;
			sort($TabBD);
			echo "<option value=''></option>";
			foreach ($TabBD as $tab){
				echo "<option value='".$tab."'";
				if (strtoupper($val['TABLE_REF']) == strtoupper($tab)){
					echo " selected";
				}
				echo ">".$tab."</option>";
			}
		?>																				
		</select >
		</td>
    </tr>
    
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('chp_crit'); ?></td>
        <td width="60%">
		
		<div id='CHAMP_REF' style='display:inline'>
		<select name="CHAMP_REF">
			<option value=''></option>
		</select>
		</div>

		</td>
    </tr>
	
    
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('sql_crit'); ?></td>
        <td width="60%">
		
		<div id='SQL_CRITERE' style='display:inline'>
		<textarea name="SQL_CRITERE" cols="40" rows="4"></textarea>
		</div>
		
    </tr>
</table>
<br>
<script type='text/javascript'>
	load_champs();
</script>
