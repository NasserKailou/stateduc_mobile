<br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php $champs = array();
		$champs[] = array('nom'=>'ID_REQUETE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_req', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_SYSTEME', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'$_SESSION[secteur]', 'lib'=>'Systeme', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_REQUETE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_req', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'SQL_REQUETE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'sql_req', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		//$champs[] = array('nom'=>'EXPORT', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'export', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE', 'type'=>'int', 'cle'=>'', 'incr'=>'1', 'val'=>'', 'lib'=>'ordre_req', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		$table->table				= 'DICO_EXCEL_REQUETE_ASSOC';
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_REQUETE';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'administration/frame_gestion_req_assoc_aggreg_export.php';
		$table->taille_ecran		= '650';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GestReqAssoc';
		
		$table->run();
		
		if($table->act_MAJ == 1){
			if(isset($GLOBALS['conn'])){
				switch($table->action){
					case 'Add' :{
						$maj_db = true ;
						$_POST['SQL_REQUETE'] = preg_replace('/;/','',$_POST['SQL_REQUETE']);
						$req_maj_db = "CREATE VIEW ".$_POST['NOM_REQUETE']." AS ".$_POST['SQL_REQUETE'];
						if ($GLOBALS['conn']->Execute($req_maj_db) === false){
							echo "<script language='javascript' type='text/javascript'>\n";
							echo "alert(\"Syntaxe Error in SQL Expression\")\n";
							echo "</script>\n";
							echo 'Syntaxe Error:<br>'.$req_maj_db.'<br>';
						}
						$req = "DELETE FROM DICO_EXCEL_REQ_ASSOC_FIELDS WHERE ID_SYSTEME = ".$_SESSION['secteur']." AND NOM_REQUETE='".$_POST['NOM_REQUETE']."'";
						$res = $GLOBALS['conn_dico']->Execute($req);
						$chps 	= $GLOBALS['conn']->MetaColumns($_POST['NOM_REQUETE']);
						$j = 1;
						$chps_req	= array();
						foreach($chps as $chp ){
							$chps_req[] = $chp->name;
						}
						$ID_SYSTEME =  $_SESSION['secteur'];
						$NOM_REQUETE = $_POST['NOM_REQUETE'];
						$champs_filtre = array();
						$champs_filtre[] = $GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
						$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
						if($GLOBALS['PARAM']['FILTRE']) $champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
						$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
						if(is_array($chps_req) && count($chps_req)>0){
							foreach( $chps_req as $chp ){
								$nom_chp = $chp;
								if(!in_array($nom_chp,$champs_filtre)){
									$req = "INSERT INTO DICO_EXCEL_REQ_ASSOC_FIELDS(ID_SYSTEME,NOM_REQUETE,NOM_CHAMP,EXPORT,ORDRE) VALUES ($ID_SYSTEME,'$NOM_REQUETE','$nom_chp',1,$j)";
									$res = $GLOBALS['conn_dico']->Execute($req);
									$j++;
								}
							}
						}
						break;
					}
					case 'Upd' :{
						$maj_db = true ;
						if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql') { 
							$req_maj_db = "DROP VIEW ".$_POST['NOM_REQUETE'];
						}else{
							$req_maj_db = "DROP TABLE ".$_POST['NOM_REQUETE'];
						}
						$GLOBALS['conn']->Execute($req_maj_db);
						$_POST['SQL_REQUETE'] = preg_replace('/;/','',$_POST['SQL_REQUETE']);
						$req_maj_db = "CREATE VIEW ".$_POST['NOM_REQUETE']." AS ".$_POST['SQL_REQUETE'];
						if ($GLOBALS['conn']->Execute($req_maj_db) === false){
							echo "<script language='javascript' type='text/javascript'>\n";
							echo "alert(\"Syntaxe Error in SQL Expression\")\n";
							echo "</script>\n";
							echo 'Syntaxe Error:<br>'.$req_maj_db.'<br>';
						}
						$req = "DELETE FROM DICO_EXCEL_REQ_ASSOC_FIELDS WHERE ID_SYSTEME = ".$_SESSION['secteur']." AND NOM_REQUETE='".$_POST['NOM_REQUETE']."'";
						$res = $GLOBALS['conn_dico']->Execute($req);
						$chps 	= $GLOBALS['conn']->MetaColumns($_POST['NOM_REQUETE']);
						$j = 1;
						$chps_req	= array();
						foreach($chps as $chp ){
							$chps_req[] = $chp->name;
						}
						$ID_SYSTEME =  $_SESSION['secteur'];
						$NOM_REQUETE = $_POST['NOM_REQUETE'];
						$champs_filtre = array();
						$champs_filtre[] = $GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
						$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
						if($GLOBALS['PARAM']['FILTRE']) $champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
						$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
						if(is_array($chps_req) && count($chps_req)>0){
							foreach( $chps_req as $chp ){
								$nom_chp = $chp;
								if(!in_array($nom_chp,$champs_filtre)){
									$req = "INSERT INTO DICO_EXCEL_REQ_ASSOC_FIELDS(ID_SYSTEME,NOM_REQUETE,NOM_CHAMP,EXPORT,ORDRE) VALUES ($ID_SYSTEME,'$NOM_REQUETE','$nom_chp',1,$j)";
									$res = $GLOBALS['conn_dico']->Execute($req);
									$j++;
								}
							}
						}
						break;
					}
					case 'Del' :{
						$maj_db = true ;
						if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql') { 
							$req_maj_db = "DROP VIEW ".$_POST['NOM_REQUETE'];
						}else{
							$req_maj_db = "DROP TABLE ".$_POST['NOM_REQUETE'];
						}
						if ($GLOBALS['conn']->Execute($req_maj_db) === false){
							echo "<script language='javascript' type='text/javascript'>\n";
							echo "alert(\"Syntaxe Error in SQL Expression\")\n";
							echo "</script>\n";
							echo 'Syntaxe Error:<br>'.$req_maj_db.'<br>';
						}
						$req = "DELETE FROM DICO_EXCEL_REQ_ASSOC_FIELDS WHERE ID_SYSTEME = ".$_SESSION['secteur']." AND NOM_REQUETE='".$_POST['NOM_REQUETE']."'";
						$res = $GLOBALS['conn_dico']->Execute($req);
						break;
					}
				}
			}
			/*echo "<script type='text/Javascript' language='javascript'>\n";
			echo "	document.location.href= 'administration.php?val=aggregated_db_structure';\n";
			echo "</script>\n";*/
		}
?>

