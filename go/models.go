package eczaneapi

type GeoLocation struct {
	Latitude  *float64 `json:"latitude,omitempty"`
	Longitude *float64 `json:"longitude,omitempty"`
}

type EczaneModel struct {
	Adi            string       `json:"adi"`
	Il             string       `json:"il"`
	Ilce           string       `json:"ilce"`
	Telefon        *string      `json:"telefon,omitempty"`
	Adres          *string      `json:"adres,omitempty"`
	Tarif          *string      `json:"tarif,omitempty"`
	NobetBaslangic *string      `json:"nobet_baslangic,omitempty"`
	NobetBitis     *string      `json:"nobet_bitis,omitempty"`
	Geo            *GeoLocation `json:"geo,omitempty"`
	Kaynak         *string      `json:"kaynak,omitempty"`
	MesafeKm       *float64     `json:"mesafe_km,omitempty"`
}

type IlModel struct {
	Il      string   `json:"il"`
	Plaka   *int     `json:"plaka,omitempty"`
	Ilceler []string `json:"ilceler"`
}
