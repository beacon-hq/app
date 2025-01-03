<?php

declare(strict_types=1);

namespace App\Values;

use App\Values\Collections\EnvironmentCollection;
use App\Values\Factories\EnvironmentFactory;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Bag;
use Bag\Traits\HasFactory;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?string $name = null, ?string $description = null, ?string $slug = null, ?string $color = null)
 */
#[Collection(EnvironmentCollection::class)]
#[Factory(EnvironmentFactory::class)]
#[TypeScript]
readonly class Environment extends Bag
{
    use HasFactory;

    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $slug = null,
        public ?string $color = null,
    ) {
    }
}
