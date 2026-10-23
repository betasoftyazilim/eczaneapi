# Eczane API - PHP SDK İstemcisi 💊

Türkiye 81 il ve 973 ilçesinin tamamı için resmi ve güncel nöbetçi eczane verilerine doğrudan erişim sağlayan resmi bağımsız PHP istemci kütüphanesi.

[![Packagist Version](https://img.shields.io/packagist/v/betasoftyazilim/eczaneapi-client.svg)](https://packagist.org/packages/betasoftyazilim/eczaneapi-client)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 🚀 Kurulum

```bash
composer require betasoftyazilim/eczaneapi-client
```

*(Sıfır harici paket bağımlılığı! Standart PHP cURL ve JSON uzantılarıyla ultra hafif ve hızlı çalışır.)*

---

## 💡 Hızlı Başlangıç

### 1. Statik Kullanım

```php
use EczaneApi\Client\Eczane;

// İstanbul Kadıköy nöbetçi eczaneleri
$eczaneler = Eczane::nobetci('istanbul', 'kadikoy');
foreach ($eczaneler as $e) {
    echo "{$e['adi']} | Tel: {$e['telefon']} | Adres: {$e['adres']}\n";
}

// 81 İl ve İlçe Listesi
$iller = Eczane::iller();

// GPS Koordinatına En Yakın Nöbetçi Eczaneler (Örn: 5 km yarıçap)
$yakinlar = Eczane::yakin(41.0082, 28.9784, 5.0);
```

### 2. İstemci (Client) ile Özel Sunucu

```php
use EczaneApi\Client\EczaneClient;

$client = new EczaneClient(
    baseUrl: 'https://eczaneapi.vercel.app', // veya kendi sunucunuz
    apiKey: 'OPSIYONEL_KEY',
    timeout: 10
);

$eczaneler = $client->nobetci('ankara', 'cankaya');
```

---

## 📦 Lisans & Geliştirici

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.  
Geliştirici: **[BetaSoft Yazılım](https://www.betasoft.com.tr)**  
Web: [https://www.betasoft.com.tr](https://www.betasoft.com.tr)  
GitHub: [https://github.com/betasoftyazilim/eczaneapi](https://github.com/betasoftyazilim/eczaneapi)
