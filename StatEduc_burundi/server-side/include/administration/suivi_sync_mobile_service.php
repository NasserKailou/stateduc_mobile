<?php
/**
 * suivi_sync_mobile_service.php
 *
 * Service AJAX (JSON) de l'ecran « Suivi synchronisation mobile » (branche ak_burundi).
 * Appele par suivi_sync_mobile.php via administration.php?val=suivi_sync_mobile_svc&action=...
 *
 * Actions (GET) :
 *   filters   : listes annees / campagnes / secteurs / periodes / chaines pour les selecteurs
 *   regroups  : &id_chaine=&code_parent=&depht=  -> regroupements enfants (drill-down)
 *   matrix    : &id_camp=&id_annee=&id_sector=[&id_periode=][&id_chaine=&code_reg=&depht=][&statut=]
 *               -> synthese etablissement x theme (U14/U15)
 *   detail    : &id_camp=&id_annee=&code_etab=     -> historique DATA_SAVING_LOGS de l'etablissement
 *   export    : memes params que matrix, renvoie un CSV (Content-Disposition attachment)
 * Actions (POST, U18) :
 *   set_statut : id_annee, id_camp, id_sector, code_etab, id_theme|'' , code_filtre|'', statut, commentaire
 *   reopen     : id_annee, id_camp, code_etab, id_theme|''  -> repasse en RECU (leve VALIDE/VERROUILLE/A_CORRIGER)
 *
 * Securite : session admin valide obligatoire ($_SESSION['valide']) ; pas d'ecriture SQL sans cast entier ;
 *            libelles quotes via ADODB qstr().
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 */
if (!isset($_SESSION['valide']) || !$_SESSION['valide']) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('se_statut' => 101, 'se_message' => 'session_end', 'se_data' => null));
    return;
}
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
require_once $GLOBALS['SISED_PATH'] . 'sync_lib.php';

function ak_svc_out($data, $statut = 200, $msg = 'ok') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('se_statut' => $statut, 'se_message' => $msg, 'se_data' => ak_sync_utf8($data)));
}
function ak_svc_get($k, $def = '') { return isset($_GET[$k]) ? trim((string)$_GET[$k]) : $def; }
function ak_svc_post($k, $def = '') { return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $def; }

/** Etablissements d'un perimetre (chaine + regroupement) pour un secteur ; sans regroupement = tous les etabs du secteur. */
function ak_svc_etabs_perimetre($id_sector, $id_chaine, $code_reg, $depht) {
    $conn = $GLOBALS['conn'];
    $P = $GLOBALS['PARAM'];
    $tab_e = $P['ETABLISSEMENT']; $code_col = $P['CODE_ETABLISSEMENT']; $nom_col = $P['NOM_ETABLISSEMENT'];
    $with_code = ($P['CODE_ADMINISTRATIF'] !== '' && exist_champ_in_table($P['CODE_ADMINISTRATIF'], $tab_e)) ? ", E." . $P['CODE_ADMINISTRATIF'] . " AS code" : ", '' AS code";
    $out = array();
    if ($id_chaine !== '' && $code_reg !== '') {
        $arbre = new arbre((int)$id_chaine);
        $niv = ($depht !== '') ? (int)$depht : $arbre->get_depht_regroup((int)$code_reg);
        $etabs = $arbre->get_list_etabs($niv, (int)$code_reg, (int)$id_sector, (int)$id_chaine);
        if (is_array($etabs)) {
            foreach ($etabs as $e) {
                $e = array_change_key_case($e, CASE_LOWER);
                $out[] = array('id' => (int)$e['id'], 'nom' => $e['nom'], 'code' => isset($e['code']) ? $e['code'] : '');
            }
        }
        return $out;
    }
    // Sans regroupement : tous les etablissements du secteur (via ETABLISSEMENT.CODE_TYPE_SYSTEME_ENSEIGNEMENT si present)
    $sys_col = $P['CODE'] . '_' . $P['TYPE_SYSTEME_ENSEIGNEMENT'];
    $where = exist_champ_in_table($sys_col, $tab_e) ? " WHERE E." . $sys_col . "=" . (int)$id_sector : "";
    $rows = $conn->GetAll("SELECT E." . $code_col . " AS id, E." . $nom_col . " AS nom" . $with_code . " FROM " . $tab_e . " AS E" . $where . " ORDER BY E." . $nom_col);
    if (is_array($rows)) foreach ($rows as $r) { $r = array_change_key_case($r, CASE_LOWER); $out[] = array('id' => (int)$r['id'], 'nom' => $r['nom'], 'code' => $r['code']); }
    return $out;
}

