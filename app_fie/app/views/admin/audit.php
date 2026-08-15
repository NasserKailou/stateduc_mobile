<?php
/**
 * FIE — Vue : Journal d'audit
 * Bootstrap 5 + Font Awesome — Charte Burundi
 */
$page_title  = $page_title  ?? "Journal d'audit — FIE";
$active_menu = $active_menu ?? 'admin';
require BASE_PATH . '/app/views/layouts/header.php';
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Administration</a></li>
        <li class="breadcrumb-item active">Journal d'audit</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="fa-solid fa-list-check me-2" style="color:var(--fie-red)"></i>
        Journal d'audit
    </h1>
    <span class="badge bg-secondary fs-6 px-3">
        <?= number_format($total ?? 0) ?> entrée(s)
    </span>
</div>

<!-- ── Tableau ──────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date / Heure</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Entité</th>
                        <th>Entité ID</th>
                        <th class="pe-3">IP</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fa-solid fa-circle-info me-1"></i>Aucune entrée d'audit.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="ps-3 text-nowrap small">
                        <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                    </td>
                    <td>
                        <?php if (!empty($log['username'])): ?>
                        <code class="small"><?= SecurityHelper::e($log['username']) ?></code>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $actionColor = match(strtolower($log['action'] ?? '')) {
                            'create', 'insert' => 'success',
                            'update', 'edit'   => 'primary',
                            'delete', 'remove' => 'danger',
                            'login'            => 'info',
                            'logout'           => 'secondary',
                            default            => 'light',
                        };
                        ?>
                        <span class="badge bg-<?= $actionColor ?>">
                            <?= SecurityHelper::e($log['action'] ?? '—') ?>
                        </span>
                    </td>
                    <td class="small"><?= SecurityHelper::e($log['entite'] ?? '—') ?></td>
                    <td class="small text-muted"><?= SecurityHelper::e($log['entite_id'] ?? '—') ?></td>
                    <td class="pe-3 small text-muted text-nowrap">
                        <?= SecurityHelper::e($log['ip_address'] ?? '—') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if (($pages ?? 1) > 1): ?>
<nav aria-label="Pagination audit" class="mt-3">
    <?php
    $link = fn(int $p): string => BASE_URL . '/admin/audit?page=' . $p;
    ?>
    <ul class="pagination justify-content-center mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $link(1) ?>">«</a>
        </li>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $link($page - 1) ?>">‹</a>
        </li>
        <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= $link($i) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $link($page + 1) ?>">›</a>
        </li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $link($pages) ?>">»</a>
        </li>
    </ul>
    <div class="text-center text-muted small mt-2">
        Page <?= $page ?> / <?= $pages ?> — <?= number_format($total) ?> entrées
    </div>
</nav>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
