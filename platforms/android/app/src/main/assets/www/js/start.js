// JavaScript Document

(function( $, undefined ) {
	stmCampagnes.init();
	stmSystems.init();
	if (typeof stmStatus != 'undefined') {
		stmStatus.init();
	}
	if (typeof stmRegroups != 'undefined') {
		stmRegroups.init();
	}
	if (typeof stmEtabs != 'undefined') {
		stmEtabs.init();
	}
})( jQuery );