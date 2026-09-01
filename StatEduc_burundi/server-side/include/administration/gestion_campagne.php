<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
	/////Creation de la table population si elle n'existe pas
	$TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
	$tables = array();
	$existe=false;
	foreach($TabBD as $tab)
	{	
		if(strtoupper($GLOBALS['PARAM']['RATTACHEMENT_STATUT'])==strtoupper($tab))
		{
			$existe=true;
			break;
		}
	}
	if(!$existe){
		$strRequete = "CREATE TABLE ".$GLOBALS['PARAM']['RATTACHEMENT_STATUT']."(".          
			$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']." byte, ".   
			$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']." byte, ".   
			$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_ANNEE']." byte, ". 
			$GLOBALS['PARAM']['DATE_DEBUT_STATUT']." varchar(20), ".
			$GLOBALS['PARAM']['LIBELLE_STATUT']." varchar(100) ".
			"CONSTRAINT PK_cle PRIMARY KEY (".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].", ".
      $GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT'].", ".
      $GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_ANNEE'].", ".$GLOBALS['PARAM']['DATE_DEBUT_STATUT'].");";
		$GLOBALS['conn']->Execute($strRequete);
	}
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();                                                                                                                                                                                            
		$champs[] = array('nom'=>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'], 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'code_camp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT'], 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'statut_camp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'2');
		$champs[] = array('nom'=>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'code_annee', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'3');           
		$champs[] = array('nom'=>$GLOBALS['PARAM']['DATE_DEBUT_STATUT'], 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'date_deb_camp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>$GLOBALS['PARAM']['LIBELLE_STATUT'], 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_camp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		
		$table 										= new gestion_table_simple() ;
		
		$table->table							= $GLOBALS['PARAM']['RATTACHEMENT_STATUT'];
		$table->champs 						= $champs;
		$table->nom_champ_combo		= $GLOBALS['PARAM']['LIBELLE_STATUT'];
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_campagne.php';
		$table->taille_ecran			= '800';
		$table->btn_quit					= false;
		$table->titre_ecran				= 'gestcamp';
		$table->code_libelle_trad	= $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']] = array('table' => $GLOBALS['PARAM']['TYPE_RATTACHEMENT'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		//echo '<br>i_enr='.$table->i_enr;
		
		//// Recharger le tableau des années en session
		if($table->act_MAJ == 1){
				//set_tab_session('campagnes', ''); 
		}
		
?>

