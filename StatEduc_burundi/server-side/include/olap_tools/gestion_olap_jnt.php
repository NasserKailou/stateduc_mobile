<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		
		$champs	= array();
		
		$champs[] = array('nom'=>'ID_OLAP', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_olap]', 'lib'=>'id_olap', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_JOINTURE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_jnt', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$champs[] = array('nom'=>'ID_OLAP_TABLE_MERE_1', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_tabm1', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_CHAMP_1', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'chp1', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');

		$champs[] = array('nom'=>'OPERATEUR_JOINTURE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'op_jnt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$champs[] = array('nom'=>'ID_OLAP_TABLE_MERE_2', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'id_tabm2', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_CHAMP_2', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'chp2', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= 'DICO_OLAP_JOINTURES';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_JOINTURE';
		$table->taille_combo		= '300';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'olap_tools/frame_gestion_olap_jnt.php';
		$table->taille_ecran		= '650';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesOlapJnt';
		
		$tab_concat = array();
		//$tab_concat[] = array( 'champ' => 'ID_CHAMP_1', 'table_cible' => 'DICO_OLAP_CHAMP', 'champ_cible' => 'ID_CHAMP', 'champ_extract' => 'NOM_CHAMP', 'separator' => '');
		$tab_concat[] = array( 'champ' => 'ID_OLAP_TABLE_MERE_1', 'table_cible' => 'DICO_OLAP_TABLE_MERE', 'champ_cible' => 'ID_OLAP_TABLE_MERE', 'champ_extract' => 'NOM_TABLE_MERE', 'separatorDeb' => '', 'separatorFin' => '');        
        $tab_concat[] = array( 'champ' => 'ID_OLAP_TABLE_MERE_1', 'table_cible' => 'DICO_OLAP_TABLE_MERE', 'champ_cible' => 'ID_OLAP_TABLE_MERE', 'champ_extract' => 'NOM_ALIAS', 'separatorDeb' => '(', 'separatorFin' => ')');        
		$tab_concat[] = array( 'champ' => 'ID_OLAP_TABLE_MERE_2', 'table_cible' => 'DICO_OLAP_TABLE_MERE', 'champ_cible' => 'ID_OLAP_TABLE_MERE', 'champ_extract' => 'NOM_TABLE_MERE', 'separatorDeb' => '- ', 'separatorFin' => '');
        $tab_concat[] = array( 'champ' => 'ID_OLAP_TABLE_MERE_2', 'table_cible' => 'DICO_OLAP_TABLE_MERE', 'champ_cible' => 'ID_OLAP_TABLE_MERE', 'champ_extract' => 'NOM_ALIAS', 'separatorDeb' => '(', 'separatorFin' => ')');

		$table->tab_concat_combo = $tab_concat ;
		
		$table->run();
		
?>

