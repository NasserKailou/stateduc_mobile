// JavaScript Document
var firstDisplay = true;
var currQstNum = 1;
var currNumPage = 1;
var totalPages = 0;
var currSmtQst = null;
var containFilter = false;
var currFilter = null;
var saveQstTab = new Array();

//Page etab init
$( document ).one( "pagecreate", "#p_etab", function( event ) {
	$('.stm_back_on_logo_lnk').bind('click', function() {
		//window.history.go(-1);											  	
  	});
	init_connexion('#p_etab');
	init_options('#p_etab');
});

var stmPageEtab = {
	
	etab : null,
	currSys : null,
	
	setSys : function(sysId) {
		this.currSys = stmSystems.getById(sysId);
	},
	
	// Charge les données de l'établissement
	chargeData : function() {
		this.etab = stmEtabs.getById(currentEtabId);
		stmYear.init();
		stmFilter.init();
		//this.etab.loadCollectData();
	},
	
	resetEtabIdent : function() {
		$("#stm_etab_ident").empty();
		$('.part_title').empty();
	},
	
	// Affiche les informations sur un établissement
	displayEtabIdent : function(qstNum) {		
		this.resetEtabIdent();
		// Identifiant de l'etablissement
		var eCode = this.etab.code==null ? '' : this.etab.code;
		var year = stmYear.getById(localStorage.getItem('stm_CurrYear'));
		var htmlData = '<div data-role="collapsible" class="div_collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u" data-collapsed="false">'
							+'<h3>'+ this.etab.nom +'</h3>'
							+'<div>'
								+'<div id="stm_etab_year" class="entry"><span class="label">Années en cours: </span><span class="value">'+ year.getNom() +'</span></div>'
								+'<div id="stm_etab_loc" class="entry"></div>'
								+'<div id="stm_etab_reg_no" class="entry"><span class="label">Admin. code: </span><span class="value">'+ eCode +'</span></div>'
								/*+'<div id="stm_etab_reg_no" class="entry"><span class="label">Status: </span><span class="value">'+ stmStatus.getById(this.etab.status).getName() +'</span></div>'*/
								+'<div id="stm_system" class="entry"><span class="label">Secteur: </span><span class="value">'+stmPageEtab.currSys.nom+'</span></div>'								
								/*+'<div id="stm_etab_adr" class="entry"><span class="label">Address P.O. Box: </span><span class="value">'+ this.etab.adr +'</span></div>'
								+'<div id="stm_etab_tel" class="entry"><span class="label">Tel: </span><span class="value">'+ this.etab.tel +'</span></div>'
								+'<div id="stm_etab_email" class="entry"><span class="label">Email: </span><span class="value">'+ this.etab.email +'</span></div>'*/
								+'<div data-role="controlgroup" data-type="horizontal" data-mini="true">'
									+'<a href="#" id="stm_save_theme_data" class="ui-shadow ui-btn ui-corner-all ui-btn-icon-left ui-icon-action ui-btn-a">Envoyer</a>'
									+'<!--a href="#" id="stm_sms_theme_data" class="ui-shadow ui-btn ui-corner-all ui-btn-icon-left ui-icon-action ui-btn-a ui-mini ui-btn-inline">Sms</a-->'
									+'<!--a href="#" id="stm_delete_theme_data" class="ui-shadow ui-btn ui-corner-all ui-btn-icon-left ui-icon-delete ui-btn-a">Supprimer</a-->'
									+'<a href="#" id="stm_reload_theme_data" class="ui-shadow ui-btn ui-corner-all ui-btn-icon-left ui-icon-refresh ui-btn-a">Recharger</a>'
								+'</div>'
								+'<div id="stm_sms_log"></div>'
								+'<!--div>'
								+'</div-->'
								+'<div id="save_log" class="ui-corner-all">'
								+'</div>'
							+'</div>'
						+'</div>';
		$('#stm_etab_ident').append(htmlData);
		$('#stm_etab_ident').trigger( "create" );
		stmRegroups.setEtabLocs(this.etab.idRegroup, 0, "stm_etab_loc");
		// Liste des questionnaires
		htmlData = '<select id="questions_lst" data-native-menu="false" data-mini="true">'
					+'<option>Choisir</option>';
		$.each(stmCamp.getQsts(), function(index, value){
			htmlData += '<option value="'+ value.getId() +'"';
			if (index == 0) {
				htmlData += ' selected="selected"';
			}
			htmlData +='>'+ value.getTitle() +'</option>';							   
		});
		htmlData += '</select>';
		$('.part_title').append(htmlData);
		
		var curr = this;
		var previousQst = $('#questions_lst').val();
		$('#questions_lst').change(function() {
			$.mobile.loading( 'show' );	
			var currQst = this;										  
			setTimeout(function() {
				if (previousQst != $(currQst).val()) {				
					if ($('.survey_page form').hasClass('dirty')) {
						if (!confirm('Modifications non sauvegardées! \n Continuer?')) {
							var prevOption = $('#questions_lst option[value='+previousQst+']');
							$(prevOption).prop('selected', true);
							$("#questions_lst").trigger("change");
							$.mobile.loading( 'hide' );	
							return true;
						} else {
							previousQst = $(currQst).val();
						}
					} else {
						previousQst = $(currQst).val();	
					}
					var qstId = $(currQst).val();
					setTimeout(function() {
						curr.initQuestion(qstId) 
					}, 200 );
				} else {	
					$.mobile.loading( 'hide' );	
				}
			}, 200);
		});
		
		if (!firstDisplay) {
			$('.part_title').trigger( "create" );
		} else {
			this.activeButtons();
		}
		this.initQuestion(stmCamp.getQsts()[0].getId());
		firstDisplay = false;
		stmChargeCamp.initVars();
		$('#stm_save_theme_data').on('click', function(event) {	
			var hasError = curr.savePage(false);										   
			if (hasError || !stmDevice.isOnline()) {
				return false;
			} else {
				if (!stmUser.isConnect()) {
					alert('Vous devez être connecté');
					return false;					
				} else {
					showCustumConfirm('StatEduc', 'Choisir un écran de saisi à sauvegarder!', 'Annuler,Courant,Tout,SMS', stmPageEtab.saveConfirmCallback);				
				}
			}
		});
		
		$('#stm_reload_theme_data').on('click', function(event) {
			if (!stmDevice.isOnline()) {
				alert('Vérifiez votre connexion internet');
				return false;
			} else {
				if (!stmUser.isConnect()) {
					alert('Vous devez être connecté');
					return false;					
				} else {
					showCustumConfirm('StatEduc', 'Choisir un écran de saisi à recharger!', 'Annuler,Courant,Tout', stmPageEtab.reloadConfirmCallback);					
				}
			}
		});	
		
		$('#stm_sms_theme_data').on('click', function(event) {	
			var hasError = curr.savePage(false);										   
			if (hasError) {
				return false;
			} 
			showConfirm('StatEduc', 'Envoyer sur le serveur par sms?', function(response) {
				if (response == 2) {
					stmPageEtab.sendSmsQstOnServer(currSmtQst.getId(), currSmtQst.getTitle());
				}
			});
		});			
		
		$('#stm_delete_theme_data').on('click', function(event) {
			showCustumConfirm('StatEduc', 'Choisir un écran de saisie à supprimer!', 'Annuler,Courant,Tous', stmPageEtab.deleteConfirmCallback);
		});
		
		if (qstNum) {
			$('#questions_lst option[value='+qstNum+']').prop('selected', true);
			$("#questions_lst").trigger("change");	
		}
	},
	
	reloadConfirmCallback : function(opt) {
		//	
		if (opt == 2) {
			$.mobile.loading( 'show' );
			$('#save_log').empty();
			$('#save_log').addClass('save_log');	
			saveQstTab = new Array();	
			saveQstTab['tot'] = 1;
			saveQstTab['_'+currSmtQst.getId()] = false;
			stmPageEtab.reloadQstFromServer(currentEtabId, currSmtQst.getId(), currSmtQst.getTitle(), true, false);	
		} else if (opt == 3) {
			$('#save_log').empty();		
			//$.mobile.loading( 'show' );	
			saveQstTab = new Array();
			stmPageEtab.reloadAllQstsFromServer(currentEtabId, false);
		} else {
			$.mobile.loading( 'hide' );		
		}
	},
	
	reloadFirstQstsFromServer : function(idEtab, fromCampPage) {
		var qsts = stmCamp.getQsts();
		saveQstTab['tot'] = 1;	
		var qst = qsts[0];
		var idQst = qst.getId();
		var isLast = true;
		saveQstTab['_'+idQst] = false;
		stmPageEtab.reloadQstFromServer(idEtab, idQst, qst.getTitle(), isLast, fromCampPage);
	},
	
	reloadAllQstsFromServer : function(idEtab, fromCampPage) {
		var qsts = stmCamp.getQsts();
		var nbQst = stmCamp.getTotalQst() - 1;
		saveQstTab['tot'] = nbQst + 1;
		$.each(qsts, function(index, qst) {
			//$.mobile.loading( 'show' );	
			var idQst = qst.getId();
			var isLast = false;
			if (index == nbQst) {
				isLast = true;
			}
			saveQstTab['_'+idQst] = false;
			stmPageEtab.reloadQstFromServer(idEtab, idQst, qst.getTitle(), isLast, fromCampPage);
		});
		//recharge les données du thème en cours
		if (!fromCampPage) {
			var currFilter = null;
			if (currSmtQst.isFiltered()) {
				currFilter = localStorage.getItem('stm_CurrFilter');
			}
			this.etab.loadCollectData(currSmtQst.getId(), currFilter);
		}
	},
	
	reloadQstFromServer : function(idEtab, idQst, libQst, isLast, fromCampPage) {
		if (!fromCampPage) {
			$('#save_log').append('<div id="save_log_'+idQst+'">'+libQst+'... </div>');
		}
		var currQst = stmCamp.getQst(idQst);
		if (currQst.isFiltered()) {
			var nb = stmFilter.chargedFilters.length - 1;
			var isEmpty = "";
			$.each(stmFilter.chargedFilters, function(index, value) {	
				if (index == nb) {
					stmPageEtab.reloadOneQstFromServer(idEtab, idQst, libQst, isLast, fromCampPage, value.getId(), true);
				} else {
					stmPageEtab.reloadOneQstFromServer(idEtab, idQst, libQst, false, fromCampPage, value.getId(), false);
				}
			});	
			if (isEmpty.length == 0) {
				//$('#save_log_'+idQst+'').append('OK');
			}
		} else {								  
			stmPageEtab.reloadOneQstFromServer(idEtab, idQst, libQst, isLast, fromCampPage, null, false);	
		}
	},
	
	reloadOneQstFromServer : function(idEtab, idQst, libQst, isLast, fromCampPage, filter, isFilterLast) {
		
		//$.mobile.loading( 'show' );
		
		var currEtab = this.etab;
		/*var error = false;
		var data_lnk = urlServer + '/data_reload.php/theme_data/' + stmChargeCamp.currUser.login +'/'+ stmPageEtab.currSys.id +'/'+ idQst +'/'+ stmCamp.id +'/'+ idEtab +'/'+ filter;*/
		
		if (fromCampPage) {				
			$('#save_log_'+idEtab).append('<span id="save_log_'+idEtab+'_'+idQst+'">/'+idQst+'.../</span> ');	
		}					  
		
		var funcCallback = this.reloadDataCallback.bind(this.reloadDataCallback, currEtab, idEtab, idQst, libQst, isLast, fromCampPage, filter, isFilterLast);
		
		getFormDataFromServer('/data_reload.php', '/theme_data/' + stmChargeCamp.currUser.login +'/'+ stmPageEtab.currSys.id +'/'+ idQst +'/'+ stmCamp.id +'/'+ idEtab +'/'+ filter, idQst, isLast, fromCampPage, idEtab, filter, isFilterLast, funcCallback);
		
		/*$.get( data_lnk, function(formData) {
			$.mobile.loading( 'show' );
			var formDataTab = JSON.parse(formData);	
			if (formDataTab.se_status == 400) {
				$.mobile.loading( 'hide' );
				saveQstTab['_'+idQst] = true;
				if (!fromCampPage) {
					$('#save_log_'+idQst+'').append('KO SERVER');
					alert(formDataTab.se_data);
				} else if (fromCampPage && isFilterLast) {
					$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#p_etab" onClick="javascript:stmPageCamp.goToQst('+idEtab+', '+idQst+')" class="log_link camp_save_ko">' + idQst + ':KO SERVER</a>/ ');
				}
			} else {
				
			}
			$.mobile.loading( 'hide' );
		});	
		return error;*/
	},
	
	reloadDataCallback : function(currEtab, idEtab, idQst, libQst, isLast, fromCampPage, filter, isFilterLast, formData) {
		var formDataTab = JSON.parse(formData);	
		currEtab.deleteQstData(idQst);
		$.each(formDataTab, function(key, formElt) {
			var tagName = key;				
			var type = formElt[1];
			var value = formElt[0];
			if (type == "radio") {
				value = 1;
				tagName += "#" + formElt[0];
			}
			if (!key.startsWith('DELETE_')) {
				var stmData = new StmCollectData(idQst, tagName, value, type);
				currEtab.addData(stmData);
			}		
		});	
		if (filter == null || isFilterLast) {
			saveQstTab['_'+idQst] = true;
		}
		if (formDataTab.length == 0) { //no data on server
			if (stmPageEtab.allIsSend()) {
				//$.mobile.loading( 'hide' );
			}			
			if (!fromCampPage) {
				if (filter == null || isFilterLast) {
					$('#save_log_'+idQst+'').append('ND');
				}
			} else if (filter == null || isFilterLast) {
				//$.mobile.loading( 'hide' );
				$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#p_etab" onClick="javascript:stmPageCamp.goToQst('+idEtab+', '+idQst+')" class="log_link camp_save_nd">' + idQst + ':ND</a>/ ');
			}
		} else {
			currEtab.saveData(idQst, false, filter);
			
			var saveStatus = 'OK';
			var camp_save_css_class = 'camp_save_ok';
			if (!fromCampPage && filter != null && !$('#save_log_'+idQst+':contains("'+saveStatus+'")').exists()) {
				$('#save_log_'+idQst+'').append(saveStatus);	
			} else if (filter == null) {
				if (!fromCampPage) {
					$('#save_log_'+idQst+'').append(saveStatus);	
				} else {
					$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#p_etab" onClick="javascript:stmPageCamp.goToQst('+idEtab+', '+idQst+')" class="log_link '+camp_save_css_class+'">' + idQst + ':'+saveStatus+'</a>/ ');	
				}
			} else if (fromCampPage && isFilterLast) {
					$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#p_etab" onClick="javascript:stmPageCamp.goToQst('+idEtab+', '+idQst+')" class="log_link '+camp_save_css_class+'">' + idQst + ':'+saveStatus+'</a>/ ');
			}						
			if (!fromCampPage && currSmtQst.getId() == idQst) {
				var isGrille = $('.survey_page .grille_ligne').length > 0;
				if (isGrille) {
					stmPageEtab.initHtml(idQst);
				} else {
					stmPageEtab.initPageData(idQst, "");
				}
				setTimeout(function() {
					stmPageEtab.initQuestion(idQst) 
				}, 100 );
			}
		}
		/*if (stmPageEtab.allIsSend()) {
			
			if (fromCampPage) {
				
			}
		}*/
		$.mobile.loading( 'hide' );
	},
	
	saveConfirmCallback : function(opt) {
		$.mobile.loading( 'show' );	
		if (opt == 2) {
			$('#save_log').empty();
			$('#save_log').addClass('save_log');	
			saveQstTab = new Array();	
			saveQstTab['tot'] = 1;
			saveQstTab['_'+currSmtQst.getId()] = false;
			stmPageEtab.saveQstOnServer(currentEtabId, currSmtQst.getId(), currSmtQst.getTitle(), true, false);	
		} else if (opt == 3) {
			$('#save_log').empty();		
			$.mobile.loading( 'show' );	
			saveQstTab = new Array();
			stmPageEtab.saveAllQstsOnServer(currentEtabId, false);
		} else {
			$.mobile.loading( 'hide' );		
		}
	},
	
	// Sauvegarde les données de tous les thèmes sur le serveur
	saveAllQstsOnServer : function(idEtab, fromCampPage) {
		var qsts = stmCamp.getQsts();
		var nbQst = stmCamp.getTotalQst() - 1;
		saveQstTab['tot'] = nbQst + 1;
		$.each(qsts, function(index, qst) {
			$.mobile.loading( 'show' );	
			var idQst = qst.getId();
			var isLast = false;
			if (index == nbQst) {
				isLast = true;
			}
			saveQstTab['_'+idQst] = false;
			stmPageEtab.saveQstOnServer(idEtab, idQst, qst.getTitle(), isLast, fromCampPage);
		});
		//recharge les données du thème en cours
		if (!fromCampPage) {
			var currFilter = null;
			if (currSmtQst.isFiltered()) {
				currFilter = localStorage.getItem('stm_CurrFilter');
			}
			this.etab.loadCollectData(currSmtQst.getId(), currFilter);
		}
	},
	
	// Sauvegarde les données d'un thème donné
	saveQstOnServer : function(idEtab, idQst, libQst, isLast, fromCampPage) {
		if (!fromCampPage) {
			$('#save_log').append('<div id="save_log_'+idQst+'">'+libQst+'... </div>');
		}
		var currQst = stmCamp.getQst(idQst);
		if (currQst.isFiltered()) {
			var nb = stmFilter.chargedFilters.length - 1;
			var isEmpty = "";
			$.each(stmFilter.chargedFilters, function(index, value) {												  
				var themeData = stmPageEtab.getPageDataToSend(idQst, value.getId());
				isEmpty += themeData;
				if (index == nb) {
					stmPageEtab.saveOneQstOnServer(idEtab, idQst, libQst, isLast, fromCampPage, themeData, value.getId(), true);
				} else {
					stmPageEtab.saveOneQstOnServer(idEtab, idQst, libQst, false, fromCampPage, themeData, value.getId(), false);
				}
			});	
			if (isEmpty.length == 0) {
				//$('#save_log_'+idQst+'').append('OK');
			}
		} else {
			var themeData = stmPageEtab.getPageDataToSend(idQst, null);							  
			stmPageEtab.saveOneQstOnServer(idEtab, idQst, libQst, isLast, fromCampPage, themeData, null, false);	
		}
	},
	
	allIsSend : function() {
		if (saveQstTab['tot'] == 0) {
			return false;
		}
		var nb = -1;
		for (var i in saveQstTab) {
			if (i != 'tot' && !saveQstTab[i]) {
				return false;
				break;
			}
			nb++;
		}
		if (saveQstTab['tot'] == nb) {
			saveQstTab['tot'] = 0;
			return true;
		}
		return false;
	},
	
	saveOneQstOnServer : function(idEtab, idQst, libQst, isLast, fromCampPage, themeData, filter, isFilterLast) {
		$.mobile.loading( 'show' );		
		if (fromCampPage) {				
			$('#save_log_'+idEtab).append('<span id="save_log_'+idEtab+'_'+idQst+'">/'+idQst+'.../</span> ');	
		}	
		if (themeData == '') {
			if (filter == null || isFilterLast) {
				saveQstTab['_'+idQst] = true;
			}
			if (stmPageEtab.allIsSend()) {
				$.mobile.loading( 'hide' );
			}			
			if (!fromCampPage) {
				if (filter == null || isFilterLast) {
					$('#save_log_'+idQst+'').append('ND');
				}
			} else if (filter == null || isFilterLast) {
				$.mobile.loading( 'hide' );
				$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#p_etab" onClick="javascript:stmPageCamp.goToQst('+idEtab+', '+idQst+')" class="log_link camp_save_nd">' + idQst + ':ND</a>/ ');
			}
			return true;
		}
		if (currNumPage == totalPages) {
			themeData += '&switch_theme_id=&save_and_prev=&save_and_next=';
		} else {
			themeData += '&switch_theme_id=&save_and_prev=0&save_and_next=0';
		}				  
		//var idEtab = stmPageEtab.etab.getId();
		postDataToServer('/data_save.php', '/theme_save/'+ stmChargeCamp.currUser.login +'/'+ stmCamp.id +'/'+ stmPageEtab.currSys.id +'/'+ idQst +'/'+ idEtab +'/'+ filter, themeData, idQst, isLast, fromCampPage, idEtab, filter, isFilterLast);
	},
	
	// Sauvegarde les données d'un thème via SMS
	sendSmsQstOnServer : function(idQst, libQst) {
		$('#stm_sms_log').empty();
		var filter = null;
		if ($('#filter_lst').exists()) {
			filter = $('#filter_lst').val();
		}
		var themeData = stmPageEtab.getPageDataToSend(idQst, filter);
		if (themeData == '') {
			$('#stm_sms_log').append('Aucune données à sauvegarder!');
			$.mobile.loading( 'hide' );			
			return false;
		} else {
			themeData = stmCamp.id +'/'+ stmPageEtab.currSys.id +'/'+ idQst +'/'+ currentEtabId +'/'+ filter +'/' + themeData;
		}
		var smsGatewayNum = localStorage.getItem('stm_SmsGatewayNum'),
		smsGatewayKey = localStorage.getItem('stm_SmsGatewayKey');
		
		if (!smsGatewayNum || !smsGatewayKey) {
			alert('Paramètres de la passerelle Sms absents. Veuillez vérifier votre paramétrage');
			return false;
		}
		
		var charLimit = 148,
		maxSplits = 15,
		placeholder,
		indicator,
		breakPoint,
		messages = [],
		n,
		m,
		splits;
		
		var totalLength = themeData.length + (9 * 5);

		// Use some obscure placeholder character that no one will actually type... (This part is a
		// little dangerous...
		if (totalLength <= (charLimit * 9)) {
			placeholder = '\v';
		}
		else {
			placeholder = '\v\v';
		}
		
		for (n = 0, m = 0; n < themeData.length / charLimit ; n++) {
			m = n * charLimit;
			// set the indicator so we can now how long it is
			if ((n + 1) >= (themeData.length / charLimit)) {
				indicator = '##';
			} else {
				indicator = '#'+n;
			}
			// set the breakpoint, taking indicator length into consideration
			breakPoint = m + charLimit - indicator.length;
			// insert the indicator into the correct spot
			themeData = themeData.substring(0, breakPoint) + indicator + themeData.substring(breakPoint);
		}
		
		// Replace the placeholder.
		themeData = themeData.replace(/\v+/g, n);
		
		splits = n;
		
		// Split the indicator-inserted message at every charLimit.
		for (n = 0; n < splits; n++) {
			m = n * charLimit;
			messages.push(themeData.substring(m, m + charLimit));
		}
		
		var messageInfo = {
			phoneNumber: smsGatewayNum,
			textMessage: ""
		};
		
		var smsBase = smsGatewayKey + ' ' + randomString(4) + '/';
		var	timeout = 500;

		// spit out each message
		$.each(messages, function(n, message) {
			setTimeout( function(){ 
				$('#stm_sms_log').append('<img src="img/b_p.png" id="sms_log_'+n+'" />');
				messageInfo.textMessage = smsBase + message;
				stmPageEtab.sendSms(messageInfo, n);
			}, timeout);
			timeout += 500;
		});
		$.mobile.loading( 'hide' );	
	},
	
	sendSms : function(messageInfo, n) {
		sms.sendMessage(messageInfo, function(message) {
			$('#sms_log_'+n).attr('src', 'img/b_ok.png');
			//alert("success: " + message);
		}, function(error) {
			$('#sms_log_'+n).attr('src', 'img/b_ko.png');
			alert("Erreur. Code: " + error.code + ", message: " + error.message);
		});
	},
	
	deleteConfirmCallback : function(opt) {
		$.mobile.loading( 'show' );	
		if (opt == 2) {		
			stmPageEtab.etab.deleteQstData(currSmtQst.getId());	
			$("#questions_lst").trigger("change");
			alert('Données supprimées');
		} else if (opt == 3) {
			var qsts = stmCamp.getQsts();
			$.each(qsts, function(index, qst) {
				var idQst = qst.getId();
				stmPageEtab.etab.deleteQstData(idQst);
			});
			var firstOpt = $('#questions_lst').find('option').eq(1);
			if ( firstOpt != null && $(firstOpt).attr('value') != null) {
				$(firstOpt).prop('selected', true);
				$("#questions_lst").trigger("change");
			}
			alert('Données supprimées');
		} else {
			$.mobile.loading( 'hide' );		
		}
	},
	
	// Initialize question form
	initQuestion : function(idQst) {
		currQstNum = idQst;
		currSmtQst = stmCamp.getQst(idQst);
		var filterName = localStorage.getItem('stm_FilterName');
		$('#page_filter').empty();
		currFilter = null;
		if (filterName != null && currSmtQst.isFiltered()) {
			currFilter = localStorage.getItem('stm_CurrFilter');
			var htmlData = '<select id="filter_lst" data-native-menu="false" data-mini="true">'
						+'<option>Choisir un filtre</option>';
			$.each(stmFilter.chargedFilters, function(index, value) {
				htmlData += '<option value="'+ value.getId() +'">'+ value.getNom() +'</option>';							   
			});
			htmlData += '</select>';
			$('#page_filter').append(htmlData);
			var curr = this;
			$('#filter_lst').change(function() {
				var currFil = this;						 
				setTimeout(function() {
					var val = $(currFil).val();	
					if (currFilter != val) {				
						if ($('.survey_page form').hasClass('dirty')) {
							if (confirm('Modifications non sauvegardées! Continuez?')) {
								localStorage.setItem('stm_CurrFilter', val);
								curr.etab.loadCollectData(idQst, val);
								currFilter = val;
								curr.initHtml(idQst);	
							} else {
								var prevOption = $('#filter_lst option[value='+currFilter+']');
								$(prevOption).prop('selected', true);
								$("#filter_lst").trigger("change");
								return false;	
							}
						} else {
							localStorage.setItem('stm_CurrFilter', val);
							curr.etab.loadCollectData(idQst, val);
							currFilter = val;
							curr.initHtml(idQst);	
						}
					}			
				}, 200);				
			});
			if (currFilter != null && currFilter > 0) {
				$('#filter_lst').find('option[value="'+currFilter+'"]').attr('selected', 'selected');
			} else {
				$('#filter_lst').find('option:eq(1)').attr('selected', 'selected');
				currFilter = stmFilter.chargedFilters[0].getId();
				localStorage.setItem('stm_CurrFilter', currFilter);
			}
			setTimeout(function() {
				$('#page_filter').trigger( "create" );
			}, 200);
		}
		this.etab.loadCollectData(idQst, currFilter);
		$('.page_num').empty();
		$('.page_num_nav').empty();
		$('.survey_page').empty();
		$('#stm_sms_log').empty();
		
		this.initHtml(idQst);
		$.mobile.loading( 'hide' );
	},
	
	// initialise le formulaire html d'une page
	initHtml : function(idQst) {		
		// Formulaire de collecte
		$('.survey_page').empty();
		var currQst = stmCamp.getQst(idQst);
		try {
			$('.survey_page').append(currQst.getHtml());
			$('.survey_page').find("td:contains('$NUMERO_')").html('');
		} catch (e) {
			alert('Erreur lors du chargement du formulaire html : ' + e.message);
		}
		$('.survey_page input:radio').attr('data-mini', 'true');
		this.highligthEvent();
		this.trGrilleLigneHighligthing();
		this.boutonDelClick($('.survey_page tr.data_line'));
		this.textGrilleLigneFocusOut();
		this.initPageData(idQst, currNumPage);
		$('.survey_page').trigger( "create" );		
		$('.survey_page input:text, .survey_page textarea').change();
		
		// Mise en place de la pagination
		totalPages = stmCamp.getTotalQst();
		$('.page_num').empty();
		currNumPage = $('#questions_lst option[value='+idQst+']').prevAll('option').length;
		$('.page_num').append(currNumPage + ' / ' + totalPages);
		this.textGrilleLigneClick();
		this.boutonAddClick();
		this.checkFormChanges();
		$.mobile.loading( 'hide' );
	},
	
	checkFormChanges : function() {
		$('.btn_save_form').addClass('ui-disabled');
		$('.survey_page form').areYouSure({'silent':true});
        // code below is optional to handle disabled state of the save button
        $('.survey_page form').bind('dirty.areYouSure', function () {
            // Enable save button only as the form is dirty.
            $('.btn_save_form').removeClass('ui-disabled');
        });
        $('.survey_page form').bind('clean.areYouSure', function () {
            // Form is clean so nothing to save - disable the save button.
            $('.btn_save_form').addClass('ui-disabled');
        });
	},
	
	// ajoute un ligne &agrave; une grille ligne pour recevoir des données sauvegardées
	addNextTr : function(input, lastTr, inc) {
		if ($(lastTr).find('input[name='+input+']').length == 0 && $(lastTr).find('input[name='+input+'_1]').length == 0 && $(lastTr).find('select[name='+input+']').length == 0) {
			var lastNum = ($(lastTr).find('.inc_number').html() * 1) - 1;
			var nextNum = lastNum + inc;
			var nextTr = this.createNextTr(lastTr, nextNum);
			if ($(nextTr).find('input[name='+input+']').length == 0 && $(nextTr).find('input[name='+input+'_1]').length == 0 && $(nextTr).find('select[name='+input+']').length == 0) {
				inc++;
				this.addNextTr(input, lastTr, inc);
			} else {
				this.addGrilleLine(lastTr, nextNum);	
			}
		}
	},
	
	// renvoie le dernier TR dans une grille ligne
	findLastTr : function() {
		return $('.grille_ligne #b_add').parent('td').parent('tr').prev();
	},
	
	// crée une ligne "TR" &agrave; ajouter dans une grille ligne
	createNextTr : function(lastTr, nextNum) {
		var nextTr = $(lastTr).clone();
		$(lastTr).removeClass('last_tr');
		if (!$(nextTr).hasClass('last_tr')) {
			$(nextTr).addClass('last_tr');
		}
		$(nextTr).find('.inc_number').empty();
		$(nextTr).find('.inc_number').append(nextNum + 1);
		//$(nextTr).find("td:contains('$NUMERO_')").html(nextNum + 1);
		$(nextTr).find('div.input_text').each(function() {
			$(this).empty();
		});
		$(nextTr).find('input[type=text]').each(function() {
			$(this).attr('id', $(this).attr('name_base').replace('#', nextNum));
			$(this).attr('name', $(this).attr('name_base').replace('#', nextNum));
			$(this).val('');
		});
		$(nextTr).find('input[type=radio]').each(function() {
			$(this).attr('id', $(this).attr('name_base').replace('#', nextNum));
			var nameBase = $(this).attr('name_base');
			var nameVal = nameBase.substr(0, nameBase.indexOf("#") + 1);
			$(this).attr('name', nameVal.replace('#', nextNum));
			$(this).attr('value', nameBase.replace('#', nextNum));
			$(this).prop('checked', false);
		});
		$(nextTr).find('input[type=checkbox]').each(function() {
			$(this).attr('id', $(this).attr('name_base').replace('#', nextNum));
			$(this).attr('name', $(this).attr('name_base').replace('#', nextNum));
			$(this).prop('checked', false);
		});
		$(nextTr).find('select').each(function() {
			$(this).attr('id', $(this).attr('name_base').replace('#', nextNum));
			$(this).attr('name', $(this).attr('name_base').replace('#', nextNum));
			$(this).parent('div').replaceWith(this['outerHTML']);
		});
		$(nextTr).find('select option').each(function() {
			var selectTag = $(this).parent('select');
			var baseName = $(selectTag).attr('name_base');
			var optionVal = "";
			if (nextNum > 0) {
				optionVal = baseName.replace('#', nextNum - 1);
			} else {
				optionVal = baseName.replace('#', nextNum);
			}
			var optionNewVal = baseName.replace('#', nextNum);
			$(this).attr('value', $(this).attr('value').replace(optionVal, optionNewVal));
		});
		$(nextTr).find('select').find('option[selected=selected]').removeAttr('selected');
		$(nextTr).find('select').find('option:eq(0)').attr('selected', 'selected');
		return nextTr;
	},
	
	// action sur click sur le bouton ajouter
	boutonAddClick : function() {
		var curr = this;
		$('.grille_ligne #b_add').on("click", function(evt) {
			var addTr = $(this).parent('td').parent('tr');
			var lastTr = $(addTr).prev();			
			var nextNum = ($(lastTr).find('.inc_number').html() * 1) - 1;
			nextNum++;
			if (nextNum > 0) {
				curr.addGrilleLine(lastTr, nextNum);
				
			}
		});
	},
	
	// ajoute une ligne dans le tableau
	addGrilleLine : function(lastTr, nextNum) {
		
		var nextTr = this.createNextTr(lastTr, nextNum);
		$(nextTr).removeClass('highlight');
		var addTr = $('.grille_ligne #b_add').parent('td').parent('tr');
		if (nextTr[0] != null) {
			var htmlData = nextTr[0]['outerHTML'];
			htmlData += addTr[0]['outerHTML'];
			var lastNum = ($(lastTr).find('.inc_number').html() * 1) - 1;
			var colSepInc = nextNum - lastNum;
			$('.grille_ligne .col_sep').each(function(index, value) {
				var num = $(this).attr('rowspan') * 1;
				num += colSepInc;
				$(this).attr('rowspan', num);
			});
			$(addTr).replaceWith(htmlData);
			$('.grille_ligne .last_tr select').selectmenu();
			this.textGrilleLigneFocusOut($('.grille_ligne .last_tr'));
			this.highligthEvent($('.grille_ligne .last_tr'));
			this.trGrilleLigneHighligthing($('.grille_ligne .last_tr'));
			this.boutonDelClick($('.grille_ligne .last_tr'));
			this.textGrilleLigneClick();			
			//$('.grille_ligne .last_tr').trigger( "create" );	
			$('.survey_page form').trigger('rescan.areYouSure');
			this.boutonAddClick();
		}
	},
	
	// action sur le bouton supprimer
	boutonDelClick : function(tr) {
		var currEtab = this.etab;
		$(tr).find('.edit_zone a').on('click', function() {			
			if ($(tr).parent('tbody').find('tr.data_line').length == 1) {
				alert('Toutes les lignes ne peuvent pas être supprimées', '_');
			} else {
				showConfirm('StatEduc', 'Supprimer cette ligne?', function(response) {
					if (response == 2) {
						$(tr).find("input").each(function() {
							var key = $(this).attr('name');
							if ($(this).attr('type') == "radio") {
								key += '#' + $(this).attr('id');
							}
							currEtab.deleteData($('#questions_lst').val(), key);
						});				
						$(tr).find("select").each(function() {
							var key = $(this).attr('name');
							currEtab.deleteData($('#questions_lst').val(), key);
						});
						$(tr).remove();
						//$('.survey_page form').trigger('rescan.areYouSure');
						$('.survey_page form').addClass('dirty');
						$('.btn_save_form').removeClass('ui-disabled');
					}
				});
			}
		});
	},
	
	highligthEvent : function(tr) {
		var base = tr;
		if (tr == null) {
			base = $('.survey_page');
		}
		$(base).find("input, select").on('click', function() {
			//if (!$(this).hasClass('.highlight'))	{					  
				$('.survey_page .highlight').removeClass('highlight');
				$(this).closest('tr').addClass('highlight');
			//}
		});
	},
	
	// action sur click sur une zone de texte
	textGrilleLigneClick : function() {
		$('.grille_ligne .input_text').parent('td').on("click", function(evt) {
			$(this).find('div.ui-input-text').show();
			$(this).find('input[type=text]').focus();
			$(this).find('div.input_text').hide();
			$(this).css({'min-width': '150px'});
		});
	},
	
	textGrilleLigneFocusOut : function(tr) {
		var base = tr;
		if (tr == null) {
			base = $('.grille_ligne');
		}
		$(base).find('input[type=text]').focusout(function(event) {
			$(this).parent('div').parent('td').css({'min-width': '1px'});
			$(this).parent('div').hide();
			var divText = $(this).parent('div').parent('td').find('div.input_text');
			$(divText).empty();
			$(divText).append($(this).val());
			$(divText).show();
		});
	},
	
	trGrilleLigneHighligthing : function(tr) {
		var base = tr;
		if (tr == null) {
			base = $('.grille_ligne tr.data_line');
		}
		$(base).on('click', function() {
			if (!$(this).hasClass('.highlight'))	{					  
				$('.grille_ligne .highlight').removeClass('highlight');
				$(this).addClass('highlight');
			}
		});
	},
	
	// display next page for current question
	nextPage : function() {
		var currQst = $('#questions_lst').val();
		var nextOpt = $('#questions_lst option[value='+currQst+']').next('option');
		if ( nextOpt != null && $(nextOpt).attr('value') != null) {
			if ($('.survey_page form').hasClass('dirty')) {
				showConfirm('StatEduc', 'Modifications non sauvegardées! Continuez?', function(response) {
					if (response == 2) {
						$('.survey_page form').trigger('reinitialize.areYouSure');	
						$(nextOpt).prop('selected', true);
						$("#questions_lst").trigger("change");
					} else {
						return false;
					}
				});
			} else {
				$(nextOpt).prop('selected', true);
				$("#questions_lst").trigger("change");
			}			
		}
	},
	
	// display previous page for current question
	previousPage : function() {
		var currQst = $('#questions_lst').val();
		var prevOpt = $('#questions_lst option[value='+currQst+']').prev('option');
		if ( prevOpt != null && $(prevOpt).attr('value') != null) {			
			if ($('.survey_page form').hasClass('dirty')) {
				showConfirm('StatEduc', 'Modifications non sauvegardées! Continuez?', function(response) {
					if (response == 2) {
						$('.survey_page form').trigger('reinitialize.areYouSure');
						$(prevOpt).prop('selected', true);
						$("#questions_lst").trigger("change");
					} else {
						return false;
					}
				});
			} else {
				$(prevOpt).prop('selected', true);
				$("#questions_lst").trigger("change");
			}
		}
	},
	
	getPageDataToSend : function(idQst, filter) {
		this.etab.loadCollectData(idQst, filter);
		var datas = this.etab.getData(idQst);
		var dataToSend = '';
		$.each(datas, function(index, value) {
			var type = value.getType();			
			if (type == "text") {
				var val = value.getValue() + '';
					val = val.replace(/\//g,'_slh_');
				dataToSend += '&' + value.getKey() + '=' + val;
			} else if (type == "radio" || type == "checkbox") {
				if (value.getValue() == 1) {
					if (type == "radio") {
						var key = value.getKey();
						var name = 	key.substr(0, key.indexOf("#"));
						var id = key.substr(key.indexOf("#") + 1);
						dataToSend += '&' + name + '=' + id;
					} else {
						dataToSend += '&' + value.getKey() + '=1';
					}
				}
			} else if (type == "select") {
				if (value.getValue() != "") {
					dataToSend += '&' + value.getKey() + '=' + value.getValue();
				}
			} 
		});
		if (stmCamp.getQsts()[0].getId() == idQst) {
			if (dataToSend.indexOf('LOC_REG_') < 0) {
				dataToSend += '&LOC_REG_0=' + stmPageEtab.etab.idRegroup;
			}
		}
		return dataToSend.substr(1);
	},
	
	// Set data on page
	initPageData : function(idQst, idPage) {
		var datas = this.etab.getData(idQst);
		var curr = this;
		$.each(datas, function(index, value) {
			var type = value.getType();	
			var isGrille = $('.survey_page .grille_ligne').length > 0;
			var isAddedTr = false;
			var lastTr = null;
			if (isGrille) {
				lastTr = curr.findLastTr();
				var key = value.getKey();
				if (value.getType() == 'radio') {
					key = 	key.substr(0, key.indexOf("#"));
				}
				if ($('.grille_ligne input[name='+ key +']').length == 0 && $('.grille_ligne input[name='+ key +'_1]').length == 0 && $('.grille_ligne select[name='+ key +']').length == 0) { 
					curr.addNextTr(key, lastTr, 1);
					isAddedTr = true;
					lastTr = curr.findLastTr();
				}
			}			
			if (type == "text") {
				$('.survey_page input[name='+ value.getKey() +']').val(value.getValue());
				if (isGrille) {
					if (isAddedTr) {
						$(lastTr).find('input[name='+ value.getKey() +']').parent('td').find('div.input_text').append(value.getValue());
					} else {
						$('.survey_page input[name='+ value.getKey() +']').parent('td').find('div.input_text').append(value.getValue());
					}					
				}
			} else if (type == "radio" || type == "checkbox") {
				if (value.getValue() == 1) {
					if (type == "radio") {
						var key = value.getKey();
						var name = 	key.substr(0, key.indexOf("#"));
						var id = key.substr(key.indexOf("#") + 1);
						$('.survey_page input[id='+ id +']').prop( "checked", true );
					} else {
						$('.survey_page input[name='+ value.getKey() +']').prop( "checked", true );
					}
				}
			} else if (type == "select") {
				var selectTag = $('.survey_page select[name='+ value.getKey() +']');
				if (value.getValue() != "") {
					$(selectTag).find('option[value="'+value.getValue()+'"]').attr('selected','selected');
				}
				var divSelect = $(selectTag).parent('div');
				$(divSelect).replaceWith($(selectTag).clone()['outerHTML']);
				//$('.survey_page select[name='+ value.getKey() +']').selectmenu();
			}
		});		
		if ($('#NOM_ETABLISSEMENT_0').exists()) {
			$('#NOM_ETABLISSEMENT_0').val(stmPageEtab.etab.nom);
		}
		if ($('#LOC_REG_0').exists()) {
			$('#LOC_REG_0').val(stmPageEtab.etab.idRegroup);
			$('#LOC_REG_0').prop('disabled', true);
		} else if (stmCamp.getQsts()[0].getId() == $('#questions_lst').val()) {
			$('FORM').append('<input name="LOC_REG_0" id="LOC_REG_0" type="hidden" value="'+stmPageEtab.etab.idRegroup+'" />');
		}
		if ($('#CODE_ADMIN_ETAB_0').exists()) {
			var eCode = stmPageEtab.etab.code==null ? '' : stmPageEtab.etab.code;	
			$('#CODE_ADMIN_ETAB_0').val(eCode);
		}
	},
	
	// Get data from server
	getPageData : function(idQst, isCurr, filter) {
		
	},
	
	// Save data on mobile
	savePage : function(displayMsg) {
		$.mobile.loading( 'show' );
		/*$("input[name*=NB_EFF_], input[name*=RED_]").each(function(index) {
			if ($(this).val() == "") {
				$(this).val(0);
			}
		});*/
		var currEtab = this.etab;
		var currIdQst = $('#questions_lst').val();
		var curr = this;
		var error = false;
		$('.survey_page input').each(function(index) {
			var key = $(this).attr("name");				
			var type = $(this).attr("type");
			var value = 0;
			if (type == "text" || type == "select") {
				value = $(this).val();
				var result = curr.validData(key, value);
				if (result != 1) {
					if ($('.grille_ligne').exists()) {
						$(this).closest('tr').click();
						$(this).closest('td').click();	
					} else {
						$(this).click();	
					}
					$(this).focus();
					$.mobile.loading( 'hide' );	
					stmError.displayMsg(result);	
					error = true;
					return false;
				}
			} else if (type == "radio" || type == "checkbox") {
				if($(this).is(":checked")) {
					value = 1;
				}
				if (type == "radio") {
					var id = $(this).attr("id");
					key += "#" + id;
				}
			} else if (type == "hidden") {
				value = $(this).val();
			}
			var stmData = new StmCollectData(currIdQst, key, value, type);
			currEtab.addData(stmData);
		});
		if (!error) {
			ctrl_saisie();
			if ( !do_submit ) {
				$.mobile.loading( 'hide' );	
				return false;
			}
			$('.survey_page select').each(function(index) {
				var key = $(this).attr("name");				
				var type = "select";
				var value = $(this).val();
				var stmData = new StmCollectData(currIdQst, key, value, type);
				currEtab.addData(stmData);
			});
			var filter = null;
			if ($('#filter_lst').exists()) {
				filter = $('#filter_lst').val();
			}
			currEtab.saveData(currIdQst, displayMsg, filter);
			$('.survey_page form').trigger('reinitialize.areYouSure');
		}
		$.mobile.loading( 'hide' );	
		return error;
	},
	
	activeButtons : function() {
		var curr = this;
		$('.btn_save_form').on('click', function() {
			curr.savePage(true);										 
		});
		$('.ui-icon-arrow-l').on('click', function() {
			curr.previousPage();										 
		});
		$('.ui-icon-arrow-r').on('click', function() {
			curr.nextPage();										 
		});
	},
	
	validData : function(name, val) {
		var result = 1;
		//if (val != '') {
			result = currSmtQst.validData(name, val);
		//}
		return result;
	}
}