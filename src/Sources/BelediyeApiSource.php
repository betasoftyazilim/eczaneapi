<?php

declare(strict_types=1);

namespace EczaneApi\Sources;

use EczaneApi\Contracts\EczaneSourceInterface;
use EczaneApi\Contracts\HttpClientInterface;
use EczaneApi\Data\Sehirler;
use EczaneApi\Http\CurlHttpClient;

class BelediyeApiSource implements EczaneSourceInterface
{
    private HttpClientInterface $httpClient;
    private ?string $lastError = null;

    public function __construct(?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    public function getName(): string
    {
        return 'belediye_api';
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

        $resmiAd = $sehir['name'];

        // 1. İZMİR BÜYÜKŞEHİR BELEDİYESİ AÇIK VERİ API ENTEGRASYONU
        if ($resmiAd === 'İzmir') {
            return $this->fetchIzmir();
        }

        return null;
    }

    private function fetchIzmir(): ?array
    {
        $url = 'https://openapi.izmir.bel.tr/api/ibb/nobetcieczaneler';
        $jsonStr = $this->httpClient->get($url, [
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        if ($jsonStr === null || $jsonStr === '') {
            $this->lastError = 'İzmir Büyükşehir Belediyesi API yanıt vermedi.';
            return null;
        }

        $data = json_decode($jsonStr, true);
        if (!is_array($data) || empty($data)) {
            $this->lastError = 'İzmir BB API geçerli veri döndürmedi.';
            return null;
        }

        $results = [];
        foreach ($data as $item) {
            $name = trim((string) ($item['Adi'] ?? ''));
            if ($name === '') {
                continue;
            }

            $dist = trim((string) ($item['Bolge'] ?? 'Merkez'));
            $address = trim((string) ($item['Adres'] ?? ''));
            $phone = trim((string) ($item['Telefon'] ?? ''));
            $lat = isset($item['LokasyonX']) && is_numeric($item['LokasyonX']) ? (float) $item['LokasyonX'] : null;
            $lon = isset($item['LokasyonY']) && is_numeric($item['LokasyonY']) ? (float) $item['LokasyonY'] : null;
            $tarihVal = (string) ($item['Tarih'] ?? date('c'));

            $results[] = [
                'name' => $name,
                'dist' => $dist,
                'city' => 'İzmir',
                'address' => $address,
                'phone' => $phone,
                'nobet_baslangic' => date('d/m/Y', strtotime($tarihVal)) . ' 08:00',
                'nobet_bitis' => date('d/m/Y', strtotime($tarihVal . ' +1 day')) . ' 08:00',
                'lat' => $lat,
                'lon' => $lon,
            ];
        }

        return $results;
    }
}
