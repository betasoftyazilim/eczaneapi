class GeoLocation {
  final double? latitude;
  final double? longitude;

  GeoLocation({this.latitude, this.longitude});

  factory GeoLocation.fromJson(Map<String, dynamic> json) {
    final lat = json['latitude'] ?? json['lat'];
    final lng = json['longitude'] ?? json['lng'];
    return GeoLocation(
      latitude: lat != null ? (lat as num).toDouble() : null,
      longitude: lng != null ? (lng as num).toDouble() : null,
    );
  }

  Map<String, dynamic> toJson() => {
    'latitude': latitude,
    'longitude': longitude,
  };
}

class EczaneModel {
  final String adi;
  final String il;
  final String ilce;
  final String? telefon;
  final String? adres;
  final String? tarif;
  final String? nobetBaslangic;
  final String? nobetBitis;
  final GeoLocation? geo;
  final String? kaynak;
  final double? mesafeKm;

  EczaneModel({
    required this.adi,
    required this.il,
    required this.ilce,
    this.telefon,
    this.adres,
    this.tarif,
    this.nobetBaslangic,
    this.nobetBitis,
    this.geo,
    this.kaynak,
    this.mesafeKm,
  });

  factory EczaneModel.fromJson(Map<String, dynamic> json) {
    GeoLocation? geoObj;
    if (json['geo'] != null && json['geo'] is Map<String, dynamic>) {
      geoObj = GeoLocation.fromJson(json['geo']);
    } else if (json['latitude'] != null || json['lat'] != null) {
      geoObj = GeoLocation.fromJson(json);
    }

    final mesafe = json['mesafe_km'] ?? json['mesafe'];

    return EczaneModel(
      adi: json['adi'] ?? json['name'] ?? '',
      il: json['il'] ?? json['city'] ?? '',
      ilce: json['ilce'] ?? json['district'] ?? '',
      telefon: json['telefon'] ?? json['phone'],
      adres: json['adres'] ?? json['address'],
      tarif: json['tarif'] ?? json['directions'],
      nobetBaslangic: json['nobet_baslangic'] ?? json['start'],
      nobetBitis: json['nobet_bitis'] ?? json['end'],
      geo: geoObj,
      kaynak: json['kaynak'] ?? json['source'],
      mesafeKm: mesafe != null ? (mesafe as num).toDouble() : null,
    );
  }
}

class IlModel {
  final String il;
  final int? plaka;
  final List<String> ilceler;

  IlModel({
    required this.il,
    this.plaka,
    required this.ilceler,
  });

  factory IlModel.fromJson(Map<String, dynamic> json) {
    final list = json['ilceler'] ?? json['districts'] ?? [];
    return IlModel(
      il: json['il'] ?? json['name'] ?? '',
      plaka: json['plaka'] ?? json['plate'],
      ilceler: List<String>.from(list),
    );
  }
}
