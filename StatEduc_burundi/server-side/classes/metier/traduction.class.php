<?php class traduction {
   
    private $conn;    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $valeurs_nomenclatures   = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $donnees_bdd = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $donnees_post = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $matrice_donnees_template = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $matrice_donnees_bdd= array();
    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $matrice_donnees = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $template ;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $entete_template;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $fin_template;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $nb_lignes = 8;   
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $VARS_GLOBALS = array();
    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_id   ;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_nom  ;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_lib1  ='LIBELLE_A';
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $champ_lib2  ='LIBELLE_B';     
    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $list_tables = array();
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $list_langues = array();    
    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $langue_depart ;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $langue_arrive;      
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $nom_table ;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $table_ref;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $type_traduction;
    
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_name;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_name;
    	
		/**
		* Attribut : 
		* <pre>
		* 
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_ordre;

    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function __construct($langue_depart, $langue_arrive ,$nom_table, $type_traduction, $conn) {

            $this->langue_depart        =   $langue_depart;
            $this->langue_arrive        =   $langue_arrive;            
            $this->nom_table            =   $nom_table;

            $this->type_traduction      =   $type_traduction;
            if ($this->type_traduction==1){
                $this->table_ref            =   'DICO_TRADUCTION';
                $this->champ_id             =   'CODE_NOMENCLATURE';
                $this->champ_nom            =   'NOM_TABLE';
            }else {
                $this->table_ref            =   'DICO_LIBELLE_PAGE';
                $this->champ_id             =   'CODE_LIBELLE';
                $this->champ_nom            =   'NOM_PAGE';
            }

            $this->lire_dico            =   $lire_dico;
           // $this->conn                 =   $conn;   
		   // Positionnement de la connexion 
		   // Modif pour externalisation de DICO
		   if ($this->type_traduction==1){
			   if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/',strtoupper($this->nom_table))){
					$this->conn                =   $GLOBALS['conn'];
			   } else{
					$this->conn                   =   $GLOBALS['conn_dico'];
			   }
			 }else{
			 	$this->conn                =   $GLOBALS['conn_dico']; 
			}
			 $GLOBALS['conn_trad'] = $this->conn  ;
		          
            $this->template             =   file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'traduction.html'); 

    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function __wakeup(){
        	$this->conn =   $GLOBALS['conn'];
			if ($this->type_traduction==1){
			   if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/',strtoupper($this->nom_table))){
					$this->conn                =   $GLOBALS['conn'];
			   } else{
					$this->conn                   =   $GLOBALS['conn_dico'];
			   }
			 }else{
			 	$this->conn                =   $GLOBALS['conn_dico']; 
			}

    	}
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_donnees(){

       $requete = "SELECT  DISTINCT A.".$this->champ_id.", A.LIBELLE AS LIBELLE_A, B.LIBELLE AS LIBELLE_B
                     FROM ".$this->table_ref. " AS A, ".$this->table_ref. " AS B 
                     WHERE A.".$this->champ_id." = B.".$this->champ_id." 
                      AND A.CODE_LANGUE='".$this->langue_depart."'
                      AND B.CODE_LANGUE='".$this->langue_arrive."'
                      AND A.".$this->champ_nom."=B.".$this->champ_nom. 
                     " AND A.".$this->champ_nom."='".$this->nom_table."'
                     ORDER BY A.LIBELLE";
                     
       /*$requete = "SELECT  DISTINCT A.".$this->champ_id.", A.LIBELLE AS LIBELLE_A, B.LIBELLE AS LIBELLE_B
                     FROM ".$this->table_ref. " AS A, ".$this->table_ref. " AS B 
                     WHERE A.".$this->champ_id." = B.".$this->champ_id." 
                      AND A.CODE_LANGUE='".$this->langue_depart."'
                      AND B.CODE_LANGUE='".$this->langue_arrive."'
                      AND A.".$this->champ_nom."=B.".$this->champ_nom. 
                     " AND A.".$this->champ_nom."='".$this->nom_table."'
                     ORDER BY A.".$this->champ_id;*/
                          
        // Gestion des erreurs lors de l'exécution de la requête SQL 
        try{
            $tab_donnees    =   array(); 
            $valeurs = $this->conn->GetAll($requete);
            if (!is_array($valeurs))            
                throw new Exception('ERR_SQL');
            if (count($valeurs)>0) {
                for ($i=0;$i<count($valeurs);$i++){
                    $tab_donnees []    =   $valeurs[$i];
                }
            } 
        }        
        catch (Exception $e){
             $erreur = new erreur_manager($e,$requete);
        }
           
        $this->VARS_GLOBALS['donnees_bdd']   =   $tab_donnees;
        $this->limiter_affichage();
        $this->init_liste_table();        
        
    }

    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function fix_donnees_non_traduites() {

        //on séléctionne les données de la langue de départ

        $requete = 'SELECT '.$this->champ_id.' FROM '.$this->table_ref.' WHERE CODE_LANGUE = \''.$this->langue_depart.'\' AND '.$this->champ_nom.' = \''.$this->nom_table.'\' ORDER BY '.$this->champ_id.';';
        try {
            $langue1 = $this->conn->GetAll($requete);
            if (!is_array($langue1 ))
                throw new Exception('ERR_SQL');
        }
        catch(Exception $e){
            $erreur = new erreur_manager($e,$requete);
        }

        //on sélectionne les données de la langue d'arrivée
        $requete = 'SELECT '.$this->champ_id.' FROM '.$this->table_ref.' WHERE CODE_LANGUE = \''.$this->langue_arrive.'\' AND '.$this->champ_nom.' = \''.$this->nom_table.'\' ORDER BY '.$this->champ_id.';';
        try {
            $langue2 = $this->conn->GetAll($requete);
            if (!is_array($langue2 ))
                throw new Exception('ERR_SQL');
        }
        catch(Exception $e){
            $erreur = new erreur_manager($e,$requete);
        }

        //on vérifie que dans langue2 il n'y a pas des id non disponibles dans l1

        if(count($langue2)>count($langue1)) {

            return false;

        }

        //on stocke chacune des langues dans un array simple

        $langue1_ids = array();
        $langue2_ids = array();

        foreach($langue1 as $l1) {

            array_push($langue1_ids, $l1[$this->champ_id]);

        }

        foreach($langue2 as $l2) {

            //on vérifie au passage qu'il n'ya pas d'incohérence (des enregistrements de langue2 qui n'existe pas dans langue1)
            if(!in_array($l2[$this->champ_id], $langue1_ids)) {

                return false;

            }

            array_push($langue2_ids, $l2[$this->champ_id]);

        }

        //on compare les deux langues 
        foreach($langue1 as $l1) {

            //et au besoin
            if(!in_array($l1[$this->champ_id], $langue2_ids)) {

                //on insère
                $requete = 'INSERT INTO '.$this->table_ref.' ('.$this->champ_nom.', CODE_LANGUE, '.$this->champ_id.') VALUES (\''.$this->nom_table.'\', \''.$this->langue_arrive.'\', '.$this->conn->qstr($l1[$this->champ_id]).');';
                try{
                    if(($this->conn->Execute($requete))===false)
                        throw new Exception('ERR_SQL');
                    
                }
                catch (Exception $e){
                    $erreur = new erreur_manager($e,$requete);
                }

            }

        }

        return true;

    }

    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function transformer_donnees(){
       $this->matrice_donnees_template = array();
       foreach ($this->matrice_donnees as $ligne_donnes) {
            $donnees_transformees = array();
            $donnees_transformees[0] = $ligne_donnes[$this->champ_id ];
            $donnees_transformees[1] = $ligne_donnes[$this->champ_lib1 ];
            $donnees_transformees[2] = $ligne_donnes[$this->champ_lib2 ];
            $this->matrice_donnees_template[] = $donnees_transformees; 
            
        }
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function limiter_affichage(){
        if(!isset($GLOBALS['nbre_total_enr']))
                    $GLOBALS['nbre_total_enr'] = count( $this->VARS_GLOBALS['donnees_bdd'] );                    
                    
                    $mat = array_slice( $this->VARS_GLOBALS['donnees_bdd'], $GLOBALS['debut'], $GLOBALS['cfg_nbres_ppage']);               
                
                foreach( $mat as $ligne_donnees){
                    $this->matrice_donnees[] = $ligne_donnees;
                }
                $GLOBALS['nbenr'] = count($this->matrice_donnees);
                
                // transfromation des donnees
                $this->transformer_donnees();
    }    
   
   
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function recherche_libelle_page($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				$requete 	= "SELECT LIBELLE
										FROM DICO_LIBELLE_PAGE 
										WHERE CODE_LIBELLE='".$code."' And CODE_LANGUE='".$langue."'
										AND NOM_PAGE='".$table."'";
				
				// Gestion des erreurs lors de l'exécution de la requête SQL
				
                try {
                    $all_res	= $GLOBALS['conn_dico']->GetAll($requete);
                    if (!is_array($all_res))                    
                        throw new Exception('ERR_SQL');                     
                }
                catch(Exception $e){
                    $erreur = new erreur_manager($e,$requete);
                }                 
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
    }    

    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function recherche_langue($langue){
        // Cette fonction permet de récuoérer le libellé de la langue passé en paramètre
        $requete    =   "SELECT LIBELLE_LANGUE 
                        FROM DICO_LANGUE
                        WHERE CODE_LANGUE='".$langue."'";
        // Gestion des erreurs lors de l'exécution de la requête SQL
        try{
            $all_res	= $GLOBALS['conn_dico']->GetAll($requete);
            if (!is_array($all_res))                    
                throw new Exception('ERR_SQL');    
        }
        catch(Exception $e){
            $erreur = new erreur_manager($e,$requete);
        }
        
        return($all_res[0]['LIBELLE_LANGUE']);
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function remplir_template($template){
        if (is_array($this->matrice_donnees)){          
            
            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                // cas des champs id                   
                if (isset($this->matrice_donnees[$ligne][$this->champ_id ])){
                  //  echo 'passé la';
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_id ];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'CODE_'.$ligne} = ''.$val_champ_base.'';                                      

                }else{
                    ${'CODE_'.$ligne} = '';	
                }
                
                // cas des champs libellé
                if (isset($this->matrice_donnees[$ligne][$this->champ_lib1])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_lib1];
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'LIBELLE_'.$ligne} = ''.$val_champ_base.'';
                }else{
                    ${'LIBELLE_'.$ligne} = '';	
                }
                
                // cas des champs ordre
                if (isset($this->matrice_donnees[$ligne][$this->champ_lib2])){
                    $val_champ_base = $this->matrice_donnees[$ligne][$this->champ_lib2];                    
                    //$val_champ_base = addslashes($val_champ_base);                    
                    ${'ORDRE_'.$ligne} = ''.$val_champ_base.'';                    
                }else{
                    ${'ORDRE_'.$ligne} = '';	
                }
                
            }
            // entete du template
            ${'id_name'}= $this->id_name;
            ${'lib_name'}=$this->lib_name;
            ${'lib_ordre'}=$this->lib_ordre;
        }       
       
       //return eval("echo \"$template\";");
       // return eval("echo \"$template\";");
        eval('$result = sprintf(\'%s\', "'.str_replace('"','\"',$template).'");');
        return $result;
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function get_post_template($matr){      

        if (is_array($matr)){              

            for($ligne=0;$ligne<$this->nb_lignes;$ligne++){
                $champ_id       = 'CODE'.'_'.$ligne;
                $champ_lib      = 'LIBELLE'.'_'.$ligne;
                $champ_ordre    = 'ORDRE'.'_'.$ligne;
                $delete         = 'DELETE_'.$ligne;               

                if (isset($matr[$champ_id]) && $matr[$champ_id]<>''){                   
                    if (!isset($matr[$delete])) {
                        $donnees_ligne  =   array();
                        $donnees_ligne [] = $matr[$champ_id];
                        if (isset($matr[$champ_lib]) && $matr[$champ_lib]<>'' ){
                            $donnees_ligne [] = $matr[$champ_lib];
                        }else{
                            $donnees_ligne []='\'\'';
                        }
                        
                        if (isset($matr[$champ_ordre]) && $matr[$champ_ordre]<>'' ){
                            $donnees_ligne [] = $matr[$champ_ordre];
                        }else{
                            $donnees_ligne []='\'\'';
                        }                                        
                        
                        
                        $this->donnees_post[]= $donnees_ligne;
                    }
                }else{
                    // traitement des nouvelles creations de données dans ce cas il faut nécessairement saisir les libellés
                    if (isset($matr[$champ_lib]) && $matr[$champ_lib]<>''){                      
                        // il faut trouver la valeur max dans la table de nomenclature
                        $max_cle_incr++;
                        $donnees_ligne  =   array();
                        $donnees_ligne [] = $max_cle_incr;
                        $donnees_ligne [] = $matr[$champ_lib];
                        //$donnees_ligne [] = $matr[$champ_ordre]; 
                        if (isset($matr[$champ_ordre]) && $matr[$champ_ordre]<>'' ){
                            $donnees_ligne [] = $matr[$champ_ordre];
                        }else{
                            $donnees_ligne []='\'\'';
                        }                        
                        $this->donnees_post[]= $donnees_ligne;                        
                        
                    }
                    
                }
                
            }
        }
    }    
    
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function init_liste_table(){
        
        $sql ='SELECT DISTINCT '.$this->champ_nom.' FROM '.$this->table_ref. ' ORDER BY '.$this->champ_nom;         
        
        // Gestion des erreurs lors de l'exécution de la requête sql
        try{        
            //$list_traduction =   $this->conn->GetAll($sql);  
			$list_traduction =  array_merge ($GLOBALS['conn_dico']->GetAll($sql), $GLOBALS['conn']->GetAll($sql));  
            if (!is_array($list_traduction)) 
                throw new Exception('ERR_SQL');            
            foreach($list_traduction as $traduction) {
                $this->list_tables[] = $traduction[$this->champ_nom];
            }
        }
        catch (Exception $e){
            $erreur = new erreur_manager($e,$requete);
        }        

        $curpage = $_SERVER['PHP_SELF'];

        $gets = '';
				//$gets_chg_tbl='';

        foreach($_GET as $i=>$g) {

            $catch = false;

            if($i != 'lib_nom_table' && $i != 'langue_depart' && $i != 'langue_arrive' && $i != 'type_traduction') {

                $catch = true;

                $gets .= $i . '=' . $g;

            }

            if($catch && $i<(count($_GET)-1)) {

                $gets .= '&';

            }

        }

        $entete     =   '<script type="text/javascript">';	
        $entete     .=   "function recharge(table_nomenc, langue_depart,langue_arrive,type_traduction) {";
				$entete	    .=   "location.href   = '".$curpage."?".$gets."&lib_nom_table='+table_nomenc+'&langue_depart='+langue_depart+'&langue_arrive='+langue_arrive+'&type_traduction='+type_traduction";
				$entete     .=   " }";
        $entete     .=   "function change_table(table_nomenc, langue_depart,langue_arrive,type_traduction) {";
				// on place $_GET['debut'] à 0
				$gets_chg_tbl = preg_replace("/(debut=)[0-9]*&/", "\\1".'0&', $gets );
				$entete	    .=   "location.href   = '".$curpage."?".$gets_chg_tbl."&lib_nom_table='+table_nomenc+'&langue_depart='+langue_depart+'&langue_arrive='+langue_arrive+'&type_traduction='+type_traduction";
				$entete     .=   " }";
				$entete     .=   "</script>";

        $entete     .= "<br />";
        $entete     .="<form action='".$_SERVER['REQUEST_URI']."' method='post' name='formulaire'>";    
        $entete     .="<span class=''>";
        $entete     .= '<div><table><tr><td>'.$this->recherche_libelle_page('DescLangD',$_SESSION['langue'],'traduction').'</td><td width="70%"> ';        
        $entete     .="<select style='width:100%;' name='langue_depart' onchange= 'recharge(table_nomenc.value,langue_depart.value,langue_arrive.value,".$this->type_traduction.")'>";
        
        $this->init_liste_langues();        
        foreach ($this->list_langues as $langue) {
            $entete .= '<option value='.'\''.$langue[0].'\'';
            
            if ($langue[0]==$this->langue_depart) {
            $entete .=   ' selected ';   
            }
            
            $entete .= '>'.$langue[1].'</option>';            
        }
        $entete     .='</select></td></tr>';
        
        $entete     .='<tr><td>'.$this->recherche_libelle_page('DescLangF',$_SESSION['langue'],'traduction').'</td><td> ';        
        $entete     .="<select style='width:100%;' name='langue_arrive' onchange= 'recharge(table_nomenc.value,langue_depart.value,langue_arrive.value,".$this->type_traduction.")'>";       
             
        foreach ($this->list_langues as $langue) {
            $entete .= '<option value='.'\''.$langue[0].'\'';
            
            if ($langue[0]==$this->langue_arrive) {
            $entete .=   ' selected ';   
            }
            
            $entete .= '>'.$langue[1].'</option>';            
        }
        $entete     .='</select></td></tr>';      
        		
        $entete     .='<tr><td>'.$this->recherche_libelle_page('DescTable',$_SESSION['langue'],'traduction').'</td><td>';
        $entete     .="<select style='width:100%;' name='table_nomenc' onchange= 'change_table(table_nomenc.value,langue_depart.value,langue_arrive.value,".$this->type_traduction.")'>";
        
        foreach ($this->list_tables as $nom_table) {
            $entete .= '<option value='.'\''.$nom_table.'\'';
            if ($nom_table==$this->nom_table) {
            $entete .=   ' selected ';   
            }
            
            $entete .= '>'.$nom_table.'</option>';            
        }
        $entete     .='</select></td></tr>';        
        $entete     .="<tr><td>".$this->recherche_libelle_page('DescDicoTr',$_SESSION['langue'],'traduction')." <INPUT TYPE='radio' name='type_traduction' value=1";
        if ($this->type_traduction==1){
            $entete .=' checked ';
        }
        $entete     .=" onClick= 'recharge(\"\",langue_depart.value,langue_arrive.value,1)'> </td>";
        $entete     .="<td>".$this->recherche_libelle_page('DescDicoPg',$_SESSION['langue'],'traduction')." <INPUT TYPE='radio' name='type_traduction' value=2";
        if ($this->type_traduction==2){
            $entete .=' checked ';
        }
        $entete     .=" onClick= 'recharge(\"\",langue_depart.value,langue_arrive.value,2)'> </td></tr>";
        
        
        
       /* $entete     .="<tr><td>Dico traduction</td><td><INPUT id='check' type='checkbox' name='lire_dico' value='1' ";
        if ($_GET['lire_dico']==1){
            $entete .=' checked ';
        }
        
        $entete .=  "onclick='recharge(table_nomenc.value,systeme.value,lire_dico.value)'></td></tr>";*/
        
        $entete     .= '</table></div></span><br />';  
        $this->entete_template = $entete;
        //$this->fin_template='</Form>'; 
        $this->fin_template="<br /><div><INPUT TYPE='SUBMIT' VALUE='".$this->recherche_libelle_page('Valider',$_SESSION['langue'],'traduction')."'></div></span></Form>";        
     
    }
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function init_liste_langues() {
        $sql    = 'SELECT * FROM DICO_LANGUE';
                         
        
        try{        
            if(($rs =  $GLOBALS['conn_dico']->Execute($sql))===false)
                    throw new Exception('ERR_SQL');
            if (!$rs->EOF){
                $rs->MoveFirst();
                while (!$rs->EOF){
                    $secteur = array();
                    $secteur [] = $rs->fields['CODE_LANGUE'];
                    $secteur [] = $rs->fields['LIBELLE_LANGUE'];                    
                    $this->list_langues[] = $secteur;
                    $rs->MoveNext();
    
                }
            }
        }
        catch(Exception $e){
            $erreur = new erreur_manager($e,$requete);
        } 
        
    }
    
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function comparer ($matr1,$matr2){
	
		// Cette fonction permet de faire la comparaison matricielle complexe		
		//$indice_cle	=	$this->nb_cles - 1;		
		
		$indice_cle	=	0;
				
		$result 	=	array();
		$i = 0;
		foreach ($matr2 as $elt)
		{
				// cette variable contient la clé identifiant de l'élément matriciell
				
				$cle	=	associer_identifiant($elt,$indice_cle);
								
				$action	=	existe_element_grille_action($cle,$elt,$indice_cle,$matr1);		
				//echo $action. '<br />';
				switch ($action)
				{
					case 'I':
					case 'U':				
						for ($i=0;$i<count($elt);$i++){
							$tmp[$i]	=	$elt[$i];		
						}
						$tmp[$i++]	=	$action;
						$result []	=	$tmp;
						break;
					
				}	
		}
		
		foreach ($matr1 as $elt)
		{
				// cette variable contient la clé identifiant de l'élément matriciell
				
				$cle	=	associer_identifiant($elt,$indice_cle);
				$action	=	existe_element_grille_action($cle,$elt,$indice_cle,$matr2);
				switch ($action)
				{
					case 'I':
						
						$action		=	'D'	;				
						for ($i=0;$i<count($elt);$i++){
							$tmp[$i]	=	$elt[$i];		
						}
						$tmp[$i++]	=	$action;
						$result []	=	$tmp;
						break;
				}	
		}				
		
		$this->matrice_donnees_bdd	=	$result;
	}	
    
    
    
		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		public function maj_bdd($matr){    
        if (is_array($matr)){                
        	foreach ($matr as $tab){
                $action = $tab[sizeof($tab)-1];
                //echo 'valeur action = ' .$action;
                switch ($action){
                    case 'I': 
                        break;
                    case 'U':
                        // Mise à jour des données sur les nomenclatures et dans la traduction                      
                       if (strlen($tab[2])>0 && $tab[2]<>"''") {
                            // Cette condition permet d'éviter d'écrire des \ dans la traduction
                            if ($this->type_traduction==1) {
                                $sql = 'UPDATE '.$this->table_ref.' SET LIBELLE='.$this->conn->qstr($tab[2]).
                                   ' WHERE '.$this->champ_id.'='.$tab[0].
                                   ' AND CODE_LANGUE=\''.$this->langue_arrive.'\''.
                                   ' AND '.$this->champ_nom .'=\''.$this->nom_table.'\''; 
                            }
                            else{
                                $sql = 'UPDATE '.$this->table_ref.' SET LIBELLE='.$this->conn->qstr($tab[2]).
                               ' WHERE '.$this->champ_id.'=\''.$tab[0].'\''.
                               ' AND CODE_LANGUE=\''.$this->langue_arrive.'\''.
                               ' AND '.$this->champ_nom .'=\''.$this->nom_table.'\''; 
                            }
                              
                        } 
                        else{
                            if ($this->type_traduction==1) {
                                $sql = 'UPDATE '.$this->table_ref.' SET LIBELLE='."''".
                                   ' WHERE '.$this->champ_id.'='.$tab[0].
                                   ' AND CODE_LANGUE=\''.$this->langue_arrive.'\''.
                                   ' AND '.$this->champ_nom .'=\''.$this->nom_table.'\''; 
                            }
                            else{
                                $sql = 'UPDATE '.$this->table_ref.' SET LIBELLE='."''".
                               ' WHERE '.$this->champ_id.'=\''.$tab[0].'\''.
                               ' AND CODE_LANGUE=\''.$this->langue_arrive.'\''.
                               ' AND '.$this->champ_nom .'=\''.$this->nom_table.'\''; 
                            }
                        
                        }
                       try{
                            if ($this->conn->execute($sql)===false) 
                                throw new Exception('ERR_SQL');
                        }catch (Exception $e){
                                $erreur = new erreur_manager($e,$sql);
                        }                       
                        break;
                    case 'D' : 
                        break;
            }
            }
         }   
    }
    

}
?>
