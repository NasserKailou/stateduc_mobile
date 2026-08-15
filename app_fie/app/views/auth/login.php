<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Connexion — FIE', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- Charte FIE -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie.css">
    <link rel="icon" href="<?= BASE_URL ?>/public/img/favicon.png" type="image/png">
</head>
<body>

<div class="fie-login-page">
  <div class="fie-login-card">

    <!-- Logo / En-tête -->
    <div class="text-center mb-4">
      <div class="fie-auth-flag" aria-hidden="true">
        <span class="fie-auth-flag__red"></span>
        <span class="fie-auth-flag__white"></span>
        <span class="fie-auth-flag__green"></span>
      </div>
      <div class="fie-login-logo-title">FIE</div>
      <p class="fie-login-logo-sub mt-1 mb-0">
        Fichier Informatisé des Élèves<br>
        <small class="text-muted">SIGE Burundi — MENERS</small>
      </p>
    </div>

    <h1 class="h5 text-center fw-semibold mb-4" style="color:#343a40">
      <i class="fa-solid fa-right-to-bracket me-2" style="color:var(--fie-red)"></i>Connexion
    </h1>

    <!-- Message de déconnexion -->
    <?php if (isset($_GET['deconnecte'])): ?>
    <div class="alert alert-info d-flex align-items-center gap-2 py-2 small" role="alert">
      <i class="fa-solid fa-circle-info flex-shrink-0"></i>
      <span>Vous avez été déconnecté(e) avec succès.</span>
    </div>
    <?php endif; ?>

    <!-- Message d'erreur -->
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-start gap-2 py-2 small" role="alert">
      <i class="fa-solid fa-circle-exclamation flex-shrink-0 mt-1"></i>
      <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <?php endif; ?>

    <!-- Formulaire de connexion -->
    <form method="post" action="<?= BASE_URL ?>/connexion" novalidate>
      <?= SecurityHelper::csrfField() ?>

      <?php if (!empty($_GET['redirect'])): ?>
      <input type="hidden" name="redirect"
             value="<?= htmlspecialchars($_GET['redirect'], ENT_QUOTES, 'UTF-8') ?>">
      <?php endif; ?>

      <!-- Identifiant -->
      <div class="mb-3">
        <label for="username" class="form-label fw-medium">
          <i class="fa-solid fa-user me-1 text-muted"></i>Identifiant
          <span class="text-danger">*</span>
        </label>
        <input
          type="text"
          id="username"
          name="username"
          class="form-control<?= !empty($error) ? ' is-invalid' : '' ?>"
          value="<?= htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8') ?>"
          autocomplete="username"
          autofocus
          required
          placeholder="Votre identifiant FIE"
          aria-required="true"
        >
      </div>

      <!-- Mot de passe -->
      <div class="mb-4">
        <label for="password" class="form-label fw-medium">
          <i class="fa-solid fa-lock me-1 text-muted"></i>Mot de passe
          <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <input
            type="password"
            id="password"
            name="password"
            class="form-control<?= !empty($error) ? ' is-invalid' : '' ?>"
            autocomplete="current-password"
            required
            placeholder="••••••••"
            aria-required="true"
          >
          <button class="btn btn-outline-secondary" type="button" id="togglePwd"
                  aria-label="Afficher/masquer le mot de passe" tabindex="-1">
            <i class="fa-solid fa-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <!-- Soumettre -->
      <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg fw-semibold">
          <i class="fa-solid fa-right-to-bracket me-2"></i>Se connecter
        </button>
      </div>
    </form>

    <!-- Mention légale -->
    <p class="mt-4 mb-0 text-center small text-muted" style="line-height:1.5">
      <i class="fa-solid fa-shield-halved me-1"></i>
      Accès réservé aux agents autorisés.<br>
      Conforme à la loi n°1/03-2026 sur la protection des données.
    </p>

  </div><!-- /.fie-login-card -->
</div><!-- /.fie-login-page -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmU1EspL3xfC8wMw1ECNEkOsEHGU"
        crossorigin="anonymous"></script>
<script>
(function() {
    var btn = document.getElementById('togglePwd');
    var pwd = document.getElementById('password');
    var ico = document.getElementById('eyeIcon');
    if (!btn || !pwd) return;
    btn.addEventListener('click', function() {
        var show = pwd.type === 'password';
        pwd.type = show ? 'text' : 'password';
        ico.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    });
}());
</script>
</body>
</html>
