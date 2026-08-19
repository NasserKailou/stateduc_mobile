<?php
/**
 * FIE — Vue : Tableau de bord analytique
 * Bootstrap 5 + Font Awesome — Charte Bleu Ciel FIE
 * PHASE 3 : Refonte charte graphique + nouvelles KPI cards avec icônes FA
 */
$page_title      = $page_title  ?? 'Tableau de bord — FIE';
$active_menu     = $active_menu ?? 'dashboard';
$app_breadcrumb  = [['label' => 'Tableau de bord', 'url' => '']];
require BASE_PATH . '/app/views/layouts/app_layout.php';

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

// Statistiques complémentaires (calculées ou issues du contrôleur)
$totalEleves        = $kpis['total_eleves']     ?? 0;
$totalInscrits      = $kpis['inscriptions_an']  ?? 0;
$totalEtabs         = $kpis['etablissements']   ?? 0;
$totalDoublons      = $kpis['doublons']         ?? 0;
$totalFilles        = 0; $totalGarcons = 0;
if (!empty($bySexe)) {
    foreach ($bySexe as $row) {
        if ($row['sexe'] === 'F') $totalFilles   = (int)$row['nb'];
        else                      $totalGarcons  = (int)$row['nb'];
    }
}
$pctFilles = ($totalEleves > 0) ? round($totalFilles / $totalEleves * 100, 1) : 0;
$pctCouverture = ($totalEtabs > 0) ? min(100, round($totalInscrits / max($totalEtabs, 1) * 100, 1)) : 0;
?>

<!-- ── En-tête de page ─────────────────────────────────────────────────── -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="fa-solid fa-gauge-high me-2" style="color:var(--fie-primary)"></i>Tableau de bord
        </h1>
        <p class="text-muted mb-0 small">
            Bienvenue,
            <strong><?= SecurityHelper::e($_SESSION['fie_user']['nom'] ?? '') ?></strong>
            — <span class="badge" style="background:var(--fie-primary)"><?= SecurityHelper::e($_SESSION['fie_user']['role'] ?? '') ?></span>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/inscription/nouveau" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus me-1"></i>Nouvelle inscription
        </a>
        <a href="<?= BASE_URL ?>/inscription/recherche" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Rechercher
        </a>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     KPI CARDS — Ligne 1 : Indicateurs principaux FIE
     ══════════════════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-3">

    <!-- Carte Élève Numérique FIE -->
    <div class="col-sm-6 col-xl-3">
        <div class="card fie-kpi-card fie-kpi--blue h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fie-kpi-icon fie-kpi--blue">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div class="flex-fill">
                    <div class="fie-kpi-label">Carte Élève Numérique FIE</div>
                    <div class="fie-kpi-value fie-kpi--blue"><?= number_format($totalEleves) ?></div>
                    <div class="fie-kpi-sub">IUE uniques émis</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Élèves immatriculés -->
    <div class="col-sm-6 col-xl-3">
        <div class="card fie-kpi-card fie-kpi--sky h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fie-kpi-icon fie-kpi--sky">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="flex-fill">
                    <div class="fie-kpi-label">Élèves immatriculés</div>
                    <div class="fie-kpi-value fie-kpi--sky"><?= number_format($totalInscrits) ?></div>
                    <div class="fie-kpi-sub">Inscrits actifs (année en cours)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Établissements couverts -->
    <div class="col-sm-6 col-xl-3">
        <div class="card fie-kpi-card fie-kpi--green h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fie-kpi-icon fie-kpi--green">
                    <i class="fa-solid fa-school"></i>
                </div>
                <div class="flex-fill">
                    <div class="fie-kpi-label">Établissements couverts</div>
                    <div class="fie-kpi-value fie-kpi--green"><?= number_format($totalEtabs) ?></div>
                    <div class="fie-kpi-sub">Miroir StatEduc synchronisé</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unicité garantie -->
    <div class="col-sm-6 col-xl-3">
        <div class="card fie-kpi-card fie-kpi--teal h-100 <?= $totalDoublons > 0 ? 'border-warning' : '' ?>">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fie-kpi-icon fie-kpi--teal">
                    <i class="fa-solid fa-shield-check"></i>
                </div>
                <div class="flex-fill">
                    <div class="fie-kpi-label">Unicité garantie</div>
                    <div class="fie-kpi-value fie-kpi--teal">
                        <?= $totalDoublons > 0 ? '<span style="color:#fd7e14">'.number_format($totalDoublons).'</span>' : '<span>0</span>' ?>
                    </div>
                    <div class="fie-kpi-sub">
                        <?= $totalDoublons > 0
                            ? '<a href="'.BASE_URL.'/inscription/recherche?doublon=1" class="text-warning fw-semibold">Doublons suspects à vérifier</a>'
                            : 'Aucun doublon détecté ✓' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════════════════════
     KPI CARDS — Ligne 2 : Couverture & Parité
     ══════════════════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Couverture nationale -->
    <div class="col-sm-6 col-xl-3">
        <div class="card fie-kpi-card fie-kpi--indigo h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fie-kpi-icon fie-kpi--indigo">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div class="flex-fill">
                    <div class="fie-kpi-label">Couverture nationale</div>
                    <div class="fie-kpi-value fie-kpi--indigo"><?= $pctCouverture ?>%</div>
                    <div class="fie-kpi-sub">Taux d'immatriculation estimé</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parité filles -->
    <div class="col-sm-6 col-xl-3">
        <div class="card fie-kpi-card fie-kpi--pink h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fie-kpi-icon fie-kpi--pink">
                    <i class="fa-solid fa-venus"></i>
                </div>
                <div class="flex-fill">
                    <div class="fie-kpi-label">Parité — Filles</div>
                    <div class="fie-kpi-value fie-kpi--pink"><?= $pctFilles ?>%</div>
                    <div class="fie-kpi-sub"><?= number_format($totalFilles) ?> filles enregistrées</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agrégats à synchroniser -->
    <div class="col-sm-6 col-xl-3">
        <div class="card fie-kpi-card fie-kpi--orange h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fie-kpi-icon fie-kpi--orange">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div class="flex-fill">
                    <div class="fie-kpi-label">Agrégats en attente</div>
                    <div class="fie-kpi-value fie-kpi--orange"><?= number_format($kpis['agregats_pending'] ?? 0) ?></div>
                    <div class="fie-kpi-sub">À synchroniser vers StatEduc</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unicité à l'échelle nationale -->
    <div class="col-sm-6 col-xl-3">
        <div class="card fie-kpi-card fie-kpi--purple h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fie-kpi-icon fie-kpi--purple">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <div class="flex-fill">
                    <div class="fie-kpi-label">Unicité nationale</div>
                    <div class="fie-kpi-value fie-kpi--purple"><?= number_format($totalEleves) ?></div>
                    <div class="fie-kpi-sub">IUE uniques à l'échelle nationale</div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── Ligne 3 : Répartition secteur + Parité sexe ────────────────────── -->
