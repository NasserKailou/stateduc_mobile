<?php
/**
 * FIE — Vue : Formulaire création/édition utilisateur (AdminLTE)
 * Gère les rôles : super_admin, admin_central, directeur_ecole, enseignant, bibliothecaire
 */
$editMode        = $editMode       ?? false;
$page_title      = $page_title     ?? ($editMode ? 'Modifier utilisateur' : 'Nouvel utilisateur');
$active_menu     = $active_menu    ?? 'admin_user_form';
$admin_breadcrumb = [
    ['label' => 'Utilisateurs', 'url' => BASE_URL . '/admin/users'],
    ['label' => $editMode ? 'Modifier' : 'Nouvel utilisateur', 'url' => ''],
];
require BASE_PATH . '/app/views/layouts/app_layout.php';

$csrf    = SecurityHelper::csrfToken();
$errors  = $errors ?? [];
$old     = $old    ?? [];

$rolesLabels = [
    'super_admin'      => ['Super administrateur',   'fa-crown',            'danger'],
    'admin_central'    => ['Administrateur central', 'fa-user-shield',      'primary'],
    'directeur_ecole'  => ['Directeur d\'école',     'fa-building-columns', 'info'],
    'enseignant'       => ['Enseignant(e)',           'fa-chalkboard-teacher','success'],
    'bibliothecaire'   => ['Bibliothécaire',          'fa-book',             'purple'],
];
$actionUrl = $editMode
    ? BASE_URL . '/admin/users/' . (int)($old['id'] ?? 0) . '/editer'
    : BASE_URL . '/admin/users/nouveau';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h4 fw-bold mb-0">
    <i class="fa-solid fa-<?= $editMode ? 'pen-to-square' : 'user-plus' ?> me-2"
       style="color:var(--fie-primary)"></i>
    <?= $editMode ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' ?>
  </h1>
  <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary btn-sm">
    <i class="fa-solid fa-arrow-left me-1"></i>Liste
  </a>
</div>

