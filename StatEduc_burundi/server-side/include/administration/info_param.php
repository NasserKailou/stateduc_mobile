<?php
lit_libelles_page(__FILE__);
$server = array();
$server[] = array("Access","access");
$server[] = array("Sql Serveur","mssql");
$server[] = array("MySql","mysql");
$server[] = array("Oracle","oracle");
$server[] = array("PostgreSQL","postgres9");
$active = $connexion->getActive();

if (isset($_POST['Defaut'])){
		if( isset($_POST['LIBELLE_TYPE_ANNEE']) and (trim($_POST['LIBELLE_TYPE_ANNEE'])<>'') ){
				$dft_annee = $_POST['LIBELLE_TYPE_ANNEE'];
		}
		else{
				$dft_annee = $_SESSION['annee'];
		}
		if( isset($_POST['LIBELLE_TYPE_PERIODE']) and (trim($_POST['LIBELLE_TYPE_PERIODE'])<>'') ){
				$dft_filtre = $_POST['LIBELLE_TYPE_PERIODE'];
		}
		else{
				$dft_filtre = $_SESSION['filtre'];
		}
		if( isset($_POST['LIBELLE']) and (trim($_POST['LIBELLE'])<>'') ){
				$dft_secteur = $_POST['LIBELLE'];
		}
		else{
				$dft_secteur = $_SESSION['secteur'];
		}
		if( isset($_POST['LIBELLE_LANGUE']) and (trim($_POST['LIBELLE_LANGUE'])<>'') ){
				$dft_langue = $_POST['LIBELLE_LANGUE'];
		}
		else{
				$dft_langue = $_SESSION['langue'];
		}
		
		$req_param_dft 	= 'SELECT * FROM PARAM_DEFAUT';
		$exist_dft 			= $GLOBALS['conn_dico']->GetAll($req_param_dft);
		if($GLOBALS['PARAM']['FILTRE']==true){
			$update_dft_filtre=', CODE_FILTRE='.(int)$dft_filtre;
			$insert_dft_filtre=', '.$dft_filtre;
		}
		else{
			$update_dft_filtre='';
			$insert_dft_filtre='';
		}
		if(is_array($exist_dft) and count($exist_dft)){
				$requete = 'UPDATE PARAM_DEFAUT SET CODE_ANNEE='.(int)$dft_annee.
							$update_dft_filtre.', 
							CODE_LANGUE=\''.$dft_langue.'\', 
							CODE_SECTEUR='.(int)$dft_secteur.'';
		}
		else{
				$requete = 'INSERT INTO PARAM_DEFAUT(CODE_ANNEE,CODE_FILTRE,CODE_LANGUE,CODE_SECTEUR)
							VALUES('.$dft_annee.$insert_dft_filtre.', '.$dft_langue.', '.$dft_secteur.')';
		}
		//echo $requete;
		$GLOBALS['conn_dico']->execute($requete);
    //$_SESSION['NB_NIVEAU_ARBO'] = $_POST['NB_NIVEAU_ARBO'];
}
if ( isset($_GET['serveur']) && !empty($_GET['serveur']) ) {
	$active['type'] = $_GET['serveur'];
}
?>
<script type="text/Javascript">
	var isConOk = true;
	function toggle_annee() {
			var value_annee = jQuery('#<?php echo $GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_ANNEE'];?> option:selected').val();
			location.href= '?<?php if(isset($_GET['val']) && $_GET['val']<>'') echo "val=".$_GET['val']; ?>&annee='+value_annee;
	}
	function toggle_secteur() {
			var value_secteur = jQuery('#LIBELLE option:selected').val();
			location.href= '?<?php if(isset($_GET['val']) && $_GET['val']<>'') echo "val=".$_GET['val']; ?>&secteur='+value_secteur;
	}
	function toggle_langue() {
			var value_langue = jQuery('#LIBELLE_LANGUE option:selected').val();
			location.href= '?<?php if(isset($_GET['val']) && $_GET['val']<>'') echo "val=".$_GET['val']; ?>&langue='+value_langue;
	}
