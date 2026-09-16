<?php echo '<script language="javascript" src="'.$GLOBALS['SISED_URL_JSC'] . 'js.js"></script>' . "\n";
?>
<?php unset($_SESSION['reg_parents']);
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
// Lecture de certains paramètres
//lit_libelles_page(__FILE__);
lit_libelles_page('/atlas.php');

/* construction de la chaine */

$tab_chaine_secteur =  array();

if(is_array($_SESSION['tab_chaines'])){
		foreach ($_SESSION['tab_chaines'] as $row) {
		
				if ($row[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $_SESSION['secteur']) {
		
						array_push($tab_chaine_secteur, $row);
		
				}
		
		}
}

$nom_combo_chaine      =$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'];
$nom_code_chaine       =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'];

$champs_pop = array();
$champs_pop = array();
if( is_array($GLOBALS['PARAM']['CHAMPS_POPULATION']) && (count($GLOBALS['PARAM']['CHAMPS_POPULATION'])) ){
	foreach($GLOBALS['PARAM']['CHAMPS_POPULATION'] as $i_chp => $chp_pop){
		$champs_pop[] 		= $chp_pop['NOM_CHAMP'];
		$lib_champs_pop[] 	= $chp_pop['LABEL_CHAMP'];
	}
}else{
	$champs_pop[] = $GLOBALS['PARAM']['NB_POPULATION_MALE']	;
	$champs_pop[] = $GLOBALS['PARAM']['NB_POPULATION_FEMALE']	;
	$lib_champs_pop[] 	= $GLOBALS['PARAM']['NB_POPULATION_MALE']	;
	$lib_champs_pop[] 	= $GLOBALS['PARAM']['NB_POPULATION_FEMALE']	;
}

/////Creation de la table population si elle n'existe pas
if(!isset($_GET['typeregid']) && !isset($_GET['chaine']) && !isset($_GET['secteur'])){
	$TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
	$tables = array();
	$existe=false;
	foreach($TabBD as $tab)
	{	
		if(strtoupper($GLOBALS['PARAM']['TYPE_AGE_POPULATION'])==strtoupper($tab))
		{
			$existe=true; break;
		}
	}
	if(!$existe){
		$strRequete = "CREATE TABLE ".$GLOBALS['PARAM']['TYPE_AGE_POPULATION']."(".
			$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']." integer, ".
			$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']." varchar(50), ".
			$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']." integer, ".
			"CONSTRAINT PK_cle PRIMARY KEY (".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'].")".
		")";
		$GLOBALS['conn']->Execute($strRequete);
	}
	
	$existe=false;
	foreach($TabBD as $tab)
	{	
		if(strtoupper($GLOBALS['PARAM']['POPULATION'])==strtoupper($tab))
		{
			$existe=true; break;
		}
	}
	if(!$existe){
		$strRequete = "CREATE TABLE ".$GLOBALS['PARAM']['POPULATION']."(".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']." integer, ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']." integer, ".
			$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']." integer, ";
		
		foreach($champs_pop as $i_chp => $chp_pop){
			$strRequete .= $chp_pop." integer, ";;
		}
		
			$strRequete .="CONSTRAINT PK_cle PRIMARY KEY (".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].",".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT'].",".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'].")".")";
		
		$GLOBALS['conn']->Execute($strRequete);
	}
}

if ( isset($_GET['chaine']) && (trim($_GET['chaine']) <> '') && $_GET['chaine'] != $_SESSION['chaine']) {

    $_SESSION['chaine'] = $_GET['chaine'];

}

// récupération de la première chaine du secteur courant
if( !isset($_GET['chaine']) or (trim($_GET['chaine']) == '')){
	$requete 	= 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.(int)$_SESSION['secteur'].' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].';';	
    $all_ch 	= $GLOBALS['conn']->GetAll($requete);
	$_SESSION['chaine'] = $all_ch[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']];
	$_GET['chaine'] = $_SESSION['chaine'];
}

if( !isset($_GET['secteur']) or (trim($_GET['secteur']) == '')){
	$_GET['secteur'] = $_SESSION['secteur'];
}
//echo '<br> $_SESSION[chaine] = '.$_SESSION['chaine'];

//Recuperation annee population
if(isset($_POST['type_annee']) && $_POST['type_annee']<>''){
	$_SESSION['annee_pop'] = $_POST['type_annee'];
}
elseif(isset($_GET['type_annee']) && $_GET['type_annee']<>''){
	$_SESSION['annee_pop'] = $_GET['type_annee'];
}
elseif(isset($_SESSION['annee']) && $_SESSION['annee']<>''){
	$_SESSION['annee_pop'] = $_SESSION['annee'];
}

$arbre = new arbre($_SESSION['chaine']);

if(isset($_GET['typeregid']) && $_GET['typeregid']<>''){
	$rq="SELECT ".$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']." FROM ".$GLOBALS['PARAM']['TYPE_REGROUPEMENT']." WHERE ".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_REGROUPEMENT']."=".$_GET['typeregid'];
	$lib_entete=$GLOBALS['conn']->GetOne($rq);
}

