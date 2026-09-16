<?php //Variables sur le serveur
//$SISED_SERVER       = 'http://' . $_SERVER['HTTP_HOST']; // Sans / à la fin

// Détection HTTPS derrière reverse proxy Nginx
if (
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) 
    && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    ||
    (isset($_SERVER['HTTPS']) 
    && $_SERVER['HTTPS'] == 'on')
) {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;
    $SISED_PROTOCOL = 'https://';
}
else {
    $SISED_PROTOCOL = 'http://';
}

$SISED_SERVER = $SISED_PROTOCOL . $_SERVER['HTTP_HOST']; // Sans / à la fin

$SISED_PATH         = preg_replace('`\\\`', '/', dirname(__FILE__)) . '/'; // Chemin absolu pour accéder à l'application
$SISED_URL          = str_replace(preg_replace('`\\\`', '/', $_SERVER['DOCUMENT_ROOT']), '', $SISED_PATH); // URL relative pour accéder à l'application
$SISED_AURL         = $SISED_SERVER . $SISED_URL; // URL absolue pour accéder à l'application
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
    // AK-FIX-PORT : Correction bug 404 curl interne sur ports non-standard (ex: 8083).
    //
    // ANCIEN COMPORTEMENT (bug) :
    //   $ports_http_only = [80, 8080, 8000, 8888] -> SERVER_PORT 8083 non present
    //   -> curl interne vers 127.0.0.1:80 -> 404 -> se_data='404 : HTTP/1.1 404 Not Found'
    //
    // NOUVEAU COMPORTEMENT :
    //   Priorite 1 : SERVER_PORT si HTTP (pas SSL : != 443, != 8443)
    //     Valide pour TOUT port HTTP : 80, 8080, 8000, 8083, 8888, 9090...
    //     Un fsockopen verifie que le port repond vraiment en local.
    //   Priorite 2 : sonder les ports HTTP standard en fallback
    //   Priorite 3 : fallback 80 si rien ne repond
    //
    // SECURITE SSL-51 preservee : ports SSL/proxy (443, 8443) toujours exclus.
    $ssl_ports = array(443, 8443);   // ports SSL/proxy a exclure
    $fallback_ports = array(80, 8080, 8000, 8888);  // ports HTTP standard a sonder

    // Priorite 1 : SERVER_PORT si ce n'est pas un port SSL
    if (isset($_SERVER['SERVER_PORT'])) {
        $p = (int)$_SERVER['SERVER_PORT'];
        if ($p > 0 && !in_array($p, $ssl_ports)) {
            $en = 0; $es = ''; $s = @fsockopen('127.0.0.1', $p, $en, $es, 2);
            if ($s !== false) { fclose($s); return $p; }
        }
    }

    // Priorite 2 : sonder les ports HTTP standard (jamais les ports SSL)
    foreach ($fallback_ports as $p) {
        if (in_array($p, $ssl_ports)) continue;
        $en = 0; $es = ''; $s = @fsockopen('127.0.0.1', $p, $en, $es, 2);
        if ($s !== false) { fclose($s); return $p; }
    }

    // Fallback absolu : 80
    return 80;
}
$_sised_local_port  = _sised_local_port();
// Session 47: scheme TOUJOURS http pour curl interne (127.0.0.1 != certificat SSL)
// Meme si Apache tourne sur 443 (cas rare), on force http pour eviter SSL-51.
$_sised_local_scheme = 'http';
// Port omis seulement pour 80 (HTTP standard) - 443 exclu par _sised_local_port()
$_sised_local_ps    = ($_sised_local_port != 80) ? ':' . $_sised_local_port : '';
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