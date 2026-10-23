<?php

declare(strict_types=1);

namespace EczaneApi\Laravel;

use EczaneApi\Cache\FileCache;
use EczaneApi\Cache\LaravelCacheAdapter;
use EczaneApi\Client;
use EczaneApi\Contracts\CacheInterface;
use EczaneApi\Contracts\EczaneSourceInterface;
use EczaneApi\EczaneService;
use EczaneApi\Laravel\Console\EczaneFetchCommand;
use EczaneApi\Sources\EDevletSource;
use EczaneApi\Sources\MockSource;
use Illuminate\Support\ServiceProvider;

class EczaneServiceProvider extends ServiceProvider
{
    /**
     * Servisleri Container'a kaydeder
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/eczane.php', 'eczane');

        $this->app->singleton(CacheInterface::class, function ($app) {
            $driver = config('eczane.cache.driver', 'laravel');
            $ttl = (int) config('eczane.cache.ttl', 21600);

            if ($driver === 'file') {
                $path = config('eczane.cache.path', storage_path('framework/cache/eczane'));
                return new FileCache($path, $ttl);
            }

            return new LaravelCacheAdapter(
                store: config('eczane.cache.store', null),
                prefix: 'eczane_api_'
            );
        });

        $this->app->singleton(EczaneSourceInterface::class, function ($app) {
            $sourceType = config('eczane.source', 'edevlet');
            return $sourceType === 'mock' ? new MockSource() : new EDevletSource();
        });

        $this->app->singleton(EczaneService::class, function ($app) {
            return new EczaneService(
                source: $app->make(EczaneSourceInterface::class),
                cache: $app->make(CacheInterface::class),
                cacheTtl: (int) config('eczane.cache.ttl', 21600),
                defaultCity: (string) config('eczane.default_il', 'İstanbul'),
                enableFallback: true
            );
        });

        $this->app->singleton('eczane', function ($app) {
            return new Client($app->make(EczaneService::class));
        });

        $this->app->alias('eczane', Client::class);
    }

    /**
     * Paket başlangıç yapılandırması
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/eczane.php' => config_path('eczane.php'),
            ], 'eczane-config');

            $this->commands([
                EczaneFetchCommand::class,
            ]);
        }

        // Rotaları yükle (eğer config'de rota yükleme kapatılmadıysa)
        if (config('eczane.api.load_routes', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        }
    }
}