<form method="post" action="<?= $actionUrl ?>" novalidate>
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">
<?php if ($editMode): ?>
<input type="hidden" name="_method" value="PUT">
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger d-flex gap-2 align-items-start mb-3">
  <i class="fa-solid fa-circle-exclamation mt-1 flex-shrink-0"></i>
  <ul class="mb-0 ps-2">
    <?php foreach ($errors as $e): ?><li><?= SecurityHelper::e($e) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- Colonne gauche : Informations compte -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-2 fw-semibold">
        <i class="fa-solid fa-circle-user me-2 text-primary"></i>Informations du compte
      </div>
      <div class="card-body">

        <!-- Login -->
        <div class="mb-3">
          <label for="login" class="form-label fw-semibold">
            Login <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
            <input type="text" id="login" name="login"
                   class="form-control <?= isset($errors['login']) ? 'is-invalid' : '' ?>"
                   value="<?= SecurityHelper::e($old['login'] ?? '') ?>"
                   <?= $editMode ? 'readonly' : '' ?>
                   required maxlength="50" autocomplete="username">
          </div>
          <?php if (isset($errors['login'])): ?>
          <div class="invalid-feedback d-block"><?= SecurityHelper::e($errors['login']) ?></div>
          <?php endif; ?>
          <?php if ($editMode): ?>
          <div class="form-text">Le login ne peut pas être modifié.</div>
          <?php endif; ?>
        </div>

        <!-- Mot de passe -->
        <div class="mb-3">
          <label for="mot_de_passe" class="form-label fw-semibold">
            Mot de passe <?= !$editMode ? '<span class="text-danger">*</span>' : '' ?>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" id="mot_de_passe" name="mot_de_passe"
                   class="form-control <?= isset($errors['mot_de_passe']) ? 'is-invalid' : '' ?>"
                   minlength="8" <?= !$editMode ? 'required' : '' ?>
                   autocomplete="new-password"
                   placeholder="<?= $editMode ? 'Laisser vide pour ne pas changer' : 'Minimum 8 caractères' ?>">
            <button class="btn btn-outline-secondary" type="button" id="toggle-pwd">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
          <?php if (isset($errors['mot_de_passe'])): ?>
          <div class="invalid-feedback d-block"><?= SecurityHelper::e($errors['mot_de_passe']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Actif -->
        <?php if ($editMode): ?>
        <div class="mb-3">
          <label class="form-label fw-semibold">Statut du compte</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="actif" id="actif_oui" value="1"
                     <?= (int)($old['actif'] ?? 1) === 1 ? 'checked' : '' ?>>
              <label class="form-check-label text-success fw-semibold" for="actif_oui">
                <i class="fa-solid fa-circle-check me-1"></i>Actif
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="actif" id="actif_non" value="0"
                     <?= (int)($old['actif'] ?? 1) === 0 ? 'checked' : '' ?>>
              <label class="form-check-label text-danger fw-semibold" for="actif_non">
                <i class="fa-solid fa-ban me-1"></i>Désactivé
              </label>
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <!-- Colonne droite : Identité + Rôle -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-2 fw-semibold">
        <i class="fa-solid fa-id-badge me-2 text-primary"></i>Identité et rôle
      </div>
      <div class="card-body">

        <!-- Nom -->
        <div class="mb-3">
          <label for="nom" class="form-label fw-semibold">
            Nom <span class="text-danger">*</span>
          </label>
          <input type="text" id="nom" name="nom"
                 class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                 value="<?= SecurityHelper::e($old['nom'] ?? '') ?>"
                 required maxlength="100">
          <?php if (isset($errors['nom'])): ?>
          <div class="invalid-feedback"><?= SecurityHelper::e($errors['nom']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Prénoms -->
        <div class="mb-3">
          <label for="prenoms" class="form-label fw-semibold">Prénoms</label>
          <input type="text" id="prenoms" name="prenoms"
                 class="form-control"
                 value="<?= SecurityHelper::e($old['prenoms'] ?? '') ?>"
                 maxlength="100">
        </div>

        <!-- Rôle -->
        <div class="mb-3">
          <label class="form-label fw-semibold">
            Rôle <span class="text-danger">*</span>
          </label>
          <div class="row g-2" id="role-cards">
            <?php foreach ($rolesLabels as $val => [$label, $icon, $color]): ?>
            <div class="col-12">
              <input type="radio" class="btn-check" name="role"
                     id="role_<?= $val ?>" value="<?= $val ?>"
                     <?= ($old['role'] ?? '') === $val ? 'checked' : '' ?> required>
              <label class="btn btn-outline-<?= $color ?> w-100 text-start py-2 px-3"
                     for="role_<?= $val ?>">
                <i class="fa-solid <?= $icon ?> me-2"></i><?= $label ?>
                <?php if ($val === 'directeur_ecole' || $val === 'enseignant'): ?>
                <span class="float-end badge bg-secondary fw-normal small">Périmètre école/classe</span>
                <?php endif; ?>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if (isset($errors['role'])): ?>
          <div class="text-danger small mt-1"><?= SecurityHelper::e($errors['role']) ?></div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>

  <!-- Périmètre d'intervention (affiché selon rôle) -->
  <div class="col-12" id="section-perimetre">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-2 fw-semibold">
        <i class="fa-solid fa-map-pin me-2 text-primary"></i>Périmètre d'intervention
        <span class="text-muted fw-normal small ms-2">(selon le rôle)</span>
      </div>
      <div class="card-body">
        <div class="row g-3">

          <!-- Province (admin_central) -->
          <div class="col-md-4" id="field-province">
            <label for="province_perimetre" class="form-label fw-semibold">Province</label>
            <input type="text" id="province_perimetre" name="province_perimetre"
                   class="form-control"
                   value="<?= SecurityHelper::e($old['province_perimetre'] ?? '') ?>"
                   placeholder="Laisser vide = toutes provinces"
                   maxlength="50">
            <div class="form-text">Pour admin_central : restreindre à une province.</div>
          </div>

          <!-- École (directeur / enseignant) -->
          <div class="col-md-4" id="field-ecole">
            <label for="ecole_code" class="form-label fw-semibold">École</label>
            <select id="ecole_code" name="ecole_code" class="form-select">
              <option value="">— Toutes les écoles —</option>
              <?php foreach ($ecoles ?? [] as $ec): ?>
              <option value="<?= SecurityHelper::e($ec['code_etablissement']) ?>"
                <?= ($old['ecole_code'] ?? '') === $ec['code_etablissement'] ? 'selected' : '' ?>>
                <?= SecurityHelper::e($ec['nom_etablissement'] . ' (' . $ec['code_etablissement'] . ')') ?>
              </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Obligatoire pour directeur_ecole et enseignant.</div>
          </div>

          <!-- Classe (enseignant) -->
          <div class="col-md-4" id="field-classe">
            <label for="classe_id" class="form-label fw-semibold">Classe</label>
            <select id="classe_id" name="classe_id" class="form-select">
              <option value="">— Toutes les classes —</option>
              <?php foreach ($classes ?? [] as $cl): ?>
              <option value="<?= (int)$cl['id'] ?>"
                <?= (int)($old['classe_id'] ?? 0) === (int)$cl['id'] ? 'selected' : '' ?>>
                <?= SecurityHelper::e($cl['nom_classe'] . ' — ' . $cl['code_etablissement'] . ' (' . $cl['annee_scolaire'] . ')') ?>
              </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Pour enseignant : classe assignée.</div>
          </div>

        </div>
      </div>
    </div>
  </div>

</div><!-- /.row -->

<!-- Actions -->
<div class="d-flex gap-2 justify-content-end mt-4">
  <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary">
    <i class="fa-solid fa-arrow-left me-1"></i>Annuler
  </a>
  <button type="submit" class="btn btn-primary px-4">
    <i class="fa-solid fa-<?= $editMode ? 'floppy-disk' : 'user-plus' ?> me-1"></i>
    <?= $editMode ? 'Enregistrer les modifications' : 'Créer l\'utilisateur' ?>
  </button>
</div>

</form>

<script>
// Afficher/masquer le mot de passe
document.getElementById('toggle-pwd')?.addEventListener('click', function () {
  const input = document.getElementById('mot_de_passe');
  const icon  = this.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'fa-solid fa-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'fa-solid fa-eye';
  }
});

// Adapter les champs de périmètre selon le rôle sélectionné
function updatePerimetre() {
  const role = document.querySelector('input[name="role"]:checked')?.value ?? '';
  const fProv  = document.getElementById('field-province');
  const fEcole = document.getElementById('field-ecole');
  const fClasse = document.getElementById('field-classe');
  // Réinitialiser
  [fProv, fEcole, fClasse].forEach(el => el && (el.style.opacity = '.4'));
  if (role === 'admin_central')    { fProv.style.opacity  = '1'; }
  if (['directeur_ecole','bibliothecaire'].includes(role)) { fEcole.style.opacity  = '1'; }
  if (role === 'enseignant')        { fEcole.style.opacity = '1'; fClasse.style.opacity = '1'; }
  if (['super_admin'].includes(role)) { [fProv,fEcole,fClasse].forEach(el => el && (el.style.opacity='1')); }
}

document.querySelectorAll('input[name="role"]').forEach(r =>
  r.addEventListener('change', updatePerimetre)
);
updatePerimetre();
</script>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
