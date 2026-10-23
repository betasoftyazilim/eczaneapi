import { EczaneItem, IlItem, EczaneClientOptions } from "./types";
export declare const DEFAULT_BASE_URL = "https://eczaneapi.vercel.app";
export declare class EczaneClient {
    private baseUrl;
    private apiKey?;
    private timeout;
    constructor(options?: EczaneClientOptions);
    private request;
    /**
     * Belirtilen il ve opsiyonel ilçe için nöbetçi eczaneleri listeler.
     */
    nobetci(il: string, ilce?: string, tarih?: string): Promise<EczaneItem[]>;
    /**
     * 81 il ve bağlı ilçelerini listeler.
     */
    iller(): Promise<IlItem[]>;
    /**
     * Belirli bir ile ait ilçeleri listeler.
     */
    ilceler(il: string): Promise<string[]>;
    /**
     * Belirtilen GPS koordinatına en yakın nöbetçi eczaneleri listeler.
     */
    yakin(latitude: number, longitude: number, mesafeKm?: number): Promise<EczaneItem[]>;
}
//# sourceMappingURL=client.d.ts.map