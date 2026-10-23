package tr.com.betasoft.eczaneapi.models;

import com.google.gson.annotations.SerializedName;

public class EczaneModel {
    @SerializedName("adi")
    private String adi;

    @SerializedName("il")
    private String il;

    @SerializedName("ilce")
    private String ilce;

    @SerializedName("telefon")
    private String telefon;

    @SerializedName("adres")
    private String adres;

    @SerializedName("tarif")
    private String tarif;

    @SerializedName("nobet_baslangic")
    private String nobetBaslangic;

    @SerializedName("nobet_bitis")
    private String nobetBitis;

    @SerializedName("geo")
    private GeoLocation geo;

    @SerializedName("kaynak")
    private String kaynak;

    @SerializedName("mesafe_km")
    private Double mesafeKm;

    public String getAdi() { return adi; }
    public String getIl() { return il; }
    public String getIlce() { return ilce; }
    public String getTelefon() { return telefon; }
    public String getAdres() { return adres; }
    public String getTarif() { return tarif; }
    public String getNobetBaslangic() { return nobetBaslangic; }
    public String getNobetBitis() { return nobetBitis; }
    public GeoLocation getGeo() { return geo; }
    public String getKaynak() { return kaynak; }
    public Double getMesafeKm() { return mesafeKm; }
}
