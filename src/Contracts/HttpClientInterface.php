<?php

declare(strict_types=1);

namespace EczaneApi\Contracts;

interface HttpClientInterface
{
    /**
     * HTTP GET isteği gönderir.
     */
    public function get(string $url, array $headers = [], ?string $cookieJar = null): ?string;

    /**
     * HTTP POST isteği gönderir (x-www-form-urlencoded veya multipart).
     */
    public function post(string $url, array $data, array $headers = [], ?string $cookieJar = null): ?string;

    /**
     * Son HTTP yanıt kodunu döndürür.
     */
    public function getLastStatusCode(): int;

    /**
     * Son hatayı döndürür.
     */
    public function getLastError(): ?string;
}
