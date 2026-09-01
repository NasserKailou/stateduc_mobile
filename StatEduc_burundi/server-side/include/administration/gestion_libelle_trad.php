<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'CODE_LIBELLE', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'code_lib', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'NOM_PAGE', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[nom_page]', 'lib'=>'nom_pg', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'CODE_LANGUE', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'$_SESSION[langue]', 'lib'=>'langue', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'LIBELLE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'libelle', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 										= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table							= 'DICO_LIBELLE_PAGE';
		$table->champs 						= $champs;
		$table->nom_champ_combo		= 'CODE_LIBELLE';
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_libelle_trad.php';
		$table->taille_ecran			= '600';
		$table->btn_quit					= false;
		$table->titre_ecran				= 'gestlibpg';
		//$table->code_libelle_trad	= 'CODE_TYPE_SYSTEME_ENSEIGNEMENT';
		
		$table->run();
		//echo '<br>i_enr='.$table->i_enr;
		if($table->action == 'Add'){
				insert_traduction('DICO_LIBELLE_PAGE',$_POST['CODE_LIBELLE'], $_POST['NOM_PAGE'], $_POST['CODE_LANGUE'], convertWordSpecialChr($_POST['LIBELLE']), 1);			
		}
		elseif($table->action == 'Del'){
				$sql = 'DELETE FROM DICO_LIBELLE_PAGE WHERE CODE_LIBELLE=\''.$_POST['CODE_LIBELLE'].'\' 
				AND NOM_PAGE='.'\''.$_POST['NOM_PAGE'].'\'';
				if ($GLOBALS['conn_dico']->Execute($sql)==false){}
		}
	

?>

