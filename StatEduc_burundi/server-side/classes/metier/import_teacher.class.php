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
        * code de l'etablissement recevant l'enseignant à transferer (importer)
		* @var integer
		* @access public
		*/   
		public $code_school  ;
		
		/**
		* Attribut : 
        * chemin d'accès du fichier de log
		* @var string
		* @access public
		*/   
		public $chemin_log  ;
		
		public $schema_table = array();
		
	
		/**
		* Contructeur de la classe
		* @access public
		* @param string chemin_zip répertoire des données à importer
        * @param boolean nomencl Inclsusion des données de nomenclature
        * @param string name_log chemin asbolu du fichier de trace  
		*/
		function __construct($typ_thm, $code_etab, $chemin_zip, $name_log){
			$this->conn 				= $GLOBALS['conn'];
			$this->dossier_xml 	= preg_replace('/(.*)/([[:alnum:]]|_)*(\.zip)$/','\\1/',$chemin_zip);
            if ($name_log <> ''){
                $this->chemin_log = $GLOBALS['SISED_PATH'].$name_log.'.log';
            }else{
                $this->chemin_log = $GLOBALS['SISED_PATH'].'import_teacher_records.log';
            }
			$this->code_school = $code_etab;
            //echo  $this->chemin_log;
            debut_popup_progress();
			$this->create_log_file();
            $this->import_xml(); // appel de la fonction import_xml();            
			$this->close_log_file();
			echo '<script type="text/javascript" language="javascript">'."\n";
			if($typ_thm=='gril') echo 'parent.document.location.href="questionnaire.php?theme='.$_SESSION['theme_pere'].'&tmis='.$_SESSION['tmis'].'";'."\n";
			if($typ_thm=='form') echo 'parent.document.location.href="questionnaire.php?theme='.$_SESSION['theme'].'&id_teacher='.$_SESSION['id_teacher'].'";'."\n";
			//echo 'fermer();'."\n";
			echo '</script>'."\n";
            fin_popup_progress();
	}
	
    
	function set_liste_tables_ord_simple(){
		include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
		
		if(!isset($_SESSION['code_etab']) || $_SESSION['code_etab']==''){
			if(isset($_SESSION['liste_etab'][0]) || count($_SESSION['liste_etab'][0])>0)
				$code_etabl = $_SESSION['liste_etab'][0];
			else				
				$code_etabl = 0;
		}else{
			$code_etabl = $_SESSION['code_etab'];
		}
		
		$code_etablissement = $code_etabl;
		$code_annee = $_SESSION['annee'];
		$code_filtre = $_SESSION['filtre'];
		$id_systeme	= $_SESSION['secteur'];
		$tmis_imput_themes_syst = array_merge($GLOBALS['PARAM']['TEACHERS_LIST_THEMES'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']);
		$tmis_imput_themes = array();
		$tmis_imput_tabms = array();
		foreach($tmis_imput_themes_syst as $theme_syst){
			$requete  = "SELECT DICO_THEME.* 
						 FROM DICO_THEME, DICO_THEME_SYSTEME 
						 WHERE DICO_THEME.ID = DICO_THEME_SYSTEME.ID AND DICO_THEME_SYSTEME.ID_THEME_SYSTEME = $theme_syst;";
			$result_theme	= $GLOBALS['conn_dico']->GetAll($requete);
			if(!in_array($result_theme[0]['ID'],$tmis_imput_themes)){
				$tmis_imput_themes[] = $result_theme[0]['ID'];
				$curr_inst	= $result_theme[0]['ACTION_THEME'];									
				$id_theme =	$result_theme[0]['ID'];    
				if (file_exists($GLOBALS['SISED_PATH_CLS'] . 'metier/'.$result_theme[0]['CLASSE'].'.class.php')) {
					require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/'.$result_theme[0]['CLASSE'].'.class.php'; 
				}
				switch($curr_inst){
					case 'instance_grille.php' :{
							$curobj_grille		=	new grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
							$curobj_grille->set_code_nomenclature();
							$_SESSION['tmis_curobj_theme'] = $curobj_grille;
							break;
					}	
					case 'instance_mat_grille.php' :{
							$curobj_matgrille = new mat_grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
							$curobj_matgrille->set_code_nomenclature();
							$curobj_matgrille->set_champs();
							$_SESSION['tmis_curobj_theme'] = $curobj_matgrille;
							break;
					}
					case 'instance_matrice.php' :{
							$curobj_matrice	=	new matrice($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
							$_SESSION['tmis_curobj_theme'] = $curobj_matrice;
							break;
					}
				}
				foreach($_SESSION['tmis_curobj_theme']->nomtableliee as $nom_tab){
					if(!in_array($nom_tab,$tmis_imput_tabms)){
						$tmis_imput_tabms[] = $nom_tab;
					}
				}
			}
		}
		
		$liste_table    =   $tmis_imput_tabms;
		if (is_array($liste_table)){
			foreach($liste_table as $table){
				if(!in_array($table, $this->liste_tables_ord)){
					$this->liste_tables_ord[]=$table;
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
				//Ajout d'un champ pour recuperer la hierarchie complete des ecoles d'où proviennent un enseignant en transfert
				if($table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
					$exist_chp_hierar_etab = false;
					$do_import = true;
					if(is_array($this->conn->MetaColumns($GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']))){                           
						foreach($this->conn->MetaColumns($GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']) as $champ){
							if($champ->name == $GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT']){
								$exist_chp_hierar_etab = true;
								break;
							}
						}
						if($exist_chp_hierar_etab == false){
							$req_alter = "ALTER TABLE ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']." ADD ".$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT']." varchar(255);";
							if ($this->conn->Execute($req_alter) === false) {$do_import = false ; print ' <br> error altering :<br>'.$req_alter.'<br>';  }
						}
					}
				}
				////Fin Ajout d'un champ
				//Ajout d'un champ pour recuperer le code des ecoles dans les autres de données d'où proviennent un enseignant en transfert
				if($table<>$GLOBALS['PARAM']['ENSEIGNANT'] && $table<>$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
					$exist_chp_curr_etab = false;
					$do_import = true;
					if(is_array($this->conn->MetaColumns($table))){                           
						foreach($this->conn->MetaColumns($table) as $champ){
							if($champ->name == $GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
								$exist_chp_curr_etab = true;
								break;
							}
						}
						if($exist_chp_curr_etab == false){
							$req_alter = "ALTER TABLE ".$table." ADD ".$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']." integer;";
							if ($this->conn->Execute($req_alter) === false) {$do_import = false ; print ' <br> error altering :<br>'.$req_alter.'<br>';  }
						}
					}
				}
				////Fin Ajout d'un champ
				$racine=$doc_xml->getElementsByTagName($table)->item(0);
				$lignes_data=$racine->getElementsByTagName('ligne_data');
				
				//Pour recuperer la derniere annee de donnees exportées
				if (is_object($lignes_data)){
					$derniere_annee_data = 0;
					foreach($lignes_data as $ligne_data){
						if ($ligne_data->nodeType == 1 &&  $ligne_data->nodeName == 'ligne_data'){
							foreach($xsd_schema as $champ => $type){
								$element=$ligne_data->getElementsByTagName($champ)->item(0);
								if ($element->nodeType == 1 &&  (trim($element->nodeValue) <> '') ){
									if($element->nodeName==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'] && $element->nodeValue<>''){
										$derniere_annee_data = $element->nodeValue;
									}
									
								}
							}
						}
					}
				}
				//Fin recuperation derniere année
				
				if (is_object($lignes_data)){
					$exist_donnee_annee_cour = false;
					$derniere_ligne_data = array();
					$annee_cour_ligne_data = array();
					$derniere_annee_lignes_data = array();
					foreach($lignes_data as $ligne_data){
						if ($ligne_data->nodeType == 1 &&  $ligne_data->nodeName == 'ligne_data'){
							$cpt_chp = 0;
							$cpt_val = 0;
							$exist_donnee_derniere_annee = false;
							foreach($xsd_schema as $champ => $type){
								$element=$ligne_data->getElementsByTagName($champ)->item(0);
								if ($element->nodeType == 1 &&  (trim($element->nodeValue) <> '') ){
									if ($cpt_chp == 0){
										$sql_col = $element->nodeName;
									}else{
										$sql_col .=  ', '.$element->nodeName;
									}
									$cpt_chp++;
									if($element->nodeName==$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']){
										$_SESSION['id_teacher'] = $element->nodeValue;
									}
									if($element->nodeName==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'] && $element->nodeValue==$_SESSION['annee']){
										$exist_donnee_annee_cour = true;
										$annee_cour_ligne_data = $ligne_data;
									}
									if($element->nodeName==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'] && $element->nodeValue==$derniere_annee_data){
										$exist_donnee_derniere_annee = true;
										$derniere_annee_lignes_data[] = $ligne_data;
									}
									//Transformer le code etablissement des enregistrements à inserre de sorte à ce que la base puisse les acepter 
									//du moment que ces etablissements n'existent pas dans la base d'arrivée (du moins normalement)
									if($element->nodeName==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
										if ($xsd_schema[$element->nodeName]=='integer'){                                
											if($cpt_val == 0){
												$sql_val		= $this->code_school;
											}else{
												$sql_val 		.= ', '.$this->code_school;
											}
											$cpt_val++;
										}	
									}else{	
										if ($xsd_schema[$element->nodeName]=='integer'){                                
											if($cpt_val == 0){
												$sql_val		= $element->nodeValue;
											}else{
												$sql_val 		.= ', '.$element->nodeValue;
											}
											$cpt_val++;
										}else{              
										   $str_val=$element->nodeValue;
										   if($cpt_val == 0){
												$sql_val 	= $this->conn->qstr($this->maj_special_caractere($str_val)); 
										   }else{
												$sql_val 		.= ', '.$this->conn->qstr($this->maj_special_caractere($str_val));
										   }
										   $cpt_val++;
										}
									}	
									
								}
							}
							if(trim($sql_col)<>''){			
								//Ensuite il faut inserrer cet enseignant dans les differentes tables comme un nouveau mais avec son ID_STAFF et ses CODE_SCHOOL connus
								$sql_insert = 'INSERT INTO '.$table. '('.$sql_col.') VALUES ('.$sql_val.')';
								if ($this->conn->Execute($sql_insert) === false){
									$this->record_log_file($sql_insert); 
								}
							}        
							$derniere_ligne_data = $ligne_data;
						}
						
					}
					//Ensuite il faut inserrer cet enseignant dans les differentes tables pour l'année en cours
					if($table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
						if($exist_donnee_annee_cour){
							unset($derniere_ligne_data);
							$derniere_ligne_data = $annee_cour_ligne_data;
						}
						if(count($derniere_ligne_data)>0){
							$cpt_chp = 0;
							$cpt_val = 0;
							$sql_col = '';
							$sql_val = '';
							$cpt_set = 0;
							$cpt_where = 0;
							$exist_chp_prev_year = false;
							$exist_chp_annee = false;
							$exist_chp_etab = false;
							$sql_val_prev_year = '';
							foreach($xsd_schema as $champ => $type){
								$element=$derniere_ligne_data->getElementsByTagName($champ)->item(0);
								//Mettre dans le champ previous year school le dernier etablissement de l'enseignant en transfert (concatener avec les regroupements) seleon le fichier d'enregistrement
								if($element->nodeType == 1 && $element->nodeName==$GLOBALS['PARAM']['ETAB_PREC_ENSEIG_TRANSFERT']){
									$exist_chp_prev_year = true;
								}
								if ($element->nodeType == 1 &&  (trim($element->nodeValue) <> '') ){
									if($element->nodeName==$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']){
										$_SESSION['id_teacher'] = $element->nodeValue;
										//Pr mise update au cas où
										if($cpt_where == 0){
											$var_sql_maj_cles	= $element->nodeName . ' = ' . $element->nodeValue;
										}else{
											$var_sql_maj_cles	.= ' AND '.$element->nodeName . ' = ' . $element->nodeValue;
										}
										$cpt_where++;
									}
									if($element->nodeName==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']){
										if ($xsd_schema[$element->nodeName]=='integer'){                                
											$exist_chp_annee = true;
											if ($cpt_chp == 0){
												$sql_col = $element->nodeName;
											}else{
												$sql_col .=  ', '.$element->nodeName;
											}
											$cpt_chp++;
											
											if($cpt_val == 0){
												$sql_val		= $_SESSION['annee'];
											}else{
												$sql_val 		.= ', '.$_SESSION['annee'];
											}
											$cpt_val++;
											//Pr mise update au cas où
											if($cpt_where == 0){
												$var_sql_maj_cles	= $element->nodeName . ' = ' . $_SESSION['annee'];
											}else{
												$var_sql_maj_cles	.= ' AND '.$element->nodeName . ' = ' . $_SESSION['annee'];
											}
											$cpt_where++;
										}	
									}elseif($element->nodeName==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
										if ($xsd_schema[$element->nodeName]=='integer'){                                
											$exist_chp_etab = true;
											if ($cpt_chp == 0){
												$sql_col = $element->nodeName;
											}else{
												$sql_col .=  ', '.$element->nodeName;
											}
											$cpt_chp++;
											
											if($cpt_val == 0){
												$sql_val		= $this->code_school;
											}else{
												$sql_val 		.= ', '.$this->code_school;
											}
											$cpt_val++;
											//Pr mise update au cas où
											if($cpt_where == 0){
												$var_sql_maj_cles	= $element->nodeName . ' = ' . $this->code_school;
											}else{
												$var_sql_maj_cles	.= ' AND '.$element->nodeName . ' = ' . $this->code_school;
											}
											$cpt_where++;
										}	
									}elseif($element->nodeName==$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
										if ($xsd_schema[$element->nodeName]=='integer'){                                
											if ($cpt_chp == 0){
												$sql_col = $element->nodeName;
											}else{
												$sql_col .=  ', '.$element->nodeName;
											}
											$cpt_chp++;
											
											if($cpt_val == 0){
												$sql_val		= $this->code_school;
											}else{
												$sql_val 		.= ', '.$this->code_school;
											}
											$cpt_val++;
											//Pr mise update au cas où
											if($cpt_set == 0){
												$var_sql_maj	= $element->nodeName . ' = ' . $this->code_school;
											}else{
												$var_sql_maj	.= ', '.$element->nodeName . ' = ' . $this->code_school;
											}
											$cpt_set++;
										}	
									}
									//Mettre dans le champ previous year school son dernier etablissement (concatener avec les regroupements) seleon le fichier d'enregistrement
									elseif($element->nodeName==$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT']){
										if ($cpt_chp == 0){
											$sql_col = $element->nodeName;
										}else{
											$sql_col .=  ', '.$element->nodeName;
										}
										$cpt_chp++;
										
										$str_val=$element->nodeValue;
										if($cpt_val == 0){
											$sql_val 	= '\'\''; 
										}else{
											$sql_val 		.= ', \'\'';
										}
										$sql_val_prev_year = $this->conn->qstr($this->maj_special_caractere($str_val));
										$cpt_val++;
										//Pr update au cas où
										if($cpt_set == 0){
											$var_sql_maj = $element->nodeName . ' = ' . '\'\'';                                                                                                                                                                                                                                                      
										}else{
											$var_sql_maj 	.= ', '. $element->nodeName . ' = ' . '\'\''; 
										}
										$cpt_set++;
									}
									
									elseif($element->nodeName<>$GLOBALS['PARAM']['ETAB_PREC_ENSEIG_TRANSFERT']){	
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
										}else{              
										   $str_val=$element->nodeValue;
										   if($cpt_val == 0){
												$sql_val 	= $this->conn->qstr($this->maj_special_caractere($str_val)); 
										   }else{
												$sql_val 		.= ', '.$this->conn->qstr($this->maj_special_caractere($str_val));
										   }
										   $cpt_val++;
										}
									}	
								}
							}
							//Mettre dans le champ previous year school son dernier etablissement (concatener avec les regroupements) seleon le fichier d'enregistrement
							if($exist_chp_prev_year){
									if ($cpt_chp == 0){
										$sql_col = $GLOBALS['PARAM']['ETAB_PREC_ENSEIG_TRANSFERT'];
									}else{
										$sql_col .=  ', '.$GLOBALS['PARAM']['ETAB_PREC_ENSEIG_TRANSFERT'];
									}
									$cpt_chp++;
									
									if($cpt_val == 0){
										$sql_val		= $sql_val_prev_year;
									}else{
										$sql_val 		.= ', '.$sql_val_prev_year;
									}
									$cpt_val++;
									//Pr update au cas où
									if($cpt_set == 0){
										$var_sql_maj = $GLOBALS['PARAM']['ETAB_PREC_ENSEIG_TRANSFERT'] . ' = ' . $sql_val_prev_year;                                                                                                                                                                                                                                                      
									}else{
										$var_sql_maj 	.= ', '. $GLOBALS['PARAM']['ETAB_PREC_ENSEIG_TRANSFERT'] . ' = ' . $sql_val_prev_year; 
									}
									$cpt_set++;
							}
							if(trim($sql_col)<>'' && $exist_chp_annee && $exist_chp_etab){			
								//Insertion (par recopie pour re-utilisation) des dernieres données enseignant pour année courante si non incluses dans les enregistrements importés
								$sql_insert = 'INSERT INTO '.$table. '('.$sql_col.') VALUES ('.$sql_val.')';
								if ($this->conn->Execute($sql_insert) === false){
									//$this->record_log_file($sql_insert);
									if(trim($var_sql_maj)<>''){
										$sql_update = 'UPDATE ' . $table . ' SET ' . $var_sql_maj. ' WHERE ' .$var_sql_maj_cles;
										//echo "<br>$sql_update<br>";
										if ($this->conn->Execute($sql_update) === false){
											$this->record_log_file($sql_insert);  
											$this->record_log_file($sql_update); 
										}
									}
								}
							}   
						}
					}
					//
					else{
						foreach($derniere_annee_lignes_data as $derniere_ligne_data){
							$cpt_chp = 0;
							$cpt_val = 0;
							$sql_col = '';
							$sql_val = '';
							$cpt_set = 0;
							$cpt_where = 0;
							$exist_chp_annee = false;
							$exist_chp_etab = false;
							foreach($xsd_schema as $champ => $type){
								$element=$derniere_ligne_data->getElementsByTagName($champ)->item(0);
								//Mettre dans le champ previous year school le dernier etablissement de l'enseignant en transfert (concatener avec les regroupements) seleon le fichier d'enregistrement
								if ($element->nodeType == 1 &&  (trim($element->nodeValue) <> '') ){
									if($element->nodeName==$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']){
										$_SESSION['id_teacher'] = $element->nodeValue;
										//Pr mise update au cas où
										if($cpt_where == 0){
											$var_sql_maj_cles	= $element->nodeName . ' = ' . $element->nodeValue;
										}else{
											$var_sql_maj_cles	.= ' AND '.$element->nodeName . ' = ' . $element->nodeValue;
										}
										$cpt_where++;
									}
									if($element->nodeName==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']){
										if ($xsd_schema[$element->nodeName]=='integer'){                                
											$exist_chp_annee = true;
											if ($cpt_chp == 0){
												$sql_col = $element->nodeName;
											}else{
												$sql_col .=  ', '.$element->nodeName;
											}
											$cpt_chp++;
											
											if($cpt_val == 0){
												$sql_val		= $_SESSION['annee'];
											}else{
												$sql_val 		.= ', '.$_SESSION['annee'];
											}
											$cpt_val++;
											//Pr mise update au cas où
											if($cpt_where == 0){
												$var_sql_maj_cles	= $element->nodeName . ' = ' . $_SESSION['annee'];
											}else{
												$var_sql_maj_cles	.= ' AND '.$element->nodeName . ' = ' . $_SESSION['annee'];
											}
											$cpt_where++;
										}	
									}elseif($element->nodeName==$GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
										if ($xsd_schema[$element->nodeName]=='integer'){                                
											$exist_chp_etab = true;
											if ($cpt_chp == 0){
												$sql_col = $element->nodeName;
											}else{
												$sql_col .=  ', '.$element->nodeName;
											}
											$cpt_chp++;
											
											if($cpt_val == 0){
												$sql_val		= $this->code_school;
											}else{
												$sql_val 		.= ', '.$this->code_school;
											}
											$cpt_val++;
											//Pr mise update au cas où
											if($cpt_where == 0){
												$var_sql_maj_cles	= $element->nodeName . ' = ' . $this->code_school;
											}else{
												$var_sql_maj_cles	.= ' AND '.$element->nodeName . ' = ' . $this->code_school;
											}
											$cpt_where++;
										}	
									}elseif($element->nodeName==$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
										if ($xsd_schema[$element->nodeName]=='integer'){                                
											if ($cpt_chp == 0){
												$sql_col = $element->nodeName;
											}else{
												$sql_col .=  ', '.$element->nodeName;
											}
											$cpt_chp++;
											
											if($cpt_val == 0){
												$sql_val		= $this->code_school;
											}else{
												$sql_val 		.= ', '.$this->code_school;
											}
											$cpt_val++;
											//Pr mise update au cas où
											if($cpt_set == 0){
												$var_sql_maj	= $element->nodeName . ' = ' . $this->code_school;
											}else{
												$var_sql_maj	.= ', '.$element->nodeName . ' = ' . $this->code_school;
											}
											$cpt_set++;
										}	
									}
									else{	
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
										}else{              
										   $str_val=$element->nodeValue;
										   if($cpt_val == 0){
												$sql_val 	= $this->conn->qstr($this->maj_special_caractere($str_val)); 
										   }else{
												$sql_val 		.= ', '.$this->conn->qstr($this->maj_special_caractere($str_val));
										   }
										   $cpt_val++;
										}
									}	
								}
							}
							if(trim($sql_col)<>'' && $exist_chp_annee && $exist_chp_etab){			
								//Insertion (par recopie pour re-utilisation) des dernieres données enseignant pour année courante si non incluses dans les enregistrements importés
								$sql_insert = 'INSERT INTO '.$table. '('.$sql_col.') VALUES ('.$sql_val.')';
								if ($this->conn->Execute($sql_insert) === false){
									//$this->record_log_file($sql_insert);
									if(trim($var_sql_maj)<>''){
										$sql_update = 'UPDATE ' . $table . ' SET ' . $var_sql_maj. ' WHERE ' .$var_sql_maj_cles;
										//echo "<br>$sql_update<br>";
										if ($this->conn->Execute($sql_update) === false){
											$this->record_log_file($sql_insert);  
											$this->record_log_file($sql_update); 
										}
									}
								}
							}   
						}
					}
					//
				}
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
