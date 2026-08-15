<?php
/**
 * app_fie/app/controllers/InscriptionController.php
 * Contrôleur du module Inscription (module complet).
 *
 * CORRECTIONS PHASE 1 :
 *   - Suppression namespace App\Controllers (pas de PSR-4 complet)
 *   - detail($iue) et printFiche($iue) : les paramètres viennent de $_GET['iue']
 *     (le Router injecte les paramètres nommés dans $_GET) → signatures sans paramètre
 *   - ajaxSelectDependent() : méthode générique supprimée → remplacée par 4 méthodes
 *     distinctes (ajaxCommunes, ajaxZones, ajaxCollines, ajaxEtablissements)
 *     conformes aux routes du Router corrigé
 *   - ajaxCheckDoublon() : retour JSON même si critères manquants (évite sortie vide)
 *   - index() : renvoie vers search (list view)
 *   - redirectWithError() : utilise BASE_URL/FIE_BASE_URL cohérent
 *   - Chargement des modèles nécessaires
 */

require_once FIE_MODELS_PATH . 'EleveModel.php';
require_once FIE_MODELS_PATH . 'InscriptionModel.php';
require_once FIE_MODELS_PATH . 'EtablissementModel.php';
require_once FIE_SERVICES_PATH . 'IueGenerator.php';
require_once FIE_SERVICES_PATH . 'AggregateService.php';

class InscriptionController
{
    /**
     * GET /inscription → redirige vers recherche (liste des élèves).
     */
    public function index(): void
    {
        SecurityHelper::requireLogin();
        header('Location: ' . BASE_URL . '/inscription/recherche');
        exit;
    }

    /**
     * GET /inscription/nouveau — Affiche le formulaire de nouvelle inscription.
     */
    public function newForm(): void
    {
        SecurityHelper::requireLogin();

        $anneeActive = Database::fetchOne(
            "SELECT code_type_annee, libelle FROM ref_type_annee WHERE actif=1 LIMIT 1"
        );
        $secteurs  = Database::fetchAll(
            "SELECT * FROM ref_secteur_ens WHERE actif=1 ORDER BY ordre"
        );
        $niveaux   = Database::fetchAll(
            "SELECT * FROM ref_type_niveau ORDER BY code_secteur, ordre"
        );
        $sections  = Database::fetchAll("SELECT * FROM ref_type_section ORDER BY code_type_section");
        $provinces = EtablissementModel::getProvinces();
        $csrf      = SecurityHelper::getCsrfToken();
        $lastSync  = EtablissementModel::getLastSyncDate();

        // Récupère les anciennes valeurs de formulaire après erreur
        $old    = $_SESSION['fie_form_old']     ?? [];
        $errors = $_SESSION['fie_field_errors'] ?? [];
        unset($_SESSION['fie_form_old'], $_SESSION['fie_field_errors']);

        $page_title  = 'Nouvelle inscription — FIE';
        $active_menu = 'inscription';

        require FIE_VIEWS_PATH . 'inscription/new.php';
    }

