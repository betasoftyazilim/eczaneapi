<?php

declare(strict_types=1);

namespace EczaneApi\Laravel\Console;

use EczaneApi\Data\Sehirler;
use EczaneApi\EczaneService;
use Illuminate\Console\Command;

class EczaneFetchCommand extends Command
{
    protected $signature = 'eczane:fetch
                            {il? : Sorgulanacak il adı veya plaka kodu}
                            {--ilce= : Belirli bir ilçeyi filtrele}
                            {--fresh : Önbelleği pas geçip güncel veriyi çek}
                            {--all : 81 ilin tamamını sırayla tara ve çekilemeyen illeri raporla}
                            {--sleep=1 : Toplu taramada istekler arası bekleme süresi (saniye)}';

    protected $description = 'Türkiye nöbetçi eczane verilerini çeker, önbelleğe alır ve çekilemeyen illeri raporlar';

    public function handle(EczaneService $service): int
    {
        $isAll = (bool) $this->option('all');

        if ($isAll) {
            return $this->handleAllCities($service);
        }

        $il = (string) ($this->argument('il') ?: config('eczane.default_il', 'İstanbul'));
        $ilce = $this->option('ilce') ? (string) $this->option('ilce') : null;
        $fresh = (bool) $this->option('fresh');

        $this->info("🏥 Nöbetçi eczaneler sorgulanıyor: {$il}" . ($ilce ? " / {$ilce}" : '') . ($fresh ? ' [Taze İstek]' : ''));

        $eczaneler = $service->getNobetciEczaneler($il, $ilce, null, $fresh);

        if (empty($eczaneler)) {
            $this->error("❌ '{$il}' ili için nöbetçi eczane bulunamadı veya e-Devlet veri sağlayamadı.");
            return Command::FAILURE;
        }

        $this->info("✅ Toplam " . count($eczaneler) . " nöbetçi eczane listelendi:\n");

        $tableData = [];
        foreach ($eczaneler as $e) {
            $tableData[] = [
                'Eczane Adı' => $e->name,
                'İlçe' => $e->dist,
                'Telefon' => $e->phone ?? '-',
                'Adres' => mb_substr($e->address, 0, 45, 'UTF-8') . (mb_strlen($e->address, 'UTF-8') > 45 ? '...' : ''),
                'Nöbet Saatleri' => ($e->nobetBaslangic && $e->nobetBitis) ? "{$e->nobetBaslangic} - {$e->nobetBitis}" : '24 Saat',
            ];
        }

        $this->table(['Eczane Adı', 'İlçe', 'Telefon', 'Adres', 'Nöbet Saatleri'], $tableData);
        return Command::SUCCESS;
    }

    private function handleAllCities(EczaneService $service): int
    {
        $iller = array_column(Sehirler::all(), 'name');
        $sleep = (int) $this->option('sleep');
        $this->warn("🚀 81 il için toplu nöbetçi eczane taraması başlatılıyor (İstekler arası bekleme: {$sleep} sn)...");

        $bar = $this->output->createProgressBar(count($iller));
        $bar->start();

        $basarili = [];
        $cekilemeyenler = [];

        foreach ($iller as $ilAdi) {
            $data = $service->getNobetciRaw($ilAdi, null, null, true);

            if (!empty($data)) {
                $basarili[$ilAdi] = count($data);
            } else {
                $plaka = Sehirler::getPlakaKodu($ilAdi) ?? 0;
                $cekilemeyenler[] = [
                    'plaka' => $plaka,
                    'il' => $ilAdi,
                    'hata' => 'e-Devlet boş yanıt döndü veya bağlantı zaman aşımına uğradı',
                ];
            }

            $bar->advance();
            if ($sleep > 0) {
                sleep($sleep);
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Başarılı İller Özeti
        $this->info("✅ Başarılı İller: " . count($basarili) . " / " . count($iller));

        // Çekilemeyen İller Detaylı Raporu
        if (!empty($cekilemeyenler)) {
            $this->newLine();
            $this->error("⚠️ ÇEKİLEMEYEN İLLER (" . count($cekilemeyenler) . " İl):");
            $this->table(
                ['Plaka', 'İl Adı', 'Hata / Durum Açıklaması'],
                array_map(static fn ($c) => [
                    'Plaka' => $c['plaka'],
                    'İl Adı' => $c['il'],
                    'Hata / Durum Açıklaması' => $c['hata'],
                ], $cekilemeyenler)
            );
        } else {
            $this->info("🎉 Harika! 81 ilin tamamı başarıyla çekildi, hiçbir çekilemeyen il kalmadı.");
        }

        return Command::SUCCESS;
    }
}
