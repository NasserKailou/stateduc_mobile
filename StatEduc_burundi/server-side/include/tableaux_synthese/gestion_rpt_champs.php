<br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $table 						= new gestion_table_simple() ;
	$table->conn				= $GLOBALS['conn_dico'];
	
	$requete                = ' SELECT DICO_RPT_TABLE_MERE.ID_RPT_TABLE_MERE, 
								DICO_RPT_TABLE_MERE.NOM_TABLE_MERE, DICO_RPT_TABLE_MERE.NOM_ALIAS
								FROM DICO_RPT_TABLE_MERE
								WHERE DICO_RPT_TABLE_MERE.ID = '.$_GET['id'].' ;';
	//print $requete;
	$all_tabm 				= $GLOBALS['conn_dico']->GetAll($requete);
	
	//if( $_POST['action'] == 'New' ){
		$requete                = 'SELECT Max(DICO_RPT_CHAMP.ORDRE_RPT) AS Max_Order
									FROM DICO_RPT_CHAMP
									WHERE DICO_RPT_CHAMP.ID = '.$_GET['id'];
		//print $requete;
		$Max_Order 				= $GLOBALS['conn_dico']->GetOne($requete);
		$Max_Order++;
	//}
	
	( isset($_GET['tabm']) && (trim($_GET['tabm']) <> '') ) ? ( $GLOBALS['tabm'] = $_GET['tabm'] ) : ( $GLOBALS['tabm'] = $all_tabm[0]['ID_RPT_TABLE_MERE'] ) ;

		
		$champs	= array();
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id]', 'lib'=>'id', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		
		$champs[] = array('nom'=>'ID_CHAMP', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_chp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
        
		if (isset($GLOBALS['tabm']) && $GLOBALS['tabm']<>'')
            $champs[] = array('nom'=>'ID_RPT_TABLE_MERE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'$GLOBALS[tabm]', 'lib'=>'tabm', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
        
		$champs[] = array('nom'=>'ORDRE_RPT', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'$GLOBALS[Max_Order]', 'lib'=>'ord_rpt', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');

		$champs[] = array('nom'=>'NOM_CHAMP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_chp', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ALIAS', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'alias', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$champs[] = array('nom'=>'ID_TYPE_CHAMP', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'typ_chp', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'FORMAT', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'format', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$champs[] = array('nom'=>'FONCTION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'fonc', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'EXPRESSION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'expr', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$champs[] = array('nom'=>'ID_DIMENSION', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_dim', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ENTETE_CHAMP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ent_champ', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_DIM', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ord_dim', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
            
        $table->table				= 'DICO_RPT_CHAMP';
        $table->champs 				= $champs;
        $table->nom_champ_combo		= 'NOM_CHAMP';
        $table->taille_combo		= '250';
        $table->frame				= $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/frame_gestion_rpt_champs.php';
        $table->taille_ecran		= '500';
        $table->btn_quit			= true;
        $table->titre_ecran			= 'GesRptChp';
        
        $tab_concat = array();
		$tab_concat[] = array( 'champ' => 'ALIAS', 'table_cible' => 'DICO_RPT_CHAMP', 'champ_cible' => 'ALIAS', 'champ_extract' => 'ALIAS', 'separatorDeb' => '(', 'separatorFin' => ')');

		$table->tab_concat_combo = $tab_concat ;
        
        $table->run();
       
		
?>

