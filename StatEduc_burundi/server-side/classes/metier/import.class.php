<?php
class import {
		
        /**	
         * Attribut : conn
         * Base de données
         * @var adodb.connection
         * @access private
	    */  
		public $conn; // à la base de départ
		
		/**
		* Attribut : liste_tables_ord
        * liste des tables de la base ordonnées selon les contraintes d'intégrité référentielle
		* @var array
		* @access public
		*/   
		public $liste_tables_ord = array(); //liste des tables ordonnées suivant les contraintes d'intégrité référentielle
		
		/**
		* Attribut : 
        * nom du repertoire contenant les fichiers de données à intégrer dans la base
		* @var 
		* @access public
		*/   
		public $dossier_xml = ''; //répertoire de stockage des fichiers xml
		
		/**
		* Attribut : with_nomenc
        * variable permettant d'inclure les données provenant des tables de nomenclature
		* @var boolean
		* @access public
		*/   
		public $with_nomenc = false; // Transfert des tables de nomenclatures
		
		/**
		* Attribut : 
		*  pointeur du fichier de log
		* @var mixed
		* @access public
		*/   
		public $fp ; 
		
		/**
		* Attribut : 
        * chemin d'accès du fichier de log
		* @var string
		* @access public
		*/   
		public $chemin_log  ;
		
		public $schema_table = array();
		
		public $cles_tables = array();
		public $tab_theme_ordre;
    
    /**
     * Données de log pour les appels ajax
     */         
    public $log_data;
	
    /**
     * Vrai si l'import est fait depuis une requete ajax
     */         
    public $is_ajax;
    
    /**
     * Indique la ligne de log est pair ou non
     */          
    public $log_line_pair = true;	
    
    /**
     * Indique le code index des logs en cours de traitement
     */          
    public $log_idx;
    
		/**
		* Contructeur de la classe
		* @access public
		* @param string chemin_zip chemin absolue de fichier avec les données à importer
        * @param boolean nomencl Inclsusion des données de nomenclature
        * @param string log_idx numéro d'index du fichier de trace  
		*/
		function __construct($chemin_zip, $nomencl,$log_idx, $ajax=false){
			$this->conn 				= $GLOBALS['conn'];
			$this->tab_theme_ordre = array();
			$this->dossier_xml 	= preg_replace('/(.*)\/([[:alnum:]]|_)*(\.zip)$/','\\1/',$chemin_zip);
            if (isset($nomencl)){
                $this->with_nomenc = $nomencl;
            }
			$this->log_idx = $log_idx;
            $this->chemin_log = dirname($chemin_zip).'/_log_'.basename($chemin_zip,".zip").'_'.$log_idx.'.log';
			$this->is_ajax = $ajax;     
			if (!$ajax) debut_popup_progress();
				$this->create_log_file();
				$this->import_xml(); // appel de la fonction import_xml();            
				$this->close_log_file();
			if (!$ajax) fin_popup_progress();
		}
	
	
		/**
		* Ordonnancement les tables ds la variables $this->liste_tables_ord en fonction des contraintes 
		* @access public
		*/
		function set_liste_tables_ord(){
        $this->liste_tables_ord =array();
        $table_base = array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
        $nb_table_ord = 0;
        
        while ($nb_table_ord <= count($table_base)){
            foreach($table_base as $table){
                if (strtoupper(substr($table,0,5)) != 'DICO_' && strtoupper($table) != 'ADMIN_DROITS' && strtoupper($table) != 'ADMIN_GROUPES' && strtoupper($table) != 'ADMIN_USERS' && strtoupper($table) != 'PARAM_DEFAUT' ){
                        $foreignKeys = $this->conn->MetaForeignKeys($table,true,true);
                        
						if (is_array($foreignKeys)){
                            $cpt_trouve = 0;
                            foreach($foreignKeys as $key => $val){
                                if (is_null($this->liste_tables_ord[$key]) == false){
                                    $cpt_trouve += 1;    
                                }
                            }
                            if ($cpt_trouve == count($foreignKeys)){
                                if (is_null($this->liste_tables_ord[$table])){ 
                                    $this->liste_tables_ord[$table]  = $nb_table_ord ;
                                    $nb_table_ord += 1;
                                }
                            }
                        }else{
                            if (is_null($this->liste_tables_ord[$table])){ 
                                $this->liste_tables_ord[$table]  = $nb_table_ord ;
                                $nb_table_ord += 1;
                            }
                        }
                }else{
                    $nb_table_ord += 1;
                }
            }
        }
        $this->liste_tables_ord = array_flip($this->liste_tables_ord);
    }
    
