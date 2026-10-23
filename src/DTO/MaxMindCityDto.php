<?php

declare(strict_types=1);

namespace EczaneApi\DTO;

use JsonSerializable;

class MaxMindCityDto implements JsonSerializable
{
    public function __construct(
        public readonly array $country,
        public readonly array $subdivisions,
        public readonly array $city,
        public readonly array $location,
        public readonly ?array $postal = null,
        public readonly ?string $ip = null
    ) {}

    /**
     * MaxMind GeoLite2-City ham kaydından DTO oluşturur
     */
    public static function fromMaxMindRecord(array $record, ?string $ip = null): self
    {
        $country = [
            'iso_code' => $record['country']['iso_code'] ?? 'TR',
            'geoname_id' => $record['country']['geoname_id'] ?? 298795,
            'names' => [
                'tr' => $record['country']['names']['tr'] ?? 'Türkiye',
                'en' => $record['country']['names']['en'] ?? 'Turkey',
            ],
        ];

        $subdivisions = [];
        if (!empty($record['subdivisions'])) {
            foreach ($record['subdivisions'] as $sub) {
                $iso = (string) ($sub['iso_code'] ?? '');
                $plate = null;
                if (preg_match('/^TR-(\d{1,2})$/i', $iso, $m)) {
                    $plate = (int) $m[1];
                }
                $subdivisions[] = [
                    'iso_code' => $iso,
                    'geoname_id' => $sub['geoname_id'] ?? null,
                    'plate' => $plate,
                    'names' => [
                        'tr' => $sub['names']['tr'] ?? $sub['names']['en'] ?? '',
                        'en' => $sub['names']['en'] ?? $sub['names']['tr'] ?? '',
                    ],
                ];
            }
        }

        $cityNameTr = $record['city']['names']['tr'] ?? $record['city']['names']['en'] ?? '';
        $cityNameEn = $record['city']['names']['en'] ?? $record['city']['names']['tr'] ?? '';
        $city = [
            'geoname_id' => $record['city']['geoname_id'] ?? null,
            'name' => $cityNameTr ?: $cityNameEn,
            'names' => [
                'tr' => $cityNameTr,
                'en' => $cityNameEn,
            ],
        ];

        $location = [
            'latitude' => isset($record['location']['latitude']) ? (float) $record['location']['latitude'] : null,
            'longitude' => isset($record['location']['longitude']) ? (float) $record['location']['longitude'] : null,
            'time_zone' => $record['location']['time_zone'] ?? 'Europe/Istanbul',
            'accuracy_radius' => $record['location']['accuracy_radius'] ?? 10,
        ];

        $postal = isset($record['postal']['code']) ? ['code' => (string) $record['postal']['code']] : null;

        return new self(
            country: $country,
            subdivisions: $subdivisions,
            city: $city,
            location: $location,
            postal: $postal,
            ip: $ip
        );
    }

    /**
     * İl adını döndürür (Türkçe)
     */
    public function getProvinceName(): ?string
    {
        if (!empty($this->subdivisions[0]['names']['tr'])) {
            return $this->subdivisions[0]['names']['tr'];
        }
        if (!empty($this->subdivisions[0]['names']['en'])) {
            return $this->subdivisions[0]['names']['en'];
        }
        return null;
    }

    /**
     * İlçe adını döndürür (Türkçe)
     */
    public function getDistrictName(): ?string
    {
        return $this->city['names']['tr'] ?? $this->city['names']['en'] ?? null;
    }

    /**
     * Plaka kodunu döndürür
     */
    public function getPlateCode(): ?int
    {
        return $this->subdivisions[0]['plate'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'ip' => $this->ip,
            'country' => $this->country,
            'subdivisions' => $this->subdivisions,
            'city' => $this->city,
            'postal' => $this->postal,
            'location' => $this->location,
            'detected' => [
                'province' => $this->getProvinceName(),
                'district' => $this->getDistrictName(),
                'plate' => $this->getPlateCode(),
            ],
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
