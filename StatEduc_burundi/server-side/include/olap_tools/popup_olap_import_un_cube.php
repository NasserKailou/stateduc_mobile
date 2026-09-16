<?php lit_libelles_page('/popup_olap_import_un_cube.php');
set_time_limit(0);
ini_set('memory_limit',113246208);	
if($_FILES['file']) {	
	echo "<br /><br /><br />";	
	$rep = "server-side/include/olap_tools/cubes/"; 	
	$fichier = $_FILES['file']; // simplication du tableau $_FILES
	
	if(!empty($fichier["name"]))
	{
		
		//nom du fichier choisi:
		$nomFichier    = $fichier["name"];//$_FILES["fichier1"]["name"] ;
		//nom temporaire sur le serveur:
		$nomTemporaire = $fichier["tmp_name"] ;
		//type du fichier choisi:
		$typeFichier   = $fichier["type"] ;
		//poids en octets du fichier choisit:
		$poidsFichier  = $fichier["size"];
		//code de l'erreur si jamais il y en a une:
		$codeErreur    = $fichier["error"];
		
		
		if(eregi("(\.cub)",$nomFichier)) //Extraction nom de fichier
		{
			//chemin qui mène au dossier qui va contenir les fichiers upload:
			$chemin = $rep ;
			if (!is_dir($chemin))
			{
				mkdir($chemin, 0777);
			}
			$saveFile=$chemin.$nomFichier;
			if(copy($nomTemporaire, $saveFile))
			{
				echo "<center> $nomFichier : Upload OK </center>";
										
				echo '<script type="text/Javascript">
					parent.document.Formulaire.ASSOCIATE_OLAP_FILE.value=\''.$nomFichier.'\';
					fermer();
				  </script>';				
			}
			else
				echo "<center> $nomFichier : Upload not OK </center>";
		}
		else
			echo "<center> $nomFichier : ".recherche_libelle_page('TypeFichIncorrect')." </center>";		
	}		
}
?>
<br />
<br />
<br />
<!-- This is the form -->
<form action="<?php echo $PHP_SELF; ?>" method="post" enctype="multipart/form-data">
	<table border="1" align="center" width="400">
		<caption><b><?php echo recherche_libelle_page('LibImportUnCub'); ?></b></caption>
		<tr> 
		  <td align="center">
		  <input id="my_file_element" type="file" name="file" size="40">
		  &nbsp;&nbsp;&nbsp;
		  <INPUT name="Submit" type="submit" value="<?php echo recherche_libelle_page('Importer'); ?>">
		  </td>
		</tr>
	</table>
</form>
<center><input type="button" <?php echo 'value="'.recherche_libelle_page('fermer').'"';?> onClick="javascript:fermer();"></center>
