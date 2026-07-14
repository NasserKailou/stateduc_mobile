<?php /**
     * Classe create_query permet de construire une requête 
     * Les données sont récupérées d'une base de données à laide 
     * d'une requête que l'utilisateur a défini lors de la configuration 
     * de l'état soit de manière
     * brute ou en définissant les tables, les champs et les jointures
	 * @package default
     * @access  public
     * @author Alassane, Touré, Yacine
     * @version 1.4	 
    */   
class create_query{
		/**
		* Attribut : id_report
		* reference du report
		* @var numeric 
		* @access public
		*/  
    public $id_report;
    /**
	* Attribut : conn
	* Variable de connexion à la base de données
	* @var ADODB.connection 
	* @access public
	*/ 
    public $conn;    
    /**
	* Constructeur de la classe: Recupération de l'identifiant de l'état
	* et activation de la connexion
	* @access public
	* @param numeric $id_report identifiant du report
	*/    
    public function __construct($id_report){
        $this->conn  = $GLOBALS['conn'];
        $this->id_report = $id_report;
    }
    /**
	* Construction de la requête SQL associée à l'état à partir des champs et des tables définis dans la configuratio
	* @access public
	*/  
    public function creer_query() {
    	//Choix des délimiteurs de tables selon la base de données
        switch ($this->conn->databaseType){
			  
             Case 'mysqli':                                    
                        $delimit_gche="`";
                        $delimit_drte="`"; 
                        break;
						
			Case 'postgres9':                                    
                        $delimit_gche="";
                        $delimit_drte=""; 
                        break;
            default:
                    $delimit_gche="[";
                    $delimit_drte="]";   
                    break;                        
        }           
     //Recupération des champs qui ne sont pas liés à une dimension    
     $req_create_un ='SELECT     
                DICO_RPT_CHAMP.NOM_CHAMP AS CHAMP,
                DICO_RPT_CHAMP.FONCTION AS FONCTION, 
                DICO_RPT_CHAMP.FORMAT AS FORMAT, 
                DICO_RPT_CHAMP.EXPRESSION AS EXPRESSION,
                DICO_RPT_CHAMP.ALIAS AS ALIAS_CHAMP,
                DICO_RPT_CHAMP.ORDRE_DIM AS ORDRE_DIM,
                DICO_RPT_CHAMP.ID_TYPE_CHAMP AS TYPE_CHAMP, 
                DICO_RPT_CHAMP.ID_DIMENSION AS ID_DIM, 
                DICO_DIMENSION.CHAMP AS DIMENSION, 
                DICO_DIMENSION.DIM_LIBELLE AS LIBELLE_DIMENSION, 
                DICO_REPORT.LIBELLE_REPORT AS NOM_CUBE,
                DICO_RPT_CHAMP.ID_RPT_TABLE_MERE AS ID_TABLE_MERE, 
                DICO_RPT_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, 
                DICO_RPT_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE, 
                DICO_RPT_CHAMP.ORDRE_RPT AS ORDRE_RPT
                FROM         
                DICO_REPORT, 
                DICO_RPT_CHAMP, 
                DICO_DIMENSION_REPORT, 
                DICO_RPT_TABLE_MERE, 
                DICO_DIMENSION
                WHERE     (((DICO_REPORT.ID) = '.$this->id_report.') 
                AND ((DICO_RPT_CHAMP.ID) = DICO_REPORT.ID) 
                AND ((DICO_RPT_CHAMP.ID_RPT_TABLE_MERE) = DICO_RPT_TABLE_MERE.ID_RPT_TABLE_MERE)) 
                AND ((DICO_DIMENSION.ID_DIMENSION  = DICO_DIMENSION_REPORT.ID_DIMENSION))
                AND ((DICO_RPT_CHAMP.ID_DIMENSION  = DICO_DIMENSION_REPORT.ID_DIMENSION))
                AND ((DICO_RPT_CHAMP.ID  = DICO_DIMENSION_REPORT.ID))
                ORDER BY  DICO_RPT_CHAMP.ORDRE_DIM';
     //Recupération des champs qui  sont liés à une dimension
    $req_create_deux ='SELECT     
            DICO_RPT_CHAMP.NOM_CHAMP AS CHAMP,
            DICO_RPT_CHAMP.FONCTION AS FONCTION, 
            DICO_RPT_CHAMP.FORMAT AS FORMAT, 
            DICO_RPT_CHAMP.EXPRESSION AS EXPRESSION,
            DICO_RPT_CHAMP.ALIAS AS ALIAS_CHAMP,
            DICO_RPT_CHAMP.ORDRE_DIM AS ORDRE_DIM,
            DICO_RPT_CHAMP.ID_TYPE_CHAMP AS TYPE_CHAMP, 
            DICO_REPORT.LIBELLE_REPORT AS NOM_CUBE,
            DICO_RPT_CHAMP.ID_RPT_TABLE_MERE AS ID_TABLE_MERE, 
            DICO_RPT_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, 
            DICO_RPT_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE, 
            DICO_RPT_CHAMP.ORDRE_RPT AS ORDRE_RPT
            FROM         
            DICO_REPORT, 
            DICO_RPT_CHAMP, 
            DICO_RPT_TABLE_MERE
            WHERE     (((DICO_REPORT.ID) = '.$this->id_report.') 
            And (DICO_RPT_CHAMP.ID_DIMENSION Is Null)
            AND ((DICO_RPT_CHAMP.ID) = DICO_REPORT.ID) 
            AND ((DICO_RPT_CHAMP.ID_RPT_TABLE_MERE) = DICO_RPT_TABLE_MERE.ID_RPT_TABLE_MERE)) 
            ORDER BY  DICO_RPT_CHAMP.ORDRE_DIM';  

    //Recupération de la liste des tables utilisées par la requête
	$req_from='SELECT DICO_RPT_TABLE_MERE.NOM_TABLE_MERE AS TABLE_MERE, DICO_RPT_TABLE_MERE.NOM_ALIAS AS ALIAS_TABLE_MERE 
			FROM DICO_REPORT, DICO_RPT_TABLE_MERE
			WHERE DICO_REPORT.ID='.$this->id_report.' 
			AND DICO_REPORT.ID=DICO_RPT_TABLE_MERE.ID';
    //requête pour la clause where (jointure)
    $req_where='SELECT TM1.NOM_TABLE_MERE AS TABLE1, TM1.NOM_ALIAS AS ALIAS_TABLE1, CH1.NOM_CHAMP AS CHAMP1, TM2.NOM_TABLE_MERE AS TABLE2, TM2.NOM_ALIAS AS ALIAS_TABLE2, CH2.NOM_CHAMP AS CHAMP2 
		    FROM DICO_REPORT, DICO_RPT_JOINTURES, DICO_RPT_TABLE_MERE TM1, DICO_RPT_TABLE_MERE TM2, DICO_RPT_CHAMP CH1, DICO_RPT_CHAMP CH2
		    WHERE DICO_REPORT.ID='.$this->id_report.' 
            AND DICO_REPORT.ID=TM1.ID
            AND DICO_REPORT.ID=TM2.ID
            AND TM1.ID_RPT_TABLE_MERE=DICO_RPT_JOINTURES.ID_RPT_TABLE_MERE_1
            AND TM2.ID_RPT_TABLE_MERE=DICO_RPT_JOINTURES.ID_RPT_TABLE_MERE_2
            AND CH1.ID_CHAMP=DICO_RPT_JOINTURES.ID_CHAMP_1
            AND CH2.ID_CHAMP=DICO_RPT_JOINTURES.ID_CHAMP_2';

    $rs_create_un = $GLOBALS['conn_dico']->GetAll($req_create_un);
    $rs_create_deux = $GLOBALS['conn_dico']->GetAll($req_create_deux);               
    //$rs_create_deux = $this->tri_ordre_RPT($rs_create_deux);   
    if (is_array($rs_create_un) && is_array($rs_create_deux)){
        $rs_create = array_merge($rs_create_un,$rs_create_deux);
    }
	// Récuperation des critères autres que les jointures
    $req_critere='SELECT ID,NOM_TABLE, ALIAS_TABLE, NOM_CHAMP,OPERATEUR, VALEUR,TYPE_CHAMP
    FROM DICO_RPT_CRITERE
    WHERE (((DICO_RPT_CRITERE.ID)='.$this->id_report.'))';
    $rs_criteres  = $GLOBALS['conn_dico']->GetAll($req_critere);
    
    $req_mes ='SELECT Count(DICO_RPT_CHAMP.ID_CHAMP) AS NB_MESURE FROM DICO_RPT_CHAMP                            
            WHERE (DICO_RPT_CHAMP.ID='.$this->id_report.' AND (DICO_RPT_CHAMP.ID_TYPE_CHAMP=1))
            GROUP BY DICO_RPT_CHAMP.ID';
    $rs_nb_mes = $GLOBALS['conn_dico']->GetAll($req_mes);
    
    $rs_from	= $GLOBALS['conn_dico']->GetAll($req_from);
    $rs_where	= $GLOBALS['conn_dico']->GetAll($req_where);

    if (count($rs_nb_mes )>0)
        $nb_mes = $rs_nb_mes[0]['NB_MESURE'];
    else
         $nb_mes = 0;
    if(is_array($rs_create) && count($rs_create) && is_array($rs_nb_mes) && count($rs_nb_mes) && is_array($rs_from) && count($rs_from))
    {
        $script=' SELECT ';
        
                    $nb_chps=count($rs_create);
                    $cpte=0;
                    if ($nb_chps){    
                        
                        foreach($rs_create as $rs)
                        { 
                            if($rs['CHAMP']<>'' && $rs['TYPE_CHAMP']==2) // Cas des dimensions (incluant l'Atlas)
                            { 
                                //$cpte++;
                                if(trim($rs['ALIAS_TABLE_MERE'])<>"")
                                    $nom_table=$rs['ALIAS_TABLE_MERE'];
                                else
                                    $nom_table = $rs['TABLE_MERE'];                                           
                                
                                $nom_champs = $rs['CHAMP']; 
								if(trim($rs['ALIAS_CHAMP'])=="")
									$alias_champs = $nom_champs;  
								else
                                	$alias_champs = $rs['ALIAS_CHAMP']; 
                                $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.' AS '.$delimit_gche.$alias_champs.$delimit_drte.','; 
                                //if($cpte < $nb_mes ) $script.=','; 
                                //else $script.='';                                         
                            }                                        
                        }
						// Cas des champs ordre
						foreach($rs_create as $rs)
                        { 
                            if($rs['CHAMP']<>'' && $rs['TYPE_CHAMP']==4 && preg_match('/^'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($rs['CHAMP'])))
							{ 
                                //$cpte++;
                                if(trim($rs['ALIAS_TABLE_MERE'])<>"")
                                    $nom_table=$rs['ALIAS_TABLE_MERE'];
                                else
                                    $nom_table = $rs['TABLE_MERE'];                                           
                                
                                $nom_champs = $rs['CHAMP']; 
								if(trim($rs['ALIAS_CHAMP'])=="")
									$alias_champs = $nom_champs;  
								else
                                	$alias_champs = $rs['ALIAS_CHAMP']; 
                                $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.' AS '.$alias_champs.','; 
                                //if($cpte < $nb_mes ) $script.=','; 
                                //else $script.='';                                         
                            }                                        
                        }
                        // cas des mesures
                        foreach($rs_create as $rs)
                        { 
                            //if($rs['TYPE_CHAMP']==1) 
                            if($rs['CHAMP']<>'' && $rs['TYPE_CHAMP']==1  && !(isset($rs['EXPRESSION']) && trim($rs['EXPRESSION'])<>''))// Cas des mesures
                            { 
                                $cpte++;
                                $k++;
                                if(trim($rs['ALIAS_TABLE_MERE'])<>"")
                                    $nom_table=$rs['ALIAS_TABLE_MERE'];
                                else
                                    $nom_table = $rs['TABLE_MERE'];                                           
								
								$nom_champs = $rs['CHAMP']; 
								if(trim($rs['ALIAS_CHAMP'])=="")
									$alias_champs = $nom_champs;  
								else
                                	$alias_champs = $rs['ALIAS_CHAMP']; 
								                                 
                                $script.=$rs['FONCTION'].'('.$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte.') AS '.$alias_champs.''; 
                                if($cpte < $nb_mes) $script.=','; 
                                else $script.='';                                         
                            }elseif($rs['CHAMP']<>'' && $rs['TYPE_CHAMP']==1 && (isset($rs['EXPRESSION']) && trim($rs['EXPRESSION'])<>''))// Cas des mesures calculées
                            { 
                                $cpte++;
                                $k++;
                                $script.=$rs['EXPRESSION'].' AS '.$rs['ALIAS_CHAMP'].''; 
                                if($cpte < $nb_mes) $script.=','; 
                                else $script.='';                                         
                            }                                      
                        }
                    }
        //Contruction de la clause FROM            
        $script.=' FROM ';                
                    $nb_chps=count($rs_from);
                    $cpte=0;
                    if ($nb_chps)
                    {                        
                        foreach($rs_from as $rs)
                        { 
                            $cpte++;
                            if(trim($rs['ALIAS_TABLE_MERE'])<>"")
                                $nom_alias_table=' AS '.$delimit_gche.$rs['ALIAS_TABLE_MERE'].$delimit_drte;
                            else
                                $nom_alias_table='';                                    
                            $script.=$delimit_gche.$rs['TABLE_MERE'].$delimit_drte.$nom_alias_table;
                            if($cpte < $nb_chps) $script.=','; 
                            else $script.='';                                                               
                        }
                    }
        //Construction de la clause where
        //Ajout des liens entre les tables             
        $script.=' WHERE ';            
                    $nb_chps=count($rs_where);
                    $cpte=0;
                    if ($nb_chps)
                    {  
                        foreach($rs_where as $rs)
                        { 
                            $cpte++; 
                            if(trim($rs['ALIAS_TABLE1'])<>"")
                                $nom_table1=$rs['ALIAS_TABLE1'];
                            else
                                $nom_table1 = $rs['TABLE1'];     
                            if(trim($rs['ALIAS_TABLE2'])<>"")
                                $nom_table2=$rs['ALIAS_TABLE2'];
                            else
                                $nom_table2 = $rs['TABLE2'];     
                            $script.=$delimit_gche.$nom_table1.$delimit_drte.'.'.$delimit_gche.$rs['CHAMP1'].$delimit_drte.' = '.$delimit_gche.$nom_table2.$delimit_drte.'.'.$delimit_gche.$rs['CHAMP2'].$delimit_drte.' ';                                      
                            if($cpte < $nb_chps) $script.='And '; 
                            else $script.='';                                                               
                        }
                    }
                    
        // Début d'insertion des critères
        if ($rs_criteres){
        if (count($rs_criteres )>0){
            foreach ($rs_criteres as $critere){
                $script.=' AND ';
                if (trim($critere['ALIAS_TABLE'])<>'')                        
                     $script.= $delimit_gche.trim($critere['ALIAS_TABLE']).$delimit_drte.'.';
                else
                    $script.= $delimit_gche.trim($critere['NOM_TABLE']).$delimit_drte.'.';
                
                $script.= $delimit_gche.trim($critere['NOM_CHAMP']).$delimit_drte;
                if (trim($critere['OPERATEUR'])<>'')                        
                     $script.= ' '.trim($critere['OPERATEUR']).' ';
                else
                    $script.= ' = ';
                
                if(trim($critere['OPERATEUR'])<>'IN' && trim($critere['OPERATEUR'])<>'NOT IN')
                {
                    if (trim($critere['TYPE_CHAMP'])=='int')                        
                         $script.= trim($critere['VALEUR']).'';
                    else
                        $script.= '\''.trim($critere['VALEUR']).'\'';                           
                }
                else
                {
                    $script.= '('.trim($critere['VALEUR']).')';
                }
            }
         }
         }
         // GROUP BY
         $cpte=0;
        if ($nb_chps){    
        $script.= ' GROUP BY ';
        //Cas des champs de l'Atlas et des dimensions
        foreach($rs_create as $rs)
        { 
            if($rs['CHAMP']<>'' && $rs['TYPE_CHAMP']==2) // cas des dimensions
            { 
                $cpte++;
                if(trim($rs['ALIAS_TABLE_MERE'])<>"")
                    $nom_table=$rs['ALIAS_TABLE_MERE'];
                else
                    $nom_table = $rs['TABLE_MERE'];                                           
                
                $nom_champs = $rs['CHAMP'];  
                //$alias_champs= $rs['ALIAS_CHAMP']; 
                if ($cpte==1){
                    $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte; 
                }else{
                    $script.=','.$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte; 
                }                                                
            }                                        
        }
		// cas des champs ordre
		foreach($rs_create as $rs)
        { 
            if($rs['CHAMP']<>'' && $rs['TYPE_CHAMP']==4 && preg_match('/^'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($rs['CHAMP'])))
            { 
                $cpte++;
                if(trim($rs['ALIAS_TABLE_MERE'])<>"")
                    $nom_table=$rs['ALIAS_TABLE_MERE'];
                else
                    $nom_table = $rs['TABLE_MERE'];                                           
                
                $nom_champs = $rs['CHAMP'];  
                //$alias_champs= $rs['ALIAS_CHAMP']; 
                if ($cpte==1){
                    $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte; 
                }else{
                    $script.=','.$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte; 
                }                                                
            }                                        
        }
       
	   //Cas des champs de mesure de type text
        foreach($rs_create as $rs)
        { 
            if($rs['CHAMP']<>'' && $rs['TYPE_CHAMP']==1 && $rs['FORMAT']=='text') // cas des mesures text
            { 
                $cpte++;
                if(trim($rs['ALIAS_TABLE_MERE'])<>"")
                    $nom_table=$rs['ALIAS_TABLE_MERE'];
                else
                    $nom_table = $rs['TABLE_MERE'];                                           
                
                $nom_champs = $rs['CHAMP'];  
                //$alias_champs= $rs['ALIAS_CHAMP']; 
                if ($cpte==1){
                    $script.=$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte; 
                }else{
                    $script.=','.$delimit_gche.$nom_table.$delimit_drte.'.'.$delimit_gche.$nom_champs.$delimit_drte; 
                }                                                
            }                                        
        }
    }
       return $script;        
       }           
    }
    /**
	* Tri des champs dimension suivant leur ordre d'arrivée dans la dimension
	* @access private
    * @param array rs_create tableau de champ
	*/  
    //Tri des champs dimension dans leur ordre d'affichage
	private function tri_ordre_RPT($rs_create) {
            $rs_create_tri = array();
            $result = array(); 
            $rs_create_tri  = $rs_create;
            while (count($rs_create_tri)>0){
                $min=-1;
                $elt_min = array();
                
                if (is_array($rs_create_tri)){
                    foreach ($rs_create_tri as $elt){
                        if ($min == -1){
                            $min = $elt['ORDRE_RPT'];  
                            $elt_min =$elt;
                        }
                        else{
                            if ($min > $elt['ORDRE_RPT']){
                                $min = $elt['ORDRE_RPT'];
                                $elt_min =$elt;
                            }                                
                        }
                    }
                }
                
                $result[]= $elt_min;
                
                $temp= array();
                if (is_array($rs_create_tri)){
                    foreach ($rs_create_tri as $elt){
                        if ($elt['ORDRE_RPT']<>$min)
                        $temp[] = $elt;
                    }
                }
                $rs_create_tri = $temp;
            }
            return $result;
        }
}
?>
