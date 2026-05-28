<?php unset($_SESSION['reg_parents']);
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';

// Lecture de certains paramètres
lit_libelles_page(__FILE__);
/*echo '$get<pre>';
print_r($_GET);*/

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


if ($_GET['chaine'] && $_GET['chaine'] != $_SESSION['chaine']) {

    $_SESSION['chaine'] = $_GET['chaine'];

}

$arbre = new arbre($_SESSION['chaine']);

$html = '';
if(!isset($_GET['typeregid']) && (isset($arbre->chaine[1][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]))){
		$_GET['typeregid'] = $arbre->chaine[1][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
}
if(isset($_GET['type'])) {

    switch($_GET['type']) {

        case 'regs':
            //lecture des éléments du type regroupement
            if(isset($_GET['typeregid'])) {

                require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
                require_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php'; 
				//modif 26/09    
                foreach($arbre->chaine as $i=>$c) {
					if($c[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''] == $_GET['typeregid']) {
				
						$curdepht = $i;
						break;
				
					}
				
				}
                if(count($_POST)==0) {

                    foreach($arbre->chaine as $i=>$c) {
												if( $i == 0 ) continue ; // on saute le premier niveau de reg : région par exple
                        if($c[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''] == $_GET['typeregid']) {

                            $curdepht = $i;
                            break;

                        }

                    }                   
                    
                    // Ajout Alassane
                    $arbre->type_access='config';
                    // fin Ajout Alassane
                    $entete = $arbre->create_entete(0, $curdepht, true);                   
                    
                    $atlas = new atlas($_SESSION['secteur'], $_SESSION['chaine'], $_GET['typeregid'], $entete['code_regroup'],$conn);
										$atlas->modif_atlas = true;
										
                    configurer_barre_nav($atlas->nb_lignes); 

                    $atlas->get_donnees();
                    
                    // recherche des libellés des entêtes du template
                    $atlas->id_name       	 	=   recherche_libelle_page('CodeModAt');
                    $atlas->lib_name      		=   recherche_libelle_page('LibModAt');
                    $atlas->lib_ordre      		=   recherche_libelle_page('NewPere');                    
                    $atlas->lib_entete     		=   recherche_libelle_page('TitRatAtl');
					$atlas->vars_modif_atlas  =   $arbre->vars_modif_atlas;
										
                
                    $html .= '<table width="100%" style="border: none;">';                   
           
                    $html .= '<tr>';
                    $html .= '<td align="center" width ="30%">';

                    if($curdepht > 0) {

												$html .= str_replace('<select', '<select  style=\'width:100%;\'',$entete['html']);

                    }

                    $html .= '</td>';
                    $html .= '<td align="center"  width ="70%">';

                    $html .= $atlas->entete_template; 
										$atlas->template = file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'modif_atlas.html');
                    $html .= $atlas->remplir_template($atlas->template);
                    $html .= $atlas->fin_template; 
					//deb
					$plus_get = array('val', 'type', 'typeregid', 'chaine');
                    for( $i_combo = 0 ; $i_combo < $curdepht ; $i_combo++ ){
                        //if(!isset($_GET['code_regroup_'.$i_combo ]) or trim($_GET['code_regroup_'.$i_combo ]=='')){
                        if(isset($_GET['code_regroup_'.$i_combo])==false  or  trim($_GET['code_regroup_'.$i_combo]=='')){
                            $curcodetyperegroup = $arbre->chaine[$i_combo][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
                            $requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.';';
                            
                            $regroupement = $atlas->conn->execute($requete);

                            $_GET['code_regroup_'.$i_combo ] = $regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
                            
                        }
                        $plus_get[] = 'code_regroup_'.$i_combo ;
                    }
                    //barre de navigation
                    $_SESSION['PLUS_GET'] =$plus_get;
                    $html .= afficher_barre_nav(true,true,$plus_get);
					//fin
					
					
                    //$html .= afficher_barre_nav(true,true, array('val', 'type', 'typeregid'));

                    $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '</table>';
                    $_SESSION['instance_regs']  =   $atlas;  

                } else {
                
                    if(isset($_SESSION['instance_regs'] )){
                
                        $atlas = $_SESSION['instance_regs'];
                
                    }      
                
                    $atlas->get_post_template($_POST);
										$atlas->maj_bdd_modif_atlas($_POST);
                    unset($_SESSION['instance_regs']);
                    
                    // Réaffichage des données après le post
                    require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
                    require_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';                          
                    
                    foreach($arbre->chaine as $i=>$c) {
												if( $i == 0 ) continue ; // on saute le premier niveau de reg : région par exple
                        if($c[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''] == $_GET['typeregid']) {

                            $curdepht = $i;
                            break;

                        }

                    }                   
                    
                    // Ajout Alassane
                    $arbre->type_access='config';
                    // fin Ajout Alassane
                    $entete = $arbre->create_entete(0, $curdepht, true);                   
                    
                    $atlas = new atlas($_SESSION['secteur'], $_SESSION['chaine'], $_GET['typeregid'], $entete['code_regroup'],$conn);
										$atlas->modif_atlas = true;
                    configurer_barre_nav($atlas->nb_lignes); 

                    $atlas->get_donnees();
                    
                    // recherche des libellés des entêtes du template
                    $atlas->id_name        		=   recherche_libelle_page('CodeModAt');
                    $atlas->lib_name       		=   recherche_libelle_page('LibModAt');
                    $atlas->lib_ordre      		=   recherche_libelle_page('NewPere');                    
                    $atlas->lib_entete     		=   recherche_libelle_page('TitRatAtl');
										$atlas->vars_modif_atlas  =   $arbre->vars_modif_atlas;
                
                    $html .= '<table width="100%" style="border: none;">';                   
           
                    $html .= '<tr>';
                    $html .= '<td align="center" width="30%">';

                    if($curdepht > 0) {

												$html .= str_replace('<select', '<select  style=\'width:100%;\'',$entete['html']);

                    }

                    $html .= '</td>';
                    $html .= '<td align="center" width="70%">';

                    $html .= $atlas->entete_template; 
										$atlas->template = file_get_contents($GLOBALS['SISED_PATH_TPL'] . 'modif_atlas.html');
                    $html .= $atlas->remplir_template($atlas->template);
                    $html .= $atlas->fin_template; 
                    $html .= afficher_barre_nav(true,true, array('val', 'type', 'typeregid'));

                    $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '</table>';

                    $_SESSION['instance_regs']  =   $atlas;
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

$nummenu = count($typeregs) - 1;
if($nummenu <> 0){
	$tdwidth = floor(100 / $nummenu);
}

$menu_html = '';

$menu_html .= '<script type="text/Javascript">' . "\n";
$menu_html .= '<!--' . "\n";
$menu_html .= 'function toggle_atlas() {' . "\n";
$menu_html .= "\t".'var frm = document.options;' . "\n";
$menu_html .= "\t".'var value_secteur = frm.systeme.options[frm.systeme.selectedIndex].value;' . "\n";
$menu_html .= "\t".'var value_chaine = frm.chaine.options[frm.chaine.selectedIndex].value;' . "\n";
$menu_html .= "\t".'location.href= \''.$_SERVER['PHP_SELF'].'?val=modif_atlas&type='.$_GET['type'].'&chaine=\'+value_chaine+\'&secteur=\'+value_secteur;' . "\n";
$menu_html .= '}' . "\n";
$menu_html .= '-->' . "\n";
$menu_html .= '</script>' . "\n";

$menu_html .= '<table width="100%" style="border: none;">' . "\n";

$menu_html .= ' <tr>' . "\n";

$menu_html .= '<td colspan="'.$nummenu.'">' . "\n";
$menu_html .= '<table width="100%" style="border: none;">' . "\n"; 
$menu_html .= '<tr>' . "\n";
$menu_html .= '<td style="text-align:center" width="30%">' . "\n";
$menu_html .= '<form name="options">' . "\n";


$menu_html .= '<select name="systeme" onchange="toggle_atlas();" style="width:100%">' . "\n";

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

$menu_html .= '<br>' . "\n";

$menu_html .= '<select name="chaine" onchange="toggle_atlas();" style="width:100%">' . "\n";

// gestion de la taille des champ
$champ_lib_chaine   = get_champ_extraction($GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']);
// Fin de la gestion de la taille des champ

if(is_array($chaines))
foreach($chaines as $c) {

    if(isset($_SESSION['chaine']) && $_SESSION['chaine'] == $c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']]) {

        $selected = ' selected';

    } else {

        $selected = '';

    }
    $menu_html .= '<option value="'.$c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']].'"'.$selected.'>'.$c[$champ_lib_chaine].'</option>' . "\n";

}

$menu_html .= '</select>' . "\n";

$menu_html .= '</form>' . "\n";

$menu_html .= '</td>' . "\n";
$menu_html .= '<td  width="70%" align ="center">' . "\n";
if(isset($_GET['type']) && preg_match('`^regs`', $_GET['type'])) {

    $menu_html .= '<table width ="95%">' . "\n";
		$menu_html .= '<tr><td align="center">';
    foreach($arbre->chaine as $type => $t) {
				if( $type == 0 ) continue ; // on saute le premier niveau de reg : région par exple 
				//$menu_html .= '<a href="'.$_SERVER['PHP_SELF'].'?val=modif_atlas&type=regs&typeregid='.$t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].'">'.$t[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].'</a>';
				$menu_html .= '<input  style="width:100%;" onClick="javascript:location.href=\''.$_SERVER['PHP_SELF'].'?val=modif_atlas&type=regs&typeregid='.$t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].'\'" type="button" value="'.$t[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].'">';
				if($type < (count($arbre->chaine) - 1)) $menu_html .= '<br>';
    }
		$menu_html .= '</td></tr>' . "\n";
		$menu_html .= '</table>' . "\n";
}
$menu_html .= '</td>' . "\n";
$menu_html .= '</tr>' . "\n";
$menu_html .= '</table>' . "\n";
$menu_html .= '</td>' . "\n";

$menu_html .= '</tr>' . "\n";

$menu_html .= '</table>' . "\n";

?>
<br>
<br>
<table width="70%" class="center-table">
    <caption>
    <?php echo recherche_libelle_page('EntModAtl'); ?>
    </caption>
    <tr> 
        <td align="center"> 
            <?php echo $menu_html;

?>
        </td>
    </tr>
    <tr> 
        <td align="center"> <br>
            <?php echo $html;

?>
            <br> </td>
    </tr>
</table>

