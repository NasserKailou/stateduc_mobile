<?php 
	lit_libelles_page('/import.php');
?>
<br>
<br>                
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<script type="text/Javascript">
	function fermer() {
		window.close();
	}
	
  function importData(zipFile, idxLog) {
    
		var params = 'serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>'+
                 '&op=import_data&data_file='+zipFile+
                 '&log_filename='+jQuery('#log_filename').val().trim()+
                 '&idx='+idxLog;
    
    $.ajax({type:'post', url: 'server-side/include/administration/gestion_import_service.php', data: params, 
			success: function(response) {
				if (response.se_statut == 101) {
          var error_tr = '<tr><td colspan="3"> ERROR : '+response.se_message+'</td></tr>'; 
          endLogProcess(response.idx, error_tr);
				} else if (response.se_statut == 200) {
          endLogProcess(response.idx, response.se_datas);
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert(errorThrown+":"+XMLHttpRequest.responseText);     
				endLogProcess(idxLog, errorThrown+":"+XMLHttpRequest.responseText);   
			},
      ContentType: 'application/x-www-form-urlencoded', 
			dataType:'json',
			timeout: 5400000
		});
	}
  
  function endLogProcess(idxLog, logData) {
      var icStatus = 'b_valider.png';
      if (logData != '') {
          icStatus = 'b_alert.png';
      }
	  jQuery('.log_filter').removeClass('hidden');
	  jQuery('#div_log_'+idxLog+' a').removeClass('hidden');
      jQuery('#log_loading_'+idxLog).attr('src', '<?php echo $GLOBALS["SISED_URL_IMG"]; ?>'+icStatus);
      jQuery('#body_list_logs_'+idxLog).append(logData);
  }
  
  function createLogEntry(pathfile, filename, idx) {
      var html = '<h3 id="h3_log_'+idx+'">'+filename+'&nbsp;&nbsp;&nbsp;<img id="log_loading_'+idx+'" class="log_loading" src="<?php echo $GLOBALS["SISED_URL_IMG"]; ?>loading.gif" /></h3>'+
  	             '<div id="div_log_'+idx+'" class="div_log">'+
				 '<a class="btn btn-info hidden" href="<?php echo $GLOBALS["SISED_URL"]; ?>administration.php?val=download_file&fichier='+encodeURI(pathfile+'_log_'+filename.split('.').slice(0, -1).join('.'))+'_'+idx+'.log"><?php echo recherche_libelle_page('download_log'); ?></a>'+
                 '<table id="table_list_logs">'+
                  '<tbody id="body_list_logs_'+idx+'">'+
                    '<tr class="ui-widget-header">'+
                      '<th><?php echo recherche_libelle_page('import_time'); ?></th><th><?php echo recherche_libelle_page('import_sql'); ?></th>'+
					  '<th><?php echo recherche_libelle_page('import_status'); ?><br/>'+
					  '<div class="log_filter hidden">'+
					  	'<img src="client-side/image/b_ok.png">'+
						'<input name="" type="checkbox" onChange="hideShowOkLog(this, '+idx+')" value="" checked="checked"/>'+
					  '</div>'+
					  '<div class="log_filter hidden">'+
					  	'<img src="client-side/image/b_ko.png">'+
						'<input name="" type="checkbox" onChange="hideShowKoLog(this, '+idx+')" value="" checked="checked"/>'+
					  '</div>'+
					  '</th>'+
                    '</tr>'+
                    '</tbody>'+ 
                  '</table>'+
                 '</div>';   
      jQuery('#accordion_logs').append(html);     
      $( "#accordion_logs" ).accordion("refresh");
	  $( "#accordion_logs" ).accordion({ active: idx});      
  }
  
  function hideShowOkLog(checkbox, idx) {
  	if($(checkbox).is(":checked")){
		$('.log_ok_'+idx).closest("tr").show();
	}
	else {
		$('.log_ok_'+idx).closest("tr").hide();
	}
  }
  
  function hideShowKoLog(checkbox, idx) {
  	if($(checkbox).is(":checked")){
		$('.log_ko_'+idx).closest("tr").show();
	}
	else {
		$('.log_ko_'+idx).closest("tr").hide();
	}
  }
	 
  var idxLog = 1;
   
	$(function() {	
    
    $( "#accordion_logs" ).accordion({
    	heightStyle: "content"
    });
      
		var upload = $("#uploader").plupload({
			// General settings
			runtimes : 'html5,flash,silverlight,html4',
			url : 'server-side/include/administration/upload.php?serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>&delete_exist_base=1&file_age=0&target_dir=import_export',
	
			// User can upload no more then 20 files in one go (sets multiple_queues to false)
			//max_file_count: 20,
			chunk_size: '1mb',
			// Resize images on clientside if we can
			resize : {
				width : 200, 
				height : 150, 
				quality : 90,
				crop: true // crop to exact dimensions
			},
			filters : {
				// Maximum file size
				max_file_size : '2000mb',
				// Specify what files to browse for
				mime_types: [
					{title : "Zip files", extensions : "zip"}
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
				active: 'list'
			},
			// Flash settings
			flash_swf_url : '<?php echo $GLOBALS['SISED_URL_JSC']; ?>plupload/Moxie.swf',
			// Silverlight settings
			silverlight_xap_url : '<?php echo $GLOBALS['SISED_URL_JSC']; ?>plupload/Moxie.xap',
			init : {
          BeforeUpload: function(up, file) {
            this.setOption('url', 'server-side/include/administration/upload.php?serverpath=<?php echo $GLOBALS['SISED_PATH']; ?>&delete_exist_base=1&file_age=0&target_dir=import_export%2Ftemp%2F'+idxLog);
						createLogEntry('temp\\'+idxLog+'\\', file.name, idxLog);
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
							importData('<?php echo $GLOBALS['SISED_PATH']; ?>server-side\\import_export\\temp\\'+idxLog+'\\'+file.name, idxLog);  
              idxLog++;
						}
					},
					UploadComplete: function(up, file, info) {
					// Called when a file has finished uploading
					}
		  }
		}) 
	}); 
</script>
<TABLE class="center-table">
<caption class="ui-widget-header"><?php echo recherche_libelle_page('upload_files'); ?></caption>
<TR>
<TD align="center">	
		<form name="form_menu" method="post" action="">
		<table align="center" style="width:100%;">  
      <tr> 
        <td height="33" ><?php echo recherche_libelle_page('Dossier_lo'); ?></td>
        <td>
		      <input size="50" type="text" name="log_filename" id="log_filename" value="" >
		    </td>
      </tr> 
			<tr>
				<td colspan="2">						
					<div id="uploader">
						<p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
					</div>
				</td> 
			</tr>
	 </table>
	 </form>
</TD>
</TR>
</TABLE>
<TABLE class="center-table">
<TR>
<TD>
  <div id="accordion_logs">
  </div>
</TD>
</TR>
</TABLE>
<br/><br/>