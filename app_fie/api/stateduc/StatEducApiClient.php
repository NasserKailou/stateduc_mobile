<?php
/**
 * app_fie/api/stateduc/StatEducApiClient.php
 * Client HTTP pour consommer l'API StatEduc (endpoint etabs_fie_ws.php).
 * Utilisé par SyncService pour alimenter la table miroir etablissements_miroir.
 *
 * PRIORITÉ de configuration (ordre décroissant) :
 *   1. Table fie_settings (DB) — valeurs saisies dans le panneau Paramètres
 *   2. Variables d'environnement (STATEDUC_API_URL, STATEDUC_API_TOKEN)
 *   3. Constantes PHP config.php (STATEDUC_API_BASE_URL, STATEDUC_API_TOKEN)
 *
 * ENDPOINT appelé sur StatEduc :
 *   GET <stateduc_url>/api/fie/etabs_fie_ws.php?page=1&per_page=500
 *
 * FIX : l'URL construite pointait vers app_fie lui-même au lieu de StatEduc.
 *       Correction : utiliser STATEDUC_URL (base StatEduc) + chemin relatif StatEduc.
 */

class StatEducApiClient
{
    private string $baseUrl;
    private string $token;
    private int    $timeout;

    // ── Constructeur — résolution de config depuis DB ou constantes ───────────

    public function __construct(
        ?string $baseUrl = null,
        ?string $token   = null,
        int     $timeout = STATEDUC_API_TIMEOUT
    ) {
        // Résoudre baseUrl et token depuis fie_settings si non fournis explicitement
        [$resolvedUrl, $resolvedToken] = self::resolveConfig();

        $this->baseUrl = rtrim($baseUrl ?? $resolvedUrl, '/');
        $this->token   = $token         ?? $resolvedToken;
        $this->timeout = $timeout;
    }

