// JavaScript Document
/*(function( $, undefined ) {
	//special click handling to make widget work remove after nav changes in 1.4
	var href,
		ele = "";
	$( document ).on( "click", "a", function( e ) {
		href = $( this ).attr( "href" );
		var hash = $.mobile.path.parseUrl( href );
		if( typeof href !== "undefined" && hash !== "" && href !== href.replace( hash,"" ) && hash.search( "/" ) !== -1 ){
			//remove the hash from the link to allow normal loading of the page.
			var newHref = href.replace( hash,"" );
			$( this ).attr( "href", newHref );
		}
		ele = $( this );
	});
	$( document ).on( "pagebeforechange", function( e, f ){
			f.originalHref = href;
	});
	$( document ).on("pagebeforechange", function( e,f ){
		var hash = $.mobile.path.parseUrl(f.toPage).hash,
			hashEl, hashElInPage;

		try {
			hashEl = $( hash );
		} catch( e ) {
			hashEl = $();
		}

		try {
			hashElInPage = $( ".ui-page-active " + hash );
		} catch( e ) {
			hashElInPage = $();
		}

		if( typeof hash !== "undefined" &&
			hash.search( "/" ) === -1 &&
			hash !== "" &&
			hashEl.length > 0 &&
			!hashEl.hasClass( "ui-page" ) &&
			!hashEl.hasClass( "ui-popup" ) &&
			hashEl.data('role') !== "page" &&
			!hashElInPage.hasClass( "ui-panel" ) &&
			!hashElInPage.hasClass( "ui-popup" ) ) {
			//scroll to the id
			var pos = hashEl.offset().top;
			$.mobile.silentScroll( pos );
			$.mobile.navigate( hash, '', true );
		} else if( typeof f.toPage !== "object" &&
			hash !== "" &&
			$.mobile.path.parseUrl( href ).hash !== "" &&
			!hashEl.hasClass( "ui-page" ) && hashEl.attr('data-role') !== "page" &&
			!hashElInPage.hasClass( "ui-panel" ) &&
			!hashElInPage.hasClass( "ui-popup" ) ) {
			$( ele ).attr( "href", href );
			$.mobile.document.one( "pagechange", function() {
				if( typeof hash !== "undefined" &&
					hash.search( "/" ) === -1 &&
					hash !== "" &&
					hashEl.length > 0 &&
					hashElInPage.length > 0 &&
					!hashEl.hasClass( "ui-page" ) &&
					hashEl.data('role') !== "page" &&
					!hashElInPage.hasClass( "ui-panel" ) &&
					!hashElInPage.hasClass( "ui-popup" ) ) {
					hash = $.mobile.path.parseUrl( href ).hash;
					var pos = hashElInPage.offset().top;
					$.mobile.silentScroll( pos );
				}
			} );
		}
	});			
})( jQuery );*/

// Wait for device API libraries to load
//
document.addEventListener("deviceready", onDeviceReady, false);

// device APIs are available
//
function onDeviceReady() {
	// Empty
}

//
function showAlert(title, message, callback) {
	if (navigator && navigator.notification) {
		navigator.notification.alert(
			message,  // message
			callback,         // callback
			title,            // title
			'OK'                  // buttonName
		);
	} else {
		alert(message);
		if (callback != null) {
			callback();
		}
	}
}
//
function showCustumConfirm(title, message, responses, callback) {
	if (navigator && navigator.notification) {
		window.navigator.notification.confirm(
			message,  // message
			callback,              // callback to invoke with index of button pressed
			title,            // title
			responses         // buttonLabels
		);
	} else {
		if (confirm(message)) {
			callback(2);
		}
	}
}
function showConfirm(title, message, callback) {
	if (navigator && navigator.notification) {
		window.navigator.notification.confirm(
			message,  // message
			callback,              // callback to invoke with index of button pressed
			title,            // title
			'Annuler,OK'         // buttonLabels
		);
	} else {
		if (confirm(message)) {
			callback(2);
		}
	}
}
function exitFromApp(buttonIndex) {
	if (buttonIndex == 2) {
		window.navigator.app.exitApp();
	}
}
window.alert = function(message) {
	showAlert('StatEduc', message, null);
};


