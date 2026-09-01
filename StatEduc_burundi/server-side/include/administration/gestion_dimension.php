<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_DIMENSION', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_dim', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CHAMP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'champ', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DIM_LIBELLE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'dim_lib', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TABLE_REF', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'table_ref', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'DIM_SQL', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'dim_sql', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DIM_LIBELLE_ENTTE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ent_dim', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table				= 'DICO_DIMENSION';
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'DIM_LIBELLE_ENTTE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_dimension.php';
		$table->taille_ecran		= '550';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'gestdim';
		
		$table->run();
?>

