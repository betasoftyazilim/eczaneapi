import unittest
from eczaneapi.models import EczaneModel, IlModel, GeoModel
from eczaneapi.client import EczaneClient, Eczane
from eczaneapi.exceptions import EczaneApiException, NotFoundException


class TestEczaneApi(unittest.TestCase):
    def test_model_parsing(self):
        sample = {
            "adi": "Güneş Eczanesi",
            "il": "İstanbul",
            "ilce": "Kadıköy",
            "telefon": "0216 123 45 67",
            "adres": "Moda Cad. No:12",
            "geo": {"lat": 40.98, "lng": 29.02},
            "kaynak": "titck_edevlet",
        }
        item = EczaneModel.from_dict(sample)
        self.assertEqual(item.adi, "Güneş Eczanesi")
        self.assertEqual(item.il, "İstanbul")
        self.assertEqual(item.ilce, "Kadıköy")
        self.assertIsNotNone(item.geo)
        self.assertEqual(item.geo.latitude, 40.98)
        self.assertEqual(item.geo.longitude, 29.02)
        self.assertEqual(item.kaynak, "titck_edevlet")

    def test_query_builder_il_zorunlulugu(self):
        client = EczaneClient()
        query = client.query()
        with self.assertRaises(ValueError):
            query.get()

    def test_exceptions(self):
        exc = NotFoundException("Bulunamadı", 404)
        self.assertIn("404", str(exc))
        self.assertEqual(exc.status_code, 404)


if __name__ == "__main__":
    unittest.main()
