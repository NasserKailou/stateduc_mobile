      </div><!-- /.container-fluid -->
    </div><!-- /.app-content -->
  </main><!-- /.app-main -->

  <!-- ── Footer barre ── -->
  <footer class="app-footer py-2 px-3" style="background:#fff;border-top:1px solid #e2e8f0;font-size:.78rem;color:#8896a7;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
      <span>
        &copy; <?= date('Y') ?> <strong>DGESS / MENERS Burundi</strong> — FIE v1.0
      </span>
      <span class="d-none d-md-inline">
        <i class="fa-solid fa-circle me-1" style="color:#28a745;font-size:.55rem;vertical-align:middle;"></i>
        Système opérationnel · Bootstrap 5.3 · AdminLTE 4 · PHP 8.2
      </span>
    </div>
  </footer>

</div><!-- /.app-wrapper -->

<!-- ── Scripts CDN ── -->
<!-- Bootstrap 5 bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

<!-- AdminLTE 4.0-beta2 JS -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/js/adminlte.min.js"
        crossorigin="anonymous"></script>

<!-- Auto-hide flash messages -->
<script>
(function() {
    document.querySelectorAll('[data-fie-autohide]').forEach(function(el) {
        var ms = parseInt(el.dataset.fieAutohide, 10) || 5000;
        setTimeout(function() {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            if (bsAlert) bsAlert.close();
        }, ms);
    });
})();
</script>

<?php if (!empty($extra_scripts)): echo $extra_scripts; endif; ?>
</body>
</html>
