<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php 

include $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';     

// ── NASSER LOG : chargement du logger de diagnostic ──────────────────────────
require_once $GLOBALS['SISED_PATH'] . 'moblogs/nasser_logger.php';
NasserLog::debut_requete();
// ── FIN NASSER LOG ────────────────────────────────────────────────────────────

$GLOBALS['conn'] = $GLOBALS['conn_dico'] ;
$importResult = array();
$listUserFileName = "";

// ── fix AK-PHP-02 (simplifié) : Mise à jour ID_ANNEE dans DICO_FIXE_REGROUPEMENT ──
// AK-BUG-05 : CORRECTION CRITIQUE — restriction aux agents mobiles (id_groupe = 4) uniquement.
// Le bug précédent exécutait UPDATE DICO_FIXE_REGROUPEMENT SET ID_ANNEE = X sans filtre,
// ce qui mettait à jour l'année de collecte de TOUS les utilisateurs (superviseurs, admins…).
// Correction : le UPDATE est désormais limité aux ID_USER dont CODE_GROUPE = 4 dans ADMIN_USERS.
$ak_update_message = '';
$ak_update_class   = '';
if (isset($_POST['ak_update_annee'])) {
    // ── NASSER LOG ────────────────────────────────────────────────────────────
    NasserLog::clic('MIGRER_ANNEE_CLICK', $_POST, [], $_GET);
    NasserLog::note('Bouton "Migrer les agents mobiles" cliqué — ak_new_annee_simple=' . ($_POST['ak_new_annee_simple'] ?? 'N/A'));
    // ── FIN NASSER LOG ────────────────────────────────────────────────────────
    $new_annee_simple = intval($_POST['ak_new_annee_simple']);
    if ($new_annee_simple > 0) {
        // AK-BUG-05 : Filtre strict sur id_groupe = 4 (agents mobiles uniquement).
        // La sous-requête IN sélectionne uniquement les CODE_USER dont CODE_GROUPE = 4.
        // Les superviseurs (groupe 1, 2, 3) et administrateurs ne sont PAS affectés.
        $sql_upd = 'UPDATE DICO_FIXE_REGROUPEMENT'
                 . ' SET ID_ANNEE = ' . $new_annee_simple
                 . ' WHERE ID_USER IN ('
                 .   'SELECT CODE_USER FROM ADMIN_USERS'
                 .   ' WHERE CODE_GROUPE = 4'
                 . ')';
        // Journaliser la requête pour diagnostic
        error_log('[gestion_user] AK-BUG-05 : UPDATE agents mobiles — SQL: ' . $sql_upd);
        NasserLog::sql('MIGRER_ANNEE_UPDATE', $sql_upd);
        $exec_result = $GLOBALS['conn_dico']->Execute($sql_upd);
        if ($exec_result === false) {
            $db_err = method_exists($GLOBALS['conn_dico'], 'ErrorMsg')
                ? $GLOBALS['conn_dico']->ErrorMsg() : 'erreur inconnue';
            error_log('[gestion_user] AK-BUG-05 : ERREUR SQL — ' . $db_err);
            NasserLog::err('MIGRER_ANNEE_UPDATE', $db_err);
            $ak_update_message = 'Erreur lors de la mise &agrave; jour : ' . htmlspecialchars(substr($db_err, 0, 200));
            $ak_update_class   = 'error';
        } else {
            // Récupérer le libellé de l'année pour un message plus explicite
            $col_lib  = $GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']; // LIBELLE_TYPE_ANNEE
            $col_code = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];    // CODE_TYPE_ANNEE
            $sql_lib  = 'SELECT '.$col_lib.' FROM '.$GLOBALS['PARAM']['TYPE_ANNEE']
                      . ' WHERE '.$col_code.' = '.$new_annee_simple;
            // TYPE_ANNEE est dans la base principale (conn), pas dans conn_dico
            $conn_main = isset($GLOBALS['conn_main']) ? $GLOBALS['conn_main'] : null;
            $lib_annee = '';
            if ($conn_main) {
                $lib_annee = $conn_main->GetOne($sql_lib);
            }
            $label_annee = $lib_annee ? htmlspecialchars($lib_annee) : $new_annee_simple;
            $ak_update_message = 'Ann&eacute;e de collecte des agents mobiles (groupe 4) mise &agrave; jour vers '
                               . $label_annee . '.';
            $ak_update_class   = 'success';
            error_log('[gestion_user] AK-BUG-05 : mise à jour réussie vers annee=' . $new_annee_simple);
        }
        } else {
            $ak_update_message = 'Veuillez s&eacute;lectionner une ann&eacute;e valide.';
            $ak_update_class   = 'error';
        }
    // BUG-GESTION-USER-001 : purge de l'instance en session pour éviter que le bloc
    // "else if (count($_POST)>0)" ci-dessous ne rappelle maj_bdd() avec des données
    // périmées lorsque ce même POST contient ak_update_annee.
    unset($_SESSION['instance_nomenc']);
}
// ── fin fix AK-PHP-02 / AK-BUG-05 ───────────────────────────────────────────

