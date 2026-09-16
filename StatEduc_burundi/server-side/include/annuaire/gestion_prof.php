<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
	
	$table 				= new gestion_table_simple() ;
	$table->conn		= $GLOBALS['conn_dico'];

	
	// récupération des chaines 
	
	$requete            = ' SELECT DICO_CHAINE_LOCALISATION.CODE_TYPE_CHAINE, DICO_TRADUCTION.LIBELLE, SYSTEME.LIBELLE_SYSTEME
							FROM SYSTEME, DICO_CHAINE_LOCALISATION, DICO_TRADUCTION 
							WHERE DICO_CHAINE_LOCALISATION.CODE_TYPE_CHAINE = DICO_TRADUCTION.CODE_NOMENCLATURE
							AND	SYSTEME.ID_SYSTEME = DICO_CHAINE_LOCALISATION.CODE_TYPE_SYSTEME_ENSEIGNEMENT
							AND DICO_TRADUCTION.NOM_TABLE =\'DICO_CHAINE_LOCALISATION\' 
							AND DICO_TRADUCTION.CODE_LANGUE =\''.$_SESSION['langue'].'\'
							ORDER BY DICO_CHAINE_LOCALISATION.CODE_TYPE_SYSTEME_ENSEIGNEMENT;';

	$GLOBALS['all_ch'] = $table->conn->GetAll($requete);
	
	( isset($_GET['id_ch']) ) ? ( $GLOBALS['id_ch'] = $_GET['id_ch'] ) : ( $GLOBALS['id_ch'] = '' ) ;
	
	
	// récupération des aggrégations 
	$requete            = ' SELECT ID_AGGREGATION, LIBELLE_AGGREGATION 
							FROM DICO_AGGREGATION_LEVEL
							ORDER  BY LIBELLE_AGGREGATION;';
	$GLOBALS['all_agg'] = $GLOBALS['conn_dico']->GetAll($requete);
	
	( isset($_GET['id_agg']) ) ? ( $GLOBALS['id_agg'] = $_GET['id_agg'] ) : ( $GLOBALS['id_agg'] = '' ) ;
	
	

?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		
		$champs[] = array('nom'=>'CODE_TYPE_CHAINE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_ch]', 'lib'=>'id_ch', 'obli'=>'1','filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_AGGREGATION', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_agg]', 'lib'=>'id_agg', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_PROFONDEUR', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_prof', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'HIERARCHY_LEVEL', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'level', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$table->table				= 'DICO_PROFONDEUR';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_PROFONDEUR';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_prof.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesProf';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

