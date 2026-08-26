<?php
/**
 * FIE — Vue erreur 404 (Page introuvable)
 * Bootstrap 5 + Font Awesome — Charte Burundi
 */
$page_title  = $page_title  ?? 'Page introuvable — FIE';
$active_menu = $active_menu ?? '';
require BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">

            <!-- Icône -->
            <div class="mb-4">
                <i class="fa-solid fa-map-location-dot"
                   style="font-size:5rem;color:var(--fie-red);opacity:.35"></i>
            </div>

            <!-- Code erreur -->
            <h1 class="display-1 fw-black mb-0"
                style="font-size:8rem;color:var(--fie-red);opacity:.15;line-height:1">404</h1>

            <h2 class="h3 fw-bold mb-3" style="color:var(--fie-gray-900)">
                Page introuvable
            </h2>

            <p class="text-muted mb-4">
                La page que vous demandez n'existe pas ou a été déplacée.<br>
                Vérifiez l'adresse URL ou revenez à l'accueil.
            </p>

            <!-- Bande drapeau décorative -->
            <div class="fie-flag-strip mb-4" aria-hidden="true">
                <span class="red"></span>
                <span class="white"></span>
                <span class="green"></span>
            </div>

            <!-- Boutons d'action -->
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?= BASE_URL ?>/" class="btn btn-primary">
                    <i class="fa-solid fa-house me-2"></i>Accueil
                </a>
                <?php if (isset($_SESSION['fie_user'])): ?>
                <a href="<?= BASE_URL ?>/tableau-de-bord" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-gauge me-2"></i>Tableau de bord
                </a>
                <?php endif; ?>
                <button onclick="history.back()" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Page précédente
                </button>
            </div>

        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
