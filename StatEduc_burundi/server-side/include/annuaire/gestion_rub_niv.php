<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
	
	$table 				= new gestion_table_simple() ;
	$table->conn		= $GLOBALS['conn_dico'];

	// récupération des secteurs 
	$requete            = ' SELECT SYSTEME.ID_SYSTEME as id_systeme,
							SYSTEME.LIBELLE_SYSTEME as libelle_systeme
							FROM SYSTEME;';
	$GLOBALS['all_systemes'] = $GLOBALS['conn_dico']->GetAll($requete);
	
	( isset($_GET['id_syst']) ) ? ( $GLOBALS['id_syst'] = $_GET['id_syst'] ) : ( $GLOBALS['id_syst'] = '' ) ;
	
	
	// récupération des aggrégations 
	$requete            = ' SELECT ID_AGGREGATION, LIBELLE_AGGREGATION 
							FROM DICO_AGGREGATION_LEVEL
							ORDER  BY LIBELLE_AGGREGATION;';
	$GLOBALS['all_agg'] = $GLOBALS['conn_dico']->GetAll($requete);
	
	( isset($_GET['id_agg']) ) ? ( $GLOBALS['id_agg'] = $_GET['id_agg'] ) : ( $GLOBALS['id_agg'] = '' ) ;
	
	// récupération des rubriques 
	$requete            = ' SELECT ID_RUBRIQUE, LIBELLE_RUBRIQUE 
							FROM DICO_RUBRIQUE
							ORDER  BY LIBELLE_RUBRIQUE;';
	$GLOBALS['all_rub'] = $GLOBALS['conn_dico']->GetAll($requete);
	
	( isset($_GET['id_rub']) ) ? ( $GLOBALS['id_rub'] = $_GET['id_rub'] ) : ( $GLOBALS['id_rub'] = '' ) ;

?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		
		$champs[] = array('nom'=>'ID_AGGREGATION', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'id_agg', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_syst]', 'lib'=>'id_syst', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'1');
		$champs[] = array('nom'=>'ID_RUBRIQUE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_rub]', 'lib'=>'id_rub', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_RUBRIQUE_NIVEAU', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_assoc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		

		$table->table				= 'DICO_RUBRIQUE_NIVEAU';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_RUBRIQUE_NIVEAU';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_rub_niv.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesRubNiv';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

