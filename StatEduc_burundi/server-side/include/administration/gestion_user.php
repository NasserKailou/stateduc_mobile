<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php 

include $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     

$GLOBALS['conn'] = $GLOBALS['conn_dico'] ;
$importResult = array();
$listUserFileName = "";
if (isset($_POST["import"])) {

    // PhpSpreadsheet chargé uniquement lors d'un import (évite erreur fatale si lib absente)
    require_once ($GLOBALS['SISED_PATH_LIB'].'autoload.php');

    $allowedFileType = [
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    if (in_array($_FILES["file"]["type"], $allowedFileType)) {

		$listUserFileName = $_FILES['file']['name'];
        $targetPath = $GLOBALS['SISED_PATH']."server-side/import_export/" . $listUserFileName;
        move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadSheet = $Reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();
        $sheetCount = count($spreadSheetAry);
		
		if (isset($_SESSION['instance_nomenc'] )){
			$user   =   $_SESSION['instance_nomenc'];   
			// R�cup�ration de la valeur du post
    		$user->get_excel_data($spreadSheetAry, $sheetCount);  
			
			$importResult = $user->maj_bdd_excel($targetPath);  
			$type = "ok"; 
		} 
		
    } else {
        $type = "error";
        $message = "Invalid File Type. Upload Excel File.";
    }
} else if (count($_POST)>0)  {
    // Il s'agit du traitement des donnees du POST
    
    if (isset($_SESSION['instance_nomenc'] )){
        $user   =   $_SESSION['instance_nomenc'];        
    }      
   
    // R�cup�ration de la valeur du post
    $user->get_post_template($_POST);

    // compraison
    $user->comparer($user->matrice_donnees_template,$user->donnees_post);
    
    // maj dans la base de donn�es  
		 
   $user->maj_bdd($user->matrice_donnees_bdd);
   unset($_SESSION['instance_nomenc']);   
    
}
   
	////////////  Modif Alassane
	if(isset($_GET['app']) && $_GET['app'] == 'mob'){
		$id_groupe   = -1;
		$_GET['id_groupe'] = $id_groupe;
	}elseif(isset($_GET['id_groupe'])){
		$id_groupe  =$_GET['id_groupe'];
	}elseif(isset($_SESSION['groupe'])){
		$id_groupe   = $_SESSION['groupe']; 
	}else{
		$id_groupe   =1; 
	} 
	$_SESSION['id_groupe'] = $id_groupe; 
	
	$lib_nom_table='ADMIN_USERS';
	$type_traitement='user';  
	
	$user = new user($id_groupe, $lib_nom_table,$type_traitement,$_SESSION['langue'],$conn); 
	 
	$user->champ_id      = 'CODE_USER';
	$user->champ_lib     = 'NOM_USER';
	$user->champ_ordre   = 'PASSWORD';
	$user->champ_name_user    = 'NOM_LONG_USER';
	$user->champ_email_user   = 'EMAIL_USER';
	$user->champ_tel_user   = 'TEL_USER';
	$user->champ_systeme = 'CODE_GROUPE';      
	$user->champ_user_parent = 'CODE_USER_PARENT'; 
	
	if (isset($_POST['filtrer']) || isset($_GET['debut'])) {
		$user->get_post_filtre($_POST);
	}     
	
	// Configuration de la barre de navigation
	configurer_barre_nav($user->nb_lignes);
	$html .='<br /> <br />';
	$html     .="<span class=''>";
	
	// Inteface import utilisateurs
	$html .= '<div class="inner-box"><div class="inner-box-title">'.$user->recherche_libelle_page('ImportUserTitle',$_SESSION['langue'],'user').'</div>';
	$html .= '<div class="inner-box-container">';
	$html .= '<form action="" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">';
	$html .= '<div>';
	$html .= ' <label>Choisir un fichier Excel (.xlsx)</label> <input type="file" name="file" id="file" accept=".xls,.xlsx">';
	$html .= ' <button type="submit" id="submit" name="import" class="btn-submit">Importer</button>';
	$html .= '</div>';
	$html .= '</form>';	   
	// Bouton téléchargement du canevas Excel (session 68)
	$html .= '<div style="margin-top:8px;"><a class="btn btn-default" href="'.$GLOBALS["SISED_URL"].'administration.php?val=download_user_template" style="text-decoration:none;">&#11015; Télécharger le canevas Excel (12 colonnes)</a></div>';
	$html .= '</div>';
	
	$html .= '<div id="response" class="';
	if(!empty($type)) { $html .= $type . ' display-block"'; } 
	$html .= '">'; 
	if(!empty($message)) { $html .= $message; } 
	else if(count($importResult)>0) { 
		$html .= '<a class="btn btn-info" href="'.$GLOBALS["SISED_URL"].'administration.php?val=download_file&fichier=_log_'.basename($listUserFileName,".xlsx").'.log">'.$user->recherche_libelle_page('downloadLog',$_SESSION['langue'],'user').'</a>';
		$html .= '<table width="50%" class="center-table">';
		$html .= '<caption>';
		$html .=  $user->recherche_libelle_page('ResultImport',$_SESSION['langue'],'user');
		$html .= '</caption>';
		$html .= '<thead><tr class="ligne-titre"><th>Num</th><th>Nom utilisateur</th><th>Email</th><th>Tel</th><th>Login</th><th>Groupe</th><th>Code étab.</th><th>Camp.</th><th>Période</th><th>Message</th></tr></thead>';
		foreach ($importResult as $key=>$tab) {
			$classLigne = ($key % 2 === 1)?'ligne-paire-left':'ligne-impaire-left';
			// $tab[7]=code_etab, $tab[8]=id_camp, $tab[12]=id_periode, $tab[13]=message (session 69)
			$html .= '<tr class="'.$classLigne.'"><td>'.($key+1).'</td><td>'.$tab[1].'</td><td>'.$tab[2].'</td><td>'.$tab[3].'</td><td>'.$tab[4].'</td><td>'.$tab[6].'</td><td>'.$tab[7].'</td><td>'.$tab[8].'</td><td>'.$tab[12].'</td><td>'.$tab[13].'</td></tr>';
		}
		$html .= '</table>';
	}
	$html .= '</div></div>';
	// Fin Inteface import utilisateurs
	
	$html .= '<table width="50%" class="center-table">';
	
	// gestion des ent�tes des menus
	$html .= '<caption>';
	$html .=  $user->recherche_libelle_page('IdEntete',$_SESSION['langue'],'user');
	$html .= '</caption>';
	// Fin de la gestion des ent�tes
	   
	$html .= '<tr>';
	$html .= '<td align="center">';
	$html .= '</td>';
	$html .= '<td align="center">';          
	
	$user->get_donnees();       
	
	$html .= $user->entete_template; 
  
   	// recherche des libell�s des ent�tes du template
	$user->id_name        =   $user->recherche_libelle_page('DescIdNom',$_SESSION['langue'],'user');
	$user->lib_name       =   'login';
	$user->lib_name_long  =   $user->recherche_libelle_page('DescLibNom',$_SESSION['langue'],'user');
	$user->lib_email      =   'E-mail';
	$user->lib_tel      	=   'Tel';
	$user->lib_ordre      =   $user->recherche_libelle_page('DescOrdNom',$_SESSION['langue'],'user');      
	
	
	$html .= $user->remplir_template($user->template);
	$html .= $user->fin_template; 
	$html .= afficher_barre_nav(true,true, array('val', 'type', 'gestionuser','id_groupe'));
	$html .= '<br />';       
	
	//passage de l'objet en session
	$_SESSION['instance_nomenc']  =   $user;   
	
	$html .= '</td>';
	$html .= '</tr>';
	$html .= '</table>'; 
	$html.='</span><br />';
	echo $html;   
   	////////// Fin modif Alassane
    
?>
