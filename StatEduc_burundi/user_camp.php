<?php
// session 37 : suppression du session_start() duplique (ligne 1)
// common_ws.php (inclus apres) gere desormais session_start() avec session_status() check.
// Conserver session_start() ici masquait le bug read_and_close (le 2e appel etait ignore
// -> user_camp.php obtenait une session normale par accident, data_camp.php non).

/**
 * user_camp.php
 *
 * Web Service REST - Gestion des campagnes et etablissements pour l'app mobile.
 * Routes : GET /new_camp, /theme_camp, /list_etab, etc.
 * Renvoie la liste des campagnes disponibles, les themes et les ecoles
 * assignes a l'agent collecteur connecte.
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 * @sessions 1-19
 * @modifie Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
 *          Toutes les modifications et nouveautes sont documentees
 *          directement dans le code avec des commentaires en francais.
 */
require_once 'common_ws.php';

require_once $GLOBALS['SISED_PATH_LIB'] . 'adodb_xml/class.ADODB_XML.php';

$app = new \Slim\Slim();

$lib_status =  $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data = $GLOBALS['PARAM_WS']['LIB_DATA'];

$status_ok = $GLOBALS['PARAM_WS']['STATUS_OK'];
$status_ko = $GLOBALS['PARAM_WS']['STATUS_KO'];

//$app->add(new \HttpAuth());

 // PARTIE 3 : cherche les nouvelles campagnes disponibles pour un utilisateur.
// AK-CAMP-03 : deux routes pour le même endpoint :
//   Route A (MOBILE) : /new_camp/:user_id/:id_period/:id_annee
//     Le mobile passe son année active en 3e segment.
//     Si id_annee = 0 ou vide, fallback sur $_SESSION['annee'].
//   Route B (WEB / compatibilité) : /new_camp/:user_id/:id_period
//     Le client web n'envoie que 2 segments -> utilise $_SESSION['annee'].
// L'année de collecte est ainsi pilotée par le mobile, indépendamment
// de l'année par défaut serveur sélectionnée par l'administrateur.

/**
 * AK-CAMP-03 : Récupère la liste des campagnes disponibles pour un agent.
 *
 * @param int    $user_id  ID de l'utilisateur (DICO_FIXE_REGROUPEMENT.ID_USER)
 * @param int    $id_period Période de collecte
 * @param mixed  $id_annee  Année de collecte (CODE_TYPE_ANNEE) ou 0/vide = année serveur
 */
