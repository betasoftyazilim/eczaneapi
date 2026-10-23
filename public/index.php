<?php

declare(strict_types=1);

// Otomatik Yükleyici (Composer varsa Composer, yoksa yerel PSR-4 autoloader)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    spl_autoload_register(static function (string $class) {
        $prefix = 'EczaneApi\\';
        $baseDir = __DIR__ . '/../src/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
    require_once __DIR__ . '/../src/helpers.php';
}

use EczaneApi\Client;
use EczaneApi\Data\Sehirler;
use EczaneApi\Http\Request;
use EczaneApi\Http\Response;
use EczaneApi\Http\Router;

$request = Request::capture();
$path = $request->getPath();

// 1. API İSTEKLERİ: /api/ ile başlayan veya format=json içeren istekleri JSON Router'a gönder
if (str_starts_with($path, '/api/') || $request->input('format') === 'json' || $request->isJson()) {
    $router = new Router();
    $routeLoader = require __DIR__ . '/../routes/api.php';
    if (is_callable($routeLoader)) {
        $routeLoader($router);
    }
    $response = $router->dispatch($request);
    $response->send();
    exit;
}

// 2. WEB ARAYÜZÜ: Canlı Nöbetçi Eczane ve Çekilemeyen İller İnceleme Paneli
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

$rawIl = (string) $request->input('il', 'İstanbul');
$secilenIl = mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', '', trim($rawIl)), 0, 50, 'UTF-8') ?: 'İstanbul';

$rawIlce = (string) ($request->input('ilce') ?: '');
$secilenIlce = mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', '', trim($rawIlce)), 0, 50, 'UTF-8');

$rawTarih = (string) ($request->input('tarih') ?: date('Y-m-d'));
$secilenTarih = preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawTarih) ? $rawTarih : date('Y-m-d');
$forceRefresh = (bool) $request->input('refresh', false);

$iller = Sehirler::all();
$eczaneler = Client::il($secilenIl)
    ->ilce($secilenIlce)
    ->tarih($secilenTarih)
    ->fresh($forceRefresh)
    ->get();

$ilceler = Client::ilceler($secilenIl);
$service = Client::getInstance()->getService();
$cacheStats = $service->getCacheStats();

