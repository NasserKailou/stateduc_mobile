<br/><br/>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; ?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'CODE_TYPE_REGROUP', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[type_regroup]', 'lib'=>'type_regroup', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_CHAMP', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[nom_champ]', 'lib'=>'nom_champ', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'1');
		$champs[] = array('nom'=>'NOM_CHAMP_ASSOC', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'nom_champ_rattach', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_CHAMP_ASSOC', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ordre_champ_rattach', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		
		$table						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table				= 'DICO_PLAGE_CODES_ASSOC';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_CHAMP_ASSOC';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_plages_codes_assoc.php';
		$table->taille_ecran		= '500';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'TitPlagesCodesRattach';
		
		$table->run();
				
?>

