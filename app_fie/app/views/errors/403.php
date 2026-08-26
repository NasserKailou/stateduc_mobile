<?php
/**
 * FIE — Vue erreur 403 Accès interdit
 * PHASE 2 — Bootstrap 5
 */
$page_title  = $page_title ?? 'Accès refusé (403)';
$active_menu = '';
require BASE_PATH . '/app/views/layouts/header.php';
?>
<div class="text-center py-5">
  <div class="mb-4">
    <i class="fa-solid fa-ban fa-5x" style="color:var(--fie-red);opacity:.4"></i>
  </div>
  <h1 class="display-5 fw-bold text-danger">403</h1>
  <h2 class="h4 mb-3">Accès refusé</h2>
  <p class="text-muted mb-4">
    Vous n'avez pas les droits nécessaires pour accéder à cette page.
  </p>
  <a href="<?= BASE_URL ?>/tableau-de-bord" class="btn btn-primary">
    <i class="fa-solid fa-house me-1"></i> Tableau de bord
  </a>
</div>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
