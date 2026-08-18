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
        $active_menu = 'admin';
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
        $active_menu = 'admin';
        require BASE_PATH . '/app/views/admin/sync.php';
    }

    /* ── POST /admin/sync/lancer ─────────────────────────────────────────── */

    public function triggerSync(): void
    {
        // Garantir que la réponse est toujours JSON même en cas d'exception PHP
        header('Content-Type: application/json; charset=utf-8');

        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Jeton CSRF invalide', 403);
            return;
        }

        $mode     = $_POST['mode']     ?? 'full';
        $province = $_POST['province'] ?? null;

        $this->log->info("Lancement synchronisation manuelle", [
            'user'     => $_SESSION['fie_user']['login'] ?? 'unknown',
            'mode'     => $mode,
            'province' => $province,
        ]);

        try {
            $sync = new SyncService();

            // syncFromApi(?string $updatedSince, ?int $secteur, ?string $province, ?string $triggeredBy)
            $updatedSince = ($mode === 'incremental') ? date('Y-m-d', strtotime('-7 days')) : null;
            $user         = $_SESSION['fie_user']['login'] ?? 'admin';

            $result = $sync->syncFromApi(
                updatedSince: $updatedSince,
                province:     $province ?: null,
                triggeredBy:  $user
            );

            SecurityHelper::jsonResponse([
                'ok'      => true,
                'message' => "Synchronisation terminée : {$result['inserted']} insérés, {$result['updated']} mis à jour, {$result['errors']} erreurs.",
                'data'    => $result,
            ]);

        } catch (Throwable $e) {
            $this->log->error("triggerSync échoué : " . $e->getMessage());
            // Réponse JSON propre — jamais de HTML
            http_response_code(200); // 200 pour que fetch() .then(r=>r.json()) fonctionne
            echo json_encode([
                'ok'    => false,
                'error' => $e->getMessage(),
                'message' => 'Synchronisation échouée : ' . $e->getMessage(),
            ]);
            exit;
        }
    }

    /* ── GET /admin/import-excel ─────────────────────────────────────────── */

    public function importExcelForm(): void
    {
        $page_title  = 'Import Excel — Administration FIE';
        $active_menu = 'admin';
        require BASE_PATH . '/app/views/admin/import_excel.php';
    }

    /* ── POST /admin/import-excel ────────────────────────────────────────── */

    public function processExcelImport(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
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
        $result = $sync->importFromExcel($destFile);

        @unlink($destFile);

        if ($result['ok']) {
            $_SESSION['fie_flash_success'] = sprintf(
                'Import terminé : %d insérés, %d mis à jour, %d ignorés.',
                $result['inserted'] ?? 0,
                $result['updated']  ?? 0,
                $result['skipped']  ?? 0
            );
        } else {
            $_SESSION['fie_flash_error'] = "Erreur lors de l'import : " . ($result['error'] ?? 'inconnue');
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
        $active_menu = 'admin';
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
        $active_menu = 'admin';
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
            "SELECT id, nom_classe, code_etablissement, annee_scolaire
             FROM classes ORDER BY annee_scolaire DESC, nom_classe LIMIT 500"
        );
        $errors  = [];
        $old     = [];
        $page_title  = 'Nouvel utilisateur — Admin FIE';
        $active_menu = 'admin';
        require BASE_PATH . '/app/views/admin/user_form.php';
    }

    /* ── POST /admin/users/nouveau ──────────────────────────────────────── */

    public function userCreate(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
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
                "SELECT id, nom_classe, code_etablissement, annee_scolaire
                 FROM classes ORDER BY annee_scolaire DESC, nom_classe LIMIT 500"
            );
            $old     = $_POST;
            $page_title  = 'Nouvel utilisateur — Admin FIE';
            $active_menu = 'admin';
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
            "SELECT id, nom_classe, code_etablissement, annee_scolaire
             FROM classes ORDER BY annee_scolaire DESC, nom_classe LIMIT 500"
        );
        $errors  = [];
        $old     = $user; // pré-remplissage
        $editMode = true;
        $page_title  = 'Modifier utilisateur — Admin FIE';
        $active_menu = 'admin';
        require BASE_PATH . '/app/views/admin/user_form.php';
    }

    /* ── POST /admin/users/:id/editer ──────────────────────────────────── */

    public function userUpdate(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
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
                "SELECT id, nom_classe, code_etablissement, annee_scolaire
                 FROM classes ORDER BY annee_scolaire DESC, nom_classe LIMIT 500"
            );
            $old = $_POST; $old['id'] = $id;
            $editMode = true;
            $page_title = 'Modifier utilisateur — Admin FIE';
            $active_menu = 'admin';
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
        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
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
