<?php
/**
 * StatEduc_burundi/fie_sync.php
 * Page administration — Synchronisation avec app_fie
 * Permet de déclencher manuellement la récupération des données FIE
 * et d'afficher le statut de la dernière synchronisation.
 */
require_once 'common.php';
require_once 'api/fie_config.php';

$page_title = 'Synchronisation FIE';
$errors  = [];
$success = '';
$result  = null;

// ── Action : déclencher la sync ───────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'sync_agregats') {

    if (FIE_API_BASE_URL === '') {
        $errors[] = 'URL FIE non configurée (FIE_API_BASE_URL vide dans api/fie_config.php).';
    } else {
        $url     = rtrim(FIE_API_BASE_URL, '/') . '/api/agregats';
        $annee   = trim($_POST['annee'] ?? '');
        if ($annee !== '') $url .= '?annee=' . urlencode($annee);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => FIE_API_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: Bearer ' . FIE_API_TOKEN,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $body     = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr !== '') {
            $errors[] = "Erreur réseau : $curlErr";
        } elseif ($httpCode !== 200) {
            $errors[] = "HTTP $httpCode depuis FIE ($url)";
        } else {
            $data = json_decode($body, true);
            if (!is_array($data)) {
                $errors[] = "Réponse JSON invalide.";
            } else {
                $result = $data;
                $success = "Données FIE récupérées avec succès.";
            }
        }
    }
}

// ── Action : récupérer les établissements FIE ─────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'sync_etabs') {
    if (FIE_API_BASE_URL === '') {
        $errors[] = 'URL FIE non configurée.';
    } else {
        $url = rtrim(FIE_API_BASE_URL, '/') . '/api/etablissements';
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => FIE_API_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: Bearer ' . FIE_API_TOKEN,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $body     = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr !== '') {
            $errors[] = "Erreur réseau : $curlErr";
        } elseif ($httpCode !== 200) {
            $errors[] = "HTTP $httpCode depuis FIE ($url)";
        } else {
            $data = json_decode($body, true);
            if (!is_array($data)) {
                $errors[] = "Réponse JSON invalide.";
            } else {
                $result  = $data;
                $success = sprintf(
                    'Établissements FIE récupérés : %d enregistrements (page %d/%d).',
                    count($data['etablissements'] ?? []),
                    $data['page'] ?? 1,
                    $data['pages'] ?? 1
                );
            }
        }
    }
}

// ── Test de connectivité ──────────────────────────────────────────────────────
$pingStatus = null;
if (FIE_API_BASE_URL !== '') {
    $pingUrl = rtrim(FIE_API_BASE_URL, '/') . '/api/ping.php';
    $ch = curl_init($pingUrl);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>5,CURLOPT_SSL_VERIFYPEER=>false]);
    curl_exec($ch);
    $pingStatus = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($page_title) ?> — StatEduc</title>
<link rel="stylesheet" href="<?= $GLOBALS['SISED_URL'] ?>client-side/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= $GLOBALS['SISED_URL'] ?>client-side/css/all.min.css">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:800px">

  <div class="d-flex align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">
      <i class="fa-solid fa-arrows-rotate me-2 text-primary"></i>
      Synchronisation FIE ↔ StatEduc
    </h1>
    <a href="administration.php" class="btn btn-sm btn-outline-secondary ms-auto">
      <i class="fa-solid fa-arrow-left me-1"></i>Retour
    </a>
  </div>

  <!-- Statut connexion -->
  <div class="alert alert-<?= ($pingStatus >= 200 && $pingStatus < 300) ? 'success' : (($pingStatus === null) ? 'secondary' : 'danger') ?> d-flex align-items-center mb-3">
    <i class="fa-solid fa-<?= ($pingStatus >= 200 && $pingStatus < 300) ? 'wifi' : 'wifi-slash' ?> me-2"></i>
    <span>
      Serveur FIE : <strong><?= htmlspecialchars(FIE_API_BASE_URL ?: '(non configuré)') ?></strong>
      — Statut :
      <?php if ($pingStatus === null): ?>
        <span class="badge bg-secondary">Non configuré</span>
      <?php elseif ($pingStatus >= 200 && $pingStatus < 300): ?>
        <span class="badge bg-success">Accessible</span>
      <?php else: ?>
        <span class="badge bg-danger">HTTP <?= $pingStatus ?></span>
      <?php endif; ?>
    </span>
  </div>

  <?php foreach ($errors as $e): ?>
  <div class="alert alert-danger">
    <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($e) ?>
  </div>
  <?php endforeach; ?>

  <?php if ($success !== ''): ?>
  <div class="alert alert-success">
    <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($success) ?>
  </div>
  <?php endif; ?>

  <div class="row g-3 mb-4">

    <!-- Agrégats élèves -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h5 class="fw-semibold mb-3">
            <i class="fa-solid fa-users me-2 text-primary"></i>Agrégats élèves FIE
          </h5>
          <p class="text-muted small">Récupère les agrégats élèves (âge/niveau/sexe) depuis app_fie.</p>
          <form method="POST">
            <input type="hidden" name="action" value="sync_agregats">
            <div class="mb-2">
              <input type="text" name="annee" class="form-control form-control-sm"
                     placeholder="Année scolaire (ex: 2025-2026)" value="">
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100"
                    <?= FIE_API_BASE_URL === '' ? 'disabled' : '' ?>>
              <i class="fa-solid fa-download me-1"></i>Récupérer les agrégats
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Établissements -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h5 class="fw-semibold mb-3">
            <i class="fa-solid fa-school me-2 text-success"></i>Établissements FIE
          </h5>
          <p class="text-muted small">Récupère la liste des établissements depuis app_fie.</p>
          <form method="POST">
            <input type="hidden" name="action" value="sync_etabs">
            <button type="submit" class="btn btn-success btn-sm w-100 mt-4"
                    <?= FIE_API_BASE_URL === '' ? 'disabled' : '' ?>>
              <i class="fa-solid fa-download me-1"></i>Récupérer les établissements
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>

  <!-- Résultat JSON -->
  <?php if ($result !== null): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
      <i class="fa-solid fa-code me-2"></i>Réponse FIE
    </div>
    <div class="card-body p-0">
      <pre class="m-0 p-3 bg-dark text-light small" style="overflow:auto;max-height:400px"><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    </div>
  </div>
  <?php endif; ?>

  <div class="mt-3 text-muted small text-center">
    Pour modifier l'URL et le token FIE : éditez <code>api/fie_config.php</code>
  </div>

</div>
</body>
</html>
