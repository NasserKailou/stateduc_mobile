<?php
$page_title  = $page_title  ?? 'Erreur serveur';
$active_menu = $active_menu ?? '';
require __DIR__ . '/../layouts/header.php';
?>
<div class="fie-empty-state" style="padding: 4rem 1rem;">
    <svg class="fie-empty-state__icon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48
                 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
    </svg>
    <h1 style="font-size:5rem;font-weight:800;color:var(--fie-red-light);line-height:1">500</h1>
    <p class="fie-empty-state__text">
        Une erreur interne s'est produite. Veuillez réessayer ou contacter l'administrateur.
    </p>
    <a href="<?= BASE_URL ?>/" class="fie-btn fie-btn--primary">Retour à l'accueil</a>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
