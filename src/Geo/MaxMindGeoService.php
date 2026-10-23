<?php

declare(strict_types=1);

namespace EczaneApi\Geo;

use EczaneApi\Data\MaxMindTurkiye;
use EczaneApi\Data\Sehirler;
use EczaneApi\DTO\MaxMindCityDto;
use MaxMind\Db\Reader;

class MaxMindGeoService
{
    private static ?Reader $reader = null;
    private static bool $initialized = false;
    private string $dbPath;

    public function __construct(?string $dbPath = null)
    {
        $this->dbPath = $dbPath ?? (__DIR__ . '/../../storage/geoip/GeoLite2-City.mmdb');
        $this->initReader();
    }

    private function initReader(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        if (file_exists($this->dbPath) && class_exists(Reader::class)) {
            try {
                self::$reader = new Reader($this->dbPath);
            } catch (\Throwable $e) {
                self::$reader = null;
            }
        }
    }

    /**
     * WP-Statistics (github.com/wp-statistics/GeoLite2-City) reposundan
     * GeoLite2-City.mmdb dosyasını anahtarsız ve ücretsiz olarak indirir/günceller.
     */
    public function downloadOrUpdateFromWpStatistics(): array
    {
        $urls = [
            'https://raw.githubusercontent.com/wp-statistics/GeoLite2-City/master/GeoLite2-City.mmdb',
            'https://cdn.jsdelivr.net/gh/wp-statistics/GeoLite2-City@master/GeoLite2-City.mmdb',
        ];

        $targetDir = dirname($this->dbPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $tempFile = $this->dbPath . '.tmp';
        $downloaded = false;
        $usedUrl = '';
        $lastError = null;

        foreach ($urls as $url) {
            $fp = @fopen($tempFile, 'w+');
            if (!$fp) {
                $lastError = 'Geçici MMDB dosyası açılamadı: ' . $tempFile;
                continue;
            }

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) WP-Statistics-GeoLite-Client',
            ]);

            $success = curl_exec($ch);
            $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            fclose($fp);

            if ($success && $statusCode === 200 && filesize($tempFile) > 10000000) {
                $downloaded = true;
                $usedUrl = $url;
                break;
            }

            @unlink($tempFile);
            $lastError = "URL ({$url}) HTTP {$statusCode}: {$error}";
        }

        if (!$downloaded) {
            return [
                'success' => false,
                'message' => 'WP-Statistics GeoLite2-City indirilemedi: ' . ($lastError ?? 'Bilinmeyen hata'),
            ];
        }

        // Atomik taşıma
        if (is_file($this->dbPath)) {
            @unlink($this->dbPath);
        }
        rename($tempFile, $this->dbPath);

        // Reader'ı yeniden başlat
        self::$initialized = false;
        self::$reader = null;
        $this->initReader();

        return [
            'success' => true,
            'source' => 'wp-statistics/GeoLite2-City',
            'download_url' => $usedUrl,
            'file_size_mb' => round(filesize($this->dbPath) / 1024 / 1024, 2),
            'updated_at' => date('c'),
            'message' => 'WP-Statistics GeoLite2-City veritabanı başarıyla güncellendi.',
        ];
    }

    /**
     * IP adresinden MaxMind GeoIP2 City sorgusu yapar
     */
    public function lookup(string $ip): ?MaxMindCityDto
    {
        $cleanIp = trim($ip);
        if ($cleanIp === '' || in_array($cleanIp, ['127.0.0.1', '::1', 'localhost'], true)) {
            // Yerel geliştirme için varsayılan İstanbul/Kadıköy DTO'su
            return $this->getDefaultDto($cleanIp);
        }

        if (self::$reader !== null) {
            try {
                $record = self::$reader->get($cleanIp);
                if (is_array($record)) {
                    return MaxMindCityDto::fromMaxMindRecord($record, $cleanIp);
                }
            } catch (\Throwable $e) {
                // MMDB sorgusu başarısızsa fallback'e geç
            }
        }

        return $this->getDefaultDto($cleanIp);
    }

    /**
     * İki koordinat arasındaki mesafeyi kilometre (km) cinsinden hesaplar (Haversine Formülü)
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    /**
     * Nöbetçi eczaneleri kullanıcının konumuna göre en yakından en uzağa sıralar
     */
    public static function sortByDistance(array $pharmacies, float $userLat, float $userLon): array
    {
        $withDistance = [];
        $withoutDistance = [];

        foreach ($pharmacies as $p) {
            $lat = is_object($p) ? $p->latitude : ($p['lat'] ?? $p['latitude'] ?? null);
            $lon = is_object($p) ? $p->longitude : ($p['lon'] ?? $p['longitude'] ?? null);

            if ($lat !== null && $lon !== null) {
                $dist = self::calculateDistance($userLat, $userLon, (float) $lat, (float) $lon);
                if (is_object($p)) {
                    // DTO için dizi kopyası veya dinamik property
                    $pArr = $p->toArray();
                    $pArr['distance_km'] = $dist;
                    $withDistance[] = $pArr;
                } else {
                    $p['distance_km'] = $dist;
                    $withDistance[] = $p;
                }
            } else {
                $withoutDistance[] = is_object($p) ? $p->toArray() : $p;
            }
        }

        usort($withDistance, static fn ($a, $b) => ($a['distance_km'] ?? 0) <=> ($b['distance_km'] ?? 0));
        return array_merge($withDistance, $withoutDistance);
    }

    /**
     * Geliştirme veya yerel IP'ler için varsayılan MaxMind yapısı
     */
    private function getDefaultDto(string $ip): MaxMindCityDto
    {
        return new MaxMindCityDto(
            country: [
                'iso_code' => 'TR',
                'geoname_id' => 298795,
                'names' => ['tr' => 'Türkiye', 'en' => 'Turkey'],
            ],
            subdivisions: [
                [
                    'iso_code' => 'TR-34',
                    'geoname_id' => 745044,
                    'plate' => 34,
                    'names' => ['tr' => 'İstanbul', 'en' => 'Istanbul'],
                ],
            ],
            city: [
                'geoname_id' => 744930,
                'name' => 'Kadıköy',
                'names' => ['tr' => 'Kadıköy', 'en' => 'Kadikoy'],
            ],
            location: [
                'latitude' => 40.9922,
                'longitude' => 29.0239,
                'time_zone' => 'Europe/Istanbul',
                'accuracy_radius' => 10,
            ],
            postal: ['code' => '34710'],
            ip: $ip
        );
    }
}
