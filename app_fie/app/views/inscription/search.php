<?php
/**
 * FIE — Vue : Recherche d'élèves
 * Bootstrap 5 + Font Awesome — Charte Burundi
 * CORRECTION Phase 2 : suppression use App\Services\SecurityHelper + redesign BS5
 */
$page_title  = "Recherche d'élèves — FIE";
$active_menu = 'inscription';
require BASE_PATH . '/app/views/layouts/app_layout.php';
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Recherche</li>
    </ol>
</nav>

<!-- ── Titre ───────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="fa-solid fa-magnifying-glass me-2" style="color:var(--fie-red)"></i>
            Recherche d'élèves
        </h1>
        <?php if (($total ?? 0) > 0): ?>
        <p class="text-muted mb-0 small">
            <strong><?= $total ?></strong> résultat<?= $total > 1 ? 's' : '' ?>
            <?php if (!empty($query)): ?>
            pour <em>«&nbsp;<?= SecurityHelper::e($query) ?>&nbsp;»</em>
            <?php endif; ?>
        </p>
        <?php endif; ?>
    </div>
    <?php if (SecurityHelper::isLoggedIn()): ?>
    <a href="<?= BASE_URL ?>/inscription/nouveau" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i>Nouvelle inscription
    </a>
    <?php endif; ?>
</div>