if(isset($_GET['type'])) {
    switch($_GET['type']) {

        case 'regs':
            //lecture des éléments du type regroupement
            if(isset($_GET['typeregid'])) {

                require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
                require_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     
                
                if(count($_POST)==0) {

                    foreach($arbre->chaine as $i=>$c) {

                        if($c[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''] == $_GET['typeregid']) {

                            $curdepht = $i;
                            break;

                        }

                    }                   
                     // echo "<br>curdepht=$curdepht<br>";
                    // Ajout Alassane
                    $arbre->type_access='config';
                    // fin Ajout Alassane
                    $entete = $arbre->create_entete(0, $curdepht, true);                   
                    
                    $atlas_pop = new atlas_pop($_SESSION['secteur'], $_SESSION['chaine'], $_GET['typeregid'], $entete['code_regroup'],$conn);

                    configurer_barre_nav($atlas_pop->nb_lignes); 

                    $atlas_pop->get_donnees();
                    
					///Generation du template de donnees de population
					$tab_ligne=array();
					if (is_array($atlas_pop->matrice_donnees)){  
						for($ligne=0;$ligne<$atlas_pop->nb_lignes;$ligne++){
							// cas des champs id                   
							if (isset($atlas_pop->matrice_donnees[$ligne][$atlas_pop->champ_id ])){
							  //  echo 'passé la';
								$val_champ_base = $atlas_pop->matrice_donnees[$ligne][$atlas_pop->champ_id ];
								//$val_champ_base = addslashes($val_champ_base);                    
								$tab_ligne[] = $val_champ_base;
							}else{
								$tab_ligne[] = '';
							}
							
						}
					}
					//echo'<pre>';
					//print_r($tab_ligne);
					
					//if(!isset($_GET['typeregid']) && !isset($_GET['chaine']) && !isset($_GET['secteur'])){
						
						$req="SELECT * FROM ".$GLOBALS['PARAM']['TYPE_AGE_POPULATION']." WHERE ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']."<>255"." ORDER BY ".$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'];
						$res=$GLOBALS['conn']->GetAll($req);
						$nb_col=count($res);
						$NB_LIGNE_ECRAN=8;
						$html="<span class=''>\n";
						$html.="  <div >\n";
						$html.="    <table border='1'><caption>".recherche_libelle_page('DebEntDetailPop')." \$lib_entete ".recherche_libelle_page('FinEntDetailPop')."</caption>\n";       
						$html.="        <tr>\n"; 
						$html.="            <td ROWSPAN='2'><img src='client-side/image/b_drop.png' width='16' height='16'></td>\n";
						$html.="            <td ROWSPAN='2' Align='center'>\$id_name</td>\n";
						//$html.="			<td Align='center'>\$lib_ordre</td>\n";
						$html.="			<td ROWSPAN='2' Align='center'>\$lib_name</td>\n";
											foreach ($res as $element){
											// On imprime le libellé des colonnes
												$html		.="<TD align='center' COLSPAN='".count($lib_champs_pop)."'>"; 
												$html 		.= $element[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']];
												$html		.="</TD>\n";
											}
						$html.="		</tr>\n";
						
						$html 			.= "\t"."<TR>"."\n";
								$result_nomenc 		= $res;
								foreach($result_nomenc as $element_result_nomenc){
									foreach($lib_champs_pop as $i_chp => $lib_chp_pop){
										$html .= "\t\t<TD align='center'>".$lib_chp_pop."</TD>\n";
									}
								}
						$html 			.= "\t".'</TR>'."\n";
						$ligne=$tab_ligne[0];
						$l=0;
						$i_TD = 0;
						$classe_fond = 'ligne-impaire';	
						$html.="        <tr>\n"; 
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='checkbox' name='DELETE_0' value=1  onClick=Alert_Supp(this.name); disabled='disabled'></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_0' value='\$".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_0' size='2' Class ='Defaut_Ligne'  readonly></td>\n";
						//$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><input type='text' name='ORDRE_0' value='$ORDRE_0' size='2'  class ='Defaut_Ligne'  readonly /></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='LIBELLE_0' value=\"\$LIBELLE_0\" size='25' readonly='1'></td>\n";
						$i_TD++;						
											foreach($result_nomenc as $element_result_nomenc){		
												foreach($champs_pop as $i_chp => $chp_pop){
													$html .= "\t\t<td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'>";
													$html .= "<INPUT ".' ID=\''.$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']] .'\' '."NAME='".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."' TYPE='text' size='5' ";
													$html .= " VALUE=\"$".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."\""; 
													$html .= "/></TD>\n";
													$i_TD++;
												}
											}
						
						$html.="        </tr>\n";
						$ligne=$tab_ligne[1];
						$l++;
						$i_TD = 0;
						$html.="        <tr>\n";
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='checkbox' name='DELETE_1' value=1  onClick=Alert_Supp(this.name); disabled='disabled'></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_1' value='\$".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_1' size='2' Class ='Defaut_Ligne'  readonly></td>\n";
						//$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><input type='text' name='ORDRE_1' value='$ORDRE_1' size='2' class ='Defaut_Ligne'  readonly /></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='LIBELLE_1' value=\"\$LIBELLE_1\" size='25' readonly='1'></td>\n";
						$i_TD++;						
											foreach($result_nomenc as $element_result_nomenc){		
												foreach($champs_pop as $i_chp => $chp_pop){
													$html .= "\t\t<td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'>";
													$html .= "<INPUT ".' ID=\''.$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']] .'\' '."NAME='".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."' TYPE='text' size='5' ";
													$html .= " VALUE=\"$".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."\""; 
													$html .= "/></TD>\n";
													$i_TD++;
												}
											}
						
						
						$html.="        </tr>\n";
						$ligne=$tab_ligne[2];
						$l++;
						$i_TD = 0;
						$html.="        <tr>\n";
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='checkbox' name='DELETE_2' value=1  onClick=Alert_Supp(this.name); disabled='disabled'></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_2' value='\$".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_2' size='2' Class ='Defaut_Ligne'  readonly></td>\n";
						//$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><input type='text' name='ORDRE_2' value='$ORDRE_2' size='2' class ='Defaut_Ligne'  readonly /></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='LIBELLE_2' value=\"\$LIBELLE_2\" size='25' readonly='1'></td>\n";
						$i_TD++;						
											foreach($result_nomenc as $element_result_nomenc){	
												foreach($champs_pop as $i_chp => $chp_pop){	
													$html .= "\t\t<td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'>";
													$html .= "<INPUT ".' ID=\''.$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']] .'\' '."NAME='".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."' TYPE='text' size='5' ";
													$html .= " VALUE=\"$".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."\""; 
													$html .= "/></TD>\n";
													$i_TD++;
												}
											}
						
						$html.="        </tr>\n";
						$ligne=$tab_ligne[3];
						$l++;
						$i_TD = 0;
						$html.="        <tr>\n"; 
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='checkbox' name='DELETE_3' value=1  onClick=Alert_Supp(this.name); disabled='disabled'></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_3' value='\$".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_3' size='2' Class ='Defaut_Ligne'  readonly></td>\n";
						//$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><input type='text' name='ORDRE_3' value='$ORDRE_3' size='2' class ='Defaut_Ligne'  readonly /></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='LIBELLE_3' value=\"\$LIBELLE_3\" size='25' readonly='1'></td>\n";
						$i_TD++;						
											foreach($result_nomenc as $element_result_nomenc){	
												foreach($champs_pop as $i_chp => $chp_pop){	
													$html .= "\t\t<td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'>";
													$html .= "<INPUT ".' ID=\''.$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']] .'\' '."NAME='".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."' TYPE='text' size='5' ";
													$html .= " VALUE=\"$".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."\""; 
													$html .= "/></TD>\n";
													$i_TD++;
												}
											}
						
						$html.="        </tr>\n";
						$ligne=$tab_ligne[4];
						$l++;
						$i_TD = 0;
						$html.="		<tr>\n"; 
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='checkbox' name='DELETE_4' value=1  onClick=Alert_Supp(this.name); disabled='disabled'></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_4' value='\$".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_4' size='2' Class ='Defaut_Ligne'  readonly></td>\n";
						//$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><input type='text' name='ORDRE_4' value='$ORDRE_4' size='2' class ='Defaut_Ligne'  readonly /></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='LIBELLE_4' value=\"\$LIBELLE_4\" size='25' readonly='1'></td>\n";
						$i_TD++;						
											foreach($result_nomenc as $element_result_nomenc){	
												foreach($champs_pop as $i_chp => $chp_pop){	
													$html .= "\t\t<td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'>";
													$html .= "<INPUT ".' ID=\''.$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']] .'\' '."NAME='".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."' TYPE='text' size='5' ";
													$html .= " VALUE=\"$".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."\""; 
													$html .= "/></TD>\n";
													$i_TD++;
												}
											}
						
						$html.="        </tr>\n";
						$ligne=$tab_ligne[5];
						$l++;
						$i_TD = 0;
						$html.="		<tr>\n"; 
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='checkbox' name='DELETE_5' value=1  onClick=Alert_Supp(this.name); disabled='disabled'></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_5' value='\$".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_5' size='2' Class ='Defaut_Ligne'  readonly></td>\n";
						//$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><input type='text' name='ORDRE_5' value='$ORDRE_5' size='2' class ='Defaut_Ligne'  readonly /></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='LIBELLE_5' value=\"\$LIBELLE_5\" size='25' readonly='1'></td>\n";
						$i_TD++;						
											foreach($result_nomenc as $element_result_nomenc){	
												foreach($champs_pop as $i_chp => $chp_pop){	
													$html .= "\t\t<td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'>";
													$html .= "<INPUT ".' ID=\''.$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']] .'\' '."NAME='".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."' TYPE='text' size='5' ";
													$html .= " VALUE=\"$".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."\""; 
													$html .= "/></TD>\n";
													$i_TD++;
												}
											}
						
						$html.="        </tr>\n";
						$ligne=$tab_ligne[6];
						$l++;
						$i_TD = 0;
						$html.="		<tr>\n"; 
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='checkbox' name='DELETE_6' value=1  onClick=Alert_Supp(this.name); disabled='disabled'></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_6' value='\$".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_6' size='2' Class ='Defaut_Ligne'  readonly></td>\n";
						//$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><input type='text' name='ORDRE_6' value='$ORDRE_6' size='2' class ='Defaut_Ligne'  readonly /></td>\n";             
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='LIBELLE_6' value=\"\$LIBELLE_6\" size='25' readonly='1'></td>\n";
						$i_TD++;						
											foreach($result_nomenc as $element_result_nomenc){	
												foreach($champs_pop as $i_chp => $chp_pop){	
													$html .= "\t\t<td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'>";
													$html .= "<INPUT ".' ID=\''.$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']] .'\' '."NAME='".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."' TYPE='text' size='5' ";
													$html .= " VALUE=\"$".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."\""; 
													$html .= "/></TD>\n";
													$i_TD++;
												}
											}
						
						$html.="        </tr>\n";
						$ligne=$tab_ligne[7];
						$l++;
						$i_TD = 0;
						$html.="		<tr>\n";
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='checkbox' name='DELETE_7' value=1  onClick=Alert_Supp(this.name); disabled='disabled'></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_7' value='\$".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']."_7' size='2' Class ='Defaut_Ligne'  readonly></td>\n";
						//$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><input type='text' name='ORDRE_7' value='$ORDRE_7' size='2' class ='Defaut_Ligne'  readonly /></td>\n";
						$i_TD++;
						$html.="              <td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'><INPUT type='text' name='LIBELLE_7' value=\"\$LIBELLE_7\" size='25' readonly='1'></td>\n";
						$i_TD++;						
											foreach($result_nomenc as $element_result_nomenc){	
												foreach($champs_pop as $i_chp => $chp_pop){	
													$html .= "\t\t<td class='".$classe_fond."'  id='".$classe_fond.'_'.$l.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($l,$NB_LIGNE_ECRAN)'>";
													$html .= "<INPUT ".' ID=\''.$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']] .'\' '."NAME='".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."' TYPE='text' size='5' ";
													$html .= " VALUE=\"$".$chp_pop."_".$ligne."_".$element_result_nomenc[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION']]."\""; 
													$html .= "/></TD>\n";
													$i_TD++;
												}
											}
						
						$html.="        </tr>\n";
						$html.="    </table>\n";
						$html.="</div>    <br>\n";
						
						file_put_contents($GLOBALS['SISED_PATH_TPL'] . 'atlas_pop.html', $html);
					//}
					////Fin generation du template de donnees de population

                    // recherche des libellés des entêtes du template
                    $atlas_pop->id_name        =   $atlas_pop->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'regroupement');
                    $atlas_pop->lib_name       =   $atlas_pop->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'regroupement');
                    $atlas_pop->lib_ordre      =   $atlas_pop->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'regroupement');                    
                    $atlas_pop->lib_entete      =   $atlas_pop->recherche_libelle_page('IdEntete',$_SESSION['langue'],'regroupement');
                	
					$html = '';
                    $html .= '<table width="100%" style="border: none;">';                   
           
                    $html .= '<tr>';
                    $html .= '<td align="center" width="30%">';

                    if($curdepht > 0) {

                            $html .= str_replace('<select', '<select  style=\'width:100px;\'',$entete['html']);

                    }

                    $html .= '</td>';
                    $html .= '<td align="center" width="70%">';

                    $html .= $atlas_pop->entete_template; 
                    $html .= $atlas_pop->remplir_template(file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'atlas_pop.html'), $lib_entete);
                    $html .= $atlas_pop->fin_template; 
                    
                    $plus_get = array('val', 'type', 'typeregid', 'chaine','type_annee');
                    for( $i_combo = 0 ; $i_combo < $curdepht ; $i_combo++ ){
                        if(!isset($_GET['code_regroup_'.$i_combo ]) or trim($_GET['code_regroup_'.$i_combo ]=='')){
                            $curcodetyperegroup = $arbre->chaine[$i_combo][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                            $requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.';';
            
                            $regroupement = $atlas_pop->conn->execute($requete);

                            $_GET['code_regroup_'.$i_combo ] = $regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
                            
                        }
                        $plus_get[] = 'code_regroup_'.$i_combo ;
                    }
                    //echo '<pre>';
                    //print_r($plus_get);
                    $html .= afficher_barre_nav(true,true,$plus_get);

                    $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '</table>';
                    $_SESSION['instance_regs_pop']  =   $atlas_pop;  

                } else {
                
                    if(isset($_SESSION['instance_regs_pop'] )){
                
                        $atlas_pop = $_SESSION['instance_regs_pop'];
                
                    }      
                	$nomtableliee=$GLOBALS['PARAM']['POPULATION'];
                    $atlas_pop->get_post_template($_POST);
                    $atlas_pop->comparer($atlas_pop->matrice_donnees[$nomtableliee],$atlas_pop->matrice_donnees_post[$nomtableliee]);                   
                    $atlas_pop->maj_bdd();
                    unset($_SESSION['instance_regs_pop']);
                    
                    // Réaffichage des données après le post
                    require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
                    require_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';                          
                    
                    foreach($arbre->chaine as $i=>$c) {

                        if($c[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''] == $_GET['typeregid']) {

                            $curdepht = $i;
                            break;

                        }

                    }                   
                    
                    // Ajout Alassane
                    $arbre->type_access='config';
                    // fin Ajout Alassane
                    $entete = $arbre->create_entete(0, $curdepht, true);                   
                    $atlas_pop = new atlas_pop($_SESSION['secteur'], $_SESSION['chaine'], $_GET['typeregid'], $entete['code_regroup'],$conn);
                    
                    configurer_barre_nav($atlas_pop->nb_lignes); 

                    $atlas_pop->get_donnees();
                    
                    // recherche des libellés des entêtes du template
                    $atlas_pop->id_name        =   $atlas_pop->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'regroupement');
                    $atlas_pop->lib_name       =   $atlas_pop->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'regroupement');
                    $atlas_pop->lib_ordre      =   $atlas_pop->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'regroupement');                    
                    $atlas_pop->lib_entete      =   $atlas_pop->recherche_libelle_page('IdEntete',$_SESSION['langue'],'regroupement');
                
                    $html .= '<table width="100%" style="border: none;">';                   
           
                    $html .= '<tr>';
                    $html .= '<td align="center" width="30%">';

                    if($curdepht > 0) {

                       $html .= str_replace('<select', '<select  style=\'width:100px;\'',$entete['html']);

                    }

                    $html .= '</td>';
                    $html .= '<td align="center" width="70%">';

                    $html .= $atlas_pop->entete_template; 
                    $html .= $atlas_pop->remplir_template(file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'atlas_pop.html'), $lib_entete);
                    $html .= $atlas_pop->fin_template; 
					//debut 07-12 Yacine
                    $plus_get = array('val', 'type', 'typeregid', 'chaine','type_annee');
                    for( $i_combo = 0 ; $i_combo < $curdepht ; $i_combo++ ){
                        if(!isset($_GET['code_regroup_'.$i_combo ]) or trim($_GET['code_regroup_'.$i_combo ]=='')){
                            $curcodetyperegroup = $arbre->chaine[$i_combo][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                            $requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.';';
            
                            $regroupement = $atlas_pop->conn->execute($requete);

                            $_GET['code_regroup_'.$i_combo ] = $regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
                            
                        }
                        $plus_get[] = 'code_regroup_'.$i_combo ;
                    }
                    //fin 07-12 Yacine
                    $html .= afficher_barre_nav(true,true,$plus_get);
                    //$html .= afficher_barre_nav(true,true, array('val', 'type', 'typeregid'));

                    $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '</table>';

                    $_SESSION['instance_regs_pop']  =   $atlas_pop;
                    // Fin réaffichage des données après le post
                
                }

            }

        break;

    }

} else {

    //lecture des typereg existants
    $typeregs = $arbre->get_typeregs();

}

