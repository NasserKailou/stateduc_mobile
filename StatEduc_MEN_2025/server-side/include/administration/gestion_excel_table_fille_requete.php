<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_SESSION[secteur]', 'lib'=>'Systeme', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_REQUETE', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[nom_requete]', 'lib'=>'nom_req', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_CHAMP', 'type'=>'text', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[nom_champ]', 'lib'=>'nom_champ', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'TYPE_DIM', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'type_dim', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'TABLE_REF', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'table_ref', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'CHAMP', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'champ', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DIM_LIBELLE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'dim_lib', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'DIM_SQL', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'dim_sql', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'RECORDS_ROW', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'rec_row', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'RECORDS_COL', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'rec_col', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table				= 'DICO_EXCEL_REQ_ASSOC_FIELDS';
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_CHAMP';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_excel_table_fille_requete.php';
		$table->taille_ecran		= '600';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GestChildTable';
		/*echo '<pre>';
		print_r($_POST);*/
		
		$table->hidden_btn = array('btn_new','btn_add','btn_del');

		$table->run();
		
		if($table->act_MAJ == 1){
			switch($table->action){
				case 'Add' :
				case 'Upd' :{
					$id_systeme = $_SESSION['secteur'];
					//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
					${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
					//Fin ajout Hebie
					$sql = $_POST['DIM_SQL'];
					$chaine_eval ="\$sql_eval =\"$sql\";";						
					eval ($chaine_eval);	
					$records = $GLOBALS['conn']->GetAll($sql_eval);
					$records_minus_val_indet = array();
					foreach($records as $rec){
						if($rec[$GLOBALS['PARAM']['CODE'].'_'.$_POST['TABLE_REF']] <> 255){
							$records_minus_val_indet[] = $rec[$GLOBALS['PARAM']['CODE'].'_'.$_POST['TABLE_REF']];
						}
					}
					$records_tab_ref_dim = $GLOBALS['conn']->GetAll("SELECT * FROM ".$_POST['TABLE_REF']." ORDER BY ".$GLOBALS['PARAM']['ORDRE'].'_'.$_POST['TABLE_REF']);
					$name_records_tab_ref_dim = array();
					foreach($records_tab_ref_dim as $rec){
						if(in_array($rec[$GLOBALS['PARAM']['CODE'].'_'.$_POST['TABLE_REF']],$records_minus_val_indet)){
							$name_records_tab_ref_dim[] = $rec[$GLOBALS['PARAM']['LIBELLE'].'_'.$_POST['TABLE_REF']];
						}
					}
					$k = 0;
					$table_records = '';
					if(is_array($name_records_tab_ref_dim)){
						foreach($name_records_tab_ref_dim as $rec){
							if($k == 0)	$table_records .= $rec;
							else $table_records .= '#'.$rec;
							$k++;
						}
					}
					if($_POST['TYPE_DIM']=='dimension_ligne'){
						$requete='UPDATE DICO_EXCEL_REQ_ASSOC_FIELDS SET RECORDS_ROW=\''.$table_records.'\', RECORDS_COL=\'\' WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND NOM_REQUETE=\''.$_POST['NOM_REQUETE'].'\' AND NOM_CHAMP=\''.$_POST['NOM_CHAMP'].'\'';
					}elseif($_POST['TYPE_DIM']=='dimension_colonne'){
						$requete='UPDATE DICO_EXCEL_REQ_ASSOC_FIELDS SET RECORDS_ROW=\'\', RECORDS_COL=\''.$table_records.'\' WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND NOM_REQUETE=\''.$_POST['NOM_REQUETE'].'\' AND NOM_CHAMP=\''.$_POST['NOM_CHAMP'].'\'';
					}
					if ($GLOBALS['conn_dico']->Execute($requete) === false){
						$GLOBALS['theme_data_MAJ_ok'] 	= false;
						print ' <br> error updating :<br> --- '.$requete.' --- <br>'; 
					}
					break;
				}
			}
		}
		
?>
