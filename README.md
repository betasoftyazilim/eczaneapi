# Türkiye Nöbetçi Eczane API & Kazıma Motoru 💊

Türkiye'nin **81 ili** ve **973 resmi ilçesi** için resmi **e-Devlet TİTCK (Türkiye İlaç ve Tıbbi Cihaz Kurumu)** altyapısıyla anlık güncellenen, yüksek performanslı, önbellek korumalı, **Laravel** ve **tüm PHP MVC mimarileriyle** tam uyumlu bağımsız API ve kazıma sistemi.

Ayrıca **MaxMind GeoIP** ve **WP-Statistics** açık kaynak GeoLite2 altyapısını kullanarak kullanıcının IP adresinden otomatik il/ilçe tespiti ve en yakın nöbetçi eczaneleri mesafeye göre (km) sıralama yeteneğine sahiptir.

---

## 🌟 Öne Çıkan Özellikler

- **81 İl ve 973 İlçe:** Türkiye'nin tüm idari yapısı ve resmi ilçeleri eksiksiz tanımlıdır.
- **Resmi e-Devlet Kazıma Motoru:** Dinamik oturum, CSRF token ayrıştırma ve XPath DOM analiziyle doğrudan kaynaktan güncel nöbet listesi.
- **MaxMind & WP-Statistics GeoIP:** `github.com/wp-statistics/GeoLite2-City` deposuyla anahtarsız ve ücretsiz MMDB entegrasyonu; IP'den il/ilçe tespiti ve Haversine formülüyle en yakın eczaneleri listeleme.
- **Laravel Desteği:** ServiceProvider, Facade (`Eczane::il('İstanbul')->get()`), Artisan komutları (`php artisan eczane:fetch --all`), Controller ve API rotaları.
- **Herhangi Bir MVC / Saf PHP ile Uyumlu:** Laravel, Symfony, CodeIgniter, Slim veya özel MVC mimarilerine tek satırda dahil edilebilir PSR-4 mimarisi.
- **Akıllı Önbellek (Cache):** Dosya tabanlı (FileCache), bellek içi (ArrayCache), veritabanı (PDO/MySQL) veya Laravel Cache sürücüleri ile 6 saatlik (ayarlanabilir) TTL koruması.
- **Çekilemeyen İller Analizi:** e-Devlet'in yayınlamadığı veya zaman aşımına uğrayan illeri tespit edip detaylı raporlama ve yeniden çekme altyapısı (bkz. `IL_VE_ILCE_DURUM_RAPORU.md`).
- **Dahili Modern Canlı Panel:** Web tarayıcısı üzerinden interaktif sorgulama, harita/yol tarifi, telefon arama ve REST API test arayüzü (`public/index.php`).

---

---

## 📦 SDK ve Kütüphane Kurulumları

Projenizi hangi dille geliştiriyorsanız doğrudan ilgili resmi istemci paketini kullanabilirsiniz:

| Platform / Dil | Paket Yöneticisi | Kurulum Komutu | Dizin |
| :--- | :--- | :--- | :--- |
| **PHP (Composer)** | Packagist | `composer require betasoft/eczaneapi` | `./` |
| **Python (PyPI)** | pip | `pip install eczaneapi` | [`pypi/`](./pypi) |
| **Node.js / TS (npm)** | npm / yarn / pnpm | `npm install eczaneapi` | [`npm/`](./npm) |
| **.NET / C# (NuGet)** | dotnet CLI | `dotnet add package EczaneApi` | [`dotnet/`](./dotnet) |

---

### 🐍 Python (PyPI) Hızlı Kullanım
```python
from eczaneapi import Eczane

# İstanbul Kadıköy nöbetçi eczaneleri
eczaneler = Eczane.nobetci("istanbul", "kadikoy")
for e in eczaneler:
    print(f"{e.adi} | Tel: {e.telefon} | Adres: {e.adres}")
```

### 🟩 Node.js & TypeScript (npm) Hızlı Kullanım
```typescript
import { Eczane } from "eczaneapi";

const eczaneler = await Eczane.nobetci("istanbul", "kadikoy");
console.log(eczaneler);
```

