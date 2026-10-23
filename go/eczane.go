package eczaneapi

var defaultClient = NewClient("", "")

func Configure(baseURL string, apiKey string) {
	defaultClient = NewClient(baseURL, apiKey)
}

func Nobetci(il string, ilce string, tarih string) ([]EczaneModel, error) {
	return defaultClient.Nobetci(il, ilce, tarih)
}

func Iller() ([]IlModel, error) {
	return defaultClient.Iller()
}

func Ilceler(il string) ([]string, error) {
	return defaultClient.Ilceler(il)
}

func Yakin(lat float64, lng float64, mesafeKm float64) ([]EczaneModel, error) {
	return defaultClient.Yakin(lat, lng, mesafeKm)
}
