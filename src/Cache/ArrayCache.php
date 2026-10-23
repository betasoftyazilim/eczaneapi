<?php

declare(strict_types=1);

namespace EczaneApi\Cache;

use EczaneApi\Contracts\CacheInterface;

class ArrayCache implements CacheInterface
{
    private array $storage = [];

    public function get(string $key): ?array
    {
        if (!isset($this->storage[$key])) {
            return null;
        }

        $item = $this->storage[$key];
        if (time() > $item['expires_at']) {
            unset($this->storage[$key]);
            return null;
        }

        return $item['data'];
    }

    public function set(string $key, array $data, int $ttl = 21600): bool
    {
        $this->storage[$key] = [
            'expires_at' => time() + $ttl,
            'data' => $data,
        ];
        return true;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        unset($this->storage[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->storage = [];
        return true;
    }
}
