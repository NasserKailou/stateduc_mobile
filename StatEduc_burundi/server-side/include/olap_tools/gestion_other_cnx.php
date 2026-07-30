<?php require_once 'config_app.php';
    include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID_CNX', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_cnx', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SGBD', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sgbd', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'PROVIDER', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'provider', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DESCRIPTION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'desc', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SERVEUR', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'serveur', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DB', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'db', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_USER', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'user', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'PASSWORD', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'pwd', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= 'DICO_OLAP_AUTRE_CONNEXION';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'DESCRIPTION';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'olap_tools/frame_gestion_other_cnx.php';
		$table->taille_ecran		= '600';
		$table->taille_combo		= '100';
		$table->btn_quit			= false;
		$table->titre_ecran			= 'other_cnx';
        // Gestion du raffraichisement de la liste des cubes
        $GLOBALS['SISED_OLAP_SERVER_SETUP']=true;
        
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>

