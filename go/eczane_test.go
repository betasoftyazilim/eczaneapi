package eczaneapi

import (
	"encoding/json"
	"testing"
)

func TestModelDeserialization(t *testing.T) {
	raw := `{
		"adi": "Gunes Eczanesi",
		"il": "Istanbul",
		"ilce": "Kadikoy",
		"telefon": "0216 111 22 33",
		"geo": {
			"latitude": 40.98,
			"longitude": 29.02
		}
	}`

	var item EczaneModel
	if err := json.Unmarshal([]byte(raw), &item); err != nil {
		t.Fatalf("Model json unmarshal basarisiz: %v", err)
	}

	if item.Adi != "Gunes Eczanesi" {
		t.Errorf("Beklenen: Gunes Eczanesi, Alinan: %s", item.Adi)
	}

	if item.Geo == nil || *item.Geo.Latitude != 40.98 {
		t.Errorf("Latitude degeri hatali")
	}
}

func TestClientInit(t *testing.T) {
	client := NewClient("https://mock.local", "test-key")
	if client.BaseURL != "https://mock.local" {
		t.Errorf("BaseURL hatali: %s", client.BaseURL)
	}
}
