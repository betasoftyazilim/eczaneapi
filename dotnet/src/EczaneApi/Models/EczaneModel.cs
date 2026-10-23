using System.Text.Json.Serialization;

namespace EczaneApi.Models
{
    public class EczaneModel
    {
        [JsonPropertyName("adi")]
        public string Adi { get; set; } = string.Empty;

        [JsonPropertyName("il")]
        public string Il { get; set; } = string.Empty;

        [JsonPropertyName("ilce")]
        public string Ilce { get; set; } = string.Empty;

        [JsonPropertyName("telefon")]
        public string? Telefon { get; set; }

        [JsonPropertyName("adres")]
        public string? Adres { get; set; }

        [JsonPropertyName("tarif")]
        public string? Tarif { get; set; }

        [JsonPropertyName("nobet_baslangic")]
        public string? NobetBaslangic { get; set; }

        [JsonPropertyName("nobet_bitis")]
        public string? NobetBitis { get; set; }

        [JsonPropertyName("geo")]
        public GeoLocation? Geo { get; set; }

        [JsonPropertyName("kaynak")]
        public string? Kaynak { get; set; }

        [JsonPropertyName("mesafe_km")]
        public double? MesafeKm { get; set; }
    }
}
