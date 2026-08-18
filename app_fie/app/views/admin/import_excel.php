<?php
/**
 * FIE — Vue : Import Excel des établissements
 * Format ATLAS_COLLINE — 14 colonnes — FICHIER_ETAB.xlsx
 * Session 8 : alignement sur le vrai format FICHIER_ETAB.xlsx confirmé
 */
$page_title  = $page_title  ?? 'Import Excel — Administration FIE';
$active_menu = $active_menu ?? 'admin_import';
require BASE_PATH . '/app/views/layouts/app_layout.php';
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/tableau-de-bord">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Administration</a></li>
        <li class="breadcrumb-item active">Import Excel</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">
            <i class="fa-solid fa-file-excel me-2" style="color:#1d6f42"></i>
            Import Excel — Établissements
        </h1>
        <p class="text-muted small mb-0 mt-1">
            Format <strong>ATLAS_COLLINE</strong> — 14 colonnes — feuille active
        </p>
    </div>
    <a href="<?= BASE_URL ?>/admin/sync" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i>Synchronisation
    </a>
</div>

<!-- ── Alerte info ───────────────────────────────────────────────────────── -->
<div class="alert alert-info d-flex align-items-start gap-3 mb-4">
    <i class="fa-solid fa-circle-info mt-1 fa-lg flex-shrink-0"></i>
    <div>
        <strong>Mode hors-ligne (fallback) :</strong>
        Importez <code>FICHIER_ETAB.xlsx</code> quand l'API StatEduc est inaccessible.
        L'opération est <strong>idempotente</strong> (upsert par <code>CODE_ETABLISSEMENT</code>)
        — relancer l'import plusieurs fois ne crée pas de doublons.
        <br><small class="text-muted">Les colonnes doivent correspondre exactement au format ATLAS_COLLINE ci-dessous.</small>
    </div>
</div>

