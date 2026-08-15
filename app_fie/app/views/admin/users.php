<?php
/**
 * FIE — Vue : Gestion des utilisateurs
 * Bootstrap 5 + Font Awesome — Charte Burundi
 * CORRECTION Phase 2 : redesign BS5 + correction colonne `login` (pas username)
 */
$page_title  = $page_title  ?? 'Utilisateurs — Administration FIE';
$active_menu = $active_menu ?? 'admin';
require BASE_PATH . '/app/views/layouts/header.php';

// Libellés des rôles
$roleLabels = [
    'super_admin'       => ['Super Admin',         'danger'],
    'admin_central'     => ['Admin central',        'warning'],
    'admin_provincial'  => ['Admin provincial',     'info'],
    'gestionnaire_etab' => ['Gestionnaire étab.',   'primary'],
    'enseignant'        => ['Enseignant',            'secondary'],
    'consultant'        => ['Consultant',            'light'],
    'api_client'        => ['Client API',            'dark'],
];
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Administration</a></li>
        <li class="breadcrumb-item active">Utilisateurs</li>
    </ol>
</nav>

<!-- ── Titre ───────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="fa-solid fa-users-gear me-2" style="color:var(--fie-red)"></i>
        Gestion des utilisateurs
    </h1>
    <span class="badge bg-secondary fs-6 px-3">
        <?= count($users ?? []) ?> compte(s)
    </span>
</div>

<!-- ── Tableau ──────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Login</th>
                        <th>Nom complet</th>
                        <th>Rôle</th>
                        <th>Province</th>
                        <th class="text-center">Statut</th>
                        <th>Dernière connexion</th>
                        <th class="pe-3">Depuis le</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fa-solid fa-circle-info me-1"></i>Aucun utilisateur trouvé.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $u): ?>
                <?php
                    [$roleLbl, $roleColor] = $roleLabels[$u['role'] ?? ''] ?? [$u['role'] ?? '—', 'secondary'];
                ?>
                <tr>
                    <td class="ps-3 text-muted small"><?= (int)$u['id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light"
                                 style="width:32px;height:32px">
                                <i class="fa-solid fa-user text-muted small"></i>
                            </div>
                            <code class="small"><?= SecurityHelper::e($u['login']) ?></code>
                        </div>
                    </td>
                    <td class="fw-semibold">
                        <?= SecurityHelper::e(trim(($u['nom'] ?? '') . ' ' . ($u['prenoms'] ?? ''))) ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $roleColor ?> text-<?= $roleColor === 'light' ? 'dark' : 'white' ?>">
                            <?= htmlspecialchars($roleLbl, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td class="small text-muted">
                        <?= SecurityHelper::e($u['province_perimetre'] ?? '—') ?>
                    </td>
                    <td class="text-center">
                        <?php if ($u['actif']): ?>
                        <span class="badge bg-success">
                            <i class="fa-solid fa-circle-check me-1"></i>Actif
                        </span>
                        <?php else: ?>
                        <span class="badge bg-secondary">
                            <i class="fa-solid fa-circle-xmark me-1"></i>Inactif
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted text-nowrap">
                        <?= !empty($u['last_login_at'])
                            ? date('d/m/Y H:i', strtotime($u['last_login_at']))
                            : '—' ?>
                    </td>
                    <td class="pe-3 small text-muted text-nowrap">
                        <?= !empty($u['created_at'])
                            ? date('d/m/Y', strtotime($u['created_at']))
                            : '—' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 text-muted small">
    <i class="fa-solid fa-circle-info me-1 text-primary"></i>
    La gestion CRUD des utilisateurs (création, modification, suppression) est à implémenter en Phase 2 du projet.
    Les mots de passe sont stockés hashés avec bcrypt (cost 12).
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