$action = ak_svc_get('action', ak_svc_post('action', ''));
$P = $GLOBALS['PARAM'];
$conn = $GLOBALS['conn']; $dico = $GLOBALS['conn_dico'];

switch ($action) {

case 'filters': {
    $annees = array();
    if (isset($_SESSION['tab_annees']) && is_array($_SESSION['tab_annees'])) {
        foreach ($_SESSION['tab_annees'] as $a) {
            $a = array_change_key_case($a, CASE_UPPER);
            $annees[] = array('code' => (int)$a[$P['CODE'] . '_' . $P['TYPE_ANNEE']], 'libelle' => $a[$P['LIBELLE'] . '_' . $P['TYPE_ANNEE']]);
        }
    }
    $camps = array();
    $rows = $conn->GetAll("SELECT " . $P['CODE'] . "_" . $P['TYPE_RATTACHEMENT'] . " AS id, " . $P['LIBELLE'] . "_" . $P['TYPE_RATTACHEMENT'] . " AS lib FROM " . $P['TYPE_RATTACHEMENT'] . " ORDER BY " . $P['ORDRE'] . "_" . $P['TYPE_RATTACHEMENT']);
    if (is_array($rows)) foreach ($rows as $r) { $r = array_change_key_case($r, CASE_LOWER); $camps[] = array('id' => (int)$r['id'], 'libelle' => $r['lib']); }
    $secteurs = array();
    if (isset($_SESSION['tab_secteur']) && is_array($_SESSION['tab_secteur'])) {
        foreach ($_SESSION['tab_secteur'] as $s) {
            $s = array_change_key_case($s, CASE_UPPER);
            $secteurs[] = array('id' => (int)$s[$P['CODE'] . '_' . $P['TYPE_SYSTEME_ENSEIGNEMENT']], 'libelle' => $s[$P['LIBELLE'] . '_' . $P['TYPE_SYSTEME_ENSEIGNEMENT']]);
        }
    }
    $periodes = array();
    if (isset($_SESSION['tab_periodes']) && is_array($_SESSION['tab_periodes'])) {
        foreach ($_SESSION['tab_periodes'] as $p) {
            $p = array_change_key_case($p, CASE_UPPER);
            $periodes[] = array('id' => (int)$p[$P['CODE'] . '_' . $P['TYPE_PERIODE']], 'libelle' => $p[$P['LIBELLE'] . '_' . $P['TYPE_PERIODE']]);
        }
    }
    $chaines = array();
    if (isset($_SESSION['tab_chaines']) && is_array($_SESSION['tab_chaines'])) {
        foreach ($_SESSION['tab_chaines'] as $c) {
            $c = array_change_key_case($c, CASE_UPPER);
            $chaines[] = array('id' => (int)$c[$P['CODE'] . '_' . $P['TYPE_CHAINE_REGROUPEMENT']], 'libelle' => $c[$P['LIBELLE'] . '_' . $P['TYPE_CHAINE_REGROUPEMENT']],
                               'secteur' => isset($c[$P['CODE'] . '_' . $P['TYPE_SYSTEME_ENSEIGNEMENT']]) ? (int)$c[$P['CODE'] . '_' . $P['TYPE_SYSTEME_ENSEIGNEMENT']] : null);
        }
    }
    $agents = array();
    $rows = $dico->GetAll("SELECT CODE_USER, NOM_USER, NOM_LONG_USER, CODE_GROUPE FROM ADMIN_USERS ORDER BY NOM_USER");
    if (is_array($rows)) foreach ($rows as $r) { $r = array_change_key_case($r, CASE_UPPER); $agents[] = array('login' => $r['NOM_USER'], 'nom' => $r['NOM_LONG_USER'], 'groupe' => (int)$r['CODE_GROUPE']); }
    ak_svc_out(array(
        'annees' => $annees, 'annee_active' => isset($_SESSION['annee']) ? (int)$_SESSION['annee'] : 0,
        'campagnes' => $camps, 'secteurs' => $secteurs, 'secteur_actif' => isset($_SESSION['secteur']) ? (int)$_SESSION['secteur'] : 0,
        'periodes' => $periodes, 'chaines' => $chaines, 'agents' => $agents,
        'migration_ok' => ak_sync_logs_extended($dico), 'statut_table_ok' => ak_sync_table_exists($dico, 'SYNC_ETAB_STATUT'),
        'admin' => isset($_SESSION['login']) ? $_SESSION['login'] : '',
    ));
    break;
}

case 'regroups': {
    $id_chaine = (int)ak_svc_get('id_chaine', 0);
    $code_parent = ak_svc_get('code_parent', '');
    $depht = (int)ak_svc_get('depht', 0);
    $arbre = new arbre($id_chaine);
    $out = array();
    $code_c = $P['CODE'] . '_' . $P['REGROUPEMENT']; $lib_c = $P['LIBELLE'] . '_' . $P['REGROUPEMENT'];
    $type_c = $P['CODE'] . '_' . $P['TYPE_REGROUPEMENT']; $pere_c = $P['REG_CODE_REGROUPEMENT'];
    if (!isset($arbre->chaine[$depht])) { ak_svc_out(array()); break; }
    $type_reg = $arbre->chaine[$depht][$type_c];
    $req = "SELECT " . $code_c . ", " . $lib_c . " FROM " . $P['REGROUPEMENT'] . " WHERE " . $type_c . "=" . (int)$type_reg;
    if ($code_parent !== '' && $depht > 0) $req .= " AND " . $pere_c . "=" . (int)$code_parent;
    $req .= " ORDER BY " . $P['ORDRE_TRIE_REGROUPEMENT'];
    $rows = $conn->GetAll($req);
    if (is_array($rows)) foreach ($rows as $r) { $r = array_change_key_case($r, CASE_UPPER); $out[] = array('code' => (int)$r[$code_c], 'libelle' => $r[$lib_c], 'depht' => $depht, 'has_next' => isset($arbre->chaine[$depht]['NIVEAU_CHAINE_SUIVANT']) && is_array($arbre->chaine[$depht]['NIVEAU_CHAINE_SUIVANT']) && count($arbre->chaine[$depht]['NIVEAU_CHAINE_SUIVANT']) > 0); }
    ak_svc_out($out);
    break;
}

case 'matrix':
case 'export': {
    $id_camp = (int)ak_svc_get('id_camp', 0); $id_annee = (int)ak_svc_get('id_annee', $_SESSION['annee']);
    $id_sector = (int)ak_svc_get('id_sector', $_SESSION['secteur']);
    $id_periode = ak_svc_get('id_periode', ''); $id_periode = ($id_periode === '' ? null : (int)$id_periode);
    $filtre_statut = strtoupper(ak_svc_get('statut', ''));
    $agent = ak_svc_get('agent', '');
    $etabs = ak_svc_etabs_perimetre($id_sector, ak_svc_get('id_chaine', ''), ak_svc_get('code_reg', ''), ak_svc_get('depht', ''));
    if ($agent !== '') {
        // Restreindre au perimetre de l'agent
        $ag = ak_sync_agent_etabs($agent, $id_camp, $id_annee, $id_sector);
        $ids = array(); foreach ($ag as $e) $ids[(int)$e['id']] = 1;
        $tmp = array(); foreach ($etabs as $e) if (isset($ids[(int)$e['id']])) $tmp[] = $e;
        $etabs = $tmp;
    }
    $themes = ak_sync_expected_themes($id_camp, $id_sector);
    $summary = ak_sync_etab_summary($id_camp, $id_annee, $id_sector, $etabs, $id_periode);
    if ($filtre_statut !== '') {
        $tmp = array(); foreach ($summary as $s) if ($s['statut'] === $filtre_statut) $tmp[] = $s; $summary = $tmp;
    }
    if ($action === 'export') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="suivi_sync_mobile_' . $id_camp . '_' . $id_annee . '_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel
        $head = array('Code etab', 'Etablissement', 'Code admin', 'Statut global', 'Themes attendus', 'OK', 'Erreur', 'Manquants', 'Derniere reception', 'Statut admin');
        foreach ($themes as $th) $head[] = ak_sync_utf8($th['title']);
        echo implode(';', $head) . "\r\n";
        foreach ($summary as $s) {
            $line = array($s['id'], ak_sync_utf8($s['nom']), ak_sync_utf8($s['code']), $s['statut'], $s['nb_attendus'], $s['nb_ok'], $s['nb_ko'], $s['nb_manquants'], $s['derniere_reception'], $s['admin'] ? $s['admin']['statut'] : '');
            foreach ($themes as $th) { $c = $s['themes'][$th['id']]; $line[] = $c['statut'] . ($c['date'] ? ' (' . $c['date'] . ')' : ''); }
            $esc = array(); foreach ($line as $v) { $v = str_replace('"', '""', (string)$v); $esc[] = '"' . $v . '"'; }
            echo implode(';', $esc) . "\r\n";
        }
        return;
    }
    $totaux = array('COMPLET' => 0, 'PARTIEL' => 0, 'ERREUR' => 0, 'AUCUN' => 0, 'total' => count($summary));
    foreach ($summary as $s) if (isset($totaux[$s['statut']])) $totaux[$s['statut']]++;
    ak_svc_out(array('themes' => $themes, 'etabs' => $summary, 'totaux' => $totaux, 'migration_ok' => ak_sync_logs_extended($dico)));
    break;
}

