<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
	
	$table 				= new gestion_table_simple() ;
	$table->conn		= $GLOBALS['conn_dico'];

	
	
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
		
		$champs[] = array('nom'=>'ID_FONCTIONNALITE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_fonc]', 'lib'=>'id_fonc', 'obli'=>'1','filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_RUBRIQUE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_rub]', 'lib'=>'id_rub', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_FONC_RUBRIQUE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_assoc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'ORDRE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ordre', 'obli'=>'', 'filtre'=>'', 'ordre'=>'1');

		$table->table				= 'DICO_FONC_RUBRIQUE';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_FONC_RUBRIQUE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_fonc_rub.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesFoncRub';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

