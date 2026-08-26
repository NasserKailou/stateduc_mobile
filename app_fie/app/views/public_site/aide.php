<?php
/**
 * Vue : Aide et documentation — FIE Burundi
 * Bootstrap 5.3 CDN + Font Awesome 6.5 CDN
 * Charte Burundi : #CE1126 / #1EB53A / #FFFFFF
 */
$page_title  = 'Aide et documentation — FIE Burundi';
$active_menu = 'aide';
require BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <!-- En-tête de page -->
      <div class="d-flex align-items-center mb-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
             style="width:56px;height:56px;background:var(--fie-red,#CE1126);">
          <i class="fa-solid fa-circle-question fa-xl text-white"></i>
        </div>
        <div>
          <h1 class="h2 mb-0 fw-bold">Aide et documentation</h1>
          <p class="text-muted mb-0">FIE — Fichier Informatisé des Élèves du Burundi</p>
        </div>
      </div>

      <!-- Bande drapeau décorative -->
      <div class="mb-4" style="height:4px;border-radius:2px;background:linear-gradient(to right,#CE1126 33%,#FFFFFF 33%,#FFFFFF 66%,#1EB53A 66%);"></div>

      <!-- FAQ Accordion -->
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold text-white"
             style="background:var(--fie-red,#CE1126);">
          <i class="fa-solid fa-rocket me-2"></i>Questions fréquentes
        </div>
        <div class="card-body p-0">
          <div class="accordion accordion-flush" id="accordionAide">

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#faq1">
                  <i class="fa-solid fa-user-plus me-2 text-success"></i>
                  Comment inscrire un élève ?
                </button>
              </h2>
              <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#accordionAide">
                <div class="accordion-body text-muted">
                  Naviguez vers <strong>Inscription → Nouvel élève</strong>.
                  Renseignez les informations d'état civil, sélectionnez la localisation
                  géographique (Province → Commune → Zone → Colline → Établissement),
                  puis soumettez le formulaire. Un <strong>Identifiant Unique Élève (IUE)</strong>
                  sera automatiquement généré au format
                  <code class="text-danger">BI-SSSS-AAAA-NNNNNN-CC</code>,
                  conforme à l'ISO 7064 MOD 97-10.
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#faq2">
                  <i class="fa-solid fa-magnifying-glass me-2 text-primary"></i>
                  Comment rechercher un élève ?
                </button>
              </h2>
              <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionAide">
                <div class="accordion-body text-muted">
                  Utilisez <strong>Inscription → Rechercher</strong>. Vous pouvez rechercher
                  par nom, prénom, IUE, établissement ou province. Les filtres avancés
                  permettent de croiser plusieurs critères simultanément.
                  La recherche est accessible aux rôles <em>enseignant</em>,
                  <em>gestionnaire_etab</em>, <em>admin_provincial</em>,
                  <em>admin_central</em>, <em>super_admin</em> et <em>consultant</em>.
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#faq3">
                  <i class="fa-solid fa-rotate me-2 text-warning"></i>
                  Comment synchroniser les établissements ?
                </button>
              </h2>
              <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#accordionAide">
                <div class="accordion-body text-muted">
                  Accédez à <strong>Administration → Synchronisation</strong>
                  (rôles <em>super_admin</em> et <em>admin_central</em> uniquement).
                  Lancez une synchronisation <em>complète</em> (premier chargement)
                  ou <em>incrémentale</em> (mises à jour quotidiennes) depuis l'API StatEduc.
                  En cas d'indisponibilité de l'API, importez le fichier Excel
                  <code>infos_etab_bu.xlsx</code> via <strong>Administration → Import Excel</strong>.
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#faq4">
                  <i class="fa-solid fa-key me-2" style="color:var(--fie-red,#CE1126);"></i>
                  J'ai oublié mon mot de passe, que faire ?
                </button>
              </h2>
              <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#accordionAide">
                <div class="accordion-body text-muted">
                  Les mots de passe sont gérés par l'administrateur système.
                  Contactez votre <strong>admin_central</strong> ou
                  <strong>super_admin</strong> pour une réinitialisation.
                  Les mots de passe sont stockés avec <em>bcrypt cost 12</em> —
                  aucune récupération en clair n'est possible.
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-semibold" type="button"
                        data-bs-toggle="collapse" data-bs-target="#faq5">
                  <i class="fa-solid fa-print me-2 text-secondary"></i>
                  Comment imprimer la fiche d'un élève ?
                </button>
              </h2>
              <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#accordionAide">
                <div class="accordion-body text-muted">
                  Sur la page de détail d'un élève (<strong>Inscription → Détail</strong>),
                  utilisez le bouton <em>Imprimer la fiche</em>. Une mise en page
                  optimisée pour l'impression (CSS <code>@media print</code>)
                  sera automatiquement appliquée avec l'IUE en grand format.
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Documentation technique -->
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold text-white"
             style="background:var(--fie-green,#1EB53A);">
          <i class="fa-solid fa-book me-2"></i>Documentation technique
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="d-flex align-items-start p-3 rounded border h-100">
                <i class="fa-solid fa-file-code fa-lg text-danger me-3 mt-1 flex-shrink-0"></i>
                <div>
                  <strong class="d-block mb-1">Guide de déploiement XAMPP</strong>
                  <span class="text-muted small">
                    Procédure complète d'installation, VirtualHost Apache,
                    ordre import SQL. Voir <code>docs/DEPLOYMENT.md</code>
                  </span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-start p-3 rounded border h-100">
                <i class="fa-solid fa-database fa-lg text-primary me-3 mt-1 flex-shrink-0"></i>
                <div>
                  <strong class="d-block mb-1">Schéma de base de données</strong>
                  <span class="text-muted small">
                    16 tables MySQL documentées — élèves, inscriptions,
                    établissements, RBAC, audit. Voir <code>db/schema.sql</code>
                  </span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-start p-3 rounded border h-100">
                <i class="fa-solid fa-shield-halved fa-lg text-success me-3 mt-1 flex-shrink-0"></i>
                <div>
                  <strong class="d-block mb-1">Sécurité &amp; RGPD</strong>
                  <span class="text-muted small">
                    PDO prepared statements, CSRF tokens, bcrypt cost 12,
                    sessions régénérées, journal d'audit 5 ans
                  </span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-start p-3 rounded border h-100">
                <i class="fa-solid fa-plug fa-lg text-warning me-3 mt-1 flex-shrink-0"></i>
                <div>
                  <strong class="d-block mb-1">API REST agrégats</strong>
                  <span class="text-muted small">
                    Endpoint JSON sécurisé <code>/api/aggregates</code> —
                    interopérabilité StatEduc ↔ FIE
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Rôles RBAC -->
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold" style="background:#f8f9fa;">
          <i class="fa-solid fa-users-gear me-2" style="color:var(--fie-red,#CE1126);"></i>
          Rôles et permissions (RBAC)
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 text-center">
              <thead class="table-light">
                <tr>
                  <th class="text-start ps-3">Rôle</th>
                  <th>Inscrire</th>
                  <th>Rechercher</th>
                  <th>Modifier</th>
                  <th>Sync</th>
                  <th>Audit</th>
                  <th>Utilisateurs</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-start ps-3"><span class="badge" style="background:var(--fie-red,#CE1126);">super_admin</span></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                </tr>
                <tr>
                  <td class="text-start ps-3"><span class="badge bg-warning text-dark">admin_provincial</span></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                </tr>
                <tr>
                  <td class="text-start ps-3"><span class="badge bg-info text-dark">gestionnaire_etab</span></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                </tr>
                <tr>
                  <td class="text-start ps-3"><span class="badge bg-secondary">enseignant</span></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                </tr>
                <tr>
                  <td class="text-start ps-3"><span class="badge" style="background:var(--fie-green,#1EB53A);">consultant</span></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-check text-success"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                  <td><i class="fa-solid fa-xmark text-danger"></i></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Alert support -->
      <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="fa-solid fa-headset fa-lg me-3 flex-shrink-0"></i>
        <div>
          <strong>Support technique SIGE Burundi</strong><br>
          Pour toute question non couverte ici, contactez l'équipe via la
          <a href="<?= BASE_URL ?>/contact" class="alert-link">page Contact</a>
          ou consultez la documentation dans <code>docs/</code>.
        </div>
      </div>

      <div class="text-center mt-3">
        <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary">
          <i class="fa-solid fa-arrow-left me-2"></i>Retour à l'accueil
        </a>
      </div>

    </div>
  </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
