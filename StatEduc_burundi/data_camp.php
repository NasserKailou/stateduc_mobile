<?php

/**
 * data_camp.php
 *
 * Web Service REST - Generation et envoi des frames (templates HTML) pour formulaires mobiles.
 * Route : GET /theme_frame/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
 * Utilise frame_mobile.class.php pour produire les fichiers ws_mob_*.frame.
 * MODIFIE session 23 : integration de _mobile_libelle_clean() via frame_mobile
 *   pour corriger Bug A (entites HTML brutes) et Bug B (mojibake ISO-8859-1).
 *
 * SESSION 38 :
 *   - theme_camp : suppression du filtre FRAME <> '' qui bloquait les themes sans frame pre-generee
 *   - theme_camp : remplacement de utf8_encode() par mb_convert_encoding() (deprecie PHP 8.2)
 *   - theme_camp : correction de la boucle de tri while ($nb > $nbo) -> protection anti-boucle infinie
 *   - theme_camp : ajout de error_log() pour diagnostics serveur
 *   - theme_camp : include FRAME dans les champs retournes pour eviter un 2eme SELECT dans html_theme_camp
 *   - html_theme_camp : robustesse si FRAME vide ou NULL
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 * @sessions 1-38
 * @modifie Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
 *          Toutes les modifications et nouveautes sont documentees
 *          directement dans le code avec des commentaires en francais.
 */
require_once 'common_ws.php';

$app = new \Slim\Slim();

$lib_status =  $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data = $GLOBALS['PARAM_WS']['LIB_DATA'];

$status_ok = $GLOBALS['PARAM_WS']['STATUS_OK'];
$status_ko = $GLOBALS['PARAM_WS']['STATUS_KO'];

$app->add(new \HttpAuth());

 // renvoie les thèmes pour une campagne