function _new_camp_handler($user_id, $id_period, $id_annee, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status = $GLOBALS['PARAM_WS']['OK'];

	$camp_list = array();

	// AK-CAMP-03 : détermination de l'année active.
	// Si l'app mobile passe un id_annee valide (>0), on l'utilise.
	// Sinon on tombe sur l'année de session serveur (comportement original).
	$id_annee_int = intval($id_annee);
	if ($id_annee_int > 0) {
		$id_year = $id_annee_int;
		error_log('[user_camp] new_camp : année MOBILE passée : ' . $id_year);
	} else {
		$id_year = $_SESSION['annee'];
		error_log('[user_camp] new_camp : année SERVEUR (session) utilisée : ' . $id_year);
	}

	$requete = "SELECT DISTINCT ID_CAMPAGNE, ID_TYPE_REGROUP, ID_TYPE_REGROUP_PARENTS
				FROM DICO_FIXE_REGROUPEMENT
				WHERE ID_USER=".$user_id."
        		AND ID_ANNEE=".$id_year."   
        		AND ID_PERIODE=".$id_period." 
				ORDER BY ID_CAMPAGNE;";

	error_log('[user_camp] new_camp : SQL = ' . $requete);
  
	$camps    = $GLOBALS['conn_dico']->GetAll($requete); 
	
	//$camps =	array_change_key_case_recursive($camps);
	$id_camps = array();
	$type_regroups = array();
	if (count($camps) > 0) {
		foreach ($camps as $row) {
			$id_camp = $row["ID_CAMPAGNE"];
			$type_regroups[$id_camp] = array();
			if (trim($row["ID_TYPE_REGROUP_PARENTS"]) != '') {
				$reg_pars = preg_split("/,/", $row["ID_TYPE_REGROUP_PARENTS"]);
			}
			$reg = $row["ID_TYPE_REGROUP"];
			if (($reg != 0) && !in_array($reg, $type_regroups[$id_camp])) {
				$type_regroups[$id_camp][] = $reg;
				$reg_p_children = get_type_reg_id($reg);
				foreach ($reg_p_children as $child) {
					if (!in_array($child['id'], $type_regroups[$id_camp])) {
						$type_regroups[$id_camp][] = $child['id'];
					}
				}
			}
			foreach ($reg_pars as $reg_par) {
				if (!in_array($reg_par, $type_regroups[$id_camp])) {
					$type_regroups[$id_camp][] = $reg_par;
				}
			}
			if (!in_array($id_camp, $id_camps)) {
				$id_camps[] = $id_camp;
			}
			asort($type_regroups[$id_camp]);
		}
	}

	if (count($id_camps) > 0) {
		foreach ($id_camps as $id_camp) {
			$requete = "SELECT ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].".*
						FROM ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']."
						WHERE ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].".".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']."=".$id_camp.";";
			$camp = $GLOBALS['conn']->GetAll($requete); 
			$type_regroups[$id_camp] = array_values($type_regroups[$id_camp]);

			$camp_list[] = array("id"=>$id_camp, "nom"=>utf8_encode(trim($camp[0][$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']])), "debut"=>"", "fin"=>"", "statut"=>2, "typeregroups" =>implode(",", $type_regroups[$id_camp]));
		}
	}
	$camp_list =	array_change_key_case_recursive($camp_list);
	error_log('[user_camp] new_camp : ' . count($camp_list) . ' campagne(s) retournée(s) pour user=' . $user_id . ' annee=' . $id_year);
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$camp_list);
	echo json_encode($rps);
}

// Route A (MOBILE) : année passée en 3e segment (:id_annee)
// AK-CAMP-03 : cette route est appelée par Flutter avec l'année active mobile.
$app->get('/new_camp/:user_id/:id_period/:id_annee', function ($user_id, $id_period, $id_annee) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	_new_camp_handler($user_id, $id_period, $id_annee, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko);
});

// Route B (COMPATIBILITÉ WEB) : 2 segments seulement, année depuis $_SESSION['annee']
$app->get('/new_camp/:user_id/:id_period', function ($user_id, $id_period) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	_new_camp_handler($user_id, $id_period, 0, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko);
});


// cherche les syst�mes concern�s par une campagne donn�es pour un utilisateur
$app->get('/sys_camp/:user_id/:id_camp', function ($user_id, $id_camp) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status = $GLOBALS['PARAM_WS']['OK'];
	$sys_list = array();
	
	$requete = "SELECT DISTINCT SYSTEME.ID_SYSTEME, SYSTEME.LIBELLE_SYSTEME
				FROM DICO_FIXE_REGROUPEMENT INNER JOIN SYSTEME ON DICO_FIXE_REGROUPEMENT.ID_SYSTEME = SYSTEME.ID_SYSTEME
				WHERE (((DICO_FIXE_REGROUPEMENT.ID_USER)=".$user_id.") AND ((DICO_FIXE_REGROUPEMENT.ID_CAMPAGNE)=".$id_camp."));";
   
	$systems = $GLOBALS['conn_dico']->GetAll($requete); 
	
	foreach ($systems as $sys) {
		$sys_list[] = array("id"=>$sys["ID_SYSTEME"], "nom"=>utf8_encode($sys["LIBELLE_SYSTEME"]));
	}
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$sys_list);
	// recherche les systemes
	echo json_encode($rps);
});

