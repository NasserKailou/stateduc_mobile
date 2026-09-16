<?php unset($_SESSION['reg_parents']);
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';

// Lecture de certains paramètres
lit_libelles_page(__FILE__);

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
$arbre = new arbre($_SESSION['chaine']);

$html = '';

if(isset($_GET['type'])) {
    switch($_GET['type']) {

        case 'typeregs':        
        //lecture des éléments du type regroupement  
               
          if (count($_POST)==0){                
                require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
                require_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     
                
                $lib_nom_table=$GLOBALS['PARAM']['TYPE_REGROUPEMENT'];
                $lire_dico          =   false;
                $insertion_dico     =   true;
                $afficher_secteur   =   false;
                
                $typeregroupement = new nomenclature($_SESSION['secteur'], $lib_nom_table,'','',false, $_SESSION['langue'],$lire_dico, $insertion_dico,$afficher_secteur ,$conn); 
         
                // Configuration de la barre de navigation
                configurer_barre_nav($typeregroupement->nb_lignes); 
                
                // recherche des libellés des entêtes du template
                $typeregroupement->id_name        =   $typeregroupement->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'typeregroupement');
                $typeregroupement->lib_name       =   $typeregroupement->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'typeregroupement');
                $typeregroupement->lib_ordre      =   $typeregroupement->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'typeregroupement');   
                $typeregroupement->lib_entete     =   $typeregroupement->recherche_libelle_page('IdEntete',$_SESSION['langue'],'typeregroupement');   
                $typeregroupement->get_donnees();
                
                    $html .= '<table width="100%" style="border: none;">';                  
                    
                    $html .= '<tr>';
                    $html .= '<td align="center">';
                    $html .= '</td>';
                    $html .= '<td align="center">';

                    $html .= $typeregroupement->entete_template; 
                    $html .= $typeregroupement->remplir_template($typeregroupement->template);
                    $html .= $typeregroupement->fin_template; 
                    $html .= afficher_barre_nav(true,true, array('val', 'type', 'typeregs'));
                    

                    $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '</table>';                    

                    $_SESSION['instance_typeregs']  =   $typeregroupement;  

                } else {              
           
                   require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php'; 
                   require_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';                    
                 
                   if (isset($_SESSION['instance_typeregs'])){
                        $typeregroupement   =   $_SESSION['instance_typeregs'];
                    }                    
                    
                    $typeregroupement->get_post_template($_POST);
                    $typeregroupement->comparer($typeregroupement->matrice_donnees_template,$typeregroupement->donnees_post);                    
                    $typeregroupement->maj_bdd($typeregroupement->matrice_donnees_bdd); 
                    unset ($_SESSION['instance_typeregs']);
                    
                     // Reaffichage des données de la page couramment postée
                
                    $lib_nom_table=$GLOBALS['PARAM']['TYPE_REGROUPEMENT'];
                    $lire_dico          =   false;
                    $insertion_dico     =   false;
                    $afficher_secteur   =   false;
                    
                    $typeregroupement = new nomenclature($_SESSION['secteur'], $lib_nom_table,'','',false, $_SESSION['langue'],$lire_dico, $insertion_dico,$afficher_secteur ,$conn); 
             
                    // Configuration de la barre de navigation
                    configurer_barre_nav($typeregroupement->nb_lignes); 
                    
                    // recherche des libellés des entêtes du template
                    $typeregroupement->id_name        =   $typeregroupement->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'typeregroupement');
                    $typeregroupement->lib_name       =   $typeregroupement->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'typeregroupement');
                    $typeregroupement->lib_ordre      =   $typeregroupement->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'typeregroupement');   
                    $typeregroupement->lib_entete     =   $typeregroupement->recherche_libelle_page('IdEntete',$_SESSION['langue'],'typeregroupement');   
                    $typeregroupement->get_donnees();
                    
                        $html .= '<table width="100%" style="border: none;">';                  
                        
                        $html .= '<tr>';
                        $html .= '<td align="center">';
                        $html .= '</td>';
                        $html .= '<td align="center">';
    
                        $html .= $typeregroupement->entete_template; 
                        $html .= $typeregroupement->remplir_template($typeregroupement->template);
                        $html .= $typeregroupement->fin_template; 
                        $html .= afficher_barre_nav(true,true, array('val', 'type', 'typeregs'));
                        
    
                        $html .= '</td>';
                        $html .= '</tr>';
                        $html .= '</table>';                    
    
                        $_SESSION['instance_typeregs']  =   $typeregroupement; 
                    
                    // Fin du reaffichage des données de la page couramment postée

                    
                }
          

        break;

        case 'geschaines':
        //lecture des éléments du type regroupement          
          if (count($_POST)==0){
                require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
                require_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     
                
                $lib_nom_table=$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'];                
               
                $typechaine = new chaine($_SESSION['secteur'], $lib_nom_table, $_SESSION['langue'],$conn); 
                
                // recherche des libellés des entêtes du template
                $typechaine->id_name        =   $typechaine->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'chaineregroupement');
                $typechaine->lib_name       =   $typechaine->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'chaineregroupement');
                $typechaine->lib_ordre      =   $typechaine->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'chaineregroupement'); 
                $typechaine->lib_entete     =   $typechaine->recherche_libelle_page('IdEntete',$_SESSION['langue'],'chaineregroupement'); 
         
                
                $typechaine->champ_id             =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'];
                $typechaine->champ_lib            =$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'];
                $typechaine->champ_ordre          =$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'];
                $typechaine->champ_systeme        =$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];  
								
                
                // Configuration de la barre de navigation
                configurer_barre_nav($typechaine->nb_lignes); 
                
                $typechaine->get_donnees();
                
                    $html .= '<table width="100%" style="border: none;">';                  
                   
                    $html .= '<tr>';
                    $html .= '<td align="center">';
                    $html .= '</td>';
                    $html .= '<td align="center">';

                    $html .= $typechaine->entete_template; 
                    $html .= $typechaine->remplir_template($typechaine->template);
                    $html .= $typechaine->fin_template; 
                    $html .= afficher_barre_nav(true,true, array('val', 'type', 'geschaines'));
                    

                    $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '</table>';                    

                    $_SESSION['instance_geschaines']  =   $typechaine;
                  

                } else {
                    
                    require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
                    require_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';                      
           
                   if (isset($_SESSION['instance_geschaines'])){
                        $typechaine   =   $_SESSION['instance_geschaines'];
                    }
                    
                   
                    $typechaine->get_post_template($_POST);
                    $typechaine->comparer($typechaine->matrice_donnees_template,$typechaine->donnees_post);
                    $typechaine->maj_bdd($typechaine->matrice_donnees_bdd);  
                    unset($_SESSION['instance_geschaines']);  
                    
					// bass : maj des tab_chaines en session
					set_tab_session('chaines', '');  
					// fin bass : maj des tab_chaines en session
										
                    // Réaffichage des données de la page après avoir posté                   
                
                    $lib_nom_table=''.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'';                
                   
                    $typechaine = new chaine($_SESSION['secteur'], $lib_nom_table, $_SESSION['langue'],$conn); 
                    
                    // recherche des libellés des entêtes du template
                    $typechaine->id_name        =   $typechaine->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'chaineregroupement');
                    $typechaine->lib_name       =   $typechaine->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'chaineregroupement');
                    $typechaine->lib_ordre      =   $typechaine->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'chaineregroupement'); 
                    $typechaine->lib_entete     =   $typechaine->recherche_libelle_page('IdEntete',$_SESSION['langue'],'chaineregroupement'); 
             
                    
                    $typechaine->champ_id             =   ''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'';
                    $typechaine->champ_lib            =   ''.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'';
                    $typechaine->champ_ordre          =   ''.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'';
                    $typechaine->champ_systeme        =   ''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'';  
                    
                    // Configuration de la barre de navigation
                    configurer_barre_nav($typechaine->nb_lignes); 
                    
                    $typechaine->get_donnees();
                        $html .= '<table width="100%" style="border: none;">';                  
                       
                        $html .= '<tr>';
                        $html .= '<td align="center">';
                        $html .= '</td>';
                        $html .= '<td align="center">';
    
                        $html .= $typechaine->entete_template; 
                        $html .= $typechaine->remplir_template($typechaine->template);
                        $html .= $typechaine->fin_template; 
                        $html .= afficher_barre_nav(true,true, array('val', 'type', 'geschaines'));
                        
    
                        $html .= '</td>';
                        $html .= '</tr>';
                        $html .= '</table>';                    
    
                        $_SESSION['instance_geschaines']  =   $typechaine;  
                    
                    // Fin du réaffichage des données de la page après avoir posté                               
                }

        break;

        case 'edichaines':

            if(count($_POST)>0) {

                if(isset($_POST['reg_toadd']) || isset($_POST['reg_todel'])) {

                    if(isset($_POST['reg_toadd']) && (trim($_POST['reg_toadd'])<>'') && !$arbre->add_chaine_typereg($_POST['reg_toadd'])) {

                        $html .= '<script type="text/Javascript">'."\n";
                        $html .= '<!--'."\n";
                        $html .= 'alert(\'Problème de cohérence de la chaine\');'."\n";
                        $html .= '-->'."\n";
                        $html .= '</script>'."\n";

                    }

                    if(isset($_POST['reg_todel']) && (trim($_POST['reg_todel'])<>'') && !$arbre->del_chaine_typereg($_POST['reg_todel'])) {

                        $html .= '<script type="text/Javascript">'."\n";
                        $html .= '<!--'."\n";
                        $html .= 'alert(\'Problème de cohérence de la chaine\');'."\n";
                        $html .= '-->'."\n";
                        $html .= '</script>'."\n";

                    }

                } else {

                    $regs = array();
    
                    foreach($_POST as $i=>$v) {
    
                        if(preg_match('`^regs_(\d+)`', $i, $reg)) {
    
                            $regs[$reg[1]] = $v;
    
                        }
    
                    }
                    
                    asort($regs);
    
                    if(!$arbre->save_chaine_typeregs($regs)) {

                        $html .= '<script type="text/Javascript">'."\n";
                        $html .= '<!--'."\n";
                        $html .= 'alert(\'Problème de cohérence de la chaine\');'."\n";
                        $html .= '-->'."\n";
                        $html .= '</script>'."\n";

                    }

                }

            }
            
            $html .= '<style type="text/css">'."\n";
            $html .= "\n";

            $html .= '#ordering-view {'."\n";
            $html .= 'position: relative;'."\n";
            $html .= 'width: 300px;'."\n";
            $html .= 'font: menu;'."\n";
            $html .= 'background: WindowFrame;'."\n";
            $html .= 'padding: 1px;'."\n";
            $html .= '}'."\n";

            $html .= '.orderingItem {'."\n";
            $html .= 'cursor: default;'."\n";
            $html .= 'position: relative;'."\n";
            $html .= 'text-align: center;'."\n";
            $html .= 'top: 0;'."\n";
            $html .= 'left: 0;'."\n";
            $html .= 'width: 296px;'."\n";
            $html .= 'margin: 1px;'."\n";
            $html .= 'font-size: 12px;'."\n";
            $html .= 'height: 18px;'."\n";
            $html .= 'background: ButtonFace;'."\n";
            $html .= 'color: ButtonText;'."\n";
            $html .= 'border-color: Menu;'."\n";
            $html .= 'border-width: 1px;'."\n";
            $html .= 'border-style: solid;'."\n";
            $html .= '}'."\n";

            $html .= '.orderingItem.hoveredItem {'."\n";
            $html .= 'border-color: Menu;'."\n";
            $html .= 'color: MenuText;'."\n";
            $html .= '}'."\n";

            $html .= '.orderingItem.activeDrag {'."\n";
            $html .= 'background-color: Highlight;'."\n";
            $html .= 'color: HighlightText;'."\n";
            $html .= 'border-color: activeborder;'."\n";
            $html .= 'opacity: .8;'."\n";
            $html .= 'filter: alpha(opacity=80);'."\n";
            $html .= '}'."\n";

            $html .= '.orderingItem.focusedItem {'."\n";
            $html .= 'outline: 1px solid infotext;'."\n";
            $html .= 'background-color: white;'."\n";
            $html .= 'color: #333;'."\n";
            $html .= 'border-color: black;'."\n";
            $html .= '}'."\n";

            $html .= '.itemInsertBefore {'."\n";
            $html .= 'border-top: 1px solid #036;'."\n";
            $html .= 'margin: 0 1px;'."\n";
            $html .= '}'."\n";

            $html .= '.itemInsertAfter {'."\n";
            $html .= 'border-bottom: 1px solid #036;'."\n";
            $html .= 'margin: 0 1px;'."\n";
            $html .= '}'."\n";

            $html .= '</style>'."\n";

            $html .= "\n";

            $html .= '<script type="text/javascript" src="'.$GLOBALS['SISED_URL_JSC'].'dragndrop/drag.js"></script>'."\n";
            $html .= '<script type="text/javascript" src="'.$GLOBALS['SISED_URL_JSC'].'dragndrop/DragPane.js"></script>'."\n";
            $html .= "\n";
            $html .= '<script type="text/javascript" defer="defer">'."\n";
            $html .= '// <![CDATA['."\n";
            $html .= '// this script is deferred until the page is loaded.'."\n";
            $html .= "\n";

            $html .= 'function ordering_enableDrag(el) {'."\n";
            $html .= 'SortableListItem.getInstance(el);'."\n";
            $html .= '}'."\n";

            $html .= 'function ordering_hover(el) {'."\n";
            $html .= "\n";
            $html .= 'if(DragPane.hoveredElement != null) {'."\n";
            $html .= 'removeClass(DragPane.hoveredElement, "hoveredItem");'."\n";
            $html .= '}'."\n";
            $html .= 'if(!hasToken(el.className, "hoveredItem"))'."\n";
            $html .= 'el.className += " hoveredItem";'."\n";
            $html .= 'DragPane.hoveredElement = el;'."\n";
            $html .= "\n";
            $html .= '}'."\n";

            $html .= 'function ordering_hoverOff() {'."\n";
            $html .= 'if(DragPane.hoveredElement != null){'."\n";
            $html .= 'removeClass(DragPane.hoveredElement, "hoveredItem");'."\n";
            $html .= '}'."\n";
            $html .= 'DragPane.hoveredElement = null;'."\n";
            $html .= '}'."\n";

            $html .= 'function ordering_formSubmitted(form) {'."\n";
            $html .= 'var dragPane = DragPane.getInstance(document.getElementById(form.id.replace(/Form$/, "")));'."\n";
            $html .= "\n";
            $html .= 'var fields = dragPane.elements;'."\n";
            $html .= "\n";
            $html .= '//Show an alert box with the fields'."\n";
            $html .= 'var array = [fields.length]'."\n";
            $html .= 'for(var i = 0, len = fields.length; i < len; i++) {'."\n";
            $html .= 'var field = fields[i];'."\n";
            $html .= 'array[i] = field.name + " = " + field.value;'."\n";
            $html .= '}'."\n";
            $html .= "\n";
            $html .= '//cancel form submission'."\n";
            $html .= '//return false;'."\n";
            $html .= "\n";
            $html .= '//Don\'t cancel form submission (demo only)'."\n";
            $html .= 'return true;'."\n";
            $html .= '}'."\n";

            $html .= 'function ordering_formReset(form) {'."\n";
            $html .= 'history.go(0);'."\n";
            $html .= '}'."\n";

            $html .= 'function inserting_enableDrag(el) {'."\n";
            $html .= '//SortableListItem.getInstance(el);'."\n";
            $html .= '}'."\n";

						$html .= 'function set_reg_toadd (reg) {'."\n";
						$html .= 'document.getElementById( "reg_toadd" ).value = reg;'."\n";
						$html .= '}'."\n";
						
						$html .= 'function set_reg_todel (reg) {'."\n";
						$html .= 'document.getElementById( "reg_todel" ).value = reg;'."\n";
						$html .= '}'."\n";

            $html .= '// ]]>'."\n";
            $html .= '</script>'."\n";

            $html .= "\n";

            $html .= '<table width="100%" class="center-table">'."\n";
            
            // gestion des entêtes 
           $html .= '<caption>'."\n";
           
           $html .=  recherche_libelle_page('IdEntEdCh') ."\n";
           $html .= '</caption>'."\n";
           // Fin de la gestion des entêtes
                   
            $html .= '<tr>'."\n";
						
						$regs_chaine = $arbre->chaine ;
						$chaine_unused_typeregs = $arbre->get_chaine_unused_typeregs();

            $html .= '<td align="center">'."\n";

            $html .= '<br>'."\n";
            $html .= '<div id="ordering-view" class="dragPane" onmouseout="ordering_hoverOff()">'."\n";
						
						if( is_array($regs_chaine) and count($regs_chaine) ){
								foreach($regs_chaine as $t) {
		
										$html .= '<div id="regs_'.$t[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''].'" class="orderingItem" onmousedown="ordering_enableDrag(this);" onmouseover="ordering_hover(this)" onClick="set_reg_todel('.$t[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''].')">'."\n";
										$html .= '<span class="title">'.$t[''.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''].'</span>'."\n";
										$html .= '</div>'."\n";
								}
						}

            $html .= '</div>'."\n";
            $html .= '</td>'."\n";
						
						$html .= '<td align="center" valign="middle">'."\n";
						$html .= '<br>'."\n";
						
            if( is_array($chaine_unused_typeregs) and count($chaine_unused_typeregs) ){
								$html .= '<form name="frm_regs_toadd" method="post" action="'.$_SERVER['PHP_SELF'].'?val=atlas&type=edichaines&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">';
								$html .= '<input type="image" src="'.$GLOBALS['SISED_URL_IMG'].'chain_add_item.gif">'."\n";
								$html .= '<INPUT name="reg_toadd" id="reg_toadd" type="hidden">'."\n";
								$html .= '</form>';
						}
						if( is_array($regs_chaine) and count($regs_chaine) ){
								$html .= '<form name="frm_regs_todel" method="post" action="'.$_SERVER['PHP_SELF'].'?val=atlas&type=edichaines&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'."\n";
								$html .= '<input type="image" src="'.$GLOBALS['SISED_URL_IMG'].'chain_del_item.gif">'."\n";
								$html .= '<INPUT name="reg_todel" id="reg_todel" type="hidden">'."\n";
								$html .= '</form>';
						}
					
						$html .= '</td>'."\n";
						
            $html .= '<td align="center">'."\n";

            $html .= '<br>'."\n";
            $html .= '<div id="ordering-view" class="dragPane"  onmouseout="ordering_hoverOff()" align="right">'."\n";
						
						if( is_array($chaine_unused_typeregs) and count($chaine_unused_typeregs) ){
								foreach($chaine_unused_typeregs as $t) {
										$html .= '<div id="regs_unused_'.$t[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''].'" class="orderingItem" onmousedown="ordering_enableDrag(this);" onmouseover="ordering_hover(this)" onClick="set_reg_toadd('.$t[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''].')">'."\n";
										$html .= '<span class="title">'.$t[''.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''].'</span>'."\n";
										$html .= '</div>'."\n";
								}
						}

            $html .= '</div>'."\n";
            $html .= '</td>'."\n";

            $html .= '</tr>'."\n";

            $html .= '<tr>'."\n";
            $html .= '<td>'."\n";

            $html .= '<div id="view_rep">'."\n";
            $html .= '<form id="ordering-viewForm" method="post" onsubmit="return ordering_formSubmitted(this)" onreset="return ordering_formReset(this)">'."\n";
            $html .= "\n";
            $html .= '<span id="ordering-viewFields">'."\n";

            foreach($arbre->chaine as $i=>$t) {

                $html .= '<input type="hidden" name="regs_'.$t[''.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''].'" value="'.$i.'">'."\n";

            }

            $html .= '</span>'."\n";
            $html .= "\n";
            $html .= '<div align="center">'."\n";
            $html .= ' <input type="submit" value="'.recherche_libelle_page('save').'">'."\n";
            $html .= ' <input type="reset" value="'.recherche_libelle_page('reset').'">'."\n";
            $html .= '</div>'."\n";
            $html .= "\n";
            $html .= '</form>'."\n";
            $html .= '</div>'."\n";
            $html .= '</td>'."\n";
            $html .= '<td>'."\n";
            $html .= '&nbsp;'."\n";
            $html .= '</td>'."\n";
            $html .= '</tr>'."\n";
            $html .= '</table>'."\n";

        break;
        

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
                    $atlas = new atlas($_SESSION['secteur'], $_SESSION['chaine'], $_GET['typeregid'], $entete['code_regroup'],$conn);

                    configurer_barre_nav($atlas->nb_lignes); 

                    $atlas->get_donnees();
                    
                    // recherche des libellés des entêtes du template
                    $atlas->id_name        =   $atlas->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'regroupement');
                    $atlas->code_admin       =   $atlas->recherche_libelle_page('DescCodeAdmin',$_SESSION['langue'],'regroupement');
                    $atlas->lib_name       =   $atlas->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'regroupement');
                    $atlas->lib_ord		   =   $atlas->recherche_libelle_page('DescOrdReg',$_SESSION['langue'],'regroupement');
					$atlas->lib_ordre      =   $atlas->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'regroupement');
					$atlas->lib_entete     =   $atlas->recherche_libelle_page('IdEntete',$_SESSION['langue'],'regroupement');
                
                    $html .= '<table width="100%" style="border: none;">';                   
           
                    $html .= '<tr>';
                    $html .= '<td align="center" width="30%">';

                    if($curdepht > 0) {

                            $html .= str_replace('<select', '<select  style=\'width:100%;\'',$entete['html']);

                    }

                    $html .= '</td>';
                    $html .= '<td align="center" width="70%">';

                    $html .= $atlas->entete_template; 
                    $html .= $atlas->remplir_template($atlas->template);
                    $html .= $atlas->fin_template; 
                    
                    $plus_get = array('val', 'type', 'typeregid', 'chaine');
                    for( $i_combo = 0 ; $i_combo < $curdepht ; $i_combo++ ){
                        if(!isset($_GET['code_regroup_'.$i_combo ]) or trim($_GET['code_regroup_'.$i_combo ]=='')){
                            $curcodetyperegroup = $arbre->chaine[$i_combo][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                            $requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.';';
            
                            $regroupement = $atlas->conn->execute($requete);

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
                    $_SESSION['instance_regs']  =   $atlas;  

                } else {
                
                    if(isset($_SESSION['instance_regs'] )){
                
                        $atlas = $_SESSION['instance_regs'];
                
                    }      
                
                    $atlas->get_post_template($_POST);
                    $atlas->comparer($atlas->matrice_donnees_template,$atlas->donnees_post);                   
                    $atlas->maj_bdd($atlas->matrice_donnees_bdd);
                    unset($_SESSION['instance_regs']);
                    
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
					$atlas = new atlas($_SESSION['secteur'], $_SESSION['chaine'], $_GET['typeregid'], $entete['code_regroup'],$conn);
                    
                    configurer_barre_nav($atlas->nb_lignes); 

                    $atlas->get_donnees();
                    
                    // recherche des libellés des entêtes du template
                    $atlas->id_name        =   $atlas->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'regroupement');
                    $atlas->code_admin       =   $atlas->recherche_libelle_page('DescCodeAdmin',$_SESSION['langue'],'regroupement');
                    $atlas->lib_name       =   $atlas->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'regroupement');
					$atlas->lib_ord      =   $atlas->recherche_libelle_page('DescOrdReg',$_SESSION['langue'],'regroupement');
                    $atlas->lib_ordre      =   $atlas->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'regroupement');
                    $atlas->lib_entete      =   $atlas->recherche_libelle_page('IdEntete',$_SESSION['langue'],'regroupement');
                
                    $html .= '<table width="100%" style="border: none;">';                   
           
                    $html .= '<tr>';
                    $html .= '<td align="center" width="30%">';

                    if($curdepht > 0) {

                       $html .= str_replace('<select', '<select  style=\'width:100%;\'',$entete['html']);

                    }

                    $html .= '</td>';
                    $html .= '<td align="center" width="70%">';

                    $html .= $atlas->entete_template; 
                    $html .= $atlas->remplir_template($atlas->template);
                    $html .= $atlas->fin_template; 
                    //debut 07-12 Yacine
                    $plus_get = array('val', 'type', 'typeregid', 'chaine');
                    for( $i_combo = 0 ; $i_combo < $curdepht ; $i_combo++ ){
                        if(!isset($_GET['code_regroup_'.$i_combo ]) or trim($_GET['code_regroup_'.$i_combo ]=='')){
                            $curcodetyperegroup = $arbre->chaine[$i_combo][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                            $requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['CODE_ADMIN'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.';';
            
                            $regroupement = $atlas->conn->execute($requete);

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

$nummenu = count($typeregs);
if($nummenu <> 0){
	$tdwidth = floor(100 / $nummenu);
}

$menu_html = '';

$menu_html .= '<script type="text/Javascript">' . "\n";
$menu_html .= '<!--' . "\n";

$menu_html .= 'function toggle_atlas_sys(sys) {' . "\n";
$menu_html .= "\t".'location.href= \''.$_SERVER['PHP_SELF'].'?val=atlas&type='.$_GET['type'].'&secteur=\'+sys;' . "\n";
$menu_html .= '}' . "\n";

$menu_html .= 'function toggle_atlas_ch(sys, ch) {' . "\n";
$menu_html .= "\t".'location.href= \''.$_SERVER['PHP_SELF'].'?val=atlas&type='.$_GET['type'].'&chaine=\'+ch+\'&secteur=\'+sys;' . "\n";
$menu_html .= '}' . "\n";

$menu_html .= '-->' . "\n";
$menu_html .= '</script>' . "\n";

$menu_html .= '<table width="100%" style="border: none;">' . "\n";

$menu_html .= ' <tr>' . "\n";

$menu_html .= '<td colspan="'.$nummenu.'">' . "\n";
$menu_html .= '<table width="100%" style="border: none;">' . "\n"; 
$menu_html .= '<tr>' . "\n";

$menu_html .= '<td width="19%" style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?val=atlas&type=typeregs&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'.recherche_libelle_page('IdEntity') .'</a></td>' . "\n";
$menu_html .= '<td width="19%" style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?val=atlas&type=geschaines&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'.recherche_libelle_page('IdChaine').'</a></td>' . "\n";
$menu_html .= '<td width="19%" style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?val=atlas&type=edichaines&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'.recherche_libelle_page('IdEdition').'</a></td>' . "\n";
$menu_html .= '<td width="19%" style="text-align:center"><a href="'.$_SERVER['PHP_SELF'].'?val=atlas&type=regs&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'.recherche_libelle_page('IdDivision').'</a></td>' . "\n";

//$menu_html .= '<td width="20%" style="text-align:left"> systeme' . "\n";
//$menu_html .= '</td>'."\n";
$menu_html .= '<td width="24%" style="text-align:center">' . "\n";
$menu_html .= '<form name="options">' . "\n";


$menu_html .= '<select name="systeme" onchange="toggle_atlas_sys(this.value);" style="width:100%">' . "\n";

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

$menu_html .= '<select name="chaine" onchange="toggle_atlas_ch(systeme.value, this.value);" style="width:100%">' . "\n";

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

$menu_html .= '</select>' . "\n";

$menu_html .= '</form>' . "\n";

$menu_html .= '</td>' . "\n";
$menu_html .= '</tr>' . "\n";
$menu_html .= '</table>' . "\n";
$menu_html .= '</td>' . "\n";

$menu_html .= '</tr>' . "\n";

if(isset($_GET['type']) && preg_match('`^regs`', $_GET['type'])) {

    $menu_html .= '<tr>' . "\n";

    foreach($arbre->chaine as $t) {

        $menu_html .= '<td width="'.$tdwidth.'%" align="center" style="border: thin black solid;"><a href="'.$_SERVER['PHP_SELF'].'?val=atlas&type=regs&typeregid='.$t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].'&chaine='.$_GET['chaine'].'&secteur='.$_GET['secteur'].'">'.$t[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].'</a></td>' . "\n";

    }

    $menu_html .= '</tr>' . "\n";

}

$menu_html .= '</table>' . "\n";

?>
<br>
<br>
<table width="70%" class="center-table">
<caption><?php echo recherche_libelle_page('IdEntete') ?></caption>
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
