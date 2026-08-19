<?php
/**
 * FIE — Site public · Page d'accueil INSTITUTIONNELLE — REFONTE COMPLÈTE v2.0
 * ─────────────────────────────────────────────────────────────────────────────
 * Stack         : Bootstrap 5.3.3 + Font Awesome 6.5.2 + AOS 2.3.4
 * Polices       : Poppins (700–900 titres) + Open Sans (400–600 corps) via Google Fonts
 * Charte        : Bleu Royal #1a56db · Cyan #0891b2 · Blanc #FFFFFF · Vert #059669
 * Animations    : AOS scroll + compteurs easeOutExpo + navbar scroll-shadow
 *                 + hover cards elevation + scroll-indicator + typed text
 * Accessibilité : prefers-reduced-motion respecté (CSS + JS)
 * Page standalone — inclut ses propres CDN (pas du layout admin)
 * ─────────────────────────────────────────────────────────────────────────────
 */
?><!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Le Fichier Informatisé des Élèves (FIE) attribue un Identifiant Unique (IUE) à chaque apprenant du système éducatif burundais. Géré par la DGESS / MENERS — SIGE Burundi.">
    <meta name="keywords" content="FIE, Burundi, éducation, SIGE, IUE, DGESS, MENERS, élèves, immatriculation">
    <meta name="theme-color" content="#CE1126">
    <meta property="og:title" content="FIE Burundi — Fichier Informatisé des Élèves">
    <meta property="og:description" content="Système national d'immatriculation des élèves du Burundi — DGESS / MENERS.">
    <meta property="og:type" content="website">
    <title>FIE — Fichier Informatisé des Élèves du Burundi | SIGE</title>

    <!-- ═══ Google Fonts (preconnect pour performance) ═══════════════════════ -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- ═══ Bootstrap 5.3.3 ══════════════════════════════════════════════════ -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    <!-- ═══ Font Awesome 6.5.2 ═══════════════════════════════════════════════ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- ═══ AOS — Animate On Scroll 2.3.4 ═══════════════════════════════════ -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

    <!-- ═══ FIE CSS charte Burundi ════════════════════════════════════════════ -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/fie.css">

    <style>
    /* ══════════════════════════════════════════════════════════════════════════
       TOKENS & FONDATION — Charte FIE Bleu Royal v3.0
       Palette complémentaire :
         Bleu Royal #1a56db  → Primaire institutionnel, confiance, technologie
         Bleu Foncé #0f2749  → Backgrounds sombres, gradients profonds
         Cyan #0891b2        → Secondaire, frais, numérique
         Émeraude #059669    → Succès, vert données positives
         Ambre #d97706       → Stats chaudes, KPIs clés
         Violet #7c3aed      → Accent CTA, mouvements, transferts
       ══════════════════════════════════════════════════════════════════════════ */
    :root {
        /* ── Rouge Burundi (drapeau : Rouge · Blanc · Vert) ── */
        --bi-red:        #CE1126;   /* Rouge Burundi officiel */
        --bi-red-d:      #a00e1b;
        --bi-red-l:      #fce8ea;
        --bi-red-m:      #f5a8b0;

        /* ── Palette étendue (bleu institutionnel conservé séparément) ── */
        --bi-blue:       #1a56db;
        --bi-blue-d:     #1343a8;
        --bi-blue-l:     #eff6ff;
        --bi-navy:       #0f2749;
        --bi-cyan:       #0891b2;
        --bi-cyan-l:     #e0f2fe;
        --bi-emerald:    #059669;
        --bi-emerald-l:  #d1fae5;
        --bi-amber:      #d97706;
        --bi-amber-l:    #fef3c7;
        --bi-violet:     #7c3aed;
        --bi-violet-l:   #ede9fe;

        /* ── Garder le vert Burundi pour les éléments de drapeau ── */
        --bi-green:      #1EB53A;
        --bi-green-d:    #178a2b;
        --bi-green-l:    #eaf9ed;

        --bi-white:      #FFFFFF;
        --bi-dark:       #0d1b2a;
        --bi-dark-2:     #0f2749;
        --bi-text:       #1e293b;
        --bi-muted:      #64748b;
        --bi-border:     #e2e8f0;
        --bi-bg-light:   #f8fafc;
        --bi-shadow-sm:  0 2px 8px rgba(0,0,0,.08);
        --bi-shadow-md:  0 8px 30px rgba(0,0,0,.12);
        --bi-shadow-lg:  0 20px 60px rgba(0,0,0,.15);
        --bi-radius:     1rem;
        --bi-radius-lg:  1.5rem;
        --bi-transition: .3s cubic-bezier(.25,.8,.25,1);
    }

    *, *::before, *::after { box-sizing: border-box; }

    html { scroll-behavior: smooth; }

    body {
        font-family: 'Open Sans', system-ui, -apple-system, sans-serif;
        color: var(--bi-text);
        background: #fff;
        overflow-x: hidden;
        line-height: 1.7;
    }

    h1,h2,h3,h4,h5,h6,
    .poppins { font-family: 'Poppins', system-ui, sans-serif; }

    /* ── UTILITAIRES ────────────────────────────────────────────────────────── */
    .text-bi-blue  { color: var(--bi-blue) !important; }
    .text-bi-cyan  { color: var(--bi-cyan) !important; }
    .text-bi-green { color: var(--bi-green) !important; }
    .text-bi-amber { color: var(--bi-amber) !important; }
    .bg-bi-blue    { background-color: var(--bi-blue) !important; }
    .bg-bi-cyan    { background-color: var(--bi-cyan) !important; }
    .bg-bi-green   { background-color: var(--bi-green) !important; }

    .section-eyebrow {
        font-family: 'Poppins', sans-serif;
        font-size: .7rem; font-weight: 700;
        letter-spacing: .14em; text-transform: uppercase;
        display: inline-block;
    }

    .section-title {
        font-family: 'Poppins', sans-serif;
        font-size: clamp(1.6rem, 3.5vw, 2.5rem);
        font-weight: 800;
        color: var(--bi-dark);
        line-height: 1.2;
    }

    .section-lead {
        font-size: 1.05rem;
        color: var(--bi-muted);
        line-height: 1.75;
        max-width: 580px;
    }

    .badge-pill {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .35rem .9rem;
        border-radius: 50px;
        font-family: 'Poppins', sans-serif;
        font-size: .72rem; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase;
    }

    .badge-pill--red   { background: var(--bi-red-l); color: var(--bi-red); }
    .badge-pill--green { background: var(--bi-green-l); color: var(--bi-green-d); }
    .badge-pill--blue  { background: #e8f0fe; color: #1a73e8; }
    .badge-pill--gold  { background: #fff8e1; color: #b45309; }

    /* Diviseur tricolore décoratif */
    .tri-divider {
        display: inline-block;
        width: 60px; height: 4px; border-radius: 2px;
        background: linear-gradient(to right, var(--bi-red) 33%,#fff 33%,#fff 66%,var(--bi-green) 66%);
    }

    /* ══════════════════════════════════════════════════════════════════════════
       TOPBAR INSTITUTIONNELLE (barre grise au-dessus de la navbar)
       ══════════════════════════════════════════════════════════════════════════ */
    #topBar {
        background: var(--bi-dark-2);
        color: rgba(255,255,255,.7);
        font-size: .75rem;
        padding: .35rem 0;
        line-height: 1.4;
    }
    #topBar a { color: rgba(255,255,255,.75); text-decoration: none; }
    #topBar a:hover { color: #fff; }
    #topBar .separator { margin: 0 .6rem; opacity: .35; }

    /* ══════════════════════════════════════════════════════════════════════════
       BANDE DRAPEAU TRICOLORE
       ══════════════════════════════════════════════════════════════════════════ */
    .flag-strip {
        height: 4px;
        background: linear-gradient(to right,
            var(--bi-red)   0%    33.33%,
            #fff            33.33% 66.66%,
            var(--bi-green) 66.66% 100%);
        flex-shrink: 0;
    }

    /* ══════════════════════════════════════════════════════════════════════════
       NAVBAR STICKY
       ══════════════════════════════════════════════════════════════════════════ */
    #mainNav {
        background: var(--bi-red);
        padding-top: .9rem;
        padding-bottom: .9rem;
        transition: padding var(--bi-transition), box-shadow var(--bi-transition);
        position: sticky;
        top: 0;
        z-index: 1030;
    }

    #mainNav.scrolled {
        box-shadow: 0 4px 20px rgba(206,17,38,.35);
        padding-top: .5rem;
        padding-bottom: .5rem;
    }

    #mainNav .navbar-brand {
        font-family: 'Poppins', sans-serif;
        font-weight: 800; font-size: 1.25rem;
        color: #fff; text-decoration: none;
        display: flex; align-items: center; gap: .65rem;
    }

    .nav-logo-box {
        width: 40px; height: 40px;
        background: rgba(255,255,255,.2);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: background var(--bi-transition);
    }
    #mainNav:hover .nav-logo-box { background: rgba(255,255,255,.28); }

    #mainNav .nav-link {
        color: rgba(255,255,255,.85) !important;
        font-family: 'Open Sans', sans-serif;
        font-size: .875rem; font-weight: 600;
        padding: .45rem .8rem !important;
        border-radius: 6px;
        transition: background var(--bi-transition), color var(--bi-transition);
    }
    #mainNav .nav-link:hover,
    #mainNav .nav-link.active {
        color: #fff !important;
        background: rgba(255,255,255,.18);
    }

    .navbar-toggler { border: none !important; padding: .3rem .5rem; }
    .navbar-toggler:focus { box-shadow: none !important; }

    /* Bouton Connexion en pill blanc */
    .btn-nav-login {
        background: #fff;
        color: var(--bi-red) !important;
        font-family: 'Poppins', sans-serif;
        font-weight: 700 !important; font-size: .85rem !important;
        padding: .45rem 1.3rem !important;
        border-radius: 50px !important;
        border: none;
        transition: transform var(--bi-transition), box-shadow var(--bi-transition) !important;
        white-space: nowrap;
    }
    .btn-nav-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,.25);
        background: #f8f8f8 !important;
    }

    /* ══════════════════════════════════════════════════════════════════════════
       HERO — Plein écran avec fond dégradé + motif + vague
       ══════════════════════════════════════════════════════════════════════════ */
    .hero-section {
        min-height: calc(100vh - 112px); /* minus topbar + navbar + flagstrip */
        background: linear-gradient(145deg,
            #0c1f3f 0%,
            #0f2749 20%,
            #1343a8 50%,
            #1a56db 75%,
            #0891b2 100%);
        display: flex; align-items: center;
        position: relative; overflow: hidden;
        padding: 4rem 0 7rem;
    }

    /* Motif géométrique en fond */
    .hero-section::before {
        content: '';
        position: absolute; inset: 0; pointer-events: none;
        background-image:
            radial-gradient(ellipse at 15% 85%, rgba(8,145,178,.18) 0%, transparent 55%),
            radial-gradient(ellipse at 85% 15%, rgba(124,58,237,.15) 0%, transparent 50%),
            radial-gradient(ellipse at 50% 50%, rgba(255,255,255,.04) 0%, transparent 70%),
            url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='20' cy='20' r='8'/%3E%3Ccircle cx='80' cy='20' r='8'/%3E%3Ccircle cx='20' cy='80' r='8'/%3E%3Ccircle cx='80' cy='80' r='8'/%3E%3Ccircle cx='50' cy='50' r='12'/%3E%3C/g%3E%3C/svg%3E");
    }

    /* Vague blanche en bas */
    .hero-section::after {
        content: '';
        position: absolute; bottom: -1px; left: 0; right: 0;
        height: 90px;
        background: #fff;
        clip-path: ellipse(56% 100% at 50% 100%);
    }

    .hero-content { position: relative; z-index: 2; }

    .hero-badge {
        display: inline-flex; align-items: center; gap: .5rem;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.3);
        color: rgba(255,255,255,.95);
        font-family: 'Poppins', sans-serif;
        font-size: .72rem; font-weight: 700;
        letter-spacing: .08em; text-transform: uppercase;
        padding: .38rem 1rem;
        border-radius: 50px;
        margin-bottom: 1.4rem;
        animation: heroBadgeIn .8s ease both;
    }
    @keyframes heroBadgeIn {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .hero-title {
        font-family: 'Poppins', sans-serif;
        font-size: clamp(2.1rem, 5.5vw, 3.75rem);
        font-weight: 900;
        color: #fff;
        line-height: 1.1;
        margin-bottom: 1.4rem;
        letter-spacing: -.02em;
    }
    .hero-title .accent { color: #ffd166; }
    .hero-title .line-under {
        position: relative; display: inline-block;
    }
    .hero-title .line-under::after {
        content: '';
        position: absolute; left: 0; bottom: -3px;
        width: 100%; height: 3px;
        background: var(--bi-green);
        border-radius: 2px;
        animation: lineGrow .9s ease .8s both;
        transform-origin: left;
    }
    @keyframes lineGrow {
        from { transform: scaleX(0); }
        to   { transform: scaleX(1); }
    }

    .hero-subtitle {
        font-size: clamp(.95rem, 1.8vw, 1.15rem);
        color: rgba(255,255,255,.88);
        line-height: 1.75;
        margin-bottom: 2.2rem;
    }

    .hero-iue {
        display: inline-block;
        font-family: 'Courier New', monospace;
        font-size: .95rem;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.22);
        color: #ffd166;
        padding: .5rem 1.25rem;
        border-radius: 8px;
        letter-spacing: .08em;
        margin-bottom: 2rem;
    }

    .btn-hero-primary {
        display: inline-flex; align-items: center; gap: .5rem;
        background: #fff;
        color: var(--bi-red);
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: 1rem;
        padding: .8rem 2.2rem;
        border-radius: 50px;
        text-decoration: none; border: none;
        transition: transform var(--bi-transition), box-shadow var(--bi-transition);
        box-shadow: 0 6px 20px rgba(0,0,0,.3);
    }
    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0,0,0,.4);
        color: var(--bi-red-d);
    }

    .btn-hero-outline {
        display: inline-flex; align-items: center; gap: .5rem;
        background: transparent;
        color: rgba(255,255,255,.92);
        font-family: 'Poppins', sans-serif;
        font-weight: 600; font-size: 1rem;
        padding: .8rem 2rem;
        border-radius: 50px;
        text-decoration: none;
        border: 2px solid rgba(255,255,255,.5);
        transition: background var(--bi-transition), border-color var(--bi-transition);
    }
    .btn-hero-outline:hover {
        background: rgba(255,255,255,.15);
        border-color: rgba(255,255,255,.9);
        color: #fff;
    }

    /* Carte "ID numérique" flottante à droite du hero */
    .hero-card {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: var(--bi-radius-lg);
        padding: 2rem;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .hero-id-card {
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.22);
        border-radius: var(--bi-radius);
        padding: 1.5rem;
        position: relative; overflow: hidden;
    }
    .hero-id-card::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(to right, var(--bi-red), var(--bi-green));
    }

    .hero-pill {
        display: flex; align-items: center; gap: .85rem;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 12px;
        padding: .75rem 1rem;
        color: #fff;
        transition: background var(--bi-transition);
    }
    .hero-pill:hover { background: rgba(255,255,255,.22); }
    .hero-pill .hp-num {
        font-family: 'Poppins', sans-serif;
        font-weight: 800; font-size: 1.35rem; line-height: 1;
    }
    .hero-pill .hp-lbl {
        font-size: .72rem; opacity: .8; line-height: 1.3;
        font-weight: 500;
    }

    /* Scroll indicator animé */
    .scroll-indicator {
        position: absolute; bottom: 100px; left: 50%;
        transform: translateX(-50%);
        z-index: 3;
        display: flex; flex-direction: column; align-items: center; gap: .3rem;
        color: rgba(255,255,255,.5);
        font-size: .7rem; letter-spacing: .08em;
        text-transform: uppercase;
        animation: scrollBounce 2.5s ease-in-out infinite;
    }
    @keyframes scrollBounce {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50%       { transform: translateX(-50%) translateY(8px); }
    }

    /* ══════════════════════════════════════════════════════════════════════════
       BANDEAU CONFIANCE (logos / accréditations)
       ══════════════════════════════════════════════════════════════════════════ */
    .trust-band {
        background: var(--bi-bg-light);
        border-top: 1px solid var(--bi-border);
        border-bottom: 1px solid var(--bi-border);
        padding: 1.5rem 0;
    }
    .trust-band .trust-item {
        display: flex; align-items: center; gap: .6rem;
        color: var(--bi-muted); font-size: .85rem; font-weight: 600;
        white-space: nowrap;
    }
    .trust-band .trust-item i {
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    /* ══════════════════════════════════════════════════════════════════════════
       CHIFFRES CLÉS (compteurs animés)
       ══════════════════════════════════════════════════════════════════════════ */
    .stats-section {
        background: #fff;
        padding: 5.5rem 0;
    }
    .stat-card {
        background: #fff;
        border: 1px solid var(--bi-border);
        border-radius: var(--bi-radius);
        padding: 2.25rem 1.5rem 2rem;
        text-align: center;
        transition: transform var(--bi-transition), box-shadow var(--bi-transition), border-color var(--bi-transition);
        position: relative; overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 3px 3px 0 0;
    }
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--bi-shadow-lg);
        border-color: transparent;
    }
    .stat-card--red::before   { background: var(--bi-blue); }
    .stat-card--green::before { background: var(--bi-cyan); }
    .stat-card--blue::before  { background: var(--bi-emerald); }
    .stat-card--gold::before  { background: var(--bi-amber); }

    .stat-icon {
        width: 72px; height: 72px; border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.75rem; margin: 0 auto 1.2rem;
        transition: transform var(--bi-transition);
    }
    .stat-card:hover .stat-icon { transform: scale(1.12) rotate(-5deg); }
    .stat-card--red   .stat-icon { background: var(--bi-blue-l);    color: var(--bi-blue); }
    .stat-card--green .stat-icon { background: var(--bi-cyan-l);    color: var(--bi-cyan); }
    .stat-card--blue  .stat-icon { background: var(--bi-emerald-l); color: var(--bi-emerald); }
    .stat-card--gold  .stat-icon { background: var(--bi-amber-l);   color: var(--bi-amber); }

    .stat-number {
        font-family: 'Poppins', sans-serif;
        font-size: 2.75rem; font-weight: 900;
        line-height: 1; margin-bottom: .3rem;
    }
    .stat-card--red   .stat-number { color: var(--bi-blue); }
    .stat-card--green .stat-number { color: var(--bi-cyan); }
    .stat-card--blue  .stat-number { color: var(--bi-emerald); }
    .stat-card--gold  .stat-number { color: var(--bi-amber); }
    .stat-sublabel {
        font-size: .78rem; color: var(--bi-muted); font-weight: 500;
        line-height: 1.4;
    }
    .stat-label {
        font-size: .9rem; color: var(--bi-text); font-weight: 600;
        margin-bottom: .2rem;
    }

    /* ══════════════════════════════════════════════════════════════════════════
       FONCTIONNALITÉS / SERVICES
       ══════════════════════════════════════════════════════════════════════════ */
    .features-section {
        background: var(--bi-bg-light);
        padding: 5.5rem 0;
    }

    .feat-card {
        background: #fff;
        border: 1px solid var(--bi-border);
        border-radius: var(--bi-radius);
        padding: 2rem 1.75rem;
        height: 100%;
        transition: transform var(--bi-transition), box-shadow var(--bi-transition), border-color var(--bi-transition);
        display: flex; flex-direction: column;
    }
    .feat-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--bi-shadow-md);
        border-color: transparent;
    }

    .feat-icon {
        width: 58px; height: 58px; border-radius: 15px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; margin-bottom: 1.25rem; flex-shrink: 0;
        transition: transform var(--bi-transition);
    }
    .feat-card:hover .feat-icon { transform: scale(1.15) rotate(-5deg); }

    .feat-card h5 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: 1rem;
        color: var(--bi-dark); margin-bottom: .5rem;
    }
    .feat-card p {
        font-size: .875rem; color: var(--bi-muted);
        line-height: 1.7; margin: 0; flex: 1;
    }
    .feat-link {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .8rem; font-weight: 700;
        text-decoration: none;
        margin-top: 1rem;
        transition: gap var(--bi-transition);
    }
    .feat-link:hover { gap: .6rem; }

    /* ══════════════════════════════════════════════════════════════════════════
       SECTION COMMENT ÇA MARCHE (processus IUE)
       ══════════════════════════════════════════════════════════════════════════ */
    .how-section {
        background: #fff;
        padding: 5.5rem 0;
    }

    .how-step {
        position: relative;
        text-align: center;
        padding: 0 1rem;
    }
    .how-step::after {
        content: '\f054'; /* fa-chevron-right */
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute; top: 28px; right: -12px;
        font-size: .9rem; color: var(--bi-muted);
        opacity: .5;
    }
    .how-step:last-child::after { display: none; }

    .how-step-num {
        width: 64px; height: 64px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-family: 'Poppins', sans-serif;
        font-weight: 900; font-size: 1.4rem;
        margin: 0 auto 1rem;
        position: relative;
        transition: transform var(--bi-transition), box-shadow var(--bi-transition);
        cursor: default;
    }
    .how-step-num:hover { transform: scale(1.08); box-shadow: var(--bi-shadow-md); }
    .how-step-num--red   { background: var(--bi-blue);    color: #fff; }
    .how-step-num--green { background: var(--bi-emerald); color: #fff; }
    .how-step-num--blue  { background: var(--bi-cyan);    color: #fff; }
    .how-step-num--gold  { background: var(--bi-amber);   color: #fff; }

    .how-step h6 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: .95rem;
        color: var(--bi-dark); margin-bottom: .4rem;
    }
    .how-step p {
        font-size: .82rem; color: var(--bi-muted); line-height: 1.6; margin: 0;
    }

    /* ══════════════════════════════════════════════════════════════════════════
       SECTION MISSION / À PROPOS
       ══════════════════════════════════════════════════════════════════════════ */
    .about-section {
        background: var(--bi-bg-light);
        padding: 5.5rem 0;
    }

    .about-visual {
        border-radius: var(--bi-radius-lg);
        background: linear-gradient(145deg, #0f2749 0%, var(--bi-blue-d) 40%, var(--bi-blue) 75%, var(--bi-cyan) 100%);
        min-height: 400px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 3rem 2rem;
        position: relative; overflow: hidden;
    }
    .about-visual::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='40' cy='40' r='30'/%3E%3Ccircle cx='40' cy='40' r='20'/%3E%3Ccircle cx='40' cy='40' r='10'/%3E%3C/g%3E%3C/svg%3E");
    }
    .about-visual-inner { position: relative; z-index: 1; text-align: center; padding: 2rem; }

    .milestone {
        display: flex; align-items: flex-start; gap: 1rem;
        padding: 1rem 1.25rem;
        border-radius: var(--bi-radius);
        background: #fff;
        border: 1px solid var(--bi-border);
        transition: transform var(--bi-transition), box-shadow var(--bi-transition);
        margin-bottom: .75rem;
    }
    .milestone:hover { transform: translateX(6px); box-shadow: var(--bi-shadow-sm); }

    .milestone-icon {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .milestone h6 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: .9rem;
        color: var(--bi-dark); margin-bottom: .2rem;
    }
    .milestone p { font-size: .82rem; color: var(--bi-muted); margin: 0; line-height: 1.55; }

    /* ══════════════════════════════════════════════════════════════════════════
       PROVINCES (carte visuelle)
       ══════════════════════════════════════════════════════════════════════════ */
    .provinces-section {
        background: #fff;
        padding: 5.5rem 0;
    }
    .province-chip {
        background: var(--bi-bg-light);
        border: 1px solid var(--bi-border);
        border-radius: 50px;
        padding: .45rem 1rem;
        font-size: .82rem; font-weight: 600;
        color: var(--bi-text);
        transition: background var(--bi-transition), color var(--bi-transition), border-color var(--bi-transition);
        display: inline-flex; align-items: center; gap: .35rem;
        white-space: nowrap;
    }
    .province-chip:hover {
        background: var(--bi-blue-l);
        border-color: var(--bi-red-m);
        color: var(--bi-blue);
    }
    .province-chip i { font-size: .75rem; }

    /* ══════════════════════════════════════════════════════════════════════════
       ACTUALITÉS
       ══════════════════════════════════════════════════════════════════════════ */
    .news-section {
        background: var(--bi-bg-light);
        padding: 5.5rem 0;
    }
    .news-card {
        border: 1px solid var(--bi-border);
        border-radius: var(--bi-radius);
        overflow: hidden;
        background: #fff;
        transition: transform var(--bi-transition), box-shadow var(--bi-transition);
        height: 100%;
    }
    .news-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--bi-shadow-md);
    }
    .news-thumb {
        height: 190px;
        display: flex; align-items: center; justify-content: center;
        font-size: 3rem;
        position: relative; overflow: hidden;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    .news-thumb img {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform .5s ease;
    }
    .news-card:hover .news-thumb img { transform: scale(1.05); }
    .news-thumb::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.18), transparent);
    }
    .news-category {
        font-family: 'Poppins', sans-serif;
        font-size: .68rem; font-weight: 700;
        letter-spacing: .1em; text-transform: uppercase;
    }
    .news-card .card-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: .97rem;
        color: var(--bi-dark); line-height: 1.4;
        margin-bottom: .5rem;
    }
    .news-date {
        font-size: .78rem; color: var(--bi-muted);
        display: flex; align-items: center; gap: .3rem;
    }
    .news-read-more {
        font-size: .8rem; font-weight: 700; font-family: 'Poppins', sans-serif;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: .3rem;
        transition: gap var(--bi-transition);
    }
    .news-read-more:hover { gap: .6rem; }

    /* ══════════════════════════════════════════════════════════════════════════
       TÉMOIGNAGES / PARTENAIRES
       ══════════════════════════════════════════════════════════════════════════ */
    .partners-section {
        background: #fff;
        padding: 4.5rem 0;
    }
    .partner-logo {
        height: 72px;
        background: var(--bi-bg-light);
        border: 1px solid var(--bi-border);
        border-radius: var(--bi-radius);
        display: flex; align-items: center; justify-content: center;
        padding: 1rem 1.5rem;
        transition: background var(--bi-transition), box-shadow var(--bi-transition), transform var(--bi-transition);
        cursor: default;
    }
    .partner-logo:hover {
        background: #fff;
        box-shadow: var(--bi-shadow-md);
        transform: translateY(-4px);
    }
    .partner-logo span {
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: .78rem;
        color: var(--bi-muted); letter-spacing: .04em;
        text-align: center; line-height: 1.3;
    }
    .partner-logo i {
        font-size: 1.4rem;
        margin-right: .5rem;
        color: var(--bi-muted);
    }

    /* Ticker défilant partenaires */
    .partner-ticker-wrap {
        overflow: hidden; position: relative;
    }
    .partner-ticker-wrap::before,
    .partner-ticker-wrap::after {
        content: '';
        position: absolute; top: 0; bottom: 0; width: 60px; z-index: 1;
    }
    .partner-ticker-wrap::before { left: 0;  background: linear-gradient(to right, #fff, transparent); }
    .partner-ticker-wrap::after  { right: 0; background: linear-gradient(to left,  #fff, transparent); }

    .partner-ticker {
        display: flex; gap: 1.25rem;
        width: max-content;
        animation: tickerScroll 28s linear infinite;
    }
    @keyframes tickerScroll {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }
    .partner-ticker:hover { animation-play-state: paused; }

    /* ══════════════════════════════════════════════════════════════════════════
       CTA — SECTION FINALE (vert Burundi)
       ══════════════════════════════════════════════════════════════════════════ */
    .cta-section {
        background: linear-gradient(135deg, #0f2749 0%, var(--bi-blue-d) 40%, var(--bi-blue) 70%, var(--bi-cyan) 100%);
        padding: 6rem 0;
        position: relative; overflow: hidden;
    }
    .cta-section::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M0 40 L40 0 L80 40 L40 80Z'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .cta-section h2 {
        font-family: 'Poppins', sans-serif;
        font-weight: 800;
        font-size: clamp(1.6rem, 3.5vw, 2.5rem);
        color: #fff; line-height: 1.2;
    }
    .cta-section p { color: rgba(255,255,255,.88); font-size: 1.08rem; line-height: 1.75; }

    .btn-cta-white {
        display: inline-flex; align-items: center; gap: .5rem;
        background: #fff; color: var(--bi-blue-d);
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: 1rem;
        padding: .85rem 2.5rem;
        border-radius: 50px;
        text-decoration: none;
        transition: transform var(--bi-transition), box-shadow var(--bi-transition);
        box-shadow: 0 6px 20px rgba(0,0,0,.25);
    }
    .btn-cta-white:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0,0,0,.35);
        color: var(--bi-blue-d);
    }

    .btn-cta-outline {
        display: inline-flex; align-items: center; gap: .5rem;
        background: transparent; color: rgba(255,255,255,.92);
        font-family: 'Poppins', sans-serif;
        font-weight: 600; font-size: 1rem;
        padding: .85rem 2.2rem;
        border-radius: 50px;
        text-decoration: none;
        border: 2px solid rgba(255,255,255,.55);
        transition: background var(--bi-transition), border-color var(--bi-transition);
    }
    .btn-cta-outline:hover {
        background: rgba(255,255,255,.15);
        border-color: rgba(255,255,255,.9);
        color: #fff;
    }

    /* ══════════════════════════════════════════════════════════════════════════
       FOOTER RICHE
       ══════════════════════════════════════════════════════════════════════════ */
    .site-footer {
        background: var(--bi-dark);
        color: rgba(255,255,255,.75);
        padding: 4.5rem 0 2rem;
    }
    .footer-brand {
        font-family: 'Poppins', sans-serif;
        font-weight: 800; font-size: 1.3rem;
        color: #fff; margin-bottom: .3rem;
    }
    .footer-tagline {
        font-size: .82rem; color: rgba(255,255,255,.5);
        letter-spacing: .06em; text-transform: uppercase;
        margin-bottom: 1rem;
    }
    .footer-heading {
        font-family: 'Poppins', sans-serif;
        font-weight: 700; font-size: .8rem;
        color: rgba(255,255,255,.95);
        letter-spacing: .08em; text-transform: uppercase;
        margin-bottom: .9rem;
    }
    .footer-link {
        display: block;
        color: rgba(255,255,255,.6);
        text-decoration: none; font-size: .875rem;
        padding: .2rem 0;
        transition: color var(--bi-transition), padding-left var(--bi-transition);
    }
    .footer-link:hover {
        color: rgba(255,255,255,.95);
        padding-left: .4rem;
    }
    .footer-social {
        display: flex; gap: .5rem; flex-wrap: wrap;
    }
    .footer-social a {
        width: 36px; height: 36px; border-radius: 10px;
        background: rgba(255,255,255,.08);
        color: rgba(255,255,255,.65);
        display: flex; align-items: center; justify-content: center;
        text-decoration: none; font-size: .9rem;
        transition: background var(--bi-transition), color var(--bi-transition), transform var(--bi-transition);
    }
    .footer-social a:hover {
        background: var(--bi-blue);
        color: #fff;
        transform: translateY(-2px);
    }
    .footer-divider {
        border-color: rgba(255,255,255,.08);
        margin: 2.5rem 0 1.5rem;
    }
    .footer-copyright {
        font-size: .8rem; color: rgba(255,255,255,.4);
    }

    /* ══════════════════════════════════════════════════════════════════════════
       BOUTON RETOUR EN HAUT
       ══════════════════════════════════════════════════════════════════════════ */
    #backToTop {
        position: fixed; bottom: 2rem; right: 2rem;
        width: 46px; height: 46px; border-radius: 50%;
        background: var(--bi-red); color: #fff;
        border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; z-index: 999;
        box-shadow: 0 4px 16px rgba(26,86,219,.4);
        opacity: 0; transform: translateY(20px) scale(.8);
        transition: opacity .3s, transform .3s;
        pointer-events: none;
    }
    #backToTop.visible {
        opacity: 1; transform: translateY(0) scale(1);
        pointer-events: auto;
    }
    #backToTop:hover { background: var(--bi-red-d); transform: translateY(-3px) scale(1.05); }

    /* ══════════════════════════════════════════════════════════════════════════
       ACCESSIBILITÉ — prefers-reduced-motion
       ══════════════════════════════════════════════════════════════════════════ */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
        [data-aos] { opacity: 1 !important; transform: none !important; }
        .partner-ticker { animation: none !important; }
        .scroll-indicator { animation: none !important; }
    }

    /* ══════════════════════════════════════════════════════════════════════════
       RESPONSIVE AJUSTEMENTS
       ══════════════════════════════════════════════════════════════════════════ */
    @media (max-width: 991.98px) {
        .hero-section { padding: 3rem 0 6rem; }
        .hero-card { margin-top: 2.5rem; }
        .trust-band .trust-row { flex-wrap: wrap; gap: 1rem; }
        .how-step::after { display: none; }
    }
    @media (max-width: 575.98px) {
        .hero-title { font-size: 2rem; }
        .stat-number { font-size: 2.2rem; }
        .section-title { font-size: 1.6rem; }
        .cta-section { padding: 4rem 0; }
    }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════════════════════════
     TOPBAR INSTITUTIONNELLE
     ══════════════════════════════════════════════════════════════════════════ -->
