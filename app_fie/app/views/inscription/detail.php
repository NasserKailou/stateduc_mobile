<?php
/**
 * FIE — Vue : Fiche détail de l'élève
 * Bootstrap 5 + Font Awesome — Charte Burundi
 * CORRECTION Phase 2 :
 *   - Remplacement require FIE_VIEWS_PATH par BASE_PATH
 *   - Suppression use / namespace
 *   - Redesign Bootstrap complet avec carte IUE hero
 * Variables : $eleve (array), $inscriptions (array), $success (bool)
 */
$page_title  = 'Fiche Élève — ' . SecurityHelper::e($eleve['iue'] ?? '');
$active_menu = 'inscription';
require BASE_PATH . '/app/views/layouts/header.php';
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inscription/recherche">Inscriptions</a></li>
        <li class="breadcrumb-item active">Fiche élève</li>
    </ol>
</nav>

<!-- ── Boutons d'action ──────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 fw-bold mb-0">
        <i class="fa-solid fa-id-card me-2" style="color:var(--fie-red)"></i>Fiche Élève
    </h1>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/inscription/<?= urlencode($eleve['iue'] ?? '') ?>/imprimer"
           target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-print me-1"></i>Imprimer
        </a>
        <a href="<?= BASE_URL ?>/inscription/nouveau" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>Nouvelle inscription
        </a>
    </div>
</div>

<!-- ── Flash success ─────────────────────────────────────────────────────── -->
<?php if (!empty($success)): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-3">
    <i class="fa-solid fa-circle-check"></i>
    <span>Inscription enregistrée avec succès. L'IUE a été généré et attribué.</span>
</div>
<?php endif; ?>

<!-- ══ CARTE IUE HERO ═══════════════════════════════════════════════════════ -->
<div class="fie-iue-hero mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="small text-white mb-1" style="opacity:.8">
                <i class="fa-solid fa-fingerprint me-1"></i>Identifiant Unique de l'Élève (IUE)
            </div>
            <div class="fie-iue-display"><?= SecurityHelper::e($eleve['iue'] ?? '') ?></div>
            <div class="small mt-1" style="opacity:.7">
                Cet identifiant est permanent et suit l'élève tout au long de sa scolarité.
            </div>
        </div>
        <?php
        require_once BASE_PATH . '/services/IueGenerator.php';
        $iueValide = IueGenerator::validate($eleve['iue'] ?? '');
        ?>
        <div>
            <?php if ($iueValide): ?>
            <span class="badge bg-success fs-6 px-3 py-2">
                <i class="fa-solid fa-shield-halved me-1"></i>IUE valide
            </span>
            <?php else: ?>
            <span class="badge bg-danger fs-6 px-3 py-2">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>IUE invalide
            </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Alerte doublon ────────────────────────────────────────────────────── -->
<?php if (!empty($eleve['doublon_suspect'])): ?>
<div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
    <i class="fa-solid fa-triangle-exclamation mt-1"></i>
    <div>
        <strong>Doublon potentiel signalé.</strong>
        <?php if (!empty($eleve['doublon_iue_ref'])): ?>
        Voir l'IUE de référence :
        <a href="<?= BASE_URL ?>/inscription/<?= urlencode($eleve['doublon_iue_ref']) ?>">
            <?= SecurityHelper::e($eleve['doublon_iue_ref']) ?>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── État civil + Tuteur ───────────────────────────────────────────────── -->
