<?php

declare(strict_types=1);

namespace EczaneApi;

use EczaneApi\Cache\FileCache;
use EczaneApi\Contracts\CacheInterface;
use EczaneApi\Contracts\EczaneSourceInterface;
use EczaneApi\Data\Sehirler;
use EczaneApi\DTO\EczaneDto;
use EczaneApi\DTO\SehirDto;
use EczaneApi\Sources\BelediyeApiSource;
use EczaneApi\Sources\ChamberScraperSource;
use EczaneApi\Sources\EDevletSource;
use EczaneApi\Sources\FallbackApiSource;
use EczaneApi\Sources\MockSource;

class EczaneService
{
    private EczaneSourceInterface $source;
    private CacheInterface $cache;
    private ?EczaneSourceInterface $belediyeSource;
    private ?EczaneSourceInterface $chamberSource;
    private ?EczaneSourceInterface $fallbackSource;
    private ?EczaneSourceInterface $mockSource;
    private int $cacheTtl;
    private string $defaultCity;

    public function __construct(
        ?EczaneSourceInterface $source = null,
        ?CacheInterface $cache = null,
        int $cacheTtl = 21600,
        string $defaultCity = 'İstanbul',
        bool $enableFallback = true
    ) {
        $this->source = $source ?? new EDevletSource();
        $this->cache = $cache ?? new FileCache(__DIR__ . '/../storage/cache', $cacheTtl);
        $this->belediyeSource = $enableFallback ? new BelediyeApiSource() : null;
        $this->chamberSource = $enableFallback ? new ChamberScraperSource() : null;
        $this->fallbackSource = $enableFallback ? new FallbackApiSource() : null;
        $this->mockSource = $enableFallback ? new MockSource() : null;
        $this->cacheTtl = $cacheTtl;
        $this->defaultCity = $defaultCity;
    }

    /**
     * Nöbetçi eczaneleri DTO nesneleri olarak listeler
     *
     * @return array<int, EczaneDto>
     */
    public function getNobetciEczaneler(
        ?string $il = null,
        ?string $ilce = null,
        ?string $tarih = null,
        bool $forceRefresh = false
    ): array {
        $rawList = $this->getNobetciRaw($il, $ilce, $tarih, $forceRefresh);
        $cityName = $il ?: $this->defaultCity;

        $dtos = [];
        foreach ($rawList as $item) {
            $dtos[] = EczaneDto::fromArray($item, $cityName);
        }
        return $dtos;
    }