<div class="row g-4 mb-4">

    <!-- Répartition par secteur -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fa-solid fa-chart-bar me-2" style="color:var(--fie-primary)"></i>
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
                $sectorColors = ['#1d4ed8','#17a2b8','#178a2b','#fd7e14','#6d28d9','#e83e8c','#0f766e'];
                $ci = 0;
                foreach ($bySecteur as $row):
                    $pct = $totalIns > 0 ? round($row['nb'] / $totalIns * 100, 1) : 0;
                    $label = $secteurLabels[(int)$row['code_type_secteur_ens']] ?? 'Code '.$row['code_type_secteur_ens'];
                    $color = $sectorColors[$ci % count($sectorColors)];
                    $ci++;
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
                             style="width:<?= $pct ?>%;background:<?= $color ?>"
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
                    $color  = $isF ? '#e83e8c' : '#1d4ed8';
                    $label  = $isF ? 'Filles' : 'Garçons';
                    $icon   = $isF ? 'fa-venus' : 'fa-mars';
                    $bg     = $isF ? '#fce7f3' : '#dbeafe';
                ?>
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:40px;height:40px;background:<?= $bg ?>">
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
        <i class="fa-solid fa-bolt me-2" style="color:var(--fie-primary)"></i>
        Actions rapides
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="<?= BASE_URL ?>/inscription/nouveau"
                   class="d-flex flex-column align-items-center gap-2 p-3 rounded-3 text-center
                          text-decoration-none border"
                   style="border-color:var(--fie-primary)!important;background:#f0f4ff">
                    <i class="fa-solid fa-user-plus fa-lg" style="color:var(--fie-primary)"></i>
                    <span class="small fw-semibold" style="color:var(--fie-primary)">Inscrire un élève</span>
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
                    <i class="fa-solid fa-arrow-right-arrow-left fa-lg" style="color:#17a2b8"></i>
                    <span class="small fw-semibold" style="color:#17a2b8">Mouvements</span>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= BASE_URL ?>/bibliotheque"
                   class="d-flex flex-column align-items-center gap-2 p-3 rounded-3 text-center
                          text-decoration-none border bg-light">
                    <i class="fa-solid fa-book-open fa-lg" style="color:#6d28d9"></i>
                    <span class="small fw-semibold" style="color:#6d28d9">Bibliothèque</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     SECTION ANALYTIQUE AVANCÉE
     ══════════════════════════════════════════════════════════════════════ -->

