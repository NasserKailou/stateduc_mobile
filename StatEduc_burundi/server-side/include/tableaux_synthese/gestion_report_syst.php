<br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
	$table 				= new gestion_table_simple() ;
	$requete            = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme,
							D_TRAD.LIBELLE as libelle_systeme
							FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
							WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
							AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' AND D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
	//echo " <br> $requete <br>";
	$GLOBALS['all_systemes'] = $GLOBALS['conn']->GetAll($requete);
	
	( isset($_GET['id_syst']) ) ? ( $GLOBALS['id_syst'] = $_GET['id_syst'] ) : ( $GLOBALS['id_syst'] = $_SESSION['secteur'] ) ;

?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_rpt]', 'lib'=>'id_rpt', 'obli'=>'1','filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_syst]', 'lib'=>'id_syst', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ACTIVER', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'activer', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'HAUTEUR_LIGNE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'h_ligne', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LARGEUR_COLONNE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'l_col', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LARGEUR_COLONNE_ZONE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'l_col_zon', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'PAGE_ORIENTATION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'page_ori', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TITRE_ETAT_MENU', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'etat_menu', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_AFFICHAGE_MENU', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ordre', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'AFFICHER_TOTAL_VERTICAL', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'tot_vert', 'lib'=>'type_pres', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'AFFICHER_TOTAL_HORIZONTAL', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'tot_hori', 'lib'=>'type_pres', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TITRE_ETAT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'tit_etat', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ENTETE_PAGE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'entete', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'PIED_PAGE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'pied', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'VAL_DONNEES_MANQUANTES', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'miss_data', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORIENTATION_MESURE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ori_mes', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ALIGNEMENT_MESURE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'align_mes', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'PERE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'pere', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		//$champs[] = array('nom'=>'PRECEDENT', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'preced', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table->table				= 'DICO_REPORT_SYSTEME';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'TITRE_ETAT_MENU';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/frame_gestion_report_syst.php';
		$table->taille_ecran		= '600';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesRptSys';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