case 'detail': {
    $id_camp = (int)ak_svc_get('id_camp', 0); $id_annee = (int)ak_svc_get('id_annee', 0); $code_etab = (int)ak_svc_get('code_etab', 0);
    $ext = ak_sync_logs_extended($dico);
    $req = "SELECT * FROM DATA_SAVING_LOGS WHERE CODE_CAMPAGNE=" . $id_camp . " AND CODE_ANNEE=" . $id_annee . " AND CODE_ECOLE=" . $code_etab . " ORDER BY LOG_DATE_TIME DESC";
    $rows = $dico->GetAll($req);
    $logs = array();
    if (is_array($rows)) {
        $n = 0;
        foreach ($rows as $r) {
            $r = array_change_key_case($r, CASE_UPPER);
            $logs[] = array(
                'date' => $r['LOG_DATE_TIME'], 'user' => $r['CODE_USER'], 'id_theme' => (int)$r['ID_THEME_SYSTEME'], 'periode' => $r['CODE_PERIODE'],
                'statut' => $r['STATUT_OPERATION'],
                'nb_insert' => $ext ? $r['NB_INSERT'] : null, 'nb_update' => $ext ? $r['NB_UPDATE'] : null, 'nb_delete' => $ext ? $r['NB_DELETE'] : null,
                'nb_unchanged' => $ext ? $r['NB_UNCHANGED'] : null, 'message' => $ext ? $r['MESSAGE_ERREUR'] : null,
                'sync_id' => $ext ? $r['SYNC_ID'] : null, 'app_version' => $ext ? $r['APP_VERSION'] : null, 'ip' => $ext ? $r['IP_CLIENT'] : null,
            );
            if (++$n >= 500) break;
        }
    }
    $statuts = array();
    if (ak_sync_table_exists($dico, 'SYNC_ETAB_STATUT')) {
        $rows = $dico->GetAll("SELECT * FROM SYNC_ETAB_STATUT WHERE ACTIF=1 AND ID_CAMPAGNE=" . $id_camp . " AND CODE_ANNEE=" . $id_annee . " AND CODE_ETABLISSEMENT=" . $code_etab);
        if (is_array($rows)) foreach ($rows as $r) { $r = array_change_key_case($r, CASE_UPPER); $statuts[] = array('id_theme' => $r['ID_THEME_SYSTEME'], 'code_filtre' => $r['CODE_FILTRE'], 'statut' => $r['STATUT'], 'commentaire' => $r['COMMENTAIRE'], 'admin' => $r['CODE_USER_ADMIN'], 'date' => $r['DATE_STATUT']); }
    }
    $hist = array();
    if (ak_sync_table_exists($dico, 'SYNC_ETAB_STATUT_HIST')) {
        $rows = $dico->GetAll("SELECT * FROM SYNC_ETAB_STATUT_HIST WHERE ID_CAMPAGNE=" . $id_camp . " AND CODE_ANNEE=" . $id_annee . " AND CODE_ETABLISSEMENT=" . $code_etab . " ORDER BY DATE_HIST DESC");
        if (is_array($rows)) foreach ($rows as $r) { $r = array_change_key_case($r, CASE_UPPER); $hist[] = array('id_theme' => $r['ID_THEME_SYSTEME'], 'ancien' => $r['ANCIEN_STATUT'], 'nouveau' => $r['NOUVEAU_STATUT'], 'commentaire' => $r['COMMENTAIRE'], 'admin' => $r['CODE_USER_ADMIN'], 'date' => $r['DATE_HIST']); }
    }
    ak_svc_out(array('logs' => $logs, 'statuts' => $statuts, 'historique' => $hist));
    break;
}

