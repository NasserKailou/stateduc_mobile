<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= SecurityHelper::e($title ?? FIE_APP_NAME) ?> | FIE Burundi</title>
  <link rel="stylesheet" href="<?= FIE_BASE_URL ?>public/css/fie.css">
  <link rel="icon" href="<?= FIE_BASE_URL ?>public/img/favicon.png" type="image/png">
</head>
<body class="fie-app">

<!-- ── Barre de navigation ───────────────────────────────────────────────── -->
<nav class="fie-navbar" role="navigation">
  <div class="fie-navbar__brand">
    <a href="<?= FIE_BASE_URL ?>">
      <span class="fie-navbar__logo">🇧🇮</span>
      <span class="fie-navbar__title"><?= FIE_APP_SHORT ?> <em>Burundi</em></span>
    </a>
  </div>

  <?php if (SecurityHelper::isLoggedIn()): ?>
  <ul class="fie-navbar__menu">
    <li><a href="<?= FIE_BASE_URL ?>inscription/new">✏ Inscription</a></li>
    <li><a href="<?= FIE_BASE_URL ?>inscription/search">🔍 Recherche</a></li>
    <li><a href="<?= FIE_BASE_URL ?>mouvement">↔ Mouvements</a></li>
    <li><a href="<?= FIE_BASE_URL ?>examen">📝 Examens</a></li>
    <li><a href="<?= FIE_BASE_URL ?>dashboard">📊 Tableau de bord</a></li>
    <?php if (in_array(SecurityHelper::userRole(), ['super_admin','admin_central'])): ?>
    <li><a href="<?= FIE_BASE_URL ?>admin">⚙ Admin</a></li>
    <?php endif; ?>
  </ul>
  <div class="fie-navbar__user">
    <span>👤 <?= SecurityHelper::e(SecurityHelper::userLogin() ?? '') ?></span>
    <a href="<?= FIE_BASE_URL ?>auth/logout" class="fie-btn fie-btn--ghost fie-btn--sm">Déconnexion</a>
  </div>
  <?php else: ?>
  <div class="fie-navbar__user">
    <a href="<?= FIE_BASE_URL ?>auth/login" class="fie-btn fie-btn--primary fie-btn--sm">Connexion</a>
  </div>
  <?php endif; ?>
</nav>

<!-- ── Contenu principal ─────────────────────────────────────────────────── -->
<main class="fie-main" id="fie-main-content">
