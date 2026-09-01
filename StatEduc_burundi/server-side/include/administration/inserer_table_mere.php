<?php
	lit_libelles_page('/gestion_zone.php');
	
	$tableMere = $_GET['tm'];
	$priorite = $_GET['p'];
	$idTheme = $_GET['th'];
	$iTab = $_GET['iTab'];
	$numTab = $_GET['numTab'];
	$typeTheme = $_GET['typeTheme'];
	$idSysteme = $_GET['id_sys'];
	$OkAction = 1;
	$ColTabBD = array();
	
	$_SESSION['GestZones']['type_int'] = array('-6','4','5','7','8','I','N','bigint','bit','decimal','float','float4','float8','int','int2','int4','int8','numeric','smallint','tinyint','real');
	$_SESSION['GestZones']['type_text'] = array('12','C','char','nchar','ntext','nvarchar','text','varchar');
	$_SESSION['GestZones']['type_date'] = array('11','D','T','datetime','smalldatetime');
		
	$ColTabBD	=	$GLOBALS['conn']->MetaColumns($tableMere);
	$requete	= "	SELECT  TYPE_DONNEES, CODE_TYPE_DONNEES, LIBELLE_TYPE_DONNEES   
										FROM   DICO_TYPE_DONNEES WHERE CODE_TYPE_DONNEES < 10";
	$_SESSION['GestZones']['type_donnees'] = $GLOBALS['conn_dico']->GetAll($requete);
	
	$requete = "SELECT TYPE_OBJET, CODE_TYPE_OBJET, LIBELLE_TYPE_OBJET   
		FROM   DICO_TYPE_OBJET
		WHERE CODE_LANGUE = '".$_SESSION['langue']."'";
	
	$_SESSION['GestZones']['type_objet'] = $GLOBALS['conn_dico']->GetAll($requete);
	
	/**
	* METHODE : get_tables_nomenc_db()
	* <pre>
	* Récupération des tables nomenclature de la base de données 
	* </pre>
	* @access public
	* 
	*/
	function get_tables_nomenc_db() {		
		$TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
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
		return $tables;
	}
	
	function Aff_Type_Donnees($curr_typ) {
		$currType = "";	
		if(in_array($curr_typ, $_SESSION['GestZones']['type_int'])) { $currType = "int"; }
		elseif(in_array($curr_typ, $_SESSION['GestZones']['type_text'])) { $currType = "text"; }
		elseif(in_array($curr_typ, $_SESSION['GestZones']['type_date'])) { $currType = "date"; }
		
		echo "<select name='TYPE_ZONE_BASE' disabled='disabled'>";
		echo '<option value=\'\'></option>';	
		foreach ($_SESSION['GestZones']['type_donnees'] as $j => $type_donnees) {
			if (in_array($type_donnees['CODE_TYPE_DONNEES'], array(1,2,3))) {
				echo "<option value='".trim($type_donnees['TYPE_DONNEES'])."'";							
				if (trim($type_donnees['TYPE_DONNEES']) == $currType ){
						echo " selected='selected'";
				}
				echo ">".get_libelle($type_donnees['CODE_TYPE_DONNEES'],'DICO_TYPE_DONNEES',$type_donnees['LIBELLE_TYPE_DONNEES'])."</option>";
			}
		}		
		echo "</select>";
	}
	
	function Aff_Type_Objets($i, $isCleSimpe, $isCodeReGroup) {
		echo "<select name='TYPE_OBJET' onchange='choisirZone(this, ".$i.")'>".
				"<option value=''></option>";
				foreach ($_SESSION['GestZones']['type_objet'] as $j => $type_objet) {
					$optValue = trim($type_objet['TYPE_OBJET']);
					echo "<option value='".$optValue."' ";
					if ($isCleSimpe && $optValue == 'systeme') {
						echo "selected='selected' ";
					} else if ($isCodeReGroup && $optValue == 'loc_etab') {
						echo "selected='selected' ";
					}
					echo "/>".get_libelle($type_objet['CODE_TYPE_OBJET'],'DICO_TYPE_OBJET',$type_objet['LIBELLE_TYPE_OBJET'])."</option>";
				}
		 echo "</select>";
	}
	
