<?php

namespace EczaneApi\Client;

class Eczane
{
    private static ?EczaneClient $client = null;

    public static function config(?string $baseUrl = null, ?string $apiKey = null, int $timeout = 15): void
    {
        self::$client = new EczaneClient($baseUrl, $apiKey, $timeout);
    }

    private static function getClient(): EczaneClient
    {
        if (self::$client === null) {
            self::$client = new EczaneClient();
        }
        return self::$client;
    }

    public static function nobetci(string $il, ?string $ilce = null, ?string $tarih = null): array
    {
        return self::getClient()->nobetci($il, $ilce, $tarih);
    }

    public static function iller(): array
    {
        return self::getClient()->iller();
    }

    public static function ilceler(string $il): array
    {
        return self::getClient()->ilceler($il);
    }

    public static function yakin(float $lat, float $lng, float $mesafeKm = 5.0): array
    {
        return self::getClient()->yakin($lat, $lng, $mesafeKm);
    }
}
