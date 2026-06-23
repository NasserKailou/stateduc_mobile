<?php //$cfg_nb_pages    = 3; // Nombre de n° de pages affichés dans la barre

function configurer_barre_nav($nb_lignes)
{
	if (!isset($_GET['debut'])) $GLOBALS['debut'] = 0;
	else $GLOBALS['debut'] = $_GET['debut'];
	
	$GLOBALS['cfg_nbres_ppage'] = $nb_lignes;
	$GLOBALS['cfg_nb_pages'] = 10;
}

function afficher_barre_nav($affiche,$ajouter,$getparams=array()) {

    $gets = '';

    if(count($getparams)>0 && count($_GET)>0) {

        foreach($getparams as $i=>$g) {

            if(isset($_GET[$g])) {

                $gets .= $g . '=' . $_GET[$g];
    
                if($i<(count($getparams)-1)) {
    
                    $gets .= '&';
    
                }

            }

        }

    }

    if($affiche==true) {

        if (($GLOBALS['nbre_total_enr']) >= ($GLOBALS['cfg_nbres_ppage'])) {

            $barre_nav = barre_navigation($GLOBALS['nbre_total_enr'], $GLOBALS['nbenr'], $GLOBALS['cfg_nbres_ppage'], $GLOBALS['debut'], $GLOBALS['cfg_nb_pages'], $_SERVER['PHP_SELF']."?suite=true&".$gets);
        }
    }

    if($ajouter==true) {

        $ajouter = "<a href=".$PHP_SELF."?debut=".($GLOBALS['nbre_total_enr'])."&".$gets.'>'.get_libelle_page('Add',$_SESSION['langue'],'navigation.inc.php').' >></a>' ;

    }

    if(isset($barre_nav)){

        return "<div align='center'><br>
			<table width='50%' border=0 align='center'><tr>
			<td align='left' width=75%>&nbsp;".$barre_nav."&nbsp;</td>
				<td align='right' width=25%>&nbsp".$ajouter."&nbsp;</td>
			</tr>
			</table> </div>";
	}
}
function afficher_barre_nav_tmis($affiche,$ajouter,$getparams=array()) {
	//echo "<pre>";
	//print_r($_GET);
    //
	$gets = '';
	$i_count_gets = -1 ;
	$nb_exclu = 1;
	foreach($_GET as $i_get=>$g) {
		$i_count_gets++ ;
		$catch = false;
		if(!preg_match('`^suite`', $i_get) && !preg_match('`^debut`', $i_get) && !preg_match('`^ret_list`', $i_get) && !preg_match('`^line`', $i_get) && !preg_match('`^nb_line`', $i_get) && !preg_match('`^annee`', $i_get)) {
			$catch = true;
			$gets .= $i_get . '=' . $g;
		}
		if(preg_match('`^suite`', $i_get)){
			$nb_exclu = 2;	
		}
		if($catch && $i_count_gets<(count($_GET)-$nb_exclu)) {
			$gets .= '&';
		}
	}
	//
	//echo "<br>$gets";
    if($affiche==true) {

        if (($GLOBALS['nbre_total_enr']) >= ($GLOBALS['cfg_nbres_ppage'])) {

            $barre_nav = barre_navigation($GLOBALS['nbre_total_enr'], $GLOBALS['nbenr'], $GLOBALS['cfg_nbres_ppage'], $GLOBALS['debut'], $GLOBALS['cfg_nb_pages'], $_SERVER['PHP_SELF']."?suite=true&".$gets);
        }
    }

    if($ajouter==true) {

        $ajouter = "<a href=".$PHP_SELF."?debut=".($GLOBALS['nbre_total_enr'])."&".$gets.'>'.get_libelle_page('Add',$_SESSION['langue'],'navigation.inc.php').' >></a>' ;

    }

    if(isset($barre_nav)){

        return "<div align='center'><br>
			<table width='50%' border=0 align='center'><tr>
			<td align='left' width=75%>&nbsp;".$barre_nav."&nbsp;</td>
				<td align='right' width=25%>&nbsp".$ajouter."&nbsp;</td>
			</tr>
			</table> </div>";
	}
}

