package tr.com.betasoft.eczaneapi;

import com.google.gson.Gson;
import com.google.gson.JsonObject;
import com.google.gson.reflect.TypeToken;
import tr.com.betasoft.eczaneapi.models.EczaneModel;
import tr.com.betasoft.eczaneapi.models.IlModel;

import java.io.IOException;
import java.lang.reflect.Type;
import java.net.URI;
import java.net.URLEncoder;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.charset.StandardCharsets;
import java.time.Duration;
import java.util.Collections;
import java.util.List;

public class EczaneClient {
    public static final String DEFAULT_BASE_URL = "https://eczaneapi.vercel.app";

    private final String baseUrl;
    private final String apiKey;
    private final HttpClient httpClient;
    private final Gson gson;

    public EczaneClient() {
        this(DEFAULT_BASE_URL, null);
    }

    public EczaneClient(String baseUrl, String apiKey) {
        this.baseUrl = (baseUrl != null ? baseUrl : DEFAULT_BASE_URL).replaceAll("/+$", "");
        this.apiKey = apiKey;
        this.httpClient = HttpClient.newBuilder()
                .connectTimeout(Duration.ofSeconds(15))
                .build();
        this.gson = new Gson();
    }

    private <T> T sendRequest(String endpoint, Type type) throws IOException, InterruptedException {
        String url = baseUrl + "/" + endpoint.replaceAll("^/+", "");
        HttpRequest.Builder builder = HttpRequest.newBuilder()
                .uri(URI.create(url))
                .timeout(Duration.ofSeconds(15))
                .header("Accept", "application/json")
                .header("User-Agent", "EczaneAPI-Java-SDK/1.1.1 (www.betasoft.com.tr)")
                .GET();

        if (apiKey != null && !apiKey.trim().isEmpty()) {
            builder.header("Authorization", "Bearer " + apiKey);
        }

        HttpResponse<String> response = httpClient.send(builder.build(), HttpResponse.BodyHandlers.ofString());

        if (response.statusCode() >= 400) {
            throw new IOException("API Hatası HTTP " + response.statusCode() + ": " + response.body());
        }

        String body = response.body();
        try {
            JsonObject root = gson.fromJson(body, JsonObject.class);
            if (root != null && root.has("data")) {
                return gson.fromJson(root.get("data"), type);
            }
        } catch (Exception ignored) {
        }

        return gson.fromJson(body, type);
    }

    public List<EczaneModel> nobetci(String il, String ilce, String tarih) throws IOException, InterruptedException {
        String cleanIl = URLEncoder.encode(il.trim().toLowerCase(), StandardCharsets.UTF_8);
        StringBuilder endpoint = new StringBuilder("api/nobetci/").append(cleanIl);

        if (ilce != null && !ilce.trim().isEmpty()) {
            String cleanIlce = URLEncoder.encode(ilce.trim().toLowerCase(), StandardCharsets.UTF_8);
            endpoint.append("/").append(cleanIlce);
        }

        if (tarih != null && !tarih.trim().isEmpty()) {
            endpoint.append("?tarih=").append(URLEncoder.encode(tarih, StandardCharsets.UTF_8));
        }

        Type type = new TypeToken<List<EczaneModel>>() {}.getType();
        List<EczaneModel> list = sendRequest(endpoint.toString(), type);
        return list != null ? list : Collections.emptyList();
    }

    public List<IlModel> iller() throws IOException, InterruptedException {
        Type type = new TypeToken<List<IlModel>>() {}.getType();
        List<IlModel> list = sendRequest("api/iller", type);
        return list != null ? list : Collections.emptyList();
    }

    public List<String> ilceler(String il) throws IOException, InterruptedException {
        String cleanIl = URLEncoder.encode(il.trim().toLowerCase(), StandardCharsets.UTF_8);
        Type type = new TypeToken<List<String>>() {}.getType();
        List<String> list = sendRequest("api/ilceler/" + cleanIl, type);
        return list != null ? list : Collections.emptyList();
    }

    public List<EczaneModel> yakin(double latitude, double longitude, double mesafeKm) throws IOException, InterruptedException {
        String endpoint = String.format("api/yakin?lat=%f&lng=%f&mesafe=%f", latitude, longitude, mesafeKm);
        Type type = new TypeToken<List<EczaneModel>>() {}.getType();
        List<EczaneModel> list = sendRequest(endpoint, type);
        return list != null ? list : Collections.emptyList();
    }
}
