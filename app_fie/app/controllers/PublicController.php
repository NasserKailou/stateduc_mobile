<?php
/**
 * FIE — PublicController
 * Site public accessible sans authentification.
 * CORRECTION Phase 1 :
 *   - Suppression namespace App\Controllers
 *   - Pas de require de SecurityHelper (non nécessaire ici — pages publiques)
 *   - BASE_PATH utilisé pour les require (défini dans index.php)
 */

declare(strict_types=1);

class PublicController
{
    public function home(): void
    {
        $page_title  = 'FIE — Fichier Informatisé des Élèves du Burundi';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/home.php';
    }

    public function aide(): void
    {
        $page_title  = 'Aide — FIE';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/aide.php';
    }

    public function contact(): void
    {
        $page_title  = 'Contact — FIE';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/contact.php';
    }

    public function confidentialite(): void
    {
        $page_title  = 'Politique de confidentialité — FIE';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/confidentialite.php';
    }

    public function mentions(): void
    {
        $page_title  = 'Mentions légales — FIE';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/mentions.php';
    }

    /**
     * Profil public d'un élève — accessible via QR code sans connexion.
     * Affiche uniquement les données non-sensibles : IUE, nom, prénoms, sexe,
     * établissement actif. Ne montre PAS la date de naissance complète ni adresse.
     * Route : GET /eleve/:iue
     */
    public function elevePublic(): void
    {
        $iue = strtoupper(trim($_GET['iue'] ?? ''));

        // Validation format IUE (ex: BI-2024-XXXXXXXX)
        if (empty($iue) || !preg_match('/^[A-Z0-9\-]{4,30}$/', $iue)) {
            http_response_code(404);
            $page_title = 'IUE introuvable — FIE';
            require BASE_PATH . '/app/views/public_site/eleve_public_404.php';
            return;
        }

        // Récupérer uniquement les infos non-sensibles
        $safe = static function (callable $fn, mixed $default = null): mixed {
            try { return $fn(); } catch (\Throwable $e) { return $default; }
        };

        $eleve = $safe(fn() => Database::fetchRow(
            "SELECT iue, nom, prenoms, sexe, annee_naissance,
                    code_type_nationalite
             FROM eleves WHERE iue = ? LIMIT 1",
            [$iue]
        ));

        if (!$eleve) {
            http_response_code(404);
            $page_title = 'IUE introuvable — FIE';
            require BASE_PATH . '/app/views/public_site/eleve_public_404.php';
            return;
        }

        // Inscription active
        $inscription = $safe(fn() => Database::fetchRow(
            "SELECT i.code_type_annee, i.statut,
                    em.nom_etablissement, em.province, em.commune
             FROM inscriptions i
             LEFT JOIN etablissements_miroir em ON em.code_etablissement = i.code_etablissement
             WHERE i.eleve_id = (SELECT id FROM eleves WHERE iue = ? LIMIT 1)
               AND i.statut = 'inscrit'
             ORDER BY i.code_type_annee DESC LIMIT 1",
            [$iue]
        ));

        $page_title  = 'Profil élève — ' . htmlspecialchars($iue, ENT_QUOTES, 'UTF-8') . ' — FIE Burundi';
        $active_menu = '';
        require BASE_PATH . '/app/views/public_site/eleve_public.php';
    }
}