// cherche les regroupements concern�s par une campagne donn�es pour un utilisateur
$app->get('/reg_camp/:user_login/:id_camp/:id_period', function ($user_login, $id_camp, $id_period) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $arbre) {
	$status = $GLOBALS['PARAM_WS']['OK'];
	$reg_list = array();
	
	$sql = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$user_login'";
	$user_id = $GLOBALS['conn_dico']->GetOne($sql);  //$user_login;  
	
	if (!$user_id || $user_id == '') {
		sendError("Utilisateur non trouv&eacute;"); 
		return;
	}  
	                
	$id_year = $_SESSION['annee'];
	$requete = "SELECT ID_CAMPAGNE, ID_CHAINE, ID_TYPE_REGROUP, ID_REGROUP, ID_REGROUP_PARENTS, ID_TYPE_REGROUP_PARENTS
				FROM DICO_FIXE_REGROUPEMENT
				WHERE ID_USER=".$user_id." AND ID_CAMPAGNE=".$id_camp." AND ID_ANNEE=".$id_year." AND ID_PERIODE=".$id_period.";";
    //echo $requete."<br/>"; 
	$camps = $GLOBALS['conn_dico']->GetAll($requete); 
	
	$regroups = array();
	//echo "<pre>"; print_r($camps); echo "<br/><br/>";
	if (count($camps) > 0) {
		foreach ($camps as $row) {
			$ch = $row["ID_CHAINE"];
			$arbre = new arbre($ch);
			if ($regroups[$ch] == NULL) {
				$regroups[$ch] = array();
			}
			$reg_pars = preg_split("/,/", $row["ID_REGROUP_PARENTS"]);
			$type_reg_pars = preg_split("/,/", $row["ID_TYPE_REGROUP_PARENTS"]);
			$type_reg = $row["ID_TYPE_REGROUP"];
			$regs = preg_split("/,/", $row["ID_REGROUP"]);
			if ($type_reg != 0) {
				foreach ($regs as $reg) {
          			$niv_profond = $arbre->get_depht_regroup($reg);
					$reg_childs = $arbre->getchildsid($niv_profond, $reg, $ch);//print_r($reg_childs); echo "<br/><br/>";
					//$reg_childs = array_values($reg_childs[0]); 
					if (count($reg_childs) == 1) {
						$reg_childs[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] = $row["ID_TYPE_REGROUP"];
						$regroups[$ch][] = $reg_childs[0];
					} else {
						$regroups[$ch] = array_merge($regroups[$ch], $reg_childs);
						$regroups[$ch][] = array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']=>$type_reg, $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']=>$reg);
					}
				}
			}
			$i = 0;
			foreach ($reg_pars as $reg_par) {
				$parent = array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']=>$reg_par, $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']=>$type_reg_pars[$i]);
				if (!in_array($parent, $regroups[$ch])) {
					$regroups[$ch][] = $parent;
				}
				$i++;
			}
		}
	}
	
	foreach ($regroups as $ch=>$regs) {
		$arbre = new arbre($ch);
		foreach ($regs as $reg) {
      		$niv_profond = $arbre->get_depht_regroup($reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]);
			$reg_c = $arbre->getregwithparentid($niv_profond, $reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]);
			if (!in_array($reg_c, $reg_list) && $reg_c) {
				$reg_list[] = $reg_c;
			}
		}
	}
	$reg_list =	array_change_key_case_recursive($reg_list);	

	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$reg_list);
	// recherche les regroupements
	echo json_encode($rps);
});

// cherche les types de regroupements concern�s par une campagne donn�es pour un utilisateur
$app->get('/typ_reg_camp/:user_id/:id_camp/:id_types_reg', function ($user_id, $id_camp, $id_types_reg) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status = $GLOBALS['PARAM_WS']['OK'];
	$type_reg_list = array();	
	
	$reg_types = preg_split("/,/", $id_types_reg);
	foreach ($reg_types as $type) {
		$type_reg_list[] = get_type_reg_data($type);
	}
	
	$type_reg_list =	array_change_key_case_recursive($type_reg_list);
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$type_reg_list);
	// recherche les types de regroupements
	echo json_encode($rps);
});

