<br><br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID_FONCTIONNALITE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_fonc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_TYPE_FONCTIONNALITE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_typ_fonc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_rpt', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LIBELLE_FONCTIONNALITE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_fonc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'2');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->table				= 'DICO_FONCTIONNALITE';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'LIBELLE_FONCTIONNALITE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_fonc.php';
		$table->taille_ecran		= '450';
		$table->taille_combo		= '200';
		$table->hauteur_combo		= '6';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'Gesfonc';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$btn1 = array();
		$btn1['label_code'] = 'BtnFoncSys';
		$btn1['fonc_js']	= 'OpenPopupAnnFoncSys';
		$btn1['params']		= array();
		$btn1['params'][]	= array( 'code' => 'ID_FONCTIONNALITE', 'eval' => '' );
		$btn1['params'][]	= array( 'code' => '$_SESSION[secteur]', 'eval' => '1' );
		
		$table->tab_btns_popup[]  = $btn1;
		unset($btn1);
		
		$btn2 = array();
		$btn2['show_cond'] 	= "!( isset(\$this->donnees[\$this->i_enr]['ID']) && (trim(\$this->donnees[\$this->i_enr]['ID']) <> '') )";
		$btn2['label_code'] = 'BtnFoncRub';
		$btn2['fonc_js']	= 'OpenPopupAnnFoncRub';
		$btn2['params']		= array();
		$btn2['params'][]	= array( 'code' => 'ID_FONCTIONNALITE', 'eval' => '' );
		$btn2['params'][]	= array( 'code' => '\'\'', 'eval' => '1' );
		
		$table->tab_btns_popup[]  = $btn2;
		unset($btn2);
		
		$btn3 = array();
		$btn3['label_code'] = 'BtnFoncNivRub';
		$btn3['fonc_js']	= 'OpenPopupAnnFoncNivRub';
		$btn3['params']		= array();
		$btn3['params'][]	= array( 'code' => 'ID_FONCTIONNALITE', 'eval' => '' );
		$btn3['params'][]	= array( 'code' => '\'\'', 'eval' => '1' );
		$btn3['params'][]	= array( 'code' => '\'\'', 'eval' => '1' );
		$btn3['params'][]	= array( 'code' => '\'\'', 'eval' => '1' );
		
		$table->tab_btns_popup[]  = $btn3;
		unset($btn3);
		
		$table->run();
		
?>

