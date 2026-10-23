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
 
         $il = $request->input('il');
         $ilce = $request->input('ilce');
         $ipParam = $request->input('ip');
         $latParam = $request->input('lat');
         $lonParam = $request->input('lon');
 
         // IP'den otomatik tespit istenmişse veya il belirtilmemiş ve ip verilmişse
         if ($ipParam !== null || ($il === null && $latParam === null)) {
             $targetIp = ($ipParam === 'auto' || $ipParam === null || $ipParam === '') ? $request->getIp() : (string) $ipParam;
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
         $tarih = $request->input('tarih');
         $force = (bool) $request->input('refresh', false);
 
         $eczaneler = $this->service->getNobetciEczaneler($il, $ilce, $tarih, $force);
 
         // Koordinat verilmişse mesafeye göre sırala
         $userLat = ($latParam !== null && is_numeric($latParam)) ? (float) $latParam : null;
         $userLon = ($lonParam !== null && is_numeric($lonParam)) ? (float) $lonParam : null;
 
         $data = array_map(static fn ($e) => $e->toArray(), $eczaneler);
         if ($userLat !== null && $userLon !== null) {
             $data = \EczaneApi\Geo\MaxMindGeoService::sortByDistance($data, $userLat, $userLon);
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
                'version' => '1.0.0',
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
}
