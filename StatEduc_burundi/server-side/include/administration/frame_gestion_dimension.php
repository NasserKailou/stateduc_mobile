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
	function load_champs1(){
		getXhr();
		// On défini ce qu'on va faire quand on aura la réponse
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				leselect1 = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('CHAMP').innerHTML = leselect1;
			}
		}
		
		xhr2.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr2.readyState == 4 && xhr2.status == 200){
				leselect2 = xhr2.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('DIM_LIBELLE').innerHTML = leselect2;
			}
		}
		
		xhr3.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr3.readyState == 4 && xhr3.status == 200){
				letexte = xhr3.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('DIM_SQL').innerHTML = letexte;
			}
		}
		
		sel = document.getElementById('TABLE_REF');
		table = sel.options[sel.selectedIndex].value;
		url="administration.php?val=charger_chps&table="+table+"<?php if(trim($val['CHAMP'])<>'') echo '&chp_dim='.$val['CHAMP']; else echo '&chp_dim=\'\''; ?>&type_theme=1";
		url2="administration.php?val=charger_chps&table="+table+"<?php if(trim($val['DIM_LIBELLE'])<>'') echo '&lib_dim='.$val['DIM_LIBELLE']; else echo '&lib_dim=\'\''; ?>&type_theme=1";
		url3="administration.php?val=charger_chps&val_dim_table=<?php echo $val['TABLE_REF']; ?>&table="+table+"<?php echo '&chp_sql_dim='.urlencode($val['DIM_SQL']); ?>&type_theme=1";
		
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		xhr2.open("GET",url2,true);	
		xhr3.open("GET",url3,true);	
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
		xhr2.send(null);
		xhr3.send(null);
	}
	
</script>

<table>
	<tr>
		<td><?php echo recherche_libelle_page('id_dim'); ?></td>
										<td><INPUT type="text" size="2" name="ID_DIMENSION" value="<?php echo $val['ID_DIMENSION']; ?>"></td>
	</tr>
	<tr>
		<td><?php echo recherche_libelle_page('tab_ref'); ?></td>
		<td>
		<select name="TABLE_REF" id="TABLE_REF" onchange="load_champs1()" style='width:240px'>
		<?php $TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
			$tables = array();
			$deb_nomenc = $GLOBALS['PARAM']['TYPE'].'_';
			$fin_nomenc_system = '_'.$GLOBALS['PARAM']['SYSTEME'];
				foreach($TabBD as $tab)
				{	
					if(preg_match("/^($deb_nomenc)/",strtoupper($tab)) && !preg_match("/($fin_nomenc_system)$/",strtoupper($tab)))
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
		<td><?php echo recherche_libelle_page('champ'); ?></td>
		<td>
		<div id='CHAMP' style='display:inline'>
		<select name="CHAMP" style="width: 240px">
			<option value=''></option>
		</select>
		</div>
		</td>
	</tr>
	<tr>
		<td><?php echo recherche_libelle_page('lib_dim'); ?></td>
		<td>
		<div id='DIM_LIBELLE' style='display:inline'>
		<select name="DIM_LIBELLE" style="width: 240px">
			<option value=''></option>
		</select>
		</div>
		</td>
	</tr>	
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('ent_dim'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="30" name="DIM_LIBELLE_ENTTE" value="<?php echo $val['DIM_LIBELLE_ENTTE']; ?>"></td>
    </tr>
	<tr>
		<td valign="top"><?php echo recherche_libelle_page('dim_sql'); ?></td>
		<td>
		<div id='DIM_SQL' style='display:inline'>
		<textarea name="DIM_SQL" cols="50" rows="10"></textarea>
		</div>
		</td>
	</tr>

</table>
<script type='text/javascript'>
	load_champs1();
</script>
