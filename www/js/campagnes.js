//Utilisé pour la gestion des campagnes chargées
var stmCampagnes = {
	
	// Liste des campagnes chargées
	chargedCamps : null,
	chargedCampsIds : null,
	// Liste des nouvelles campagnes
	newCamps : null,
	
	status : {1 : "&agrave; venir", 2 : "en cours", 3 : "ferm&eacute;es"},
	
	getNewCampType : function() {
		return 1;
	},
	
	getChargedCampType : function() {
		return 2;
	},
	
	getLibelleStatut : function(num) {
		return this.status[num];
	},
	
	getCampActiveStatut : function() {
		return 1;
	},
	
	getCampOpenStatut : function() {
		return 2;
	},
	
	getCampCloseStatut : function() {
		return 3;
	},
	
	// Initialise la liste des campagnes chargées
	init : function() {
		if (existStorage('localStorage')) {
			var chargedCampsStr = window.localStorage.getItem('stm_ChargedCamps');
			if (chargedCampsStr != null) {
				this.chargedCamps = JSON.parse(chargedCampsStr);
			} else {
				this.chargedCamps = new Array();
				this.chargedCampsIds = '';
			}
		}
	},
	
	// Renvoie la liste des campagnes chargées en fonction du statut
	getChargedCampsByStatut : function(statut) {
		if (this.chargedCamps == null) {
			this.init();	
		}
		if (this.chargedCamps == null) {
			return null;
		}
		var camps = $.grep(this.chargedCamps, function(n) {
			return (n.statut == statut);
		});
		return camps;
	},
	
	// Id des campagnes déjà chargées
	getChargedCampsIds : function() {
		if (this.chargedCamps == null) {
			this.init();	
		}
		if (this.chargedCamps == null) {
			this.chargedCampsIds = '';
			return '';
		}
		var ids = this.chargedCamps[0].id;
		var nb = this.chargedCamps.length;
		for (var i = 1; i < nb; i++) {
			ids += ',' + this.chargedCamps[i].id;
		}
		this.chargedCampsIds = ids;
		return ids;
	},
	
	// ajoute une nouvelle campagne chargée
	addNewCamp : function(camp) {
		if (camp == null) {
			return false;
		}
		if (this.chargedCamps == null) {
			this.init();
		}
		var exist = false;
		$.each(this.chargedCamps, function(index, value) {
			if (value.id == camp.id) {
				value.nom = camp.nom;
				value.debut = camp.debut;
				value.fin = camp.fin;
				value.statut = camp.statut;
				value.typeRegroups = camp.typeRegroups;
				exist = true;
			}
			return !exist;
		});
		if (!exist) {
			this.chargedCamps.push(camp);
		}
		this.deleteNotChargedNewCamp(camp.id);
		this.chargedCampsIds += ','+camp.id;
		this.saveCamps();
	},
	
	// ajoute une nouvelle campagne chargée
	addNotChargedNewCamp : function(camp) {
		if (camp == null) {
			return false;
		}
		if (this.newCamps == null) {
			this.newCamps = new Array();
		}
		this.newCamps.push(camp);
	},
	
	deleteNotChargedNewCamp : function(id) {
		if (this.newCamps != null) {
			for (var i = 0; i < this.newCamps.length; i++) {
				if (this.newCamps[i].id == id) {break}
			}
			this.newCamps.splice(i, 1);
		}
	},
	
	// Retrouve les nouvelles campagnes du serveur.
	getNewCamps : function() {
		return this.newCamps;
	},
		
	//Retrouve une campagne à partir de son ID et son type (new : 1, chargee : 2)
	getById : function(type, camp_id) {
		var liste = null;
		if (type == this.getNewCampType()) {
			liste = this.newCamps;
		} else if (type == this.getChargedCampType()) {
			liste = this.chargedCamps;
		} else {
			return null;
		}
		var camps = $.grep(liste, function(n) {
			return (n.id == camp_id);
		});
		return camps[0];
	},
	
	deleteChargedCamp : function(id) {
		if (this.chargedCamps != null) {
			for (var i = 0; i < this.chargedCamps.length; i++) {
				if (this.chargedCamps[i].id == id) {break}
			}
			this.chargedCamps.splice(i, 1);
			this.saveCamps();
		}
	},
	
	saveCamps : function() {
		var chargedCampsStr = JSON.stringify(this.chargedCamps);
		window.localStorage.setItem('stm_ChargedCamps', chargedCampsStr);
	}
	
};