### 🔷 .NET / C# (NuGet) Hızlı Kullanım
```csharp
using EczaneApi;

var eczaneler = await Eczane.NobetciAsync("istanbul", "kadikoy");
foreach (var e in eczaneler)
{
    Console.WriteLine($"{e.Adi} - {e.Telefon}");
}
```

---

## 🚀 1. Laravel Projelerine Entegrasyon Rehberi

Paket, Laravel 9, 10, 11 ve 12 sürümleriyle otomatik paket keşfi (`package:discover`) desteğine sahiptir.

### Adım 1: Yapılandırma Dosyasını Yayınlayın
```bash
php artisan vendor:publish --tag=eczane-config
```
Bu komut `config/eczane.php` dosyasını oluşturur. Buradan varsayılan ili, önbellek süresini (TTL) ve GeoIP ayarlarını özelleştirebilirsiniz.

### Adım 2: Controller İçinde Kullanım
Örnek Controller: `app/Http/Controllers/NobetciEczaneController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use EczaneApi\Laravel\Facades\Eczane;

class NobetciEczaneController extends Controller
{
    public function index(Request $request)
    {
        $secilenIl = $request->input('il', 'İstanbul');
        $secilenIlce = $request->input('ilce');

        // Nöbetçi eczaneleri çek
        $eczaneler = Eczane::il($secilenIl)->ilce($secilenIlce)->get();
        
        // 81 ili ve seçili ilin ilçelerini al
        $iller = Eczane::iller();
        $ilceler = Eczane::ilceler($secilenIl);

        return view('nobetci-eczane', compact('eczaneler', 'iller', 'ilceler', 'secilenIl', 'secilenIlce'));
    }
}
```

### Adım 3: Rota Tanımı (`routes/web.php`)
```php
use App\Http\Controllers\NobetciEczaneController;

Route::get('/nobetci-eczaneler', [NobetciEczaneController::class, 'index'])->name('nobetci.eczane');
```

### Adım 4: Blade Görünümünde Listeleme (`resources/views/nobetci-eczane.blade.php`)
```html
<div class="container">
    <h2>{{ $secilenIl }} Nöbetçi Eczaneleri</h2>

    <!-- İl / İlçe Seçimi -->
    <form method="GET" action="{{ route('nobetci.eczane') }}">
        <select name="il" onchange="this.form.submit()">
            @foreach($iller as $il)
                <option value="{{ $il->name }}" {{ $secilenIl == $il->name ? 'selected' : '' }}>
                    {{ $il->name }}
                </option>
            @endforeach
        </select>
        
        @if(!empty($ilceler))
        <select name="ilce" onchange="this.form.submit()">
            <option value="">Tüm İlçeler</option>
            @foreach($ilceler as $ilce)
                <option value="{{ $ilce }}" {{ $secilenIlce == $ilce ? 'selected' : '' }}>
                    {{ $ilce }}
                </option>
            @endforeach
        </select>
        @endif
    </form>

    <!-- Eczane Kartları -->
    <div class="grid">
        @forelse($eczaneler as $eczane)
            <div class="card">
                <span class="badge">{{ $eczane->dist }}</span>
                <h3>{{ $eczane->name }}</h3>
                <p>📍 {{ $eczane->address }}</p>
                @if($eczane->phone)
                    <a href="tel:{{ $eczane->getCleanPhone() }}">📞 {{ $eczane->phone }}</a>
                @endif
                <a href="{{ $eczane->getMapsUrl() }}" target="_blank">🗺️ Yol Tarifi</a>
            </div>
        @empty
            <p>Bu il/ilçe için nöbetçi eczane kaydı bulunamadı.</p>
        @endforelse
    </div>
</div>
```

### Adım 5: Artisan CLI Komutları
```bash
# Tek bir ili sorgula
php artisan eczane:fetch İstanbul --ilce=Kadıköy

# Önbelleği pas geçip güncel canlı veriyi çek
php artisan eczane:fetch Ankara --fresh

# 81 ilin tamamını sırayla tara ve çekilemeyen illeri raporla
php artisan eczane:fetch --all --sleep=1
```