function barre_navigation($nbtotal,
                          $nbenr, 
                          $cfg_nbres_ppage, 
                          $debut, 
						  $cfg_nb_pages,
                          $criteres)
{
    // --------------------------------------------------------------------
       global $cfg_nb_pages; // Nb de n° de pages affichées dans la barre

       $lien_on         = '&nbsp;<A HREF="{cible}">{lien}</A>&nbsp;';
       $lien_off        = '&nbsp;{lien}&nbsp;';

    // --------------------------------------------------------------------
    
    $query  = $criteres."&debut=";

     // Précédente ( 10 enr precedentes ) (0->99 ; 100->199 ; 200-299)
    // --------------------------------------------------------------------
		$debut_1ere_page = ((int)($debut/($cfg_nb_pages * $cfg_nbres_ppage) ))*$cfg_nb_pages * $cfg_nbres_ppage ;
		//echo "<br>deb1=".$debut_1ere_page;
		$precedente_debut_1ere_page = $debut_1ere_page - $cfg_nb_pages * $cfg_nbres_ppage ;
		//echo "<br>deb2=".$prochaine_debut_1ere_page;
    if ( $precedente_debut_1ere_page >= 0 )
    {
        $cible = $query.($precedente_debut_1ere_page);
        //$image = image_html('../images/droite_on.gif');
				$image = get_libelle_page('Prev',$_SESSION['langue'],'navigation.inc.php');
        $lien = str_replace('{lien}', $image, $lien_on);
        $lien = str_replace('{cible}', $cible, $lien);
    }
    else
    {
        //$image = image_html('../images/droite_off.gif');
				$image = get_libelle_page('Prev',$_SESSION['langue'],'navigation.inc.php');
        $lien = str_replace('{lien}', $image, $lien_off);
    }
    //$barre .= "&nbsp;<B>&middot;</B>".$lien;
		
   $barre .= $lien."&nbsp;";

   
    if ($debut >= ($cfg_nb_pages * $cfg_nbres_ppage))
    {
       // echo "cfg_nbres_ppage=$cfg_nbres_ppage";
				//$cpt_fin = ($debut / $cfg_nbres_ppage) + 1;
       // $cpt_deb = $cpt_fin - $cfg_nb_pages + 1;
			 	$cpt_deb = ((int)($debut/($cfg_nb_pages * $cfg_nbres_ppage) ))* $cfg_nb_pages +1;
	  		$cpt_fin = $cpt_deb  + $cfg_nb_pages - 1;
				if( ($cpt_fin * $cfg_nbres_ppage) > $nbtotal)
						$cpt_fin = (int)($nbtotal / $cfg_nbres_ppage);
				if (($nbtotal % $cfg_nbres_ppage) != 0) $cpt_fin++;
    }
    else
    {
        $cpt_deb = 1;
        
        $cpt_fin = (int)($nbtotal / $cfg_nbres_ppage);
        if (($nbtotal % $cfg_nbres_ppage) != 0) $cpt_fin++;
        
        if ($cpt_fin > $cfg_nb_pages) $cpt_fin = $cfg_nb_pages;
    }

    for ($cpt = $cpt_deb; $cpt <= $cpt_fin; $cpt++)
    {
        if ($cpt == ($debut / $cfg_nbres_ppage) + 1)
        {
            $barre .= "<B>".$cpt."</B>&nbsp;&nbsp;";
        }
        else
        {
            $barre .= "<A HREF='".$query.(($cpt-1)*$cfg_nbres_ppage);
            $barre .= "'>".$cpt."</A>&nbsp;&nbsp;";
        }
    }
    
    // suivante ( 10 enr suivantes ) (0->99 ; 100->199 ; 200-299)
    // --------------------------------------------------------------------
		$debut_1ere_page = ((int)($debut/($cfg_nb_pages * $cfg_nbres_ppage) ))*$cfg_nb_pages * $cfg_nbres_ppage ;
		//echo "<br>deb1=".$debut_1ere_page;
		$prochaine_debut_1ere_page = $debut_1ere_page + $cfg_nb_pages * $cfg_nbres_ppage ;
		//echo "<br>deb2=".$prochaine_debut_1ere_page;
    if ( $prochaine_debut_1ere_page < $nbtotal )
    {
        $cible = $query.($prochaine_debut_1ere_page);
        //$image = image_html('../images/droite_on.gif');
				$image = get_libelle_page('Next',$_SESSION['langue'],'navigation.inc.php');
        $lien = str_replace('{lien}', $image, $lien_on);
        $lien = str_replace('{cible}', $cible, $lien);
    }
    else
    {
        //$image = image_html('../images/droite_off.gif');
				$image = get_libelle_page('Next',$_SESSION['langue'],'navigation.inc.php');
        $lien = str_replace('{lien}', $image, $lien_off);
    }
    //$barre .= "&nbsp;<B>&middot;</B>".$lien;
		$barre .= "&nbsp;".$lien;
		
    return($barre);
} 



?>