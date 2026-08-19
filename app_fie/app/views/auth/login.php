<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Connexion — FIE Burundi', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1a56db">

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
        /* ── Variables ─────────────────────────────────────────── */
        :root {
            --red:          #CE1126;
            --red-dk:       #a00e1b;
            --green:        #1EB53A;
            --green-dk:     #178a2b;
            --blue:         #1a56db;
            --blue-dk:      #1343a8;
            --blue-light:   #eff6ff;
            --blue-mid:     #3b82f6;
            --navy:         #0f2749;
            --white:        #ffffff;
            --gray-50:      #f9fafb;
            --gray-100:     #f3f4f6;
            --gray-200:     #e5e7eb;
            --gray-400:     #9ca3af;
            --gray-600:     #4b5563;
            --gray-700:     #374151;
            --gray-900:     #111827;
            --shadow-card:  0 20px 60px rgba(0,0,0,0.12), 0 4px 20px rgba(0,0,0,0.08);
            --radius-lg:    1rem;
            --radius-md:    0.625rem;
            --transition:   .25s cubic-bezier(.4,0,.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Corps — fond clair dégradé ── */
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f0fe 0%, #f0f7ff 40%, #f5f9ff 70%, #e8f5ee 100%);
            display: flex;
            flex-direction: column;
            font-family: 'Open Sans', system-ui, sans-serif;
            color: var(--gray-700);
        }

        /* ── Barre institutionnelle supérieure ── */
        .gov-bar {
            background: var(--navy);
            border-bottom: 3px solid var(--blue);
            padding: 8px 0;
            flex-shrink: 0;
        }
        .gov-bar-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .gov-title {
            color: rgba(255,255,255,.75);
            font-size: 0.70rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }
        .gov-title strong {
            color: #fff;
            font-size: 0.78rem;
            display: block;
            letter-spacing: 0.02em;
            margin-top: 1px;
        }
        /* .tri-strip removed — replaced by drapeau_burundi.gif image */

        /* ── Zone principale ── */
        .login-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
        }

        /* ── Carte principale ── */
        .login-card {
            width: 100%;
            max-width: 960px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            background: var(--white);
        }

        /* ════════════════════════════════
           PANNEAU GAUCHE — Bleu institutionnel
           ════════════════════════════════ */
        .login-left {
            background: linear-gradient(155deg,
                #1343a8 0%,
                #1a56db 45%,
                #2563eb 75%,
                #1e40af 100%);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        /* Cercles décoratifs légers */
        .login-left::before {
            content: '';
            position: absolute; top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(255,255,255,0.10) 0%, transparent 70%);
            pointer-events: none;
        }
        .login-left::after {
            content: '';
            position: absolute; bottom: -40px; left: -40px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(30,181,58,0.15) 0%, transparent 70%);
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
            width: 90px;
            height: auto;
            filter: drop-shadow(0 6px 16px rgba(0,0,0,0.30));
            transition: transform var(--transition);
        }
        .armoirie-badge img:hover { transform: scale(1.04); }

        .fie-brand {
            position: relative; z-index: 1;
            color: #fff;
            text-align: center;
        }
        .fie-brand .fie-code {
            font-family: 'Poppins', sans-serif;
            font-size: 2.4rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.06em;
            line-height: 1;
        }
        .fie-brand .fie-full {
            font-size: 0.84rem;
            color: rgba(255,255,255,.85);
            margin-top: 0.35rem;
            line-height: 1.4;
            font-weight: 500;
        }
        .fie-brand .fie-sub {
            font-size: 0.72rem;
            color: rgba(255,255,255,.60);
            margin-top: 0.2rem;
        }

        .left-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent);
            margin: 1.5rem 0;
            position: relative; z-index: 1;
        }

        .features-list {
            list-style: none; padding: 0; margin: 0;
            position: relative; z-index: 1;
        }
        .features-list li {
            display: flex; align-items: flex-start; gap: 0.75rem;
            color: rgba(255,255,255,.80);
            font-size: 0.80rem; margin-bottom: 0.80rem; line-height: 1.45;
        }
        .features-list .fi-icon {
            color: rgba(255,255,255,.90);
            background: rgba(255,255,255,.15);
            width: 28px; height: 28px;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 0.78rem;
        }

        .left-footer {
            position: relative; z-index: 1;
        }
        .left-footer small {
            color: rgba(255,255,255,.40);
            font-size: 0.68rem;
        }

        /* Bande drapeau en bas du panneau gauche */
        .left-flag {
            display: flex; height: 4px;
            position: absolute; bottom: 0; left: 0; right: 0;
        }
        .left-flag span { flex: 1; }

        /* ════════════════════════════════
           PANNEAU DROIT — Formulaire blanc
           ════════════════════════════════ */
        .login-right {
            background: #fff;
            padding: 3rem 2.75rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header { margin-bottom: 2rem; }
        .form-header .form-icon {
            width: 52px; height: 52px;
            background: var(--blue-light);
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
        }
        .form-header .form-icon i {
            color: var(--blue);
            font-size: 1.4rem;
        }
        .form-header h2 {
            color: var(--gray-900);
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.3rem;
            line-height: 1.2;
        }
        .form-header .subtitle {
            color: var(--gray-400);
            font-size: 0.84rem;
            margin: 0;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--gray-700);
            margin-bottom: 0.4rem;
        }

        .form-control {
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            color: var(--gray-900);
            background: var(--gray-50);
            transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
        }
        .form-control:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(26,86,219,.13);
            background: #fff;
            outline: none;
        }
        .form-control::placeholder { color: var(--gray-400); }

        .input-group-text {
            background: var(--gray-100);
            border: 1.5px solid var(--gray-200);
            color: var(--gray-400);
            border-radius: var(--radius-md) 0 0 var(--radius-md) !important;
        }
        .input-group .form-control {
            border-left: 0;
            border-radius: 0 !important;
        }
        .input-group .btn-pw-toggle {
            border: 1.5px solid var(--gray-200);
            border-left: 0;
            background: var(--gray-100);
            color: var(--gray-400);
            border-radius: 0 var(--radius-md) var(--radius-md) 0 !important;
            padding: 0 .85rem;
            transition: background var(--transition), color var(--transition);
        }
        .input-group .btn-pw-toggle:hover {
            background: var(--gray-200);
            color: var(--gray-700);
        }
        /* Focus ring propagation sur le groupe */
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control,
        .input-group:focus-within .btn-pw-toggle {
            border-color: var(--blue);
        }

        /* Bouton connexion — bleu vif, texte blanc parfaitement lisible */
        .btn-connexion {
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dk) 100%);
            border: none;
            color: #fff !important;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 0.78rem 1rem;
            border-radius: var(--radius-md);
            letter-spacing: 0.03em;
            width: 100%;
            margin-top: 0.5rem;
            cursor: pointer;
            transition: opacity var(--transition), transform var(--transition), box-shadow var(--transition);
            box-shadow: 0 4px 14px rgba(26,86,219,.35);
        }
        .btn-connexion:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(26,86,219,.40);
        }
        .btn-connexion:active { transform: translateY(0); }

        /* Séparateur léger */
        .form-divider {
            display: flex; align-items: center; gap: .75rem;
            color: var(--gray-400); font-size: .75rem;
            margin: 1.5rem 0;
        }
        .form-divider::before, .form-divider::after {
            content: ''; flex: 1; height: 1px; background: var(--gray-200);
        }

        .legal-note {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.76rem;
            color: var(--gray-400);
            line-height: 1.55;
        }
        .legal-note i { color: var(--blue); }

        /* ── Footer ── */
        .login-footer-bar {
            background: var(--navy);
            border-top: 2px solid var(--blue);
            padding: 0.7rem 1rem;
            text-align: center;
            flex-shrink: 0;
        }
        .login-footer-bar small { color: rgba(255,255,255,.35); font-size: .68rem; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .login-card {
                grid-template-columns: 1fr;
                max-width: 440px;
                border-radius: var(--radius-lg);
            }
            .login-left { padding: 2rem 1.75rem; }
            .features-list { display: none; }
            .left-divider { margin: 1rem 0; }
            .login-right { padding: 2rem 1.75rem; }
        }

        /* ── Animation d'entrée douce ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .login-card { animation: fadeUp .45s ease both; }
    </style>
</head>
<body>

<!-- ── Barre institutionnelle ─────────────────────────────────────────── -->
<div class="gov-bar">
    <div class="gov-bar-inner">
        <div class="gov-title">
            République du Burundi
            <strong>Ministère de l'Éducation Nationale et de la Recherche Scientifique</strong>
        </div>
        <img src="<?= BASE_URL ?>/public/images/drapeau_burundi.gif"
             alt="Drapeau du Burundi"
             style="height:22px;width:auto;border-radius:2px;flex-shrink:0;">
    </div>
</div>

<!-- ── Zone principale ────────────────────────────────────────────────── -->
<div class="login-wrapper">
    <div class="login-card">

        <!-- ══ PANNEAU GAUCHE ══ -->
        <div class="login-left">

            <!-- Bande drapeau -->
            <div class="left-flag" aria-hidden="true">
                <span style="background:#CE1126;"></span>
                <span style="background:#fff;"></span>
                <span style="background:#1EB53A;"></span>
            </div>

            <div>
                <!-- Armoirie -->
                <div class="armoirie-badge">
                    <img src="<?= BASE_URL ?>/public/images/armoiries_burundi.gif"
                         alt="Armoiries de la République du Burundi">
                </div>

                <!-- Identité -->
                <div class="fie-brand">
                    <div class="fie-code">FIE</div>
                    <div class="fie-full">Fichier Informatisé des Élèves</div>
                    <div class="fie-sub">SIGE Burundi — DGESS / MENERS</div>
                </div>

                <div class="left-divider"></div>

                <!-- Fonctionnalités -->
                <ul class="features-list">
                    <li>
                        <div class="fi-icon"><i class="fa-solid fa-id-card"></i></div>
                        <span>Identifiant Unique de l'Élève (IUE) pour chaque apprenant</span>
                    </li>
                    <li>
                        <div class="fi-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <span>Détection automatique des doublons à l'échelle nationale</span>
                    </li>
                    <li>
                        <div class="fi-icon"><i class="fa-solid fa-chart-line"></i></div>
                        <span>Tableau de bord analytique — province, secteur, sexe</span>
                    </li>
                    <li>
                        <div class="fi-icon"><i class="fa-solid fa-qrcode"></i></div>
                        <span>Fiche imprimable avec QR code et filigrane officiel</span>
                    </li>
                    <li>
                        <div class="fi-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
                        <span>Synchronisation avec StatEduc Burundi</span>
                    </li>
                </ul>
            </div>

            <div class="left-footer">
                <small>Accès réservé aux agents autorisés du MENERS<br>
                Données protégées — Loi n°1/03 de 2026</small>
            </div>
        </div><!-- /.login-left -->

        <!-- ══ PANNEAU DROIT ══ -->
        <div class="login-right">

            <!-- En-tête du formulaire -->
            <div class="form-header">
                <div class="form-icon">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </div>
                <h2>Connexion sécurisée</h2>
                <p class="subtitle">Saisissez vos identifiants pour accéder au système FIE</p>
            </div>

            <!-- Message de déconnexion -->
            <?php if (isset($_GET['deconnecte'])): ?>
            <div class="alert alert-info d-flex align-items-center gap-2 py-2 small mb-3" role="alert"
                 style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:.6rem;">
                <i class="fa-solid fa-circle-check flex-shrink-0"></i>
                <span>Vous avez été déconnecté(e) avec succès.</span>
            </div>
            <?php endif; ?>

            <!-- Message d'erreur -->
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-start gap-2 py-2 small mb-3" role="alert"
                 style="border-radius:.6rem;">
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
                        Identifiant <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-user" style="font-size:.85rem;"></i>
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
                        Mot de passe <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-lock" style="font-size:.85rem;"></i>
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
                        <button class="btn-pw-toggle" type="button" id="togglePwd"
                                aria-label="Afficher/masquer le mot de passe" tabindex="-1">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Bouton connexion — bleu vif, texte blanc -->
                <button type="submit" class="btn-connexion">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Se connecter
                </button>
            </form>

            <!-- Note légale -->
            <p class="legal-note">
                <i class="fa-solid fa-shield-halved me-1"></i>
                Accès réservé aux agents autorisés du MENERS.<br>
                Conforme à la loi n°1/03-2026 sur la protection des données.
            </p>

        </div><!-- /.login-right -->
    </div><!-- /.login-card -->
</div><!-- /.login-wrapper -->

<!-- ── Pied de page ─────────────────────────────────────────────────── -->
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