//création du menu
$typeregs = $arbre->get_typeregs();
$chaines = $arbre->get_chaines_simples();

$nummenu = count($typeregs);
if($nummenu <> 0){
	$tdwidth = floor(100 / $nummenu);
}

$menu_html = '';

$menu_html .= '<script type="text/Javascript">' . "\n";
$menu_html .= '<!--' . "\n";

$menu_html .= 'function toggle_pop_page(annee, sys, ch) {' . "\n";
$menu_html .= "\t".'location.href= \''.$_SERVER['PHP_SELF'].'?val=pop&type='.$_GET['type'].'&chaine=\'+ch+\'&type_annee=\'+annee+\'&secteur=\'+sys;' . "\n";
$menu_html .= '}' . "\n";
$menu_html .= '-->' . "\n";
$menu_html .= '</script>' . "\n";

$menu_html .= '<table  style="border: none;">' . "\n";

$menu_html .= ' <tr>' . "\n";

$menu_html .= '<td colspan="'.$nummenu.'">' . "\n";
$menu_html .= '<table  style="border: none;">' . "\n"; 
$menu_html .= '<tr>' . "\n";

//$menu_html .= '<td width="19%" style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?val=pop&type=typeregs&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'.recherche_libelle_page('IdEntity') .'</a></td>' . "\n";
//$menu_html .= '<td width="19%" style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?val=pop&type=geschaines&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'.recherche_libelle_page('IdChaine').'</a></td>' . "\n";
//$menu_html .= '<td width="19%" style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?val=pop&type=edichaines&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'.recherche_libelle_page('IdEdition').'</a></td>' . "\n";
//$menu_html .= '<td  style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?val=pop&type=regs&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'.recherche_libelle_page('IdDivision').'</a></td>' . "\n";
$menu_html .= '<td style="text-align:center;"  nowrap="nowrap">'. "\n";
$menu_html .= '<form name="options">' . "\n";
$menu_html .= '<select name="population" disabled="disabled" >' . "\n";

