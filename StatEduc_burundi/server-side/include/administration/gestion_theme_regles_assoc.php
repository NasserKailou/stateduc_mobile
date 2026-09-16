<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_ASSOC_REG_THM', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'is_assoc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_REGLE_THEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_regle_theme]', 'lib'=>'regth', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_REGLE_THEME_ASSOC', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'regthasc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'CRITERE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'crit', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ACTIVER_CTRL', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'1', 'lib'=>'activ', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table				= 'DICO_REGLE_THEME_ASSOC';
		$table->table_trad			= 'DICO_REGLE_THEME'; // a changer 
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'ID_REGLE_THEME_ASSOC';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gest_thm_regles_asc.php';
		$table->code_libelle_trad	= 'ID_REGLE_THEME_ASSOC'; // a changer

		//$table->tab_champs_trad['ID_REGLE_THEME_ASSOC'] = array('table'=>'DICO_REGLE_THEME', 				'libelle'=>'LIBELLE_TRAD');
		$table->tab_champs_trad['ID_ASSOC_REG_THM'] 		= array('table'=>'DICO_REGLE_THEME_ASSOC', 	'libelle'=>'MESSAGE_ALERT_THM');
		
		$table->run();
?>

