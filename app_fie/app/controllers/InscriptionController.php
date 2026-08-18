<?php
/**
 * app_fie/app/controllers/InscriptionController.php
 * Contrôleur du module Inscription / émission d'IUE.
 *
 * CORRECTIONS & AMÉLIORATIONS Session 6 :
 *   - newForm() : charge toutes les années scolaires (pas seulement actif=1)
 *   - ajaxCheckDoublon() : retourne JSON riche pour modal Bootstrap
 *   - Nouvelles routes AJAX code-based (ATLAS_COLLINE) :
 *       ajaxCommunesCode()     → communes par code_province
 *       ajaxCollinesCode()     → collines par code_commune
 *       ajaxEtabsCode()        → établissements par code_colline + code_commune
 *       ajaxEtabDetail()       → détail ATLAS_COLLINE d'un établissement
 *   - ajaxSyncTypeAnnee()      → sync TYPE_ANNEE depuis StatEduc
 *   - Routes legacy (province/commune texte) conservées pour rétrocompat
 */

require_once FIE_MODELS_PATH   . 'EleveModel.php';
require_once FIE_MODELS_PATH   . 'InscriptionModel.php';
require_once FIE_MODELS_PATH   . 'EtablissementModel.php';
require_once FIE_SERVICES_PATH . 'IueGenerator.php';
require_once FIE_SERVICES_PATH . 'AggregateService.php';
require_once FIE_SERVICES_PATH . 'SyncService.php';
require_once FIE_APP_PATH      . '../api/stateduc/StatEducApiClient.php';

