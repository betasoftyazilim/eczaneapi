using System.Collections.Generic;
using System.Threading;
using System.Threading.Tasks;
using EczaneApi.Models;

namespace EczaneApi
{
    public static class Eczane
    {
        private static EczaneClient _defaultClient = new EczaneClient();

        public static void Configure(string? baseUrl = null, string? apiKey = null)
        {
            _defaultClient.Dispose();
            _defaultClient = new EczaneClient(baseUrl, apiKey);
        }

        public static Task<List<EczaneModel>> NobetciAsync(string il, string? ilce = null, string? tarih = null, CancellationToken cancellationToken = default)
        {
            return _defaultClient.NobetciAsync(il, ilce, tarih, cancellationToken);
        }

        public static Task<List<IlModel>> IllerAsync(CancellationToken cancellationToken = default)
        {
            return _defaultClient.IllerAsync(cancellationToken);
        }

        public static Task<List<string>> IlcelerAsync(string il, CancellationToken cancellationToken = default)
        {
            return _defaultClient.IlcelerAsync(il, cancellationToken);
        }

        public static Task<List<EczaneModel>> YakinAsync(double latitude, double longitude, double mesafeKm = 5.0, CancellationToken cancellationToken = default)
        {
            return _defaultClient.YakinAsync(latitude, longitude, mesafeKm, cancellationToken);
        }
    }
}
