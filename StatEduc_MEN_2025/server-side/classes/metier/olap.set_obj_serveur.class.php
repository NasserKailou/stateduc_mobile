
<?php class set_obj_serveur{   
       
    public $conn;
    public $default_db;
    public $liste_cubes = array();
    
    public function __construct($default_db=''){
            $this->conn  = $GLOBALS['conn_dico'];  
            $this->default_db = $default_db;            
            
        }
        
    public function set_olap_base(){
        
        if($this->default_db <> '') {
            $req = 'SELECT * FROM DICO_OLAP_OBJ_SERVEUR WHERE BASE_OLAP =\''.$this->default_db.'\'';
        }else{
            $req = "SELECT * FROM DICO_OLAP_OBJ_SERVEUR";
        }   
        $res = $GLOBALS['conn_dico']->GetAll($req);
        if(is_array($res )){
            $liste_cubes = array();
            $tab_cube = array();
            if (count($res)>0){
                foreach($res as $rs) {
                    $tab_cube['ID'] = $rs['ID_CUBE'];
                    $tab_cube['NAME'] = $rs['NOM_CUBE'];
                    $liste_cubes[$rs['BASE_OLAP']][] = $tab_cube;                             
                }
            }
        }
        $this->liste_cubes =  $liste_cubes;        
    }
    
}
    
?>
