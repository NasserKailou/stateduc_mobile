<?php
/**
 * FIE — Vue : Formulaire de nouvelle inscription / émission d'IUE
 * Session 13 :
 *   - Nationalités depuis ref_type_nationalite (AJAX + champ libre si "Autres")
 *   - Vérification doublon : approche INLINE (résultat sous le bouton, plus de modal)
 * Variables injectées par InscriptionController::newForm() :
 *   $annees, $anneeActive, $secteurs, $niveaux, $sections,
 *   $provinces, $nationalites, $csrf, $lastSync, $nbEtabs
 */
$page_title  = "Nouvelle Inscription — FIE";
$active_menu = 'inscription';
require BASE_PATH . '/app/views/layouts/app_layout.php';

$old  = $_SESSION['fie_form_old']     ?? [];
$ferr = $_SESSION['fie_field_errors'] ?? [];
unset($_SESSION['fie_form_old'], $_SESSION['fie_field_errors']);
?>

<!-- ── Fil d'Ariane ────────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inscription">Inscriptions</a></li>
        <li class="breadcrumb-item active">Nouvelle inscription</li>
    </ol>
</nav>

<!-- ── Titre ────────────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="fa-solid fa-user-plus me-2" style="color:var(--fie-red)"></i>
            Nouvelle Inscription — Émission d'IUE
        </h1>
        <p class="text-muted mb-0 small">
            Référentiel : <strong><?= $nbEtabs ?> établissements</strong> chargés
            <?php if ($lastSync): ?>
            — sync le <strong><?= SecurityHelper::e(date('d/m/Y H:i', strtotime($lastSync))) ?></strong>
            <?php endif; ?>
        </p>
    </div>
    <?php if (!$lastSync): ?>
    <a href="<?= BASE_URL ?>/admin/sync" class="btn btn-sm btn-warning">
        <i class="fa-solid fa-rotate me-1"></i>Synchroniser
    </a>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     FORMULAIRE PRINCIPAL
     ═══════════════════════════════════════════════════════════════════════════ -->
