<?php
/**
 * ws_route_template.php — Template Slim 2.x pour un nouveau web service REST
 *
 * USAGE: Copier ce fichier et adapter pour chaque nouveau WS.
 *        Nommer le fichier: {ressource}_ws.php (ex: etablissements_ws.php)
 *
 * STRUCTURE D'UNE RÉPONSE JSON STANDARD (envelope):
 *   Succès: {"data": [...], "total": N, "annee": "2024"}
 *   Erreur: {"erreur": "Message lisible", "code": "ERR_CODE"}
 *
 * TAGS: AK-YEAR-MULTI, AK-FIX-SESSION
 *
 * POINTS D'ATTENTION:
 *   1. Utiliser $GLOBALS['conn'] (PAS conn_dico) pour données métier
 *   2. Toujours filtrer par annee_code (AK-YEAR-MULTI)
 *   3. Clés ADODB en majuscules (ADODB_ASSOC_CASE_UPPER)
 *   4. session_write_close() avant tout cURL interne
 */

// ─────────────────────────────────────────────────────────────────────────────
// BOOTSTRAP (commun à tous les WS)
// ─────────────────────────────────────────────────────────────────────────────
require_once __DIR__ . '/config_app.php';
require_once __DIR__ . '/common_ws.php';  // initialise $GLOBALS['conn'], Slim, ADODB

use \Slim\Slim;
$app = Slim::getInstance();

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS LOCAUX
// ─────────────────────────────────────────────────────────────────────────────

/** Raccourci connexion données (évite BUG-ANNEE-001) */
function _ws_conn() { return $GLOBALS['conn']; }

/**
 * Envoie une réponse JSON et termine.
 * @param mixed $data   Données à sérialiser
 * @param int   $status Code HTTP (défaut 200)
 */
