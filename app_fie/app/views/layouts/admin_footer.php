
      </div><!-- /.container-fluid -->
    </div><!-- /.app-content -->
  </main><!-- /.app-main -->

  <!-- ── FOOTER ────────────────────────────────────────────── -->
  <footer class="app-footer py-2" style="background:#f4f6f9;border-top:1px solid #dee2e6;">
    <div class="container-fluid d-flex justify-content-between align-items-center small text-muted">
      <span>
        <strong>FIE Burundi</strong> — Administration &copy; <?= date('Y') ?>
        &nbsp;|&nbsp; v<?= defined('FIE_VERSION') ? FIE_VERSION : '1.x' ?>
      </span>
      <span>
        <i class="fa-solid fa-server me-1"></i>
        <?= defined('FIE_ENV') ? strtoupper(FIE_ENV) : 'DEV' ?>
      </span>
    </div>
  </footer>

</div><!-- /.app-wrapper -->

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
<!-- AdminLTE 4 JS -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/js/adminlte.min.js"
        crossorigin="anonymous"></script>

<?php if (isset($extra_scripts)): echo $extra_scripts; endif; ?>
</body>
</html>