    /**
     * Lit l'URL et le token depuis fie_settings (DB) en priorité.
     * Fallback : constantes PHP (config.php).
     *
     * @return array{string, string}  [$baseUrl, $token]
     */
    private static function resolveConfig(): array
    {
        $url   = STATEDUC_API_BASE_URL;
        $token = STATEDUC_API_TOKEN;

        try {
            // Lecture depuis fie_settings si la table est disponible
            $rows = Database::fetchAll(
                "SELECT cle, valeur FROM fie_settings WHERE cle IN ('stateduc_url', 'stateduc_api_token')"
            );
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $val = trim((string)($row['valeur'] ?? ''));
                    if ($val === '') continue;
                    if ($row['cle'] === 'stateduc_url')       $url   = $val;
                    if ($row['cle'] === 'stateduc_api_token') $token = $val;
                }
            }
        } catch (Throwable $e) {
            // Table pas encore créée ou erreur DB → on garde les constantes
        }

        return [$url, $token];
    }

    // ── Méthode principale : GET établissements ───────────────────────────────

    /**
     * Récupère une page d'établissements depuis l'API StatEduc.
     *
     * L'URL finale construite est :
     *   <stateduc_base_url>/api/fie/etabs_fie_ws.php?page=1&per_page=500&...
     *
     * Exemple :
     *   http://localhost:8085/stateduc_burundi/api/fie/etabs_fie_ws.php?page=1&per_page=500
     *
     * @param array $params  Paramètres de filtre : page, per_page, province,
     *                       secteur, updated_since, actif, code_etablissement
     * @return array|null    Tableau ['page', 'per_page', 'total', 'pages', 'etablissements']
     * @throws RuntimeException En cas d'erreur réseau ou réponse non-JSON
     */
    public function getEtablissements(array $params = []): ?array
    {
        $defaultParams = [
            'page'     => 1,
            'per_page' => STATEDUC_SYNC_PAGE_SIZE,
        ];
        $queryParams = array_merge($defaultParams, $params);

        // Chemin de l'endpoint côté StatEduc_burundi
        // Le fichier etabs_fie_ws.php est placé dans StatEduc_burundi/api/fie/
        $url = $this->baseUrl . '/api/fie/etabs_fie_ws.php?' . http_build_query($queryParams);

        // Garantir un timeout PHP cohérent avec cURL (évite max_execution_time 600s)
        $effectiveTimeout = max(10, min($this->timeout, 60));
        set_time_limit($effectiveTimeout + 15);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $effectiveTimeout,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->token,
                'Accept: application/json',
                'X-FIE-Client: FIE-App/1.1',
            ],
            CURLOPT_SSL_VERIFYPEER => false, // localhost HTTP — pas de SSL
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Erreur réseau / DNS
        if ($curlError) {
            throw new RuntimeException("StatEduc API — erreur réseau cURL : $curlError\nURL tentée : $url");
        }

        // Réponse non-200
        if ($httpCode === 0) {
            throw new RuntimeException("StatEduc API — connexion impossible (timeout ou hôte injoignable).\nURL tentée : $url");
        }
        if ($httpCode === 401) {
            throw new RuntimeException("StatEduc API — token invalide ou manquant (HTTP 401).\nVérifiez la configuration dans Paramètres.");
        }
        if ($httpCode === 404) {
            throw new RuntimeException("StatEduc API — endpoint introuvable (HTTP 404).\nURL tentée : $url\nVérifiez que le fichier api/fie/etabs_fie_ws.php existe dans StatEduc_burundi/.");
        }
        if ($httpCode !== 200) {
            // Inclure les 200 premiers chars de la réponse pour aider au diagnostic
            $preview = substr(strip_tags((string)$response), 0, 200);
            throw new RuntimeException("StatEduc API — HTTP $httpCode.\nURL : $url\nRéponse : $preview");
        }

        // Vérifier que la réponse est bien du JSON (pas une page PHP d'erreur)
        $trimmed = ltrim((string)$response);
        if ($trimmed !== '' && $trimmed[0] !== '{' && $trimmed[0] !== '[') {
            $preview = substr(strip_tags($trimmed), 0, 300);
            throw new RuntimeException(
                "StatEduc API — la réponse n'est pas du JSON (probablement une erreur PHP ou une page HTML).\n" .
                "URL : $url\n" .
                "Début de la réponse : $preview"
            );
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("StatEduc API — JSON invalide (json_decode a échoué).");
        }

        // Vérifier le champ se_status de la réponse StatEduc
        $seStatus = $decoded['se_status'] ?? 0;
        if ($seStatus !== 200) {
            $msg = $decoded['se_message'] ?? 'Erreur inconnue';
            throw new RuntimeException("StatEduc API — erreur applicative (se_status=$seStatus) : $msg");
        }

        return $decoded['se_data'] ?? null;
    }

    // ── Détail d'un établissement ─────────────────────────────────────────────

    public function getEtablissementDetail(int $codeEtab): ?array
    {
        $data = $this->getEtablissements(['code_etablissement' => $codeEtab, 'per_page' => 1]);
        if (!$data || empty($data['etablissements'])) return null;
        return $data['etablissements'][0];
    }

    // ── Ping ──────────────────────────────────────────────────────────────────

    /**
     * Vérifie la connectivité avec l'API StatEduc.
     * @return bool  true si l'API répond avec se_status=200
     */
    public function ping(): bool
    {
        try {
            $data = $this->getEtablissements(['per_page' => 1, 'page' => 1]);
            return $data !== null;
        } catch (Throwable $e) {
            return false;
        }
    }

    // ── Diagnostique pour le panneau Paramètres ───────────────────────────────

    /**
     * Retourne l'URL et le token réellement utilisés (pour affichage debug).
     * @return array{url: string, token_masked: string}
     */
    public function getConfig(): array
    {
        $masked = strlen($this->token) > 8
            ? substr($this->token, 0, 4) . str_repeat('*', strlen($this->token) - 8) . substr($this->token, -4)
            : '****';
        return [
            'url'          => $this->baseUrl,
            'token_masked' => $masked,
            'endpoint'     => $this->baseUrl . '/api/fie/etabs_fie_ws.php',
        ];
    }
}
