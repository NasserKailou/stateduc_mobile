<?php require_once $GLOBALS['SISED_PATH_CLS'].'metier/olap.createcube.class.php';
    $cube = new olap_create_cube();

    //echo '<HTML><HEAD>'.'<br>';    
    for($i=0; $i<count($cube->html); $i++)
    {            
        echo $cube->html[$i];
    }    
    //echo '</HEAD>'.'<br>';
    
    echo '<SCRIPT LANGUAGE="VBSCRIPT">';
    for($i=0; $i<count($cube->liste_cubes); $i++)
    {       
        echo 'Create_cube'.$cube->liste_cubes[$i].'()';
    }
    echo '</SCRIPT>';
    
    //Si c'est un seul un cube à générer, afficher le pivot table à la fin
    /*
    if(count($cube->liste_cubes)==1)
    {
        require_once $GLOBALS['SISED_PATH_CLS'].'metier/olap.class.php';
        $source_cube="OlapFile";
        $nom_cube=$cube->liste_file_cubes[0];
        $olap=new olap($source_cube, $nom_cube);//source_cube = serveur olap ou cube local
        echo $olap->html;       
    
    }    
    */

?>
