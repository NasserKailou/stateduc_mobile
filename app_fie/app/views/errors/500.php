<?php
/**
 * FIE — Vue erreur 500 (Erreur serveur interne)
 * Bootstrap 5 + Font Awesome — Charte Burundi
 */
$page_title  = $page_title  ?? 'Erreur serveur — FIE';
$active_menu = $active_menu ?? '';
require BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">

            <!-- Icône -->
            <div class="mb-4">
                <i class="fa-solid fa-triangle-exclamation"
                   style="font-size:5rem;color:#dc3545;opacity:.35"></i>
            </div>

            <!-- Code erreur -->
            <h1 class="display-1 fw-black mb-0"
                style="font-size:8rem;color:#dc3545;opacity:.15;line-height:1">500</h1>

            <h2 class="h3 fw-bold mb-3" style="color:var(--fie-gray-900)">
                Erreur interne du serveur
            </h2>

            <p class="text-muted mb-4">
                Une erreur inattendue s'est produite côté serveur.<br>
                Veuillez réessayer dans quelques instants ou contacter l'administrateur.
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
                    <i class="fa-solid fa-house me-2"></i>Retour à l'accueil
                </a>
                <button onclick="location.reload()" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-right me-2"></i>Réessayer
                </button>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-envelope me-2"></i>Contacter l'administrateur
                </a>
            </div>

            <?php if (defined('FIE_DEBUG') && FIE_DEBUG && isset($errorMessage)): ?>
            <div class="alert alert-warning text-start mt-4 small">
                <strong><i class="fa-solid fa-bug me-1"></i>Détails (mode débogage) :</strong><br>
                <code><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></code>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
