<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Varsayılan İl ve İlçe
    |--------------------------------------------------------------------------
    | Sorgularda il belirtilmediğinde veya varsayılan arama gerektiğinde
    | kullanılacak il ve ilçe tanımı.
    */
    'default_il' => 'İstanbul',
    'default_ilce' => null,

    /*
    |--------------------------------------------------------------------------
    | Önbellek Yapılandırması (Cache)
    |--------------------------------------------------------------------------
    | 'driver': 'file', 'array', 'laravel', 'pdo'
    | 'ttl': Saniye cinsinden önbellek geçerlilik süresi (varsayılan: 21600 = 6 saat)
    | 'path': Dosya önbelleği için depolama dizini
    */
    'cache' => [
        'enabled' => true,
        'driver' => 'file',
        'ttl' => 21600, // 6 saat
        'path' => __DIR__ . '/../storage/cache',
    ],

    /*
    |--------------------------------------------------------------------------
    | Veri Kaynağı Sağlayıcısı (Source Provider)
    |--------------------------------------------------------------------------
    | 'edevlet': Resmi e-Devlet TİTCK web servis kazıma motoru
    | 'mock': Çevrimdışı ve test senaryoları için sahte veri sağlayıcı
    */
    'source' => 'edevlet',

    /*
    |--------------------------------------------------------------------------
    | Ağ ve İstek Ayarları (HTTP Client)
    |--------------------------------------------------------------------------
    | İstek zaman aşımı, yeniden deneme sayısı ve User-Agent ayarları.
    */
    'http' => [
        'timeout' => 20,
        'connect_timeout' => 10,
        'verify_ssl' => false,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    ],

    /*
    |--------------------------------------------------------------------------
    | GeoIP & MaxMind / WP-Statistics Yapılandırması
    |--------------------------------------------------------------------------
    | WP-Statistics açık kaynak GeoLite2-City veritabanı entegrasyonu.
    | API anahtarı gerektirmeden otomatik güncellenir ve IP'den il/ilçe çözer.
    */
    'geoip' => [
        'enabled' => true,
        'db_path' => __DIR__ . '/../storage/geoip/GeoLite2-City.mmdb',
        'source_url' => 'https://raw.githubusercontent.com/wp-statistics/GeoLite2-City/master/GeoLite2-City.mmdb',
        'cdn_url' => 'https://cdn.jsdelivr.net/gh/wp-statistics/GeoLite2-City@master/GeoLite2-City.mmdb',
        'auto_download' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Rota ve Güvenlik Ayarları
    |--------------------------------------------------------------------------
    | API rota öneki, CORS başlıkları ve rate limiting.
    */
    'api' => [
        'prefix' => 'api/v1',
        'cors_enabled' => true,
        'cors_allowed_origins' => ['*'],
    ],
];
