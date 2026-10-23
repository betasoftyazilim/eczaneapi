import { EczaneClient } from "./client";
import { EczaneItem, IlItem, EczaneClientOptions } from "./types";

export * from "./types";
export * from "./client";

const defaultClient = new EczaneClient();

export const Eczane = {
  config(options: EczaneClientOptions) {
    (defaultClient as any).baseUrl = (options.baseUrl || (defaultClient as any).baseUrl).replace(/\/+$/, "");
    if (options.apiKey) (defaultClient as any).apiKey = options.apiKey;
    if (options.timeout) (defaultClient as any).timeout = options.timeout;
  },

  nobetci(il: string, ilce?: string, tarih?: string): Promise<EczaneItem[]> {
    return defaultClient.nobetci(il, ilce, tarih);
  },

  iller(): Promise<IlItem[]> {
    return defaultClient.iller();
  },

  ilceler(il: string): Promise<string[]> {
    return defaultClient.ilceler(il);
  },

  yakin(latitude: number, longitude: number, mesafeKm = 5): Promise<EczaneItem[]> {
    return defaultClient.yakin(latitude, longitude, mesafeKm);
  },
};

export default Eczane;
