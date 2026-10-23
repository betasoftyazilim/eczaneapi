"""
Eczane API Python Client
~~~~~~~~~~~~~~~~~~~~~~~~
Türkiye 81 İl ve 973 İlçesi İçin Nöbetçi Eczane API İstemcisi.
"""

from .client import EczaneClient, Eczane
from .models import EczaneModel, IlModel, GeoModel
from .exceptions import EczaneApiException, NotFoundException

__version__ = "1.0.0"
__author__ = "BetaSoft Yazılım"
__email__ = "betasoftyazilim@gmail.com"

__all__ = [
    "EczaneClient",
    "Eczane",
    "EczaneModel",
    "IlModel",
    "GeoModel",
    "EczaneApiException",
    "NotFoundException",
]
