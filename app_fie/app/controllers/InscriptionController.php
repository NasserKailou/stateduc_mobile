<?php
/**
 * app_fie/app/controllers/InscriptionController.php
 * Contrôleur du module Inscription (premier module complet).
 *
 * Routes gérées :
 *   GET  /inscription/new             → formulaire de nouvelle inscription
 *   POST /inscription/new             → traitement du formulaire
 *   GET  /inscription/search          → recherche d'élève existant
 *   GET  /inscription/detail/:iue     → fiche de l'élève
 *   GET  /inscription/print/:iue      → fiche imprimable
 *   POST /inscription/ajax/check_doublon  → AJAX vérif doublon
 *   POST /inscription/ajax/communes       → AJAX selects dépendants
 *   POST /inscription/ajax/zones          → idem
 *   POST /inscription/ajax/collines       → idem
 *   POST /inscription/ajax/etablissements → idem
 */

class InscriptionController
{
    /**
     * Affiche le formulaire de nouvelle inscription.
     */
    public function newForm(): void
    {
        SecurityHelper::requireLogin();

        $anneeActive = Database::fetchOne(
            "SELECT code_type_annee, libelle FROM ref_type_annee WHERE actif=1 LIMIT 1"
        );
        $secteurs    = Database::fetchAll(
            "SELECT * FROM ref_secteur_ens WHERE actif=1 ORDER BY ordre"
        );
        $niveaux     = Database::fetchAll(
            "SELECT * FROM ref_type_niveau ORDER BY code_secteur, ordre"
        );
        $sections    = Database::fetchAll("SELECT * FROM ref_type_section ORDER BY code_type_section");
        $provinces   = EtablissementModel::getProvinces();
        $csrf        = SecurityHelper::getCsrfToken();

        $lastSync    = EtablissementModel::getLastSyncDate();

        require FIE_VIEWS_PATH . 'inscription/new.php';
    }

