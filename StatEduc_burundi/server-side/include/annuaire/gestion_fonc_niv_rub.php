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
	
	//( isset($_GET['id_agg']) ) ? ( $GLOBALS['id_agg'] = $_GET['id_agg'] ) : ( $GLOBALS['id_agg'] = '' ) ;
	

?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		
		$champs[] = array('nom'=>'ID_FONCTIONNALITE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_fonc]', 'lib'=>'id_fonc', 'obli'=>'1','filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_syst]', 'lib'=>'id_syst', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_AGGREGATION', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_agg', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_ASSOC_FONC_NIV', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_assoc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		

		$table->table				= 'DICO_FONCTIONNALITE_NIVEAU';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_ASSOC_FONC_NIV';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_fonc_niv_rub.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesFoncNivRub';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

