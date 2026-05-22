// Gestion des statuts des etablissements
// Gestion des systèmes
var stmStatus = {
	
	chargedStatus : null,
	
	// Initialise la liste des statuts chargés
	init : function() {
		this.chargedStatus = new Array();
		if (existStorage('localStorage')) {
			var chargedStatusStr = window.localStorage.getItem('stm_ChargedStatus');
			if (chargedStatusStr != null) {
				var allstatus = JSON.parse(chargedStatusStr);
				var nb = allstatus.length;
				for (var i = 0; i < nb; i++) {
					var status = new StmStatus(allstatus[i].id, allstatus[i].name);
					this.addStatus(status);
				}
			}
		}
	},
	
	addStatus : function(status) {
		if (this.chargedStatus == null) {
			this.init();			
		}
		if (!this.exist(status.getName())) {
			this.chargedStatus.push(status);
		}
	},
	
	exist : function(name) {
		var filteredStatus = $.grep(this.chargedStatus, function(n) {
			return n.isEqualByName(name);
		});
		return filteredStatus.length > 0;
	},
	
	getById : function(id) {
		if (id == null) {
			id = 255;	
		}
		var filteredStatus = $.grep(this.chargedStatus, function(n) {
			return n.isEqualById(id);
		});
		return filteredStatus[0];
	},
	
	save : function() {
		if (existStorage('localStorage')) {
			var chargedStatusStr = JSON.stringify(this.chargedStatus);
			window.localStorage.setItem('stm_ChargedStatus', chargedStatusStr);
		}	
	}
};

function StmStatus (id, name) {
	//id du statut
	this.id = id;
	//nom du system
	this.name = name;
	
	this.getId = function() {
		return this.id;
	};
	
	this.getName = function() {
		return this.name;
	};
	
	this.isEqualByName = function(name) {
		return (this.name == name);	
	};
	
	this.isEqualById = function(id) {
		return (this.id == id);	
	};
}