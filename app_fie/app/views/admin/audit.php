<?php
/**
 * FIE — Vue : Journal d'audit
 */
use App\Services\SecurityHelper;
require __DIR__ . '/../layouts/header.php';
?>
<nav aria-label="Fil d'Ariane" class="fie-breadcrumb">
    <ol>
        <li><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li><a href="<?= BASE_URL ?>/admin">Administration</a></li>
        <li aria-current="page">Journal d'audit</li>
    </ol>
</nav>
<div class="fie-page-header">
    <h1 class="fie-page-title">Journal d'audit</h1>
    <span class="fie-text--muted fie-text--sm"><?= $total ?> enregistrement(s)</span>
</div>

<div class="fie-alert fie-alert--info">
    <div class="fie-alert__body">
        Conformément à la loi n°1/03-2026, toutes les opérations sur les données personnelles
        sont tracées et conservées pendant 5 ans.
    </div>
</div>

<div class="fie-card" style="padding:0">
    <div class="fie-table-wrapper">
        <table class="fie-table">
            <thead>
                <tr>
                    <th class="fie-table__th">Date</th>
                    <th class="fie-table__th">Utilisateur</th>
                    <th class="fie-table__th">Action</th>
                    <th class="fie-table__th">Table</th>
                    <th class="fie-table__th">ID enregistrement</th>
                    <th class="fie-table__th">IP</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr class="fie-table__row">
                    <td class="fie-table__td fie-text--sm"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                    <td class="fie-table__td"><code class="fie-text--sm"><?= SecurityHelper::e($log['username'] ?? 'système') ?></code></td>
                    <td class="fie-table__td">
                        <span class="fie-badge fie-badge--<?= match($log['action']) {
                            'INSERT' => 'success', 'UPDATE' => 'info',
                            'DELETE' => 'danger', default => 'neutral'
                        } ?>">
                            <?= SecurityHelper::e($log['action']) ?>
                        </span>
                    </td>
                    <td class="fie-table__td fie-text--sm"><code><?= SecurityHelper::e($log['table_name']) ?></code></td>
                    <td class="fie-table__td fie-text--sm"><?= SecurityHelper::e((string)$log['record_id']) ?></td>
                    <td class="fie-table__td fie-text--sm fie-text--muted"><?= SecurityHelper::e($log['ip_address'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<nav class="fie-pagination">
    <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
        <?php if ($i === $page): ?>
            <span class="fie-pagination__btn fie-pagination__btn--active"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>" class="fie-pagination__btn"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <span class="fie-pagination__info">Page <?= $page ?> / <?= $pages ?></span>
</nav>
<?php endif; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