<div id="topBar" class="d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span>🇧🇮</span>
            <span>République du Burundi — Ministère de l'Éducation Nationale et de la Recherche Scientifique</span>
        </div>
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-clock me-1"></i>
            <span>Lun–Ven 07h30–16h00 UTC+2</span>
            <span class="separator">|</span>
            <a href="<?= BASE_URL ?>/contact"><i class="fa-solid fa-headset me-1"></i>Support</a>
            <span class="separator">|</span>
            <a href="<?= BASE_URL ?>/connexion"><i class="fa-solid fa-lock me-1"></i>Espace sécurisé</a>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     NAVBAR STICKY RESPONSIVE
     ══════════════════════════════════════════════════════════════════════════ -->
<nav id="mainNav" class="navbar navbar-expand-lg" role="navigation" aria-label="Navigation principale">
    <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand" href="<?= BASE_URL ?>/">
            <div class="nav-logo-box">
                <i class="fa-solid fa-graduation-cap text-white fa-lg"></i>
            </div>
            <div class="d-flex flex-column lh-1">
                <span class="text-white fw-bold" style="font-size:1.1rem;letter-spacing:-.01em;">FIE</span>
                <span class="text-white d-none d-sm-block" style="font-size:.68rem;opacity:.7;font-weight:400;letter-spacing:.06em;text-transform:uppercase;">Burundi · SIGE</span>
            </div>
        </a>

        <!-- BURGER -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false"
                aria-label="Ouvrir le menu">
            <i class="fa-solid fa-bars text-white fs-5"></i>
        </button>

        <!-- LIENS -->
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-1 py-2 py-lg-0">
                <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="#fonctionnalites">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#comment">Comment ça marche</a></li>
                <li class="nav-item"><a class="nav-link" href="#mission">Mission</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/bibliotheque"><i class="fa-solid fa-book-open me-1"></i>Bibliothèque</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/aide">Aide</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact">Contact</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 pb-3 pb-lg-0">
                <a class="nav-link btn-nav-login" href="<?= BASE_URL ?>/connexion">
                    <i class="fa-solid fa-right-to-bracket me-1"></i>Connexion
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Bande tricolore drapeau sous navbar -->
<div class="flag-strip"></div>