    /**
     * POST /inscription/nouveau — Traitement du formulaire d'inscription.
     */
    public function processNew(): void
    {
        SecurityHelper::requireLogin();

        // ── Vérification CSRF ──────────────────────────────────────────────
        if (!SecurityHelper::verifyCsrf($_POST[FIE_CSRF_TOKEN_NAME] ?? null)) {
            $this->redirectWithError('inscription/nouveau', 'Jeton de sécurité invalide. Veuillez réessayer.');
            return;
        }

        $errors = [];

        // ── Validation + sanitisation ─────────────────────────────────────
        $nom             = SecurityHelper::sanitizeStr($_POST['nom']     ?? '');
        $prenoms         = SecurityHelper::sanitizeStr($_POST['prenoms'] ?? '');
        $sexe            = $_POST['sexe'] ?? '';
        $dateNaissance   = $_POST['date_naissance'] ?? '';
        $lieuNaissance   = SecurityHelper::sanitizeStr($_POST['lieu_naissance']    ?? '');
        $provNaissance   = SecurityHelper::sanitizeStr($_POST['province_naissance']?? '');
        $nationalite     = SecurityHelper::sanitizeStr($_POST['nationalite']       ?? 'BDI', 3);
        $nomPere         = SecurityHelper::sanitizeStr($_POST['nom_pere']          ?? '');
        $nomMere         = SecurityHelper::sanitizeStr($_POST['nom_mere']          ?? '');
        $nomTuteur       = SecurityHelper::sanitizeStr($_POST['nom_tuteur']        ?? '');
        $telTuteur       = SecurityHelper::sanitizeStr($_POST['telephone_tuteur']  ?? '', 30);
        $codeEtab        = (int)($_POST['code_etablissement']   ?? 0);
        $codeSecteur     = (int)($_POST['code_type_secteur_ens']?? 0);
        $codeNiveau      = (int)($_POST['code_type_niveau']     ?? 0);
        $codeSection     = (int)($_POST['code_type_section']    ?? 1);
        $codeAnnee       = (int)($_POST['code_type_annee']      ?? 0);
        $dateInscription = $_POST['date_inscription'] ?? date('Y-m-d');
        $numeroClasse    = SecurityHelper::sanitizeStr($_POST['numero_classe'] ?? '', 20);

        // ── Règles de validation ──────────────────────────────────────────
        if (strlen($nom) < 2)      $errors['nom']     = "Le nom est obligatoire (min. 2 caractères).";
        if (strlen($prenoms) < 2)  $errors['prenoms'] = "Le(s) prénom(s) sont obligatoires.";
        if (!in_array($sexe, ['M','F'], true)) $errors['sexe'] = "Le sexe est obligatoire.";
        if (!SecurityHelper::validateDate($dateNaissance)) {
            $errors['date_naissance'] = "Date de naissance invalide (format YYYY-MM-DD attendu).";
        } elseif (strtotime($dateNaissance) > time()) {
            $errors['date_naissance'] = "La date de naissance ne peut pas être dans le futur.";
        }
        if ($codeEtab    <= 0) $errors['code_etablissement']    = "L'établissement est obligatoire.";
        if ($codeSecteur <= 0) $errors['code_type_secteur_ens'] = "Le sous-secteur est obligatoire.";
        if ($codeNiveau  <= 0) $errors['code_type_niveau']      = "Le niveau est obligatoire.";
        if ($codeAnnee   <= 0) $errors['code_type_annee']       = "L'année scolaire est obligatoire.";
        if (!SecurityHelper::validateDate($dateInscription)) {
            $errors['date_inscription'] = "Date d'inscription invalide.";
        }

        // Vérification que l'établissement existe dans le miroir
        if ($codeEtab > 0) {
            $etab = EtablissementModel::findByCode($codeEtab);
            if (!$etab) {
                $errors['code_etablissement'] = "Établissement introuvable. Veuillez resynchroniser.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['fie_form_old']     = $_POST;
            $_SESSION['fie_field_errors'] = $errors;
            $this->redirectWithError('inscription/nouveau', 'Veuillez corriger les erreurs ci-dessous.');
            return;
        }

        // ── Création de l'élève + IUE ─────────────────────────────────────
        try {
            Database::beginTransaction();

            $result = EleveModel::create([
                'nom'                => $nom,
                'prenoms'            => $prenoms,
                'sexe'               => $sexe,
                'date_naissance'     => $dateNaissance,
                'lieu_naissance'     => $lieuNaissance  ?: null,
                'province_naissance' => $provNaissance  ?: null,
                'nationalite'        => $nationalite,
                'nom_pere'           => $nomPere        ?: null,
                'nom_mere'           => $nomMere        ?: null,
                'nom_tuteur'         => $nomTuteur      ?: null,
                'telephone_tuteur'   => $telTuteur      ?: null,
                'created_by'         => SecurityHelper::userId(),
            ], $codeSecteur, $codeAnnee);

            InscriptionModel::create($result['id'], [
                'code_etablissement'    => $codeEtab,
                'code_type_secteur_ens' => $codeSecteur,
                'code_type_annee'       => $codeAnnee,
                'code_type_niveau'      => $codeNiveau,
                'code_type_section'     => $codeSection,
                'numero_classe'         => $numeroClasse ?: null,
                'date_inscription'      => $dateInscription,
                'created_by'            => SecurityHelper::userId(),
            ]);

            (new Logger('inscription'))->info(
                sprintf("Nouvelle inscription : IUE=%s Nom=%s %s Etab=%d par=%s",
                    $result['iue'], $nom, $prenoms, $codeEtab, SecurityHelper::userLogin())
            );

            Database::commit();
            SecurityHelper::renewCsrf();

            $_SESSION['fie_flash_success'] = "Élève inscrit avec succès. IUE : " . $result['iue'];

            header('Location: ' . BASE_URL . '/inscription/' . urlencode($result['iue']));
            exit;

        } catch (\Throwable $e) {
            Database::rollback();
            (new Logger('inscription'))->error("Erreur création : " . $e->getMessage());
            $this->redirectWithError('inscription/nouveau',
                'Erreur technique lors de l\'inscription' . (FIE_DEBUG ? ' : ' . $e->getMessage() : '. Contactez le support.'));
        }
    }

    /**
     * GET /inscription/:iue — Fiche de l'élève (détail + historique).
     * CORRECTION : pas de paramètre en signature — $_GET['iue'] injecté par le Router
     */
    public function detail(): void
    {
        SecurityHelper::requireLogin();

        $iue = $_GET['iue'] ?? '';

        if (!IueGenerator::validate($iue)) {
            http_response_code(400);
            $page_title = 'IUE invalide';
            require FIE_VIEWS_PATH . 'errors/404.php';
            exit;
        }

        $eleve = EleveModel::findByIue($iue);
        if (!$eleve) {
            http_response_code(404);
            $page_title = 'Élève introuvable';
            require FIE_VIEWS_PATH . 'errors/404.php';
            exit;
        }

        $inscriptions = InscriptionModel::forEleve((int)$eleve['id']);
        $success      = !empty($_GET['success']);

        $page_title  = 'Fiche élève — ' . SecurityHelper::e($eleve['nom']) . ' ' . SecurityHelper::e($eleve['prenoms'] ?? '');
        $active_menu = 'recherche';

        require FIE_VIEWS_PATH . 'inscription/detail.php';
    }

    /**
     * GET /inscription/:iue/imprimer — Fiche imprimable.
     * CORRECTION : même pattern, $_GET['iue'] injecté par Router
     */
    public function printFiche(): void
    {
        SecurityHelper::requireLogin();

        $iue = $_GET['iue'] ?? '';

        $eleve = EleveModel::findByIue($iue);
        if (!$eleve) {
            http_response_code(404);
            die('Élève introuvable');
        }
        $inscriptions = InscriptionModel::forEleve((int)$eleve['id']);
        require FIE_VIEWS_PATH . 'inscription/print.php';
    }

    /**
     * GET /inscription/recherche — Recherche multi-critères.
     */
    public function search(): void
    {
        SecurityHelper::requireLogin();

        $criteria = [
            'nom'            => SecurityHelper::sanitizeStr($_GET['nom']            ?? ''),
            'prenoms'        => SecurityHelper::sanitizeStr($_GET['prenoms']        ?? ''),
            'date_naissance' => $_GET['date_naissance'] ?? '',
            'iue'            => SecurityHelper::sanitizeStr($_GET['iue']            ?? ''),
            'sexe'           => in_array($_GET['sexe'] ?? '', ['M','F']) ? $_GET['sexe'] : '',
        ];
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $results = null;

        if (array_filter($criteria)) {
            $results = EleveModel::search(array_filter($criteria), $page);
        }

        $page_title  = 'Recherche élève — FIE';
        $active_menu = 'recherche';
        $csrf        = SecurityHelper::getCsrfToken();

        require FIE_VIEWS_PATH . 'inscription/search.php';
    }

    // ── Endpoints AJAX ────────────────────────────────────────────────────────

    /**
     * POST /inscription/ajax/doublon — Vérification doublon avant soumission.
     */
    public function ajaxCheckDoublon(): void
    {
        SecurityHelper::requireLogin();

        $nom   = SecurityHelper::sanitizeStr($_POST['nom']    ?? '');
        $prens = SecurityHelper::sanitizeStr($_POST['prenoms']?? '');
        $ddn   = $_POST['date_naissance'] ?? '';
        $lieu  = SecurityHelper::sanitizeStr($_POST['lieu_naissance'] ?? '');

        if (!$nom || !$prens || !$ddn) {
            SecurityHelper::jsonResponse(['doublons' => [], 'count' => 0]);
        }

        $iues  = IueGenerator::detectDoublons($nom, $prens, $ddn, $lieu);
        $eleves = [];
        foreach ($iues as $iue) {
            $e = EleveModel::findByIue($iue);
            if ($e) {
                $eleves[] = [
                    'iue'            => $e['iue'],
                    'nom'            => SecurityHelper::e($e['nom']),
                    'prenoms'        => SecurityHelper::e($e['prenoms']),
                    'date_naissance' => $e['date_naissance'],
                    'lieu_naissance' => SecurityHelper::e($e['lieu_naissance'] ?? ''),
                ];
            }
        }

        SecurityHelper::jsonResponse(['doublons' => $eleves, 'count' => count($eleves)]);
    }

    /**
     * GET /inscription/ajax/communes — Communes d'une province.
     */
    public function ajaxCommunes(): void
    {
        SecurityHelper::requireLogin();
        $province = SecurityHelper::sanitizeStr($_GET['province'] ?? '');
        if (!$province) {
            SecurityHelper::jsonResponse(['communes' => []]);
        }
        $data = EtablissementModel::getCommunes($province);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => ['code' => $r['commune'], 'libelle' => $r['commune']], $data)
        ]);
    }

    /**
     * GET /inscription/ajax/zones — Zones d'une commune.
     */
    public function ajaxZones(): void
    {
        SecurityHelper::requireLogin();
        $province = SecurityHelper::sanitizeStr($_GET['province'] ?? '');
        $commune  = SecurityHelper::sanitizeStr($_GET['commune']  ?? '');
        if (!$province || !$commune) {
            SecurityHelper::jsonResponse(['items' => []]);
        }
        $data = EtablissementModel::getZones($province, $commune);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => ['code' => $r['zone_admin'], 'libelle' => $r['zone_admin']], $data)
        ]);
    }

    /**
     * GET /inscription/ajax/collines — Collines d'une zone.
     */
    public function ajaxCollines(): void
    {
        SecurityHelper::requireLogin();
        $province = SecurityHelper::sanitizeStr($_GET['province'] ?? '');
        $commune  = SecurityHelper::sanitizeStr($_GET['commune']  ?? '');
        $zone     = SecurityHelper::sanitizeStr($_GET['zone']     ?? '') ?: null;
        if (!$province || !$commune) {
            SecurityHelper::jsonResponse(['items' => []]);
        }
        $data = EtablissementModel::getCollines($province, $commune, $zone);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => ['code' => $r['colline'], 'libelle' => $r['colline']], $data)
        ]);
    }

    /**
     * GET /inscription/ajax/etablissements — Établissements d'une colline.
     */
    public function ajaxEtablissements(): void
    {
        SecurityHelper::requireLogin();
        $province = SecurityHelper::sanitizeStr($_GET['province'] ?? '');
        $commune  = SecurityHelper::sanitizeStr($_GET['commune']  ?? '');
        $zone     = SecurityHelper::sanitizeStr($_GET['zone']     ?? '') ?: null;
        $colline  = SecurityHelper::sanitizeStr($_GET['colline']  ?? '') ?: null;
        $secteur  = (int)($_GET['secteur'] ?? 0) ?: null;

        if (!$province || !$commune) {
            SecurityHelper::jsonResponse(['items' => []]);
        }

        $data = EtablissementModel::getEtablissements($province, $commune, $zone, $colline, $secteur);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => [
                'code'    => $r['code_etablissement'],
                'libelle' => $r['nom_etablissement'],
                'extra'   => $r['chaine_localisation'] ?? '',
            ], $data)
        ]);
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function redirectWithError(string $path, string $msg): void
    {
        $_SESSION['fie_flash_error'] = $msg;
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }
}
