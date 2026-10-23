"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __exportStar = (this && this.__exportStar) || function(m, exports) {
    for (var p in m) if (p !== "default" && !Object.prototype.hasOwnProperty.call(exports, p)) __createBinding(exports, m, p);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Eczane = void 0;
const client_1 = require("./client");
__exportStar(require("./types"), exports);
__exportStar(require("./client"), exports);
const defaultClient = new client_1.EczaneClient();
exports.Eczane = {
    config(options) {
        defaultClient.baseUrl = (options.baseUrl || defaultClient.baseUrl).replace(/\/+$/, "");
        if (options.apiKey)
            defaultClient.apiKey = options.apiKey;
        if (options.timeout)
            defaultClient.timeout = options.timeout;
    },
    nobetci(il, ilce, tarih) {
        return defaultClient.nobetci(il, ilce, tarih);
    },
    iller() {
        return defaultClient.iller();
    },
    ilceler(il) {
        return defaultClient.ilceler(il);
    },
    yakin(latitude, longitude, mesafeKm = 5) {
        return defaultClient.yakin(latitude, longitude, mesafeKm);
    },
};
exports.default = exports.Eczane;
