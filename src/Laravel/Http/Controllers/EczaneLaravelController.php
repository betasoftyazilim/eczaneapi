<?php

declare(strict_types=1);

namespace EczaneApi\Laravel\Http\Controllers;

use EczaneApi\DTO\ApiResponse;
use EczaneApi\EczaneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EczaneLaravelController extends Controller
{
    protected EczaneService $service;

    public function __construct(EczaneService $service)
    {
        $this->service = $service;
    }

    /**
     * Nöbetçi eczaneleri listeler
     */
    public function index(Request $request): JsonResponse
    {
        $il = (string) $request->input('il', config('eczane.default_il', 'İstanbul'));
        $ilce = $request->input('ilce') ? (string) $request->input('ilce') : null;
        $tarih = $request->input('tarih') ? (string) $request->input('tarih') : null;
        $fresh = $request->boolean('refresh', false);

        $eczaneler = $this->service->getNobetciEczaneler($il, $ilce, $tarih, $fresh);

        return response()->json(ApiResponse::success(
            data: array_map(static fn ($e) => $e->toArray(), $eczaneler),
            message: count($eczaneler) . ' nöbetçi eczane listelendi.',
            meta: [
                'il' => $il,
                'ilce' => $ilce,
                'tarih' => $tarih ?? date('Y-m-d'),
            ]
        ));
    }

    /**
     * 81 ili listeler
     */
    public function iller(): JsonResponse
    {
        $iller = $this->service->getIller();
        return response()->json(ApiResponse::success(
            data: array_map(static fn ($s) => $s->toArray(), $iller),
            message: '81 il listelendi.'
        ));
    }

    /**
     * Seçili ilin ilçelerini listeler
     */
    public function ilceler(Request $request, ?string $il = null): JsonResponse
    {
        $targetIl = (string) ($il ?: $request->input('il', config('eczane.default_il', 'İstanbul')));
        $ilceler = $this->service->getIlceler($targetIl);

        return response()->json(ApiResponse::success(
            data: $ilceler,
            message: "'{$targetIl}' ili için " . count($ilceler) . ' ilçe listelendi.',
            meta: ['il' => $targetIl]
        ));
    }

    /**
     * Toplu tarama yapar ve çekilemeyen illeri raporlar
     */
    public function bulkCheck(Request $request): JsonResponse
    {
        $iller = (array) $request->input('iller', []);
        $sleep = (int) $request->input('sleep', 1);

        $rapor = $this->service->bulkFetch($iller, $sleep);
        return response()->json(ApiResponse::success(
            data: $rapor,
            message: 'Toplu il taraması tamamlandı.'
        ));
    }

    /**
     * Servis ve önbellek durumu
     */
    public function status(): JsonResponse
    {
        return response()->json(ApiResponse::success(
            data: [
                'service' => 'Eczane API (Laravel)',
                'cache' => $this->service->getCacheStats(),
            ],
            message: 'Eczane API servisi aktif.'
        ));
    }
}
