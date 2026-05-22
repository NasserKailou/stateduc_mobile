// JavaScript Document
var currentUser = null;
var urlServer = null;
//var isLastHtml = false;
var isUpdate = false;

$.ajaxSetup({
        scriptCharset: "iso-8859-1",
        cache: false
});

var stmChargeCamp = {
	
	currCamp : null,	
	currUser : null,
	
	initVars : function() {
		if (existStorage('localStorage')) {
			urlServer = localStorage.getItem('stm_UrlServer');
			var user = JSON.parse(sessionStorage.getItem('stm_userData'));
			currentUser = user;
			this.currUser = user;
		}
	},
	
	// charge toute les données d'une nouvelle campagne
	chargeNewCampData : function(id_camp, camp) {
		$.mobile.loading( 'show' );	
		if (camp == null) {
			camp = stmCampagnes.getById(stmCampagnes.getNewCampType(), id_camp);
		}
		this.currCamp = camp;
		if (!isUpdate) {
			$('#display_new_camp').empty();
			$('#display_new_camp').append(camp.nom);
			$('#display_new_camp').attr('href', 'camp.html?id='+id_camp+'&type='+camp.statut);
			$('#submit_new_camp_frm').prop('disabled', true);
		}
		$('#select-choice-camp').selectmenu('disable');
		$('#new_camp_charge_box').show();
		$('#new_camp_charge_msg').empty();
		$('#new_camp_charge_msg').append('<div>Demarrage du chargement : ' + (new Date()).toLocaleString() + '&#10;</div>');
		
		stmCampagnes.addNewCamp(camp);
		this.get_regroups(id_camp);
		this.get_type_regroups(id_camp);
		this.get_status();
		this.get_etabs(id_camp);
		this.get_locs(id_camp);
		this.get_systems(id_camp);
	},
	
	// recupères les regroupements d'un utilisateur pour une campagnes du serveur
	get_regroups : function(id_camp) {	
		$('#new_camp_charge_msg').append('<div id="reg_log">--- chargement entit&eacute;s administratives</div>');
		getDataFromServer('/user_camp.php/reg_camp/', currentUser.login +'/'+ id_camp +'/1', this.addRegroup);
	},	
	addRegroup : function(regs) {
		$.each(regs, function(idx, value) {
			stmRegroups.addRegroup(value);
		});	
		stmRegroups.saveRegroup();
		$('#new_camp_charge_msg #reg_log').append(' : OK');
	},
	
	// recupères les types de regroupement d'un utilisateur pour une campagnes du serveur
	get_type_regroups : function(id_camp) {	
		$('#new_camp_charge_msg').append('<div id="type_reg_log">--- chargement types des entit&eacute;es administratives</div>');
		getDataFromServer('/user_camp.php/typ_reg_camp/', currentUser.id +'/'+ id_camp +'/'+ this.currCamp.typeregroups, this.addTypeRegroup);
	},	
	addTypeRegroup : function(typeRegs) {
		$.each(typeRegs, function(idx, value) {
			stmRegroups.addTypeRegroup(value);
		});	
		stmRegroups.saveTypeRegroup();
		$('#new_camp_charge_msg #type_reg_log').append(' : OK');
	},
	
	// recupères les statuts du serveur
	get_status : function() {	
		$('#new_camp_charge_msg').append('<div id="status_log">--- chargement des status</div>');
		getDataFromServer('/user_camp.php/etabs_status/', '', this.addStatus);
	},	
	addStatus : function(allstatus) {
		$.each(allstatus, function(idx, value) {
			var status = new StmStatus(value.id, value.name);	
			stmStatus.addStatus(status);
		});	
		stmStatus.save();
		$('#new_camp_charge_msg #status_log').append(' : OK');
	},
	
	// recupères les etablissements d'un utilisateur pour une campagnes du serveur
	get_etabs : function(id_camp) {	
		$('#new_camp_charge_msg').append('<div id="etab_log">--- chargement des &eacute;tablissements</div>');
		getDataFromServer('/user_camp.php/etabs_camp/', currentUser.id +'/'+ id_camp +'/1', this.addEtab);
	},	
	addEtab : function(etabs) {
		$.each(etabs, function(idx, value) {
			stmEtabs.addEtab(value);
		});	
		stmEtabs.save();
		$('#new_camp_charge_msg #etab_log').append(' : OK');
	},
	
	// recupères les chaines de localisation d'un utilisateur pour une campagnes du serveur
	get_locs : function(id_camp) {	
		$('#new_camp_charge_msg').append('<div id="loc_log">--- chargement des chaines de localisation</div>');
		var addLocsFunc = this.addLocs.bind(this.addLocs, id_camp);
		getDataFromServer('/user_camp.php/locs_camp/', currentUser.id +'/'+ id_camp, addLocsFunc);
	},	
	addLocs : function(id_camp, locs) {
		var locsStr = JSON.stringify(locs);
		window.localStorage.setItem('stm_ChargedLocs_'+id_camp, locsStr);
		$('#new_camp_charge_msg #loc_log').append(' : OK');
	},
	
	// recupères les systemes du serveur
	get_systems : function(id_camp) {	
		$('#new_camp_charge_msg').append('<div id="sys_log"><span id="sys_lib">--- chargement des secteurs</span></div>');
		var addSystemsFunc = this.addSystems.bind(this.addSystems, id_camp);
		getDataFromServer('/user_camp.php/sys_camp/', currentUser.id +'/'+ id_camp, addSystemsFunc);
	},	
	addSystems : function(id_camp, systs) {
		$.each(systs, function(idx, value) {
			var sys = new StmSystem(value.id, value.nom);	
			stmSystems.addSystem(sys);
			var isLastSys = false;
			if (idx == (systs.length - 1)) {				
				isLastSys = true;
			}	
			stmChargeCamp.get_qsts(id_camp, value.id, isLastSys);
		});	
		stmSystems.save();
		$('#new_camp_charge_msg #sys_log #sys_lib').append(' : OK');
	},
	
	// recupères les thèmes d'une campagne
	get_qsts : function(id_camp, id_sys, isLastSys) {
		$('#new_camp_charge_msg #sys_log').append('<div id="qst_log_'+id_sys+'"><span id="qst_lib_'+id_sys+'">------ chargement formulaire du syst&egrave;me '+id_sys+'</span></div>');
		var addQstsFunc = this.addQsts.bind(this.addQsts, this.addQstHtml, id_camp, id_sys, isLastSys);
		getDataFromServer('/data_camp.php/theme_camp/', id_camp+'/'+id_sys + '/eng', addQstsFunc);
	},	
	addQsts : function(addQstHtmlFunc, id_camp, id_sys, isLastSys, qsts) {
		var qstsStr = JSON.stringify(qsts);
		window.localStorage.setItem('stm_ChargedQst_'+id_camp+'_'+id_sys, qstsStr);
		$.each(qsts, function(idx, qst) {	
			var isLastHtml = false;
			if (idx == (qsts.length - 1)) {				
				isLastHtml = true;
			}	
			var addQstHtmlFuncOk = addQstHtmlFunc.bind(addQstHtmlFunc, id_camp, qst.id, id_sys, isLastHtml, isLastSys);			
			getDataFromServer('/data_camp.php/html_theme_camp/', id_camp +'/'+ qst.id +'/eng', addQstHtmlFuncOk);						  
		});
	},	
	addQstHtml : function(id_camp, id_qst, id_sys, isLastHtml, isLastSys, html) {
		/*$.get( html, function( data ) {
			var htmlStr = JSON.stringify(data);
			window.localStorage.setItem('stm_ChargedQstHtml_'+id_camp+'_'+id_qst+'_'+id_sys, '');
			window.localStorage.removeItem('stm_ChargedQstHtml_'+id_camp+'_'+id_qst+'_'+id_sys);
			setTimeout(function() {window.localStorage.setItem('stm_ChargedQstHtml_'+id_camp+'_'+id_qst+'_'+id_sys, htmlStr);}, 100);				
			if (isLastHtml) {
				$('#new_camp_charge_msg span#qst_lib_'+id_sys).append(' : OK');
			}
			stmChargeCamp.getRules(id_camp, id_qst, id_sys, isLastHtml && isLastSys);
		});	*/
		$.ajax({
		  url: html,
		  headers: { 'Authorization': 'Basic ' + btoa(stmChargeCamp.currUser.login+':'+sessionStorage.getItem("stm_userPass")) },
		  statusCode: {
			401: function() {
				alert( "Accès refusé" );
				stmChargeCamp.loadingEnd();
			},
			404: function() {
				alert( "Erreur : vérifier l'url de votre serveur!" );
				stmChargeCamp.loadingEnd();
			}
		  },
		  success: function (data) {
			var htmlStr = JSON.stringify(data);
			window.localStorage.setItem('stm_ChargedQstHtml_'+id_camp+'_'+id_qst+'_'+id_sys, '');
			window.localStorage.removeItem('stm_ChargedQstHtml_'+id_camp+'_'+id_qst+'_'+id_sys);
			setTimeout(function() {window.localStorage.setItem('stm_ChargedQstHtml_'+id_camp+'_'+id_qst+'_'+id_sys, htmlStr);}, 100);				
			if (isLastHtml) {
				$('#new_camp_charge_msg span#qst_lib_'+id_sys).append(' : OK');
			}
			stmChargeCamp.getRules(id_camp, id_qst, id_sys, isLastHtml && isLastSys);
		  },
		  error: function(XMLHttpRequest, textStatus, errorThrown) {
			if (textStatus==="timeout") {
				alert( "Délais d'attente dépassé!" );
			} else {
				alert( "Erreur : " + textStatus);
			}
			stmChargeCamp.loadingEnd();
		  }, 
		  dataType:'html',
		  timeout: 60000
		});		
	},
	getRules : function(id_camp, id_qst, id_sys, isLast) {
		$('#new_camp_charge_msg #qst_log_'+id_sys).append('<div id="rule_log_'+id_qst+'">-------- chargement des r&egrave;gles pour '+id_qst+'</div>');
		var addRulesFunc = stmChargeCamp.addRules.bind(stmChargeCamp.addRules, id_camp, id_qst, id_sys, isLast);
		getDataFromServer('/data_camp.php/regle_theme_camp/', id_qst +'/'+ id_sys, addRulesFunc);		
	},
	addRules : function(id_camp, id_qst, id_sys, isLast, rules) {
		window.localStorage.setItem('stm_ChargedThemeRule_'+id_camp+'_'+id_qst+'_'+id_sys, JSON.stringify(rules));
		$('#new_camp_charge_msg #rule_log_'+id_qst).append(' : OK');
		if (isLast) {
			setTimeout(function() {
				$('#new_camp_charge_msg').append('<div>Fin du chargement : ' + (new Date()).toLocaleString() + '&#10;</div>');
				stmChargeCamp.loadingEnd(id_camp);
			}, 100);
		}
	},
	
	loadingEnd : function(id_camp) {
		if (!isUpdate) {
			if (id_camp != null) {
				$('#select-choice-camp option[value= ' + id_camp + ' ]').remove();
				$('#select-choice-camp').change();
			}
			$('#submit_new_camp_frm').prop('disabled', false);
			$('#submit_new_camp_frm').prop('disabled', false);
			$('#select-choice-camp').selectmenu('enable');
		} else {
			window.location.reload();	
		}
		$.mobile.loading( 'hide' );	
	},
	
	resetAll : function() {
		$('#select-choice-camp').empty();
		$('#select-choice-camp').append('<option>Choisir</option>');
		$('#new_camp_frm_debut').empty();
		$('#new_camp_frm_fin').empty();
		$('#new_camp_frm_statut').empty();	
	}
}


