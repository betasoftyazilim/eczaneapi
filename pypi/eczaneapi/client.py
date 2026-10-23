"""
Eczane API Python HTTP İstemcisi
"""

from __future__ import annotations
import json
import urllib.request
import urllib.parse
import urllib.error
from typing import List, Optional, Dict, Any

from .models import EczaneModel, IlModel
from .exceptions import EczaneApiException, NotFoundException


DEFAULT_BASE_URL = "https://eczaneapi.vercel.app"


class EczaneClient:
    """Eczane API için HTTP İstemci Sınıfı."""

    def __init__(
        self,
        base_url: Optional[str] = None,
        api_key: Optional[str] = None,
        timeout: int = 15,
    ) -> None:
        self.base_url = (base_url or DEFAULT_BASE_URL).rstrip("/")
        self.api_key = api_key
        self.timeout = timeout

    def _request(self, endpoint: str, params: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        url = f"{self.base_url}/{endpoint.lstrip('/')}"
        
        filtered_params = {k: v for k, v in (params or {}).items() if v is not None}
        if filtered_params:
            url += f"?{urllib.parse.urlencode(filtered_params)}"

        headers = {
            "User-Agent": "EczaneAPI-Python-SDK/1.0.0",
            "Accept": "application/json",
        }
        if self.api_key:
            headers["Authorization"] = f"Bearer {self.api_key}"

        req = urllib.request.Request(url, headers=headers, method="GET")

        try:
            with urllib.request.urlopen(req, timeout=self.timeout) as resp:
                status_code = resp.getcode()
                raw_body = resp.read().decode("utf-8")
                try:
                    payload = json.loads(raw_body)
                except Exception as json_err:
                    raise EczaneApiException(f"Geçersiz JSON yanıtı: {json_err}", status_code=status_code)
                
                if status_code >= 400:
                    msg = payload.get("message") or payload.get("error") or f"HTTP Hatası: {status_code}"
                    if status_code == 404:
                        raise NotFoundException(msg, status_code=status_code)
                    raise EczaneApiException(msg, status_code=status_code)
                
                return payload
        except urllib.error.HTTPError as e:
            raw_err = e.read().decode("utf-8", errors="ignore")
            try:
                err_json = json.loads(raw_err)
                msg = err_json.get("message") or err_json.get("error") or str(e)
            except Exception:
                msg = str(e)
            
            if e.code == 404:
                raise NotFoundException(msg, status_code=e.code)
            raise EczaneApiException(msg, status_code=e.code)
        except urllib.error.URLError as e:
            raise EczaneApiException(f"Bağlantı hatası: {e.reason}")
        except Exception as e:
            if isinstance(e, EczaneApiException):
                raise
            raise EczaneApiException(f"Beklenmeyen istek hatası: {str(e)}")

    def nobetci(
        self,
        il: str,
        ilce: Optional[str] = None,
        tarih: Optional[str] = None,
    ) -> List[EczaneModel]:
        """Belirtilen il (ve opsiyonel ilçe/tarih) için nöbetçi eczaneleri döndürür."""
        clean_il = urllib.parse.quote(il.strip().lower())
        endpoint = f"api/nobetci/{clean_il}"
        if ilce:
            clean_ilce = urllib.parse.quote(ilce.strip().lower())
            endpoint += f"/{clean_ilce}"

        params = {}
        if tarih:
            params["tarih"] = tarih

        data = self._request(endpoint, params=params)
        items = data.get("data", []) if isinstance(data, dict) else data
        return [EczaneModel.from_dict(item) for item in items]

    def iller(self) -> List[IlModel]:
        """Tüm illeri ve ilçelerini listeler."""
        data = self._request("api/iller")
        items = data.get("data", []) if isinstance(data, dict) else data
        return [IlModel.from_dict(item) for item in items]

    def ilceler(self, il: str) -> List[str]:
        """Belirtilen ile ait ilçe isimlerini döndürür."""
        clean_il = urllib.parse.quote(il.strip().lower())
        data = self._request(f"api/ilceler/{clean_il}")
        items = data.get("data", []) if isinstance(data, dict) else data
        return items

    def yakin(self, latitude: float, longitude: float, mesafe_km: float = 5.0) -> List[EczaneModel]:
        """Koordinata en yakın nöbetçi eczaneleri listeler."""
        params = {
            "lat": latitude,
            "lng": longitude,
            "mesafe": mesafe_km,
        }
        data = self._request("api/yakin", params=params)
        items = data.get("data", []) if isinstance(data, dict) else data
        return [EczaneModel.from_dict(item) for item in items]

    def query(self) -> EczaneQuery:
        """Akıcı sorgu nesnesi döndürür."""
        return EczaneQuery(self)


class EczaneQuery:
    """Akıcı (fluent) sorgu oluşturucu."""

    def __init__(self, client: EczaneClient) -> None:
        self._client = client
        self._il: Optional[str] = None
        self._ilce: Optional[str] = None
        self._tarih: Optional[str] = None

    def il(self, il_adi: str) -> EczaneQuery:
        self._il = il_adi
        return self

    def ilce(self, ilce_adi: str) -> EczaneQuery:
        self._ilce = ilce_adi
        return self

    def tarih(self, tarih_str: str) -> EczaneQuery:
        self._tarih = tarih_str
        return self

    def get(self) -> List[EczaneModel]:
        if not self._il:
            raise ValueError("Sorgu için en az bir 'il' belirtilmelidir.")
        return self._client.nobetci(il=self._il, ilce=self._ilce, tarih=self._tarih)


# Kolay statik erişim için Singleton / Standart Wrapper
_default_client = EczaneClient()


class Eczane:
    """Hızlı ve statik kullanım için facade arayüzü."""

    @classmethod
    def set_base_url(cls, url: str) -> None:
        global _default_client
        _default_client.base_url = url.rstrip("/")

    @classmethod
    def set_api_key(cls, key: str) -> None:
        global _default_client
        _default_client.api_key = key

    @classmethod
    def nobetci(
        cls,
        il: str,
        ilce: Optional[str] = None,
        tarih: Optional[str] = None,
    ) -> List[EczaneModel]:
        return _default_client.nobetci(il, ilce, tarih)

    @classmethod
    def iller(cls) -> List[IlModel]:
        return _default_client.iller()

    @classmethod
    def ilceler(cls, il: str) -> List[str]:
        return _default_client.ilceler(il)

    @classmethod
    def yakin(cls, latitude: float, longitude: float, mesafe_km: float = 5.0) -> List[EczaneModel]:
        return _default_client.yakin(latitude, longitude, mesafe_km)

    @classmethod
    def il(cls, il_adi: str) -> EczaneQuery:
        return _default_client.query().il(il_adi)
