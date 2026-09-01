<script language="JavaScript" type="text/javascript">
    function OpenPopupRefreshCubes(nom_fic) {
        var	popup	=	window.open(nom_fic,'popGenAll', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=350, height=50, left=200, top=150')
        popup.document.close();
        popup.focus();
    }
</script>

<?php 
	
	if($_GET['val'] == 'refresh_cubes_server' || $_GET['val'] == 'refresh_cubes_server2'){
        $nom_fichier = 'olap.php?val=pop_refresh_cubes_menu';
        echo '<script language="JavaScript" type="text/javascript">';
        echo 'OpenPopupRefreshCubes(\''.$nom_fichier.'\')';
        echo '</script>';
    }else{
         $sql ='SELECT * FROM DICO_OLAP_AUTRE_CONNEXION WHERE SGBD='.'\'OlapServer\'';
         $result =  $GLOBALS['conn_dico']->GetAll($sql);   
         if (is_array($result) && count($result)>0){
             $strAnalysisServer    =   $result[0]['SERVEUR']; 
             if (isset($result[0]['DB']) && $result[0]['DB']<>'')
                $strOlapDB  =   $result[0]['DB'];
		}
		if($strAnalysisServer <> "" && $strOlapDB <> ""){
			debut_popup_progress();
			echo "\n";  
			echo '<SCRIPT LANGUAGE="VBSCRIPT">'."\n";
			echo "Dim dsoServer\n";
			echo "Dim dsoDB\n";
			echo "Dim dsoDim\n";
			echo "Dim WshShell, oExec \n";
		
			echo "Set dsoServer = CreateObject(\"DSO.Server\")\n";
			echo "Set dsoDB = CreateObject(\"DSO.MDStore\")\n";
			//Set dsoDim = CreateObject("DSO.Dimension")
			//Set dsoCube = CreateObject("DSO.Cube")
		
			//Connect to Analysis server.
			echo "strAnalysisServer = \"$strAnalysisServer\"\n";
			echo "dsoServer.Connect strAnalysisServer\n";
		
			//Open olap database.
			echo "strOlapDB = \"$strOlapDB\"\n";
			echo "Set dsoDB = dsoServer.MDStores(strOlapDB)\n";
	  
			//Open cube.
			//Set dsoCube = dsoDB.Cubes("EFFECTIFS")
		
			//Completely reprocess the olap db.
			echo "dsoDB.Process processFull\n";
			//Clean up.
			//Set dsoDim = Nothing
			//Set dsoCube = Nothing
			echo "Set dsoDB = Nothing\n";
			echo "dsoServer.CloseServer\n";
			echo "Set dsoServer = Nothing\n";
			echo "MsgBox \"OLAP Database Full Process Sucessful\"\n";
			//echo "MsgBox \"Rafraichissement des cubes serveur terminé avec succes\"\n";
			
			echo "Set WshShell = CreateObject(\"WScript.Shell\")\n";
			echo "WshShell.SendKeys( \"%{F4}\" )\n";
			echo "\n";
			echo "</SCRIPT>\n";
		}else{
			//echo "<b>Fill in server and olap base name from Admin/Config -> OLAP-Cube Managemt -> OLAP-Server Connection menu</b>";
			echo "<b>Bien vouloir renseigner le nom du serveur et de la base olap a partir du menu Admin/Config -> Gestion Cubes OLAP -> OLAP-Connexion Serveur</b>";
		}
    }
?>
