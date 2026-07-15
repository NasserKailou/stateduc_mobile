<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'SGBD', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'key_sgbd', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'PROVIDER', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'prov', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= 'DICO_OLAP_TYPE_SGBD';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'SGBD';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'olap_tools/frame_gestion_olap_sgbd.php';
		$table->taille_ecran		= '600';
		$table->taille_combo		= '100';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'type_sgbd';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

