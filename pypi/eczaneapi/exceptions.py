"""
Eczane API Hata Sınıfları
"""

from typing import Optional


class EczaneApiException(Exception):
    """Eczane API genel hata sınıfı."""

    def __init__(self, message: str, status_code: Optional[int] = None) -> None:
        super().__init__(message)
        self.message = message
        self.status_code = status_code

    def __str__(self) -> str:
        if self.status_code:
            return f"[HTTP {self.status_code}] {self.message}"
        return self.message


class NotFoundException(EczaneApiException):
    """Bulunamadı (404) hatası."""
    pass