<form method="POST" action="<?= BASE_URL ?>/inscription/nouveau"
      id="fie-insc-form" novalidate>

    <?= SecurityHelper::csrfField() ?>

    <!-- ══ SECTION 1 : ÉTAT CIVIL ════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-semibold" style="background:var(--fie-red);color:#fff">
            <i class="fa-solid fa-person me-2"></i>1. État civil de l'élève
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nom" class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                    <input type="text" id="nom" name="nom" required maxlength="100"
                           class="form-control <?= isset($ferr['nom']) ? 'is-invalid' : '' ?>"
                           value="<?= SecurityHelper::e($old['nom'] ?? '') ?>"
                           placeholder="ex : NIYONZIMA">
                    <?php if (isset($ferr['nom'])): ?><div class="invalid-feedback"><?= SecurityHelper::e($ferr['nom']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="prenoms" class="form-label fw-semibold">Prénom(s) <span class="text-danger">*</span></label>
                    <input type="text" id="prenoms" name="prenoms" required maxlength="150"
                           class="form-control <?= isset($ferr['prenoms']) ? 'is-invalid' : '' ?>"
                           value="<?= SecurityHelper::e($old['prenoms'] ?? '') ?>"
                           placeholder="ex : Jean-Pierre">
                    <?php if (isset($ferr['prenoms'])): ?><div class="invalid-feedback"><?= SecurityHelper::e($ferr['prenoms']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Sexe <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sexe" id="sexeM" value="M"
                                   <?= (($old['sexe'] ?? '') === 'M') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sexeM"><i class="fa-solid fa-mars text-primary me-1"></i>Masculin</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sexe" id="sexeF" value="F"
                                   <?= (($old['sexe'] ?? '') === 'F') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sexeF"><i class="fa-solid fa-venus text-danger me-1"></i>Féminin</label>
                        </div>
                    </div>
                    <?php if (isset($ferr['sexe'])): ?><div class="text-danger small mt-1"><?= SecurityHelper::e($ferr['sexe']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="date_naissance" class="form-label fw-semibold">Date de naissance <span class="text-danger">*</span></label>
                    <input type="date" id="date_naissance" name="date_naissance" required
                           class="form-control <?= isset($ferr['date_naissance']) ? 'is-invalid' : '' ?>"
                           max="<?= date('Y-m-d') ?>"
                           value="<?= SecurityHelper::e($old['date_naissance'] ?? '') ?>">
                    <?php if (isset($ferr['date_naissance'])): ?><div class="invalid-feedback"><?= SecurityHelper::e($ferr['date_naissance']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="lieu_naissance" class="form-label fw-semibold">Lieu de naissance</label>
                    <input type="text" id="lieu_naissance" name="lieu_naissance" maxlength="150"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['lieu_naissance'] ?? '') ?>"
                           placeholder="ex : Gitega">
                </div>
                <div class="col-md-4">
                    <label for="province_naissance" class="form-label fw-semibold">Province / Région de naissance</label>
                    <!-- Champ texte libre avec suggestions (datalist) pour permettre
                         les naissances hors Burundi (autre pays, autre région) -->
                    <input type="text" id="province_naissance" name="province_naissance"
                           list="list_provinces_naiss"
                           class="form-control"
                           maxlength="150"
                           value="<?= SecurityHelper::e($old['province_naissance'] ?? '') ?>"
                           placeholder="ex : Gitega, Bujumbura, Kigali, Paris...">
                    <datalist id="list_provinces_naiss">
                        <?php foreach ($provinces as $p):
                            $prov = $p['libelle'] ?? $p['province'] ?? ''; ?>
                        <option value="<?= SecurityHelper::e($prov) ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <div class="form-text text-muted" style="font-size:.78rem;">
                        Saisissez ou sélectionnez — peut être hors Burundi
                    </div>
                </div>

                <!-- ── Nationalité (depuis ref_type_nationalite) ── -->
                <div class="col-md-4">
                    <label for="nationalite" class="form-label fw-semibold">Nationalité</label>
                    <select id="nationalite" name="nationalite" class="form-select">
                        <?php
                        $oldNat = $old['nationalite'] ?? 'BDI';
                        // Détecter si l'ancienne valeur correspond à une option connue
                        $natCodes = array_column($nationalites ?? [], 'code');
                        $natLibelles = array_column($nationalites ?? [], 'libelle');
                        ?>
                        <?php if (!empty($nationalites)): ?>
                            <?php foreach ($nationalites as $nat): ?>
                            <?php
                                // Chaque option a pour value le libellé ISO-compatible (ou 'AUTRE' pour Autres)
                                $isAutre = (strtoupper($nat['libelle']) === 'AUTRES' || (int)$nat['code'] === 99);
                                $natVal  = $isAutre ? 'AUTRE' : SecurityHelper::e($nat['libelle']);
                                $selected = ($oldNat === $natVal || (!$isAutre && strtoupper($oldNat) === strtoupper(substr($nat['libelle'],0,3))));
                            ?>
                            <option value="<?= $natVal ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= SecurityHelper::e($nat['libelle']) ?>
                            </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="BDI" <?= $oldNat === 'BDI' ? 'selected' : '' ?>>Burundaise</option>
                            <option value="AUTRE">Autres</option>
                        <?php endif; ?>
                    </select>
                </div>
                <!-- Champ libre affiché si "Autres" sélectionné -->
                <div class="col-md-4" id="field-nat-autre" style="display:none;">
                    <label for="nationalite_autre" class="form-label fw-semibold">
                        Préciser la nationalité <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="nationalite_autre" name="nationalite_autre"
                           class="form-control" maxlength="50"
                           placeholder="ex : Français, Américain…"
                           value="<?= SecurityHelper::e($old['nationalite_autre'] ?? '') ?>">
                    <div class="form-text">Entrez le nom de la nationalité</div>
                </div>
            </div>

            <!-- Bouton vérification doublon — approche INLINE -->
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" id="btn-check-doublon" class="btn btn-outline-warning btn-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i>Vérifier les doublons
                    </button>
                    <span id="fie-doublon-spinner" class="d-none">
                        <span class="spinner-border spinner-border-sm text-warning me-1"></span>
                        Vérification…
                    </span>
                </div>
                <!-- Zone résultat inline (visible après clic) -->
                <div id="fie-doublon-result" class="mt-2" style="display:none;"></div>
            </div>
        </div>
    </div>

    <!-- ══ SECTION 2 : ACTE DE NAISSANCE ══════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-semibold bg-light">
            <i class="fa-solid fa-file-contract me-2 text-secondary"></i>
            2. Acte de naissance <span class="badge bg-secondary ms-2 fw-normal">facultatif</span>
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

    <!-- ══ SECTION 3 : TUTEUR / PARENT ════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-semibold bg-light">
            <i class="fa-solid fa-people-roof me-2 text-secondary"></i>3. Tuteur / Parent
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nom_pere" class="form-label">Nom du père</label>
                    <input type="text" id="nom_pere" name="nom_pere" maxlength="150"
                           class="form-control" value="<?= SecurityHelper::e($old['nom_pere'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="nom_mere" class="form-label">Nom de la mère</label>
                    <input type="text" id="nom_mere" name="nom_mere" maxlength="150"
                           class="form-control" value="<?= SecurityHelper::e($old['nom_mere'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="nom_tuteur" class="form-label">Tuteur légal</label>
                    <input type="text" id="nom_tuteur" name="nom_tuteur" maxlength="150"
                           class="form-control" value="<?= SecurityHelper::e($old['nom_tuteur'] ?? '') ?>">
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

    <!-- ══ SECTION 4 : SCOLARISATION + ÉTABLISSEMENT ══════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-semibold" style="background:var(--fie-green);color:#fff">
            <i class="fa-solid fa-school me-2"></i>4. Scolarisation
        </div>
        <div class="card-body">
            <div class="row g-3">

                <!-- Année scolaire SELECT (toutes les années, actif présélectionné) -->
                <div class="col-md-4">
                    <label for="code_type_annee" class="form-label fw-semibold">
                        Année scolaire <span class="text-danger">*</span>
                    </label>
                    <select id="code_type_annee" name="code_type_annee" required
                            class="form-select <?= isset($ferr['code_type_annee']) ? 'is-invalid' : '' ?>">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($annees as $an): ?>
                        <option value="<?= (int)$an['code_type_annee'] ?>"
                            <?= ((int)($old['code_type_annee'] ?? $anneeActive['code_type_annee'] ?? 0) === (int)$an['code_type_annee']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($an['libelle']) ?>
                            <?php if ($an['actif']): ?>(courante)<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($ferr['code_type_annee'])): ?>
                    <div class="invalid-feedback"><?= SecurityHelper::e($ferr['code_type_annee']) ?></div>
                    <?php endif; ?>
                    <div class="form-text">
                        <a href="#" id="btn-sync-annees" class="text-decoration-none small">
                            <i class="fa-solid fa-rotate me-1"></i>Actualiser depuis StatEduc
                        </a>
                    </div>
                </div>

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
                        <option value="">— Sous-secteur d'abord —</option>
                        <?php foreach ($niveaux as $niv): ?>
                        <option value="<?= (int)$niv['code_type_niveau'] ?>"
                                data-secteur="<?= (int)$niv['code_secteur'] ?>"
                            <?= ((int)($old['code_type_niveau'] ?? 0) === (int)$niv['code_type_niveau']) ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($niv['libelle']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Section + Classe -->
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label for="numero_classe" class="form-label fw-semibold">Classe</label>
                    <input type="text" id="numero_classe" name="numero_classe" maxlength="20"
                           class="form-control"
                           value="<?= SecurityHelper::e($old['numero_classe'] ?? '') ?>"
                           placeholder="ex: 2AF-B">
                </div>
                <div class="col-md-3">
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

            <!-- Localisation établissement (cascade ATLAS_COLLINE par codes) -->
            <hr class="my-3">
            <p class="fw-semibold small mb-2">
                <i class="fa-solid fa-location-dot me-1" style="color:var(--fie-red)"></i>
                Localisation de l'établissement <span class="text-danger">*</span>
            </p>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="sel-province" class="form-label">Province</label>
                    <select id="sel-province" class="form-select">
                        <option value="">— Province —</option>
                        <?php foreach ($provinces as $p): ?>
                        <option value="<?= (int)($p['code_province'] ?? 0) ?>"
                                data-libelle="<?= SecurityHelper::e($p['libelle'] ?? $p['province'] ?? '') ?>"
                                <?= ((int)($old['_cp'] ?? 0) === (int)($p['code_province'] ?? 0)) ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($p['libelle'] ?? $p['province'] ?? '') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sel-commune" class="form-label">Commune</label>
                    <select id="sel-commune" class="form-select" disabled>
                        <option value="">— Commune —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sel-colline" class="form-label">Colline</label>
                    <select id="sel-colline" class="form-select" disabled>
                        <option value="">— Colline —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <!-- Info auto-remplie après sélection établissement -->
                    <label class="form-label">Informations</label>
                    <div id="etab-info" class="small text-muted p-2 border rounded bg-light" style="min-height:38px;">
                        Sélectionner un établissement
                    </div>
                </div>
            </div>

            <!-- Établissement -->
            <div class="row g-3 mt-1">
                <div class="col-md-12">
                    <label for="code_etablissement" class="form-label fw-semibold">
                        Établissement <span class="text-danger">*</span>
                    </label>
                    <select id="code_etablissement" name="code_etablissement" required
                            class="form-select <?= isset($ferr['code_etablissement']) ? 'is-invalid' : '' ?>"
                            disabled>
                        <option value="">— Sélectionner province → commune → colline d'abord —</option>
                    </select>
                    <?php if (isset($ferr['code_etablissement'])): ?>
                    <div class="invalid-feedback"><?= SecurityHelper::e($ferr['code_etablissement']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- ══ BOUTONS ════════════════════════════════════════════════════════════ -->
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <button type="submit" id="btn-submit-insc" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-id-card me-2"></i>Inscrire et générer l'IUE
        </button>
        <a href="<?= BASE_URL ?>/inscription/recherche" class="btn btn-outline-secondary">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Rechercher un élève
        </a>
        <a href="<?= BASE_URL ?>/tableau-de-bord" class="btn btn-outline-secondary">
            <i class="fa-solid fa-xmark me-1"></i>Annuler
        </a>
    </div>

</form>

<!-- ═══════════════════════════════════════════════════════════════════════════
     JAVASCRIPT — Cascades ATLAS_COLLINE + doublon INLINE + nationalité + sync
     ═══════════════════════════════════════════════════════════════════════════ -->
<script>
(function () {
  'use strict';

  const BASE = '<?= BASE_URL ?>/';
  const CSRF = document.querySelector('input[name="<?= FIE_CSRF_TOKEN_NAME ?>"]')?.value ?? '';

  /* ── Utilitaires fetch ─────────────────────────────────────────────────────── */
  function getJSON(url, cb, errCb) {
    fetch(url)
      .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status + ' ' + r.statusText);
        return r.json();
      })
      .then(cb)
      .catch(function(e) { console.error('GET', url, e); if (errCb) errCb(e); });
  }
  function postJSON(url, data, cb, errCb) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v]) => fd.append(k, v));
    fetch(url, {method:'POST', body:fd})
      .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status + ' ' + r.statusText);
        return r.json();
      })
      .then(cb)
      .catch(function(e) {
        console.error('POST', url, e);
        if (errCb) errCb(e);
      });
  }
  function fillSelect(sel, items, placeholder, codeKey, libKey) {
    codeKey = codeKey || 'code';
    libKey  = libKey  || 'libelle';
    sel.innerHTML = '<option value="">' + placeholder + '</option>';
    (items||[]).forEach(function(item){
      const o = document.createElement('option');
      o.value       = item[codeKey];
      o.textContent = item[libKey];
      Object.keys(item).forEach(k => o.dataset[k.replace(/_([a-z])/g, (_,c)=>c.toUpperCase())] = item[k]);
      sel.appendChild(o);
    });
    sel.disabled = (!items || items.length === 0);
  }

  /* ── Références DOM ─────────────────────────────────────────────────────── */
  const selProv    = document.getElementById('sel-province');
  const selComm    = document.getElementById('sel-commune');
  const selColl    = document.getElementById('sel-colline');
  const selEtab    = document.getElementById('code_etablissement');
  const selSecteur = document.getElementById('code_type_secteur_ens');
  const selNiveau  = document.getElementById('code_type_niveau');
  const etabInfo   = document.getElementById('etab-info');

  function resetFrom(sel) {
    sel.innerHTML = '<option value="">...</option>';
    sel.disabled = true;
  }

  /* ── Province → Communes ─────────────────────────────────────────────────── */
  selProv.addEventListener('change', function() {
    [selComm, selColl, selEtab].forEach(resetFrom);
    etabInfo.innerHTML = 'Sélectionner un établissement';
    const cp = parseInt(this.value);
    if (!cp) return;
    getJSON(BASE + 'inscription/ajax/communes-code?code_province=' + cp, function(d) {
      fillSelect(selComm, d.items, '— Commune —', 'code', 'libelle');
    });
  });

  /* ── Commune → Collines ──────────────────────────────────────────────────── */
  selComm.addEventListener('change', function() {
    [selColl, selEtab].forEach(resetFrom);
    etabInfo.innerHTML = 'Sélectionner un établissement';
    const cc = parseInt(this.value);
    if (!cc) return;
    getJSON(BASE + 'inscription/ajax/collines-code?code_commune=' + cc, function(d) {
      fillSelect(selColl, d.items, '— Colline —', 'code', 'libelle');
      if (!d.items || d.items.length === 0) loadEtabs();
    });
  });

  /* ── Colline → Établissements ────────────────────────────────────────────── */
  selColl.addEventListener('change', loadEtabs);
  selSecteur.addEventListener('change', function() {
    filterNiveaux(this.value);
    if (selComm.value) loadEtabs();
  });

  function loadEtabs() {
    resetFrom(selEtab);
    etabInfo.innerHTML = 'Chargement…';
    const cc  = parseInt(selComm.value) || 0;
    const ccl = parseInt(selColl.value) || 0;
    const sec = parseInt(selSecteur.value) || 0;
    if (!cc) { resetFrom(selEtab); etabInfo.innerHTML = 'Sélectionner une commune'; return; }

    const url = BASE + 'inscription/ajax/etabs-code?code_commune=' + cc
              + '&code_colline=' + ccl
              + (sec ? '&secteur=' + sec : '');

    getJSON(url, function(d) {
      selEtab.innerHTML = '<option value="">— Établissement —</option>';
      (d.items||[]).forEach(function(e) {
        const o = document.createElement('option');
        o.value = e.code;
        o.textContent = e.libelle;
        o.dataset.province   = e.province   || '';
        o.dataset.commune    = e.commune    || '';
        o.dataset.colline    = e.colline    || '';
        o.dataset.secteurEns = e.secteur_ens|| '';
        o.dataset.statutOrg  = e.statut_org || '';
        o.dataset.milieu     = e.milieu     || '';
        o.dataset.chaine     = e.chaine     || '';
        selEtab.appendChild(o);
      });
      selEtab.disabled = (!d.items || d.items.length === 0);
      etabInfo.innerHTML = d.items && d.items.length > 0
        ? '<span class="text-success"><i class="fa-solid fa-check me-1"></i>' + d.items.length + ' établissement(s)</span>'
        : '<span class="text-muted">Aucun établissement trouvé</span>';
    });
  }

  /* ── Sélection établissement → auto-remplissage infos ──────────────────── */
  selEtab.addEventListener('change', function() {
    const opt = this.selectedOptions[0];
    if (!opt || !opt.value) {
      etabInfo.innerHTML = 'Sélectionner un établissement';
      return;
    }
    const html = [
      opt.dataset.secteurEns ? '<span class="badge bg-primary me-1">' + opt.dataset.secteurEns + '</span>' : '',
      opt.dataset.statutOrg  ? '<span class="badge bg-secondary me-1">' + opt.dataset.statutOrg + '</span>'  : '',
      opt.dataset.milieu     ? '<span class="badge bg-success">' + opt.dataset.milieu + '</span>'             : '',
      opt.dataset.colline    ? '<br><small class="text-muted">Colline : ' + opt.dataset.colline + '</small>' : '',
    ].join('');
    etabInfo.innerHTML = html || '<span class="text-muted">Info non disponible</span>';
  });

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

  /* ── Nationalité : afficher/masquer champ libre ──────────────────────────── */
  const selNat       = document.getElementById('nationalite');
  const fieldNatAutre = document.getElementById('field-nat-autre');
  const inputNatAutre = document.getElementById('nationalite_autre');

  function toggleNatAutre() {
    const isAutre = selNat.value === 'AUTRE';
    fieldNatAutre.style.display = isAutre ? '' : 'none';
    if (inputNatAutre) inputNatAutre.required = isAutre;
  }
  if (selNat) {
    selNat.addEventListener('change', toggleNatAutre);
    toggleNatAutre(); // init
  }

  /* ── Sync années scolaires ───────────────────────────────────────────────── */
  const btnSyncAnnees = document.getElementById('btn-sync-annees');
  if (btnSyncAnnees) {
    btnSyncAnnees.addEventListener('click', function(e) {
      e.preventDefault();
      btnSyncAnnees.textContent = 'Synchronisation…';
      postJSON(BASE + 'inscription/ajax/sync-annees', {'<?= FIE_CSRF_TOKEN_NAME ?>': CSRF},
        function(d) {
          btnSyncAnnees.innerHTML = '<i class="fa-solid fa-rotate me-1"></i>Actualiser depuis StatEduc';
          if (d.success) {
            alert(d.message + '\nRechargez la page pour voir les années mises à jour.');
            window.location.reload();
          } else {
            alert('Erreur : ' + (d.message || 'Sync échouée'));
          }
        },
        function(err) {
          btnSyncAnnees.innerHTML = '<i class="fa-solid fa-rotate me-1"></i>Actualiser depuis StatEduc';
          alert('Erreur réseau lors de la synchronisation : ' + (err ? err.message : 'Réponse inattendue'));
        }
      );
    });
  }

  /* ── DOUBLON — approche INLINE (résultat affiché sous le bouton) ─────────── */
  const btnDoublon     = document.getElementById('btn-check-doublon');
  const doublonSpinner = document.getElementById('fie-doublon-spinner');
  const doublonResult  = document.getElementById('fie-doublon-result');

  if (btnDoublon) {
    btnDoublon.addEventListener('click', function() {
      const nom = document.getElementById('nom').value.trim();
      const prn = document.getElementById('prenoms').value.trim();
      const ddn = document.getElementById('date_naissance').value;

      if (!nom || !prn || !ddn) {
        doublonResult.style.display = '';
        doublonResult.innerHTML = '<div class="alert alert-info py-2 mt-1">'
          + '<i class="fa-solid fa-circle-info me-2"></i>'
          + 'Renseignez le nom, les prénoms et la date de naissance avant de vérifier.'
          + '</div>';
        return;
      }

      // État chargement
      btnDoublon.disabled    = true;
      doublonSpinner.classList.remove('d-none');
      doublonResult.style.display = 'none';
      doublonResult.innerHTML = '';

      postJSON(BASE + 'inscription/ajax/doublon',
        {nom: nom, prenoms: prn, date_naissance: ddn, '<?= FIE_CSRF_TOKEN_NAME ?>': CSRF},

        function(resp) {
          btnDoublon.disabled = false;
          doublonSpinner.classList.add('d-none');
          doublonResult.style.display = '';

          if (resp.found && resp.count > 0) {
            // ── Doublons trouvés ──
            let rows = '';
            (resp.doublons || []).forEach(function(d) {
              rows += '<tr>'
                + '<td><a href="' + BASE + 'inscription/' + encodeURIComponent(d.iue) + '" target="_blank" class="fw-bold">' + d.iue + '</a></td>'
                + '<td>' + d.nom + ' ' + d.prenoms + '</td>'
                + '<td>' + (d.date_naissance || '') + '</td>'
                + '<td>' + (d.lieu_naissance || '') + '</td>'
                + '<td>' + (d.etablissement || '') + '</td>'
                + '<td>' + (d.annee_scolaire || '') + '</td>'
                + '</tr>';
            });

            doublonResult.innerHTML =
              '<div class="alert alert-warning py-2 mb-2">'
              + '<i class="fa-solid fa-triangle-exclamation me-2"></i>'
              + '<strong>' + resp.count + ' élève(s) similaire(s) trouvé(s).</strong> '
              + 'Vérifiez qu\'il ne s\'agit pas du même élève avant de continuer.'
              + '</div>'
              + '<div class="table-responsive mb-2">'
              + '<table class="table table-sm table-bordered table-hover mb-0">'
              + '<thead class="table-warning"><tr><th>IUE</th><th>Nom &amp; Prénoms</th><th>Né(e) le</th><th>Lieu</th><th>Établissement</th><th>Année</th></tr></thead>'
              + '<tbody>' + rows + '</tbody></table></div>'
              + '<div class="form-check">'
              + '<input class="form-check-input" type="checkbox" id="chkConfirmDoublon">'
              + '<label class="form-check-label" for="chkConfirmDoublon">'
              + 'Je confirme que cet élève <strong>n\'est pas</strong> déjà enregistré et je souhaite continuer.'
              + '</label></div>';

            // Écouter la checkbox pour informer visuellement
            const chk = document.getElementById('chkConfirmDoublon');
            if (chk) {
              chk.addEventListener('change', function() {
                btnDoublon.classList.toggle('btn-outline-warning', !this.checked);
                btnDoublon.classList.toggle('btn-outline-success', this.checked);
              });
            }
          } else {
            // ── Aucun doublon ──
            doublonResult.innerHTML =
              '<div class="alert alert-success py-2 mb-0">'
              + '<i class="fa-solid fa-circle-check me-2"></i>'
              + '<strong>Aucun doublon détecté.</strong> Cet élève ne semble pas encore enregistré dans le système.'
              + '</div>';
            btnDoublon.classList.remove('btn-outline-warning');
            btnDoublon.classList.add('btn-outline-success');
          }
        },

        // ── Erreur réseau ──
        function(err) {
          btnDoublon.disabled = false;
          doublonSpinner.classList.add('d-none');
          doublonResult.style.display = '';
          doublonResult.innerHTML =
            '<div class="alert alert-danger py-2 mb-0">'
            + '<i class="fa-solid fa-circle-xmark me-2"></i>'
            + '<strong>Erreur de vérification</strong> — '
            + (err ? err.message : 'Réponse inattendue du serveur')
            + '<br><small class="text-muted">Consultez la console (F12) pour plus de détails.</small>'
            + '</div>';
        }
      );
    });
  }

  /* ── Validation avant soumission ────────────────────────────────────────── */
  document.getElementById('fie-insc-form').addEventListener('submit', function(e) {
    if (!selEtab.value) {
      e.preventDefault();
      alert('Veuillez sélectionner un établissement.');
      selEtab.focus();
      return;
    }
    // Vérif champ nationalite_autre obligatoire si "Autres"
    if (selNat && selNat.value === 'AUTRE' && inputNatAutre && !inputNatAutre.value.trim()) {
      e.preventDefault();
      inputNatAutre.classList.add('is-invalid');
      inputNatAutre.focus();
      return;
    }
  });

}());
</script>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
