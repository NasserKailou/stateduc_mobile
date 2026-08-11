<?php
/**
 * FIE — Vue : Résultats de recherche d'élèves
 * Rendue par InscriptionController::search()
 * Variables injectées :
 *   $query        string  — terme de recherche saisi
 *   $results      array   — lignes issues de EleveModel::search()
 *   $total        int     — nombre total de résultats (avant pagination)
 *   $page         int     — page courante
 *   $per_page     int     — résultats par page
 *   $pages        int     — nombre total de pages
 *   $criteria     array   — filtres actifs (province, secteur, annee)
 *   $provinces    array   — liste pour filtre province
 *   $secteurs     array   — liste pour filtre secteur
 *   $annees       array   — liste des années scolaires
 * @var string  $query
 * @var array   $results
 * @var int     $total
 * @var int     $page
 * @var int     $per_page
 * @var int     $pages
 * @var array   $criteria
 * @var array   $provinces
 * @var array   $secteurs
 * @var array   $annees
 */
$page_title   = 'Recherche d\'élèves — FIE';
$active_menu  = 'inscription';
require __DIR__ . '/../layouts/header.php';

use App\Services\SecurityHelper;
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="Fil d'Ariane" class="fie-breadcrumb">
    <ol>
        <li><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li><a href="<?= BASE_URL ?>/inscription">Inscriptions</a></li>
        <li aria-current="page">Recherche</li>
    </ol>
</nav>