<!-- ── Formulaire d'import ───────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header fw-semibold bg-white border-bottom">
        <i class="fa-solid fa-upload me-2" style="color:#1d6f42"></i>
        Téléverser le fichier Excel
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/import-excel"
              enctype="multipart/form-data" id="excelForm">

            <?= SecurityHelper::csrfField() ?>

            <div class="mb-3">
                <label for="excel_file" class="form-label fw-semibold">
                    Fichier Excel (.xlsx)
                    <span class="text-danger">*</span>
                </label>
                <input type="file" id="excel_file" name="excel_file"
                       class="form-control" accept=".xlsx,.xls" required>
                <div class="form-text">
                    Fichier : <code>FICHIER_ETAB.xlsx</code> — feuille active (Feuil1).
                    Ligne 1 = en-têtes. Taille max : 10 Mo.
                    <strong><?= defined('PHPSPREADSHEET_AVAILABLE') && PHPSPREADSHEET_AVAILABLE
                        ? '<span class="text-success">PhpSpreadsheet disponible ✔</span>'
                        : '<span class="text-warning">Fallback Python (openpyxl)</span>' ?></strong>
                </div>
            </div>

            <!-- Progression -->
            <div id="progress-zone" class="d-none mb-3">
                <div class="progress" style="height:6px">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         style="width:100%;background:#1d6f42"></div>
                </div>
                <p class="text-muted small mt-1 mb-0">
                    <i class="fa-solid fa-spinner fa-spin me-1"></i>
                    Import en cours — ne fermez pas la page…
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" id="btn-import" class="btn btn-success">
                    <i class="fa-solid fa-file-import me-1"></i>Lancer l'import
                </button>
                <a href="<?= BASE_URL ?>/admin" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-gauge-high me-1"></i>Dashboard admin
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ── Spécifications ATLAS_COLLINE ─────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-header fw-semibold bg-white border-bottom d-flex align-items-center gap-2">
        <i class="fa-solid fa-table-columns text-secondary"></i>
        Format ATLAS_COLLINE — 14 colonnes obligatoires
        <span class="badge bg-success ms-auto">Conforme FICHIER_ETAB.xlsx</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered mb-0" style="font-size:.82rem;">
                <thead class="table-dark">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Colonne Excel (en-tête ligne 1)</th>
                        <th>Champ MySQL</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-light">
                        <td class="text-center fw-bold text-muted">1</td>
                        <td><code class="text-primary">CODE_PROVINCE</code></td>
                        <td><code>code_province</code></td>
                        <td><span class="badge bg-info text-dark">INT</span></td>
                        <td>Code entier de la province</td>
                    </tr>
                    <tr>
                        <td class="text-center fw-bold text-muted">2</td>
                        <td><code class="text-primary">PROVINCE</code></td>
                        <td><code>province</code></td>
                        <td><span class="badge bg-secondary">VARCHAR</span></td>
                        <td>Libellé de la province</td>
                    </tr>
                    <tr class="table-light">
                        <td class="text-center fw-bold text-muted">3</td>
                        <td><code class="text-primary">CODE_COMMUNE</code></td>
                        <td><code>code_commune</code></td>
                        <td><span class="badge bg-info text-dark">INT</span></td>
                        <td>Code entier de la commune</td>
                    </tr>
                    <tr>
                        <td class="text-center fw-bold text-muted">4</td>
                        <td><code class="text-primary">COMMUNE</code></td>
                        <td><code>commune</code></td>
                        <td><span class="badge bg-secondary">VARCHAR</span></td>
                        <td>Libellé de la commune</td>
                    </tr>
                    <tr class="table-light">
                        <td class="text-center fw-bold text-muted">5</td>
                        <td><code class="text-primary">CODE_COLLINE</code></td>
                        <td><code>code_colline</code></td>
                        <td><span class="badge bg-info text-dark">INT</span></td>
                        <td>Code entier de la colline/quartier</td>
                    </tr>
                    <tr>
                        <td class="text-center fw-bold text-muted">6</td>
                        <td><code class="text-primary">COLLINE</code></td>
                        <td><code>colline</code></td>
                        <td><span class="badge bg-secondary">VARCHAR</span></td>
                        <td>Libellé de la colline / quartier</td>
                    </tr>
                    <tr class="table-light">
                        <td class="text-center fw-bold text-muted">7</td>
                        <td><code class="text-primary">CODE_TYPE_SECTEUR_ENS</code></td>
                        <td><code>code_type_secteur_ens</code></td>
                        <td><span class="badge bg-info text-dark">INT</span></td>
                        <td>1=Préscolaire, 2=Fondamental, 3=Post-Fond.Gén., …</td>
                    </tr>
                    <tr>
                        <td class="text-center fw-bold text-muted">8</td>
                        <td><code class="text-primary">SECTEUR_ENS</code></td>
                        <td><code>secteur_ens</code></td>
                        <td><span class="badge bg-secondary">VARCHAR</span></td>
                        <td>Libellé du secteur d'enseignement</td>
                    </tr>
                    <tr class="table-light">
                        <td class="text-center fw-bold text-muted">9</td>
                        <td><code class="text-primary">CODE_TYPE_STATUT_ORG</code></td>
                        <td><code>code_type_statut_org</code></td>
                        <td><span class="badge bg-info text-dark">INT</span></td>
                        <td>Code du statut organisationnel</td>
                    </tr>
                    <tr>
                        <td class="text-center fw-bold text-muted">10</td>
                        <td><code class="text-primary">STATUT</code></td>
                        <td><code>statut_org</code></td>
                        <td><span class="badge bg-secondary">VARCHAR</span></td>
                        <td>Libellé du statut (ex: école maternelle publique)</td>
                    </tr>
                    <tr class="table-light">
                        <td class="text-center fw-bold text-muted">11</td>
                        <td><code class="text-primary">NOM_ETAB</code> <span class="badge bg-danger ms-1">Clé</span></td>
                        <td><code>nom_etablissement</code></td>
                        <td><span class="badge bg-secondary">VARCHAR</span></td>
                        <td>Dénomination officielle de l'établissement</td>
                    </tr>
                    <tr>
                        <td class="text-center fw-bold text-muted">12</td>
                        <td><code class="text-primary">CODE_ETABLISSEMENT</code> <span class="badge bg-danger ms-1">PK</span></td>
                        <td><code>code_etablissement</code></td>
                        <td><span class="badge bg-info text-dark">INT</span></td>
                        <td>Identifiant unique StatEduc — clé upsert</td>
                    </tr>
                    <tr class="table-light">
                        <td class="text-center fw-bold text-muted">13</td>
                        <td><code class="text-primary">CODE_TYPE_MILIEU</code></td>
                        <td><code>code_type_milieu</code></td>
                        <td><span class="badge bg-info text-dark">INT</span></td>
                        <td>1=Urbain, 2=Rural, …</td>
                    </tr>
                    <tr>
                        <td class="text-center fw-bold text-muted">14</td>
                        <td><code class="text-primary">MILIEU</code></td>
                        <td><code>milieu</code></td>
                        <td><span class="badge bg-secondary">VARCHAR</span></td>
                        <td>Libellé du milieu (urbain / rural)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0 small text-muted">
        <i class="fa-solid fa-circle-info me-1"></i>
        L'ordre des colonnes est libre — seuls les <strong>noms des en-têtes</strong> (ligne 1) comptent.
        Les lignes vides sont ignorées automatiquement.
        <code>CODE_ETABLISSEMENT</code> est la clé d'upsert (INSERT … ON DUPLICATE KEY UPDATE).
    </div>
</div>

<script>
document.getElementById('excelForm').addEventListener('submit', function() {
    document.getElementById('btn-import').disabled = true;
    document.getElementById('progress-zone').classList.remove('d-none');
});
// Validation côté client : vérifier qu'un fichier est sélectionné
document.getElementById('excelForm').addEventListener('submit', function(e) {
    const file = document.getElementById('excel_file').files[0];
    if (!file) { e.preventDefault(); alert('Veuillez sélectionner un fichier Excel.'); }
}, true);
</script>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