// renvoie les differents statuts existants
$app->get('/etabs_status/', function () use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status = $GLOBALS['PARAM_WS']['OK'];	
	$etab_status = array();

	$requete = "SELECT ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']." AS id, ".$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT']." AS name
				FROM ".$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'].";";
	
	$etab_statuts = $GLOBALS['conn']->GetAll($requete); 
	
	$etab_statuts = array_change_key_case_recursive($etab_statuts);
	for($i=0;$i<count($etab_statuts);$i++) {
		$etab_statuts[$i]['name'] = utf8_encode($etab_statuts[$i]['name']);
	}
				
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$etab_statuts);
	
	echo json_encode($rps);
});

// cherche les �tablissements concern�s par une campagne donn�es pour un utilisateur
$app->get('/etabs_camp/:user_id/:id_camp/:id_period', function ($user_id, $id_camp, $id_period) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status = $GLOBALS['PARAM_WS']['OK'];	
	$etab_list = array();  
	$id_year = $_SESSION['annee'];
	
	$requete = "SELECT ID_CHAINE, ID_SYSTEME, ID_TYPE_REGROUP, ID_REGROUP
				FROM DICO_FIXE_REGROUPEMENT
				WHERE ID_USER=".$user_id." AND ID_CAMPAGNE=".$id_camp." AND ID_ANNEE=".$id_year." AND ID_PERIODE=".$id_period.";";
	
	$regroups = $GLOBALS['conn_dico']->GetAll($requete); 
		
	$with_code_admin =  '';
  	$with_status = '';
	if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) && ($GLOBALS['PARAM']['CONCAT_CODE_ADMIN']) ){
		$with_code_admin = ', E.'.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' AS code ' ;
	}
  	if( exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT'])){
		$with_status = ', E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'].' AS status ' ;
	}
	
	if (count($regroups) > 0) {
		foreach ($regroups as $row) {
			$ch = $row["ID_CHAINE"];
			$sys = $row["ID_SYSTEME"];
			$arbre = new arbre($ch);
			$type_reg = $row["ID_TYPE_REGROUP"];
			$regs = preg_split("/,/", $row["ID_REGROUP"]);
			if ($type_reg == 0) {
				foreach ($regs as $etab_id) { 
					$requete_etab   = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS id, E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS idregroup, E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS nom'. $with_status . $with_code_admin .'
									 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R'.$from_periode.'  
									 WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].$where_periode.'
									 AND E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$etab_id.';';
					//echo $requete_etab;
          $etab = $GLOBALS['conn']->GetAll($requete_etab); 
		  $etab = array_change_key_case_recursive($etab);
          $etab = $etab[0];
					if (!in_array($etab, $etab_list) && $etab != null) {
						$etab['nom'] = utf8_encode($etab['nom']);
						$etab_list[] = $etab;                                       
					}
				}
			} else {
				foreach ($regs as $reg) {
          			$niv_profond = $arbre->get_depht_regroup($reg);
					$etabs = $arbre->get_list_etabs($niv_profond, $reg, $sys, $ch, $id_camp, $id_year, $id_period);
          			//print_r($regroups);echo "<br/><br/><br/>";
					$etabs = array_change_key_case_recursive($etabs);
					foreach ($etabs as $etab) { 
						if (!in_array($etab, $etab_list) && $etab != null) {
							$etab['nom'] = utf8_encode($etab['nom']);
							$etab_list[] = $etab;
						}
					}
				}
			}
		}
	}
	
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$etab_list);
	// recherche les �tablissements
	echo json_encode($rps);
});

