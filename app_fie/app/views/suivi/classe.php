<?php
/**
 * FIE — Vue : Suivi pédagogique — Détail d'une classe
 * Grille élèves + checkboxes décision fin d'année (AJAX)
 */
$page_title  = $page_title  ?? 'Suivi classe — FIE';
$active_menu = $active_menu ?? 'suivi';
require BASE_PATH . '/app/views/layouts/header.php';

$classe   = $classe   ?? [];
$eleves   = $eleves   ?? [];
$decisions = $decisions ?? []; // ['iue' => ['decision'=>'...','note'=>'...']]
$csrf     = SecurityHelper::csrfToken();
?>

<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Accueil</a></li>
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/suivi">Suivi pédagogique</a></li>
    <li class="breadcrumb-item active"><?= SecurityHelper::e($classe['nom_classe'] ?? 'Classe') ?></li>
  </ol>
</nav>

<!-- En-tête classe -->
<div class="d-flex align-items-start justify-content-between mb-4 gap-2 flex-wrap">
  <div>
    <h1 class="h4 fw-bold mb-1">
      <i class="fa-solid fa-door-open me-2" style="color:var(--fie-primary)"></i>
      <?= SecurityHelper::e($classe['nom_classe'] ?? '—') ?>
    </h1>
    <div class="text-muted small">
      <i class="fa-solid fa-school me-1"></i><?= SecurityHelper::e($classe['nom_etablissement'] ?? '—') ?>
      &nbsp;·&nbsp;
      <i class="fa-solid fa-calendar me-1"></i><?= SecurityHelper::e($classe['annee_scolaire'] ?? '—') ?>
      &nbsp;·&nbsp;
      <i class="fa-solid fa-user-tie me-1"></i><?= SecurityHelper::e($classe['enseignant_nom'] ?? '—') ?>
    </div>
  </div>
  <div class="d-flex gap-2">
    <button type="button" id="btn-tout-passe" class="btn btn-success btn-sm">
      <i class="fa-solid fa-check-double me-1"></i>Tous promus
    </button>
    <button type="button" id="btn-save-all" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-floppy-disk me-1"></i>Enregistrer tout
    </button>
  </div>
</div>

<!-- Légende -->
<div class="d-flex gap-2 mb-3 flex-wrap">
  <span class="badge bg-success py-2 px-3"><i class="fa-solid fa-arrow-up me-1"></i>Promu(e)</span>
  <span class="badge bg-warning text-dark py-2 px-3"><i class="fa-solid fa-rotate-left me-1"></i>Redoublant(e)</span>
  <span class="badge bg-danger py-2 px-3"><i class="fa-solid fa-user-xmark me-1"></i>Abandonne</span>
  <span class="badge bg-secondary py-2 px-3"><i class="fa-solid fa-clock me-1"></i>En attente</span>
</div>

<!-- Feedback AJAX -->
<div id="suivi-feedback" class="d-none mb-3"></div>

