using System.Text.Json.Serialization;

namespace EczaneApi.Models
{
    public class GeoLocation
    {
        [JsonPropertyName("latitude")]
        public double? Latitude { get; set; }

        [JsonPropertyName("longitude")]
        public double? Longitude { get; set; }

        [JsonPropertyName("lat")]
        private double? AltLat { set => Latitude ??= value; }

        [JsonPropertyName("lng")]
        private double? AltLng { set => Longitude ??= value; }
    }
}
