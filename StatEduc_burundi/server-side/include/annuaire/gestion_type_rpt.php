<br><br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID_TYPE_REPORT', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_typ_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LIBELLE_TYPE_REPORT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_typ_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_TYPE_REPORT', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ord_typ_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->table				= 'DICO_TYPE_REPORT';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'LIBELLE_TYPE_REPORT';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_type_rpt.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesTypRpt';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

