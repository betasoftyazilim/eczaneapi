<?php

declare(strict_types=1);

namespace EczaneApi\DTO;

use JsonSerializable;

class EczaneDto implements JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly string $dist,
        public readonly string $city,
        public readonly string $address,
        public readonly ?string $phone = null,
        public readonly ?string $nobetBaslangic = null,
        public readonly ?string $nobetBitis = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $tarifi = null
    ) {}

    /**
     * Ham dizi verisinden DTO oluşturur
     */
    public static function fromArray(array $data, string $city = ''): self
    {
        $name = trim((string) ($data['name'] ?? $data['eczane_adi'] ?? $data['eczane'] ?? ''));
        $dist = trim((string) ($data['dist'] ?? $data['ilce'] ?? ''));
        $cityName = trim((string) ($data['city'] ?? $data['il'] ?? $city));
        $address = trim((string) ($data['address'] ?? $data['adres'] ?? ''));
        $phone = isset($data['phone']) ? trim((string) $data['phone']) : (isset($data['telefon']) ? trim((string) $data['telefon']) : null);
        $nobetBaslangic = isset($data['nobet_baslangic']) ? trim((string) $data['nobet_baslangic']) : null;
        $nobetBitis = isset($data['nobet_bitis']) ? trim((string) $data['nobet_bitis']) : null;
        $lat = isset($data['lat']) && is_numeric($data['lat']) ? (float) $data['lat'] : (isset($data['latitude']) && is_numeric($data['latitude']) ? (float) $data['latitude'] : null);
        $lon = isset($data['lon']) && is_numeric($data['lon']) ? (float) $data['lon'] : (isset($data['longitude']) && is_numeric($data['longitude']) ? (float) $data['longitude'] : null);
        $tarifi = isset($data['tarifi']) ? trim((string) $data['tarifi']) : (isset($data['tarif']) ? trim((string) $data['tarif']) : null);

        return new self(
            name: $name,
            dist: $dist,
            city: $cityName,
            address: $address,
            phone: $phone !== '' ? $phone : null,
            nobetBaslangic: $nobetBaslangic !== '' ? $nobetBaslangic : null,
            nobetBitis: $nobetBitis !== '' ? $nobetBitis : null,
            latitude: $lat,
            longitude: $lon,
            tarifi: $tarifi !== '' ? $tarifi : null
        );
    }

    /**
     * Telefonu arama için temizler (sadece rakam ve +)
     */
    public function getCleanPhone(): ?string
    {
        if ($this->phone === null) {
            return null;
        }
        $clean = preg_replace('/[^0-9+]/', '', $this->phone);
        return $clean !== '' ? $clean : null;
    }

    /**
     * Harita yol tarifi URL'si üretir (Google Maps)
     */
    public function getMapsUrl(): string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return "https://www.google.com/maps/search/?api=1&query={$this->latitude},{$this->longitude}";
        }
        $query = urlencode("{$this->name} Eczanesi, {$this->dist}, {$this->city}");
        return "https://www.google.com/maps/search/?api=1&query={$query}";
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'dist' => $this->dist,
            'city' => $this->city,
            'address' => $this->address,
            'phone' => $this->phone,
            'phone_clean' => $this->getCleanPhone(),
            'nobet_baslangic' => $this->nobetBaslangic,
            'nobet_bitis' => $this->nobetBitis,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'tarifi' => $this->tarifi,
            'maps_url' => $this->getMapsUrl(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
