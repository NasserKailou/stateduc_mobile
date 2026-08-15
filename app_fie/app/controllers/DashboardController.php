<?php
/**
 * FIE — DashboardController
 * Tableau de bord analytique.
 * CORRECTION Phase 1 :
 *   - Suppression namespace App\Controllers et use App\...
 *   - requireLogin() utilise la bonne implémentation de SecurityHelper
 * CORRECTION Phase 2 :
 *   - Database::getInstance() retourne un objet PDO brut (pas de wrapper).
 *   - fetchScalar / fetchAll / fetchOne sont des méthodes STATIQUES de la
 *     classe Database, pas des méthodes d'instance PDO.
 *   - Suppression de $db = Database::getInstance() ; utilisation de la
 *     syntaxe statique Database::fetchScalar(...) partout.
 */

declare(strict_types=1);

require_once FIE_SERVICES_PATH . 'SecurityHelper.php';
require_once FIE_CONFIG_PATH   . 'Database.php';

class DashboardController
{
    public function __construct()
    {
        SecurityHelper::requireLogin();
    }

    public function index(): void
    {
        // KPIs rapides
        $kpis = [
            'total_eleves'     => (int)Database::fetchScalar("SELECT COUNT(*) FROM eleves"),
            'inscriptions_an'  => (int)Database::fetchScalar(
                "SELECT COUNT(*) FROM inscriptions WHERE statut = 'actif'"
            ),
            'etablissements'   => (int)Database::fetchScalar(
                "SELECT COUNT(*) FROM etablissements_miroir WHERE actif = 1"
            ),
            'agregats_pending' => (int)Database::fetchScalar(
                "SELECT COUNT(*) FROM agregats_eleves_age_niveau_sexe WHERE synced_to_stateduc = 0"
            ),
        ];

        // Répartition par secteur d'enseignement
        $bySecteur = Database::fetchAll(
            "SELECT i.code_type_secteur_ens, COUNT(*) as nb
             FROM inscriptions i WHERE i.statut = 'actif'
             GROUP BY i.code_type_secteur_ens ORDER BY nb DESC"
        );

        // Répartition par sexe
        $bySexe = Database::fetchAll(
            "SELECT e.sexe, COUNT(*) as nb FROM inscriptions i
             JOIN eleves e ON e.id = i.eleve_id
             WHERE i.statut = 'actif' GROUP BY e.sexe"
        );

        // Répartition par province (top 10)
        $byProvince = Database::fetchAll(
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
