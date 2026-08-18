<?php
/**
 * FIE — Vue : Formulaire de nouvelle inscription / émission d'IUE
 * Bootstrap 5 + Font Awesome — Charte Burundi
 * CORRECTION Phase 2 : refonte Bootstrap complète
 * Variables injectées par InscriptionController::newForm() :
 *   $anneeActive, $secteurs, $niveaux, $sections, $provinces, $csrf, $lastSync
 */
$page_title  = "Nouvelle Inscription — FIE";
$active_menu = 'inscription';
require BASE_PATH . '/app/views/layouts/app_layout.php';

$old  = $_SESSION['fie_form_old']     ?? [];
$ferr = $_SESSION['fie_field_errors'] ?? [];
unset($_SESSION['fie_form_old'], $_SESSION['fie_field_errors']);
?>

<!-- ── Fil d'Ariane ────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inscription">Inscriptions</a></li>
        <li class="breadcrumb-item active">Nouvelle inscription</li>
    </ol>
</nav>

<!-- ── Titre ───────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="fa-solid fa-user-plus me-2" style="color:var(--fie-red)"></i>
            Nouvelle Inscription — Émission d'IUE
        </h1>
        <p class="text-muted mb-0 small">
            Année scolaire :
            <strong><?= SecurityHelper::e($anneeActive['libelle'] ?? 'N/A') ?></strong>
        </p>
    </div>
</div>

<!-- ── Alerte sync ─────────────────────────────────────────────────────── -->
<?php if ($lastSync): ?>
<div class="alert alert-success border-0 small d-flex align-items-center gap-2 mb-3">
    <i class="fa-solid fa-circle-check"></i>
    <span>
        Référentiel établissements synchronisé depuis StatEduc le
        <strong><?= SecurityHelper::e(date('d/m/Y H:i', strtotime($lastSync))) ?></strong>.
    </span>
</div>
<?php else: ?>
<div class="alert alert-warning small d-flex align-items-center gap-2 mb-3">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <span>
        Le référentiel établissements n'a pas encore été synchronisé.
        <a href="<?= BASE_URL ?>/admin/sync" class="alert-link">Synchroniser maintenant</a>
    </span>
</div>
<?php endif; ?>

<!-- ── Bandeau doublon AJAX (caché par défaut) ─────────────────────────── -->
<div id="fie-doublon-alert" class="alert alert-warning d-none" role="alert">
    <h6 class="alert-heading">
        <i class="fa-solid fa-triangle-exclamation me-1"></i>
        Doublon potentiel détecté !
    </h6>
    <p id="fie-doublon-msg" class="mb-2"></p>
    <ul id="fie-doublon-list" class="mb-2"></ul>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="fie-confirm-no-doublon">
        <label class="form-check-label" for="fie-confirm-no-doublon">
            Je confirme que cet élève n'est pas déjà enregistré et souhaite continuer.
        </label>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     FORMULAIRE PRINCIPAL
     ═══════════════════════════════════════════════════════════════════════ -->