$app->get('/theme_camp/:id_camp/:id_sys/:code_lang', function ($id_camp, $id_sys, $code_lang) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status = $GLOBALS['PARAM_WS']['OK'];	
	$qst_list = array();
	
	// SESSION 38 : suppression du filtre FRAME <> '' qui cachait tous les themes
	// dont le fichier .frame n'avait pas encore ete genere cote serveur.
	// Le champ FRAME est maintenant inclus dans le SELECT pour eviter un 2eme
	// SELECT dans html_theme_camp et pour permettre au client de savoir si le
	// formulaire HTML est disponible.
	$requete = "SELECT DICO_THEME_SYSTEME.ID_THEME_SYSTEME AS id,
	                   DICO_THEME_SYSTEME.ID AS id_theme,
	                   DICO_TRADUCTION.LIBELLE AS title,
	                   DICO_THEME_SYSTEME.APPARTENANCE AS idcamp,
	                   DICO_THEME_SYSTEME.ID_SYSTEME AS idsys,
	                   DICO_THEME_SYSTEME.PRECEDENT AS pre,
	                   DICO_THEME_SYSTEME.FRAME AS frame
	            FROM DICO_TRADUCTION
	            INNER JOIN DICO_THEME_SYSTEME
	                ON DICO_TRADUCTION.CODE_NOMENCLATURE = DICO_THEME_SYSTEME.ID_THEME_SYSTEME
	            WHERE DICO_THEME_SYSTEME.APPARTENANCE = ".$id_camp."
	              AND DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_sys."
	              AND DICO_TRADUCTION.NOM_TABLE = 'DICO_THEME_LIB_MENU'
	              AND DICO_TRADUCTION.CODE_LANGUE = 'fr';";

	// SESSION 38 : log diagnostique — SQL + paramètres reçus

	if (!isset($GLOBALS['conn_dico']) || $GLOBALS['conn_dico'] === false) {
		error_log('[data_camp] theme_camp — ERREUR: conn_dico non disponible');
		echo json_encode(array($lib_status=>$status_ko, $lib_message=>'DB unavailable', $lib_data=>array()));
		return;
	}

	$qst_list = $GLOBALS['conn_dico']->GetAll($requete);

	// SESSION 38 : log du nombre de lignes retournées

	if ($qst_list === false || !is_array($qst_list)) {
		$db_err = $GLOBALS['conn_dico']->ErrorMsg();
		error_log('[data_camp] theme_camp — ERREUR DB: '.$db_err);
		echo json_encode(array($lib_status=>$status_ko, $lib_message=>'DB error: '.$db_err, $lib_data=>array()));
		return;
	}

	$qst_list = array_change_key_case_recursive($qst_list);	

	$qst_ord = array();
	$nb = count($qst_list);
	$idx_curr = 0;

	if ($nb > 0) {
		// --- Étape 1 : ajout du flag 'filter' et conversion de l'encodage ---
		for ($i = 0; $i < $nb; $i++) {
			$qst = $qst_list[$i];

			$requete_zone = "SELECT CHAMP_PERE FROM DICO_ZONE WHERE ID_THEME=".$qst["id_theme"];
			$result = $GLOBALS['conn_dico']->GetAll($requete_zone);
			$theme_periodique = false;
			if (is_array($result)) {
				foreach ($result as $rs) {
					if ($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']==$rs['CHAMP_PERE']) {
						$theme_periodique=true;
						break;
					}
				}
			}
			$qst_list[$i]['filter'] = $theme_periodique ? 1 : 0;

			// SESSION 38 : remplacement de utf8_encode() (deprecie PHP 8.2)
			// par mb_convert_encoding() avec fallback sur iconv() si mbstring absent.
			if (function_exists('mb_convert_encoding')) {
				$qst_list[$i]['title'] = mb_convert_encoding($qst['title'], 'UTF-8', 'ISO-8859-1');
			} elseif (function_exists('iconv')) {
				$qst_list[$i]['title'] = iconv('ISO-8859-1', 'UTF-8//TRANSLIT//IGNORE', $qst['title']);
			} else {
				$qst_list[$i]['title'] = utf8_encode($qst['title']); // fallback ultime
			}
		}

		// --- Étape 2 : tri selon la chaîne PRECEDENT (pre=0 = premier élément) ---
		// SESSION 38 : protection anti-boucle infinie.
		// L'algorithme original while ($nb > $nbo) pouvait boucler indéfiniment
		// si la chaîne PRECEDENT était brisée (ex. un pre pointant vers un id
		// inexistant dans le résultat). On ajoute un compteur de sécurité $max_iter
		// et on sort dès que $nbo ne progresse plus.

		// Trouver le premier élément (pre == 0)
		$first_idx = -1;
		for ($i = 0; $i < $nb; $i++) {
			if ($qst_list[$i]["pre"] == 0) {
				$first_idx = $i;
				break;
			}
		}

		if ($first_idx === -1) {
			// Aucun élément avec pre=0 trouvé → chaîne corrompue, on retourne la liste brute sans tri
			error_log('[data_camp] theme_camp — AVERTISSEMENT: aucun element pre=0 trouve, retour liste brute');
			foreach ($qst_list as &$q) {
				unset($q["pre"]);
			}
			unset($q);
			$qst_ord = array_values($qst_list);
		} else {
			// Construire la liste triée
			$first = $qst_list[$first_idx];
			unset($first["pre"]);
			$qst_ord[0] = $first;
			unset($qst_list[$first_idx]);
			$qst_list = array_values($qst_list); // re-indexer

			$nbo = 0;
			$max_iter = $nb * $nb + 1; // SESSION 38 : borne sup anti-boucle infinie
			$iter = 0;

			while ($nbo < count($qst_ord) && $iter < $max_iter) {
				$iter++;
				$id_curr = $qst_ord[$nbo]['id'];
				$nbo++;
				$found = false;
				foreach ($qst_list as $key => $qst) {
					if ($qst["pre"] == $id_curr) {
						$tmp = $qst;
						unset($tmp["pre"]);
						$qst_ord[] = $tmp;
						unset($qst_list[$key]);
						$qst_list = array_values($qst_list); // re-indexer
						$found = true;
						break;
					}
				}
				if (!$found && count($qst_list) > 0) {
					// SESSION 38 : chaîne brisée — les éléments restants sont ajoutés en fin de liste
					error_log('[data_camp] theme_camp — AVERTISSEMENT: chaine PRECEDENT brisee apres id='.$id_curr.', '
					          .count($qst_list).' element(s) restant(s) ajoute(s) en fin de liste');
					foreach ($qst_list as $q) {
						unset($q["pre"]);
						$qst_ord[] = $q;
					}
					break;
				}
			}

			if ($iter >= $max_iter) {
				error_log('[data_camp] theme_camp — ERREUR: boucle de tri interrompue apres '.$iter.' iterations (chaine PRECEDENT corrompue)');
			}
		}
	}

	// SESSION 38 : log du résultat final
	if (count($qst_ord) > 0) {
	}

	$rps = array($lib_status=>$status_ok, $lib_message=>$status, $lib_data=>$qst_ord); 
	echo json_encode($rps);
});

 // renvoie les règles associés aux zones d'un thème pour une campagne
