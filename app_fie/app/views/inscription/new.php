<?php
/**
 * Vue : Formulaire de nouvelle inscription
 * Variables injectées par InscriptionController::newForm() :
 *   $anneeActive, $secteurs, $niveaux, $sections, $provinces, $csrf, $lastSync
 */
$title = 'Nouvelle Inscription';
require FIE_VIEWS_PATH . 'layouts/header.php';
$old    = $_SESSION['fie_form_old']     ?? [];
$ferr   = $_SESSION['fie_field_errors'] ?? [];
$flash  = $_SESSION['fie_flash_error']  ?? null;
unset($_SESSION['fie_form_old'], $_SESSION['fie_field_errors'], $_SESSION['fie_flash_error']);
?>

<div class="fie-page-header">
  <h1><i class="fi-icon">✏</i> Nouvelle Inscription — Émission d'IUE</h1>
  <div class="fie-breadcrumb">Accueil &rsaquo; Inscriptions &rsaquo; Nouvelle inscription</div>
</div>

<?php if ($lastSync): ?>
<div class="fie-notice info">
  <strong>Référentiel établissements :</strong> dernière synchronisation depuis StatEduc le
  <?= SecurityHelper::e(date('d/m/Y H:i', strtotime($lastSync))) ?>.
</div>
<?php else: ?>
<div class="fie-notice warning">
  <strong>Attention :</strong> Le référentiel établissements n'a pas encore été synchronisé.
  <a href="<?= FIE_BASE_URL ?>admin/sync">Synchroniser maintenant</a>
</div>
<?php endif; ?>

<?php if ($flash): ?>
<div class="fie-notice error" id="fie-flash-error">
  <?= SecurityHelper::e($flash) ?>
  <?php if (!empty($ferr)): ?>
  <ul class="fie-error-list">
    <?php foreach ($ferr as $f => $msg): ?>
    <li><?= SecurityHelper::e($msg) ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Bandeau doublon AJAX -->
<div id="fie-doublon-alert" class="fie-notice warning" style="display:none">
  <strong>⚠ Doublon potentiel détecté !</strong>
  <span id="fie-doublon-msg"></span>
  <ul id="fie-doublon-list"></ul>
  <label>
    <input type="checkbox" id="fie-confirm-no-doublon">
    Je confirme que cet élève n'est pas déjà enregistré et souhaite continuer.
  </label>
</div>

