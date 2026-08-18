<?php
/**
 * BibliothequeController — Mini-bibliothèque publique FIE
 *
 * Routes publiques  : GET /bibliotheque, GET /bibliotheque/:id/telecharger
 * Routes admin      : GET/POST /bibliotheque/admin (liste + publication)
 *                     GET /bibliotheque/admin/nouveau
 *                     POST /bibliotheque/admin/publier
 *                     GET /bibliotheque/admin/:id/supprimer
 *
 * Profils autorisés pour l'admin : super_admin, admin_central, bibliothecaire
 */
class BibliothequeController
{
    private const ROLES_ADMIN = ['super_admin', 'admin_central', 'bibliothecaire'];

    // ──────────────────────────────────────────────────────────────────────────
    // PARTIE PUBLIQUE
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /bibliotheque — Liste publique des documents
     */
    public function index(): void
    {
        $search      = trim($_GET['q'] ?? '');
        $thematique  = (int)($_GET['thematique'] ?? 0);
        $niveau      = trim($_GET['niveau'] ?? '');

        // Charger les thématiques
        $thematiques = Database::fetchAll(
            "SELECT * FROM bibliotheque_thematiques WHERE actif = 1 ORDER BY ordre ASC, libelle ASC"
        ) ?: [];

        // Requête documents publics et publiés
        $params = ['publie'];
        $where  = "d.statut = ? AND d.public = 1";

        if ($search !== '') {
            $where   .= " AND MATCH(d.titre, d.description) AGAINST(? IN BOOLEAN MODE)";
            $params[] = $search . '*';
        }
        if ($thematique > 0) {
            $where   .= " AND d.thematique_id = ?";
            $params[] = $thematique;
        }
        if ($niveau !== '') {
            $where   .= " AND d.niveau_scolaire LIKE ?";
            $params[] = '%' . $niveau . '%';
        }

        $documents = Database::fetchAll(
            "SELECT d.*, t.libelle AS thematique_libelle, t.icone_fa, t.couleur
             FROM bibliotheque_documents d
             JOIN bibliotheque_thematiques t ON t.id = d.thematique_id
             WHERE {$where}
             ORDER BY d.publie_le DESC
             LIMIT 60",
            $params
        ) ?: [];

        $page_title  = 'Bibliothèque — FIE';
        $active_menu = 'bibliotheque';
        require BASE_PATH . '/app/views/bibliotheque/index.php';
    }

    /**
     * GET /bibliotheque/:id/telecharger — Téléchargement avec compteur
     */
    public function telecharger(int $id): void
    {
        $doc = Database::fetchOne(
            "SELECT * FROM bibliotheque_documents WHERE id = ? AND statut = 'publie' AND public = 1",
            [$id]
        );

        if (!$doc) {
            http_response_code(404);
            echo '<p style="font-family:sans-serif;padding:2rem">Document introuvable ou non disponible.</p>';
            return;
        }

        $filePath = BASE_PATH . '/' . $doc['chemin_fichier'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo '<p style="font-family:sans-serif;padding:2rem">Fichier introuvable sur le serveur.</p>';
            return;
        }

        // Incrémenter le compteur
        Database::query(
            "UPDATE bibliotheque_documents SET telechargements = telechargements + 1 WHERE id = ?",
            [$id]
        );

        // Historique si connecté
        if (SecurityHelper::isLoggedIn()) {
            // pas d'historique élève ici — c'est un document public
        }

        // Envoi du fichier
        header('Content-Type: ' . $doc['type_mime']);
        header('Content-Disposition: attachment; filename="' . $doc['nom_fichier'] . '"');
        header('Content-Length: ' . $doc['taille_octets']);
        header('Cache-Control: private, no-cache');
        readfile($filePath);
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PARTIE ADMINISTRATION (bibliothécaire / admin)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /bibliotheque/admin — Liste avec gestion (statut, suppression)
     */
    public function adminIndex(): void
    {
        $this->requireAdminRole();

        $documents = Database::fetchAll(
            "SELECT d.*, t.libelle AS thematique_libelle, t.icone_fa, t.couleur,
                    u.login AS publie_par_login
             FROM bibliotheque_documents d
             JOIN bibliotheque_thematiques t ON t.id = d.thematique_id
             LEFT JOIN users u ON u.id = d.publie_par
             ORDER BY d.cree_le DESC
             LIMIT 100"
        ) ?: [];

        $thematiques = Database::fetchAll(
            "SELECT * FROM bibliotheque_thematiques WHERE actif = 1 ORDER BY ordre ASC"
        ) ?: [];

        $page_title  = 'Gestion bibliothèque — FIE';
        $active_menu = 'bibliotheque_admin';
        require BASE_PATH . '/app/views/bibliotheque/admin.php';
    }

    /**
     * GET /bibliotheque/admin/nouveau — Formulaire de publication
     */
    public function adminNewForm(): void
    {
        $this->requireAdminRole();

        $thematiques = Database::fetchAll(
            "SELECT * FROM bibliotheque_thematiques WHERE actif = 1 ORDER BY ordre ASC"
        ) ?: [];

        $csrf = SecurityHelper::getCsrfToken();
        $page_title  = 'Publier un document — Bibliothèque FIE';
        $active_menu = 'bibliotheque_admin';
        require BASE_PATH . '/app/views/bibliotheque/new.php';
    }

    /**
     * POST /bibliotheque/admin/publier — Traitement upload + publication
     */
    public function adminPublish(): void
    {
        $this->requireAdminRole();

        if (!SecurityHelper::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['fie_flash_error'] = 'Jeton CSRF invalide.';
            header('Location: ' . BASE_URL . '/bibliotheque/admin/nouveau');
            exit;
        }

        // Validation champs obligatoires
        $titre        = trim($_POST['titre'] ?? '');
        $thematiqueId = (int)($_POST['thematique_id'] ?? 0);
        $description  = trim($_POST['description'] ?? '');
        $auteur       = trim($_POST['auteur'] ?? '');
        $annee        = (int)($_POST['annee_publication'] ?? 0) ?: null;
        $niveau       = trim($_POST['niveau_scolaire'] ?? '');
        $isPublic     = isset($_POST['public']) ? 1 : 0;
        $statut       = in_array($_POST['statut'] ?? '', ['brouillon','publie']) ? $_POST['statut'] : 'brouillon';

        if ($titre === '' || $thematiqueId <= 0) {
            $_SESSION['fie_flash_error'] = 'Le titre et la thématique sont obligatoires.';
            header('Location: ' . BASE_URL . '/bibliotheque/admin/nouveau');
            exit;
        }

        // Vérification fichier uploadé
        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['fie_flash_error'] = 'Aucun fichier reçu ou erreur upload (code: ' . ($_FILES['fichier']['error'] ?? 'N/A') . ')';
            header('Location: ' . BASE_URL . '/bibliotheque/admin/nouveau');
            exit;
        }

        $file     = $_FILES['fichier'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = explode(',', Database::fetchScalar("SELECT valeur FROM fie_settings WHERE cle = 'bibliotheque_extensions'") ?: 'pdf,doc,docx,ppt,pptx,xlsx');
        $maxMo    = (int)(Database::fetchScalar("SELECT valeur FROM fie_settings WHERE cle = 'bibliotheque_max_size_mb'") ?: 20);

        if (!in_array($ext, $allowed)) {
            $_SESSION['fie_flash_error'] = "Extension '.$ext' non autorisée. Extensions acceptées : " . implode(', ', $allowed);
            header('Location: ' . BASE_URL . '/bibliotheque/admin/nouveau');
            exit;
        }

        if ($file['size'] > $maxMo * 1024 * 1024) {
            $_SESSION['fie_flash_error'] = "Fichier trop volumineux (max {$maxMo} Mo).";
            header('Location: ' . BASE_URL . '/bibliotheque/admin/nouveau');
            exit;
        }

        // Détermination type MIME
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip'  => 'application/zip',
        ];
        $mime = $mimeMap[$ext] ?? 'application/octet-stream';

        // Créer dossier upload
        $uploadDir = BASE_PATH . '/uploads/bibliotheque/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Nom unique pour éviter collisions
        $uniqueName = date('YmdHis') . '_' . uniqid() . '.' . $ext;
        $destPath   = $uploadDir . $uniqueName;
        $relPath    = 'uploads/bibliotheque/' . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $_SESSION['fie_flash_error'] = 'Erreur lors du déplacement du fichier uploadé.';
            header('Location: ' . BASE_URL . '/bibliotheque/admin/nouveau');
            exit;
        }

