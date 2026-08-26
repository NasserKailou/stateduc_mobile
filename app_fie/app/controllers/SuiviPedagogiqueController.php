<?php
/**
 * SuiviPedagogiqueController — Suivi pédagogique + Transferts scolaires
 *
 * Routes enseignant/directeur :
 *   GET  /suivi                       — liste des élèves de ma classe (enseignant) ou école (directeur)
 *   GET  /suivi/classe/:classe_id     — liste élèves d'une classe spécifique
 *   POST /suivi/decision              — enregistrer décision fin d'année
 *   GET  /suivi/transferts            — liste demandes de transfert
 *   GET  /suivi/transfert/nouveau     — formulaire demande transfert
 *   POST /suivi/transfert/demander    — soumettre demande transfert
 *   POST /suivi/transfert/:id/traiter — approuver/rejeter (admin/directeur)
 */
class SuiviPedagogiqueController
{
    private const ROLES_ENSEIGNANT  = ['enseignant'];
    private const ROLES_DIRECTEUR   = ['directeur_ecole'];
    private const ROLES_ADMIN       = ['super_admin', 'admin_central'];
    private const ROLES_GESTION     = ['super_admin', 'admin_central', 'directeur_ecole'];

    // ──────────────────────────────────────────────────────────────────────────
    // LISTE DES ÉLÈVES — Décisions fin d'année
    // ──────────────────────────────────────────────────────────────────────────

    public function index(): void
    {
        $this->requireAuthenticated();
        $role      = SecurityHelper::userRole();
        $userId    = $_SESSION['fie_user']['id'];
        $ecolCode  = $_SESSION['fie_user']['ecole_code'] ?? null;
        $classeId  = $_SESSION['fie_user']['classe_id'] ?? null;

        // Récupérer l'année scolaire active
        $annee = Database::fetchOne("SELECT * FROM annees_scolaires WHERE actif = 1 LIMIT 1");
        $anneeLib = $annee['libelle'] ?? date('Y') . '-' . (date('Y') + 1);

        // Construire la requête selon le rôle
        if (in_array($role, self::ROLES_ADMIN)) {
            // Admin : toutes les classes de toutes les écoles
            $classes = Database::fetchAll(
                "SELECT c.*, u.nom_complet AS enseignant_nom
                 FROM classes c
                 LEFT JOIN users u ON u.id = c.enseignant_id
                 WHERE c.annee_scolaire = ?
                 ORDER BY c.ecole_code, c.niveau",
                [$anneeLib]
            ) ?: [];
        } elseif (in_array($role, self::ROLES_DIRECTEUR)) {
            // Directeur : toutes les classes de son école
            $classes = Database::fetchAll(
                "SELECT c.*, u.nom_complet AS enseignant_nom
                 FROM classes c
                 LEFT JOIN users u ON u.id = c.enseignant_id
                 WHERE c.ecole_code = ? AND c.annee_scolaire = ?
                 ORDER BY c.niveau",
                [$ecolCode, $anneeLib]
            ) ?: [];
        } else {
            // Enseignant : uniquement sa classe
            $classes = Database::fetchAll(
                "SELECT c.*, u.nom_complet AS enseignant_nom
                 FROM classes c
                 LEFT JOIN users u ON u.id = c.enseignant_id
                 WHERE c.enseignant_id = ? AND c.annee_scolaire = ?",
                [$userId, $anneeLib]
            ) ?: [];
        }

        $page_title  = 'Suivi pédagogique — FIE';
        $active_menu = 'suivi';
        require BASE_PATH . '/app/views/suivi/index.php';
    }

