<script language="JavaScript" type="text/javascript">
		function recharger(nom_page) {
						location.href   = '?val=gest_lib_trad&nom_page='+nom_page;
		}
</script>
<?php $requete = "SELECT  DISTINCT  NOM_PAGE	FROM  DICO_LIBELLE_PAGE";
        $l_nom_page = $GLOBALS['conn_dico']->GetAll($requete);

?>

<table align="right">
	<tr> 
		<td><?php echo recherche_libelle_page('RechLibelle'); ?></td>
		<td><INPUT type="text" size="30" id="LIBELLE" name="LIBELLE" value="<?php if(isset($_GET['libelle'])) echo $_GET['libelle']; else echo '';?>"/></td>
		<td><input id="searchButton" type="button" value=" ... " width="21" height="22" border="0" onclick="OpenPopupRechLibelle()"/></td>
	</tr>
</table>
<br>
<br>
<br>
<table align="center">
	<tr> 
        <td><?php echo recherche_libelle_page('l_nom_pg'); ?></td>
        <td><select name="nom_page"	onChange="recharger(this.value);">
												<?php foreach ($l_nom_page as $i => $page){
									echo "<option value='".$page['NOM_PAGE']."'";
									if ($page['NOM_PAGE'] == $_GET['nom_page']){
											echo " selected";
									}
									echo ">".$page['NOM_PAGE']."</option>";
							}
					?>
						</select></td>
    </tr>
	<tr><td colspan="2">&nbsp;</td></tr>
    <tr> 
        <td><?php echo recherche_libelle_page('code_lib'); ?></td>
        <td><INPUT type="text" size="10" name="CODE_LIBELLE" value="<?php echo $val['CODE_LIBELLE']; ?>"></td>
    </tr>
    <tr> 
        <td height="26"><?php echo recherche_libelle_page('nom_pg'); ?></td>
        <td><INPUT type="text" size="35" name="NOM_PAGE" value="<?php echo $val['NOM_PAGE']; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('langue'); ?></td>
        <td><INPUT type="text" readonly="1" size="1" name="CODE_LANGUE" value="<?php echo $val['CODE_LANGUE']; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('libelle'); ?></td>
        <td><INPUT type="text" size="50" name="LIBELLE" value="<?php echo $val['LIBELLE']; ?>"></td>
    </tr>
</table>
<br>
<?php if(isset($_GET['nom_page']) && isset($_GET['id_libelle']) && isset($_GET['libelle']) && $_SESSION['choix_libelle']){
	$_SESSION['choix_libelle']=0;
	$requete =" SELECT   DICO_LIBELLE_PAGE.CODE_LIBELLE, DICO_LIBELLE_PAGE.NOM_PAGE, DICO_LIBELLE_PAGE.CODE_LANGUE, DICO_LIBELLE_PAGE.LIBELLE
				FROM     DICO_LIBELLE_PAGE 
				WHERE 	 DICO_LIBELLE_PAGE.CODE_LANGUE ='".$_SESSION['langue']."'
				AND DICO_LIBELLE_PAGE.CODE_LIBELLE = '".$_GET['id_libelle']."'
				AND DICO_LIBELLE_PAGE.NOM_PAGE = '".$_GET['nom_page']."'";
	//echo $requete ;			
	$choix = $GLOBALS['conn_dico']->GetAll($requete);
	$ch=array();
	foreach($choix as $val){
		$ch['CODE_LIBELLE']=$val['CODE_LIBELLE'];
		$ch['NOM_PAGE']=$val['NOM_PAGE'];
		$ch['CODE_LANGUE']=$val['CODE_LANGUE'];
		$ch['LIBELLE']=$val['LIBELLE'];
	}
	$enr=0;
	foreach ($this->donnees as $i_enr => $enr){
		if (count(array_diff_assoc($enr, $ch))==0){
			$enr=$i_enr;
			break;
		}
	}
	$select_code="";
	$select_code .= "<script language='javascript' type='text/javascript'>\n";
	$select_code .= "load_action(".$enr.",'Open');\n";
	$select_code .= "</script>\n";
	echo $select_code;
}
?>
