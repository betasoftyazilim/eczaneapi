<?php

namespace EczaneApi\Client;

use Exception;

class EczaneClient
{
    public const DEFAULT_BASE_URL = 'https://eczaneapi.vercel.app';

    private string $baseUrl;
    private ?string $apiKey;
    private int $timeout;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null, int $timeout = 15)
    {
        $this->baseUrl = rtrim($baseUrl ?: self::DEFAULT_BASE_URL, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }

    private function request(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $filtered = array_filter($params, fn($v) => $v !== null && $v !== '');
        if (!empty($filtered)) {
            $url .= '?' . http_build_query($filtered);
        }

        $ch = curl_init();
        $headers = [
            'Accept: application/json',
            'User-Agent: EczaneAPI-PHP-SDK/1.0.0 (betasoft.com.tr)',
        ];

        if ($this->apiKey) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("Bağlantı hatası: {$curlError}");
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            $msg = $decoded['message'] ?? $decoded['error'] ?? "HTTP {$httpCode} Hatası";
            throw new Exception($msg, $httpCode);
        }

        if (is_array($decoded) && isset($decoded['data'])) {
            return $decoded['data'];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Nöbetçi eczaneleri listeler.
     */
    public function nobetci(string $il, ?string $ilce = null, ?string $tarih = null): array
    {
        $cleanIl = rawurlencode(mb_strtolower(trim($il)));
        $endpoint = "api/nobetci/{$cleanIl}";

        if ($ilce) {
            $cleanIlce = rawurlencode(mb_strtolower(trim($ilce)));
            $endpoint .= "/{$cleanIlce}";
        }

        $params = [];
        if ($tarih) {
            $params['tarih'] = $tarih;
        }

        return $this->request($endpoint, $params);
    }

    /**
     * 81 il ve bağlı ilçelerini listeler.
     */
    public function iller(): array
    {
        return $this->request('api/iller');
    }

    /**
     * Belirli bir ilin ilçelerini listeler.
     */
    public function ilceler(string $il): array
    {
        $cleanIl = rawurlencode(mb_strtolower(trim($il)));
        return $this->request("api/ilceler/{$cleanIl}");
    }

    /**
     * Koordinata en yakın nöbetçi eczaneleri listeler.
     */
    public function yakin(float $lat, float $lng, float $mesafeKm = 5.0): array
    {
        return $this->request('api/yakin', [
            'lat' => $lat,
            'lng' => $lng,
            'mesafe' => $mesafeKm,
        ]);
    }
}