// cherche les �tablissements concern�s par une campagne donn�es pour un utilisateur
$app->get('/etabs_camp_zip/:user_login/:id_camp/:id_period', function ($user_login, $id_camp, $id_period) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $app) {
	$status = $GLOBALS['PARAM_WS']['OK'];	
	$etab_list = array();  
	$id_year = $_SESSION['annee'];
	$user_id = $user_login; //$GLOBALS['conn_dico']->GetOne("SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$user_login'"); 
	$requete = "SELECT ID_CHAINE, ID_SYSTEME, ID_TYPE_REGROUP, ID_REGROUP
				FROM DICO_FIXE_REGROUPEMENT
				WHERE ID_USER=".$user_id." AND ID_CAMPAGNE=".$id_camp." AND ID_ANNEE=".$id_year." AND ID_PERIODE=".$id_period.";";
	
	$regroups = $GLOBALS['conn_dico']->GetAll($requete); 
		
	$with_code_admin =  '';
  	$with_status = '';
	if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) && ($GLOBALS['PARAM']['CONCAT_CODE_ADMIN']) ){
		$with_code_admin = ', E.'.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' AS code ' ;
	}
  	if( exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT'])){
		$with_status = ', E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'].' AS status ' ;
	}
	
	if (count($regroups) > 0) {
		foreach ($regroups as $row) {
			$ch = $row["ID_CHAINE"];
			$sys = $row["ID_SYSTEME"];
			$arbre = new arbre($ch);
			$type_reg = $row["ID_TYPE_REGROUP"];
			$regs = preg_split("/,/", $row["ID_REGROUP"]);
			if ($type_reg == 0) {
				foreach ($regs as $etab_id) { 
					$requete_etab   = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS id, E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS idregroup, E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS nom'. $with_status . $with_code_admin .'
									 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R'.$from_periode.'  
									 WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].$where_periode.'
									 AND E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$etab_id.';';
					//echo $requete_etab;
          $etab = $GLOBALS['conn']->GetAll($requete_etab); 
          $etab = $etab[0];
					if (!in_array($etab, $etab_list) && $etab != null) {
						$etab_list[] = $etab;                                       
					}
				}
			} else {
				foreach ($regs as $reg) {
          			$niv_profond = $arbre->get_depht_regroup($reg);
					$etabs = $arbre->get_list_etabs($niv_profond, $reg, $sys, $ch, $id_camp, $id_year, $id_period);
          			//print_r($regroups);echo "<br/>";
					foreach ($etabs as $etab) { 
						if (!in_array($etab, $etab_list) && $etab != null) {
							$etab_list[] = $etab;
						}
					}
				}
			}
		}
	}
	
	$idSchools = array();
	$idRegs = array();
	foreach($etab_list as $etab) {
		$idSchools[] = $etab['ID'];
		$idRegs[] = $etab['IDREGROUP'];
	}
	
	$sqlSchools = "SELECT * FROM ".$GLOBALS['PARAM']['ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']." IN (".join(', ',$idSchools).")";
	$sqlSchoolsReg = "SELECT * FROM ".$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']." IN (".join(', ',$idSchools).") AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']." IN (".join(', ',$idRegs).")";
	
	$rootDir = time();
	$exportDir = $GLOBALS['SISED_PATH']."server-side/import_export/".$rootDir;
	if (!file_exists($exportDir)) {
		mkdir($exportDir, 0777, true);
	}
	$adodbXML = new ADODB_XML("1.0", "ISO-8859-1");
	$all_champs_table = $GLOBALS['conn']->MetaColumns($GLOBALS['PARAM']['ETABLISSEMENT']);
	$schoolFields = array();
	foreach( $all_champs_table as $champ ) {
		$schoolFields[] = $champ->name;
	}	
	$all_champs_table = $GLOBALS['conn']->MetaColumns($GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']);
	$schoolRegFields = array();
	foreach( $all_champs_table as $champ ) {
		$schoolRegFields[] = $champ->name;
	}	  
	$adodbXML->ConvertToXML($GLOBALS['conn'], $sqlSchools, $schoolFields, $exportDir."/".$GLOBALS['PARAM']['ETABLISSEMENT'].".xml");	
	$adodbXML->ConvertToXML($GLOBALS['conn'], $sqlSchoolsReg, $schoolRegFields, $exportDir."/".$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].".xml");	
	if (create_zip($GLOBALS['SISED_PATH']."server-side/import_export", $rootDir)) {
		$fData = file_get_contents($GLOBALS['SISED_PATH']."server-side/import_export/".$rootDir.".zip");
		$app->response->header('Content-Type', 'application/octet-stream');
		$app->response->header('Pragma', "public");
		$app->response->header('Content-disposition:', 'attachment; filename='. $rootDir.'.zip');
		$app->response->header('Content-Transfer-Encoding', 'binary');
		$app->response->header("Content-Description", "File Transfer");
		$app->response->header('Content-Length', filesize($GLOBALS['SISED_PATH']."server-side/import_export/".$rootDir.".zip"));
		$app->response->setBody($fData);		
	} else {	
		$rps = array($lib_status=>$status_ko,$lib_message=>$status_ko,$lib_data=>'');
		// recherche les �tablissements
		echo json_encode($rps);
	}
});

