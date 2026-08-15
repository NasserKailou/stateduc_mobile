<?php
/**
 * app_fie/services/SecurityHelper.php
 * Fonctions de sécurité : session, CSRF, XSS, authentification.
 *
 * CORRECTIONS PHASE 1 :
 *   - Ajout de startSession() (manquant → public/index.php appelait cette méthode inexistante)
 *   - requireLogin() : redirection corrigée vers BASE_URL . '/connexion' (était FIE_BASE_URL . 'auth/login')
 *   - login() : utilise colonne 'login' cohérente avec le schéma SQL (schema.sql) ;
 *     corrigé la confusion login/username (AuthController utilisait 'username' → incohérent)
 *   - logout() : supprimé session_start() après session_destroy() (causait warning)
 *   - jsonResponse() : ajout header CORS pour endpoints AJAX internes
 */

class SecurityHelper
{
    // ── Session ────────────────────────────────────────────────────────────────

    /**
     * Démarre la session sécurisée.
     * Appelé une seule fois depuis public/index.php.
     */
    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return; // Déjà démarrée
        }

        session_name(FIE_SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => FIE_SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => !FIE_DEBUG,   // HTTPS uniquement en production
            'httponly' => true,          // Inaccessible via JS → anti-XSS
            'samesite' => 'Lax',
        ]);
        session_start();

        // Régénération périodique de l'ID de session (anti-fixation)
        if (!isset($_SESSION['_fie_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_fie_initiated'] = true;
            $_SESSION['_fie_start']     = time();
        }

        // Rotation toutes les 30 minutes (anti-hijacking)
        if (isset($_SESSION['_fie_start']) && (time() - $_SESSION['_fie_start']) > 1800) {
            session_regenerate_id(true);
            $_SESSION['_fie_start'] = time();
        }
    }

    // ── Jeton CSRF ─────────────────────────────────────────────────────────────

    /**
     * Génère (ou retourne) le jeton CSRF stocké en session.
     */
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION[FIE_CSRF_TOKEN_NAME])) {
            $_SESSION[FIE_CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[FIE_CSRF_TOKEN_NAME];
    }

    /**
     * Vérifie le jeton CSRF envoyé dans le formulaire.
     */
    public static function verifyCsrf(?string $token): bool
    {
        $expected = $_SESSION[FIE_CSRF_TOKEN_NAME] ?? '';
        if (empty($token) || empty($expected)) return false;
        return hash_equals($expected, $token);
    }

    /**
     * Renouvelle le jeton CSRF (après soumission réussie).
     */
    public static function renewCsrf(): void
    {
        $_SESSION[FIE_CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }

    /**
     * Champ HTML caché contenant le jeton CSRF.
     */
    public static function csrfField(): string
    {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="' . FIE_CSRF_TOKEN_NAME
             . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    // ── Échappement XSS ────────────────────────────────────────────────────────

    /**
     * Échappe une chaîne pour affichage HTML (prévient XSS).
     * Alias court : SecurityHelper::e($val)
     */
    public static function e(?string $s): string
    {
        return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Valide et assainit une chaîne (strip_tags + trim + troncature).
     */
    public static function sanitizeStr(?string $s, int $maxLen = 255): string
    {
        $s = strip_tags(trim((string)($s ?? '')));
        return mb_substr($s, 0, $maxLen, 'UTF-8');
    }

    // ── Authentification ───────────────────────────────────────────────────────

    /**
     * Vérifie si l'utilisateur est connecté.
     * CORRECTION : utilise la clé de session 'fie_user' (définie dans AuthController::login)
     */
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['fie_user']['id'])
            && !empty($_SESSION['fie_user']['username'])
            && !empty($_SESSION['fie_user']['role']);
    }

    /**
     * Retourne l'ID de l'utilisateur connecté.
     */
    public static function userId(): ?int
    {
        return isset($_SESSION['fie_user']['id']) ? (int)$_SESSION['fie_user']['id'] : null;
    }

    /**
     * Retourne le login (username) de l'utilisateur connecté.
     */
    public static function userLogin(): ?string
    {
        return $_SESSION['fie_user']['username'] ?? null;
    }

    /**
     * Retourne le nom complet de l'utilisateur connecté.
     */
    public static function userNom(): ?string
    {
        return $_SESSION['fie_user']['nom'] ?? null;
    }

    /**
     * Retourne le rôle de l'utilisateur connecté.
     */
    public static function userRole(): ?string
    {
        return $_SESSION['fie_user']['role'] ?? null;
    }

    /**
     * Redirige vers la page de connexion si non connecté.
     * CORRECTION : URL corrigée de 'auth/login' vers '/connexion'
     */
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            $_SESSION['fie_redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }
    }

    /**
     * Vérifie que l'utilisateur a l'un des rôles requis.
     * Redirige vers 403 si insuffisant.
     */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array(self::userRole(), $roles, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/views/errors/403.php';
            exit;
        }
    }

    /**
     * Déconnecte l'utilisateur proprement.
     * CORRECTION : suppression du session_start() après session_destroy() (PHP warning)
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $p['path'], $p['domain'],
                $p['secure'], $p['httponly']
            );
        }
        session_destroy();
    }

    // ── IP et Headers ──────────────────────────────────────────────────────────

    /**
     * Retourne l'IP réelle du client (tient compte de X-Forwarded-For).
     */
    public static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                // Prendre seulement la première IP (en cas de liste X-Forwarded-For)
                return trim(explode(',', $_SERVER[$k])[0]);
            }
        }
        return '0.0.0.0';
    }

    // ── Validation ─────────────────────────────────────────────────────────────

    /**
     * Valide une date au format YYYY-MM-DD.
     */
    public static function validateDate(string $date): bool
    {
        if (empty($date)) return false;
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Valide un email.
     */
    public static function validateEmail(?string $email): bool
    {
        return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // ── Réponses JSON ──────────────────────────────────────────────────────────

    /**
     * Retourne la réponse JSON pour les requêtes AJAX et termine l'exécution.
     */
    public static function jsonResponse(array $data, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        // Anti-cache pour les endpoints AJAX
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit;
    }

    // ── Redirection sécurisée ──────────────────────────────────────────────────

    /**
     * Redirige vers une URL interne uniquement (évite open redirect).
     */
    public static function safeRedirect(string $url, string $fallback = ''): void
    {
        if ($fallback === '') {
            $fallback = BASE_URL . '/tableau-de-bord';
        }
        // N'autoriser que les chemins relatifs internes (/...) ou l'URL de base
        if (!empty($url) && str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $target = BASE_URL . $url;
        } else {
            $target = $fallback;
        }
        header('Location: ' . $target);
        exit;
    }
}
