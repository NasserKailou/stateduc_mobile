<?php
/**
 * FIE — Vue : Fiche imprimable (print.php)
 * CORRECTION Phase 2 : standalone (pas de layouts), optimisée A4 impression
 * Aucun require FIE_VIEWS_PATH — utilise BASE_PATH
 */
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Fiche d'inscription — <?= SecurityHelper::e($eleve['iue'] ?? '') ?></title>
<style>
  /* Fiche imprimable — optimisée pour A4 */
  @page { size: A4; margin: 1.5cm; }
  * { box-sizing: border-box; }
  body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #000; margin: 0; }
  .header-gov { display: flex; justify-content: space-between; align-items: center;
                border-bottom: 3px solid #CE1126; padding-bottom: 8px; margin-bottom: 12px; }
  .header-gov .logo-left, .header-gov .logo-right { width: 80px; }
  .header-gov .center-text { text-align: center; flex: 1; }
  .header-gov h1 { font-size: 13pt; text-transform: uppercase; margin: 0; color: #CE1126; }
  .header-gov h2 { font-size: 11pt; margin: 2px 0; }
  .header-gov h3 { font-size: 10pt; margin: 2px 0; color: #1EB53A; }
  .iue-badge { text-align: center; background: #f5f5f5; border: 2px solid #CE1126;
               border-radius: 6px; padding: 10px; margin: 12px 0; }
  .iue-badge .iue-label { font-size: 10pt; text-transform: uppercase; color: #666; }
  .iue-badge .iue-value { font-size: 22pt; font-weight: bold; letter-spacing: 3px; color: #CE1126; }
  table.info-table { width: 100%; border-collapse: collapse; margin: 8px 0; }
  table.info-table th { background: #CE1126; color: #fff; padding: 5px 8px;
                        font-size: 10pt; text-align: left; width: 35%; }
  table.info-table td { border: 1px solid #ddd; padding: 5px 8px; font-size: 11pt; }
  table.info-table tr:nth-child(even) td { background: #fafafa; }
  .section-title { font-size: 12pt; font-weight: bold; color: #CE1126; border-bottom: 1px solid #CE1126;
                   margin: 14px 0 6px; padding-bottom: 2px; text-transform: uppercase; }
  .signatures { display: flex; justify-content: space-between; margin-top: 30px; }
  .sig-block { text-align: center; width: 45%; }
  .sig-block .sig-line { border-bottom: 1px solid #000; margin: 40px 0 5px; }
  .legal-notice { font-size: 8pt; color: #888; border-top: 1px solid #ddd;
                  margin-top: 20px; padding-top: 8px; }
  @media print {
    body { font-size: 11pt; }
    .no-print { display: none !important; }
  }
</style>
</head>
<body>

<!-- Entête gouvernementale -->
<div class="header-gov">
  <div class="center-text">
    <h2>RÉPUBLIQUE DU BURUNDI</h2>
    <h3>Ministère de l'Éducation Nationale et de la Recherche Scientifique</h3>
    <h1>FICHE D'INSCRIPTION — FIE</h1>
    <h3>Fichier Informatisé des Élèves</h3>
  </div>
</div>

<!-- Badge IUE -->
<div class="iue-badge">
  <div class="iue-label">Identifiant Unique de l'Élève (IUE)</div>
  <div class="iue-value"><?= SecurityHelper::e($eleve['iue']) ?></div>
  <div style="font-size:9pt; color:#666;">
    Généré le <?= SecurityHelper::e(date('d/m/Y', strtotime($eleve['created_at']))) ?>
  </div>
</div>

<!-- État civil -->
<div class="section-title">1. État civil</div>
<table class="info-table">
  <tr><th>Nom</th><td><?= SecurityHelper::e($eleve['nom']) ?></td></tr>
  <tr><th>Prénom(s)</th><td><?= SecurityHelper::e($eleve['prenoms']) ?></td></tr>
  <tr><th>Sexe</th><td><?= $eleve['sexe'] === 'M' ? 'Masculin' : 'Féminin' ?></td></tr>
  <tr><th>Date de naissance</th>
      <td><?= SecurityHelper::e(date('d/m/Y', strtotime($eleve['date_naissance']))) ?></td></tr>
  <tr><th>Lieu de naissance</th><td><?= SecurityHelper::e($eleve['lieu_naissance'] ?? '—') ?></td></tr>
  <tr><th>Province de naissance</th><td><?= SecurityHelper::e($eleve['province_naissance'] ?? '—') ?></td></tr>
  <tr><th>Nationalité</th><td><?= SecurityHelper::e($eleve['nationalite']) ?></td></tr>
</table>

<!-- Acte de naissance -->
<?php if ($eleve['numero_acte_naissance']): ?>
<div class="section-title">2. Acte de naissance</div>
<table class="info-table">
  <tr><th>N° acte</th><td><?= SecurityHelper::e($eleve['numero_acte_naissance']) ?></td></tr>
  <tr><th>Date</th><td><?= SecurityHelper::e($eleve['date_acte_naissance'] ?? '—') ?></td></tr>
  <tr><th>Commune</th><td><?= SecurityHelper::e($eleve['commune_acte'] ?? '—') ?></td></tr>
</table>
<?php endif; ?>

<!-- Inscription en cours -->
<?php if (!empty($inscriptions)): $insc = $inscriptions[0]; ?>
<div class="section-title">3. Inscription <?= SecurityHelper::e($insc['code_type_annee']) ?></div>
<table class="info-table">
  <tr><th>Établissement</th><td><?= SecurityHelper::e($insc['nom_etablissement'] ?? '') ?></td></tr>
  <tr><th>Localisation</th><td><?= SecurityHelper::e($insc['chaine_localisation'] ?? '') ?></td></tr>
  <tr><th>Sous-secteur</th><td><?= SecurityHelper::e($insc['libelle_secteur'] ?? $insc['code_type_secteur_ens']) ?></td></tr>
  <tr><th>Niveau</th><td><?= SecurityHelper::e($insc['libelle_niveau'] ?? $insc['code_type_niveau']) ?></td></tr>
  <tr><th>Classe</th><td><?= SecurityHelper::e($insc['numero_classe'] ?? '—') ?></td></tr>
  <tr><th>Date d'inscription</th>
      <td><?= SecurityHelper::e(date('d/m/Y', strtotime($insc['date_inscription']))) ?></td></tr>
  <tr><th>N° matricule interne</th><td><?= SecurityHelper::e($insc['matricule_etab'] ?? '—') ?></td></tr>
</table>
<?php endif; ?>

<!-- Signatures -->
<div class="signatures">
  <div class="sig-block">
    <div class="sig-line"></div>
    <div>Signature du parent/tuteur</div>
  </div>
  <div class="sig-block">
    <div class="sig-line"></div>
    <div>Signature du chef d'établissement<br>Cachet</div>
  </div>
</div>

<!-- Notice légale -->
<div class="legal-notice">
  Document généré par le Système d'Information de Gestion de l'Éducation (SIGE) — Burundi.
  Conformément à la loi n°1/03 de 2026 sur la protection des données personnelles,
  les informations figurant sur cette fiche sont strictement confidentielles et réservées à l'usage scolaire.
  Imprimé le <?= date('d/m/Y à H:i') ?> | IUE : <?= SecurityHelper::e($eleve['iue']) ?>
</div>

<script>
  window.onload = function() { window.print(); };
</script>
</body>
</html>
