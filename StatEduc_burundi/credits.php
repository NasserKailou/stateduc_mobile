<?php include 'common.php';

?>
<html>
<head>
<title>Cr&eacute;dits</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<link rel="stylesheet" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>defaut.css" type="text/css">
</head>

<body bgcolor="#FFFFFF">
<br>
<br>
<br>
<?php //lit_libelles_page('/credits.php');
function lit_credits() {        
        lit_libelles_page('/credits.php');
        
        // Lecture des contribution principales
        if (isset($_SESSION['langue']) && $_SESSION['langue']<>''){
            $requete = ' SELECT CONTENU, ADRESSE FROM  DICO_CREDITS '.
                       ' WHERE   (TYPE_CONTENU ='.'\''.'contribution'.'\''.') AND (CODE_LANGUE ='.'\''.$_SESSION['langue'].'\' )'.
                       ' ORDER BY ORDRE_AFFICHAGE ';
        } else{
            $requete = ' SELECT CONTENU, ADRESSE FROM  DICO_CREDITS '.
                       ' WHERE   (TYPE_CONTENU ='.'\''.'contribution'.'\''.') AND (CODE_LANGUE ='.'\''.'fr'.'\''.' )'.
                       ' ORDER BY ORDRE_AFFICHAGE ';        
        }
        
        $rscontribution = $GLOBALS['conn_dico']->GetAll($requete );
        $html  ='';
        $html .='<table border="1" width="400" align="center">';
        $html .='<CAPTION>';
        //$html .='<B>'. recherche_libelle_page(ContFin).'</B>';
        $html .='</CAPTION>';        
        $html .='<tr><td>'; 
       
        foreach ($rscontribution as $contribution){
            if (isset($contribution['ADRESSE']) && isset($contribution['ADRESSE'])<>''){
                $html .= $contribution['CONTENU'].'     <a href="'.$contribution['ADRESSE'].'" target="_blank">('.$contribution['ADRESSE'].')</a>      ';      
            }else{
                $html .= $contribution['CONTENU'].'  ';                  
            }    
        }
         $html .='</td></tr>'; 
        //$html .='</table>'; 
        $html .='<br>';
        
         // Lecture des appuis principaux
        if (isset($_SESSION['langue']) && $_SESSION['langue']<>''){
            $requete = ' SELECT CONTENU, ADRESSE FROM  DICO_CREDITS '.
                       ' WHERE   (TYPE_CONTENU ='.'\''.'appui'.'\''.') AND (CODE_LANGUE ='.'\''.$_SESSION['langue'].'\' )'.
                       ' ORDER BY ORDRE_AFFICHAGE ';
        } else{
            $requete = ' SELECT CONTENU, ADRESSE FROM  DICO_CREDITS '.
                       ' WHERE   (TYPE_CONTENU ='.'\''.'appui'.'\''.') AND (CODE_LANGUE ='.'\''.'fr'.'\''.' )'.
                       ' ORDER BY ORDRE_AFFICHAGE ';        
        }
        
        $rscontribution = $GLOBALS['conn_dico']->GetAll($requete );
        
        /*$html .='<table border="1" width="400" align="center">';
        $html .='<CAPTION>';
        $html .='<B>'. recherche_libelle_page(ContFin).'</B>';
        $html .='</CAPTION>';  */      
        $html .='<tr><td>'; 
       
        foreach ($rscontribution as $contribution){
            $html .= $contribution['CONTENU'].'     <a href="'.$contribution['ADRESSE'].'" target="_blank">('.$contribution['ADRESSE'].')</a>      ';           
        }
         $html .='</td></tr>'; 
        $html .='</table>'; 
        $html .='<br>';
        
        // Lecture de la direcion scientifique
        
        if (isset($_SESSION['langue']) && $_SESSION['langue']<>''){
            $requete = ' SELECT CONTENU, FONCTION, ADRESSE FROM  DICO_CREDITS '.
                       ' WHERE   (TYPE_CONTENU ='.'\''.'direction'.'\''.') AND (CODE_LANGUE ='.'\''.$_SESSION['langue'].'\' )'.
                       ' ORDER BY ORDRE_AFFICHAGE ';
        } else{
            $requete = ' SELECT CONTENU, FONCTION, ADRESSE FROM  DICO_CREDITS '.
                       ' WHERE   (TYPE_CONTENU ='.'\''.'direction'.'\''.') AND (CODE_LANGUE ='.'\''.'fr'.'\''.' )'.
                       ' ORDER BY ORDRE_AFFICHAGE ';        
        }
        
        $rsdirection = $GLOBALS['conn_dico']->GetAll($requete );       
        $html .= '<div align="center">';
        //$html .= '<b>'.recherche_libelle_page(Contrib).'</b>';
        $html .='</div>';
        $html .='<table border="1" width="400" align="center">';
        $html .='<CAPTION>';
        $html .='<B>'. recherche_libelle_page(ContFin).'</B>';
        $html .='</CAPTION>';        
        $html .='<tr>'; 
        $html .=    '<td><div align="center"><strong>'.'<b>'.recherche_libelle_page(NomsPre).'</b>'.'</strong></div></td>';
        $html .=    '<td><div align="center"><strong>'.'<b>'.recherche_libelle_page(ProfFonc).'</b>'.'</strong></div></td>';
        //$html .=    '<td><div align="center"><strong>'.'<b>'.recherche_libelle_page(ServAff).'</b>'.'</strong></div></td>';
        $html .=    '<td><div align="center"><strong>'.'<b>'.recherche_libelle_page(Contacts).'</b>'.'</strong></div></td>';
        $html .= '</tr>';
        
        
        $i=0;
        foreach ($rsdirection  as $direction){
            
            if (($i % 2) == 0) {
                $html .= '<tr class="ligne-paire"> ';
            }else {
                $html .= '<tr class="ligne-impaire"> ';
            }            
            $html .= '<td>'.$direction['CONTENU'].'</td>';
            $html .= '<td>'.$direction['FONCTION'].'</td>';
            $html .='<td><a href="mailto:'.$direction['ADRESSE'].'">'.$direction['ADRESSE'].'</a></td>';           
            $html .='</tr>';
            $i++;            
        }        
        
       $html .='</table>';
       $html .='<br>';
       
        if (isset($_SESSION['langue']) && $_SESSION['langue']<>''){
            $requete = ' SELECT CONTENU, FONCTION, ADRESSE FROM  DICO_CREDITS '.
                       ' WHERE   (TYPE_CONTENU ='.'\''.'codeur'.'\''.') AND (CODE_LANGUE ='.'\''.$_SESSION['langue'].'\' )'.
                       ' ORDER BY ORDRE_AFFICHAGE ';
        } else{
            $requete = ' SELECT CONTENU, FONCTION, ADRESSE FROM  DICO_CREDITS '.
                       ' WHERE   (TYPE_CONTENU ='.'\''.'codeur'.'\''.') AND (CODE_LANGUE ='.'\''.'fr'.'\''.' )'.
                       ' ORDER BY ORDRE_AFFICHAGE ';        
        }
                   
        $rscodeur = $GLOBALS['conn_dico']->GetAll($requete );
        
        $html .= '<div align="center">';
        //$html .='<b>'.recherche_libelle_page(ContTech).'</b>';
        $html .='</div>';
        $html .='<table border="1" width="400" align="center">';
        $html .='<CAPTION>';
        $html .='<B>'.recherche_libelle_page(ContTech).'</B>';
        $html .='</CAPTION>';        
        $html .='<tr>'; 
        $html .=    '<td><div align="center"><strong>'.'<b>'.recherche_libelle_page(NomsPre).'</b>'.'</strong></div></td>';
        $html .=    '<td><div align="center"><strong>'.'<b>'.recherche_libelle_page(ProfFonc).'</b>'.'</strong></div></td>';
        //$html .=    '<td><div align="center"><strong>'.'<b>'.recherche_libelle_page(ServAff).'</b>'; .'</strong></div></td>';
        $html .=    '<td><div align="center"><strong>'.'<b>'.recherche_libelle_page(Contacts).'</b>'.'</strong></div></td>';
        $html .= '</tr>';
        
        $i=0;
        foreach ($rscodeur as $codeur){
            if (($i % 2) == 0) {
                $html .= '<tr class="ligne-paire"> ';
            }else {
                $html .= '<tr class="ligne-impaire"> ';
            }
            $html .= '<td>'.$codeur['CONTENU'].'</td>';
            $html .= '<td>'.$codeur['FONCTION'].'</td>';
            $html .='<td><a href="mailto:'.$codeur['ADRESSE'].'">'.$codeur['ADRESSE'].'</a></td>';           
            $html .='</tr>';
            $i++;            
        }        
        
       $html .='</table>';
       return $html;
}

 echo lit_credits(); ?>

</body>
</html>