if (isset($_POST["import"])) {
    // ── NASSER LOG ────────────────────────────────────────────────────────────
    NasserLog::clic('IMPORT_EXCEL_CLICK', $_POST, $_FILES, $_GET);
    NasserLog::note('Bouton "Importer" cliqué — fichier: ' . (isset($_FILES['file']['name']) ? $_FILES['file']['name'] : 'N/A'));
    NasserLog::note('instance_nomenc en session: ' . (isset($_SESSION['instance_nomenc']) ? 'OUI [objet '.get_class($_SESSION['instance_nomenc']).']' : 'NON — import impossible!'));
    // ── FIN NASSER LOG ────────────────────────────────────────────────────────

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

        // ── NASSER LOG ────────────────────────────────────────────────────────
        NasserLog::etape('FICHIER_ACCEPTE', 'N/A',
            'Type MIME OK | Fichier déplacé vers: ' . $targetPath, 'OK');
        // ── FIN NASSER LOG ────────────────────────────────────────────────────

        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadSheet = $Reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();
        $sheetCount = count($spreadSheetAry);

        // ── NASSER LOG ────────────────────────────────────────────────────────
        NasserLog::etape('EXCEL_PARSE', 'N/A',
            'PhpSpreadsheet toArray() → ' . $sheetCount . ' lignes (dont entête)', 'OK');
        // ── FIN NASSER LOG ────────────────────────────────────────────────────
		
		if (isset($_SESSION['instance_nomenc'] )){
			$user   =   $_SESSION['instance_nomenc'];
            // ── NASSER LOG ────────────────────────────────────────────────────
            NasserLog::etape('GET_EXCEL_DATA', 'N/A',
                'Appel get_excel_data() sur ' . $sheetCount . ' lignes', '');
            // ── FIN NASSER LOG ────────────────────────────────────────────────
    		$user->get_excel_data($spreadSheetAry, $sheetCount);  
            // ── NASSER LOG ────────────────────────────────────────────────────
            NasserLog::etape('MAJ_BDD_EXCEL_START', 'N/A',
                'Appel maj_bdd_excel("' . basename($targetPath) . '") — début import en base', '');
            // ── FIN NASSER LOG ────────────────────────────────────────────────
			$importResult = $user->maj_bdd_excel($targetPath);
            // ── NASSER LOG ────────────────────────────────────────────────────
            NasserLog::etape('MAJ_BDD_EXCEL_END', 'N/A',
                count($importResult) . ' lignes traitées retournées dans $importResult', 'OK');
            // ── FIN NASSER LOG ────────────────────────────────────────────────
			$type = "ok"; 
		} else {
            // ── NASSER LOG ────────────────────────────────────────────────────
            NasserLog::err('IMPORT_SESSION_MANQUANTE',
                'instance_nomenc ABSENT de la session — import Excel ANNULÉ (aucun INSERT effectué)');
            // ── FIN NASSER LOG ────────────────────────────────────────────────
        }
		
    } else {
        $type = "error";
        $message = "Invalid File Type. Upload Excel File.";
        // ── NASSER LOG ────────────────────────────────────────────────────────
        NasserLog::err('TYPE_FICHIER_INVALIDE',
            'Type MIME refusé: ' . (isset($_FILES['file']['type']) ? $_FILES['file']['type'] : 'N/A')
            . ' | Fichier: ' . (isset($_FILES['file']['name']) ? $_FILES['file']['name'] : 'N/A'));
        // ── FIN NASSER LOG ────────────────────────────────────────────────────
    }
} else if (count($_POST) > 0 && !isset($_POST['ak_update_annee'])) {
    // BUG-GESTION-USER-001 : guard explicite — ce bloc NE doit JAMAIS s'exécuter
    // lorsque c'est le formulaire "Migrer les agents mobiles" qui est soumis.
    // Sans ce guard, count($_POST)>0 était TRUE (POST contient ak_update_annee +
    // ak_new_annee_simple), déclenchant maj_bdd() avec l'instance_nomenc périmée
    // et supprimant les enregistrements ADMIN_USERS avec CODE_GROUPE=1.
    // Il s'agit du traitement des donnees du POST

    // ── NASSER LOG ────────────────────────────────────────────────────────────
    NasserLog::clic('USER_EDIT_POST', $_POST, [], $_GET);
    NasserLog::note('Bloc édition utilisateur (maj_bdd) — instance_nomenc: ' . (isset($_SESSION['instance_nomenc']) ? 'PRESENT' : 'ABSENT'));
    // ── FIN NASSER LOG ────────────────────────────────────────────────────────
    
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
    
} else {
    // ── NASSER LOG : aucun POST significatif → simple affichage de la page ────
    NasserLog::clic('AFFICHAGE_PAGE', $_POST, [], $_GET);
    // ── FIN NASSER LOG ────────────────────────────────────────────────────────
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

	// ── fix AK-PHP-02 (simplifié) : Fixer l'année des agents mobiles ─────────
	// Utilise $_SESSION['tab_annees'] (SELECT * FROM TYPE_ANNEE, peuplé par common.php)
	// dont les clés sont CODE_TYPE_ANNEE et LIBELLE_TYPE_ANNEE (AdoDB ASSOC_CASE_UPPER).
	// Fallback : requête directe si la session n'est pas encore peuplée.
	$col_code = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];     // CODE_TYPE_ANNEE
	$col_lib  = $GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];  // LIBELLE_TYPE_ANNEE
	$col_ord  = $GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];    // ORDRE_TYPE_ANNEE
	if (!empty($_SESSION['tab_annees'])) {
		$tab_annees_dispo = $_SESSION['tab_annees'];
	} else {
		$tab_annees_dispo = $GLOBALS['conn_dico']->GetAll(
			'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_ANNEE']
			.' ORDER BY '.$col_ord.' DESC'
		);
	}

	$html .= '<div class="inner-box" style="margin-top:20px;">';
	$html .= '<div class="inner-box-title" style="background:#2980b9;color:#fff;">&#9654; Fixer les agents mobiles sur une nouvelle ann&eacute;e de collecte</div>';
	$html .= '<div class="inner-box-container">';

	// Résultat mise à jour si disponible
	if (!empty($ak_update_message)) {
		$html .= '<div class="'.$ak_update_class.' display-block" style="margin-bottom:12px;">'.$ak_update_message.'</div>';
	}

	$html .= '<form action="" method="post" name="frmUpdateAnnee" id="frmUpdateAnnee" style="margin:0;">';
	$html .= '<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">';

	// Dropdown années — valeur = CODE_TYPE_ANNEE, label = LIBELLE_TYPE_ANNEE
	$html .= '<select name="ak_new_annee_simple" style="min-width:200px;padding:6px 10px;">';
	if (!empty($tab_annees_dispo)) {
		foreach ($tab_annees_dispo as $an) {
			$code_an = isset($an[$col_code]) ? $an[$col_code] : '';
			$lib_an  = isset($an[$col_lib])  ? $an[$col_lib]  : $code_an;
			$sel = (isset($_SESSION['annee']) && (string)$code_an === (string)$_SESSION['annee']) ? ' selected' : '';
			$html .= '<option value="'.htmlspecialchars($code_an).'"'.$sel.'>'.htmlspecialchars($lib_an).'</option>';
		}
	}
	$html .= '</select>';

	// AK-BUG-05 : message de confirmation précisant que seuls les agents mobiles (groupe 4) sont affectés
	$html .= '<button type="submit" name="ak_update_annee" value="1" class="btn-submit" style="background:#2980b9;padding:6px 18px;" ';
	$html .= 'onclick="return confirm(\'Confirmer la mise à jour de l\'année de collecte des agents mobiles (groupe 4) uniquement ?\')">'
;
	$html .= 'Migrer les agents mobiles (groupe 4)</button>';

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
