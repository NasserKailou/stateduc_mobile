// JavaScript Document

var stmEtabs = {
	
	// Liste des établissements existants
	chargedEtabs : null,
	
	// Initialise la liste des établissements chargés
	init : function() {
		if (existStorage('localStorage')) {
			var chargedEtabsStr = window.localStorage.getItem('stm_ChargedEtabs');
			if (chargedEtabsStr != null) {
				var etabs = JSON.parse(chargedEtabsStr);
				this.chargedEtabs = new Array();
				var nb = etabs.length;
				for (var i = 0; i < nb; i++) {
					var etab = etabs[i];
					//var stmEtab = new StmEtab(etab.id, etab.code, etab.nom, etab.status, etab.adr, etab.tel, etab.email, etab.idregroup);
					var stmEtab = new StmEtab(etab.id, etab.code, etab.nom, etab.status, etab.idregroup);
					this.chargedEtabs.push(stmEtab);
				}
			} else {
				this.chargedEtabs = new Array();
			}
		}
	},
	
	// Verifie l'existance d'un etablissement
	existEtab : function(id) {
		var etabs = $.grep(this.chargedEtabs, function(n) {
			return (n.id == id);
		});
		return (etabs != null && etabs.length > 0);
	},
	
	// Ajoute un etablissement
	addEtab : function(etab) {
		if (!this.existEtab(etab.id)) {
			//var stmEtab = new StmEtab(etab.id, etab.code, etab.nom, etab.status, etab.adr, etab.tel, etab.email, etab.idregroup);
			var stmEtab = new StmEtab(etab.id, etab.code, etab.nom, etab.status, etab.idregroup);
			this.chargedEtabs.push(stmEtab);
		}
	},
	
	// Retrouve sun etablissement à partir de son ID
	getById : function(id) {
		if (this.chargedEtabs == null) {
			this.init();	
		}
		if (this.chargedEtabs == null) {
			return null;
		}
		var etab = $.grep(this.chargedEtabs, function(e) {
			return (e.isEqual(id));
		});
		return etab[0];
	},
	
	// Sauvegarde la liste des établissements
	save : function() {
		if (existStorage('localStorage')) {
			var etabs = new Array();
			var nb = this.chargedEtabs.length;
			for (var i=0; i < nb; i++) {
				var etab = this.chargedEtabs[i];
				//var slimEtab = new StmLiftEtab(etab.id, etab.code, etab.nom, etab.status, etab.adr, etab.tel, etab.email, etab.idRegroup);
				var slimEtab = new StmLiftEtab(etab.id, etab.code, etab.nom, etab.status, etab.idRegroup);
				etabs.push(slimEtab);
			}
			var chargedEtabsStr = JSON.stringify(etabs);
			window.localStorage.setItem('stm_ChargedEtabs', chargedEtabsStr);
		}	
	}
	
};

// Class etablissement simplifiée
function StmLiftEtab(id, code, nom, status, idregroup) {
	this.id = id;
	this.code = code;
	this.nom = nom;
	this.status = status;
	this.adr = "";
	this.tel = "";
	this.email = "";
	this.idregroup = idregroup;
}

