<?php

declare(strict_types=1);

namespace EczaneApi\Sources;

use DOMDocument;
use DOMXPath;
use EczaneApi\Contracts\EczaneSourceInterface;
use EczaneApi\Contracts\HttpClientInterface;
use EczaneApi\Data\Sehirler;
use EczaneApi\Http\CurlHttpClient;

class EDevletSource implements EczaneSourceInterface
{
    private const BASE_URL = 'https://www.turkiye.gov.tr/saglik-titck-nobetci-eczane-sorgulama';
    private const SUBMIT_URL = 'https://www.turkiye.gov.tr/saglik-titck-nobetci-eczane-sorgulama?submit';

    private HttpClientInterface $httpClient;
    private ?string $lastError = null;

    public function __construct(?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    public function getName(): string
    {
        return 'edevlet';
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function fetch(string $ilAdi, ?string $tarih = null): ?array
    {
        $plakaKodu = Sehirler::getPlakaKodu($ilAdi);
        if ($plakaKodu === null) {
            $this->lastError = "Geçersiz şehir adı veya plaka bulunamadı: '{$ilAdi}'";
            return null;
        }

        $resmiIlAdi = Sehirler::getResmiAd($plakaKodu) ?? $ilAdi;
        $cookieJar = tempnam(sys_get_temp_dir(), 'edvlt_ecz_');
        if ($cookieJar === false) {
            $this->lastError = 'Geçici cookie dosyası oluşturulamadı.';
            return null;
        }

        try {
            // 1. Adım: Form sayfasını ve CSRF tokenını al
            $pageHtml = $this->httpClient->get(self::BASE_URL, [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7',
            ], $cookieJar);

            if ($pageHtml === null || $pageHtml === '') {
                $this->lastError = 'e-Devlet sayfası yüklenemedi: ' . ($this->httpClient->getLastError() ?? 'Bilinmeyen ağ hatası');
                return null;
            }

            // CSRF Token yakalama
            if (!preg_match('/name=["\']token["\']\s+value=["\']([^"\']+)["\']/', $pageHtml, $matches)) {
                $this->lastError = 'e-Devlet sayfasından CSRF token ayrıştırılamadı.';
                return null;
            }
            $token = $matches[1];

            // Tarih formatı (d/m/Y)
            $sorguTarihi = $tarih !== null ? $this->formatTarih($tarih) : date('d/m/Y');

            // 2. Adım: Form sorgusunu POST et
            $postData = [
                'ilkod-address-il' => (string) $plakaKodu,
                'ilkod-address-ilce' => '',
                'ilkod-full' => '',
                'ilkod' => (string) $plakaKodu,
                'nobetTarihi' => $sorguTarihi,
                'token' => $token,
                'btn' => 'Sorgula',
            ];

            $resultHtml = $this->httpClient->post(self::SUBMIT_URL, $postData, [
                'Origin' => 'https://www.turkiye.gov.tr',
                'Referer' => self::BASE_URL,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ], $cookieJar);

            if ($resultHtml === null || $resultHtml === '') {
                $this->lastError = "e-Devlet sorgu POST isteği başarısız: (İl: {$resmiIlAdi}, Plaka: {$plakaKodu})";
                return null;
            }

            // 3. Adım: Sonuç HTML tablosunu ayrıştır
            return $this->parseResultHtml($resultHtml, $resmiIlAdi);
        } catch (\Throwable $e) {
            $this->lastError = 'e-Devlet sorgusu sırasında istisna: ' . $e->getMessage();
            return null;
        } finally {
            if (is_file($cookieJar)) {
                @unlink($cookieJar);
            }
        }
    }

    /**
     * e-Devlet sonuç tablosundaki <tr> satırlarını ayrıştırır
     */
    private function parseResultHtml(string $html, string $city): array
    {
        $dom = new DOMDocument();
        // UTF-8 uyumluluğu için libxml ve XML encoding öneki
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $rows = $xpath->query('//table//tbody/tr');
        if ($rows === false || $rows->length === 0) {
            return [];
        }

        $results = [];
        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            if ($cells === null || $cells->length < 6) {
                continue;
            }

            $cellText = static function (int $index) use ($cells): string {
                $item = $cells->item($index);
                if ($item === null) {
                    return '';
                }
                $text = preg_replace('/\s+/u', ' ', $item->textContent ?? '');
                return trim((string) $text);
            };

            $name = $cellText(1);
            if ($name === '') {
                continue;
            }

            $dist = $cellText(0);
            $address = $cellText(2);
            $phone = $cellText(3);
            $nobetBaslangic = $cellText(4);
            $nobetBitis = $cellText(5);

            // Harita linki veya koordinat varsa yakala
            $lat = null;
            $lon = null;
            $links = $xpath->query('.//a', $row);
            if ($links !== false) {
                foreach ($links as $link) {
                    $href = $link->getAttribute('href');
                    if (preg_match('/q=([0-9.-]+),([0-9.-]+)/', $href, $coords) || preg_match('/ll=([0-9.-]+),([0-9.-]+)/', $href, $coords)) {
                        $lat = (float) $coords[1];
                        $lon = (float) $coords[2];
                        break;
                    }
                }
            }

            $results[] = [
                'name' => $name,
                'dist' => $dist,
                'city' => $city,
                'address' => $address,
                'phone' => $phone,
                'nobet_baslangic' => $nobetBaslangic,
                'nobet_bitis' => $nobetBitis,
                'lat' => $lat,
                'lon' => $lon,
            ];
        }

        return $results;
    }

    private function formatTarih(string $tarih): string
    {
        $timestamp = strtotime($tarih);
        if ($timestamp !== false) {
            return date('d/m/Y', $timestamp);
        }
        return date('d/m/Y');
    }
}
