<br/><br/>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php
		$champs = array();
		$champs[] = array('nom'=>'USER_PRIV', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'user_priv', 'obli'=>'', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'ID_CAMPAGNE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'Campagne', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_STATUS', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'Status', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_USER', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_user]', 'lib'=>'id_user', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'Secteur', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_CHAINE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'Chaine', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_ANNEE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'Annee', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_PERIODE', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'Periode', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_TYPE_REGROUP', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'TypeLocalite', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_REGROUP', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'', 'lib'=>'id_regroup', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_REGROUP_PARENTS', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_regroup_parents', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_TYPE_REGROUP_PARENTS', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'id_type_regroup_parents', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$table 										= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table							= 'DICO_FIXE_REGROUPEMENT';
		$table->champs 						= $champs;
		$table->nom_champ_combo		= 'USER_PRIV';
		$table->frame							= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_user_priv.php';
		//$table->code_libelle_trad	= 'USER_PRIV';
		$table->titre_ecran				= 'UserEntFixRegroup';
		$table->taille_ecran			= '800';
		if(isset($_GET['id_user']) && $_GET['id_user']==0)
			$table->btn_quit					= false;
		else
			$table->btn_quit					= true;
		//$table->tab_champs_trad['USER_PRIV'] = array('table' => 'DICO_FIXE_REGROUPEMENT', 'libelle'=>'LIBELLE_TRAD');

		$table->hidden_btn = array('btn_add','btn_upd');
		
		$req_ord    = 'SELECT ORDRE_GROUPE FROM ADMIN_GROUPES WHERE CODE_GROUPE ='.$_SESSION['groupe'];
		$ord_grp     =  $GLOBALS['conn_dico']->GetOne($req_ord);
		$req_ord_user    = 'SELECT ORDRE_GROUPE FROM ADMIN_GROUPES WHERE CODE_GROUPE ='.$_SESSION['id_groupe'];
		$ord_grp_user     =  $GLOBALS['conn_dico']->GetOne($req_ord_user);
		if($ord_grp >= $ord_grp_user && $_SESSION['groupe']<>1){
			$table->hidden_btn[] = 'btn_new';
			$table->hidden_btn[] = 'btn_del';
			$table->hidden_btn[] = 'btn_ann';
		}
		
		$table->run();
		/*
		if( ($table->act_MAJ == 1) and ($table->action == 'Del') and ($table->ok_action 	== 1) ){
				$requete = 'DELETE FROM DICO_REGLE_ZONE_ASSOC WHERE  ID_REGLE_ZONE = '.trim($_POST['ID_REGLE_ZONE']);
				if ($GLOBALS['conn_dico']->Execute($requete) === false){}
		}
		*/			

?>