class InscriptionController
{
    /**
     * GET /inscription → redirige vers recherche.
     */
    public function index(): void
    {
        SecurityHelper::requireLogin();
        header('Location: ' . BASE_URL . '/inscription/recherche');
        exit;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FORMULAIRE NOUVELLE INSCRIPTION
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /inscription/nouveau — Formulaire de nouvelle inscription.
     */
    public function newForm(): void
    {
        SecurityHelper::requireLogin();

        // Toutes les années scolaires — ORDER BY code_type_annee DESC
        // (ref_type_annee n'a PAS de colonne 'ordre' dans le schéma réel)
        $annees = Database::fetchAll(
            "SELECT code_type_annee, libelle, actif
             FROM ref_type_annee
             ORDER BY code_type_annee DESC"
        );
        // Année active par défaut (actif=1)
        $anneeActive = null;
        foreach ($annees as $a) {
            if ($a['actif']) { $anneeActive = $a; break; }
        }
        if (!$anneeActive && !empty($annees)) $anneeActive = $annees[0];

        $secteurs  = Database::fetchAll("SELECT * FROM ref_secteur_ens WHERE actif=1 ORDER BY ordre");
        $niveaux   = Database::fetchAll("SELECT * FROM ref_type_niveau ORDER BY code_secteur, ordre");
        $sections  = Database::fetchAll("SELECT * FROM ref_type_section ORDER BY code_type_section");
        $provinces = EtablissementModel::getProvinces();
        $csrf      = SecurityHelper::getCsrfToken();
        $lastSync  = EtablissementModel::getLastSyncDate();
        $nbEtabs   = EtablissementModel::count();

        $old    = $_SESSION['fie_form_old']     ?? [];
        $errors = $_SESSION['fie_field_errors'] ?? [];
        unset($_SESSION['fie_form_old'], $_SESSION['fie_field_errors']);

        $page_title  = 'Nouvelle inscription — FIE';
        $active_menu = 'inscription';

        require FIE_VIEWS_PATH . 'inscription/new.php';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TRAITEMENT POST INSCRIPTION
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * POST /inscription/nouveau — Traitement du formulaire.
     */
    public function processNew(): void
    {
        SecurityHelper::requireLogin();

        if (!SecurityHelper::verifyCsrf($_POST[FIE_CSRF_TOKEN_NAME] ?? null)) {
            $this->redirectWithError('inscription/nouveau', 'Jeton de sécurité invalide. Veuillez réessayer.');
            return;
        }

        $errors = [];

        $nom             = SecurityHelper::sanitizeStr($_POST['nom']              ?? '');
        $prenoms         = SecurityHelper::sanitizeStr($_POST['prenoms']          ?? '');
        $sexe            = $_POST['sexe']            ?? '';
        $dateNaissance   = $_POST['date_naissance']  ?? '';
        $lieuNaissance   = SecurityHelper::sanitizeStr($_POST['lieu_naissance']   ?? '');
        $provNaissance   = SecurityHelper::sanitizeStr($_POST['province_naissance']?? '');
        $nationalite     = SecurityHelper::sanitizeStr($_POST['nationalite']      ?? 'BDI', 3);
        $nomPere         = SecurityHelper::sanitizeStr($_POST['nom_pere']         ?? '');
        $nomMere         = SecurityHelper::sanitizeStr($_POST['nom_mere']         ?? '');
        $nomTuteur       = SecurityHelper::sanitizeStr($_POST['nom_tuteur']       ?? '');
        $telTuteur       = SecurityHelper::sanitizeStr($_POST['telephone_tuteur'] ?? '', 30);
        $codeEtab        = (int)($_POST['code_etablissement']    ?? 0);
        $codeSecteur     = (int)($_POST['code_type_secteur_ens'] ?? 0);
        $codeNiveau      = (int)($_POST['code_type_niveau']      ?? 0);
        $codeSection     = (int)($_POST['code_type_section']     ?? 1);
        $codeAnnee       = (int)($_POST['code_type_annee']       ?? 0);
        $dateInscription = $_POST['date_inscription'] ?? date('Y-m-d');
        $numeroClasse    = SecurityHelper::sanitizeStr($_POST['numero_classe']    ?? '', 20);

        if (strlen($nom) < 2)     $errors['nom']     = "Le nom est obligatoire (min. 2 caractères).";
        if (strlen($prenoms) < 2) $errors['prenoms'] = "Le(s) prénom(s) sont obligatoires.";
        if (!in_array($sexe, ['M','F'], true)) $errors['sexe'] = "Le sexe est obligatoire.";
        if (!SecurityHelper::validateDate($dateNaissance)) {
            $errors['date_naissance'] = "Date de naissance invalide.";
        } elseif (strtotime($dateNaissance) > time()) {
            $errors['date_naissance'] = "La date ne peut pas être dans le futur.";
        }
        if ($codeEtab    <= 0) $errors['code_etablissement']    = "L'établissement est obligatoire.";
        if ($codeSecteur <= 0) $errors['code_type_secteur_ens'] = "Le sous-secteur est obligatoire.";
        if ($codeNiveau  <= 0) $errors['code_type_niveau']      = "Le niveau est obligatoire.";
        if ($codeAnnee   <= 0) $errors['code_type_annee']       = "L'année scolaire est obligatoire.";
        if (!SecurityHelper::validateDate($dateInscription)) {
            $errors['date_inscription'] = "Date d'inscription invalide.";
        }

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

            (new Logger('inscription'))->info(sprintf(
                "Nouvelle inscription : IUE=%s Nom=%s %s Etab=%d Annee=%d par=%s",
                $result['iue'], $nom, $prenoms, $codeEtab, $codeAnnee, SecurityHelper::userLogin()
            ));

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

    // ══════════════════════════════════════════════════════════════════════════
    // DETAIL / FICHE / RECHERCHE
    // ══════════════════════════════════════════════════════════════════════════

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
        $page_title   = 'Fiche élève — ' . SecurityHelper::e($eleve['nom']) . ' ' . SecurityHelper::e($eleve['prenoms'] ?? '');
        $active_menu  = 'recherche';
        require FIE_VIEWS_PATH . 'inscription/detail.php';
    }

    public function printFiche(): void
    {
        SecurityHelper::requireLogin();
        $iue   = $_GET['iue'] ?? '';
        $eleve = EleveModel::findByIue($iue);
        if (!$eleve) { http_response_code(404); die('Élève introuvable'); }
        $inscriptions = InscriptionModel::forEleve((int)$eleve['id']);
        require FIE_VIEWS_PATH . 'inscription/print.php';
    }

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

    // ══════════════════════════════════════════════════════════════════════════
    // ENDPOINTS AJAX — CASCADE CODE-BASED (ATLAS_COLLINE)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /inscription/ajax/communes-code?code_province=117
     * Communes d'une province (par code entier).
     */
    public function ajaxCommunesCode(): void
    {
        SecurityHelper::requireLogin();
        $cp = (int)($_GET['code_province'] ?? 0);
        if ($cp <= 0) { SecurityHelper::jsonResponse(['items' => []]); }

        $data = EtablissementModel::getCommunesByCode($cp);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => [
                'code'    => (int)$r['code_commune'],
                'libelle' => $r['libelle'],
            ], $data)
        ]);
    }

    /**
     * GET /inscription/ajax/collines-code?code_commune=11716
     * Collines d'une commune (par code entier).
     */
    public function ajaxCollinesCode(): void
    {
        SecurityHelper::requireLogin();
        $cc = (int)($_GET['code_commune'] ?? 0);
        if ($cc <= 0) { SecurityHelper::jsonResponse(['items' => []]); }

        $data = EtablissementModel::getCollinesByCode($cc);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => [
                'code'    => (int)$r['code_colline'],
                'libelle' => $r['libelle'],
            ], $data)
        ]);
    }

    /**
     * GET /inscription/ajax/etabs-code?code_commune=11716&code_colline=1170501&secteur=1
     * Établissements filtrés par code_commune + optionnel code_colline + secteur.
     * Retourne les 14 champs ATLAS_COLLINE pour auto-remplissage.
     */
    public function ajaxEtabsCode(): void
    {
        SecurityHelper::requireLogin();
        $cc      = (int)($_GET['code_commune']  ?? 0);
        $ccl     = (int)($_GET['code_colline']  ?? 0);
        $secteur = (int)($_GET['secteur']       ?? 0) ?: null;

        if ($cc <= 0) { SecurityHelper::jsonResponse(['items' => []]); }

        $data = EtablissementModel::getEtablissementsByCode($ccl, $cc, $secteur);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => [
                'code'           => (int)$r['code_etablissement'],
                'libelle'        => $r['nom_etablissement'],
                'code_province'  => (int)$r['code_province'],
                'province'       => $r['province'],
                'code_commune'   => (int)$r['code_commune'],
                'commune'        => $r['commune'],
                'code_colline'   => (int)$r['code_colline'],
                'colline'        => $r['colline'],
                'secteur_ens'    => $r['secteur_ens'],
                'statut_org'     => $r['statut_org'],
                'milieu'         => $r['milieu'],
                'code_secteur'   => (int)$r['code_type_secteur_ens'],
                'chaine'         => $r['chaine_localisation'],
            ], $data)
        ]);
    }

    /**
     * GET /inscription/ajax/etab-detail?code=10011
     * Détail complet (ATLAS_COLLINE) d'un établissement pour auto-remplissage.
     */
    public function ajaxEtabDetail(): void
    {
        SecurityHelper::requireLogin();
        $code = (int)($_GET['code'] ?? 0);
        if ($code <= 0) { SecurityHelper::jsonResponse(['etab' => null]); }

        $etab = EtablissementModel::findByCode($code);
        if (!$etab) { SecurityHelper::jsonResponse(['etab' => null]); }

        SecurityHelper::jsonResponse(['etab' => [
            'code_etablissement'   => (int)$etab['code_etablissement'],
            'nom_etablissement'    => $etab['nom_etablissement'],
            'code_province'        => (int)($etab['code_province'] ?? 0),
            'province'             => $etab['province'],
            'code_commune'         => (int)($etab['code_commune']  ?? 0),
            'commune'              => $etab['commune'],
            'code_colline'         => (int)($etab['code_colline']  ?? 0),
            'colline'              => $etab['colline'],
            'secteur_ens'          => $etab['secteur_ens'],
            'statut_org'           => $etab['statut_org'],
            'milieu'               => $etab['milieu'],
            'code_type_secteur_ens'=> (int)($etab['code_type_secteur_ens'] ?? 0),
            'chaine_localisation'  => $etab['chaine_localisation'],
        ]]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ENDPOINTS AJAX — LEGACY (texte, pour rétrocompat)
    // ══════════════════════════════════════════════════════════════════════════

    public function ajaxCommunes(): void
    {
        SecurityHelper::requireLogin();
        $province = SecurityHelper::sanitizeStr($_GET['province'] ?? '');
        if (!$province) { SecurityHelper::jsonResponse(['items' => []]); }
        $data = EtablissementModel::getCommunes($province);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => ['code' => (int)($r['code_commune'] ?? 0), 'libelle' => $r['libelle']], $data)
        ]);
    }

    public function ajaxZones(): void
    {
        // ATLAS_COLLINE n'a pas de zone → retourner vide pour skip l'étape
        SecurityHelper::requireLogin();
        SecurityHelper::jsonResponse(['items' => []]);
    }

    public function ajaxCollines(): void
    {
        SecurityHelper::requireLogin();
        $province = SecurityHelper::sanitizeStr($_GET['province'] ?? '');
        $commune  = SecurityHelper::sanitizeStr($_GET['commune']  ?? '');
        if (!$province || !$commune) { SecurityHelper::jsonResponse(['items' => []]); }
        $data = EtablissementModel::getCollines($province, $commune);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => ['code' => (int)($r['code_colline'] ?? 0), 'libelle' => $r['libelle']], $data)
        ]);
    }

    public function ajaxEtablissements(): void
    {
        SecurityHelper::requireLogin();
        $province = SecurityHelper::sanitizeStr($_GET['province'] ?? '');
        $commune  = SecurityHelper::sanitizeStr($_GET['commune']  ?? '');
        $colline  = SecurityHelper::sanitizeStr($_GET['colline']  ?? '') ?: null;
        $secteur  = (int)($_GET['secteur'] ?? 0) ?: null;

        if (!$province || !$commune) { SecurityHelper::jsonResponse(['items' => []]); }

        $data = EtablissementModel::getEtablissements($province, $commune, null, $colline, $secteur);
        SecurityHelper::jsonResponse([
            'items' => array_map(fn($r) => [
                'code'           => $r['code_etablissement'],
                'libelle'        => $r['nom_etablissement'],
                'code_province'  => (int)($r['code_province'] ?? 0),
                'province'       => $r['province'],
                'code_commune'   => (int)($r['code_commune']  ?? 0),
                'commune'        => $r['commune'],
                'code_colline'   => (int)($r['code_colline']  ?? 0),
                'colline'        => $r['colline'],
                'secteur_ens'    => $r['secteur_ens'],
                'statut_org'     => $r['statut_org'],
                'milieu'         => $r['milieu'],
                'code_secteur'   => (int)($r['code_type_secteur_ens'] ?? 0),
                'chaine'         => $r['chaine_localisation'],
            ], $data)
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ENDPOINT AJAX — VÉRIFICATION DOUBLON (JSON pour modal Bootstrap)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * POST /inscription/ajax/doublon
     * Retourne JSON enrichi pour modal Bootstrap :
     * {
     *   "found": true|false,
     *   "count": N,
     *   "doublons": [{"iue","nom","prenoms","sexe","date_naissance","lieu_naissance","etablissement"}, ...]
     * }
     */
    public function ajaxCheckDoublon(): void
    {
        SecurityHelper::requireLogin();

        $nom   = SecurityHelper::sanitizeStr($_POST['nom']    ?? '');
        $prens = SecurityHelper::sanitizeStr($_POST['prenoms']?? '');
        $ddn   = $_POST['date_naissance'] ?? '';
        $lieu  = SecurityHelper::sanitizeStr($_POST['lieu_naissance'] ?? '');

        if (!$nom || !$prens || !$ddn) {
            SecurityHelper::jsonResponse(['found' => false, 'count' => 0, 'doublons' => []]);
        }

        $iues   = IueGenerator::detectDoublons($nom, $prens, $ddn, $lieu);
        $eleves = [];
        foreach ($iues as $iue) {
            $e = EleveModel::findByIue($iue);
            if ($e) {
                // Récupérer la dernière inscription pour le nom de l'établissement
                $derniereInsc = Database::fetchOne(
                    "SELECT i.code_etablissement, e.nom_etablissement,
                            ta.libelle AS annee_scolaire
                     FROM inscriptions i
                     LEFT JOIN etablissements_miroir e ON e.code_etablissement = i.code_etablissement
                     LEFT JOIN ref_type_annee ta       ON ta.code_type_annee   = i.code_type_annee
                     WHERE i.eleve_id = ?
                     ORDER BY i.date_inscription DESC
                     LIMIT 1",
                    [(int)$e['id']]
                );

                $eleves[] = [
                    'iue'             => $e['iue'],
                    'nom'             => SecurityHelper::e($e['nom']),
                    'prenoms'         => SecurityHelper::e($e['prenoms'] ?? ''),
                    'sexe'            => $e['sexe'] ?? '',
                    'date_naissance'  => $e['date_naissance'] ?? '',
                    'lieu_naissance'  => SecurityHelper::e($e['lieu_naissance'] ?? ''),
                    'etablissement'   => SecurityHelper::e($derniereInsc['nom_etablissement'] ?? ''),
                    'annee_scolaire'  => SecurityHelper::e($derniereInsc['annee_scolaire']   ?? ''),
                ];
            }
        }

        SecurityHelper::jsonResponse([
            'found'    => count($eleves) > 0,
            'count'    => count($eleves),
            'doublons' => $eleves,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ENDPOINT AJAX — SYNC TYPE_ANNEE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * POST /inscription/ajax/sync-annees
     * Déclenche la synchronisation des années scolaires depuis StatEduc.
     */
    public function ajaxSyncTypeAnnee(): void
    {
        SecurityHelper::requireLogin();

        try {
            $sync = new SyncService();
            $result = $sync->syncTypeAnnee(SecurityHelper::userLogin() ?? 'admin');
            SecurityHelper::jsonResponse([
                'success' => true,
                'message' => "Années scolaires synchronisées ({$result['upserted']} entrées).",
                'result'  => $result,
            ]);
        } catch (Throwable $e) {
            SecurityHelper::jsonResponse([
                'success' => false,
                'message' => 'Erreur sync années : ' . $e->getMessage(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ══════════════════════════════════════════════════════════════════════════

    private function redirectWithError(string $path, string $msg): void
    {
        $_SESSION['fie_flash_error'] = $msg;
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }
}
