using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace EczaneApi.Models
{
    public class IlModel
    {
        [JsonPropertyName("il")]
        public string Il { get; set; } = string.Empty;

        [JsonPropertyName("plaka")]
        public int? Plaka { get; set; }

        [JsonPropertyName("ilceler")]
        public List<string> Ilceler { get; set; } = new List<string>();
    }
}
