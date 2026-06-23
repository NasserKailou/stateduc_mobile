<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
	/////Creation de la table population si elle n'existe pas
	$TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
	$tables = array();
	$existe=false;
	foreach($TabBD as $tab)
	{	
		if(strtoupper($GLOBALS['PARAM']['TYPE_FILTRE'])==strtoupper($tab))
		{
			$existe=true;
			break;
		}
	}
	if(!$existe){
		$strRequete = "CREATE TABLE ".$GLOBALS['PARAM']['TYPE_FILTRE']."(".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']." integer, ".
			$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_FILTRE']." varchar(50), ".
			$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']." integer, ".
			"CONSTRAINT PK_cle PRIMARY KEY (".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].")".
		")";
		$GLOBALS['conn']->Execute($strRequete);
	}
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'code_period', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_period', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ord_period', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 										= new gestion_table_simple() ;
		
		$table->table							= $GLOBALS['PARAM']['TYPE_FILTRE'];
		$table->champs 						= $champs;
		$table->nom_champ_combo		=$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_filtres.php';
		$table->taille_ecran			= '400';
		$table->btn_quit					= false;
		$table->titre_ecran				= 'gestperiod';
		$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
		
		$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']] = array('table' => $GLOBALS['PARAM']['TYPE_FILTRE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		//echo '<br>i_enr='.$table->i_enr;
		
		//// Recharger le tableau des années en session
		if($table->act_MAJ == 1){
				set_tab_session('filtres', ''); 
		}
		
?>

