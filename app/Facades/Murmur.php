<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \lastguest\Murmur
 * @mixin \lastguest\Murmur
 */
class Murmur extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \lastguest\Murmur::class;
    }
}
