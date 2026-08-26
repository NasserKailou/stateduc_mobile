<?php
/**
 * FIE — AggregatesApiController
 * Route interne /api/agregats — délègue à api/endpoints/aggregates_ws.php.
 * CORRECTION Phase 1 :
 *   - Suppression namespace App\Controllers
 *   - BASE_PATH utilisé pour le require (défini dans public/index.php)
 */

declare(strict_types=1);

class AggregatesApiController
{
    public function index(): void
    {
        // Déléguer à l'endpoint API dédié
        require BASE_PATH . '/api/endpoints/aggregates_ws.php';
    }
}