---

## 🏛️ 2. CodeIgniter 4 Projelerine Entegrasyon Rehberi

### Adım 1: PSR-4 Autoload Tanımı
Eğer Composer ile yüklediyseniz otomatik tanınır. Manuel eklemek için `app/Config/Autoload.php`:
```php
public $psr4 = [
    APP_NAMESPACE => APPPATH,
    'EczaneApi\\' => ROOTPATH . 'packages/eczaneapi/src',
];
```

### Adım 2: Controller (`app/Controllers/Eczane.php`)
```php
<?php

namespace App\Controllers;

use EczaneApi\Client;

class Eczane extends BaseController
{
    public function index()
    {
        $il = $this->request->getGet('il') ?? 'İstanbul';
        $ilce = $this->request->getGet('ilce');

        $eczaneler = Client::il($il)->ilce($ilce)->get();
        $iller = Client::iller();
        $ilceler = Client::ilceler($il);

        return view('eczane_listesi', [
            'eczaneler' => $eczaneler,
            'iller' => $iller,
            'ilceler' => $ilceler,
            'secilenIl' => $il,
            'secilenIlce' => $ilce,
        ]);
    }
}
```

---

## 🎻 3. Symfony Projelerine Entegrasyon Rehberi

Controller: `src/Controller/EczaneController.php`
```php
<?php

namespace App\Controller;

use EczaneApi\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EczaneController extends AbstractController
{
    #[Route('/nobetci-eczaneler', name: 'app_nobetci_eczane')]
    public function index(Request $request): Response
    {
        $il = $request->query->get('il', 'İstanbul');
        $ilce = $request->query->get('ilce');

        $eczaneler = Client::il($il)->ilce($ilce)->get();

        return $this->render('eczane/index.html.twig', [
            'eczaneler' => $eczaneler,
            'secilenIl' => $il,
        ]);
    }
}
```

---

## 💻 4. Saf PHP ve Özel MVC Sistemlerinde Kullanım

Framework kullanmadan en yalın haliyle entegrasyon:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use EczaneApi\Client;

// 1. Şehir ve İlçe ile Sorgu
$eczaneler = Client::il('İzmir')->ilce('Karşıyaka')->get();

foreach ($eczaneler as $eczane) {
    echo "Eczane: " . $eczane->name . PHP_EOL;
    echo "İlçe: " . $eczane->dist . PHP_EOL;
    echo "Telefon: " . $eczane->phone . " (Temiz: " . $eczane->getCleanPhone() . ")" . PHP_EOL;
    echo "Adres: " . $eczane->address . PHP_EOL;
    echo "Google Maps: " . $eczane->getMapsUrl() . PHP_EOL;
    echo "-----------------------------------" . PHP_EOL;
}

// 2. Dizi (Array) Formatında Almak İsterseniz
$diziVerisi = Client::il('Ankara')->toArray();

// 3. 81 İli ve 973 İlçeyi Listelemek
$iller = Client::iller();
$ilceler = Client::ilceler('Bursa');
```

---

## 🌍 5. MaxMind & WP-Statistics GeoIP Entegrasyonu

Sistem, `github.com/wp-statistics/GeoLite2-City` açık kaynak veritabanını kullanarak:
1. Ziyaretçinin IP adresinden (`CF-Connecting-IP`, `X-Forwarded-For` veya `REMOTE_ADDR`) **il**, **ilçe** ve **enlem/boylam** koordinatlarını otomatik tespit eder.
2. Türkiye'nin tüm 81 ilini `TR-01` ile `TR-81` arası **ISO 3166-2:TR** kodlarıyla eşler.
3. `Haversine` mesafe algoritması sayesinde kullanıcıya en yakın nöbetçi eczaneleri kilometre (km) mesafesiyle sıralar.

```php
use EczaneApi\Client;

// Ziyaretçinin IP adresinden il ve ilçe tespiti
$geo = Client::geoLookup('88.255.10.20');
$il = $geo->getProvinceName();     // örn: "İstanbul"
$ilce = $geo->getDistrictName();   // örn: "Kadıköy"
$plaka = $geo->getPlateCode();      // 34