// cherche les chaines de localisation concern�s par une campagne donn�es pour un utilisateur
$app->get('/locs_camp/:user_id/:id_camp', function ($user_id, $id_camp) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status = $GLOBALS['PARAM_WS']['OK'];		
	$loc_list = array();
	
	$requete = "SELECT ID_CAMPAGNE, ID_SYSTEME, ID_CHAINE, ID_TYPE_REGROUP, ID_REGROUP, ID_REGROUP_PARENTS, ID_TYPE_REGROUP_PARENTS
				FROM DICO_FIXE_REGROUPEMENT
				WHERE ID_USER=".$user_id." AND ID_CAMPAGNE=".$id_camp.";";
   
	$camps    = $GLOBALS['conn_dico']->GetAll($requete); 
	
	if (count($camps) > 0) {
		foreach ($camps as $row) {
			$ch = $row["ID_CHAINE"];
			$sys = $row["ID_SYSTEME"];
			$arbre = new arbre($ch);
			$regroups = array();
			$etabs = array();
			$reg_pars = preg_split("/,/", $row["ID_REGROUP_PARENTS"]);
			$type_reg = $row["ID_TYPE_REGROUP"];
			$regs = preg_split("/,/", $row["ID_REGROUP"]);
			if ($type_reg == 0) {
				$etabs = array_merge($etabs, $regs);
			} else {
				foreach ($regs as $reg) {
					$niv_profond = $arbre->get_depht_regroup($reg);
					$reg_childs = $arbre->getchildsid($niv_profond, $reg, $ch);//print_r($reg_childs); echo $reg."<br/><br/>";
					//$reg_childs = array_values($reg_childs[0]); 
					if (count($reg_childs) == 1) {
						if (!in_array($reg_childs[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], $regroups)) {
							$regroups[] = $reg_childs[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
						}
					} else {
						foreach ($reg_childs as $reg_id) { 
							if (!in_array($reg_id[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], $regroups)) {
								$regroups[] = $reg_id[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
							}
						}
						if (!in_array($reg, $regroups)) {
							$regroups[] = $reg;
						}
					}
          			$niv_profond = $arbre->get_depht_regroup($reg);
					$curr_etabs = $arbre->get_list_etabs_ids($niv_profond, $reg, $sys);
					$curr_etabs = array_change_key_case_recursive($curr_etabs);
					foreach ($curr_etabs as $curr_etab) {
						if (!in_array($curr_etab['id'], $etabs)) {
							$etabs[] = $curr_etab['id'];
						}
					}
				}
			}
			foreach ($reg_pars as $reg_par) {
				if (!in_array($reg_par, $regroups)) {
					$regroups[] = $reg_par;
				}
			}
			$loc_list[] = array("idloc"=>(int)($user_id.$id_camp.$sys), "idcamp"=>$id_camp, "idsys"=>$sys, "regroups"=>implode(",", $regroups), "etabs"=>implode(",", $etabs));
		}
	}
	
	$loc_list =	array_change_key_case_recursive($loc_list);
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$loc_list);
	// recherche les chaines de localisation
	echo json_encode($rps);
});


