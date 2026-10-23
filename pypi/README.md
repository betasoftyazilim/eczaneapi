# Eczane API - Python SDK 💊

Türkiye 81 il ve 973 ilçesinin tamamı için resmi ve güncel nöbetçi eczane verilerine doğrudan erişim sağlayan resmi Python istemci kütüphanesi.

[![PyPI version](https://img.shields.io/pypi/v/eczaneapi.svg)](https://pypi.org/project/eczaneapi/)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 🚀 Kurulum

```bash
pip install eczaneapi
```

*(Sıfır harici bağımlılık! Standart Python kütüphanesi ile ultra hafif ve hızlı çalışır.)*

---

## 💡 Hızlı Başlangıç

### 1. Statik Kolay Kullanım

```python
from eczaneapi import Eczane

# İstanbul Kadıköy nöbetçi eczaneleri
eczaneler = Eczane.nobetci(il="istanbul", ilce="kadikoy")
for e in eczaneler:
    print(f"{e.adi} | Tel: {e.telefon} | Adres: {e.adres}")

# Akıcı (Fluent) Arayüz
eczaneler = Eczane.il("ankara").ilce("cankaya").get()

# Tüm İller
iller = Eczane.iller()
for sehir in iller:
    print(sehir.il, len(sehir.ilceler))

# En Yakın Eczaneler (GPS Koordinatları ile)
yakinlar = Eczane.yakin(latitude=41.0082, longitude=28.9784, mesafe_km=3.0)
```

### 2. İstemci (Client) ile Özel Sunucu / API Anahtarı

```python
from eczaneapi import EczaneClient

client = EczaneClient(
    base_url="https://eczaneapi.vercel.app", # veya kendi sunucunuz
    api_key="OPSIYONEL_KEY",
    timeout=10
)

eczaneler = client.nobetci(il="izmir", ilce="karsiyaka")
```

---

## 📦 Lisans & Geliştirici

MIT License - **[BetaSoft Yazılım](https://betasoft.com.tr)**  
Web: [https://betasoft.com.tr](https://betasoft.com.tr)
