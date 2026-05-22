// JavaScript Document

// Class Question
function StmQuestion(id, title, idCamp, idSys, filter, html) {
	
	this.id = id;
	this.title = title;
	this.idcamp = idCamp;
	this.idsys = idSys;
	this.filter = filter;
	this.html = html;
	this.chargedRules = null;
	
	this.init = function(qst) {
		this.id = qst.id;
		this.title = qst.title;
		this.idcamp = qst.idcamp;
		this.idsys = qst.idsys;
		this.filter = qst.filter;
		//this.html = qst.html;		
	};
	
	this.getId = function() {
		return this.id;	
	};
	
	this.getTitle = function() {
		return this.title;	
	};
	
	// Set html data
	this.setHtml = function(html) {
		this.html = html;
	};
	
	this.getHtml = function() {
		return this.html;	
	};
	
	//Charge les règles de controle de saisie
	this.loadRules =  function(id_camp) {
		this.chargedRules = new Array();
		if (existStorage('localStorage')) {
			var chargedRulesStr = window.localStorage.getItem('stm_ChargedThemeRule_'+id_camp+'_'+this.id+'_'+this.idsys);
			if (chargedRulesStr != null) {
				var rules = JSON.parse(chargedRulesStr);
				var nb = rules.length;
				for (var i=0; i<nb; i++) {
					var rule = rules[i];
					var stmRule = new StmRule();
					stmRule.init(rule);
					this.chargedRules.push(stmRule);
				}
			}
		}
	};
	
	this.validData = function(name, val) {
		var nb = this.chargedRules.length;
		var result = 1;		
		for (var i=0; i<nb; i++) {
			var stmRule = this.chargedRules[i];
			if (name.indexOf(stmRule.champ) > -1) {
				result = stmRule.testVal(val);
				if (result != 1) {
					break;	
				}
			}
		}
		return result;
	};
	
	this.isFiltered = function() {
		return this.filter == 1;
	};
}

// Class Data
function StmData(key, value) {
	
	this.key = key;
	this.value = value;
	
	// set key
	this.setKey = function(key) {
		this.key = key;
	};
	
	// get key
	this.getKey = function(key) {
		this.key = key;
	};
	
	// set value
	this.setValue = function(value) {
		this.value = value;	
	};
	
	// get value
	this.getValue = function(value) {
		this.value = value;	
	}
}

//Class Rule
function StmRule() {
	this.champ = null;
	this.type = null;
	this.taille = null;
	this.format = null;
	this.inter = null;
	this.min_val = null;
	this.max_val = null;
	this.pres = null;
	this.paru = null;
	this.obli = null;
	this.int_ref = null;
	this.edits = null;
	this.enums = null;
	this.enumsArray = null;
	this.uniq = null;
	
	this.init = function(rule) {
		this.champ = rule.champ;
		this.type = rule.type;
		this.taille = parseInt(rule.taille);
		this.format = rule.format;
		this.inter = rule.inter;
		this.min_val = parseInt(rule.min_val);
		this.max_val = parseInt(rule.max_val);
		this.pres = rule.pres;
		this.paru = rule.paru;
		this.obli = rule.obli;
		this.int_ref = rule.int_ref;
		this.edits = rule.edits;
		this.enums = rule.enums;
		this.enumsArray = rule.enums.split(',');
		this.uniq = rule.uniq;
	};
	
	this.testVal = function(val) {
		if (val.length != '') {
			if (!testType(this.type, val)) {
				return 'type_' + this.type;	
			}
			if (this.taille != '' && val.length > this.taille) {
				return 'taille_' + this.taille;
			}
			if (this.format != '' && val.macth(this.format)) {
				return 'format_' + this.format;
			}
			if (this.inter != 0 && (parseInt(val) < this.min_val) || (parseInt(val)  > this.max_val)) {
				return 'inter_[' + this.min_val + ' - ' + this.max_val + ']';
			}
		}
		//presence ?
		// paru?
		if (this.obli != 0 && (val == '')) {
			return 'obli';
		}
		//inte ref
		// edit
		if (this.enums != '' && $.inArray(val, this.enumsArray)) {
			return 'enums_' + this.enums;
		}
		// uniq
		return 1;
	};
}

function testType(type, value) {
	if (type == 'int') {
		return is_int(value);
	}
	if (type == 'date') {
		return true;
	}
	if (type == 'decimal') {
		return $.isNumeric(value);
	}
	return true;
}

function is_int(value){
	if ((parseFloat(value) == parseInt(value)) && !isNaN(value)) {
		return true;
	} else {
		return false;
	}
}