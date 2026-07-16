<?php class export {

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
		* Attribut : $with_nomenc
		* <pre>
		*  booléen permettant de définir l'exportation ou non les tables de nomenclatures
		* </pre>
		* @var 
		* @access public
		*/   
		public $with_nomenc = false; // bool avec ou sans tables nomenc
		
			
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
		* Attribut : $fichier_excel
		* <pre>
		*  Nom du fichier Zip
		* </pre>
		* @var 
		* @access public
		*/   
		public $fichier_excel;
		public $new_fichier_excel;
		
		public $tab_mat_dim_lig = array();
		public $tab_mat_dim_col = array();
		public $tab_mat_liste_checkbox = array();
		public $liste_tab = array();
		public $tab_with_checkbox = array();
		public $table_champ_fils = array();
		public $excel;
		public $book;
		public $sheet;
		public $tab_avec_fixe_val = array();
		
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
		}
		
		// Ajout HEBIE pour la selection automatique des tables de donnees
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
		
		//HEBIE
		function set_liste_tables(){
			$this->ordonner_theme();
			$this->ordonner_table();
			$requete='SELECT NOM_TABLE FROM DICO_TABLE_ORDRE ORDER BY ORDRE';
			$liste_table    =   $GLOBALS['conn_dico']->GetAll($requete);
			if (is_array($liste_table)){
				foreach($liste_table as $table){
					if($table['NOM_TABLE']<>$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']) $this->liste_tables[]=$table['NOM_TABLE'];
				}
			}
			if($this->with_nomenc == true){
				$all_tables_temp  = array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
				sort($all_tables_temp);
				foreach( $all_tables_temp as $i => $table){
					if( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/',strtoupper($table)) ){
						$this->liste_tables[] 	= $table;
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
				$where = $this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'],$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'],$list_code_reg);
				if($_SESSION['FILTRE']['FILTRE_2']){
					$where_filtre2	=	$this->get_criteres_where($_SESSION['FILTRE']['CODE_TYPE_FILTRE_2'], $GLOBALS['PARAM']['ETABLISSEMENT'], $this->tab_filtres['filtres2']);
					$requete        = ' SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']. ' as code_etab'.'
										FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'   
										WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
										AND '.$where.' AND '.$where_filtre2.' AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$this->tab_filtres['secteur'].'
										ORDER BY '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
				}else{
					$requete        = ' SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']. ' as code_etab'.'
										FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'   
										WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
										AND '.$where.' AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$this->tab_filtres['secteur'].'
										ORDER BY '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
				}
				//echo '<br>'.$requete.'<br>';die;
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
			}
		}
		
		
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
			if(is_array($champs)){
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
		function export_excel(){
			debut_popup_progress();
			$this->get_dico();
			$this->set_liste_tables(); // appel de la fonction set_liste_tables_ord(); 
			$this->get_liste_etabs_filtres(); // appel de la fonction set_liste_tables_ord(); 
			if(count($this->liste_etabs_filtres)>0){
				$requete = "SELECT * FROM DICO_EXCEL_AGGREGATED_TABLE WHERE ID_SYSTEME = ".$_SESSION['secteur']." ORDER BY ORDRE_FORM";
				$rs_aggreg_table = $GLOBALS['conn_dico']->GetAll($requete);
				
				$FILENAME = $this->fichier_excel;
				/*$path_parts = pathinfo("/forum/index.php");
				echo '<pre>';
				print_r($path_parts); // Affiche Array ( [dirname] => /forum [basename] => index.php [extension] => php [filename] => index )
				echo '</pre>';*/
				$location = dirname($this->fichier_excel);
				$FILENAME2 = $location.'\\'.$this->new_fichier_excel.'.xls';
				
				$this->excel=new COM("Excel.application") or die ('Impossible d\'ouvrir Excel');
				$this->excel->Workbooks->Open($FILENAME) or die ('Impossible d\'ouvrir le modèle');
				$this->book=$this->excel->Workbooks(1);
				
				if(is_array($rs_aggreg_table)){
					foreach($rs_aggreg_table as $table){
						$this->sheet=$this->book->Worksheets($table['NOM_PAGE']);
						$requete = "SELECT * FROM DICO_AGGREGATED_TABLE WHERE ID_SYSTEME = ".$_SESSION['secteur']." AND NOM_TABLE_2 = '".$table['NOM_TABLE_2']."'";
						$res_aggreg_tab = $GLOBALS['conn_dico']->GetAll($requete);
						$TABLE_MERE = $res_aggreg_tab[0]['TABLE_MERE'];
						$ID_TABLE = $res_aggreg_tab[0]['ID_TABLE'];
						$sql_donnees_table = $this->get_sql_donnees_table($TABLE_MERE,$table['ID_TABLE']); 
						$sql_tables = array();
						$sql_tables = explode('#',$sql_donnees_table);
						foreach($sql_tables as $sql_tab){
							if(preg_match("/ ".$res_aggreg_tab[0]['NOM_TABLE']."[\.]/".$GLOBALS['PARAM']['CODE_ETABLISSEMENT'],$sql_tab)){
								$requete 	=	' SELECT * 
													FROM DICO_AGGREGATED_FIELD
													WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_TABLE =\''.$res_aggreg_tab[0]['NOM_TABLE'].'\' AND EXPORT = 1 
													ORDER BY ORDRE';
								$fields  = $GLOBALS['conn_dico']->GetAll($requete);
								$tab = array();
								foreach($fields as $chp){
									if(!preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$chp['NOM_CHAMP']))
										$tab['data_entry_fields'][] = $chp['NOM_CHAMP'];
								}
								$result_sql_tab	= $GLOBALS['conn']->GetAll($sql_tab);
								//echo "<br><br><pre>";
								//print_r($result_sql_tab);
								$NUM_LIGNES = explode(',',$table['NUM_LIGNES']);
								$lignes = array();
								foreach($NUM_LIGNES as $lig){
									if($lig <> ''){
										$lignes[] = $lig;
									}else{
										$lignes[]=0;
									}
								}
								$NUM_COLONNES = explode(',',$table['NUM_COLONNES']);
								$colonnes = array();
								foreach($NUM_COLONNES as $col){
									if($col <> ''){
										$colonnes[] = $col;
									}else{
										$colonnes[]=0;
									}
								}
								$table['NUM_LIGNES'] = implode(',',$lignes);
								$table['NUM_COLONNES'] = implode(',',$colonnes);
								eval("\$rows_excel = array(".$table['NUM_LIGNES'].");");
								eval("\$cols_excel = array(".$table['NUM_COLONNES'].");");
								
								if($res_aggreg_tab[0]['TABLE_REF_ROW']<>''){
									$tab['tab_ref_row_dim'] = $res_aggreg_tab[0]['TABLE_REF_ROW'];
									$res_aggreg_tab[0]['SQL_ROW'] = str_replace('SELECT '.$res_aggreg_tab[0]['TABLE_REF_ROW'].'.'.$GLOBALS['PARAM']['LIBELLE'],'SELECT '.$res_aggreg_tab[0]['TABLE_REF_ROW'].'.'.$GLOBALS['PARAM']['CODE'],$res_aggreg_tab[0]['SQL_ROW']);
									$res_aggreg_tab[0]['SQL_ROW'] = str_replace('SELECT '.$GLOBALS['PARAM']['LIBELLE'],'SELECT '.$GLOBALS['PARAM']['CODE'],$res_aggreg_tab[0]['SQL_ROW']);
									$res = $GLOBALS['conn']->GetAll($res_aggreg_tab[0]['SQL_ROW']);
									foreach($res as $rs){
										$tab['records_tab_ref_row_dim'][] = $rs[$GLOBALS['PARAM']['CODE'].'_'.$res_aggreg_tab[0]['TABLE_REF_ROW']];
									}
									if(is_array($tab['records_tab_ref_row_dim'])) $tab['records_tab_ref_row_dim'] = array_diff($tab['records_tab_ref_row_dim'], array('255'));
									$tab['chp_pere_row_dim'] = $res_aggreg_tab[0]['CHAMP_PERE_ROW'];
								}
								
								if($res_aggreg_tab[0]['TABLE_REF_COL']<>''){
									$tab['tab_ref_col_dim'] = $res_aggreg_tab[0]['TABLE_REF_COL'];
									$res_aggreg_tab[0]['SQL_COL'] = str_replace('SELECT '.$res_aggreg_tab[0]['TABLE_REF_COL'].'.'.$GLOBALS['PARAM']['LIBELLE'],'SELECT '.$res_aggreg_tab[0]['TABLE_REF_COL'].'.'.$GLOBALS['PARAM']['CODE'],$res_aggreg_tab[0]['SQL_COL']);
									$res_aggreg_tab[0]['SQL_COL'] = str_replace('SELECT '.$GLOBALS['PARAM']['LIBELLE'],'SELECT '.$GLOBALS['PARAM']['CODE'],$res_aggreg_tab[0]['SQL_COL']);
									$res = $GLOBALS['conn']->GetAll($res_aggreg_tab[0]['SQL_COL']);
									foreach($res as $rs){
										$tab['records_tab_ref_col_dim'][] = $rs[$GLOBALS['PARAM']['CODE'].'_'.$res_aggreg_tab[0]['TABLE_REF_COL']];
									}
									if(is_array($tab['records_tab_ref_col_dim'])) $tab['records_tab_ref_col_dim'] = array_diff($tab['records_tab_ref_col_dim'], array('255'));
									$tab['chp_pere_col_dim'] = $res_aggreg_tab[0]['CHAMP_PERE_COL'];
								}
								
								////////////////////////////
								//matrice_dim_lig_col
								///////////////////////////
								if(isset($tab['records_tab_ref_row_dim']) && isset($tab['records_tab_ref_col_dim'])){
									$j = 0;
									if(is_array($tab['records_tab_ref_col_dim'])){
										foreach($tab['records_tab_ref_col_dim'] as $code_col) {
											$k = 0;
											if(is_array($tab['records_tab_ref_row_dim'])){
												foreach($tab['records_tab_ref_row_dim'] as $code) {
													$empty_row = true;
													$ligne = array();
													$i = 0;
													foreach($result_sql_tab as $rs){
														if($rs[$tab['chp_pere_col_dim']] == $code_col 
															&& $rs[$tab['chp_pere_row_dim']] == $code){
															$empty_row = false;
															$ligne = $rs;
															break;
														}
													}
													if(!$empty_row){	
														foreach ($tab['data_entry_fields'] as $data_field) {
															if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
																$selcell = $this->sheet->cells($rows_excel[$k],$cols_excel[$j]);	
																$selcell->value=round($ligne[$data_field]);
															}
															$i++;
															$j++;
															$k++;
														}
													}else{
														foreach ($tab['data_entry_fields'] as $data_field) {
															if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
																$selcell = $this->sheet->cells($rows_excel[$k],$cols_excel[$j]);	
																$selcell->value='';
															}
															$i++;
															$j++;
															$k++;
														}
													}
													$j -= $i;
												}
											}
											$j += $i;
										}
									}
								}
								////////////////////////////
								//matrice_dim_lig
								///////////////////////////
								elseif(isset($tab['records_tab_ref_row_dim'])){
									$i=0;
									if(is_array($tab['records_tab_ref_row_dim'])){
										foreach($tab['records_tab_ref_row_dim'] as $code) {
											$empty_row = true;
											$ligne = array();
											foreach($result_sql_tab as $rs){
												if($rs[$tab['chp_pere_row_dim']] == $code){
													$empty_row = false;
													$ligne = $rs;
													break;
												}
											}
											if(!$empty_row){	
												foreach ($tab['data_entry_fields'] as $data_field) {
													if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
														$selcell = $this->sheet->cells($rows_excel[$i],$cols_excel[$i]);	
														$selcell->value=round($ligne[$data_field]);
													}
													$i++;
												}
											}else{
												foreach ($tab['data_entry_fields'] as $data_field) {
													if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
														$selcell = $this->sheet->cells($rows_excel[$i],$cols_excel[$i]);	
														$selcell->value='';
													}
													$i++;
												}
											}
										}
									}
								}
								////////////////////////////
								//matrice_dim_col
								///////////////////////////
								elseif(isset($tab['records_tab_ref_col_dim'])){
									$i=0;
									if(is_array($tab['records_tab_ref_col_dim'])){
										foreach($tab['records_tab_ref_col_dim'] as $code_col) {
											$empty_row = true;
											$ligne = array();
											//echo "<br><br><pre>";
											//print_r($result_sql_tab);
											foreach($result_sql_tab as $rs){
												if($rs[$tab['chp_pere_col_dim']] == $code_col){
													$empty_row = false;
													$ligne = $rs;
													break;
												}
											}
											
											if(!$empty_row){	
												foreach ($tab['data_entry_fields'] as $data_field) {
													if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
														$selcell = $this->sheet->cells($rows_excel[$i],$cols_excel[$i]);	
														$selcell->value=round($ligne[$data_field]);
													}
													$i++;
												}
											}else{
												foreach ($tab['data_entry_fields'] as $data_field) {
													if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
														$selcell = $this->sheet->cells($rows_excel[$i],$cols_excel[$i]);	
														$selcell->value='';
													}
													$i++;
												}
											}
										}
									}
								}
								////////////////////////////
								//formulaire
								///////////////////////////
								else{
									$empty_row = true;
									$ligne = array();
									foreach($result_sql_tab as $rs){
										foreach ($tab['data_entry_fields'] as $data_field) {
											if($rs[$data_field] <> ''){
												$empty_row = false;
												$ligne = $rs;
												break;
											}
										}
									}
									if(!$empty_row){
										$k=0;
										foreach ($tab['data_entry_fields'] as $data_field) {
											if($rows_excel[$k]<>0 && $cols_excel[$k]<>0){
												$selcell = $this->sheet->cells($rows_excel[$k],$cols_excel[$k]);	
												if(is_integer($ligne[$data_field])) $selcell->value=round($ligne[$data_field]);
												else $selcell->value=$ligne[$data_field];
											}
											$k++;
										}
									}else{
										$k=0;
										foreach ($tab['data_entry_fields'] as $data_field) {
											if($rows_excel[$k]<>0 && $cols_excel[$k]<>0){
												$selcell = $this->sheet->cells($rows_excel[$k],$cols_excel[$k]);	
												$selcell->value='';
											}
											$k++;
										}
									}
								}
							}
						}
					}
				}
				$this->export_excel_requete();
				if(file_exists($FILENAME2)) unlink($FILENAME2);
				$this->book->SaveAs($FILENAME2);
				unset($this->sheet);
				unset($this->book);
				$this->excel->Workbooks->Close();
				$this->excel->Quit();
				unset($this->excel);
			}else{
				echo "<script type=\"text/javascript\">\n";
				echo "\t alert('".$this->recherche_libelle_page('no_etabs',$_SESSION['langue'],'export.php')."');\n";
				echo "</script>\n";
			}          
			fin_popup_progress();
		}
		
		//Exportation donnees Requete
		function export_excel_requete(){
			
			$requete = "SELECT DICO_EXCEL_AGGREGATED_TABLE.* FROM DICO_EXCEL_AGGREGATED_TABLE, DICO_EXCEL_REQUETE_ASSOC WHERE DICO_EXCEL_AGGREGATED_TABLE.NOM_TABLE_2 = DICO_EXCEL_REQUETE_ASSOC.NOM_REQUETE AND DICO_EXCEL_AGGREGATED_TABLE.ID_SYSTEME = ".$_SESSION['secteur']." ORDER BY DICO_EXCEL_AGGREGATED_TABLE.ORDRE_FORM";
			$rs_aggreg_table = $GLOBALS['conn_dico']->GetAll($requete);
			if(is_array($rs_aggreg_table) && count($rs_aggreg_table)>0)
			foreach($rs_aggreg_table as $table){
				$this->sheet=$this->book->Worksheets($table['NOM_PAGE']);
				$requete = "SELECT * FROM DICO_EXCEL_REQUETE_ASSOC WHERE ID_SYSTEME = ".$_SESSION['secteur']." AND NOM_REQUETE = '".$table['NOM_TABLE_2']."'";
				$res_aggreg_tab = $GLOBALS['conn_dico']->GetAll($requete);
				$sql_donnees_table = $this->get_sql_donnees_requete($table['NOM_TABLE_2']); 
				$sql_tab = $sql_donnees_table;
				if(preg_match("/ ".$res_aggreg_tab[0]['NOM_REQUETE']."[\.]".$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'/',$sql_tab)){
					$requete 	=	' SELECT * 
										FROM DICO_EXCEL_REQ_ASSOC_FIELDS
										WHERE ID_SYSTEME ='.$_SESSION['secteur'].' AND NOM_REQUETE =\''.$res_aggreg_tab[0]['NOM_REQUETE'].'\' AND EXPORT = 1 
										ORDER BY ORDRE';
					$fields  = $GLOBALS['conn_dico']->GetAll($requete);
					$tab = array();
					foreach($fields as $chp){
						if($chp['TYPE_DIM'] == '')
							$tab['data_entry_fields'][] = $chp['NOM_CHAMP'];
					}
					$result_sql_tab	= $GLOBALS['conn']->GetAll($sql_tab);
					//echo "<br><br><pre>";
					//print_r($sql_tab);
					$NUM_LIGNES = explode(',',$table['NUM_LIGNES']);
					$lignes = array();
					foreach($NUM_LIGNES as $lig){
						if($lig <> ''){
							$lignes[] = $lig;
						}else{
							$lignes[]=0;
						}
					}
					$NUM_COLONNES = explode(',',$table['NUM_COLONNES']);
					$colonnes = array();
					foreach($NUM_COLONNES as $col){
						if($col <> ''){
							$colonnes[] = $col;
						}else{
							$colonnes[]=0;
						}
					}
					$table['NUM_LIGNES'] = implode(',',$lignes);
					$table['NUM_COLONNES'] = implode(',',$colonnes);
					eval("\$rows_excel = array(".$table['NUM_LIGNES'].");");
					eval("\$cols_excel = array(".$table['NUM_COLONNES'].");");
					
					foreach($fields as $chp){
						if($chp['TYPE_DIM'] == 'dimension_ligne'){
							$tab['tab_ref_row_dim'] = $chp['TABLE_REF'];
							$res = $GLOBALS['conn']->GetAll($chp['DIM_SQL']);
							foreach($res as $rs){
								$tab['records_tab_ref_row_dim'][] = $rs[$GLOBALS['PARAM']['CODE'].'_'.$chp['TABLE_REF']];
							}
							$tab['records_tab_ref_row_dim'] = array_diff($tab['records_tab_ref_row_dim'], array('255'));
						}
						if($chp['TYPE_DIM'] == 'dimension_colonne'){
							$tab['tab_ref_col_dim'] = $chp['TABLE_REF'];
							$res = $GLOBALS['conn']->GetAll($chp['DIM_SQL']);
							foreach($res as $rs){
								$tab['records_tab_ref_col_dim'][] = $rs[$GLOBALS['PARAM']['CODE'].'_'.$chp['TABLE_REF']];
							}
							$tab['records_tab_ref_col_dim'] = array_diff($tab['records_tab_ref_col_dim'], array('255'));
						}
					}
					
					////////////////////////////
					//matrice_dim_lig_col
					///////////////////////////
					if(isset($tab['records_tab_ref_row_dim']) && isset($tab['records_tab_ref_col_dim'])){
						$j = 0;
						foreach($tab['records_tab_ref_col_dim'] as $code_col) {
							$k = 0;
							foreach($tab['records_tab_ref_row_dim'] as $code) {
								$empty_row = true;
								$ligne = array();
								$i = 0;
								foreach($result_sql_tab as $rs){
									if($rs[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] == $code_col 
										&& $rs[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] == $code){
										$empty_row = false;
										$ligne = $rs;
										break;
									}
								}
								if(!$empty_row){	
									foreach ($tab['data_entry_fields'] as $data_field) {
										if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
											$selcell = $this->sheet->cells($rows_excel[$k],$cols_excel[$j]);	
											$selcell->value=round($ligne[$data_field]);
										}
										$i++;
										$j++;
										$k++;
									}
								}else{
									foreach ($tab['data_entry_fields'] as $data_field) {
										if($rows_excel[$k]<>0 && $cols_excel[$j]<>0){
											$selcell = $this->sheet->cells($rows_excel[$k],$cols_excel[$j]);	
											$selcell->value='';
										}
										$i++;
										$j++;
										$k++;
									}
								}
								$j -= $i;
							}
							$j += $i;
						}
					}
					////////////////////////////
					//matrice_dim_lig
					///////////////////////////
					elseif(isset($tab['records_tab_ref_row_dim'])){
						$i=0;
						foreach($tab['records_tab_ref_row_dim'] as $code) {
							$empty_row = true;
							$ligne = array();
							foreach($result_sql_tab as $rs){
								if($rs[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_row_dim']] == $code){
									$empty_row = false;
									$ligne = $rs;
									break;
								}
							}
							if(!$empty_row){	
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
										$selcell = $this->sheet->cells($rows_excel[$i],$cols_excel[$i]);	
										$selcell->value=round($ligne[$data_field]);
									}
									$i++;
								}
							}else{
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
										$selcell = $this->sheet->cells($rows_excel[$i],$cols_excel[$i]);	
										$selcell->value='';
									}
									$i++;
								}
							}
						}
					}
					////////////////////////////
					//matrice_dim_col
					///////////////////////////
					elseif(isset($tab['records_tab_ref_col_dim'])){
						$i=0;
						foreach($tab['records_tab_ref_col_dim'] as $code_col) {
							$empty_row = true;
							$ligne = array();
							foreach($result_sql_tab as $rs){
								if($rs[$GLOBALS['PARAM']['CODE'].'_'.$tab['tab_ref_col_dim']] == $code_col){
									$empty_row = false;
									$ligne = $rs;
									break;
								}
							}
							if(!$empty_row){	
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
										$selcell = $this->sheet->cells($rows_excel[$i],$cols_excel[$i]);	
										$selcell->value=round($ligne[$data_field]);
									}
									$i++;
								}
							}else{
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($rows_excel[$i]<>0 && $cols_excel[$i]<>0){
										$selcell = $this->sheet->cells($rows_excel[$i],$cols_excel[$i]);	
										$selcell->value='';
									}
									$i++;
								}
							}
						}
					}
					////////////////////////////
					//formulaire
					///////////////////////////
					else{
						$empty_row = true;
						$ligne = array();
						if(is_array($result_sql_tab)){
							foreach($result_sql_tab as $rs){
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($rs[$data_field] <> ''){
										$empty_row = false;
										$ligne = $rs;
										break;
									}
								}
							}
						}
						if(!$empty_row){
							$k=0;
							if(is_array($tab['data_entry_fields'])){
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($rows_excel[$k]<>0 && $cols_excel[$k]<>0){
										$selcell = $this->sheet->cells($rows_excel[$k],$cols_excel[$k]);	
										if(is_integer($ligne[$data_field])) $selcell->value=round($ligne[$data_field]);
										else $selcell->value=$ligne[$data_field];
									}
									$k++;
								}
							}
						}else{
							$k=0;
							if(is_array($tab['data_entry_fields'])){
								foreach ($tab['data_entry_fields'] as $data_field) {
									if($rows_excel[$k]<>0 && $cols_excel[$k]<>0){
										$selcell = $this->sheet->cells($rows_excel[$k],$cols_excel[$k]);	
										$selcell->value='';
									}
									$k++;
								}
							}
						}
					}
				}
			}
		}
		//Fin Exportation Donnees Requete
			
		//Recuperation des donnees de config
		function get_dico(){
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
			if(isset($GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE']) && count($GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE'])>0){
				$cpt = 0;
				foreach($GLOBALS['PARAM']['GENERER_THEMES_PENDANT_SAISIE'] as $id_th){
					if($cpt == 0) $themes_saisie .= '('.$id_th;
					else $themes_saisie .= ','.$id_th;
					$cpt++;
				}
				$themes_saisie .= ')';
			}
			if($themes_saisie<>'')
			$requete  = "SELECT DICO_THEME.* 
						 FROM DICO_THEME, DICO_THEME_SYSTEME 
						 WHERE DICO_THEME.ID = DICO_THEME_SYSTEME.ID AND DICO_THEME_SYSTEME.ID_THEME_SYSTEME NOT IN $themes_saisie AND DICO_THEME_SYSTEME.ID_SYSTEME = $id_systeme AND DICO_THEME.ID_TYPE_THEME NOT IN (1,5,6,7,8)".";";
			else
			$requete  = "SELECT DICO_THEME.* 
						 FROM DICO_THEME, DICO_THEME_SYSTEME 
						 WHERE DICO_THEME.ID = DICO_THEME_SYSTEME.ID AND DICO_THEME_SYSTEME.ID_SYSTEME = $id_systeme AND DICO_THEME.ID_TYPE_THEME NOT IN (1,5,6,7,8)".";";
			$result_themes	= $GLOBALS['conn_dico']->GetAll($requete);
			$tables = array();
			if(is_array($result_themes)){
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
								$curobj_grille->set_code_nomenclature();
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
					if($_SESSION['curobj_theme']->type_theme <> 4 || $_SESSION['curobj_theme']->type_gril_eff_fix_col){
						foreach($_SESSION['curobj_theme']->val_cle as $nom_tab => $tab){
							$chps_val_cle = array();
							$chps_val_cle[] = $GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
							$chps_val_cle[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
							$chps_val_cle[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
							if($GLOBALS['PARAM']['FILTRE'] && $GLOBALS['PARAM']['TYPE_FILTRE']<>'')
								$chps_val_cle[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
							$this->tab_avec_fixe_val[$nom_tab] = array();
							if(is_array($tab)){
								foreach($tab as $nom_chp => $val_chp){
									if(!in_array($nom_chp,$chps_val_cle)){
										if(!in_array($nom_chp, $this->tab_avec_fixe_val[$nom_tab])) 
											$this->tab_avec_fixe_val[$nom_tab][] = $nom_chp;
									}
								}
							}
						}
						
						foreach($_SESSION['curobj_theme']->tableau_zone_saisie as $nom_tab => $tab){
							if(!in_array($nom_tab, $this->liste_tab)){
								$this->liste_tab[] = $nom_tab;
								foreach($tab as $chp){
									if((isset($chp['type'])) && ($chp['type']<>'c') && ($chp['type']<>'l') && ($chp['type']<>'loc_etab') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
										if(($chp['type']<>'tvm') && ($chp['type']<>'b') && ($chp['ss_type']<>'b')) $this->table_champ_fils[$nom_tab][$chp['champ']] = $chp['table_ref'];
									}
									if(($chp['type']=='dim_col') || ($chp['type']=='dim_lig')){
										if(($chp['type']=='dim_lig')){
											$tables[$nom_tab]['tab_ref_row_dim'] = $chp['table_ref'];
											if(!in_array($nom_tab, $this->tab_mat_dim_lig))
												$this->tab_mat_dim_lig[] = $nom_tab;
										}
										if(($chp['type']=='dim_col')){
											$tables[$nom_tab]['tab_ref_col_dim'] = $chp['table_ref'];
											if(!in_array($nom_tab, $this->tab_mat_dim_col))
												$this->tab_mat_dim_col[] = $nom_tab;
										}
									}
									if(($chp['type']=='tvm')){
										$tables[$nom_tab]['tab_ref_col_dim'] = $chp['table_ref'];
										if(!in_array($nom_tab, $this->tab_mat_dim_col))
											$this->tab_mat_dim_col[] = $nom_tab;
									}
									if(($chp['type']=='li_ch')){
										$tables[$nom_tab]['tab_ref_col_dim'] = $chp['table_ref'];
										if(!in_array($nom_tab, $this->tab_mat_dim_col))
											$this->tab_mat_dim_col[] = $nom_tab;
										$this->tab_mat_liste_checkbox[] = $nom_tab;
									}
									if((isset($chp['type']) && $chp['type']=='ch') || (isset($chp['type']) && $chp['type']=='b') || (isset($chp['ss_type']) && $chp['ss_type']=='ch') || (isset($chp['ss_type']) && $chp['ss_type']=='b')){
										$this->tab_with_checkbox[$nom_tab][] = $chp['champ'];
									}
								}
							}
						}
						
						//cas de grille_eff : recherche dimension colonne
						if(($_SESSION['curobj_theme']->type_theme == 4)){
							$curobj_theme = $_SESSION['curobj_theme'];
							foreach($_SESSION['curobj_theme']->nomtableliee as $nom_tab){
								if(!isset($tables[$nom_tab]['tab_ref_col_dim'])){
									foreach($curobj_theme->nomtableliee as $tab){
										if(isset($tables[$tab]['tab_ref_col_dim'])){
											if(!in_array($nom_tab, $this->tab_mat_dim_col))
												$this->tab_mat_dim_col[] = $nom_tab;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		//un champ de type nomenclature : code_type_..., alias de code_type_...
		function is_nomenc_field($table,$champ){
			if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($champ)) || 
				(isset($this->table_champ_fils[$table][$champ]) && $this->table_champ_fils[$table][$champ]<>'')){
				return true;
			}
			return false;
		}
		
		/**
		* METHODE : get_sql_donnees_table($table,id_table)
		* <pre>
		*  récupère le sql de récupération des données de la table $table
		* </pre>
		* @access public
		* 
		*/
		function get_sql_donnees_table($table,$id_table){
			// Récupération des données de la table en conformité 
			// avec $schema_table et de $this->tab_filtres
			
			$champs_filtre = array();
			$champs_filtre[] = $GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
			$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
			if($GLOBALS['PARAM']['FILTRE']) $champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
			$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
			$schema_table = $this->get_schema_table($table);
			$req = "SELECT NOM_CHAMP, VAL_CHAMP 
					FROM DICO_EXCEL_AGGREG_TAB_FIXE_VAL 
					WHERE ID_SYSTEME = ".$_SESSION['secteur']." AND ID_TABLE =".$id_table.";";
			$rs_champs_fixe_val	= $GLOBALS['conn_dico']->GetAll($req);
			$k = 0;
			$il_ya_chp_mes_type_cpte = false;
			if(in_array($table,$this->tab_mat_dim_lig) || in_array($table,$this->tab_mat_dim_col)){
				
				$req_table  = 'SELECT ';
				if(is_array($schema_table['champs'])){
					foreach($schema_table['champs'] as $i => $champ){
						if((isset($this->tab_mat_liste_checkbox) && in_array($table,$this->tab_mat_liste_checkbox)) || 
							(isset($this->tab_with_checkbox[$table]) && in_array($champ['nom_champ'],$this->tab_with_checkbox[$table]))){
							$il_ya_chp_mes_type_cpte = true;
							break;
						}
					}
				}
				if($il_ya_chp_mes_type_cpte){
					foreach($schema_table['champs'] as $i => $champ){
						if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
							(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
							(!in_array(trim($champ['nom_champ']),$this->tab_avec_fixe_val[$table]))){
							if($k == 0) $req_table  .= $table.'.'.$champ['nom_champ'];
							else $req_table  .= ', '.$table.'.'.$champ['nom_champ'];
							$k++;
						}
					}
					if(in_array($table,$this->tab_mat_dim_lig) && in_array($table,$this->tab_mat_dim_col)){
						foreach($schema_table['champs'] as $i => $champ){
							if((!$this->is_nomenc_field($table,$champ['nom_champ'])) && 
								(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
								(!in_array(trim($champ['nom_champ']),$this->tab_avec_fixe_val[$table])) && 
								($champ['type_champ'] == 'integer')){
								$champ_cpte = trim($champ['nom_champ']);
								$req_table  .= ', Count('.$table.'.'.$champ['nom_champ'].') AS '.$GLOBALS['PARAM']['NB'].'_'.$champ_cpte;
							}
						}
					}else{
						foreach($schema_table['champs'] as $i => $champ){
							if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
								(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
								(!in_array(trim($champ['nom_champ']),$this->tab_avec_fixe_val[$table]))){
								if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($champ['nom_champ'])))
									$champ_cpte = substr(trim($champ['nom_champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
								else
									$champ_cpte = trim($champ['nom_champ']);
								$req_table  .= ', Count('.$table.'.'.$champ['nom_champ'].') AS '.$GLOBALS['PARAM']['NB'].'_'.$champ_cpte;
							}
						}
					}
				}else{
					if(is_array($schema_table['champs'])){
						foreach($schema_table['champs'] as $i => $champ){
							if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
								(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
								(!in_array(trim($champ['nom_champ']),$this->tab_avec_fixe_val[$table]))){
								if($k == 0) $req_table  .= $table.'.'.$champ['nom_champ'];
								else $req_table  .= ', '.$table.'.'.$champ['nom_champ'];
								$k++;
							}
						}
					}
					if(is_array($schema_table['champs'])){
						foreach($schema_table['champs'] as $i => $champ){
							if((!$this->is_nomenc_field($table,$champ['nom_champ'])) && 
								(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
								(!in_array(trim($champ['nom_champ']),$this->tab_avec_fixe_val[$table])) && 
								($champ['type_champ'] == 'integer')){
								if($k == 0) $req_table  .= 'Sum('.$table.'.'.$champ['nom_champ'].') AS '.$champ['nom_champ'];
								else $req_table  .= ', '.'Sum('.$table.'.'.$champ['nom_champ'].') AS '.$champ['nom_champ'];
								$k++;
							}
						}
					}
				}

				$req_table  .= ' FROM '.$table;
	
				$pass = 0;
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
					$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $table, array(0 => array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']=> $this->tab_filtres['secteur'])));
					$pass++;
				}
				
				if(is_array($rs_champs_fixe_val) && count($rs_champs_fixe_val)>0){
					foreach($rs_champs_fixe_val as $champ){
						($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
						$req_table	.=	$this->get_criteres_where($champ['NOM_CHAMP'], $table, array(0 => array($champ['NOM_CHAMP']=> $champ['VAL_CHAMP'])));
						$pass++;
					}
				}
				
				$req_table .= ' GROUP BY ';
				$k = 0;
				if(is_array($schema_table['champs'])){
					foreach($schema_table['champs'] as $i => $champ){
						if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
							(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
							(!in_array(trim($champ['nom_champ']),$this->tab_avec_fixe_val[$table]))){
							if($k == 0) $req_table  .= $table.'.'.$champ['nom_champ'];
							else $req_table  .= ', '.$table.'.'.$champ['nom_champ'];
							$k++;
						}
					}
				}
			}else{

				$req_table = '';
				
				$k = 0;
				if($table == $GLOBALS['PARAM']['ETABLISSEMENT']){
					$requete='SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG 
								FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
								WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$this->tab_filtres['regs'][0];            
					$lib_reg = $GLOBALS['conn']->GetOne($requete);
					
					$req_table .= 'SELECT '.'\''.addslashes($lib_reg).'\''.' AS '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
					$req_table .= ', '.count($this->liste_etabs_filtres).' AS '.$GLOBALS['PARAM']['NB'].'_'.$GLOBALS['PARAM']['ETABLISSEMENT'];
					$k++;
				}
				
				if(is_array($schema_table['champs'])){
					foreach($schema_table['champs'] as $i => $champ){
						if((!$this->is_nomenc_field($table,$champ['nom_champ'])) && 
							(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
							(!in_array(trim($champ['nom_champ']),$this->tab_avec_fixe_val[$table])) && 
							($champ['type_champ'] == 'integer')){
							if((isset($this->tab_with_checkbox[$table]) && in_array($champ['nom_champ'],$this->tab_with_checkbox[$table]))){
								$chp_nb = $GLOBALS['PARAM']['NB'].'_';
							}else{
								$chp_nb = '';
							}
							if($k == 0) $req_table  .= 'SELECT Sum('.$table.'.'.$champ['nom_champ'].') AS '.$chp_nb.$champ['nom_champ'];
							else $req_table  .= ', '.'Sum('.$table.'.'.$champ['nom_champ'].') AS '.$chp_nb.$champ['nom_champ'];
							$k++;
						}
					}
				}
								
				if($req_table<>''){
				
					$req_table  .= ' FROM '.$table;
		
					$pass = 0;
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
						$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $table, array(0 => array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']=> $this->tab_filtres['secteur'])));
						$pass++;
					}

					if(is_array($rs_champs_fixe_val) && count($rs_champs_fixe_val)>0){
						foreach($rs_champs_fixe_val as $champ){
							($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
							$req_table	.=	$this->get_criteres_where($champ['NOM_CHAMP'], $table, array(0 => array($champ['NOM_CHAMP']=> $champ['VAL_CHAMP'])));
							$pass++;
						}
					}
				}
				if(is_array($schema_table['champs'])){
					foreach($schema_table['champs'] as $i => $champ){
						if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
							(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
							(!in_array(trim($champ['nom_champ']),$this->tab_avec_fixe_val[$table]))){
							if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($champ['nom_champ']))){
								$champ_cpte = substr(trim($champ['nom_champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
								$nomenc = substr(trim($champ['nom_champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
							}else{
								$champ_cpte = trim($champ['nom_champ']);
								$nomenc = trim($champ['nom_champ']);
							}
							$alias_table = $table.'_'.$nomenc;
							if($k == 0) $req_table  .= 'SELECT ';
							else  $req_table  .= '# SELECT ';
							$req_table  .= $alias_table.'.'.$champ['nom_champ'];
							$req_table  .= ', Count('.$alias_table.'.'.$champ['nom_champ'].') AS '.$GLOBALS['PARAM']['NB'].'_'.$champ_cpte;
							$req_table  .= ' FROM '.$table.' AS '.$alias_table;
							
							$pass = 0;
							if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $schema_table) ){
								($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
								$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $alias_table, $this->liste_etabs_filtres);	
								$pass++;
							}
							if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $schema_table)){
								($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
								$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $alias_table, $this->tab_filtres['annees']);
								$pass++;
							}
							if($GLOBALS['PARAM']['FILTRE']){
								if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $schema_table)){
									($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
									$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $alias_table, $this->tab_filtres['filtres']);
									$pass++;
								}
							}
							if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $schema_table)){
								($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
								$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $alias_table, array(0 => array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']=> $this->tab_filtres['secteur'])));
								$pass++;
							}
							
							if(is_array($rs_champs_fixe_val) && count($rs_champs_fixe_val)>0){
								foreach($rs_champs_fixe_val as $champ){
									($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
									$req_table	.=	$this->get_criteres_where($champ['NOM_CHAMP'], $table, array(0 => array($champ['NOM_CHAMP']=> $champ['VAL_CHAMP'])));
									$pass++;
								}
							}
							
							$req_table .= ' GROUP BY ';
							$req_table  .= $alias_table.'.'.$champ['nom_champ'];
							$k++;
						}
					}
				}
			}
			//echo '<br>'.$req_table.'<br>';die;
			return($req_table);
		}
		
		/**
		* METHODE : get_sql_donnees_requete($req_aggreg)
		* <pre>
		*  récupère le sql de récupération des données de la requete $req_aggreg
		* </pre>
		* @access public
		* 
		*/
		function get_sql_donnees_requete($req_aggreg){
			// Récupération des données de la table en conformité 
			// avec $schema_table et de $this->tab_filtres
			
			$champs_filtre = array();
			$champs_filtre[] = $GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
			$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
			if($GLOBALS['PARAM']['FILTRE']) $champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
			$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
			$schema_table = $this->get_schema_table($req_aggreg);
				
			$req_table = "SELECT ";
			$k = 0;
			if(is_array($schema_table['champs'])){
				foreach($schema_table['champs'] as $i => $champ){
					if(($this->is_nomenc_field($req_aggreg,$champ['nom_champ'])) && 
						!in_array(trim($champ['nom_champ']),$champs_filtre)){
						if($k == 0) $req_table  .= $req_aggreg.'.'.$champ['nom_champ'];
						else $req_table  .= ', '.$req_aggreg.'.'.$champ['nom_champ'];
						$k++;
					}
				}
			}
			if(is_array($schema_table['champs'])){
				foreach($schema_table['champs'] as $i => $champ){
					if((!$this->is_nomenc_field($req_aggreg,$champ['nom_champ'])) && 
						(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
						($champ['type_champ'] == 'integer')){
						if($k == 0) $req_table  .= 'Sum('.$req_aggreg.'.'.$champ['nom_champ'].') AS '.$champ['nom_champ'];
						else $req_table  .= ', '.'Sum('.$req_aggreg.'.'.$champ['nom_champ'].') AS '.$champ['nom_champ'];
						$k++;
					}
				}
			}
			
			$req_table .= " FROM $req_aggreg ";
			
			$pass = 0;
			if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $schema_table) ){
				($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
				$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $req_aggreg, $this->liste_etabs_filtres);	
				$pass++;
			}
			if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $schema_table)){
				($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
				$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $req_aggreg, $this->tab_filtres['annees']);
				$pass++;
			}
			if($GLOBALS['PARAM']['FILTRE']){
				if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $schema_table)){
					($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
					$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $req_aggreg, $this->tab_filtres['filtres']);
					$pass++;
				}
			}
			if($this->exist_champ_in_schema($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $schema_table)){
				($pass == 0) ? ($req_table .= ' WHERE ') : ($req_table .= ' AND ') ;
				$req_table	.=	$this->get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], $req_aggreg, array(0 => array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']=> $this->tab_filtres['secteur'])));
				$pass++;
			}
			
			$req_table .= ' GROUP BY ';
			$k = 0;
			if(is_array($schema_table['champs'])){
				foreach($schema_table['champs'] as $i => $champ){
					if(($this->is_nomenc_field($req_aggreg,$champ['nom_champ'])) && 
						(!in_array(trim($champ['nom_champ']),$champs_filtre))){
						if($k == 0) $req_table  .= $req_aggreg.'.'.$champ['nom_champ'];
						else $req_table  .= ', '.$req_aggreg.'.'.$champ['nom_champ'];
						$k++;
					}
				}
			}
			//echo '<br>'.$req_table.'<br>';
			return($req_table);
		}
		
}	
		
?>
