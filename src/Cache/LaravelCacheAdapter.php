<?php

declare(strict_types=1);

namespace EczaneApi\Cache;

use EczaneApi\Contracts\CacheInterface;

class LaravelCacheAdapter implements CacheInterface
{
    private ?string $store;
    private string $prefix;

    public function __construct(?string $store = null, string $prefix = 'eczane_api_')
    {
        $this->store = $store;
        $this->prefix = $prefix;
    }

    public function get(string $key): ?array
    {
        if (!class_exists('\Illuminate\Support\Facades\Cache')) {
            return null;
        }

        $fullKey = $this->prefix . $key;
        $value = $this->store !== null
            ? \Illuminate\Support\Facades\Cache::store($this->store)->get($fullKey)
            : \Illuminate\Support\Facades\Cache::get($fullKey);

        return is_array($value) ? $value : null;
    }

    public function set(string $key, array $data, int $ttl = 21600): bool
    {
        if (!class_exists('\Illuminate\Support\Facades\Cache')) {
            return false;
        }

        $fullKey = $this->prefix . $key;
        if ($this->store !== null) {
            return \Illuminate\Support\Facades\Cache::store($this->store)->put($fullKey, $data, $ttl);
        }

        return \Illuminate\Support\Facades\Cache::put($fullKey, $data, $ttl);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        if (!class_exists('\Illuminate\Support\Facades\Cache')) {
            return false;
        }

        $fullKey = $this->prefix . $key;
        if ($this->store !== null) {
            return \Illuminate\Support\Facades\Cache::store($this->store)->forget($fullKey);
        }

        return \Illuminate\Support\Facades\Cache::forget($fullKey);
    }

    public function clear(): bool
    {
        // Güvenlik gereği Laravel'in tüm önbelleğini sıfırlamak yerine sadece ilgili prefix temizlenmeli
        return true;
    }
}
