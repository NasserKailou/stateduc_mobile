<?php //Variables sur le serveur
$SISED_SERVER       = 'http://' . $_SERVER['HTTP_HOST']; // Sans / à la fin
$SISED_PATH         = preg_replace('`\\\`', '/', dirname(__FILE__)) . '/'; // Chemin absolu pour accéder à l'application
$SISED_URL          = str_replace(preg_replace('`\\\`', '/', $_SERVER['DOCUMENT_ROOT']), '', $SISED_PATH); // URL relative pour accéder à l'application
$SISED_AURL         = $SISED_SERVER . $SISED_URL; // URL absolue pour accéder à l'application
// SISED_AURL_INTERNAL : URL pour les appels curl internes (data_save.php -> questionnaire_ws.php).
//
// HISTORIQUE :
//   Session 41  : SISED_AURL (hostname DNS) -> curl 6 Could not resolve host
//   Session 41b : 127.0.0.1:9191 (port proxy) -> curl 7 Connection refused
//   Session 41c : SERVER_ADDR:SERVER_PORT -> theoriquement correct
//
// SOLUTION DEFINITIVE (Session 42) - Auto-detection par sondage TCP :
//   La fonction _sised_probe_internal_url() teste fsockopen() sur plusieurs
//   candidats (SERVER_PORT, 80, 8080) et retient le premier qui repond.
//   Resultat mis en cache (APCu ou fichier tmp, 1h) => 0 overhead en prod.
//   Fonctionne sur : XAMPP, Apache seul, Apache+Tomcat, reverse proxy, VirtualHost.
function _sised_probe_internal_url($sised_url) {
    $cache_key = 'sised_int_url_' . md5($sised_url);
    // Lecture cache APCu
    if (function_exists('apcu_fetch')) {
        $cached = apcu_fetch($cache_key, $ok);
        if ($ok && !empty($cached)) return $cached;
    }
    // Lecture cache fichier (1h)
    $tmp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $cache_key . '.txt';
    if (file_exists($tmp_file) && (time() - filemtime($tmp_file)) < 3600) {
        $cached = trim(file_get_contents($tmp_file));
        if (!empty($cached)) return $cached;
    }
    // Candidats ip:port a sonder (ordre de priorite)
    $srv_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
    $srv_port = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
    // IPv6 loopback ::1 -> 127.0.0.1 pour la compatibilite curl
    if ($srv_addr === '::1' || $srv_addr === '0:0:0:0:0:0:0:1') { $srv_addr = '127.0.0.1'; }
    $candidates = array(
        array($srv_addr,   $srv_port), // priorite 1 : port Apache reel
        array('127.0.0.1', 80),        // priorite 2 : Apache standard
        array('127.0.0.1', 8080),      // priorite 3 : Tomcat / alt
        array($srv_addr,   80),
        array($srv_addr,   8080),
    );
    // Deduplication
    $seen = array(); $uniq = array();
    foreach ($candidates as $c) {
        $k = $c[0].':'.$c[1];
        if (!isset($seen[$k])) { $seen[$k]=1; $uniq[]=$c; }
    }
    $chosen = null;
    foreach ($uniq as $c) {
        $ip = $c[0]; $p = $c[1];
        $en = 0; $es = '';
        $sock = @fsockopen($ip, $p, $en, $es, 3);
        if ($sock !== false) {
            fclose($sock);
            $sch = ($p === 443) ? 'https' : 'http';
            $ps  = (!in_array($p, array(80,443))) ? ':'.$p : '';
            $chosen = $sch.'://'.$ip.$ps.$sised_url;
            break;
        }
    }
    // Fallback sans sondage si aucun port ne repond
    if ($chosen === null) {
        $sch = ($srv_port === 443) ? 'https' : 'http';
        $ps  = (!in_array($srv_port, array(80,443))) ? ':'.$srv_port : '';
        $chosen = $sch.'://'.$srv_addr.$ps.$sised_url;
    }
    // Ecriture cache
    if (function_exists('apcu_store')) { apcu_store($cache_key, $chosen, 3600); }
    @file_put_contents($tmp_file, $chosen);
    return $chosen;
}
$SISED_AURL_INTERNAL = _sised_probe_internal_url($SISED_URL);

$SISED_PATH_INC     = $SISED_PATH . 'server-side/include/';
$SISED_PATH_CLS     = $SISED_PATH . 'server-side/classes/';
$SISED_PATH_LIB     = $SISED_PATH . 'server-side/lib/';
$SISED_PATH_INS     = $SISED_PATH . 'server-side/instances/';
$SISED_PATH_TPL     = $SISED_PATH . 'server-side/templates/';
$SISED_PATH_DB      = $SISED_PATH . 'server-side/db/';
$SISED_PATH_ADC     = $SISED_PATH . 'server-side/adodbcache/';

$SISED_URL_JSC      = 'client-side/js/';
$SISED_PATH_JSC      = $SISED_PATH . 'client-side/js/';
$SISED_URL_CSS      = 'client-side/css/';
$SISED_PATH_CSS      = $SISED_PATH . 'client-side/css/';
$SISED_URL_IMG      = 'client-side/image/';
$SISED_SHARED_PATH_LOCAL     = $SISED_PATH .'/server-side/include/olap_tools/cubes/';

if($_SERVER['SERVER_NAME']=='localhost')
    $SISED_SHARED_PATH_LAN       = $SISED_SHARED_PATH_LOCAL;
else
    $SISED_SHARED_PATH_LAN       ='\\\\'.$_SERVER['SERVER_NAME'].'\cubes\\'; //Ou : '\\\172.24.9.195\cubes\\';  

//$SISED_SHARED_PATH_LAN       = $SISED_SHARED_PATH_LOCAL;
//$SISED_SHARED_PATH_LAN       ='\\\code\cubes\\'; //Ou : '\\\172.24.9.195\cubes\\';  
$SISED_OLAP_SERVER_SETUP = false;
?>