<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Connexion — FIE Burundi', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#CE1126">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="icon" href="<?= BASE_URL ?>/public/img/favicon.png" type="image/png">

    <style>
        :root {
            --bi-red:       #CE1126;   /* Rouge Burundi */
            --bi-red-dark:  #a00e1b;
            --bi-red-light: #fce8ea;
            --bi-green:     #1EB53A;
            --bi-green-dk:  #178a2b;
            --bi-white:     #FFFFFF;
            --bi-dark:      #0f1f0e;
            --bi-navy:      #1a2636;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: var(--bi-navy);
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(206,17,38,0.18) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(30,181,58,0.10) 0%, transparent 50%);
            display: flex;
            flex-direction: column;
            font-family: 'Open Sans', system-ui, -apple-system, sans-serif;
        }

        /* ── Barre institutionnelle supérieure ── */
        .gov-bar {
            background: linear-gradient(90deg, #0a1520 0%, #1a2636 50%, #0a1520 100%);
            border-bottom: 3px solid var(--bi-red);
            padding: 10px 0;
        }
        .gov-bar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .gov-title {
            color: #fff;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            opacity: 0.8;
        }
        .gov-title strong {
            color: #ffd0d6;
            font-size: 0.80rem;
            display: block;
        }

        /* Bande tricolore décorative */
        .tri-strip {
            height: 3px;
            background: linear-gradient(to right, var(--bi-red) 0% 33.33%, #fff 33.33% 66.66%, var(--bi-green) 66.66% 100%);
            width: 60px;
            border-radius: 2px;
        }

        /* ── Zone principale ── */
        .login-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 960px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 30px 90px rgba(0,0,0,0.55), 0 0 0 1px rgba(206,17,38,0.25);
        }

        /* ── Panneau gauche institutionnel ── */
        .login-left {
            background: linear-gradient(160deg, #1e1020 0%, #1a2636 50%, #0a1520 100%);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute; top: -80px; right: -80px;
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(206,17,38,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .login-left::after {
            content: '';
            position: absolute; bottom: -60px; left: -60px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(30,181,58,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Armoirie */
        .armoirie-badge {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative; z-index: 1;
        }
        .armoirie-badge img {
            width: 100px;
            height: auto;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));
        }

        .fie-brand {
            position: relative; z-index: 1;
            color: #fff;
        }
        .fie-brand .fie-code {
            font-family: 'Poppins', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.04em;
            line-height: 1;
        }
        .fie-brand .fie-full {
            font-size: 0.82rem;
            opacity: 0.8;
            margin-top: 0.4rem;
            line-height: 1.4;
            max-width: 220px;
        }
        .fie-brand .fie-sub {
            font-size: 0.72rem;
            color: #ffd0d6;
            opacity: 0.7;
            margin-top: 0.3rem;
        }

        .red-divider {
            height: 2px;
            background: linear-gradient(90deg, var(--bi-red), transparent);
            margin: 1.5rem 0;
        }

        .features-list {
            list-style: none; padding: 0; margin: 0;
            position: relative; z-index: 1;
        }
        .features-list li {
            display: flex; align-items: flex-start; gap: 0.75rem;
            color: rgba(255,255,255,0.72);
            font-size: 0.81rem; margin-bottom: 0.85rem; line-height: 1.4;
        }
        .features-list li .icon-bullet {
            color: #ffd0d6;
            font-size: 0.95rem;
            flex-shrink: 0; margin-top: 0.1rem;
        }

        .left-footer {
            position: relative; z-index: 1;
        }
        .left-footer small {
            color: rgba(255,255,255,0.35);
            font-size: 0.70rem;
        }

        /* ── Panneau droit formulaire ── */
        .login-right {
            background: #fff;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right h2 {
            color: var(--bi-navy);
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.3rem;
        }
        .login-right .subtitle {
            color: #6b7280;
            font-size: 0.84rem;
            margin-bottom: 2rem;
        }

        .red-accent-bar {
            height: 3px;
            background: linear-gradient(90deg, var(--bi-red), var(--bi-red-dark), var(--bi-red));
            border-radius: 3px;
            margin-bottom: 1.75rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.82rem;
            color: #374151;
            margin-bottom: 0.35rem;
        }

        .form-control {
            border: 1.5px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: var(--bi-red);
            box-shadow: 0 0 0 3px rgba(206,17,38,.12);
            outline: none;
        }
        .input-group-text {
            background: #f9fafb;
            border: 1.5px solid #d1d5db;
            color: #6b7280;
        }
        .input-group .form-control { border-left: 0; }
        .input-group .btn-outline-secondary {
            border: 1.5px solid #d1d5db;
            border-left: 0;
            color: #6b7280; background: #f9fafb;
            transition: background .15s;
        }
        .input-group .btn-outline-secondary:hover { background: #f3f4f6; }

        .btn-connexion {
            background: linear-gradient(135deg, var(--bi-red) 0%, var(--bi-red-dark) 100%);
            border: none;
            color: #fff !important;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 0.96rem;
            padding: 0.76rem;
            border-radius: 0.5rem;
            letter-spacing: 0.03em;
            transition: opacity .2s, transform .1s;
            width: 100%;
            margin-top: 0.5rem;
        }
        .btn-connexion:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn-connexion:active { transform: translateY(0); }

        .legal-note {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.78rem;
            color: #9ca3af;
            line-height: 1.5;
        }

        /* ── Footer ── */
        .login-footer-bar {
            background: rgba(0,0,0,0.3);
            border-top: 1px solid rgba(206,17,38,0.2);
            padding: 0.75rem 1rem;
            text-align: center;
        }
        .login-footer-bar small {
            color: rgba(255,255,255,0.35);
            font-size: 0.70rem;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .login-container { grid-template-columns: 1fr; max-width: 420px; }
            .login-left { padding: 2rem 1.5rem; }
            .features-list { display: none; }
            .red-divider { margin: 1rem 0; }
            .login-right { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<!-- ── Barre institutionnelle supérieure ───────────────────────────────── -->
<div class="gov-bar">
    <div class="gov-bar-inner">
        <div class="gov-title">
            République du Burundi
            <strong>Ministère de l'Éducation Nationale et de la Recherche Scientifique</strong>
        </div>
        <div class="tri-strip" aria-hidden="true"></div>
    </div>
</div>

<!-- ── Zone principale ────────────────────────────────────────────────── -->
<div class="login-wrapper">
    <div class="login-container">

        <!-- Panneau gauche — Présentation institutionnelle -->
        <div class="login-left">

            <div>
                <!-- Armoirie du Burundi -->
                <div class="armoirie-badge">
                    <img src="<?= BASE_URL ?>/public/images/armoiries_burundi.gif"
                         alt="Armoiries de la République du Burundi">
                </div>

                <!-- Identité FIE -->
                <div class="fie-brand">
                    <div class="fie-code">FIE</div>
                    <div class="fie-full">Fichier Informatisé des Élèves</div>
                    <div class="fie-sub">SIGE Burundi — DGESS / MENERS</div>
                </div>

                <div class="red-divider"></div>

                <!-- Caractéristiques -->
                <ul class="features-list">
                    <li>
                        <span class="icon-bullet"><i class="fa-solid fa-id-card"></i></span>
                        <span>Attribution d'un Identifiant Unique de l'Élève (IUE) à chaque apprenant</span>
                    </li>
                    <li>
                        <span class="icon-bullet"><i class="fa-solid fa-shield-halved"></i></span>
                        <span>Détection automatique des doublons pour garantir l'unicité nationale</span>
                    </li>
                    <li>
                        <span class="icon-bullet"><i class="fa-solid fa-chart-line"></i></span>
                        <span>Tableau de bord analytique : répartition par province, secteur, sexe</span>
                    </li>
                    <li>
                        <span class="icon-bullet"><i class="fa-solid fa-sync"></i></span>
                        <span>Synchronisation bidirectionnelle avec le système StatEduc Burundi</span>
                    </li>
                    <li>
                        <span class="icon-bullet"><i class="fa-solid fa-file-pdf"></i></span>
                        <span>Génération de fiches d'élèves imprimables avec filigrane officiel</span>
                    </li>
                </ul>
            </div>

            <div class="left-footer">
                <small>Accès réservé aux agents autorisés du MENERS<br>
                Données protégées — Loi n°1/03 de 2026</small>
            </div>
        </div><!-- /.login-left -->

        <!-- Panneau droit — Formulaire de connexion -->
        <div class="login-right">

            <div class="red-accent-bar"></div>

            <h2><i class="fa-solid fa-right-to-bracket me-2" style="color:var(--bi-red);font-size:1.2rem;"></i>Connexion sécurisée</h2>
            <p class="subtitle">Veuillez saisir vos identifiants pour accéder au système FIE</p>

            <!-- Message de déconnexion -->
            <?php if (isset($_GET['deconnecte'])): ?>
            <div class="alert alert-info d-flex align-items-center gap-2 py-2 small mb-3" role="alert"
                 style="background:#e0f4ff;border:1px solid #b3d9f5;color:#1a5276;border-radius:.5rem;">
                <i class="fa-solid fa-circle-info flex-shrink-0"></i>
                <span>Vous avez été déconnecté(e) avec succès.</span>
            </div>
            <?php endif; ?>

            <!-- Message d'erreur -->
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-start gap-2 py-2 small mb-3" role="alert">
                <i class="fa-solid fa-circle-exclamation flex-shrink-0 mt-1"></i>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <form method="post" action="<?= BASE_URL ?>/connexion" novalidate>
                <?= SecurityHelper::csrfField() ?>

                <?php if (!empty($_GET['redirect'])): ?>
                <input type="hidden" name="redirect"
                       value="<?= htmlspecialchars($_GET['redirect'], ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>

                <!-- Identifiant -->
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fa-solid fa-user me-1" style="color:var(--bi-red)"></i>Identifiant
                        <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-radius:.5rem 0 0 .5rem;">
                            <i class="fa-solid fa-user" style="color:var(--bi-red)"></i>
                        </span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control<?= !empty($error) ? ' is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="username"
                            autofocus
                            required
                            placeholder="Votre identifiant FIE"
                            aria-required="true"
                        >
                    </div>
                </div>

                <!-- Mot de passe -->
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fa-solid fa-lock me-1" style="color:var(--bi-red)"></i>Mot de passe
                        <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-radius:.5rem 0 0 .5rem;">
                            <i class="fa-solid fa-lock" style="color:var(--bi-red)"></i>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control<?= !empty($error) ? ' is-invalid' : '' ?>"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            aria-required="true"
                        >
                        <button class="btn btn-outline-secondary" type="button" id="togglePwd"
                                aria-label="Afficher/masquer le mot de passe" tabindex="-1">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Bouton connexion -->
                <button type="submit" class="btn-connexion">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Se connecter
                </button>
            </form>

            <!-- Note légale -->
            <p class="legal-note">
                <i class="fa-solid fa-shield-halved me-1" style="color:var(--bi-red)"></i>
                Accès réservé aux agents autorisés du MENERS.<br>
                Conforme à la loi n°1/03-2026 sur la protection des données.
            </p>

        </div><!-- /.login-right -->
    </div><!-- /.login-container -->
</div><!-- /.login-wrapper -->

<!-- ── Pied de page ─────────────────────────────────────────────────────── -->
<div class="login-footer-bar">
    <small>
        FIE Burundi — Système d'Information de Gestion de l'Éducation (SIGE) &nbsp;·&nbsp;
        DGESS / MENERS &nbsp;·&nbsp; Tous droits réservés <?= date('Y') ?>
    </small>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmU1EspL3xfC8wMw1ECNEkOsEHGU"
        crossorigin="anonymous"></script>
<script>
(function() {
    var btn = document.getElementById('togglePwd');
    var pwd = document.getElementById('password');
    var ico = document.getElementById('eyeIcon');
    if (!btn || !pwd) return;
    btn.addEventListener('click', function() {
        var show = pwd.type === 'password';
        pwd.type = show ? 'text' : 'password';
        ico.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    });
}());
</script>
</body>
</html>
