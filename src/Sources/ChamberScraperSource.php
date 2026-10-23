<?php

declare(strict_types=1);

namespace EczaneApi\Sources;

use DOMDocument;
use DOMXPath;
use EczaneApi\Contracts\EczaneSourceInterface;
use EczaneApi\Contracts\HttpClientInterface;
use EczaneApi\Data\Sehirler;
use EczaneApi\Http\CurlHttpClient;

class ChamberScraperSource implements EczaneSourceInterface
{
    private HttpClientInterface $httpClient;
    private ?string $lastError = null;

    private array $chamberUrls = [
        'Eskişehir' => 'https://www.eskisehireo.org.tr/eskisehir-nobetci-eczaneler',
        'Kayseri' => 'https://www.kayserieo.org.tr/nobetci-eczaneler',
        'Aydın' => 'https://www.aydineczaciodasi.org.tr/nobetci-eczaneler',
        'Elazığ' => 'https://www.elazigeczaciodasi.org.tr/nobetci-eczaneler',
        'Malatya' => 'https://www.malatyaeczaciodasi.org.tr/nobetci-eczaneler',
    ];

    public function __construct(?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    public function getName(): string
    {
        return 'chamber_direct';
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
        if (!isset($this->chamberUrls[$resmiAd])) {
            // Doğrudan HTML sayfası tanımlı olmayan iller için Fallback API'ye yönlendir
            $fallback = new FallbackApiSource($this->httpClient);
            return $fallback->fetch($resmiAd, $tarih);
        }

        $url = $this->chamberUrls[$resmiAd];
        $html = $this->httpClient->get($url, [
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'tr-TR,tr;q=0.9',
        ]);

        if ($html === null || $html === '') {
            $this->lastError = "{$resmiAd} Eczacı Odası web sitesine ulaşılamadı.";
            // Ağ hatasında yedek açık API motorundan çek
            $fallback = new FallbackApiSource($this->httpClient);
            return $fallback->fetch($resmiAd, $tarih);
        }

        $results = $this->parseHtml($html, $resmiAd);
        if (empty($results)) {
            // Sayfa yapısı değişmişse yedek açık API motoruna başvur
            $fallback = new FallbackApiSource($this->httpClient);
            return $fallback->fetch($resmiAd, $tarih);
        }

        return $results;
    }

    private function parseHtml(string $html, string $city): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        $xpath = new DOMXPath($dom);

        $results = [];

        // Genel oda CMS kartlarını ve bloklarını ara
        $cards = $xpath->query("//div[contains(@class, 'col-')] | //div[contains(@class, 'card')] | //div[contains(@class, 'nobet')]");
        if ($cards !== false && $cards->length > 0) {
            foreach ($cards as $card) {
                $text = trim((string) preg_replace('/\s+/u', ' ', $card->textContent ?? ''));
                if (preg_match('/([A-ZÇĞİÖŞÜ0-9\s]+ECZANES[İI])/u', $text, $nameMatches)) {
                    $name = trim($nameMatches[1]);

                    // Telefon yakala
                    $phone = '';
                    if (preg_match('/(0?\s*\(?[2-5][0-9]{2}\)?\s*[0-9]{3}\s*[0-9]{2}\s*[0-9]{2})/u', $text, $pMatches)) {
                        $phone = trim($pMatches[1]);
                    }

                    // İlçe yakala
                    $dist = 'Merkez';
                    $sehirBilgi = Sehirler::find($city);
                    if ($sehirBilgi) {
                        foreach ($sehirBilgi['districts'] as $d) {
                            if (stripos($text, $d) !== false) {
                                $dist = $d;
                                break;
                            }
                        }
                    }

                    $results[] = [
                        'name' => $name,
                        'dist' => $dist,
                        'city' => $city,
                        'address' => $text,
                        'phone' => $phone,
                        'nobet_baslangic' => date('d/m/Y') . ' 08:30',
                        'nobet_bitis' => date('d/m/Y', strtotime('+1 day')) . ' 08:30',
                        'lat' => null,
                        'lon' => null,
                    ];
                }
            }
        }

        return $results;
    }
}
