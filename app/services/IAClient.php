<?php
// app/services/IAClient.php
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');

class IAClient
{
    private string $baseUrl;
    private string $apiKey;
    private int $connectTimeout;
    private int $timeout;

    public function __construct()
    {
        $cfg = require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/ia.php';
        $this->baseUrl        = rtrim((string)($cfg['IA_BASE_URL'] ?? ''), '/') . '/';
        $this->apiKey         = (string)($cfg['IA_API_KEY'] ?? '');
        $this->connectTimeout = (int)($cfg['CONNECT_TIMEOUT'] ?? 5);
        $this->timeout        = (int)($cfg['TIMEOUT'] ?? 25);
    }

    /** Ping al servicio de IA */
    public function health(): array
    {
        return $this->request('GET', 'health');
    }

    /**
     * Evaluación de un candidato via IA
     */
    public function predict(array $payload): array
    {
        return $this->request('POST', 'predict', $payload);
    }

    /**
     * Ranking por score de evaluación
     * $items = [
     *   ['id' => 123, 'evaluation_score' => 88],
     *   ...
     * ]
     */
    public function rank(array $items): array
    {
        // Aseguramos que $items sea un array de objetos válidos
        $clean = [];
        foreach ($items as $it) {
            if (is_string($it)) {
                $decoded = json_decode($it, true);
                if (is_array($decoded)) $it = $decoded;
            }
            if (is_array($it)) {
                $clean[] = [
                    'id' => (int)($it['id'] ?? 0),
                    'evaluation_score' => (int)($it['evaluation_score'] ?? 0)
                ];
            }
        }

        // Enviar al endpoint /rank
        return $this->request('POST', 'rank', ['items' => $clean]);
    }

    // ---------- Interno: request genérico ----------
    private function request(string $method, string $path, array $body = null): array
    {
        $url = $this->baseUrl . ltrim($path, '/');

        $ch = curl_init();
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-Key: ' . $this->apiKey,
        ];

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if (strtoupper($method) === 'POST') {
            $opts[CURLOPT_POST] = true;
            // Aquí garantizamos que el cuerpo siempre sea JSON válido
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?? [], JSON_UNESCAPED_UNICODE);
        }

        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $http= (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            throw new RuntimeException("IA request error: $err");
        }

        $json = json_decode((string)$raw, true);
        if (!is_array($json)) {
            throw new RuntimeException("IA invalid response (HTTP $http): " . substr((string)$raw,0,300));
        }

        if (isset($json['ok']) && $json['ok'] === false) {
            $msg = $json['error'] ?? "IA returned ok:false";
            throw new RuntimeException("IA error (HTTP $http): " . $msg);
        }

        return $json;
    }
}
