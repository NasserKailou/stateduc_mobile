<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $table 						= new gestion_table_simple() ;
	$table->conn				= $GLOBALS['conn_dico'];

	/*$requete                = ' SELECT DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE, 
								DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS
								FROM DICO_OLAP_TABLE_MERE
								WHERE DICO_OLAP_TABLE_MERE.ID_OLAP = '.$_GET['id_olap'].' ;';*/
	//print $requete;
	//$all_tabm 				= $table->conn->GetAll($requete);
	
	//( isset($_GET['tabm']) && (trim($_GET['tabm']) <> '') ) ? ( $GLOBALS['tabm'] = $_GET['tabm'] ) : ( $GLOBALS['tabm'] = $all_tabm[0]['ID_OLAP_TABLE_MERE'] ) ;

		
		$champs	= array();
		$champs[] = array('nom'=>'ID_OLAP', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_olap]', 'lib'=>'id_olap', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		
		$champs[] = array('nom'=>'ID_CRITERE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_crit', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
	
		$champs[] = array('nom'=>'ID_TABLE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_table', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
	
		$champs[] = array('nom'=>'NOM_TABLE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_table', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$champs[] = array('nom'=>'ALIAS_TABLE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'alias_table', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$champs[] = array('nom'=>'ID_CHAMP', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_champ', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');

		$champs[] = array('nom'=>'NOM_CHAMP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_chp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$champs[] = array('nom'=>'OPERATEUR', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'opera', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$champs[] = array('nom'=>'VALEUR', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'val', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');

		$champs[] = array('nom'=>'TYPE_CHAMP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'type_chp', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		
		
		$table->table				= 'DICO_OLAP_CRITERE';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_TABLE';
		$table->taille_combo		= '300';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'olap_tools/frame_gestion_olap_critere.php';
		$table->taille_ecran		= '600';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesOlapCRit';
		
        $tab_concat = array();
		
        $tab_concat[] = array( 'champ' => 'ALIAS_TABLE', 'table_cible' => 'DICO_OLAP_CRITERE', 'champ_cible' => 'ALIAS_TABLE', 'champ_extract' => 'ALIAS_TABLE', 'separatorDeb' => '(', 'separatorFin' => ')');
        $tab_concat[] = array( 'champ' => 'NOM_CHAMP', 'table_cible' => 'DICO_OLAP_CRITERE', 'champ_cible' => 'NOM_CHAMP', 'champ_extract' => 'NOM_CHAMP', 'separatorDeb' => '.', 'separatorFin' => '');
        
        $table->tab_concat_combo = $tab_concat ;

		$table->run();
		
?>

