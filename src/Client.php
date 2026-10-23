<?php

declare(strict_types=1);

namespace EczaneApi;

use EczaneApi\DTO\EczaneDto;
use EczaneApi\DTO\SehirDto;

class Client
{
    private static ?self $instance = null;
    private EczaneService $service;

    private ?string $currentIl = null;
    private ?string $currentIlce = null;
    private ?string $currentTarih = null;
    private bool $forceRefresh = false;

    public function __construct(?EczaneService $service = null)
    {
        $this->service = $service ?? new EczaneService();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * İl seçimi yapar (Fluent)
     */
    public static function il(string $il): self
    {
        $client = clone self::getInstance();
        $client->currentIl = $il;
        return $client;
    }

    /**
     * İlçe seçimi yapar (Fluent)
     */
    public function ilce(string $ilce): self
    {
        $this->currentIlce = $ilce;
        return $this;
    }

    /**
     * Tarih seçimi yapar (Fluent)
     */
    public function tarih(string $tarih): self
    {
        $this->currentTarih = $tarih;
        return $this;
    }

    /**
     * Önbelleği pas geçip güncel veriyi çekmeye zorlar (Fluent)
     */
    public function fresh(bool $force = true): self
    {
        $this->forceRefresh = $force;
        return $this;
    }

    /**
     * Seçili il/ilçe/tarih için nöbetçi eczaneleri DTO dizisi olarak getirir
     *
     * @return array<int, EczaneDto>
     */
    public function get(): array
    {
        return $this->service->getNobetciEczaneler(
            $this->currentIl,
            $this->currentIlce,
            $this->currentTarih,
            $this->forceRefresh
        );
    }

    /**
     * Seçili il/ilçe/tarih için nöbetçi eczaneleri dizi formatında getirir
     */
    public function toArray(): array
    {
        return $this->service->getNobetciRaw(
            $this->currentIl,
            $this->currentIlce,
            $this->currentTarih,
            $this->forceRefresh
        );
    }

    /**
     * Hızlı statik metod: Belirtilen il için nöbetçi eczaneleri döndürür
     *
     * @return array<int, EczaneDto>
     */
    public static function nobetci(string $il, ?string $ilce = null, ?string $tarih = null): array
    {
        return self::getInstance()->service->getNobetciEczaneler($il, $ilce, $tarih);
    }

    /**
     * 81 ili listeler
     *
     * @return array<int, SehirDto>
     */
    public static function iller(): array
    {
        return self::getInstance()->service->getIller();
    }

    /**
     * Bir ilin nöbetçi eczanelerinin bulunduğu ilçeleri listeler
     *
     * @return array<int, string>
     */
    public static function ilceler(string $il): array
    {
        return self::getInstance()->service->getIlceler($il);
    }

    /**
     * Önbelleği yeniler
     */
    public static function refresh(?string $il = null): array
    {
        return self::getInstance()->service->refresh($il);
    }

    /**
     * Önbelleği temizler
     */
    public static function clearCache(?string $il = null): bool
    {
        return self::getInstance()->service->clearCache($il);
    }

    /**
     * MaxMind / WP-Statistics GeoIP konum tespiti yapar
     */
    public static function geoLookup(?string $ip = null): ?\EczaneApi\DTO\MaxMindCityDto
    {
        $geo = new \EczaneApi\Geo\MaxMindGeoService();
        return $geo->lookup($ip ?? \EczaneApi\Http\Request::capture()->getIp());
    }

    /**
     * WP-Statistics deposundan en güncel GeoLite2-City veritabanını indirir/günceller
     */
    public static function updateGeoIp(): array
    {
        $geo = new \EczaneApi\Geo\MaxMindGeoService();
        return $geo->downloadOrUpdateFromWpStatistics();
    }

    public function getService(): EczaneService
    {
        return $this->service;
    }
}
