<?php

declare(strict_types=1);

namespace EczaneApi\Cache;

use EczaneApi\Contracts\CacheInterface;

class FileCache implements CacheInterface
{
    private string $storageDir;
    private int $defaultTtl;

    public function __construct(string $storageDir, int $defaultTtl = 21600)
    {
        $this->storageDir = rtrim($storageDir, '/\\');
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->storageDir)) {
            @mkdir($this->storageDir, 0750, true);
        }

        // Web erişimine karşı dizini koru
        $htaccess = $this->storageDir . DIRECTORY_SEPARATOR . '.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "<IfModule !mod_authz_core.c>\nOrder deny,allow\nDeny from all\n</IfModule>\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n");
        }
        $indexHtml = $this->storageDir . DIRECTORY_SEPARATOR . 'index.html';
        if (!file_exists($indexHtml)) {
            @file_put_contents($indexHtml, "<!DOCTYPE html><html><head><title>Access Denied</title></head><body>Forbidden</body></html>");
        }
    }

    public function get(string $key): ?array
    {
        $file = $this->getFilePath($key);
        if (!is_file($file)) {
            return null;
        }

        $content = @file_get_contents($file);
        if ($content === false || $content === '') {
            return null;
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded) || !isset($decoded['expires_at'], $decoded['data'])) {
            @unlink($file);
            return null;
        }

        if (time() > (int) $decoded['expires_at']) {
            @unlink($file);
            return null;
        }

        return (array) $decoded['data'];
    }

    public function set(string $key, array $data, int $ttl = 21600): bool
    {
        $file = $this->getFilePath($key);
        $payload = [
            'created_at' => time(),
            'expires_at' => time() + ($ttl > 0 ? $ttl : $this->defaultTtl),
            'key' => $key,
            'data' => $data,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) {
            return false;
        }

        return (bool) @file_put_contents($file, $json, LOCK_EX);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        if (is_file($file)) {
            return @unlink($file);
        }
        return true;
    }

    public function clear(): bool
    {
        if (!is_dir($this->storageDir)) {
            return true;
        }

        $files = glob($this->storageDir . '/*.cache.json');
        if ($files === false) {
            return false;
        }

        $success = true;
        foreach ($files as $file) {
            if (is_file($file)) {
                $success = @unlink($file) && $success;
            }
        }
        return $success;
    }

    public function getStats(): array
    {
        $files = glob($this->storageDir . '/*.cache.json') ?: [];
        $validCount = 0;
        $expiredCount = 0;
        $keys = [];

        foreach ($files as $file) {
            $content = @file_get_contents($file);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['expires_at'])) {
                    if (time() <= (int) $decoded['expires_at']) {
                        $validCount++;
                        $keys[] = $decoded['key'] ?? basename($file);
                    } else {
                        $expiredCount++;
                    }
                }
            }
        }

        return [
            'total_files' => count($files),
            'valid_count' => $validCount,
            'expired_count' => $expiredCount,
            'active_keys' => $keys,
            'storage_dir' => $this->storageDir,
        ];
    }

    private function getFilePath(string $key): string
    {
        $clean = trim((string) preg_replace('/[^a-zA-Z0-9_-]/', '_', $key), '_');
        $safeKey = ($clean !== '')
            ? (strlen($clean) > 60 ? substr($clean, 0, 40) . '_' . md5($key) : $clean)
            : md5($key);

        return $this->storageDir . DIRECTORY_SEPARATOR . $safeKey . '.cache.json';
    }
}
