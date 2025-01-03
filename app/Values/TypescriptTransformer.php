<?php

declare(strict_types=1);

namespace App\Values;

use Illuminate\Support\Str;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;

class TypescriptTransformer extends DtoTransformer
{
    protected function transformPropertyName(ReflectionProperty $property, MissingSymbolsCollection $missingSymbols): string
    {
        return Str::snake(parent::transformPropertyName($property, $missingSymbols));
    }
}
