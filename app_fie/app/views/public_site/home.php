<?php
/**
 * FIE — Site public : Page d'accueil REFONTE COMPLÈTE
 * Bootstrap 5.3 + Font Awesome 6.5 + Google Fonts (Poppins/Open Sans)
 * AOS (Animate On Scroll) + Compteurs animés
 * Charte Burundi : #CE1126 / #1EB53A / #FFFFFF
 * Page standalone (inclut ses propres CDN — pas de layouts admin)
 */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIE — Fichier Informatisé des Élèves du Burundi | SIGE</title>
    <meta name="description" content="Le Fichier Informatisé des Élèves (FIE) du Burundi attribue un Identifiant Unique (IUE) à chaque apprenant du système éducatif national — DGESS / SIGE Burundi.">
    <meta name="theme-color" content="#CE1126">

    <!-- Google Fonts : Poppins (titres) + Open Sans (corps) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    <!-- Font Awesome 6.5 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- AOS — Animate On Scroll -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

    <!-- FIE CSS charte Burundi -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie.css">

    <style>
        /* ── Typographie Poppins/Open Sans ── */
        body { font-family: 'Open Sans', sans-serif; background: #fff; }
        h1,h2,h3,h4,h5,h6,.navbar-brand { font-family: 'Poppins', sans-serif; }

        /* ── Variables Burundi ── */
        :root {
            --bi-red:    #CE1126;
            --bi-red-d:  #a50d1e;
            --bi-green:  #1EB53A;
            --bi-green-d:#178a2b;
            --bi-white:  #FFFFFF;
        }

        /* ══════════════════════════════════════
           NAVBAR sticky avec ombre au scroll
           ══════════════════════════════════════ */
        #mainNav {
            background: var(--bi-red);
            transition: box-shadow .3s, padding .3s;
            padding-top: .75rem;
            padding-bottom: .75rem;
        }
        #mainNav.scrolled {
            box-shadow: 0 4px 20px rgba(0,0,0,.25);
            padding-top: .45rem;
            padding-bottom: .45rem;
        }
        #mainNav .navbar-brand {
            font-weight: 800;
            font-size: 1.3rem;
            color: #fff;
            letter-spacing: .02em;
        }
        #mainNav .nav-link {
            color: rgba(255,255,255,.85) !important;
            font-size: .875rem;
            font-weight: 500;
            padding: .5rem .8rem !important;
            border-radius: 6px;
            transition: background .2s, color .2s;
        }
        #mainNav .nav-link:hover, #mainNav .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,.15);
        }
        .navbar-toggler { border: none; }
        .navbar-toggler:focus { box-shadow: none; }

        /* Bouton Connexion dans navbar */
        .nav-btn-login {
            background: #fff;
            color: var(--bi-red) !important;
            font-weight: 700 !important;
            padding: .4rem 1.2rem !important;
            border-radius: 50px !important;
            transition: transform .2s, box-shadow .2s !important;
        }
        .nav-btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,.2);
            color: var(--bi-red-d) !important;
            background: #f5f5f5;
        }

        /* Bande drapeau sous navbar */
        .flag-strip {
            height: 5px;
            background: linear-gradient(to right, var(--bi-red) 33.33%, #fff 33.33%, #fff 66.66%, var(--bi-green) 66.66%);
        }

        /* ══════════════════════════════════════
           HERO PLEIN ÉCRAN
           ══════════════════════════════════════ */
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #8c0019 0%, var(--bi-red) 40%, #b01020 75%, #6e010e 100%);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        /* Motif géométrique en arrière-plan */
        .hero-section::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(255,255,255,.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(30,181,58,.1) 0%, transparent 45%),
                url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M0 0h40v40H0V0zm40 40h40v40H40V40zm0-40h2l-2 2V0zm0 4l4-4h2l-6 6V4zm0 4l8-8h2L40 10V8zm0 4L52 0h2L40 14v-2zm0 4L56 0h2L40 18v-2zm0 4L60 0h2L40 22v-2zm0 4L64 0h2L40 26v-2zm0 4L68 0h2L40 30v-2zm0 4L72 0h2L40 34v-2zm0 4L76 0h2L40 38v-2zm0 4L80 0v2L42 40h-2zm4 0L80 4v2L46 40h-2zm4 0L80 8v2L50 40h-2zm4 0L80 12v2L54 40h-2zm4 0L80 16v2L58 40h-2zm4 0L80 20v2L62 40h-2zm4 0L80 24v2L66 40h-2zm4 0L80 28v2L70 40h-2zm4 0L80 32v2L74 40h-2zm4 0L80 36v2L78 40h-2zm4 0L80 40v-2z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        /* Vague verte en bas */
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 0; right: 0;
            height: 80px;
            background: #fff;
            clip-path: ellipse(55% 100% at 50% 100%);
        }
        .hero-content { position: relative; z-index: 1; }
        .hero-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 900;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 1.25rem;
        }
        .hero-title .accent { color: #ffd166; }
        .hero-subtitle {
            font-size: clamp(1rem, 2vw, 1.2rem);
            color: rgba(255,255,255,.88);
            line-height: 1.7;
            margin-bottom: 2.5rem;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: .8rem; font-weight: 600;
            padding: .35rem 1rem;
            border-radius: 50px;
            margin-bottom: 1.25rem;
            letter-spacing: .04em;
        }
        .hero-iue-example {
            display: inline-block;
            font-family: 'Courier New', monospace;
            font-size: .95rem;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25);
            color: #ffd166;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            letter-spacing: .06em;
            margin-bottom: 2rem;
        }
        .btn-hero-primary {
            background: #fff;
            color: var(--bi-red);
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            padding: .75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            border: none;
            display: inline-block;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(0,0,0,.25);
        }
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(0,0,0,.35);
            color: var(--bi-red-d);
        }
        .btn-hero-outline {
            background: transparent;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            padding: .75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            border: 2px solid rgba(255,255,255,.6);
            display: inline-block;
            transition: background .2s, border-color .2s;
        }
        .btn-hero-outline:hover {
            background: rgba(255,255,255,.15);
            border-color: #fff;
            color: #fff;
        }
        /* Illustration hero (droit) */
        .hero-illustration {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 1.5rem;
            padding: 2rem;
            backdrop-filter: blur(4px);
        }
        .hero-stat-pill {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 12px;
            padding: .75rem 1rem;
            color: #fff;
            display: flex; align-items: center; gap: .75rem;
        }
        .hero-stat-pill .num {
            font-family: 'Poppins', sans-serif;
            font-weight: 800; font-size: 1.4rem;
        }
        .hero-stat-pill .lbl { font-size: .75rem; opacity: .8; line-height: 1.3; }

        /* ══════════════════════════════════════
           SECTION CHIFFRES CLÉS
           ══════════════════════════════════════ */
        .stats-section { background: #fff; padding: 5rem 0; }
        .stat-box {
            text-align: center;
            padding: 2rem 1rem;
            border-radius: 1rem;
            transition: transform .25s, box-shadow .25s;
        }
        .stat-box:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,.1); }
        .stat-icon-wrap {
            width: 72px; height: 72px; border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem;
        }
        .stat-number {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem; font-weight: 900;
            line-height: 1;
        }
        .stat-label { font-size: .875rem; color: #6c757d; font-weight: 500; margin-top: .25rem; }
        .stat-box--red   .stat-icon-wrap { background: #ffeef0; color: var(--bi-red); }
        .stat-box--green .stat-icon-wrap { background: #eaf9ed; color: var(--bi-green); }
        .stat-box--blue  .stat-icon-wrap { background: #e8f0fe; color: #1a73e8; }
        .stat-box--gold  .stat-icon-wrap { background: #fff8e1; color: #f0a500; }
        .stat-box--red   .stat-number { color: var(--bi-red); }
        .stat-box--green .stat-number { color: var(--bi-green); }
        .stat-box--blue  .stat-number { color: #1a73e8; }
        .stat-box--gold  .stat-number { color: #f0a500; }

        /* ══════════════════════════════════════
           SECTION FONCTIONNALITÉS
           ══════════════════════════════════════ */
        .features-section {
            background: #f8f9fa;
            padding: 5rem 0;
        }
        .section-eyebrow {
            font-family: 'Poppins', sans-serif;
            font-size: .75rem; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase;
        }
        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(1.5rem, 3vw, 2.25rem);
            font-weight: 800;
            color: #1a1a2e;
            line-height: 1.25;
        }
        .feature-card {
            background: #fff;
            border-radius: 1rem;
            padding: 2rem;
            height: 100%;
            border: 1px solid #e9ecef;
            transition: transform .25s, box-shadow .25s, border-color .25s;
            cursor: default;
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(0,0,0,.09);
            border-color: transparent;
        }
        .feature-card:hover .feature-icon { transform: scale(1.1); }
        .feature-icon {
            width: 60px; height: 60px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
            transition: transform .25s;
        }
        .feature-card h5 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700; font-size: 1rem;
            color: #1a1a2e; margin-bottom: .5rem;
        }
        .feature-card p { font-size: .875rem; color: #6c757d; line-height: 1.65; margin: 0; }

        /* ══════════════════════════════════════
           SECTION À PROPOS / MISSION
           ══════════════════════════════════════ */
        .about-section { background: #fff; padding: 5rem 0; }
        .about-img-placeholder {
            background: linear-gradient(135deg, var(--bi-red) 0%, var(--bi-red-d) 100%);
            border-radius: 1.25rem;
            min-height: 380px;
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        .about-img-placeholder::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .about-milestone {
            display: flex; align-items: flex-start; gap: 1rem;
            padding: 1rem; border-radius: .75rem;
            transition: background .2s;
        }
        .about-milestone:hover { background: #f8f9fa; }
        .about-milestone-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }
        .about-milestone h6 { font-family: 'Poppins', sans-serif; font-weight: 700; margin-bottom: .2rem; }
        .about-milestone p { font-size: .825rem; color: #6c757d; margin: 0; }

        /* ══════════════════════════════════════
           SECTION ACTUALITÉS (carrousel)
           ══════════════════════════════════════ */
        .news-section { background: #f8f9fa; padding: 5rem 0; }
        .news-card {
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            border: none;
            height: 100%;
            transition: transform .25s, box-shadow .25s;
        }
        .news-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,.1); }
        .news-img {
            height: 160px;
            display: flex; align-items: center; justify-content: center;
            font-size: 3rem;
        }
        .news-category {
            font-size: .7rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
        }
        .news-card .card-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700; font-size: .95rem;
            color: #1a1a2e;
            line-height: 1.4;
        }
        .news-date { font-size: .75rem; color: #adb5bd; }

        /* ══════════════════════════════════════
           SECTION PARTENAIRES
           ══════════════════════════════════════ */
        .partners-section { background: #fff; padding: 4rem 0; }
        .partner-logo {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 1rem;
            padding: 1.5rem;
            display: flex; align-items: center; justify-content: center;
            height: 100px;
            transition: border-color .2s, box-shadow .2s;
            text-align: center;
        }
        .partner-logo:hover { border-color: var(--bi-red); box-shadow: 0 4px 16px rgba(206,17,38,.1); }
        .partner-logo span {
            font-family: 'Poppins', sans-serif;
            font-weight: 700; font-size: .8rem;
            color: #6c757d; line-height: 1.3;
        }

        /* ══════════════════════════════════════
           CTA SECTION
           ══════════════════════════════════════ */
        .cta-section {
            background: linear-gradient(135deg, var(--bi-green) 0%, var(--bi-green-d) 100%);
            padding: 5rem 0;
            position: relative; overflow: hidden;
        }
        .cta-section::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        .cta-section .cta-inner { position: relative; z-index: 1; }
        .cta-section h2 { font-family: 'Poppins', sans-serif; font-weight: 800; color: #fff; }
        .cta-section p { color: rgba(255,255,255,.88); }

        /* ══════════════════════════════════════
           FOOTER
           ══════════════════════════════════════ */
        .site-footer {
            background: #0f1923;
            color: #94a3b8;
            padding: 4rem 0 2rem;
        }
        .footer-brand {
            font-family: 'Poppins', sans-serif;
            font-size: 1.4rem; font-weight: 800; color: #fff;
        }
        .footer-tagline { font-size: .8rem; color: #64748b; }
        .footer-heading {
            font-family: 'Poppins', sans-serif;
            font-size: .75rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            color: #cbd5e1; margin-bottom: 1rem;
        }
        .footer-link {
            color: #94a3b8; text-decoration: none;
            font-size: .875rem; display: block; margin-bottom: .45rem;
            transition: color .2s;
        }
        .footer-link:hover { color: #fff; }
        .footer-divider { border-color: #1e2d3d; margin: 2rem 0 1.5rem; }
        .footer-copyright { font-size: .8rem; color: #64748b; }
        .footer-social a {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%;
            background: #1e2d3d; color: #94a3b8;
            text-decoration: none; font-size: .875rem;
            transition: background .2s, color .2s;
        }
        .footer-social a:hover { background: var(--bi-red); color: #fff; }

        /* ══════════════════════════════════════
           SCROLL INDICATOR
           ══════════════════════════════════════ */
        .scroll-down {
            position: absolute; bottom: 100px; left: 50%;
            transform: translateX(-50%);
            z-index: 2; color: rgba(255,255,255,.6);
            font-size: .75rem; text-align: center;
            animation: bounceDown 2s infinite;
        }
        @keyframes bounceDown {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(8px); }
        }

        /* ══════════════════════════════════════
           ACCESSIBILITÉ
           ══════════════════════════════════════ */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
            [data-aos] { opacity: 1 !important; transform: none !important; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════
     NAVBAR sticky responsive
     ═══════════════════════════════════════════════════════ -->
<nav id="mainNav" class="navbar navbar-expand-lg sticky-top" aria-label="Navigation principale">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/">
            <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:10px;
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-graduation-cap text-white"></i>
            </div>
            <div>
                <span class="text-white fw-bold" style="font-size:1.1rem;">FIE</span>
                <span class="d-none d-sm-inline text-white ms-1" style="opacity:.75;font-size:.8rem;font-weight:400;">Burundi</span>
            </div>
        </a>

        <!-- Burger -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Menu">
            <i class="fa-solid fa-bars text-white"></i>
        </button>

        <!-- Liens -->
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="#fonctionnalites">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#mission">Mission</a></li>
                <li class="nav-item"><a class="nav-link" href="#actualites">Actualités</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/aide">Aide</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact">Contact</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <span class="text-white opacity-50 small d-none d-lg-inline">|</span>
                <a class="nav-link nav-btn-login" href="<?= BASE_URL ?>/connexion">
                    <i class="fa-solid fa-right-to-bracket me-1"></i>Connexion
                </a>
            </div>
        </div>
    </div>
</nav>
<!-- Bande tricolore drapeau -->
<div class="flag-strip"></div>


<!-- ═══════════════════════════════════════════════════════
     HERO — Plein écran
     ═══════════════════════════════════════════════════════ -->
<section class="hero-section" id="accueil">
    <div class="container py-5">
        <div class="row align-items-center g-5">

            <!-- Texte gauche -->
            <div class="col-lg-6 hero-content">
                <div class="hero-badge mb-3" data-aos="fade-down" data-aos-duration="600">
                    <i class="fa-solid fa-star text-warning"></i>
                    SIGE Burundi — DGESS / MENERS
                </div>
                <h1 class="hero-title" data-aos="fade-right" data-aos-duration="700" data-aos-delay="100">
                    Un <span class="accent">identifiant unique</span><br>
                    pour chaque élève<br>du Burundi
                </h1>
                <p class="hero-subtitle" data-aos="fade-right" data-aos-duration="700" data-aos-delay="200">
                    Le <strong class="text-white">Fichier Informatisé des Élèves</strong> attribue un IUE
                    (Identifiant Unique Élève) à chaque apprenant du système éducatif national.
                    Traçabilité, interopérabilité StatEduc, et pilotage par les données.
                </p>
                <div data-aos="fade-right" data-aos-delay="300">
                    <div class="mb-3">
                        <span class="text-white opacity-60 small me-2">Exemple d'IUE :</span>
                        <span class="hero-iue-example">BI-0002-2025-000001-07</span>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= BASE_URL ?>/connexion" class="btn-hero-primary">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Accéder à la plateforme
                        </a>
                        <a href="#fonctionnalites" class="btn-hero-outline">
                            <i class="fa-solid fa-circle-play me-2"></i>Découvrir
                        </a>
                    </div>
                </div>
            </div>

            <!-- Illustration droite -->
            <div class="col-lg-6 hero-content" data-aos="fade-left" data-aos-duration="700" data-aos-delay="200">
                <div class="hero-illustration">
                    <div class="text-center mb-4">
                        <div style="width:80px;height:80px;background:rgba(255,255,255,.2);border-radius:20px;
                                    display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                            <i class="fa-solid fa-id-card fa-2x text-white"></i>
                        </div>
                        <p class="text-white fw-semibold mb-0" style="font-family:'Poppins',sans-serif;">
                            Carte Élève Numérique
                        </p>
                        <p class="text-white small" style="opacity:.7;">Format IUE ISO 7064 MOD 97-10</p>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="hero-stat-pill">
                            <i class="fa-solid fa-users fa-lg text-warning"></i>
                            <div>
                                <div class="num">3,2M+</div>
                                <div class="lbl">Élèves immatriculés</div>
                            </div>
                        </div>
                        <div class="hero-stat-pill">
                            <i class="fa-solid fa-school fa-lg" style="color:#a5f3c0;"></i>
                            <div>
                                <div class="num">8 500+</div>
                                <div class="lbl">Établissements couverts</div>
                            </div>
                        </div>
                        <div class="hero-stat-pill">
                            <i class="fa-solid fa-map-location-dot fa-lg" style="color:#93c5fd;"></i>
                            <div>
                                <div class="num">18</div>
                                <div class="lbl">Provinces du Burundi</div>
                            </div>
                        </div>
                        <div class="hero-stat-pill">
                            <i class="fa-solid fa-shield-halved fa-lg" style="color:#fca5a5;"></i>
                            <div>
                                <div class="num">99,9%</div>
                                <div class="lbl">Disponibilité du système</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Indicateur scroll -->
    <div class="scroll-down">
        <i class="fa-solid fa-chevron-down d-block"></i>
        <span>Découvrir</span>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     CHIFFRES CLÉS — compteurs animés
     ═══════════════════════════════════════════════════════ -->
<section class="stats-section" id="chiffres">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow" style="color:var(--bi-red);">Chiffres clés</span>
            <h2 class="section-title mt-2">Le FIE en quelques chiffres</h2>
            <p class="text-muted mx-auto" style="max-width:520px;">
                Des statistiques nationales en temps réel pour piloter l'éducation burundaise.
            </p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="0">
                <div class="stat-box stat-box--red">
                    <div class="stat-icon-wrap">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-number" data-counter="3200000" data-suffix="M+">0</div>
                    <div class="stat-label">Élèves immatriculés</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-box stat-box--green">
                    <div class="stat-icon-wrap">
                        <i class="fa-solid fa-school"></i>
                    </div>
                    <div class="stat-number" data-counter="8500" data-suffix="+">0</div>
                    <div class="stat-label">Établissements</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-box stat-box--blue">
                    <div class="stat-icon-wrap">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div class="stat-number" data-counter="18" data-suffix="">0</div>
                    <div class="stat-label">Provinces couvertes</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-box stat-box--gold">
                    <div class="stat-icon-wrap">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="stat-number" data-counter="99" data-suffix="%">0</div>
                    <div class="stat-label">Taux de couverture IUE</div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     FONCTIONNALITÉS / SERVICES
     ═══════════════════════════════════════════════════════ -->
<section class="features-section" id="fonctionnalites">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow" style="color:var(--bi-green);">Nos services</span>
            <h2 class="section-title mt-2">Tout ce dont vous avez besoin</h2>
            <p class="text-muted mx-auto" style="max-width:540px;">
                Le FIE offre une suite complète de services pour la gestion des élèves
                et l'interopérabilité avec le système StatEduc.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="0">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#ffeef0;color:var(--bi-red);">
                        <i class="fa-solid fa-fingerprint"></i>
                    </div>
                    <h5>Immatriculation IUE</h5>
                    <p>Attribution automatique d'un Identifiant Unique Élève au format
                       <code>BI-SSSS-AAAA-NNNNNN-CC</code>, certifié ISO 7064 MOD 97-10.
                       Zéro doublon garanti.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="80">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#eaf9ed;color:var(--bi-green);">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h5>Recherche avancée</h5>
                    <p>Trouvez instantanément un élève par nom, IUE, province, commune ou
                       établissement. Filtres multicritères et export des résultats.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="160">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#e8f0fe;color:#1a73e8;">
                        <i class="fa-solid fa-rotate"></i>
                    </div>
                    <h5>Interopérabilité StatEduc</h5>
                    <p>Synchronisation bidirectionnelle avec l'API StatEduc et SQL Server
                       ELEVES_AGE_NIVEAU_SEXE. Mode dégradé via import Excel.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="0">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#fff8e1;color:#f0a500;">
                        <i class="fa-solid fa-chart-bar"></i>
                    </div>
                    <h5>Tableaux de bord</h5>
                    <p>KPI nationaux : répartition par province, parité filles/garçons,
                       agrégats par niveau et secteur. Pilotage par les données.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="80">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#f3e8ff;color:#9333ea;">
                        <i class="fa-solid fa-arrows-left-right"></i>
                    </div>
                    <h5>Suivi des mouvements</h5>
                    <p>Traçabilité complète des transferts, abandons, réintégrations et
                       diplômés. L'IUE persiste tout au long de la scolarité.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="160">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#fef3c7;color:#d97706;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h5>Sécurité &amp; Audit</h5>
                    <p>RBAC 7 rôles, CSRF, bcrypt cost 12, sessions sécurisées, journal
                       d'audit complet conforme loi n°1/03-2026 (RGPD Burundi).</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     MISSION / À PROPOS
     ═══════════════════════════════════════════════════════ -->
<section class="about-section" id="mission">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- Illustration gauche -->
            <div class="col-lg-5" data-aos="fade-right">
                <div class="about-img-placeholder">
                    <div class="text-center text-white position-relative z-1">
                        <i class="fa-solid fa-flag fa-4x mb-3" style="opacity:.8;"></i>
                        <p class="fw-bold fs-5" style="font-family:'Poppins',sans-serif;">
                            République du Burundi
                        </p>
                        <div class="d-flex justify-content-center gap-2 mt-2">
                            <span style="width:40px;height:8px;background:#CE1126;border-radius:4px;display:inline-block;"></span>
                            <span style="width:40px;height:8px;background:#fff;border-radius:4px;display:inline-block;"></span>
                            <span style="width:40px;height:8px;background:#1EB53A;border-radius:4px;display:inline-block;"></span>
                        </div>
                        <p class="mt-3 small" style="opacity:.7;">
                            Ministère de l'Éducation Nationale<br>et de la Recherche Scientifique
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contenu droit -->
            <div class="col-lg-7" data-aos="fade-left">
                <span class="section-eyebrow" style="color:var(--bi-red);">Notre mission</span>
                <h2 class="section-title mt-2 mb-3">
                    Un système d'information éducatif<br>au service de chaque enfant
                </h2>
                <p class="text-muted mb-4" style="line-height:1.8;">
                    Le FIE (Fichier Informatisé des Élèves) est la composante centrale du
                    <strong>SIGE Burundi</strong> (Système d'Information et de Gestion de l'Éducation).
                    Développé par la DGESS / MENERS, il garantit à chaque élève
                    un identifiant unique pérenne, quelles que soient ses mobilités scolaires.
                </p>

                <div class="d-flex flex-column gap-2 mb-4">
                    <div class="about-milestone">
                        <div class="about-milestone-icon" style="background:#ffeef0;color:var(--bi-red);">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <div>
                            <h6>Unicité garantie</h6>
                            <p>Chaque élève reçoit un IUE unique, certifié par clé de contrôle ISO 7064 MOD 97-10, éliminant les doublons.</p>
                        </div>
                    </div>
                    <div class="about-milestone">
                        <div class="about-milestone-icon" style="background:#eaf9ed;color:var(--bi-green);">
                            <i class="fa-solid fa-link"></i>
                        </div>
                        <div>
                            <h6>Interopérabilité nationale</h6>
                            <p>Connexion bidirectionnelle avec StatEduc (API REST + SQL Server) pour une donnée éducative cohérente à l'échelle nationale.</p>
                        </div>
                    </div>
                    <div class="about-milestone">
                        <div class="about-milestone-icon" style="background:#e8f0fe;color:#1a73e8;">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </div>
                        <div>
                            <h6>Conformité RGPD Burundi</h6>
                            <p>Traitement des données conforme à la loi n°1/03-2026, avec audit trail complet et conservation 5 ans.</p>
                        </div>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/connexion" class="btn btn-lg text-white fw-semibold px-4"
                   style="background:var(--bi-red);border-radius:50px;">
                    <i class="fa-solid fa-arrow-right me-2"></i>Accéder au portail
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     ACTUALITÉS
     ═══════════════════════════════════════════════════════ -->
<section class="news-section" id="actualites">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3" data-aos="fade-up">
            <div>
                <span class="section-eyebrow" style="color:var(--bi-green);">Actualités</span>
                <h2 class="section-title mt-2 mb-0">Dernières nouvelles du FIE</h2>
            </div>
            <a href="<?= BASE_URL ?>/aide" class="btn btn-outline-secondary btn-sm px-4 rounded-pill">
                Voir tout <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <!-- Actualité 1 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
                <div class="news-card card">
                    <div class="news-img" style="background:linear-gradient(135deg,#ffeef0,#ffd6db);">
                        <i class="fa-solid fa-rocket" style="color:var(--bi-red);"></i>
                    </div>
                    <div class="card-body p-4">
                        <span class="news-category text-danger">Déploiement</span>
                        <h5 class="card-title mt-2 mb-2">
                            Lancement du FIE v1.0 — Immatriculation IUE opérationnelle
                        </h5>
                        <p class="text-muted small mb-3" style="line-height:1.6;">
                            Le système d'immatriculation est désormais actif dans les 18 provinces.
                            Chaque inscription génère automatiquement un IUE certifié.
                        </p>
                        <span class="news-date"><i class="fa-regular fa-calendar me-1"></i>Janvier 2026</span>
                    </div>
                </div>
            </div>
            <!-- Actualité 2 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="news-card card">
                    <div class="news-img" style="background:linear-gradient(135deg,#eaf9ed,#c8f0d2);">
                        <i class="fa-solid fa-rotate" style="color:var(--bi-green);"></i>
                    </div>
                    <div class="card-body p-4">
                        <span class="news-category" style="color:var(--bi-green);">Interopérabilité</span>
                        <h5 class="card-title mt-2 mb-2">
                            Connexion StatEduc API REST — synchronisation établissements active
                        </h5>
                        <p class="text-muted small mb-3" style="line-height:1.6;">
                            8 500+ établissements synchronisés depuis l'API StatEduc.
                            Mise à jour incrémentale quotidienne opérationnelle.
                        </p>
                        <span class="news-date"><i class="fa-regular fa-calendar me-1"></i>Février 2026</span>
                    </div>
                </div>
            </div>
            <!-- Actualité 3 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="news-card card">
                    <div class="news-img" style="background:linear-gradient(135deg,#e8f0fe,#c5d8fd);">
                        <i class="fa-solid fa-chart-line" style="color:#1a73e8;"></i>
                    </div>
                    <div class="card-body p-4">
                        <span class="news-category text-primary">Statistiques</span>
                        <h5 class="card-title mt-2 mb-2">
                            Premier rapport national de parité — tableau de bord FIE
                        </h5>
                        <p class="text-muted small mb-3" style="line-height:1.6;">
                            Les agrégats par province, niveau et sexe sont désormais
                            disponibles en temps réel via l'API REST FIE.
                        </p>
                        <span class="news-date"><i class="fa-regular fa-calendar me-1"></i>Mars 2026</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     PARTENAIRES
     ═══════════════════════════════════════════════════════ -->
<section class="partners-section" id="partenaires">
    <div class="container">
        <div class="text-center mb-4" data-aos="fade-up">
            <span class="section-eyebrow text-muted">Partenaires &amp; soutiens institutionnels</span>
        </div>
        <div class="row g-3 justify-content-center">
            <div class="col-6 col-md-2" data-aos="fade-up" data-aos-delay="0">
                <div class="partner-logo">
                    <span><i class="fa-solid fa-building-government d-block fs-3 mb-1" style="color:var(--bi-red);"></i>MENERS<br>Burundi</span>
                </div>
            </div>
            <div class="col-6 col-md-2" data-aos="fade-up" data-aos-delay="60">
                <div class="partner-logo">
                    <span><i class="fa-solid fa-globe d-block fs-3 mb-1 text-primary"></i>UNESCO<br>Bujumbura</span>
                </div>
            </div>
            <div class="col-6 col-md-2" data-aos="fade-up" data-aos-delay="120">
                <div class="partner-logo">
                    <span><i class="fa-solid fa-chart-pie d-block fs-3 mb-1" style="color:var(--bi-green);"></i>DGESS<br>SIGE Burundi</span>
                </div>
            </div>
            <div class="col-6 col-md-2" data-aos="fade-up" data-aos-delay="180">
                <div class="partner-logo">
                    <span><i class="fa-solid fa-handshake d-block fs-3 mb-1 text-warning"></i>Partenaires<br>Techniques</span>
                </div>
            </div>
            <div class="col-6 col-md-2" data-aos="fade-up" data-aos-delay="240">
                <div class="partner-logo">
                    <span><i class="fa-solid fa-children d-block fs-3 mb-1" style="color:#9333ea;"></i>UNICEF<br>Éducation</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     CTA FINAL — Vert Burundi
     ═══════════════════════════════════════════════════════ -->
<section class="cta-section">
    <div class="container cta-inner text-center" data-aos="zoom-in">
        <div class="d-inline-flex align-items-center justify-content-center mb-4"
             style="width:80px;height:80px;background:rgba(255,255,255,.2);border-radius:20px;">
            <i class="fa-solid fa-graduation-cap fa-2x text-white"></i>
        </div>
        <h2 class="fw-bold mb-3" style="font-size:clamp(1.5rem,3vw,2.25rem);">
            Prêt à rejoindre le FIE ?
        </h2>
        <p class="mb-4 mx-auto" style="max-width:520px;">
            Connectez-vous au portail d'administration FIE pour gérer les inscriptions,
            rechercher des élèves et piloter votre établissement ou province.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= BASE_URL ?>/connexion"
               class="btn btn-lg fw-bold px-5 rounded-pill"
               style="background:#fff;color:var(--bi-green);">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Se connecter
            </a>
            <a href="<?= BASE_URL ?>/aide"
               class="btn btn-lg fw-semibold px-5 rounded-pill"
               style="border:2px solid rgba(255,255,255,.6);color:#fff;">
                <i class="fa-solid fa-book me-2"></i>Documentation
            </a>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     FOOTER RICHE
     ═══════════════════════════════════════════════════════ -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Branding -->
            <div class="col-lg-4">
                <div class="footer-brand mb-1">
                    <i class="fa-solid fa-graduation-cap me-2" style="color:var(--bi-red);"></i>FIE Burundi
                </div>
                <p class="footer-tagline mb-3">Fichier Informatisé des Élèves</p>
                <p class="small mb-3" style="color:#64748b;line-height:1.7;">
                    Système national d'immatriculation des élèves du Burundi.
                    Développé par la DGESS / MENERS dans le cadre du SIGE Burundi.
                </p>
                <!-- Bande tricolore mini -->
                <div style="width:60px;height:4px;border-radius:2px;background:linear-gradient(to right,#CE1126 33%,#fff 33%,#fff 66%,#1EB53A 66%);margin-bottom:1rem;"></div>
                <div class="footer-social d-flex gap-2">
                    <a href="#" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <!-- Liens rapides -->
            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="footer-heading">Navigation</h6>
                <a href="<?= BASE_URL ?>/" class="footer-link">Accueil</a>
                <a href="#fonctionnalites" class="footer-link">Services</a>
                <a href="#mission" class="footer-link">Mission</a>
                <a href="#actualites" class="footer-link">Actualités</a>
                <a href="#partenaires" class="footer-link">Partenaires</a>
            </div>

            <!-- Portail -->
            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="footer-heading">Portail</h6>
                <a href="<?= BASE_URL ?>/connexion" class="footer-link">Connexion</a>
                <a href="<?= BASE_URL ?>/tableau-de-bord" class="footer-link">Tableau de bord</a>
                <a href="<?= BASE_URL ?>/inscription/nouveau" class="footer-link">Nouvelle inscription</a>
                <a href="<?= BASE_URL ?>/inscription/recherche" class="footer-link">Rechercher un élève</a>
            </div>

            <!-- Aide & Légal -->
            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="footer-heading">Aide</h6>
                <a href="<?= BASE_URL ?>/aide" class="footer-link">Documentation</a>
                <a href="<?= BASE_URL ?>/contact" class="footer-link">Support SIGE</a>
                <a href="<?= BASE_URL ?>/confidentialite" class="footer-link">Confidentialité</a>
                <a href="<?= BASE_URL ?>/mentions-legales" class="footer-link">Mentions légales</a>
            </div>

            <!-- Contact -->
            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="footer-heading">Contact</h6>
                <p class="small mb-2" style="color:#64748b;line-height:1.6;">
                    <i class="fa-solid fa-building me-1"></i>
                    DGESS / MENERS<br>Bujumbura, Burundi
                </p>
                <p class="small" style="color:#64748b;line-height:1.6;">
                    <i class="fa-solid fa-clock me-1"></i>
                    Lun–Ven : 07h30–16h00<br>
                    <span style="font-size:.75rem;">(UTC+2)</span>
                </p>
            </div>
        </div>

        <hr class="footer-divider">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <p class="footer-copyright mb-0">
                © <?= date('Y') ?> DGESS / MENERS Burundi — FIE v1.0.0
                · Tous droits réservés
                · <span style="color:#1EB53A;">●</span> Système opérationnel
            </p>
            <p class="footer-copyright mb-0">
                Bootstrap 5.3 · Font Awesome 6.5 · PHP 8.1+
            </p>
        </div>
    </div>
</footer>


<!-- ═══════════════════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════════════════ -->
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmMFkBg1VXt6DdWX5Fj3hZJzmZVZ"
        crossorigin="anonymous"></script>

<!-- AOS — Animate On Scroll -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
/* ── Init AOS ── */
AOS.init({
    once: true,
    duration: 650,
    easing: 'ease-out-cubic',
    offset: 60
});

/* ── Navbar ombre au scroll ── */
(function() {
    var nav = document.getElementById('mainNav');
    window.addEventListener('scroll', function() {
        nav.classList.toggle('scrolled', window.scrollY > 30);
    }, { passive: true });
})();

/* ── Compteurs animés ── */
(function() {
    function formatNum(n, suffix) {
        if (suffix === 'M+') {
            var m = n / 1000000;
            return m.toFixed(1) + 'M+';
        }
        return n.toLocaleString('fr-FR') + suffix;
    }

    function animateCounter(el) {
        var target = parseInt(el.dataset.counter, 10);
        var suffix = el.dataset.suffix || '';
        var duration = 1800;
        var start = performance.now();
        function step(now) {
            var elapsed = now - start;
            var progress = Math.min(elapsed / duration, 1);
            // easing easeOutExpo
            var ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            var current = Math.round(target * ease);
            el.textContent = formatNum(current, suffix);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    var counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });

    counters.forEach(function(c) { observer.observe(c); });
})();

/* ── Smooth scroll pour ancres internes ── */
document.querySelectorAll('a[href^="#"]').forEach(function(a) {
    a.addEventListener('click', function(e) {
        var target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

</body>
</html>
