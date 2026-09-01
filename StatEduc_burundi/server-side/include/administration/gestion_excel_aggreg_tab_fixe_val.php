<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_SESSION[secteur]', 'lib'=>'Systeme', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_TABLE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_table]', 'lib'=>'IdTable', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_CHAMP', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'NomChamp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'VAL_CHAMP', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ValChamp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table				= 'DICO_EXCEL_AGGREG_TAB_FIXE_VAL';
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_CHAMP';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_excel_aggreg_tab_fixe_val.php';
		$table->taille_ecran		= '350';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'gestexcelfixeval';
		
		$table->run();
?>

