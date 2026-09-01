<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'code_syst', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_syst', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ord_syst', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		//$champs[] = array('nom'=>$GLOBALS['PARAM']['CODE_TYPE_TRANCHE_AGE'], 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'255', 'lib'=>'tr_age', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		//$champs[] = array('nom'=>$GLOBALS['PARAM']['AGE_RETRAITE'], 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'0', 'lib'=>'age_ret', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
		$table->champs 				= $champs;
		$table->nom_champ_combo		=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_systemes.php';
		$table->taille_ecran		= '500';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'gestsyst';
		$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
		
		$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] = array('table' => $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		//echo '<br>i_enr='.$table->i_enr;
				//// Recharger le tableau des années en session
		if($table->act_MAJ == 1){
			set_tab_session('secteurs', $_SESSION['langue']);
			
			if( $table->action == 'Del' ){
				supp_config_of_secteur($_POST[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]);
			}
			
			if(isset($GLOBALS['conn_dico'])){
				switch($table->action){
					case 'Add' :{
						$maj_dico = true ;
						$req_maj_dico = 'INSERT INTO SYSTEME (ID_SYSTEME,LIBELLE_SYSTEME) VALUES 
										('.$_POST[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']].','.$GLOBALS['conn_dico']->qstr($_POST[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]).')';
						break;
					}
					case 'Upd' :{
						$maj_dico = true ;
						$req_maj_dico = 'UPDATE SYSTEME SET LIBELLE_SYSTEME = '.$GLOBALS['conn_dico']->qstr($_POST[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]).'
										 WHERE ID_SYSTEME = '.$_POST[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']].'';
						break;
					}
					case 'Del' :{
						$maj_dico = true ;
						$req_maj_dico = 'DELETE FROM SYSTEME WHERE ID_SYSTEME = '.$_POST[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']].'';
						break;
					}
				}
				if( isset($maj_dico) and ($maj_dico == true) ){
					if ($GLOBALS['conn_dico']->Execute($req_maj_dico) === false){
						echo '<br>'.$req_maj_dico.'<br>';
					}
				}
			}	
		}
					
			
?>