<!-- ── Titre + bouton Nouvelle inscription ───────────────────────────────── -->
<div class="fie-page-header">
    <div>
        <h1 class="fie-page-title">
            <svg class="fie-icon" aria-hidden="true" viewBox="0 0 24 24">
                <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0
                         9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19
                         l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14
                         9.5 11.99 14 9.5 14z"/>
            </svg>
            Recherche d'élèves
        </h1>
        <?php if ($total > 0): ?>
            <p class="fie-page-subtitle">
                <?= $total ?> résultat<?= $total > 1 ? 's' : '' ?>
                <?php if (!empty($query)): ?>
                    pour <em>«&nbsp;<?= SecurityHelper::e($query) ?>&nbsp;»</em>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    <?php if (SecurityHelper::isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/inscription/nouveau" class="fie-btn fie-btn--primary">
            <svg class="fie-icon" aria-hidden="true" viewBox="0 0 24 24">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            Nouvelle inscription
        </a>
    <?php endif; ?>
</div>

<!-- ── Formulaire de recherche + filtres ─────────────────────────────────── -->
<section class="fie-card fie-search-panel" aria-label="Paramètres de recherche">
    <form method="get" action="<?= BASE_URL ?>/inscription/recherche" class="fie-search-form" id="searchForm">

        <!-- Barre principale -->
        <div class="fie-search-main">
            <label for="q" class="fie-sr-only">Recherche par nom, prénom ou IUE</label>
            <div class="fie-search-input-wrapper">
                <svg class="fie-search-icon" aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5
                             0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5
                             4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01
                             5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input
                    type="search"
                    id="q"
                    name="q"
                    class="fie-input fie-search-input"
                    placeholder="Nom, prénom, IUE, date de naissance…"
                    value="<?= SecurityHelper::e($query) ?>"
                    autocomplete="off"
                    autofocus
                    aria-label="Terme de recherche"
                >
            </div>
            <button type="submit" class="fie-btn fie-btn--primary fie-search-btn">
                Rechercher
            </button>
        </div>

        <!-- Filtres avancés (repliables) -->
        <details class="fie-filters-details" <?= (!empty($criteria)) ? 'open' : '' ?>>
            <summary class="fie-filters-summary">
                Filtres avancés
                <?php if (!empty(array_filter($criteria ?? []))): ?>
                    <span class="fie-badge fie-badge--info">actifs</span>
                <?php endif; ?>
            </summary>
            <div class="fie-filters-grid">

                <!-- Province -->
                <div class="fie-form-group">
                    <label for="f_province" class="fie-label">Province</label>
                    <select id="f_province" name="province" class="fie-select">
                        <option value="">— Toutes —</option>
                        <?php foreach ($provinces as $prov): ?>
                            <option value="<?= SecurityHelper::e($prov['libelle']) ?>"
                                <?= (($criteria['province'] ?? '') === $prov['libelle']) ? 'selected' : '' ?>>
                                <?= SecurityHelper::e($prov['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Secteur d'enseignement -->
                <div class="fie-form-group">
                    <label for="f_secteur" class="fie-label">Secteur d'enseignement</label>
                    <select id="f_secteur" name="secteur" class="fie-select">
                        <option value="">— Tous —</option>
                        <?php foreach ($secteurs as $code => $libelle): ?>
                            <option value="<?= (int)$code ?>"
                                <?= (isset($criteria['secteur']) && (int)$criteria['secteur'] === (int)$code) ? 'selected' : '' ?>>
                                <?= SecurityHelper::e($libelle) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Année scolaire -->
                <div class="fie-form-group">
                    <label for="f_annee" class="fie-label">Année scolaire</label>
                    <select id="f_annee" name="annee" class="fie-select">
                        <option value="">— Toutes —</option>
                        <?php foreach ($annees as $code => $libelle): ?>
                            <option value="<?= (int)$code ?>"
                                <?= (isset($criteria['annee']) && (int)$criteria['annee'] === (int)$code) ? 'selected' : '' ?>>
                                <?= SecurityHelper::e($libelle) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Doublon suspect uniquement -->
                <div class="fie-form-group fie-form-group--checkbox-only">
                    <label class="fie-checkbox-label">
                        <input type="checkbox" name="doublons_only" value="1"
                            class="fie-checkbox"
                            <?= !empty($criteria['doublons_only']) ? 'checked' : '' ?>>
                        Afficher uniquement les doublons suspects
                    </label>
                </div>

            </div><!-- /.fie-filters-grid -->

            <div class="fie-filters-actions">
                <button type="submit" class="fie-btn fie-btn--secondary">
                    Appliquer les filtres
                </button>
                <a href="<?= BASE_URL ?>/inscription/recherche" class="fie-btn fie-btn--ghost">
                    Réinitialiser
                </a>
            </div>
        </details>

    </form>
</section>

<!-- ── Résultats ─────────────────────────────────────────────────────────── -->
<?php if (empty($query) && empty(array_filter($criteria ?? []))): ?>
    <!-- État initial : invitation à rechercher -->
    <div class="fie-empty-state">
        <svg class="fie-empty-state__icon" aria-hidden="true" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48
                     10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
        </svg>
        <p class="fie-empty-state__text">
            Saisissez un nom, prénom, IUE ou date de naissance pour rechercher un élève.
        </p>
    </div>

<?php elseif (empty($results)): ?>
    <!-- Aucun résultat -->
    <div class="fie-empty-state fie-empty-state--warn">
        <svg class="fie-empty-state__icon" aria-hidden="true" viewBox="0 0 24 24">
            <path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2
                     12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52
                     2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8
                     8 3.58 8 8-3.58 8-8 8z"/>
        </svg>
        <p class="fie-empty-state__text">
            Aucun élève ne correspond à votre recherche.
        </p>
        <?php if (SecurityHelper::isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/inscription/nouveau?nom=<?= urlencode($query) ?>"
               class="fie-btn fie-btn--primary">
                Inscrire un nouvel élève
            </a>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- Tableau de résultats -->
    <section aria-label="Résultats de recherche">
        <div class="fie-table-wrapper">
            <table class="fie-table fie-table--hover" aria-live="polite">
                <caption class="fie-sr-only">
                    Résultats de recherche d'élèves —
                    <?= $total ?> résultat<?= $total > 1 ? 's' : '' ?>
                </caption>
                <thead>
                    <tr>
                        <th scope="col" class="fie-table__th fie-table__th--iue">IUE</th>
                        <th scope="col" class="fie-table__th">Nom</th>
                        <th scope="col" class="fie-table__th">Prénom(s)</th>
                        <th scope="col" class="fie-table__th fie-table__th--date">
                            Naissance
                        </th>
                        <th scope="col" class="fie-table__th fie-table__th--sexe">
                            Sexe
                        </th>
                        <th scope="col" class="fie-table__th">Dernier établissement</th>
                        <th scope="col" class="fie-table__th fie-table__th--statut">
                            Statut
                        </th>
                        <th scope="col" class="fie-table__th fie-table__th--actions">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $eleve): ?>
                        <tr class="fie-table__row <?= !empty($eleve['doublon_suspect']) ? 'fie-table__row--warn' : '' ?>">

                            <!-- IUE -->
                            <td class="fie-table__td fie-table__td--iue">
                                <code class="fie-iue-badge fie-iue-badge--sm">
                                    <?= SecurityHelper::e($eleve['iue']) ?>
                                </code>
                                <?php if (!empty($eleve['doublon_suspect'])): ?>
                                    <span class="fie-badge fie-badge--warn" title="Doublon suspect détecté">
                                        ⚠ doublon
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Nom -->
                            <td class="fie-table__td fie-table__td--nom">
                                <?= SecurityHelper::e($eleve['nom']) ?>
                            </td>

                            <!-- Prénom -->
                            <td class="fie-table__td">
                                <?= SecurityHelper::e($eleve['prenom']) ?>
                            </td>

                            <!-- Date de naissance -->
                            <td class="fie-table__td fie-table__td--date">
                                <?php
                                $ddn = $eleve['date_naissance'] ?? null;
                                echo $ddn
                                    ? date('d/m/Y', strtotime($ddn))
                                    : '<span class="fie-text--muted">—</span>';
                                ?>
                            </td>

                            <!-- Sexe -->
                            <td class="fie-table__td fie-table__td--center">
                                <span class="fie-badge fie-badge--<?= $eleve['sexe'] === 'F' ? 'pink' : 'blue' ?>">
                                    <?= SecurityHelper::e($eleve['sexe']) ?>
                                </span>
                            </td>

                            <!-- Dernier établissement -->
                            <td class="fie-table__td fie-table__td--etab">
                                <?php if (!empty($eleve['dernier_etablissement'])): ?>
                                    <span class="fie-text--sm">
                                        <?= SecurityHelper::e($eleve['dernier_etablissement']) ?>
                                    </span>
                                    <?php if (!empty($eleve['derniere_annee'])): ?>
                                        <span class="fie-text--muted fie-text--xs">
                                            (<?= SecurityHelper::e($eleve['derniere_annee']) ?>)
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="fie-text--muted">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- Statut actif/transféré/sorti -->
                            <td class="fie-table__td fie-table__td--center">
                                <?php
                                $statut = $eleve['statut'] ?? 'actif';
                                $statutClass = match($statut) {
                                    'actif'    => 'success',
                                    'transfere' => 'info',
                                    'sorti'    => 'neutral',
                                    default    => 'neutral',
                                };
                                $statutLabels = [
                                    'actif'     => 'Actif',
                                    'transfere' => 'Transféré',
                                    'sorti'     => 'Sorti',
                                ];
                                ?>
                                <span class="fie-badge fie-badge--<?= $statutClass ?>">
                                    <?= SecurityHelper::e($statutLabels[$statut] ?? $statut) ?>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="fie-table__td fie-table__td--actions">
                                <a href="<?= BASE_URL ?>/inscription/<?= urlencode($eleve['iue']) ?>"
                                   class="fie-btn fie-btn--xs fie-btn--secondary"
                                   title="Voir la fiche de <?= SecurityHelper::e($eleve['nom'] . ' ' . $eleve['prenom']) ?>">
                                    Fiche
                                </a>
                                <a href="<?= BASE_URL ?>/inscription/<?= urlencode($eleve['iue']) ?>/imprimer"
                                   class="fie-btn fie-btn--xs fie-btn--ghost"
                                   target="_blank"
                                   title="Imprimer la fiche">
                                    Imprimer
                                </a>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div><!-- /.fie-table-wrapper -->

        <!-- ── Pagination ──────────────────────────────────────────────── -->
        <?php if ($pages > 1): ?>
            <nav class="fie-pagination" aria-label="Pagination des résultats">
                <?php
                // Construction de la query string sans le paramètre page
                $params = $_GET;
                unset($params['page']);
                $baseQs = http_build_query($params);
                $link = function(int $p) use ($baseQs): string {
                    return BASE_URL . '/inscription/recherche?' . $baseQs
                         . ($baseQs ? '&' : '') . 'page=' . $p;
                };
                ?>

                <?php if ($page > 1): ?>
                    <a href="<?= $link(1) ?>" class="fie-pagination__btn" aria-label="Première page">
                        «
                    </a>
                    <a href="<?= $link($page - 1) ?>" class="fie-pagination__btn" aria-label="Page précédente">
                        ‹
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end   = min($pages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <?php if ($i === $page): ?>
                        <span class="fie-pagination__btn fie-pagination__btn--active"
                              aria-current="page" aria-label="Page <?= $i ?>">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= $link($i) ?>"
                           class="fie-pagination__btn"
                           aria-label="Page <?= $i ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $pages): ?>
                    <a href="<?= $link($page + 1) ?>" class="fie-pagination__btn" aria-label="Page suivante">
                        ›
                    </a>
                    <a href="<?= $link($pages) ?>" class="fie-pagination__btn" aria-label="Dernière page">
                        »
                    </a>
                <?php endif; ?>

                <span class="fie-pagination__info">
                    Page <?= $page ?> / <?= $pages ?>
                    &nbsp;(<?= $total ?> résultat<?= $total > 1 ? 's' : '' ?>)
                </span>
            </nav>
        <?php endif; ?>

    </section>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
