<?php
/**
 * sync_lib.php
 *
 * Bibliotheque commune — synchronisation / tracabilite mobile (branche ak_burundi).
 * Fonctions PHP natives (compatibles PHP 7.3, sans framework) partagees par :
 *   - data_save.php            (journal enrichi, idempotence, verrou, ACK)
 *   - sync_status_ws.php       (WS lecture statut / feedback)
 *   - suivi_sync_mobile*.php   (ecran admin)
 *
 * TOUTES les fonctions sont defensives : si une colonne/table de la migration
 * n'existe pas encore, elles retombent sur le comportement historique
 * (INSERT 10 colonnes, pas de court-circuit, pas de verrou).
 *
 * Cle de soumission (U05) :
 *   SUBMISSION_KEY = (CODE_ANNEE, ID_CAMPAGNE, ID_THEME_SYSTEME, CODE_ETABLISSEMENT, CODE_FILTRE|NULL)
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 */

if (!defined('AK_SYNC_LIB_LOADED')) {
define('AK_SYNC_LIB_LOADED', true);

// Statuts SYNC_ETAB_STATUT
define('AK_SYNC_ST_RECU',       'RECU');
define('AK_SYNC_ST_A_CORRIGER', 'A_CORRIGER');
define('AK_SYNC_ST_VALIDE',     'VALIDE');
define('AK_SYNC_ST_VERROUILLE', 'VERROUILLE');

// Statuts d'operation DATA_SAVING_LOGS (en plus de OKSAVE / KOSAVE / KO historiques)
define('AK_SYNC_OP_REJET_ANNEE',  'REJET_ANNEE');
define('AK_SYNC_OP_REJET_ACCES',  'REJET_ACCES');
define('AK_SYNC_OP_REJET_CAMP',   'REJET_CAMP_FERMEE');
define('AK_SYNC_OP_REJET_VERROU', 'REJET_VERROU');
define('AK_SYNC_OP_DUPLICATE',    'DUPLICATE');

/* -------------------------------------------------------------------------
 * Utilitaires generiques
 * ---------------------------------------------------------------------- */

/** Horodatage serveur au format historique DATA_SAVING_LOGS ('Y/m/d H:i:s'). */
function ak_sync_now() {
    return date('Y/m/d H:i:s');
}

/** Horodatage ISO 8601 (pour l'ACK JSON cote mobile). */
function ak_sync_now_iso() {
    return date('c');
}

/** Normalise un filtre : 'null', '', '0', null -> null ; sinon entier. */
function ak_sync_norm_filter($f) {
    if ($f === null) return null;
    $f = trim((string)$f);
    if ($f === '' || strtolower($f) === 'null' || $f === '0') return null;
    return (int)$f;
}

/** Valeur SQL pour un entier nullable. */
function ak_sync_sql_int_or_null($v) {
    return ($v === null || $v === '') ? 'NULL' : (string)(int)$v;
}

/** Valeur SQL pour une chaine nullable (quotee via ADODB). */
function ak_sync_sql_str_or_null($conn, $v, $maxlen = 0) {
    if ($v === null || $v === '') return 'NULL';
    $v = (string)$v;
    if ($maxlen > 0 && strlen($v) > $maxlen) $v = substr($v, 0, $maxlen);
    return $conn->qstr($v);
}

/** Verifie si une colonne existe (mise en cache par requete). Fallback prudent = false. */
function ak_sync_column_exists($conn, $table, $column) {
    static $cache = array();
    $k = strtoupper($table . '.' . $column);
    if (isset($cache[$k])) return $cache[$k];
    $exists = false;
    try {
        $cols = $conn->MetaColumnNames($table);
        if (is_array($cols)) {
            foreach ($cols as $c) {
                if (strtoupper($c) === strtoupper($column)) { $exists = true; break; }
            }
        }
    } catch (Exception $e) {
        $exists = false;
    }
    $cache[$k] = $exists;
    return $exists;
}

/** Verifie si une table existe. */
function ak_sync_table_exists($conn, $table) {
    static $cache = array();
    $k = strtoupper($table);
    if (isset($cache[$k])) return $cache[$k];
    $exists = false;
    try {
        $tables = $conn->MetaTables('TABLES');
        if (is_array($tables)) {
            foreach ($tables as $t) {
                if (strtoupper($t) === $k) { $exists = true; break; }
            }
        }
    } catch (Exception $e) {
        $exists = false;
    }
    $cache[$k] = $exists;
    return $exists;
}

/** true si la migration U09 est appliquee (colonne SYNC_ID presente). */
function ak_sync_logs_extended($conn) {
    return ak_sync_column_exists($conn, 'DATA_SAVING_LOGS', 'SYNC_ID');
}

/** Lecture d'un en-tete HTTP (compatible Apache/FastCGI). */
function ak_sync_header($name) {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER[$key])) return trim($_SERVER[$key]);
    if (function_exists('getallheaders')) {
        $h = getallheaders();
        foreach ($h as $k => $v) {
            if (strcasecmp($k, $name) === 0) return trim($v);
        }
    }
    return '';
}

