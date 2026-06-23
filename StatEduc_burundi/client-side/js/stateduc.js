
jQuery.fn.exists = function(){return jQuery(this).length>0;}

window.alert = function(message){
    $.alert(message, '_');
};

$.extend({
	/**
	* Create DialogBox by ID
	* 
	* @param { String } elementID
	*/
	getOrCreateDialog: function(id) {
	
		$box = $('#' + id);
		if (!$box.length) {
			$box = $('<div id="' + id + '"><p></p></div>').hide().appendTo('body');
		}
		return $box;
	},
	 
	alert: function (message, title) {
		if (title == '_') {
			title = 'StatEduc';	
		}
		$("<div></div>").dialog( {
			dialogClass: "toTop",					
			buttons: { "Ok": function () { $(this).dialog("close"); } },
			close: function (event, ui) { $(this).remove(); },
			resizable: false,
			title: title,
			modal: true
		}).text(message);
	}
});

function dialogVal(message) {
  $( "#result_val_diag" ).dialog({
  	dialogClass: "toTop",	
  	draggable: true,
  	//title: _title.length==0?'_':_title,
  	title: '_',
  	width: 1024,
  	minHeight: 600,
  	maxHeight: 600,
  	modal:true,
  	position: { my: "center top", at: "center bottom", of: $("#bandeau")},
  	open: function( event, ui ) {
  		$("#result_val_diag_ctt").append(message);
  	/*	$('#result_val_diag_ctt').load(function() {
  			var hauteur = this.contentWindow.document.body.offsetHeight;
  			var largeur = this.contentWindow.document.body.offsetWidth;
  			if (hauteur < 60) {
  				hauteur = _heigth - 50;
  			}	
  			if (largeur < _width) {
  				largeur = _width;
  			}
  			if (_title == 'popinstabmere' || _title == 'popinszone') {
  				hauteur = hauteur + 50;
  				largeur = largeur - 30;
  			}	
  			this.style.height = hauteur + 'px';
  			this.style.width = largeur + 'px';
  		});
  		//window.parent.jQuery("#div_dialog").dialog("option", "height", $(document).height()-20);*/
  	},
  	beforeClose: function( event, ui ) {
  		jQuery("#result_val_diag_ctt").empty();
  	}
  });
}
$(function() {
	if (!$('#themePage').exists()) {		
		runFormScript("#contenu");
	} else {
		
	}
	if (!isDialog()) {
		$('body').append('<a href="#" id="backToTop"></a>');
		$(window).scroll(function(){
			if ($(this).scrollTop() > 50) {
				$('#backToTop').fadeIn('slow');
			} else {
				$('#backToTop').fadeOut('slow');
			}
		});
		$('#backToTop').click(function(){
			$("html, body").animate({ scrollTop: 0 }, 500);
			return false;
		});
	}
	$('input[type=radio]').click(function() {
		var previousValue = $(this).attr('previousValue');
		var name = $(this).attr('name');
		var currVal = $(this).attr('value');
		$('input[name='+name+']').each(function (index) {
			if ($(this).attr('value') != currVal) {
				$(this).attr('previousValue', false)
			}
		});
		if (previousValue == 'true') {
			$(this).prop('checked', false);
			$(this).uniform("update");
		}
		$(this).attr('previousValue', $(this).prop('checked'));
	});
});


function runFormScript(iddiv) {
	//$("#"+iddiv+" :text,#"+iddiv+" :password,#"+iddiv+" textarea,#"+iddiv+" select,#"+iddiv+" :submit,#"+iddiv+" :button").uniform();
	//$(":text,:password,:checkbox,textarea,select,:submit,:button,button").uniform();
	if ($("#bandeau").length != 0) {
		jQuery(iddiv+" :text,"+iddiv+" :password,"+iddiv+" :checkbox,"+iddiv+" :radio,"+iddiv+" textarea,"+iddiv+" select").uniform();
		jQuery( iddiv+" :submit,"+iddiv+" :button,"+iddiv+" :reset,"+iddiv+" button" )
			.button()
			.click(function( event ) {
				//event.preventDefault();
			}
		);
	} else {
		jQuery(":text,:password,:checkbox,:radio,textarea,select").uniform();
		jQuery( ":submit,:button,:reset,button" )
			.button()
			.click(function( event ) {
				//event.preventDefault();
			}
		);
	}
}

