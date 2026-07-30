<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_rpt]', 'lib'=>'id_rpt', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_DIMENSION', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_dim]', 'lib'=>'id_dim', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'1');
		$champs[] = array('nom'=>'TYPE_DIMENSION', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'type_dim', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DIM_LIBELLE_ENTETE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ent_dim', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= 'DICO_DIMENSION_REPORT';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'DIM_LIBELLE_ENTETE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/frame_gestion_report_dim.php';
		$table->taille_ecran		= '500';
		$table->btn_quit			= false;
		$table->btn_ann				= false;
		$table->hidden_btn			= array('btn_new');
		$table->titre_ecran			= '';
		$table->is_list				= true;
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

