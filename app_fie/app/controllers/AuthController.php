<?php
/**
 * app_fie/app/controllers/AuthController.php
 * Gestion connexion / déconnexion.
 *
 * CORRECTIONS PHASE 1 :
 *   - Suppression du namespace App\Controllers (pas de PSR-4 complet dans ce projet)
 *   - Suppression des use App\... (autoloader simple)
 *   - Colonne de connexion : `login` (conforme à schema.sql fie_users)
 *     Ancienne erreur : utilisait `username` → SQL SELECT échouait silencieusement
 *   - Session stockée dans $_SESSION['fie_user'] (clé cohérente avec SecurityHelper::isLoggedIn)
 *   - Logger::info/warning : signature corrigée (1 seul argument string)
 *   - MAX_LOGIN_ATTEMPTS / LOGIN_LOCKOUT_SECONDS désormais dans config.php
 *   - Redirection post-login via SecurityHelper::safeRedirect()
 *   - derniere_connexion renommé last_login_at (conforme schema.sql)
 */

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
        // Si déjà connecté → tableau de bord
        if (SecurityHelper::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/tableau-de-bord');
            exit;
        }

        $page_title  = 'Connexion — FIE';
        $active_menu = '';
        $error       = $_SESSION['auth_error']    ?? null;
        $username    = $_SESSION['auth_username'] ?? '';

        unset($_SESSION['auth_error'], $_SESSION['auth_username']);

        require BASE_PATH . '/app/views/auth/login.php';
    }

    /* ── POST /connexion ─────────────────────────────────────────────────── */

    public function login(): void
    {
        // Vérification CSRF
        $token = $_POST[FIE_CSRF_TOKEN_NAME] ?? ($_POST['csrf_token'] ?? '');
        if (!SecurityHelper::verifyCsrf($token)) {
            $_SESSION['auth_error'] = 'Jeton de sécurité invalide. Veuillez réessayer.';
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']      ?? '';
        $redirect = $_POST['redirect']      ?? '';

        // Conserver le username en session (re-remplissage du champ après erreur)
        $_SESSION['auth_username'] = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

        if (empty($username) || empty($password)) {
            $_SESSION['auth_error'] = 'Identifiant et mot de passe requis.';
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        // ── Vérification brute-force (session) ────────────────────────────
        $lockKey   = 'bf_' . md5($username . SecurityHelper::clientIp());
        $attempts  = (int)($_SESSION[$lockKey . '_count'] ?? 0);
        $lockUntil = (int)($_SESSION[$lockKey . '_until'] ?? 0);

        if ($lockUntil > time()) {
            $wait = ceil(($lockUntil - time()) / 60);
            $_SESSION['auth_error'] = "Compte temporairement bloqué. Réessayez dans {$wait} minute(s).";
            $this->log->warning("Tentative connexion en période blocage | login=$username ip=" . SecurityHelper::clientIp());
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        // ── Recherche de l'utilisateur (colonne : login) ──────────────────
        // CORRECTION : schéma SQL utilise `login`, pas `username`
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM fie_users WHERE login = ? AND actif = 1 LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $attempts++;
            $_SESSION[$lockKey . '_count'] = $attempts;

            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $_SESSION[$lockKey . '_until'] = time() + LOGIN_LOCKOUT_SECONDS;
                $_SESSION[$lockKey . '_count'] = 0;
                $this->log->warning("Compte bloqué après $attempts tentatives | login=$username");
                $msg = "Trop de tentatives échouées. Compte bloqué "
                     . ceil(LOGIN_LOCKOUT_SECONDS / 60) . " minute(s).";
            } else {
                $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
                $msg = "Identifiant ou mot de passe incorrect. "
                     . ($remaining > 0 ? "$remaining tentative(s) restante(s)." : '');
            }

            $_SESSION['auth_error'] = $msg;
            $this->log->warning("Échec connexion | login=$username tentatives=$attempts");
            header('Location: ' . BASE_URL . '/connexion');
            exit;
        }

        // ── Connexion réussie ─────────────────────────────────────────────
        unset($_SESSION[$lockKey . '_count'], $_SESSION[$lockKey . '_until']);

        // Régénérer l'ID de session (protection fixation de session)
        session_regenerate_id(true);

        // Mise à jour last_login_at (conforme schema.sql)
        $pdo->prepare(
            "UPDATE fie_users SET last_login_at=NOW(), last_login_ip=?, failed_login_count=0, locked_until=NULL WHERE id=?"
        )->execute([SecurityHelper::clientIp(), $user['id']]);

        // ── Stocker l'identité en session ────────────────────────────────
        // Clé 'fie_user' attendue par SecurityHelper::isLoggedIn()
        $_SESSION['fie_user'] = [
            'id'       => (int)$user['id'],
            'username' => $user['login'],          // expose 'username' pour SecurityHelper::userLogin()
            'nom'      => $user['nom'] . (isset($user['prenoms']) ? ' ' . $user['prenoms'] : ''),
            'role'     => $user['role'],
            'province' => $user['province_perimetre'] ?? null,
            'etab'     => $user['code_etablissement'] ?? null,
            'must_change_password' => (bool)($user['must_change_password'] ?? false),
        ];

        // Renouveler le jeton CSRF
        SecurityHelper::renewCsrf();

        $this->log->info("Connexion réussie | login={$user['login']} id={$user['id']}");
        unset($_SESSION['auth_username']);

        // ── Redirection post-connexion ────────────────────────────────────
        if (!empty($redirect) && str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) {
            header('Location: ' . BASE_URL . $redirect);
        } else {
            header('Location: ' . BASE_URL . '/tableau-de-bord');
        }
        exit;
    }

    /* ── GET|POST /deconnexion ───────────────────────────────────────────── */

    public function logout(): void
    {
        $login = SecurityHelper::userLogin() ?? '(anonyme)';
        $this->log->info("Déconnexion | login=$login");

        SecurityHelper::logout();

        header('Location: ' . BASE_URL . '/connexion?deconnecte=1');
        exit;
    }
}