<!-- ══════════════════════════════════════════════════════════════════════════
     HERO — PLEIN ÉCRAN
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="hero-section" id="accueil" aria-label="Présentation FIE">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- ── TEXTE GAUCHE ── -->
            <div class="col-lg-6 hero-content">
                <div class="hero-badge" aria-label="Ministère de l'Éducation">
                    <i class="fa-solid fa-star text-warning"></i>
                    DGESS / MENERS — SIGE Burundi
                </div>

                <h1 class="hero-title">
                    Un <span class="accent">identifiant unique</span><br>
                    pour chaque <span class="line-under">élève</span><br>
                    du Burundi
                </h1>

                <p class="hero-subtitle">
                    Le <strong class="text-white">Fichier Informatisé des Élèves (FIE)</strong>
                    attribue à chaque apprenant un IUE — Identifiant Unique Élève — certifié
                    ISO 7064 MOD 97-10. Traçabilité tout au long de la scolarité,
                    interopérabilité StatEduc, pilotage national par les données.
                </p>

                <div data-aos="fade-up" data-aos-delay="200">
                    <p class="text-white mb-1" style="font-size:.82rem;opacity:.7;">
                        <i class="fa-solid fa-tag me-1"></i>Exemple d'identifiant :
                    </p>
                    <div class="hero-iue mb-4">BI-0002-2025-000001-07</div>

                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= BASE_URL ?>/connexion" class="btn-hero-primary">
                            <i class="fa-solid fa-right-to-bracket"></i>Accéder à la plateforme
                        </a>
                        <a href="#fonctionnalites" class="btn-hero-outline">
                            <i class="fa-solid fa-circle-play"></i>Découvrir
                        </a>
                    </div>
                </div>
            </div>

            <!-- ── CARTE DROITE ── -->
            <div class="col-lg-6 hero-content" data-aos="fade-left" data-aos-duration="700">
                <div class="hero-card">

                    <!-- Mini carte ID élève -->
                    <div class="hero-id-card mb-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width:48px;height:48px;background:rgba(255,255,255,.2);
                                        border-radius:12px;display:flex;align-items:center;
                                        justify-content:center;">
                                <i class="fa-solid fa-id-card fa-lg text-white"></i>
                            </div>
                            <div>
                                <p class="text-white fw-bold mb-0" style="font-family:'Poppins',sans-serif;font-size:.95rem;">
                                    Carte Élève Numérique FIE
                                </p>
                                <p class="text-white mb-0" style="font-size:.72rem;opacity:.65;">
                                    Format IUE · ISO 7064 MOD 97-10
                                </p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge rounded-pill text-white" style="background:rgba(255,255,255,.15);font-size:.7rem;">
                                <i class="fa-solid fa-check-circle me-1" style="color:#a5f3c0;"></i>Certifié
                            </span>
                            <span class="badge rounded-pill text-white" style="background:rgba(255,255,255,.15);font-size:.7rem;">
                                <i class="fa-solid fa-shield-halved me-1" style="color:#93c5fd;"></i>Sécurisé
                            </span>
                            <span class="badge rounded-pill text-white" style="background:rgba(255,255,255,.15);font-size:.7rem;">
                                <i class="fa-solid fa-infinity me-1" style="color:#fca5a5;"></i>Pérenne
                            </span>
                        </div>
                    </div>

                    <!-- Statistiques pill -->
                    <div class="d-flex flex-column gap-3">
                        <div class="hero-pill">
                            <div style="width:44px;height:44px;background:rgba(255,255,255,.15);border-radius:12px;
                                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fa-solid fa-users text-warning"></i>
                            </div>
                            <div>
                                <div class="hp-num">3,2 M+</div>
                                <div class="hp-lbl">Élèves immatriculés</div>
                            </div>
                        </div>
                        <div class="hero-pill">
                            <div style="width:44px;height:44px;background:rgba(255,255,255,.15);border-radius:12px;
                                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fa-solid fa-school" style="color:#a5f3c0;"></i>
                            </div>
                            <div>
                                <div class="hp-num">8 500+</div>
                                <div class="hp-lbl">Établissements couverts</div>
                            </div>
                        </div>
                        <div class="hero-pill">
                            <div style="width:44px;height:44px;background:rgba(255,255,255,.15);border-radius:12px;
                                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fa-solid fa-map-location-dot" style="color:#93c5fd;"></i>
                            </div>
                            <div>
                                <div class="hp-num">18 Provinces</div>
                                <div class="hp-lbl">Couverture nationale</div>
                            </div>
                        </div>
                        <div class="hero-pill">
                            <div style="width:44px;height:44px;background:rgba(255,255,255,.15);border-radius:12px;
                                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fa-solid fa-bolt" style="color:#fcd34d;"></i>
                            </div>
                            <div>
                                <div class="hp-num">99,9%</div>
                                <div class="hp-lbl">Disponibilité garantie</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Indicateur de scroll -->
    <div class="scroll-indicator" aria-hidden="true">
        <i class="fa-solid fa-chevron-down"></i>
        <span>Découvrir</span>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     BANDEAU CONFIANCE
     ══════════════════════════════════════════════════════════════════════════ -->
