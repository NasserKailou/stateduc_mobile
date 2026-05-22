// JavaScript Document
var error_msg = {"type_int":"Vous devez saisir un entier", 
				 "type_date":"Vous devez saisir une date valide",
				 "type_decimal":"Vous devez saisir un décimal",
				 "taille_":"La longeur maximal de ce champ est ",
				 "format_":"Le format attendu est ",
				 "inter_":"La valeur doit être entre ",
				 "obli":"Ce champ est obligatoire",
				 "enums_":"Les valeurs autorisées sont "};
				 
				 
var stmError = {
	
	displayMsg : function(error) {
		var msg = 'Erreur de validation : ';
		if (error == 'type_int' || error == 'type_date' || error == 'type_decimal' || error == 'obli') {
			msg += error_msg[error];
		} else if (error.startsWith('taille_')) {
			var maxL = error.substring(7);
			msg += error_msg['taille_'];
			msg += maxL;
		} else if (error.startsWith('format_')) {
			var format = error.substring(7);
			msg += error_msg['format_'];
			msg += format;
		} else if (error.startsWith('inter_')) {
			var inter = error.substring(6);
			msg += error_msg['inter_'];
			msg += inter;
		} else if (error.startsWith('enums_')) {
			var enums = error.substring(6);
			msg += error_msg['enums_'];
			msg += enums;
		}
		alert(msg);
	},
};