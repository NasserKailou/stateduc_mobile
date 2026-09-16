<?php
/**
 * sync_status_ws.php  —  AK-SYNC (branche ak_burundi)
 *
 * Web Service REST — LECTURE SEULE — statut de synchronisation vu du serveur.
 * Permet a l'application mobile de reconcilier son etat local (form_sync_status)
 * avec la verite serveur (DATA_SAVING_LOGS) et de recevoir les demandes de
 * correction de l'administrateur (SYNC_ETAB_STATUT).
 *
 * Routes (HTTP Basic identique aux autres WS mobiles) :
 *
 *   GET /etab/:login/:id_camp/:id_sector/:id_etab/:id_annee            (U16)
 *       -> pour chaque theme attendu : dernier statut, date, compteurs, statut admin
 *
 *   GET /camp/:login/:id_camp/:id_sector/:id_annee                     (U16/U17)
 *       -> synthese par etablissement du perimetre de l'agent (COMPLET/PARTIEL/ERREUR/VIDE/AUCUN)
 *          + derniere reception + statut admin. Utilise pour la reconciliation a l'ouverture.
 *
 *   GET /feedback/:login/:id_camp/:id_annee                            (U19)
 *       -> demandes de correction / verrous actifs (SYNC_ETAB_STATUT) sur le perimetre de l'agent
 *
 * Reponse : { se_status:200, se_message:'ok', se_data:{...} }  |  { se_status:400, se_message:'ko', se_data:'message' }
 * Toutes les chaines sont converties en UTF-8 (base ISO-8859-1).
 *
 * FAIL-OPEN cote mobile : si ce fichier est absent (ancien serveur) -> 404 -> le mobile ignore.
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 */
require_once 'common_ws.php';
require_once 'sync_lib.php';
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';

$app = new \Slim\Slim();
$lib_status  = $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data    = $GLOBALS['PARAM_WS']['LIB_DATA'];
$status_ok   = $GLOBALS['PARAM_WS']['STATUS_OK'];
$status_ko   = $GLOBALS['PARAM_WS']['STATUS_KO'];
$app->add(new \HttpAuth());

/** Verifie que le login existe dans ADMIN_USERS ; retourne CODE_USER ou 0. */
function ak_ws_check_user($login) {
	$row = $GLOBALS['conn_dico']->GetRow("SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER LIKE " . $GLOBALS['conn_dico']->qstr($login));
	if (!is_array($row) || count($row) == 0) return 0;
	$row = array_change_key_case($row, CASE_UPPER);
	return (int)$row['CODE_USER'];
}

function ak_ws_ko($msg) {
	echo json_encode(array($GLOBALS['PARAM_WS']['LIB_STATUS'] => $GLOBALS['PARAM_WS']['STATUS_KO'], $GLOBALS['PARAM_WS']['LIB_MESSAGE'] => $GLOBALS['PARAM_WS']['KO'], $GLOBALS['PARAM_WS']['LIB_DATA'] => $msg));
}
function ak_ws_ok($data) {
	echo json_encode(array($GLOBALS['PARAM_WS']['LIB_STATUS'] => $GLOBALS['PARAM_WS']['STATUS_OK'], $GLOBALS['PARAM_WS']['LIB_MESSAGE'] => $GLOBALS['PARAM_WS']['OK'], $GLOBALS['PARAM_WS']['LIB_DATA'] => ak_sync_utf8($data)));
}

/** Annee effective : parametre URL prioritaire, sinon session, sinon PARAM_DEFAUT. */
function ak_ws_year($id_annee) {
	if ($id_annee !== '' && $id_annee !== '0' && $id_annee !== null) return (int)$id_annee;
	if (isset($_SESSION['annee']) && $_SESSION['annee'] !== '') return (int)$_SESSION['annee'];
	$d = $GLOBALS['conn_dico']->GetOne('SELECT CODE_ANNEE FROM PARAM_DEFAUT');
	return (int)$d;
}

