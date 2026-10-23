<?php

declare(strict_types=1);

namespace EczaneApi\Http;

use EczaneApi\Contracts\HttpClientInterface;

class CurlHttpClient implements HttpClientInterface
{
    private int $lastStatusCode = 0;
    private ?string $lastError = null;

    private int $timeout;
    private int $connectTimeout;
    private bool $verifySsl;
    private string $userAgent;

    public function __construct(
        int $timeout = 20,
        int $connectTimeout = 10,
        bool $verifySsl = false,
        ?string $userAgent = null
    ) {
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->verifySsl = $verifySsl;
        $this->userAgent = $userAgent ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
    }

    public function get(string $url, array $headers = [], ?string $cookieJar = null): ?string
    {
        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
        ];

        if ($cookieJar !== null) {
            $options[CURLOPT_COOKIEJAR] = $cookieJar;
            $options[CURLOPT_COOKIEFILE] = $cookieJar;
        }

        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        $this->lastStatusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastError = curl_error($ch) ?: null;
        curl_close($ch);

        return ($body !== false && $this->lastStatusCode >= 200 && $this->lastStatusCode < 400) ? (string) $body : null;
    }

    public function post(string $url, array $data, array $headers = [], ?string $cookieJar = null): ?string
    {
        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
        ];

        if ($cookieJar !== null) {
            $options[CURLOPT_COOKIEJAR] = $cookieJar;
            $options[CURLOPT_COOKIEFILE] = $cookieJar;
        }

        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        $this->lastStatusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastError = curl_error($ch) ?: null;
        curl_close($ch);

        return ($body !== false && $this->lastStatusCode >= 200 && $this->lastStatusCode < 400) ? (string) $body : null;
    }

    public function getLastStatusCode(): int
    {
        return $this->lastStatusCode;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            if (is_numeric($key)) {
                $formatted[] = (string) $value;
            } else {
                $formatted[] = "{$key}: {$value}";
            }
        }
        return $formatted;
    }
}
