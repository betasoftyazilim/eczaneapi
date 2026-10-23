<?php

require_once __DIR__ . '/../src/EczaneClient.php';
require_once __DIR__ . '/../src/Eczane.php';

use EczaneApi\Client\Eczane;
use EczaneApi\Client\EczaneClient;

echo "🧪 PHP Eczane SDK Testleri Başlatılıyor...\n";

$client = new EczaneClient('https://mock.local');
if (!($client instanceof EczaneClient)) {
    throw new Exception("EczaneClient örneği oluşturulamadı!");
}

if (!is_callable([Eczane::class, 'nobetci'])) {
    throw new Exception("Eczane::nobetci çağrılabilir değil!");
}

if (!is_callable([Eczane::class, 'iller'])) {
    throw new Exception("Eczane::iller çağrılabilir değil!");
}

if (!is_callable([Eczane::class, 'yakin'])) {
    throw new Exception("Eczane::yakin çağrılabilir değil!");
}

echo "✅ Tüm PHP SDK birim testleri başarıyla geçti!\n";
