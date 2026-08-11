<?php
/**
 * FIE — Vue : Gestion des utilisateurs
 */
use App\Services\SecurityHelper;
require __DIR__ . '/../layouts/header.php';
?>
<nav aria-label="Fil d'Ariane" class="fie-breadcrumb">
    <ol>
        <li><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li><a href="<?= BASE_URL ?>/admin">Administration</a></li>
        <li aria-current="page">Utilisateurs</li>
    </ol>
</nav>
<div class="fie-page-header">
    <h1 class="fie-page-title">Gestion des utilisateurs</h1>
</div>
<div class="fie-card">
    <div class="fie-table-wrapper">
        <table class="fie-table fie-table--hover">
            <thead>
                <tr>
                    <th class="fie-table__th">Identifiant</th>
                    <th class="fie-table__th">Nom complet</th>
                    <th class="fie-table__th">Rôle</th>
                    <th class="fie-table__th">Province</th>
                    <th class="fie-table__th">Statut</th>
                    <th class="fie-table__th">Dernière connexion</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="fie-table__row">
                    <td class="fie-table__td"><code><?= SecurityHelper::e($u['username']) ?></code></td>
                    <td class="fie-table__td"><?= SecurityHelper::e($u['nom'] . ' ' . $u['prenom']) ?></td>
                    <td class="fie-table__td">
                        <span class="fie-badge fie-badge--<?= $u['role'] === 'admin' ? 'danger' : 'info' ?>">
                            <?= SecurityHelper::e($u['role']) ?>
                        </span>
                    </td>
                    <td class="fie-table__td fie-text--sm">
                        <?= $u['province_code'] ? SecurityHelper::e($u['province_code']) : '<span class="fie-text--muted">Toutes</span>' ?>
                    </td>
                    <td class="fie-table__td fie-table__td--center">
                        <span class="fie-badge fie-badge--<?= $u['actif'] ? 'success' : 'neutral' ?>">
                            <?= $u['actif'] ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td class="fie-table__td fie-text--sm fie-text--muted">
                        <?= $u['derniere_connexion'] ? date('d/m/Y H:i', strtotime($u['derniere_connexion'])) : '—' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
