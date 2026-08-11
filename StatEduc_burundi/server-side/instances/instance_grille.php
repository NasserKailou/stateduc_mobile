<?php 
ini_set("memory_limit", "256M");
// Fichiers d'includes'
include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'controle.inc.php';

//si il y'a des donn�es dans le post alors on met � jour et on gen�re le formulaire
if(count($_POST) == 0) {
		// Modif Bass
} elseif(!isset($GLOBALS['dont_submit'])) {
	//Ajout Hebie pour verifier l'existence du code etablissement
	if(isset($_GET['tmis']) && (!isset($_SESSION['code_etab']) || $_SESSION['code_etab']=='') && (!isset($_POST[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_0']) || $_POST[$GLOBALS['PARAM']['CODE_ETAB_ENSEIG_TRANSFERT'].'_0']=='')){ 
		print "<script type='text/Javascript'>\n";
		print " alert('".recherche_libelle_page('Code_Etab_Not_Found')."');\n";
		print " document.location.href='?$gets';\n";
		print "</script>\n";
		echo "<script type='text/Javascript'>\n";
		echo "$.unblockUI();\n";
		echo "</script>\n";
		exit();
    }
	//Fin Ajout Hebie pour verifier l'existence du code etablissement
    $GLOBALS['action_insert'] = false; // pour verifier plus tard s'il a une insertion
    //on inclut la classe de controle
    if(!isset($_GET['tmis']))
	require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/controle_theme.class.php';
	
    $curobj_grille = $_SESSION['curobj_instance'];
    
    // ici fonction VERIF du $_POST
    $curobj_grille->verif($_POST);    
    
    // Demarrage de la Transaction pour contr�ler le probl�me d'acc�s simultan� sur le dernier enregistrement
    $curobj_grille->conn->StartTrans();   // Start of trasaction

    // R�cuperation des donn�es de la superglobale $_POST
    $curobj_grille->get_post_template($_POST);
	// Comparaison des matrices de d�part et d'arriv�e'
	$curobj_grille->comparer($curobj_grille->matrice_donnees,$curobj_grille->matrice_donnees_post, $curobj_grille->keys_template);

    // maj de la base de donn�es
    
    $curobj_grille->maj_bdd();
    
    // Vaidation de la Transaction apr�s la Maj des donn�es afin de lib�rer le processus
    $curobj_grille->conn->CompleteTrans(); // Commit Transaction
       
    if($curobj_grille->new_etab == true){ 
        if($GLOBALS['action_insert'] == false){
            $GLOBALS['pb_new_etab'] = true;
        }
        //$_SESSION['code_etab'] = $curobj_grille->code_etablissement;
        unset ($_SESSION['code_etab']);
        $New_Etab = $curobj_grille->code_etablissement;                
        if (isset($New_Etab) && (trim($New_Etab)<>'') ){
            $_SESSION['code_etab']= $curobj_grille->code_etablissement;
        }
    }
	if(!isset($_GET['tmis']))
	$controle_theme	=	new controle_theme($curobj_grille->id_theme, $_SESSION['langue'], $curobj_grille->code_etablissement, $curobj_grille->code_annee, $curobj_grille->code_filtre,true);
    
    unset($_SESSION['curobj_instance']);
		
}
    echo '<script language="javascript" src="'.$GLOBALS['SISED_URL_JSC'] . 'js.js"></script>' . "\n";
    // param�tre d'�change
    $id_theme			=	$theme_manager->id;    
	$code_etablissement = $_SESSION['code_etab'];
    $code_annee = $_SESSION['annee'];
	// SESSION 59 FIX DÉFINITIF — KOSAVE thèmes sans filtre (10502, 10602, 10702)
	// ROOT CAUSE : questionnaire_ws.php utilise session_start(['read_and_close'=>true]),
	// donc $_SESSION ne peut PAS être modifié. La Session B (cURL data_save→questionnaire_ws)
	// conserve la valeur stale de $_SESSION['filtre'] d'un appel précédent (ex: '1').
	// Instance_grille instancie grille avec code_filtre='1' → WHERE CODE_TYPE_PERIODE=1
	// sur thème sans période → matrice vide → KOSAVE.
	//
	// FIX : quand $_GET['filtre'] est présent dans l'URL (appel cURL mobile ou navigateur),
	// l'utiliser directement. data_save.php passe &filtre=<valeur> seulement si filtre réel,
	// sinon il ne passe PAS &filtre → isset($_GET['filtre']) = false → code_filtre = ''.
	// Cela court-circuite complètement la session stale.
	if (isset($_GET['filtre'])) {
	    $code_filtre = $_GET['filtre'];
	} else {
	    $code_filtre = $_SESSION['filtre'];
	}
    $id_systeme	= $_SESSION['secteur'];

    if( $_GET['val'] == 'new_etab'){
				unset($code_etablissement);
    }

if(!isset($_SESSION['tab_html_export_hist'])){
	// Instanciation de la classe
    $curobj_grille		=	new grille($code_etablissement,$code_annee,$id_theme,$id_systeme,$code_filtre);
	//echo "<pre>";
	//print_r($curobj_grille);
	if(isset($_SESSION['tab_html_export']))	$curobj_grille->nb_lignes = $_SESSION['nbre_total_enr'];
	
	// Appel des fonctions de la classe
    // chargement des codes des nomenclatures des champs de type matrice
    $curobj_grille->set_code_nomenclature();

    // R�cup�ration des diff�rents champs
    // $curobj_grille->set_champs();

    // Configuration de la barre de navigation
    configurer_barre_nav($curobj_grille->nb_lignes);
	
	//Ajout HEBIE pour ordonner la grille teachers details
	if(isset($GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && isset($_SESSION['tab_get_sort'])){
		//echo '<pre>';
		//print_r($_SESSION['tab_get_sort']);
		foreach($curobj_grille->sql_data as $tab=>$sql){
			$list_sql_tab = explode('UNION',$sql);
			$tables_sql = array();
			$list_ord = "";
			$i = 0;
			foreach($list_sql_tab as $sql_tab){
				$list_select_suite = "";
				foreach($_SESSION['tab_get_sort'] as $tab_ord){
					if($tab_ord <> ""){	
						$tabs0 = explode('|',$tab_ord);
						$tabs1 = explode('-',$tabs0[1]);
						$tabs2 = explode('=',$tabs0[1]);
						$tabs3 = explode('-',$tabs2[0]);
						if(ereg(' '.$tabs1[0].' ',$sql_tab)){
							if($tabs1[0] <> $tab){
								if(!ereg(str_replace('-','.',$tabs2[0])." AS ".$tabs3[1],$sql_tab) && !ereg(str_replace('-','.',$tabs2[0])." AS ".$tabs3[1],$list_select_suite)){
									$list_select_suite .= ", ".str_replace('-','.',$tabs2[0])." AS ".$tabs3[1]." ";
								}
								if($i==0) $list_ord .= " ORDER BY ".$tabs3[1].' '.$tabs2[1];
								else $list_ord .= ', '.$tabs3[1].' '.$tabs2[1].' ';
							}else{
								if($i==0) $list_ord .= " ORDER BY ".$tabs3[1].' '.$tabs2[1];
								elseif(!ereg($tabs3[1],$list_ord))	$list_ord .= ', '.$tabs3[1].' '.$tabs2[1].' ';
							}
							$i++;
						}elseif(count($list_sql_tab) > 1){
							if($tabs2[1] == 'ASC'){
								if(!ereg(" AS ".$tabs3[1],$sql_tab) && !ereg(" AS ".$tabs3[1],$list_select_suite)){
									if($tabs0[0] == 'int') $list_select_suite .= ", 4294967295 "." AS ".$tabs3[1]." ";//4294967295 est la valeur maxi d'un entier long non sign�
									else $list_select_suite .= ", 'zzzzzzzzzz' "." AS ".$tabs3[1]." ";//zzzzzzzzzz etant une valeur tres tres grande d'une chaine de caracteres
								}
							}elseif($tabs2[1] == 'DESC'){
								if(!ereg(" AS ".$tabs3[1],$sql_tab) && !ereg(" AS ".$tabs3[1],$list_select_suite)){
									if($tabs0[0] == 'int') $list_select_suite .= ", 0 "." AS ".$tabs3[1]." ";//0 est la valeur mini d'un entier long non sign�
									else $list_select_suite .= ", 'aaaaaaaaaa' "." AS ".$tabs3[1]." ";//aaaaaaaaaa etant une valeur tres tres petite d'une chaine de caracteres
								}
							}
						}
					}
				}
				$sql_tab = str_replace('FROM',$list_select_suite.'FROM',$sql_tab);
				$tables_sql[] = $sql_tab;
			}
				
			$curobj_grille->sql_data[$tab] = implode('UNION',$tables_sql);
			$curobj_grille->sql_data[$tab] .= $list_ord;
			
		}
		//echo '<pre>';
		//print_r($curobj_grille->sql_data);
	}
	//Fin Ajout HEBIE TMIS
	//echo '<pre>';
	//print_r($_SESSION['tab_get_filter']);
	//Ajout HEBIE pour filtrer la grille teachers details
	if(isset($GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']) && isset($_SESSION['tab_get_filter'])){
		foreach($curobj_grille->sql_data as $tab=>$sql){
			$list_sql_tab = explode('UNION',$sql);
			$tables_sql = array();
			$operators = array("equalint"=>"=","equaltext"=>"=","different"=>"<>","contain"=>" LIKE '%#%'","notcontain"=>" NOT LIKE '%#%'","start"=>" LIKE '#%'","end"=>" LIKE '%#'","superior"=>">","inferior"=>"<","between"=>" BETWEEN #1 AND #2");
			foreach($list_sql_tab as $sql_tab){
				$list_crit = "";
				$list_select_suite = "";
				foreach($_SESSION['tab_get_filter'] as $tab_filt){
					if($tab_filt <> ""){	
						$tabs0 = explode('=',$tab_filt);
						if($tabs0[0]=='champ'){
							$tabs1 = explode('|',$tabs0[1]);
							$tabs2 = explode('-',$tabs1[1]);//table = $tabs2[0] et champ = $tabs2[1]
						}elseif(ereg(' '.$tabs2[0].' ',$sql_tab)){
							if($tabs2[0] <> $tab){
								if(!ereg(str_replace('-','.',$tabs1[1])." AS ".$tabs2[1],$sql_tab) && !ereg(str_replace('-','.',$tabs1[1])." AS ".$tabs2[1],$list_select_suite)){
									$list_select_suite .= ", ".str_replace('-','.',$tabs1[1])." AS ".$tabs2[1]." ";
								}
								if($tabs0[0]=='equalint' || $tabs0[0]=='equaltext' || $tabs0[0]=='different' || $tabs0[0]=='superior' || $tabs0[0]=='inferior'){
									if($tabs0[0]=='equaltext') $list_crit .= str_replace('-','.',$tabs1[1]).$operators[$tabs0[0]].'\''.$tabs0[1].'\' AND ';
									else $list_crit .= str_replace('-','.',$tabs1[1]).$operators[$tabs0[0]].$tabs0[1].' AND ';
									
								}elseif($tabs0[0]=='contain' || $tabs0[0]=='notcontain' || $tabs0[0]=='start' || $tabs0[0]=='end'){
									$list_crit .= str_replace('-','.',$tabs1[1]).str_replace('#',$tabs0[1],$operators[$tabs0[0]]).' AND ';
								}elseif($tabs0[0]=='between'){
									$tab_betw = explode('-', $tabs0[1]);
									//$tab_betw = explode(';', $tab_betw);
									//$tab_betw = explode(',', $tab_betw);
									$ch_betw = str_replace('#1',$tab_betw[0],$operators[$tabs0[0]]);
									$ch_betw = str_replace('#2',$tab_betw[1],$ch_betw);
									$list_crit .= str_replace('-','.',$tabs1[1]).$ch_betw.' AND ';
								}
							}else{
								if($tabs0[0]=='equalint' || $tabs0[0]=='equaltext' || $tabs0[0]=='different' || $tabs0[0]=='superior' || $tabs0[0]=='inferior')
									if($tabs0[0]=='equaltext') $list_crit .= str_replace('-','.',$tabs1[1]).$operators[$tabs0[0]].'\''.$tabs0[1].'\' AND ';
									else $list_crit .= str_replace('-','.',$tabs1[1]).$operators[$tabs0[0]].$tabs0[1].' AND ';
								elseif($tabs0[0]=='contain' || $tabs0[0]=='notcontain' || $tabs0[0]=='start' || $tabs0[0]=='end'){
									$list_crit .= str_replace('-','.',$tabs1[1]).str_replace('#',$tabs0[1],$operators[$tabs0[0]]).' AND ';
								}elseif($tabs0[0]=='between'){
									$tab_betw = explode('-', $tabs0[1]);
									//$tab_betw = explode(';', $tab_betw);
									//$tab_betw = explode(',', $tab_betw);
									$ch_betw = str_replace('#1',$tab_betw[0],$operators[$tabs0[0]]);
									$ch_betw = str_replace('#2',$tab_betw[1],$ch_betw);
									$list_crit .= str_replace('-','.',$tabs1[1]).$ch_betw.' AND ';
								}
							}
						}elseif(count($list_sql_tab) > 1){
							if($tabs1[0] == 'int'){
								if(!ereg(" AS ".$tabs2[1],$sql_tab) && !ereg(" AS ".$tabs2[1],$list_select_suite)){
									$list_select_suite .= ", NULL "." AS ".$tabs2[1]." ";//
								}
								$list_crit .= "NULL<>NULL AND ";
							}else{
								if(!ereg(" AS ".$tabs2[1],$sql_tab) && !ereg(" AS ".$tabs2[1],$list_select_suite)){
									$list_select_suite .= ", '' "." AS ".$tabs2[1]." ";//
								}
								$list_crit .= "''<>'' AND ";
							}
						}
					}
				}
				$sql_tab = str_replace('FROM',$list_select_suite.'FROM',$sql_tab);
				$sql_tab = str_replace('WHERE','WHERE '.$list_crit,$sql_tab);
				$tables_sql[] = $sql_tab;
			}
				
			$curobj_grille->sql_data[$tab] = implode('UNION',$tables_sql);
		}
		//echo '<pre>';
		//print_r($curobj_grille->sql_data);
	}
	//Fin Ajout HEBIE TMIS
	
    //Ajout HEBIE pour ajout de critere de filtrage complementaire sur ID_TEACHER dans le formulaire full teacher details
	if(isset($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])){
		//Gestion id_teacher
		require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/teacher_manager.class.php';
		$teacher_manager = new teacher_manager(); 
		$teacher_manager->teacher_sql_data = $_SESSION['teacher_sql_data'];
		$teacher_manager->init();
		$new_teacher = false;	
		if(isset($_GET['id_teacher'])){
			$_SESSION['id_teacher'] = $_GET['id_teacher'];
		}elseif(isset($_GET['ligne']) && !isset($_GET['deb_save'])){
			if(!isset($_SESSION['debut'])) $deb = 0;
			else  $deb = (int)($_SESSION['debut']/$curobj_grille->nb_lignes);
			if((count($teacher_manager->list)==0) || (!isset($teacher_manager->list[$_GET['ligne']+($deb*$curobj_grille->nb_lignes)]['ID_TEACHER'])) || ($teacher_manager->list[$_GET['ligne']+($deb*$curobj_grille->nb_lignes)]['ID_TEACHER']=='')){
				$new_teacher = true;
				$_SESSION['id_teacher'] = $teacher_manager->get_cle_max()+1;
			}else{
				$_SESSION['id_teacher'] = $teacher_manager->list[$_GET['ligne']+($deb*$curobj_grille->nb_lignes)]['ID_TEACHER'];
			}
			if(isset($_SESSION['debut'])) unset($_SESSION['debut']);
		}elseif(isset($_GET['ligne']) && isset($_GET['deb_save'])){
			$deb = (int)($_GET['deb_save']/$curobj_grille->nb_lignes);
			$_SESSION['id_teacher'] = $teacher_manager->list[$_GET['ligne']+($deb*$curobj_grille->nb_lignes)]['ID_TEACHER'];
			if(isset($_SESSION['debut'])) unset($_SESSION['debut']);
		}
		foreach($curobj_grille->sql_data as $tab=>$sql){
			$curobj_grille->sql_data[$tab] = str_replace('WHERE','WHERE '.$tab.'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].'='.$_SESSION['id_teacher'].' AND',$curobj_grille->sql_data[$tab]);
		}
		if(!$new_teacher) $_SESSION['code_etab'] = $teacher_manager->list_sch_teach[$_SESSION['id_teacher']];
		elseif(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>1){ $_SESSION['code_etab'] = '';}
		if(isset($_SESSION['liste_etab']) && $_SESSION['code_etab']<>''){
			$_SESSION['code_etab'] = $teacher_manager->list_sch_teach[$_SESSION['id_teacher']];
			foreach($curobj_grille->nomtableliee as $nomtableliee){
				foreach($curobj_grille->sql_data as $tab=>$sql){
					$list_sql_tab = explode('UNION',$sql);
					$tables_sql = array();
					$list_ord = "";
					$i = 0;
					foreach($list_sql_tab as $sql_tab){
						if(ereg(' '.$nomtableliee.'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' IN ',$sql_tab)){
							$sql_tab = str_replace('WHERE','WHERE '.$nomtableliee.'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$teacher_manager->list_sch_teach[$_SESSION['id_teacher']].' AND',$sql_tab);
						}
						$tables_sql[] = $sql_tab;
					}
					$curobj_grille->sql_data[$tab] = implode('UNION',$tables_sql);
				}
			}
		}
		//echo '<pre>';
		//print_r($curobj_grille->sql_data);
	}
	//Fin Ajout HEBIE TMIS
	// r�cup�ration des donn�es de la base de donn�es
    //echo '<pre>';
	//print_r($curobj_grille->sql_data);
	$curobj_grille->get_donnees_bdd();
	
	// remplissage et affichage du template
    $template = $curobj_grille->remplir_template($curobj_grille->template);
	// Affichage de la barre de navigation 
	if(!(isset($_SESSION['liste_etab']) && count($_SESSION['liste_etab'])>0)){
		if (($curobj_grille->type_frame==3) || ($curobj_grille->nb_lignes==1) || ($curobj_grille->type_gril_eff_fix_col))  { 
			$barre_nav = afficher_barre_nav(false,true,array('theme','type_ent_stat'));
		}else{
			$barre_nav = afficher_barre_nav(true,true,array('theme','type_ent_stat'));
		}
	}else{
		if (($curobj_grille->type_frame==3) || ($curobj_grille->nb_lignes==1) || ($curobj_grille->type_gril_eff_fix_col))  { 
			$barre_nav = afficher_barre_nav_tmis(false,true,array('theme'));
		}else{
			$barre_nav = afficher_barre_nav_tmis(true,true,array('theme'));
			echo "\n<div style='text-align:left; font:bold;'>".$GLOBALS['nbre_total_enr']." ".recherche_libelle_page('Teach_Found')."</div>\n";
		}
	}
	
    echo $template;    
    
    echo $barre_nav;

	$_SESSION['nbre_total_enr'] = $GLOBALS['nbre_total_enr'];
    
	$_SESSION['curobj_instance'] = $curobj_grille;
	
	if(isset($_SESSION['tab_html_export'])){
		$table_html = $curobj_grille->remplir_table_html($_SESSION['tab_html_export'],'');
		$_SESSION["table_html"] = '<br/><br/>'.$table_html;
		$suite_gets = "";
		if(isset($_GET['tmis'])) $suite_gets .= "&tmis=".$_GET['tmis'];
		if(isset($_GET['id_teacher'])) $suite_gets .= "&id_teacher=".$_GET['id_teacher'];
		if(isset($_GET['ligne'])) $suite_gets .= "&ligne=".$_GET['ligne'];
		echo '<script language="JavaScript1.2" type="text/javascript">'."\n";
		echo 'OpenPopupExportGrilleXls()'."\n";
		echo "document.location.href='questionnaire.php?theme=".$id_theme.$id_systeme.$suite_gets."'\n"; 
		echo '</script>'."\n";
		unset($_SESSION['tab_html_export']);
	}
}elseif(isset($_SESSION['tab_html_export_hist'])){

	$req_annee = "SELECT DISTINCT ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].", ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_ANNEE'].
						", ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].
				" FROM ".$GLOBALS['PARAM']['TYPE_ANNEE'].", ".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].
				" WHERE ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."=".$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].".".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']." AND ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."<>255".
				" ORDER BY ".$GLOBALS['PARAM']['TYPE_ANNEE'].".".$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];

	$res_annee = $GLOBALS['conn']->GetAll($req_annee);
	$tab_cod_ann = array();
	foreach($res_annee as $annee){	
		// Instanciation de la classe
		$curobj_grille		=	new grille($code_etablissement,$annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']],$id_theme,$id_systeme,$code_filtre);
		// chargement des codes des nomenclatures des champs de type matrice
		$curobj_grille->set_code_nomenclature();
		// Configuration de la barre de navigation
		configurer_barre_nav($curobj_grille->nb_lignes);
		//Ajout HEBIE pour ajout de critere de filtrage complementaire sur ID_TEACHER dans le formulaire full teacher details
		if(isset($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && in_array($_GET['theme'],$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'])){
			//Gestion id_teacher
			if(isset($_GET['id_teacher'])){
				$_SESSION['id_teacher'] = $_GET['id_teacher'];
			}elseif(isset($_GET['ligne']) && !isset($_GET['deb_save'])){
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/teacher_manager.class.php';
				$teacher_manager = new teacher_manager(); 
				$teacher_manager->teacher_sql_data = $_SESSION['teacher_sql_data'];
				$teacher_manager->init();
				if(!isset($_SESSION['debut'])) $deb = 0;
				else  $deb = (int)($_SESSION['debut']/$curobj_grille->nb_lignes);
				$_SESSION['id_teacher'] = $teacher_manager->list[$_GET['ligne']+($deb*$curobj_grille->nb_lignes)]['ID_TEACHER'];
				if(isset($_SESSION['debut'])) unset($_SESSION['debut']);
			}elseif(isset($_GET['ligne']) && isset($_GET['deb_save'])){
				require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/teacher_manager.class.php';
				$teacher_manager = new teacher_manager(); 
				$teacher_manager->teacher_sql_data = $_SESSION['teacher_sql_data'];
				$teacher_manager->init();
				$deb = (int)($_GET['deb_save']/$curobj_grille->nb_lignes);
				$_SESSION['id_teacher'] = $teacher_manager->list[$_GET['ligne']+($deb*$curobj_grille->nb_lignes)]['ID_TEACHER'];
				if(isset($_SESSION['debut'])) unset($_SESSION['debut']);
			}
			foreach($curobj_grille->sql_data as $tab=>$sql){
				$curobj_grille->sql_data[$tab] = str_replace('WHERE','WHERE '.$tab.'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].'='.$_SESSION['id_teacher'].' AND',$curobj_grille->sql_data[$tab]);
			}
		}
		//Fin Ajout HEBIE TMIS
		// r�cup�ration des donn�es de la base de donn�es
		$curobj_grille->get_donnees_bdd();
		//
		foreach($curobj_grille->nomtableliee as $nomtableliee){
			$cod_ann = $annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']];
			$tab_cod_ann[] = $cod_ann;
			$curobj_grille->preparer_donnees_tpl($nomtableliee);
			$_SESSION['matrice_donnees']['ANN_'.$cod_ann][$nomtableliee] = $curobj_grille->matrice_donnees[$nomtableliee];
			$curobj_grille->preparer_donnees_tpl($nomtableliee);
			$_SESSION['matrice_donnees_tpl']['ANN_'.$cod_ann][$nomtableliee] = $curobj_grille->matrice_donnees_tpl[$nomtableliee];
			/*echo"$nomtableliee <br> <pre>";
			print_r($this->matrice_donnees[$nomtableliee]);
			echo"$nomtableliee - tpl<br> <pre>";
			print_r($this->matrice_donnees_tpl[$nomtableliee]);*/
		}
		//
		
	}
	$table_html = $curobj_grille->remplir_table_html($_SESSION['tab_html_export_hist'], $tab_cod_ann);
		
	//unset($curobj_grille);
	$_SESSION["table_html"] = '<br/><br/>'.$table_html;
	$suite_gets = "";
	if(isset($_GET['tmis'])) $suite_gets .= "&tmis=".$_GET['tmis'];
	if(isset($_GET['id_teacher'])) $suite_gets .= "&id_teacher=".$_GET['id_teacher'];
	if(isset($_GET['ligne'])) $suite_gets .= "&ligne=".$_GET['ligne'];
	echo '<script language="JavaScript1.2" type="text/javascript">'."\n";
	echo 'OpenPopupExportGrilleXls()'."\n";
	echo "document.location.href='questionnaire.php?theme=".$id_theme.$id_systeme.$suite_gets."'\n";
	echo '</script>'."\n";
	//echo "<pre>";
	//print_r($_SESSION["table_html"]);
	unset($_SESSION['tab_html_export_hist']);
}
//unset($_SESSION['tab_html_export_hist']);
//echo "<pre>";
//print_r($_SESSION["table_html"]);
?>
