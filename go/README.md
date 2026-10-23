# Eczane API - Go (Golang) SDK 💊

Türkiye 81 il ve 973 ilçesinin tamamı için resmi ve güncel nöbetçi eczane verilerine doğrudan erişim sağlayan resmi Go istemci kütüphanesi.

[![Go Reference](https://pkg.go.dev/badge/github.com/betasoftyazilim/eczaneapi/go.svg)](https://pkg.go.dev/github.com/betasoftyazilim/eczaneapi/go)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 🚀 Kurulum

```bash
go get github.com/betasoftyazilim/eczaneapi/go
```

*(Sıfır harici paket bağımlılığı! Standart Go kütüphanesi `net/http` ile ultra hızlı çalışır.)*

---

## 💡 Hızlı Başlangıç

```go
package main

import (
	"fmt"
	"log"

	eczane "github.com/betasoftyazilim/eczaneapi/go"
)

func main() {
	// 1. Kadıköy nöbetçi eczaneleri
	eczaneler, err := eczane.Nobetci("istanbul", "kadikoy", "")
	if err != nil {
		log.Fatal(err)
	}

	for _, e := range eczaneler {
		fmt.Printf("%s | Tel: %v | Adres: %v\n", e.Adi, *e.Telefon, *e.Adres)
	}

	// 2. 81 İl ve İlçe Listesi
	iller, _ := eczane.Iller()
	fmt.Printf("Toplam %d il listelendi.\n", len(iller))

	// 3. GPS Koordinatına En Yakın Nöbetçi Eczaneler (5 km yarıçap)
	yakinlar, _ := eczane.Yakin(41.0082, 28.9784, 5.0)
	fmt.Printf("%d adet yakın eczane bulundu.\n", len(yakinlar))
}
```

---

## 📦 Lisans & Geliştirici

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.  
Geliştirici: **[BetaSoft Yazılım](https://www.betasoft.com.tr)**  
Web: [https://www.betasoft.com.tr](https://www.betasoft.com.tr)  
GitHub: [https://github.com/betasoftyazilim/eczaneapi](https://github.com/betasoftyazilim/eczaneapi)