function getDataFromServer(servSuffix, params, callBack) {
	var mdp = sessionStorage.getItem("stm_userPass");	
	$.ajax({type:'get', url: urlServer + servSuffix + params, data: '', 
		headers: {	'Authorization': 'Basic ' + btoa(stmChargeCamp.currUser.login+':'+mdp)	},
		statusCode: {
			401: function() {
				alert( "Accès refusé" );
				stmChargeCamp.loadingEnd();
			},
			404: function() {
				alert( "Erreur : vérifier l'url de votre serveur!" );
				stmChargeCamp.loadingEnd();
			}
		},
		success: function(response) {
			if (response.se_status == 200) {
				callBack(response.se_data);
			} else {
				callBack(response.se_data);
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			if (textStatus==="timeout") {
				alert( "Délais d'attente dépassé!" );
				stmChargeCamp.loadingEnd();
			}
		}, 
		dataType:'json',
		timeout: 60000
	});
}

function sendDataToServer(servSuffix, params, themeData, idQst, isLast, fromCampPage, idEtab, filter, isFilterLast) {
	var mdp = sessionStorage.getItem("stm_userPass");	
	$.ajax({type:'get', url: urlServer + servSuffix + params, data: themeData, 
		headers: {	'Authorization': 'Basic ' + btoa(stmChargeCamp.currUser.login+':'+mdp)	},
		statusCode: {
			401: function() {
				$.mobile.loading( 'hide' );
				if (fromCampPage) {
					$('#save_log_'+idEtab+'').append('/<a href="#" onClick="javascript:alert( \"Accès refusé!\" );" id_etab="'+ idEtab +'" id_qst="'+ idQst +'" class="log_link camp_save_401">' + idQst + ':E_401</a>/ ');
				} else {
					alert( "Accès refusé!" );
				}
			},
			404: function() {
				$.mobile.loading( 'hide' );
				if (fromCampPage) {
					$('#save_log_'+idEtab+'').append('/<a href="#" onClick="javascript:alert( \"Erreur : vérifier url de votre serveur!\" );" id_etab="'+ idEtab +'" id_qst="'+ idQst +'" class="log_link camp_save_404">' + idQst + ':E_404</a>/ ');
				} else {
					alert( "Error : vérifier l'url de votre serveur!" );
				}
			}
		},
		success: function(response) {
			var result = JSON.parse(response);	
			if (result.se_status == 400) {
				$.mobile.loading( 'hide' );
				$('#save_log_'+idQst+'').append('KO');	
				alert(result.se_data);
			} else {
				if (filter == null || isFilterLast) {
					saveQstTab['_'+idQst] = true;
				}
				if (!fromCampPage && filter != null && !$('#save_log_'+idQst+':contains("OK")').exists()) {
					$('#save_log_'+idQst+'').append('OK');	
				} else if (filter == null) {
					if (!fromCampPage) {
						$('#save_log_'+idQst+'').append('OK');	
					} else {
						$('#save_log_'+idEtab+'').append('/<a href="#p_etab" onClick="javascript:stmPageCamp.goToQst('+idEtab+', '+idQst+')" class="log_link camp_save_ok">' + idQst + ':OK</a>/ ');	
					}
				} else if (fromCampPage && isFilterLast) {
						$('#save_log_'+idEtab+'').append('/<a href="#p_etab" onClick="javascript:stmPageCamp.goToQst('+idEtab+', '+idQst+')" class="log_link camp_save_ok">' + idQst + ':OK</a>/ ');
				}
				if (stmPageEtab.allIsSend()) {
					$.mobile.loading( 'hide' );
					if (fromCampPage) {
						//alert('Saved on server');
					}
				}
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$.mobile.loading( 'hide' );
			if (textStatus==="timeout") {
				if (fromCampPage) {
					$('#save_log_'+idEtab+'').append('/<a href="#" onClick="javascript:alert( \"Délais attente dépassé!\" );" id_etab="'+ idEtab +'" id_qst="'+ idQst +'" class="log_link camp_save_to">' + idQst + ':E_TO</a>/ ');
				} else {
					alert( "Délais d'attente dépassé!" );
				}
			}
		}, 
		dataType:'html',
		timeout: 60000
	});
}

function postDataToServer(servSuffix, params, themeData, idQst, isLast, fromCampPage, idEtab, filter, isFilterLast) {
	var mdp = sessionStorage.getItem("stm_userPass");	
	$.ajax({type:'post', url: urlServer + servSuffix + params + '/0', data: themeData, 
		headers: {	'Authorization': 'Basic ' + btoa(stmChargeCamp.currUser.login+':'+mdp)	},
		statusCode: {
			401: function() {
				$.mobile.loading( 'hide' );
				if (fromCampPage) {
					$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#" onClick="javascript:alert( \"Accès refusé!\" );" id_etab="'+ idEtab +'" id_qst="'+ idQst +'" class="log_link camp_save_401">' + idQst + ':E_401</a>/ ');
				} else {
					alert( "Accès refusé!" );
				}
			},
			404: function() {
				$.mobile.loading( 'hide' );
				if (fromCampPage) {
					$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#" onClick="javascript:alert( \"Erreur : vérifier url de votre serveur!\" );" id_etab="'+ idEtab +'" id_qst="'+ idQst +'" class="log_link camp_save_404">' + idQst + ':E_404</a>/ ');
				} else {
					alert( "Erreur : vérifier l'url de votre serveur!" );
				}
			}
		},
		success: function(response) { try {
			var result = JSON.parse(response);	
			if (result.se_status == 400) {
				$.mobile.loading( 'hide' );
				saveQstTab['_'+idQst] = true;
				if (!fromCampPage) {
					$('#save_log_'+idQst+'').append('KO SERVER');	
					alert(result.se_data);
				} else if (fromCampPage && isFilterLast) {
					$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#p_etab" onClick="javascript:stmPageCamp.goToQst('+idEtab+', '+idQst+')" class="log_link camp_save_ko">' + idQst + ':KO SERVER</a>/ ');
				}
			} else {
				var saveStatus = 'OK';
				var camp_save_css_class = 'camp_save_ok';
				if (result.se_data != 'OKSAVE') {
					saveStatus = 'KO DATA';
					camp_save_css_class = 'camp_save_ko';
				}
				if (filter == null || isFilterLast) {
					saveQstTab['_'+idQst] = true;
				}
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
				if (stmPageEtab.allIsSend()) {
					$.mobile.loading( 'hide' );
					if (fromCampPage) {
						//alert('Saved on server');
					}
				}
			} } catch (e) {alert(e.message);}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$.mobile.loading( 'hide' );
			if (textStatus==="timeout") {
				if (fromCampPage) {
					$('#save_log_'+idEtab+'_'+idQst).html('/<span class="camp_save_to">' + idQst + ':E_TO</a>/ ');
				} else {
					alert( "Délais d'attente dépassé!" );
				}
			}
		}, 
		dataType:'html',
		timeout: 120000
	});
}

function getFormDataFromServer(servSuffix, params, idQst, isLast, fromCampPage, idEtab, filter, isFilterLast, funcCallBack) {
	var mdp = sessionStorage.getItem("stm_userPass");	
	$.ajax({type:'get', url: urlServer + servSuffix + params, data: "", 
		headers: {	'Authorization': 'Basic ' + btoa(stmChargeCamp.currUser.login+':'+mdp)	},
		statusCode: {
			401: function() {
				$.mobile.loading( 'hide' );
				if (fromCampPage) {
					$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#" onClick="javascript:alert( \"Accès refusé!\" );" id_etab="'+ idEtab +'" id_qst="'+ idQst +'" class="log_link camp_save_401">' + idQst + ':E_401</a>/ ');
				} else {
					alert( "Accès refusé!" );
				}
			},
			404: function() {
				$.mobile.loading( 'hide' );
				if (fromCampPage) {
					$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#" onClick="javascript:alert( \"Erreur : vérifier url de votre serveur!\" );" id_etab="'+ idEtab +'" id_qst="'+ idQst +'" class="log_link camp_save_404">' + idQst + ':E_404</a>/ ');
				} else {
					alert( "Erreur : vérifier l'url de votre serveur!" );
				}
			}
		},
		success: function(response) { 
			try {
				var result = JSON.parse(response);	
				if (result.se_status == 400) {
					$.mobile.loading( 'hide' );
					saveQstTab['_'+idQst] = true;
					if (!fromCampPage) {
						$('#save_log_'+idQst+'').append('KO SERVER');	
						alert(result.se_data);
					} else if (fromCampPage && isFilterLast) {
						$('#save_log_'+idEtab+'_'+idQst).html('/<a href="#p_etab" onClick="javascript:stmPageCamp.goToQst('+idEtab+', '+idQst+')" class="log_link camp_save_ko">' + idQst + ':KO SERVER</a>/ ');
					}
				} else {
					funcCallBack(response);
				} 
			} catch (e) {alert(e.message);}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			$.mobile.loading( 'hide' );
			if (textStatus==="timeout") {
				if (fromCampPage) {
					$('#save_log_'+idEtab+'_'+idQst).html('/<span class="camp_save_to">' + idQst + ':E_TO</a>/ ');
				} else {
					alert( "Délais d'attente dépassé!" );
				}
			}
		}, 
		dataType:'html',
		timeout: 120000
	});
}