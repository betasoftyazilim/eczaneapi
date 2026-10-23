using System.Text.Json;
using EczaneApi;
using EczaneApi.Models;
using Xunit;

namespace EczaneApi.Tests
{
    public class EczaneApiTests
    {
        [Fact]
        public void TestModelDeserialization()
        {
            var json = """
            {
                "adi": "Bahar Eczanesi",
                "il": "Ankara",
                "ilce": "Çankaya",
                "telefon": "0312 000 00 00",
                "adres": "Tunalı Hilmi Cad.",
                "geo": {
                    "latitude": 39.90,
                    "longitude": 32.85
                },
                "kaynak": "titck_edevlet"
            }
            """;

            var model = JsonSerializer.Deserialize<EczaneModel>(json);
            Assert.NotNull(model);
            Assert.Equal("Bahar Eczanesi", model.Adi);
            Assert.Equal("Ankara", model.Il);
            Assert.Equal("Çankaya", model.Ilce);
            Assert.NotNull(model.Geo);
            Assert.Equal(39.90, model.Geo.Latitude);
            Assert.Equal(32.85, model.Geo.Longitude);
        }

        [Fact]
        public void TestClientInstanceCreation()
        {
            using var client = new EczaneClient();
            Assert.NotNull(client);
        }
    }
}
