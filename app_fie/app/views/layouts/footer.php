<?php
/**
 * FIE — Pied de page commun
 * Inclus par toutes les vues (sauf print.php qui est autonome)
 * Variables attendues depuis la vue parente : aucune obligatoire
 */
?>
</main><!-- /#fie-main-content -->

<!-- ── Pied de page ──────────────────────────────────────────────────────── -->
<footer class="fie-footer" role="contentinfo">
    <div class="fie-footer__inner">

        <!-- Bande institutionnelle -->
        <div class="fie-footer__brand">
            <span class="fie-footer__logo-text">
                <strong>FIE</strong> — Fichier Informatisé des Élèves
            </span>
            <span class="fie-footer__sep" aria-hidden="true">·</span>
            <span class="fie-footer__ministry">
                Ministère de l'Éducation Nationale et de la Recherche Scientifique
            </span>
        </div>

        <!-- Liens utiles -->
        <nav class="fie-footer__nav" aria-label="Liens pied de page">
            <a href="<?= BASE_URL ?>/" class="fie-footer__link">Accueil</a>
            <a href="<?= BASE_URL ?>/aide" class="fie-footer__link">Aide</a>
            <a href="<?= BASE_URL ?>/contact" class="fie-footer__link">Contact</a>
            <a href="<?= BASE_URL ?>/confidentialite" class="fie-footer__link">
                Confidentialité
            </a>
            <a href="<?= BASE_URL ?>/mentions-legales" class="fie-footer__link">
                Mentions légales
            </a>
        </nav>

        <!-- Mentions légales courtes -->
        <div class="fie-footer__legal">
            <p>
                Conformément à la loi n°1/03-2026 relative à la protection des données
                à caractère personnel au Burundi, les données collectées sont réservées
                exclusivement à des fins statistiques et administratives scolaires.
            </p>
            <p class="fie-footer__version">
                Version <?= defined('FIE_VERSION') ? htmlspecialchars(FIE_VERSION, ENT_QUOTES, 'UTF-8') : '1.0.0' ?>
                &nbsp;|&nbsp;
                &copy; <?= date('Y') ?> SIGE Burundi — DGESS/MENERS
            </p>
        </div>

    </div><!-- /.fie-footer__inner -->
</footer>

<!-- ── Scripts JS de fin de page ─────────────────────────────────────────── -->
<!-- Bibliothèque utilitaire commune (ne charge pas jQuery — vanilla JS) -->
<script src="<?= BASE_URL ?>/public/js/fie.js"></script>

<?php if (isset($extra_js) && is_array($extra_js)): ?>
    <?php foreach ($extra_js as $js): ?>
        <script src="<?= htmlspecialchars($js, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($inline_js) && $inline_js): ?>
    <script><?= $inline_js ?></script>
<?php endif; ?>

</body>
</html>
