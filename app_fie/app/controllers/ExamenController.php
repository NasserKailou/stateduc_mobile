<?php
/**
 * FIE — ExamenController (squelette)
 * Gère les résultats d'examens et de concours liés aux IUE.
 * À développer en phase 3.
 */
declare(strict_types=1);
namespace App\Controllers;

use App\Services\SecurityHelper;

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
        // TODO: Implémenter en phase 3
        $_SESSION['flash_info'] = 'Module en cours de développement.';
        header('Location: ' . BASE_URL . '/examen');
        exit;
    }

    public function detail(): void
    {
        $page_title  = 'Résultats d\'examen — FIE';
        $active_menu = 'examen';
        require BASE_PATH . '/app/views/examen/detail.php';
    }
}
