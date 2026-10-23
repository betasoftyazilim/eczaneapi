<?php

declare(strict_types=1);

namespace EczaneApi\Http;

use EczaneApi\DTO\ApiResponse;

class Response
{
    private mixed $content;
    private int $statusCode;
    private array $headers;

    public function __construct(mixed $content, int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function json(mixed $data, int $statusCode = 200, array $headers = []): self
    {
        $headers = array_merge([
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ], $headers);

        return new self($data, $statusCode, $headers);
    }

    public static function api(ApiResponse $apiResponse): self
    {
        return self::json($apiResponse->toArray(), $apiResponse->statusCode);
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                $cleanName = preg_replace('/[\r\n]/', '', (string) $name);
                $cleanValue = preg_replace('/[\r\n]/', '', (string) $value);
                header("{$cleanName}: {$cleanValue}");
            }
        }

        if (is_array($this->content) || is_object($this->content)) {
            echo json_encode($this->content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            echo (string) $this->content;
        }
    }
}