// Classe etablissement
function StmEtab(id, code, nom, status, idRgp) {
	this.id = id;
	this.code = code;
	this.nom = nom;
	this.status = status;
	this.adr = "";
	this.tel = "";
	this.email = "";
	this.idRegroup = idRgp;
	this.datas = new Array();
	
	this.getId = function () {
		return this.id;	
	};
		
	this.getRegroup = function () {
		return this.idRegroup;	
	};
	
	// test s'il s'agit de l'etablissement recherché
	this.isEqual = function(id) {
		return this.id == id;	
	};
	
	// test si l'etablissement est dans un regroupement donnée
	this.inRegroup = function(idRgp) {
		return this.idRegroup == idRgp;
	};
	
	// get collected data
	this.getData = function(idQst) {
		return this.datas;
		/*var qstData = $.grep(this.datas, function(d) {
			return (d.inQst(idQst));
		});
		return qstData;*/
	};
	
	this.addData = function(data) {
		var exist = false;
		$.each(this.datas, function(index, value) {
			if (value.isEqual(data)) {
				value.setValue(data.getValue());
				exist = true;
			}
			return !exist;
		});
		if (!exist) {
			this.datas.push(data);
		}
	};
	
	this.chargeData = function(datas) {
		var curr = this;
		$.each(datas, function(index, value) {
			var stmCollectData = new StmCollectData();
			stmCollectData.init(value);
			curr.addData(stmCollectData);
		});
	};
	
	this.loadCollectData = function(id_qst, id_filter) {
		if (existStorage('localStorage')) {
			var key = 'stm_EtabCollectData_'+this.id+'_'+id_qst;			
			if (id_filter != null) {
				key += '_' + id_filter;
			}
			var collectDataStr = window.localStorage.getItem(key);
			if (collectDataStr != null) {
				var colDatas = JSON.parse(collectDataStr);
				this.datas = new Array();
				var nb = colDatas.length;
				for (var i = 0; i < nb; i++) {
					var colData = colDatas[i];
					var stmCollectData = new StmCollectData(colData.qst, colData.key, colData.value, colData.type);
					this.datas.push(stmCollectData);
				}
			} else {
				this.datas = new Array();
			}
		}	
	};
	
	this.saveData = function(id_qst, displayMsg, id_filter) {
		if (existStorage('localStorage')) {
			var key = 'stm_EtabCollectData_'+this.id+'_'+id_qst;			
			if (id_filter != null) {
				key += '_' + id_filter;
			}
			var qstDatas = $.grep(this.datas, function(d) {
				return (d.inQst(id_qst));
			});
			window.localStorage.setItem(key, JSON.stringify(qstDatas));
			if (displayMsg) {
				alert('Données sauvegardées');
			}
		}
	},
	
	this.deleteData = function(idQst, key) {
		var stmCollectData = new StmCollectData(idQst, key, "", "");
		var okDatas = $.grep(this.datas, function(d) {
			return (!d.isEqual(stmCollectData));
		});
		this.datas = okDatas;
	},
	
	this.deleteQstData = function(idQst) {
		var okDatas = $.grep(this.datas, function(d) {
			return (!d.inQst(idQst));
		});
		this.datas = okDatas;
		if (existStorage('localStorage')) {
			window.localStorage.removeItem('stm_EtabCollectData_'+this.id+'_'+idQst);
		}
	},
	
	this.isEmpty = function(idQsts, idFilters) {
		var isEmpty = true;
		var etab = this;
		$.each(idQsts, function (idx, idQst) {
			if (existStorage('localStorage')) {
				var key = 'stm_EtabCollectData_'+etab.id+'_'+idQst;	
				$.each(idFilters, function (idxF, idFilter) {
					if (id_filter != null) {
						key += '_' + idFilter;
					}
					var collectDataStr = window.localStorage.getItem(key);
					if (collectDataStr != null) {
						isEmpty = false;
						return isEmpty;
					}
				});
			}
		});
		return isEmpty;
	}
}

// Classe contenant les données collectées
function StmCollectData(qst, key, value, type) {
	this.qst = qst;
	this.key = key;
	this.value = value;
	this.type = type;
	
	this.init = function(data) {
		this.qst = data.qst;
		this.key = data.key;
		this.value = data.value;	
		this.type = data.type;
	};
	
	this.getKey = function() {
		return this.key;	
	};
	
	this.getType = function() {
		return this.type;	
	};
	
	this.getValue = function() {
		return this.value;	
	};
	
	this.setValue = function(value) {
		this.value = value;	
	};
	
	this.getQst = function() {
		return this.qst;	
	};
	
	this.isEqual = function(data) {
		return (this.qst == data.getQst()) && (this.key == data.getKey());
	};
	
	this.inQst = function(qstId) {
		return (this.qst == qstId);
	};
}