case 'set_statut': {
    $statut = strtoupper(ak_svc_post('statut', ''));
    $comment = ak_svc_post('commentaire', '');
    if ($statut === AK_SYNC_ST_A_CORRIGER && $comment === '') { ak_svc_out(null, 400, 'Commentaire obligatoire pour une demande de correction'); break; }
    if (function_exists('mb_check_encoding') && mb_check_encoding($comment, 'UTF-8')) $comment = utf8_decode($comment); // base SISED ISO-8859-1
    $ok = ak_sync_set_statut((int)ak_svc_post('id_annee', 0), (int)ak_svc_post('id_camp', 0), (int)ak_svc_post('code_etab', 0),
        ak_svc_post('id_theme', ''), ak_svc_post('code_filtre', ''), $statut, $comment,
        isset($_SESSION['login']) ? $_SESSION['login'] : 'admin', (int)ak_svc_post('id_sector', 0));
    ak_svc_out(array('ok' => $ok), $ok ? 200 : 400, $ok ? 'ok' : 'Echec enregistrement statut (table SYNC_ETAB_STATUT absente ?)');
    break;
}

case 'reopen': {
    $ok = ak_sync_set_statut((int)ak_svc_post('id_annee', 0), (int)ak_svc_post('id_camp', 0), (int)ak_svc_post('code_etab', 0),
        ak_svc_post('id_theme', ''), ak_svc_post('code_filtre', ''), AK_SYNC_ST_RECU,
        'Reouverture par ' . (isset($_SESSION['login']) ? $_SESSION['login'] : 'admin'),
        isset($_SESSION['login']) ? $_SESSION['login'] : 'admin', (int)ak_svc_post('id_sector', 0));
    ak_svc_out(array('ok' => $ok), $ok ? 200 : 400, $ok ? 'ok' : 'Echec reouverture');
    break;
}

default:
    ak_svc_out(null, 400, 'action inconnue');
}
?>
