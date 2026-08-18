<?php
/**
 * FIE — Vue : Historique complet d'un élève
 * Frise chronologique par année scolaire
 */
$page_title  = $page_title  ?? 'Historique élève — FIE';
$active_menu = $active_menu ?? 'recherche';
require BASE_PATH . '/app/views/layouts/header.php';

$eleve      = $eleve      ?? [];
$byAnnee    = $byAnnee    ?? [];
$historique = $historique ?? [];
$typeLabels = $typeLabels  ?? [];

$nomComplet = trim(($eleve['nom'] ?? '') . ' ' . ($eleve['prenom'] ?? ''));
?>

<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inscription/recherche">Recherche</a></li>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inscription/<?= SecurityHelper::e($eleve['iue'] ?? '') ?>">
      <?= SecurityHelper::e($nomComplet) ?></a>
    </li>
    <li class="breadcrumb-item active">Historique</li>
  </ol>
</nav>

<!-- Carte élève -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row align-items-center g-3">
      <!-- Avatar initiales -->
      <div class="col-auto">
        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white fs-4"
             style="width:64px;height:64px;background:var(--fie-primary);">
          <?= strtoupper(substr($eleve['prenom'] ?? '?', 0, 1) . substr($eleve['nom'] ?? '?', 0, 1)) ?>
        </div>
      </div>
      <!-- Infos -->
      <div class="col">
        <h1 class="h5 fw-bold mb-1"><?= SecurityHelper::e($nomComplet) ?></h1>
        <div class="d-flex flex-wrap gap-3 small text-muted">
          <span>
            <i class="fa-solid fa-id-card me-1" style="color:var(--fie-primary)"></i>
            <code class="text-dark"><?= SecurityHelper::e($eleve['iue'] ?? '—') ?></code>
          </span>
          <span>
            <?php if (($eleve['sexe'] ?? '') === 'F'): ?>
            <i class="fa-solid fa-venus text-pink me-1"></i>Féminin
            <?php else: ?>
            <i class="fa-solid fa-mars text-info me-1"></i>Masculin
            <?php endif; ?>
          </span>
          <?php if (!empty($eleve['date_naissance'])): ?>
          <span>
            <i class="fa-solid fa-cake-candles me-1"></i>
            <?= date('d/m/Y', strtotime($eleve['date_naissance'])) ?>
          </span>
          <?php endif; ?>
          <?php if (!empty($eleve['nom_etablissement'])): ?>
          <span>
            <i class="fa-solid fa-school me-1"></i>
            <?= SecurityHelper::e($eleve['nom_etablissement']) ?>
          </span>
          <?php endif; ?>
          <?php if (!empty($eleve['classe_nom'])): ?>
          <span>
            <i class="fa-solid fa-door-open me-1"></i>
            <?= SecurityHelper::e($eleve['classe_nom']) ?>
          </span>
          <?php endif; ?>
        </div>
      </div>
      <!-- Actions -->
      <div class="col-auto">
        <a href="<?= BASE_URL ?>/inscription/<?= SecurityHelper::e($eleve['iue'] ?? '') ?>"
           class="btn btn-sm btn-outline-primary">
          <i class="fa-solid fa-file-lines me-1"></i>Dossier
        </a>
        <?php if (count($historique) > 0): ?>
        <span class="badge bg-primary ms-2"><?= count($historique) ?> événement<?= count($historique) > 1 ? 's' : '' ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Frise chronologique -->
<?php if (empty($historique)): ?>
<div class="alert alert-info d-flex align-items-center gap-2">
  <i class="fa-solid fa-circle-info fa-lg"></i>
  <div>Aucun événement enregistré pour cet élève.</div>
</div>
<?php else: ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h2 class="h6 fw-bold text-muted mb-0">
    <i class="fa-solid fa-clock-rotate-left me-2"></i>Frise chronologique
  </h2>
  <!-- Filtre type -->
  <div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
            data-bs-toggle="dropdown">
      <i class="fa-solid fa-filter me-1"></i>Filtrer par type
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm" id="filter-menu">
      <li><a class="dropdown-item active" href="#" data-filter="all">Tous les événements</a></li>
      <li><hr class="dropdown-divider"></li>
      <?php foreach ($typeLabels as $k => [$lbl, $icon, $color]): ?>
      <li>
        <a class="dropdown-item" href="#" data-filter="<?= $k ?>">
          <i class="fa-solid <?= $icon ?> me-2 text-<?= $color ?>"></i><?= $lbl ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<!-- Par année scolaire -->
