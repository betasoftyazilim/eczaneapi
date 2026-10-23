# Eczane API - Dart & Flutter SDK 💊

Türkiye 81 il ve 973 ilçesinin tamamı için resmi ve güncel nöbetçi eczane verilerine doğrudan erişim sağlayan resmi Dart & Flutter istemci kütüphanesi.

[![pub package](https://img.shields.io/pub/v/eczaneapi.svg)](https://pub.dev/packages/eczaneapi)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 🚀 Kurulum

Flutter projelerinizde:
```bash
flutter pub add eczaneapi
```

Saf Dart projelerinizde:
```bash
dart pub add eczaneapi
```

---

## 💡 Hızlı Başlangıç

### 1. Statik Kullanım (Flutter / Dart)

```dart
import 'package:eczaneapi/eczaneapi.dart';

void main() async {
  // 1. Kadıköy nöbetçi eczaneleri
  final eczaneler = await Eczane.nobetci('istanbul', 'kadikoy');
  for (var e in eczaneler) {
    print('${e.adi} | Tel: ${e.telefon} | Adres: ${e.adres}');
  }

  // 2. 81 İl ve İlçe Listesi
  final iller = await Eczane.iller();
  print('Toplam ${iller.length} il listelendi.');

  // 3. GPS Koordinatına En Yakın Nöbetçi Eczaneler (Örn: 5 km yarıçap)
  final yakinlar = await Eczane.yakin(41.0082, 28.9784, 5.0);
}
```

### 2. Özel İstemci

```dart
import 'package:eczaneapi/eczaneapi.dart';

final client = EczaneClient(
  baseUrl: 'https://eczaneapi.vercel.app',
  apiKey: 'OPSIYONEL_KEY',
);

final sonuclar = await client.nobetci('ankara', 'cankaya');
```

---

## 📦 Lisans & Geliştirici

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.  
Geliştirici: **[BetaSoft Yazılım](https://www.betasoft.com.tr)**  
Web: [https://www.betasoft.com.tr](https://www.betasoft.com.tr)  
GitHub: [https://github.com/betasoftyazilim/eczaneapi](https://github.com/betasoftyazilim/eczaneapi)
