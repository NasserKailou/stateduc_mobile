<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_SESSION[secteur]', 'lib'=>'Systeme', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_THEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_SESSION[theme_excel]', 'lib'=>'Systeme', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_TABLE', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'$_SESSION[table]', 'lib'=>'NomTable', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_CHAMP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'NomChamp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NUM_LIGNE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'NumLig', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NUM_COLONNE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'NumCol', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table				= 'DICO_EXCEL_TAB_FILTRE_VAL';
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_TABLE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_excel_tab_filtre_val.php';
		$table->taille_ecran		= '350';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'gestexcelfiltreval';
		
		$table->hidden_btn[] = 'btn_new';
		$table->run();
?>

