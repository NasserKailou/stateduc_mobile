<?php //Variables sur le serveur
$SISED_SERVER       = 'http://' . $_SERVER['HTTP_HOST']; // Sans / à la fin
$SISED_PATH         = preg_replace('`\\\`', '/', dirname(__FILE__)) . '/'; // Chemin absolu pour accéder à l'application
$SISED_URL          = str_replace(preg_replace('`\\\`', '/', $_SERVER['DOCUMENT_ROOT']), '', $SISED_PATH); // URL relative pour accéder à l'application
$SISED_AURL         = $SISED_SERVER . $SISED_URL; // URL absolue pour accéder à l'application
// SISED_AURL_INTERNAL : URL locale pour les appels curl internes (data_save.php -> questionnaire_ws.php).
// PROBLEME PRODUCTION : $SISED_SERVER utilise $_SERVER['HTTP_HOST'] = 'stateduc.ins.ne:9191'.
// Quand PHP fait un curl vers cette URL depuis le serveur lui-meme, le DNS interne
// 'stateduc.ins.ne' doit etre resolvable depuis la VM. S'il ne l'est pas (entree
// /etc/hosts manquante), curl echoue avec : "6 : Could not resolve host: stateduc.ins.ne".
// SOLUTION : remplacer le hostname par 127.0.0.1 pour les appels curl internes.
// Le port est conserve depuis HTTP_HOST (ex: 9191) pour que Apache/IIS achemine vers le bon vhost.
$_sised_host_parts  = explode(':', $_SERVER['HTTP_HOST']);
$_sised_port        = isset($_sised_host_parts[1]) ? ':' . $_sised_host_parts[1] : '';
$SISED_AURL_INTERNAL = 'http://127.0.0.1' . $_sised_port . $SISED_URL;

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