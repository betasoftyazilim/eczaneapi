<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use EczaneApi\Data\Sehirler;
use EczaneApi\Sources\EDevletSource;

$src = new EDevletSource();
$iller = Sehirler::all();
$toplam = count($iller);

$basarili = [];
$cekilemeyenler = [];

echo "81 İl Canlı e-Devlet Taraması Başlatıldı...\n";

foreach ($iller as $idx => $il) {
    $plaka = $il['id'];
    $ad = $il['name'];
    $veri = $src->fetch($ad);

    if (is_array($veri) && !empty($veri)) {
        $count = count($veri);
        $basarili[$ad] = [
            'plaka' => $plaka,
            'il' => $ad,
            'eczane_sayisi' => $count,
        ];
        echo sprintf("[%02d/%02d] Plaka %02d - %s: ✔ %d Eczane\n", $idx + 1, $toplam, $plaka, $ad, $count);
    } else {
        $hata = $src->getLastError() ?? 'Veri bulunamadı veya e-Devlet yanıt vermedi';
        $cekilemeyenler[] = [
            'plaka' => $plaka,
            'il' => $ad,
            'hata' => $hata,
        ];
        echo sprintf("[%02d/%02d] Plaka %02d - %s: ✘ ÇEKİLEMEDİ (%s)\n", $idx + 1, $toplam, $plaka, $ad, $hata);
    }

    usleep(250000); // 250ms rate limit koruması
}

$rapor = [
    'tarih' => date('c'),
    'toplam_il' => $toplam,
    'basarili_sayisi' => count($basarili),
    'cekilemeyen_sayisi' => count($cekilemeyenler),
    'basarili_iller' => $basarili,
    'cekilemeyen_iller' => $cekilemeyenler,
];

file_put_contents(
    __DIR__ . '/../storage/cekilemeyen_iller.json',
    json_encode($rapor, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

echo "\n============================================\n";
echo "📊 TARAMA TAMAMLANDI\n";
echo "Başarılı İl Sayısı: " . count($basarili) . " / {$toplam}\n";
echo "Çekilemeyen İl Sayısı: " . count($cekilemeyenler) . " / {$toplam}\n";
echo "============================================\n";
