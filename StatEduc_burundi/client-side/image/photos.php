<?php

session_start();

$name = 'defaut';

if(isset($_SESSION['style'])) {

    $name = preg_replace('`\.css$`', '', $_SESSION['style']);

    if(!file_exists(dirname(__FILE__) . '/photo-' . $name . '.jpg')) {

        $name = 'defaut';

    }

}

if(file_exists(dirname(__FILE__) . '/photo-' . $name . '.jpg')) {

    header("Content-type: image/jpg");
    readfile(dirname(__FILE__) . '/photo-' . $name . '.jpg');

}

?>