    /**
     * Traite la soumission du formulaire d'inscription.
     */
    public function processNew(): void
    {
        SecurityHelper::requireLogin();

        // ── Vérification CSRF ──────────────────────────────────────────────────
        if (!SecurityHelper::verifyCsrf($_POST[FIE_CSRF_TOKEN_NAME] ?? null)) {
            $this->redirectWithError('inscription/new', 'Jeton de sécurité invalide. Veuillez réessayer.');
            return;
        }

        $errors = [];

        // ── Validation des champs obligatoires ────────────────────────────────
        $nom             = SecurityHelper::sanitizeStr($_POST['nom']     ?? '');
        $prenoms         = SecurityHelper::sanitizeStr($_POST['prenoms'] ?? '');
        $sexe            = $_POST['sexe'] ?? '';
        $dateNaissance   = $_POST['date_naissance'] ?? '';
        $lieuNaissance   = SecurityHelper::sanitizeStr($_POST['lieu_naissance'] ?? '');
        $provNaissance   = SecurityHelper::sanitizeStr($_POST['province_naissance'] ?? '');
        $nationalite     = SecurityHelper::sanitizeStr($_POST['nationalite'] ?? 'BDI', 3);
        $nomPere         = SecurityHelper::sanitizeStr($_POST['nom_pere']   ?? '');
        $nomMere         = SecurityHelper::sanitizeStr($_POST['nom_mere']   ?? '');
        $nomTuteur       = SecurityHelper::sanitizeStr($_POST['nom_tuteur'] ?? '');
        $telTuteur       = SecurityHelper::sanitizeStr($_POST['telephone_tuteur'] ?? '', 30);
        $codeEtab        = (int)($_POST['code_etablissement'] ?? 0);
        $codeSecteur     = (int)($_POST['code_type_secteur_ens']  ?? 0);
        $codeNiveau      = (int)($_POST['code_type_niveau']        ?? 0);
        $codeSection     = (int)($_POST['code_type_section']       ?? 1);
        $codeAnnee       = (int)($_POST['code_type_annee']         ?? 0);
        $dateInscription = $_POST['date_inscription'] ?? date('Y-m-d');
        $numeroClasse    = SecurityHelper::sanitizeStr($_POST['numero_classe'] ?? '', 20);

        if (strlen($nom) < 2)      $errors['nom']  = "Le nom est obligatoire (min. 2 caractères).";
        if (strlen($prenoms) < 2)  $errors['prenoms'] = "Le(s) prénom(s) sont obligatoires.";
        if (!in_array($sexe, ['M','F'])) $errors['sexe'] = "Le sexe est obligatoire.";
        if (!SecurityHelper::validateDate($dateNaissance)) $errors['date_naissance'] = "Date de naissance invalide.";
        if ($dateNaissance && strtotime($dateNaissance) > time()) $errors['date_naissance'] = "La date de naissance ne peut pas être dans le futur.";
        if ($codeEtab    <= 0) $errors['code_etablissement']   = "L'établissement est obligatoire.";
        if ($codeSecteur <= 0) $errors['code_type_secteur_ens'] = "Le sous-secteur est obligatoire.";
        if ($codeNiveau  <= 0) $errors['code_type_niveau']     = "Le niveau est obligatoire.";
        if ($codeAnnee   <= 0) $errors['code_type_annee']      = "L'année scolaire est obligatoire.";
        if (!SecurityHelper::validateDate($dateInscription)) $errors['date_inscription'] = "Date d'inscription invalide.";

        // Vérifier que l'établissement existe dans le miroir
        if ($codeEtab > 0) {
            $etab = EtablissementModel::findByCode($codeEtab);
            if (!$etab) $errors['code_etablissement'] = "Établissement introuvable. Veuillez resynchroniser.";
        }

        if (!empty($errors)) {
            $this->redirectWithError('inscription/new', 'Veuillez corriger les erreurs.', $errors);
            return;
        }

        // ── Vérification doublon AVANT création ───────────────────────────────
        $doublonsPreCheck = IueGenerator::detectDoublons($nom, $prenoms, $dateNaissance, $lieuNaissance);

        // ── Création de l'élève + IUE ─────────────────────────────────────────
        try {
            Database::beginTransaction();

            $result = EleveModel::create([
                'nom'                  => $nom,
                'prenoms'              => $prenoms,
                'sexe'                 => $sexe,
                'date_naissance'       => $dateNaissance,
                'lieu_naissance'       => $lieuNaissance  ?: null,
                'province_naissance'   => $provNaissance  ?: null,
                'nationalite'          => $nationalite,
                'nom_pere'             => $nomPere  ?: null,
                'nom_mere'             => $nomMere  ?: null,
                'nom_tuteur'           => $nomTuteur ?: null,
                'telephone_tuteur'     => $telTuteur ?: null,
                'created_by'           => SecurityHelper::userId(),
            ], $codeSecteur, $codeAnnee);

            // Créer l'inscription
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

            // Audit
            (new Logger('inscription'))->info(
                sprintf("Nouvelle inscription : IUE=%s, Nom=%s %s, Etab=%d, par=%s",
                    $result['iue'], $nom, $prenoms, $codeEtab, SecurityHelper::userLogin())
            );

            Database::commit();
            SecurityHelper::renewCsrf();

            // Rediriger vers la fiche
            header('Location: ' . FIE_BASE_URL . 'inscription/detail/' . urlencode($result['iue'])
                . '?success=1');
            exit;

        } catch (Throwable $e) {
            Database::rollback();
            (new Logger('inscription'))->error("Erreur création inscription : " . $e->getMessage());
            $this->redirectWithError('inscription/new', 'Erreur technique : ' . (FIE_DEBUG ? $e->getMessage() : 'Contactez le support.'));
        }
    }

    /**
     * Fiche de l'élève (détail + historique).
     */
    public function detail(string $iue): void
    {
        SecurityHelper::requireLogin();

        if (!IueGenerator::validate($iue)) {
            http_response_code(400);
            die('IUE invalide');
        }

        $eleve = EleveModel::findByIue($iue);
        if (!$eleve) {
            http_response_code(404);
            die('Élève introuvable');
        }

        $inscriptions = InscriptionModel::forEleve((int)$eleve['id']);
        $success      = !empty($_GET['success']);

        require FIE_VIEWS_PATH . 'inscription/detail.php';
    }

