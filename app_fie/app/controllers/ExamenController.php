<?php
/**
 * FIE — ExamenController
 * Gère les résultats d'examens et concours liés aux IUE.
 * CORRECTION Phase 1 :
 *   - Suppression namespace App\Controllers et use App\Services\SecurityHelper
 *   - Flash messages normalisés : $_SESSION['fie_flash_*']
 *   - detail() : id lu via $_GET (pas de paramètre de signature)
 */

declare(strict_types=1);

require_once FIE_SVC_PATH . 'SecurityHelper.php';

class ExamenController
{
    public function __construct()
    {
        SecurityHelper::requireLogin();
    }

    public function index(): void
    {
        $page_title  = 'Examens — FIE';
        $active_menu = 'examen';
        require BASE_PATH . '/app/views/examen/index.php';
    }

    public function newForm(): void
    {
        $page_title  = 'Saisie de résultats — FIE';
        $active_menu = 'examen';
        require BASE_PATH . '/app/views/examen/new.php';
    }

    public function processNew(): void
    {
        // TODO : Implémenter la logique métier d'examen
        $_SESSION['fie_flash_info'] = 'Module en cours de développement.';
        header('Location: ' . BASE_URL . '/examen');
        exit;
    }

    public function detail(): void
    {
        // CORRECTION : pas de paramètre dans la signature — id lu via $_GET
        $id          = (int)($_GET['id'] ?? 0);
        $page_title  = "Résultats d'examen — FIE";
        $active_menu = 'examen';
        require BASE_PATH . '/app/views/examen/detail.php';
    }
}
