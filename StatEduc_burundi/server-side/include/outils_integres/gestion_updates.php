<?php
lit_libelles_page(__FILE__);

$currRevId = '';
$currRevNum = '';
$currRevCom = '';

if (isset($_GET['rev_id']) && $_GET['rev_id'] != '') { 
	$currRevId = $_GET['rev_id'];
	$requete = "SELECT * FROM REVISION WHERE ID_REVISION=".$currRevId;
	$currRev = $GLOBALS['conn_dico']->GetRow($requete);
	$currRevNum = $currRev['NUM_REVISION'];
	$currRevCom = $currRev['COMMENT_REVISION']; 
	$requete = "SELECT NOM_REV_FILE FROM REVISION_FILES WHERE ID_REVISION=".$currRevId." AND CODE_TYPE_REVISION=1";
	$sqlFiles = $GLOBALS['conn_dico']->GetCol($requete);
	
	$requete = "SELECT NOM_REV_FILE FROM REVISION_FILES WHERE ID_REVISION=".$currRevId." AND CODE_TYPE_REVISION=2";
	$dicoFiles = $GLOBALS['conn_dico']->GetCol($requete);
	
	$requete = "SELECT NOM_REV_FILE FROM REVISION_FILES WHERE ID_REVISION=".$currRevId." AND CODE_TYPE_REVISION=3";
	$nomenFiles = $GLOBALS['conn_dico']->GetCol($requete);
	
	$requete = "SELECT NOM_REV_FILE FROM REVISION_FILES WHERE ID_REVISION=".$currRevId." AND CODE_TYPE_REVISION=4";
	$htmlFiles = $GLOBALS['conn_dico']->GetCol($requete);
}

function get_themes($htmlFiles) {
	$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as id_campagne, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as libelle_campagne
					FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
					ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'];         
	$campagnes = $GLOBALS['conn']->GetAll($requete);
	$campagnes = array_change_key_case_recursive($campagnes, CASE_LOWER);
	$campagnes_ok = array();
	foreach ($campagnes as $camp){  
		$campagnes_ok[$camp['id_campagne']] = $camp['libelle_campagne'];
	}	
	$requete = "SELECT DICO_THEME_SYSTEME.ID_SYSTEME, SYSTEME.LIBELLE_SYSTEME, DICO_THEME_SYSTEME.APPARTENANCE, DICO_THEME_SYSTEME.ID_THEME_SYSTEME, DICO_TRADUCTION.LIBELLE, DICO_THEME_SYSTEME.FRAME
FROM DICO_THEME_SYSTEME, DICO_TRADUCTION, SYSTEME
WHERE (((DICO_TRADUCTION.NOM_TABLE)='DICO_THEME_LIB_LONG') AND ((DICO_TRADUCTION.CODE_NOMENCLATURE)=DICO_THEME_SYSTEME.ID_THEME_SYSTEME))
AND SYSTEME.ID_SYSTEME=DICO_THEME_SYSTEME.ID_SYSTEME
ORDER BY DICO_THEME_SYSTEME.ID_THEME_SYSTEME;";
	$themes = $GLOBALS['conn_dico']->GetAll($requete);
	$html_theme = "";
	foreach ($themes as $i => $theme){ 
		$html_theme .= "<tr class=\"non_active_base liste_elt_";
		if ($i % 2 == 0) {
			$html_theme .= "a\">";
		} else {
			$html_theme .= "b\">";
		} 
		$html_theme .= "<td>".$theme['LIBELLE_SYSTEME']."</td><td>".$campagnes_ok[$theme['APPARTENANCE']]."</td><td>".$theme['LIBELLE']."</td><td>".$theme['FRAME']."</td><td><input type=\"checkbox\" ";
		if (in_array($theme['FRAME'], $htmlFiles)) {
			$html_theme .= "checked=\"checked\" ";
		}
		$html_theme .= "name=\"".$theme['FRAME']."\" id=\"th_".$theme['ID_THEME_SYSTEME']."\" /></td></tr>\n";
	}
	return $html_theme;
}

$list_tables_nommenc = array();
foreach (array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES')) as $nom_table){            
	if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/',strtoupper($nom_table)) or (strtoupper($nom_table)=='DICO_TRADUCTION') ){        
		$list_tables_nommenc[]    =   $nom_table ;
	}
}
sort($list_tables_nommenc);

