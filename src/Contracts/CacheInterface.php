<?php

declare(strict_types=1);

namespace EczaneApi\Contracts;

interface CacheInterface
{
    /**
     * Önbellekten anahtara ait değeri döndürür.
     */
    public function get(string $key): ?array;

    /**
     * Değeri önbelleğe yazar.
     */
    public function set(string $key, array $data, int $ttl = 21600): bool;

    /**
     * Önbellekte anahtarın var olup olmadığını ve süresinin dolup dolmadığını kontrol eder.
     */
    public function has(string $key): bool;

    /**
     * Önbellekten anahtarı siler.
     */
    public function delete(string $key): bool;

    /**
     * Tüm önbelleği temizler.
     */
    public function clear(): bool;
}
