<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_user_report.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>easyResponsiveTabs.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CLASSE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'rpt_classe', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ACTION_REPORT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'act_rpt', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SQL_REPORT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sql_rpt', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DEFAULT_CONNEXION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'dft_cnx', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'AUTRE_CONNEXION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'autre_cnx', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SQL_LINK_FILE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sql_file', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'RPT_ASSOCIATE_FILE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'rpt_file', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LIBELLE_REPORT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'ID_TYPE_PRESENTATION', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'type_pres', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_TYPE_REPORT', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'type_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= 'DICO_REPORT';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'LIBELLE_REPORT';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/frame_gestion_user_report.php';
		$table->taille_ecran		= '600';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'gestrpt';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>