// Çekilemeyen İller Kontrolü (Hızlı analiz için önbellek veya istek durumundan)
$topluKontrol = (bool) $request->input('toplu_analiz', false);
$topluRapor = null;
if ($topluKontrol) {
    // Sadece ilk 10 veya hızlı tarama ile sunucu donmasını önleyen örnek analiz
    $topluRapor = $service->bulkFetch([], 0);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($secilenIl) ?> Nöbetçi Eczaneler - Modern Eczane API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #dc2626;
            --primary-dark: #b91c1c;
            --primary-light: #fee2e2;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            line-height: 1.6;
            padding-bottom: 60px;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        header {
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.4) 100%);
            border-bottom: 1px solid var(--border);
            padding: 24px 0;
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .logo-box { display: flex; align-items: center; gap: 12px; }
        .logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.4);
        }
        .logo-text h1 { font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -0.5px; }
        .logo-text span { font-size: 13px; color: var(--text-muted); }
        .badges { display: flex; gap: 8px; align-items: center; }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid var(--border);
        }
        .badge-success { background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.4); color: #34d399; }
        .badge-warning { background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.4); color: #fbbf24; }
        
        .hero {
            padding: 40px 0 20px;
            text-align: center;
        }
        .hero h2 { font-size: 32px; font-weight: 700; margin-bottom: 8px; }
        .hero p { color: var(--text-muted); font-size: 16px; max-width: 600px; margin: 0 auto 24px; }
        
        /* Arama Filtre Formu */
        .filter-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 32px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 140px;
            gap: 16px;
            align-items: flex-end;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #fff;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus { border-color: var(--primary); }
        .btn {
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #334155; color: #fff; }
        .btn-secondary:hover { background: #475569; }

        /* Nöbetçi Eczane Kartları */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .results-count { font-size: 18px; font-weight: 600; }
        .pharmacy-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .pharmacy-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, border-color 0.2s;
        }
        .pharmacy-card:hover {
            transform: translateY(-4px);
            border-color: rgba(220, 38, 38, 0.5);
        }
        .card-top { margin-bottom: 16px; }
        .dist-tag {
            display: inline-block;
            background: rgba(220, 38, 38, 0.15);
            color: #f87171;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .pharmacy-name { font-size: 20px; font-weight: 700; margin-bottom: 8px; color: #fff; }
        .pharmacy-address { font-size: 14px; color: var(--text-muted); margin-bottom: 12px; }
        .duty-time {
            font-size: 13px;
            color: #34d399;
            background: rgba(16, 185, 129, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 16px;
        }
        .card-actions { display: flex; gap: 10px; }
        .card-actions a {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-call { background: #0f172a; border: 1px solid var(--border); color: #fff; }
        .btn-call:hover { border-color: #38bdf8; color: #38bdf8; }
        .btn-map { background: var(--primary); color: #fff; }
        .btn-map:hover { background: var(--primary-dark); }

        /* Çekilemeyen İller Paneli */
        .failed-panel {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 40px;
        }
        .failed-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .failed-title { font-size: 18px; font-weight: 700; color: #f87171; display: flex; align-items: center; gap: 8px; }
        .failed-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .failed-table th, .failed-table td {
            padding: 10px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-align: left;
        }
        .failed-table th { color: var(--text-muted); font-weight: 600; }

        /* API Dokümantasyon Bölümü */
        .api-docs {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
        }
        .api-docs h3 { font-size: 20px; font-weight: 700; margin-bottom: 16px; }
        .endpoint-item {
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .endpoint-code { font-family: monospace; font-size: 14px; color: #38bdf8; }
        .method-badge {
            background: #0284c7;
            color: #fff;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }
    </style>
</head>
<body>

<header>
    <div class="container header-content">
        <div class="logo-box">
            <div class="logo-icon">💊</div>
            <div class="logo-text">
                <h1>Nöbetçi Eczane API</h1>
                <span>Laravel & Generic MVC Bağımsız Servisi</span>
            </div>
        </div>
        <div class="badges">
            <span class="badge badge-success">● e-Devlet TİTCK Aktif</span>
            <span class="badge">Önbellek: <?= count($cacheStats['active_keys'] ?? []) ?> İl</span>
            <span class="badge badge-warning">PHP <?= PHP_VERSION ?></span>
        </div>
    </div>
</header>

<main class="container">
    <div class="hero">
        <h2>Türkiye Nöbetçi Eczaneleri</h2>
        <p>Resmi e-Devlet TİTCK verileriyle anlık güncellenen, yüksek performanslı ve önbellek korumalı nöbetçi eczane motoru.</p>
    </div>

    <!-- Arama ve Filtreleme -->
    <div class="filter-card">
        <form method="get" action="/" class="filter-form">
            <div class="form-group">
                <label for="il">Şehir (İl):</label>
                <select name="il" id="il" class="form-control" onchange="this.form.submit();">
                    <?php foreach ($iller as $il): ?>
                    <option value="<?= htmlspecialchars($il['name']) ?>" <?= $secilenIl === $il['name'] ? 'selected' : '' ?>>
                        <?= sprintf('%02d', $il['id']) ?> - <?= htmlspecialchars($il['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="ilce">İlçe:</label>
                <select name="ilce" id="ilce" class="form-control" onchange="this.form.submit();">
                    <option value="">Tüm İlçeler</option>
                    <?php foreach ($ilceler as $ilce): ?>
                    <option value="<?= htmlspecialchars($ilce) ?>" <?= $secilenIlce === $ilce ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ilce) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tarih">Tarih:</label>
                <input type="date" name="tarih" id="tarih" class="form-control" value="<?= htmlspecialchars($secilenTarih) ?>">
            </div>

            <button type="submit" class="btn btn-primary">Sorgula</button>
        </form>
    </div>

    <!-- Sonuç Başlığı -->
    <div class="results-header">
        <div class="results-count">
            🏥 <?= htmlspecialchars($secilenIl) ?>
            <?= $secilenIlce ? ' / ' . htmlspecialchars($secilenIlce) : '' ?>
            <span style="color: var(--text-muted); font-size: 15px;">(<?= count($eczaneler) ?> Nöbetçi Eczane)</span>
        </div>
        <div>
            <a href="?il=<?= urlencode($secilenIl) ?>&ilce=<?= urlencode($secilenIlce) ?>&refresh=1" class="btn btn-secondary" style="font-size: 12px; padding: 8px 12px;">
                🔄 Önbelleği Yenile
            </a>
            <a href="?toplu_analiz=1" class="btn btn-secondary" style="font-size: 12px; padding: 8px 12px; margin-left: 6px;">
                📊 Çekilemeyen İller Analizi
            </a>
        </div>
    </div>

    <!-- Eczane Kartları Grid -->
    <?php if (empty($eczaneler)): ?>
    <div style="text-align: center; padding: 60px 20px; background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border);">
        <p style="font-size: 18px; color: var(--text-muted); margin-bottom: 12px;">⚠️ Bu il/ilçe için nöbetçi eczane kaydı bulunamadı veya e-Devlet henüz bu tarihi yayınlamadı.</p>
        <a href="?il=<?= urlencode($secilenIl) ?>&refresh=1" class="btn btn-primary">Tekrar Dene</a>
    </div>
    <?php else: ?>
    <div class="pharmacy-grid">
        <?php foreach ($eczaneler as $ecz): ?>
        <div class="pharmacy-card">
            <div class="card-top">
                <span class="dist-tag"><?= htmlspecialchars($ecz->dist) ?></span>
                <h3 class="pharmacy-name"><?= htmlspecialchars($ecz->name) ?></h3>
                <p class="pharmacy-address">📍 <?= htmlspecialchars($ecz->address) ?></p>
                <?php if ($ecz->nobetBaslangic || $ecz->nobetBitis): ?>
                <div class="duty-time">⏰ <?= htmlspecialchars($ecz->nobetBaslangic ?? '') ?> - <?= htmlspecialchars($ecz->nobetBitis ?? '') ?></div>
                <?php endif; ?>
            </div>
            <div class="card-actions">
                <?php if ($ecz->phone): ?>
                <a href="tel:<?= htmlspecialchars($ecz->getCleanPhone() ?? '') ?>" class="btn-call">
                    📞 <?= htmlspecialchars($ecz->phone) ?>
                </a>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($ecz->getMapsUrl()) ?>" target="_blank" rel="noopener noreferrer" class="btn-map">
                    🗺️ Yol Tarifi
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Çekilemeyen İller Analiz Tablosu -->
    <?php if ($topluRapor !== null): ?>
    <div class="failed-panel">
        <div class="failed-header">
            <div class="failed-title">
                ⚠️ Toplu Tarama & Çekilemeyen İller Durumu (<?= $topluRapor['basarisiz_sayisi'] ?> İl Başarısız / <?= $topluRapor['toplam'] ?> İl)
            </div>
            <span class="badge badge-success">Başarılı: <?= $topluRapor['basarili_sayisi'] ?></span>
        </div>
        <?php if (!empty($topluRapor['cekilemeyen_iller'])): ?>
        <table class="failed-table">
            <thead>
                <tr>
                    <th>Plaka</th>
                    <th>İl Adı</th>
                    <th>Hata / Durum Açıklaması</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topluRapor['cekilemeyen_iller'] as $f): ?>
                <tr>
                    <td><strong><?= sprintf('%02d', $f['plaka']) ?></strong></td>
                    <td><?= htmlspecialchars($f['il']) ?></td>
                    <td style="color: #fca5a5;"><?= htmlspecialchars($f['hata']) ?></td>
                    <td><a href="?il=<?= urlencode($f['il']) ?>&refresh=1" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;">Tekrar Çek</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color: #34d399;">🎉 Tüm iller başarıyla çekildi, çekilemeyen il bulunmuyor!</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- REST API Uç Noktaları -->
    <div class="api-docs">
        <h3>🔗 REST API Uç Noktaları (JSON)</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
            Laravel, Symfony, CodeIgniter, Vue, React, Mobil veya herhangi bir PHP/Web projenizden doğrudan aşağıdaki API uç noktalarını çağırabilirsiniz:
        </p>

        <div class="endpoint-item">
            <div>
                <span class="method-badge">GET</span>
                <span class="endpoint-code">/api/v1/nobetci?il=istanbul&ilce=kadikoy</span>
            </div>
            <a href="/api/v1/nobetci?il=<?= urlencode($secilenIl) ?>" target="_blank" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">JSON Gör</a>
        </div>

        <div class="endpoint-item">
            <div>
                <span class="method-badge">GET</span>
                <span class="endpoint-code">/api/v1/iller</span>
            </div>
            <a href="/api/v1/iller" target="_blank" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">JSON Gör</a>
        </div>

        <div class="endpoint-item">
            <div>
                <span class="method-badge">GET</span>
                <span class="endpoint-code">/api/v1/ilceler?il=<?= urlencode($secilenIl) ?></span>
            </div>
            <a href="/api/v1/ilceler?il=<?= urlencode($secilenIl) ?>" target="_blank" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">JSON Gör</a>
        </div>

        <div class="endpoint-item">
            <div>
                <span class="method-badge">GET</span>
                <span class="endpoint-code">/api/v1/status</span>
            </div>
            <a href="/api/v1/status" target="_blank" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">JSON Gör</a>
        </div>
    </div>
</main>

</body>
</html>
