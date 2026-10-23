<?php

declare(strict_types=1);

namespace EczaneApi\Data;

class MaxMindTurkiye
{
    /**
     * MaxMind GeoIP2 Uyumlu 81 İl ve İlçe Hiyerarşik Veritabanı
     */
    public static function getHierarchy(): array
    {
        $iller = Sehirler::all();
        $hierarchy = [];

        foreach ($iller as $il) {
            $plate = (int) $il['id'];
            $iso = sprintf('TR-%02d', $plate);
            $trName = $il['name'];
            $enName = Sehirler::toAscii($trName);

            $districts = [];
            foreach ($il['districts'] as $distName) {
                $districts[] = [
                    'geoname_id' => null,
                    'name' => $distName,
                    'names' => [
                        'tr' => $distName,
                        'en' => Sehirler::toAscii($distName),
                    ],
                ];
            }

            $hierarchy[$iso] = [
                'country' => [
                    'iso_code' => 'TR',
                    'geoname_id' => 298795,
                    'names' => [
                        'tr' => 'Türkiye',
                        'en' => 'Turkey',
                    ],
                ],
                'subdivision' => [
                    'iso_code' => $iso,
                    'plate' => $plate,
                    'geoname_id' => 745000 + $plate,
                    'names' => [
                        'tr' => $trName,
                        'en' => $enName,
                    ],
                    'time_zone' => 'Europe/Istanbul',
                ],
                'cities_count' => count($districts),
                'cities' => $districts,
            ];
        }

        return $hierarchy;
    }

    /**
     * ISO kodundan veya plaka kodundan MaxMind il bilgisini bulur
     */
    public static function findSubdivision(string|int $query): ?array
    {
        $iso = is_numeric($query) ? sprintf('TR-%02d', (int) $query) : strtoupper(trim((string) $query));
        $all = self::getHierarchy();

        if (isset($all[$iso])) {
            return $all[$iso];
        }

        // İsimle ara
        $norm = Sehirler::normalize((string) $query);
        foreach ($all as $item) {
            if (Sehirler::normalize($item['subdivision']['names']['tr']) === $norm
                || Sehirler::normalize($item['subdivision']['names']['en']) === $norm) {
                return $item;
            }
        }

        return null;
    }
}
