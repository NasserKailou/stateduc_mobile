// Gestion de l'utilisateur
var stmUser = {
	
	//Current user
	user : null,
	
	//Test if user is connected
	isConnect : function () {
		if (existStorage('sessionStorage')) {
			var userStr = window.sessionStorage.getItem("stm_userData");
			if (userStr != null) {
				return true;
			} else {
				return false;	
			}
		} else {
			return true;
		}
	},
	
	//Connect a user
	connect : function (login, mdp, page_id) {
		$.mobile.loading( "show" );
		var urlServer = localStorage.getItem('stm_UrlServer');
		if (urlServer == null) {
			$.mobile.loading( "hide" );
			alert( "Erreur : veuillez vérifier l'url de votre serveur0!" );
		}
		urlServer = urlServer.substring(7);
		//Post user data
		$.ajax({type:'get', url: 'http://'+ urlServer +'/user_ident.php/user/'+ login +'/'+ mdp , data: '', 
			statusCode: {
				401: function() {
					$.mobile.loading( "hide" );
					alert( "Accès refusé" );
				},
				404: function() {
					$.mobile.loading( "hide" );
					alert( "Erreur : veuillez vérifier l'url de votre serveur!" );
				}
			},
         	headers: {	'Authorization': 'Basic ' + btoa(login+':'+mdp)	},
			success: function(response) {
				if (response.se_status == 200) {
					var userIdent = response.se_data;
					if (userIdent == '' || response.se_message == 'log_ko') {
						$.mobile.loading( "hide" );	
						alert( "Accès refusé : veuillez vérifier vos données d'identification" );
						return true;
					}
					if (existStorage('sessionStorage')) {
						sessionStorage.setItem('stm_userData', JSON.stringify(userIdent));
						sessionStorage.setItem('stm_userPass', mdp);
						this.user = userIdent;
						user_is_login(page_id);
						stmYear.init();
						var year = new StmYear(userIdent.codeyear, userIdent.libyear);
						stmYear.addYear(year);
						stmYear.save();
						window.localStorage.setItem('stm_CurrYear', userIdent.codeyear);
						if (userIdent.filter) {
							stmFilter.resetFilters();
							var nb = userIdent.filters.length;
							for (var i = 0; i < nb; i++) {
								var filter = new StmFilter(userIdent.filters[i].CODE_TYPE_PERIOD, userIdent.filters[i].NAME_TYPE_PERIOD, userIdent.filters[i].ORDER_TYPE_PERIOD);
								stmFilter.addFilter(filter);
							}
							stmFilter.save();
							window.localStorage.setItem('stm_FilterName', userIdent.filter);
						}
						if (page_id == '#p_etab' || page_id == '#p_camp') {
							stmChargeCamp.initVars();
						}
					} else {	
						$.mobile.loading( "hide" );	
					}
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				if (textStatus == 'error' && XMLHttpRequest.status != 401 && XMLHttpRequest.status != 404) {
					$.mobile.loading( "hide" );
					alert( "Erreur : veuillez vérifier l'url et ou la configuration de votre serveur!" );
				} else if (textStatus==="timeout") {
					$.mobile.loading( "hide" );
					alert( "Délais d'attente dépassé. Veuillez vérifier l'url de votre serveur!" );
				}
			},
			crossDomain:false,
			CORS: true,
			secure: true, 
			dataType:'json',
			timeout: 10000
		});		
	},
	
	// Deconnecte un utilisateur
	deconnect : function (page_id) {
		$.mobile.loading( "show" );
		var urlServer = localStorage.getItem('stm_UrlServer');
		urlServer = urlServer.substring(7);
		this.user = sessionStorage.getItem("stm_userData");
		$.ajax({type:'get', url: 'http://'+ urlServer +'/user_ident.php/logout/xxxx/xxxx' , data: '', 
			statusCode: {
				401: function() {
					if (existStorage('sessionStorage')) {
						sessionStorage.removeItem('stm_userData');
						sessionStorage.removeItem('stm_userPass');
						this.user = null;
						user_is_logout(page_id);
					} else {		
						$.mobile.loading( "hide" );
					}
				},
				404: function() {
					alert( "Erreur lors de la déconnexion" );
					$.mobile.loading( "hide" );
				}
			},
         	headers: {	'Authorization': 'Basic ' + btoa(this.user.login+':'+sessionStorage.getItem("stm_userPass")) },
			success: function(response) {
				if (existStorage('sessionStorage')) {
					sessionStorage.removeItem('stm_userData');
					sessionStorage.removeItem('stm_userPass');
					this.user = null;
					user_is_logout(page_id);
				} else {		
					$.mobile.loading( "hide" );
				}
				$.mobile.loading( "hide" );
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				//alert( "Erreur lors de la déconnexion" );
				$.mobile.loading( "hide" );
			},
			crossDomain:false,
			CORS: true,
			secure: true, 
			dataType:'json',
			timeout: 10000
		});		
	},
	
	// Renvoie l'utilisateur connecté
	getConnectedUser : function () {
		if (this.user != null) {
			return user;
		}
		return null;
	}
};