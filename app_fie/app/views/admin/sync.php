<?php
/**
 * FIE — Vue : Synchronisation StatEduc
 * Bootstrap 5 + Font Awesome — Charte Burundi
 * CORRECTION Phase 2 : redesign BS5 + suppression use App\...
 */
$page_title  = $page_title  ?? 'Synchronisation — Administration FIE';
$active_menu = $active_menu ?? 'admin';
require BASE_PATH . '/app/views/layouts/header.php';
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Administration</a></li>
        <li class="breadcrumb-item active">Synchronisation</li>
    </ol>
</nav>

<!-- ── Titre ───────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="fa-solid fa-arrows-rotate me-2" style="color:var(--fie-red)"></i>
        Synchronisation des établissements
    </h1>
</div>

<!-- ── Résumé ────────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">
                    <i class="fa-solid fa-school me-1"></i>Total établissements (miroir)
                </div>
                <div class="h3 fw-bold"><?= number_format($etablissementsCount ?? 0) ?></div>
                <?php if (!empty($bySource)): ?>
                <div class="mt-2">
                    <?php foreach ($bySource as $src): ?>
                    <div class="d-flex justify-content-between small text-muted">
                        <span><?= SecurityHelper::e($src['source']) ?></span>
                        <span class="fw-semibold"><?= number_format($src['nb']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">
                    <i class="fa-solid fa-circle-check text-success me-1"></i>Dernière sync réussie
                </div>
                <?php if ($lastSuccess): ?>
                <div class="fw-bold"><?= date('d/m/Y à H:i', strtotime($lastSuccess['created_at'])) ?></div>
                <div class="small text-muted">
                    Insérés : <?= (int)($lastSuccess['nb_inserted'] ?? 0) ?> |
                    Mis à jour : <?= (int)($lastSuccess['nb_updated'] ?? 0) ?>
                </div>
                <?php else: ?>
                <div class="text-muted">Aucune synchronisation réussie</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-lg-4">
        <!-- Bouton lancer synchronisation -->
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="text-muted small mb-2">
                    <i class="fa-solid fa-play-circle me-1"></i>Lancer une synchronisation
                </div>
                <div class="d-flex gap-2">
                    <button id="btn-sync-full" class="btn btn-primary btn-sm"
                            onclick="lancerSync('full')">
                        <i class="fa-solid fa-arrows-rotate me-1"></i>Complète
                    </button>
                    <button id="btn-sync-incr" class="btn btn-outline-secondary btn-sm"
                            onclick="lancerSync('incremental')">
                        <i class="fa-solid fa-rotate-right me-1"></i>Incrémentale
                    </button>
                </div>
                <div id="sync-status" class="small text-muted mt-2 d-none">
                    <i class="fa-solid fa-spinner fa-spin me-1"></i>Synchronisation en cours…
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── Journal des synchronisations ──────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom fw-semibold">
        <i class="fa-solid fa-list me-2" style="color:var(--fie-red)"></i>
        Journal des synchronisations (20 dernières)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Source</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Insérés</th>
                        <th class="text-center">Mis à jour</th>
                        <th class="pe-3">Message</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fa-solid fa-circle-info me-1"></i>Aucun journal disponible.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="ps-3 text-nowrap small">
                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= ($log['source'] ?? '') === 'api' ? 'primary' : 'success' ?>">
                            <i class="fa-solid fa-<?= ($log['source'] ?? '') === 'api' ? 'cloud' : 'file-excel' ?> me-1"></i>
                            <?= SecurityHelper::e($log['source'] ?? '—') ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if (($log['statut'] ?? '') === 'succes'): ?>
                        <span class="badge bg-success">
                            <i class="fa-solid fa-check me-1"></i>Succès
                        </span>
                        <?php elseif (($log['statut'] ?? '') === 'echec'): ?>
                        <span class="badge bg-danger">
                            <i class="fa-solid fa-xmark me-1"></i>Échec
                        </span>
                        <?php else: ?>
                        <span class="badge bg-secondary"><?= SecurityHelper::e($log['statut'] ?? '—') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= (int)($log['nb_inserted'] ?? 0) ?></td>
                    <td class="text-center"><?= (int)($log['nb_updated'] ?? 0) ?></td>
                    <td class="pe-3 small text-muted"><?= SecurityHelper::e($log['message'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Lien import Excel -->
<div class="mt-3">
    <a href="<?= BASE_URL ?>/admin/import-excel" class="btn btn-outline-success btn-sm">
        <i class="fa-solid fa-file-excel me-1"></i>Importer depuis Excel (fallback hors-ligne)
    </a>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     JAVASCRIPT : lancer synchronisation via AJAX
     ═══════════════════════════════════════════════════════════════════════ -->
<script>
function lancerSync(mode) {
    const csrf = '<?= SecurityHelper::getCsrfToken() ?>';
    const btn  = document.getElementById('btn-sync-' + (mode === 'full' ? 'full' : 'incr'));
    const status = document.getElementById('sync-status');

    btn.disabled = true;
    status.classList.remove('d-none');

    const fd = new FormData();
    fd.append('csrf_token', csrf);
    fd.append('mode', mode);

    fetch('<?= BASE_URL ?>/admin/sync/lancer', {method: 'POST', body: fd})
      .then(r => r.json())
      .then(function(d) {
        btn.disabled = false;
        status.classList.add('d-none');
        if (d.ok) {
            alert('✅ ' + d.message);
            location.reload();
        } else {
            alert('❌ ' + (d.message || d.error || 'Erreur inconnue'));
        }
      })
      .catch(function(e) {
        btn.disabled = false;
        status.classList.add('d-none');
        alert('❌ Erreur réseau : ' + e.message);
      });
}
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