$app->get('/regle_theme_camp/:id_theme/:id_sys', function ($id_theme, $id_sys) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status = $GLOBALS['PARAM_WS']['OK'];	
	$regle_theme_list = array();
	// Il faut vérifier s'il existe des fils dans DICO_THEME_SYSTEME
	$requete = "SELECT DICO_REGLE_ZONE.*, DICO_ZONE.CHAMP_PERE
FROM DICO_THEME_SYSTEME INNER JOIN ((DICO_REGLE_ZONE_ASSOC INNER JOIN DICO_ZONE ON DICO_REGLE_ZONE_ASSOC.ID_ZONE = DICO_ZONE.ID_ZONE) INNER JOIN DICO_REGLE_ZONE ON DICO_REGLE_ZONE_ASSOC.ID_REGLE_ZONE = DICO_REGLE_ZONE.ID_REGLE_ZONE) ON DICO_THEME_SYSTEME.ID = DICO_ZONE.ID_THEME
WHERE DICO_THEME_SYSTEME.ID_THEME_SYSTEME=".$id_theme.";";
   
	$theme_regles    = $GLOBALS['conn_dico']->GetAll($requete); 
	if (count($theme_regles) > 0) {
		foreach ($theme_regles as $row) {
			$regle_theme_list[] = array("champ"=>$row["CHAMP_PERE"], "type"=>$row["TYPE_DONNEES"]==null?"":$row["TYPE_DONNEES"], "taille"=>$row["TAILLE_DONNEES"]==null?"":$row["TYPE_DONNEES"], "format"=>$row["FORMAT_DONNEES"]==null?"":$row["TYPE_DONNEES"], "inter"=>$row["INTERVALLE_VALEURS"], "min_val"=>$row["VALEUR_MINIMALE"]==null?"":$row["VALEUR_MINIMALE"], "max_val"=>$row["VALEUR_MAXIMALE"]==null?"":$row["VALEUR_MAXIMALE"], "pres"=>$row["CONTROLE_PRESENCE"], "paru"=>$row["CONTROLE_PARUTION"], "obli"=>$row["CONTROLE_OBLIGATION"], "int_ref"=>$row["CONTROLE_INTEGRITE_REF"], "edits"=>$row["CONTROLE_EDITION"], "enums"=>$row["VALEURS_ENUM"]==null?"":$row["VALEURS_ENUM"], "uniq"=>$row["CONTROLE_UNICITE"]);
		}
	}

	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$regle_theme_list);
	//recherche les questions pour une campagne
	echo json_encode($rps);
});

// renvoie le formulaire html pour une question
$app->get('/html_theme_camp/:id_camp/:id_theme/:code_lang', function ($id_camp, $id_theme, $code_lang) use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko) {
	$status = $GLOBALS['PARAM_WS']['OK'];	
	$html = "";
	
	$requete = "SELECT DICO_THEME_SYSTEME.FRAME
				FROM DICO_THEME_SYSTEME
				WHERE DICO_THEME_SYSTEME.ID_THEME_SYSTEME=".$id_theme.";";

	$frame = $GLOBALS['conn_dico']->GetAll($requete); 

	// SESSION 38 : robustesse — si FRAME vide ou NULL, retourner une erreur propre
	if (!is_array($frame) || count($frame) === 0 || empty($frame[0]['FRAME'])) {
		error_log('[data_camp] html_theme_camp — FRAME vide ou NULL pour id_theme='.$id_theme);
		$rps = array($lib_status=>$status_ko, $lib_message=>'Formulaire HTML indisponible (FRAME vide)', $lib_data=>'');
		echo json_encode($rps);
		return;
	}

	//$path = 'questionnaire/'.$code_lang.'/ws_mob_'.$frame[0]['FRAME'];
	$path = 'questionnaire/fr/ws_mob_'.$frame[0]['FRAME'];
	
	$html = $GLOBALS['SISED_AURL'].$path;

	$rps = array($lib_status=>$status_ok,$lib_message=>$status,$lib_data=>$html);
	// recherche formulaire html
	echo json_encode($rps);
});

$app->run();
 
?>
