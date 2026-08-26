<?php
/**
 * app_fie/app/controllers/EtablissementsApiController.php
 * GET /api/etablissements — délègue à établissements_ws.php
 */
declare(strict_types=1);

class EtablissementsApiController
{
    public function index(): void
    {
        require FIE_API_PATH . 'endpoints/etablissements_ws.php';
    }
}
