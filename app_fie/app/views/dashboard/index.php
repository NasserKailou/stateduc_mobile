<?php
/**
 * FIE — Vue : Tableau de bord analytique
 * Bootstrap 5 + Font Awesome — Charte Bleu Ciel FIE
 * PHASE 3 : Refonte charte graphique + nouvelles KPI cards avec icônes FA
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

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