// Extention
$.extend({
	getUrlVars: function() {
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for (var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	},
	getUrlVar: function(name) {
		return $.getUrlVars()[name];
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
jQuery.fn.exists = function(){return jQuery(this).length>0;}
jQuery.fn.pushEach = function( arrayOfWrappers ) {
 
// Map the array of jQuery objects to an array of
// raw DOM nodes.
var rawArray = jQuery.map(
	arrayOfWrappers,
	function( value, index ){

		// Return the unwrapped version. This will return
		// the underlying DOM nodes contained within each
		// jQuery value.
		return( value );

	}
);

// Add the raw DOM array to the current collection.
this.push( rawArray );

// Return this reference to maintain method chaining.
return( this );

};

if (!String.prototype.startsWith) {
	Object.defineProperty(String.prototype, 'startsWith', {
			enumerable: false,
			configurable: false,
			writable: false,
			value: function (searchString, position) {
			position = position || 0;
			return this.lastIndexOf(searchString, position) === position;
		}
	});
}

// Initialise les éléments de connexion
function init_connexion(page_id, userConnectCallback) {
	
	$( document ).on( "click", page_id + " .stm_btn_user_lnk", function(event) {
		event.preventDefault(); 
		if (!stmDevice.isOnline()) {
			return;
		}
		var currUser = JSON.parse(sessionStorage.getItem('stm_userData'));
		var firstName = '';
		var lastName = '';
		if (currUser != null) {
			firstName = currUser.firstname;
			lastName = currUser.lastname;
		}
		var popupConnexion = '<div class="stm_form_user_connexion ui-content" data-role="popup">'+
				'<form action="/" method="post" class="ui-corner-all user_is_logout" data-ajax="false">'+
					'<div id="connexion_frm_error"></div>'+
					'<label for="login">Identifiant :</label>'+
					'<input type="text" name="login" id="login_con" value="" placeholder="login"/>'+			
					'<label for="pwd">Mot de passe :</label>'+
					'<input type="password" name="pwd" id="pwd_con" value="" placeholder="mot de passe"/>'+
					'<fieldset class="ui-grid-a">'+
						'<div class="ui-block-a"><a href="#" data-role="button" data-direction="reverse" data-rel="back" data-theme="c">Annuler</a></div>'+
						'<div class="ui-block-b"><button type="submit" data-theme="b">OK</button></div>'+
					'</fieldset>'+
					'<!--div class="pwd_login_change">'+
						'<a href="javascript:void(0);" onClick="javascript:display_pwd_frm();" lang="en">Mot de passe oublié?</a>'+
					'</div-->'+
				'</form>'+
				'<div class="user_is_login">'+
					'<div class="user_nom">'+ firstName +'</div>'+
					'<div class="user_prenom">('+ lastName +')</div>'+
					'<p><a href="#" class="stm_btn_user_logout" data-role="button" data-theme="c">D&eacute;connexion</a></p>'+
				'</div>'+
			'</div>';
		$.mobile.activePage.append( popupConnexion ).trigger( "create" );
		$(".stm_form_user_connexion").popup( "open", { x: event.pageX, y: event.pageY });
		
		$(".stm_form_user_connexion").css({position:'fixed',top:'43px',right:'2.6em'});
		if (stmUser.isConnect()) {
			$(".stm_form_user_connexion .user_is_login").css('display', 'block');
			$(page_id + " .stm_form_user_connexion .user_is_logout").css('display', 'none');
		} else {
			$(".stm_form_user_connexion .user_is_login").css('display', 'none');
			$(".stm_form_user_connexion .user_is_logout").css('display', 'block');
		}		
		$(".stm_form_user_connexion").on( "popupafterclose", function( event, ui ) {
			if (userConnectCallback != null) {
				userConnectCallback();
			}
			$(this).remove();
		});	
	});	
	
	$(document).on('submit',  page_id + " .user_is_logout", function(event) {
		event.preventDefault();		
		var login = $('#login_con').val();
		var pwd = $('#pwd_con').val();
		stmUser.connect(login, pwd, page_id);
	});
	
	$( document ).on( "click", page_id + " .stm_btn_user_logout", function(event) {
		event.preventDefault();									
		stmUser.deconnect(page_id);
	});
}

// Initialise les options
function init_options(page_id) {
	$( document ).on( "click", page_id + " .stm_btn_options_lnk", function(event) {
		event.preventDefault();
		var popupOptions = '<div class="stm_form_options ui-corner-none" data-role="popup">'+
				'<ul data-role="listview" data-inset="true">';
		if (page_id != "#p_new_camp" && page_id != "#p_accueil" && page_id != "#p_etab" && page_id != "#p_settings" && page_id != "#p_acc_settings") {
			popupOptions += '<li data-icon="false"><a href="new_camp.html" data-ajax="false" class="btn_new_camp">Charger campagne</a></li>';
		}
		if (page_id != "#p_accueil" && page_id != "#p_etab" && page_id != "#p_settings" && page_id != "#p_acc_settings") {
			popupOptions += '<li data-icon="false"><a href="settings.html" data-ajax="false">Param&egrave;tres</a></li>';
		}
		popupOptions +=	'<li data-icon="false"><a href="#">A propos</a></li>';
		//'<!--li data-icon="false"><a href="#">Help</a></li-->'+
		if (page_id != "#p_accueil") {
			popupOptions += '<li data-icon="false"><a href="#" data-ajax="false" id="stm_exit_app">Quitter</a></li>';
		}
		popupOptions += '</ul>'+
					'</div>'; //alert(event.pageX + '  ' + event.pageY)
		$.mobile.activePage.append( popupOptions ).trigger( "create" );
		$(".stm_form_options").popup( "open", { x: event.pageX, y: event.pageY } );
		$(".stm_form_options").css({position:'fixed',top:'43px',right:'.2em'});
		$(".stm_form_options").on( "popupafterclose", function( event, ui ) {
			$(this).remove();
		});	
		$("#stm_exit_app").on( "click", 
			function() {				
				showConfirm('StatEduc', 'Voulez vous vraiment quitter?', exitFromApp);
		});
	});	
}

// Affiche le formulaire de connexion ou de deconnexion
function display_user_con_form(page_id) {
	if (existStorage('sessionStorage')) {
		$.mobile.loading( "show" );
		var user = sessionStorage.getItem('stm_userData'); //alert(user);
		var urlServer = localStorage.getItem('stm_UrlServer');
		var mdp = sessionStorage.getItem("stm_userPass");			
		if (!user) {
			user_is_logout(page_id);
		} else { 
			user = JSON.parse(user);
			$.ajax({type:'get', url: urlServer +'/user_ident.php/user_test_login/' , data: '', 
				statusCode: {
					401: function() {
						window.sessionStorage.removeItem("stm_userData");
						user_is_logout(page_id);
					},
					404: function() {
						window.sessionStorage.removeItem("stm_userData");
						user_is_logout(page_id);
					}
				},
         		headers: {	'Authorization': 'Basic ' + btoa(user.login+':'+mdp)	},
				success: function(response) {
					if (response.se_status == 200) {
						user_is_login(page_id);
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					if (textStatus==="timeout") {
						window.sessionStorage.removeItem("stm_userData");
						user_is_logout(page_id);
					}
				},  
				CORS: true,
				secure: true,
				dataType:'json',
				timeout: 10000
			});
		}
	}
}

// L'utilisateur vient de se connecter au serveur
function user_is_login(page_id) {
	//$(page_id + " .stm_form_user_connexion").popup( "close" );
	$(page_id + " .stm_btn_user").attr("src", "img/user_login.png");
	$(page_id + " .stm_form_user_connexion").popup( "close" );
	$.mobile.loading( "hide" );
}


// L'utilisateur s'est déconnecté
function user_is_logout(page_id) {
	//$(page_id + " .stm_form_user_connexion").popup( "close" );
	$(page_id + " .stm_btn_user").attr("src", "img/user_logout.png");
	$(page_id + " .stm_form_user_connexion").popup( "close" );
	$.mobile.loading( "hide" );
}



/// FONCTIONS USUELLES

function change_page(page_path) {
	$.mobile.pageContainer.pagecontainer( "change", page_path, {});
}


function postFunc(serv_url, params, callback, serv_timeout) {
	if (!serv_timeout) {
		serv_timeout = 10000;	
	}
	$.ajax({type:'post', url: serv_url, data:params, 
		success: function(response) {		
			if (response.stm_statut == 101) {
				alert(response.stm_message);
			} else if (response.se_statut == 200) {
				//console.log(response.se_data);
				if (response.stm_data.length > 0) {
					var callbackFunc = callback.bind(callback, response.stm_data);
					callbackFunc();
				} else if (callback != null) {
					callback();
				}
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert('ERROR : ' + textStatus + '-' + errorThrown); 
		},  
		CORS: true,
		secure: true,
		dataType:'json',
		timeout: serv_timeout
	});
}

// Test si le support de sauvegarde existe
function existStorage(type) {
	return ((type in window) && window[type] !== null);
}

function displayDate(format, date, locale) {
	if (locale == null) {
		locale = "fr";
	}
	return $.datepicker.formatDate( format, date);
	/*$.datepicker.formatDate( format, date, {
		dayNamesShort: $.datepicker.regional[ locale ].dayNamesShort,
		dayNames: $.datepicker.regional[ locale ].dayNames,
		monthNamesShort: $.datepicker.regional[ locale ].monthNamesShort,
		monthNames: $.datepicker.regional[ locale ].monthNames
	});*/
}

function displayPopup(message, timeout, page_id) {
	if (timeout == null) {
		timeout = 3000;	
	}
	var popupId = '#popup' + page_id;
	$.dynamic_popup({
		content: '<p>' + message + '</p>',
		'data-transition': 'slidedown'
	})
	.bind({
		popupafteropen: function(e){
			setTimeout(function() {
				//$( popupId ).popup( "close" ) 
			}, timeout );
		}
	});
}

var stmDevice = {
	
	// Test la connexion internet
	isOnline : function() {return true;
		if ( !window.navigator.network ) {
			 // set the parent windows navigator network object to the child window
			 window.navigator.network = window.top.navigator.network;
		 }

		// return the type of connection found
	   	if ( window.navigator.network.connection.type === Connection.NONE || window.navigator.network.connection.type === null || 
			  window.navigator.network.connection.type === Connection.UNKNOWN ) {
			//displayPopup('Check your internet connection.', 3000 );
			alert('Vérifier votre connexion internet.');
			return false;
		} else {
			return true;  
		}
	},
};

function randomString(string_length) {
	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var randomstring = '';
	for (var i=0; i<string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		randomstring += chars.substring(rnum,rnum+1);
	}
	return randomstring;
}

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
		alert(entitiesDecode(ctrlSaisie[5])+': ('+valg+') '+ctrlSaisie[3]+' ('+vald+') !!');
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
			alert(entitiesDecode(ctrlSaisie[5])+': ('+valg+') '+ctrlSaisie[3]+' ('+vald+') !!');
			return false;
		}
	});
	return do_submit;
}

function entitiesDecode(str) {
	var entities = new Array("&quot;", "&apos;", "&amp;", "&lt;", "&gt;", "&nbsp;", "&iexcl;", "&cent;", "&pound;", "&curren;", "&yen;", "&brvbar;", "&sect;", "&uml;", "&copy;", "&ordf;", "&laquo;", "&not;", "&shy;", "&reg;", "&macr;", "&deg;", "&plusmn;", "&sup2;", "&sup3;", "&acute;", "&micro;", "&para;", "&middot;", "&cedil;", "&sup1;", "&ordm;", "&raquo;", "&frac14;", "&frac12;", "&frac34;", "&iquest;", "&times;", "&divide;", "&Agrave;", "&Aacute;", "&Acirc;", "&Atilde;", "&Auml;", "&Aring;", "&AElig;", "&Ccedil;", "&Egrave;", "&Eacute;", "&Ecirc;", "&Euml;", "&Igrave;", "&Iacute;", "&Icirc;", "&Iuml;", "&ETH;", "&Ntilde;", "&Ograve;", "&Oacute;", "&Ocirc;", "&Otilde;", "&Ouml;", "&Oslash;", "&Ugrave;", "&Uacute;", "&Ucirc;", "&Uuml;", "&Yacute;", "&THORN;", "&szlig;", "&agrave;", "&aacute;", "&acirc;", "&atilde;", "&auml;", "&aring;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&igrave;", "&iacute;", "&icirc;", "&iuml;", "&eth;", "&ntilde;", "&ograve;", "&oacute;", "&ocirc;", "&otilde;", "&ouml;", "&oslash;", "&ugrave;", "&uacute;", "&ucirc;", "&uuml;", "&yacute;", "&thorn;", "&yuml;");

	var UCchar = new Array("\"", "'", "&", "<", ">", "", "¡", "¢", "£", "¤", "¥", "¦", "§", "¨", "©", "ª", "«", "¬", "", "®", "¯", "°", "±", "²", "³", "´", "µ", "¶", "·", "¸", "¹", "º", "»", "¼", "½", "¾", "¿", "×", "÷", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï", "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "Þ", "ß", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "þ", "ÿ");
	
	var tempString = str;
	
	var existAmp = false;
	// convert all HTML entities into Unicodes 
	for (var i=0; i<entities.length; i++) {
		var ent = "/"+entities[i]+"/g";
		if (entities[i] == '&amp') {
			existAmp = true;
		}
		tempString = tempString.replace(eval(ent), UCchar[i]);
	}
	
	for (var i=0; i<entities.length; i++) {
		var ent = "/"+entities[i]+"/g";
		tempString = tempString.replace(eval(ent), UCchar[i]);
	}
	
	return tempString;
}