$list_tables_dico = array();
foreach (array_map('strtoupper',$GLOBALS['conn_dico']->MetaTables('TABLES')) as $nom_table){      
	if (in_array(strtoupper($nom_table), $GLOBALS['PARAM']['DICO_TABLE_SHARED'])){  
		$list_tables_dico[]    =   $nom_table ;
	}
}
sort($list_tables_dico);

$nom_combo_systeme = 'LIBELLE';
$nom_code_systeme = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
$code_secteur = "";
if ($_GET['secteur']) {
	$code_secteur        = $_GET['secteur'];            
	$_SESSION['secteur'] = $_GET['secteur'];
} else if ($_SESSION['secteur']) {
	$code_secteur        = $_SESSION['secteur'];
	$_GET['secteur']     = $_SESSION['secteur'];
} else {
	print 'Pb avec le secteur';
}	
$fonction = "toggle_arbo('$nom_combo_systeme','$nom_combo_chaine', 'secteur')";
$systeme_html = create_combo($_SESSION['tab_secteur'], $nom_combo_systeme, $nom_code_systeme, $code_secteur,  $fonction);

if($_SESSION['groupe'] ==1) {
?>
<script type="text/Javascript">
	var currIdRev = -1;
	var dicoTables = '';
	var nomenTables = '';
	var htmlFiles = '';
	var sqlFiles = '';
	
	if (<?php if (isset($_GET["rev_id"])) echo '1==1'; else echo '1==0'; ?>) {
		currIdRev = '<?php echo $currRevId ?>';
		dicoTables = <?php echo json_encode(array_values($dicoFiles)); ?>;
		nomenTables = <?php echo json_encode(array_values($nomenFiles)); ?>;
		htmlFiles = <?php echo json_encode(array_values($htmlFiles)); ?>;
		sqlFiles = <?php echo json_encode(array_values($sqlFiles)); ?>;
	}
	
	function setRevisionsListe(newIdRep) {
		var params = 'Action=listeRevs';
		if (newIdRep != null) {
			editRev(newIdRep.id);
		}
		if (currIdRev != -1) {			
			$( "#accordion_revision" ).show();
			$( "#accordion_revision" ).accordion({
				heightStyle: "content"
			});
		}
		$.ajax({type:'post', url: 'server-side/include/administration/services/ws_gestion_updates_service.php', data: params, 
			success: function(response) {
				if (response.se_statut == 101) {
					alert(response.se_message);
				} else if (response.se_statut == 200) {
					var table = "<table id='table_liste_revs'><tr class='ui-widget-header'><th><?php echo recherche_libelle_page('num_version');?></th><th><?php echo recherche_libelle_page('num_version_comment');?></th><th>SQL</th><th>DICO</th><th>NOMENCLATURE</th><th>HTML</th><th></th></tr>";
					var elt = 0;
					$.each(response.se_data, function(key, val) {
						var classTd = "non_active_base";
						var line = "<tr id=\"line_rev_"+val.id+"\" class=\"ligne_base "+ classTd +" liste_elt_";
						if (elt == 0) {
							line += "a\">";
							elt = 1;
						} else {
							line += "b\">";
							elt = 0;	
						}
						line += "<td class='num_rev'>"+val.num+"</td>";
						line += "<td class='com_rev'>"+val.comm+"</td>";
						line += "<td class='rev_1'></td>";
						line += "<td class='rev_2'></td>";
						line += "<td class='rev_3'></td>";
						line += "<td class='rev_4'></td>";
						line += "<td>"+
								"<a href=\"javascript:editRev('"+val.id+"')\" title=\"<?php echo recherche_libelle_page('editer');?>\" class='btnRev'><img src='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_edit.png' /></a><br/>"+
								"<a href=\"javascript:deleteRev('"+val.id+"', '"+val.num+"')\" title=\"<?php echo recherche_libelle_page('supprimer');?>\" class='btnRev'><img src='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_drop.png' /></a><br/>";
						line += "<a href=\"#\" onclick=\"createDelRevZip('"+val.id+"', '"+val.num+"', this)\" title=\"<?php echo recherche_libelle_page('statut_rev');?>\" class='btnRev'><img class='img_inact_rev' src='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_val_"+val.val+".png' id='img_ckb_"+val.id+"' /></a><br/>"+
								"<input type='checkbox' name='ckb_"+val.id+"' id='' onClick='activateDesRev("+val.id+", this)' ";
						if (val.act == 1) {
							line += "checked='checked' ";
						}		
						line += " class='btnRev' title='<?php echo recherche_libelle_page('activer_rev');?>'/>";
						line += "</td>";
						line += "</tr>";
						table += line;
					});
					table += "</table>";
					jQuery("#div_liste_rev").empty();
					jQuery("#div_liste_rev").append(table);
					//runFormScript("#div_liste_rev");
					$.each(response.se_data, function(key, val) {
						setRevisionsFiles(val.id, 1);
					});
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
	
	function setRevisionsFiles(idRev, type) {
		if (type > 4) {
			return;
		}
		var params = 'Action=listeFiles';
			params += '&REV_ID='+idRev;
			params += '&TYPE_REV_FILE='+type;
		$.ajax({type:'post', url: 'server-side/include/administration/services/ws_gestion_updates_service.php', data: params, 
			success: function(response) {
				if (response.se_statut == 101) {
					alert(response.se_message);
				} else if (response.se_statut == 200) {
					var filesTab = '<table align="center" class="full_div"><tbody>';					
					$.each(response.se_data, function(i, file) {
						filesTab += '<tr id="'+file.slice(0, file.indexOf("."))+'_'+type+'"><td>'+file+'</td></tr>';
					});
					filesTab += '</tbody></table>';
					
					jQuery("#line_rev_"+idRev+" .rev_"+type).empty();
					jQuery("#line_rev_"+idRev+" .rev_"+type).append(filesTab);
					setRevisionsFiles(idRev, type+1);
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
	
	function editRev(id) {	
		location.href =  "administration.php?val=update_version&rev_id="+id;
	}
	
	function refreshPage(id) {
		if (id == currIdRev) {
			location.href = 'administration.php?val=update_version';
		} else {
			location.reload();
		}
	}
	
	function deleteRev(id, num) {
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
					var params = 'Action=DelRev&REV_ID='+id+'&REV_NUM='+num;
					var refreshPageFunc = refreshPage.bind(refreshPage, id);
					post(params, refreshPageFunc);
				},
				"<?php echo recherche_libelle_page('annuler');?>": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
	
	function saveFiles(files, type, usedTables) {
		var currTables = new Array();
		$(files).each(function( index ) {
			var nameTab = $(this).attr("name");
			if (!usedTables || usedTables.length == 0 || jQuery.inArray( nameTab, usedTables ) == -1) {
				var params = 'Action=AddFile';
					params += '&REV_ID='+currIdRev;
					params += '&REV_NUM=<?php echo $currRevNum; ?>';
					params += '&NOM_REV_FILE='+nameTab;
					params += '&TYPE_REV_FILE='+type;
				if (type == 2 || type == 3) {
					params += '&FILE_EXT=.xml';
				} else {
					params += '&FILE_EXT=';
				}
				var addFileFunc = addFile.bind(addFile, nameTab, type);	
				funcPost('server-side/include/administration/services/ws_gestion_updates_service.php', params, addFileFunc);
			}	
			currTables.push(nameTab);
		});
		var tabsDel = diffArray(usedTables, currTables);
		$.each(tabsDel, function(key, tab) {
			var params = 'Action=DelFile';
				params += '&REV_ID='+currIdRev;
				params += '&REV_NUM=<?php echo $currRevNum; ?>';
				params += '&NOM_REV_FILE='+tab;
				params += '&TYPE_REV_FILE='+type;
				if (type == 2 || type == 3) {
					params += '&FILE_EXT=.xml';
				} else {
					params += '&FILE_EXT=';
				}
			var delFileFunc = delFile.bind(delFile, tab, type);	
			funcPost('server-side/include/administration/services/ws_gestion_updates_service.php', params, delFileFunc);
		});
		return currTables;
	}
	
	function saveFile(fileName, type, usedTables) {
		if (!usedTables || usedTables.length == 0 || jQuery.inArray( fileName, usedTables ) == -1) {
			var params = 'Action=AddFile';
				params += '&REV_ID='+currIdRev;
				params += '&REV_NUM=<?php echo $currRevNum; ?>';
				params += '&NOM_REV_FILE='+fileName;
				params += '&TYPE_REV_FILE='+type;
			if (type != 1 && type != 4) {
				params += '&FILE_EXT=.xml';
			} else {
				params += '&FILE_EXT=';
			}
			var addFileFunc = addFile.bind(addFile, fileName, type);	
			funcPost('server-side/include/administration/services/ws_gestion_updates_service.php', params, addFileFunc);
			if (type == 1) {
				if ($('#lst_sql_files tbody tr:last td').length > 4) {
					$('#lst_sql_files tbody').append('<tr class="non_active_base"></tr>');
				}
				$('#lst_sql_files tbody tr:last').append('<td id="file_sql_'+fileName.slice(0, fileName.indexOf("."))+'">'+fileName+' <a href="javascript:removeFile(\''+fileName+'\', 1, sqlFiles)" title="<?php echo recherche_libelle_page('supprimer'); ?>"><img src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_drop.png" /></a></td>');
			}
			usedTables.push(fileName);
		}	
		return usedTables;
	}
	
	function removeFile(fileName, type, usedTables) {
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
					var params = 'Action=DelFile';
						params += '&REV_ID='+currIdRev;
						params += '&REV_NUM=<?php echo $currRevNum; ?>';
						params += '&NOM_REV_FILE='+fileName;
						params += '&TYPE_REV_FILE='+type;
					var delFileFunc = delFile.bind(delFile, fileName, type);	
					funcPost('server-side/include/administration/services/ws_gestion_updates_service.php', params, delFileFunc);
					if (type == 1) {
						$('#file_sql_'+fileName.slice(0, fileName.indexOf("."))).empty();
					}
					var i = usedTables.indexOf(fileName);
					usedTables.splice(i, 1);
				},
				"<?php echo recherche_libelle_page('annuler');?>": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
	
	function addFile(filename, type) {
		jQuery("#line_rev_"+currIdRev+" .rev_"+type+" table tbody").append('<tr id="'+filename.slice(0, filename.indexOf("."))+'_'+type+'"><td>'+filename+'</td></tr>');
	}
	
	function delFile(filename, type) {
		jQuery('#'+filename.slice(0, filename.indexOf("."))+'_'+type).remove();
	}
	
	function post(params, callback) {
		funcPost('server-side/include/administration/services/ws_gestion_updates_service.php', params, callback); //voir stateduc.js
	}
	
	function diffArray(a, b) {
		var seen = [], diff = [];
		for ( var i = 0; i < b.length; i++)
			seen[b[i]] = true;
		for ( var i = 0; i < a.length; i++)
			if (!seen[a[i]])
				diff.push(a[i]);
		return diff;
	}	
	
	function activateDesRev(id, elt) {
		var val = $( elt ).is(':checked')?1:0;
		var imgId = '#img_' + $( elt ).attr('name');
		var imgSrc = $( imgId ).attr('src');
		var statusRev = (imgSrc.indexOf('b_val_0') > -1)?0:1;
		if (val == 1 && statusRev == 0) {
			$( elt ).prop('checked', false);
			$.alert("<?php echo recherche_libelle_page('rev_not_final');?>", 'StatEduc');
			return;
		}
		var params = 'Action=activateRev&REV_ID='+id;
			params += '&ACT_VAL='+val;
		funcPost('server-side/include/administration/services/ws_gestion_updates_service.php', params, function() {
			
		});
	}
	
	function createDelRevZip(id, num, elt) {
		var params = '';
		var val = 0;
		var img = $( elt ).find('img')[0];
		if ($( img ).attr( "src").indexOf('b_val_0') > -1) {				
			params = 'Action=createZip';
			val = 1;
		} else {
			params = 'Action=delZip';
		}
		params += '&REV_NUM='+num+'&REV_ID='+id;
		funcPost('server-side/include/administration/services/ws_gestion_updates_service.php', params, function(rep) {
			if (rep=='ko_zip') {
				alert ("<?php echo recherche_libelle_page('ko_zip');?>");
			} else {
				$( img ).attr( "src", "<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_val_"+val+".png" );	
			}
		});
	}
	
	$(function() {
		setRevisionsListe(null);
		if (currIdRev == -1) {
			$( "#accordion_revision" ).hide();
		} else {
			$( "#accordion_revision" ).accordion({
				heightStyle: "content"
			});
		}
		var upload = $("#uploader").plupload({
			// General settings
			runtimes : 'html5,flash,silverlight,html4',
			url : 'server-side/include/administration/services/ws_upload_sql.php?target_dir=<?php echo $currRevNum; ?>/1',
	
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
					{title : "SQL", extensions : "sql"}
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
						if(jQuery("#delete_exist_file").is(':checked')) {
							this.setOption('url', 'server-side/include/administration/services/ws_upload_sql.php?delete_exist_file=1&target_dir=<?php echo $currRevNum; ?>/1');
						} else {
							this.setOption('url', 'server-side/include/administration/services/ws_upload_sql.php?target_dir=<?php echo $currRevNum; ?>/1');
						}
					},
					FileUploaded: function(up, file, info) {
					// Called when a file has finished uploading
						var response = JSON.parse(info.response);
						if (response.error) {
							if (response.error.message.indexOf('fileExist') > -1) {
								$.alert("<?php echo html_entity_decode(recherche_libelle_page('fileExist')); ?>", 'StatEduc');
							} else {
								$.alert(response.error.message, 'StatEduc');
							}
						} else {
							sqlFiles = saveFile(file.name, 1, sqlFiles);
						}
					},
					UploadComplete: function(up, file, info) {
					// Called when a file has finished uploading
					}
			}
		});
		jQuery("#submit_num_version").click(function(event) {
			event.preventDefault();
			var numVersion = jQuery("#num_version").val().trim();
			var numVersionComment = jQuery("#num_version_comment").val().trim();
			var idVersion = jQuery("#id_version").val().trim();
			if (numVersion.length == 0 || numVersionComment.length == 0) {
				$.alert("<?php echo html_entity_decode(recherche_libelle_page('champVide')); ?>", 'StatEduc');
			} else if (currIdRev < 0 ) {
				var params = 'Action=AddRevision';
				params += '&REV_NUM='+numVersion;
				params += '&REV_COMMENT='+numVersionComment;
				
				post(params, setRevisionsListe);
			} else {
				var params = 'Action=UpdRevision';
				params += '&REV_ID='+currIdRev;
				params += '&REV_NUM='+numVersion;
				params += '&REV_COMMENT='+numVersionComment;
				//var setRevisionsListeFunc = setRevisionsListe.bind(setRevisionsListe, currIdRev);
				post(params, setRevisionsListe);
			}
		});
		jQuery("#new_revision").click(function(event) {
			location.href =  "administration.php?val=update_version";
		});
		jQuery("#save_dico").click(function(event) {
			event.preventDefault();
			var lstTables = $("#div_choose_dico_tables input:checked");
			dicoTables = saveFiles(lstTables, 2, dicoTables);
		});
		jQuery("#save_nomen").click(function(event) {
			event.preventDefault();
			var lstTables = $("#div_choose_nommenclature input:checked");
			nomenTables = saveFiles(lstTables, 3, nomenTables);
		});
		jQuery("#save_html").click(function(event) { console.log("click");
			event.preventDefault();
			var lstTables = $("#div_choose_html_files input:checked");
			htmlFiles = saveFiles(lstTables, 4, htmlFiles);
		});
	});
</script>
<br>
<TABLE class="center-table" style="min-width: 1024px;">
<caption class="ui-widget-header"><?php echo recherche_libelle_page('gestion_updates');?></caption>
<TR>
<TD align="center">
		<TABLE align="center">
			<TR>
				<TD>
					<div id="div_liste_rev">
					
					</div>
				</TD>
			</TR>
		</TABLE><br/>
		<TABLE align="center" style="width:100%;">
			<TR align="center">
				<TD>
					<?php echo recherche_libelle_page('add_num_version');?>
				</TD>
				<TD style="width:100%;">
					<form name="form_add_revision" method="post" action="">
						<input type="hidden" style="width:90%;" name="id_version" id="id_version" value="">
						<table align="center" style="width:100%;">
							<tr>
								<td style="width:15%;">
									* <?php echo recherche_libelle_page('num_version');?> : 
								</td>
								<td style="width:80%;">
									<input type="text" style="width:90%;" name="num_version" id="num_version" value="<?php echo $currRevNum; ?>">
								</td> 
							</tr>               
							<tr>
								<td style="width:15%;">
									* <?php echo recherche_libelle_page('num_version_comment');?> : 
								</td>
								<td style="width:80%;">
									<textarea style="width:90%;" name="num_version_comment" id="num_version_comment" cols="5" rows="3"><?php echo $currRevCom; ?></textarea>
								</td> 
							</tr>              
							<tr>
								<td style="width:15%;"></td>
								<td style="width:80%;" align="right">
									<input type="button" name="submit_num_version" id="submit_num_version" value="<?php echo recherche_libelle_page('num_version_save');?>" />
		<input type="button" id="new_revision" class="float_right" value="<?php echo recherche_libelle_page('cancel_add_version');?>"/>
								</td> 
							</tr>
						</table>	
					</form>
				</TD>
			</TR>
	
		</TABLE>

		<form name="form_menu" method="post" action="">
		<table align="center" style="width:100%;">
			<tr>
				<td>
					<?php echo "<b>".recherche_libelle_page('curr_num_version'); echo " ".$currRevNum."</b>"; ?>
				</td>
			</tr>               
			<tr>
				<td>						
					<div id="accordion_revision">
						<h3><?php echo recherche_libelle_page('upload_sql'); ?></h3>
						<div>
							<div>
								<table id="lst_sql_files">
									<tbody>
										<tr class="non_active_base">
										<?php 
										foreach($sqlFiles as $i => $file) {
											if ($i % 5 == 0 && $i > 0) {
												echo "</tr><tr class=\"non_active_base\">";
											}
											echo "<td id=\"file_sql_".explode('.', $file)[0]."\">$file <a href=\"javascript:removeFile('".$file."', 1, sqlFiles)\" title=\"".recherche_libelle_page('supprimer')."\"><img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' /></a></td>";
										}
										?>
										</tr>
									</tbody>
								</table>
							</div>
							<div style="text-align:right;"><?php echo recherche_libelle_page('delete_exist_file'); ?>&nbsp;<input type="checkbox" name="delete_exist_file" id="delete_exist_file" /></div>
							<div id="uploader">
								<p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
							</div>
						</div>
						<h3><?php echo recherche_libelle_page('choose_dico_tables'); ?></h3>
						<div id="div_choose_dico_tables">
							<table align="center">
								<tr class="non_active_base">
								<?php 
								foreach($list_tables_dico as $i => $tab) {
									if ($i % 4 == 0 && $i > 0) {
										echo "</tr><tr class=\"non_active_base\">";
									}
									echo "<td><input type=\"checkbox\" ";
									if (in_array($tab, $dicoFiles)) {
										echo "checked=\"checked\" ";
									}
									echo "name=\"$tab\" id=\"$tab\" />".$tab."</td>";
								}
								?>
								</tr>
							</table>
							<button id="save_dico" class="float_right" type="button"><?php echo recherche_libelle_page('num_version_save');?></button>
						</div>
						<h3><?php echo recherche_libelle_page('choose_nommenclature'); ?></h3>
						<div id="div_choose_nommenclature">
							<table align="center">
								<tr class="non_active_base">
								<?php 
								foreach($list_tables_nommenc as $i => $tab) {
									if ($i % 4 == 0 && $i > 0) {
										echo "</tr><tr class=\"non_active_base\">";
									}
									echo "<td><input type=\"checkbox\" ";
									if (in_array($tab, $nomenFiles)) {
										echo "checked=\"checked\" ";
									}
									echo "name=\"$tab\" id=\"$tab\" />".$tab."</td>";
								}
								?>
								</tr>
							</table>
							<button id="save_nomen" class="float_right" type="button"><?php echo recherche_libelle_page('num_version_save');?></button>
						</div>
						<h3><?php echo recherche_libelle_page('choose_html_files'); ?></h3>
						<div id="div_choose_html_files">
							<table align="center">
								<tr class='ui-widget-header'><th><?php echo recherche_libelle_page('system'); ?></th><th><?php echo recherche_libelle_page('camp_name'); ?></th><th><?php echo recherche_libelle_page('theme_name'); ?></th><th><?php echo recherche_libelle_page('html_file'); ?></th><th></th></tr>
								<?php echo get_themes($htmlFiles); ?>
							</table>							
							<button id="save_html" class="float_right" type="button"><?php echo recherche_libelle_page('num_version_save');?></button>
						</div>
					</div>
				</td> 
			</tr>
	 </table>
	 </form>
		
</TD>
</TR>
</TABLE>
<?php
}
?>
