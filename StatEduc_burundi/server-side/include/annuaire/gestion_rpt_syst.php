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
	
	$requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT, DICO_REPORT.RADICAL_RPT_FILE 
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['id_rpt'].' ;';
	//print $requete;
	$row_rpt = $GLOBALS['conn_dico']->GetRow($requete);
	
	$GLOBALS['nom_rpt'] = $row_rpt['LIBELLE_REPORT'];
	
	$rad_rpt_file = $row_rpt['RADICAL_RPT_FILE'];
	
	$GLOBALS['dft_file_name']	= $rad_rpt_file . '_' . $GLOBALS['id_syst'] . '.rpt';
	$GLOBALS['dft_temp_table']	= $rad_rpt_file . '_' . $GLOBALS['id_syst'];

?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_syst]', 'lib'=>'id_syst', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_rpt]', 'lib'=>'id_rpt', 'obli'=>'1','filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'TITRE_PRINCIPAL', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'tit_princ', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TITRE_SECOND', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'tit_second', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TITRE_TERTIAIRE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'tit_tert', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'PAGE_ORIENTATION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'page_ori', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_REPORT', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ordre_rpt', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ACTIVER_REPORT', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'activer', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NUMERO_PAGE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'num_page', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'RPT_FILE_NAME', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'$GLOBALS[dft_file_name]', 'lib'=>'rpt_file', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TEMP_TABLE_NAME', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'$GLOBALS[dft_temp_table]', 'lib'=>'temp_table', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');


		$table->table				= 'DICO_REPORT_PARAM';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'TITRE_PRINCIPAL';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_rpt_syst.php';
		$table->taille_ecran		= '600';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesRptSys';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

