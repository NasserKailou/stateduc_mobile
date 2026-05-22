// JavaScript Document
//Id de la campagne chargée
var vars = $.getUrlVars();
var idCamp = vars['id'];
var typeCamp = vars['type'];
var currentEtabId = null;
var currPage = 1;

var camp = stmCampagnes.getById(stmCampagnes.getChargedCampType(), idCamp);
var stmCamp = new StmCampagne(camp.id, camp.nom, camp.debut, camp.fin, camp.statut, camp.typeregroups.split(","));

// Page campagne init
$( document ).one( "pagecreate", "#p_camp", function( event ) {
	$('.stm_back_on_logo_lnk').bind('click', function(evt) { 
		if ($('.survey_page form').hasClass('dirty')) {       
      evt.preventDefault(); 
			showConfirm('StatEduc', 'Modifications non sauvegardées! \n Continuer?', function(response) {
				if (response == 2) {
					window.history.go(-1); 
				} else {
					return false;
				}
			});
		} else {
			if (currPage == 1) {   
				window.history.go(-1);
			} else {
				currPage = 1;
				window.history.back();
			}
		}										  	
  });
	
	stmPageCamp.chargeData();
	stmPageCamp.displayCampIdent();
	stmPageCamp.displaySystems();
});

//Page campagne show
$( document ).one( "pageshow", "#p_camp", function( event ) {
	init_connexion('#p_camp');
	init_options('#p_camp');
	display_user_con_form("#p_camp");
	currPage = 1;
});

