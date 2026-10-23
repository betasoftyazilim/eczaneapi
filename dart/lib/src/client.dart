import 'dart:convert';
import 'package:http/http.dart' as http;
import 'models.dart';

const String defaultBaseUrl = 'https://eczaneapi.vercel.app';

class EczaneClient {
  final String baseUrl;
  final String? apiKey;
  final Duration timeout;
  final http.Client _client;

  EczaneClient({
    String? baseUrl,
    this.apiKey,
    this.timeout = const Duration(seconds: 15),
    http.Client? httpClient,
  })  : baseUrl = (baseUrl ?? defaultBaseUrl).replaceAll(RegExp(r'/+$'), ''),
        _client = httpClient ?? http.Client();

  Future<dynamic> _get(String endpoint, [Map<String, String>? queryParams]) async {
    var uri = Uri.parse('$baseUrl/${endpoint.replaceAll(RegExp(r'^/+'), '')}');
    if (queryParams != null && queryParams.isNotEmpty) {
      uri = uri.replace(queryParameters: queryParams);
    }

    final headers = {
      'Accept': 'application/json',
      'User-Agent': 'EczaneAPI-Dart-SDK/1.1.1 (www.betasoft.com.tr)',
    };
    if (apiKey != null && apiKey!.isNotEmpty) {
      headers['Authorization'] = 'Bearer $apiKey';
    }

    final response = await _client.get(uri, headers: headers).timeout(timeout);

    if (response.statusCode >= 400) {
      throw Exception('API Hatası [${response.statusCode}]: ${response.body}');
    }

    final dynamic decoded = json.decode(utf8.decode(response.bodyBytes));
    if (decoded is Map<String, dynamic> && decoded.containsKey('data')) {
      return decoded['data'];
    }
    return decoded;
  }

  Future<List<EczaneModel>> nobetci(String il, [String? ilce, String? tarih]) async {
    final cleanIl = Uri.encodeComponent(il.trim().toLowerCase());
    var endpoint = 'api/nobetci/$cleanIl';
    if (ilce != null && ilce.trim().isNotEmpty) {
      final cleanIlce = Uri.encodeComponent(ilce.trim().toLowerCase());
      endpoint += '/$cleanIlce';
    }

    final params = <String, String>{};
    if (tarih != null && tarih.isNotEmpty) {
      params['tarih'] = tarih;
    }

    final data = await _get(endpoint, params.isEmpty ? null : params);
    if (data is List) {
      return data.map((e) => EczaneModel.fromJson(e as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<List<IlModel>> iller() async {
    final data = await _get('api/iller');
    if (data is List) {
      return data.map((e) => IlModel.fromJson(e as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<List<String>> ilceler(String il) async {
    final cleanIl = Uri.encodeComponent(il.trim().toLowerCase());
    final data = await _get('api/ilceler/$cleanIl');
    if (data is List) {
      return List<String>.from(data);
    }
    return [];
  }

  Future<List<EczaneModel>> yakin(double latitude, double longitude, [double mesafeKm = 5.0]) async {
    final params = {
      'lat': latitude.toString(),
      'lng': longitude.toString(),
      'mesafe': mesafeKm.toString(),
    };

    final data = await _get('api/yakin', params);
    if (data is List) {
      return data.map((e) => EczaneModel.fromJson(e as Map<String, dynamic>)).toList();
    }
    return [];
  }

  void close() {
    _client.close();
  }
}
