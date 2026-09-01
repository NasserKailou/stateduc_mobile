<?php require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/olap.createcube.class.php'; 
    $tab_cubes=array();
    $req = "SELECT ID_OLAP FROM DICO_OLAP";
    $res = $GLOBALS['conn_dico']->GetAll($req);
    foreach($res as $rs)
    {
        $tab_cubes[] = $rs['ID_OLAP'];
    }
    $cube = new olap_create_cube($tab_cubes);        
    $cube->delete_table_faits();   
?>
