<br><br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID_RUBRIQUE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_rubr', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_TYPE_RUBRIQUE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_typ_rubr', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_rpt', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LIBELLE_RUBRIQUE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_rubr', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'2');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->table				= 'DICO_RUBRIQUE';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'LIBELLE_RUBRIQUE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'annuaire/frame_gestion_rubr.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->hauteur_combo		= '5';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'Gesrubr';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		$btn1 = array();
		$btn1['label_code'] = 'BtnRubSys';
		$btn1['fonc_js']	= 'OpenPopupAnnRubSys';
		$btn1['params']		= array();
		$btn1['params'][]	= array( 'code' => 'ID_RUBRIQUE', 'eval' => '' );
		$btn1['params'][]	= array( 'code' => '$_SESSION[secteur]', 'eval' => '1' );
		
		$table->tab_btns_popup[]  = $btn1;
		unset($btn1);
		
		$btn2 = array();
		$btn2['label_code'] = 'BtnRubNiv';
		$btn2['fonc_js']	= 'OpenPopupAnnRubNiv';
		$btn2['params']		= array();
		$btn2['params'][]	= array( 'code' => 'ID_RUBRIQUE', 'eval' => '' );
		$btn2['params'][]	= array( 'code' => '\'\'', 'eval' => '1' );
		$btn2['params'][]	= array( 'code' => '\'\'', 'eval' => '1' );
		
		$table->tab_btns_popup[]  = $btn2;
		unset($btn2);
		
		$btn3 = array();
		$btn3['label_code'] = 'BtnLinkRub';
		$btn3['fonc_js']	= 'OpenPopupAnnLinkRub';
		$btn3['params']		= array();
		$btn3['params'][]	= array( 'code' => 'ID_RUBRIQUE', 'eval' => '' );
		$btn3['params'][]	= array( 'code' => '\'\'', 'eval' => '1' );
		
		$table->tab_btns_popup[]  = $btn3;
		unset($btn3);
		
		$table->run();
		
		switch($table->action){
			case 'Add' :{
				$maj = true ;
				$req_maj = 'INSERT INTO DICO_FONC_RUBRIQUE (ID_FONCTIONNALITE, ID_RUBRIQUE, ORDRE, NOM_FONC_RUBRIQUE) VALUES 
								('.$_POST['ID_FONCTIONNALITE'].', '.$_POST['ID_RUBRIQUE'].', 0, '.$table->conn->qstr($_POST['LIBELLE_RUBRIQUE']).')';
				break;
			}
			case 'Upd' :{
				$maj = true ;
				if( isset($_POST['ID_FONCTIONNALITE']) && (trim($_POST['ID_FONCTIONNALITE']) <> '') ){
					
					$req_test = 'SELECT * FROM DICO_FONC_RUBRIQUE
								 WHERE ID_FONCTIONNALITE = '.$_POST['ID_FONC_BASE'].'
								 AND ID_RUBRIQUE = '.$_POST['ID_RUBRIQUE'];
					$exist_enr = $GLOBALS['conn_dico']->GetRow($req_test);
					if( is_array($exist_enr) && (count($exist_enr) > 0) ){
					
						$req_maj = 'UPDATE DICO_FONC_RUBRIQUE SET ID_FONCTIONNALITE = '.$_POST['ID_FONCTIONNALITE'].'
									WHERE ID_FONCTIONNALITE = '.$_POST['ID_FONC_BASE'].'
									AND ID_RUBRIQUE = '.$_POST['ID_RUBRIQUE'];
					}else{
						$req_maj = 'INSERT INTO DICO_FONC_RUBRIQUE (ID_FONCTIONNALITE, ID_RUBRIQUE, ORDRE, NOM_FONC_RUBRIQUE) VALUES 
										('.$_POST['ID_FONCTIONNALITE'].', '.$_POST['ID_RUBRIQUE'].', 0, '.$table->conn->qstr($_POST['LIBELLE_RUBRIQUE']).')';
					}
				}else{
					$req_maj = 'DELETE FROM DICO_FONC_RUBRIQUE 
								WHERE ID_FONCTIONNALITE = '.$_POST['ID_FONC_BASE'].'
								AND ID_RUBRIQUE = '.$_POST['ID_RUBRIQUE'];
				}
				break;
			}
			case 'Del' :{
				$maj = true ;
				$req_maj = 'DELETE FROM DICO_FONC_RUBRIQUE 
							WHERE ID_FONCTIONNALITE = '.$_POST['ID_FONCTIONNALITE'].'
							AND ID_RUBRIQUE = '.$_POST['ID_RUBRIQUE'];
				break;
			}
		}
		//echo '<br>'.$req_maj.'<br>';
		if( isset($maj) and ($maj == true) ){
			if ($GLOBALS['conn_dico']->Execute($req_maj) === false){
				echo '<br>'.$req_maj.'<br>';
			}
		}
		
?>

