<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<script type="text/Javascript">
	function fermer() {
		window.close();
	}
</script>
<?php set_time_limit(0);
		ini_set("memory_limit", "128M");
		//require_once 'common.php';
		include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
		include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/aggregated_export.class.php'; 
				
		$export	= new export() ;
		
		$chemin_output = $SISED_PATH.'server-side/import_export/';
		$export->chemin_output	=	$chemin_output ;
		
		$tab_filtres 	= 	array();
		
		$tab_annees		=	array();
		if($GLOBALS['PARAM']['FILTRE']){
			$tab_filtres		=	array();
		}
		if($_SESSION['FILTRE']['FILTRE_2']){
			$tab_filtres2		=	array();
		}
		$tab_regs		=	array();
		
		// A interfacer
		
		$secteur				= $_SESSION['cfg_exp']['id_systeme']; 
		$chaine					= $_SESSION['cfg_exp']['id_chaine']; 
		
		for( $i = 0 ; $i < $_SESSION['cfg_exp']['nb_annees_dispo'] ; $i++ ){
				if(isset($_SESSION['cfg_exp']['ANN_'.$i]) and trim($_SESSION['cfg_exp']['ANN_'.$i]) <> ''){
						$tab_annees[]	= array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'] => $_SESSION['cfg_exp']['ANN_'.$i]);
				}
		}
		if($GLOBALS['PARAM']['FILTRE']){
			for( $i = 0 ; $i < $_SESSION['cfg_exp']['nb_filtres_dispo'] ; $i++ ){
					if(isset($_SESSION['cfg_exp']['PERIOD_'.$i]) and trim($_SESSION['cfg_exp']['PERIOD_'.$i]) <> ''){
							$tab_filtres[]	= array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'] => $_SESSION['cfg_exp']['PERIOD_'.$i]);
					}
			}
		}
		if($_SESSION['FILTRE']['FILTRE_2']){
			for( $i = 0 ; $i < $_SESSION['cfg_exp']['nb_filtres2_dispo'] ; $i++ ){
					if(isset($_SESSION['cfg_exp']['FILTRE2_'.$i]) and trim($_SESSION['cfg_exp']['FILTRE2_'.$i]) <> ''){
							$tab_filtres2[]	= array($_SESSION['FILTRE']['CODE_TYPE_FILTRE_2'] => $_SESSION['cfg_exp']['FILTRE2_'.$i]);
					}
			}
		}
		for( $i = 0 ; $i < $_SESSION['cfg_exp']['nb_regs_dispo'] ; $i++ ){
				if(isset($_SESSION['cfg_exp']['REGS_'.$i]) and trim($_SESSION['cfg_exp']['REGS_'.$i]) <> ''){
						$tab_regs[]			= $_SESSION['cfg_exp']['REGS_'.$i];
				}
		}
		// Fin A interfacer
		
		$tab_filtres['annees'] 		= $tab_annees;
		if($GLOBALS['PARAM']['FILTRE']){
			$tab_filtres['filtres'] 	= $tab_filtres;
		}
		if($_SESSION['FILTRE']['FILTRE_2']){
			$tab_filtres['filtres2'] 	= $tab_filtres2;
		}
		$tab_filtres['secteur'] 	= $secteur;
		$tab_filtres['regs'] 		= $tab_regs;
		$tab_filtres['chaine']		= $chaine;
		//echo '<br><pre>';
		//print_r($tab_filtres);
		$export->tab_filtres	=	$tab_filtres ;
		$export->fichier_excel	=	$_SESSION['cfg_exp']['fichier_excel'] ;
		$export->new_fichier_excel	=	$_SESSION['cfg_exp']['export_file_name'] ;
		
		$export->export_excel();
		
?>
