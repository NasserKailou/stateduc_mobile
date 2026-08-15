<?php
/**
 * FIE — Vue : Tableau de bord analytique
 * Bootstrap 5 + Font Awesome — Charte Burundi
 * CORRECTION Phase 2 : suppression use App\Services\SecurityHelper (pas de namespace)
 *                       redesign complet Bootstrap 5
 */
$page_title  = $page_title  ?? 'Tableau de bord — FIE';
$active_menu = $active_menu ?? 'dashboard';
require BASE_PATH . '/app/views/layouts/header.php';

// Labels secteurs d'enseignement
$secteurLabels = [
    1 => 'Préscolaire',
    2 => 'Primaire',
    3 => 'Secondaire général',
    4 => 'Secondaire technique',
    5 => 'Formation professionnelle',
    6 => 'Alphabétisation',
    7 => 'Supérieur',
];
?>

<!-- ── En-tête de page ─────────────────────────────────────────────────── -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="fa-solid fa-gauge-high me-2" style="color:var(--fie-red)"></i>Tableau de bord
        </h1>
        <p class="text-muted mb-0 small">
            Bienvenue,
            <strong><?= SecurityHelper::e($_SESSION['fie_user']['nom'] ?? '') ?></strong>
            — <span class="badge" style="background:var(--fie-red)"><?= SecurityHelper::e($_SESSION['fie_user']['role'] ?? '') ?></span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/inscription/nouveau" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus me-1"></i>Nouvelle inscription
        </a>
        <a href="<?= BASE_URL ?>/inscription/recherche" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Rechercher
        </a>
    </div>
</div>

<!-- ── KPI Cards ──────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 d-flex align-items-center justify-content-center"
                     style="background:#fff5f5;min-width:54px;height:54px">
                    <i class="fa-solid fa-users fa-lg" style="color:var(--fie-red)"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Élèves enregistrés</div>
                    <div class="h4 fw-bold mb-0"><?= number_format($kpis['total_eleves']) ?></div>
                    <div class="text-muted small">avec IUE unique</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 d-flex align-items-center justify-content-center"
                     style="background:#f0fff4;min-width:54px;height:54px">
                    <i class="fa-solid fa-file-lines fa-lg" style="color:var(--fie-green)"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Inscriptions actives</div>
                    <div class="h4 fw-bold mb-0"><?= number_format($kpis['inscriptions_an']) ?></div>
                    <div class="text-muted small">statut actif</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 d-flex align-items-center justify-content-center"
                     style="background:#f0f4ff;min-width:54px;height:54px">
                    <i class="fa-solid fa-school fa-lg" style="color:#0d6efd"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Établissements</div>
                    <div class="h4 fw-bold mb-0"><?= number_format($kpis['etablissements']) ?></div>
                    <div class="text-muted small">miroir StatEduc</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 <?= $kpis['agregats_pending'] > 0 ? 'border-warning' : '' ?>">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 d-flex align-items-center justify-content-center"
                     style="background:#fffbf0;min-width:54px;height:54px">
                    <i class="fa-solid fa-clock-rotate-left fa-lg"
                       style="color:<?= $kpis['agregats_pending'] > 0 ? '#fd7e14' : '#adb5bd' ?>"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Agrégats à synchroniser</div>
                    <div class="h4 fw-bold mb-0"
                         style="color:<?= $kpis['agregats_pending'] > 0 ? '#fd7e14' : 'inherit' ?>">
                        <?= number_format($kpis['agregats_pending']) ?>
                    </div>
                    <div class="text-muted small">vers StatEduc</div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── Ligne 2 : Répartition secteur + Parité sexe ────────────────────── -->