<?php foreach ($byAnnee as $annee => $evenements): ?>
<div class="mb-4 fie-timeline-year" data-annee="<?= SecurityHelper::e($annee) ?>">
  <div class="d-flex align-items-center gap-2 mb-2">
    <span class="badge bg-primary rounded-pill px-3 py-2">
      <i class="fa-solid fa-calendar-days me-1"></i><?= SecurityHelper::e($annee) ?>
    </span>
    <div class="flex-fill" style="height:2px;background:linear-gradient(to right,var(--fie-primary-light),transparent)"></div>
    <span class="small text-muted"><?= count($evenements) ?> évt.</span>
  </div>

  <div class="fie-timeline ps-3">
    <?php foreach ($evenements as $evt): ?>
    <?php
      $type = $evt['type_action'] ?? 'modification';
      [$lbl, $icon, $color] = $typeLabels[$type] ?? [$type, 'fa-circle-dot', 'secondary'];
      $dateEvt = $evt['date_evenement'] ?? null;
    ?>
    <div class="fie-timeline__item mb-3" data-type="<?= $type ?>">
      <!-- Point + ligne -->
      <div class="fie-timeline__dot" style="background:var(--bs-<?= $color ?>);">
        <i class="fa-solid <?= $icon ?> text-white" style="font-size:.7rem;"></i>
      </div>

      <!-- Contenu -->
      <div class="fie-timeline__content card border-0 shadow-sm ms-3">
        <div class="card-body py-2 px-3">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> fw-semibold mb-1">
                <i class="fa-solid <?= $icon ?> me-1"></i><?= $lbl ?>
              </span>
              <?php if (!empty($evt['description'])): ?>
              <p class="mb-1 small"><?= SecurityHelper::e($evt['description']) ?></p>
              <?php endif; ?>

              <!-- Détails selon type -->
              <div class="d-flex flex-wrap gap-2 small text-muted mt-1">
                <?php if (!empty($evt['annee_scolaire'])): ?>
                <span><i class="fa-solid fa-calendar me-1"></i><?= SecurityHelper::e($evt['annee_scolaire']) ?></span>
                <?php endif; ?>
                <?php if (!empty($evt['classe_nom'])): ?>
                <span><i class="fa-solid fa-door-open me-1"></i><?= SecurityHelper::e($evt['classe_nom']) ?></span>
                <?php endif; ?>
                <?php if (!empty($evt['nom_ecole'])): ?>
                <span><i class="fa-solid fa-school me-1"></i><?= SecurityHelper::e($evt['nom_ecole']) ?></span>
                <?php endif; ?>
                <?php if (!empty($evt['utilisateur_action'])): ?>
                <span><i class="fa-solid fa-user me-1"></i>Par : <?= SecurityHelper::e($evt['utilisateur_action']) ?></span>
                <?php endif; ?>
                <?php if (!empty($evt['resultat'])): ?>
                <span class="fw-semibold text-dark"><i class="fa-solid fa-star me-1"></i>Résultat : <?= SecurityHelper::e($evt['resultat']) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <!-- Date -->
            <div class="text-end text-muted small text-nowrap">
              <?php if ($dateEvt): ?>
              <div><?= date('d/m/Y', strtotime($dateEvt)) ?></div>
              <div class="opacity-75"><?= date('H:i', strtotime($dateEvt)) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<style>
/* Frise chronologique */
.fie-timeline { position: relative; }
.fie-timeline::before {
  content: '';
  position: absolute;
  left: .75rem;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e9ecef;
}
.fie-timeline__item { position: relative; display: flex; align-items: flex-start; }
.fie-timeline__dot {
  position: relative;
  z-index: 1;
  flex-shrink: 0;
  width: 1.6rem;
  height: 1.6rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: .15rem;
  box-shadow: 0 0 0 3px #fff;
}
.fie-timeline__content { flex: 1; min-width: 0; }

/* Couleurs badge BS5 avec suffixe -subtle */
.bg-purple-subtle { background: #f3e8ff !important; }
.text-purple       { color: #6f42c1 !important; }
.text-pink         { color: #d63384 !important; }
</style>

<script>
// Filtre par type d'événement
document.querySelectorAll('#filter-menu .dropdown-item').forEach(item => {
  item.addEventListener('click', function(e) {
    e.preventDefault();
    const filter = this.dataset.filter;
    document.querySelectorAll('#filter-menu .dropdown-item').forEach(i => i.classList.remove('active'));
    this.classList.add('active');
    document.querySelectorAll('.fie-timeline__item').forEach(el => {
      el.style.display = (filter === 'all' || el.dataset.type === filter) ? '' : 'none';
    });
    // Masquer les années vides
    document.querySelectorAll('.fie-timeline-year').forEach(year => {
      const visible = year.querySelectorAll('.fie-timeline__item:not([style*="none"])').length;
      year.style.display = visible ? '' : 'none';
    });
  });
});
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
