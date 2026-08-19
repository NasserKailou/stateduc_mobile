<?php
/**
 * FIE — Vue : Liste complète des élèves inscrits + Recherche avancée
 * Bootstrap 5 + Font Awesome — Charte Burundi Rouge/Blanc/Vert
 * Filtres : nom/IUE, école, colline, commune, province, sexe, année
 * Visiblité : admin = tout, directeur/enseignant = leur établissement
 */
$page_title  = $page_title  ?? "Liste des élèves — FIE";
$active_menu = 'recherche';
require BASE_PATH . '/app/views/layouts/app_layout.php';
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Liste &amp; Recherche</li>
    </ol>
</nav>

<!-- ── Titre ───────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="fa-solid fa-users me-2" style="color:var(--fie-primary)"></i>
            Élèves inscrits
        </h1>
        <p class="text-muted mb-0 small">
            <?php if ($total > 0): ?>
            <strong><?= number_format($total) ?></strong> élève<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?>
            <?php if (!empty(array_filter($criteria))): ?>
            — filtrés sur <?= count(array_filter($criteria)) ?> critère<?= count(array_filter($criteria)) > 1 ? 's' : '' ?>
            <?php endif; ?>
            <?php elseif (isset($total) && $total === 0): ?>
            Aucun élève ne correspond aux critères.
            <?php else: ?>
            Utilisez les filtres ci-dessous pour rechercher.
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if (SecurityHelper::isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/inscription/nouveau" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus me-1"></i>Nouvelle inscription
        </a>
        <?php endif; ?>
        <?php if (!empty($results)): ?>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()" type="button">
            <i class="fa-solid fa-print me-1"></i>Imprimer liste
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ── Formulaire de recherche + filtres ────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" style="border-bottom:2px solid var(--fie-primary);">
        <i class="fa-solid fa-filter me-2" style="color:var(--fie-primary)"></i>
        Filtres de recherche
        <?php if (!empty(array_filter($criteria))): ?>
        <span class="badge ms-2" style="background:var(--fie-primary)">
            <?= count(array_filter($criteria)) ?> actif<?= count(array_filter($criteria))>1?'s':'' ?>
        </span>
        <?php endif; ?>
    </div>
    <div class="card-body pb-2">
        <form method="get" action="<?= BASE_URL ?>/inscription/recherche" id="searchForm">

            <!-- Ligne 1 : Recherche textuelle -->
            <div class="row g-2 mb-2">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold mb-1">Nom / Prénom(s) / IUE</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass" style="color:var(--fie-primary)"></i>
                        </span>
                        <input type="search" name="q" id="q" class="form-control border-start-0"
                               placeholder="Nom, prénom ou identifiant IUE…"
                               value="<?= SecurityHelper::e($criteria['q'] ?? '') ?>"
                               autocomplete="off" aria-label="Recherche rapide">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">IUE exact</label>
                    <input type="text" name="iue" class="form-control"
                           placeholder="ex: BI-2024-0001"
                           value="<?= SecurityHelper::e($criteria['iue'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Sexe</label>
                    <select name="sexe" class="form-select">
                        <option value="">— Tous —</option>
                        <option value="M" <?= ($criteria['sexe'] ?? '') === 'M' ? 'selected' : '' ?>>Masculin</option>
                        <option value="F" <?= ($criteria['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>Féminin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Année scolaire</label>
                    <select name="annee" class="form-select">
                        <option value="">— Toutes —</option>
                        <?php foreach ($annees ?? [] as $a): ?>
                        <option value="<?= (int)$a['code_type_annee'] ?>"
                            <?= ($criteria['annee'] ?? 0) == $a['code_type_annee'] ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($a['libelle']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Ligne 2 : Filtres géographiques / établissement -->
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">
                        <i class="fa-solid fa-map-marker-alt me-1" style="color:var(--fie-primary)"></i>Province
                    </label>
                    <select id="f_province" name="province" class="form-select form-select-sm">
                        <option value="">— Toutes provinces —</option>
                        <?php foreach ($provinces ?? [] as $prov): ?>
                        <option value="<?= SecurityHelper::e($prov['province']) ?>"
                            <?= ($criteria['province'] ?? '') === $prov['province'] ? 'selected' : '' ?>>
                            <?= SecurityHelper::e($prov['province']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">
                        <i class="fa-solid fa-city me-1" style="color:var(--fie-primary)"></i>Commune
                    </label>
                    <input type="text" name="commune" class="form-control form-control-sm"
                           placeholder="Nom commune…"
                           value="<?= SecurityHelper::e($criteria['commune'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">
                        <i class="fa-solid fa-hill-rockslide me-1" style="color:var(--fie-primary)"></i>Colline / Quartier
                    </label>
                    <input type="text" name="colline" class="form-control form-control-sm"
                           placeholder="Nom colline…"
                           value="<?= SecurityHelper::e($criteria['colline'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">
                        <i class="fa-solid fa-school me-1" style="color:var(--fie-primary)"></i>École / Établissement
                    </label>
                    <input type="text" name="ecole" class="form-control form-control-sm"
                           placeholder="Nom ou code école…"
                           value="<?= SecurityHelper::e($criteria['ecole'] ?? '') ?>">
                </div>
            </div>

            <!-- Boutons -->
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-sm btn-primary px-4">
                    <i class="fa-solid fa-search me-1"></i>Rechercher
                </button>
                <a href="<?= BASE_URL ?>/inscription/recherche" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left me-1"></i>Réinitialiser
                </a>
                <?php if (!empty($results)): ?>
                <span class="ms-auto text-muted small align-self-center">
                    Page <?= $page ?? 1 ?> / <?= $pages ?? 1 ?>
                    — <?= number_format($total ?? 0) ?> résultat<?= ($total ?? 0) > 1 ? 's' : '' ?>
                </span>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>

<!-- ── Résultats ─────────────────────────────────────────────────────────── -->
<?php if (empty($results) && isset($total) && $total === 0): ?>

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-circle-exclamation fa-3x mb-3 d-block text-warning" style="opacity:.5"></i>
        <p class="text-muted mb-3">Aucun élève ne correspond aux critères de recherche.</p>
        <?php if (SecurityHelper::isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/inscription/nouveau" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>Inscrire un nouvel élève
        </a>
        <?php endif; ?>
    </div>
</div>

<?php elseif (empty($results) && !isset($total)): ?>

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="fa-solid fa-magnifying-glass fa-3x mb-3 d-block" style="opacity:.3"></i>
        <p class="mb-0">Saisissez des critères et cliquez sur <strong>Rechercher</strong>, ou laissez les filtres vides pour afficher tous les élèves.</p>
    </div>
</div>

<?php else: ?>

<!-- Tableau des élèves -->
<div class="card border-0 shadow-sm">
    <!-- En-tête avec compteur + export -->
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-2"
         style="border-bottom:2px solid var(--fie-primary);">
        <span class="fw-semibold small">
            <i class="fa-solid fa-table me-1" style="color:var(--fie-primary)"></i>
            <?= number_format($total ?? count($results)) ?> élève<?= ($total ?? count($results)) > 1 ? 's' : '' ?>
        </span>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/inscription/nouveau" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i><span class="d-none d-md-inline">Inscrire</span>
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:130px">IUE</th>
                        <th>Nom</th>
                        <th>Prénom(s)</th>
                        <th class="text-center" style="width:70px">Sexe</th>
                        <th>Naissance</th>
                        <th>Province</th>
                        <th>Établissement</th>
                        <th class="text-center" style="width:80px">Statut</th>
                        <th class="text-center pe-3" style="width:100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($results as $eleve): ?>
                    <tr <?= !empty($eleve['doublon_suspect']) ? 'class="table-warning"' : '' ?>>

                        <!-- IUE -->
                        <td class="ps-3">
                            <code class="fie-iue-badge small"><?= SecurityHelper::e($eleve['iue']) ?></code>
                            <?php if (!empty($eleve['doublon_suspect'])): ?>
                            <br><span class="badge bg-warning text-dark mt-1" style="font-size:.6rem;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>doublon
                            </span>
                            <?php endif; ?>
                        </td>

                        <!-- Nom -->
                        <td class="fw-semibold"><?= SecurityHelper::e($eleve['nom']) ?></td>

                        <!-- Prénom -->
                        <td><?= SecurityHelper::e($eleve['prenoms'] ?? $eleve['prenom'] ?? '') ?></td>

                        <!-- Sexe -->
                        <td class="text-center">
                            <?php if (($eleve['sexe'] ?? '') === 'F'): ?>
                            <span class="badge" style="background:#e83e8c">F</span>
                            <?php elseif (($eleve['sexe'] ?? '') === 'M'): ?>
                            <span class="badge bg-primary">M</span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>

                        <!-- Date naissance -->
                        <td class="text-nowrap">
                            <?php
                            $ddn = $eleve['date_naissance'] ?? null;
                            echo $ddn ? date('d/m/Y', strtotime($ddn)) : '<span class="text-muted">—</span>';
                            ?>
                        </td>

                        <!-- Province -->
                        <td class="small text-muted">
                            <?= !empty($eleve['province']) ? SecurityHelper::e($eleve['province']) : '—' ?>
                        </td>

                        <!-- Établissement -->
                        <td>
                            <?php if (!empty($eleve['dernier_etablissement'])): ?>
                            <div class="small"><?= SecurityHelper::e($eleve['dernier_etablissement']) ?></div>
                            <?php if (!empty($eleve['derniere_annee'])): ?>
                            <div class="text-muted" style="font-size:.72rem;">
                                (<?= SecurityHelper::e($eleve['derniere_annee']) ?>)
                            </div>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
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
                            <span class="badge bg-<?= $bg ?>" style="font-size:.65rem;">
                                <i class="fa-solid <?= $icon ?> me-1"></i>
                                <?= $labels[$statut] ?? $statut ?>
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/inscription/<?= urlencode($eleve['iue']) ?>"
                                   class="btn btn-outline-primary"
                                   title="Voir la fiche élève">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/inscription/<?= urlencode($eleve['iue']) ?>/imprimer"
                                   class="btn btn-outline-secondary"
                                   target="_blank"
                                   title="Imprimer la fiche">
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

<!-- ── Pagination + sélecteur de résultats/page ──────────────────────── -->
<?php
$_page    = $page   ?? 1;
$_pages   = $pages  ?? 1;
$_total   = $total  ?? 0;
$_perPage = $perPage ?? 50;
$_params  = $_GET;
unset($_params['page']);
$_baseQs  = http_build_query($_params);
$_link    = fn(int $p): string => BASE_URL . '/inscription/recherche?' . ($_baseQs ? $_baseQs . '&' : '') . 'page=' . $p;
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3 mb-2">
    <!-- Sélecteur résultats/page -->
    <form method="get" action="<?= BASE_URL ?>/inscription/recherche" class="d-flex align-items-center gap-2 no-print">
        <?php foreach ($_GET as $k => $v): if ($k === 'per_page' || $k === 'page') continue; ?>
        <input type="hidden" name="<?= htmlspecialchars($k, ENT_QUOTES) ?>"
               value="<?= htmlspecialchars((string)$v, ENT_QUOTES) ?>">
        <?php endforeach; ?>
        <label class="form-label mb-0 small fw-semibold text-muted" for="perPageSel">Résultats/page :</label>
        <select name="per_page" id="perPageSel" class="form-select form-select-sm" style="width:auto;"
                onchange="this.form.submit()">
            <?php foreach ([25, 50, 100, 200] as $pp): ?>
            <option value="<?= $pp ?>" <?= $pp === $_perPage ? 'selected' : '' ?>><?= $pp ?></option>
            <?php endforeach; ?>
        </select>
        <span class="text-muted small">
            <?= number_format($_total) ?> élève<?= $_total > 1 ? 's' : '' ?>
            &nbsp;·&nbsp;
            Page <?= $_page ?> / <?= $_pages ?>
        </span>
    </form>

    <!-- Contrôles de pagination -->
    <?php if ($_pages > 1): ?>
    <nav aria-label="Pagination des élèves">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $_page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $_link(1) ?>" aria-label="Première page" title="Première">«</a>
            </li>
            <li class="page-item <?= $_page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $_link($_page - 1) ?>" aria-label="Page précédente">‹</a>
            </li>
            <?php
            // Fenêtre de pages : max 7 liens (± 3 autour de la page courante)
            $wStart = max(1, $_page - 3);
            $wEnd   = min($_pages, $_page + 3);
            if ($wStart > 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif;
            for ($i = $wStart; $i <= $wEnd; $i++): ?>
            <li class="page-item <?= $i === $_page ? 'active' : '' ?>">
                <a class="page-link" href="<?= $_link($i) ?>"><?= $i ?></a>
            </li>
            <?php endfor;
            if ($wEnd < $_pages): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
            <li class="page-item <?= $_page >= $_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $_link($_page + 1) ?>" aria-label="Page suivante">›</a>
            </li>
            <li class="page-item <?= $_page >= $_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $_link($_pages) ?>" aria-label="Dernière page" title="Dernière">»</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php endif; ?>

<!-- CSS impression liste -->
<style>
@media print {
    .app-sidebar, .app-header, nav[aria-label="breadcrumb"], .card-header .btn,
    form, .pagination, .no-print { display: none !important; }
    .app-main, .main-content { padding: 0 !important; }
    table { font-size: 9pt !important; }
    .badge { border: 1px solid #999 !important; }
}
</style>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
