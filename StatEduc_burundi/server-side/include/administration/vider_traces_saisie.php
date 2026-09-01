<?php
lit_libelles_page(__FILE__);

if(isset($_POST['annee_trace'])){
	$req_del_trace = "DELETE FROM DICO_TRACE WHERE CODE_ANNEE = ".$_POST['annee_trace'];
	if($GLOBALS['conn_dico']->Execute($req_del_trace)===false){
		echo'<br>Error deleting : '.$req_del_trace.'<br>';
	}
}

$req_ann_trace = "SELECT DISTINCT DICO_TRACE.CODE_ANNEE FROM DICO_TRACE;";
$res_ann_trace = $GLOBALS['conn_dico']->GetAll($req_ann_trace);
$tab_ann_trace = array();
if(is_array($res_ann_trace)){
	foreach($res_ann_trace as $i_ann => $ann_trace){
		$tab_ann_trace[] = $ann_trace['CODE_ANNEE'];
	}
}
$codes_ann_trace = '('.implode(', ',$tab_ann_trace).')';
$requete = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' IN '.$codes_ann_trace.' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
$list_ann = $GLOBALS['conn']->GetAll($requete);
//krsort($list_ann);

?>
<form  name="vider_traces_form" id="vider_traces_form" method="post">
<br/>
<br/>
<table class="center-table" id="table_vider_traces"  width="350px">
  <caption><?php echo recherche_libelle_page('ViderTraces'); ?></caption>
<tr>
 	<td nowrap="nowrap"><br/>&nbsp;<?php echo recherche_libelle_page('yeardata');?>
		<select name="annee_trace">
			<?php 
			if(is_array($list_ann)){
				foreach ($list_ann as $i_ann => $ann){
						echo "<option value='".$ann[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]."'";
						echo ">".$ann[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]."</option>";
				}
			}
			?>
		</select>
	</td>
</tr>
<tr>
  <td colspan="2" nowrap="nowrap">
	<br/>
	  <div align="center">
		<input name="ok" type="button" value="<?php echo recherche_libelle_page('submit');?>" onclick="javascript:document.vider_traces_form.submit();"/>
	  </div>
  </td>
 </tr>
</table>
</form>
<?php $xdbtype=$GLOBALS['conn_dico']->databaseType;
if($xdbtype == "access"){
	if (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb')){
		$pathDataBaseSource = $GLOBALS['SISED_PATH'].'server-side/dico_DB.mdb';
		$pathDataBaseDestination = $GLOBALS['SISED_PATH'].'server-side/db_temp.mdb';
	}elseif(file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.accdb')){
		$pathDataBaseSource = $GLOBALS['SISED_PATH'].'server-side/dico_DB.accdb';
		$pathDataBaseDestination = $GLOBALS['SISED_PATH'].'server-side/db_temp.accdb';
	}
}/*else{
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo 'alert("Type Base Dico Access Exigé")'."\n";
	echo '</script>'."\n";
}*/
?>
<script language="vbscript">
//Définition de la base à compacter
uidBaseSource = "Admin"
pwdBaseSource = ""
pathDataBaseSource = "<?php echo $pathDataBaseSource;?>"

//Définition de la base compactée temporaire
uidBaseDestination = "Admin"
pwdBaseDestination = ""
pathDataBaseDestination = "<?php echo $pathDataBaseDestination;?>"

//Définition des valeurs du compactage
strProvider = "Provider=Microsoft.Jet.OLEDB.4.0;"
strEngine = "Jet OLEDB:Engine Type=5;"
strEncrypt = "Jet OLEDB:Encrypt Database=False;" 

strUidBaseSource = "User ID=" + uidBaseSource + ";"
strPwdBaseSource = "Password=" + pwdBaseSource + ";"

strUidBaseDestination = "User ID=" + uidBaseDestination + ";"
strPwdBaseDestination = "Password=" + pwdBaseDestination + ";"

strDataBaseSource = "Data Source=" + pathDataBaseSource + ";"
strCompactDataBaseSource = strProvider + strDataBaseSource

strDataBaseDestination = "Data Source=" + pathDataBaseDestination + ";"
strCompactDataBaseDestination = strProvider + strEngine + strEncrypt + strDataBaseDestination

//Création d'un objet FileSystemObject
Set ObjFileSystem = CreateObject("Scripting.FileSystemObject")

//Vérification de l'existence de la base à compacter
If (ObjFileSystem.FileExists(pathDataBaseSource)) Then

   //Vérifie que la base temporaire n'existe pas
   If (ObjFileSystem.FileExists(pathDataBaseDestination)) Then
      //Si elle existe la base temporaire est effacée
      ObjFileSystem.DeleteFile pathDataBaseDestination
   End If

   //Création de l'objet JetEngine
   Set ObjEngine = CreateObject("JRO.JetEngine")
   //Compactage de la base de données
   ObjEngine.CompactDatabase strCompactDataBaseSource, strCompactDataBaseDestination
   //Destruction de l'objet JetEngine
   
   Set ObjEngine = Nothing

   //Remplacement de l'ancienne base par la base compactée temporaire
   ObjFileSystem.CopyFile pathDataBaseDestination,pathDataBaseSource ,True
   //Effacement de la base compactée temporaire
   ObjFileSystem.DeleteFile pathDataBaseDestination

End If

//Destruction de l'objet FileSystemObject
Set ObjFileSystem = Nothing
//MsgBox "Compactage OK "
</script>