<?php
/**
 * FIE — Site public : Page d'accueil
 * Bootstrap 5 + Font Awesome 6.5 via CDN — Charte Burundi
 * CORRECTION Phase 2 : refonte complète avec Bootstrap (suppression custom CSS inline)
 */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'FIE — Fichier Informatisé des Élèves', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="Le Fichier Informatisé des Élèves (FIE) du Burundi attribue un Identifiant Unique (IUE) à chaque apprenant du système éducatif burundais.">

    <!-- Bootstrap 5.3 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <!-- Font Awesome 6.5 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- FIE CSS (charte Burundi + surcharges Bootstrap) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie.css">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════════════
     NAVBAR PUBLIQUE Bootstrap 5
     ═══════════════════════════════════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg fie-navbar" aria-label="Navigation principale">
    <div class="container">
        <!-- Logo / Marque -->
        <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2" href="<?= BASE_URL ?>/">
            <div class="rounded-2 d-flex align-items-center justify-content-center"
                 style="width:36px;height:36px;background:rgba(255,255,255,.2)">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            FIE <span class="d-none d-md-inline ms-1 fw-normal" style="opacity:.8;font-size:.85rem">Burundi</span>
        </a>

        <!-- Burger mobile -->
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#publicNav"
                aria-controls="publicNav" aria-expanded="false" aria-label="Menu">
            <i class="fa-solid fa-bars text-white"></i>
        </button>

        <!-- Liens -->
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASE_URL ?>/#fonctionnalites">
                        <i class="fa-solid fa-list-check me-1"></i>Fonctionnalités
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASE_URL ?>/#interoperabilite">
                        <i class="fa-solid fa-arrows-left-right me-1"></i>Interopérabilité
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASE_URL ?>/aide">
                        <i class="fa-solid fa-circle-question me-1"></i>Aide
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASE_URL ?>/contact">
                        <i class="fa-solid fa-envelope me-1"></i>Contact
                    </a>
                </li>
            </ul>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/connexion" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="fa-solid fa-right-to-bracket me-1"></i>Se connecter
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Bande tricolore sous la navbar -->
<div class="fie-flag-strip" aria-hidden="true">
    <span class="red"></span>
    <span class="white"></span>
    <span class="green"></span>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     HERO
     ═══════════════════════════════════════════════════════════════════════ -->
