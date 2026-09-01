<br/><br/>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_REGLE_THEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'idregth', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_THEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_theme]', 'lib'=>'idth', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_REGLE_THEME', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ordregth', 'obli'=>'', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'SQL_REGLE_THEME', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sqlregth', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$table 										= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table							= 'DICO_REGLE_THEME';
		$table->champs 						= $champs;
		$table->nom_champ_combo		= 'ID_REGLE_THEME';
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_theme_regles.php';
		$table->code_libelle_trad	= 'ID_REGLE_THEME';
		
		$table->tab_champs_trad['ID_REGLE_THEME'] = array('table' => 'DICO_REGLE_THEME', 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
?>

