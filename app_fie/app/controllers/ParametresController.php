<?php
/**
 * app_fie/app/controllers/ParametresController.php
 * Administration — Paramétrage URL StatEduc + token API
 * Routes : GET /admin/parametres  →  index()
 *          POST /admin/parametres →  save()
 */
declare(strict_types=1);

// Les classes sont chargées par l'autoloader de config.php (spl_autoload_register)
// FIE_SVC_PATH et FIE_CFG_PATH n'existent pas — les vraies constantes sont
// FIE_SERVICES_PATH et FIE_CONFIG_PATH, mais l'autoloader suffit ici.

class ParametresController
{
    public function __construct()
    {
        SecurityHelper::requireRole(['super_admin', 'admin_central']);
    }

    // ── GET /admin/parametres ─────────────────────────────────────────────────
    public function index(): void
    {
        $settings = $this->loadSettings();
        $testResult = null;
        $page_title  = 'Paramétrage StatEduc — FIE';
        $active_menu = 'admin';
        require BASE_PATH . '/app/views/admin/parametres.php';
    }

    // ── POST /admin/parametres ────────────────────────────────────────────────
    public function save(): void
    {
        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['fie_flash_error'] = 'Jeton CSRF invalide.';
            header('Location: ' . BASE_URL . '/admin/parametres');
            exit;
        }

        $action = $_POST['action'] ?? 'save';

        // ── Test de connexion ──────────────────────────────────────────────
        if ($action === 'test') {
            $url   = trim($_POST['stateduc_url']       ?? '');
            $token = trim($_POST['stateduc_api_token'] ?? '');
            $client = new StatEducClient($url, $token, 8);
            $ok     = $client->ping();

            $settings    = $this->loadSettings();
            $testResult  = ['ok' => $ok, 'url' => $url];
            $page_title  = 'Paramétrage StatEduc — FIE';
            $active_menu = 'admin';
            require BASE_PATH . '/app/views/admin/parametres.php';
            return;
        }

        // ── Sauvegarde ────────────────────────────────────────────────────
        $map = [
            'stateduc_url'                  => trim($_POST['stateduc_url']                  ?? ''),
            'stateduc_api_token'            => trim($_POST['stateduc_api_token']            ?? ''),
            'stateduc_sync_enabled'         => isset($_POST['stateduc_sync_enabled']) ? '1' : '0',
            'stateduc_sync_interval_minutes'=> (string)(int)($_POST['stateduc_sync_interval_minutes'] ?? 60),
            'fie_api_token'                 => trim($_POST['fie_api_token']                 ?? ''),
            'fie_api_enabled'               => isset($_POST['fie_api_enabled']) ? '1' : '0',
        ];

        foreach ($map as $cle => $valeur) {
            Database::query(
                "INSERT INTO fie_settings (cle, valeur) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE valeur = VALUES(valeur), modifie_le = NOW()",
                [$cle, $valeur]
            );
        }

        $_SESSION['fie_flash_success'] = 'Paramètres enregistrés avec succès.';
        header('Location: ' . BASE_URL . '/admin/parametres');
        exit;
    }

    // ── Chargement des paramètres depuis fie_settings ─────────────────────────
    private function loadSettings(): array
    {
        $defaults = [
            'stateduc_url'                   => defined('STATEDUC_API_BASE_URL') ? STATEDUC_API_BASE_URL : '',
            'stateduc_api_token'             => '',
            'stateduc_sync_enabled'          => '1',
            'stateduc_sync_interval_minutes' => '60',
            'fie_api_token'                  => '',
            'fie_api_enabled'                => '1',
        ];

        try {
            $rows = Database::fetchAll("SELECT cle, valeur FROM fie_settings");
            foreach ($rows as $row) {
                $defaults[$row['cle']] = $row['valeur'];
            }
        } catch (\Throwable $e) {
            // Table absente avant migration — garder les defaults
        }

        return $defaults;
    }
}