?>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<script type="text/javascript">	
	var prefixTabRef = '<?php echo $GLOBALS['PARAM']['CODE']; ?>_<?php echo $GLOBALS['PARAM']['TYPE']; ?>_';
	<?php		
		$nbCles = count($GLOBALS['PARAM_SYS']['CLES_SIMPLES']);	
		$idx = 1;
	?>	
	var clesSimples = [<?php foreach($GLOBALS['PARAM_SYS']['CLES_SIMPLES'] as $cle) { echo '"'.$cle.'"'; echo $idx<$nbCles?',':''; $idx++;} ?>];	
	<?php 
		$baseTables = get_tables_nomenc_db(); 
		$nbTables = count($baseTables);
		$idx = 1;
	?>
	var baseTables = [<?php foreach($baseTables as $tab) { echo '"'.$tab.'"'; echo $idx<$nbTables?',':''; $idx++;} ?>];	
	<?php		
		$nbElt = count($GLOBALS['PARAM_SYS']['COMP_CODE_TYPE']);	
		$idx = 1;
	?>	
	// liste des comportements associés au champs commençants par prefixTabRef
	var compCodeType = [<?php foreach($GLOBALS['PARAM_SYS']['COMP_CODE_TYPE'] as $elt) { echo '"'.$elt.'"'; echo $idx<$nbElt?',':''; $idx++;} ?>];
	<?php		
		$nbElt = count($GLOBALS['PARAM_SYS']['COMP_TYPE_NUMERIQUE']);	
		$idx = 1;
	?>	
	// liste des comportements de type numérique
	var compTypeNumerique = [<?php foreach($GLOBALS['PARAM_SYS']['COMP_TYPE_NUMERIQUE'] as $elt) { echo '"'.$elt.'"'; echo $idx<$nbElt?',':''; $idx++;} ?>];	
	<?php		
		$nbElt = count($GLOBALS['PARAM_SYS']['COMP_TABLE_FILLE']);	
		$idx = 1;
	?>	
	// liste des comportements avec table fille
	var compTableFille = [<?php foreach($GLOBALS['PARAM_SYS']['COMP_TABLE_FILLE'] as $elt) { echo '"'.$elt.'"'; echo $idx<$nbElt?',':''; $idx++;} ?>];	
						
	function insertTabM(iTab) {		
		var params = 'ActionTabM=AddTabM';
			params += '&TabMActive='+iTab;
			params += '&PRIORITE_'+iTab+'=<?php echo $priorite; ?>';
			params += '&NOM_TABLE_MERE_'+iTab+'=<?php echo $tableMere; ?>';
		
		var url = 'server-side/include/administration/gestion_zone_service.php?id_theme_choisi=<?php echo $idTheme; ?>';
		
		if ($(".insert_zone_status[src='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_zone_o.png']").exists() ||
					$(".insert_zone_status[src='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_zone_r.png']").exists()) {
			var funcConfirm = postQuery.bind(postQuery, url, params, insertZones);
			confirmDialog('<?php echo recherche_libelle_page('Warning'); ?>', funcConfirm, {title:'_'});
		} else {
			postQuery(url, params, insertZones);
		}	
	}
		
	function insertZones() {
		insertZone(0);
	}
	
	function insertZone(iZone) {
		var iTab = <?php echo $numTab; ?>;
		var ligneNum = iZone + 1;
		if ($('#LigneTableMere'+ligneNum).exists()) {
			var ligne = $('#LigneTableMere'+ligneNum);
			if ($(ligne).find('input[name=AJOUTER_ZONE]').is(':checked')) {
				var typeObjet = $(ligne).find('select[name=TYPE_OBJET]').val();
				var chpPere = $(ligne).find('input[name=CHAMP_PERE]').val();
				var params = 'ActionZone=NewZone';
					params += '&TabMActive='+iTab;
					params += '&ZoneActive='+iZone;
					params += '&CHAMP_PERE='+ chpPere;
					params += '&TYPE_ZONE_BASE='+ $(ligne).find('select[name=TYPE_ZONE_BASE]').val();
					params += '&LIBELLE='+ encodeURIComponent($(ligne).find('input[name=LIBELLE_ZONE]').val());
					params += '&ORDRE='+ $(ligne).find('input[name=ORDRE]').val();
					params += '&ZONE_STATUT='+ $(ligne).find('input[name=ZONE_STATUT]').val();
				
					params += '&ORDRE_TRI=';
					params += '&NOM_GROUPE='+ $(ligne).find('input[name=NOM_GROUPE]').val();
					params += '&ID_ZONE_REF=';
					params += '&CHAMP_FILS=';
					params += '&SQL_REQ=';
					params += '&TYPE_OBJET='+ typeObjet;
					
				var tableFille = chpPere.substr('<?php echo $GLOBALS['PARAM']['CODE']; ?>_'.length);
				if (inArray(tableFille, baseTables)) {							
					params += '&TABLE_FILLE='+tableFille;
				} else {
					params += '&TABLE_FILLE=';
				}				
				
				var funcCallback = insertZoneDetails.bind(insertZoneDetails, iTab, iZone, typeObjet);
				postZone(params, funcCallback);
			} else {
				insertZone(++iZone);
			}
		} else {
			var parentPath = window.parent.location.href;
			var refLimit = parentPath.indexOf('#');
			if (refLimit != -1) {
				parentPath = parentPath.substr(0, refLimit);
			}
			var refLimit = parentPath.indexOf('&newTabM');
			if (refLimit != -1) {
				parentPath = parentPath.substr(0, refLimit);
			}
			parentPath += '&newTabM='+iTab;
			window.parent.location = parentPath;
			//window.parent.location.reload();
			fermer();
		}		
	}
				
	function insertZoneDetails(iTab, iZone, typeObjet, IDZone) {
		var chkVal = '';
		var params = 'ActionZoneSyst=1';
			params += '&TYPE_OBJET='+ typeObjet;	
			params += '&ATTRIB_OBJET=';
			chkVal = '0';
			params += '&AFFICH_VERTIC_MES='+ chkVal;
			params += '&ORDRE_AFFICHAGE=';
			chkVal = '1';
			params += '&ACTIVER='+ chkVal;
			chkVal = '0';
			params += '&AFFICHE_TOTAL='+ chkVal;
			chkVal = '0';
			params += '&AFFICHE_SOUS_TOTAUX='+ chkVal;
			params += '&EXPRESSION=';
			params += '&LIB_EXPR=';
			chkVal = '0';
			params += '&AFFICHE_TOTAL_VERTIC='+ chkVal;
			params += '&VALEUR_CONSTANTE=';
			params += '&BOUTON_INTERFACE=';
			params += '&TABLE_INTERFACE=';
			params += '&CHAMP_INTERFACE=';
			params += '&FONCTION_INTERFACE=';
			params += '&REQUETE_CHAMP_SAISIE=';		
			
		var funcInsertNext = insertZone.bind(insertZone, ++iZone);	
		postZoneSystem(IDZone, params, funcInsertNext);
	}
	
	function postZone(params, callback) {
		var url = 'server-side/include/administration/gestion_zone_service.php?id_theme_choisi=<?php echo $idTheme; ?>';
		postQuery(url, params, callback);
	}
	
	function postZoneSystem(iZone, params, callback) {
		var url = 'server-side/include/administration/gestion_zone_systeme.php?id_zone_active='+iZone+'&id_systeme_choisi=<?php echo $idSysteme; ?>';
		postQuery(url, params, callback);
	}
	
	function postQuery(service, params, callback) {
	//console.log(service+'?'+params);console.log('');console.log('');
		$.ajax({type:'post', url: service, data: params, 
			success: function(response) {
				if (response.se_statut == 101) {
					$.alert(response.se_message, '_');
				} else if (response.se_statut == 200) {
					//console.log(response.se_type +' : ' + response.se_data);
					if (response.se_type == 'insertDetails') {
						var insdetailsFunc = callback.bind(callback, response.se_data);
						insdetailsFunc();
					} else if (callback != null) {
						callback();
					}
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$.alert('ERREUR : ' + params + '-' + errorThrown, '_');        
			}, 
			dataType:'json',
			timeout: 120000
		});
	}
	
	function choisirZone(typeObjet, ligneNum) {
		if ($(typeObjet).val() != '') {
			$('#LigneTableMere'+ligneNum).find(':checkbox').attr('checked', true);
		} else {
			var champPere = $('#LigneTableMere'+ligneNum).find('input[name=CHAMP_PERE]').val();	
			if (!inArray(champPere, clesSimples) && (champPere != '<?php echo $GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']; ?>')) {
				$('#LigneTableMere'+ligneNum).find(':checkbox').attr('checked', false);
			}
		}
		$.uniform.update('#LigneTableMere'+ligneNum+' :checkbox');
		validateZone($(typeObjet).val(), ligneNum);
	}
	
	function clickCheckbox(check, ligneNum) {
		if ($(check).is(':checked')) {
			var typeObjet = $('#LigneTableMere'+ligneNum).find('select[name=TYPE_OBJET]');	
			validateZone($(typeObjet).val(), ligneNum);
		}
	}
	
	// Valide la configuration d'une zone
	function validateZone(typeObjet, ligneNum) {					
		var ligneMere = $('#LigneTableMere'+ligneNum);	
		var typeTheme = '<?php echo $typeTheme; ?>';
		var champPere = $(ligneMere).find('input[name=CHAMP_PERE]').val();	
		var typeChamp = $(ligneMere).find('select[name=TYPE_ZONE_BASE]').val();	
		var img = $(ligneMere).find('.insert_zone_status');
		var imgPath = '<?php echo $GLOBALS['SISED_URL_IMG']; ?>';		
					
		// Le champ code_regroupement doit avoir le type d'affichage champ_localisation
		if (champPere == '<?php echo $GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']; ?>' && typeObjet != 'loc_etab') {
			$.alert("<?php echo recherche_libelle_page('err_code_regroupement'); ?>", '_');
			$(img).attr('src', imgPath+'b_zone_o.png');
			$(img).attr('title', "<?php echo recherche_libelle_page('err_code_regroupement'); ?>");
			$(ligneMere).find('input[name=ZONE_STATUT]').val('o:'+'err_code_regroupement');
			return false;
		}
					
		// Le champ code_type_system ne doit pas avoir de type d'affichage
		if (champPere == '<?php echo $GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']; ?>' && typeObjet.length > 0) {
			$.alert("<?php echo recherche_libelle_page('err_champ_code_type_system'); ?>", '_');
			$(img).attr('src', imgPath+'b_zone_r.png');
			$(img).attr('title', "<?php echo recherche_libelle_page('err_champ_code_type_system'); ?>");
			$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_champ_code_type_system');
			return false;
		}		
		
		// Tout champ excepté CODE_TYPE_SYSTEME doit avoir un comportement
		if ((champPere != '<?php echo $GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']; ?>') && (typeObjet.length == 0)) {
			$.alert("<?php echo recherche_libelle_page('err_no_comp'); ?>", '_');
			$(img).attr('src', imgPath+'b_zone_r.png');
			$(img).attr('title', "<?php echo recherche_libelle_page('err_no_comp'); ?>");
			$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_no_comp');
			return false;
		}
					
		//Comportemants correspondant à un type numérique
		if (inArray(typeObjet, compTypeNumerique)) {
			if(typeChamp != 'int') {
				$.alert("<?php echo recherche_libelle_page('err_comp_numerique'); ?>", '_');
				$(img).attr('src', imgPath+'b_zone_r.png');
				$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_numerique'); ?>");
				$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_numerique');
				return false;
			}
		}
		
		// Si comportement systeme_valeur_multiple alors mat_grille
		//Modif Hebié: desormais le systeme_valeur_multiple est utilisable dans un thème autre que mat grille
		/*if (typeObjet == 'systeme_valeur_multiple') {
			if(typeTheme != 'Mat_Grille') {
				$.alert("<?php echo recherche_libelle_page('err_comp_svmmg'); ?>", '_');
				$(img).attr('src', imgPath+'b_zone_r.png');
				$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_svmmg'); ?>");
				$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_svmmg');
				return false;
			}
		}*/
		
		// Si comportement text_valeur_multiple alors mat_grille
		if (typeObjet == 'text_valeur_multiple') {
			if(typeTheme != 'Mat_Grille') {
				$.alert("<?php echo recherche_libelle_page('err_comp_tvm'); ?>", '_');
				$(img).attr('src', imgPath+'b_zone_r.png');
				$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_tvm'); ?>");
				$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_tvm');
				return false;
			}
		}
		
		// Champ contenant CODE_TYPE_*
		if (champPere.substring( 0, prefixTabRef.length ) === prefixTabRef) {			
			if(!inArray(champPere, clesSimples) && (champPere != '<?php echo $GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']; ?>') && !inArray(typeObjet, compCodeType)) {
				$(img).attr('src', imgPath+'b_zone_o.png');
				$(img).attr('title', "<?php echo recherche_libelle_page('err_code_type_comp'); ?>");
				$(ligneMere).find('input[name=ZONE_STATUT]').val('o:'+'err_code_type_comp');
				$.alert("<?php echo recherche_libelle_page('err_code_type_comp'); ?>", '_');
				return false;
			}
			if(typeChamp != 'int') {
				$(img).attr('src', imgPath+'b_zone_o.png');
				$(img).attr('title', "<?php echo recherche_libelle_page('err_code_type_type_int'); ?>");
				$(ligneMere).find('input[name=ZONE_STATUT]').val('o:'+'err_code_type_type_int');
				$.alert("<?php echo recherche_libelle_page('err_code_type_type_int'); ?>", '_');
				return false;
			}			
		} 	
		
		//Comportemants devant avoir une table fille
		if (inArray(typeObjet, compTableFille) && champPere.substring( 0, prefixTabRef.length ) != prefixTabRef) {
			$(img).attr('src', imgPath+'b_zone_r.png');
			$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_tablefille'); ?>");
			$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_tablefille');
			$.alert("<?php echo recherche_libelle_page('err_comp_tablefille'); ?>", '_');
			return false;
		}
		
		// Si comportement systeme_valeur_unique alors boutonI<>0 et champI<>0 et fonctionI<>0 et requeteI<>0
		if (typeObjet == 'systeme_valeur_unique') {
			$(img).attr('src', imgPath+'b_zone_r.png');
			$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_svucl'); ?>");
			$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_svucl');
			$.alert("<?php echo recherche_libelle_page('err_comp_svucl'); ?>", '_');
			return false;
		}
		$(img).attr('src', imgPath+'b_zone_v.png');
		$(img).attr('title', "<?php echo recherche_libelle_page('TitreZoneOk'); ?>");
		$(ligneMere).find('input[name=ZONE_STATUT]').val('v:'+'ok');
		return true;
	}
	
	function annulerCreer() {
		confirmDialog('<?php echo recherche_libelle_page('Annuler'); ?>?', fermer, {title:'_'});
	}