<div class="row g-4 mb-4">

    <!-- Répartition par secteur -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fa-solid fa-chart-bar me-2" style="color:var(--fie-red)"></i>
                Inscriptions par secteur d'enseignement
            </div>
            <div class="card-body">
                <?php if (empty($bySecteur)): ?>
                <p class="text-muted text-center py-3 mb-0">
                    <i class="fa-solid fa-circle-info me-1"></i>Aucune donnée disponible.
                </p>
                <?php else: ?>
                <?php
                $totalIns = array_sum(array_column($bySecteur, 'nb'));
                foreach ($bySecteur as $row):
                    $pct = $totalIns > 0 ? round($row['nb'] / $totalIns * 100, 1) : 0;
                    $label = $secteurLabels[(int)$row['code_type_secteur_ens']] ?? 'Code '.$row['code_type_secteur_ens'];
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold"><?= SecurityHelper::e($label) ?></span>
                        <span class="small text-muted">
                            <?= number_format($row['nb']) ?> — <strong><?= $pct ?>%</strong>
                        </span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px">
                        <div class="progress-bar" role="progressbar"
                             style="width:<?= $pct ?>%;background:var(--fie-red)"
                             aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Parité Filles/Garçons -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fa-solid fa-venus-mars me-2" style="color:var(--fie-green)"></i>
                Parité Filles / Garçons
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <?php if (empty($bySexe)): ?>
                <p class="text-muted text-center py-3 mb-0">
                    <i class="fa-solid fa-circle-info me-1"></i>Aucune donnée disponible.
                </p>
                <?php else: ?>
                <?php
                $totalSexe = array_sum(array_column($bySexe, 'nb'));
                foreach ($bySexe as $row):
                    $pct    = $totalSexe > 0 ? round($row['nb'] / $totalSexe * 100, 1) : 0;
                    $isF    = $row['sexe'] === 'F';
                    $color  = $isF ? '#e83e8c' : '#0d6efd';
                    $label  = $isF ? 'Filles' : 'Garçons';
                    $icon   = $isF ? 'fa-venus' : 'fa-mars';
                ?>
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:40px;height:40px;background:<?= $isF ? '#fff0f7' : '#f0f4ff' ?>">
                            <i class="fa-solid <?= $icon ?>" style="color:<?= $color ?>"></i>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= $label ?></div>
                            <div class="text-muted small">
                                <?= number_format($row['nb']) ?> élève<?= $row['nb'] > 1 ? 's' : '' ?>
                                — <strong><?= $pct ?>%</strong>
                            </div>
                        </div>
                    </div>
                    <div class="progress" style="height:12px;border-radius:6px">
                        <div class="progress-bar" role="progressbar"
                             style="width:<?= $pct ?>%;background:<?= $color ?>"
                             aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ── Actions rapides ────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom fw-semibold">
        <i class="fa-solid fa-bolt me-2" style="color:var(--fie-red)"></i>
        Actions rapides
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="<?= BASE_URL ?>/inscription/nouveau"
                   class="d-flex flex-column align-items-center gap-2 p-3 rounded-3 text-center
                          text-decoration-none border"
                   style="border-color:var(--fie-red)!important;background:#fff5f5">
                    <i class="fa-solid fa-user-plus fa-lg" style="color:var(--fie-red)"></i>
                    <span class="small fw-semibold" style="color:var(--fie-red)">Inscrire un élève</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= BASE_URL ?>/inscription/recherche"
                   class="d-flex flex-column align-items-center gap-2 p-3 rounded-3 text-center
                          text-decoration-none border bg-light">
                    <i class="fa-solid fa-magnifying-glass fa-lg text-secondary"></i>
                    <span class="small fw-semibold text-secondary">Rechercher un élève</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= BASE_URL ?>/mouvement"
                   class="d-flex flex-column align-items-center gap-2 p-3 rounded-3 text-center
                          text-decoration-none border bg-light">
                    <i class="fa-solid fa-arrow-right-arrow-left fa-lg" style="color:#6f42c1"></i>
                    <span class="small fw-semibold" style="color:#6f42c1">Mouvements</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <?php if (in_array($_SESSION['fie_user']['role'] ?? '', ['super_admin','admin_central'], true)): ?>
                <a href="<?= BASE_URL ?>/admin"
                   class="d-flex flex-column align-items-center gap-2 p-3 rounded-3 text-center
                          text-decoration-none border bg-light">
                    <i class="fa-solid fa-gears fa-lg" style="color:#0d6efd"></i>
                    <span class="small fw-semibold" style="color:#0d6efd">Administration</span>
                </a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/examen"
                   class="d-flex flex-column align-items-center gap-2 p-3 rounded-3 text-center
                          text-decoration-none border bg-light">
                    <i class="fa-solid fa-pen-to-square fa-lg" style="color:#20c997"></i>
                    <span class="small fw-semibold" style="color:#20c997">Examens</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
