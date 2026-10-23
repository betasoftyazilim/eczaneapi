# Eczane API - .NET / C# SDK 💊

Türkiye 81 il ve 973 ilçesinin tamamı için resmi ve güncel nöbetçi eczane verilerine doğrudan erişim sağlayan resmi .NET / C# istemci kütüphanesi.

[![NuGet version](https://img.shields.io/nuget/v/EczaneApi.svg)](https://www.nuget.org/packages/EczaneApi/)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 🚀 Kurulum

.NET CLI ile:
```bash
dotnet add package EczaneApi
```

Paket Yöneticisi Konsolu (Visual Studio):
```powershell
Install-Package EczaneApi
```

Uyumlu Platformlar: `.NET Standard 2.0`, `.NET 8.0`, `.NET 9.0` (WPF, Windows Forms, MAUI, ASP.NET Core, Konsol).

---

## 💡 Hızlı Başlangıç

### 1. Statik Kullanım

```csharp
using EczaneApi;

// İstanbul Kadıköy nöbetçi eczaneleri
var eczaneler = await Eczane.NobetciAsync("istanbul", "kadikoy");
foreach (var e in eczaneler)
{
    Console.WriteLine($"{e.Adi} - Tel: {e.Telefon} - {e.Adres}");
}

// 81 İl ve İlçe Listesi
var iller = await Eczane.IllerAsync();

// GPS Koordinatına En Yakın Nöbetçi Eczaneler (Örn: 5 km yarıçap)
var yakinlar = await Eczane.YakinAsync(41.0082, 28.9784, 5.0);
```

### 2. Dependency Injection / Özel İstemci

```csharp
using EczaneApi;

var client = new EczaneClient(
    baseUrl: "https://eczaneapi.vercel.app",
    apiKey: "OPSIYONEL_KEY"
);

var sonuclar = await client.NobetciAsync("ankara", "cankaya");
```

---

## 📦 Lisans & Geliştirici

MIT License - **[BetaSoft Yazılım](https://betasoft.com.tr)**  
Web: [https://betasoft.com.tr](https://betasoft.com.tr)
