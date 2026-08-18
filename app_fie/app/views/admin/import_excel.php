<?php
/**
 * FIE — Vue : Import Excel des établissements
 * Bootstrap 5 + Font Awesome — Charte Burundi
 */
$page_title  = $page_title  ?? 'Import Excel — Administration FIE';
$active_menu = $active_menu ?? 'admin';
require BASE_PATH . '/app/views/layouts/app_layout.php';
?>

<!-- ── Fil d'Ariane ─────────────────────────────────────────────────────── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Administration</a></li>
        <li class="breadcrumb-item active">Import Excel</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="fa-solid fa-file-excel me-2" style="color:#20c997"></i>
        Import Excel des établissements
    </h1>
</div>

<!-- ── Info ─────────────────────────────────────────────────────────────── -->
<div class="alert alert-info d-flex align-items-start gap-3 mb-4">
    <i class="fa-solid fa-circle-info mt-1 fa-lg flex-shrink-0"></i>
    <div>
        <strong>Fallback hors-ligne :</strong>
        Quand l'API StatEduc est inaccessible, importez le fichier
        <code>infos_etab_bu.xlsx</code> (feuille <code>etab</code>) pour alimenter
        la table miroir locale <code>etablissements_miroir</code>.
        L'opération est <strong>idempotente</strong> (upsert par CODE_ETABLISSEMENT).
    </div>
</div>

<!-- ── Formulaire d'import ───────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-header fw-semibold bg-white border-bottom">
        <i class="fa-solid fa-upload me-2" style="color:var(--fie-green)"></i>
        Téléverser le fichier Excel
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/import-excel"
              enctype="multipart/form-data" id="excelForm">

            <?= SecurityHelper::csrfField() ?>

            <div class="mb-3">
                <label for="excel_file" class="form-label fw-semibold">
                    Fichier Excel (.xlsx ou .xls)
                    <span class="text-danger">*</span>
                </label>
                <input type="file" id="excel_file" name="excel_file"
                       class="form-control" accept=".xlsx,.xls" required>
                <div class="form-text">
                    Format attendu : fichier <code>infos_etab_bu.xlsx</code>, feuille <code>etab</code>.
                    Taille max : 10 Mo.
                </div>
            </div>

            <!-- Barre de progression (masquée par défaut) -->
            <div id="progress-zone" class="d-none mb-3">
                <div class="progress" style="height:6px">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         style="width:100%;background:var(--fie-green)"></div>
                </div>
                <p class="text-muted small mt-1">
                    <i class="fa-solid fa-spinner fa-spin me-1"></i>
                    Import en cours, veuillez patienter…
                </p>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" id="btn-import" class="btn btn-success">
                    <i class="fa-solid fa-file-import me-1"></i>Lancer l'import
                </button>
                <a href="<?= BASE_URL ?>/admin/sync" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>Retour à la synchronisation
                </a>
            </div>

        </form>
    </div>
</div>

<!-- ── Spécifications du fichier ─────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header fw-semibold bg-white border-bottom">
        <i class="fa-solid fa-table me-2 text-secondary"></i>
        Structure attendue de la feuille <code>etab</code>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Colonne</th>
                        <th>Champ BD</th>
                        <th>Type</th>
                        <th>Obligatoire</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>CODE_ETABLISSEMENT</code></td>
                        <td><code>code_etablissement</code></td>
                        <td>VARCHAR(20)</td>
                        <td><span class="badge bg-danger">Oui</span></td>
                        <td>Identifiant unique StatEduc (clé upsert)</td>
                    </tr>
                    <tr>
                        <td><code>NOM_ETABLISSEMENT</code></td>
                        <td><code>nom_etablissement</code></td>
                        <td>VARCHAR(300)</td>
                        <td><span class="badge bg-danger">Oui</span></td>
                        <td>Dénomination officielle</td>
                    </tr>
                    <tr>
                        <td><code>PROVINCE</code></td>
                        <td><code>province</code></td>
                        <td>VARCHAR(100)</td>
                        <td><span class="badge bg-danger">Oui</span></td>
                        <td>Province administrative</td>
                    </tr>
                    <tr>
                        <td><code>COMMUNE</code></td>
                        <td><code>commune</code></td>
                        <td>VARCHAR(100)</td>
                        <td><span class="badge bg-secondary">Non</span></td>
                        <td>Commune</td>
                    </tr>
                    <tr>
                        <td><code>ZONE</code></td>
                        <td><code>zone</code></td>
                        <td>VARCHAR(100)</td>
                        <td><span class="badge bg-secondary">Non</span></td>
                        <td>Zone</td>
                    </tr>
                    <tr>
                        <td><code>COLLINE</code></td>
                        <td><code>colline</code></td>
                        <td>VARCHAR(100)</td>
                        <td><span class="badge bg-secondary">Non</span></td>
                        <td>Colline / Quartier</td>
                    </tr>
                    <tr>
                        <td><code>CODE_TYPE_SECTEUR_ENS</code></td>
                        <td><code>code_type_secteur_ens</code></td>
                        <td>INT</td>
                        <td><span class="badge bg-secondary">Non</span></td>
                        <td>1=Préscolaire, 2=Primaire, 3=Sec.gén., etc.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('excelForm').addEventListener('submit', function() {
    document.getElementById('btn-import').disabled = true;
    document.getElementById('progress-zone').classList.remove('d-none');
});
</script>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
