<?php

declare(strict_types=1);

if (!function_exists('eczane')) {
    /**
     * Eczane istemcisine hızlı erişim fonksiyonu
     */
    function eczane(): \EczaneApi\Client
    {
        return \EczaneApi\Client::getInstance();
    }
}

if (!function_exists('eczane_slugify')) {
    /**
     * Türkçe karakter uyumlu slug üreteci
     */
    function eczane_slugify(string $text): string
    {
        $replace = [
            'ı' => 'i', 'İ' => 'i', 'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u', 'ş' => 's', 'Ş' => 's',
            'ö' => 'o', 'Ö' => 'o', 'ç' => 'c', 'Ç' => 'c',
        ];
        $text = strtr($text, $replace);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', (string) $text);
        return trim((string) $text, '-');
    }
}
