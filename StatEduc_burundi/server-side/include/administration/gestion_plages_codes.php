<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'CODE_TYPE_REGROUP', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'type_regroup', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_CHAMP', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'nom_champ', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'NOM_TABLE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_table', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'DESCRIP_CHAMP_PLAGE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_champ_entete', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'VAL_MIN', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'val_min', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'VAL_MAX', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'val_max', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table				= 'DICO_PLAGE_CODES';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'DESCRIP_CHAMP_PLAGE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_plages_codes.php';
		$table->taille_ecran		= '400';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'TitPlages';
		
		$table->run();
		
		if($table->action == 'Del'){
				$requete = 'DELETE FROM DICO_PLAGE_CODES_ASSOC WHERE  CODE_TYPE_REGROUP = '.trim($_POST['CODE_TYPE_REGROUP']).' AND NOM_CHAMP = \''.trim($_POST['NOM_CHAMP']).'\'';
				if ($GLOBALS['conn_dico']->Execute($requete) === false){
					echo "Error deletion : $requete";
				}
		}		
?>