</script>
<form name="Formulaire" method="POST" action="" style="margin-top: 45px; left: 20px;">
	<div style=""> 
		<div style="text-align:right; font-weight:bold; margin-right:20px;"> <?php echo recherche_libelle_page('TabMere'); ?> : <?php echo $tableMere; ?></div>
		<table class="sous_liste_form" id="zone_table_<?php echo $iTab; ?>" cellspacing="0">
			<tbody>
				<tr class="zone_table_header ui-widget-header">
					<tr class="zone_table_header ui-widget-header">
						<th class=""><div><?php echo recherche_libelle_page('OrdreZone'); ?></div></th>
						<th class=""><div><?php echo recherche_libelle_page('ChZone'); ?></div></th>
						<th class=""><div><?php echo recherche_libelle_page('TypeZone'); ?></div></th>
						<th class=""><div><?php echo recherche_libelle_page('LibZone'); ?></div></th>
						<th class=""><div><?php echo recherche_libelle_page('NomGrp'); ?></div></th>
						<th class=""><div><?php echo recherche_libelle_page('CompZone'); ?></div></th>
						<th class="liste_cmd"><div><?php echo recherche_libelle_page('StatutZone'); ?></div></th>
					</tr>
				</tr>
				<?php 
				$i = 0;
				foreach ($ColTabBD as $col) {
					$i++;
					echo "<tr id='LigneTableMere".$i."' class='zone_tr zone_liste liste_elt_";
					if (($i % 2) == 0) {
						echo "a";
					} else {
						echo "b";
					}
					$ordre = 10 * $i;
					echo "'>
						<td><input type='text' name='ORDRE' value='".$ordre."' size='2'/></td>
						<td><input class='readonly_input' type='text' name='CHAMP_PERE' value=\"".$col->name."\"  readonly='readonly'  style=\"width:255px;\"/><div class='tablefille hidden'></div><div class='chpfils hidden'></div><div class='reqsql hidden'></div></td>
						<td>";
					echo Aff_Type_Donnees($col->type);
					echo "</td>
						<td><input type='text' name='LIBELLE_ZONE' value=\"\"  style=\"width:280px;\"/></td>
						<td><input type='text' name='NOM_GROUPE' value=\"\"  style=\"width:100px;\"/></td>
						<td>";
					$isCleSimpe = in_array($col->name, $GLOBALS['PARAM_SYS']['CLES_SIMPLES']);
					$isCodeReGroup = $col->name == $GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT'];
					$choisir = $isCleSimpe || ($col->name == $GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']) || $isCodeReGroup;
					echo Aff_Type_Objets($i, $isCleSimpe, $isCodeReGroup);
					echo "</td>
						<td align='center'>
							<input type='checkbox' name='AJOUTER_ZONE' ";  echo $choisir?"checked='checked' onclick='clickCheckbox(this,".$i.");' />":"onclick='clickCheckbox(this,".$i.");' />";
					echo "<img src='".$GLOBALS['SISED_URL_IMG']."b_zone_"; echo $choisir?"v.png":".png"; echo"' "; echo $choisir?"title=\"".recherche_libelle_page('TitreZoneOk')."\"":"title=\"".recherche_libelle_page('TitreZoneNonVerif')."\""; echo " class='insert_zone_status' />";
					echo "<input type='hidden' name='ZONE_STATUT' value='"; echo $choisir?"v:ok":""; echo "' />";	
					echo "</td>
					</tr>";
				}
				?>
				<tr>
						<td colspan="6" align="right">
						<input type="button" value="<?php echo recherche_libelle_page('Annuler'); ?>" name="btn_annuler_zones" onclick="javascript:annulerCreer()" />&nbsp;<input type="button" value="<?php echo recherche_libelle_page('Creer'); ?>" name="btn_creer_zones" onclick="javascript:insertTabM(<?php echo $iTab; ?>)" />
					</td>
				</tr>
			</tbody>
		</table>	
	</div>
</form>