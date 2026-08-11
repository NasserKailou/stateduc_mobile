<?php
/**
 * FIE — DashboardController
 * Tableau de bord analytique (squelette — données agrégées à implémenter)
 */
declare(strict_types=1);
namespace App\Controllers;

use App\Services\SecurityHelper;
use App\Config\Database;

class DashboardController
{
    public function __construct()
    {
        SecurityHelper::requireLogin();
    }

    public function index(): void
    {
        $db = Database::getInstance();

        // KPIs rapides
        $kpis = [
            'total_eleves'      => (int)$db->fetchScalar("SELECT COUNT(*) FROM eleves"),
            'inscriptions_an'   => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM inscriptions WHERE statut = 'actif'"
            ),
            'etablissements'    => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM etablissements_miroir WHERE actif = 1"
            ),
            'agregats_pending'  => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM agregats_eleves_age_niveau_sexe WHERE synced_to_stateduc = 0"
            ),
        ];

        // Répartition par secteur
        $bySecteur = $db->fetchAll(
            "SELECT i.code_type_secteur_ens, COUNT(*) as nb
             FROM inscriptions i WHERE i.statut = 'actif'
             GROUP BY i.code_type_secteur_ens ORDER BY nb DESC"
        );

        // Répartition par sexe
        $bySexe = $db->fetchAll(
            "SELECT e.sexe, COUNT(*) as nb FROM inscriptions i
             JOIN eleves e ON e.id = i.eleve_id
             WHERE i.statut = 'actif' GROUP BY e.sexe"
        );

        $page_title  = 'Tableau de bord — FIE';
        $active_menu = 'dashboard';
        require BASE_PATH . '/app/views/dashboard/index.php';
    }
}
