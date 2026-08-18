<?php
/**
 * app_fie/api/stateduc/StatEducApiClient.php
 * Client HTTP pour consommer l'API StatEduc (endpoint etabs_fie_ws.php).
 * Utilisé par SyncService pour alimenter la table miroir etablissements_miroir.
 */

class StatEducApiClient
{
    private string $baseUrl;
    private string $token;
    private int    $timeout;
    public  string $lastError = '';  // Dernière erreur pour diagnostic

    public function __construct(
        string $baseUrl  = STATEDUC_API_BASE_URL,
        string $token    = STATEDUC_API_TOKEN,
        int    $timeout  = STATEDUC_API_TIMEOUT
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token   = $token;
        $this->timeout = $timeout;
    }

    /**
     * Récupère une page d'établissements depuis l'API StatEduc.
     *
     * @param array $params  Paramètres de filtre : page, per_page, province,
     *                       secteur, updated_since, actif
     * @return array|null    Tableau ['page', 'per_page', 'total', 'pages', 'etablissements']
     * @throws RuntimeException En cas d'erreur réseau ou HTTP
     */
    public function getEtablissements(array $params = []): ?array
    {
        $defaultParams = [
            'page'     => 1,
            'per_page' => STATEDUC_SYNC_PAGE_SIZE,
        ];
        $queryParams = array_merge($defaultParams, $params);
        // L'endpoint réel est dans StatEduc_burundi/api/fie/etabs_fie_ws.php
        // (et non dans app_fie/api/stateduc/ qui est le proxy local)
        $url = $this->baseUrl . '/StatEduc_burundi/api/fie/etabs_fie_ws.php?' . http_build_query($queryParams);

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
                'User-Agent: FIE-App/1.0',
            ],
            CURLOPT_SSL_VERIFYPEER => !FIE_DEBUG, // Vérifier SSL en production
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("StatEduc API cURL error: $curlError");
        }
        if ($httpCode === 401) {
            throw new RuntimeException("StatEduc API: token invalide ou expiré (401)");
        }
        if ($httpCode !== 200) {
            throw new RuntimeException("StatEduc API: HTTP $httpCode pour $url");
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("StatEduc API: réponse JSON invalide");
        }
        if (($decoded['se_status'] ?? 0) !== 200) {
            $msg = $decoded['se_message'] ?? 'Erreur inconnue';
            throw new RuntimeException("StatEduc API: $msg");
        }

        return $decoded['se_data'] ?? null;
    }

    /**
     * Récupère le détail d'un établissement précis.
     */
    public function getEtablissementDetail(int $codeEtab): ?array
    {
        $data = $this->getEtablissements(['code_etablissement' => $codeEtab, 'per_page' => 1]);
        if (!$data || empty($data['etablissements'])) return null;
        return $data['etablissements'][0];
    }

    /**
     * Vérifie la connectivité avec le serveur StatEduc via un endpoint LÉGER
     * (api/ping.php) qui répond sans bootstrap ADOdb / SQL Server.
     *
     * Pourquoi ne PAS utiliser getEtablissements() ici ?
     *   → getEtablissements() déclenche la connexion SQL Server + ADOdb qui
     *     peut prendre 5–30 s → timeout systématique à 30 s au niveau cURL.
     *   → api/ping.php répond immédiatement (< 100 ms), sans aucune DB.
     *
     * @return bool  true si le serveur StatEduc est joignable
     */
    public function ping(): bool
    {
        $url = $this->baseUrl . '/api/ping.php';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,   // 10 s max — ce ping doit être rapide
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode === 0) return false;
        if ($httpCode !== 200) return false;

        // Vérifier que la réponse JSON contient bien status=ok
        $decoded = @json_decode((string)$response, true);
        return is_array($decoded) && ($decoded['status'] ?? '') === 'ok';
    }
}
