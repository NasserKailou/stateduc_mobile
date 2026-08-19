<?php
/**
 * FIE — AdminController
 * Tableau d'administration : synchronisation API/Excel, gestion utilisateurs, journal d'audit.
 * CORRECTION Phase 1 :
 *   - Suppression namespace App\Controllers et tous les use App\...
 *   - requireRole() appelé avec rôles valides du schéma
 *   - Colonne `login` et `last_login_at` conformes au schéma SQL
 *   - Flash messages normalisés : $_SESSION['fie_flash_*']
 * CORRECTION Phase 2 :
 *   - Database::getInstance() retourne un objet PDO brut (pas de wrapper).
 *   - fetchScalar / fetchAll / fetchOne sont des méthodes STATIQUES de la
 *     classe Database, pas des méthodes d'instance PDO.
 *   - Suppression de tous les $db = Database::getInstance() dans les
 *     méthodes index(), syncStatus(), users() et auditLog().
 *   - Remplacement de $db->fetchScalar/fetchAll/fetchOne(...)
 *     par Database::fetchScalar/fetchAll/fetchOne(...) partout.
 */

declare(strict_types=1);

require_once FIE_SERVICES_PATH . 'SecurityHelper.php';
require_once FIE_SERVICES_PATH . 'Logger.php';
require_once FIE_SERVICES_PATH . 'SyncService.php';
require_once FIE_CONFIG_PATH   . 'Database.php';

class AdminController
{
    private Logger $log;

    public function __construct()
    {
        // Rôles autorisés : super_admin ou admin_central
        SecurityHelper::requireRole(['super_admin', 'admin_central']);
        $this->log = new Logger('admin');
    }

    /* ── GET /admin ──────────────────────────────────────────────────────── */

    public function index(): void
    {
        $stats = [
            'etablissements' => (int)Database::fetchScalar(
                "SELECT COUNT(*) FROM etablissements_miroir WHERE actif = 1"
            ),
            'eleves'         => (int)Database::fetchScalar(
                "SELECT COUNT(*) FROM eleves"
            ),
            'inscriptions'   => (int)Database::fetchScalar(
                "SELECT COUNT(*) FROM inscriptions WHERE statut = 'inscrit'"
            ),
            'doublons'       => (int)Database::fetchScalar(
                "SELECT COUNT(*) FROM eleves WHERE doublon_suspect = 1"
            ),
        ];

        $lastSync = Database::fetchOne(
            "SELECT * FROM sync_log ORDER BY started_at DESC LIMIT 1"
        );

        $pendingAggregats = (int)Database::fetchScalar(
            "SELECT COUNT(*) FROM agregats_eleves_age_niveau_sexe WHERE synced_to_stateduc = 0"
        );

        $page_title  = 'Administration — FIE';
        $active_menu = 'admin_home';
        require BASE_PATH . '/app/views/admin/index.php';
    }

    /* ── GET /admin/sync ─────────────────────────────────────────────────── */

    public function syncStatus(): void
    {
        $logs = Database::fetchAll(
            "SELECT * FROM sync_log ORDER BY started_at DESC LIMIT 20"
        );

        $lastSuccess = Database::fetchOne(
            "SELECT * FROM sync_log WHERE status = 'success' ORDER BY started_at DESC LIMIT 1"
        );

        $etablissementsCount = (int)Database::fetchScalar(
            "SELECT COUNT(*) FROM etablissements_miroir"
        );

        $bySource = Database::fetchAll(
            "SELECT source, COUNT(*) as nb FROM etablissements_miroir GROUP BY source"
        );

        $page_title  = 'Synchronisation — Administration FIE';
        $active_menu = 'admin_sync';
        require BASE_PATH . '/app/views/admin/sync.php';
    }

    /* ── POST /admin/sync/lancer ─────────────────────────────────────────── */

