<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'code_ann', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_ANNEE'], 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_ann', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ord_ann', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 										= new gestion_table_simple() ;
		
		$table->table							= $GLOBALS['PARAM']['TYPE_ANNEE'];
		$table->champs 						= $champs;
		$table->nom_champ_combo		=$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_ANNEE'];
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_annees.php';
		$table->taille_ecran			= '400';
		$table->btn_quit					= false;
		$table->titre_ecran				= 'gestann';
		$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		//echo '<br>i_enr='.$table->i_enr;
		
		//// Recharger le tableau des années en session
		if($table->act_MAJ == 1){
				set_tab_session('annees', ''); 
		}
		
?>

