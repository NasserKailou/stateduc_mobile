<?php
/**
 * FIE — Vue : Formulaire demande de transfert scolaire
 */
$page_title  = $page_title  ?? 'Nouveau transfert — FIE';
$active_menu = $active_menu ?? 'suivi';
require BASE_PATH . '/app/views/layouts/header.php';

$csrf        = SecurityHelper::csrfToken();
$errors      = $errors ?? [];
$old         = $old    ?? [];
?>

<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/suivi">Suivi pédagogique</a></li>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/suivi/transferts">Transferts</a></li>
    <li class="breadcrumb-item active">Nouveau transfert</li>
  </ol>
</nav>

<div class="row justify-content-center">
  <div class="col-lg-7">

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h1 class="h5 fw-bold mb-0">
          <i class="fa-solid fa-arrows-left-right me-2" style="color:var(--fie-primary)"></i>
          Demande de transfert scolaire
        </h1>
        <p class="text-muted small mb-0 mt-1">
          Remplissez ce formulaire pour initier un transfert en cours d'année scolaire.
        </p>
      </div>
      <div class="card-body">

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger d-flex gap-2 align-items-start">
          <i class="fa-solid fa-circle-exclamation mt-1 flex-shrink-0"></i>
          <ul class="mb-0 ps-2">
            <?php foreach ($errors as $e): ?>
            <li><?= SecurityHelper::e($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/suivi/transfert/demander" novalidate>
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

          <!-- Élève -->
          <div class="mb-3">
            <label for="iue" class="form-label fw-semibold">
              IUE de l'élève <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
              <input type="text" id="iue" name="iue"
                     class="form-control <?= isset($errors['iue']) ? 'is-invalid' : '' ?>"
                     value="<?= SecurityHelper::e($old['iue'] ?? '') ?>"
                     placeholder="Ex: BI2024000001"
                     required maxlength="20">
              <button type="button" class="btn btn-outline-secondary" id="btn-verify-iue"
                      title="Vérifier l'IUE">
                <i class="fa-solid fa-magnifying-glass"></i>
              </button>
            </div>
            <div class="form-text" id="iue-info"></div>
            <?php if (isset($errors['iue'])): ?>
            <div class="invalid-feedback d-block"><?= SecurityHelper::e($errors['iue']) ?></div>
            <?php endif; ?>
          </div>

          <!-- École départ -->
          <div class="mb-3">
            <label for="ecole_depart_code" class="form-label fw-semibold">
              École de départ <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-school text-danger"></i></span>
              <input type="text" id="ecole_depart_code" name="ecole_depart_code"
                     class="form-control <?= isset($errors['ecole_depart_code']) ? 'is-invalid' : '' ?>"
                     value="<?= SecurityHelper::e($old['ecole_depart_code'] ?? '') ?>"
                     placeholder="Code établissement départ"
                     required maxlength="20">
            </div>
            <?php if (isset($errors['ecole_depart_code'])): ?>
            <div class="invalid-feedback d-block"><?= SecurityHelper::e($errors['ecole_depart_code']) ?></div>
            <?php endif; ?>
          </div>

          <!-- École arrivée -->
          <div class="mb-3">
            <label for="ecole_arrivee_code" class="form-label fw-semibold">
              École d'arrivée <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-school text-success"></i></span>
              <input type="text" id="ecole_arrivee_code" name="ecole_arrivee_code"
                     class="form-control <?= isset($errors['ecole_arrivee_code']) ? 'is-invalid' : '' ?>"
                     value="<?= SecurityHelper::e($old['ecole_arrivee_code'] ?? '') ?>"
                     placeholder="Code établissement arrivée"
                     required maxlength="20">
            </div>
            <?php if (isset($errors['ecole_arrivee_code'])): ?>
            <div class="invalid-feedback d-block"><?= SecurityHelper::e($errors['ecole_arrivee_code']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Date prévue -->
          <div class="mb-3">
            <label for="date_transfert" class="form-label fw-semibold">
              Date de transfert prévue
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
              <input type="date" id="date_transfert" name="date_transfert"
                     class="form-control"
                     value="<?= SecurityHelper::e($old['date_transfert'] ?? date('Y-m-d')) ?>"
                     min="<?= date('Y-m-d') ?>">
            </div>
          </div>

          <!-- Motif -->
          <div class="mb-4">
            <label for="motif" class="form-label fw-semibold">
              Motif du transfert <span class="text-danger">*</span>
            </label>
            <select id="motif" name="motif"
                    class="form-select <?= isset($errors['motif']) ? 'is-invalid' : '' ?>"
                    required>
              <option value="">— Sélectionner un motif —</option>
              <option value="demenagement" <?= ($old['motif'] ?? '') === 'demenagement' ? 'selected' : '' ?>>
                Déménagement familial
              </option>
              <option value="affectation_parent" <?= ($old['motif'] ?? '') === 'affectation_parent' ? 'selected' : '' ?>>
                Affectation professionnelle d'un parent
              </option>
              <option value="raisons_familiales" <?= ($old['motif'] ?? '') === 'raisons_familiales' ? 'selected' : '' ?>>
                Raisons familiales
              </option>
              <option value="sante" <?= ($old['motif'] ?? '') === 'sante' ? 'selected' : '' ?>>
                Raisons de santé
              </option>
              <option value="rapprochement" <?= ($old['motif'] ?? '') === 'rapprochement' ? 'selected' : '' ?>>
                Rapprochement domicile–école
              </option>
              <option value="autre" <?= ($old['motif'] ?? '') === 'autre' ? 'selected' : '' ?>>
                Autre
              </option>
            </select>
            <?php if (isset($errors['motif'])): ?>
            <div class="invalid-feedback"><?= SecurityHelper::e($errors['motif']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Observation -->
          <div class="mb-4">
            <label for="observation" class="form-label fw-semibold">Observation (optionnel)</label>
            <textarea id="observation" name="observation" class="form-control" rows="3"
                      maxlength="500" placeholder="Précisions supplémentaires…"><?= SecurityHelper::e($old['observation'] ?? '') ?></textarea>
          </div>

          <!-- Boutons -->
          <div class="d-flex gap-2 justify-content-end">
            <a href="<?= BASE_URL ?>/suivi/transferts" class="btn btn-outline-secondary">
              <i class="fa-solid fa-arrow-left me-1"></i>Annuler
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-paper-plane me-1"></i>Soumettre la demande
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>

<script>
// Vérification IUE en AJAX
document.getElementById('btn-verify-iue')?.addEventListener('click', async function () {
  const iue  = document.getElementById('iue').value.trim();
  const info = document.getElementById('iue-info');
  if (!iue) { info.textContent = 'Saisir un IUE d\'abord.'; info.className = 'form-text text-danger'; return; }
  info.textContent = 'Vérification…'; info.className = 'form-text text-muted';
  try {
    const resp = await fetch('<?= BASE_URL ?>/inscription/' + encodeURIComponent(iue));
    if (resp.ok) {
      info.textContent = '✓ Élève trouvé.';
      info.className = 'form-text text-success';
    } else {
      info.textContent = '✗ IUE non trouvé dans la base FIE.';
      info.className = 'form-text text-danger';
    }
  } catch { info.textContent = 'Erreur de vérification.'; info.className = 'form-text text-warning'; }
});
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
