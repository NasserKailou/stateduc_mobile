<?php
/**
 * FIE — Vue : Suivi pédagogique — Liste des classes
 * Accessible : super_admin, admin_central, directeur_ecole, enseignant
 */
$page_title  = $page_title  ?? 'Suivi pédagogique — FIE';
$active_menu = $active_menu ?? 'suivi';
require BASE_PATH . '/app/views/layouts/app_layout.php';

$role        = SecurityHelper::userRole();
$classes     = $classes     ?? [];
$anneeCour   = $anneeCour   ?? date('Y') . '-' . (date('Y') + 1);
?>

<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
    <li class="breadcrumb-item active">Suivi pédagogique</li>
  </ol>
</nav>

<!-- En-tête -->
<div class="d-flex align-items-center justify-content-between mb-4 gap-2 flex-wrap">
  <h1 class="h4 fw-bold mb-0">
    <i class="fa-solid fa-chalkboard-teacher me-2" style="color:var(--fie-primary)"></i>
    Suivi pédagogique
    <span class="badge bg-primary ms-2 fw-normal fs-6"><?= SecurityHelper::e($anneeCour) ?></span>
  </h1>
  <?php if (in_array($role, ['super_admin', 'admin_central', 'directeur_ecole'], true)): ?>
  <a href="<?= BASE_URL ?>/suivi/transferts" class="btn btn-outline-primary btn-sm">
    <i class="fa-solid fa-arrows-left-right me-1"></i> Transferts en cours
  </a>
  <?php endif; ?>
</div>

<!-- Stats rapides -->
<?php if (!empty($stats)): ?>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="h3 fw-bold text-primary mb-0"><?= number_format($stats['total_classes'] ?? 0) ?></div>
      <div class="small text-muted mt-1"><i class="fa-solid fa-door-open me-1"></i>Classes</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="h3 fw-bold text-success mb-0"><?= number_format($stats['total_promus'] ?? 0) ?></div>
      <div class="small text-muted mt-1"><i class="fa-solid fa-arrow-up me-1"></i>Promus</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="h3 fw-bold text-warning mb-0"><?= number_format($stats['total_redoublants'] ?? 0) ?></div>
      <div class="small text-muted mt-1"><i class="fa-solid fa-rotate-left me-1"></i>Redoublants</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="h3 fw-bold text-secondary mb-0"><?= number_format($stats['en_attente'] ?? 0) ?></div>
      <div class="small text-muted mt-1"><i class="fa-solid fa-clock me-1"></i>En attente</div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Filtre école (admin / directeur) -->
<?php if (in_array($role, ['super_admin', 'admin_central'], true) && !empty($ecoles)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-2">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label small mb-1">École</label>
        <select name="ecole_code" class="form-select form-select-sm">
          <option value="">— Toutes les écoles —</option>
          <?php foreach ($ecoles as $ec): ?>
          <option value="<?= SecurityHelper::e($ec['code_etablissement']) ?>"
            <?= (($_GET['ecole_code'] ?? '') === $ec['code_etablissement']) ? 'selected' : '' ?>>
            <?= SecurityHelper::e($ec['nom_etablissement'] . ' (' . $ec['code_etablissement'] . ')') ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">Année scolaire</label>
        <select name="annee_scolaire" class="form-select form-select-sm">
          <?php foreach ($annees ?? [$anneeCour] as $an): ?>
          <option value="<?= SecurityHelper::e($an) ?>"
            <?= (($an) === ($annee_scolaire ?? $anneeCour)) ? 'selected' : '' ?>>
            <?= SecurityHelper::e($an) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-filter me-1"></i> Filtrer
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Tableau des classes -->
<?php if (empty($classes)): ?>
<div class="alert alert-info d-flex align-items-center gap-2">
  <i class="fa-solid fa-circle-info fa-lg"></i>
  <div>Aucune classe trouvée
    <?= in_array($role, ['enseignant'], true) ? 'pour votre compte.' : 'pour les critères sélectionnés.' ?>
  </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex align-items-center justify-content-between py-2">
    <span class="fw-semibold small text-muted">
      <i class="fa-solid fa-list me-1"></i><?= count($classes) ?> classe<?= count($classes) > 1 ? 's' : '' ?>
    </span>
    <span class="small text-muted">Année : <strong><?= SecurityHelper::e($annee_scolaire ?? $anneeCour) ?></strong></span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-sm mb-0 align-middle fie-suivi-table">
      <thead class="table-light">
        <tr>
          <th>Classe</th>
          <th>École</th>
          <th>Enseignant</th>
          <th class="text-end">Élèves</th>
          <th class="text-center">Promus</th>
          <th class="text-center">Redoublants</th>
          <th class="text-center">Abandons</th>
          <th class="text-center">En attente</th>
          <th class="text-center">Progression</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($classes as $cl): ?>
        <?php
          $total    = max(1, (int)($cl['nb_eleves'] ?? 1));
          $decided  = (int)($cl['nb_promus'] ?? 0) + (int)($cl['nb_redoublants'] ?? 0) + (int)($cl['nb_abandons'] ?? 0);
          $attente  = max(0, $total - $decided);
          $pct      = (int)round($decided / $total * 100);
          $barClass = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-primary' : 'bg-warning');
        ?>
        <tr>
          <td class="fw-semibold"><?= SecurityHelper::e($cl['nom_classe'] ?? '—') ?></td>
          <td>
            <span class="small"><?= SecurityHelper::e($cl['nom_etablissement'] ?? '—') ?></span>
          </td>
          <td class="small text-muted"><?= SecurityHelper::e($cl['enseignant_nom'] ?? '—') ?></td>
          <td class="text-end fw-bold"><?= (int)($cl['nb_eleves'] ?? 0) ?></td>
          <td class="text-center">
            <span class="badge bg-success-subtle text-success fw-semibold"><?= (int)($cl['nb_promus'] ?? 0) ?></span>
          </td>
          <td class="text-center">
            <span class="badge bg-warning-subtle text-warning fw-semibold"><?= (int)($cl['nb_redoublants'] ?? 0) ?></span>
          </td>
          <td class="text-center">
            <span class="badge bg-danger-subtle text-danger fw-semibold"><?= (int)($cl['nb_abandons'] ?? 0) ?></span>
          </td>
          <td class="text-center">
            <span class="badge bg-secondary-subtle text-secondary fw-semibold"><?= $attente ?></span>
          </td>
          <td style="min-width:90px">
            <div class="progress" style="height:8px;" title="<?= $pct ?>% décidés">
              <div class="progress-bar <?= $barClass ?>" style="width:<?= $pct ?>%"></div>
            </div>
            <div class="small text-muted text-center mt-1"><?= $pct ?>%</div>
          </td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/suivi/classe/<?= (int)$cl['id'] ?>"
               class="btn btn-sm btn-primary">
              <i class="fa-solid fa-pen-to-square me-1"></i>Saisir
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
