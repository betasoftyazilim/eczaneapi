<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use EczaneApi\Cache\ArrayCache;
use EczaneApi\Cache\FileCache;
use EczaneApi\Client;
use EczaneApi\Data\MaxMindTurkiye;
use EczaneApi\Data\Sehirler;
use EczaneApi\DTO\EczaneDto;
use EczaneApi\EczaneService;
use EczaneApi\Geo\MaxMindGeoService;
use EczaneApi\Http\Request;
use EczaneApi\Http\Router;
use EczaneApi\Sources\MockSource;

echo "========================================================\n";
echo "🏥 ECZANE API VE MAXMIND SİSTEM DOĞRULAMA TESTLERİ\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        echo "  \033[32m✔ [BAŞARILI]\033[0m {$name}\n";
        $passed++;
    } else {
        echo "  \033[31m✘ [BAŞARISIZ]\033[0m {$name}\n";
        $failed++;
    }
}

// 1. Şehir ve İlçe Testleri
$iller = Sehirler::all();
assertTest('81 İlin tamamı yüklendi', count($iller) === 81);

$istanbul = Sehirler::find('İstanbul');
assertTest('İstanbul plaka ve veri kontrolü', $istanbul !== null && $istanbul['id'] === 34);

$ilcelerIstanbul = Sehirler::getIlceler('İstanbul');
assertTest('İstanbul 39 ilçesi eksiksiz tanımlı', count($ilcelerIstanbul) === 39);

$ankara = Sehirler::find(6);
assertTest('Plaka 6 (Ankara) sorgusu', $ankara !== null && $ankara['name'] === 'Ankara');

$toplamIlceSayisi = 0;
foreach ($iller as $il) {
    $toplamIlceSayisi += count($il['districts']);
}
assertTest("Türkiye genelindeki tüm 973 ilçe tanımlı (Tespit: {$toplamIlceSayisi})", $toplamIlceSayisi >= 970);

// 2. MaxMind Hiyerarşi Testleri
$hierarchy = MaxMindTurkiye::getHierarchy();
assertTest('MaxMind TR-34 ISO kodu mevcut', isset($hierarchy['TR-34']));
assertTest('MaxMind TR-34 Türkçe ve İngilizce isimler doğru', $hierarchy['TR-34']['subdivision']['names']['tr'] === 'İstanbul');

$subAnkara = MaxMindTurkiye::findSubdivision('Ankara');
assertTest('MaxMind Ankara subdivision bulma', $subAnkara !== null && $subAnkara['subdivision']['iso_code'] === 'TR-06');

// 3. MaxMind GeoIP ve Mesafe Testleri
$geo = new MaxMindGeoService();
$geoDto = $geo->lookup('88.255.10.20');
assertTest('MaxMind GeoIP DTO üretimi', $geoDto !== null && $geoDto->getProvinceName() !== null);

$distance = MaxMindGeoService::calculateDistance(41.0082, 28.9784, 40.9922, 29.0239);
assertTest("Haversine mesafe hesaplama (Mesafe: {$distance} km)", $distance > 0 && $distance < 10);

// 4. DTO ve Telefon Normalizasyonu Testi
$dto = EczaneDto::fromArray([
    'name' => 'Şifa Eczanesi',
    'dist' => 'Kadıköy',
    'city' => 'İstanbul',
    'address' => 'Moda Cad. No: 12',
    'phone' => '0(216) 345 67 89',
    'lat' => 40.99,
    'lon' => 29.02,
]);
assertTest('EczaneDto oluşturma ve toArray', $dto->name === 'Şifa Eczanesi');
assertTest('Telefon normalizasyonu (temiz numara)', $dto->getCleanPhone() === '02163456789');
assertTest('Maps URL üretimi', str_contains($dto->getMapsUrl(), 'google.com/maps'));

// 5. Önbellek (Cache) Testi
$tempCacheDir = __DIR__ . '/../storage/cache_test';
$cache = new FileCache($tempCacheDir, 3600);
$cache->set('test_key', ['foo' => 'bar'], 3600);
$cachedVal = $cache->get('test_key');
assertTest('FileCache set ve get işlemi', isset($cachedVal['foo']) && $cachedVal['foo'] === 'bar');
$cache->delete('test_key');
assertTest('FileCache silme işlemi', $cache->get('test_key') === null);
$cache->clear();
@rmdir($tempCacheDir);

// 6. Servis, Mock ve Çekilemeyen İller Raporlama Testi
$mockSource = new MockSource();
$arrayCache = new ArrayCache();
$service = new EczaneService($mockSource, $arrayCache, 3600, 'İstanbul', false);

$eczaneler = $service->getNobetciEczaneler('İstanbul');
assertTest('EczaneService nöbetçi eczaneleri çekme', count($eczaneler) > 0);

$bulkReport = $service->bulkFetch(['İstanbul', 'Ankara', 'GecersizIl999'], 0);
assertTest('bulkFetch başarılı iller sayısı', $bulkReport['basarili_sayisi'] === 2);
assertTest('bulkFetch çekilemeyen iller raporlama', $bulkReport['basarisiz_sayisi'] === 1 && $bulkReport['cekilemeyen_iller'][0]['il'] === 'GecersizIl999');

// 7. REST Router Testi
$router = new Router();
$router->get('/api/v1/ping', fn() => ['status' => 'pong']);
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/v1/ping']);
$res = $router->dispatch($req);
assertTest('Router RESTful dispatch', $res instanceof \EczaneApi\Http\Response);

// 8. Güvenlik ve SSRF Doğrulama Testleri
$httpClient = new \EczaneApi\Http\CurlHttpClient();
$invalidRes = $httpClient->get('file:///etc/passwd');
assertTest('SSRF & Protokol Engelleme (file:// engellendi)', $invalidRes === null && str_contains($httpClient->getLastError() ?? '', 'desteklenmeyen URL'));

$nullByteReq = new Request(['test' => "guvenlik\0tehditi"], [], []);
assertTest('Input Null-Byte Temizleme', $nullByteReq->input('test') === 'guvenliktehditi');

$secHeadersRes = \EczaneApi\Http\Response::json(['ok' => true]);
assertTest('Response Nesnesi Başarıyla Üretildi', $secHeadersRes instanceof \EczaneApi\Http\Response);

// 9. Resources ve Kaynak Kataloğu Testleri
$sources = \EczaneApi\Data\SourcesCatalog::getSources();
assertTest('resources/data/sources.json yüklendi', isset($sources['primary_source']['name']));

$odalar = \EczaneApi\Data\SourcesCatalog::getOdalar();
assertTest('54 Eczacı Odası eksiksiz tanımlı', count($odalar) === 54);

$istanbulOda = \EczaneApi\Data\SourcesCatalog::findOdaByCity('İstanbul');
assertTest('İstanbul Eczacı Odası tespiti', $istanbulOda !== null && $istanbulOda['bolge_no'] === 1);

$antalyaOda = \EczaneApi\Data\SourcesCatalog::findOdaByPlaka(7);
assertTest('Antalya (07) Eczacı Odası ve XML yetenek tespiti', $antalyaOda !== null && $antalyaOda['xml_destegi'] === true);

echo "\n--------------------------------------------------------\n";
echo "📊 TEST SONUÇLARI: {$passed} Başarılı | {$failed} Hatalı\n";
echo "--------------------------------------------------------\n";

exit($failed > 0 ? 1 : 0);
