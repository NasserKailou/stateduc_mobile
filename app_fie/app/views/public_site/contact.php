<?php
/**
 * Vue : Contact — FIE Burundi
 * Bootstrap 5.3 CDN + Font Awesome 6.5 CDN
 * Charte Burundi : #CE1126 / #1EB53A / #FFFFFF
 */
$page_title  = 'Contact — FIE Burundi';
$active_menu = 'contact';
require BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <!-- En-tête de page -->
      <div class="d-flex align-items-center mb-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
             style="width:56px;height:56px;background:var(--fie-red,#CE1126);">
          <i class="fa-solid fa-envelope fa-xl text-white"></i>
        </div>
        <div>
          <h1 class="h2 mb-0 fw-bold">Contactez-nous</h1>
          <p class="text-muted mb-0">DGESS / SIGE Burundi — Support FIE</p>
        </div>
      </div>

      <!-- Bande drapeau décorative -->
      <div class="mb-4" style="height:4px;border-radius:2px;background:linear-gradient(to right,#CE1126 33%,#FFFFFF 33%,#FFFFFF 66%,#1EB53A 66%);"></div>

      <!-- 3 cartes info -->
      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="card shadow-sm h-100 text-center p-3">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                   style="width:56px;height:56px;background:#CE112615;">
                <i class="fa-solid fa-building-columns fa-lg" style="color:var(--fie-red,#CE1126);"></i>
              </div>
              <h6 class="card-title fw-semibold mb-2">Adresse</h6>
              <p class="card-text text-muted small mb-0">
                Ministère de l'Éducation Nationale<br>
                et de la Recherche Scientifique<br>
                <strong>Bujumbura, Burundi</strong>
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100 text-center p-3">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                   style="width:56px;height:56px;background:#1EB53A15;">
                <i class="fa-solid fa-envelope fa-lg" style="color:var(--fie-green,#1EB53A);"></i>
              </div>
              <h6 class="card-title fw-semibold mb-2">Support SIGE</h6>
              <p class="card-text text-muted small mb-0">
                Pour toute question technique<br>
                relative au FIE,<br>
                contactez le support SIGE.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm h-100 text-center p-3">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                   style="width:56px;height:56px;background:#0d6efd15;">
                <i class="fa-solid fa-clock fa-lg text-primary"></i>
              </div>
              <h6 class="card-title fw-semibold mb-2">Horaires</h6>
              <p class="card-text text-muted small mb-0">
                Lundi – Vendredi<br>
                <strong>07h30 – 16h00</strong><br>
                <span class="text-muted" style="font-size:.75rem;">(UTC+2, Bujumbura)</span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulaire de contact -->
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold text-white"
             style="background:var(--fie-red,#CE1126);">
          <i class="fa-solid fa-paper-plane me-2"></i>Envoyer un message
        </div>
        <div class="card-body p-4">

          <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <i class="fa-solid fa-circle-info me-2 flex-shrink-0"></i>
            <span class="small">
              Ce formulaire est destiné aux agents SIGE et partenaires institutionnels.
              Pour les demandes d'accès urgentes, contactez directement l'administrateur système.
            </span>
          </div>

          <form>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                  <input type="text" class="form-control" placeholder="Votre nom et prénom">
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Institution <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-building"></i></span>
                  <input type="text" class="form-control" placeholder="Ministère / Province / Établissement">
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Rôle dans le système</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-id-badge"></i></span>
                  <select class="form-select">
                    <option value="">-- Sélectionner --</option>
                    <option>super_admin</option>
                    <option>admin_central</option>
                    <option>admin_provincial</option>
                    <option>gestionnaire_etab</option>
                    <option>enseignant</option>
                    <option>consultant</option>
                    <option>Non-utilisateur / Partenaire</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Province concernée</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-map-location-dot"></i></span>
                  <input type="text" class="form-control" placeholder="Ex: Bujumbura Mairie">
                </div>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Objet <span class="text-danger">*</span></label>
                <select class="form-select">
                  <option value="">-- Sélectionner un objet --</option>
                  <optgroup label="Problèmes techniques">
                    <option>Connexion impossible</option>
                    <option>Erreur lors d'une inscription</option>
                    <option>Problème de synchronisation</option>
                    <option>IUE incorrect ou manquant</option>
                  </optgroup>
                  <optgroup label="Accès et comptes">
                    <option>Demande de création de compte</option>
                    <option>Réinitialisation de mot de passe</option>
                    <option>Modification de rôle/permissions</option>
                  </optgroup>
                  <optgroup label="Données">
                    <option>Correction de données élève</option>
                    <option>Import Excel — question</option>
                    <option>Autre</option>
                  </optgroup>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                <textarea class="form-control" rows="5"
                          placeholder="Décrivez votre demande avec précision (captures d'écran, messages d'erreur, IUE concerné…)"></textarea>
                <div class="form-text">
                  <i class="fa-solid fa-circle-info me-1"></i>
                  Pensez à mentionner votre login FIE et l'établissement concerné pour accélérer le traitement.
                </div>
              </div>
              <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn text-white"
                        style="background:var(--fie-green,#1EB53A);">
                  <i class="fa-solid fa-paper-plane me-2"></i>Envoyer le message
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                  <i class="fa-solid fa-xmark me-2"></i>Effacer
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Note bas de page -->
      <div class="text-center text-muted small mb-3">
        <i class="fa-solid fa-lock me-1"></i>
        Vos données ne seront utilisées qu'à des fins de traitement de votre demande.
        Voir notre <a href="<?= BASE_URL ?>/confidentialite" class="text-decoration-none">politique de confidentialité</a>.
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
