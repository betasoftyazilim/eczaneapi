library eczaneapi;

import 'src/client.dart';
import 'src/models.dart';

export 'src/client.dart';
export 'src/models.dart';

class Eczane {
  static EczaneClient _defaultClient = EczaneClient();

  static void configure({String? baseUrl, String? apiKey, Duration? timeout}) {
    _defaultClient = EczaneClient(
      baseUrl: baseUrl,
      apiKey: apiKey,
      timeout: timeout ?? const Duration(seconds: 15),
    );
  }

  static Future<List<EczaneModel>> nobetci(String il, [String? ilce, String? tarih]) {
    return _defaultClient.nobetci(il, ilce, tarih);
  }

  static Future<List<IlModel>> iller() {
    return _defaultClient.iller();
  }

  static Future<List<String>> ilceler(String il) {
    return _defaultClient.ilceler(il);
  }

  static Future<List<EczaneModel>> yakin(double latitude, double longitude, [double mesafeKm = 5.0]) {
    return _defaultClient.yakin(latitude, longitude, mesafeKm);
  }
}
