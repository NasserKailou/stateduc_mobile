<?php
/**
 * FIE — AuthController
 * Gestion connexion / déconnexion avec protection brute-force
 * Dépendances : SecurityHelper, config/Database.php
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SecurityHelper;
use App\Config\Database;
use App\Services\Logger;

class AuthController
{
    private Logger $log;

    public function __construct()
    {
        $this->log = new Logger('auth');
    }

    /* ── GET /connexion ──────────────────────────────────────────────────── */

    public function loginForm(): void
    {
        // Si déjà connecté, rediriger
        if (SecurityHelper::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/tableau-de-bord');
            exit;
        }

        $page_title  = 'Connexion — FIE';
        $active_menu = '';
        $error       = $_SESSION['auth_error']  ?? null;
        $username    = $_SESSION['auth_username'] ?? '';

        unset($_SESSION['auth_error'], $_SESSION['auth_username']);

        require BASE_PATH . '/app/views/auth/login.php';
    }

    /* ── POST /connexion ─────────────────────────────────────────────────── */

    public function login(): void
    {
        // Vérification CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!SecurityHelper::verifyCsrf($token)) {
            $_SESSION['auth_error'] = 'Jeton de sécurité invalide. Veuillez réessayer.';
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = $_POST['redirect'] ?? '';

        // Conserver le username en cas d'erreur
        $_SESSION['auth_username'] = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

        if (empty($username) || empty($password)) {
            $_SESSION['auth_error'] = 'Identifiant et mot de passe requis.';
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        // ── Vérification brute-force ───────────────────────────────────────
        $lockKey  = 'bf_' . md5($username . SecurityHelper::clientIp());
        $attempts = (int)($_SESSION[$lockKey . '_count'] ?? 0);
        $lockUntil = (int)($_SESSION[$lockKey . '_until'] ?? 0);

        if ($lockUntil > time()) {
            $wait = ceil(($lockUntil - time()) / 60);
            $_SESSION['auth_error'] = "Compte temporairement bloqué. Réessayez dans $wait minute(s).";
            $this->log->warning("Tentative de connexion en période de blocage", [
                'username' => $username, 'ip' => SecurityHelper::clientIp()
            ]);
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        // ── Recherche de l'utilisateur ────────────────────────────────────
        $db   = Database::getInstance();
        $user = $db->fetchOne(
            "SELECT * FROM fie_users WHERE username = ? AND actif = 1 LIMIT 1",
            [$username]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $attempts++;
            $_SESSION[$lockKey . '_count'] = $attempts;

            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $_SESSION[$lockKey . '_until'] = time() + LOGIN_LOCKOUT_SECONDS;
                $_SESSION[$lockKey . '_count'] = 0;
                $this->log->warning("Compte bloqué après $attempts tentatives", [
                    'username' => $username, 'ip' => SecurityHelper::clientIp()
                ]);
                $msg = "Trop de tentatives échouées. Compte bloqué "
                     . ceil(LOGIN_LOCKOUT_SECONDS / 60) . " minute(s).";
            } else {
                $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
                $msg = "Identifiant ou mot de passe incorrect. "
                     . ($remaining > 0 ? "$remaining tentative(s) restante(s)." : '');
            }

            $_SESSION['auth_error'] = $msg;
            $this->log->warning("Échec de connexion", [
                'username' => $username, 'ip' => SecurityHelper::clientIp(), 'attempts' => $attempts
            ]);
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        // ── Connexion réussie ─────────────────────────────────────────────
        unset($_SESSION[$lockKey . '_count'], $_SESSION[$lockKey . '_until']);

        // Régénérer l'ID de session (protection fixation)
        session_regenerate_id(true);

        // Mise à jour de la dernière connexion
        $db->query(
            "UPDATE fie_users SET derniere_connexion = NOW() WHERE id = ?",
            [$user['id']]
        );

        // Stocker l'identité en session
        $_SESSION['fie_user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'nom'      => $user['nom'],
            'prenom'   => $user['prenom'],
            'role'     => $user['role'],
            'province' => $user['province_code'] ?? null,
        ];

        // Renouveler le jeton CSRF après connexion
        SecurityHelper::renewCsrf();

        $this->log->info("Connexion réussie", [
            'user_id' => $user['id'], 'username' => $username
        ]);

        unset($_SESSION['auth_username']);

        // Redirection post-connexion
        $safe = $this->safeRedirect($redirect);
        header('Location: ' . $safe);
        exit;
    }

    /* ── GET|POST /deconnexion ───────────────────────────────────────────── */

    public function logout(): void
    {
        $userId = $_SESSION['fie_user']['id'] ?? null;
        if ($userId) {
            $this->log->info("Déconnexion", ['user_id' => $userId]);
        }

        SecurityHelper::logout();

        header('Location: ' . BASE_URL . '/connexion?deconnecte=1');
        exit;
    }

    /* ── Helpers privés ──────────────────────────────────────────────────── */

    /**
     * Valide et retourne une URL de redirection sûre (interne uniquement).
     */
    private function safeRedirect(string $url): string
    {
        // N'autoriser que les chemins internes
        if (!empty($url) && str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return BASE_URL . $url;
        }
        return BASE_URL . '/tableau-de-bord';
    }
}
