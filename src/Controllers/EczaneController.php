<?php

declare(strict_types=1);

namespace EczaneApi\Controllers;

use EczaneApi\DTO\ApiResponse;
use EczaneApi\EczaneService;
use EczaneApi\Http\Request;
use EczaneApi\Http\Response;

class EczaneController
{
    private EczaneService $service;

    public function __construct(?EczaneService $service = null)
    {
        $this->service = $service ?? new EczaneService();
    }

    /**
     * Nöbetçi eczaneleri listeler (İl, İlçe, IP tespiti veya Koordinat mesafesi ile)
     * GET /api/v1/nobetci?il=istanbul&ilce=kadikoy&tarih=2026-10-23
     * GET /api/v1/nobetci?ip=auto
     * GET /api/v1/nobetci?lat=41.0082&lon=28.9784
     */
    public function index(Request $request): Response
    {
        $geoService = new \EczaneApi\Geo\MaxMindGeoService();
        $geoMeta = null;

        $il = $this->sanitizeText($request->input('il'), 60);
        $ilce = $this->sanitizeText($request->input('ilce'), 60);
        $ipParam = $this->sanitizeText($request->input('ip'), 45);
        $latParam = $request->input('lat');
        $lonParam = $request->input('lon');

        // IP'den otomatik tespit istenmişse veya il belirtilmemiş ve ip verilmişse
        if ($ipParam !== null || ($il === null && $latParam === null)) {
            $targetIp = ($ipParam === 'auto' || $ipParam === null || $ipParam === '')
                ? $request->getIp()
                : (filter_var($ipParam, FILTER_VALIDATE_IP) ? $ipParam : $request->getIp());

            $geoDto = $geoService->lookup($targetIp);
            if ($geoDto !== null) {
                if ($il === null || $il === '') {
                    $il = $geoDto->getProvinceName();
                }
                if (($ilce === null || $ilce === '') && $geoDto->getDistrictName()) {
                    $ilce = $geoDto->getDistrictName();
                }
                $geoMeta = $geoDto->toArray();
            }
        }

        $il = (string) ($il ?: 'İstanbul');
        $tarih = $this->validateDate($request->input('tarih'));
        $force = (bool) $request->input('refresh', false);

        $eczaneler = $this->service->getNobetciEczaneler($il, $ilce, $tarih, $force);

        // Koordinat verilmişse mesafeye göre sırala
        $userLat = ($latParam !== null && is_numeric($latParam)) ? (float) $latParam : null;
        $userLon = ($lonParam !== null && is_numeric($lonParam)) ? (float) $lonParam : null;

        if ($userLat !== null && ($userLat < -90.0 || $userLat > 90.0)) {
            $userLat = null;
        }
        if ($userLon !== null && ($userLon < -180.0 || $userLon > 180.0)) {
            $userLon = null;
        }

        $data = array_map(static fn ($e) => $e->toArray(), $eczaneler);
        if ($userLat !== null && $userLon !== null) {
            $data = \EczaneApi\Geo\MaxMindGeoService::sortByDistance($data, $userLat, $userLon);
        }

        if ($request->input('format') === 'xml') {
            return Response::xml($this->toOdaXml($data, $il, $ilce, $tarih));
        }

        return Response::api(ApiResponse::success(
            data: $data,
            message: count($data) . ' nöbetçi eczane başarıyla listelendi.',
            meta: [
                'il' => $il,
                'ilce' => $ilce,
                'tarih' => $tarih ?? date('Y-m-d'),
                'maxmind_geo' => $geoMeta,
                'user_coordinates' => ($userLat && $userLon) ? ['latitude' => $userLat, 'longitude' => $userLon] : null,
            ]
        ));
    }
 
     /**
      * MaxMind GeoIP IP Konum Sorgulama Uç Noktası
      * GET /api/v1/geo/lookup?ip=...
      */
     public function geoLookup(Request $request): Response
     {
         $ip = (string) ($request->input('ip') ?: $request->getIp());
         $geoService = new \EczaneApi\Geo\MaxMindGeoService();
         $geoDto = $geoService->lookup($ip);
 
         return Response::api(ApiResponse::success(
             data: $geoDto ? $geoDto->toArray() : null,
             message: 'MaxMind GeoIP konum tespiti tamamlandı.'
         ));
     }
 
     /**
      * MaxMind 81 İl ve İlçe Hiyerarşik Veritabanı
      * GET /api/v1/geo/hierarchy
      */
     public function maxMindHierarchy(Request $request): Response
     {
         $hierarchy = \EczaneApi\Data\MaxMindTurkiye::getHierarchy();
         return Response::api(ApiResponse::success(
             data: $hierarchy,
             message: 'MaxMind uyumlu 81 il ve ilçe hiyerarşisi listelendi.'
         ));
     }

    /**
     * Tüm veri kaynakları kataloğu (e-Devlet, Fallback, MaxMind, Odalar, Belediyeler)
     * GET /api/v1/sources
     */
    public function sources(Request $request): Response
    {
        $sources = \EczaneApi\Data\SourcesCatalog::getSources();
        $belediyeler = \EczaneApi\Data\SourcesCatalog::getBelediyeler();

        return Response::api(ApiResponse::success(
            data: [
                'sources' => $sources,
                'belediyeler' => $belediyeler,
                'odalar_sayisi' => count(\EczaneApi\Data\SourcesCatalog::getOdalar()),
            ],
            message: 'Tüm veri kaynakları ve servisler listelendi.'
        ));
    }

