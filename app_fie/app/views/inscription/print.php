<?php
/**
 * FIE — Vue : Fiche imprimable (print.php)
 * PHASE 5 : Armoirie en filigrane + Drapeau dans l'en-tête
 * Standalone (pas de layouts), optimisée A4 impression
 */

// Chemins absolus vers les images (pour CSS background-image, on utilise BASE_URL)
$base = BASE_URL;
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Fiche d'inscription — <?= SecurityHelper::e($eleve['iue'] ?? '') ?></title>
<style>
  /* ══════════════════════════════════════════════════════
     FICHE ÉLÈVE — SESSION 17 : UNE SEULE PAGE A4
     Marges réduites, police compacte, bordures visibles
     ══════════════════════════════════════════════════════ */

  /* ── Foundation ── */
  @page { size: A4; margin: 0.9cm 1cm 0.9cm 1cm; }
  * { box-sizing: border-box; }
  body {
    font-family: 'Times New Roman', serif;
    font-size: 10pt;
    color: #000;
    margin: 0;
    position: relative;
  }

  /* ════════════════════════════════
     FILIGRANE — Armoirie du Burundi
     ════════════════════════════════ */
  body::before {
    content: '';
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 460px;
    height: 560px;
    background-image: url('<?= $base ?>/public/images/armoiries_burundi.gif');
    background-repeat: no-repeat;
    background-size: contain;
    background-position: center center;
    opacity: 0.06;
    pointer-events: none;
    z-index: 0;
  }

  /* Tout le contenu au-dessus du filigrane */
  .page-content { position: relative; z-index: 1; }

  /* ════════════════════════════════
     EN-TÊTE GOUVERNEMENTAL (compact)
     ════════════════════════════════ */
  .header-gov {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2.5px solid #CE1126;
    padding-bottom: 6px;
    margin-bottom: 6px;
  }
  .header-gov .logo-left { width: 58px; flex-shrink: 0; }
  .header-gov .logo-left img { width: 58px; height: auto; display: block; }
  .header-gov .logo-right { width: 65px; flex-shrink: 0; text-align: right; }
  .header-gov .logo-right img { width: 65px; height: auto; display: block; margin-left: auto; }
  .header-gov .center-text { text-align: center; flex: 1; padding: 0 8px; }
  .header-gov .center-text .rep {
    font-size: 9.5pt; font-weight: bold; text-transform: uppercase;
    color: #CE1126; margin: 0; letter-spacing: 0.04em;
  }
  .header-gov .center-text .min { font-size: 8.5pt; margin: 1px 0; color: #333; }
  .header-gov .center-text .dgess { font-size: 7.5pt; margin: 1px 0; color: #555; font-style: italic; }
  .header-gov .center-text .title-fie {
    font-size: 11pt; font-weight: bold; text-transform: uppercase;
    color: #CE1126; margin: 3px 0 0; letter-spacing: 0.07em;
  }

  /* Bande drapeau tricolore (fine) */
  .flag-band {
    height: 4px;
    background: linear-gradient(to right,
      #CE1126 0% 33.33%,
      #FFFFFF 33.33% 66.66%,
      #1EB53A 66.66% 100%);
    border: 1px solid #bbb;
    margin-bottom: 6px;
    border-radius: 2px;
  }

  /* ── Badge IUE (compact) ── */
  .iue-badge {
    text-align: center;
    background: #f5f5f5;
    border: 2px solid #CE1126;
    border-radius: 5px;
    padding: 5px 10px;
    margin: 5px 0;
  }
  .iue-badge .iue-label {
    font-size: 8pt; text-transform: uppercase; color: #666; letter-spacing: 0.05em;
  }
  .iue-badge .iue-value {
    font-size: 18pt; font-weight: bold; letter-spacing: 3px;
    color: #CE1126; font-family: 'Courier New', monospace; line-height: 1.1;
  }
  .iue-badge .iue-date { font-size: 8pt; color: #666; margin-top: 1px; }

  /* ── Titres de sections (compacts) ── */
  .section-title {
    font-size: 9.5pt;
    font-weight: bold;
    color: #CE1126;
    border-bottom: 1.5px solid #CE1126;
    margin: 7px 0 3px;
    padding-bottom: 1px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  /* ── Tableau info — BORDURES VISIBLES + LABELS GRAS ── */
  table.info-table {
    width: 100%;
    border-collapse: collapse;
    margin: 3px 0;
    font-size: 9pt;
  }
  table.info-table th {
    /* Label gras, fond gris clair, texte noir, bordure visible */
    background: #f0f0f0;
    color: #000;
    padding: 3px 7px;
    font-size: 8.5pt;
    text-align: left;
    width: 36%;
    font-weight: bold;
    border: 1.5px solid #333;
  }
  table.info-table td {
    border: 1.5px solid #333;
    padding: 3px 7px;
    font-size: 9pt;
    background: #fff;
  }
  table.info-table thead th {
    background: #e8e8e8;
    font-weight: bold;
    border: 1.5px solid #333;
  }

  /* ── Zone signatures + QR (flex côte à côte) ── */
  .sig-qr-zone {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 10px;
    gap: 8px;
  }
  .signatures {
    display: flex;
    justify-content: space-between;
    flex: 1;
    gap: 12px;
  }
  .sig-block {
    text-align: center;
    flex: 1;
  }
  .sig-block .sig-line {
    border-bottom: 1px solid #000;
    margin: 30px 0 4px;
  }
  .sig-block .sig-label { font-size: 8pt; color: #333; }

  /* QR code inline avec signatures */
  .qr-box {
    border: 1.5px solid #bbb;
    border-radius: 5px;
    padding: 5px;
    background: #fff;
    text-align: center;
    width: 105px;
    flex-shrink: 0;
  }
  .qr-box img { display: block; margin: 0 auto 2px; }
  .qr-box .qr-iue { font-size: 6pt; color: #555; font-family: monospace; letter-spacing: .03em; word-break: break-all; }
  .qr-box .qr-hint { font-size: 6pt; color: #888; margin-top: 1px; }

  /* ── Notice légale ── */
  .legal-notice {
    font-size: 7pt;
    color: #888;
    border-top: 1px solid #ddd;
    margin-top: 6px;
    padding-top: 4px;
    line-height: 1.35;
  }

  /* ── Print media ── */
  @media print {
    .no-print { display: none !important; }
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
</head>
<body>

<div class="page-content">

  <!-- ─── En-tête gouvernemental avec armoirie + drapeau ──────────────── -->
  <div class="header-gov">
    <!-- Armoirie gauche -->
    <div class="logo-left">
      <img src="<?= $base ?>/public/images/armoiries_burundi.gif"
           alt="Armoiries du Burundi">
    </div>

    <!-- Texte centré -->
    <div class="center-text">
      <p class="rep">République du Burundi</p>
      <p class="min">Ministère de l'Éducation Nationale et de la Recherche Scientifique</p>
      <p class="dgess">Direction Générale des Études et des Statistiques Scolaires (DGESS)</p>
      <p class="title-fie">Fiche d'Inscription — FIE</p>
    </div>

    <!-- Drapeau droit -->
    <div class="logo-right">
      <img src="<?= $base ?>/public/images/drapeau_burundi.gif"
           alt="Drapeau du Burundi">
    </div>
  </div>

  <!-- Bande tricolore décorative -->
  <div class="flag-band" aria-hidden="true"></div>

  <!-- ─── Badge IUE ──────────────────────────────────────────────────── -->
  <div class="iue-badge">
    <div class="iue-label">Identifiant Unique de l'Élève (IUE)</div>
    <div class="iue-value"><?= SecurityHelper::e($eleve['iue']) ?></div>
    <div class="iue-date">
      Émis le <?= SecurityHelper::e(date('d/m/Y', strtotime($eleve['created_at']))) ?>
    </div>
  </div>

  <!-- ─── 1. État civil ─────────────────────────────────────────────── -->
  <div class="section-title">1. État civil</div>
  <table class="info-table">
    <tr><th>Nom</th><td><?= SecurityHelper::e($eleve['nom']) ?></td></tr>
    <tr><th>Prénom(s)</th><td><?= SecurityHelper::e($eleve['prenoms']) ?></td></tr>
    <tr><th>Sexe</th><td><?= $eleve['sexe'] === 'M' ? 'Masculin' : 'Féminin' ?></td></tr>
    <tr><th>Date de naissance</th>
        <td><?= SecurityHelper::e(date('d/m/Y', strtotime($eleve['date_naissance']))) ?></td></tr>
    <tr><th>Lieu de naissance</th><td><?= SecurityHelper::e($eleve['lieu_naissance'] ?? '—') ?></td></tr>
    <tr><th>Province de naissance</th><td><?= SecurityHelper::e($eleve['province_naissance'] ?? '—') ?></td></tr>
    <tr><th>Nationalité</th><td><?= SecurityHelper::e($eleve['nationalite'] ?? '—') ?></td></tr>
  </table>

  <!-- ─── 2. Acte de naissance ─────────────────────────────────────── -->
  <?php if (!empty($eleve['numero_acte_naissance'])): ?>
  <div class="section-title">2. Acte de naissance</div>
  <table class="info-table">
    <tr><th>N° acte</th><td><?= SecurityHelper::e($eleve['numero_acte_naissance']) ?></td></tr>
    <tr><th>Date</th><td><?= SecurityHelper::e($eleve['date_acte_naissance'] ?? '—') ?></td></tr>
    <tr><th>Commune</th><td><?= SecurityHelper::e($eleve['commune_acte'] ?? '—') ?></td></tr>
  </table>
  <?php endif; ?>

  <!-- ─── 3. Inscription en cours ──────────────────────────────────── -->
  <?php if (!empty($inscriptions)): $insc = $inscriptions[0]; ?>
  <div class="section-title">3. Inscription scolaire <?= SecurityHelper::e($insc['code_type_annee'] ?? '') ?></div>
  <table class="info-table">
    <tr><th>Établissement</th><td><?= SecurityHelper::e($insc['nom_etablissement'] ?? '—') ?></td></tr>
    <tr><th>Localisation</th><td><?= SecurityHelper::e($insc['chaine_localisation'] ?? '—') ?></td></tr>
    <tr><th>Sous-secteur</th><td><?= SecurityHelper::e($insc['libelle_secteur'] ?? $insc['code_type_secteur_ens'] ?? '—') ?></td></tr>
    <tr><th>Niveau</th><td><?= SecurityHelper::e($insc['libelle_niveau'] ?? $insc['code_type_niveau'] ?? '—') ?></td></tr>
    <tr><th>Classe</th><td><?= SecurityHelper::e($insc['numero_classe'] ?? '—') ?></td></tr>
    <tr><th>Date d'inscription</th>
        <td><?= !empty($insc['date_inscription']) ? SecurityHelper::e(date('d/m/Y', strtotime($insc['date_inscription']))) : '—' ?></td></tr>
    <tr><th>N° matricule interne</th><td><?= SecurityHelper::e($insc['matricule_etab'] ?? '—') ?></td></tr>
  </table>
  <?php endif; ?>

  <!-- ─── 4. Historique inscriptions ──────────────────────────────── -->
  <?php if (!empty($inscriptions) && count($inscriptions) > 1): ?>
  <div class="section-title">4. Historique des inscriptions</div>
  <table class="info-table">
    <thead>
      <tr>
        <th style="width:20%">Année</th>
        <th style="width:50%">Établissement</th>
        <th style="width:30%">Niveau / Classe</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($inscriptions as $h): ?>
    <tr>
      <td><?= SecurityHelper::e($h['code_type_annee'] ?? '—') ?></td>
      <td><?= SecurityHelper::e($h['nom_etablissement'] ?? '—') ?></td>
      <td><?= SecurityHelper::e($h['libelle_niveau'] ?? $h['code_type_niveau'] ?? '—') ?>
          <?= !empty($h['numero_classe']) ? ' — Cl. '.SecurityHelper::e($h['numero_classe']) : '' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <!-- ─── Signatures + QR code (côte à côte) ────────────────────── -->
  <?php
    $iue_val = SecurityHelper::e($eleve['iue'] ?? '');
    $qr_url  = rtrim(BASE_URL, '/') . '/eleve/' . urlencode($eleve['iue'] ?? '');
    $qr_api  = 'https://api.qrserver.com/v1/create-qr-code/?size=90x90&data='
               . urlencode($qr_url) . '&ecc=M&margin=3';
  ?>
  <div class="sig-qr-zone">
    <!-- Blocs de signature (gauche + centre) -->
    <div class="signatures">
      <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-label">Signature du parent / tuteur</div>
      </div>
      <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-label">Signature et cachet du chef d'établissement</div>
      </div>
    </div>
    <!-- QR code (droite) -->
    <div class="qr-box">
      <img src="<?= $qr_api ?>" alt="QR IUE <?= $iue_val ?>" width="90" height="90">
      <div class="qr-iue"><?= $iue_val ?></div>
      <div class="qr-hint">Scanner pour vérifier</div>
    </div>
  </div>

  <!-- ─── Notice légale ────────────────────────────────────────────── -->
  <div class="legal-notice">
    Document officiel généré par le Système d'Information de Gestion de l'Éducation (SIGE) — Burundi.
    Conformément à la loi n°1/03 de 2026 sur la protection des données personnelles,
    les informations figurant sur cette fiche sont strictement confidentielles et réservées à l'usage scolaire.
    Imprimé le <?= date('d/m/Y à H:i') ?> — IUE : <strong><?= SecurityHelper::e($eleve['iue']) ?></strong>
    — DGESS / MENERS · Bujumbura
  </div>

</div><!-- /.page-content -->

<script>
  window.onload = function() { window.print(); };
</script>
</body>
</html>
