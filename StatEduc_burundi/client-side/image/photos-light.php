<?php

// session 34 : read_and_close - evite deadlock XAMPP Windows lock session
@session_start(['read_and_close' => true]);

$name = 'defaut';

if(isset($_SESSION['style'])) {

    $name = preg_replace('`\.css$`', '', $_SESSION['style']);

    if(!file_exists(dirname(__FILE__) . '/photo-' . $name . '-light.jpg')) {

        $name = 'defaut';

    }

}

if(file_exists(dirname(__FILE__) . '/photo-' . $name . '-light.jpg')) {

    header("Content-type: image/jpg");
    readfile(dirname(__FILE__) . '/photo-' . $name . '-light.jpg');

}

?>