    /**
     * GET /suivi/classe/:classe_id — Élèves d'une classe + décisions
     */
    public function classeDetail(int $classeId): void
    {
        $this->requireAuthenticated();

        $classe = Database::fetchOne("SELECT * FROM classes WHERE id = ?", [$classeId]);
        if (!$classe) {
            $_SESSION['fie_flash_error'] = 'Classe introuvable.';
            header('Location: ' . BASE_URL . '/suivi');
            exit;
        }

        $this->checkClasseAccess($classe);

        // Élèves inscrits dans cette classe (via inscriptions)
        $eleves = Database::fetchAll(
            "SELECT e.iue, e.nom, e.prenom, e.date_naissance, e.sexe,
                    i.id AS inscription_id, i.statut AS statut_inscription,
                    sp.decision, sp.moyenne_annuelle, sp.observations, sp.id AS suivi_id
             FROM inscriptions i
             JOIN eleves e ON e.iue = i.eleve_iue
             LEFT JOIN suivi_pedagogique sp
                    ON sp.eleve_iue = e.iue
                   AND sp.classe_id = ?
                   AND sp.annee_scolaire = ?
             WHERE i.ecole_code = ?
               AND i.annee_scolaire = ?
               AND i.statut = 'inscrit'
             ORDER BY e.nom, e.prenom",
            [$classeId, $classe['annee_scolaire'], $classe['ecole_code'], $classe['annee_scolaire']]
        ) ?: [];

        $csrf = SecurityHelper::getCsrfToken();
        $page_title  = 'Classe ' . SecurityHelper::e($classe['nom_classe']) . ' — Suivi pédagogique';
        $active_menu = 'suivi';
        require BASE_PATH . '/app/views/suivi/classe.php';
    }

    /**
     * POST /suivi/decision — Enregistrer/mettre à jour la décision pour un élève
     */
    public function saveDecision(): void
    {
        $this->requireAuthenticated();

        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->jsonError('CSRF invalide', 403);
            return;
        }

        $eleveIue   = trim($_POST['eleve_iue'] ?? '');
        $classeId   = (int)($_POST['classe_id'] ?? 0);
        $decision   = $_POST['decision'] ?? 'en_attente';
        $moyenne    = !empty($_POST['moyenne']) ? (float)$_POST['moyenne'] : null;
        $obs        = trim($_POST['observations'] ?? '');

        $validDecisions = ['en_attente', 'passe', 'redouble', 'abandonne'];
        if (!in_array($decision, $validDecisions) || $eleveIue === '' || $classeId <= 0) {
            $this->jsonError('Paramètres invalides');
            return;
        }

        $classe = Database::fetchOne("SELECT * FROM classes WHERE id = ?", [$classeId]);
        if (!$classe) { $this->jsonError('Classe introuvable'); return; }

        $this->checkClasseAccess($classe);

        // Upsert suivi
        $existing = Database::fetchOne(
            "SELECT id FROM suivi_pedagogique WHERE eleve_iue = ? AND classe_id = ? AND annee_scolaire = ?",
            [$eleveIue, $classeId, $classe['annee_scolaire']]
        );

        if ($existing) {
            Database::query(
                "UPDATE suivi_pedagogique
                 SET decision = ?, moyenne_annuelle = ?, observations = ?,
                     decision_date = IF(? <> 'en_attente', CURDATE(), decision_date)
                 WHERE id = ?",
                [$decision, $moyenne, $obs ?: null, $decision, $existing['id']]
            );
        } else {
            Database::query(
                "INSERT INTO suivi_pedagogique
                 (eleve_iue, classe_id, annee_scolaire, ecole_code, decision, decision_date,
                  moyenne_annuelle, observations, cree_par)
                 VALUES (?,?,?,?,?,?,?,?,?)",
                [
                    $eleveIue, $classeId, $classe['annee_scolaire'], $classe['ecole_code'],
                    $decision, ($decision !== 'en_attente') ? date('Y-m-d') : null,
                    $moyenne, $obs ?: null,
                    $_SESSION['fie_user']['id']
                ]
            );
        }

        // Historique élève
        $this->logHistorique($eleveIue, $classe['annee_scolaire'], $classe['ecole_code'], $classeId,
            $decision === 'passe'     ? 'promotion'   :
            ($decision === 'redouble' ? 'redoublement' : 'modification'),
            "Décision de fin d'année : " . $decision . ($obs ? " — " . substr($obs, 0, 100) : '')
        );