<form method="POST" action="<?= FIE_BASE_URL ?>inscription/new"
      id="fie-insc-form" enctype="multipart/form-data" novalidate>

  <?= SecurityHelper::csrfField() ?>
  <input type="hidden" name="code_type_annee"
         value="<?= (int)($anneeActive['code_type_annee'] ?? 0) ?>">

  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <!-- SECTION 1 : ÉTAT CIVIL                                                -->
  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <fieldset class="fie-fieldset">
    <legend>1. État civil de l'élève</legend>
    <div class="fie-form-grid">

      <div class="fie-field <?= isset($ferr['nom']) ? 'fie-field--error' : '' ?>">
        <label for="nom">Nom de famille <span class="fie-required">*</span></label>
        <input type="text" id="nom" name="nom" required maxlength="100"
               value="<?= SecurityHelper::e($old['nom'] ?? '') ?>"
               placeholder="ex : NIYONZIMA"
               class="fie-input <?= isset($ferr['nom']) ? 'fie-input--error' : '' ?>">
        <?php if (isset($ferr['nom'])): ?>
        <span class="fie-field-error"><?= SecurityHelper::e($ferr['nom']) ?></span>
        <?php endif; ?>
      </div>

      <div class="fie-field <?= isset($ferr['prenoms']) ? 'fie-field--error' : '' ?>">
        <label for="prenoms">Prénom(s) <span class="fie-required">*</span></label>
        <input type="text" id="prenoms" name="prenoms" required maxlength="150"
               value="<?= SecurityHelper::e($old['prenoms'] ?? '') ?>"
               placeholder="ex : Jean-Pierre">
        <?php if (isset($ferr['prenoms'])): ?>
        <span class="fie-field-error"><?= SecurityHelper::e($ferr['prenoms']) ?></span>
        <?php endif; ?>
      </div>

      <div class="fie-field <?= isset($ferr['sexe']) ? 'fie-field--error' : '' ?>">
        <label>Sexe <span class="fie-required">*</span></label>
        <div class="fie-radio-group">
          <label><input type="radio" name="sexe" value="M"
            <?= (($old['sexe'] ?? '') === 'M') ? 'checked' : '' ?>> Masculin</label>
          <label><input type="radio" name="sexe" value="F"
            <?= (($old['sexe'] ?? '') === 'F') ? 'checked' : '' ?>> Féminin</label>
        </div>
        <?php if (isset($ferr['sexe'])): ?>
        <span class="fie-field-error"><?= SecurityHelper::e($ferr['sexe']) ?></span>
        <?php endif; ?>
      </div>

      <div class="fie-field <?= isset($ferr['date_naissance']) ? 'fie-field--error' : '' ?>">
        <label for="date_naissance">Date de naissance <span class="fie-required">*</span></label>
        <input type="date" id="date_naissance" name="date_naissance" required
               max="<?= date('Y-m-d') ?>"
               value="<?= SecurityHelper::e($old['date_naissance'] ?? '') ?>">
        <?php if (isset($ferr['date_naissance'])): ?>
        <span class="fie-field-error"><?= SecurityHelper::e($ferr['date_naissance']) ?></span>
        <?php endif; ?>
      </div>

      <div class="fie-field">
        <label for="lieu_naissance">Lieu de naissance</label>
        <input type="text" id="lieu_naissance" name="lieu_naissance" maxlength="150"
               value="<?= SecurityHelper::e($old['lieu_naissance'] ?? '') ?>"
               placeholder="ex : Gitega">
      </div>

      <div class="fie-field">
        <label for="province_naissance">Province de naissance</label>
        <select id="province_naissance" name="province_naissance">
          <option value="">-- Sélectionner --</option>
          <?php foreach ($provinces as $p): ?>
          <option value="<?= SecurityHelper::e($p['province']) ?>"
            <?= (($old['province_naissance'] ?? '') === $p['province']) ? 'selected' : '' ?>>
            <?= SecurityHelper::e($p['province']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="fie-field">
        <label for="nationalite">Nationalité</label>
        <input type="text" id="nationalite" name="nationalite" maxlength="3"
               value="<?= SecurityHelper::e($old['nationalite'] ?? 'BDI') ?>"
               placeholder="BDI">
        <span class="fie-hint">Code ISO 3 lettres (ex : BDI = Burundais)</span>
      </div>

    </div><!-- .fie-form-grid -->

    <!-- Bouton vérification doublon (AJAX) -->
    <div class="fie-doublon-check-bar">
      <button type="button" id="btn-check-doublon" class="fie-btn fie-btn--secondary">
        🔍 Vérifier les doublons
      </button>
      <span id="fie-doublon-status" class="fie-hint"></span>
    </div>
  </fieldset>

  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <!-- SECTION 2 : ACTE DE NAISSANCE                                         -->
  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <fieldset class="fie-fieldset fie-fieldset--collapsible">
    <legend>2. Acte de naissance <small>(facultatif)</small></legend>
    <div class="fie-form-grid">
      <div class="fie-field">
        <label for="numero_acte_naissance">N° acte de naissance</label>
        <input type="text" id="numero_acte_naissance" name="numero_acte_naissance" maxlength="50"
               value="<?= SecurityHelper::e($old['numero_acte_naissance'] ?? '') ?>">
      </div>
      <div class="fie-field">
        <label for="date_acte_naissance">Date de l'acte</label>
        <input type="date" id="date_acte_naissance" name="date_acte_naissance"
               value="<?= SecurityHelper::e($old['date_acte_naissance'] ?? '') ?>">
      </div>
      <div class="fie-field">
        <label for="commune_acte">Commune de l'acte</label>
        <input type="text" id="commune_acte" name="commune_acte" maxlength="100"
               value="<?= SecurityHelper::e($old['commune_acte'] ?? '') ?>">
      </div>
    </div>
  </fieldset>

  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <!-- SECTION 3 : TUTEUR / PARENT                                           -->
  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <fieldset class="fie-fieldset fie-fieldset--collapsible">
    <legend>3. Tuteur / Parent</legend>
    <div class="fie-form-grid">
      <div class="fie-field">
        <label for="nom_pere">Nom du père</label>
        <input type="text" id="nom_pere" name="nom_pere" maxlength="150"
               value="<?= SecurityHelper::e($old['nom_pere'] ?? '') ?>">
      </div>
      <div class="fie-field">
        <label for="nom_mere">Nom de la mère</label>
        <input type="text" id="nom_mere" name="nom_mere" maxlength="150"
               value="<?= SecurityHelper::e($old['nom_mere'] ?? '') ?>">
      </div>
      <div class="fie-field">
        <label for="nom_tuteur">Tuteur légal</label>
        <input type="text" id="nom_tuteur" name="nom_tuteur" maxlength="150"
               value="<?= SecurityHelper::e($old['nom_tuteur'] ?? '') ?>">
      </div>
      <div class="fie-field">
        <label for="telephone_tuteur">Téléphone tuteur</label>
        <input type="tel" id="telephone_tuteur" name="telephone_tuteur" maxlength="30"
               value="<?= SecurityHelper::e($old['telephone_tuteur'] ?? '') ?>"
               placeholder="+257 XX XXX XXX">
      </div>
    </div>
  </fieldset>

  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <!-- SECTION 4 : ÉTABLISSEMENT & NIVEAU (Sélects dépendants AJAX)         -->
  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <fieldset class="fie-fieldset">
    <legend>4. Scolarisation — Année <?= SecurityHelper::e($anneeActive['libelle'] ?? '') ?></legend>

    <!-- Sous-secteur -->
    <div class="fie-field <?= isset($ferr['code_type_secteur_ens']) ? 'fie-field--error' : '' ?>">
      <label for="code_type_secteur_ens">Sous-secteur <span class="fie-required">*</span></label>
      <select id="code_type_secteur_ens" name="code_type_secteur_ens" required>
        <option value="">-- Sélectionner --</option>
        <?php foreach ($secteurs as $s): ?>
        <option value="<?= (int)$s['code_type_secteur_ens'] ?>"
          <?= ((int)($old['code_type_secteur_ens'] ?? 0) === (int)$s['code_type_secteur_ens']) ? 'selected' : '' ?>>
          <?= SecurityHelper::e($s['libelle']) ?>
        </option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($ferr['code_type_secteur_ens'])): ?>
      <span class="fie-field-error"><?= SecurityHelper::e($ferr['code_type_secteur_ens']) ?></span>
      <?php endif; ?>
    </div>

    <!-- Niveau -->
    <div class="fie-field <?= isset($ferr['code_type_niveau']) ? 'fie-field--error' : '' ?>">
      <label for="code_type_niveau">Niveau <span class="fie-required">*</span></label>
      <select id="code_type_niveau" name="code_type_niveau" required>
        <option value="">-- Sélectionner d'abord le sous-secteur --</option>
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
    <div class="fie-field">
      <label for="code_type_section">Section</label>
      <select id="code_type_section" name="code_type_section">
        <?php foreach ($sections as $sec): ?>
        <option value="<?= (int)$sec['code_type_section'] ?>"
          <?= ((int)($old['code_type_section'] ?? 1) === (int)$sec['code_type_section']) ? 'selected' : '' ?>>
          <?= SecurityHelper::e($sec['libelle']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Classe -->
    <div class="fie-field">
      <label for="numero_classe">N° de classe</label>
      <input type="text" id="numero_classe" name="numero_classe" maxlength="20"
             value="<?= SecurityHelper::e($old['numero_classe'] ?? '') ?>"
             placeholder="ex: 2AF-B">
    </div>

    <!-- Sélects dépendants Province → Commune → Zone → Colline → Établissement -->
    <div class="fie-loc-grid">
      <div class="fie-field">
        <label for="province">Province <span class="fie-required">*</span></label>
        <select id="province" name="_province" required>
          <option value="">-- Province --</option>
          <?php foreach ($provinces as $p): ?>
          <option value="<?= SecurityHelper::e($p['province']) ?>"
            <?= (($old['_province'] ?? '') === $p['province']) ? 'selected' : '' ?>>
            <?= SecurityHelper::e($p['province']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="fie-field">
        <label for="commune">Commune <span class="fie-required">*</span></label>
        <select id="commune" name="_commune" required disabled>
          <option value="">-- Commune --</option>
        </select>
      </div>

      <div class="fie-field">
        <label for="zone">Zone</label>
        <select id="zone" name="_zone" disabled>
          <option value="">-- Zone --</option>
        </select>
      </div>

      <div class="fie-field">
        <label for="colline">Colline</label>
        <select id="colline" name="_colline" disabled>
          <option value="">-- Colline --</option>
        </select>
      </div>
    </div><!-- .fie-loc-grid -->

    <div class="fie-field <?= isset($ferr['code_etablissement']) ? 'fie-field--error' : '' ?>">
      <label for="code_etablissement">Établissement <span class="fie-required">*</span></label>
      <select id="code_etablissement" name="code_etablissement" required disabled>
        <option value="">-- Sélectionner la localisation d'abord --</option>
      </select>
      <span class="fie-hint">
        Alimenté depuis le référentiel StatEduc synchronisé.
      </span>
      <?php if (isset($ferr['code_etablissement'])): ?>
      <span class="fie-field-error"><?= SecurityHelper::e($ferr['code_etablissement']) ?></span>
      <?php endif; ?>
    </div>

    <!-- Date d'inscription -->
    <div class="fie-field <?= isset($ferr['date_inscription']) ? 'fie-field--error' : '' ?>">
      <label for="date_inscription">Date d'inscription <span class="fie-required">*</span></label>
      <input type="date" id="date_inscription" name="date_inscription"
             value="<?= SecurityHelper::e($old['date_inscription'] ?? date('Y-m-d')) ?>"
             required>
    </div>

  </fieldset>

  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <!-- BOUTONS                                                                -->
  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <div class="fie-form-actions">
    <button type="submit" id="btn-submit-insc" class="fie-btn fie-btn--primary fie-btn--large">
      ✅ Inscrire et générer l'IUE
    </button>
    <a href="<?= FIE_BASE_URL ?>inscription/search" class="fie-btn fie-btn--ghost">
      🔍 Rechercher un élève existant
    </a>
    <a href="<?= FIE_BASE_URL ?>" class="fie-btn fie-btn--ghost">Annuler</a>
  </div>

</form>

<script>
/* ═══════════════════════════════════════════════════════════════════════════
   JAVASCRIPT DU FORMULAIRE D'INSCRIPTION
   • Sélects dépendants Province → Commune → Zone → Colline → Établissement
   • Filtre dynamique des niveaux par sous-secteur
   • Vérification de doublon AJAX
   ═══════════════════════════════════════════════════════════════════════════ */
(function() {
  'use strict';

  const BASE = '<?= FIE_BASE_URL ?>';

  // ── Utilitaire AJAX POST ──────────────────────────────────────────────────
  function postJSON(url, data, cb) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(data)) fd.append(k, v);
    fetch(url, {method:'POST', body: fd})
      .then(r => r.json())
      .then(cb)
      .catch(e => console.error('AJAX error:', e));
  }

  function setSelectOptions(sel, items, valKey, textKey, placeholder) {
    sel.innerHTML = '<option value="">' + placeholder + '</option>';
    items.forEach(function(item) {
      const opt = document.createElement('option');
      opt.value       = item[valKey] !== undefined ? item[valKey] : item;
      opt.textContent = item[textKey] !== undefined ? item[textKey] : item;
      sel.appendChild(opt);
    });
    sel.disabled = items.length === 0;
  }

  // ── Sélects dépendants ────────────────────────────────────────────────────
  const selProvince   = document.getElementById('province');
  const selCommune    = document.getElementById('commune');
  const selZone       = document.getElementById('zone');
  const selColline    = document.getElementById('colline');
  const selEtab       = document.getElementById('code_etablissement');
  const selSecteur    = document.getElementById('code_type_secteur_ens');

  selProvince.addEventListener('change', function() {
    selCommune.disabled = true;
    selZone.disabled    = true;
    selColline.disabled = true;
    selEtab.disabled    = true;
    selCommune.innerHTML = '<option value="">Chargement...</option>';
    if (!this.value) return;
    postJSON(BASE + 'inscription/ajax/communes', {province: this.value}, function(resp) {
      setSelectOptions(selCommune, resp.communes || [], null, null, '-- Commune --');
      selCommune.disabled = false;
    });
  });

  selCommune.addEventListener('change', function() {
    selZone.disabled    = true;
    selColline.disabled = true;
    selEtab.disabled    = true;
    if (!this.value) return;
    postJSON(BASE + 'inscription/ajax/zones',
      {province: selProvince.value, commune: this.value}, function(resp) {
      const zones = resp.zones || [];
      if (zones.length > 0) {
        setSelectOptions(selZone, zones, null, null, '-- Zone --');
        selZone.disabled = false;
      } else {
        // Pas de zones → charger directement les collines
        postJSON(BASE + 'inscription/ajax/collines',
          {province: selProvince.value, commune: selCommune.value, zone: ''}, function(r) {
          setSelectOptions(selColline, r.collines || [], null, null, '-- Colline --');
          selColline.disabled = (r.collines || []).length === 0;
        });
      }
    });
  });

  selZone.addEventListener('change', function() {
    selColline.disabled = true;
    selEtab.disabled    = true;
    postJSON(BASE + 'inscription/ajax/collines',
      {province: selProvince.value, commune: selCommune.value, zone: this.value}, function(resp) {
      setSelectOptions(selColline, resp.collines || [], null, null, '-- Colline --');
      selColline.disabled = (resp.collines || []).length === 0;
    });
  });

  function loadEtablissements() {
    const province = selProvince.value;
    const commune  = selCommune.value;
    if (!province || !commune) return;
    selEtab.disabled = true;
    selEtab.innerHTML = '<option value="">Chargement...</option>';
    postJSON(BASE + 'inscription/ajax/etablissements', {
      province: province,
      commune:  commune,
      zone:     selZone.value,
      colline:  selColline.value,
      secteur:  selSecteur.value,
    }, function(resp) {
      const etabs = resp.etablissements || [];
      selEtab.innerHTML = '<option value="">-- Établissement --</option>';
      etabs.forEach(function(e) {
        const opt = document.createElement('option');
        opt.value       = e.code_etablissement;
        opt.textContent = e.nom_etablissement;
        selEtab.appendChild(opt);
      });
      selEtab.disabled = etabs.length === 0;
    });
  }

  selColline.addEventListener('change', loadEtablissements);
  selZone.addEventListener('change', function() {
    if (!selColline.value) loadEtablissements();
  });
  selSecteur.addEventListener('change', function() {
    // Re-filtrer les niveaux
    filterNiveaux(this.value);
    // Recharger les établissements si localisation déjà sélectionnée
    if (selCommune.value) loadEtablissements();
  });

  // ── Filtre dynamique des niveaux par sous-secteur ─────────────────────────
  function filterNiveaux(secteurCode) {
    const sel = document.getElementById('code_type_niveau');
    const opts = sel.querySelectorAll('option[data-secteur]');
    let firstVisible = null;
    opts.forEach(function(opt) {
      const match = !secteurCode || opt.dataset.secteur === secteurCode;
      opt.hidden = !match;
      opt.disabled = !match;
      if (match && !firstVisible) firstVisible = opt;
    });
    sel.value = '';
  }
  // Init au chargement si secteur déjà sélectionné (retour formulaire)
  if (selSecteur.value) filterNiveaux(selSecteur.value);

  // ── Vérification doublon AJAX ─────────────────────────────────────────────
  const btnDoublon    = document.getElementById('btn-check-doublon');
  const alertDoublon  = document.getElementById('fie-doublon-alert');
  const listDoublon   = document.getElementById('fie-doublon-list');
  const msgDoublon    = document.getElementById('fie-doublon-msg');
  const statusDoublon = document.getElementById('fie-doublon-status');
  const confirmCheck  = document.getElementById('fie-confirm-no-doublon');
  const btnSubmit     = document.getElementById('btn-submit-insc');

  function checkDoublon() {
    const nom = document.getElementById('nom').value.trim();
    const prn = document.getElementById('prenoms').value.trim();
    const ddn = document.getElementById('date_naissance').value;
    const lieu = document.getElementById('lieu_naissance').value.trim();
    if (!nom || !prn || !ddn) {
      alert('Veuillez renseigner le nom, les prénoms et la date de naissance avant de vérifier les doublons.');
      return;
    }
    statusDoublon.textContent = 'Vérification en cours…';
    btnDoublon.disabled = true;
    postJSON(BASE + 'inscription/ajax/check_doublon',
      {nom: nom, prenoms: prn, date_naissance: ddn, lieu_naissance: lieu},
      function(resp) {
        btnDoublon.disabled = false;
        statusDoublon.textContent = '';
        if (resp.count > 0) {
          msgDoublon.textContent = resp.count + ' élève(s) potentiellement similaire(s) trouvé(s) :';
          listDoublon.innerHTML  = '';
          resp.doublons.forEach(function(d) {
            const li = document.createElement('li');
            li.innerHTML = '<strong>' + d.iue + '</strong> — '
              + d.nom + ' ' + d.prenoms + ' — né(e) le ' + d.date_naissance
              + (d.lieu_naissance ? ' à ' + d.lieu_naissance : '')
              + ' <a href="' + BASE + 'inscription/detail/' + encodeURIComponent(d.iue)
              + '" target="_blank">Voir la fiche</a>';
            listDoublon.appendChild(li);
          });
          alertDoublon.style.display = '';
          btnSubmit.disabled = true; // Forcer confirmation
        } else {
          alertDoublon.style.display = 'none';
          statusDoublon.textContent  = '✅ Aucun doublon détecté.';
          statusDoublon.style.color  = 'green';
          btnSubmit.disabled = false;
        }
      });
  }

  btnDoublon.addEventListener('click', checkDoublon);

  confirmCheck.addEventListener('change', function() {
    btnSubmit.disabled = !this.checked;
  });

  // ── Validation avant soumission ───────────────────────────────────────────
  document.getElementById('fie-insc-form').addEventListener('submit', function(e) {
    // Vérifier doublon non confirmé
    if (alertDoublon.style.display !== 'none' && !confirmCheck.checked) {
      e.preventDefault();
      alert('Veuillez confirmer qu\'il ne s\'agit pas d\'un doublon avant de soumettre.');
      return;
    }
    // Vérifier établissement
    if (!selEtab.value) {
      e.preventDefault();
      alert('Veuillez sélectionner un établissement.');
      selEtab.focus();
    }
  });

})();
</script>

<?php require FIE_VIEWS_PATH . 'layouts/footer.php'; ?>
