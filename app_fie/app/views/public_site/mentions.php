<?php
/**
 * Vue : Mentions légales — FIE Burundi
 * Bootstrap 5.3 CDN + Font Awesome 6.5 CDN
 * Charte Burundi : #CE1126 / #1EB53A / #FFFFFF
 */
$page_title  = 'Mentions légales — FIE Burundi';
$active_menu = '';
require BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <!-- En-tête de page -->
      <div class="d-flex align-items-center mb-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
             style="width:56px;height:56px;background:var(--fie-red,#CE1126);">
          <i class="fa-solid fa-scale-balanced fa-xl text-white"></i>
        </div>
        <div>
          <h1 class="h2 mb-0 fw-bold">Mentions légales</h1>
          <p class="text-muted mb-0">FIE — Fichier Informatisé des Élèves du Burundi</p>
        </div>
      </div>

      <!-- Bande drapeau décorative -->
      <div class="mb-4" style="height:4px;border-radius:2px;background:linear-gradient(to right,#CE1126 33%,#FFFFFF 33%,#FFFFFF 66%,#1EB53A 66%);"></div>

      <!-- Éditeur du service -->
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold text-white"
             style="background:var(--fie-red,#CE1126);">
          <i class="fa-solid fa-landmark me-2"></i>Éditeur du service
        </div>
        <div class="card-body p-0">
          <table class="table table-borderless mb-0">
            <tbody>
              <tr class="border-bottom">
                <th class="text-muted fw-normal ps-3 py-3" style="width:200px;white-space:nowrap;">Organisme</th>
                <td class="py-3 pe-3">
                  <strong>Ministère de l'Éducation Nationale et de la Recherche Scientifique (MENERS)</strong>
                </td>
              </tr>
              <tr class="border-bottom">
                <th class="text-muted fw-normal ps-3 py-3">Direction</th>
                <td class="py-3 pe-3">
                  Direction Générale des Études et des Statistiques Scolaires (DGESS) — SIGE Burundi
                </td>
              </tr>
              <tr class="border-bottom">
                <th class="text-muted fw-normal ps-3 py-3">Siège</th>
                <td class="py-3 pe-3">Bujumbura, République du Burundi</td>
              </tr>
              <tr>
                <th class="text-muted fw-normal ps-3 py-3">Statut juridique</th>
                <td class="py-3 pe-3">Service public de l'État du Burundi</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Application FIE -->
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold text-white"
             style="background:var(--fie-green,#1EB53A);">
          <i class="fa-solid fa-laptop-code me-2"></i>Application FIE
        </div>
        <div class="card-body p-0">
          <table class="table table-borderless mb-0">
            <tbody>
              <tr class="border-bottom">
                <th class="text-muted fw-normal ps-3 py-3" style="width:200px;white-space:nowrap;">Nom complet</th>
                <td class="py-3 pe-3">Fichier Informatisé des Élèves (FIE) — Composante SIGE Burundi</td>
              </tr>
              <tr class="border-bottom">
                <th class="text-muted fw-normal ps-3 py-3">Architecture</th>
                <td class="py-3 pe-3">
                  <span class="badge bg-secondary me-1">PHP 8.1+ MVC</span>
                  <span class="badge bg-secondary me-1">MySQL 8</span>
                  <span class="badge bg-primary me-1">Bootstrap 5.3</span>
                  <span class="badge" style="background:var(--fie-green,#1EB53A);">Font Awesome 6.5</span>
                </td>
              </tr>
              <tr class="border-bottom">
                <th class="text-muted fw-normal ps-3 py-3">Version</th>
                <td class="py-3 pe-3">
                  <span class="badge" style="background:var(--fie-red,#CE1126);">v1.0.0</span>
                  &nbsp;— janvier 2026
                </td>
              </tr>
              <tr class="border-bottom">
                <th class="text-muted fw-normal ps-3 py-3">Interopérabilité</th>
                <td class="py-3 pe-3">API REST StatEduc · SQL Server ELEVES_AGE_NIVEAU_SEXE · IUE ISO 7064 MOD 97-10</td>
              </tr>
              <tr>
                <th class="text-muted fw-normal ps-3 py-3">Dépôt source</th>
                <td class="py-3 pe-3">
                  <code>github.com/NasserKailou/stateduc_mobile</code>
                  &nbsp;·&nbsp; branche <code class="text-success">ak_app_ident</code>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Stack technique -->
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold" style="background:#f8f9fa;">
          <i class="fa-solid fa-layer-group me-2" style="color:var(--fie-red,#CE1126);"></i>
          Stack technique et licences tierces
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="d-flex align-items-start">
                <i class="fa-brands fa-bootstrap fa-lg text-primary me-3 mt-1 flex-shrink-0"></i>
                <div>
                  <strong class="small d-block">Bootstrap 5.3.3</strong>
                  <span class="text-muted" style="font-size:.8rem;">MIT License — cdn.jsdelivr.net</span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-start">
                <i class="fa-brands fa-font-awesome fa-lg text-info me-3 mt-1 flex-shrink-0"></i>
                <div>
                  <strong class="small d-block">Font Awesome 6.5.2</strong>
                  <span class="text-muted" style="font-size:.8rem;">Free License — cdnjs.cloudflare.com</span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-start">
                <i class="fa-brands fa-php fa-lg text-secondary me-3 mt-1 flex-shrink-0"></i>
                <div>
                  <strong class="small d-block">PHP 8.1+</strong>
                  <span class="text-muted" style="font-size:.8rem;">PHP License v3.01 — php.net</span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-start">
                <i class="fa-solid fa-database fa-lg text-warning me-3 mt-1 flex-shrink-0"></i>
                <div>
                  <strong class="small d-block">MySQL 8.0</strong>
                  <span class="text-muted" style="font-size:.8rem;">GPL v2 / Oracle — mysql.com</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Propriété intellectuelle -->
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold" style="background:#f8f9fa;">
          <i class="fa-solid fa-copyright me-2" style="color:var(--fie-red,#CE1126);"></i>
          Propriété intellectuelle
        </div>
        <div class="card-body">
          <p class="mb-2">
            L'application FIE et l'ensemble de ses composants (code source, bases de données,
            interfaces graphiques, documentation technique) sont la propriété exclusive du
            <strong>MENERS / DGESS</strong>.
          </p>
          <p class="mb-0 text-muted small">
            Toute reproduction, diffusion ou utilisation à des fins autres que l'administration
            scolaire burundaise est interdite sans autorisation expresse et écrite du MENERS.
          </p>
        </div>
      </div>

      <!-- Limitation de responsabilité -->
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold" style="background:#f8f9fa;">
          <i class="fa-solid fa-triangle-exclamation me-2 text-warning"></i>
          Limitation de responsabilité
        </div>
        <div class="card-body">
          <p class="mb-2 text-muted">
            Le MENERS s'efforce d'assurer l'exactitude et la mise à jour des informations
            diffusées sur cette application. Toutefois, des erreurs ou omissions peuvent subsister.
          </p>
          <p class="mb-0 text-muted">
            L'utilisateur assume l'entière responsabilité de l'utilisation faite des données.
            Le MENERS ne saurait être tenu responsable d'éventuels dommages directs ou indirects
            résultant de l'utilisation du service FIE ou de l'indisponibilité temporaire du système.
          </p>
        </div>
      </div>

      <div class="text-center text-muted small mb-3">
        <i class="fa-solid fa-flag me-1" style="color:var(--fie-green,#1EB53A);"></i>
        Mis à jour en janvier 2026 ·
        <a href="<?= BASE_URL ?>/confidentialite" class="text-decoration-none">Confidentialité</a> ·
        <a href="<?= BASE_URL ?>/contact" class="text-decoration-none">Contact</a>
      </div>

      <div class="text-center">
        <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary">
          <i class="fa-solid fa-arrow-left me-2"></i>Retour à l'accueil
        </a>
      </div>

    </div>
  </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
