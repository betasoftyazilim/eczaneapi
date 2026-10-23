<?php

declare(strict_types=1);

namespace EczaneApi\Cache;

use EczaneApi\Contracts\CacheInterface;
use PDO;

class PdoCacheAdapter implements CacheInterface
{
    private PDO $pdo;
    private string $table;
    private int $defaultTtl;

    public function __construct(PDO $pdo, string $table = 'harici_api_cache', int $defaultTtl = 21600)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->defaultTtl = $defaultTtl;
    }

    public function get(string $key): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT veri, guncelleme_tarihi FROM {$this->table} WHERE anahtar = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || empty($row['veri'])) {
                return null;
            }

            if (!empty($row['guncelleme_tarihi'])) {
                $updatedAt = strtotime((string) $row['guncelleme_tarihi']);
                if ($updatedAt !== false && (time() - $updatedAt) > $this->defaultTtl) {
                    return null;
                }
            }

            $decoded = json_decode((string) $row['veri'], true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function set(string $key, array $data, int $ttl = 21600): bool
    {
        try {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->table} (anahtar, veri, guncelleme_tarihi) VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE veri = VALUES(veri), guncelleme_tarihi = NOW()"
            );
            return $stmt->execute([$key, $json]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE anahtar = ?");
            return $stmt->execute([$key]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function clear(): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE anahtar LIKE 'ne-il-%'");
            return $stmt->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
