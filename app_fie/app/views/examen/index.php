<?php
$page_title = "Examens — FIE"; $active_menu = 'examen';
require BASE_PATH . '/app/views/layouts/header.php';
?>
<nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
<li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
<li class="breadcrumb-item active">Examens</li>
</ol></nav>
<div class="d-flex align-items-center justify-content-between mb-4">
<h1 class="h4 fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2" style="color:var(--fie-red)"></i>Examens &amp; résultats</h1>
<a href="<?= BASE_URL ?>/examen/saisie" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Saisir des résultats</a>
</div>
<div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
<i class="fa-solid fa-pen-ruler fa-3x mb-3 d-block" style="color:var(--fie-red);opacity:.3"></i>
<h5 class="fw-semibold">Module en cours de développement</h5>
<p class="text-muted">La gestion des résultats d'examens nationaux liés aux IUE sera disponible dans la prochaine version.</p>
<a href="<?= BASE_URL ?>/tableau-de-bord" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-gauge me-1"></i>Retour au tableau de bord</a>
</div></div>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
