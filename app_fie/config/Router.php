<?php
/**
 * FIE — Routeur HTTP simple
 * Dispatche les requêtes vers les bons contrôleurs/méthodes.
 *
 * Syntaxe de déclaration :
 *   $router->get('/path', 'ControllerClass', 'method')
 *   $router->post('/path', 'ControllerClass', 'method')
 *   $router->any('/path', 'ControllerClass', 'method')
 *
 * Paramètres nommés : /inscription/:iue  → disponibles dans $_GET['iue']
 * Les contrôleurs sont chargés via l'autoloader PSR-4 défini dans config.php
 */

declare(strict_types=1);

namespace App\Config;

use App\Services\SecurityHelper;

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
        $this->get('/',               'PublicController', 'home');
        $this->get('/aide',           'PublicController', 'aide');
        $this->get('/contact',        'PublicController', 'contact');
        $this->get('/confidentialite','PublicController', 'confidentialite');
        $this->get('/mentions-legales','PublicController','mentions');

        // ── Authentification ─────────────────────────────────────────────────
        $this->get('/connexion',      'AuthController', 'loginForm');
        $this->post('/connexion',     'AuthController', 'login');
        $this->any('/deconnexion',    'AuthController', 'logout');

        // ── Inscriptions ─────────────────────────────────────────────────────
        $this->get('/inscription',              'InscriptionController', 'index');
        $this->get('/inscription/nouveau',       'InscriptionController', 'newForm');
        $this->post('/inscription/nouveau',      'InscriptionController', 'processNew');
        $this->get('/inscription/recherche',     'InscriptionController', 'search');
        $this->get('/inscription/:iue',          'InscriptionController', 'detail');
        $this->get('/inscription/:iue/imprimer', 'InscriptionController', 'printFiche');

        // AJAX
        $this->post('/inscription/ajax/doublon',        'InscriptionController', 'ajaxCheckDoublon');
        $this->get('/inscription/ajax/communes',        'InscriptionController', 'ajaxSelectDependent');
        $this->get('/inscription/ajax/zones',           'InscriptionController', 'ajaxSelectDependent');
        $this->get('/inscription/ajax/collines',        'InscriptionController', 'ajaxSelectDependent');
        $this->get('/inscription/ajax/etablissements',  'InscriptionController', 'ajaxSelectDependent');

        // ── Mouvements (squelette) ───────────────────────────────────────────
        $this->get('/mouvement',           'MouvementController', 'index');
        $this->get('/mouvement/nouveau',   'MouvementController', 'newForm');
        $this->post('/mouvement/nouveau',  'MouvementController', 'processNew');
        $this->get('/mouvement/:id',       'MouvementController', 'detail');

        // ── Examens (squelette) ──────────────────────────────────────────────
        $this->get('/examen',              'ExamenController', 'index');
        $this->get('/examen/nouveau',      'ExamenController', 'newForm');
        $this->post('/examen/nouveau',     'ExamenController', 'processNew');
        $this->get('/examen/:id',          'ExamenController', 'detail');

        // ── Tableau de bord ──────────────────────────────────────────────────
        $this->get('/tableau-de-bord',     'DashboardController', 'index');
        $this->get('/dashboard',           'DashboardController', 'index');

        // ── Administration ───────────────────────────────────────────────────
        $this->get('/admin',               'AdminController', 'index');
        $this->get('/admin/sync',          'AdminController', 'syncStatus');
        $this->post('/admin/sync/lancer',  'AdminController', 'triggerSync');
        $this->get('/admin/import-excel',  'AdminController', 'importExcelForm');
        $this->post('/admin/import-excel', 'AdminController', 'processExcelImport');
        $this->get('/admin/users',         'AdminController', 'users');
        $this->get('/admin/audit',         'AdminController', 'auditLog');

        // ── API interne (agrégats) ───────────────────────────────────────────
        $this->get('/api/agregats',        'AggregatesApiController', 'index');
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
                    $_GET[$key] = $val;
                }
            }

            $this->callController($route['controller'], $route['action']);
            return;
        }

        // Aucune route trouvée → 404
        $this->notFound($uri);
    }

    private function callController(string $controllerName, string $action): void
    {
        // Chemin complet du fichier contrôleur
        $file = BASE_PATH . '/app/controllers/' . $controllerName . '.php';

        if (!file_exists($file)) {
            $this->error500("Contrôleur introuvable : $controllerName");
            return;
        }

        require_once $file;

        $fqcn = 'App\\Controllers\\' . $controllerName;
        if (!class_exists($fqcn)) {
            $this->error500("Classe introuvable : $fqcn");
            return;
        }

        $controller = new $fqcn();

        if (!method_exists($controller, $action)) {
            $this->error500("Méthode $action introuvable dans $fqcn");
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
        require BASE_PATH . '/app/views/errors/404.php';
    }

    private function error500(string $message): void
    {
        http_response_code(500);
        if (defined('FIE_DEBUG') && FIE_DEBUG) {
            echo '<pre style="padding:2rem;font-family:monospace;color:red">'
               . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            $page_title  = 'Erreur serveur (500)';
            $active_menu = '';
            require BASE_PATH . '/app/views/errors/500.php';
        }
    }
}
