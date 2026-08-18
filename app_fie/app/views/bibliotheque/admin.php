<?php
/**
 * FIE — Vue : Gestion bibliothèque (admin/bibliothécaire)
 */
$page_title  = $page_title  ?? 'Gestion bibliothèque — FIE';
$active_menu = $active_menu ?? 'bibliotheque_admin';
require BASE_PATH . '/app/views/layouts/app_layout.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="fa-solid fa-book-open me-2" style="color:var(--fie-primary)"></i>Gestion de la bibliothèque
    </h1>
    <a href="<?= BASE_URL ?>/bibliotheque/admin/nouveau" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i>Publier un document
    </a>
</div>

<!-- Tableau documents -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom fw-semibold">
        <i class="fa-solid fa-list me-2" style="color:var(--fie-primary)"></i>
        Documents (<?= count($documents) ?>)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Titre</th>
                        <th>Thématique</th>
                        <th>Type</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Public</th>
                        <th class="text-center">↓</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($documents)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fa-solid fa-folder-open me-2"></i>Aucun document. Commencez par publier un document.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                <tr>
                    <td class="ps-3">
                        <div class="fw-semibold small"><?= SecurityHelper::e($doc['titre']) ?></div>
                        <div class="text-muted" style="font-size:.7rem">
                            <?= SecurityHelper::e($doc['publie_par_login'] ?? 'N/A') ?>
                            <?php if ($doc['publie_le']): ?>
                            · <?= date('d/m/Y', strtotime($doc['publie_le'])) ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge fw-normal"
                              style="background:<?= SecurityHelper::e($doc['couleur']) ?>22;color:<?= SecurityHelper::e($doc['couleur']) ?>">
                            <i class="fa-solid <?= SecurityHelper::e($doc['icone_fa']) ?> me-1"></i>
                            <?= SecurityHelper::e($doc['thematique_libelle']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-light text-muted fw-normal">.<?= strtoupper(SecurityHelper::e($doc['extension'])) ?></span>
                    </td>
                    <td class="text-center">
                        <?php if ($doc['statut'] === 'publie'): ?>
                        <span class="badge bg-success">Publié</span>
                        <?php elseif ($doc['statut'] === 'brouillon'): ?>
                        <span class="badge bg-warning text-dark">Brouillon</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Archivé</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?= $doc['public'] ? '<i class="fa-solid fa-globe text-success" title="Public"></i>' : '<i class="fa-solid fa-lock text-secondary" title="Privé"></i>' ?>
                    </td>
                    <td class="text-center small"><?= number_format($doc['telechargements']) ?></td>
                    <td class="pe-3 text-end">
                        <div class="btn-group btn-group-sm">
                            <?php if ($doc['statut'] !== 'publie'): ?>
                            <a href="<?= BASE_URL ?>/bibliotheque/admin/<?= $doc['id'] ?>/statut/publie"
                               class="btn btn-outline-success" title="Publier">
                                <i class="fa-solid fa-check"></i>
                            </a>
                            <?php else: ?>
                            <a href="<?= BASE_URL ?>/bibliotheque/admin/<?= $doc['id'] ?>/statut/archive"
                               class="btn btn-outline-warning" title="Archiver">
                                <i class="fa-solid fa-box-archive"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/bibliotheque/admin/<?= $doc['id'] ?>/supprimer"
                               class="btn btn-outline-danger" title="Supprimer"
                               onclick="return confirm('Supprimer ce document ?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?= BASE_URL ?>/bibliotheque" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-eye me-1"></i>Voir le site public
    </a>
</div>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
