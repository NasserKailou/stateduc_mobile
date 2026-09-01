<?php class import_dim_olap
{
    public $conn;
    public $id_olap;
    public $tab_dim = array();
    public $liste_dim = array();
    public $liste_tabm = array();
    public $liste_chps = array();
    public $liste_join = array();
    
    function __construct($tab_dim, $id_olap)
    {   
        $this->conn = $GLOBALS['conn'];
        $this->tab_dim = $tab_dim;
        $this->id_olap = $id_olap;       
    }
    
    function get_donnees_dim()
    {        
        $req_max_id_dim='SELECT MAX(ID_DIMENSION) AS MAX_ID FROM DICO_OLAP_DIMENSION';
        $req_max_id_chp='SELECT MAX(ID_CHAMP) AS MAX_ID FROM DICO_OLAP_CHAMP';
        $req_max_id_tabm='SELECT MAX(ID_OLAP_TABLE_MERE) AS MAX_ID FROM DICO_OLAP_TABLE_MERE';
        $req_max_ordre_in_olap='SELECT MAX(ORDRE_OLAP) AS MAX_ORDRE FROM DICO_OLAP_CHAMP WHERE ID_OLAP = '.$this->id_olap;
        
        $rs_id_dim=$GLOBALS['conn_dico']->GetAll($req_max_id_dim);
        $rs_id_chp=$GLOBALS['conn_dico']->GetAll($req_max_id_chp);
        $rs_id_tabm=$GLOBALS['conn_dico']->GetAll($req_max_id_tabm);
        $rs_ordre_in_olap=$GLOBALS['conn_dico']->GetAll($req_max_ordre_in_olap);
        
        if (count($rs_id_dim )>0)
            $max_id_dim = $rs_id_dim[0]['MAX_ID'];
        else
            $max_id_dim = 0;
        
        if (count($rs_id_chp )>0)
            $max_id_chp = $rs_id_chp[0]['MAX_ID'];
        else
            $max_id_chp = 0;
        
        if (count($rs_id_tabm )>0)
            $max_id_tabm = $rs_id_tabm[0]['MAX_ID'];
        else
            $max_id_tabm = 0;
            
        if (count($rs_ordre_in_olap )>0)
            $max_ordre_in_olap = $rs_ordre_in_olap[0]['MAX_ORDRE'];
        else
            $max_ordre_in_olap = 0;
        
        $nvl_id_dim = $max_id_dim;
        $nvl_id_chp = $max_id_chp;
        $nvl_id_tabm = $max_id_tabm;
        $nvl_ordre_in_olap = $max_ordre_in_olap;
        
        $i_dim=-1;
        $i_tabm=-1;
        $i_chp=-1;
        $i_join=-1;        
                
        $liste_champs=array();
        $liste_tables=array();
        
        $nvl_tab_tabm=array();
        $nvl_tab_champs=array();
        
        ///////////////////////////Ajout HEBIE du 27 09 06///////////////////////////////////////
        
        $tabm_de_toutes_les_dim ='(';
        $cpteur_dim=0;
        foreach($this->tab_dim as $id_dim)
        {
            $req_champ_dim ='SELECT * FROM DICO_OLAP_CHAMP WHERE ID_DIMENSION ='.$id_dim.' ORDER BY ORDRE_OLAP';            
            $res_champ_dim=$GLOBALS['conn_dico']->GetAll($req_champ_dim);
            
            $nb_tabm = count($res_champ_dim);
            $cpte=0;
            $cpteur_dim++;
            foreach($res_champ_dim as $rs)
            {   
                if($cpteur_dim==count($this->tab_dim)) $cpte++;                               
                $tabm_de_toutes_les_dim .= $rs['ID_OLAP_TABLE_MERE'];
                if($cpte < $nb_tabm) $tabm_de_toutes_les_dim .= ', ';               
            }
        }
        $tabm_de_toutes_les_dim .=')';
            
        //Recuperation des noms des tables mères associées à toutes les dimensions           
        $rq_nom_tabm='SELECT DISTINCT NOM_TABLE_MERE FROM DICO_OLAP_TABLE_MERE WHERE ID_OLAP_TABLE_MERE IN '.$tabm_de_toutes_les_dim;
        $rs_nom_tabm = $GLOBALS['conn_dico']->GetAll($rq_nom_tabm);
        
        $nbre_tabm = count($rs_nom_tabm);
        $cpteur=0;
        $nom_chps ='(';
        foreach($rs_nom_tabm as $rs)
        {   
            $cpteur++;                               
            $nom_chps .= '\''.$GLOBALS['PARAM']['CODE'].'_'.$rs['NOM_TABLE_MERE'].'\'';
            if($cpteur < $nbre_tabm) $nom_chps .= ', ';               
        }
        $nom_chps .= ', \''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'\'';
        $nom_chps .=')';

        //////////////////Fin Ajout HEBIE du 27 09 06//////////////////////////////////////
        
        foreach($this->tab_dim as $id_dim)
        {            
            $nvl_id_dim++;
            $i_dim++;
            //Selection de la dimension courante
            $req_dim ='SELECT * FROM DICO_OLAP_DIMENSION WHERE ID_DIMENSION ='.$id_dim;       
            $res_dim = $GLOBALS['conn_dico']->GetAll($req_dim);
            
            //Selection des champs dimension
            $req_champ_dim ='SELECT * FROM DICO_OLAP_CHAMP WHERE ID_DIMENSION ='.$id_dim.' ORDER BY ORDRE_OLAP';            
            $res_champ_dim=$GLOBALS['conn_dico']->GetAll($req_champ_dim);
            
            $nb_tabm = count($res_champ_dim);
            $cpte=0;
            $tabm ='(';
            foreach($res_champ_dim as $rs)
            {   
                $cpte++;                               
                $tabm .= $rs['ID_OLAP_TABLE_MERE'];
                if($cpte < $nb_tabm) $tabm .= ', ';               
            }
            $tabm .=')';
            
            //////////////////Mise en commentaire HEBIE du 27 09 06//////////////////////////////////////            
            /*
            //Recuperation des noms des tables mères associées à une dimension           
            $req_nom_tabm='SELECT NOM_TABLE_MERE FROM DICO_OLAP_TABLE_MERE WHERE ID_OLAP_TABLE_MERE IN '.$tabm;
            $res_nom_tabm = $GLOBALS['conn_dico']->GetAll($req_nom_tabm);
            
            $nb_tabm = count($res_nom_tabm);
            $cpte=0;
            $nom_chps ='(';
            foreach($res_nom_tabm as $rs)
            {   
                $cpte++;                               
                $nom_chps .= '\''.$GLOBALS['PARAM']['CODE'].'_'.$rs['NOM_TABLE_MERE'].'\'';
                if($cpte < $nb_tabm) $nom_chps .= ', ';               
            }
            //$nom_chps .= ', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'];
            $nom_chps .=')';
            */
            //////////////////Fin Mise en commentaire HEBIE 27 09 06//////////////////////////////////////
            
            //Selection des champs identifiants
            $req_champ_ident='SELECT * FROM DICO_OLAP_CHAMP WHERE ID_TYPE_CHAMP =3 AND ID_OLAP_TABLE_MERE IN '.$tabm.' AND NOM_CHAMP IN '.$nom_chps.' ORDER BY ORDRE_OLAP';
            
            //////////////////Modif HEBIE du 27 09 06//////////////////////////////////////
            //Selection des champs ordre
            $req_champ_ordre='SELECT * FROM DICO_OLAP_CHAMP WHERE ID_TYPE_CHAMP =4 AND ID_OLAP_TABLE_MERE IN '.$tabm.' ORDER BY ORDRE_OLAP';
            $req_champ = $req_champ_dim.' UNION '.$req_champ_ident.' UNION '.$req_champ_ordre;//Ajout de : UNION $req_champ_ordre"
            //////////////////Fin Modif HEBIE du 27 09 06//////////////////////////////////////
            
            $res_champ = $GLOBALS['conn_dico']->GetAll($req_champ);
            
            //Recuperation dans un tableau des champs liés d'une manière ou d'une autre à la dimension
            $ls_champs=array();
            foreach($res_champ as $rs)
            {
                $ls_champs[]=$rs['ID_CHAMP'];
            }     
            
            //Selection des champs mesures
            $req_champ_mes ='SELECT * FROM DICO_OLAP_CHAMP WHERE ID_TYPE_CHAMP = 1 AND ID_OLAP = '.$res_dim[0]['ID_OLAP'].' ORDER BY ORDRE_OLAP';            
            $res_champ_mes=$GLOBALS['conn_dico']->GetAll($req_champ_mes);
            
            $nb_tabm = count($res_champ_mes);
            $cpte=0;
            
            if(is_array($res_champ_mes) && count($res_champ_mes))
            {
                $tabm ='(';
                foreach($res_champ_mes as $rs)
                {   
                    $cpte++;                               
                    $tabm .= $rs['ID_OLAP_TABLE_MERE'];
                    if($cpte < $nb_tabm) $tabm .= ', ';               
                }
                $tabm .=')';
                
                //Recuperation des champs identifiants rattachés aux tables de mesures           
                $req_champs='SELECT ID_CHAMP FROM DICO_OLAP_CHAMP WHERE ID_TYPE_CHAMP = 3 AND ID_OLAP_TABLE_MERE IN '.$tabm;
                $res_champs = $GLOBALS['conn_dico']->GetAll($req_champs);
            }
                
            $ls_chps_tab_mes =array();            
            if(is_array($res_champs) && count($res_champs))
            {
                foreach($res_champs as $rs)
                {   
                    $ls_chps_tab_mes[] = $rs['ID_CHAMP'];
                }
            }                
            
            //Selection des champs identifiants à partir des jointures
            
            $req_chp_ident_join = 'SELECT * FROM DICO_OLAP_JOINTURES WHERE ID_OLAP = '.$res_dim[0]['ID_OLAP'];        
            $res_chp_ident_join = $GLOBALS['conn_dico']->GetAll($req_chp_ident_join);      
            
            foreach($res_chp_ident_join as $rs)
            {
                if((in_array($rs['ID_CHAMP_1'],$ls_champs) && !in_array($rs['ID_CHAMP_2'],$ls_champs) && !in_array($rs['ID_CHAMP_2'],$ls_chps_tab_mes)) || (in_array($rs['ID_CHAMP_2'],$ls_champs) && !in_array($rs['ID_CHAMP_1'],$ls_champs) && !in_array($rs['ID_CHAMP_1'],$ls_chps_tab_mes)))
                {
                    if(in_array($rs['ID_CHAMP_1'],$ls_champs) && !in_array($rs['ID_CHAMP_2'],$ls_champs) && !in_array($rs['ID_CHAMP_2'],$ls_chps_tab_mes))
                    {   
                        ////Ajout HEBIE du 28 09 06////////////////////////////
                        
                        $req_chp = 'SELECT NOM_CHAMP FROM DICO_OLAP_CHAMP WHERE ID_CHAMP = '.$rs['ID_CHAMP_2'];        
                        $res_chp = $GLOBALS['conn_dico']->GetAll($req_chp);
                        $req_tabm = 'SELECT NOM_TABLE_MERE FROM DICO_OLAP_TABLE_MERE WHERE ID_OLAP_TABLE_MERE = '.$rs['ID_OLAP_TABLE_MERE_2'];        
                        $res_tabm = $GLOBALS['conn_dico']->GetAll($req_tabm);                        
                        $tab_chps_atlas = array();
                        $tab_chps_atlas[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
                        $tab_chps_atlas[] = $GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'];
                        $tab_tabm_atlas = array();
                        $tab_tabm_atlas[] = $GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'];
                        
                        if(in_array($res_chp[0]['NOM_CHAMP'],$tab_chps_atlas) || in_array($res_tabm[0]['NOM_TABLE_MERE'],$tab_tabm_atlas))
                            $ls_champs[]=$rs['ID_CHAMP_2'];
                        
                        ////Fin Ajout HEBIE du 28 09 06////////////////////////////
                    }
                    if(in_array($rs['ID_CHAMP_2'],$ls_champs) && !in_array($rs['ID_CHAMP_1'],$ls_champs) && !in_array($rs['ID_CHAMP_1'],$ls_chps_tab_mes))
                    {
                        ////Ajout HEBIE du 28 09 06////////////////////////////
                        
                        $req_chp = 'SELECT NOM_CHAMP FROM DICO_OLAP_CHAMP WHERE ID_CHAMP = '.$rs['ID_CHAMP_1'];        
                        $res_chp = $GLOBALS['conn_dico']->GetAll($req_chp);
                        $req_tabm = 'SELECT NOM_TABLE_MERE FROM DICO_OLAP_TABLE_MERE WHERE ID_OLAP_TABLE_MERE = '.$rs['ID_OLAP_TABLE_MERE_1'];        
                        $res_tabm = $GLOBALS['conn_dico']->GetAll($req_tabm);                        
                        $tab_chps_atlas = array();
                        $tab_chps_atlas[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
                        $tab_chps_atlas[] = $GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'];
                        $tab_tabm_atlas = array();
                        $tab_tabm_atlas[] = $GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'];
                        
                        if(in_array($res_chp[0]['NOM_CHAMP'],$tab_chps_atlas) || in_array($res_tabm[0]['NOM_TABLE_MERE'],$tab_tabm_atlas))
                            $ls_champs[]=$rs['ID_CHAMP_1'];
                        
                        ////Fin Ajout HEBIE du 28 09 06////////////////////////////
                    }
                }
            }
            
            //Données d'une occurence de dimension
            $this->liste_dim[$i_dim]['ID_DIMENSION']=$nvl_id_dim;
            $this->liste_dim[$i_dim]['ID_OLAP']=$this->id_olap;
            $this->liste_dim[$i_dim]['LIBELLE_DIMENSION']=$res_dim[0]['LIBELLE_DIMENSION'];
            $this->liste_dim[$i_dim]['LIBELLE_ENTETE_DIM']=$res_dim[0]['LIBELLE_ENTETE_DIM'];
            $this->liste_dim[$i_dim]['ORDRE']=$res_dim[0]['ORDRE'];
            
            //Selection des tables mères utilisées au compte de la dimension
            $nb_chp = count($ls_champs);
            $cpte=0;
            $champs ='(';
            foreach($ls_champs as $chp)
            {                                                  
                $cpte++;
                $champs .= $chp;                
                if($cpte < $nb_chp) $champs .= ', ';               
            }
            $champs .=')';
            $req_tabm = 'SELECT DISTINCT(ID_OLAP_TABLE_MERE) FROM DICO_OLAP_CHAMP WHERE ID_CHAMP IN '.$champs;
            $res_tabm = $GLOBALS['conn_dico']->GetAll($req_tabm);            
            $nb_tabm = count($res_tabm);
            $cpte=0;
            $tabm ='(';
            foreach($res_tabm as $rs)
            {   
                $cpte++;                               
                $tabm .= $rs['ID_OLAP_TABLE_MERE'];
                if($cpte < $nb_tabm) $tabm .= ', ';               
            }
            $tabm .=')';
            $req_tabm = 'SELECT * FROM DICO_OLAP_TABLE_MERE WHERE ID_OLAP_TABLE_MERE IN '.$tabm;
            $res_tabm = $GLOBALS['conn_dico']->GetAll($req_tabm);                      
            foreach($res_tabm as $rs)
            {
                $nvl_id_tabm++;
                $i_tabm++;
                $liste_tables[]=$rs['ID_OLAP_TABLE_MERE'];  
                $nvl_tab_tabm[$rs['ID_OLAP_TABLE_MERE']]=$nvl_id_tabm;//Correspondance ancien ID_OLAP_TABLE_MERE <-> nouvel ID_OLAP_TABLE_MERE
                $this->liste_tabm[$i_tabm]['ID_OLAP_TABLE_MERE']=$nvl_id_tabm;
                $this->liste_tabm[$i_tabm]['ID_OLAP']=$this->id_olap;
                $this->liste_tabm[$i_tabm]['NOM_TABLE_MERE']=$rs['NOM_TABLE_MERE'];
                $this->liste_tabm[$i_tabm]['NOM_ALIAS']=$rs['NOM_ALIAS'];
                $this->liste_tabm[$i_tabm]['IS_FICTIF']=$rs['IS_FICTIF'];                
            }   
            
            //Données champs rattachés à une occurence de dimension 
            $req_champ='SELECT * FROM DICO_OLAP_CHAMP WHERE ID_CHAMP IN '.$champs.' ORDER BY ORDRE_OLAP';
            $res_champ = $GLOBALS['conn_dico']->GetAll($req_champ);                       
            foreach($res_champ as $rs)
            {
                $nvl_id_chp++;
                $nvl_ordre_in_olap++;
                $i_chp++;
                $liste_champs[]=$rs['ID_CHAMP'];
                $nvl_tab_champs[$rs['ID_CHAMP']]=$nvl_id_chp;//Correspondance ancien ID_CHAMP <-> nouvel ID_CHAMP
                $this->liste_chps[$i_chp]['ID_CHAMP']=$nvl_id_chp;
                $this->liste_chps[$i_chp]['ID_OLAP']=$this->id_olap;
                if($rs['ID_TYPE_CHAMP']==3)
                    $this->liste_chps[$i_chp]['ID_DIMENSION']=null;
                else
                    $this->liste_chps[$i_chp]['ID_DIMENSION']=$nvl_id_dim;
                $this->liste_chps[$i_chp]['ID_TYPE_CHAMP']=$rs['ID_TYPE_CHAMP'];
                $this->liste_chps[$i_chp]['ID_OLAP_TABLE_MERE']=$nvl_tab_tabm[$rs['ID_OLAP_TABLE_MERE']];
                $this->liste_chps[$i_chp]['NOM_CHAMP']=$rs['NOM_CHAMP'];
                $this->liste_chps[$i_chp]['FONCTION']=$rs['FONCTION'];
                $this->liste_chps[$i_chp]['EXPRESSION']=$rs['EXPRESSION'];            
                $this->liste_chps[$i_chp]['ALIAS']=$rs['ALIAS'];
                $this->liste_chps[$i_chp]['FORMAT']=$rs['FORMAT'];
                $this->liste_chps[$i_chp]['ORDRE_OLAP']=$nvl_ordre_in_olap;
                $this->liste_chps[$i_chp]['ORDRE_DIM']=$rs['ORDRE_DIM'];
				$this->liste_chps[$i_chp]['ALL_LEVEL']=$rs['ALL_LEVEL'];
				              
            }
                 
        }
        
        //Jointures rattachées à la liste de dimension
        $req_join = 'SELECT * FROM DICO_OLAP_JOINTURES WHERE ID_OLAP = '.$res_dim[0]['ID_OLAP'];        
        $res_join = $GLOBALS['conn_dico']->GetAll($req_join);  
        foreach($res_join as $rs)
        {
            if(in_array($rs['ID_OLAP_TABLE_MERE_1'],$liste_tables) && in_array($rs['ID_OLAP_TABLE_MERE_2'],$liste_tables) && in_array($rs['ID_CHAMP_1'],$liste_champs) && in_array($rs['ID_CHAMP_2'],$liste_champs))
            {
                $i_join++;                
                $this->liste_join[$i_join]['ID_OLAP_TABLE_MERE_1']=$nvl_tab_tabm[$rs['ID_OLAP_TABLE_MERE_1']];
                $this->liste_join[$i_join]['ID_CHAMP_1']=$nvl_tab_champs[$rs['ID_CHAMP_1']];
                $this->liste_join[$i_join]['ID_OLAP_TABLE_MERE_2']=$nvl_tab_tabm[$rs['ID_OLAP_TABLE_MERE_2']];
                $this->liste_join[$i_join]['ID_CHAMP_2']=$nvl_tab_champs[$rs['ID_CHAMP_2']];
                $this->liste_join[$i_join]['ID_OLAP']=$this->id_olap;
                $this->liste_join[$i_join]['OPERATEUR_JOINTURE']=$rs['OPERATEUR_JOINTURE'];
                $this->liste_join[$i_join]['NOM_JOINTURE']=$rs['NOM_JOINTURE'];                               
            }        
        }
        
    }
    
    function maj_base_olap()
    {
        //Insertion dans la table DICO_OLAP_DIMENSION
        if(count($this->liste_dim))
        {
            foreach($this->liste_dim as $dim)
            {
                $req_insert='INSERT INTO DICO_OLAP_DIMENSION VALUES ('.$dim['ID_DIMENSION'].', '.$dim['ID_OLAP'].', \''.$dim['LIBELLE_DIMENSION'].'\', \''.$dim['LIBELLE_ENTETE_DIM'].'\', '.$dim['ORDRE'].')';
                $GLOBALS['conn_dico']->Execute($req_insert);
            }   
        }            
       
        //Insertion dans la table DICO_OLAP_TABLE_MERE
        if(count($this->liste_tabm))
        {
            foreach($this->liste_tabm as $tabm)
            {
                $req_insert='INSERT INTO DICO_OLAP_TABLE_MERE (ID_OLAP_TABLE_MERE,ID_OLAP,NOM_TABLE_MERE,NOM_ALIAS) VALUES ('.$tabm['ID_OLAP_TABLE_MERE'].', '.$tabm['ID_OLAP'].', \''.$tabm['NOM_TABLE_MERE'].'\', \''.$tabm['NOM_ALIAS'].'\')';
                $GLOBALS['conn_dico']->Execute($req_insert);
            }
        }            
        
        //Insertion dans la table DICO_OLAP_CHAMP
        if(count($this->liste_chps))
        {
            foreach($this->liste_chps as $chp)
            {
                if($chp['ID_DIMENSION'] <> null)
                    if($chp['ORDRE_DIM']==null)
                        $req_insert='INSERT INTO DICO_OLAP_CHAMP (ID_CHAMP,ID_OLAP,ID_DIMENSION,ID_TYPE_CHAMP,ID_OLAP_TABLE_MERE,NOM_CHAMP,FONCTION,EXPRESSION,ALIAS,FORMAT,ORDRE_OLAP,ALL_LEVEL) 
                                    VALUES ('.$chp['ID_CHAMP'].', '.$chp['ID_OLAP'].', '.$chp['ID_DIMENSION'].', '.$chp['ID_TYPE_CHAMP'].', '.$chp['ID_OLAP_TABLE_MERE'].', \''.$chp['NOM_CHAMP'].'\', \''.$chp['FONCTION'].'\', \''.$chp['EXPRESSION'].'\', \''.$chp['ALIAS'].'\', \''.$chp['FORMAT'].'\', '.$chp['ORDRE_OLAP'].', '.$chp['ALL_LEVEL'].')';
                    else
                        $req_insert='INSERT INTO DICO_OLAP_CHAMP (ID_CHAMP,ID_OLAP,ID_DIMENSION,ID_TYPE_CHAMP,ID_OLAP_TABLE_MERE,NOM_CHAMP,FONCTION,EXPRESSION,ALIAS,FORMAT,ORDRE_OLAP,ORDRE_DIM,ALL_LEVEL) 
                                    VALUES ('.$chp['ID_CHAMP'].', '.$chp['ID_OLAP'].', '.$chp['ID_DIMENSION'].', '.$chp['ID_TYPE_CHAMP'].', '.$chp['ID_OLAP_TABLE_MERE'].', \''.$chp['NOM_CHAMP'].'\', \''.$chp['FONCTION'].'\', \''.$chp['EXPRESSION'].'\', \''.$chp['ALIAS'].'\', \''.$chp['FORMAT'].'\', '.$chp['ORDRE_OLAP'].', '.$chp['ORDRE_DIM'].', '.$chp['ALL_LEVEL'].')';
                else
                    if($chp['ORDRE_DIM']==null)
                        $req_insert='INSERT INTO DICO_OLAP_CHAMP (ID_CHAMP,ID_OLAP,ID_TYPE_CHAMP,ID_OLAP_TABLE_MERE,NOM_CHAMP,FONCTION,EXPRESSION,ALIAS,FORMAT,ORDRE_OLAP) 
                                    VALUES ('.$chp['ID_CHAMP'].', '.$chp['ID_OLAP'].', '.$chp['ID_TYPE_CHAMP'].', '.$chp['ID_OLAP_TABLE_MERE'].', \''.$chp['NOM_CHAMP'].'\', \''.$chp['FONCTION'].'\', \''.$chp['EXPRESSION'].'\', \''.$chp['ALIAS'].'\', \''.$chp['FORMAT'].'\', '.$chp['ORDRE_OLAP'].')';
                    else
                        $req_insert='INSERT INTO DICO_OLAP_CHAMP (ID_CHAMP,ID_OLAP,ID_TYPE_CHAMP,ID_OLAP_TABLE_MERE,NOM_CHAMP,FONCTION,EXPRESSION,ALIAS,FORMAT,ORDRE_OLAP,ORDRE_DIM) 
                        VALUES ('.$chp['ID_CHAMP'].', '.$chp['ID_OLAP'].', '.$chp['ID_TYPE_CHAMP'].', '.$chp['ID_OLAP_TABLE_MERE'].', \''.$chp['NOM_CHAMP'].'\', \''.$chp['FONCTION'].'\', \''.$chp['EXPRESSION'].'\', \''.$chp['ALIAS'].'\', \''.$chp['FORMAT'].'\', '.$chp['ORDRE_OLAP'].', '.$chp['ORDRE_DIM'].')';
                
                $GLOBALS['conn_dico']->Execute($req_insert);
            }
        }            
       
        //Insertion dans la table DICO_OLAP_JOINTURES
        if(count($this->liste_join))
        {
            foreach($this->liste_join as $join)
            {
                $req_insert='INSERT INTO DICO_OLAP_JOINTURES VALUES ('.$join['ID_OLAP_TABLE_MERE_1'].', '.$join['ID_CHAMP_1'].', '.$join['ID_OLAP_TABLE_MERE_2'].', '.$join['ID_CHAMP_2'].', '.$join['ID_OLAP'].', \''.$join['OPERATEUR_JOINTURE'].'\', \''.$join['NOM_JOINTURE'].'\')';
                $GLOBALS['conn_dico']->Execute($req_insert);
            } 
        }            
    }
}
?>
