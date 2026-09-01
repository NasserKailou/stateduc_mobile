<?php lit_libelles_page('/recherche_etab.php');
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre2.class.php';

switch($_GET['type']) {

    case 'etablissement':

        $arbre = new arbre($_SESSION['chaine']);

        if(isset($_GET['nb_niveau_arbo'])) {

            $nb_niveau_arbo = $_GET['nb_niveau_arbo'];

        } else {

            $nb_niveau_arbo = 0;

        }

        $arbre->init($nb_niveau_arbo);

        $curpage = $_SERVER['PHP_SELF'];

        $gets = '';

        foreach($_GET as $i=>$g) {

            $catch = false;

            if($i != 'code_regroup' && $i != 'nom_regroup') {

                $catch = true;

                $gets .= $i . '=' . $g;

            }

            if($catch && $i<(count($_GET)-1)) {

                $gets .= '&';

            }

        }

        $arbre_html = $arbre->build($curpage.'?' . $gets . '&code_regroup=$code_regroup&nom_regroup=$nom_regroup');

        // Recherche des regroupements sélectionnés par click dans l'arbre
        $list_code_reg 	= array();
        $list_nom_reg 	= array();

        $liste_etab_html       ='';

        if ($_GET['nom_regroup'] && isset($_GET['code_regroup'])) {

            //création de la liste des coderegs enfants
            $list_code_reg = $arbre->getchildsid(substr($_GET['nom_regroup'],1),$_GET['code_regroup'],$_SESSION['chaine']);

            // On fabrique le "where" de la requête sur ETABLISSEMENT_REGROUPEMENT
            $where               = "(";
            $nb_reg              = count($list_code_reg);

            for ($i=0; $i<$nb_reg; $i++) {

                $where          .= 'E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$list_code_reg[$i][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];

                if ($i<$nb_reg-1) {

                    $where      .= " or ";

                }
            }

            $where              .= ")";

            $requete        = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].',
																		E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].'
                                    FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R  
                                    WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
                                    AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].'
                                    ORDER BY '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].'';

            $liste_etab = $conn->GetAll($requete);

            //création de la liste des coderegs parents
            $list_code_reg = $arbre->getparentsid(substr($_GET['nom_regroup'],1),$_GET['code_regroup'],$_SESSION['chaine']);

            $parent_reg = $list_code_reg[count($list_code_reg)-1][''.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''];

            $liste_etab_html .= "<B>".count($liste_etab).' '.recherche_libelle_page(EtabPour).' '.$parent_reg.":<BR><BR>"."</B>";

            $liste_etab_html .= '<blockquote>' . "\n";

            foreach ($liste_etab as $row) {

                $liste_etab_html .= '<a href="javascript:action_etab(\''.$row[''.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].''].'\');">'.$row[''.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].''].'</a><br />';

            }

            $liste_etab_html .= '</blockquote>' . "\n";

        }

?>
<style>

ol.menu img {cursor: pointer; width: 16px; height: 16px; margin-left: -20px; margin-bottom: -2px;}
ol.menu, ol.menu ol {text-align: left; list-style-type: none;}
ol.menu li {list-style-type: none; list-style-image: none; margin-left: -20px;}
ol.menu ol {display: none;}

</style>
<script type="text/Javascript">
<!--

function expand(n) {

    var node = n;

    while(node.nodeName != "OL") {

        node = node.nextSibling;

    }

    if(node.style.display == 'block') {

        node.style.display = 'none';
        n.src = '<?php echo $GLOBALS['SISED_URL_IMG']; ?>arbre/plus.gif';

    } else {

        node.style.display = 'block';
        n.src = '<?php echo $GLOBALS['SISED_URL_IMG']; ?>arbre/minus.gif';

    }

}

function action_etab(code_etab) {

    alert(code_etab);

}

-->
</script>

<table width="100%" height="100%">
<tr>
<td width="50%">
<?php echo $arbre_html;

?>
</td>
<td width="50%">
<?php echo $liste_etab_html;

?>
</td>
</tr>
</table>
<?php break;

    case 'atlas':

       // <bass>	
			 	if(isset($_GET['iLoc'])){
						$_SESSION['iLoc'] = $_GET['iLoc'] ;
				}
				if(!isset($_SESSION['iLoc'])){
						$_SESSION['iLoc'] = 0 ;
				}
				if(!isset($_GET['code_regroup_0']) && isset($_SESSION['rattachmts'])){
					$_SESSION['rattachements'] = $_SESSION['rattachmts'];
				}
				//echo "<pre>";
				//print_r($_SESSION['rattachements']);
				// </bass>
				//echo '<br>chaine='.$_GET['code_ch'];
				$arbre = new arbre($_GET['code_ch']);

        if(isset($_GET['nb_niveau_arbo'])) {

            $arbre->init($_GET['nb_niveau_arbo']);

        }        
        $GLOBALS['popup'] = 1 ;
        $arbre_html = $arbre->build('javascript:action_arbo(\'$code_regroup\', \'$nom_regroup\');');

?>
<style>

ol.menu img {cursor: pointer; width: 16px; height: 16px; margin-left: -20px; margin-bottom: -2px;}
ol.menu, ol.menu ol {text-align: left; list-style-type: none;}
ol.menu li {list-style-type: none; list-style-image: none; margin-left: -20px;}
ol.menu ol {display: none;}

</style>
<script type="text/Javascript">
<!--

function expand(n) {

    var node = n;

    while(node.nodeName != "OL") {

        node = node.nextSibling;

    }

    if(node.style.display == 'block') {

        node.style.display = 'none';
        n.src = '<?php echo $GLOBALS['SISED_URL_IMG']; ?>arbre/plus.gif';

    } else {

        node.style.display = 'block';
        n.src = '<?php echo $GLOBALS['SISED_URL_IMG']; ?>arbre/minus.gif';

    }

}

function action_arbo(code_regroup, nom_regroup) {

    alert(code_regroup);
    alert(nom_regroup);

}

-->
</script>
<BR /><BR /><br /><br />
<div align="center">
<form name="combo_regroups">

<table width="90%" align="center">
<tr>
<td width="40%"> 
		
		<?php echo recherche_libelle_page('Secteur');?> : <br />
		<B><?php echo get_libelle_from_array($_SESSION['tab_secteur'], $_SESSION['secteur'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE');?></B> 
		
</td>
<td  width="60%"> 
		<?php echo recherche_libelle_page('Chaine');?> : <br />
		<B><?php echo get_libelle_from_array($_SESSION['tab_chaines'], $_GET['code_ch'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']);?></B> 
     </td>
</tr>
</table>
<table style="border:none; width:100%; height:10px; background:none"><tr><td></td></tr></table>
<table width="90%" align="center">

<?php echo $arbre_html;

?>

</table>

<table style="border:none; width:100%; height:20px; background:none"><tr><td></td></tr></table>
<table width="90%" align="center" style=" background:none;">
<tr>
<td  width="100%" align="center">
<INPUT type="button" style="width:100%" value=" OK " onClick="transmettre_regroups();">
</td>
     
</tr>
</table>

</form></div><?php break;

}

?>
