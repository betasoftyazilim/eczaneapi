package tr.com.betasoft.eczaneapi;

import tr.com.betasoft.eczaneapi.models.EczaneModel;
import tr.com.betasoft.eczaneapi.models.IlModel;

import java.io.IOException;
import java.util.List;

public class Eczane {
    private static EczaneClient defaultClient = new EczaneClient();

    public static void configure(String baseUrl, String apiKey) {
        defaultClient = new EczaneClient(baseUrl, apiKey);
    }

    public static List<EczaneModel> nobetci(String il, String ilce) throws IOException, InterruptedException {
        return defaultClient.nobetci(il, ilce, null);
    }

    public static List<EczaneModel> nobetci(String il, String ilce, String tarih) throws IOException, InterruptedException {
        return defaultClient.nobetci(il, ilce, tarih);
    }

    public static List<IlModel> iller() throws IOException, InterruptedException {
        return defaultClient.iller();
    }

    public static List<String> ilceler(String il) throws IOException, InterruptedException {
        return defaultClient.ilceler(il);
    }

    public static List<EczaneModel> yakin(double latitude, double longitude, double mesafeKm) throws IOException, InterruptedException {
        return defaultClient.yakin(latitude, longitude, mesafeKm);
    }
}
