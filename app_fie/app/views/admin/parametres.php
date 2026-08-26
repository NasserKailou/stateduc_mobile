<?php
/**
 * FIE — Vue : Paramétrage interopérabilité StatEduc
 * GET/POST /admin/parametres
 */
$page_title  = $page_title  ?? 'Paramétrage StatEduc — FIE';
$active_menu = $active_menu ?? 'admin';
$settings    = $settings    ?? [];
$testResult  = $testResult  ?? null;
require BASE_PATH . '/app/views/layouts/app_layout.php';
$h = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Administration</a></li>
    <li class="breadcrumb-item active">Paramétrage StatEduc</li>
  </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h1 class="h4 fw-bold mb-0">
    <i class="fa-solid fa-sliders me-2" style="color:var(--fie-red)"></i>
    Paramétrage — Interopérabilité StatEduc
  </h1>
</div>

<?php if (!empty($_SESSION['fie_flash_success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
  <i class="fa-solid fa-circle-check me-2"></i>
  <?= $h($_SESSION['fie_flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['fie_flash_success']); endif; ?>

<?php if (!empty($_SESSION['fie_flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <i class="fa-solid fa-triangle-exclamation me-2"></i>
  <?= $h($_SESSION['fie_flash_error']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['fie_flash_error']); endif; ?>

<?php if ($testResult !== null): ?>
<div class="alert <?= $testResult['ok'] ? 'alert-success' : 'alert-danger' ?> mb-4">
  <i class="fa-solid <?= $testResult['ok'] ? 'fa-wifi' : 'fa-wifi-slash' ?> me-2"></i>
  <?php if ($testResult['ok']): ?>
    Connexion à <strong><?= $h($testResult['url']) ?></strong> réussie.
  <?php else: ?>
    Impossible de joindre <strong><?= $h($testResult['url']) ?></strong>. Vérifiez l'URL et le token.
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- ── Formulaire principal ──────────────────────────────────────────────── -->
  <div class="col-lg-8">
    <form method="POST" action="<?= BASE_URL ?>/admin/parametres" id="formParametres">
      <input type="hidden" name="csrf_token" value="<?= $h(SecurityHelper::generateCsrf()) ?>">
      <input type="hidden" name="action" value="save" id="formAction">

      <!-- ── Connexion StatEduc ─────────────────────────────────────────── -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
          <h5 class="mb-0 fw-semibold">
            <i class="fa-solid fa-server me-2 text-danger"></i>
            Connexion au serveur StatEduc
          </h5>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label for="stateduc_url" class="form-label fw-semibold">
              URL du serveur StatEduc <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
              <input type="url" class="form-control" id="stateduc_url" name="stateduc_url"
                     value="<?= $h($settings['stateduc_url'] ?? '') ?>"
                     placeholder="http://stateduc.ins.bi/"
                     required>
            </div>
            <div class="form-text">URL complète du serveur StatEduc avec lequel FIE se synchronise (ex: <code>http://stateduc.ins.bi/</code>)</div>
          </div>

          <div class="mb-3">
            <label for="stateduc_api_token" class="form-label fw-semibold">Token API StatEduc</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
              <input type="password" class="form-control" id="stateduc_api_token" name="stateduc_api_token"
                     value="<?= $h($settings['stateduc_api_token'] ?? '') ?>"
                     placeholder="Token Bearer pour l'authentification">
              <button class="btn btn-outline-secondary" type="button" id="toggleToken" title="Afficher/Masquer">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
            <div class="form-text">Token envoyé dans le header <code>Authorization: Bearer …</code></div>
          </div>

          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="stateduc_sync_enabled"
                     name="stateduc_sync_enabled" value="1"
                     <?= ($settings['stateduc_sync_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
              <label class="form-check-label" for="stateduc_sync_enabled">
                Synchronisation automatique activée
              </label>
            </div>
          </div>

          <div class="mb-0">
            <label for="stateduc_sync_interval_minutes" class="form-label fw-semibold">
              Intervalle de synchronisation (minutes)
            </label>
            <input type="number" class="form-control" style="max-width:200px"
                   id="stateduc_sync_interval_minutes" name="stateduc_sync_interval_minutes"
                   value="<?= (int)($settings['stateduc_sync_interval_minutes'] ?? 60) ?>"
                   min="5" max="1440">
          </div>

        </div>
      </div>

      <!-- ── API FIE exposée ────────────────────────────────────────────── -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
          <h5 class="mb-0 fw-semibold">
            <i class="fa-solid fa-plug me-2 text-success"></i>
            API FIE exposée (pour StatEduc)
          </h5>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label for="fie_api_token" class="form-label fw-semibold">Token d'accès API FIE</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-shield-halved"></i></span>
              <input type="password" class="form-control" id="fie_api_token" name="fie_api_token"
                     value="<?= $h($settings['fie_api_token'] ?? '') ?>"
                     placeholder="Token que StatEduc doit présenter pour accéder à FIE">
              <button class="btn btn-outline-secondary" type="button" id="toggleFieToken" title="Afficher/Masquer">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
            <div class="form-text">
              StatEduc doit inclure ce token pour accéder à <code>/api/etablissements</code> et aux agrégats FIE.
            </div>
          </div>

          <div class="mb-0">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="fie_api_enabled"
                     name="fie_api_enabled" value="1"
                     <?= ($settings['fie_api_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
              <label class="form-check-label" for="fie_api_enabled">
                API FIE activée (autorise StatEduc à interroger FIE)
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Boutons ────────────────────────────────────────────────────── -->
      <div class="d-flex gap-2 flex-wrap">
        <button type="submit" class="btn btn-danger">
          <i class="fa-solid fa-floppy-disk me-2"></i>Enregistrer les paramètres
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btnTest">
          <i class="fa-solid fa-wifi me-2"></i>Tester la connexion StatEduc
        </button>
        <a href="<?= BASE_URL ?>/admin" class="btn btn-outline-secondary">
          <i class="fa-solid fa-arrow-left me-2"></i>Retour
        </a>
      </div>

    </form>
  </div>

  <!-- ── Panneau info ───────────────────────────────────────────────────────── -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-circle-info me-2 text-info"></i>Endpoints disponibles</h6>
      </div>
      <div class="card-body small">
        <p class="text-muted mb-2">FIE expose les endpoints suivants pour StatEduc :</p>
        <ul class="list-unstyled mb-0">
          <li class="mb-2">
            <code class="text-success">GET</code>
            <code><?= BASE_URL ?>/api/etablissements</code>
            <div class="text-muted">Liste des établissements miroir</div>
          </li>
          <li class="mb-2">
            <code class="text-primary">GET</code>
            <code><?= BASE_URL ?>/api/agregats</code>
            <div class="text-muted">Agrégats élèves par âge/niveau/sexe</div>
          </li>
        </ul>
        <hr class="my-2">
        <p class="text-muted mb-0">Authentification : <code>Authorization: Bearer &lt;fie_api_token&gt;</code></p>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-arrows-rotate me-2 text-warning"></i>Flux de synchronisation</h6>
      </div>
      <div class="card-body small text-muted">
        <p><strong>FIE → StatEduc :</strong> FIE pousse les agrégats élèves vers StatEduc à chaque synchronisation.</p>
        <p><strong>StatEduc → FIE :</strong> FIE tire les établissements depuis StatEduc pour alimenter son répertoire miroir.</p>
        <p class="mb-0"><strong>StatEduc → FIE API :</strong> StatEduc interroge FIE via son token pour obtenir établissements et statistiques.</p>
      </div>
    </div>
  </div>

</div>

<script>
// Toggle visibilité token
function setupToggle(btnId, inputId) {
  const btn   = document.getElementById(btnId);
  const input = document.getElementById(inputId);
  if (!btn || !input) return;
  btn.addEventListener('click', function() {
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.querySelector('i').className = 'fa-solid ' + (show ? 'fa-eye-slash' : 'fa-eye');
  });
}
setupToggle('toggleToken',    'stateduc_api_token');
setupToggle('toggleFieToken', 'fie_api_token');

// Bouton test connexion
document.getElementById('btnTest')?.addEventListener('click', function() {
  document.getElementById('formAction').value = 'test';
  document.getElementById('formParametres').submit();
});
</script>

<?php require BASE_PATH . '/app/views/layouts/app_footer.php'; ?>
