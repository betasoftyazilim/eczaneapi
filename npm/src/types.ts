export interface GeoLocation {
  latitude?: number;
  longitude?: number;
  [key: string]: any;
}

export interface EczaneItem {
  adi: string;
  il: string;
  ilce: string;
  telefon?: string;
  adres?: string;
  tarif?: string;
  nobet_baslangic?: string;
  nobet_bitis?: string;
  geo?: GeoLocation;
  kaynak?: string;
  mesafe_km?: number;
  [key: string]: any;
}

export interface IlItem {
  il: string;
  plaka?: number;
  ilceler: string[];
}

export interface EczaneClientOptions {
  baseUrl?: string;
  apiKey?: string;
  timeout?: number;
}
