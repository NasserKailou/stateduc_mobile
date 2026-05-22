// JavaScript Document
var list_new_camps = null;
var newCampsIsCharged = false;
var vars = $.getUrlVars();
var origin = vars['origin'];

// Page nouvelle campagne init
$( document ).one( "pagecreate", "#p_new_camp", function( event ) {
	$('.stm_back_on_logo_lnk').bind('click', function() {
		if (origin && origin == "1") {
			window.history.go(-1);
		} else {
			window.history.go(-2);											  	
		}
  	});
	
	$(document).on('submit', "#new_camp_frm", function(event) {
		event.preventDefault();
		if (!stmUser.isConnect()) {
			//displayPopup('You must be connected', 3000, 'p_new_camp' );
			alert('Vous devez être connecté');
			return false;
		}
		var id = $("#select-choice-camp").val();
		if (!$.isNumeric(id)) {
			//displayPopup('Please choose a data collection', 3000, 'p_new_camp' );
			alert( 'Veuillez choisir une campagne' );
		} else {
			//chargement de la campagne
			stmPageNewCamp.chargeNewCamp(id);
		}
	});
});

//Page show
$( document ).one( "pageshow", "#p_new_camp", function( event ) {	
	var userConnectCallback = function( event, ui ) {								
								if (stmUser.isConnect() && !newCampsIsCharged) {
									stmPageNewCamp.get_campagnes();
								} else if (!stmUser.isConnect() && newCampsIsCharged) {
									stmPageNewCamp.resetAll();
									$('#select-choice-camp').change();
									newCampsIsCharged = false;
								}
							};													 
	init_connexion('#p_new_camp', userConnectCallback);
	init_options('#p_new_camp');
	display_user_con_form("#p_new_camp");
	stmPageNewCamp.test_user_status();
});

var stmPageNewCamp = {
	
	chargeNewCamp : function(id_camp) {
		var camp = stmCampagnes.getById(stmCampagnes.getChargedCampType(), id_camp);
		if (camp != null) {
			showConfirm('StatEduc', 'Cette campagne est déjà chargée. Recharger?', this.chargeNewCampData);
		} else {
			stmChargeCamp.chargeNewCampData(id_camp);
		}
	},
	
	chargeNewCampData : function(buttonIndex) {
		if (buttonIndex == 2) {
			var id_camp = $('#select-choice-camp').val();
			stmChargeCamp.chargeNewCampData(id_camp);
		}
	},
	
	// Test le statut de l'utilisateur pour l'obliger à se connecter
	test_user_status : function() {
		if (existStorage('sessionStorage')) {
			var user = sessionStorage.getItem('stm_userData');
			if (!user) {
				setTimeout(function () {
					$(".stm_btn_user_lnk").trigger( "click");
				}, 1000);
			} else {
				//chargement des campagnes dispo pour cet utilisateur
				this.get_campagnes();
			}
		} else {		
			//$.mobile.loading( "hide" );
		}	
	},
	
	// recupères les nouvelles campagnes du serveur
	get_campagnes : function() {
		if (existStorage('localStorage')) {
			stmChargeCamp.initVars();
			getDataFromServer('/user_camp.php/new_camp/', currentUser.id + '/1', this.init_select);
		}
	},	
	// Initialise la liste des campagnes disponibles
	init_select : function(new_camps) {
		list_new_camps = new_camps;
		var nb = list_new_camps.length;
		if (nb == 0) {
			//Traitement du cas pas de nouvelle campagne
			return false;
		} 
		var optionsElt = "";
		$('#select-choice-camp').empty();
		$('#select-choice-camp').append('<option>Choisir</option>');
		for (var i = 0; i < nb; i++) {
			var camp = list_new_camps[i];
			stmCampagnes.addNotChargedNewCamp(camp);
			$('#select-choice-camp').append('<option value="'+ camp.id +'">'+ camp.nom +'</option>');
		}
		$('#select-choice-camp').change();
		/*
		$('#select-choice-camp').on("change", function() {
			$('#new_camp_frm_debut').empty();
			$('#new_camp_frm_fin').empty();
			$('#new_camp_frm_statut').empty();
			var id = $(this).val();
			if ($.isNumeric(id)) {
				var camp = stmCampagnes.getById(stmCampagnes.getNewCampType(), id);
				$('#new_camp_frm_debut').append(camp.debut);
				$('#new_camp_frm_fin').append(camp.fin);
				$('#new_camp_frm_statut').append(stmCampagnes.getLibelleStatut(camp.statut));
			}
		});*/
		newCampsIsCharged = true;
	}
}