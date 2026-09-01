<br><br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID_REFERENCE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_ref', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_REFERENCE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_ref', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'ID_RUBRIQUE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_rubr', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_FONCTIONNALITE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_fonc', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'RADICAL_TABLE_NAME', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'tab_name', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TYPE_REFERENCE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'type_ref', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ACTIVER_REF', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'act_ref', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->table				= 'DICO_REFERENCE';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_REFERENCE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_ref.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '200';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'Gesref';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

