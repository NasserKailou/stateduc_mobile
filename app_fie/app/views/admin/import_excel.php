<?php
/**
 * FIE — Vue : Import Excel des établissements (fallback hors-ligne)
 */
use App\Services\SecurityHelper;
require __DIR__ . '/../layouts/header.php';
?>
<nav aria-label="Fil d'Ariane" class="fie-breadcrumb">
    <ol>
        <li><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li><a href="<?= BASE_URL ?>/admin">Administration</a></li>
        <li aria-current="page">Import Excel</li>
    </ol>
</nav>

<div class="fie-page-header">
    <h1 class="fie-page-title">Import Excel des établissements</h1>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="fie-alert fie-alert--error">
        <?= SecurityHelper::e($_SESSION['flash_error']) ?>
        <?php unset($_SESSION['flash_error']); ?>
    </div>
<?php endif; ?>

<div class="fie-alert fie-alert--info">
    <div class="fie-alert__body">
        <div class="fie-alert__title">Fonctionnalité de secours</div>
        <p>Cet import est un <strong>mode dégradé</strong> à utiliser uniquement si l'API StatEduc
        n'est pas disponible. Les établissements déjà issus de l'API StatEduc
        (<code>source = api_stateduc</code>) ne seront pas écrasés par l'import Excel.</p>
        <p>Colonnes attendues dans la feuille <code>etab</code> :
        <code>CODE_ETABLISSEMENT</code>, <code>NOM_ETABLISSEMENT</code>, <code>PROVINCE</code>,
        <code>COMMUNE</code>, <code>ZONE</code>, <code>COLLINE</code>,
        <code>CODE_TYPE_MILIEU</code>, <code>CODE_TYPE_SECTEUR_ENS</code>,
        <code>CODE_TYPE_STATUT_ORG</code>.</p>
    </div>
</div>

<div class="fie-card">
    <h2 class="fie-card__title">Sélectionner le fichier Excel</h2>
    <form method="post" action="<?= BASE_URL ?>/admin/import-excel"
          enctype="multipart/form-data" class="fie-form">
        <?= SecurityHelper::csrfField() ?>
        <div class="fie-form-group">
            <label for="excel_file" class="fie-label fie-label--required">
                Fichier Excel (.xlsx ou .xls)
            </label>
            <input type="file" id="excel_file" name="excel_file"
                   accept=".xlsx,.xls" class="fie-input" required>
            <span class="fie-hint">Taille maximale : <?= ini_get('upload_max_filesize') ?></span>
        </div>
        <div class="fie-btn-group">
            <button type="submit" class="fie-btn fie-btn--primary">
                Importer
            </button>
            <a href="<?= BASE_URL ?>/admin/sync" class="fie-btn fie-btn--ghost">
                Annuler
            </a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
