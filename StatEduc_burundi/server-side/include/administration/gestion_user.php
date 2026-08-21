<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php 

include $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     

$GLOBALS['conn'] = $GLOBALS['conn_dico'] ;
$importResult = array();
$listUserFileName = "";

// ── fix AK-PHP-02 : Migration des utilisateurs vers la nouvelle année ─────────
$migrate_result  = null;
$migrate_message = '';
if (isset($_POST['ak_migrate_annee'])) {
    if (isset($_SESSION['instance_nomenc'])) {
        $user_mig = $_SESSION['instance_nomenc'];
    } else {
        $lib_nom_table_mig = 'ADMIN_USERS';
        $user_mig = new user(isset($_GET['id_groupe']) ? (int)$_GET['id_groupe'] : 4, $lib_nom_table_mig, 'user', $_SESSION['langue'], $GLOBALS['conn_dico']);
    }
    $old_annee   = intval($_POST['ak_old_annee']);
    $new_annee   = intval($_POST['ak_new_annee']);
    $new_camp    = intval($_POST['ak_new_camp']);
    $new_periode = intval($_POST['ak_new_periode']);
    $id_grp_fil  = isset($_POST['ak_id_groupe']) ? intval($_POST['ak_id_groupe']) : 0;
    if ($old_annee > 0 && $new_annee > 0 && $new_camp > 0) {
        $migrate_result = $user_mig->migrer_utilisateurs_annee($old_annee, $new_annee, $new_camp, $new_periode, $id_grp_fil);
    } else {
        $migrate_message = '<span class="error">Veuillez renseigner Année source, Nouvelle année et Nouvelle campagne.</span>';
    }
}
// ── fin fix AK-PHP-02 ────────────────────────────────────────────────────────

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
	// Fin Interface import utilisateurs

	// ── fix AK-PHP-02 : Bouton migration utilisateurs vers nouvelle année ─────
	// Récupérer les années et campagnes disponibles pour les combos
	$tab_annees_dispo = $GLOBALS['conn_dico']->GetAll(
		'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' AS code_annee,'
		.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' AS lib_annee'
		.' FROM '.$GLOBALS['PARAM']['TYPE_ANNEE']
		.' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' DESC'
	);
	$tab_camps_dispo = $GLOBALS['conn_dico']->GetAll(
		'SELECT DISTINCT ID_CAMPAGNE, ID_ANNEE FROM DICO_FIXE_REGROUPEMENT ORDER BY ID_CAMPAGNE DESC'
	);

	$html .= '<div class="inner-box" style="margin-top:20px;">';
	$html .= '<div class="inner-box-title" style="background:#2980b9;color:#fff;">&#9654; Fixer les agents mobiles sur une nouvelle ann&eacute;e de collecte</div>';
	$html .= '<div class="inner-box-container">';
	$html .= '<p style="color:#555;margin-bottom:10px;">Cette action copie les liaisons &eacute;cole/agent depuis une ann&eacute;e source vers la nouvelle ann&eacute;e configur&eacute;e, sans supprimer les donn&eacute;es existantes. Les agents d&eacute;j&agrave; pr&eacute;sents dans la nouvelle ann&eacute;e sont ignor&eacute;s (pas de doublon).</p>';

	// Résultat migration si disponible
	if ($migrate_result !== null) {
		$status_cls = (count($migrate_result['errors']) === 0) ? 'success' : 'error';
		$html .= '<div class="'.$status_cls.' display-block" style="margin-bottom:12px;">';
		$html .= '<strong>R&eacute;sultat de la migration :</strong> ';
		$html .= $migrate_result['migrated'].' migr&eacute;(s), ';
		$html .= $migrate_result['skipped'].' d&eacute;j&agrave; existant(s) ignor&eacute;(s)';
		if (!empty($migrate_result['errors'])) {
			$html .= '<br/><strong>Erreurs :</strong><ul>';
			foreach ($migrate_result['errors'] as $e) {
				$html .= '<li>'.htmlspecialchars($e).'</li>';
			}
			$html .= '</ul>';
		}
		$html .= '</div>';
	}
	if ($migrate_message) $html .= $migrate_message;

	$html .= '<form action="" method="post" name="frmMigrateAnnee" id="frmMigrateAnnee">';
	$html .= '<table style="border-collapse:collapse;width:100%;max-width:720px;">';

	// Ligne 1 : Année source
	$html .= '<tr style="margin-bottom:8px;">';
	$html .= '<td style="padding:6px 12px 6px 0;font-weight:bold;white-space:nowrap;">Ann&eacute;e source&nbsp;:</td>';
	$html .= '<td style="padding:6px 0;"><select name="ak_old_annee" style="min-width:180px;">';
	if (!empty($tab_annees_dispo)) {
		foreach ($tab_annees_dispo as $an) {
			$sel = (isset($_SESSION['annee']) && $an['code_annee'] == $_SESSION['annee']) ? ' selected' : '';
			$html .= '<option value="'.htmlspecialchars($an['code_annee']).'"'.$sel.'>'.htmlspecialchars($an['lib_annee']).'</option>';
		}
	}
	$html .= '</select>';
	$html .= ' <small style="color:#888;">(ann&eacute;e dont les agents sont d&eacute;j&agrave; configur&eacute;s)</small></td>';
	$html .= '</tr>';

	// Ligne 2 : Nouvelle année
	$html .= '<tr>';
	$html .= '<td style="padding:6px 12px 6px 0;font-weight:bold;white-space:nowrap;">Nouvelle ann&eacute;e&nbsp;:</td>';
	$html .= '<td style="padding:6px 0;"><select name="ak_new_annee" style="min-width:180px;">';
	if (!empty($tab_annees_dispo)) {
		foreach ($tab_annees_dispo as $an) {
			$html .= '<option value="'.htmlspecialchars($an['code_annee']).'">'.htmlspecialchars($an['lib_annee']).'</option>';
		}
	}
	$html .= '</select></td></tr>';

	// Ligne 3 : Nouveau ID_CAMPAGNE (saisie libre + info)
	$html .= '<tr>';
	$html .= '<td style="padding:6px 12px 6px 0;font-weight:bold;white-space:nowrap;">Nouveau ID Campagne&nbsp;:</td>';
	$html .= '<td style="padding:6px 0;"><input type="number" name="ak_new_camp" value="" style="width:100px;" placeholder="ex: 5" min="1" required />';
	if (!empty($tab_camps_dispo)) {
		$html .= ' <small style="color:#888;">Campagnes existantes&nbsp;: ';
		$parts = array();
		foreach ($tab_camps_dispo as $c) { $parts[] = 'ID='.$c['ID_CAMPAGNE'].' (ann&eacute;e '.$c['ID_ANNEE'].')'; }
		$html .= implode(', ', $parts);
		$html .= '</small>';
	}
	$html .= '</td></tr>';

	// Ligne 4 : Nouveau ID_PERIODE (0 = conserver)
	$html .= '<tr>';
	$html .= '<td style="padding:6px 12px 6px 0;font-weight:bold;white-space:nowrap;">Nouveau ID P&eacute;riode&nbsp;:</td>';
	$html .= '<td style="padding:6px 0;"><input type="number" name="ak_new_periode" value="0" style="width:80px;" min="0" />';
	$html .= ' <small style="color:#888;">(0 = conserver la p&eacute;riode d\'origine de chaque agent)</small></td></tr>';

	// Ligne 5 : Filtre groupe (optionnel)
	$html .= '<tr>';
	$html .= '<td style="padding:6px 12px 6px 0;font-weight:bold;white-space:nowrap;">Filtre groupe&nbsp;:</td>';
	$html .= '<td style="padding:6px 0;"><input type="number" name="ak_id_groupe" value="'.intval(isset($_GET['id_groupe']) ? $_GET['id_groupe'] : 0).'" style="width:80px;" min="0" />';
	$html .= ' <small style="color:#888;">(0 = tous les groupes)</small></td></tr>';

	$html .= '</table>';
	$html .= '<div style="margin-top:12px;">';
	$html .= '<button type="submit" name="ak_migrate_annee" value="1" class="btn-submit" style="background:#2980b9;" ';
	$html .= 'onclick="return confirm(\'Confirmer la migration des agents vers la nouvelle ann\\u00e9e ?\\nLes agents d\\u00e9j\\u00e0 pr\\u00e9sents dans la nouvelle ann\\u00e9e ne seront pas modifi\\u00e9s.\');">';
	$html .= '&#9654; Migrer les agents vers la nouvelle ann&eacute;e</button>';
	$html .= '</div>';
	$html .= '</form>';
	$html .= '</div></div>';
	// ── fin fix AK-PHP-02 ────────────────────────────────────────────────────────

	// Fin interface import utilisateurs
	
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
