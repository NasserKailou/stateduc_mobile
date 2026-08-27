<?php
/**
 * annees_ws_template.php — Endpoints REST pour la gestion des années scolaires
 *
 * USAGE: Copier/adapter dans StatEduc_burundi/annees_ws.php
 *
 * TAGS: AK-YEAR-MULTI, AK-ANNEE-001
 *
 * PROBLÈME RÉSOLU (BUG-ANNEE-001):
 *   conn_dico référençait la mauvaise base (dictionnaire, pas données).
 *   Toujours utiliser $GLOBALS['conn'] pour les données métier.
 *
 * ROUTES DISPONIBLES:
 *   GET  /active/:login          → année active courante
 *   GET  /liste/:login           → toutes les années disponibles
 *   GET  /check/:login/:code     → vérifier si une année existe
 *   POST /activer/:code          → changer l'année active (admin)
 */

use \Slim\Slim;

$app = Slim::getInstance();

// ─────────────────────────────────────────────────────────────────────────────
// HELPER — connexion données (PAS conn_dico — voir BUG-ANNEE-001)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Retourne la connexion ADODB aux données métier.
 * NE PAS utiliser $GLOBALS['conn_dico'] pour les tables annees/etablissements.
 */
function _annees_conn() {
    if (isset($GLOBALS['conn']) && is_object($GLOBALS['conn'])) {
        return $GLOBALS['conn'];
    }
    // Fallback — normalement déjà initialisé par common_ws.php
    throw new RuntimeException('Connexion DB non initialisée');
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /active/:login — Année active courante
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Retourne l'année active unique.
 * Utilisé par Flutter/api_service.dart → fetchServerActiveYear()
 *
 * Réponse succès (200):
 *   {"code":"2024","libelle":"Année scolaire 2024-2025"}
 *
 * Réponse erreur (404):
 *   {"erreur":"Aucune année active configurée"}
 */
$app->get('/active/:login', function($login) use ($app) {
    try {
        $conn = _annees_conn();

        $rs = $conn->Execute(
            "SELECT code, libelle FROM annees WHERE active = 1"
        );

        if (!$rs || $rs->EOF) {
            $app->response->setStatus(404);
            echo json_encode([
                'erreur' => 'Aucune année active configurée',
                'login'  => $login,
            ]);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code'    => $rs->fields['CODE'],    // ADODB_ASSOC_CASE_UPPER
            'libelle' => $rs->fields['LIBELLE'],
        ]);

    } catch (Exception $e) {
        error_log('[annees_ws] /active error: ' . $e->getMessage());
        $app->response->setStatus(500);
        echo json_encode(['erreur' => 'Erreur serveur interne']);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /liste/:login — Toutes les années disponibles
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Retourne la liste de toutes les années scolaires (pour le dropdown Flutter).
 *
 * Réponse succès (200):
 *   [
 *     {"code":"2025","libelle":"Année scolaire 2025-2026","active":true},
 *     {"code":"2024","libelle":"Année scolaire 2024-2025","active":false}
 *   ]
 */
$app->get('/liste/:login', function($login) use ($app) {
    try {
        $conn = _annees_conn();

        $rs = $conn->Execute(
            "SELECT code, libelle, active FROM annees ORDER BY code DESC"
        );

        if (!$rs) {
            $app->response->setStatus(500);
            echo json_encode(['erreur' => 'Erreur lecture années']);
            return;
        }

        $annees = [];
        while (!$rs->EOF) {
            $annees[] = [
                'code'    => $rs->fields['CODE'],
                'libelle' => $rs->fields['LIBELLE'],
                'active'  => (int)$rs->fields['ACTIVE'] === 1,
            ];
            $rs->MoveNext();
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($annees);

    } catch (Exception $e) {
        error_log('[annees_ws] /liste error: ' . $e->getMessage());
        $app->response->setStatus(500);
        echo json_encode(['erreur' => 'Erreur serveur interne']);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /check/:login/:code — Vérifier l'existence d'une année
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Vérifie si un code d'année existe en base.
 * Utilisé par _checkYearConsistency() Flutter pour validation croisée.
 *
 * Réponse 200: {"existe":true,"code":"2024","libelle":"..."}
 * Réponse 404: {"existe":false,"code":"9999"}
 */
$app->get('/check/:login/:code', function($login, $code) use ($app) {
    try {
        $conn = _annees_conn();
        $code = trim($code);

        // Valider format code (ex: "2024", "2024-2025")
        if (!preg_match('/^[\w\-]{1,20}$/', $code)) {
            $app->response->setStatus(400);
            echo json_encode(['erreur' => 'Format code année invalide']);
            return;
        }

        $rs = $conn->Execute(
            "SELECT code, libelle FROM annees WHERE code = ?",
            [$code]
        );

        if (!$rs || $rs->EOF) {
            $app->response->setStatus(404);
            echo json_encode(['existe' => false, 'code' => $code]);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'existe'  => true,
            'code'    => $rs->fields['CODE'],
            'libelle' => $rs->fields['LIBELLE'],
        ]);

    } catch (Exception $e) {
        error_log('[annees_ws] /check error: ' . $e->getMessage());
        $app->response->setStatus(500);
        echo json_encode(['erreur' => 'Erreur serveur interne']);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /activer/:code — Changer l'année active (admin uniquement)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Change l'année active. Opération admin — à protéger par authentification.
 *
 * ATTENTION: Toutes les requêtes sans annee_code explicite utiliseront
 * la nouvelle année active après ce changement.
 *
 * Réponse 200: {"ok":true,"code":"2025"}
 * Réponse 404: {"erreur":"Année 9999 introuvable"}
 */
$app->post('/activer/:code', function($code) use ($app) {
    // TODO: Vérifier que l'appelant est admin
    // $user = _getAuthUser($app); if (!$user['admin']) { 403 }

    try {
        $conn = _annees_conn();
        $code = trim($code);

        // Vérifier que l'année existe
        $rs = $conn->Execute(
            "SELECT code FROM annees WHERE code = ?", [$code]
        );
        if (!$rs || $rs->EOF) {
            $app->response->setStatus(404);
            echo json_encode(['erreur' => "Année $code introuvable"]);
            return;
        }

        // Désactiver toutes les années
        $conn->Execute("UPDATE annees SET active = 0 WHERE active = 1");

        // Activer l'année demandée
        $conn->Execute(
            "UPDATE annees SET active = 1 WHERE code = ?", [$code]
        );

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'code' => $code]);

    } catch (Exception $e) {
        error_log('[annees_ws] /activer error: ' . $e->getMessage());
        $app->response->setStatus(500);
        echo json_encode(['erreur' => 'Erreur serveur interne']);
    }
});
