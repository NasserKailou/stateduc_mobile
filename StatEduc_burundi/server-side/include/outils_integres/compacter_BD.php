<?php $xdbtype=$GLOBALS['conn']->databaseType;
if($xdbtype == access){
	$lines = file($GLOBALS['SISED_PATH'] . 'connexion.php');
	foreach($lines as $line) {
		if(preg_match('`^#`', $line)) {
			$line = preg_replace('`^#(.*)\r?\n$`', '\\1', $line);
			$info = explode(';', $line);
			if(count($info) == 7) {
				$pathDataBaseSource = $info[3];
				if (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb')){
					$pathDataBaseDestination = $GLOBALS['SISED_PATH_DB'].'db_temp.mdb';
				}elseif(file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.accdb'){
					$pathDataBaseDestination = $GLOBALS['SISED_PATH_DB'].'db_temp.accdb';
				}
				break;
			}
		}
	}
				
}else{
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo 'alert("This work only for Access Database")'."\n";
	echo 'fermer()'."\n";
	echo '</script>'."\n";
}
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
Set WshShell = CreateObject("WScript.Shell")
WshShell.SendKeys( "%{F4}" )
//MsgBox "Compactage OK "
</script>

