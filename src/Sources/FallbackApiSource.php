<?php

declare(strict_types=1);

namespace EczaneApi\Sources;

use EczaneApi\Contracts\EczaneSourceInterface;
use EczaneApi\Contracts\HttpClientInterface;
use EczaneApi\Data\Sehirler;
use EczaneApi\Http\CurlHttpClient;

class FallbackApiSource implements EczaneSourceInterface
{
    private const BASE_URL = 'https://api.teknikzeka.net/eczane/api.php?islem=nobetci&il=';

    private HttpClientInterface $httpClient;
    private ?string $lastError = null;

    public function __construct(?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    public function getName(): string
    {
        return 'fallback_api';
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function fetch(string $ilAdi, ?string $tarih = null): ?array
    {
        $sehir = Sehirler::find($ilAdi);
        if ($sehir === null) {
            $this->lastError = "Geçersiz il adı: '{$ilAdi}'";
            return null;
        }

        // API ASCII karakterler bekler (örn: Eskisehir, Aydin, Elazig)
        $asciiName = Sehirler::toAscii($sehir['name']);
        $url = self::BASE_URL . urlencode($asciiName);

        $jsonStr = $this->httpClient->get($url, [
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        ]);

        if ($jsonStr === null || $jsonStr === '') {
            $this->lastError = "Yedek API yanıt vermedi: {$asciiName}";
            return null;
        }

        $data = json_decode($jsonStr, true);
        if (!is_array($data) || empty($data['sonuc']) || !is_array($data['sonuc'])) {
            $this->lastError = "Yedek API geçerli veri döndürmedi: {$asciiName}";
            return null;
        }

        $results = [];
        $resmiSehir = $sehir['name'];

        foreach ($data['sonuc'] as $item) {
            $name = trim((string) ($item['name'] ?? $item['ad'] ?? ''));
            if ($name === '') {
                continue;
            }

            // "Eczanesi" ekini temizle veya düzenle
            if (!str_ends_with(mb_strtolower($name, 'UTF-8'), 'eczanesi')) {
                $name .= ' Eczanesi';
            }

            $dist = trim((string) ($item['district'] ?? $item['ilce'] ?? 'Merkez'));
            $dist = mb_convert_case($dist, MB_CASE_TITLE, 'UTF-8');

            $address = trim((string) ($item['address'] ?? $item['adres'] ?? ''));
            $phone = trim((string) ($item['phone'] ?? $item['telefon'] ?? ''));
            if ($phone !== '' && !str_starts_with($phone, '0') && strlen($phone) === 10) {
                $phone = '0' . $phone;
            }

            $lat = isset($item['latitude']) && is_numeric($item['latitude']) ? (float) $item['latitude'] : null;
            $lon = isset($item['longitude']) && is_numeric($item['longitude']) ? (float) $item['longitude'] : null;

            $results[] = [
                'name' => $name,
                'dist' => $dist,
                'city' => $resmiSehir,
                'address' => $address,
                'phone' => $phone,
                'nobet_baslangic' => date('d/m/Y') . ' 08:30',
                'nobet_bitis' => date('d/m/Y', strtotime('+1 day')) . ' 08:30',
                'lat' => $lat,
                'lon' => $lon,
            ];
        }

        return $results;
    }
}
