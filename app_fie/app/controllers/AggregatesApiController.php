<?php
/**
 * FIE — AggregatesApiController
 * Route interne /api/agregats (dispatche vers api/endpoints/aggregates_ws.php
 * en réutilisant la même logique via redirection interne).
 *
 * Note : pour les appels StatEduc en production, utiliser directement
 * l'endpoint brut api/endpoints/aggregates_ws.php (sans le router FIE)
 * pour éviter le surcoût du bootstrap complet.
 */
declare(strict_types=1);
namespace App\Controllers;

class AggregatesApiController
{
    public function index(): void
    {
        // Déléguer à l'endpoint API dédié
        require BASE_PATH . '/api/endpoints/aggregates_ws.php';
    }
}