/** Adresse IP du client (derriere proxy : premier X-Forwarded-For). */
function ak_sync_client_ip() {
    $xff = ak_sync_header('X-Forwarded-For');
    if ($xff !== '') {
        $parts = explode(',', $xff);
        return trim($parts[0]);
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
}

/** UUID v4 valide ? */
function ak_sync_is_uuid($s) {
    return (bool)preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', (string)$s);
}

/**
 * Empreinte deterministe d'une soumission (U22).
 * Le corps POST est trie par cle pour etre insensible a l'ordre des champs.
 * Les cles techniques (_sync_id, _app_version, switch_theme_id, save_and_*) sont exclues.
 */
function ak_sync_hash($login, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $id_annee, $post) {
    $clean = array();
    if (is_array($post)) {
        foreach ($post as $k => $v) {
            if (in_array($k, array('_sync_id', '_app_version', 'switch_theme_id', 'save_and_prev', 'save_and_next'))) continue;
            $clean[$k] = is_array($v) ? json_encode($v) : (string)$v;
        }
    }
    ksort($clean);
    $base = implode('|', array($login, $id_camp, $id_sector, $id_theme, $id_etab, (string)ak_sync_norm_filter($id_filter), $id_annee));
    return sha1($base . '#' . http_build_query($clean));
}

/* -------------------------------------------------------------------------
 * Journal DATA_SAVING_LOGS (U09 / U10)
 * ---------------------------------------------------------------------- */

/**
 * Insere une ligne de journal. Remplace saveLogInfo() en conservant sa signature
 * de base ; les champs supplementaires sont ecrits seulement si la migration est faite.
 *
 * @param array $extra  cles optionnelles : sync_id, sync_hash, nb_insert, nb_update,
 *                      nb_delete, nb_unchanged, message, app_version, ip
 * @return bool
 */
function ak_sync_log($user, $date_time, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $statut, $id_annee = 0, $extra = array()) {
    $conn = $GLOBALS['conn_dico'];
    $f = ak_sync_norm_filter($id_filter);
    $cols = "CODE_USER, LOG_DATE_TIME, ID_THEME_SYSTEME, CODE_ANNEE, CODE_PERIODE, CODE_CAMPAGNE, CODE_SECTEUR, CODE_ECOLE, CODE_FILTRE, STATUT_OPERATION";
    $vals = $conn->qstr((string)$user) . ",'" . $date_time . "'," . (int)$id_theme . "," . (int)$id_annee . "," .
            ak_sync_sql_int_or_null($f) . "," . (int)$id_camp . "," . (int)$id_sector . "," . (int)$id_etab . "," .
            ak_sync_sql_int_or_null($f) . ",'" . $statut . "'";

    if (ak_sync_logs_extended($conn)) {
        $cols .= ", SYNC_ID, SYNC_HASH, NB_INSERT, NB_UPDATE, NB_DELETE, NB_UNCHANGED, MESSAGE_ERREUR, APP_VERSION, IP_CLIENT";
        $vals .= ", " . ak_sync_sql_str_or_null($conn, isset($extra['sync_id']) ? $extra['sync_id'] : null, 36)
               . ", " . ak_sync_sql_str_or_null($conn, isset($extra['sync_hash']) ? $extra['sync_hash'] : null, 40)
               . ", " . ak_sync_sql_int_or_null(isset($extra['nb_insert']) ? $extra['nb_insert'] : null)
               . ", " . ak_sync_sql_int_or_null(isset($extra['nb_update']) ? $extra['nb_update'] : null)
               . ", " . ak_sync_sql_int_or_null(isset($extra['nb_delete']) ? $extra['nb_delete'] : null)
               . ", " . ak_sync_sql_int_or_null(isset($extra['nb_unchanged']) ? $extra['nb_unchanged'] : null)
               . ", " . ak_sync_sql_str_or_null($conn, isset($extra['message']) ? $extra['message'] : null, 255)
               . ", " . ak_sync_sql_str_or_null($conn, isset($extra['app_version']) ? $extra['app_version'] : null, 20)
               . ", " . ak_sync_sql_str_or_null($conn, isset($extra['ip']) ? $extra['ip'] : null, 45);
    }
    $req = "INSERT INTO DATA_SAVING_LOGS (" . $cols . ") VALUES (" . $vals . ")";
    $ok = $conn->Execute($req);
    return ($ok !== false);
}

/**
 * Extrait le marqueur MAJ_STATS={"i":..,"u":..,"d":..,"r":..} emis par questionnaire_ws.php (U08).
 * @return array|null
 */
function ak_sync_parse_maj_stats($response) {
    if (!is_string($response)) return null;
    if (preg_match('/MAJ_STATS=(\{[^}]*\})/', $response, $m)) {
        $d = json_decode($m[1], true);
        if (is_array($d)) {
            return array(
                'nb_insert'    => isset($d['i']) ? (int)$d['i'] : 0,
                'nb_update'    => isset($d['u']) ? (int)$d['u'] : 0,
                'nb_delete'    => isset($d['d']) ? (int)$d['d'] : 0,
                'nb_unchanged' => isset($d['r']) ? (int)$d['r'] : 0,
                'no_delete_guard' => isset($d['g']) ? (int)$d['g'] : 0,
            );
        }
    }
    return null;
}

/* -------------------------------------------------------------------------
 * Idempotence (U11 / U22)
 * ---------------------------------------------------------------------- */

/**
 * Cherche une soumission OKSAVE recente identique (par SYNC_ID d'abord, puis par SYNC_HASH).
 * @return array|null  ligne DATA_SAVING_LOGS trouvee (cles UPPER) ou null
 */
function ak_sync_find_duplicate($sync_id, $sync_hash, $id_camp, $id_theme, $id_etab, $id_annee) {
    $conn = $GLOBALS['conn_dico'];
    if (!ak_sync_logs_extended($conn)) return null;
    $window = isset($GLOBALS['PARAM']['MOBILE_DUPLICATE_WINDOW_MIN']) ? (int)$GLOBALS['PARAM']['MOBILE_DUPLICATE_WINDOW_MIN'] : 30;

    // 1) Par SYNC_ID : toujours actif (un meme UUID = meme tentative logique, quel que soit le delai)
    if ($sync_id !== '' && ak_sync_is_uuid($sync_id)) {
        $req = "SELECT * FROM DATA_SAVING_LOGS WHERE SYNC_ID=" . $conn->qstr($sync_id) . " AND STATUT_OPERATION='OKSAVE'";
        $row = $conn->GetRow($req);
        if (is_array($row) && count($row) > 0) return array_change_key_case($row, CASE_UPPER);
    }
    // 2) Par hash : uniquement dans la fenetre configuree
    if ($window > 0 && $sync_hash !== '') {
        $req = "SELECT * FROM DATA_SAVING_LOGS WHERE SYNC_HASH=" . $conn->qstr($sync_hash) .
               " AND CODE_CAMPAGNE=" . (int)$id_camp . " AND ID_THEME_SYSTEME=" . (int)$id_theme .
               " AND CODE_ECOLE=" . (int)$id_etab . " AND CODE_ANNEE=" . (int)$id_annee .
               " AND STATUT_OPERATION='OKSAVE'";
        $rows = $conn->GetAll($req);
        if (is_array($rows)) {
            $limit = time() - $window * 60;
            foreach ($rows as $r) {
                $r = array_change_key_case($r, CASE_UPPER);
                $ts = ak_sync_parse_log_date(isset($r['LOG_DATE_TIME']) ? $r['LOG_DATE_TIME'] : '');
                if ($ts !== false && $ts >= $limit) return $r;
            }
        }
    }
    return null;
}

/** Convertit 'Y/m/d H:i:s' (ou tout format strtotime) en timestamp ; false si illisible. */
function ak_sync_parse_log_date($s) {
    $s = trim((string)$s);
    if ($s === '') return false;
    $d = DateTime::createFromFormat('Y/m/d H:i:s', $s);
    if ($d instanceof DateTime) return $d->getTimestamp();
    $t = strtotime($s);
    return ($t === false) ? false : $t;
}

/* -------------------------------------------------------------------------
 * Statut administratif SYNC_ETAB_STATUT (U18 / U20)
 * ---------------------------------------------------------------------- */

/**
 * Statut actif le plus specifique pour une cle de soumission.
 * Priorite : (theme+filtre) > (theme) > (etab tous themes).
 * @return array|null  ['STATUT'=>..., 'COMMENTAIRE'=>..., 'DATE_STATUT'=>..., 'CODE_USER_ADMIN'=>..., 'ID_STATUT'=>...]
 */
function ak_sync_get_statut($id_annee, $id_camp, $id_etab, $id_theme = null, $id_filter = null) {
    $conn = $GLOBALS['conn_dico'];
    if (!ak_sync_table_exists($conn, 'SYNC_ETAB_STATUT')) return null;
    $f = ak_sync_norm_filter($id_filter);
    $req = "SELECT * FROM SYNC_ETAB_STATUT WHERE ACTIF=1 AND CODE_ANNEE=" . (int)$id_annee .
           " AND ID_CAMPAGNE=" . (int)$id_camp . " AND CODE_ETABLISSEMENT=" . (int)$id_etab .
           " AND (ID_THEME_SYSTEME IS NULL" . ($id_theme !== null ? " OR ID_THEME_SYSTEME=" . (int)$id_theme : "") . ")" .
           " AND (CODE_FILTRE IS NULL" . ($f !== null ? " OR CODE_FILTRE=" . (int)$f : "") . ")";
    $rows = $conn->GetAll($req);
    if (!is_array($rows) || count($rows) == 0) return null;
    $best = null; $best_score = -1;
    foreach ($rows as $r) {
        $r = array_change_key_case($r, CASE_UPPER);
        $score = 0;
        if ($r['ID_THEME_SYSTEME'] !== null && $r['ID_THEME_SYSTEME'] !== '') $score += 2;
        if ($r['CODE_FILTRE'] !== null && $r['CODE_FILTRE'] !== '') $score += 1;
        if ($score > $best_score) { $best = $r; $best_score = $score; }
    }
    return $best;
}

/** true si la soumission est bloquee (VALIDE ou VERROUILLE) et que l'application du verrou est activee. */
function ak_sync_is_locked($id_annee, $id_camp, $id_etab, $id_theme, $id_filter, &$statut_row = null) {
    if (isset($GLOBALS['PARAM']['MOBILE_ENFORCE_LOCK']) && !$GLOBALS['PARAM']['MOBILE_ENFORCE_LOCK']) return false;
    $statut_row = ak_sync_get_statut($id_annee, $id_camp, $id_etab, $id_theme, $id_filter);
    if ($statut_row === null) return false;
    return in_array($statut_row['STATUT'], array(AK_SYNC_ST_VALIDE, AK_SYNC_ST_VERROUILLE));
}

/**
 * Positionne un statut (desactive l'ancien statut actif de meme cle, insere le nouveau, historise).
 * @return bool
 */
function ak_sync_set_statut($id_annee, $id_camp, $id_etab, $id_theme, $id_filter, $statut, $commentaire, $admin_login, $id_sector = null) {
    $conn = $GLOBALS['conn_dico'];
    if (!ak_sync_table_exists($conn, 'SYNC_ETAB_STATUT')) return false;
    $statut = strtoupper(trim($statut));
    if (!in_array($statut, array(AK_SYNC_ST_RECU, AK_SYNC_ST_A_CORRIGER, AK_SYNC_ST_VALIDE, AK_SYNC_ST_VERROUILLE))) return false;
    $f = ak_sync_norm_filter($id_filter);
    $t = ($id_theme === null || $id_theme === '' || (int)$id_theme === 0) ? null : (int)$id_theme;
    $now = ak_sync_now();

    $where = " CODE_ANNEE=" . (int)$id_annee . " AND ID_CAMPAGNE=" . (int)$id_camp . " AND CODE_ETABLISSEMENT=" . (int)$id_etab .
             " AND " . ($t === null ? "ID_THEME_SYSTEME IS NULL" : "ID_THEME_SYSTEME=" . $t) .
             " AND " . ($f === null ? "CODE_FILTRE IS NULL" : "CODE_FILTRE=" . $f) . " AND ACTIF=1";
    $old = $conn->GetRow("SELECT STATUT FROM SYNC_ETAB_STATUT WHERE" . $where);
    $old_statut = (is_array($old) && count($old)) ? array_change_key_case($old, CASE_UPPER)['STATUT'] : null;

    $conn->Execute("UPDATE SYNC_ETAB_STATUT SET ACTIF=0 WHERE" . $where);
    $req = "INSERT INTO SYNC_ETAB_STATUT (CODE_ANNEE, ID_CAMPAGNE, CODE_SECTEUR, CODE_ETABLISSEMENT, ID_THEME_SYSTEME, CODE_FILTRE, STATUT, COMMENTAIRE, CODE_USER_ADMIN, DATE_STATUT, ACTIF) VALUES (" .
           (int)$id_annee . "," . (int)$id_camp . "," . ak_sync_sql_int_or_null($id_sector) . "," . (int)$id_etab . "," .
           ak_sync_sql_int_or_null($t) . "," . ak_sync_sql_int_or_null($f) . ",'" . $statut . "'," .
           ak_sync_sql_str_or_null($conn, $commentaire, 1000) . "," . ak_sync_sql_str_or_null($conn, $admin_login, 100) . ",'" . $now . "',1)";
    $ok = $conn->Execute($req);
    if ($ok === false) return false;

    if (ak_sync_table_exists($conn, 'SYNC_ETAB_STATUT_HIST')) {
        $conn->Execute("INSERT INTO SYNC_ETAB_STATUT_HIST (CODE_ANNEE, ID_CAMPAGNE, CODE_ETABLISSEMENT, ID_THEME_SYSTEME, CODE_FILTRE, ANCIEN_STATUT, NOUVEAU_STATUT, COMMENTAIRE, CODE_USER_ADMIN, DATE_HIST) VALUES (" .
            (int)$id_annee . "," . (int)$id_camp . "," . (int)$id_etab . "," . ak_sync_sql_int_or_null($t) . "," . ak_sync_sql_int_or_null($f) . "," .
            ak_sync_sql_str_or_null($conn, $old_statut, 20) . ",'" . $statut . "'," . ak_sync_sql_str_or_null($conn, $commentaire, 1000) . "," .
            ak_sync_sql_str_or_null($conn, $admin_login, 100) . ",'" . $now . "')");
    }
    return true;
}

/**
 * Apres un OKSAVE mobile : si un statut A_CORRIGER (theme precis ou etab entier) existe,
 * le passe automatiquement a RECU (U20). Ne touche jamais VALIDE / VERROUILLE.
 */
function ak_sync_auto_recu_after_save($id_annee, $id_camp, $id_etab, $id_theme, $id_filter, $agent_login) {
    $row = ak_sync_get_statut($id_annee, $id_camp, $id_etab, $id_theme, $id_filter);
    if ($row === null || $row['STATUT'] !== AK_SYNC_ST_A_CORRIGER) return false;
    return ak_sync_set_statut($id_annee, $id_camp, $id_etab, $row['ID_THEME_SYSTEME'], $row['CODE_FILTRE'],
        AK_SYNC_ST_RECU, 'Renvoi mobile par ' . $agent_login . ' (auto)', 'system');
}

/* -------------------------------------------------------------------------
 * Synthese par etablissement (U15)
 * ---------------------------------------------------------------------- */

/**
 * Liste des themes attendus d'une campagne/secteur : source de verite = theme_manager.
 * @return array  [ ['id'=>ID_THEME_SYSTEME, 'title'=>LIBELLE], ... ] tries par ordre de saisie
 */
function ak_sync_expected_themes($id_camp, $id_sector) {
    static $cache = array();
    $k = $id_camp . '#' . $id_sector;
    if (isset($cache[$k])) return $cache[$k];
    $out = array();
    if (!class_exists('theme_manager')) {
        require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/theme_manager.class.php';
    }
    try {
        $tm = new theme_manager($id_camp);
        $tm->charger_theme($id_camp, $id_sector);
        if (is_array($tm->list)) {
            foreach ($tm->list as $th) {
                $out[] = array('id' => (int)$th['ID_THEME_SYSTEME'], 'title' => isset($th['LIBELLE']) ? $th['LIBELLE'] : (string)$th['ID_THEME_SYSTEME']);
            }
        }
    } catch (Exception $e) {
        $out = array();
    }
    // Fallback : requete historique get_camp_themes (fonctions.inc.php)
    if (count($out) == 0 && function_exists('get_camp_themes')) {
        $lang = isset($_SESSION['langue']) ? $_SESSION['langue'] : 'fr';
        $tmp = get_camp_themes($id_sector, $id_camp, $lang);
        if (is_array($tmp)) foreach ($tmp as $t) $out[] = array('id' => (int)$t['id'], 'title' => $t['title']);
    }
    $cache[$k] = $out;
    return $out;
}

/**
 * Dernier evenement de journal par (etab, theme) pour une campagne/annee[/secteur][/periode].
 * @param  int|null $id_period  null = toutes periodes (les lignes CODE_PERIODE NULL sont TOUJOURS incluses — U27)
 * @return array  [code_etab][id_theme] => ['statut'=>..., 'date'=>..., 'user'=>..., 'nb'=>total_lignes, 'nb_ok'=>..., 'nb_ko'=>..., 'last_ok_date'=>...]
 */
function ak_sync_last_events($id_camp, $id_annee, $id_sector = null, $id_period = null, $etab_ids = null) {
    $conn = $GLOBALS['conn_dico'];
    $ext = ak_sync_logs_extended($conn);
    $req = "SELECT CODE_ECOLE, ID_THEME_SYSTEME, CODE_USER, LOG_DATE_TIME, STATUT_OPERATION, CODE_PERIODE" .
           ($ext ? ", NB_INSERT, NB_UPDATE, NB_DELETE, NB_UNCHANGED, MESSAGE_ERREUR" : "") .
           " FROM DATA_SAVING_LOGS WHERE CODE_CAMPAGNE=" . (int)$id_camp . " AND CODE_ANNEE=" . (int)$id_annee;
    if ($id_sector !== null && $id_sector !== '') $req .= " AND CODE_SECTEUR=" . (int)$id_sector;
    if ($id_period !== null && $id_period !== '') $req .= " AND (CODE_PERIODE=" . (int)$id_period . " OR CODE_PERIODE IS NULL)";
    if (is_array($etab_ids) && count($etab_ids) > 0) {
        $ids = array(); foreach ($etab_ids as $e) $ids[] = (int)$e;
        $req .= " AND CODE_ECOLE IN (" . implode(',', $ids) . ")";
    }
    $rows = $conn->GetAll($req);
    $out = array();
    if (!is_array($rows)) return $out;
    foreach ($rows as $r) {
        $r = array_change_key_case($r, CASE_UPPER);
        $e = (int)$r['CODE_ECOLE']; $t = (int)$r['ID_THEME_SYSTEME'];
        $ts = ak_sync_parse_log_date($r['LOG_DATE_TIME']);
        if ($ts === false) $ts = 0;
        if (!isset($out[$e][$t])) {
            $out[$e][$t] = array('statut' => null, 'date' => null, 'ts' => -1, 'user' => null, 'nb' => 0, 'nb_ok' => 0, 'nb_ko' => 0,
                                 'last_ok_date' => null, 'last_ok_ts' => -1, 'nb_rows' => null, 'message' => null);
        }
        $c = &$out[$e][$t];
        $c['nb']++;
        $st = $r['STATUT_OPERATION'];
        if ($st === 'OKSAVE' || $st === AK_SYNC_OP_DUPLICATE) {
            $c['nb_ok']++;
            if ($ts > $c['last_ok_ts']) { $c['last_ok_ts'] = $ts; $c['last_ok_date'] = $r['LOG_DATE_TIME']; }
        } else {
            $c['nb_ko']++;
        }
        if ($ts > $c['ts']) {
            $c['ts'] = $ts; $c['date'] = $r['LOG_DATE_TIME']; $c['statut'] = $st; $c['user'] = $r['CODE_USER'];
            $c['message'] = $ext && isset($r['MESSAGE_ERREUR']) ? $r['MESSAGE_ERREUR'] : null;
            if ($ext && isset($r['NB_INSERT']) && $r['NB_INSERT'] !== null) {
                $c['nb_rows'] = (int)$r['NB_INSERT'] + (int)$r['NB_UPDATE'] + (int)$r['NB_UNCHANGED'];
            }
        }
        unset($c);
    }
    return $out;
}

/**
 * Synthese par etablissement : COMPLET / PARTIEL / ERREUR / VIDE / AUCUN (U15).
 *
 * @param array $etabs  liste [ ['id'=>code_etab, 'nom'=>..., 'code'=>...], ... ]
 * @return array  par etab : ['id','nom','code','statut','nb_attendus','nb_ok','nb_ko','nb_manquants',
 *                            'derniere_reception','themes'=>[id_theme=>['statut','date','user','nb_rows','message','libelle']],
 *                            'admin'=>statut SYNC_ETAB_STATUT etab (ou null)]
 */
function ak_sync_etab_summary($id_camp, $id_annee, $id_sector, $etabs, $id_period = null) {
    $themes = ak_sync_expected_themes($id_camp, $id_sector);
    $ids = array(); foreach ($etabs as $e) $ids[] = (int)$e['id'];
    $events = ak_sync_last_events($id_camp, $id_annee, $id_sector, $id_period, $ids);
    $out = array();
    foreach ($etabs as $e) {
        $ce = (int)$e['id'];
        $row = array('id' => $ce, 'nom' => isset($e['nom']) ? $e['nom'] : '', 'code' => isset($e['code']) ? $e['code'] : '',
                     'nb_attendus' => count($themes), 'nb_ok' => 0, 'nb_ko' => 0, 'nb_vide' => 0, 'nb_manquants' => 0,
                     'derniere_reception' => null, 'themes' => array(), 'admin' => null);
        $last_ts = -1;
        foreach ($themes as $th) {
            $t = $th['id'];
            $ev = isset($events[$ce][$t]) ? $events[$ce][$t] : null;
            $cell = array('libelle' => $th['title'], 'statut' => 'AUCUN', 'date' => null, 'user' => null, 'nb_rows' => null, 'message' => null, 'nb_tentatives' => 0);
            if ($ev !== null) {
                $cell['nb_tentatives'] = $ev['nb'];
                $cell['date'] = $ev['date']; $cell['user'] = $ev['user']; $cell['nb_rows'] = $ev['nb_rows']; $cell['message'] = $ev['message'];
                if ($ev['last_ok_ts'] >= 0 && ($ev['statut'] === 'OKSAVE' || $ev['statut'] === AK_SYNC_OP_DUPLICATE || $ev['last_ok_ts'] >= $ev['ts'])) {
                    $cell['statut'] = ($ev['nb_rows'] !== null && $ev['nb_rows'] === 0) ? 'VIDE' : 'OK';
                    $cell['date'] = $ev['last_ok_date'];
                    if ($cell['statut'] === 'VIDE') $row['nb_vide']++; else $row['nb_ok']++;
                } else {
                    $cell['statut'] = 'ERREUR';
                    $row['nb_ko']++;
                }
                if ($ev['ts'] > $last_ts) { $last_ts = $ev['ts']; $row['derniere_reception'] = $ev['date']; }
            } else {
                $row['nb_manquants']++;
            }
            $row['themes'][$t] = $cell;
        }
        if ($row['nb_attendus'] == 0)                          $row['statut'] = 'AUCUN';
        elseif ($row['nb_ok'] == $row['nb_attendus'])           $row['statut'] = 'COMPLET';
        elseif ($row['nb_ko'] > 0)                              $row['statut'] = 'ERREUR';
        elseif ($row['nb_ok'] + $row['nb_vide'] == 0)           $row['statut'] = 'AUCUN';
        else                                                    $row['statut'] = 'PARTIEL';
        $adm = ak_sync_get_statut($id_annee, $id_camp, $ce);
        if ($adm !== null) $row['admin'] = array('statut' => $adm['STATUT'], 'commentaire' => $adm['COMMENTAIRE'], 'date' => $adm['DATE_STATUT'], 'admin' => $adm['CODE_USER_ADMIN']);
        $out[] = $row;
    }
    return $out;
}

/**
 * Liste des etablissements accessibles a un agent (login) pour une campagne/annee.
 * Reprend la logique DICO_FIXE_REGROUPEMENT de user_camp.php::etabs_camp ; fallback :
 * etablissements pour lesquels l'agent a deja journalise un envoi (agents sans DFR).
 * @return array [ ['id'=>..., 'nom'=>..., 'code'=>...], ... ]
 */
function ak_sync_agent_etabs($login, $id_camp, $id_annee, $id_sector) {
    $conn_dico = $GLOBALS['conn_dico'];
    $conn = $GLOBALS['conn'];
    $out = array(); $seen = array();
    $urow = $conn_dico->GetRow("SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER=" . $conn_dico->qstr($login));
    $code_user = (is_array($urow) && count($urow)) ? (int)array_change_key_case($urow, CASE_UPPER)['CODE_USER'] : 0;

    $nom_col  = $GLOBALS['PARAM']['NOM_ETABLISSEMENT'];
    $code_col = $GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
    $adm_col  = $GLOBALS['PARAM']['CODE_ADMINISTRATIF'];
    $tab_e    = $GLOBALS['PARAM']['ETABLISSEMENT'];
    $with_code = ($adm_col !== '' && exist_champ_in_table($adm_col, $tab_e)) ? ", E." . $adm_col . " AS code" : ", '' AS code";

    if ($code_user > 0) {
        $regroups = $conn_dico->GetAll("SELECT ID_CHAINE, ID_SYSTEME, ID_TYPE_REGROUP, ID_REGROUP FROM DICO_FIXE_REGROUPEMENT WHERE ID_USER=" . $code_user .
                                       " AND ID_CAMPAGNE=" . (int)$id_camp . " AND ID_ANNEE=" . (int)$id_annee);
        if (is_array($regroups)) {
            foreach ($regroups as $row) {
                $row = array_change_key_case($row, CASE_UPPER);
                if ((int)$row['ID_SYSTEME'] !== (int)$id_sector) continue;
                $regs = preg_split("/,/", $row['ID_REGROUP']);
                if ((int)$row['ID_TYPE_REGROUP'] == 0) {
                    foreach ($regs as $etab_id) {
                        if (trim($etab_id) === '') continue;
                        $r = $conn->GetRow("SELECT E." . $code_col . " AS id, E." . $nom_col . " AS nom" . $with_code . " FROM " . $tab_e . " AS E WHERE E." . $code_col . "=" . (int)$etab_id);
                        if (is_array($r) && count($r)) {
                            $r = array_change_key_case($r, CASE_LOWER);
                            if (!isset($seen[(int)$r['id']])) { $seen[(int)$r['id']] = 1; $out[] = array('id' => (int)$r['id'], 'nom' => $r['nom'], 'code' => $r['code']); }
                        }
                    }
                } else {
                    $arbre = new arbre($row['ID_CHAINE']);
                    foreach ($regs as $reg) {
                        if (trim($reg) === '') continue;
                        $niv = $arbre->get_depht_regroup($reg);
                        $etabs = $arbre->get_list_etabs($niv, $reg, $row['ID_SYSTEME'], $row['ID_CHAINE']);
                        if (!is_array($etabs)) continue;
                        foreach ($etabs as $et) {
                            $et = array_change_key_case($et, CASE_LOWER);
                            $eid = (int)$et['id'];
                            if (!isset($seen[$eid])) { $seen[$eid] = 1; $out[] = array('id' => $eid, 'nom' => $et['nom'], 'code' => isset($et['code']) ? $et['code'] : ''); }
                        }
                    }
                }
            }
        }
    }
    // Fallback / complement : etablissements deja journalises par cet agent
    $logged = $conn_dico->GetAll("SELECT DISTINCT CODE_ECOLE FROM DATA_SAVING_LOGS WHERE CODE_USER=" . $conn_dico->qstr($login) .
                                 " AND CODE_CAMPAGNE=" . (int)$id_camp . " AND CODE_ANNEE=" . (int)$id_annee);
    if (is_array($logged)) {
        foreach ($logged as $l) {
            $eid = (int)array_change_key_case($l, CASE_UPPER)['CODE_ECOLE'];
            if (isset($seen[$eid])) continue;
            $r = $conn->GetRow("SELECT E." . $code_col . " AS id, E." . $nom_col . " AS nom" . $with_code . " FROM " . $tab_e . " AS E WHERE E." . $code_col . "=" . $eid);
            if (is_array($r) && count($r)) {
                $r = array_change_key_case($r, CASE_LOWER);
                $seen[$eid] = 1; $out[] = array('id' => $eid, 'nom' => $r['nom'], 'code' => $r['code']);
            }
        }
    }
    return $out;
}

/** Encode recursivement en UTF-8 les chaines ISO-8859-1 (base SISED) pour json_encode. */
function ak_sync_utf8($v) {
    if (is_array($v)) { $o = array(); foreach ($v as $k => $x) $o[$k] = ak_sync_utf8($x); return $o; }
    if (is_string($v)) {
        if (function_exists('mb_check_encoding') && mb_check_encoding($v, 'UTF-8')) return $v;
        return utf8_encode($v);
    }
    return $v;
}

} // AK_SYNC_LIB_LOADED
?>
