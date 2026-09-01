<?php
  
  
  set_time_limit(0);
  ini_set("memory_limit", "2048M");
  
  $fichier = "";
  $chemin = "";
  if (isset($_GET['typefic']) && ($_GET['typefic'] == 'im')) {
  	$chemin = "server-side\\import_export\\import\\";
  }	else {
  	$chemin = "server-side\\import_export\\";
  }
  $nom_fichier = urldecode($_GET['fichier']);
  $fichier = $chemin.$nom_fichier;
  $type = ""; echo $fichier;
  
  // Suivant l'extention du fichier, on détermine le type de téléchargement pour lequel il faut opter
  // Pour autoriser le téléchargement de nouveaux types de fichier (par extention), il suffit de
  // décommenter les lignes ci-dessous
  switch(strrchr(basename($fichier), ".")) {
    //case ".gz": $type = "application/x-gzip"; break;
    //case ".tgz": $type = "application/x-gzip"; break;
    case ".zip": $type = "application/zip"; break;
    case ".mdb": $type = "application/msaccess"; break;
    case ".log": $type = "text/plain"; break;
    //case ".pdf": $type = "application/pdf"; break;
    //case ".png": $type = "image/png"; break;
    //case ".gif": $type = "image/gif"; break;
    //case ".jpg": $type = "image/jpeg"; break;
    //case ".txt": $type = "text/plain"; break;
    //case ".htm": $type = "text/html"; break;
    //case ".html": $type = "text/html"; break;

    // Pour les autres types (ceux que l'on ne veut pas autoriser en téléchargement, on affiche un message d'avertissement)
    // Sinon, ce script pourrait être utilisé pour télécharger les sources des pages PHP, par exemple, ou un fichier .htaccess
    default:
      
      exit;
      break;
  }
  
  // On démarre le téléchargement du fichier
  if (!file_exists($fichier) && $type == "application/zip" && file_exists($chemin.basename($nom_fichier,".zip").".xls")) { 
  		$fichier = createZip(dirname($fichier)."\\", basename ($nom_fichier,".zip"), ".xls");
  } else if (file_exists($fichier) && $type == "text/plain" && !file_exists($chemin, basename ($nom_fichier,".log").".zip")) { 
  		$fichier = createZip(dirname($fichier)."\\", basename ($nom_fichier,".log"), ".log");
  }   
  if (file_exists($fichier)) { 
  	if ($type == "application/msaccess") {
  		$fichier = createZip(dirname($fichier)."\\", basename ($nom_fichier,".mdb"), ".mdb");
  	} else if (filesize($fichier) == 0) {
  		$fichier = createZip(dirname($fichier)."\\", basename ($nom_fichier,".zip"), ".log");
  	}
  	$nomfichier=basename($fichier);
  	header("Content-disposition: attachment; filename=$nomfichier");
  	header("Content-Type: application/force-download");
  	header("Content-Transfer-Encoding: application/zip\n"); // Surtout ne pas enlever le \n
  	header("Content-Length: ".filesize($fichier));
  	header("Pragma: no-cache");
  	header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
  	header("Expires: 0");
  	readfile($fichier);
	  
  } else {
	  print "<FONT COLOR=red><CENTER>";
      print "  File not found : '$nom_fichier' !!!";
	  print "</CENTER></FONT>";
  }
  
  
  
  function createZip($chemin, $nom_fichier, $type_fic) {
  	require_once($GLOBALS['SISED_PATH_LIB'].'pclzip.lib.php');
	$fichier_zip = "";
	if(!empty($nom_fichier)){
		$fichier_zip = preg_replace("/[[:blank:]|[:space:]]/", '_', $nom_fichier);
		$fichier_zip = (str_replace('.zip','',$fichier_zip)).'.zip';
	}else{
		$fichier_zip = 'import_export.zip';
	}
	
	$fich_compl_zip = $chemin . $fichier_zip ;
	if(file_exists($fich_compl_zip)){
		unlink($fich_compl_zip);
	}
	$zip = new PclZip($fich_compl_zip);
	$list_zip = array();
	$list_zip[] = $chemin . $nom_fichier . $type_fic; 
	$res = $zip->create( $list_zip , PCLZIP_OPT_REMOVE_PATH, $chemin, PCLZIP_OPT_ADD_TEMP_FILE_ON);
	if ($res == 0) {
		if (startsWith($zip->errorInfo(true), 'PCLZIP_ERR_INVALID_PARAMETER')) {
			$res = $zip->create( $list_zip , PCLZIP_OPT_REMOVE_PATH, $chemin);
		} 		
		if ($res == 0) {
			echo "Error : ".$zip->errorInfo(true); die();
		}
	}
	return $fich_compl_zip;
  }
?>
