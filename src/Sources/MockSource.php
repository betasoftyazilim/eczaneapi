<?php

declare(strict_types=1);

namespace EczaneApi\Sources;

use EczaneApi\Contracts\EczaneSourceInterface;
use EczaneApi\Data\Sehirler;

class MockSource implements EczaneSourceInterface
{
    private static array $ornekAdlar = [
        'Merkez', 'Hayat', 'Şifa', 'Güneş', 'Hilal', 'Sağlık', 'Yeni', 'Park',
        'Çarşı', 'Liman', 'Akdeniz', 'Anadolu', 'Umut', 'Huzur', 'Barış',
        'Menekşe', 'Zambak', 'Papatya', 'Deva', 'Derman', 'Bahar', 'Cumhuriyet'
    ];

    public function getName(): string
    {
        return 'mock';
    }

    public function fetch(string $ilAdi, ?string $tarih = null): ?array
    {
        $plakaKodu = Sehirler::getPlakaKodu($ilAdi);
        if ($plakaKodu === null) {
            return null;
        }

        $resmiAd = Sehirler::getResmiAd($plakaKodu) ?? $ilAdi;
        $tarihStr = $tarih ?? date('d/m/Y');
        $nobetBaslangic = $tarihStr . ' 08:30';
        $nobetBitis = date('d/m/Y', strtotime('+1 day')) . ' 08:30';

        $ornekIlceler = [
            'Merkez', 'Atatürk', 'Cumhuriyet', 'İnönü', 'Gazi'
        ];

        $eczaneler = [];
        $adet = max(3, ($plakaKodu % 7) + 3);

        for ($i = 0; $i < $adet; $i++) {
            $adIndex = ($plakaKodu + $i) % count(self::$ornekAdlar);
            $eczaneAdi = self::$ornekAdlar[$adIndex] . ' Eczanesi';
            $ilce = $ornekIlceler[$i % count($ornekIlceler)];
            $sokakNo = ($i + 1) * 12;
            $telRastgele = sprintf('0(%03d) %03d %02d %02d', 200 + $plakaKodu, 100 + $i * 15, 20 + $i, 30 + $i);

            $eczaneler[] = [
                'name' => $eczaneAdi,
                'dist' => $ilce,
                'city' => $resmiAd,
                'address' => "{$ilce} Mah. Atatürk Cad. No: {$sokakNo}/A {$resmiAd}",
                'phone' => $telRastgele,
                'nobet_baslangic' => $nobetBaslangic,
                'nobet_bitis' => $nobetBitis,
                'lat' => round(39.0 + ($plakaKodu * 0.05), 6),
                'lon' => round(35.0 + ($plakaKodu * 0.08), 6),
                'tarifi' => 'Devlet Hastanesi karşısı',
            ];
        }

        return $eczaneler;
    }
}