</script>
<?php
if($GLOBALS['PARAM']['FILTRE']==true){
	echo '<script type="text/Javascript">'."\n";
	echo 'function toggle_filtre() {'."\n";
			echo "var value_filtre = jQuery('#".$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']." option:selected').val();"."\n";
			if(isset($_GET['val']) && $_GET['val']<>'') echo 'location.href= "?val='.$_GET['val'].'&filtre="+value_filtre;'."\n";
			else echo 'location.href= "?filtre="+value_filtre;'."\n";
	echo '}'."\n";
	echo '</script>'."\n";
}
?>
<br>
<br>
<?php
if(isset($_GET['val']) && $_GET['val']=='param_conn'){
	echo "<br/><div align='center'><b>".recherche_libelle_page('no_conn_msg')."</b></div>";
}
?>
<TABLE class="center-table">
<caption class="ui-widget-header"><?php echo recherche_libelle_page('Params');?></caption>
<?php
if(isset($_GET['val']) && $_GET['val']=='param_conn') {
?>
<TR>	
	<TD align="right">
		<form method="post" name="form_conn" action="administration.php?val=param_conn">
			<INPUT type="submit" name="btn_quitter" value="<?php echo recherche_libelle_page('quitter');?>"/>
			<INPUT type="submit" name="btn_connexion" value="<?php echo recherche_libelle_page('connexion');?>"/>
		</form>
		<script type="text/Javascript">
			isConOk = false;
		</script>	
	</TD>
</TR>
<?php
}
?>
<TR>
<TD align="center">
<?php
if(isset($_GET['val']) && $_GET['val']<>'param_conn'){
?>
<TABLE align="center">  
 <form name="param" enctype="multipart/form-data" method="post" action="administration.php?val=param">
	 <TR>		
    <TD>
	  <TABLE align="center">
		
     <TR> 
		<TD nowrap><?php echo recherche_libelle_page(AnCour);?></TD>
	</TR>
	<TR> 
            <TD>
<?php echo create_combo($_SESSION['tab_annees'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $_SESSION['annee'], 'toggle_annee()'); ?>
            </TD>
    </TR>
    </TABLE>
    </TD>
<?php if($GLOBALS['PARAM']['FILTRE']==true){
	echo '<TD>'."\n";
		  echo '<TABLE align="center">'."\n";
		 echo '<TR>'."\n"; 
				echo '<TD nowrap>'.recherche_libelle_page(PeriodCour).'</TD>'."\n";
			echo '</TR>'."\n";
			echo '<TR>'."\n"; 
				echo '<TD>'."\n";
	echo create_combo($_SESSION['tab_filtres'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $_SESSION['filtre'], 'toggle_filtre()');
				echo '</TD>'."\n";
			echo '</TR>'."\n";
		echo '</TABLE>'."\n";
		echo '</TD>'."\n";
		$colspan=4;
	}
	else{
		$colspan=3;
	}
?>
    <TD>
    <TABLE>
        <TR>
            <TD>
<?php echo recherche_libelle_page(SsSectCour);?>
            </TD>
        </TR>
        <TR>
            <TD>
<?php echo create_combo($_SESSION['tab_secteur'], 'LIBELLE',$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $_SESSION['secteur'], 'toggle_secteur()'); ?>
            </TD>
        </TR>
    </TABLE>
    </TD>
    
    <TD>
    <TABLE>
        <TR>
            <TD>
<?php echo recherche_libelle_page(LangCour);?>
            </TD>
        </TR>
        <TR>
            <TD>
<?php echo create_combo($_SESSION['tab_langues'], 'LIBELLE_LANGUE', 'CODE_LANGUE', $_SESSION['langue'], 'toggle_langue()'); ?>
            </TD>
        </TR>
    </TABLE>
    </TD>
		</TR>
		<TR>  
        <TD align="center" colspan="<?php echo $colspan; ?>">
            <INPUT type="submit" name="Defaut" value="<?php echo recherche_libelle_page('default');?>">
        </TD>
  </TR>
</form>
</TABLE>
<?php
}
?>	
</TD>
</TR>

<script type="text/Javascript">
	
	function toggle_sources() {
		var serveur = jQuery("#database_sources option:selected").val();
		location.href= '?<?php if(isset($_GET['val']) && $_GET['val']<>'') echo "val=".$_GET['val']; ?>&serveur='+serveur;
	}
	
	function insertDatabase(basename) {
		if (!testBaseExist('Access', 'access', basename, 'Admin', '')) {
			var params = 'serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>&op=insert_base_access<?php if(isset($_GET['val']) && $_GET['val']<>'') echo "&val=".$_GET['val']; ?>&filename='+basename;
			$.ajax({type:'post', url: 'server-side/include/administration/gestion_base_service.php', data: params, 
				success: function(response) {
					if (response.se_statut == 101) {
						alert(response.se_message);
					} else if (response.se_statut == 200) {
						setDatabaseListe();
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					alert(XMLHttpRequest.responseText);        
				}, 
				dataType:'json',
				timeout: 10000
			});
		} else {			
			$.alert('<?php echo html_entity_decode(recherche_libelle_page('baseExist')); ?>', 'Stateduc');
		}
	}
	
	function insertOtherDatabase(server, type, url, user, mdp, nom) {
		if (!testBaseExist(server, type, url, user, nom)) {
			var params = 'serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>&op=insert_base_other<?php if(isset($_GET['val']) && $_GET['val']<>'') echo "&val=".$_GET['val']; ?>&nom_serveur='+server;
			params += '&type_serveur='+type;
			params += '&url_base='+url;
			params += '&user='+user;
			params += '&mdp='+mdp;
			params += '&nom_base='+nom;
			
			post(params, setDatabaseListe);
		} else {			
			$.alert('<?php echo html_entity_decode(recherche_libelle_page('baseExist')); ?>', 'Stateduc');
		}
	}
	
	function testBaseExist(server, type, url, user, nom) {
		var result = 0;
		$('#table_liste_bases .ligne_base').each(function() {
			var currServer = $(this).find('.server').html().toLowerCase().trim();
			var currType = $(this).find('.type').html().toLowerCase().trim();
			var currUrl = $(this).find('.url').html().toLowerCase().replace(/[\/\\]/gi,'').trim();
			var urlTested = url.toLowerCase().replace(/[\/\\]/gi,'').trim();
			var currUser = $(this).find('.user').html().toLowerCase().trim();
			var currNom = $(this).find('.nom').html().toLowerCase().trim();
			if ((currServer == server.toLowerCase()) && (currType == type.toLowerCase()) && (currUrl == urlTested) && (currUser == user.toLowerCase()) && (currNom == nom.toLowerCase())) {
				result += 1;
			}
		});
		return result > 0;
	}
	
	function setDatabaseListe() {
		var params = 'serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>&op=liste_base<?php if(isset($_GET['val']) && $_GET['val']<>'') echo "&val=".$_GET['val']; ?>';
		$.ajax({type:'post', url: 'server-side/include/administration/gestion_base_service.php', data: params, 
			success: function(response) {
				if (response.se_statut == 101) {
					alert(response.se_message);
				} else if (response.se_statut == 200) {
					var table = "<table id='table_liste_bases'><tr class='ui-widget-header'><th><?php echo recherche_libelle_page('typesgbd');?></th><th>Type</th><th><?php echo recherche_libelle_page('serveur');?></th><th><?php echo recherche_libelle_page('nom_utilisateur');?></th><th><?php echo recherche_libelle_page('mot_de_passe');?></th><th><?php echo recherche_libelle_page('nom_base');?></th><th></th></tr>";
					var elt = 0;
					$.each(response.se_datas, function(key, val) {
						var classTd = "";
						if (val.statut == 1) {
							classTd = "active_base";
						} else {
							classTd = "non_active_base";
						}
						var line = "<tr class=\"ligne_base "+ classTd +" liste_elt_";
						if (elt == 0) {
							line += "a\">";
							elt = 1;
						} else {
							line += "b\">";
							elt = 0;	
						}
						line += "<td class='server'>"+val.nomServeur+"</td>";
						line += "<td class='type'>"+val.typeServeur+"</td>";
						line += "<td class='url'>"+val.urlBase+"</td>";
						line += "<td class='user'>"+val.utilisateur+"</td>";
						line += "<td class='mdp'>"+val.motDePasse+"</td>";
						line += "<td class='nom'>"+val.nomBase+"</td>";
						if (val.statut == 0) {
							line += "<td>"+
								"<a href=\"javascript:activeBase('"+val.id+"')\" title=\"<?php echo recherche_libelle_page('activer');?>\"><img class='img_inact_base' src='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_val_0.png' /></a>&nbsp;"+
								"<a href=\"javascript:deleteBase('"+val.id+"')\" title=\"<?php echo recherche_libelle_page('supprimer');?>\"><img src='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_drop.png' /></a></td>";
						} else {
							line += "<td class=\"active_base\"><img src='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_val_active.png' /></td>";
						}
						line += "</tr>";
						table += line;
					});
					table += "</table>";
					jQuery("#div_liste_base").empty();
					jQuery("#div_liste_base").append(table);
					inactBaseLinkHover();
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert(XMLHttpRequest.responseText);      
				console.log(XMLHttpRequest.responseText);  
			}, 
			dataType:'json',
			timeout: 10000
		});
	}
	
	function inactBaseLinkHover() {
		jQuery(".img_inact_base").hover(
			function() {
				$( this ).attr( "src", "<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_val_1.png" );
			}, function() {
				$( this ).attr( "src", "<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_val_0.png" );
			}
		);
	}
	
	function activeBase(id) {	
		$( "#confirm_content" ).empty();
		$( "#confirm_content" ).append("<?php echo recherche_libelle_page('confirmer_operation');?>");
		$( "#div_confirm" ).dialog({
			dialogClass: "toTop",	
			resizable: false,
			height:155,
			modal: true,
			buttons: {
				"<?php echo recherche_libelle_page('activer');?>": function() {
					$( this ).dialog( "close" );
					var params = 'serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>&op=active_base<?php if(isset($_GET['val']) && $_GET['val']<>'') echo "&val=".$_GET['val']; ?>&id='+id;
					post(params, refresh_page);
				},
				"<?php echo recherche_libelle_page('annuler');?>": function() {
					$( this ).dialog( "close" );
				}
			}
		});	
	}
	
	function refresh_page() {
		if (!isConOk) {
			location.href = 'accueil.php';
		} else {
			location.reload();
		}
	}
	
	function deleteBase(id) {
		$( "#confirm_content" ).empty();
		$( "#confirm_content" ).append("<?php echo recherche_libelle_page('confirmer_operation');?>");
		$( "#div_confirm" ).dialog({
			dialogClass: "toTop",	
			resizable: false,
			height:155,
			modal: true,
			buttons: {
				"<?php echo recherche_libelle_page('supprimer');?>": function() {
					$( this ).dialog( "close" );
					var params = 'serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>&op=delete_base<?php if(isset($_GET['val']) && $_GET['val']<>'') echo "&val=".$_GET['val']; ?>&id='+id;
					post(params, setDatabaseListe);
				},
				"<?php echo recherche_libelle_page('annuler');?>": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
	
	function post(params, callback) {
		funcPost('server-side/include/administration/gestion_base_service.php', params, callback); //voir stateduc.js
	}
	
	function setSaisieBaseInputs() {
		var userAgent = navigator.userAgent.toLowerCase();
		$.browser = {
			chrome: /chrome/.test( userAgent ),
			safari: /webkit/.test( userAgent ) && !/chrome/.test( userAgent ),
			opera: /opera/.test( userAgent ),
			msie: /msie/.test( userAgent ) && !/opera/.test( userAgent ),
			mozilla: /mozilla/.test( userAgent ) && !/(compatible|webkit)/.test( userAgent )
			};
		if (isIe()) {
			jQuery("#div_saisie_base").append('<div style="position :absolute ;" id="div_saisie_base_abs">'+
								'<input type="file" id="select_db" name="select_db" style="width:90%; filter :alpha(opacity=0); opacity:0" onchange="document.form_menu.base.value=document.form_menu.select_db.value"/></div>');
								
			jQuery("#div_saisie_base").append('<input style="width:90%;" id="access_base_url" type="text" name="base" value=""/>'+
								'<input type="button" value="..." onclick="document.getElementById(\'select_db\').click();"/>');
		} else {
			jQuery("#div_saisie_base").append('<input style="width:90%;" id="access_base_url" type="text" name="base" value="">');
		}
		jQuery("#div_saisie_base").append('<img src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>addIcon.png" id="add_single_database"/>');
		unpdateFormElt();
		jQuery("#add_single_database").click(function(event) {
			if (jQuery("#access_base_url").val().length == 0) {
				$.alert('<?php echo html_entity_decode(recherche_libelle_page('champVide')); ?>', 'Stateduc');
			} else {
				insertDatabase(jQuery("#access_base_url").val().trim());
			}
		});
	}
	// Returns the version of Internet Explorer or a -1
	// (indicating the use of another browser).
	
	function getInternetExplorerVersion() {
		var rv = -1;
		if (navigator.appName == 'Microsoft Internet Explorer') {
			var ua = navigator.userAgent;
			var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
			if (re.exec(ua) != null)
				rv = parseFloat( RegExp.$1 );
		}
		else if (navigator.appName == 'Netscape') {
			var ua = navigator.userAgent;
			var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
			if (re.exec(ua) != null)
				rv = parseFloat( RegExp.$1 );
		}
		return rv;
	}
	
	function isIe()	{
		var ver = getInternetExplorerVersion();	
		if ( ver > -1 ) {
			return true;
		} else {
			return false;
		}
	}
	
	$(function() {
		//Liste des bases
		setDatabaseListe();
		//Input de saisie des bases
		setSaisieBaseInputs();	
		// Access database
		if ('<?php echo $active['type']; ?>' == 'access') {
			$( "#accordion_base_access" ).accordion({
				heightStyle: "content"
			});
			var upload = $("#uploader").plupload({
				// General settings
				runtimes : 'html5,flash,silverlight,html4',
				url : 'server-side/include/administration/upload.php?serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>',
		
				// User can upload no more then 20 files in one go (sets multiple_queues to false)
				//max_file_count: 20,
				chunk_size: '1mb',
				// Resize images on clientside if we can
				resize : {
					width : 200, 
					height : 200, 
					quality : 90,
					crop: true // crop to exact dimensions
				},
				filters : {
					// Maximum file size
					max_file_size : '2000mb',
					// Specify what files to browse for
					mime_types: [
						{title : "Base access", extensions : "mdb"},
						{title : "Image", extensions : "jpg"}
					]
				},
				// Rename files by clicking on their titles
				rename: true,
				// Sort files
				sortable: true,
				// Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
				dragdrop: false,
				// Views to activate
				views: {
					list: true,
					thumbs: true, // Show thumbs
					active: 'thumbs'
				},
				// Flash settings
				flash_swf_url : '<?php echo $GLOBALS['SISED_URL_JSC']; ?>plupload/Moxie.swf',
				// Silverlight settings
				silverlight_xap_url : '<?php echo $GLOBALS['SISED_URL_JSC']; ?>plupload/Moxie.xap',
				init : {
						BeforeUpload: function(up, file) {
							if(jQuery("#delete_exist_base").is(':checked')) {
								this.setOption('url', 'server-side/include/administration/upload.php?delete_exist_base=1&serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>');
							} else {
								this.setOption('url', 'server-side/include/administration/upload.php?serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>');
							}
						},
						FileUploaded: function(up, file, info) {
						// Called when a file has finished uploading
							var response = JSON.parse(info.response);
							if (response.error) {
								if (response.error.message == 'fileExist') {
									$.alert('<?php echo html_entity_decode(recherche_libelle_page('fileExist')); ?>', 'StatEduc');
								} else {
									$.alert(response.error.message, 'StatEduc');
								}
							} else {
								insertDatabase('<?php echo $GLOBALS['SISED_PATH']; ?>server-side/db/'+file.name);
							}
						},
						UploadComplete: function(up, file, info) {
						// Called when a file has finished uploading
						}
				}
			});
		}
		jQuery("#add_other_single_database").click(function(event) {
			var server = jQuery("#database_sources option:selected").html().trim();
			var type = jQuery("#database_sources option:selected").val().trim();
			var url = jQuery("#serveur").val().trim();
			var user = jQuery("#nom_utilisateur").val().trim();
			var mdp = jQuery("#mot_de_passe").val().trim();
			var base = jQuery("#base").val().trim();
			if (url.length == 0 || user.length == 0 || mdp.length == 0 || base.length == 0) {
				$.alert('<?php echo html_entity_decode(recherche_libelle_page('champVide')); ?>', 'StatEduc');
			} else {
				insertOtherDatabase(server, type, url, user, mdp, base);
			}
		});
	});
</script>
<br/>
<?php
if($_SESSION['groupe'] ==1){
?>
<TR>
<TD align="center">
		<TABLE align="center">
			<TR>
				<TD>					
					<div><?php echo recherche_libelle_page('choisir_base');?></div><br/>
					<div id="div_liste_base">
					
					</div>
				</TD>
			</TR>
		</TABLE><br/>
		<TABLE align="center">
			<TR align="center">
				<TD>
					<?php echo recherche_libelle_page('typesgbd');?>
				</TD>
				<TD>
					<select name="sources" id="database_sources" onchange="toggle_sources();">
					<?php 
					foreach($server as $i=>$s) {
						$selected = '';
						if($active['type'] == $s[1]) {
							$selected = ' selected="selected"';
						}
						echo '<option value="'.$s[1].'"'.$selected.'>'.$s[0].'</option>' . "\n";
					}
					?>
					</select>
				</TD>
			</TR>
	
		</TABLE>
	
		<form name="form_menu" method="post" action="">
		<table align="center" style="width:100%;">
			<?php if ($active['type'] == 'access') { ?>
			<tr>
				<td colspan="2">
					<?php echo recherche_libelle_page('ch_bd_acc');?>
				</td>
			</tr>               
			<tr>
				<td colspan="2">						
					<div id="accordion_base_access">
						<h3><?php echo recherche_libelle_page('saisi_chemin_base'); ?></h3>
						<div id="div_saisie_base">
							
						</div>
						<h3><?php echo recherche_libelle_page('upload_base'); ?></h3>
						<div>
							<div style="text-align:right;"><?php echo recherche_libelle_page('delete_exist_base'); ?>&nbsp;<input type="checkbox" name="delete_exist_base" id="delete_exist_base" /></div>
							<div id="uploader">
								<p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
							</div>
						</div>
					</div>
				</td> 
			</tr>
	<?php }else{ ?>
			<tr>
				<td style="width:15%;">
					<?php echo recherche_libelle_page('serveur');?>
				</td>
				<td style="width:80%;">
					<input type="text" style="width:90%;" name="serveur" id="serveur" value="">
				</td> 
			</tr>               
			<tr>
				<td style="width:15%;">
					<?php echo recherche_libelle_page('nom_base');?>
				</td>
				<td style="width:80%;">
					<input type="text" style="width:90%;" name="base" id="base" value="">
				</td> 
			</tr>
			<tr>
				<td style="width:15%;">
					<?php echo recherche_libelle_page('nom_utilisateur');?>
				</td>
				<td style="width:80%;">
					<input type="text" style="width:90%;" name="nom_utilisateur" id="nom_utilisateur" value="">
				</td> 
			</tr>
			<tr>
				<td style="width:15%;">
					<?php echo recherche_libelle_page('mot_de_passe');?>
				</td>
				<td style="width:80%;">
					<input type="password" style="width:90%;" name="mot_de_passe" id="mot_de_passe" value="">
				</td> 
			</tr>
			<tr> 
				<td colspan="2"><img src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>addIcon.png" id="add_other_single_database"/></td>
			</tr>
	<?php } ?>
	 </table>
	 </form>
</TD>
</TR>
<?php
}
?>
</TABLE>
