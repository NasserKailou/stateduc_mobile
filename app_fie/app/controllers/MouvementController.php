<?php
/**
 * FIE — MouvementController
 * Gère les transferts et radiations d'élèves.
 * CORRECTION Phase 1 :
 *   - Suppression namespace App\Controllers et use App\Services\SecurityHelper
 *   - Flash messages normalisés : $_SESSION['fie_flash_*']
 *   - detail() : id lu depuis $_GET (Router n'injecte pas de paramètre)
 */

declare(strict_types=1);

require_once FIE_SVC_PATH . 'SecurityHelper.php';

class MouvementController
{
    public function __construct()
    {
        SecurityHelper::requireLogin();
    }

    public function index(): void
    {
        $page_title  = "Mouvements d'élèves — FIE";
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
        // TODO : Implémenter la logique métier de mouvement
        $_SESSION['fie_flash_info'] = 'Module en cours de développement.';
        header('Location: ' . BASE_URL . '/mouvement');
        exit;
    }

    public function detail(): void
    {
        // CORRECTION : pas de paramètre dans la signature — id lu via $_GET
        $id          = (int)($_GET['id'] ?? 0);
        $page_title  = 'Détail mouvement — FIE';
        $active_menu = 'mouvement';
        require BASE_PATH . '/app/views/mouvement/detail.php';
    }
}
