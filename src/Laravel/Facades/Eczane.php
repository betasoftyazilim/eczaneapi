<?php

declare(strict_types=1);

namespace EczaneApi\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \EczaneApi\Client il(string $il)
 * @method static \EczaneApi\Client ilce(string $ilce)
 * @method static \EczaneApi\Client fresh(bool $force = true)
 * @method static array get()
 * @method static array toArray()
 * @method static array nobetci(string $il, ?string $ilce = null, ?string $tarih = null)
 * @method static array iller()
 * @method static array ilceler(string $il)
 * @method static array refresh(?string $il = null)
 * @method static bool clearCache(?string $il = null)
 *
 * @see \EczaneApi\Client
 */
class Eczane extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'eczane';
    }
}
