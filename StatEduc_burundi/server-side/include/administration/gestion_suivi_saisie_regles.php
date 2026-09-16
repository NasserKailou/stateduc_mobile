<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_REGLE_SUIVI', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'idregsuivi', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		//$champs[] = array('nom'=>'ACTIVER_CRITERE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'activer', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE_REGLE_SUIVI', 'type'=>'int', 'cle'=>'', 'incr'=>'1', 'val'=>'', 'lib'=>'ordregsuivi', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SQL_REGLE_SUIVI', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sqlregsuivi', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');

		$table 										= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table							= 'DICO_REGLE_SUIVI';
		$table->champs 						= $champs;
		$table->nom_champ_combo		= 'ID_REGLE_SUIVI';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_suivi_saisie_regles.php';
		$table->code_libelle_trad	= 'ID_REGLE_SUIVI';
		$table->titre_ecran				= 'gestregsuivi';
		$table->taille_ecran			= '600';
		$table->btn_quit					= false;
		
		$table->tab_champs_trad['ID_REGLE_SUIVI'] = array('table' => 'DICO_REGLE_SUIVI', 'libelle'=>'LIBELLE_TRAD');
				
		$table->run();
		
		if($table->act_MAJ == 1){
			$req_crit_sys = "SELECT COUNT(ID_REGLE_SUIVI) FROM DICO_REGLE_SUIVI_SYSTEME WHERE ID_REGLE_SUIVI = ".$_POST['ID_REGLE_SUIVI']." AND ID_SYSTEME =".$_SESSION['secteur'];
			$exist_crit_sys = $GLOBALS['conn_dico']->getOne($req_crit_sys);
			switch($table->action){
				case 'Add' :{
					$maj_dico = true ;
					if($_POST['ACTIVER_CRITERE'] && $_POST['ACTIVER_CRITERE']==1){
						$req_maj_dico = 'INSERT INTO DICO_REGLE_SUIVI_SYSTEME (ID_REGLE_SUIVI,ID_SYSTEME,ACTIVER_CRITERE) VALUES 
										('.$_POST['ID_REGLE_SUIVI'].','.$_SESSION['secteur'].','.$_POST['ACTIVER_CRITERE'].')';
					}	
					break;
				}
				case 'Upd' :{
					$maj_dico = true ;
					if($_POST['ACTIVER_CRITERE'] && $_POST['ACTIVER_CRITERE']==1){
						if(!$exist_crit_sys){
							$req_maj_dico = 'INSERT INTO DICO_REGLE_SUIVI_SYSTEME (ID_REGLE_SUIVI,ID_SYSTEME,ACTIVER_CRITERE) VALUES 
										('.$_POST['ID_REGLE_SUIVI'].','.$_SESSION['secteur'].','.$_POST['ACTIVER_CRITERE'].')';
						}
					}else{
						if($exist_crit_sys){
							$req_maj_dico = 'DELETE FROM DICO_REGLE_SUIVI_SYSTEME WHERE ID_REGLE_SUIVI = '.$_POST['ID_REGLE_SUIVI'].' AND ID_SYSTEME = '.$_SESSION['secteur'];
						}
					}
					break;
				}
				case 'Del' :{
					$maj_dico = true ;
					$req_maj_dico = 'DELETE FROM DICO_REGLE_SUIVI_SYSTEME WHERE ID_REGLE_SUIVI = '.$_POST['ID_REGLE_SUIVI'];
					break;
				}
			}
			if( isset($maj_dico) and ($maj_dico == true) and $req_maj_dico != ''){
				if ($GLOBALS['conn_dico']->Execute($req_maj_dico) === false){
					echo '<br>sql error: '.$req_maj_dico.'<br>';
				}
				print "<script type='text/javascript' language='javascript'>\n";
				print "load_action(".$table->i_enr.",'Open')\n";
				print "</script>\n";
			}
		}	
?>

