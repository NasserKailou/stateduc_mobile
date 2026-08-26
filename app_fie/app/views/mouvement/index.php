<?php
$page_title = "Mouvements d'élèves — FIE"; $active_menu = 'mouvement';
require BASE_PATH . '/app/views/layouts/app_layout.php';
?>
<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
<li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
<li class="breadcrumb-item active">Mouvements</li>
</ol></nav>
<div class="d-flex align-items-center justify-content-between mb-4">
<h1 class="h4 fw-bold mb-0"><i class="fa-solid fa-arrow-right-arrow-left me-2" style="color:var(--fie-red)"></i>Mouvements d'élèves</h1>
<a href="<?= BASE_URL ?>/mouvement/nouveau" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Nouveau mouvement</a>
</div>
<div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
<i class="fa-solid fa-wrench fa-3x mb-3 d-block" style="color:var(--fie-red);opacity:.3"></i>
<h5 class="fw-semibold">Module en cours de développement</h5>
<p class="text-muted">Les transferts et radiations d'élèves seront disponibles dans la prochaine version du FIE.</p>
<a href="<?= BASE_URL ?>/tableau-de-bord" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-gauge me-1"></i>Retour au tableau de bord</a>
</div></div>
<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
