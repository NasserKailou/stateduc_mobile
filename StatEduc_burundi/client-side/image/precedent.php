<?php

session_start();

$name = 'defaut';

if(isset($_SESSION['style'])) {

    $name = preg_replace('`\.css$`', '', $_SESSION['style']);

    if(!file_exists(dirname(__FILE__) . '/precedent-' . $name . '.gif')) {

        $name = 'defaut';

    }

}

if(file_exists(dirname(__FILE__) . '/precedent-' . $name . '.gif')) {

    header("Content-type: image/gif");
    readfile(dirname(__FILE__) . '/precedent-' . $name . '.gif');

}

?>