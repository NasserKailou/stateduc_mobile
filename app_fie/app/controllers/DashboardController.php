<?php
/**
 * FIE — DashboardController
 * Tableau de bord analytique.
 * CORRECTION Phase 1 :
 *   - Suppression namespace App\Controllers et use App\...
 *   - requireLogin() utilise la bonne implémentation de SecurityHelper
 */

declare(strict_types=1);

require_once FIE_SVC_PATH . 'SecurityHelper.php';
require_once FIE_CFG_PATH . 'Database.php';

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
            'total_eleves'     => (int)$db->fetchScalar("SELECT COUNT(*) FROM eleves"),
            'inscriptions_an'  => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM inscriptions WHERE statut = 'actif'"
            ),
            'etablissements'   => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM etablissements_miroir WHERE actif = 1"
            ),
            'agregats_pending' => (int)$db->fetchScalar(
                "SELECT COUNT(*) FROM agregats_eleves_age_niveau_sexe WHERE synced_to_stateduc = 0"
            ),
        ];

        // Répartition par secteur d'enseignement
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

        // Répartition par province (top 10)
        $byProvince = $db->fetchAll(
            "SELECT em.province, COUNT(DISTINCT e.id) as nb
             FROM inscriptions i
             JOIN eleves e ON e.id = i.eleve_id
             JOIN etablissements_miroir em ON em.code_etablissement = i.code_etablissement
             WHERE i.statut = 'actif'
             GROUP BY em.province ORDER BY nb DESC LIMIT 10"
        );

        $page_title  = 'Tableau de bord — FIE';
        $active_menu = 'dashboard';
        require BASE_PATH . '/app/views/dashboard/index.php';
    }
}
