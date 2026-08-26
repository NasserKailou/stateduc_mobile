<?php
/**
 * FIE — Vue publique profil élève (accessible via QR code)
 * Affiche les données non-sensibles : IUE, nom, établissement actif.
 * Accessible sans connexion — GET /eleve/:iue
 */
$base = BASE_URL;
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title ?? 'Profil élève — FIE', ENT_QUOTES, 'UTF-8') ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
      crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
<style>
:root {
    --blue: #1a56db; --blue-dk: #1343a8; --blue-light: #eff6ff;
    --red: #CE1126; --green: #1EB53A;
    --gray-50: #f9fafb; --gray-100: #f3f4f6; --gray-200: #e5e7eb;
    --gray-600: #4b5563; --gray-900: #111827;
}
*, *::before, *::after { box-sizing: border-box; }
body {
    min-height: 100vh;
    background: linear-gradient(135deg, #e8f0fe 0%, #f0f7ff 50%, #f5f9ff 100%);
    font-family: 'Open Sans', system-ui, sans-serif;
    color: var(--gray-900);
    display: flex; flex-direction: column;
}
.top-bar {
    background: #0f2749;
    border-bottom: 3px solid var(--blue);
    padding: 8px 0;
}
.top-bar-inner {
    max-width: 700px; margin: 0 auto; padding: 0 1.25rem;
    display: flex; align-items: center; justify-content: space-between;
}
.top-bar .txt { color: rgba(255,255,255,.65); font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; }
.top-bar .txt strong { color: #fff; display: block; font-size: .78rem; }
.tri { display: flex; height: 16px; width: 48px; border-radius: 3px; overflow: hidden; flex-shrink: 0; }
.tri span { flex: 1; }
.main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
.card-profil {
    width: 100%; max-width: 520px;
    background: #fff; border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(0,0,0,.10), 0 4px 16px rgba(0,0,0,.06);
    overflow: hidden;
}
.card-header-profil {
    background: linear-gradient(135deg, var(--blue-dk) 0%, var(--blue) 100%);
    padding: 2rem 2rem 1.5rem;
    text-align: center;
    position: relative;
}
.card-header-profil img {
    width: 60px; height: auto;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,.3));
    margin-bottom: .75rem;
}
.iue-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(255,255,255,.15);
    color: #fff; font-family: 'Poppins', sans-serif;
    font-size: .78rem; font-weight: 600; letter-spacing: .08em;
    padding: .35rem .85rem; border-radius: 50px;
    border: 1px solid rgba(255,255,255,.25);
    margin-bottom: .75rem;
}
.student-name {
    color: #fff; font-family: 'Poppins', sans-serif;
    font-size: 1.35rem; font-weight: 700;
    margin: 0; line-height: 1.2;
}
.student-sub {
    color: rgba(255,255,255,.70); font-size: .82rem; margin-top: .25rem;
}
.flag-band {
    height: 4px;
    background: linear-gradient(to right, var(--red) 0% 33.33%, #fff 33.33% 66.66%, var(--green) 66.66% 100%);
}
.card-body-profil { padding: 1.75rem 2rem; }
.info-row {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: .85rem 0;
    border-bottom: 1px solid var(--gray-100);
}
.info-row:last-child { border-bottom: none; }
.info-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .85rem;
}
.info-icon.blue { background: var(--blue-light); color: var(--blue); }
.info-icon.green { background: #eaf9ed; color: #178a2b; }
.info-icon.orange { background: #fff7ed; color: #c2410c; }
.info-label { font-size: .72rem; color: var(--gray-600); text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }
.info-val { font-size: .92rem; color: var(--gray-900); font-weight: 600; margin-top: .1rem; }
.status-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .78rem; font-weight: 600; padding: .25rem .7rem;
    border-radius: 50px;
}
.status-badge.inscrit { background: #eaf9ed; color: #178a2b; }
.status-badge.non-inscrit { background: #fef3c7; color: #92400e; }
.legal-box {
    margin: 0 2rem 1.5rem;
    background: var(--gray-50); border-radius: .5rem;
    padding: .75rem 1rem; font-size: .72rem; color: var(--gray-600);
    border: 1px solid var(--gray-200);
    display: flex; align-items: flex-start; gap: .5rem;
}
.legal-box i { color: var(--blue); flex-shrink: 0; margin-top: 2px; }
.footer-bar {
    background: #0f2749; border-top: 2px solid var(--blue);
    padding: .65rem; text-align: center;
}
.footer-bar small { color: rgba(255,255,255,.30); font-size: .68rem; }
</style>
</head>
<body>

<!-- Barre institutionnelle -->
<div class="top-bar">
    <div class="top-bar-inner">
        <div class="txt">
            République du Burundi
            <strong>Ministère de l'Éducation Nationale et de la Recherche Scientifique</strong>
        </div>
        <div class="tri">
            <span style="background:#CE1126;"></span>
            <span style="background:#fff;"></span>
            <span style="background:#1EB53A;"></span>
        </div>
    </div>
</div>

<div class="main">
    <div class="card-profil">

        <!-- En-tête -->
        <div class="card-header-profil">
            <img src="<?= $base ?>/public/images/armoiries_burundi.gif"
                 alt="Armoiries du Burundi">
            <div class="iue-badge">
                <i class="fa-solid fa-id-card"></i>
                <?= SecurityHelper::e($eleve['iue']) ?>
            </div>
            <div class="student-name">
                <?= SecurityHelper::e($eleve['nom']) ?>
                <?= SecurityHelper::e($eleve['prenoms'] ?? '') ?>
            </div>
            <div class="student-sub">
                <?= ($eleve['sexe'] ?? '') === 'M' ? 'Garçon' : (($eleve['sexe'] ?? '') === 'F' ? 'Fille' : '') ?>
                <?php if (!empty($eleve['annee_naissance'])): ?>
                &nbsp;·&nbsp; Né(e) en <?= (int)$eleve['annee_naissance'] ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="flag-band"></div>

        <!-- Corps -->
        <div class="card-body-profil">

            <!-- Identifiant -->
            <div class="info-row">
                <div class="info-icon blue"><i class="fa-solid fa-fingerprint"></i></div>
                <div>
                    <div class="info-label">Identifiant Unique de l'Élève (IUE)</div>
                    <div class="info-val" style="font-family:monospace;font-size:1rem;letter-spacing:.06em;">
                        <?= SecurityHelper::e($eleve['iue']) ?>
                    </div>
                </div>
            </div>

            <?php if ($inscription): ?>
            <!-- Établissement actif -->
            <div class="info-row">
                <div class="info-icon green"><i class="fa-solid fa-school"></i></div>
                <div>
                    <div class="info-label">Établissement actif (<?= SecurityHelper::e($inscription['code_type_annee'] ?? '') ?>)</div>
                    <div class="info-val"><?= SecurityHelper::e($inscription['nom_etablissement'] ?? 'N/A') ?></div>
                    <?php if (!empty($inscription['province'])): ?>
                    <div style="font-size:.78rem;color:#6b7280;margin-top:.15rem;">
                        <i class="fa-solid fa-location-dot me-1"></i>
                        <?= SecurityHelper::e($inscription['province']) ?>
                        <?= !empty($inscription['commune']) ? ' — ' . SecurityHelper::e($inscription['commune']) : '' ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statut -->
            <div class="info-row">
                <div class="info-icon orange"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="info-label">Statut</div>
                    <div class="info-val">
                        <span class="status-badge inscrit">
                            <i class="fa-solid fa-circle" style="font-size:.45rem;"></i>
                            Inscrit(e) — Actif
                        </span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="info-row">
                <div class="info-icon orange"><i class="fa-solid fa-circle-info"></i></div>
                <div>
                    <div class="info-label">Statut</div>
                    <div class="info-val">
                        <span class="status-badge non-inscrit">
                            <i class="fa-solid fa-circle" style="font-size:.45rem;"></i>
                            Aucune inscription active
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /.card-body-profil -->

        <!-- Avertissement légal -->
        <div class="legal-box">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Données officielles du SIGE Burundi — DGESS / MENERS.
            Cette page affiche uniquement les informations non-confidentielles de l'élève.
            Toute reproduction non autorisée est interdite (Loi n°1/03-2026).</span>
        </div>

    </div><!-- /.card-profil -->
</div><!-- /.main -->

<div class="footer-bar">
    <small>FIE Burundi — SIGE &nbsp;·&nbsp; DGESS / MENERS &nbsp;·&nbsp; <?= date('Y') ?></small>
</div>

</body>
</html>
