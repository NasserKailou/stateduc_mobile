<?php //Variables sur le serveur
$SISED_SERVER       = 'http://' . $_SERVER['HTTP_HOST']; // Sans / � la fin
$SISED_PATH         = preg_replace('`\\\`', '/', dirname(__FILE__)) . '/'; // Chemin absolu pour acc�der � l'application
$SISED_URL          = str_replace(preg_replace('`\\\`', '/', $_SERVER['DOCUMENT_ROOT']), '', $SISED_PATH); // URL relative pour acc�der � l'application
$SISED_AURL         = $SISED_SERVER . $SISED_URL; // URL absolue pour acc�der � l'application
// SISED_AURL_INTERNAL : URL pour les appels curl internes (data_save.php -> questionnaire_ws.php).
//
// TOPOLOGIE PRODUCTION MEN :
//   Internet -> Fortinet port 9191 (NAT) -> VM Apache port local (80 ou 8080)
//   Le port 9191 N'EXISTE PAS sur la VM -> curl vers *:9191 echoue toujours.
//
// SOLUTION DEFINITIVE (Session 44) :
//   1. Curl vers http://127.0.0.1:PORT_APACHE_LOCAL/stateduc/questionnaire_ws.php
//      PORT_APACHE_LOCAL = SERVER_PORT (port reel Apache, ex: 80 ou 8080)
//      Fallback si SERVER_PORT non disponible : sonder 80 puis 8080
//   2. Passer header 'Host: HTTP_HOST' (ex: stateduc.ins.ne:9191) pour que
//      le VirtualHost Apache route vers la bonne application.
//   => Bypass total du Fortinet/NAT, fonctionne quel que soit le nom de domaine.
//
// $SISED_AURL_INTERNAL  = URL interne (127.0.0.1:port_local)
// $SISED_HOST_HEADER    = valeur du Host header a passer dans curl
function _sised_local_port() {
    // Priorite 1 : SERVER_PORT = port Apache reel (fiable si pas de proxy AJP)
    if (isset($_SERVER['SERVER_PORT'])) {
        $p = (int)$_SERVER['SERVER_PORT'];
        // Ignorer si c'est le port proxy externe (ex: 9191, 8443, 443 sur proxy)
        // SERVER_PORT est fiable sur Apache direct ; sur proxy inverse il peut
        // valoir le port frontal. On le valide par fsockopen.
        $en = 0; $es = ''; $s = @fsockopen('127.0.0.1', $p, $en, $es, 2);
        if ($s !== false) { fclose($s); return $p; }
    }
    // Priorite 2 : sonder les ports Apache standard
    // Note : on sonde 443 en dernier et on preferera toujours HTTP en interne
    foreach (array(80, 8080, 8000, 8888, 443) as $p) {
        $en = 0; $es = ''; $s = @fsockopen('127.0.0.1', $p, $en, $es, 2);
        if ($s !== false) { fclose($s); return $p; }
    }
    // Fallback absolu : 80
    return 80;
}
$_sised_local_port  = _sised_local_port();
// CORRECTION SSL-51 (Session 45) :
// Les appels curl internes vers 127.0.0.1 utilisent TOUJOURS http://, meme si Apache
// ecoute sur le port 443 (HTTPS). Raison : le certificat TLS est emis pour
// stateduc.mineduc.gov.bi et ne couvre pas 127.0.0.1 -> erreur BoringSSL/cURL 51
// "no alternative certificate subject name matches target host name '127.0.0.1'".
// Le trafic interne 127.0.0.1 n'a pas besoin de chiffrement (meme machine).
// Si Apache n'ecoute QUE sur 443, on tente le port 80 comme fallback HTTP explicite.
$_sised_internal_port = $_sised_local_port;
if ($_sised_internal_port === 443) {
    // Chercher un port HTTP alternatif si Apache ecoute aussi en HTTP
    $en = 0; $es = '';
    foreach (array(80, 8080, 8000, 8888) as $_p_http) {
        $s = @fsockopen('127.0.0.1', $_p_http, $en, $es, 2);
        if ($s !== false) { fclose($s); $_sised_internal_port = $_p_http; break; }
    }
}
// Scheme TOUJOURS http pour les appels internes 127.0.0.1 (bypass SSL 51)
$_sised_local_scheme = 'http';
$_sised_local_ps    = (!in_array($_sised_internal_port, array(80, 443))) ? ':' . $_sised_internal_port : '';
$SISED_AURL_INTERNAL = $_sised_local_scheme . '://127.0.0.1' . $_sised_local_ps . $SISED_URL;
// Host header = HTTP_HOST (ex: stateduc.ins.ne:9191) pour que Apache route correctement
$SISED_HOST_HEADER   = $_SERVER['HTTP_HOST'];

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