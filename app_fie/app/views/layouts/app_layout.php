<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= SecurityHelper::e($page_title ?? FIE_APP_NAME) ?> — FIE Burundi</title>

  <!-- Google Fonts : Source Sans 3 + Open Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        crossorigin="anonymous">

  <!-- Font Awesome 6.5 -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- AdminLTE 4.0-beta2 -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css"
        crossorigin="anonymous">

  <!-- Charte FIE -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie_admin.css">

  <link rel="icon" href="<?= BASE_URL ?>/public/img/favicon.png" type="image/png">

  <?php if (!empty($extra_head)): echo $extra_head; endif; ?>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<?php
// Rôle courant pour contrôle des menus
$_role     = SecurityHelper::userRole() ?? '';
$_isAdmin  = in_array($_role, ['super_admin', 'admin_central'], true);
$_isSuivi  = in_array($_role, ['super_admin', 'admin_central', 'directeur_ecole', 'enseignant'], true);
$_isBiblio = in_array($_role, ['super_admin', 'admin_central', 'bibliothecaire'], true);
$_am       = $active_menu ?? '';
?>

<div class="app-wrapper">

  <!-- ╔═══════════════════════════════════════════════════════╗
       ║  TOPBAR ADMINLTE                                      ║
       ╚═══════════════════════════════════════════════════════╝ -->
  <nav class="app-header navbar navbar-expand fie-app-topbar" style="background:var(--fie-primary);">
    <div class="container-fluid">

      <!-- Sidebar toggle + Brand -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-white px-2" data-lte-toggle="sidebar" href="#" role="button" title="Masquer/Afficher le menu">
            <i class="fa-solid fa-bars fa-lg"></i>
          </a>
        </li>
      </ul>

      <a href="<?= BASE_URL ?>/tableau-de-bord" class="navbar-brand ms-2 fw-bold text-white text-decoration-none d-flex align-items-center gap-2">
        <span style="font-size:1.4rem;" aria-hidden="true">🇧🇮</span>
        <span class="d-none d-sm-inline" style="font-family:'Source Sans 3',sans-serif;font-weight:700;font-size:1.15rem;">FIE</span>
        <span class="d-none d-md-inline opacity-75 fw-normal" style="font-size:.85rem;">| Burundi · SIGE</span>
      </a>

      <!-- Breadcrumb centre (md+) -->
      <?php if (!empty($app_breadcrumb)): ?>
      <nav aria-label="breadcrumb" class="d-none d-lg-flex align-items-center ms-3">
        <ol class="breadcrumb mb-0 bg-transparent" style="font-size:.78rem;">
          <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>/tableau-de-bord" class="text-white opacity-75 text-decoration-none">
              <i class="fa-solid fa-house me-1"></i>Accueil
            </a>
          </li>
          <?php foreach ($app_breadcrumb as $bc): ?>
          <?php if (!empty($bc['url'])): ?>
          <li class="breadcrumb-item">
            <a href="<?= SecurityHelper::e($bc['url']) ?>" class="text-white opacity-75 text-decoration-none">
              <?= SecurityHelper::e($bc['label']) ?>
            </a>
          </li>
          <?php else: ?>
          <li class="breadcrumb-item active text-white" aria-current="page">
            <?= SecurityHelper::e($bc['label']) ?>
          </li>
          <?php endif; ?>
          <?php endforeach; ?>
        </ol>
      </nav>
      <?php endif; ?>

      <!-- Droite : user info + déconnexion -->
      <ul class="navbar-nav ms-auto gap-1 align-items-center">

        <!-- Notification placeholder (extensible) -->
        <li class="nav-item d-none d-md-flex">
          <span class="nav-link text-white opacity-75 pe-none" style="font-size:.82rem;">
            <i class="fa-solid fa-user-shield me-1"></i>
            <strong><?= SecurityHelper::e(SecurityHelper::userNom() ?? SecurityHelper::userLogin() ?? '') ?></strong>
            <span class="badge ms-1 rounded-pill" style="background:var(--fie-accent);font-size:.65rem;">
              <?= SecurityHelper::e($_role) ?>
            </span>
          </span>
        </li>

        <?php if ($_isAdmin): ?>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/admin" class="nav-link text-white opacity-75" title="Administration">
            <i class="fa-solid fa-gear"></i>
          </a>
        </li>
        <?php endif; ?>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/deconnexion"
             class="btn btn-sm btn-outline-light rounded-pill px-3 ms-1">
            <i class="fa-solid fa-right-from-bracket me-1"></i>
            <span class="d-none d-sm-inline">Déconnexion</span>
          </a>
        </li>
      </ul>
    </div>
  </nav><!-- /.app-header -->


  <!-- ╔═══════════════════════════════════════════════════════╗
       ║  SIDEBAR ADMINLTE — MENU VERTICAL                     ║
       ╚═══════════════════════════════════════════════════════╝ -->
  <aside class="app-sidebar fie-sidebar shadow" style="background:#1a2636;">
    <!-- En-tête sidebar -->
    <div class="sidebar-brand px-3 py-3 d-flex align-items-center gap-2"
         style="border-bottom:1px solid rgba(255,255,255,.08);min-height:56px;">
      <div style="width:36px;height:36px;background:var(--fie-primary);border-radius:10px;
                  display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fa-solid fa-graduation-cap text-white" style="font-size:.9rem;"></i>
      </div>
      <div>
        <div class="text-white fw-bold" style="font-size:.9rem;line-height:1.2;">FIE Burundi</div>
        <div class="text-white opacity-50" style="font-size:.65rem;letter-spacing:.05em;">SIGE · DGESS</div>
      </div>
    </div>

    <div class="sidebar-wrapper">
      <nav class="mt-2 pb-4">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

          <!-- ── Tableau de bord ── -->
          <li class="nav-header" style="color:rgba(255,255,255,.35);font-size:.65rem;letter-spacing:.1em;padding:.8rem 1rem .3rem;">
            PRINCIPAL
          </li>
          <li class="nav-item">
            <a href="<?= BASE_URL ?>/tableau-de-bord"
               class="nav-link <?= in_array($_am, ['dashboard'], true) ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-chart-pie"></i>
              <p>Tableau de bord</p>
            </a>
          </li>

          <!-- ── Inscriptions ── -->
          <li class="nav-header" style="color:rgba(255,255,255,.35);font-size:.65rem;letter-spacing:.1em;padding:.8rem 1rem .3rem;">
            GESTION ÉLÈVES
          </li>

          <li class="nav-item <?= in_array($_am, ['inscription','inscription_nouveau','inscription_detail','impression'], true) ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link">
              <i class="nav-icon fa-solid fa-user-graduate"></i>
              <p>Inscriptions <i class="nav-arrow fa-solid fa-angle-right ms-auto"></i></p>
            </a>
            <ul class="nav nav-treeview ps-3">
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/inscription"
                   class="nav-link <?= $_am === 'inscription' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-list"></i><p>Liste</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/inscription/nouveau"
                   class="nav-link <?= $_am === 'inscription_nouveau' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-plus"></i><p>Nouvelle inscription</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/inscription/recherche"
                   class="nav-link <?= $_am === 'recherche' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-magnifying-glass"></i><p>Rechercher</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- ── Mouvements ── -->
          <li class="nav-item">
            <a href="<?= BASE_URL ?>/mouvement"
               class="nav-link <?= $_am === 'mouvement' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-arrows-left-right"></i>
              <p>Mouvements</p>
            </a>
          </li>

          <!-- ── Examens ── -->
          <li class="nav-item">
            <a href="<?= BASE_URL ?>/examen"
               class="nav-link <?= $_am === 'examen' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-file-pen"></i>
              <p>Examens</p>
            </a>
          </li>

          <?php if ($_isSuivi): ?>
          <!-- ── Suivi pédagogique ── -->
          <li class="nav-header" style="color:rgba(255,255,255,.35);font-size:.65rem;letter-spacing:.1em;padding:.8rem 1rem .3rem;">
            SUIVI &amp; PÉDAGOGIE
          </li>

          <li class="nav-item <?= in_array($_am, ['suivi','suivi_classe','transferts','transfert_form'], true) ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link">
              <i class="nav-icon fa-solid fa-chalkboard-teacher"></i>
              <p>Suivi pédagogique <i class="nav-arrow fa-solid fa-angle-right ms-auto"></i></p>
            </a>
            <ul class="nav nav-treeview ps-3">
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/suivi"
                   class="nav-link <?= $_am === 'suivi' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-users-class"></i><p>Classes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/suivi/transferts"
                   class="nav-link <?= $_am === 'transferts' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-right-left"></i><p>Transferts</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/suivi/transfert/nouveau"
                   class="nav-link <?= $_am === 'transfert_form' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-plus"></i><p>Demander transfert</p>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

          <!-- ── Bibliothèque ── -->
          <li class="nav-header" style="color:rgba(255,255,255,.35);font-size:.65rem;letter-spacing:.1em;padding:.8rem 1rem .3rem;">
            RESSOURCES
          </li>

          <li class="nav-item <?= in_array($_am, ['bibliotheque','bibliotheque_admin'], true) ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link">
              <i class="nav-icon fa-solid fa-book-open"></i>
              <p>Bibliothèque <i class="nav-arrow fa-solid fa-angle-right ms-auto"></i></p>
            </a>
            <ul class="nav nav-treeview ps-3">
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/bibliotheque"
                   class="nav-link <?= $_am === 'bibliotheque' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-book"></i><p>Consulter</p>
                </a>
              </li>
              <?php if ($_isBiblio): ?>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/bibliotheque/admin"
                   class="nav-link <?= $_am === 'bibliotheque_admin' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-gear"></i><p>Gérer</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/bibliotheque/admin/nouveau"
                   class="nav-link">
                  <i class="nav-icon fa-solid fa-plus"></i><p>Publier document</p>
                </a>
              </li>
              <?php endif; ?>
            </ul>
          </li>

          <?php if ($_isAdmin): ?>
          <!-- ── Administration ── -->
          <li class="nav-header" style="color:rgba(255,255,255,.35);font-size:.65rem;letter-spacing:.1em;padding:.8rem 1rem .3rem;">
            ADMINISTRATION
          </li>

          <li class="nav-item <?= in_array($_am, ['admin_home','admin_sync','admin_import','admin_import_eleves','admin_param','admin_audit'], true) ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link">
              <i class="nav-icon fa-solid fa-screwdriver-wrench"></i>
              <p>Système <i class="nav-arrow fa-solid fa-angle-right ms-auto"></i></p>
            </a>
            <ul class="nav nav-treeview ps-3">
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin"
                   class="nav-link <?= $_am === 'admin_home' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-gauge-high"></i><p>Dashboard admin</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin/sync"
                   class="nav-link <?= $_am === 'admin_sync' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-arrows-rotate"></i><p>Synchronisation</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin/import-excel"
                   class="nav-link <?= $_am === 'admin_import' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-file-excel"></i><p>Import établissements</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin/import-eleves"
                   class="nav-link <?= $_am === 'admin_import_eleves' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-users"></i><p>Import élèves (IUE)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin/parametres"
                   class="nav-link <?= $_am === 'admin_param' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-sliders"></i><p>Paramètres</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin/audit"
                   class="nav-link <?= $_am === 'admin_audit' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-shield-halved"></i><p>Audit</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item <?= in_array($_am, ['admin_users','admin_user_form'], true) ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link">
              <i class="nav-icon fa-solid fa-users-gear"></i>
              <p>Utilisateurs <i class="nav-arrow fa-solid fa-angle-right ms-auto"></i></p>
            </a>
            <ul class="nav nav-treeview ps-3">
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin/users"
                   class="nav-link <?= $_am === 'admin_users' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-list"></i><p>Liste</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>/admin/users/nouveau"
                   class="nav-link <?= $_am === 'admin_user_form' ? 'active' : '' ?>">
                  <i class="nav-icon fa-solid fa-plus"></i><p>Nouvel utilisateur</p>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

          <!-- ── Site public ── -->
          <li class="nav-item mt-2" style="border-top:1px solid rgba(255,255,255,.07);padding-top:.5rem;">
            <a href="<?= BASE_URL ?>/" class="nav-link opacity-75">
              <i class="nav-icon fa-solid fa-globe"></i>
              <p>Site public</p>
            </a>
          </li>

        </ul><!-- /.sidebar-menu -->
      </nav>
    </div><!-- /.sidebar-wrapper -->
  </aside><!-- /.app-sidebar -->


  <!-- ╔═══════════════════════════════════════════════════════╗
       ║  CONTENU PRINCIPAL                                    ║
       ╚═══════════════════════════════════════════════════════╝ -->
  <main class="app-main">
    <div class="app-content-header py-2 px-3" style="background:#f4f6f9;border-bottom:1px solid #e2e8f0;">
      <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between">
          <h4 class="mb-0 fw-semibold" style="font-size:1.05rem;color:#2c3e50;">
            <?= SecurityHelper::e($page_title ?? '') ?>
          </h4>
          <?php if (!empty($app_breadcrumb)): ?>
          <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-0 small" style="font-size:.78rem;">
              <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/tableau-de-bord" class="text-decoration-none">
                  <i class="fa-solid fa-house me-1"></i>Accueil
                </a>
              </li>
              <?php foreach ($app_breadcrumb as $bc): ?>
              <?php if (!empty($bc['url'])): ?>
              <li class="breadcrumb-item">
                <a href="<?= SecurityHelper::e($bc['url']) ?>" class="text-decoration-none">
                  <?= SecurityHelper::e($bc['label']) ?>
                </a>
              </li>
              <?php else: ?>
              <li class="breadcrumb-item active" aria-current="page">
                <?= SecurityHelper::e($bc['label']) ?>
              </li>
              <?php endif; ?>
              <?php endforeach; ?>
            </ol>
          </nav>
          <?php endif; ?>
        </div>
      </div>
    </div><!-- /.app-content-header -->

    <div class="app-content px-3 pt-3">
      <div class="container-fluid">

        <!-- Flash messages -->
        <?php
        $flashError   = $_SESSION['fie_flash_error']   ?? null;
        $flashSuccess = $_SESSION['fie_flash_success'] ?? null;
        $flashWarn    = $_SESSION['fie_flash_warn']    ?? null;
        unset($_SESSION['fie_flash_error'], $_SESSION['fie_flash_success'], $_SESSION['fie_flash_warn']);
        ?>
        <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
          <i class="fa-solid fa-circle-exclamation fa-lg flex-shrink-0"></i>
          <div><?= SecurityHelper::e($flashError) ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
          <i class="fa-solid fa-circle-check fa-lg flex-shrink-0"></i>
          <div><?= SecurityHelper::e($flashSuccess) ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php endif; ?>
        <?php if ($flashWarn): ?>
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
          <i class="fa-solid fa-triangle-exclamation fa-lg flex-shrink-0"></i>
          <div><?= SecurityHelper::e($flashWarn) ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php endif; ?>