<div class="row g-4 mb-4">

    <!-- État civil -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header fw-semibold bg-white border-bottom">
                <i class="fa-solid fa-person me-2" style="color:var(--fie-red)"></i>État civil
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="ps-3 text-muted fw-normal" style="width:40%">Nom</th>
                            <td class="fw-semibold"><?= SecurityHelper::e($eleve['nom'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Prénom(s)</th>
                            <td><?= SecurityHelper::e($eleve['prenoms'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Sexe</th>
                            <td>
                                <?php if (($eleve['sexe'] ?? '') === 'M'): ?>
                                <span class="badge bg-primary">
                                    <i class="fa-solid fa-mars me-1"></i>Masculin
                                </span>
                                <?php else: ?>
                                <span class="badge" style="background:#e83e8c">
                                    <i class="fa-solid fa-venus me-1"></i>Féminin
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Date de naissance</th>
                            <td><?= SecurityHelper::e(date('d/m/Y', strtotime($eleve['date_naissance'] ?? 'now'))) ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Lieu de naissance</th>
                            <td><?= SecurityHelper::e($eleve['lieu_naissance'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Province de naissance</th>
                            <td><?= SecurityHelper::e($eleve['province_naissance'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Nationalité</th>
                            <td><?= SecurityHelper::e($eleve['nationalite'] ?? '—') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tuteur / Parents -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header fw-semibold bg-white border-bottom">
                <i class="fa-solid fa-people-roof me-2" style="color:var(--fie-green)"></i>Tuteur / Parents
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="ps-3 text-muted fw-normal" style="width:40%">Nom du père</th>
                            <td><?= SecurityHelper::e($eleve['nom_pere'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Nom de la mère</th>
                            <td><?= SecurityHelper::e($eleve['nom_mere'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Tuteur légal</th>
                            <td><?= SecurityHelper::e($eleve['nom_tuteur'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Téléphone tuteur</th>
                            <td><?= SecurityHelper::e($eleve['telephone_tuteur'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">N° acte de naissance</th>
                            <td><?= SecurityHelper::e($eleve['numero_acte_naissance'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Date de l'acte</th>
                            <td><?= !empty($eleve['date_acte_naissance']) ? date('d/m/Y', strtotime($eleve['date_acte_naissance'])) : '—' ?></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted fw-normal">Commune de l'acte</th>
                            <td><?= SecurityHelper::e($eleve['commune_acte'] ?? '—') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ── Historique des inscriptions ──────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header fw-semibold bg-white border-bottom">
        <i class="fa-solid fa-clock-rotate-left me-2" style="color:var(--fie-red)"></i>
        Historique des inscriptions
    </div>
    <?php if (empty($inscriptions)): ?>
    <div class="card-body text-center text-muted py-4">
        <i class="fa-solid fa-circle-info me-1"></i>Aucune inscription enregistrée.
    </div>
    <?php else: ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Année</th>
                        <th>Établissement</th>
                        <th>Localisation</th>
                        <th>Secteur</th>
                        <th>Niveau</th>
                        <th>Section</th>
                        <th>Classe</th>
                        <th class="text-center">Statut</th>
                        <th class="pe-3">Date insc.</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($inscriptions as $ins): ?>
                <tr>
                    <td class="ps-3 fw-semibold"><?= SecurityHelper::e($ins['code_type_annee']) ?></td>
                    <td class="small"><?= SecurityHelper::e($ins['nom_etablissement'] ?? '') ?></td>
                    <td class="small text-muted"><?= SecurityHelper::e($ins['chaine_localisation'] ?? '') ?></td>
                    <td class="small"><?= SecurityHelper::e($ins['libelle_secteur'] ?? $ins['code_type_secteur_ens'] ?? '') ?></td>
                    <td class="small"><?= SecurityHelper::e($ins['libelle_niveau'] ?? $ins['code_type_niveau'] ?? '') ?></td>
                    <td class="small"><?= SecurityHelper::e($ins['code_type_section'] ?? '') ?></td>
                    <td class="small"><?= SecurityHelper::e($ins['numero_classe'] ?? '—') ?></td>
                    <td class="text-center">
                        <?php
                        $s = $ins['statut'] ?? 'actif';
                        $bg2 = match($s) { 'actif'=>'success', 'transfere'=>'info', 'sorti'=>'secondary', default=>'secondary' };
                        ?>
                        <span class="badge bg-<?= $bg2 ?> small"><?= SecurityHelper::e($s) ?></span>
                    </td>
                    <td class="pe-3 small text-nowrap">
                        <?= SecurityHelper::e(date('d/m/Y', strtotime($ins['date_inscription'] ?? 'now'))) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
