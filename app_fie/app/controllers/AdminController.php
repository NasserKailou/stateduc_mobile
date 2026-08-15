<?php
/**
 * FIE — AdminController
 * Tableau d'administration : synchronisation API/Excel, gestion utilisateurs, journal d'audit.
 * CORRECTION Phase 1 :
 *   - Suppression namespace App\Controllers et tous les use App\...
 *   - requireRole() appelé avec rôles valides du schéma
 *   - Colonne `login` et `last_login_at` conformes au schéma SQL
 *   - Flash messages normalisés : $_SESSION['fie_flash_*']
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
        $db = Database::getInstance();

        $stats = [
            'etablissements' => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM etablissements_miroir WHERE actif = 1"
            ),
            'eleves'         => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM eleves"
            ),
            'inscriptions'   => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM inscriptions WHERE statut = 'actif'"
            ),
            'doublons'       => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM eleves WHERE doublon_suspect = 1"
            ),
        ];

        $lastSync = $db->fetchOne(
            "SELECT * FROM sync_log ORDER BY created_at DESC LIMIT 1"
        );

        $pendingAggregats = (int)$db->fetchScalar(
            "SELECT COUNT(*) FROM agregats_eleves_age_niveau_sexe WHERE synced_to_stateduc = 0"
        );

        $page_title  = 'Administration — FIE';
        $active_menu = 'admin';
        require BASE_PATH . '/app/views/admin/index.php';
    }

    /* ── GET /admin/sync ─────────────────────────────────────────────────── */

    public function syncStatus(): void
    {
        $db = Database::getInstance();

        $logs = $db->fetchAll(
            "SELECT * FROM sync_log ORDER BY created_at DESC LIMIT 20"
        );

        $lastSuccess = $db->fetchOne(
            "SELECT * FROM sync_log WHERE statut = 'succes' ORDER BY created_at DESC LIMIT 1"
        );

        $etablissementsCount = (int)$db->fetchScalar(
            "SELECT COUNT(*) FROM etablissements_miroir"
        );

        $bySource = $db->fetchAll(
            "SELECT source, COUNT(*) as nb FROM etablissements_miroir GROUP BY source"
        );

        $page_title  = 'Synchronisation — Administration FIE';
        $active_menu = 'admin';
        require BASE_PATH . '/app/views/admin/sync.php';
    }

    /* ── POST /admin/sync/lancer ─────────────────────────────────────────── */

    public function triggerSync(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->jsonError('Jeton CSRF invalide', 403);
            return;
        }

        $mode     = $_POST['mode']     ?? 'full';
        $perPage  = (int)($_POST['per_page'] ?? 100);
        $province = $_POST['province'] ?? null;

        $sync   = new SyncService();
        $params = [];
        if ($province)               $params['province']    = $province;
        if ($mode === 'incremental') $params['incremental'] = true;

        $this->log->info("Lancement synchronisation manuelle", [
            'user'     => $_SESSION['fie_user']['username'] ?? 'unknown',
            'mode'     => $mode,
            'province' => $province,
        ]);

        $result = $sync->syncFromApi($params, $perPage);

        SecurityHelper::jsonResponse([
            'ok'      => $result['ok'],
            'message' => $result['ok']
                ? "Synchronisation terminée : {$result['inserted']} insérés, {$result['updated']} mis à jour."
                : "Synchronisation échouée : " . ($result['error'] ?? 'erreur inconnue'),
            'data'    => $result,
        ]);
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
        $db = Database::getInstance();
        // CORRECTION : colonnes conformes au schéma (login, not username; last_login_at, not derniere_connexion)
        $users = $db->fetchAll(
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
        $db = Database::getInstance();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $total = (int)$db->fetchScalar("SELECT COUNT(*) FROM audit_log");
        $pages = max(1, (int)ceil($total / $perPage));

        // CORRECTION : colonne `login` pas `username` dans la jointure
        $logs = $db->fetchAll(
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

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        SecurityHelper::jsonResponse(['ok' => false, 'error' => $message]);
    }
}