$menu_html .= '<option value="'.$GLOBALS['PARAM']['POPULATION'].'">'.$GLOBALS['PARAM']['POPULATION'].'</option>' . "\n";

$menu_html .= '</select>' . "\n";
//recherche_libelle_page('type_age').
$menu_html .= '&nbsp;&nbsp;&nbsp;&nbsp;<select name="type_age" disabled="disabled" >' . "\n";

$menu_html .= '<option value="'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'].'">'.$GLOBALS['PARAM']['TYPE_AGE_POPULATION'].'</option>' . "\n";

$menu_html .= '</select>' . "\n";

$menu_html .= '&nbsp;&nbsp;&nbsp;&nbsp;<select  name="type_annee" onchange="toggle_pop_page(type_annee.value, systeme.value, chaine.value);">' . "\n";
$req="SELECT * FROM ".$GLOBALS['PARAM']['TYPE_ANNEE']." WHERE ".$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']."<>255 ORDER BY ".$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
$res=$GLOBALS['conn']->GetAll($req);
foreach($res as $rs){
	$selected="";
	if($rs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]==$_SESSION['annee_pop']){
		$selected=" selected='selected'";
	}
	$menu_html .= '<option value="'.$rs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']].'"'.$selected.'>'.$rs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']].'</option>' . "\n";
}
$menu_html .= '</select>' . "\n";

