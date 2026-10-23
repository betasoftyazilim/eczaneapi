<?php

declare(strict_types=1);

use EczaneApi\Controllers\EczaneController;
use EczaneApi\Laravel\Http\Controllers\EczaneLaravelController;

if (class_exists('\Illuminate\Support\Facades\Route')) {
    /*
    |--------------------------------------------------------------------------
    | Laravel Rotaları
    |--------------------------------------------------------------------------
    */
    \Illuminate\Support\Facades\Route::prefix('api/v1')->middleware(['api'])->group(function () {
        \Illuminate\Support\Facades\Route::get('/nobetci', [EczaneLaravelController::class, 'index'])->name('eczane.nobetci');
        \Illuminate\Support\Facades\Route::get('/iller', [EczaneLaravelController::class, 'iller'])->name('eczane.iller');
        \Illuminate\Support\Facades\Route::get('/ilceler/{il?}', [EczaneLaravelController::class, 'ilceler'])->name('eczane.ilceler');
        \Illuminate\Support\Facades\Route::get('/status', [EczaneLaravelController::class, 'status'])->name('eczane.status');
        \Illuminate\Support\Facades\Route::get('/bulk-check', [EczaneLaravelController::class, 'bulkCheck'])->name('eczane.bulk_check');
        \Illuminate\Support\Facades\Route::get('/geo/lookup', [EczaneController::class, 'geoLookup'])->name('eczane.geo_lookup');
        \Illuminate\Support\Facades\Route::get('/geo/hierarchy', [EczaneController::class, 'maxMindHierarchy'])->name('eczane.geo_hierarchy');
    });
} else {
    /*
    |--------------------------------------------------------------------------
    | Bağımsız (Standalone / Generic MVC) Rota Kayıtları
    |--------------------------------------------------------------------------
    */
    return function (\EczaneApi\Http\Router $router) {
        $controller = new EczaneController();

        $router->get('/api/v1/nobetci', [$controller, 'index']);
        $router->get('/api/v1/iller', [$controller, 'iller']);
        $router->get('/api/v1/ilceler', [$controller, 'ilceler']);
        $router->get('/api/v1/ilceler/{il}', [$controller, 'ilceler']);
        $router->get('/api/v1/status', [$controller, 'status']);
        $router->get('/api/v1/geo/lookup', [$controller, 'geoLookup']);
        $router->get('/api/v1/geo/hierarchy', [$controller, 'maxMindHierarchy']);
        $router->post('/api/v1/refresh', [$controller, 'refresh']);
        $router->post('/api/v1/cache/clear', [$controller, 'clearCache']);
    };
}