        // Insertion en DB
        Database::query(
            "INSERT INTO bibliotheque_documents
             (thematique_id, titre, description, auteur, annee_publication, niveau_scolaire,
              nom_fichier, chemin_fichier, type_mime, taille_octets, extension,
              statut, public, publie_par, publie_le)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())",
            [
                $thematiqueId, $titre, $description ?: null, $auteur ?: null,
                $annee, $niveau ?: null,
                $file['name'], $relPath, $mime, $file['size'], $ext,
                $statut, $isPublic, $_SESSION['fie_user']['id']
            ]
        );

        $_SESSION['fie_flash_success'] = "Document « {$titre} » publié avec succès.";
        header('Location: ' . BASE_URL . '/bibliotheque/admin');
        exit;
    }

    /**
     * GET /bibliotheque/admin/:id/statut/:statut — Changer le statut d'un document
     */
    public function adminSetStatut(int $id, string $statut): void
    {
        $this->requireAdminRole();

        $validStatuts = ['brouillon', 'publie', 'archive'];
        if (!in_array($statut, $validStatuts)) {
            $_SESSION['fie_flash_error'] = 'Statut invalide.';
            header('Location: ' . BASE_URL . '/bibliotheque/admin');
            exit;
        }

        Database::query(
            "UPDATE bibliotheque_documents SET statut = ?, publie_le = IF(? = 'publie', NOW(), publie_le) WHERE id = ?",
            [$statut, $statut, $id]
        );

        $_SESSION['fie_flash_success'] = 'Statut mis à jour.';
        header('Location: ' . BASE_URL . '/bibliotheque/admin');
        exit;
    }

    /**
     * GET /bibliotheque/admin/:id/supprimer — Suppression document
     */
    public function adminDelete(int $id): void
    {
        $this->requireAdminRole();

        $doc = Database::fetchOne("SELECT * FROM bibliotheque_documents WHERE id = ?", [$id]);
        if ($doc) {
            // Supprimer fichier physique
            $filePath = BASE_PATH . '/' . $doc['chemin_fichier'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            Database::query("DELETE FROM bibliotheque_documents WHERE id = ?", [$id]);
            $_SESSION['fie_flash_success'] = 'Document supprimé.';
        } else {
            $_SESSION['fie_flash_error'] = 'Document introuvable.';
        }

        header('Location: ' . BASE_URL . '/bibliotheque/admin');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // UTILITAIRES PRIVÉS
    // ──────────────────────────────────────────────────────────────────────────

    private function requireAdminRole(): void
    {
        if (!SecurityHelper::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }
        if (!in_array(SecurityHelper::userRole(), self::ROLES_ADMIN, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/views/errors/403.php';
            exit;
        }
    }
}
