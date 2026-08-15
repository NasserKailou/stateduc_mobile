<?php
/**
 * app_fie/services/StatEducClient.php
 * ══════════════════════════════════════════════════════════════════════════════
 * Client HTTP bidirectionnel FIE ↔ StatEduc_burundi
 *
 * Responsabilités :
 *   1. Lire l'URL StatEduc depuis fie_settings (priorité) ou la constante config.php
 *   2. Envoyer des agrégats élèves depuis FIE → StatEduc (POST)
 *   3. Récupérer des établissements depuis StatEduc → FIE  (GET)
 *   4. Tester la connectivité (ping)
 *
 * Usage :
 *   $client = StatEducClient::fromSettings();   // lit fie_settings en DB
 *   $client->ping();
 *   $client->pushAgregats($rows);
 *   $client->getEtablissements(['page'=>1]);
 *
 * @author   Projet FIE / SIGE Burundi
 * @version  1.0.0
 * @date     2026-08-15
 */

declare(strict_types=1);

class StatEducClient
{
    private string $baseUrl;
    private string $token;
    private int    $timeout;

    // ─── Constructeur ────────────────────────────────────────────────────────

    public function __construct(
        string $baseUrl,
        string $token   = '',
        int    $timeout = 30
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token   = $token;
        $this->timeout = $timeout;
    }

    // ─── Factory : lit depuis fie_settings ──────────────────────────────────

    /**
     * Crée une instance en lisant l'URL et le token depuis la table fie_settings.
     * Fallback sur les constantes de config.php si la table est vide.
     */
    public static function fromSettings(): self
    {
        $url   = '';
        $token = '';

        // Tenter de lire depuis fie_settings
        try {
            $urlRow   = Database::fetchOne(
                "SELECT valeur FROM fie_settings WHERE cle = 'stateduc_url' LIMIT 1"
            );
            $tokenRow = Database::fetchOne(
                "SELECT valeur FROM fie_settings WHERE cle = 'stateduc_api_token' LIMIT 1"
            );

            $url   = trim($urlRow['valeur']  ?? '');
            $token = trim($tokenRow['valeur'] ?? '');
        } catch (\Throwable $e) {
            // Table peut ne pas exister encore (avant migration SQL)
        }

        // Fallback sur les constantes de configuration
        if ($url === '') {
            $url = defined('STATEDUC_API_BASE_URL') ? STATEDUC_API_BASE_URL : '';
        }
        if ($token === '') {
            $token = defined('STATEDUC_API_TOKEN') ? STATEDUC_API_TOKEN : '';
        }

        return new self($url, $token);
    }

    // ─── Accesseurs ──────────────────────────────────────────────────────────

    public function getBaseUrl(): string { return $this->baseUrl; }
    public function getToken(): string   { return $this->token; }

    // ─── Test de connectivité ────────────────────────────────────────────────