// Kullanıcının bulunduğu yere en yakın nöbetçi eczaneleri çek
$enYakinEczaneler = Client::il($il)->ilce($ilce)->get();

// WP-Statistics deposundan en son GeoLite2-City.mmdb dosyasını güncelle
$guncelleme = Client::updateGeoIp();
```

---

## 📡 6. RESTful JSON API Uç Noktaları

Bağımsız yerleşik web sunucusunu başlatmak için:
```bash
php -S localhost:8000 -t public
```

### Uç Noktalar Tablosu

| Metot | Uç Nokta | Açıklama |
| :---: | :--- | :--- |
| `GET` | `/api/v1/nobetci?il=istanbul&ilce=kadikoy` | Belirtilen il ve ilçedeki nöbetçi eczaneleri listeler. |
| `GET` | `/api/v1/nobetci?ip=auto` | Ziyaretçinin IP'sinden ili tespit eder ve nöbetçileri getirir. |
| `GET` | `/api/v1/nobetci?lat=41.0082&lon=28.9784` | Koordinata en yakın eczaneleri kilometre mesafesiyle sıralar. |
| `GET` | `/api/v1/iller` | Türkiye'nin 81 ilini ve MaxMind ISO kodlarını döndürür. |
| `GET` | `/api/v1/ilceler?il=istanbul` | Seçili ilin ilçelerini listeler. |
| `GET` | `/api/v1/geo/lookup?ip=...` | Verilen IP'nin MaxMind GeoIP detaylarını döndürür. |
| `GET` | `/api/v1/geo/hierarchy` | 81 il ve 973 ilçenin MaxMind standart hiyerarşisini verir. |
| `GET` | `/api/v1/status` | Sistem sağlık ve önbellek istatistiklerini verir. |
| `POST`| `/api/v1/refresh?il=istanbul` | Belirli bir ilin önbelleğini zorla tazeler. |
| `POST`| `/api/v1/cache/clear` | Tüm önbelleği temizler. |

---

## 📊 7. Çekilemeyen İller ve Durum Raporu

Resmi e-Devlet TİTCK web servisinden yapılan 81 il canlı taramasında:
- **71 İl Başarıyla Çekilmektedir.**
- **10 İl (Artvin, Aydın, Elazığ, Eskişehir, Hakkari, Kayseri, Kırşehir, Malatya, Muş, Kilis)** e-Devlet üzerinde ilgili yerel eczacı odalarının veriyi sisteme girmemesi nedeniyle merkezi serviste boş dönmektedir.

Detaylı teknik rapor ve analiz için **[IL_VE_ILCE_DURUM_RAPORU.md](file:///d:/proje/eczaneapi/IL_VE_ILCE_DURUM_RAPORU.md)** dosyasını inceleyebilirsiniz.

---

## 🚀 8. GitHub'a Gönderme (Push) Adımları

Projeyi kendi GitHub hesabınıza yüklemek için terminalde şu komutları sırayla çalıştırabilirsiniz:

```bash
git init
git add .
git commit -m "feat: Turkiye Nobetci Eczane API, MaxMind GeoIP ve 81 il/973 ilce sistemi"
git branch -M main
git remote add origin https://github.com/KULLANICI_ADINIZ/eczane-api.git
git push -u origin main
```

---

## 🛠️ Testleri Çalıştırma

Tüm modülleri, MaxMind hiyerarşisini, 973 ilçeyi ve e-Devlet kazıma motorunu doğrulamak için:

```bash
php tests/run_tests.php
```

---

## 📄 Lisans
Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.

---

## ⭐ Destek ve Katkı

> **Projeyi beğendiyseniz ve işinize yaradıysa GitHub'da bir yıldız (⭐ Star) vermeyi unutmayın! Destekleriniz için teşekkürler.**
> 
> 👉 **[GitHub Deposu: betasoftyazilim/eczaneapi](https://github.com/betasoftyazilim/eczaneapi)**