// ---------------------------------------------------------------------------
// U16 : statut serveur d'un etablissement, theme par theme
// ---------------------------------------------------------------------------
$app->get('/etab/:login/:id_camp/:id_sector/:id_etab/:id_annee', function ($login, $id_camp, $id_sector, $id_etab, $id_annee) {
	header('Content-Type: application/json; charset=utf-8');
	if (ak_ws_check_user($login) <= 0) { ak_ws_ko("Utilisateur '" . $login . "' inconnu"); return; }
	$year = ak_ws_year($id_annee);
	$etabs = array(array('id' => (int)$id_etab, 'nom' => '', 'code' => ''));
	$summary = ak_sync_etab_summary($id_camp, $year, $id_sector, $etabs, null);
	$row = count($summary) ? $summary[0] : null;
	if ($row === null) { ak_ws_ko('Aucune donnee'); return; }
	$themes = array();
	foreach ($row['themes'] as $t => $cell) {
		$adm = ak_sync_get_statut($year, $id_camp, (int)$id_etab, $t, null);
		$themes[] = array(
			'id_theme'      => (int)$t,
			'libelle'       => $cell['libelle'],
			'statut'        => $cell['statut'],          // OK | VIDE | ERREUR | AUCUN
			'date'          => $cell['date'],            // 'Y/m/d H:i:s' serveur
			'user'          => $cell['user'],
			'nb_rows'       => $cell['nb_rows'],
			'nb_tentatives' => $cell['nb_tentatives'],
			'message'       => $cell['message'],
			'statut_admin'  => $adm ? $adm['STATUT'] : null,
			'commentaire'   => $adm ? $adm['COMMENTAIRE'] : null,
			'admin'         => $adm ? $adm['CODE_USER_ADMIN'] : null,
			'date_admin'    => $adm ? $adm['DATE_STATUT'] : null,
		);
	}
	ak_ws_ok(array(
		'server_time'        => ak_sync_now_iso(),
		'code_annee'         => $year,
		'id_camp'            => (int)$id_camp,
		'id_sector'          => (int)$id_sector,
		'code_etab'          => (int)$id_etab,
		'statut'             => $row['statut'],
		'derniere_reception' => $row['derniere_reception'],
		'statut_admin'       => $row['admin'] ? $row['admin']['statut'] : null,
		'commentaire_admin'  => $row['admin'] ? $row['admin']['commentaire'] : null,
		'themes'             => $themes,
	));
});

// ---------------------------------------------------------------------------
// U16 / U17 : synthese de toutes les ecoles du perimetre de l'agent
// ---------------------------------------------------------------------------
$app->get('/camp/:login/:id_camp/:id_sector/:id_annee', function ($login, $id_camp, $id_sector, $id_annee) {
	header('Content-Type: application/json; charset=utf-8');
	if (ak_ws_check_user($login) <= 0) { ak_ws_ko("Utilisateur '" . $login . "' inconnu"); return; }
	$year  = ak_ws_year($id_annee);
	$etabs = ak_sync_agent_etabs($login, $id_camp, $year, $id_sector);
	$summary = ak_sync_etab_summary($id_camp, $year, $id_sector, $etabs, null);
	$out = array();
	foreach ($summary as $row) {
		$themes = array();
		foreach ($row['themes'] as $t => $cell) {
			$themes[] = array('id_theme' => (int)$t, 'statut' => $cell['statut'], 'date' => $cell['date'], 'nb_rows' => $cell['nb_rows']);
		}
		$out[] = array(
			'code_etab'          => $row['id'],
			'nom'                => $row['nom'],
			'statut'             => $row['statut'],
			'nb_attendus'        => $row['nb_attendus'],
			'nb_ok'              => $row['nb_ok'],
			'nb_ko'              => $row['nb_ko'],
			'nb_vide'            => $row['nb_vide'],
			'nb_manquants'       => $row['nb_manquants'],
			'derniere_reception' => $row['derniere_reception'],
			'statut_admin'       => $row['admin'] ? $row['admin']['statut'] : null,
			'commentaire_admin'  => $row['admin'] ? $row['admin']['commentaire'] : null,
			'themes'             => $themes,
		);
	}
	ak_ws_ok(array('server_time' => ak_sync_now_iso(), 'code_annee' => $year, 'id_camp' => (int)$id_camp, 'id_sector' => (int)$id_sector, 'etabs' => $out));
});