// cherche les privil�ges d'un utilisateur
$app->get('/user_priv/:user_login/:id_camp/:id_period', function ($user_login, $id_camp, $id_period) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $arbre) {
	$status = $GLOBALS['PARAM_WS']['OK'];
	$reg_list = array();
	
	//$sql = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$user_login'";
	$user_id = $user_login;//$GLOBALS['conn_dico']->GetOne($sql);   
	
	if (!$user_id || $user_id == '') {
		sendError("Utilisateur non trouv&eacute;"); 
		return;
	}  
	                
	$id_year = $_SESSION['annee'];
	$requete = "SELECT USER_PRIV, ID_CAMPAGNE, ID_STATUS, ID_SYSTEME, ID_CHAINE, ID_ANNEE, ID_PERIODE, ID_TYPE_REGROUP, ID_REGROUP, ID_REGROUP_PARENTS, ID_TYPE_REGROUP_PARENTS
				FROM DICO_FIXE_REGROUPEMENT
				WHERE ID_USER=".$user_id." AND ID_CAMPAGNE=".$id_camp." AND ID_ANNEE=".$id_year." AND ID_PERIODE=".$id_period.";";
    //echo $requete."<br/>"; 
	$user_privs = $GLOBALS['conn_dico']->GetAll($requete); 	

	$user_privs =	array_change_key_case_recursive($user_privs);
	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$user_privs);
	// recherche les regroupements
	echo json_encode($rps);
});

