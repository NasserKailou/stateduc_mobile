<?php
$page_title = "Nouveau mouvement — FIE"; $active_menu = 'mouvement';
require BASE_PATH . '/app/views/layouts/header.php';
?>
<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
<li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
<li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mouvement">Mouvements</a></li>
<li class="breadcrumb-item active">Nouveau</li>
</ol></nav>
<h1 class="h4 fw-bold mb-4"><i class="fa-solid fa-arrow-right-arrow-left me-2" style="color:var(--fie-red)"></i>Nouveau mouvement</h1>
<div class="alert alert-info"><i class="fa-solid fa-circle-info me-2"></i>Module en cours de développement — disponible prochainement.</div>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
