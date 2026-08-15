<?php
/**
 * FIE — Vue : Tableau de bord
 */
use App\Services\SecurityHelper;
require __DIR__ . '/../layouts/header.php';

// Labels secteurs
$secteurLabels = [
    1 => 'Préscolaire', 2 => 'Primaire', 3 => 'Secondaire général',
    4 => 'Secondaire technique', 5 => 'Formation professionnelle',
    6 => 'Alphabétisation', 7 => 'Supérieur',
];
?>
<div class="fie-page-header">
    <h1 class="fie-page-title">Tableau de bord</h1>
    <span class="fie-text--muted fie-text--sm">
        Bienvenue, <?= SecurityHelper::e($_SESSION['fie_user']['prenom'] ?? '') ?>
        <?= SecurityHelper::e($_SESSION['fie_user']['nom'] ?? '') ?>
    </span>
</div>

<!-- KPIs -->
<div class="fie-stats-grid">
    <div class="fie-stat-card">
        <div class="fie-stat-card__label">Élèves enregistrés</div>
        <div class="fie-stat-card__value"><?= number_format($kpis['total_eleves']) ?></div>
        <div class="fie-stat-card__sub">avec IUE unique</div>
    </div>
    <div class="fie-stat-card fie-stat-card--green">
        <div class="fie-stat-card__label">Inscriptions actives</div>
        <div class="fie-stat-card__value"><?= number_format($kpis['inscriptions_an']) ?></div>
    </div>
    <div class="fie-stat-card fie-stat-card--blue">
        <div class="fie-stat-card__label">Établissements (miroir)</div>
        <div class="fie-stat-card__value"><?= number_format($kpis['etablissements']) ?></div>
    </div>
    <?php if ($kpis['agregats_pending'] > 0): ?>
    <div class="fie-stat-card fie-stat-card--warn">
        <div class="fie-stat-card__label">Agrégats à synchroniser</div>
        <div class="fie-stat-card__value"><?= number_format($kpis['agregats_pending']) ?></div>
        <div class="fie-stat-card__sub">en attente d'envoi vers StatEduc</div>
    </div>
    <?php endif; ?>
</div>

<!-- Répartition par secteur -->
<?php if (!empty($bySecteur)): ?>
<div class="fie-card">
    <h2 class="fie-card__title">Inscriptions par secteur d'enseignement</h2>
    <div class="fie-table-wrapper">
        <table class="fie-table">
            <thead>
                <tr>
                    <th class="fie-table__th">Secteur</th>
                    <th class="fie-table__th">Inscriptions actives</th>
                    <th class="fie-table__th">%</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $totalInscriptions = array_sum(array_column($bySecteur, 'nb'));
            foreach ($bySecteur as $row):
                $pct = $totalInscriptions > 0 ? round($row['nb'] / $totalInscriptions * 100, 1) : 0;
            ?>
                <tr class="fie-table__row">
                    <td class="fie-table__td">
                        <?= SecurityHelper::e($secteurLabels[(int)$row['code_type_secteur_ens']] ?? 'Code ' . $row['code_type_secteur_ens']) ?>
                    </td>
                    <td class="fie-table__td"><?= number_format($row['nb']) ?></td>
                    <td class="fie-table__td">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:8px;background:var(--fie-gray-200);border-radius:4px;max-width:120px">
                                <div style="width:<?= $pct ?>%;height:100%;background:var(--fie-red);border-radius:4px"></div>
                            </div>
                            <span class="fie-text--sm"><?= $pct ?>%</span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Répartition par sexe -->
<?php if (!empty($bySexe)): ?>
<div class="fie-card">
    <h2 class="fie-card__title">Parité Filles / Garçons</h2>
    <?php
    $totalSexe = array_sum(array_column($bySexe, 'nb'));
    foreach ($bySexe as $row):
        $pct = $totalSexe > 0 ? round($row['nb'] / $totalSexe * 100, 1) : 0;
    ?>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
            <span class="fie-badge fie-badge--<?= $row['sexe'] === 'F' ? 'pink' : 'blue' ?>"
                  style="width:32px;justify-content:center"><?= $row['sexe'] ?></span>
            <div style="flex:1;height:20px;background:var(--fie-gray-200);border-radius:4px;overflow:hidden">
                <div style="width:<?= $pct ?>%;height:100%;
                            background:<?= $row['sexe'] === 'F' ? '#ffd6e0' : '#d0e8ff' ?>;
                            border-radius:4px;display:flex;align-items:center;
                            padding-left:8px;font-size:12px;font-weight:600">
                    <?= $pct ?>%
                </div>
            </div>
            <span class="fie-text--sm"><?= number_format($row['nb']) ?> élèves</span>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Liens rapides -->
<div class="fie-card">
    <h2 class="fie-card__title">Actions</h2>
    <div class="fie-btn-group">
        <a href="<?= BASE_URL ?>/inscription/nouveau" class="fie-btn fie-btn--primary">
            Nouvelle inscription
        </a>
        <a href="<?= BASE_URL ?>/inscription/recherche" class="fie-btn fie-btn--secondary">
            Rechercher un élève
        </a>
        <?php if (SecurityHelper::requireRole('admin', false)): ?>
        <a href="<?= BASE_URL ?>/admin" class="fie-btn fie-btn--ghost">
            Administration
        </a>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
