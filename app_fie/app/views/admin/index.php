<?php
/**
 * FIE — Vue : Tableau d'administration
 * Bootstrap 5 + Font Awesome — Charte Burundi
 * CORRECTION Phase 2 : suppression use App\Services\SecurityHelper + redesign BS5
 */
$page_title  = $page_title  ?? 'Administration — FIE';
$active_menu = $active_menu ?? 'admin';
require BASE_PATH . '/app/views/layouts/app_layout.php';
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item active">Administration</li>
    </ol>
</nav>

<!-- ── Titre ───────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="fa-solid fa-gears me-2" style="color:var(--fie-red)"></i>Administration FIE
    </h1>
</div>

<!-- ── KPI Cards ──────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#f0f4ff;min-width:52px;height:52px;display:flex;align-items:center;justify-content:center">
                    <i class="fa-solid fa-school fa-lg" style="color:#0d6efd"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Établissements (miroir)</div>
                    <div class="h4 fw-bold mb-0"><?= number_format($stats['etablissements']) ?></div>
                    <div class="text-muted small">base locale synchronisée</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#f0fff4;min-width:52px;height:52px;display:flex;align-items:center;justify-content:center">
                    <i class="fa-solid fa-users fa-lg" style="color:var(--fie-green)"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Élèves enregistrés</div>
                    <div class="h4 fw-bold mb-0"><?= number_format($stats['eleves']) ?></div>
                    <div class="text-muted small">avec IUE unique</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#fff5f5;min-width:52px;height:52px;display:flex;align-items:center;justify-content:center">
                    <i class="fa-solid fa-file-lines fa-lg" style="color:var(--fie-red)"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Inscriptions actives</div>
                    <div class="h4 fw-bold mb-0"><?= number_format($stats['inscriptions']) ?></div>
                    <div class="text-muted small">année en cours</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 <?= $stats['doublons'] > 0 ? 'border-warning' : '' ?>">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:#fffbf0;min-width:52px;height:52px;display:flex;align-items:center;justify-content:center">
                    <i class="fa-solid fa-copy fa-lg" style="color:<?= $stats['doublons'] > 0 ? '#fd7e14' : '#adb5bd' ?>"></i>
                </div>
                <div>
                    <div class="text-muted small mb-1">Doublons suspects</div>
                    <div class="h4 fw-bold mb-0" style="color:<?= $stats['doublons'] > 0 ? '#fd7e14' : 'inherit' ?>">
                        <?= number_format($stats['doublons']) ?>
                    </div>
                    <?php if ($stats['doublons'] > 0): ?>
                    <a href="<?= BASE_URL ?>/inscription/recherche?doublons_only=1" class="small text-warning">Voir les doublons</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── Info synchronisation ──────────────────────────────────────────────── -->
<?php if ($lastSync): ?>
<div class="alert alert-<?= ($lastSync['status'] ?? '') === 'success' ? 'success' : (($lastSync['status'] ?? '') === 'error' ? 'danger' : 'warning') ?> d-flex align-items-center gap-2 mb-4">
    <i class="fa-solid fa-<?= ($lastSync['status'] ?? '') === 'success' ? 'circle-check' : 'triangle-exclamation' ?>"></i>
    <span>
        Dernière synchronisation :
        <strong><?= SecurityHelper::e(date('d/m/Y à H:i', strtotime($lastSync['started_at']))) ?></strong>
        — Statut : <strong><?= SecurityHelper::e($lastSync['status'] ?? 'inconnu') ?></strong>
        <?php if (!empty($lastSync['details'])): ?>
        — <?= SecurityHelper::e($lastSync['details']) ?>
        <?php endif; ?>
    </span>
</div>
<?php endif; ?>

<?php if ($pendingAggregats > 0): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <i class="fa-solid fa-clock-rotate-left"></i>
    <span>
        <strong><?= number_format($pendingAggregats) ?></strong> agrégat(s) en attente de synchronisation vers StatEduc.
    </span>
</div>
<?php endif; ?>

<!-- ── Navigation admin ───────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-md-6 col-lg-3">
        <a href="<?= BASE_URL ?>/admin/sync"
           class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body text-center py-4">
                <i class="fa-solid fa-arrows-rotate fa-2x mb-3" style="color:var(--fie-green)"></i>
                <div class="fw-semibold">Synchronisation</div>
                <div class="text-muted small">API StatEduc &amp; Import Excel</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-3">
        <a href="<?= BASE_URL ?>/admin/users"
           class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body text-center py-4">
                <i class="fa-solid fa-users-gear fa-2x mb-3" style="color:#0d6efd"></i>
                <div class="fw-semibold">Utilisateurs</div>
                <div class="text-muted small">Gestion des comptes &amp; rôles</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-3">
        <a href="<?= BASE_URL ?>/admin/audit"
           class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body text-center py-4">
                <i class="fa-solid fa-list-check fa-2x mb-3" style="color:#6f42c1"></i>
                <div class="fw-semibold">Journal d'audit</div>
                <div class="text-muted small">Traçabilité complète des actions</div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-3">
        <a href="<?= BASE_URL ?>/admin/import-excel"
           class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body text-center py-4">
                <i class="fa-solid fa-file-excel fa-2x mb-3" style="color:#20c997"></i>
                <div class="fw-semibold">Import Excel</div>
                <div class="text-muted small">Fichier infos_etab_bu.xlsx</div>
            </div>
        </a>
    </div>

</div>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
