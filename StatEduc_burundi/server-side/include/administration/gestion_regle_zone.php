<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_REGLE_ZONE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'idregz', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'TYPE_DONNEES', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'type_d', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TAILLE_DONNEES', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'size_d', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'FORMAT_DONNEES', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'frmt_d', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'INTERVALLE_VALEURS', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'int_val', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'VALEUR_MINIMALE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'val_min', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'VALEUR_MAXIMALE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'val_max', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CONTROLE_PRESENCE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ctrl_pres', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CONTROLE_PARUTION', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ctrl_par', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CONTROLE_OBLIGATION', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ctrl_obl', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CONTROLE_INTEGRITE_REF', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ctrl_ref', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CONTROLE_EDITION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ctrl_edi', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'VALEURS_ENUM', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'val_enum', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CONTROLE_UNICITE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ctrl_obl', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$table 										= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table							= 'DICO_REGLE_ZONE';
		$table->champs 						= $champs;
		$table->nom_champ_combo		= 'ID_REGLE_ZONE';
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_regle_zone.php';
		$table->code_libelle_trad	= 'ID_REGLE_ZONE';
		$table->titre_ecran				= 'gestregz';
		$table->taille_ecran			= '600';
		$table->btn_quit					= false;
		
		$table->tab_champs_trad['ID_REGLE_ZONE'] = array('table' => 'DICO_REGLE_ZONE', 'libelle'=>'LIBELLE_TRAD');

		
		$table->run();
		
		if( ($table->act_MAJ == 1) and ($table->action == 'Del') and ($table->ok_action 	== 1) ){
				$requete = 'DELETE FROM DICO_REGLE_ZONE_ASSOC WHERE  ID_REGLE_ZONE = '.trim($_POST['ID_REGLE_ZONE']);
				if ($GLOBALS['conn_dico']->Execute($requete) === false){}
		}			

?>

