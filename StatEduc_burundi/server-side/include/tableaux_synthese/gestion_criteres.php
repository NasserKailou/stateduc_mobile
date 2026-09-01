<br/><br/>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID_CRITERE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_crit', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LIBELLE_CRITERE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_crit', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'CHAMP_REF', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'chp_crit', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TABLE_REF', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'tab_crit', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SQL_CRITERE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sql_crit', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= 'DICO_CRITERE_FILTRE';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'LIBELLE_CRITERE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/frame_gestion_criteres.php';
		$table->taille_ecran		= '550';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'gestcrit';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