var stmPageCamp = {
	
	// Liste des établissements filtrés
	listEtabs : new Array(),
	
	// Charge les données de la campagne
	chargeData : function() {
		stmCamp.chargeLocs();
	},
	
	// Affiche les informations sur une campagne
	displayCampIdent : function() {		
		var htmlData = '<div data-role="collapsible" class="div_collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u" data-collapsed="false">'
							+'<h3>' + camp.nom + '</h3>'
							+'<!--div>'
								+'<div><span id="camp_frm_debut">'+ camp.debut +'</span> - <span id="camp_frm_fin">'+ camp.fin +'</span></div>'
								+'<div><span id="camp_frm_statut">'+ stmCampagnes.getLibelleStatut(camp.statut) +'</span></div>'
							+'</div-->'	
							+'<div data-role="controlgroup" data-type="horizontal" data-mini="true">'
								+'<a href="#" id="btn_delete_camp" class="ui-shadow ui-btn ui-corner-all ui-btn-icon-left ui-icon-delete ui-btn-a">Supprimer</a>'
								+'<a href="#" id="btn_update_camp" class="ui-shadow ui-btn ui-corner-all ui-btn-icon-left ui-icon-refresh ui-btn-a">Recharger m&eacute;tadonn&eacute;es</a>'
							+'</div><br/>'	
							+'<div data-role="controlgroup" data-type="horizontal" data-mini="true">'
								+'<a href="#" id="btn_save_camp_data" class="ui-shadow ui-btn ui-corner-all ui-btn-icon-left ui-icon-action ui-btn-a">Envoyer donn&eacute;es</a>'
								+'<a href="#" id="btn_update_etab_data" class="ui-shadow ui-btn ui-corner-all ui-btn-icon-left ui-icon-refresh ui-btn-a">Recharger Identification</a>'
								+'<a href="#" id="btn_update_camp_data" class="ui-shadow ui-btn ui-corner-all ui-btn-icon-left ui-icon-refresh ui-btn-a">Recharger donn&eacute;es</a>'
							+'</div>'	
							+'<div id="new_camp_charge_box">'
								+'<label for="new_camp_charge_msg">Rechargement...</label>' 					
								+'<div id="new_camp_charge_msg" class="ui-corner-all ui-shadow"></div>'
							+'</div>'
							+'<div id="save_log_camp" class="ui-corner-all">'
							+'</div>'
		 				+'</div>';
		$('#stm_camp_ident').empty();
		$('#stm_camp_ident').append(htmlData);		
		$('#stm_camp_ident').trigger( "create" );
		stmChargeCamp.initVars();
		$('#btn_delete_camp').on("click", function() {
		  	showConfirm('StatEduc', 'supprimer cette campagne?', stmPageCamp.deleteCamp );
		});
		$('#btn_update_camp').on("click", function() {
			if (!stmUser.isConnect()) {
				alert('Vous devez être connecté');
				return false;					
			} else {
				showConfirm('StatEduc', 'Recharger cette campagne?', stmPageCamp.reloadCamp);
			}
		});
		$('#btn_save_camp_data').on('click', function(event) {	
			if (!stmUser.isConnect()) {
				alert('Vous devez être connecté');
				return false;					
			} else {
				showConfirm('StatEduc', 'Sauvegarder les données de tous les établissements?', stmPageCamp.saveConfirmCallback);				
			}
		});	
		$('#btn_update_etab_data').on('click', function(event) {	
			if (!stmUser.isConnect()) {
				alert('Vous devez être connecté');
				return false;					
			} else {
				showConfirm('StatEduc', 'Recharger les données d\'identification de tous les établissements?', stmPageCamp.reloadFirstConfirmCallback);				
			}
		});	
		$('#btn_update_camp_data').on('click', function(event) {	
			if (!stmUser.isConnect()) {
				alert('Vous devez être connecté');
				return false;					
			} else {
				showConfirm('StatEduc', 'Recharger les données de tous les établissements?', stmPageCamp.reloadConfirmCallback);				
			}
		});	
	},
	
	saveConfirmCallback : function(opt) {		
		if (opt == 2) {
			$.mobile.loading( 'show' );	
			$('#save_log_camp').empty();
			$('#save_log_camp').addClass('save_log');
			var nbEtabs = stmPageCamp.listEtabs.length;
			if (nbEtabs == 0) {
				$.mobile.loading( 'hide' );	
				alert('Aucun établissement sélectionné!');
				return false;
			} 
			for (var i=0; i < nbEtabs; i++) {
				currentEtabId = stmPageCamp.listEtabs[i];
				stmPageEtab.chargeData();
				stmPageEtab.setSys($('#select-choice-sys').val());
				$('#save_log_camp').append('<div id="save_log_'+currentEtabId+'">'+stmPageEtab.etab.nom+'... </div>');
				stmPageEtab.saveAllQstsOnServer(currentEtabId, true);
			}
		}
	},
	
	reloadFirstConfirmCallback : function(opt) {		
		if (opt == 2) {
			//$.mobile.loading( 'show' );	
			$('#save_log_camp').empty();
			$('#save_log_camp').addClass('save_log');
			var nbEtabs = stmPageCamp.listEtabs.length;
			if (nbEtabs == 0) {
				$.mobile.loading( 'hide' );	
				alert('Aucun établissement sélectionné!');
				return false;
			} 
			for (var i=0; i < nbEtabs; i++) {
				currentEtabId = stmPageCamp.listEtabs[i];
				stmPageEtab.chargeData();
				stmPageEtab.setSys($('#select-choice-sys').val());
				$('#save_log_camp').append('<div id="save_log_'+currentEtabId+'">'+stmPageEtab.etab.nom+'... </div>');
				stmPageEtab.reloadFirstQstsFromServer(currentEtabId, true);
			}
		}
	},
	
	reloadConfirmCallback : function(opt) {		
		if (opt == 2) {
			//$.mobile.loading( 'show' );	
			$('#save_log_camp').empty();
			$('#save_log_camp').addClass('save_log');
			var nbEtabs = stmPageCamp.listEtabs.length;
			if (nbEtabs == 0) {
				$.mobile.loading( 'hide' );	
				alert('Aucun établissement sélectionné!');
				return false;
			} 
			for (var i=0; i < nbEtabs; i++) {
				currentEtabId = stmPageCamp.listEtabs[i];
				stmPageEtab.chargeData();
				stmPageEtab.setSys($('#select-choice-sys').val());
				$('#save_log_camp').append('<div id="save_log_'+currentEtabId+'">'+stmPageEtab.etab.nom+'... </div>');
				stmPageEtab.reloadAllQstsFromServer(currentEtabId, true);
			}
		}
	},
	
	reloadCamp : function(buttonIndex) {
		if (buttonIndex == 2) {
			isUpdate = true;
			stmChargeCamp.initVars();
			stmChargeCamp.chargeNewCampData(idCamp, camp);
		}
	},
	
	deleteCamp : function(buttonIndex) {
		if (buttonIndex == 2) {
			stmCamp.deleteCamp();
			window.history.go(-1);
		}
	},
	
	// Affiche les systèmes d'enseignement disponible
	displaySystems : function() {
		var htmlData = '<option>Choisir</option>';
		$.each( stmCamp.getSystems(), function( index, value ){
			htmlData += '<option value="'+ value.getId() +'">'+ value.getNom() +'</option>';
		});
		$('#select-choice-sys').empty();
		$('#select-choice-sys').append(htmlData);		
		$('#select-choice-sys').change();
		
		$('#select-choice-sys').on("change", function() {
			$.mobile.loading( 'show' );	
			var curr = this;										  
			setTimeout(function() {
				$.mobile.loading( 'show' );		
				stmCamp.chargeQst($(curr).val());
				$('#data_regroups').empty();
				stmPageCamp.displayRegroups(-1, $(curr).val(), 0);
				stmPageCamp.resetListEtabs();
				stmPageCamp.displayEtabs(-1, $(curr).val());
			}, 200);
		});
	},
	
	// Affiche les regroupements fils d'un regroupement donné
	displayRegroups : function(parentId, systemId, niv) {	
		$.mobile.loading( 'show' );	
		var rpgs = stmCamp.getRegroups(parentId, systemId);
		if (rpgs.length > 0) {
			var htmlData = '<div class="ui-field-contain" id="div-regroup-'+ niv +'">'
							+'<label for="select-choice-'+ niv +'" class="select">'+ stmCamp.getTypeRegroup(niv) +'</label>'
							+'<select name="select-choice-'+ niv +'" id="select-regroup-'+ niv +'" data-native-menu="false" data-mini="true">'
							+'<option>Choisir</option>';
			var nbRgp = rpgs.length - 1;				
			for (var i = nbRgp; i > -1; i--) {
				htmlData += '<option value="'+ rpgs[i].getId() +'">'+ rpgs[i].getNom() +'</option>';
			}
			htmlData += '</select>'
					   +'</div>';
			$('#data_regroups').append(htmlData);			
			$('#select-regroup-'+ niv).selectmenu();
			
			$('#select-regroup-'+ niv).on("change", function() {
				$.mobile.loading( 'show' );	
				var curr = this;										  
				setTimeout(function() {
					$('#data_regroups select').each(function(i) {
						var currNiv = $(this).attr('id').substring(15);									 
						if (currNiv > niv) {													 
							 $('#div-regroup-'+ currNiv).replaceWith("");		
						}
					});
					stmPageCamp.resetListEtabs();
					stmPageCamp.displayEtabs($(curr).val(), $('#select-choice-sys').val());
					stmPageCamp.displayRegroups($(curr).val(), $('#select-choice-sys').val(), niv + 1);
				}, 200);
			});
		}
		$.mobile.loading( 'hide' );	
	},
	
	//Réinitialise la liste des établissements
	resetListEtabs : function() {
		$("#stm_camp_etabs ul").empty();
		this.listEtabs = new Array();
	},
	
	// Affiche la liste des établissements pour une zone et un système donnés
	displayEtabs : function(parentId, systemId) {	
		$.mobile.loading( 'show' );		
		var rpgs = stmCamp.getRegroups(parentId, systemId);
		if (rpgs.length > 0) {
			var isEnd = true;
			$.each( rpgs, function( index, value ) {
				if (stmCamp.isLastRegroup(value.getType())) {
					stmPageCamp.displayFinalRegroupEtabs(value.getId(), systemId);
				} else {
					isEnd = false;
					stmPageCamp.displayEtabs(value.getId(), systemId);
				}
			});
			if (isEnd) {
				$.mobile.loading( 'hide' );	
			}
		} else {
			stmPageCamp.displayFinalRegroupEtabs(parentId, systemId);
			$.mobile.loading( 'hide' );	
		}
	},
	
	// Affiche les établissements d'un regroupement final en fonction d'un système
	displayFinalRegroupEtabs : function(regroupId, systemId) {
		var etabs = stmCamp.getRegroupEtabs(regroupId, systemId);
		var nb = etabs.length;
		for (var i = 0; i < nb; i++) {
			var etab = etabs[i];
			var eCode = etab.code==null ? '' : '<p>' + etab.code + '</p>';
			var cssAdd = etab.code==null ? ' camp_title' : '';
			var li = '<li><a href="#p_etab" id_etab="'+ etab.id +'"><div class="stm_list_title'+ cssAdd +'">' + etab.nom + '</div>' + eCode + '</a></li>';			
			if ($("#stm_camp_etabs ul li").exists()) {
				var insert = false;
				$("#stm_camp_etabs ul li").each(function(i) {
					var res = 0;
					if ($(this).find(".stm_list_title").html()) {
						res = $(this).find(".stm_list_title").html().localeCompare(etab.nom);
					}
					if (res > 0) {
						$(this).before(li);
						$("#stm_camp_etabs ul").listview( "refresh" );
						insert = true;
						return false;
					} else if (res == 0) {
						if ($(this).find("p").html() && $(this).find("p").html().localeCompare(etab.code) != 0) {
							$(this).before(li);
							$("#stm_camp_etabs ul").listview( "refresh" );
						}
						insert = true;
						return false;
					}
				});			
				if (!insert) {
					$("#stm_camp_etabs ul").append(li);
					$("#stm_camp_etabs ul").listview( "refresh" );
				}
			} else {
				$("#stm_camp_etabs ul").append(li);
				$("#stm_camp_etabs ul").listview( "refresh" );
			}
			this.listEtabs.push(etab.id);
		}
		$('#stm_camp_etabs_nb').empty();
		$('#stm_camp_etabs_nb').append($('#stm_camp_etabs ul li').length + ' schools');
		$("#stm_camp_etabs ul li a").bind("click", function() {
			if (currentEtabId != $(this).attr("id_etab")) {
				currentEtabId = $(this).attr("id_etab");
				display_user_con_form("#p_etab");
				stmPageEtab.chargeData();
				stmPageEtab.setSys($('#select-choice-sys').val());
				stmPageEtab.displayEtabIdent();		
				currPage = 2;
			}
		});
	},
	
	goToQst : function(etabId, idQst) {
		currentEtabId = etabId;
		display_user_con_form("#p_etab");
		stmPageEtab.chargeData();
		stmPageEtab.setSys($('#select-choice-sys').val());
		stmPageEtab.displayEtabIdent(idQst);		
	}
}