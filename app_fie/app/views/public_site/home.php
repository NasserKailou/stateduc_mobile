<?php
/**
 * FIE — Site public : Page d'accueil
 * Responsive, couleurs Burundi, inspiré de sige-sectoriel.cm
 * Accessible sans authentification
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'FIE — Fichier Informatisé des Élèves', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie.css">
    <meta name="description" content="Le Fichier Informatisé des Élèves (FIE) du Burundi attribue un Identifiant Unique de l'Élève (IUE) à chaque apprenant du système éducatif burundais.">
</head>
<body>

<!-- ── Barre de navigation publique ──────────────────────────────────────── -->
<nav class="fie-navbar" role="navigation" aria-label="Navigation principale">
    <div class="fie-navbar__inner">
        <a class="fie-navbar__brand" href="<?= BASE_URL ?>/" aria-label="FIE — Accueil">
            <svg class="fie-navbar__brand-logo" viewBox="0 0 32 32" fill="white" aria-hidden="true">
                <rect width="32" height="32" rx="4" fill="rgba(255,255,255,0.2)"/>
                <text x="16" y="22" text-anchor="middle" font-size="14" font-weight="bold" fill="white">FIE</text>
            </svg>
            FIE
        </a>

        <div class="fie-navbar__nav" id="publicNav">
            <a href="<?= BASE_URL ?>/#fonctionnalites" class="fie-navbar__link">Fonctionnalités</a>
            <a href="<?= BASE_URL ?>/#interoperabilite" class="fie-navbar__link">Interopérabilité</a>
            <a href="<?= BASE_URL ?>/aide" class="fie-navbar__link">Aide</a>
            <a href="<?= BASE_URL ?>/contact" class="fie-navbar__link">Contact</a>
        </div>

        <div class="fie-navbar__user">
            <a href="<?= BASE_URL ?>/connexion" class="fie-btn fie-btn--secondary"
               style="border-color:rgba(255,255,255,.7);color:white;">
                Se connecter
            </a>
        </div>

        <button class="fie-navbar__burger" id="burgerBtn"
                aria-controls="publicNav" aria-expanded="false" aria-label="Ouvrir le menu">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="currentColor">
                <rect y="3"  width="22" height="2.5" rx="1.25"/>
                <rect y="10" width="22" height="2.5" rx="1.25"/>
                <rect y="17" width="22" height="2.5" rx="1.25"/>
            </svg>
        </button>
    </div>
</nav>

<!-- ── Hero ──────────────────────────────────────────────────────────────── -->
<section class="fie-hero" aria-labelledby="heroTitle">
    <div class="fie-hero__inner">
        <!-- Drapeau miniature -->
        <div style="display:flex;justify-content:center;margin-bottom:1.5rem">
            <div style="display:flex;width:60px;height:12px;border-radius:3px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.3)">
                <span style="flex:1;background:#CE1126"></span>
                <span style="flex:1;background:#fff"></span>
                <span style="flex:1;background:#1EB53A"></span>
            </div>
        </div>

        <h1 class="fie-hero__title" id="heroTitle">
            Fichier Informatisé des Élèves
        </h1>
        <p class="fie-hero__subtitle">
            Chaque élève burundais mérite un identifiant unique.<br>
            Le FIE attribue un <strong>IUE (Identifiant Unique de l'Élève)</strong>
            persistant tout au long du parcours scolaire — de la maternelle à l'université.
        </p>
        <div class="fie-hero__actions">
            <a href="<?= BASE_URL ?>/connexion" class="fie-hero__btn-primary">
                Accéder à l'application
            </a>
            <a href="<?= BASE_URL ?>/#fonctionnalites" class="fie-hero__btn-secondary">
                En savoir plus
            </a>
        </div>
    </div>
</section>

<!-- Bande drapeau -->
<div class="fie-flag-strip" aria-hidden="true">
    <span class="red"></span>
    <span class="white"></span>
    <span class="green"></span>
</div>

<!-- ── Chiffres clés ──────────────────────────────────────────────────────── -->
<section style="background:var(--fie-white);padding:3rem 1rem">
    <div style="max-width:1200px;margin:0 auto;display:grid;
                grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:2rem;text-align:center">
        <?php
        $chiffres = [
            ['~12 000', 'Établissements', 'sur l\'ensemble du territoire'],
            ['5', 'Sous-secteurs', 'Préscolaire, Primaire, Secondaire, EFTP, Supérieur'],
            ['Format IUE', 'BI-SSSS-AAAA-NNNNNN-CC', 'Identifiant unique national'],
            ['ISO 7064', 'Contrôle d\'intégrité', 'MOD 97-10 sur chaque IUE'],
        ];
        foreach ($chiffres as [$val, $label, $sub]):
        ?>
        <div>
            <div style="font-size:2rem;font-weight:800;color:var(--fie-red)"><?= $val ?></div>
            <div style="font-weight:600;color:var(--fie-gray-900);margin:.25rem 0"><?= $label ?></div>
            <div style="font-size:.8rem;color:var(--fie-gray-500)"><?= $sub ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ── Fonctionnalités ────────────────────────────────────────────────────── -->
<section class="fie-features" id="fonctionnalites" aria-labelledby="featuresTitle">
    <div class="fie-features__inner">
        <h2 class="fie-features__title" id="featuresTitle">Fonctionnalités</h2>
        <div class="fie-features__grid">

            <?php
            $features = [
                [
                    'icon' => '<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>',
                    'title' => 'IUE — Identifiant unique',
                    'text'  => 'Génération automatique d\'un matricule national unique au format BI-SSSS-AAAA-NNNNNN-CC avec contrôle d\'intégrité ISO 7064 MOD 97-10.',
                ],
                [
                    'icon' => '<path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/>',
                    'title' => 'Inscription numérique',
                    'text'  => 'Formulaire complet avec sélects dépendants Province→Commune→Zone→Colline→Établissement, détection de doublons AJAX et fiche imprimable.',
                ],
                [
                    'icon' => '<path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 4l5 2.18V11c0 3.5-2.33 6.79-5 7.93-2.67-1.14-5-4.43-5-7.93V7.18L12 5z"/>',
                    'title' => 'Sécurité & conformité',
                    'text'  => 'PDO préparé, CSRF, XSS, bcrypt cost-12, session sécurisée, journal d\'audit complet conformément à la loi n°1/03-2026.',
                ],
                [
                    'icon' => '<path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>',
                    'title' => 'Interopérabilité StatEduc',
                    'text'  => 'Synchronisation bidirectionnelle avec la base SQL Server de StatEduc : consommation de l\'API établissements et exposition des agrégats ELEVES_AGE_NIVEAU_SEXE.',
                ],
                [
                    'icon' => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>',
                    'title' => 'Tableau de bord analytique',
                    'text'  => 'Indicateurs en temps réel : répartition par secteur, parité filles/garçons, doublons détectés, agrégats en attente de synchronisation.',
                ],
                [
                    'icon' => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>',
                    'title' => 'Disponibilité hors-ligne',
                    'text'  => 'Table miroir locale des établissements. Fonctionne même sans connexion à StatEduc grâce au cache MySQL et au mode de synchronisation incrémentale.',
                ],
            ];
            foreach ($features as $f):
            ?>
            <div class="fie-feature-card">
                <div class="fie-feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="currentColor"><?= $f['icon'] ?></svg>
                </div>
                <h3 class="fie-feature-card__title"><?= $f['title'] ?></h3>
                <p class="fie-feature-card__text"><?= $f['text'] ?></p>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<!-- Bande drapeau -->
<div class="fie-flag-strip" aria-hidden="true">
    <span class="red"></span>
    <span class="white"></span>
    <span class="green"></span>
</div>

<!-- ── Interopérabilité ───────────────────────────────────────────────────── -->
<section style="background:var(--fie-gray-50);padding:4rem 1rem" id="interoperabilite">
    <div style="max-width:900px;margin:0 auto">
        <h2 style="text-align:center;font-size:1.75rem;margin-bottom:2rem;color:var(--fie-gray-900)">
            Architecture d'interopérabilité
        </h2>

        <!-- Diagramme simplifié -->
        <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:1rem;align-items:center;
                    text-align:center;margin-bottom:2rem">

            <!-- StatEduc -->
            <div style="background:white;border:2px solid var(--fie-red);border-radius:12px;padding:1.5rem">
                <div style="font-weight:700;font-size:1.1rem;color:var(--fie-red);margin-bottom:.5rem">
                    StatEduc (SQL Server)
                </div>
                <ul style="list-style:disc;text-align:left;padding-left:1.2rem;font-size:.85rem;color:var(--fie-gray-600)">
                    <li>Référentiel établissements</li>
                    <li>Codes de nomenclature</li>
                    <li>ELEVES_AGE_NIVEAU_SEXE</li>
                    <li>API REST ← <code>etabs_fie_ws.php</code></li>
                </ul>
            </div>

            <!-- Flèches -->
            <div style="display:flex;flex-direction:column;gap:.5rem;align-items:center">
                <div style="font-size:.75rem;color:var(--fie-gray-500)">Étabs + localisation</div>
                <div style="font-size:1.5rem;color:var(--fie-green)">⟶</div>
                <div style="font-size:1.5rem;color:var(--fie-red)">⟵</div>
                <div style="font-size:.75rem;color:var(--fie-gray-500)">Agrégats effectifs</div>
            </div>

            <!-- FIE -->
            <div style="background:white;border:2px solid var(--fie-green);border-radius:12px;padding:1.5rem">
                <div style="font-weight:700;font-size:1.1rem;color:var(--fie-green);margin-bottom:.5rem">
                    FIE (MySQL)
                </div>
                <ul style="list-style:disc;text-align:left;padding-left:1.2rem;font-size:.85rem;color:var(--fie-gray-600)">
                    <li>Élèves + IUE unique</li>
                    <li>Inscriptions individuelles</li>
                    <li>Table miroir établissements</li>
                    <li>API REST → <code>aggregates_ws.php</code></li>
                </ul>
            </div>

        </div>

        <p style="text-align:center;font-size:.9rem;color:var(--fie-gray-600);max-width:700px;margin:0 auto">
            La synchronisation est <strong>idempotente</strong> (upsert par CODE_ETABLISSEMENT)
            et supporte un <strong>mode incrémental</strong> via le paramètre
            <code>updated_since</code> de l'API StatEduc.
            En l'absence de connectivité, un <strong>import Excel</strong> (<em>fallback</em>)
            alimente la même table miroir sans écraser les données d'origine API.
        </p>
    </div>
</section>

<!-- ── CTA final ─────────────────────────────────────────────────────────── -->
<section style="background:var(--fie-green);color:white;padding:3rem 1rem;text-align:center">
    <h2 style="font-size:1.75rem;font-weight:800;margin-bottom:1rem;color:white">
        Prêt à commencer ?
    </h2>
    <p style="font-size:1rem;opacity:.9;margin-bottom:2rem;max-width:500px;margin-left:auto;margin-right:auto">
        Connectez-vous pour inscrire des élèves, générer des IUE et synchroniser les effectifs avec StatEduc.
    </p>
    <a href="<?= BASE_URL ?>/connexion"
       style="background:white;color:var(--fie-green);font-weight:700;
              padding:.75rem 2.5rem;border-radius:9999px;text-decoration:none;
              font-size:1rem;box-shadow:0 4px 12px rgba(0,0,0,.15);display:inline-block;
              transition:box-shadow .18s ease">
        Se connecter à FIE
    </a>
</section>

<!-- ── Pied de page public ────────────────────────────────────────────────── -->
<footer style="background:var(--fie-gray-800);color:var(--fie-gray-400);
               padding:2rem 1rem;font-size:.85rem;border-top:3px solid var(--fie-red)">
    <div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;
                justify-content:space-between;gap:1rem;align-items:center">
        <div>
            <strong style="color:white">FIE</strong> — Fichier Informatisé des Élèves &nbsp;·&nbsp;
            SIGE Burundi — MENERS
        </div>
        <nav style="display:flex;gap:1rem;flex-wrap:wrap">
            <a href="<?= BASE_URL ?>/aide"             style="color:var(--fie-gray-400)">Aide</a>
            <a href="<?= BASE_URL ?>/contact"          style="color:var(--fie-gray-400)">Contact</a>
            <a href="<?= BASE_URL ?>/confidentialite"  style="color:var(--fie-gray-400)">Confidentialité</a>
            <a href="<?= BASE_URL ?>/mentions-legales" style="color:var(--fie-gray-400)">Mentions légales</a>
        </nav>
        <div style="color:var(--fie-gray-600);font-size:.75rem">
            &copy; <?= date('Y') ?> — Conforme loi n°1/03-2026
        </div>
    </div>
</footer>

<script>
// Burger mobile
(function(){
    var burger = document.getElementById('burgerBtn');
    var nav    = document.getElementById('publicNav');
    if(!burger||!nav) return;
    burger.addEventListener('click', function(){
        var open = nav.classList.toggle('fie-navbar__nav--open');
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
}());
</script>

</body>
</html>
