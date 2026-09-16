<?php class excel_db_structure{
	
	public $conn;
	public $excel_php_file;
	public $tables = array();
	public $lig_chp_ident = array();
	public $col_chp_ident = array();
	public $page_chp_ident = array();
	public $lig_code_etab = array();
	public $col_code_etab = array();
	public $tab_mat_dim_lig = array();
	public $tab_mat_dim_col = array();
	public $tab_mat_liste_checkbox = array();
	public $tab_theme_ordre;
	
	function __construct(){
		$this->conn			= $GLOBALS['conn'];
		$this->secteur    	= $_SESSION['secteur'];
		$this->tab_theme_ordre = array();
			
		$this->generer_excel_db_file();
	}
	
	function __wakeup(){
		$this->conn	= $GLOBALS['conn'];
	}
	
	function generer_excel_db_file(){
		if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>"")
			$this->ordonner_theme_appart($_SESSION['secteur']);
		else
			$this->ordonner_theme($_SESSION['secteur']);

		$this->ordonner_table();
		$this->ordonner_tables_excel($_SESSION['secteur']);
		$this->get_excel_ident_field();
		$this->get_tabms_matricielles();
		
		$this->excel_php_file = "<?php \n";
		$req_theme = "SELECT ID_THEME FROM DICO_EXCEL_TABLE WHERE ORDRE_TABLE = 1 AND ID_SYSTEME = ".$_SESSION['secteur'];
		$res_theme = $GLOBALS['conn_dico']->GetAll($req_theme);
		foreach($res_theme as $th){
			if($this->lig_chp_ident[$th['ID_THEME']] <> 0 && $this->col_chp_ident[$th['ID_THEME']] <> 0 && trim($this->page_chp_ident[$th['ID_THEME']]) <> ""){
				////Checking if District Name in District Identification Screen Form  is the same in Selected Excel file
				$this->excel_php_file .= "if( isset(\$_POST) && count(\$_POST) > 0 && \$_POST['id_theme_syst'] == ".$th['ID_THEME'].$_SESSION['secteur']." ){\n";
				$this->excel_php_file .= "	for(\$sheet=0;\$sheet<count(\$data->sheets);\$sheet++){\n";
				$this->excel_php_file .= "		if(strtoupper(\$data->boundsheets[\$sheet]['name'])=='".strtoupper($this->page_chp_ident[$th['ID_THEME']])."'){\n";
				$this->excel_php_file .= "			\$row_excel_nom_etab = ".$this->lig_chp_ident[$th['ID_THEME']].";//row\n";
				$this->excel_php_file .= "			\$col_excel_nom_etab = ".$this->col_chp_ident[$th['ID_THEME']].";//column\n";
				if($this->lig_code_etab[$th['ID_THEME']] <> '') $this->excel_php_file .= "			\$row_excel_code_etab = ".$this->lig_code_etab[$th['ID_THEME']].";//row\n";
				if($this->col_code_etab[$th['ID_THEME']] <> '') $this->excel_php_file .= "			\$col_excel_code_etab = ".$this->col_code_etab[$th['ID_THEME']].";//column\n";
				
				$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'verif_nom_etab.php');
				foreach($lines as $line) {	
					$this->excel_php_file .= $line;
				}		
				
				$this->excel_php_file .= "\n";
				$this->excel_php_file .= "			break;\n";
				$this->excel_php_file .= "		}\n";
				$this->excel_php_file .= "	}\n";
				$this->excel_php_file .= "}\n";
			}else{
				$this->excel_php_file .= "if( isset(\$_POST) && count(\$_POST) > 0 ){\n";
				$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'non_verif_nom_etab.php');
				foreach($lines as $line) {	
					$this->excel_php_file .= $line;
				}
				$this->excel_php_file .= "}\n";
			}
		}
		////End: Checking up District Name between District Identification' Screen Form  and Selected Excel file
		
		$this->excel_php_file .= "if( isset(\$_GET['excel_file']) && \$_GET['excel_file']<>'' ){\n";
		//$this->excel_php_file .= "	debut_popup_progress();//For progress popup starting\n";
		
		$this->excel_php_file .= "	\$tab_themes = array();\n";
		
		$req_excel_tables = "SELECT * FROM DICO_EXCEL_TABLE WHERE ID_SYSTEME = ".$_SESSION['secteur']." ORDER BY ORDRE_TABLE";
		$res_excel_tables =  $GLOBALS['conn_dico']->GetAll($req_excel_tables);	
		
		foreach($res_excel_tables as $excel_table){
			
			$NUM_LIGNES = explode(',',$excel_table['NUM_LIGNES']);
			$lignes = array();
			foreach($NUM_LIGNES as $lig){
				if(trim($lig) <> ''){
					$lignes[] = $lig;
				}else{
					$lignes[]=0;
				}
			}
			$NUM_COLONNES = explode(',',$excel_table['NUM_COLONNES']);
			$colonnes = array();
			foreach($NUM_COLONNES as $col){
				if(trim($col) <> ''){
					$colonnes[] = $col;
				}else{
					$colonnes[]=0;
				}
			}
			$excel_table['NUM_LIGNES'] = implode(',',$lignes);
			$excel_table['NUM_COLONNES'] = implode(',',$colonnes);
			
			//$this->excel_php_file .= "	for(\$sheet=0;\$sheet<count(\$data->sheets);\$sheet++){\n";
			if(($excel_table['TYPE_THEME']<>2 && $excel_table['TYPE_THEME']<>4) || ($excel_table['TYPE_THEME']==4 && (!isset($excel_table['NUM_COLONNE_DEBUT']) || $excel_table['NUM_COLONNE_DEBUT']==""))){
				$this->excel_php_file .= "	for(\$sheet=0;\$sheet<count(\$data->sheets);\$sheet++){\n";
				//$this->excel_php_file .= "		if(strtoupper(\$data->boundsheets[\$sheet]['name'])=='".strtoupper($excel_table['NOM_PAGE'])."'){\n";
				$this->excel_php_file .= "		if(strtoupper(substr(\$data->boundsheets[\$sheet]['name'],0,".strlen($excel_table['NOM_PAGE'])."))=='".strtoupper($excel_table['NOM_PAGE'])."'){\n";
			}else{
				$this->excel_php_file .= "	\$cpt_del = 0;\n";
				$this->excel_php_file .= "	for(\$sheet=0;\$sheet<count(\$data->sheets);\$sheet++){\n";
				$this->excel_php_file .= "		if(strtoupper(substr(\$data->boundsheets[\$sheet]['name'],0,".strlen($excel_table['NOM_PAGE'])."))=='".strtoupper($excel_table['NOM_PAGE'])."'){\n";
			}
			$this->excel_php_file .= "			\$table = '".$excel_table['NOM_TABLE']."_".$excel_table['ID_THEME']."';\n";
			//Modif Hebie pr gestion valeur filtre à importer depuis Excel
			$req_filtre	=	"	SELECT * FROM  DICO_EXCEL_TAB_FILTRE_VAL
								WHERE NOM_TABLE = '".$excel_table['NOM_TABLE']."' 
								AND ID_THEME = ".$excel_table['ID_THEME']."
								AND ID_SYSTEME = ".$excel_table['ID_SYSTEME'];
			$res_filtre = $GLOBALS['conn_dico']->GetAll($req_filtre);
			if(is_array($res_filtre) && count($res_filtre)>0){
				$this->excel_php_file .= "			\$exist_filtre = true;\n";
				$this->excel_php_file .= "			\$champ_filtre = '".$res_filtre[0]['NOM_CHAMP']."';\n";
				$this->excel_php_file .= "			\$num_lig_filtre = ".$res_filtre[0]['NUM_LIGNE'].";\n";
				$this->excel_php_file .= "			\$num_col_filtre = ".$res_filtre[0]['NUM_COLONNE'].";\n";
			}else{
				$this->excel_php_file .= "			\$exist_filtre = false;\n";
			}
			//Fin Modif Hebie pr gestion valeur filtre à importer depuis Excel
			$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'descrip_champs_dico.php');
			foreach($lines as $line) {	
				$this->excel_php_file .= $line;
			}
			if(($excel_table['TYPE_THEME']<>2 && $excel_table['TYPE_THEME']<>4) || ($excel_table['TYPE_THEME']==4 && (!isset($excel_table['NUM_COLONNE_DEBUT']) || $excel_table['NUM_COLONNE_DEBUT']==""))){
				$this->excel_php_file .= "\n			\$rows_excel = array(".$excel_table['NUM_LIGNES'].");\n";
				$this->excel_php_file .= "			\$cols_excel = array(".$excel_table['NUM_COLONNES'].");\n";
			}elseif($excel_table['TYPE_THEME']==2){
				$this->excel_php_file .= "\n			\$row_excel = ".$excel_table['NUM_LIGNE_DEBUT'].";\n";
				$this->excel_php_file .= "			\$cols_excel = array(".$excel_table['NUM_COLONNES'].");\n";
			}elseif($excel_table['TYPE_THEME']==4 && isset($excel_table['NUM_COLONNE_DEBUT']) && $excel_table['NUM_COLONNE_DEBUT']<>""){
				$this->excel_php_file .= "\n			\$col_excel = ".$excel_table['NUM_COLONNE_DEBUT'].";\n";
				$this->excel_php_file .= "			\$rows_excel = array(".$excel_table['NUM_LIGNES'].");\n";
			}
			
			if($excel_table['TYPE_THEME']==2 && $excel_table['NUM_LIGNE_DEBUT']<>""){
				$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'grille.php');
				foreach($lines as $line) {	
					$this->excel_php_file .= $line;
				}
			}elseif($excel_table['TYPE_THEME']==4 && isset($excel_table['NUM_COLONNE_DEBUT']) && $excel_table['NUM_COLONNE_DEBUT']<>""){
				$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'grille_colonne.php');
				foreach($lines as $line) {	
					$this->excel_php_file .= $line;
				}
			}elseif(in_array($excel_table['NOM_TABLE']."_".$excel_table['ID_THEME'],$this->tab_mat_dim_lig) && in_array($excel_table['NOM_TABLE']."_".$excel_table['ID_THEME'],$this->tab_mat_dim_col)){
				$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'matrice_dim_lig_col.php');
				foreach($lines as $line) {	
					$this->excel_php_file .= $line;
				}
			}elseif(in_array($excel_table['NOM_TABLE']."_".$excel_table['ID_THEME'],$this->tab_mat_dim_lig)){
				$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'matrice_dim_lig.php');
				foreach($lines as $line) {	
					$this->excel_php_file .= $line;
				}
			}elseif(in_array($excel_table['NOM_TABLE']."_".$excel_table['ID_THEME'],$this->tab_mat_dim_col)){
				if(in_array($excel_table['NOM_TABLE']."_".$excel_table['ID_THEME'],$this->tab_mat_liste_checkbox)){
					$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'matrice_dim_liste_checkbox.php');
					foreach($lines as $line) {	
						$this->excel_php_file .= $line;
					}
				}else{
					$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'matrice_dim_col.php');
					foreach($lines as $line) {	
						$this->excel_php_file .= $line;
					}
				}
			}elseif($excel_table['TYPE_THEME']==3){
				$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'formulaire.php');
				foreach($lines as $line) {	
					$this->excel_php_file .= $line;
				}
			}
			
			$this->excel_php_file .= "\n		}\n";
			$this->excel_php_file .= "	}\n";
		}
		/*
		$lines = file($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/routines_excel/'.'fin_import_excel.php');
		foreach($lines as $line) {	
			$this->excel_php_file .= $line;
		}
		*/
		$this->excel_php_file .= "\n}\n";
		$this->excel_php_file .= "?>\n";

		if (trim($this->excel_php_file) <> '') {
			file_put_contents($GLOBALS['SISED_PATH_INC'] . 'saisie_donnees/'.'excel_db_structure_'. $_SESSION['secteur'].'.php', $this->excel_php_file);
		}
	}
	
	function get_tabms_matricielles(){
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
			//Modif HEBIE 09 06 2019: Mise en commentaire pour import grille colonne sans dimension colonne
			//if($_SESSION['curobj_theme']->type_theme <> 4 || $_SESSION['curobj_theme']->type_gril_eff_fix_col){
				foreach($_SESSION['curobj_theme']->tableau_zone_saisie as $nom_tab => $tab){
					foreach($tab as $chp){
						if(($chp['type']=='dim_col') || ($chp['type']=='dim_lig')){
							if(($chp['type']=='dim_lig')){
								$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_row_dim'] = $chp['table_ref'];
								if(!in_array($nom_tab.'_'.$theme['ID'], $this->tab_mat_dim_lig))
									$this->tab_mat_dim_lig[] = $nom_tab.'_'.$theme['ID'];
							}
							if(($chp['type']=='dim_col')){
								$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $chp['table_ref'];
								if(!in_array($nom_tab.'_'.$theme['ID'], $this->tab_mat_dim_col))
									$this->tab_mat_dim_col[] = $nom_tab.'_'.$theme['ID'];
							}
						}
						if(($chp['type']=='tvm')){
							$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $chp['table_ref'];
							if(!in_array($nom_tab.'_'.$theme['ID'], $this->tab_mat_dim_col))
								$this->tab_mat_dim_col[] = $nom_tab.'_'.$theme['ID'];
						}
						if(($chp['type']=='li_ch')){
							$tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'] = $chp['table_ref'];
							if(!in_array($nom_tab.'_'.$theme['ID'], $this->tab_mat_dim_col))
								$this->tab_mat_dim_col[] = $nom_tab.'_'.$theme['ID'];
							$this->tab_mat_liste_checkbox[] = $nom_tab.'_'.$theme['ID'];
						}
					}
				}
				
				//cas de grille_eff : recherche dimension colonne
				if(($_SESSION['curobj_theme']->type_theme == 4)){
					$curobj_theme = $_SESSION['curobj_theme'];
					foreach($_SESSION['curobj_theme']->nomtableliee as $nom_tab){
						if(!isset($tables[$nom_tab.'_'.$theme['ID']]['tab_ref_col_dim'])){
							foreach($curobj_theme->nomtableliee as $tab){
								if(isset($tables[$tab.'_'.$theme['ID']]['tab_ref_col_dim'])){
									if(!in_array($nom_tab.'_'.$theme['ID'], $this->tab_mat_dim_col))
										$this->tab_mat_dim_col[] = $nom_tab.'_'.$theme['ID'];
								}
							}
						}
					}
				}
			//}
		}
		//echo "<br>tables<pre>";
		//print_r($tables);
	}
	
	function get_excel_ident_field(){
		if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
		if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
		if(!isset($_SESSION['code_etab']) || $_SESSION['code_etab']==''){
			$_SESSION['code_etab'] = 0;
		}
		$code_etablissement = $_SESSION['code_etab'];
		$code_annee = $_SESSION['annee'];
		$code_filtre = $_SESSION['filtre'];
		$id_systeme	= $_SESSION['secteur'];
		
		$req_theme = "SELECT ID_THEME FROM DICO_EXCEL_TABLE WHERE ORDRE_TABLE = 1 AND ID_SYSTEME = ".$_SESSION['secteur'];
		$res_theme = $GLOBALS['conn_dico']->GetAll($req_theme);
		$list_th = "(";
		$k = 0;
		foreach($res_theme as $th){
			if($k==0) $list_th .= $th['ID_THEME'];
			else $list_th .= ",".$th['ID_THEME'];
			$k++;
		}
		$list_th .= ")";
		$requete  = "SELECT DICO_THEME.* 
			 FROM DICO_THEME 
			 WHERE DICO_THEME.ID IN $list_th";
		$result_themes	= $GLOBALS['conn_dico']->GetAll($requete);
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
				$trouver = false;
				foreach($_SESSION['curobj_theme']->tableau_zone_saisie as $nom_tab => $tab){
					foreach($tab as $chp){
						if((isset($chp['type'])) && ($chp['type']<>'c' || ($chp['type']=='c' && $nom_tab==$GLOBALS['PARAM']['ETABLISSEMENT'] && $chp['champ']==$GLOBALS['PARAM']['CODE_ETABLISSEMENT'])) 
							&& ($chp['type']<>'l') && ($chp['type']<>'dim_lig') && ($chp['type']<>'dim_col') && ($chp['champ']<>$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'])){
							
							$this->tables[$nom_tab.'_'.$theme['ID']]['data_entry_fields'][] = $chp['champ'];
							if($chp['champ']==$GLOBALS['PARAM']['NOM_ETABLISSEMENT']){
								$NOM_TABLE = $nom_tab;
								$ID_THEME = $theme['ID'];
								$req_ident_tab = "SELECT NOM_PAGE, NUM_LIGNES, NUM_COLONNES FROM DICO_EXCEL_TABLE WHERE NOM_TABLE = '$NOM_TABLE' AND ID_THEME = $ID_THEME AND ID_SYSTEME = ".$_SESSION['secteur'];
								$res_ident_tab = $GLOBALS['conn_dico']->GetAll($req_ident_tab);
								if(trim($res_ident_tab[0]['NUM_LIGNES'])<>'') $lignes = explode(',',$res_ident_tab[0]['NUM_LIGNES']);
								if(trim($res_ident_tab[0]['NUM_COLONNES'])<>'') $colonnes = explode(',',$res_ident_tab[0]['NUM_COLONNES']);
								$k=0;
								$this->lig_chp_ident[$ID_THEME] = 0;
								$this->col_chp_ident[$ID_THEME] = 0;
								$this->page_chp_ident[$ID_THEME] = "";
								$this->lig_code_etab[$ID_THEME] = 0;
								$this->col_code_etab[$ID_THEME] = 0;
								foreach($this->tables[$NOM_TABLE.'_'.$ID_THEME]['data_entry_fields'] as $chp){
									if($chp == $GLOBALS['PARAM']['NOM_ETABLISSEMENT']){
										$this->lig_chp_ident[$ID_THEME] = $lignes[$k];
										$this->col_chp_ident[$ID_THEME] = $colonnes[$k];
										$this->page_chp_ident[$ID_THEME] = $res_ident_tab[0]['NOM_PAGE'];
										//break;
									}
									if($chp == $GLOBALS['PARAM']['CODE_ETABLISSEMENT']){
										$this->lig_code_etab[$ID_THEME] = $lignes[$k];
										$this->col_code_etab[$ID_THEME] = $colonnes[$k];
										//break;
									}
									$k++;
								}
								$trouver = true;
								break;
							}
						}
					}
					if($trouver) break;
				}
			}
		}
	}
	
	function ordonner_tables_excel($secteur){
		$req_tab_ord = "SELECT NOM_TABLE, ORDRE FROM DICO_TABLE_ORDRE ORDER BY ORDRE";
		$res_tab_ord = $GLOBALS['conn_dico']->GetAll($req_tab_ord);
		$req_excel_tab = "SELECT DISTINCT NOM_TABLE FROM DICO_EXCEL_TABLE WHERE ID_SYSTEME = ".$secteur;
		$res_excel_tab = $GLOBALS['conn_dico']->GetAll($req_excel_tab);
		$tab_excel_tab = array();
		foreach($res_excel_tab as $tab){
			$tab_excel_tab[] = $tab['NOM_TABLE'];
		}
		$ordre = 0;
		foreach($res_tab_ord as $tab){
			if(in_array($tab['NOM_TABLE'],$tab_excel_tab)){
				$ordre++;
				$req_upd_excel_tab = "UPDATE DICO_EXCEL_TABLE SET ORDRE_TABLE = ".$ordre. " WHERE NOM_TABLE = '".$tab['NOM_TABLE']."'";
				$res = $GLOBALS['conn_dico']->Execute($req_upd_excel_tab);
			}
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
}//End class
?>
