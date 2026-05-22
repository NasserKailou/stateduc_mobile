// JavaScript Document
// Page settings init
$( document ).one( "pagecreate", "#p_settings", function( event ) {
	$('.stm_back_on_logo_lnk').bind('click', function() {
		window.history.go(-2);											  	
  	});
	
	$("#popupChangePin form").submit(function(event) {		
		event.preventDefault();
		var currPin = $(this).find('#current_pin').val();
		var userPin = window.localStorage.getItem('stm_UserPin');
		if (currPin != userPin) {
			$("#pin_frm_error").show();
			return false;
		}
		var pinToValidate = $(this).find('#new_pin').val();
		var reNewPin = $(this).find('#re_new_pin').val();
		if (pinToValidate == "") {		
			$("#pin_frm_error").empty();					
			return false;
		} else if (reNewPin == pinToValidate) {						
			window.localStorage.setItem('stm_UserPin', pinToValidate);
			$("#popupChangePin").popup( "close" );
			return true;
		} else {
			$("#pin_frm_error").empty();
			$("#pin_frm_error").append("Les CODEs saisis sont différents");
			$("#pin_frm_error").show();	
			return false;
		}
	});
	
	$('#btn_change_pin').on("click", function(evt) {
		$( "#popupChangePin" ).popup( "open" );
		$( "#current_pin" ).focus();
	});
	
	$("#change_sec_quest_frm").submit(function(event) {		
		event.preventDefault();
		$.mobile.loading( 'show' );
		var pinToValidate = $("#stm_user_pin").val();
		if (existStorage('localStorage')) {
			var userPin = window.localStorage.getItem('stm_UserPin');
			var userSecQuestion = $("#stm_user_remind_list").val();
			var userSecQuestionValue = $("#stm_user_remind_value").val();
			if (pinToValidate == "" || pinToValidate != userPin) {	
				$("#change_sec_quest_frm_error").empty();
				$("#change_sec_quest_frm_error").append("Le CODE saisi est invalide");
				$("#change_sec_quest_frm_error").show();
				$("#stm_user_pin").val("");
				$.mobile.loading( 'hide' );
			} else if (userSecQuestion == 'Choisir' || userSecQuestionValue == '') { 
				$("#change_sec_quest_frm_error").empty();
				$("#change_sec_quest_frm_error").append("Sélectionnez une question de sécurité et saisissez une réponse");
				$("#change_sec_quest_frm_error").show();	
				$.mobile.loading( 'hide' );					
			} else {								
				$("#change_sec_quest_frm_error").empty();
				$("#change_sec_quest_frm_error").hide();	
				window.localStorage.setItem('stm_UserSecQuestion', userSecQuestion);	
				window.localStorage.setItem('stm_UserSecQuestionValue', userSecQuestionValue);
				$('#stm_user_remind_list option:first').prop('selected', true);
				$("#stm_user_remind_value").val( '' );
				$("#stm_user_pin").val( '' );
				$.mobile.loading( 'hide' );
				alert("Saved");
				return true;
			}
		}
	});
});

//Page show
$( document ).one( "pageshow", "#p_settings", function( event ) {													 
	init_connexion('#p_settings');
	init_options('#p_settings');
	display_user_con_form("#p_settings");
	stmYear.init();
	stmPageSettings.chargeData();
});


var stmPageSettings = {
	
	chargeData : function() {
		if (existStorage('localStorage')) {
			var urlServer = localStorage.getItem('stm_UrlServer');
			$('#server_url').val(urlServer);
			$('#server_url').on("change", function(evt) {
				localStorage.setItem('stm_UrlServer', $(this).val());
			});
			/*var smsGateway = localStorage.getItem('stm_SmsGatewayNum');
			$('#sms_gateway_num').val(smsGateway);
			$('#sms_gateway_num').on("change", function(evt) {
				localStorage.setItem('stm_SmsGatewayNum', $(this).val());
			});
			var smsGatewayKey = localStorage.getItem('stm_SmsGatewayKey');
			$('#sms_gateway_key').val(smsGatewayKey);
			$('#sms_gateway_key').on("change", function(evt) {
				localStorage.setItem('stm_SmsGatewayKey', $(this).val());
			});
			var currYear = localStorage.getItem('stm_CurrYear');
			var htmlData = '<select id="year_lst" data-native-menu="false" data-mini="true">'
						+'<option>Choose year</option>';
			$.each(stmYear.chargedYears, function(index, value){
				htmlData += '<option value="'+ value.getId() +'"';
				if (currYear == value.getId()) {
					htmlData += ' selected="selected"';
				}
				htmlData +='>'+ value.getNom() +'</option>';							   
			});
			htmlData += '</select>';
			$('#curr_year_div').append(htmlData);
			$('#curr_year_div').trigger( "create" );
			$('#year_lst').change(function() {
				var currYear = this;										  
				setTimeout(function() {
					localStorage.setItem('stm_CurrYear', $(currYear).val());
				}, 200);
			});*/
		}
	},
}