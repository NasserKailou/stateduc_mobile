// JavaScript Document
// Page accueil init
$( document ).one( "pagecreate", "#p_accueil", function( event ) {
	init_options('#p_accueil');
});

//Page accueil show
$( document ).one( "pageshow", "#p_accueil", function( event ) {
	stmPageIndex.initFormElt();
});

var stmPageIndex = {
	
	initFormElt : function() {
		if (window.localStorage.getItem('stm_UserPin') == null) {
			$("#div_user_re_pin").show();
			$("#div_user_remind_list").show();
			$("#div_user_remind_value").show();
			$("#div_user_pin_forgotten").hide();
			$("#div_user_pin_forgotten_back").hide();
		} else if (sessionStorage.getItem('stm_UserPin') != null) {
			stmPageIndex.showNextPage();
			return;
		} else {
			$("#stm_user_op	").val("1");
		}
		$("#stm_user_pin_forgotten").click(function() {
			$("#pin_frm_error").empty();
			$("#div_user_pin").hide();
			$("#div_user_pin_forgotten").hide();
			$("#div_user_remind_list").show();
			$("#stm_user_remind_list option:first").prop('selected', true);
			$("#stm_user_remind_list").trigger("change");
			$("#div_user_remind_value").show();
			$("#div_user_pin_forgotten_back").show();
			$("#stm_user_op	").val("2");
		});		
		$("#stm_user_pin_forgotten_back").click(function() {
			$("#pin_frm_error").empty();
			$("#div_user_pin").show();
			$("#div_user_pin_forgotten").show();
			$("#div_user_remind_list").hide();
			$("#div_user_remind_value").hide();
			$("#div_user_pin_forgotten_back").hide();
			$("#stm_user_op	").val("1");
		});
		
		$("#pin_frm").submit(function(event) {
			event.preventDefault();
			$.mobile.loading( 'show' );
			var pinToValidate = $("#stm_user_pin").val();
			if (existStorage('localStorage')) {
				var userPin = window.localStorage.getItem('stm_UserPin');
				var userSecQuestion = $("#stm_user_remind_list").val();
				var userSecQuestionValue = $("#stm_user_remind_value").val();
				if (userPin != null) {
					if ($("#stm_user_op	").val() == 2) {
						if (userSecQuestion == window.localStorage.getItem('stm_UserSecQuestion') && userSecQuestionValue == window.localStorage.getItem('stm_UserSecQuestionValue')) {
							alert('Votre CODE est "'+userPin+'"');	
							$('#stm_user_remind_list option:first').prop('selected', true);
							$("#stm_user_remind_value").val( '' );
							$("#pin_frm_error").empty();
							$.mobile.loading( 'hide' );
						} else {
							$("#pin_frm_error").empty();
							$("#pin_frm_error").append("Question de s&eacute;curit&eacute; incorrecte");
							$("#pin_frm_error").show();	
							$.mobile.loading( 'hide' );
						}
					} else if (userPin == pinToValidate) {				
						window.sessionStorage.setItem('stm_UserPin', pinToValidate);
						sessionStorage.removeItem('stm_userData');
						stmPageIndex.showNextPage();
						return true;
					} else {
						$("#pin_frm_error").empty();
						$("#pin_frm_error").append("Le CODE saisi est invalide");
						$("#pin_frm_error").show();
						$("#stm_user_pin").val("");
						$.mobile.loading( 'hide' );
					}
				} else {
					if (pinToValidate == "") {							
						$.mobile.loading( 'hide' );
					} else if ($("#stm_user_re_pin").val() != pinToValidate) {
						$("#pin_frm_error").empty();
						$("#pin_frm_error").append("Les CODEs saisies sont diff&eacute;rents");
						$("#pin_frm_error").show();	
						$.mobile.loading( 'hide' );
					} else if (userSecQuestion == 'Choose' || userSecQuestionValue == '') { 
						$("#pin_frm_error").empty();
						$("#pin_frm_error").append("S&eacute;lectionner une question de s&eacute;curit&eacute; et saisissez une r&eacute;ponse");
						$("#pin_frm_error").show();	
						$.mobile.loading( 'hide' );					
					} else {						
						window.sessionStorage.setItem('stm_UserPin', pinToValidate);				
						window.localStorage.setItem('stm_UserPin', pinToValidate);				
						window.localStorage.setItem('stm_UserSecQuestion', userSecQuestion);	
						window.localStorage.setItem('stm_UserSecQuestionValue', userSecQuestionValue);
						sessionStorage.removeItem('stm_userData');
						stmPageIndex.showNextPage();
						return true;
					}
				}
			}
			
			return false;
		});	
	},
	
	// Show settings page if server url is not settings
	showNextPage : function() {		
		var urlServer = localStorage.getItem('stm_UrlServer');	
		if (urlServer == null || urlServer.length == 0) {
			change_page("#p_acc_settings");	
			//$( ":mobile-pagecontainer" ).pagecontainer( "change", "#p_acc_settings");
		} else {
			change_page("#p_lst_camps");	
		}
	}
	
};

