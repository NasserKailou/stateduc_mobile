<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
	
	$table 				= new gestion_table_simple() ;
	$table->conn		= $GLOBALS['conn_dico'];

	
	// récupération des fonctionnalités 
	$requete            = ' SELECT ID_FONCTIONNALITE, LIBELLE_FONCTIONNALITE 
							FROM DICO_FONCTIONNALITE
							ORDER  BY LIBELLE_FONCTIONNALITE;';
	$GLOBALS['all_fonc'] = $GLOBALS['conn_dico']->GetAll($requete);
	
	( isset($_GET['id_fonc']) ) ? ( $GLOBALS['id_fonc'] = $_GET['id_fonc'] ) : ( $GLOBALS['id_fonc'] = '' ) ;
	
	
	( isset($_GET['id_comp']) ) ? ( $GLOBALS['id_comp'] = $_GET['id_comp'] ) : ( $GLOBALS['id_comp'] = '' ) ;
	// récupération des compriques 
	$requete            = ' SELECT ID_COMPOSANT, LIBELLE_COMPOSANT 
							FROM DICO_COMPOSANT
							WHERE ID_COMPOSANT = ' . $GLOBALS['id_comp'] . '
							ORDER  BY LIBELLE_COMPOSANT;';
	$GLOBALS['all_comp'] = $GLOBALS['conn_dico']->GetAll($requete);
	

?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		
		$champs[] = array('nom'=>'ID_FONCTIONNALITE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_fonc]', 'lib'=>'id_fonc', 'obli'=>'1','filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_COMPOSANT', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$GLOBALS[id_comp]', 'lib'=>'id_comp', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_COMP_FONCTIONNALITE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_assoc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'ORDRE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ordre', 'obli'=>'', 'filtre'=>'', 'ordre'=>'1');

		
		$table->table				= 'DICO_COMP_FONCTIONNALITE';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_COMP_FONCTIONNALITE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_comp_fonc.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesCompFonc';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

