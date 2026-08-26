<?php
/**
 * app_fie/config/Router.php
 * Routeur HTTP — dispatche les requêtes vers les contrôleurs.
 *
 * CORRECTIONS PHASE 1 :
 *   - Suppression du namespace App\Config (incompatible avec l'autoloader simple — pas de PSR-4 complet)
 *   - Suppression du use App\Services\SecurityHelper (idem)
 *   - callController() : charge le fichier contrôleur via BASE_PATH/FIE_CTRL_PATH
 *     et instancie la classe sans namespace (cohérent avec l'autoloader)
 *   - Ajout de la route GET /inscription (index) manquante
 *   - Ajout route GET /inscription/:iue/imprimer → printFiche (paramètre $iue passé via $_GET)
 *   - AJAX routes : uniformisation GET/POST selon usage réel (communes/zones/collines = GET)
 *   - Route /deconnexion → déplacée avant les routes protégées
 */

declare(strict_types=1);

class Router
{
    /** @var array<array{method:string, pattern:string, controller:string, action:string}> */
    private array $routes = [];

    public function __construct()
    {
        $this->registerRoutes();
    }

    /* ── Enregistrement des routes ────────────────────────────────────────── */

    public function get(string $path, string $controller, string $action): void
    {
        $this->add('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->add('POST', $path, $controller, $action);
    }

    public function any(string $path, string $controller, string $action): void
    {
        $this->add('*', $path, $controller, $action);
    }

    private function add(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $this->buildPattern($path),
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    /** Convertit /path/:param en regex PHP */
    private function buildPattern(string $path): string
    {
        $p = preg_replace('/\//', '\\/', $path);
        $p = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', '(?P<$1>[^\/]+)', $p);
        return '/^' . $p . '\/?$/';
    }

    /* ── Table des routes de l'application ───────────────────────────────── */

    private function registerRoutes(): void
    {
        // ── Site public ──────────────────────────────────────────────────────
        $this->get('/',                'PublicController', 'home');
        $this->get('/aide',            'PublicController', 'aide');
        $this->get('/contact',         'PublicController', 'contact');
        $this->get('/confidentialite', 'PublicController', 'confidentialite');
        $this->get('/mentions-legales','PublicController', 'mentions');
        // ── Profil public élève (accessible via QR code, sans connexion) ────
        $this->get('/eleve/:iue',      'PublicController', 'elevePublic');

        // ── Authentification ─────────────────────────────────────────────────
        $this->get('/connexion',  'AuthController', 'loginForm');
        $this->post('/connexion', 'AuthController', 'login');
        $this->any('/deconnexion','AuthController', 'logout');

        // ── Tableau de bord ──────────────────────────────────────────────────
        $this->get('/tableau-de-bord', 'DashboardController', 'index');
        $this->get('/dashboard',       'DashboardController', 'index');

        // ── Inscriptions ─────────────────────────────────────────────────────
        $this->get('/inscription',               'InscriptionController', 'index');
        $this->get('/inscription/recherche',      'InscriptionController', 'search');
        $this->get('/inscription/nouveau',        'InscriptionController', 'newForm');
        $this->post('/inscription/nouveau',       'InscriptionController', 'processNew');
        // CORRECTION : routes AJAX d'abord (avant :iue sinon capturées par le param)
        $this->post('/inscription/ajax/doublon',         'InscriptionController', 'ajaxCheckDoublon');
        // Cascades code-based ATLAS_COLLINE (nouvelles — session 6)
        $this->get('/inscription/ajax/communes-code',    'InscriptionController', 'ajaxCommunesCode');
        $this->get('/inscription/ajax/collines-code',    'InscriptionController', 'ajaxCollinesCode');
        $this->get('/inscription/ajax/etabs-code',       'InscriptionController', 'ajaxEtabsCode');
        $this->get('/inscription/ajax/etab-detail',      'InscriptionController', 'ajaxEtabDetail');
        $this->post('/inscription/ajax/sync-annees',     'InscriptionController', 'ajaxSyncTypeAnnee');
        $this->get('/inscription/ajax/nationalites',     'InscriptionController', 'ajaxNationalites');
        // Cascades texte legacy
        $this->get('/inscription/ajax/communes',         'InscriptionController', 'ajaxCommunes');
        $this->get('/inscription/ajax/zones',            'InscriptionController', 'ajaxZones');
        $this->get('/inscription/ajax/collines',         'InscriptionController', 'ajaxCollines');
        $this->get('/inscription/ajax/etablissements',   'InscriptionController', 'ajaxEtablissements');
        // Routes paramétrées en dernier
        $this->get('/inscription/:iue/imprimer', 'InscriptionController', 'printFiche');
        $this->get('/inscription/:iue',           'InscriptionController', 'detail');

        // ── Mouvements (squelette) ───────────────────────────────────────────
        $this->get('/mouvement',            'MouvementController', 'index');
        $this->get('/mouvement/nouveau',    'MouvementController', 'newForm');
        $this->post('/mouvement/nouveau',   'MouvementController', 'processNew');
        $this->get('/mouvement/:id',        'MouvementController', 'detail');

        // ── Examens (squelette) ──────────────────────────────────────────────
        $this->get('/examen',               'ExamenController', 'index');
        $this->get('/examen/nouveau',       'ExamenController', 'newForm');
        $this->post('/examen/nouveau',      'ExamenController', 'processNew');
        $this->get('/examen/:id',           'ExamenController', 'detail');

        // ── Administration ───────────────────────────────────────────────────
        $this->get('/admin',                'AdminController', 'index');
        $this->get('/admin/sync',           'AdminController', 'syncStatus');
        $this->post('/admin/sync/lancer',   'AdminController', 'triggerSync');
        $this->get('/admin/import-excel',   'AdminController', 'importExcelForm');
        $this->post('/admin/import-excel',  'AdminController', 'processExcelImport');
        // Import liste élèves (nouveau)
        $this->get('/admin/import-eleves',         'AdminController', 'importElevesForm');
        $this->post('/admin/import-eleves',        'AdminController', 'processElevesImport');
        $this->get('/admin/import-eleves/modele',  'AdminController', 'downloadElevesTemplate');
        $this->get('/admin/users',          'AdminController', 'users');
        $this->get('/admin/audit',          'AdminController', 'auditLog');

        // ── Administration — Paramétrage StatEduc ────────────────────────────
        $this->get('/admin/parametres',  'ParametresController', 'index');
        $this->post('/admin/parametres', 'ParametresController', 'save');

        // ── Bibliothèque (public — accessible sans connexion) ────────────────
        // IMPORTANT : routes statiques AVANT les routes paramétrées
        $this->get('/bibliotheque',                        'BibliothequeController', 'index');

        // ── Bibliothèque — Administration (bibliothecaire / admin) ───────────
        // Ces routes doivent être enregistrées AVANT /bibliotheque/:id/telecharger
        // sinon "admin" serait capturé comme :id
        $this->get('/bibliotheque/admin',                  'BibliothequeController', 'adminIndex');
        $this->get('/bibliotheque/admin/nouveau',          'BibliothequeController', 'adminNewForm');
        $this->post('/bibliotheque/admin/publier',         'BibliothequeController', 'adminPublish');
        $this->post('/bibliotheque/admin/:id/statut/:statut', 'BibliothequeController', 'adminSetStatut');
        $this->post('/bibliotheque/admin/:id/supprimer',   'BibliothequeController', 'adminDelete');

        // Route paramétrée en DERNIER (après toutes les statiques /bibliotheque/...)
        $this->get('/bibliotheque/:id/telecharger',        'BibliothequeController', 'telecharger');

        // ── Suivi pédagogique ────────────────────────────────────────────────
        $this->get('/suivi',                               'SuiviPedagogiqueController', 'index');
        $this->get('/suivi/classe/:id',                    'SuiviPedagogiqueController', 'classeDetail');
        $this->post('/suivi/decision',                     'SuiviPedagogiqueController', 'saveDecision');

        // ── Transferts scolaires ─────────────────────────────────────────────
        $this->get('/suivi/transferts',                    'SuiviPedagogiqueController', 'transfertsList');
        $this->get('/suivi/transfert/nouveau',             'SuiviPedagogiqueController', 'transfertForm');
        $this->post('/suivi/transfert/demander',           'SuiviPedagogiqueController', 'transfertSubmit');
        $this->post('/suivi/transfert/:id/traiter',        'SuiviPedagogiqueController', 'transfertTraiter');

        // ── Historique élève ─────────────────────────────────────────────────
        $this->get('/eleve/:iue/historique',               'HistoriqueController', 'eleve');

        // ── Gestion utilisateurs (admin) ─────────────────────────────────────
        $this->get('/admin/users/nouveau',                 'AdminController', 'userNewForm');
        $this->post('/admin/users/nouveau',                'AdminController', 'userCreate');
        $this->get('/admin/users/:id/editer',              'AdminController', 'userEditForm');
        $this->post('/admin/users/:id/editer',             'AdminController', 'userUpdate');
        $this->post('/admin/users/:id/supprimer',          'AdminController', 'userDelete');

        // ── API interne (agrégats) ───────────────────────────────────────────
        $this->get('/api/agregats',         'AggregatesApiController',   'index');
        $this->get('/api/etablissements',   'EtablissementsApiController', 'index');
    }

    /* ── Dispatch ─────────────────────────────────────────────────────────── */

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== '*' && $route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            // Injecter les paramètres nommés dans $_GET
            foreach ($matches as $key => $val) {
                if (is_string($key)) {
                    $_GET[$key] = urldecode($val);
                }
            }

                try {
                $this->callController($route['controller'], $route['action']);
            } catch (Throwable $e) {
                // Log de l'exception en mode debug
                if (defined('FIE_DEBUG') && FIE_DEBUG) {
                    $this->error500($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                } else {
                    error_log('[FIE] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                    $this->error500('Une erreur interne est survenue. Veuillez réessayer.');
                }
            }
            return;
        }

        // Aucune route trouvée → 404
        $this->notFound($uri);
    }

    private function callController(string $controllerName, string $action): void
    {
        $file = FIE_CTRL_PATH . $controllerName . '.php';

        if (!file_exists($file)) {
            $this->error500("Contrôleur introuvable : $controllerName");
            return;
        }

        require_once $file;

        // Les contrôleurs sont des classes simples (pas de namespace)
        if (!class_exists($controllerName)) {
            $this->error500("Classe introuvable : $controllerName");
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            $this->error500("Méthode $action introuvable dans $controllerName");
            return;
        }

        $controller->$action();
    }

    /* ── Pages d'erreur ───────────────────────────────────────────────────── */

    private function notFound(string $uri): void
    {
        http_response_code(404);
        $page_title  = 'Page introuvable (404)';
        $active_menu = '';
        if (file_exists(FIE_VIEWS_PATH . 'errors/404.php')) {
            require FIE_VIEWS_PATH . 'errors/404.php';
        } else {
            echo '<h1>404 — Page introuvable</h1><p>' . htmlspecialchars($uri, ENT_QUOTES, 'UTF-8') . '</p>';
        }
    }

    private function error500(string $message): void
    {
        http_response_code(500);
        if (defined('FIE_DEBUG') && FIE_DEBUG) {
            echo '<pre style="padding:2rem;font-family:monospace;color:red;background:#fff;">'
               . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            $page_title  = 'Erreur serveur (500)';
            $active_menu = '';
            if (file_exists(FIE_VIEWS_PATH . 'errors/500.php')) {
                require FIE_VIEWS_PATH . 'errors/500.php';
            } else {
                echo '<h1>500 — Erreur serveur</h1>';
            }
        }
    }
}
