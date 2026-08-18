<?php
/**
 * FIE — Vue : Transferts scolaires en cours
 */
$page_title  = $page_title  ?? 'Transferts scolaires — FIE';
$active_menu = $active_menu ?? 'suivi';
require BASE_PATH . '/app/views/layouts/header.php';

$transferts  = $transferts  ?? [];
$role        = SecurityHelper::userRole();
$csrf        = SecurityHelper::csrfToken();

$statutLabels = [
  'demande'  => ['bg-warning text-dark', 'fa-clock',         'Demandé'],
  'approuve' => ['bg-success',           'fa-circle-check',  'Approuvé'],
  'rejete'   => ['bg-danger',            'fa-circle-xmark',  'Rejeté'],
  'execute'  => ['bg-primary',           'fa-check-double',  'Exécuté'],
];
?>

<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/suivi">Suivi pédagogique</a></li>
    <li class="breadcrumb-item active">Transferts</li>
  </ol>
</nav>

<!-- En-tête -->
<div class="d-flex align-items-center justify-content-between mb-4 gap-2 flex-wrap">
  <h1 class="h4 fw-bold mb-0">
    <i class="fa-solid fa-arrows-left-right me-2" style="color:var(--fie-primary)"></i>
    Transferts scolaires
  </h1>
  <a href="<?= BASE_URL ?>/suivi/transfert/nouveau" class="btn btn-primary btn-sm">
    <i class="fa-solid fa-plus me-1"></i>Nouveau transfert
  </a>
</div>

<!-- Filtre statut -->
<div class="mb-3">
  <form method="get" class="d-flex gap-2 flex-wrap align-items-center">
    <label class="small fw-semibold text-muted me-1">Statut :</label>
    <?php foreach ($statutLabels as $val => [$cls, $icon, $label]): ?>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="statut[]"
             id="f_<?= $val ?>" value="<?= $val ?>"
             <?= in_array($val, $_GET['statut'] ?? array_keys($statutLabels)) ? 'checked' : '' ?>>
      <label class="form-check-label small" for="f_<?= $val ?>">
        <span class="badge <?= $cls ?>"><i class="fa-solid <?= $icon ?> me-1"></i><?= $label ?></span>
      </label>
    </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-filter me-1"></i>Filtrer
    </button>
  </form>
</div>

<!-- Tableau transferts -->
<?php if (empty($transferts)): ?>
<div class="alert alert-info d-flex align-items-center gap-2">
  <i class="fa-solid fa-circle-info fa-lg"></i>
  <div>Aucun transfert trouvé pour les critères sélectionnés.</div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 fie-suivi-table">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Élève</th>
          <th>École départ</th>
          <th>École arrivée</th>
          <th>Demandé le</th>
          <th class="text-center">Statut</th>
          <th>Motif</th>
          <?php if (in_array($role, ['super_admin','admin_central','directeur_ecole'], true)): ?>
          <th class="text-center">Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transferts as $i => $tr): ?>
        <?php
          $statut = $tr['statut'] ?? 'demande';
          [$cls, $icon, $label] = $statutLabels[$statut] ?? ['bg-secondary', 'fa-question', $statut];
        ?>
        <tr>
          <td class="text-muted small"><?= $i + 1 ?></td>
          <td>
            <div class="fw-semibold"><?= SecurityHelper::e(trim(($tr['nom'] ?? '') . ' ' . ($tr['prenom'] ?? ''))) ?></div>
            <code class="small text-muted"><?= SecurityHelper::e($tr['iue'] ?? '—') ?></code>
          </td>
          <td class="small">
            <i class="fa-solid fa-school text-danger me-1"></i>
            <?= SecurityHelper::e($tr['ecole_depart'] ?? '—') ?>
          </td>
          <td class="small">
            <i class="fa-solid fa-school text-success me-1"></i>
            <?= SecurityHelper::e($tr['ecole_arrivee'] ?? '—') ?>
          </td>
          <td class="small text-muted">
            <?= $tr['date_demande'] ? date('d/m/Y', strtotime($tr['date_demande'])) : '—' ?>
          </td>
          <td class="text-center">
            <span class="badge <?= $cls ?>">
              <i class="fa-solid <?= $icon ?> me-1"></i><?= $label ?>
            </span>
          </td>
          <td class="small text-muted" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            <?= SecurityHelper::e($tr['motif'] ?? '—') ?>
          </td>
          <?php if (in_array($role, ['super_admin','admin_central','directeur_ecole'], true)): ?>
          <td class="text-center">
            <?php if ($statut === 'demande'): ?>
            <div class="d-flex gap-1 justify-content-center">
              <form method="post" action="<?= BASE_URL ?>/suivi/transfert/<?= (int)$tr['id'] ?>/traiter"
                    onsubmit="return confirm('Approuver ce transfert ?')">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="approuver">
                <button type="submit" class="btn btn-success btn-sm" title="Approuver">
                  <i class="fa-solid fa-check"></i>
                </button>
              </form>
              <form method="post" action="<?= BASE_URL ?>/suivi/transfert/<?= (int)$tr['id'] ?>/traiter"
                    onsubmit="return confirm('Rejeter ce transfert ?')">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="rejeter">
                <button type="submit" class="btn btn-danger btn-sm" title="Rejeter">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </form>
            </div>
            <?php elseif ($statut === 'approuve'): ?>
            <form method="post" action="<?= BASE_URL ?>/suivi/transfert/<?= (int)$tr['id'] ?>/traiter"
                  onsubmit="return confirm('Marquer comme exécuté ?')">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="action" value="executer">
              <button type="submit" class="btn btn-primary btn-sm" title="Marquer exécuté">
                <i class="fa-solid fa-check-double"></i>
              </button>
            </form>
            <?php else: ?>
            <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
