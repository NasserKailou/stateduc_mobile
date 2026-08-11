<?php
/**
 * FIE — Vue : Tableau d'administration
 */
use App\Services\SecurityHelper;
$page_title  = $page_title  ?? 'Administration — FIE';
$active_menu = $active_menu ?? 'admin';
require __DIR__ . '/../layouts/header.php';
?>
<nav aria-label="Fil d'Ariane" class="fie-breadcrumb">
    <ol><li><a href="<?= BASE_URL ?>/">Accueil</a></li><li aria-current="page">Administration</li></ol>
</nav>

<div class="fie-page-header">
    <h1 class="fie-page-title">Administration FIE</h1>
</div>

<!-- Stats -->
<div class="fie-stats-grid">
    <div class="fie-stat-card">
        <div class="fie-stat-card__label">Établissements (miroir)</div>
        <div class="fie-stat-card__value"><?= number_format($stats['etablissements']) ?></div>
        <div class="fie-stat-card__sub">base locale synchronisée</div>
    </div>
    <div class="fie-stat-card fie-stat-card--green">
        <div class="fie-stat-card__label">Élèves enregistrés</div>
        <div class="fie-stat-card__value"><?= number_format($stats['eleves']) ?></div>
        <div class="fie-stat-card__sub">avec IUE unique</div>
    </div>
    <div class="fie-stat-card fie-stat-card--blue">
        <div class="fie-stat-card__label">Inscriptions actives</div>
        <div class="fie-stat-card__value"><?= number_format($stats['inscriptions']) ?></div>
        <div class="fie-stat-card__sub">année en cours</div>
    </div>
    <?php if ($stats['doublons'] > 0): ?>
    <div class="fie-stat-card fie-stat-card--warn">
        <div class="fie-stat-card__label">Doublons suspects</div>
        <div class="fie-stat-card__value"><?= number_format($stats['doublons']) ?></div>
        <div class="fie-stat-card__sub"><a href="<?= BASE_URL ?>/inscription/recherche?doublons_only=1">Voir</a></div>
    </div>
    <?php endif; ?>
</div>

<!-- Actions rapides -->
<div class="fie-card">
    <h2 class="fie-card__title">Actions rapides</h2>
    <div class="fie-btn-group">
        <a href="<?= BASE_URL ?>/admin/sync" class="fie-btn fie-btn--secondary">
            État de la synchronisation
        </a>
        <a href="<?= BASE_URL ?>/admin/import-excel" class="fie-btn fie-btn--secondary">
            Import Excel établissements
        </a>
        <a href="<?= BASE_URL ?>/admin/users" class="fie-btn fie-btn--secondary">
            Gestion des utilisateurs
        </a>
        <a href="<?= BASE_URL ?>/admin/audit" class="fie-btn fie-btn--ghost">
            Journal d'audit
        </a>
    </div>
</div>

<!-- Dernière synchro -->
<?php if ($lastSync): ?>
<div class="fie-card">
    <h2 class="fie-card__title">Dernière synchronisation</h2>
    <dl class="fie-dl">
        <dt>Date</dt>
        <dd><?= SecurityHelper::e(date('d/m/Y H:i', strtotime($lastSync['created_at']))) ?></dd>
        <dt>Statut</dt>
        <dd>
            <span class="fie-badge fie-badge--<?= $lastSync['statut'] === 'succes' ? 'success' : 'error' ?>">
                <?= SecurityHelper::e($lastSync['statut']) ?>
            </span>
        </dd>
        <dt>Insérés</dt><dd><?= (int)($lastSync['nb_inseres'] ?? 0) ?></dd>
        <dt>Mis à jour</dt><dd><?= (int)($lastSync['nb_mis_a_jour'] ?? 0) ?></dd>
        <?php if ($lastSync['message_erreur']): ?>
        <dt>Erreur</dt>
        <dd style="color:var(--fie-red)"><?= SecurityHelper::e($lastSync['message_erreur']) ?></dd>
        <?php endif; ?>
    </dl>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