	// Ajout HEBIE : Remplissage automatique de la table DICO_TABLE_ORDRE
	function set_liste_tables_ord_simple(){
		$list_exp_sectors = array();
		foreach($_SESSION['tab_secteur'] as $i => $sect){
			$list_exp_sectors[] = $sect[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] ;
		}		
		foreach( $list_exp_sectors as $i => $secteur ){
			if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>"")
				$this->ordonner_theme_appart($secteur);
			else
				$this->ordonner_theme($secteur);
			$this->ordonner_table();
			$requete='SELECT NOM_TABLE FROM DICO_TABLE_ORDRE ORDER BY ORDRE';
			$liste_table    =   $GLOBALS['conn_dico']->GetAll($requete);
			if (is_array($liste_table)){
				foreach($liste_table as $table){
					if(!in_array($table['NOM_TABLE'], $this->liste_tables_ord)){
						$this->liste_tables_ord[]=$table['NOM_TABLE'];
					}
				}
			}
		}
	}
	//Fin ajout HEBIE
    /**
    * Ordonnancement les tables ds la variables $this->liste_tables_ord en fonction des contraintes 
    * @access public
    */
    // Ajout Alassane
    function set_liste_tables_ord_bis(){
         // ordonner les tables ds la variables $this->liste_tables_ord
         // en fonction des contraintes
     
        $this->liste_tables_ord =array();
        $table_base = array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));   
        $table_base_courant =   array();        
        
        // Identification de la liste des tables sans contraintes
        foreach($table_base as $table){
            if (strtoupper(substr($table,0,5)) != 'DICO_' && strtoupper($table) != 'ADMIN_DROITS' && strtoupper($table) != 'ADMIN_GROUPES' && strtoupper($table) != 'ADMIN_USERS' && strtoupper($table) != 'PARAM_DEFAUT' ){
                    $foreignKeys = $this->conn->MetaForeignKeys($table,true,true);
                    if (is_array($foreignKeys)){
                        $table_base_courant[] =$table;
                    }else{
                        // A cette condition la table est sans contraintes
                         //$nb_table_ord +=1;                        
                        $this->liste_tables_ord[]=$table;
                    }  
            }
        }
      
        //$this->liste_tables_ord contient la liste des tables sans contraintes
        //$table_base_courant contient la liste des tables restantes à ordonner
        $table_base =   $table_base_courant;
        
        while (!is_array($table_base)){
            // On fait la boucle tant que la liste n'est pas vide             
            $table_base_courant = array();
            foreach($table_base as $table){
                // on vérifie pour la table encours si la liste de ces tables référencées sont présentes dans la liste               
                // il faut initialiser la liste des table ref               
                $liste_table_ref    =   array();
                $foreignKeys = $this->conn->MetaForeignKeys($table,true,true);
                foreach($foreignKeys as $key => $val){
                      $liste_table_ref []=$key;
                }
                if (array_intersect($liste_table_ref,$this->liste_tables_ord)==count($liste_table_ref)){
                    // Dans cette condition la liste des tables reférencées sont presentes dans la liste
                    $this->liste_tables_ord[]=$table;
                }else{
                     $table_base_courant[]  =   $table;
                }        
            }
            // On réinitialise la  liste pour recommencer la boucle
            $table_base =   $table_base_courant;      
        }   
    }
	
	function ordonner_theme($secteur){
		$requete = "SELECT D_T_S.ID_THEME_SYSTEME, D_T.ID_TYPE_THEME, D_T_S.ID, D_T_S.PERE, D_T_S.PRECEDENT
					FROM DICO_THEME_SYSTEME AS D_T_S, DICO_THEME AS D_T
					WHERE D_T.ID = D_T_S.ID
					AND  D_T_S.ID_SYSTEME=".$secteur;

		$result_theme	= $GLOBALS['conn_dico']->GetAll($requete);
		
		// tri de la table selon les precedences
		$result_theme   =   $GLOBALS['theme_manager']->tri_list($result_theme); 
		
		foreach($result_theme as $thm){
			$this->tab_theme_ordre[] = $thm;
		}			
	}
	 
	function ordonner_theme_appart($secteur){
		$critere_appart = "";
		$req_appart = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'<>255';
		$rs_appart= $GLOBALS['conn']->GetAll($req_appart);
		foreach ($rs_appart as $appart){
			$critere_appart = ' AND  D_T_S.APPARTENANCE='.$appart[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']];
			
			$requete = "SELECT D_T_S.ID_THEME_SYSTEME, D_T.ID_TYPE_THEME, D_T_S.ID, D_T_S.PERE, D_T_S.PRECEDENT
					FROM DICO_THEME_SYSTEME AS D_T_S, DICO_THEME AS D_T
					WHERE D_T.ID = D_T_S.ID
					AND  D_T_S.ID_SYSTEME=".$secteur.$critere_appart;

			$result_theme	= $GLOBALS['conn_dico']->GetAll($requete);
			
			// tri de la table selon les precedences
			$result_theme   =   $GLOBALS['theme_manager']->tri_list($result_theme); 
			
			foreach($result_theme as $thm){
				$this->tab_theme_ordre[] = $thm;
			}			
		}
  	}
	function ordonner_table(){
		$GLOBALS['conn_dico']->Execute("DELETE FROM  DICO_TABLE_ORDRE");
		$cpt = 3;
		foreach ($this->tab_theme_ordre as $thm_ord){
			if ($thm_ord['ID_TYPE_THEME'] <> 1){
				$strRequete = "SELECT * FROM DICO_TABLE_MERE_THEME WHERE ID_THEME=" . $thm_ord['ID'] . " ORDER BY PRIORITE";
			}else{
				$strRequete = "SELECT ID_THEME,ID_TABLE_MERE_THEME ,TABLE_MERE AS NOM_TABLE_MERE,PRIORITE FROM DICO_ZONE WHERE ID_THEME=" . $thm_ord['ID'] . " ORDER BY PRIORITE";
			}
			$rsTable= $GLOBALS['conn_dico']->Execute($strRequete);
			if ($rsTable->RecordCount()>0){
				while (!$rsTable->EOF){
					//if (trim($rsTable->fields['NOM_TABLE_MERE']) <> $GLOBALS['PARAM']['ENSEIGNANT']){
						$strRequete = "SELECT * FROM DICO_TABLE_ORDRE WHERE NOM_TABLE='" . trim($rsTable->fields['NOM_TABLE_MERE']) . "'";
						$rsTrouve=$GLOBALS['conn_dico']->Execute($strRequete);
						if ($rsTrouve->RecordCount()==0){
							if ($thm_ord['ID_TYPE_THEME'] <> 1){
								$res=$GLOBALS['conn_dico']->Execute("INSERT INTO DICO_TABLE_ORDRE (ID_TABLE_ORDRE,NOM_TABLE,ORDRE) VALUES (" . $rsTable->fields['ID_TABLE_MERE_THEME'] . ",'" . $rsTable->fields['NOM_TABLE_MERE'] . "'," . $cpt . ")");
							}else{
								$res=$GLOBALS['conn_dico']->Execute ("INSERT INTO DICO_TABLE_ORDRE (ID_TABLE_ORDRE,NOM_TABLE,ORDRE) VALUES (" . ($cpt * 100) . ",'" . $rsTable->fields['NOM_TABLE_MERE'] . "'," . $cpt . ")");
							}
						}
						$rsTrouve->Close();
				   //}
					$rsTable->MoveNext();
				   $cpt = $cpt + 1;
				}
			}
		}
	}
	
	function get_cles_tables(){
		if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
		if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
		if(!isset($_SESSION['code_etab']) || $_SESSION['code_etab']==''){
			$_SESSION['code_etab'] = 0;
		}
		$code_etablissement = $_SESSION['code_etab'];
		$code_annee = $_SESSION['annee'];
		$code_filtre = $_SESSION['filtre'];
		$id_systeme	= $_SESSION['secteur'];
		$themes_saisie = '';
		$requete  = "SELECT DICO_THEME.* 
					 FROM DICO_THEME, DICO_THEME_SYSTEME 
					 WHERE DICO_THEME.ID = DICO_THEME_SYSTEME.ID AND DICO_THEME_SYSTEME.ID_SYSTEME = $id_systeme AND DICO_THEME.ID_TYPE_THEME NOT IN (8)".";";
		$result_themes	= $GLOBALS['conn_dico']->GetAll($requete);
		$tab_nom_tables = array();
		foreach($result_themes as $theme){
			$curr_inst	= $theme['ACTION_THEME'];									
			$id_theme =	$theme['ID'];    
			if (file_exists($GLOBALS['SISED_PATH_CLS'] . 'metier/'.$theme['CLASSE'].'.class.php')) {
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/'.$theme['CLASSE'].'.class.php'; 
			}
			switch($curr_inst){
				case 'instance_grille.php' :{
						// Instanciation de la classe
						$curobj_grille		=	new grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
						// chargement des codes des nomenclatures des champs de type matrice
						//$curobj_grille->set_code_nomenclature();
						$_SESSION['curobj_theme'] = $curobj_grille;
						break;
				}	
				case 'instance_mat_grille.php' :{
						// Instanciation de la classe
						$curobj_matgrille = new mat_grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
						// chargement des codes des nomenclatures des champs de type matrice
						$curobj_matgrille->set_code_nomenclature();
						// Récupération des différents champs
						$curobj_matgrille->set_champs();
						// Sauvegarde de l'objet en session
						$_SESSION['curobj_theme'] = $curobj_matgrille;
						break;
				}
				case 'instance_matrice.php' :{
						// Instanciation de la classe
						$curobj_matrice	=	new matrice($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
						$_SESSION['curobj_theme'] = $curobj_matrice;
						break;
				}
			}
			//echo "curobj_theme<pre>";
			//print_r($_SESSION['curobj_theme']);
			if((isset($_SESSION['curobj_theme']->type_theme)) && ($_SESSION['curobj_theme']->type_theme<>'') && (!isset($_SESSION['curobj_theme']->type_matrice))){
				foreach($_SESSION['curobj_theme']->tableau_zone_saisie as $nom_tab => $tab){
					if(!in_array($nom_tab,$tab_nom_tables)){
						$tab_nom_tables[] = $nom_tab;
						foreach($tab as $chp){
							if((isset($chp['type'])) && ($chp['type']=='c' || $chp['type']=='li_ch' || $chp['type']=='loc_etab'
								 || $chp['type']=='csu' || $chp['type']=='cmm' || $chp['type']=='cmu' 
								 || $chp['type']=='sco' || $chp['type']=='sys_txt' || $chp['type']=='dim_lig'  
								 || $chp['type']=='dim_col') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
								$this->cles_tables[$nom_tab]['keys_fields'][] = $chp['champ'];
							}
						}
					}
				}
			}elseif(isset($_SESSION['curobj_theme']->type_matrice) && $_SESSION['curobj_theme']->type_matrice<>''){
				if(!in_array($_SESSION['curobj_theme']->nomtableliee,$tab_nom_tables)){
					$tab_nom_tables[] = $_SESSION['curobj_theme']->nomtableliee;
					foreach($_SESSION['curobj_theme']->val_cle as $cle){
						$this->cles_tables[$_SESSION['curobj_theme']->nomtableliee]['keys_fields'][] = $cle[0];
					}
					foreach($_SESSION['curobj_theme']->dimensions as $dim){
						$this->cles_tables[$_SESSION['curobj_theme']->nomtableliee]['keys_fields'][] = $dim;
					}
				}
			}
		}
	}	
		
		/**
		* Renvoie le schema dd'une table 
		* @access public
        * @param string table nom d'une table de la base
        * @param string $file chemin d'accès de fichier zip contenant les données à importer
		* 
		*/
    function get_xsd_schema($table,$file){ 
        $nom_fic_xsd = dirname($this->dossier_xml).'\\'.basename ($file,".zip").'\\'.$table.'.xsd';
		// récupération de la structure d'une table donnée à partir du fichier .xsd
		$xsd_schema_table =	array();
		// $xsd_schema  sous la forme
		// nom de la table
		// nom de chaque champ et type de chaque champ
        if (file_exists($nom_fic_xsd )){
			$doc_xml = new DomDocument();
            $doc_xml->load($nom_fic_xsd);
			$racine=$doc_xml->getElementsByTagName('schema')->item(0);
			$complexType=$racine->getElementsByTagName('complexType')->item(0);
			$sequence=$complexType->getElementsByTagName('sequence')->item(0);
			$elements=$sequence->getElementsByTagName('element');
			unset($this->schema_table);
			if (is_object($elements)){
				foreach($elements as $element){
					if ($element->nodeType == 1 && $element->nodeName =='element') {
						$xsd_schema_table [$element->getAttribute('name')]=$element->getAttribute('type');
						$this->schema_table[] = $element->getAttribute('name');
					}
				}
			}
		}
        return $xsd_schema_table;
	}
	
	
		/**
		* Importation des données dans la base en cours à paritr de fichiers compressés contenus dans un répertoire spécifié
		* @access public
		* 
		*/
		function import_xml(){
            // Ajout Alassane
            switch ($this->conn->databaseType)
            {              
                case 'mssql':
                        //$this->set_liste_tables_ord();   //TODO Changer la fonction set_liste_tables_ord par set_liste_tables_ord_bis                           
                        $this->set_liste_tables_ord_simple(); 
						break;
                default:
                        $this->set_liste_tables_ord_simple(); // cas de MS Access
                        break;
            }
			// appel de la fonction set_liste_tables_ord(); 
			// Pour chaque élément $table de $this->liste_tables_ord faire :
			if (is_dir(dirname($this->dossier_xml))){
				if ($dh = opendir(dirname($this->dossier_xml))) {
					//recuperation des cles des tables de donnees
					$this->get_cles_tables();
					//Fin recuperation des cles des tables de donnees
					while (($file = readdir($dh)) !== false) {
						if (substr($file,-3) == 'zip'){
							$nom_file = dirname($this->dossier_xml).'\\'.$file;
							if($this->valid_zip_import($nom_file)){
                                foreach($this->liste_tables_ord as $table){
									$file_xml = dirname($this->dossier_xml).'\\'.basename ($file,".zip").'\\'.$table . '.xml';
									$file_xsd = dirname($this->dossier_xml).'\\' .basename ($file,".zip").'\\'.$table . '.xsd';
									if(file_exists($file_xml)){
										//echo 'traitement de '.$table.'<br>';
										$this->get_xml_data($table,$file);
									   // Suppresssion des fichiers xml et xsd issus de la décompression
									   unlink($file_xml);
									   if (file_exists( $file_xsd )){
											unlink($file_xsd);
									   }
									}   
                                }
                            }
							if (is_dir(dirname($this->dossier_xml).'\\' .basename ($file,".zip")))
								rmdir(dirname($this->dossier_xml).'\\' .basename ($file,".zip")) ;
						}
                    }
                    closedir($dh);
                }
            }
	}
	
	
		/**
		* Récupération des données d'une table spécifique
		* @access public
        * @param string table nom d'une table de la base
        * @param string $file chemin d'accès de fichier zip contenant les données à importer
		*/
		function get_xml_data($table,$file){
			// nom fichier xml = $dossier_xml / nom_table.xml
			// nom fichier xsd = $dossier_xml / nom_table.xsd
			// vérifier l'existence  des fichiers fichier xml et xsd
			// Si affirmatif
			$nom_fic_xml = dirname($this->dossier_xml).'\\'.basename ($file,".zip").'\\'.$table.'.xml';
			if (file_exists($nom_fic_xml)){
                $xsd_schema = $this->get_xsd_schema($table,$file);               
				$doc_xml = new DomDocument();
                $doc_xml->load($nom_fic_xml);
				$racine=$doc_xml->getElementsByTagName($table)->item(0);
				//$lignes_data=$racine->getElementsByTagName('ligne_data');
				$lignes_data=$racine->childNodes;
				if (is_object($lignes_data)){
					foreach($lignes_data as $ligne_data){
                    	if ($ligne_data->nodeType == 1 &&  $ligne_data->nodeName == 'ligne_data'){
							$cpt_chp = 0;
							$cpt_val = 0;
							if($table == $GLOBALS['PARAM']['ETABLISSEMENT']){//Pour le moment on fait uniquement une maj sur la table etablissement
								$cpt_set = 0;
								$cpt_where = 0;
							}
							//Ajout HEBIE pr Recherche des valeurs associées à $GLOBALS['PARAM']['CODE_ETABLISSEMENT'] et $GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'] pour eviter l'importation de l'historique
							$val_school = '';
							$val_curr_school = '';
							//foreach($xsd_schema as $champ => $type){
							//	if($champ==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
							//		$element=$ligne_data->getElementsByTagName($champ)->item(0);
							foreach($ligne_data->childNodes as $element){
								if($element->nodeName==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
									if ($element->nodeType == 1 &&  (trim($element->nodeValue) <> '') ){
										$val_school = $element->nodeValue;
									}
								//}elseif($champ==$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
								//	$element=$ligne_data->getElementsByTagName($champ)->item(0);
								}elseif($element->nodeName==$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
									if ($element->nodeType == 1 &&  (trim($element->nodeValue) <> '') ){
										$val_curr_school = $element->nodeValue;
									}
								}
							}
							//Fin Ajout HEBIE pr Recherche des valeurs
							//Ajout HEBIE pr eviter l'import de l'historique enseignant dans la base centrale (consolidation): risque d'attribution de l'enseignant à plusieurs etablissement dans la meme année
							if($val_curr_school=='' || ($val_school==$val_curr_school)){
								//foreach($xsd_schema as $champ => $type){
								foreach($ligne_data->childNodes as $element){
									//$element=$ligne_data->getElementsByTagName($champ)->item(0);
									if ($element->nodeType == 1 &&  (trim($element->nodeValue) <> '')){
										if ($cpt_chp == 0){
											$sql_col = $element->nodeName;
										}else{
											$sql_col .=  ', '.$element->nodeName;
										}
										$cpt_chp++;
										if ($xsd_schema[$element->nodeName]=='integer'){                                
											if($cpt_val == 0){
												$sql_val		= $element->nodeValue;
											}else{
												$sql_val 		.= ', '.$element->nodeValue;
											}
											$cpt_val++;
											if($table == $GLOBALS['PARAM']['ETABLISSEMENT']){//Pour le moment on fait uniquement une maj sur la table etablissement
												if(!in_array($element->nodeName,$this->cles_tables[$table]['keys_fields'])){	
													if(!preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($element->nodeName)) || ($element->nodeValue <> 255)){
														if($cpt_set == 0){
															$var_sql_maj	= $element->nodeName . ' = ' . $element->nodeValue;
														}else{
															$var_sql_maj	.= ', '.$element->nodeName . ' = ' . $element->nodeValue;
														}
														$cpt_set++;
													}
												}else{
													if($cpt_where == 0){
														$var_sql_maj_cles	= $element->nodeName . ' = ' . $element->nodeValue;
													}else{
														$var_sql_maj_cles	.= ' AND '.$element->nodeName . ' = ' . $element->nodeValue;
													}
													$cpt_where++;
												}
											}																		
										}else{              
										   $str_val=$element->nodeValue;
										   if($cpt_val == 0){
												$sql_val 	= $this->conn->qstr($this->maj_special_caractere($str_val)); 
										   }else{
												$sql_val 		.= ', '.$this->conn->qstr($this->maj_special_caractere($str_val));
										   }
										   $cpt_val++;
											if($table == $GLOBALS['PARAM']['ETABLISSEMENT']){//Pour le moment on fait uniquement une maj sur la table etablissement
												if(!in_array($element->nodeName,$this->cles_tables[$table]['keys_fields'])){
													if($cpt_set == 0){
														$var_sql_maj = $element->nodeName . ' = ' . $this->conn->qstr($this->maj_special_caractere($str_val));                                                                                                                                                                                                                                                      
													}else{
														$var_sql_maj 	.= ', '. $element->nodeName . ' = ' . $this->conn->qstr($this->maj_special_caractere($str_val)); 
													}
													$cpt_set++;
												}else{
													if($cpt_where == 0){
														$var_sql_maj_cles = $element->nodeName . ' = ' . $this->conn->qstr($this->maj_special_caractere($str_val));                                                                                                                                                                                                                                                      
													}else{
														$var_sql_maj_cles 	.= ' AND '. $element->nodeName . ' = ' . $this->conn->qstr($this->maj_special_caractere($str_val)); 
													}
													$cpt_where++;
												}
											}
										}
									}
								}
								if(trim($sql_col)<>''){	
									$sql_insert = 'INSERT INTO '.$table. '('.$sql_col.') VALUES ('.$sql_val.')';
									if ($this->conn->Execute($sql_insert) === false){
										$this->record_log_file($sql_insert,$table);
										if($table == $GLOBALS['PARAM']['ETABLISSEMENT']){//Pour le moment on fait uniquement une maj sur la table etablissement
											if(trim($var_sql_maj)<>''){
												$sql_update = 'UPDATE ' . $table . ' SET ' . $var_sql_maj. ' WHERE ' .$var_sql_maj_cles;
												if ($this->conn->Execute($sql_update) === false){
													//$this->record_log_file($sql_insert,$table);  
													$this->record_log_file($sql_update,$table); 
												} else {                                                         
													//$this->record_log_file($sql_update,$table, true); 
												}
											}
										}
									} else {
										$this->record_log_file($sql_insert,$table, true);
									}
								}
							}//Fin Ajout HEBIE pr eviter l'import de l'historique enseignant dans la base centrale (consolidation): risque d'attribution de l'enseignant à plusieurs etablissement dans la meme année
                        }
                    }
                }
			}
		}
    
		/**
		* Création d'un fichier de log
		* @access public
		* 
		*/
		function create_log_file(){
      		$this->log_data = "";
			$ficlog  =  $this->chemin_log;
			if (file_exists( $ficlog )){
				unlink($ficlog);
			}
			$this->fp = fopen ("$ficlog","a");
		}
    
		/**
		* Ecriture d'une requête qui n'a pas pu être ecécutée dans le fichier de log
		* @access public
		*  @param string sql requête sql non 
		*/
		function record_log_file($sql,$table,$status=false){
	        $date=date("D M j G:i:s T Y");
	        if(!$status && !in_array($table,$GLOBALS['PARAM']['TABLES_WITHOUT_LOG_IMPORT'])) {
				$chaine="$date : $sql\n";	        
	          	$ligne = fputs($this->fp,$chaine);
	        }
			if ($this->is_ajax && !in_array($table,$GLOBALS['PARAM']['TABLES_WITHOUT_LOG_IMPORT'])) {
				$this->log_data .= "<tr class='ligne_log liste_elt_";
				$this->log_data .= $this->log_line_pair?"a":"b";
				$this->log_data .= "'>".
							  "<td class='time'>$date</td><td class='sql_query'>".utf8_encode($sql)."</td><td><img class='log_";
				$this->log_data .=  $status?"ok_":"ko_";
				$this->log_data .= $this->log_idx."' src='client-side/image/b_";
				$this->log_data .=  $status?"ok":"ko";
				$this->log_data .= ".png'></td>".
							 "</tr>";
				$this->log_line_pair = !$this->log_line_pair; 
			}
	    }
    
		/**
		* Fermeture du fichier de log
		* @access public
		*/
		function close_log_file(){
	        if ($this->is_ajax) {
	          unlink($this->dossier_xml);
	          rmdir(dirname($this->dossier_xml));
	        }
	        fclose($this->fp);    
	    }
		
		/**
		* Décrompression du fichier compressé contenant les données à importer
		* @access public
        * @param stirng fichier_zip chemin complet du fichier à décompresser
		*/
		function valid_zip_import($fichier_zip){
				include_once($GLOBALS['SISED_PATH_LIB'].'pclzip.lib.php');
				$zip = new PclZip($fichier_zip);
				if (($list_files = $zip->listContent()) == 0) {
						return false;
				}
				foreach($list_files as $i => $file){
						if( !preg_match('/^([[:alnum:]]|_)*((\.xml)|(\.xsd))$/',$file['filename']) ){
								return false;
						}
				}
				if ($zip->extract(PCLZIP_OPT_PATH, dirname($this->dossier_xml).'\\'.basename ($fichier_zip,".zip")) == 0) {
                    echo "<script type='text/Javascript'>\n";
					echo "$.unblockUI();\n";
					echo "</script>\n";
					die("Error : ".$zip->errorInfo(true));
				}
				return true;
		}
        
        /**
		* Cherche et remplace les caractères spéciaux dans une chaine de caractères
        * @access public
		* @param string chaine texte à tester
		*/
        // Maj Alassane
        function maj_special_caractere(&$chaine){		
                $chars=array();
                $chars[]=array('find'=>'Ã©','replace'=>'é');
                $chars[]=array('find'=>'Ã¨','replace'=>'è');
                //$chars[]=array('find'=>' ','replace'=>'');
                $chars[]=array('find'=>'Ã¯','replace'=>'ï');
                $chars[]=array('find'=>'Ã®','replace'=>'î');
                $chars[]=array('find'=>'Ãª','replace'=>'ê');
                $chars[]=array('find'=>'Ã¢','replace'=>'â');
                $chars[]=array('find'=>'Ã¤','replace'=>'ä');
                $chars[]=array('find'=>'Ã¹','replace'=>'ù');
                $chars[]=array('find'=>'Ã«','replace'=>'ë');
                $chars[]=array('find'=>'Ã§','replace'=>'ç');                  
               
                if (is_array($chars)){
                    foreach ($chars as $char){
                        $chaine=str_replace($char['find'],$char['replace'],$chaine);
                    }
                }
                return trim($chaine);               
                
              
         }
		
}
?>
