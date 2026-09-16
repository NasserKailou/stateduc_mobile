<br/><br/>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
	$champs = array();
	$champs[] = array('nom'=>'ID_AGGREGATION', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_agg', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
	$champs[] = array('nom'=>'LIBELLE_AGGREGATION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_agg', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
	$champs[] = array('nom'=>'HIERARCHY_LEVEL_MAX', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'max_hier', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
	$champs[] = array('nom'=>'HIERARCHY_LEVEL', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'hier', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
	
	$table 						= new gestion_table_simple() ;
	
	$table->table				= 'DICO_AGGREGATION_LEVEL';
	$table->conn				= $GLOBALS['conn_dico'];
	
	$table->champs 				= $champs;
	$table->nom_champ_combo		= 'LIBELLE_AGGREGATION';
	$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/frame_gestion_aggregations.php';
	$table->taille_ecran		= '550';
	$table->btn_quit			= false;
	$table->titre_ecran			= 'gestagg';
	
	//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
	
	//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
	
	$table->run();
		
?>

