<?php

declare(strict_types=1);

namespace EczaneApi\Data;

class SourcesCatalog
{
    private static ?array $sourcesCache = null;
    private static ?array $odalarCache = null;
    private static ?array $belediyelerCache = null;

    public static function getSources(): array
    {
        if (self::$sourcesCache === null) {
            $path = __DIR__ . '/../../resources/data/sources.json';
            $content = file_exists($path) ? file_get_contents($path) : '{}';
            self::$sourcesCache = (array) json_decode((string) $content, true);
        }
        return self::$sourcesCache;
    }

    public static function getOdalar(): array
    {
        if (self::$odalarCache === null) {
            $path = __DIR__ . '/../../resources/data/odalar.json';
            $content = file_exists($path) ? file_get_contents($path) : '{"odalar":[]}';
            $decoded = (array) json_decode((string) $content, true);
            self::$odalarCache = (array) ($decoded['odalar'] ?? []);
        }
        return self::$odalarCache;
    }

    public static function getBelediyeler(): array
    {
        if (self::$belediyelerCache === null) {
            $path = __DIR__ . '/../../resources/data/belediyeler_api.json';
            $content = file_exists($path) ? file_get_contents($path) : '{"belediyeler":[]}';
            $decoded = (array) json_decode((string) $content, true);
            self::$belediyelerCache = (array) ($decoded['belediyeler'] ?? []);
        }
        return self::$belediyelerCache;
    }

    public static function findOdaByCity(string $city): ?array
    {
        $target = Sehirler::normalize($city);
        $odalar = self::getOdalar();

        foreach ($odalar as $oda) {
            foreach ($oda['iller'] ?? [] as $il) {
                if (Sehirler::normalize($il) === $target) {
                    return $oda;
                }
            }
        }

        return null;
    }

    public static function findOdaByPlaka(int $plaka): ?array
    {
        $odalar = self::getOdalar();
        foreach ($odalar as $oda) {
            if (in_array($plaka, $oda['plakalar'] ?? [], true)) {
                return $oda;
            }
        }
        return null;
    }
}