<!-- ── Formulaire de recherche ─────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= BASE_URL ?>/inscription/recherche" id="searchForm">

            <!-- Barre principale -->
            <div class="input-group mb-3">
                <span class="input-group-text bg-white">
                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                </span>
                <input type="search" name="q" id="q" class="form-control border-start-0"
                       placeholder="Nom, prénom, IUE ou date de naissance…"
                       value="<?= SecurityHelper::e($query ?? '') ?>"
                       autocomplete="off" autofocus
                       aria-label="Terme de recherche">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-search me-1"></i>Rechercher
                </button>
            </div>

            <!-- Filtres avancés repliables -->
            <div class="accordion" id="accordionFiltres">
                <div class="accordion-item border-0 bg-light rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-light rounded-3 py-2 small fw-semibold"
                                type="button"
                                data-bs-toggle="collapse" data-bs-target="#filtresBody">
                            <i class="fa-solid fa-sliders me-2"></i>Filtres avancés
                            <?php if (!empty(array_filter($criteria ?? []))): ?>
                            <span class="badge bg-warning text-dark ms-2">actifs</span>
                            <?php endif; ?>
                        </button>
                    </h2>
                    <div id="filtresBody"
                         class="accordion-collapse collapse <?= !empty(array_filter($criteria ?? [])) ? 'show' : '' ?>">
                        <div class="accordion-body pt-2">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="f_province" class="form-label small fw-semibold">Province</label>
                                    <select id="f_province" name="province" class="form-select form-select-sm">
                                        <option value="">— Toutes —</option>
                                        <?php foreach ($provinces ?? [] as $prov): ?>
                                        <option value="<?= SecurityHelper::e($prov['province']) ?>"
                                            <?= (($criteria['province'] ?? '') === $prov['province']) ? 'selected' : '' ?>>
                                            <?= SecurityHelper::e($prov['province']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="f_secteur" class="form-label small fw-semibold">Secteur</label>
                                    <select id="f_secteur" name="secteur" class="form-select form-select-sm">
                                        <option value="">— Tous —</option>
                                        <?php foreach ($secteurs ?? [] as $code => $libelle): ?>
                                        <option value="<?= (int)$code ?>"
                                            <?= (isset($criteria['secteur']) && (int)$criteria['secteur'] === (int)$code) ? 'selected' : '' ?>>
                                            <?= SecurityHelper::e($libelle) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="f_annee" class="form-label small fw-semibold">Année scolaire</label>
                                    <select id="f_annee" name="annee" class="form-select form-select-sm">
                                        <option value="">— Toutes —</option>
                                        <?php foreach ($annees ?? [] as $code => $libelle): ?>
                                        <option value="<?= (int)$code ?>"
                                            <?= (isset($criteria['annee']) && (int)$criteria['annee'] === (int)$code) ? 'selected' : '' ?>>
                                            <?= SecurityHelper::e($libelle) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="doublons_only" value="1" id="doublonsOnly"
                                               <?= !empty($criteria['doublons_only']) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="doublonsOnly">
                                            Doublons suspects seulement
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-sm btn-secondary">
                                    <i class="fa-solid fa-filter me-1"></i>Appliquer
                                </button>
                                <a href="<?= BASE_URL ?>/inscription/recherche" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-rotate-left me-1"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- ── Résultats ────────────────────────────────────────────────────────── -->
<?php if (empty($query) && empty(array_filter($criteria ?? []))): ?>

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="fa-solid fa-magnifying-glass fa-3x mb-3 d-block" style="opacity:.3"></i>
        <p class="mb-0">Saisissez un nom, prénom, IUE ou date de naissance pour rechercher un élève.</p>
    </div>
</div>

<?php elseif (empty($results)): ?>

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-circle-exclamation fa-3x mb-3 d-block text-warning" style="opacity:.5"></i>
        <p class="text-muted mb-3">Aucun élève ne correspond à votre recherche.</p>
        <?php if (SecurityHelper::isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/inscription/nouveau?nom=<?= urlencode($query ?? '') ?>"
           class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>Inscrire un nouvel élève
        </a>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>

<!-- Tableau de résultats -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">IUE</th>
                        <th>Nom</th>
                        <th>Prénom(s)</th>
                        <th>Naissance</th>
                        <th class="text-center">Sexe</th>
                        <th>Dernier établissement</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($results as $eleve): ?>
                    <tr <?= !empty($eleve['doublon_suspect']) ? 'class="table-warning"' : '' ?>>

                        <!-- IUE -->
                        <td class="ps-3">
                            <code class="fie-iue-badge small"><?= SecurityHelper::e($eleve['iue']) ?></code>
                            <?php if (!empty($eleve['doublon_suspect'])): ?>
                            <span class="badge bg-warning text-dark ms-1 small">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>doublon
                            </span>
                            <?php endif; ?>
                        </td>

                        <!-- Nom -->
                        <td class="fw-semibold"><?= SecurityHelper::e($eleve['nom']) ?></td>

                        <!-- Prénom -->
                        <td><?= SecurityHelper::e($eleve['prenom'] ?? $eleve['prenoms'] ?? '') ?></td>

                        <!-- Date naissance -->
                        <td class="text-nowrap">
                            <?php
                            $ddn = $eleve['date_naissance'] ?? null;
                            echo $ddn ? date('d/m/Y', strtotime($ddn)) : '<span class="text-muted">—</span>';
                            ?>
                        </td>

                        <!-- Sexe -->
                        <td class="text-center">
                            <?php if ($eleve['sexe'] === 'F'): ?>
                            <span class="badge" style="background:#e83e8c">
                                <i class="fa-solid fa-venus me-1"></i>F
                            </span>
                            <?php else: ?>
                            <span class="badge bg-primary">
                                <i class="fa-solid fa-mars me-1"></i>M
                            </span>
                            <?php endif; ?>
                        </td>

                        <!-- Dernier établissement -->
                        <td>
                            <?php if (!empty($eleve['dernier_etablissement'])): ?>
                            <div class="small"><?= SecurityHelper::e($eleve['dernier_etablissement']) ?></div>
                            <?php if (!empty($eleve['derniere_annee'])): ?>
                            <div class="text-muted" style="font-size:.75rem">
                                (<?= SecurityHelper::e($eleve['derniere_annee']) ?>)
                            </div>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>

                        <!-- Statut -->
                        <td class="text-center">
                            <?php
                            $statut = $eleve['statut'] ?? 'actif';
                            [$bg, $icon] = match($statut) {
                                'actif'     => ['success', 'fa-circle-check'],
                                'transfere' => ['info',    'fa-right-left'],
                                'sorti'     => ['secondary','fa-door-open'],
                                default     => ['secondary','fa-circle'],
                            };
                            $labels = ['actif'=>'Actif','transfere'=>'Transféré','sorti'=>'Sorti'];
                            ?>
                            <span class="badge bg-<?= $bg ?>">
                                <i class="fa-solid <?= $icon ?> me-1"></i>
                                <?= $labels[$statut] ?? $statut ?>
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/inscription/<?= urlencode($eleve['iue']) ?>"
                                   class="btn btn-outline-primary"
                                   title="Voir la fiche">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/inscription/<?= urlencode($eleve['iue']) ?>/imprimer"
                                   class="btn btn-outline-secondary"
                                   target="_blank" title="Imprimer">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        </td>

                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination Bootstrap -->
<?php if (($pages ?? 1) > 1): ?>
<nav aria-label="Pagination" class="mt-3">
    <?php
    $params = $_GET;
    unset($params['page']);
    $baseQs = http_build_query($params);
    $link   = fn(int $p): string => BASE_URL . '/inscription/recherche?' . $baseQs . ($baseQs ? '&' : '') . 'page=' . $p;
    ?>
    <ul class="pagination justify-content-center mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $link(1) ?>" aria-label="Première">«</a>
        </li>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $link($page - 1) ?>" aria-label="Précédente">‹</a>
        </li>
        <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= $link($i) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $link($page + 1) ?>" aria-label="Suivante">›</a>
        </li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $link($pages) ?>" aria-label="Dernière">»</a>
        </li>
    </ul>
    <div class="text-center text-muted small mt-2">
        Page <?= $page ?> / <?= $pages ?> — <?= $total ?> résultat<?= $total > 1 ? 's' : '' ?>
    </div>
</nav>
<?php endif; ?>

<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
