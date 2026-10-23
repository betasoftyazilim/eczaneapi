# Eczane API - Node.js & TypeScript SDK 💊

Türkiye 81 il ve 973 ilçesinin tamamı için resmi ve güncel nöbetçi eczane verilerine doğrudan erişim sağlayan resmi Node.js & TypeScript istemci kütüphanesi.

[![npm version](https://img.shields.io/npm/v/eczaneapi.svg)](https://www.npmjs.com/package/eczaneapi)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 🚀 Kurulum

```bash
npm install eczaneapi
# veya
yarn add eczaneapi
# veya
pnpm add eczaneapi
```

*(Sıfır ek harici bağımlılık! Native `fetch` kullanarak ultra hafif ve hızlı çalışır.)*

---

## 💡 Hızlı Başlangıç

### JavaScript (CommonJS / ES Modules) & TypeScript

```typescript
import { Eczane } from "eczaneapi";

// 1. İl & İlçe bazlı nöbetçi eczaneleri çekme
const eczaneler = await Eczane.nobetci("istanbul", "kadikoy");
eczaneler.forEach((e) => {
  console.log(`${e.adi} | Tel: ${e.telefon} | Adres: ${e.adres}`);
});

// 2. 81 İl ve İlçe Listesi
const iller = await Eczane.iller();
console.log(`Toplam ${iller.length} il listelendi.`);

// 3. GPS Koordinatına En Yakın Nöbetçi Eczaneler
const yakinlar = await Eczane.yakin(41.0082, 28.9784, 5); // 5 km yarıçap
```

### Özel Sunucu / API Anahtarı ile Kullanım

```typescript
import { EczaneClient } from "eczaneapi";

const client = new EczaneClient({
  baseUrl: "https://eczaneapi.vercel.app", // kendi API adresiniz
  apiKey: "OPSIYONEL_KEY",
  timeout: 10000,
});

const liste = await client.nobetci("izmir", "bornova");
```

---

## 📦 Lisans

MIT License - BetaSoft Yazılım
