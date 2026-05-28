<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>multifile.js"></script>
<?php lit_libelles_page('/gestion_olap_import_cube.php');
set_time_limit(0);
ini_set('memory_limit',113246208);
$etat_import = "<table border='1' align='center' width='300'>\n";
if($_FILES['file']) {	
	echo "<br /><br />";	
	$rep = "server-side/include/olap_tools/cubes/"; 	
	$fichier = $_FILES['file']; // simplication du tableau $_FILES
	//debut_popup_progress();
	for($i=0; $i<count($fichier['name']); $i++) {
	
		if(!empty($fichier["name"][$i]))
		{
			
			//nom du fichier choisi:
			$nomFichier    = $fichier["name"][$i];//$_FILES["fichier1"]["name"] ;
			//nom temporaire sur le serveur:
			$nomTemporaire = $fichier["tmp_name"][$i] ;
			//type du fichier choisi:
			$typeFichier   = $fichier["type"][$i] ;
			//poids en octets du fichier choisit:
			$poidsFichier  = $fichier["size"][$i] ;
			//code de l'erreur si jamais il y en a une:
			$codeErreur    = $fichier["error"][$i] ;
			
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
					$etat_import .= "<tr><td> $nomFichier : Upload OK </td></tr>\n";
											
					/*$req_max_id_olap='SELECT MAX(ID_OLAP) AS MAX_ID FROM DICO_OLAP';
					$max_id_olap=$GLOBALS['conn_dico']->GetOne($req_max_id_olap);
					$max_id_olap++;
					$nom_theme=substr($nomFichier,0,strlen($nomFichier)-4);
					$req_insert='INSERT INTO DICO_OLAP (ID_OLAP,THEME_NAME,ASSOCIATE_OLAP_FILE) VALUES ('.$max_id_olap.', \''.$nom_theme.'\', \''.$nomFichier.'\')';
					$GLOBALS['conn_dico']->Execute($req_insert);
					
					echo '<script type="text/Javascript">
							parent.document.location.href=\'olap.php?val=gest_olap_inst\';
							fermer();
						  </script>';	*/				
				}
				else
					$etat_import .= "<tr><td>$nomFichier : Upload not OK </td></tr>\n";
			}
			else
				$etat_import .= "<tr><td>$nomFichier : ".recherche_libelle_page('TypeFichIncorrect')."</td></tr>\n";		
		}		
	}
	//fin_popup_progress();		
}
$etat_import .= "</table>\n";
echo $etat_import;
	
?>
<br />
<br />
<!-- This is the form -->
<form action="<?php echo $PHP_SELF; ?>" method="post" enctype="multipart/form-data">
	<table border="1" align="center" width="600">
		<caption><b><?php echo recherche_libelle_page('LibImportCube'); ?></b></caption>
		<tr> 
		  <td align="center">
		  <input id="my_file_element" type="file" name="file[]" size="60">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		  <INPUT name="Submit" type="submit" value="<?php echo recherche_libelle_page('Importer'); ?>">
		  </td>
		</tr>
	</table>
</form>

<table border="1" align="center" width="600">
	<tr> 
	  	<td>
		<?php echo recherche_libelle_page('Fichier'); ?>:
		<!-- This is where the output will appear -->
		<div id="files_list"></div>
		</td>
	</tr>		
</table>
<script language="javascript" type="text/javascript">
	<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
	var multi_selector = new MultiSelector( document.getElementById( 'files_list' ));
	<!-- Pass in the file element -->
	multi_selector.addElement( document.getElementById( 'my_file_element' ) );
</script>

