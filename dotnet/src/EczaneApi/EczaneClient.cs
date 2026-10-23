using System;
using System.Collections.Generic;
using System.Net;
using System.Net.Http;
using System.Text.Json;
using System.Threading;
using System.Threading.Tasks;
using EczaneApi.Exceptions;
using EczaneApi.Models;

namespace EczaneApi
{
    public class EczaneClient : IDisposable
    {
        public const string DefaultBaseUrl = "https://eczaneapi.vercel.app";

        private readonly HttpClient _httpClient;
        private readonly bool _disposeClient;
        private readonly string _baseUrl;
        private readonly string? _apiKey;

        public EczaneClient(string? baseUrl = null, string? apiKey = null, HttpClient? httpClient = null)
        {
            _baseUrl = (baseUrl ?? DefaultBaseUrl).TrimEnd('/');
            _apiKey = apiKey;

            if (httpClient != null)
            {
                _httpClient = httpClient;
                _disposeClient = false;
            }
            else
            {
                _httpClient = new HttpClient { Timeout = TimeSpan.FromSeconds(15) };
                _disposeClient = true;
            }
        }

        private async Task<T> SendRequestAsync<T>(string endpoint, Dictionary<string, string?>? queryParams = null, CancellationToken cancellationToken = default)
        {
            var url = $"{_baseUrl}/{endpoint.TrimStart('/')}";

            if (queryParams != null && queryParams.Count > 0)
            {
                var queryList = new List<string>();
                foreach (var kvp in queryParams)
                {
                    if (!string.IsNullOrEmpty(kvp.Value))
                    {
                        queryList.Add($"{WebUtility.UrlEncode(kvp.Key)}={WebUtility.UrlEncode(kvp.Value)}");
                    }
                }
                if (queryList.Count > 0)
                {
                    url += "?" + string.Join("&", queryList);
                }
            }

            using var request = new HttpRequestMessage(HttpMethod.Get, url);
            request.Headers.Add("User-Agent", "EczaneAPI-DotNet-SDK/1.0.0");
            request.Headers.Add("Accept", "application/json");

            if (!string.IsNullOrEmpty(_apiKey))
            {
                request.Headers.Add("Authorization", $"Bearer {_apiKey}");
            }

            HttpResponseMessage response;
            try
            {
                response = await _httpClient.SendAsync(request, cancellationToken).ConfigureAwait(false);
            }
            catch (Exception ex) when (ex is not EczaneApiException)
            {
                throw new EczaneApiException($"Bağlantı hatası: {ex.Message}", null, ex);
            }

            var content = await response.Content.ReadAsStringAsync().ConfigureAwait(false);

            if (!response.IsSuccessStatusCode)
            {
                if (response.StatusCode == HttpStatusCode.NotFound)
                {
                    throw new EczaneNotFoundException($"Kayıt bulunamadı: {endpoint}");
                }
                throw new EczaneApiException($"İstek başarısız oldu: {response.StatusCode} - {content}", (int)response.StatusCode);
            }

            try
            {
                using var doc = JsonDocument.Parse(content);
                var root = doc.RootElement;

                // Yanıt { success: true, data: [...] } formatında ise data düğümünü al
                if (root.ValueKind == JsonValueKind.Object && root.TryGetProperty("data", out var dataElement))
                {
                    var options = new JsonSerializerOptions { PropertyNameCaseInsensitive = true };
                    return JsonSerializer.Deserialize<T>(dataElement.GetRawText(), options)
                           ?? throw new EczaneApiException("Veri boş döndü.");
                }

                var defaultOptions = new JsonSerializerOptions { PropertyNameCaseInsensitive = true };
                return JsonSerializer.Deserialize<T>(content, defaultOptions)
                       ?? throw new EczaneApiException("Veri boş döndü.");
            }
            catch (Exception ex) when (ex is not EczaneApiException)
            {
                throw new EczaneApiException($"JSON çözümleme hatası: {ex.Message}", (int)response.StatusCode, ex);
            }
        }

        public async Task<List<EczaneModel>> NobetciAsync(string il, string? ilce = null, string? tarih = null, CancellationToken cancellationToken = default)
        {
            if (string.IsNullOrWhiteSpace(il))
                throw new ArgumentException("İl adı boş olamaz.", nameof(il));

            var cleanIl = WebUtility.UrlEncode(il.Trim().ToLowerInvariant());
            var endpoint = $"api/nobetci/{cleanIl}";

            if (!string.IsNullOrWhiteSpace(ilce))
            {
                var cleanIlce = WebUtility.UrlEncode(ilce.Trim().ToLowerInvariant());
                endpoint += $"/{cleanIlce}";
            }

            var queryParams = new Dictionary<string, string?>();
            if (!string.IsNullOrWhiteSpace(tarih))
            {
                queryParams["tarih"] = tarih;
            }

            return await SendRequestAsync<List<EczaneModel>>(endpoint, queryParams, cancellationToken).ConfigureAwait(false);
        }

        public async Task<List<IlModel>> IllerAsync(CancellationToken cancellationToken = default)
        {
            return await SendRequestAsync<List<IlModel>>("api/iller", null, cancellationToken).ConfigureAwait(false);
        }

        public async Task<List<string>> IlcelerAsync(string il, CancellationToken cancellationToken = default)
        {
            if (string.IsNullOrWhiteSpace(il))
                throw new ArgumentException("İl adı boş olamaz.", nameof(il));

            var cleanIl = WebUtility.UrlEncode(il.Trim().ToLowerInvariant());
            return await SendRequestAsync<List<string>>($"api/ilceler/{cleanIl}", null, cancellationToken).ConfigureAwait(false);
        }

        public async Task<List<EczaneModel>> YakinAsync(double latitude, double longitude, double mesafeKm = 5.0, CancellationToken cancellationToken = default)
        {
            var queryParams = new Dictionary<string, string?>
            {
                ["lat"] = latitude.ToString(System.Globalization.CultureInfo.InvariantCulture),
                ["lng"] = longitude.ToString(System.Globalization.CultureInfo.InvariantCulture),
                ["mesafe"] = mesafeKm.ToString(System.Globalization.CultureInfo.InvariantCulture)
            };

            return await SendRequestAsync<List<EczaneModel>>("api/yakin", queryParams, cancellationToken).ConfigureAwait(false);
        }

        public void Dispose()
        {
            if (_disposeClient)
            {
                _httpClient.Dispose();
            }
        }
    }
}