// Page settings create
$( document ).one( "pagecreate", "#p_acc_settings", function( event ) {
	$('#stm_btn_settings_ok').bind('click', function() {
		stmPageIndex.showNextPage();	
		return true;
  	});
});

//Page settings show
$( document ).one( "pageshow", "#p_acc_settings", function( event ) {													 
	init_connexion('#p_acc_settings');
	init_options('#p_acc_settings');
	display_user_con_form("#p_acc_settings");
	$('#server_url').on("change", function(evt) {
		localStorage.setItem('stm_UrlServer', $(this).val());
	});
	$('#sms_gateway_num').on("change", function(evt) {
		localStorage.setItem('stm_SmsGatewayNum', $(this).val());
	});
	$('#sms_gateway_key').on("change", function(evt) {
		localStorage.setItem('stm_SmsGatewayKey', $(this).val());
	});
});

// Page data collection list create
$( document ).one( "pagecreate", "#p_lst_camps", function( event ) {
});

//Page data collection list show
$( document ).one( "pageshow", "#p_lst_camps", function( event ) {												  
	init_connexion('#p_lst_camps');
	init_options('#p_lst_camps');
	display_user_con_form("#p_lst_camps");
	stmPageLstCamps.displayChargedCamps();
});

var stmPageLstCamps = {
	
	createCampCollapsible : function (camps, title) {
		if (camps ==  null || camps.length == 0) {
			//Traitement du cas pas de nouvelle campagne
			return '';
		}
		var nb = camps.length;
		var htmlData = '<div data-role="collapsible" class="div_collapsible" data-collapsed-icon="carat-d" data-expanded-icon="carat-u" data-collapsed="false">'
							+'<h3>' + title + '</h3>'
							+'<ul class="stm_list" data-role="listview" data-filter="true" data-filter-placeholder="Trouver une campagne..." data-inset="false">';		
		for (var i = 0; i < nb; i++) {
			var camp = camps[i];
			htmlData += '<li>'
							+'<a href="camp.html?id='+ camp.id +'&type=' + camp.statut +'" data-ajax="false">'
								+'<div class="stm_list_title camp_title">' + camp.nom + '</div>'
								+'<!--p>' + camp.debut + ' - ' + camp.fin + '</p-->'
							+'</a>'
						+'</li>';
		}
		htmlData += '</ul>'
					+'</div>';
		return htmlData;			
	},
	
	
	displayChargedCamps : function () {
		$('#stm_liste_camps').empty();
		var noData = true;
		var camps = stmCampagnes.getChargedCampsByStatut(stmCampagnes.getCampOpenStatut());
		if (camps && camps.length > 0) {
			noData = false;
		}
		$('#stm_liste_camps').append(this.createCampCollapsible(camps, 'Campagnes de collecte en cours'));
		/*
		camps = stmCampagnes.getChargedCampsByStatut(stmCampagnes.getCampActiveStatut());
		if (camps && camps.length > 0) {
			noData = false;
		}
		$('#stm_liste_camps').append(this.createCampCollapsible(camps, 'Future Data collections'));
		
		camps = stmCampagnes.getChargedCampsByStatut(stmCampagnes.getCampCloseStatut());
		if (camps && camps.length > 0) {
			noData = false;
		}
		$('#stm_liste_camps').append(this.createCampCollapsible(camps, 'Closed Data collections'));
		*/
		if (noData) {
			$('#stm_no_camps').show();
		} else {		
			$('#stm_liste_camps').trigger( "create" );
			//$('#stm_liste_camps .div_collapsible:nth-child(2)').collapsible( "collapse" );
			//$('#stm_liste_camps .div_collapsible:nth-child(3)').collapsible( "collapse" );
		}
	}
};