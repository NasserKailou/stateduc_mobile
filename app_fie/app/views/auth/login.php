<?php
/**
 * FIE — Vue : Formulaire de connexion
 * Rendu par AuthController::loginForm()
 * Variables : $error (string|null), $username (string)
 */
use App\Services\SecurityHelper;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Connexion — FIE', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie.css">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>

<div class="fie-auth-page">
    <div class="fie-auth-card">

        <!-- Logo -->
        <div class="fie-auth-logo">
            <div class="fie-auth-flag" aria-hidden="true">
                <span class="fie-auth-flag__red"></span>
                <span class="fie-auth-flag__white"></span>
                <span class="fie-auth-flag__green"></span>
            </div>
            <div class="fie-auth-logo__title">FIE</div>
            <p class="fie-auth-logo__subtitle">
                Fichier Informatisé des Élèves<br>
                <small>SIGE Burundi — MENERS</small>
            </p>
        </div>

        <h1 class="fie-auth-title">Connexion</h1>

        <!-- Message de déconnexion -->
        <?php if (isset($_GET['deconnecte'])): ?>
            <div class="fie-alert fie-alert--info" data-autohide="4000" role="alert">
                <svg class="fie-alert__icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48
                             10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <div class="fie-alert__body">Vous avez été déconnecté(e).</div>
            </div>
        <?php endif; ?>

        <!-- Erreur de connexion -->
        <?php if ($error): ?>
            <div class="fie-alert fie-alert--error" role="alert">
                <svg class="fie-alert__icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48
                             10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <div class="fie-alert__body">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <form method="post" action="<?= BASE_URL ?>/connexion" class="fie-form" novalidate>
            <?= SecurityHelper::csrfField() ?>

            <?php if (!empty($_GET['redirect'])): ?>
                <input type="hidden" name="redirect"
                       value="<?= htmlspecialchars($_GET['redirect'], ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>

            <!-- Identifiant -->
            <div class="fie-form-group">
                <label for="username" class="fie-label fie-label--required">
                    Identifiant
                </label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="fie-input"
                    value="<?= htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="username"
                    autofocus
                    required
                    placeholder="Votre identifiant FIE"
                    aria-required="true"
                >
            </div>

            <!-- Mot de passe -->
            <div class="fie-form-group">
                <label for="password" class="fie-label fie-label--required">
                    Mot de passe
                </label>
                <div style="position:relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="fie-input"
                        autocomplete="current-password"
                        required
                        placeholder="••••••••"
                        aria-required="true"
                        style="padding-right: 2.8rem"
                    >
                    <!-- Bouton afficher/masquer mot de passe -->
                    <button
                        type="button"
                        id="togglePwd"
                        aria-label="Afficher/masquer le mot de passe"
                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                               background:none;border:none;cursor:pointer;color:var(--fie-gray-500);
                               padding:4px;"
                    >
                        <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11
                                     11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5
                                     5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3
                                     3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Soumettre -->
            <button type="submit" class="fie-btn fie-btn--primary fie-btn--lg"
                    style="width:100%;margin-top:var(--fie-space-2)">
                Se connecter
            </button>
        </form>

        <!-- Mention légale -->
        <p style="margin-top:var(--fie-space-6);font-size:var(--fie-font-size-xs);
                  color:var(--fie-gray-500);text-align:center;line-height:1.6">
            Accès réservé aux agents autorisés.<br>
            Conforme à la loi n°1/03-2026 sur la protection des données.
        </p>

    </div><!-- /.fie-auth-card -->
</div><!-- /.fie-auth-page -->

<script>
(function() {
    var btn = document.getElementById('togglePwd');
    var pwd = document.getElementById('password');
    if (!btn || !pwd) return;
    btn.addEventListener('click', function() {
        var visible = pwd.type === 'text';
        pwd.type = visible ? 'password' : 'text';
        btn.setAttribute('aria-label',
            visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
    });
}());
</script>

</body>
</html>
