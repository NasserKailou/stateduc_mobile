<?php
/**
 * Vue : Fiche détail de l'élève
 * Variables : $eleve (array), $inscriptions (array), $success (bool)
 */
$title = 'Fiche Élève — ' . SecurityHelper::e($eleve['iue']);
require FIE_VIEWS_PATH . 'layouts/header.php';
?>

<div class="fie-page-header">
  <h1>📋 Fiche Élève</h1>
  <div class="fie-breadcrumb">Accueil &rsaquo; Inscriptions &rsaquo; Fiche</div>
  <div class="fie-page-actions">
    <a href="<?= FIE_BASE_URL ?>inscription/print/<?= urlencode($eleve['iue']) ?>"
       target="_blank" class="fie-btn fie-btn--secondary">🖨 Imprimer</a>
    <a href="<?= FIE_BASE_URL ?>inscription/new" class="fie-btn fie-btn--primary">+ Nouvelle inscription</a>
  </div>
</div>

<?php if ($success): ?>
<div class="fie-notice success">
  ✅ Inscription enregistrée avec succès. L'IUE a été généré.
</div>
<?php endif; ?>

<!-- Carte IUE -->
<div class="fie-iue-card">
  <div class="fie-iue-label">Identifiant Unique de l'Élève (IUE)</div>
  <div class="fie-iue-value"><?= SecurityHelper::e($eleve['iue']) ?></div>
  <div class="fie-iue-hint">
    Cet identifiant est permanent et suit l'élève tout au long de sa scolarité,
    indépendamment des transferts ou des niveaux d'études.
  </div>
  <?php if (!IueGenerator::validate($eleve['iue'])): ?>
  <div class="fie-iue-warning">⚠ IUE invalide (chiffres de contrôle incorrects)</div>
  <?php endif; ?>
</div>

<div class="fie-detail-grid">
  <!-- ── État civil ─────────────────────────────────────────────────────── -->
  <section class="fie-detail-section">
    <h2>État civil</h2>
    <table class="fie-detail-table">
      <tr><th>Nom</th><td><?= SecurityHelper::e($eleve['nom']) ?></td></tr>
      <tr><th>Prénom(s)</th><td><?= SecurityHelper::e($eleve['prenoms']) ?></td></tr>
      <tr><th>Sexe</th><td><?= $eleve['sexe'] === 'M' ? 'Masculin' : 'Féminin' ?></td></tr>
      <tr><th>Date de naissance</th>
          <td><?= SecurityHelper::e(date('d/m/Y', strtotime($eleve['date_naissance']))) ?></td></tr>
      <tr><th>Lieu de naissance</th><td><?= SecurityHelper::e($eleve['lieu_naissance'] ?? '—') ?></td></tr>
      <tr><th>Province de naissance</th><td><?= SecurityHelper::e($eleve['province_naissance'] ?? '—') ?></td></tr>
      <tr><th>Nationalité</th><td><?= SecurityHelper::e($eleve['nationalite']) ?></td></tr>
    </table>
    <?php if ($eleve['doublon_suspect']): ?>
    <div class="fie-notice warning">
      ⚠ Doublon potentiel signalé.
      <a href="<?= FIE_BASE_URL ?>inscription/detail/<?= urlencode($eleve['doublon_iue_ref'] ?? '') ?>">
        Voir l'IUE de référence : <?= SecurityHelper::e($eleve['doublon_iue_ref'] ?? '') ?>
      </a>
    </div>
    <?php endif; ?>
  </section>

  <!-- ── Tuteur ─────────────────────────────────────────────────────────── -->
  <section class="fie-detail-section">
    <h2>Tuteur / Parents</h2>
    <table class="fie-detail-table">
      <tr><th>Nom du père</th><td><?= SecurityHelper::e($eleve['nom_pere'] ?? '—') ?></td></tr>
      <tr><th>Nom de la mère</th><td><?= SecurityHelper::e($eleve['nom_mere'] ?? '—') ?></td></tr>
      <tr><th>Tuteur légal</th><td><?= SecurityHelper::e($eleve['nom_tuteur'] ?? '—') ?></td></tr>
      <tr><th>Téléphone tuteur</th><td><?= SecurityHelper::e($eleve['telephone_tuteur'] ?? '—') ?></td></tr>
    </table>
  </section>
</div>

<!-- ── Historique des inscriptions ──────────────────────────────────────── -->
<section class="fie-detail-section fie-detail-section--full">
  <h2>Historique des inscriptions</h2>
  <?php if (empty($inscriptions)): ?>
  <p class="fie-empty">Aucune inscription enregistrée.</p>
  <?php else: ?>
  <div class="fie-table-scroll">
    <table class="fie-table">
      <thead>
        <tr>
          <th>Année</th>
          <th>Établissement</th>
          <th>Localisation</th>
          <th>Sous-secteur</th>
          <th>Niveau</th>
          <th>Section</th>
          <th>Classe</th>
          <th>Statut</th>
          <th>Date insc.</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($inscriptions as $ins): ?>
        <tr>
          <td><?= SecurityHelper::e($ins['code_type_annee']) ?></td>
          <td><?= SecurityHelper::e($ins['nom_etablissement'] ?? '') ?></td>
          <td><small><?= SecurityHelper::e($ins['chaine_localisation'] ?? '') ?></small></td>
          <td><?= SecurityHelper::e($ins['libelle_secteur'] ?? $ins['code_type_secteur_ens']) ?></td>
          <td><?= SecurityHelper::e($ins['libelle_niveau']  ?? $ins['code_type_niveau']) ?></td>
          <td><?= SecurityHelper::e($ins['code_type_section']) ?></td>
          <td><?= SecurityHelper::e($ins['numero_classe'] ?? '—') ?></td>
          <td><span class="fie-badge fie-badge--<?= SecurityHelper::e($ins['statut']) ?>">
            <?= SecurityHelper::e($ins['statut']) ?></span></td>
          <td><?= SecurityHelper::e(date('d/m/Y', strtotime($ins['date_inscription']))) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>

<?php require FIE_VIEWS_PATH . 'layouts/footer.php'; ?>