<section class="fie-hero" aria-labelledby="heroTitle">
    <div class="container py-5">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">

                <!-- Mini drapeau -->
                <div class="d-flex justify-content-center mb-4">
                    <div class="rounded-pill overflow-hidden shadow-sm"
                         style="display:flex;width:60px;height:12px">
                        <span class="flex-fill" style="background:#CE1126"></span>
                        <span class="flex-fill" style="background:#fff"></span>
                        <span class="flex-fill" style="background:#1EB53A"></span>
                    </div>
                </div>

                <h1 class="display-4 fw-black text-white mb-3" id="heroTitle">
                    Fichier Informatisé<br>des Élèves
                </h1>
                <p class="lead text-white mb-4" style="opacity:.9">
                    Chaque élève burundais mérite un identifiant unique.<br>
                    Le <strong>FIE</strong> attribue un
                    <strong>IUE (Identifiant Unique de l'Élève)</strong>
                    persistant de la maternelle à l'université.
                </p>

                <div class="d-flex flex-wrap gap-3 justify-content-center mb-4">
                    <a href="<?= BASE_URL ?>/connexion"
                       class="btn btn-light btn-lg fw-bold px-4 shadow">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Accéder à l'application
                    </a>
                    <a href="<?= BASE_URL ?>/#fonctionnalites"
                       class="btn btn-outline-light btn-lg px-4">
                        <i class="fa-solid fa-circle-info me-2"></i>En savoir plus
                    </a>
                </div>

                <!-- Badge IUE exemple -->
                <div class="d-inline-flex align-items-center gap-2 rounded-pill px-4 py-2
                            text-white fw-bold" style="background:rgba(0,0,0,.25);font-size:1.1rem">
                    <i class="fa-solid fa-fingerprint"></i>
                    <span>BI-0002-2024-000001-28</span>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════
     CHIFFRES CLÉS
     ═══════════════════════════════════════════════════════════════════════ -->
<section class="bg-white py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <?php
            $chiffres = [
                ['~12 000',  'Établissements',    'sur tout le territoire',    'fa-school'],
                ['5',        'Sous-secteurs',      'Prés., Prim., Sec., EFTP, Sup.', 'fa-layer-group'],
                ['BI-SSSS-AAAA-NNNNNN-CC', 'Format IUE', 'Matricule national unique', 'fa-id-card'],
                ['ISO 7064', 'Contrôle intégrité', 'MOD 97-10 sur chaque IUE', 'fa-shield-halved'],
            ];
            foreach ($chiffres as [$val, $label, $sub, $icon]):
            ?>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width:60px;height:60px;background:#fff5f5">
                        <i class="fa-solid <?= $icon ?> fa-lg" style="color:var(--fie-red)"></i>
                    </div>
                    <div class="fw-black fs-4 mb-1" style="color:var(--fie-red)"><?= $val ?></div>
                    <div class="fw-semibold mb-1"><?= $label ?></div>
                    <div class="text-muted small"><?= $sub ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Bande tricolore -->
<div class="fie-flag-strip" aria-hidden="true">
    <span class="red"></span>
    <span class="white"></span>
    <span class="green"></span>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     FONCTIONNALITÉS
     ═══════════════════════════════════════════════════════════════════════ -->
<section class="py-5" id="fonctionnalites" style="background:#f8f9fa">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge mb-3 px-3 py-2 rounded-pill"
                  style="background:#fff5f5;color:var(--fie-red);font-size:.85rem">
                <i class="fa-solid fa-star me-1"></i>FONCTIONNALITÉS
            </span>
            <h2 class="h2 fw-bold">Un système complet pour le SIGE Burundi</h2>
            <p class="text-muted">Toutes les fonctionnalités nécessaires au suivi individuel de chaque élève</p>
        </div>

        <div class="row g-4">
            <?php
            $features = [
                [
                    'icon'  => 'fa-fingerprint',
                    'color' => 'var(--fie-red)',
                    'bg'    => '#fff5f5',
                    'title' => 'IUE — Identifiant unique',
                    'text'  => "Génération automatique d'un matricule national unique au format BI-SSSS-AAAA-NNNNNN-CC avec contrôle ISO 7064 MOD 97-10.",
                ],
                [
                    'icon'  => 'fa-file-signature',
                    'color' => '#0d6efd',
                    'bg'    => '#f0f4ff',
                    'title' => 'Inscription numérique',
                    'text'  => "Formulaire complet : sélects dépendants Province→Commune→Zone→Colline→Établissement, détection de doublons AJAX, fiche imprimable.",
                ],
                [
                    'icon'  => 'fa-shield-halved',
                    'color' => 'var(--fie-green)',
                    'bg'    => '#f0fff4',
                    'title' => 'Sécurité & conformité',
                    'text'  => "PDO préparé, CSRF, XSS, bcrypt cost-12, session sécurisée, journal d'audit complet — loi n°1/03-2026.",
                ],
                [
                    'icon'  => 'fa-arrows-left-right',
                    'color' => '#6f42c1',
                    'bg'    => '#f5f0ff',
                    'title' => 'Interopérabilité StatEduc',
                    'text'  => "Synchronisation avec la base SQL Server de StatEduc : API établissements + agrégats ELEVES_AGE_NIVEAU_SEXE.",
                ],
                [
                    'icon'  => 'fa-chart-bar',
                    'color' => '#fd7e14',
                    'bg'    => '#fff8f0',
                    'title' => 'Tableau de bord analytique',
                    'text'  => "Indicateurs en temps réel : parité filles/garçons, répartition par secteur, doublons détectés, agrégats en attente.",
                ],
                [
                    'icon'  => 'fa-wifi',
                    'color' => '#20c997',
                    'bg'    => '#f0fffa',
                    'title' => 'Mode hors-ligne',
                    'text'  => "Table miroir locale des établissements. Fonctionne même sans connexion StatEduc grâce au cache MySQL et à l'import Excel.",
                ],
            ];
            foreach ($features as $f):
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="rounded-circle mb-3 d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;background:<?= $f['bg'] ?>">
                            <i class="fa-solid <?= $f['icon'] ?> fa-lg"
                               style="color:<?= $f['color'] ?>"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-2"><?= $f['title'] ?></h3>
                        <p class="text-muted small mb-0"><?= $f['text'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Bande tricolore -->
<div class="fie-flag-strip" aria-hidden="true">
    <span class="red"></span>
    <span class="white"></span>
    <span class="green"></span>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     INTEROPÉRABILITÉ
     ═══════════════════════════════════════════════════════════════════════ -->
<section class="bg-white py-5" id="interoperabilite">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge mb-3 px-3 py-2 rounded-pill"
                  style="background:#f0fff4;color:var(--fie-green);font-size:.85rem">
                <i class="fa-solid fa-network-wired me-1"></i>INTEROPÉRABILITÉ
            </span>
            <h2 class="h2 fw-bold">Architecture d'interopérabilité</h2>
        </div>

        <div class="row g-4 align-items-center justify-content-center">
            <!-- StatEduc -->
            <div class="col-md-5">
                <div class="card border-danger h-100 shadow-sm">
                    <div class="card-header text-white fw-bold" style="background:var(--fie-red)">
                        <i class="fa-solid fa-database me-2"></i>StatEduc (SQL Server)
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Référentiel établissements</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Codes de nomenclature</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>ELEVES_AGE_NIVEAU_SEXE</li>
                            <li><i class="fa-solid fa-check text-success me-2"></i>API REST ← <code>etabs_fie_ws.php</code></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Flèches -->
            <div class="col-md-2 text-center">
                <div class="d-flex flex-column align-items-center gap-3">
                    <div>
                        <div class="text-muted small mb-1">Étabs + localisation</div>
                        <i class="fa-solid fa-arrow-right fa-2x" style="color:var(--fie-green)"></i>
                    </div>
                    <div>
                        <i class="fa-solid fa-arrow-left fa-2x" style="color:var(--fie-red)"></i>
                        <div class="text-muted small mt-1">Agrégats effectifs</div>
                    </div>
                </div>
            </div>

            <!-- FIE -->
            <div class="col-md-5">
                <div class="card h-100 shadow-sm" style="border-color:var(--fie-green)">
                    <div class="card-header text-white fw-bold" style="background:var(--fie-green)">
                        <i class="fa-solid fa-server me-2"></i>FIE (MySQL)
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Élèves + IUE unique</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Inscriptions individuelles</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Table miroir établissements</li>
                            <li><i class="fa-solid fa-check text-success me-2"></i>API REST → <code>aggregates_ws.php</code></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <div class="alert alert-light border text-center small">
                    <i class="fa-solid fa-circle-info text-primary me-2"></i>
                    La synchronisation est <strong>idempotente</strong> (upsert par CODE_ETABLISSEMENT)
                    et supporte un <strong>mode incrémental</strong> via le paramètre
                    <code>updated_since</code> de l'API StatEduc.
                    En l'absence de connectivité, un <strong>import Excel</strong> alimente la même table miroir.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════
     CTA FINAL
     ═══════════════════════════════════════════════════════════════════════ -->
<section class="py-5 text-center text-white" style="background:var(--fie-green)">
    <div class="container">
        <i class="fa-solid fa-rocket fa-3x mb-3" style="opacity:.7"></i>
        <h2 class="h2 fw-bold mb-3">Prêt à commencer ?</h2>
        <p class="lead mb-4" style="opacity:.9;max-width:500px;margin:0 auto 1.5rem">
            Connectez-vous pour inscrire des élèves, générer des IUE
            et synchroniser les effectifs avec StatEduc.
        </p>
        <a href="<?= BASE_URL ?>/connexion"
           class="btn btn-light btn-lg fw-bold px-5 rounded-pill shadow">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Se connecter à FIE
        </a>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════
     FOOTER PUBLIC
     ═══════════════════════════════════════════════════════════════════════ -->
<footer class="py-4" style="background:#212529;border-top:3px solid var(--fie-red)">
    <div class="container">
        <div class="row align-items-center gy-3">
            <div class="col-md-4">
                <div class="text-white fw-bold mb-1">
                    <i class="fa-solid fa-graduation-cap me-2" style="color:var(--fie-red)"></i>
                    FIE — Fichier Informatisé des Élèves
                </div>
                <div class="text-secondary small">SIGE Burundi — MENERS</div>
            </div>
            <div class="col-md-4 text-center">
                <nav class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>/aide"            class="text-secondary small text-decoration-none">Aide</a>
                    <a href="<?= BASE_URL ?>/contact"         class="text-secondary small text-decoration-none">Contact</a>
                    <a href="<?= BASE_URL ?>/confidentialite" class="text-secondary small text-decoration-none">Confidentialité</a>
                    <a href="<?= BASE_URL ?>/mentions-legales" class="text-secondary small text-decoration-none">Mentions légales</a>
                </nav>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="text-secondary small">
                    &copy; <?= date('Y') ?> — Conforme loi n°1/03-2026
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmz1fQ4EVhUb+pE7gNWAxQplbEW"
        crossorigin="anonymous"></script>

</body>
</html>
