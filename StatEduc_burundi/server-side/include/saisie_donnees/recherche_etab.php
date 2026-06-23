<?php lit_libelles_page('/recherche_etab.php');
?>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS'] . $_SESSION['style']; ?>">

<?php print("<script language=\"javascript\" type=\"text/javascript\">\n");
print("function choisir_etab(VAL_1,VAL_2){\n");
print("parent.document.form1.".$_GET['codetab'].".value=VAL_1;\n");
print("parent.document.form1.".$_GET['nom_etab'].".value=VAL_2;\n");	
print("fermer() ;\n");	 
print("}\n");
print("</script>\n");



?>
<BR /><BR /><br /><br />
<?php require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre3.class.php';		
		
		//Modif Yacine
		if(isset($_GET['chaine_sector']) && $_GET['chaine_sector']<>''){
			$requete ='SELECT DICO_CHAINE_LOCALISATION.CODE_TYPE_CHAINE AS CODE_TYPE_CHAINE
						FROM DICO_CHAINE_LOCALISATION
						WHERE (((DICO_CHAINE_LOCALISATION.CODE_TYPE_SYSTEME_ENSEIGNEMENT)='.$_GET['etab_sector'].'))
						AND DICO_CHAINE_LOCALISATION.CODE_TYPE_CHAINE = '.$_GET['chaine_sector'].'
						ORDER BY DICO_CHAINE_LOCALISATION.ORDRE_TYPE_CHAINE';
		}else{
			$requete ='SELECT DICO_CHAINE_LOCALISATION.CODE_TYPE_CHAINE AS CODE_TYPE_CHAINE
                    FROM DICO_CHAINE_LOCALISATION
                    WHERE (((DICO_CHAINE_LOCALISATION.CODE_TYPE_SYSTEME_ENSEIGNEMENT)='.$_GET['etab_sector'].'))
                    ORDER BY DICO_CHAINE_LOCALISATION.ORDRE_TYPE_CHAINE';
		}				
		//echo 	$requete;	
		if ($GLOBALS['conn_dico']->Execute($requete) === false){
				echo '<br>error : '.$requete.'<br>';
		}else{
				$code_chaine_loc  = $GLOBALS['conn_dico']->GetAll($requete);
                $liste_chaine = '';
                foreach ($code_chaine_loc as $code_chaine){
                    if ( $liste_chaine == '')
                         $liste_chaine .=$code_chaine['CODE_TYPE_CHAINE'];
                    else
                        $liste_chaine .=','.$code_chaine['CODE_TYPE_CHAINE'];
                }
		}
        //fin modif yacine
		$requete        = '	SELECT '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'  AS CODE_TYPE_CH, '.
							$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
							AS LIBELLE_TYPE_CH
                        	FROM     '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
							WHERE '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' IN ('.$liste_chaine.')
							AND '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_GET['etab_sector'].' 
							ORDER BY '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'';
												
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
		
		if( ($_GET['option_reg'] == 'IN_REG') or ($_GET['option_reg'] == 'NOT_IN_REG') ){
		
			$req_regs_etab        = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG 
									 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'  
									 WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '. $_SESSION['code_etab'] ;
	
			$all_regs_etab	  	  = $GLOBALS['conn']->GetAll($req_regs_etab);
			$GLOBALS['regs_etab'] = array();
			//echo $req_regs_etab;
			if(is_array($all_regs_etab))
			foreach($all_regs_etab as $i => $reg){
				$GLOBALS['regs_etab'][] = $reg['CODE_REG'];
			}
		}
		
		$req_niv  	=  'SELECT    COUNT(*) 
						FROM      '.$GLOBALS['PARAM']['HIERARCHIE'].'
						WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['ch'];
					
		$GLOBALS['niv'] = 	$GLOBALS['conn']->getOne($req_niv);
	
	
		$arbre = new arbre($GLOBALS['ch']);
		$arbre->init($GLOBALS['niv']); 
		 
		$GLOBALS['popup'] =	1;
	  	$arbre_html = $arbre->build('javascript:action_arbo(\'$code_regroup\', \'$nom_regroup\');');
		
		$requete_etabs        = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS CODE_ETAB,  
								 E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS NOM_ETAB
								 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R  
								 WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
								 AND E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = '.$GLOBALS['last_reg'].'  
								 AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$_GET['etab_sector'].' 
								 ORDER BY E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].', E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];

        //echo  $requete_etabs .'<br>';
		// die( $GLOBALS['last_reg'] );
		$GLOBALS['liste_etabs'] = $GLOBALS['conn']->GetAll($requete_etabs);
		//print_r($GLOBALS['liste_etabs']);
		if(isset($_GET['code_etab_choisi']) && trim($_GET['code_etab_choisi']) <> ''){
			$GLOBALS['code_etab_choisi'] = $_GET['code_etab_choisi'] ;
		}else{
			$GLOBALS['code_etab_choisi'] = $GLOBALS['liste_etabs'][0]['CODE_ETAB'] ;
		}
		
		if(is_array($GLOBALS['liste_etabs']))
		foreach($GLOBALS['liste_etabs'] as $i_etab => $etab){
			if($GLOBALS['code_etab_choisi'] == $etab['CODE_ETAB']){	
				$GLOBALS['nom_etab_choisi'] = $etab['NOM_ETAB'] ;
				break;
			}
		}
		//echo'<pre>';
		//print_r($_GET);
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
		location.href= 'saisie_donnees.php?val=OpenPopupChoixEtab&codetab=<?php echo $_GET['codetab'];?>&nom_etab=<?php echo $_GET['nom_etab'];?>&etab_sector=<?php echo $_GET['etab_sector'];?>&option_reg=<?php echo $_GET['option_reg'];?>&ch='+ch;
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
<form name="combo_regroups" id="combo_regroups">
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
<table style="border:none; width:100%; height:10px; background:none"><tr><td></td></tr></table>
<table width="90%" align="center">
<tr>
<td width="40%"> 
	<?php echo recherche_libelle_page('etab_chose');?> : <br />
</td>
<td  width="60%">
		<select name="form_etab" onChange="get_params_etab(this.value);" style="width:100%">
					<?php if(is_array($GLOBALS['liste_etabs']))
					foreach ($GLOBALS['liste_etabs'] as $i => $etab){
							echo "<option value='".$etab['CODE_ETAB']."'";
							if ($etab['CODE_ETAB'] == $GLOBALS['code_etab_choisi']){
								echo " selected";
							}
							echo ">".$etab['NOM_ETAB']."</option>";
					}
	?>
    </select> 
	<input type="hidden" id="form_code_etab" name="form_code_etab" value="<?php echo $GLOBALS['code_etab_choisi'];?>" /> 
	<input type="hidden" id="form_nom_etab" name="form_nom_etab" value="<?php echo $GLOBALS['nom_etab_choisi'];?>" />   
     </td>
</tr>
</table>
<table style="border:none; width:100%; height:20px; background:none"><tr><td></td></tr></table>
<table width="90%" align="center" style=" background:none;">
<tr>
<td  width="100%" align="center">
<INPUT type="button" style="width:100%" value=" OK " onClick="choisir_etab(form_code_etab.value, form_nom_etab.value);">
</td>
     
</tr>
</table>

<script type="text/Javascript">
<!--
	function get_params_etab(code_etab){
		<?php echo  $GLOBALS['href_chg_etab'].'+\'&code_etab_choisi=\'+code_etab;';?>
	}
-->
</script>
<?php echo '</form></div>'; 
?>


