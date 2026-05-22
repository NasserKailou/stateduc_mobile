
var stmRegroups = {
	
	chargedRegroups : null,
	chargedTypeRegroups : null,
	currentLocPath : null,
	
	init : function() {
		if (existStorage('localStorage')) {
			//regroupements
			var chargedRegroupsStr = window.localStorage.getItem('stm_ChargedRegroups');
			this.chargedRegroups = new Array();
			if (chargedRegroupsStr != null) {
				var regroups = JSON.parse(chargedRegroupsStr);
				var nb = regroups.length;
				for (var i = 0; i < nb; i++) {
					var regroup = regroups[i];
					var stmRegroup = new StmRegroup(regroup.id, regroup.nom, regroup.type, regroup.parentid);
					this.chargedRegroups.push(stmRegroup);
				}
			}
			//type des regroupements
			var chargedTypeRegroupsStr = window.localStorage.getItem('stm_ChargedTypeRegroups');
			if (chargedTypeRegroupsStr != null) {
				this.chargedTypeRegroups = JSON.parse(chargedTypeRegroupsStr);
			} else {
				this.chargedTypeRegroups = new Array();	
			}
		}
	},
	
	// Verifie l'existance d'un regroupement
	existRegroup : function(id) {
		var rgps = $.grep(this.chargedRegroups, function(n) {
			return (n.id == id);
		});
		return (rgps.length > 0);
	},
	
	// Ajoute un regroupement
	addRegroup : function(regroup) {
		if (!this.existRegroup(regroup.id)) {
			var stmRegroup = new StmRegroup(regroup.id, regroup.nom, regroup.type, regroup.parentid);
			this.chargedRegroups.push(stmRegroup);
		}
	},
	
	//Retrouve un regroupement à partir de son id
	getById : function(id) {
		var rgps = $.grep(this.chargedRegroups, function(n) {
			return (n.id == id);
		});
		return rgps[0];
	},
	
	// Verifie l'existance d'un type de regroupement
	existTypeRegroup : function(typeId) {
		var typeRgps = $.grep(this.chargedTypeRegroups, function(n) {
			return (n.id == typeId);
		});
		return (typeRgps.length > 0);
	},
	
	// Ajoute un type de regroupement
	addTypeRegroup : function(typeRegroup) {
		if (!this.existTypeRegroup(typeRegroup.id)) {
			this.chargedTypeRegroups.push(typeRegroup);
		}
	},
	
	//Retourne le type d'un regroupement
	getTypeRegroup : function(typeId) {
		var typeRgps = $.grep(this.chargedTypeRegroups, function(n) {
			return (n.id == typeId);
		});
		return typeRgps[0];
	},
	
	setEtabLocs : function(locId, niv, htmlTagId) {
		if (niv == 0) {
			stmRegroups.currentLocPath = new Array();
		}
		var rgp = this.getById(locId);
		stmRegroups.currentLocPath.push(rgp.nom);
		if (rgp.parentid != -1) {
			this.setEtabLocs(rgp.parentid, 1, htmlTagId);
		} else {
			stmRegroups.currentLocPath.reverse();	
			var nb = stmRegroups.currentLocPath.length;
			for (var i = 0; i < nb; i++) {
				var nom = stmRegroups.currentLocPath[i];				
				if (i > 0) {
					$("#" + htmlTagId).append(" / ");	
				}
				$("#" + htmlTagId).append(nom);
			}
		}
	},
	
	saveRegroup : function() {
		if (existStorage('localStorage')) {
			var chargedRegroupsStr = JSON.stringify(this.chargedRegroups);
			window.localStorage.setItem('stm_ChargedRegroups', chargedRegroupsStr);
		}	
	},
	
	saveTypeRegroup : function() {
		if (existStorage('localStorage')) {
			var chargedTypeRegroupsStr = JSON.stringify(this.chargedTypeRegroups);
			window.localStorage.setItem('stm_ChargedTypeRegroups', chargedTypeRegroupsStr);
		}	
	}
};


// Classe Regroupement
function StmRegroup(id, nom, type, parentid) {
	
	this.id = id * 1;
	this.nom = nom;
	this.type = type;
	this.parentid = parentid * 1;
	
	this.getId = function() {
		return this.id;
	};
	
	this.getNom = function() {
		return this.nom;
	};
	
	this.getType = function() {
		return this.type;
	};
	
	this.getParentId = function() {
		return this.parentid;
	};
	
	// teste si le regroupement est un des fils du regroupement de id
	this.isChildOf = function(id) {
		return this.parentid == id;	
	}
}
