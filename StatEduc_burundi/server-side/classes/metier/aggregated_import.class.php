<?php class import {

		
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

	
		/**
		* Contructeur de la classe
		* @access public
		* @param string chemin_zip répertoire des données à importer
        * @param boolean nomencl Inclsusion des données de nomenclature
        * @param string name_log chemin asbolu du fichier de trace  
		*/
		function __construct($chemin_zip, $nomencl,$name_log){
			$this->conn 				= $GLOBALS['conn'];
			$this->dossier_xml 	= preg_replace('/(.*)/([[:alnum:]]|_)*(\.zip)$/','\\1/',$chemin_zip);
            if (isset($nomencl)){
                $this->with_nomenc = $nomencl;
            }
            if (isset($name_log)){
                $this->chemin_log = $SISED_PATH.$name_log.'.log';
            }else{
                $this->chemin_log = $SISED_PATH.'import.log';
            }
            //echo  $this->chemin_log;
            debut_popup_progress();
			$this->create_log_file();
            $this->import_xml(); // appel de la fonction import_xml();            
			$this->close_log_file();
            fin_popup_progress();
	}
	
	
		/**
		* Ordonnancement les tables ds la variables $this->liste_tables_ord en fonction des contraintes 
		* @access public
		*/
		function set_liste_tables_ord(){

     
        $this->liste_tables_ord =array();
        $table_base = array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
        sort($table_base);
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
    
    // Ajout Alassane
    /*function set_liste_tables_ord_simple(){
        $requete='SELECT NOM_TABLE FROM DICO_TABLE_ORDRE ORDER BY ORDRE';
        $liste_table    =   $GLOBALS['conn_dico']->GetAll($requete);
        if (is_array($liste_table)){
            foreach($liste_table as $table){
                  $this->liste_tables_ord[]=$table['NOM_TABLE'];
            }
        }
        
    }*/
	// Ajout HEBIE : Remplissage automatique de la table DICO_TABLE_ORDRE
	function set_liste_tables_ord_simple(){
		$this->ordonner_theme();
		$this->ordonner_table();
		$requete='SELECT NOM_TABLE FROM DICO_TABLE_ORDRE ORDER BY ORDRE';
        $liste_table    =   $GLOBALS['conn_dico']->GetAll($requete);
        if (is_array($liste_table)){
            foreach($liste_table as $table){
                  $this->liste_tables_ord[]=$table['NOM_TABLE'];
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
        sort($table_base);
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
	
	/**
    * Ordonnancement les tables ds la variables $this->liste_tables_ord en fonction des contraintes 
    * @access public
    */
    // Ajout HEBIE pour le remplissage automatique de DICO_TABLE_ORDRE
	function creer_tables_temp(){
		$GLOBALS['conn_dico']->Execute("DROP TABLE TEMP_THEME_ORD_TEMP");
		$strRequete = "CREATE TABLE TEMP_THEME_ORD_TEMP (".
		   "ID integer, ".
		   "ID_THEME_SYSTEME integer, ".
		   "ID_TYPE_THEME integer, ".
		   "PERE integer, ".
		   "PRECEDENT integer, ".
		   "ORDRE integer, ".
		   "CONSTRAINT PK_cle PRIMARY KEY (ID)".
		")";
		$res=$GLOBALS['conn_dico']->Execute($strRequete);
		
		$GLOBALS['conn_dico']->Execute("DROP TABLE TEMP_THEME_ORD");
		$strRequete = "CREATE TABLE TEMP_THEME_ORD (".
			"ID integer, ".
			"ID_THEME_SYSTEME integer, ".
			"ID_TYPE_THEME integer, ".
			"PERE integer, ".
			"PRECEDENT integer, ".
			"ORDRE integer, ".
			"CONSTRAINT PK_cle PRIMARY KEY (ID)".
		")";
		$res=$GLOBALS['conn_dico']->Execute($strRequete);
		
		$GLOBALS['conn_dico']->Execute("DELETE FROM  DICO_TABLE_ORDRE");
		/*$strRequete = "CREATE TABLE TEMP_TABLE_THEME  ( ".
				" ID_TABLE integer ," .
				" NOM_TABLE varchar(50) ," .
				" ORDRE integer ," .
				" LIE_ETAB integer ," .
				" CONSTRAINT PK_cle PRIMARY KEY (ID_TABLE))";
		$res=$GLOBALS['conn_dico']->Execute($strRequete);*/
	}
	function ordonner_theme(){
		$rsTheme =array();
		$rsThemeCourant =array();
		$rsThemeRacine =array();
		$precedent=0; $pere=0;
    
		$req="DROP TABLE TEMP_THEME";
		$res=$GLOBALS['conn_dico']->Execute($req);
		//recuperation des thèmes liés au système courant
		$strRequete = "SELECT DICO_THEME_SYSTEME.ID_THEME_SYSTEME, DICO_THEME.ID_TYPE_THEME, DICO_THEME_SYSTEME.ID, DICO_THEME_SYSTEME.PERE, DICO_THEME_SYSTEME.PRECEDENT".
					" INTO TEMP_THEME FROM DICO_THEME_SYSTEME , DICO_THEME  ".
					" Where DICO_THEME.ID = DICO_THEME_SYSTEME.ID  AND  DICO_THEME_SYSTEME.ID_SYSTEME=" . $_SESSION['secteur'];
	
		$res=$GLOBALS['conn_dico']->Execute($strRequete);
		$req="SELECT * FROM TEMP_THEME";
		$rsTheme=$GLOBALS['conn_dico']->Execute($req);
		$cpt = 1;
		
		$this->creer_tables_temp();
    	
		//Themes dont le pere est la racine
		$precedent = 0;
		$pere = 1030;
		$cpt=1;
		while(!($rsTheme->EOF)){
			if (($rsTheme->fields['PERE'] == $pere) && ($rsTheme->fields['PRECEDENT'] == $precedent)){
				$precedent = $rsTheme->fields['ID_THEME_SYSTEME'];
				$strRequete = "INSERT INTO TEMP_THEME_ORD_TEMP (ID,ID_THEME_SYSTEME, ID_TYPE_THEME,PERE,PRECEDENT, ORDRE) VALUES (" . $rsTheme->fields['ID'] . "," . $precedent . "," . $rsTheme->fields['ID_TYPE_THEME'] . "," . $rsTheme->fields['PERE'] . "," . $rsTheme->fields['PRECEDENT'] . "," . $cpt . ")";
				$res=$GLOBALS['conn_dico']->Execute($strRequete); 
				$cpt = $cpt + 1;
				$strRequete = "DELETE FROM TEMP_THEME Where TEMP_THEME.ID =  " . $rsTheme->fields['ID'];
				$res=$GLOBALS['conn_dico']->Execute($strRequete); 
				$strRequete = "SELECT * FROM TEMP_THEME";
				$rsTheme=$GLOBALS['conn_dico']->Execute($strRequete); 
				//$rsTheme = $rsThemeCourant;
				if ($rsTheme->RecordCount()>0){
					$rsTheme->MoveFirst();
				}
			}else{
				$rsTheme->MoveNext();
			}
		}
		$rsTheme->Close();
		$cpt = 1;
		//Thèmes de pere <>  de la racine
		$rsThemeRacine =  $GLOBALS['conn_dico']->Execute("SELECT * FROM TEMP_THEME_ORD_TEMP ORDER BY ORDRE");
		while(!($rsThemeRacine->EOF)){
			$strRequete = "INSERT INTO TEMP_THEME_ORD (ID,ID_THEME_SYSTEME,ID_TYPE_THEME,PERE,PRECEDENT, ORDRE) VALUES (" . $rsThemeRacine->fields['ID'] . "," . $rsThemeRacine->fields['ID_THEME_SYSTEME'] . "," . $rsThemeRacine->fields['ID_TYPE_THEME'] . "," . $rsThemeRacine->fields['PERE'] . "," . $rsThemeRacine->fields['PRECEDENT'] . "," . $cpt . ")";
			$res=$GLOBALS['conn_dico']->Execute($strRequete); 
			$cpt = $cpt + 1;
			$precedent = 0;
			$pere = $rsThemeRacine->fields['ID_THEME_SYSTEME'];
			$rsTheme=$GLOBALS['conn_dico']->Execute("SELECT * FROM TEMP_THEME WHERE PERE=" . $rsThemeRacine->fields['ID_THEME_SYSTEME']);
			while (!($rsTheme->EOF)){
				if (($rsTheme->fields['PERE'] == $pere) && ($rsTheme->fields['PRECEDENT'] == $precedent)){
					$precedent = $rsTheme->fields['ID_THEME_SYSTEME'];
					$strRequete = "INSERT INTO TEMP_THEME_ORD (ID,ID_THEME_SYSTEME,ID_TYPE_THEME,PERE,PRECEDENT, ORDRE) VALUES (" . $rsTheme->fields['ID'] . "," . $precedent . "," . $rsTheme->fields['ID_TYPE_THEME'] . "," . $rsTheme->fields['PERE'] . "," . $rsTheme->fields['PRECEDENT'] . "," . $cpt . ")";
					$res=$GLOBALS['conn_dico']->Execute($strRequete); 
					$cpt = $cpt + 1;
					$strRequete = "DELETE FROM TEMP_THEME Where TEMP_THEME.ID =  " . $rsTheme->fields['ID'];
					$res=$GLOBALS['conn_dico']->Execute($strRequete); 
					$strRequete = "SELECT * FROM TEMP_THEME";
					$rsTheme=$GLOBALS['conn_dico']->Execute($strRequete);
					//$rsTheme = $rsThemeCourant;
					if ($rsTheme->RecordCount()>0){
						$rsTheme->MoveFirst();
					}
				}else{
					$rsTheme->MoveNext();
				}
			}
        	$rsTheme->Close();
        	$rsThemeRacine->MoveNext();
		}
		$rsThemeRacine->Close();
		$res=$GLOBALS['conn_dico']->Execute("DROP TABLE TEMP_THEME");
		$res=$GLOBALS['conn_dico']->Execute("DROP TABLE TEMP_THEME_ORD_TEMP");
	}
	
	function ordonner_table(){
		$rsTheme =array();
		$rsTable =array();
		$rsTrouve=array();
		$cpt = 3;
		$strRequete = "SELECT * FROM TEMP_THEME_ORD ORDER BY ORDRE";
		$rsTheme=$GLOBALS['conn_dico']->Execute($strRequete);
		while (!($rsTheme->EOF)){
			if ($rsTheme->fields['ID_TYPE_THEME'] <> 1){
				$strRequete = "SELECT * FROM DICO_TABLE_MERE_THEME WHERE ID_THEME=" . $rsTheme->fields['ID'] . " ORDER BY PRIORITE";
			}else{
				$strRequete = "SELECT ID_THEME,ID_TABLE_MERE_THEME ,TABLE_MERE AS NOM_TABLE_MERE,PRIORITE FROM DICO_ZONE WHERE ID_THEME=" . $rsTheme->fields['ID'] . " ORDER BY PRIORITE";
			}
			$rsTable= $GLOBALS['conn_dico']->Execute($strRequete);
			if ($rsTable->RecordCount()>0){
				while (!$rsTable->EOF){
					//if (trim($rsTable['NOM_TABLE_MERE']) <> $GLOBALS['PARAM']['ENSEIGNANT'] Then
						$strRequete = "SELECT * FROM DICO_TABLE_ORDRE WHERE NOM_TABLE='" . trim($rsTable->fields['NOM_TABLE_MERE']) . "'";
						$rsTrouve=$GLOBALS['conn_dico']->Execute($strRequete);
						if ($rsTrouve->RecordCount()==0){
							if ($rsTheme->fields['ID_TYPE_THEME'] <> 1){
								$res=$GLOBALS['conn_dico']->Execute("INSERT INTO DICO_TABLE_ORDRE (ID_TABLE_ORDRE,NOM_TABLE,ORDRE) VALUES (" . $rsTable->fields['ID_TABLE_MERE_THEME'] . ",'" . $rsTable->fields['NOM_TABLE_MERE'] . "'," . $cpt . ")");
							}else{
								$res=$GLOBALS['conn_dico']->Execute ("INSERT INTO DICO_TABLE_ORDRE (ID_TABLE_ORDRE,NOM_TABLE,ORDRE) VALUES (" . ($cpt * 100) . ",'" . $rsTable->fields['NOM_TABLE_MERE'] . "'," . $cpt . ")");
							}
						}
						$rsTrouve->Close();
				   // End If
					$rsTable->MoveNext();
				   $cpt = $cpt + 1;
				}
			}
			$rsTheme->MoveNext();
		}
	}
	//Fin ajout HEBIE
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
			if (is_object($elements)){
				foreach($elements as $element){
					if ($element->nodeType == 1 && $element->nodeName =='element') {
						$xsd_schema_table [$element->getAttribute('name')]=$element->getAttribute('type');	
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
                            $this->set_liste_tables_ord();   //TODO Changer la fonction set_liste_tables_ord par set_liste_tables_ord_bis                           
                             break;
                default:
                        $this->set_liste_tables_ord_simple(); // cas de MS Access
                        break;
            }
            
			// appel de la fonction set_liste_tables_ord(); 
			// Pour chaque élément $table de $this->liste_tables_ord faire :
            if (is_dir(dirname($this->dossier_xml))){
                if ($dh = opendir(dirname($this->dossier_xml))) {
                    while (($file = readdir($dh)) !== false) {
                        $nom_file = dirname($this->dossier_xml).'\\'.$file;
                        if (substr($file,-3) == 'zip'){
                            if($this->valid_zip_import($nom_file)){
                                foreach($this->liste_tables_ord as $table){
                                    //echo 'traitement de '.$table.'<br>';
                                    $this->get_xml_data($table,$file);
                                    
                                    // Suppresssion des fichiers xml et xsd issus de la décompression
                                   $file_xml = dirname($this->dossier_xml).'\\'.basename ($file,".zip").'\\'.$table . '.xml';
                                   if (file_exists( $file_xml )){
                                            unlink($file_xml);
                                    }
                                   $file_xsd = dirname($this->dossier_xml).'\\' .basename ($file,".zip").'\\'.$table . '.xsd';
                                    if (file_exists( $file_xsd )){
                                           unlink($file_xsd);
                                   }
                                }
                            }
                        }
                        rmdir(dirname($this->dossier_xml).'\\' .basename ($file,".zip")) ;   
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
                ///if ($doc_xml->schemaValidate(dirname($this->dossier_xml).'\\'.basename ($file,".zip").'\\'.$table.'.xsd')){
					$racine=$doc_xml->getElementsByTagName($table)->item(0);
					$lignes_data=$racine->getElementsByTagName('ligne_data');
					if (is_object($lignes_data)){
							foreach($lignes_data as $ligne_data){
                                    if ($ligne_data->nodeType == 1 &&  $ligne_data->nodeName == 'ligne_data'){
                                            $cpt =  0;
                                            foreach($xsd_schema as $champ => $type){
												$element=$ligne_data->getElementsByTagName($champ)->item(0);
												//echo '<br>Value:'.$element->nodeValue;
											//if (is_object($ligne_data->childNodes)){
                                                    
													//foreach($ligne_data->childNodes as $element){
                                                            if ($element->nodeType == 1 &&  (trim($element->nodeValue) <> '') ){
                                                                    if ($cpt == 0){
                                                                            $sql_col = $element->nodeName; 
                                                                            if ($xsd_schema[$element->nodeName]=='integer'){                                
                                                                                    $sql_val		= $element->nodeValue;
																					$var_sql_maj	= $element->nodeName . ' = ' . $element->nodeValue;																					
                                                                            }else{              
                                                                                   //$sql_val = '\''.($element->nodeValue).'\'';  
                                                                                   // Modif Alassane
                                                                                   $str_val=$element->nodeValue;
																				   $sql_val 	= $this->conn->qstr($this->maj_special_caractere($str_val)); 
																				   $var_sql_maj = $element->nodeName . ' = ' . $this->conn->qstr($this->maj_special_caractere($str_val));                                                                                                                                                                                                                                                      
                                                                            } 
																			                               
                                                                    }else{
                                                                            $sql_col .=  ' ,'.$element->nodeName;
                                                                            if ($xsd_schema[$element->nodeName]=='integer'){                                
                                                                                    $sql_val 		.= ','.$element->nodeValue;
																					$var_sql_maj	.= ','.$element->nodeName . ' = ' . $element->nodeValue;	
                                                                            }else{
                                                                                    //$sql_val .= ','.$this->conn->qstr($element->nodeValue);                                                                                                                                                                      
                                                                                    //Modif Alassane
                                                                                    $str_val=$element->nodeValue;
																					$sql_val 		.= ','.$this->conn->qstr($this->maj_special_caractere($str_val));
																					$var_sql_maj 	.= ','. $element->nodeName . ' = ' . $this->conn->qstr($this->maj_special_caractere($str_val)); 
                                                                                    
                                                                            }                                
                                                                    }
                                                                    $cpt += 1; 
                                                            }
                                                    //}
                                            //}
											}
                                            $sql = 'INSERT INTO '.$table. '('.$sql_col.') VALUES ('.$sql_val.')'; 
											//echo '<br>'.$sql;                                
                                            if ($this->conn->Execute($sql) === false){
                                                    $this->record_log_file($sql);  
													// bass : si c'est parce que l'enregistrement existe ds la base,
													// on peut effectuer la mise à jour des données : cas  de la table ETAB
													$sql = 'UPDATE ' . $table . ' SET ' . $var_sql_maj ;
													if ($this->conn->Execute($sql) === false){
														$this->record_log_file($sql); 
													}
													                                                 
                                            }            
                                    }
                            }
                    }
                ///}
			}
	}
    
		/**
		* Création d'un fichier de log
		* @access public
		* 
		*/
		function create_log_file(){
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
		function record_log_file($sql){
        $date=date("D M j G:i:s T Y");
        $chaine="$date : $sql\n";
        $ligne = fputs($this->fp,$chaine);
    }
    
		/**
		* Fermeture du fichier de log
		* @access public
		*/
		function close_log_file(){
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
                //echo $fichier_zip.'<br>';
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