$menu_html .= '&nbsp;&nbsp;&nbsp;&nbsp;<select  name="systeme" onchange="toggle_pop_page(type_annee.value, systeme.value, chaine.value);" >' . "\n";

// Gestion de la taille des éléments Modif alassane
$taille_max_secteur =   0;
// Fin de la gestion de la taille des éléments Modif alassane

foreach($_SESSION['tab_secteur'] as $s) {

    if(isset($_SESSION['secteur']) && $_SESSION['secteur'] == $s[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'']) {

        $selected = ' selected';

    } else {

        $selected = '';

    }
    // Gestion Alassasne
    if ($taille_max_secteur < strlen($s['LIBELLE'])){
        $taille_max_secteur =   strlen($s['LIBELLE']);
    }
    // Fin gestion alassane

    $menu_html .= '<option value="'.$s[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].''].'"'.$selected.'>'.$s['LIBELLE'].'</option>' . "\n";

}

$menu_html .= '</select>' . "\n";

//$menu_html .= '<br>' . "\n";

$menu_html .= '&nbsp;&nbsp;&nbsp;&nbsp;<select  name="chaine" onchange="toggle_pop_page(type_annee.value, systeme.value, chaine.value);" >' . "\n";

// gestion de la taille des champ
$champ_lib_chaine   = get_champ_extraction($GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']);
// Fin de la gestion de la taille des champ

if(is_array($chaines))
foreach($chaines as $c) {

    if(isset($_SESSION['chaine']) && $_SESSION['chaine'] == $c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']]) {

        $selected = ' selected';

    } else {

        $selected = '';

    }/*
    // Modif alassane
    if ($taille_max_secteur > 0 ) {       
        if (strlen($c[$champ_lib_chaine])<$taille_max_secteur ){               
                $taille_ch  =   $taille_max_secteur -strlen($c[$champ_lib_chaine]);
               
                for($i=0; $i< 2*($taille_ch-3) ;$i++) {
                    $c[$champ_lib_chaine].= "&nbsp;";                    
                }                
                $taille_max_secteur = 0;
        }
    }*/
    // Fin Modif alassane

    //$menu_html .= '<option value="'.$c['CODE_TYPE_CHAINE_REGROUPEMENT'].'"'.$selected.'>'.$c['LIBELLE_TYPE_CHAINE_REGROUPEMENT'].'</option>' . "\n";
    $menu_html .= '<option value="'.$c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']].'"'.$selected.'>'.$c[$champ_lib_chaine].'</option>' . "\n";

}

$menu_html .= '</select>&nbsp;&nbsp;&nbsp;&nbsp;' . "\n";

$menu_html .= '</form>' . "\n";

$menu_html .= '</td>' . "\n";
$menu_html .= '</tr>' . "\n";
$menu_html .= '</table>' . "\n";
$menu_html .= '</td>' . "\n";

$menu_html .= '</tr>' . "\n";

if(isset($_GET['type']) && preg_match('`^regs`', $_GET['type'])) {

    $menu_html .= '<tr>' . "\n";

    foreach($arbre->chaine as $t) {

        $menu_html .= '<td align="center" style="border: thin black solid;"><a href="'.$_SERVER['PHP_SELF'].'?val=pop&type=regs&typeregid='.$t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].'&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'&type_annee='.$_SESSION['annee_pop'].'">'.$t[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].'</a></td>' . "\n";

    }

    $menu_html .= '</tr>' . "\n";

}

$menu_html .= '</table>' . "\n";

?>
<br>
<br>
<table class="center-table">
<caption><?php echo recherche_libelle_page('IdEntPopData') ?></caption>
<tr>
<td align="center">
<?php echo $menu_html;

?>
</td>
</tr>
<tr>
<td align="center">
<?php echo $html;

?>
</td>
</tr>
</table>
