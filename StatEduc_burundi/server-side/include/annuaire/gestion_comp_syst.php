<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
	$table 				= new gestion_table_simple() ;
	$table->conn		= $GLOBALS['conn_dico'];

	/*
	$requete            = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme,
							D_TRAD.LIBELLE as libelle_systeme
							FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
							WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
							AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' AND D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
	
	*/
	//echo " <br> $requete <br>";
	$requete            = ' SELECT SYSTEME.ID_SYSTEME as id_systeme,
							SYSTEME.LIBELLE_SYSTEME as libelle_systeme
							FROM SYSTEME;';
	$GLOBALS['all_systemes'] = $table->conn->GetAll($requete);
	
	( isset($_GET['id_syst']) ) ? ( $GLOBALS['id_syst'] = $_GET['id_syst'] ) : ( $GLOBALS['id_syst'] = '' ) ;

?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_syst]', 'lib'=>'id_syst', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_COMPOSANT', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_comp]', 'lib'=>'id_comp', 'obli'=>'1','filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_COMPOSANT_SYSTEME', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'comp_sys', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ordre', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ACTIVER', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'activer', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		

		$table->table				= 'DICO_COMPOSANT_SYSTEME';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_COMPOSANT_SYSTEME';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_comp_syst.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesCompSys';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

