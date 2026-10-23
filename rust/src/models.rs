use serde::{Deserialize, Serialize};

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct GeoLocation {
    pub latitude: Option<f64>,
    pub longitude: Option<f64>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct EczaneModel {
    pub adi: String,
    pub il: String,
    pub ilce: String,
    pub telefon: Option<String>,
    pub adres: Option<String>,
    pub tarif: Option<String>,
    pub nobet_baslangic: Option<String>,
    pub nobet_bitis: Option<String>,
    pub geo: Option<GeoLocation>,
    pub kaynak: Option<String>,
    pub mesafe_km: Option<f64>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct IlModel {
    pub il: String,
    pub plaka: Option<i32>,
    pub ilceler: Vec<String>,
}
