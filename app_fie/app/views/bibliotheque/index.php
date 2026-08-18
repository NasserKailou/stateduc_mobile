<?php
/**
 * FIE — Vue : Bibliothèque publique
 * Accessible à tous — Bootstrap 5 + FontAwesome
 */
$page_title     = $page_title  ?? 'Bibliothèque — FIE';
$active_menu    = $active_menu ?? 'bibliotheque';
$app_breadcrumb = [['label' => 'Bibliothèque', 'url' => '']];
require BASE_PATH . '/app/views/layouts/app_layout.php';
?>

<!-- ── En-tête ──────────────────────────────────────────────────────────── -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="fa-solid fa-book-open me-2" style="color:var(--fie-primary)"></i>Bibliothèque FIE
        </h1>
        <p class="text-muted mb-0 small">
            Annales, manuels, guides pédagogiques et documents de formation — téléchargement gratuit
        </p>
    </div>
    <?php if (SecurityHelper::isLoggedIn() && in_array(SecurityHelper::userRole(), ['super_admin','admin_central','bibliothecaire'])): ?>
    <a href="<?= BASE_URL ?>/bibliotheque/admin" class="btn btn-sm btn-outline-primary">
        <i class="fa-solid fa-gear me-1"></i>Gérer la bibliothèque
    </a>
    <?php endif; ?>
</div>

<!-- ── Barre de recherche + filtres ─────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= BASE_URL ?>/bibliotheque" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Rechercher
                </label>
                <input type="search" name="q" class="form-control form-control-sm"
                       placeholder="Titre, mot-clé, auteur…"
                       value="<?= SecurityHelper::e($_GET['q'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">
                    <i class="fa-solid fa-folder me-1"></i>Thématique
                </label>
                <select name="thematique" class="form-select form-select-sm">
                    <option value="0">Toutes les thématiques</option>
                    <?php foreach ($thematiques as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= (int)($_GET['thematique'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>>
                        <?= SecurityHelper::e($t['libelle']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">
                    <i class="fa-solid fa-graduation-cap me-1"></i>Niveau
                </label>
                <select name="niveau" class="form-select form-select-sm">
                    <option value="">Tous niveaux</option>
                    <option value="Préscolaire" <?= ($_GET['niveau'] ?? '') === 'Préscolaire' ? 'selected' : '' ?>>Préscolaire</option>
                    <option value="Primaire"    <?= ($_GET['niveau'] ?? '') === 'Primaire'    ? 'selected' : '' ?>>Primaire</option>
                    <option value="Secondaire"  <?= ($_GET['niveau'] ?? '') === 'Secondaire'  ? 'selected' : '' ?>>Secondaire</option>
                    <option value="Tous niveaux" <?= ($_GET['niveau'] ?? '') === 'Tous niveaux' ? 'selected' : '' ?>>Tous niveaux</option>
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Thématiques (navigation rapide) ──────────────────────────────────── -->
<?php if (empty($_GET['q']) && empty($_GET['thematique'])): ?>
<div class="row g-3 mb-4">
    <?php foreach ($thematiques as $t): ?>
    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
        <a href="<?= BASE_URL ?>/bibliotheque?thematique=<?= $t['id'] ?>"
           class="card fie-library-card text-decoration-none h-100">
            <div class="card-body text-center py-3">
                <div class="fie-lib-icon mb-2 mx-auto"
                     style="background:<?= SecurityHelper::e($t['couleur']) ?>22;color:<?= SecurityHelper::e($t['couleur']) ?>">
                    <i class="fa-solid <?= SecurityHelper::e($t['icone_fa']) ?>"></i>
                </div>
                <div class="small fw-semibold"><?= SecurityHelper::e($t['libelle']) ?></div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Liste des documents ───────────────────────────────────────────────── -->
<?php if (empty($documents)): ?>
<div class="text-center py-5">
    <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
    <p class="text-muted">Aucun document disponible pour cette recherche.<br>
    Revenez prochainement ou <a href="<?= BASE_URL ?>/bibliotheque">effacez les filtres</a>.</p>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($documents as $doc): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card fie-library-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <!-- Icône type document -->
                    <div class="fie-lib-icon flex-shrink-0"
                         style="background:<?= SecurityHelper::e($doc['couleur']) ?>22;color:<?= SecurityHelper::e($doc['couleur']) ?>">
                        <?php
                        $extIcons = ['pdf'=>'fa-file-pdf','doc'=>'fa-file-word','docx'=>'fa-file-word',
                                     'ppt'=>'fa-file-powerpoint','pptx'=>'fa-file-powerpoint',
                                     'xls'=>'fa-file-excel','xlsx'=>'fa-file-excel','zip'=>'fa-file-zipper'];
                        $ico = $extIcons[$doc['extension']] ?? 'fa-file';
                        ?>
                        <i class="fa-solid <?= $ico ?>"></i>
                    </div>
                    <div class="flex-fill min-w-0">
                        <!-- Badges thématique + extension -->
                        <div class="d-flex gap-1 mb-1 flex-wrap">
                            <span class="fie-lib-type-badge"
                                  style="background:<?= SecurityHelper::e($doc['couleur']) ?>22;color:<?= SecurityHelper::e($doc['couleur']) ?>">
                                <i class="fa-solid <?= SecurityHelper::e($doc['icone_fa']) ?> me-1"></i>
                                <?= SecurityHelper::e($doc['thematique_libelle']) ?>
                            </span>
                            <span class="badge bg-light text-muted fw-normal" style="font-size:.65rem">
                                .<?= strtoupper(SecurityHelper::e($doc['extension'])) ?>
                            </span>
                        </div>

                        <!-- Titre -->
                        <h6 class="fw-bold mb-1 text-truncate" title="<?= SecurityHelper::e($doc['titre']) ?>">
                            <?= SecurityHelper::e($doc['titre']) ?>
                        </h6>

                        <!-- Auteur + année + niveau -->
                        <div class="small text-muted">
                            <?php if ($doc['auteur']): ?>
                            <i class="fa-solid fa-user me-1"></i><?= SecurityHelper::e($doc['auteur']) ?>
                            <?php endif; ?>
                            <?php if ($doc['annee_publication']): ?>
                            <span class="ms-2"><i class="fa-solid fa-calendar me-1"></i><?= $doc['annee_publication'] ?></span>
                            <?php endif; ?>
                            <?php if ($doc['niveau_scolaire']): ?>
                            <span class="ms-2"><i class="fa-solid fa-graduation-cap me-1"></i><?= SecurityHelper::e($doc['niveau_scolaire']) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Description courte -->
                        <?php if ($doc['description']): ?>
                        <p class="small text-muted mt-1 mb-0" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                            <?= SecurityHelper::e($doc['description']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-2">
                <span class="small text-muted">
                    <i class="fa-solid fa-download me-1"></i><?= number_format($doc['telechargements']) ?> téléchargements
                </span>
                <a href="<?= BASE_URL ?>/bibliotheque/<?= $doc['id'] ?>/telecharger"
                   class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-download me-1"></i>Télécharger
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
