<?php
/**
 * FIE — Vue : Formulaire publication document bibliothèque
 */
$page_title  = $page_title  ?? 'Publier un document — Bibliothèque FIE';
$active_menu = $active_menu ?? 'bibliotheque_admin';
require BASE_PATH . '/app/views/layouts/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/bibliotheque/admin">Bibliothèque admin</a></li>
        <li class="breadcrumb-item active">Nouveau document</li>
    </ol>
</nav>

<div class="d-flex align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="fa-solid fa-file-arrow-up me-2" style="color:var(--fie-primary)"></i>Publier un document
    </h1>
</div>

<div class="card border-0 shadow-sm" style="max-width:780px">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/bibliotheque/admin/publier"
              enctype="multipart/form-data" id="form-publish">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

            <div class="row g-3">
                <!-- Titre -->
                <div class="col-12">
                    <label class="form-label fw-semibold" for="titre">
                        <i class="fa-solid fa-heading me-1"></i>Titre <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="titre" name="titre" class="form-control" required
                           placeholder="Ex : Annales de mathématiques — BEPC 2025"
                           value="<?= SecurityHelper::e($_SESSION['fie_form_old']['titre'] ?? '') ?>">
                </div>

                <!-- Thématique -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="thematique_id">
                        <i class="fa-solid fa-folder me-1"></i>Thématique <span class="text-danger">*</span>
                    </label>
                    <select id="thematique_id" name="thematique_id" class="form-select" required>
                        <option value="">— Choisir une thématique —</option>
                        <?php foreach ($thematiques as $t): ?>
                        <option value="<?= $t['id'] ?>">
                            <?= SecurityHelper::e($t['libelle']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Niveau scolaire -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="niveau_scolaire">
                        <i class="fa-solid fa-graduation-cap me-1"></i>Niveau scolaire
                    </label>
                    <select id="niveau_scolaire" name="niveau_scolaire" class="form-select">
                        <option value="">Tous niveaux</option>
                        <option value="Préscolaire">Préscolaire</option>
                        <option value="Primaire">Primaire</option>
                        <option value="Secondaire général">Secondaire général</option>
                        <option value="Secondaire technique">Secondaire technique</option>
                        <option value="Formation professionnelle">Formation professionnelle</option>
                        <option value="Tous niveaux">Tous niveaux</option>
                    </select>
                </div>

                <!-- Auteur + Année -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="auteur">
                        <i class="fa-solid fa-user me-1"></i>Auteur / Source
                    </label>
                    <input type="text" id="auteur" name="auteur" class="form-control"
                           placeholder="Ex : MEPS Burundi"
                           value="<?= SecurityHelper::e($_SESSION['fie_form_old']['auteur'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="annee_publication">
                        <i class="fa-solid fa-calendar me-1"></i>Année de publication
                    </label>
                    <input type="number" id="annee_publication" name="annee_publication"
                           class="form-control" min="2000" max="<?= date('Y') + 1 ?>"
                           placeholder="<?= date('Y') ?>"
                           value="<?= SecurityHelper::e($_SESSION['fie_form_old']['annee_publication'] ?? '') ?>">
                </div>

                <!-- Description -->
                <div class="col-12">
                    <label class="form-label fw-semibold" for="description">
                        <i class="fa-solid fa-align-left me-1"></i>Description
                    </label>
                    <textarea id="description" name="description" class="form-control" rows="3"
                              placeholder="Décrivez brièvement ce document…"><?= SecurityHelper::e($_SESSION['fie_form_old']['description'] ?? '') ?></textarea>
                </div>

                <!-- Fichier -->
                <div class="col-12">
                    <label class="form-label fw-semibold" for="fichier">
                        <i class="fa-solid fa-file-arrow-up me-1"></i>Fichier <span class="text-danger">*</span>
                    </label>
                    <input type="file" id="fichier" name="fichier" class="form-control" required
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip">
                    <div class="form-text">
                        Formats acceptés : PDF, Word, PowerPoint, Excel, ZIP — Max 20 Mo
                    </div>
                </div>

                <!-- Statut + Visibilité -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="statut">
                        <i class="fa-solid fa-eye me-1"></i>Statut
                    </label>
                    <select id="statut" name="statut" class="form-select">
                        <option value="publie">Publié immédiatement</option>
                        <option value="brouillon">Brouillon (non visible)</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end pb-1">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="public" name="public" value="1" checked>
                        <label class="form-check-label fw-semibold" for="public">
                            <i class="fa-solid fa-globe me-1"></i>Visible sans connexion (public)
                        </label>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i>Publier le document
                </button>
                <a href="<?= BASE_URL ?>/bibliotheque/admin" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
