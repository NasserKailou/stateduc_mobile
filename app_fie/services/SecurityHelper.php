<?php
/**
 * app_fie/services/SecurityHelper.php
 * Fonctions de sécurité : CSRF, XSS, authentification, rate limiting.
 */

class SecurityHelper
{
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
        return '<input type="hidden" name="' . FIE_CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    // ── Échappement XSS ────────────────────────────────────────────────────────

    /**
     * Échappe une chaîne pour affichage HTML (prévient XSS).
     */
    public static function e(?string $s): string
    {
        return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Valide et assainit une chaîne (strip_tags + trim).
     */
    public static function sanitizeStr(?string $s, int $maxLen = 255): string
    {
        $s = strip_tags(trim((string)($s ?? '')));
        return mb_substr($s, 0, $maxLen, 'UTF-8');
    }

    // ── Authentification ───────────────────────────────────────────────────────

    /**
     * Vérifie si l'utilisateur est connecté.
     */
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['fie_user_id'])
            && !empty($_SESSION['fie_user_login'])
            && !empty($_SESSION['fie_user_role']);
    }

    /**
     * Retourne l'ID de l'utilisateur connecté.
     */
    public static function userId(): ?int
    {
        return $_SESSION['fie_user_id'] ?? null;
    }

    /**
     * Retourne le login de l'utilisateur connecté.
     */
    public static function userLogin(): ?string
    {
        return $_SESSION['fie_user_login'] ?? null;
    }

    /**
     * Retourne le rôle de l'utilisateur connecté.
     */
    public static function userRole(): ?string
    {
        return $_SESSION['fie_user_role'] ?? null;
    }

    /**
     * Redirige vers la page de login si non connecté.
     */
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            $_SESSION['fie_redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
            header('Location: ' . FIE_BASE_URL . 'auth/login');
            exit;
        }
    }

    /**
     * Vérifie que l'utilisateur a l'un des rôles requis.
     */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array(self::userRole(), $roles, true)) {
            http_response_code(403);
            die('<h1>403 Accès refusé</h1>');
        }
    }

    /**
     * Connecte l'utilisateur en session après vérification du mot de passe.
     *
     * @return array|null  Données utilisateur ou null si échec
     */
    public static function login(string $login, string $password): ?array
    {
        $user = Database::fetchOne(
            "SELECT * FROM fie_users WHERE login = ? AND actif = 1",
            [trim($login)]
        );
        if (!$user) return null;

        // Vérification du verrouillage
        if ($user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
            return null; // Compte temporairement verrouillé
        }

        if (!password_verify($password, $user['password_hash'])) {
            // Incrémenter les tentatives échouées
            $fails = (int)$user['failed_login_count'] + 1;
            $lock  = $fails >= 5 ? date('Y-m-d H:i:s', time() + 900) : null; // 15 min après 5 échecs
            Database::query(
                "UPDATE fie_users SET failed_login_count=?, locked_until=? WHERE id=?",
                [$fails, $lock, $user['id']]
            );
            return null;
        }

        // Succès — mettre à jour les méta de connexion
        Database::query(
            "UPDATE fie_users SET failed_login_count=0, locked_until=NULL,
             last_login_at=NOW(), last_login_ip=? WHERE id=?",
            [$_SERVER['REMOTE_ADDR'] ?? '', $user['id']]
        );

        // Régénérer l'ID de session (anti-fixation)
        session_regenerate_id(true);

        $_SESSION['fie_user_id']    = (int)$user['id'];
        $_SESSION['fie_user_login'] = $user['login'];
        $_SESSION['fie_user_role']  = $user['role'];
        $_SESSION['fie_user_nom']   = $user['nom'];
        $_SESSION['fie_login_at']   = time();

        return $user;
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public static function logout(): void
    {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    // ── IP et Headers ──────────────────────────────────────────────────────────

    /**
     * Retourne l'IP réelle du client (tient compte de X-Forwarded-For).
     */
    public static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                return explode(',', $_SERVER[$k])[0];
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
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Valide un email.
     */
    public static function validateEmail(?string $email): bool
    {
        return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Retourne la réponse JSON pour les requêtes AJAX.
     */
    public static function jsonResponse(array $data, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
