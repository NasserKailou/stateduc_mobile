<?php
/**
 * FIE — DashboardController
 * Tableau de bord analytique.
 * CORRECTION Phase 1 :
 *   - Suppression namespace App\Controllers et use App\...
 *   - requireLogin() utilise la bonne implémentation de SecurityHelper
 * CORRECTION Phase 2 :
 *   - Database::fetchScalar / fetchAll / fetchOne sont des méthodes STATIQUES
 * CORRECTION Phase 3 :
 *   - Graceful fallback sur chaque requête (tables peuvent ne pas exister)
 *   - Requêtes supplémentaires : inscriptions_mois, doublons_suspects, transferts_en_attente
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
        // ── Helper : exécuter une requête avec fallback silencieux ────────────
        $safe = static function (callable $fn, mixed $default = null): mixed {
            try {
                return $fn();
            } catch (Throwable $e) {
                error_log('[FIE/Dashboard] ' . $e->getMessage());
                return $default;
            }
        };

        // ── KPIs principaux ───────────────────────────────────────────────────
        $kpis = [
            'total_eleves'      => (int)$safe(fn() => Database::fetchScalar("SELECT COUNT(*) FROM eleves"), 0),
            'inscriptions_an'   => (int)$safe(fn() => Database::fetchScalar(
                "SELECT COUNT(*) FROM inscriptions WHERE statut = 'inscrit'"
            ), 0),
            'etablissements'    => (int)$safe(fn() => Database::fetchScalar(
                "SELECT COUNT(*) FROM etablissements_miroir WHERE actif = 1"
            ), 0),
            'agregats_pending'  => (int)$safe(fn() => Database::fetchScalar(
                "SELECT COUNT(*) FROM agregats_eleves_age_niveau_sexe WHERE synced_to_stateduc = 0"
            ), 0),
            // Nouvelles inscriptions du mois en cours
            'inscriptions_mois' => (int)$safe(fn() => Database::fetchScalar(
                "SELECT COUNT(*) FROM inscriptions WHERE statut = 'inscrit'
                 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
            ), 0),
            // Doublons suspects
            'doublons'          => (int)$safe(fn() => Database::fetchScalar(
                "SELECT COUNT(*) FROM eleves WHERE doublon_suspect = 1"
            ), 0),
            // Transferts en attente (si table existe)
            'transferts_attente' => (int)$safe(fn() => Database::fetchScalar(
                "SELECT COUNT(*) FROM transferts_scolaires WHERE statut = 'en_attente'"
            ), 0),
            // Documents bibliothèque publiés
            'biblio_docs'       => (int)$safe(fn() => Database::fetchScalar(
                "SELECT COUNT(*) FROM bibliotheque_documents WHERE statut = 'publie'"
            ), 0),
        ];

        // ── Répartition par secteur d'enseignement ────────────────────────────
        $bySecteur = $safe(fn() => Database::fetchAll(
            "SELECT i.code_type_secteur_ens, COUNT(*) as nb
             FROM inscriptions i WHERE i.statut = 'inscrit'
             GROUP BY i.code_type_secteur_ens ORDER BY nb DESC"
        ), []) ?: [];

        // ── Répartition par sexe ──────────────────────────────────────────────
        $bySexe = $safe(fn() => Database::fetchAll(
            "SELECT e.sexe, COUNT(*) as nb FROM inscriptions i
             JOIN eleves e ON e.id = i.eleve_id
             WHERE i.statut = 'inscrit' GROUP BY e.sexe ORDER BY e.sexe"
        ), []) ?: [];

        // ── Répartition par province (top 18) ─────────────────────────────────
        $byProvince = $safe(fn() => Database::fetchAll(
            "SELECT em.province, COUNT(DISTINCT e.id) as nb
             FROM inscriptions i
             JOIN eleves e ON e.id = i.eleve_id
             JOIN etablissements_miroir em ON em.code_etablissement = i.code_etablissement
             WHERE i.statut = 'inscrit' AND em.province IS NOT NULL AND em.province <> ''
             GROUP BY em.province ORDER BY nb DESC LIMIT 18"
        ), []) ?: [];

        // ── Dernières inscriptions (15 dernières) ─────────────────────────────
        $lastInscrits = $safe(fn() => Database::fetchAll(
            "SELECT e.nom, e.prenoms, e.iue, e.sexe, i.code_etablissement,
                    em.nom_etablissement, em.province, i.created_at
             FROM inscriptions i
             JOIN eleves e ON e.id = i.eleve_id
             LEFT JOIN etablissements_miroir em ON em.code_etablissement = i.code_etablissement
             WHERE i.statut = 'inscrit'
             ORDER BY i.created_at DESC LIMIT 15"
        ), []) ?: [];

        // ── Évolution des inscriptions par mois (12 derniers mois) ────────────
        $byMois = $safe(fn() => Database::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mois,
                    COUNT(*) AS nb
             FROM inscriptions
             WHERE statut = 'inscrit'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY mois ORDER BY mois ASC"
        ), []) ?: [];

        // ── Répartition par niveau d'enseignement ─────────────────────────────
        $byNiveau = $safe(fn() => Database::fetchAll(
            "SELECT i.code_type_niveau, COUNT(*) as nb
             FROM inscriptions i
             WHERE i.statut = 'inscrit' AND i.code_type_niveau IS NOT NULL
             GROUP BY i.code_type_niveau ORDER BY nb DESC LIMIT 12"
        ), []) ?: [];

        // ── Répartition par nationalité ────────────────────────────────────────
        $byNationalite = $safe(fn() => Database::fetchAll(
            "SELECT COALESCE(e.nationalite, 'Non renseignée') as nationalite, COUNT(*) as nb
             FROM eleves e
             GROUP BY e.nationalite ORDER BY nb DESC LIMIT 8"
        ), []) ?: [];

        // ── Top 10 établissements ──────────────────────────────────────────────
        $topEtabs = $safe(fn() => Database::fetchAll(
            "SELECT em.nom_etablissement, em.province, COUNT(DISTINCT e.id) as nb
             FROM inscriptions i
             JOIN eleves e ON e.id = i.eleve_id
             JOIN etablissements_miroir em ON em.code_etablissement = i.code_etablissement
             WHERE i.statut = 'inscrit'
             GROUP BY i.code_etablissement ORDER BY nb DESC LIMIT 10"
        ), []) ?: [];

        $page_title     = 'Tableau de bord — FIE';
        $active_menu    = 'dashboard';
        $app_breadcrumb = [['label' => 'Tableau de bord', 'url' => '']];
        require BASE_PATH . '/app/views/dashboard/index.php';
    }
}
