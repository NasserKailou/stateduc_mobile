<?php 
		class export {

		/**
		* Attribut : $conn
		* <pre>
		* Variable de connexion à la base
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn; 
		
		/**
		* Attribut : $liste_tables
		* <pre>
		*  liste des tables de données à exporter
		* </pre>
		* @var 
		* @access public
		*/   
		public $liste_tables = array();
		
		/**
		* Attribut : $chemin_output
		* <pre>
		*  chemin où le fichier zip sera logé
		* </pre>
		* @var 
		* @access public
		*/   
		public $chemin_output = '';
		
		/**
		* Attribut : $fichier_zip
		* <pre>
		*  Nom du fichier Zip
		* </pre>
		* @var 
		* @access public
		*/   
		public $fichier_zip;
		
		/**
		* METHODE : __construct()
		* <pre>
		*  Constructeur
		* </pre>
		* @access public
		* 
		*/
		function __construct(){
			$this->conn			= $GLOBALS['conn'];
			$this->fichier_zip	= 'import_export.zip';
		}
		
		/**
         * Cette fonction __wakeup() permet de réveiller l'objet placé en session
         * @access public
        */
		public function __wakeup(){
			$this->conn     =   $GLOBALS['conn'];
		}

		function set_liste_tables(){
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
					if(!in_array($table, $this->liste_tables)){
						$this->liste_tables[]=$table;
					}
				}
			}
		}
		//Fin HEBIE
		/**
		* METHODE : get_liste_etabs_filtres()
		* <pre>
		*  récupération des établiss respectant les critères d'exportation 
		* </pre>
		* @access public
		* 
		*/
		function get_liste_etabs_filtres(){}
		
		function est_ds_tableau($elem,$tab){
			if(is_array($tab)){
				foreach($tab as $elements){
					if( $elements == $elem ){
						return true;
		}	}	}	}
		
		/**
		* METHODE : get_schema_table($table)
		* <pre>
		*  récupére le scéma de la table $table
		* </pre>
		* @access public
		* 
		*/
		function get_schema_table($table){ 
			// récupération de la structure d'une table donnée dans le catalogue
			$schema_table =	array();
			$schema_table['table']  = $table;
			
			$meta_types_integer = array('-6','4','5','7','8','I','N','bigint','bit','decimal','float','float4','float8','int','int2','int4','int8','numeric','smallint','tinyint','real');
			$meta_types_text = array('12','C','char','nchar','ntext','nvarchar','text','varchar');
			$meta_types_date = array('11','D','T','datetime','smalldatetime');
			
			$champs 	= $this->conn->MetaColumns($table);
			
			foreach( $champs as $champ ){
				if(in_array($champ->type , $meta_types_integer)){
					$type = 'integer';
				}elseif(in_array($champ->type, $meta_types_date)){
					$type = 'date';
				}else{
					$type = 'string';
				}
				$schema_table['champs'][] =  array('nom_champ'	=>	$champ->name	,	'type_champ'	=>	$type);	
			}
			return $schema_table;
		}
		
		/**
		* METHODE : exist_champ_in_schema($nom_champ, $schema)
		* <pre>
		*  vérifie si le champ $nom_champ appartient au schéma $schema)
		* </pre>
		* @access public
		* 
		*/
		function exist_champ_in_schema($nom_champ, $schema){
			if(is_array($schema['champs'])){
				foreach($schema['champs'] as $i => $champ){
					if(trim($champ['nom_champ']) == trim($nom_champ)){
						return true;
						break;
		}	}	}	}
		
		function recherche_libelle_page($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				$requete 	= "SELECT LIBELLE
								FROM DICO_LIBELLE_PAGE 
								WHERE CODE_LIBELLE='".$code."' And CODE_LANGUE='".$langue."'
								AND NOM_PAGE='".$table."'";
				
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$all_res	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($all_res)){                    
								throw new Exception('ERR_SQL');  
						} 
						return($all_res[0]['LIBELLE']);									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
		}
		
		/**
		* METHODE : export_xml()
		* <pre>
		* fonction de lancement de l'export
		* </pre>
		* @access public
		* 
		*/
		function export_xml(){
			debut_popup_progress();
			$this->set_liste_tables(); // appel de la fonction set_liste_tables_ord(); 
			vider_contenu_dossier($this->chemin_output, false);
			foreach($this->liste_tables as $table){
				$this->put_xml_data($table);
			}
			$this->create_zip();
			ouvrir_popup($GLOBALS['SISED_AURL'].'server-side/import_export/'.$this->fichier_zip, 'down');            
			fin_popup_progress();
		}
		
		
		/**
		* METHODE : get_sql_donnees_table($table)
		* <pre>
		*  récupère le sql de récupération des données de la table $table
		* </pre>
		* @access public
		* 
		*/
		function get_sql_donnees_table($table){
			$schema_table = $this->get_schema_table($table);
			$req_table  = 'SELECT * FROM '.$table;
			$pass = 0;
			if($this->exist_champ_in_schema($GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'], $schema_table) ){
				($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
				$req_table	.=	$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']." = ".$_SESSION['id_teacher'];	
				$pass++;
			}
			if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $schema_table) ){
				$req_table .= ' ORDER BY '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
			}
			//echo '<br>'.$req_table.'<br>'; 
			return($req_table);
		}
		
		function get_champ_extraction($nom_champ){
				$conn 		= $GLOBALS['conn'];
				$champ_extract = $nom_champ;
				if (strlen($nom_champ)>30) {
						if ($conn->databaseType == 'mssqlnative' || $conn->databaseType == 'mssql') { 
								$taille_max_extract=30;                
						}else if ($conn->databaseType == 'postgres9') {
								$taille_max_extract=strlen($nom_champ);
						}else{
								$taille_max_extract=31;                
						}
						$champ_extract = substr($nom_champ ,0,$taille_max_extract); 
				}
				return($champ_extract);
		}
			
		/**
		* METHODE : put_xml_data($table)
		* <pre>
		*  met les données de la table $table dans le fichier XML
		* </pre>
		* @access public
		* 
		*/
		function put_xml_data($table){
			$xml_output = $this->chemin_output . $table .'.xml';
			$xsd_output = $this->chemin_output . $table .'.xsd';
			//echo "<br> table = $table< br>";
			$schema_table = $this->get_schema_table($table);
			// creation fichier xsd
			$file_xsd ='';
			/*$file_xsd .= '<?xml version="1.0" encoding="iso-8859-1"?>'."\n";*/
            // Modif Alassane
            $file_xsd .= '<?xml version="1.0" encoding="utf-8"?>'."\n";
			$file_xsd .= "\t".'<schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">'."\n";
			$file_xsd .= "\t\t".'<element name="'.$table.'" type="ligne_data"/>'."\n";
			$file_xsd .= "\t\t".'<complexType name="ligne_data">'."\n";
			$file_xsd .= "\t\t\t".'<sequence>'."\n";
			$exist_chp_hierar_etab = false;
			$exist_chp_curr_etab = false;
			foreach($schema_table['champs'] as $champ){
				$file_xsd .= "\t\t\t\t".'<element name="'.$champ['nom_champ'].'" type="'.$champ['type_champ'].'"/>'."\n";
				if($champ['nom_champ'] == $GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT'])	$exist_chp_hierar_etab = true;
				if($champ['nom_champ'] == $GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'])	$exist_chp_curr_etab = true;
			}
			//Ajout dans l'export d'un champ localisation des etablissements de l'enseignant en transfert
			if(!$exist_chp_hierar_etab && $table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
				$file_xsd .= "\t\t\t\t".'<element name="'.$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT'].'" type="string"/>'."\n";
			}
			//Fin Ajout dans l'export d'un champ localisation des etablissements de l'enseignant en transfert
			//Ajout dans l'export d'un champ etablissement courant de l'enseignant en transfert
			if(!$exist_chp_curr_etab && $table<>$GLOBALS['PARAM']['ENSEIGNANT'] && $table<>$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
				$file_xsd .= "\t\t\t\t".'<element name="'.$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'" type="integer"/>'."\n";
			}
			//Fin Ajout dans l'export d'un champ etablissement courant de l'enseignant en transfert
			$file_xsd .= "\t\t\t".'</sequence>'."\n";
			$file_xsd .= "\t\t".'</complexType>'."\n";
			$file_xsd .= "\t".'</schema>'."\n";
			
			file_put_contents($xsd_output, $file_xsd);
			
			// creation fichier xml ecriture ligne par ligne du fait que le fichier peut etre grand
			$fp_xml		 = fopen($xml_output,'w');
			
			/*fputs($fp_xml, '<?xml version="1.0" encoding="iso-8859-1"?>'."\n");*/
            // Modif Alassane
            fputs($fp_xml, '<?xml version="1.0" encoding="utf-8"?>'."\n");
			fputs($fp_xml, "\t".'<'.$table.'>'."\n");
			
			//$this->conn->GetAll($req_table)
            
			$sql_donnees_table = $this->get_sql_donnees_table($table); 
			$rs	= $this->conn->Execute($sql_donnees_table);
			if($rs <> false){
				while (!$rs->EOF){
					fputs($fp_xml, "\t\t".'<ligne_data>'."\n");
					$exist_donnee_hierar_etab = false;
					$exist_donnee_curr_etab = false;
					foreach($schema_table['champs'] as $champ){
						//fputs($fp_xml, "\t\t\t".'<'.$champ['nom_champ'].'>'.$rs->fields[get_champ_extraction($champ['nom_champ'])].'</'.$champ['nom_champ'].'>'."\n");
                        //Modif Alassane
						if($champ['nom_champ'] <> $GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT'] && $champ['nom_champ'] <> $GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']){
							fputs($fp_xml, "\t\t\t".'<'.$champ['nom_champ'].'>'.utf8_encode(str_replace("&","&amp;",$rs->fields[get_champ_extraction($champ['nom_champ'])])).'</'.$champ['nom_champ'].'>'."\n");
						}
						if($champ['nom_champ'] == $GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT'] && trim($rs->fields[$this->get_champ_extraction($champ['nom_champ'])])<>''){
							$exist_donnee_hierar_etab = true;
							fputs($fp_xml, "\t\t\t".'<'.$champ['nom_champ'].'>'.utf8_encode(str_replace("&","&amp;",$rs->fields[get_champ_extraction($champ['nom_champ'])])).'</'.$champ['nom_champ'].'>'."\n");
						}
						if($champ['nom_champ'] == $GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'] && trim($rs->fields[$this->get_champ_extraction($champ['nom_champ'])])<>''){
							$exist_donnee_curr_etab = true;
							fputs($fp_xml, "\t\t\t".'<'.$champ['nom_champ'].'>'.utf8_encode(str_replace("&","&amp;",$rs->fields[get_champ_extraction($champ['nom_champ'])])).'</'.$champ['nom_champ'].'>'."\n");
						}
					}
					if(!$exist_donnee_hierar_etab && $table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
						//Ajout dans l'export d'un champ localisation des etablissements de l'enseignant en transfert
						require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
						$conn  = $GLOBALS['conn'];
						//Recherche le code_regroupement à partir du code_etab
						/*$requete    = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
									   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'
									   WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='. $rs->fields[$this->get_champ_extraction($GLOBALS['PARAM']['CODE_ETABLISSEMENT'])];
						//et mise en session
						$code_regroup = $conn->GetOne($requete);
						//if($_SESSION['code_regroupement']<>$code_regroup){
							$code_regroupement=$code_regroup;*/
							$requete ='SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'
									FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].', '.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['HIERARCHIE'].','.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 
									WHERE '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
									AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
									AND  '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'
									AND  '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$rs->fields[$this->get_champ_extraction($GLOBALS['PARAM']['CODE_ETABLISSEMENT'])].'
									AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$_SESSION['chaine'].'
									AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'=1
									AND  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'];
							//et mise en session
							$code_regroupement = $conn->GetOne($requete);
							
							//Recherche du nombre de niveaux de la chaine
							$requete ='SELECT COUNT(*)
										FROM '.$GLOBALS['PARAM']['HIERARCHIE'].'
										WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$_SESSION['chaine'];
							$niveau = $conn->GetOne($requete)-1;
							//La classe arbre permet de recomposer la hiérarchie des regroupements
							$arbre = new arbre($_SESSION['chaine']);
							$hierarchie = $arbre->getparentsid($niveau,$code_regroupement,$_SESSION['chaine']);
						//}else{
						//	$arbre = new arbre($_SESSION['chaine']);
						//	$hierarchie = $arbre->getparentsid(substr($_SESSION['nom_regroupement'],1),$_SESSION['code_regroupement']);
						//}
						$hierarchie_regroup_etab = '';
						foreach($hierarchie as $i=>$h) {
							$hierarchie_regroup_etab .= trim($h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]);
							if($i<(count($hierarchie)-1)) {
								$hierarchie_regroup_etab .= '/';
							}
						}
						$hierarchie_regroup_etab .= '/';
						$champs_extract_in_etab = array($GLOBALS['PARAM']['CODE_ETABLISSEMENT'].', '.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']);
						$requete    = 'SELECT '.implode(', ', $champs_extract_in_etab).' 
									   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' 
									   WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='.$rs->fields[$this->get_champ_extraction($GLOBALS['PARAM']['CODE_ETABLISSEMENT'])].';';
						//echo $requete;exit;
						$result_etab = $conn->GetAll($requete);
						$hierarchie_regroup_etab .= trim($result_etab[0][$GLOBALS['PARAM']['NOM_ETABLISSEMENT']]);
						fputs($fp_xml, "\t\t\t".'<'.$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT'].'>'.utf8_encode(str_replace("&","&amp;",$hierarchie_regroup_etab)).'</'.$GLOBALS['PARAM']['HIERARCHIE_ETAB_TRANSFERT'].'>'."\n");
						//Fin Ajout dans l'export d'un champ localisation des etablissements de l'enseignant en transfert
					}
					//Ajout dans l'export d'un champ etablissement courant de l'enseignant en transfert
					if(!$exist_donnee_curr_etab && $table==$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
						//mise à jour du CURRENT_SCHOOL dans la table STAFF_SCHOOL
						$req_upd_cur_etab	= "UPDATE ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']."
									   			SET ".$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT']."=".$rs->fields[$this->get_champ_extraction($GLOBALS['PARAM']['CODE_ETABLISSEMENT'])]."
												WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='.$rs->fields[$this->get_champ_extraction($GLOBALS['PARAM']['CODE_ETABLISSEMENT'])]."
												AND ".$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].' ='.$rs->fields[$this->get_champ_extraction($GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'])]."
												AND ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' ='.$rs->fields[$this->get_champ_extraction($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'])].";";
						//die($req_upd_cur_etab);
						$res_upd_cur_etab = $conn->Execute($req_upd_cur_etab);
						//Ajout dans l'export d'un champ etablissement courant de l'enseignant en transfert
						fputs($fp_xml, "\t\t\t".'<'.$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'>'.utf8_encode(str_replace("&","&amp;",$rs->fields[$this->get_champ_extraction($GLOBALS['PARAM']['CODE_ETABLISSEMENT'])])).'</'.$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'>'."\n");
					}
					//Ajout dans l'export d'un champ etablissement courant de l'enseignant en transfert dans les autres tables de données enseignant
					if(!$exist_donnee_curr_etab && $table<>$GLOBALS['PARAM']['ENSEIGNANT'] && $table<>$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']){
						fputs($fp_xml, "\t\t\t".'<'.$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'>'.utf8_encode(str_replace("&","&amp;",$rs->fields[$this->get_champ_extraction($GLOBALS['PARAM']['CODE_ETABLISSEMENT'])])).'</'.$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'>'."\n");
					}
					fputs($fp_xml, "\t\t".'</ligne_data>'."\n");
					$rs->MoveNext();
			}	}
			
			fputs($fp_xml, "\t".'</'.$table.'>'."\n");
			
			fclose($fp_xml);
		}
		
		
		/**
		* METHODE : create_zip()
		* <pre>
		* fonction de création du fichier ZIP aprés export
		* </pre>
		* @access public
		* 
		*/
		function create_zip(){
			include_once($GLOBALS['SISED_PATH_LIB'].'pclzip.lib.php');
			if(!empty($this->fichier_zip)){
				$fichier_zip = preg_replace("/[[:blank:]|[:space:]]/", '_', $fichier_zip);
				$fichier_zip = (str_replace('.zip','',$this->fichier_zip)).'.zip';
			}else{
				$fichier_zip = 'import_export.zip';
			}
			$this->fichier_zip = $fichier_zip;
			$fich_compl_zip = $this->chemin_output . $fichier_zip ;
			if(file_exists($fich_compl_zip)){
				unlink($fich_compl_zip);
			}
			$zip 			= new PclZip($fich_compl_zip);
			$list_zip = array();
			foreach($this->liste_tables as $table){
				$list_zip[] = $this->chemin_output . $table . '.xml';
				$list_zip[] = $this->chemin_output . $table . '.xsd';
			}
			$res = $zip->create( $list_zip , PCLZIP_OPT_REMOVE_PATH, $this->chemin_output);
			if ($res == 0) {
				print("Error : ".$zip->errorInfo(true));
			}
			foreach($this->liste_tables as $table){
				unlink($this->chemin_output . $table . '.xml' );
				unlink($this->chemin_output . $table . '.xsd' );
			}
		}
	}
?>