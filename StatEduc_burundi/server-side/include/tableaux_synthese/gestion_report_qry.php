<br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		
		$champs	= array();
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_rpt]', 'lib'=>'id_rpt', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_QUERY', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_qry', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'2');
		$champs[] = array('nom'=>'NOM_QUERY', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_qry', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_EXECUTION', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ord_exec', 'obli'=>'', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'SQL_QUERY', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'query', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TYPE_QUERY', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'type_req', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'COMMENTAIRE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'comm', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= 'DICO_QUERY';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_QUERY';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/frame_gestion_report_qry.php';
		$table->taille_ecran		= '600';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesRptQry';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

