<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_TABLE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'IdTable', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_FORM', 'type'=>'int', 'cle'=>'', 'incr'=>'1', 'val'=>'', 'lib'=>'OrdreForm', 'obli'=>'', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'$_SESSION[secteur]', 'lib'=>'Systeme', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_FORM', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'NomForm', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_TABLE_2', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'NomTable', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_PAGE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'NomPage', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NUM_LIGNES', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'Lignes', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NUM_COLONNES', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'Colonnes', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table				= 'DICO_EXCEL_AGGREGATED_TABLE';
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_FORM';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_excel_aggreg_tables.php';
		$table->taille_ecran		= '450';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'gestexceldesc';
		
		$table->run();
?>

