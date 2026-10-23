<?php

declare(strict_types=1);

namespace EczaneApi\Data;

use EczaneApi\DTO\SehirDto;

class Sehirler
{
    /**
     * Türkiye 81 İl ve Resmi 973 İlçesinin Eksiksiz Haritası (Plaka sırasıyla)
     */
    private static array $ilIlceHaritasi = [
        1 => ['name' => 'Adana', 'districts' => ['Aladağ', 'Ceyhan', 'Çukurova', 'Feke', 'İmamoğlu', 'Karaisalı', 'Karataş', 'Kozan', 'Pozantı', 'Saimbeyli', 'Sarıçam', 'Seyhan', 'Tufanbeyli', 'Yumurtalık', 'Yüreğir']],
        2 => ['name' => 'Adıyaman', 'districts' => ['Besni', 'Çelikhan', 'Gerger', 'Gölbaşı', 'Kahta', 'Merkez', 'Samsat', 'Sincik', 'Tut']],
        3 => ['name' => 'Afyonkarahisar', 'districts' => ['Başmakçı', 'Bayat', 'Bolvadin', 'Çay', 'Çobanlar', 'Dazkırı', 'Dinar', 'Emirdağ', 'Evciler', 'Hocalar', 'İhsaniye', 'İscehisar', 'Kızılören', 'Merkez', 'Sandıklı', 'Sinanpaşa', 'Sultandağı', 'Şuhut']],
        4 => ['name' => 'Ağrı', 'districts' => ['Diyadin', 'Doğubayazıt', 'Eleşkirt', 'Hamur', 'Merkez', 'Patnos', 'Taşlıçay', 'Tutak']],
        5 => ['name' => 'Amasya', 'districts' => ['Göynücek', 'Gümüşhacıköy', 'Hamamözü', 'Merkez', 'Merzifon', 'Suluova', 'Taşova']],
        6 => ['name' => 'Ankara', 'districts' => ['Akyurt', 'Altındağ', 'Ayaş', 'Bala', 'Beypazarı', 'Çamlıdere', 'Çankaya', 'Çubuk', 'Elmadağ', 'Etimesgut', 'Evren', 'Gölbaşı', 'Güdül', 'Haymana', 'Kalecik', 'Kahramankazan', 'Keçiören', 'Kızılcahamam', 'Mamak', 'Nallıhan', 'Polatlı', 'Pursaklar', 'Sincan', 'Şereflikoçhisar', 'Yenimahalle']],
        7 => ['name' => 'Antalya', 'districts' => ['Akseki', 'Aksu', 'Alanya', 'Demre', 'Döşemealtı', 'Elmalı', 'Finike', 'Gazipaşa', 'Gündoğmuş', 'İbradı', 'Kaş', 'Kemer', 'Kepez', 'Konyaaltı', 'Korkuteli', 'Kumluca', 'Manavgat', 'Muratpaşa', 'Serik']],
        8 => ['name' => 'Artvin', 'districts' => ['Ardanuç', 'Arhavi', 'Borçka', 'Hopa', 'Kemalpaşa', 'Merkez', 'Murgul', 'Şavşat', 'Yusufeli']],
        9 => ['name' => 'Aydın', 'districts' => ['Bozdoğan', 'Buharkent', 'Çine', 'Didim', 'Efeler', 'Germencik', 'İncirliova', 'Karacasu', 'Karpuzlu', 'Koçarlı', 'Köşk', 'Kuşadası', 'Kuyucak', 'Nazilli', 'Söke', 'Sultanhisar', 'Yenipazar']],
        10 => ['name' => 'Balıkesir', 'districts' => ['Altıeylül', 'Ayvalık', 'Balya', 'Bandırma', 'Bigadiç', 'Burhaniye', 'Dursunbey', 'Edremit', 'Erdek', 'Gömeç', 'Gönen', 'Havran', 'İvrindi', 'Karesi', 'Kepsut', 'Manyas', 'Marmara', 'Savaştepe', 'Sındırgı', 'Susurluk']],
        11 => ['name' => 'Bilecik', 'districts' => ['Bozüyük', 'Gölpazarı', 'İnhisar', 'Merkez', 'Osmaneli', 'Pazaryeri', 'Söğüt', 'Yenipazar']],
        12 => ['name' => 'Bingöl', 'districts' => ['Adaklı', 'Genç', 'Karlıova', 'Kiğı', 'Merkez', 'Solhan', 'Yayladere', 'Yedisu']],
        13 => ['name' => 'Bitlis', 'districts' => ['Adilcevaz', 'Ahlat', 'Güroymak', 'Hizan', 'Merkez', 'Mutki', 'Tatvan']],
        14 => ['name' => 'Bolu', 'districts' => ['Dörtdivan', 'Gerede', 'Göynük', 'Kıbrıscık', 'Mengen', 'Merkez', 'Mudurnu', 'Seben', 'Yeniçağa']],
        15 => ['name' => 'Burdur', 'districts' => ['Ağlasun', 'Altınyayla', 'Bucak', 'Çavdır', 'Çeltikçi', 'Gölhisar', 'Karamanlı', 'Kemer', 'Merkez', 'Tefenni', 'Yeşilova']],
        16 => ['name' => 'Bursa', 'districts' => ['Büyükorhan', 'Gemlik', 'Gürsu', 'Harmancık', 'İnegöl', 'İznik', 'Karacabey', 'Keles', 'Kestel', 'Mudanya', 'Mustafakemalpaşa', 'Nilüfer', 'Orhaneli', 'Orhangazi', 'Osmangazi', 'Yenişehir', 'Yıldırım']],
        17 => ['name' => 'Çanakkale', 'districts' => ['Ayvacık', 'Bayramiç', 'Biga', 'Bozcaada', 'Çan', 'Eceabat', 'Ezine', 'Gelibolu', 'Gökçeada', 'Lapseki', 'Merkez', 'Yenice']],
        18 => ['name' => 'Çankırı', 'districts' => ['Atkaracalar', 'Bayramören', 'Çerkeş', 'Eldivan', 'Ilgaz', 'Kızılırmak', 'Korgun', 'Kurşunlu', 'Merkez', 'Orta', 'Şabanözü', 'Yapraklı']],
        19 => ['name' => 'Çorum', 'districts' => ['Alaca', 'Bayat', 'Boğazkale', 'Dodurga', 'İskilip', 'Kargı', 'Laçin', 'Mecitözü', 'Merkez', 'Oğuzlar', 'Ortaköy', 'Osmancık', 'Sungurlu', 'Uğurludağ']],
        20 => ['name' => 'Denizli', 'districts' => ['Acıpayam', 'Babadağ', 'Baklan', 'Bekilli', 'Beyağaç', 'Bozkurt', 'Buldan', 'Çal', 'Çameli', 'Çardak', 'Çivril', 'Güney', 'Honaz', 'Kale', 'Merkezefendi', 'Pamukkale', 'Sarayköy', 'Serinhisar', 'Tavas']],
        21 => ['name' => 'Diyarbakır', 'districts' => ['Bağlar', 'Bismil', 'Çermik', 'Çınar', 'Çüngüş', 'Dicle', 'Eğil', 'Ergani', 'Hani', 'Hazro', 'Kayapınar', 'Kocaköy', 'Kulp', 'Lice', 'Silvan', 'Sur', 'Yenişehir']],
        22 => ['name' => 'Edirne', 'districts' => ['Enez', 'Havsa', 'İpsala', 'Keşan', 'Lalapaşa', 'Meriç', 'Merkez', 'Süloğlu', 'Uzunköprü']],
        23 => ['name' => 'Elazığ', 'districts' => ['Ağın', 'Alacakaya', 'Arıcak', 'Baskil', 'Karakoçan', 'Keban', 'Kovancılar', 'Maden', 'Merkez', 'Palu', 'Sivrice']],
        24 => ['name' => 'Erzincan', 'districts' => ['Çayırlı', 'İliç', 'Kemah', 'Kemaliye', 'Merkez', 'Otlukbeli', 'Refahiye', 'Tercan', 'Üzümlü']],
        25 => ['name' => 'Erzurum', 'districts' => ['Aşkale', 'Aziziye', 'Çat', 'Hınıs', 'Horasan', 'İspir', 'Karaçoban', 'Karayazı', 'Köprüköy', 'Narman', 'Oltu', 'Olur', 'Palandöken', 'Pasinler', 'Pazaryolu', 'Şenkaya', 'Tekman', 'Tortum', 'Uzundere', 'Yakutiye']],
        26 => ['name' => 'Eskişehir', 'districts' => ['Alpu', 'Beylikova', 'Çifteler', 'Günyüzü', 'Han', 'İnönü', 'Mahmudiye', 'Mihalgazi', 'Mihalıççık', 'Odunpazarı', 'Sarıcakaya', 'Seyitgazi', 'Sivrihisar', 'Tepebaşı']],
        27 => ['name' => 'Gaziantep', 'districts' => ['Araban', 'İslahiye', 'Karkamış', 'Nizip', 'Nurdağı', 'Oğuzeli', 'Şahinbey', 'Şehitkamil', 'Yavuzeli']],
        28 => ['name' => 'Giresun', 'districts' => ['Alucra', 'Bulancak', 'Çamoluk', 'Çanakçı', 'Dereli', 'Doğankent', 'Espiye', 'Eynesil', 'Görele', 'Güce', 'Keşap', 'Merkez', 'Piraziz', 'Şebinkarahisar', 'Tirebolu', 'Yağlıdere']],
        29 => ['name' => 'Gümüşhane', 'districts' => ['Kelkit', 'Köse', 'Kürtün', 'Merkez', 'Şiran', 'Torul']],
        30 => ['name' => 'Hakkari', 'districts' => ['Çukurca', 'Derecik', 'Merkez', 'Şemdinli', 'Yüksekova']],
        31 => ['name' => 'Hatay', 'districts' => ['Altınözü', 'Antakya', 'Arsuz', 'Belen', 'Defne', 'Dörtyol', 'Erzin', 'Hassa', 'İskenderun', 'Kırıkhan', 'Kumlu', 'Payas', 'Reyhanlı', 'Samandağ', 'Yayladağı']],
        32 => ['name' => 'Isparta', 'districts' => ['Aksu', 'Atabey', 'Eğirdir', 'Gelendost', 'Gönen', 'Keçiborlu', 'Merkez', 'Senirkent', 'Sütçüler', 'Şarkikaraağaç', 'Uluborlu', 'Yalvaç', 'Yenişarbademli']],
        33 => ['name' => 'Mersin', 'districts' => ['Akdeniz', 'Anamur', 'Aydıncık', 'Bozyazı', 'Çamlıyayla', 'Erdemli', 'Gülnar', 'Mezitli', 'Mut', 'Silifke', 'Tarsus', 'Toroslar', 'Yenişehir']],
        34 => ['name' => 'İstanbul', 'districts' => ['Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 'Bahçelievler', 'Bakırköy', 'Başakşehir', 'Bayrampaşa', 'Beşiktaş', 'Beykoz', 'Beylikdüzü', 'Beyoğlu', 'Büyükçekmece', 'Çatalca', 'Çekmeköy', 'Esenler', 'Esenyurt', 'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kağıthane', 'Kartal', 'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer', 'Silivri', 'Sultanbeyli', 'Sultangazi', 'Şile', 'Şişli', 'Tuzla', 'Ümraniye', 'Üsküdar', 'Zeytinburnu']],
        35 => ['name' => 'İzmir', 'districts' => ['Aliağa', 'Balçova', 'Bayındır', 'Bayraklı', 'Bergama', 'Beydağ', 'Bornova', 'Buca', 'Çeşme', 'Çiğli', 'Dikili', 'Foça', 'Gaziemir', 'Güzelbahçe', 'Karabağlar', 'Karaburun', 'Karşıyaka', 'Kemalpaşa', 'Kınık', 'Kiraz', 'Konak', 'Menderes', 'Menemen', 'Narlıdere', 'Ödemiş', 'Seferihisar', 'Selçuk', 'Tire', 'Torbalı', 'Urla']],
        36 => ['name' => 'Kars', 'districts' => ['Akyaka', 'Arpaçay', 'Digor', 'Kağızman', 'Merkez', 'Sarıkamış', 'Selim', 'Susuz']],
        37 => ['name' => 'Kastamonu', 'districts' => ['Abana', 'Ağlı', 'Araç', 'Bozkurt', 'Cide', 'Çatalzeytin', 'Daday', 'Devrekani', 'Doğanyurt', 'Hanönü', 'İhsangazi', 'İnebolu', 'Küre', 'Merkez', 'Pınarbaşı', 'Seydiler', 'Şenpazar', 'Taşköprü', 'Tosya']],
        38 => ['name' => 'Kayseri', 'districts' => ['Akkışla', 'Bünyan', 'Develi', 'Felahiye', 'Hacılar', 'İncesu', 'Kocasinan', 'Melikgazi', 'Özvatan', 'Pınarbaşı', 'Sarıoğlan', 'Sarız', 'Talas', 'Tomarza', 'Yahyalı', 'Yeşilhisar']],
        39 => ['name' => 'Kırklareli', 'districts' => ['Babaeski', 'Demirköy', 'Kofçaz', 'Lüleburgaz', 'Merkez', 'Pehlivanköy', 'Pınarhisar', 'Vize']],
        40 => ['name' => 'Kırşehir', 'districts' => ['Akçakent', 'Akpınar', 'Boztepe', 'Çiçekdağı', 'Kaman', 'Merkez', 'Mucur']],
        41 => ['name' => 'Kocaeli', 'districts' => ['Başiskele', 'Çayırova', 'Darıca', 'Derince', 'Dilovası', 'Gebze', 'Gölcük', 'İzmit', 'Kandıra', 'Karamürsel', 'Kartepe', 'Körfez']],
        42 => ['name' => 'Konya', 'districts' => ['Ahırlı', 'Akören', 'Akşehir', 'Altınekin', 'Beyşehir', 'Bozkır', 'Cihanbeyli', 'Çeltik', 'Çumra', 'Derbent', 'Derebucak', 'Doğanhisar', 'Emirgazi', 'Ereğli', 'Güneysınır', 'Hadim', 'Halkapınar', 'Hüyük', 'Ilgın', 'Kadınhanı', 'Karapınar', 'Karatay', 'Kulu', 'Meram', 'Sarayönü', 'Selçuklu', 'Seydişehir', 'Taşkent', 'Tuzlukçu', 'Yalıhüyük', 'Yunak']],
        43 => ['name' => 'Kütahya', 'districts' => ['Altıntaş', 'Aslanapa', 'Çavdarhisar', 'Domaniç', 'Dumlupınar', 'Emet', 'Gediz', 'Hisarcık', 'Merkez', 'Pazarlar', 'Şaphane', 'Simav', 'Tavşanlı']],
        44 => ['name' => 'Malatya', 'districts' => ['Akçadağ', 'Arapgir', 'Arguvan', 'Battalgazi', 'Darende', 'Doğanşehir', 'Doğanyol', 'Hekimhan', 'Kale', 'Kuluncak', 'Pütürge', 'Yazıhan', 'Yeşilyurt']],
        45 => ['name' => 'Manisa', 'districts' => ['Ahmetli', 'Akhisar', 'Alaşehir', 'Demirci', 'Gölmarmara', 'Gördes', 'Kırkağaç', 'Köprübaşı', 'Kula', 'Salihli', 'Sarıgöl', 'Saruhanlı', 'Selendi', 'Soma', 'Şehzadeler', 'Turgutlu', 'Yunusemre']],
        46 => ['name' => 'Kahramanmaraş', 'districts' => ['Afşin', 'Andırın', 'Çağlayancerit', 'Dulkadiroğlu', 'Ekinözü', 'Elbistan', 'Göksun', 'Nurhak', 'Onikişubat', 'Pazarcık', 'Türkoğlu']],
        47 => ['name' => 'Mardin', 'districts' => ['Artuklu', 'Dargeçit', 'Derik', 'Kızıltepe', 'Mazıdağı', 'Midyat', 'Nusaybin', 'Ömerli', 'Savur', 'Yeşilli']],
        48 => ['name' => 'Muğla', 'districts' => ['Bodrum', 'Dalaman', 'Datça', 'Fethiye', 'Kavaklıdere', 'Köyceğiz', 'Marmaris', 'Menteşe', 'Milas', 'Ortaca', 'Seydikemer', 'Ula', 'Yatağan']],
        49 => ['name' => 'Muş', 'districts' => ['Bulanık', 'Hasköy', 'Korkut', 'Malazgirt', 'Merkez', 'Varto']],
        50 => ['name' => 'Nevşehir', 'districts' => ['Acıgöl', 'Avanos', 'Derinkuyu', 'Gülşehir', 'Hacıbektaş', 'Kozaklı', 'Merkez', 'Ürgüp']],
        51 => ['name' => 'Niğde', 'districts' => ['Altunhisar', 'Bor', 'Çamardı', 'Çiftlik', 'Merkez', 'Ulukışla']],
        52 => ['name' => 'Ordu', 'districts' => ['Akkuş', 'Altınordu', 'Aybastı', 'Çamaş', 'Çatalpınar', 'Çaybaşı', 'Fatsa', 'Gölköy', 'Gülyalı', 'Gürgentepe', 'İkizce', 'Kabadüz', 'Kabataş', 'Korgan', 'Kumru', 'Mesudiye', 'Perşembe', 'Ulubey', 'Ünye']],
        53 => ['name' => 'Rize', 'districts' => ['Ardeşen', 'Çamlıhemşin', 'Çayeli', 'Derepazarı', 'Fındıklı', 'Güneysu', 'Hemşin', 'İkizdere', 'İyidere', 'Kalkandere', 'Merkez', 'Pazar']],
        54 => ['name' => 'Sakarya', 'districts' => ['Akyazı', 'Arifiye', 'Erenler', 'Ferizli', 'Geyve', 'Hendek', 'Karapürçek', 'Karasu', 'Kaynarca', 'Kocaali', 'Pamukova', 'Sapanca', 'Serdivan', 'Söğütlü', 'Taraklı']],
        55 => ['name' => 'Samsun', 'districts' => ['19 Mayıs', 'Alaçam', 'Asarcık', 'Atakum', 'Ayvacık', 'Bafra', 'Canik', 'Çarşamba', 'Havza', 'İlkadım', 'Kavak', 'Ladik', 'Salıpazarı', 'Tekkeköy', 'Terme', 'Vezirköprü', 'Yakakent']],
        56 => ['name' => 'Siirt', 'districts' => ['Baykan', 'Eruh', 'Kurtalan', 'Merkez', 'Pervari', 'Şirvan', 'Tillo']],
        57 => ['name' => 'Sinop', 'districts' => ['Ayancık', 'Boyabat', 'Dikmen', 'Durağan', 'Erfelek', 'Gerze', 'Merkez', 'Saraydüzü', 'Türkeli']],
        58 => ['name' => 'Sivas', 'districts' => ['Akıncılar', 'Altınyayla', 'Divriği', 'Doğanşar', 'Gemerek', 'Gölova', 'Gürün', 'Hafik', 'İmranlı', 'Kangal', 'Koyulhisar', 'Merkez', 'Suşehri', 'Şarkışla', 'Ulaş', 'Yıldızeli', 'Zara']],
        59 => ['name' => 'Tekirdağ', 'districts' => ['Çerkezköy', 'Çorlu', 'Ergene', 'Hayrabolu', 'Kapaklı', 'Malkara', 'Marmaraereğlisi', 'Muratlı', 'Saray', 'Süleymanpaşa', 'Şarköy']],
        60 => ['name' => 'Tokat', 'districts' => ['Almus', 'Artova', 'Başçiftlik', 'Erbaa', 'Merkez', 'Niksar', 'Pazar', 'Reşadiye', 'Sulusaray', 'Turhal', 'Yeşilyurt', 'Zile']],
        61 => ['name' => 'Trabzon', 'districts' => ['Akçaabat', 'Araklı', 'Arsin', 'Beşikdüzü', 'Çarşıbaşı', 'Çaykara', 'Dernekpazarı', 'Düzköy', 'Hayrat', 'Köprübaşı', 'Maçka', 'Of', 'Ortahisar', 'Sürmene', 'Şalpazarı', 'Tonya', 'Vakfıkebir', 'Yomra']],
        62 => ['name' => 'Tunceli', 'districts' => ['Çemişgezek', 'Hozat', 'Mazgirt', 'Merkez', 'Nazımiye', 'Ovacık', 'Pertek', 'Pülümür']],
        63 => ['name' => 'Şanlıurfa', 'districts' => ['Akçakale', 'Birecik', 'Bozova', 'Ceylanpınar', 'Eyyübiye', 'Halfeti', 'Haliliye', 'Harran', 'Hilvan', 'Karaköprü', 'Siverek', 'Suruç', 'Viranşehir']],
        64 => ['name' => 'Uşak', 'districts' => ['Banaz', 'Eşme', 'Karahallı', 'Merkez', 'Sivaslı', 'Ulubey']],
        65 => ['name' => 'Van', 'districts' => ['Bahçesaray', 'Başkale', 'Çaldıran', 'Çatak', 'Edremit', 'Erciş', 'Gevaş', 'Gürpınar', 'İpekyolu', 'Muradiye', 'Özalp', 'Saray', 'Tuşba']],
        66 => ['name' => 'Yozgat', 'districts' => ['Akdağmadeni', 'Aydıncık', 'Boğazlıyan', 'Çandır', 'Çayıralan', 'Çekerek', 'Kadışehri', 'Saraykent', 'Sarıkaya', 'Sorgun', 'Şefaatli', 'Yenifakılı', 'Yerköy']],
        67 => ['name' => 'Zonguldak', 'districts' => ['Alaplı', 'Çaycuma', 'Devrek', 'Gökçebey', 'Karadeniz Ereğli', 'Kilimli', 'Kozlu', 'Merkez']],
        68 => ['name' => 'Aksaray', 'districts' => ['Ağaçören', 'Eskil', 'Gülağaç', 'Güzelyurt', 'Merkez', 'Ortaköy', 'Sarıyahşi', 'Sultanhanı']],
        69 => ['name' => 'Bayburt', 'districts' => ['Aydıntepe', 'Demirözü', 'Merkez']],
        70 => ['name' => 'Karaman', 'districts' => ['Ayrancı', 'Başyayla', 'Ermenek', 'Kazımkarabekir', 'Merkez', 'Sarıveliler']],
        71 => ['name' => 'Kırıkkale', 'districts' => ['Bahşılı', 'Balışeyh', 'Çelebi', 'Delice', 'Karakeçili', 'Keskin', 'Merkez', 'Sulakyurt', 'Yahşihan']],
        72 => ['name' => 'Batman', 'districts' => ['Beşiri', 'Gercüş', 'Hasankeyf', 'Kozluk', 'Merkez', 'Sason']],
        73 => ['name' => 'Şırnak', 'districts' => ['Beytüşşebap', 'Cizre', 'Güçlükonak', 'İdil', 'Merkez', 'Silopi', 'Uludere']],
        74 => ['name' => 'Bartın', 'districts' => ['Amasra', 'Kurucaşile', 'Merkez', 'Ulus']],
        75 => ['name' => 'Ardahan', 'districts' => ['Çıldır', 'Damal', 'Göle', 'Hanak', 'Merkez', 'Posof']],
        76 => ['name' => 'Iğdır', 'districts' => ['Aralık', 'Karakoyunlu', 'Merkez', 'Tuzluca']],
        77 => ['name' => 'Yalova', 'districts' => ['Altınova', 'Armutlu', 'Çınarcık', 'Çiftlikköy', 'Merkez', 'Termal']],
        78 => ['name' => 'Karabük', 'districts' => ['Eflani', 'Eskipazar', 'Merkez', 'Ovacık', 'Safranbolu', 'Yenice']],
        79 => ['name' => 'Kilis', 'districts' => ['Elbeyli', 'Merkez', 'Musabeyli', 'Polateli']],
        80 => ['name' => 'Osmaniye', 'districts' => ['Bahçe', 'Düziçi', 'Hasanbeyli', 'Kadirli', 'Merkez', 'Sumbas', 'Toprakkale']],
        81 => ['name' => 'Düzce', 'districts' => ['Akçakoca', 'Cumayeri', 'Çilimli', 'Gölyaka', 'Gümüşova', 'Kaynaşlı', 'Merkez', 'Yığılca']],
    ];

    /**
     * Tüm 81 ili ve ilçelerini dizi formatında döndürür
     */
    public static function all(): array
    {
        $list = [];
        foreach (self::$ilIlceHaritasi as $id => $info) {
            $list[] = [
                'id' => $id,
                'name' => $info['name'],
                'slug' => self::slugify($info['name']),
                'districts_count' => count($info['districts']),
                'districts' => $info['districts'],
            ];
        }
        return $list;
    }

    /**
     * Tüm illeri SehirDto nesneleri dizisi olarak döndürür
     *
     * @return array<int, SehirDto>
     */
    public static function allDtos(): array
    {
        $list = [];
        foreach (self::$ilIlceHaritasi as $id => $info) {
            $list[] = new SehirDto(
                id: $id,
                name: $info['name'],
                slug: self::slugify($info['name']),
                districts: $info['districts']
            );
        }
        return $list;
    }

    /**
     * İl adından, plaka kodundan veya slug'dan şehri ve ilçelerini bulur
     */
    public static function find(string|int $query): ?array
    {
        if (is_numeric($query)) {
            $id = (int) $query;
            if (isset(self::$ilIlceHaritasi[$id])) {
                $info = self::$ilIlceHaritasi[$id];
                return [
                    'id' => $id,
                    'name' => $info['name'],
                    'slug' => self::slugify($info['name']),
                    'districts' => $info['districts'],
                ];
            }
            return null;
        }

        $query = trim((string) $query);
        $normalizedQuery = self::normalize($query);
        $slugQuery = self::slugify($query);

        foreach (self::$ilIlceHaritasi as $id => $info) {
            if (self::normalize($info['name']) === $normalizedQuery || self::slugify($info['name']) === $slugQuery) {
                return [
                    'id' => $id,
                    'name' => $info['name'],
                    'slug' => self::slugify($info['name']),
                    'districts' => $info['districts'],
                ];
            }
        }

        return null;
    }

    /**
     * Bir ilin tüm resmi ilçelerini dizi olarak döndürür
     *
     * @return array<int, string>
     */
    public static function getIlceler(string|int $sehir): array
    {
        $item = self::find($sehir);
        return $item ? $item['districts'] : [];
    }

    /**
     * Şehir adına karşılık gelen plaka kodunu döndürür
     */
    public static function getPlakaKodu(string|int $sehir): ?int
    {
        $item = self::find($sehir);
        return $item ? (int) $item['id'] : null;
    }

    /**
     * Şehir adının resmi Türkçe yazılışını döndürür
     */
    public static function getResmiAd(string|int $sehir): ?string
    {
        $item = self::find($sehir);
        return $item ? (string) $item['name'] : null;
    }

    /**
     * Karşılaştırma için metin normalizasyonu (küçük harf, Türkçe karakter dönüştürme)
     */
    public static function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        return self::toAscii($text);
    }

    /**
     * Türkçe karakterleri ASCII karşılıklarına dönüştürür
     */
    public static function toAscii(string $text): string
    {
        return strtr($text, [
            'ı' => 'i', 'İ' => 'i', 'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u', 'ş' => 's', 'Ş' => 's',
            'ö' => 'o', 'Ö' => 'o', 'ç' => 'c', 'Ç' => 'c',
        ]);
    }

    /**
     * Slug üretir
     */
    public static function slugify(string $text): string
    {
        $text = self::toAscii($text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', (string) $text);
        return trim((string) $text, '-');
    }
}
