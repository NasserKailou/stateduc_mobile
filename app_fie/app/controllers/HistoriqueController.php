<?php
/**
 * FIE — HistoriqueController
 * Affiche le journal complet de tous les événements d'un élève identifié par son IUE.
 * Données issues de la table `historique_eleve` (migration 002_nouveaux_modules.sql).
 *
 * Routes :
 *   GET /eleve/:iue/historique   → eleve()
 *
 * Accès :
 *   - super_admin, admin_central : tous les élèves
 *   - directeur_ecole : élèves de son école uniquement
 *   - enseignant      : élèves de sa classe uniquement
 */

declare(strict_types=1);

require_once FIE_SERVICES_PATH . 'SecurityHelper.php';
require_once FIE_CONFIG_PATH   . 'Database.php';

class HistoriqueController
{
    /** Rôles autorisés à consulter l'historique */
    private const ROLES_ALLOWED = [
        'super_admin', 'admin_central', 'directeur_ecole', 'enseignant',
    ];

    public function __construct()
    {
        SecurityHelper::requireRole(self::ROLES_ALLOWED);
    }

    /* ── GET /eleve/:iue/historique ───────────────────────────────────────── */

    public function eleve(): void
    {
        $iue  = trim($_GET['iue'] ?? '');
        $role = SecurityHelper::userRole();

        if ($iue === '') {
            http_response_code(400);
            $_SESSION['fie_flash_error'] = 'IUE manquant.';
            header('Location: ' . BASE_URL . '/inscription/recherche');
            exit;
        }

        // ── Récupérer l'élève ─────────────────────────────────────────────
        $eleve = Database::fetchOne(
            "SELECT e.*, i.code_etablissement, i.annee_scolaire, i.classe_nom,
                    et.nom_etablissement
             FROM eleves e
             LEFT JOIN inscriptions i ON i.iue = e.iue AND i.statut = 'inscrit'
             LEFT JOIN etablissements_miroir et ON et.code_etablissement = i.code_etablissement
             WHERE e.iue = ?
             LIMIT 1",
            [$iue]
        );

        if (!$eleve) {
            $_SESSION['fie_flash_error'] = "Élève IUE « $iue » introuvable.";
            header('Location: ' . BASE_URL . '/inscription/recherche');
            exit;
        }

        // ── Contrôle d'accès par périmètre ────────────────────────────────
        if ($role === 'directeur_ecole') {
            $ecoleUser = $_SESSION['fie_user']['ecole_code'] ?? '';
            if ($ecoleUser && ($eleve['code_etablissement'] ?? '') !== $ecoleUser) {
                http_response_code(403);
                $_SESSION['fie_flash_error'] = 'Accès refusé : cet élève n\'est pas dans votre école.';
                header('Location: ' . BASE_URL . '/inscription/recherche');
                exit;
            }
        }

        if ($role === 'enseignant') {
            $classeUser = (int)($_SESSION['fie_user']['classe_id'] ?? 0);
            $classeNom  = $_SESSION['fie_user']['classe_nom']  ?? '';
            // Vérifier que l'élève est dans la classe de l'enseignant
            $inClasse = Database::fetchOne(
                "SELECT id FROM inscriptions
                 WHERE iue = ? AND classe_nom = ? AND statut = 'inscrit' LIMIT 1",
                [$iue, $classeNom]
            );
            if (!$inClasse) {
                http_response_code(403);
                $_SESSION['fie_flash_error'] = 'Accès refusé : cet élève n\'est pas dans votre classe.';
                header('Location: ' . BASE_URL . '/suivi');
                exit;
            }
        }

        // ── Récupérer l'historique ─────────────────────────────────────────
        $historique = Database::fetchAll(
            "SELECT h.*,
                    COALESCE(h.ecole_code_dest, h.ecole_code_source, '') AS ecole_ref,
                    et.nom_etablissement AS nom_ecole
             FROM historique_eleve h
             LEFT JOIN etablissements_miroir et
                    ON et.code_etablissement = COALESCE(h.ecole_code_dest, h.ecole_code_source)
             WHERE h.iue = ?
             ORDER BY h.date_evenement DESC, h.id DESC",
            [$iue]
        );

        // ── Grouper par année scolaire pour la frise ──────────────────────
        $byAnnee = [];
        foreach ($historique as $evt) {
            $annee = $evt['annee_scolaire'] ?? 'Indéterminé';
            $byAnnee[$annee][] = $evt;
        }

        // ── Libellés types d'événements ───────────────────────────────────
        $typeLabels = [
            'inscription'       => ['Inscription initiale',    'fa-user-plus',        'primary'],
            'reinscription'     => ['Réinscription',           'fa-rotate-right',     'info'],
            'transfert_depart'  => ['Transfert (départ)',      'fa-arrow-right-from-bracket','warning'],
            'transfert_arrivee' => ['Transfert (arrivée)',     'fa-arrow-right-to-bracket', 'success'],
            'promotion'         => ['Promotion (passé)',       'fa-arrow-up',         'success'],
            'redoublement'      => ['Redoublement',            'fa-rotate-left',      'warning'],
            'abandon'           => ['Abandon scolaire',        'fa-user-xmark',       'danger'],
            'examen'            => ['Résultat examen',         'fa-graduation-cap',   'purple'],
            'iue_emis'          => ['IUE émis',                'fa-id-card',          'primary'],
            'modification'      => ['Modification dossier',    'fa-pen-to-square',    'secondary'],
        ];

        $page_title  = 'Historique élève — ' . htmlspecialchars(
            trim(($eleve['nom'] ?? '') . ' ' . ($eleve['prenom'] ?? '')), ENT_QUOTES, 'UTF-8'
        );
        $active_menu = 'recherche';

        require BASE_PATH . '/app/views/historique/eleve.php';
    }
}
