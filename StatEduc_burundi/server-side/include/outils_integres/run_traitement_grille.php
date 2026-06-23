<?php
	if( isset($_GET['sort']) ){
		$tab_list_chps_ord = "";
		if(count($_GET)>0) {
			foreach($_GET as $cle => $val) {
				if($cle<>'' && $cle<>'val' && $cle<>'theme' && $cle<>'sort') {
					$tab_list_chps_ord .= "&".$cle."=".$val;
				}
			}
		}
		$_SESSION['tab_get_sort'] = explode('&', $tab_list_chps_ord);
	}
	elseif( isset($_GET['filter']) ){
		$tab_list_chps_filter = "";
		if(count($_GET)>0) {
			foreach($_GET as $cle => $val) {
				if($cle<>'' && $cle<>'val' && $cle<>'theme' && $cle<>'filter') {
					$champ = explode('_',$cle);
					$tab_list_chps_filter .= "&".$champ[0]."=".$val;
				}
			}
		}
		$_SESSION['tab_get_filter'] = explode('&', $tab_list_chps_filter);
	}
	elseif( isset($_GET['cancelsort']) ){
		if(isset($_SESSION['tab_get_sort'])) unset($_SESSION['tab_get_sort']);
	}
	elseif( isset($_GET['cancelfilter']) ){
		if(isset($_SESSION['tab_get_filter'])) unset($_SESSION['tab_get_filter']);
	}elseif( isset($_GET['export_gril']) ){
		set_time_limit(0);
		ini_set("memory_limit", "64M");
		$list_champs = explode(':', $_GET['list_chps']);
		$_SESSION['list_champs'] = $_GET['list_chps'];
		if(isset($_SESSION['list_chps_export'])) unset($_SESSION['list_chps_export']);
		foreach($list_champs as $champ){
			$tabs0 = explode('|',$champ);
			$tabs1 = explode('-',$tabs0[1]);
			$_SESSION['list_chps_export'][] = "".$tabs1[1]."";
		}
		require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
		require_once $GLOBALS['SISED_PATH_INC']	.'outils_integres/export_grille.php';
		$long_syst_id=strlen(''.$_SESSION['secteur']);
		$long_theme_syst_id=strlen(''.$_GET['theme']);
		$long_theme_id=$long_theme_syst_id-$long_syst_id;
		$id_theme=substr($_GET['theme'],0,$long_theme_id);
		$_SESSION['tab_html_export'] = generer_table_grille($id_theme, $_SESSION['langue'] ,$_SESSION['secteur']);
		
	}elseif( isset($_GET['export_form_curr']) ){
		set_time_limit(0);
		ini_set("memory_limit", "64M");
		require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
		require_once $GLOBALS['SISED_PATH_INC']	.'outils_integres/export_grille.php';
		
		$long_syst_id=strlen(''.$_SESSION['secteur']);
		$long_theme_syst_id=strlen(''.$_GET['theme']);
		$long_theme_id=$long_theme_syst_id-$long_syst_id;
		$id_theme=substr($_GET['theme'],0,$long_theme_id);
		
		$_SESSION['tab_html_export'] = generer_table_formulaire($id_theme, $_SESSION['langue'] ,$_SESSION['secteur']);
		
	}elseif( isset($_GET['export_form_hist']) ){
		set_time_limit(0);
		ini_set("memory_limit", "64M");
		$list_champs = explode(':', $_GET['list_chps']);
		if(isset($_SESSION['list_chps_export'])) unset($_SESSION['list_chps_export']);
		foreach($list_champs as $champ){
			$tabs0 = explode('|',$champ);
			$tabs1 = explode('-',$tabs0[1]);
			$_SESSION['list_chps_export'][] = "".$tabs1[1]."";
		}
	
		require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
		require_once $GLOBALS['SISED_PATH_INC']	.'outils_integres/export_grille.php';
		
		$long_syst_id=strlen(''.$_SESSION['secteur']);
		$long_theme_syst_id=strlen(''.$_GET['theme']);
		$long_theme_id=$long_theme_syst_id-$long_syst_id;
		$id_theme=substr($_GET['theme'],0,$long_theme_id);
		
		$_SESSION['tab_html_export_hist'] = generer_table_formulaire_hist($id_theme, $_SESSION['langue'] ,$_SESSION['secteur'], $_SESSION['annee']);
	}		
?>


