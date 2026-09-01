<?php lit_libelles_page('/recherche_etab.php');
		require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre2.class.php';		
		
		/*requete        = '	SELECT DICO_CHAINE_LOCALISATION.CODE_TYPE_CHAINE  AS CODE_TYPE_CHAINE, '.
							$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
							AS LIBELLE_TYPE_CHAINE
                        	FROM      DICO_CHAINE_LOCALISATION, '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
							WHERE DICO_CHAINE_LOCALISATION.CODE_TYPE_CHAINE = '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
							AND DICO_CHAINE_LOCALISATION.CODE_TYPE_SYSTEME_ENSEIGNEMENT='.$_GET['etab_sector'].' 
							ORDER BY DICO_CHAINE_LOCALISATION.ORDRE_TYPE_CHAINE';*/
		if(isset($_GET['chaine_sector']) && $_GET['chaine_sector']<>''){
			$requete ='SELECT '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' AS CODE_TYPE_CH,
					 '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' AS LIBELLE_TYPE_CH 
					FROM  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
					WHERE '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_GET['etab_sector'].' AND '.
							$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$_GET['chaine_sector'].'
					ORDER BY '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'';
			//echo $requete;
		}else{
			$requete ='SELECT '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' AS CODE_TYPE_CH,
					 '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' AS LIBELLE_TYPE_CH 
					FROM  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
					WHERE '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_GET['etab_sector'].'
					ORDER BY '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'';
		}
		if ($GLOBALS['conn']->Execute($requete) === false){
				echo '<br>error : '.$requete.'<br>';
		}else{
				$GLOBALS['all_chaines_loc']  = $GLOBALS['conn']->GetAll($requete);
		}
		
		if( isset($_GET['ch']) && (trim($_GET['ch']) <> '') ){
			$GLOBALS['ch'] = $_GET['ch'] ;
		}else{
			$GLOBALS['ch'] = $GLOBALS['all_chaines_loc'][0]['CODE_TYPE_CH'] ;
		}
		
		$req_niv  	=  'SELECT    COUNT(*) 
						FROM      '.$GLOBALS['PARAM']['HIERARCHIE'].'
						WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['ch'];
					
		$GLOBALS['niv'] = 	$GLOBALS['conn']->getOne($req_niv);
	
	
		$arbre = new arbre($GLOBALS['ch']);
		$arbre->init($GLOBALS['niv']); 
		$GLOBALS['popup'] =	1;
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
function reload_page(ch) {
	location.href= 'saisie_donnees.php?val=OpenPopupChoixReg&code_reg=<?php echo $_GET['code_reg'];?>&libelle_reg=<?php echo $_GET['libelle_reg'];?>&etab_sector=<?php echo $_GET['etab_sector'];?>&ch='+ch;
}
	
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
		<B><?php echo get_libelle_from_array($_SESSION['tab_secteur'], $_GET['etab_sector'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE');?></B> 
		
</td>
<td  width="60%"> 
		<?php echo recherche_libelle_page('Chaine');?> <br />
		<select name="id_chaine" onChange="reload_page(this.value);" style="width:100%">
					<?php if(is_array($GLOBALS['all_chaines_loc']))
					foreach ($GLOBALS['all_chaines_loc'] as $i => $chaine_systeme){
							echo "<option value='".$chaine_systeme['CODE_TYPE_CH']."'";
							if ($chaine_systeme['CODE_TYPE_CH'] == $GLOBALS['ch']){
								echo " selected";
							}
							echo ">".$chaine_systeme['LIBELLE_TYPE_CH']."</option>";
					}
	?>
				</select> 
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

</form></div>



