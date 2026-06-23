<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_ZONE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_zone]', 'lib'=>'idzone', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_INDEXE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_ind', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'CHAMP_INDEXE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'chp_ind', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'VALEUR_DEFAUT_INDEXE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'val_def', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 										= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->nom_page					= '/gestion_zone_indexes.php';
		$table->table							= 'DICO_INDEXES';
		$table->champs 						= $champs;
		$table->nom_champ_combo		= 'CHAMP_INDEXE';
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_zone_indexes.php';
		$table->titre_ecran				= 'gestindex';
		
		$table->run();
		//echo '<br>i_enr='.$table->i_enr;			
?>

