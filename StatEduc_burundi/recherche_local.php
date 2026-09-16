<?php //$GLOBALS['PARAM']['']
		session_start();
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
print("function choisir_local(VAL_1){\n");
print("parent.document.form1.".$_GET['num_local'].".value=VAL_1;\n");	
//print("parent.document.form1.".$_GET['libelle_reg'].".value=VAL_2;\n");	
print("fermer() ;\n");	 
print("}\n");
print("</script>\n");

$plus_sql_filtr = '' ;
$filtre_locaux = array();
if(isset($_GET['codes_type_local']) && $_GET['codes_type_local']<>''){
	eval("\$filtre_locaux[]=array('chp' => '".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE']."_".$GLOBALS['PARAM']['LOCAL']."', 'val' => array(".$_GET['codes_type_local']."));");
}else{
	eval("\$filtre_locaux[]=array('chp' => '".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE']."_".$GLOBALS['PARAM']['LOCAL']."', 'val' => array('1'));");
}
//$filtre_locaux[] = array('chp' => 'CODE_TYPE_LOCAL', 'val' => array('1'));
if( is_array($filtre_locaux) && count($filtre_locaux)){
	foreach( $filtre_locaux as $i_filtr => $filtre ){
		if( exist_champ_in_table($filtre['chp'], $GLOBALS['PARAM']['LOCAL']) && (count($filtre['val'])) ){
			$plus_sql_filtr .= ' AND '. $filtre['chp'] . ' IN (' .implode(',',$filtre['val']). ')';
		}
	}
}
$req = 'SELECT '.$GLOBALS['PARAM']['NUMERO_LOCAL'].'
				FROM '.$GLOBALS['PARAM']['LOCAL'].'
				WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$_GET['code_etablissement'].'
				AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$_GET['code_annee']. $plus_sql_filtr .'
				ORDER BY '.$GLOBALS['PARAM']['NUMERO_LOCAL'].';';
$conn = $GLOBALS['conn'];
$rs	= $conn->Execute($req);


?>
<table width="90%" align="center" bgcolor="#000000" cellspacing="1" cellpading="3" border="1">
  <tr bgcolor="#cccccc" > 
    <td  height="22"> <div align="center" class="gras11">NUMERO_LOCAL</div></td>
    <td  align="center"><div align="center" class="gras11">CHOISIR</div></td>
  </tr>
   <tr bgcolor='#FFFFFF'> 
    <td  align='center' height="20">---</td>
     <?php //echo "<td  align='center'><a href=# onClick=javascript:fermer();>Choisir</a><br></td>";
	//echo "<td  align='center'><a href=# onClick=\"choisir_reg('1000','BASS');\">Choisir</a></td>";
	//echo "<td  align='center'><a href=# onClick=choisir_reg('$code_regroupement','$libelle_regroupement');>Choisir</a></td>";
	echo"<td  align='center'><a href=# onClick=\"choisir_local('');\">Choisir</a></td>";
	//<td  align='center'><a href=# onClick=choisir_reg('6','IA Kaolack');>Choisir</a></td>
	?>
  </tr>
  <?php while (!$rs->EOF){
  $numero_local		=	$rs->fields[$GLOBALS['PARAM']['NUMERO_LOCAL']];

?>
  <tr bgcolor='#FFFFFF'> 
    <td  align='center' height="20"> <?php echo "$numero_local"; ?></td>
     <?php //echo "<td  align='center'><a href=# onClick=javascript:fermer();>Choisir</a><br></td>";
	//echo "<td  align='center'><a href=# onClick=\"choisir_reg('1000','BASS');\">Choisir</a></td>";
	//echo "<td  align='center'><a href=# onClick=choisir_reg('$code_regroupement','$libelle_regroupement');>Choisir</a></td>";
	echo"<td  align='center'><a href=# onClick=\"choisir_local($numero_local);\">Choisir</a></td>";
	//<td  align='center'><a href=# onClick=choisir_reg('6','IA Kaolack');>Choisir</a></td>
	?>
  </tr>
<?php $rs->MoveNext();
}

?>

</TABLE>

