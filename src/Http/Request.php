<?php

declare(strict_types=1);

namespace EczaneApi\Http;

class Request
{
    private array $queryParams;
    private array $postParams;
    private array $serverParams;
    private mixed $jsonBody = null;

    public function __construct(
        array $queryParams = [],
        array $postParams = [],
        array $serverParams = []
    ) {
        $this->queryParams = $queryParams ?: $_GET;
        $this->postParams = $postParams ?: $_POST;
        $this->serverParams = $serverParams ?: $_SERVER;

        $rawBody = file_get_contents('php://input');
        if ($rawBody) {
            $this->jsonBody = json_decode($rawBody, true);
        }
    }

    public static function capture(): self
    {
        return new self();
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->queryParams;
        }
        return $this->queryParams[$key] ?? $default;
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->postParams;
        }
        return $this->postParams[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $val = null;
        if (is_array($this->jsonBody) && array_key_exists($key, $this->jsonBody)) {
            $val = $this->jsonBody[$key];
        } elseif (array_key_exists($key, $this->postParams)) {
            $val = $this->postParams[$key];
        } elseif (array_key_exists($key, $this->queryParams)) {
            $val = $this->queryParams[$key];
        } else {
            return $default;
        }

        if (is_string($val)) {
            return str_replace("\0", '', trim($val));
        }

        return $val;
    }

    public function getMethod(): string
    {
        return strtoupper((string) ($this->serverParams['REQUEST_METHOD'] ?? 'GET'));
    }

    public function getPath(): string
    {
        $uri = (string) ($this->serverParams['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        return '/' . trim($path, '/');
    }

    public function isJson(): bool
    {
        $contentType = (string) ($this->serverParams['CONTENT_TYPE'] ?? '');
        return str_contains($contentType, 'application/json');
    }

    public function getIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($this->serverParams[$header])) {
                $ips = explode(',', (string) $this->serverParams[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        $remote = (string) ($this->serverParams['REMOTE_ADDR'] ?? '');
        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '127.0.0.1';
    }
}
