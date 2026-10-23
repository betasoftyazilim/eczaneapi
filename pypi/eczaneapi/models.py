"""
Eczane API Veri Modelleri
"""

from __future__ import annotations
from dataclasses import dataclass, field
from typing import Optional, List, Dict, Any


@dataclass
class GeoModel:
    """Coğrafi koordinat bilgisi."""
    latitude: Optional[float] = None
    longitude: Optional[float] = None

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> GeoModel:
        if not data:
            return cls()
        lat = data.get("latitude") or data.get("lat")
        lng = data.get("longitude") or data.get("lng")
        return cls(
            latitude=float(lat) if lat is not None else None,
            longitude=float(lng) if lng is not None else None,
        )


@dataclass
class EczaneModel:
    """Nöbetçi Eczane Veri Modeli."""
    adi: str
    il: str
    ilce: str
    telefon: Optional[str] = None
    adres: Optional[str] = None
    tarif: Optional[str] = None
    nobet_baslangic: Optional[str] = None
    nobet_bitis: Optional[str] = None
    geo: Optional[GeoModel] = None
    kaynak: Optional[str] = None
    mesafe_km: Optional[float] = None
    raw: Dict[str, Any] = field(default_factory=dict)

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> EczaneModel:
        geo_data = data.get("geo")
        geo_obj = GeoModel.from_dict(geo_data) if geo_data else None

        # Doğrudan lat/lng kök objede varsa
        if not geo_obj and ("latitude" in data or "lat" in data):
            geo_obj = GeoModel.from_dict(data)

        return cls(
            adi=data.get("adi") or data.get("name") or "",
            il=data.get("il") or data.get("city") or "",
            ilce=data.get("ilce") or data.get("district") or "",
            telefon=data.get("telefon") or data.get("phone"),
            adres=data.get("adres") or data.get("address"),
            tarif=data.get("tarif") or data.get("directions"),
            nobet_baslangic=data.get("nobet_baslangic") or data.get("start"),
            nobet_bitis=data.get("nobet_bitis") or data.get("end"),
            geo=geo_obj,
            kaynak=data.get("kaynak") or data.get("source"),
            mesafe_km=float(data["mesafe_km"]) if "mesafe_km" in data and data["mesafe_km"] is not None else None,
            raw=data,
        )


@dataclass
class IlModel:
    """İl ve İlçe Veri Modeli."""
    il: str
    plaka: Optional[int] = None
    ilceler: List[str] = field(default_factory=list)

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> IlModel:
        return cls(
            il=data.get("il") or data.get("name") or "",
            plaka=data.get("plaka") or data.get("plate"),
            ilceler=data.get("ilceler") or data.get("districts") or [],
        )