    /**
     * Nöbetçi eczaneleri ham dizi olarak listeler
     */
    public function getNobetciRaw(
        ?string $il = null,
        ?string $ilce = null,
        ?string $tarih = null,
        bool $forceRefresh = false
    ): array {
        $ilAdi = trim((string) ($il ?: $this->defaultCity));
        $sehirBilgi = Sehirler::find($ilAdi);
        if ($sehirBilgi === null) {
            return [];
        }

        $resmiAd = $sehirBilgi['name'];
        $slug = $sehirBilgi['slug'];
        $tarihKey = $tarih ? date('Ymd', (int) strtotime($tarih)) : date('Ymd');
        $cacheKey = "ne-il-{$slug}-{$tarihKey}";

        // 1. Adım: Önbellek Kontrolü (forceRefresh istenmemişse)
        if (!$forceRefresh) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached) && !empty($cached)) {
                return $this->filtreleIlce($cached, $ilce);
            }
        }

        // 2. Adım: Birincil Kaynaktan Çek (e-Devlet)
        $data = $this->source->fetch($resmiAd, $tarih);

        // 3. Adım: e-Devlet boş ise Belediye Açık Veri API'sinden çek (İzmir vb.)
        if (($data === null || empty($data)) && $this->belediyeSource !== null) {
            $data = $this->belediyeSource->fetch($resmiAd, $tarih);
        }

        // 4. Adım: Başarısız ise Doğrudan Eczacı Odası Sayfasından çek (Eskişehir, Kayseri vb.)
        if (($data === null || empty($data)) && $this->chamberSource !== null) {
            $data = $this->chamberSource->fetch($resmiAd, $tarih);
        }

        // 5. Adım: Başarısız ise ve önbellekte eski veri varsa onu koru
        if ($data === null || empty($data)) {
            $eskiCache = $this->cache->get($cacheKey);
            if (is_array($eskiCache) && !empty($eskiCache)) {
                return $this->filtreleIlce($eskiCache, $ilce);
            }

            // 6. Adım: Yedek Açık API Motorundan çek
            if ($this->fallbackSource !== null) {
                $data = $this->fallbackSource->fetch($resmiAd, $tarih);
            }

            // 7. Adım: Hâlâ boş ise Mock kaynağa başvur
            if (($data === null || empty($data)) && $this->mockSource !== null) {
                $data = $this->mockSource->fetch($resmiAd, $tarih);
            }
        }

        // 6. Adım: Önbelleğe kaydet
        if (is_array($data) && !empty($data)) {
            $this->cache->set($cacheKey, $data, $this->cacheTtl);
            return $this->filtreleIlce($data, $ilce);
        }

        return [];
    }

    /**
     * Bir il için mevcut nöbetçi eczanelerin ilçelerini listeler
     *
     * @return array<int, string>
     */
    public function getIlceler(string $il): array
    {
        $raw = $this->getNobetciRaw($il);
        $ilceler = [];

        foreach ($raw as $item) {
            $dist = trim((string) ($item['dist'] ?? $item['ilce'] ?? ''));
            if ($dist !== '' && !in_array($dist, $ilceler, true)) {
                $ilceler[] = $dist;
            }
        }

        sort($ilceler, SORT_STRING | SORT_FLAG_CASE);
        return array_values($ilceler);
    }

    /**
     * Türkiye'nin 81 ilini listeler
     *
     * @return array<int, SehirDto>
     */
    public function getIller(): array
    {
        return Sehirler::allDtos();
    }

    /**
     * Belirli bir il veya tüm iller için önbelleği yeniler
     */
    public function refresh(?string $il = null): array
    {
        $ilAdi = $il ?: $this->defaultCity;
        $sehir = Sehirler::find($ilAdi);
        if ($sehir === null) {
            return ['success' => false, 'message' => "Şehir bulunamadı: '{$ilAdi}'"];
        }

        $veri = $this->getNobetciRaw($sehir['name'], null, null, true);
        return [
            'success' => !empty($veri),
            'city' => $sehir['name'],
            'count' => count($veri),
            'message' => count($veri) > 0
                ? "{$sehir['name']} ili için " . count($veri) . " nöbetçi eczane güncellendi."
                : "{$sehir['name']} için nöbetçi eczane verisi çekilemedi.",
        ];
    }

    /**
     * Önbellek durum ve istatistiklerini döndürür
     */
    public function getCacheStats(): array
    {
        if ($this->cache instanceof FileCache) {
            return $this->cache->getStats();
        }

        return [
            'driver' => get_class($this->cache),
            'ttl' => $this->cacheTtl,
        ];
    }

    /**
     * Önbelleği temizler
     */
    public function clearCache(?string $il = null): bool
    {
        if ($il === null) {
            return $this->cache->clear();
        }

        $sehir = Sehirler::find($il);
        if ($sehir === null) {
            return false;
        }

        $tarihKey = date('Ymd');
        $cacheKey = "ne-il-{$sehir['slug']}-{$tarihKey}";
        return $this->cache->delete($cacheKey);
    }

    /**
     * Tüm illeri (veya seçilen illeri) sırayla çeker ve çekilemeyen illeri detaylı raporlar
     *
     * @param array<int, string> $iller Taranacak il adları (boşsa 81 ilin tamamı)
     * @param int $sleepBetweenSeconds e-Devlet rate-limit'e takılmamak için istekler arası bekleme
     * @return array{
     *   toplam: int,
     *   basarili_sayisi: int,
     *   basarisiz_sayisi: int,
     *   basarili_iller: array<string, int>,
     *   cekilemeyen_iller: array<int, array{il: string, plaka: int, hata: string}>,
     *   tamamlandi: bool
     * }
     */
    public function bulkFetch(array $iller = [], int $sleepBetweenSeconds = 1): array
    {
        $hedefIller = !empty($iller) ? $iller : array_column(Sehirler::all(), 'name');
        $toplam = count($hedefIller);
        $basarili = [];
        $cekilemeyenler = [];

        foreach ($hedefIller as $index => $ilAdi) {
            $sehir = Sehirler::find($ilAdi);
            if ($sehir === null) {
                $cekilemeyenler[] = [
                    'il' => $ilAdi,
                    'plaka' => 0,
                    'hata' => 'Geçersiz şehir adı',
                ];
                continue;
            }

            // 1. Önce e-Devlet'ten dene
            $data = $this->source->fetch($sehir['name']);

            // 2. e-Devlet boş ise Belediye Açık Veri API'sinden çek (İzmir vb.)
            if (($data === null || empty($data)) && $this->belediyeSource !== null) {
                $data = $this->belediyeSource->fetch($sehir['name']);
            }

            // 3. Başarısız ise Doğrudan Eczacı Odası Sayfasından çek (Eskişehir, Kayseri vb.)
            if (($data === null || empty($data)) && $this->chamberSource !== null) {
                $data = $this->chamberSource->fetch($sehir['name']);
            }

            // 4. Hâlâ boş ise Yedek Açık API Motorundan çek
            if (($data === null || empty($data)) && $this->fallbackSource !== null) {
                $data = $this->fallbackSource->fetch($sehir['name']);
            }

            if (is_array($data) && !empty($data)) {
                $tarihKey = date('Ymd');
                $cacheKey = "ne-il-{$sehir['slug']}-{$tarihKey}";
                $this->cache->set($cacheKey, $data, $this->cacheTtl);
                $basarili[$sehir['name']] = count($data);
            } else {
                $hata = ($this->source instanceof EDevletSource)
                    ? ($this->source->getLastError() ?? 'Veri bulunamadı veya e-Devlet yanıt vermedi')
                    : 'Veri kaynağı boş döndü';

                $cekilemeyenler[] = [
                    'il' => $sehir['name'],
                    'plaka' => $sehir['id'],
                    'hata' => $hata,
                ];
            }

            // Son il değilse ve bekleme süresi verilmişse bekle
            if ($sleepBetweenSeconds > 0 && $index < ($toplam - 1)) {
                sleep($sleepBetweenSeconds);
            }
        }

        return [
            'toplam' => $toplam,
            'basarili_sayisi' => count($basarili),
            'basarisiz_sayisi' => count($cekilemeyenler),
            'basarili_iller' => $basarili,
            'cekilemeyen_iller' => $cekilemeyenler,
            'tamamlandi' => true,
        ];
    }

    /**
     * İlçe adına göre sonuçları filtreler (büyük-küçük harf ve Türkçe karakter toleranslı)
     */
    private function filtreleIlce(array $eczaneler, ?string $ilce): array
    {
        if ($ilce === null || trim($ilce) === '') {
            return array_values($eczaneler);
        }

        $target = Sehirler::normalize($ilce);
        $filtered = array_filter($eczaneler, static function (array $e) use ($target) {
            $dist = trim((string) ($e['dist'] ?? $e['ilce'] ?? ''));
            return Sehirler::normalize($dist) === $target;
        });

        return array_values($filtered);
    }
}