<!-- Grille élèves -->
<?php if (empty($eleves)): ?>
<div class="alert alert-info"><i class="fa-solid fa-circle-info me-2"></i>Aucun élève dans cette classe.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
    <span class="fw-semibold small text-muted">
      <i class="fa-solid fa-users me-1"></i><?= count($eleves) ?> élève<?= count($eleves) > 1 ? 's' : '' ?>
    </span>
    <span class="small text-muted" id="suivi-counter">
      <span id="cnt-decided">0</span> / <?= count($eleves) ?> décisions saisies
    </span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 fie-suivi-table" id="suivi-table">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>IUE</th>
          <th>Nom complet</th>
          <th>Sexe</th>
          <th>Date naissance</th>
          <th class="text-center" style="min-width:260px">Décision fin d'année</th>
          <th>Note / Obs.</th>
          <th class="text-center">Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($eleves as $i => $elv): ?>
        <?php
          $iue = SecurityHelper::e($elv['iue'] ?? '');
          $dec = $decisions[$elv['iue'] ?? ''] ?? [];
          $decVal  = $dec['decision'] ?? 'en_attente';
          $noteVal = SecurityHelper::e($dec['note_observation'] ?? '');
        ?>
        <tr data-iue="<?= $iue ?>" class="suivi-row <?= $decVal !== 'en_attente' ? 'table-light' : '' ?>">
          <td class="text-muted small"><?= $i + 1 ?></td>
          <td><code class="small"><?= $iue ?></code></td>
          <td class="fw-semibold">
            <?= SecurityHelper::e(trim(($elv['nom'] ?? '') . ' ' . ($elv['prenom'] ?? ''))) ?>
          </td>
          <td>
            <?php if (($elv['sexe'] ?? '') === 'F'): ?>
              <span class="badge bg-pink-subtle text-pink"><i class="fa-solid fa-venus"></i></span>
            <?php else: ?>
              <span class="badge bg-info-subtle text-info"><i class="fa-solid fa-mars"></i></span>
            <?php endif; ?>
          </td>
          <td class="small text-muted">
            <?= $elv['date_naissance'] ? date('d/m/Y', strtotime($elv['date_naissance'])) : '—' ?>
          </td>
          <td>
            <div class="btn-group btn-group-sm w-100 decision-group" role="group" data-iue="<?= $iue ?>">
              <input type="radio" class="btn-check" name="dec_<?= $iue ?>" id="dec_passe_<?= $iue ?>"
                     value="passe" autocomplete="off" <?= $decVal === 'passe' ? 'checked' : '' ?>>
              <label class="btn btn-outline-success" for="dec_passe_<?= $iue ?>" title="Promu(e)">
                <i class="fa-solid fa-arrow-up"></i>
              </label>

              <input type="radio" class="btn-check" name="dec_<?= $iue ?>" id="dec_redouble_<?= $iue ?>"
                     value="redouble" autocomplete="off" <?= $decVal === 'redouble' ? 'checked' : '' ?>>
              <label class="btn btn-outline-warning" for="dec_redouble_<?= $iue ?>" title="Redoublement">
                <i class="fa-solid fa-rotate-left"></i>
              </label>

              <input type="radio" class="btn-check" name="dec_<?= $iue ?>" id="dec_abandonne_<?= $iue ?>"
                     value="abandonne" autocomplete="off" <?= $decVal === 'abandonne' ? 'checked' : '' ?>>
              <label class="btn btn-outline-danger" for="dec_abandonne_<?= $iue ?>" title="Abandon">
                <i class="fa-solid fa-user-xmark"></i>
              </label>

              <input type="radio" class="btn-check" name="dec_<?= $iue ?>" id="dec_attente_<?= $iue ?>"
                     value="en_attente" autocomplete="off" <?= $decVal === 'en_attente' ? 'checked' : '' ?>>
              <label class="btn btn-outline-secondary" for="dec_attente_<?= $iue ?>" title="En attente">
                <i class="fa-solid fa-clock"></i>
              </label>
            </div>
          </td>
          <td>
            <input type="text" class="form-control form-control-sm note-input"
                   data-iue="<?= $iue ?>"
                   value="<?= $noteVal ?>"
                   placeholder="Observation…"
                   maxlength="255">
          </td>
          <td class="text-center">
            <span class="decision-badge badge
              <?= $decVal === 'passe'      ? 'bg-success' :
                 ($decVal === 'redouble'   ? 'bg-warning text-dark' :
                 ($decVal === 'abandonne'  ? 'bg-danger' : 'bg-secondary')) ?>">
              <?= $decVal === 'passe'     ? 'Promu' :
                 ($decVal === 'redouble'  ? 'Redouble' :
                 ($decVal === 'abandonne' ? 'Abandon' : 'Attente')) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-white d-flex justify-content-end gap-2 py-2">
    <a href="<?= BASE_URL ?>/suivi" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-arrow-left me-1"></i>Retour
    </a>
    <button type="button" id="btn-save-all-bottom" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-floppy-disk me-1"></i>Enregistrer tout
    </button>
  </div>
</div>
<?php endif; ?>

