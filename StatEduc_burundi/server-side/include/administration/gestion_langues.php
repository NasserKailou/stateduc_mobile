<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'CODE_LANGUE', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'code_lang', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'LIBELLE_LANGUE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_lang', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 										= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table							= 'DICO_LANGUE';
		$table->champs 						= $champs;
		$table->nom_champ_combo		= 'LIBELLE_LANGUE';
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_langues.php';
		$table->taille_ecran			= '400';
		$table->btn_quit					= false;
		$table->titre_ecran				= 'gestlang';
		
		$table->run();
		//echo '<br>i_enr='.$table->i_enr;
				//// Recharger le tableau des années en session
		if($table->act_MAJ == 1){
				set_tab_session('langues', ''); 
		}			
		
		if($table->action == 'Del'){
				sup_libelles_langue($_POST['CODE_LANGUE']);
		}
			
		if($table->action == 'Add'){
				init_libelles_langue($_POST['CODE_LANGUE']);
				$_SESSION['lancer_popup_page'] = 'administration.php?val=regen&langue_regen='.$_POST['CODE_LANGUE'];
		}	
		
?>

