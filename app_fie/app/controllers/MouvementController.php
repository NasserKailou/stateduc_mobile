<?php
/**
 * FIE — MouvementController (squelette)
 * Gère les transferts et radiations d'élèves.
 * À développer en phase 2.
 */
declare(strict_types=1);
namespace App\Controllers;

use App\Services\SecurityHelper;

class MouvementController
{
    public function __construct()
    {
        SecurityHelper::requireLogin();
    }

    public function index(): void
    {
        $page_title  = 'Mouvements d\'élèves — FIE';
        $active_menu = 'mouvement';
        require BASE_PATH . '/app/views/mouvement/index.php';
    }

    public function newForm(): void
    {
        $page_title  = 'Nouveau mouvement — FIE';
        $active_menu = 'mouvement';
        require BASE_PATH . '/app/views/mouvement/new.php';
    }

    public function processNew(): void
    {
        // TODO: Implémenter en phase 2
        $_SESSION['flash_info'] = 'Module en cours de développement.';
        header('Location: ' . BASE_URL . '/mouvement');
        exit;
    }

    public function detail(): void
    {
        $id = $_GET['id'] ?? null;
        $page_title  = 'Détail mouvement — FIE';
        $active_menu = 'mouvement';
        require BASE_PATH . '/app/views/mouvement/detail.php';
    }
}
