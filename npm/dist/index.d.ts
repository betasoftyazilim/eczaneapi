import { EczaneItem, IlItem, EczaneClientOptions } from "./types";
export * from "./types";
export * from "./client";
export declare const Eczane: {
    config(options: EczaneClientOptions): void;
    nobetci(il: string, ilce?: string, tarih?: string): Promise<EczaneItem[]>;
    iller(): Promise<IlItem[]>;
    ilceler(il: string): Promise<string[]>;
    yakin(latitude: number, longitude: number, mesafeKm?: number): Promise<EczaneItem[]>;
};
export default Eczane;
//# sourceMappingURL=index.d.ts.map