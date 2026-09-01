<?php class aggregated_db_structure{
	
			
		/**
		* Attribut : $conn
		* <pre>
		* 	Variable de connexion à la base
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn;
			
		/**
		* Attribut : $langue
		* <pre>
		* 	Langue choisie
		* </pre>
		* @var 
		* @access public
		*/   
		public $langue;
			
		/**
		* Attribut : $nomPage
		* <pre>
		* 	Nom de la page à transmettre à la fonction lit_libelles_page
		* </pre>
		* @var 
		* @access public
		*/   
		public $nomPage;
			
		/**
		* Attribut : $id_theme
		* <pre>
		* 	le thème en cours
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_theme; // 
			
		/**
		* Attribut : $id_type_theme
		* <pre>
		* 	Type du Thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_type_theme;
			
		/**
		* Attribut : $libelle_type_theme
		* <pre>
		* 	libellé du type thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $libelle_type_theme;
			
		/**
		* Attribut : $libelle_theme
		* <pre>
		* 	libellé du thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $libelle_theme;
			
		/**
		* Attribut : $lib_champ_err
		* <pre>
		* libellé associé au champ en cas d'erreur
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_champ_err;
			
		/**
		* Attribut : $order_zones
		* <pre>
		* booléen utilisé pour permettre ou non d'ordonner 
		* les zones par secteur
		* </pre>
		* @var 
		* @access public
		*/   
		public $order_zones = false;
			
		/**
		* Attribut :  $iTab
		* <pre>
		* indice tableau de la table mère  ds TabGestZones['tables_meres']
		* </pre>
		* @var 
		* @access public
		*/   
		public $iTab;  // 
			
		/**
		* Attribut : $iZone
		* <pre>
		* indice tableau de la zone  ds TabGestZones['zones'][$iTab]
		* </pre>
		* @var 
		* @access public
		*/   
		public $iZone;  // // 
		
		/**
		* Attribut : $id_Zone
		* <pre>
		* id de la zone  ds TabGestZones['zones'][$iTab] pour modification
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_zone;  // // 
		
		/**
		* Attribut : $i_tabm
		* <pre>
		* indice de la table mere  ds TabGestZones['zones'] pour modification
		* </pre>
		* @var 
		* @access public
		*/   
		public $i_tabm;	
		
		/**
		* Attribut : $nb_TabM
		* <pre>
		* le nombre total de table mères
		* </pre>
		* @var 
		* @access public
		*/   
		public $nb_TabM;  // // 
			
		/**
		* Attribut : $Action
		* <pre>
		* contient le type d'action à effectuer aprés soumission
		* </pre>
		* @var 
		* @access public
		*/   
		public $Action; // 
			
		/**
		* Attribut : $OkAction
		* <pre>
		* indique le resultat de l'action de MAJ ds la base
		* </pre>
		* @var 
		* @access public
		*/   
		public $OkAction; // 
			
		/**
		* Attribut : $ActMAJ
		* <pre>
		* indique la tenue d'une action de MAJ
		* </pre>
		* @var 
		* @access public
		*/   
		public $ActMAJ;  // 
			
		/**
		* Attribut : $champs_zone
		* <pre>
		* les noms des champs relatifs à la ZONE
		* </pre>
		* @var 
		* @access public
		*/   
		public $champs_zone 	= array(); // 
			
		/**
		* Attribut : $btn_add 
		* <pre>
		* controler l'affichage du bouton Ajouter
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_add 			= false; // 
			
		/**
		* Attribut : $TabValTabM 
		* <pre>
		* Vals tables mères pour l'affichage
		* </pre>
		* @var 
		* @access public
		*/   
		public $TabValTabM 		= array(); // 
			
		/**
		* Attribut : $TabValZone
		* <pre>
		* Vals zones pour l'affichage
		* </pre>
		* @var 
		* @access public
		*/   
		public $TabValZone		= array(); // 
			
		/**
		* Attribut : $TabGestZones
		* <pre>
		* Contient plusieurs valeurs recuperees de la base pour la manip des zones
		* </pre>
		* @var 
		* @access public
		*/   
		public $TabGestZones 	= array(); // 
		
		/**
		* Attribut : $TabBD
		* <pre>
		* Contient les tables de la base de données
		* </pre>
		* @var 
		* @access public
		*/		
		public $TabBD 	= array(); //
		
		/**
		* Attribut : $ColTabBD
		* <pre>
		* Contient les colonnes d'une table de la base de données
		* </pre>
		* @var 
		* @access public
		*/		
		public $ColTabBD 	= array(); //
		
		/**
		* Attribut : $liste_tables
		* <pre>
		*  liste des tables de données à exporter
		* </pre>
		* @var 
		* @access public
		*/   
		public $liste_tables = array();
		public $db_structure_defaut = array();
		public $tab_mat_dim_lig = array();
		public $tab_mat_dim_col = array();
		public $tab_mat_liste_checkbox = array();
		public $tab_liste_radio = array();
		public $tab_ref_liste_radio = array();
		public $liste_tab = array();
		public $tab_with_checkbox = array();
		public $table_champ_fils = array();
		public $tables = array();
		public $db_tables_config_details = array();
		public $tab_avec_fixe_val = array();
		public $tab_not_matricielle = array();
		/**
		* METHODE : 
		* <pre>
		* Constructeur de la classe
		* </pre>
		* @access public
		* 
		*/
		function __construct(){
			$this->conn			= $GLOBALS['conn'];
			$this->langue    	= $_SESSION['langue'];
			$this->nomPage		= '/aggregated_db_structure.php';
			
		}
		
		/**
		* METHODE : init()
		* <pre>
		* Enchaine les fonctions dans le bon ordre de traitement de l'objet instancié
		* </pre>
		* @access public
		* 
		*/
		function init(){
			$this->btn_add 	= false;

			lit_libelles_page($this->nomPage);
			$this->printJS();
			$this->get_aggregated_tables();
			if(count($this->TabGestZones['tables_meres'])==0){
				$this->set_dico_aggregated_db();
			}
			if ($_GET['todo']=='upd'){ 
				$this->update_dico_aggregated_db();
    		}
			if ($_GET['todo']=='res'){
			 	$this->set_dico_aggregated_db();
    		}
			$this->get_aggregated_tables();
			$this->gererAff();			
		}
		
		function __wakeup(){
			$this->conn	= $GLOBALS['conn'];
		}
		
		/**
		* METHODE : printJS()
		* <pre>
		* Affiche les fonctions javascript
		* </pre>
		* @access public
		* 
		*/
		function printJS(){
			?>
			<script type=text/javascript>
			 <!-- 
			
				 function AvertirSupp(iTab,iZone,cas_elem,lib_elem){ 
					var TxtAvertSuppZone ="<?php echo $this->TabGestZones['TxtAlert']['AvSupZone']; ?>"; 
					var TxtAvertSuppTabM ="<?php echo $this->TabGestZones['TxtAlert']['AvSupTabM']; ?>"; 
					if(cas_elem=='Zone'){ 
						var TxtAvert = TxtAvertSuppZone + lib_elem ; 
						var Act   = 'SupZone'; 
					} 
					else if(cas_elem=='TabM'){ 
						var TxtAvert = TxtAvertSuppTabM + lib_elem ; 
						var Act   = 'SupTabM'; 
					} 
					if(confirm(TxtAvert)){ 
						Action(iTab,iZone,cas_elem,Act); 
					} 
				 } 
			
				function MessMAJ(action,res_action,lib_champ_err){ 
					//var i = 0; 
					//alert (action + '***' + res_action + '***' + lib_champ_err)
					var lib_champ = '';
					if(action=='AddZone' || action=='AddTabM' ) i = 0; 
					else if(action=='UpdZone' || action=='UpdTabM' ) i = 1;
					else if(action=='SupZone' || action=='SupTabM' ) i = 2									
					if(lib_champ_err != '') lib_champ = ' : (' + lib_champ_err + ')';									
					OK00 = " <?php echo $this->TabGestZones['TxtAlert']['PbIns']; ?> "; 
					OK01 = " <?php echo $this->TabGestZones['TxtAlert']['OkIns']; ?> "; 
					OK10 = " <?php echo $this->TabGestZones['TxtAlert']['PbUpd']; ?> "; 
					OK11 = " <?php echo $this->TabGestZones['TxtAlert']['OkUpd']; ?> "; 
					OK20 = " <?php echo $this->TabGestZones['TxtAlert']['PbSup']; ?> "; 
					OK21 = " <?php echo $this->TabGestZones['TxtAlert']['OkSup']; ?> ";

					var OK	= Array([OK00,OK01],[OK10,OK11],[OK20,OK21]); 
					alert(OK[i][res_action] + lib_champ);
				}
			 //--> 
				function changer_action(todo){
					document.forms['Formulaire'].action = "administration.php?val=aggregated_db_structure&todo="+todo ;
					//alert(document.forms['Formulaire'].action);
				}
				function recharger(){
					document.location.href = "administration.php?val=aggregated_db_structure";
				}
				var do_submit = true;
				function do_post(form_name){
					if( do_submit == true ){
						eval('document.'+form_name+'.submit();');
						$('<img id="page_loading" class="page_loading" src="<?php echo $GLOBALS["SISED_URL_IMG"]; ?>loading.gif" />').prependTo('form');
					}
				}			
			</script>
			<img id="page_loading" class="page_loading" src="<?php echo $GLOBALS["SISED_URL_IMG"]; ?>loading.gif" />
		<?php }
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
			$strRequete = "";
			if ($GLOBALS['conn_dico']->databaseType=='access') {
				$strRequete = "SELECT DICO_THEME_SYSTEME.ID_THEME_SYSTEME, DICO_THEME.ID_TYPE_THEME, DICO_THEME_SYSTEME.ID, DICO_THEME_SYSTEME.PERE, DICO_THEME_SYSTEME.PRECEDENT".
						" INTO TEMP_THEME FROM DICO_THEME_SYSTEME , DICO_THEME  ".
						" Where DICO_THEME.ID = DICO_THEME_SYSTEME.ID  AND  DICO_THEME_SYSTEME.ID_SYSTEME=" . $_SESSION['secteur'];
			} else {
			
				$strRequete = "CREATE TABLE TEMP_THEME AS 
							SELECT DICO_THEME_SYSTEME.ID_THEME_SYSTEME, DICO_THEME.ID_TYPE_THEME, DICO_THEME_SYSTEME.ID, DICO_THEME_SYSTEME.PERE, DICO_THEME_SYSTEME.PRECEDENT".
						" FROM DICO_THEME_SYSTEME , DICO_THEME  ".
						" Where DICO_THEME.ID = DICO_THEME_SYSTEME.ID  AND  DICO_THEME_SYSTEME.ID_SYSTEME=" . $_SESSION['secteur'];
			}			
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
		}
		
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
				/*echo "curobj_theme<pre>";
				print_r($_SESSION['curobj_theme']);*/
				if($_SESSION['curobj_theme']->type_theme <> 4 || $_SESSION['curobj_theme']->type_gril_eff_fix_col){
					foreach($_SESSION['curobj_theme']->val_cle as $nom_tab => $tab){
						$chps_val_cle = array();
						$chps_val_cle[] = $GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
						$chps_val_cle[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
						$chps_val_cle[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
						if($GLOBALS['PARAM']['FILTRE'] && $GLOBALS['PARAM']['TYPE_FILTRE']<>'')
							$chps_val_cle[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
						$j = 0;
						//echo "$nom_tab<pre>";
						//print_r($tab);
						if(is_array($tab))
						foreach($tab as $nom_chp => $val_chp){
							if(!in_array($nom_chp,$chps_val_cle)){
								if(!in_array($nom_tab, $this->tab_avec_fixe_val)) $this->tab_avec_fixe_val[] = $nom_tab;
								$this->tables[$nom_tab]['fixe_val'][$nom_chp][] = $val_chp;
								if($j==0){
									//$this->tables[$nom_tab]['fixe_val']['cpte']++;
									$this->tables[$nom_tab]['fixe_val']['cpte'] = $val_chp;
									$this->tables[$nom_tab]['fixe_val']['val_chp'][] = $val_chp;
								}
								$j++;
							}
						}
					}
					
					foreach($_SESSION['curobj_theme']->tableau_zone_saisie as $nom_tab => $tab){
						//if(!in_array($nom_tab, $this->liste_tab)){
							//$this->liste_tab[] = $nom_tab;
							//echo $nom_tab.'<br>';
							foreach($tab as $chp){
								if((isset($chp['type'])) && ($chp['type']<>'c') && ($chp['type']<>'l') && ($chp['type']<>'loc_etab') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
									if(($chp['type']<>'tvm') && ($chp['type']<>'b') && ($chp['ss_type']<>'b')) $this->table_champ_fils[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'][$chp['champ']] = $chp['table_ref'];
								}
								if(($chp['type']=='dim_col') || ($chp['type']=='dim_lig')){
									if(($chp['type']=='dim_lig')){
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_row_dim'] = $chp['table_ref'];
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['chp_pere_row_dim'] = $chp['champ'];
										
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['id_zone_row_dim'] = $chp['id_zone'];
										$chp['sql'] = strtoupper($chp['sql']);
										$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
										$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
										$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
										$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
										$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['sql_row_dim'] = $chp['sql'];
										
										if(!in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')', $this->tab_mat_dim_lig))
											$this->tab_mat_dim_lig[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')';
									}
									if(($chp['type']=='dim_col')){
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_col_dim'] = $chp['table_ref'];
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['chp_pere_col_dim'] = $chp['champ'];
										
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['id_zone_col_dim'] = $chp['id_zone'];
										$chp['sql'] = strtoupper($chp['sql']);
										$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
										$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
										$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
										$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
										$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['sql_col_dim'] = $chp['sql'];
										
										if(!in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')', $this->tab_mat_dim_col))
											$this->tab_mat_dim_col[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')';
									}
								}
								if(($chp['type']=='tvm')){
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_col_dim'] = $chp['table_ref'];
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['chp_pere_col_dim'] = $chp['champ'];
									
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['id_zone_col_dim'] = $chp['id_zone'];
									$chp['sql'] = strtoupper($chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
									$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['sql_col_dim'] = $chp['sql'];
									
									if(!in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')', $this->tab_mat_dim_col))
										$this->tab_mat_dim_col[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')';
								}
								if(($chp['type']=='li_ch')){
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_col_dim'] = $chp['table_ref'];
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['chp_pere_col_dim'] = $chp['champ'];
									
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['id_zone_col_dim'] = $chp['id_zone'];
									$chp['sql'] = strtoupper($chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
									$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['sql_col_dim'] = $chp['sql'];
									
									if(!in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')', $this->tab_mat_dim_col))
										$this->tab_mat_dim_col[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')';
									$this->tab_mat_liste_checkbox[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')';
								}
								if(($chp['type']=='m') || ($chp['type']=='co')){
									$nom_cpte = $chp['champ'];
									if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($chp['champ']))){
										$nomenc = substr(trim($chp['champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
									}else{
										$nomenc = trim($chp['champ']);
									}
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc]['tab_ref_col_dim'] = $chp['table_ref'];
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc]['chp_pere_col_dim'] = $chp['champ'];
									
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc]['id_zone_col_dim'] = $chp['id_zone'];
									$chp['sql'] = strtoupper($chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
									$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc]['sql_col_dim'] = $chp['sql'];
									
									if(!in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc, $this->tab_mat_dim_col))
										$this->tab_mat_dim_col[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc;
									$this->tab_liste_radio[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')';
									$this->tab_ref_liste_radio[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'][] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc;
								}
								//todo:Gerer le cas type zone_mat
								/*if(($chp['type']=='zone_mat') && (($chp['ss_type']=='m') || ($chp['ss_type']=='co'))){
									$nom_cpte = $chp['champ'];
									if(ereg('^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$',trim($chp['champ']))){
										$nomenc = substr(trim($chp['champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
									}else{
										$nomenc = trim($chp['champ']);
									}
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc]['tab_ref_col_dim'] = $chp['table_ref'];
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc]['chp_pere_col_dim'] = $chp['champ'];
									
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc]['id_zone_col_dim'] = $chp['id_zone'];
									$chp['sql'] = strtoupper($chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['CODE'],'SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$chp['table_ref'].'.*','SELECT '.$chp['table_ref'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT '.$GLOBALS['PARAM']['CODE'],'SELECT '.$GLOBALS['PARAM']['LIBELLE'],$chp['sql']);
									$chp['sql'] = str_replace('SELECT *','SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$chp['table_ref'],$chp['sql']);
									$chp['sql'] = str_replace(' AS '.$chp['champ'],'',$chp['sql']);
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc]['sql_col_dim'] = $chp['sql'];
									
									if(!in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc, $this->tab_mat_dim_col))
										$this->tab_mat_dim_col[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc;
									$this->tab_liste_radio[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')';
									$this->tab_ref_liste_radio[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'][] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'.'_'.$nomenc;
								}*/
								if((isset($chp['type']) && $chp['type']=='ch') || (isset($chp['type']) && $chp['type']=='b') || (isset($chp['ss_type']) && $chp['ss_type']=='ch') || (isset($chp['ss_type']) && $chp['ss_type']=='b')){
									$this->tab_with_checkbox[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'][] = $chp['champ'];
								}
							}
							if(!in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')', $this->tab_mat_dim_lig) && !in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')', $this->tab_mat_dim_col) && !in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')', $this->tab_not_matricielle))
								$this->tab_not_matricielle[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')';
						//}
					}
	
					foreach($_SESSION['curobj_theme']->code_nomenclature as $nom_tab => $tab){
						foreach($tab as $zone_tab => $records){
							$records_minus_val_indet = array_diff($records, array('255'));
							if($zone_tab == $this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['id_zone_row_dim'].'_'.$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_row_dim']){
								$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['records_tab_ref_row_dim'] = $records_minus_val_indet;
								$res = $GLOBALS['conn']->GetAll($this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['sql_row_dim']);
								if(is_array($res))
								foreach($res as $tb){
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['name_records_tab_ref_row_dim'][] = $tb[$GLOBALS['PARAM']['LIBELLE'].'_'.$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_row_dim']];
								} 
							}
							if($zone_tab == $this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['id_zone_col_dim'].'_'.$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_col_dim']){
								$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['records_tab_ref_col_dim'] = $records_minus_val_indet;
								$res = $GLOBALS['conn']->GetAll($this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['sql_col_dim']);
								if(is_array($res))
								foreach($res as $tb){
									$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['name_records_tab_ref_col_dim'][] = $tb[$GLOBALS['PARAM']['LIBELLE'].'_'.$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_col_dim']];
								}
							}
							if(in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')',$this->tab_liste_radio)){
								foreach($this->tab_ref_liste_radio[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')'] as $alias){
									if($zone_tab == $this->tables[$alias]['id_zone_col_dim'].'_'.$this->tables[$alias]['tab_ref_col_dim']){
										$this->tables[$alias]['records_tab_ref_col_dim'] = $records_minus_val_indet;
										$res = $GLOBALS['conn']->GetAll($this->tables[$alias]['sql_col_dim']);
										if(is_array($res))
										foreach($res as $tb){
											$this->tables[$alias]['name_records_tab_ref_col_dim'][] = $tb[$GLOBALS['PARAM']['LIBELLE'].'_'.$this->tables[$alias]['tab_ref_col_dim']];
										}
									}
								}
							}
						}
					}
				
					//cas de grille_eff : recherche dimension colonne
					if(($_SESSION['curobj_theme']->type_theme == 4)){
						$curobj_theme = $_SESSION['curobj_theme'];
						foreach($_SESSION['curobj_theme']->nomtableliee as $nom_tab){
							if(!isset($this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_col_dim'])){
								foreach($curobj_theme->nomtableliee as $tab){
									if(isset($this->tables[$tab.'('.$this->tables[$tab]['fixe_val']['cpte'].')']['tab_ref_col_dim'])){
										
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['tab_ref_col_dim'] = $this->tables[$tab.'('.$this->tables[$tab]['fixe_val']['cpte'].')']['tab_ref_col_dim'];
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['records_tab_ref_col_dim'] = $this->tables[$tab.'('.$this->tables[$tab]['fixe_val']['cpte'].')']['records_tab_ref_col_dim'];
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['name_records_tab_ref_col_dim'] = $this->tables[$tab.'('.$this->tables[$tab]['fixe_val']['cpte'].')']['name_records_tab_ref_col_dim'];
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['sql_col_dim'] = $this->tables[$tab.'('.$this->tables[$tab]['fixe_val']['cpte'].')']['sql_col_dim'];
										
										$this->tables[$nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')']['chp_pere_col_dim'] = $this->tables[$tab.'('.$this->tables[$tab]['fixe_val']['cpte'].')']['chp_pere_col_dim'];
										
										if(!in_array($nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')', $this->tab_mat_dim_col))
											$this->tab_mat_dim_col[] = $nom_tab.'('.$this->tables[$nom_tab]['fixe_val']['cpte'].')';
									}
								}
							}
						}
					}
				}
			}
			/*echo '<pre>';
			print_r($this->tables);*/
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
		
		
		/**
		* METHODE : get_sql_donnees_table($table)
		* <pre>
		*  récupère le sql de récupération des données de la table $table
		* </pre>
		* @access public
		* 
		*/
		function get_dico_aggregated_db($table0){
			$champs_filtre = array();
			$champs_filtre[] = $GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
			$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
			if($GLOBALS['PARAM']['FILTRE']) $champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'];
			$champs_filtre[] =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
			$schema_table = $this->get_schema_table($table0);
			//echo $table0."<pre><br>";
			if(isset($this->tables[$table0]['fixe_val']['cpte']) && count($this->tables[$table0]['fixe_val']['val_chp'])>0){//Table avec fixed value
				foreach($this->tables[$table0]['fixe_val']['val_chp'] as $v){
					$table = $table0.'('.$v.')';
					$il_ya_chp_mes_type_cpte = false;
					if(in_array($table,$this->tab_mat_dim_lig) || in_array($table,$this->tab_mat_dim_col)){
						
						if(in_array($table,$this->tab_mat_dim_lig)){
							$this->db_tables_config_details[$table]['sql_row_dim'] = $this->tables[$table]['sql_row_dim'];
							$this->db_tables_config_details[$table]['name_records_tab_ref_row_dim'] = $this->tables[$table]['name_records_tab_ref_row_dim'];
						}
						if(in_array($table,$this->tab_mat_dim_col)){
							$this->db_tables_config_details[$table]['sql_col_dim'] = $this->tables[$table]['sql_col_dim'];
							$this->db_tables_config_details[$table]['name_records_tab_ref_col_dim'] = $this->tables[$table]['name_records_tab_ref_col_dim'];
						}
						
						foreach($schema_table['champs'] as $i => $champ){
							if((isset($this->tab_mat_liste_checkbox) && in_array($table,$this->tab_mat_liste_checkbox)) || 
								(isset($this->tab_with_checkbox[$table]) && in_array($champ['nom_champ'],$this->tab_with_checkbox[$table]))){
								$il_ya_chp_mes_type_cpte = true;
								break;
							}
						}
						if($il_ya_chp_mes_type_cpte){
							foreach($schema_table['champs'] as $i => $champ){
								if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
									(!in_array(trim($champ['nom_champ']),$champs_filtre))){
									$this->db_structure_defaut[$table][] = $champ['nom_champ'];
								}
							}
							if(in_array($table,$this->tab_mat_dim_lig) && in_array($table,$this->tab_mat_dim_col)){
								foreach($schema_table['champs'] as $i => $champ){
									if((!$this->is_nomenc_field($table,$champ['nom_champ'])) && 
										(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
										($champ['type_champ'] == 'integer')){
										$champ_cpte = trim($champ['nom_champ']);
										$this->db_structure_defaut[$table][] = $GLOBALS['PARAM']['NB'].'_'.$champ_cpte;
									}
								}
							}else{
								foreach($schema_table['champs'] as $i => $champ){
									if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
										(!in_array(trim($champ['nom_champ']),$champs_filtre))){
										if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($champ['nom_champ'])))
											$champ_cpte = substr(trim($champ['nom_champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
										else
											$champ_cpte = trim($champ['nom_champ']);
										$this->db_structure_defaut[$table][] = $GLOBALS['PARAM']['NB'].'_'.$champ_cpte;
									}
								}
							}
						}else{
							foreach($schema_table['champs'] as $i => $champ){
								if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
									(!in_array(trim($champ['nom_champ']),$champs_filtre))){
									$this->db_structure_defaut[$table][] = $champ['nom_champ'];
								}
							}
							foreach($schema_table['champs'] as $i => $champ){
								if((!$this->is_nomenc_field($table,$champ['nom_champ'])) && 
									(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
									($champ['type_champ'] == 'integer')){
									$this->db_structure_defaut[$table][] = $champ['nom_champ'];
								}
							}
						}
					}else{
						if($table0 == $GLOBALS['PARAM']['ETABLISSEMENT']){
							$this->db_structure_defaut[$table][] =$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
							$this->db_structure_defaut[$table][] = $GLOBALS['PARAM']['NB'].'_'.$GLOBALS['PARAM']['ETABLISSEMENT'];
						}
						foreach($schema_table['champs'] as $i => $champ){
							if((!$this->is_nomenc_field($table,$champ['nom_champ'])) && 
								(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
								($champ['type_champ'] == 'integer')){
								if((isset($this->tab_with_checkbox[$table]) && in_array($champ['nom_champ'],$this->tab_with_checkbox[$table]))){
									$chp_nb = $GLOBALS['PARAM']['NB'].'_';
								}else{
									$chp_nb = '';
								}
								$this->db_structure_defaut[$table][] = $chp_nb.$champ['nom_champ'];
							}
						}
						foreach($schema_table['champs'] as $i => $champ){
							if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
								(!in_array(trim($champ['nom_champ']),$champs_filtre))){
								if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($champ['nom_champ']))){
									$champ_cpte = substr(trim($champ['nom_champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
									$nomenc = substr(trim($champ['nom_champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
								}else{
									$champ_cpte = trim($champ['nom_champ']);
									$nomenc = trim($champ['nom_champ']);
								}
								$alias_table = $table.'_'.$nomenc;
								$this->db_structure_defaut[$alias_table][] = $champ['nom_champ'];
								$this->db_structure_defaut[$alias_table][] = $GLOBALS['PARAM']['NB'].'_'.$champ_cpte;
								$this->db_tables_config_details[$alias_table]['sql_col_dim'] = $this->tables[$alias_table]['sql_col_dim'];
								$this->db_tables_config_details[$alias_table]['name_records_tab_ref_col_dim'] = $this->tables[$alias_table]['name_records_tab_ref_col_dim'];
								$this->db_tables_config_details[$alias_table]['table_mere'] = $table;
							}
						}
					}
					//echo '<pre>';
					//print_r($this->db_structure_defaut);
				}
			}else{//Table sans fixed value
				$table = $table0.'()';
				$il_ya_chp_mes_type_cpte = false;
				if(in_array($table,$this->tab_mat_dim_lig) || in_array($table,$this->tab_mat_dim_col)){
					
					if(in_array($table,$this->tab_mat_dim_lig)){
						$this->db_tables_config_details[$table]['sql_row_dim'] = $this->tables[$table]['sql_row_dim'];
						$this->db_tables_config_details[$table]['name_records_tab_ref_row_dim'] = $this->tables[$table]['name_records_tab_ref_row_dim'];
					}
					if(in_array($table,$this->tab_mat_dim_col)){
						$this->db_tables_config_details[$table]['sql_col_dim'] = $this->tables[$table]['sql_col_dim'];
						$this->db_tables_config_details[$table]['name_records_tab_ref_col_dim'] = $this->tables[$table]['name_records_tab_ref_col_dim'];
					}
					
					foreach($schema_table['champs'] as $i => $champ){
						if((isset($this->tab_mat_liste_checkbox) && in_array($table,$this->tab_mat_liste_checkbox)) || 
							(isset($this->tab_with_checkbox[$table]) && in_array($champ['nom_champ'],$this->tab_with_checkbox[$table]))){
							$il_ya_chp_mes_type_cpte = true;
							break;
						}
					}
					if($il_ya_chp_mes_type_cpte){
						foreach($schema_table['champs'] as $i => $champ){
							if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
								(!in_array(trim($champ['nom_champ']),$champs_filtre))){
								$this->db_structure_defaut[$table][] = $champ['nom_champ'];
							}
						}
						if(in_array($table,$this->tab_mat_dim_lig) && in_array($table,$this->tab_mat_dim_col)){
							foreach($schema_table['champs'] as $i => $champ){
								if((!$this->is_nomenc_field($table,$champ['nom_champ'])) && 
									(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
									($champ['type_champ'] == 'integer')){
									$champ_cpte = trim($champ['nom_champ']);
									$this->db_structure_defaut[$table][] = $GLOBALS['PARAM']['NB'].'_'.$champ_cpte;
								}
							}
						}else{
							foreach($schema_table['champs'] as $i => $champ){
								if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
									(!in_array(trim($champ['nom_champ']),$champs_filtre))){
									if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($champ['nom_champ'])))
										$champ_cpte = substr(trim($champ['nom_champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
									else
										$champ_cpte = trim($champ['nom_champ']);
									$this->db_structure_defaut[$table][] = $GLOBALS['PARAM']['NB'].'_'.$champ_cpte;
								}
							}
						}
					}else{
						foreach($schema_table['champs'] as $i => $champ){
							if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
								(!in_array(trim($champ['nom_champ']),$champs_filtre))){
								$this->db_structure_defaut[$table][] = $champ['nom_champ'];
							}
						}
						foreach($schema_table['champs'] as $i => $champ){
							if((!$this->is_nomenc_field($table,$champ['nom_champ'])) && 
								(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
								($champ['type_champ'] == 'integer')){
								$this->db_structure_defaut[$table][] = $champ['nom_champ'];
							}
						}
					}
				}elseif(in_array($table,$this->tab_not_matricielle)){
					if($table0 == $GLOBALS['PARAM']['ETABLISSEMENT']){
						$this->db_structure_defaut[$table][] =$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
						$this->db_structure_defaut[$table][] = $GLOBALS['PARAM']['NB'].'_'.$GLOBALS['PARAM']['ETABLISSEMENT'];
					}
					foreach($schema_table['champs'] as $i => $champ){
						if((!$this->is_nomenc_field($table,$champ['nom_champ'])) && 
							(!in_array(trim($champ['nom_champ']),$champs_filtre)) && 
							($champ['type_champ'] == 'integer')){
							if((isset($this->tab_with_checkbox[$table]) && in_array($champ['nom_champ'],$this->tab_with_checkbox[$table]))){
								$chp_nb = $GLOBALS['PARAM']['NB'].'_';
							}else{
								$chp_nb = '';
							}
							$this->db_structure_defaut[$table][] = $chp_nb.$champ['nom_champ'];
						}
					}
					foreach($schema_table['champs'] as $i => $champ){
						if(($this->is_nomenc_field($table,$champ['nom_champ'])) && 
							(!in_array(trim($champ['nom_champ']),$champs_filtre))){
							if(preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',trim($champ['nom_champ']))){
								$champ_cpte = substr(trim($champ['nom_champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
								$nomenc = substr(trim($champ['nom_champ']),strlen($GLOBALS['PARAM']['CODE'])+1);
							}else{
								$champ_cpte = trim($champ['nom_champ']);
								$nomenc = trim($champ['nom_champ']);
							}
							$alias_table = $table.'_'.$nomenc;
							$this->db_structure_defaut[$alias_table][] = $champ['nom_champ'];
							$this->db_structure_defaut[$alias_table][] = $GLOBALS['PARAM']['NB'].'_'.$champ_cpte;
							$this->db_tables_config_details[$alias_table]['sql_col_dim'] = $this->tables[$alias_table]['sql_col_dim'];
							$this->db_tables_config_details[$alias_table]['name_records_tab_ref_col_dim'] = $this->tables[$alias_table]['name_records_tab_ref_col_dim'];
							$this->db_tables_config_details[$alias_table]['table_mere'] = $table;
						}
					}
				}
			}
		}
		
		function set_dico_aggregated_db(){
			$this->get_dico();
			$this->set_liste_tables(); // appel de la fonction set_liste_tables_ord(); 
			$i = 0;
			foreach($this->liste_tables as $table){
				$this->get_dico_aggregated_db($table);
			}
			
			$req = "DELETE FROM DICO_AGGREGATED_FIELD WHERE ID_SYSTEME = ".$_SESSION['secteur'];
			$res = $GLOBALS['conn_dico']->Execute($req);
			$req = "DELETE FROM DICO_AGGREGATED_TABLE WHERE ID_SYSTEME = ".$_SESSION['secteur'];
			$res = $GLOBALS['conn_dico']->Execute($req);
			$i = 1;
			$ID_SYSTEME =  $_SESSION['secteur'];
			foreach($this->db_structure_defaut as $nom_table => $table){
				$table_ref_row = "";
				$chp_pere_row = "";
				$table_sql_row = "";
				$table_records_row = "";
				if(isset($this->db_tables_config_details[$nom_table]['sql_row_dim']) && $this->db_tables_config_details[$nom_table]['sql_row_dim']<>''){
					$table_ref_row = $this->tables[$nom_table]['tab_ref_row_dim'];
					$chp_pere_row = $this->tables[$nom_table]['chp_pere_row_dim'];
					$table_sql_row = $this->db_tables_config_details[$nom_table]['sql_row_dim'];
					$k = 0;
					if(is_array($this->db_tables_config_details[$nom_table]['name_records_tab_ref_row_dim'])){
						foreach($this->db_tables_config_details[$nom_table]['name_records_tab_ref_row_dim'] as $rec){
							if($k == 0)	$table_records_row .= $rec;
							else $table_records_row .= '#'.$rec;
							$k++;
						}
					}
				}
				$table_ref_col = "";
				$chp_pere_col = "";
				$table_sql_col = "";
				$table_records_col = "";
				if(isset($this->db_tables_config_details[$nom_table]['sql_col_dim']) && $this->db_tables_config_details[$nom_table]['sql_col_dim']<>''){
					$table_ref_col = $this->tables[$nom_table]['tab_ref_col_dim'];
					$chp_pere_col = $this->tables[$nom_table]['chp_pere_col_dim'];
					$table_sql_col = $this->db_tables_config_details[$nom_table]['sql_col_dim'];
					$k = 0;
					if(is_array($this->db_tables_config_details[$nom_table]['name_records_tab_ref_col_dim'])){
						foreach($this->db_tables_config_details[$nom_table]['name_records_tab_ref_col_dim'] as $rec){
							if($k == 0)	$table_records_col .= $rec;
							else $table_records_col .= '#'.$rec;
							$k++;
						}
					}
				}
				if(isset($this->db_tables_config_details[$nom_table]['table_mere'])){
					$table_mere = $this->db_tables_config_details[$nom_table]['table_mere'];
				}else{
					$table_mere = $nom_table;
				}
				$nom_table = str_replace('()','',$nom_table);
				
				$nom_table2 = $nom_table;
				$nom_table = preg_replace('/[\(](0|([1-9][0-9]*))[\)]/','',$nom_table);
				
				$table_mere = str_replace('()','',$table_mere);
				$table_mere = preg_replace('/[\(](0|([1-9][0-9]*))[\)]/','',$table_mere);
				
				$req = "INSERT INTO DICO_AGGREGATED_TABLE(ID_SYSTEME,NOM_TABLE,NOM_TABLE_2,TABLE_MERE,EXPORT,ORDRE,TABLE_REF_ROW,CHAMP_PERE_ROW,SQL_ROW,RECORDS_ROW,TABLE_REF_COL,CHAMP_PERE_COL,SQL_COL,RECORDS_COL) VALUES ($ID_SYSTEME,'$nom_table','$nom_table2','$table_mere',1,$i,'$table_ref_row','$chp_pere_row',".$GLOBALS['conn_dico']->qstr($table_sql_row).",".$GLOBALS['conn_dico']->qstr($table_records_row).",'$table_ref_col','$chp_pere_col',".$GLOBALS['conn_dico']->qstr($table_sql_col).",".$GLOBALS['conn_dico']->qstr($table_records_col).")";
				$res = $GLOBALS['conn_dico']->Execute($req);
				$i++;
				$j = 1;
				foreach($table as $nom_champ){
					$fixes_vals = "";
					if(isset($this->tables[$nom_table]['fixe_val'][$nom_champ])){
						$k = 0;
						foreach($this->tables[$nom_table]['fixe_val'][$nom_champ] as $val_chp){
							if($k == 0) $fixes_vals .= $val_chp;
							else $fixes_vals .= ",".$val_chp;
							$k++;
						}
					}	
					$req = "INSERT INTO DICO_AGGREGATED_FIELD(ID_SYSTEME,NOM_TABLE,NOM_CHAMP,NOM_CHAMP_2,EXPORT,ORDRE,FIXES_VALS) VALUES ($ID_SYSTEME,'$nom_table','$nom_champ','$nom_champ',1,$j,'$fixes_vals')";
					$res = $GLOBALS['conn_dico']->Execute($req);
					$j++;
				}
			}
		}
		
		/**
		* METHODE : get_tables_db()
		* <pre>
		* Récupération des tables de la base de données 
		* </pre>
		* @access public
		* 
		*/
		function get_tables_db(){
		
				$this->TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
				sort($this->TabBD);
				$tables = array();
				$deb_nomenc = $GLOBALS['PARAM']['TYPE'].'_';
				$deb_fact_tab = 'FACT_TABLE_';
				$fin_nomenc_system = '_'.$GLOBALS['PARAM']['SYSTEME'];
				foreach($this->TabBD as $tab)
				{
					if(!preg_match("/^($deb_fact_tab)/",strtoupper($tab)) && !preg_match("/^($deb_nomenc)/",strtoupper($tab)) && !preg_match("/($fin_nomenc_system)$/",strtoupper($tab)) && !preg_match("/^(DICO_)/",strtoupper($tab)) && !preg_match("/^(ADMIN_DROITS)/",strtoupper($tab)) && !preg_match("/^(ADMIN_GROUPES)/",strtoupper($tab)) && !preg_match("/^(ADMIN_USERS)/",strtoupper($tab)) && !preg_match("/^(PARAM_DEFAUT)/",strtoupper($tab)) && !preg_match("/^(SYSTEME)/",strtoupper($tab)))
					{
						$tables[] = $tab;
					}
				}
				$this->TabBD = $tables;
				
		} //FIN get_tables_db()
		
		
		/**
		* METHODE : get_col_table_db()
		* <pre>
		* Récupération des colonnes d'une table de la base de données 
		* </pre>
		* @access public
		* 
		*/
		function get_col_table_db($table){
		
				$this->ColTabBD	=	$this->conn->MetaColumnNames($table);
				
		} //FIN get_tables_db()
		
		function get_col_table_db_2($table){
		
				$this->ColTabBD	=	$this->conn->MetaColumns($table);
				
		} //FIN get_tables_db_2()
		
		/**
		* METHODE : get_tables_meres_theme()
		* <pre>
		* Récupération des tables mères du thème
		* </pre>
		* @access public
		* 
		*/
		function get_aggregated_tables(){
				$requete 	=	' SELECT    * 
									FROM DICO_AGGREGATED_TABLE
									WHERE ID_SYSTEME = '.$_SESSION['secteur'].' 
									ORDER BY ORDRE';
		
				$this->TabGestZones['tables_meres']  = $GLOBALS['conn_dico']->GetAll($requete);
				//Recuperation des requetes pour exportation donnees aggregees
				$requete= ' SELECT    * 
							FROM DICO_EXCEL_REQUETE_ASSOC
							WHERE ID_SYSTEME = '.$_SESSION['secteur'].' 
							ORDER BY ORDRE';
				$this->TabGestZones['requetes']  = $GLOBALS['conn_dico']->GetAll($requete);
				
				$this->nb_TabM = count($this->TabGestZones['tables_meres']) + count($this->TabGestZones['requetes']);
				
		} //FIN get_tables_meres_theme()

		/**
		* METHODE : recherche_libelle_bouton($code, $langue, $page)
		* <pre>
		*  permet de récupérer le libellé dans la table de traduction
		*  en fonction de la langue et de la table  aussi
		* </pre>
		* @access public
		* 
		*/
		function recherche_libelle_bouton($code, $langue, $page){
				// $page = 'generer_theme.php' ou gestion_zone.php
				$requete 	= " SELECT LIBELLE
								FROM DICO_LIBELLE_PAGE 
								WHERE CODE_LIBELLE='".$code."' AND CODE_LANGUE='".$this->langue."'
								AND NOM_PAGE='".$page."'";
												
				$all_res	= $GLOBALS['conn_dico']->GetAll($requete);                 
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
		
		/**
		* METHODE : recherche_libelle($code,$langue,$table)
		* <pre>
		* permet de récupérer le libellé dans la table de traduction
		* en fonction de la langue et de la table  aussi
		* </pre>
		* @access public
		* 
		*/
		function recherche_libelle($code,$langue,$table){
				
				if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					$conn                 =   $GLOBALS['conn'];
				} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					$conn                 =   $GLOBALS['conn_dico']; 
				}
				
				$requete 	= "SELECT LIBELLE
								FROM DICO_TRADUCTION 
								WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
								AND NOM_TABLE='".$table."'";
				
				//echo $requete;
				//print_r($this->conn);
				$all_res	= $conn->GetAll($requete); 
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
		
		function update_dico_aggregated_db(){
			//echo '<pre>';
			//print_r($_POST);
			if(is_array($this->TabGestZones['tables_meres'])){
				foreach($this->TabGestZones['tables_meres'] as $itab => $table){
						$nom_table2 = $_POST['NOM_TABLE_2_'.$itab];
						if(isset($_POST['EXPORT_'.$itab])) $export = $_POST['EXPORT_'.$itab];
						else $export = 0;
						$requete='UPDATE DICO_AGGREGATED_TABLE SET NOM_TABLE_2=\''.$nom_table2.'\', EXPORT='.$export.' WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND NOM_TABLE=\''.$table['NOM_TABLE'].'\'';
						if ($GLOBALS['conn_dico']->Execute($requete) === false){
							$GLOBALS['theme_data_MAJ_ok'] 	= false;
							print ' <br> error updating :<br> --- '.$requete.' --- <br>'; 
						}
				}
			}
			if(is_array($this->TabGestZones['requetes'])){
				foreach($this->TabGestZones['requetes'] as $itab => $table){
						$nom_req = $_POST['NOM_REQ_'.$itab];
						if(isset($_POST['EXPORT_R_'.$itab])) $export = $_POST['EXPORT_R_'.$itab];
						else $export = 0;
						$requete='UPDATE DICO_EXCEL_REQUETE_ASSOC SET NOM_REQUETE=\''.$nom_req.'\', EXPORT='.$export.' WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND ID_REQUETE='.$table['ID_REQUETE'];
						if ($GLOBALS['conn_dico']->Execute($requete) === false){
							$GLOBALS['theme_data_MAJ_ok'] 	= false;
							print ' <br> error updating :<br> --- '.$requete.' --- <br>'; 
						}
				}
			}
		}
		
		/**
		* METHODE : gererAff()
		* <pre>
		* fonction de gestion de l'affichage du Formulaire
		* </pre>
		* @access public
		* 
		*/
		function gererAff(){
		?>
				<FORM name="Formulaire"  method="post" action="administration.php?val=aggregated_db_structure">
				<br/>
				<br/>
				<br/>
				<DIV>
				<table style="width:60%;" align="center">
					<tr align="center"> 
						<td>
						<input style="width:400px;" type="button" name="btn_reset" <?php echo ' value="'.recherche_libelle_page('reset').'"'; ?> onclick="changer_action('res'); do_post('Formulaire');">
						</td>
					</tr>
				</table>
				<br/>
				<table style="width:60%;" align="center">
					<tr align="center"> 
						<td>
						<input style="width:400px;" type="button" name="btn_assoc_query" <?php echo ' value="'.recherche_libelle_page('assoc_query').'"'; ?> onclick="javascript:OpenPopupExcelAssocQuery();">
						</td>
					</tr>
				</table>
				<br/>
				<table align="center">
				<caption><?php echo recherche_libelle_page('GestAggregDB');?></caption>
				<tr>
						<?php if($this->id_type_theme <> 1){
						?>
						<td  align="right" valign="middle" rowspan="4">
								<SPAN class=""> 
									
									<div name="DivGestTablesMeres0" id="DivGestTablesMeres0">
											<table border="1">
													<tr> 
															<td align="center"><b><?php echo recherche_libelle_page('export'); ?></b></td>
															<td align="center"><b><?php echo recherche_libelle_page('Tab'); ?></b></td>
															<td align="center"><b><?php echo recherche_libelle_page('Fields'); ?></b></td>
													</tr>
													<?php if(is_array($this->TabGestZones['tables_meres']))
															foreach($this->TabGestZones['tables_meres'] as $iTab => $table_mere){
																if($table_mere['EXPORT']==1) $checked = ' CHECKED';
																else $checked = '';
																echo "
																	<tr id='TableMere".$iTab."' onClick=\"MiseEvidenceTablesMeres('".$iTab."')\"> 
																			<td>
																				<input type='checkbox' name='EXPORT_".$iTab."' id='EXPORT_".$iTab."' style='width:75px' value='1'".$checked."/>";
																echo "</td>
																			<td>
																				<input type='text' name='NOM_TABLE_2_".$iTab."' id='NOM_TABLE_2_".$iTab."' style='width:225px' value='".$table_mere['NOM_TABLE_2']."'/>";
																echo "</td>
																			<td><INPUT type='button' name='' value='".recherche_libelle_page('fields_desc').' '.$table_mere['NOM_TABLE_2']."' 
																			onclick=\"javascript:MiseEvidenceTablesMeres('".$iTab."'); javascript:OpenPopupFieldsTable('".$table_mere['NOM_TABLE']."','".$table_mere['NOM_TABLE_2']."');\" style='width:300px'/></td>
																	</tr>";		
															}
															if(is_array($this->TabGestZones['requetes']))
															foreach($this->TabGestZones['requetes'] as $iTab => $table_mere){
																if($table_mere['EXPORT']==1) $checked = ' CHECKED';
																else $checked = '';
																echo "
																	<tr id='Requete".$iTab."' onClick=\"MiseEvidenceTablesMeres('".$iTab."')\"> 
																			<td>
																				<input type='checkbox' name='EXPORT_R_".$iTab."' id='EXPORT_R_".$iTab."' style='width:75px' value='1'".$checked."/>";
																echo "</td>
																			<td>
																				<input type='text' name='NOM_REQ_".$iTab."' id='NOM_REQ_".$iTab."' style='width:225px' value='".$table_mere['NOM_REQUETE']."'/>";
																echo "</td>
																			<td><INPUT type='button' name='' value='".recherche_libelle_page('fields_desc').' '.$table_mere['NOM_REQUETE']."' 
																			onclick=\"javascript:MiseEvidenceTablesMeres('".$iTab."'); javascript:OpenPopupFieldsRequete('".$table_mere['NOM_REQUETE']."');\" style='width:300px'/></td>
																	</tr>";		
															}
															?>
													</table>
												</div>
												
										</SPAN>
								</td>
								<?php }?>
						</tr>
						
				</table>
				<br/>
				<table style="width:60%;" align="center">
					<tr> 
						<td align="center">
						<input style="width:200px;" type="button" name="btn_save" <?php echo ' value="'.recherche_libelle_page('save').'"'; ?> onclick="changer_action('upd'); do_post('Formulaire');"/>
						</td>
						<td align="center">
						<input style="width:150px;" type="button" name="refresh" <?php echo ' value="'.recherche_libelle_page('refresh').'"'; ?> onclick="recharger();"/>
						</td>
						<td align="center">
						<INPUT   style="width:150px;"  type="button" <?php echo 'value="'.recherche_libelle_page('fermer').'"';?> onClick="javascript:fermer();"/>
						</td>
					</tr>
				</table>
				<br/>
			</DIV>
			</FORM>
			<script type=text/javascript>
				$('#page_loading').remove();
			</script>
			<?php }
		
}	// fin class 
?>

