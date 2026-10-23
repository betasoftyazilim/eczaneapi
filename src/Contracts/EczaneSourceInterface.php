<?php

declare(strict_types=1);

namespace EczaneApi\Contracts;

interface EczaneSourceInterface
{
    /**
     * Belirtilen il ve isteğe bağlı tarih için nöbetçi eczaneleri çeker.
     *
     * @param string $ilAdi Şehir adı veya plaka kodu
     * @param string|null $tarih d/m/Y veya Y-m-d formatında tarih (null ise bugün)
     * @return array<int, array<string, mixed>>|null Ham eczane listesi
     */
    public function fetch(string $ilAdi, ?string $tarih = null): ?array;

    /**
     * Kaynak sağlayıcısının adı (örn: 'edevlet', 'mock')
     */
    public function getName(): string;
}