// ---------------------------------------------------------------------------
// U19 : demandes de correction / verrous actifs pour l'agent
// ---------------------------------------------------------------------------
$app->get('/feedback/:login/:id_camp/:id_annee', function ($login, $id_camp, $id_annee) {
	header('Content-Type: application/json; charset=utf-8');
	if (ak_ws_check_user($login) <= 0) { ak_ws_ko("Utilisateur '" . $login . "' inconnu"); return; }
	$year = ak_ws_year($id_annee);
	$conn = $GLOBALS['conn_dico'];
	$items = array();
	if (ak_sync_table_exists($conn, 'SYNC_ETAB_STATUT')) {
		// Perimetre : etablissements journalises par l'agent OU affectes via DICO_FIXE_REGROUPEMENT (tous secteurs)
		$etab_ids = array();
		$logged = $conn->GetAll("SELECT DISTINCT CODE_ECOLE FROM DATA_SAVING_LOGS WHERE CODE_USER=" . $conn->qstr($login) . " AND CODE_CAMPAGNE=" . (int)$id_camp . " AND CODE_ANNEE=" . $year);
		if (is_array($logged)) foreach ($logged as $l) { $l = array_change_key_case($l, CASE_UPPER); $etab_ids[(int)$l['CODE_ECOLE']] = 1; }
		$secteurs = $conn->GetAll("SELECT DISTINCT DFR.ID_SYSTEME FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU WHERE AU.NOM_USER LIKE " . $conn->qstr($login) . " AND DFR.ID_USER=AU.CODE_USER AND DFR.ID_CAMPAGNE=" . (int)$id_camp . " AND DFR.ID_ANNEE=" . $year);
		if (is_array($secteurs)) {
			foreach ($secteurs as $s) {
				$s = array_change_key_case($s, CASE_UPPER);
				foreach (ak_sync_agent_etabs($login, $id_camp, $year, (int)$s['ID_SYSTEME']) as $e) $etab_ids[(int)$e['id']] = 1;
			}
		}
		if (count($etab_ids) > 0) {
			$req = "SELECT CODE_ETABLISSEMENT, ID_THEME_SYSTEME, CODE_FILTRE, CODE_SECTEUR, STATUT, COMMENTAIRE, CODE_USER_ADMIN, DATE_STATUT FROM SYNC_ETAB_STATUT"
			     . " WHERE ACTIF=1 AND CODE_ANNEE=" . $year . " AND ID_CAMPAGNE=" . (int)$id_camp
			     . " AND STATUT IN ('A_CORRIGER','VALIDE','VERROUILLE')"
			     . " AND CODE_ETABLISSEMENT IN (" . implode(',', array_keys($etab_ids)) . ")";
			$rows = $conn->GetAll($req);
			if (is_array($rows)) {
				foreach ($rows as $r) {
					$r = array_change_key_case($r, CASE_UPPER);
					$items[] = array(
						'code_etab'   => (int)$r['CODE_ETABLISSEMENT'],
						'id_theme'    => ($r['ID_THEME_SYSTEME'] === null || $r['ID_THEME_SYSTEME'] === '') ? null : (int)$r['ID_THEME_SYSTEME'],
						'code_filtre' => ($r['CODE_FILTRE'] === null || $r['CODE_FILTRE'] === '') ? null : (int)$r['CODE_FILTRE'],
						'id_sector'   => ($r['CODE_SECTEUR'] === null || $r['CODE_SECTEUR'] === '') ? null : (int)$r['CODE_SECTEUR'],
						'statut'      => $r['STATUT'],
						'commentaire' => (string)$r['COMMENTAIRE'],
						'admin'       => (string)$r['CODE_USER_ADMIN'],
						'date'        => (string)$r['DATE_STATUT'],
					);
				}
			}
		}
	}
	ak_ws_ok(array('server_time' => ak_sync_now_iso(), 'code_annee' => $year, 'id_camp' => (int)$id_camp, 'items' => $items));
});

$app->run();
?>
