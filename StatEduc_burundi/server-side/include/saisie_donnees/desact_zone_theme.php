<?php 
if(isset($_GET['liste_themes']) && $_GET['liste_themes']<>''){
	if($_GET['bool']=="false"){
		$liste_themes = explode(':', $_GET['liste_themes']);
		if(isset($_SESSION['list_themes_desact'])) unset($_SESSION['list_themes_desact']);
		foreach($liste_themes as $id_th){
			$_SESSION['list_themes_desact'][] = $id_th;
		}
	}else{
		if(isset($_SESSION['list_themes_desact'])) unset($_SESSION['list_themes_desact']);
	}
}elseif(isset($_GET['liste_zones']) && $_GET['liste_zones']<>''){
	$long_syst_id=strlen(''.$_SESSION['secteur']);
	$long_theme_syst_id=strlen(''.$_GET['theme']);
	$long_theme_id=$long_theme_syst_id-$long_syst_id;
	$str_theme_id=substr($_GET['theme'],0,$long_theme_id);
	
	$tab_zones = explode(':',$_GET['liste_zones']);
	$liste_val_champs = "";
	$cpt_zone = 0;
	foreach($tab_zones as $zone){
		$requete =" SELECT	DICO_ZONE_SYSTEME.TYPE_OBJET, DICO_ZONE.TABLE_MERE, DICO_ZONE.TYPE_ZONE_BASE,
					DICO_ZONE.TABLE_FILLE, DICO_ZONE.SQL_REQ, DICO_ZONE.CHAMP_FILS, DICO_ZONE.CHAMP_PERE, DICO_ZONE.ID_ZONE_REF
					FROM	DICO_ZONE_SYSTEME, DICO_ZONE, DICO_TRADUCTION
					WHERE	DICO_ZONE_SYSTEME.ID_ZONE = DICO_ZONE.ID_ZONE
					AND DICO_TRADUCTION.CODE_NOMENCLATURE=DICO_ZONE.ID_ZONE 
					AND	DICO_ZONE.ID_THEME = ".$str_theme_id."
					AND DICO_ZONE.CHAMP_PERE = '".$zone."'
					AND	DICO_ZONE_SYSTEME.ID_SYSTEME = ".$_SESSION['secteur']." 
					AND	DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme'
					AND	DICO_ZONE_SYSTEME.ACTIVER = 1
					AND NOM_TABLE='DICO_ZONE'
					AND CODE_LANGUE='".$_SESSION['langue']."'
					ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE
					";
		
		$col = $GLOBALS['conn_dico']->GetAll($requete);
		if(is_array($col) && count($col)>0){
			if(($col[0]['TABLE_FILLE']<>'' && $col[0]['SQL_REQ']<>'' && $col[0]['TYPE_OBJET']<>'combo') || ($col[0]['ID_ZONE_REF']<>'') || ($col[0]['TYPE_OBJET']=='booleen')){
				$champ_nomenc = "";
				if($col[0]['TYPE_OBJET'] == 'liste_radio' || $col[0]['TYPE_OBJET'] == 'liste_checkbox'){
					$list_val_nomenc = requete_nomenclature($col[0]['TABLE_FILLE'], $col[0]['CHAMP_FILS'], $col[0]['CHAMP_PERE'], $_SESSION['langue'], $_SESSION['secteur'], $col[0]['SQL_REQ']);
					$champ_nomenc = $col[0]['CHAMP_PERE'];
				}elseif($col[0]['TYPE_OBJET'] == 'text_valeur_multiple' || $col[0]['TYPE_OBJET'] == 'text' || $col[0]['TYPE_OBJET'] == 'combo' || $col[0]['TYPE_OBJET'] == 'checkbox'){
					$zone_ref = get_zone_ref_of($col[0]['ID_ZONE_REF']);
					$list_val_nomenc = requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $_SESSION['langue'], $_SESSION['secteur'], $zone_ref['SQL_REQ']);
					$champ_nomenc = $zone_ref['CHAMP_FILS'];
				}elseif($col[0]['TYPE_OBJET'] == 'booleen'){
					$list_val_nomenc = requete_nomenclature_bool($col[0]['CHAMP_PERE'], $_SESSION['langue']);
					$champ_nomenc = $col[0]['CHAMP_PERE'];
				}				
				$cpt_val = 0;
				foreach($list_val_nomenc as $val_nomenc){
					if($cpt_zone==0){
						if($cpt_val==0){
							$liste_val_champs .= $val_nomenc[get_champ_extract($champ_nomenc)];
						}else{
							$liste_val_champs .= ";".$val_nomenc[get_champ_extract($champ_nomenc)];
						}
					}else{
						if($cpt_val==0){
							$liste_val_champs .= "#".$val_nomenc[get_champ_extract($champ_nomenc)];
						}else{
							$liste_val_champs .= ";".$val_nomenc[get_champ_extract($champ_nomenc)];
						}
					}
					$cpt_val++;
				}
				$cpt_zone++;
			}else{
				$liste_val_champs .= "#";
			}
		}
	}
	echo $liste_val_champs;
}
?>