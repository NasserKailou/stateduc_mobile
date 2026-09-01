<?php session_start();
		require_once 'config_app.php';
		require_once 'params.php';
		require_once 'params_sys.php';
		require_once $GLOBALS['SISED_PATH_LIB'] . 'connexion.inc.php';
		require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
?>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS'] . $_SESSION['style']; ?>">
<br>
<br>
<br>
<br>
<?php print("<script language=\"javascript\" type=\"text/javascript\">\n");
print("function fermer() {\n");
print("window.parent.jQuery('#div_dialog').dialog('close');\n");
print("}\n");
print("function choisir_ens(VAL_1,VAL_2){\n");
print("parent.document.form1.".$_GET['num_ens'].".value=VAL_1;\n");	
print("parent.document.form1.".$_GET['vue_ens'].".value=VAL_2;\n");	
print("fermer() ;\n");	 
print("}\n");
print("</script>\n");

$critere_fct = '';
if(isset($_GET['champ_filtre_add']) && $_GET['champ_filtre_add']<>'' && isset($_GET['vals_filtre_add']) && $_GET['vals_filtre_add']<>''){
	$ColTab	=	$GLOBALS['conn']->MetaColumnNames($GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT']);  
	$champs=array_keys($ColTab);
	if(in_array($_GET['champ_filtre_add'],$champs)){
		$critere_fct = ' AND '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$_GET['champ_filtre_add'].' IN ('.$_GET['vals_filtre_add'].')';
	}else{
		$ColTab	=	$GLOBALS['conn']->MetaColumnNames($GLOBALS['PARAM']['ENSEIGNANT']);  
		$champs=array_keys($ColTab);
		if(in_array($_GET['champ_filtre_add'],$champs)){
			$critere_fct = ' AND '.$GLOBALS['PARAM']['ENSEIGNANT'].'.'.$_GET['champ_filtre_add'].' IN ('.$_GET['vals_filtre_add'].')';
		}
	}
}

$req = 'SELECT '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NUMERO_ORDRE_ENSEIGNANT'].', 
		'.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].', 
        '.$GLOBALS['PARAM']['ENSEIGNANT'].'.'.$GLOBALS['PARAM']['PRENOMS_ENSEIGNANT'].', 
		'.$GLOBALS['PARAM']['ENSEIGNANT'].'.'.$GLOBALS['PARAM']['NOM_ENSEIGNANT'].'
		FROM  '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ENSEIGNANT'].' 
		WHERE '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].' 
		= '.$GLOBALS['PARAM']['ENSEIGNANT'].'.'.$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT'].'
		AND '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='.$_GET['code_etablissement']. 
		' AND '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_GET['code_annee'].$critere_fct.
		' ORDER BY '.$GLOBALS['PARAM']['ENSEIGNANT_ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NUMERO_ORDRE_ENSEIGNANT'];

$conn = $GLOBALS['conn'];
//echo $req ;

$rs	= $conn->Execute($req);


?>
<table width="90%" align="center" bgcolor="#000000" cellspacing="1" cellpading="3" border="1">
  <tr bgcolor="#cccccc" > 
  <td  height="22"> <div align="center" class="gras11">N° ORDRE</div></td>
    <td  height="22"> <div align="center" class="gras11">IDENTIFIANT ENSEIGNANT</div></td>
	<td  height="22"> <div align="center" class="gras11">PRENOMS & NOMS</div></td>
    <td  align="center"><div align="center" class="gras11">CHOISIR</div></td>
  </tr>
  <tr bgcolor='#FFFFFF'> 
  	<td  align='center' height="20">---</td>
    <td  align='center' height="20">---</td>
	<td  align='center' height="20">---</td>
	
     <?php //$prenoms_ens = addslashes($prenoms_ens);
	 //$nom_ens = addslashes($nom_ens);
	//echo "<td  align='center'><a href=# onClick=javascript:fermer();>Choisir</a><br></td>";
	//echo "<td  align='center'><a href=# onClick=\"choisir_reg('1000','BASS');\">Choisir</a></td>";
	//echo "<td  align='center'><a href=# onClick=choisir_reg('$code_regroupement','$libelle_regroupement');>Choisir</a></td>";
	echo"<td  align='center'><a href=# onClick=\"choisir_ens('','');\">Choisir</a></td>";
	//<td  align='center'><a href=# onClick=choisir_reg('6','IA Kaolack');>Choisir</a></td>
	?>
  </tr>
  <?php while (!$rs->EOF){
  $num_ordre		=	$rs->fields[$GLOBALS['PARAM']['NUMERO_ORDRE_ENSEIGNANT']];
  $num_ens			=	$rs->fields[$GLOBALS['PARAM']['IDENTIFIANT_ENSEIGNANT']];
  $prenoms_ens		=	$rs->fields[$GLOBALS['PARAM']['PRENOMS_ENSEIGNANT']];
  $nom_ens			=	$rs->fields[$GLOBALS['PARAM']['NOM_ENSEIGNANT']];
  if( trim($GLOBALS['PARAM']['PRENOMS_ENSEIGNANT']) <> trim($GLOBALS['PARAM']['NOM_ENSEIGNANT'])){
  		$prenom_nom_ens = $prenoms_ens .' '. $nom_ens ;
  }else{
  		$prenom_nom_ens = $prenoms_ens  ;
  }
  

?>
  <tr bgcolor='#FFFFFF'> 
  	<td  align='center' height="20"> <?php echo "$num_ordre"; ?></td>
    <td  align='center' height="20"> <?php echo "$num_ens"; ?></td>
	<td  align='center' height="20"> <?php echo $prenom_nom_ens; ?></td>
	
     <?php //$prenoms_ens = addslashes($prenoms_ens);
	 //$nom_ens = addslashes($nom_ens);
	//echo "<td  align='center'><a href=# onClick=javascript:fermer();>Choisir</a><br></td>";
	//echo "<td  align='center'><a href=# onClick=\"choisir_reg('1000','BASS');\">Choisir</a></td>";
	//echo "<td  align='center'><a href=# onClick=choisir_reg('$code_regroupement','$libelle_regroupement');>Choisir</a></td>";
	echo"<td  align='center'><a href=# onClick=\"choisir_ens('$num_ens','$num_ordre');\">Choisir</a></td>";
	//<td  align='center'><a href=# onClick=choisir_reg('6','IA Kaolack');>Choisir</a></td>
	?>
  </tr>
<?php $rs->MoveNext();
}

?>

</TABLE>