// Classe Campagne
function StmCampagne(id, nom, debut, fin, statut, typeRegroups) {
	
	this.id = id;
	this.nom = nom;
	this.debut = debut;
	this.fin = fin;
	this.statut = statut;
	this.typeRegroups = typeRegroups;
	
	// Chaines de localisation
	this.locs = new Array();
	// Questionnaires
	this.qsts = new Array();
	
	this.init = function(camp) {
		this.id = camp.id;
		this.nom = camp.nom;
		this.debut = camp.debut;
		this.fin = camp.fin;
		this.statut = camp.statut;
		this.typeRegroups = camp.typeRegroups;
	};

	this.addLoc = function(loc) {
		this.locs.push(loc);
	};
	
	// charge les chaines de localisation pour une campagne
	this.chargeLocs = function() {
		if (existStorage('localStorage')) {
			var chargedLocsStr = window.localStorage.getItem('stm_ChargedLocs_'+this.id);
			if (chargedLocsStr != null) {
				var campLocs = JSON.parse(chargedLocsStr);
				var nb = campLocs.length;
				for (var i=0; i < nb; i++) {
					var stmLoc = new StmLocalisation();
					stmLoc.init(campLocs[i]);
					this.locs.push(stmLoc);
				}
			} else {
				this.locs = new Array();
			}
		}	
	};
	
	// supprime les informations sur une campagne
	this.deleteCamp = function() {
		stmCampagnes.deleteChargedCamp(this.id);
		var curr = this;
		$.each(this.qsts, function(idx, loc) {			
			localStorage.removeItem('stm_ChargedQstHtml_'+curr.id+'_'+this.getId());
		});
		localStorage.removeItem('stm_ChargedQst_'+this.id);
		localStorage.removeItem('stm_ChargedLocs_'+this.id);
	};
	
	// renvoie les systemes liés à une campagne
	this.getSystems = function() {
		var systems = new Array();
		$.each( this.locs, function( index, value ) {
			var camps = $.grep(systems, function(n) {
				return (n.id == value.getSystem().getId());
			});
			if (camps == null || camps.length == 0) {
				systems.push(value.getSystem());
			}
		});
		return systems;
	};
	
	// Renvoie la liste des regroupements fils d'un regroupement dans un système donné
	this.getRegroups = function(parentId, systemId) {
		var rgps = new Array();
		$.each( this.locs, function( index, value ) {
			if (value.getSystemId() == systemId) {
				var lst = value.getChildrenRegroup(parentId);
				if (lst != null) {
					$.merge(rgps, lst);
					rgps = $.unique(rgps);
				}
			}
		});
		return rgps;
	};
	
	// Renvoit le type d'un regroupement suivant son niveau
	this.getTypeRegroup = function (niv) {
		if (this.typeRegroups == null || niv >= this.typeRegroups.length) {
			return "";	
		}
		return stmRegroups.getTypeRegroup(this.typeRegroups[niv]).nom;
	};
	
	// True si regroupement en bout et false sinon
	this.isLastRegroup = function(typeId) {
		if (typeId == this.typeRegroups[this.typeRegroups.length -1]) {
			return true;
		}
		return false;
	};
	
	// Renvoie la liste des établissement d'un regroupement dans un système donné
	this.getRegroupEtabs = function(idRegroup, systemId) {
		var etabs = new Array();
		$.each( this.locs, function( index, value ) {
			if (value.getSystemId() == systemId) {
				var lst = value.getFinalRegroupEtabs(idRegroup);
				if (lst != null) {
					const etabsLst = [...new Set([...etabs, ...lst])];
					etabs = [...etabsLst];
					//$.merge(etabs, lst);
					//etabs = $.unique(etabs);
				}
			}
		});
		return etabs;	
	};
	
	// Charge les thèmes d'une campagne pour un système donné
	this.chargeQst = function(idSys) {		
		this.qsts = new Array();	
		if (existStorage('localStorage')) {
			var chargedQstStr = window.localStorage.getItem('stm_ChargedQst_'+this.id+'_'+idSys);
			if (chargedQstStr != null) {
				var campQsts = JSON.parse(chargedQstStr);
				var nb = campQsts.length;
				for (var i=0; i < nb; i++) {
					var stmQst = new StmQuestion();
					stmQst.init(campQsts[i]);
					stmQst.loadRules(this.id);
					var html = window.localStorage.getItem('stm_ChargedQstHtml_'+this.id+'_'+stmQst.getId()+'_'+idSys);
					stmQst.setHtml(JSON.parse(html));
					this.qsts.push(stmQst);
				}
				return true;
			} else {			
				alert("Il n'y a aucun écran de saisi pour ce secteur");
				return false;
			}
		}		
	};
	
	this.getQst = function(idQst) {
		var filterQsts = $.grep(this.qsts, function(n) {
			return (n.id == idQst);
		});
		return filterQsts[0];	
	};
	
	this.getTotalQst = function() {
		return this.qsts.length;	
	};
	
	this.getQsts = function() {
		return this.qsts;	
	};
	
	this.setHtml = function(idQst, html) {
		var filterQsts = $.grep(this.qsts, function(n) {
			return (n.id == idQst);
		});
		filterQsts[0].setHtml(html);
	}
}