<script>
(function () {
  const CSRF    = <?= json_encode($csrf) ?>;
  const CLASSE_ID = <?= (int)($classe['id'] ?? 0) ?>;
  const URL_SAVE  = '<?= BASE_URL ?>/suivi/decision';

  // ── Mise à jour du compteur ─────────────────────────────────────────
  function updateCounter() {
    const decided = document.querySelectorAll(
      '.decision-group input[type="radio"]:not([value="en_attente"]):checked'
    ).length;
    document.getElementById('cnt-decided').textContent = decided;
  }

  // ── Coloriser la ligne selon la décision ────────────────────────────
  function colorRow(iue, val) {
    const row = document.querySelector(`tr[data-iue="${iue}"]`);
    if (!row) return;
    row.classList.remove('table-success', 'table-warning', 'table-danger', 'table-light');
    const map = { passe: 'table-success', redouble: 'table-warning', abandonne: 'table-danger' };
    if (map[val]) row.classList.add(map[val]);
    // Mettre à jour le badge
    const badge = row.querySelector('.decision-badge');
    if (badge) {
      const labels = { passe: ['bg-success', 'Promu'], redouble: ['bg-warning text-dark', 'Redouble'],
                       abandonne: ['bg-danger', 'Abandon'], en_attente: ['bg-secondary', 'Attente'] };
      const [cls, txt] = labels[val] || labels['en_attente'];
      badge.className = 'decision-badge badge ' + cls;
      badge.textContent = txt;
    }
  }

  // ── Enregistrement d'une décision (auto-save au clic) ───────────────
  async function saveDecision(iue, decision, note) {
    try {
      const resp = await fetch(URL_SAVE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ csrf_token: CSRF, classe_id: CLASSE_ID, iue, decision, note })
      });
      const data = await resp.json();
      if (!data.ok) throw new Error(data.error || 'Erreur inconnue');
      colorRow(iue, decision);
      updateCounter();
    } catch (e) {
      showFeedback('danger', 'Erreur : ' + e.message);
    }
  }

  // ── Enregistrer tout ────────────────────────────────────────────────
  async function saveAll() {
    const rows = document.querySelectorAll('tr[data-iue]');
    let ok = 0, err = 0;
    for (const row of rows) {
      const iue  = row.dataset.iue;
      const chk  = row.querySelector('.decision-group input[type="radio"]:checked');
      const note = row.querySelector('.note-input')?.value ?? '';
      const dec  = chk ? chk.value : 'en_attente';
      try {
        const resp = await fetch(URL_SAVE, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ csrf_token: CSRF, classe_id: CLASSE_ID, iue, decision: dec, note })
        });
        const data = await resp.json();
        if (!data.ok) throw new Error(data.error);
        colorRow(iue, dec);
        ok++;
      } catch { err++; }
    }
    updateCounter();
    showFeedback(err === 0 ? 'success' : 'warning',
      err === 0 ? `${ok} décision(s) enregistrée(s) avec succès.`
                : `${ok} enregistrée(s), ${err} erreur(s).`);
  }

  function showFeedback(type, msg) {
    const el = document.getElementById('suivi-feedback');
    el.className = `alert alert-${type} d-flex align-items-center gap-2 mb-3`;
    el.innerHTML = `<i class="fa-solid fa-circle-${type === 'success' ? 'check' : 'exclamation'}"></i><div>${msg}</div>`;
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), 5000);
  }

  // ── Événements ──────────────────────────────────────────────────────
  document.querySelectorAll('.decision-group input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function () {
      const iue  = this.closest('.decision-group').dataset.iue;
      const note = document.querySelector(`.note-input[data-iue="${iue}"]`)?.value ?? '';
      saveDecision(iue, this.value, note);
    });
  });

  document.getElementById('btn-save-all')?.addEventListener('click', saveAll);
  document.getElementById('btn-save-all-bottom')?.addEventListener('click', saveAll);

  document.getElementById('btn-tout-passe')?.addEventListener('click', function () {
    document.querySelectorAll('.decision-group').forEach(grp => {
      const radio = grp.querySelector('input[value="passe"]');
      if (radio && !radio.checked) { radio.checked = true; radio.dispatchEvent(new Event('change')); }
    });
  });

  updateCounter();
})();
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