    public function triggerSync(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST[FIE_CSRF_TOKEN_NAME] ?? '')) {
            $this->jsonError('Jeton CSRF invalide', 403);
            return;
        }

        $mode     = $_POST['mode']     ?? 'full';
        $perPage  = (int)($_POST['per_page'] ?? 100);
        $province = $_POST['province'] ?? null;

        $sync = new SyncService();

        // Paramètres individuels pour syncFromApi (signature correcte)
        $updatedSince = ($mode === 'incremental') ? date('Y-m-d', strtotime('-30 days')) : null;
        $secteur      = null;  // pas de filtre secteur depuis l'UI pour l'instant

        $this->log->info("Lancement synchronisation manuelle", [
            'user'     => $_SESSION['fie_user']['username'] ?? 'unknown',
            'mode'     => $mode,
            'province' => $province,
        ]);

        try {
            $result  = $sync->syncFromApi(
                $updatedSince,
                $secteur,
                $province ?: null,
                $_SESSION['fie_user']['username'] ?? 'admin-ui'
            );
            $ok      = true;
            $message = "Synchronisation terminée : {$result['inserted']} insérés, {$result['updated']} mis à jour.";
        } catch (Throwable $e) {
            $ok      = false;
            $message = "Synchronisation échouée : " . $e->getMessage();
            $result  = ['inserted' => 0, 'updated' => 0, 'errors' => 1, 'total' => 0];
        }

        SecurityHelper::jsonResponse([
            'ok'      => $ok,
            'message' => $message,
            'data'    => $result,
        ]);
    }

    /* ── GET /admin/import-excel ─────────────────────────────────────────── */

    public function importExcelForm(): void
    {
        $page_title  = 'Import Excel — Administration FIE';
        $active_menu = 'admin_import';
        require BASE_PATH . '/app/views/admin/import_excel.php';
    }

    /* ── POST /admin/import-excel ────────────────────────────────────────── */

    public function processExcelImport(): void
    {
        // FIE_CSRF_TOKEN_NAME = '_csrf_token' — utiliser la constante, pas le nom brut
        if (!SecurityHelper::verifyCsrf($_POST[FIE_CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['fie_flash_error'] = 'Jeton CSRF invalide.';
            header('Location: ' . BASE_URL . '/admin/import-excel');
            exit;
        }

        if (empty($_FILES['excel_file']['tmp_name'])) {
            $_SESSION['fie_flash_error'] = 'Aucun fichier sélectionné.';
            header('Location: ' . BASE_URL . '/admin/import-excel');
            exit;
        }

        $tmpFile  = $_FILES['excel_file']['tmp_name'];
        $origName = $_FILES['excel_file']['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls'], true)) {
            $_SESSION['fie_flash_error'] = 'Format non supporté. Utilisez .xlsx ou .xls';
            header('Location: ' . BASE_URL . '/admin/import-excel');
            exit;
        }

        $cacheDir = BASE_PATH . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0750, true);
        }

        $destFile = $cacheDir . '/import_' . date('YmdHis') . '.' . $ext;
        if (!move_uploaded_file($tmpFile, $destFile)) {
            $_SESSION['fie_flash_error'] = "Erreur lors de l'enregistrement du fichier.";
            header('Location: ' . BASE_URL . '/admin/import-excel');
            exit;
        }

        $this->log->info("Import Excel démarré", [
            'user' => $_SESSION['fie_user']['username'] ?? 'unknown',
            'file' => $origName,
        ]);

        $sync   = new SyncService();
        try {
            $result = $sync->importFromExcel($destFile);
        } catch (\Throwable $importEx) {
            @unlink($destFile);
            $_SESSION['fie_flash_error'] = "Erreur lors de l'import : " . $importEx->getMessage();
            header('Location: ' . BASE_URL . '/admin/sync');
            exit;
        }

        @unlink($destFile);

        // importFromExcel() retourne ['inserted','skipped','errors','total']
        // Mode INSERT-ONLY : les lignes déjà en base sont ignorées (skipped).
        if (($result['errors'] ?? 0) === 0) {
            $_SESSION['fie_flash_success'] = sprintf(
                'Import terminé : %d nouveaux établissements insérés, %d déjà présents ignorés (sur %d lignes).',
                $result['inserted'] ?? 0,
                $result['skipped']  ?? 0,
                $result['total']    ?? 0
            );
        } else {
            $_SESSION['fie_flash_error'] = sprintf(
                'Import terminé avec %d erreur(s) : %d insérés, %d ignorés (sur %d lignes).',
                $result['errors']   ?? 0,
                $result['inserted'] ?? 0,
                $result['skipped']  ?? 0,
                $result['total']    ?? 0
            );
        }

        header('Location: ' . BASE_URL . '/admin/sync');
        exit;
    }

    /* ── GET /admin/users ────────────────────────────────────────────────── */

    public function users(): void
    {
        // CORRECTION : colonnes conformes au schéma (login, not username; last_login_at, not derniere_connexion)
        $users = Database::fetchAll(
            "SELECT id, login, nom, prenoms, role, province_perimetre,
                    actif, last_login_at, created_at
             FROM fie_users ORDER BY nom, prenoms"
        );

        $page_title  = 'Utilisateurs — Administration FIE';
        $active_menu = 'admin_users';
        require BASE_PATH . '/app/views/admin/users.php';
    }

    /* ── GET /admin/audit ────────────────────────────────────────────────── */

    public function auditLog(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $total = (int)Database::fetchScalar("SELECT COUNT(*) FROM audit_log");
        $pages = max(1, (int)ceil($total / $perPage));

        // CORRECTION : colonne `login` pas `username` dans la jointure
        $logs = Database::fetchAll(
            "SELECT al.*, fu.login AS username
             FROM audit_log al
             LEFT JOIN fie_users fu ON al.user_id = fu.id
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        $page_title  = "Journal d'audit — Administration FIE";
        $active_menu = 'admin_audit';
        require BASE_PATH . '/app/views/admin/audit.php';
    }

    /* ── GET /admin/users/nouveau ───────────────────────────────────────── */

    public function userNewForm(): void
    {
        $ecoles = Database::fetchAll(
            "SELECT code_etablissement, nom_etablissement
             FROM etablissements_miroir WHERE actif = 1
             ORDER BY nom_etablissement LIMIT 500"
        );
        $classes = Database::fetchAll(
            "SELECT id, nom_classe, ecole_code AS code_etablissement, annee_scolaire
             FROM classes ORDER BY annee_scolaire DESC, nom_classe LIMIT 500"
        );
        $errors  = [];
        $old     = [];
        $page_title  = 'Nouvel utilisateur — Admin FIE';
        $active_menu = 'admin_user_form';
        require BASE_PATH . '/app/views/admin/user_form.php';
    }

    /* ── POST /admin/users/nouveau ──────────────────────────────────────── */

    public function userCreate(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST[FIE_CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['fie_flash_error'] = 'Jeton CSRF invalide.';
            header('Location: ' . BASE_URL . '/admin/users/nouveau');
            exit;
        }

        $errors = $this->validateUserForm($_POST);
        if (!empty($errors)) {
            $ecoles = Database::fetchAll(
                "SELECT code_etablissement, nom_etablissement
                 FROM etablissements_miroir WHERE actif = 1 ORDER BY nom_etablissement LIMIT 500"
            );
            $classes = Database::fetchAll(
                "SELECT id, nom_classe, ecole_code AS code_etablissement, annee_scolaire
                 FROM classes ORDER BY annee_scolaire DESC, nom_classe LIMIT 500"
            );
            $old     = $_POST;
            $page_title  = 'Nouvel utilisateur — Admin FIE';
            $active_menu = 'admin_user_form';
            require BASE_PATH . '/app/views/admin/user_form.php';
            return;
        }

        $hash = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);
        Database::query(
            "INSERT INTO fie_users
               (login, password_hash, nom, prenoms, role, ecole_code, classe_id,
                nom_complet, province_perimetre, actif, created_at)
             VALUES (?,?,?,?,?,?,?,?,?,1,NOW())",
            [
                trim($_POST['login']),
                $hash,
                trim($_POST['nom']),
                trim($_POST['prenoms'] ?? ''),
                $_POST['role'],
                $_POST['ecole_code']  ?: null,
                ($_POST['classe_id'] ?? '') ?: null,
                trim($_POST['nom_complet'] ?? ($_POST['prenoms'] . ' ' . $_POST['nom'])),
                $_POST['province_perimetre'] ?: null,
            ]
        );

        $this->log->info("Utilisateur créé", ['login' => $_POST['login'], 'role' => $_POST['role']]);
        $_SESSION['fie_flash_success'] = 'Utilisateur ' . htmlspecialchars($_POST['login'], ENT_QUOTES) . ' créé.';
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    /* ── GET /admin/users/:id/editer ───────────────────────────────────── */

    public function userEditForm(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $user = Database::fetchOne("SELECT * FROM fie_users WHERE id = ?", [$id]);
        if (!$user) {
            $_SESSION['fie_flash_error'] = 'Utilisateur introuvable.';
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }
        $ecoles = Database::fetchAll(
            "SELECT code_etablissement, nom_etablissement
             FROM etablissements_miroir WHERE actif = 1 ORDER BY nom_etablissement LIMIT 500"
        );
        $classes = Database::fetchAll(
            "SELECT id, nom_classe, ecole_code AS code_etablissement, annee_scolaire
             FROM classes ORDER BY annee_scolaire DESC, nom_classe LIMIT 500"
        );
        $errors  = [];
        $old     = $user; // pré-remplissage
        $editMode = true;
        $page_title  = 'Modifier utilisateur — Admin FIE';
        $active_menu = 'admin_users';
        require BASE_PATH . '/app/views/admin/user_form.php';
    }

    /* ── POST /admin/users/:id/editer ──────────────────────────────────── */

    public function userUpdate(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST[FIE_CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['fie_flash_error'] = 'Jeton CSRF invalide.';
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        $errors = $this->validateUserForm($_POST, $id);
        if (!empty($errors)) {
            $ecoles = Database::fetchAll(
                "SELECT code_etablissement, nom_etablissement
                 FROM etablissements_miroir WHERE actif = 1 ORDER BY nom_etablissement LIMIT 500"
            );
            $classes = Database::fetchAll(
                "SELECT id, nom_classe, ecole_code AS code_etablissement, annee_scolaire
                 FROM classes ORDER BY annee_scolaire DESC, nom_classe LIMIT 500"
            );
            $old = $_POST; $old['id'] = $id;
            $editMode = true;
            $page_title = 'Modifier utilisateur — Admin FIE';
            $active_menu = 'admin_users';
            require BASE_PATH . '/app/views/admin/user_form.php';
            return;
        }

        $params = [
            trim($_POST['nom']),
            trim($_POST['prenoms'] ?? ''),
            $_POST['role'],
            $_POST['ecole_code']  ?: null,
            ($_POST['classe_id'] ?? '') ?: null,
            trim($_POST['nom_complet'] ?? ($_POST['prenoms'] . ' ' . $_POST['nom'])),
            $_POST['province_perimetre'] ?: null,
            (int)($_POST['actif'] ?? 1),
        ];
        $sql = "UPDATE fie_users SET nom=?,prenoms=?,role=?,ecole_code=?,classe_id=?,
                nom_complet=?,province_perimetre=?,actif=?";
        if (!empty($_POST['mot_de_passe'])) {
            $sql     .= ',password_hash=?';
            $params[] = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);
        }
        $sql     .= ' WHERE id=?';
        $params[] = $id;
        Database::query($sql, $params);

        $this->log->info("Utilisateur modifié", ['id' => $id]);
        $_SESSION['fie_flash_success'] = 'Utilisateur mis à jour.';
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    /* ── POST /admin/users/:id/supprimer ───────────────────────────────── */

    public function userDelete(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST[FIE_CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['fie_flash_error'] = 'Jeton CSRF invalide.';
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        // Soft-delete : désactiver plutôt que supprimer
        Database::query("UPDATE fie_users SET actif=0 WHERE id=?", [$id]);
        $this->log->info("Utilisateur désactivé", ['id' => $id]);
        $_SESSION['fie_flash_success'] = 'Utilisateur désactivé.';
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // IMPORT EXCEL ÉLÈVES
    // ══════════════════════════════════════════════════════════════════════════

    /* ── GET /admin/import-eleves ────────────────────────────────────────── */

    public function importElevesForm(): void
    {
        $page_title  = 'Import liste élèves — FIE';
        $active_menu = 'admin_import_eleves';
        // Liste des établissements pour le select
        $ecoles = Database::fetchAll(
            "SELECT code_etablissement, nom_etablissement
             FROM etablissements_miroir WHERE actif = 1
             ORDER BY nom_etablissement LIMIT 500"
        ) ?: [];
        $annees = Database::fetchAll(
            "SELECT code_type_annee, libelle, actif FROM ref_type_annee ORDER BY code_type_annee DESC"
        ) ?: [];
        require BASE_PATH . '/app/views/admin/import_eleves.php';
    }

    /* ── GET /admin/import-eleves/modele ─────────────────────────────────── */

    public function downloadElevesTemplate(): void
    {
        // ── Charger PhpSpreadsheet via l'autoloader du projet StatEduc ──────
        // BASE_PATH = .../app_fie  — StatEduc est dans le répertoire parent (.../StatEduc_burundi)
        $candidates = [
            BASE_PATH . '/../../StatEduc_burundi/server-side/lib/autoload.php',
            dirname(BASE_PATH) . '/StatEduc_burundi/server-side/lib/autoload.php',
            realpath(BASE_PATH . '/..') . '/StatEduc_burundi/server-side/lib/autoload.php',
        ];
        $autoloaderPath = null;
        foreach ($candidates as $candidate) {
            if ($candidate && file_exists($candidate)) {
                $autoloaderPath = $candidate;
                break;
            }
        }

        if ($autoloaderPath) {
            require_once $autoloaderPath;
            $this->downloadElevesTemplateXlsx();
        } else {
            // Fallback CSV si PhpSpreadsheet non disponible
            $this->downloadElevesTemplateCsv();
        }
    }

    /** Génère le modèle XLSX avec listes déroulantes (PhpSpreadsheet requis) */
    private function downloadElevesTemplateXlsx(): void
    {
        // Supprimer les notices Deprecated de la vieille version PhpSpreadsheet
        // (ReturnTypeWillChange PHP8) — elles pollueraient le flux binaire XLSX
        $prevErrorLevel = error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        // Capturer tout output parasite (notices d'autres libs) avant les headers
        ob_start();

        // Namespace résolu via autoloader : \PhpOffice\PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Modèle import élèves');

        // ── 1. En-têtes des colonnes ──────────────────────────────────────
        $columns = [
            'A' => 'nom',
            'B' => 'prenoms',
            'C' => 'sexe',
            'D' => 'date_naissance',
            'E' => 'lieu_naissance',
            'F' => 'province_naissance',
            'G' => 'nationalite',
            'H' => 'nom_pere',
            'I' => 'nom_mere',
            'J' => 'nom_tuteur',
            'K' => 'telephone_tuteur',
            'L' => 'code_etablissement',
            'M' => 'code_type_annee',
            'N' => 'code_type_secteur_ens',
            'O' => 'code_type_niveau',
            'P' => 'code_type_section',
            'Q' => 'numero_classe',
            'R' => 'date_inscription',
        ];

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF1A56DB']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];

        foreach ($columns as $col => $label) {
            $sheet->setCellValue($col . '1', $label);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $sheet->getColumnDimension($col)->setWidth(20);
        }

        // ── 2. Ligne d'exemple ────────────────────────────────────────────
        // Récupérer l'année active pour l'exemple
        $anneeActive = Database::fetchOne(
            "SELECT code_type_annee FROM ref_type_annee WHERE actif=1 ORDER BY code_type_annee DESC LIMIT 1"
        );
        $codeAnneeEx = $anneeActive['code_type_annee'] ?? date('Y');

        $exemple = [
            'A' => 'NIYONZIMA',        'B' => 'Jean-Pierre',
            'C' => 'M',                 'D' => '2010-05-14',
            'E' => 'Gitega',            'F' => 'Gitega',
            'G' => 'BDI',               'H' => 'NIYONZIMA Gérard',
            'I' => 'HAKIZIMANA Cécile', 'J' => '',
            'K' => '+257 79 123 456',   'L' => '21422',
            'M' => (string)$codeAnneeEx,'N' => '2',
            'O' => '4',                 'P' => '1',
            'Q' => '1AF-A',             'R' => date('Y-m-d'),
        ];
        foreach ($exemple as $col => $val) {
            $sheet->setCellValue($col . '2', $val);
        }
        $sheet->getStyle('A2:R2')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                       'startColor' => ['argb' => 'FFF0F4FF']],
        ]);

        // ── 3. Onglet caché "RefData" pour les listes déroulantes ─────────
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('RefData');

        // 3a. Sexe
        $refSheet->setCellValue('A1', 'code_sexe');
        $refSheet->setCellValue('B1', 'libelle_sexe');
        $sexeData = [['M', 'Masculin'], ['F', 'Féminin']];
        $rowIdx = 2;
        foreach ($sexeData as $row) {
            $refSheet->setCellValue('A' . $rowIdx, $row[0]);
            $refSheet->setCellValue('B' . $rowIdx, $row[1]);
            $rowIdx++;
        }
        // La liste affichera "Masculin / Féminin" mais stockera M / F
        // On crée les labels concaténés pour la validation directe
        $sexeLabels = '"Masculin,Féminin"';

        // 3b. Nationalités — codes ISO 3166-1 alpha-3 (pas de table ref_type_nationalite)
        // Liste des principaux pays africains + monde pour le contexte Burundi
        $nationalites = [
            ['BDI','Burundais(e)'],['COD','Congolais(e) RDC'],['RWA','Rwandais(e)'],
            ['TZA','Tanzanien(ne)'],['UGA','Ougandais(e)'],['KEN','Kényan(e)'],
            ['ETH','Éthiopien(ne)'],['BEL','Belge'],['FRA','Français(e)'],
            ['USA','Américain(e)'],['GBR','Britannique'],['DEU','Allemand(e)'],
            ['CHN','Chinois(e)'],['IND','Indien(ne)'],['ZAF','Sud-Africain(e)'],
            ['MOZ','Mozambicain(e)'],['ZMB','Zambien(ne)'],['MWI','Malawien(ne)'],
            ['CAF','Centrafricain(e)'],['CMR','Camerounais(e)'],['SSD','Sud-Soudanais(e)'],
            ['AUT','Autrichien(ne)'],['ITA','Italien(ne)'],['NLD','Néerlandais(e)'],
            ['XXX','Autre / Inconnu'],
        ];
        $refSheet->setCellValue('C1', 'code_nat');
        $refSheet->setCellValue('D1', 'libelle_nat');
        $rowIdx = 2;
        foreach ($nationalites as $n) {
            $refSheet->setCellValue('C' . $rowIdx, $n[0]);
            $refSheet->setCellValue('D' . $rowIdx, $n[1]);
            $rowIdx++;
        }
        $natLastRow = max(2, $rowIdx - 1);

        // 3c. Années scolaires
        $annees = Database::fetchAll(
            "SELECT code_type_annee, libelle FROM ref_type_annee ORDER BY code_type_annee DESC LIMIT 20"
        ) ?: [];
        $refSheet->setCellValue('E1', 'code_annee');
        $refSheet->setCellValue('F1', 'libelle_annee');
        $rowIdx = 2;
        foreach ($annees as $a) {
            $refSheet->setCellValue('E' . $rowIdx, $a['code_type_annee']);
            $refSheet->setCellValue('F' . $rowIdx, $a['libelle'] ?? $a['code_type_annee']);
            $rowIdx++;
        }
        $anneeLastRow = max(2, $rowIdx - 1);

        // 3d. Secteurs enseignement (table réelle : ref_secteur_ens, pas ref_type_secteur_ens)
        $secteurs = Database::fetchAll(
            "SELECT code_type_secteur_ens, libelle FROM ref_secteur_ens ORDER BY ordre, libelle LIMIT 50"
        ) ?: [];
        $refSheet->setCellValue('G1', 'code_secteur');
        $refSheet->setCellValue('H1', 'libelle_secteur');
        $rowIdx = 2;
        foreach ($secteurs as $s) {
            $refSheet->setCellValue('G' . $rowIdx, $s['code_type_secteur_ens']);
            $refSheet->setCellValue('H' . $rowIdx, $s['libelle'] ?? $s['code_type_secteur_ens']);
            $rowIdx++;
        }
        $sectLastRow = max(2, $rowIdx - 1);

        // 3e. Niveaux
        $niveaux = Database::fetchAll(
            "SELECT code_type_niveau, libelle FROM ref_type_niveau ORDER BY libelle LIMIT 50"
        ) ?: [];
        $refSheet->setCellValue('I1', 'code_niveau');
        $refSheet->setCellValue('J1', 'libelle_niveau');
        $rowIdx = 2;
        foreach ($niveaux as $nv) {
            $refSheet->setCellValue('I' . $rowIdx, $nv['code_type_niveau']);
            $refSheet->setCellValue('J' . $rowIdx, $nv['libelle'] ?? $nv['code_type_niveau']);
            $rowIdx++;
        }
        $niveauLastRow = max(2, $rowIdx - 1);

        // 3f. Sections
        $sections = Database::fetchAll(
            "SELECT code_type_section, libelle FROM ref_type_section ORDER BY libelle LIMIT 50"
        ) ?: [];
        $refSheet->setCellValue('K1', 'code_section');
        $refSheet->setCellValue('L1', 'libelle_section');
        $rowIdx = 2;
        foreach ($sections as $sec) {
            $refSheet->setCellValue('K' . $rowIdx, $sec['code_type_section']);
            $refSheet->setCellValue('L' . $rowIdx, $sec['libelle'] ?? $sec['code_type_section']);
            $rowIdx++;
        }
        $sectionLastRow = max(2, $rowIdx - 1);

        // 3g. Numéros de classe (depuis inscriptions existantes)
        $classes = Database::fetchAll(
            "SELECT DISTINCT numero_classe FROM inscriptions WHERE numero_classe IS NOT NULL ORDER BY numero_classe LIMIT 100"
        ) ?: [];
        $refSheet->setCellValue('M1', 'numero_classe');
        $rowIdx = 2;
        foreach ($classes as $cl) {
            $refSheet->setCellValue('M' . $rowIdx, $cl['numero_classe']);
            $rowIdx++;
        }
        $classeLastRow = max(2, $rowIdx - 1);

        // Masquer l'onglet RefData
        $refSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // ── 4. Validations de données (listes déroulantes) ────────────────
        $spreadsheet->setActiveSheetIndex(0);
        $mainSheet = $spreadsheet->getActiveSheet();

        $maxRows = 500; // Appliquer sur 500 lignes max
        $sheetName = 'RefData';

        // Helper : crée une validation liste depuis une plage RefData
        $makeListValidation = function(
            string $col,
            string $refRange,
            string $prompt,
            string $title
        ) use ($mainSheet, $maxRows, $sheetName): void {
            $validation = $mainSheet->getCell($col . '2')->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1($refRange);
            $validation->setError('Valeur invalide. Choisissez dans la liste.');
            $validation->setErrorTitle('Valeur non reconnue');
            $validation->setPrompt($prompt);
            $validation->setPromptTitle($title);
            $validation->setSqref($col . '2:' . $col . $maxRows);
            $mainSheet->setDataValidation($col . '2:' . $col . $maxRows, $validation);
        };

        // C — Sexe (liste fixe directe : plus simple, pas de ref sheet)
        $vSexe = $mainSheet->getCell('C2')->getDataValidation();
        $vSexe->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $vSexe->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $vSexe->setAllowBlank(true);
        $vSexe->setShowDropDown(true);
        $vSexe->setShowInputMessage(true);
        $vSexe->setShowErrorMessage(true);
        $vSexe->setFormula1('"M,F"');
        $vSexe->setPrompt('M = Masculin, F = Féminin');
        $vSexe->setPromptTitle('Sexe');
        $vSexe->setError('Entrez M (Masculin) ou F (Féminin).');
        $vSexe->setErrorTitle('Valeur invalide');
        $vSexe->setSqref('C2:C' . $maxRows);
        $mainSheet->setDataValidation('C2:C' . $maxRows, $vSexe);

        // G — Nationalité (codes depuis RefData!C)
        $makeListValidation('G', "RefData!\$C\$2:\$C\${$natLastRow}", 'Choisissez un code nationalité (ex: BDI)', 'Nationalité');

        // M — Année scolaire
        $makeListValidation('M', "RefData!\$E\$2:\$E\${$anneeLastRow}", 'Choisissez le code année scolaire', 'Année scolaire');

        // N — Secteur enseignement
        $makeListValidation('N', "RefData!\$G\$2:\$G\${$sectLastRow}", 'Choisissez le code secteur enseignement', 'Secteur');

        // O — Niveau
        $makeListValidation('O', "RefData!\$I\$2:\$I\${$niveauLastRow}", 'Choisissez le code niveau', 'Niveau');

        // P — Section
        $makeListValidation('P', "RefData!\$K\$2:\$K\${$sectionLastRow}", 'Choisissez le code section', 'Section');

        // Q — Numéro de classe
        if ($classeLastRow > 1) {
            $makeListValidation('Q', "RefData!\$M\$2:\$M\${$classeLastRow}", 'Choisissez ou saisissez le numéro de classe', 'Classe');
        }

        // ── 5. Ajouter une légende sur un 3e onglet ───────────────────────
        $helpSheet = $spreadsheet->createSheet();
        $helpSheet->setTitle('Légende');
        $helpSheet->setCellValue('A1', 'Colonne');
        $helpSheet->setCellValue('B1', 'Description');
        $helpSheet->setCellValue('C1', 'Format / Valeurs');
        $helpStyle = ['font' => ['bold' => true],
                      'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                  'startColor' => ['argb' => 'FFCED4DA']]];
        $helpSheet->getStyle('A1:C1')->applyFromArray($helpStyle);

        $legend = [
            ['sexe (C)',              'Sexe de l\'élève',                     'M ou F'],
            ['nationalite (G)',       'Code nationalité',                     'BDI = Burundais, etc. (voir RefData)'],
            ['code_type_annee (M)',   'Année scolaire (code numérique)',      'Ex: 1 = 2024-2025 (voir RefData)'],
            ['code_type_secteur_ens (N)', 'Secteur enseignement',             'Code numérique (voir RefData)'],
            ['code_type_niveau (O)', 'Niveau scolaire',                       'Code numérique (voir RefData)'],
            ['code_type_section (P)', 'Section / Filière',                   'Code numérique (voir RefData)'],
            ['numero_classe (Q)',     'Numéro ou nom de la classe',           'Ex: CP1-A, 6A, 1ere-A'],
            ['date_naissance (D)',    'Date de naissance',                    'Format : AAAA-MM-JJ'],
            ['date_inscription (R)', 'Date inscription',                      'Format : AAAA-MM-JJ'],
        ];
        $r = 2;
        foreach ($legend as $row) {
            $helpSheet->setCellValue('A' . $r, $row[0]);
            $helpSheet->setCellValue('B' . $r, $row[1]);
            $helpSheet->setCellValue('C' . $r, $row[2]);
            $r++;
        }
        $helpSheet->getColumnDimension('A')->setWidth(28);
        $helpSheet->getColumnDimension('B')->setWidth(32);
        $helpSheet->getColumnDimension('C')->setWidth(40);

        // ── 6. Figer la ligne d'en-tête ──────────────────────────────────
        $spreadsheet->setActiveSheetIndex(0);
        $mainSheet->freezePane('A2');
        $mainSheet->getRowDimension(1)->setRowHeight(20);

        // ── 7. Téléchargement : vider output parasite PUIS envoyer headers ──
        // Vider tout output parasite capturé avant d'envoyer les headers XLSX
        ob_end_clean();

        $filename = 'modele_import_eleves_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

        // Restaurer le niveau d'erreur PHP
        error_reporting($prevErrorLevel);
        exit;
    }

    /** Fallback CSV si PhpSpreadsheet non disponible */
    private function downloadElevesTemplateCsv(): void
    {
        $headers = [
            'nom', 'prenoms', 'sexe', 'date_naissance',
            'lieu_naissance', 'province_naissance', 'nationalite',
            'nom_pere', 'nom_mere', 'nom_tuteur', 'telephone_tuteur',
            'code_etablissement', 'code_type_annee', 'code_type_secteur_ens',
            'code_type_niveau', 'code_type_section', 'numero_classe', 'date_inscription',
        ];
        $exemple = [
            'NIYONZIMA', 'Jean-Pierre', 'M', '2010-05-14',
            'Gitega', 'Gitega', 'BDI',
            'NIYONZIMA Gérard', 'HAKIZIMANA Cécile', '', '+257 79 123 456',
            '21422', '1', '1', '2', '1', 'CP1-A', date('Y-m-d'),
        ];

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="modele_import_eleves.csv"');
        header('Cache-Control: no-cache, no-store');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers, ';');
        fputcsv($out, $exemple, ';');
        fclose($out);
        exit;
    }

    /* ── POST /admin/import-eleves ───────────────────────────────────────── */

    public function processElevesImport(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST[FIE_CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['fie_flash_error'] = 'Jeton CSRF invalide.';
            header('Location: ' . BASE_URL . '/admin/import-eleves');
            exit;
        }

        if (empty($_FILES['eleves_file']['tmp_name'])) {
            $_SESSION['fie_flash_error'] = 'Aucun fichier sélectionné.';
            header('Location: ' . BASE_URL . '/admin/import-eleves');
            exit;
        }

        $tmpFile  = $_FILES['eleves_file']['tmp_name'];
        $origName = $_FILES['eleves_file']['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            $_SESSION['fie_flash_error'] = 'Format non supporté. Utilisez .xlsx, .xls ou .csv';
            header('Location: ' . BASE_URL . '/admin/import-eleves');
            exit;
        }

        require_once FIE_MODELS_PATH   . 'EleveModel.php';
        require_once FIE_MODELS_PATH   . 'InscriptionModel.php';
        require_once FIE_SERVICES_PATH . 'IueGenerator.php';
        require_once FIE_SERVICES_PATH . 'SyncService.php';  // readExcelNative()

        $cacheDir = BASE_PATH . '/cache';
        if (!is_dir($cacheDir)) mkdir($cacheDir, 0750, true);
        $destFile = $cacheDir . '/import_eleves_' . date('YmdHis') . '.' . $ext;
        if (!move_uploaded_file($tmpFile, $destFile)) {
            $_SESSION['fie_flash_error'] = "Erreur lors de l'enregistrement du fichier.";
            header('Location: ' . BASE_URL . '/admin/import-eleves');
            exit;
        }

        $this->log->info("Import élèves démarré", [
            'user' => $_SESSION['fie_user']['username'] ?? 'unknown',
            'file' => $origName,
        ]);

        $counts = ['inserted' => 0, 'skipped' => 0, 'errors' => 0, 'total' => 0, 'messages' => []];

        try {
            if ($ext === 'csv') {
                $rows = $this->readCsvRows($destFile);
            } else {
                $sync = new SyncService();
                $rows = $sync->readExcelNative($destFile);
            }

            $userId = (int)($_SESSION['fie_user']['id'] ?? 0);

            foreach ($rows as $i => $row) {
                $counts['total']++;
                // Normaliser les clés (minuscules, trim)
                $r = [];
                foreach ($row as $k => $v) {
                    $r[strtolower(trim((string)$k))] = trim((string)$v);
                }

                // Champs obligatoires
                $nom    = $r['nom']    ?? '';
                $prenom = $r['prenoms'] ?? $r['prenom'] ?? '';
                $sexe   = strtoupper($r['sexe'] ?? '');
                $ddn    = $r['date_naissance'] ?? '';
                $etab   = (int)($r['code_etablissement'] ?? 0);
                $annee  = (int)($r['code_type_annee'] ?? 0);
                $secteur= (int)($r['code_type_secteur_ens'] ?? 0);
                $niveau = (int)($r['code_type_niveau'] ?? 0);

                if (strlen($nom) < 2 || strlen($prenom) < 2 || !in_array($sexe, ['M','F'], true)
                    || !$ddn || $etab <= 0 || $annee <= 0 || $secteur <= 0 || $niveau <= 0) {
                    $counts['errors']++;
                    $counts['messages'][] = "Ligne " . ($i + 2) . " ignorée : champs obligatoires manquants (nom, prenoms, sexe, date_naissance, code_etablissement, code_type_annee, code_type_secteur_ens, code_type_niveau).";
                    continue;
                }

                // Vérifier date
                $ddnTs = strtotime($ddn);
                if (!$ddnTs || $ddnTs > time()) {
                    $counts['errors']++;
                    $counts['messages'][] = "Ligne " . ($i + 2) . " ({$nom}) : date_naissance invalide.";
                    continue;
                }
                $ddn = date('Y-m-d', $ddnTs);

                try {
                    Database::beginTransaction();

                    $result = EleveModel::create([
                        'nom'                  => $nom,
                        'prenoms'              => $prenom,
                        'sexe'                 => $sexe,
                        'date_naissance'       => $ddn,
                        'lieu_naissance'       => $r['lieu_naissance'] ?: null,
                        'province_naissance'   => $r['province_naissance'] ?: null,
                        'nationalite'          => strtoupper(substr($r['nationalite'] ?? 'BDI', 0, 3)) ?: 'BDI',
                        'nom_pere'             => $r['nom_pere'] ?: null,
                        'nom_mere'             => $r['nom_mere'] ?: null,
                        'nom_tuteur'           => $r['nom_tuteur'] ?: null,
                        'telephone_tuteur'     => $r['telephone_tuteur'] ?: null,
                        'created_by'           => $userId,
                    ], $secteur, $annee);

                    InscriptionModel::create($result['id'], [
                        'code_etablissement'    => $etab,
                        'code_type_secteur_ens' => $secteur,
                        'code_type_annee'       => $annee,
                        'code_type_niveau'      => $niveau,
                        'code_type_section'     => (int)($r['code_type_section'] ?? 1),
                        'numero_classe'         => $r['numero_classe'] ?: null,
                        'date_inscription'      => $r['date_inscription'] ?: date('Y-m-d'),
                        'created_by'            => $userId,
                    ]);

                    Database::commit();
                    $counts['inserted']++;
                } catch (\Throwable $rowEx) {
                    Database::rollback();
                    $counts['errors']++;
                    $msg = $rowEx->getMessage();
                    // Doublon IUE/inscription = skipped pas error
                    if (str_contains($msg, 'Duplicate') || str_contains($msg, 'uk_eleve_annee_etab')) {
                        $counts['skipped']++;
                        $counts['errors']--;
                        $counts['messages'][] = "Ligne " . ($i + 2) . " ({$nom}) : déjà inscrit cette année dans cet établissement — ignoré.";
                    } else {
                        $counts['messages'][] = "Ligne " . ($i + 2) . " ({$nom}) : " . $msg;
                    }
                }
            }
        } catch (\Throwable $e) {
            @unlink($destFile);
            $_SESSION['fie_flash_error'] = "Erreur lors de la lecture du fichier : " . $e->getMessage();
            header('Location: ' . BASE_URL . '/admin/import-eleves');
            exit;
        }

        @unlink($destFile);

        $this->log->info("Import élèves terminé", $counts);

        $summary = sprintf(
            '%d élève(s) importé(s) avec IUE générée, %d ignoré(s) (doublon), %d erreur(s) — sur %d ligne(s) traitée(s).',
            $counts['inserted'], $counts['skipped'], $counts['errors'], $counts['total']
        );

        if ($counts['errors'] > 0 || !empty($counts['messages'])) {
            $_SESSION['fie_import_eleves_messages'] = array_slice($counts['messages'], 0, 20);
            $_SESSION['fie_flash_error']            = $summary;
        } else {
            $_SESSION['fie_flash_success'] = $summary;
        }

        header('Location: ' . BASE_URL . '/admin/import-eleves');
        exit;
    }

    /** Lit un fichier CSV (séparateur ; ou ,) et retourne tableau de lignes associatives. */
    private function readCsvRows(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if (!$handle) return [];

        // Supprimer BOM UTF-8
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            fseek($handle, 0);
        }

        // Détecter séparateur
        $firstLine = fgets($handle);
        $sep = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';
        fseek($handle, 0);
        if ($bom === "\xEF\xBB\xBF") fseek($handle, 3);

        $headers = fgetcsv($handle, 0, $sep);
        if (!$headers) { fclose($handle); return []; }
        // Nettoyer BOM dans le premier header
        $headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
        $headers = array_map('trim', $headers);

        while (($line = fgetcsv($handle, 0, $sep)) !== false) {
            if (count($line) < 2) continue;
            $row = [];
            foreach ($headers as $idx => $h) {
                $row[$h] = $line[$idx] ?? '';
            }
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    /** Valide le formulaire utilisateur. Retourne tableau d'erreurs (vide = OK). */
    private function validateUserForm(array $data, int $excludeId = 0): array
    {
        $errors = [];
        if (empty(trim($data['login'] ?? '')))      $errors['login'] = 'Le login est requis.';
        if (empty(trim($data['nom']   ?? '')))       $errors['nom']   = 'Le nom est requis.';
        if ($excludeId === 0 && empty($data['mot_de_passe'] ?? ''))
            $errors['mot_de_passe'] = 'Le mot de passe est requis pour un nouvel utilisateur.';
        if (!empty($data['mot_de_passe']) && strlen($data['mot_de_passe']) < 8)
            $errors['mot_de_passe'] = 'Le mot de passe doit faire au moins 8 caractères.';

        $rolesValides = ['super_admin','admin_central','directeur_ecole','enseignant','bibliothecaire'];
        if (!in_array($data['role'] ?? '', $rolesValides, true))
            $errors['role'] = 'Rôle invalide.';

        // Unicité du login
        if (empty($errors['login'])) {
            $existing = Database::fetchOne(
                "SELECT id FROM fie_users WHERE login = ? AND id != ?",
                [trim($data['login']), $excludeId]
            );
            if ($existing) $errors['login'] = 'Ce login est déjà utilisé.';
        }
        return $errors;
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        SecurityHelper::jsonResponse(['ok' => false, 'error' => $message]);
    }
}
