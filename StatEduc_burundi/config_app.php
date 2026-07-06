<?php //Variables sur le serveur
$SISED_SERVER       = 'http://' . $_SERVER['HTTP_HOST']; // Sans / à la fin
$SISED_PATH         = preg_replace('`\\\`', '/', dirname(__FILE__)) . '/'; // Chemin absolu pour accéder à l'application
$SISED_URL          = str_replace(preg_replace('`\\\`', '/', $_SERVER['DOCUMENT_ROOT']), '', $SISED_PATH); // URL relative pour accéder à l'application
$SISED_AURL         = $SISED_SERVER . $SISED_URL; // URL absolue pour accéder à l'application
// SISED_AURL_INTERNAL : URL pour les appels curl internes (data_save.php -> questionnaire_ws.php).
//
// Session 41  : 127.0.0.1:9191 -> curl 6 (DNS) -> 41b : 127.0.0.1:9191 -> curl 7 (Connection refused)
// Cause : le port 9191 est celui du reverse proxy/Tomcat FRONTAL (externe).
//         Apache lui-meme ecoute sur $_SERVER['SERVER_PORT'] (ex: 80 ou 8080).
//
// SOLUTION DEFINITIVE (Session 41c) :
//   $_SERVER['SERVER_ADDR'] = IP sur laquelle Apache a accepte la requete (127.0.0.1 ou LAN IP)
//   $_SERVER['SERVER_PORT'] = port reel Apache (80, 8080...) - pas le port proxy externe (9191)
//   => fonctionne quelle que soit la topologie : direct, proxy, VirtualHost, XAMPP, IIS, Tomcat
$_sised_server_addr  = $_SERVER['SERVER_ADDR'];
$_sised_server_port  = (int)$_SERVER['SERVER_PORT'];
$_sised_scheme_int   = ($_sised_server_port === 443) ? 'https' : 'http';
$_sised_port_int     = (!in_array($_sised_server_port, [80, 443])) ? ':' . $_sised_server_port : '';
$SISED_AURL_INTERNAL = $_sised_scheme_int . '://' . $_sised_server_addr . $_sised_port_int . $SISED_URL;

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