function unpdateFormElt() {
	jQuery(":text,:password,:checkbox,:radio,textarea,select").uniform('update');
}

function uniformElt(iddiv) {
	jQuery(iddiv+" :text,"+iddiv+" :password,"+iddiv+" :checkbox,"+iddiv+" :radio,"+iddiv+" textarea,"+iddiv+" select").uniform();
}

function updateElt(iddiv) {
	jQuery(iddiv+" :text,"+iddiv+" :password,"+iddiv+" :checkbox,"+iddiv+" :radio,"+iddiv+" textarea,"+iddiv+" select").uniform('update');
	jQuery( iddiv+" :submit,"+iddiv+" :button,"+iddiv+" button" )
			.button()
}

function restoreElt(iddiv) {
	jQuery(iddiv+" :text,"+iddiv+" :password,"+iddiv+" :checkbox,"+iddiv+" :radio,"+iddiv+" textarea,"+iddiv+" select").uniform('restore');
}
	
function funcPost(service, params, callback) {
	console.log(params);
	console.log(service);
	//console.log(callback);	
	$.ajax({type:'post', url: service, data: params, 
		success: function(response) {
			if (response.se_statut == 101) {
				if (response.se_message == 'session_end') {
					window.document.location = 'index.php';	
				} else {
					$.alert(response.se_message, '_');
				}
			} else if (response.se_statut == 200) {
				//console.log(response.se_data);
				if ((response.se_type == 'details') || (response.se_type == 'insertDetails') || (response.se_type == 'insertTableM')
					 || (response.se_type == 'searchEtab') || (response.se_type == 'lstThemes') || (response.se_type == 'lstTabMeres')
					 || (response.se_type == 'exportFile') || (response.se_type == 'lstQueries')) {
					if (response.se_type == 'details') {
						//console.log(response.se_data);
					}
					var callbackFunc = callback.bind(callback, response.se_data);
					callbackFunc();
				} else if (response.se_data != null) {					
					var callbackFunc = callback.bind(callback, response.se_data);
					callbackFunc();
				} else if (callback != null) {
					callback();
				}
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$.alert('ERREUR : ' + textStatus + '-' + errorThrown, '_');      
		}, 
		dataType:'json',
		timeout: 120000
	});
}

function funcPostOut(service, params, callback) {
	$.ajax({type:'post', url: service, data: params, 
		success: function(response) {
			if (response.se_statut == 101) {
				if (response.se_message == 'session_end') {
					window.document.location = 'index.php';	
				} else {
					$.alert(response.se_message, '_');
				}
			} else if (response.se_statut == 200) {
				//console.log(response.se_data);
				if ((response.se_type == 'details') || (response.se_type == 'insertDetails') || (response.se_type == 'insertTableM')
					 || (response.se_type == 'searchEtab') || (response.se_type == 'lstThemes') || (response.se_type == 'lstTabMeres')
					 || (response.se_type == 'exportFile') || (response.se_type == 'lstQueries')) {
					if (response.se_type == 'details') {
						//console.log(response.se_data);
					}
					var callbackFunc = callback.bind(callback, response.se_data);
					callbackFunc();
				} else if (response.se_data != null) {					
					var callbackFunc = callback.bind(callback, response.se_data);
					callbackFunc();
				} else if (callback != null) {
					callback();
				}
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$.alert('ERREUR : ' + textStatus + '-' + errorThrown, '_'); 
			callback(textStatus);			
		}, 
		dataType:'json',
		timeout: 120000
	});
}

function funcPostHtml(service, params, callback) {
	$.ajax({type:'post', url: service, data: params, 
		success: function(response) {
			var callbackFunc = callback.bind(callback, response);
			callbackFunc();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$.alert('ERREUR : ' + textStatus + '-' + errorThrown, '_');      
		}, 
		contentType:'application/x-www-form-urlencoded; charset=iso8859-1',
		dataType:'html',
		timeout: 120000
	});
}

function isDialog() {
	var src = window.parent.jQuery('#dialog_content').attr('src');
	var sous_src = window.parent.jQuery('#sousdialog_content').attr('src');
	if (sous_src && sous_src.length > 0) {
		return true;
	} else if (src && src.length > 0) {		
		return true;
	} else {
		return false;
	}	
}


function fermer() {
	var src = window.parent.jQuery('#dialog_content').attr('src');
	var sous_src = window.parent.jQuery('#sousdialog_content').attr('src');
	if (sous_src.length > 0) {
		window.parent.jQuery('#div_sousdialog').dialog('close');
	} else if (src.length > 0) {		
		window.parent.jQuery('#div_dialog').dialog('close');
	} else {
		window.close()
	}	
}

function afficherPopupAvertissement(message, targetUrl, num_lig_page) {
	//alert(message);
	// crée la division qui sera convertie en popup 
	$('body').append('<div id="popupavertissement" title="StatEduc"></div>');
	$("#popupavertissement").html(message);
	// transforme la division en popup
	$("#popupavertissement").dialog({
		autoOpen: false,
		//modal: true,
		//width: 400,
		//closeOnEscape: false,
		dialogClass: 'dialogstyleperso',
		buttons: [
			{
				text: "OK",
				"class": 'ui-state-warning',
				click: function() {
					$(this).dialog( "close" );
					if(targetUrl != "" && num_lig_page != ""){
						window.location.href = targetUrl;
					}
				}
			}
		]
	});
	$("#popupavertissement").dialog ().prev ().find (".ui-dialog-titlebar-close").hide ();
	$("#popupavertissement").dialog( "open" );
	$("#popupavertissement").prev().addClass('ui-state-warning');
}

/*if (ctrlSaisie[2] == "text") {
	if (!ctrl_saisie_text(ctrlSaisie, "")) {return;}
} else if (ctrlSaisie[2].indexOf("dimension") > -1) {
	if (!ctrl_saisie_dimension(ctrlSaisie)) {return;}
}*/
	
	
function inArray(needle, haystack) {
	var length = haystack.length;
	for(var i = 0; i < length; i++) {
		if(haystack[i] == needle) return true;
	}
	return false;
}	

function ctrl_saisie() {
	$.each(ctrlSaisieLst, function(idx, ctrlSaisie) {
		if (ctrlSaisie[3] == "si") {
			
		} else if (ctrlSaisie[4].split(";").length < 3) {
			if (!ctrl_saisie_text(ctrlSaisie, "")) {return false;}
		} else {
			if (!ctrl_saisie_dimension(ctrlSaisie)) {return false;}
		}
	});	
}

function ctrl_saisie_text(ctrlSaisie, codeNomenc) {	
	var valg = 0;
	var zonesAddExpr = "";
	if (ctrlSaisie[4].indexOf(";") > -1) {
		// il y a une expression à gauche
		//concatène le champ concerné à l'expression définie
		//ctrlSaisie[4] += ctrlSaisie[1];
		var ctrlParts = ctrlSaisie[4].split(";");
		zonesAddExpr = ctrlParts[0].split("+");
		$.each(zonesAddExpr, function(idx, zoneAddExpr) {
			if (zoneAddExpr.indexOf("-") > -1) {
				var zonesMinExpr = zoneAddExpr.split("-");
				var valdMin = $("#"+zonesMinExpr[0]+"_0"+codeNomenc).val();
				for (var i = 1; i < zonesMinExpr.length; i++) {
					valdMin -= $("#"+zonesMinExpr[i]+"_0"+codeNomenc).val() * 1; 
				}
				valg += valdMin * 1;
			} else {
				valg += $("#"+zoneAddExpr+"_0"+codeNomenc).val() * 1;
			}
		});
		zonesAddExpr = ctrlParts[1].split("+");
	} else {	
		valg = $("#"+ctrlSaisie[1]+"_0"+codeNomenc).val() * 1;
		zonesAddExpr = ctrlSaisie[4].split("+");
	}
	var vald = 0;
	$.each(zonesAddExpr, function(idx, zoneAddExpr) {
		if (zoneAddExpr.indexOf("-") > -1) {
			var zonesMinExpr = zoneAddExpr.split("-");
			var valdMin = $("#"+zonesMinExpr[0]+"_0"+codeNomenc).val();
			for (var i = 1; i < zonesMinExpr.length; i++) {
				valdMin -= $("#"+zonesMinExpr[i]+"_0"+codeNomenc).val() * 1; 
			}
			vald += valdMin * 1;
		} else {
			vald += $("#"+zoneAddExpr+"_0"+codeNomenc).val() * 1;
		}
	});
	eval("do_submit = valg "+ctrlSaisie[3]+" vald");
	
	if (!do_submit) {
		alert(ctrlSaisie[5]+': ('+valg+') '+ctrlSaisie[3]+' ('+vald+') !');
		$("#"+ctrlSaisie[1]+"_0"+codeNomenc).focus();
		return false;
	}
	return true;
}

function ctrl_saisie_dimension(ctrlSaisie) {
	var ctrlParts = ctrlSaisie[4].split(";");
	var exp2 = ctrlParts[1];
	exp2 = exp2.replace("+","");
	exp2 = exp2.replace("-","");
	if ($.isNumeric(exp2)) {
		return ctrl_saisie_dimension_colonne(ctrlSaisie);
	} else {
		return ctrl_saisie_dimension_ligne(ctrlSaisie);
	}
}

function ctrl_saisie_dimension_ligne(ctrlSaisie) {
	var ctrlParts = ctrlSaisie[4].split(";");
	var expG = ctrlParts[0];
	var expD = ctrlParts[1];
	var listCode = ctrlParts[2].split(',');
	
	var ctrlSaisieLign = new Array();
	ctrlSaisieLign[0] = "";
	ctrlSaisieLign[1] = "";
	ctrlSaisieLign[2] = "";
	ctrlSaisieLign[3] = ctrlSaisie[3];
	ctrlSaisieLign[4] = ctrlParts[0]+";"+ctrlParts[1];
	ctrlSaisieLign[5] = ctrlSaisie[5];
	$.each(listCode, function(idx, code) {
		if (!ctrl_saisie_text(ctrlSaisieLign, "_"+code)) {
			return false;	
		}
	});
	return do_submit;
}

function ctrl_saisie_dimension_colonne(ctrlSaisie) {
	var ctrlParts = ctrlSaisie[4].split(";");
	if (ctrlParts.length > 3) {
		var colNums = ctrlParts[3].split(',');
		$.each(colNums, function(idx, colNum) {
			if (!ctrl_saisie_dimension_colonne_1(ctrlSaisie, '_'+colNum)) {
				return false;	
			}						  
		});
	} else {
		return ctrl_saisie_dimension_colonne_1(ctrlSaisie, '');
	}
}

function ctrl_saisie_dimension_colonne_1(ctrlSaisie, colNum) {
	var ctrlParts = ctrlSaisie[4].split(";");
	var champs = ctrlParts[0].split(",");
	var valNomenG = ctrlParts[1];
	var valNomenD = ctrlParts[2];
	$.each(champs, function(idx, champ) {
		var zonesAddExpr = valNomenG.split("+");
		var valg = 0;
		$.each(zonesAddExpr, function(idx, zoneAddExpr) {
			if (zoneAddExpr.indexOf("-") > -1) {
				var zonesMinExpr = zoneAddExpr.split("-");
				var valdMin = $("#"+champ+"_0_"+zonesMinExpr[0]+colNum).val();
				for (var i = 1; i < zonesMinExpr.length; i++) {
					valdMin -= $("#"+champ+"_0_"+zonesMinExpr[i]+colNum).val() * 1; 
				}
				valg += valdMin * 1;
			} else {
				valg += $("#"+champ+"_0_"+zoneAddExpr+colNum).val() * 1;
			}
		});
		
		zonesAddExpr = valNomenD.split("+");
		var vald = 0;
		$.each(zonesAddExpr, function(idx, zoneAddExpr) {
			if (zoneAddExpr.indexOf("-") > -1) {
				var zonesMinExpr = zoneAddExpr.split("-");
				var valdMin = $("#"+champ+"_0_"+zonesMinExpr[0]+colNum).val();
				for (var i = 1; i < zonesMinExpr.length; i++) {
					valdMin -= $("#"+champ+"_0_"+zonesMinExpr[i]+colNum).val() * 1; 
				}
				vald += valdMin * 1;
			} else {
				vald += $("#"+champ+"_0_"+zoneAddExpr+colNum).val() * 1;
			}
		});
		eval("do_submit = valg "+ctrlSaisie[3]+" vald");
	
		if (!do_submit) {
			alert(ctrlSaisie[5]+': ('+valg+') '+ctrlSaisie[3]+' ('+vald+') !');
			return false;
		}
	});
	return do_submit;
}