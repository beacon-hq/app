<?php

declare(strict_types=1);

namespace App\Values;

use App\Values\Collections\PolicyValueCollection;
use App\Values\Factories\PolicyValueFactory;
use Bag\Attributes\Collection as CollectionAttribute;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Bag;
use Bag\Collection;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Spatie\TypeScriptTransformer\Attributes\Hidden as HiddenFromTypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(PolicyDefinition $policyDefinition, CollectionAttribute $value, ?bool $status = null)
 * @method static PolicyValueCollection collect(iterable $items)
 */
#[CollectionAttribute(PolicyValueCollection::class)]
#[Factory(PolicyValueFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class PolicyValue extends Bag
{
    use HasFactory;

    public function __construct(
        public PolicyDefinition $policyDefinition,
        #[LiteralTypeScriptType('Array<any>')]
        public Collection $values,
        #[HiddenFromTypeScript]
        public ?bool $status = null,
    ) {
    }
}
