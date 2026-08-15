<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= SecurityHelper::e($page_title ?? FIE_APP_NAME) ?> — FIE Burundi</title>

  <!-- Google Fonts : Poppins (titres) + Open Sans (corps) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CDN -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

  <!-- Font Awesome 6.5 CDN -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- Charte FIE Burundi (variables CSS + surcharges légères) -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie.css">

  <link rel="icon" href="<?= BASE_URL ?>/public/img/favicon.png" type="image/png">
</head>
<body class="fie-body">

<!-- ═══════════════════════════════════════════════════
     BARRE DE NAVIGATION PRINCIPALE
     Couleur primaire : #CE1126 (rouge Burundi)
     ═══════════════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg fie-navbar shadow-sm" role="navigation" aria-label="Navigation principale">
  <div class="container-xl">

    <!-- Marque / Logo -->
    <a class="navbar-brand fie-navbar__brand fw-bold" href="<?= BASE_URL ?>/">
      <span class="fie-flag-icon me-2" aria-hidden="true">🇧🇮</span>
      <span class="fie-navbar__title">
        <?= FIE_APP_SHORT ?> <span class="fie-navbar__sub fw-normal opacity-75">Burundi</span>
      </span>
    </a>

    <!-- Burger mobile -->
    <button class="navbar-toggler border-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#fieNavMenu"
            aria-controls="fieNavMenu" aria-expanded="false"
            aria-label="Ouvrir le menu">
      <i class="fa-solid fa-bars text-white fs-5"></i>
    </button>

    <!-- Menu collapsible -->
    <div class="collapse navbar-collapse" id="fieNavMenu">

      <?php if (SecurityHelper::isLoggedIn()): ?>
      <!-- Liens de navigation -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="nav-link fie-nav-link <?= ($active_menu ?? '') === 'inscription' ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/inscription/nouveau">
            <i class="fa-solid fa-user-plus me-1"></i> Inscription
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link fie-nav-link <?= ($active_menu ?? '') === 'recherche' ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/inscription/recherche">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Recherche
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link fie-nav-link <?= ($active_menu ?? '') === 'mouvement' ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/mouvement">
            <i class="fa-solid fa-arrows-left-right me-1"></i> Mouvements
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link fie-nav-link <?= ($active_menu ?? '') === 'examen' ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/examen">
            <i class="fa-solid fa-file-pen me-1"></i> Examens
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link fie-nav-link <?= ($active_menu ?? '') === 'dashboard' ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/tableau-de-bord">
            <i class="fa-solid fa-chart-line me-1"></i> Tableau de bord
          </a>
        </li>

        <?php if (in_array(SecurityHelper::userRole(), ['super_admin', 'admin_central'], true)): ?>
        <li class="nav-item">
          <a class="nav-link fie-nav-link <?= ($active_menu ?? '') === 'admin' ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/admin">
            <i class="fa-solid fa-gear me-1"></i> Admin
          </a>
        </li>
        <?php endif; ?>

      </ul>

      <!-- Zone utilisateur -->
      <div class="d-flex align-items-center gap-2 ms-lg-3">
        <span class="text-white-50 small d-none d-lg-inline">
          <i class="fa-solid fa-user-shield me-1 text-white opacity-75"></i>
          <span class="text-white opacity-90 fw-medium">
            <?= SecurityHelper::e(SecurityHelper::userNom() ?? SecurityHelper::userLogin() ?? '') ?>
          </span>
          <span class="badge fie-badge-role ms-1">
            <?= SecurityHelper::e(SecurityHelper::userRole() ?? '') ?>
          </span>
        </span>
        <a href="<?= BASE_URL ?>/deconnexion"
           class="btn btn-sm btn-outline-light fie-btn-logout rounded-pill px-3">
          <i class="fa-solid fa-right-from-bracket me-1"></i> Déconnexion
        </a>
      </div>

      <?php else: ?>

      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="btn btn-light btn-sm fie-btn-login ms-2 fw-semibold"
             href="<?= BASE_URL ?>/connexion">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Connexion
          </a>
        </li>
      </ul>

      <?php endif; ?>
    </div><!-- /.collapse -->
  </div><!-- /.container-xl -->
</nav>

<!-- Bande tricolore sous la navbar -->
<div class="fie-flag-strip" aria-hidden="true">
  <span class="fie-flag-strip__red"></span>
  <span class="fie-flag-strip__white"></span>
  <span class="fie-flag-strip__green"></span>
</div>

<!-- ═══════════════════════════════════════════════════
     MESSAGES FLASH (succès / erreur / avertissement)
     ═══════════════════════════════════════════════════ -->
<?php
$flashError   = $_SESSION['fie_flash_error']   ?? null;
$flashSuccess = $_SESSION['fie_flash_success'] ?? null;
$flashWarn    = $_SESSION['fie_flash_warn']    ?? null;
unset($_SESSION['fie_flash_error'], $_SESSION['fie_flash_success'], $_SESSION['fie_flash_warn']);
?>
<?php if ($flashError || $flashSuccess || $flashWarn): ?>
<div class="container-xl mt-3" id="fie-flash-zone">
  <?php if ($flashError): ?>
  <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert"
       data-fie-autohide="6000">
    <i class="fa-solid fa-circle-exclamation fa-lg flex-shrink-0"></i>
    <div><?= SecurityHelper::e($flashError) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
  <?php endif; ?>
  <?php if ($flashSuccess): ?>
  <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert"
       data-fie-autohide="5000">
    <i class="fa-solid fa-circle-check fa-lg flex-shrink-0"></i>
    <div><?= SecurityHelper::e($flashSuccess) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
  <?php endif; ?>
  <?php if ($flashWarn): ?>
  <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2" role="alert"
       data-fie-autohide="7000">
    <i class="fa-solid fa-triangle-exclamation fa-lg flex-shrink-0"></i>
    <div><?= SecurityHelper::e($flashWarn) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════
     CONTENU PRINCIPAL
     ═══════════════════════════════════════════════════ -->
<main class="fie-main container-xl py-4" id="fie-main-content" tabindex="-1">
