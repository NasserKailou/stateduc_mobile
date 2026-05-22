// JavaScript Document

// Gestion des annees
var stmYear = {
	
	chargedYears : null,
	
	// Initialise la liste des années chargées
	init : function() {
		if (existStorage('localStorage')) {
			var chargedYearsStr = window.localStorage.getItem('stm_ChargedYears');
			if (chargedYearsStr != null) {
				var years = JSON.parse(chargedYearsStr);
				this.chargedYears = new Array();
				var nb = years.length;
				for (var i = 0; i < nb; i++) {
					var year = new StmYear(years[i].id, years[i].nom);
					this.addYear(year);
				}
			} else {
				this.chargedYears = new Array();
			}
		}
	},
	
	addYear : function(stmYear) {
		if (this.chargedYears == null) {
			this.init();			
		}
		if (!this.exist(stmYear.getId())) {
			this.chargedYears.push(stmYear);
		}
	},
	
	exist : function(id) {
		var years = $.grep(this.chargedYears, function(n) {
			return (n.getId() == id);
		});
		return years.length > 0;
	},
	
	getById : function(id) {
		var years = $.grep(this.chargedYears, function(n) {
			return (n.getId() == id);
		});
		return years[0];
	},
	
	save : function() {
		if (existStorage('localStorage')) {
			var chargedYearsStr = JSON.stringify(this.chargedYears);
			window.localStorage.setItem('stm_ChargedYears', chargedYearsStr);
		}	
	}
};

// Classe Year
function StmYear(id, nom) {
	
	this.id = id * 1;
	this.nom = nom;
	
	this.getId = function() {
		return this.id;
	};
	
	this.getNom = function() {
		return this.nom;
	}
}


// Gestion des filtres
var stmFilter = {
	
	chargedFilters : null,
	
	// Initialise la liste des filtres chargées
	init : function() {
		if (existStorage('localStorage')) {
			var chargedFiltersStr = window.localStorage.getItem('stm_ChargedFilters');
			if (chargedFiltersStr != null) {
				var filters = JSON.parse(chargedFiltersStr);
				this.chargedFilters = new Array();
				var nb = filters.length;
				for (var i = 0; i < nb; i++) {
					var filter = new StmFilter(filters[i].id, filters[i].nom);
					this.addFilter(filter);
				}
			} else {
				this.chargedFilters = new Array();
			}
		}
	},
	
	resetFilters : function() {
		this.chargedFilters = new Array(); 	
	},
	
	addFilter : function(stmFilter) {
		if (this.chargedFilters == null) {
			this.init();			
		}
		if (!this.exist(stmFilter.getId())) {
			this.chargedFilters.push(stmFilter);
		}
	},
	
	exist : function(id) {
		var filters = $.grep(this.chargedFilters, function(n) {
			return (n.getId() == id);
		});
		return filters.length > 0;
	},
	
	getById : function(id) {
		var filters = $.grep(this.chargedFilters, function(n) {
			return (n.getId() == id);
		});
		return filters[0];
	},
	
	save : function() {
		if (existStorage('localStorage')) {
			var chargedFiltersStr = JSON.stringify(this.chargedFilters);
			window.localStorage.setItem('stm_ChargedFilters', chargedFiltersStr);
		}	
	}
};


// Classe Filter
function StmFilter(id, nom, order) {
	
	this.id = id * 1;
	this.nom = nom;
	this.order = order;
	
	this.getId = function() {
		return this.id;
	};
	
	this.getNom = function() {
		return this.nom;
	};
	
	this.getOrder = function() {
		return this.order;
	}
}