        SecurityHelper::jsonResponse(['ok' => true, 'message' => 'Décision enregistrée.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TRANSFERTS SCOLAIRES
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /suivi/transferts — Liste des demandes de transfert
     */
    public function transfertsList(): void
    {
        $this->requireAuthenticated();
        $role     = SecurityHelper::userRole();
        $ecoCode  = $_SESSION['fie_user']['ecole_code'] ?? null;
        $userId   = $_SESSION['fie_user']['id'];

        if (in_array($role, self::ROLES_ADMIN)) {
            $transferts = Database::fetchAll(
                "SELECT t.*, e.nom, e.prenom,
                        u1.login AS demande_par_login,
                        u2.login AS traite_par_login
                 FROM transferts_scolaires t
                 JOIN eleves e ON e.iue = t.eleve_iue
                 LEFT JOIN users u1 ON u1.id = t.demande_par
                 LEFT JOIN users u2 ON u2.id = t.traite_par
                 ORDER BY t.date_demande DESC LIMIT 100"
            ) ?: [];
        } elseif (in_array($role, self::ROLES_DIRECTEUR)) {
            $transferts = Database::fetchAll(
                "SELECT t.*, e.nom, e.prenom,
                        u1.login AS demande_par_login
                 FROM transferts_scolaires t
                 JOIN eleves e ON e.iue = t.eleve_iue
                 LEFT JOIN users u1 ON u1.id = t.demande_par
                 WHERE t.ecole_source_code = ? OR t.ecole_dest_code = ?
                 ORDER BY t.date_demande DESC",
                [$ecoCode, $ecoCode]
            ) ?: [];
        } else {
            // Enseignant : ses demandes
            $transferts = Database::fetchAll(
                "SELECT t.*, e.nom, e.prenom
                 FROM transferts_scolaires t
                 JOIN eleves e ON e.iue = t.eleve_iue
                 WHERE t.demande_par = ?
                 ORDER BY t.date_demande DESC",
                [$userId]
            ) ?: [];
        }

        $csrf = SecurityHelper::getCsrfToken();
        $page_title  = 'Transferts scolaires — FIE';
        $active_menu = 'suivi';
        require BASE_PATH . '/app/views/suivi/transferts.php';
    }

    /**
     * GET /suivi/transfert/nouveau — Formulaire demande de transfert
     */
    public function transfertForm(): void
    {
        $this->requireAuthenticated();

        $eleveIue = trim($_GET['iue'] ?? '');
        $eleve    = null;
        if ($eleveIue !== '') {
            $eleve = Database::fetchOne("SELECT * FROM eleves WHERE iue = ?", [$eleveIue]);
        }

        $csrf = SecurityHelper::getCsrfToken();
        $page_title  = 'Nouvelle demande de transfert — FIE';
        $active_menu = 'suivi';
        require BASE_PATH . '/app/views/suivi/transfert_form.php';
    }

    /**
     * POST /suivi/transfert/demander — Soumettre demande transfert
     */
    public function transfertSubmit(): void
    {
        $this->requireAuthenticated();

        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['fie_flash_error'] = 'Jeton CSRF invalide.';
            header('Location: ' . BASE_URL . '/suivi/transferts');
            exit;
        }

        $eleveIue    = trim($_POST['eleve_iue'] ?? '');
        $ecoleSource = trim($_POST['ecole_source'] ?? '');
        $ecoleDest   = trim($_POST['ecole_dest'] ?? '') ?: null;
        $motif       = trim($_POST['motif'] ?? '');
        $annee       = trim($_POST['annee_scolaire'] ?? '');

        if ($eleveIue === '' || $ecoleSource === '') {
            $_SESSION['fie_flash_error'] = 'IUE élève et école source sont obligatoires.';
            header('Location: ' . BASE_URL . '/suivi/transfert/nouveau');
            exit;
        }

        Database::query(
            "INSERT INTO transferts_scolaires
             (eleve_iue, annee_scolaire, ecole_source_code, ecole_dest_code, motif_depart, demande_par)
             VALUES (?,?,?,?,?,?)",
            [$eleveIue, $annee, $ecoleSource, $ecoleDest, $motif ?: null, $_SESSION['fie_user']['id']]
        );

        // Historique
        $this->logHistorique($eleveIue, $annee, $ecoleSource, null,
            'transfert_depart', "Demande de transfert vers " . ($ecoleDest ?: 'école non spécifiée'));

        $_SESSION['fie_flash_success'] = 'Demande de transfert soumise avec succès.';
        header('Location: ' . BASE_URL . '/suivi/transferts');
        exit;
    }

    /**
     * POST /suivi/transfert/:id/traiter — Approuver ou rejeter un transfert
     */
    public function transfertTraiter(int $id): void
    {
        $this->requireAuthenticated();

        $role = SecurityHelper::userRole();
        if (!in_array($role, array_merge(self::ROLES_GESTION, self::ROLES_ADMIN))) {
            $_SESSION['fie_flash_error'] = 'Accès refusé.';
            header('Location: ' . BASE_URL . '/suivi/transferts');
            exit;
        }

        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['fie_flash_error'] = 'Jeton CSRF invalide.';
            header('Location: ' . BASE_URL . '/suivi/transferts');
            exit;
        }

        $decision = $_POST['decision'] ?? '';
        $motif    = trim($_POST['motif_decision'] ?? '');
        $ecoleDest = trim($_POST['ecole_dest'] ?? '') ?: null;

        if (!in_array($decision, ['approuve', 'rejete'])) {
            $_SESSION['fie_flash_error'] = 'Décision invalide.';
            header('Location: ' . BASE_URL . '/suivi/transferts');
            exit;
        }

        $transfert = Database::fetchOne("SELECT * FROM transferts_scolaires WHERE id = ?", [$id]);
        if (!$transfert) {
            $_SESSION['fie_flash_error'] = 'Demande introuvable.';
            header('Location: ' . BASE_URL . '/suivi/transferts');
            exit;
        }

        Database::query(
            "UPDATE transferts_scolaires
             SET statut = ?, date_decision = CURDATE(), motif_decision = ?,
                 ecole_dest_code = COALESCE(?, ecole_dest_code),
                 traite_par = ?
             WHERE id = ?",
            [$decision, $motif ?: null, $ecoleDest, $_SESSION['fie_user']['id'], $id]
        );

        // Historique
        $actionType = ($decision === 'approuve') ? 'transfert_arrivee' : 'autre';
        $this->logHistorique(
            $transfert['eleve_iue'], $transfert['annee_scolaire'],
            $transfert['ecole_source_code'], null, $actionType,
            "Transfert " . $decision . ($motif ? " — " . $motif : '')
        );

        $_SESSION['fie_flash_success'] = 'Demande de transfert ' . $decision . '.';
        header('Location: ' . BASE_URL . '/suivi/transferts');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // UTILITAIRES
    // ──────────────────────────────────────────────────────────────────────────

    private function requireAuthenticated(): void
    {
        if (!SecurityHelper::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }
    }

    private function checkClasseAccess(array $classe): void
    {
        $role    = SecurityHelper::userRole();
        $userId  = $_SESSION['fie_user']['id'];
        $ecole   = $_SESSION['fie_user']['ecole_code'] ?? null;

        if (in_array($role, self::ROLES_ADMIN)) return; // Accès total

        if (in_array($role, self::ROLES_DIRECTEUR)) {
            if ($classe['ecole_code'] !== $ecole) {
                http_response_code(403);
                echo '<p>Accès refusé : cette classe n\'appartient pas à votre école.</p>';
                exit;
            }
            return;
        }

        // Enseignant : uniquement sa propre classe
        if ((int)$classe['enseignant_id'] !== (int)$userId) {
            http_response_code(403);
            echo '<p>Accès refusé : vous n\'êtes pas l\'enseignant de cette classe.</p>';
            exit;
        }
    }

    private function jsonError(string $msg, int $code = 400): void
    {
        http_response_code($code);
        SecurityHelper::jsonResponse(['ok' => false, 'message' => $msg]);
    }

    private function logHistorique(
        string $iue, ?string $annee, string $ecole, ?int $classeId,
        string $type, string $description
    ): void {
        try {
            Database::query(
                "INSERT INTO historique_eleve
                 (eleve_iue, annee_scolaire, type_action, description, ecole_code, classe_id, effectue_par)
                 VALUES (?,?,?,?,?,?,?)",
                [$iue, $annee, $type, $description, $ecole, $classeId, $_SESSION['fie_user']['id'] ?? null]
            );
        } catch (Throwable $e) {
            // Non bloquant
        }
    }
}
