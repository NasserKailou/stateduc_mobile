<?php //Variables sur le serveur
$SISED_SERVER       = 'http://' . $_SERVER['HTTP_HOST']; // Sans / à la fin
$SISED_PATH         = preg_replace('`\\\`', '/', dirname(__FILE__)) . '/'; // Chemin absolu pour accéder à l'application
$SISED_URL          = str_replace(preg_replace('`\\\`', '/', $_SERVER['DOCUMENT_ROOT']), '', $SISED_PATH); // URL relative pour accéder à l'application
$SISED_AURL         = $SISED_SERVER . $SISED_URL; // URL absolue pour accéder à l'application

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