    /**
     * 54 Bölge Eczacı Odası ve 81 il kaynak rehberi
     * GET /api/v1/sources/odalar
     */
    public function odalar(Request $request): Response
    {
        $il = $request->input('il');
        if ($il !== null && $il !== '') {
            $oda = \EczaneApi\Data\SourcesCatalog::findOdaByCity((string) $il);
            return Response::api(ApiResponse::success(
                data: $oda,
                message: "'{$il}' için bağlı Eczacı Odası kaynağı."
            ));
        }

        $odalar = \EczaneApi\Data\SourcesCatalog::getOdalar();
        return Response::api(ApiResponse::success(
            data: $odalar,
            message: count($odalar) . ' Bölge Eczacı Odası kaynağı listelendi.'
        ));
    }

    /**
     * 81 ili listeler
     * GET /api/v1/iller
     */
    public function iller(Request $request): Response
    {
        $iller = $this->service->getIller();
        return Response::api(ApiResponse::success(
            data: array_map(static fn ($s) => $s->toArray(), $iller),
            message: '81 il listelendi.'
        ));
    }

    /**
     * Bir ile ait nöbetçi eczane ilçelerini listeler
     * GET /api/v1/ilceler?il=istanbul veya /api/v1/ilceler/{il}
     */
    public function ilceler(Request $request, array $params = []): Response
    {
        $il = (string) ($params['il'] ?? $request->input('il', 'İstanbul'));
        $ilceler = $this->service->getIlceler($il);

        return Response::api(ApiResponse::success(
            data: $ilceler,
            message: "'{$il}' ili için " . count($ilceler) . ' ilçe listelendi.',
            meta: ['il' => $il]
        ));
    }

    /**
     * Sistem ve önbellek durum raporu
     * GET /api/v1/status
     */
    public function status(Request $request): Response
    {
        $cacheStats = $this->service->getCacheStats();

        return Response::api(ApiResponse::success(
            data: [
                'service' => 'Eczane API Servisi',
                'version' => '1.1.1',
                'php_version' => PHP_VERSION,
                'timezone' => date_default_timezone_get(),
                'server_time' => date('c'),
                'cache' => $cacheStats,
            ],
            message: 'Eczane API servisi çalışıyor.'
        ));
    }

    /**
     * Önbelleği yenileme
     * POST /api/v1/refresh?il=istanbul
     */
    public function refresh(Request $request): Response
    {
        $il = (string) $request->input('il', 'İstanbul');
        $result = $this->service->refresh($il);

        return Response::api(
            $result['success']
                ? ApiResponse::success($result, $result['message'])
                : ApiResponse::error($result['message'], 500, $result)
        );
    }

    /**
     * Önbelleği temizleme
     * POST /api/v1/cache/clear
     */
    public function clearCache(Request $request): Response
    {
        $il = $request->input('il');
        $success = $this->service->clearCache($il !== null ? (string) $il : null);

        return Response::api(
            $success
                ? ApiResponse::success(null, 'Önbellek başarıyla temizlendi.')
                : ApiResponse::error('Önbellek temizlenemedi.', 500)
        );
    }

    private function sanitizeText(mixed $value, int $maxLength = 100): ?string
    {
        if ($value === null || !is_scalar($value)) {
            return null;
        }

        $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string) $value));
        if ($cleaned === '') {
            return null;
        }

        return mb_substr($cleaned, 0, $maxLength, 'UTF-8');
    }

    private function validateDate(mixed $date): ?string
    {
        if ($date === null || !is_string($date)) {
            return null;
        }

        $trimmed = trim($date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed)) {
            [$year, $month, $day] = explode('-', $trimmed);
            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return $trimmed;
            }
        }

        return null;
    }

    private function toOdaXml(array $eczaneler, string $il, ?string $ilce, ?string $tarih): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        $nobetBilgi = $xml->addChild('NobetBilgi');
        $nobetBilgi->addChild('Tarih', htmlspecialchars($tarih ?? date('Y-m-d')));
        $nobetBilgi->addChild('NobetBaslangic', date('Y-m-d\T08:30:00'));
        $nobetBilgi->addChild('NobetBitis', date('Y-m-d\T08:30:00', strtotime('+1 day')));
        $nobetBilgi->addChild('Il', htmlspecialchars($il));
        if ($ilce) {
            $nobetBilgi->addChild('Ilce', htmlspecialchars($ilce));
        }

        foreach ($eczaneler as $ecz) {
            $url = $xml->addChild('url');
            $url->addChild('EczaneAd', htmlspecialchars($ecz['name'] ?? ''));
            $url->addChild('EczaneTelefon', htmlspecialchars($ecz['phone'] ?? ''));
            $url->addChild('EczaneAdres', htmlspecialchars($ecz['address'] ?? ''));
            $url->addChild('EczaneBolge', htmlspecialchars($ecz['dist'] ?? ''));
            $url->addChild('EczaneIlce', htmlspecialchars($ecz['dist'] ?? ''));
            $url->addChild('EczaneIl', htmlspecialchars($ecz['city'] ?? $il));
            $url->addChild('EczaneEnlem', (string) ($ecz['latitude'] ?? $ecz['lat'] ?? ''));
            $url->addChild('EczaneBoylam', (string) ($ecz['longitude'] ?? $ecz['lon'] ?? ''));
            $url->addChild('EczaneGLN', (string) ($ecz['gln'] ?? ''));
            $url->addChild('NobetBaslangic', htmlspecialchars($ecz['nobet_baslangic'] ?? ''));
            $url->addChild('NobetBitis', htmlspecialchars($ecz['nobet_bitis'] ?? ''));
        }

        return $xml->asXML() ?: '';
    }
}
