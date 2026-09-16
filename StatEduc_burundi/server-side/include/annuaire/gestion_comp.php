<br><br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID_COMPOSANT', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_comp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_TYPE_COMPOSANT', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_typ_comp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'LIBELLE_COMPOSANT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_comp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->table				= 'DICO_COMPOSANT';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'LIBELLE_COMPOSANT';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_comp.php';
		$table->taille_ecran		= '400';
		$table->taille_combo		= '200';
		$table->hauteur_combo		= '5';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'GesComp';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$btn1 = array();
		$btn1['label_code'] = 'BtnCompSys';
		$btn1['fonc_js']	= 'OpenPopupAnnCompSys';
		$btn1['params']		= array();
		$btn1['params'][]	= array( 'code' => 'ID_COMPOSANT', 'eval' => '' );
		$btn1['params'][]	= array( 'code' => '$_SESSION[secteur]', 'eval' => '1' );
		
		$table->tab_btns_popup[]  = $btn1;
		unset($btn1);
		
		$btn2 = array();
		$btn2['label_code'] = 'BtnCompFonc';
		$btn2['fonc_js']	= 'OpenPopupAnnCompFonc';
		$btn2['params']		= array();
		
		$btn2['params'][]	= array( 'code' => '\'\'', 'eval' => '1' );
		$btn2['params'][]	= array( 'code' => 'ID_COMPOSANT', 'eval' => '' );
		
		$table->tab_btns_popup[]  = $btn2;
		unset($btn2);

		$table->run();
		
?>

