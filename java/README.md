# Eczane API - Java SDK 💊

Türkiye 81 il ve 973 ilçesinin tamamı için resmi ve güncel nöbetçi eczane verilerine doğrudan erişim sağlayan resmi Java (Android, Spring Boot, Quarkus) istemci kütüphanesi.

[![Maven Central](https://img.shields.io/maven-central/v/tr.com.betasoft/eczaneapi.svg)](https://central.sonatype.com/artifact/tr.com.betasoft/eczaneapi)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 🚀 Kurulum

### Maven
```xml
<dependency>
    <groupId>tr.com.betasoft</groupId>
    <artifactId>eczaneapi</artifactId>
    <version>1.1.1</version>
</dependency>
```

### Gradle
```groovy
implementation 'tr.com.betasoft:eczaneapi:1.1.1'
```

---

## 💡 Hızlı Başlangıç

```java
import tr.com.betasoft.eczaneapi.Eczane;
import tr.com.betasoft.eczaneapi.models.EczaneModel;
import java.util.List;

public class Main {
    public static void main(String[] args) throws Exception {
        // 1. Kadıköy nöbetçi eczaneleri
        List<EczaneModel> eczaneler = Eczane.nobetci("istanbul", "kadikoy");
        for (EczaneModel e : eczaneler) {
            System.out.println(e.getAdi() + " | Tel: " + e.getTelefon() + " | " + e.getAdres());
        }

        // 2. 81 İl ve İlçe Listesi
        var iller = Eczane.iller();
        System.out.println("Toplam il: " + iller.size());

        // 3. En Yakın Eczaneler (GPS ile 5 km yarıçap)
        var yakinlar = Eczane.yakin(41.0082, 28.9784, 5.0);
    }
}
```

---

## 📦 Lisans & Geliştirici

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.  
Geliştirici: **[BetaSoft Yazılım](https://www.betasoft.com.tr)**  
Web: [https://www.betasoft.com.tr](https://www.betasoft.com.tr)  
GitHub: [https://github.com/betasoftyazilim/eczaneapi](https://github.com/betasoftyazilim/eczaneapi)
