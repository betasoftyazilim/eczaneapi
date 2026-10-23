# Eczane API - Rust SDK 💊

Türkiye 81 il ve 973 ilçesinin tamamı için resmi ve güncel nöbetçi eczane verilerine doğrudan erişim sağlayan resmi Rust istemci kütüphanesi.

[![Crates.io](https://img.shields.io/crates/v/eczaneapi.svg)](https://crates.io/crates/eczaneapi)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 🚀 Kurulum

`Cargo.toml` dosyanıza ekleyin:
```toml
[dependencies]
eczaneapi = "1.1.1"
tokio = { version = "1", features = ["full"] }
```

---

## 💡 Hızlı Başlangıç

```rust
use eczaneapi::EczaneClient;

#[tokio::main]
async fn main() -> Result<(), Box<dyn std::error::Error>> {
    let client = EczaneClient::new(None, None);

    // 1. Kadıköy nöbetçi eczaneleri
    let eczaneler = client.nobetci("istanbul", Some("kadikoy"), None).await?;
    for e in eczaneler {
        println!("{} | Tel: {:?} | Adres: {:?}", e.adi, e.telefon, e.adres);
    }

    // 2. 81 İl ve İlçe Listesi
    let iller = client.iller().await?;
    println!("Toplam {} il listelendi.", iller.len());

    // 3. GPS Koordinatına En Yakın Nöbetçi Eczaneler (5 km yarıçap)
    let yakinlar = client.yakin(41.0082, 28.9784, 5.0).await?;
    println!("{} adet yakın eczane bulundu.", yakinlar.len());

    Ok(())
}
```

---

## 📦 Lisans & Geliştirici

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.  
Geliştirici: **[BetaSoft Yazılım](https://www.betasoft.com.tr)**  
Web: [https://www.betasoft.com.tr](https://www.betasoft.com.tr)  
GitHub: [https://github.com/betasoftyazilim/eczaneapi](https://github.com/betasoftyazilim/eczaneapi)