// Classe Localisation
function StmLocalisation() {
	// id de la localisation
	this.idLoc = null;
	// id de la campagne
	this.idCamp = null;
	// id du systeme d'enseignement
	this.idSystem = null;
	// liste des etablissements
	this.lstEtabs = null;
	// chaine de localisation
	this.regroups = null;

	this.setCampId = function(id) {
		this.idCamp = id;	
	};
	
	this.getCampId = function() {
		return this.idCamp;	
	};
	
	this.setSystemId = function(id) {
		this.idSystem = id;	
	};
	
	this.getSystemId = function() {
		return this.idSystem;	
	}
	
	this.getSystem = function() {
		return stmSystems.getById(this.idSystem);
	};
	
	// lsLoc = local storage localisation
	this.init = function(lsLoc) {
		this.idLoc = lsLoc.idloc;
		this.idCamp = lsLoc.idcamp;
		this.idSystem = lsLoc.idsys;
		var idRgps = lsLoc.regroups.split(',');
		var currLoc = this;
		$.each(idRgps, function(index, value) { 		    
			var stmRgp = stmRegroups.getById(value);
			if (stmRegroups) {
				currLoc.addRegroup(stmRgp);
			}
		});
		var idEtabs = lsLoc.etabs.split(',');
		$.each(idEtabs, function(index, value) { 		    
			var stmEtab = stmEtabs.getById(value);
			currLoc.addEtab(stmEtab);
		});
	};
	
	// ajoute un etablissement
	this.addEtab = function(etab) {
		if (etab == null) {
			return;	
		}
		if (this.lstEtabs == null) {
			this.lstEtabs = new Array();
		}
		this.lstEtabs.push(etab);
	};
	
	// retrouve un etablissement à partir de son index dans la liste
	this.getEtabByIndex = function(index) {
		if (this.lstEtabs == null || this.lstEtabs.length < index) {
			return null;
		}
		return this.lstEtabs[index];
	};
	
	// retrouve un etablissement à partir de son id
	this.getEtabById = function(id) {
		if (this.lstEtabs == null || this.lstEtabs.length == 0) {
			return null;
		}
		var etab = $.grep(this.lstEtabs, function(e) {
			return e.isEqual(id);
		});
		return etab[0];
	};
	
	this.getFinalRegroupEtabs = function (id) {
		// appel une fonction recursive qui met à jour une liste
		if (this.lstEtabs == null || this.lstEtabs.length == 0) {
			return null;
		}
		var etabs = $.grep(this.lstEtabs, function(e) {
			return e.inRegroup(id);
		});
		return etabs;
	};
	
	// ajoute un regroupement à la chaine
	this.addRegroup = function(regroup) {
		if (this.regroups == null) {
			this.regroups = new Array();
		}
		this.regroups.push(regroup);
	};
	
	// retrouve les regroupements fils d'un regroupement donné
	this.getChildrenRegroup = function(parentId) {
		var nb = this.regroups.length;
		var result = new Array();
		for (var i = 0; i < nb; i++) {
			if (this.regroups[i].isChildOf(parentId)) {
				result.push(this.regroups[i]);
			}
		}
		return result;
	}
}
