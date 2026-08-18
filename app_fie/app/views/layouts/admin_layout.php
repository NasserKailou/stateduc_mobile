<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= SecurityHelper::e($page_title ?? 'Administration') ?> — FIE Admin</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        crossorigin="anonymous">

  <!-- Font Awesome 6.5 -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- AdminLTE 4 CDN -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css"
        crossorigin="anonymous">

  <!-- Charte FIE (variables + surcharges AdminLTE) -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie_admin.css">

  <link rel="icon" href="<?= BASE_URL ?>/public/img/favicon.png" type="image/png">

  <?php if (isset($extra_head)): echo $extra_head; endif; ?>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<!-- ╔══════════════════════════════════════════════════════════╗
     ║  WRAPPER ADMINLTE                                        ║
     ╚══════════════════════════════════════════════════════════╝ -->
<div class="app-wrapper">

  <!-- ── TOPBAR ─────────────────────────────────────────────── -->
  <nav class="app-header navbar navbar-expand" style="background:var(--fie-primary);">
    <div class="container-fluid">
      <!-- Sidebar toggle -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-white" data-lte-toggle="sidebar" href="#" role="button">
            <i class="fa-solid fa-bars"></i>
          </a>
        </li>
      </ul>

      <!-- Brand -->
      <a href="<?= BASE_URL ?>/admin" class="navbar-brand ms-2 text-white fw-bold">
        <span class="me-2">🇧🇮</span>FIE <span class="fw-light opacity-75">| Admin</span>
      </a>

      <!-- Right -->
      <ul class="navbar-nav ms-auto gap-2 align-items-center">
        <li class="nav-item d-none d-md-flex align-items-center">
          <span class="text-white opacity-75 small">
            <i class="fa-solid fa-user-shield me-1"></i>
            <?= SecurityHelper::e(SecurityHelper::userNom() ?? SecurityHelper::userLogin() ?? '') ?>
          </span>
          <span class="badge ms-2" style="background:var(--fie-accent)">
            <?= SecurityHelper::e(SecurityHelper::userRole() ?? '') ?>
          </span>
        </li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/deconnexion" class="btn btn-sm btn-outline-light rounded-pill px-3">
            <i class="fa-solid fa-right-from-bracket me-1"></i>Déconnexion
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- ── SIDEBAR ────────────────────────────────────────────── -->
  <aside class="app-sidebar shadow" style="background:#1e2a38;">
    <div class="sidebar-brand d-flex align-items-center py-3 px-3"
         style="border-bottom:1px solid rgba(255,255,255,.1);">
      <span class="me-2 fs-5">🇧🇮</span>
      <span class="text-white fw-bold">FIE Admin</span>
    </div>

    <div class="sidebar-wrapper">
      <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">

          <!-- Tableau de bord admin -->
          <li class="nav-item">
            <a href="<?= BASE_URL ?>/admin"
               class="nav-link <?= ($active_menu ?? '') === 'admin_home' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-gauge-high"></i>
              <p>Tableau de bord</p>
            </a>
          </li>

          <!-- Synchronisation -->
          <li class="nav-item">
            <a href="<?= BASE_URL ?>/admin/sync"
               class="nav-link <?= ($active_menu ?? '') === 'admin_sync' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-arrows-rotate"></i>
              <p>Synchronisation</p>
            </a>
          </li>

          <!-- Utilisateurs -->
          <li class="nav-item <?= in_array($active_menu ?? '', ['admin_users','admin_user_form'], true) ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link">
              <i class="nav-icon fa-solid fa-users-gear"></i>
              <p>Utilisateurs <i class="nav-arrow fa-solid fa-angle-right"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin/users"
                   class="nav-link <?= ($active_menu ?? '') === 'admin_users' ? 'active' : '' ?>">
                  <i class="nav-icon fa-regular fa-circle-dot"></i>
                  <p>Liste des utilisateurs</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin/users/nouveau"
                   class="nav-link <?= ($active_menu ?? '') === 'admin_user_form' ? 'active' : '' ?>">
                  <i class="nav-icon fa-regular fa-circle-dot"></i>
                  <p>Nouvel utilisateur</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- Bibliothèque admin -->
          <li class="nav-item">
            <a href="<?= BASE_URL ?>/bibliotheque/admin"
               class="nav-link <?= ($active_menu ?? '') === 'bibliotheque_admin' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-book-open"></i>
              <p>Bibliothèque</p>
            </a>
          </li>

          <!-- Import Excel -->
          <li class="nav-item">
            <a href="<?= BASE_URL ?>/admin/import-excel"
               class="nav-link <?= ($active_menu ?? '') === 'admin_import' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-file-excel"></i>
              <p>Import Excel</p>
            </a>
          </li>

          <!-- Paramètres -->
          <li class="nav-item">
            <a href="<?= BASE_URL ?>/admin/parametres"
               class="nav-link <?= ($active_menu ?? '') === 'admin_param' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-sliders"></i>
              <p>Paramètres</p>
            </a>
          </li>

          <!-- Journal d'audit -->
          <li class="nav-item">
            <a href="<?= BASE_URL ?>/admin/audit"
               class="nav-link <?= ($active_menu ?? '') === 'admin_audit' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-shield-halved"></i>
              <p>Journal d'audit</p>
            </a>
          </li>

          <li class="nav-item mt-3" style="border-top:1px solid rgba(255,255,255,.1);padding-top:.5rem;">
            <a href="<?= BASE_URL ?>/tableau-de-bord" class="nav-link">
              <i class="nav-icon fa-solid fa-arrow-left"></i>
              <p>Retour application</p>
            </a>
          </li>

        </ul>
      </nav>
    </div>
  </aside>

  <!-- ── CONTENU PRINCIPAL ──────────────────────────────────── -->
  <main class="app-main">
    <div class="app-content-header py-2">
      <div class="container-fluid">
        <!-- Fil d'Ariane dynamique si fourni -->
        <?php if (!empty($admin_breadcrumb)): ?>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Admin</a></li>
            <?php foreach ($admin_breadcrumb as $bc): ?>
            <?php if (!empty($bc['url'])): ?>
            <li class="breadcrumb-item"><a href="<?= SecurityHelper::e($bc['url']) ?>"><?= SecurityHelper::e($bc['label']) ?></a></li>
            <?php else: ?>
            <li class="breadcrumb-item active"><?= SecurityHelper::e($bc['label']) ?></li>
            <?php endif; ?>
            <?php endforeach; ?>
          </ol>
        </nav>
        <?php endif; ?>
      </div>
    </div>

    <div class="app-content">
      <div class="container-fluid">

        <!-- Flash messages -->
        <?php
        $flashError   = $_SESSION['fie_flash_error']   ?? null;
        $flashSuccess = $_SESSION['fie_flash_success'] ?? null;
        $flashWarn    = $_SESSION['fie_flash_warn']    ?? null;
        unset($_SESSION['fie_flash_error'], $_SESSION['fie_flash_success'], $_SESSION['fie_flash_warn']);
        ?>
        <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex gap-2" role="alert">
          <i class="fa-solid fa-circle-exclamation mt-1"></i>
          <div><?= SecurityHelper::e($flashError) ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex gap-2" role="alert">
          <i class="fa-solid fa-circle-check mt-1"></i>
          <div><?= SecurityHelper::e($flashSuccess) ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if ($flashWarn): ?>
        <div class="alert alert-warning alert-dismissible fade show d-flex gap-2" role="alert">
          <i class="fa-solid fa-triangle-exclamation mt-1"></i>
          <div><?= SecurityHelper::e($flashWarn) ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
