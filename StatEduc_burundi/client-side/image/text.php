<?php

header("Content-type: image/png");

//création du texte
$text = stripslashes(urldecode($_GET['text']));

$textlen = strlen(stripslashes(urldecode($_GET['text'])));

if(file_exists(dirname(__FILE__) . '/../fonts/courier8.gdf')) {
    $font = imageloadfont(dirname(__FILE__) . '/../fonts/courier8.gdf');
} else {
    $font = 1;
}

$width = imagefontheight($font);
$height = ($textlen * imagefontwidth($font)) + 4;

//création du fond transparent
$im = imagecreate($width, $height);

$background_color = imagecolorallocate($im, 255, 255, 255);
imagecolortransparent($im, $background_color);

$text_color = imagecolorallocate($im, 0, 0, 0);
imagestringup($im, $font, 0, ($height - 2),  $text , $text_color);

//génération de l'image
imagepng ($im);
imagedestroy($im);

?>