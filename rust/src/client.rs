use crate::models::{EczaneModel, IlModel};
use reqwest::header::{HeaderMap, HeaderValue, ACCEPT, USER_AGENT};
use serde::Deserialize;
use std::time::Duration;

pub const DEFAULT_BASE_URL: &str = "https://eczaneapi.vercel.app";

#[derive(Clone)]
pub struct EczaneClient {
    base_url: String,
    api_key: Option<String>,
    client: reqwest::Client,
}

#[derive(Deserialize)]
struct ApiWrapper<T> {
    #[allow(dead_code)]
    success: Option<bool>,
    data: Option<T>,
}

impl EczaneClient {
    pub fn new(base_url: Option<&str>, api_key: Option<&str>) -> Self {
        let base = base_url.unwrap_or(DEFAULT_BASE_URL).trim_end_matches('/');

        let mut headers = HeaderMap::new();
        headers.insert(ACCEPT, HeaderValue::from_static("application/json"));
        headers.insert(
            USER_AGENT,
            HeaderValue::from_static("EczaneAPI-Rust-SDK/1.1.1 (www.betasoft.com.tr)"),
        );

        let client = reqwest::Client::builder()
            .default_headers(headers)
            .timeout(Duration::from_secs(15))
            .build()
            .unwrap_or_default();

        Self {
            base_url: base.to_string(),
            api_key: api_key.map(|s| s.to_string()),
            client,
        }
    }

    pub async fn nobetci(
        &self,
        il: &str,
        ilce: Option<&str>,
        tarih: Option<&str>,
    ) -> Result<Vec<EczaneModel>, Box<dyn std::error::Error + Send + Sync>> {
        let clean_il = urlencoding::encode(il.trim().to_lowercase().as_str());
        let mut endpoint = format!("{}/api/nobetci/{}", self.base_url, clean_il);

        if let Some(district) = ilce {
            let clean_ilce = urlencoding::encode(district.trim().to_lowercase().as_str());
            endpoint = format!("{}/{}", endpoint, clean_ilce);
        }

        let mut req = self.client.get(&endpoint);
        if let Some(key) = &self.api_key {
            req = req.bearer_auth(key);
        }

        if let Some(date_str) = tarih {
            req = req.query(&[("tarih", date_str)]);
        }

        let res = req.send().await?;
        if !res.status().is_success() {
            return Err(format!("API Hatasi: HTTP {}", res.status()).into());
        }

        let body = res.text().await?;
        if let Ok(wrapper) = serde_json::from_str::<ApiWrapper<Vec<EczaneModel>>>(&body) {
            if let Some(data) = wrapper.data {
                return Ok(data);
            }
        }

        let items: Vec<EczaneModel> = serde_json::from_str(&body)?;
        Ok(items)
    }

    pub async fn iller(&self) -> Result<Vec<IlModel>, Box<dyn std::error::Error + Send + Sync>> {
        let url = format!("{}/api/iller", self.base_url);
        let mut req = self.client.get(&url);
        if let Some(key) = &self.api_key {
            req = req.bearer_auth(key);
        }

        let res = req.send().await?;
        let body = res.text().await?;
        if let Ok(wrapper) = serde_json::from_str::<ApiWrapper<Vec<IlModel>>>(&body) {
            if let Some(data) = wrapper.data {
                return Ok(data);
            }
        }

        let items: Vec<IlModel> = serde_json::from_str(&body)?;
        Ok(items)
    }

    pub async fn yakin(
        &self,
        lat: f64,
        lng: f64,
        mesafe_km: f64,
    ) -> Result<Vec<EczaneModel>, Box<dyn std::error::Error + Send + Sync>> {
        let url = format!("{}/api/yakin", self.base_url);
        let mut req = self.client.get(&url).query(&[
            ("lat", lat.to_string()),
            ("lng", lng.to_string()),
            ("mesafe", mesafe_km.to_string()),
        ]);

        if let Some(key) = &self.api_key {
            req = req.bearer_auth(key);
        }

        let res = req.send().await?;
        let body = res.text().await?;
        if let Ok(wrapper) = serde_json::from_str::<ApiWrapper<Vec<EczaneModel>>>(&body) {
            if let Some(data) = wrapper.data {
                return Ok(data);
            }
        }

        let items: Vec<EczaneModel> = serde_json::from_str(&body)?;
        Ok(items)
    }
}