function _ws_json($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Résoudre l'année à utiliser (code fourni ou année active en base).
 * @param  string|null $code
 * @return string|null  Code d'année ou null si introuvable
 */
function _ws_resolve_annee(?string $code): ?string {
    $conn = _ws_conn();
    if ($code !== null && trim($code) !== '') {
        $rs = $conn->Execute("SELECT code FROM annees WHERE code = ?", [trim($code)]);
        return ($rs && !$rs->EOF) ? $rs->fields['CODE'] : null;
    }
    $rs = $conn->Execute("SELECT code FROM annees WHERE active = 1");
    return ($rs && !$rs->EOF) ? $rs->fields['CODE'] : null;
}

// ─────────────────────────────────────────────────────────────────────────────
// ROUTE GET — Liste des ressources (filtrée par année)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * GET /{ressources}/annee/:annee_code/:login
 * GET /{ressources}/:login   (utilise l'année active)
 *
 * Remplacer {ressources} par le nom de la table (ex: etablissements)
 */
$app->get('/ressources(/annee/:annee_code)/:login', function($annee_code = null, $login) use ($app) {
    try {
        // 1. Résoudre l'année
        $annee = _ws_resolve_annee($annee_code);
        if (!$annee) {
            _ws_json(['erreur' => 'Année introuvable', 'code_recu' => $annee_code], 400);
        }

        // 2. Requête filtrée
        $conn = _ws_conn();
        $rs   = $conn->Execute(
            "SELECT id, nom, /* ...champs... */ annee_code
             FROM ressources
             WHERE annee_code = ?
             ORDER BY nom",
            [$annee]
        );

        if (!$rs) {
            _ws_json(['erreur' => 'Erreur lecture base de données'], 500);
        }

        // 3. Construire le tableau résultat
        $items = [];
        while (!$rs->EOF) {
            $items[] = [
                'id'         => $rs->fields['ID'],
                'nom'        => $rs->fields['NOM'],
                // ... autres champs
                'annee_code' => $rs->fields['ANNEE_CODE'],
            ];
            $rs->MoveNext();
        }

        // 4. Réponse envelope standard
        _ws_json([
            'data'   => $items,
            'total'  => count($items),
            'annee'  => $annee,
            'login'  => $login,
        ]);

    } catch (Exception $e) {
        error_log('[ws_template] GET /ressources error: ' . $e->getMessage());
        _ws_json(['erreur' => 'Erreur serveur interne'], 500);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// ROUTE GET — Ressource unique par ID
// ─────────────────────────────────────────────────────────────────────────────

$app->get('/ressources/:id/:login', function($id, $login) use ($app) {
    try {
        $conn = _ws_conn();
        $rs   = $conn->Execute(
            "SELECT * FROM ressources WHERE id = ?",
            [(int)$id]
        );

        if (!$rs || $rs->EOF) {
            _ws_json(['erreur' => "Ressource $id introuvable"], 404);
        }

        _ws_json([
            'id'   => $rs->fields['ID'],
            'nom'  => $rs->fields['NOM'],
            // ...
        ]);

    } catch (Exception $e) {
        error_log('[ws_template] GET /ressources/:id error: ' . $e->getMessage());
        _ws_json(['erreur' => 'Erreur serveur interne'], 500);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// ROUTE POST — Créer une ressource
// ─────────────────────────────────────────────────────────────────────────────

$app->post('/ressources/save/:login', function($login) use ($app) {
    // AK-FIX-SESSION: si cURL interne après cette route, libérer avant
    // if (session_status() === PHP_SESSION_ACTIVE) { session_write_close(); }

    try {
        // 1. Parser le payload JSON
        $raw  = $app->request->getBody();
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            _ws_json(['erreur' => 'Payload JSON invalide'], 400);
        }

        // 2. Validation des champs obligatoires
        $nom = trim($data['nom'] ?? '');
        if (empty($nom)) {
            _ws_json(['erreur' => 'Champ nom obligatoire'], 422);
        }

        // 3. Résoudre l'année
        $annee = _ws_resolve_annee($data['annee_code'] ?? null);
        if (!$annee) {
            _ws_json(['erreur' => 'Année introuvable'], 422);
        }

        // 4. Insertion
        $conn = _ws_conn();
        $ok   = $conn->Execute(
            "INSERT INTO ressources (nom, annee_code) VALUES (?, ?)",
            [$nom, $annee]
        );

        if (!$ok) {
            _ws_json(['erreur' => 'Erreur insertion'], 500);
        }

        $new_id = $conn->Insert_ID();
        _ws_json(['ok' => true, 'id' => $new_id, 'annee' => $annee], 201);

    } catch (Exception $e) {
        error_log('[ws_template] POST /ressources/save error: ' . $e->getMessage());
        _ws_json(['erreur' => 'Erreur serveur interne'], 500);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// ROUTE PUT — Mettre à jour une ressource
// ─────────────────────────────────────────────────────────────────────────────

$app->put('/ressources/:id/:login', function($id, $login) use ($app) {
    try {
        $raw  = $app->request->getBody();
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            _ws_json(['erreur' => 'Payload JSON invalide'], 400);
        }

        $conn = _ws_conn();

        // Vérifier existence
        $rs = $conn->Execute("SELECT id FROM ressources WHERE id = ?", [(int)$id]);
        if (!$rs || $rs->EOF) {
            _ws_json(['erreur' => "Ressource $id introuvable"], 404);
        }

        $nom = trim($data['nom'] ?? '');
        if (empty($nom)) {
            _ws_json(['erreur' => 'Champ nom obligatoire'], 422);
        }

        $conn->Execute(
            "UPDATE ressources SET nom = ? WHERE id = ?",
            [$nom, (int)$id]
        );

        _ws_json(['ok' => true, 'id' => (int)$id]);

    } catch (Exception $e) {
        error_log('[ws_template] PUT /ressources/:id error: ' . $e->getMessage());
        _ws_json(['erreur' => 'Erreur serveur interne'], 500);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// LANCEMENT Slim (si ce fichier est le point d'entrée)
// ─────────────────────────────────────────────────────────────────────────────
$app->run();
