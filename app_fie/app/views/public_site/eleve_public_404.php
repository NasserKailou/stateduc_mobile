<?php
/**
 * FIE — Vue : IUE introuvable (404)
 */
$base = BASE_URL;
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IUE introuvable — FIE Burundi</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<style>
body { background: #f0f7ff; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
.box { background: #fff; border-radius: 1rem; padding: 2.5rem 2rem; max-width: 420px; text-align: center;
       box-shadow: 0 20px 60px rgba(0,0,0,.10); }
.icon { font-size: 3rem; color: #CE1126; margin-bottom: 1rem; }
h1 { font-size: 1.4rem; font-weight: 700; color: #111; }
p { color: #6b7280; font-size: .9rem; }
</style>
</head>
<body>
<div class="box">
    <div class="icon">&#128683;</div>
    <h1>IUE introuvable</h1>
    <p>L'identifiant unique élève <strong><?= htmlspecialchars($_GET['iue'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
    n'existe pas dans le système FIE Burundi.<br>
    Vérifiez le QR code ou contactez l'établissement.</p>
    <a href="<?= $base ?>/" class="btn btn-primary btn-sm mt-2">Retour à l'accueil</a>
</div>
</body>
</html>
