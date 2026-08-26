<?php
/**
 * Vue : Politique de confidentialité — FIE Burundi
 * Bootstrap 5.3 CDN + Font Awesome 6.5 CDN
 * Charte Burundi : #CE1126 / #1EB53A / #FFFFFF
 */
$page_title  = 'Politique de confidentialité — FIE Burundi';
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
          <i class="fa-solid fa-shield-halved fa-xl text-white"></i>
        </div>
        <div>
          <h1 class="h2 mb-0 fw-bold">Politique de confidentialité</h1>
          <p class="text-muted mb-0">FIE — Fichier Informatisé des Élèves · Version janvier 2026</p>
        </div>
      </div>

      <!-- Bande drapeau décorative -->
      <div class="mb-4" style="height:4px;border-radius:2px;background:linear-gradient(to right,#CE1126 33%,#FFFFFF 33%,#FFFFFF 66%,#1EB53A 66%);"></div>

      <!-- Alerte base légale -->
      <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
        <i class="fa-solid fa-gavel fa-lg me-3 mt-1 flex-shrink-0"></i>
        <div>
          <strong>Base légale</strong><br>
          Conformément à la <em>loi n°1/03-2026</em> relative à la protection des données
          à caractère personnel au Burundi, toutes les données collectées par le FIE sont
          traitées sous la responsabilité du <strong>Ministère de l'Éducation Nationale
          et de la Recherche Scientifique (MENERS)</strong> — DGESS/SIGE Burundi.
        </div>
      </div>

      <!-- Section 1 : Données collectées -->
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold d-flex align-items-center" style="background:#f8f9fa;">
          <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2 flex-shrink-0"
                style="width:28px;height:28px;background:var(--fie-red,#CE1126);font-size:.85rem;color:#fff;font-weight:700;">1</span>
          Données collectées
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            Le FIE collecte uniquement les données strictement nécessaires à l'immatriculation scolaire :
          </p>
          <div class="row g-2">
            <div class="col-md-6">
              <div class="d-flex align-items-center p-2 rounded" style="background:#f8f9fa;">
                <i class="fa-solid fa-user me-2" style="color:var(--fie-red,#CE1126);width:20px;"></i>
                <span class="small">Identité civile de l'élève (nom, prénom, date/lieu naissance, sexe)</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center p-2 rounded" style="background:#f8f9fa;">
                <i class="fa-solid fa-people-roof me-2 text-primary" style="width:20px;"></i>
                <span class="small">Informations du tuteur légal (nom, prénom, contact)</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center p-2 rounded" style="background:#f8f9fa;">
                <i class="fa-solid fa-map-location-dot me-2 text-warning" style="width:20px;"></i>
                <span class="small">Localisation géographique et établissement scolaire</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center p-2 rounded" style="background:#f8f9fa;">
                <i class="fa-solid fa-fingerprint me-2" style="color:var(--fie-green,#1EB53A);width:20px;"></i>
                <span class="small">Identifiant Unique Élève (IUE) — généré automatiquement</span>
              </div>
            </div>
            <div class="col-12">
              <div class="d-flex align-items-center p-2 rounded" style="background:#f8f9fa;">
                <i class="fa-solid fa-clock-rotate-left me-2 text-secondary" style="width:20px;"></i>
                <span class="small">Historique des inscriptions, mouvements et résultats scolaires</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 2 : Finalités -->
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold d-flex align-items-center" style="background:#f8f9fa;">
          <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2 flex-shrink-0"
                style="width:28px;height:28px;background:var(--fie-red,#CE1126);font-size:.85rem;color:#fff;font-weight:700;">2</span>
          Finalités du traitement
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            Les données sont utilisées <strong>exclusivement</strong> à des fins institutionnelles :
          </p>
          <div class="row g-2">
            <div class="col-md-6">
              <div class="d-flex align-items-center p-2 rounded" style="background:#1EB53A12;">
                <i class="fa-solid fa-check-circle me-2" style="color:var(--fie-green,#1EB53A);"></i>
                <span class="small">Statistiques éducatives nationales (MENERS/DGESS)</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center p-2 rounded" style="background:#1EB53A12;">
                <i class="fa-solid fa-check-circle me-2" style="color:var(--fie-green,#1EB53A);"></i>
                <span class="small">Gestion administrative scolaire (inscriptions, mouvements)</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center p-2 rounded" style="background:#1EB53A12;">
                <i class="fa-solid fa-check-circle me-2" style="color:var(--fie-green,#1EB53A);"></i>
                <span class="small">Planification des ressources et infrastructures éducatives</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center p-2 rounded" style="background:#1EB53A12;">
                <i class="fa-solid fa-check-circle me-2" style="color:var(--fie-green,#1EB53A);"></i>
                <span class="small">Interopérabilité StatEduc ↔ FIE (API REST / SQL Server)</span>
              </div>
            </div>
          </div>
          <p class="mt-3 mb-0 small text-muted">
            <i class="fa-solid fa-ban me-1 text-danger"></i>
            <strong>Aucune donnée</strong> personnelle d'élève n'est communiquée à des tiers
            sans autorisation légale expresse.
          </p>
        </div>
      </div>

      <!-- Section 3 : Conservation et sécurité -->
      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold d-flex align-items-center" style="background:#f8f9fa;">
          <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2 flex-shrink-0"
                style="width:28px;height:28px;background:var(--fie-red,#CE1126);font-size:.85rem;color:#fff;font-weight:700;">3</span>
          Conservation et sécurité
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="d-flex align-items-center mb-2">
                  <i class="fa-solid fa-calendar-days me-2" style="color:var(--fie-red,#CE1126);"></i>
                  <strong class="small">Durée de conservation</strong>
                </div>
                <p class="small text-muted mb-0">
                  Les journaux d'audit sont conservés <strong>5 ans</strong>.
                  Les données élèves sont conservées pour la durée de la scolarité
                  et archivées conformément à la réglementation MENERS.
                </p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="d-flex align-items-center mb-2">
                  <i class="fa-solid fa-lock me-2" style="color:var(--fie-green,#1EB53A);"></i>
                  <strong class="small">Mots de passe</strong>
                </div>
                <p class="small text-muted mb-0">
                  Tous les mots de passe des agents sont hachés via
                  <strong>bcrypt (coût 12)</strong> — aucune récupération
                  en clair n'est possible.
                </p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="d-flex align-items-center mb-2">
                  <i class="fa-solid fa-database me-2 text-primary"></i>
                  <strong class="small">Requêtes préparées</strong>
                </div>
                <p class="small text-muted mb-0">
                  Toutes les communications avec la base de données utilisent
                  des <strong>PDO prepared statements</strong> — protection
                  totale contre les injections SQL.
                </p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="d-flex align-items-center mb-2">
                  <i class="fa-solid fa-shield-halved me-2 text-warning"></i>
                  <strong class="small">Sessions sécurisées</strong>
                </div>
                <p class="small text-muted mb-0">
                  Les sessions PHP sont régénérées à chaque connexion
                  (anti-fixation). Les tokens <strong>CSRF</strong>
                  protègent tous les formulaires POST.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 4 : Droits -->
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold d-flex align-items-center" style="background:#f8f9fa;">
          <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2 flex-shrink-0"
                style="width:28px;height:28px;background:var(--fie-red,#CE1126);font-size:.85rem;color:#fff;font-weight:700;">4</span>
          Droits des personnes concernées
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            Conformément à la loi n°1/03-2026, vous disposez des droits suivants :
          </p>
          <div class="row g-3">
            <div class="col-md-4">
              <div class="text-center p-3 rounded border h-100">
                <i class="fa-solid fa-eye fa-2x mb-2" style="color:var(--fie-red,#CE1126);"></i>
                <div class="fw-semibold small mb-1">Droit d'accès</div>
                <small class="text-muted">Consulter vos données personnelles enregistrées</small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="text-center p-3 rounded border h-100">
                <i class="fa-solid fa-pen-to-square fa-2x mb-2" style="color:var(--fie-green,#1EB53A);"></i>
                <div class="fw-semibold small mb-1">Droit de rectification</div>
                <small class="text-muted">Corriger les données inexactes vous concernant</small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="text-center p-3 rounded border h-100">
                <i class="fa-solid fa-trash-can fa-2x mb-2 text-warning"></i>
                <div class="fw-semibold small mb-1">Droit à l'effacement</div>
                <small class="text-muted">Dans les limites des obligations légales de conservation</small>
              </div>
            </div>
          </div>
          <div class="alert alert-secondary mt-3 mb-0 d-flex align-items-center" role="alert">
            <i class="fa-solid fa-circle-info me-2 flex-shrink-0"></i>
            <small>
              Pour exercer vos droits, contactez l'administrateur système SIGE Burundi
              via la <a href="<?= BASE_URL ?>/contact" class="alert-link">page Contact</a>.
              Délai de réponse : 30 jours ouvrés.
            </small>
          </div>
        </div>
      </div>

      <div class="text-center text-muted small mb-3">
        Dernière mise à jour : <strong>janvier 2026</strong> ·
        <a href="<?= BASE_URL ?>/mentions" class="text-decoration-none">Mentions légales</a> ·
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