<div class="trust-band">
    <div class="container">
        <div class="d-flex justify-content-center justify-content-md-between align-items-center flex-wrap gap-3">
            <div class="trust-item">
                <i class="fa-solid fa-shield-halved text-success"></i>
                Données chiffrées bcrypt
            </div>
            <div class="trust-item d-none d-sm-flex">
                <i class="fa-solid fa-scale-balanced text-primary"></i>
                Conforme loi n°1/03-2026
            </div>
            <div class="trust-item d-none d-md-flex">
                <i class="fa-solid fa-rotate text-warning"></i>
                Sync StatEduc temps réel
            </div>
            <div class="trust-item d-none d-lg-flex">
                <i class="fa-solid fa-users-gear" style="color:var(--bi-blue);"></i>
                RBAC 7 rôles
            </div>
            <div class="trust-item d-none d-lg-flex">
                <i class="fa-solid fa-earth-africa text-success"></i>
                18 provinces du Burundi
            </div>
            <div class="trust-item d-none d-xl-flex">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                Journal d'audit complet
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════════════
     CHIFFRES CLÉS — COMPTEURS ANIMÉS
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="stats-section" id="chiffres" aria-labelledby="stats-title">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow text-bi-blue">Chiffres clés</span>
            <div class="tri-divider d-block mx-auto my-2"></div>
            <h2 class="section-title" id="stats-title">Le FIE en quelques chiffres</h2>
            <p class="section-lead mx-auto mt-2">
                Des données nationales actualisées pour un pilotage éducatif
                précis à chaque niveau de gouvernance.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="0">
                <div class="stat-card stat-card--red">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-number" data-counter="3200000" data-suffix="M+">0</div>
                    <div class="stat-label">Élèves immatriculés</div>
                    <div class="stat-sublabel">Identifiants IUE actifs</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-card stat-card--green">
                    <div class="stat-icon">
                        <i class="fa-solid fa-school"></i>
                    </div>
                    <div class="stat-number" data-counter="8500" data-suffix="+">0</div>
                    <div class="stat-label">Établissements</div>
                    <div class="stat-sublabel">Publics et privés, maternelle → lycée</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-card stat-card--blue">
                    <div class="stat-icon">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div class="stat-number" data-counter="18" data-suffix="">0</div>
                    <div class="stat-label">Provinces couvertes</div>
                    <div class="stat-sublabel">Couverture nationale complète</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-card stat-card--gold">
                    <div class="stat-icon">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div class="stat-number" data-counter="99" data-suffix="%">0</div>
                    <div class="stat-label">Disponibilité</div>
                    <div class="stat-sublabel">SLA garanti 99,9% par an</div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     FONCTIONNALITÉS / SERVICES — 6 CARTES
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="features-section" id="fonctionnalites" aria-labelledby="feat-title">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow text-bi-green">Fonctionnalités</span>
            <div class="tri-divider d-block mx-auto my-2"></div>
            <h2 class="section-title" id="feat-title">
                Tout ce dont l'éducation burundaise a besoin
            </h2>
            <p class="section-lead mx-auto mt-2">
                Une plateforme complète de gestion des données élèves,
                sécurisée, interopérable et accessible depuis n'importe quel établissement.
            </p>
        </div>

        <div class="row g-4">
            <!-- 1 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="0">
                <div class="feat-card">
                    <div class="feat-icon" style="background:var(--bi-blue-l);color:var(--bi-blue);">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <h5>Immatriculation IUE</h5>
                    <p>Attribution automatique d'un Identifiant Unique Élève certifié ISO 7064 MOD 97-10
                       à chaque inscription. Détection des doublons en temps réel.</p>
                    <a href="<?= BASE_URL ?>/aide" class="feat-link" style="color:var(--bi-blue);">
                        En savoir plus <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <!-- 2 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="60">
                <div class="feat-card">
                    <div class="feat-icon" style="background:#eaf9ed;color:var(--bi-green-d);">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h5>Recherche multicritères</h5>
                    <p>Trouvez instantanément un élève par nom, IUE, date de naissance, province, commune
                       ou établissement. Filtres avancés et export des résultats.</p>
                    <a href="<?= BASE_URL ?>/aide" class="feat-link" style="color:var(--bi-green);">
                        En savoir plus <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <!-- 3 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="120">
                <div class="feat-card">
                    <div class="feat-icon" style="background:#e8f0fe;color:#1a73e8;">
                        <i class="fa-solid fa-rotate"></i>
                    </div>
                    <h5>Interopérabilité StatEduc</h5>
                    <p>Synchronisation bidirectionnelle avec l'API REST StatEduc et la base SQL Server
                       ELEVES_AGE_NIVEAU_SEXE. Mode dégradé via import Excel si hors-ligne.</p>
                    <a href="<?= BASE_URL ?>/aide" class="feat-link" style="color:#1a73e8;">
                        En savoir plus <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <!-- 4 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="0">
                <div class="feat-card">
                    <div class="feat-icon" style="background:#fff8e1;color:#d97706;">
                        <i class="fa-solid fa-chart-bar"></i>
                    </div>
                    <h5>Tableaux de bord</h5>
                    <p>KPI nationaux : répartition par province, parité filles/garçons, agrégats par
                       niveau, secteur et cycle. Pilotage des politiques éducatives par les données.</p>
                    <a href="<?= BASE_URL ?>/connexion" class="feat-link" style="color:#d97706;">
                        Accéder <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <!-- 5 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="60">
                <div class="feat-card">
                    <div class="feat-icon" style="background:#f3e8ff;color:#9333ea;">
                        <i class="fa-solid fa-arrows-left-right"></i>
                    </div>
                    <h5>Suivi des mouvements</h5>
                    <p>Traçabilité complète des transferts inter-établissements, abandons, réintégrations
                       et passages en classe supérieure. L'IUE persiste sur toute la scolarité.</p>
                    <a href="<?= BASE_URL ?>/aide" class="feat-link" style="color:#9333ea;">
                        En savoir plus <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <!-- 6 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="120">
                <div class="feat-card">
                    <div class="feat-icon" style="background:#fef3c7;color:#b45309;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h5>Sécurité &amp; Audit</h5>
                    <p>RBAC 7 rôles, CSRF, bcrypt cost-12, sessions sécurisées, chiffrement des données.
                       Journal d'audit complet conforme à la loi n°1/03-2026 (RGPD Burundi).</p>
                    <a href="<?= BASE_URL ?>/confidentialite" class="feat-link" style="color:#b45309;">
                        Politique de données <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     COMMENT ÇA MARCHE — 4 ÉTAPES DU PROCESSUS IUE
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="how-section" id="comment" aria-labelledby="how-title">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow text-bi-blue">Processus</span>
            <div class="tri-divider d-block mx-auto my-2"></div>
            <h2 class="section-title" id="how-title">Comment fonctionne l'immatriculation ?</h2>
            <p class="section-lead mx-auto mt-2">
                En quelques minutes, chaque nouvel élève obtient son IUE certifié,
                valable pour toute sa scolarité au Burundi.
            </p>
        </div>

        <div class="row g-4 g-lg-0 justify-content-center" data-aos="fade-up" data-aos-delay="100">
            <div class="col-6 col-md-3">
                <div class="how-step">
                    <div class="how-step-num how-step-num--red">1</div>
                    <h6>Saisie élève</h6>
                    <p>L'agent de l'établissement saisit les données civiles : nom, prénom,
                       date et lieu de naissance, sexe, province, établissement.</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="how-step">
                    <div class="how-step-num how-step-num--blue">2</div>
                    <h6>Contrôle doublon</h6>
                    <p>Le système compare en temps réel la fiche avec la base nationale
                       pour détecter toute immatriculation préexistante.</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="how-step">
                    <div class="how-step-num how-step-num--gold">3</div>
                    <h6>Génération IUE</h6>
                    <p>Si unique, un IUE est calculé et certifié par clé de contrôle
                       ISO 7064 MOD 97-10. L'élève est enregistré dans la base nationale.</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="how-step">
                    <div class="how-step-num how-step-num--green">4</div>
                    <h6>Fiche imprimable</h6>
                    <p>Une fiche d'immatriculation officielle avec l'IUE est générée
                       et peut être imprimée pour l'élève et l'établissement.</p>
                </div>
            </div>
        </div>

        <!-- CTA intermédiaire -->
        <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="200">
            <a href="<?= BASE_URL ?>/connexion"
               class="btn text-white fw-bold px-5 py-3 rounded-pill"
               style="background:var(--bi-blue);font-family:'Poppins',sans-serif;">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Commencer l'immatriculation
            </a>
            <a href="<?= BASE_URL ?>/aide"
               class="btn btn-outline-secondary fw-semibold px-5 py-3 rounded-pill ms-3"
               style="font-family:'Poppins',sans-serif;">
                <i class="fa-solid fa-book me-2"></i>Consulter la documentation
            </a>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     MISSION / À PROPOS
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="about-section" id="mission" aria-labelledby="about-title">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- VISUEL GAUCHE -->
            <div class="col-lg-5" data-aos="fade-right" data-aos-duration="700">
                <div class="about-visual" style="padding:0;overflow:hidden;">
                    <!-- Photo réelle école Burundi -->
                    <div style="position:relative;overflow:hidden;border-radius:var(--bi-radius-lg) var(--bi-radius-lg) 0 0;">
                        <img src="https://sspark.genspark.ai/cfimages?u1=ziz%2BUR%2Bf9ElrUjugiPTIUoEl4zlECubNCaYLWMnRb5sNQwNzoXUEB%2B3AuIitpgPV9oEoRI2vdxyG9CinScxvqH6xO7eME34wZ42wTG01nZO%2BHpaqM0n4GC9zs%2FoXun6ei4nIbOIxO8RvUzbY1LkObs5QP24v9pnfEiHDLRu307AqTWCpyBZaWbUGeftY3iIa2%2B%2BgK8Yfc5CdjbDj9DXdSw%3D%3D&u2=EFStBu5SGDE4H4u3&width=2560"
                             alt="Apprentissage fondé sur le jeu au Burundi — Right To Play"
                             style="width:100%;height:220px;object-fit:cover;object-position:center;display:block;">
                        <div style="position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,.45), transparent);"></div>
                        <div style="position:absolute;bottom:.75rem;left:1rem;color:#fff;font-family:'Poppins',sans-serif;font-size:.8rem;font-weight:600;">
                            <i class="fa-solid fa-camera me-1" style="opacity:.7;"></i>Burundi — Right To Play
                        </div>
                    </div>
                    <div class="about-visual-inner">
                        <!-- Écu drapeau stylisé -->
                        <div style="width:90px;height:90px;background:rgba(255,255,255,.15);
                                    border-radius:50%;display:flex;align-items:center;
                                    justify-content:center;margin:0 auto 1.5rem;">
                            <i class="fa-solid fa-flag fa-2x text-white"></i>
                        </div>

                        <p class="text-white fw-bold fs-5 mb-1" style="font-family:'Poppins',sans-serif;">
                            République du Burundi
                        </p>
                        <p class="text-white small mb-3" style="opacity:.75;">
                            Ministère de l'Éducation Nationale<br>et de la Recherche Scientifique
                        </p>

                        <!-- Barre tricolore -->
                        <div class="d-flex justify-content-center gap-2 mb-4">
                            <div style="width:44px;height:7px;background:#1a56db;border-radius:4px;"></div>
                            <div style="width:44px;height:7px;background:#fff;border-radius:4px;"></div>
                            <div style="width:44px;height:7px;background:#1EB53A;border-radius:4px;"></div>
                        </div>

                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div style="background:rgba(255,255,255,.12);border-radius:.75rem;padding:1rem .5rem;">
                                    <div class="text-white fw-bold" style="font-size:1.4rem;font-family:'Poppins',sans-serif;">DGESS</div>
                                    <div class="text-white" style="font-size:.7rem;opacity:.7;">Direction Générale<br>des Statistiques</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div style="background:rgba(255,255,255,.12);border-radius:.75rem;padding:1rem .5rem;">
                                    <div class="text-white fw-bold" style="font-size:1.4rem;font-family:'Poppins',sans-serif;">SIGE</div>
                                    <div class="text-white" style="font-size:.7rem;opacity:.7;">Système d'Information<br>et Gestion Éducation</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENU DROIT -->
            <div class="col-lg-7" data-aos="fade-left" data-aos-duration="700">
                <span class="section-eyebrow text-bi-blue">Notre mission</span>
                <div class="tri-divider d-block my-2"></div>
                <h2 class="section-title mt-2 mb-3" id="about-title">
                    Un système d'information éducatif<br>au service de chaque enfant
                </h2>
                <p class="text-muted mb-4" style="line-height:1.8;">
                    Le FIE (Fichier Informatisé des Élèves) est la composante centrale du
                    <strong>SIGE Burundi</strong>. Développé par la DGESS / MENERS, il garantit
                    à chaque élève un identifiant unique pérenne, quelles que soient ses
                    mobilités scolaires sur l'ensemble du territoire burundais.
                </p>

                <!-- Milestones -->
                <div class="mb-4">
                    <div class="milestone">
                        <div class="milestone-icon" style="background:var(--bi-red-l);color:var(--bi-blue);">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <div>
                            <h6>Unicité garantie à l'échelle nationale</h6>
                            <p>Chaque élève reçoit un IUE unique, certifié par clé de contrôle
                               ISO 7064 MOD 97-10, éliminant tout doublon dans le système éducatif.</p>
                        </div>
                    </div>
                    <div class="milestone">
                        <div class="milestone-icon" style="background:var(--bi-green-l);color:var(--bi-green-d);">
                            <i class="fa-solid fa-link"></i>
                        </div>
                        <div>
                            <h6>Interopérabilité nationale avec StatEduc</h6>
                            <p>Connexion bidirectionnelle (API REST + SQL Server) pour une
                               donnée éducative cohérente à l'échelle nationale.</p>
                        </div>
                    </div>
                    <div class="milestone">
                        <div class="milestone-icon" style="background:#e8f0fe;color:#1a73e8;">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </div>
                        <div>
                            <h6>Conformité RGPD Burundi (loi n°1/03-2026)</h6>
                            <p>Traitement des données conforme à la loi nationale,
                               avec audit trail complet et conservation sécurisée 5 ans.</p>
                        </div>
                    </div>
                    <div class="milestone">
                        <div class="milestone-icon" style="background:#fff8e1;color:#d97706;">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div>
                            <h6>Pilotage par les données</h6>
                            <p>Tableaux de bord nationaux, provincials et locaux pour des
                               décisions éducatives fondées sur des statistiques précises et actualisées.</p>
                        </div>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/connexion"
                   class="btn text-white fw-semibold px-4 py-2 rounded-pill"
                   style="background:var(--bi-blue);font-family:'Poppins',sans-serif;">
                    <i class="fa-solid fa-arrow-right me-2"></i>Accéder au portail FIE
                </a>
                <a href="<?= BASE_URL ?>/aide"
                   class="btn btn-outline-secondary fw-semibold px-4 py-2 rounded-pill ms-2"
                   style="font-family:'Poppins',sans-serif;">
                    <i class="fa-solid fa-book me-2"></i>Documentation
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     PROVINCES — 18 PROVINCES DU BURUNDI
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="provinces-section" id="provinces" aria-labelledby="prov-title">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5" data-aos="fade-right">
                <span class="section-eyebrow text-bi-green">Couverture nationale</span>
                <div class="tri-divider d-block my-2"></div>
                <h2 class="section-title mt-2 mb-3" id="prov-title">
                    18 provinces,<br>un seul registre national
                </h2>
                <p class="text-muted mb-4" style="line-height:1.8;">
                    Le FIE couvre l'ensemble du territoire burundais, des zones urbaines
                    de Bujumbura aux communautés rurales les plus reculées.
                    Chaque élève, où qu'il soit scolarisé, est identifié dans le registre national.
                </p>
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <div class="text-center">
                        <div class="fw-bold" style="font-size:2rem;font-family:'Poppins',sans-serif;color:var(--bi-green);">18</div>
                        <div class="text-muted small">Provinces</div>
                    </div>
                    <div class="vr d-none d-sm-block" style="height:40px;"></div>
                    <div class="text-center">
                        <div class="fw-bold" style="font-size:2rem;font-family:'Poppins',sans-serif;color:var(--bi-blue);">119</div>
                        <div class="text-muted small">Communes</div>
                    </div>
                    <div class="vr d-none d-sm-block" style="height:40px;"></div>
                    <div class="text-center">
                        <div class="fw-bold" style="font-size:2rem;font-family:'Poppins',sans-serif;color:#1a73e8;">2 900+</div>
                        <div class="text-muted small">Collines</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7" data-aos="fade-left">
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $provinces = [
                        'Bujumbura Mairie','Bujumbura Rural','Bubanza','Bururi',
                        'Cankuzo','Cibitoke','Gitega','Karuzi','Kayanza','Kirundo',
                        'Makamba','Muramvya','Muyinga','Mwaro','Ngozi','Rumonge',
                        'Rutana','Ruyigi'
                    ];
                    foreach ($provinces as $i => $p):
                    ?>
                    <div class="province-chip"
                         data-aos="zoom-in"
                         data-aos-delay="<?= ($i % 6) * 40 ?>">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= htmlspecialchars($p, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     ACTUALITÉS — 3 CARTES
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="news-section" id="actualites" aria-labelledby="news-title">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3"
             data-aos="fade-up">
            <div>
                <span class="section-eyebrow text-bi-green">Actualités</span>
                <div class="tri-divider d-block my-2"></div>
                <h2 class="section-title mt-2 mb-0" id="news-title">Dernières nouvelles du FIE</h2>
            </div>
            <a href="<?= BASE_URL ?>/aide"
               class="btn btn-outline-secondary btn-sm px-4 rounded-pill fw-semibold"
               style="font-family:'Poppins',sans-serif;">
                Tout voir <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <!-- Actualité 1 — Déploiement -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
                <div class="news-card card border-0">
                    <div class="news-thumb" style="background:linear-gradient(135deg,#ffeef0 0%,#ffc8d0 100%);">
                        <img src="https://sspark.genspark.ai/cfimages?u1=bIQsqcvxMgBpZ9pbIbuou06u0LSjSxlxkkuPPUjAJP3Qwh9AMR0tckWFh1egjvmeqV7ckikQJ5xRohvCVCvGJehI9sdQFy%2FiATHLzZPriofCqyfBTKXvKVnpEMDKn5Y%3D&u2=cy0ndCkVn7PS4jVG&width=2560"
                             alt="Élèves du Burundi en classe" loading="lazy">
                        <span style="position:absolute;bottom:.75rem;right:.75rem;z-index:2;
                                     background:var(--bi-blue);color:#fff;border-radius:6px;
                                     padding:.2rem .6rem;font-size:.68rem;font-weight:700;font-family:'Poppins',sans-serif;">
                            <i class="fa-solid fa-rocket me-1"></i>Déploiement
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <span class="news-category text-primary">Déploiement</span>
                        <h5 class="card-title mt-2">
                            Lancement FIE v1.0 — Immatriculation IUE opérationnelle
                        </h5>
                        <p class="text-muted small mb-3" style="line-height:1.65;">
                            Le système d'immatriculation est désormais actif dans les 18 provinces.
                            Chaque inscription génère automatiquement un IUE certifié.
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="news-date">
                                <i class="fa-regular fa-calendar"></i>Janvier 2026
                            </span>
                            <a href="<?= BASE_URL ?>/aide" class="news-read-more text-primary">
                                Lire <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actualité 2 — Interopérabilité -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="news-card card border-0">
                    <div class="news-thumb" style="background:linear-gradient(135deg,#eaf9ed 0%,#b8f0c8 100%);">
                        <img src="https://sspark.genspark.ai/cfimages?u1=%2BOwcmeusI7YmeMqFIxZMlJGnOzuVc4sviqPzFsBjWODRExfbWDI%2FOpiol4NOTovfhuX5M4e%2FCfIOySPgP2K7M2F4Dy7XdaxwH1x7BXHUmD2FeJBU3Yb%2BuaGIpKixwY6%2Fyk%2FXV6HN6weA&u2=dTxigUFkOTz6JYzm&width=2560"
                             alt="Outils numériques pour l'éducation en Afrique" loading="lazy">
                        <span style="position:absolute;bottom:.75rem;right:.75rem;z-index:2;
                                     background:var(--bi-green);color:#fff;border-radius:6px;
                                     padding:.2rem .6rem;font-size:.68rem;font-weight:700;font-family:'Poppins',sans-serif;">
                            <i class="fa-solid fa-rotate me-1"></i>Interopérabilité
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <span class="news-category" style="color:var(--bi-green);">Interopérabilité</span>
                        <h5 class="card-title mt-2">
                            Connexion StatEduc — synchronisation 8 500 établissements
                        </h5>
                        <p class="text-muted small mb-3" style="line-height:1.65;">
                            L'API StatEduc est désormais connectée. Mise à jour incrémentale
                            quotidienne des référentiels établissements et effectifs.
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="news-date">
                                <i class="fa-regular fa-calendar"></i>Février 2026
                            </span>
                            <a href="<?= BASE_URL ?>/aide" class="news-read-more" style="color:var(--bi-green);">
                                Lire <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actualité 3 — Statistiques -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="news-card card border-0">
                    <div class="news-thumb" style="background:linear-gradient(135deg,#e8f0fe 0%,#c0d4fc 100%);">
                        <img src="https://sspark.genspark.ai/cfimages?u1=wNNjty7UhhKGJoXlZdd11oq0j5mmLjkD4FNNvpZ9rYZRgb%2BfXf8HxfjhJW5SXKxUtlsDi%2BRkkb%2FrrkGV3Nbm3H9twiKdciwMCb66uOKmeJi5Fe0756x7wv2snA%3D%3D&u2=G19d1W2GwRPBatNA&width=2560"
                             alt="Éducation numérique en Afrique" loading="lazy">
                        <span style="position:absolute;bottom:.75rem;right:.75rem;z-index:2;
                                     background:var(--bi-cyan);color:#fff;border-radius:6px;
                                     padding:.2rem .6rem;font-size:.68rem;font-weight:700;font-family:'Poppins',sans-serif;">
                            <i class="fa-solid fa-chart-line me-1"></i>Statistiques
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <span class="news-category text-primary">Statistiques</span>
                        <h5 class="card-title mt-2">
                            Rapport annuel 2025 — 3,2 millions d'élèves immatriculés
                        </h5>
                        <p class="text-muted small mb-3" style="line-height:1.65;">
                            Le rapport annuel 2025 confirme une couverture nationale de 99,9%.
                            Parité filles/garçons améliorée de 3,2 points par rapport à 2024.
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="news-date">
                                <i class="fa-regular fa-calendar"></i>Mars 2026
                            </span>
                            <a href="<?= BASE_URL ?>/aide" class="news-read-more text-primary">
                                Lire <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     PARTENAIRES — TICKER DÉFILANT
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="partners-section" id="partenaires" aria-label="Partenaires institutionnels">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow text-bi-blue">Partenaires</span>
            <div class="tri-divider d-block mx-auto my-2"></div>
            <h2 class="section-title mt-2 mb-2">Nos partenaires institutionnels</h2>
            <p class="text-muted" style="font-size:.95rem;">
                Développé en partenariat avec les acteurs clés de l'éducation burundaise et internationale.
            </p>
        </div>

        <!-- Ticker défilant (dupliqué pour boucle infinie) -->
        <div class="partner-ticker-wrap" data-aos="fade-up" data-aos-delay="100">
            <div class="partner-ticker">
                <?php
                $partners = [
                    ['icon' => 'fa-landmark', 'label' => 'MENERS\nBurundi'],
                    ['icon' => 'fa-building-columns', 'label' => 'DGESS\nStatistiques'],
                    ['icon' => 'fa-earth-africa', 'label' => 'UNESCO\nBurundi'],
                    ['icon' => 'fa-hands-helping', 'label' => 'UNICEF\nBurundi'],
                    ['icon' => 'fa-globe', 'label' => 'Banque\nMondiale'],
                    ['icon' => 'fa-flag', 'label' => 'Gouvernement\nBurundi'],
                    ['icon' => 'fa-graduation-cap', 'label' => 'Universités\nPartenaires'],
                    ['icon' => 'fa-database', 'label' => 'StatEduc\nAPI'],
                ];
                // Doubler pour le ticker infini
                $allPartners = array_merge($partners, $partners);
                foreach ($allPartners as $p):
                ?>
                <div class="partner-logo flex-shrink-0" style="min-width:140px;">
                    <i class="fa-solid <?= htmlspecialchars($p['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                    <span><?= nl2br(htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8')) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     CTA FINAL — VERT BURUNDI
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="cta-section" aria-label="Appel à l'action">
    <div class="container position-relative z-1">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="zoom-in">
                <div class="d-inline-flex align-items-center justify-content-center mb-4"
                     style="width:84px;height:84px;background:rgba(255,255,255,.2);
                            border-radius:24px;border:1px solid rgba(255,255,255,.3);">
                    <i class="fa-solid fa-graduation-cap fa-2x text-white"></i>
                </div>
                <h2 class="mb-3">
                    Prêt à rejoindre le FIE ?
                </h2>
                <p class="mb-4 mx-auto" style="max-width:520px;">
                    Connectez-vous au portail d'administration FIE pour gérer les inscriptions,
                    rechercher des élèves, suivre les mouvements et piloter votre
                    établissement, commune ou province.
                </p>
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>/connexion" class="btn-cta-white">
                        <i class="fa-solid fa-right-to-bracket"></i>Se connecter au portail
                    </a>
                    <a href="<?= BASE_URL ?>/contact" class="btn-cta-outline">
                        <i class="fa-solid fa-headset"></i>Contacter le support
                    </a>
                </div>

                <!-- Badges confiance sous les boutons -->
                <div class="d-flex justify-content-center flex-wrap gap-3 mt-4">
                    <span class="badge-pill" style="background:rgba(255,255,255,.2);color:#fff;">
                        <i class="fa-solid fa-shield-halved"></i>Données sécurisées
                    </span>
                    <span class="badge-pill" style="background:rgba(255,255,255,.2);color:#fff;">
                        <i class="fa-solid fa-clock"></i>Accès 24h/24
                    </span>
                    <span class="badge-pill" style="background:rgba(255,255,255,.2);color:#fff;">
                        <i class="fa-solid fa-headset"></i>Support DGESS
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     FOOTER RICHE — 5 COLONNES
     ══════════════════════════════════════════════════════════════════════════ -->
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="row g-4 mb-2">

            <!-- Colonne 1 : Branding -->
            <div class="col-lg-4 col-md-12">
                <div class="footer-brand">
                    <i class="fa-solid fa-graduation-cap me-2" style="color:var(--bi-blue);"></i>FIE Burundi
                </div>
                <p class="footer-tagline">Fichier Informatisé des Élèves · SIGE</p>
                <p class="small mb-3" style="color:rgba(255,255,255,.5);line-height:1.7;">
                    Système national d'immatriculation des élèves du Burundi.
                    Développé par la DGESS / MENERS dans le cadre du
                    Système d'Information et de Gestion de l'Éducation (SIGE Burundi).
                </p>
                <!-- Tricolore mini -->
                <div style="width:64px;height:4px;border-radius:2px;margin-bottom:1rem;
                            background:linear-gradient(to right,#1a56db 33%,#fff 33%,#fff 66%,#1EB53A 66%);"></div>
                <div class="footer-social">
                    <a href="#" aria-label="Twitter / X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <!-- Colonne 2 : Navigation -->
            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="footer-heading">Navigation</h6>
                <a href="<?= BASE_URL ?>/"                class="footer-link">Accueil</a>
                <a href="#fonctionnalites"                class="footer-link">Services</a>
                <a href="#comment"                        class="footer-link">Comment ça marche</a>
                <a href="#mission"                        class="footer-link">Mission</a>
                <a href="#actualites"                     class="footer-link">Actualités</a>
                <a href="#partenaires"                    class="footer-link">Partenaires</a>
            </div>

            <!-- Colonne 3 : Portail admin -->
            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="footer-heading">Portail</h6>
                <a href="<?= BASE_URL ?>/connexion"              class="footer-link">Connexion</a>
                <a href="<?= BASE_URL ?>/tableau-de-bord"        class="footer-link">Tableau de bord</a>
                <a href="<?= BASE_URL ?>/inscription/nouveau"    class="footer-link">Nouvelle inscription</a>
                <a href="<?= BASE_URL ?>/inscription/recherche"  class="footer-link">Rechercher un élève</a>
                <a href="<?= BASE_URL ?>/admin"                  class="footer-link">Administration</a>
            </div>

            <!-- Colonne 4 : Aide & Légal -->
            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="footer-heading">Aide &amp; Légal</h6>
                <a href="<?= BASE_URL ?>/aide"            class="footer-link">Documentation</a>
                <a href="<?= BASE_URL ?>/contact"         class="footer-link">Support SIGE</a>
                <a href="<?= BASE_URL ?>/confidentialite" class="footer-link">Confidentialité</a>
                <a href="<?= BASE_URL ?>/mentions-legales" class="footer-link">Mentions légales</a>
            </div>

            <!-- Colonne 5 : Contact -->
            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="footer-heading">Contact</h6>
                <p class="small mb-2" style="color:rgba(255,255,255,.5);line-height:1.6;">
                    <i class="fa-solid fa-building me-1"></i>
                    DGESS / MENERS<br>
                    Bujumbura, Burundi
                </p>
                <p class="small mb-2" style="color:rgba(255,255,255,.5);line-height:1.6;">
                    <i class="fa-solid fa-clock me-1"></i>
                    Lun–Ven : 07h30–16h00<br>
                    <span style="font-size:.75rem;">(UTC+2)</span>
                </p>
                <p class="small mb-0" style="color:rgba(255,255,255,.5);">
                    <i class="fa-solid fa-circle me-1" style="color:#1EB53A;font-size:.6rem;"></i>
                    Système opérationnel
                </p>
            </div>

        </div><!-- /.row -->

        <hr class="footer-divider">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <p class="footer-copyright mb-0">
                © <?= date('Y') ?> DGESS / MENERS Burundi — FIE v1.0.0 · Tous droits réservés
            </p>
            <p class="footer-copyright mb-0">
                Bootstrap 5.3 · Font Awesome 6.5 · PHP 8.2 · AOS 2.3
            </p>
        </div>
    </div>
</footer>

<!-- Bouton retour en haut -->
<button id="backToTop" aria-label="Retourner en haut de page" onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <i class="fa-solid fa-chevron-up"></i>
</button>


<!-- ══════════════════════════════════════════════════════════════════════════
     SCRIPTS
     ══════════════════════════════════════════════════════════════════════════ -->
<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmMFkBg1VXt6DdWX5Fj3hZJzmZVZ"
        crossorigin="anonymous"></script>

<!-- AOS — Animate On Scroll -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
/* ── Détection prefers-reduced-motion ── */
var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ── Init AOS ── */
AOS.init({
    once:     true,
    duration: prefersReduced ? 0 : 650,
    easing:   'ease-out-cubic',
    offset:   60,
    disable:  prefersReduced
});

/* ══════════════════════════════════════════════════════════════════════════
   NAVBAR — ombre + compactage au scroll
   ══════════════════════════════════════════════════════════════════════════ */
(function() {
    var nav = document.getElementById('mainNav');
    if (!nav) return;
    function onScroll() {
        nav.classList.toggle('scrolled', window.scrollY > 40);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();

/* ══════════════════════════════════════════════════════════════════════════
   BOUTON RETOUR EN HAUT
   ══════════════════════════════════════════════════════════════════════════ */
(function() {
    var btn = document.getElementById('backToTop');
    if (!btn) return;
    window.addEventListener('scroll', function() {
        btn.classList.toggle('visible', window.scrollY > 400);
    }, { passive: true });
})();

/* ══════════════════════════════════════════════════════════════════════════
   COMPTEURS ANIMÉS (IntersectionObserver + easeOutExpo)
   ══════════════════════════════════════════════════════════════════════════ */
(function() {
    if (prefersReduced) {
        /* Afficher directement la valeur finale si prefers-reduced-motion */
        document.querySelectorAll('[data-counter]').forEach(function(el) {
            var target = parseInt(el.dataset.counter, 10);
            var suffix = el.dataset.suffix || '';
            el.textContent = formatNum(target, suffix);
        });
        return;
    }

    function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
    }

    function formatNum(n, suffix) {
        if (suffix === 'M+') {
            var m = n / 1000000;
            return m.toFixed(1) + '\u00a0M+';
        }
        return n.toLocaleString('fr-FR') + suffix;
    }

    function animateCounter(el) {
        var target   = parseInt(el.dataset.counter, 10);
        var suffix   = el.dataset.suffix || '';
        var duration = 2000;
        var start    = performance.now();

        function step(now) {
            var elapsed  = now - start;
            var progress = Math.min(elapsed / duration, 1);
            var eased    = easeOutExpo(progress);
            var current  = Math.round(target * eased);
            el.textContent = formatNum(current, suffix);
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    }

    var counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;

    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });
        counters.forEach(function(c) { io.observe(c); });
    } else {
        /* Fallback navigateurs anciens */
        counters.forEach(function(c) { animateCounter(c); });
    }
})();

/* ══════════════════════════════════════════════════════════════════════════
   SMOOTH SCROLL pour les ancres internes
   ══════════════════════════════════════════════════════════════════════════ */
document.querySelectorAll('a[href^="#"]').forEach(function(link) {
    link.addEventListener('click', function(e) {
        var id = this.getAttribute('href');
        if (id === '#') return;
        var target = document.querySelector(id);
        if (target) {
            e.preventDefault();
            var navH = document.getElementById('mainNav')
                       ? document.getElementById('mainNav').offsetHeight : 0;
            var top  = target.getBoundingClientRect().top + window.scrollY - navH - 12;
            window.scrollTo({ top: top, behavior: 'smooth' });
        }
    });
});

/* ══════════════════════════════════════════════════════════════════════════
   ACTIVE LINK selon la section visible (IntersectionObserver)
   ══════════════════════════════════════════════════════════════════════════ */
(function() {
    var sections = document.querySelectorAll('section[id]');
    var navLinks = document.querySelectorAll('#mainNav .nav-link');
    if (!sections.length || !navLinks.length) return;

    var io2 = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var id = entry.target.id;
                navLinks.forEach(function(link) {
                    link.classList.toggle('active',
                        link.getAttribute('href') === ('#' + id) ||
                        link.getAttribute('href') === '<?= BASE_URL ?>/' && id === 'accueil'
                    );
                });
            }
        });
    }, { rootMargin: '-40% 0px -55% 0px' });

    sections.forEach(function(s) { io2.observe(s); });
})();
</script>

</body>
</html>
