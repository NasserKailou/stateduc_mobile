<?php
/**
 * FIE — Vue : Import liste élèves depuis Excel/CSV
 * Génère un IUE pour chaque élève importé.
 * GET/POST /admin/import-eleves
 */
$page_title  = $page_title  ?? 'Import liste élèves — FIE';
$active_menu = $active_menu ?? 'admin_import_eleves';
$ecoles      = $ecoles      ?? [];
$annees      = $annees      ?? [];
require BASE_PATH . '/app/views/layouts/app_layout.php';
$csrf = SecurityHelper::csrfToken();
$msgs = $_SESSION['fie_import_eleves_messages'] ?? [];
unset($_SESSION['fie_import_eleves_messages']);
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Administration</a></li>
    <li class="breadcrumb-item active">Import liste élèves</li>
  </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h1 class="h4 fw-bold mb-0">
    <i class="fa-solid fa-file-excel me-2 text-success"></i>
    Import liste d'élèves — Génération IUE
  </h1>
  <a href="<?= BASE_URL ?>/admin/import-eleves/modele" class="btn btn-success btn-sm">
    <i class="fa-solid fa-download me-1"></i>Télécharger le modèle CSV
  </a>
</div>

<?php if (!empty($_SESSION['fie_flash_success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
  <i class="fa-solid fa-circle-check me-2"></i><?= SecurityHelper::e($_SESSION['fie_flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['fie_flash_success']); endif; ?>

<?php if (!empty($_SESSION['fie_flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <i class="fa-solid fa-triangle-exclamation me-2"></i><?= SecurityHelper::e($_SESSION['fie_flash_error']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['fie_flash_error']); endif; ?>

<?php if (!empty($msgs)): ?>
<div class="alert alert-warning">
  <h6 class="fw-bold"><i class="fa-solid fa-list me-2"></i>Détail des lignes ignorées/erreurs :</h6>
  <ul class="mb-0 small">
    <?php foreach ($msgs as $m): ?><li><?= SecurityHelper::e($m) ?></li><?php endforeach; ?>
    <?php if (count($msgs) >= 20): ?><li class="text-muted">(liste tronquée à 20 messages)</li><?php endif; ?>
  </ul>
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- ── Formulaire Upload ─────────────────────────────────────────────────── -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="fa-solid fa-upload me-2 text-primary"></i>Importer un fichier
      </div>
      <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/import-eleves" enctype="multipart/form-data">
          <input type="hidden" name="<?= FIE_CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">

          <div class="mb-3">
            <label for="eleves_file" class="form-label fw-semibold">
              Fichier Excel / CSV <span class="text-danger">*</span>
            </label>
            <input type="file" id="eleves_file" name="eleves_file"
                   class="form-control" accept=".xlsx,.xls,.csv" required>
            <div class="form-text">
              Formats acceptés : <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong>
              (séparateur <code>;</code> ou <code>,</code>, encodage UTF-8).
            </div>
          </div>

          <div class="alert alert-info small mb-3">
            <i class="fa-solid fa-circle-info me-2"></i>
            <strong>Colonnes obligatoires :</strong>
            <code>nom</code>, <code>prenoms</code>, <code>sexe</code> (M/F),
            <code>date_naissance</code> (AAAA-MM-JJ),
            <code>code_etablissement</code>, <code>code_type_annee</code>,
            <code>code_type_secteur_ens</code>, <code>code_type_niveau</code>.<br>
            <strong>Colonnes facultatives :</strong>
            <code>lieu_naissance</code>, <code>province_naissance</code>,
            <code>nationalite</code> (ex: BDI),
            <code>nom_pere</code>, <code>nom_mere</code>, <code>nom_tuteur</code>,
            <code>telephone_tuteur</code>, <code>code_type_section</code>,
            <code>numero_classe</code>, <code>date_inscription</code>.
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-file-import me-2"></i>Importer et générer les IUE
            </button>
            <a href="<?= BASE_URL ?>/admin/import-eleves/modele" class="btn btn-outline-success">
              <i class="fa-solid fa-download me-1"></i>Modèle CSV
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ── Aide références ──────────────────────────────────────────────────── -->
  <div class="col-lg-5">

    <!-- Établissements -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold small">
        <i class="fa-solid fa-school me-2 text-info"></i>Codes établissements (premiers 20)
      </div>
      <div class="card-body p-0">
        <div class="table-responsive" style="max-height:180px;overflow-y:auto;">
          <table class="table table-sm table-hover mb-0 small">
            <thead class="table-light sticky-top">
              <tr><th>Code</th><th>Établissement</th></tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($ecoles, 0, 20) as $ec): ?>
              <tr>
                <td><code><?= (int)$ec['code_etablissement'] ?></code></td>
                <td><?= SecurityHelper::e($ec['nom_etablissement']) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (count($ecoles) > 20): ?>
              <tr><td colspan="2" class="text-muted text-center"><?= count($ecoles) - 20 ?> autres établissements…</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Années scolaires -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold small">
        <i class="fa-solid fa-calendar me-2 text-warning"></i>Codes années scolaires
      </div>
      <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0 small">
          <thead class="table-light">
            <tr><th>Code</th><th>Libellé</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($annees as $an): ?>
            <tr>
              <td><code><?= (int)$an['code_type_annee'] ?></code></td>
              <td><?= SecurityHelper::e($an['libelle']) ?></td>
              <td><?php if ($an['actif']): ?><span class="badge bg-success">courante</span><?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Rappel -->
    <div class="card border-0 shadow-sm border-start border-4 border-primary">
      <div class="card-body small text-muted">
        <p class="mb-1"><i class="fa-solid fa-lightbulb me-2 text-warning"></i>
        <strong>Conseils :</strong></p>
        <ul class="mb-0 ps-3">
          <li>Chaque ligne = un élève.</li>
          <li>Un IUE unique est généré pour chaque élève importé.</li>
          <li>Les doublons (même élève/année/établissement) sont ignorés automatiquement.</li>
          <li>Utilisez le modèle CSV comme point de départ.</li>
          <li>La colonne <code>sexe</code> doit contenir <code>M</code> ou <code>F</code>.</li>
          <li>La colonne <code>nationalite</code> doit contenir un code ISO 3 lettres (ex: <code>BDI</code>).</li>
        </ul>
      </div>
    </div>

  </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