<form method="POST" action="<?= BASE_URL ?>/inscription/nouveau"
      id="fie-insc-form" novalidate>

    <?= SecurityHelper::csrfField() ?>
    <input type="hidden" name="code_type_annee"
           value="<?= (int)($anneeActive['code_type_annee'] ?? 0) ?>">

    <!-- ══ SECTION 1 : ÉTAT CIVIL ══════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-semibold"
             style="background:var(--fie-red);color:#fff">
            <i class="fa-solid fa-person me-2"></i>1. État civil de l'élève
        </div>
        <div class="card-body">
            <div class="row g-3">

                <!-- Nom -->
                <div class="col-md-6">
                    <label for="nom" class="form-label fw-semibold">
                        Nom de famille <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="nom" name="nom" required maxlength="100"
                           class="form-control <?= isset($ferr['nom']) ? 'is-invalid' : '' ?>"
                           value="<?= SecurityHelper::e($old['nom'] ?? '') ?>"
                           placeholder="ex : NIYONZIMA">
                    <?php if (isset($ferr['nom'])): ?>
                    <div class="invalid-feedback"><?= SecurityHelper::e($ferr['nom']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Prénoms -->
                <div class="col-md-6">
                    <label for="prenoms" class="form-label fw-semibold">
                        Prénom(s) <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="prenoms" name="prenoms" required maxlength="150"
                           class="form-control <?= isset($ferr['prenoms']) ? 'is-invalid' : '' ?>"
                           value="<?= SecurityHelper::e($old['prenoms'] ?? '') ?>"
                           placeholder="ex : Jean-Pierre">
                    <?php if (isset($ferr['prenoms'])): ?>
                    <div class="invalid-feedback"><?= SecurityHelper::e($ferr['prenoms']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Sexe -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Sexe <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sexe" id="sexeM" value="M"
                                   <?= (($old['sexe'] ?? '') === 'M') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sexeM">
                                <i class="fa-solid fa-mars text-primary me-1"></i>Masculin
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sexe" id="sexeF" value="F"
                                   <?= (($old['sexe'] ?? '') === 'F') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sexeF">
                                <i class="fa-solid fa-venus text-danger me-1"></i>Féminin
                            </label>
                        </div>
                    </div>
                    <?php if (isset($ferr['sexe'])): ?>
                    <div class="text-danger small mt-1"><?= SecurityHelper::e($ferr['sexe']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Date de naissance -->
                <div class="col-md-4">
                    <label for="date_naissance" class="form-label fw-semibold">
                        Date de naissance <span class="text-danger">*</span>
                    </label>
                    <input type="date" id="date_naissance" name="date_naissance" required
                           class="form-control <?= isset($ferr['date_naissance']) ? 'is-invalid' : '' ?>"
                           max="<?= date('Y-m-d') ?>"
                           value="<?= SecurityHelper::e($old['date_naissance'] ?? '') ?>">
                    <?php if (isset($ferr['date_naissance'])): ?>
                    <div class="invalid-feedback"><?= SecurityHelper::e($ferr['date_naissance']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Lieu de naissance -->
                <div class="col-md-4">
                    <label for="lieu_naissance" class="form-label fw-semibold">Lieu de naissance</label>
                    <input type="text" id="lieu_naissance" name="lieu_naissance" maxlength="150"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['lieu_naissance'] ?? '') ?>"
                           placeholder="ex : Gitega">
                </div>

                <!-- Province de naissance -->
                <div class="col-md-4">
                    <label for="province_naissance" class="form-label fw-semibold">Province de naissance</label>
                    <select id="province_naissance" name="province_naissance" class="form-select">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($provinces as $p): ?>
                        <option value="<?= SecurityHelper::e($p['province']) ?>"
                            <?= (($old['province_naissance'] ?? '') === $p['province']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($p['province']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Nationalité -->
                <div class="col-md-4">
                    <label for="nationalite" class="form-label fw-semibold">Nationalité</label>
                    <input type="text" id="nationalite" name="nationalite" maxlength="3"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['nationalite'] ?? 'BDI') ?>"
                           placeholder="BDI">
                    <div class="form-text">Code ISO 3 lettres (BDI = Burundais)</div>
                </div>

            </div><!-- .row -->

            <!-- Bouton vérification doublon -->
            <div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top">
                <button type="button" id="btn-check-doublon" class="btn btn-outline-warning btn-sm">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Vérifier les doublons
                </button>
                <span id="fie-doublon-status" class="small text-muted"></span>
            </div>
        </div>
    </div>

    <!-- ══ SECTION 2 : ACTE DE NAISSANCE ══════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-semibold bg-light">
            <i class="fa-solid fa-file-contract me-2 text-secondary"></i>
            2. Acte de naissance
            <span class="badge bg-secondary ms-2 fw-normal">facultatif</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="numero_acte_naissance" class="form-label">N° acte de naissance</label>
                    <input type="text" id="numero_acte_naissance" name="numero_acte_naissance"
                           maxlength="50" class="form-control"
                           value="<?= SecurityHelper::e($old['numero_acte_naissance'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_acte_naissance" class="form-label">Date de l'acte</label>
                    <input type="date" id="date_acte_naissance" name="date_acte_naissance"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['date_acte_naissance'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="commune_acte" class="form-label">Commune de l'acte</label>
                    <input type="text" id="commune_acte" name="commune_acte" maxlength="100"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['commune_acte'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ══ SECTION 3 : TUTEUR / PARENT ════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-semibold bg-light">
            <i class="fa-solid fa-people-roof me-2 text-secondary"></i>3. Tuteur / Parent
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nom_pere" class="form-label">Nom du père</label>
                    <input type="text" id="nom_pere" name="nom_pere" maxlength="150"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['nom_pere'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="nom_mere" class="form-label">Nom de la mère</label>
                    <input type="text" id="nom_mere" name="nom_mere" maxlength="150"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['nom_mere'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="nom_tuteur" class="form-label">Tuteur légal</label>
                    <input type="text" id="nom_tuteur" name="nom_tuteur" maxlength="150"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['nom_tuteur'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="telephone_tuteur" class="form-label">Téléphone tuteur</label>
                    <input type="tel" id="telephone_tuteur" name="telephone_tuteur" maxlength="30"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['telephone_tuteur'] ?? '') ?>"
                           placeholder="+257 XX XXX XXX">
                </div>
            </div>
        </div>
    </div>

    <!-- ══ SECTION 4 : SCOLARISATION + ÉTABLISSEMENT ══════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-semibold" style="background:var(--fie-green);color:#fff">
            <i class="fa-solid fa-school me-2"></i>
            4. Scolarisation — Année <?= SecurityHelper::e($anneeActive['libelle'] ?? '') ?>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <!-- Sous-secteur -->
                <div class="col-md-4">
                    <label for="code_type_secteur_ens" class="form-label fw-semibold">
                        Sous-secteur <span class="text-danger">*</span>
                    </label>
                    <select id="code_type_secteur_ens" name="code_type_secteur_ens" required
                            class="form-select <?= isset($ferr['code_type_secteur_ens']) ? 'is-invalid' : '' ?>">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($secteurs as $s): ?>
                        <option value="<?= (int)$s['code_type_secteur_ens'] ?>"
                            <?= ((int)($old['code_type_secteur_ens'] ?? 0) === (int)$s['code_type_secteur_ens']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($s['libelle']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($ferr['code_type_secteur_ens'])): ?>
                    <div class="invalid-feedback"><?= SecurityHelper::e($ferr['code_type_secteur_ens']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Niveau -->
                <div class="col-md-4">
                    <label for="code_type_niveau" class="form-label fw-semibold">
                        Niveau <span class="text-danger">*</span>
                    </label>
                    <select id="code_type_niveau" name="code_type_niveau" required
                            class="form-select <?= isset($ferr['code_type_niveau']) ? 'is-invalid' : '' ?>">
                        <option value="">— Sélectionner le sous-secteur d'abord —</option>
                        <?php foreach ($niveaux as $niv): ?>
                        <option value="<?= (int)$niv['code_type_niveau'] ?>"
                                data-secteur="<?= (int)$niv['code_secteur'] ?>"
                            <?= ((int)($old['code_type_niveau'] ?? 0) === (int)$niv['code_type_niveau']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($niv['libelle']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Section -->
                <div class="col-md-2">
                    <label for="code_type_section" class="form-label fw-semibold">Section</label>
                    <select id="code_type_section" name="code_type_section" class="form-select">
                        <?php foreach ($sections as $sec): ?>
                        <option value="<?= (int)$sec['code_type_section'] ?>"
                            <?= ((int)($old['code_type_section'] ?? 1) === (int)$sec['code_type_section']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($sec['libelle']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Classe -->
                <div class="col-md-2">
                    <label for="numero_classe" class="form-label fw-semibold">Classe</label>
                    <input type="text" id="numero_classe" name="numero_classe" maxlength="20"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['numero_classe'] ?? '') ?>"
                           placeholder="ex: 2AF-B">
                </div>

            </div><!-- row secteur/niveau -->

            <!-- Sélects dépendants géographiques -->
            <hr class="my-3">
            <p class="fw-semibold small mb-2">
                <i class="fa-solid fa-location-dot me-1" style="color:var(--fie-red)"></i>
                Localisation de l'établissement
                <span class="text-danger">*</span>
            </p>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="province" class="form-label">Province</label>
                    <select id="province" name="_province" required class="form-select">
                        <option value="">— Province —</option>
                        <?php foreach ($provinces as $p): ?>
                        <option value="<?= SecurityHelper::e($p['province']) ?>"
                            <?= (($old['_province'] ?? '') === $p['province']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($p['province']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="commune" class="form-label">Commune</label>
                    <select id="commune" name="_commune" required class="form-select" disabled>
                        <option value="">— Commune —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="zone" class="form-label">Zone</label>
                    <select id="zone" name="_zone" class="form-select" disabled>
                        <option value="">— Zone —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="colline" class="form-label">Colline</label>
                    <select id="colline" name="_colline" class="form-select" disabled>
                        <option value="">— Colline —</option>
                    </select>
                </div>
            </div>

            <!-- Établissement -->
            <div class="row g-3 mt-1">
                <div class="col-md-8">
                    <label for="code_etablissement" class="form-label fw-semibold">
                        Établissement <span class="text-danger">*</span>
                    </label>
                    <select id="code_etablissement" name="code_etablissement" required
                            class="form-select <?= isset($ferr['code_etablissement']) ? 'is-invalid' : '' ?>"
                            disabled>
                        <option value="">— Sélectionner la localisation d'abord —</option>
                    </select>
                    <div class="form-text">
                        <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                        Alimenté depuis le référentiel StatEduc synchronisé.
                    </div>
                    <?php if (isset($ferr['code_etablissement'])): ?>
                    <div class="invalid-feedback"><?= SecurityHelper::e($ferr['code_etablissement']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="date_inscription" class="form-label fw-semibold">
                        Date d'inscription <span class="text-danger">*</span>
                    </label>
                    <input type="date" id="date_inscription" name="date_inscription" required
                           class="form-control <?= isset($ferr['date_inscription']) ? 'is-invalid' : '' ?>"
                           value="<?= SecurityHelper::e($old['date_inscription'] ?? date('Y-m-d')) ?>">
                    <?php if (isset($ferr['date_inscription'])): ?>
                    <div class="invalid-feedback"><?= SecurityHelper::e($ferr['date_inscription']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- ══ BOUTONS DE SOUMISSION ════════════════════════════════════════════ -->
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <button type="submit" id="btn-submit-insc" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-id-card me-2"></i>Inscrire et générer l'IUE
        </button>
        <a href="<?= BASE_URL ?>/inscription/recherche" class="btn btn-outline-secondary">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Rechercher un élève existant
        </a>
        <a href="<?= BASE_URL ?>/tableau-de-bord" class="btn btn-outline-secondary">
            <i class="fa-solid fa-xmark me-1"></i>Annuler
        </a>
    </div>

</form>

<!-- ═══════════════════════════════════════════════════════════════════════
     JAVASCRIPT : cascades AJAX + doublon
     ═══════════════════════════════════════════════════════════════════════ -->
<script>
(function () {
  'use strict';

  const BASE = '<?= BASE_URL ?>/';

  /* ── Utilitaires AJAX ──────────────────────────────────────────────────── */
  function getJSON(url, cb) {
    fetch(url).then(r => r.json()).then(cb).catch(e => console.error('AJAX GET:', e));
  }
  function postJSON(url, data, cb) {
    const fd = new FormData();
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    fetch(url, {method: 'POST', body: fd})
      .then(r => r.json()).then(cb).catch(e => console.error('AJAX POST:', e));
  }

  /* ── Peupler un <select> ───────────────────────────────────────────────── */
  function fillSelect(sel, items, placeholder) {
    sel.innerHTML = '<option value="">' + placeholder + '</option>';
    (items || []).forEach(function(item) {
      const o = document.createElement('option');
      o.value       = item.code;
      o.textContent = item.libelle;
      sel.appendChild(o);
    });
    sel.disabled = !items || items.length === 0;
  }

  /* ── Références DOM ────────────────────────────────────────────────────── */
  const selProvince  = document.getElementById('province');
  const selCommune   = document.getElementById('commune');
  const selZone      = document.getElementById('zone');
  const selColline   = document.getElementById('colline');
  const selEtab      = document.getElementById('code_etablissement');
  const selSecteur   = document.getElementById('code_type_secteur_ens');
  const selNiveau    = document.getElementById('code_type_niveau');

  /* ── Cascade Province → Commune ────────────────────────────────────────── */
  selProvince.addEventListener('change', function () {
    [selCommune, selZone, selColline, selEtab].forEach(s => { s.disabled = true; s.innerHTML = '<option value="">...</option>'; });
    if (!this.value) return;
    getJSON(BASE + 'inscription/ajax/communes?province=' + encodeURIComponent(this.value), function(d) {
      fillSelect(selCommune, d.items, '— Commune —');
    });
  });

  /* ── Commune → Zone ─────────────────────────────────────────────────────── */
  selCommune.addEventListener('change', function () {
    [selZone, selColline, selEtab].forEach(s => { s.disabled = true; s.innerHTML = '<option value="">...</option>'; });
    if (!this.value) return;
    getJSON(BASE + 'inscription/ajax/zones?province=' + encodeURIComponent(selProvince.value)
            + '&commune=' + encodeURIComponent(this.value), function(d) {
      fillSelect(selZone, d.items, '— Zone —');
      if (!d.items || d.items.length === 0) loadCollines();
    });
  });

  /* ── Zone → Colline ─────────────────────────────────────────────────────── */
  selZone.addEventListener('change', function () {
    [selColline, selEtab].forEach(s => { s.disabled = true; s.innerHTML = '<option value="">...</option>'; });
    loadCollines();
  });

  function loadCollines() {
    getJSON(BASE + 'inscription/ajax/collines?province=' + encodeURIComponent(selProvince.value)
            + '&commune=' + encodeURIComponent(selCommune.value)
            + '&zone=' + encodeURIComponent(selZone.value), function(d) {
      fillSelect(selColline, d.items, '— Colline —');
    });
  }

  /* ── Colline → Établissement ─────────────────────────────────────────────── */
  selColline.addEventListener('change', loadEtabs);
  selZone.addEventListener('change', function() { if (!selColline.value) loadEtabs(); });
  selSecteur.addEventListener('change', function() {
    filterNiveaux(this.value);
    if (selCommune.value) loadEtabs();
  });

  function loadEtabs() {
    selEtab.disabled = true;
    selEtab.innerHTML = '<option value="">Chargement...</option>';
    getJSON(BASE + 'inscription/ajax/etablissements?province=' + encodeURIComponent(selProvince.value)
            + '&commune=' + encodeURIComponent(selCommune.value)
            + '&zone=' + encodeURIComponent(selZone.value)
            + '&colline=' + encodeURIComponent(selColline.value)
            + '&secteur=' + encodeURIComponent(selSecteur.value), function(d) {
      selEtab.innerHTML = '<option value="">— Établissement —</option>';
      (d.items || []).forEach(function(e) {
        const o = document.createElement('option');
        o.value       = e.code;
        o.textContent = e.libelle;
        selEtab.appendChild(o);
      });
      selEtab.disabled = !d.items || d.items.length === 0;
    });
  }

  /* ── Filtre niveaux par sous-secteur ─────────────────────────────────────── */
  function filterNiveaux(secteurCode) {
    Array.from(selNiveau.options).forEach(function(opt) {
      if (!opt.dataset.secteur) return;
      const match = !secteurCode || opt.dataset.secteur === secteurCode;
      opt.hidden = opt.disabled = !match;
    });
    selNiveau.value = '';
  }
  if (selSecteur.value) filterNiveaux(selSecteur.value);

  /* ── Vérification doublon ────────────────────────────────────────────────── */
  const btnDoublon   = document.getElementById('btn-check-doublon');
  const alertDoublon = document.getElementById('fie-doublon-alert');
  const listDoublon  = document.getElementById('fie-doublon-list');
  const msgDoublon   = document.getElementById('fie-doublon-msg');
  const statusMsg    = document.getElementById('fie-doublon-status');
  const confirmChk   = document.getElementById('fie-confirm-no-doublon');
  const btnSubmit    = document.getElementById('btn-submit-insc');

  btnDoublon.addEventListener('click', function() {
    const nom = document.getElementById('nom').value.trim();
    const prn = document.getElementById('prenoms').value.trim();
    const ddn = document.getElementById('date_naissance').value;
    if (!nom || !prn || !ddn) {
      alert('Renseignez le nom, les prénoms et la date de naissance avant de vérifier.');
      return;
    }
    statusMsg.textContent = 'Vérification en cours…';
    btnDoublon.disabled = true;
    const csrf = document.querySelector('input[name="csrf_token"]').value;
    postJSON(BASE + 'inscription/ajax/doublon',
      {nom, prenoms: prn, date_naissance: ddn, csrf_token: csrf},
      function(resp) {
        btnDoublon.disabled = false;
        if (resp.count > 0) {
          msgDoublon.textContent = resp.count + ' élève(s) similaire(s) trouvé(s) :';
          listDoublon.innerHTML  = '';
          (resp.doublons || []).forEach(function(d) {
            const li = document.createElement('li');
            li.innerHTML = '<strong>' + d.iue + '</strong> — ' + d.nom + ' ' + d.prenoms
              + ' — né(e) le ' + d.date_naissance
              + ' <a href="' + BASE + 'inscription/' + encodeURIComponent(d.iue) + '" target="_blank">Voir la fiche</a>';
            listDoublon.appendChild(li);
          });
          alertDoublon.classList.remove('d-none');
          btnSubmit.disabled = true;
          statusMsg.textContent = '';
        } else {
          alertDoublon.classList.add('d-none');
          statusMsg.innerHTML = '<span class="text-success"><i class="fa-solid fa-check me-1"></i>Aucun doublon détecté.</span>';
          btnSubmit.disabled = false;
        }
      }
    );
  });

  confirmChk.addEventListener('change', function() {
    btnSubmit.disabled = !this.checked;
  });

  /* ── Validation avant soumission ──────────────────────────────────────────── */
  document.getElementById('fie-insc-form').addEventListener('submit', function(e) {
    if (!alertDoublon.classList.contains('d-none') && !confirmChk.checked) {
      e.preventDefault();
      alert("Veuillez confirmer qu'il ne s'agit pas d'un doublon avant de soumettre.");
      return;
    }
    if (!selEtab.value) {
      e.preventDefault();
      alert("Veuillez sélectionner un établissement.");
      selEtab.focus();
    }
  });

}());
</script>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