    /**
     * Fiche d'inscription imprimable (PDF-ready).
     */
    public function printFiche(string $iue): void
    {
        SecurityHelper::requireLogin();
        $eleve = EleveModel::findByIue($iue);
        if (!$eleve) { http_response_code(404); die('Introuvable'); }
        $inscriptions = InscriptionModel::forEleve((int)$eleve['id']);
        require FIE_VIEWS_PATH . 'inscription/print.php';
    }

    /**
     * Recherche d'élèves existants.
     */
    public function search(): void
    {
        SecurityHelper::requireLogin();
        $criteria = [
            'nom'             => SecurityHelper::sanitizeStr($_GET['nom']             ?? ''),
            'prenoms'         => SecurityHelper::sanitizeStr($_GET['prenoms']         ?? ''),
            'date_naissance'  => $_GET['date_naissance']  ?? '',
            'iue'             => SecurityHelper::sanitizeStr($_GET['iue']             ?? ''),
            'sexe'            => $_GET['sexe'] ?? '',
        ];
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $results = null;

        if (array_filter($criteria)) {
            $results = EleveModel::search(array_filter($criteria), $page);
        }

        require FIE_VIEWS_PATH . 'inscription/search.php';
    }

    // ── Endpoints AJAX ────────────────────────────────────────────────────────

    /**
     * AJAX : vérification de doublon avant soumission du formulaire.
     */
    public function ajaxCheckDoublon(): void
    {
        SecurityHelper::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            SecurityHelper::jsonResponse(['error' => 'Méthode non autorisée'], 405);
        }

        $nom   = SecurityHelper::sanitizeStr($_POST['nom']   ?? '');
        $prens = SecurityHelper::sanitizeStr($_POST['prenoms'] ?? '');
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
     * AJAX : sélects dépendants (provinces → communes → zones → collines → établissements).
     */
    public function ajaxSelectDependent(string $type): void
    {
        SecurityHelper::requireLogin();

        $province = SecurityHelper::sanitizeStr($_POST['province'] ?? $_GET['province'] ?? '');
        $commune  = SecurityHelper::sanitizeStr($_POST['commune']  ?? $_GET['commune']  ?? '');
        $zone     = SecurityHelper::sanitizeStr($_POST['zone']     ?? $_GET['zone']     ?? '');
        $colline  = SecurityHelper::sanitizeStr($_POST['colline']  ?? $_GET['colline']  ?? '');
        $secteur  = (int)($_POST['secteur'] ?? $_GET['secteur'] ?? 0);

        switch ($type) {
            case 'communes':
                $data = EtablissementModel::getCommunes($province);
                SecurityHelper::jsonResponse(['communes' => array_column($data, 'commune')]);
                break;
            case 'zones':
                $data = EtablissementModel::getZones($province, $commune);
                SecurityHelper::jsonResponse(['zones' => array_column($data, 'zone_admin')]);
                break;
            case 'collines':
                $data = EtablissementModel::getCollines($province, $commune, $zone ?: null);
                SecurityHelper::jsonResponse(['collines' => array_column($data, 'colline')]);
                break;
            case 'etablissements':
                $data = EtablissementModel::getEtablissements(
                    $province, $commune, $zone ?: null, $colline ?: null, $secteur ?: null
                );
                SecurityHelper::jsonResponse(['etablissements' => $data]);
                break;
            default:
                SecurityHelper::jsonResponse(['error' => 'Type inconnu'], 400);
        }
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function redirectWithError(string $path, string $msg, array $fieldErrors = []): void
    {
        $_SESSION['fie_flash_error']  = $msg;
        $_SESSION['fie_field_errors'] = $fieldErrors;
        $_SESSION['fie_form_old']     = $_POST;
        header('Location: ' . FIE_BASE_URL . $path);
        exit;
    }
}
