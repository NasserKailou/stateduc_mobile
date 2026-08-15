<?php
/**
 * FIE — Pied de page commun Bootstrap 5
 * Inclus par toutes les vues (sauf print.php qui est autonome)
 *
 * PHASE 2 — Refonte Bootstrap 5 + Font Awesome
 */
?>
</main><!-- /#fie-main-content -->

<!-- ═══════════════════════════════════════════════════
     PIED DE PAGE
     ═══════════════════════════════════════════════════ -->
<footer class="fie-footer mt-auto py-4" role="contentinfo">
  <div class="container-xl">
    <div class="row align-items-start gy-3">

      <!-- Col 1 : Identité -->
      <div class="col-lg-4">
        <div class="fie-footer__brand fw-bold mb-1">
          <i class="fa-solid fa-graduation-cap me-2 text-danger"></i>FIE — Burundi
        </div>
        <p class="fie-footer__sub small mb-1">
          Fichier Informatisé des Élèves
        </p>
        <p class="fie-footer__ministry small text-muted mb-0">
          Ministère de l'Éducation Nationale<br>et de la Recherche Scientifique (MENERS)
        </p>
      </div>

      <!-- Col 2 : Liens utiles -->
      <div class="col-lg-4">
        <nav aria-label="Liens pied de page">
          <ul class="list-unstyled d-flex flex-wrap gap-2 mb-0">
            <li>
              <a href="<?= BASE_URL ?>/" class="fie-footer__link small">
                <i class="fa-solid fa-house-chimney me-1"></i>Accueil
              </a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>/aide" class="fie-footer__link small">
                <i class="fa-solid fa-circle-question me-1"></i>Aide
              </a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>/contact" class="fie-footer__link small">
                <i class="fa-solid fa-envelope me-1"></i>Contact
              </a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>/confidentialite" class="fie-footer__link small">
                <i class="fa-solid fa-shield-halved me-1"></i>Confidentialité
              </a>
            </li>
            <li>
              <a href="<?= BASE_URL ?>/mentions-legales" class="fie-footer__link small">
                <i class="fa-solid fa-scale-balanced me-1"></i>Mentions légales
              </a>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Col 3 : Mention légale -->
      <div class="col-lg-4">
        <p class="fie-footer__legal small mb-1">
          Conformément à la loi n°1/03-2026 relative à la protection des données
          à caractère personnel au Burundi, les données collectées sont réservées
          exclusivement à des fins statistiques et administratives scolaires.
        </p>
        <p class="fie-footer__version small text-muted mb-0">
          <i class="fa-solid fa-code-branch me-1"></i>
          v<?= defined('FIE_VERSION') ? htmlspecialchars(FIE_VERSION, ENT_QUOTES, 'UTF-8') : '1.1.0' ?>
          &nbsp;|&nbsp;
          &copy; <?= date('Y') ?> SIGE Burundi — DGESS/MENERS
        </p>
      </div>

    </div><!-- /.row -->
  </div><!-- /.container-xl -->
</footer>

<!-- ═══════════════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════════════ -->
<!-- Bootstrap 5.3 JS Bundle (inclut Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmU1EspL3xfC8wMw1ECNEkOsEHGU"
        crossorigin="anonymous"></script>

<!-- FIE Vanilla JS -->
<script src="<?= BASE_URL ?>/public/js/fie.js"></script>

<?php if (isset($extra_js) && is_array($extra_js)): ?>
  <?php foreach ($extra_js as $js): ?>
    <script src="<?= htmlspecialchars($js, ENT_QUOTES, 'UTF-8') ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($inline_js) && $inline_js): ?>
<script>
<?= $inline_js ?>
</script>
<?php endif; ?>

</body>
</html>