    /**
     * Vérifie que l'URL StatEduc est joignable.
     *
     * @return bool  true = serveur répond
     */
    public function ping(): bool
    {
        if ($this->baseUrl === '') return false;

        try {
            $result = $this->doRequest('GET', '/api/ping.php', [], [], 5);
            return ($result['http_code'] >= 200 && $result['http_code'] < 500);
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ─── Pousser des agrégats FIE → StatEduc ─────────────────────────────────

    /**
     * Envoie des agrégats élèves (par âge / niveau / sexe) au serveur StatEduc.
     *
     * @param  array  $agregats    Tableau de lignes agrégées
     * @param  string $annee       Année scolaire (ex: '2025-2026')
     * @return array               Réponse JSON décodée
     * @throws \RuntimeException   En cas d'erreur réseau ou de refus HTTP
     */
    public function pushAgregats(array $agregats, string $annee = ''): array
    {
        $payload = [
            'source'    => 'app_fie',
            'annee'     => $annee,
            'agregats'  => $agregats,
            'pushed_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->doRequest(
            'POST',
            '/api/fie_agregats_ws.php',
            [],
            $payload
        );

        return $this->parseJsonResponse($result, 'pushAgregats');
    }

    // ─── Récupérer des établissements depuis StatEduc ─────────────────────────

    /**
     * Récupère une page d'établissements depuis l'API StatEduc.
     *
     * @param  array  $params   Filtres : page, per_page, province, secteur, updated_since
     * @return array            Tableau ['page', 'per_page', 'total', 'pages', 'etablissements']
     * @throws \RuntimeException
     */
    public function getEtablissements(array $params = []): array
    {
        $defaults = [
            'page'     => 1,
            'per_page' => defined('STATEDUC_SYNC_PAGE_SIZE') ? STATEDUC_SYNC_PAGE_SIZE : 500,
        ];
        $query = array_merge($defaults, $params);

        $result = $this->doRequest(
            'GET',
            '/app_fie/api/stateduc/etabs_fie_ws.php',
            $query
        );

        $decoded = $this->parseJsonResponse($result, 'getEtablissements');
        return $decoded['se_data'] ?? $decoded;
    }

    // ─── Envoyer les établissements FIE vers StatEduc_mobile ─────────────────

    /**
     * Pousse les établissements miroir vers un endpoint StatEduc_mobile.
     *
     * @param  array  $etablissements   Liste d'établissements
     * @return array                    Réponse décodée
     * @throws \RuntimeException
     */
    public function pushEtablissements(array $etablissements): array
    {
        $payload = [
            'source'         => 'app_fie',
            'etablissements' => $etablissements,
            'pushed_at'      => date('Y-m-d H:i:s'),
        ];

        $result = $this->doRequest(
            'POST',
            '/api/fie_etablissements_ws.php',
            [],
            $payload
        );

        return $this->parseJsonResponse($result, 'pushEtablissements');
    }

    // ─── Méthode HTTP générique ───────────────────────────────────────────────

    /**
     * Exécute une requête HTTP via cURL.
     *
     * @param  string  $method     GET | POST
     * @param  string  $endpoint   Chemin relatif (ex: '/api/ping.php')
     * @param  array   $query      Paramètres GET
     * @param  array   $body       Corps JSON (POST)
     * @param  int     $timeout    Timeout override
     * @return array               ['http_code'=>int, 'body'=>string, 'error'=>string]
     */
    private function doRequest(
        string $method,
        string $endpoint,
        array  $query   = [],
        array  $body    = [],
        int    $timeout = 0
    ): array {
        if ($this->baseUrl === '') {
            throw new \RuntimeException('StatEducClient: URL StatEduc non configurée dans fie_settings.');
        }

        $url = $this->baseUrl . $endpoint;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $timeout = $timeout > 0 ? $timeout : $this->timeout;

        $headers = [
            'Accept: application/json',
            'User-Agent: FIE-App/' . (defined('FIE_VERSION') ? FIE_VERSION : '1.0'),
        ];
        if ($this->token !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => defined('FIE_DEBUG') ? !FIE_DEBUG : true,
        ];

        if ($method === 'POST') {
            $jsonBody          = json_encode($body);
            $opts[CURLOPT_POST]           = true;
            $opts[CURLOPT_POSTFIELDS]     = $jsonBody;
            $opts[CURLOPT_HTTPHEADER][]   = 'Content-Type: application/json';
            $opts[CURLOPT_HTTPHEADER][]   = 'Content-Length: ' . strlen($jsonBody);
        }

        curl_setopt_array($ch, $opts);
        $responseBody = curl_exec($ch);
        $httpCode     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'body'      => (string) $responseBody,
            'error'     => $curlError,
        ];
    }

    // ─── Décodage JSON centralisé ─────────────────────────────────────────────

    private function parseJsonResponse(array $result, string $context): array
    {
        if ($result['error'] !== '') {
            throw new \RuntimeException("StatEduc [{$context}] cURL error: {$result['error']}");
        }

        $code = $result['http_code'];

        if ($code === 401) {
            throw new \RuntimeException("StatEduc [{$context}]: token invalide ou expiré (HTTP 401).");
        }
        if ($code === 403) {
            throw new \RuntimeException("StatEduc [{$context}]: accès refusé (HTTP 403).");
        }
        if ($code === 0) {
            throw new \RuntimeException("StatEduc [{$context}]: serveur inaccessible (timeout ou réseau).");
        }
        if ($code < 200 || $code >= 300) {
            throw new \RuntimeException("StatEduc [{$context}]: HTTP {$code}.");
        }

        $decoded = json_decode($result['body'], true);
        if (!is_array($decoded)) {
            throw new \RuntimeException("StatEduc [{$context}]: réponse JSON invalide.");
        }

        return $decoded;
    }
}
