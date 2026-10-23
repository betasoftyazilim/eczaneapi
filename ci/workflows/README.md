# CI / CD Otomatik Release İş Akışları 🚀

Bu dizindeki iş akışları, PyPI, npm ve NuGet paketlerini bağımsız olarak derleyip yayınlamak için hazırlanmıştır.

## İş Akışı Dosyaları

- `release-pypi.yml` -> `pypi-v*` etiketi geldiğinde Python SDK'sını PyPI'ye yükler ve bağımsız GitHub Release oluşturur.
- `release-npm.yml` -> `npm-v*` etiketi geldiğinde Node.js / TypeScript SDK'sını npm'e yükler ve bağımsız GitHub Release oluşturur.
- `release-dotnet.yml` -> `dotnet-v*` etiketi geldiğinde C# / .NET SDK'sını NuGet'e yükler ve bağımsız GitHub Release oluşturur.

> **Not:** GitHub web arayüzünden `.github/workflows/` klasörüne kopyalayarak Actions sekmesinde doğrudan çalıştırabilirsiniz.