// SESSION 53 FIX — Hiérarchie localisation pour les établissements (mobile)
// ─────────────────────────────────────────────────────────────────────────────
// Retourne la chaîne de localisation de chaque établissement en utilisant la
// CHAÎNE PRINCIPALE (première dans TYPE_CHAINE_REGROUPEMENT ordonnée par ORDRE)
// qui correspond à ce qu'affiche questionnaire.php ($SESSION['chaine']).
//
// Endpoint : GET /user_camp.php/etab_hier/:id_sys/:id_camp/:etab_ids
//   :id_sys    — identifiant du système éducatif (pour filtrer la chaîne)
//   :id_camp   — identifiant de la campagne (non utilisé ici, réservé)
//   :etab_ids  — liste CSV des IDs d'établissements
//
// Réponse : { se_status:'ok', se_message:'ok',
//             se_data: [ { id: "22222", lib_localisation: "CANKUZO / CENDAJURU / Busyana" }, ... ] }
//
// Algorithme :
//   1. Obtenir l'ID de la chaîne principale pour ce système (= $_SESSION['chaine'])
//   2. Pour chaque étab, trouver son code_regroupement via ETABLISSEMENT_REGROUPEMENT
//      filtré par la chaîne principale (via jointure HIERARCHIE)
//   3. Construire la hiérarchie avec arbre->getparentsid()
//   4. Retourner "PARENT1 / PARENT2 / FEUILLE" pour chaque étab
//
// INTERNATIONAL : cette logique est identique à questionnaire.php — elle
// utilise la chaîne référence du serveur, pas la chaîne de l'agent mobile.
$app->get('/etab_hier/:id_sys/:id_camp/:etab_ids', function ($id_sys, $id_camp, $etab_ids) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status   = $GLOBALS['PARAM_WS']['OK'];
	$hier_list = array();

	// 1. Obtenir la chaîne principale pour ce système éducatif
	//    (= première chaîne ordonnée — correspond à $_SESSION['chaine'] de questionnaire.php)
	$sql_chaine = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' AS id_chaine'
		.' FROM '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']
		.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.(int)$id_sys
		.' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' LIMIT 1';
	$row_chaine = $GLOBALS['conn_dico']->GetRow($sql_chaine);

	if (!$row_chaine || !isset($row_chaine['id_chaine'])) {
		// Fallback : chaîne 1 si aucune trouvée pour ce système
		$id_chaine = 1;
	} else {
		$id_chaine = (int)$row_chaine['id_chaine'];
	}

	// 2. Construire l'arbre pour cette chaîne
	$arbre = new arbre($id_chaine);

	// 3. Nombre de niveaux de la chaîne
	$sql_niv = 'SELECT COUNT(*) FROM '.$GLOBALS['PARAM']['HIERARCHIE']
		.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$id_chaine;
	$nb_niveaux = (int)$GLOBALS['conn']->GetOne($sql_niv);
	$niveau = max(0, $nb_niveaux - 1);

	// 4. Traiter chaque établissement
	$etab_id_list = array_filter(array_map('trim', explode(',', $etab_ids)), function($v){ return $v !== ''; });

	foreach ($etab_id_list as $etab_id) {
		$etab_id = (int)$etab_id;
		if ($etab_id <= 0) continue;

		// Trouver le code_regroupement de l'étab dans la chaîne principale
		// (joint ETABLISSEMENT_REGROUPEMENT avec REGROUPEMENT et HIERARCHIE filtrée par id_chaine)
		$sql_reg = 'SELECT ER.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS code_reg'
			.' FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS ER'
			.', '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS R'
			.', '.$GLOBALS['PARAM']['HIERARCHIE'].' AS H'
			.' WHERE ER.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$etab_id
			.' AND ER.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']
			.' AND R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'=H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']
			.' AND H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$id_chaine
			.' AND H.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'=1'
			.' LIMIT 1';
		$row_reg = $GLOBALS['conn']->GetRow($sql_reg);

		if (!$row_reg || !isset($row_reg['code_reg'])) {
			// Pas de correspondance dans cette chaîne — retourne chaîne vide
			$hier_list[] = array('id' => (string)$etab_id, 'lib_localisation' => '');
			continue;
		}

		$code_reg = (int)$row_reg['code_reg'];

		// Construire la hiérarchie via arbre->getparentsid()
		$hierarchie = $arbre->getparentsid($niveau, $code_reg, $id_chaine);

		$lib_hier = '';
		if (is_array($hierarchie) && count($hierarchie) > 0) {
			$parts = array();
			foreach ($hierarchie as $h) {
				$lib = isset($h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]) 
					? trim(utf8_encode($h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']])) 
					: '';
				if ($lib !== '') $parts[] = $lib;
			}
			$lib_hier = implode(' / ', $parts);
		}

		$hier_list[] = array('id' => (string)$etab_id, 'lib_localisation' => $lib_hier);
	}

	$rps = array($lib_status=>$status_ok, $lib_message=>$status, $lib_data=>$hier_list);
	echo json_encode($rps);
});

function get_type_reg_data($code_type_reg) {
	$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS id, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS nom, '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS ordre FROM '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$code_type_reg.';';            
	
	$result = $GLOBALS['conn']->GetAll($requete); 
	$result =	array_change_key_case_recursive($result);
	$result[0]['nom'] = utf8_encode($result[0]['nom']);
	return($result[0]);
}

function get_type_reg_id($code_type_reg) {
	$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS id FROM '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'>'.$code_type_reg.';';            
	
	$result = $GLOBALS['conn']->GetAll($requete); 
	return($result);
}

function comp_reg($reg1, $reg2) {  
  return strcasecmp($reg1['nom'], $reg2['nom']);
}

$app->run();
 
function sendError($message) {
	$posts = array('se_statut'=>101,'se_message'=>$message,'se_data'=>NULL);	
	echo json_encode($posts);
}
?>
