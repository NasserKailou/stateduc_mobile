<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		
		$champs	= array();
		$champs[] = array('nom'=>'ID_OLAP', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_olap]', 'lib'=>'id_olap', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_QUERY_OLAP', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_qry', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_QUERY_OLAP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_qry', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_EXECUTION_OLAP', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ord_exec', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'SQL_QUERY_OLAP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'query', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TYPE_QUERY_OLAP', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'type_req', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'COMMENTAIRE_OLAP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'comm', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= 'DICO_OLAP_QUERY';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_QUERY_OLAP';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'olap_tools/frame_gestion_olap_qry.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesOlapQry';

		
		$table->run();
		
?>

