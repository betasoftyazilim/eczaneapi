"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.EczaneClient = exports.DEFAULT_BASE_URL = void 0;
exports.DEFAULT_BASE_URL = "https://eczaneapi.vercel.app";
class EczaneClient {
    constructor(options) {
        this.baseUrl = (options?.baseUrl || exports.DEFAULT_BASE_URL).replace(/\/+$/, "");
        this.apiKey = options?.apiKey;
        this.timeout = options?.timeout || 15000;
    }
    async request(endpoint, params) {
        let url = `${this.baseUrl}/${endpoint.replace(/^\/+/, "")}`;
        if (params) {
            const searchParams = new URLSearchParams();
            for (const [key, value] of Object.entries(params)) {
                if (value !== undefined && value !== null) {
                    searchParams.append(key, String(value));
                }
            }
            const qs = searchParams.toString();
            if (qs) {
                url += `?${qs}`;
            }
        }
        const headers = {
            Accept: "application/json",
            "User-Agent": "EczaneAPI-Node-SDK/1.0.0",
        };
        if (this.apiKey) {
            headers["Authorization"] = `Bearer ${this.apiKey}`;
        }
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), this.timeout);
        try {
            const res = await fetch(url, {
                method: "GET",
                headers,
                signal: controller.signal,
            });
            const body = await res.json().catch(() => ({}));
            if (!res.ok) {
                const errorMsg = body?.message || body?.error || `HTTP ${res.status} Hatası`;
                const err = new Error(errorMsg);
                err.status = res.status;
                throw err;
            }
            return body?.data ?? body;
        }
        catch (err) {
            if (err.name === "AbortError") {
                throw new Error(`İstek zaman aşımına uğradı (${this.timeout}ms)`);
            }
            throw err;
        }
        finally {
            clearTimeout(timer);
        }
    }
    /**
     * Belirtilen il ve opsiyonel ilçe için nöbetçi eczaneleri listeler.
     */
    async nobetci(il, ilce, tarih) {
        const cleanIl = encodeURIComponent(il.trim().toLowerCase());
        let endpoint = `api/nobetci/${cleanIl}`;
        if (ilce) {
            const cleanIlce = encodeURIComponent(ilce.trim().toLowerCase());
            endpoint += `/${cleanIlce}`;
        }
        const params = {};
        if (tarih)
            params.tarih = tarih;
        const data = await this.request(endpoint, params);
        return Array.isArray(data) ? data : data?.data || [];
    }
    /**
     * 81 il ve bağlı ilçelerini listeler.
     */
    async iller() {
        const data = await this.request("api/iller");
        return Array.isArray(data) ? data : data?.data || [];
    }
    /**
     * Belirli bir ile ait ilçeleri listeler.
     */
    async ilceler(il) {
        const cleanIl = encodeURIComponent(il.trim().toLowerCase());
        const data = await this.request(`api/ilceler/${cleanIl}`);
        return Array.isArray(data) ? data : data?.data || [];
    }
    /**
     * Belirtilen GPS koordinatına en yakın nöbetçi eczaneleri listeler.
     */
    async yakin(latitude, longitude, mesafeKm = 5) {
        const params = {
            lat: latitude,
            lng: longitude,
            mesafe: mesafeKm,
        };
        const data = await this.request("api/yakin", params);
        return Array.isArray(data) ? data : data?.data || [];
    }
}
exports.EczaneClient = EczaneClient;
