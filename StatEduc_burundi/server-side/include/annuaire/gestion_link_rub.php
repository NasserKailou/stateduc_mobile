<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
	
	$table 				= new gestion_table_simple() ;
	$table->conn		= $GLOBALS['conn_dico'];

	
	( isset($_GET['id_rub']) ) ? ( $GLOBALS['id_rub'] = $_GET['id_rub'] ) : ( $GLOBALS['id_rub'] = '' ) ;
	// récupération des rubriques 
	$requete            = ' SELECT ID_RUBRIQUE, LIBELLE_RUBRIQUE 
							FROM DICO_RUBRIQUE
							WHERE ID_RUBRIQUE = '.$_GET['id_rub'].'
							ORDER  BY LIBELLE_RUBRIQUE;';
	$GLOBALS['all_rub'] = $GLOBALS['conn_dico']->GetAll($requete);
	
	
	// récupération des rubriques pour constituer les sous-rubriques
	$requete            = ' SELECT ID_RUBRIQUE, LIBELLE_RUBRIQUE 
							FROM DICO_RUBRIQUE
							WHERE ID_RUBRIQUE <> '.$GLOBALS['id_rub'].'
							ORDER  BY LIBELLE_RUBRIQUE;';
	$GLOBALS['all_ss_rub'] = $GLOBALS['conn_dico']->GetAll($requete);
	
	( isset($_GET['id_ss_rub']) ) ? ( $GLOBALS['id_ss_rub'] = $_GET['id_ss_rub'] ) : ( $GLOBALS['id_ss_rub'] = '' ) ;
	
	

?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		
		$champs[] = array('nom'=>'ID_RUBRIQUE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_rub]', 'lib'=>'id_rub', 'obli'=>'1','filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_SOUS_RUBRIQUE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_ss_rub]', 'lib'=>'id_ss_rub', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_LINK_RUBRIQUE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_link', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'2');
		$champs[] = array('nom'=>'ORDRE_SS_RUB', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ordre', 'obli'=>'', 'filtre'=>'', 'ordre'=>'1');

		$table->table				= 'DICO_LINK_RUBRIQUE';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_LINK_RUBRIQUE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_link_rub.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesLinkRub';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

