const assert = require("assert");
const { Eczane, EczaneClient } = require("../dist/index.js");

console.log("🧪 Node.js Eczane SDK Testleri Başlatılıyor...");

assert(typeof Eczane.nobetci === "function", "Eczane.nobetci bir fonksiyon olmalı");
assert(typeof Eczane.iller === "function", "Eczane.iller bir fonksiyon olmalı");
assert(typeof Eczane.ilceler === "function", "Eczane.ilceler bir fonksiyon olmalı");
assert(typeof Eczane.yakin === "function", "Eczane.yakin bir fonksiyon olmalı");

const client = new EczaneClient({ baseUrl: "https://mock.local" });
assert(client !== null, "EczaneClient örneği oluşturulabilmeli");

console.log("✅ Tüm Node.js SDK birim testleri başarıyla geçti!");
