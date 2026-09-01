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
		* Attribut : $tab_filtres
		* <pre>
		* Tableau contenant années, regroupements, secteurs
		* </pre>
		* @var 
		* @access public
		*/   
		public $tab_filtres = array(); // tab filtres : années , regs, secteurs, ...
		
			
		/**
		* Attribut : $all_sectors
		* <pre>
		*  booléen permettant de définir l'exportation ou non les tables de nomenclatures
		* </pre>
		* @var 
		* @access public
		*/   
		public $all_sectors = false; // bool avec ou sans tables nomenc
		
			
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
		* Attribut : $liste_etabs_filtres
		* <pre>
		*  les codes etabs qui respectent les filtres
		* </pre>
		* @var 
		* @access public
		*/   
		public $liste_etabs_filtres	=	array(); // 
		
			
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
		
		public $tab_theme_ordre;
		
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
			$this->tab_theme_ordre = array();
		}
		
		/**
         * Cette fonction __wakeup() permet de réveiller l'objet placé en session
         * @access public
        */
		public function __wakeup(){
			$this->conn     =   $GLOBALS['conn'];
		}
		
		function ordonner_theme($secteur) {
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
		 
		function ordonner_theme_appart($secteur) {
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
		
		function set_liste_tables(){
			$list_exp_sectors = array();
			
			if($this->all_sectors == true){
				foreach($_SESSION['tab_secteur'] as $i => $sect){
					$list_exp_sectors[] = $sect[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] ;
				}
			}else{
				$list_exp_sectors[] = $this->tab_filtres['secteur'];
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
						if(!in_array($table['NOM_TABLE'], $this->liste_tables)){
						 	$this->liste_tables[]=$table['NOM_TABLE'];
						}
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
		function get_liste_etabs_filtres(){
			
			$this->liste_etabs_filtres = array();
			require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php'; 
			$arbre = new arbre($this->tab_filtres['chaine']);
			//print_r($this->tab_filtres['regs']);die();
			foreach($this->tab_filtres['regs'] as $code_regroup){

				$depht	=	$arbre->get_depht_regroup($code_regroup);
				
				//création de la liste des coderegs enfants
				$list_code_reg = $arbre->getchildsid($depht, $code_regroup, $this->tab_filtres['chaine']);

				// On fabrique le "where" de la requête sur ETABLISSEMENT_REGROUPEMENT
				$where_regs = $this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'],$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'],$list_code_reg);
				
				if($this->all_sectors == true){
					$where_sectors = $this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT'], $_SESSION['tab_secteur']) ;
				}else{
					$where_sectors = $GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$this->tab_filtres['secteur'] ;
				}
				
				

				$requete        = ' SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']. ' as code_etab'.'
									FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'   
									WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
									AND '.$where_regs.' AND ' . $where_sectors . '
									ORDER BY '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];

				//echo '<br>'.$requete.'<br>';
				$liste_etab = $this->conn->GetAll($requete);
				if(is_array($liste_etab)){
					foreach( $liste_etab as $ii => $etab ){
						$this->liste_etabs_filtres[$etab['CODE_ETAB']] = array($GLOBALS['PARAM']['CODE_ETABLISSEMENT']=>$etab['CODE_ETAB']);
					}
				}
			}
		}
		
		function est_ds_tableau($elem,$tab){
			if(is_array($tab)){
				foreach($tab as $elements){
					if( $elements == $elem ){
						return true;
		}	}	}	}
		
		/**
		* METHODE : get_criteres_where($champ,$table,$array)
		* <pre>
		*  préparation des critères dans la clause where en fonction  de la table $table et du champ $champ
		* </pre>
		* @access public
		* 
		*/
		function get_criteres_where($champ,$table,$array){
			/*echo'<br>DEBUT---------------<br>';
			echo '<br>'.$champ;
			echo '<br>'.$table;
			echo '<pre>';
			print_r($array);
			echo'<br>FIN ---------------<br><br>';*/
			if(count($array)){
				 // Modif Bass depuis Niger
				$crit_in = array();
				foreach($array as $i_elem => $elem){
					if(!$this->est_ds_tableau($elem[$champ], $crit_in)){
						$crit_in[] = $elem[$champ] ;
					}
				}
				$where               = ' '.$table.'.'.$champ . ' IN ( '.implode(', ',$crit_in).' ) ';
				// Fin Modif Bass depuis Niger
				return $where ;
		}	}
		
		
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
			if(is_array($champs))
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
			$this->get_liste_etabs_filtres(); // appel de la fonction set_liste_tables_ord(); 
			if(count($this->liste_etabs_filtres)>0){
				vider_contenu_dossier($this->chemin_output, false);
				foreach($this->liste_tables as $table){
					$this->put_xml_data($table);
				}
				$this->create_zip();
				ouvrir_popup($GLOBALS['SISED_AURL'].'server-side/import_export/'.$this->fichier_zip, 'down');            
			}else{
				echo "<script type=\"text/javascript\">\n";
				echo "\t alert('".$this->recherche_libelle_page('no_etabs',$_SESSION['langue'],'export.php')."');\n";
				echo "</script>\n";
			}
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
			// Récupération des données de la table en conformité 
			// avec $schema_table et de $this->tab_filtres
			$schema_table = $this->get_schema_table($table);
			$store_table = $table;
			$req_table  = 'SELECT * FROM '.$table;
			$pass = 0;
            
            // Modif Alassane Ajout d'une condition sur la table ENSEIGNANT
			// Modif Yacine 14:07.2010
			if ($table == $GLOBALS['PARAM']['ENSEIGNANT'])
			{
				$req_table  = 'SELECT DISTINCT '.$table.'.* FROM '.$table;
				$req_table	.=	' , '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].' WHERE '.  $GLOBALS['PARAM']['ENSEIGNANT'].'.'. $GLOBALS['PARAM'][	'IDENTIFIANT_ENSEIGNANT'].'='.  $GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'. $GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'];
				$table = $GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'];
				$schema_table = $this->get_schema_table($table);
				$pass++;
			}
			// Fin ajout Yacine
           //TODO trouver une autre solution de controle
			if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $schema_table) ){
				($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
				$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $table, $this->liste_etabs_filtres);	
				$pass++;
			}
			if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $schema_table)){
				($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
				$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $table, $this->tab_filtres['annees']);
				$pass++;
			}
			if($GLOBALS['PARAM']['FILTRE']){
				if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $schema_table)){
					($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
					$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $table, $this->tab_filtres['filtres']);
					$pass++;
				}
			}
			if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $schema_table)){
				($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
				if($this->all_sectors == true){
					$req_table	.= $this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $table, $_SESSION['tab_secteur']) ;
				}else{
					$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $table, array(0 => array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']=> $this->tab_filtres['secteur'])));
				}
				$pass++;
			}
			$table= $store_table ;
			//echo "<br/>$req_table";
			return($req_table);
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
			foreach($schema_table['champs'] as $champ){
				$file_xsd .= "\t\t\t\t".'<element name="'.$champ['nom_champ'].'" type="'.$champ['type_champ'].'"/>'."\n";
			}
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
					foreach($schema_table['champs'] as $champ){
						//fputs($fp_xml, "\t\t\t".'<'.$champ['nom_champ'].'>'.$rs->fields[get_champ_extraction($champ['nom_champ'])].'</'.$champ['nom_champ'].'>'."\n");
                        //Modif Yacine
                        fputs($fp_xml, "\t\t\t".'<'.$champ['nom_champ'].'>'.utf8_encode(str_replace("&","&amp;",$rs->fields[get_champ_extraction($champ['nom_champ'])])).'</'.$champ['nom_champ'].'>'."\n");
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
