<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CLASSE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'rpt_classe', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ACTION_REPORT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'act_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SQL_REPORT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sql_rpt', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DEFAULT_CONNEXION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'dft_cnx', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'AUTRE_CONNEXION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'autre_cnx', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SQL_LINK_FILE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sql_file', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'RADICAL_RPT_FILE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'rpt_file', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LIBELLE_REPORT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'ID_TYPE_REPORT', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'2', 'lib'=>'type_rpt', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->table				= 'DICO_REPORT';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'LIBELLE_REPORT';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_rpt.php';
		$table->taille_ecran		= '600';
		$table->taille_combo		= '250';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'gestrpt';
		
		$table->tab_btns_popup  = array();
		
		$btn1 = array();
		$btn1['label_code'] = 'BtnRptSys';
		$btn1['fonc_js']	= 'OpenPopupAnnRptParamsSys';
		$btn1['params']		= array();
		$btn1['params'][]	= array( 'code' => 'ID', 'eval' => '' );
		$btn1['params'][]	= array( 'code' => '$_SESSION[secteur]', 'eval' => '1' );
		
		$table->tab_btns_popup[]  = $btn1;
		unset($btn1);
		
		
		$table->run();
		
?>

