<?php
/**
 * FIE — Vue : Statut synchronisation API StatEduc
 */
use App\Services\SecurityHelper;
require __DIR__ . '/../layouts/header.php';
?>
<nav aria-label="Fil d'Ariane" class="fie-breadcrumb">
    <ol>
        <li><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li><a href="<?= BASE_URL ?>/admin">Administration</a></li>
        <li aria-current="page">Synchronisation</li>
    </ol>
</nav>

<div class="fie-page-header">
    <h1 class="fie-page-title">Synchronisation des établissements</h1>
</div>

<!-- Stats locales -->
<div class="fie-stats-grid">
    <div class="fie-stat-card">
        <div class="fie-stat-card__label">Total dans la base locale</div>
        <div class="fie-stat-card__value"><?= number_format($etablissementsCount) ?></div>
    </div>
    <?php foreach ($bySource as $src): ?>
    <div class="fie-stat-card <?= $src['source'] === 'api_stateduc' ? 'fie-stat-card--green' : '' ?>">
        <div class="fie-stat-card__label"><?= SecurityHelper::e($src['source']) ?></div>
        <div class="fie-stat-card__value"><?= number_format($src['nb']) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Déclencheur manuel -->
<div class="fie-card">
    <h2 class="fie-card__title">Lancer une synchronisation</h2>
    <form id="syncForm" class="fie-form">
        <?= SecurityHelper::csrfField() ?>
        <div class="fie-form-grid">
            <div class="fie-form-group">
                <label for="sync_mode" class="fie-label">Mode</label>
                <select id="sync_mode" name="mode" class="fie-select">
                    <option value="full">Complète (toutes les pages)</option>
                    <option value="incremental">Incrémentale (modifiés depuis dernière synchro)</option>
                </select>
            </div>
            <div class="fie-form-group">
                <label for="sync_per_page" class="fie-label">Taille des pages</label>
                <select id="sync_per_page" name="per_page" class="fie-select">
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="200">200</option>
                </select>
            </div>
        </div>
        <div class="fie-btn-group">
            <button type="submit" class="fie-btn fie-btn--primary" id="syncBtn">
                Lancer la synchronisation
            </button>
        </div>
    </form>
    <div id="syncResult" class="fie-alert" style="display:none;margin-top:var(--fie-space-4)"></div>
</div>

<!-- Journal des synchronisations -->
<div class="fie-card">
    <h2 class="fie-card__title">Journal des synchronisations (20 dernières)</h2>
    <?php if (empty($logs)): ?>
        <p class="fie-text--muted">Aucune synchronisation enregistrée.</p>
    <?php else: ?>
    <div class="fie-table-wrapper">
        <table class="fie-table">
            <thead>
                <tr>
                    <th class="fie-table__th">Date</th>
                    <th class="fie-table__th">Statut</th>
                    <th class="fie-table__th">Insérés</th>
                    <th class="fie-table__th">Mis à jour</th>
                    <th class="fie-table__th">Erreurs</th>
                    <th class="fie-table__th">Durée</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr class="fie-table__row">
                    <td class="fie-table__td"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                    <td class="fie-table__td">
                        <span class="fie-badge fie-badge--<?= $log['statut'] === 'succes' ? 'success' : 'error' ?>">
                            <?= SecurityHelper::e($log['statut']) ?>
                        </span>
                    </td>
                    <td class="fie-table__td"><?= (int)($log['nb_inseres'] ?? 0) ?></td>
                    <td class="fie-table__td"><?= (int)($log['nb_mis_a_jour'] ?? 0) ?></td>
                    <td class="fie-table__td">
                        <?php if ($log['message_erreur']): ?>
                            <span class="fie-text--sm" style="color:var(--fie-red)" title="<?= SecurityHelper::e($log['message_erreur']) ?>">
                                <?= SecurityHelper::e(substr($log['message_erreur'], 0, 60)) ?>…
                            </span>
                        <?php else: ?>
                            <span class="fie-text--muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="fie-table__td fie-text--sm fie-text--muted">
                        <?= isset($log['duree_secondes']) ? $log['duree_secondes'] . 's' : '—' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('syncForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn    = document.getElementById('syncBtn');
    const result = document.getElementById('syncResult');
    btn.disabled = true;
    btn.textContent = 'Synchronisation en cours…';
    result.style.display = 'none';

    const fd = new FormData(this);
    const body = {};
    fd.forEach(function(v,k){ body[k] = v; });

    try {
        const data = await postJSON('<?= BASE_URL ?>/admin/sync/lancer', body);
        result.className = 'fie-alert fie-alert--' + (data.ok ? 'success' : 'error');
        result.textContent = data.message;
        result.style.display = 'flex';
        if (data.ok) setTimeout(function(){ location.reload(); }, 2000);
    } catch(err) {
        result.className = 'fie-alert fie-alert--error';
        result.textContent = 'Erreur de communication : ' + err.message;
        result.style.display = 'flex';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Lancer la synchronisation';
    }
});
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
