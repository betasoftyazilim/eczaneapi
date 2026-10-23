package eczaneapi

import (
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"strings"
	"time"
)

const DefaultBaseURL = "https://eczaneapi.vercel.app"

type Client struct {
	BaseURL    string
	APIKey     string
	HTTPClient *http.Client
}

func NewClient(baseURL string, apiKey string) *Client {
	if baseURL == "" {
		baseURL = DefaultBaseURL
	}
	return &Client{
		BaseURL: strings.TrimRight(baseURL, "/"),
		APIKey:  apiKey,
		HTTPClient: &http.Client{
			Timeout: 15 * time.Second,
		},
	}
}

func (c *Client) request(endpoint string, params url.Values, target interface{}) error {
	reqURL := fmt.Sprintf("%s/%s", c.BaseURL, strings.TrimLeft(endpoint, "/"))
	if len(params) > 0 {
		reqURL = fmt.Sprintf("%s?%s", reqURL, params.Encode())
	}

	req, err := http.NewRequest(http.MethodGet, reqURL, nil)
	if err != nil {
		return fmt.Errorf("istek olusturulamadi: %w", err)
	}

	req.Header.Set("Accept", "application/json")
	req.Header.Set("User-Agent", "EczaneAPI-Go-SDK/1.1.1 (www.betasoft.com.tr)")
	if c.APIKey != "" {
		req.Header.Set("Authorization", "Bearer "+c.APIKey)
	}

	resp, err := c.HTTPClient.Do(req)
	if err != nil {
		return fmt.Errorf("baglanti hatasi: %w", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return fmt.Errorf("yanit okunamadi: %w", err)
	}

	if resp.StatusCode >= 400 {
		return fmt.Errorf("API hatasi [%d]: %s", resp.StatusCode, string(body))
	}

	// { "success": true, "data": [...] } sarmalayicisini cozelim
	var rawWrapper struct {
		Success bool            `json:"success"`
		Data    json.RawMessage `json:"data"`
	}

	if err := json.Unmarshal(body, &rawWrapper); err == nil && len(rawWrapper.Data) > 0 {
		return json.Unmarshal(rawWrapper.Data, target)
	}

	return json.Unmarshal(body, target)
}

func (c *Client) Nobetci(il string, ilce string, tarih string) ([]EczaneModel, error) {
	cleanIl := url.PathEscape(strings.ToLower(strings.TrimSpace(il)))
	endpoint := fmt.Sprintf("api/nobetci/%s", cleanIl)
	if ilce != "" {
		cleanIlce := url.PathEscape(strings.ToLower(strings.TrimSpace(ilce)))
		endpoint = fmt.Sprintf("%s/%s", endpoint, cleanIlce)
	}

	params := url.Values{}
	if tarih != "" {
		params.Set("tarih", tarih)
	}

	var eczaneler []EczaneModel
	if err := c.request(endpoint, params, &eczaneler); err != nil {
		return nil, err
	}
	return eczaneler, nil
}

func (c *Client) Iller() ([]IlModel, error) {
	var iller []IlModel
	if err := c.request("api/iller", nil, &iller); err != nil {
		return nil, err
	}
	return iller, nil
}

func (c *Client) Ilceler(il string) ([]string, error) {
	cleanIl := url.PathEscape(strings.ToLower(strings.TrimSpace(il)))
	var ilceler []string
	if err := c.request(fmt.Sprintf("api/ilceler/%s", cleanIl), nil, &ilceler); err != nil {
		return nil, err
	}
	return ilceler, nil
}

func (c *Client) Yakin(lat float64, lng float64, mesafeKm float64) ([]EczaneModel, error) {
	params := url.Values{}
	params.Set("lat", fmt.Sprintf("%f", lat))
	params.Set("lng", fmt.Sprintf("%f", lng))
	if mesafeKm <= 0 {
		mesafeKm = 5.0
	}
	params.Set("mesafe", fmt.Sprintf("%f", mesafeKm))

	var eczaneler []EczaneModel
	if err := c.request("api/yakin", params, &eczaneler); err != nil {
		return nil, err
	}
	return eczaneler, nil
}