<!-- ── Évolution mensuelle des inscriptions ──────────────────────────── -->
<?php if (!empty($byMois)): ?>
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fa-solid fa-chart-line me-2" style="color:var(--fie-primary)"></i>
                Évolution des inscriptions — 12 derniers mois
            </div>
            <div class="card-body">
                <canvas id="chartMois" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Répartition province + Top établissements ─────────────────────── -->
<div class="row g-4 mb-4">

    <!-- Donut / bar par province -->
    <?php if (!empty($byProvince)): ?>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fa-solid fa-map-location-dot me-2" style="color:var(--fie-primary)"></i>
                Élèves par province (Top <?= count($byProvince) ?>)
            </div>
            <div class="card-body" style="max-height:360px;overflow-y:auto;">
                <?php
                $totalProv = array_sum(array_column($byProvince, 'nb'));
                $provColors = ['#CE1126','#1EB53A','#1d4ed8','#17a2b8','#fd7e14','#6d28d9',
                               '#e83e8c','#0f766e','#92400e','#b45309','#374151','#0891b2',
                               '#7c3aed','#dc2626','#16a34a','#2563eb','#d97706','#0e7490'];
                $ci = 0;
                foreach ($byProvince as $row):
                    $pct = $totalProv > 0 ? round($row['nb'] / $totalProv * 100, 1) : 0;
                    $color = $provColors[$ci % count($provColors)]; $ci++;
                ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold"><?= SecurityHelper::e($row['province']) ?></span>
                        <span class="small text-muted"><?= number_format($row['nb']) ?> — <strong><?= $pct ?>%</strong></span>
                    </div>
                    <div class="progress" style="height:7px;border-radius:4px">
                        <div class="progress-bar" role="progressbar"
                             style="width:<?= $pct ?>%;background:<?= $color ?>"
                             aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Top 10 établissements -->
    <?php if (!empty($topEtabs)): ?>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fa-solid fa-school me-2" style="color:var(--fie-green)"></i>
                Top 10 établissements
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:.82rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:30px">#</th>
                                <th>Établissement</th>
                                <th>Province</th>
                                <th class="text-end pe-3">Élèves</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topEtabs as $rank => $etab): ?>
                        <tr>
                            <td class="ps-3 text-muted fw-semibold"><?= $rank + 1 ?></td>
                            <td><?= SecurityHelper::e($etab['nom_etablissement'] ?? '—') ?></td>
                            <td class="text-muted small"><?= SecurityHelper::e($etab['province'] ?? '—') ?></td>
                            <td class="text-end pe-3 fw-semibold" style="color:var(--fie-primary)"><?= number_format($etab['nb']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ── Nationalités + Niveaux d'enseignement ─────────────────────────── -->
<div class="row g-4 mb-4">

    <!-- Nationalités -->
    <?php if (!empty($byNationalite)): ?>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fa-solid fa-globe me-2" style="color:#17a2b8"></i>
                Répartition par nationalité
            </div>
            <div class="card-body">
                <canvas id="chartNationalite" height="220"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Niveaux d'enseignement -->
    <?php if (!empty($byNiveau)): ?>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fa-solid fa-graduation-cap me-2" style="color:#6d28d9"></i>
                Inscriptions par niveau d'enseignement
            </div>
            <div class="card-body">
                <?php
                $totalNiv = array_sum(array_column($byNiveau, 'nb'));
                $nivColors = ['#CE1126','#1EB53A','#1d4ed8','#17a2b8','#fd7e14','#6d28d9',
                              '#e83e8c','#0f766e','#92400e','#b45309','#374151','#0891b2'];
                $ci = 0;
                foreach ($byNiveau as $row):
                    $pct = $totalNiv > 0 ? round($row['nb'] / $totalNiv * 100, 1) : 0;
                    $color = $nivColors[$ci % count($nivColors)]; $ci++;
                ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Niveau <?= SecurityHelper::e($row['code_type_niveau']) ?></span>
                        <span class="small text-muted"><?= number_format($row['nb']) ?> — <strong><?= $pct ?>%</strong></span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px">
                        <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ── Dernières inscriptions ────────────────────────────────────────── -->
<?php if (!empty($lastInscrits)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom fw-semibold d-flex align-items-center justify-content-between">
        <span>
            <i class="fa-solid fa-clock-rotate-left me-2" style="color:var(--fie-primary)"></i>
            Dernières inscriptions enregistrées
        </span>
        <a href="<?= BASE_URL ?>/inscription/recherche" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-list me-1"></i>Voir toutes
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.85rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">IUE</th>
                        <th>Nom</th>
                        <th>Prénom(s)</th>
                        <th class="text-center">Sexe</th>
                        <th>Établissement</th>
                        <th>Province</th>
                        <th>Date inscription</th>
                        <th class="pe-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($lastInscrits as $ins): ?>
                <tr>
                    <td class="ps-3">
                        <code class="fie-iue-badge small"><?= SecurityHelper::e($ins['iue']) ?></code>
                    </td>
                    <td class="fw-semibold"><?= SecurityHelper::e($ins['nom']) ?></td>
                    <td><?= SecurityHelper::e($ins['prenoms'] ?? '') ?></td>
                    <td class="text-center">
                        <?php if (($ins['sexe'] ?? '') === 'F'): ?>
                        <span class="badge" style="background:#e83e8c">F</span>
                        <?php else: ?>
                        <span class="badge bg-primary">M</span>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= SecurityHelper::e($ins['nom_etablissement'] ?? '—') ?></td>
                    <td class="small text-muted"><?= SecurityHelper::e($ins['province'] ?? '—') ?></td>
                    <td class="text-nowrap small text-muted">
                        <?= !empty($ins['created_at']) ? date('d/m/Y', strtotime($ins['created_at'])) : '—' ?>
                    </td>
                    <td class="pe-3 text-center">
                        <a href="<?= BASE_URL ?>/inscription/<?= urlencode($ins['iue']) ?>"
                           class="btn btn-sm btn-outline-primary" title="Voir fiche">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Chart.js CDN + data -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function() {
    Chart.defaults.font.family = "'Open Sans', system-ui, sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#6c757d';

    // ── Graphique évolution mensuelle ────────────────────────────────
    <?php if (!empty($byMois)): ?>
    (function() {
        var labels  = <?= json_encode(array_column($byMois, 'mois')) ?>;
        var data    = <?= json_encode(array_map('intval', array_column($byMois, 'nb'))) ?>;
        // Format labels: "2024-08" → "Août 24"
        var moisFr  = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
        var fmtLabels = labels.map(function(m) {
            var p = m.split('-'); return moisFr[parseInt(p[1])] + ' ' + p[0].slice(2);
        });
        var ctx = document.getElementById('chartMois');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: fmtLabels,
                datasets: [{
                    label: 'Inscriptions',
                    data: data,
                    borderColor: '#CE1126',
                    backgroundColor: 'rgba(206,17,38,0.08)',
                    pointBackgroundColor: '#CE1126',
                    pointRadius: 4,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(c) { return ' ' + c.parsed.y + ' inscription' + (c.parsed.y > 1 ? 's' : ''); } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }());
    <?php endif; ?>

    // ── Graphique nationalités (doughnut) ────────────────────────────
    <?php if (!empty($byNationalite)): ?>
    (function() {
        var labels = <?= json_encode(array_column($byNationalite, 'nationalite')) ?>;
        var data   = <?= json_encode(array_map('intval', array_column($byNationalite, 'nb'))) ?>;
        var colors = ['#CE1126','#1EB53A','#1d4ed8','#17a2b8','#fd7e14','#6d28d9','#e83e8c','#374151'];
        var ctx = document.getElementById('chartNationalite');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } },
                    tooltip: { callbacks: {
                        label: function(c) {
                            var total = c.dataset.data.reduce(function(a,b){return a+b;},0);
                            var pct = total > 0 ? Math.round(c.parsed / total * 1000) / 10 : 0;
                            return ' ' + c.label + ': ' + c.parsed + ' (' + pct + '%)';
                        }
                    }}
                }
            }
        });
    }());
    <?php endif; ?>
}());
</script>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>

