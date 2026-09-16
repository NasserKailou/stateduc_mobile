<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_olap.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_OLAP', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_olap', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_OLAP', 'type'=>'int', 'cle'=>'', 'incr'=>'1', 'val'=>'', 'lib'=>'ordre_olap', 'obli'=>'', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'THEME_NAME', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'thm_name', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		//$champs[] = array('nom'=>'CLASSE_OLAP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'olap_classe', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		//$champs[] = array('nom'=>'ACTION_OLAP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'action_olap', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SGBD', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sgbd_olap', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'USE_CURRENT_DB_CNX', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'curr_cnx', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DEFAUT_CONNEXION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'dft_cnx', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SQL_OLAP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sql_olap', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SQL_LINK_FILE_OLAP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sql_file', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ASSOCIATE_DESC_FILE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'doc_file', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ASSOCIATE_IMAGE_FILE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'img_file', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
        $champs[] = array('nom'=>'TABLE_FAITS', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'faits_table', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ASSOCIATE_OLAP_FILE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'olap_file', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
        
        
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->table				= 'DICO_OLAP';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'THEME_NAME';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'olap_tools/frame_gestion_olap_inst.php';
		$table->taille_ecran		= '600';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'gestolap';
		
		
		$table->run();        
		
?>

